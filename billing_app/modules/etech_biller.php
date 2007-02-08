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
		
		$arrUpdateData['Status']		= NULL;
		$arrUpdateData['InvoiceRun']	= NULL;
		$arrUpdateData['Id']			= NULL;
		$this->_ubiMatchCDR	= new StatementUpdateById("CDR", $arrUpdateData);
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
													/*"Status != 199 AND " .*/
													"FNN = <FNN> AND " .
													"Account = <Account> AND " .
													"Units = <Units> AND " .
													"StartDatetime = <StartDatetime> AND " .
													"Credit = 0",
													NULL,
													2);
		
		
		$this->_selMatchLocal = new StatementSelect("CDR",
													"SUM(Charge) AS Total",
													"Account = <Account> AND " .
													"StartDatetime BETWEEN <StartDatetime> AND <EndDatetime>");
			
		$this->_insEtechCDR = new StatementInsert("CDREtech");
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
 	function AddInvoice($arrInvoice, $strInvoiceRun)
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
	 * @param	string	$strInvoiceCreatedOn	date the invoice was created (from Invoice dataset)
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function AddServiceTypeTotal($arrServiceTypeTotal, $strInvoiceRun, $strInvoiceCreatedOn)
 	{
		// Insert into the database
		$arrInsertData['FNN']			= $arrServiceTypeTotal['FNN'];
		$arrInsertData['AccountGroup']	= $arrServiceTypeTotal['AccountGroup'];
		$arrInsertData['Account']		= $arrServiceTypeTotal['Account'];
		$arrInsertData['Service']		= $this->FindServiceByFNN($arrServiceTypeTotal['FNN'], $arrInsertData['Account']);
		$arrInsertData['InvoiceRun']	= $strInvoiceRun;
		$arrInsertData['RecordType']	= $arrServiceTypeTotal['RecordType'];
		$arrInsertData['Charge']		= $arrServiceTypeTotal['Charge'];
		$arrInsertData['Units']			= 0;
		$arrInsertData['Records']		= $arrServiceTypeTotal['Records'];
		
		$mixInsertResult = (bool)$this->_insServiceTypeTotal->Execute($arrInsertData);
		
		// Match with all Local CDRs
		switch ($arrServiceTypeTotal['RecordType'])
		{
			case 17:
			//case 34: // <-- not sure if these are itemised on the etech bills? they probably should be
				// Find the CDRs total
				$strStartDate	= date("Y-m-", strtotime("-1 Month", strtotime($strInvoiceCreatedOn)))."01";
				$strEndDate		= date("Y-m-d", strtotime("-1 Day", strtotime("+1 Month", strtotime($strStartDate))));
				$arrWhere['Account']	= $arrServiceTypeTotal['Account'];
				$arrWhere['StartDate']	= $strStartDate." 00:00:00";
				$arrWhere['EndDate']	= $strEndDate." 23:59:59";
				if (!$this->_selMatchLocal->Execute($arrWhere))
				{
					// Error or no matches
					return FALSE;
				}
				$mixInsertResult = $this->_selMatchLocal->Fetch();
				$mixInsertResult = $mixInsertResult['Total'];
				
				// Update the CDRs
				$arrUpdateData['Status']		= CDR_INVOICED;
				$arrUpdateData['InvoiceRun']	= $strInvoiceRun;
				$ubiMatchLocal = new StatementUpdate("CDR", $arrWhere, $arrUpdateData, $arrServiceTypeTotal['Records']);	
				$ubiMatchLocal->Execute($arrUpdateData);
		}		
		return $mixInsertResult;
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
 	function AddServiceTotal($arrServiceTotal, $strInvoiceRun)
 	{
		// Insert into the database
		$arrInsertData['FNN']				= $arrServiceTotal['FNN'];
		$arrInsertData['AccountGroup']		= $arrServiceTotal['AccountGroup'];
		$arrInsertData['Account']			= $arrServiceTotal['Account'];
		$arrInsertData['Service']			= $this->FindServiceByFNN($arrServiceTotal['FNN'], $arrInsertData['Account']);
		$arrInsertData['InvoiceRun']		= $strInvoiceRun;
		$arrInsertData['CappedCharge']		= $arrServiceTotal['TotalCharge'];
		$arrInsertData['UncappedCharge']	= 0.0;
		$arrInsertData['TotalCharge']		= $arrServiceTotal['TotalCharge'];
		$arrInsertData['Credit']			= 0.0;
		$arrInsertData['Debit']				= $arrServiceTotal['TotalCharge'];
		
		return (bool)$this->_insServiceTotal->Execute($arrInsertData);
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
		
		$arrReturn = Array();
		$arrReturn['Id']			= $arrCDRResult['Id'];
		$arrReturn['Difference']	= $arrCDRResult['Charge'] - $arrCDR['Charge'];
		$arrReturn['Status']		= $arrCDRResult['Status'];
		
		// return the difference between our charge and etech's
		return $arrReturn;
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
		$arrCDR['File']			= $arrCDR['_File'];
		$arrCDR['InvoiceRun']	= $arrCDR['_Status']['BillingPeriod'];
		
		// return the difference between our charge and etech's
		if (($mixResult = $this->_insEtechCDR->Execute($arrCDR)) === FALSE)
		{
			Debug($this->_insEtechCDR->Error());
		}
		return $this->_insEtechCDR->Execute($arrCDR);
	}
 }	
?>
