<?php

/**
 * Version 56 of database update.
 * This version: -
 *	1:	Builds database structure for customer faq search engine
 */

class Flex_Rollout_Version_000056 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "CREATE TABLE IF NOT EXISTS `customer_faq` (
  `customer_faq_id` mediumint(9) NOT NULL auto_increment,
  `customer_faq_subject` varchar(255) NOT NULL,
  `customer_faq_contents` text NOT NULL,
  `customer_faq_time_added` varchar(20) NOT NULL,
  `customer_faq_time_updated` varchar(20) NOT NULL COMMENT 'to show user when information was last updated',
  `customer_faq_download` blob NOT NULL COMMENT 'e.g. pdf, terms&conditions file, etc',
  `customer_faq_group` varchar(10) NOT NULL COMMENT 'e.g. protalk, voicetalk.. so we only display for the right cust group',
  `customer_faq_hits` mediumint(11) NOT NULL default '0' COMMENT 'so we can list top 10',
  PRIMARY KEY  (`customer_faq_id`)
);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create table. ' . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "DROP TABLE `customer_faq`;";
	}

	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
