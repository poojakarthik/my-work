<?php
class Application_Handler_CustomerGroup extends Application_Handler {
	public function RecordTypeVisibility($subPath) {

		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$oCustomerGroup = Customer_Group::getForId(DBO()->CustomerGroup->Id->Value);

		$detailsToRender = array();
		$detailsToRender['iCustomerGroupId'] = $oCustomerGroup->Id;
		$detailsToRender['iCustomerGroupDefaultRecordTypeVisibility'] = $oCustomerGroup->default_record_type_visibility;

		AppTemplateCustomerGroup::BuildContextMenu($oCustomerGroup->Id);

		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup($oCustomerGroup->Id, $oCustomerGroup->name);
		BreadCrumb()->SetCurrentPage("Record Type Visibility");

		$this->LoadPage('customer_group_record_type_visibility', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function CreditCardConfig($subPath) {
		$detailsToRender = array();

		// We need to load the configuration record for the customer group
		$customerGroupId = count($subPath) ? intval(array_shift($subPath)) : null;
		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';

		$detailsToRender['customerGroup'] = $customerGroupId ? Customer_Group::getForId($customerGroupId) : null;
		$detailsToRender['config'] = $detailsToRender['customerGroup'] ? Credit_Card_Payment_Config::getForCustomerGroup($detailsToRender['customerGroup'], true) : null;

		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup($customerGroupId, $detailsToRender['customerGroup'] ? $detailsToRender['customerGroup']->name : null);
		BreadCrumb()->SetCurrentPage("Secure Pay Configuration");

		if (!$detailsToRender['customerGroup'] || !$detailsToRender['config']) {
			$action = 'error';
		}

		switch ($action) {
			case 'delete':
				if (!$detailsToRender['config']->isSaved()) {
					$action = 'no_config';
					break;
				}
				$detailsToRender['config']->delete();
				break;

			case 'save':
				$detailsToRender['error'] = '';
				$detailsToRender['invalid_values'] = array();
				// Need to apply the posted values to the config object and save it
				$detailsToRender['config']->merchantId = array_key_exists('merchantId', $_REQUEST) && trim($_REQUEST['merchantId']) ? trim($_REQUEST['merchantId']) : '';
				$detailsToRender['config']->password = array_key_exists('password', $_REQUEST) && trim($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
				$detailsToRender['config']->confirmationText = array_key_exists('confirmationText', $_REQUEST) && trim($_REQUEST['confirmationText']) ? trim($_REQUEST['confirmationText']) : '';
				$detailsToRender['config']->confirmationEmail = array_key_exists('confirmationEmail', $_REQUEST) && trim($_REQUEST['confirmationEmail']) ? trim($_REQUEST['confirmationEmail']) : '';
				$detailsToRender['config']->directDebitDisclaimer = array_key_exists('directDebitDisclaimer', $_REQUEST) && trim($_REQUEST['directDebitDisclaimer']) ? trim($_REQUEST['directDebitDisclaimer']) : '';
				$detailsToRender['config']->directDebitText = array_key_exists('directDebitText', $_REQUEST) && trim($_REQUEST['directDebitText']) ? trim($_REQUEST['directDebitText']) : '';
				$detailsToRender['config']->directDebitEmail = array_key_exists('directDebitEmail', $_REQUEST) && trim($_REQUEST['directDebitEmail']) ? trim($_REQUEST['directDebitEmail']) : '';
				if (!$detailsToRender['config']->merchantId) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a valid Secure Pay Merchant ID.";
					$detailsToRender['invalid_values']['merchantId'] = true;
				}
				if (!$detailsToRender['config']->password) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a password for the Secure Pay account.";
					$detailsToRender['invalid_values']['password'] = true;
				}
				if (!$detailsToRender['config']->confirmationText) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be displayed to a customer after making a credit card payment.";
					$detailsToRender['invalid_values']['confirmationText'] = true;
				}
				if (!$detailsToRender['config']->confirmationEmail) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be emailed to a customer after making a credit card payment.";
					$detailsToRender['invalid_values']['confirmationEmail'] = true;
				}
				if (!$detailsToRender['config']->directDebitDisclaimer) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter the disclaimer/terms and conditions to be displayed to a user about to set up a direct debit.";
					$detailsToRender['invalid_values']['directDebitDisclaimer'] = true;
				}
				if (!$detailsToRender['config']->directDebitText) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be displayed to a customer after making a credit card payment and setting up a direct debit.";
					$detailsToRender['invalid_values']['directDebitText'] = true;
				}
				if (!$detailsToRender['config']->directDebitEmail) {
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be emailed to a customer after making a credit card payment and setting up a direct debit.";
					$detailsToRender['invalid_values']['directDebitEmail'] = true;
				}

				try {
					if (!$detailsToRender['error']) {
						$detailsToRender['config']->save();
					} else {
						$action = 'edit';
					}
				} catch (Exception $e) {
					$detailsToRender['error'] = 'Failed to save details: ' . $e->getMessage();
					$action = 'edit';
				}
				break;

			case 'edit':
				$detailsToRender['invalid_values'] = array();
				break;

			case 'error':
				break;

			case 'view':
			default:
				break;
		}

		$detailsToRender['action'] = $action;
		$this->LoadPage('customer_group_credit_card_config', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	function ViewEmailTemplateHistory($subPath) {
		$iCustomerGroupId = $subPath[0];
		$iTemplateId = $subPath[1];

		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));

		$aDetailsToRender = array();

		$aDetailsToRender['customerGroup'] = $iCustomerGroupId ? Customer_Group::getForId($iCustomerGroupId) : null;
		$aDetailsToRender['sTemplateName'] = Email_Template::getForId(Email_Template_Customer_Group::getForId($iTemplateId)->email_template_id)->name;
		$aDetailsToRender['iTemplateId'] = $iTemplateId;

		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup($iCustomerGroupId, $aDetailsToRender['customerGroup'] ? $aDetailsToRender['customerGroup']->name : null);
		BreadCrumb()->SetCurrentPage("View Email Template History");

		try {
			$this->LoadPage('email_template_history', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		} catch (Exception $e) {
			$aDetailsToRender['Message'] = "An error occured while trying to build the \"View Email History\" page";
			$aDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}