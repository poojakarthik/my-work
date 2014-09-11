<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// print_example
//----------------------------------------------------------------------------//
/**
 * print_example
 *
 * Prints an example file for the bill printers
 *
 * Prints an example file for the bill printers, using fake data.
 *
 * @file		print_example.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste' Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
require_once("../definitions.php");

//----------------------------------------------------------------------------//
// Define the file format
//----------------------------------------------------------------------------//

// Invoice Details record
$arrDefine['InvoiceDetails']	['RecordType']		['Start']	= 0;
$arrDefine['InvoiceDetails']	['RecordType']		['Length']	= 2;
$arrDefine['InvoiceDetails']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['InvoiceDetails']	['RecordType']		['Value']	= "10";

$arrDefine['InvoiceDetails']	['InvoiceGroup']		['Start']	= 2;
$arrDefine['InvoiceDetails']	['InvoiceGroup']		['Length']	= 2;
$arrDefine['InvoiceDetails']	['InvoiceGroup']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['InvoiceDetails']	['Inserts']			['Start']	= 4;
$arrDefine['InvoiceDetails']	['Inserts']			['Length']	= 6;
$arrDefine['InvoiceDetails']	['Inserts']			['Type']	= BILL_TYPE_BINARY;

$arrDefine['InvoiceDetails']	['InvoiceNo']		['Start']	= 10;
$arrDefine['InvoiceDetails']	['InvoiceNo']		['Length']	= 10;
$arrDefine['InvoiceDetails']	['InvoiceNo']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['InvoiceDetails']	['BillPeriod']		['Start']	= 20;
$arrDefine['InvoiceDetails']	['BillPeriod']		['Length']	= 12;
$arrDefine['InvoiceDetails']	['BillPeriod']		['Type']	= BILL_TYPE_CHAR;

$arrDefine['InvoiceDetails']	['IssueDate']		['Start']	= 32;
$arrDefine['InvoiceDetails']	['IssueDate']		['Length']	= 11;
$arrDefine['InvoiceDetails']	['IssueDate']		['Type']	= BILL_TYPE_LONGDATE;

$arrDefine['InvoiceDetails']	['AccountNo']		['Start']	= 43;
$arrDefine['InvoiceDetails']	['AccountNo']		['Length']	= 20;
$arrDefine['InvoiceDetails']	['AccountNo']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['InvoiceDetails']	['OpeningBalance']	['Start']	= 63;
$arrDefine['InvoiceDetails']	['OpeningBalance']	['Length']	= 11;
$arrDefine['InvoiceDetails']	['OpeningBalance']	['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['InvoiceDetails']	['WeReceived']		['Start']	= 74;
$arrDefine['InvoiceDetails']	['WeReceived']		['Length']	= 11;
$arrDefine['InvoiceDetails']	['WeReceived']		['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['InvoiceDetails']	['Adjustments']		['Start']	= 85;
$arrDefine['InvoiceDetails']	['Adjustments']		['Length']	= 11;
$arrDefine['InvoiceDetails']	['Adjustments']		['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['InvoiceDetails']	['Balance']			['Start']	= 96;
$arrDefine['InvoiceDetails']	['Balance']			['Length']	= 11;
$arrDefine['InvoiceDetails']	['Balance']			['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['InvoiceDetails']	['BillTotal']		['Start']	= 107;
$arrDefine['InvoiceDetails']	['BillTotal']		['Length']	= 11;
$arrDefine['InvoiceDetails']	['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['InvoiceDetails']	['TotalOwing']		['Start']	= 118;
$arrDefine['InvoiceDetails']	['TotalOwing']		['Length']	= 11;
$arrDefine['InvoiceDetails']	['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['InvoiceDetails']	['CustomerName']	['Start']	= 129;
$arrDefine['InvoiceDetails']	['CustomerName']	['Length']	= 60;
$arrDefine['InvoiceDetails']	['CustomerName']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['InvoiceDetails']	['PropertyName']	['Start']	= 189;
$arrDefine['InvoiceDetails']	['PropertyName']	['Length']	= 60;
$arrDefine['InvoiceDetails']	['PropertyName']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['InvoiceDetails']	['AddressLine1']	['Start']	= 249;
$arrDefine['InvoiceDetails']	['AddressLine1']	['Length']	= 60;
$arrDefine['InvoiceDetails']	['AddressLine1']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['InvoiceDetails']	['Suburb']			['Start']	= 309;
$arrDefine['InvoiceDetails']	['Suburb']			['Length']	= 30;
$arrDefine['InvoiceDetails']	['Suburb']			['Type']	= BILL_TYPE_CHAR;

$arrDefine['InvoiceDetails']	['State']			['Start']	= 339;
$arrDefine['InvoiceDetails']	['State']			['Length']	= 3;
$arrDefine['InvoiceDetails']	['State']			['Type']	= BILL_TYPE_CHAR;

$arrDefine['InvoiceDetails']	['Postcode']		['Start']	= 342;
$arrDefine['InvoiceDetails']	['Postcode']		['Length']	= 4;
$arrDefine['InvoiceDetails']	['Postcode']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Start']	= 346;
$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Length']	= 11;
$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Type']	= BILL_TYPE_LONGDATE;


// Graph Header record
$arrDefine['GraphHeader']		['RecordType']		['Start']	= 0;
$arrDefine['GraphHeader']		['RecordType']		['Length']	= 2;
$arrDefine['GraphHeader']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphHeader']		['RecordType']		['Value']	= "20";

$arrDefine['GraphHeader']		['GraphType']		['Start']	= 2;
$arrDefine['GraphHeader']		['GraphType']		['Length']	= 2;
$arrDefine['GraphHeader']		['GraphType']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['GraphHeader']		['GraphTitle']		['Start']	= 4;
$arrDefine['GraphHeader']		['GraphTitle']		['Length']	= 20;
$arrDefine['GraphHeader']		['GraphTitle']		['Type']	= BILL_TYPE_CHAR;

$arrDefine['GraphHeader']		['XTitle']			['Start']	= 24;
$arrDefine['GraphHeader']		['XTitle']			['Length']	= 20;
$arrDefine['GraphHeader']		['XTitle']			['Type']	= BILL_TYPE_CHAR;

$arrDefine['GraphHeader']		['YTitle']			['Start']	= 34;
$arrDefine['GraphHeader']		['YTitle']			['Length']	= 20;
$arrDefine['GraphHeader']		['YTitle']			['Type']	= BILL_TYPE_CHAR;

$arrDefine['GraphHeader']		['ZTitle']			['Start']	= 44;
$arrDefine['GraphHeader']		['ZTitle']			['Length']	= 20;
$arrDefine['GraphHeader']		['ZTitle']			['Type']	= BILL_TYPE_CHAR;

$arrDefine['GraphHeader']		['Description']		['Start']	= 54;
$arrDefine['GraphHeader']		['Description']		['Length']	= 100;
$arrDefine['GraphHeader']		['Description']		['Type']	= BILL_TYPE_CHAR;


// Graph Data record
$arrDefine['GraphData']			['RecordType']		['Start']	= 0;
$arrDefine['GraphData']			['RecordType']		['Length']	= 2;
$arrDefine['GraphData']			['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphData']			['RecordType']		['Value']	= "21";

$arrDefine['GraphData']			['Title']			['Start']	= 2;
$arrDefine['GraphData']			['Title']			['Length']	= 20;
$arrDefine['GraphData']			['Title']			['Type']	= BILL_TYPE_CHAR;

$arrDefine['GraphData']			['Value']			['Start']	= 22;
$arrDefine['GraphData']			['Value']			['Length']	= 10;
$arrDefine['GraphData']			['Value']			['Type']	= BILL_TYPE_FLOAT;


// Graph Footer record
$arrDefine['GraphFooter']		['RecordType']		['Start']	= 0;
$arrDefine['GraphFooter']		['RecordType']		['Length']	= 2;
$arrDefine['GraphFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphFooter']		['RecordType']		['Value']	= "29";

$arrDefine['GraphFooter']		['TotalSamples']	['Start']	= 2;
$arrDefine['GraphFooter']		['TotalSamples']	['Length']	= 6;
$arrDefine['GraphFooter']		['TotalSamples']	['Type']	= BILL_TYPE_INTEGER;


// Vertical Breakdown Bargraph Column Header record
$arrDefine['GraphColumn']		['RecordType']		['Start']	= 0;
$arrDefine['GraphColumn']		['RecordType']		['Length']	= 2;
$arrDefine['GraphColumn']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphColumn']		['RecordType']		['Value']	= "30";

$arrDefine['GraphColumn']		['Title']			['Start']	= 2;
$arrDefine['GraphColumn']		['Title']			['Length']	= 20;
$arrDefine['GraphColumn']		['Title']			['Type']	= BILL_TYPE_CHAR;


// Vertical Breakdown Bargraph Column Footer record
$arrDefine['GraphColumnFooter']	['RecordType']		['Start']	= 0;
$arrDefine['GraphColumnFooter']	['RecordType']		['Length']	= 2;
$arrDefine['GraphColumnFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphColumnFooter']	['RecordType']		['Value']	= "31";


// Horizontal Split Bargraph Row Header record
$arrDefine['GraphRowHeader']	['RecordType']		['Start']	= 0;
$arrDefine['GraphRowHeader']	['RecordType']		['Length']	= 2;
$arrDefine['GraphRowHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphRowHeader']	['RecordType']		['Value']	= "32";

$arrDefine['GraphRowHeader']	['Title']			['Start']	= 2;
$arrDefine['GraphRowHeader']	['Title']			['Length']	= 20;
$arrDefine['GraphRowHeader']	['Title']			['Type']	= BILL_TYPE_CHAR;


// Horizontal Split Bargraph Row Footer record
$arrDefine['GraphRowFooter']	['RecordType']		['Start']	= 0;
$arrDefine['GraphRowFooter']	['RecordType']		['Length']	= 2;
$arrDefine['GraphRowFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphRowFooter']	['RecordType']		['Value']	= "33";


// Linegraph Line Header record
$arrDefine['GraphLineHeader']	['RecordType']		['Start']	= 0;
$arrDefine['GraphLineHeader']	['RecordType']		['Length']	= 2;
$arrDefine['GraphLineHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphLineHeader']	['RecordType']		['Value']	= "34";

$arrDefine['GraphLineHeader']	['Title']			['Start']	= 2;
$arrDefine['GraphLineHeader']	['Title']			['Length']	= 20;
$arrDefine['GraphLineHeader']	['Title']			['Type']	= BILL_TYPE_CHAR;


// LinegraphLine Bargraph Row Footer record
$arrDefine['GraphLineFooter']	['RecordType']		['Start']	= 0;
$arrDefine['GraphLineFooter']	['RecordType']		['Length']	= 2;
$arrDefine['GraphLineFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['GraphLineFooter']	['RecordType']		['Value']	= "35";


// Charge Totals Header record
$arrDefine['ChargeTotalsHeader']['RecordType']		['Start']	= 0;
$arrDefine['ChargeTotalsHeader']['RecordType']		['Length']	= 2;
$arrDefine['ChargeTotalsHeader']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ChargeTotalsHeader']['RecordType']		['Value']	= "40";


// Charge Total record
$arrDefine['ChargeTotal']		['RecordType']		['Start']	= 0;
$arrDefine['ChargeTotal']		['RecordType']		['Length']	= 2;
$arrDefine['ChargeTotal']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ChargeTotal']		['RecordType']		['Value']	= "41";

$arrDefine['ChargeTotal']		['ChargeName']		['Start']	= 2;
$arrDefine['ChargeTotal']		['ChargeName']		['Length']	= 40;
$arrDefine['ChargeTotal']		['ChargeName']		['Type']	= BILL_TYPE_CHAR;

$arrDefine['ChargeTotal']		['ChargeTotal']		['Start']	= 42;
$arrDefine['ChargeTotal']		['ChargeTotal']		['Length']	= 11;
$arrDefine['ChargeTotal']		['ChargeTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;


// Charge Totals Footer record
$arrDefine['ChargeTotalsFooter']['RecordType']		['Start']	= 0;
$arrDefine['ChargeTotalsFooter']['RecordType']		['Length']	= 2;
$arrDefine['ChargeTotalsFooter']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ChargeTotalsFooter']['RecordType']		['Value']	= "49";

$arrDefine['ChargeTotalsFooter']['BillTotal']		['Start']	= 2;
$arrDefine['ChargeTotalsFooter']['BillTotal']		['Length']	= 11;
$arrDefine['ChargeTotalsFooter']['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;


// Payment Data record
$arrDefine['PaymentData']		['RecordType']		['Start']	= 0;
$arrDefine['PaymentData']		['RecordType']		['Length']	= 2;
$arrDefine['PaymentData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['PaymentData']		['RecordType']		['Value']	= "11";

$arrDefine['PaymentData']		['BillExpBillerId']	['Start']	= 2;
$arrDefine['PaymentData']		['BillExpBillerId']	['Length']	= 6;
$arrDefine['PaymentData']		['BillExpBillerId']	['Type']	= BILL_TYPE_INTEGER;
$arrDefine['PaymentData']		['BillExpBillerId']	['Value']	= 000376;

$arrDefine['PaymentData']		['BillExpRef']		['Start']	= 8;
$arrDefine['PaymentData']		['BillExpRef']		['Length']	= 25;
$arrDefine['PaymentData']		['BillExpRef']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['PaymentData']		['BPayBillerId']	['Start']	= 33;
$arrDefine['PaymentData']		['BPayBillerId']	['Length']	= 5;
$arrDefine['PaymentData']		['BPayBillerId']	['Type']	= BILL_TYPE_INTEGER;
$arrDefine['PaymentData']		['BPayBillerId']	['Value']	= 63412;

$arrDefine['PaymentData']		['BPayCustomerRef']	['Start']	= 38;
$arrDefine['PaymentData']		['BPayCustomerRef']	['Length']	= 25;
$arrDefine['PaymentData']		['BPayCustomerRef']	['Type']	= BILL_TYPE_INTEGER;

$arrDefine['PaymentData']		['AccountNo']		['Start']	= 63;
$arrDefine['PaymentData']		['AccountNo']		['Length']	= 20;
$arrDefine['PaymentData']		['AccountNo']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['PaymentData']		['DateDue']			['Start']	= 83;
$arrDefine['PaymentData']		['DateDue']			['Length']	= 13;
$arrDefine['PaymentData']		['DateDue']			['Type']	= BILL_TYPE_LONGDATE;

$arrDefine['PaymentData']		['TotalOwing']		['Start']	= 96;
$arrDefine['PaymentData']		['TotalOwing']		['Length']	= 11;
$arrDefine['PaymentData']		['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;

$arrDefine['PaymentData']		['CustomerName']	['Start']	= 107;
$arrDefine['PaymentData']		['CustomerName']	['Length']	= 60;
$arrDefine['PaymentData']		['CustomerName']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['PaymentData']		['PropertyName']	['Start']	= 167;
$arrDefine['PaymentData']		['PropertyName']	['Length']	= 60;
$arrDefine['PaymentData']		['PropertyName']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['PaymentData']		['AddressLine1']	['Start']	= 227;
$arrDefine['PaymentData']		['AddressLine1']	['Length']	= 60;
$arrDefine['PaymentData']		['AddressLine1']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['PaymentData']		['AddressLine2']	['Start']	= 287;
$arrDefine['PaymentData']		['AddressLine2']	['Length']	= 60;
$arrDefine['PaymentData']		['AddressLine2']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['PaymentData']		['SpecialOffer1']	['Start']	= 347;
$arrDefine['PaymentData']		['SpecialOffer1']	['Length']	= 250;
$arrDefine['PaymentData']		['SpecialOffer1']	['Type']	= BILL_TYPE_CHAR;

$arrDefine['PaymentData']		['SpecialOffer2']	['Start']	= 597;
$arrDefine['PaymentData']		['SpecialOffer2']	['Length']	= 250;
$arrDefine['PaymentData']		['SpecialOffer2']	['Type']	= BILL_TYPE_CHAR;


// Service Summary Header record
$arrDefine['SvcSummaryHeader']	['RecordType']		['Start']	= 0;
$arrDefine['SvcSummaryHeader']	['RecordType']		['Length']	= 2;
$arrDefine['SvcSummaryHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['SvcSummaryHeader']	['RecordType']		['Value']	= "50";


// Service Summary Footer record
$arrDefine['SvcSummaryFooter']	['RecordType']		['Start']	= 0;
$arrDefine['SvcSummaryFooter']	['RecordType']		['Length']	= 2;
$arrDefine['SvcSummaryFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['SvcSummaryFooter']	['RecordType']		['Value']	= "59";


// Service Summary Service Header record
$arrDefine['SvcSummSvcHeader']	['RecordType']		['Start']	= 0;
$arrDefine['SvcSummSvcHeader']	['RecordType']		['Length']	= 2;
$arrDefine['SvcSummSvcHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['SvcSummSvcHeader']	['RecordType']		['Value']	= "60";

$arrDefine['SvcSummSvcHeader']	['FNN']				['Start']	= 2;
$arrDefine['SvcSummSvcHeader']	['FNN']				['Length']	= 10;
$arrDefine['SvcSummSvcHeader']	['FNN']				['Type']	= BILL_TYPE_INTEGER;


// Service Summary Data record
$arrDefine['SvcSummaryData']	['RecordType']		['Start']	= 0;
$arrDefine['SvcSummaryData']	['RecordType']		['Length']	= 2;
$arrDefine['SvcSummaryData']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['SvcSummaryData']	['RecordType']		['Value']	= "61";

$arrDefine['SvcSummaryData']	['CallType']		['Start']	= 2;
$arrDefine['SvcSummaryData']	['CallType']		['Length']	= 40;
$arrDefine['SvcSummaryData']	['CallType']		['Type']	= BILL_TYPE_CHAR;

$arrDefine['SvcSummaryData']	['CallCount']		['Start']	= 42;
$arrDefine['SvcSummaryData']	['CallCount']		['Length']	= 10;
$arrDefine['SvcSummaryData']	['CallCount']		['Type']	= BILL_TYPE_INTEGER;

$arrDefine['SvcSummaryData']	['Charge']			['Start']	= 52;
$arrDefine['SvcSummaryData']	['Charge']			['Length']	= 11;
$arrDefine['SvcSummaryData']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;


// Service Summary Service Footer record
$arrDefine['SvcSummSvcFooter']	['RecordType']		['Start']	= 0;
$arrDefine['SvcSummSvcFooter']	['RecordType']		['Length']	= 2;
$arrDefine['SvcSummSvcFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['SvcSummSvcFooter']	['RecordType']		['Value']	= "69";

$arrDefine['SvcSummSvcFooter']	['TotalCharge']		['Start']	= 2;
$arrDefine['SvcSummSvcFooter']	['TotalCharge']		['Length']	= 11;
$arrDefine['SvcSummSvcFooter']	['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;


// Itemised Call Header record
$arrDefine['ItemisedHeader']	['RecordType']		['Start']	= 0;
$arrDefine['ItemisedHeader']	['RecordType']		['Length']	= 2;
$arrDefine['ItemisedHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemisedHeader']	['RecordType']		['Value']	= "70";


// Itemised Call Service Header record
$arrDefine['ItemSvcHeader']		['RecordType']		['Start']	= 0;
$arrDefine['ItemSvcHeader']		['RecordType']		['Length']	= 2;
$arrDefine['ItemSvcHeader']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemSvcHeader']		['RecordType']		['Value']	= "80";

$arrDefine['ItemSvcHeader']		['FNN']				['Start']	= 2;
$arrDefine['ItemSvcHeader']		['FNN']				['Length']	= 10;
$arrDefine['ItemSvcHeader']		['FNN']				['Type']	= BILL_TYPE_INTEGER;


// Itemised Call Type Header record
$arrDefine['ItemCallTypeHeader']['RecordType']		['Start']	= 0;
$arrDefine['ItemCallTypeHeader']['RecordType']		['Length']	= 2;
$arrDefine['ItemCallTypeHeader']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemCallTypeHeader']['RecordType']		['Value']	= "90";

$arrDefine['ItemCallTypeHeader']['CallType']		['Start']	= 2;
$arrDefine['ItemCallTypeHeader']['CallType']		['Length']	= 40;
$arrDefine['ItemCallTypeHeader']['CallType']		['Type']	= BILL_TYPE_CHAR;


// Itemised Call Data Call record
$arrDefine['ItemisedDataCall']	['RecordType']		['Start']	= 0;
$arrDefine['ItemisedDataCall']	['RecordType']		['Length']	= 2;
$arrDefine['ItemisedDataCall']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemisedDataCall']	['RecordType']		['Value']	= "91";

$arrDefine['ItemisedDataCall']	['Date']			['Start']	= 2;
$arrDefine['ItemisedDataCall']	['Date']			['Length']	= 10;
$arrDefine['ItemisedDataCall']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;

$arrDefine['ItemisedDataCall']	['Time']			['Start']	= 12;
$arrDefine['ItemisedDataCall']	['Time']			['Length']	= 8;
$arrDefine['ItemisedDataCall']	['Time']			['Type']	= BILL_TYPE_TIME;

$arrDefine['ItemisedDataCall']	['CalledParty']		['Start']	= 20;
$arrDefine['ItemisedDataCall']	['CalledParty']		['Length']	= 30;
$arrDefine['ItemisedDataCall']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

$arrDefine['ItemisedDataCall']	['Duration']		['Start']	= 50;
$arrDefine['ItemisedDataCall']	['Duration']		['Length']	= 9;
$arrDefine['ItemisedDataCall']	['Duration']		['Type']	= BILL_TYPE_DURATION;

$arrDefine['ItemisedDataCall']	['Charge']			['Start']	= 59;
$arrDefine['ItemisedDataCall']	['Charge']			['Length']	= 11;
$arrDefine['ItemisedDataCall']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;


// Itemised Call Data S&E record
$arrDefine['ItemisedDataS&E']	['RecordType']		['Start']	= 0;
$arrDefine['ItemisedDataS&E']	['RecordType']		['Length']	= 2;
$arrDefine['ItemisedDataS&E']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemisedDataS&E']	['RecordType']		['Value']	= "92";

$arrDefine['ItemisedDataS&E']	['Description']		['Start']	= 2;
$arrDefine['ItemisedDataS&E']	['Description']		['Length']	= 100;
$arrDefine['ItemisedDataS&E']	['Description']		['Type']	= BILL_TYPE_CHAR;

$arrDefine['ItemisedDataS&E']	['Charge']			['Start']	= 102;
$arrDefine['ItemisedDataS&E']	['Charge']			['Length']	= 11;
$arrDefine['ItemisedDataS&E']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;


// Itemised Call Type Footer record
$arrDefine['ItemCallTypeFooter']['RecordType']		['Start']	= 0;
$arrDefine['ItemCallTypeFooter']['RecordType']		['Length']	= 2;
$arrDefine['ItemCallTypeFooter']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemCallTypeFooter']['RecordType']		['Value']	= "99";

$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Start']	= 2;
$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Length']	= 11;
$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;


// Itemised Call Service Footer record
$arrDefine['ItemSvcFooter']		['RecordType']		['Start']	= 0;
$arrDefine['ItemSvcFooter']		['RecordType']		['Length']	= 2;
$arrDefine['ItemSvcFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemSvcFooter']		['RecordType']		['Value']	= "89";

$arrDefine['ItemSvcFooter']		['TotalCharge']		['Start']	= 2;
$arrDefine['ItemSvcFooter']		['TotalCharge']		['Length']	= 11;
$arrDefine['ItemSvcFooter']		['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;


// Itemised Call Footer record
$arrDefine['ItemisedFooter']	['RecordType']		['Start']	= 0;
$arrDefine['ItemisedFooter']	['RecordType']		['Length']	= 2;
$arrDefine['ItemisedFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['ItemisedFooter']	['RecordType']		['Value']	= "79";


// Invoice Footer record
$arrDefine['InvoiceFooter']		['RecordType']		['Start']	= 0;
$arrDefine['InvoiceFooter']		['RecordType']		['Length']	= 2;
$arrDefine['InvoiceFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
$arrDefine['InvoiceFooter']		['RecordType']		['Value']	= "19";

$arrDefine['InvoiceFooter']		['Delivery']		['Start']	= 2;
$arrDefine['InvoiceFooter']		['Delivery']		['Length']	= 1;
$arrDefine['InvoiceFooter']		['Delivery']		['Type']	= BILL_TYPE_INTEGER;


//----------------------------------------------------------------------------//
// Set Test Values
//----------------------------------------------------------------------------//

// Invoice Details record
$arrDefine['InvoiceDetails']	['InvoiceGroup']		['Value']	= BILL_DESIGN_TELCOBLUE;
$arrDefine['InvoiceDetails']	['Inserts']			['Value']	= "011010";
$arrDefine['InvoiceDetails']	['InvoiceNo']		['Value']	= 3000987654;
$arrDefine['InvoiceDetails']	['BillPeriod']		['Value']	= "December 06";
$arrDefine['InvoiceDetails']	['IssueDate']		['Value']	= "6 Dec 2006";
$arrDefine['InvoiceDetails']	['AccountNo']		['Value']	= 1000123456;
$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= "500.12";
$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= "500.12";
$arrDefine['InvoiceDetails']	['Adjustments']		['Value']	= "0.00";
$arrDefine['InvoiceDetails']	['Balance']			['Value']	= "0.00";
$arrDefine['InvoiceDetails']	['BillTotal']		['Value']	= "655.35";
$arrDefine['InvoiceDetails']	['TotalOwing']		['Value']	= "655.35";
$arrDefine['InvoiceDetails']	['CustomerName']	['Value']	= "Shooter McGaven";
$arrDefine['InvoiceDetails']	['PropertyName']	['Value']	= "The Green";
$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= "10 Happy Way";
$arrDefine['InvoiceDetails']	['Suburb']			['Value']	= "GILMORE";
$arrDefine['InvoiceDetails']	['State']			['Value']	= "QLD";
$arrDefine['InvoiceDetails']	['Postcode']		['Value']	= 4531;
$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Value']	= "20 Dec 2006";
$arrFileData[] = $arrDefine['InvoiceDetails'];

// Graph Header record
$arrDefine['GraphHeader']		['GraphType']		['Value']	= GRAPH_TYPE_VERTICALBAR;
$arrDefine['GraphHeader']		['GraphTitle']		['Value']	= "Account History";
$arrDefine['GraphHeader']		['XTitle']			['Value']	= "Month";
$arrDefine['GraphHeader']		['YTitle']			['Value']	= "\$ Value";
$arrFileData[] = $arrDefine['GraphHeader'];
	// Graph Data record
	$arrDefine['GraphData']			['Title']			['Value']	= "Jul 06";
	$arrDefine['GraphData']			['Value']			['Value']	= 22.22;
	$arrFileData[] = $arrDefine['GraphData'];
	$arrDefine['GraphData']			['Title']			['Value']	= "Aug 06";
	$arrDefine['GraphData']			['Value']			['Value']	= 123.45;
	$arrFileData[] = $arrDefine['GraphData'];
	$arrDefine['GraphData']			['Title']			['Value']	= "Sep 06";
	$arrDefine['GraphData']			['Value']			['Value']	= 680.15;
	$arrFileData[] = $arrDefine['GraphData'];
	$arrDefine['GraphData']			['Title']			['Value']	= "Oct 06";
	$arrDefine['GraphData']			['Value']			['Value']	= 2.15;
	$arrFileData[] = $arrDefine['GraphData'];
	$arrDefine['GraphData']			['Title']			['Value']	= "Nov 06";
	$arrDefine['GraphData']			['Value']			['Value']	= 500.12;
	$arrFileData[] = $arrDefine['GraphData'];
	$arrDefine['GraphData']			['Title']			['Value']	= "Dec 06";
	$arrDefine['GraphData']			['Value']			['Value']	= 655.35;
	$arrFileData[] = $arrDefine['GraphData'];
// Graph Footer record
$arrDefine['GraphFooter']		['TotalSamples']	['Value']	= 6;
$arrFileData[] = $arrDefine['GraphFooter'];

// Charge Totals Header record
$arrFileData[] = $arrDefine['ChargeTotalsHeader'];
	// Charge Totals record
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Local Calls";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 25.12;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "National Calls";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 25.25;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Calls to Mobiles";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 12.12;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Calls to 13/1300 Numbers";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 5.12;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "13/1300 Inbound";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 20.12;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Mobile to National";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 25.02;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Mobile to Mobile";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 25.10;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Mobile - Other Charges";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 15.12;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Other Call Types";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 21.12;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Service & Equipment";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 25.11;
	$arrFileData[] = $arrDefine['ChargeTotal'];
	$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "GST Total";
	$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= 65.54;
	$arrFileData[] = $arrDefine['ChargeTotal'];
// Charge Totals Footer record
$arrDefine['ChargeTotalsFooter']['BillTotal']		['Value']	= 655.35;
$arrFileData[] = $arrDefine['ChargeTotalsFooter'];

// Payment Data record
$arrDefine['PaymentData']		['BillExpRef']		['Value']	= 10001234567;
$arrDefine['PaymentData']		['BPayCustomerRef']	['Value']	= 10001234567;
$arrDefine['PaymentData']		['AccountNo']		['Value']	= 1000123456;
$arrDefine['PaymentData']		['DateDue']			['Value']	= "20 Dec 2006";
$arrDefine['PaymentData']		['TotalOwing']		['Value']	= 655.35;
$arrDefine['PaymentData']		['CustomerName']	['Value']	= "Shooter McGaven";
$arrDefine['PaymentData']		['PropertyName']	['Value']	= "The Green";
$arrDefine['PaymentData']		['AddressLine1']	['Value']	= "10 Happy Way";
$arrDefine['PaymentData']		['AddressLine2']	['Value']	= "GILMORE   QLD   4531";
$arrDefine['PaymentData']		['SpecialOffer1']	['Value']	= "FREE One Month Trial for our unlimited " .
																  "Dial Up Internet. Call customer care to " .
																  "get connected.";
$arrDefine['PaymentData']		['SpecialOffer2']	['Value']	= "View your bill online, simply go to " .
																  "www.telcoblue.com.au click on " .
																  "Customer Login, and use your " .
																  "supplied username and password. " .
																  "See calls made in the last few days plus " .
																  "your local calls itemised, or copy all your " .
																  "calls to a spreadsheet for analysis.";
$arrFileData[] = $arrDefine['PaymentData'];

// Service Summary Header Record
$arrFileData[] = $arrDefine['SvcSummaryHeader'];
	// Service Summary Service Header record
	$arrDefine['SvcSummSvcHeader']	['FNN']				['Value']	= "0733531219";
	$arrFileData[] = $arrDefine['SvcSummSvcHeader'];
		// Service Summary Data record
		$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Local Calls";
		$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 5;
		$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 5.00;
		$arrFileData[] = $arrDefine['SvcSummaryData'];
		$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "National Calls";
		$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 13;
		$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 13.00;
		$arrFileData[] = $arrDefine['SvcSummaryData'];
		$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Service & Equipment";
		$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 0;
		$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 10.00;
		$arrFileData[] = $arrDefine['SvcSummaryData'];
	// Service Summary Service Footer record
	$arrDefine['SvcSummSvcFooter']	['TotalCharge']		['Value']	= 23.00;
	$arrFileData[] = $arrDefine['SvcSummSvcFooter'];
	
	// Service Summary Service record
	$arrDefine['SvcSummSvcHeader']	['FNN']				['Value']	= "0408295199";
	$arrFileData[] = $arrDefine['SvcSummSvcHeader'];
		// Service Summary Data record
		$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Mobile to Mobile";
		$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 25;
		$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 50.00;
		$arrFileData[] = $arrDefine['SvcSummaryData'];
		$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Mobile - Other Charges";
		$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 2;
		$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 0.50;
		$arrFileData[] = $arrDefine['SvcSummaryData'];
	// Service Summary Service Footer record
	$arrDefine['SvcSummSvcFooter']	['TotalCharge']		['Value']	= 50.50;
	$arrFileData[] = $arrDefine['SvcSummSvcFooter'];
// Service Summary Footer Record
$arrFileData[] = $arrDefine['SvcSummaryFooter'];


// Itemised Call Header record
$arrFileData[] = $arrDefine['ItemisedHeader'];
	// Itemised Call Service Header record
	$arrDefine['ItemSvcHeader']		['FNN']				['Value']	= "0733531219";
	$arrFileData[] = $arrDefine['ItemSvcHeader'];
		// Itemised Call Type Header record
		$arrDefine['ItemCallTypeHeader']['CallType']		['Value']	= "National Calls";
		$arrFileData[] = $arrDefine['ItemCallTypeHeader'];
			// Itemised Call Data Call record
			$arrDefine['ItemisedDataCall']	['Date']			['Value']	= "20/11/2006";
			$arrDefine['ItemisedDataCall']	['Time']			['Value']	= "10:54:01";
			$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= 0233531929;
			$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= "12:15";
			$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= "0.1531";
			$arrFileData[] = $arrDefine['ItemisedDataCall'];
			// Itemised Call Data Call record
			$arrDefine['ItemisedDataCall']	['Date']			['Value']	= "21/11/2006";
			$arrDefine['ItemisedDataCall']	['Time']			['Value']	= "11:54:01";
			$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= 0333531929;
			$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= "22:15";
			$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= "2.1531";
			$arrFileData[] = $arrDefine['ItemisedDataCall'];
		// Itemised Call Type Footer record
		$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= 13.00;
		$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
		
		// Itemised Call Type Header record
		$arrDefine['ItemCallTypeHeader']	['CallType']		['Value']	= "Service & Equipment";
		$arrFileData[] = $arrDefine['ItemCallTypeHeader'];
			// Itemised Call Data S&E record
			$arrDefine['ItemisedDataS&E']	['Description']		['Value']	= "0733531219: Business Telephone Line 11 Nov 2006 to 10 Dec 2006";
			$arrDefine['ItemisedDataS&E']	['Charge']			['Value']	= 10.0;
			$arrFileData[] = $arrDefine['ItemisedDataS&E'];
		// Itemised Call Type Footer record
		$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= 10.00;
		$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
	// Itemised Call Service Footer record
	$arrDefine['ItemSvcFooter']		['TotalCharge']		['Value']	= 23.00;
	$arrFileData[] = $arrDefine['ItemSvcFooter'];
	
	// Itemised Call Service record
	$arrDefine['ItemSvcHeader']	['FNN']				['Value']	= "0408295199";
	$arrFileData[] = $arrDefine['ItemSvcHeader'];
		// Itemised Call Type record
		$arrDefine['ItemCallTypeHeader']['CallType']		['Value']	= "Mobile to Mobile";
		$arrFileData[] = $arrDefine['ItemCallTypeHeader'];
			// Itemised Call Data Call record
			$arrDefine['ItemisedDataCall']	['Date']			['Value']	= "11/11/2006";
			$arrDefine['ItemisedDataCall']	['Time']			['Value']	= "10:24:01";
			$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= 0415151515;
			$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= "12:15";
			$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= "13.00";
			$arrFileData[] = $arrDefine['ItemisedDataCall'];
		// Itemised Call Type Footer record
		$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= 13.00;
		$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
	// Itemised Call Service Footer record
	$arrDefine['ItemSvcFooter']		['TotalCharge']		['Value']	= 13.00;
	$arrFileData[] = $arrDefine['ItemSvcFooter'];
// Itemised Call Footer record
$arrFileData[] = $arrDefine['ItemisedFooter'];

$arrFileData[] = $arrDefine['InvoiceFooter'];


//----------------------------------------------------------------------------//
// Process and implode the data to a giant ass string
//----------------------------------------------------------------------------//

$strFileContents = "";

// Loop through Records
foreach ($arrFileData as $arrRecord)
{
	$i++;
	$t = 0;
	
	// Loop through Fields
	foreach ($arrRecord as $arrField)
	{
		$strValue = $arrField['Value'];
		
		$t++;
		
		if(!isset($strValue))
		{
			echo "Continuing on $i--$t...<BR />";
			continue;
		}
		
		if (strlen($strValue) > $arrField['Length'])
		{
			echo "Field $i--$t is too long! (".strlen($strValue)." > ".$arrField['Length'].") -> $strValue<BR />";
		}
		
		switch ($arrField['Type'])
		{
			case BILL_TYPE_INTEGER:
				$strValue = str_pad($strValue, $arrField['Length'], " ", STR_PAD_LEFT);
				break;
			case BILL_TYPE_CHAR:
				$strValue = str_pad($strValue, $arrField['Length'], " ", STR_PAD_RIGHT);
				break;
			case BILL_TYPE_BINARY:
				$strValue = str_pad($strValue, 11, "0", STR_PAD_RIGHT);
				break;
			case BILL_TYPE_FLOAT:
				$strValue = str_pad((float)$strValue, 11, " ", STR_PAD_LEFT);
				break;
			case BILL_TYPE_SHORTDATE:
				$strValue = str_pad($strValue, 10, " ", STR_PAD_LEFT);
				break;
			case BILL_TYPE_LONGDATE:
				$strValue = str_pad($strValue, 11, " ", STR_PAD_RIGHT);
				break;
			case BILL_TYPE_TIME:
				$strValue = str_pad($strValue, 8, " ", STR_PAD_LEFT);
				break;
			case BILL_TYPE_DURATION:
				$strValue = str_pad($strValue, 7, " ", STR_PAD_LEFT);
				break;
			case BILL_TYPE_SHORTCURRENCY:
				$strTemp = sprintf("%1.2f", ((float)$strValue));
				$strValue = str_pad($strTemp, 11, " ", STR_PAD_LEFT);
				break;
			default:
				// Unknown Data Type
				echo "<BR /><BR />DEATH on $i--$t<BR /><BR />";
				print_r($arrField);
				Die(); 
		}
		
		$strFileContents .= $strValue;
	}
	
	$strFileContents .= "\n";
}


//----------------------------------------------------------------------------//
// Output to screen
//----------------------------------------------------------------------------//

echo "<pre>";
print_r($strFileContents);
echo "\n\n\n".sha1_file("/home/richdavis/donkey1.gif");
echo "</pre>";

?>
