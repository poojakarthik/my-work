<?php

// TODO: CR135 -- make this FALSE
// If this is set to TRUE then, if in test mode, confirmation emails will be sent to ybs-admin@ybs.net.au
define(SEND_CREDIT_CARD_EMAILS_IN_TEST_MODE, TRUE);

class Credit_Card_Payment
{
	const PAYMENT_TYPE_CREDIT_CARD_PAYMENT = '0';
	const PAYMENT_TYPE_CREDIT_CARD_REFUND = '4';
	const PAYMENT_TYPE_CREDIT_CARD_REVERSAL = '6';
	const PAYMENT_TYPE_CREDIT_CARD_PRE_AUTHORISATION = '10';
	const PAYMENT_TYPE_CREDIT_CARD_COMPLETE = '11';
	const PAYMENT_TYPE_DIRECT_DEBIT = '15';
	const PAYMENT_TYPE_DIRECT_CREDIT = '17';

	const REQUEST_TYPE_PAYMENT = 'Payment';
	const REQUEST_TYPE_PERIODIC = 'Periodic';
	const REQUEST_TYPE_ECHO = 'Echo';

	const CURRENCY_AUD = 'AUD';

	protected static $realHost = "www.securepay.com.au/xmlapi/payment";
	protected static $testHost = "www.securepay.com.au/test/payment";

	const TOKEN_START = '[';
	const TOKEN_END = ']';

	public static function getHost()
	{
		if (self::isTestMode())
		{
			return self::$testHost;
		}
		else
		{
			return self::$realHost;
		}
	}

	public static function isTestMode()
	{
		// If constant CREDIT_CARD_PAYMENT_TEST_MODE is not defined or is defined and set to TRUE, then isTestMode will return TRUE
		// The constant HAS TO BE defined and set to FALSE, for LIVE Credit Card payments to work
		return (!defined('CREDIT_CARD_PAYMENT_TEST_MODE') || CREDIT_CARD_PAYMENT_TEST_MODE !== FALSE);
	}

	private static function getGmtTimeStamp($time)
	{
		$O = str_pad(intval(intval(date('Z', $time))/60), 3, '0', STR_PAD_LEFT);
		$ms = str_pad((intval(date('u', $time))%1000), 3, '0', STR_PAD_LEFT);
		return date("YdmHis", $time)."{$ms}000" . ($O < 0 ? '-' : '+') . $O;
	}

	private static function amount2dp($strAmountInDollars)
	{
		if (is_float($strAmountInDollars))
		{
			$neg = $strAmountInDollars < 0 ? '-' : '';
			$strAmountInCents = ''.round(abs($strAmountInDollars)*100);
			if (strlen($strAmountInCents) <= 1)
			{
				// Under 0 - 9 cents
				$strAmountInDollars = '0.0'.$strAmountInCents;
			}
			else if (strlen($strAmountInCents) <= 2)
			{
				// 10 - 99 cents
				$strAmountInDollars = '0.'.$strAmountInCents;
			}
			else
			{
				// Over a dollar
				$strAmountInDollars = substr($strAmountInCents, 0, -2) . '.' . substr($strAmountInCents, - 2);
			}
			$strAmountInDollars = $neg.$strAmountInDollars;
		}
		$nrDecPlaces = (strpos($strAmountInDollars, '.') === FALSE) ? 0 : (strlen($strAmountInDollars) - strpos($strAmountInDollars, '.') - 1);
		if ($nrDecPlaces != 2)
		{
			if ($nrDecPlaces < 2)
			{
				$strAmountInDollars .= str_repeat('0', 2 - $nrDecPlaces);
			}
			else
			{
				$strAmountInDollars = substr($strAmountInDollars, 0, (strlen($strAmountInDollars) - $nrDecPlaces) + 2);
			}
		}
		return $strAmountInDollars;
	}

	private static function amountInCents($strAmountInDollars)
	{
		return str_replace('.', '', self::amount2dp($strAmountInDollars));
	}

