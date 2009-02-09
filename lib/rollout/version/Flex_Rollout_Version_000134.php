<?php

/**
 * Version 134 of database update.
 * This version: -
 *	1:	Add the RatePlan.brochure_document_id and auth_script_document_id Fields
 */

class Flex_Rollout_Version_000134 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the RatePlan.brochure_document_id and auth_script_document_id Fields
		$strSQL =	"ALTER TABLE RatePlan " .
					"ADD brochure_document_id		BIGINT(20) UNSIGNED NULL COMMENT '(FK) Brochure for this Plan'," .
					"ADD auth_script_document_id	BIGINT(20) UNSIGNED NULL COMMENT '(FK) Authorisation Script for this Plan'," .
					"ADD CONSTRAINT fk_rate_plan_brochure_document_id FOREIGN KEY (brochure_document_id) REFERENCES document(id) ON UPDATE CASCADE ON DELETE SET NULL," .
					"ADD CONSTRAINT fk_rate_plan_auth_script_document_id FOREIGN KEY (auth_script_document_id) REFERENCES document(id) ON UPDATE CASCADE ON DELETE SET NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to the RatePlan.brochure_document_id and auth_script_document_id Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE " .
								"DROP FOREIGN KEY fk_rate_plan_auth_script_document_id, " .
								"DROP FOREIGN KEY fk_rate_plan_brochure_document_id, " .
								"DROP auth_script_document_id, " .
								"DROP brochure_document_id;";
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