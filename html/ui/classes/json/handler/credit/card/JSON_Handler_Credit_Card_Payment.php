<?php

class JSON_Handler_Credit_Card_Payment extends JSON_Handler
{

	public function makePayment($intAccountNumber, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $fltSurcharge, $fltTotal, $bolDD, $strPassword)
	{
		$response 	= array();
		$bTestMode	= Credit_Card_Payment::isTestMode();
		try 
		{
			$response['OUTCOME']	= 'FAILED';
			$response['MESSAGE']	= 'Unknown';
			
			// Check that the requested account is in the authenticated customers account group
			$oAccount		= Account::getForId($intAccountNumber);
			$oContact		= Contact::getForId(Flex::getUserId());
			$iEmployeeId	= Employee::SYSTEM_EMPLOYEE_ID;
			
			if (!$oContact || !$oContact->canAccessAccount($oAccount))
			{
				throw new Exception("Invalid user account selected for credit card payment.");
			}

			// Check that the customer provided a valid password
			if ($bolDD && !$oContact->passwordIsValid($strPassword))
			{
				throw new Credit_Card_Payment_Incorrect_Password_Exception();
			}
			
			$oTransactionDetails	=	Credit_Card_Payment::makeCreditCardPayment(
											$intAccountNumber, 
											$oContact->id, 
											$iEmployeeId, 
											$intCardType, 
											$strCardNumber, 
											$intCVV, 
											$intMonth, 
											$intYear, 
											$strName, 
											$fltAmount, 
											$strEmail,
											$bolDD
										);
			
			$response['OUTCOME']	= 'SUCCESS';
			$response['MESSAGE']	= 'Thank you for your payment.';
			
			if ($bolDD && Flex::isCustomerSession())
			{
				// Send the direct debit confirmation email
				$oAccount					= Account::getForId($oTransactionDetails->iAccountId);
				$oCustomerGroup				= Customer_Group::getForId($oAccount->CustomerGroup);
				$oCreditCardPaymentConfig 	= Credit_Card_Payment_Config::getForCustomerGroup($oAccount->CustomerGroup);
				
				$aMessageTokens		= Credit_Card_Payment::buildMessageTokens($oTransactionDetails, $oContact->getName(), $strEmail);
				$oEmail 			= new Email_Notification(EMAIL_NOTIFICATION_PAYMENT_CONFIRMATION, $oAccount->customerGroup);
				$oEmail->subject	= "{$oCustomerGroup->name} Direct Debit Setup Confirmation";
				$oEmail->text 		= Credit_Card_Payment::replaceMessageTokens($oCreditCardPaymentConfig->directDebitEmail, $aMessageTokens);
				$oEmail->to 		= ($bTestMode ? 'ybs-admin@ybs.net.au' : $strEmail);
				$oEmail->send();
			}
		}
		catch (Credit_Card_Payment_Incorrect_Password_Exception $e)
		{
			$response['OUTCOME'] = 'PASSWORD';
		}
		catch (Credit_Card_Payment_Communication_Response_Exception $e)
		{
			// Could possibly send an email to alert us to the fact that payments are failing, although this is likely to be temporary.
			// Maybe only do this when running in test mode?
			$response['OUTCOME'] = 'UNAVAILABLE';
			$response['MESSAGE'] = 'We were unable to read the response from SecurePay so we do not know whether the payment succeeded or failed. Please do not retry payment at this time.';
			if ($bTestMode)
			{
				$response['MESSAGE'] = $e->getMessage();
			}
		}
		catch (Credit_Card_Payment_Communication_Exception $e)
		{
			// Could possibly send an email to alert us to the fact that payments are failing, although this is likely to be temporary.
			// Maybe only do this when running in test mode?
			$response['OUTCOME'] = 'UNAVAILABLE';
			$response['MESSAGE'] = 'We were unable to connect to SecurePay to process the payment.';
			if ($bTestMode)
			{
				$response['MESSAGE'] = $e->getMessage();
			}
		}
		catch (Credit_Card_Payment_Remote_Processing_Error $e)
		{
			// Should probably send an email to alert us to the fact that payments are failing!
			$response['OUTCOME'] = 'FAILED';
			$response['MESSAGE'] = 'SecurePay was unable to process the payment request.';
			if ($bTestMode) 
			{
				$response['MESSAGE'] = $e->getMessage();
			}
		}
		catch (Credit_Card_Payment_Validation_Exception $e)
		{
			$response['OUTCOME'] = 'INVALID';
			$response['MESSAGE'] = $e->getMessage();
		}
		catch (Credit_Card_Payment_Flex_Logging_Exception $e)
		{
			$response['OUTCOME'] = 'FLEX_LOGGING_FAILURE';
			$response['MESSAGE'] = $e->getMessage();
		}
		catch (Exception_Assertion $e)
		{
			// Assertions should be handled at a much higher level than this
			throw $e;
		}
		catch (Exception $e)
		{
			// This is likely to be a user data validation error. Should not throw the exception.
			$response['OUTCOME'] = 'INVALID';
			$response['MESSAGE'] = $e->getMessage();
		}

		// If an exception was thrown and caught, email the details to ybs
		if (isset($e))
		{
			$arrCustomerDetails =	array(
										"AccountId"			=> $intAccountNumber,
										"Email"				=> $strEmail,
										"CreditCardNumber"	=> substr($strCardNumber, 0, 3) ."***". substr($strCardNumber, -5),
										"Name"				=> $strName,
										"Amount"			=> $fltAmount,
										"Surcharge"			=> $fltSurcharge,
										"TotalCharged"		=> $fltTotal
									);
			$strCustomerDetails		= print_r($arrCustomerDetails, TRUE);
			$strMessageSentToUser	= $response['MESSAGE'];
			$strExceptionMessage	= $e->getMessage();
			
			$strDetails = "SecurePay Credit Card transaction failed via the ". (Flex::isAdminSession()? "Flex Customer Management System" : "Flex Customer Portal") .".

Exception Message:
	$strExceptionMessage

Message sent to User:
	$strMessageSentToUser

CustomerDetails:
$strCustomerDetails\n\n";
			
			Flex::sendEmailNotificationAlert("SecurePay Transaction Failure", $strDetails, FALSE, TRUE, TRUE);
		}

		return $response;
	}

}

?>