	private static function setPaymentCreditCard($merchantId, $merchantPassword, $time, $messageId, $paymentAmount, $purchaseOrderNo, $cardNumber, $cvv, $expiryMonth, $expiryYear, $requestType=self::REQUEST_TYPE_PAYMENT, $paymentType=self::PAYMENT_TYPE_CREDIT_CARD_PAYMENT, $currency=self::CURRENCY_AUD, $preauthid='', $txnid='')
	{
		$timestamp		= self::getGmtTimeStamp($time);
		$expiryMonth	= (intval($expiryMonth) >= 10 ? '' : '0') . intval($expiryMonth);
		$expiryYear		= intval($expiryYear) % 100;
		$expiryYear		= (intval($expiryYear) >= 10 ? '' : '0') . intval($expiryYear);
		$paymentAmount	= self::amountInCents($paymentAmount);

		$merchantId			= htmlspecialchars($merchantId);
		$merchantPassword	= htmlspecialchars($merchantPassword);
		$time				= htmlspecialchars($time);
		$messageId			= htmlspecialchars($messageId);
		$currency			= htmlspecialchars($currency);
		$paymentAmount		= htmlspecialchars($paymentAmount);
		$purchaseOrderNo	= htmlspecialchars($purchaseOrderNo);
		$cardNumber			= htmlspecialchars($cardNumber);
		$cvv				= htmlspecialchars($cvv);
		$expiryMonth		= htmlspecialchars($expiryMonth);
		$expiryYear			= htmlspecialchars($expiryYear);
		$requestType		= htmlspecialchars($requestType);
		$paymentType		= htmlspecialchars($paymentType);
		$preauthid			= htmlspecialchars($preauthid);
		$txnid				= htmlspecialchars($txnid);

		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r
<SecurePayMessage>\r
	<MessageInfo>\r
		<messageID>$messageId</messageID>\r
		<messageTimestamp>$timestamp</messageTimestamp>\r
		<timeoutValue>60</timeoutValue>\r
		<apiVersion>xml-4.2</apiVersion>\r
	</MessageInfo>\r
	<MerchantInfo>\r
		<merchantID>$merchantId</merchantID>\r
		<password>$merchantPassword</password>\r
	</MerchantInfo>\r
	<RequestType>$requestType</RequestType>\r
	<Payment>\r
		<TxnList count=\"1\">\r
			<Txn ID=\"1\">\r
				<txnType>$paymentType</txnType>\r
				<txnSource>23</txnSource>\r
				<amount>$paymentAmount</amount>\r
				<currency>$currency</currency>\r
				<purchaseOrderNo>$purchaseOrderNo</purchaseOrderNo>\r
				" . (strlen($preauthid) ? "<preauthID>$preauthid</preauthID>" : '') . "\r
				" . (strlen($txnid) ? "<txnID>$txnid</txnID>" : '') . "\r
				<CreditCardInfo>\r
					<cardNumber>$cardNumber</cardNumber>\r
					<cvv>$cvv</cvv>\r
					<expiryDate>$expiryMonth/$expiryYear</expiryDate>\r
				</CreditCardInfo>\r
			</Txn>\r
		</TxnList>\r
	</Payment>\r
</SecurePayMessage>";
	}

	private static function openSocket($host, $query)
	{
		/**************************/
		/* Secure Socket Function */
		/**************************/

		// Break the URL into usable parts
		$path = explode('/',$host);
		$host = $path[0];
		unset($path[0]);
		$path = '/'.(implode('/',$path));

		// Prepare the post query
		$post  = "POST $path HTTP/1.1\r\n";
		$post .= "Host: $host\r\n";
		$post .= "Content-type: application/x-www-form-urlencoded\r\n";
		$post .= "Content-type: text/xml\r\n";
		$post .= "Content-length: ".strlen($query)."\r\n";
		$post .= "Connection: close\r\n\r\n$query";

		/***********************************************/
		/* Open the secure socket and post the message */
		/***********************************************/
		$h = @fsockopen("ssl://".$host, 443, $errno, $errstr);

		if ($errstr)
		{
			throw new Credit_Card_Payment_Communication_Exception("$errstr ($errno)");
		}
		if ($h === FALSE)
		{
			throw new Credit_Card_Payment_Communication_Exception("Failed to connect to SecurePay server.");
		}
		$ok = @fwrite($h,$post);
		if ($ok === FALSE)
		{
			throw new Credit_Card_Payment_Communication_Exception("Failed to submit request to SecurePay server.");
		}
		if ($ok !== strlen($post))
		{
			throw new Credit_Card_Payment_Communication_Exception("Failed to submit complete request to SecurePay server.");
		}

		/*******************************************/
		/* Retrieve the HTML headers (and discard) */
		/*******************************************/

		$itimelimit = 120;
		stream_set_timeout($h, $itimelimit);
		set_time_limit($itimelimit + 10);

		$headers = "";
		while ($str = @fgets($h, 4096))
		{
			if ($str === FALSE)
			{
				throw new Credit_Card_Payment_Communication_Response_Exception("Failed to read response headers from SecurePay server.");
			}
			if (!trim($str))
			{
				break;
			}
			$headers .= trim($str) . "\n";
		}

		$headers2 = "";
		while ($str = @fgets($h, 4096))
		{
			if ($str === FALSE)
			{
				throw new Credit_Card_Payment_Communication_Response_Exception("Failed to read response from SecurePay server.");
			}
			if (!trim($str))
			{
				break;
			}
			$headers2 .= trim($str) . "\n";
		}

		/**********************************************************/
		/* Retrieve the response */
		/**********************************************************/
	
		$body = "";
		while (!feof($h))
		{
			$str = @fgets($h, 4096);
			if ($str === FALSE)
			{
				$body = trim($body);
				if ($body && strlen($body) > 19 && strtolower(substr($body, -19)) == "</securepaymessage>")
				{
					// We reached the end of the stream, but PHP is naf at recognising it!
					break;
				}
				throw new Credit_Card_Payment_Communication_Response_Exception("Failed to read body of response from SecurePay server.".strtolower(substr($body, -19)));
			}
			if (!trim($str))
			{
				break;
			}
			$body .= $str;
		}

		// Close the socket
		@fclose($h);

		// Return the body of the response
		return $body;
	}
	
