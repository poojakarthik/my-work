<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DEFINITIONS
//----------------------------------------------------------------------------//
/**
 * config
 *
 * ApplicationConfig Definitions
 *
 * This file exclusively declares application config
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

// BILL OUTPUT FILE DEFINITION

		// The RecordType and InvoiceNo fields will be added by an SQL query later on
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Value']	= "10";
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Print']	= FALSE;
		
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Start']	= 2;
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Length']	= 10;
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Print']	= FALSE;
		
		$arrPrintDefine['InvoiceDetails']	['BillType']		['Start']	= 12;
		$arrPrintDefine['InvoiceDetails']	['BillType']		['Length']	= 2;
		$arrPrintDefine['InvoiceDetails']	['BillType']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['InvoiceDetails']	['Inserts']			['Start']	= 14;
		$arrPrintDefine['InvoiceDetails']	['Inserts']			['Length']	= 6;
		$arrPrintDefine['InvoiceDetails']	['Inserts']			['Type']	= BILL_TYPE_BINARY;
		
		$arrPrintDefine['InvoiceDetails']	['BillPeriod']		['Start']	= 20;
		$arrPrintDefine['InvoiceDetails']	['BillPeriod']		['Length']	= 12;
		$arrPrintDefine['InvoiceDetails']	['BillPeriod']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['IssueDate']		['Start']	= 32;
		$arrPrintDefine['InvoiceDetails']	['IssueDate']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['IssueDate']		['Type']	= BILL_TYPE_LONGDATE;
		
		$arrPrintDefine['InvoiceDetails']	['AccountNo']		['Start']	= 43;
		$arrPrintDefine['InvoiceDetails']	['AccountNo']		['Length']	= 20;
		$arrPrintDefine['InvoiceDetails']	['AccountNo']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['InvoiceDetails']	['OpeningBalance']	['Start']	= 63;
		$arrPrintDefine['InvoiceDetails']	['OpeningBalance']	['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['OpeningBalance']	['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['WeReceived']		['Start']	= 74;
		$arrPrintDefine['InvoiceDetails']	['WeReceived']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['WeReceived']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['Adjustments']		['Start']	= 85;
		$arrPrintDefine['InvoiceDetails']	['Adjustments']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['Adjustments']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['Balance']			['Start']	= 96;
		$arrPrintDefine['InvoiceDetails']	['Balance']			['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['Balance']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['BillTotal']		['Start']	= 107;
		$arrPrintDefine['InvoiceDetails']	['BillTotal']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['TotalOwing']		['Start']	= 118;
		$arrPrintDefine['InvoiceDetails']	['TotalOwing']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['CustomerName']	['Start']	= 129;
		$arrPrintDefine['InvoiceDetails']	['CustomerName']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['CustomerName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['PropertyName']	['Start']	= 189;
		$arrPrintDefine['InvoiceDetails']	['PropertyName']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['PropertyName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['AddressLine1']	['Start']	= 249;
		$arrPrintDefine['InvoiceDetails']	['AddressLine1']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['AddressLine1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['Suburb']			['Start']	= 309;
		$arrPrintDefine['InvoiceDetails']	['Suburb']			['Length']	= 30;
		$arrPrintDefine['InvoiceDetails']	['Suburb']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['State']			['Start']	= 339;
		$arrPrintDefine['InvoiceDetails']	['State']			['Length']	= 3;
		$arrPrintDefine['InvoiceDetails']	['State']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['Postcode']		['Start']	= 342;
		$arrPrintDefine['InvoiceDetails']	['Postcode']		['Length']	= 4;
		$arrPrintDefine['InvoiceDetails']	['Postcode']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['InvoiceDetails']	['PaymentDueDate']	['Start']	= 346;
		$arrPrintDefine['InvoiceDetails']	['PaymentDueDate']	['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['PaymentDueDate']	['Type']	= BILL_TYPE_LONGDATE;
		
		$arrPrintDefine['GraphHeader']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphHeader']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphHeader']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphHeader']		['RecordType']		['Value']	= "20";
		
		$arrPrintDefine['GraphHeader']		['GraphType']		['Start']	= 2;
		$arrPrintDefine['GraphHeader']		['GraphType']		['Length']	= 2;
		$arrPrintDefine['GraphHeader']		['GraphType']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['GraphHeader']		['GraphTitle']		['Start']	= 4;
		$arrPrintDefine['GraphHeader']		['GraphTitle']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['GraphTitle']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['XTitle']			['Start']	= 24;
		$arrPrintDefine['GraphHeader']		['XTitle']			['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['XTitle']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['YTitle']			['Start']	= 34;
		$arrPrintDefine['GraphHeader']		['YTitle']			['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['YTitle']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['ZTitle']			['Start']	= 44;
		$arrPrintDefine['GraphHeader']		['ZTitle']			['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['ZTitle']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['Description']		['Start']	= 54;
		$arrPrintDefine['GraphHeader']		['Description']		['Length']	= 100;
		$arrPrintDefine['GraphHeader']		['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphData']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphData']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphData']		['RecordType']		['Value']	= "21";
		
		$arrPrintDefine['GraphData']		['Title']			['Start']	= 2;
		$arrPrintDefine['GraphData']		['Title']			['Length']	= 20;
		$arrPrintDefine['GraphData']		['Title']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphData']		['Value']			['Start']	= 22;
		$arrPrintDefine['GraphData']		['Value']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphFooter']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphFooter']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphFooter']		['RecordType']		['Value']	= "29";
		
		$arrPrintDefine['GraphFooter']		['TotalSamples']	['Start']	= 2;
		$arrPrintDefine['GraphFooter']		['TotalSamples']	['Length']	= 6;
		$arrPrintDefine['GraphFooter']		['TotalSamples']	['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['GraphColumn']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphColumn']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphColumn']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphColumn']		['RecordType']		['Value']	= "30";
		
		$arrPrintDefine['GraphColumn']		['Title']			['Start']	= 2;
		$arrPrintDefine['GraphColumn']		['Title']			['Length']	= 20;
		$arrPrintDefine['GraphColumn']		['Title']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphColumnFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphColumnFooter']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphColumnFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphColumnFooter']	['RecordType']		['Value']	= "31";
		
		$arrPrintDefine['GraphRowHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphRowHeader']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphRowHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphRowHeader']	['RecordType']		['Value']	= "32";
		
		$arrPrintDefine['GraphRowHeader']	['Title']			['Start']	= 2;
		$arrPrintDefine['GraphRowHeader']	['Title']			['Length']	= 20;
		$arrPrintDefine['GraphRowHeader']	['Title']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphRowFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphRowFooter']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphRowFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphRowFooter']	['RecordType']		['Value']	= "33";
		
		$arrPrintDefine['GraphLineHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphLineHeader']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphLineHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphLineHeader']	['RecordType']		['Value']	= "34";
		
		$arrPrintDefine['GraphLineHeader']	['Title']			['Start']	= 2;
		$arrPrintDefine['GraphLineHeader']	['Title']			['Length']	= 20;
		$arrPrintDefine['GraphLineHeader']	['Title']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphLineFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphLineFooter']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['GraphLineFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphLineFooter']	['RecordType']		['Value']	= "35";
		
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Length']	= 2;
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Value']	= "40";
		
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Value']	= "41";
		
		$arrPrintDefine['ChargeTotal']		['ChargeName']		['Start']	= 2;
		$arrPrintDefine['ChargeTotal']		['ChargeName']		['Length']	= 40;
		$arrPrintDefine['ChargeTotal']		['ChargeName']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ChargeTotal']		['ChargeTotal']		['Start']	= 42;
		$arrPrintDefine['ChargeTotal']		['ChargeTotal']		['Length']	= 11;
		$arrPrintDefine['ChargeTotal']		['ChargeTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Length']	= 2;
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Value']	= "49";
		
		$arrPrintDefine['ChargeTotalsFooter']['BillTotal']		['Start']	= 2;
		$arrPrintDefine['ChargeTotalsFooter']['BillTotal']		['Length']	= 11;
		$arrPrintDefine['ChargeTotalsFooter']['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['PaymentData']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['PaymentData']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['PaymentData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['PaymentData']		['RecordType']		['Value']	= "11";
		
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Start']	= 2;
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Length']	= 6;
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Value']	= 000376;
		
		$arrPrintDefine['PaymentData']		['BillExpRef']		['Start']	= 8;
		$arrPrintDefine['PaymentData']		['BillExpRef']		['Length']	= 25;
		$arrPrintDefine['PaymentData']		['BillExpRef']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Start']	= 33;
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Length']	= 5;
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Value']	= 63412;
		
		$arrPrintDefine['PaymentData']		['BPayCustomerRef']	['Start']	= 38;
		$arrPrintDefine['PaymentData']		['BPayCustomerRef']	['Length']	= 25;
		$arrPrintDefine['PaymentData']		['BPayCustomerRef']	['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['PaymentData']		['AccountNo']		['Start']	= 63;
		$arrPrintDefine['PaymentData']		['AccountNo']		['Length']	= 20;
		$arrPrintDefine['PaymentData']		['AccountNo']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['PaymentData']		['DateDue']			['Start']	= 83;
		$arrPrintDefine['PaymentData']		['DateDue']			['Length']	= 13;
		$arrPrintDefine['PaymentData']		['DateDue']			['Type']	= BILL_TYPE_LONGDATE;
		
		$arrPrintDefine['PaymentData']		['TotalOwing']		['Start']	= 96;
		$arrPrintDefine['PaymentData']		['TotalOwing']		['Length']	= 11;
		$arrPrintDefine['PaymentData']		['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['PaymentData']		['CustomerName']	['Start']	= 107;
		$arrPrintDefine['PaymentData']		['CustomerName']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['CustomerName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['PropertyName']	['Start']	= 167;
		$arrPrintDefine['PaymentData']		['PropertyName']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['PropertyName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine1']	['Start']	= 227;
		$arrPrintDefine['PaymentData']		['AddressLine1']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine2']	['Start']	= 287;
		$arrPrintDefine['PaymentData']		['AddressLine2']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine2']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['SpecialOffer1']	['Start']	= 347;
		$arrPrintDefine['PaymentData']		['SpecialOffer1']	['Length']	= 250;
		$arrPrintDefine['PaymentData']		['SpecialOffer1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['SpecialOffer2']	['Start']	= 597;
		$arrPrintDefine['PaymentData']		['SpecialOffer2']	['Length']	= 250;
		$arrPrintDefine['PaymentData']		['SpecialOffer2']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Value']	= "50";
		
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Value']	= "59";
		
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Value']	= "60";
		
		$arrPrintDefine['SvcSummSvcHeader']	['FNN']				['Start']	= 2;
		$arrPrintDefine['SvcSummSvcHeader']	['FNN']				['Length']	= 10;
		$arrPrintDefine['SvcSummSvcHeader']	['FNN']				['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Value']	= "61";
		
		$arrPrintDefine['SvcSummaryData']	['CallType']		['Start']	= 2;
		$arrPrintDefine['SvcSummaryData']	['CallType']		['Length']	= 40;
		$arrPrintDefine['SvcSummaryData']	['CallType']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['SvcSummaryData']	['CallCount']		['Start']	= 42;
		$arrPrintDefine['SvcSummaryData']	['CallCount']		['Length']	= 10;
		$arrPrintDefine['SvcSummaryData']	['CallCount']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['SvcSummaryData']	['Charge']			['Start']	= 52;
		$arrPrintDefine['SvcSummaryData']	['Charge']			['Length']	= 11;
		$arrPrintDefine['SvcSummaryData']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Value']	= "69";
		
		$arrPrintDefine['SvcSummSvcFooter']	['TotalCharge']		['Start']	= 2;
		$arrPrintDefine['SvcSummSvcFooter']	['TotalCharge']		['Length']	= 11;
		$arrPrintDefine['SvcSummSvcFooter']	['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Value']	= "70";
		
		$arrPrintDefine['ItemSvcHeader']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemSvcHeader']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemSvcHeader']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemSvcHeader']		['RecordType']		['Value']	= "80";
		
		$arrPrintDefine['ItemSvcHeader']		['FNN']				['Start']	= 2;
		$arrPrintDefine['ItemSvcHeader']		['FNN']				['Length']	= 10;
		$arrPrintDefine['ItemSvcHeader']		['FNN']				['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Value']	= "90";
		
		$arrPrintDefine['ItemCallTypeHeader']['CallType']		['Start']	= 2;
		$arrPrintDefine['ItemCallTypeHeader']['CallType']		['Length']	= 40;
		$arrPrintDefine['ItemCallTypeHeader']['CallType']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Value']	= "91";
		
		$arrPrintDefine['ItemisedDataCall']	['Date']			['Start']	= 2;
		$arrPrintDefine['ItemisedDataCall']	['Date']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataCall']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['ItemisedDataCall']	['Time']			['Start']	= 12;
		$arrPrintDefine['ItemisedDataCall']	['Time']			['Length']	= 8;
		$arrPrintDefine['ItemisedDataCall']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrPrintDefine['ItemisedDataCall']	['CalledParty']		['Start']	= 20;
		$arrPrintDefine['ItemisedDataCall']	['CalledParty']		['Length']	= 30;
		$arrPrintDefine['ItemisedDataCall']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

		$arrPrintDefine['ItemisedDataCall']	['Description']		['Start']	= 50;
		$arrPrintDefine['ItemisedDataCall']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataCall']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataCall']	['Duration']		['Start']	= 150;
		$arrPrintDefine['ItemisedDataCall']	['Duration']		['Length']	= 9;
		$arrPrintDefine['ItemisedDataCall']	['Duration']		['Type']	= BILL_TYPE_DURATION;
				
		$arrPrintDefine['ItemisedDataCall']	['Charge']			['Start']	= 159;
		$arrPrintDefine['ItemisedDataCall']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataCall']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Value']	= "92";
		
		$arrPrintDefine['ItemisedDataS&E']	['Description']		['Start']	= 2;
		$arrPrintDefine['ItemisedDataS&E']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataS&E']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataS&E']	['Items']			['Start']	= 102;
		$arrPrintDefine['ItemisedDataS&E']	['Items']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataS&E']	['Items']			['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['ItemisedDataS&E']	['Charge']			['Start']	= 112;
		$arrPrintDefine['ItemisedDataS&E']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataS&E']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Value']	= "93";
		
		$arrPrintDefine['ItemisedDataKB']	['Date']			['Start']	= 2;
		$arrPrintDefine['ItemisedDataKB']	['Date']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataKB']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['ItemisedDataKB']	['Time']			['Start']	= 12;
		$arrPrintDefine['ItemisedDataKB']	['Time']			['Length']	= 8;
		$arrPrintDefine['ItemisedDataKB']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrPrintDefine['ItemisedDataKB']	['CalledParty']		['Start']	= 20;
		$arrPrintDefine['ItemisedDataKB']	['CalledParty']		['Length']	= 30;
		$arrPrintDefine['ItemisedDataKB']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

		$arrPrintDefine['ItemisedDataKB']	['Description']		['Start']	= 50;
		$arrPrintDefine['ItemisedDataKB']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataKB']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataKB']	['DataTransfered']	['Start']	= 150;
		$arrPrintDefine['ItemisedDataKB']	['DataTransfered']	['Length']	= 10;
		$arrPrintDefine['ItemisedDataKB']	['DataTransfered']	['Type']	= BILL_TYPE_INTEGER;
				
		$arrPrintDefine['ItemisedDataKB']	['Charge']			['Start']	= 160;
		$arrPrintDefine['ItemisedDataKB']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataKB']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Value']	= "94";
		
		$arrPrintDefine['ItemisedDataSMS']	['Date']			['Start']	= 2;
		$arrPrintDefine['ItemisedDataSMS']	['Date']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataSMS']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['ItemisedDataSMS']	['Time']			['Start']	= 12;
		$arrPrintDefine['ItemisedDataSMS']	['Time']			['Length']	= 8;
		$arrPrintDefine['ItemisedDataSMS']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrPrintDefine['ItemisedDataSMS']	['CalledParty']		['Start']	= 20;
		$arrPrintDefine['ItemisedDataSMS']	['CalledParty']		['Length']	= 30;
		$arrPrintDefine['ItemisedDataSMS']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

		$arrPrintDefine['ItemisedDataSMS']	['Description']		['Start']	= 50;
		$arrPrintDefine['ItemisedDataSMS']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataSMS']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataSMS']	['Items']			['Start']	= 150;
		$arrPrintDefine['ItemisedDataSMS']	['Items']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataSMS']	['Items']			['Type']	= BILL_TYPE_INTEGER;
				
		$arrPrintDefine['ItemisedDataSMS']	['Charge']			['Start']	= 160;
		$arrPrintDefine['ItemisedDataSMS']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataSMS']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Value']	= "99";
		
		$arrPrintDefine['ItemCallTypeFooter']['TotalCharge']		['Start']	= 2;
		$arrPrintDefine['ItemCallTypeFooter']['TotalCharge']		['Length']	= 11;
		$arrPrintDefine['ItemCallTypeFooter']['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Value']	= "89";
		
/*		$arrPrintDefine['ItemSvcFooter']		['TotalCharge']		['Start']	= 2;
		$arrPrintDefine['ItemSvcFooter']		['TotalCharge']		['Length']	= 11;
		$arrPrintDefine['ItemSvcFooter']		['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;*/
		
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Length']	= 2;
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Value']	= "79";
		
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Length']	= 2;
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Value']	= "19";
		
		$arrPrintDefine['InvoiceFooter']		['Delivery']		['Start']	= 2;
		$arrPrintDefine['InvoiceFooter']		['Delivery']		['Length']	= 1;
		$arrPrintDefine['InvoiceFooter']		['Delivery']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrConfig['BillPrintDefine']									= $arrPrintDefine;
		

?>
