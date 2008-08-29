<?php

class JSON_Handler_Credit_Card_Payment extends JSON_Handler
{

	public function makePayment($intAccountNumber, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $fltSurcharge, $fltTotal, $bolDD)
	{
		$response = array();
		
		try 
		{
			$response['OUTCOME'] = 'FAILED';
			$response['MESSAGE'] = 'Unknown';
			if (Credit_Card_Payment::makePayment($intAccountNumber, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $fltSurcharge, $fltTotal, $bolDD, $response))
			{
				$response['OUTCOME'] = 'SUCCESS';
			}
			else
			{
				$response['OUTCOME'] = 'INVALID';
			}
		}
		catch (Credit_Card_Payment_Not_Enabled_Exception $e)
		{
			// Should send an email to alert us to the fact that payment was attempted for a non-configgurred account - there is likely a bug in the UI!!
			throw $e;
		}
		catch (Credit_Card_Payment_Not_Configurred_Exception $e)
		{
			// Should send an email to alert us to the fact that payments are failing - there is likely a bug in the UI!!
			throw $e;
		}
		catch (Credit_Card_Payment_Communication_Exception $e)
		{
			// Could possibly send an email to alert us to the fact that payments are failing, although this is likely to be temporary.
			// Maybe only do this when running in test mode?
			$response['OUTCOME'] = 'UNAVAILABLE';
			$response['MESSAGE'] = 'We were unable to connect to SecurePay to process the payment.';
			if (Credit_Card_Payment::isTestMode()) $response['MESSAGE'] = $e->getMessage();
		}
		catch (Credit_Card_Payment_Remote_Processing_Error $e)
		{
			// Should probably send an email to alert us to the fact that payments are failing!
			$response['OUTCOME'] = 'FAILED';
			$response['MESSAGE'] = 'SecurePay was unable to process the payment request.';
			if (Credit_Card_Payment::isTestMode()) $response['MESSAGE'] = $e->getMessage();
		}
		catch (Credit_Card_Payment_Validation_Exception $e)
		{
			$response['OUTCOME'] = 'INVALID';
			$response['MESSAGE'] = $e->getMessage();
		}
		catch (Exception $e)
		{
			// This is likely to be a user data validation error. Should not throw the exception.
			$response['OUTCOME'] = 'INVALID';
			$response['MESSAGE'] = $e->getMessage();
		}

		return $response;
	}

}

?>