	public static function buildMessageTokens($oTransactionDetails, $sContactName, $sEmail)
	{
		$aTokens	= array();
		$oAccount	= Account::getForId($oTransactionDetails->iAccountId);
		
		// Balance before transaction string
		$sBalanceBefore	= self::amount2dp($oTransactionDetails->fBalanceBefore);
		if ($sBalanceBefore[0] == '-') 
		{
			$sBalanceBefore	= substr($sBalanceBefore, 1).' CR';
		}
		
		// Balance after transaction string
		$sBalanceAfter	= self::amount2dp($oAccount->getBalance());
		if ($sBalanceAfter[0] == '-') 
		{
			$sBalanceAfter	= substr($sBalanceAfter, 1).' CR';
		}

		$aTokenList	= self::listMessageTokens();
		foreach ($aTokenList as $sToken => $sDescription)
		{
			switch ($sToken)
			{
				case self::TOKEN_START.'DATE_TIME'.self::TOKEN_END:
					$aTokens[$sToken] 	= date('g:i:sA, jS M Y', $oTransactionDetails->iTime);
					break;
				case self::TOKEN_START.'PAYMENT_REFERENCE'.self::TOKEN_END:
					$aTokens[$sToken] 	= $oTransactionDetails->sPurchaseOrderNumber;
					break;
				case self::TOKEN_START.'AMOUNT_APPLIED'.self::TOKEN_END:
					$aTokens[$sToken] 	= '$' . $oTransactionDetails->fAmount;
					break;
				case self::TOKEN_START.'AMOUNT_SURCHARGE'.self::TOKEN_END:
					$aTokens[$sToken] 	= '$' . $oTransactionDetails->fSurcharge;
					break;
				case self::TOKEN_START.'AMOUNT_TOTAL'.self::TOKEN_END:
					$aTokens[$sToken] 	= '$' . $oTransactionDetails->fTotal;
					break;
				case self::TOKEN_START.'BALANCE_BEFORE'.self::TOKEN_END:
					$aTokens[$sToken] 	= '$' . $sBalanceBefore;
					break;
				case self::TOKEN_START.'BALANCE_AFTER'.self::TOKEN_END:
					$aTokens[$sToken]	= '$' . $sBalanceAfter;
					break;
				case self::TOKEN_START.'ACCOUNT_NUMBER'.self::TOKEN_END:
					$aTokens[$sToken] 	= $oTransactionDetails->iAccountId;
					break;
				case self::TOKEN_START.'CONTACT_NAME'.self::TOKEN_END:
					$aTokens[$sToken] 	= $sContactName;
					break;
				case self::TOKEN_START.'CONTACT_EMAIL'.self::TOKEN_END:
					$aTokens[$sToken] 	= $sEmail;
					break;
				case self::TOKEN_START.'COMPANY_ABN'.self::TOKEN_END:
					$aTokens[$sToken] 	= $oAccount->abn;
					break;
				case self::TOKEN_START.'COMPANY_NAME'.self::TOKEN_END:
					$aTokens[$sToken]	= $oAccount->businessName ? $oAccount->businessName : $oAccount->tradingName;
					break;
				case self::TOKEN_START.'CARD_NUMBER'.self::TOKEN_END:
					$aTokens[$sToken] 	= (substr($oTransactionDetails->sCardNumber, 0, 4).str_repeat('.', strlen($oTransactionDetails->sCardNumber) - 8).substr($oTransactionDetails->sCardNumber, -4));
					break;
			}
		}
		
		return $aTokens;
	}

	public static function replaceMessageTokens($message, $tokens)
	{
		foreach ($tokens as $token => $value)
		{
			do
			{
				$oldMessage = $message;
				$message = str_replace($token, $value, $message);
			} while($oldMessage != $message);
		}
		return $message;
	}

	public static function listMessageTokens()
	{
		return array(
			self::TOKEN_START . "DATE_TIME" . self::TOKEN_END 			=> "will be replaced by the date and time of the action.",
			self::TOKEN_START . "PAYMENT_REFERENCE" . self::TOKEN_END	=> "will be replaced by our unique payment reference number.",
			self::TOKEN_START . "AMOUNT_APPLIED" . self::TOKEN_END 		=> "will be replaced by the payment amount applied to the balance of the account.",
			self::TOKEN_START . "AMOUNT_SURCHARGE" . self::TOKEN_END 	=> "will be replaced by the amount of the credit card surcharge for the transaction.",
			self::TOKEN_START . "AMOUNT_TOTAL" . self::TOKEN_END 		=> "will be replaced by the amount actually charged to their credit card.",
			self::TOKEN_START . "ACCOUNT_NUMBER" . self::TOKEN_END 		=> "will be replaced by the account number.",
			self::TOKEN_START . "COMPANY_ABN" . self::TOKEN_END 		=> "will be replaced by the ABN of the company.",
			self::TOKEN_START . "COMPANY_NAME" . self::TOKEN_END 		=> "will be replaced by the name of the company.",
			self::TOKEN_START . "BALANCE_BEFORE" . self::TOKEN_END 		=> "will be replaced by the balance of the account before applying the payment.",
			self::TOKEN_START . "BALANCE_AFTER" . self::TOKEN_END 		=> "will be replaced by the balance of the account after applying the payment.",
			self::TOKEN_START . "CARD_NUMBER" . self::TOKEN_END 		=> "will be replaced by the first and last four (4) digits of the credit card number.",
			self::TOKEN_START . "CONTACT_EMAIL" . self::TOKEN_END 		=> "will be replaced by the email address a confirmation message was sent to.",
			self::TOKEN_START . "CONTACT_NAME" . self::TOKEN_END 		=> "will be replaced by the contact name.",
		);
	}

	public static function availableForCustomerGroup($mxdCustomerGroupOrId)
	{
		if (!Flex_Module::isActive(FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS))
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
		<script type=\"text/javascript\"><!--
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
		
		if ($params)
		{
			return "<input type='button' id='online-credit-card-payment-button' class='online-credit-card-payment-button' value=\"Pay by Credit Card\" onclick=\"new Popup_Credit_Card_Payment(". htmlspecialchars($params[0], ENT_QUOTES) .");return false;\" />";
			
			/*
			// Customer
			return "<script><!--
						function creditCardPaymentOnLoad()
						{\n" . (Flex::isCustomerSession() ? ("\t\t\t\tCreditCardPayment.directDebitTermsAndConditions = \"$disclaimer\"") : "") . "
						}
						Event.observe(window, \"load\", creditCardPaymentOnLoad);
					//--></script><input type='button' id='online-credit-card-payment-button' class='online-credit-card-payment-button' value=\"Pay by Credit Card\" onclick=\"new CreditCardPayment(". htmlspecialchars($params[0], ENT_QUOTES) .");return false;\" />";
			*/
		}
		return false;
	}

