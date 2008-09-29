<?php

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
		return (!defined('CREDIT_CARD_PAYMENT_TEST_MODE') || CREDIT_CARD_PAYMENT_TEST_MODE !== FALSE);
	}

	public static function makePayment($intAccountId, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $fltSurcharge, $fltTotal, $bolDD, $strPassword, &$resultProperties)
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
			$employeeId = Flex::getUserId();
			$contact = Contact::getForId($account->primaryContact);
			if (!$contact)
			{
				throw new Exception("Failed to load primary contact details for the account.");
			}
		}
		else if (Flex::isCustomerSession())
		{
			// Check that the requested account is in the authenticated customers account group
			$contact = Contact::getForId(Flex::getUserId());
			$employeeId = USER_ID;
			if (!$contact || !$contact->canAccessAccount($account))
			{
				throw new Exception("Invalid user account selected for credit card payment.");
			}

			// Check that the customer provided a valid password
			if ($bolDD && !$contact->passwordIsValid($strPassword))
			{
				throw new Credit_Card_Payment_Incorrect_Password_Exception();
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
		if ($fltAmount[0] == '.') $fltAmount = '0' . $fltAmount;
		$amount = floatVal($fltAmount);
		if (!$fltAmount || strpos($fltAmount, '-') !== FALSE || !preg_match("/^(0|[1-9]+[0-9]*)(|\.[0-9]*)$/", $fltAmount))
		{
			throw new Exception('Invalid amount specified.');
		}
		$fltAmount = self::amount2dp($fltAmount);

		// Check that the amount has been specified and that is is a positive amount between the min and max permitted for the card type.
		$fltSurcharge = preg_replace(array("/^0+/", "/[^0-9\.\-]+/"), "", '0'.$fltSurcharge);
		if ($fltSurcharge[0] == '.') $fltSurcharge = '0' . $fltSurcharge;
		$surcharge = floatVal($fltSurcharge);
		if (!$fltSurcharge || strpos($fltSurcharge, '-') !== FALSE || !preg_match("/^(0|[1-9]+[0-9]*)(|\.[0-9]*)$/", $fltSurcharge))
		{
			throw new Exception('Invalid surcharge specified.'.$fltSurcharge);
		}
		$fltSurcharge = self::amount2dp($fltSurcharge);

		// Check that the amount has been specified and that is is a positive amount between the min and max permitted for the card type.
		$fltTotal = preg_replace(array("/^0+/", "/[^0-9\.\-]+/"), "", '0'.$fltTotal);
		if ($fltTotal[0] == '.') $fltTotal = '0' . $fltTotal;
		$total = floatVal($fltTotal);
		if (!$fltTotal || strpos($fltTotal, '-') !== FALSE || $total < $cardType->minimumAmount || $total > $cardType->maximumAmount || !preg_match("/^(0|[1-9]+[0-9]*)(|\.[0-9]*)$/", $fltTotal))
		{
			throw new Exception('Invalid total specified.');
		}
		$fltTotal = self::amount2dp($fltTotal);

		$cAmount = intval(self::amountInCents($fltAmount));
		$cSurcharge = intval(self::amountInCents($fltSurcharge));
		$cTotal = intval(self::amountInCents($fltTotal));

		// Check that the amount + surcharge comes to total and that the surcharge is right for the credit card type.
		if (abs($cTotal - ($cSurcharge + $cAmount)) > 0)
		{
			throw new Exception('The amounts specified do not add up.');
		}

		// Complain if there is anything other than a truly trivial difference in surcharge from that which is expected
		$calculatedSurcharge = $cardType->calculateSurcharge($amount);
		if (abs($calculatedSurcharge - $surcharge) >= 0.005)
		{
			throw new Exception('The surcharge specified is incorrect.'.$surcharge. ' : ' . $calculatedSurcharge . ' : ' . abs($calculatedSurcharge - $surcharge));
		}

		// OK. That's everything validated. Now we can start talking to SecurePay...
		// $account, $strEmail, $cardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $bolDD

		$time = time();

		$messageId = substr($account->id . '.' . base64_encode($time), 0, 30);

		$purchaseOrderNo = $account->id . '.' . $time;

		$xmlmessage = self::setPaymentCreditCard($creditCardPaymentConfig->merchantId, $creditCardPaymentConfig->password, $time, $messageId, $fltTotal, $purchaseOrderNo, $strCardNumber, $strCVV, $intMonth, $intYear);

		$host = self::getHost();

		$response = self::openSocket($host, $xmlmessage);

		//throw new Exception("\n\n\n\n\n\n\n\n" . $xmlmessage . "\n\n\n\n\n\n\n\n" . $response . "\n\n\n\n\n\n\n");


		// Need to check the XML response is valid and that the status code (SecurePayMessage/Status/statusCode) == "000".
		$matches = array();
		$statusCode = '999';
		// Get actual status code of response
		if (preg_match("/\<statusCode(?:| [^\>]*)\>([^\>]*)\</i", $response, $matches))
		{
			$statusCode = $matches[1];
		}
		// If not, we should throw a Credit_Card_Payment_Remote_Processing_Error exception
		if ($statusCode !== '000')
		{
			throw new Credit_Card_Payment_Remote_Processing_Error($statusCode);
		}

		// Need to check the XML payment response is ok and that the response code (SecurePayMessage/Payment/TxnList/Txn/responseCode) == "00".
		$responseCode = 'xx';
		$responseText = '';

		// Get the actual response code from the response
		if (preg_match("/\<responseCode(?:| [^\>]*)\>([^\>]*)\</i", $response, $matches))
		{
			$responseCode = $matches[1];
		}
		// Get the actual response code from the response
		if (preg_match("/\<responseText(?:| [^\>]*)\>([^\>]*)\</i", $response, $matches))
		{
			$responseText = $matches[1];
		}

		// Check to see if the transaction was approved
		// This should be determined from the SecurePayMessage/Payment/TxnList/Txn/approved element in the XML (always 'Yes' or 'No'))
		$approved = preg_match("/\<approved(?:| [^\>]*)\>Yes\</i", $response); 
		// If not approved, we should return a message containing the response text (SecurePayMessage/Payment/TxnList/Txn/responseText)
		if (!$approved)
		{
			$resultProperties['MESSAGE'] = "$responseText ($responseCode)";
			throw new Credit_Card_Payment_Validation_Exception($responseCode, $responseText);
		}

		// Find the TXN Id for the payment
		$txnId = '';
		if (preg_match("/\<txnID(?:| [^\>]*)\>([0-9]+)\<\/txnID\>/i", $response, $matches))
		{
			$txnId = $matches[1];
		}

		// Record the balance before applying the payment
		$balanceBefore = $account->getBalance();

		// Payment has been processed!
		try
		{
			// Store details of the payment in the credit_card_payment_history table
			// Apply the payment to the account
			// Add an adjustment to the account for the credit card surcharge
			TransactionStart();
			$account->applyPayment($employeeId, $contact, $time, $fltTotal, $txnId, $purchaseOrderNo, PAYMENT_TYPE_CREDIT_CARD, $strCardNumber, $cardType, $surcharge);
			TransactionCommit();
		}
		catch (Exception $e)
		{
			TransactionRollback();
			// Oh jees... payment has been processed but we can't log it???
			// WIP: Probably best send an email or something!
			throw $e;
		}

		try
		{
			$bolFailedDD = FALSE;
			// If doing DD, also need to create an entry in the credit card table
			if ($bolDD)
			{
				TransactionStart();
				$insCreditCard = new StatementInsert('CreditCard');
				$arrValues = array(
					'AccountGroup'	=> $account->accountGroup,
					'CardType'		=> $cardType->id,
					'Name'			=> $strName,
					'CardNumber'	=> Encrypt($strCardNumber),
					'ExpMonth'		=> str_pad($intMonth, 2, '0', STR_PAD_LEFT),
					'ExpYear'		=> $intYear,
					'CVV'			=> Encrypt($intCVV),
					'Archived'		=> 0,
					'created_on'	=> date('Y-m-d H:i:s', $time),
					'employee_id'	=> $employeeId,
				);
				if (($intCreditCardId = $insCreditCard->Execute($arrValues)) === FALSE)
				{
					throw new Exception('Failed to store details for direct debit: ' . $insCreditCard->Error());
				}
				$account->creditCard = $intCreditCardId;
				$account->billingType = BILLING_TYPE_CREDIT_CARD;
				$account->save($employeeId);
				TransactionCommit();
			}
		}
		catch (Exception $e)
		{
			TransactionRollback();
			$bolFailedDD = TRUE;
		}

		// Build an array of 'magic tokens' to be inserted into the message
		$tokens = array();
		$balanceBefore = self::amount2dp($balanceBefore);
		if ($balanceBefore[0] == '-') $balanceBefore = substr($balanceBefore, 1) . ' CR';
		$balanceAfter = self::amount2dp($account->getBalance());
		if ($balanceAfter[0] == '-') $balanceAfter = substr($balanceAfter, 1) . ' CR';

		$tokenList = self::listMessageTokens();
		foreach ($tokenList as $token => $description)
		{
			switch ($token)
			{
				case self::TOKEN_START . 'DATE_TIME' . self::TOKEN_END:
					$tokens[$token] = date('g:i:sA, jS M Y', $time);
					break;
				case self::TOKEN_START . 'PAYMENT_REFERENCE' . self::TOKEN_END:
					$tokens[$token] = $purchaseOrderNo;
					break;
				case self::TOKEN_START . 'AMOUNT_APPLIED' . self::TOKEN_END:
					$tokens[$token] = '$' . $fltAmount;
					break;
				case self::TOKEN_START . 'AMOUNT_SURCHARGE' . self::TOKEN_END:
					$tokens[$token] = '$' . $fltSurcharge;
					break;
				case self::TOKEN_START . 'AMOUNT_TOTAL' . self::TOKEN_END:
					$tokens[$token] = '$' . $fltTotal;
					break;
				case self::TOKEN_START . 'BALANCE_BEFORE' . self::TOKEN_END:
					$tokens[$token] = '$' . $balanceBefore;
					break;
				case self::TOKEN_START . 'BALANCE_AFTER' . self::TOKEN_END:
					$tokens[$token] = '$' . $balanceAfter;
					break;
				case self::TOKEN_START . 'ACCOUNT_NUMBER' . self::TOKEN_END:
					$tokens[$token] = $account->id;
					break;
				case self::TOKEN_START . 'CONTACT_NAME' . self::TOKEN_END:
					$tokens[$token] = $contact->getName();
					break;
				case self::TOKEN_START . 'CONTACT_EMAIL' . self::TOKEN_END:
					$tokens[$token] = $strEmail;
					break;
				case self::TOKEN_START . 'COMPANY_ABN' . self::TOKEN_END:
					$tokens[$token] = $account->abn;
					break;
				case self::TOKEN_START . 'COMPANY_NAME' . self::TOKEN_END:
					$tokens[$token] = $account->businessName ? $account->businessName : $account->tradingName;
					break;
				case self::TOKEN_START . 'CARD_NUMBER' . self::TOKEN_END:
					$tokens[$token] = (substr($strCardNumber, 0, 4) . str_repeat('.', strlen($strCardNumber) - 8) . substr($strCardNumber, -4));
					break;
			}
		}

		$bolCanSendEmail = EmailAddressValid($strEmail);
		$bolFailedToEmail = FALSE;

		if ($bolCanSendEmail && 
			(!self::isTestMode() || 
			(defined('SEND_CREDIT_CARD_EMAILS_IN_TEST_MODE') && SEND_CREDIT_CARD_EMAILS_IN_TEST_MODE === TRUE)))
		{
			$customerGroup = Customer_Group::getForId($account->customerGroup);

			try
			{
				// Send the payment confirmation email
				$emailBody = self::replaceMessageTokens($creditCardPaymentConfig->confirmationEmail, $tokens);
				$email = new Email_Notification(EMAIL_NOTIFICATION_PAYMENT_CONFIRMATION, $account->customerGroup);
				$email->subject = $customerGroup->name . " Credit Card Payment Confirmation (Ref: $purchaseOrderNo / $txnId)";
				$email->text = self::replaceMessageTokens($creditCardPaymentConfig->confirmationEmail, $tokens);
				$email->to = $strEmail;
				$email->send();

				if ($bolDD && !$bolFailedDD)
				{
					// Send the direct debit confirmation email
					$email = new Email_Notification(EMAIL_NOTIFICATION_PAYMENT_CONFIRMATION, $account->customerGroup);
					$email->subject = $customerGroup->name . " Direct Debit Setup Confirmation";
					$email->text = self::replaceMessageTokens($creditCardPaymentConfig->directDebitEmail, $tokens);
					$email->to = $strEmail;
					$email->send();
				}
			}
			catch (Exception $e)
			{
				$bolFailedToEmail = TRUE;
			}
		}

		$outputMessage = $creditCardPaymentConfig->confirmationText . 
			($bolDD 
				? ("\n\n\n" . ($bolFailedDD 
								? 'We were unable to store your details for Direct Debit at this time. Please try again later.' 
								: $creditCardPaymentConfig->directDebitText)) 
				: '');

		if (!$bolCanSendEmail)
		{
			$outputMessage .= "\n\n\nCould not send " . (($bolDD && !$bolFailedDD) ? '' : 'a ') . "confirmation email" . (($bolDD && !$bolFailedDD) ? 's' : '') . " as the email address on record is invalid.";
		}
		else if ($bolFailedToEmail)
		{
			$outputMessage .= "\n\n\nThe system failed to send " . (($bolDD && !$bolFailedDD) ? '' : 'a ') . "confirmation email" . (($bolDD && !$bolFailedDD) ? 's' : '') . ".";
		}

		$resultProperties['MESSAGE'] = self::replaceMessageTokens($outputMessage, $tokens);
		return TRUE;
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
			if (strlen($strAmountInCents) <= 2)
			{
				$strAmountInDollars = '0.'.$strAmountInCents;
			}
			else
			{
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
		$timestamp = self::getGmtTimeStamp($time);
		$expiryMonth = (intval($expiryMonth) >= 10 ? '' : '0') . intval($expiryMonth);
		$expiryYear = intval($expiryYear) % 100;
		$expiryYear = (intval($expiryYear) >= 10 ? '' : '0') . intval($expiryYear);
		$paymentAmount = self::amountInCents($paymentAmount);

		$merchantId = htmlspecialchars($merchantId);
		$merchantPassword = htmlspecialchars($merchantPassword);
		$time = htmlspecialchars($time);
		$messageId = htmlspecialchars($messageId);
		$currency = htmlspecialchars($currency);
		$paymentAmount = htmlspecialchars($paymentAmount);
		$purchaseOrderNo = htmlspecialchars($purchaseOrderNo);
		$cardNumber = htmlspecialchars($cardNumber);
		$cvv = htmlspecialchars($cvv);
		$expiryMonth = htmlspecialchars($expiryMonth);
		$expiryYear = htmlspecialchars($expiryYear);
		$requestType = htmlspecialchars($requestType);
		$paymentType = htmlspecialchars($paymentType);
		$preauthid = htmlspecialchars($preauthid);
		$txnid = htmlspecialchars($txnid);

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

	private static function replaceMessageTokens($message, $tokens)
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

class Credit_Card_Payment_Incorrect_Password_Exception extends Exception { function __construct()	{ parent::__construct("The customer password specified was incorrect."); 				} }
class Credit_Card_Payment_Not_Enabled_Exception 	extends Exception { function __construct()	{ parent::__construct("Credit Card Payments are not enabled in Flex."); 				} }
class Credit_Card_Payment_Not_Configurred_Exception	extends Exception { function __construct() 	{ parent::__construct("Credit Card Payments have not been configurred in Flex Admin."); } }
class Credit_Card_Payment_Communication_Exception	extends Exception { }
class Credit_Card_Payment_Communication_Response_Exception	extends Credit_Card_Payment_Communication_Exception { }

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
				"504" => array("Invalid Merchant ID", "If Merchant ID does not follow the format XXXDDDD, where X is a letter and D is a digit, or Merchant ID is not found in SecurePay's database."),
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
	}}
?>
