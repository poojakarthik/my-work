<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-7 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// FRAMEWORK
//----------------------------------------------------------------------------//
/**
 * FRAMEWORK
 *
 * The framework which links everything
 *
 * The framework which links all of our modules
 *
 * @file		framework.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.06
 * @copyright	2006-7 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$thisDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $thisDir.'../classes/Framework.php';
require_once $thisDir.'../classes/ApplicationBaseClass.php';
require_once $thisDir.'../classes/CarrierModule.php';
require_once $thisDir.'../classes/module/Module_Config.php';

?>
