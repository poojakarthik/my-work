<?php

require_once dirname(__FILE__) . '/data/Data_Source.php';

class CDR
{
	public static function getForInvoice($mxdInvoice)
	{
		// Invoiced CDRs will be in the CDR database.
		// The CDR database has one table for the invoiced CDRs, but the table is partitioned
		// by the invoice_run_id of the invoice. To query this table we MUST include the
		// invoice_run_id in the where clause of the query.

		if (is_numeric($mxdInvoice))
		{
			$invoiceId = $mxdInvoice;
			DBO()->ThisInvoice->Id = $mxdInvoice;
			DBO()->ThisInvoice->Load();
			$accountId = $mxdInvoice->Account->Value;
			$invoiceRunId = $mxdInvoice->invoice_run_id->Value;
		}
		else
		{
			$invoiceId = $mxdInvoice->Id->Value;
			$accountId = $mxdInvoice->Account->Value;
			$invoiceRunId = $mxdInvoice->invoice_run_id->Value;
		}

		if (!is_numeric($invoiceId))
		{
			$invoiceId = intval($invoiceId);
		}
		if (!is_numeric($invoiceRunId))
		{
			$invoiceRunId = intval($invoiceRunId);
		}

		// We now have all the details needed to load the CDRs from either database
		
		
		// Try to load the records from the cdr_invoiced table of the CDR db
		$cdrDb = Data_Source::get(FLEX_DATABASE_CONNECTION_CDR);
		$strCdrSelect = 
				'SELECT id as "Id", fnn as "FNN", file as "File", carrier as "Carrier", carrier_ref as "CarrierRef", source as "Source", destination as "Destination", start_date_time as "StartDatetime", end_date_time as "EndDatetime", units as "Units", account_group as "AccountGroup", account as "Account", service as "Service", cost as "Cost", status as "Status", cdr as "CDR", description as "Description", destination_code as "DestinationCode", record_type as "RecordType", service_type as "ServiceType", charge as "Charge", rate as "Rate", normalised_on as "NormalisedOn", rated_on as "RatedOn", invoice_run_id, sequence_no as "SequenceNo", credit as "Credit" ' .
				"  FROM cdr_invoiced " .
				" WHERE account = " . $cdrDb->quote($accountId) . 
				"   AND invoice_run_id = " . $cdrDb->quote($invoiceRunId);

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


}



?>
