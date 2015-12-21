<?php
/**
 * @package Core
 * @subpackage model.data
 */
class kConvertLiveSegmentJobData extends kJobData
{
	/**
	 * @var string
	 */
	private $entryId;
	
	/**
	 * @var string
	 */
	private $assetId;
	
	/**
	 * @var int
	 */
	private $mediaServerIndex;
	
	/**
	 * @var int
	 */
	private $fileIndex;
	
	/**
	 * @var string
	 */
	private $srcFilePath;
	
	/**
	 * @var string
	 */
	private $destFilePath;
	
	/**
	 * @var float
	 */
	private $endTime;

	/**
	 * @var array
	 */
	private $amfArray;

	/**
	 * @var float
	 */
	private $duration;

	/**
	 * @return string $entryId
	 */
	public function getEntryId()
	{
		return $this->entryId;
	}

	/**
	 * @return int $mediaServerIndex
	 */
	public function getMediaServerIndex()
	{
		return $this->mediaServerIndex;
	}

	/**
	 * @return string $srcFilePath
	 */
	public function getSrcFilePath()
	{
		return $this->srcFilePath;
	}

	/**
	 * @return string $destFilePath
	 */
	public function getDestFilePath()
	{
		return $this->destFilePath;
	}

	/**
	 * @return float $endTime
	 */
	public function getEndTime()
	{
		return $this->endTime;
	}

	/**
	 * @param string $entryId
	 */
	public function setEntryId($entryId)
	{
		$this->entryId = $entryId;
	}

	/**
	 * @param int $mediaServerIndex
	 */
	public function setMediaServerIndex($mediaServerIndex)
	{
		$this->mediaServerIndex = $mediaServerIndex;
	}

	/**
	 * @param string $srcFilePath
	 */
	public function setSrcFilePath($srcFilePath)
	{
		$this->srcFilePath = $srcFilePath;
	}

	/**
	 * @param string $destFilePath
	 */
	public function setDestFilePath($destFilePath)
	{
		$this->destFilePath = $destFilePath;
	}

	/**
	 * @param float $endTime
	 */
	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
	}
	
	/**
	 * @return int $fileIndex
	 */
	public function getFileIndex()
	{
		return $this->fileIndex;
	}

	/**
	 * @param int $fileIndex
	 */
	public function setFileIndex($fileIndex)
	{
		$this->fileIndex = $fileIndex;
	}
	
	/**
	 * @return the $assetId
	 */
	public function getAssetId()
	{
		return $this->assetId;
	}

	/**
	 * @param string $assetId
	 */
	public function setAssetId($assetId)
	{
		$this->assetId = $assetId;
	}

	/**
	 * @return the $amfArray
	 */
	public function getAmfArray()
	{
		return $this->amfArray;
	}

	/**
	 * @param KalturaKeyValueArray $amfArray
	 */
	public function setAmfArray($amfArray)
	{
		$this->amfArray = $amfArray;
	}

	/**
	 * @return the $duration
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @param float $duration
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;
	}
}
