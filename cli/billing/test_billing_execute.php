<?php

// load application
require_once("../../flex.require.php");
LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);



// Use transactions so we don't fuck the database :D
$appBilling->db->TransactionStart();

$selAccounts = new StatementSelect("Account", "*", "Id = 1000155448");
$selAccounts->Execute();
$arrAccounts = $selAccounts->FetchAll();

$appBilling->_strInvoiceRun = "465f4b2218916";
/*Debug(*/$appBilling->GenerateInvoices($arrAccounts, TRUE)/*)*/;

//$appBilling->Revoke();

$appBilling->db->TransactionRollback();
?>