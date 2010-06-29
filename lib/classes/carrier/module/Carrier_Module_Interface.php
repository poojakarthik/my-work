<?php
interface Carrier_Module_Interface
{
	/**
	 * getConfigDefinition()
	 * @method
	 * @static
	 *
	 * Retrieves the definition of this module's Configuration in the form of a
	 * 2-dimensional associative array
	 *
	 * @return	array
	 */
	public static function getConfigDefinition();
	
	/**
	 * getCarrierModule()
	 * @method
	 *
	 * Retrieves the Carrier_Module ORM Object
	 *
	 * @return	Carrier_Module
	 */
	public function getCarrierModule();
}
?>