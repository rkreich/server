<?php

require_once(dirname(__FILE__) . '/kApiCacheBase.php');
require_once(dirname(__FILE__) . '/../../../../config/kConf.php');
require_once(dirname(__FILE__) . '/../request/infraRequestUtils.class.php');
require_once(dirname(__FILE__) . '/kCacheManager.php');
require_once(dirname(__FILE__) . '/../request/kSessionBase.class.php');
require_once(dirname(__FILE__) . '/../request/kIpAddressUtils.php');
require_once(dirname(__FILE__) . '/../request/kGeoUtils.php');
require_once(dirname(__FILE__) . '/../kGeoCoderManager.php');
require_once(dirname(__FILE__) . '/../../../../../infra/monitor/KalturaMonitorClient.php');

/**
 * @package server-infra
 * @subpackage cache
 */
class kApiCache extends kApiCacheBase
{
	const EXTRA_KEYS_PREFIX = 'extra-keys-';	// apc cache key prefix

	const EXTRA_FIELDS_EXPIRY = 604800;			// when using a cache shared between servers it makes sense to have EXTRA_FIELDS_EXPIRY > CONDITIONAL_CACHE_EXPIRY
												// since the extra fields are stored locally on each server

	const SUFFIX_DATA =  '.cache';
	const SUFFIX_RULES = '.rules';
	const SUFFIX_LOG = '.log';

	const CACHE_VERSION = '14';

	// cache modes
	const CACHE_MODE_ANONYMOUS = 1;				// anonymous caching should be performed - the cached response will not be associated with any conditions
	const CACHE_MODE_CONDITIONAL = 2;			// cache the response along with its matching conditions

	const ANONYMOUS_CACHE_EXPIRY = 600;
	const CONDITIONAL_CACHE_EXPIRY = 86400;		// 1 day, must not be greater than the expiry of the query cache keys
	const KALTURA_COMMENT_MARKER = '@KALTURA_COMMENT@';
	const REDIRECT_ENTRY_CACHE_EXPIRY = 120;
	
	const EXPIRY_MARGIN = 300;

	const CACHE_DELIMITER = "\r\n\r\n";
	const MIN_CONDITIONAL_CACHE_EXPIRATION = 10;
	
	// warm cache constants
	// cache warming is used to maintain continous use of the request caching while preventing a load once the cache expires
	// during WARM_CACHE_INTERVAL before the cache expiry a single request will be allowed to get through and renew the cache
	// this request named warm cache request will block other such requests for WARM_CACHE_TTL seconds

	// header to mark the request is due to cache warming. the header holds the original request protocol http/https
	const WARM_CACHE_HEADER = "X-KALTURA-WARM-CACHE";

	// interval before cache expiry in which to try and warm the cache
	const WARM_CACHE_INTERVAL = 60;

	// time in which a warm cache request will block another request from warming the cache
	const WARM_CACHE_TTL = 10;
	
	const MAX_CACHE_HEADER_COUNT = 30;

	protected $_cacheStoreTypes = array();
	protected $_cacheStores = array();
	protected $_cacheRules = null;
	protected $_cacheRulesDirty = false;
	protected $_responseMetadata = null;
	protected $_cacheId = null;							// the cache id ensures that the conditions are in sync with the response buffer
	protected $_cacheModes = null;
	protected $_monitorEvents = array();				// holds monitor events that happen before monitorApiStart is called
	protected static $_cacheWarmupInitiated = false;
	protected static $_cacheHeaderCount = 0;
	
	// cache key
	protected $_params = array();
	protected $_cacheKey = "";					// a hash of _params used as the key for caching
	protected $_cacheKeyPrefix = '';			// the prefix of _cacheKey, the cache key is generated by concatenating this prefix with the hash of the params
	protected $_cacheKeyDirty = true;			// true if _params was changed since _cacheKey was calculated
	protected $_originalCacheKey = null;		// the value of the cache key before any extra fields were added to it

	// ks
	protected $_ks = "";
	protected $_ksStatus = kSessionBase::UNKNOWN;
	protected $_ksObj = null;
	protected $_ksPartnerId = null;
	protected $_partnerId = null;
	
	// extra fields
	protected $_referrers = array();				// a request can theoritically have more than one referrer, in case of several baseEntry.getContextData calls in a single multirequest
	protected static $_country = null;				// caches the country of the user issuing this request
	protected static $_coordinates = null;			// caches the latitude and longitude of the user issuing this request
	protected static $_anonymousIPInfo = null;		// caches the anonymous IP info of the user issuing this request
	protected $minCacheTTL = null;
	
	protected $clientTag = null;
	
	protected function __construct($cacheType, $params = null)
	{

		if ($params)
			$this->_params = $params;
		else
			$this->_params = infraRequestUtils::getRequestParams();

		if(isset($this->_params['action'])  && isset($this->_params['service']))
		{
			$cacheType = kConf::getArrayValue($this->_params['service'] . '_' . $this->_params['action'], 'api_v3', 'cache', $cacheType);
		}

		$this->_cacheStoreTypes = kCacheManager::getCacheSectionNames($cacheType);

		parent::__construct();
	}

