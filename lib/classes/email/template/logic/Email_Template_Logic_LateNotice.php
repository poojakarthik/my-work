<?php
class Email_Template_Logic_LateNotice extends Email_Template_Logic
{

	protected static $_aVariables	=	array(
								'CustomerGroup'	=> array('external_name'=>"",
														  'email_domain'=> ""),
								'Contact'		=> array('first_name'=>'Bob'),
								'Account'		=> array('id'=>'1234567890')
							);

	static function getVariables()
	{
		return self::$_aVariables;
	}

	static function getData($iAccountId)
	{
		$oAccount = Account::getForId($iAccountId);
		$oPrimaryContact = Contact::getForId($oAccount->PrimaryContact);
		$oCustomerGroup = Customer_Group::getForId($oAccount->CustomerGroup);
		$aData = self::$_aVariables;
		$aData['CustomerGroup']['external_name'] = trim($oCustomerGroup->external_name);
		$aData['CustomerGroup']['email_domain'] = trim($oCustomerGroup->email_domain);
		$aData['Contact']['first_name'] = self::normalizeWhiteSpaces(trim($oPrimaryContact->FirstName));
		$aData['Account']['id'] = trim($iAccountId);
		return $aData;
	}

	function generateEmail($aDataParameters, Email_Flex $mEmail=null)
	{
		$aData = $this->getData($aDataParameters['account_id']);
		return parent::generateEmail($aData, $mEmail);
	}

}
?>