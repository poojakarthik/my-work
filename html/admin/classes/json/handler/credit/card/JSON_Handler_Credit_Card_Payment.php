<?php

class JSON_Handler_Credit_Card_Payment extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	function makePayment($iAccountId, $iCardType, $sCardNumber, $iCVV, $iMonth, $iYear, $sName, $fAmount, $sEmail, $bDirectDebit, $bUseCurrentPaymentMethod)
	{
		$aResponse				= array();
		$bSuccess				= false;
		$bSendErrorEmailToYBS	= false;
		$bGod					= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Get employee id and contact id for the payment
			$oAccount		= Account::getForId($iAccountId);
			$iEmployeeId	= Flex::getUserId();
			$oContact		= Contact::getForId($oAccount->primaryContact);
			if (!$oContact)
			{
				throw new Exception("Failed to load primary contact details for the account.");
			}
			
			if ($bUseCurrentPaymentMethod)
			{
				// Get the credit card details
				$oCreditCard	= $oAccount->getPaymentMethodDetails();
				$iCardType		= $oCreditCard->CardType;
				$sName			= $oCreditCard->Name;
				$sCardNumber	= Decrypt($oCreditCard->CardNumber);
				$iCVV			= Decrypt($oCreditCard->CVV);
				$iMonth			= $oCreditCard->ExpMonth;
				$iYear			= $oCreditCard->ExpYear;
			}
			
			// Attempt payment & direct debit setup (if flagged)
			$oTransactionDetails	=	Credit_Card_Payment::makeCreditCardPayment(
											$iAccountId, 
											$oContact->id, 
											$iEmployeeId, 
											$iCardType, 
											$sCardNumber, 
											$iCVV, 
											$iMonth, 
											$iYear, 
											$sName, 
											$fAmount, 
											$sEmail,
											$bDirectDebit
										);
			
			// Success
			$bSuccess	= true;
			$aResponse['oTransactionDetails']	= $oTransactionDetails;
		}
		catch (Credit_Card_Payment_Communication_Response_Exception $oException)
		{
			$aResponse['sMessage']	= 'We were unable to read the response from SecurePay so we do not know whether the payment succeeded or failed. Please do not retry payment at this time.';
			if (Credit_Card_Payment::isTestMode())
			{
				$aResponse['sMessage']	.= ' '.$oException->getMessage();
			}
			$aResponse['bInformativeError']	= true;
			$bSendErrorEmailToYBS			= true;
		}
		catch (Credit_Card_Payment_Communication_Exception $oException)
		{
			$aResponse['sMessage']	= 'We were unable to connect to SecurePay to process the payment.';
			if (Credit_Card_Payment::isTestMode())
			{
				$aResponse['sMessage']	.= ' '.$oException->getMessage();
			}
			$aResponse['bInformativeError']	= true;
			$bSendErrorEmailToYBS			= true;
		}
		catch (Credit_Card_Payment_Remote_Processing_Error $oException)
		{
			$aResponse['sMessage']	= 'SecurePay was unable to process the payment request.';
			if (Credit_Card_Payment::isTestMode())
			{
				$aResponse['sMessage']	.= ' '.$oException->getMessage();
			}
			$aResponse['bInformativeError']	= true;
		}
		catch (Credit_Card_Payment_Validation_Exception $oException)
		{
			$aResponse['sMessage']			= $oException->getMessage();
			$aResponse['bInformativeError']	= true;
		}
		catch (Credit_Card_Payment_Reversal_Exception $oException)
		{
			$sMessage = "The credit card payment was successful but Flex encountered a problem when trying to record the payment. A reversal was sent to reverse the payment";
			if ($oException->reversalFailed())
			{
				// Reversal failed as well
				$sMessage .= " however the reversal was NOT successful.";
			} 
			else 
			{
				$sMessage .= " and was successful.";
			}
			
			if (Credit_Card_Payment::isTestMode())
			{
				$sMessage .= ' '.$oException->getMessage();
			}
			$aResponse['sMessage']			= $sMessage;
			$aResponse['bInformativeError']	= true;
			$bSendErrorEmailToYBS			= true;
		}
		catch (Exception_Assertion $oException)
		{
			// Assertions should be handled at a much higher level than this
			throw $oException;
		}
		catch (Exception $oException)
		{
			// Regular exception, hide detail unless god user
			$aResponse['sMessage']			= ($bGod ? $oException->getMessage() : 'There was an error accessing the database. please contact YBS for assitance.');
			$aResponse['bInformativeError']	= false;
			$bSendErrorEmailToYBS			= true;
		}
		
		// If an exception was thrown and caught, email the details to ybs
		if (isset($oException) && $bSendErrorEmailToYBS)
		{
			$aCustomerDetails = array(
									"AccountId"			=> $iAccountId,
									"Email"				=> $sEmail,
									"CreditCardNumber"	=> substr($sCardNumber, 0, 3) ."***". substr($sCardNumber, -5),
									"Name"				=> $sName,
									"Amount"			=> $fAmount
								);
			$sCustomerDetails	= print_r($aCustomerDetails, TRUE);
			$sMessageSentToUser	= $aResponse['sMessage'];
			$sExceptionMessage	= $oException->getMessage();
			
			$sDetails	= "SecurePay Credit Card transaction failed via the Flex Customer Management System";
			$sDetails 	.= "Exception Message:\n";
			$sDetails 	.= "\t$sExceptionMessage\n\n";
			$sDetails 	.= "Message sent to User:\n";
			$sDetails 	.= "\t$sMessageSentToUser\n\n";
			$sDetails 	.= "CustomerDetails:\n";
			$sDetails 	.= "\t$sCustomerDetails\n\n";
			
			Flex::sendEmailNotificationAlert(
				(Credit_Card_Payment::isTestMode() ? '[TEST MODE] ' : '')."SecurePay Transaction Failure", 
				$sDetails, 
				FALSE, 
				TRUE, 
				TRUE
			);
		}
		
		$aResponse['bSuccess']	= $bSuccess;
		$aResponse['sDebug']	= ($bGod ? $this->_JSONDebug : '');
		return $aResponse;
	}
}

?>
