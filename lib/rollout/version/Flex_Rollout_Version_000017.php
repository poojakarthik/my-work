<?php

/**
 * Version 17 of database update.
 * This version: -
 *	1:	Add email_notification entries for ticketing system
 *	2:	Create ticketing system tables
 *	3:	Populate ticketing system table with initial data
 *	4:	Create views for getting the current service for an account
 */

class Flex_Rollout_Version_000017 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Create email_notification Table
		$strSQL = " INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails)
					VALUES
					('Ticketeting System', 	'Messages sent from ticketing system', 'EMAIL_NOTIFICATION_TICKETING_SYSTEM', 	1),
					('Ticketeting System Admin Message', 	'Messages sent from ticketing system administration message', 'EMAIL_NOTIFICATION_TICKETING_SYSTEM_ADMIN_MESSAGE', 	0)
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate email_notification table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name IN ('EMAIL_NOTIFICATION_TICKETING_SYSTEM', 'EMAIL_NOTIFICATION_TICKETING_SYSTEM_ADMIN_MESSAGE')";


		$strTableSQLs = array(

			"ticketing_config" => "	CREATE TABLE ticketing_config (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										protocol varchar(50) NOT NULL DEFAULT 'Pop3' COMMENT 'Currently only POP3, IMAP, MBOX and MailDir are supported',
										host varchar(255) NOT NULL COMMENT 'Host machine (POP3 or IMAP) or directory path (MBOX or MailDir)',
										port BIGINT(20) DEFAULT NULL COMMENT 'Port for mail retrieval on host machine (NULL uses default port)',
										username varchar(50) DEFAULT NULL COMMENT 'Username to use when retrieving emails (or backup dir for XML files)',
										password varchar(50) DEFAULT NULL COMMENT 'Password (encrypted) to use when retrieving emails (or dir for junk XML files)',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Configuration setting for the ticketing system';
									",

			"ticketing_status" => "	CREATE TABLE ticketing_status (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the status',
										description varchar(255) NOT NULL COMMENT 'Description of the status',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket workflow statuses';
									",

			"ticketing_priority" => " CREATE TABLE ticketing_priority (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the priority',
										description varchar(255) NOT NULL COMMENT 'Description of the priority',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket priorities';
									",

			"ticketing_correspondance_delivery_status" => "	CREATE TABLE ticketing_correspondance_delivery_status (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the status',
										description varchar(255) NOT NULL COMMENT 'Description of the status',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The delivery status of a ticketing system correspondance';
									",

			"ticketing_correspondance_source" => "	CREATE TABLE ticketing_correspondance_source (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the source',
										description varchar(255) NOT NULL COMMENT 'Description of the source',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The source of a ticketing system correspondance';
									",

			"ticketing_user_permission" => " CREATE TABLE ticketing_user_permission (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the permission',
										description varchar(255) NOT NULL COMMENT 'Description of the permission',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User permission level (user or admin)';
									",

			"ticketing_category" => " CREATE TABLE ticketing_category (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the category',
										description varchar(255) NOT NULL COMMENT 'Description of the category',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Categories of ticketing system tickets';
									",

			"ticketing_contact" => " CREATE TABLE ticketing_contact (
										id bigint(20) unsigned NOT NULL auto_increment,
										title char(4) DEFAULT NULL,
										first_name varchar(255) DEFAULT NULL,
										last_name varchar(255) DEFAULT NULL,
										job_title varchar(255) DEFAULT NULL,
										email varchar(255) DEFAULT NULL,
										phone char(25) DEFAULT NULL,
										mobile char(25) DEFAULT NULL,
										fax char(25) DEFAULT NULL,
										status tinyint(1) NOT NULL COMMENT 'FK to active_status',
										auto_reply tinyint(1) NOT NULL COMMENT 'FK to active_status',
										PRIMARY KEY(Id),
										KEY first_name (first_name),
										KEY last_name (last_name),
										KEY email (email),
										KEY phone (phone),
										KEY mobile (mobile),
										KEY fax (fax),
										KEY status (status)
										) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Customer contacts in the ticketing system';
									",

			"ticketing_contact_account" => " CREATE TABLE ticketing_contact_account (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										ticketing_contact_id bigint(20) NOT NULL COMMENT 'FK to ticketing_contact table',
										account_id bigint(20) NOT NULL COMMENT 'FK to Account table',
										PRIMARY KEY(Id)
										) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Accounts associated with ticketing contact';
									",

			"ticketing_user" => " CREATE TABLE ticketing_user (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										employee_id bigint(20) unsigned NOT NULL COMMENT 'FK to the Employee table',
										permission_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_user_permission table',
										PRIMARY KEY (id),
										UNIQUE KEY employee_id (employee_id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Categories of ticketing system tickets';
									",

			"ticketing_ticket" => " CREATE TABLE ticketing_ticket (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										group_ticket_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to ticket that this ticket belongs to',
										subject varchar(255) NOT NULL COMMENT 'Name of the category',
										priority_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_priority table',
										owner_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_user table',
										contact_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_contact table; Primary contact for ticket.',
										status_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_status table',
										customer_group_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
										account_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the Account table',
										category_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_category table',
										creation_datetime datetime default NULL COMMENT 'Date/Time that the ticket was created',
										modified_datetime datetime default NULL COMMENT 'Date/Time that the ticket was modified',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tickets in the ticketing system';
									",

			"ticketing_ticket_contact" =>  " CREATE TABLE ticketing_ticket_contact (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										ticket_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to ticketing_ticket',
										contact_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to ticketing_contact',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contacts for tickets in the ticketing system';
									",

			"ticketing_ticket_history" => " CREATE TABLE ticketing_ticket_history (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										ticket_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticket table',
										group_ticket_id bigint(20) unsigned NOT NULL COMMENT 'FK to ticket that this ticket belongs to',
										subject varchar(50) NOT NULL COMMENT 'Name of the category',
										priority_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_priority table',
										owner_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_user table',
										contact_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_contact table',
										status_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_status table',
										customer_group_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
										account_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the Account table',
										category_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_category table',
										creation_datetime datetime default NULL COMMENT 'Date/Time that the ticket was created',
										modified_datetime datetime default NULL COMMENT 'Date/Time that the ticket was modified',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='History of tickets in the ticketing system';
									",

			"ticketing_ticket_service" => " CREATE TABLE ticketing_ticket_service (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										ticket_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_ticket table',
										service_id bigint(20) unsigned NOT NULL COMMENT 'FK to the Service table',
										PRIMARY KEY (id),
										UNIQUE KEY ticket_service (ticket_id, service_id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Services associated with tickets in the ticketing system';
									",

			"ticketing_correspondance" => " CREATE TABLE ticketing_correspondance (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										ticket_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_ticket table',
										summary varchar(255) NOT NULL COMMENT 'Summary of correspondance (subject)',
										details mediumtext NOT NULL COMMENT 'Correspondance details (message)',
										user_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_user table (null if created by contact)',
										contact_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_contact table(null if created by user)',
										customer_group_email_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_customer_group_email table',
										source_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_correspondance_source table',
										delivery_status_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_correspondance_delivery_status table',
										creation_datetime datetime default NULL COMMENT 'Date/Time that the correspondance was received/created',
										delivery_datetime datetime default NULL COMMENT 'Date/Time that the correspondance was received/delivered',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Correspondances for tickets in the ticketing system';
									",

			"ticketing_attachment_blacklist_status" => "	CREATE TABLE ticketing_attachment_blacklist_status (
										id bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
										name varchar(50) NOT NULL COMMENT 'Name of the status',
										description varchar(255) NOT NULL COMMENT 'Description of the status',
										const_name varchar(255) NOT NULL COMMENT 'The constant name',
										PRIMARY KEY (id),
										UNIQUE KEY const_name (const_name)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket attachment blacklist statuses';
									",

			"ticketing_attachment_type" => " CREATE TABLE ticketing_attachment_type (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										extension varchar(255) NOT NULL COMMENT 'File extension',
										mime_type varchar(255) NOT NULL COMMENT 'MIME type',
										blacklist_status_id smallint(5) unsigned NOT NULL COMMENT 'FK to the ticketing_attachment_blacklist_status table',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticketing attachment file type';
									",

			"ticketing_attachment" => " CREATE TABLE ticketing_attachment (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										correspondance_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_correspondance table',
										file_name varchar(255) NOT NULL COMMENT 'Name of the file',
										attachment_type_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_attachment_type table',
										file_content mediumblob NOT NULL COMMENT 'The binary contents of the attachment file',
										blacklist_override smallint(5) unsigned DEFAULT NULL COMMENT 'FK to the active_status table',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attachments to correspondances in the ticketing system';
									",

			"ticketing_customer_group_config" => " CREATE TABLE ticketing_customer_group_config (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										customer_group_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
										acknowledge_email_receipts smallint(5) unsigned NOT NULL DEFAULT 2 COMMENT 'FK to active_status; Whether or not to acknowledge email receipts',
										email_receipt_acknowledgement mediumtext DEFAULT NULL COMMENT 'The body of the email sent to acknowledge an email receipt',
										default_email_id bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_customer_group_email table',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attachments to correspondances in the ticketing system';
									",

			"ticketing_customer_group_email" => " CREATE TABLE ticketing_customer_group_email (
										id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the table',
										customer_group_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
										email varchar(255) DEFAULT NULL COMMENT 'Email address for accepted emails',
										name varchar(255) DEFAULT NULL COMMENT 'Email name for outbound emails',
										auto_reply tinyint(1) NOT NULL COMMENT 'FK to active_status',
										PRIMARY KEY (id)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attachments to correspondances in the ticketing system';
									",
		);

		foreach ($strTableSQLs as $strTable => $strSQL)
		{
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to create $strTable table. " . $qryQuery->Error());
			}
			$this->rollbackSQL[] = "DROP TABLE $strTable";
		}

		$strInsertSQLs = array(

			"ticketing_status" => "INSERT INTO ticketing_status (id, name, description, const_name) VALUES
									(1, 'Unassigned', 		'Not yet assigned to anyone', 			'TICKETING_STATUS_UNASSIGNED'),
									(2, 'With Customer',	'Awaiting response from customer', 		'TICKETING_STATUS_WITH_CUSTOMER'),
									(3, 'With Carrier', 	'Awaiting response from carrier', 		'TICKETING_STATUS_WITH_CARRIER'),
									(4, 'In Progress', 		'Currently being worked on', 			'TICKETING_STATUS_IN_PROGRESS'),
									(5, 'Completed', 		'The issue has been resolved',			'TICKETING_STATUS_COMPLETED'),
									(6, 'Deleted', 			'The ticket has been deleted', 			'TICKETING_STATUS_DELETED'),
									(7, 'Assigned', 		'Assigned to someone, but not started', 'TICKETING_STATUS_ASSIGNED')
									",

			"ticketing_attachment_blacklist_status" => "INSERT INTO ticketing_attachment_blacklist_status (id, name, description, const_name) VALUES
									(0, 'Grey Listed', 	'Grey Listed', 	'TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY'),
									(1, 'White Listed',	'White Listed', 'TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE'),
									(2, 'Black Listed', 'Black Listed', 'TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK')
									",

			"ticketing_priority" => "INSERT INTO ticketing_priority (id, name, description, const_name) VALUES
									(1, 'Low', 		'Low priority', 	'TICKETING_PRIORITY_LOW'),
									(2, 'Medium',	'Medium priority', 	'TICKETING_PRIORITY_MEDIUM'),
									(3, 'High', 	'High priority', 	'TICKETING_PRIORITY_HIGH'),
									(4, 'Urgent', 	'Urgent priority', 	'TICKETING_PRIORITY_URGENT')
									",

			"ticketing_correspondance_delivery_status" => "INSERT INTO ticketing_correspondance_delivery_status (id, name, description, const_name) VALUES
									(1, 'Received', 'Received', 'TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED'),
									(2, 'Not Sent',	'Not Sent', 'TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT'),
									(3, 'Sent', 	'Sent', 	'TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT')
									",

			"ticketing_correspondance_source" => "INSERT INTO ticketing_correspondance_source (id, name, description, const_name) VALUES
									(0, 'XML', 		'XML', 		'TICKETING_CORRESPONDANCE_SOURCE_XML'),
									(1, 'Email', 	'Email', 	'TICKETING_CORRESPONDANCE_SOURCE_EMAIL'),
									(2, 'Web',		'Web', 		'TICKETING_CORRESPONDANCE_SOURCE_WEB'),
									(3, 'Phone', 	'Phone', 	'TICKETING_CORRESPONDANCE_SOURCE_PHONE')
									",

			"ticketing_user_permission" => "INSERT INTO ticketing_user_permission (id, name, description, const_name) VALUES
									(1, 'User', 			'Ticketing system user', 		'TICKETING_USER_PERMISSION_USER'),
									(2, 'Administrator',	'Ticketing system administrator', 'TICKETING_USER_PERMISSION_ADMIN')
									",

			"ticketing_category" => "INSERT INTO ticketing_category (id, name, description, const_name) VALUES
									( 0, 'Uncategorized', 			'Uncategorized', 			'TICKETING_CATEGORY_UNCATEGORIZED'),
									( 1, 'Billing Enquiries', 		'Billing enquiries', 		'TICKETING_CATEGORY_BILLING_ENQUIRIES'),
									( 2, 'Product Enquiries',		'Product enquiries', 		'TICKETING_CATEGORY_PRODUCT_ENQUIRIES'),
									( 3, 'Credit Control',			'Credit control', 			'TICKETING_CATEGORY_CREDIT_CONTROL'),
									( 4, 'Mobile Activations',		'Mobile activations', 		'TICKETING_CATEGORY_MOBILE_ACTIVIATIONS'),
									( 5, 'T.I.O.',					'T.I.O.', 					'TICKETING_CATEGORY_TIO'),
									( 6, 'Complaints',				'Complaints', 				'TICKETING_CATEGORY_COMPLAINTS'),
									( 7, 'Faults',					'Faults', 					'TICKETING_CATEGORY_FAULTS'),
									( 8, 'A.M.C.',					'A.M.C.', 					'TICKETING_CATEGORY_AMC'),
									( 9, 'Disconnections',			'Disconnections', 			'TICKETING_CATEGORY_DISCONNECTIONS'),
									(10, 'Customer Cancellations',	'Customer cancellations', 	'TICKETING_CATEGORY_CUSTOMER_CANCELLATIONS')
									",


		);

		foreach ($strInsertSQLs as $strTable => $strSQL)
		{
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to populate $strTable table. " . $qryQuery->Error());
			}
		}

		// Create views for seeing the services currently associated with accounts
		$strSQL = "
					CREATE VIEW current_service_account (serviceId, accountId)
					AS 
							SELECT MAX( Service.Id ) serviceId, MAX( Account.Id ) accountId
							FROM Service, Account
							WHERE Account.Id = Service.Account
							  AND (
								Service.ClosedOn IS NULL
								OR NOW( ) < Service.ClosedOn
							  )
							  AND Service.CreatedOn < NOW( ) 
							GROUP BY Account.Id, Service.FNN
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to create current_service_account view. " . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP VIEW current_service_account";

		$strSQL = "
					CREATE
						VIEW account_services (account_id, service_id, fnn)
						AS 
						SELECT Service.Account AS account_id, Service.Id AS service_id, Service.FNN AS fnn
						  FROM Service
						INNER JOIN current_service_account
						   ON Service.Account = current_service_account.accountId
						  AND Service.Id = current_service_account.serviceId
						  AND Service.Status IN (400,402,403)
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to create account_services view. " . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP VIEW account_services";

		$strSQL = "
					ALTER TABLE CustomerGroup
						ADD flex_url varchar(255) DEFAULT NULL COMMENT 'The base URL for the Flex web interface for this customer group',
						ADD email_domain varchar(255) DEFAULT NULL COMMENT 'The domain part of email addresses sent to to this customer group'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to alter CustomerGroup table. " . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE CustomerGroup DROP flex_url, DROP email_domain";

		$strSQL = "
			UPDATE CustomerGroup SET 
				email_domain = SUBSTRING_INDEX(outbound_email, '@', -1),
				flex_url = CONCAT('https://', SUBSTRING_INDEX(SUBSTRING_INDEX(outbound_email, '@', -1), '.', 1), '.yellowbilling.com.au')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to default email_domain in CustomerGroup table. " . $qryQuery->Error());
		}
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
