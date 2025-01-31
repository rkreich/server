<?php

/**
 * Base class that represents a row from the 'track_entry' table.
 *
 * 
 *
 * @package Core
 * @subpackage model.om
 */
abstract class BaseTrackEntry extends BaseObject  implements Persistent {


	/**
	 * The Peer class.
	 * Instance provides a convenient way of calling static methods on a class
	 * that calling code may not be able to identify.
	 * @var        TrackEntryPeer
	 */
	protected static $peer;

	/**
	 * The value for the id field.
	 * @var        int
	 */
	protected $id;

	/**
	 * The value for the track_event_type_id field.
	 * @var        int
	 */
	protected $track_event_type_id;

	/**
	 * The value for the ps_version field.
	 * @var        string
	 */
	protected $ps_version;

	/**
	 * The value for the context field.
	 * @var        string
	 */
	protected $context;

	/**
	 * The value for the partner_id field.
	 * @var        int
	 */
	protected $partner_id;

	/**
	 * The value for the entry_id field.
	 * @var        string
	 */
	protected $entry_id;

	/**
	 * The value for the host_name field.
	 * @var        string
	 */
	protected $host_name;

	/**
	 * The value for the uid field.
	 * @var        string
	 */
	protected $uid;

	/**
	 * The value for the track_event_status_id field.
	 * @var        int
	 */
	protected $track_event_status_id;

	/**
	 * The value for the changed_properties field.
	 * @var        string
	 */
	protected $changed_properties;

	/**
	 * The value for the param_1_str field.
	 * @var        string
	 */
	protected $param_1_str;

	/**
	 * The value for the param_2_str field.
	 * @var        string
	 */
	protected $param_2_str;

	/**
	 * The value for the param_3_str field.
	 * @var        string
	 */
	protected $param_3_str;

	/**
	 * The value for the ks field.
	 * @var        string
	 */
	protected $ks;

	/**
	 * The value for the description field.
	 * @var        string
	 */
	protected $description;

	/**
	 * The value for the created_at field.
	 * @var        string
	 */
	protected $created_at;

	/**
	 * The value for the updated_at field.
	 * @var        string
	 */
	protected $updated_at;

	/**
	 * The value for the user_ip field.
	 * @var        string
	 */
	protected $user_ip;

	/**
	 * The value for the custom_data field.
	 * @var        string
	 */
	protected $custom_data;

	/**
	 * Flag to prevent endless save loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected $alreadyInSave = false;

	/**
	 * Flag to indicate if save action actually affected the db.
	 * @var        boolean
	 */
	protected $objectSaved = false;

	/**
	 * Flag to prevent endless validation loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected $alreadyInValidation = false;

	/**
	 * Store columns old values before the changes
	 * @var        array
	 */
	protected $oldColumnsValues = array();
	
	/**
	 * @return array
	 */
	public function getColumnsOldValues()
	{
		return $this->oldColumnsValues;
	}
	
	/**
	 * @return mixed field value or null
	 */
	public function getColumnsOldValue($name)
	{
		if(isset($this->oldColumnsValues[$name]))
			return $this->oldColumnsValues[$name];
			
		return null;
	}

	/**
	 * Get the [id] column value.
	 * 
	 * @return     int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the [track_event_type_id] column value.
	 * 
	 * @return     int
	 */
	public function getTrackEventTypeId()
	{
		return $this->track_event_type_id;
	}

	/**
	 * Get the [ps_version] column value.
	 * 
	 * @return     string
	 */
	public function getPsVersion()
	{
		return $this->ps_version;
	}

	/**
	 * Get the [context] column value.
	 * 
	 * @return     string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Get the [partner_id] column value.
	 * 
	 * @return     int
	 */
	public function getPartnerId()
	{
		return $this->partner_id;
	}

	/**
	 * Get the [entry_id] column value.
	 * 
	 * @return     string
	 */
	public function getEntryId()
	{
		return $this->entry_id;
	}

	/**
	 * Get the [host_name] column value.
	 * 
	 * @return     string
	 */
	public function getHostName()
	{
		return $this->host_name;
	}

	/**
	 * Get the [uid] column value.
	 * 
	 * @return     string
	 */
	public function getUid()
	{
		return $this->uid;
	}

	/**
	 * Get the [track_event_status_id] column value.
	 * 
	 * @return     int
	 */
	public function getTrackEventStatusId()
	{
		return $this->track_event_status_id;
	}

	/**
	 * Get the [changed_properties] column value.
	 * 
	 * @return     string
	 */
	public function getChangedProperties()
	{
		return $this->changed_properties;
	}

	/**
	 * Get the [param_1_str] column value.
	 * 
	 * @return     string
	 */
	public function getParam1Str()
	{
		return $this->param_1_str;
	}

	/**
	 * Get the [param_2_str] column value.
	 * 
	 * @return     string
	 */
	public function getParam2Str()
	{
		return $this->param_2_str;
	}

