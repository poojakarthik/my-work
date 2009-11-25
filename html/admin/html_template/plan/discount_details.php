<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// discount_details.php
//----------------------------------------------------------------------------//
/**
 * discount_details
 *
 * HTML Template for the Plan's Discount Details HTML object
 *
 * HTML Template for the Plan's Discount Details HTML object
 *
 * @file		discount_details.php
 * @language	PHP
 * @package		ui_app
 * @author		Rich Davis
 * @version		9.11
 * @copyright	2009 Yellow Billing Services Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplatePlanDiscountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanDiscountDetails
 *
 * HTML Template class for the Plan's Discount Details HTML object
 *
 * HTML Template class for the Plan's Discount Details HTML object
 * Lists all Discounts belonging to a RatePlan, in the one table
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanDiscountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanDiscountDetails extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;

		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
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
		echo	"<div id='DiscountDefinitions' style='display: inline-block; vertical-align: top; width: 50%;'>\n";
		
		echo	"<table id='rate_plan_discounts' class='listing-fw3' style='width: 98%; margin: auto;'>\n";
		
		echo	"<caption style='text-align: left;'><h2>Discounts</h2></caption>\n";
		
		echo	"<thead>\n" .
				"	<tr>\n" .
				"		<th style='text-align: left; max-width: 40%; min-width: 40%;'>Name</th>" .
				"		<th style='text-align: left; max-width: 40%; min-width: 40%;'>Description</th>" .
				"		<th style='text-align: left;'>Limit</th>" .
				"	</tr>\n" .
				"</thead>\n";
		
		echo	"<tbody>\n";
		
		// Available Discounts
		$aRecordTypeDiscountMap	= array();
		if (DBL()->discount->RecordCount())
		{
			foreach (DBL()->discount as $dboDiscount)
			{
				echo	"<tr>\n" .
						"	<td style='text-align: left; max-width: 40%; min-width: 40%;'>".htmlspecialchars($dboDiscount->name->Value)."</th>" .
						"	<td style='text-align: left; max-width: 40%; min-width: 40%;'>".htmlspecialchars($dboDiscount->description->Value)."</th>" .
						"	<td style='text-align: left;'>".(($dboDiscount->unit_limit->Value) ? "{$dboDiscount->unit_limit->Value} Units" : "\$".number_format($dboDiscount->charge_limit->Value, 2, '.', ','))."</th>" .
						"</tr>\n";
				
				foreach ($dboDiscount->dblRecordTypes->Value as $dboRecordType)
				{
					$aRecordTypeDiscountMap[$dboRecordType->Id->Value]	= $dboDiscount;
				}
			}
		}
		else
		{
			echo	"<tr>\n" .
					"	<td colspan='5'>There are no Discounts defined for this Plan</td>\n" .
					"</tr>\n";
		}
		
		echo	"</tbody>\n";
		echo	"</table>\n";
		
		// Record Type Associations
		echo "</div><div id='DiscountRecordTypes' style='display: inline-block; vertical-align: top; width: 50%;'>\n";
		
		echo "<table id='discount_record_types' class='listing-fw3' style='width: 98%; margin: auto;'>\n";
		
		echo "<caption style='text-align: left;'><h2>Call Type Associations</h2></caption>\n";
		
		echo	"<thead>\n" .
				"	<tr>\n" .
				"		<th style='text-align: left; max-width: 60%; min-width: 60%;'>Call Type</th>" .
				"		<th style='text-align: left;'>Discount</th>" .
				"	</tr>\n" .
				"</thead>\n";
		
		echo	"<tbody>\n";
		
		if (DBL()->RecordType->RecordCount() > 0)
		{
			foreach (DBL()->RecordType as $dboRecordType)
			{
				
				echo	"<tr value='{$dboRecordType->Id->Value}'>\n" .
						"	<td>".htmlspecialchars($dboRecordType->Description->Value)."</td>\n" .
						"	<td>".((array_key_exists($dboRecordType->Id->Value, $aRecordTypeDiscountMap)) ? htmlspecialchars($dboDiscount->name->Value) : '[ No Discount ]')."</td>\n" .
						"</tr>\n";
			}
		}
		else
		{
			echo	"<tr>\n" .
					"	<td colspan='2'>There are no Call Types associated with this Service Type</td>\n" .
					"</tr>\n";
		}
		
		echo	"</tbody>\n";
		echo	"</table>\n";
		
		echo	"</div>";

		echo	"<div class='SmallSeperator'></div>\n";
	}
}

?>
