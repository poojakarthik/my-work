<?php
abstract class Resource_Type_File_Deliver extends Resource_Type_Base
{
	abstract public function connect();
	
	abstract public function disconnect();
	
	abstract public function deliver($sLocalPath);
}
?>