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
		$intCustomerGroup		= DBO()->CustomerGroup->Id->Value;
		$arrResourceTypes		= DBO()->DocumentResourceTypes->AsArray->Value;
		$arrFileTypes			= DBO()->FileTypes->AsArray->Value;
		$jsonResourceTypes		= Json()->Encode($arrResourceTypes);
		$jsonFileTypes			= Json()->Encode($arrFileTypes);
		$intPropertyValueLeft	= 120;
		
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
<!-- START HtmlTemplateDocumentResourceManagement -->
<div class='SmallSeparator'></div>

<div id='Container_ResourceHistory' style='width:100%;'></div>
<script type='text/javascript'>Vixen.DocumentResourceManagement.Initialise($jsonResourceTypes, $jsonFileTypes, $intCustomerGroup)</script>
<!-- END HtmlTemplateDocumentResourceManagement -->
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
	function GetHistory($arrResources)
	{
		$strPlaceHolder = DBO()->DocumentResourceType->PlaceHolder->Value;
		
		// Build the code to draw the "Upload New Resource" button, but only if the user has permission to
		$strAddResourceButtonHtml = "";
		if (AuthenticatedUser()->UserHasPerm(DBO()->DocumentResourceType->PermissionRequired->Value))
		{
			$strHref = Href()->AddDocumentResource(DBO()->CustomerGroup->Id->Value, DBO()->DocumentResourceType->Id->Value, $strPlaceHolder);
			$strHref = htmlspecialchars($strHref, ENT_QUOTES);
			$strAddResourceButtonHtml = "\n<input type='button' value='Upload New' onClick='$strHref'></input>\n";
		}
		
		// Build the Table listing all the available ResourceTypes
		Table()->Resources->SetHeader("Uploaded", "File Name", "Effective", "&nbsp;", "&nbsp;");
		Table()->Resources->SetWidth("10%", "68%", "10%", "2%", "10%");
		Table()->Resources->SetAlignment("Left", "Left", "Left", "Center", "Left");
		
		// Sorting functionality cannot currently handle sorting dates in the format "dd/mm/yyyy" 
		// as you can't just do a string comparison. Currently the pagination funcationality only works
		// if sorting is turned on
		Table()->Resources->SetSortable(TRUE);
		Table()->Resources->SetSortFields(NULL, NULL, NULL, NULL, NULL);
		Table()->Resources->SetPageSize(10);
		
		foreach ($arrResources as $arrResource)
		{
			if ($arrResource['EndDatetime'] == END_OF_TIME)
			{
				$strEnd = "Indefinite";
			}
			else
			{
				$intEnd	= strtotime($arrResource['EndDatetime']);
				$strEnd	= "<span title='". date("g:i:s A", $intEnd) ."'>". date("M j, Y", $intEnd) ."</span>";
			}
			
			$intStart	= strtotime($arrResource['StartDatetime']);
			$strStart	= "<span title='". date("g:i:s A", $intStart) ."'>". date("M j, Y", $intStart) ."</span>";
			
			$intCreatedOn	= strtotime($arrResource['CreatedOn']);
			$strCreatedOn	= "<span title='". date("g:i:s A", $intCreatedOn) ."'>". date("M j, Y", $intCreatedOn) ."</span>";
			
			//TODO! make sure you draw a line through those that are completely overridden
			
			$strFilename = htmlspecialchars($arrResource['OriginalFilename']);
			
			Table()->Resources->AddRow($strCreatedOn, $strFilename, $strStart, "-", $strEnd);
			Table()->Resources->SetOnClick("Vixen.DocumentResourceManagement.ShowResource({$arrResource['Id']})");
		}
		if (count($arrResources) == 0)
		{
			// There are no resources of this ResourceType
			Table()->Resources->AddRow("No records to display");
			Table()->Resources->SetRowAlignment("left");
			Table()->Resources->SetRowColumnSpan(5);
		}
		ob_start();
		echo "
<!-- START HtmlTemplateDocumentResourceManagement (ResourceType History Component) -->
<div style='width:100%;height:30px'>
	<h2 style='float:left'>$strPlaceHolder</h2>
	<div style='float:right'>$strAddResourceButtonHtml</div>	
</div>
				";
				
		Table()->Resources->Render();
		
		echo "\n<!-- END HtmlTemplateDocumentResourceManagement (ResourceType History Component) -->\n";
		return ob_get_clean();
	}
	
}

?>
