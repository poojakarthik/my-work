<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// adjustment.php
//----------------------------------------------------------------------------//
/**
 * adjustment
 *
 * contains all ApplicationTemplate extended classes relating to Adjustment functionality
 *
 * contains all ApplicationTemplate extended classes relating to Adjustment functionality
 *
 * @file		adjustment.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateAdjustment
//----------------------------------------------------------------------------//
/**
 * AppTemplateAdjustment
 *
 * The AppTemplateAdjustment class
 *
 * The AppTemplateAdjustment class.  This incorporates all logic for all pages
 * relating to Adjustments
 *
 *
 * @package	ui_app
 * @class	AppTemplateAdjustment
 * @extends	ApplicationTemplate
 */
class AppTemplateAdjustment extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Adjustment popup window (Used to request an adjustment)
	 * 
	 * Performs the logic for the Add Adjustment popup window (Used to request an adjustment)
	 * Note that regardless of whether or not the adjustment is a credit or debit adjustment, and regardless
	 * of the user's permission level, no manually requested adjustments (using this function) are automatically approved.
	 *
	 * @return		void
	 * @method
	 */
	function Add()
	{
		Flex::assert(
			false, 
			'Flex is trying to add an adjustment using the application handler, this is deprecated.', 
			"Employee = ".Employee::getForId(Flex::getUserId())->getName()." (".Flex::getUserId().")",
			'Deprecated Adjustment Addition Method Used'
		);
		
		/*if (AppTemplateCharge::addCharge($this->_objAjax, CHARGE_MODEL_ADJUSTMENT))
		{
			// All required data has been retrieved from the database so now load the page template
			$this->LoadPage('charge_add');
			
			return true;
		}*/
	}
}
?>
