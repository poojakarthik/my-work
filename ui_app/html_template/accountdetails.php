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
		<form method='POST' action=''>
		<table>
			<tr>
				<h1>Account Details</h1>
			</tr>
			<tr>
				<?php
					// Dbo()->Object->Property->RenderInput([$bolRequired], [$strContext]);
					// Dbo()->Object->Property->RenderInput(TRUE, 'Account');
					// Dbo()->Object->Property->RenderInput(TRUE);				
					DBO()->Account->Id->RenderOutput(TRUE, 1);					
					DBO()->Account->BusinessName->RenderOutput(TRUE);
					DBO()->Account->TradingName->RenderOutput(TRUE,1);
					DBO()->Account->ABN->RenderOutput(TRUE,1);
					// DBO()->Account->ABN->RenderInput(TRUE,1);
					DBO()->Account->BillingType->RenderOutput(TRUE);
					
				?>	
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
				<textarea name='PopupContent' rows=20 cols=100></textarea>
		<?php
		//var_dump($_POST);
		//HTML is OK here, to define structures which enclose these objects
	}
}

?>
