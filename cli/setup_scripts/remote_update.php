<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// remote_update
//----------------------------------------------------------------------------//
/**
 * remote_update
 *
 * Updates a remote server with the local version of viXen
 *
 * Updates a remote server with the local version of viXen
 *
 * @file		remote_update.php
 * @language	PHP
 * @package		Payment_application
 * @author		Rich 'Waste' Davis
 * @version		7.04
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
require_once("../../flex.require.php");
require_once("../framework/remote_copy.php");
 
// Definitions
define("REMOTE_SERVER_DPS"		, 1);
define("REMOTE_SERVER_MINX"		, 2);
define("REMOTE_SERVER_SPANK"	, 3);
define("REMOTE_SERVER_CATWALK"	, 4);

define("PATH_PACKAGE_VIXEN"		, 1);
define("PATH_PACKAGE_BACKEND"	, 2);
define("PATH_PACKAGE_INTRANET"	, 3);
define("PATH_PACKAGE_FRAMEWORK"	, 4);
define("PATH_PACKAGE_OTHER"		, 5);
 
//----------------------------------------------------------------------------//
// Configuration
//----------------------------------------------------------------------------//
$arrConfig = Array();

// Application settings
$arrConfig['Application']	['ScreenWidth']	= 80;
$arrConfig['Application']	['MenuX']		= 10;		// Number of characters to offset the menu by
$arrConfig['Application']	['MenuY']		= 7;		// Number of characters to offset the menu by
$arrConfig['Application']	['MenuTitleX']	= 10;
$arrConfig['Application']	['MenuTitleY']	= 6;
$arrConfig['Application']	['Title']		= "[ REMOTE COPY APPLICATION ]";
$arrConfig['Application']	['TitleX']		= floor(($arrConfig['Application']['ScreenWidth'] - strlen($arrConfig['Application']['Title'])) / 2);
$arrConfig['Application']	['TitleY']		= 3;
$arrConfig['Application']	['LocalPath']	= '/home/richdavis/vixen/';	// Change to your local dir

// Path Packages
$arrConfig['Package']	[PATH_PACKAGE_VIXEN]		['Name']		= 'All of viXen';
$arrConfig['Package']	[PATH_PACKAGE_VIXEN]		['Path']	[]	= '';	// leave bank!

$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Name']		= 'Entire Backend';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'billing_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'charges_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'collection_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'master_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'mistress_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'normalisation_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'payment_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'provisioning_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'rating_app';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'backup_scripts';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'setup_scripts';
$arrConfig['Package']	[PATH_PACKAGE_BACKEND]		['Path']	[]	= 'screen_scrape';

$arrConfig['Package']	[PATH_PACKAGE_INTRANET]		['Name']		= 'Entire Intranet';
$arrConfig['Package']	[PATH_PACKAGE_INTRANET]		['Path']	[]	= 'intranet_app';
$arrConfig['Package']	[PATH_PACKAGE_INTRANET]		['Path']	[]	= 'images';

$arrConfig['Package']	[PATH_PACKAGE_FRAMEWORK]	['Name']		= 'Framework';
$arrConfig['Package']	[PATH_PACKAGE_FRAMEWORK]	['Path']	[]	= 'oblib';
$arrConfig['Package']	[PATH_PACKAGE_FRAMEWORK]	['Path']	[]	= 'framework';
$arrConfig['Package']	[PATH_PACKAGE_FRAMEWORK]	['Path']	[]	= 'import';

$arrConfig['Package']	[PATH_PACKAGE_OTHER]		['Name']		= 'Choose a different path';

// Copy Modes
$arrConfig['CopyMode']	[RCOPY_BACKUP]		['Name']	= 'Backup Files';
$arrConfig['CopyMode']	[RCOPY_REMOVE]		['Name']	= 'Remove Files and Directories before Copy';
$arrConfig['CopyMode']	[RCOPY_OVERWRITE]	['Name']	= 'Overwrite Files';


// Servers
$arrConfig['Server']	[REMOTE_SERVER_MINX]	['Name']		= 'TelcoBlue Live Backend (MINX)';
$arrConfig['Server']	[REMOTE_SERVER_MINX]	['Host']		= '10.11.12.16';
$arrConfig['Server']	[REMOTE_SERVER_MINX]	['Username']	= 'flame';
$arrConfig['Server']	[REMOTE_SERVER_MINX]	['Password']	= 'zeemu';
$arrConfig['Server']	[REMOTE_SERVER_MINX]	['Protocol']	= PROTOCOL_SSH2;
$arrConfig['Server']	[REMOTE_SERVER_MINX]	['RemotePath']	= '/usr/share/vixen/';

$arrConfig['Server']	[REMOTE_SERVER_SPANK]	['Name']		= 'TelcoBlue Live Frontend (SPANK)';
$arrConfig['Server']	[REMOTE_SERVER_SPANK]	['Host']		= '10.11.12.15';
$arrConfig['Server']	[REMOTE_SERVER_SPANK]	['Username']	= 'flame';
$arrConfig['Server']	[REMOTE_SERVER_SPANK]	['Password']	= 'zeemu';
$arrConfig['Server']	[REMOTE_SERVER_SPANK]	['Protocol']	= PROTOCOL_SSH2;
$arrConfig['Server']	[REMOTE_SERVER_SPANK]	['RemotePath']	= '/usr/share/vixen/';

$arrConfig['Server']	[REMOTE_SERVER_DPS]		['Name']		= 'viXen Testing Environment (DPS)';
$arrConfig['Server']	[REMOTE_SERVER_DPS]		['Host']		= '10.11.12.13';
$arrConfig['Server']	[REMOTE_SERVER_DPS]		['Username']	= 'flame';
$arrConfig['Server']	[REMOTE_SERVER_DPS]		['Password']	= 'zeemu';
$arrConfig['Server']	[REMOTE_SERVER_DPS]		['Protocol']	= PROTOCOL_FTP;
$arrConfig['Server']	[REMOTE_SERVER_DPS]		['RemotePath']	= '/usr/share/vixen/';

$arrConfig['Server']	[REMOTE_SERVER_CATWALK]	['Name']		= 'TelcoBlue Testing Environment (CATWALK)';
$arrConfig['Server']	[REMOTE_SERVER_CATWALK]	['Host']		= '10.11.12.14';
$arrConfig['Server']	[REMOTE_SERVER_CATWALK]	['Username']	= 'flame';
$arrConfig['Server']	[REMOTE_SERVER_CATWALK]	['Password']	= 'zeemu';
$arrConfig['Server']	[REMOTE_SERVER_CATWALK]	['Protocol']	= PROTOCOL_SSH2;
$arrConfig['Server']	[REMOTE_SERVER_CATWALK]	['RemotePath']	= '/usr/share/vixen/';

/*	Skeleton Server Config
$arrConfig['Server']	[REMOTE_SERVER_SKEL]	['Name']		= 'Friendly name';
$arrConfig['Server']	[REMOTE_SERVER_SKEL]	['Host']		= 'server to connect to';
$arrConfig['Server']	[REMOTE_SERVER_SKEL]	['Username']	= 'username';
$arrConfig['Server']	[REMOTE_SERVER_SKEL]	['Password']	= 'passowrd';
$arrConfig['Server']	[REMOTE_SERVER_SKEL]	['Protocol']	= PROTOCOL_SSH2 | PROTOCOL_FTP;
$arrConfig['Server']	[REMOTE_SERVER_SKEL]	['RemotePath']	= 'root vixen directory';
 */
 
