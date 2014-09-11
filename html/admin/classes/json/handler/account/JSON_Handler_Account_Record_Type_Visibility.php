<?php

class JSON_Handler_Account_Record_Type_Visibility extends JSON_Handler implements JSON_Handler_Loggable {

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
							rt.*,
							st.id AS service_type_id,
							st.name AS service_type_name

				FROM		RecordType rt

				LEFT JOIN	account_record_type_visibility artv
							ON (artv.record_type_id = rt.Id AND artv.account_id = {$mData->iAccountId})

				INNER JOIN	service_type st
							ON (st.id = rt.ServiceType)
			");
			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aCustomer[] = $aRow;
				}
			}
			return array(
				"aRecordTypes" => $aCustomer
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

	public function updateReordTypeVisibilityForArray($mData) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		try {
			if(is_array($mData)) {
				foreach($mData as $oRecord) {

					// Check if record already exists.
					$oAccountRecordTypeVisibility = Query::run("
						SELECT		artv.*
						FROM		account_record_type_visibility artv
						WHERE artv.record_type_id = {$oRecord->record_type_id} AND artv.account_id = {$oRecord->account_id}
					");
					if ($oAccountRecordTypeVisibility) {
						$oRow = $oAccountRecordTypeVisibility->fetch_assoc();
						if($oRow) {
							$bFoundExistingRecord = true;
						} else {
							$bFoundExistingRecord = false;
						}
					}
					// Update existing record accordingly
					if($bFoundExistingRecord) {
						if($oRecord->visibility == 'inherit') {
							$oQuery = new Query();
							$oQuery->Execute("
								DELETE FROM account_record_type_visibility
								WHERE record_type_id = {$oRecord->record_type_id} AND account_id = {$oRecord->account_id}");
						}
						if($oRecord->visibility == 'visible') {
							$oAccountRecordTypeVisibility = new Account_Record_Type_Visibility($oRow, $bLoad=true);
							$oAccountRecordTypeVisibility->account_id = $oRecord->account_id;
							$oAccountRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oAccountRecordTypeVisibility->is_visible = 1;
							$oAccountRecordTypeVisibility->save();
						}
						if($oRecord->visibility == 'hidden') {
							$oAccountRecordTypeVisibility = new Account_Record_Type_Visibility($oRow, $bLoad=true);
							$oAccountRecordTypeVisibility->account_id = $oRecord->account_id;
							$oAccountRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oAccountRecordTypeVisibility->is_visible = 0;
							$oAccountRecordTypeVisibility->save();
						}
					}

					// Create new record
					if(!$bFoundExistingRecord) {
						if($oRecord->visibility == 'inherit') {
							// Nothing to do.
						}
						if($oRecord->visibility == 'visible') {
							$oAccountRecordTypeVisibility = new Account_Record_Type_Visibility();
							$oAccountRecordTypeVisibility->account_id = $oRecord->account_id;
							$oAccountRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oAccountRecordTypeVisibility->is_visible = 1;
							$oAccountRecordTypeVisibility->save();
						}
						if($oRecord->visibility == 'hidden') {
							$oAccountRecordTypeVisibility = new Account_Record_Type_Visibility();
							$oAccountRecordTypeVisibility->account_id = $oRecord->account_id;
							$oAccountRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oAccountRecordTypeVisibility->is_visible = 0;
							$oAccountRecordTypeVisibility->save();
						}
					}
				}
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
}

class JSON_Handler_Customer_Group_Exception extends Exception {
	// No changes
}
