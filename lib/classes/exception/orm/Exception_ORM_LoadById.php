<?php
class Exception_ORM_LoadById extends Exception
{
	public function __construct($strTableName, $mixId)
	{
		parent::__construct("Unable to autoload ORM Object '{$strTableName}' with Id '{$mixId}'");
	}
}
?>