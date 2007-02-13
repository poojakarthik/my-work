<?php
// This file is a place to dump todo comments that have no place better to live


// ---------------------------------------------------------------------------//
// RICH
// ---------------------------------------------------------------------------//

//TODO!rich! check any of our charges that do not match etech... find out why

//TODO!rich! Test shared plans

// when you have some spare time
//TODO!rich! get a list of extension numbers for all employees and add them to the DB - Waiting on reply from scott
//TODO!rich! also get DOB, phone & mobile 
//TODO!print charges and credits on bill
// itemised
// print on front page
// print in service summary
//TODO!rich! print charges and credits on bill
//TODO!rich! don't add dates to S&E on bill
//TODO!rich! fix this error in billing...
/*
Mixing of GROUP columns (MIN(),MAX(),COUNT(),...) with no GROUP columns is illegal if there is no GROUP BY clause
 Call Stack:
#0  DatabaseAccess->Error() called at [/usr/share/vixen/framework/db_access.php:1841]
#1  StatementSelect->__construct("InvoiceTemp", "MIN(Id) AS MinId, MAX(Id) AS MaxId, COUNT(Id) AS Invoices, Invoi...", "Status = 105") called at [/usr/share/vixen/billing_app/modules/module_etech.php:1103]
#2  BillingModuleEtech->BuildOutput("45c8060d0f81f", TRUE) called at [/usr/share/vixen/billing_app/modules/module_etech.php:1248]
#3  BillingModuleEtech->BuildSample("45c8060d0f81f") called at [/usr/share/vixen/billing_app/application.php:590]
#4  ApplicationBilling->Execute() called at [/usr/share/vixen/billing_app/billing_execute.php:21]



</pre>

Fatal error: Call to a member function fetch_field() on a non-object in /usr/share/vixen/framework/db_access.php on line 1973
root@catwalk:/usr/share/vixen/billing_app# php billing_commit.php



Building and Sending Invoice Output...
Warning: Missing argument 1 for BillingModulePrint::BuildOutput(), called in /usr/share/vixen/billing_app/application.php on line 847 and defined in /usr/share/vixen/billing_app/modules/module_printing.php on line 672
                [   OK   ]
Building and Sending Invoice Output...
<pre>
Mixing of GROUP columns (MIN(),MAX(),COUNT(),...) with no GROUP columns is illegal if there is no GROUP BY clause
 Call Stack:
#0  DatabaseAccess->Error() called at [/usr/share/vixen/framework/db_access.php:1841]
#1  StatementSelect->__construct("Invoice", "MIN(Id) AS MinId, MAX(Id) AS MaxId, COUNT(Id) AS Invoices, Invoi...", "Status = 105") called at [/usr/share/vixen/billing_app/modules/module_etech.php:1103]
#2  BillingModuleEtech->BuildOutput() called at [/usr/share/vixen/billing_app/application.php:847]
#3  ApplicationBilling->Commit() called at [/usr/share/vixen/billing_app/billing_commit.php:21]



</pre>

Fatal error: Call to a member function fetch_field() on a non-object in /usr/share/vixen/framework/db_access.php on line 1973

*/



// ---------------------------------------------------------------------------//
// BASH
// ---------------------------------------------------------------------------//

//TODO!bash! the following are listed in order of importance, start at the top of the list

//TODO!bash! filter CDR list by Record type
//TODO!bash! [UNTESTED] unbilled charges
//TODO!bash! [  DONE  ] invoice details
//TODO!bash! list_account, seach by contact name & service #

//TODO!bash! add the following items to the interface, use the exact text shown below
//TODO!bash! replace the crap ascii art with checkbox/radio button etc.

//TODO!bash! checkbox (add/edit account);
//TODO!bash! NDD Fee [] Charge a fee if account is not paid by direct debit

//TODO!bash! radio buttons (add/edit account);
//TODO!bash! Late Payments	() Charge a late payment fee
//TODO!bash! 				() Don't charge a late payment fee on the next invoice
//TODO!bash! 				() Never charge a late payment fee

