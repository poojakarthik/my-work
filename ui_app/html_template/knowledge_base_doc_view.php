<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// knowledge_base_doc_view.php
//----------------------------------------------------------------------------//
/**
 * knowledge_base_doc_view
 *
 * HTML Template for the knowledge base doc view HTML object
 *
 * HTML Template for the knowledge base doc view HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all information relating to a knowledge base document and can be embedded in
 * various Page Templates
 *
 * @file		knowledge_base_doc_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateKnowledgeBaseDocView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateKnowledgeBaseDocView
 *
 * HTML Template class for the knowledge base doc view HTML object
 *
 * HTML Template class for the knowledge base doc view HTML object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateKnowledgeBaseDocView
 * @extends	HtmlTemplate
 */
 
 
class HtmlTemplateKnowledgeBaseDocView extends HtmlTemplate
{
	function __construct()
	{
		// I don't currently know if this is necessary for this page
		$this->LoadJavascript("dhtml");
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
echo "<br>THIS IS WITHIN THE HTML TEMPLATE Render() METHOD";		
		// output the information about the document
		//TODO!!Joel, finish this off
		echo "<br>Article Id: " . DBO()->KnowledgeBase->Id->Value;
		
		echo "<br>Title: " . DBO()->KnowledgeBase->Title->Value;
		
		echo "<br>Abstract"
		
/*		
		?>
		<form method='POST' action='account_view.php'>
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
					DBO()->Account->ABN->RenderInput(TRUE,1);
					DBO()->Account->BillingType->RenderOutput(TRUE);
					
				?>	
			</tr>
			<tr>
				<input type='submit' value='Submit'></input>
			</tr>
		</table>
		<?php
		//var_dump($_POST);
		//HTML is OK here, to define structures which enclose these objects
*/
	}
}

?>
