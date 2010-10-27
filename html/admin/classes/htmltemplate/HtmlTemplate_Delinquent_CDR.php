<?php

class HtmlTemplate_Delinquent_CDR extends FlexHtmlTemplate
{

	protected $sStartDate;
	protected $sEndDate;

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);

// AJAX and pagination
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('sort');

		// Helper classes
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('reflex_sorter');
		$this->LoadJavascript('reflex_anchor');
		$this->LoadJavascript('component_date_picker');
		$this->LoadJavascript('actions_and_notes');

		// Control fields & other components
		$this->LoadJavascript('section');
		$this->LoadJavascript('section_expandable');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_date_picker');
		$this->LoadJavascript('control_field_combo_date');

		// Pseudo ORM
		$this->LoadJavascript('employee');
		$this->LoadJavascript('followup_category');
		$this->LoadJavascript('followup_status');
		$this->LoadJavascript('followup_closure');
		$this->LoadJavascript('followup_modify_reason');
		$this->LoadJavascript('followup_reassign_reason');

		// Classes that renders the page
		$this->LoadJavascript('component_followup_list_all');
		$this->LoadJavascript('page_followup_list');
		$this->LoadJavascript('popup_followup_close');
		$this->LoadJavascript('popup_followup_reassign');
		$this->LoadJavascript('popup_followup_due_date');
		$this->LoadJavascript('popup_followup_view');

		$this->LoadJavascript('page_delinquent_cdr_list');
		$this->LoadJavascript('component_delinquent_cdr_list');
		$this->setDates();

	}

	private function setDates()
	{

		// Work out the start of the most recently started billing period
		$arrCustomerGroups = Customer_Group::listAll();

		$strNow = GetCurrentISODateTime();
		$intNow = strtotime($strNow);

		$intMostRecentlyStartedBillingPeriod = 0;
		foreach ($arrCustomerGroups as $customerGroup)
		{
			try
			{
				$intStartOfCurrentBillingPeriod = strtotime(Invoice_Run::getLastInvoiceDateByCustomerGroup($customerGroup->id, $strNow));
				if ($intStartOfCurrentBillingPeriod > $intMostRecentlyStartedBillingPeriod)
				{
					$intMostRecentlyStartedBillingPeriod = $intStartOfCurrentBillingPeriod;
				}
			}
			catch (Exception $e)
			{
				// Suppress errors at this stage
			}
		}

		if ($intMostRecentlyStartedBillingPeriod == 0)
		{
			// Invoice_Run::getLastInvoiceDateByCustomerGroup() must have failed for each customer group
			// Use today's date
			$intMostRecentlyStartedBillingPeriod = $intNow;
		}

		$intStartingDate	= strtotime("-189 days", $intMostRecentlyStartedBillingPeriod);

		$strStartingDate	= date("d/m/Y", $intStartingDate);
		$strEndingDate		= date("d/m/Y", $intNow);

		$this->sStartDate = $strStartingDate;
		$this->sEndDate = $strEndingDate;


		$strYearLowerLimit	= substr($strStartingDate, 6);
		$strYearUpperLimit	= substr($strEndingDate, 6);

		$intMaxYear			= intval(date("Y", $intNow));
		$intMinYear			= intval(date("Y", $intStartingDate));

		$intDefaultYear		= $intMaxYear;
		$intDefaultMonth	= intval(date("m", $intNow));
		$intDefaultDay		= intval(date("d", $intNow));


	}


	public function Render()
	{

$x=3;

		echo "
		<div id='DelinquentCDRContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window,
				'load',
				function()
				{
					CDRList = new Page_Delinquent_CDR_List(\$ID('DelinquentCDRContainer'),237, true,'". $this->sStartDate."','".$this->sEndDate."');

				}
			)
		</script>\n";
	}
}

?>