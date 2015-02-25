<?php
interface ServiceType_PropertySet_Interface {
	public static function getForService($serviceId);

	public function set($property, $value, $modifiedEmployeeId=null, $modifiedDatetime=null);

	public function save();
}