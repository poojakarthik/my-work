<?php

// This modifies all records referencing the deprecated contact record, so that they now reference the receiving contact record

class Cli_App_Merge_Contacts extends Cli
{
	const SWITCH_RECEIVING_CONTACT	= "d";
	const SWITCH_DEPRECATED_CONTACT	= "o";
	const SWITCH_REVERSAL_FILENAME	= "r";
	const SWITCH_TEST_MODE			= "t";

	protected $_arrPropertiesToUpdate = array(	0	=> array(
															"Table"			=> "Account",
															"IdColumn"		=> "Id",
															"ContactColumn"	=> "PrimaryContact"
															),
												1	=> array(
															"Table"			=> "EmployeeAccountAudit",
															"IdColumn"		=> "Id",
															"ContactColumn"	=> "Contact"
															),
												2	=> array(
															"Table"			=> "Note",
															"IdColumn"		=> "Id",
															"ContactColumn"	=> "Contact"
															),
												3	=> array(
															"Table"			=> "credit_card_payment_history",
															"IdColumn"		=> "id",
															"ContactColumn"	=> "contact_id"
															),
												4	=> array(
															"Table"			=> "survey_completed",
															"IdColumn"		=> "id",
															"ContactColumn"	=> "contact_id"
															)
											);

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

			$intReceivingContactId	= $arrArgs[self::SWITCH_RECEIVING_CONTACT];
			$intDeprecatedContactId	= $arrArgs[self::SWITCH_DEPRECATED_CONTACT];
			$strReversalFilename	= $arrArgs[self::SWITCH_REVERSAL_FILENAME];
			$bolTestMode			= $arrArgs[self::SWITCH_TEST_MODE];
			