	protected function init()			// overridable
	{
		if(isset($this->_params['clientTag']))
			$this->clientTag = $this->_params['clientTag'];
		
		$ks = $this->getKs();
		if ($ks === false)
		{
			if (self::$_debugMode)
				$this->debugLog("getKs failed, disabling cache");
			return false;
		}

		// if the request triggering the cache warmup was an https request, fool the code to treat the current request as https as well
		$warmCacheHeader = self::getRequestHeaderValue(self::WARM_CACHE_HEADER);
		if ($warmCacheHeader == "https")
			$_SERVER['HTTPS'] = "on";

		$this->addKsData($ks);
		$this->addInternalCacheParams();
		
		// print the partner id using apache note
		if ($this->_ksPartnerId)
		{
			$this->_partnerId = $this->_ksPartnerId;
		}
		else if (isset($this->_params["partnerId"]))
		{
			$this->_partnerId = $this->_params["partnerId"];
		}
		
		if (!is_numeric($this->_partnerId))
		{
			$this->_partnerId = null;
		}
		
		if ($this->_partnerId && function_exists('apache_note'))
		{
			apache_note("Kaltura_PartnerId", $this->_partnerId);
		}

		if (!kConf::get('enable_cache') ||
			$this->isCacheDisabled())
		{
			if (self::$_debugMode)
				$this->debugLog("cache disabled due to request parameters / configuration");
			return false;
		}

		return true;
	}

	protected function getKs()			// overridable
	{
		$ks = isset($this->_params['ks']) ? $this->_params['ks'] : '';
		unset($this->_params['ks']);
		return $ks;
	}

	protected function addKSData($ks)
	{
		$this->_ks = $ks;
		
		// determine the KS status
		if (empty($ks))
		{
			$this->_ksStatus = kSessionBase::OK;
		}
		else
		{
			$ksObj = new kSessionBase();
			$parseResult = $ksObj->parseKS($ks);
			if ($parseResult)
			{
				$this->_ksStatus = $ksObj->tryToValidateKS();
				if ($this->_ksStatus == kSessionBase::OK)
				{
					$this->_ksObj = $ksObj;
					$this->_ksPartnerId = $ksObj->partner_id;
				}
				else
				{
					$this->_partnerId = $ksObj->partner_id;
				}
			}
			else if ($parseResult === false)
			{
				$this->_ksStatus = kSessionBase::INVALID_STR;
			}
			else	// null
			{
				$this->_ksStatus = kSessionBase::UNKNOWN;
			}
		}

		$this->_params["___cache___partnerId"] =  $this->_ksPartnerId;
		$this->_params["___cache___ksStatus"] =   $this->_ksStatus;
		$this->_params["___cache___ksType"] = 	  ($this->_ksObj ? $this->_ksObj->type		 : null);
		$this->_params["___cache___userId"] =     ($this->_ksObj ? $this->_ksObj->user		 : null);
		$this->_params["___cache___privileges"] = ($this->_ksObj ? $this->_ksObj->privileges : null);
	}

