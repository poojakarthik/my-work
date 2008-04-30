<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// resource_management.php
//----------------------------------------------------------------------------//
/**
 * resource_management
 *
 * HTML Template for the Document Resource Management HTML object
 *
 * HTML Template for the Document Resource Management HTML object
 *
 * @file		resource_management.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateDocumentResourceManagement
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDocumentResourceManagement
 *
 * HTML Template class for the DocumentResourceManagement HTML object
 *
 * HTML Template class for the DocumentResourceManagement HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateDocumentResourceManagement
 * @extends	HtmlTemplate
 */
class HtmlTemplateDocumentResourceManagement extends HtmlTemplate
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
		
		$this->LoadJavascript("document_resource_management");
		$this->LoadJavascript("validation");
		$this->LoadJavascript("table_sort");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_TABLE:
				$this->RenderHistory();
				break;
				
			case HTML_CONTEXT_DEFAULT:
			default:
				$this->RenderPage();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// RenderPage
	//------------------------------------------------------------------------//
	/**
	 * RenderPage()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderPage()
	{
		$arrResourceTypes	= DBO()->DocumentResourceTypes->AsArray->Value;
		$arrFileTypes		= DBO()->FileTypes->AsArray->Value;
		$jsonResourceTypes	= Json()->Encode($arrResourceTypes);
		$jsonFileTypes		= Json()->Encode($arrFileTypes);

		// Build the Table listing all the available ResourceTypes
		Table()->ResourceType->SetHeader("Resource Type", "Description");
		Table()->ResourceType->SetWidth("40%", "60%");
		Table()->ResourceType->SetAlignment("Left", "Left");
		Table()->ResourceType->SetSortable(TRUE);
		Table()->ResourceType->SetSortFields("PlaceHolder", "Description");
		Table()->ResourceType->SetPageSize(8);
		Table()->ResourceType->RowHighlighting = TRUE;
		
		foreach ($arrResourceTypes as $arrResourceType)
		{
			Table()->ResourceType->AddRow($arrResourceType['PlaceHolder'], htmlspecialchars($arrResourceType['Description'], ENT_QUOTES));
			Table()->ResourceType->SetOnClick("Vixen.DocumentResourceManagement.ShowHistory({$arrResourceType['Id']})");
		}
		
		Table()->ResourceType->Render();
		
		echo	"
<div class='SmallSeparator'>
<div class='GroupedContent'>
	Insert the upload controls here
	Don't forget to specify a start date AND an end date
</div>
<div class='SmallSeparator'></div>
<div id='Container_ResourceHistory' style='width:100%;'>
	Insert the ResourceHistory table here
</div>
<script type='text/javascript'>Vixen.DocumentResourceManagement.Initialise($jsonResourceTypes, $jsonFileTypes)</script>
				";
	}
	
	//------------------------------------------------------------------------//
	// GetHistory
	//------------------------------------------------------------------------//
	/**
	 * GetHistory()
	 *
	 * Builds the VixenTable representing the history for a given DocumentResourceType and CustomerGroup
	 *
	 * Builds the VixenTable representing the history for a given DocumentResourceType and CustomerGroup
	 *
	 * @param	string		$strResourceTypePlaceHolder		PlaceHolder name for the ResourceType
	 * @param	array		$arrResources					array of records from the DocumentResource table (every column)
	 * 														ordered by CreatedOn DESC, StartDatetime DESC, representing the history
	 *
	 * @return	string		html code used to render the table
	 * @method	GetHistory
	 */
	function GetHistory($strResourceTypePlaceHolder, $arrResources)
	{
		// Build the Table listing all the available ResourceTypes
		Table()->Resources->SetHeader("File Name", "Uploaded", "Starts", "Ends");
		Table()->Resources->SetWidth("55%", "15%", "15%", "15%");
		Table()->Resources->SetAlignment("Left", "Left", "Left", "Left");
		
		// Sorting functionality cannot currently handle sorting dates in the format "dd/mm/yyyy" 
		// as you can't just do a string comparison. Currently the pagination funcationality only works
		// if sorting is turned on
		Table()->Resources->SetSortable(TRUE);
		Table()->Resources->SetSortFields(NULL, NULL);
		Table()->Resources->SetPageSize(10);
		
		foreach ($arrResources as $arrResource)
		{
			// TODO! Make sure you display "Indefinate", instead of 31/12/9999
			if ($arrResource['EndDatetime'] == END_OF_TIME)
			{
				$strEnd = "Indefinite";
			}
			else
			{
				//TODO!
			}
			
			
			Table()->ResourceType->AddRow($arrResourceType['PlaceHolder'], htmlspecialchars($arrResourceType['Description'], ENT_QUOTES));
			Table()->ResourceType->SetOnClick("Vixen.DocumentResourceManagement.ShowHistory({$arrResourceType['Id']})");
		}
		
		ob_start();
		echo "<h2 class='ResourceType'>Resource History - $strResourceTypePlaceHolder</h2>";
		Table()->Resources->Render();
		return ob_get_flush();
	}
}

?>
