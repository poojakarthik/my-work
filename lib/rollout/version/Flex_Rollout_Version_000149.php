<?php

/**
 * Version 149 of database update.
 * This version: -
 *
 *	1:	Add Plan Brochure/Sripts, Interim/Final Invoice, Telemarketing, and Contract Management Flex Modules
 */

class Flex_Rollout_Version_000149 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add Plan Brochure/Sripts, Interim/Final Invoice, Telemarketing, and Contract Management Flex Modules
		$strSQL = "	INSERT INTO flex_module (id, name, description, const_name, active) VALUES" .
				"	(8, 'Plan Brochures', 'Plan Brochures Module', 'FLEX_MODULE_PLAN_BROCHURE', 0), " .
				"	(9, 'Authorisation Scripts', ' Plan Change Voice Authorisation Scripts Module', 'FLEX_MODULE_PLAN_AUTH_SCRIPT', 0), " .
				"	(10, 'Interim Invoices', 'Interim Invoices Module', 'FLEX_MODULE_INVOICE_INTERIM', 0), " .
				"	(11, 'Final Invoices', 'Final Invoices Module', 'FLEX_MODULE_INVOICE_FINAL', 0), " .
				"	(12, 'Telemarketing', 'Telemarketing Module', 'FLEX_MODULE_TELEMARKETING', 0), " .
				"	(13, 'Contract Management', 'Contract Management Module', 'FLEX_MODULE_CONTRACT_MANAGEMENT', 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Plan Brochure/Sripts, Interim/Final Invoice, Telemarketing, and Contract Management Flex Modules. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DELETE FROM flex_module " .
								"WHERE const_name IN ('FLEX_MODULE_PLAN_BROCHURE', 'FLEX_MODULE_PLAN_AUTH_SCRIPT', 'FLEX_MODULE_INVOICE_INTERIM', 'FLEX_MODULE_INVOICE_FINAL', 'FLEX_MODULE_TELEMARKETING', 'FLEX_MODULE_CONTRACT_MANAGEMENT');";
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>