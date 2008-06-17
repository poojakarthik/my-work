<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		process
 * @author		Rich 'Waste' Davis
 * @version		8.02
 * @copyright	2006-2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationProcess
//----------------------------------------------------------------------------//
/**
 * ApplicationProcess
 *
 * Automatic Process Application
 *
 * Automatic Process Application
 *
 *
 * @prefix		app
 *
 * @package		process
 * @class		ApplicationProcess
 */
 class ApplicationProcess extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
	}
	
	//------------------------------------------------------------------------//
	// RunProcess
	//------------------------------------------------------------------------//
	/**
	 * RunProcess()
	 *
	 * Runs an Automatic Process
	 *
	 * Runs an Automatic Process
	 * 
	 * @param	string	$strProcessName					Friendly Name of the Process to run
	 *
	 * @return	boolean									Pass/Fail
	 *
	 * @method
	 */
 	function RunProcess($strProcessName)
 	{
		// Statements
		$arrColumns = Array();
		$arrColumns['ProcessType']	= NULL;
		$arrColumns['WaitDatetime']	= new MySQLFunction('NOW()');
		$arrColumns['PID']			= NULL;
		$insInstance	= new StatementInsert("Process", $arrColumns);
		
		$selProcess		= new StatementSelect("ProcessType", "*", "Name = <Name>");
		$selPriorities	= new StatementSelect("ProcessPriority JOIN ProcessType ON ProcessType.Id = ProcessPriority.ProcessRunning", "*", "ProcessWaiting = <Id>");
		$selWaiting		= new StatementSelect("Process", "*", "ProcessType = <ProcessType> AND EndDatetime IS NULL");
		
		$strCustomer		= strtoupper(CUSTOMER_URL_NAME);
		$strSubject			= "Process $strProcessName failed to start for $strCustomer";
		$strSubjectWaiting	= "Process $strProcessName is waiting to start for $strCustomer";
		$strSubjectStarted	= "Process $strProcessName finished waiting for $strCustomer";
		
		$this->_intPID		= getmypid();
		
		// Select the Process
		$selProcess->Execute(Array('Name' => $strProcessName));
		if (!$arrProcess = $selProcess->Fetch())
		{
			// Email, debug, and fail out
			$strContent = "Cannot retrieve ProcessType data";
			SendEmail(PROCESS_ALERT_EMAIL, $strSubject, $strContent, PROCESS_ALERT_FROM);
			CliEcho("*** ERROR: $strContent ***");
			return FALSE;
		}
		
		// Check to see if this is already running
		$arrProcess['ProcessType']	= $arrProcess['Id'];
		if ($selWaiting->Execute($arrProcess))
		{
			// Email, debug, and fail out
			$strContent = "Process is already running";
			SendEmail(PROCESS_ALERT_EMAIL, $strSubject, $strContent, PROCESS_ALERT_FROM);
			CliEcho("*** ERROR: $strContent ***");
			return FALSE;
		}
		
		// Create the Instance
		$arrInstance = Array();
		$arrInstance['ProcessType']		= $arrProcess['Id'];
		$arrInstance['WaitDatetime']	= new MySQLFunction('NOW()');
		$arrInstance['PID']				= $this->_intPID;
		$arrInstance['Id']	= $insInstance->Execute($arrInstance);
		
		// Check Priorities
		while (true)
		{
			$selPriorities->Execute($arrProcess);
			while ($arrPriority = $selPriorities->Fetch())
			{
				if ($arrPriority['WaitMode'] == 0)
				{
					// Do not wait - fail out
					$strContent = "Prioritised Process '{$arrPriority['Name']}' is currently running";
					SendEmail(PROCESS_ALERT_EMAIL, $strSubject, $strContent, PROCESS_ALERT_FROM);
					$arrInstance['Output']	= "*** ERROR: $strContent ***";
					$this->_EndProcess($arrInstance);
					return FALSE;
				}
				elseif ($arrPriority['WaitMode'] < 0)
				{
					if ($selWaiting->Execute(Array('ProcessType' => $arrPriority['ProcessRunning'])))
					{
						// Wait indefinitely - email alert
						$intStartTime	= time();
						$intCurrentTime	= 0;
						$strContent		= "Prioritised Process '{$arrPriority['Name']}' is currently running";
						SendEmail(PROCESS_ALERT_EMAIL, $strSubjectWaiting, $strContent, PROCESS_ALERT_FROM);
						CliEcho("Waiting for '{$arrPriority['Name']}' to finish... ", FALSE);
						while ($selWaiting->Execute(Array('ProcessType' => $arrPriority['ProcessRunning'])))
						{
							sleep(5);
							$intLength		= strlen($intCurrentTime)+1;
							$intCurrentTime	= time() - $intStartTime;
							CliEcho("\033[{$intLength}D{$intCurrentTime}s", FALSE);
						}
						$strContent = "Prioritised Process '{$arrPriority['Name']}' has finished (Waited $intCurrentTime seconds)";
						SendEmail(PROCESS_ALERT_EMAIL, $strSubjectStarted, $strContent, PROCESS_ALERT_FROM);
						CliEcho("\nLock on '{$arrPriority['Name']}' resolved!");
					}
				}
				else
				{
					// Wait for specified time
					$intMaxTime = time() + (int)$arrPriority['WaitMode'];
					CliEcho("Waiting for '{$arrPriority['Name']}' to finish... ", FALSE);
					$intStartTime	= time();
					$intCurrentTime	= 0;
					while ((time() < $intMaxTime) && ($intCount = $selWaiting->Execute(Array('ProcessType' => $arrPriority['ProcessRunning']))))
					{
						sleep(5);
						$intLength		= strlen($intCurrentTime)+1;
						$intCurrentTime	= time() - $intStartTime;
						CliEcho("\033[{$intLength}D{$intCurrentTime}s", FALSE);
					}
					
					if ($intCount)
					{
						// Out of time - email and fail out
						$strContent = "Prioritised Process '{$arrPriority['Name']}' is currently running (Waited {$arrPriority['WaitMode']} seconds)";
						SendEmail(PROCESS_ALERT_EMAIL, $strSubject, $strContent, PROCESS_ALERT_FROM);
						$arrInstance['Output']	= "*** ERROR: $strContent ***";
						$this->_EndProcess($arrInstance);
						return FALSE;
					}
					else
					{
						CliEcho("\nLock on '{$arrPriority['Name']}' resolved!");
					}
				}
			}
			
			// No more locks
			break;
		}
		
		// Start script
		if (!chdir($arrProcess['WorkingDirectory']))
		{
			// Could not change to Working Directory
			$strContent = "Could not change to working directory '{$arrProcess['WorkingDirectory']}'";
			SendEmail(PROCESS_ALERT_EMAIL, $strSubject, $strContent, PROCESS_ALERT_FROM);
			$arrInstance['Output']	= "*** ERROR: $strContent ***";
			$this->_EndProcess($arrInstance);
			return FALSE;
		}
		
		$arrPipes = Array();
		$arrDescriptor = Array();
		$arrDescriptor[0]	= Array('pipe', 'r');	// STDIN for Process
		$arrDescriptor[1]	= Array('pipe', 'w');	// STDOUT for Process
		$arrDescriptor[2]	= Array('pipe', 'w');	// STDERR for Process
		if (!$ptrProcess	= proc_open($arrProcess['Command'], $arrDescriptor, $arrPipes))
		{
			// Could not start process
			$strContent = "Could not execute command '{$arrProcess['Command']}'";
			SendEmail(PROCESS_ALERT_EMAIL, $strSubject, $strContent, PROCESS_ALERT_FROM);
			$arrInstance['Output']	= "*** ERROR: $strContent ***";
			$this->_EndProcess($arrInstance);
			return FALSE;
		}
		$arrProcessStatus = proc_get_status($ptrProcess);
		
		// Update DB
		$arrCols = Array();
		$arrCols['StartDatetime']		= new MySQLFunction("NOW()");
		$arrCols['PID']					= NULL;
		$arrInstance['StartDatetime']	= new MySQLFunction("NOW()");
		$arrInstance['PID']				= $arrProcessStatus['pid'];
		$ubiStartDatetime	= new StatementUpdateById("Process", $arrCols);
		$ubiStartDatetime->Execute($arrInstance);
		
		// Monitor Output
		$arrBlank				= Array();
		$arrInstance['Output']	= "";
		stream_set_blocking($arrPipes[1], 0);
		do
		{
			$arrProcess	= Array($arrPipes[1]);
			if (stream_select($arrProcess, $arrBlank, $arrBlank, 0, 500000))
			{
				// Check for output every 0.5s
				$strOutput	= stream_get_contents($arrPipes[1]);
				CliEcho($strOutput, FALSE);
				$arrInstance['Output']	.= $strOutput;
			}
			$arrStatus	= proc_get_status($ptrProcess);
		}
		while ($arrStatus['running']);
		pclose($arrPipes[0]);
		pclose($arrPipes[1]);
		pclose($arrPipes[2]);
		@proc_close($ptrProcess);
		
		// End Process
		$this->_EndProcess($arrInstance, FALSE);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// CleanProcesses
	//------------------------------------------------------------------------//
	/**
	 * CleanProcesses()
	 *
	 * Checks to see if there are Processes which may have stalled or quit unexpectedly
	 *
	 * Checks to see if there are Processes which may have stalled or quit unexpectedly
	 *
	 * @return			VOID
	 *
	 * @method
	 */
 	function CleanProcesses()
 	{
		CliEcho("Searching for unfinished processes... ", FALSE);
		
		// Look for processes that might have failed
		$selProcesses	= new StatementSelect("Process JOIN ProcessType ON Process.ProcessType = ProcessType.Id", "*, Process.Id AS Id", "EndDatetime IS NULL");
		$intCount		= $selProcesses->Execute();
		CliEcho("found $intCount");
		while ($arrProcess = $selProcesses->Fetch())
		{
			CliEcho("\t+ {$arrProcess['Name']} (Started: {$arrProcess['StartDatetime']}; PID: {$arrProcess['PID']})... ", FALSE);
			
			// Get current Linux Processlist
			$strPS	= shell_exec("ps -e");
			$arrPS	= explode("\n", $strPS);
			
			// If the process id is in this list, then it is still active
			foreach ($arrPS as $strProcess)
			{
				$intPID	= (int)$strProcess;
				if ($intPID == $arrProcess['PID'])
				{
					// Appears active, so ignore
					CliEcho("[  ACTIVE  ]");
					continue 2;
				}
			}
			
			// The script appears to have finished, so set the EndDatetime
			CliEcho("[ FINALISED ]");
			$arrProcess['Output']	= "*** CLEANUP: Process was lost and finalised by Process::CleanProcesses() ***";
			$this->_EndProcess($arrProcess, FALSE);
		}
	}
	
	//------------------------------------------------------------------------//
	// _EndProcess
	//------------------------------------------------------------------------//
	/**
	 * _EndProcess()
	 *
	 * Sets the EndDatetime for a Process
	 *
	 * Sets the EndDatetime for a Process
	 * 
	 * @param	array	$arrInstance			The instance to End
	 *
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
 	function _EndProcess($arrInstance, $bolOutput = TRUE)
 	{
		// Display Output
		if ($bolOutput)
		{
			CliEcho($arrInstance['Output']);
		}
		
		// Save Process
		$arrCols = Array();
		$arrCols['EndDatetime']		= new MySQLFunction("NOW()");
		$arrCols['Output']			= "";
		$arrInstance['EndDatetime']	= new MySQLFunction("NOW()");
		$ubiProcess	= new StatementUpdateById("Process", $arrCols);
		return (bool)$ubiProcess->Execute($arrInstance);
	}
 }


?>