	/**
	 * Get the [param_3_str] column value.
	 * 
	 * @return     string
	 */
	public function getParam3Str()
	{
		return $this->param_3_str;
	}

	/**
	 * Get the [ks] column value.
	 * 
	 * @return     string
	 */
	public function getKs()
	{
		return $this->ks;
	}

	/**
	 * Get the [description] column value.
	 * 
	 * @return     string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Get the [optionally formatted] temporal [created_at] column value.
	 * 
	 * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
	 * option in order to avoid converstions to integers (which are limited in the dates they can express).
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw unix timestamp integer will be returned.
	 * @return     mixed Formatted date/time value as string or (integer) unix timestamp (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getCreatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->created_at === null) {
			return null;
		}


		if ($this->created_at === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->created_at);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->created_at, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [optionally formatted] temporal [updated_at] column value.
	 * 
	 * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
	 * option in order to avoid converstions to integers (which are limited in the dates they can express).
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw unix timestamp integer will be returned.
	 * @return     mixed Formatted date/time value as string or (integer) unix timestamp (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getUpdatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->updated_at === null) {
			return null;
		}


		if ($this->updated_at === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->updated_at);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->updated_at, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [user_ip] column value.
	 * 
	 * @return     string
	 */
	public function getUserIp()
	{
		return $this->user_ip;
	}

	/**
	 * Get the [custom_data] column value.
	 * 
	 * @return     string
	 */
	public function getCustomData()
	{
		return $this->custom_data;
	}

