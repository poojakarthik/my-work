<?php
interface Carrier_Module_Interface
{
	/**
	 * getConfigDefinition()
	 *
	 * Retrieves the definition of this module's Configuration in the form of a
	 * 2-dimensional associative array
	 *
	 * @return	array
	 */
	public static function getConfigDefinition();
}
?>