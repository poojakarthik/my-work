<?php
abstract class ServiceType_Module {
	protected $_orm;

	protected function __construct(Service_Type $serviceTypeORM) {
		$this->_orm = $serviceTypeORM;
	}

	public static function getPropertySet() {
		return array();
	}

	abstract public function createNew(Service $serviceORM, stdClass $servicePropertyData);

	public static function makeClassName($module) {
		return 'ServiceType_Module_' . $module;
	}

	public static function getForServiceType($serviceTypeId) {
		$serviceTypeORM = Service_Type::getForId($serviceTypeId);
		$moduleClass = self::makeClassName($serviceTypeORM->module);
		return new $moduleClass($serviceTypeORM);
	}

	public function __get($property) {
		return $this->_orm->$property;
	}

	public function __set($property, $value) {
		$this->_orm->$property = $value;
	}
}