<?php

/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class KalturaVendorVideoAnalysisCatalogItemFilter extends KalturaVendorCatalogItemFilter
{
	public function getTypeListResponse(KalturaFilterPager $pager, KalturaDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if (!$type) {
			$type = KalturaVendorServiceFeature::VIDEO_ANALYSIS;
		}

		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}
