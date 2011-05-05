<?php

class Test_Postgres_Migration extends Test {
	
	private static $_aFixedColumnChanges = array(
		'Account' => array(
			'PrimaryContact' 	=> 'primary_contact_id',
			'LastBilled'		=> 'last_billed_date',
			'BillingMethod'		=> 'delivery_method_id',
			'CreatedBy'			=> 'created_employee_id',
			'CreatedOn'			=> 'created_date',
			'Archived'			=> 'account_status_id'
		),
		'AccountGroup' => array(
			'CreatedBy'	=> 'created_employee_id',
			'CreatedOn'	=> 'created_date',
			'ManagedBy'	=> 'managed_employee_id',
		),
		'CDR' => array(
			'Status' 		=> 'status',
			'NormalisedOn'	=> 'normalised_datetime',
			'RatedOn'		=> 'rated_datetime'
		),
		'CarrierModule' => array(
			'Type' 			=> 'carrier_module_type_id',
			'FileType'		=> 'resource_type_id',
			'LastSentOn'	=> 'last_sent_datetime'
		),
		'CarrierModuleConfig' => array(
			'Type' => 'data_type_id'
		),
		'Charge' => array(
			'Status' 		=> 'status',
			'CreatedBy'		=> 'created_employee_id',
			'CreatedOn'		=> 'created_date',
			'ApprovedBy'	=> 'approved_employee_id',
			'ChargedOn'		=> 'charged_date'
		),
		'Contact' => array(
			'PassWord' => 'password'
		),
		'CreditCard' => array(
			'CardType' => 'credit_card_type_id'
		),
		'DataReport' => array(
			'CreatedOn'	=> 'created_date'
		),
		'DataReportSchedule' => array(
			'Status' 		=> 'status',
			'CreatedOn'		=> 'created_datetime',
			'GeneratedOn'	=> 'generated_datetime'
		),
		'DocumentResource' => array(
			'Type' 		=> 'document_resource_type_id',
			'CreatedOn'	=> 'created_datetime'
		),
		'DocumentTemplate' => array(
			'TemplateType' 		=> 'document_template_type_id',
			'TemplateSchema'	=> 'document_template_schema_id',
			'EffectiveOn'		=> 'effective_datetime',
			'CreatedOn'			=> 'created_datetime',
			'ModifiedOn'		=> 'modified_datetime',
			'LastUsedOn'		=> 'last_used_datetime'
		),
		'DocumentTemplateSchema' => array(
			'TemplateType' => 'document_template_type_id'
		),
		'Employee' => array(
			'PassWord' => 'password'
		),
		'FileDownload' => array(
			'Status' 		=> 'status',
			'CollectedOn' 	=> 'collected_datetime',
			'ImportedOn' 	=> 'imported_datetime'
		),
		'FileExport' => array(
			'Status'		=> 'status',
			'SHA1'			=> 'sha1',
			'ExportedOn'	=> 'exported_datetime'
		),
		'FileImport' => array(
			'Status'		=> 'status',
			'SHA1'			=> 'sha1',
			'ImportedOn'	=> 'imported_datetime',
			'NormalisedOn'	=> 'normalised_datetime',
			'archived_on'	=> 'archived_datetime'
		),
		'Invoice' => array(
			'CreatedOn'	=> 'created_date',
			'DueOn'		=> 'due_date',
			'SettledOn'	=> 'settled_date'
		),
		'ProvisioningLog' => array(
			'Type' => 'provisioning_type_id'
		),
		'ProvisioningRequest' => array(
			'Type' 			=> 'provisioning_type_id',
			'Response'		=> 'provisioning_response_id',
			'Status'		=> 'provisioning_request_status_id',
			'RequestedOn'	=> 'requested_datetime',
			'SentOn'		=> 'sent_datetime',
			'LastUpdated'	=> 'last_update_datetime'
		),
		'ProvisioningResponse' => array(
			'Type' 			=> 'provisioning_type_id',
			'Request' 		=> 'provisioning_request_id',
			'Status' 		=> 'provisioning_response_status_id',
			'ImportedOn'	=> 'imported_datetime'
		),
		'RecurringCharge' => array(
			'CreatedBy'		=> 'created_employee_id',
			'ApprovedBy'	=> 'approved_employee_id',
			'CreatedOn'		=> 'created_date',
			'StartedOn'		=> 'started_date',
			'LastChargedOn'	=> 'last_charged_date'
		),
		'Service' => array(
			'Status' 	=> 'service_status_id',
			'CreatedOn'	=> 'created_datetime',
			'CreatedBy'	=> 'created_employee_id',
			'ClosedOn'	=> 'closed_datetime',
			'ClosedBy' 	=> 'closed_employee_id'
		),
		'ServiceRateGroup' => array(
			'CreatedBy'	=> 'created_employee_id',
			'CreatedOn'	=> 'created_datetime'
		),
		'ServiceRatePlan' => array(
			'CreatedBy'							=> 'created_employee_id',
			'CreatedOn'							=> 'created_datetime',
			'LastChargedOn'						=> 'last_charged_datetime',
			'contract_breach_fees_charged_on'	=> 'contract_breach_fees_charged_datetime'
		),
		'collections_schedule' => array(
			'day' => 'day'
		),
		'contact_terms' => array(
			'created_by'	=> 'created_employee_id',
			'created_on'	=> 'created_datetime'
		),
		'employee_message' => array(
			'created_on'	=> 'created_datetime',
			'effective_on'	=> 'effective_datetime'
		),
		'flex_config' => array(
			'created_by' 	=> 'created_employee_id',
			'created_on'	=> 'created_datetime'
		),
		'sale' => array(
			'verified_on' => 'verified_datetime'
		),
		'survey' => array(
			'created_by' => 'created_employee_id'
		),
		'telemarketing_fnn_blacklist' => array(
			'cached_on' 	=> 'cached_datetime',
			'expired_on' 	=> 'expired_datetime'
		),
		'telemarketing_fnn_dialled' => array(
			'dialled_on' => 'dialled_datetime'
		)
	);
	
