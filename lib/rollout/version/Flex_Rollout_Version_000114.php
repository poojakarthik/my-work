<?php

/**
 * Version 114 of database update.
 * This version: -
 *	1:	Add telemarketing_fnn_blacklist_nature Table
 *	2:	Populate telemarketing_fnn_blacklist_nature Table
 *
 *	3:	Add telemarketing_fnn_blacklist Table
 *
 *	4:	Add telemarketing_fnn_dialled_result Table
 *
 *	5:	Add telemarketing_fnn_dialled Table
 *
 *	6:	Add telemarketing_fnn_proposed_status Table
 *	7:	Populate telemarketing_fnn_proposed_status Table
 *
 *	8:	Add telemarketing_fnn_withheld_reason Table
 *	9:	Populate telemarketing_fnn_withheld_reason Table
 *
 *	10:	Add telemarketing_fnn_proposed Table
 */

class Flex_Rollout_Version_000114 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add telemarketing_fnn_blacklist_nature Table
		$strSQL = "CREATE TABLE IF NOT EXISTS `telemarketing_fnn_blacklist_nature` (
						`id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique Identifier',
						`name` varchar(255) NOT NULL COMMENT 'Short Name for the Nature',
						`description` varchar(1024) NOT NULL COMMENT 'Long Description for the Nature',
						`const_name` varchar(512) NOT NULL COMMENT 'Constant Name',
						PRIMARY KEY  (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_blacklist_nature Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_blacklist_nature;";
		
		// 2:	Populate telemarketing_fnn_blacklist_nature Table
		$strSQL = "INSERT INTO telemarketing_fnn_blacklist_nature (name, description, const_name) VALUES " .
					"('Opt-Out'					, 'Internal Opt-Out'		, 'TELEMARKETING_FNN_BLACKLIST_NATURE_OPTOUT'), " .
					"('Do Not Call Register'	, 'Do Not Call Register'	, 'TELEMARKETING_FNN_BLACKLIST_NATURE_DNCR');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate telemarketing_fnn_blacklist_nature Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE telemarketing_fnn_blacklist_nature;";
		
		// 3:	Add telemarketing_fnn_blacklist Table
		$strSQL = "CREATE TABLE IF NOT EXISTS `telemarketing_fnn_blacklist` (
						`id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique Identifier',
						`fnn` varchar(20) NOT NULL COMMENT 'Service Number',
						`cached_on` datetime NOT NULL COMMENT 'Date the blacklisting comes into effect',
						`expired_on` datetime NOT NULL COMMENT 'Date the blacklisting expires',
						`telemarketing_fnn_blacklist_nature_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Nature of this Blacklisting',
						
						CONSTRAINT pk_telemarketing_fnn_blacklist PRIMARY KEY (id),
						CONSTRAINT fk_telemarketing_fnn_blacklist_nature_id FOREIGN KEY (telemarketing_fnn_blacklist_nature_id) REFERENCES telemarketing_fnn_blacklist_nature(id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_blacklist Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_blacklist;";
		
		// 4:	Add telemarketing_fnn_dialled_result Table
		$strSQL = "CREATE TABLE IF NOT EXISTS telemarketing_fnn_dialled_result (
						id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique Identifier',
						`name` varchar(255) NOT NULL COMMENT 'Short Name for the Result',
						description varchar(1024) NOT NULL COMMENT 'Long Description for the Result',
						const_name varchar(512) NOT NULL COMMENT 'Constant Name',
						
						CONSTRAINT pk_telemarketing_fnn_dialled_result PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_dialled_result Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_dialled_result;";
		
		// 5:	Add telemarketing_fnn_dialled Table
		$strSQL = "CREATE TABLE IF NOT EXISTS telemarketing_fnn_dialled (
						id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique Identifier',
						fnn varchar(20) NOT NULL COMMENT 'The Service Number Dialled',
						customer_group_id bigint(20) NOT NULL COMMENT '(FK) The Customer Group represented',
						file_import_id bigint(20) unsigned NOT NULL COMMENT '(FK) Dialler Report this was imported from',
						dealer_id bigint(20) unsigned NOT NULL COMMENT '(FK) Dealer who made the call',
						dialled_by varchar(512) default NULL COMMENT 'Salesperson who made the call',
						dialled_on datetime NOT NULL COMMENT 'When the call was made',
						telemarketing_fnn_dialled_result_id bigint(20) unsigned NOT NULL COMMENT '(FK) The Result of the Call',
						
						CONSTRAINT pk_telemarketing_fnn_dialled PRIMARY KEY (id), 
						
						CONSTRAINT fk_telemarketing_fnn_dialled_customer_group_id					FOREIGN KEY (customer_group_id)						REFERENCES CustomerGroup(Id)					ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT fk_telemarketing_fnn_dialled_file_import_id						FOREIGN KEY (file_import_id)						REFERENCES FileImport(Id)						ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT fk_telemarketing_fnn_dialled_dealer_id							FOREIGN KEY (dealer_id)								REFERENCES dealer(id)							ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT fk_telemarketing_fnn_dialled_telemarketing_fnn_dialled_result_id	FOREIGN KEY (telemarketing_fnn_dialled_result_id)	REFERENCES telemarketing_fnn_dialled_result(id)	ON UPDATE CASCADE 	ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_dialled Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_dialled;";
		
		// 6:	Add telemarketing_fnn_status Table
		$strSQL = "CREATE TABLE IF NOT EXISTS telemarketing_fnn_status (
						id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique Identifier',
						`name` varchar(255) NOT NULL COMMENT 'Short Name for the Status',
						description varchar(1024) NOT NULL COMMENT 'Long Description for the Status',
						const_name varchar(512) NOT NULL COMMENT 'Constant Name',
						
						CONSTRAINT pk_telemarketing_fnn_status PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_status Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_status;";
		
		// 7:	Populate telemarketing_fnn_proposed_status Table
		$strSQL = "INSERT INTO telemarketing_fnn_proposed_status (name, description, const_name) VALUES " .
					"('Imported'	, 'Imported'	, 'TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED'), " .
					"('Withheld'	, 'Withheld'	, 'TELEMARKETING_FNN_PROPOSED_STATUS_WITHHELD'), " .
					"('Exported'	, 'Exported'	, 'TELEMARKETING_FNN_PROPOSED_STATUS_EXPORT');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate telemarketing_fnn_proposed_status Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE telemarketing_fnn_proposed_status;";
		
		// 8:	Add telemarketing_fnn_withheld_reason Table
		$strSQL = "CREATE TABLE IF NOT EXISTS telemarketing_fnn_withheld_reason (
						id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique Identifier',
						`name` varchar(255) NOT NULL COMMENT 'Short Name for the Reason',
						description varchar(1024) NOT NULL COMMENT 'Long Description for the Reason',
						const_name varchar(512) NOT NULL COMMENT 'Constant Name',
						
						CONSTRAINT pk_telemarketing_fnn_withheld_reason PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_withheld_reason Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_withheld_reason;";
		
		// 9:	Populate telemarketing_fnn_withheld_reason Table
		$strSQL = "INSERT INTO telemarketing_fnn_withheld_reason (name, description, const_name) VALUES " .
					"('Do Not Call Register'	, 'Do Not Call Register'	, 'TELEMARKETING_FNN_WITHHELD_REASON_DNCR'), " .
					"('Opt-Out'					, 'Internal Opt-Out'		, 'TELEMARKETING_FNN_WITHHELD_REASON_OPTOUT'), " .
					"('Tolling'					, 'Currently Tolling'		, 'TELEMARKETING_FNN_WITHHELD_REASON_TOLLING'), " .
					"('Call Period Conflict'	, 'Call Period Conflict'	, 'TELEMARKETING_FNN_WITHHELD_REASON_CALL_PERIOD_CONFLICT');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate telemarketing_fnn_withheld_reason Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE telemarketing_fnn_withheld_reason;";
		
		// 10:	Add telemarketing_fnn_proposed Table
		$strSQL = "CREATE TABLE IF NOT EXISTS telemarketing_fnn_proposed (
						id bigint(20) unsigned NOT NULL COMMENT 'Unique Identifier',
						fnn varchar(20) NOT NULL COMMENT 'Service Number',
						customer_group_id bigint(20) NOT NULL COMMENT '(FK) Customer Group which will be represented in the pitch',
						proposed_list_file_import_id bigint(20) unsigned NOT NULL COMMENT '(FK) The Proposed Dialling List File this was Imported from',
						do_not_call_file_export_id bigint(20) unsigned default NULL COMMENT '(FK) The DNCR Washing File this was exported to',
						permitted_list_file_export_id bigint(20) unsigned default NULL COMMENT '(FK) The Permitted Dialling List this was Exported to',
						call_period_start datetime NOT NULL COMMENT 'The Earliest Date this FNN can be called',
						call_period_end datetime NOT NULL COMMENT 'The Latest Date this FNN can be called',
						dealer_id bigint(20) unsigned NOT NULL COMMENT '(FK) The Dealer who requested this FNN',
						telemarketing_fnn_proposed_status_id bigint(20) unsigned NOT NULL COMMENT '(FK) The Status of this FNN Request',
						telemarketing_fnn_withheld_reason_id bigint(20) unsigned default NULL COMMENT '(FK) The Reason why this FNN was withheld',
						raw_record varchar(4092) NOT NULL COMMENT 'Raw record data from the source file',
						
						CONSTRAINT pk_telemarketing_fnn_proposed PRIMARY KEY (id), 
						
						CONSTRAINT fk_telemarketing_fnn_proposed_customer_group_id						FOREIGN KEY (customer_group_id)						REFERENCES CustomerGroup(Id)						ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT fk_telemarketing_fnn_proposed_proposed_list_file_import_id			FOREIGN KEY (proposed_list_file_import_id)			REFERENCES FileImport(Id)							ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT fk_telemarketing_fnn_proposed_do_not_call_file_export_id				FOREIGN KEY (do_not_call_file_export_id)			REFERENCES FileExport(Id)							ON UPDATE CASCADE	ON DELETE SET NULL,
						CONSTRAINT fk_telemarketing_fnn_proposed_permitted_list_file_export_id			FOREIGN KEY (permitted_list_file_export_id)			REFERENCES FileExport(Id)							ON UPDATE CASCADE	ON DELETE SET NULL,
						CONSTRAINT fk_telemarketing_fnn_proposed_dealer_id								FOREIGN KEY (dealer_id)								REFERENCES dealer(id)								ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT fk_telemarketing_fnn_proposed_telemarketing_fnn_proposed_status_id	FOREIGN KEY (telemarketing_fnn_proposed_status_id)	REFERENCES telemarketing_fnn_proposed_status(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT fk_telemarketing_fnn_proposed_telemarketing_fnn_withheld_reason_id	FOREIGN KEY (telemarketing_fnn_withheld_reason_id)	REFERENCES telemarketing_fnn_withheld_reason(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add telemarketing_fnn_withheld_reason Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE telemarketing_fnn_withheld_reason;";
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