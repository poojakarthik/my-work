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
	// This defines the popup content for the "Build PDF" functionality, allowing 
	// the user to specify a hypothetical date and time for when the PDF will
	// be built
	private $_strBuildSamplePDFPopupContent = "";

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
		
		$this->LoadJavascript("document_template");
		if ($intContext != HTML_CONTEXT_VIEW)
		{
			$this->LoadJavascript("textarea");
		}
		$this->LoadJavascript("input_masks");
		$this->LoadJavascript("validation");
		$this->LoadJavascript("table_sort");
		$this->LoadJavascript("highlight");
		
		
		$strNow = GetCurrentISODateTime();
		$intNow = strtotime($strNow);
		$strDate = date("d/m/Y", $intNow);
		$strTime = date("H:i:s", $intNow);
		$strDocumentTemplateMediaTypeOptions = "";
		foreach ($GLOBALS['*arrConstant']['DocumentTemplateMediaType'] as $intConstant=>$arrConstant)
		{
			if ($intConstant == DOCUMENT_TEMPLATE_MEDIA_TYPE_ALL)
			{
				// Don't include this option
				continue;
			}
			$strDocumentTemplateMediaTypeOptions .= "<option value='{$intConstant}'>{$arrConstant['Description']}</option>";
		}
		
		$this->_strBuildSamplePDFPopupContent = "
<div id='PopupPageBody'>
	<div class='GroupedContent' style='position:relative;margin-bottom:5px;'>
		<span>Please specify the hypothetical date and time that this pdf will be generated on, and the media type to use</span>
		
		<div class='ContentSeparator'></div>
		
		<div style='margin-bottom:8px;'>
			<span style='top:2px'>Date</span>
			<input type='text' id='SamplePdfDate' value='$strDate' InputMask='ShortDate' maxlength='10' style='padding:1px 2px;position:absolute;left:50%;width:11em;border: solid 1px #D1D1D1' />
		</div>
		<div style='margin-bottom:8px;'>
			<span style='top:2px'>Time</span>
			<input type='text' id='SamplePdfTime' value='$strTime' InputMask='Time24Hr' maxlength='8' style='padding:1px 2px;position:absolute;left:50%;width:11em;border: solid 1px #D1D1D1'/>
		</div>
		<div style='margin-bottom:8px;'>
			<span style='top:2px'>Media Type</span>
			<select id='SamplePdfMediaType' style='position:absolute;left:50%;width:11em;border: solid 1px #D1D1D1'>
				$strDocumentTemplateMediaTypeOptions
			</select>
		</div>

	</div>
<!-- This form is no longer required -->
	<form id='FormBuildSamplePDF' name='FormBuildSamplePDF' method='post' target='PdfWindow' action='flex.php/CustomerGroup/BuildSamplePDF/'>
		<input type='hidden' name='Template.Source' value=''></input>
		<input type='hidden' name='Generation.Date' value=''></input>
		<input type='hidden' name='Generation.Time' value=''></input>
		<input type='hidden' name='CustomerGroup.Id' value=''></input>
		<input type='hidden' name='DocumentTemplateType.Id' value=''></input>
		<input type='hidden' name='Schema.Id' value=''></input>
	</form>
<!-- This form is no longer required -->

	<div class='ButtonContainer'>
		<div style='float:right'>
			<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>
			<input type='button' value='Build PDF' onclick='Vixen.DocumentTemplate.BuildSamplePDF(true)'></input>
		</div>
	</div>
	<script type='text/javascript'>RegisterAllInputMasks();</script>