//TODO!bash! Dropdown (add/edit service);
//TODO!bash! Cost Centre  [              |V]

//TODO!bash! make sure we are parsing and importing all details required for the above fields

//TODO!bash! add the following reports and test them
//TODO!bash! report : profit per rate
/*
	return ->	Rate.Name	Cost	Charge	$profit	%profit
	
	allow selection of start and end date for report
	
*/

//TODO!bash! report : profit per RatePlan
/*
	return ->	RatePlan.Name	Cost	Charge	$profit	%profit
	
	allow selection of start and end date for report
	
*/

//TODO!bash! report profit per customer
/*
	return ->	Account.Id Account.BusinessName	Cost	Charge	$profit	%profit
	
	allow selection of start and end date for report
*/



// ---------------------------------------------------------------------------//
// FLAME
// ---------------------------------------------------------------------------//

//TODO!flame! Schedule MySQL Backup
//TODO!flame! MySQL Recovery

//TODO!flame! Rollback CDR files
//TODO!flame! Credits

//TODO!flame! Clean and validate input as it comes into the system
//TODO!flame! USER_ID
//TODO!flame! Duplicates in DB, caused by cleaning FNNs
//TODO!flame! How to generate Ref # for Payments
//TODO!flame! Auto add one off connection fee
//TODO!flame! Timezones
//TODO!flame! 1900 Report
//TODO!flame! SMS, International/Multipart
//TODO!flame! SMS, Display in GUI

//TODO!flame! Testing Server
//TODO!flame! Live Server
//TODO!flame! Billing Server

//TODO!flame! Zip file : remove /home/blah
//TODO!flame! Unzip : don't ask for password

//TODO!flame! Provisioning Permissions
//TODO!flame! Customer Service	: Full Service, Activate, DeActivate, Preselection
//TODO!flame! Credit Manager	: Bar / UnBar

//TODO!flame! REPORTS
//TODO!flame! Customers who don't have enough info in the system to allow validation

//TODO!flame! BILLING
//TODO!flame! Insert Specification
//TODO!flame! Special Offer Specification

//TODO!flame! NORMALISATION
//TODO!flame! Program local
//TODO!flame! NZ PRS
//TODO!flame! Some inbound calls have full calling no : do we show this
//TODO!flame! missing some IDD rates ?
//TODO!flame! missing some IDD codes (unitel)
//TODO!flame! inbound S&E charges ($20) in CDRs WTF ????
//TODO!flame! Record Types for Inbound
//TODO!flame! OC&C

//TODO!flame! RATES
//TODO!flame! Congo, Cyprus, Korea, Dominica, East Timor, NZ Mobile

//TODO!flame! Residential rates include GST
//TODO!flame! remove parse_service_rate_group & import telcoblue_old

// ---------------------------------------------------------------------------//
// Later
// ---------------------------------------------------------------------------//

//TODO!later! Select correct list of accounts to invoice

//TODO!later! Master to look for VIXEN_FAIL, segfault etc

//TODO!later! Reserved Usernames

//TODO!later! Equipment
//TODO!later! Brand, Lines, Extns, Maintainer, Model, Year, Notes
//TODO!later! SOAP, ABN
//TODO!later! ASIC LINK

//TODO!later! White screen of death on oversized page loads

//TODO!later! Dealer Support
//TODO!later! add Dealer to AccountGroup & Service

//TODO!later! Disable write-back cache
//TODO!later! Full reporting of server load
//TODO!later! Nagios

//TODO!later! PHP Command Line Options
//TODO!later! Whiptail

// ---------------------------------------------------------------------------//
// Notes
// ---------------------------------------------------------------------------//

//TODO!notes! Unitel does iBurst

//TODO!notes! Delinquents 	= illegal/bad churn
//TODO!notes! UnApplied CDR = can't find owner
//TODO!notes! Unrated 		= can't rate call

?>
