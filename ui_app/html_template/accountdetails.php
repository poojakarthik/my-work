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
		//$this->LoadJavascript("thing.js");
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
		<table>
			<tr>
				<?php
					// Dbo()->Object->Property->RenderInput([$bolRequired], [$strContext]);
					// Dbo()->Object->Property->RenderInput(TRUE, 'Account');
					// Dbo()->Object->Property->RenderInput(TRUE);					
					dboRender('Input',TRUE);
				?>	
			</tr>
			<tr>
				<?php dboRender('Label',TRUE); ?>	
			</tr>
			<tr>
				<?php dboRender('Other',TRUE); ?>	
			</tr>
		</table>
		<?php
		
		//HTML is OK here, to define structures which enclose these objects
	}
}

?>
