<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// cli_interface
//----------------------------------------------------------------------------//
/**
 * cli_interface
 *
 * Implements a basic user interface using NCurses
 *
 * Implements a basic user interface using NCurses
 *
 * @file		cli_interface.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste' Davis
 * @version		7.04
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 require_once("require.php");
 
 // Define common ascii codes not defined by Ncurses
 define('KEY_ESCAPE'			, 27);
 define('KEY_ENTER'				, 13);
 define('KEY_BACKSPACE'			, 263);
 define('KEY_BACKSPACE_ASCII'	, 8);
 define('KEY_DELETE'			, 330);
 define('KEY_DELETE_ASCII'		, 127);
 define('KEY_TAB'				, 9);
 
 define('VIXEN_ROOT'			, "/home/richdavis/vixen/");
 
 function VixenPathComplete($strPath)
 {
 	chdir(VIXEN_ROOT);
 	if (is_dir(VIXEN_ROOT.$strPath."/"))
 	{
 		chdir(VIXEN_ROOT.$strPath);
 		return glob("*");
 	}
 	elseif (dirname($strPath) != '.')
 	{
 		@chdir(VIXEN_ROOT.dirname(VIXEN_ROOT.$strPath));
 		return glob(basename($strPath)."*");
 	}
 	else
 	{
 		return glob($strPath."*");
 	}
 }
 
//----------------------------------------------------------------------------//
// CLIInterface
//----------------------------------------------------------------------------//
/**
 * CLIInterface
 *
 * Implements a basic user interface using NCurses
 *
 * Implements a basic user interface using NCurses
 *
 *
 * @prefix		itf
 *
 * @package		framework
 * @class		CLIInterface
 */
 class CLIInterface
 {
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the CLIInterface Class
	 *
	 * Constructor for the CLIInterface Class
	 * 
	 * @return	CLIInterface
	 *
	 * @method
	 */
	 function __construct($strApplicationTitle)
	 {	 	
	 	// Init ncurses
	 	ncurses_init();
	 	ncurses_savetty();
	 	ncurses_clear();
	 	ncurses_start_color();
	 	
	 	// Setup main window
	 	$this->_winFullscreen = ncurses_newwin(0, 0, 0, 0);
	 	ncurses_border(0, 0, 0, 0, 0, 0, 0, 0);
	 	ncurses_curs_set(0);
	 	
	 	// Set application title
	 	$intX = 0;
	 	$intY = 0;
	 	ncurses_getmaxyx($this->_winFullscreen, $intY, $intX);
	 	//ncurses_attron(NCURSES_A_UNDERLINE);
	 	$strApplicationTitle = " $strApplicationTitle ";
	 	ncurses_mvaddstr(0, CentreText($strApplicationTitle, $intX), $strApplicationTitle);
	 	//ncurses_attroff(NCURSES_A_UNDERLINE);
	 	
	 	// Draw the status bar
	 	//ncurses_attron(NCURSES_A_REVERSE);
	 	//ncurses_mvaddstr($intY - 1, 2, str_pad(" Press ESC at any time to exit ", $intX - 4));
	 	ncurses_mvaddstr($intY - 1, 2, " Press ESC at any time to exit ");
	 	//ncurses_attroff(NCURSES_A_REVERSE);
	 	
	 	ncurses_refresh();
	 	
	 	// Init variables
	 	$this->_arrWindows = Array();
	 }
	 
	//------------------------------------------------------------------------//
	// __destruct
	//------------------------------------------------------------------------//
	/**
	 * __destruct()
	 *
	 * Destructor for the CLIInterface Class
	 *
	 * Destructor for the CLIInterface Class
	 *
	 * @method
	 */
	 function __destruct()
	 {
	 	ncurses_resetty();
	 	ncurses_end();
	 }
	 
	//------------------------------------------------------------------------//
	// DrawMenu
	//------------------------------------------------------------------------//
	/**
	 * DrawMenu()
	 *
	 * Draws a menu, and retrieves user input
	 *
	 * Draws a menu, and retrieves user input
	 * 
	 * @param	array		$arrItems		inde
	 * 
	 * @return	mixed		FALSE	: exit the program
	 * 						integer	: menu option selected
	 *
	 * @method
	 */
	function DrawMenu($arrItems, $strTitle, $arrPosition = NULL, $arrDimensions = NULL)
	{
	 	// disable input echoing
	 	ncurses_noecho();
	 	
	 	// Get parent window size
	 	$intMaxX = 0;
	 	$intMaxY = 0;
	 	ncurses_getmaxyx($this->_winFullscreen, $intMaxY, $intMaxX);
	 	
	 	// Get width of widest menu option
	 	$intMaxWidth		= 0;
	 	$intMinWidth		= 60;	// Window must be this wide
	 	$intBufferWidth		= 2;	// Side Buffer between menu border and item text
	 	$intBufferHeight	= 1;	// Top/Bottom Buffer between menu border and item text
	 	foreach ($arrItems as $arrItem)
	 	{
	 		$intMaxWidth = max($intMinWidth, $intMaxWidth, strlen($arrItem['Name']) + ($intBufferWidth * 2) + 2);
	 	}
	 	
	 	// Do we have a positions or dimensions?
	 	if (!$arrDimensions)
	 	{
	 		// Set defaults
	 		$arrDimensions['Width']		= $intMaxWidth;
	 		$arrDimensions['Height']	= count($arrItems) + ($intBufferHeight * 2) + 2;
	 	}
	 	if (!$arrPosition)
	 	{
	 		// Centre the window
	 		$arrPosition['X']	= CentreText(str_repeat(" ", $arrDimensions['Width']), $intMaxX);
	 		$arrPosition['Y']	= CentreText(str_repeat(" ", $arrDimensions['Height']), $intMaxY);
	 	}
	 	
	 	
	 	// Create new window
	 	$winMenu = ncurses_newwin($arrDimensions['Height'], $arrDimensions['Width'], $arrPosition['Y'], $arrPosition['X']);
	 	ncurses_wborder($winMenu, 0, 0, 0, 0, 0, 0, 0, 0);
	 	ncurses_keypad($winMenu, TRUE);
	 	
	 	// Create a array of links between menu options and values
	 	$arrMenuValues = Array();
	 	reset($arrItems);
	 	for ($intI = 0; $intI < count($arrItems); $intI++)
	 	{
	 		$arrMenuValues[$intI]	= key($arrItems);
	 		next($arrItems);
	 	}
	 	
	 	// Create menu list and await input
	 	$intSelected = NULL;
	 	while (TRUE)
	 	{
	 		// Draw the title
	 		ncurses_mvwaddstr($winMenu, 0, $intBufferWidth + 1, " $strTitle: ");
	 		
	 		// Draw the menu
	 		foreach ($arrMenuValues as $intId=>$intItem)
	 		{
	 			// Do we have a selected item?
	 			if ($intSelected === NULL)
	 			{
	 				// Default to top item
	 				$intSelected = $intId;
	 			}
	 			
	 			// Is this the currently selected item?
	 			if ($intSelected === $intId)
	 			{
	 				ncurses_wattron($winMenu, NCURSES_A_REVERSE);
	 			}
	 			
	 			// Draw the item
	 			ncurses_mvwaddstr($winMenu, $intBufferHeight + 1 + $intId, $intBufferWidth + 1, str_pad($arrItems[$intItem]['Name'], $arrDimensions['Width'] - ($intBufferWidth * 2) - 2));
	 			ncurses_wattroff($winMenu, NCURSES_A_REVERSE);
	 		}
	 		
	 		ncurses_wrefresh($winMenu);
	 		
	 		// Wait for user input
	 		ncurses_move($intMaxY, $intMaxX);
	 		$intPressed = ncurses_getch();
	 		switch ($intPressed)
	 		{
	 			// Exit the program
	 			case KEY_ESCAPE:
	 				ncurses_delwin($winMenu);
	 				$strNotice = " EXITING... ";
	 				ncurses_mvaddstr($intMaxY - 1, $intMaxX - 1 - strlen($strNotice), $strNotice);
	 				ncurses_refresh();
	 				ncurses_clear();
	 				return NULL;
	 			
	 			// Navigation keys
	 			case NCURSES_KEY_UP:
	 				$intSelected = ($intSelected - 1 < 0) ? count($arrMenuValues) - 1 : $intSelected - 1;
	 				break;
	 			case NCURSES_KEY_DOWN:
	 				$intSelected = ($intSelected + 1 > count($arrMenuValues) - 1) ? 0 : $intSelected + 1;
	 				break;
	 			
	 			// Enter: return the current selected item's value
	 			case KEY_ENTER:
	 				ncurses_wclear($winMenu);
	 				ncurses_wrefresh($winMenu);
	 				ncurses_delwin($winMenu);
	 				ncurses_refresh();
	 				return $arrMenuValues[$intSelected];
	 				break;
	 		}
	 	}
	}
	
	 
	//------------------------------------------------------------------------//
	// DrawPrompt
	//------------------------------------------------------------------------//
	/**
	 * DrawPrompt()
	 *
	 * Draws a prompt, and retrieves user input
	 *
	 * Draws a prompt, and retrieves user input
	 * 
	 * @param	string		$strTitle		Title for the prompt
	 * @param	mixed		$mixValidate	Regular Expression to use for validation, or Custom Validation method for us in call_user_func()
	 * @param	integer		$intMaxChars	optional Maximum number of characters in the input
	 * @param	string		$strDefault		optional The default value for the prompt
	 * @param	string		$strPrompt		optional Custom prompt text (defaults to '? ')
	 * @param	boolean		$strTabComplete	optional Method to return tab completion results
	 * 
	 * @return	mixed		FALSE	: exit the program
	 * 						string	: input string
	 *
	 * @method
	 */
	function DrawPrompt($strTitle, $mixValidate, $bolPassword = FALSE, $intMaxChars = NULL, $strDefault = NULL, $strPrompt = '? ', $strTabComplete = NULL, $arrPosition = NULL)
	{
	 	// disable input echoing
	 	ncurses_noecho();
	 	ncurses_curs_set(1);
	 	
	 	// Get parent window size
	 	$intMaxX = 0;
	 	$intMaxY = 0;
	 	ncurses_getmaxyx($this->_winFullscreen, $intMaxY, $intMaxX);
	 	
	 	// Get width of widest menu option
	 	$intMaxWidth		= 60;
	 	$intBufferWidth		= 2;	// Side Buffer between menu border and item text
	 	$intBufferHeight	= 1;	// Top/Bottom Buffer between menu border and item text
	 	
	 	// Do we have a positions?
 		$arrDimensions['Width']		= $intMaxWidth;
 		$arrDimensions['Height']	= 1 + ($intBufferHeight * 2) + 2;
	 	if (!$arrPosition)
	 	{
	 		// Centre the window
	 		$arrPosition['X']	= CentreText(str_repeat(" ", $arrDimensions['Width']), $intMaxX);
	 		$arrPosition['Y']	= CentreText(str_repeat(" ", $arrDimensions['Height']), $intMaxY);
	 	}
	 	
	 	// Create new window
	 	$winPrompt = ncurses_newwin($arrDimensions['Height'], $arrDimensions['Width'], $arrPosition['Y'], $arrPosition['X']);
	 	ncurses_wborder($winPrompt, 0, 0, 0, 0, 0, 0, 0, 0);
	 	ncurses_keypad($winPrompt, TRUE);
	 	
	 	// Create Error window
	 	$winError = ncurses_newwin(3, $arrDimensions['Width'], $arrPosition['Y'] + 5, $arrPosition['X']);
	 	
	 	$winTabComplete = NULL;
	 	
	 	// Create the prompt and await input
	 	$strInput = $strDefault;
	 	$strError = NULL;
	 	while (TRUE)
	 	{
	 		// Draw the title
	 		ncurses_mvwaddstr($winPrompt, 0, $intBufferWidth + 1, " $strTitle: ");
	 		
	 		// Draw error message
	 		if ($strError)
	 		{
	 			ncurses_wclear($winError);
			 	ncurses_wborder($winError, 0, 0, 0, 0, 0, 0, 0, 0);
			 	$intErrorColour		= 1;
			 	$intNormalColour	= 2;
			 	ncurses_init_pair($intErrorColour, NCURSES_COLOR_RED, NCURSES_COLOR_BLACK);
			 	ncurses_init_pair($intNormalColour, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLACK);
			 	
			 	ncurses_wcolor_set($winError, $intErrorColour);
			 	ncurses_mvwaddstr($winError, 0, 3, " Error! ");
		 		ncurses_wrefresh($winError);
		 		
			 	ncurses_wcolor_set($winError, $intNormalColour);
			 	ncurses_mvwaddstr($winError, 1, 2, $strError);
			 	ncurses_wrefresh($winError);
			 	$strError = NULL;
	 		}
	 		
	 		// Draw the prompt
	 		$strFullPrompt = $strPrompt;
	 		if ($bolPassword)
	 		{
	 			$strFullPrompt .= str_repeat("*", strlen($strInput));
	 		}
	 		else
	 		{
	 			$strFullPrompt .= $strInput;
	 		}
	 		
	 		ncurses_wattron($winPrompt, NCURSES_A_REVERSE);
	 		ncurses_mvwaddstr($winPrompt, 2, 2, str_pad($strFullPrompt, $arrDimensions['Width'] - 4));
	 		ncurses_wattroff($winPrompt, NCURSES_A_REVERSE);
	 		
	 		// Wait for user input
	 		ncurses_wmove($winPrompt, 2, 2 + strlen($strFullPrompt));
	 		$intPressed = ncurses_wgetch($winPrompt);
	 		switch ($intPressed)
	 		{
	 			// Exit the program
	 			case KEY_ESCAPE:
	 				$strNotice = " EXITING... ";
	 				ncurses_mvaddstr($intMaxY - 1, $intMaxX - 1 - strlen($strNotice), $strNotice);
	 				ncurses_refresh();
	 				ncurses_curs_set(0);
	 				return NULL;
	 			
	 			// Enter: validate and return the current input
	 			case KEY_ENTER:
	 				switch (true)
	 				{
	 					// Validation function
	 					case function_exists($mixValidate):
	 						if (is_string($mixData = call_user_func($mixValidate, $strInput)))
	 						{
	 							// Show error
	 							 $strError = $mixData;
	 						}
	 						else
	 						{
	 							// return valid data
	 							return $strInput;
	 						}
	 						break;
	 					
	 					// Regular expression	
	 					default:
	 						if (preg_match($mixValidate, $strInput))
	 						{
	 							// return valid data
	 							ncurses_curs_set(0);
	 							return $strInput;
	 						}
	 						else
	 						{
	 							$strError = "Invalid data entered!";
	 						}
	 				}
	 				break;
	 				
				case KEY_BACKSPACE:
				case KEY_BACKSPACE_ASCII:
					$strInput = substr($strInput, 0, -1);
					break;
					
				case KEY_TAB:
					// Emulate Tab completion
					if (function_exists($strTabComplete))
					{
						// Get tab compete matches
						$arrMatches = call_user_func($strTabComplete, $strInput);
						@ncurses_wclear($winTabComplete);
						@ncurses_wrefresh($winTabComplete);
						@ncurses_delwin($winTabComplete);
						
						if (count($arrMatches) > 1)
						{
						 	$intMaxMatches = 5;
						 	
						 	// Create Tab Complete window
						 	$intHeight = min($intMaxMatches, count($arrMatches));
						 	$winTabComplete = ncurses_newwin($intHeight + 4, $arrDimensions['Width'], $arrPosition['Y'] + 5, $arrPosition['X']);
						 	ncurses_wborder($winTabComplete, 0, 0, 0, 0, 0, 0, 0, 0);
						 	ncurses_mvwaddstr($winTabComplete, 0, 3, " Tab Complete Options: ");
						 	
						 	// Draw list
						 	$intI = 0;
						 	while ($intI < $intMaxMatches && $intI < count($arrMatches))
						 	{
						 		// Draw the result
						 		ncurses_mvwaddstr($winTabComplete, $intI + 2, 2, $arrMatches[$intI]);
						 		$intI++;
						 	}
						 	
						 	if (count($arrMatches) > $intMaxMatches)
						 	{
						 		// Draw notice
						 		ncurses_wattron($winTabComplete, NCURSES_A_REVERSE);
						 		ncurses_mvwaddstr($winTabComplete, $intMaxMatches + 3, 2, "More than $intMaxMatches matches.  Please refine your search.");
						 		ncurses_wattroff($winTabComplete, NCURSES_A_REVERSE);
						 	}
						 	
						 	ncurses_wrefresh($winTabComplete);
						}
						elseif ($arrMatches)
						{
							// Tab complete
							$strInput = $arrMatches[0];
						}
						else
						{
							// No results
							ncurses_beep();
						}
					}
					break;
	 		}
	 		
	 		// add to the string if a printing character
	 		if ($intPressed > 31 && $intPressed < 127)
	 		{
	 			$strInput .= chr($intPressed);
	 		}
	 	}
	}
	
	
	
	
	//------------------------------------------------------------------------//
	// InitConsole
	//------------------------------------------------------------------------//
	/**
	 * InitConsole()
	 *
	 * Initiates a Console window
	 *
	 * Initiates a Console window and Progress bar if specified
	 * 
	 * @param	string		$strTitle		Title for the console
	 * @param	boolean		$bolProgressBar	optional Enable Progress Bar
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function InitConsole($strTitle, $bolProgressBar = FALSE)
	{
	 	// disable input echoing
	 	ncurses_noecho();
	 	
	 	// disable input blocking
 		ncurses_keypad($this->_winFullscreen, TRUE);
	 	ncurses_timeout(0);
	 	
	 	// Get parent window size
	 	$intMaxX = 0;
	 	$intMaxY = 0;
	 	ncurses_getmaxyx($this->_winFullscreen, $intMaxY, $intMaxX);
	 	$this->_intConsoleWidth = $intMaxX - 4;
	 	
	 	// Are we allowing a progress bar?
	 	$fltProgress = NULL;
	 	if ($bolProgressBar)
	 	{
	 		// Create Progress Bar window
	 		$this->_winProgressBar = ncurses_newwin(3, $intMaxX - 2, $intMaxY - 4, 1);
	 		ncurses_wborder($this->_winProgressBar, 0, 0, 0, 0, 0, 0, 0, 0);
	 		$strProgressTitle = " Total Progress ";
	 		ncurses_mvwaddstr($this->_winProgressBar, 0, CentreText($strProgressTitle, $intMaxX - 2), $strProgressTitle);
	 		ncurses_wrefresh($this->_winProgressBar);
	 		$intMaxY -= 3;
	 		$this->_intProgressY = $intMaxY - 3;
	 		$fltProgress = 0;
	 		$this->_intStartTime = microtime(TRUE);
	 	}
	 	
	 	// Create Console Window
 		$this->_winConsole = ncurses_newwin($intMaxY - 2, $intMaxX - 2, 1, 1);
 		ncurses_wborder($this->_winConsole, 0, 0, 0, 0, 0, 0, 0, 0);
 		ncurses_mvwaddstr($this->_winConsole, 0, CentreText(" $strTitle ", $intMaxX - 2), " $strTitle ");
 		$this->_intConsoleHeight = $intMaxY - 2;
 		$this->_arrConsoleLines = Array();
 		$this->_intConsoleLine = 1;
 		$this->_intConsoleMaxView = $this->_intConsoleHeight - 1;
 		$this->ConsoleRefresh($fltProgress);
	}
	
	// ConsoleRefresh()
	function ConsoleRefresh($fltProgress = NULL)
	{ 		
 		// Get input
 		$intInput = ncurses_getch();
 		switch ($intInput)
 		{
 			case NCURSES_KEY_UP:
 				$this->_intConsoleMaxView = max($this->_intConsoleHeight - 2, $this->_intConsoleMaxView - 1);
 				break;
 				
 			case NCURSES_KEY_DOWN:
 				$this->_intConsoleMaxView = min($this->_intConsoleLine, $this->_intConsoleMaxView + 1);
 				break;
 				
 			case NCURSES_KEY_NPAGE:
 				$this->_intConsoleMaxView = min($this->_intConsoleLine, $this->_intConsoleMaxView - $this->_intConsoleHeight - 2);
 				break;
 				
 			case NCURSES_KEY_PPAGE:
 				$this->_intConsoleMaxView = min($this->_intConsoleLine, $this->_intConsoleMaxView + $this->_intConsoleHeight - 2);
 				break;
 				
 			case KEY_ESCAPE:
 				$this->_bolExit = TRUE;
 				break;
 		}
 		
		// Redraw Console
		$intY = 1;
		$intLine = 1;
		foreach ($this->_arrConsoleLines as $strLine)
		{
			foreach (explode("\n", $strLine) as $strNewLines)
			{
				if ($intY < $this->_intConsoleMaxView && $intY > ($this->_intConsoleMaxView - $this->_intConsoleHeight) + 1)
				{
					ncurses_mvwaddstr($this->_winConsole, $intLine, 1, str_repeat(" ", $this->_intConsoleWidth - 1));
					ncurses_mvwaddstr($this->_winConsole, $intLine, 1, $strNewLines);
					$intLine++;
				}
				$intY++;
			}
		}
 		
 		// Calculate bar position
 		if ($this->_intConsoleLine < ($this->_intConsoleHeight - 2))
 		{
 			$intBarPos = 2;
 		}
 		else
 		{
 			//$intBarPos = $this->_intConsoleMaxView;
 			
 			$intBarPos = ($this->_intConsoleHeight - 3) / $this->_intConsoleLine;
 			
 			$intBarPos *= $this->_intConsoleMaxView;
 			
 			//$intBarPos = round(((($this->_intConsoleLine - $this->_intConsoleMinView) / $this->_intConsoleLine) * ($this->_intConsoleHeight - 2)));
 			$intBarPos = max(min(round($intBarPos), ($this->_intConsoleHeight - 3)), 0);
 		}
 		
 		// Draw Scroll Bar
 		$intScrollColour	= 1;
	 	$intNormalColour	= 2;
	 	$intBarColour		= 3;
	 	//ncurses_init_color(NCURSES_COLOR_MAGENTA, 127, 127, 127);
	 	ncurses_init_pair($intScrollColour, NCURSES_COLOR_BLACK, NCURSES_COLOR_WHITE);
	 	ncurses_init_pair($intNormalColour, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLACK);
	 	ncurses_init_pair($intBarColour, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLACK);
		ncurses_wcolor_set($this->_winConsole, $intScrollColour);
 		ncurses_mvwaddstr($this->_winConsole, 1, $this->_intConsoleWidth, "-");
		ncurses_wcolor_set($this->_winConsole, $intBarColour);
 		for ($intRow = 2; $intRow < $this->_intConsoleHeight - 1; $intRow++)
 		{
 			if ($intBarPos == $intRow)
 			{
 				$strDrawChar = "*";
 			}
 			else
 			{
 				$strDrawChar = "|";
 			}
 			ncurses_mvwaddstr($this->_winConsole, $intRow, $this->_intConsoleWidth, $strDrawChar);
 		}
		ncurses_wcolor_set($this->_winConsole, $intScrollColour);
 		ncurses_mvwaddstr($this->_winConsole, $this->_intConsoleHeight - 2, $this->_intConsoleWidth, "+");
		ncurses_wcolor_set($this->_winConsole, $intNormalColour);
 		ncurses_wrefresh($this->_winConsole);
		
		// Redraw Progress Bar
		if ($this->_winProgressBar && $fltProgress !== NULL)
		{
			if ($fltProgress)
			{
				$intElapsed = round(microtime(TRUE) - $this->_intStartTime);
				$strElapsed = SecsToHMS($intElapsed);
				$intRemain	= (($intElapsed / $fltProgress) * 100) - $intElapsed;
				$strRemain	= SecsToHMS($intRemain);
				$strMessage = round($fltProgress)."% ($strElapsed elapsed; $strRemain est. remaining)";
			}
			else
			{
				$strMessage = "0%";
			}
			
			$strPercent = str_pad($strMessage, $this->_intConsoleWidth, " ", STR_PAD_BOTH);
			$strPercentDone = substr($strPercent, 0, round(strlen($strPercent) * ($fltProgress / 100)));
			$strPercentLeft = substr($strPercent, round(strlen($strPercent) * ($fltProgress / 100)));
			
			ncurses_wattron($this->_winProgressBar, NCURSES_A_REVERSE);
			ncurses_mvwaddstr($this->_winProgressBar, 1, 1, $strPercentDone);
			ncurses_wattroff($this->_winProgressBar, NCURSES_A_REVERSE);
			ncurses_mvwaddstr($this->_winProgressBar, 1, round(strlen($strPercent) * ($fltProgress / 100)) + 1, $strPercentLeft);
			ncurses_wrefresh($this->_winProgressBar);
		}
	}
	
	// ConsoleAddLine()
	function ConsoleAddLine($strText, $fltProgress = NULL)
	{
		// Add to array
		$this->_arrConsoleLines[] = $strText;
		foreach (explode("\n", $strText) as $strNewLines)
		{
	 		// snap view to bottom if close
	 		if ($this->_intConsoleMaxView == $this->_intConsoleLine)
	 		{
	 			$this->_intConsoleMaxView++;
	 		}
	 		
 			$this->_intConsoleLine++;
		}
		
 		$this->ConsoleRefresh($fltProgress);
	}
	
	// ConsoleRedrawLine()
	function ConsoleRedrawLine($strText, $fltProgress = NULL)
	{
		// Clear last line
		foreach (explode("\n", end($this->_arrConsoleLines)) as $intLine=>$strLine)
		{
	 		// snap view to bottom if close
	 		if ($this->_intConsoleMaxView == $this->_intConsoleLine)
	 		{
	 			$this->_intConsoleMaxView--;
	 		}
	 		
 			$this->_intConsoleLine--;
		}
		
		// Replace last line in array
		array_pop($this->_arrConsoleLines);
		$this->_arrConsoleLines[] = $strText;
		foreach (explode("\n", $strText) as $strNewLines)
		{
	 		// snap view to bottom if close
	 		if ($this->_intConsoleMaxView == $this->_intConsoleLine)
	 		{
	 			$this->_intConsoleMaxView++;
	 		}
	 		
			$this->_intConsoleLine++;
		}
		
 		$this->ConsoleRefresh($fltProgress);
	}
	
	// ConsoleGetContents()
	function ConsoleGetContents()
	{
		return $this->_arrConsoleLines;
	}
 }
?>