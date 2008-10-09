<?php

// This moves an account group into another account group
// It does not update the CDRInvoiced table.  CDRInvoiced.AccountGroup should be removed from the table anyway

class Cli_App_Merge_Account_Groups extends Cli
{
	const SWITCH_RECEIVING_ACCOUNT_GROUP	= "d";
	const SWITCH_DEPRECATED_ACCOUNT_GROUP	= "o";
	const SWITCH_REVERSAL_FILENAME			= "r";
	const SWITCH_TEST_MODE					= "t";
	

	function run()
	{
		try
		{
			
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Include the application... 
			$this->requireOnce("flex.require.php");
			$this->requireOnce("./lib/classes/Flex.php");
			
			Flex::load();

			$intReceivingAccountGroup	= $arrArgs[self::SWITCH_RECEIVING_ACCOUNT_GROUP];
			$intDeprecatedAccountGroup	= $arrArgs[self::SWITCH_DEPRECATED_ACCOUNT_GROUP];
			$strReversalFilename		= $arrArgs[self::SWITCH_REVERSAL_FILENAME];
			$bolTestMode				= $arrArgs[self::SWITCH_TEST_MODE];
			
			if ($intReceivingAccountGroup !== NULL && $intDeprecatedAccountGroup !== NULL && $strReversalFilename === NULL)
			{
				// A merge has been requested
				$this->merge($intReceivingAccountGroup, $intDeprecatedAccountGroup, $bolTestMode);
			}
			elseif ($intReceivingAccountGroup === NULL && $intDeprecatedAccountGroup === NULL && $strReversalFilename !== NULL)
			{
				// A merge reversal has been requested
				$this->reverse($strReversalFilename);
			}
			else
			{
				throw new Exception("Conflicting switches. Choose either (-". self::SWITCH_RECEIVING_ACCOUNT_GROUP ." AND -". self::SWITCH_DEPRECATED_ACCOUNT_GROUP .") OR (-". self::SWITCH_REVERSAL_FILENAME .")");
			}
			
			// Must have worked! Exit with 'OK' code 0
			return 0;
		}
		catch (Exception $exception)
		{
			$this->showUsage($exception->getMessage());
			return 1;  // Or should this be a negative number, or what?
		}
	}
	
