<?php

/**
 * Version 14 (fourteen) of database update.
 * This version: -
 *	1:	Adds tables required for email address configuration (email_notification, email_address_usage, email_notification_address)
 *	2:	Modify DocumentTemplateType table to work with constants model
 *	3:	Adds support for Friendly Reminder letters
 *	4:	Adds support for Credit Card image for letters (on payslip)
 */

class Flex_Rollout_Version_000014 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Create email_notification Table
		$strSQL = " CREATE TABLE IF NOT EXISTS email_notification (
					  id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the email notification',
					  name varchar(50) NOT NULL COMMENT 'Name of the email notification',
					  description varchar(255) NOT NULL COMMENT 'Description of the email notification',
					  const_name varchar(255) NOT NULL COMMENT 'The constant name',
					  allow_customer_group_emails tinyint(1) unsigned NOT NULL COMMENT 'Whether or not to allow emails to be sent to customer group contacts',
					  PRIMARY KEY (id),
					  UNIQUE KEY name (name)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Emails generated by the system';
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create email_notification table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE email_notification";

		$strSQL = " INSERT INTO email_notification (id, name, description, const_name, allow_customer_group_emails) 
					VALUES
					(1, 'Late Notice List', 			'Email listing accounts that will be sent late notices', 		'EMAIL_NOTIFICATION_LATE_NOTICE_LIST', 				0),
					(2, 'Late Notice Report', 			'Email listing accounts that have been sent late notices', 		'EMAIL_NOTIFICATION_LATE_NOTICE_REPORT', 			0),
					(3, 'Late Notice', 					'Email to customer with late notice attachment', 				'EMAIL_NOTIFICATION_LATE_NOTICE', 					1),
					(4, 'Late Fee List', 				'Email listing accounts that will have late fees applied', 		'EMAIL_NOTIFICATION_LATE_FEE_LIST', 				0),
					(5, 'Late Fee Report', 				'Email listing accounts that had late fees applied', 			'EMAIL_NOTIFICATION_LATE_FEE_REPORT', 				0),
					(6, 'Automatic Barring List', 		'Email listing accounts that will be automatically barred', 	'EMAIL_NOTIFICATION_AUTOMATIC_BARRING_LIST', 		0),
					(7, 'Automatic Barring Report', 	'Email listing accounts that have been automatically barred', 	'EMAIL_NOTIFICATION_AUTOMATIC_BARRING_REPORT', 		0),
					(8, 'Automatic Unbarring Report', 	'Email listing accounts that have been automatically unbarred', 'EMAIL_NOTIFICATION_AUTOMATIC_UNBARRING_REPORT', 	0),
					(9, 'Failed Email Report', 			'Report of emails that failed to be delivered to customers', 	'EMAIL_NOTIFICATION_FAILED_EMAIL_REPORT', 			1)
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate email_notification table. ' . $qryQuery->Error());
		}

		// Create email_address_usage Table
		$strSQL = " CREATE TABLE IF NOT EXISTS email_address_usage (
					  id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the email address usage',
					  name varchar(50) NOT NULL COMMENT 'Name of the email address usage (to, cc, bcc or from)',
					  description varchar(255) NOT NULL COMMENT 'Description of the email address usage',
					  const_name varchar(255) NOT NULL COMMENT 'The constant name',
					  PRIMARY KEY  (id),
					  UNIQUE KEY name (name)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email address usage';
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create email_address_usage table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE email_address_usage";

		$strSQL = "
					INSERT INTO email_address_usage (name, description, const_name) VALUES 
					('to',		'Primary recipient of email', 'EMAIL_ADDRESS_USAGE_TO'),
					('cc', 		'Secondary recipient of email', 'EMAIL_ADDRESS_USAGE_CC'),
					('bcc', 	'Undisclosed recipient of email', 'EMAIL_ADDRESS_USAGE_BCC'),
					('from', 	'Sender of email', 'EMAIL_ADDRESS_USAGE_FROM')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate email_address_usage table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE email_address_usage";

		// Create email_notification_address Table
		$strSQL = " CREATE TABLE IF NOT EXISTS email_notification_address (
					  id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the email notification address',
					  email_notification_id bigint(20) unsigned NOT NULL COMMENT 'Id of the email notification',
					  email_address_usage_id bigint(20) unsigned NOT NULL COMMENT 'Id of the email address usage',
					  email_address varchar(255) NOT NULL COMMENT 'The email address',
					  customer_group_id bigint(20) unsigned NULL DEFAULT NULL COMMENT 'Id of the customer group or NULL to apply to ALL customer groups',
					  PRIMARY KEY  (id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email notification address';
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create email_notification_address table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE email_notification_address ";

		// Make the DocumentTemplateType table work with the constant model
		$strSQL = "ALTER TABLE DocumentTemplateType 
					ADD description VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Description of the document template type',
					ADD const_name VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Constant name in code';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter DocumentTemplateType table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE DocumentTemplateType DROP description, DROP const_name ";

		// Update the existing rows
		$strSQL = "UPDATE DocumentTemplateType SET description = Name, const_name = UCASE(CONCAT('document_template_type_', REPLACE(Name, ' ', '_')))";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate name and const_name columns to active_status table. ' . $qryQuery->Error());
		}

		// Add row for friendly reminder
		$strSQL = "INSERT INTO DocumentTemplateType (Name, description, const_name)VALUES ('Friendly Reminder', 'Friendly Reminder', 'DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate name and const_name columns to active_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM DocumentTemplateType WHERE const_name = 'DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER'";

		// Add row for friendly reminder
		$strSQL = "UPDATE DocumentTemplateType SET const_name = 'DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND' WHERE const_name = 'DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND_NOTICE'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate name and const_name columns to active_status table. ' . $qryQuery->Error());
		}

		// Add a default schema for the document
		$strSQL = 'INSERT INTO DocumentTemplateSchema (TemplateType, Version, Description, Sample)
				VALUES ((SELECT Id FROM DocumentTemplateType WHERE const_name = \'DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER\'), 1, \'Original schema for friendly reminders\', \'<Document DateIssued="06 May 2008">\n\n	<Currency>\n		<Symbol Location="Prefix">$</Symbol>\n		<Negative Location="Suffix">CR</Negative>\n	</Currency>\n\n	<Account Id="9000999999" Name="Some Account Name" CustomerGroup="CUSTOMER_GROUP_SOME_GROUP">\n		<Addressee>A. Ddressee</Addressee>\n		<AddressLine1>Unit 13</AddressLine1>\n		<AddressLine2>99 Some Road</AddressLine2>\n		<Suburb>Placeville</Suburb>\n		<Postcode>4099</Postcode>\n		<State>QLD</State>\n		\n		<CustomerReference>9000999999</CustomerReference>	\n\n		<PrimaryContact>\n			<FirstName>Primary</FirstName>\n			<LastName>Contact</LastName>\n			<Title>Mr</Title>\n			<FullName>Mr Primary Contact</FullName>\n		</PrimaryContact>\n	</Account>\n\n	<Payment>\n		<BPay>\n			<CustomerReference>10009999999</CustomerReference>\n		</BPay>\n		\n		<BillExpress>\n			<CustomerReference>10009999999</CustomerReference>\n		</BillExpress>\n	</Payment>\n\n	<Outstanding>\n		<Overdue>121.84</Overdue>\n		<NotOverdue>0.00</NotOverdue>\n		<Total>121.84</Total>\n		<CurrentInvoiceId>9000999999</CurrentInvoiceId>\n		<ActionDate>20 May 2008</ActionDate>\n	</Outstanding>\n\n</Document>\')
		';
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add default DocumentTemplateSchema record for friendly reminders. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM DocumentTemplateSchema WHERE TemplateType = (SELECT Id FROM DocumentTemplateType WHERE const_name = 'DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER')";

		// Add support for the new credit card image on payslips
		$strSQL = "
			INSERT INTO DocumentResourceType (PlaceHolder, Description, PermissionRequired, TagSignature) VALUES
			('Credit Card', 'Credit Card for payslip', 1023, '<img src=''fdbp://[PlaceHolder]'' />')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add credit card entry to DocumentResourceType. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card'";

		// Now need to create the DocumentResourceTypeFileType entries
		$strSQL = "
			INSERT INTO DocumentResourceTypeFileType (FileType, ResourceType) VALUES
			((SELECT Id FROM FileType WHERE Extension = 'jpeg'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')),
			((SELECT Id FROM FileType WHERE Extension = 'jpg'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')),
			((SELECT Id FROM FileType WHERE Extension = 'jpe'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')),
			((SELECT Id FROM FileType WHERE Extension = 'png'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')),
			((SELECT Id FROM FileType WHERE Extension = 'tiff'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')),
			((SELECT Id FROM FileType WHERE Extension = 'tif'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')),
			((SELECT Id FROM FileType WHERE Extension = 'raw'), (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card'))
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add credit card entry to DocumentResourceType. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM DocumentResourceTypeFileType WHERE ResourceType = (SELECT Id FROM DocumentResourceType WHERE PlaceHolder = 'Credit Card')";
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
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>