	public static function makeCreditCardPayment($iAccountId, $iContactId, $iEmployeeId, $iCardType, $sCardNumber, $iCVV, $iMonth, $iYear, $sName, $fAmount, $sEmail, $bDirectDebit)
	{
		$oAccount	= Account::getForId($iAccountId);
		$oContact	= Contact::getForId($iContactId);
		
		if ($bDirectDebit)
		{
			// Direct debit to be setup for the account using the credit card
			// Cache the accounts previous payment method information (just in case the payment fails)
			$iPreviousCreditCard	= $oAccount->CreditCard;
			$iPreviousDirectDebit	= $oAccount->DirectDebit;
			$iPreviousBillingType	= $oAccount->BillingType;
			
			// Create a new credit card
			$oCreditCard				= new Credit_Card();
			$oCreditCard->AccountGroup	= $oAccount->AccountGroup;
			$oCreditCard->CardType		= $iCardType;
			$oCreditCard->Name			= $sName;
			$oCreditCard->CardNumber	= Encrypt($sCardNumber);
			$oCreditCard->ExpMonth		= str_pad($iMonth, 2, '0', STR_PAD_LEFT);
			$oCreditCard->ExpYear		= $iYear;
			$oCreditCard->CVV			= Encrypt($iCVV);
			$oCreditCard->Archived		= 0;
			$oCreditCard->created_on	= date('Y-m-d H:i:s');
			$oCreditCard->employee_id	= $iEmployeeId;
			$oCreditCard->save();
			
			// Set the new credit card as the payment method
			$oAccount->DirectDebit	= null;
			$oAccount->CreditCard 	= $oCreditCard->Id;
			$oAccount->BillingType 	= BILLING_TYPE_CREDIT_CARD;
			$oAccount->save($iEmployeeId);
			
			Log::getLog()->log("Created credit card {$oCreditCard->id}, saved as payment method");
		}
		
		try
		{
			// Attempt payment
			$oTransactionDetails	= self::_makePayment($iAccountId, $iCardType, $sCardNumber, $iCVV, $iMonth, $iYear, $sName, $fAmount, $iEmployeeId, $iContactId);
		}
		catch (Exception $oException)
		{
			// Payment failed
			if ($bDirectDebit)
			{
				// Archive the credit card, reinstate the previous payment method
				$oCreditCard->Archived	= 1;
				$oCreditCard->save();
				
				$oAccount->DirectDebit	= $iPreviousDirectDebit;
				$oAccount->CreditCard 	= $iPreviousCreditCard;
				$oAccount->BillingType 	= $iPreviousBillingType;
				$oAccount->save($iEmployeeId);
				
				Log::getLog()->log("Reverted payment method");
			}
			
			throw $oException;
		}
		
		// Send the payment confirmation email
		if (!self::isTestMode() || (defined('SEND_CREDIT_CARD_EMAILS_IN_TEST_MODE') && SEND_CREDIT_CARD_EMAILS_IN_TEST_MODE === TRUE))
		{
			// Validate email address
			if (EmailAddressValid($sEmail))
			{
				// Valid, send confirmation email
				$oCustomerGroup 			= Customer_Group::getForId($oAccount->customerGroup);
				$oCreditCardPaymentConfig 	= Credit_Card_Payment_Config::getForCustomerGroup($oAccount->customerGroup);
				try
				{
					$aMessageTokens		= self::buildMessageTokens($oTransactionDetails, $oContact->getName(), $sEmail);
					$oEmail 			= new Email_Notification(EMAIL_NOTIFICATION_PAYMENT_CONFIRMATION, $oAccount->CustomerGroup);
					$oEmail->subject	= (self::isTestMode() ? '[TEST EMAIL] ' : '')."{$oCustomerGroup->name} Credit Card Payment Confirmation (Ref: {$oTransactionDetails->sPurchaseOrderNumber} / {$oTransactionDetails->sTransactionId})";
					$oEmail->text 		= self::replaceMessageTokens($oCreditCardPaymentConfig->confirmationEmail, $aMessageTokens);
					$oEmail->to 		= (self::isTestMode() ? 'ybs-admin@ybs.net.au' : $sEmail);
					$oEmail->send();
					Log::getLog()->log("Email sent to '".implode(', ', $oEmail->getRecipients())."'".(self::isTestMode() ? " instead of '{$sEmail}'" : ''));
				}
				catch (Exception $oException)
				{
					// Ignore this case
				}
			}
			else
			{
				// Invalid email address
				Log::getLog()->log("Invalid email address, confirmation email not sent");
			}
		}
		
		// Log the 'Payment Made' Action
		try
		{
			$sExtraDetails	= "";
			if (Flex::isAdminSession())
			{
				$sExtraDetails	.= "SecurePay credit card transaction via Flex\n Receipt No: {$oTransactionDetails->sTransactionId}";
			}
			else
			{
				$sExtraDetails	.= "SecurePay credit card transaction via Customer Portal, made by customer: ".$oContact->getName()."\n Receipt No: {$oTransactionDetails->sTransactionId}";
			}
			Action::createAction('Payment Made', $sExtraDetails, $oAccount->id, NULL, NULL, $iEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}
		catch (Exception $oException)
		{
			// Fail silently, but notify system administrators
			Flex::sendEmailNotificationAlert(
				"Failed to record Payment Action", 
				"Account: {$oAccount->id}\nContact: ".$oContact->getName()."\nEmployee Id: {$iEmployeeId}\n\nException Message:".$oException->getMessage(), 
				FALSE, 
				TRUE, 
				TRUE
			);
		}
		
		return $oTransactionDetails;
	}

	private static function _makePayment($iAccountId, $iCardType, $sCardNumber, $iCVV, $iMonth, $iYear, $sName, $fAmount, $iEmployeeId, $iContactId)
	{
		$iTime					= 	time();
		$bTestMode				= 	Credit_Card_Payment::isTestMode();
		$oTransactionDetails	= 	new Credit_Card_Payment_TransactionDetails(
										array(
											'iTime'			=> $iTime,
											'iAccountId'	=> $iAccountId,
											'sCardNumber'	=> $sCardNumber
										)
									);
		
		$oAccount			= Account::getForId($iAccountId);
		$bWaiveSurcharge	= ($oAccount->BillingType == BILLING_TYPE_CREDIT_CARD);
		
		// Check that the module is enabled
		if (!Flex_Module::isActive(FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS))
		{
			// This should never happen
			throw new Exception_Assertion("Credit Card Payments are not enabled in Flex", "Flex Module FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS is inactive, but its functionality has been called", "Inactive Flex Module has been accessed");
		}
		
		// Validate the expiry date
		$iMonth		= intval($iMonth);
		$iMonthNow 	= intval(date("m"));
		if ($iMonth <= 0 || $iMonth > 12)
		{
			throw new Credit_Card_Payment_Exception('Invalid expiry month specified.');
		}
		
		$iYear 		= intval($iYear);
		$iYearNow 	= intval(date("Y"));
		if ($iYear > ($iYearNow + 10))
		{
			throw new Credit_Card_Payment_Exception('Invalid expiry year specified.');
		}
		
		if (($iYear == $iYearNow && $iMonth < $iMonthNow) || $iYear < $iYearNow)
		{
			throw new Credit_Card_Payment_Exception('The expiry date has already passed.');
		}

		// $iCardType
		$oCardType	= Credit_Card_Type::getForId(intval($iCardType));
		if (!$oCardType)
		{
			throw new Credit_Card_Payment_Exception('The selected credit card type is not supported.');
		}

		// $sCardNumber - Need to check that the type matches that specified and that the number is valid.
		$sCardNumber	= preg_replace("/[^0-9]+/", "", $sCardNumber);
		if (!$oCardType->cardNumberIsValid($sCardNumber))
		{
			throw new Credit_Card_Payment_Exception('The specified card number is invalid for the credit card type.');
		}

		// Need to check that the cvv is valid for the card type.
		$sCVV	= preg_replace("/[^0-9]+/", "", $iCVV);
		if (!$oCardType->cvvIsValid($sCVV))
		{
			throw new Credit_Card_Payment_Exception("CVV should be " . $oCardType->cvvLength . " digits long.");
		}

		// Check that the name has been specified.
		$sName	= trim($sName);
		if (!$sName)
		{
			throw new Credit_Card_Payment_Exception('No credit card holder name specified.');
		}

		// Check that the amount has been specified and that it is a positive amount between the min and max permitted for the card type.
		$sAmount	= preg_replace(array("/^0+/", "/[^0-9\.\-]+/"), "", '0'.$fAmount);
		if ($sAmount[0] == '.') 
		{
			$sAmount = '0' . $sAmount;
		}
		
		if (!$sAmount || strpos($sAmount, '-') !== FALSE || !preg_match("/^(0|[1-9]+[0-9]*)(|\.[0-9]*)$/", $sAmount))
		{
			throw new Credit_Card_Payment_Exception('Invalid amount specified.');
		}
		
		$sAmount	= self::amount2dp($sAmount);
		
		// Calculate the surcharge
		$fSurcharge	= $bWaiveSurcharge ? 0 : $oCardType->calculateSurcharge($fAmount);
		
		// Calculate the total
		$fTotal	= $fAmount + $fSurcharge;
		
		// Check that the Total amount is between the min and max permitted for the card type.
		if ($fTotal < $oCardType->minimumAmount)
		{
			throw new Credit_Card_Payment_Exception('Invalid amount specified. It is too small for the given card type.');
		}
		else if ($fTotal > $oCardType->maximumAmount)
		{
			throw new Credit_Card_Payment_Exception('Invalid amount specified. It is too large for the given card type.');
		}
	
		// Convert amount to cents
		$iAmount 	= intval(self::amountInCents($fAmount));
		$iSurcharge	= intval(self::amountInCents($fSurcharge));
		$iTotal 	= intval(self::amountInCents($fTotal));

		Log::getLog()->log("F: amt={$fAmount}, sur={$fSurcharge}, tot={$fTotal}");
		Log::getLog()->log("I: amt={$iAmount}, sur={$iSurcharge}, tot={$iTotal}");

		// Check that the amount + surcharge comes to total and that the surcharge is right for the credit card type.
		if (abs($iTotal - ($iSurcharge + $iAmount)) > 0)
		{
			throw new Credit_Card_Payment_Exception("The amounts specified do not add up. [abs($iTotal - ($iSurcharge + $iAmount)) > 0]");
		}
		
		// Everything is valid, store in the transaction details
		$oTransactionDetails->fAmount		= $fAmount;
		$oTransactionDetails->iAmount		= $iAmount;
		$oTransactionDetails->fSurcharge	= $fSurcharge;
		$oTransactionDetails->iSurcharge	= $iSurcharge;
		$oTransactionDetails->fTotal		= $fTotal;
		$oTransactionDetails->iTotal		= $iTotal;
		
		try
		{
			// We should now check to see if the customer group for the account allows credit card payments (it requires a config)
			$oCreditCardPaymentConfig = Credit_Card_Payment_Config::getForCustomerGroup($oAccount->customerGroup);
			if (!$oCreditCardPaymentConfig)
			{
				throw new Exception_Assertion("Credit Card Payments have not been configurred in Flex Admin.", "Credit_Card_Payment_Config::getForCustomerGroup({$oAccount->customerGroup}) didn't return anything");
			}
			
			// Create payment_request
			$oPaymentRequest	= Payment_Request::generatePending($iAccountId, PAYMENT_TYPE_CREDIT_CARD, $fTotal, null, $iEmployeeId);
		}
		catch (Exception $oException)
		{
			throw new Credit_Card_Payment_Exception("Failed credit card payment preparation.".($bTestMode ? " ".$oException->getMessage() : ''));
		}
		
		try
		{
			// Start Transaction
			$oDataAccess	= DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
			
			// Get secure pay password & merchant id
			$sMerchantId	= $oCreditCardPaymentConfig->merchantId;
			$sPassword		= $oCreditCardPaymentConfig->password;
			
			// SecurePayMessage/MessageInfo/MessageID
			$sMessageId	= substr($oAccount->id.'.'.base64_encode($iTime), 0, 30);
	
			// SecurePayMessage/Payment/TxnList/Txn/purchaseOrderNo
			$sPurchaseOrderNo							= $oAccount->id.'.'.$iTime;
			$oTransactionDetails->sPurchaseOrderNumber	= $sPurchaseOrderNo;
	
			$sPassword	= $creditCardPaymentConfig->password;
			
			if (self::isTestMode())
			{
				// These are testing passwords for securepay
				// Taken from an email received on Wed 24/11/2010 1:23 PM (by Rod McTainsh, subject = 'FW: Secure Pay Details for TB VT PT' from Rich Davis)
				$oCustomerGroup	= Customer_Group::getForId($oAccount->CustomerGroup);
				switch ($oCustomerGroup->getConstantName())
				{
					case 'CUSTOMER_GROUP_TELCO_BLUE':
						$sPassword	= '2thCQJjo';
						break;
					case 'CUSTOMER_GROUP_PROTALK':
						$sPassword	= 'g5T2Wcpt';
						break;
					case 'CUSTOMER_GROUP_VOICETALK':
						$sPassword	= 'kd39cBLL';
						break;
				}
			}
			
			// Date string used by multiple records that are created below
			$sNowDate	= date('Y-m-d', $iTime);
				
			// Create a Payment
			$oPayment				= new Payment();
			$oPayment->AccountGroup	= $oAccount->AccountGroup;
			$oPayment->Account		= $iAccountId;
			$oPayment->EnteredBy	= $iEmployeeId;
			$oPayment->Amount		= $fTotal;
			$oPayment->Balance		= $fTotal;
			$oPayment->PaidOn		= $sNowDate;
			$oPayment->OriginId		= substr($sCardNumber, 0, 6).'...'.substr($sCardNumber, -3);
			$oPayment->OriginType	= PAYMENT_TYPE_CREDIT_CARD;
			$oPayment->Status		= PAYMENT_WAITING;
			$oPayment->PaymentType	= PAYMENT_TYPE_CREDIT_CARD;
			$oPayment->Payment		= '';
			$oPayment->save();
			
			if ($fSurcharge != 0)
			{
				// Create a charge for the transaction surcharge
				$oCharge					= new Charge();
				$oCharge->AccountGroup		= $oAccount->AccountGroup;
				$oCharge->Account			= $iAccountId;
				$oCharge->CreatedBy			= $iEmployeeId;
				$oCharge->Amount			= RemoveGST($fSurcharge);
				$oCharge->CreatedOn			= $sNowDate;
				$oCharge->ChargedOn			= $sNowDate;
				$oCharge->Status			= CHARGE_APPROVED;
				$oCharge->LinkType			= CHARGE_LINK_PAYMENT;
				$oCharge->LinkId			= $oPayment->Id;
				$oCharge->ChargeType		= 'CCS';
				$oCharge->Nature			= 'DR';
				$oCharge->global_tax_exempt	= 0;
				$oCharge->Description		= ($oCardType->name.' Surcharge for Payment on '.date('d/m/Y', $iTime).' ('.$fTotal.') @ '.(round(floatval($oCardType->surcharge) * 100, 2)).'%');
				$oCharge->charge_model_id	= CHARGE_MODEL_CHARGE;
				$oCharge->Notes				= '';
				$oCharge->save();
			}
			
			// Create a credit_card_payment_history record
			$oCreditCardPaymentHistory						= new Credit_Card_Payment_History();
			$oCreditCardPaymentHistory->account_id 			= $iAccountId;
			$oCreditCardPaymentHistory->employee_id 		= $iEmployeeId;
			$oCreditCardPaymentHistory->contact_id 			= $iContactId;
			$oCreditCardPaymentHistory->receipt_number 		= $sPurchaseOrderNo;
			$oCreditCardPaymentHistory->amount 				= $fTotal;
			$oCreditCardPaymentHistory->payment_datetime 	= date('Y-m-d H:i:s', $iTime);
			$oCreditCardPaymentHistory->payment_id			= $oPayment->Id;
			$oCreditCardPaymentHistory->save();
			
			// Link payment_request to payment
			$oPaymentRequest->payment_request_status_id	= PAYMENT_REQUEST_STATUS_DISPATCHED;
			$oPaymentRequest->payment_id				= $oPayment->Id;
			$oPaymentRequest->save();
			
			// Make the secure pay request
			$sTransactionId	= self::_securePayRequest($sMerchantId, $sPassword, $iTime, $sMessageId, $fTotal, $sPurchaseOrderNo, $sCardNumber, $iCVV, $iMonth, $iYear);
			
			// Set the Payments transaction reference
			$oPayment->TXNReference	= $sTransactionId;
			$oPayment->save();
			
			// Set the credit card payment history transaction reference
			$oCreditCardPaymentHistory->txn_id	= $sTransactionId;
			$oCreditCardPaymentHistory->save();
			
			// Cache transaction id in details
			$oTransactionDetails->sTransactionId	= $sTransactionId;
			
			// Commit transaction
			$oDataAccess->TransactionCommit();
		}
		catch (Exception $oException)
		{
			// Rollback transaction
			$oDataAccess->TransactionRollback();
			
			// Cancel the payment request
			Log::getLog()->log("Error in transaction, setting status of payment_request {$oPaymentRequest->id} to cancelled (".PAYMENT_REQUEST_STATUS_CANCELLED.")");
			$oPaymentRequest->payment_request_status_id	= PAYMENT_REQUEST_STATUS_CANCELLED;
			$oPaymentRequest->payment_id				= null;
			$oPaymentRequest->save();
			
			throw $oException;
		}
		
		return $oTransactionDetails;
	}

	private static function _securePayRequest($sMerchantId, $sPassword, $iTime, $sMessageId, $fTotal, $sPurchaseOrderNo, $sCardNumber, $iCVV, $iMonth, $iYear)
	{
		// Send request
		$sXmlmessage	= self::setPaymentCreditCard($sMerchantId, $sPassword, $iTime, $sMessageId, $fTotal, $sPurchaseOrderNo, $sCardNumber, $iCVV, $iMonth, $iYear);
		$sHost 			= self::getHost();
		
		Log::getLog()->log("Sending to host: {$sHost}");
		
		$sResponse 		= self::openSocket($sHost, $sXmlmessage);
		
		Log::getLog()->log("Got response: {$sResponse}");
		
		// Need to check the XML response is valid and that the status code (SecurePayMessage/Status/statusCode) == "000".
		$aMatches 		= array();
		$sStatusCode 	= '999';
		
		// Get actual status code of response
		if (preg_match("/\<statusCode(?:| [^\>]*)\>([^\>]*)\</i", $sResponse, $aMatches))
		{
			$sStatusCode	= $aMatches[1];
		}
		
		Log::getLog()->log("Checking status code...");
		
		// If not, we should throw a Credit_Card_Payment_Remote_Processing_Error exception
		if ($sStatusCode !== '000')
		{
			Log::getLog()->log("... status code <> 000");
			throw new Credit_Card_Payment_Remote_Processing_Error($sStatusCode);
		}

		// Need to check the XML payment response is ok and that the response code (SecurePayMessage/Payment/TxnList/Txn/responseCode) == "00".
		$sResponseCode	= 'xx';
		$sResponseText	= '';

		// Get the actual response code from the response
		if (preg_match("/\<responseCode(?:| [^\>]*)\>([^\>]*)\</i", $sResponse, $aMatches))
		{
			$sResponseCode	= $aMatches[1];
		}
		// Get the actual response text from the response
		if (preg_match("/\<responseText(?:| [^\>]*)\>([^\>]*)\</i", $sResponse, $aMatches))
		{
			$sResponseText	= $aMatches[1];
		}

		Log::getLog()->log("Checking approval...");

		// Check to see if the transaction was approved
		// This should be determined from the SecurePayMessage/Payment/TxnList/Txn/approved element in the XML (always 'Yes' or 'No'))
		$sApproved	= preg_match("/\<approved(?:| [^\>]*)\>Yes\</i", $sResponse);
		// If not approved, we should return a message containing the response text (SecurePayMessage/Payment/TxnList/Txn/responseText)
		if (!$sApproved)
		{
			Log::getLog()->log("... not approved => '{$sResponseText}'");
			throw new Credit_Card_Payment_Validation_Exception($sResponseCode, $sResponseText);
		}

		// Find the TXN Id for the payment
		$sTxnId	= '';
		if (preg_match("/\<txnID(?:| [^\>]*)\>([0-9]+)\<\/txnID\>/i", $sResponse, $aMatches))
		{
			$sTxnId	= $aMatches[1];
		}
		
		Log::getLog()->log("Transaction ID: {$sTxnId}");
		
		return $sTxnId;
	}

	// Should probably detect this automatically and use the primary contact details
	// when employee is logged in or the contact details if in customer interface
	private static function getJavaScriptActionParams($accountId)
	{
		// Check that the module is enabled
		if (!Flex_Module::isActive(FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS))
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

		$contactName = str_replace("'", "\\'", str_replace("\\", "\\\\", $contact->getName()));
		$contactEmail = str_replace("'", "\\'", str_replace("\\", "\\\\", $contact->email));

		$amountOwing = $account->getBalance();
		$accountName = str_replace("'", "\\'", str_replace("\\", "\\\\", $account->getName()));

		// Output a link for making a credit card payment, using the credit_card_payment.js file.
		$allowDD = Flex::isCustomerSession() ? 'true' : 'false';
		return array("{$account->id}, '{$account->abn}', '$accountName', '$contactName', '$contactEmail', $amountOwing, $allowDD", $creditCardPaymentConfig);
	}

}

class Credit_Card_Payment_Exception extends Exception {};

class Credit_Card_Payment_Incorrect_Password_Exception extends Exception
{
	function __construct()
	{
		parent::__construct("The customer password specified was incorrect.");
	}
}

class Credit_Card_Payment_Communication_Exception extends Exception
{
}

class Credit_Card_Payment_Communication_Response_Exception extends Credit_Card_Payment_Communication_Exception
{
}

class Credit_Card_Payment_Flex_Logging_Exception extends Exception
{
	
}

class Credit_Card_Payment_Remote_Processing_Error extends Exception
{
	private $statusCode;