	function merge($intReceivingAccountGroup, $intDeprecatedAccountGroup, $bolTestMode=FALSE)
	{
		try
		{
			$bolRecoveryFileEmpty = TRUE;
			$strReport = "Merging AccountGroup $intDeprecatedAccountGroup, into AccountGroup $intReceivingAccountGroup". (($bolTestMode)? "  (TEST MODE)" : "");
			$this->log($strReport, FALSE, FALSE, TRUE);
			
			$intTotalAffectedRecords = 0;
			$objDb		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
			$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
			
			// Check that both AccountGroups exist
			if (($objRecordSet = $qryQuery->Execute("SELECT COUNT(Id) AS NumOfAccountGroups FROM AccountGroup WHERE Id IN ($intReceivingAccountGroup, $intDeprecatedAccountGroup)")) === FALSE)
			{
				throw new Exception("Could not verify that both AccountGroups exist. ". $qryQuery->Error());
			}
			$arrResult = $objRecordSet->fetch_assoc();
			if ($arrResult['NumOfAccountGroups'] != 2)
			{
				throw new Exception("Could not find both the AccountGroups");
			}

			// Check that all the accounts belonging to the deprecatedAccountGroup are of the same CustomerGroup as all the Accounts belonging to the RecievingAccountGroup
			if (($objRecordSet = $qryQuery->Execute("SELECT COUNT(DISTINCT CustomerGroup) AS NumOfCustomerGroups FROM Account WHERE AccountGroup IN ($intReceivingAccountGroup, $intDeprecatedAccountGroup)")) === FALSE)
			{
				throw new Exception("Could not verify that all Accounts, belonging to the deprecated AccountGroup, are in the same CustomerGroup as the receiving AccountGroup. ". $qryQuery->Error());
			}
			$arrResult = $objRecordSet->fetch_assoc();
			if ($arrResult['NumOfCustomerGroups'] > 1)
			{
				throw new Exception("Accounts, within the two AccountGroups, belong to different CustomerGroups.  These Accounts should not be merged into one");
			}
			
			// Create the recovery file
			$strTimestamp				= date("Ymd_His");
			$strRecorveryFilename		= "reversal_for_merging_accountgroup_{$intDeprecatedAccountGroup}_into_{$intReceivingAccountGroup}_at_{$strTimestamp}.csv";
			$strRecorverySQLFilename	= "reversal_for_merging_accountgroup_{$intDeprecatedAccountGroup}_into_{$intReceivingAccountGroup}_at_{$strTimestamp}.sql";
			//$resRecoveryFile = fopen($strRecorveryFilename, "w");
			$resRecoverySQLFile = fopen($strRecorverySQLFilename, "w");
			
			/*if (fputcsv($resRecoveryFile, array("ReceivingAccountGroup", $intReceivingAccountGroup)) === FALSE)
			{
				throw new exception("Could not record the receiving account group, $intReceivingAccountGroup, into the recovery file");
			}
			if (fputcsv($resRecoveryFile, array("LosingAccountGroup", $intLosingAccountGroup)) === FALSE)
			{
				throw new exception("Could not record the losing account group, $intLosingAccountGroup, into the recovery file");
			}
			*/
			
			// First define any tables that reference the AccountGroup, using a field name other than "AccountGroup"
			// Currently there aren't any
			$arrTablesToUpdate = array();
			
			// Find all tables that make reference to the AccountGroup
			
			// Get all tables in the database
			$arrTables = $objDb->FetchAllTableDefinitions();
			foreach ($arrTables as $arrTableDef)
			{
				// It is assumed that there is at most only 1 reference to an AccountGroup within a table and it is named either "AccountGroup" or "account_group"
				if (array_key_exists("AccountGroup", $arrTableDef['Column']) || array_key_exists("account_group", $arrTableDef['Column']))
				{
					
					$arrTablesToUpdate[] = array(	"Table"					=> $arrTableDef['Name'],
													"IdColumn"				=> $arrTableDef['Id'],
													"AccountGroupColumn"	=> array_key_exists("AccountGroup", $arrTableDef['Column'])? "AccountGroup" : "account_group"
												);
				}
			}
		
			TransactionStart(FLEX_DATABASE_CONNECTION_ADMIN);
			
			// Update the tables
			foreach ($arrTablesToUpdate as $arrDetails)
			{
				$arrRecordsModified = array();
				
				// Store the id of each record that will be updated
				$strRecordsToUpdateQuery = "SELECT {$arrDetails['IdColumn']} AS IdColumn FROM {$arrDetails['Table']} WHERE {$arrDetails['AccountGroupColumn']} = $intDeprecatedAccountGroup";
				if (($objRecordSet = $qryQuery->Execute($strRecordsToUpdateQuery)) === FALSE)
				{
					// Something broke
					throw new Exception("Error occurred when executing query: $strRecordsToUpdateQuery - " . $qryQuery->Error());
				}
				
				while ($arrRecord = $objRecordSet->fetch_assoc())
				{
					$arrRecordsModified[] = $arrRecord['IdColumn'];
				}
				
				$intExpectedAffectedRowCount = count($arrRecordsModified);
				
				$this->log("Updating records in {$arrDetails['Table']} where {$arrDetails['AccountGroupColumn']} = $intDeprecatedAccountGroup...", FALSE, FALSE, TRUE);

				if (!$bolTestMode)
				{
					// Update the records
					$strUpdateQuery = "UPDATE {$arrDetails['Table']} SET {$arrDetails['AccountGroupColumn']} = $intReceivingAccountGroup WHERE {$arrDetails['AccountGroupColumn']} = $intDeprecatedAccountGroup";
					if ($qryQuery->Execute($strUpdateQuery) === FALSE)
					{
						// Something broke
						throw new Exception("Error occurred when executing query: $strUpdateQuery - " . $qryQuery->Error());
					}
					$intActualAffectedRowCount = $qryQuery->AffectedRows();
				}
				else
				{
					// Test Mode
					$intActualAffectedRowCount = $intExpectedAffectedRowCount;
				}
				
				if ($intExpectedAffectedRowCount !== $intActualAffectedRowCount)
				{
					throw new Exception("There is a discrepancy between the records we are recording as having been updated, and those that actually were, when updating the '{$arrDetails['Table']}' table.  Expected affected row count: $intExpectedAffectedRowCount.  Actual affected row count: $intActualAffectedRowCount.");
				}

				$intTotalAffectedRecords += $intActualAffectedRowCount;
				$this->log("\t$intActualAffectedRowCount records updated", FALSE, FALSE, TRUE);

				// Write the updated records to the recovery file
				$arrCSVRecord = array(	"Table"					=> $arrDetails['Table'], 
										"AccountGroupColumn"	=> $arrDetails['AccountGroupColumn'],
										"IdColumn"				=> $arrDetails['IdColumn']
										);
				foreach ($arrRecordsModified as $intId)
				{
					$arrCSVRecord['RecordId'] = $intId;
					/*if (fputcsv($resRecoveryFile, $arrCSVRecord) === FALSE)
					{
						throw new exception("Could not record reference to the updating of {$arrDetails['Table']}.{$arrDetails['AccountGroupColumn']} for record with Id {$intId}");
					}*/
					$strReversalQuery = "UPDATE {$arrDetails['Table']} SET {$arrDetails['AccountGroupColumn']} = $intDeprecatedAccountGroup WHERE {$arrDetails['IdColumn']} = $intId;";
					if ($bolRecoveryFileEmpty)
					{
						// Must be the first record
						$bolRecoveryFileEmpty = FALSE;
					}
					else
					{
						// Prepend a new line character to the line
						$strReversalQuery = "\n". $strReversalQuery;
					}
					if (fwrite($resRecoverySQLFile, $strReversalQuery) === FALSE)
					{
						throw new exception("Could not record reference to the updating of {$arrDetails['Table']}.{$arrDetails['AccountGroupColumn']} for record with Id {$intId} in the recorvery file");
					}
				}
			}
			
			//fclose($resRecoveryFile);
			fclose($resRecoverySQLFile);

			// Commit the transactions
			TransactionCommit(FLEX_DATABASE_CONNECTION_ADMIN);
			
			if ($bolTestMode)
			{
				$strReport = "Testing of AccountGroup merge completed successfully.  $intTotalAffectedRecords records would have been modified.";
			}
			else
			{
				$strReport = "Merge completed successfully.  $intTotalAffectedRecords records were updated";
			}
			
			$this->log($strReport, FALSE, FALSE, TRUE);
			$this->log("Created reversal file: $strRecorverySQLFilename\n", FALSE, FALSE, TRUE);
		}
		catch (Exception $e)
		{
			TransactionRollback(FLEX_DATABASE_CONNECTION_ADMIN);
			
			// Discard the reversal files
			/*if (isset($resRecoveryFile) && $resRecoveryFile !== FALSE)
			{
				unlink($strRecorveryFilename);
			}
			*/
			
			if (isset($resRecoverySQLFile) && $resRecoverySQLFile !== FALSE)
			{
				unlink($strRecorverySQLFilename);
			}
			
			throw new Exception("Merging of AccountGroup $intDeprecatedAccountGroup into $intReceivingAccountGroup failed.  ". $e->getMessage());
		}
	}
	