	/**
	 * Set the value of [id] column.
	 * 
	 * @param      int $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setId($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::ID]))
			$this->oldColumnsValues[TrackEntryPeer::ID] = $this->id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = TrackEntryPeer::ID;
		}

		return $this;
	} // setId()

	/**
	 * Set the value of [track_event_type_id] column.
	 * 
	 * @param      int $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setTrackEventTypeId($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::TRACK_EVENT_TYPE_ID]))
			$this->oldColumnsValues[TrackEntryPeer::TRACK_EVENT_TYPE_ID] = $this->track_event_type_id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->track_event_type_id !== $v) {
			$this->track_event_type_id = $v;
			$this->modifiedColumns[] = TrackEntryPeer::TRACK_EVENT_TYPE_ID;
		}

		return $this;
	} // setTrackEventTypeId()

	/**
	 * Set the value of [ps_version] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setPsVersion($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::PS_VERSION]))
			$this->oldColumnsValues[TrackEntryPeer::PS_VERSION] = $this->ps_version;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->ps_version !== $v) {
			$this->ps_version = $v;
			$this->modifiedColumns[] = TrackEntryPeer::PS_VERSION;
		}

		return $this;
	} // setPsVersion()

	/**
	 * Set the value of [context] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setContext($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::CONTEXT]))
			$this->oldColumnsValues[TrackEntryPeer::CONTEXT] = $this->context;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->context !== $v) {
			$this->context = $v;
			$this->modifiedColumns[] = TrackEntryPeer::CONTEXT;
		}

		return $this;
	} // setContext()

	/**
	 * Set the value of [partner_id] column.
	 * 
	 * @param      int $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setPartnerId($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::PARTNER_ID]))
			$this->oldColumnsValues[TrackEntryPeer::PARTNER_ID] = $this->partner_id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->partner_id !== $v) {
			$this->partner_id = $v;
			$this->modifiedColumns[] = TrackEntryPeer::PARTNER_ID;
		}

		return $this;
	} // setPartnerId()

	/**
	 * Set the value of [entry_id] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setEntryId($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::ENTRY_ID]))
			$this->oldColumnsValues[TrackEntryPeer::ENTRY_ID] = $this->entry_id;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->entry_id !== $v) {
			$this->entry_id = $v;
			$this->modifiedColumns[] = TrackEntryPeer::ENTRY_ID;
		}

		return $this;
	} // setEntryId()

	/**
	 * Set the value of [host_name] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setHostName($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::HOST_NAME]))
			$this->oldColumnsValues[TrackEntryPeer::HOST_NAME] = $this->host_name;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->host_name !== $v) {
			$this->host_name = $v;
			$this->modifiedColumns[] = TrackEntryPeer::HOST_NAME;
		}

		return $this;
	} // setHostName()

	/**
	 * Set the value of [uid] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setUid($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::UID]))
			$this->oldColumnsValues[TrackEntryPeer::UID] = $this->uid;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->uid !== $v) {
			$this->uid = $v;
			$this->modifiedColumns[] = TrackEntryPeer::UID;
		}

		return $this;
	} // setUid()

	/**
	 * Set the value of [track_event_status_id] column.
	 * 
	 * @param      int $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setTrackEventStatusId($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::TRACK_EVENT_STATUS_ID]))
			$this->oldColumnsValues[TrackEntryPeer::TRACK_EVENT_STATUS_ID] = $this->track_event_status_id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->track_event_status_id !== $v) {
			$this->track_event_status_id = $v;
			$this->modifiedColumns[] = TrackEntryPeer::TRACK_EVENT_STATUS_ID;
		}

		return $this;
	} // setTrackEventStatusId()

	/**
	 * Set the value of [changed_properties] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setChangedProperties($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::CHANGED_PROPERTIES]))
			$this->oldColumnsValues[TrackEntryPeer::CHANGED_PROPERTIES] = $this->changed_properties;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->changed_properties !== $v) {
			$this->changed_properties = $v;
			$this->modifiedColumns[] = TrackEntryPeer::CHANGED_PROPERTIES;
		}

		return $this;
	} // setChangedProperties()

	/**
	 * Set the value of [param_1_str] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setParam1Str($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::PARAM_1_STR]))
			$this->oldColumnsValues[TrackEntryPeer::PARAM_1_STR] = $this->param_1_str;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->param_1_str !== $v) {
			$this->param_1_str = $v;
			$this->modifiedColumns[] = TrackEntryPeer::PARAM_1_STR;
		}

		return $this;
	} // setParam1Str()

	/**
	 * Set the value of [param_2_str] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setParam2Str($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::PARAM_2_STR]))
			$this->oldColumnsValues[TrackEntryPeer::PARAM_2_STR] = $this->param_2_str;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->param_2_str !== $v) {
			$this->param_2_str = $v;
			$this->modifiedColumns[] = TrackEntryPeer::PARAM_2_STR;
		}

		return $this;
	} // setParam2Str()

	/**
	 * Set the value of [param_3_str] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setParam3Str($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::PARAM_3_STR]))
			$this->oldColumnsValues[TrackEntryPeer::PARAM_3_STR] = $this->param_3_str;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->param_3_str !== $v) {
			$this->param_3_str = $v;
			$this->modifiedColumns[] = TrackEntryPeer::PARAM_3_STR;
		}

		return $this;
	} // setParam3Str()

	/**
	 * Set the value of [ks] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setKs($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::KS]))
			$this->oldColumnsValues[TrackEntryPeer::KS] = $this->ks;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->ks !== $v) {
			$this->ks = $v;
			$this->modifiedColumns[] = TrackEntryPeer::KS;
		}

		return $this;
	} // setKs()

	/**
	 * Set the value of [description] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setDescription($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::DESCRIPTION]))
			$this->oldColumnsValues[TrackEntryPeer::DESCRIPTION] = $this->description;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->description !== $v) {
			$this->description = $v;
			$this->modifiedColumns[] = TrackEntryPeer::DESCRIPTION;
		}

		return $this;
	} // setDescription()

	/**
	 * Sets the value of [created_at] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.  Empty string will
	 *						be treated as NULL for temporal objects.
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setCreatedAt($v)
	{
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->created_at !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($this->created_at !== null && $tmpDt = new DateTime($this->created_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d H:i:s') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$this->created_at = ($dt ? $dt->format('Y-m-d H:i:s') : null);
				$this->modifiedColumns[] = TrackEntryPeer::CREATED_AT;
			}
		} // if either are not null

		return $this;
	} // setCreatedAt()

	/**
	 * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.  Empty string will
	 *						be treated as NULL for temporal objects.
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setUpdatedAt($v)
	{
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->updated_at !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($this->updated_at !== null && $tmpDt = new DateTime($this->updated_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d H:i:s') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$this->updated_at = ($dt ? $dt->format('Y-m-d H:i:s') : null);
				$this->modifiedColumns[] = TrackEntryPeer::UPDATED_AT;
			}
		} // if either are not null

		return $this;
	} // setUpdatedAt()

	/**
	 * Set the value of [user_ip] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setUserIp($v)
	{
		if(!isset($this->oldColumnsValues[TrackEntryPeer::USER_IP]))
			$this->oldColumnsValues[TrackEntryPeer::USER_IP] = $this->user_ip;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->user_ip !== $v) {
			$this->user_ip = $v;
			$this->modifiedColumns[] = TrackEntryPeer::USER_IP;
		}

		return $this;
	} // setUserIp()

	/**
	 * Set the value of [custom_data] column.
	 * 
	 * @param      string $v new value
	 * @return     TrackEntry The current object (for fluent API support)
	 */
	public function setCustomData($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->custom_data !== $v) {
			$this->custom_data = $v;
			$this->modifiedColumns[] = TrackEntryPeer::CUSTOM_DATA;
		}

