<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// etech_biller
//----------------------------------------------------------------------------//
/**
 * etech_biller
 *
 * Import an etech billing file
 *
 * Import an etech billing file
 *
 * @file		etech_biller.php
 * @language	PHP
 * @package		Billing
 * @author		Jared 'flame' Herbohn, Rich "Waste" Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// EtechBiller
//----------------------------------------------------------------------------//
/**
 * EtechBiller
 *
 * Import an etech billing file
 *
 * Import an etech billing file
 *
 *
 * @prefix		etb
 *
 * @package		billing_app
 * @class		EtechBiller
 */
 class EtechBiller extends ApplicationBaseClass
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
	 * @return			EtechBiller
	 *
	 * @method
	 */
 	function __construct($arrConfig=NULL)
 	{
		parent::__construct();
		
		// Init statements
		$this->_insInvoice 			= new StatementInsert("Invoice");
		$this->_insServiceTypeTotal = new StatementInsert("ServiceTypeTotal");
		$this->_insServiceTotal		= new StatementInsert("ServiceTotal");
		
		$arrUpdateData = Array();
		$arrUpdateData['Status']		= '';
		$arrUpdateData['InvoiceRun']	= '';
		$arrUpdateData['Charge']		= '';
		$this->_ubiCDR	= new StatementUpdateById("CDR", $arrUpdateData);
		
		$this->_ubiEtechCDR = new StatementUpdateById("CDREtech", Array('Status' => NULL, 'VixenCDR' => NULL));
		
		/*
		$this->_selMatchCDR	= new StatementSelect(	"CDR",
													"Id, Charge",
													"Status != 199 AND " .
													"FNN = <FNN> AND " .
													"Account = <Account> AND " .
													"Units = <Units> AND " .
													"Destination = <Destination> AND " .
													"StartDatetime = <StartDatetime> AND EndDatetime = <EndDateTime>");*/
													
		$this->_selMatchCDR	= new StatementSelect(	"CDR",
													"Id, Charge, Status, RecordType",
													"Status != ".CDR_INVOICED." AND " .
													"FNN LIKE <FNN> AND " .
													"(Units = <Units> OR (Units = 1 AND <Units> = 0)) AND " .
													"StartDatetime = <StartDatetime> AND " .
													"Credit = 0",
													NULL,
													2);
		
		
		$this->_selMatchLocal = new StatementSelect("CDR",
													"SUM(Charge) AS Total",
													"Account = <Account> AND " .
													"StartDatetime BETWEEN <StartDatetime> AND <EndDatetime>");
			
		$this->_insEtechCDR = new StatementInsert("CDREtech");
		
		$arrInsertData['Id']				= NULL;
		$arrInsertData['CreatedOn']			= NULL;
		$arrInsertData['DueOn']				= NULL;
		$arrInsertData['Credits']			= NULL;
		$arrInsertData['Debits']			= NULL;
		$arrInsertData['Total']				= NULL;
		$arrInsertData['Tax']				= NULL;
		$arrInsertData['AccountBalance']	= NULL;
		$this->_ubiInvoice	= new StatementUpdateById("Invoice", $arrInsertData);
		
		$arrUpdateData = Array();
		$arrUpdateData['TotalOwing']		= NULL;
		$this->_updInvoiceTotalOwing = new StatementUpdate("Invoice", "<Account> = Account AND InvoiceRun = <InvoiceRun>", $arrUpdateData);
		
		$this->_selInvoiceRun	= new StatementSelect("Invoice", "InvoiceRun", "Id = <Invoice>");
	}
	
	//------------------------------------------------------------------------//
	// AddInvoice
	//------------------------------------------------------------------------//
	/**
	 * AddInvoice()
	 *
	 * Imports an Etech Invoice to the Invoice table
	 *
	 * Imports an Etech Invoice to the Invoice table
	 * 
	 * @param	array	$arrInvoice		associative array to be inserted
	 * @param	string	$strInvoiceRun	generated InvoiceRun Id
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function AddInvoice($arrInvoice, $strInvoiceRun='')
 	{
		// Insert into the database
		$arrInsertData['AccountGroup']		= $arrInvoice['AccountGroup'];
		$arrInsertData['Account']			= $arrInvoice['Account'];
		$arrInsertData['CreatedOn']			= $arrInvoice['CreatedOn'];
		$arrInsertData['DueOn']				= $arrInvoice['DueOn'];
		$arrInsertData['Credits']			= $arrInvoice['Credits'];
		$arrInsertData['Debits']			= $arrInvoice['Debits'];
		$arrInsertData['Total']				= $arrInvoice['Total'];
		$arrInsertData['Tax']				= $arrInvoice['Tax'];
		$arrInsertData['Balance']			= $arrInvoice['Total'] + $arrInvoice['Tax'];
		$arrInsertData['Disputed']			= 0.0;
		$arrInsertData['AccountBalance']	= $arrInvoice['AccountBalance'];
		$arrInsertData['Status']			= INVOICE_COMMITTED;
		$arrInsertData['InvoiceRun']		= $strInvoiceRun;
		
		return (bool)$this->_insInvoice->Execute($arrInsertData);
	}
	
	//------------------------------------------------------------------------//
	// UpdateInvoice
	//------------------------------------------------------------------------//
	/**
	 * UpdateInvoice()
	 *
	 * Updates an Etech Invoice to the Invoice table
	 *
	 * Updates an Etech Invoice to the Invoice table
	 * 
	 * @param	array	$arrInvoice		associative array of invoice details
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function UpdateInvoice($arrInvoice)
 	{
		// Insert into the database
		$arrInsertData['Id']				= $arrInvoice['Id'];
		$arrInsertData['CreatedOn']			= $arrInvoice['CreatedOn'];
		$arrInsertData['DueOn']				= $arrInvoice['DueOn'];
		$arrInsertData['Credits']			= $arrInvoice['Credits'];
		$arrInsertData['Debits']			= $arrInvoice['Debits'];
		$arrInsertData['Total']				= $arrInvoice['Total'];
		$arrInsertData['Tax']				= $arrInvoice['Tax'];
		$arrInsertData['AccountBalance']	= $arrInvoice['AccountBalance'];
		
		return (bool)$this->_ubiInvoice->Execute($arrInsertData);
	}
	
	//------------------------------------------------------------------------//
	// MatchInvoices
	//------------------------------------------------------------------------//
	/**
	 * MatchInvoices()
	 *
	 * Matches an Etech Invoice to the Invoice table
	 *
	 * Matches an Etech Invoice to the Invoice table
	 * 
	 * @param	array	$arrInvoices	indexed array of Invoice Numbers
	 *
	 * @return			array			associative array of Invoices that are missing
	 *
	 * @method
	 */
 	function MatchInvoices($arrInvoices)
 	{
		// Find InvoiceRun
		$strInvoiceRun = $this->FindInvoiceRun($arrInvoices[0]);
		
		// Match
		$strInvoices = implode(', ', $arrInvoices);
		$this->_selMatchInvoice	= new StatementSelect("Invoice", "Id", "Id NOT IN ($strInvoices) AND InvoiceRun = '$strInvoiceRun'");
		$this->_selMatchInvoice->Execute();
		return $this->_selMatchInvoice->FetchAll();
	}
	
	//------------------------------------------------------------------------//
	// UpdateTotalOwing
	//------------------------------------------------------------------------//
	/**
	 * UpdateTotalOwing()
	 *
	 * Updates the TotalOwing for an Etech Invoice in the Invoice table
	 *
	 * Updates the TotalOwing for an Etech Invoice in the Invoice table
	 * 
	 * @param	array	$arrInvoice		associative array of invoice details
	 * @param	string	$strInvoiceRun	invoice run to apply to
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function UpdateTotalOwing($arrInvoice, $strInvoiceRun)
 	{
		//Debug("Total Owing: {$arrInvoice['TotalOwing']}\n$strInvoiceRun");
		
		// Insert into the database
		$arrWhere = Array();
		$arrWhere['Account']	= $arrInvoice['Account'];
		$arrWhere['InvoiceRun']	= $strInvoiceRun;
		
		$arrInsertData = Array();
		$arrInsertData['TotalOwing']	= $arrInvoice['TotalOwing'];
		
		/*if ($arrInvoice['Account'] == 1000160340)
		{
			die;
		}*/
		
		return TRUE;
		
		//return (bool)$this->_updInvoiceTotalOwing->Execute($arrInsertData, $arrWhere);
	}
	
	//------------------------------------------------------------------------//
	// AddServiceTypeTotal
	//------------------------------------------------------------------------//
	/**
	 * AddServiceTypeTotal()
	 *
	 * Imports an Etech Invoice to the ServiceTypeTotal table
	 *
	 * Imports an Etech Invoice to the ServiceTypeTotal table
	 * 
	 * @param	array	$arrServiceTypeTotal	associative array to be inserted
	 * @param	string	$strInvoiceRun			generated InvoiceRun Id
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function AddServiceTypeTotal($arrServiceTypeTotal, $strInvoiceRun=FALSE)
 	{
		if (!$arrServiceTypeTotal['FNN'])
		{
			return FALSE;
		}
		
		// Get Invoice Run
		if (!$strInvoiceRun)
		{
			$strInvoiceRun = $this->FindInvoiceRun($arrServiceTypeTotal['Invoice']);
			if (!$strInvoiceRun)
			{
				$strInvoiceRun = '';
			}
		}
		
		// Insert into the database
		$arrInsertData['FNN']			= $arrServiceTypeTotal['FNN'];
		$arrInsertData['AccountGroup']	= $arrServiceTypeTotal['AccountGroup'];
		$arrInsertData['Account']		= $arrServiceTypeTotal['Account'];
		$arrInsertData['Service']		= $this->Framework->FindServiceByFNN($arrServiceTypeTotal['FNN'], NULL, $arrInsertData['Account']);
		$arrInsertData['InvoiceRun']	= $strInvoiceRun;
		$arrInsertData['RecordType']	= $arrServiceTypeTotal['RecordType'];
		$arrInsertData['Charge']		= $arrServiceTypeTotal['Charge'];
		$arrInsertData['Units']			= 0;
		$arrInsertData['Records']		= $arrServiceTypeTotal['Records'];
		
		$mixInsertResult = (bool)$this->_insServiceTypeTotal->Execute($arrInsertData);
		if (!$mixInsertResult)
		{
			/*echo $this->_insServiceTypeTotal->Error();
			print_r($arrServiceTypeTotal);
			Die();*/
		}
		return $mixInsertResult;
	}
	
	function UpdateLocalCDRs($arrServiceTypeTotal, $strInvoiceDate, $strInvoiceRun=FALSE)
	{
		if ($arrServiceTypeTotal['RecordType'] == 17)
		{
			$intRecords = (int)$arrServiceTypeTotal['Records'];
			if (!$intRecords)
			{
				return FALSE;
			}
			
			// Get Invoice Run
			if (!$strInvoiceRun)
			{
				$strInvoiceRun = $this->FindInvoiceRun($arrServiceTypeTotal['Invoice']);
				if (!$strInvoiceRun)
				{
					$strInvoiceRun = '';
				}
			}
			
			// Update the CDRs
			$arrWhere = Array();
			$strWhere = "FNN = <FNN> AND Account = <Account> AND RecordType = 17 AND StartDatetime < <Date>";
			$arrUpdateData = Array();
			$arrUpdateData['Status']		= CDR_INVOICED;
			$arrUpdateData['InvoiceRun']	= $strInvoiceRun;
			$updMatchLocal = new StatementUpdate("CDR", $strWhere, $arrUpdateData, $intRecords);	
			$arrServiceTypeTotal['Date'] 	= $strInvoiceDate;
			return $updMatchLocal->Execute($arrUpdateData, $arrServiceTypeTotal);
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// AddServiceTotal
	//------------------------------------------------------------------------//
	/**
	 * AddServiceTotal()
	 *
	 * Imports an Etech Invoice to the ServiceTotal table
	 *
	 * Imports an Etech Invoice to the ServiceTotal table
	 * 
	 * @param	array	$arrServiceTotal	associative array to be inserted
	 * @param	string	$strInvoiceRun		generated InvoiceRun Id
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function AddServiceTotal($arrServiceTotal, $strInvoiceRun=FALSE)
 	{
		// Get Invoice Run
		if (!$strInvoiceRun)
		{
			$strInvoiceRun = $this->FindInvoiceRun($arrServiceTotal['Invoice']);
			if (!$strInvoiceRun)
			{
				$strInvoiceRun = '';
			}
		}
		
		// Insert into the database
		$arrInsertData['FNN']				= $arrServiceTotal['FNN'];
		$arrInsertData['AccountGroup']		= $arrServiceTotal['AccountGroup'];
		$arrInsertData['Account']			= $arrServiceTotal['Account'];
		$arrInsertData['Service']			= $this->Framework->FindServiceByFNN($arrServiceTotal['FNN'], NULL, $arrInsertData['Account']);
		$arrInsertData['InvoiceRun']		= $strInvoiceRun;
		$arrInsertData['CappedCharge']		= $arrServiceTotal['TotalCharge'];
		$arrInsertData['UncappedCharge']	= 0.0;
		$arrInsertData['TotalCharge']		= $arrServiceTotal['TotalCharge'];
		$arrInsertData['Credit']			= 0.0;
		$arrInsertData['Debit']				= $arrServiceTotal['TotalCharge'];
		
		$mixInsertResult = (bool)$this->_insServiceTotal->Execute($arrInsertData);
		if (!$mixInsertResult)
		{
			/*echo $this->_insServiceTypeTotal->Error();
			print_r($arrServiceTotal);
			Die();*/
		}
		return $mixInsertResult;
	}
	
	//------------------------------------------------------------------------//
	// MatchCDR
	//------------------------------------------------------------------------//
	/**
	 * MatchCDR()
	 *
	 * Attempts to match an Etech CDR with one of our own
	 *
	 * Attempts to match an Etech CDR with one of our own.  Changes the CDR status to
	 * CDR_INVOICED if there is a match
	 * 
	 * @param	array	$arrCDR				associative array to be matched
	 * @param	string	$strInvoiceRun		generated InvoiceRun Id
	 *
	 * @return	mixed	int					difference between our charge and etech's
	 *					bool				FALSE if a match was not found (use === FALSE, as this method may return 0)
	 *
	 * @method
	 */
 	function MatchCDR($arrCDR, $strInvoiceRun = NULL)
 	{
		// Try to match
		$arrWhere = $arrCDR;
		$intResults = $this->_selMatchCDR->Execute($arrWhere);
		if ($intResults === FALSE)
		{
			// Error
			return FALSE;
		}
		$arrCDRResult = $this->_selMatchCDR->Fetch();
		
		// Determine status
		$fltDifference = $arrCDRResult['Charge'] - $arrCDR['Charge'];
		if ($fltDifference === (float)0)
		{
			$arrCDR['Status']	= CDR_ETECH_PERFECT_MATCH;
		}
		elseif ($arrCDRResult['Id'])
		{
			$arrCDR['Status']	= CDR_ETECH_IMPERFECT_MATCH;
		}
		else
		{
			$arrCDR['Status']	= CDR_ETECH_NO_MATCH;
		}
		/*
		// Insert CDR into CDREtech table
		$arrCDR['VixenCDR']		= $arrCDRResult['Id'];
		$arrCDR['SequenceNo']	= $arrCDR['_LineNo'];
		$arrCDR['CDR']			= $arrCDR['_OriginalLine'];
		$arrCDR['File']			= $arrCDR['_File'];
		$arrCDR['InvoiceRun']	= $arrCDR['_Status']['InvoiceRun'];
		if ($this->InsertEtechCDR($arrCDR) === FALSE)
		{
			// Error
			return FALSE;
		}*/
		
		$arrReturn = Array();
		$arrReturn['Id']			= $arrCDRResult['Id'];
		$arrReturn['Difference']	= $arrCDRResult['Charge'] - $arrCDR['Charge'];
		$arrReturn['Status']		= $arrCDRResult['Status'];
		
		// return the difference between our charge and etech's
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// FindCDR
	//------------------------------------------------------------------------//
	//TODO!flame! fix doc
	/**
	 * FindCDR()
	 *
	 * Attempts to Find an Etech CDR in our database.
	 *
	 * Attempts to Find an Etech CDR in our database.
	 * 
	 * @param	array	$arrCDR				associative array to be matched
	 *
	 * @return	mixed	int					difference between our charge and etech's
	 *					bool				FALSE if a match was not found (use === FALSE, as this method may return 0)
	 *
	 * @method
	 */
 	function FindCDR($arrCDR)
 	{
		// Try to match
		$arrWhere = $arrCDR;
		$intResults = $this->_selMatchCDR->Execute($arrWhere);
		if (!$intResults)
		{
			// Error or no results
			return FALSE;
		}
		$arrCDRResult = $this->_selMatchCDR->Fetch();
		
		$arrCDRResult['Difference']	= $arrCDRResult['Charge'] - $arrCDR['Charge'];
		
		// return the CDR
		return $arrCDRResult;
	}
	
	function UpdateCDR($arrCDR)
	{
		// check Id
		$arrCDR['Id'] = (int)$arrCDR['Id'];
		if (!$arrCDR['Id'])
		{
			return FALSE;
		}
		
		// Get Invoice Run
		if (!$arrCDR['InvoiceRun'])
		{
			$arrCDR['InvoiceRun'] = $this->FindInvoiceRun($arrCDR['Invoice']);
			if (!$arrCDR['InvoiceRun'])
			{
				$arrCDR['InvoiceRun'] = '';
			}
		}
		
		// update CDR
		$mixResult = $this->_ubiCDR->Execute($arrCDR);
		if (!$mixResult)
		{
			//Debug($this->_ubiCDR->Error());
		}
		return $mixResult;
	}
	
	function UpdateEtechCDR($arrCDR)
	{
		// check Id
		$arrCDR['Id'] = (int)$arrCDR['Id'];
		if (!$arrCDR['Id'])
		{
			return FALSE;
		}
		
		// Get Invoice Run
		if (!$arrCDR['InvoiceRun'])
		{
			$arrCDR['InvoiceRun'] = $this->FindInvoiceRun($arrCDR['Invoice']);
			if (!$arrCDR['InvoiceRun'])
			{
				$arrCDR['InvoiceRun'] = '';
			}
		}
		
		// update CDR
		$mixResult = $this->_ubiEtechCDR->Execute($arrCDR);
		if (!$mixResult)
		{

		}
		return $mixResult;
	}
	
	//------------------------------------------------------------------------//
	// InsertEtechCDR
	//------------------------------------------------------------------------//
	/**
	 * InsertEtechCDR()
	 *
	 * Inserts a CDR into the CDREtech table
	 *
	 * Inserts a CDR into the CDREtech table
	 * 
	 * @param	array	$arrCDR				associative array to be inserted
	 *
	 * @return	mixed	int					Id of the inserted CDR 
	 *					bool				FALSE if a match was not found (use === FALSE, as this method may return 0)
	 *
	 * @method
	 */
 	function InsertEtechCDR($arrCDR)
 	{		
		// Insert CDR into CDREtech table
		$arrCDR['SequenceNo']	= $arrCDR['_LineNo'];
		$arrCDR['CDR']			= $arrCDR['_OriginalLine'];
		$arrCDR['File']			= basename($arrCDR['_File']);
		$arrCDR['InvoiceRun']	= $arrCDR['_Status']['BillingPeriod'];
		
		// return the Id
		if (($mixResult = $this->_insEtechCDR->Execute($arrCDR)) === FALSE)
		{
			//Debug($this->_insEtechCDR->Error());
		}
		return $mixResult;
	}
	
	
	//------------------------------------------------------------------------//
	// FindInvoiceRun
	//------------------------------------------------------------------------//
	/**
	 * FindInvoiceRun()
	 *
	 * Finds the Invoice Run for an invoice in our database.
	 *
	 * Finds the Invoice Run for an invoice in our database.
	 * 
	 * @param	int	$intInvoice				id of the invoice to find
	 *
	 * @return	mixed	string				Invoice Run
	 *					bool				FALSE if a match was not found
	 *
	 * @method
	 */
 	function FindInvoiceRun($intInvoice)
	{
		$intInvoice = (int)$intInvoice;
		
		if ($intInvoice < 1)
		{
			return FALSE;
		}
		
		// return invoice run from cache
		if ($this->_InvoiceRun[$intInvoice] || $this->_InvoiceRun[$intInvoice] === FALSE)
		{
			return $this->_InvoiceRun[$intInvoice];
		}
		
		// find invoice run
		if (!$this->_selInvoiceRun->Execute(Array("Invoice" => $intInvoice)))
		{
			return FALSE;
		}
		$arrInvoiceRun = $this->_selInvoiceRun->Fetch();
		$strInvoiceRun = $arrInvoiceRun['InvoiceRun'];
		
		// cache invoice run
		if (!$strInvoiceRun)
		{
			$strInvoiceRun = FALSE;
		}
		$this->_InvoiceRun[$intInvoice] = $strInvoiceRun;
		
		return $strInvoiceRun;
	}
 }	
?>