	function __construct($statusCode)
	{
		$this->statusCode = $statusCode;
		parent::__construct($this->_getMessage());
	}

	static function getKnownErrors()
	{
		static $knownErrors;
		if (!isset($knownErrors))
		{
			$knownErrors = array(
				"000" => array("Normal", "Message processed correctly (check transaction response for details)."),
				"504" => array("Invalid Merchant ID", "Merchant ID does not follow the format XXXDDDD, where X is a letter and D is a digit, or Merchant ID is not found in SecurePay's database."),
				"505" => array("Invalid URL", "The URL passed to either Echo, Query, or Payment object is invalid."),
				"510" => array("Unable To Connect To Server", "Produced by SecurePay Client API when unable to establish connection to SecurePay Payment Gateway."),
				"511" => array("Server Connection Aborted During Transaction", "Produced by SecurePay Client API when connection to SecurePay Payment Gateway is lost after the payment transaction has been sent."),
				"512" => array("Transaction timed out By Client", "Produced by SecurePay Client API when no response to payment transaction has been received from SecurePay Payment Gateway within predefined time period (default 80 seconds)."),
				"513" => array("General Database Error", "Unable to read information from the database."),
				"514" => array("Error loading properties file", "Payment Gateway encountered an error while loading configuration information for this transaction."),
				"515" => array("Fatal Unknown Error", "Transaction could not be processed by the Payment Gateway due to unknown reasons."),
				"516" => array("Request type unavailable", "SecurePay system doesn't support the requested transaction type."),
				"517" => array("Message Format Error", "SecurePay Payment Gateway couldn't correctly interpret the transaction message sent."),
				"524" => array("Response not received", "The client could not receive a response from the server."),
				"545" => array("System maintenance in progress", "The system maintenance is in progress and the system is currently unable to process transactions."),
				"550" => array("Invalid password", "The merchant has attempted to process a request with an invalid password."),
				"575" => array("Not implemented", "This functionality has not yet been implemented."),
				"577" => array("Too Many Records for Processing", "The maximum number of allowed events in a single message has been exceeded."),
				"580" => array("Process method has not been called", "The process() method on either Echo, Payment or Query object has not been called."),
				"595" => array("Merchant Disabled", "SecurePay has disabled the merchant and the requests from this merchant will not be processed."),
			);
		}
		return $knownErrors;
	}

