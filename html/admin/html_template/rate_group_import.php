<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import.php
//----------------------------------------------------------------------------//
/**
 * rate_group_import
 *
 * HTML Template for the Import Rate Group HTML object
 *
 * HTML Template for the Import Rate Group HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate group.
 *
 * @file		rate_group_import.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupImport
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupImport
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupImport
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupImport extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("rate_group_import");		
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
	 *
	 * @method
	 */
	function Render()
	{
		// Build the url used for the iframe which is used to upload the RateGroup csv file
		$strRateGroupFleet = (DBO()->RateGroup->Fleet->Value)? "1" : "0";
		$intRecordTypeId = DBO()->RecordType->Id->Value;
		$strIframeSource = "flex.php/RateGroup/ImportCSV/?RateGroup.Fleet={$strRateGroupFleet}&RecordType.Id={$intRecordTypeId}";

		// Display known details of the RateGroup
		echo "<div class='GroupedContent'>";
		DBO()->RateGroup->ServiceType = DBO()->RecordType->ServiceType->Value;
		DBO()->RateGroup->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT); 
		DBO()->RecordType->Description->RenderOutput();
		DBO()->RateGroup->Fleet->RenderOutput();
		
		// File uploads can only be done using conventional form submittion (not via ajax), so it must be wrapped in a frame
		$strFrameId = "FrameRateGroupImport";
		echo "<iframe src='$strIframeSource' width='100%' height='25px' frameborder='0' id='$strFrameId' name='$strFrameId'></iframe>\n";
		
		echo "</div>"; // GroupedContent
		echo "<div class='SmallSeperator'></div>\n";

		// Display the Import Report
		$strImportReportId = "ImportReport";
		echo "<h2 class='Plan'>Import Report</h2>\n";
		echo "<div id='ContainerDiv_$strImportReportId' style='border: solid 1px #D1D1D1; padding: 5px 5px 5px 5px'>\n";
		echo "<div id='$strImportReportId' style='overflow:auto; line-height: 1.15; height:300px; width:auto; padding: 0px 3px 0px 3px'>\n";
		echo "</div></div>";

		// Create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		$this->Button("Import As Draft", "Vixen.RateGroupImport.ImportAsDraft();");
		$this->Button("Import And Commit", "Vixen.RateGroupImport.ImportAndCommit();");
		echo "</div></div>\n"; // Buttons
		
		echo "<script type='text/javascript'>Vixen.RateGroupImport.Initialise('$strFrameId', '$strImportReportId');</script>\n";
	}
}

?>
