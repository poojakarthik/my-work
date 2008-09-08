<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_status_history.php
//----------------------------------------------------------------------------//
/**
 * customer_status_history
 *
 * HTML Template for the Account's Customer Status history 
 *
 * HTML Template for the Account's Customer Status history 
 *
 * @file		customer_status_history.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.09
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountCustomerStatusHistory
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountCustomerStatusHistory
 *
 * A specific HTML Template object
 *
 * An Account Details HTML Template object
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountCustomerStatusHistory
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountCustomerStatusHistory extends HtmlTemplate
{
	const MAX_INVOICE_RUNS = 12;
	
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
		
		$this->LoadJavascript("customer_status_history");
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
		$intAccountId = DBO()->Account->Id->Value;
		$intUserRoleId = $_SESSION['User']['user_role_id'];
		
		// Retrieve the CustomerStatusAssignment for this account, for the last self::MAX_INVOICE_RUNS invoice runs
		$arrStatusHistory = Customer_Status_Assignment::getForAccount($intAccountId, self::MAX_INVOICE_RUNS);
		
		if (count($arrStatusHistory) == 0)
		{
			// There are no records in the customer_status_history table relating to this account
			echo "
<h2 class='CustomerStatus'>Customer Status History</h2>
<div class='GroupedContent'>
	There is no history for this account
</div>
<div class='SmallSeparator'></div>
";
			return;
		}
		
		$arrHistoryDetails = array();
		$strStatuses = "";
		foreach ($arrStatusHistory as $i=>$objStatusAssignment)
		{
			$objInvoiceRun		= Invoice_Run::getForId($objStatusAssignment->invoiceRunId);
			$objCustomerStatus	= $objStatusAssignment->getCustomerStatus();
			
			$strStatuses .= "<td align='center' class='{$objCustomerStatus->cssClass}'>". date("jS M", strtotime($objInvoiceRun->billingDate)) ."<br />{$objCustomerStatus->name}</td>";

			$arrHistoryDetails[] = array(	"name"			=> htmlspecialchars($objCustomerStatus->name),
											"description"	=> htmlspecialchars($objCustomerStatus->description),
											"action"		=> htmlspecialchars($objStatusAssignment->getActionDescription($intUserRoleId)),
											"lastUpdated"	=> date("jS M, Y g:i:s a", strtotime($objStatusAssignment->lastUpdated)),
											"cssClass"		=> $objCustomerStatus->cssClass
										);
		}
		$arrFirst = $arrHistoryDetails[0];
		
		$jsonHistoryDetails = Json()->encode($arrHistoryDetails);
		
		echo "
<h2 class='CustomerStatus'>Customer Status History</h2>
<table class='customer-status-history' id='CustomerStatusHistory' cellpadding='0' cellspacing='0'>
	<tr>$strStatuses</tr>
</table>
<div class='TinySeparator'></div>
<table id='CustomerStatusDetails' class='{$arrFirst['cssClass']}'>
	<tr valign='top'>
		<td>Action</td>
		<td id='CustomerStatusDetails.Action'>{$arrFirst['action']}</td>
	</tr>
	<tr valign='top'>
		<td id='CustomerStatusDetails.Name'>{$arrFirst['name']}</td>
		<td id='CustomerStatusDetails.Description'>{$arrFirst['description']}</td>
	</tr>
	<tr valign='top'>
		<td>Updated</td>
		<td id='CustomerStatusDetails.LastUpdated'>{$arrFirst['lastUpdated']}</td>
	</tr>
</table>
<div class='SmallSeparator'></div>
<script type='text/javascript'>Vixen.CustomerStatusHistory.Initialise($jsonHistoryDetails)</script>
";

		
	}

}

?>
