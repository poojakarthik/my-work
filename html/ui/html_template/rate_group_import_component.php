<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import_component.php
//----------------------------------------------------------------------------//
/**
 * rate_group_import_component
 *
 * HTML Template for the Rate Group Import Component HTML object
 *
 * HTML Template for the Rate Group Import Component HTML object
 * This HTML object will be embedded in an iframe which will be included in the
 * "RateGroup Import" popup.  An iframe is required, because uploading files
 * can not be done using ajax requests; a proper form submittion must be made
 *
 * @file		rate_group_import_component.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupImportComponent
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupImportComponent
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupImportComponent
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupImportComponent extends HtmlTemplate
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
		echo "<form enctype='multipart/form-data' action='flex.php/RateGroup/ImportCSV/' method='POST'>";
		echo "<input type='hidden' name='VixenFormId' value='ImportRateGroup'></input>";
		echo "<input type='hidden' id='SubmitButtonValue' name='VixenButtonId' value=''></input>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='" . RATEGROUP_IMPORT_MAXSIZE . "'>";
		
		DBO()->RecordType->Id->RenderHidden();
		DBO()->RateGroup->Fleet->RenderHidden();
		
		// Display the file selector control
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span>&nbsp;&nbsp;</span>Rate Group CSV file :</div>\n";
		echo "   <input type='file' id='RateGroupCSVFile' name='RateGroupCSVFile' class='DefaultInputText' size='50'></input>\n";
		echo "</div>\n"; // DefaultElement
		
		echo "</form>\n";
		
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
