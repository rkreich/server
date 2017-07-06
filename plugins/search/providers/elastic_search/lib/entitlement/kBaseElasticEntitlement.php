<?php

/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */
abstract class kBaseElasticEntitlement
{
    public static $isInitialized = false;
    public static $partnerId;
    public static $ks;

    public static function init()
    {
        if(!self::$isInitialized)
            self::initialize();
    }
    
    protected static function initialize()
    {
        self::$ks = ks::fromSecureString(kCurrentContext::$ks);
        self::$partnerId = kCurrentContext::$partner_id ? kCurrentContext::$partner_id : kCurrentContext::$ks_partner_id;
    }
    
}