</div>";
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
		$jsonObjTemplate				= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema					= Json()->encode(DBO()->DocumentTemplateSchema->_arrProperties);
		$jsonInsertResourcePopupContent	= Json()->encode($this->_BuildResourceSelectorPopupContent());
		$jsonBuildPdfPopupContent		= Json()->encode($this->_strBuildSamplePDFPopupContent);
		
		
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
		echo "<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseAddPage($jsonObjTemplate, $jsonObjSchema, $jsonObjResourceTypes, $jsonInsertResourcePopupContent, $jsonBuildPdfPopupContent)</script>\n";
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
		// Escape the Source Code
		DBO()->DocumentTemplate->Source = htmlspecialchars(DBO()->DocumentTemplate->Source->Value, ENT_QUOTES);
		
		// Prepare data
		$jsonObjTemplate				= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema					= Json()->encode(DBO()->DocumentTemplateSchema->_arrProperties);
		$jsonInsertResourcePopupContent	= Json()->encode($this->_BuildResourceSelectorPopupContent());
		$jsonBuildPdfPopupContent		= Json()->encode($this->_strBuildSamplePDFPopupContent);

		// Build the array of ResourceTypes
		$arrResourceTypes = Array();
		foreach (DBL()->DocumentResourceType as $dboResourceType)
		{
			$arrResourceTypes[$dboResourceType->Id->Value] = Array(	"PlaceHolder" => $dboResourceType->PlaceHolder->Value,
																	"TagSignature" => str_replace("[PlaceHolder]", $dboResourceType->PlaceHolder->Value, $dboResourceType->TagSignature->Value)
																	);
		}
		$jsonObjResourceTypes = Json()->encode($arrResourceTypes);
		
		echo "<!-- START HtmlTemplateDocumentTemplate (rendered in 'EDIT' context) -->\n";
		$this->RenderForm();
		echo "<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseEditPage($jsonObjTemplate, $jsonObjSchema, $jsonObjResourceTypes, $jsonInsertResourcePopupContent, $jsonBuildPdfPopupContent)</script>\n";
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
		$intCustomerGroup		= DBO()->CustomerGroup->Id->Value;
		$intTemplateId			= (DBO()->DocumentTemplate->Id->IsSet)? DBO()->DocumentTemplate->Id->Value : "null";
		$strDescription			= DBO()->DocumentTemplate->Description->Value;
		$strSchemaVersion		= DBO()->DocumentTemplateSchema->Version->Value;
		$intPropertyValueLeft	= "120";
		$strTextAreaHeight		= "4in";
		$strEffectiveOn			= (DBO()->DocumentTemplate->EffectiveOn->Value != NULL)? OutputMask()->LongDateAndTime(DBO()->DocumentTemplate->EffectiveOn->Value) : "Undeclared";
		
		// Escape the Source Code
		DBO()->DocumentTemplate->Source = htmlspecialchars(DBO()->DocumentTemplate->Source->Value, ENT_QUOTES);
		
		$jsonObjTemplate			= Json()->encode(DBO()->DocumentTemplate->_arrProperties);
		$jsonObjSchema				= Json()->encode(DBO()->DocumentTemplateSchema->_arrProperties);
		$jsonBuildPdfPopupContent	= Json()->encode($this->_strBuildSamplePDFPopupContent);


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

	<textarea id='DocumentTemplate.Source' name='DocumentTemplate.Source' wrap='off' readonly='true' style='overflow:auto;width:100%;height:$strTextAreaHeight;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'></textarea>

	<div class='ButtonContainer'>
		<div class='Left'>
			<input type='button' id='ButtonBuildPDF' class='InputSubmit' value='Build PDF' onclick='Vixen.DocumentTemplate.BuildSamplePDF()'></input>
		</div>
	</div>
</div>
<div class='Separator'></div>
<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseViewPage($jsonObjTemplate, $jsonObjSchema, $jsonBuildPdfPopupContent)</script>
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

	<textarea id='DocumentTemplate.Source' name='DocumentTemplate.Source' wrap='off' style='overflow:auto;width:100%;height:$strTextAreaHeight;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'></textarea>

	<div class='ButtonContainer'>
		<div class='Left'>
			<input type='button' id='ButtonInsertImage' value='Insert Resource' onclick='Vixen.DocumentTemplate.InsertResource()'></input>
			<input type='button' id='ButtonBuildPDF' value='Build PDF' onclick='Vixen.DocumentTemplate.BuildSamplePDF()'></input>
		</div>
		<div class='Right'>
			<input type='button' id='ButtonSave' value='Save' onclick='Vixen.DocumentTemplate.Save()'></input>
		</div>
	</div>

</div>
<div class='Separator'></div>\n";
	}
	
	
	//------------------------------------------------------------------------//
	// _BuildResourceSelectorPopupContent
	//------------------------------------------------------------------------//
	/**
	 * _BuildResourceSelectorPopupContent()
	 *
	 * Builds the html required for the Resource Selector Popup, which is displayed when the "Insert Resource" button is used on this form
	 *
	 * Builds the html required for the Resource Selector Popup, which is displayed when the "Insert Resource" button is used on this form
	 * Returns the code required of the Resource Selector popup, which will be stored in DOM memory, for when it is needed
	 * 
	 * @return	string		Html content for the popup
	 * @method
	 */
	private function _BuildResourceSelectorPopupContent()
	{
		// Build the Table listing all the available ResourceTypes
		Table()->ResourceType->SetHeader("Resource Type", "Description");
		Table()->ResourceType->SetWidth("40%", "60%");
		Table()->ResourceType->SetAlignment("Left", "Left");
		Table()->ResourceType->SetSortable(TRUE);
		Table()->ResourceType->SetSortFields("PlaceHolder", "Description");
		Table()->ResourceType->SetPageSize(10);
		Table()->ResourceType->RowHighlighting = TRUE;
		
		foreach (DBL()->DocumentResourceType as $dboResourceType)
		{
			Table()->ResourceType->AddRow($dboResourceType->PlaceHolder->Value, htmlspecialchars($dboResourceType->Description->Value, ENT_QUOTES));
			Table()->ResourceType->SetOnClick("Vixen.DocumentTemplate.InsertResource({$dboResourceType->Id->Value})");
		}

		ob_start();
		echo "
<div id='PopupPageBody'>\n";
		Table()->ResourceType->Render();
		
		echo "
	<div class='ButtonContainer'>
		<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)' style='float:right'></input>
	</div>
</div>
				";

		$strPopupContent = ob_get_clean();
		return $strPopupContent;
	}
	
	
}

?>
