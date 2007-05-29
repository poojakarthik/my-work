<?php

// load application
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);



// Use transactions so we don't fuck the database :D
$appBilling->db->TransactionStart();

$selAccounts = new StatementSelect("Account", "*", "Id = 1000155226");
$selAccounts->Execute();
$arrAccounts = $selAccounts->FetchAll();

$appBilling->_strInvoiceRun = "465a21828604a";
Debug($appBilling->GenerateInvoices($arrAccounts, TRUE));

$appBilling->db->TransactionRollback();
?>