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
 * HTML Template for the details of an Account.  Primarily those stored in the Account table
 *
 * HTML Template for the details of an Account.  Primarily those stored in the Account table
 *
 * @file		details.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * A specific HTML Template object
 *
 * An Account Details HTML Template object
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountDetails extends HtmlTemplate
{
	//const SEVERITY_WARNING_DISPLAY_LOG_SESSION_KEY = 'severity_warning_display_log';

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
		
		$this->LoadJavascript("account_details");
		$this->LoadJavascript("constants");

		$this->LoadJavascript("reflex_popup");
		$this->LoadJavascript("credit_card_type");
		$this->LoadJavascript("credit_card_payment");
		
		$this->LoadJavascript("section");
		$this->LoadJavascript("reflex_validation");
		$this->LoadJavascript("reflex_validation_credit_card");
		$this->LoadJavascript("control_field");
		$this->LoadJavascript("control_field_text");
		$this->LoadJavascript("control_field_select");
		$this->LoadJavascript("control_field_checkbox");
		$this->LoadJavascript("control_field_radiobutton");
		$this->LoadJavascript("control_field_combo_date");
		$this->LoadJavascript("control_field_number");
		$this->LoadJavascript("control_field_date_picker");
		$this->LoadJavascript("component_date_picker");
		$this->LoadJavascript("popup_credit_card_payment");
		$this->LoadJavascript("popup_account_tio_complaint");
		$this->LoadJavascript("popup_account_tio_complaint_view");
		$this->LoadJavascript("popup_account_tio_complaint_close");
		$this->LoadJavascript("popup_account_promise_new");
		$this->LoadJavascript("component_collections_suspension");
		$this->LoadJavascript("popup_account_suspend_from_collections");
		$this->LoadJavascript("popup_account_collection_scenario");
		$this->LoadJavascript("popup_account_severity_warning");
		$this->LoadJavascript("popup_account_promise_edit");
		$this->LoadJavascript("popup_account_promise_edit_schedule");
		$this->LoadJavascript("popup_account_promise_cancel");
		$this->LoadJavascript("account");
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
			case HTML_CONTEXT_VIEW:
				$this->_RenderForViewing();
				$this->_RenderSeverityWarnings();
				break;
			case HTML_CONTEXT_EDIT:
				$this->_RenderForEditing();
				break;
		}
		
		// Is this Account on Debt Collection?
		/*if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_DEBT_COLLECTION)
		{
			// Only allow Super Admins and Accounts/Credit Control to access this page
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT))
			{
				$strJS	= "JsAutoLoader.loadScript(\"javascript/account.js\", function(){Flex.Account.displayReferAccountsPopup(".DBO()->Account->Id->Value.", \"".htmlspecialchars(DBO()->Account->BusinessName->Value, ENT_QUOTES)."\", \"".GetConstantDescription(DBO()->Account->Archived->Value, 'account_status')."\");});";
				echo "\n<!-- User doesn't have permission to view these Accounts -->\n";
				echo "<script type='text/javascript'>{$strJS}</script>\n\n";
			}
		}*/
	}

	//------------------------------------------------------------------------//
	// _RenderForViewing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForViewing()
	 *
	 * Renders the Account Details in "View" mode
	 *
	 * Renders the Account Details in "View" mode
	 *
	 * @method
	 */
	private function _RenderForViewing()
	{
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		$oLogicAccount = Logic_Account::getInstance(DBO()->Account->Id->Value);

		// Check if there are any sales associated with the account, that were made in the last 2 months
		$arrSales = FlexSale::listForAccountId(DBO()->Account->Id->Value, "verified_on DESC, id DESC");
		if (count($arrSales))
		{
			$objSale = current($arrSales);
			$strAccountSalesLink = Href()->ViewSalesForAccount(DBO()->Account->Id->Value);
			if ($objSale->verifiedOn > date("Y-m-d H:i:s", strtotime("- 2 months")))
			{
				echo "	<div class='MsgNotice'>
							This account has had recent sales associated with it.  To view them click <a onclick=\"$strAccountSalesLink\">here</a>.
							<br />Sales associated with the account can always be accessed through the menu, Account &gt; Sales &gt; View Sales.
						</div>
						";
			}
		}

		// Build the Full Postal Address Field
		$strBusinessName	= trim(DBO()->Account->BusinessName->Value);
		$strTradingName		= trim(DBO()->Account->TradingName->Value);
		$strAddress1		= trim(DBO()->Account->Address1->Value);
		$strAddress2		= trim(DBO()->Account->Address2->Value);
		$strSuburb			= strtoupper(trim(DBO()->Account->Suburb->Value));
		$strPostcode		= trim(DBO()->Account->Postcode->Value);
		$strState			= strtoupper(trim(DBO()->Account->State->Value));

		$strPostalAddress = "";
		if ($strBusinessName != "")
		{
			$strPostalAddress .= htmlspecialchars($strBusinessName) . "<br />";
		}
		elseif ($strTradingName != "")
		{
			$strPostalAddress .= htmlspecialchars($strTradingName) . "<br />";
		}
		else
		{
			$strPostalAddress .= "<em style='color:#f00'>[ NO BUSINESS NAME OR TRADING NAME SPECIFIED.  PLEASE FIX ME ]</em><br />";
		}
		if ($strAddress1 != "")
		{
			$strPostalAddress .= htmlspecialchars($strAddress1) . "<br />";
		}
		else
		{
			// There is no address 1
			$strPostalAddress .= "<em style='color:#f00'>[ NO ADDRESS LINE 1 SPECIFIED.  PLEASE FIX ME ]</em><br />";
		}
		if ($strAddress2 != "")
		{
			$strPostalAddress .= htmlspecialchars($strAddress2) . "<br />";
		}
		
		$strPostalAddress .= ($strSuburb != "")? $strSuburb : "<em style='color:#f00'>[ NO SUBURB/LOCALITY SPECIFIED.  PLEASE FIX ME ]</em>";
		$strPostalAddress .= " ";
		$strPostalAddress .= ($strState != "")? $strState : "<em style='color:#f00'>[ NO STATE SPECIFIED.  PLEASE FIX ME ]</em>";
		$strPostalAddress .= " ";
		$strPostalAddress .= ($strPostcode != "")? $strPostcode : "<em style='color:#f00'>[ NO POSTCODE SPECIFIED.  PLEASE FIX ME ]</em>";
		DBO()->Account->PostalAddress = $strPostalAddress;
		
		
		// Render the details of the Account
		DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
		DBO()->CustomerGroup->Load();
		$strCustomerGroup = DBO()->CustomerGroup->internal_name->Value;
		DBO()->Account->CustomerGroup->RenderArbitrary($strCustomerGroup, RENDER_OUTPUT);
		
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("account_status"), RENDER_OUTPUT);
		
		DBO()->Account->Id->RenderOutput();
		
		if ((DBO()->Account->BusinessName->Value == "") && (DBO()->Account->TradingName->Value == ""))
		{
			// There is no business name of trading name
			DBO()->Account->BusinessName->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		
		if (DBO()->Account->BusinessName->Value != "")
		{
			// Display the Business Name, but only if there is one
			DBO()->Account->BusinessName->RenderOutput();
		}
		
		if (DBO()->Account->TradingName->Value != "")
		{
			// Display the Trading name, but only if there is one
			DBO()->Account->TradingName->RenderOutput();
		}

		if ((DBO()->Account->ABN->Value == "") && (DBO()->Account->ACN->Value == ""))
		{
			// There is no ABN or ACN
			DBO()->Account->ABN->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		
		if (DBO()->Account->ABN->Value != "")
		{
			// Display the ABN, but only if there is one
			DBO()->Account->ABN->RenderOutput();
		}
		
		if (DBO()->Account->ACN->Value != "")
		{
			// If there is no ABN, display the ACN, if there is one
			DBO()->Account->ACN->RenderOutput();
		}
		
		// Don't include address and BillingType/BillingMethod details if this
		// HtmlTemplate is being rendered on the InvoicesAndPayments page
		if (!DBO()->Account->InvoicesAndPaymentsPage->Value)
		{
			DBO()->Account->PostalAddress->RenderOutput();
						
			if (DBO()->Account->Country->Value != "")
			{
				DBO()->Account->Country->RenderOutput();
			}
			
			$sPaymentMethodName	= GetConstantDescription(DBO()->Account->BillingType->Value, 'billing_type');
			if (DBO()->Account->BillingType->Value === BILLING_TYPE_REBILL)
			{
				$oRebill	= Account::getForId(DBO()->Account->Id->Value)->getRebill();
				$sPaymentMethodName	.= ': '.GetConstantDescription($oRebill->rebill_type_id, 'rebill_type');
			}
			
			?>
			<div class="DefaultElement">
				<div id="Account.BillingType.Output" name="Account.BillingType" class="DefaultOutput Default ">
					<?php echo $sPaymentMethodName; ?>
				</div>
				<div id="Account.BillingType.Label" class="DefaultLabel">
					<span> &nbsp;</span>
					<span id="Account.BillingType.Label.Text">Payment Method : </span>
				</div>
			</div>
			<?php
			
			// DEPRECATED, Rebill billing type insufficiently described using this method
			//DBO()->Account->BillingType->RenderCallback("GetConstantDescription", Array("billing_type"), RENDER_OUTPUT);
			
			// DEPRECATED, Delivery Method used to name it now
			//DBO()->Account->BillingMethod->RenderCallback("GetConstantDescription", Array("delivery_method"), RENDER_OUTPUT);
			
			DBO()->Account->Delivery_Method	= DBO()->Account->BillingMethod->Value;
			DBO()->Account->Delivery_Method->RenderCallback("GetConstantDescription", Array("delivery_method"), RENDER_OUTPUT);
		}
			
		// Get the accounts active promise to pay
		$oActivePromise	= $oLogicAccount->getActivePromise();
			
		?>
		<div class="DefaultElement">
			<div id="Account.Balance.Output" name="Account.Balance" class="DefaultOutput Currency">
			<?php
				DBO()->Account->Balance->Render('Currency2DecWithNegAsCR');
				
				if (Credit_Card_Payment::availableForCustomerGroup(DBO()->Account->CustomerGroup->Value) && AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
				{
					// Rebill Customers cannot Pay by Credit Card, unless the Account Balance is over $0 and the user is a Credit Management employee
					if (DBO()->Account->BillingType->Value !== BILLING_TYPE_REBILL || (AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT) && DBO()->Account->Balance->Value > 0.0))
					{
						echo " ".Credit_Card_Payment::getPopupActionButton(DBO()->Account->Id->Value);
					}
				}
				
				// Button for creating a promise if there isn' already one
				if ($oActivePromise === null)
				{
					echo "	<button class='icon-button' onclick='javascript: new Popup_Account_Promise_Edit(".DBO()->Account->Id->Value.");'>
								<img src='../admin/img/template/payment.png'/>
								<span>Create Promise to Pay</span>
							</button>";
				}
			?></div>
			<div id="Account.Balance.Label" class="DefaultLabel">
				<span> &nbsp;</span>
				<span id="Account.Balance.Label.Text">Balance : </span>
			</div>
		</div>
		<?php

		DBO()->Account->Overdue->RenderOutput();
		
		// Total unbilled adjustments
		$fTotalUnbilledAdjustments 		= DBO()->Account->TotalUnbilledAdjustments->Value;
		$sAbsTotalUnbilledAdjustments	= number_format(abs(Rate::roundToRatingStandard($fTotalUnbilledAdjustments, 2)), 2);
		if ($fTotalUnbilledAdjustments < 0)
		{
			// Credit amount
			$sTotalUnbilledAdjustments = $sAbsTotalUnbilledAdjustments." CR";
		}
		else
		{
			// Debit amount
			$sTotalUnbilledAdjustments = $sAbsTotalUnbilledAdjustments;
		}
		
		echo "	<div class='DefaultElement'>
					<div id='Account.TotalUnbilledAdjustments.Output' name='Account.TotalUnbilledAdjustments' class='DefaultOutput'>\${$sTotalUnbilledAdjustments}</div>
					<div id='Account.TotalUnbilledAdjustments.Label' class='DefaultLabel'>
						<span> &nbsp;</span>
						<span id='Account.TotalUnbilledAdjustments.Label.Text'>Total Unbilled Adjustments : </span>
					</div>
				</div>";
		
		// Promised amount
		$fTotalPromised	= 0;
		if ($oActivePromise !== null)
		{
			$fTotalPromised	= $oActivePromise->getAmount();
		}
		$fTotalPromised = Rate::roundToRatingStandard($fTotalPromised, 2);
		$sTotalPromised	= number_format($fTotalPromised, 2);
		
		echo "	<div class='DefaultElement'>
					<div id='Account.promised_amount.Output' name='Account.promised_amount' class='DefaultOutput'>\${$sTotalPromised}</div>
					<div id='Account.promised_amount.Label' class='DefaultLabel'>
						<span> &nbsp;</span>
						<span id='Account.promised_amount.Label.Text'>Promised Amount : </span>
					</div>
				</div>";
		
		// Most Recent Collection Event
		$oEventInstance	= Logic_Collection_Event_Instance::getMostRecentForAccount($oLogicAccount, ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED);
		$sLastEvent		= 'None';
		if ($oEventInstance !== null)
		{
			if (!$oEventInstance->isExitEvent())
			{
				$sEventName	= $oEventInstance->getEventName();
				$sLastEvent	= "{$sEventName} on ".date('l, M j, Y g:i:s A', strtotime($oEventInstance->completed_datetime));
			}
		}
		
		$sSuspendFromCollectionsButton = '';
		if (!$oLogicAccount->isInSuspension())
		{
			$sSuspendFromCollectionsButton = "	<button class='icon-button' onclick='javascript: new Popup_Account_Suspend_From_Collections(".DBO()->Account->Id->Value.");'>
													<img src='../admin/img/template/collection_suspension.png'/>
													<span>Suspend From Collections</span>
												</button>";
		}
		echo "	<div class='DefaultElement'>
					<div id='Account.most_recent_collection_event.Output' name='Account.most_recent_collection_event' class='DefaultOutput'>{$sLastEvent}{$sSuspendFromCollectionsButton}</div>
					<div id='Account.most_recent_collection_event.Label' class='DefaultLabel'>
						<span> &nbsp;</span>
						<span id='Account.most_recent_collection_event.Label.Text'>Last Collections Event: </span>
					</div>
				</div>";
		
		if (DBO()->Account->Sample->Value === NULL)
		{
			// If Account->Sample is NULL, then set it to 0
			DBO()->Account->Sample = 0;
		}
		DBO()->Account->Sample->RenderOutput();
		
		// This property is DEPRECATED
		//DBO()->Account->DisableLateNotices->RenderOutput();
		
		// NOTE: CR137 - Removed, deprecated collections concept (late notices)
		/*if (DBO()->Account->LatePaymentAmnesty->Value == substr(END_OF_TIME, 0, 10))
		{
			DBO()->Account->LatePaymentAmnesty = "Never send late notices";
		}
		elseif (DBO()->Account->LatePaymentAmnesty->Value < date("Y-m-d"))
		{
			DBO()->Account->LatePaymentAmnesty = "Send late notices";
		}
		else
		{
			DBO()->Account->LatePaymentAmnesty = "Exempt until after ". date("jS F, Y", strtotime(DBO()->Account->LatePaymentAmnesty->Value));
		}
		DBO()->Account->LatePaymentAmnesty->RenderOutput();*/

		// NOTE: CR137 - Removed, deprecated collections concept (late payment fee & credit control status)
		/*DBO()->credit_control_status->Id = DBO()->Account->credit_control_status->Value;
		DBO()->credit_control_status->Load();
		DBO()->Account->credit_control_status = DBO()->credit_control_status->name->Value;
		DBO()->Account->credit_control_status->RenderOutput();

		if (DBO()->Account->DisableLatePayment->Value === NULL)
		{
			// If DisableLatePayment is NULL then set it to 0
			DBO()->Account->DisableLatePayment = 0;
		}
		if (DBO()->Account->DisableLatePayment->Value < -1)
		{
			DBO()->Account->DisableLatePayment->Value = abs(DBO()->Account->DisableLatePayment->Value);
		}
		DBO()->Account->DisableLatePayment->RenderOutput();*/
		
		// To avoid a double negative display ChargeAdminFee instead of DisableDDR
		DBO()->Account->ChargeAdminFee = !(DBO()->Account->DisableDDR->Value);
		DBO()->Account->ChargeAdminFee->RenderOutput();
		
		// NOTE: CR137 - Removed, deprecated collections concept (vip status)
		/*
		?>
		<div class="DefaultElement">
			<div id="Account.vip.Output" name="Account.vip" class="DefaultOutput"><?php echo (DBO()->Account->vip->Value ? 'VIP' : 'Non-VIP (Normal)'); ?></div>
			<div id="Account.vip.Label" class="DefaultLabel">
				<span> &nbsp;</span>
				<span id="Account.Balance.Label.Text">VIP Status : </span>
			</div>
		</div>
		<?php
		*/
		
		// NOTE: CR137 - Removed, deprecated collections concept (last automatic invoice action)
		/*DBO()->automatic_invoice_action->Id = DBO()->Account->last_automatic_invoice_action->Value;
		DBO()->automatic_invoice_action->Load();
		if (DBO()->Account->last_automatic_invoice_action->Value != AUTOMATIC_INVOICE_ACTION_NONE)
		{
			DBO()->Account->last_automatic_invoice_action =
				DBO()->automatic_invoice_action->name->Value . ' on ' .
				OutputMask()->LongDateAndTime(DBO()->Account->last_automatic_invoice_action_datetime->Value);
		}
		else
		{
			DBO()->Account->last_automatic_invoice_action = DBO()->automatic_invoice_action->name->Value;
		}
		DBO()->Account->last_automatic_invoice_action->RenderOutput();*/
		
		// NOTE: CR137 - Removed, deprecated concept automatic barring status (replaced by Current Barring Level below)
		// ... automatic account barring
		/*DBO()->automatic_barring_status->Id = DBO()->Account->automatic_barring_status->Value;
		DBO()->automatic_barring_status->Load();
		if (DBO()->Account->automatic_barring_status->Value != AUTOMATIC_BARRING_STATUS_NONE)
		{
			DBO()->Account->automatic_barring_status =
				DBO()->automatic_barring_status->name->Value . ' on ' .
				OutputMask()->LongDateAndTime(DBO()->Account->automatic_barring_datetime->Value);
		}
		else
		{
			DBO()->Account->automatic_barring_status = DBO()->automatic_barring_status->name->Value;
		}
		DBO()->Account->automatic_barring_status->RenderOutput();*/
		
		// Current Barring Level
		$aBarringDetails = Logic_Account::getInstance(DBO()->Account->Id->Value)->getLatestActionedBarringLevel();
		if ($aBarringDetails !== null)
		{
			// Latest Barring Level change
			$sBarringLevelName 			= Constant_Group::getConstantGroup('barring_level')->getConstantName($aBarringDetails['barring_level_id']);
			$sActionedOn				= date('l, M d, Y g:i:s A', strtotime($aBarringDetails['actioned_datetime']));
			$sBarringLevelDescription 	= "{$sBarringLevelName} on {$sActionedOn}";
			if (isset($aBarringDetails['service_count']))
			{
				$iServiceCount 				= $aBarringDetails['service_count'];
				$sBarringLevelDescription	.= " ({$iServiceCount} Service".($iServiceCount == 1 ? '' : 's').")";
			}
		}
		else
		{
			// Unrestricted
			$sBarringLevelName 			= Constant_Group::getConstantGroup('barring_level')->getConstantName(BARRING_LEVEL_UNRESTRICTED);
			$sBarringLevelDescription 	= $sBarringLevelName;
		}
		
		echo "	<div class='DefaultElement'>
						<div class='DefaultOutput'>{$sBarringLevelDescription}</div>
						<div class='DefaultLabel'>
							<span> &nbsp;</span>
							<span>Current Barring Level :</span>
						</div>
					</div>";
		
		if (DBO()->Account->tio_reference_number->Value !== NULL)
		{
			// This account has a TIO reference number.  Display it
			$strTIORefNum = htmlspecialchars(DBO()->Account->tio_reference_number->Value, ENT_QUOTES);
			echo "	<div class='DefaultElement'>
						<div class='DefaultOutput'>
							$strTIORefNum 
							<button class='icon-button' onclick='javascript: new Popup_Account_TIO_Complaint_View(".DBO()->Account->Id->Value.", Vixen.AccountDetails.CancelEdit.bind(Vixen.AccountDetails));'>
								<img src='../admin/img/template/magnifier.png'/>
								<span>View Complaint Details</span>
							</button>
						</div>
						<div class='DefaultLabel'>
							<span> &nbsp;</span>
							<span>T.I.O. Reference Number :</span>
						</div>
					</div>";
		}
		else
		{
			// This Account has no tio_reference_number, allow creation of a complaint
			echo "	<div class='DefaultElement'>
						<div class='DefaultOutput'>
							None
							<button class='icon-button' onclick='javascript: new Popup_Account_TIO_Complaint(".DBO()->Account->Id->Value.", Vixen.AccountDetails.CancelEdit.bind(Vixen.AccountDetails));'>
								<img src='../admin/img/template/collection_suspension.png'/>
								<span>Create a TIO Complaint</span>
							</button>
						</div>
						<div class='DefaultLabel'>
							<span> &nbsp;</span>
							<span>T.I.O. Reference Number :</span>
						</div>
					</div>";
		}
		
		// Account class
		$oAccountClass = Account_Class::getForId(DBO()->Account->account_class_id->Value);
		echo "	<div class='DefaultElement'>
					<div class='DefaultOutput'>{$oAccountClass->name}</div>
					<div class='DefaultLabel'>
						<span> &nbsp;</span>
						<span>Account Class :</span>
					</div>
				</div>";
		
		// Collection Scenario
		$oScenario 		= Logic_Account::getInstance(DBO()->Account->Id->Value)->getCurrentScenarioInstance()->getScenario();
		$sEditButton	= "	<button class='icon-button' onclick='javascript: new Popup_Account_Collection_Scenario(".DBO()->Account->Id->Value.", {$oScenario->id}, Vixen.AccountDetails.CancelEdit.bind(Vixen.AccountDetails));'>
								<img src='../admin/img/template/pencil.png'/>
								<span>Change</span>
							</button>";
		echo "	<div class='DefaultElement'>
					<div class='DefaultOutput'>{$oScenario->name} {$sEditButton}</div>
					<div class='DefaultLabel'>
						<span> &nbsp;</span>
						<span>Collection Scenario :</span>
					</div>
				</div>";
		
		echo "</div>\n"; // GroupedContent
		
		// Render the buttons but only if the user has operator privileges
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Edit Details", "Vixen.AccountDetails.RenderAccountDetailsForEditing();");
			echo "</div></div>\n";
		}
		else
		{
			echo "<div class='SmallSeperator'></div>\n";
		}
		
		// Initialise the AccountDetails object and register the OnAccountDetailsUpdate Listener
		$strInvoicesAndPaymentsPage	= (DBO()->Account->InvoicesAndPaymentsPage->Value) ? "true" : "false";
		$intAccountId				= DBO()->Account->Id->Value;
		$strJavascript = "Vixen.AccountDetails.InitialiseView($intAccountId, '{$this->_strContainerDivId}', $strInvoicesAndPaymentsPage);";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderForEditing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForEditing()
	 *
	 * Renders the Account Details in "Edit" mode
	 *
	 * Renders the Account Details in "Edit" mode
	 *
	 * @method
	 */
	private function _RenderForEditing()
	{
		$bolIsAdminUser			= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$bolIsSuperAdminUser	= AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
		$bolIsProperAdminUser	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
	
		$this->FormStart("EditAccount", "Account", "SaveDetails");
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Render hidden values
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		DBO()->Account->InvoicesAndPaymentsPage->RenderHidden();
		
		// Render the details of the Account
		DBO()->Account->Id->RenderOutput();

		if ($bolIsSuperAdminUser)
		{
			// Render the CustomerGroup combobox
			DBL()->CustomerGroup->OrderBy("internal_name");
			DBL()->CustomerGroup->Load();
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Customer Group :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.CustomerGroup' name='Account.CustomerGroup' style='width:330px'>\n";
			foreach (DBL()->CustomerGroup as $dboCustomerGroup)
			{
				$intCustomerGroupId		= $dboCustomerGroup->Id->Value;
				$strCustomerGroupName	= $dboCustomerGroup->internal_name->Value;
				$strSelected = (DBO()->Account->CustomerGroup->Value == $intCustomerGroupId) ? "selected='selected'" : "";
				echo "		<option value='$intCustomerGroupId' $strSelected>$strCustomerGroupName</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}
		else
		{
			// The user does not have permission to edit this property
			DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
			DBO()->CustomerGroup->Load();
			$strCustomerGroupName = DBO()->CustomerGroup->internal_name->Value;
			DBO()->Account->CustomerGroup->RenderArbitrary($strCustomerGroupName, RENDER_OUTPUT);
		}
		
		// Work out which Account Status' can be chosen
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			// The account is pending activation
			$arrAccountStatuses = array(ACCOUNT_STATUS_PENDING_ACTIVATION, ACCOUNT_STATUS_ACTIVE);
		}
		else
		{
			// The account has already been activated
			$arrAccountStatuses = array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED, ACCOUNT_STATUS_DEBT_COLLECTION, ACCOUNT_STATUS_ARCHIVED);
		}
		
		$strStatusOptions = "";
		foreach ($arrAccountStatuses as $intStatus)
		{
			$strSelected		= (DBO()->Account->Archived->Value == $intStatus)? "selected='selected'" : "";
			$strStatus			= GetConstantDescription($intStatus, "account_status");
			
			if ($bolIsProperAdminUser || $strSelected)
			{
				$strStatusOptions	.= "<option value='$intStatus' $strSelected>$strStatus</option>";
			}
		}
		
		// Render the Account Status Combobox
		echo "
<div class='DefaultElement'>
	<div class='DefaultLabel'>&nbsp;&nbsp;Status :</div>
	<div class='DefaultOutput'>
		<select id='AccountStatusCombo' name='Account.Archived' style='width:330px'>$strStatusOptions</select>
	</div>
</div>";
		
		
		DBO()->Account->BusinessName->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		DBO()->Account->TradingName->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		
		if ($bolIsProperAdminUser)
		{
			DBO()->Account->ABN->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->ACN->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		}
		else
		{
			// The user does not have permission to edit this property
			if ((DBO()->Account->ABN->Value == "") && (DBO()->Account->ACN->Value == ""))
			{
				// There is no ABN or ACN
				DBO()->Account->ABN->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
			}
			
			if (DBO()->Account->ABN->Value != "")
			{
				// Display the ABN, but only if there is one
				DBO()->Account->ABN->RenderOutput();
			}
			
			if (DBO()->Account->ACN->Value != "")
			{
				// If there is no ABN, display the ACN, if there is one
				DBO()->Account->ACN->RenderOutput();
			}
		}
		
		// Don't include address and BillingMethod details if this
		// HtmlTemplate is being rendered on the InvoicesAndPayments page
		if (!DBO()->Account->InvoicesAndPaymentsPage->Value)
		{
			DBO()->Account->Address1->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->Address2->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->Suburb->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->Postcode->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			
			// Render the State combobox
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.State' name='Account.State' style='width:330px'>\n";
			foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $strKey=>$arrState)
			{
				$strSelected = (DBO()->Account->State->Value == $strKey) ? "selected='selected'" : "";
				echo "		<option value='$strKey' $strSelected>{$arrState['Description']}</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
			
			// Render the BillingMethod combobox
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Billing Method :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.BillingMethod' name='Account.BillingMethod' style='width:330px'>\n";
			
			$arrDeliveryMethods	= Delivery_Method::getAll();
			foreach ($arrDeliveryMethods as $intDeliveryMetyhodId=>$objDeliveryMethod)
			{
				if (!$objDeliveryMethod->account_setting || ($objDeliveryMethod->const_name == 'DELIVERY_METHOD_DO_NOT_SEND' && DBO()->Account->BillingMethod->Value != DELIVERY_METHOD_DO_NOT_SEND))
				{
					// Don't include this option
					continue;
				}
				
				$strSelected = (DBO()->Account->BillingMethod->Value == $objDeliveryMethod->id) ? "selected='selected'" : "";
				echo "		<option value='{$objDeliveryMethod->id}' $strSelected>{$objDeliveryMethod->description}</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}
		
		DBO()->Account->Sample->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		
		// This property is DEPRICATED
		//DBO()->Account->DisableLateNotices->RenderInput();
		
		// NOTE: CR137 - Removed, deprecated collections concept (late notices)
		/*$strEndOfTime				= substr(END_OF_TIME, 0, 10);
		$arrOptions					= Array();
		$arrOptions[NULL]			= "Send late notices";
		
		// Build the Array of options for the Late Notices combobox
		// The key to this array will be the amnesty date as a string, so that it can be sorted, and any previous
		// value for LatePaymentAmnesty can safely override any new ones set up
		
		$sLastInvoiceRunDate	= Invoice_Run::getLastInvoiceDateByCustomerGroup(DBO()->Account->CustomerGroup->Value);
		
		$intPaymentTerms			= DBO()->Account->PaymentTerms->Value;
		$intLastMonthsBillAmnesty	= strtotime("+ $intPaymentTerms days", strtotime($sLastInvoiceRunDate));
		if ($intLastMonthsBillAmnesty > time())
		{
			$strLastMonthsBillAmnesty = date("Y-m-d", $intLastMonthsBillAmnesty);
			
			// The user can still flag the account to not receive late notices regarding last months bill
			$arrOptions[$strLastMonthsBillAmnesty]	= "Exempt until ". date("jS F", $intLastMonthsBillAmnesty);
		}
		
		$intThisMonthsBillAmnesty					= strtotime("+ $intPaymentTerms days", strtotime(Invoice_Run::predictNextInvoiceDate(DBO()->Account->CustomerGroup->Value)));
		$strThisMonthsBillAmnesty					= date("Y-m-d", $intThisMonthsBillAmnesty);
		$arrOptions[$strThisMonthsBillAmnesty]		= "Exempt until ". date("jS F", $intThisMonthsBillAmnesty);
		$arrOptions[$strEndOfTime]					= "Never send late notices";
		
		// Add the Account's current LatePaymentAmnesty if it is in the future (or today) and is not set to END_OF_TIME
		$strLatePaymentAmnesty = DBO()->Account->LatePaymentAmnesty->Value;
		if (($strLatePaymentAmnesty != $strEndOfTime) && ($strLatePaymentAmnesty >= date("Y-m-d")))
		{
			// If this date is already in the array of options, then it will just override it
			$arrOptions[$strLatePaymentAmnesty] = "Exempt until ". date("jS F", strtotime($strLatePaymentAmnesty));
		}
		
		// Sort the list
		ksort($arrOptions);
		
		// Render the combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Late Notices :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.LatePaymentAmnesty' name='Account.LatePaymentAmnesty' style='width:330px'>\n";
		foreach ($arrOptions as $strDate=>$strLabel)
		{
			$strSelected = (DBO()->Account->LatePaymentAmnesty->Value == $strDate) ? "selected='selected'" : "";
			echo "		<option value='$strDate' $strSelected>$strLabel</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";*/
		
		// NOTE: CR137 - Removed, deprecated collections concept (Credit control status & late payment fee)
		/*DBL()->credit_control_status->Load();
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Credit Control Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.credit_control_status' name='Account.credit_control_status' style='width:330px'>\n";
		while ($cc = DBL()->credit_control_status->current())
		{
			$id = $cc->id->Value;
			$strLabel = $cc->name->Value;
			$strSelected = (DBO()->Account->credit_control_status->Value == $id) ? "selected='selected'" : "";
			echo "		<option value='$id' $strSelected>$strLabel</option>\n";
			DBL()->credit_control_status->next();
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		DBO()->Account->DisableLatePayment->RenderInput(1);*/
		
		// To avoid a double negative, display ChargeAdminFee instead of DisableDDR
		//DBO()->Account->DisableDDR->RenderInput(1);
		// If ChargeAdminFee has not been set, then set it to the opposite of what DisableDDR is set to
		if (!DBO()->Account->ChargeAdminFee->IsSet)
		{
			DBO()->Account->ChargeAdminFee = !(DBO()->Account->DisableDDR->Value);
		}
		DBO()->Account->ChargeAdminFee->RenderInput();


		// NOTE: CR137 - Removed, deprecated collections concept (vip status)
		/*if (AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT))
		{
?>
<div class="DefaultElement">
	<div class="DefaultLabel">&nbsp;&nbsp;VIP Status : </div>
	<div class="DefaultOutput">
		<select id='Account.vip' name='Account.vip' style='width:330px'>
			<option value='0'<?php echo (DBO()->Account->vip->Value ? '' : ' selected="selected"'); ?>>Non-VIP (Normal)</option>
			<option value='1'<?php echo (DBO()->Account->vip->Value ? ' selected="selected"' : ''); ?>>VIP</option>
		</select>
	</div>
</div>
<?php
		}
		else
		{
?>
<div class="DefaultElement">
	<div id="Account.vip.Output" name="Account.vip" class="DefaultOutput"><?php echo (DBO()->Account->vip->Value ? 'VIP' : 'Non-VIP (Normal)'); ?></div>
	<div id="Account.vip.Label" class="DefaultLabel">
		<span> &nbsp;</span>
		<span id="Account.Balance.Label.Text">VIP Status : </span>
	</div>
</div>
<?php
		}*/
		
		// Account Class
		echo "	<div class='DefaultElement'>
					<div class='DefaultLabel'>&nbsp;&nbsp;Account Class : </div>
					<div class='DefaultOutput'>
						<select id='Account.account_class_id' name='Account.account_class_id' style='width:330px'>";
		
		// Get all available account classes
		$aAccountClasses 	= Account_Class::getForStatus(STATUS_ACTIVE);
		$iAccountClassId	= DBO()->Account->account_class_id->Value;
		
		// Add the current account class even if it is inactive
		if (!isset($aAccountClasses[$iAccountClassId]))
		{
			$aAccountClasses[$iAccountClassId] = Account_Class::getForId($iAccountClassId);
		}
		
		// Add options
		foreach ($aAccountClasses as $oAccountClass)
		{
			$sSelected = ($iAccountClassId == $oAccountClass->id ? " selected='selected'" : '');
			echo "			<option value='{$oAccountClass->id}'{$sSelected}>{$oAccountClass->name}</option>";
		}
		
		echo "			</select>
					</div>
				</div>";
		// END: Account Class
		
		echo "</div>\n"; // GroupedContent

		// Render buttons
		echo "
<div class='ButtonContainer'>
	<div style='float:right'>
		<input type='button' style='display:none;' id='AccountEditSubmitButton' value='Commit Changes' onclick=\"Vixen.Ajax.SendForm('VixenForm_EditAccount', 'Commit Changes', 'Account', 'SaveDetails', '', '', '', '{$this->_strContainerDivId}')\"></input>
		<input type='button' value='Cancel' onclick='Vixen.AccountDetails.CancelEdit();'></input>
		<input type='button' value='Commit Changes' onclick='Vixen.AccountDetails.CommitChanges()'></input>
	</div>
</div>
";
		
		// Load the Constants required for the javascript code
		$jsonAccountStatuses = Json()->encode($GLOBALS['*arrConstant']['account_status']);
		echo "<script type='text/javascript'>\$Const.SetConstantGroup('account_status', $jsonAccountStatuses);</script>";
		
		// Initialise the AccountDetails object
		$strInvoicesAndPaymentsPage = (DBO()->Account->InvoicesAndPaymentsPage->Value) ? "true" : "false";
		$jsonObjAccount				= Json()->encode(DBO()->Account->_arrProperties);
		$strJavascript = "Vixen.AccountDetails.InitialiseEdit($jsonObjAccount, '{$this->_strContainerDivId}', $strInvoicesAndPaymentsPage);";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
		
		$this->FormEnd();
		
		// NOTE: CR137 - Removed, deprecated collections concept (late payment fee)
		/*if (!$bolIsAdminUser)
		{
			// If the user doesn't have Admin privileges they cannot select the "Never charge a late payment fee" option
			// The user doesn't have admin privileges
			$strJsCode .=	"document.getElementById('Account.DisableLatePayment_1').disabled = true;\n".
							"document.getElementById('Account.DisableLatePayment_1.Label').style.color = '#4C4C4C';\n";
		}*/
		
		echo "<script type='text/javascript'>$strJsCode</script>";
	}
	
	private function _RenderSeverityWarnings()
	{
		$iAccountId	= DBO()->Account->Id->Value;
		$aWarnings	= DBO()->SeverityWarnings;
		if (($aWarnings !== null) && (count($aWarnings) > 0))
		{
			echo "	<script type='text/javascript'>
						window.onload = 
							function()
							{
								JsAutoLoader.loadScript(
									'popup_account_severity_warning.js', 
									function()
									{
										new Popup_Account_Severity_Warning({$iAccountId});
									},
									true
								);
							};
					</script>";
		}
	}
}

?>
