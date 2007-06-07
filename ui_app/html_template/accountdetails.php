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
	function __construct()
	{
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
			<tr>
				<input type='button' value='Popup' onclick='Vixen.Popup.Create(PopupContent.value, "MyLogin", "medium", "centre", "autohide")'></input>
			</tr>
		</table>
				<textarea name='PopupContent' rows=20 cols=100></textarea>
		<?php
		//var_dump($_POST);
		//HTML is OK here, to define structures which enclose these objects
	}
}

?>
