<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// details.php
//----------------------------------------------------------------------------//
/**
 * details
 *
 * HTML Template for the Service Details HTML object
 *
 * HTML Template for the Service Details HTML object
 * This file defines the class responsible for defining and rendering the layout
 * of the HTML Template object which displays Service details
 *
 * @file		details.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross and Joel
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateServiceDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceDetails
 *
 * A specific HTML Template object
 *
 * An service details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceDetails extends HtmlTemplate
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
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		$this->LoadJavascript("services_view");
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
			case HTML_CONTEXT_MINIMUM_DETAIL:
				$this->_RenderMinimumDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			case HTML_CONTEXT_BARE_DETAIL:
				$this->_RenderBareDetail();
				break;		
			case HTML_CONTEXT_TABULAR_DETAIL:
				$this->_RenderTabularDetail();
				break;				
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderTabularDetail();
	//------------------------------------------------------------------------//
	/**
	 * _RenderTabularDetail();
	 *
	 * Renders all services for a given Account
	 *
	 * Renders all services for a given Account
	 * This assumes it will be placed in a popup, as it includes a close button
	 *
	 * @method
	 */
	private function _RenderTabularDetail()
	{
		echo "<div class='PopupLarge'>\n";
		echo "<div  style='overflow:auto; height:300px'>\n";
		
		echo "<div class='NarrowForm'>\n";

		Table()->ServiceTable->SetHeader("FNN #", "Service Type", "Plan Name", "Status", "Actions");
		Table()->ServiceTable->SetWidth("15%", "15%", "20%","20%","20%");
		Table()->ServiceTable->SetAlignment("Left", "Left", "Left", "Left", "Left");
		
		foreach (DBL()->Service as $dboService)
		{
			switch ($dboService->Status->Value)
			{
				case SERVICE_ACTIVE:
					$strStatus = "<div class='DefaultRegularOutput'>".Active."</div>";//Opened On: ".$dboService->CreatedOn->Value."</div>";
					break;
				case SERVICE_DISCONNECTED:
					$strStatus = "<div class='DefaultRegularOutput'>".Disconnected."</div>";//Closes On: ".$dboService->ClosedOn->Value."</div>";
					break;
				case SERVICE_ARCHIVED:
					$strStatus = "<div class='DefaultRegularOutput'>".Archived."</div>";//Closes On: ".$dboService->ClosedOn->Value."</div>";
					break;					
			}
		
			// Returns the plan for each DBL object
			$strWhere = "NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime AND";
			$strWhere .= " ServiceRatePlan.Service = ".$dboService->Id->Value;
			DBO()->RatePlan->Where->SetString($strWhere);
		
			$arrColumns = Array("Service"=>"ServiceRatePlan.Service",
											"RatePlan"=>"ServiceRatePlan.RatePlan",
											"StartDatetime"=>"ServiceRatePlan.StartDatetime",
											"EndDatetime"=>"ServiceRatePlan.EndDatetime",
											"CreatedWhen"=>"ServiceRatePlan.CreatedOn",
											"Id"=>"RatePlan.Id",
											"Name"=>"RatePlan.Name");
	
			DBO()->RatePlan->SetColumns($arrColumns);
			DBO()->RatePlan->SetTable("ServiceRatePlan JOIN RatePlan ON RatePlan.Id = ServiceRatePlan.RatePlan");
			DBO()->RatePlan->OrderBy("ServiceRatePlan.CreatedOn DESC");
			DBO()->RatePlan->Load();

			$strChangePlanLink = Href()->ChangePlan($dboService->Id->Value);
			$strDivItem = $dboService->Id->Value;

			$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($dboService->Id->Value, DBO()->Account->Id->Value);
			$strDivItem = $dboService->Id->Value;

			if (DBO()->RatePlan->Name->Value == NULL)
			{
				$strRatePlanName = "<div class='DefaultRegularOutput' id='$strDivItem'><a href='$strChangePlanLink'>No Plan Selected</a></div>";
			}
			else
			{
				$strRatePlanName = "<div class='DefaultRegularOutput'><a href='$strViewServiceRatePlanLink'> ".DBO()->RatePlan->Name->Value."</a></div>";
			}			
					
			$strViewServiceNotesLink = Href()->ViewServiceNotes($dboService->Id->Value, DBO()->Note->NoteType->Value);
			$strOutputLink = "<div class='DefaultRegularOutput'><a href='$strViewServiceNotesLink'>View Notes</a></div>\n";
				
			$strViewServiceLink = Href()->ViewService($dboService->Id->Value);
			$strFNN = "<div class='DefaultRegularOutput'><a href='$strViewServiceLink'>".$dboService->FNN->Value."</a></div>";	
				
			Table()->ServiceTable->AddRow($strFNN, $dboService->ServiceType->AsCallBack('GetConstantDescription', Array('ServiceType')), 
															$strRatePlanName,
															$strStatus,
															$strOutputLink);									
					
		}
		Table()->ServiceTable->Render();
		
		echo "</div>\n";
		echo "</div>\n";
	
		echo "<div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		echo "</div>\n";

		echo "</div>\n";
	}

	//------------------------------------------------------------------------//
	// _RenderBareDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderBareDetail()
	 *
	 * Render this HTML Template with bare service detail
	 *
	 * Render this HTML Template with bare service detail
	 *
	 * @method
	 */
	private function _RenderBareDetail()
	{
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";	
	}

	//------------------------------------------------------------------------//
	// _RenderMinimumDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderMinimumDetail()
	 *
	 * Render this HTML Template with minimum service detail
	 *
	 * Render this HTML Template with minimum service detail
	 *
	 * @method
	 */
	private function _RenderMinimumDetail()
	{
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);		
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";	
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();	
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		
	
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			DBO()->Service->Indial100->RenderOutput();
			DBO()->Service->ELB->RenderOutput();
		}
		
		DBO()->Service->CreatedOn->RenderOutput();
		DBO()->Service->ClosedOn->RenderOutput();
		DBO()->Service->TotalUnbilledCharges->RenderOutput();
		//Only display the current rate plan if there is one
		if (DBO()->RatePlan->Id->Value !== FALSE)
		{
			DBO()->RatePlan->Name->RenderOutput(1);
		}
		else
		{
			DBO()->RatePlan->Name->RenderArbitrary("No Plan", RENDER_OUTPUT, 1);
		}
		
		DBO()->Service->LineStatus->RenderCallback("GetConstantDescription", Array("LineStatus"), RENDER_OUTPUT);
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);

		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
