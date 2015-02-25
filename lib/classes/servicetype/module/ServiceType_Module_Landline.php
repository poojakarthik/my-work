<?php
class ServiceType_Module_Landline extends ServiceType_Module {
	public function createNew(Service $service, stdClass $servicePropertyData) {
		$service->FNN = $servicePropertyData->fnn;
		$service->Indial100 = !!$servicePropertyData->is_indial100;

		if ($this->isFNNInUse($service->FNN)) {
			throw new Exception(sprintf('%s FNN %s is already in use', $this->name, $service->FNN));
		}

		$serviceProperties = new Service_Address(array(
			'Service' => $service->Id,

		));

		return $service;
	}

	public function getCurrentInstanceForFNN($fnn, $effectiveDatetime=null) {
		if ($effectiveDatetime === null) {
			$effectiveDatetime = DataAccess::get()->getNow();
		}

		$result = DataAccess::get()->query("
			SELECT s.Id
			FROM Service s
			WHERE (
					s.FNN = <fnn>
					OR (
						s.Indial100 = 1
						AND <fnn> LIKE CONCAT(SUBSTRING(s.FNN, 1, LENGTH(s.FNN) - 2), '__')
					)
				)
				AND s.ServiceType = <service_type_id>
				AND <effective_datetime> BETWEEN s.CreatedOn AND COALESCE(s.ClosedOn, '9999-12-31 23:59:59')
		", array(
			'service_type_id' => $this->id,
			'fnn' => $fnn,
			'effective_datetime' => $effectiveDatetime
		));
		if ($result->num_rows > 1) {
			throw new ServiceType_Module_Landline_Exception_FNNMultipleInstances(sprintf('There are multiple "current" instances of Service Type #%d: "%s" with FNN: "%s" at %s',
				$this->id,
				$this->name,
				$fnn,
				$effectiveDatetime
			));
		}
		if ($service = $result->fetch_object('Service')) {
			return $service;
		}
		throw new ServiceType_Module_Landline_Exception_FNNNoInstances(sprintf('There are no "current" instances of Service Type #%d: "%s" with FNN: "%s" at %s',
			$this->id,
			$this->name,
			$fnn,
			$effectiveDatetime
		));
	}

	public function isFNNInUse($fnn, $effectiveDatetime=null) {
		try {
			if ($this->getCurrentInstanceForFNN()) {
				return true;
			}
		} catch (ServiceType_Module_Landline_Exception_FNNMultipleInstances $exception) {
			return true;
		}
		return false;
	}
}

class ServiceType_Module_Landline_Exception_FNNNoInstances {}
class ServiceType_Module_Landline_Exception_FNNMultipleInstances {}