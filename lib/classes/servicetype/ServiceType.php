<?php
class ServiceType {
	protected $_orm;

	public function __construct(Service_Type $serviceTypeORM) {
		$this->_orm = $serviceTypeORM;
	}

	public static function getPropertySet() {
		return array();
	}

	public static function makeClassName($module) {
		return 'ServiceType_' . $module;
	}
}