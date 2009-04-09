<?php
class Exception_Class_Static extends Exception_Class
{
	public function __construct($strClassName)
	{
		parent::__construct("Cannot initialised Static Class '{$strClassName}'!");
	}
}
?>