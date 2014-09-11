<?php

// load framework
LoadFramework();

$selCreditAccounts	= new StatementSelect(	"(Invoice I1 JOIN Invoice I2 USING (Account)) JOIN Invoice I3 USING (Account)",
											"I1.Account AS Account, I1.Id AS Jan, I2.Id AS Feb, I3.Id AS Mar, I1.TotalOwing AS JanTotalOwing", 
											"I2.InvoiceRun = '45f4cb0c0a135' AND I3.InvoiceRun = '460c6dfc434a7' AND I1.InvoiceRun = '45dfe46ae67cd' AND I1.TotalOwing < 0");

$selOriginalFucked	= new StatementSelect(	"(Invoice I1 JOIN Invoice I2 USING (Account)) JOIN Invoice I3 USING (Account)",
											"I1.Account AS Account, I1.Id AS Jan, I2.Id AS Feb, I3.Id AS Mar, I1.TotalOwing AS JanTotalOwing",
											"Account IN (1000155642,1000160255,1000160218,1000160216,1000004902,1000009448,1000009327," .
											"1000005218,1000155261,1000005325,1000155492,1000154964,1000006538,1000155718,1000155776,".
											"1000007192,1000155877,1000007440,1000007530,1000156161,1000156170,1000156320,1000156330," .
											"1000008388,1000008338,1000008339,1000156432,1000156531,1000156003,1000155932,1000156644," .
											"1000008898,1000156818,1000009170,1000156962,1000157096,1000157160,1000157184,1000155021," .
											"1000157985,1000157971,1000157970,1000157941,1000157931,1000157929,1000157902,1000157893," .
											"1000157856,1000157818,1000157797,1000157625,1000157532,1000157448,1000157423,1000157404," .
											"1000157316,1000157303,1000158253,1000010237,1000158327,1000158323,1000010331,1000158400," .
											"1000158378,1000158370,1000010395,1000155537,1000155531,1000010875,1000158445,1000011028," .
											"1000158471,1000011092,1000011136,1000158613,1000158515,1000158687,1000158957,1000159043," .
											"1000159068,1000159144,1000159137,1000159132,1000159344,1000159423,1000159617,1000159598," .
											"1000159991,1000159919,1000159875,1000159383,1000155346,1000156398,1000160072,1000160107," .
											"1000160117,1000160170,1000158842,1000160287,1000160340,1000160367,1000160377,1000160380," .
											"1000160418,1000160595,1000160650,1000161025,1000160806,1000160734,1000160778,1000160790," .
											"1000160928,1000160940,1000161116,1000161129,1000161682,1000161406,1000161540,1000161147," .
											"1000161770,1000161180,1000161279,1000161290,1000161352,1000161384,1000161648,1000161688," .
											"1000161735,1000161738,1000161744,1000161745,1000161759,1000161801,1000161970,1000161951," .
											"1000162024,1000162034,1000162067,1000162174,1000162230,1000162271,1000162281,1000162336," .
											"1000162349,1000162412,1000162479,1000162498,1000162513,1000162670)" .
											" AND I2.InvoiceRun = '45f4cb0c0a135' AND I3.InvoiceRun = '460c6dfc434a7' AND I1.InvoiceRun = '45dfe46ae67cd'");

$selCreditAccounts->Execute();
$selOriginalFucked->Execute();

$arrCreditAccounts = $selCreditAccounts->FetchAll();
$arrOriginalFucked = $selOriginalFucked->FetchAll();

ob_start();

$arrPDFs = Array();

// LOOP!
$intMatches = 0;
foreach ($arrCreditAccounts as $arrCreditAccount)
{
	echo " + Matching {$arrCreditAccount['Account']}...\t\t\t";
	ob_flush();
	
	// PDFs
	$arrPDFs['Jan'][] = $arrCreditAccount['Account']."_".$arrCreditAccount['Jan'];
	$arrPDFs['Feb'][] = $arrCreditAccount['Account']."_0".$arrCreditAccount['Feb'];
	$arrPDFs['Mar'][] = $arrCreditAccount['Account']."_0".$arrCreditAccount['Mar'];
	
	foreach ($arrOriginalFucked as $intKey=>$arrFucked)
	{
		if ($arrFucked['Account'] == $arrCreditAccount['Account'])
		{
			// Found our match
			echo "[   OK   ]\n";
			$arrOriginalFucked[$intKey]['Matched']	= TRUE;
			$intMatches++;
			continue 2;
		}
	}
	
	// No match
	echo "[ FAILED ]\n";
}

foreach ($arrOriginalFucked as $intKey=>$arrFucked)
{
	if (!$arrFucked['Matched'])
	{
		// PDFs
		$arrPDFs['Jan'][] = $arrFucked['Account']."_".$arrFucked['Jan'];
		$arrPDFs['Feb'][] = $arrFucked['Account']."_0".$arrFucked['Feb'];
		$arrPDFs['Mar'][] = $arrFucked['Account']."_0".$arrFucked['Mar'];
	}
}

$intTotalCredit = count($arrCreditAccounts);
$intTotalFucked = count($arrOriginalFucked);
echo "\n * There were $intMatches from $intTotalCredit Credited Accounts and $intTotalFucked originally screwed Accounts.\n\n";

// Generate ZIP files
foreach ($arrPDFs as $strMonth=>$arrInvoices)
{
	// Make parameter list
	switch ($strMonth)
	{
		case 'Jan':
			$strPath = FILES_BASE_PATH."invoices/2007/1/";
			break;
		case 'Feb':
			$strPath = FILES_BASE_PATH."/invoices/2007/2/";
			break;
		case 'Mar':
			$strPath = FILES_BASE_PATH."invoices/2007/3/";
			break;
		default:
			Debug("ERROR");
			die;
	}
	$strParams = $strPath.implode(".pdf $strPath", $arrInvoices).".pdf";
	
	// Make ZIP
	$strCommand = "zip -j /home/flame/$strMonth.zip $strParams";
	echo $strCommand."; ";
}

?>