	private static $_aTablesToIgnoreChanges = array(
		'CVFV1',
		'Payment',
		'tmp_staggered_barring_accounts_1261621293',
		'tmp_staggered_barring_accounts_1261621488',
		'tmp_staggered_barring_accounts_1264030405',
		'tmp_staggered_barring_accounts_1264030465',
		'tmp_staggered_barring_accounts_1264030756',
		'tmp_staggered_barring_accounts_1264030782',
		'tmp_staggered_barring_accounts_1264030844',
		'tmp_staggered_barring_accounts_1264030997',
		'tmp_staggered_barring_accounts_1264031089',
		'tmp_staggered_barring_accounts_1264031094',
		'tmp_staggered_barring_accounts_1264031102',
		"tmp_staggered_account_ranks_1261621293",
		"tmp_staggered_account_ranks_1261621488",
		"tmp_staggered_account_ranks_1264030405",
		"tmp_staggered_account_ranks_1264030465",
		"tmp_staggered_account_ranks_1264030756",
		"tmp_staggered_account_ranks_1264030782",
		"tmp_staggered_account_ranks_1264030844",
		"tmp_staggered_account_ranks_1264030997",
		"tmp_staggered_account_ranks_1264031089",
		"tmp_staggered_account_ranks_1264031094",
		"tmp_staggered_account_ranks_1264031102",
		'cd_11',
		'cd_12',
		'm2_credits',
		'm2_credits1',
		'UnitelFundedFNNs',
		//We're adding the names of any views here, because the mysql info schema lists them as tables as well
		'account_services',
		'current_service_account'
	);
	
