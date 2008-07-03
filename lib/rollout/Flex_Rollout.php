<?php

// Note: Supress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
@require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'flex.require.php');
require_once('Flex_Rollout_Version.php');

class Flex_Rollout
{

	public function updateToLatestVersion($intVersion=NULL, $bolTestOnly=FALSE)
	{
		// Turn on error reporting for all rollout errors
		@mysqli_report(MYSQLI_REPORT_ALL);

		// Using the default connection, find the current (maximum) version number
		$strTables = 'database_version';
		$arrColumns = array( 'version' 	=> 'max(version)');
		$strWhere = NULL;
		$arrWhere = Array();
		$selVersion = new StatementSelect($strTables, $arrColumns, $strWhere);
		$mixResult = $selVersion->Execute($arrWhere);

		// If we couldn't get the connection or result, bail out before we do any damage!
		if ($mixResult === FALSE || !$mixResult)
		{
			throw new Exception("Rollout was unable to determine the current database version prior to starting.");
		}

		// Get the version number from the results
		$arrVersion = $selVersion->Fetch();
		$intVersion = intval($arrVersion['version']);

		// Get the available versions after the current version
		$arrVersions = self::_getVersionsAfter($intVersion);
		$versions = array_keys($arrVersions);

		// Rollouts can use any of the configured database connections,
		// so we need to start a transaction on each of them.
		$arrConnectionNames = array_keys($GLOBALS['**arrDatabase']);
		$nrConnections = count($arrConnectionNames);
		$arrConnections = array();


		$errors = array();

		// We always want to update the data model, as this ensures a model exists
		for ($i = 0; $i < $nrConnections; $i++)
		{
			try
			{
				@mysqli_report(MYSQLI_REPORT_ERROR);
				Flex_Data_Model::generateDataModelForDatabase($arrConnectionNames[$i]);
			}
			catch (Exception $e)
			{
				$errors[] = "ERROR: Rollout failed to generate new data model for data source '" . $arrConnectionNames[$i] . "'.\nThis must be resolved manually (or by re-running rollout).\n" . $e->getMessage();
			}
		}
		
		// We always want to update the database_constants.php file
		// Rebuild the database_constants.php file
		try
		{
			self::GenerateDatabaseConstantsFile();
		}
		catch (Exception $e)
		{
			// Error occurred.  All rollback actions have been performed
			$errors[] = "WARNING: Failed to build new database_constants.php file. ". $e->getMessage();
		}
		

		$errors = implode("\n", $errors);

		if ($errors)
		{
			throw new Exception($errors);
		}

		$errors = array();
		$index = 0;
		$nrVersions = count($versions);

		// If there are no rollouts, end here
		if (!$nrVersions)
		{
			return '';
		}

		// Begin a database transaction for each connection
		for ($i = 0; $i < $nrConnections; $i++)
		{
			try
			{
				$step = 'connect to';
				$arrConnections[$i] = DataAccess::getDataAccess($arrConnectionNames[$i]);
				$step = 'start transaction on';
				$arrConnections[$i]->TransactionStart();
			}
			catch (Exception $e)
			{
				$dbName = $arrConnectionNames[$i];
				throw new Exception("Rollout failed to $step database ''$dbName' prior to starting: " . $e->getMessage());
			}
		}

		try
		{
			// Roll out each change in order
			for($index = 0; $index < $nrVersions; $index++)
			{
				$arrVersions[$versions[$index]]->rollout();
			}
			$index--;

			// Using the default database connection, update the database version number
			$arrValues = array(
				'version' => max($versions),
				'rolled_out_date' => date('Y-m-d H:i:s')
			);
			$insVersion = new StatementInsert($strTables);
			$mxdResult = $insVersion->Execute($arrValues);
			if ($mxdResult === FALSE)
			{
				throw new Exception('Failed to update the database version number to ' . max($versions) . '. ' . mysqli_errno() . '::' . mysqli_error());
			}
		}
		catch (Exception $exception)
		{
			$errors[] = "ERROR: " . $exception->getMessage();

			// Need to rollback the db changes for each connection
			for ($i = 0; $i < $nrConnections; $i++)
			{
				try
				{
					$arrConnections[$i]->TransactionRollback();
				}
				catch (Exception $e)
				{
					$errors[] = "ERROR: Rollout failed to rollback changes to db " . $arrConnectionNames[$i] . ": " . $e->getMessage();
				}
			}

			// For each update applied do a rollback, in reverse order
			for(; $index >= 0; $index--)
			{
				try
				{
					$arrVersions[$versions[$index]]->rollback();
				}
				catch (Exception $e)
				{
					// This really shouldn't happen!
					$errors[] = "WARNING: Failed to rollback version " . $versions[$index] . " (non-data changes):\n" . $e->getMessage();
				}
			}

			throw new Exception(implode("\n", $errors));
		}

		// Commit the database changes
		for ($i = 0; $i < $nrConnections; $i++)
		{
			try
			{
				if ($bolTestOnly)
				{
					$arrConnections[$i]->TransactionRollback();
				}
				else
				{
					$arrConnections[$i]->TransactionCommit();
				}
			}
			catch (Exception $e)
			{
				$errors[] = "ERROR: Rollout failed to commit changes to db " . $arrConnectionNames[$i] . ": " . $e->getMessage();
			}

			try
			{
				@mysqli_report(MYSQLI_REPORT_ERROR);
				Flex_Data_Model::generateDataModelForDatabase($arrConnectionNames[$i]);
			}
			catch (Exception $e)
			{
				$errors[] = "ERROR: Rollout failed to generate new data model for data source '" . $arrConnectionNames[$i] . "'.\nThis must be resolved manually (or by re-running rollout).\n" . $e->getMessage();
			}
		}

		// Invoke the commit function for each rollout, in the same sequence
		for($index = 0; $index < $nrVersions; $index++)
		{
			try
			{
				if ($bolTestOnly)
				{
					$arrVersions[$versions[$index]]->rollback();
				}
				else
				{
					$arrVersions[$versions[$index]]->commit();
				}
			}
			catch (Exception $e)
			{
				// This really shouldn't happen!
				$errors[] = "WARNING: Failed to commit version " . $versions[$index] . " (non-database changes only)";
			}
		}

		// Rebuild the database_constants.php file
		try
		{
			self::GenerateDatabaseConstantsFile();
		}
		catch (Exception $e)
		{
			// Error occurred.  All rollback actions have been performed
			$errors[] = "WARNING: Failed to build new database_constants.php file.";
		}

		$errors = implode("\n", $errors);

		if ($errors)
		{
			throw new Exception($errors);
		}

		return $errors;
	}


