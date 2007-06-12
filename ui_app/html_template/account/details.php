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
