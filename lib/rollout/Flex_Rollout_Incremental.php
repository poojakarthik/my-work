<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../lib/classes/Flex.php");
Flex::load();
require_once('Flex_Rollout_Version.php');



class Flex_Rollout_Incremental
{
	public function updateToLatestVersion($nextVersionOnly=FALSE, $bolTestOnly=FALSE)
	{
		// Find the available updates
		$db = Data_Source::get();

		$sql = "SELECT MAX(version) FROM database_version";
		$res = $db->query($sql);
		
		if (PEAR::isError($res))
		{
			throw new Exception("Failed to find latest database version: " . $res->getMessage());
		}

		$currentVersion = intval($res->fetchOne());

		$arrVersions = self::_getVersionsAfter($currentVersion);

		foreach ($arrVersions as $intVersion => $objRollout)
		{
			// Don't run old style rollouts with the new script
			if ($intVersion < Flex_Rollout_Version::NEW_SYSTEM_CUTOVER)
			{
				throw new NonIncrementalRolloutException("Rollouts for the old rollout system remain unapplied ($intVersion). Apply those before running this newer rollout system.");
			}



			try
			{
				// Try to generate the constants file so that there is always one for the rollout scripts
				self::GenerateDatabaseConstantsFile();
			}
			catch(Exception $e)
			{
				throw new Exception("Failed to build constants file prior to attempting rollout $intVersion. Current version is $currentVersion.");
			}



			try
			{
				// Begin transactions for each of the configurred data sources
				self::beginTransactions();
			}
			catch (Exception $e)
			{
				@self::RollbackDatabaseConstantsFile();
				throw new Exception("Failed to begin database transactions prior to attempting rollout $intVersion. Current version is $currentVersion.");
			}



			try
			{
				// Try to rollout the script (if this fails, we must roll-back script & db changes)
				$objRollout->rollout();

				// Update the database_version table with the latest version number (insert a new record)
				$strSQL = "INSERT INTO database_version (version, rolled_out_date) VALUES ($intVersion, '" . date("Y-m-d H:i:s") . "')";
				$res = $db->query($strSQL);
				if (PEAR::isError($res))
				{
					throw new Exception("Failed to update database_version table: " . $res->getMessage());
				}

				// Commit the database changes (if this fails, we must roll-back script & db changes)
				if (!$bolTestOnly)
				{
					self::commitTransactions();
				}
			}
			catch (Exception $e)
			{
				$arrRollbackErrors = array();

				try
				{
					// Rollback the database changes
					self::rollbackTransactions();
				}
				catch (Exception $ex)
				{
					$arrRollbackErrors[] = "Failed to roll-back database changes: " . $ex->getMessage();
				}

				try
				{
					// Attempt to rollback the script to keep it in sync with the constants file
					$objRollout->rollback();
				}
				catch (Exception $ex)
				{
					$arrRollbackErrors[] = "Failed to roll-back script: " . $ex->getMessage();
				}

				if (!count($arrRollbackErrors))
				{
					$arrRollbackErrors[] = "Rolled back to version $currentVersion.";
				}

				@self::RollbackDatabaseConstantsFile();
				array_unshift($arrRollbackErrors, "Failed to rollout to version $intVersion: " . $e->getMessage());
				throw new Exception(implode("\n", $arrRollbackErrors));
			}


			try
			{
				// Commit the script (this should be a tidy-up operation only. failure is non-critical and does not require a roll-back)
				if (!$bolTestOnly)
				{
					$objRollout->commit();
				}
			}
			catch (Exception $e)
			{
				throw new Exception("Rollout $intVersion worked but ran into problems when running the tidy-up 'commit' function. \n" . 
									"Make any necessary changes manually and then re-run rollout. \n" . 
									"The 'commit' function reported the following message: \n" .
									$e->getMessage());
			}

			if ($bolTestOnly)
			{
				$nextVersionOnly = TRUE;
				// Rollback all changes made and exit.
				// We can only try one test at a time with this incremental stuff!
				$strRollbackFailMessage = "";
				try
				{
					self::rollbackTransactions();
				}
				catch (Exception $e)
				{
					$strRollbackFailMessage .= "Failed to roll-back database changes after test: " . $e->getMessage();
				}

				try
				{
					$objRollout->rollback();
				}
				catch (Exception $e)
				{
					$strRollbackFailMessage .= "Failed to roll-back non-database changes after test: " . $e->getMessage();
				}

				@self::RollbackDatabaseConstantsFile();
				
				if ($strRollbackFailMessage)
				{
					throw new Exception($strRollbackFailMessage);
				}
				
				break;
			}
			
			$currentVersion = $intVersion;
			
			if ($nextVersionOnly)
			{
				break;
			}
		}


		try
		{
			self::GenerateCreditCardDetailsJS();
		}
		catch (Exception $e)
		{
			throw new Exception("Failed to create credit card js file: " . $e->getMessage());
		}


		try
		{
			self::GenerateDatabaseConstantsFile();
		}
		catch (Exception $e)
		{
			throw new Exception("Failed to re-generate constants file (i): " . $e->getMessage());
		}


		// We always want to update the data model, as this ensures a model exists
		$arrConnectionNames = array_keys($GLOBALS['**arrDatabase']);
		for ($i = 0, $l = count($arrConnectionNames); $i < $l; $i++)
		{
			try
			{
				//if ($arrConnectionNames[$i] == FLEX_DATABASE_CONNECTION_CDR) continue;
				if ($GLOBALS['**arrDatabase'][$arrConnectionNames[$i]]['DataModel'] !== FALSE)
				{
					Flex_Data_Model::generateDataModelForDatabase($arrConnectionNames[$i]);
				}
			}
			catch (Exception $e)
			{
				throw new Exception("Rollout failed to generate new data model for data source '" . $arrConnectionNames[$i] . "'.\nThis must be resolved manually (or by re-running rollout).\n" . $e->getMessage());
			}
		}
	}

