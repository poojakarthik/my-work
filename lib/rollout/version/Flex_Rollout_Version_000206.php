<?php

/**
 * Version 206 (Two-Hundred-And-Six) of database update.
 * This version: -
 *	1:	Adds a new table data_report_status
 *	2:	Insert the data_report_status records
 *	3:	Adds a foreign key to data_report_status in the DataReport table
 */

class Flex_Rollout_Version_000206 extends Flex_Rollout_Version
{
	private $rollbackSQL 		= array();
	private	$iPermissionDebug	= 0x80000000;
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds a new table data_report_status
		$sSQL	= "	CREATE TABLE data_report_status
					(
						id 			INT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)	NOT NULL					COMMENT 'Status name',
						description VARCHAR(1024)	NULL						COMMENT 'Status description',
						system_name	VARCHAR(256)	NOT NULL					COMMENT 'System name',
						const_name	VARCHAR(512)	NOT NULL					COMMENT 'Constant alias',
						  
						CONSTRAINT	pk_data_report_status_id	PRIMARY KEY	(id)
					) ENGINE=InnoDB COMMENT = 'Defines a data report status';";
		
		$oResult	= $dbAdmin->query($sSQL);
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to create data_report_status table - ". $oResult->getMessage());
		}
		
		$this->rollbackSQL[]	= 	array(	
										"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE data_report_status;"
									);
		
		// 2:	Insert the data_report_status records
		$sSQL	= "	INSERT INTO	data_report_status
						(name		, description				, system_name	, const_name)
					VALUES
						('Draft'	, 'Incomplete Data Report'	, 'DRAFT'		, 'DATA_REPORT_STATUS_DRAFT'),
						('Active'	, 'Active Data Report'		, 'ACTIVE'		, 'DATA_REPORT_STATUS_ACTIVE'),
						('Inactive'	, 'Inactive Data Report'	, 'INACTIVE'	, 'DATA_REPORT_STATUS_INACTIVE');";
		$oResult	= $dbAdmin->query($sSQL);
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Other Charges & 3G Destination Contexts. ' . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		
		$this->rollbackSQL[]	= 	array(	
										"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DELETE FROM	data_report_status
														WHERE		const_name IN ('DATA_REPORT_STATUS_DRAFT', 'DATA_REPORT_STATUS_ACTIVE', 'DATA_REPORT_STATUS_INACTIVE');"
									);
		
		// 3:	Adds a foreign key to data_report_status in the DataReport table
		// Get the DATA_REPORT_ACTIVE id
		$sDefault	= "	SELECT	id
						FROM	data_report_status
						WHERE	const_name	= 'DATA_REPORT_STATUS_ACTIVE';";
		$oResult	= $dbAdmin->query($sDefault);
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . ' Failed to get the id of the DATA_REPORT_STATUS_ACTIVE data_report_status. ' . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		
		$aRow				= $oResult->fetchRow();
		$iDataReportActive	= $aRow[0];
		if (!is_numeric($iDataReportActive))
		{
			throw new Exception(__CLASS__ . " Non numeric id returned for DATA_REPORT_STATUS_ACTIVE, ROW=".print_r($aRow, true).". ");
		}
		
		// Add the column with the default value of DATA_REPORT_ACTIVE
		$sSQL	= "
			ALTER TABLE DataReport
			ADD COLUMN data_report_status_id INT UNSIGNED	NOT NULL	DEFAULT {$iDataReportActive}	COMMENT '(FK) data_report_status',
			ADD	CONSTRAINT fk_DataReport_data_report_status_id	FOREIGN KEY	(data_report_status_id)	REFERENCES	data_report_status(id);
		";
		
		$oResult	= $dbAdmin->query($sSQL);
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to add data_report_status_id column to the DataReport table - ". $oResult->getMessage());
		}
		
		// Update each data_report_status_id depending on the existing priviledges. Make all debug or greater reports, DATA_REPORT_DRAFT
		// Get the DATA_REPORT_DRAFT id
		$sDraft		= "	SELECT	id
						FROM	data_report_status
						WHERE	const_name	= 'DATA_REPORT_STATUS_DRAFT';";
		$oResult	= $dbAdmin->query($sDraft);
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . ' Failed to get the id of the DATA_REPORT_STATUS_DRAFT data_report_status. ' . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		
		$aRow				= $oResult->fetchRow();
		$iDataReportDraft	= $aRow[0];
		if (!is_numeric($iDataReportDraft))
		{
			throw new Exception(__CLASS__ . " Non numeric id returned for DATA_REPORT_STATUS_DRAFT, ROW=".print_r($aRow, true).". ");
		}
		
		$sUpdate	= "	UPDATE	DataReport
						SET		data_report_status_id = {$iDataReportDraft}
						WHERE	Priviledges >= {$this->iPermissionDebug};";
		
		$oResult	= $dbAdmin->query($sUpdate);
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to update the data_report_status_id column in the DataReport table - ". $oResult->getMessage().". QUERY='{$sUpdate}'.");
		}
		
		$this->rollbackSQL[]	= 	array(
										"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "ALTER TABLE DataReport 
														DROP COLUMN data_report_status_id,
														DROP FOREIGN KEY fk_DataReport_data_report_status_id;"
									);
	}
	
	function rollback()
	{
		// Setup a connection for each database
		$arrConnections = array();
		foreach ($GLOBALS['*arrConstant']['DatabaseConnection'] as $strDb=>$arrDetails)
		{
			$arrConnections[$strDb] = Data_Source::get($strDb);
		}
		
		// Pointer to the appropriate db connection
		$objDb = NULL;
		
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				// Get reference to appropriate database
				$objDb = &$arrConnections[$this->rollbackSQL[$l]['Database']];
				
				// Perform the SQL
				$objResult = $objDb->query($this->rollbackSQL[$l]['SQL']);
				
				if (PEAR::isError($objResult))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l]['SQL'] . '. ' . $objResult->getMessage());
				}
			}
		}
	}
}

?>
