<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// decode_etech
//----------------------------------------------------------------------------//
/**
 * decode_etech
 *
 * Contains all classes for decoding etech scrape data
 *
 * Contains all classes for decoding etech scrape data
 *
 * @file		decode_etech.php
 * @language	PHP
 * @package		vixen_import
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDecode
//----------------------------------------------------------------------------//
/**
 * VixenDecode
 *
 * etech decode Module
 *
 * etech decode Module
 *
 *
 * @prefix		obj
 *
 * @package		vixen_import
 * @class		VixenDecode
 */
 class VixenDecode extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		// local config
		$this->arrConfig = $arrConfig;
		
		// setup db object
		$this->sqlQuery = new Query();
		
		// sql result array
		$this->sqlResult = Array();
	}
	
	// ------------------------------------//
	// Decode Constants
	// ------------------------------------//
	
	// carrier code	
	function DecodeCarrier($intCarrierCode)
	{
		switch ($intCarrierCode)
		{
			case 19:
				$intCarrier = CARRIER_UNITEL;
				break;
			case 24:
				$intCarrier = CARRIER_OPTUS;
				break;
			case 26:
				$intCarrier = CARRIER_AAPT;
				break;
			default:
				$intCarrier = 0;
		}
		
		return $intCarrier;
	}
	
	// ------------------------------------//
	// FETCH RECORDS
	// ------------------------------------//
	
	function FetchIDDGroupRate()
	{
		$strQuery 	= "SELECT DataSerialised, AxisM FROM ScrapeRates";
		$strName	= 'IDDGroupRate';
		return $this->FetchResult($strName, $strQuery);
	}
	
	function FetchCustomer()
	{
		$strQuery 	= "SELECT CustomerId, DataSerialized AS DataSerialised FROM ScrapeAccount ";
		$strName	= 'Account';
		return $this->FetchResult($strName, $strQuery);
	}
	
	// generic fetch
	function FetchResult($strName, $strQuery)
	{
		// check if we have the results yet
		if (!$this->sqlResult[$strName])
		{
			// init the counter & total
			$this->sqlResult[$strName]['count'] = 0;
			$this->sqlResult[$strName]['total'] = 0;
			// if not, get them
			$strQuery .= " LIMIT 100";
			$this->sqlResult[$strName]['sql'] = $this->sqlQuery->Execute($strQuery);
			if (!$this->sqlResult[$strName]['sql'])
			{
				// return false if we can't get the results
				Return FALSE;
			}
		}
		
		// incrament the total
		$this->sqlResult[$strName]['total']++;
		
		// check if we need to get the next batch of results
		if ($this->sqlResult[$strName]['count'] > 99)
		{
			// reset the counter
			$this->sqlResult[$strName]['count'] = 0;
			
			// free the current result
			$this->sqlResult[$strName]['sql']->free();
			
			// get the next group of records
			$strQuery .= " LIMIT {$this->sqlResult[$strName]['total']}, 100";
			$this->sqlResult[$strName]['sql'] = $this->sqlQuery->Execute($strQuery);
			if (!$this->sqlResult[$strName]['sql'])
			{
				// return false if we can't get the results
				Return FALSE;
			}
		}
		
		// increment the counter
		$this->sqlResult[$strName]['count']++;
		
		// return next record
		return $this->sqlResult[$strName]['sql']->fetch_assoc();
	}
	
	// ------------------------------------//
	// DECODE RECORDS
	// ------------------------------------//
	
	// decode a customer
	function DecodeCustomer($arrCustomer)
	{
		// clean the output array
		$arrOutput = Array();
		
		// is this customer archived
		$bolArchived = ($arrCustomer ['archived'] ? "TRUE" : "FALSE");
		
		// ------------------------------------//
		// Account Group
		// ------------------------------------//
		$arrOutput['AccountGroup'][0] = Array('Id'=>mysql_escape_string($arrCustomer['CustomerId']), Archived=>$bolArchived);
		
		// ------------------------------------//
		// Credit Card
		// ------------------------------------//
		
		// credit card type
		switch ($arrCustomer['cc_type'])
		{
			case 'Visa':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_VISA;
				break;
			
			case 'Mastercard':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_MASTERCARD;
				break;
				
			case 'Bankcard':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_BANKCARD;
				break;
			
			case 'Amex':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_AMEX;
				break;
				
			case 'Diners':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_DINERS;
				break;
		}
		
		// credit card details
		if ($arrOutput['CreditCard'][0])
		{
			$arrOutput['CreditCard'][0]['AccountGroup'] 	= mysql_escape_string ($arrCustomer['CustomerId']);
			$arrOutput['CreditCard'][0]['Name'] 			= mysql_escape_string ($arrCustomer['cc_name']);
			$arrOutput['CreditCard'][0]['CardNumber'] 		= mysql_escape_string ($arrCustomer['cc_num']);
			$arrOutput['CreditCard'][0]['ExpMonth'] 		= mysql_escape_string ($arrCustomer['cc_exp_m']);
			$arrOutput['CreditCard'][0]['ExpYear'] 			= mysql_escape_string ($arrCustomer['cc_exp_y']);
			$arrOutput['CreditCard'][0]['CVV'] 				= mysql_escape_string ($arrCustomer['cc_cvv']);
		}
		
		// ------------------------------------//
		// Account
		// ------------------------------------//
		
		// customer group
		switch ($arrCustomer['customer_group'])
		{
			case 'VoiceTalk':
				$arrCustomer['customer_group'] = 2;
				break;
				
			case 'Imagine':
				$arrCustomer['customer_group'] = 3;
				break;
				
			case 'TelcoBlue':
			default:
				$arrCustomer['customer_group'] = 1;
		}
		
		// abn / acn
		$arrCustomer ['abn'] = (strlen (preg_replace ("/\D/", "", $arrCustomer ['abn_acn'])) == 11) ? $arrCustomer ['abn_acn'] : "";
		$arrCustomer ['acn'] = (strlen (preg_replace ("/\D/", "", $arrCustomer ['abn_acn'])) == 9) ? $arrCustomer ['abn_acn'] : "";
		
		// account
		$arrOutput['CreditCard'][0]['Id'] 				= mysql_escape_string ($arrCustomer['CustomerId']);
		$arrOutput['CreditCard'][0]['BusinessName'] 	= mysql_escape_string ($arrCustomer['businessname']);
		$arrOutput['CreditCard'][0]['TradingName'] 		= mysql_escape_string ($arrCustomer['tradingname']);
		$arrOutput['CreditCard'][0]['ABN'] 				= mysql_escape_string ($arrCustomer['abn']);
		$arrOutput['CreditCard'][0]['ACN'] 				= mysql_escape_string ($arrCustomer['acn']);
		$arrOutput['CreditCard'][0]['Address1'] 		= mysql_escape_string ($arrCustomer['address1']);
		$arrOutput['CreditCard'][0]['Address2'] 		= mysql_escape_string ($arrCustomer['address2']);
		$arrOutput['CreditCard'][0]['Suburb'] 			= mysql_escape_string (strtoupper($arrCustomer['suburb']));
		$arrOutput['CreditCard'][0]['Postcode'] 		= mysql_escape_string ($arrCustomer['postcode']);
		$arrOutput['CreditCard'][0]['State'] 			= mysql_escape_string (strtoupper($arrCustomer['state']));
		$arrOutput['CreditCard'][0]['Country'] 			= 'AU';
		$arrOutput['CreditCard'][0]['CustomerGroup'] 	= mysql_escape_string ($arrCustomer['customer_group']);
		//TODO!!!! - $arrOutput['CreditCard'][0]['CreditCard']
		$arrOutput['CreditCard'][0]['AccountGroup'] 	= mysql_escape_string ($row ['CustomerId']) . "', ";
		$arrOutput['CreditCard'][0]['Archived'] 		= $bolArchived;
		
		// ------------------------------------//
		// Contacts
		// ------------------------------------//
		
		$arrContact = Array();
		$arrContact['AccountGroup'] 			= mysql_escape_string ($arrCustomer['CustomerId']);
		$arrContact['Title'] 					= mysql_escape_string ($arrCustomer['title']);
		$arrContact['FirstName'] 				= mysql_escape_string ($arrCustomer['firstname']);
		$arrContact['LastName'] 				= mysql_escape_string ($arrCustomer['lastname']);
		$arrContact['DOB'] 						= sprintf("%04d", intval ($arrCustomer['dob_year'])) . "-" . sprintf("%02d", ($arrCustomer['dob_month'] != "") ? intval($MonthAbbr[trim($arrCustomer['dob_month'])]) : "0") . "-" . sprintf("%02d", intval($arrCustomer['dob_day']));
		$arrContact['JobTitle'] 				= mysql_escape_string ($arrCustomer['position']);
		$arrContact['Email'] 					= mysql_escape_string ($arrCustomer['admin_email']);
		$arrContact['Account'] 					= mysql_escape_string ($arrCustomer['CustomerId']);
		$arrContact['CustomerContact'] 			=  1;
		$arrContact['Phone'] 					= mysql_escape_string ($arrCustomer['phone']);
		$arrContact['Mobile'] 					= mysql_escape_string ($arrCustomer['mobile']);
		$arrContact['Fax'] 						= mysql_escape_string ($arrCustomer['fax']);
		$arrContact['UserName'] 				= mysql_escape_string ($row['CustomerId']);
		$arrContact['PassWord'] 				= sha1("password"); //TODO!!!! - create a random password ????
		$arrContact['Archived'] 				= $bolArchived;
		
		//NOTE : First contact will be added with the name from the etech system
		//			and the first available email address. Any additional users
		//			will have their name changed to Billing Contact or 
		//			Secondary Billing Contact.
		
		// if we have an admin email address
		if ($arrCustomer ['admin_email'])
		{
			// add admin user
			$arrOutput['Contact'][] = $arrContact;
			
			// change the contact details for next user
			unset($arrContact['Title']);
			unset($arrCustomer['lastname']);
			$arrContact['FirstName'] 				= 'Billing Contact';
			$arrContact['UserName'] 				.= "-1";
			$arrContact['CustomerContact'] 			=  0;
		}

		// if we have a billing addrees that is different from the admin address
		if ($arrCustomer['billing_email'] && $arrCustomer['admin_email'] != $arrCustomer['billing_email'])
		{
			// add billing user
			$arrContact['Email'] 					= mysql_escape_string ($arrCustomer['billing_email']);
			$arrOutput['Contact'][] = $arrContact;
			
			// change the contact details for next user
			unset($arrContact['Title']);
			unset($arrCustomer['lastname']);
			$arrContact['FirstName'] 				= 'Secondary Billing Contact';
			$arrContact['UserName'] 				.= "-2";
			$arrContact['CustomerContact'] 			=  0;
		}
		
		// if we have a second billing addrees that is different from the admin address & the first billing address
		if ($arrCustomer['billing_email_2'] && $arrCustomer['admin_email'] != $arrCustomer['billing_email_2'] && $arrCustomer['billing_email'] != $arrCustomer['billing_email_2'])
		{
			// add second billing user
			$arrContact['Email'] 					= mysql_escape_string ($arrCustomer['billing_email_2']);
			$arrOutput['Contact'][] = $arrContact;
		}
		
		// if we did not add a contact yet
		if (!$arrOutput['Contact'])
		{
			// add a contact
			$arrOutput['Contact'][] = $arrContact;
		}
		
		// ------------------------------------//
		// Services
		// ------------------------------------//
		
		// clean the service arrays
		$arrServices = Array();
		$arrServiceType = Array();
		
		// for each service number
		foreach ($arrCustomer['sn'] as $sn_id => $_SN)
		{
			// clean the service number
			$strFNN = CleanFNN($_SN['Number'], $_SN['AreaCode']);
			
			// find the service type
			$arrServiceType[$strFNN] = ServiceType($strFNN);
			
			// check landlines for indial ranges
			if ($arrServiceType[$strFNN] == SERVICE_TYPE_LAND_LINE)
			{
				$strIndialPrefix = substr($strFNN, 0, 8); 
				$arrIndial[$strIndialPrefix]['Count']++;
			}
		}
		
		// set default service details
		$arrService = Array();
		$arrService['AccountGroup'] 			= mysql_escape_string ($arrCustomer['CustomerId']);
		$arrService['Account'] 					= mysql_escape_string ($arrCustomer['CustomerId']);
		
		// for each service number
		foreach ($arrCustomer['sn'] as $sn_id => $_SN)
		{
			// default is not an indial 
			$arrService['Indial100'] 			= 0;
			
			// check Land Lines for indial ranges
			if ($arrServiceType[$strFNN] == SERVICE_TYPE_LAND_LINE)
			{
				// if this service is part of an indial range
				$strIndialPrefix = substr($strFNN, 0, 8); 
				if ($arrIndial[$strIndialPrefix]['Count'] > 90)
				{
					// check if we already have a GDN
					if($arrIndial[$strIndialPrefix]['GDN'])
					{
						// if so, don't add this service
						continue;
					}
					else
					{
						// if not, add this service as the GDN
						$arrIndial[$strIndialPrefix]['GDN'] = $strFNN;
						$arrService['Indial100'] 			= 1;
					}
				}
			}
			
			// add service
			$arrService['EtechId'] 				= mysql_escape_string ($_SN['Id']);
			$arrService['FNN'] 					= $strFNN;
			$arrService['ServiceType'] 			= $arrServiceType[$strFNN];
			$arrOutput['Service'][$strFNN]		= $arrService;
		}
		
		// return the output array
		return $arrOutput;
	}
	
	// decode a group of IDD rate records
	function DecodeIDDGroupRate($arrScrapeRate)
	{
		// return on error
		if (!is_array($arrScrapeRate))
		{
			return FALSE;
		}
		
		// clean output array
		$arrFullOutput = Array();
		
		// set RateGroup wide values
		$fltStdFlagfall		= $arrScrapeRate['StdFlag'];
		$fltExsFlagfall		= $arrScrapeRate['PostFlag'];
		$arrTitle 			= explode(': ',$arrScrapeRate['Title']);
		$strName 			= trim($arrTitle[1]);
		
		// get carrier code
		$intCarrier			= $this->DecodeCarrier($arrScrapeRate['Carrier']);	
		
		// get carrier name
		if ($intCarrier)
		{
			$strCarrier 	= GetCarrierName($intCarrier);
		}
		else
		{
			$strCarrier 	= 'Unknown';
		}
		
		// for each record
		foreach ($arrScrapeRate['Rates'] as $arrRate)
		{
			// clean output array
			$arrOutput = Array();
			
			// get rate specific values
			$strDestination 	= $arrRate['Destination'];
			$intCapSet 			= Max($arrScrapeRate['SetCap'],  $arrRate['CapSet']);
			
			// set output array values
			$arrOutput['Name'] 				= "$strName : $strCarrier : $strDestination";
			$arrOutput['Description'] 		= "Calls to $strDestination via the $strCarrier network on the $strName plan";
			$arrOutput['StdRatePerUnit'] 	= number_format($arrRate['StdRate'] / 60, 8, '.', '');
			$arrOutput['ExsRatePerUnit'] 	= number_format($arrRate['PostCredit'] / 60, 8, '.', '');
			if ($intCapSet)
			{
				$arrOutput['CapUsage'] 		= Max($arrScrapeRate['CapTime'], $arrRate['CapSeconds']);
				$arrOutput['CapCost']		= Max($arrScrapeRate['MaxCost'], $arrRate['CapCost']);
			}
			$arrOutput['StdFlagfall'] 		= $fltStdFlagfall;
			$arrOutput['ExsFlagfall'] 		= $fltExsFlagfall;
			$arrOutput['ServiceType'] 		= 102;
			$arrOutput['RecordType'] 		= 28;
			$arrOutput['StdUnits'] 			= 1;
			$arrOutput['StartTime'] 		= '00:00:00';
			$arrOutput['EndTime'] 			= '23:59:59';
			$arrOutput['Monday'] 			= 1;
			$arrOutput['Tuesday'] 			= 1;
			$arrOutput['Wednesday'] 		= 1;
			$arrOutput['Thursday'] 			= 1;
			$arrOutput['Friday'] 			= 1;
			$arrOutput['Saturday'] 			= 1;
			$arrOutput['Sunday'] 			= 1;
			
			// try to find the destination code
			$strQuery = "SELECT Code FROM DestinationCode WHERE CarrierDescription LIKE '$strDestination' AND Carrier = $intCarrier LIMIT 1";
			$sqlResult = $this->sqlQuery->Execute($strQuery);
			$row = $sqlResult->fetch_assoc();
			if ($row['Code'])
			{
				$arrOutput['Destination'] 			= $row['Code'];
			}
			
			$arrFullOutput[] = $arrOutput;
		}
		
		return $arrFullOutput;
	}
 }


?>
