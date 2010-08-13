<?php
abstract class Resource_Type_File extends Resource_Type_Base
{
	public static function getExportPath()
	{
		return FILES_BASE_PATH;
	}
}
?>