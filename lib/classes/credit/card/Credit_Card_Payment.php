<?php

class Credit_Card_Payment
{


	public static function makePayment($intAccountId, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $bolDD)
	{
		// Check that the module is enabled
		if (!defined('FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS') || !FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
		{
			throw new Credit_Card_Payment_Not_Enabled_Exception();
		}

		// The Module is enabled.
		// Load the account details
		$account = $intAccountId ? Account::getForId($intAccountId) : NULL;
		if (!$account)
		{
			throw new Exception('Invalid account selected for credit card payment.');
		}

		if (Flex::isAdminSession())
		{
			// Prevent admins from setting up direct debits
			$bolDD = FALSE;
		}
		else if (Flex::isCustomerSession())
		{
			// Check that the requested account is in the authenticated customers account group
			$contact = Contact::getForId($_SESSION['User']['Id']);
			if (!$contact || !$contact->canAccessAccount($account))
			{
				throw new Exception("Invalid user account selected for credit card payment.");
			}
		}
		else
		{
			// This should NEVER EVER HAPPEN!! The session type should always be recognised.
			// Authentication should have prevented the code getting this far.
			throw new Exception("Invalid session. Unable to make credit card payments.");
		}

		// We should now check to see if the customer group for the account allows credit card payments (it requires a config)
		$creditCardPaymentConfig = Credit_Card_Payment_Config::getForCustomerGroup($account->customerGroup);
		if (!$creditCardPaymentConfig)
		{
			throw new Credit_Card_Payment_Not_Configurred_Exception();
		}

		// Validate the expiry date
		$intMonth = intval($intMonth);
		$month = intval(date("m"));
		if ($intMonth <= 0 || $intMonth > 12)
		{
			throw new Exception('Invalid expiry month specified.');
		}
		$intYear = intval($intYear);
		$year = intval(date("Y"));
		if ($intYear > ($year + 10))
		{
			throw new Exception('Invalid expiry year specified.');
		}
		if (($intYear == $year && $intMonth < $month) || $intYear < $year)
		{
			throw new Exception('The expiry date has already passed.');
		}
		mktime(0, 0, 0, $intMonth, 1, $intYear);

		// Validate the email address
		if (!EmailAddressValid($strEmail))
		{
			throw new Exception('The email address provided is invalid.');
		}

		// $intCardType
		$cardType = Credit_Card_Type::getForId(intval($intCardType));
		if (!$cardType)
		{
			throw new Exception('The selected credit card type is not supported.');
		}

		// $strCardNumber - Need to check that the type matches that specified and that the number is valid.
		$strCardNumber = preg_replace("/[^0-9]+/", "", $strCardNumber);
		if (!$cardType->cardNumberIsValid($strCardNumber))
		{
			throw new Exception('The specified card number is invalid for the credit card type.');
		}

		// Need to check that the cvv is valid for the card type.
		$strCVV = preg_replace("/[^0-9]+/", "", $intCVV);
		if (!$cardType->cvvIsValid($strCVV))
		{
			throw new Exception("CVV should be " . $cardType->cvvLength . " digits long.");
		}

		// Check that the name has been specified.
		$strName = trim($strName);
		if (!$strName)
		{
			throw new Exception('No credit card holder name specified.');
		}

		// Check that the amount has been specified and that is is a positive amount between the min and max permitted for the card type.
		$fltAmount = preg_replace(array("/^0+/", "/[^0-9\.\-]+/"), "", '0'.$fltAmount);
		$amount = floatVal($fltAmount);
		if (!$fltAmount || strpos($fltAmount, '-') !== FALSE || $amount < $cardType->minimumAmount || $amount > $cardType->maximumAmount)
		{
			throw new Exception('Invalid amount specified.');
		}

		// OK. That's everything validated. Now we can start talking to SecurePay...
		// $account, $strEmail, $cardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $bolDD
	}




	public static function availableForCustomerGroup($mxdCustomerGroupOrId)
	{
		if (!defined('FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS') || !FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
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
		$panel = $targetContainerId ? '' : '<div id="credit-card-payment-panel"></div>';
		$targetContainerId = $targetContainerId ? $targetContainerId : 'credit-card-payment-panel';

		$disclaimer = str_replace(array('"', "\n", "\r"), array('\\"', '\\n', ''), $params[1]->directDebitDisclaimer);

		$panel .= "
		<script><!--
			function creditCardPaymentOnLoad()
			{\n" . (Flex::isCustomerSession() ? ("\t\t\t\tCreditCardPayment.directDebitTermsAndConditions = \"$disclaimer\"") : "") . "
				new CreditCardPaymentPanel(".$params[0].", \"$targetContainerId\");
			}
			Event.observe(window, \"load\", creditCardPaymentOnLoad);
		//--></script>";

		return $panel;
	}

	public static function getPopupActionButton($accountId)
	{
		$params = self::getJavaScriptActionParams($accountId);
		$disclaimer = '';
		if ($params)
		{
			$disclaimer = str_replace(array('"', "\n", "\r"), array('\\"', '\\n', ''), $params[1]->directDebitDisclaimer);
		}
		return $params ? "
		<script><!--
			function creditCardPaymentOnLoad()
			{\n" . (Flex::isCustomerSession() ? ("\t\t\t\tCreditCardPayment.directDebitTermsAndConditions = \"$disclaimer\"") : "") . "
			}
			Event.observe(window, \"load\", creditCardPaymentOnLoad);
		//--></script><input type='button' id='online-credit-card-payment-button' class='online-credit-card-payment-button' value=\"Pay by Credit Card\" onclick=\"new CreditCardPayment(".$params[0].");return false;\" />" : FALSE;
	}

	// Should probably detect this automatically and use the primary contact details
	// when employee is logged in or the contact details if in customer interface
	private static function getJavaScriptActionParams($accountId)
	{
		// Check that the module is enabled
		if (!defined('FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS') || !FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
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
		return array("{$account->id}, '{$account->abn}', '$accountName', '$contactName', '$contactEmail', $amountOwing, $allowDD", $creditCardPaymentConfig);
	}

}

class Credit_Card_Payment_Not_Enabled_Exception 	extends Exception { function __construct()	{ parent::__construct("Credit Card Payments are not enabled in Flex."); 				} }
class Credit_Card_Payment_Not_Configurred_Exception extends Exception { function __construct() 	{ parent::__construct("Credit Card Payments have not been configurred in Flex Admin."); } }

?>
