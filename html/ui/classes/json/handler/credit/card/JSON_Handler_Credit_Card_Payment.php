<?php

class JSON_Handler_Credit_Card_Payment extends JSON_Handler
{

	public function makePayment($intAccountNumber, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $bolDD)
	{
		$response = array();
		
		try 
		{
			$message = Credit_Card_Payment::makePayment($intAccountNumber, $strEmail, $intCardType, $strCardNumber, $intCVV, $intMonth, $intYear, $strName, $fltAmount, $bolDD, $response);
			$response['OUTCOME'] = 'SUCCESS';
		}
		catch (Credit_Card_Payment_Not_Enabled_Exception $e)
		{
			throw $e;
		}
		catch (Credit_Card_Payment_Not_Configurred_Exception $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			//var_dump($x=$e->getTrace());
			throw $e;
		}

		return $response;
	}

}

?>
