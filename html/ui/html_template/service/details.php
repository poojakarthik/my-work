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
		
		$this->LoadJavascript("service_update_listener");
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
			default:
				$this->_RenderFullDetail();
				break;
		}
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
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("service_status"), RENDER_OUTPUT);
		echo "</div>\n";
		
		// Register a listener to handle when the service has been updated
		echo "<script type='text/javascript'>Vixen.EventHandler.AddListener('". EVENT_ON_SERVICE_UPDATE ."', Vixen.ServiceUpdateListener.OnUpdate);</script>\n";
		
		echo "<div class='SmallSeperator'></div>\n";
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
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("service_status"), RENDER_OUTPUT);		
		echo "</div>\n";
		echo "<div class='SmallSeperator'></div>\n";	
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
		echo "<div class='GroupedContent'>\n";
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		DBO()->Service->FNN->RenderOutput();	
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);	
		
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			echo "<div class='ContentSeparator'></div>\n";
			DBO()->Service->Indial100->RenderOutput();
			if (DBO()->Service->Indial100->Value)
			{
				// Only show the ELB setting if the Landline is an Indial100
				DBO()->Service->ELB->RenderOutput();
			}
		}

		// If the ServiceType is a mobile display the extra information for the service
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			echo "<div class='ContentSeparator'></div>\n";
			$strWhere = "Service = <Service>";
			DBO()->ServiceMobileDetail->Where->Set($strWhere, Array('Service' => DBO()->Service->Id->Value));
			DBO()->ServiceMobileDetail->Load();
		
			DBO()->ServiceMobileDetail->SimPUK->RenderOutput();
			DBO()->ServiceMobileDetail->SimESN->RenderOutput();
			DBO()->ServiceMobileDetail->SimState->RenderCallback("GetConstantDescription", Array("ServiceStateType"), RENDER_OUTPUT);
			DBO()->ServiceMobileDetail->DOB->RenderOutput();
			if (DBO()->ServiceMobileDetail->Comments->Value)
			{
				DBO()->ServiceMobileDetail->Comments->RenderOutput();
			}
		}

		// If the ServiceType is a inbound display the extra information for the service
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			echo "<div class='ContentSeparator'></div>\n";
			$strWhere = "Service = <Service>";
			DBO()->ServiceInboundDetail->Where->Set($strWhere, Array('Service' => DBO()->Service->Id->Value));
			DBO()->ServiceInboundDetail->Load();
		
			DBO()->ServiceInboundDetail->AnswerPoint->RenderOutput();
			DBO()->ServiceInboundDetail->Configuration->RenderOutput();			
		}

		echo "<div class='ContentSeparator'></div>\n";
		// Display the Cost Center, if there is one
		if (DBO()->Service->CostCentre->Value)
		{
			DBO()->Service->CostCentre->RenderOutput();
		}
		
		DBO()->Service->ForceInvoiceRender->RenderOutput();
		
		DBO()->Service->TotalUnbilledCharges->RenderOutput();
		// Display the current rate plan, if there is one
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			DBO()->CurrentRatePlan->Name->RenderOutput();
		}
		else
		{
			DBO()->CurrentRatePlan->Name->RenderArbitrary("No Plan", RENDER_OUTPUT);
		}
		
		// Display the plan scheduled to start for the next billing period, if there is one
		if (DBO()->FutureRatePlan->Id->Value)
		{
			DBO()->FutureRatePlan->Name->RenderOutput();
		}
		
		// Separate the Service status properties from the rest
		echo "<div class='ContentSeparator'></div>\n";
		$strViewHistoryLink	= Href()->ViewServiceHistory(DBO()->Service->Id->Value);
		$strViewHistory		= "<a href='$strViewHistoryLink'>history</a>";
		$strCreatedOn		= DBO()->Service->CreatedOn->FormattedValue() . " ($strViewHistory)";
		//DBO()->Service->CreatedOn->RenderArbitrary($strCreatedOn, RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		//DBO()->Service->ClosedOn->RenderOutput();

		if (DBO()->Service->LineStatus->Value !== NULL)
		{
			DBO()->Service->LineStatus->RenderCallback("GetConstantDescription", Array("service_line_status"), RENDER_OUTPUT);
		
			if (DBO()->Service->LineStatusDate->Value !== NULL && DBO()->Service->LineStatusDate->Value != "0000-00-00 00:00:00")
			{
				$intLineStatusDate = strtotime(DBO()->Service->LineStatusDate->Value);
				$strLineStatusDate = date("M j, Y g:i:s A", $intLineStatusDate);
				
				DBO()->Service->LineStatusLastUpdated = $strLineStatusDate;
				DBO()->Service->LineStatusLastUpdated->RenderOutput();
			}
		}
		
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("service_status"), RENDER_OUTPUT);
		
		$objService		= ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->RecordType->Value);		
		$arrLastEvent	= HtmlTemplateServiceHistory::GetLastEvent($objService);
		$strLastEvent	= "{$arrLastEvent['Event']}<br />on {$arrLastEvent['TimeStamp']}<br />by {$arrLastEvent['EmployeeName']} ({$strViewHistory})";
		DBO()->Service->MostRecentEvent = $strLastEvent;
		DBO()->Service->MostRecentEvent->RenderOutput();

		// Register a listener to handle when the service has been updated
		echo "<script type='text/javascript'>Vixen.EventHandler.AddListener('". EVENT_ON_SERVICE_UPDATE ."', Vixen.ServiceUpdateListener.OnUpdate);</script>\n";

		echo "</div>\n";  // GroupedContent
		echo "<div class='Seperator'></div>\n";
	}
}

?>
