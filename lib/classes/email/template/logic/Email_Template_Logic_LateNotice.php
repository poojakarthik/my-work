<?php
class Email_Template_Logic_LateNotice extends Email_Template_Logic
{

	protected static $_aVariables	=	array(
								'CustomerGroup'	=> array('external_name'=>"",
														  'email_domain'=> ""),
								'Contact'		=> array('first_name'=>'Bob'),
								'Account'		=> array('id'=>'1234567890'),
								'Letter'		=> array('type'=>'')
							);

	static function getVariables()
	{
		return self::$_aVariables;
	}


}
?>