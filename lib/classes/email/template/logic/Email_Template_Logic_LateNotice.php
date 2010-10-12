<?php
class Email_Template_Logic_LateNotice extends Email_Template_Logic
{
	protected static $_aVariables	=	array(
									'CustomerGroup'	=> array('external_name'),
									'Contact'		=> array('first_name'),
									'Account'		=> array('id'),
									'Letter'		=> array('type')
								);

	static function getVariables()
	{
		return self::$_aVariables;
	}
}
?>