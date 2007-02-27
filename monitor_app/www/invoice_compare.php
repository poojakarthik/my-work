<?php

// CDR View

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen / etech Invoice Comparison');

// page link
$objPage->SetPageLink('[ Compare Invoice ]');

// menu
$objPage->AddBackLink();

// get Account Id
$intAccount = (int)$_GET['Account'];

// get Invoice Run
$strInvoiceRun = $_GET['InvoiceRun'];

// display Invoice Details
$objPage->ShowTempInvoice($intAccount, $strInvoiceRun);

// display etech invoice total
$strEtech = number_format((float)$_GET['Etech'], 2);
$this->AddTitle("Etech Invoice Total : $strEtech");

// display Service Totals
$objPage->ShowAccountServiceTotals($intAccount, $strInvoiceRun);

// display CDR Totals
$objPage->ShowAccountCDRTotals($intAccount, $strInvoiceRun);

// display Charge Totals
$objPage->ShowAccountChargeTotals($intAccount, $strInvoiceRun);

// display Charges
$objPage->ShowAccountCharges($intAccount, $strInvoiceRun);

// display the page
$objPage->Render();
?>
