<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_services.php
//----------------------------------------------------------------------------//
/**
 * account_services
 *
 * HTML Template for the Account Services popup
 *
 * HTML Template for the Account Services popup
 * This file defines the class responsible for defining and rendering the layout
 * of the HTML Template used by the Account Services popup
 *
 * @file		account_services.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross, Joel
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountServices
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountServices
 *
 * HTML Template object defining the presentation of the Account Services popup
 *
 * HTML Template object defining the presentation of the Account Services popup
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountServices
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountServices extends HtmlTemplate
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
		
		$this->LoadJavascript("account_services");
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
		echo "<div class='PopupLarge'>\n";
		
		// Work out if a virtical scroll bar will be required
		$strTableContainerStyle = (DBL()->Service->RecordCount() > 14) ? "style='overflow:auto; height:450px'": "";
		
		// Draw the table container
		echo "<div $strTableContainerStyle>\n";

		Table()->ServiceTable->SetHeader("FNN #", "Service Type", "Plan Name", "Status", "Actions");
		Table()->ServiceTable->SetWidth("13%", "15%", "35%","15%","22%");
		Table()->ServiceTable->SetAlignment("Left", "Left", "Left", "Left", "Center");
		
		foreach (DBL()->Service as $dboService)
		{
			// Record the Status of the service
			$strStatusCell = $dboService->Status->AsCallBack("GetConstantDescription", Array("Service"));
		
			// Find the current plan for the service
			$mixCurrentPlanId = GetCurrentPlan($dboService->Id->Value);
			if ($mixCurrentPlanId !== FALSE)
			{
				// A plan was found
				DBO()->RatePlan->Id = $mixCurrentPlanId;
				DBO()->RatePlan->Load();
				$strPlan = DBO()->RatePlan->Name->AsValue();
				
				// Create a link to the View Plan for Service popup (although this currently isn't a popup)
				$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($dboService->Id->Value);
				
				$strPlanCell = "<a href='$strViewServiceRatePlanLink'>$strPlan</a>";
			}
			else
			{
				// There is no current plan for the service
				$strPlan = "<span class='DefaultOutputSpan' id='RatePlan.Name'>No Plan Selected</span>";
				
				// Create a link to the ChangePlan popup
				$strChangePlanLink = Href()->ChangePlan($dboService->Id->Value);
				
				$strPlanCell = "<a href='$strChangePlanLink'>$strPlan</a>";
			}
			
			$strViewServiceNotesLink	= Href()->ViewServiceNotes($dboService->Id->Value);
			$strEditServiceLink			= Href()->EditService($dboService->Id->Value);
			$strActionsCell				= 	"<span class='DefaultOutputSpan'><a href='$strViewServiceNotesLink'>Notes</a>" .
											"&nbsp;&nbsp;<a href='$strEditServiceLink'>Edit</a></span>";
				
			$strViewServiceLink = Href()->ViewService($dboService->Id->Value);
			
			if ($dboService->FNN->Value == NULL)
			{
				// The service doesn't have an FNN yet
				$strFnnDescription = "<span class='DefaultOutputSpan'>not specified</span>";
			}
			else
			{
				// The service has an FNN
				$strFnnDescription = $dboService->FNN->AsValue();
			}
			
			$strFnnCell = "<div class='DefaultRegularOutput'><a href='$strViewServiceLink'>$strFnnDescription</a></div>";
				
			Table()->ServiceTable->AddRow($strFnnCell, $dboService->ServiceType->AsCallBack('GetConstantDescription', Array('ServiceType')), 
											$strPlanCell, $strStatusCell, $strActionsCell);									
					
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->ServiceTable->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->ServiceTable->AddRow("<span class='DefaultOutputSpan Default'>No services to display</span>");
			Table()->ServiceTable->SetRowAlignment("left");
			Table()->ServiceTable->SetRowColumnSpan(5);
		}
		
		Table()->ServiceTable->Render();
		
		echo "</div>\n";  // Table Container
	
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";

		echo "</div>\n";  //PopupLarge
		
		// Initialise the javascript object that facilitates this popup (Vixen.AccountServices)
		echo "<script type='text/javascript'>Vixen.AccountServices.Initialise('{$this->_objAjax->strId}')</script>";
	}
}

?>
