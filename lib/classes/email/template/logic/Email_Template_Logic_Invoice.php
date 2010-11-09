<?php
class Email_Template_Logic_Invoice extends Email_Template_Logic
{


	protected static $_aVariables = array(
									'CustomerGroup'	=>	array('external_name'=>"",
															 'customer_service_phone'=>"",
																'email_domain'=> ""),
									'Account'		=>	array('id'=>'1234567890'),
									'Invoice'		=>	array('created_on'=>"",
															  'billing_period'=>"the current billing period"),
									'Contact'		=>	array('first_name'=>"Bob")
										);
	static function getVariables()
	{
		return self::$_aVariables;
	}
}
?>