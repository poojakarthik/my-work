<?php
class Exception_ORM_LoadById extends Exception_ORM
{
	public function __construct($sTable, $iId, $sDetails='')
	{
		parent::__construct("Unable to autoload ORM Object '{$sTable}' with Id '{$iId}' ".(($sDetails) ? "({$sDetails})" : ''));
	}
}
?>