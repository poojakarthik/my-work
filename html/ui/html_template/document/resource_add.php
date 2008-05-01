<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// resource_add.php
//----------------------------------------------------------------------------//
/**
 * resource_add
 *
 * HTML Template for the Document Resource Add popup
 *
 * HTML Template for the Document Resource Add popup
 *
 * @file		resource_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateDocumentResourceAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDocumentResourceAdd
 *
 * HTML Template class for the DocumentResourceAdd HTML object
 *
 * HTML Template class for the DocumentResourceAdd HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateDocumentResourceAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateDocumentResourceAdd extends HtmlTemplate
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
		
		//$this->LoadJavascript("document_resource_add");
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
			case HTML_CONTEXT_IFRAME:
				$this->RenderFileUploadComponent();
				break;
				
			case HTML_CONTEXT_DEFAULT:
			default:
				$this->RenderPopup();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// RenderPopup
	//------------------------------------------------------------------------//
	/**
	 * RenderPopup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderPopup()
	{
		$intCustomerGroup		= DBO()->CustomerGroup->Id->Value;
		$jsonResourceType		= Json()->Encode(DBO()->DocumentResourceType->_arrProperties);
		$intPropertyValueLeft	= 120;
		
		echo	"
<!-- START HtmlTemplateDocumentResourceAdd -->
<div class='GroupedContent' style='position:relative'>

	<div style='margin-bottom:8px'>
		<span style='top:2px'>Starting</span>
		<select id='StartCombo' style='position:absolute;left:{$intPropertyValueLeft}px;width:110px;border: solid 1px #D1D1D1'>
			<option value='immediately'>Immediately</option>
			<option value='date'>Date</option>
		</select>
		<input type='text' id='DocumentTemplate.Start' value='' maxlength='10' style='visibility:hidden;display:none;position:absolute;left:". ($intPropertyValueLeft + 120) ."px;width:85px;border: solid 1px #D1D1D1'/>
	</div>
	
	<div style='margin-bottom:8px'>
		<span style='top:2px'>Ending</span>
		<select id='EndCombo' style='position:absolute;left:{$intPropertyValueLeft}px;width:110px;border: solid 1px #D1D1D1'>
			<option value='indefinite'>Indefinite</option>
			<option value='date'>Date</option>
		</select>
		<input type='text' id='DocumentTemplate.End' value='' maxlength='10' style='visibility:hidden;display:none;position:absolute;left:". ($intPropertyValueLeft + 120) ."px;width:85px;border: solid 1px #D1D1D1'/>
	</div>

	<iframe src='flex.php/CustomerGroup/UploadResource/' width='100%' height='25px' frameborder='0' id='FrameUploadResource' name='FrameUploadResource'></iframe>

</div>
<div class='ButtonContainer'>
	<div class='Right'>
		<input type='button' id='CancelButton' class='InputSubmit' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>
		<input type='button' id='ButtonSave' class='InputSubmit' value='Save' onclick='Vixen.DocumentResourceManagement.UploadResource()'></input>
	</div>
</div>
<script type='text/javascript'>Vixen.DocumentResourceAdd.Initialise($intCustomerGroup, $jsonResourceType)</script>
<!-- END HtmlTemplateDocumentResourceAdd -->
				";
	}
	
	//------------------------------------------------------------------------//
	// RenderFileUploadComponent
	//------------------------------------------------------------------------//
	/**
	 * RenderFileUploadComponent()
	 *
	 * Renders the component that allows resource files to be uploaded
	 *
	 * Renders the component that allows resource files to be uploaded
	 *
	 * @method
	 */
	function RenderFileUploadComponent()
	{
		echo "
<!-- START HtmlTemplateDocumentResourceManagement (File Upload Component) -->
<form enctype='multipart/form-data' action='flex.php/CustomerGroup/UploadResource/' method='POST'>

	<input type='hidden' name='VixenFormId' value='UploadResource'></input>
	<input type='hidden' name='MAX_FILE_SIZE' value='" . RESOURCE_FILE_MAX_SIZE . "'></input>
	<input type='hidden' name='DocumentResource.Start' value='0'></input>
	<input type='hidden' name='DocumentResource.End' value='0'></input>
	<input type='hidden' name='DocumentResource.CustomerGroup' value='0'></input>
	
	<div style='margin-bottom:8px'>
		<span style='top:2px'>Resource</span>
		<input type='file' id='ResourceFile' name='ResourceFile' style='padding:1px 2px;position:absolute;left:115px;width:auto;border: solid 1px #D1D1D1' size='50'></input>
	</div>
	
</form>
<!-- END HtmlTemplateDocumentResourceManagement (File Upload Component) -->
			";
		
		return;
		
		if (DBO()->RateGroupImport->Success->Value)
		{
			// The import was successful
			$objRateGroup = Json()->encode(DBO()->RateGroupImport->ArrRateGroup->Value);
			$objReport = Json()->encode(DBO()->RateGroupImport->Report->Value);
			echo "<script type='text/javascript'>top.Vixen.RateGroupImport.OnImportSuccess($objReport, $objRateGroup)</script>\n";
		}
		elseif (DBO()->RateGroupImport->Success->Value === FALSE)
		{
			// The import failed. Display the import report
			$objReport = Json()->encode(DBO()->RateGroupImport->Report->Value);
			echo "<script type='text/javascript'>top.Vixen.RateGroupImport.OnImportFailure($objReport)</script>\n";
		}
	}
}

?>
