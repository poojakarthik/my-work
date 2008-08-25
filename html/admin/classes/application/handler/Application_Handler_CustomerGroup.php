<?php

class Application_Handler_CustomerGroup extends Application_Handler
{
	public function CreditCardConfig($subPath)
	{
		$detailsToRender = array();

		// We need to load the configuration record for the customer group
		$customerGroupId = count($subPath) ? intval(array_shift($subPath)) : NULL;
		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';

		$detailsToRender['customerGroup'] = $customerGroupId ? Customer_Group::getForId($customerGroupId) : NULL;
		$detailsToRender['config'] = $detailsToRender['customerGroup'] ? Credit_Card_Payment_Config::getForCustomerGroup($detailsToRender['customerGroup'], TRUE) : NULL;

		BreadCrumb()->Admin_Console();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup($customerGroupId, $detailsToRender['customerGroup'] ? $detailsToRender['customerGroup']->name : NULL);
		BreadCrumb()->SetCurrentPage("Secure Pay Configuration");

		if (!$detailsToRender['customerGroup'] || !$detailsToRender['config'])
		{
			$action = 'error';
		}

		switch ($action)
		{
			case 'delete':
				if (!$detailsToRender['config']->isSaved())
				{
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
				if (!$detailsToRender['config']->merchantId)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a valid Secure Pay Merchant ID.";
					$detailsToRender['invalid_values']['merchantId'] = TRUE;
				}
				if (!$detailsToRender['config']->password)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a password for the Secure Pay account.";
					$detailsToRender['invalid_values']['password'] = TRUE;
				}
				if (!$detailsToRender['config']->confirmationText)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be displayed to a customer after making a credit card payment.";
					$detailsToRender['invalid_values']['confirmationText'] = TRUE;
				}
				if (!$detailsToRender['config']->confirmationEmail)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be emailed to a customer after making a credit card payment.";
					$detailsToRender['invalid_values']['confirmationEmail'] = TRUE;
				}
				if (!$detailsToRender['config']->directDebitDisclaimer)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter the disclaimer/terms and conditions to be displayed to a user about to set up a direct debit.";
					$detailsToRender['invalid_values']['directDebitDisclaimer'] = TRUE;
				}
				if (!$detailsToRender['config']->directDebitText)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be displayed to a customer after making a credit card payment and setting up a direct debit.";
					$detailsToRender['invalid_values']['directDebitText'] = TRUE;
				}
				if (!$detailsToRender['config']->directDebitEmail)
				{
					$detailsToRender['error'] .= ($detailsToRender['error'] ? '<br/>' : '') . "Please enter a message to be emailed to a customer after making a credit card payment and setting up a direct debit.";
					$detailsToRender['invalid_values']['directDebitEmail'] = TRUE;
				}

				try
				{
					if (!$detailsToRender['error'])
					{
						$detailsToRender['config']->save();
					}
					else
					{
						$action = 'edit';
					}
				}
				catch (Exception $e)
				{
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

}

?>
