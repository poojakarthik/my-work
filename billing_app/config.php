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
 * @version		7.01
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
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Value']	= "10";
		$arrPrintDefine['InvoiceDetails']	['RecordType']		['Print']	= FALSE;
		
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Start']	= 4;
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Length']	= 10;
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['InvoiceDetails']	['InvoiceNo']		['Print']	= FALSE;
		
		$arrPrintDefine['InvoiceDetails']	['InvoiceGroup']	['Start']	= 14;
		$arrPrintDefine['InvoiceDetails']	['InvoiceGroup']	['Length']	= 2;
		$arrPrintDefine['InvoiceDetails']	['InvoiceGroup']	['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['InvoiceDetails']	['Inserts']			['Start']	= 16;
		$arrPrintDefine['InvoiceDetails']	['Inserts']			['Length']	= 6;
		$arrPrintDefine['InvoiceDetails']	['Inserts']			['Type']	= BILL_TYPE_BINARY;
		
		$arrPrintDefine['InvoiceDetails']	['BillPeriod']		['Start']	= 22;
		$arrPrintDefine['InvoiceDetails']	['BillPeriod']		['Length']	= 12;
		$arrPrintDefine['InvoiceDetails']	['BillPeriod']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['IssueDate']		['Start']	= 34;
		$arrPrintDefine['InvoiceDetails']	['IssueDate']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['IssueDate']		['Type']	= BILL_TYPE_LONGDATE;
		
		$arrPrintDefine['InvoiceDetails']	['AccountNo']		['Start']	= 45;
		$arrPrintDefine['InvoiceDetails']	['AccountNo']		['Length']	= 20;
		$arrPrintDefine['InvoiceDetails']	['AccountNo']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['InvoiceDetails']	['OpeningBalance']	['Start']	= 65;
		$arrPrintDefine['InvoiceDetails']	['OpeningBalance']	['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['OpeningBalance']	['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['WeReceived']		['Start']	= 76;
		$arrPrintDefine['InvoiceDetails']	['WeReceived']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['WeReceived']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['Adjustments']		['Start']	= 87;
		$arrPrintDefine['InvoiceDetails']	['Adjustments']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['Adjustments']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['Balance']			['Start']	= 98;
		$arrPrintDefine['InvoiceDetails']	['Balance']			['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['Balance']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['BillTotal']		['Start']	= 109;
		$arrPrintDefine['InvoiceDetails']	['BillTotal']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['TotalOwing']		['Start']	= 120;
		$arrPrintDefine['InvoiceDetails']	['TotalOwing']		['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['InvoiceDetails']	['CustomerName']	['Start']	= 131;
		$arrPrintDefine['InvoiceDetails']	['CustomerName']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['CustomerName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['AddressLine1']	['Start']	= 191;
		$arrPrintDefine['InvoiceDetails']	['AddressLine1']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['AddressLine1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['AddressLine2']	['Start']	= 251;
		$arrPrintDefine['InvoiceDetails']	['AddressLine2']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['AddressLine2']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['AddressLine3']	['Start']	= 311;
		$arrPrintDefine['InvoiceDetails']	['AddressLine3']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['AddressLine3']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['AddressLine4']	['Start']	= 371;
		$arrPrintDefine['InvoiceDetails']	['AddressLine4']	['Length']	= 60;
		$arrPrintDefine['InvoiceDetails']	['AddressLine4']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['Suburb']			['Start']	= 431;
		$arrPrintDefine['InvoiceDetails']	['Suburb']			['Length']	= 30;
		$arrPrintDefine['InvoiceDetails']	['Suburb']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['State']			['Start']	= 461;
		$arrPrintDefine['InvoiceDetails']	['State']			['Length']	= 3;
		$arrPrintDefine['InvoiceDetails']	['State']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['InvoiceDetails']	['Postcode']		['Start']	= 464;
		$arrPrintDefine['InvoiceDetails']	['Postcode']		['Length']	= 4;
		$arrPrintDefine['InvoiceDetails']	['Postcode']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['InvoiceDetails']	['PaymentDueDate']	['Start']	= 468;
		$arrPrintDefine['InvoiceDetails']	['PaymentDueDate']	['Length']	= 11;
		$arrPrintDefine['InvoiceDetails']	['PaymentDueDate']	['Type']	= BILL_TYPE_LONGDATE;
		
		
		
		
		
		$arrPrintDefine['GraphHeader']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphHeader']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['GraphHeader']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphHeader']		['RecordType']		['Value']	= "20";
		
		$arrPrintDefine['GraphHeader']		['GraphType']		['Start']	= 4;
		$arrPrintDefine['GraphHeader']		['GraphType']		['Length']	= 2;
		$arrPrintDefine['GraphHeader']		['GraphType']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['GraphHeader']		['GraphTitle']		['Start']	= 6;
		$arrPrintDefine['GraphHeader']		['GraphTitle']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['GraphTitle']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['XTitle']			['Start']	= 26;
		$arrPrintDefine['GraphHeader']		['XTitle']			['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['XTitle']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['YTitle']			['Start']	= 46;
		$arrPrintDefine['GraphHeader']		['YTitle']			['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['YTitle']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['ZTitle']			['Start']	= 66;
		$arrPrintDefine['GraphHeader']		['ZTitle']			['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['ZTitle']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['ValueCount']		['Start']	= 86;
		$arrPrintDefine['GraphHeader']		['ValueCount']		['Length']	= 2;
		$arrPrintDefine['GraphHeader']		['ValueCount']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['GraphHeader']		['LegendText1']		['Start']	= 88;
		$arrPrintDefine['GraphHeader']		['LegendText1']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText1']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText2']		['Start']	= 108;
		$arrPrintDefine['GraphHeader']		['LegendText2']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText2']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText3']		['Start']	= 128;
		$arrPrintDefine['GraphHeader']		['LegendText3']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText3']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText4']		['Start']	= 148;
		$arrPrintDefine['GraphHeader']		['LegendText4']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText4']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText5']		['Start']	= 168;
		$arrPrintDefine['GraphHeader']		['LegendText5']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText5']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText6']		['Start']	= 188;
		$arrPrintDefine['GraphHeader']		['LegendText6']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText6']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText7']		['Start']	= 208;
		$arrPrintDefine['GraphHeader']		['LegendText7']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText7']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText8']		['Start']	= 228;
		$arrPrintDefine['GraphHeader']		['LegendText8']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText8']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText9']		['Start']	= 248;
		$arrPrintDefine['GraphHeader']		['LegendText9']		['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText9']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['LegendText10']	['Start']	= 268;
		$arrPrintDefine['GraphHeader']		['LegendText10']	['Length']	= 20;
		$arrPrintDefine['GraphHeader']		['LegendText10']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphHeader']		['Description']		['Start']	= 288;
		$arrPrintDefine['GraphHeader']		['Description']		['Length']	= 100;
		$arrPrintDefine['GraphHeader']		['Description']		['Type']	= BILL_TYPE_CHAR;
		



		$arrPrintDefine['GraphData']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphData']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['GraphData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphData']		['RecordType']		['Value']	= "21";
		
		$arrPrintDefine['GraphData']		['Title']			['Start']	= 4;
		$arrPrintDefine['GraphData']		['Title']			['Length']	= 20;
		$arrPrintDefine['GraphData']		['Title']			['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['GraphData']		['Value1']			['Start']	= 24;
		$arrPrintDefine['GraphData']		['Value1']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value1']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value2']			['Start']	= 34;
		$arrPrintDefine['GraphData']		['Value2']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value2']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value3']			['Start']	= 44;
		$arrPrintDefine['GraphData']		['Value3']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value3']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value4']			['Start']	= 54;
		$arrPrintDefine['GraphData']		['Value4']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value4']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value5']			['Start']	= 64;
		$arrPrintDefine['GraphData']		['Value5']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value5']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value6']			['Start']	= 74;
		$arrPrintDefine['GraphData']		['Value6']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value6']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value7']			['Start']	= 84;
		$arrPrintDefine['GraphData']		['Value7']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value7']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value8']			['Start']	= 94;
		$arrPrintDefine['GraphData']		['Value8']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value8']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value9']			['Start']	= 104;
		$arrPrintDefine['GraphData']		['Value9']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value9']			['Type']	= BILL_TYPE_FLOAT;
		
		$arrPrintDefine['GraphData']		['Value10']			['Start']	= 114;
		$arrPrintDefine['GraphData']		['Value10']			['Length']	= 10;
		$arrPrintDefine['GraphData']		['Value10']			['Type']	= BILL_TYPE_FLOAT;
		



		$arrPrintDefine['GraphFooter']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['GraphFooter']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['GraphFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['GraphFooter']		['RecordType']		['Value']	= "29";
		
		
		

		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Length']	= 4;
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ChargeTotalsHeader']['RecordType']		['Value']	= "40";
		
		
		

		$arrPrintDefine['ChargeTotal']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ChargeTotal']		['RecordType']		['Value']	= "41";
		
		$arrPrintDefine['ChargeTotal']		['ChargeName']		['Start']	= 4;
		$arrPrintDefine['ChargeTotal']		['ChargeName']		['Length']	= 40;
		$arrPrintDefine['ChargeTotal']		['ChargeName']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ChargeTotal']		['ChargeTotal']		['Start']	= 44;
		$arrPrintDefine['ChargeTotal']		['ChargeTotal']		['Length']	= 11;
		$arrPrintDefine['ChargeTotal']		['ChargeTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Length']	= 4;
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ChargeTotalsFooter']['RecordType']		['Value']	= "49";
		
		$arrPrintDefine['ChargeTotalsFooter']['BillTotal']		['Start']	= 4;
		$arrPrintDefine['ChargeTotalsFooter']['BillTotal']		['Length']	= 11;
		$arrPrintDefine['ChargeTotalsFooter']['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		
		$arrPrintDefine['PaymentData']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['PaymentData']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['PaymentData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['PaymentData']		['RecordType']		['Value']	= "11";
		
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Start']	= 4;
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Length']	= 6;
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['PaymentData']		['BillExpBillerId']	['Value']	= 000376;
		
		$arrPrintDefine['PaymentData']		['BillExpRef']		['Start']	= 12;
		$arrPrintDefine['PaymentData']		['BillExpRef']		['Length']	= 25;
		$arrPrintDefine['PaymentData']		['BillExpRef']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Start']	= 35;
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Length']	= 5;
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['PaymentData']		['BPayBillerId']	['Value']	= 63412;
		
		$arrPrintDefine['PaymentData']		['BPayCustomerRef']	['Start']	= 40;
		$arrPrintDefine['PaymentData']		['BPayCustomerRef']	['Length']	= 25;
		$arrPrintDefine['PaymentData']		['BPayCustomerRef']	['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['PaymentData']		['AccountNo']		['Start']	= 65;
		$arrPrintDefine['PaymentData']		['AccountNo']		['Length']	= 20;
		$arrPrintDefine['PaymentData']		['AccountNo']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['PaymentData']		['DateDue']			['Start']	= 85;
		$arrPrintDefine['PaymentData']		['DateDue']			['Length']	= 11;
		$arrPrintDefine['PaymentData']		['DateDue']			['Type']	= BILL_TYPE_LONGDATE;
		
		$arrPrintDefine['PaymentData']		['TotalOwing']		['Start']	= 96;
		$arrPrintDefine['PaymentData']		['TotalOwing']		['Length']	= 11;
		$arrPrintDefine['PaymentData']		['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrPrintDefine['PaymentData']		['CustomerName']	['Start']	= 107;
		$arrPrintDefine['PaymentData']		['CustomerName']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['CustomerName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine1']	['Start']	= 167;
		$arrPrintDefine['PaymentData']		['AddressLine1']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine2']	['Start']	= 227;
		$arrPrintDefine['PaymentData']		['AddressLine2']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine2']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine3']	['Start']	= 287;
		$arrPrintDefine['PaymentData']		['AddressLine3']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine3']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine4']	['Start']	= 347;
		$arrPrintDefine['PaymentData']		['AddressLine4']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine4']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['AddressLine5']	['Start']	= 407;
		$arrPrintDefine['PaymentData']		['AddressLine5']	['Length']	= 60;
		$arrPrintDefine['PaymentData']		['AddressLine5']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['PaymentMethod']	['Start']	= 467;
		$arrPrintDefine['PaymentData']		['PaymentMethod']	['Length']	= 2;
		$arrPrintDefine['PaymentData']		['PaymentMethod']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['SpecialOffer1']	['Start']	= 469;
		$arrPrintDefine['PaymentData']		['SpecialOffer1']	['Length']	= 250;
		$arrPrintDefine['PaymentData']		['SpecialOffer1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['PaymentData']		['SpecialOffer2']	['Start']	= 719;
		$arrPrintDefine['PaymentData']		['SpecialOffer2']	['Length']	= 250;
		$arrPrintDefine['PaymentData']		['SpecialOffer2']	['Type']	= BILL_TYPE_CHAR;
		
		
		
		
		
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummaryHeader']	['RecordType']		['Value']	= "50";
		
		
		
		
		
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummaryFooter']	['RecordType']		['Value']	= "59";
		
		
		
		
		
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummSvcHeader']	['RecordType']		['Value']	= "60";
		
		$arrPrintDefine['SvcSummSvcHeader']	['FNN']				['Start']	= 4;
		$arrPrintDefine['SvcSummSvcHeader']	['FNN']				['Length']	= 10;
		$arrPrintDefine['SvcSummSvcHeader']	['FNN']				['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['SvcSummSvcHeader']	['CostCentre']		['Start']	= 14;
		$arrPrintDefine['SvcSummSvcHeader']	['CostCentre']		['Length']	= 40;
		$arrPrintDefine['SvcSummSvcHeader']	['CostCentre']		['Type']	= BILL_TYPE_CHAR;
		
		
		
		
		
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummaryData']	['RecordType']		['Value']	= "61";
		
		$arrPrintDefine['SvcSummaryData']	['CallType']		['Start']	= 4;
		$arrPrintDefine['SvcSummaryData']	['CallType']		['Length']	= 40;
		$arrPrintDefine['SvcSummaryData']	['CallType']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['SvcSummaryData']	['CallCount']		['Start']	= 44;
		$arrPrintDefine['SvcSummaryData']	['CallCount']		['Length']	= 10;
		$arrPrintDefine['SvcSummaryData']	['CallCount']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['SvcSummaryData']	['Charge']			['Start']	= 54;
		$arrPrintDefine['SvcSummaryData']	['Charge']			['Length']	= 11;
		$arrPrintDefine['SvcSummaryData']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['SvcSummSvcFooter']	['RecordType']		['Value']	= "69";
		
		$arrPrintDefine['SvcSummSvcFooter']	['TotalCharge']		['Start']	= 4;
		$arrPrintDefine['SvcSummSvcFooter']	['TotalCharge']		['Length']	= 11;
		$arrPrintDefine['SvcSummSvcFooter']	['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedHeader']	['RecordType']		['Value']	= "70";
		
		
		
		
		$arrPrintDefine['ItemSvcHeader']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemSvcHeader']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemSvcHeader']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemSvcHeader']	['RecordType']		['Value']	= "80";
		
		$arrPrintDefine['ItemSvcHeader']	['FNN']				['Start']	= 4;
		$arrPrintDefine['ItemSvcHeader']	['FNN']				['Length']	= 10;
		$arrPrintDefine['ItemSvcHeader']	['FNN']				['Type']	= BILL_TYPE_INTEGER;
		
		
		
		
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemCallTypeHeader']['RecordType']		['Value']	= "90";
		
		$arrPrintDefine['ItemCallTypeHeader']['CallType']		['Start']	= 4;
		$arrPrintDefine['ItemCallTypeHeader']['CallType']		['Length']	= 40;
		$arrPrintDefine['ItemCallTypeHeader']['CallType']		['Type']	= BILL_TYPE_CHAR;
		
		
		
		
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataCall']	['RecordType']		['Value']	= "91";
		
		$arrPrintDefine['ItemisedDataCall']	['Date']			['Start']	= 4;
		$arrPrintDefine['ItemisedDataCall']	['Date']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataCall']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['ItemisedDataCall']	['Time']			['Start']	= 14;
		$arrPrintDefine['ItemisedDataCall']	['Time']			['Length']	= 8;
		$arrPrintDefine['ItemisedDataCall']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrPrintDefine['ItemisedDataCall']	['CalledParty']		['Start']	= 22;
		$arrPrintDefine['ItemisedDataCall']	['CalledParty']		['Length']	= 30;
		$arrPrintDefine['ItemisedDataCall']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

		$arrPrintDefine['ItemisedDataCall']	['Description']		['Start']	= 52;
		$arrPrintDefine['ItemisedDataCall']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataCall']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataCall']	['Duration']		['Start']	= 152;
		$arrPrintDefine['ItemisedDataCall']	['Duration']		['Length']	= 9;
		$arrPrintDefine['ItemisedDataCall']	['Duration']		['Type']	= BILL_TYPE_DURATION;
				
		$arrPrintDefine['ItemisedDataCall']	['Charge']			['Start']	= 161;
		$arrPrintDefine['ItemisedDataCall']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataCall']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataS&E']	['RecordType']		['Value']	= "92";
		
		$arrPrintDefine['ItemisedDataS&E']	['Description']		['Start']	= 4;
		$arrPrintDefine['ItemisedDataS&E']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataS&E']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataS&E']	['Items']			['Start']	= 104;
		$arrPrintDefine['ItemisedDataS&E']	['Items']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataS&E']	['Items']			['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['ItemisedDataS&E']	['Charge']			['Start']	= 114;
		$arrPrintDefine['ItemisedDataS&E']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataS&E']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataKB']	['RecordType']		['Value']	= "93";
		
		$arrPrintDefine['ItemisedDataKB']	['Date']			['Start']	= 4;
		$arrPrintDefine['ItemisedDataKB']	['Date']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataKB']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['ItemisedDataKB']	['Time']			['Start']	= 14;
		$arrPrintDefine['ItemisedDataKB']	['Time']			['Length']	= 8;
		$arrPrintDefine['ItemisedDataKB']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrPrintDefine['ItemisedDataKB']	['CalledParty']		['Start']	= 22;
		$arrPrintDefine['ItemisedDataKB']	['CalledParty']		['Length']	= 30;
		$arrPrintDefine['ItemisedDataKB']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

		$arrPrintDefine['ItemisedDataKB']	['Description']		['Start']	= 52;
		$arrPrintDefine['ItemisedDataKB']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataKB']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataKB']	['DataTransfered']	['Start']	= 152;
		$arrPrintDefine['ItemisedDataKB']	['DataTransfered']	['Length']	= 10;
		$arrPrintDefine['ItemisedDataKB']	['DataTransfered']	['Type']	= BILL_TYPE_INTEGER;
				
		$arrPrintDefine['ItemisedDataKB']	['Charge']			['Start']	= 162;
		$arrPrintDefine['ItemisedDataKB']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataKB']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedDataSMS']	['RecordType']		['Value']	= "94";
		
		$arrPrintDefine['ItemisedDataSMS']	['Date']			['Start']	= 4;
		$arrPrintDefine['ItemisedDataSMS']	['Date']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataSMS']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['ItemisedDataSMS']	['Time']			['Start']	= 14;
		$arrPrintDefine['ItemisedDataSMS']	['Time']			['Length']	= 8;
		$arrPrintDefine['ItemisedDataSMS']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrPrintDefine['ItemisedDataSMS']	['CalledParty']		['Start']	= 22;
		$arrPrintDefine['ItemisedDataSMS']	['CalledParty']		['Length']	= 30;
		$arrPrintDefine['ItemisedDataSMS']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;

		$arrPrintDefine['ItemisedDataSMS']	['Description']		['Start']	= 52;
		$arrPrintDefine['ItemisedDataSMS']	['Description']		['Length']	= 100;
		$arrPrintDefine['ItemisedDataSMS']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrPrintDefine['ItemisedDataSMS']	['Items']			['Start']	= 152;
		$arrPrintDefine['ItemisedDataSMS']	['Items']			['Length']	= 10;
		$arrPrintDefine['ItemisedDataSMS']	['Items']			['Type']	= BILL_TYPE_INTEGER;
				
		$arrPrintDefine['ItemisedDataSMS']	['Charge']			['Start']	= 162;
		$arrPrintDefine['ItemisedDataSMS']	['Charge']			['Length']	= 11;
		$arrPrintDefine['ItemisedDataSMS']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemCallTypeFooter']['RecordType']		['Value']	= "99";
		
		$arrPrintDefine['ItemCallTypeFooter']['TotalCharge']		['Start']	= 4;
		$arrPrintDefine['ItemCallTypeFooter']['TotalCharge']		['Length']	= 11;
		$arrPrintDefine['ItemCallTypeFooter']['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		
		
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemSvcFooter']		['RecordType']		['Value']	= "89";
		
/*		$arrPrintDefine['ItemSvcFooter']		['TotalCharge']		['Start']	= 4;
		$arrPrintDefine['ItemSvcFooter']		['TotalCharge']		['Length']	= 11;
		$arrPrintDefine['ItemSvcFooter']		['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;*/
		
		
		
		
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Start']	= 0;
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Length']	= 4;
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['ItemisedFooter']	['RecordType']		['Value']	= "79";
		
		
		
		
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Start']	= 0;
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Length']	= 4;
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['InvoiceFooter']		['RecordType']		['Value']	= "18";
		
		$arrPrintDefine['InvoiceFooter']		['Delivery']		['Start']	= 4;
		$arrPrintDefine['InvoiceFooter']		['Delivery']		['Length']	= 1;
		$arrPrintDefine['InvoiceFooter']		['Delivery']		['Type']	= BILL_TYPE_INTEGER;
		
		
		
		
		$arrPrintDefine['FileFooter']			['RecordType']		['Start']	= 0;
		$arrPrintDefine['FileFooter']			['RecordType']		['Length']	= 4;
		$arrPrintDefine['FileFooter']			['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrPrintDefine['FileFooter']			['RecordType']		['Value']	= "19";
		
		$arrPrintDefine['FileFooter']			['Date']			['Start']	= 4;
		$arrPrintDefine['FileFooter']			['Date']			['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrPrintDefine['FileFooter']			['InvoiceCount']	['Start']	= 14;
		$arrPrintDefine['FileFooter']			['InvoiceCount']	['Length']	= 10;
		$arrPrintDefine['FileFooter']			['InvoiceCount']	['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['FileFooter']			['Insert1Id']		['Start']	= 24;
		$arrPrintDefine['FileFooter']			['Insert1Id']		['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Insert1Id']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['FileFooter']			['Insert2Id']		['Start']	= 34;
		$arrPrintDefine['FileFooter']			['Insert2Id']		['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Insert2Id']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['FileFooter']			['Insert3Id']		['Start']	= 44;
		$arrPrintDefine['FileFooter']			['Insert3Id']		['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Insert3Id']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['FileFooter']			['Insert4Id']		['Start']	= 54;
		$arrPrintDefine['FileFooter']			['Insert4Id']		['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Insert4Id']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['FileFooter']			['Insert5Id']		['Start']	= 64;
		$arrPrintDefine['FileFooter']			['Insert5Id']		['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Insert5Id']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrPrintDefine['FileFooter']			['Insert6Id']		['Start']	= 74;
		$arrPrintDefine['FileFooter']			['Insert6Id']		['Length']	= 10;
		$arrPrintDefine['FileFooter']			['Insert6Id']		['Type']	= BILL_TYPE_INTEGER;

		
		
		
		$arrConfig['BillPrintDefine']									= $arrPrintDefine;
		

?>
