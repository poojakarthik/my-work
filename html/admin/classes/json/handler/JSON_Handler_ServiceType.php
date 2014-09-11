<?php
class JSON_Handler_ServiceType extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		return array(
			'serviceTypes' => array_map('ORM::mapToArray', Service_Type::getAll())
		);
	}
}