	private static $_aTablesToNotMigrate = array(
		'CVFV1',
		'Payment',		
		'tmp_staggered_barring_accounts_1261621293',
		'tmp_staggered_barring_accounts_1261621488',
		'tmp_staggered_barring_accounts_1264030405',
		'tmp_staggered_barring_accounts_1264030465',
		'tmp_staggered_barring_accounts_1264030756',
		'tmp_staggered_barring_accounts_1264030782',
		'tmp_staggered_barring_accounts_1264030844',
		'tmp_staggered_barring_accounts_1264030997',
		'tmp_staggered_barring_accounts_1264031089',
		'tmp_staggered_barring_accounts_1264031094',
		'tmp_staggered_barring_accounts_1264031102',
		"tmp_staggered_account_ranks_1261621293",
		"tmp_staggered_account_ranks_1261621488",
		"tmp_staggered_account_ranks_1264030405",
		"tmp_staggered_account_ranks_1264030465",
		"tmp_staggered_account_ranks_1264030756",
		"tmp_staggered_account_ranks_1264030782",
		"tmp_staggered_account_ranks_1264030844",
		"tmp_staggered_account_ranks_1264030997",
		"tmp_staggered_account_ranks_1264031089",
		"tmp_staggered_account_ranks_1264031094",
		"tmp_staggered_account_ranks_1264031102",
		'cd_11',
		'cd_12',
		'm2_credits',
		'm2_credits1',
		'UnitelFundedFNNs',
		//We're adding the names of any views here, because the mysql info schema lists them as tables as well
		'account_services',
		'current_service_account'
	);
	
	public function __construct() {
		parent::__construct("***DO NOT RUN IN LIVE SITE***");
	}
	
