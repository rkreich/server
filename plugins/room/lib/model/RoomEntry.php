<?php
/**
 * @package plugins.room
 * @subpackage model
 */
class RoomEntry extends entry
{

	const CUSTOM_DATA_ROOM_TYPE = 'roomType';

	public function getRoomType()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_ROOM_TYPE);
	}

	public function setRoomType($v)
	{
		$this->putInCustomData(self::CUSTOM_DATA_ROOM_TYPE, $v);
	}

}