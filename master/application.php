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
					$this->_arrState['LastReturn'] = shell_exec($strCommand);
					$this->_arrState['LastScript'] = $strScriptName;
					$this->_arrState['LastRunTime'] = $intTimeNow;
				}
				
				// calculate next run time for the script
				$intNextRun = $this->_CalculateNextRun($arrScript);
				$this->_arrScript[$strScriptName]['NextRun'] = $intNextRun;
				
				// write state to database
				$this->_WriteState(STATE_SCRIPT_RUN);
				
			}
		}
	}
	
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
					//TODO-LATER
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
	
	function _ClearInstructions()
	{
		// clear any instructions from the database
		//TODO!!!! - truncate table
	}
	
	function Halt()
	{
		// set status in DB (STATE_HALT)
		//TODO!!!!
		
		// Stop
		Die();
	}
	
	function Debug($strText)
	{
		if ($this->_arrConfig['Verbose'] == TRUE)
		{
			Echo "$strText\n";
		}
	}
	
	function _CalculateNextRun($arrScript)
	{
		//TODO!!!!
	}
 }


?>
