<?php

require_once dirname(__FILE__) . '/data/Data_Source.php';

class CDR extends ORM
{
	protected	$_strTableName	= "CDR";
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the object with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * updateQuarantineStatus()
	 *
	 * Updates the Quarantine Status for this CDR
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function updateQuarantineStatus()
	{
		// Determine what original CDR this is tied to, and retrieve it
		// TODO
		
		switch ($this->Status)
		{
			case CDR_RECHARGE:
				break;
			
			case CDR_CREDIT_QUARANTINE:
				break;
			
			default:
				// This is not a Quarantined CDR, return nicely
				return;
		}
	}
	
	/**
	 * getForInvoice
	 *
	 * Returns all CDRs for the given invoice
	 * 
	 * @param	mixed	$mxdInvoice		integer			: Invoice Id representing the invoice in question
	 * 									Invoice object	: Representing the invoice in question
	 * 
	 * @return	array					array of associative arrays representing the cdr records
	 * @method
	 */
	public static function getForInvoice($mxdInvoice)
	{
		// Invoiced CDRs can either be in the CDR database, or the flex one.
		// The CDR database has one table for the invoiced CDRs, but the table is partitioned
		// by the invoice_run_id of the invoice. To query this table we MUST include the
		// invoice_run_id in the where clause of the query.

		$objInvoice = is_numeric($mxdInvoice)? new Invoice(array('Id'=>$mxdInvoice), true) : $mxdInvoice;
		
		$dataSource = self::getDataSourceForInvoiceRunCDRs($objInvoice->invoiceRunId);

		// We now have all the details needed to load the CDRs from either database
		
		// Try to load the records from the cdr_invoiced table of the CDR db
		$cdrDb = Data_Source::get($dataSource);
		if ($dataSource == FLEX_DATABASE_CONNECTION_CDR)
		{
			$strCdrSelect = 
				'SELECT id as "Id", fnn as "FNN", file as "File", carrier as "Carrier", carrier_ref as "CarrierRef", source as "Source", destination as "Destination", start_date_time as "StartDatetime", end_date_time as "EndDatetime", units as "Units", account_group as "AccountGroup", account as "Account", service as "Service", cost as "Cost", status as "Status", cdr as "CDR", description as "Description", destination_code as "DestinationCode", record_type as "RecordType", service_type as "ServiceType", charge as "Charge", rate as "Rate", normalised_on as "NormalisedOn", rated_on as "RatedOn", invoice_run_id, sequence_no as "SequenceNo", credit as "Credit" ' .
				"  FROM cdr_invoiced " .
				" WHERE account = " . $cdrDb->quote($objInvoice->account) . 
				"   AND invoice_run_id = " . $cdrDb->quote($objInvoice->invoiceRunId) .
				" ORDER BY service_type ASC, fnn ASC, record_type ASC, start_date_time ASC";
		}
		else
		{
			// Must be in CDR table of default db
			$strCdrSelect = 
				'SELECT Id as "Id", FNN as "FNN", File as "File", Carrier as "Carrier", CarrierRef as "CarrierRef", Source as "Source", Destination as "Destination", StartDatetime as "StartDatetime", EndDatetime as "EndDatetime", Units as "Units", AccountGroup as "AccountGroup", Account as "Account", Service as "Service", Cost as "Cost", Status as "Status", CDR as "CDR", Description as "Description", DestinationCode as "DestinationCode", RecordType as "RecordType", ServiceType as "ServiceType", Charge as "Charge", Rate as "Rate", NormalisedOn as "NormalisedOn", RatedOn as "RatedOn", invoice_run_id, SequenceNo as "SequenceNo", Credit as "Credit" ' .
				"  FROM CDR " .
				" WHERE Account = " . $cdrDb->quote($objInvoice->account) . 
				"   AND invoice_run_id = " . $cdrDb->quote($objInvoice->invoiceRunId) .
				" ORDER BY ServiceType ASC, FNN ASC, RecordType ASC, StartDatetime ASC";
		}

		// Proceed with a query...
		$res =& $cdrDb->query($strCdrSelect);

		// Always check that result is not an error
		if (PEAR::isError($res)) {
			throw new Exception($res->getMessage() . "\n$strCdrSelect");
		}
		
		$rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

		// Otherwise, we should assume that there weren't any.
		return $rows;
	}
	
	/**
	 * getDataSourceForInvoiceRunCDRs
	 *
	 * Returns the name of the data source which currently stores the CDRs for the invoice run specified
	 * The way it does this is, if it finds at least 1 cdr in the flex data source referencing this invoice run, then it assumes all the cdrs are in the flex
	 * data source, else it will assume the CDRs are in the cdr data source (but won't actually test this) 
	 * 
	 * @param	int		$intInvoiceRunId	The invoice run id for the CDRs to find
	 * 
	 * @return	string						either FLEX_DATABASE_CONNECTION_DEFAULT or FLEX_DATABASE_CONNECTION_CDR
	 * @method
	 */
	public static function getDataSourceForInvoiceRunCDRs($intInvoiceRunId)
	{
		$strQuery	= "SELECT CASE WHEN (SELECT 'Still In CDR table' FROM CDR WHERE invoice_run_id = $intInvoiceRunId LIMIT 1) = 'Still In CDR table' THEN '". FLEX_DATABASE_CONNECTION_DEFAULT ."' ELSE '". FLEX_DATABASE_CONNECTION_CDR ."' END AS DataSource";
		$db			= Data_Source::get();
		$res		= $db->query($strQuery);
		
		if (PEAR::isError($res))
		{
			throw new Exception("Failed to find the data source storing CDRs for invoice run: $intInvoiceRunId - " . $res->getMessage());
		}
		
		$strDataSourceName = $res->fetchOne();
		
		return $strDataSourceName;
	}

	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Note", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Note");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Note");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>