			if ($intReceivingContactId !== NULL && $intDeprecatedContactId !== NULL && $strReversalFilename === NULL)
			{
				// A merge has been requested
				$this->merge($intReceivingContactId, $intDeprecatedContactId, $bolTestMode);
			}
			elseif ($intReceivingContactId === NULL && $intDeprecatedContactId === NULL && $strReversalFilename !== NULL)
			{
				// A merge reversal has been requested
				$this->reverse($strReversalFilename);
			}
			else
			{
				throw new Exception("Conflicting switches. Choose either (-". self::SWITCH_RECEIVING_CONTACT ." AND -". self::SWITCH_DEPRECATED_CONTACT .") OR (-". self::SWITCH_REVERSAL_FILENAME .")");
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
	
	function merge($intReceivingContactId, $intDeprecatedContactId, $bolTestMode=FALSE)
	{
		try
		{
			$intTotalAffectedRecords	= 0;
			$qryQuery					= new Query();
			
			// Check that both contacts exist and they have the same name and AccountGroup
			$objReceivingContact	= Contact::getForId($intReceivingContactId);
			if ($objReceivingContact === NULL)
			{
				throw new Exception("Contact with id: $intReceivingContactId, could not be found");
			}
			$objDeprecatedContact	= Contact::getForId($intDeprecatedContactId);
			if ($objDeprecatedContact === NULL)
			{
				throw new Exception("Contact with id: $intDeprecatedContactId, could not be found");
			}
			
			if ($objReceivingContact->getName() !== $objDeprecatedContact->getName())
			{
				throw new Exception("The contacts have differing names");
			}
			
			if ($objReceivingContact->accountGroup !== $objDeprecatedContact->accountGroup)
			{
				throw new Exception("The contacts belong to different AccountGroups");
			}
			
			$strContactName = $objReceivingContact->getName();
			$strReport = "Merging Contact $intDeprecatedContactId, into Contact $intReceivingContactId for contact with name '$strContactName'". (($bolTestMode)? "  (TEST MODE)" : "");
			$this->log($strReport, FALSE, FALSE, TRUE);
			
			// Create the recovery file
			$strTimestamp				= date("Ymd_His");
			$strRecorverySQLFilename	= "reversal_for_merging_contact_{$intDeprecatedContactId}_into_{$intReceivingContactId}_at_{$strTimestamp}.sql";
			$resRecoverySQLFile			= fopen($strRecorverySQLFilename, "w");
			if ($resRecoverySQLFile === FALSE)
			{
				throw new exception("Could not create recovery file, $strRecorverySQLFilename");
			}

			TransactionStart();
			
			// Update the records that require updating
			foreach ($this->_arrPropertiesToUpdate as $arrDetails)
			{
				$arrRecordsModified = array();
				
				// Store the id of each record that will be updated
				$strRecordsToUpdateQuery = "SELECT {$arrDetails['IdColumn']} AS IdColumn FROM {$arrDetails['Table']} WHERE {$arrDetails['ContactColumn']} = $intDeprecatedContactId";
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
				
				$this->log("Updating records in {$arrDetails['Table']} where {$arrDetails['ContactColumn']} = $intDeprecatedContactId...", FALSE, FALSE, TRUE);

				if (!$bolTestMode)
				{
					// Update the records
					$strUpdateQuery = "UPDATE {$arrDetails['Table']} SET {$arrDetails['ContactColumn']} = $intReceivingContactId WHERE {$arrDetails['ContactColumn']} = $intDeprecatedContactId;";
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
				foreach ($arrRecordsModified as $intId)
				{
					$strReversalQuery = "UPDATE {$arrDetails['Table']} SET {$arrDetails['ContactColumn']} = $intDeprecatedContactId WHERE {$arrDetails['IdColumn']} = $intId AND {$arrDetails['ContactColumn']} = $intReceivingContactId;";
					$this->_addLineToRecoveryFile($strReversalQuery, $resRecoverySQLFile);
				}
			}
			
			// Set the CustomerContact flag of the receiving Contact to 1, if it isn't already set
			// (don't worry about rolling this back in the recovery file)
			$this->log("Setting receiving Contact.CustomerContact to TRUE", FALSE, FALSE, TRUE);
			$objReceivingContact->customerContact = 1;
			$intTotalAffectedRecords++;
			if (!$bolTestMode)
			{
				if ($objReceivingContact->save() === FALSE)
				{
					throw new Exception("Failed to update the customerContact property for the receiving contact record");
				}
			}
			
			// Add a 'Contact' Note to the receiving contact detailing all details of the deprecated contact record, so that they can still be used, if they differ
			$this->log("Adding System Note detailing contact details that cannot be merged into the receiving contact record", FALSE, FALSE, TRUE);
			$objAccount = Account::getForId($objDeprecatedContact->account);
			$strDeprecatedContactsDefaultAccountName = ($objAccount !== NULL)? $objAccount->getName() : "";
			$strNote =	"Contact Record: $intDeprecatedContactId has been absorbed into this contact ".
						"($strContactName, id: $intReceivingContactId) because they logically represented ".
						"the same person. The contact details of the deprecated contact record are as follows:\n".
						"Default Account: $strDeprecatedContactsDefaultAccountName (id: {$objDeprecatedContact->account})\n".
						"Email: {$objDeprecatedContact->email}\n".
						"Phone: {$objDeprecatedContact->phone}\n".
						"Mobile: {$objDeprecatedContact->mobile}\n".
						"Fax: {$objDeprecatedContact->fax}\n".
						"Job Title: {$objDeprecatedContact->jobTitle}\n".
						"Status: ". (($objDeprecatedContact->archived)? "archived" : "active");
			if (!$bolTestMode)
			{
				$intNoteId = $GLOBALS['fwkFramework']->AddNote($strNote, Note::SYSTEM_NOTE_TYPE_ID, USER_ID, $objReceivingContact->accountGroup, $objReceivingContact->account, NULL, $objReceivingContact->id, TRUE);
				if ($intNoteId === FALSE)
				{
					throw new Exception("Could not add note to receiving contact, defining the contact's contact details that could not be merged (ie phone, mobile, email, etc)");
				}
				
				$intTotalAffectedRecords++;
				
				// Create SQL DELETE command to remove the note (this shouldn't be added to the recovery file if we are in test mode)
				$strDeleteSystemNote = "DELETE FROM Note WHERE Id = $intNoteId AND Contact = {$objReceivingContact->id} AND AccountGroup = {$objReceivingContact->accountGroup};";
	
				$this->_addLineToRecoveryFile($strDeleteSystemNote, $resRecoverySQLFile);
			}

			// Nullify important properties of the deprecated contact record (frees its association with an AccountGroup, Account, email address, name, etc)
			$this->log("Nullifing the identifying properties of the deprecated contact record (not including the Id property)", FALSE, FALSE, TRUE);
			$arrProps = array(	"AccountGroup"	=> array(	"NewValue"	=> 0,
															"OldValue"	=> $objDeprecatedContact->accountGroup
														),
								"Title"			=> array(	"NewValue"	=> NULL,
															"OldValue"	=> ($objDeprecatedContact->title === NULL)? "NULL" : "'". $qryQuery->EscapeString($objDeprecatedContact->title) ."'"
														),
								"FirstName"		=> array(	"NewValue"	=> "",
															"OldValue"	=> "'". $qryQuery->EscapeString($objDeprecatedContact->firstName) ."'"
														),
								"LastName"		=> array(	"NewValue"	=> "",
															"OldValue"	=> "'". $qryQuery->EscapeString($objDeprecatedContact->lastName) ."'"
														),
								"JobTitle"		=> array(	"NewValue"	=> "",
															"OldValue"	=> "'". $qryQuery->EscapeString($objDeprecatedContact->jobTitle) ."'"
														),
								"Email"			=> array(	"NewValue"	=> NULL,
															"OldValue"	=> ($objDeprecatedContact->email === NULL)? "NULL" : "'". $qryQuery->EscapeString($objDeprecatedContact->email) ."'"
														),
								"Account"		=> array(	"NewValue"	=> 0,
															"OldValue"	=> $objDeprecatedContact->account
														),
								"Phone"			=> array(	"NewValue"	=> "",
															"OldValue"	=> "'". $qryQuery->EscapeString($objDeprecatedContact->phone) ."'"
														),
								"Mobile"		=> array(	"NewValue"	=> "",
															"OldValue"	=> "'". $qryQuery->EscapeString($objDeprecatedContact->mobile) ."'"
														),
								"Fax"			=> array(	"NewValue"	=> "",
															"OldValue"	=> "'". $qryQuery->EscapeString($objDeprecatedContact->fax) ."'"
														)
							);
			
			$arrSetClause = array();
			foreach ($arrProps as $strProp=>$arrProp)
			{
				// Update the property in the Contact object
				$objDeprecatedContact->{$strProp} = $arrProp['NewValue'];
				
				// Build the UPDATE-SET clause parts
				$arrSetClauseParts[] = "$strProp = {$arrProp['OldValue']}";
			}
			$strSetClause = implode(", ", $arrSetClauseParts);
			$strRecoverDeprecatedContactRecord = "UPDATE Contact SET $strSetClause WHERE Id = $intDeprecatedContactId;";
			
			if (!$bolTestMode)
			{
				if ($objDeprecatedContact->save() === FALSE)
				{
					throw new Exception("Failed to Nullify the identifying properties of the deprecated contact record (not including the Id property)");
				}
			}
			$intTotalAffectedRecords++;
			
			$this->_addLineToRecoveryFile($strRecoverDeprecatedContactRecord, $resRecoverySQLFile);

			fclose($resRecoverySQLFile);
			
			if ($bolTestMode)
			{
				// Rollback the transaction (although there shouldn't be anything to rollback)
				TransactionRollback();
				$strReport = "Testing of Contact merge completed successfully.  $intTotalAffectedRecords records would have been modified.";
			}
			else
			{
				// Commit the transaction
				TransactionCommit();
				$strReport = "Merge completed successfully.  $intTotalAffectedRecords records were updated";
			}
			
			$this->log($strReport, FALSE, FALSE, TRUE);
			$this->log("Created reversal file: $strRecorverySQLFilename\n", FALSE, FALSE, TRUE);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			
			if (isset($resRecoverySQLFile) && $resRecoverySQLFile !== FALSE)
			{
				unlink($strRecorverySQLFilename);
			}
			
			throw new Exception("Merging of Contacts $intDeprecatedContactId into $intReceivingContactId failed.  ". $e->getMessage());
		}
	}
	
	function reverse($strReversalFilename)
	{
		try
		{
			$this->log("Reversing Contact Merge", FALSE, FALSE, TRUE);
			
			$intCurrentFileLine = 0;
			$intTotalAffectedRecords = 0;
			$resRecoverySQLFile = fopen($strReversalFilename, "r");
			
			if ($resRecoverySQLFile === FALSE)
			{
				throw new Exception("Could not open recovery file");
			}
			TransactionStart();
			
			$qryQuery = new Query();
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
			TransactionCommit();
			fclose($resRecoverySQLFile);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			if (isset($resRecoverySQLFile) && $resRecoverySQLFile !== FALSE)
			{
				fclose($resRecoverySQLFile);
			}
			
			throw new Exception("Reversing Contact merge failed.  ". $e->getMessage());
		}
	}
	
	// Throws an exception on error
	private function _addLineToRecoveryFile($strLine, $resFile)
	{
		static $bolRecoverFileIsAtFirstLine;
		if (!isset($bolRecoverFileIsAtFirstLine))
		{
			$bolRecoverFileIsAtFirstLine = TRUE;
		}
		
		if ($bolRecoverFileIsAtFirstLine)
		{
			// The recovery file has not yet been written to
			$bolRecoverFileIsAtFirstLine = FALSE;
		}
		else
		{
			// It is not the first line of the recovery file.  Prepend a new line char to the line
			$strLine = "\n". $strLine;
		}
		
		if (fwrite($resFile, $strLine) === FALSE)
		{
			throw new exception("Could not record line to recorvery file - ". str_replace("\n", "", $strLine));
		}
	}

	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_RECEIVING_CONTACT => array(
				self::ARG_LABEL 		=> "RECEIVING_CONTACT",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "Id of the Contact record that will now be referenced by all records that used to reference the Id of the deprecated contact",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_DEPRECATED_CONTACT => array(
				self::ARG_LABEL 		=> "DEPRECATED_CONTACT",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "Id of the contact record to deprecate",
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
