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
		$this->LoadJavascript("validation");
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
				$this->RenderEdit();
				break;
				
			case HTML_CONTEXT_VIEW:
				$this->RenderView();
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
		$jsonObjTemplate	= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema		= Json()->encode(DBO()->DocumentTemplateSchema->_arrProperties);

		echo "<!-- START HtmlTemplateDocumentTemplate (rendered in 'NEW' context) -->\n";
		$this->RenderForm();
		echo "<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseAddPage($jsonObjTemplate, $jsonObjSchema)</script>\n";
		echo "<!-- END HtmlTemplateDocumentTemplate -->\n";	
	}

	//------------------------------------------------------------------------//
	// RenderEdit
	//------------------------------------------------------------------------//
	/**
	 * RenderEdit()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderEdit()
	{
		// Prepare data
		$jsonObjTemplate	= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema		= Json()->encode(DBO()->DocumentTemplateSchema->_arrProperties);

		echo "<!-- START HtmlTemplateDocumentTemplate (rendered in 'EDIT' context) -->\n";
		$this->RenderForm();
		echo "<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseEditPage($jsonObjTemplate, $jsonObjSchema)</script>\n";
		echo "<!-- END HtmlTemplateDocumentTemplate -->\n";	
	}
	
	//------------------------------------------------------------------------//
	// RenderView
	//------------------------------------------------------------------------//
	/**
	 * RenderView()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderView()
	{
		// Prepare data
		$strSource				= DBO()->DocumentTemplate->Source->Value;
		$intCustomerGroup		= DBO()->CustomerGroup->Id->Value;
		$intTemplateId			= (DBO()->DocumentTemplate->Id->IsSet)? DBO()->DocumentTemplate->Id->Value : "null";
		$strDescription			= DBO()->DocumentTemplate->Description->Value;
		$strSchemaVersion		= DBO()->DocumentTemplateSchema->Version->Value;
		$intPropertyValueLeft	= "120";
		$strTextAreaHeight		= "4in";
		$strEffectiveOn			= (DBO()->DocumentTemplate->EffectiveOn->Value != NULL)? OutputMask()->LongDateAndTime(DBO()->DocumentTemplate->EffectiveOn->Value) : "Undeclared";
		
		$jsonObjTemplate	= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema		= Json()->encode(DBO()->DocumentTemplateSchema->_arrProperties);

		echo "
<!-- START HtmlTemplateDocumentTemplate (rendered in 'VIEW' context) -->
<div class='GroupedContent'>
	<div class='GroupedContent' style='height:70px;margin-bottom:5px'>
		<div style='position:relative;margin-bottom:8px'>
			<span style='top:2px'>Schema Version</span>
			<span style='top:2px;position:absolute;left:$intPropertyValueLeft;'>$strSchemaVersion</span>
		</div>
		<div style='position:relative;margin-bottom:8px'>
			<span style='top:2px'>Description</span>
			<span id='DocumentTemplate.Description' style='position:absolute;top:2px;left:{$intPropertyValueLeft}px;overflow:hidden;' >$strDescription</span>
		</div>
		<div style='position:relative;margin-bottom:8px'>
			<span style='top:2px'>Effective On</span>
			<span id='DocumentTemplate.EffectiveOn' style='position:absolute;top:2px;left:{$intPropertyValueLeft}px;' >$strEffectiveOn</span>
		</div>
	</div>

	<textarea id='DocumentTemplate.Source' name='DocumentTemplate.Source' wrap='off' readonly='true' style='overflow:auto;width:100%;height:$strTextAreaHeight;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'>$strSource</textarea>

	<div class='ButtonContainer'>
		<div class='Left'>
			<input type='button' id='ButtonBuildPDF' class='InputSubmit' value='Build PDF' onclick='Vixen.DocumentTemplate.BuildSamplePDF()'></input>
		</div>
	</div>
</div>
<div class='Separator'></div>
<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseViewPage($jsonObjTemplate, $jsonObjSchema)</script>
<!-- END HtmlTemplateDocumentTemplate -->\n";
	}

	//------------------------------------------------------------------------//
	// RenderForm
	//------------------------------------------------------------------------//
	/**
	 * RenderForm()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderForm()
	{
		// Prepare data
		$strSource				= DBO()->DocumentTemplate->Source->Value;
		$strEffectiveOn			= DBO()->DocumentTemplate->EffectiveOn->Value;
		$intCustomerGroup		= DBO()->CustomerGroup->Id->Value;
		$intTemplateId			= (DBO()->DocumentTemplate->Id->IsSet)? DBO()->DocumentTemplate->Id->Value : "null";
		$strDescription			= DBO()->DocumentTemplate->Description->Value;
		$strSchemaVersion		= DBO()->DocumentTemplateSchema->Version->Value;
		$intPropertyValueLeft	= 120;
		$strTextAreaHeight		= "4in";

		if ($strEffectiveOn != NULL)
		{
			$strEffectiveOn = OutputMask()->ShortDate($strEffectiveOn);
		}

		echo 	"
<div class='GroupedContent'>
	<div class='GroupedContent' style='height:70px;margin-bottom:5px'>
		
		<div style='float:left;position:relative;width:750px;height:70px'>
			<div style='margin-bottom:8px'>
				<span style='top:2px'>Schema Version</span>
				<span style='top:2px;position:absolute;left:$intPropertyValueLeft;'>$strSchemaVersion</span>
			</div>
			<div style='margin-bottom:8px'>
				<span style='top:2px'>Description</span>
				<input type='text' id='DocumentTemplate.Description' value='$strDescription' maxlength='255' style='padding:1px 2px;position:absolute;left:{$intPropertyValueLeft}px;width:600px;border: solid 1px #D1D1D1' />
			</div>
			<div style='margin-bottom:8px'>
				<span style='top:2px'>Effective On</span>
				<select id='EffectiveOnCombo' style='position:absolute;left:{$intPropertyValueLeft}px;width:110px;border: solid 1px #D1D1D1'>
					". (($strEffectiveOn == NULL)? "<option value='undeclared'>Undeclared</option>" : "") ."
					<option value='immediately'>Immediately</option>
					<option value='date'>Date</option>
				</select>
				<input type='text' id='DocumentTemplate.EffectiveOn' value='$strEffectiveOn' maxlength='10' style='visibility:hidden;display:none;position:absolute;left:". ($intPropertyValueLeft + 120) ."px;width:85px;border: solid 1px #D1D1D1'/>
			</div>
		</div>
	</div>

	<textarea id='DocumentTemplate.Source' name='DocumentTemplate.Source' wrap='off' style='overflow:auto;width:100%;height:$strTextAreaHeight;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'>$strSource</textarea>

	<div class='ButtonContainer'>
		<div class='Left'>
			<input type='button' id='ButtonInsertImage' class='InputSubmit' value='Insert Image' onclick='Vixen.DocumentTemplate.InsertImage()'></input>
			<input type='button' id='ButtonBuildPDF' class='InputSubmit' value='Build PDF' onclick='Vixen.DocumentTemplate.BuildSamplePDF()'></input>
		</div>
		<div class='Right'>
			<input type='button' id='ButtonSave' class='InputSubmit' value='Save' onclick='Vixen.DocumentTemplate.Save()'></input>
		</div>
	</div>

</div>
<div class='Separator'></div>\n";
	}
	
}

?>
