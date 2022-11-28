<?php

/**
* @package deployment
* @subpackage quasar.roles_and_permissions
*/

$script = realpath(dirname(__FILE__) . '/../../../') . '/base/scripts/insertDefaults.php';
$config = realpath(dirname(__FILE__) . '/../../../') . '/base/scripts/init_data/01.Partner.ini';
passthru("php $script $config");

$script = realpath(dirname(__FILE__) . '/../../../../') . '/alpha/scripts/utils/permissions/addPermissionsAndItems.php';

/* auth broker */
$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/partner.-17.ini';
passthru("php $script $config");

/* user profile */
$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/partner.-18.ini';
passthru("php $script $config");

/* kms */
$config = realpath(dirname(__FILE__) . '/../../../') . '/permissions/partner.-19.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/service.session.ini';
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/../../../permissions/service.partner.ini';
passthru("php $script $config");