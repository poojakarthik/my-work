<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../lib/classes/Flex.php");
Flex::load();
require_once('Flex_Rollout_Version.php');

class Flex_Rollout
{

	public static function updateToLatestVersion($intVersion=NULL, $bolTestOnly=FALSE)
	{
		//unset($GLOBALS['**arrDatabase']['cdr']);
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

		$ccVersionNumber = 32;

		$errors = array();

		// We always want to update the data model, as this ensures a model exists
		for ($i = 0; $i < $nrConnections; $i++)
		{
			try
			{
				@mysqli_report(MYSQLI_REPORT_ERROR);
				//if ($arrConnectionNames[$i] == FLEX_DATABASE_CONNECTION_CDR) continue;
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
		
		// We always want to update the credit_card_details.js file
		// Rebuild the credit_card_details.js file
		if ($intVersion >= $ccVersionNumber)
		{
			try
			{
				self::GenerateCreditCardDetailsJS();
			}
			catch (Exception $e)
			{
				// Error occurred.  All rollback actions have been performed
				$errors[] = "WARNING: Failed to build new credit_card_details.js file.";
			}
		}

		$errors = implode("\n", $errors);

		if ($errors)
		{
			throw new Exception("Errors occurred: ".$errors);
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
				throw new Exception("Rollout failed to $step database '$dbName' prior to starting: " . $e->getMessage());
			}
		}

		try
		{
			// Roll out each change in order
			for($index = 0; $index < $nrVersions; $index++)
			{
				if ($versions[$index] >= Flex_Rollout_Version::NEW_SYSTEM_CUTOVER)
				{
					break;
				}
				$arrVersions[$versions[$index]]->rollout();
			}
			$index--;

			// Using the default database connection, update the database version number
			$arrValues = array(
				'version' => $versions[$index],
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
		$intEndVersion = $intVersion;
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
					$intEndVersion++;
				}
			}
			catch (Exception $e)
			{
				$errors[] = "ERROR: Rollout failed to commit changes to db " . $arrConnectionNames[$i] . ": " . $e->getMessage();
			}

			try
			{
				@mysqli_report(MYSQLI_REPORT_ERROR);
				//if ($arrConnectionNames[$i] == FLEX_DATABASE_CONNECTION_CDR) continue;
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

		// Rebuild the credit_card_details.js file
		if ($intEndVersion >= $ccVersionNumber)
		{
			try
			{
				self::GenerateCreditCardDetailsJS();
			}
			catch (Exception $e)
			{
				// Error occurred.  All rollback actions have been performed
				$errors[] = "WARNING: Failed to build new credit_card_details.js file.";
			}
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
			throw new Exception("Failed to retrieve tables of the '$strDataSource' database: " . $qryQuery->Error());
		}
		
		// For each table of the database, check if constants should be built for it
		$arrConstantGroups = array();
		while ($arrTable = $objTables->fetch_assoc())
		{
			$strTable	= current($arrTable);
			$strQuery	= "SHOW COLUMNS FROM $strTable WHERE LCASE(Field) IN ('id', 'const_name', 'description', 'name')";
			$objColumns	= $qryQuery->Execute($strQuery);
			
			if (!$objColumns)
			{
				throw new Exception("Failed to retrieve column listing for the '$strTable' table of the '$strDataSource' database: " . $qryQuery->Error());
			}
			
			// Check if it has id AND const_name AND description
			$intRequiredColumns	= 0;
			$strId				= NULL;
			$strConstName		= NULL;
			$strDescription		= NULL;
			$strName			= NULL;
			
			while ($arrColumn = $objColumns->fetch_assoc())
			{
				if (strtolower($arrColumn['Field']) == "id") 
				{
					// Save the case sensitive id field
					$strId = $arrColumn['Field'];
					$intRequiredColumns++;
				}
				if (strtolower($arrColumn['Field']) == "description") 
				{
					// Save the case sensitive description field
					$strDescription = $arrColumn['Field'];
					$intRequiredColumns++;
				}
				if ($arrColumn['Field'] == "const_name")
				{
					// Save the case sensitive const_name field
					$strConstName = $arrColumn['Field'];
					$intRequiredColumns++;
				}
				if (strtolower($arrColumn['Field']) == "name")
				{
					// Save the case sensitive name field
					$strName = $arrColumn['Field'];
				}
			}
			
			if ($intRequiredColumns != 3)
			{
				// This table does not have all three of the id, name and description columns
				// Don't build constant declarations for it
				continue;
			}
			
			// If there was no acceptable "name" column, then use the description column
			if ($strName === NULL)
			{
				$strName = $strDescription;
			}
			
			// The table has all 3 columns, which means we should convert it to constant declarations
			// Retrieve the values and create a constant group
			$strQuery		= "SELECT $strId 'id', const_name 'const_name', $strDescription 'description', $strName 'name' FROM $strTable WHERE const_name IS NOT NULL AND const_name != '' ORDER BY $strId ASC";

			$objRecordSet	= $qryQuery->Execute($strQuery);
			
			if (!$objRecordSet)
			{
				throw new Exception("Failed to retrieve the contents of the '$strTable' table of the '$strDataSource' database: " . $qryQuery->Error());
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
																'Description'	=> $arrRecord['description'],
																'Name'			=> $arrRecord['name']
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
\$GLOBALS['*arrConstant']\t['$strConstantGroupName']\t[$mixValue]\t['Name']\t\t= '{$arrConstant['Name']}';
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
	
	public static function GenerateCreditCardDetailsJS()
	{
		$strDataSource = "flex";
		
		// Retrieve a list of all tables in the database
		$qryQuery	= new StatementSelect('credit_card_type', array('id', 'name', 'description', 'const_name', 'surcharge', 'valid_lengths', 'valid_prefixes', 'cvv_length', 'minimum_amount', 'maximum_amount'));
		$nrCreditCards	= $qryQuery->Execute();
		if ($nrCreditCards === FALSE)
		{
			throw new Exception("Failed to retrieve credit card type details from database: " . $qryQuery->Error());
		}
		$ccDetails = $qryQuery->FetchAll();
		$js = "
CreditCardType = Class.create();
Object.extend(CreditCardType, 
{
	types: [\n";
		$prefixTypes = "";
		$idTypes = "";
		$prefixLengths = array();
		$maxCVV = 0;
		$minCVV = 999;
		$maxCardLength = 0;
		$minCardLength = 999;
		$minPrefixLength = 999;
		for ($i = 0; $i < $nrCreditCards; $i++)
		{
			$d = $ccDetails[$i];
			$js .= $i ? ",\n" : '';
			$prefixes = explode(',', $ccDetails[$i]['valid_prefixes']);
			$lengths = explode(',', $ccDetails[$i]['valid_lengths']);
			$js .= "		{ id: " . $d['id'] . ", name: '" . $d['name'] . "', description: '" . $d['description'] . "'" 
						. ", const_name: '" . $d['const_name'] . "', surcharge: " . $d['surcharge'] . ", valid_prefixes: ['" . implode("','", $prefixes) . "']" 
						. ", valid_lengths: [" . $d['valid_lengths'] . "], cvv_length: " . $d['cvv_length'] 
						. ", minimum_amount: " . $d['minimum_amount'] . ", maximum_amount: " . $d['maximum_amount'] . "}";

			$idTypes .= ($idTypes ? ",\n\t\t\t  " : '') . 'ID_' . $d['id'] . ": $i";

			$prefixTypes .= ($prefixTypes ? ",\n\t\t\t\t  " : '') . 'PREFIX_' . implode(": $i,\n\t\t\t\t  PREFIX_", $prefixes) . ": $i";
			foreach($prefixes as $prefix)
			{
				$prefixLengths[] = strlen($prefix);
			}
			$maxCVV = max($maxCVV, $d['cvv_length']);
			$minCVV = min($minCVV, $d['cvv_length']);
			$maxCardLength = max($maxCardLength, max($lengths));
			$minCardLength = min($minCardLength, min($lengths));
			$minPrefixLength = min($minPrefixLength, min($prefixLengths));
		}
		$prefixLengths = array_unique($prefixLengths);
		asort($prefixLengths);
		$js .= "
	],

	minCvvLength: $minCVV,

	maxCvvLength: $maxCVV,

	minCardNumberLength: $minCardLength,

	maxCardNumberLength: $maxCardLength,

	minPrefixLength: $minPrefixLength,

	prefixLengths: [" . implode(',', $prefixLengths) . "],

	prefixTypes: {{$prefixTypes}},

	idTypes: {{$idTypes}},

	cardTypeForNumber: function(cardNumber)
	{
		var cardNumberLen = cardNumber.length;
		for (var i = 0; i < " . count($prefixLengths) . "; i++)
		{
			var len = CreditCardType.prefixLengths[i];
			if (cardNumberLen < len) break;
			var type = CreditCardType.cardTypeForPrefix(cardNumber.substr(0, len));
			if (type) return type;
		}
		return false;
	},

	cardTypeForPrefix: function(prefix)
	{
		if (typeof CreditCardType.prefixTypes['PREFIX_' + prefix] == 'undefined')
		{
			return false;
		}
		return CreditCardType.types[CreditCardType.prefixTypes['PREFIX_' + prefix]];
	},

	cardTypeForId: function(id)
	{
		if (typeof CreditCardType.idTypes['ID_' + id] == 'undefined')
		{
			return false;
		}
		return CreditCardType.types[CreditCardType.idTypes['ID_' + id]];
	},

	indexOfType: function(type)
	{
		return CreditCardType.idTypes['ID_' + type['id']];
	}
});\n";

		$strFilePath	= GetVixenBase() . 'html' . DIRECTORY_SEPARATOR ."ui". DIRECTORY_SEPARATOR ."javascript". DIRECTORY_SEPARATOR ."credit_card_type.js";
		$jsFile = fopen($strFilePath, "w");
		fwrite($jsFile, $js);
		fclose($jsFile);
	}
}

?>