	protected function addInternalCacheParams()
	{
		$this->_params['___cache___protocol'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
		$this->_params['___cache___host'] = @$_SERVER['HTTP_HOST'];
		$this->_params['___cache___version'] = self::CACHE_VERSION;
		$this->_params['___internal'] = intval(kIpAddressUtils::isInternalIp());
		
		if (kConf::hasMap("optimized_playback"))
		{
			$optimizedPlayback = kConf::getMap("optimized_playback");
			if (array_key_exists($this->_ksPartnerId, $optimizedPlayback))
			{
				$params = $optimizedPlayback[$this->_ksPartnerId];
				if (array_key_exists('cache_kdp_access_control', $params) && $params['cache_kdp_access_control'])
				{
					$clientTag = 'none';
					if (strpos(strtolower($this->clientTag), "kdp") !== false || strpos(strtolower($this->clientTag), "html") !== false )
					{
						$clientTag = 'player';
					}
					$this->_params['___cache___clientTag'] = $clientTag;
				}
			}
		}
		
		if ($this->clientTag)
		{
			$matches = null;
			if (preg_match("/cache_st:(\\d+)/", $this->clientTag, $matches))
			{
				if ($matches[1] > time())
				{
					$this->_params['___cache___start'] = $matches[1];
				}
			}
		}
	}

	protected function isCacheDisabled()
	{
		if (isset($this->_params['nocache']))
		{
			return true;
		}

		return false;
	}

	// extra fields
	
	static protected function getGeoCoderFieldValue($extraField, $fieldName, $method)
	{
		if (is_null(self::$$fieldName))
		{
			$geoCoder = kGeoCoderManager::getGeoCoder(is_array($extraField) ? $extraField[self::ECFD_GEO_CODER_TYPE] : null);
			if ($geoCoder)
			{
				$ipAddress = infraRequestUtils::getRemoteAddress();
			
				self::$$fieldName = $geoCoder->$method($ipAddress); 
			}
		}
		
		return self::$$fieldName;
	}
	
	static protected function getExtraFieldType($extraField)
	{
		return is_array($extraField) ? $extraField["type"] : $extraField;
	}

	protected function getFieldValues($extraField)
	{
		switch (self::getExtraFieldType($extraField))
		{
		case self::ECF_REFERRER:
			$values = array();
			// a request can theoritically have more than one referrer, in case of several baseEntry.getContextData calls in a single multirequest
			foreach ($this->_referrers as $referrer)
			{
				$values[] = $referrer;
			}
			return $values;

		case self::ECF_USER_AGENT:
			if (isset($_SERVER['HTTP_USER_AGENT']))
				return array($_SERVER['HTTP_USER_AGENT']);
			break;

		case self::ECF_COUNTRY:
			return array(self::getGeoCoderFieldValue($extraField, "_country", "getCountry"));

		case self::ECF_COORDINATES:
			return array(self::getGeoCoderFieldValue($extraField, "_coordinates", "getCoordinates"));
			
		case self::ECF_ANONYMOUS_IP:
			return array(self::getGeoCoderFieldValue($extraField, "_anonymousIPInfo", "getAnonymousInfo"));

		case self::ECF_IP:
			if (is_array($extraField))
				return array(infraRequestUtils::getIpFromHttpHeader($extraField[self::ECFD_IP_HTTP_HEADER], $extraField[self::ECFD_IP_ACCEPT_INTERNAL_IPS], true));

			return array(infraRequestUtils::getRemoteAddress());
			
		case self::ECF_CDN_REGION:
			return array(kGeoUtils::getCDNRegionFromIP());

		case self::ECF_HTTP_HEADER:
			$headerName = $extraField[self::ECF_HTTP_HEADER];
			if (isset($_SERVER[$headerName]))
			{
				return array($_SERVER[$headerName]);
			}
			break;

		}

		return array();
	}

	protected function applyCondition($fieldValue, $condition, $refValue, $strippedFieldValue)
	{
		switch ($condition)
		{
		case self::COND_MATCH:
		case self::COND_MATCH_ALL:
			if (!count($refValue))
				return null;
				
			if (!is_array($fieldValue))
				return in_array($fieldValue, $refValue);
			
			if ($condition == self::COND_MATCH_ALL) // compare all field values
			{
				foreach($fieldValue as $value)
				{
					if (!in_array($value, $refValue))
						return false;
				}
				return true;
			}
			
			foreach($fieldValue as $value)
			{
				if (in_array($value, $refValue))
					return true;
			}
			return false;
		
		case self::COND_COUNTRY_MATCH:
			if (!count($refValue))
				return null;
			
			return in_array(trim(strtolower($fieldValue), " \n\r\t"), $refValue);
			
		case self::COND_REGEX:
			if (!count($refValue))
				return null;
			foreach($refValue as $curRefValue)
			{
				if ($fieldValue === $curRefValue ||
					preg_match("/$curRefValue/i", $fieldValue))
					return true;
			}
			return false;

		case self::COND_SITE_MATCH:
			$result = (strpos($fieldValue, "kwidget") === false ? '0' : '1');
			if (!count($refValue))
				return $result;
			foreach($refValue as $curRefValue)
			{
				if ($strippedFieldValue === $curRefValue ||
					strpos($strippedFieldValue, "." . $curRefValue) !== false)
					return $result . '1';
			}
			return $result . '0';

		case self::COND_IP_RANGE:
			if (!count($refValue))
				return null;
			foreach($refValue as $curRefValue)
			{
				if (kIpAddressUtils::isIpInRanges($fieldValue, $curRefValue))
					return true;
			}
			return false;
			
		case self::COND_GEO_DISTANCE:
			if (!count($refValue))
				return null;
			foreach($refValue as $curRefValue)
			{
				if (kGeoUtils::isInGeoDistance($fieldValue, $curRefValue))
					return true;
			}
			return false;
		}
		return $strippedFieldValue;
	}

	protected function getConditionKey($condition, $refValue)
	{
		switch ($condition)
		{
		case self::COND_REGEX:
		case self::COND_MATCH:
		case self::COND_MATCH_ALL:
		case self::COND_SITE_MATCH:
		case self::COND_GEO_DISTANCE:
		case self::COND_COUNTRY_MATCH:
			return "_{$condition}_" . implode(',', $refValue);
		case self::COND_IP_RANGE:
			return "_{$condition}_" . implode(',', str_replace('/', '_', $refValue)); // ip range can contain slashes
		}
		return '';
	}

	protected function addExtraFieldsToCacheParams($extraField, $condition, $refValue)
	{
		foreach ($this->getFieldValues($extraField) as $valueIndex => $fieldValue)
		{
			$extraFieldType = self::getExtraFieldType($extraField);
			$extraFieldCacheKey = is_array($extraField) ? json_encode($extraField) : $extraField;
			
			if ($extraFieldType == self::ECF_REFERRER)
				$strippedFieldValue = infraRequestUtils::parseUrlHost($fieldValue);
			else
				$strippedFieldValue = $fieldValue;
			$conditionResult = $this->applyCondition($fieldValue, $condition, $refValue, $strippedFieldValue);
			$key = "___cache___{$extraFieldCacheKey}_{$valueIndex}" . $this->getConditionKey($condition, $refValue);
			$this->_params[$key] = $conditionResult;
		}

		$this->_cacheKeyDirty = true;
	}
	
	protected function addExtraFields()
	{
		$extraFieldsCache = kCacheManager::getSingleLayerCache(kCacheManager::CACHE_TYPE_API_EXTRA_FIELDS);
		if (!$extraFieldsCache)
			return;

		$extraFields = $extraFieldsCache->get(self::EXTRA_KEYS_PREFIX . $this->_cacheKey);
		if (!$extraFields)
			return;

		foreach ($extraFields as $extraFieldParams)
		{
			call_user_func_array(array('kApiCache', 'addExtraField'), $extraFieldParams);
			call_user_func_array(array($this, 'addExtraFieldInternal'), $extraFieldParams);			// the current instance may have not been activated yet
		}

		$this->finalizeCacheKey();
	}

	protected function storeExtraFields()
	{
		if (!$this->_cacheKeyDirty)
			return true;			// no extra fields were added to the cache

		$extraFieldsCache = kCacheManager::getSingleLayerCache(kCacheManager::CACHE_TYPE_API_EXTRA_FIELDS);
		if (!$extraFieldsCache)
		{
			self::disableCache();
			return false;
		}

		if ($extraFieldsCache->set(self::EXTRA_KEYS_PREFIX . $this->_originalCacheKey, $this->_extraFields, self::EXTRA_FIELDS_EXPIRY) === false)
		{
			self::disableCache();
			return false;
		}

		$this->finalizeCacheKey();			// update the cache key to include the extra fields
		return true;
	}

	protected function finalizeCacheKey()
	{
		if (!$this->_cacheKeyDirty)
			return;
		$this->_cacheKeyDirty = false;

		ksort($this->_params);
		$this->_cacheKey = $this->_cacheKeyPrefix . md5( http_build_query($this->_params, '', '&') );		// we have to explicitly set the separator since symfony changes it to '&amp;'
		if (is_null($this->_originalCacheKey))
			$this->_originalCacheKey = $this->_cacheKey;
	}

	// cache read functions
	protected static function getMaxInvalidationTime($invalidationKeys)
	{
		$memcache = kCacheManager::getSingleLayerCache(kCacheManager::CACHE_TYPE_QUERY_CACHE_KEYS);
		if (!$memcache)
			return null;

		$cacheResult = $memcache->multiGet($invalidationKeys);
		if ($cacheResult === false)
			return null;			// failed to get the invalidation keys

		if (!$cacheResult)
			return 0;				// no invalidation keys - no changes occured

		return max($cacheResult);
	}

	protected function validateSqlQueries($sqlConditions)
	{
		if (!$sqlConditions)
			return true;
		
		$dataSources = kConf::get('datasources', 'db', null);
		if (!$dataSources)
			return false;
		
		foreach ($sqlConditions as $configKey => $queries)
		{
			if (!isset($dataSources[$configKey]['connection']['dsn']))
				return false;
			
			$dsn = $dataSources[$configKey]['connection']['dsn'];

			$connStart = microtime(true);
			try
			{
				$pdo = new PDO($dsn, null, null, array(PDO::ATTR_TIMEOUT => 1));
			}
			catch(PDOException $e)
			{
				return false;
			}
			$connTook = microtime(true) - $connStart;

			$this->_monitorEvents[] = array(array('KalturaMonitorClient', 'monitorConnTook'), array($dsn, $connTook));

			// get the host name from the dsn
			list($mysql, $connection) = explode(':', $dsn);
			$arguments = explode(';', $connection);

			$hostName = null;
			foreach($arguments as $argument)
			{
				list($argumentName, $argumentValue) = explode('=', $argument);
				if(strtolower($argumentName) == 'host')
				{
					$hostName = $argumentValue;
					break;
				}
			}
		
			foreach ($queries as $query)
			{
				$sql = $query['sql'];
				
				$comment = (isset($_SERVER["HOSTNAME"]) ? $_SERVER["HOSTNAME"] : gethostname());
				$comment .= "[{$this->_cacheKey}]";
				$sql = str_replace(self::KALTURA_COMMENT_MARKER, $comment, $sql);
				
				$sqlStart = microtime(true);
				$stmt = $pdo->query($sql);
				if(!$stmt)
				{
					return false;
				}
				$sqlTook = microtime(true) - $sqlStart;

				$this->_monitorEvents[] = array(array('KalturaMonitorClient', 'monitorDatabaseAccess'), array($sql, $sqlTook, $hostName));

				if ($query['fetchStyle'] == PDO::FETCH_COLUMN)
					$result = $stmt->fetchAll($query['fetchStyle'], $query['columnIndex']);
				else
					$result = $stmt->fetchAll($query['fetchStyle']);
				
				$filteredResult = self::filterQueryResult($result, $query['filter']);
		
				if ($filteredResult != $query['expectedResult'])
				{
					return false;
				}
			}
		}
	
		return true;
	}
	
	protected function validateCachingRules($isWarmupRequest)
	{
		foreach ($this->_cacheRules as $rule)
		{
			list($cacheExpiry, $expiryInterval, $conditions) = $rule;

			$cacheTTL = $cacheExpiry - time();
			if($cacheTTL <= 0)
			{
				if (self::$_debugMode)
					$this->debugLog("validateCachingRules - cache expired");
				continue;
			}

			if (is_null($this->minCacheTTL) || $cacheTTL < $this->minCacheTTL)
			{
				$this->minCacheTTL = $cacheTTL;
			}

			if ($conditions)
			{
				list($this->_cacheId, $invalidationKeys, $cachedInvalidationTime, $sqlConditions) = $conditions;
				
				if ($invalidationKeys)
				{
					$invalidationTime = self::getMaxInvalidationTime($invalidationKeys);
					if ($invalidationTime === null)
					{
						if (self::$_debugMode)
							$this->debugLog("validateCachingRules - failed to get invalidation time");
						continue;					// failed to get the invalidation time from memcache, can't use cache
					}
	
					if ($cachedInvalidationTime <= $invalidationTime)
					{
						if (self::$_debugMode)
							$this->debugLog("validateCachingRules - invalidation keys changed since the response was cached");
						continue;					// something changed since the response was cached
					}
				}

				if (!$this->validateSqlQueries($sqlConditions))
				{
					if (self::$_debugMode)
						$this->debugLog("validateCachingRules - failed to validate sql queries");
					continue;
				}
				
				if (isset($this->_cacheRules[self::CACHE_MODE_ANONYMOUS]))
				{
					// since the conditions matched, we can extend the expiry of the anonymous cache
					list($cacheExpiry, $expiryInterval, $conditions) = $this->_cacheRules[self::CACHE_MODE_ANONYMOUS];
					$cacheExpiry = time() + $expiryInterval;
					$this->_cacheRules[self::CACHE_MODE_ANONYMOUS] = array($cacheExpiry, $expiryInterval, $conditions);
					$this->_cacheRulesDirty = true;
					
					// in case of multirequest, limit the cache time of the multirequest according to this request
					$this->setExpiry($expiryInterval);
					
					//In case of anonymous request take the cacheTTL to be the min form expiryInterval and the previously calculated cache expiry
					$this->minCacheTTL = min($this->minCacheTTL, $expiryInterval);
				}
				
				if (count(self::$_activeInstances) > 1)
				{
					// in case of multirequest need to add the current conditions to the conditions of the full multirequest
					self::setConditionalCacheExpiry(max($cacheTTL, 5));
					self::addInvalidationKeys($invalidationKeys, $cachedInvalidationTime);
					self::addSqlQueryConditions($sqlConditions);
				}
				
				return self::CACHE_MODE_CONDITIONAL;
			}
			else if ($isWarmupRequest)
			{
				// if there are no conditions and this is a cache warmup request, don't use the cache
				continue;
			}
			else if ($cacheTTL < self::WARM_CACHE_INTERVAL) // 1 minute left for cache, lets warm it
			{
				if (kConf::hasParam('disable_cache_warmup_client_tags') && !in_array($this->clientTag, kConf::get('disable_cache_warmup_client_tags')))
					self::warmCache($this->_cacheKey);
			}
			
			// in case of multirequest, limit the cache time of the multirequest according to this request
			$this->setExpiry($expiryInterval);

			return self::CACHE_MODE_ANONYMOUS;
		}

		return null;
	}

	protected function getCacheStoreForRead()
	{
		if ($this->_ksStatus == kSessionBase::UNKNOWN)
		{
			if (self::$_debugMode)
				$this->debugLog('getCacheStoreForRead ks status is unknown');
			return array(null, null);					// ks not valid, do not return from cache
		}

		// if the request is for warming the cache, disregard the cache and run the request
		$warmCacheHeader = self::getRequestHeaderValue(self::WARM_CACHE_HEADER);
		if ($warmCacheHeader !== false)
		{
			// make a trace in the access log of this being a warmup call
			header("X-Kaltura:cached-warmup-$warmCacheHeader,".$this->_cacheKey, false);
		}
		
		if(is_null($this->_cacheStoreTypes))
			return array(null, null);

		foreach ($this->_cacheStoreTypes as $cacheType)
		{
			$cacheStore = kCacheManager::getCache($cacheType);
			if (!$cacheStore)
			{
				continue;
			}

			$cacheRules = $cacheStore->get($this->_cacheKey . self::SUFFIX_RULES);
			if ($cacheRules)
			{
				$this->_cacheRules = unserialize($cacheRules);
				$cacheMode = $this->validateCachingRules($warmCacheHeader !== false);
				if (!is_null($cacheMode))
				{
					return array($cacheMode, $cacheStore);
				}
			}

			$this->_cacheRules = null;
			$this->_cacheStores[] = $cacheStore;
		}

		if (self::$_debugMode)
			$this->debugLog('cache rules validation failed on all cache stores');
		return array(null, null);
	}

	/**
	 * Enter description here ...
	 * @return string
	 */
	protected function getCurrentSessionType()
	{
		if(!$this->_ksObj)
			return kSessionBase::SESSION_TYPE_NONE;

		if($this->_ksObj->isAdmin())
			return kSessionBase::SESSION_TYPE_ADMIN;

		if($this->_ksObj->isWidgetSession())
			return kSessionBase::SESSION_TYPE_WIDGET;

		return kSessionBase::SESSION_TYPE_USER;
	}

	/**
	 * This functions checks if a certain response resides in cache.
	 * In case it does, the response is returned from cache and a response header is added.
	 * There are two possibilities on which this function is called:
	 * 1)	The request is a single 'stand alone' request (maybe this request is a multi request containing several sub-requests)
	 * 2)	The request is a single request that is part of a multi request (sub-request in a multi request)
	 *
	 * in case this function is called when handling a sub-request (single request as part of a multirequest) it
	 * is preferable to change the default $cacheHeaderName
	 *
	 * @param $cacheHeaderName - the header name to add
	 * @param $cacheHeader - the header value to add
	 */
	public function checkCache($cacheHeaderName = 'X-Kaltura', $cacheHeader = 'cached-dispatcher')
	{
		for ($attempt = 0; $attempt < 20; $attempt++)
		{
			$result = $this->checkCacheInternal($cacheHeaderName, $cacheHeader);
			if ($result !== false)
			{
				break;
			}

			if (!self::$_lockEnabled)
			{
				break;
			}

			if (kApcWrapper::functionExists('add') && kApcWrapper::apcAdd('apiCacheLock-'.$this->_cacheKey, true, 1))
			{
				break;
			}

			KalturaMonitorClient::usleep(50000);
		}

		$action = null;
		if (get_class($this) == 'kPlayManifestCacher')
		{
			$action = 'extwidget.playManifest';
		}
		else if (isset($this->_params['service']) && isset($this->_params['action']) && $this->_params['service'] != 'multirequest')
		{
			$action = $this->_params['service'] . '.' . $this->_params['action'];
		}

		if ($action)
		{
			$isInMultiRequest = isset($this->_params['multirequest']);
			KalturaMonitorClient::monitorApiStart($result !== false, $action, $this->_partnerId, $this->getCurrentSessionType(), $this->clientTag, $isInMultiRequest);

			foreach ($this->_monitorEvents as $event)
			{
				list($func, $params) = $event;

				call_user_func_array($func, $params);
			}

			if ($result !== false)
			{
				KalturaMonitorClient::flushPacket();
			}
		}
		
		return $result;
	}

	protected function checkCacheInternal($cacheHeaderName, $cacheHeader)
	{
		if ($this->_cacheStatus == self::CACHE_STATUS_DISABLED)
			return false;

		$this->_cacheStores = array();
		$startTime = microtime(true);
		list($cacheMode, $cacheStore) = $this->getCacheStoreForRead();
		if (!$cacheStore)
		{
			return false;
		}

		$cacheResult = $cacheStore->get($this->_cacheKey . self::SUFFIX_DATA);
		if (!$cacheResult)
		{
			if (self::$_debugMode)
				$this->debugLog('failed to get cached data from cache');
			$this->_cacheStores[] = $cacheStore;
			return false;
		}

		list($cacheId, $responseMetadata, $response) = explode(self::CACHE_DELIMITER, $cacheResult, 3);
		if ($this->_cacheId && $this->_cacheId != $cacheId)
		{
			if (self::$_debugMode)
				$this->debugLog('response cache id does not match the cache id of the rules');
			$this->_cacheStores[] = $cacheStore;
			return false;
		}

		if($responseMetadata)
			$this->_responseMetadata = unserialize($responseMetadata);
		else
			$this->_responseMetadata = array();

		if ($this->_cacheRulesDirty)
		{
			$maxExpiry = $this->getMaxExpiryFromRules();
			$cacheStore->set($this->_cacheKey . self::SUFFIX_RULES, serialize($this->_cacheRules), $maxExpiry + self::EXPIRY_MARGIN);
		}

		$this->saveToCacheStores($response);

		if ($cacheMode == self::CACHE_MODE_ANONYMOUS)
		{
			// in case of multirequest, we must not condtionally cache the multirequest when a sub request comes 
			// from anonymous cache. for single requests, the next line has no effect
			self::disableConditionalCache();
		}

		$processingTime = microtime(true) - $startTime;
		if (self::hasExtraFields() && $cacheHeaderName == 'X-Kaltura')
			$cacheHeader = 'cached-with-extra-fields';
		if (self::$_cacheHeaderCount < self::MAX_CACHE_HEADER_COUNT)
			header("$cacheHeaderName:$cacheHeader,$this->_cacheKey,$processingTime", false);
		self::$_cacheHeaderCount++;
		
		// remove $this from the list of active instances - the request is complete
		$this->removeFromActiveList();
		
		return $response;
	}

	// cache write functions
	protected function isAnonymous($ks)					// overridable
	{
		if(kIpAddressUtils::isInternalIp())
		{
			return false;
		}

		if (!$ks)
			return true;

		if(!$ks->isAdmin() && ($ks->user === "0" || $ks->user === null ))
		{
			$privileges = $ks->getParsedPrivileges();
			if (!$privileges || !array_key_exists (kSessionBase::PRIVILEGE_SET_ROLE,$privileges))
				return true;

			if (kConf::hasParam('anonymous_roles_to_cache'))
			{
				$ksRoles = $privileges[kSessionBase::PRIVILEGE_SET_ROLE];
				$rolesToCacheList = kConf::get('anonymous_roles_to_cache');
				foreach ($rolesToCacheList as $roleKey => $roleValue)
				{
					if (is_array($ksRoles) && in_array($roleKey, $ksRoles))
						return true;
				}
			}

			return false;
		}

		if (kConf::hasParam('cache_anonymous_users'))
		{
			$anonymousUsers = kConf::get('cache_anonymous_users');
			foreach ($anonymousUsers as $userName => $partnerIds)
			{
				if ($ks->user == $userName && in_array($ks->partner_id, explode(',', $partnerIds)))
					return true;
			}
		}
		
		return false;
	}

	protected function getAnonymousCachingExpiry()		// overridable
	{
		return $this->_expiry;
	}

	protected function initCacheModes()
	{
		if (!is_null($this->_cacheModes))
			return;

		$this->_cacheModes = array();
		if ($this->_cacheStatus == self::CACHE_STATUS_DISABLED)
			return;

		if ($this->_ksStatus == kSessionBase::UNKNOWN)
		{
			if (self::$_debugMode)
				$this->debugLog('ks status is unknown - not saving to cache');
				
			self::disableCache();
			return;
		}

		$isAnonymous = $this->isAnonymous($this->_ksObj);
		if (!$isAnonymous && $this->_cacheStatus == self::CACHE_STATUS_ANONYMOUS_ONLY)
		{
			if (self::$_debugMode)
				$this->debugLog('request is not anonymous and conditional cache was disabled');
			
			self::disableCache();
			return;
		}

		if ($isAnonymous)
			$this->_cacheModes[] = self::CACHE_MODE_ANONYMOUS;

		if ($this->_cacheStatus != self::CACHE_STATUS_ANONYMOUS_ONLY)
			$this->_cacheModes[] = self::CACHE_MODE_CONDITIONAL;
	}

	protected function calculateCacheRules()
	{
		$this->_cacheRules = array();
		foreach ($this->_cacheModes as $cacheMode)
		{
			$conditions = null;

			switch ($cacheMode)
			{
			case self::CACHE_MODE_CONDITIONAL:
				$conditions = array($this->_cacheId, array_keys($this->_invalidationKeys), $this->_invalidationTime, $this->_sqlConditions);
				if ($this->_conditionalCacheExpiry)
					$expiry = $this->_conditionalCacheExpiry;
				else
					$expiry = self::CONDITIONAL_CACHE_EXPIRY;
				break;

			case self::CACHE_MODE_ANONYMOUS:
				$expiry = $this->getAnonymousCachingExpiry();
				if (!$expiry)
					$expiry = self::ANONYMOUS_CACHE_EXPIRY;
				break;
			}

			$this->_cacheRules[$cacheMode] = array(time() + $expiry, $expiry, $conditions);
		}
	}

	public function storeCache($response, $responseMetadata = "", $serializeResponse = false)
	{
		// remove $this from the list of active instances - the request is complete
		$this->removeFromActiveList();

		$this->initCacheModes();
		if (!$this->_cacheModes)
			return;

		if ($serializeResponse)
			$response = serialize($response);

		if (!$this->storeExtraFields())
			return;

		// set the X-Kaltura header only if it does not exist or contains 'cache-key'
		// the header is overwritten for cache-key so that for a multirequest we'll get the key of
		// the entire request and not just the last request
		$headers = headers_list();
		$foundHeader = false;
		foreach($headers as $header)
		{
			if (strpos($header, 'X-Kaltura:') === 0 && strpos($header, 'cache-key') === false)
			{
				$foundHeader = true;
				break;
			}
		}

		if (!$foundHeader)
			header("X-Kaltura: cache-key,".$this->_cacheKey);
		
		$this->_responseMetadata = $responseMetadata;
		$this->_cacheId = microtime(true) . '_' . getmypid();

		$this->calculateCacheRules();

		$this->saveToCacheStores($response);
	}

	protected function saveToCacheStores($response)
	{
		$maxExpiry = $this->getMaxExpiryFromRules();

		foreach ($this->_cacheStores as $curCacheStore)
		{
			if (self::$_debugMode)
				$curCacheStore->set($this->_cacheKey . self::SUFFIX_LOG, print_r($this->_params, true), $maxExpiry + self::EXPIRY_MARGIN);
			$curCacheStore->set($this->_cacheKey . self::SUFFIX_RULES, serialize($this->_cacheRules), $maxExpiry + self::EXPIRY_MARGIN);
			$responseMetadata = $this->_responseMetadata ? serialize($this->_responseMetadata) : '';
			$curCacheStore->set($this->_cacheKey . self::SUFFIX_DATA, implode(self::CACHE_DELIMITER, array($this->_cacheId, $responseMetadata, $response)), $maxExpiry);
		}
	}

	// cache warmup functions
	protected static function getRequestHeaders()
	{
		if(function_exists('apache_request_headers'))
			return apache_request_headers();

		foreach($_SERVER as $key => $value)
		{
			if(substr($key, 0, 5) == "HTTP_")
			{
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
				$out[$key] = $value;
			}
		}
		return $out;
	}

	// warm cache by sending the current request asynchronously via a socket to localhost
	// apc is used to flag that an existing warmup request is already running. The flag has a TTL of 10 seconds,
	// so in the case the warmup request failed another one can be ran after 10 seconds.
	// finalize IP passing (use getRemoteAddr code)
	// can the warm cache header get received via a warm request passed from the other DC?
	protected function warmCache($key)
	{
		if (self::$_cacheWarmupInitiated)
			return;

		self::$_cacheWarmupInitiated = true;		
		
		$key = "cache-warmup-$key";
		
		$cacheSections = kCacheManager::getCacheSectionNames(kCacheManager::CACHE_TYPE_API_WARMUP);
		if (!$cacheSections)
			return;
		
		foreach ($cacheSections as $cacheSection)
		{
			$cacheStore = kCacheManager::getCache($cacheSection);
			if (!$cacheStore)
				return;
		
			// abort warming if a previous warmup started less than 10 seconds ago
			if ($cacheStore->get($key) !== false)
				return;
			
			// flag we are running a warmup for the current request
			$cacheStore->set($key, true, self::WARM_CACHE_TTL);
		}
		
		$uri = $_SERVER["REQUEST_URI"];

		$fp = fsockopen(kConf::get('api_cache_warmup_host'), 80, $errno, $errstr, 1);

		if ($fp === false)
		{
			error_log("warmCache - Couldn't open a socket [".$uri."]", 0);
			return;
		}

		$method = $_SERVER["REQUEST_METHOD"];

		$out = "$method $uri HTTP/1.1\r\n";

		$sentHeaders = self::getRequestHeaders();
		$sentHeaders["Connection"] = "Close";

		// mark request as a warm cache request in order to disable caching and pass the http/https protocol (the warmup always uses http)
		$sentHeaders[self::WARM_CACHE_HEADER] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";

		// if the request wasn't proxied pass the ip on the X-FORWARDED-FOR header
		$ipHeader = infraRequestUtils::getSignedIpAddressHeader();
		if ($ipHeader)
		{
			list($headerName, $headerValue) = $ipHeader;
			$sentHeaders[$headerName] = $headerValue;
		}

		foreach($sentHeaders as $header => $value)
		{
			$out .= "$header:$value\r\n";
		}

		$out .= "\r\n";

		if ($method == "POST")
		{
			$postParams = array();
			foreach ($_POST as $key => &$val) {
				if (is_array($val)) $val = implode(',', $val);
				{
					$postParams[] = $key.'='.urlencode($val);
				}
			}

			$out .= implode('&', $postParams);
		}

		fwrite($fp, $out);
		fclose($fp);
	}

	// utility functions
	protected function getMaxExpiryFromRules()
	{
		$maxExpiry = 0;
		$curTime = time();
		foreach ($this->_cacheRules as $cacheRule)
		{
			$expiryTime = reset($cacheRule);
			$maxExpiry = max($maxExpiry, $expiryTime - $curTime);		// expiryTime-curTime may be negative, but it doesn't matter since maxExpiry was initialized to 0
		}

		return $maxExpiry;
	}

	protected static function getRequestHeaderValue($headerName)
	{
		$headerName = "HTTP_".str_replace("-", "_", strtoupper($headerName));

		if (!isset($_SERVER[$headerName]))
			return false;

		return $_SERVER[$headerName];
	}
	
	public static function limitConditionalCacheTimeToKs()
	{
		$ksObj = kCurrentContext::$ks_object;
		if(!$ksObj)
		{
			return;
		}
		$timeDiff = $ksObj->valid_until - time() - self::MIN_CONDITIONAL_CACHE_EXPIRATION;
		if($timeDiff > 0)
		{
			self::setConditionalCacheExpiry($timeDiff);
		}
		else
		{
			self::disableConditionalCache();
		}
	}	
}
