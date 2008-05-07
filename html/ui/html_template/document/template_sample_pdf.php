<?php
//----------------------------------------------------------------------------//
// HtmlTemplateDocumentTemplateSamplePDF
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDocumentTemplateSamplePDF
 *
 * The Document Template SamplePDF HTML Template object
 *
 * The Document Template SamplePDF HTML Template object
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateDocumentTemplateSamplePDF
 * @extends	HtmlTemplate
 */
class HtmlTemplateDocumentTemplateSamplePDF extends HtmlTemplate
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
		$this->_intContext			= $intContext;
		$this->_strContainerDivId	= $strId;
		
		//$this->LoadJavascript("document_template_sample_pdf");
		$this->LoadJavascript("date_textbox");
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
		$intCustomerGroup			= (DBO()->CustomerGroup->Id->Value)? DBO()->CustomerGroup->Id->Value : 0;
		$intDocumentTemplateType	= (DBO()->DocumentTemplateType->Id->Value)? DBO()->DocumentTemplateType->Id->Value : 0;
		
		// Build the combobox for the
		


		// Build the array of ResourceTypes
		$arrResourceTypes = Array();
		foreach (DBL()->DocumentResourceType as $dboResourceType)
		{
			$arrResourceTypes[$dboResourceType->Id->Value] = Array(	"PlaceHolder" => $dboResourceType->PlaceHolder->Value,
																	"TagSignature" => str_replace("[PlaceHolder]", $dboResourceType->PlaceHolder->Value, $dboResourceType->TagSignature->Value)
																	);
		}
		$jsonObjResourceTypes = Json()->encode($arrResourceTypes);

		echo "<!-- START HtmlTemplateDocumentTemplate (rendered in 'NEW' context) -->\n";
		$this->RenderForm();
		echo "<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseAddPage($jsonObjTemplate, $jsonObjSchema, $jsonObjResourceTypes, $jsonInsertResourcePopupContent)</script>\n";
		echo "<!-- END HtmlTemplateDocumentTemplate -->\n";	
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
				<input type='text' id='DocumentTemplate.EffectiveOn' InputMask='ShortDate' value='$strEffectiveOn' maxlength='10' style='visibility:hidden;display:none;position:absolute;left:". ($intPropertyValueLeft + 120) ."px;width:85px;border: solid 1px #D1D1D1'/>
			</div>
		</div>
	</div>

	<textarea id='DocumentTemplate.Source' name='DocumentTemplate.Source' wrap='off' style='overflow:auto;width:100%;height:$strTextAreaHeight;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'>$strSource</textarea>

	<div class='ButtonContainer'>
		<div class='Left'>
			<input type='button' id='ButtonInsertImage' class='InputSubmit' value='Insert Resource' onclick='Vixen.DocumentTemplate.InsertResource()'></input>
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
