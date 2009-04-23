<?php
/**
 * Cli_App_Calendar
 *
 * Calendar CLI Application
 *
 * @class	Cli_App_Calendar
 * @parent	Cli
 */
class Cli_App_Calendar extends Cli
{
	const	SWITCH_TEST_RUN			= "t";
	const	SWITCH_FILE				= "f";
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs	= $this->getValidatedArguments();

			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}

			// Any additional Includes
			$this->requireOnce('lib/classes/Flex.php');
			Flex::load();
			
			// Import the File
			$intImportCount	= Calendar_Event::importFromCSVFile($this->_arrArgs[self::SWITCH_FILE]);
			
			$this->log("{$intImportCount} Calendar Events were successfully imported.");

			// If not in test mode, Commit the Transaction
			if (!$this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionCommit();
				Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully committed!");
			}
			else
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
				Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");
			}
			return 0;
		}
		catch(Exception $exception)
		{
			$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
			Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");

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
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Revokes the transaction that encapsulates the application, and provides debug data",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_FILE => array(
				self::ARG_LABEL			=> "FILE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "CSV File to Import from",
				self::ARG_VALIDATION	=> 'Cli::_validFile("%1$s")'
			),
		);
	}
}
?>