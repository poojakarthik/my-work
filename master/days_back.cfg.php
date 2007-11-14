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
	$arrScript['RecurringDay']				= 		-17;
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




?>