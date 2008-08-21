<?php

class Credit_Card_Payment
{

	public static function availableForCustomerGroup($mxdCustomerGroupOrId)
	{
		if (!FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
		{
			return FALSE;
		}
		require_once dirname(__FILE__) . '/Credit_Card_Payment_Config.php';
		return Credit_Card_Payment_Config::getForCustomerGroup($mxdCustomerGroupOrId);
	}

	public static function getPaymentPanel($accountId, $targetContainerId=NULL)
	{
		$params = self::getJavaScriptActionParams($accountId);
		if (!$params)
		{
			return FALSE;
		}
		$panel = $targetContainerId ? '<div id="credit-card-payment-panel"></div>' : '';
		$targetContainerId = $targetContainerId ? $targetContainerId : 'credit-card-payment-panel';

		$panel .= "
		<script><!--
			function creditCardPaymentOnLoad()
			{
				new CreditCardPaymentPanel($params, \"$targetContainerId\");
			}
			Event.observe(window, \"load\", creditCardPaymentOnLoad);
		//--></script>";

		return $params ? "<input type='button' id='online-credit-card-payment-button' class='online-credit-card-payment-button' onclick=\"new CreditCardPayment($paramas)\" />" : FALSE;
	}

	public static function getPopupActionButton($accountId)
	{
		$params = self::getJavaScriptActionParams($accountId);
		return $params ? "<input type='button' id='online-credit-card-payment-button' class='online-credit-card-payment-button' onclick=\"new CreditCardPayment($paramas)\" />" : FALSE;
	}

	// Should probably detect this automatically and use the primary contact details
	// when employee is logged in or the contact details if in customer interface
	private static function getJavaScriptActionParams($accountId)
	{
		// Check that the module is enabled
		if (!FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
		{
			return FALSE;
		}

		// Allow this to work when autoloading is not working (old framework usage)
		if (!class_exists('Flex'))
		{
			require_once dirname(__FILE__) . '/../../Flex.php';
		}
		if (!class_exists('Account'))
		{
			require_once dirname(__FILE__) . '/../../Account.php';
		}
		if (!class_exists('Credit_Card_Payment_Config'))
		{
			require_once dirname(__FILE__) . '/Credit_Card_Payment_Config.php';
		}
		if (!class_exists('Contact'))
		{
			require_once dirname(__FILE__) . '/../../Contact.php';
		}

		// The Module is enabled.
		// Load the account details
		$account = $accountId ? Account::getForId($accountId) : NULL;
		if (!$account)
		{
			return FALSE;
		}

		// We should now check to see if the customer group for the account allows credit card payments (it requires a config)
		$creditCardPaymentConfig = Credit_Card_Payment_Config::getForCustomerGroup($account->customerGroup);
		if (!$creditCardPaymentConfig)
		{
			return FALSE;
		}

		// Figure out which contact we need
		$contactId = NULL;
		if (Flex::isAdminSession())
		{
			// Take contact id from the account.PrimaryContact 
			$contactId = $account->primaryContact;
		}
		else if (Flex::isCustomerSession())
		{
			// Take the contact id from the session
			$contactId = $_SESSION["User"]["Id"];
		}

		// Load the contact details
		$contact = $contactId ? Contact::getForId($contactId) : NULL;
		if (!$contact)
		{
			return FALSE;
		}

		$contactName = str_replace("'", "\\'", $contact->getName());
		$contactEmail = str_replace("'", "\\'", $contact->email);

		$amountOwing = $account->getBalance();
		$accountName = str_replace("'", "\\'", $account->getName());

		// Output a link for making a credit card payment, using the credit_card_payment.js file.
		$allowDD = Flex::isCustomerSession() ? 'true' : 'false';
		return "{$account->id}, '{$account->abn}', '$accountName', '$contactName', '$contactEmail', $amountOwing, $allowDD)' />";
	}
}

?>