	function reverse($strReversalFilename)
	{
		try
		{
			$this->log("Reversing AccountGroup Merge", FALSE, FALSE, TRUE);
			
			$intCurrentFileLine = 0;
			$intTotalAffectedRecords = 0;
			$resRecoverySQLFile = fopen($strReversalFilename, "r");
			
			if ($resRecoverySQLFile === FALSE)
			{
				throw new Exception("Could not open recovery file");
			}
			TransactionStart(FLEX_DATABASE_CONNECTION_ADMIN);
			
			$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
			while (!feof($resRecoverySQLFile))
			{
				$intCurrentFileLine++;
				if (($strQuery = fgets($resRecoverySQLFile)) === FALSE)
				{
					throw new Exception("Failed to read line $intCurrentFileLine from recovery file");
				}
				$strQuery = trim($strQuery);
				if ($strQuery == "")
				{
					// A blank line
					continue;
				}
				
				if ($qryQuery->Execute($strQuery) === FALSE)
				{
					throw new exception("The query: $strQuery located on line $intCurrentFileLine of the recovery file, failed - ". $qryQuery->Error());
				}
				
				$intTotalAffectedRecords += $qryQuery->AffectedRows();
	        }
			
			$this->log("Reversal completed successfully.  $intTotalAffectedRecords records were updated\n", FALSE, FALSE, TRUE);
			TransactionCommit(FLEX_DATABASE_CONNECTION_ADMIN);
			fclose($resRecoverySQLFile);
		}
		catch (Exception $e)
		{
			TransactionRollback(FLEX_DATABASE_CONNECTION_ADMIN);
			if (isset($resRecoverySQLFile) && $resRecoverySQLFile !== FALSE)
			{
				fclose($resRecoverySQLFile);
			}
			
			throw new Exception("Reversing AccountGroup merge failed.  ". $e->getMessage());
		}
	}
	

	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_RECEIVING_ACCOUNT_GROUP => array(
				self::ARG_LABEL 		=> "RECEIVING_ACCOUNT_GROUP",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "Account Group that will be recieving all accounts belonging to the deprecated account group",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_DEPRECATED_ACCOUNT_GROUP => array(
				self::ARG_LABEL 		=> "DEPRECATED_ACCOUNT_GROUP",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "Account Group that will have its accounts moved to the receiving account group",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_REVERSAL_FILENAME 	=> array(
				self::ARG_LABEL 		=> "REVERSAL_FILENAME",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "reverse changes (you will need to specify the Reversal filename)",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validFileNameWithOptionalExtension("%1$s")'
			),
			
			self::SWITCH_TEST_MODE 	=> array(
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "switch to execute merge in test mode.  No records will be modified, but a reversal file script is still made",
				self::ARG_DEFAULT 		=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			)
		);
		return $commandLineArguments;
	}

}

?>
