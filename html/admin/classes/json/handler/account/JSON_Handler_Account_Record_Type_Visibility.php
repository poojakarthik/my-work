<?php

class JSON_Handler_Account_Record_Type_Visibility extends JSON_Handler implements JSON_Handler_Loggable{

	public function getRecordTypesForAccountId($mData) {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
			$aCustomer = array();
			// TODO send customer group id as part of request.
			$mResult = Query::run("
				SELECT		artv.id AS account_record_type_visibility_id,
							artv.account_id,
							artv.is_visible,
							rt.*

				FROM		RecordType rt

				INNER JOIN	account_record_type_visibility artv
							ON (artv.record_type_id = rt.Id AND artv.account_id = {$mData->iAccountId})
			");
			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aCustomer[] = $aRow;
				}
			}
			return $aCustomer;
		} catch (JSON_Handler_Account_Run_Exception $oException) {
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

	public function getUnusedRecordTypesForAccountId($mData) {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
			$aCustomer = array();
			// TODO send customer group id as part of request.
			$mResult = Query::run("
				SELECT		rt.*

				FROM		RecordType rt

				LEFT JOIN	account_record_type_visibility artv ON artv.record_type_id = rt.Id

				WHERE artv.record_type_id IS NULL
			");
			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aCustomer[] = $aRow;
				}
			}
			return $aCustomer;
		} catch (JSON_Handler_Account_Run_Exception $oException) {
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
					$oAccountRecordTypeVisibility = Account_Record_Type_Visibility::getForId($oRecord->id);
					$oAccountRecordTypeVisibility->is_visible = $oRecord->is_visible;
					$oAccountRecordTypeVisibility->save();
				}
			} else {
				$oAccountRecordTypeVisibility = Account_Record_Type_Visibility::getForId($oRecord->id);
				$oAccountRecordTypeVisibility->is_visible = $mData->is_visible;
				$oAccountRecordTypeVisibility->save();
			}
			return	array(
				'Success'	=> true,
				'bSuccess'	=> true,
				'aResults'	=> $mData
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
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
				SELECT		artv.*
				FROM		account_record_type_visibility artv
				WHERE		(artv.record_type_id = {$mData->iRecordTypeId} AND artv.account_id = {$mData->iAccountId})");

			if ($mResult->fetch_assoc()) {
				return 	array(
					'bSuccess'	=> false,
					'sMessage'	=> 'There was an error inserting a new record type for account, the record type already exists.'
				);
			} else {
				$oAccountRecordTypeVisibility = new Account_Record_Type_Visibility();
				$oAccountRecordTypeVisibility->account_id = $mData->iAccountId;
				$oAccountRecordTypeVisibility->record_type_id = $mData->iRecordTypeId;
				$oAccountRecordTypeVisibility->is_visible = $mData->iIsVisible;
				$oAccountRecordTypeVisibility->save();
			}
			return	array(
				'Success'	=> true,
				'bSuccess'	=> true,
				'aResults'	=> $oAccountRecordTypeVisibility
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
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
			$aItems		= Account::listAll();
			$aResults	= array();
			foreach ($aItems as $oItem) {
				$aResults[$oItem->id]	= $oItem->toArray();
			}
			return	array(
				'Success'	=> true,
				'bSuccess'	=> true,
				'aResults'	=> $aResults
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
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