	private function _getMessage()
	{
		$knownErrors = self::getKnownErrors();
		if (!array_key_exists($this->statusCode, $knownErrors))
		{
			return "SecurePay failed to process the request and were unable to identify the problem.";
		}
		return $knownErrors[$this->statusCode][1];
	}

	function getSubject()
	{
		$knownErrors = self::getKnownErrors();
		if (!array_key_exists($this->statusCode, $knownErrors))
		{
			return "Problem Unknown";
		}
		return $knownErrors[$this->statusCode][0];
	}

	function getStatusCode()
	{
		return $this->statusCode;
	}
}


class Credit_Card_Payment_Validation_Exception extends Exception
{
	private $statusCode;

	function __construct($statusCode, $strMessage)
	{
		$this->statusCode = $statusCode;
		parent::__construct($this->_getMessage($strMessage));
	}

	static function getKnownErrors()
	{
		static $knownErrors;
		
		return array();
		
		/* These 'Known errors' are incorrect.
		if (!isset($knownErrors))
		{
			$knownErrors = array(
				"01" => "The Credit Card number does not match the Card Type.",
				"02" => "The expiry date Month entered for the credit card is invalid.",
				"03" => "The expiry date year entered for the credit card is invalid.",
				"04" => "The expiry date entered for the credit card is invalid.",
				"05" => "The credit card number entered is invalid.",
				"06" => "The credit card number entered appears to be for a type of credit card that we do not accept.",
				"07" => "The credit card number entered appears to be for a type of credit card that we do not accept.",
				"08" => "We cannot accept your card as it is blacklisted, if you feel you have received this message in error please contact your card issuer.",
				"09" => "You have not entered the correct amount of digits for the credit card number.",
				"10" => "The CVV number entered is the incorrect length.",
			);
		}
		return $knownErrors;
		*/
	}

	private function _getMessage($strMessage)
	{
		$knownErrors = self::getKnownErrors();
		if (!array_key_exists($this->statusCode, $knownErrors))
		{
			return "SecurePay failed to process the request and provided the following details: $strMessage ({$this->statusCode}).";
		}
		return $knownErrors[$this->statusCode];
	}

	function getStatusCode()
	{
		return $this->statusCode;
	}
}
?>
