<?php
//----------------------------------------------------------------------------//
// HtmlTemplateservicedetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateservicedetails
 *
 * A specific HTML Template object
 *
 * An service details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateservicedetails
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
	 * Render this HTML Template with bare service detail
	 *
	 * Render this HTML Template with bare service detail
	 *
	 * @method
	 */
	private function _RenderTabularDetail()
	{
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";

		Table()->ServiceTable->SetHeader("Service Type", "Plan Name", "Status", "Actions");
		Table()->ServiceTable->SetWidth("15%", "20%","20%","20%");
		Table()->ServiceTable->SetAlignment("Left", "Left", "Left", "Left");
		
		foreach (DBL()->Service as $dboService)
		{
			switch ($dboService->Status->Value)
			{
				case SERVICE_ACTIVE:
					$strStatus = "<div class='DefaultRegularOutput'>Opened On: ".$dboService->CreatedOn->Value."</div>";
					break;
				case SERVICE_DISCONNECTED:
					$strStatus = "<div class='DefaultRegularOutput'>Closes On: ".$dboService->ClosedOn->Value."</div>";
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
					
			if (DBO()->RatePlan->Name->Value == NULL)
			{
				$strRatePlanName = "<div class='DefaultRegularOutput'>No Plan Selected</div>";
			}
			else
			{
				$strRatePlanName = "<div class='DefaultRegularOutput'>".DBO()->RatePlan->Name->Value."</div>";
			}			
					
			$strViewServiceNotesLink = Href()->ViewServiceNotes($dboService->Id->Value);
			$strOutputLink = "<div class='DefaultRegularOutput'><a href='$strViewServiceNotesLink'>View Notes</a></div>\n";
				
			Table()->ServiceTable->AddRow($dboService->ServiceType->AsCallBack('GetConstantDescription', Array('ServiceType')), 
															$strRatePlanName,
															$strStatus,
															$strOutputLink);									
					
		}
		Table()->ServiceTable->Render();
		
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";	
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
		
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);

		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
