<?php

/**
 * Version 176 of database update.
 * This version: -
 *	
 *	1:	Add the carrier_instance Table
 *	2:	Add the carrier_instance_customer_group Table
 *
 *	3:	Add the CarrierModule.carrier_instance_id Field
 *
 *	4:	Populate the carrier_instance and carrier_instance_customer_group Tables
 *
 *	5:	Add the FileDownload.carrier_module_id Field
 *	6:	Add the FileExport.carrier_module_id Field
 *	7:	Add the FileImport.carrier_module_id Field
 *	8:	Add the ProvisioningRequest.carrier_module_id Field
 *	9:	Add the ProvisioningResponse.carrier_module_id Field
 *
 */

class Flex_Rollout_Version_000176 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	const	CARRIER_MODULE_CUSTOMER_GROUP_ALL	= '**ALL**';

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the carrier_instance Table
		$strSQL = "	CREATE TABLE	carrier_instance
					(
						id			BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						carrier_id	BIGINT				NOT NULL					COMMENT '(FK) Carrier',
						name		VARCHAR(256)		NOT NULL					COMMENT 'Name of this Instance',
						description	VARCHAR(512)		NULL						COMMENT 'Description of this Instance',
						
						CONSTRAINT	pk_carrier_instance_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_carrier_instance_carrier_id	FOREIGN KEY	(carrier_id)	REFERENCES Carrier(Id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the carrier_instance Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	carrier_instance;";
		
		//	2:	Add the carrier_instance_customer_group Table
		$strSQL = "	CREATE TABLE	carrier_instance_customer_group
					(
						id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						carrier_instance_id	BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Carrier Instance',
						customer_group_id	BIGINT				NOT NULL					COMMENT '(FK) Customer Group',
						
						CONSTRAINT	pk_carrier_instance_customer_group_id					PRIMARY KEY	(id),
						CONSTRAINT	fk_carrier_instance_customer_group_carrier_instance_id	FOREIGN KEY	(carrier_instance_id)	REFERENCES carrier_instance(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_carrier_instance_customer_group_customer_group_id	FOREIGN KEY	(customer_group_id)		REFERENCES CustomerGroup(Id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the carrier_instance_customer_group Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	carrier_instance_customer_group;";
		
		//	3:	Add the CarrierModule.carrier_instance_id Field
		$strSQL = "	ALTER TABLE	CarrierModule
						ADD	carrier_instance_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Carrier Instance',
						ADD	CONSTRAINT	fk_carrier_module_carrier_instance_id	FOREIGN KEY	(carrier_instance_id)	REFERENCES carrier_instance(id)	ON UPDATE CASCADE	ON DELETE CASCADE;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the CarrierModule.carrier_instance_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	CarrierModule
										DROP	FOREIGN KEY	fk_carrier_module_carrier_instance_id,
										DROP				carrier_instance_id;";
		
		//	4:	Populate the carrier_instance and carrier_instance_customer_group Tables
		$arrCustomerGroups	= array();
		$resCutomerGroups	= $dbAdmin->query("SELECT * FROM CustomerGroup WHERE 1");
		if (MDB2::isError($resCutomerGroups))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve Customer Group records. ' . $resCutomerGroups->getMessage() . " (DB Error: " . $resCutomerGroups->getUserInfo() . ")");
		}
		while ($arrCustomerGroup = $resCutomerGroups->fetchRow())
		{
			$arrCustomerGroups[$arrCustomerGroup['Id']]	= $arrCustomerGroup;
		}
		
		$arrCarriers	= array();
		$resCarriers	= $dbAdmin->query("SELECT * FROM Carrier WHERE 1");
		if (MDB2::isError($resCarriers))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve Carrier records. ' . $resCarriers->getMessage() . " (DB Error: " . $resCarriers->getUserInfo() . ")");
		}
		while ($arrCarrier = $resCarriers->fetchRow())
		{
			$arrCarriers[(int)$arrCarrier['Id']]	= $arrCarrier;
		}
		
		$arrCarrierInstances	= array();
		$arrCarrierModules		= array();
		$strCarrierModuleSQL	= "	SELECT	Id,
											Carrier,
											customer_group
									FROM	CarrierModule
									WHERE	carrier_instance_id IS NULL;";
		$resCarrierModule = $dbAdmin->query($strCarrierModuleSQL);
		if (MDB2::isError($resCarrierModule))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve Carrier Module records. ' . $resCarrierModule->getMessage() . " (DB Error: " . $resCarrierModule->getUserInfo() . ")");
		}
		while ($arrCarrierModule = $resCarrierModule->fetchRow())
		{
			$intCarrierId	= (int)$arrCarrierModule['Carrier'];
			if (!$intCarrierId)
			{
				throw new Exception(print_r($arrCarrierModule, true));
			}
			
			$arrCarrierInstance	= array();
			$arrCarrierInstance['carrier_id']			= $intCarrierId;
			$arrCarrierInstance['arrCustomerGroups']	= array();
			
			if ($arrCarrierModule['customer_group'] === null)
			{
				// Configured for all CustomerGroups
				$arrCarrierInstance['name']					= $arrCarriers[$intCarrierId]['Name'].": Common";
				$arrCarrierInstance['description']			= $arrCarrierInstance['name'];
				
				foreach ($arrCustomerGroups as $intCustomerGroupId=>$arrCustomerGroup)
				{
					$arrCarrierInstance['arrCustomerGroups'][$intCustomerGroupId]					= &$arrCustomerGroups[$intCustomerGroupId];
					//$arrCarrierInstance['arrCustomerGroups'][$intCustomerGroupId]					= $intCustomerGroupId;
					
					$arrCarriers[$intCarrierId]['arrCustomerGroups'][$intCustomerGroupId]			= &$arrCustomerGroups[$intCustomerGroupId];
					$arrCustomerGroups[$intCustomerGroupId]['arrCarrierInstances'][$intCarrierId]	= &$arrCarrierInstance;
				}
			}
			else
			{
				$intCustomerGroupId	= (int)$arrCarrierModule['customer_group'];
				
				// Configured for a single CustomerGroup
				$arrCarrierInstance['name']			= $arrCarriers[$intCarrierId]['Name'].": ".$arrCustomerGroups[$intCustomerGroupId]['internal_name'];
				$arrCarrierInstance['description']	= $arrCarrierInstance['name'];
				
				$arrCarrierInstance['arrCustomerGroups'][$intCustomerGroupId]					= &$arrCustomerGroups[$intCustomerGroupId];
				//$arrCarrierInstance['arrCustomerGroups'][$intCustomerGroupId]					= $intCustomerGroupId;
				
				$arrCarriers[$intCarrierId]['arrCustomerGroups'][$intCustomerGroupId]			= &$arrCustomerGroups[$intCustomerGroupId];
				$arrCustomerGroups[$intCustomerGroupId]['arrCarrierInstances'][$intCarrierId]	= &$arrCarrierInstance;
			}
			
			if (($intCarrierInstanceIndex = array_search(serialize($arrCarrierInstance), array_map('serialize', $arrCarrierInstances))) !== false)
			{
				$arrCarrierModule['arrCarrierInstance']	= &$arrCarrierInstances[$intCarrierInstanceIndex];
			}
			else
			{
				$arrCarrierModule['arrCarrierInstance']	= &$arrCarrierInstance;
				$arrCarrierInstances[]					= &$arrCarrierInstance;
			}
			
			$arrCarrierModules[]	= $arrCarrierModule;
			unset($arrCarrierInstance);
		}
		
		//$arrCarrierInstancesUnique		= array_map('unserialize', array_unique(array_map('serialize', $arrCarrierInstances)));
		//throw new Exception("Carrier Instances: ".((string)count($arrCarrierInstances))."; Unique Instances: ".((string)count($arrCarrierInstancesUnique)));
		//unset($arrCarrierInstance);
		foreach ($arrCarrierInstances as $intIndex=>&$arrCarrierInstance)
		{
			//throw new Exception(serialize($arrCarrierInstance['carrier_id']));
			//throw new Exception(print_r($arrCarrierInstance, true));
			if (!$arrCarrierInstance['carrier_id'])
			{
				$arrTests	= array();
				$arrTests[]	= $dbAdmin->quote(1		, 'integer');
				$arrTests[]	= $dbAdmin->quote(null	, 'integer');
				$arrTests[]	= $dbAdmin->quote('2'	, 'integer');
				$arrTests[]	= $dbAdmin->quote(7		, 'integer');
				$arrTests[]	= $dbAdmin->quote(-22	, 'integer');
				
				$arrTests[]	= print_r($arrCarrierInstance, true);
				throw new Exception(print_r($arrTests, true));
			}
			
			// Create the Carrier Instance
			$strCarrierInstanceInsert	= "	INSERT INTO	carrier_instance
												(carrier_id, name, description)
											VALUES
												(
													".$dbAdmin->quote($arrCarrierInstance['carrier_id']		, 'integer').",
													".$dbAdmin->quote($arrCarrierInstance['name']			, 'text').",
													".$dbAdmin->quote($arrCarrierInstance['description']	, 'text')."
												);";
			$resCarrierInstanceInsert	= $dbAdmin->query($strCarrierInstanceInsert);
			if (MDB2::isError($resCarrierInstanceInsert))
			{
				throw new Exception(__CLASS__ . ' Failed to add Carrier Instance \''.$arrCarrierInstance['name'].'\'. ' . $resCarrierInstanceInsert->getMessage() . " (DB Error: " . $resCarrierInstanceInsert->getUserInfo() . ")");
			}
			$arrCarrierInstance['id']	= (int)$dbAdmin->lastInsertID();
			
			if (!$arrCarrierInstance['id'])
			{
				throw new Exception(print_r(array_keys($arrCarrierInstance), true));
			}
			
			// Create the Carrier Instance -> Customer Group links
			foreach ($arrCarrierInstance['arrCustomerGroups'] as $intCustomerGroupId=>$arrCustomerGroup)
			{
				$strCarrierInstanceLinkInsert	= "	INSERT INTO	carrier_instance_customer_group
														(carrier_instance_id, customer_group_id)
													VALUES
														(
															".$dbAdmin->quote($arrCarrierInstance['id']	, 'integer').",
															".$dbAdmin->quote($intCustomerGroupId		, 'integer')."
														);";
				$resCarrierInstanceLinkInsert	= $dbAdmin->query($strCarrierInstanceLinkInsert);
				if (MDB2::isError($resCarrierInstanceLinkInsert))
				{
					throw new Exception(__CLASS__ . ' Failed to link Carrier Instance \''.$arrCarrierInstance['name'].'\' to Customer Groups. ' . $resCarrierInstanceLinkInsert->getMessage() . " (DB Error: " . $resCarrierInstanceLinkInsert->getUserInfo() . ")");
				}
			}
			
			//$arrCarrierInstance[$intIndex]	= $arrCarrierInstance;
		}
		
		// Update the CarrierModule.carrier_instance_id Field
		$intCount	= 0;
		foreach ($arrCarrierModules as &$arrCarrierModule)
		{
			$intCount++;
			if (!$arrCarrierModule['arrCarrierInstance']['id'])
			{
				throw new Exception("@ Count {$intCount}: ".print_r(array_keys($arrCarrierModule['arrCarrierInstance']), true));
			}
			
			//throw new Exception(serialize($arrCarrierModule['arrCarrierInstance']['id'])."\n\n".print_r($arrCarrierModule['arrCarrierInstance'], true));
			$strCarrierModuleUpdate	= "	UPDATE	CarrierModule
										SET		carrier_instance_id	= ".$dbAdmin->quote($arrCarrierModule['arrCarrierInstance']['id'], 'integer')."
										WHERE	Id = ".$dbAdmin->quote($arrCarrierModule['Id'], 'integer').";";
			$resCarrierModuleUpdate	= $dbAdmin->query($strCarrierModuleUpdate);
			if (MDB2::isError($resCarrierModuleUpdate))
			{
				throw new Exception(__CLASS__ . ' Failed to set CarrierModule.carrier_instance_id #'.$arrCarrierModule['Id'].'. ' . $resCarrierModuleUpdate->getMessage() . " (DB Error: " . $resCarrierModuleUpdate->getUserInfo() . ")");
			}
		}
		
		//	5:	Add the FileDownload.carrier_module_id Field
		$strSQL = "	ALTER TABLE	FileDownload
						ADD	carrier_module_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Carrier Module',
						ADD	CONSTRAINT	fk_file_download_carrier_module_id	FOREIGN KEY	(carrier_module_id)	REFERENCES CarrierModule(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the FileDownload.carrier_module_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	FileDownload
										DROP	FOREIGN KEY	fk_file_download_carrier_module_id,
										DROP				carrier_module_id;";
		
		//	6:	Add the FileExport.carrier_module_id Field
		$strSQL = "	ALTER TABLE	FileExport
						ADD	carrier_module_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Carrier Module',
						ADD	CONSTRAINT	fk_file_export_carrier_module_id	FOREIGN KEY	(carrier_module_id)	REFERENCES CarrierModule(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the FileExport.carrier_module_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	FileExport
										DROP	FOREIGN KEY	fk_file_export_carrier_module_id,
										DROP				carrier_module_id;";
		
		//	7:	Add the FileImport.carrier_module_id Field
		$strSQL = "	ALTER TABLE	FileImport
						ADD	carrier_module_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Carrier Module',
						ADD	CONSTRAINT	fk_file_import_carrier_module_id	FOREIGN KEY	(carrier_module_id)	REFERENCES CarrierModule(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the FileImport.carrier_module_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	FileImport
										DROP	FOREIGN KEY	fk_file_import_carrier_module_id,
										DROP				carrier_module_id;";
		
		//	8:	Add the ProvisioningRequest.carrier_module_id Field
		$strSQL = "	ALTER TABLE	ProvisioningRequest
						ADD	carrier_module_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Carrier Module',
						ADD	CONSTRAINT	fk_provisioning_request_carrier_module_id	FOREIGN KEY	(carrier_module_id)	REFERENCES CarrierModule(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningRequest.carrier_module_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	ProvisioningRequest
										DROP	FOREIGN KEY	fk_provisioning_request_carrier_module_id,
										DROP				carrier_module_id;";
		
		//	9:	Add the ProvisioningResponse.carrier_module_id Field
		$strSQL = "	ALTER TABLE	ProvisioningResponse
						ADD	carrier_module_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Carrier Module',
						ADD	CONSTRAINT	fk_provisioning_response_carrier_module_id	FOREIGN KEY	(carrier_module_id)	REFERENCES CarrierModule(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->exec($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.carrier_module_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	ProvisioningResponse
										DROP	FOREIGN KEY	fk_provisioning_response_carrier_module_id,
										DROP				carrier_module_id;";
		
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>