<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_list.php
//----------------------------------------------------------------------------//
/**
 * charge_list
 *
 * HTML Template for the Charge List HTML object
 *
 * HTML Template for the Charge List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all charges relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		charge_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateChargeList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateChargeList
 *
 * HTML Template class for the Charge List HTML object
 *
 * HTML Template class for the Charge List HTML object
 * Lists all charges related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateChargeList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all javascript specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("tooltip");
		$this->LoadJavascript("control_tab");
		$this->LoadJavascript("control_tab_group");
		$this->LoadJavascript("component_account_charge_list");
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		//---------------//
		// New
		//---------------//
		echo "
		<div id='ChargeListContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					Flex.Constant.loadConstantGroup(
						['charge_model', 'ChargeStatus', 'ChargeLink'], 
						function()
						{
							objChargeType = new Component_Account_Charge_List(\$ID('ChargeListContainer'), ".DBO()->Account->Id->Value.");
						}, false
					)
				}
			)
		</script>\n";
	}
}

?>
