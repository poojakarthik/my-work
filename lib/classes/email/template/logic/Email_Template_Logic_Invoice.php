<?php
class Email_Template_Logic_Invoice extends Email_Template_Logic
{
	protected static $_aVariables	=	array(
									'CustomerGroup'	=>	array('external_name', 'customer_service_phone'),
									'Account'		=>	array('id'),
									'Invoice'		=>	array('created_on', 'billing_period'),
									'Contact'		=>	array('first_name')
								);
	static function getVariables()
	{
		return self::$_aVariables;
	}
}
?>