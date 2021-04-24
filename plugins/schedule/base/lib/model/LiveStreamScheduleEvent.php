<?php
/**
 * @package plugins.schedule
 * @subpackage model
 */
class LiveStreamScheduleEvent extends BaseLiveStreamScheduleEvent
{
	const PROJECTED_AUDIENCE = 'projected_audience';
	const PRE_START_TIME = 'pre_start_time';
	const POST_END_TIME = 'post_end_time';
	const SOURCE_ENTRY_ID = 'source_entry_id';
	
	/**
	 * @param string $v
	 */
	public function setSourceEntryId($v)
	{
		$this->putInCustomData(self::SOURCE_ENTRY_ID, $v);
	}
	
	/**
	 * @return string
	 */
	public function getSourceEntryId()
	{
		return $this->getFromCustomData(self::SOURCE_ENTRY_ID);
	}
	
	/**
	 * @param int $v
	 */
	public function setProjectedAudience($v)
	{
		$this->putInCustomData(self::PROJECTED_AUDIENCE, $v);
	}

	/**
	 * @return int
	 */
	public function getProjectedAudience()
	{
		return $this->getFromCustomData(self::PROJECTED_AUDIENCE);
	}
	
	/**
	 * @param int $v
	 */
	public function setPreStartTime($v)
	{
		$this->putInCustomData(self::PRE_START_TIME, $v);
	}

	/**
	 * @return int
	 */
	public function getPreStartTime()
	{
		return $this->getFromCustomData(self::PRE_START_TIME, null, 0);
	}

	/**
	 * @param int $v
	 */
	public function setPostEndTime($v)
	{
		$this->putInCustomData(self::POST_END_TIME, $v);
	}

	/**
	 * @return int
	 */
	public function getPostEndTime()
	{
		return $this->getFromCustomData(self::POST_END_TIME, null, 0);
	}
	
	public function getCalculatedStartTime()
	{
		return parent::getCalculatedStartTime() - $this->getPreStartTime();
	}
	
	public function getCalculatedEndTime()
	{
		return parent::getCalculatedEndTime() + $this->getPostEndTime();
	}
	
	
	public function dynamicGetter($context, &$output)
	{
		$output = null;
		
		switch ($context)
		{
			case 'getLiveStatus':
				if($this->getSourceEntryId())
				{
					$output = EntryServerNodeStatus::PLAYABLE;
					return true;
				}
			default:
				return false;
		}
	}
	
	protected function addCapabilityToTemplateEntry($con)
	{
		$liveEntry = entryPeer::retrieveByPK($this->getTemplateEntryId());
		if ($liveEntry)
		{
			$shouldSave = false;
			if (!$liveEntry->hasCapability(LiveEntry::LIVE_SCHEDULE_CAPABILITY))
			{
				$liveEntry->addCapability(LiveEntry::LIVE_SCHEDULE_CAPABILITY);
				$shouldSave = true;
			}
			if ($this->getSourceEntryId() && !$liveEntry->hasCapability(LiveEntry::SIMULIVE_CAPABILITY))
			{
				$liveEntry->addCapability(LiveEntry::SIMULIVE_CAPABILITY);
				$shouldSave = true;
			}
			if ($shouldSave)
			{
				$liveEntry->save($con);
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see ScheduleEvent::applyDefaultValues()
	 */
	public function applyDefaultValues()
	{
		parent::applyDefaultValues();
		$this->setType(ScheduleEventType::LIVE_STREAM);
	}
}