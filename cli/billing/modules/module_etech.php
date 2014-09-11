<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_etech
//----------------------------------------------------------------------------//
/**
 * module_etech
 *
 * Module for Bill Printing (Etech Style)
 *
 * Module for Bill Printing (Etech Style)
 *
 * @file		module_etech.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste' Davis
 * @version		7.01
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// BillingModuleEtech
//----------------------------------------------------------------------------//
/**
 * BillingModuleEtech
 *
 * Module for Bill Printing (Etech Style)
 *
 * Module for Bill Printing (Etech Style)
 *
 * @prefix		bil
 *
 * @package		billing
 * @class		BillingModuleEtech
 */
 class BillingModuleEtech
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for BillingModuleEtech
	 *
	 * Constructor method for BillingModuleEtech
	 *
	 * @return		BillingModuleEtech
	 *
	 * @method
	 */
 	function __construct($ptrThisDB, $arrConfig)
 	{
		// Set up the database reference
		$this->db = $ptrThisDB;
		
		// Init member variables
		$this->_strFilename		= NULL;
		$this->_strSampleFile	= NULL;
		
		// Init database statements
		$this->_insInvoiceOutput		= new StatementInsert("InvoiceOutput");
		
		$arrColumns['CustomerGroup']	= "Account.CustomerGroup";
		$arrColumns['Account']			= "Account.Id";
		$arrColumns['PaymentTerms']		= "Account.PaymentTerms";
		$arrColumns['FirstName']		= "Contact.FirstName";
		$arrColumns['LastName']			= "Contact.LastName";
		$arrColumns['Suburb']			= "Account.Suburb";
		$arrColumns['State']			= "Account.State";
		$arrColumns['Postcode']			= "Account.Postcode";
		$arrColumns['AddressLine1']		= "Account.Address1";
		$arrColumns['AddressLine2']		= "Account.Address2";
		$arrColumns['BusinessName']		= "Account.BusinessName";
		$arrColumns['TradingName']		= "Account.TradingName";
		$arrColumns['DeliveryMethod']	= "Account.BillingMethod";
		$this->_selCustomerDetails		= new StatementSelect(	"Account LEFT OUTER JOIN Contact ON Account.PrimaryContact = Contact.Id",
																$arrColumns,
																"Account.Id = <Account>");
		
		$arrColumns = Array();
		$arrColumns[]					= "Total";
		$arrColumns[]					= "Tax";
		$arrColumns[]					= "Balance";
		$arrColumns[]					= "CreatedOn";
		$arrColumns[]					= "InvoiceRun";
		$this->_selLastBills			= new StatementSelect(	"Invoice",
																$arrColumns,
																"Account = <Account>",
																"CreatedOn DESC",
																BILL_PRINT_HISTORY_LIMIT - 1);
																
		$arrColumns = Array();
		$arrColumns['RecordTypeName']	= "RType.Name";
		$arrColumns['Charge']			= "SUM(ServiceTypeTotal.Charge)";
		$arrColumns['ServiceType']		= "RType.ServiceType";
		$this->_selServiceTypeTotals	= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, " .
																"RecordType AS RType",
																$arrColumns,
																"RecordType.GroupId = RType.Id AND ServiceTypeTotal.Account = <Account> AND ServiceTypeTotal.InvoiceRun = <InvoiceRun>",
																"RType.ServiceType, RType.Name",
																NULL,
																"RType.Id");
		
		$this->_selServices				= new StatementSelect(	"Service LEFT OUTER JOIN CostCentre ON Service.CostCentre = CostCentre.Id, ServiceTotal",
																"Service.FNN AS FNN, Service.Id AS Id, Service.ServiceType AS ServiceType, CostCentre.Name AS CostCentre",
																"Service.Account = <Account> AND (ISNULL(Service.ClosedOn) OR Service.ClosedOn > NOW()) AND ServiceTotal.Service = Service.Id",
																"Service.CostCentre, FNN");
		
		$this->_selServiceTotal			= new StatementSelect(	"ServiceTotal",
																"TotalCharge",
																"Service = <Service> AND InvoiceRun = <InvoiceRun>");
		/*
		$arrColumns = Array();
		$arrColumns['RecordTypeName']	= "RType.Name";
		$arrColumns['Charge']			= "SUM(CDR.Charge)";
		$arrColumns['CallCount']		= "COUNT(CDR.Id)";
		$this->_selServiceSummaries		= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id, " .
																"RecordType AS RType",
																$arrColumns,
																"RecordType.GroupId = RType.Id AND CDR.Service = <Service> AND (NOT ISNULL(CDR.RatedOn)) AND CDR.Credit = 0 AND CDR.InvoiceRun = <InvoiceRun> AND Status = ".CDR_TEMP_INVOICE,
																"RType.Name",
																NULL,
																"RType.Id\n" .
																"HAVING SUM(CDR.Charge) > 0.0");
																*/
		$arrColumns = Array();
		$arrColumns['RecordTypeName']	= "RecordGroup.Name";
		$arrColumns['Charge']			= "SUM(ServiceTypeTotal.Charge)";
		$arrColumns['CallCount']		= "SUM(ServiceTypeTotal.Records)";
		$this->_selServiceSummaries		= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON RecordType.Id = ServiceTypeTotal.RecordType, " .
																"RecordType AS RecordGroup",
																$arrColumns,
																"RecordGroup.Id = RecordType.GroupId AND " .
																"ServiceTypeTotal.Service = <Service> AND " .
																"InvoiceRun = <InvoiceRun> AND " .
																"ServiceTypeTotal.Charge > 0",
																"RecordGroup.Name",
																NULL,
																"RecordGroup.Name");
		$arrColumns = Array();
		$arrColumns['Charge']			= "Charge.Amount";
		$arrColumns['FNN']				= "Service.FNN";
		$arrColumns['Service']			= "Service.Id";
		$arrColumns['Description']		= "Charge.Description";
		$arrColumns['ChargeType']		= "Charge.ChargeType";
		$arrColumns['Nature']			= "Charge.Nature";
		$arrColumns['RecordTypeName']	= "'OC&C'";
		$arrColumns['ServiceType']		= "Service.ServiceType";
		$this->_selItemisedCharges		= new StatementSelect(	"Charge JOIN Service ON Service.Id = Charge.Service",
																$arrColumns,
																"Charge.Account = <Account> AND Charge.InvoiceRun = <InvoiceRun>");		
		

		$this->_selChargesTotal			= new StatementSelect(	"Charge",
																"SUM(Amount) AS Charge",
																"Account = <Account> AND InvoiceRun = <InvoiceRun>",
																"Nature ASC",
																NULL,
																"Nature");
		

		$this->_selServiceChargesTotal	= new StatementSelect(	"Charge",
																"SUM(Amount) AS Charge",
																"Service = <Service> AND InvoiceRun = <InvoiceRun>",
																"Nature ASC",
																NULL,
																"Nature");
		
		$arrColumns = Array();
		$arrColumns['Charge']			= "CDR.Charge";
		$arrColumns['FNN']				= "CDR.FNN";
		$arrColumns['Service']			= "CDR.Service";
		$arrColumns['Source']			= "CDR.Source";
		$arrColumns['Destination']		= "CDR.Destination";
		$arrColumns['StartDatetime']	= "CDR.StartDatetime";
		$arrColumns['EndDatetime']		= "CDR.EndDatetime";
		$arrColumns['Units']			= "CDR.Units";
		$arrColumns['Description']		= "CDR.Description";
		$arrColumns['DestinationCode']	= "CDR.DestinationCode";
		$arrColumns['RecordTypeName']	= "RType.Name";
		$arrColumns['DisplayType']		= "RType.DisplayType";
		$arrColumns['ServiceType']		= "RType.ServiceType";
		$this->_selItemisedCalls		= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id," .
																"RecordType AS RType",
																$arrColumns,
																"RType.Itemised = 1 AND CDR.Account = <Account> AND RecordType.GroupId = RType.Id AND CDR.Credit = 0 AND CDR.InvoiceRun = <InvoiceRun>",
																"CDR.FNN, RType.Name, CDR.StartDatetime");
																
		$this->_selRecordTypeTotal		= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id," .
																"RecordType AS RType",
																"SUM(ServiceTypeTotal.Charge) AS Charge",
																"RecordType.GroupId = RType.Id AND RType.Name = <RecordTypeName> AND ServiceTypeTotal.FNN = <FNN> AND ServiceTypeTotal.InvoiceRun = <InvoiceRun>",
																NULL,
																"1",
																"RType.Name");
		
		$this->_selWeReceived			= new StatementSelect(	"InvoicePayment",
																"SUM(Amount) AS WeReceived",
																"(InvoiceRun = <ThisInvoiceRun> OR InvoiceRun = <LastInvoiceRun>) AND Account = <Account>");
		
		//----------------------------------------------------------------------------//
		// Define the file format
		//----------------------------------------------------------------------------//
		
		$this->_strDelimeter = "|";
		/*
		// Define RecordTypes
		$arrDefine['FileHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['FileHeader']	['RecordType']		['Value']	= 1;
		$arrDefine['SPDetails']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SPDetails']		['RecordType']		['Value']	= 2;
		$arrDefine['GlobalMessage']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['GlobalMessage']	['RecordType']		['Value']	= 3;
		$arrDefine['Brands']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['Brands']		['RecordType']		['Value']	= 4;
		$arrDefine['SequenceNo']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SequenceNo']	['RecordType']		['Value']	= 5;
		$arrDefine['InvoiceNo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['InvoiceNo']		['RecordType']		['Value']	= 6;
		$arrDefine['BrandId']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['BrandId']		['RecordType']		['Value']	= 7;
		$arrDefine['InvoiceCharge']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['InvoiceCharge']	['RecordType']		['Value']	= 10;
		$arrDefine['Customer']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['Customer']		['RecordType']		['Value']	= 11;
		$arrDefine['ChargeSummary']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ChargeSummary']	['RecordType']		['Value']	= 15;
		$arrDefine['DetailsHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['DetailsHeader']	['RecordType']		['Value']	= 19;
		$arrDefine['CategoryHeader']['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CategoryHeader']['RecordType']		['Value']	= 20;
		$arrDefine['CategoryFooter']['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CategoryFooter']['RecordType']		['Value']	= 21;
		$arrDefine['DetailsFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['DetailsFooter']	['RecordType']		['Value']	= 29;
		$arrDefine['SvcSummHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SvcSummHeader']	['RecordType']		['Value']	= 39;
		$arrDefine['ServiceHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ServiceHeader']	['RecordType']		['Value']	= 40;
		$arrDefine['ServiceDetail']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ServiceDetail']	['RecordType']		['Value']	= 41;
		$arrDefine['ServiceTotals']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ServiceTotals']	['RecordType']		['Value']	= 45;
		$arrDefine['ServiceFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ServiceFooter']	['RecordType']		['Value']	= 48;
		$arrDefine['SvcSummFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SvcSummFooter']	['RecordType']		['Value']	= 49;
		$arrDefine['SpecialMsg1']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SpecialMsg1']	['RecordType']		['Value']	= 50;
		$arrDefine['SpecialMsg2']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SpecialMsg2']	['RecordType']		['Value']	= 51;
		$arrDefine['SpecialMsg3']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SpecialMsg3']	['RecordType']		['Value']	= 52;
		$arrDefine['SpecialMsg4']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SpecialMsg4']	['RecordType']		['Value']	= 53;
		$arrDefine['SpecialMsg5']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['SpecialMsg5']	['RecordType']		['Value']	= 54;
		$arrDefine['CreditHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CreditHeader']	['RecordType']		['Value']	= 60;
		$arrDefine['CreditBalance']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CreditBalance']	['RecordType']		['Value']	= 61;
		$arrDefine['CreditAdded']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CreditAdded']	['RecordType']		['Value']	= 62;
		$arrDefine['CreditUsed']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CreditUsed']	['RecordType']		['Value']	= 63;
		$arrDefine['CreditLeft']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CreditLeft']	['RecordType']		['Value']	= 64;
		$arrDefine['CreditFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CreditFooter']	['RecordType']		['Value']	= 69;
		$arrDefine['GraphInfo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['GraphInfo']		['RecordType']		['Value']	= 70;
		$arrDefine['GraphData']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['GraphData']		['RecordType']		['Value']	= 71;
		$arrDefine['ExtraInfo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ExtraInfo']		['RecordType']		['Value']	= 80;
		$arrDefine['InvoiceFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['InvoiceFooter']	['RecordType']		['Value']	= 95;
		$arrDefine['FileFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['FileFooter']	['RecordType']		['Value']	= 99;
		$arrDefine['ItemisedCall']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['ItemisedS&E']	['RecordType']		['Type']	= ETECH_ROW;
		
		// Call Details
		$arrDefine['CallLLNational']['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallLLNational']['RecordType']		['Value']	= 102;
		$arrDefine['CallLLTo1300']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallLLTo1300']	['RecordType']		['Value']	= 103;
		$arrDefine['CallLLToMob']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallLLToMob']	['RecordType']		['Value']	= 104;
		$arrDefine['CallLLToIDD']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallLLToIDD']	['RecordType']		['Value']	= 105;
		$arrDefine['CallMobToMob']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMobToMob']	['RecordType']		['Value']	= 106;
		$arrDefine['CallMobToNat']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMobToNat']	['RecordType']		['Value']	= 107;
		$arrDefine['CallMobOther']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMobOther']	['RecordType']		['Value']	= 110;
		$arrDefine['CallMobRoam']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMobRoam']	['RecordType']		['Value']	= 111;
		$arrDefine['CallMobToIDD']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMobToIDD']	['RecordType']		['Value']	= 112;
		$arrDefine['CallMobTo18']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMobTo18']	['RecordType']		['Value']	= 113;
		$arrDefine['Call13IDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['Call13IDD']		['RecordType']		['Value']	= 114;
		$arrDefine['CallLLOther']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallLLOther']	['RecordType']		['Value']	= 116;
		$arrDefine['Call18Inbound']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['Call18Inbound']	['RecordType']		['Value']	= 117;
		$arrDefine['Call13Inbound']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['Call13Inbound']	['RecordType']		['Value']	= 118;
		$arrDefine['CallSMS']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallSMS']		['RecordType']		['Value']	= 119;
		$arrDefine['CallMMS']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallMMS']		['RecordType']		['Value']	= 120;
		$arrDefine['CallUnknown']	['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallUnknown']	['RecordType']		['Value']	= 135;
		$arrDefine['CallS&E']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallS&E']		['RecordType']		['Value']	= 208;				// Record type is 108 in footer
		$arrDefine['CallOC&C']		['RecordType']		['Type']	= ETECH_ROW;
		$arrDefine['CallOC&C']		['RecordType']		['Value']	= 237;				// Record type is 137 in footer
		
		// Row Type					Field Name			Property	Value
		//------------------------------------------------------------------------------
		
		// File Header
		$arrDefine['FileHeader']	['Date']			['Type']	= ETECH_DATE_YYYYMMDD;
		$arrDefine['FileHeader']	['Filename']		['Type']	= ETECH_STRING;
		$arrDefine['FileHeader']	['SPName']			['Type']	= ETECH_STRING;
		$arrDefine['FileHeader']	['BillingPeriod']	['Type']	= ETECH_DATE_MONTH_YY;
		
		// SP Details
		$arrDefine['SPDetails']		['BusinessName']	['Type']	= ETECH_STRING;
		$arrDefine['SPDetails']		['Address1']		['Type']	= ETECH_STRING;
		$arrDefine['SPDetails']		['Address2']		['Type']	= ETECH_STRING;
		$arrDefine['SPDetails']		['Suburb']			['Type']	= ETECH_STRING;
		$arrDefine['SPDetails']		['Postcode']		['Type']	= ETECH_INTEGER;
		$arrDefine['SPDetails']		['State']			['Type']	= ETECH_STRING;
		$arrDefine['SPDetails']		['Phone']			['Type']	= ETECH_FNN;
		$arrDefine['SPDetails']		['Fax']				['Type']	= ETECH_FNN;
		$arrDefine['SPDetails']		['URL']				['Type']	= ETECH_STRING;
		$arrDefine['SPDetails']		['BPayCode']		['Type']	= ETECH_INTEGER;
		$arrDefine['SPDetails']		['InvoiceDate']		['Type']	= ETECH_DATE_YYYY_MM_DD;
		$arrDefine['SPDetails']		['DueByDate']		['Type']	= ETECH_DATE_YYYY_MM_DD;
		
		// Global Message (unused)
		$arrDefine['GlobalMessage']	['Message']			['Type']	= ETECH_STRING;
		
		// Brands (Telco Blue, Voicetalk, etc)
		$arrDefine['Brands']		['Brand1']			['Type']	= ETECH_STRING;
		$arrDefine['Brands']		['Brand2']			['Type']	= ETECH_STRING;
		$arrDefine['Brands']		['Brand3']			['Type']	= ETECH_STRING;
		
		// Invoice Sequence Number
		$arrDefine['SequenceNo']	['SequenceNo']		['Type']	= ETECH_INTEGER;
		
		// Invoice Number
		$arrDefine['InvoiceNo']		['InvoiceNo']		['Type']	= ETECH_INTEGER;
		
		// Invoice Brand Id
		$arrDefine['BrandId']		['BrandId']			['Type']	= ETECH_INTEGER;
		
		// Invoice Charges
		$arrDefine['InvoiceCharge']	['PreviousBalance']	['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['InvoiceCharge']	['Received']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['InvoiceCharge']	['Adjustments']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['InvoiceCharge']	['Overdue']			['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['InvoiceCharge']	['NewCharges']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['InvoiceCharge']	['AmountOwing']		['Type']	= ETECH_SHORT_CURRENCY;
		
		// Customer Details
		$arrDefine['Customer']		['AccountNo']		['Type']	= ETECH_INTEGER;
		$arrDefine['Customer']		['CheckDigit']		['Type']	= ETECH_INTEGER;
		$arrDefine['Customer']		['Address1']		['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['Address2']		['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['Suburb']			['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['Postcode']		['Type']	= ETECH_INTEGER;
		$arrDefine['Customer']		['State']			['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['BusinessName']	['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['Firstname']		['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['Lastname']		['Type']	= ETECH_STRING;
		$arrDefine['Customer']		['InvoiceType']		['Type']	= ETECH_STRING;
		
		// Charge Summaries
		$arrDefine['ChargeSummary']	['Category']		['Type']	= ETECH_STRING;
		$arrDefine['ChargeSummary']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Category Header
		$arrDefine['CategoryHeader']['Category']		['Type']	= ETECH_STRING;
		$arrDefine['CategoryHeader']['FNN']				['Type']	= ETECH_FNN;
		
		// Itemised Call Data
		$arrDefine['ItemisedCall']	['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrDefine['ItemisedCall']	['Datetime']		['Type']	= ETECH_DATETIME;
		$arrDefine['ItemisedCall']	['CalledParty']		['Type']	= ETECH_FNN;
		$arrDefine['ItemisedCall']	['Description']		['Type']	= ETECH_STRING;
		$arrDefine['ItemisedCall']	['Duration']		['Type']	= ETECH_DURATION;
		$arrDefine['ItemisedCall']	['Charge']			['Type']	= ETECH_LONG_CURRENCY;
		
		// Itemised S&E Data
		$arrDefine['ItemisedS&E']	['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrDefine['ItemisedS&E']	['Description']		['Type']	= ETECH_STRING;
		$arrDefine['ItemisedS&E']	['Charge']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Charge Footer
		$arrDefine['CategoryFooter']['CategoryId']		['Type']	= ETECH_INTEGER;
		$arrDefine['CategoryFooter']['RecordTotal']		['Type']	= ETECH_INTEGER;
		$arrDefine['CategoryFooter']['FNN']				['Type']	= ETECH_FNN;
		$arrDefine['CategoryFooter']['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Service Header
		$arrDefine['ServiceHeader']	['FNN']				['Type']	= ETECH_FNN;
		//$arrDefine['ServiceHeader']	['CostCentre']		['Type']	= ETECH_STRING;
		
		// Service Detail
		$arrDefine['ServiceDetail']	['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrDefine['ServiceDetail']	['ChargeType']		['Type']	= ETECH_STRING;
		$arrDefine['ServiceDetail']	['Charge']			['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['ServiceDetail']	['CallCount']		['Type']	= ETECH_INTEGER;
		
		// Service Totals
		$arrDefine['ServiceTotals']	['RecordTotal']		['Type']	= ETECH_INTEGER;
		$arrDefine['ServiceTotals']	['FNN']				['Type']	= ETECH_FNN;
		$arrDefine['ServiceTotals']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Service Footer
		//$arrDefine['ServiceFooter']	['CostCentre']		['Type']	= ETECH_STRING;
		//$arrDefine['ServiceFooter']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Special Message 1
		$arrDefine['SpecialMsg1']	['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 2
		$arrDefine['SpecialMsg2']	['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 3
		$arrDefine['SpecialMsg3']	['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 4
		$arrDefine['SpecialMsg4']	['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 5
		$arrDefine['SpecialMsg5']	['Message']			['Type']	= ETECH_STRING;
		
		// Credit Balance
		$arrDefine['CreditBalance']	['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Added
		$arrDefine['CreditAdded']	['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Left
		$arrDefine['CreditLeft']	['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Remaining
		$arrDefine['CreditLeft']	['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Global Graph Info
		$arrDefine['GraphInfo']		['DateRange']		['Type']	= ETECH_DATE_RANGE;
		
		// Graph Data
		$arrDefine['GraphData']		['CurrentMonth']	['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['GraphData']		['LastMonth']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['GraphData']		['2MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['GraphData']		['3MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['GraphData']		['4MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrDefine['GraphData']		['5MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		
		// File Footer
		$arrDefine['FileFooter']	['InvoiceCount']	['Type']	= ETECH_INTEGER;*/
		
		$this->_arrDefine = $arrConfig['FileFormatEtech'];
		
		//----------------------------------------------------------------------------//


 	}
 	
	//------------------------------------------------------------------------//
	// Clean()
	//------------------------------------------------------------------------//
	/**
	 * Clean()
	 *
	 * Cleans the database table where our data is stored
	 *
	 * Cleans the database table where our data is stored
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function Clean()
 	{

		
		return TRUE;
 	}
 	
 	//------------------------------------------------------------------------//
	// AddInvoice()
	//------------------------------------------------------------------------//
	/**
	 * AddInvoice()
	 *
	 * Adds an invoice to the bill
	 *
	 * Adds an invoice to the bill
	 * 
	 * @param		array		$arrInvoiceDetails		Associative array of details for this Invoice
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function AddInvoice($arrInvoiceDetails)
 	{
		$arrDefine = $this->_arrDefine;
		
		// Retrieve customer data
		$arrWhere['Account'] = $arrInvoiceDetails['Account'];
		if ($this->_selCustomerDetails->Execute($arrWhere) === FALSE)
		{
			Debug("Line ".__LINE__.": ".$this->_selCustomerDetails->Error());
			return FALSE;
		}
		else
		{
			$arrCustomerData	= $this->_selCustomerDetails->Fetch();
		}
		
		$bolHasBillHistory	= $this->_selLastBills->Execute(Array('Account' => $arrInvoiceDetails['Account'])) ? TRUE : FALSE;
		$arrBillHistory		= $this->_selLastBills->FetchAll();
		
		// Invoice Brand Id
		//----------------------------------------------------------------------
		switch($arrCustomerData['CustomerGroup'])
		{
			case CUSTOMER_GROUP_VOICETALK:
				$intCustomerGroup = ETECH_BRAND_VOICETALK;
				break;
			case CUSTOMER_GROUP_IMAGINE:
				$intCustomerGroup = ETECH_BRAND_IMGAINE;
				break;
			case CUSTOMER_GROUP_TELCOBLUE:
				$intCustomerGroup = ETECH_BRAND_TELCOBLUE;
				break;
			default:
				// No Customer Group
				Debug("***************** No Customer Group!!! *****************");
				return FALSE;
		}
		$arrDefine['BrandId']	['BrandId']		['Value']	= $intCustomerGroup;
		$arrFileData[] = $arrDefine['BrandId'];
		
		// Invoice Charges
		//----------------------------------------------------------------------
		if($bolHasBillHistory)
		{
			// Display the previous bill details
			$arrDefine['InvoiceCharge']	['PreviousBalance']	['Value']	= $arrBillHistory[0]['AccountBalance'] + $arrBillHistory[0]['Total'] + $arrBillHistory[0]['Tax'];						
			
			// WeReceived = payments from last invoice + payments from this invoice
			$arrWeReceivedData['LastInvoiceRun']	= $arrBillHistory[0]['InvoiceRun'];
			$arrWeReceivedData['ThisInvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
			$arrWeReceivedData['Account']			= $arrInvoiceDetails['Account'];
			$this->_selWeReceived->Execute($arrWeReceivedData);
			$arrWeReceived = $this->_selWeReceived->Fetch();

			// If there were no payments, this will return NULL, so account for this
			if ($arrWeReceived['WeReceived'] == NULL)
			{
				$arrWeReceived['WeReceived'] = 0.0;
			}
			$arrDefine['InvoiceCharge']	['Received']['Value']	= $arrWeReceived['WeReceived'];
		}
		else
		{
			// There is no previous bill
			$arrDefine['InvoiceCharge']	['PreviousBalance']	['Value']	= 0;						
			$arrDefine['InvoiceCharge']	['Received']		['Value']	= 0;
		}
		$arrDefine['InvoiceCharge']	['Adjustments']		['Value']	= $arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceCharge']	['Overdue']			['Value']	= $arrInvoiceDetails['AccountBalance'];
		$arrDefine['InvoiceCharge']	['NewCharges']		['Value']	= $arrInvoiceDetails['Balance'];
		$arrDefine['InvoiceCharge']	['AmountOwing']		['Value']	= ((float)$arrInvoiceDetails['Balance'] + (float)$arrInvoiceDetails['AccountBalance']) - (float)$arrInvoiceDetails['Credits'];
		$arrFileData[] = $arrDefine['InvoiceCharge'];
		
		// Customer Details
		//----------------------------------------------------------------------
		
		// Determine Invoice Type
		switch($arrCustomerData['BillingMethod'])
		{
			case BILLING_METHOD_POST:
				$strBillingMethod = "P";
				break;
			case BILLING_METHOD_EMAIL:
			case BILLING_METHOD_DO_NOT_SEND:
				// Etech doesn't support "do not send", mark as Email
				$strBillingMethod = "E";
				break;
			default:
				Debug("***************** Unhandled Billing Method!!! *****************");
				return FALSE;
		}
		
		// If there is less than $10 on the invoice, mark as Email
		if ($arrInvoiceDetails['Balance'] < INVOICE_MIN_CHARGE)
		{
			$strBillingMethod = "E";
		}
		
		
		$arrDefine['Customer']		['AccountNo']		['Value']	= $arrInvoiceDetails['Account'];
		$arrDefine['Customer']		['CheckDigit']		['Value']	= MakeLuhn($arrInvoiceDetails['Account']);
		$arrDefine['Customer']		['Address1']		['Value']	= $arrCustomerData['AddressLine1'];
		$arrDefine['Customer']		['Address2']		['Value']	= $arrCustomerData['AddressLine2'];
		$arrDefine['Customer']		['Suburb']			['Value']	= $arrCustomerData['Suburb'];
		$arrDefine['Customer']		['Postcode']		['Value']	= $arrCustomerData['Postcode'];
		$arrDefine['Customer']		['State']			['Value']	= $arrCustomerData['State'];
		$arrDefine['Customer']		['BusinessName']	['Value']	= $arrCustomerData['BusinessName'];
		$arrDefine['Customer']		['Firstname']		['Value']	= $arrCustomerData['FirstName'];
		$arrDefine['Customer']		['Lastname']		['Value']	= $arrCustomerData['LastName'];
		$arrDefine['Customer']		['InvoiceType']		['Value']	= $strBillingMethod;
		$arrFileData[] = $arrDefine['Customer'];
		
		// Charge Summaries
		//----------------------------------------------------------------------
		$arrDefine['ChargeSummary']	['Category']		['Type']	= ETECH_STRING;
		$arrDefine['ChargeSummary']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// get details from servicetype totals
		$arrServiceTypeTotalVars['Account']		= $arrInvoiceDetails['Account'];
		$arrServiceTypeTotalVars['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		$mixResult = $this->_selServiceTypeTotals->Execute($arrServiceTypeTotalVars);
		if ($mixResult === FALSE)
		{
			Debug("Line ".__LINE__.": ".$this->_selServiceTypeTotals->Error());
			return FALSE;
		}
		
		$arrServiceTypeTotals = $this->_selServiceTypeTotals->FetchAll();
		if(!is_array($arrServiceTypeTotals))
		{
			$arrServiceTypeTotals = Array();
		}
		
		// get details from charge table
		if (($intChargeCount = $this->_selChargesTotal->Execute($arrServiceTypeTotalVars)) === FALSE)
		{
			Debug("Line ".__LINE__.": ".$this->_selChargesTotal->Error());
			return FALSE;
		}
		$arrChargesTotal = $this->_selChargesTotal->FetchAll();
		
		// build output
		foreach($arrServiceTypeTotals as $arrTotal)
		{
			$arrRowType = $this->GetRowType($arrTotal['RecordTypeName'], $arrTotal['ServiceType']);
			
			$arrDefine['ChargeSummary']	['Category']	['Value']	= $arrRowType['LongDesc'];
			$arrDefine['ChargeSummary']	['Total']		['Value']	= $arrTotal['Charge'];
			$arrFileData[] = $arrDefine['ChargeSummary'];
		}
		// build charge output
		if ($intChargeCount)
		{
			$arrDefine['ChargeSummary']	['Category']	['Value']	= "Other Charges and Credits";
			
			$fltChargeTotal = 0.0;
			foreach ($arrChargesTotal as $arrTotal)
			{
				if ($arrTotal['Nature'] == NATURE_CR)
				{
					$fltChargeTotal -= $arrTotal['Charge'];
				}
				else
				{
					$fltChargeTotal += $arrTotal['Charge'];
				}
			}
			$arrDefine['ChargeSummary']	['Total']		['Value']	= $fltChargeTotal;
			$arrFileData[] = $arrDefine['ChargeSummary'];
		}
		// add gst entry
		$arrDefine['ChargeSummary']		['Category']	['Value']	= "GST Total";
		$arrDefine['ChargeSummary']		['Total']		['Value']	= $arrInvoiceDetails['Tax'];
		$arrFileData[] = $arrDefine['ChargeSummary'];
		
		// Category Header / Itemised Calls/ Category Footer
		//----------------------------------------------------------------------
		
		// get list of CDRs grouped by service no, record type
		// ignoring any record types that do not get itemised
		$intItemisedCount = $this->_selItemisedCalls->Execute(Array('Account' => $arrInvoiceDetails['Account'], 'InvoiceRun' => $arrInvoiceDetails['InvoiceRun']));
		if ($intItemisedCount === FALSE)
		{
			Debug("Line ".__LINE__.": ".$this->_selItemisedCalls->Error());
			return FALSE;
		}
		$arrItemisedCalls = $this->_selItemisedCalls->FetchAll();
		
		// get details...
		//TODO!rich! I assume this is what should be here ??!!
		$arrItemisedVars['Account']		= $arrInvoiceDetails['Account'];
		$arrItemisedVars['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		
		// grab itemised charges
		if ($this->_selItemisedCharges->Execute($arrItemisedVars) === FALSE)
		{
			Debug("Line ".__LINE__.": ".$this->_selItemisedCalls->Error());
			return FALSE;
		}
		while ($arrCharge = $this->_selItemisedCharges->Fetch())
		{
			$arrItemisedCalls[] = $arrCharge;
		}
		
		// reset counters
		$strCurrentService		= "";
		$strCurrentRecordType	= "";
		$strCurrentPlace		= "";
		$strCurrentLongDesc		= "";
		$intRecordCount			= 0;
		$fltRecordTypeTotal		= 0.0;
		$intCategoryId			= 0;
		$intCurrentRowType		= NULL;
		$arrData				= Array();
		// add start record
		$arrFileData[] = $arrDefine['DetailsHeader'];
		// for each record
		if($intItemisedCount)
		{
			foreach($arrItemisedCalls as $arrData)
			{
				// if new service
				if (($arrData['FNN'] != $strCurrentService) || ($arrData['RecordTypeName'] != $strCurrentRecordType))
				{
					// if old service and record type exists
					if (($strCurrentService != "") && ($strCurrentRecordType != ""))
					{
						// Get the RecordType total
						if ($strCurrentRecordType == "Other Charges and Credits")
						{
							$arrSelectData['Service']			= $arrData['Service'];
							$arrSelectData['InvoiceRun']		= $arrInvoiceDetails['InvoiceRun'];
							if ($this->_selServiceChargesTotal->Execute($arrSelectData))
							{
								$arrChargesTotal = $this->_selServiceChargesTotal->FetchAll();
								
								$fltRecordTypeTotal = 0.0;
								foreach ($arrChargesTotal as $arrTotal)
								{
									if ($arrTotal['Nature'] == NATURE_CR)
									{
										$fltRecordTypeTotal -= $arrTotal['Charge'];
									}
									else
									{
										$fltRecordTypeTotal += $arrTotal['Charge'];
									}
								}
							}
						}
						else
						{
							$arrSelectData['FNN']				= $strCurrentService;
							$arrSelectData['RecordTypeName']	= $strCurrentRecordType;
							$arrSelectData['InvoiceRun']		= $arrInvoiceDetails['InvoiceRun'];
							if ($this->_selRecordTypeTotal->Execute($arrSelectData) === FALSE)
							{
								Debug("Line ".__LINE__.": ".$this->_selRecordTypeTotal->Error());
								return FALSE;
							}
							$arrRecordTypeTotal	= $this->_selRecordTypeTotal->Fetch();
							$fltRecordTypeTotal	= $arrRecordTypeTotal['Charge'];
						}
						
						// add category footer
						$arrDefine['CategoryFooter']['CategoryId']		['Value']	= $intCategoryId;
						$arrDefine['CategoryFooter']['RecordTotal']		['Value']	= $intRecordCount;
						$arrDefine['CategoryFooter']['FNN']				['Value']	= $strCurrentService;
						$arrDefine['CategoryFooter']['Total']			['Value']	= $fltRecordTypeTotal;
						$arrFileData[] = $arrDefine['CategoryFooter'];
					}
					$strCurrentService		= $arrData['FNN'];
					$strCurrentRecordType	= $arrData['RecordTypeName'];

					// Do our Inbound Service Type hack...
					if ($arrData['ServiceType'] == SERVICE_TYPE_INBOUND)
					{
						switch (substr($arrData['FNN'], 0, 2))
						{
							case "18":
								$intServiceType = 1800;
								break;
							case "13":
								$intServiceType = 1300;
								break;
							default:
								$intServiceType = $arrData['ServiceType'];
						}
					}
					else
					{
						$intServiceType = $arrData['ServiceType'];
					}

					// Determine the Row and Category Ids
					$arrRowType		= $this->GetRowType($strCurrentRecordType, $intServiceType);
					if ($arrRowType['RowType'] > 199)
					{
						$intCategoryId  = $arrRowType['RowType'] - 100;
					}
					else
					{
						$intCategoryId  = $arrRowType['RowType'];
					}
					$intCurrentRowType		= $arrRowType['RowType'];
					$strCurrentLongDesc		= $arrRowType['LongDesc'];
					$strCurrentPlace		= $arrRowType['ShortDesc'];
										
					// add service record (80)
					$arrDefine['CategoryHeader']['Category']		['Value']	= $strCurrentLongDesc;
					$arrDefine['CategoryHeader']['FNN']				['Value']	= $arrData['FNN'];
					$arrFileData[] = $arrDefine['CategoryHeader'];
					
					$intRecordCount = 0;
				}
				
				// build charge record
				$intRecordCount++;
				if ($intCurrentRowType >= 200)
				{
					// S&E and OC&C
					$arrRow = $arrDefine['ItemisedS&E'];
					if ($intCurrentRowType = 237)
					{
						$strDescription = $arrData['ChargeType']." - ".$arrData['Description'];
						if ($arrData['Nature'] == NATURE_CR)
						{
							$arrData['Charge'] = 0 - $arrData['Charge'];
						}
					}
					else
					{
						$strDescription = $arrData['Description']." - ".date("j M Y", strtotime($arrData['StartDatetime']))." to ".date("j M Y", strtotime($arrData['EndDatetime']));
					}
					$arrRow['RecordCount']		['Value']	= $intRecordCount;
					$arrRow['Description']		['Value']	= $strDescription;
					$arrRow['Charge']			['Value']	= $arrData['Charge'];
					$arrRow['RecordType']		['Value']	= $intCurrentRowType;
					$arrFileData[] = $arrRow;
				}
				else
				{
					// Everything else
					$arrRow = $arrDefine['ItemisedCall'];
					$arrRow['RecordCount']		['Value']	= $intRecordCount;
					$arrRow['Datetime']			['Value']	= $arrData['StartDatetime'];
					$arrRow['CalledParty']		['Value']	= $arrData['Destination'];
					$arrRow['Description']		['Value']	= $strCurrentPlace;
					$intMinutes		= (int)floor($arrData['Units'] / 60);
					$intSeconds		= (int)($arrData['Units'] % 60);
					$strDuration	= "$intMinutes:".sprintf("%02d", $intSeconds);
					$arrRow['Duration']			['Value']	= $strDuration;
					$arrRow['Charge']			['Value']	= $arrData['Charge'];
					$arrRow['RecordType']		['Value']	= $intCurrentRowType;
					$arrFileData[] = $arrRow;
				}
			}
			
			// Get the RecordType total
			if ($strCurrentRecordType == "Other Charges and Credits")
			{
				$arrSelectData['Service']			= $arrData['Service'];
				$arrSelectData['InvoiceRun']		= $arrInvoiceDetails['InvoiceRun'];
				if ($this->_selServiceChargesTotal->Execute($arrSelectData))
				{
					$arrChargesTotal = $this->_selServiceChargesTotal->FetchAll();
					
					$fltRecordTypeTotal = 0.0;
					foreach ($arrChargesTotal as $arrTotal)
					{
						if ($arrTotal['Nature'] == NATURE_CR)
						{
							$fltRecordTypeTotal -= $arrTotal['Charge'];
						}
						else
						{
							$fltRecordTypeTotal += $arrTotal['Charge'];
						}
					}
				}
			}
			else
			{
				$arrSelectData['FNN']				= $strCurrentService;
				$arrSelectData['RecordTypeName']	= $strCurrentRecordType;
				$arrSelectData['InvoiceRun']		= $arrInvoiceDetails['InvoiceRun'];
				if ($this->_selRecordTypeTotal->Execute($arrSelectData) === FALSE)
				{
					Debug("Line ".__LINE__.": ".$this->_selRecordTypeTotal->Error());
					return FALSE;
				}
				$arrRecordTypeTotal	= $this->_selRecordTypeTotal->Fetch();
				$fltRecordTypeTotal	= $arrRecordTypeTotal['Charge'];
			}
			
			// add category footer
			$arrDefine['CategoryFooter']['CategoryId']		['Value']	= $intCategoryId;
			$arrDefine['CategoryFooter']['RecordTotal']		['Value']	= $intRecordCount;
			$arrDefine['CategoryFooter']['FNN']				['Value']	= $strCurrentService;
			$arrDefine['CategoryFooter']['Total']			['Value']	= $fltRecordTypeTotal;
			$arrFileData[] = $arrDefine['CategoryFooter'];
		}			
		// add details footer
		$arrFileData[] = $arrDefine['DetailsFooter'];
		
		// Service Header/Details/Totals/Footer
		//----------------------------------------------------------------------

		// get details from servicetype totals
		$intCount = $this->_selServices->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		$arrServices = $this->_selServices->FetchAll();
		
		// build output
		$strCurrentService		= "";
		$strCurrentCostCentre	= NULL;
		$fltCostCentreTotal		= 0.0;
		$arrFileData[] = $arrDefine['SvcSummHeader'];
		foreach($arrServices as $arrService)
		{			
			// Is this a new Cost Centre?
			if ($strCurrentCostCentre != $arrService['CostCentre'])
			{
				// Was there an old Cost Centre?
				if ($strCurrentCostCentre != NULL && $strCurrentService != "")
					{
					// Insert Cost Centre Footer
					$arrDefine['ServiceFooter']	['CostCentre']		['Value']	= $strCurrentCostCentre;
					$arrDefine['ServiceFooter']	['Total']			['Value']	= $fltCostCentreTotal;
					
				}
				$strCurrentCostCentre	= $arrService['CostCentre'];
				$fltCostCentreTotal		= 0.0;
			}

			// Add Service Header
			$arrDefine['ServiceHeader']		['FNN']				['Value']	= $arrService['FNN'];
			$arrDefine['ServiceHeader']		['CostCentre']		['Value']	= $strCurrentCostCentre;
			$arrFileData[] = $arrDefine['ServiceHeader'];
			
			// The individual RecordTypes for each Service
			$intSummaryCount = $this->_selServiceSummaries->Execute(Array('Service' => $arrService['Id'], 'InvoiceRun' => $arrInvoiceDetails['InvoiceRun']));
			if ($intSummaryCount === FALSE)
			{
				Debug('$intSummaryCount is FALSE! for service '.$arrService['FNN']);
			}
			$arrServiceSummaries = $this->_selServiceSummaries->FetchAll();
			
			// RecordType breakdown
			$intRecordCount = 0;
			foreach($arrServiceSummaries as $arrServiceSummary)
			{
				$arrEtechRowType = $this->GetRowType($arrServiceSummary['RecordTypeName'], $arrService['ServiceType']);
			
				$intRecordCount++;
				$arrDefine['ServiceDetail']	['RecordCount']		['Value']	= $intRecordCount;
				$arrDefine['ServiceDetail']	['ChargeType']		['Value']	= $arrEtechRowType['LongDesc'];
				$arrDefine['ServiceDetail']	['CallCount']		['Value']	= $arrServiceSummary['CallCount'];
				$arrDefine['ServiceDetail']	['Charge']			['Value']	= $arrServiceSummary['Charge'];
				$arrFileData[] = $arrDefine['ServiceDetail'];
			}
			
			// Get ServiceTotal
			$arrServiceData['Service']		= $arrService['Id'];
			$arrServiceData['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
			$this->_selServiceTotal->Execute($arrServiceData);
			$arrServiceTotal = $this->_selServiceTotal->Fetch();
			$arrDefine['ServiceTotals']		['RecordTotal']	['Value']	= $intRecordCount;
			$arrDefine['ServiceTotals']		['FNN']			['Value']	= $arrService['FNN'];
			$arrDefine['ServiceTotals']		['Total']		['Value']	= $arrServiceTotal['TotalCharge'];
			$arrFileData[] = $arrDefine['ServiceTotals'];
			
			// Add to CostCentre total
			$fltCostCentreTotal += (float)$arrServiceTotal['TotalCharge'];
		}
		
		// Don't print the ServiceFooter, because we don't do Cost Centres 
		//$arrFileData[] = $arrDefine['ServiceFooter'];
		
		$arrFileData[] = $arrDefine['SvcSummFooter'];
		

		// Special Messages
		//----------------------------------------------------------------------
		// TODO!rich! Find out what this message will be and hard code it
		
		// Credits
		//----------------------------------------------------------------------
		// TODO!rich! Find out if we are going to use this functionality....
		
		// Graph Info
		//----------------------------------------------------------------------
		// Global Graph Info
		// TODO!rich! remove this date range hack
		$strDateRange = date("d/m/Y", strtotime("-1 month", time())).":".date("d/m/Y", time());
		$arrDefine['GraphInfo']		['DateRange']		['Value']	= $strDateRange;
		$arrFileData[] = $arrDefine['GraphInfo'];
		
		// Graph Data
		$i = 0;
		foreach ($arrBillHistory as $arrBill)
		{
			if ($arrBill['Total'] == NULL)
			{
				$arrBillHistory[$i]['Total'] = 0;
			}
			if ($arrBill['Tax'] == NULL)
			{
				$arrBillHistory[$i]['Tax'] = 0;
			}
			$i++;
		}
		
		$arrDefine['GraphData']		['CurrentMonth']	['Value']	= $arrInvoiceDetails['Balance'];
		$arrDefine['GraphData']		['LastMonth']		['Value']	= $arrBillHistory[0]['Total'] + $arrBillHistory[0]['Tax'];
		$arrDefine['GraphData']		['2MonthsAgo']		['Value']	= $arrBillHistory[1]['Total'] + $arrBillHistory[1]['Tax'];
		$arrDefine['GraphData']		['3MonthsAgo']		['Value']	= $arrBillHistory[2]['Total'] + $arrBillHistory[2]['Tax'];
		$arrDefine['GraphData']		['4MonthsAgo']		['Value']	= $arrBillHistory[3]['Total'] + $arrBillHistory[3]['Tax'];
		$arrDefine['GraphData']		['5MonthsAgo']		['Value']	= $arrBillHistory[4]['Total'] + $arrBillHistory[4]['Tax'];
		$arrFileData[] = $arrDefine['GraphData'];

		// Invoice Footer
		$arrFileData[] = $arrDefine['InvoiceFooter'];

	
		// Process and implode the data so it can be inserted into the DB
		if (!($strFileContents = $this->GenerateInvoiceData($arrFileData)))
		{
			// Invalid data
			Debug("Invalid data encountered!");
			return FALSE;
		}

		// Insert into InvoiceOutput table
		$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		$arrWhere['Account']	= $arrInvoiceDetails['Account'];
		$arrWhere['Data']		= $strFileContents;
		if ($this->_insInvoiceOutput->Execute($arrWhere) === FALSE)
		{
			// Error
			Debug("Line ".__LINE__.": ".$this->_insInvoiceOutput->Error());
			return FALSE;			
		}
		return TRUE;
 	}
 	
 	//------------------------------------------------------------------------//
	// BuildOutput()
	//------------------------------------------------------------------------//
	/**
	 * BuildOutput()
	 *
	 * Builds the bill file
	 *
	 * Builds the bill file
	 *
	 * @param		boolean		bolSample		optional This is a sample billing file
	 *
	 * @return		string						filename
	 *
	 * @method
	 */
 	function BuildOutput($intOutputType = BILL_COMPLETE)
 	{
		// generate filenames
		switch ($intOutputType)
		{
			case BILL_SAMPLE:
				$strFilename	= BILLING_LOCAL_PATH_SAMPLE."sample_inv_telcoblue_".date("Ymd")."_0123456789.txt";
				$strTempFile	= BILLING_LOCAL_PATH_SAMPLE."sample_inv_telcoblue_".date("Ymd")."_0123456789.tmp";
				$strZipName		= BILLING_LOCAL_PATH_SAMPLE."sample_inv_telcoblue_".date("Ymd")."_0123456789.zip";
				$strInvoiceTable = 'InvoiceTemp';
				break;
			
			case BILL_COMPLETE:
				$strFilename	= BILLING_LOCAL_PATH."inv_telcoblue_".date("Ymd")."_0123456789.txt";
				$strTempFile	= BILLING_LOCAL_PATH."inv_telcoblue_".date("Ymd")."_0123456789.tmp";
				$strZipName		= BILLING_LOCAL_PATH."inv_telcoblue_".date("Ymd")."_0123456789.zip";
				$strInvoiceTable = 'Invoice';
				break;
				
			case BILL_REPRINT:
				$strFilename	= BILLING_LOCAL_PATH."reprint_inv_telcoblue_".date("Ymd")."_0123456789.txt";
				$strTempFile	= BILLING_LOCAL_PATH."reprint_inv_telcoblue_".date("Ymd")."_0123456789.tmp";
				$strZipName		= BILLING_LOCAL_PATH."reprint_inv_telcoblue_".date("Ymd")."_0123456789.zip";
				$strInvoiceTable = 'Invoice';
				break;	
		}
		
		$selMetaData = new StatementSelect($strInvoiceTable, "MIN(Id) AS MinId, MAX(Id) AS MaxId, COUNT(Id) AS Invoices, InvoiceRun", "Status = ".INVOICE_PRINT, NULL, NULL, "Status");
		if ($selMetaData->Execute() === FALSE)
		{
			Debug('$selMetaData : '.$selMetaData->Error());
			return FALSE;
		}
		$arrMetaData = $selMetaData->Fetch();
		
		Debug("{$arrMetaData['MinId']} {$arrMetaData['MaxId']} {$arrMetaData['Invoices']} {$arrMetaData['InvoiceRun']}");

		// Set the InvoiceRun
		$strInvoiceRun = $arrMetaData['InvoiceRun'];
		
		if($arrMetaData['Invoices'] == 0)
		{
			// Nothing to do
			Debug("Nothing to do");
			return FALSE;
		}

		$qryBuildFile	= new Query();
		$strColumns		= "'005|', LPAD(CAST(InvoiceOutput.Id AS CHAR), 10, '0'), '\\n006|', $strInvoiceTable.Id, '\\n', InvoiceOutput.Data";
		$strWhere		= "InvoiceOutput.InvoiceRun = '$strInvoiceRun' AND InvoiceOutput.InvoiceRun = $strInvoiceTable.InvoiceRun";
		$strQuery		=	"SELECT $strColumns INTO OUTFILE '$strTempFile' FIELDS TERMINATED BY '' ESCAPED BY '' LINES TERMINATED BY '\\n'\n" .
							"FROM InvoiceOutput JOIN $strInvoiceTable USING (Account)\n".
							"WHERE $strWhere\n" .
							"ORDER BY InvoiceOutput.Id";
		if($intOutputType == BILL_SAMPLE)
		{
			if((int)$arrMetaData['MaxId'] < BILL_PRINT_SAMPLE_LIMIT)
			{
				$strQuery .= "LIMIT ".(int)$arrMetaData['MaxId'];
			}
			else
			{
				$strQuery .= "LIMIT ".rand((int)$arrMetaData['MinId'] , (int)$arrMetaData['MaxId'] - BILL_PRINT_SAMPLE_LIMIT).", ".BILL_PRINT_SAMPLE_LIMIT;
			}
		}
		if (file_exists($strFilename) || file_exists($strTempFile) || file_exists($strZipName))
		{
			@unlink($strFilename);
			@unlink($strTempFile);
			@unlink($strZipName);
		}
		if ($qryBuildFile->Execute($strQuery) === FALSE)
		{
			Debug('$qryBuildFile : '.$qryBuildFile->Error());
			return FALSE;
		}
		
		// Write the file header
		$strHeader		=	"001|".date("Ymd", time())."|".basename($strFilename)."|TelcoBlue|".date("F y")."\n" .
							"002|TelcoBlue|Locked Bag 4000||Fortitude Valley|4006|QLD|1300 835 262|07 3250 4228|www.telcoblue.com.au|0|".date("Y-m-d", time())."|".date("Y-m-d", strtotime("+".PAYMENT_TERMS_DEFAULT." days", time()))."\n" .
							"003|\n" .
							"004|1:TelcoBlue|2:Voicetalk|4:Imagine\n";
		$ptrFile		= fopen($strFilename, "w");
		fwrite($ptrFile, $strHeader);
		fclose($ptrFile);
		
		// Append the invoice data from the temp file
		$strCommand = "cat $strTempFile >> $strFilename";
		exec($strCommand);		
		
		// Append metadata to bill output file
		$strFooter		=	"099|".str_pad($arrMetaData['Invoices'], 10, "0", STR_PAD_LEFT);
		$ptrFile		= fopen($strFilename, "a");
		fwrite($ptrFile, $strFooter);
		fclose($ptrFile);
		
		// zip files
		$strCommand = "zip $strZipName $strFilename";
		exec($strCommand);
		
		// set filename internally
		$this->_strFilename = $strZipName;
		
		// return zip's filename
		return $strZipName;
 	}
 	
 	//------------------------------------------------------------------------//
	// SendOutput()
	//------------------------------------------------------------------------//
	/**
	 * SendOutput()
	 *
	 * Sends the bill file
	 *
	 * Sends the bill file
	 *
	 * @param		boolean		bolSample		optional This is a sample billing file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function SendOutput($bolSample)
 	{
		// Set the remote directory
		if ($bolSample)
		{
			$strRemoteDir	= BILL_PRINT_REMOTE_DIR_SAMPLE;
			$strFile		= $this->_strSampleFile;
		}
		else
		{
			$strRemoteDir	= BILL_PRINT_REMOTE_DIR;
			$strFile		= $this->Filename;
		}
		
		/*
		// Connect to FTP
		$ptrFTP = ftp_connect(BILL_PRINT_HOST);
		if (!ftp_login($ptrFTP, BILL_PRINT_USERNAME, BILL_PRINT_PASSWORD))
		{
			// Log in failed
			return FALSE;
		}
		ftp_chdir($ptrFTP, $strRemoteDir);
		
		// Upload file
		if(!ftp_put($ptrFTP, basename($strFile), $strFile, FTP_ASCII))
		{
			return FALSE;
		}
		
		// Close the FTP connection
		ftp_close($ptrFTP);
		*/
		return TRUE;
 	}
	
	//------------------------------------------------------------------------//
	// BuildSample()
	//------------------------------------------------------------------------//
	/**
	 * BuildSample()
	 *
	 * Builds a sample bill file
	 *
	 * Builds a sample bill file
	 *
 	 * @param		string		strInvoiceRun	The Invoice Run to build from
 	 * 
	 * @return		string						filename
	 *
	 * @method
	 */
 	function BuildSample($strInvoiceRun)
 	{
		return $this->BuildOutput($strInvoiceRun, BILL_SAMPLE);
 	}
 	
 	//------------------------------------------------------------------------//
	// SendSample()
	//------------------------------------------------------------------------//
	/**
	 * SendOutput()
	 *
	 * Sends a sample bill file
	 *
	 * Sends a sample bill file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function SendSample()
 	{
		return $this->SendOutput(BILL_SAMPLE);
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// GenerateInvoiceData()
	//------------------------------------------------------------------------//
	/**
	 * GenerateInvoiceData()
	 *
	 * Generates a block of invoice data
	 *
	 * Generates a block of invoice data from the passed indexed array
	 * 
	 * @param	array					Indexed array of data to be imploded and validated
	 *
	 * @return	mixed					string	: invoice data
	 * 									FALSE	: invalid input
	 *
	 * @method
	 */
 	protected function GenerateInvoiceData($arrFileData)
 	{
		if (!is_array($arrFileData))
		{
			return FALSE;
		}
		
		$i = 0;
		// Loop through Records
		foreach ($arrFileData as $strKey=>$arrRecord)
		{
			$i++;
			$t = 0;
			$arrLine = Array();
			
			// Loop through Fields
			foreach ($arrRecord as $arrField)
			{
				// If this is a non-print field, then skip it
				if($arrField['Print'] === FALSE)
				{
					continue;
				}
				
				// If this is an optional field, and the value is NULL, then remove/ignore
				// Must be the last field in the row
				if(($arrField['Optional'] === TRUE) && (count($arrRecord) > $t + 1))
				{
					unset($arrFileData[$i-1][$t]);
					continue;
				}
				
				$strValue = $arrField['Value'];
				$t++;
				
				// Process the field
				switch ($arrField['Type'])
				{
					case ETECH_ROW:
						if (!$strValue)
						{
							$strValue = "0";
						}
						$strValue = str_pad((int)$strValue, 3, "0", STR_PAD_LEFT);
						break;
					case ETECH_INTEGER:
						if (!(int)$strValue)
						{
							$strValue = "0";
						}
						$strValue = (int)$strValue;
						break;
					case ETECH_FNN:
						if (!(int)$strValue)
						{
							$strValue = "0123456789";
						}
						$strValue = str_pad((int)$strValue, 10, "0", STR_PAD_LEFT);
						break;
					case ETECH_STRING:
						if ($strValue == NULL)
						{
							$strValue = "";
						}
						break;
					case ETECH_DATE_YYYYMMDD:
						if (!$strValue)
						{
							$strValue = "19700101";
						}
						break;
					case ETECH_DATE_MONTH_YY:
						if (!$strValue)
						{
							$strValue = "January 70";
						}
						break;
					case ETECH_DATE_YYYY_MM_DD:
						if (!$strValue)
						{
							$strValue = "1970-01-01";
						}
						break;
					case ETECH_DATETIME:
						if (strtotime($strValue) == 0)
						{
							$strValue = "1970-01-01 00:00:00";
						}
						break;
					case ETECH_DATE_RANGE:
						if (!$strValue)
						{
							$strValue = "01/01/1970:01/01/1970";
						}
						break;
						
					case ETECH_DURATION:
						if ($strValue == NULL)
						{
							$strValue = "0:00";
						}
						break;
					case ETECH_SHORT_CURRENCY:
						if (!$strValue)
						{
							$strValue = "0";
						}
						
						$strTemp = number_format((float)$strValue, 2);
						if((substr($strValue, 0, 1) == "-") && (substr($strTemp, 0, 1) != "-"))
						{
							$strTemp = "-".substr($strTemp, 1);
						}
						$strValue = $strTemp;
						break;
					case ETECH_LONG_CURRENCY:
						if (!$strValue)
						{
							$strValue = "0";
						}
						
						$strTemp = number_format((float)$strValue, 4);
						if((substr($strValue, 0, 1) == "-") && (substr($strTemp, 0, 1) != "-"))
						{
							$strTemp = "-".substr($strTemp, 1);
						}
						$strValue = $strTemp;
						break;
					default:
						// Unknown Data Type
						Debug("BIG FLOPPY DONKEY DICK (Unknown Bill Printing Data Type: {$arrField['Type']})");
						Debug($arrRecord);
						return FALSE;
				}
				
				$arrLine[] = $strValue;
			}
			
			$strFileContents .= implode($this->_strDelimeter, $arrLine)."\n";
		}
		
		// Return the data
		return rtrim($strFileContents);
 	}
 	
 	
 	
 	
	//------------------------------------------------------------------------//
	// GetRowType()
	//------------------------------------------------------------------------//
	/**
	 * GetRowType()
	 *
	 * Determines the Etech RowType from the Call's RecordType and ServiceType
	 *
	 * Determines the Etech RowType from the Call's RecordType and ServiceType
	 *
 	 * @param		string		$strRecordTypeName	The record type to work with
 	 * @param		integer		$intServiceType		The service type to work with
 	 * @param		string		$strFNN				optional FNN (used to determine type of inbound service)
 	 * 
	 * @return		array							Associative array with the RowType data
	 *
	 * @method
	 */
 	function GetRowType($strRecordTypeName, $intServiceType, $strFNN = NULL)
 	{
		// ServiceType hack for indials
		if ($strFNN)
		{
			if ($intServiceType == SERVICE_TYPE_INBOUND)
			{
				switch (substr($strFNN, 0, 2))
				{
					case "18":
						$intServiceType = 1800;
						break;
					case "13":
						$intServiceType = 1300;
						break;
					default:
						$intServiceType = $intServiceType;
				}
			}
		}
		
		// general record types
		switch ($strRecordTypeName)
		{
			case "S&E":
			case "Service & Equipment":
			case "Service and Equipment":
				return Array( 'RowType' => 208, 'LongDesc' => "Service & Equipment" );
			case "OC&C":
				return Array( 'RowType' => 237, 'LongDesc' => "Other Charges and Credits" );
		}
		
		// ServiceType specific record types
		switch ($intServiceType)
		{
			case SERVICE_TYPE_LAND_LINE:
				switch ($strRecordTypeName)
				{
					case "Local":
						return Array( 'RowType' => 101	, 'LongDesc' => "Local Calls"					, 'ShortDesc' => "Local" );
					case "National":
						return Array( 'RowType' => 102	, 'LongDesc' => "National Calls"				, 'ShortDesc' => "National" );
					case "Calls to 1300":
						return Array( 'RowType' => 103	, 'LongDesc' => "Calls to 13/1300 Numbers"		, 'ShortDesc' => "Calls" );
					case "Calls to Mobile":
						return Array( 'RowType' => 104	, 'LongDesc' => "Calls to Mobiles"				, 'ShortDesc' => "Calls" );
					case "International Direct Dial":
						return Array( 'RowType' => 105	, 'LongDesc' => "International Calls"			, 'ShortDesc' => "International" );
					case "SMS":
					case "Other":
					default:
						return Array( 'RowType' => 116	, 'LongDesc' => "Other Call Types"				, 'ShortDesc' => "Other" );
				}
				break;
			
			case SERVICE_TYPE_MOBILE:
				switch ($strRecordTypeName)
				{
					case "International Direct Dial":
						return Array( 'RowType' => 113	, 'LongDesc' => "Mobile to International"		, 'ShortDesc' => "Mobile" );
					case "Mobile to Mobile":
						return Array( 'RowType' => 106	, 'LongDesc' => "Mobile to Mobile"				, 'ShortDesc' => "Mobile" );
					case "Mobile to National":
						return Array( 'RowType' => 107	, 'LongDesc' => "Mobile to National"			, 'ShortDesc' => "Mobile" );
					case "Mobile International Roaming":
						return Array( 'RowType' => 111	, 'LongDesc' => "Mobile International Roaming"	, 'ShortDesc' => "Mobile" );
					case "Mobile to 1800 Numbers":
						return Array( 'RowType' => 113	, 'LongDesc' => "Mobile to 1800 Numbers"		, 'ShortDesc' => "Mobile" );
					case "Mobile SMS":
						return Array( 'RowType' => 119	, 'LongDesc' => "Mobile - SMS"					, 'ShortDesc' => "Mobile" );
					case "Mobile MMS":
						return Array( 'RowType' => 120	, 'LongDesc' => "Mobile - MMS"					, 'ShortDesc' => "Mobile" );
					case "Mobile Other":
					default:
						return Array( 'RowType' => 110	, 'LongDesc' => "Mobile - Other Charges"		, 'ShortDesc' => "Mobile" );
				}
				break;
			
			case 1800:
				return Array( 'RowType' => 117	, 'LongDesc' => "1800 Inbound"							, 'ShortDesc' => "1800" );
				break;
			
			case 1300:
				return Array( 'RowType' => 118	, 'LongDesc' => "13/1300 Inbound"						, 'ShortDesc' => "13/1300" );
				break;
				
			case SERVICE_TYPE_INBOUND:
				return Array( 'RowType' => 117	, 'LongDesc' => "1800/13/1300 Inbound"					, 'ShortDesc' => "1800/13/1300" );
		}
		
		Debug("Record Type: '$strRecordTypeName'; Service Type: $intServiceType");

		return Array( 'RowType' => 135	, 'LongDesc' => "Unknown Call Type"								, 'ShortDesc' => "Unknown" );
 	}
 }

?>
