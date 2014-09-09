<?php
class JSON_Handler_Report extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getEmployees() {
		$employees = Employee::getAll();
		Log::get()->log(print_r($employees, true));
		$filteredEmployees = array();
		foreach ($employees as $key=>$employee) {
			if ($employee->Archived) {
				continue;
			}
			/*if(($employee->Permission & PERMISSION_ADMIN)!=PERMISSION_ADMIN && ($employee->Permission & PERMISSION_ACCOUNTS)!=PERMISSION_ACCOUNTS){
				continue;
			}*/
			array_push($filteredEmployees, $employee->toArray());
		}
		return array(
			'success' => true,
			'employees' => $filteredEmployees,
			'allemployees' => $employees
		);
	}
}