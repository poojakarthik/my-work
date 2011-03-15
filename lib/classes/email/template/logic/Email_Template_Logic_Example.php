<?php
class Email_Template_Logic_Example extends Email_Template_Logic
{
	protected static $_aVariables	= 	array(
											'CustomerGroup'	=>	array(
																	'external_name'				=>"",
																	'customer_service_phone'	=>"",
																	'email_domain'				=> ""
																),
											'Account'		=>	array('id' => ""),
											'Contact'		=>	array('first_name' =>"")
										);
	public function getVariables()
	{
		return self::$_aVariables;
	}

	static function getVariableData($iContactId)
	{
		try
		{
			$aData 			= self::$_aVariables;
			$oContact 		= Contact::getForId($iContactId);
			$oAccount		= Account::getForId($oContact->Account);
			$oCustomerGroup	= Customer_Group::getForId($oAccount->CustomerGroup);

			$aData['CustomerGroup']['external_name']			= trim($oCustomerGroup->external_name);
			$aData['CustomerGroup']['customer_service_phone'] 	= trim($oCustomerGroup->customer_service_phone);
			$aData['CustomerGroup']['email_domain'] 			= trim($oCustomerGroup->email_domain);
			$aData['Contact']['first_name'] 					= self::normalizeWhiteSpaces(trim($oContact->FirstName));
			$aData['Account']['id'] 							= trim($oContact->Account);
			
			return $aData;
		}
		catch (Exception $e)
		{
			throw new Exception ("Error retrieving email variable data. ".$e->getMessage());
		}
	}

	public function generateEmail($aDataParameters, Email_Flex $mEmail=null)
	{
		$aData	= $this->getVariableData($aDataParameters['contact_id']);
		return $this->generateEmailFromVariableData($aData, $mEmail);
	}
		
	public function getSampleData()
	{
		$aSampleInvoiceData	= Invoice::getSampleDataForCustomerGroupId($this->_oEmailTemplate->customer_group_id);
		return $this->getVariableData($aSampleInvoiceData['contact_id']);
	}
}
?>