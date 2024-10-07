<?php

/**
 * @package plugins.reach
 * @subpackage api.objects
 * @relatedService EntryVendorTaskService
 */
class KalturaQuizVendorTaskData extends KalturaVendorTaskData
{
	/**
	 * Number Of Questions.
	 *
	 * @var int
	 */
	public $numberOfQuestions;

	/**
	 * Questions Type.
	 *
	 * @var string
	 */
	public $questionsType;

	/**
	 * Quiz Context.
	 *
	 * @var string
	 */
	public $context;

	/**
	 * Quiz entry Id
	 *
	 * @var string
	 */
	public $quizOutput;

	private static $map_between_objects = array
	(
		'numberOfQuestions',
		'questionsType',
		'context',
		'quizOutput',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject) {
			$dbObject = new kQuizVendorTaskData();
		}

		return parent::toObject($dbObject, $propsToSkip);
	}
}
