<?php

// Load app
LoacApplication();

$chgLatePayment = new ChargeLatePayment();


// Account 1
$arrInvoice = Array();
$arrInvoice['Payment']	= 0;
$arrInvoice['Overdue']	= 50 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= -2;
echo "1.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";

// Account 2
$arrInvoice = Array();
$arrInvoice['Payment']	= 0;
$arrInvoice['Overdue']	= 100 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= -1;
echo "2.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";

// Account 3
$arrInvoice = Array();
$arrInvoice['Payment']	= 100;
$arrInvoice['Overdue']	= 200 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= 1;
echo "3.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";

// Account 4
$arrInvoice = Array();
$arrInvoice['Payment']	= 400;
$arrInvoice['Overdue']	= 400 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= 0;
echo "4.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";

// Account 5
$arrInvoice = Array();
$arrInvoice['Payment']	= 1000;
$arrInvoice['Overdue']	= 800 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= NULL;
echo "5.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";

// Account 6
$arrInvoice = Array();
$arrInvoice['Payment']	= 50;
$arrInvoice['Overdue']	= 0 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= -2;
echo "6.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";

// Account 7
$arrInvoice = Array();
$arrInvoice['Payment']	= 0;
$arrInvoice['Overdue']	= 0 - $arrInvoice['Payment'];
$arrAccount = Array();
$arrAccount['DisableLatePayment']	= NULL;
echo "7.\t";
$intDLP = $chgLatePayment->Generate($arrInvoice, $arrAccount);
$intDLP = ($intDLP === NULL) ? "NULL" : $intDLP;
echo "Modified DLP: $intDLP\n";
?>