	private static function getDataSources()
	{
		static $dataSources;
		if (!$dataSources)
		{
			$dataSources = array();
			$arrConnectionNames = array_keys($GLOBALS['**arrDatabase']);
			foreach ($arrConnectionNames as $strConnectionName)
			{
				$dataSources[$strConnectionName] = Data_Source::get($strConnectionName);
			}
		}
		return $dataSources;
	}
	
	private static function beginTransactions()
	{
		$dataSources = self::getDataSources();
		$errors = array();
		foreach ($dataSources as $name => $dataSource)
		{
			$res = $dataSource->beginTransaction();
			if (PEAR::isError($res))
			{
				$errors[] = "Failed to begin transaction for data source '$name': " . $res->getMessage();
			}
		}
		if (count($errors))
		{
			throw new Exception(implode("\n", $errors));
		}
	}

	private static function commitTransactions()
	{
		$dataSources = self::getDataSources();
		$errors = array();
		foreach ($dataSources as $name => $dataSource)
		{
			if ($dataSource->inTransaction())
			{
				$res = $dataSource->commit();
				if (PEAR::isError($res))
				{
					$errors[] = "Failed to commit transaction for data source '$name': " . $res->getMessage();
				}
			}
		}
		if (count($errors))
		{
			throw new Exception(implode("\n", $errors));
		}
	}

	private static function rollbackTransactions()
	{
		$dataSources = self::getDataSources();
		$errors = array();
		foreach ($dataSources as $name => $dataSource)
		{
			if ($dataSource->inTransaction())
			{
				$res = $dataSource->rollback();
				if (PEAR::isError($res))
				{
					$errors[] = "Failed to roll-back transaction for data source '$name': " . $res->getMessage();
				}
			}
		}
		if (count($errors))
		{
			throw new Exception(implode("\n", $errors));
		}
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
		try 
		{
			$dataSource = Data_Source::get();
			$dataSource->loadModule('Manager');
	
			$tables = $dataSource->manager->listTables();
			
			if (PEAR::isError($tables))
			{
				throw new Exception("Failed to list tables when building DB constants: " . $tables->getMessage());
			}
	
			foreach ($tables as $strTable)
			{
				$arrColumns = $dataSource->manager->listTableFields($strTable);
	
				if (PEAR::isError($arrColumns))
				{
					throw new Exception("Failed to retrieve column listing for the '$strTable' table: " . $arrColumns->getMessage());
				}
				
				// Check if it has id AND const_name AND description
				$intRequiredColumns	= 0;
				$strId				= NULL;
				$strConstName		= NULL;
				$strDescription		= NULL;
				$strName			= NULL;
				
				foreach ($arrColumns as $strColumn)
				{
					if (strtolower($strColumn) == "id") 
					{
						// Save the case sensitive id field
						$strId = $strColumn;
						$intRequiredColumns++;
					}
					if (strtolower($strColumn) == "description") 
					{
						// Save the case sensitive description field
						$strDescription = $strColumn;
						$intRequiredColumns++;
					}
					if (strtolower($strColumn) == "const_name")
					{
						// Save the case sensitive const_name field
						$strConstName = $strColumn;
						$intRequiredColumns++;
					}
					if (strtolower($strColumn) == "name")
					{
						// Save the case sensitive name field
						$strName = $strColumn;
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
				$strQuery		= "SELECT $strId as 'id', $strConstName as 'const_name', $strDescription as 'description', $strName as 'name' FROM $strTable WHERE $strConstName IS NOT NULL AND const_name != '' ORDER BY $strId ASC";
	
				$mxdResult	= $dataSource->query($strQuery);
				
				if (PEAR::isError($mxdResult))
				{
					throw new Exception("Failed to retrieve the contents of the '$strTable' table: " . $mxdResult->getMessage());
				}
				
				// Build the constant group
				$arrResults = $mxdResult->fetchAll(MDB2_FETCHMODE_ASSOC);
				$arrConstantGroup		= array();
				$arrUsedConstantNames	= array();
				foreach ($arrResults as $arrRecord)
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
		catch (Exception $e)
		{
			@self::RollbackDatabaseConstantsFile();
			throw $e;
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
		$db = Data_Source::get();
		
		// Retrieve a list of all tables in the database
		$sql = "SELECT id, name, description, const_name, surcharge, valid_lengths, valid_prefixes, cvv_length, minimum_amount, maximum_amount FROM credit_card_type";
		$creditCards = $db->query($sql);

		if (PEAR::isError($creditCards))
		{
			throw new Exception("Failed to retrieve credit card type details from database: " . $creditCards->getMessage());
		}
		$ccDetails = $creditCards->fetchAll(MDB2_FETCHMODE_ASSOC);
		$nrCreditCards = count($ccDetails);

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


class NonIncrementalRolloutException extends Exception{}

?>
