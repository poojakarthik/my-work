<?php

require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_EmailQueueFlush extends Cli
{
	const SWITCH_TEST_RUN				= "t";
	const SWITCH_QUEUE_ID				= "q";
	const SWITCH_DEBUG_EMAIL_ADDRESS	= "e";
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs	= $this->getValidatedArguments();
			
			// Args
			$bTestRun		= (bool)$arrArgs[self::SWITCH_TEST_RUN];
			$iQueueId		= (int)$arrArgs[self::SWITCH_QUEUE_ID];
			$sDebugAddress	= $arrArgs[self::SWITCH_DEBUG_EMAIL_ADDRESS];
			$sDebugAddress	= !!$sDebugAddress ? $sDebugAddress	: null;
			
			$oDataAccess	= DataAccess::getDataAccess();
			if ($bTestRun)
			{
				// Start transaction, in test mode
				if ($oDataAccess->TransactionStart() === false)
				{
					throw Exception("Failed to start database transaction");
				}
				
				Log::getLog()->log("-----");
				Log::getLog()->log("Test Mode Enabled - None of the email queues will be commited.");
				if ($sDebugAddress !== null)
				{
					Log::getLog()->log("\nDebug email address: {$sDebugAddress}, all emails will be sent to this address.");
				}
				Log::getLog()->log("-----");
			}
			
			$aEmailQueues	= array();
			if($iQueueId && is_numeric($iQueueId))
			{
				Log::getLog()->log("Will attempt to deliver single queue: $iQueueId");
				
				// Deliver the single queue
				$aEmailQueues[]	= Email_Queue::getForId($iQueueId);
			}
			else
			{
				// Deliver all waiting email_queue records
				$aEmailQueues	= Email_Queue::getWaitingQueues();
			}
			
			$iQueueCount	= count($aEmailQueues);
			Log::getLog()->log("{$iQueueCount} queue".($iQueueCount == 1 ? '' : 's')." to deliver");
			
			if ($iQueueCount > 0)
			{
				// Create and save an Email_Queue_Batch
				$oEmailQueueBatch					= new Email_Queue_Batch();
				$oEmailQueueBatch->created_datetime	= date('Y-m-d H:i:s');
				$oEmailQueueBatch->save();
				
				foreach ($aEmailQueues as $oEmailQueue)
				{
					// Deliver the email queue, only commits (actually sends) if NOT in test mode
					$oEmailQueue->deliver($oEmailQueueBatch, $bTestRun, $sDebugAddress);
				}
				
				Log::getLog()->log("All queues delivered");
			}
			
			if ($bTestRun)
			{
				// Rollback transaction, in test mode
				Log::getLog()->log("Test mode, rolling back all database changes");
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw Exception("Failed to rollback database transaction");
				}
			}
			
			return 0;
		}
		catch(Exception $oException)
		{
			$this->showUsage('Error: '.$oException->getMessage());
			return 1;
		}
	}

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [fully functional EXCEPT emails will not be sent]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_QUEUE_ID => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "id of the specific email_queue to deliver",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s");'
			),

			self::SWITCH_DEBUG_EMAIL_ADDRESS => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "an email address to redirect all of the sent emails to [optional, effective only if -t option supplied]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validString("%1$s");'
			),
		);
	}

}


?>