	public function getRenameList() {
		// Get list of tables to change
		$mResult = Query::run("	SELECT	TABLE_NAME
								FROM 	INFORMATION_SCHEMA.TABLES
								WHERE 	TABLE_SCHEMA = 'flex_rdavis'");
		Data_Source_Time::
		$aAllTables = array();
		while ($aRow = $mResult->fetch_assoc()) {
			$sTableName = $aRow['TABLE_NAME'];
			if (in_array($sTableName, self::$_aTablesToIgnoreChanges)) {
				continue;
			}
			$aAllTables[$sTableName] = $aRow;
		}
		
		$aTables 		= array();
		$iTotalChanges	= 0;
		foreach ($aAllTables as $aRow) {
			$sTableName = $aRow['TABLE_NAME'];
			if (preg_match('/[A-Z]/', $sTableName)) {
				if ($sTableName === "FileType")
					$sNewTableName = 'file_type_';//temporary hack, duplicate table name needs to be resolved....
				else
					$sNewTableName 			= $this->convertDbName($sTableName);
				$aTables[$sTableName]	= array('sNewName' => $sNewTableName, 'oColumns' => array());
				$iTotalChanges++;
				Log::getLog()->log("Table: {$sTableName} => {$sNewTableName}");
			}
			
			$mResultColumns = Query::run("	SELECT	c.COLUMN_NAME, c.DATA_TYPE
											FROM	INFORMATION_SCHEMA.COLUMNS c
											JOIN	INFORMATION_SCHEMA.TABLES t ON (
														t.TABLE_NAME = c.TABLE_NAME 
														AND t.TABLE_SCHEMA = c.TABLE_SCHEMA 
														AND t.TABLE_TYPE = 'BASE TABLE'
													)
											WHERE	c.TABLE_SCHEMA = 'flex_rdavis'
											AND		c.TABLE_NAME = '{$sTableName}'");
			while ($aRowColumn = $mResultColumns->fetch_assoc()) {
				// Peform conversion
				$sColumnName 	= $aRowColumn['COLUMN_NAME'];
				$sNewColumnName	= $this->convertDbName($sColumnName, $sTableName);
				
				if (($aAllTables[$sColumnName] || $aAllTables[$sNewColumnName]) && in_array($aRowColumn['DATA_TYPE'], array('int', 'bigint'))) {
					// There is a table with the same name as the column and it is an int/bigint, add id, most likely a foreign key
					$sNewColumnName .= '_id';
				}
				
				if (preg_match('/[A-Z]/', $sColumnName) || ($sColumnName != $sNewColumnName)) {
					if (!isset($aTables[$sTableName])) {
						Log::getLog()->log("Table (GOOD NAME): {$sTableName}");
						$aTables[$sTableName] = array('oColumns' => array());
					}
					
					$aTables[$sTableName]['oColumns'][$sColumnName] = $sNewColumnName;
					$iTotalChanges++;
					Log::getLog()->log("\tColumn: {$sColumnName} => {$sNewColumnName}");
				}
			}
		}
		
		// Add any other fixed table column changes
		foreach (self::$_aFixedColumnChanges as $sTableName => $aColumns) {
			if (!isset($aTables[$sTableName])) {
				$aTables[$sTableName] = array('oColumns' => array());
			}
			
			foreach ($aColumns as $sColumnName => $sNewColumnName) {
				if ($sColumnName == $sNewColumnName) {
					unset($aTables[$sTableName]['oColumns'][$sColumnName]);
					if (count($aTables[$sTableName]['oColumns']) == 0 && !isset($aTables[$sTableName]['sNewName'])) {
						unset($aTables[$sTableName]);
					}
				} else {
					$aTables[$sTableName]['oColumns'][$sColumnName] = $sNewColumnName;
				}
				
				$iTotalChanges++;
				Log::getLog()->log("Fixed column change: {$sTableName}.{$sColumnName} to {$sTableName}.{$sNewColumnName}");
			}
		}
		
		// Repeat for views
		//$mResult = Query::run("	SELECT	v.TABLE_NAME, v.VIEW_DEFINITION
		//						FROM	INFORMATION_SCHEMA.VIEWS v
		//						WHERE	v.TABLE_SCHEMA = 'flex_rdavis'");
		$aViews	= array();

		$aViewDefs = array(					
			
						'current_service_account' => "	SELECT
										MAX(flex_rdavis.service.id) AS service_id,
										MAX(flex_rdavis.account.id) AS account_id
										FROM flex_rdavis.service
										JOIN flex_rdavis.account ON (flex_rdavis.service.account_id = flex_rdavis.account.id)
										WHERE ((flex_rdavis.account.id = flex_rdavis.service.account_id)
										AND (	flex_rdavis.service.closed_datetime IS NULL
												OR (now() < flex_rdavis.service.closed_datetime)
											)
										AND (flex_rdavis.service.created_datetime < NOW()))
										GROUP BY flex_rdavis.account.id,flex_rdavis.service.fnn"
										,
							'account_services' => "SELECT
							<schema_name>.service.account_id	AS account_id,
							<schema_name>.service.id			AS service_id,
							<schema_name>.service.fnn			AS fnn
							FROM (
									<schema_name>.service
									JOIN <schema_name>.current_service_account ON(		(<schema_name>.service.account_id = <schema_name>.current_service_account.account_id)
																					AND (<schema_name>.service.id = <schema_name>.current_service_account.service_id)
																					AND (<schema_name>.service.service_status_id IN (400,402,403)))
								)"
								
						
					);

		foreach ($aViewDefs as $sViewName => $sViewDef) {
			$aViews[$sViewName]= $sViewDef;
		}

		$aStoredProcedures = array (
				"	CREATE LANGUAGE 'plpgsql';
					CREATE OR REPLACE FUNCTION rebillMotorpassInsertAndUpdate() RETURNS trigger AS '
					DECLARE
						 acc_num INTEGER;
						 expiry DATE;
						 acc_name VARCHAR(256);
					BEGIN
						IF (NEW.motorpass_account_id IS NOT NULL) THEN
							SELECT	ma.account_name
							INTO	acc_name
							FROM	motorpass_account ma
							WHERE	ma.id = NEW.motorpass_account_id;

							SELECT	ma.account_number
							INTO	acc_num
							FROM	motorpass_account ma
							WHERE	ma.id = NEW.motorpass_account_id;

							SELECT	mc.card_expiry_date
							INTO	expiry
							FROM	motorpass_account ma
							JOIN	motorpass_card mc
										ON ma.motorpass_card_id = mc.id
							WHERE	ma.id = NEW.motorpass_account_id;

							NEW.account_name := acc_name;
							NEW.account_number := acc_num;
							NEW.card_expiry_date := expiry;

							RETURN NEW;
						END IF;
						RETURN NULL;
					END;
					' LANGUAGE 'plpgsql'",
			"	CREATE OR REPLACE FUNCTION rebillMotorpassUpdateAccountNameAndNumber() RETURNS trigger AS '
					BEGIN
						UPDATE	rebill_motorpass rm
						SET	rm.account_number = NEW.account_number,
							rm.account_name = NEW.account_name
						WHERE	rm.motorpass_account_id = NEW.id;
						RETURN NEW;
					END;
				' LANGUAGE 'plpgsql'",
			"CREATE OR REPLACE FUNCTION rebillMotorpassUpdateCardExpiryDate() RETURNS trigger AS '
				BEGIN
					UPDATE	rebill_motorpass rm
					SET	rm.card_expiry_date = NEW.card_expiry_date
					WHERE	rm.motorpass_account_id IN (
								SELECT	ma.id
								FROM	motorpass_account ma
								WHERE	ma.motorpass_card_id = NEW.id);
					RETURN NEW;
				END;
				' LANGUAGE 'plpgsql'");

		$aTriggers = array('CREATE TRIGGER rebill_motorpass_insert
							BEFORE INSERT ON <schema_name>.rebill_motorpass
							FOR EACH ROW
							EXECUTE PROCEDURE rebillMotorpassInsertAndUpdate()'
							,
							'CREATE TRIGGER rebill_motorpass_update
							BEFORE UPDATE ON <schema_name>.rebill_motorpass
							FOR EACH ROW
							EXECUTE PROCEDURE rebillMotorpassInsertAndUpdate()'
							,
							"CREATE TRIGGER rebill_motorpass_account_name_and_number
							AFTER UPDATE ON <schema_name>.motorpass_account
							FOR EACH ROW
							EXECUTE PROCEDURE rebillMotorpassUpdateAccountNameAndNumber()"
							,
							"CREATE TRIGGER rebill_motorpass_card_expiry_date
							AFTER UPDATE ON <schema_name>.motorpass_card
							FOR EACH ROW
							EXECUTE PROCEDURE rebillMotorpassUpdateCardExpiryDate()"

			);
		
		// Add details for tables not to be migrated
		foreach (self::$_aTablesToNotMigrate as $sTable) {
			if (!isset($aTables[$sTable])) {
				$aTables[$sTable] = array();
			}
			
			$aTables[$sTable]['bDoNotMigrate'] = true;
		}
		
		Log::getLog()->log("Total Changes: {$iTotalChanges}");
		return array('oTables' => $aTables, 'oViews' => $aViews, 'oProcedures' => $aStoredProcedures, 'oTriggers' => $aTriggers);
	}
	
	public function convertDbName($sDBName) {
		$i 			= 0;
		$aNewName	= array();
		$sPrevCase	= '';
		for ($i; $i < strlen($sDBName); $i++) {
			$sChar = $sDBName[$i];
			if (preg_match('/[A-Z]/', $sChar)) {
				// Upper case
				if (($sPrevCase == 'lower') || ($sPrevCase == 'numeric')) {
					// Previous char was lower case, new word
					$aNewName[] = '_';
				} else if (($sPrevCase != '') && preg_match('/[a-z]/', $sDBName[$i + 1])) {
					// Next is lower, this is the start of a new word
					$aNewName[] = '_';
				}
				
				$sChar 		= strtolower($sChar);
				$sPrevCase	= 'upper';
			} else if (preg_match('/[a-z]/', $sChar)) {
				// Lower case
				if ($sPrevCase == 'numeric') {
					// Previous char was lower case, new word
					$aNewName[] = '_';
				}
				
				$sPrevCase = 'lower';
			} else if (preg_match('/[0-9]/', $sChar)) {
				// Number
				if (($sPrevCase != '') && ($sPrevCase != 'numeric')) {
					// Previous char was not a number, new number
					$aNewName[] = '_';
				}
				
				$sPrevCase = 'numeric';
			} else {
				$sPrevCase = '';
			}
			
			$aNewName[] = $sChar;
		}
		return implode('', $aNewName);
	}
	
	public function storedProcedureConversion($sQuery) {
		$sNew = preg_replace('/<([\d\w]+)>/misU', ':$1', $sQuery);
		Log::getLog()->log($sNew);
	}
}

?>