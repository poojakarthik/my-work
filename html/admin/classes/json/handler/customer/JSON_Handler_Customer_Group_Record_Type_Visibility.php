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
							rt.*,
							st.id AS service_type_id,
							st.name AS service_type_name

				FROM		RecordType rt

				LEFT JOIN	customer_group_record_type_visibility cgrtv
							ON (cgrtv.record_type_id = rt.Id AND cgrtv.customer_group_id = {$mData->iCustomerGroupId})

				INNER JOIN	service_type st
							ON (st.id = rt.ServiceType)
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

	public function updateReordTypeVisibilityForArray($mData) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		try {
			if(is_array($mData)) {
				foreach($mData as $oRecord) {

					// Check if record already exists.
					$oCustomerGroupRecordTypeVisibility = Query::run("
						SELECT		cgrtv.*
						FROM		customer_group_record_type_visibility cgrtv
						WHERE cgrtv.record_type_id = {$oRecord->record_type_id} AND cgrtv.customer_group_id = {$oRecord->customer_group_id}
					");
					if ($oCustomerGroupRecordTypeVisibility) {
						$oRow = $oCustomerGroupRecordTypeVisibility->fetch_assoc();
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
								DELETE FROM customer_group_record_type_visibility
								WHERE record_type_id = {$oRecord->record_type_id} AND customer_group_id = {$oRecord->customer_group_id}");
						}
						if($oRecord->visibility == 'visible') {
							$oCustomerGroupRecordTypeVisibility = new Customer_Group_Record_Type_Visibility($oRow, $bLoad=true);
							$oCustomerGroupRecordTypeVisibility->customer_group_id = $oRecord->customer_group_id;
							$oCustomerGroupRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oCustomerGroupRecordTypeVisibility->is_visible = 1;
							$oCustomerGroupRecordTypeVisibility->save();
						}
						if($oRecord->visibility == 'hidden') {
							$oCustomerGroupRecordTypeVisibility = new Customer_Group_Record_Type_Visibility($oRow, $bLoad=true);
							$oCustomerGroupRecordTypeVisibility->customer_group_id = $oRecord->customer_group_id;
							$oCustomerGroupRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oCustomerGroupRecordTypeVisibility->is_visible = 0;
							$oCustomerGroupRecordTypeVisibility->save();
						}
					}

					// Create new record
					if(!$bFoundExistingRecord) {
						if($oRecord->visibility == 'inherit') {
							// Nothing to do.
						}
						if($oRecord->visibility == 'visible') {
							$oCustomerGroupRecordTypeVisibility = new Customer_Group_Record_Type_Visibility();
							$oCustomerGroupRecordTypeVisibility->customer_group_id = $oRecord->customer_group_id;
							$oCustomerGroupRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oCustomerGroupRecordTypeVisibility->is_visible = 1;
							$oCustomerGroupRecordTypeVisibility->save();
						}
						if($oRecord->visibility == 'hidden') {
							$oCustomerGroupRecordTypeVisibility = new Customer_Group_Record_Type_Visibility();
							$oCustomerGroupRecordTypeVisibility->customer_group_id = $oRecord->customer_group_id;
							$oCustomerGroupRecordTypeVisibility->record_type_id = $oRecord->record_type_id;
							$oCustomerGroupRecordTypeVisibility->is_visible = 0;
							$oCustomerGroupRecordTypeVisibility->save();
						}
					}
				}
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
}

class JSON_Handler_Customer_Group_Exception extends Exception {
	// No changes
}
