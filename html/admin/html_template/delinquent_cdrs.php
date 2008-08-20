<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// delinquent_cdrs.php
//----------------------------------------------------------------------------//
/**
 * delinquent_cdrs
 *
 * HTML Template for the DelinquentCDRs HTML object
 *
 * HTML Template for the DelinquentCDRs HTML object
 *
 * @file		delinquent_cdrs.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateDelinquentCDRs
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDelinquentCDRs
 *
 * HTML Template class for the DelinquentCDRs HTML object
 *
 * HTML Template class for the DelinquentCDRs HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateDelinquentCDRs
 * @extends	HtmlTemplate
 */
class HtmlTemplateDelinquentCDRs extends HtmlTemplate
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
		
		$this->LoadJavascript("delinquent_cdrs");
		$this->LoadJavascript("date_time_picker_dynamic");
		$this->LoadJavascript("validation");
		$this->LoadJavascript("input_masks");
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
		$intNextBillDate	= GetStartDateTimeForNextBillingPeriod();
		$intStartingDate	= strtotime("-185 days", $intNextBillDate);
		
		$strStartingDate	= date("d/m/Y", $intStartingDate);
		$strEndingDate		= date("d/m/Y", $intNextBillDate);
		
		$strYearLowerLimit	= substr($strStartingDate, 6);
		$strYearUpperLimit	= substr($strEndingDate, 6);
		
		$intMaxYear			= intval(date("Y", $intNextBillDate));
		$intMinYear			= intval(date("Y", $intStartingDate));
		
		$intDefaultYear		= $intMaxYear;
		$intDefaultMonth	= intval(date("m", $intNextBillDate));
		$intDefaultDay		= intval(date("d", $intNextBillDate));
		
		
		
		echo "
<div class='GroupedContent'>
	<div id='Container_SelectionControls' style='height:25px'>
		<div class='Left'>
			<span>Earliest Date </span>
			<input type='text' id='StartDate' name='StartDate' InputMask='ShortDate' maxlength='10' value='$strStartingDate' style='width:85px'/>
			<a href='javascript:DateChooser.showChooser(\"StartDate\", $intMinYear, $intMaxYear, \"d/m/Y\", false, true, true, $intDefaultYear, $intDefaultMonth, $intDefaultDay);'>
				<img src='img/template/calendar_small.png' width='16' height='16' title='Date picker' />
			</a>

			
			<span> Latest Date </span>
			<input type='text' id='EndDate' name='EndDate' InputMask='ShortDate' maxlength='10' value='$strEndingDate' style='width:85px'/>
			<a href='javascript:DateChooser.showChooser(\"EndDate\", $intMinYear, $intMaxYear, \"d/m/Y\", false, true, true, $intDefaultYear, $intDefaultMonth, $intDefaultDay);'>
				<img src='img/template/calendar_small.png' width='16' height='16' title='Date picker' />
			</a>
		</div>

		<div class='Right'>
			<input type='button' class='InputSubmit' value='Search' onclick='Vixen.DelinquentCDRs.GetFNNs()'></input>
		</div>

	</div>
	<div class='SmallSeparator'></div>
				
	<span style='white-space:pre;font-family:Courier New, monospace;padding-left:4px'>FNN          Carrier                          Cost     Count     Earliest       Latest</span>
	<select id='FNNSelector' size='8' style='width:100%; border-color:#D1D1D1;font-family:Courier New, monospace' onChange='Vixen.DelinquentCDRs.GetCDRs()'></select>

</div>		
<div class='SmallSeparator'></div>

<div id='Container_FNNGroup' style='width:100%;display:none;'>
	<div class='GroupedContent' style='height:24px'>
		<div class='Left'>
			<input type='checkbox' id='CheckBoxSelectAllCDRs' class='DefaultInputCheckBox' onChange='Vixen.DelinquentCDRs.SelectAllCDRs(this.checked)'/>
			<input type='Button' value='Bulk Declare Service' onclick='Vixen.DelinquentCDRs.OpenDeclareServicePopup()'/>
		</div>
		<div class='Right'>
			<input type='Button' value='Commit' onclick='Vixen.DelinquentCDRs.Commit()'/>
		</div>
	</div>

	<div style='height:24px; width:100%'>
		<div class='Left'>
			<a href='javascript:Vixen.DelinquentCDRs.MoveFirst()' >&lt;&lt;</a>
			<a href='javascript:Vixen.DelinquentCDRs.MovePrevious()' style='margin-left:20px'>&lt;</a>
		</div>
		<div class='Right'>
			<a href='javascript:Vixen.DelinquentCDRs.MoveNext()' style='margin-right:20px'>&gt;</a>
			<a href='javascript:Vixen.DelinquentCDRs.MoveLast()'>&gt;&gt;</a>
		</div>
		<div id='CDRTableCaptionTop' style='text-align:center'></div>
	</div>

	<table id='CDRTable' class='Listing' width='100%' cellspacing='0' cellpadding='3' border='0'>
		<tr class='First' style='display:table-row;'>
			<th width='4%' align='left'>&nbsp;</th>
			<th width='5%' align='left'>Rec#</th>
			<th width='15%' align='left'>Start Time</th>
			<th width='7%' align='right'>Cost</th>
			<th width='43%' align='left'>New Owner</th>
			<th width='18%' align='left'>&nbsp;</th>
			<th width='8%' align='right'>Service</th>
		</tr>
		<tr class='Odd' style='display:table-row;'>
			<td align='left'><input type='checkbox'/></td>
			<td align='left'></td>
			<td align='left'></td>
			<td align='right'></td>
			<td align='left' style='cursor:pointer;'></td>
			<td align='left'></td>
			<td align='right'></td>
		</tr>
	</table>

	<div style='height:24px; width:100%'>
		<div class='Left'>
			<a href='javascript:Vixen.DelinquentCDRs.MoveFirst()'>&lt;&lt;</a>
			<a href='javascript:Vixen.DelinquentCDRs.MovePrevious()' style='margin-left:20px'>&lt;</a>
		</div>
		<div class='Right'>
			<a href='javascript:Vixen.DelinquentCDRs.MoveNext()' style='margin-right:20px'>&gt;</a>
			<a href='javascript:Vixen.DelinquentCDRs.MoveLast()'>&gt;&gt;</a>
		</div>
		<div id='CDRTableCaptionBottom' style='text-align:center'></div>
	</div>

</div>
<script type='text/javascript'>Vixen.DelinquentCDRs.Initialise()</script>";
	}
}

?>
