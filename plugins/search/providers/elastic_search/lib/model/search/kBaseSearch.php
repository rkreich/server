<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */

abstract class kBaseSearch
{

	const GLOBAL_HIGHLIGHT_CONFIG = 'globalMaxNumberOfFragments';

    protected $elasticClient;
    protected $query;
    protected $queryAttributes;
	protected $mainBoolQuery;

    public function __construct()
    {
        $this->elasticClient = new elasticClient();
        $this->queryAttributes = new ESearchQueryAttributes();
		$this->mainBoolQuery = new kESearchBoolQuery();
    }

    public abstract function doSearch(ESearchOperator $eSearchOperator, $statuses = array(), $objectId, kPager $pager = null, ESearchOrderBy $order = null, $useHighlight = true);

    public abstract function getPeerName();

	public abstract function getPeerRetrieveFunctionName();

	protected function handleDisplayInSearch()
	{
	}

    protected function execSearch(ESearchOperator $eSearchOperator)
    {
        $subQuery = $eSearchOperator->createSearchQuery($eSearchOperator->getSearchItems(), null, $this->queryAttributes, $eSearchOperator->getOperator());
        $this->handleDisplayInSearch();
        $this->mainBoolQuery->addToMust($subQuery);
        $this->applyElasticSearchConditions();
        $this->addGlobalHighlights();
        KalturaLog::debug("Elasticsearch query [".print_r($this->query, true)."]");
        $result = $this->elasticClient->search($this->query);
        return $result;
    }

    protected function initQuery(array $statuses, $objectId, kPager $pager = null, ESearchOrderBy $order = null, $useHighlight = true)
    {
        $partnerId = kBaseElasticEntitlement::$partnerId;
        $this->initQueryAttributes($partnerId, $objectId, $useHighlight);
        $this->initBaseFilter($partnerId, $statuses, $objectId);
        $this->initPager($pager);
        $this->initOrderBy($order);
    }

    protected function initPager(kPager $pager = null)
    {
        if($pager)
        {
            $this->query['from'] = $pager->calcOffset();
            $this->query['size'] = $pager->calcPageSize();
        }
    }

    protected function initOrderBy(ESearchOrderBy $order = null)
    {
        if($order)
        {
            $orderItems = $order->getOrderItems();
            $fields = array();
            $sortConditions = array();
            foreach ($orderItems as $orderItem)
            {
                $field = $orderItem->getSortField();
                if(isset($fields[$field]))
                {
                    KalturaLog::log("Order by condition already set for field [$field]" );
                    continue;
                }
                $fields[$field] = true;
                $sortConditions[] = array(
                    $field => array('order' => $orderItem->getSortOrder())
                );
            }

            if(count($sortConditions))
                $sortConditions[] = '_score';

            $this->query['body']['sort'] = $sortConditions;
        }
    }

    protected function initBaseFilter($partnerId, array $statuses, $objectId)
    {
        $partnerStatus = array();
        foreach ($statuses as $status)
        {
            $partnerStatus[] = elasticSearchUtils::formatPartnerStatus($partnerId, $status);
        }

		$partnerStatusQuery = new kESearchTermsQuery('partner_status', $partnerStatus);
		$this->mainBoolQuery->addToFilter($partnerStatusQuery);
		
        if($objectId)
        {
			$id = elasticSearchUtils::formatSearchTerm($objectId);
			$idQuery = new kESearchTermQuery('_id', $id);
			$this->mainBoolQuery->addToFilter($idQuery);
        }

        //return only the object id
        $this->query['body']['_source'] = false;
    }

    protected function addGlobalHighlights()
	{
		$this->queryAttributes->setScopeToGlobal();
		$highlight = self::getHighlightSection(self::GLOBAL_HIGHLIGHT_CONFIG, $this->queryAttributes);
		if(isset($highlight))
		{
			$this->query['body']['highlight'] = $highlight;
		}
	}

	public static function getHighlightSection($configKey, $queryAttributes)
	{
		$highlight = null;
		$fieldsToHighlight = $queryAttributes->getFieldsToHighlight();
		if(!empty($fieldsToHighlight) && $queryAttributes->getUseHighlight())
		{
			$highlight = array();
			$highlight["type"] = "unified";
			$highlight["order"] = "score";
			$HighlightConfig = kConf::get('highlights', 'elastic');
			if(isset($HighlightConfig[$configKey]))
				$highlight['number_of_fragments'] = $HighlightConfig[$configKey];

			$highlight['fields'] = $fieldsToHighlight;
		}

		return $highlight;
	}

    protected function applyElasticSearchConditions()
    {
        $this->query['body']['query'] = $this->mainBoolQuery->getFinalQuery();
    }

    protected function initQueryAttributes($partnerId, $objectId, $useHighlight)
    {
        $this->initPartnerLanguages($partnerId);
        $this->queryAttributes->setUseHighlight($useHighlight);
        $this->queryAttributes->setObjectId($objectId);
        $this->queryAttributes->setShouldUseDisplayInSearch(true);
        $this->initOverrideInnerHits($objectId);
    }

    protected function initPartnerLanguages($partnerId)
    {
        $partner = PartnerPeer::retrieveByPK($partnerId);
        if(!$partner)
            return;

        $partnerLanguages = $partner->getESearchLanguages();
        if(!count($partnerLanguages))
        {
            //if no languages are set for partner - set the default to english
            $partnerLanguages = array('english');
        }

        $this->queryAttributes->setPartnerLanguages($partnerLanguages);
    }

    protected function initOverrideInnerHits($objectId)
    {
        if(!$objectId)
            return;

        $innerHitsConfig = kConf::get('innerHits', 'elastic');
        $overrideInnerHitsSize = isset($innerHitsConfig['innerHitsWithObjectId']) ? $innerHitsConfig['innerHitsWithObjectId'] : null;
        $this->queryAttributes->setOverrideInnerHitsSize($overrideInnerHitsSize);
    }

}
