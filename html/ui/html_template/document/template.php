<?php
//----------------------------------------------------------------------------//
// HtmlTemplateDocumentTemplate
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDocumentTemplate
 *
 * The Document Template HTML Template object
 *
 * The Document Template HTML Template object
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateDocumentTemplate
 * @extends	HtmlTemplate
 */
class HtmlTemplateDocumentTemplate extends HtmlTemplate
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
		
		$this->LoadJavascript("document_template");
		$this->LoadJavascript("textarea");
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
			case HTML_CONTEXT_NEW:
				$this->RenderNew();
				break;
				
			case HTML_CONTEXT_EDIT:
				echo "Render Edit for DocumentTemplate Html Template object";
				break;
				
			case HTML_CONTEXT_VIEW:
				echo "Render View for DocumentTemplate Html Template object";
				break;
			
			default:
				echo "ERROR: there is no default rendering context for the DocumentTemplate Html Template object";
				return;
		}
	}

	
	//------------------------------------------------------------------------//
	// RenderNew
	//------------------------------------------------------------------------//
	/**
	 * RenderNew()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderNew()
	{
		// Prepare data
		$strSource			= DBO()->DocumentTemplate->Source->Value;
		$intCustomerGroup	= DBO()->CustomerGroup->Id->Value;
		$intTemplateId		= (DBO()->DocumentTemplate->Id->IsSet)? DBO()->DocumentTemplate->Id->Value : "null";
		$strDescription		= DBO()->DocumentTemplate->Description->Value;
		$strSchemaVersion	= DBO()->DocumentSchema->Version->Value;
		$intTextBoxLeft		= "110px";
		$strTextAreaHeight	= "4in";
		
		$jsonObjTemplate	= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema		= Json()->encode(DBO()->DocumentSchema->_arrProperties);

		echo 	"
<!-- START HtmlTemplateDocumentTemplate (rendered in 'NEW' context) -->
<div class='GroupedContent'>
	<div class='GroupedContent' style='height:70px;margin-bottom:5px'>
		
		<div style='float:left;position:relative;width:750px;height:70px'>
			<div style='margin-bottom:8px'>
				<span style='top:2px'>Schema Version</span>
				<span style='top:2px;position:absolute;left:$intTextBoxLeft;'>$strSchemaVersion</span>
			</div>
			<div style='margin-bottom:8px'>
				<span style='top:2px'>Description</span>
				<input type='text' id='DocumentTemplate.Description' value='$strDescription' maxlength='255' style='padding:1px;position:absolute;left:$intTextBoxLeft;width:600px;border: solid 1px #D1D1D1' />
			</div>
		</div>

		<div style='float:left;position:relative;width:101px;height:70px'>
				<input type='button' class='InputSubmit' value='Save' style='position:absolute;bottom:0px;right:0px' onclick='Vixen.DocumentTemplate.Save()'></input>
		</div>
	</div>

	<textarea id='DocumentTemplate.Source' name='DocumentTemplate.Source' wrap='off' style='overflow:auto;width:100%;height:$strTextAreaHeight;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'>$strSource</textarea>

</div>
<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseAddPage($jsonObjTemplate, $jsonObjSchema)</script>
<!-- END HtmlTemplateDocumentTemplate -->
				";	
	}
}

?>
