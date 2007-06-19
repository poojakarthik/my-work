<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * A specific HTML Template object
 *
 * An Account Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountDetails extends HtmlTemplate
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
		
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_SEANS_DETAIL:
				$this->_RenderSeansDetail();
				break;
			case HTML_CONTEXT_LEDGER_DETAIL:
				$this->_RenderLedgerDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{
		?>
		<h2 class='Account'>Account Details</h2>
		<div class='Narrow-Form'>
			<table border='0' cellpadding='3' cellspacing='0'>
				<?php
				foreach (DBO()->Account AS $strProperty=>$objValue)
				{	
					echo "<tr>\n";
					$objValue->RenderOutput();
					echo "</tr>\n";
				}
				?>
			</table>
		</div>
		<div class='Seperator'></div>
		<?php

	}

	//------------------------------------------------------------------------//
	// _RenderLedgerDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderLedgerDetail()
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * @method
	 */
	private function _RenderLedgerDetail()
	{
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='NarrowContent'>\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderSeansDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderSeansDetail()
	 *
	 * Render this HTML Template with Sean's detail
	 *
	 * Render this HTML Template with Sean's detail
	 *
	 * @method
	 */
	private function _RenderSeansDetail()
	{
		?>
			<h2 class='Account'>Account Details</h2>
			<div class='Narrow-Form'>
			<table border='0' cellpadding='3' cellspacing='0'>
			<tr>
				<?php DBO()->Account->Id->RenderOutput(); ?>
			</tr>
			<tr>
					<?php DBO()->Account->BusinessName->RenderOutput(); ?>
			</tr>
			<tr>
					<?php DBO()->Account->TradingName->RenderOutput(); ?>
			</tr>
			<tr>
					<?php DBO()->Account->ABN->RenderOutput();?>
			</tr>
			<tr>
					<?php DBO()->Account->BillingType->RenderOutput();?>
			</tr>
			<tr><td>
				<select name='Mode'>
					<option value="modal">Modal</option>
					<option value="modeless">Modeless</option>
					<option value="autohide">Autohide</option>
				</td>
				<td>
				<select name='Size'>
					<option value='small'>Small</option>
					<option value='medium'>Medium</option>
					<option value='large'>Large</option>
				</select>
				</td></tr><tr>
				<td>
					Popup Id: </td><td><input type='text' name='PopupId' value='MyLogin'></input>
				</td>
				</tr>
			</select>
			<tr>
				<input type='button' value='Popup-Centre' onclick='Vixen.Popup.Create(PopupContent.value, PopupId.value, Size.value, "centre", Mode.value)'></input>
				<input type='button' value='Popup-Cursor' onclick='Vixen.Popup.Create(PopupContent.value, PopupId.value, Size.value, event, Mode.value)'></input>
				<input type='button' value='Popup-Target' onclick='Vixen.Popup.Create(PopupContent.value, PopupId.value, Size.value, this, Mode.value)'></input>
			</tr>
		</table>
		</div>
		<div class='Seperator'></div>
		HTML for popup:
				<textarea name='PopupContent' cols=59></textarea>
		<div class='Seperator'></div>
		<?php
		//var_dump($_POST);
		//HTML is OK here, to define structures which enclose these objects
	}
}

?>
