<?php

class JSON_Handler_Customer_Group_Record_Type_Visibility extends JSON_Handler implements JSON_Handler_Loggable {

	public function getRecordTypesForCustomerGroupId($mData) {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
			$aCustomer = array();
			// TODO send customer group id as part of request.
			$mResult = Query::run("
				SELECT		cgrtv.id AS customer_group_record_type_visibility_id,
							cgrtv.customer_group_id,
							cgrtv.is_visible,
							rt.*

				FROM		RecordType rt

				INNER JOIN	customer_group_record_type_visibility cgrtv
							ON (cgrtv.record_type_id = rt.Id AND cgrtv.customer_group_id = {$mData->iCustomerGroupId})
			");
			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aCustomer[] = $aRow;
				}
			}
			return $aCustomer;
		} catch (JSON_Handler_Customer_Group_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function getUnusedRecordTypesForCustomerGroupId($mData) {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
			$aCustomer = array();
			// TODO send customer group id as part of request.
			$mResult = Query::run("
				SELECT		rt.*

				FROM		RecordType rt

				LEFT JOIN	customer_group_record_type_visibility cgrtv ON cgrtv.record_type_id = rt.Id

				WHERE cgrtv.record_type_id IS NULL
			");
			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aCustomer[] = $aRow;
				}
			}
			return $aCustomer;
		} catch (JSON_Handler_Customer_Group_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function updateIsVisibleForId($mData) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		try {
			if(is_array($mData)) {
				foreach($mData as $oRecord) {
					//var_dump($oRecord->id);
					$oCustomerGroupRecordTypeVisibility = Customer_Group_Record_Type_Visibility::getForId($oRecord->id);
					$oCustomerGroupRecordTypeVisibility->is_visible = $oRecord->is_visible;
					$oCustomerGroupRecordTypeVisibility->save();
				}
			} else {
				$oCustomerGroupRecordTypeVisibility = Customer_Group_Record_Type_Visibility::getForId($oRecord->id);
				$oCustomerGroupRecordTypeVisibility->is_visible = $mData->is_visible;
				$oCustomerGroupRecordTypeVisibility->save();
			}
			return	array(
				'Success'	=> true,
				'bSuccess'	=> true,
				'aResults'	=> $mData
			);
		} catch (JSON_Handler_Customer_Group_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function saveRecordType($mData) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		try {
			// Check and make sure the record doesn't already exist,
			// Database unique constraints should also prevent this.
			$mResult = Query::run("
				SELECT		cgrtv.*
				FROM		customer_group_record_type_visibility cgrtv
				WHERE		(cgrtv.record_type_id = {$mData->iRecordTypeId} AND cgrtv.customer_group_id = {$mData->iCustomerGroupId})");

			if ($mResult->fetch_assoc()) {
				return 	array(
					'bSuccess'	=> false,
					'sMessage'	=> 'There was an error inserting a new record type for customer group, the record type already exists.'
				);
			} else {
				$oCustomerGroupRecordTypeVisibility = new Customer_Group_Record_Type_Visibility();
				$oCustomerGroupRecordTypeVisibility->customer_group_id = $mData->iCustomerGroupId;
				$oCustomerGroupRecordTypeVisibility->record_type_id = $mData->iRecordTypeId;
				$oCustomerGroupRecordTypeVisibility->is_visible = $mData->iIsVisible;
				$oCustomerGroupRecordTypeVisibility->save();
			}
			return	array(
				'Success'	=> true,
				'bSuccess'	=> true,
				'aResults'	=> $oCustomerGroupRecordTypeVisibility
			);
		} catch (JSON_Handler_Customer_Group_Run_Exception $oException) {
			return 	array(
				'Success'	=> false,
				'bSuccess'	=> false,
				'sMessage'	=> $oException->getMessage()
			);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function getAll() {
		try {
			$aItems		= Customer_Group::listAll();
			$aResults	= array();
			foreach ($aItems as $oItem) {
				$aResults[$oItem->id]	= $oItem->toArray();
			}
			return	array(
				'Success'	=> true,
				'bSuccess'	=> true,
				'aResults'	=> $aResults
			);
		} catch (JSON_Handler_Customer_Group_Run_Exception $oException) {
			return 	array(
				'Success'	=> false,
				'bSuccess'	=> false,
				'sMessage'	=> $oException->getMessage()
			);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}
}

class JSON_Handler_Customer_Group_Exception extends Exception {
	// No changes
}
