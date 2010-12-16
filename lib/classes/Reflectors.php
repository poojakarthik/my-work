<?php
class Reflectors
{
	static protected	$_aClasses	= array();
	
	public static function getClass($sClass)
	{
		if (!isset(self::$_aClasses[$sClass]))
		{
			self::$_aClasses[$sClass]	= new ReflectionClass($sClass);
		}
		return self::$_aClasses[$sClass];
	}
}
?>