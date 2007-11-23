<?php

$arrConfig['MaxRuns']			= 100000;
$arrConfig['Sleep']				= 60;
$arrConfig['Verbose']			= TRUE;
$arrConfig['GMTOffset']			= 10;
$arrConfig['RefreshRate']		= 1;


$arrScript	= Array();

	$arrScript['StartTime']                 =       3600*11;	// 1100
	$arrScript['FinishTime']                =       3600*15;	// 1500
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	$arrScript['RecurringDay']				= 		-16;
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	$arrScript['SubScript'] = Array();
		
		// Billing Samples
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/master/test.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/master/';
		$arrScript['SubScript']['CopyDB']				= $arrSubscript;
	
$arrConfig['Script']['Today']	= $arrScript;


$arrScript	= Array();

	$arrScript['StartTime']                 =       3600*2;	// 1100
	$arrScript['FinishTime']                =       3600*3;	// 1300
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	$arrScript['RecurringDay']				= 		-18;
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	$arrScript['SubScript'] = Array();
		
		// Billing Samples
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/master/test.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/master/';
		$arrScript['SubScript']['CopyDB']				= $arrSubscript;
	
$arrConfig['Script']['Minus18']	= $arrScript;


$arrScript	= Array();

	$arrScript['StartTime']                 =       3600*2;	// 1100
	$arrScript['FinishTime']                =       3600*3;	// 1300
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	$arrScript['RecurringDay']				= 		-10;
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	$arrScript['SubScript'] = Array();
		
		// Billing Samples
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/master/test.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/master/';
		$arrScript['SubScript']['CopyDB']				= $arrSubscript;
	
$arrConfig['Script']['Minus10']	= $arrScript;


//----------------------------------------------------------------------------//
// 5 Minutes Test
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       25200;
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       64800;
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       300;
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/master/test.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/master/';
	
	$arrConfig['Script']['Payment']         = $arrScript;
//----------------------------------------------------------------------------//


?>