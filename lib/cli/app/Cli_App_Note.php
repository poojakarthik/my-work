<?php
/**
 * Cli_App_Note
 *
 * Bulk Note Insertion CLI Application
 *
 * @class	Cli_App_Note
 * @parent	Cli
 */
class Cli_App_Note extends Cli
{
	const	SWITCH_TEST_RUN		= 't';
	
	const	SWITCH_INPUT_FILE	= 'f';
	
	private static	$_aColumns	=	array
									(
										'Account',
										'Note'
									);
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode.", true);
			}
			
			// Load up the CSV file
			$oCSVFile	= new File_CSV();
			$oCSVFile->setColumns(self::$_aColumns);
			$oCSVFile->importFile($this->_arrArgs[self::SWITCH_TEST_RUN], true, false);
			
			$oFlexDataAccess	= DataAccess::getDataAccess();
			
			$sImportDatetime	= date("Y-m-d H:i:s");
			
			if (!$oFlexDataAccess->TransactionStart())
			{
				throw new Exception("Unable to start a Transaction");
			}
			try
			{
				$iRowCount	= 0;
				foreach ($oCSVFile as $aRow)
				{
					$iRowCount++;
					
					$iAccountId	= (int)$aRow['Account'];
					if (!$iAccountId)
					{
						// Appears to be a blank row
						$this->log("[-] Skipping Row #{$iRowCount} -- Appears to be blank");
						continue;
					}
					
					$this->log("[+] Verifying that Account '{$iAccountId}' exists...", false, true);
					
					// Add a note for this record
					// Verify that the Account exists
					$oAccount = new Account(array('Id'=>$iAccountId), false ,true);
					
					$this->log(" Adding Note...", false, true);
					
					// Create a new General Note
					$oNote					= new Note();
					$oNote->AccountGroup	= $oAccount->AccountGroup;
					$oNote->Account			= $oAccount->Id;
					$oNote->Datetime		= $sImportDatetime;
					$oNote->NoteType		= Note::GENERAL_NOTE_TYPE_ID;
					if (!$oNote->save())
					{
						throw new Exception("Unable to save Note '{$aRow['Note']}' for Account #{$oAccount->Id}");
					}
					
					$this->log(" [ DONE ]");
				}
				
				// Test Mode
				if ($this->_arrArgs[self::SWITCH_TEST_RUN])
				{
					throw new Exception("Test Mode -- Rolling back changes...");
				}
				
				// Commit our Changes
				if (!$oFlexDataAccess->TransactionCommit())
				{
					throw new Exception("Unable to commit the Transaction");
				}
			}
			catch (Exception $eException)
			{
				// Rollback Transaction
				if (!$oFlexDataAccess->TransactionRollback())
				{
					throw new Exception("ALERT: Unable to roll back the Transaction.  Manual cleanup may be required!\nOriginal Exception" . $eException->__toString());
				}
				throw $eException;
			}
			
			// Success!
			return 0;
		}
		catch(Exception $exception)
		{
			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$strMessage	= $exception->__toString();
			}
			else
			{
				$strMessage	= $exception->getMessage();
			}

			// We can now show the error message
			$this->showUsage($strMessage);
			return 1;
		}
	}

	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Notes will not be saved to the Database",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_INPUT_FILE => array(
				self::ARG_REQUIRED		=> true,
				self::ARG_DESCRIPTION	=> "CSV File to Import from",
				self::ARG_VALIDATION	=> 'Cli::_validFile("%1$s", true)'
			),
		);
	}
}
?>