<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// grouplist.php
//----------------------------------------------------------------------------//
/**
 * grouplist
 *
 * HTML Template for the Group List HTML object
 *
 * HTML Template for the Group List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all rategroups relating to a service and can be embedded in
 * various Page Templates
 *
 * @file		grouplist.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateGroupList
 *
 * HTML Template class for the Group List HTML object
 *
 * HTML Template class for the Group List HTML object
 * Lists all rategrops related to a service
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupList
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

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
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
		$this->LoadJavascript("tooltip");
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
		// Render each of the account invoices
		echo "<h2 class='Invoice'>Rate Groups</h2>\n";
		//echo "<div class='NarrowColumn'>\n";
		
		Table()->RateGroupTable->SetHeader("Id", "Rate Group", "Description", "Fleet", "RecordType");
		Table()->RateGroupTable->SetWidth("10%", "25%", "30%", "5%", "30%");
		Table()->RateGroupTable->SetAlignment("Left", "Left", "Left", "Left", "Left");
		
		foreach (DBL()->RateGroup as $dboRateGroup)
		{
			// build the links 
			//$intDate = strtotime("-1 month", strtotime($dboInvoice->CreatedOn->Value));
			//$intYear = (int)date("Y", $intDate);
			//$intMonth = (int)date("m", $intDate);
			
			// check if a pdf exists for the invoice
			//if (InvoicePdfExists($dboInvoice->Account->Value, $intYear, $intMonth))
			//{
				// The pdf exists
				// Build "view invoice pdf" link
				//$strPdfHref 	= Href()->ViewInvoicePdf($dboInvoice->Account->Value, $intYear, $intMonth);
				//$strPdfLabel 	= "<span class='DefaultOutputSpan Default'><a href='$strPdfHref'><img src='img/template/pdf.png' title='View PDF Invoice' /></a></span>";
				
				// build "Email invoice pdf" link
				//$strEmailHref 	= Href()->EmailPDFInvoice($dboInvoice->Account->Value, $intYear, $intMonth);
				//$strEmailLabel 	= "<span class='DefaultOutputSpan Default'><a href='$strEmailHref'><img src='img/template/email.png' title='Email PDF Invoice' /></a></span>";
			//}
			//else
			//{
				// don't allow the user to view the pdf for this invoice (or email it) because it doesn't exist
				//$strPdfLabel	= "&nbsp;";
				//$strEmailLabel	= "&nbsp;";
			//}
			
			// build the "View Invoice Details" link
			//$strViewInvoiceHref = Href()->ViewInvoice($dboInvoice->Id->Value);
			//$strViewInvoiceLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewInvoiceHref'><img src='img/template/invoice.png' title='View Invoice Details' /></a></span>";
			
			// calculate Invoice Amount
			//$dboInvoice->Amount = $dboInvoice->Total->Value + $dboInvoice->Tax->Value;
			// calculate AppliedAmount
			//$dboInvoice->AppliedAmount = $dboInvoice->Amount->Value - $dboInvoice->Balance->Value;
			
			// Add this row to Invoice table
			Table()->RateGroupTable->AddRow(	$dboRateGroup->Id->Value,
												$dboRateGroup->Name->Value, 
												$dboRateGroup->Description->Value, 
												$dboRateGroup->Fleet->Value,
												$dboRateGroup->RecordTypeName->Value);
			
			//Retrieve the Rate information for this RateGroup
			$strWhere = "Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = <RateGroupId>)";
			DBL()->Rate->Where->Set($strWhere, Array('RateGroupId' => $dboRateGroup->Id->Value));
			DBL()->Rate->Load();
			if (DBL()->Rate->RecordCount() <= 2)
			{
				// Add the rate information to the DropDown div for the row
				// Set the drop down detail
				$strDetailHtml = "<div class='VixenTableDetail'>\n";
				$strDetailHtml .= "<table width='100%' border=0 cellspacing=0 cellpadding=0>\n";
				
				foreach (DBL()->Rate as $dboRate)
				{
					$strDetailHtml .= "   <tr>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Name->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Monday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Tuesday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Wednesday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Thursday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Friday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Saturday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->Sunday->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->StartTime->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->EndTime->AsValue();
					$strDetailHtml .= "      </td>\n";
					
					$strDetailHtml .= "   </tr>\n";
				}
				$strDetailHtml .= "</table>\n";
				$strDetailHtml .= "</div>\n";
				
				Table()->RateGroupTable->SetDetail($strDetailHtml);
				
			}
			else
			{
				// There are more than 10 rates for this RateGroup.  
				// Display a serach box and button.  The results of which will be displayed in a popup
				//TODO! begin and end AJAX to open rates in popup window
				
				$strBasicDetailHtml = "<div class='VixenTableDetail'>\n";
				$strBasicDetailHtml .= "<table width='100%' border=0 cellspacing=0 cellpadding=0>\n";
				$strBasicDetailHtml .= "	<tr>\n";
				$strBasicDetailHtml .=	"		<td>\n";			
				
				$strBasicDetailHtml .= "			<input type=text size=10>\n";
					
				$strBasicDetailHtml .= "		</td>\n";
				$strBasicDetailHtml .= "		<td>\n";
				
				$strBasicDetailHtml .= "			<input type=button value='ok'>\n";
				
				$strBasicDetailHtml .= "		</td>\n";
				$strBasicDetailHtml .=	"	</tr>\n";					
				$strBasicDetailHtml .= "</table>\n";
				$strBasicDetailHtml .= "</div>\n";
				
				Table()->RateGroupTable->SetDetail($strBasicDetailHtml);
			}
			
			
			
			//Set the drop down detail
			/*$strDetailHtml = "<div class='VixenTableDetail'>\n";
			$strDetailHtml .= $dboInvoice->DueOn->AsOutput();
			if ($dboInvoice->SettledOn->Value)
			{
				$strDetailHtml .= $dboInvoice->SettledOn->AsOutput();
			}
			$strDetailHtml .= $dboInvoice->Credits->AsOutput();
			$strDetailHtml .= $dboInvoice->Debits->AsOutput();
			$strDetailHtml .= $dboInvoice->Total->AsOutput();
			$strDetailHtml .= $dboInvoice->Tax->AsOutput();
			$strDetailHtml .= $dboInvoice->TotalOwing->AsOutput();
			$strDetailHtml .= $dboInvoice->Balance->AsOutput();
			if ($dboInvoice->Disputed->Value > 0)//does this include GST??????
			{
				$strDetailHtml .= $dboInvoice->Disputed->AsOutput();
			}
			$strDetailHtml .= $dboInvoice->AccountBalance->AsOutput();
			$strDetailHtml .= "</div>\n";
			
			Table()->InvoiceTable->SetDetail($strDetailHtml);
			
			// Add the row index
			Table()->InvoiceTable->AddIndex("InvoiceRun", $dboInvoice->InvoiceRun->Value);
			*/
		}
		/*
		if (DBL()->Invoice->RecordCount() == 0)
		{
			// There are no invoices to stick in this table
			Table()->InvoiceTable->AddRow("<span class='DefaultOutputSpan Default'>No invoices to display</span>");
			Table()->InvoiceTable->SetRowAlignment("left");
			Table()->InvoiceTable->SetRowColumnSpan(9);
		}
		else
		{
			// Link this table to the Payments table and the Adjustments table
			Table()->InvoiceTable->LinkTable("PaymentTable", "InvoiceRun");
			Table()->InvoiceTable->LinkTable("AdjustmentTable", "InvoiceRun");
			Table()->InvoiceTable->RowHighlighting = TRUE;
		}
		
		*/
		
		Table()->RateGroupTable->Render();
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		
	}
}

?>
