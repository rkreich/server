<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model
 */
class kESearchQueryManager
{
	public static function getMultiMatchQuery($searchItem, $fieldName, $shouldAddLanguageFields = false)
	{
		$multiMatch = array();
		$multiMatch['multi_match']['query'] = $searchItem->getSearchTerm();
		$multiMatch['multi_match']['fields'] = array(
			$fieldName.'.trigrams',
			$fieldName.'.raw^3',
			$fieldName.'^2',
		);
		$multiMatch['multi_match']['type'] = 'most_fields';

		if($shouldAddLanguageFields)
			$multiMatch['multi_match']['fields'][] = $fieldName.'_*^2';

		return $multiMatch;
	}

	public static function getExactMatchQuery($searchItem, $fieldName, $allowedSearchTypes)
	{
		$exactMatch = array();
		$queryType = 'term';
		$fieldSuffix = '';

		if ($searchItem->getItemType() == ESearchItemType::EXACT_MATCH && in_array(ESearchItemType::PARTIAL, $allowedSearchTypes[$searchItem->getFieldName()]))
		{
			$queryType = 'match';
			$fieldSuffix = '.raw';
		}

		$exactMatch[$queryType] = array( $fieldName . $fieldSuffix => $searchItem->getSearchTerm());
		return $exactMatch;
	}

	public static function getPrefixQuery($searchItem, $fieldName, $allowedSearchTypes)
	{
		$prefixQuery = array();
		$queryType = 'prefix';
		if(in_array(ESearchItemType::PARTIAL, $allowedSearchTypes[$searchItem->getFieldName()]))
			$queryType = 'match_phrase_prefix';
		$prefixQuery[$queryType] = array( $fieldName => $searchItem->getSearchTerm());

		return $prefixQuery;
	}

	public static function getDoesntContainQuery($searchItem, $fieldName, $allowedSearchTypes)
	{
		return self::getExactMatchQuery($searchItem, $fieldName, $allowedSearchTypes);
	}

}
