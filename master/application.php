<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
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
 * @package		master
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationMaster
//----------------------------------------------------------------------------//
/**
 * ApplicationMaster
 *
 * Master Application
 *
 * Executes all back-end scripts
 *
 *
 * @prefix		app
 *
 * @package		master
 * @class		ApplicationSkel
 */
 class ApplicationMaster extends ApplicationBaseClass
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
		
		// store the config
		$this->_arrConfig = $arrConfig;
		
		$arrColumns['Datetime']	= new MySQLFunction("NOW()");
		$arrColumns['State']	= NULL;
		$this->_updMasterState	= new StatementUpdate("MasterState", "1", $arrColumns, "1");
		
		$this->_selGetState				= new StatementSelect("MasterState", "*", "1", NULL, "1");
		$this->_selGetInstructions		= new StatementSelect("MasterInstructions", "*", "1", "Datetime ASC");
		$this->_qryDeleteInstruction	= new Query();
		$this->_qryTruncate				= new QueryTruncate();
	}
	
	//------------------------------------------------------------------------//
	// Run
	//------------------------------------------------------------------------//
	/**
	 * Run()
	 *
	 * Run for the Master Application
	 *
	 * Run for the Master Application
	 * 
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	function Run()
	{
		// Validate Config
		CliEcho(" * Validating Configuration...");
		foreach ($this->_arrConfig['Script'] as $strName=>$arrScript)
		{
			$strDescription	= "$strName: ";
			
			if ($arrScript['RecurringDay'] > 0)
			{
				switch (substr($arrScript['RecurringDay'], -1, 1))
				{
					case 1:
						$strOrd	= "{$arrScript['RecurringDay']}st";
						break;
										
					case 2:
						$strOrd	= "{$arrScript['RecurringDay']}nd";
						break;
					
					case 3:
						$strOrd	= "{$arrScript['RecurringDay']}rd";
						break;
					
					default:
						$strOrd	= "{$arrScript['RecurringDay']}th";
						break;
				}
				$strDescription .= "Runs Monthly on the $strOrd";
			}
			elseif ($arrScript['RecurringDay'] < 0)
			{
				$intDaysBack	= 0 - $arrScript['RecurringDay'];
				$strDescription .= "Runs Monthly $intDaysBack days from End of Month";
			}
			else
			{
				$strDescription .= "Runs <= Daily";
			}
			
			if ($arrScript['SubScript'])
			{
				$strDescription .= "; MULTIPART";
			}
			else
			{
				$strDescription .= "; NORMAL";
			}
			
			CliEcho("\t + $strDescription");
		}
		
		// not waiting
		$this->_bolWait = FALSE;
		
		// get state from the database
		$this->_ReadState();
		
		// write state to the database
		$this->_WriteState(STATE_INIT);
		
		// clean instructions from the database
		$this->_ClearInstructions();
		
		// write state to database
		$this->_WriteState(STATE_RUN);
		
		// Run
		for ($i=0;$i < $this->_arrConfig['MaxRuns'];$i++)
		{
			// store current run id
			$this->_intCurrentRun = $i;
			
			// check database for instructions
			$this->_ReadInstructions();
			
			// do run (unless we have been told to wait)
			if ($this->_bolWait !== TRUE)
			{
				$this->Debug('Loop : '.$this->_intCurrentRun);
				$this->_Run();
			}
			else
			{
				$i--;
			}
			
			// write state to database
			$this->_WriteState(STATE_SLEEP);
			
			// pause between runs
			sleep ($this->_arrConfig['Sleep']);
		}
		
		// Finished
		$this->Halt();
	}
	
	//------------------------------------------------------------------------//
	// _Run
	//------------------------------------------------------------------------//
	/**
	 * _Run()
	 *
	 * Perform a single application run
	 *
	 * Perform a single application run
	 * 
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	function _Run()
	{
		// for each script
		foreach ($this->_arrScript as $strScriptName=>$arrScript)
		{
			$this->Debug("Checking Script : $strScriptName");
			// get current time
			$intTimeNow = time();
			
			if (!$arrScript['NextRun'])
			{
				// calculate next run time for the script
				$intNextRun = $this->_CalculateNextRun($arrScript);
				$this->_arrScript[$strScriptName]['NextRun'] = $intNextRun;
			}
			
			// check if the script needs to be run now
			if ($intTimeNow > (int)$arrScript['NextRun'])
			{				
				$this->Debug("Loading Script : $strScriptName");
				
				// write state to database
				$this->Debug("Writing state to database");
				$this->_arrState['CurrentScript'] = $strScriptName;
				$this->_arrState['CurrentRunTime'] = $intTimeNow;
				$this->_WriteState(STATE_SCRIPT_RUN);
				
				// actually run the thing
				$this->Debug("Running Script : $strScriptName");
				if ($arrScript['Config']['SubScript'])
				{
					// Monthly script
					$bolPassed = TRUE;
					$this->_arrState['LastReturn'] = "";
					foreach ($arrScript['Config']['SubScript'] as $strName=>$arrSubscript)
					{
						$this->Debug("\tRunning SubScript: $strName");
						$strReturn = $this->_RunScript($arrScript, $arrSubscript);
						$this->Debug("SubScript Returned  :\n $strReturn");
						$this->_arrState['LastReturn'] .= $strReturn;
					}
				}
				else
				{
					// Standard script
					$this->_arrState['LastReturn']		= $this->_RunScript($arrScript);
					$this->Debug("Script Returned  :\n {$this->_arrState['LastReturn']}");
				}
				$this->_arrState['LastScript'] = $strScriptName;
				$this->_arrState['LastRunTime'] = $intTimeNow;
				
				// set last run time for the script
				$this->_arrScript[$strScriptName]['LastRun'] = $intTimeNow;
				
				// calculate next run time for the script
				$intNextRun = $this->_CalculateNextRun($arrScript);
				$this->_arrScript[$strScriptName]['NextRun'] = $intNextRun;
			}
			else
			{
				$intNextRun = (int)$arrScript['NextRun'] - $intTimeNow;
				$this->Debug("Too soon to run script : $strScriptName , will run in $intNextRun sec. ()".date("Y-m-d H:i:s", (int)$arrScript['NextRun']));
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// _RunScript
	//------------------------------------------------------------------------//
	/**
	 * _RunScript()
	 *
	 * Executes a script
	 *
	 * Executes a script
	 * 
	 * @param	array	$arrScript					Script details array
	 * @param	arrat	$arrSubScript	optional	SubScript to run
	 * 
	 * @return	int									Script Exit Code
	 *
	 * @method
	 */
	function _RunScript($arrScript, $arrSubScript=NULL)
	{
		if ($arrSubScript)
		{
			$strDirectory	= $arrSubScript['Directory'];
			$strCmd			= $arrSubScript['Command'];
		}
		else
		{
			$strDirectory	= $arrScript['Config']['Directory'];
			$strCmd			= $arrScript['Config']['Command'];
		}
		
		// set run script command
		if ($strDirectory)
		{
			// change directory first
			$strCommand  = "cd $strDirectory;";
			$strCommand .= $strCmd;
		}
		else
		{
			// run it right where we are
			$strCommand = $strCmd;
		}
		
		// Run
		return shell_exec($strCommand);
	}
	
	
	//------------------------------------------------------------------------//
	// _WriteState
	//------------------------------------------------------------------------//
	/**
	 * _WriteState()
	 *
	 * Write current state details to the database
	 *
	 * Write current state details to the database
	 * 
	 *
	 * @param	int		intState	Current State
	 * @return	VOID
	 *
	 * @method
	 */
	function _WriteState($intState)
	{
		// write our current state to the database
		$this->_arrState['Run'] 	= $this->_intCurrentRun;
		$this->_arrState['State'] 	= $intState;
		$this->_arrState['Time'] 	= time();
		$this->_arrState['Wait'] 	= $this->_bolWait;
		$this->_arrState['Script']	= $this->_arrScript;
		
		$arrData['Datetime']	= new MySQLFunction("NOW()");
		$arrData['State']		= Serialize($this->_arrState);
		// write to database
		if ($this->_updMasterState->Execute($arrData, NULL) === FALSE)
		{

		}
	}
	
	//------------------------------------------------------------------------//
	// _ReadState
	//------------------------------------------------------------------------//
	/**
	 * _ReadState()
	 *
	 * Read previous state details from the database
	 *
	 * Read previous state details from the database
	 * 
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	function _ReadState()
	{
		// read our current state from the database
		if ($this->_selGetState->Execute() === FALSE)
		{

		}
		$arrResult = $this->_selGetState->Fetch();
		$this->_arrState = Unserialize($arrResult['State']); 
		
		// setup scripts array
		$this->_arrScript = Array();
		if (is_array($this->_arrConfig['Script']))
		{	
			foreach($this->_arrConfig['Script'] as $strScriptName => $arrScriptConfig)
			{
				if (is_array($this->_arrState['Script'][$strScriptName]))
				{
					// existing script
					$arrScript = $this->_arrState['Script'][$strScriptName];
				}
				else
				{
					// new script
					$arrScript = Array();
				}
				$arrScript['Config'] = $arrScriptConfig;
				
				// calculate next run time for the script
				if (!$arrScript['NextRun'])
				{
					$arrScript['NextRun'] = $this->_CalculateNextRun($arrScript);
				}
				
				$this->_arrScript[$strScriptName] = $arrScript;	
			}
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _ReadInstructions
	//------------------------------------------------------------------------//
	/**
	 * _ReadInstructions()
	 *
	 * Read instructions from the database
	 *
	 * Read instructions from the database
	 * 
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	function _ReadInstructions()
	{
		// read any instructions from the database
		if ($this->_selGetInstructions->Execute() === FALSE)
		{

		}
		$arrInstructions = $this->_selGetInstructions->FetchAll();
		
		foreach ($arrInstructions as $arrInstruction)
		{
			// do instruction
			switch($arrInstructions['Type'])
			{
				// command
				case INSTRUCTION_COMMAND:
					//TODO-LATER !!!!
					break;
				
				// wait
				case INSTRUCTION_WAIT:
					$this->_bolWait = TRUE;
					break;
					
				// wait
				case INSTRUCTION_RESUME:
					$this->_bolWait = FALSE;
					break;
					
				// shutdown
				case INSTRUCTION_HALT:
					$this->Halt();
					break;
					
				default:
					// do nothing
					break;
			}
			
			// clear instruction from db
			if ($this->_qryDeleteInstruction->Execute("DELETE FROM MasterInstruction WHERE Id = ".$arrInstruction['Id']) === FALSE)
			{

			}
		}
		
	}
	
	//------------------------------------------------------------------------//
	// _ClearInstructions
	//------------------------------------------------------------------------//
	/**
	 * _ClearInstructions()
	 *
	 * Clear all instructions from the database
	 *
	 * Clear all instructions from the database
	 * 
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	function _ClearInstructions()
	{
		// clear all instructions from the database
		if ($this->_qryTruncate->Execute("MasterInstruction"))
		{

		}
	}
	
	//------------------------------------------------------------------------//
	// Halt
	//------------------------------------------------------------------------//
	/**
	 * Halt()
	 *
	 * Shutdown this application
	 *
	 * Shutdown this application
	 * 
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	function Halt()
	{
		// set state in DB
		$this->_WriteState(STATE_HALT);
		
		// Stop
		Die();
	}
	
	//------------------------------------------------------------------------//
	// Debug
	//------------------------------------------------------------------------//
	/**
	 * Debug()
	 *
	 * Write Debug output to the console
	 *
	 * Write Debug output to the console
	 * will only display output if config option Verbose = TRUE
	 * 
	 *
	 * @param	str		$strText	Text to be output
	 * @return			VOID
	 *
	 * @method
	 */
	function Debug($strText)
	{
		if ($this->_arrConfig['Verbose'] == TRUE)
		{
			CLIEcho($strText);
		}
	}
	
	//------------------------------------------------------------------------//
	// _CalculateNextRun
	//------------------------------------------------------------------------//
	/**
	 * _CalculateNextRun()
	 *
	 * Calculate the next run time for a script
	 *
	 * Calculate the next run time for a script
	 * 
	 *
	 * @param	array	$arrScript	Script details array
	 * @return	int		Timestamp of next scheduled run time
	 *
	 * @method
	 */
	function _CalculateNextRun($arrScript)
	{
		// get current time
		$intTimeNow = Time();
		$this->Debug("Time Now  : ".Date("Y-m-d H:i:s", $intTimeNow));
		
		// calculate zero time today
		$intZeroTime = floor((($intTimeNow + ($this->_arrConfig['GMTOffset'] * 3600)) / 86400)) * 86400 - ($this->_arrConfig['GMTOffset'] * 3600);
		$this->Debug("Zero Time : ".Date("Y-m-d H:i:s", $intZeroTime));
		
		// calculate day based timestamp
		$intDayTimeStamp = $intTimeNow - $intZeroTime;
		
		// get first run time for today
		$intFirstRun = (int)$arrScript['Config']['StartTime'] + $intZeroTime;
		$this->Debug("First Run : ".Date("Y-m-d H:i:s", $intFirstRun));
		
		// get final run time for today
		$intFinalRun = (int)$arrScript['Config']['FinishTime'] + $intZeroTime;
		$this->Debug("Final Run : ".Date("Y-m-d H:i:s", $intFinalRun));
		if ($intFinalRun == $intZeroTime)
		{
			// set last run to midnight
			$intFinalRun =  $intZeroTime + 86400;
		}
		
		// get interval
		$intInterval = (int)$arrScript['Config']['Interval'];
		if (!$intInterval)
		{
			// default interval is 1 hour
			$intInterval = 3600;
		}
		
		// get actual time last run
		//$intLastRun = (int)$arrScript['LastRun'];
		
		// get time last scheduled to run
		$intLastSchedualedRun = (int)$arrScript['NextRun'];
		if ($arrScript['Config']['RecurringDay'])
		{
			// Monthly Script
			if ($intLastSchedualedRun)
			{
				// Work out next month's date
				if ($arrScript['Config']['RecurringDay'] > 0)
				{
					// Every month on a given day
					$intNextRun = strtotime("+1 month", $intLastSchedualedRun);
				}
				else
				{
					// Certain number of days before the start of next month
					$intFirstNextMonth	= strtotime(date("Y-m-d H:i:s", strtotime("+2 months", strtotime(date("Y-m-01 H:i:s", $intLastSchedualedRun)))));
					$intNextRun			= strtotime("-{$arrScript['Config']['RecurringDay']} days");
				}
			}
			else
			{
				// Hasn't run yet, see if we're too late
				if ($arrScript['Config']['RecurringDay'] > 0)
				{
					// Are we too late to run?
					$strZeroPaddedDay	= str_pad($arrScript['Config']['RecurringDay'], 2, '0', STR_PAD_LEFT);
					$strRecurringDate	= date("Y-m-$strZeroPaddedDay", $intTimeNow);
					$strEarliestRun		= date("Y-m-d H:i:s", strtotime("+{$arrScript['Config']['StartTime']} seconds", strtotime($strRecurringDate)));
					$strLatestRun		= date("Y-m-d H:i:s", strtotime("+{$arrScript['Config']['FinishTime']} seconds", strtotime($strRecurringDate)));
					
					if ($intTimeNow < strtotime($strLatestRun))
					{
						// Nope
						$intNextRun = strtotime($strEarliestRun);
					}
					else
					{
						// Yup, set it to next month
						$intNextRun = strtotime("+1 month", strtotime($strEarliestRun));
					}
				}
				else
				{
					// Are we too late to run?
					$strRecurringDate	= date("Y-m-$strZeroPaddedDay", $intTimeNow);
					$strEarliestRun		= date("Y-m-d H:i:s", strtotime("+{$arrScript['Config']['StartTime']} seconds", strtotime($strRecurringDate)));
					$strLatestRun		= date("Y-m-d H:i:s", strtotime("+{$arrScript['Config']['StartTime']} seconds", strtotime($strRecurringDate)));
					
					// Certain number of days before the start of next month
					$intFirstNextMonth	= strtotime(date("Y-m-d", strtotime("+1 months", $intTimeNow)));
					$intEarliestRun		= strtotime("+{$arrScript['Config']['StartTime']} seconds", strtotime("-{$arrScript['Config']['RecurringDay']} days", $intFirstNextMonth));
					$intLatestRun		= strtotime("+{$arrScript['Config']['StartTime']} seconds", strtotime("-{$arrScript['Config']['RecurringDay']} days", $intFirstNextMonth));
					
					if ($intTimeNow < $intLatestRun)
					{
						// Nope
						$intNextRun = $intEarliestRun;
					}
					else
					{
						// Yup, set it to next month
						$intFirstNextMonth	= strtotime(date("Y-m-d", strtotime("+2 months", $intTimeNow)));
						$intEarliestRun		= strtotime("+{$arrScript['Config']['StartTime']} seconds", strtotime("-{$arrScript['Config']['RecurringDay']} days", $intFirstNextMonth));
					}
				}
			}
		}
		else
		{
			// Normal Script
			if ($intLastSchedualedRun)
			{
				$this->Debug("Last Run  : ".Date("Y-m-d H:i:s", $intLastSchedualedRun));
				// schedule next run based on previous schedule
				$intNextRun = $intLastSchedualedRun + $intInterval;
				
				// check run time constraints
				if ($intFirstRun > $intNextRun)
				{
					// if next run is earlier then first run...
					// run at first run time
					$intNextRun = $intFirstRun;
				}
				elseif ($intFinalRun < $intNextRun)
				{
					// if next run is later then last run...
					// run at first run time tomorrow
					$intNextRun = $intFirstRun + 86400;
				}
			}
			else
			{
				// if no previous scheduled run
				// schedule next run for first run time
				$intNextRun = $intFirstRun;
			}
		
			// check if next run is more than one interval in the past
			if ($intNextRun < ($intTimeNow - $intInterval))
			{
				$intIntervals = floor(($intTimeNow - $intNextRun) / $intInterval);
				$intNextRun += $intIntervals * $intInterval;
			}
		}
		
		$this->Debug("Next Run  : ".Date("Y-m-d H:i:s", $intNextRun));
		
		// Return Next Run TimeStamp
		return $intNextRun;
	}
 }


?>
