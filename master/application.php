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
 * @package		Skeleton_application
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//TODO!!!!
/*
Make db tables
	MasterState
		Datetime
		State		(long text) => serialised array
	MasterInstructions
		Datetime
		Instruction		int
		Command			long text
		
connect to db to read and write (see internal TODOs)


*/

// Application entry point - create an instance of the application object
$appSkel = new ApplicationSkel($arrConfig);

// Run the application
$appSkel->Run();

//----------------------------------------------------------------------------//
// ApplicationSkel
//----------------------------------------------------------------------------//
/**
 * ApplicationSkel
 *
 * Skeleton Module
 *
 * Skeleton Module
 *
 *
 * @prefix		app
 *
 * @package		skeleton_application
 * @class		ApplicationSkel
 */
 class ApplicationSkel extends ApplicationBaseClass
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
			// get current time
			$intTimeNow = time();
			
			// check if the script needs to be run now
			if ($intTimeNow > $arrScript['NextRun'])
			{
				// run the script
				$strCommand = $arrScript['Config']['Command'];
				if ($strCommand)
				{
					// write state to database
					$this->_arrState['CurrentScript'] = $strScriptName;
					$this->_arrState['CurrentRunTime'] = $intTimeNow;
					$this->_WriteState(STATE_SCRIPT_RUN);
				
					$this->_arrState['LastReturn'] = shell_exec($strCommand);
					$this->_arrState['LastScript'] = $strScriptName;
					$this->_arrState['LastRunTime'] = $intTimeNow;
				}
				
				// set last run time for the script
				$this->_arrScript[$strScriptName]['LastRun'] = $intTimeNow;
				
				// calculate next run time for the script
				$intNextRun = $this->_CalculateNextRun($arrScript);
				$this->_arrScript[$strScriptName]['NextRun'] = $intNextRun;
			}
		}
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
		//TODO!!!! - write to database
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
		//TODO!!!! - read from database
		//$this->_arrState = 
		
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
		//TODO!!!!
		//$arrInstructions = 
		
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
			//TODO!!!!
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
		//TODO!!!! - truncate table
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
			Echo "$strText\n";
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
		
		// calculate zero time today
		$intZeroTime = floor($intTimeNow / 86400) * 86400);
		
		// calculate day based timestamp
		$intDayTimeStamp = $intTimeNow - $intZeroTime;
		
		// get first run time for today
		$intFirstRun = (int)$arrScript['Config']['StartTime'] + $intZeroTime;
		
		// get final run time for today
		$intFinalRun = (int)$arrScript['Config']['FinishTime'] + $intZeroTime;
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
		if ($intLastSchedualedRun)
		{
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
			$intNextRun = $intFirstRun + $intZeroTime;
		}
		
		// Return Next Run TimeStamp
		return $intNextRun;
	}
 }


?>