//----------------------------------------------------------------------------//
// ApplicationUpdate
//----------------------------------------------------------------------------//
/**
 * ApplicationUpdate
 *
 * Updates a remote server with the local version of viXen
 *
 * Updates a remote server with the local version of viXen
 *
 *
 * @prefix		app
 *
 * @package		setup_scripts
 * @class		ApplicationUpdate
 */
 class ApplicationUpdate extends ApplicationBaseClass
 {

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Remote Update Application
	 *
	 * Constructor for the Remote Update Application
	 *
	 * @param	mixed	$$mixEmailAddress	Array or string of Addresse(s) to send report to
	 * 
	 * @return	ApplicationUpdate
	 *
	 * @method
	 */
	function __construct($arrConfig)
	{
		// Call parent constructor
		parent::__construct();
		
		// Set up config
		$this->_arrConfig = $arrConfig;
		
		// Cursor position
		$this->_intX = 0;
		$this->_intY = 0;
	}
	 

	//------------------------------------------------------------------------//
	// RemoteCopy
	//------------------------------------------------------------------------//
	/**
	 * RemoteCopy()
	 *
	 * Core function for the application
	 *
	 * Core function for the application
	 * 
	 * @return	boolean
	 *
	 * @method
	 */
	function RemoteCopy()
	{
		// Create interface
		$mixServer = $this->_DrawMenu($this->_arrConfig['Server'], "Please choose a server to update:");
		if ($mixServer === FALSE)
		{
			// return
			$this->_EchoCLS();
			return TRUE;
		}
		
		// Choose package or path to copy
		$mixPackage = $this->_DrawMenu($this->_arrConfig['Package'], "Please choose the package to copy:");
		if ($mixPackage === FALSE)
		{
			// return
			$this->_EchoCLS();
			return TRUE;
		}
		if ($mixPackage === PATH_PACKAGE_OTHER)
		{
			// Prompt the user for a path
			$arrPaths[] = $this->_DrawPrompt("Please enter a path to copy:", Array(&$this, "_VixenFileExists"), "Invalid path specified!", $this->_arrConfig['Application']['LocalPath']);
		}
		else
		{
			// Get the list of paths to copy
			$arrPaths = $this->_arrConfig['Package'][$mixPackage]['Path'];
		}
		
		// Choose Copy mode
		$mixMode = $this->_DrawMenu($this->_arrConfig['CopyMode'], "Please choose the copy mode:");
		if ($mixMode === FALSE)
		{
			// return
			$this->_EchoCLS();
			return TRUE;
		}
		
		// Confirmation
		$mixConfirm = $this->_DrawPrompt("Are you sure you want to copy these files?", "/^(yes)|(no)$/misU", "Please type 'yes' or 'no'", "[Yes | No] ? ");
		switch (true)
		{
			// Yes; continue
			case (strtoupper($mixConfirm) == 'YES'):
				break;
				
			// No; exit
			default:
				// return
				$this->_EchoCLS();
				return TRUE;
		}
		
		// Clear screen to allow for nice output
		$this->_EchoCLS();
		
		// Instanciate the RemoteCopy object
		$rcpRemoteCopy	= NULL;
		$strServer		= $this->_arrConfig['Server'][$mixServer]['Host'];
		$strUserName	= $this->_arrConfig['Server'][$mixServer]['Username'];
		$strPassword	= $this->_arrConfig['Server'][$mixServer]['Password'];
		switch ($this->_arrConfig['Server'][$mixServer]['Protocol'])
		{
			case PROTOCOL_SSH2:
				$rcpRemoteCopy = new RemoteCopySSH($strServer, $strUserName, $strPassword);
				$strMessage = "Connected to SSH2 server '$strServer'!\n\n";
				break;
			
			case PROTOCOL_FTP:
				$rcpRemoteCopy = new RemoteCopyFTP($strServer, $strUserName, $strPassword);
				$strMessage = "Connected to FTP server '$strServer'!\n\n";
				break;
			
			default:
				// return
				echo "Fatal Error: Invalid Protocol Specified!\n\n";
				return FALSE;
		}
		
		// Connect to the server
		if (is_string($mixResult = $rcpRemoteCopy->Connect()))
		{
			// return
			echo "Fatal Error: $mixResult\n\n";
			return FALSE;
		}
		
		echo $strMessage;
		ob_flush();
		
		// Copy
		$this->Framework->StartWatch();
		foreach ($arrPaths as $strPath)
		{
			$strLocalPath	= $this->_arrConfig['Application']['LocalPath'].$strPath;
			$strRemotePath	= $this->_arrConfig['Server']['RemotePath'].$strPath;
			$rcpRemoteCopy->Copy($strLocalPath, $strRemotePath, $mixMode);
		}
		
		// Success!
		$intTime = $this->Framework->SplitWatch();
		echo "Copy Completed in $intTime seconds.\n\n";
		
		// Disconnect
		if (is_string($mixResult = $rcpRemoteCopy->Disconnect()))
		{
			echo "Warning: $mixResult\n\n";
		}
		
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// _DrawMenu
	//------------------------------------------------------------------------//
	/**
	 * _DrawMenu()
	 *
	 * Draws the Main menu, and retrieves user input
	 *
	 * Draws the Main menu, and retrieves user input
	 * 
	 * @return	mixed		FALSE	: exit the program
	 * 						integer	: menu option selected
	 *
	 * @method
	 */
	function _DrawMenu($arrItems, $strTitle)
	{
		// Clear the screen
		$this->_EchoCLS();
		
		// Draw the application title
		$this->_EchoPos($this->_arrConfig['Application']['Title'], $this->_arrConfig['Application']['TitleX'], $this->_arrConfig['Application']['TitleY']);
		
		// Draw the menu title
		//$this->_EchoPos("Please select a server to update:", $this->_arrConfig['Application']['MenuTitleX'], $this->_arrConfig['Application']['MenuTitleY']);
		$this->_EchoPos($strTitle, $this->_arrConfig['Application']['MenuTitleX'], $this->_arrConfig['Application']['MenuTitleY']);
		
		// Draw the menu
		$intX	= $this->_arrConfig['Application']['MenuX'];
		$intY	= $this->_arrConfig['Application']['MenuY'];
		$intId	= NULL;
		foreach ($arrItems as $intId=>$arrItem)
		{
			$this->_EchoPos("$intId.\t{$arrItem['Name']}", $intX, $intY + $intId);
		}
		$intExitId = $intId+1;
		$this->_EchoPos("$intExitId.\tExit", $intX, $intY + $intExitId);
		ob_flush();
		
		// Await input
		$intInput = NULL;
		while (!array_key_exists($intInput, $arrItems) && $intInput != $intExitId)
		{
			// Redraw cursor
			$this->_EchoPos(str_repeat(' ', 80), $intX, $intY + $intExitId + 2);
			$this->_EchoPos("? ", $intX, $intY + $intExitId + 2, FALSE);
			ob_flush();
			
			// Get input
			$intInput = (int)trim(fgets(STDIN));
		}
		
		// Is this an Exit call or Option call?
		if ($intInput === $intExitId)
		{
			// Exit
			return FALSE;
		}
		else
		{
			// Option
			return $intInput;
		}
	}

	//------------------------------------------------------------------------//
	// _DrawPrompt
	//------------------------------------------------------------------------//
	/**
	 * _DrawPrompt()
	 *
	 * Draws a prompt, and retrieves user input
	 *
	 * Draws a prompt, and retrieves user input
	 * 
	 * @param	string		$strTitle		Title for the prompt
	 * @param	mixed		$mixValidate	optional Regular Expression to use for validation, or Custom Validation method for us in call_user_func()
	 * @param	string		$strPrompt		optional Custom prompt text (defaults to '? ')
	 * 
	 * @return	mixed		FALSE	: exit the program
	 * 						string	: input string
	 *
	 * @method
	 */
	function _DrawPrompt($strTitle, $mixValidate = NULL, $strError = "Invalid input, please try again!", $strPrompt = '? ')
	{
		// Clear the screen
		$this->_EchoCLS();
		
		// Draw the application title
		$this->_EchoPos($this->_arrConfig['Application']['Title'], $this->_arrConfig['Application']['TitleX'], $this->_arrConfig['Application']['TitleY']);
		
		// Draw the prompt title
		//$this->_EchoPos("Please select a server to update:", $this->_arrConfig['Application']['MenuTitleX'], $this->_arrConfig['Application']['MenuTitleY']);
		$this->_EchoPos($strTitle, $this->_arrConfig['Application']['MenuTitleX'], $this->_arrConfig['Application']['MenuTitleY']);
		
		// Draw the prompt window
		$intMinX	= $this->_arrConfig['Application']['MenuX'];
		$intMinY	= $this->_arrConfig['Application']['MenuY']+1;
		/*$intWidth	= 60;
		$intHeight	= 3;
		$this->_DrawWindow($intMinX, $intMinY, $intWidth, $intHeight);*/
		
		// Await input
		$strInput = NULL;
		while ($strInput === FALSE || $strInput === NULL)
		{
			// Was there bad input?
			if ($strInput === FALSE)
			{
				// Write error message
				$this->_EchoPos(str_repeat(' ', 80), $intMinX, $intMinY+2);
				$this->_EchoPos("\033[0;31m".$strError."\033[0;30m", $intMinX, $intMinY+2, FALSE);
			}
			
			// Redraw the prompt line
			$this->_EchoPos(str_repeat(' ', 80), $intMinX, $intMinY);
			$this->_EchoPos($strPrompt, $intMinX, $intMinY, FALSE);
			ob_flush();
			
			// Get input
			$strInput = trim(fgets(STDIN));
			
			// Validate
			switch (true)
			{
				// Run validation function
				case is_array($mixValidate):
				case function_exists($mixValidate):
					$strInput = (call_user_func($mixValidate, $strInput)) ? $strInput : FALSE;
					break;
				
				// Run regex
				case $mixValidate:
					$strInput = (preg_match($mixValidate, $strInput)) ? $strInput : FALSE;
					break;
			}
		}
		
		return $strInput;
	}

	//------------------------------------------------------------------------//
	// _DrawWindow
	//------------------------------------------------------------------------//
	/**
	 * _DrawWindow()
	 *
	 * Draws an ASCII window
	 *
	 * Draws an ASCII window
	 * 
	 * @param	string		$strTitle		Title for the prompt
	 * @param	string		$strValidate	optional Regular Expression to use for validation, or Custom Validation method
	 * @param	string		$strPrompt		optional Custom prompt text (defaults to '? ')
	 * 
	 * @return	mixed		FALSE	: exit the program
	 * 						string	: input string
	 *
	 * @method
	 */
	function _DrawWindow($intMinX, $intMinY, $intWidth, $intHeight, $bolShadow = TRUE)
	{		
		// Draw the Window
		
		// Set the draw colour
		echo "\033[0;47m";
		ob_flush();
		
		// foreach row
		for ($intRow = $intMinY; $intRow < ($intMinY+$intHeight); $intRow++)
		{
			// Draw a Line
			$strOutput = str_repeat(' ', $intWidth);
			echo "\033[$intRow;{$intMinX}H".$strOutput;
		}
		
		// Draw the window shadow
		if ($bolShadow)
		{
			// Right shadow
			// TODO
			
			// Bottom Shadow
			// TODO
		}
		ob_flush();
		
		// Return Draw colour to Black
		echo "\033[0;30m";
		ob_flush();
	}
	

	//------------------------------------------------------------------------//
	// _EchoPos
	//------------------------------------------------------------------------//
	/**
	 * _EchoPos()
	 *
	 * Echos a string at a give screen coordinate
	 *
	 * Echos a string at a give screen coordinate
	 * 
	 * @param	string		$strMessage		Message to echo
	 * @param	integer		$intX			X coordinate
	 * @param	integer		$intY			Y coordinate
	 * @param	boolean		$bolReturn		optional Whether or not to return to the old cursor position
	 *
	 * @method
	 */
	function _EchoPos($strMessage, $intX, $intY, $bolReturn = TRUE)
	{
		// Move the cursor, echo the string, then return to the old cursor position
		echo "\033[$intY;{$intX}H";
		echo $strMessage;
		
		// Returning to old position?
		if ($bolReturn)
		{
			// yes
			echo "\033[$this->_intY;{$this->_intX}H";
		}
		else
		{
			// no, update global X and Y positions
			$this->_intY = $intY;
			$this->_intX = $intX;
		}
		return;
	}

	//------------------------------------------------------------------------//
	// _EchoCLS
	//------------------------------------------------------------------------//
	/**
	 * _EchoCLS()
	 *
	 * Emulates a shell "clear" command
	 *
	 * Emulates a shell "clear" command
	 *
	 * @method
	 */
	function _EchoCLS()
	{
		for ($i = 0; $i < 100; $i++)
		{
			$this->_EchoPos(str_repeat(' ', 80), 0, $i);
		}
		$this->_EchoPos("", 0, 0, FALSE);
	}

	//------------------------------------------------------------------------//
	// _VixenFileExists
	//------------------------------------------------------------------------//
	/**
	 * _VixenFileExists()
	 *
	 * Performs a file_exists() for a viXen path
	 *
	 * Performs a file_exists() for a viXen path
	 * 
	 * @param	string	$strPath	viXen path to validate
	 * 
	 * @return	boolean
	 *
	 * @method
	 */
	function _VixenFileExists($strPath)
	{
		return (bool)file_exists($this->_arrConfig['Application']['LocalPath'].$strPath);
	}
	
	
 }
 
 


//----------------------------------------------------------------------------//
// Execution
//----------------------------------------------------------------------------//

// Run the application
$appUpdate = new ApplicationUpdate($arrConfig);
$appUpdate->RemoteCopy();
exit;
 
 ?>