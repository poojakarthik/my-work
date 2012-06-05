<?php
class HtmlTemplateDocumentResourceManagement extends HtmlTemplate {
	function __construct($intContext, $strId) {
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("document_resource_management");
		$this->LoadJavascript("table_sort");
		$this->LoadJavascript("highlight");
	}

	function Render() {
		switch ($this->_intContext) {
			case HTML_CONTEXT_TABLE:
				$this->RenderHistory();
				break;
				
			case HTML_CONTEXT_DEFAULT:
			default:
				$this->RenderPage();
				break;
		}
	}

	function RenderPage() {
		$intCustomerGroup = DBO()->CustomerGroup->Id->Value;
		$arrResourceTypes = DBO()->DocumentResourceTypes->AsArray->Value;
		
		// Build the Table listing all the available ResourceTypes
		Table()->ResourceType->SetHeader("Resource Type", "Description");
		Table()->ResourceType->SetWidth("40%", "60%");
		Table()->ResourceType->SetAlignment("Left", "Left");
		Table()->ResourceType->SetSortable(true);
		Table()->ResourceType->SetSortFields("PlaceHolder", "Description");
		Table()->ResourceType->SetPageSize(20);
		Table()->ResourceType->RowHighlighting = true;
		
		foreach ($arrResourceTypes as $arrResourceType) {
			Table()->ResourceType->AddRow($arrResourceType['PlaceHolder'], htmlspecialchars($arrResourceType['Description'], ENT_QUOTES));
			Table()->ResourceType->SetOnClick("Vixen.DocumentResourceManagement.ShowHistory({$arrResourceType['Id']}, false)");
		}
		?>
		<!-- START HtmlTemplateDocumentResourceManagement -->
		<?
		Table()->ResourceType->Render();
		?>
		<div class='SmallSeparator'></div>
		<div id='Container_ResourceHistory' style='width:100%;'></div>
		<script type='text/javascript'>Vixen.DocumentResourceManagement.Initialise(<?=$intCustomerGroup?>)</script>
		<!-- END HtmlTemplateDocumentResourceManagement -->
		<?;
	}
	
	function GetHistory($arrResources) {
		$strPlaceHolder = DBO()->DocumentResourceType->PlaceHolder->Value;
		
		// Build the code to draw the "Upload New Resource" button, but only if the user has permission to
		$bolUserCanEdit = AuthenticatedUser()->UserHasPerm(DBO()->DocumentResourceType->PermissionRequired->Value);
		$strAddResourceButtonHtml = "";
		if ($bolUserCanEdit) {
			$strHref = Href()->AddDocumentResource(DBO()->CustomerGroup->Id->Value, DBO()->DocumentResourceType->Id->Value, $strPlaceHolder);
			$strHref = htmlspecialchars($strHref, ENT_QUOTES);
			$strAddResourceButtonHtml = "<input type='button' value='Upload New' onClick='$strHref'></input>";
		}
		
		// Build the Table listing all the available ResourceTypes
		Table()->Resources->SetHeader("Uploaded", "File Name", "Effective", "&nbsp;", "&nbsp;", "&nbsp");
		Table()->Resources->SetWidth("10%", "60%", "10%", "2%", "10%", "8%");
		Table()->Resources->SetAlignment("Left", "Left", "Left", "Center", "Left", "Right");
		
		// Sorting functionality cannot currently handle sorting dates in the format "dd/mm/yyyy" 
		// as you can't just do a string comparison. Currently the pagination funcationality only works
		// if sorting is turned on
		Table()->Resources->SetSortable(true);
		Table()->Resources->SetSortFields(null, null, null, null, null);
		Table()->Resources->SetPageSize(20);
		
		foreach ($arrResources as $arrResource) {
			if ($arrResource['EndDatetime'] == END_OF_TIME) {
				$strEnd = "Indefinite";
			} else {
				$intEnd = strtotime($arrResource['EndDatetime']);
				$strEnd = "<span title='". date("g:i:s A", $intEnd) ."'>". date("M j, Y", $intEnd) ."</span>";
			}
			
			$intStart = strtotime($arrResource['StartDatetime']);
			$strStart = "<span title='". date("g:i:s A", $intStart) ."'>". date("M j, Y", $intStart) ."</span>";
			
			$intCreatedOn = strtotime($arrResource['CreatedOn']);
			$strCreatedOn = "<span title='". date("g:i:s A", $intCreatedOn) ."'>". date("M j, Y", $intCreatedOn) ."</span>";
			
			//TODO! make sure you draw a line through those that are completely overridden
			
			//TODO! add delete functionality
			
			$strFilename = htmlspecialchars($arrResource['OriginalFilename']);
			
			$strView = "<a href='". Href()->ViewDocumentResource($arrResource['Id']) ."' title='View Resource'><img src='img/template/view.png'></img></a>";
			$strDownload = "<a href='". Href()->ViewDocumentResource($arrResource['Id'], true) ."' title='Download Resource'><img src='img/template/download.png'></img></a>";
			
			Table()->Resources->AddRow($strCreatedOn, $strFilename, $strStart, "-", $strEnd, $strView . $strDownload);
		}
		if (count($arrResources) == 0) {
			// There are no resources of this ResourceType
			Table()->Resources->AddRow("No records to display");
			Table()->Resources->SetRowAlignment("left");
			Table()->Resources->SetRowColumnSpan(6);
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
		
		echo "\n<div class='Separator'></div>";
		echo "\n<!-- END HtmlTemplateDocumentResourceManagement (ResourceType History Component) -->\n";
		return ob_get_clean();
	}
	
}