	private static function _getVersionsAfter($intAfter=NULL)
	{
		if ($intAfter === NULL || !is_int($intAfter))
		{
			$intAfter = -1;
		}

		$pattern = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'version' . DIRECTORY_SEPARATOR . 'Flex_Rollout_Version_*.php';
		$arrVersions = array();
		preg_match_all("/_([0-9]+)\.php$/m", implode("\n", glob($pattern)), $arrVersions);
		$arrNewVersions = array();
		foreach($arrVersions[1] as $strVersion)
		{
			$intVersion = intval($strVersion);
			if ($intVersion <= $intAfter)
			{
				continue;
			}

			$className = "Flex_Rollout_Version_$strVersion";
			
			$arrNewVersions[$intVersion] = Flex_Rollout_Version::getInstance($className);
		}

		if (!ksort($arrNewVersions))
		{
			throw new Exception('Unable to load and order version updates.');
		}

		return $arrNewVersions;
	}
	
	//------------------------------------------------------------------------//
	// GenerateDatabaseConstantsFile
	//------------------------------------------------------------------------//
	/**
	 * GenerateDatabaseConstantsFile()
	 *
	 * Builds the database_constants.php file
	 *
	 * Builds the database_constants.php file
	 * throws an exception on error
	 * 
	 * @return	void
	 *
	 * @method
	 */
	public static function GenerateDatabaseConstantsFile()
	{
		$strDataSource = "flex";
		
		// Retrieve a list of all tables in the database
		$qryQuery	= new Query($strDataSource);
		$objTables	= $qryQuery->Execute("SHOW TABLES");
		if (!$objTables)
		{
			throw new Exception("Failed to retrieve tables of the '$strDataSource' database. " . mysqli_errno() . '::' . mysqli_error());
		}
		
		// For each table of the database, check if constants should be built for it
		$arrConstantGroups = array();
		while ($arrTable = $objTables->fetch_assoc())
		{
			$strTable	= current($arrTable);
			$strQuery	= "SHOW COLUMNS FROM $strTable WHERE Field IN ('id', 'const_name', 'description')";
			$objColumns	= $qryQuery->Execute($strQuery);
			
			if (!$objColumns)
			{
				throw new Exception("Failed to retrieve column listing for the '$strTable' table of the '$strDataSource' database. " . mysqli_errno() . '::' . mysqli_error());
			}
			
			// Check if it has all 3 columns
			$intColumns = 0;
			while ($arrColumn = $objColumns->fetch_assoc())
			{
				$intColumns++;
			}
			
			if ($intColumns != 3)
			{
				// This table does not have all three of the id, name and description columns
				// Don't build constant declarations for it
				continue;
			}
			
			// The table has all 3 columns, which means we should convert it to constant declarations
			// Retrieve the values and create a constant group
			$strQuery		= "SELECT id, const_name, description FROM $strTable WHERE const_name IS NOT NULL AND const_name != '' ORDER BY id ASC";
			$objRecordSet	= $qryQuery->Execute($strQuery);
			
			if (!$objRecordSet)
			{
				throw new Exception("Failed to retrieve the contents of the '$strTable' table of the '$strDataSource' database. " . mysqli_errno() . '::' . mysqli_error());
			}
			
			// Build the constant group
			$arrConstantGroup		= array();
			$arrUsedConstantNames	= array();
			while ($arrRecord = $objRecordSet->fetch_assoc())
			{
				// Check that this constant value is not already being used within the constant group
				if (array_key_exists($arrRecord['id'], $arrConstantGroup))
				{
					// This 'constant' value has already been used by another constant in this group
					throw new Exception("Redefinition of constant value {$arrRecord['id']} for ConstantGroup $strTable.");
				}
				
				// Check that the constant's name is not already being used within the constant group
				if (in_array($arrRecord['const_name'], $arrUsedConstantNames))
				{
					throw new Exception("Redeclaration of constant name {$arrRecord['const_name']} for ConstantGroup $strTable.");
				}
				
				// Check that the constant's name is not already being used by any other constant defined
				// in $GLOBALS['*arrConstant'] omitting the current constant group
				foreach ($GLOBALS['*arrConstant'] as $strConstGroup=>$arrConstGroup)
				{
					if ($strConstGroup != $strTable)
					{
						foreach ($arrConstGroup as $arrConst)
						{
							if ($arrConst['Constant'] == $arrRecord['const_name'])
							{
								// Conflicting constant names
								throw new Exception("Conflicting constant names.  Constant name '{$arrRecord['const_name']}' belonging to ConstantGroup '$strTable', is already being used by ConstantGroup '$strConstGroup'");
							}
						}
					}
				}
				
				// Add the constant to the ConstantGroup
				$arrConstantGroup[$arrRecord['id']] = array(	'Constant'		=> $arrRecord['const_name'],
																'Description'	=> $arrRecord['description']
															);
				
				// Add the constant name to the list of constant names already used by this ConstantGroup
				$arrUsedConstantNames[] = $arrRecord['const_name'];
			}
			
			if (count($arrConstantGroup) != 0)
			{
				// Add the ConstantGroup to the array of ConstantGroups
				$arrConstantGroups[$strTable] = $arrConstantGroup;
			}
		}
		
		// Build the database_constants.php file
		$strTimeStamp	= date("H:i:s d/m/Y");
		$strFilePath	= GetVixenBase() . 'lib' . DIRECTORY_SEPARATOR ."framework". DIRECTORY_SEPARATOR ."database_constants.php";
		
		// Make a backup of the current database_constants.php file
		if (file_exists($strFilePath))
		{
			if (!copy($strFilePath, "$strFilePath.bak"))
			{
				// Could not create the backup file
				throw new Exception("Could not create backup file: $strFilePath.bak");
			}
		}
			
		$fileConstFile	= @fopen($strFilePath, 'w');
		if ($fileConstFile === FALSE)
		{
			throw new Exception("Failed to open '$strFilePath' for writing.");
		}
		
		$strFileContents = 
"<?php
/* 
 * Database Constant definitions
 * File created: $strTimeStamp
 */

";
		
		foreach ($arrConstantGroups as $strConstantGroupName=>$arrConstantGroup)
		{
			$strFileContents .= "\n// Constant Group: $strConstantGroupName\n";
	
			foreach ($arrConstantGroup as $mixValue=>$arrConstant)
			{
				$strFileContents .= 
"\$GLOBALS['*arrConstant']\t['$strConstantGroupName']\t[$mixValue]\t['Constant']\t= '{$arrConstant['Constant']}';
\$GLOBALS['*arrConstant']\t['$strConstantGroupName']\t[$mixValue]\t['Description']\t= '{$arrConstant['Description']}';
";
			}
		}
		$strFileContents .= "\n?>";
		
		if (!@fwrite($fileConstFile, $strFileContents))
		{
			copy("$strFilePath.bak", $strFilePath);
			throw new Exception("Failed writing to $strFilePath");
		}
		if (!@fclose($fileConstFile))
		{
			copy("$strFilePath.bak", $strFilePath);
			throw new Exception("Failed to close file $strFilePath");
		}
	}
	
	//------------------------------------------------------------------------//
	// RollbackDatabaseConstantsFile
	//------------------------------------------------------------------------//
	/**
	 * RollbackDatabaseConstantsFile()
	 *
	 * Reverts back to the backup of database_constants.php if it exists
	 *
	 * Reverts back to the backup of database_constants.php if it exists
	 * 
	 * @return	void
	 *
	 * @method
	 */
	public static function RollbackDatabaseConstantsFile()
	{
		$strFilePath	= GetVixenBase() . 'lib' . DIRECTORY_SEPARATOR ."framework". DIRECTORY_SEPARATOR ."database_constants.php";
		
		// Check if there is a backup
		if (file_exists("$strFilePath.bak"))
		{
			// Revert to the backup
			copy("$strFilePath.bak", $strFilePath);
		}
	}
	
	
}

?>
