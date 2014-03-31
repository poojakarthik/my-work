<?php
class ServiceType_PropertySet {
	const DATATYPE_STRING = 'string';
	const DATATYPE_INTEGER = 'integer';
	const DATATYPE_FLOAT = 'float';
	const DATATYPE_BOOLEAN = 'boolean';
	const DATATYPE_FOREIGNKEY = 'foreign-key';
	const DATATYPE_JSON = 'json';

	private $_schema = array();
	private $_properties = array();

	private function __construct(array $schema, array $properties) {
		$this->_schema = $schema;
		foreach ($properties as $property) {
			$this->_properties[$property->name] = $property;
		}
	}

	public function __get($property) {
		if (isset($this->_properties[$property])) {
			return self::_unserialiseValue($this->_schema[$property], $this->_properties[$property]->value);
		}
		throw new DomainException('Undefined Service Property: ' . $property);
	}

	public function __set($property, $value) {
		$this->set($property, $value);
	}

	public function set($property, $value, $modifiedEmployeeId=null, $modifiedDatetime=null) {
		if (!isset($this->_properties[$property])) {
			throw new DomainException('Undefined Service Property: ' . $property);
		}
		$this->_properties[$property]->value = self::_serialiseValue($this->_schema[$property], $value);
		$this->_properties[$property]->modified_employee_id = isset($modifiedEmployeeId) ? $modifiedEmployeeId : Flex::getUser()->id;
		$this->_properties[$property]->modified_datetime = isset($modifiedDatetime) ? $modifiedDatetime : DataAccess::get()->now();
	}

	public function save() {
		foreach ($this->_properties as $property) {
			$property->save();
		}
	}

	public static function getForService($serviceId) {
		$schema = call_user_func(array(ServiceType::makeClassName(Service::getForId($serviceId)->getServiceType()->module), 'getPropertySchema'));

		// Merge defaults with current values
		$serviceProperties = Service_Property::getForService($serviceId);
		$properties = array();
		foreach ($schema as $name => $definition) {
			if ($serviceProperties[$name]) {
				$properties[$name] = $property;
			} else {
				$properties[$name] = new Service_Property(array(
					'service_id' => $serviceId,
					'name' => $name,
					'value' => self::_serialiseValue($definition, isset($definition['default']) ? $definition['default'] : null),
					'modified_employee_id' => Flex::getUser()->id,
					'modified_datetime' => DataAccess::get()->now()
				));
			}
		}

		return self::__construct($schema, $properties);
	}

	public static function create($serviceId) {
		$schema = call_user_func(array(Service::getForId($serviceId)->getCarrierModule()->Module, 'getPropertySchema'));

		// Make Properties from Schema
		$properties = array();
		foreach ($schema as $name => $definition) {
			$property = new Service_Property(array(
				'service_id' => $serviceId,
				'name' => $name,
				'value' => self::_serialiseValue($definition, isset($definition['default']) ? $definition['default'] : null),
				'modified_employee_id' => Flex::getUser()->id,
				'modified_datetime' => DataAccess::get()->now()
			));
			$properties[$name] = $property;
		}

		return self::__construct($schema, $properties);
	}

	private static function _serialiseValue($definition, $value) {
		if ($value === null) {
			return null;
		}

		switch ($definition['type']) {
			case ServiceType_PropertySet::DATATYPE_INTEGER:
				return strval((int)$value);

			case ServiceType_PropertySet::DATATYPE_FLOAT:
				return strval((float)$value);

			case ServiceType_PropertySet::DATATYPE_BOOLEAN:
				return strval((bool)$value);

			case ServiceType_PropertySet::DATATYPE_FOREIGNKEY:
				// Verify FK first
				if (!DataAccess::get()->query($definition['foreignKeyQuery'], array('value' => (int)$value))->num_rows) {
					throw new DomainException('Couldn\'t resolve foreign key ' . $definition['foreignKeyDescription'] . ': ' . $value);
				}
				return strval((int)$value);

			case ServiceType_PropertySet::DATATYPE_JSON:
				return json_encode($value);

			case ServiceType_PropertySet::DATATYPE_STRING:
			default:
				return (string)$value;
		}
	}

	private static function _unserialiseValue($definition, $value) {
		if ($value === null) {
			return null;
		}

		switch ($definition['type']) {
			case ServiceType_PropertySet::DATATYPE_INTEGER:
				return (int)$value;

			case ServiceType_PropertySet::DATATYPE_FLOAT:
				return (float)$value;

			case ServiceType_PropertySet::DATATYPE_BOOLEAN:
				return (bool)$value;

			case ServiceType_PropertySet::DATATYPE_FOREIGNKEY:
				return (int)$value;

			case ServiceType_PropertySet::DATATYPE_JSON:
				return json_decode($value, false);

			case ServiceType_PropertySet::DATATYPE_STRING:
			default:
				return (string)$value;
		}
	}
}