		return $this;
	} // setCustomData()

	/**
	 * Indicates whether the columns in this object are only set to default values.
	 *
	 * This method can be used in conjunction with isModified() to indicate whether an object is both
	 * modified _and_ has some values set which are non-default.
	 *
	 * @return     boolean Whether the columns in this object are only been set with default values.
	 */
	public function hasOnlyDefaultValues()
	{
		// otherwise, everything was equal, so return TRUE
		return true;
	} // hasOnlyDefaultValues()

	/**
	 * Hydrates (populates) the object variables with values from the database resultset.
	 *
	 * An offset (0-based "start column") is specified so that objects can be hydrated
	 * with a subset of the columns in the resultset rows.  This is needed, for example,
	 * for results of JOIN queries where the resultset row includes columns from two or
	 * more tables.
	 *
	 * @param      array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
	 * @param      int $startcol 0-based offset column which indicates which restultset column to start with.
	 * @param      boolean $rehydrate Whether this object is being re-hydrated from the database.
	 * @return     int next starting column
	 * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
	 */
	public function hydrate($row, $startcol = 0, $rehydrate = false)
	{
		$this->last_hydrate_time = time();

		try {

			$this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
			$this->track_event_type_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
			$this->ps_version = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
			$this->context = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
			$this->partner_id = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
			$this->entry_id = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
			$this->host_name = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
			$this->uid = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
			$this->track_event_status_id = ($row[$startcol + 8] !== null) ? (int) $row[$startcol + 8] : null;
			$this->changed_properties = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
			$this->param_1_str = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
			$this->param_2_str = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
			$this->param_3_str = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
			$this->ks = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
			$this->description = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
			$this->created_at = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
			$this->updated_at = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
			$this->user_ip = ($row[$startcol + 17] !== null) ? (string) $row[$startcol + 17] : null;
			$this->custom_data = ($row[$startcol + 18] !== null) ? (string) $row[$startcol + 18] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

			// FIXME - using NUM_COLUMNS may be clearer.
			return $startcol + 19; // 19 = TrackEntryPeer::NUM_COLUMNS - TrackEntryPeer::NUM_LAZY_LOAD_COLUMNS).

		} catch (Exception $e) {
			throw new PropelException("Error populating TrackEntry object", $e);
		}
	}

	/**
	 * Checks and repairs the internal consistency of the object.
	 *
	 * This method is executed after an already-instantiated object is re-hydrated
	 * from the database.  It exists to check any foreign keys to make sure that
	 * the objects related to the current object are correct based on foreign key.
	 *
	 * You can override this method in the stub class, but you should always invoke
	 * the base method from the overridden method (i.e. parent::ensureConsistency()),
	 * in case your model changes.
	 *
	 * @throws     PropelException
	 */
	public function ensureConsistency()
	{

	} // ensureConsistency

	/**
	 * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
	 *
	 * This will only work if the object has been saved and has a valid primary key set.
	 *
	 * @param      boolean $deep (optional) Whether to also de-associated any related objects.
	 * @param      PropelPDO $con (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - if this object is deleted, unsaved or doesn't have pk match in db
	 */
	public function reload($deep = false, PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("Cannot reload a deleted object.");
		}

		if ($this->isNew()) {
			throw new PropelException("Cannot reload an unsaved object.");
		}

		if ($con === null) {
			$con = Propel::getConnection(TrackEntryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		TrackEntryPeer::setUseCriteriaFilter(false);
		$stmt = TrackEntryPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		TrackEntryPeer::setUseCriteriaFilter(true);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); // rehydrate

		if ($deep) {  // also de-associate any related objects?

		} // if (deep)
	}

	/**
	 * Removes this object from datastore and sets delete attribute.
	 *
	 * @param      PropelPDO $con
	 * @return     void
	 * @throws     PropelException
	 * @see        BaseObject::setDeleted()
	 * @see        BaseObject::isDeleted()
	 */
	public function delete(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(TrackEntryPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$ret = $this->preDelete($con);
			if ($ret) {
				TrackEntryPeer::doDelete($this, $con);
				$this->postDelete($con);
				$this->setDeleted(true);
				$con->commit();
			} else {
				$con->commit();
			}
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	/**
	 * Persists this object to the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All modified related objects will also be persisted in the doSave()
	 * method.  This method wraps all precipitate database operations in a
	 * single transaction.
	 *
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        doSave()
	 */
	public function save(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(TrackEntryPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		$isInsert = $this->isNew();
		try {
			$ret = $this->preSave($con);
			if ($isInsert) {
				$ret = $ret && $this->preInsert($con);
			} else {
				$ret = $ret && $this->preUpdate($con);
			}
			if ($ret) {
				$affectedRows = $this->doSave($con);
				if ($isInsert) {
					$this->postInsert($con);
				} else {
					$this->postUpdate($con);
				}
				$this->postSave($con);
				TrackEntryPeer::addInstanceToPool($this);
			} else {
				$affectedRows = 0;
			}
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}
	
	public function wasObjectSaved()
	{
		return $this->objectSaved;
	}

	/**
	 * Performs the work of inserting or updating the row in the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All related objects are also updated in this method.
	 *
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        save()
	 */
	protected function doSave(PropelPDO $con)
	{
		$affectedRows = 0; // initialize var to track total num of affected rows
		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;

			if ($this->isNew() ) {
				$this->modifiedColumns[] = TrackEntryPeer::ID;
			}

			// If this object has been modified, then save it to the database.
			$this->objectSaved = false;
			if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = TrackEntryPeer::doInsert($this, $con);
					$affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
										 // should always be true here (even though technically
										 // BasePeer::doInsert() can insert multiple rows).

					$this->setId($pk);  //[IMV] update autoincrement primary key

					$this->setNew(false);
					$this->objectSaved = true;
				} else {
					$affectedObjects = TrackEntryPeer::doUpdate($this, $con);
					if($affectedObjects)
						$this->objectSaved = true;
						
					$affectedRows += $affectedObjects;
				}

				$this->resetModified(); // [HL] After being saved an object is no longer 'modified'
			}

			$this->alreadyInSave = false;

		}
		return $affectedRows;
	} // doSave()

	/**
	 * Override in order to use the query cache.
	 * Cache invalidation keys are used to determine when cached queries are valid.
	 * Before returning a query result from the cache, the time of the cached query
	 * is compared to the time saved in the invalidation key.
	 * A cached query will only be used if it's newer than the matching invalidation key.
	 *  
	 * @return     array Array of keys that will should be updated when this object is modified.
	 */
	public function getCacheInvalidationKeys()
	{
		return array();
	}
		
	/**
	 * Code to be run before persisting the object
	 * @param PropelPDO $con
	 * @return bloolean
	 */
	public function preSave(PropelPDO $con = null)
	{
		$this->setCustomDataObj();
    	
		return parent::preSave($con);
	}

	/**
	 * Code to be run after persisting the object
	 * @param PropelPDO $con
	 */
	public function postSave(PropelPDO $con = null) 
	{
		kEventsManager::raiseEvent(new kObjectSavedEvent($this));
		$this->oldColumnsValues = array();
		$this->oldCustomDataValues = array();
    	 
		parent::postSave($con);
	}
	
	/**
	 * Code to be run before inserting to database
	 * @param PropelPDO $con
	 * @return boolean
	 */
	public function preInsert(PropelPDO $con = null)
	{
    	$this->setCreatedAt(time());
    	
		$this->setUpdatedAt(time());
		return parent::preInsert($con);
	}
	
	/**
	 * Code to be run after inserting to database
	 * @param PropelPDO $con 
	 */
	public function postInsert(PropelPDO $con = null)
	{
		kQueryCache::invalidateQueryCache($this);
		
		parent::postInsert($con);
	}

	/**
	 * Code to be run after updating the object in database
	 * @param PropelPDO $con
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
		{
			return;
		}
	
		kQueryCache::invalidateQueryCache($this);
		
		parent::postUpdate($con);
	}
	/**
	 * Array of ValidationFailed objects.
	 * @var        array ValidationFailed[]
	 */
	protected $validationFailures = array();

	/**
	 * Gets any ValidationFailed objects that resulted from last call to validate().
	 *
	 *
	 * @return     array ValidationFailed[]
	 * @see        validate()
	 */
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	/**
	 * Validates the objects modified field values and all objects related to this table.
	 *
	 * If $columns is either a column name or an array of column names
	 * only those columns are validated.
	 *
	 * @param      mixed $columns Column name or an array of column names.
	 * @return     boolean Whether all columns pass validation.
	 * @see        doValidate()
	 * @see        getValidationFailures()
	 */
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	/**
	 * This function performs the validation work for complex object models.
	 *
	 * In addition to checking the current object, all related objects will
	 * also be validated.  If all pass then <code>true</code> is returned; otherwise
	 * an aggreagated array of ValidationFailed objects will be returned.
	 *
	 * @param      array $columns Array of column names to validate.
	 * @return     mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
	 */
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			if (($retval = TrackEntryPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}



			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	/**
	 * Retrieves a field from the object by name passed in as a string.
	 *
	 * @param      string $name name
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     mixed Value of field.
	 */
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = TrackEntryPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		$field = $this->getByPosition($pos);
		return $field;
	}

	/**
	 * Retrieves a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @return     mixed Value of field at $pos
	 */
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getTrackEventTypeId();
				break;
			case 2:
				return $this->getPsVersion();
				break;
			case 3:
				return $this->getContext();
				break;
			case 4:
				return $this->getPartnerId();
				break;
			case 5:
				return $this->getEntryId();
				break;
			case 6:
				return $this->getHostName();
				break;
			case 7:
				return $this->getUid();
				break;
			case 8:
				return $this->getTrackEventStatusId();
				break;
			case 9:
				return $this->getChangedProperties();
				break;
			case 10:
				return $this->getParam1Str();
				break;
			case 11:
				return $this->getParam2Str();
				break;
			case 12:
				return $this->getParam3Str();
				break;
			case 13:
				return $this->getKs();
				break;
			case 14:
				return $this->getDescription();
				break;
			case 15:
				return $this->getCreatedAt();
				break;
			case 16:
				return $this->getUpdatedAt();
				break;
			case 17:
				return $this->getUserIp();
				break;
			case 18:
				return $this->getCustomData();
				break;
			default:
				return null;
				break;
		} // switch()
	}

	/**
	 * Exports the object as an array.
	 *
	 * You can specify the key type of the array by passing one of the class
	 * type constants.
	 *
	 * @param      string $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                        BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. Defaults to BasePeer::TYPE_PHPNAME.
	 * @param      boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns.  Defaults to TRUE.
	 * @return     an associative array containing the field names (as keys) and field values
	 */
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = TrackEntryPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getTrackEventTypeId(),
			$keys[2] => $this->getPsVersion(),
			$keys[3] => $this->getContext(),
			$keys[4] => $this->getPartnerId(),
			$keys[5] => $this->getEntryId(),
			$keys[6] => $this->getHostName(),
			$keys[7] => $this->getUid(),
			$keys[8] => $this->getTrackEventStatusId(),
			$keys[9] => $this->getChangedProperties(),
			$keys[10] => $this->getParam1Str(),
			$keys[11] => $this->getParam2Str(),
			$keys[12] => $this->getParam3Str(),
			$keys[13] => $this->getKs(),
			$keys[14] => $this->getDescription(),
			$keys[15] => $this->getCreatedAt(),
			$keys[16] => $this->getUpdatedAt(),
			$keys[17] => $this->getUserIp(),
			$keys[18] => $this->getCustomData(),
		);
		return $result;
	}

	/**
	 * Sets a field from the object by name passed in as a string.
	 *
	 * @param      string $name peer name
	 * @param      mixed $value field value
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     void
	 */
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = TrackEntryPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	/**
	 * Sets a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @param      mixed $value field value
	 * @return     void
	 */
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setTrackEventTypeId($value);
				break;
			case 2:
				$this->setPsVersion($value);
				break;
			case 3:
				$this->setContext($value);
				break;
			case 4:
				$this->setPartnerId($value);
				break;
			case 5:
				$this->setEntryId($value);
				break;
			case 6:
				$this->setHostName($value);
				break;
			case 7:
				$this->setUid($value);
				break;
			case 8:
				$this->setTrackEventStatusId($value);
				break;
			case 9:
				$this->setChangedProperties($value);
				break;
			case 10:
				$this->setParam1Str($value);
				break;
			case 11:
				$this->setParam2Str($value);
				break;
			case 12:
				$this->setParam3Str($value);
				break;
			case 13:
				$this->setKs($value);
				break;
			case 14:
				$this->setDescription($value);
				break;
			case 15:
				$this->setCreatedAt($value);
				break;
			case 16:
				$this->setUpdatedAt($value);
				break;
			case 17:
				$this->setUserIp($value);
				break;
			case 18:
				$this->setCustomData($value);
				break;
		} // switch()
	}

	/**
	 * Populates the object using an array.
	 *
	 * This is particularly useful when populating an object from one of the
	 * request arrays (e.g. $_POST).  This method goes through the column
	 * names, checking to see whether a matching key exists in populated
	 * array. If so the setByName() method is called for that column.
	 *
	 * You can specify the key type of the array by additionally passing one
	 * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
	 * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
	 * The default key type is the column's phpname (e.g. 'AuthorId')
	 *
	 * @param      array  $arr     An array to populate the object from.
	 * @param      string $keyType The type of keys the array uses.
	 * @return     void
	 */
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = TrackEntryPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setTrackEventTypeId($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setPsVersion($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setContext($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setPartnerId($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setEntryId($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setHostName($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setUid($arr[$keys[7]]);
		if (array_key_exists($keys[8], $arr)) $this->setTrackEventStatusId($arr[$keys[8]]);
		if (array_key_exists($keys[9], $arr)) $this->setChangedProperties($arr[$keys[9]]);
		if (array_key_exists($keys[10], $arr)) $this->setParam1Str($arr[$keys[10]]);
		if (array_key_exists($keys[11], $arr)) $this->setParam2Str($arr[$keys[11]]);
		if (array_key_exists($keys[12], $arr)) $this->setParam3Str($arr[$keys[12]]);
		if (array_key_exists($keys[13], $arr)) $this->setKs($arr[$keys[13]]);
		if (array_key_exists($keys[14], $arr)) $this->setDescription($arr[$keys[14]]);
		if (array_key_exists($keys[15], $arr)) $this->setCreatedAt($arr[$keys[15]]);
		if (array_key_exists($keys[16], $arr)) $this->setUpdatedAt($arr[$keys[16]]);
		if (array_key_exists($keys[17], $arr)) $this->setUserIp($arr[$keys[17]]);
		if (array_key_exists($keys[18], $arr)) $this->setCustomData($arr[$keys[18]]);
	}

	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */
	public function buildCriteria()
	{
		$criteria = new Criteria(TrackEntryPeer::DATABASE_NAME);

		if ($this->isColumnModified(TrackEntryPeer::ID)) $criteria->add(TrackEntryPeer::ID, $this->id);
		if ($this->isColumnModified(TrackEntryPeer::TRACK_EVENT_TYPE_ID)) $criteria->add(TrackEntryPeer::TRACK_EVENT_TYPE_ID, $this->track_event_type_id);
		if ($this->isColumnModified(TrackEntryPeer::PS_VERSION)) $criteria->add(TrackEntryPeer::PS_VERSION, $this->ps_version);
		if ($this->isColumnModified(TrackEntryPeer::CONTEXT)) $criteria->add(TrackEntryPeer::CONTEXT, $this->context);
		if ($this->isColumnModified(TrackEntryPeer::PARTNER_ID)) $criteria->add(TrackEntryPeer::PARTNER_ID, $this->partner_id);
		if ($this->isColumnModified(TrackEntryPeer::ENTRY_ID)) $criteria->add(TrackEntryPeer::ENTRY_ID, $this->entry_id);
		if ($this->isColumnModified(TrackEntryPeer::HOST_NAME)) $criteria->add(TrackEntryPeer::HOST_NAME, $this->host_name);
		if ($this->isColumnModified(TrackEntryPeer::UID)) $criteria->add(TrackEntryPeer::UID, $this->uid);
		if ($this->isColumnModified(TrackEntryPeer::TRACK_EVENT_STATUS_ID)) $criteria->add(TrackEntryPeer::TRACK_EVENT_STATUS_ID, $this->track_event_status_id);
		if ($this->isColumnModified(TrackEntryPeer::CHANGED_PROPERTIES)) $criteria->add(TrackEntryPeer::CHANGED_PROPERTIES, $this->changed_properties);
		if ($this->isColumnModified(TrackEntryPeer::PARAM_1_STR)) $criteria->add(TrackEntryPeer::PARAM_1_STR, $this->param_1_str);
		if ($this->isColumnModified(TrackEntryPeer::PARAM_2_STR)) $criteria->add(TrackEntryPeer::PARAM_2_STR, $this->param_2_str);
		if ($this->isColumnModified(TrackEntryPeer::PARAM_3_STR)) $criteria->add(TrackEntryPeer::PARAM_3_STR, $this->param_3_str);
		if ($this->isColumnModified(TrackEntryPeer::KS)) $criteria->add(TrackEntryPeer::KS, $this->ks);
		if ($this->isColumnModified(TrackEntryPeer::DESCRIPTION)) $criteria->add(TrackEntryPeer::DESCRIPTION, $this->description);
		if ($this->isColumnModified(TrackEntryPeer::CREATED_AT)) $criteria->add(TrackEntryPeer::CREATED_AT, $this->created_at);
		if ($this->isColumnModified(TrackEntryPeer::UPDATED_AT)) $criteria->add(TrackEntryPeer::UPDATED_AT, $this->updated_at);
		if ($this->isColumnModified(TrackEntryPeer::USER_IP)) $criteria->add(TrackEntryPeer::USER_IP, $this->user_ip);
		if ($this->isColumnModified(TrackEntryPeer::CUSTOM_DATA)) $criteria->add(TrackEntryPeer::CUSTOM_DATA, $this->custom_data);

		return $criteria;
	}

	/**
	 * Builds a Criteria object containing the primary key for this object.
	 *
	 * Unlike buildCriteria() this method includes the primary key values regardless
	 * of whether or not they have been modified.
	 *
	 * @return     Criteria The Criteria object containing value(s) for primary key(s).
	 */
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(TrackEntryPeer::DATABASE_NAME);

		$criteria->add(TrackEntryPeer::ID, $this->id);
		
		if($this->alreadyInSave && count($this->modifiedColumns) == 2 && $this->isColumnModified(TrackEntryPeer::UPDATED_AT))
		{
			$theModifiedColumn = null;
			foreach($this->modifiedColumns as $modifiedColumn)
				if($modifiedColumn != TrackEntryPeer::UPDATED_AT)
					$theModifiedColumn = $modifiedColumn;
					
			$atomicColumns = TrackEntryPeer::getAtomicColumns();
			if(in_array($theModifiedColumn, $atomicColumns))
				$criteria->add($theModifiedColumn, $this->getByName($theModifiedColumn, BasePeer::TYPE_COLNAME), Criteria::NOT_EQUAL);
		}

		return $criteria;
	}

	/**
	 * Returns the primary key for this object (row).
	 * @return     int
	 */
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	/**
	 * Generic method to set the primary key (id column).
	 *
	 * @param      int $key Primary key.
	 * @return     void
	 */
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	/**
	 * Sets contents of passed object to values from current object.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      object $copyObj An object of TrackEntry (or compatible) type.
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @throws     PropelException
	 */
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setTrackEventTypeId($this->track_event_type_id);

		$copyObj->setPsVersion($this->ps_version);

		$copyObj->setContext($this->context);

		$copyObj->setPartnerId($this->partner_id);

		$copyObj->setEntryId($this->entry_id);

		$copyObj->setHostName($this->host_name);

		$copyObj->setUid($this->uid);

		$copyObj->setTrackEventStatusId($this->track_event_status_id);

		$copyObj->setChangedProperties($this->changed_properties);

		$copyObj->setParam1Str($this->param_1_str);

		$copyObj->setParam2Str($this->param_2_str);

		$copyObj->setParam3Str($this->param_3_str);

		$copyObj->setKs($this->ks);

		$copyObj->setDescription($this->description);

		$copyObj->setCreatedAt($this->created_at);

		$copyObj->setUpdatedAt($this->updated_at);

		$copyObj->setUserIp($this->user_ip);

		$copyObj->setCustomData($this->custom_data);


		$copyObj->setNew(true);

		$copyObj->setId(NULL); // this is a auto-increment column, so set to default value

	}

	/**
	 * Makes a copy of this object that will be inserted as a new row in table when saved.
	 * It creates a new object filling in the simple attributes, but skipping any primary
	 * keys that are defined for the table.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @return     TrackEntry Clone of current object.
	 * @throws     PropelException
	 */
	public function copy($deepCopy = false)
	{
		// we use get_class(), because this might be a subclass
		$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		$copyObj->setCopiedFrom($this);
		return $copyObj;
	}
	
	/**
	 * Stores the source object that this object copied from 
	 *
	 * @var     TrackEntry Clone of current object.
	 */
	protected $copiedFrom = null;
	
	/**
	 * Stores the source object that this object copied from 
	 *
	 * @param      TrackEntry $copiedFrom Clone of current object.
	 */
	public function setCopiedFrom(TrackEntry $copiedFrom)
	{
		$this->copiedFrom = $copiedFrom;
	}

	/**
	 * Returns a peer instance associated with this om.
	 *
	 * Since Peer classes are not to have any instance attributes, this method returns the
	 * same instance for all member of this class. The method could therefore
	 * be static, but this would prevent one from overriding the behavior.
	 *
	 * @return     TrackEntryPeer
	 */
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new TrackEntryPeer();
		}
		return self::$peer;
	}

	/**
	 * Resets all collections of referencing foreign keys.
	 *
	 * This method is a user-space workaround for PHP's inability to garbage collect objects
	 * with circular references.  This is currently necessary when using Propel in certain
	 * daemon or large-volumne/high-memory operations.
	 *
	 * @param      boolean $deep Whether to also clear the references on all associated objects.
	 */
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
		} // if ($deep)

	}

	/* ---------------------- CustomData functions ------------------------- */

	/**
	 * @var myCustomData
	 */
	protected $m_custom_data = null;

	/**
	 * Store custom data old values before the changes
	 * @var        array
	 */
	protected $oldCustomDataValues = array();
	
	/**
	 * @return array
	 */
	public function getCustomDataOldValues()
	{
		return $this->oldCustomDataValues;
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param string $namespace
	 * @return string
	 */
	public function putInCustomData ( $name , $value , $namespace = null )
	{
		$customData = $this->getCustomDataObj( );
		
		$currentNamespace = '';
		if($namespace)
			$currentNamespace = $namespace;
			
		if(!isset($this->oldCustomDataValues[$currentNamespace]))
			$this->oldCustomDataValues[$currentNamespace] = array();
		if(!isset($this->oldCustomDataValues[$currentNamespace][$name]))
			$this->oldCustomDataValues[$currentNamespace][$name] = $customData->get($name, $namespace);
		
		$customData->put ( $name , $value , $namespace );
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @param string $defaultValue
	 * @return string
	 */
	public function getFromCustomData ( $name , $namespace = null , $defaultValue = null )
	{
		$customData = $this->getCustomDataObj( );
		$res = $customData->get ( $name , $namespace );
		if ( $res === null ) return $defaultValue;
		return $res;
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 */
	public function removeFromCustomData ( $name , $namespace = null)
	{

		$customData = $this->getCustomDataObj( );
		return $customData->remove ( $name , $namespace );
	}

	/**
	 * @param string $name
	 * @param int $delta
	 * @param string $namespace
	 * @return string
	 */
	public function incInCustomData ( $name , $delta = 1, $namespace = null)
	{
		$customData = $this->getCustomDataObj( );
		return $customData->inc ( $name , $delta , $namespace  );
	}

	/**
	 * @param string $name
	 * @param int $delta
	 * @param string $namespace
	 * @return string
	 */
	public function decInCustomData ( $name , $delta = 1, $namespace = null)
	{
		$customData = $this->getCustomDataObj(  );
		return $customData->dec ( $name , $delta , $namespace );
	}

	/**
	 * @return myCustomData
	 */
	public function getCustomDataObj( )
	{
		if ( ! $this->m_custom_data )
		{
			$this->m_custom_data = myCustomData::fromString ( $this->getCustomData() );
		}
		return $this->m_custom_data;
	}
	
	/**
	 * Must be called before saving the object
	 */
	public function setCustomDataObj()
	{
		if ( $this->m_custom_data != null )
		{
			$this->setCustomData( $this->m_custom_data->toString() );
		}
	}
	
	/* ---------------------- CustomData functions ------------------------- */
	
	protected $last_hydrate_time;

	public function getLastHydrateTime()
	{
		return $this->last_hydrate_time;
	}

} // BaseTrackEntry
