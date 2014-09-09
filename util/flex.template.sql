-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: template_flex
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Account`
--

DROP TABLE IF EXISTS `Account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Account` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `BusinessName` varchar(255) CHARACTER SET ucs2 NOT NULL,
  `TradingName` varchar(255) CHARACTER SET ucs2 NOT NULL,
  `ABN` varchar(20) NOT NULL,
  `ACN` varchar(20) NOT NULL,
  `Address1` varchar(255) NOT NULL,
  `Address2` varchar(255) CHARACTER SET latin1 NOT NULL,
  `Suburb` varchar(255) CHARACTER SET latin1 NOT NULL,
  `Postcode` varchar(10) CHARACTER SET latin1 NOT NULL,
  `State` varchar(3) CHARACTER SET latin1 NOT NULL,
  `Country` varchar(2) CHARACTER SET latin1 NOT NULL,
  `BillingType` int(10) unsigned NOT NULL,
  `PrimaryContact` bigint(20) DEFAULT NULL,
  `CustomerGroup` int(20) unsigned NOT NULL,
  `CreditCard` bigint(20) unsigned DEFAULT NULL,
  `DirectDebit` bigint(20) unsigned DEFAULT NULL,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `LastBilled` date DEFAULT NULL,
  `BillingDate` int(10) unsigned NOT NULL DEFAULT '1',
  `BillingFreq` int(10) unsigned NOT NULL DEFAULT '1',
  `BillingFreqType` int(10) unsigned NOT NULL,
  `BillingMethod` int(10) unsigned NOT NULL,
  `PaymentTerms` int(11) NOT NULL,
  `CreatedBy` bigint(20) unsigned NOT NULL,
  `CreatedOn` date NOT NULL,
  `DisableDDR` tinyint(1) NOT NULL,
  `DisableLatePayment` int(11) DEFAULT NULL,
  `DisableLateNotices` int(11) NOT NULL DEFAULT '0',
  `LatePaymentAmnesty` date DEFAULT NULL COMMENT 'If this is set, no late payment notices are generated until after this date',
  `Sample` int(11) NOT NULL DEFAULT '0',
  `Archived` tinyint(1) NOT NULL,
  `credit_control_status` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'FK to credit_control_status.id',
  `last_automatic_invoice_action` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Last automatic invoice action. FK to automatic_invoice_action.id',
  `last_automatic_invoice_action_datetime` datetime DEFAULT NULL COMMENT 'Time of last automatic invoice action',
  `automatic_barring_status` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Automatic barring status. FK to automatic_barring_status.id',
  `automatic_barring_datetime` datetime DEFAULT NULL COMMENT 'Time of last automatic barring status change',
  `tio_reference_number` varchar(150) DEFAULT NULL COMMENT 'reference number when dealing with the T.I.O.',
  `vip` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'VIP status (1 = VIP, 0 = Non-VIP)',
  `account_class_id` int(10) unsigned NOT NULL,
  `collection_severity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Archived` (`Archived`),
  KEY `BusinessName` (`BusinessName`),
  KEY `TradingName` (`TradingName`),
  KEY `ABN` (`ABN`),
  KEY `ACN` (`ACN`),
  KEY `Suburb` (`Suburb`),
  KEY `Postcode` (`Postcode`),
  KEY `State` (`State`),
  KEY `Country` (`Country`),
  KEY `BillingType` (`BillingType`),
  KEY `PrimaryContact` (`PrimaryContact`),
  KEY `CustomerGroup` (`CustomerGroup`),
  KEY `CreditCard` (`CreditCard`),
  KEY `DirectDebit` (`DirectDebit`),
  KEY `LastBilled` (`LastBilled`),
  KEY `BillingDate` (`BillingDate`),
  KEY `BillingFreq` (`BillingFreq`),
  KEY `BillingFreqType` (`BillingFreqType`),
  KEY `BillingMethod` (`BillingMethod`),
  KEY `PaymentTerms` (`PaymentTerms`),
  KEY `CreatedOn` (`CreatedOn`),
  KEY `DisableDDR` (`DisableDDR`),
  KEY `DisableLatePayment` (`DisableLatePayment`),
  KEY `credit_control_status` (`credit_control_status`),
  KEY `fk_account_account_class_id` (`account_class_id`),
  KEY `fk_account_collection_severity_id` (`collection_severity_id`),
  CONSTRAINT `fk_account_account_class_id` FOREIGN KEY (`account_class_id`) REFERENCES `account_class` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_severity_id` FOREIGN KEY (`collection_severity_id`) REFERENCES `collection_severity` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Account`
--

LOCK TABLES `Account` WRITE;
/*!40000 ALTER TABLE `Account` DISABLE KEYS */;
/*!40000 ALTER TABLE `Account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AccountGroup`
--

DROP TABLE IF EXISTS `AccountGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AccountGroup` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `CreatedBy` bigint(20) unsigned NOT NULL,
  `CreatedOn` date NOT NULL,
  `ManagedBy` bigint(20) unsigned DEFAULT NULL,
  `Archived` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Archived` (`Archived`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AccountGroup`
--

LOCK TABLES `AccountGroup` WRITE;
/*!40000 ALTER TABLE `AccountGroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `AccountGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CDR`
--

DROP TABLE IF EXISTS `CDR`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CDR` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `FNN` char(25) DEFAULT NULL,
  `File` bigint(20) NOT NULL,
  `Carrier` int(10) unsigned NOT NULL,
  `CarrierRef` varchar(255) DEFAULT NULL,
  `Source` varchar(25) DEFAULT NULL,
  `Destination` varchar(25) DEFAULT NULL,
  `StartDatetime` datetime DEFAULT NULL,
  `EndDatetime` datetime DEFAULT NULL,
  `Units` bigint(20) unsigned DEFAULT NULL,
  `AccountGroup` bigint(20) unsigned DEFAULT NULL,
  `Account` bigint(20) unsigned DEFAULT NULL,
  `Service` bigint(20) unsigned DEFAULT NULL,
  `Cost` decimal(13,4) DEFAULT NULL,
  `Status` int(10) unsigned NOT NULL,
  `CDR` varchar(32767) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `DestinationCode` bigint(20) unsigned DEFAULT NULL,
  `RecordType` bigint(20) unsigned DEFAULT NULL,
  `ServiceType` int(10) unsigned DEFAULT NULL,
  `Charge` decimal(13,4) DEFAULT NULL,
  `Rate` bigint(20) unsigned DEFAULT NULL,
  `NormalisedOn` datetime DEFAULT NULL,
  `RatedOn` datetime DEFAULT NULL,
  `invoice_run_id` bigint(20) DEFAULT NULL COMMENT 'FK to InvoiceRun table',
  `SequenceNo` int(10) unsigned NOT NULL,
  `Credit` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FNN` (`FNN`),
  KEY `Carrier` (`Carrier`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service` (`Service`),
  KEY `RecordType` (`RecordType`),
  KEY `ServiceType` (`ServiceType`),
  KEY `Status` (`Status`),
  KEY `Rate` (`Rate`),
  KEY `Units` (`Units`),
  KEY `StartDatetime` (`StartDatetime`),
  KEY `Credit` (`Credit`),
  KEY `Cost` (`Cost`),
  KEY `Charge` (`Charge`),
  KEY `Source` (`Source`),
  KEY `Destination` (`Destination`),
  KEY `File` (`File`),
  KEY `DestinationCode` (`DestinationCode`),
  KEY `invoice_run_id` (`invoice_run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CDR`
--

LOCK TABLES `CDR` WRITE;
/*!40000 ALTER TABLE `CDR` DISABLE KEYS */;
/*!40000 ALTER TABLE `CDR` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Carrier`
--

DROP TABLE IF EXISTS `Carrier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Carrier` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `carrier_type` bigint(20) NOT NULL COMMENT '(FK) The type of Carrier',
  `description` varchar(255) NOT NULL COMMENT 'Description for this Carrier',
  PRIMARY KEY (`Id`),
  KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Carrier`
--

LOCK TABLES `Carrier` WRITE;
/*!40000 ALTER TABLE `Carrier` DISABLE KEYS */;
/*!40000 ALTER TABLE `Carrier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CarrierModule`
--

DROP TABLE IF EXISTS `CarrierModule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CarrierModule` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Carrier` bigint(20) unsigned NOT NULL COMMENT '(FK) Carrier that this instance is linked to',
  `customer_group` bigint(20) DEFAULT NULL COMMENT 'The Customer Group that this Module is associated with.  NULL: All CustomerGroups',
  `Type` int(10) unsigned NOT NULL COMMENT 'Module Type (eg. Provisioning, Collection)',
  `Module` varchar(512) NOT NULL COMMENT 'Module Class Name for Auto-loading',
  `FileType` int(11) NOT NULL COMMENT 'The File Type that this Module is associated with',
  `description` varchar(512) DEFAULT NULL COMMENT 'Description for this instance of the specific Module',
  `FrequencyType` int(10) unsigned NOT NULL COMMENT 'The Magnitude in which Frequency is measured',
  `Frequency` int(10) unsigned NOT NULL COMMENT 'How often the file is generated',
  `LastSentOn` datetime NOT NULL COMMENT 'When the file was last generated',
  `EarliestDelivery` int(10) unsigned NOT NULL COMMENT 'Earliest time the file can be generated (in seconds)',
  `Active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1: Module is Active; 0: Module is Disabled',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CarrierModule`
--

LOCK TABLES `CarrierModule` WRITE;
/*!40000 ALTER TABLE `CarrierModule` DISABLE KEYS */;
/*!40000 ALTER TABLE `CarrierModule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CarrierModuleConfig`
--

DROP TABLE IF EXISTS `CarrierModuleConfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CarrierModuleConfig` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `CarrierModule` bigint(20) unsigned NOT NULL COMMENT 'CarrierModule (FK)',
  `Name` varchar(255) NOT NULL COMMENT 'Field Name',
  `Type` int(11) NOT NULL COMMENT 'Data Type (eg. DATA_TYPE_INTEGER)',
  `Description` varchar(1024) DEFAULT NULL COMMENT 'Description for the Config Variable',
  `Value` varchar(4096) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CarrierModuleConfig`
--

LOCK TABLES `CarrierModuleConfig` WRITE;
/*!40000 ALTER TABLE `CarrierModuleConfig` DISABLE KEYS */;
/*!40000 ALTER TABLE `CarrierModuleConfig` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Charge`
--

DROP TABLE IF EXISTS `Charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Charge` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned DEFAULT NULL,
  `invoice_run_id` bigint(20) DEFAULT NULL COMMENT 'FK to InvoiceRun table',
  `CreatedBy` bigint(20) unsigned DEFAULT NULL,
  `CreatedOn` date NOT NULL,
  `ApprovedBy` bigint(20) unsigned DEFAULT NULL,
  `ChargeType` varchar(10) NOT NULL,
  `charge_type_id` bigint(20) DEFAULT NULL COMMENT '(FK) The ChargeType.Id that this implements',
  `Description` varchar(255) NOT NULL,
  `ChargedOn` date DEFAULT NULL,
  `Nature` enum('DR','CR') NOT NULL,
  `Amount` decimal(13,4) NOT NULL,
  `Invoice` bigint(20) unsigned DEFAULT NULL,
  `Notes` text NOT NULL,
  `LinkType` int(10) unsigned DEFAULT NULL,
  `LinkId` int(10) unsigned DEFAULT NULL,
  `Status` int(10) unsigned NOT NULL,
  `global_tax_exempt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: This Charge WILL NOT have the Global Tax Rate applied; 0: This Charge WILL have the Global Tax Rate applied',
  `charge_model_id` int(10) unsigned NOT NULL COMMENT '(FK) Charge Model',
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service` (`Service`),
  KEY `CreatedOn` (`CreatedOn`),
  KEY `ChargeType` (`ChargeType`),
  KEY `ChargedOn` (`ChargedOn`),
  KEY `Nature` (`Nature`),
  KEY `Amount` (`Amount`),
  KEY `Status` (`Status`),
  KEY `invoice_run_id` (`invoice_run_id`),
  KEY `fk_charge_charge_model_id` (`charge_model_id`),
  CONSTRAINT `fk_charge_charge_model_id` FOREIGN KEY (`charge_model_id`) REFERENCES `charge_model` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Charge`
--

LOCK TABLES `Charge` WRITE;
/*!40000 ALTER TABLE `Charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `Charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ChargeType`
--

DROP TABLE IF EXISTS `ChargeType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ChargeType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ChargeType` varchar(10) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Nature` enum('DR','CR') NOT NULL,
  `Fixed` tinyint(1) unsigned NOT NULL,
  `automatic_only` smallint(1) NOT NULL DEFAULT '0' COMMENT '1: This can only be automatically added by Flex; 0: This can be manually added by an Employee',
  `Amount` decimal(13,4) NOT NULL,
  `Archived` tinyint(1) NOT NULL,
  `charge_type_visibility_id` int(10) unsigned NOT NULL COMMENT '(FK) ChargeType Visiblity Mode',
  `charge_model_id` int(10) unsigned NOT NULL COMMENT '(FK) Charge Model',
  PRIMARY KEY (`Id`),
  KEY `fk_charge_type_charge_type_visibility_id` (`charge_type_visibility_id`),
  KEY `fk_charge_type_charge_model_id` (`charge_model_id`),
  CONSTRAINT `fk_charge_type_charge_model_id` FOREIGN KEY (`charge_model_id`) REFERENCES `charge_model` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_charge_type_charge_type_visibility_id` FOREIGN KEY (`charge_type_visibility_id`) REFERENCES `charge_type_visibility` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ChargeType`
--

LOCK TABLES `ChargeType` WRITE;
/*!40000 ALTER TABLE `ChargeType` DISABLE KEYS */;
/*!40000 ALTER TABLE `ChargeType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ConditionalContexts`
--

DROP TABLE IF EXISTS `ConditionalContexts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ConditionalContexts` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Object` varchar(50) NOT NULL,
  `Property` varchar(50) NOT NULL,
  `Operator` varchar(50) NOT NULL COMMENT 'valid operators are <, >, <=, >=, ==, !=, IsEmpty, IsNull',
  `Value` varchar(255) NOT NULL COMMENT 'The value of the property will be compared to this value using the operator, in the order "Property operator value"',
  `Context` int(10) unsigned NOT NULL COMMENT 'If the condition is met, then use this context to render the property',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ConditionalContexts`
--

LOCK TABLES `ConditionalContexts` WRITE;
/*!40000 ALTER TABLE `ConditionalContexts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ConditionalContexts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Contact`
--

DROP TABLE IF EXISTS `Contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contact` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `DOB` date NOT NULL,
  `JobTitle` varchar(255) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `CustomerContact` tinyint(1) unsigned NOT NULL,
  `Phone` char(25) NOT NULL,
  `Mobile` char(25) NOT NULL,
  `Fax` char(25) NOT NULL,
  `PassWord` varchar(40) NOT NULL,
  `SessionId` varchar(40) NOT NULL,
  `SessionExpire` datetime NOT NULL,
  `Archived` tinyint(1) NOT NULL,
  `LastLogin` varchar(20) DEFAULT NULL COMMENT 'Unix time stamp of when the user last authenticated.',
  `CurrentLogin` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `FirstName` (`FirstName`),
  KEY `LastName` (`LastName`),
  KEY `DOB` (`DOB`),
  KEY `Email` (`Email`),
  KEY `Account` (`Account`),
  KEY `CustomerContact` (`CustomerContact`),
  KEY `Phone` (`Phone`),
  KEY `Mobile` (`Mobile`),
  KEY `Fax` (`Fax`),
  KEY `Archived` (`Archived`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contact`
--

LOCK TABLES `Contact` WRITE;
/*!40000 ALTER TABLE `Contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `Contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CostCentre`
--

DROP TABLE IF EXISTS `CostCentre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CostCentre` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CostCentre`
--

LOCK TABLES `CostCentre` WRITE;
/*!40000 ALTER TABLE `CostCentre` DISABLE KEYS */;
/*!40000 ALTER TABLE `CostCentre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CreditCard`
--

DROP TABLE IF EXISTS `CreditCard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CreditCard` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `CardType` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  `CardNumber` varchar(100) NOT NULL,
  `ExpMonth` char(2) NOT NULL,
  `ExpYear` char(4) NOT NULL,
  `CVV` varchar(50) DEFAULT NULL,
  `Archived` tinyint(1) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp at which the credit card details were entered into flex',
  `employee_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into Employee table. Id of the employee who entered the CreditCard details',
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Archived` (`Archived`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CreditCard`
--

LOCK TABLES `CreditCard` WRITE;
/*!40000 ALTER TABLE `CreditCard` DISABLE KEYS */;
/*!40000 ALTER TABLE `CreditCard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CustomerGroup`
--

DROP TABLE IF EXISTS `CustomerGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CustomerGroup` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `internal_name` varchar(255) NOT NULL COMMENT 'Name of the Customer Group, as used within the Telco',
  `external_name` varchar(255) NOT NULL COMMENT 'Name of the Customer Group as the customers know it to be',
  `outbound_email` varchar(255) NOT NULL COMMENT 'Email Address for outgoing email correspondance from this CustomerGroup',
  `flex_url` varchar(255) DEFAULT NULL COMMENT 'The base URL for the Flex web interface for this customer group',
  `email_domain` varchar(255) DEFAULT NULL COMMENT 'The domain part of email addresses sent to to this customer group',
  `customer_primary_color` varchar(255) DEFAULT NULL COMMENT 'Primary colour displayed in the customer management interface',
  `customer_secondary_color` varchar(255) DEFAULT NULL COMMENT 'Secondary colour displayed in the customer management interface',
  `customer_logo` mediumblob,
  `customer_logo_type` char(11) DEFAULT NULL,
  `customer_breadcrumb_menu_color` varchar(255) DEFAULT NULL COMMENT 'Secondary colour displayed in the customer management interface',
  `customer_exit_url` varchar(255) DEFAULT NULL COMMENT 'When customer logs out this is the url they will be redirected to.',
  `external_name_possessive` varchar(255) DEFAULT NULL COMMENT 'Possessive name (e.g. MyCo''''s payment plan...)',
  `bill_pay_biller_code` int(5) DEFAULT NULL COMMENT 'BillPay biller code (a 5 digit number)',
  `abn` char(11) DEFAULT NULL COMMENT 'ABN of business (11 digits)',
  `acn` char(9) DEFAULT NULL COMMENT 'ACN of company (9 digits)',
  `business_phone` varchar(50) DEFAULT NULL COMMENT 'Phone number for business',
  `business_fax` varchar(50) DEFAULT NULL COMMENT 'Fax number for business',
  `business_web` varchar(255) DEFAULT NULL COMMENT 'URL of customer group external website',
  `business_contact_email` varchar(255) DEFAULT NULL COMMENT 'Phone number for general enquiries and first contact',
  `business_info_email` varchar(255) DEFAULT NULL COMMENT 'Email info email address',
  `customer_service_phone` varchar(50) DEFAULT NULL COMMENT 'Phone number of customer service department',
  `customer_service_email` varchar(255) DEFAULT NULL COMMENT 'Email address of customer service department',
  `customer_service_contact_name` varchar(255) DEFAULT NULL COMMENT 'Contact name in customer service department',
  `business_payable_name` varchar(255) DEFAULT NULL COMMENT 'Name payments should be made out to',
  `business_payable_address` varchar(255) DEFAULT NULL COMMENT 'Address postal payments should mailed to',
  `credit_card_payment_phone` varchar(50) DEFAULT NULL COMMENT 'Phone number customers call to pay by credit card',
  `faults_phone` varchar(50) DEFAULT NULL COMMENT 'Phone number customers call to report faults',
  `customer_advert_image` mediumblob COMMENT 'this field is used to store the raw image data for an advertisement in the customer interface',
  `customer_advert_image_type` char(11) DEFAULT NULL COMMENT 'this field sets the image type for the advertisement image uploaded, e.g. image/jpeg',
  `customer_advert_url` varchar(255) DEFAULT NULL COMMENT 'this url is used for the advertisement image',
  `invoice_cdr_credits` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: CDR Credits are Invoiced; 0: CDR Credits are Suppressed',
  `cooling_off_period` int(10) unsigned DEFAULT NULL COMMENT 'Cooling off period for sales (in hours)',
  `interim_invoice_delivery_method_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Delivery Method for Interim Invoices (NULL will resolve to regular Account setting)',
  `bank_account_name` varchar(1024) DEFAULT NULL COMMENT 'Bank Account Name',
  `bank_bsb` char(6) DEFAULT NULL COMMENT 'Bank BSB',
  `bank_account_number` varchar(20) DEFAULT NULL COMMENT 'Bank Account Number',
  `default_account_class_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_customer_group_interim_invoice_delivery_method_id` (`interim_invoice_delivery_method_id`),
  KEY `fk_customer_group_default_account_class_id` (`default_account_class_id`),
  CONSTRAINT `fk_customer_group_default_account_class_id` FOREIGN KEY (`default_account_class_id`) REFERENCES `account_class` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_customer_group_interim_invoice_delivery_method_id` FOREIGN KEY (`interim_invoice_delivery_method_id`) REFERENCES `delivery_method` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CustomerGroup`
--

LOCK TABLES `CustomerGroup` WRITE;
/*!40000 ALTER TABLE `CustomerGroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `CustomerGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DataReport`
--

DROP TABLE IF EXISTS `DataReport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DataReport` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `FileName` text,
  `Summary` mediumtext NOT NULL,
  `Priviledges` bigint(20) unsigned NOT NULL,
  `CreatedOn` date NOT NULL,
  `Documentation` longtext NOT NULL,
  `SQLTable` text NOT NULL,
  `SQLSelect` longtext NOT NULL,
  `SQLWhere` longtext NOT NULL,
  `SQLFields` longtext NOT NULL,
  `SQLGroupBy` longtext NOT NULL,
  `RenderMode` tinyint(1) NOT NULL DEFAULT '0',
  `RenderTarget` int(10) unsigned DEFAULT NULL,
  `Overrides` longtext,
  `PostSelectProcess` longtext COMMENT 'An array containing field name => function name pairs to indicate post-select processing',
  `data_report_status_id` int(10) unsigned NOT NULL DEFAULT '2' COMMENT '(FK) data_report_status',
  PRIMARY KEY (`Id`),
  KEY `fk_DataReport_data_report_status_id` (`data_report_status_id`),
  CONSTRAINT `fk_DataReport_data_report_status_id` FOREIGN KEY (`data_report_status_id`) REFERENCES `data_report_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DataReport`
--

LOCK TABLES `DataReport` WRITE;
/*!40000 ALTER TABLE `DataReport` DISABLE KEYS */;
/*!40000 ALTER TABLE `DataReport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DataReportSchedule`
--

DROP TABLE IF EXISTS `DataReportSchedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DataReportSchedule` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DataReport` bigint(20) unsigned NOT NULL,
  `Employee` bigint(20) unsigned NOT NULL,
  `CreatedOn` datetime NOT NULL,
  `GeneratedOn` datetime DEFAULT NULL,
  `SQLSelect` longtext NOT NULL,
  `SQLWhere` longtext NOT NULL,
  `SQLOrder` longtext,
  `SQLLimit` int(10) unsigned DEFAULT NULL,
  `RenderTarget` int(10) unsigned NOT NULL,
  `Status` int(11) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DataReportSchedule`
--

LOCK TABLES `DataReportSchedule` WRITE;
/*!40000 ALTER TABLE `DataReportSchedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `DataReportSchedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Destination`
--

DROP TABLE IF EXISTS `Destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Destination` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Code` bigint(20) unsigned NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Context` int(20) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Code` (`Code`),
  KEY `Context` (`Context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Destination`
--

LOCK TABLES `Destination` WRITE;
/*!40000 ALTER TABLE `Destination` DISABLE KEYS */;
/*!40000 ALTER TABLE `Destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DirectDebit`
--

DROP TABLE IF EXISTS `DirectDebit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DirectDebit` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `BankName` varchar(255) NOT NULL,
  `BSB` char(6) NOT NULL,
  `AccountNumber` char(9) NOT NULL,
  `AccountName` varchar(255) NOT NULL,
  `Archived` tinyint(1) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp at which the direct debit account details were entered into flex',
  `employee_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into Employee table. Id of the employee who entered the DirectDebit details',
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `BankName` (`BankName`),
  KEY `BSB` (`BSB`),
  KEY `Archived` (`Archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DirectDebit`
--

LOCK TABLES `DirectDebit` WRITE;
/*!40000 ALTER TABLE `DirectDebit` DISABLE KEYS */;
/*!40000 ALTER TABLE `DirectDebit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DocumentResource`
--

DROP TABLE IF EXISTS `DocumentResource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DocumentResource` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'uniquely identifies the Resource',
  `CustomerGroup` bigint(20) unsigned NOT NULL COMMENT 'foreign key into the CustomerGroup table',
  `Type` bigint(20) unsigned NOT NULL COMMENT 'Foreign key into the DocumentResourceType table',
  `FileType` bigint(20) unsigned NOT NULL COMMENT 'Foreign key into the FileType table, reflecting the FileType of the resource',
  `StartDatetime` datetime NOT NULL COMMENT 'Timestamp when which the Resource comes into effect',
  `EndDatetime` datetime NOT NULL COMMENT 'Timestamp for when the Resource stops being used',
  `CreatedOn` datetime NOT NULL COMMENT 'Time at which the file was uploaded',
  `OriginalFilename` varchar(255) NOT NULL COMMENT 'The original name of the file, when it was uploaded',
  `FileContent` mediumblob NOT NULL COMMENT 'The binary contents of the resource file',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DocumentResource`
--

LOCK TABLES `DocumentResource` WRITE;
/*!40000 ALTER TABLE `DocumentResource` DISABLE KEYS */;
/*!40000 ALTER TABLE `DocumentResource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DocumentResourceType`
--

DROP TABLE IF EXISTS `DocumentResourceType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DocumentResourceType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Uniquely identifies the record',
  `PlaceHolder` varchar(255) NOT NULL COMMENT 'PlaceHolder name which is how templates will reference the required resource.  Must be unique',
  `Description` varchar(255) NOT NULL COMMENT 'Describes the ResourceType',
  `PermissionRequired` bigint(20) unsigned NOT NULL COMMENT 'The permissions required by the user to associate resources with this PlaceHolder name.  Multiple permissions can be logically ORed together',
  `TagSignature` varchar(255) NOT NULL COMMENT 'Defines the tag signature for this resource type, which will be placed in document templates to reference this Resource Type. The Placeholder name will be placed where [PlaceHolder] is located',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DocumentResourceType`
--

LOCK TABLES `DocumentResourceType` WRITE;
/*!40000 ALTER TABLE `DocumentResourceType` DISABLE KEYS */;
/*!40000 ALTER TABLE `DocumentResourceType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DocumentTemplate`
--

DROP TABLE IF EXISTS `DocumentTemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DocumentTemplate` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `CustomerGroup` bigint(20) unsigned NOT NULL COMMENT 'Foreign key into the CustomerGroup table',
  `TemplateType` bigint(20) unsigned NOT NULL COMMENT 'Foreign key into the DocumentTemplateType table',
  `TemplateSchema` bigint(20) unsigned NOT NULL COMMENT 'Foreign key into the DocumentSchema table',
  `Version` int(11) unsigned NOT NULL COMMENT 'The version of this document template, for the given CustomerGroup/TemplateType combination',
  `Source` mediumtext NOT NULL COMMENT 'The actual source code of the template',
  `Description` varchar(255) DEFAULT NULL COMMENT 'Description of the template',
  `EffectiveOn` datetime DEFAULT NULL COMMENT 'The Date that the Template will come into effect and start being used',
  `CreatedOn` datetime NOT NULL COMMENT 'The time at which the template was originally created',
  `ModifiedOn` datetime NOT NULL COMMENT 'Time at which the Template was last modified',
  `LastUsedOn` datetime DEFAULT NULL COMMENT 'the most recent last time the template was actually used',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DocumentTemplate`
--

LOCK TABLES `DocumentTemplate` WRITE;
/*!40000 ALTER TABLE `DocumentTemplate` DISABLE KEYS */;
/*!40000 ALTER TABLE `DocumentTemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DocumentTemplateSchema`
--

DROP TABLE IF EXISTS `DocumentTemplateSchema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DocumentTemplateSchema` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `TemplateType` bigint(20) unsigned NOT NULL COMMENT 'Foreign key into the DocumentTemplateType table',
  `Version` int(11) unsigned NOT NULL COMMENT 'The version of the schema for a particular DocumentTemplateType',
  `Description` varchar(255) DEFAULT NULL COMMENT 'Describes new features of the schema',
  `Sample` mediumtext NOT NULL COMMENT 'Sample XML source code which utilises all features of the schema',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DocumentTemplateSchema`
--

LOCK TABLES `DocumentTemplateSchema` WRITE;
/*!40000 ALTER TABLE `DocumentTemplateSchema` DISABLE KEYS */;
/*!40000 ALTER TABLE `DocumentTemplateSchema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DocumentTemplateType`
--

DROP TABLE IF EXISTS `DocumentTemplateType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DocumentTemplateType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `Name` varchar(255) NOT NULL COMMENT 'ie Invoice, Overdue Notice, Suspension notice etc',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Description of the document template type',
  `const_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Constant name in code',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DocumentTemplateType`
--

LOCK TABLES `DocumentTemplateType` WRITE;
/*!40000 ALTER TABLE `DocumentTemplateType` DISABLE KEYS */;
INSERT INTO `DocumentTemplateType` VALUES (1,'Overdue Notice','Overdue Notice','DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE'),(2,'Suspension Notice','Suspension Notice','DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE'),(3,'Final Demand Notice','Final Demand Notice','DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND'),(4,'Invoice','Invoice','DOCUMENT_TEMPLATE_TYPE_INVOICE'),(5,'Friendly Reminder','Friendly Reminder','DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER');
/*!40000 ALTER TABLE `DocumentTemplateType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Documentation`
--

DROP TABLE IF EXISTS `Documentation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Documentation` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Entity` varchar(50) NOT NULL,
  `Field` varchar(50) NOT NULL,
  `Label` varchar(255) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` longtext NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Entity` (`Entity`,`Field`),
  KEY `Entity_2` (`Entity`),
  KEY `Field` (`Field`),
  KEY `Label` (`Label`),
  KEY `Title` (`Title`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Documentation`
--

LOCK TABLES `Documentation` WRITE;
/*!40000 ALTER TABLE `Documentation` DISABLE KEYS */;
/*!40000 ALTER TABLE `Documentation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Employee`
--

DROP TABLE IF EXISTS `Employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Employee` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `UserName` varchar(31) NOT NULL,
  `PassWord` varchar(40) NOT NULL,
  `Phone` char(25) DEFAULT NULL,
  `Mobile` char(25) DEFAULT NULL,
  `Extension` varchar(15) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `DOB` date NOT NULL,
  `SessionId` varchar(40) NOT NULL,
  `SessionExpire` datetime NOT NULL,
  `Session` longtext NOT NULL,
  `Karma` int(11) NOT NULL,
  `PabloSays` int(10) unsigned NOT NULL,
  `Privileges` bigint(20) unsigned NOT NULL,
  `Archived` tinyint(1) NOT NULL DEFAULT '0',
  `user_role_id` bigint(20) unsigned NOT NULL COMMENT 'FK into user_role table, defining the role of the employee',
  `is_god` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1: GOD Employee (trumps permissions); 0: General Employee',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Employee`
--

LOCK TABLES `Employee` WRITE;
/*!40000 ALTER TABLE `Employee` DISABLE KEYS */;
INSERT INTO `Employee` VALUES (0,'System','User','System','No Password',NULL,NULL,NULL,'','0000-00-00','','0000-00-00 00:00:00','',0,0,140737488355327,0,1,1);
/*!40000 ALTER TABLE `Employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileDownload`
--

DROP TABLE IF EXISTS `FileDownload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FileDownload` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Carrier` int(10) unsigned NOT NULL,
  `CollectedOn` datetime NOT NULL,
  `ImportedOn` datetime DEFAULT NULL,
  `Status` int(10) unsigned NOT NULL,
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Customer Group',
  PRIMARY KEY (`Id`),
  KEY `FileName` (`FileName`),
  KEY `Carrier` (`Carrier`),
  KEY `Location` (`Location`),
  KEY `Status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileDownload`
--

LOCK TABLES `FileDownload` WRITE;
/*!40000 ALTER TABLE `FileDownload` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileDownload` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileExport`
--

DROP TABLE IF EXISTS `FileExport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FileExport` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Carrier` int(10) unsigned NOT NULL,
  `ExportedOn` datetime NOT NULL,
  `Status` int(10) unsigned NOT NULL,
  `FileType` int(10) unsigned NOT NULL,
  `SHA1` varchar(40) NOT NULL,
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Customer Group',
  PRIMARY KEY (`Id`),
  KEY `FileName` (`FileName`),
  KEY `Location` (`Location`),
  KEY `Carrier` (`Carrier`),
  KEY `Status` (`Status`),
  KEY `FileType` (`FileType`),
  KEY `SHA1` (`SHA1`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileExport`
--

LOCK TABLES `FileExport` WRITE;
/*!40000 ALTER TABLE `FileExport` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileExport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileImport`
--

DROP TABLE IF EXISTS `FileImport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FileImport` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) NOT NULL,
  `Location` varchar(255) NOT NULL,
  `archive_location` varchar(1024) DEFAULT NULL COMMENT 'The Archive in which this file has been stored',
  `compression_algorithm_id` bigint(20) NOT NULL COMMENT '(FK) Compression Algorithm applied at Collection',
  `Carrier` int(10) unsigned NOT NULL,
  `ImportedOn` datetime NOT NULL,
  `NormalisedOn` datetime DEFAULT NULL,
  `archived_on` datetime DEFAULT NULL COMMENT 'The Date that this file was automatically archived',
  `Status` int(10) unsigned NOT NULL,
  `FileType` int(10) DEFAULT NULL,
  `SHA1` varchar(40) NOT NULL,
  `file_download` bigint(20) DEFAULT NULL COMMENT '(FK) FileDownload record from which this File was Imported from',
  PRIMARY KEY (`Id`),
  KEY `FileName` (`FileName`),
  KEY `Location` (`Location`),
  KEY `Carrier` (`Carrier`),
  KEY `Status` (`Status`),
  KEY `FileType` (`FileType`),
  KEY `SHA1` (`SHA1`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileImport`
--

LOCK TABLES `FileImport` WRITE;
/*!40000 ALTER TABLE `FileImport` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileImport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileType`
--

DROP TABLE IF EXISTS `FileType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FileType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Uniquely identifies the record',
  `Extension` varchar(255) NOT NULL COMMENT 'The file extension associated with this resource type (doesn''t have to be unique)',
  `MIMEType` varchar(255) DEFAULT NULL COMMENT 'The MIME type of this file type',
  `Description` varchar(255) NOT NULL COMMENT 'A description of the Resource Type',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileType`
--

LOCK TABLES `FileType` WRITE;
/*!40000 ALTER TABLE `FileType` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Invoice`
--

DROP TABLE IF EXISTS `Invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Invoice` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `CreatedOn` date NOT NULL,
  `billing_period_start_datetime` datetime NOT NULL COMMENT 'The Date on which the Billing Period starts',
  `billing_period_end_datetime` datetime NOT NULL COMMENT 'The Date on which the Billing Period ends',
  `DueOn` date NOT NULL,
  `SettledOn` date DEFAULT NULL,
  `Credits` decimal(13,4) NOT NULL,
  `Debits` decimal(13,4) NOT NULL,
  `Total` decimal(13,4) NOT NULL,
  `Tax` decimal(13,4) NOT NULL,
  `TotalOwing` decimal(13,4) NOT NULL,
  `Balance` decimal(13,4) NOT NULL,
  `Disputed` decimal(13,4) NOT NULL,
  `AccountBalance` decimal(13,4) NOT NULL,
  `DeliveryMethod` int(10) unsigned NOT NULL,
  `Status` int(10) unsigned NOT NULL,
  `invoice_run_id` bigint(20) DEFAULT NULL COMMENT 'FK to InvoiceRun table',
  `charge_total` decimal(13,4) NOT NULL COMMENT 'New Charges Total',
  `charge_tax` decimal(13,4) NOT NULL COMMENT 'New Charges Tax',
  `adjustment_total` decimal(13,4) NOT NULL COMMENT 'Adjustment Total',
  `adjustment_tax` decimal(13,4) NOT NULL COMMENT 'Adjustment Tax',
  `collectable_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Balance` (`Balance`),
  KEY `Disputed` (`Disputed`),
  KEY `Status` (`Status`),
  KEY `Total` (`Total`),
  KEY `Tax` (`Tax`),
  KEY `CreatedOn` (`CreatedOn`),
  KEY `DueOn` (`DueOn`),
  KEY `SettledOn` (`SettledOn`),
  KEY `invoice_run_id` (`invoice_run_id`),
  KEY `fk_invoice_collectable_id` (`collectable_id`),
  CONSTRAINT `fk_invoice_collectable_id` FOREIGN KEY (`collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Invoice`
--

LOCK TABLES `Invoice` WRITE;
/*!40000 ALTER TABLE `Invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `Invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `InvoicePayment`
--

DROP TABLE IF EXISTS `InvoicePayment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `InvoicePayment` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_run_id` bigint(20) DEFAULT NULL COMMENT 'FK to InvoiceRun table',
  `Account` bigint(20) unsigned NOT NULL,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Payment` bigint(20) unsigned NOT NULL,
  `Amount` decimal(13,4) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Account` (`Account`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Payment` (`Payment`),
  KEY `Amount` (`Amount`),
  KEY `invoice_run_id` (`invoice_run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `InvoicePayment`
--

LOCK TABLES `InvoicePayment` WRITE;
/*!40000 ALTER TABLE `InvoicePayment` DISABLE KEYS */;
/*!40000 ALTER TABLE `InvoicePayment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `InvoiceRun`
--

DROP TABLE IF EXISTS `InvoiceRun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `InvoiceRun` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) CustomerGroup this InvoiceRun applies to',
  `InvoiceRun` varchar(32) DEFAULT NULL,
  `BillingDate` date NOT NULL,
  `billing_period_start_datetime` datetime NOT NULL COMMENT 'The Date on which the Billing Period starts',
  `billing_period_end_datetime` datetime NOT NULL COMMENT 'The Date on which the Billing Period ends',
  `invoice_run_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The type of InvoiceRun',
  `invoice_run_schedule_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The Scheduled Invoice Run (eg. Bronze Samples)',
  `InvoiceCount` int(11) DEFAULT NULL,
  `BillCost` decimal(13,4) DEFAULT NULL,
  `BillRated` decimal(13,4) DEFAULT NULL,
  `BillInvoiced` decimal(13,4) DEFAULT NULL,
  `BillTax` decimal(13,4) DEFAULT NULL,
  `CDRArchivedState` tinyint(1) DEFAULT NULL,
  `invoice_run_status_id` bigint(20) NOT NULL COMMENT '(FK) Status of the Invoice Run',
  `previous_balance` decimal(13,4) DEFAULT NULL COMMENT 'Total oustanding balance of the customer group''s previous invoice run, at the time of this invoice run',
  `total_balance` decimal(13,4) DEFAULT NULL COMMENT 'Total outstanding balance of all of the customer group''s previous invoice runs, at the time of this invoice run',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `InvoiceRun` (`InvoiceRun`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `InvoiceRun`
--

LOCK TABLES `InvoiceRun` WRITE;
/*!40000 ALTER TABLE `InvoiceRun` DISABLE KEYS */;
/*!40000 ALTER TABLE `InvoiceRun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Note`
--

DROP TABLE IF EXISTS `Note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Note` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Note` longtext NOT NULL,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Contact` bigint(20) unsigned DEFAULT NULL,
  `Account` bigint(20) unsigned DEFAULT NULL,
  `Service` bigint(20) unsigned DEFAULT NULL,
  `Employee` bigint(20) unsigned DEFAULT NULL,
  `Datetime` datetime NOT NULL,
  `NoteType` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Contact` (`Contact`),
  KEY `Service` (`Service`),
  KEY `Datetime` (`Datetime`),
  KEY `NoteType` (`NoteType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Note`
--

LOCK TABLES `Note` WRITE;
/*!40000 ALTER TABLE `Note` DISABLE KEYS */;
/*!40000 ALTER TABLE `Note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NoteType`
--

DROP TABLE IF EXISTS `NoteType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NoteType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `TypeLabel` varchar(25) NOT NULL,
  `BorderColor` varchar(6) NOT NULL,
  `BackgroundColor` varchar(6) NOT NULL,
  `TextColor` varchar(6) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NoteType`
--

LOCK TABLES `NoteType` WRITE;
/*!40000 ALTER TABLE `NoteType` DISABLE KEYS */;
INSERT INTO `NoteType` VALUES (1,'General Notice','89b100','f1f7e1','000000'),(2,'Follow-up Required','868735','FFFFC0','000000'),(3,'Attention Notice','CC0000','f2dbdb','000000'),(4,'Complaint','000000','666666','FFFFFF'),(7,'System Notice','006599','d3e4ed','000000'),(8,'A First Call Resolution','006599','66FF66','000000'),(9,'A 3 Month Trial','006599','66CCFF','000000'),(10,'A 13th Month Free','006599','EE6AA7','000000'),(11,'Price Changes Enquiry','006599','FFA500','000000'),(12,'Loss Reversal','006599','E32636','FAFAFA'),(13,'Provisioning','006599','FFFF33','000000');
/*!40000 ALTER TABLE `NoteType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Payment`
--

DROP TABLE IF EXISTS `Payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Payment` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned DEFAULT NULL,
  `PaidOn` date DEFAULT NULL,
  `carrier` bigint(20) DEFAULT NULL COMMENT '(FK) Carrier from which this payment came from',
  `PaymentType` int(10) unsigned DEFAULT NULL,
  `Amount` decimal(13,4) DEFAULT NULL,
  `TXNReference` varchar(100) DEFAULT NULL,
  `OriginType` int(10) unsigned DEFAULT NULL,
  `OriginId` varchar(255) DEFAULT NULL,
  `EnteredBy` bigint(20) unsigned DEFAULT NULL,
  `Payment` longtext NOT NULL,
  `File` bigint(20) unsigned DEFAULT NULL,
  `SequenceNo` int(10) unsigned DEFAULT NULL,
  `Balance` decimal(13,4) DEFAULT NULL,
  `Status` int(10) unsigned NOT NULL,
  `surcharge_charge_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Surcharge Charge',
  `latest_payment_response_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) payment_response',
  `created_datetime` datetime NOT NULL COMMENT 'Timestamp for creation',
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `PaidOn` (`PaidOn`),
  KEY `PaymentType` (`PaymentType`),
  KEY `Amount` (`Amount`),
  KEY `TXNReference` (`TXNReference`),
  KEY `Balance` (`Balance`),
  KEY `Status` (`Status`),
  KEY `fk_payment_surcharge_charge_id` (`surcharge_charge_id`),
  KEY `fk_Payment_latest_payment_response_id` (`latest_payment_response_id`),
  CONSTRAINT `fk_Payment_latest_payment_response_id` FOREIGN KEY (`latest_payment_response_id`) REFERENCES `payment_response` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_surcharge_charge_id` FOREIGN KEY (`surcharge_charge_id`) REFERENCES `Charge` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Payment`
--

LOCK TABLES `Payment` WRITE;
/*!40000 ALTER TABLE `Payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `Payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Process`
--

DROP TABLE IF EXISTS `Process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Process` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ProcessType` bigint(20) unsigned NOT NULL COMMENT 'ProcessType (FK)',
  `PID` int(10) unsigned DEFAULT NULL COMMENT 'UNIX Process ID',
  `WaitDatetime` datetime NOT NULL COMMENT 'Timestamp for when the Process started waiting for other Processes',
  `StartDatetime` datetime DEFAULT NULL COMMENT 'Starting timestamp for the Process',
  `EndDatetime` datetime DEFAULT NULL COMMENT 'Ending timestamp for the Process',
  `Output` varchar(32767) DEFAULT NULL COMMENT 'Ouput Log for the Process',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Instance Log for Automatic Processes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Process`
--

LOCK TABLES `Process` WRITE;
/*!40000 ALTER TABLE `Process` DISABLE KEYS */;
/*!40000 ALTER TABLE `Process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProcessPriority`
--

DROP TABLE IF EXISTS `ProcessPriority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProcessPriority` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ProcessWaiting` bigint(20) unsigned NOT NULL COMMENT 'Process which is waiting to run (FK)',
  `ProcessRunning` bigint(20) unsigned NOT NULL COMMENT 'Process currently being waited on (FK)',
  `WaitMode` int(11) NOT NULL DEFAULT '0' COMMENT '0 = Do not wait; -1 = Wait indefinitely; 1+ = Wait for X seconds',
  `AlertEmail` varchar(256) DEFAULT NULL COMMENT 'Email to alert if waiting',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `ProcessWaiting` (`ProcessWaiting`,`ProcessRunning`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Automatic Process Pritorities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProcessPriority`
--

LOCK TABLES `ProcessPriority` WRITE;
/*!40000 ALTER TABLE `ProcessPriority` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProcessPriority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProcessType`
--

DROP TABLE IF EXISTS `ProcessType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProcessType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(256) NOT NULL COMMENT 'Script Friendly Name',
  `Command` varchar(1024) NOT NULL COMMENT 'Command to run',
  `WorkingDirectory` varchar(1024) NOT NULL COMMENT 'Working Directory of the Command',
  `Debug` tinyint(3) unsigned NOT NULL COMMENT '0 = Do not print Output; 1 = Print Script Output;',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Automatic Process Types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProcessType`
--

LOCK TABLES `ProcessType` WRITE;
/*!40000 ALTER TABLE `ProcessType` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProcessType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProvisioningLog`
--

DROP TABLE IF EXISTS `ProvisioningLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProvisioningLog` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Carrier` int(10) unsigned NOT NULL,
  `Service` bigint(20) unsigned NOT NULL,
  `Type` int(10) unsigned NOT NULL,
  `Request` bigint(20) unsigned DEFAULT NULL,
  `Direction` tinyint(1) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Description` text,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProvisioningLog`
--

LOCK TABLES `ProvisioningLog` WRITE;
/*!40000 ALTER TABLE `ProvisioningLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProvisioningLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProvisioningRequest`
--

DROP TABLE IF EXISTS `ProvisioningRequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProvisioningRequest` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned NOT NULL,
  `FNN` char(25) NOT NULL,
  `Employee` bigint(20) unsigned NOT NULL,
  `Carrier` int(10) unsigned NOT NULL,
  `Type` int(10) unsigned NOT NULL,
  `CarrierRef` varchar(255) DEFAULT NULL,
  `FileExport` bigint(20) unsigned DEFAULT NULL,
  `Response` bigint(20) unsigned DEFAULT NULL,
  `Description` text,
  `RequestedOn` datetime NOT NULL,
  `AuthorisationDate` date DEFAULT NULL COMMENT 'Authorisation Date',
  `SentOn` datetime DEFAULT NULL,
  `LastUpdated` datetime DEFAULT NULL,
  `Status` int(11) NOT NULL,
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Customer Group',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProvisioningRequest`
--

LOCK TABLES `ProvisioningRequest` WRITE;
/*!40000 ALTER TABLE `ProvisioningRequest` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProvisioningRequest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProvisioningResponse`
--

DROP TABLE IF EXISTS `ProvisioningResponse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProvisioningResponse` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned DEFAULT NULL,
  `Account` bigint(20) unsigned DEFAULT NULL,
  `Service` bigint(20) unsigned DEFAULT NULL,
  `FNN` char(25) DEFAULT NULL,
  `Carrier` int(10) unsigned NOT NULL,
  `Type` int(10) unsigned DEFAULT NULL,
  `CarrierRef` varchar(255) DEFAULT NULL,
  `FileImport` bigint(20) unsigned NOT NULL,
  `Raw` longtext NOT NULL,
  `Description` text,
  `EffectiveDate` datetime DEFAULT NULL,
  `Request` bigint(20) unsigned DEFAULT NULL,
  `ImportedOn` datetime NOT NULL,
  `Status` int(10) unsigned NOT NULL,
  `request_status` bigint(20) DEFAULT NULL,
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Customer Group',
  PRIMARY KEY (`Id`),
  KEY `Account` (`Account`),
  KEY `Service` (`Service`),
  KEY `FNN` (`FNN`),
  KEY `Carrier` (`Carrier`),
  KEY `Type` (`Type`),
  KEY `Request` (`Request`),
  KEY `Status` (`Status`),
  KEY `request_status` (`request_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProvisioningResponse`
--

LOCK TABLES `ProvisioningResponse` WRITE;
/*!40000 ALTER TABLE `ProvisioningResponse` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProvisioningResponse` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Rate`
--

DROP TABLE IF EXISTS `Rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Rate` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `RecordType` bigint(20) unsigned NOT NULL,
  `ServiceType` int(11) unsigned NOT NULL,
  `PassThrough` tinyint(1) unsigned NOT NULL,
  `StdUnits` bigint(20) unsigned NOT NULL,
  `StdRatePerUnit` decimal(17,8) NOT NULL,
  `StdFlagfall` decimal(13,4) NOT NULL,
  `StdPercentage` decimal(13,4) NOT NULL,
  `StdMarkup` decimal(17,8) NOT NULL,
  `StdMinCharge` decimal(13,4) NOT NULL,
  `ExsUnits` bigint(20) unsigned NOT NULL,
  `ExsRatePerUnit` decimal(17,8) NOT NULL,
  `ExsFlagfall` decimal(13,4) NOT NULL,
  `ExsPercentage` decimal(13,4) NOT NULL,
  `ExsMarkup` decimal(17,8) NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `Monday` tinyint(1) unsigned NOT NULL,
  `Tuesday` tinyint(1) unsigned NOT NULL,
  `Wednesday` tinyint(1) unsigned NOT NULL,
  `Thursday` tinyint(1) unsigned NOT NULL,
  `Friday` tinyint(1) unsigned NOT NULL,
  `Saturday` tinyint(1) unsigned NOT NULL,
  `Sunday` tinyint(1) unsigned NOT NULL,
  `Destination` bigint(20) unsigned NOT NULL DEFAULT '0',
  `CapUnits` bigint(20) unsigned NOT NULL,
  `CapCost` decimal(13,4) NOT NULL,
  `CapUsage` bigint(20) unsigned NOT NULL,
  `CapLimit` decimal(13,4) NOT NULL,
  `Prorate` tinyint(1) unsigned NOT NULL,
  `Fleet` tinyint(1) unsigned NOT NULL,
  `Uncapped` tinyint(1) unsigned NOT NULL,
  `Archived` tinyint(1) NOT NULL,
  `discount_percentage` float DEFAULT NULL COMMENT 'A percentage amount for a rate after the discount cap has been reached',
  `allow_cdr_hiding` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Allows Zero-Rated CDRs to be hidden on the Invoice (must also be set at the RatePlan level); 0: Normal behaviour',
  `rate_class_id` int(10) unsigned DEFAULT NULL COMMENT '(FK) Rate Class this Rate belongs to',
  PRIMARY KEY (`Id`),
  KEY `RecordType` (`RecordType`),
  KEY `ServiceType` (`ServiceType`),
  KEY `StartTime` (`StartTime`),
  KEY `EndTime` (`EndTime`),
  KEY `Monday` (`Monday`),
  KEY `Tuesday` (`Tuesday`),
  KEY `Wednesday` (`Wednesday`),
  KEY `Thursday` (`Thursday`),
  KEY `Friday` (`Friday`),
  KEY `Saturday` (`Saturday`),
  KEY `Sunday` (`Sunday`),
  KEY `StartTime_2` (`StartTime`,`EndTime`,`Monday`,`Tuesday`,`Wednesday`,`Thursday`,`Friday`,`Saturday`,`Sunday`),
  KEY `Destination` (`Destination`),
  KEY `Fleet` (`Fleet`),
  KEY `Archived` (`Archived`),
  KEY `fk_rate_rate_class_id` (`rate_class_id`),
  CONSTRAINT `fk_rate_rate_class_id` FOREIGN KEY (`rate_class_id`) REFERENCES `rate_class` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Rate`
--

LOCK TABLES `Rate` WRITE;
/*!40000 ALTER TABLE `Rate` DISABLE KEYS */;
/*!40000 ALTER TABLE `Rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RateGroup`
--

DROP TABLE IF EXISTS `RateGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RateGroup` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `RecordType` bigint(20) unsigned NOT NULL,
  `ServiceType` int(10) unsigned NOT NULL,
  `Fleet` tinyint(4) NOT NULL DEFAULT '0',
  `CapLimit` decimal(13,4) DEFAULT NULL COMMENT 'Dollar amount of the plan cap which will be specifically applied to this RateGroup',
  `Archived` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `RecordType` (`RecordType`),
  KEY `ServiceType` (`ServiceType`),
  KEY `Fleet` (`Fleet`),
  KEY `Archived` (`Archived`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RateGroup`
--

LOCK TABLES `RateGroup` WRITE;
/*!40000 ALTER TABLE `RateGroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `RateGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RateGroupRate`
--

DROP TABLE IF EXISTS `RateGroupRate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RateGroupRate` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `RateGroup` bigint(20) unsigned NOT NULL,
  `Rate` bigint(20) unsigned NOT NULL,
  `effective_start_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Effective Start Datetime for this relationship',
  `effective_end_datetime` datetime NOT NULL DEFAULT '9999-12-31 23:59:59' COMMENT 'Effective End Datetime for this relationship',
  PRIMARY KEY (`Id`),
  KEY `RateGroup` (`RateGroup`),
  KEY `Rate` (`Rate`),
  KEY `in_rate_group_rate_effective_start_datetime` (`effective_start_datetime`),
  KEY `in_rate_group_rate_effective_end_datetime` (`effective_end_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RateGroupRate`
--

LOCK TABLES `RateGroupRate` WRITE;
/*!40000 ALTER TABLE `RateGroupRate` DISABLE KEYS */;
/*!40000 ALTER TABLE `RateGroupRate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RatePlan`
--

DROP TABLE IF EXISTS `RatePlan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RatePlan` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `ServiceType` int(10) unsigned NOT NULL,
  `Shared` tinyint(1) NOT NULL DEFAULT '0',
  `MinMonthly` decimal(13,4) NOT NULL,
  `InAdvance` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1: Minimum Monthly Charged in Advance; 0: Charged Normally',
  `ChargeCap` decimal(13,4) NOT NULL,
  `UsageCap` decimal(13,4) NOT NULL,
  `CarrierFullService` int(10) unsigned NOT NULL,
  `CarrierPreselection` int(10) unsigned NOT NULL,
  `ContractTerm` int(10) unsigned DEFAULT NULL COMMENT 'Contract Term in Months',
  `contract_exit_fee` decimal(13,4) NOT NULL DEFAULT '0.0000' COMMENT 'Contract Exit Fee',
  `contract_payout_percentage` decimal(13,4) NOT NULL DEFAULT '0.0000' COMMENT 'Contract Payout Percentage',
  `RecurringCharge` decimal(13,4) DEFAULT NULL COMMENT 'Monthly plan charge',
  `Archived` tinyint(1) NOT NULL,
  `discount_cap` float DEFAULT NULL COMMENT 'A dollar amount for an entire plan',
  `customer_group` bigint(20) unsigned NOT NULL COMMENT 'Customer Group that the RatePlan belongs to',
  `scalable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean flag to enable/disable Scalable Shared Plans',
  `minimum_services` int(11) unsigned DEFAULT NULL COMMENT 'The minimum number of Services that should be on this Plan (only for Reporting purposes atm)',
  `maximum_services` int(11) unsigned DEFAULT NULL COMMENT 'The maximum number of Services before Scaling is introduced',
  `allow_cdr_hiding` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Allows Zero-Rated CDRs to be hidden on the Invoice (must also be set at the Rate level); 0: Normal behaviour',
  `included_data` int(11) NOT NULL DEFAULT '0' COMMENT 'Included Data Allowance (in KB)',
  `brochure_document_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Brochure for this Plan',
  `auth_script_document_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Authorisation Script for this Plan',
  `product_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Product that this defines',
  `locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Changes from this Plan are restricted; 0: Anyone can change from this Plan',
  `cdr_required` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: CDRs are required for initial Plan Charges; 0: CDRs are not required for initial Plan Charges',
  `commissionable_value` decimal(13,4) NOT NULL COMMENT 'Commission Paid on this Plan',
  `created_employee_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '(FK) Employee who created this Plan',
  `modified_employee_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '(FK) Employee who last modified this Plan',
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(FK) Employee who created this Plan',
  `override_default_rate_plan_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `ServiceType` (`ServiceType`),
  KEY `Archived` (`Archived`),
  KEY `fk_rate_plan_brochure_document_id` (`brochure_document_id`),
  KEY `fk_rate_plan_auth_script_document_id` (`auth_script_document_id`),
  KEY `fk_rate_plan_product_id` (`product_id`),
  KEY `fk_rate_plan_created_employee_id` (`created_employee_id`),
  KEY `fk_rate_plan_modified_employee_id` (`modified_employee_id`),
  KEY `fk_rate_plan_override_default_rate_plan_id` (`override_default_rate_plan_id`),
  CONSTRAINT `fk_rate_plan_auth_script_document_id` FOREIGN KEY (`auth_script_document_id`) REFERENCES `document` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_plan_brochure_document_id` FOREIGN KEY (`brochure_document_id`) REFERENCES `document` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_plan_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_plan_modified_employee_id` FOREIGN KEY (`modified_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_plan_override_default_rate_plan_id` FOREIGN KEY (`override_default_rate_plan_id`) REFERENCES `RatePlan` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_plan_product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RatePlan`
--

LOCK TABLES `RatePlan` WRITE;
/*!40000 ALTER TABLE `RatePlan` DISABLE KEYS */;
/*!40000 ALTER TABLE `RatePlan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RatePlanRateGroup`
--

DROP TABLE IF EXISTS `RatePlanRateGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RatePlanRateGroup` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `RatePlan` bigint(20) unsigned NOT NULL,
  `RateGroup` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `RatePlan` (`RatePlan`),
  KEY `RateGroup` (`RateGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RatePlanRateGroup`
--

LOCK TABLES `RatePlanRateGroup` WRITE;
/*!40000 ALTER TABLE `RatePlanRateGroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `RatePlanRateGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RecordType`
--

DROP TABLE IF EXISTS `RecordType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RecordType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Code` varchar(25) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `ServiceType` int(10) unsigned NOT NULL,
  `Context` int(10) unsigned DEFAULT NULL,
  `Required` tinyint(1) NOT NULL,
  `Itemised` tinyint(1) NOT NULL,
  `GroupId` bigint(20) unsigned NOT NULL,
  `DisplayType` int(11) unsigned NOT NULL,
  `global_tax_exempt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: This RecordType WILL NOT have the Global Tax Rate applied; 0: This RecordType WILL have the Global Tax Rate applied',
  PRIMARY KEY (`Id`),
  KEY `Code` (`Code`),
  KEY `ServiceType` (`ServiceType`),
  KEY `Context` (`Context`),
  KEY `Group` (`GroupId`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RecordType`
--

LOCK TABLES `RecordType` WRITE;
/*!40000 ALTER TABLE `RecordType` DISABLE KEYS */;
INSERT INTO `RecordType` VALUES (1,'MonthlyUsage','Monthly Usage','ADSL Monthly Usage',100,0,1,1,1,2,0),(2,'Mobile','Mobile to Mobile','Mobile to Mobile',101,5,0,1,2,1,0),(3,'DELETED','DELETED','DELETED',999,0,1,1,2,1,0),(4,'DELETED','DELETED','DELETED',999,0,1,1,2,1,0),(5,'DELETED','DELETED','DELETED',999,0,1,1,2,1,0),(6,'National','Mobile to National','Mobile to National',101,0,1,1,6,1,0),(7,'Freecall','Mobile to 1800 Numbers','Mobile to 1800 Numbers',101,0,1,1,16,1,0),(8,'VoiceMailRetrieval','Mobile VoiceMail Retrieval','Mobile VoiceMail Retrieval',101,0,1,1,8,1,0),(9,'VoiceMailDeposit','Mobile VoiceMail Deposit','Mobile VoiceMail Deposit',101,0,1,1,9,1,0),(10,'SMS','Mobile SMS','Mobile Originated SMS',101,0,1,0,10,4,0),(11,'Roaming','Mobile International Roaming','Mobile International Roaming',101,0,1,1,11,1,1),(12,'GPRS','GPRS','GPRS Data',101,0,1,0,12,3,0),(13,'DELETED','DELETED','DELETED',999,0,1,1,16,1,0),(14,'OSNetworkAirtime','Mobile Overseas Network Airtime Fee','Mobile Overseas Network Airtime',101,0,1,1,14,1,1),(15,'MMS','Mobile MMS','Mobile Originated MMS',101,0,1,0,10,4,0),(16,'Other','Mobile Other','Mobile Other Charges',101,0,1,1,16,1,0),(17,'Local','Local','Local Calls',102,0,1,0,17,1,0),(18,'ProgramLocal','Program Local','Programmed Local Calls',102,0,1,0,17,1,0),(19,'National','National','National Calls',102,0,1,1,19,1,0),(20,'Mobile','Calls to Mobile','Calls to Mobile',102,6,1,1,20,1,0),(21,'S&E','Service and Equipment','Service and Equipment',102,2,1,1,21,2,0),(22,'DELETED','DELETED','DELETED',999,0,0,0,22,2,0),(23,'OneNineHundred','Calls to 1900','Calls to 1900',102,0,1,1,23,1,0),(24,'OneThree','Calls to 1300','Calls to 13/1300',102,0,1,1,24,1,0),(25,'ZeroOneNine','Calls to 019','Calls to 019',102,0,1,1,25,1,0),(26,'Other','Other','Other Charges',102,0,1,1,26,1,0),(27,'IDD','International Direct Dial','Mobile International Direct Dial',101,1,0,1,27,1,0),(28,'IDD','International Direct Dial','International Direct Dial',102,1,0,1,28,1,0),(29,'Other','Other Inbound','Other Inbound',103,0,0,1,29,1,0),(30,'S&E','S&E','Inbound Service & Equipment',103,0,0,0,30,2,0),(31,'','','',0,0,0,0,0,0,0),(32,'OC&C','Other Charges and Credits','Other Charges and Credits',102,0,1,1,21,2,0),(33,'SMS','SMS','Landline Originated SMS',102,0,1,0,33,4,0),(34,'Local','Local','Inbound Local',103,0,1,1,34,1,0),(35,'National','National','Inbound National',103,0,1,1,35,1,0),(36,'MobileToFixed','Mobile to Fixed','Inbound Mobile to Fixed',103,0,1,1,36,1,0),(37,'FixedToMobile','Fixed to Mobile','Inbound Fixed to Mobile',103,0,1,1,37,1,0),(38,'MobileToMobile','Mobile to Mobile','Inbound Mobile to Mobile',103,0,1,1,38,1,0),(39,'3G','3G','3G Data',101,4,0,0,39,3,0),(42,'OneThree','Mobile to 1300','Mobile to 13/1300',101,0,1,1,42,1,0),(43,'OC&C','Other Charges & Credits','Other Charges & Credits',101,3,0,1,43,2,0);
/*!40000 ALTER TABLE `RecordType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RecurringCharge`
--

DROP TABLE IF EXISTS `RecurringCharge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RecurringCharge` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned DEFAULT NULL,
  `CreatedBy` bigint(20) unsigned DEFAULT NULL,
  `ApprovedBy` bigint(20) DEFAULT NULL,
  `ChargeType` varchar(10) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Nature` enum('DR','CR') NOT NULL,
  `CreatedOn` date NOT NULL,
  `StartedOn` date NOT NULL,
  `LastChargedOn` date DEFAULT NULL,
  `RecurringFreqType` int(10) unsigned NOT NULL,
  `RecurringFreq` int(10) unsigned NOT NULL,
  `MinCharge` decimal(13,4) NOT NULL,
  `RecursionCharge` decimal(13,4) NOT NULL,
  `CancellationFee` decimal(13,4) NOT NULL,
  `Continuable` tinyint(1) unsigned NOT NULL,
  `in_advance` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Charged in Advance; 0: Charged in Arrears',
  `PlanCharge` tinyint(1) unsigned NOT NULL,
  `UniqueCharge` tinyint(1) unsigned NOT NULL,
  `TotalCharged` decimal(13,4) NOT NULL,
  `TotalRecursions` mediumint(9) unsigned NOT NULL,
  `recurring_charge_status_id` int(10) unsigned NOT NULL COMMENT '(FK) Recurring Charge Status',
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service` (`Service`),
  KEY `ChargeType` (`ChargeType`),
  KEY `Nature` (`Nature`),
  KEY `CreatedOn` (`CreatedOn`),
  KEY `StartedOn` (`StartedOn`),
  KEY `LastChargedOn` (`LastChargedOn`),
  KEY `RecurringFreqType` (`RecurringFreqType`),
  KEY `RecurringFreq` (`RecurringFreq`),
  KEY `MinCharge` (`MinCharge`),
  KEY `RecursionCharge` (`RecursionCharge`),
  KEY `CancellationFee` (`CancellationFee`),
  KEY `Continuable` (`Continuable`),
  KEY `PlanCharge` (`PlanCharge`),
  KEY `UniqueCharge` (`UniqueCharge`),
  KEY `TotalCharged` (`TotalCharged`),
  KEY `TotalRecursions` (`TotalRecursions`),
  KEY `fk_recurring_charge_recurring_charge_status_id` (`recurring_charge_status_id`),
  CONSTRAINT `fk_recurring_charge_recurring_charge_status_id` FOREIGN KEY (`recurring_charge_status_id`) REFERENCES `recurring_charge_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RecurringCharge`
--

LOCK TABLES `RecurringCharge` WRITE;
/*!40000 ALTER TABLE `RecurringCharge` DISABLE KEYS */;
/*!40000 ALTER TABLE `RecurringCharge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RecurringChargeType`
--

DROP TABLE IF EXISTS `RecurringChargeType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RecurringChargeType` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ChargeType` varchar(10) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Nature` enum('DR','CR') NOT NULL,
  `Fixed` tinyint(1) unsigned NOT NULL,
  `RecurringFreqType` int(10) unsigned NOT NULL,
  `RecurringFreq` int(10) unsigned NOT NULL,
  `MinCharge` decimal(13,4) NOT NULL,
  `RecursionCharge` decimal(13,4) NOT NULL,
  `CancellationFee` decimal(13,4) NOT NULL,
  `Continuable` tinyint(1) unsigned NOT NULL,
  `PlanCharge` tinyint(1) unsigned NOT NULL,
  `UniqueCharge` tinyint(1) unsigned NOT NULL,
  `approval_required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1=RecCharges require approval; 0=don''t require approval',
  `Archived` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RecurringChargeType`
--

LOCK TABLES `RecurringChargeType` WRITE;
/*!40000 ALTER TABLE `RecurringChargeType` DISABLE KEYS */;
/*!40000 ALTER TABLE `RecurringChargeType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Service`
--

DROP TABLE IF EXISTS `Service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Service` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `EtechId` varchar(30) DEFAULT NULL,
  `FNN` char(25) NOT NULL,
  `ServiceType` int(10) unsigned NOT NULL,
  `residential` tinyint(1) DEFAULT NULL COMMENT '1: Residential Service; 0: Business Service',
  `Indial100` tinyint(1) unsigned NOT NULL,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `CostCentre` bigint(20) unsigned DEFAULT NULL,
  `CappedCharge` decimal(13,4) NOT NULL,
  `UncappedCharge` decimal(13,4) NOT NULL,
  `CreatedOn` datetime NOT NULL,
  `CreatedBy` bigint(20) unsigned NOT NULL,
  `NatureOfCreation` int(10) unsigned DEFAULT NULL COMMENT 'Identifies the reason why this Service Record was created',
  `ClosedOn` datetime DEFAULT NULL,
  `ClosedBy` bigint(20) unsigned DEFAULT NULL,
  `NatureOfClosure` int(10) unsigned DEFAULT NULL COMMENT 'Identifies the reason why this Service Record was closed',
  `Carrier` int(10) unsigned DEFAULT NULL,
  `CarrierPreselect` int(10) unsigned DEFAULT NULL,
  `EarliestCDR` datetime DEFAULT NULL,
  `LatestCDR` datetime DEFAULT NULL,
  `LineStatus` int(10) unsigned DEFAULT NULL,
  `LineStatusDate` datetime DEFAULT NULL COMMENT 'The Date and Time when the Line Status was last updated',
  `PreselectionStatus` int(11) DEFAULT NULL,
  `PreselectionStatusDate` datetime DEFAULT NULL COMMENT 'Date the Preselection Status was last updated',
  `ForceInvoiceRender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1: Service will appear on an Invoice even with no CDRs; 0: Service will not appear if there are no CDRs',
  `LastOwner` bigint(20) unsigned DEFAULT NULL COMMENT 'Identifies the Account which last owned this FNN',
  `NextOwner` bigint(20) unsigned DEFAULT NULL COMMENT 'Identifies the Account which next owned this FNN',
  `Status` int(10) unsigned NOT NULL DEFAULT '400',
  `Dealer` bigint(20) unsigned DEFAULT NULL COMMENT 'The associated Dealer',
  `Cost` decimal(13,4) NOT NULL DEFAULT '0.0000' COMMENT 'The cost of the service',
  `cdr_count` int(11) DEFAULT NULL COMMENT 'The number of Unbilled CDRs the Service had at the last Rating run',
  `cdr_amount` float DEFAULT NULL,
  `discount_start_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `ServiceType` (`ServiceType`),
  KEY `Indial100` (`Indial100`),
  KEY `Carrier` (`Carrier`),
  KEY `CarrierPreselect` (`CarrierPreselect`),
  KEY `LineStatus` (`LineStatus`),
  KEY `FNN` (`FNN`),
  KEY `CostCentre` (`CostCentre`),
  KEY `CreatedOn` (`CreatedOn`),
  KEY `ClosedOn` (`ClosedOn`),
  KEY `DealerId` (`Dealer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Service`
--

LOCK TABLES `Service` WRITE;
/*!40000 ALTER TABLE `Service` DISABLE KEYS */;
/*!40000 ALTER TABLE `Service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceAddress`
--

DROP TABLE IF EXISTS `ServiceAddress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceAddress` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned NOT NULL,
  `Residential` tinyint(1) NOT NULL,
  `BillName` varchar(30) NOT NULL,
  `BillAddress1` varchar(30) NOT NULL,
  `BillAddress2` varchar(30) DEFAULT NULL,
  `BillLocality` varchar(23) NOT NULL,
  `BillPostcode` char(4) NOT NULL,
  `EndUserTitle` varchar(4) DEFAULT NULL,
  `EndUserGivenName` varchar(30) DEFAULT NULL,
  `EndUserFamilyName` varchar(50) DEFAULT NULL,
  `EndUserCompanyName` varchar(50) DEFAULT NULL,
  `DateOfBirth` char(8) DEFAULT NULL,
  `Employer` varchar(30) DEFAULT NULL,
  `Occupation` varchar(30) DEFAULT NULL,
  `ABN` char(11) DEFAULT NULL,
  `TradingName` varchar(50) DEFAULT NULL,
  `ServiceAddressType` varchar(3) DEFAULT NULL,
  `ServiceAddressTypeNumber` varchar(5) DEFAULT NULL,
  `ServiceAddressTypeSuffix` varchar(2) DEFAULT NULL,
  `ServiceStreetNumberStart` varchar(5) DEFAULT NULL,
  `ServiceStreetNumberEnd` varchar(5) DEFAULT NULL,
  `ServiceStreetNumberSuffix` varchar(1) DEFAULT NULL,
  `ServiceStreetName` varchar(30) DEFAULT NULL,
  `ServiceStreetType` varchar(4) DEFAULT NULL,
  `ServiceStreetTypeSuffix` varchar(2) DEFAULT NULL,
  `ServicePropertyName` varchar(30) DEFAULT NULL,
  `ServiceLocality` varchar(30) NOT NULL,
  `ServiceState` varchar(3) NOT NULL,
  `ServicePostcode` char(4) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Service` (`Service`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service_2` (`Service`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceAddress`
--

LOCK TABLES `ServiceAddress` WRITE;
/*!40000 ALTER TABLE `ServiceAddress` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceAddress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceExtension`
--

DROP TABLE IF EXISTS `ServiceExtension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceExtension` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Service` bigint(20) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  `RangeStart` int(11) NOT NULL,
  `RangeEnd` int(11) NOT NULL,
  `CostCentre` bigint(20) unsigned DEFAULT NULL,
  `Archived` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`),
  KEY `Service` (`Service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceExtension`
--

LOCK TABLES `ServiceExtension` WRITE;
/*!40000 ALTER TABLE `ServiceExtension` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceExtension` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceInboundDetail`
--

DROP TABLE IF EXISTS `ServiceInboundDetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceInboundDetail` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Service` bigint(20) unsigned NOT NULL,
  `AnswerPoint` char(25) NOT NULL,
  `Complex` tinyint(1) NOT NULL,
  `Configuration` text NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Service` (`Service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceInboundDetail`
--

LOCK TABLES `ServiceInboundDetail` WRITE;
/*!40000 ALTER TABLE `ServiceInboundDetail` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceInboundDetail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceMobileDetail`
--

DROP TABLE IF EXISTS `ServiceMobileDetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceMobileDetail` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned NOT NULL,
  `SimPUK` varchar(50) NOT NULL,
  `SimESN` char(15) NOT NULL,
  `SimState` char(3) NOT NULL,
  `DOB` date NOT NULL,
  `Comments` longtext NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Service` (`Service`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service_2` (`Service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceMobileDetail`
--

LOCK TABLES `ServiceMobileDetail` WRITE;
/*!40000 ALTER TABLE `ServiceMobileDetail` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceMobileDetail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceRateGroup`
--

DROP TABLE IF EXISTS `ServiceRateGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceRateGroup` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Service` bigint(20) unsigned NOT NULL,
  `RateGroup` bigint(20) unsigned NOT NULL,
  `CreatedBy` bigint(20) unsigned NOT NULL,
  `CreatedOn` datetime NOT NULL,
  `StartDatetime` datetime NOT NULL,
  `EndDatetime` datetime NOT NULL,
  `Active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Id`),
  KEY `Service` (`Service`),
  KEY `RateGroup` (`RateGroup`),
  KEY `StartDatetime` (`StartDatetime`),
  KEY `EndDatetime` (`EndDatetime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceRateGroup`
--

LOCK TABLES `ServiceRateGroup` WRITE;
/*!40000 ALTER TABLE `ServiceRateGroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceRateGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceRatePlan`
--

DROP TABLE IF EXISTS `ServiceRatePlan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceRatePlan` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Service` bigint(20) unsigned NOT NULL,
  `RatePlan` bigint(20) unsigned NOT NULL,
  `CreatedBy` bigint(20) unsigned NOT NULL,
  `CreatedOn` datetime NOT NULL,
  `StartDatetime` datetime NOT NULL,
  `EndDatetime` datetime NOT NULL,
  `contract_scheduled_end_datetime` datetime DEFAULT NULL COMMENT 'Scheduled Contract End Date',
  `contract_effective_end_datetime` datetime DEFAULT NULL COMMENT 'Scheduled Contract End Date',
  `contract_status_id` bigint(20) DEFAULT NULL COMMENT '(FK) The Status of this Contract',
  `contract_breach_reason_id` bigint(20) DEFAULT NULL COMMENT '(FK) Reason why the Contract was breached',
  `contract_breach_reason_description` varchar(512) DEFAULT NULL COMMENT 'Description of why the Contract was breached',
  `contract_payout_percentage` decimal(13,4) DEFAULT NULL COMMENT 'Actual Contract Payout Percentage',
  `contract_payout_charge_id` bigint(20) DEFAULT NULL COMMENT '(FK) Charge which corresponds to the Contract Payout',
  `exit_fee_charge_id` bigint(20) DEFAULT NULL COMMENT '(FK) Charge which corresponds to the Exit Fee',
  `contract_breach_fees_charged_on` datetime DEFAULT NULL COMMENT 'Date and time the Contract Breach Fees were applied',
  `contract_breach_fees_employee_id` bigint(20) DEFAULT NULL COMMENT '(FK) Employee who charges the Contract Breach Fees',
  `contract_breach_fees_reason` varchar(512) DEFAULT NULL COMMENT 'Reason for approving/waiving the Contract Fees',
  `LastChargedOn` datetime DEFAULT NULL,
  `Active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Id`),
  KEY `Service` (`Service`),
  KEY `RatePlan` (`RatePlan`),
  KEY `CreatedOn` (`CreatedOn`),
  KEY `StartDatetime` (`StartDatetime`),
  KEY `EndDatetime` (`EndDatetime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceRatePlan`
--

LOCK TABLES `ServiceRatePlan` WRITE;
/*!40000 ALTER TABLE `ServiceRatePlan` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceRatePlan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceTotal`
--

DROP TABLE IF EXISTS `ServiceTotal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceTotal` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FNN` char(25) NOT NULL,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned NOT NULL,
  `RatePlan` bigint(20) unsigned NOT NULL,
  `service_rate_plan` bigint(20) unsigned DEFAULT NULL,
  `invoice_run_id` bigint(20) DEFAULT NULL COMMENT 'FK to InvoiceRun table',
  `CappedCost` decimal(13,4) NOT NULL,
  `UncappedCost` decimal(13,4) NOT NULL,
  `CappedCharge` decimal(13,4) NOT NULL,
  `UncappedCharge` decimal(13,4) NOT NULL,
  `TotalCharge` decimal(13,4) NOT NULL,
  `Credit` decimal(13,4) NOT NULL,
  `Debit` decimal(13,4) NOT NULL,
  `PlanCharge` decimal(13,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `RecordType` (`Service`,`invoice_run_id`),
  KEY `FNN` (`FNN`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service` (`Service`),
  KEY `CappedCharge` (`CappedCharge`),
  KEY `UncappedCharge` (`UncappedCharge`),
  KEY `TotalCharge` (`TotalCharge`),
  KEY `Credit` (`Credit`),
  KEY `Debit` (`Debit`),
  KEY `invoice_run_id` (`invoice_run_id`),
  KEY `service_rate_plan` (`service_rate_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceTotal`
--

LOCK TABLES `ServiceTotal` WRITE;
/*!40000 ALTER TABLE `ServiceTotal` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceTotal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ServiceTypeTotal`
--

DROP TABLE IF EXISTS `ServiceTypeTotal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ServiceTypeTotal` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FNN` char(25) NOT NULL,
  `AccountGroup` bigint(20) unsigned NOT NULL,
  `Account` bigint(20) unsigned NOT NULL,
  `Service` bigint(20) unsigned NOT NULL,
  `RateGroup` bigint(20) unsigned NOT NULL,
  `invoice_run_id` bigint(20) DEFAULT NULL COMMENT 'FK to InvoiceRun table',
  `RecordType` bigint(20) unsigned NOT NULL,
  `Cost` decimal(13,4) NOT NULL,
  `Charge` decimal(13,4) NOT NULL,
  `Units` bigint(20) unsigned NOT NULL,
  `Records` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `RecordType` (`Service`,`invoice_run_id`,`RecordType`,`FNN`),
  KEY `FNN` (`FNN`),
  KEY `AccountGroup` (`AccountGroup`),
  KEY `Account` (`Account`),
  KEY `Service` (`Service`),
  KEY `RecordType_2` (`RecordType`),
  KEY `Charge` (`Charge`),
  KEY `Units` (`Units`),
  KEY `Records` (`Records`),
  KEY `invoice_run_id` (`invoice_run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ServiceTypeTotal`
--

LOCK TABLES `ServiceTypeTotal` WRITE;
/*!40000 ALTER TABLE `ServiceTypeTotal` DISABLE KEYS */;
/*!40000 ALTER TABLE `ServiceTypeTotal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UIAppDocumentation`
--

DROP TABLE IF EXISTS `UIAppDocumentation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UIAppDocumentation` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Object` varchar(50) NOT NULL,
  `Property` varchar(50) NOT NULL,
  `Context` int(10) unsigned NOT NULL,
  `ValidationRule` longtext COMMENT 'This can be a regular expression or a list of comma seperated methods belonging to the Validation class.  The value is valid so long as ALL the validation rules return TRUE.  All regular expressions must have the prefix "REGEX:"',
  `InputType` varchar(50) NOT NULL,
  `OutputType` varchar(50) NOT NULL,
  `Label` char(255) NOT NULL,
  `OutputLabel` varchar(255) DEFAULT NULL,
  `OutputMask` varchar(255) DEFAULT NULL,
  `Class` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=472 DEFAULT CHARSET=utf8 COMMENT='This is a newer version of the documentation table.  It is u';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UIAppDocumentation`
--

LOCK TABLES `UIAppDocumentation` WRITE;
/*!40000 ALTER TABLE `UIAppDocumentation` DISABLE KEYS */;
INSERT INTO `UIAppDocumentation` VALUES (9,'KnowledgeBase','Id',0,NULL,'InputText','Label','Doc Id',NULL,NULL,'Default'),(10,'KnowledgeBase','Title',0,NULL,'InputText','Label','Title',NULL,NULL,'Default'),(11,'KnowledgeBase','Abstract',0,NULL,'InputText','Label','Abstract',NULL,NULL,'Default'),(12,'KnowledgeBase','Content',0,NULL,'InputText','Label','Content',NULL,NULL,'Default'),(14,'KnowledgeBase','CreatedOn',0,'DateAndTime','DateAndTimeInput','Label','Created On',NULL,'LongDateAndTime','Default'),(15,'KnowledgeBase','LastUpdated',0,'DateAndTime','DateAndTimeInput','Label','Last Updated',NULL,'LongDateAndTime','Default'),(17,'KnowledgeBase','AuthorisedOn',0,'DateAndTime','DateAndTimeInput','Label','Authorised On',NULL,'LongDateAndTime','Default'),(22,'InvoicePayment','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(23,'InvoicePayment','InvoiceRun',0,NULL,'InputText','Label','Invoice Run',NULL,NULL,'Default'),(24,'InvoicePayment','Account',0,'AccountNumber','InputText','Label','Account',NULL,NULL,'Default'),(25,'InvoicePayment','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(26,'InvoicePayment','Payment',0,NULL,'InputText','Label','Payment',NULL,NULL,'Default'),(27,'InvoicePayment','Amount',0,NULL,'InputText','Label','Amount',NULL,'Currency2DecPlaces','Currency'),(28,'Payment','Id',0,NULL,'InputText','Label','Payment Id',NULL,NULL,'Default'),(29,'Payment','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(30,'Payment','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(31,'Payment','PaidOn',0,NULL,'InputText','Label','Paid On',NULL,'ShortDate','Default'),(32,'Payment','PaymentType',0,NULL,'InputText','Label','Payment Type',NULL,NULL,'Default'),(33,'Payment','Amount',0,'IsMoneyValue','InputText','Label','Amount ($)',NULL,'Method:MoneyValue(<value>)','Currency'),(35,'Payment','TXNReference',0,'IsNotEmptyString','InputText','Label','TXN Reference',NULL,NULL,'Default'),(36,'Payment','EnteredBy',0,NULL,'InputText','Label','Entered By',NULL,NULL,'Default'),(37,'Payment','Payment',0,NULL,'InputText','Label','Payment',NULL,NULL,'Default'),(38,'Payment','File',0,NULL,'InputText','Label','File',NULL,NULL,'Default'),(39,'Payment','SequenceNo',0,NULL,'InputText','Label','Sequence No',NULL,NULL,'Default'),(40,'Payment','Balance',0,NULL,'InputText','Label','Balance ($)',NULL,'Method:MoneyValue(<value>)','Currency'),(41,'Payment','Status',0,NULL,'InputText','Label','Status',NULL,NULL,'Default'),(42,'Account','Id',0,NULL,'InputText','Label','Account Number',NULL,NULL,'Default'),(43,'Account','BusinessName',0,NULL,'InputText','Label','Business Name',NULL,NULL,'Default'),(44,'Account','TradingName',0,NULL,'InputText','Label','Trading Name',NULL,NULL,'Default'),(45,'Account','ABN',0,'Optional: IsValidABN','InputText','Label','ABN',NULL,NULL,'Default'),(46,'Account','ACN',0,NULL,'InputText','Label','ACN',NULL,NULL,'Default'),(47,'Account','Address1',0,NULL,'InputText','Label','Address line 1',NULL,NULL,'Default'),(48,'Account','Address2',0,NULL,'InputText','Label','Address line 2',NULL,NULL,'Default'),(49,'Account','Suburb',0,NULL,'InputText','Label','Suburb',NULL,NULL,'Default'),(50,'Account','Postcode',0,'Optional: IsValidPostcode','InputText','Label','Postcode',NULL,NULL,'Default'),(51,'Account','State',0,NULL,'InputText','Label','State',NULL,NULL,'Default'),(52,'Account','Country',0,NULL,'InputText','Label','Country',NULL,NULL,'Default'),(53,'Account','BillingType',0,NULL,'InputText','Label','Billing Type','',NULL,'Default'),(54,'Account','PrimaryContact',0,NULL,'InputText','Label','Primary Contact',NULL,NULL,'Default'),(55,'Account','CustomerGroup',0,NULL,'InputText','Label','Customer Group',NULL,NULL,'Default'),(56,'Account','CreditCard',0,NULL,'InputText','Label','Credit Card',NULL,NULL,'Default'),(57,'Account','DirectDebit',0,NULL,'InputText','Label','Direct Debit',NULL,NULL,'Default'),(58,'Account','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(59,'Account','LastBilled',0,NULL,'InputText','Label','Last Billed',NULL,NULL,'Default'),(60,'Account','BillingDate',0,NULL,'InputText','Label','Billing Date',NULL,NULL,'Default'),(61,'Account','BillingFreq',0,NULL,'InputText','Label','Billing Frequency',NULL,NULL,'Default'),(62,'Account','BillingFreqType',0,NULL,'InputText','Label','Billing Frequency Type',NULL,NULL,'Default'),(63,'Account','BillingMethod',0,NULL,'InputText','Label','Billing Method',NULL,NULL,'Default'),(64,'Account','PaymentTerms',0,NULL,'InputText','Label','Payment Terms',NULL,NULL,'Default'),(65,'Account','CreatedBy',0,NULL,'InputText','Label','Created By',NULL,NULL,'Default'),(66,'Account','CreatedOn',0,NULL,'InputText','Label','Created On',NULL,NULL,'Default'),(67,'Account','DisableDDR',0,NULL,'CheckBox','Label','Do not charge an admin fee',NULL,'BooleanYesNo','Default'),(68,'Account','DisableLatePayment',0,NULL,'RadioButtons','Label','Late Payment Fee','Don\'t charge late payment for next <value> invoices',NULL,'Default'),(69,'Account','Archived',0,NULL,'InputText','Label','Account Status',NULL,NULL,'Default'),(70,'AccountGroup','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(71,'AccountGroup','CreatedBy',0,NULL,'InputText','Label','Created By',NULL,NULL,'Default'),(72,'AccountGroup','CreatedOn',0,NULL,'InputText','Label','Created On',NULL,NULL,'Default'),(73,'AccountGroup','ManagedBy',0,NULL,'InputText','Label','Managed By',NULL,NULL,'Default'),(74,'AccountGroup','Archived',0,NULL,'InputText','Label','Archived',NULL,NULL,'Default'),(75,'Employee','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(76,'Employee','FirstName',0,NULL,'InputText','Label','First Name',NULL,NULL,'Default'),(77,'Employee','LastName',0,NULL,'InputText','Label','Last Name',NULL,NULL,'Default'),(78,'Employee','UserName',0,NULL,'InputText','Label','User Name',NULL,NULL,'Default'),(79,'Employee','Password',0,NULL,'InputPassword','Label','Password',NULL,NULL,'Default'),(80,'Employee','Phone',0,NULL,'InputText','Label','Phone',NULL,NULL,'Default'),(81,'Employee','Mobile',0,NULL,'InputText','Label','Mobile',NULL,NULL,'Default'),(82,'Employee','Extension',0,NULL,'InputText','Label','Extension',NULL,NULL,'Default'),(83,'Employee','Email',0,NULL,'InputText','Label','Email',NULL,NULL,'Default'),(84,'Employee','DOB',0,NULL,'InputShortDate','Label','DOB',NULL,'method:ShortDate(\"<value>\")','Default'),(85,'Employee','SessionId',0,NULL,'InputText','Label','Session Id',NULL,NULL,'Default'),(86,'Employee','SessionExpire',0,NULL,'InputText','Label','Session Expire',NULL,NULL,'Default'),(87,'Employee','Session',0,NULL,'InputText','Label','Session',NULL,NULL,'Default'),(88,'Employee','Karma',0,NULL,'InputText','Label','Karma',NULL,NULL,'Default'),(89,'Employee','PabloSays',0,NULL,'InputText','Label','Pablo Says',NULL,NULL,'Default'),(90,'Employee','Privileges',0,NULL,'InputText','Label','Priviledges',NULL,NULL,'Default'),(91,'Employee','Archived',0,NULL,'CheckBox2','Label','Archived','Active',NULL,'Default'),(92,'EmployeeAccountAudit','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(93,'EmployeeAccountAudit','Employee',0,NULL,'InputText','Label','Employee',NULL,NULL,'Default'),(94,'EmployeeAccountAudit','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(95,'EmployeeAccountAudit','Contact',0,NULL,'InputText','Label','Contact',NULL,NULL,'Default'),(96,'EmployeeAccountAudit','RequestedOn',0,'DateAndTime','InputText','Label','Requested On',NULL,NULL,'Default'),(97,'Charge','Id',0,NULL,'InputText','Label','Adjustment Id',NULL,NULL,'Default'),(98,'Charge','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(99,'Charge','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(100,'Charge','Service',0,NULL,'InputText','Label','Service',NULL,NULL,'Default'),(101,'Charge','InvoiceRun',0,NULL,'InputText','Label','Invoice Run',NULL,NULL,'Default'),(102,'Charge','CreatedBy',0,NULL,'InputText','Label','Created By',NULL,NULL,'Default'),(103,'Charge','CreatedOn',0,NULL,'InputText','Label','Created On',NULL,'ShortDate','Default'),(104,'Charge','ApprovedBy',0,NULL,'InputText','Label','Approved By',NULL,NULL,'Default'),(105,'Charge','ChargeType',0,NULL,'InputText','Label','Charge Type',NULL,NULL,'Default'),(106,'Charge','Description',0,NULL,'InputText','Label','Description',NULL,NULL,'Default'),(107,'Charge','ChargedOn',0,NULL,'InputText','Label','Charged On',NULL,'ShortDate','Default'),(108,'Charge','Nature',0,NULL,'InputText','Label','Nature',NULL,NULL,'Default'),(109,'Charge','Amount',0,'IsMoneyValue','InputText','Label','Amount ($)',NULL,'Method:MoneyValue(<value>, 2, FALSE)','Currency'),(110,'Charge','Invoice',0,NULL,'InputText','Label','Invoice',NULL,NULL,'Default'),(111,'Charge','Notes',0,NULL,'TextArea','Label','Notes',NULL,NULL,'Default'),(112,'Charge','Status',0,NULL,'InputText','Label','Status',NULL,NULL,'Default'),(113,'ChargeType','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(114,'ChargeType','ChargeType',0,NULL,'InputText','Label','Charge Type',NULL,NULL,'Default'),(115,'ChargeType','Description',0,NULL,'InputText','Label','Description',NULL,NULL,'Default'),(116,'ChargeType','Nature',0,NULL,'InputText','Label','Nature',NULL,NULL,'Default'),(117,'ChargeType','Fixed',0,NULL,'InputText','Label','Fixed',NULL,NULL,'Default'),(118,'ChargeType','Amount',0,NULL,'InputText','Label','Amount',NULL,'Currency2DecPlaces','Currency'),(119,'ChargeType','Archived',0,NULL,'InputText','Label','Archived',NULL,NULL,'Default'),(120,'Note','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(121,'Note','Note',0,'IsNotEmptyString','TextArea','Label','Note',NULL,NULL,'Default'),(122,'Note','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(123,'Note','Contact',0,NULL,'InputText','Label','Contact',NULL,NULL,'Default'),(124,'Note','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(125,'Note','Service',0,NULL,'InputText','Label','Service',NULL,NULL,'Default'),(126,'Note','Employee',0,NULL,'InputText','Label','Employee',NULL,NULL,'Default'),(127,'Note','Datetime',0,'DateAndTime','InputText','Label','Datetime',NULL,'LongDateAndTime','Default'),(128,'Note','NoteType',0,NULL,'InputText','Label','NoteType',NULL,NULL,'Default'),(129,'NoteType','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(130,'NoteType','TypeLabel',0,NULL,'InputText','Label','TypeLabel',NULL,NULL,'Default'),(131,'NoteType','BorderColor',0,NULL,'InputText','Label','Border Color',NULL,NULL,'Default'),(132,'NoteType','BackgroundColor',0,NULL,'InputText','Label','Background Color',NULL,NULL,'Default'),(133,'NoteType','TextColor',0,NULL,'InputText','Label','Text Color',NULL,NULL,'Default'),(134,'RecurringCharge','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(135,'RecurringCharge','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(136,'RecurringCharge','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(137,'RecurringCharge','Service',0,NULL,'InputText','Label','Service',NULL,NULL,'Default'),(138,'RecurringCharge','CreatedBy',0,NULL,'InputText','Label','Created By',NULL,NULL,'Default'),(139,'RecurringCharge','ApprovedBy',0,NULL,'InputText','Label','Approved By',NULL,NULL,'Default'),(140,'RecurringCharge','ChargeType',0,NULL,'InputText','Label','Charge Type',NULL,NULL,'Default'),(141,'RecurringCharge','Description',0,NULL,'InputText','Label','Description',NULL,NULL,'Default'),(142,'RecurringCharge','Nature',0,NULL,'InputText','Label','Nature',NULL,NULL,'Default'),(143,'RecurringCharge','CreatedOn',0,NULL,'InputText','Label','Created On',NULL,'ShortDate','Default'),(144,'RecurringCharge','StartedOn',0,NULL,'InputText','Label','Started On',NULL,'ShortDate','Default'),(145,'RecurringCharge','LastChargedOn',0,NULL,'InputText','Label','Last Charged On',NULL,'ShortDate','Default'),(146,'RecurringCharge','RecurringFreqType',0,NULL,'InputText','Label','Recurring Frequency Type',NULL,NULL,'Default'),(147,'RecurringCharge','RecurringFreq',0,NULL,'InputText','Label','Recurring Frequency',NULL,NULL,'Default'),(148,'RecurringCharge','MinCharge',0,'IsMoneyValue','InputText','Label','Minimum Charge',NULL,'Currency2DecPlaces','Currency'),(149,'RecurringCharge','RecursionCharge',0,'IsMoneyValue','InputText','Label','Recurring Charge',NULL,'Currency2DecPlaces','Currency'),(150,'RecurringCharge','CancellationFee',0,NULL,'InputText','Label','Cancellation Fee',NULL,'Currency2DecPlaces','Currency'),(151,'RecurringCharge','Continuable',0,NULL,'InputText','Label','Continuable',NULL,'BooleanYesNo','Default'),(152,'RecurringCharge','PlanCharge',0,NULL,'InputText','Label','Plan Charge',NULL,NULL,'Default'),(153,'RecurringCharge','UniqueCharge',0,NULL,'InputText','Label','Unique Charge',NULL,'BooleanYesNo','Default'),(154,'RecurringCharge','TotalCharged',0,NULL,'InputText','Label','Amount Already Charged',NULL,'Currency2DecPlaces','Currency'),(155,'RecurringCharge','TotalRecursions',0,NULL,'InputText','Label','Times Charged',NULL,NULL,'Default'),(156,'RecurringCharge','Archived',0,NULL,'InputText','Label','Archived',NULL,'BooleanYesNo','Default'),(157,'RecurringChargeType','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(158,'RecurringChargeType','ChargeType',0,NULL,'InputText','Label','ChargeType',NULL,NULL,'Default'),(159,'RecurringChargeType','Description',0,NULL,'InputText','Label','Description',NULL,NULL,'Default'),(160,'RecurringChargeType','Nature',0,NULL,'InputText','Label','Nature',NULL,NULL,'Default'),(161,'RecurringChargeType','Fixed',0,NULL,'InputText','Label','Fixed',NULL,NULL,'Default'),(162,'RecurringChargeType','RecurringFreqType',0,NULL,'InputText','Label','Recurring Frequency Type',NULL,NULL,'Default'),(163,'RecurringChargeType','RecurringFreq',0,NULL,'InputText','Label','Recurring Frequency',NULL,NULL,'Default'),(164,'RecurringChargeType','MinCharge',0,NULL,'InputText','Label','Minimum Charge',NULL,'Currency2DecPlaces','Currency'),(165,'RecurringChargeType','RecursionCharge',0,NULL,'InputText','Label','Recursion Charge',NULL,'Currency2DecPlaces','Currency'),(166,'RecurringChargeType','CancellationFee',0,NULL,'InputText','Label','Cancellation Fee',NULL,'Currency2DecPlaces','Currency'),(167,'RecurringChargeType','Continuable',0,NULL,'InputText','Label','Continuable',NULL,NULL,'Default'),(168,'RecurringChargeType','PlanCharge',0,NULL,'InputText','Label','Plan Charge',NULL,NULL,'Default'),(169,'RecurringChargeType','UniqueCharge',0,NULL,'InputText','Label','Unique Charge',NULL,NULL,'Default'),(170,'RecurringChargeType','Archived',0,NULL,'InputText','Label','Archived',NULL,NULL,'Default'),(171,'Account','Balance',1,NULL,'NA','Label','Balance',NULL,'Method:MoneyValue(<value>, 2, TRUE)','Red Currency'),(173,'Account','Balance',0,NULL,'NA','Label','Balance',NULL,'Currency2DecPlaces','Currency'),(182,'Account','Overdue',0,NULL,'NA','Label','Overdue',NULL,'Currency2DecPlaces','Currency'),(183,'Account','TotalUnbilledAdjustments',0,NULL,'NA','Label','Total Un-billed Adjustments',NULL,'Currency2DecPlaces','Currency'),(186,'Invoice','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(187,'Invoice','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(188,'Invoice','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(189,'Invoice','CreatedOn',0,NULL,'InputText','Label','Created On',NULL,'ShortDate','Default'),(190,'Invoice','DueOn',0,NULL,'InputText','Label','Due On',NULL,'ShortDate','Default'),(191,'Invoice','SettledOn',0,NULL,'InputText','Label','Settled On',NULL,'ShortDate','Default'),(192,'Invoice','Credits',0,NULL,'InputText','Label','Credits',NULL,'Currency2DecPlaces','Currency'),(193,'Invoice','Debits',0,NULL,'InputText','Label','Debits',NULL,'Currency2DecPlaces','Currency'),(194,'Invoice','Total',0,NULL,'InputText','Label','Total',NULL,'Method:MoneyValue(<value>, 2, FALSE)','Currency'),(195,'Invoice','Tax',0,NULL,'InputText','Label','Tax',NULL,'Currency2DecPlaces','Currency'),(196,'Invoice','TotalOwing',0,NULL,'InputText','Label','Total Owing (shown on invoice)',NULL,'Currency2DecPlaces','Currency'),(197,'Invoice','Balance',0,NULL,'InputText','Label','Balance',NULL,'Method:MoneyValue(<value>, 2, FALSE)','Currency'),(198,'Invoice','Disputed',0,NULL,'InputText','Label','Disputed',NULL,'Currency2DecPlaces','Currency'),(199,'Invoice','AccountBalance',0,NULL,'InputText','Label','Account Balance',NULL,'Currency2DecPlaces','Currency'),(200,'Invoice','Status',0,NULL,'InputText','Label','Status',NULL,NULL,'Default'),(201,'Invoice','InvoiceRun',0,NULL,'InputText','Label','Invoice Run',NULL,NULL,'Default'),(202,'Contact','Email',0,'IsValidEmail','InputText','EmailLinkLabel','Email Address',NULL,NULL,'Default'),(204,'Payment','AmountApplied',0,NULL,'NA','Label','Amount Applied ($)',NULL,'Method:MoneyValue(<value>)','Currency'),(205,'Invoice','Amount',0,NULL,'InputText','Label','Invoice Amount',NULL,'Method:MoneyValue(<value>, 2, FALSE)','Currency'),(206,'Invoice','AppliedAmount',0,NULL,'InputText','Label','Applied Amount',NULL,'Method:MoneyValue(<value>, 2, FALSE)','Currency'),(209,'Status','Message',0,NULL,'NA','Label','Status',NULL,NULL,'StatusMessage'),(210,'AccountToApplyTo','Id',0,NULL,'NA','NA','NA',NULL,NULL,'Default'),(211,'AccountToApplyTo','IsGroup',0,NULL,'NA','NA','This should only ever be renderred as hidden',NULL,NULL,'Default'),(212,'ChargeTypesAvailable','RecurringFreqType',0,NULL,'InputText','Label','Recurring Frequency Type',NULL,NULL,'Default'),(213,'ChargeTypesAvailable','Nature',0,NULL,'NA','Label','Nature',NULL,NULL,'Default'),(214,'DeleteRecord','Description',0,NULL,'NA','Label','NA',NULL,NULL,'Default'),(215,'Invoice','Year',0,NULL,'InputText','Label','Year',NULL,NULL,'Default'),(216,'Invoice','Month',0,NULL,'InputText','Label','Month',NULL,NULL,'Default'),(217,'RecurringCharge','TotalAdditionalCharge',0,NULL,'NA','Label','Total Additional Charge',NULL,'Currency2DecPlaces','Currency'),(220,'Charge','Amount',1,'IsMoneyValue','InputText','Label','Amount (inc GST)',NULL,'Currency2DecPlaces','Currency'),(221,'RecurringCharge','MinCharge',1,'IsMoneyValue','InputText','Label','Minimum Charge (inc GST)',NULL,'Currency2DecPlaces','Currency'),(222,'RecurringCharge','RecursionCharge',1,'IsMoneyValue','InputText','Label','Recurring Charge (inc GST)',NULL,'Currency2DecPlaces','Currency'),(223,'RecurringCharge','CancellationFee',1,NULL,'InputText','Label','Cancellation Fee (inc GST)',NULL,'Currency2DecPlaces','Currency'),(224,'RecurringCharge','TotalCharged',1,NULL,'Label','Label','Already Charged (inc GST)',NULL,'Currency2DecPlaces','Currency'),(225,'RecurringChargeType','CancellationFee',1,NULL,'Label','Label','Cancellation Fee (inc GST)',NULL,'Currency2DecPlaces','Currency'),(226,'RatePlan','Name',0,'IsNotEmptyString','InputText','Label','Name',NULL,NULL,'Default'),(227,'RatePlan','ServiceType',0,NULL,'InputText','Label','Service Type',NULL,NULL,'Default'),(229,'RatePlan','Description',0,'IsNotEmptyString','InputText','Label','Description',NULL,NULL,'Default'),(230,'RatePlan','Archived',0,NULL,'CheckBox','Label','Archived','Available',NULL,'Green'),(231,'RatePlan','Archived',1,NULL,'CheckBox','Label','Archived','Archived',NULL,'Red'),(232,'RatePlan','ChargeCap',0,'IsMoneyValue','InputText','Label','Cap Charge ($)',NULL,'Method:MoneyValue(<value>)','Default'),(233,'RatePlan','UsageCap',0,'IsMoneyValue','InputText','Label','Cap Limit ($)',NULL,'Method:MoneyValue(<value>)','Default'),(234,'RatePlan','MinMonthly',0,'IsMoneyValue','InputText','Label','Minimum Monthly Spend ($)',NULL,'Method:MoneyValue(<value>)','Default'),(235,'RatePlan','Shared',0,NULL,'CheckBox2','Label','Shared','Non-Shared Plan',NULL,'Default'),(236,'RatePlan','Shared',1,NULL,'CheckBox2','Label','Shared','Shared Plan',NULL,'Default'),(237,'Rate','Name',0,'IsNotEmptyString','InputText','Label','Name',NULL,NULL,'Default'),(238,'Rate','Description',0,NULL,'InputText','Label','Description',NULL,NULL,'Default'),(239,'Rate','StdRatePerUnit',0,NULL,'InputText','Label','Charge ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(240,'Rate','StdUnits',0,NULL,'InputText','Label','Standard Billing Units',NULL,NULL,'Default'),(241,'Rate','StdFlagfall',0,'IsMoneyValue','InputText','Label','Flagfall ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(242,'Rate','StdFlagfall',1,NULL,'InputText','Label','Flagfall','No Flagfall',NULL,'Red'),(244,'Rate','StdMinCharge',0,'IsMoneyValue','InputText','Label','Minimum Charge ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(245,'Rate','StdMinCharge',1,NULL,'InputText','Label','Minimum Charge','No Minimum Charge',NULL,'Red'),(246,'Rate','Monday',0,NULL,'CheckBox','Label','Availability','Mo',NULL,'Red'),(247,'Rate','Monday',1,NULL,'CheckBox','Label','Availability','Mo',NULL,'Green'),(248,'Rate','Tuesday',0,NULL,'CheckBox','Label','Availability','Tu',NULL,'Red'),(249,'Rate','Tuesday',1,NULL,'CheckBox','Label','Availability','Tu',NULL,'Green'),(250,'Rate','Wednesday',0,NULL,'CheckBox','Label','Availability','We',NULL,'Red'),(251,'Rate','Wednesday',1,NULL,'CheckBox','Label','Availability','We',NULL,'Green'),(252,'Rate','Thursday',0,NULL,'CheckBox','Label','Availability','Th',NULL,'Red'),(253,'Rate','Thursday',1,NULL,'CheckBox','Label','Availability','Th',NULL,'Green'),(254,'Rate','Friday',0,NULL,'CheckBox','Label','Availability','Fr',NULL,'Red'),(255,'Rate','Friday',1,NULL,'CheckBox','Label','Availability','Fr',NULL,'Green'),(256,'Rate','Saturday',0,NULL,'CheckBox','Label','Availability','Sa',NULL,'Red'),(257,'Rate','Saturday',1,NULL,'CheckBox','Label','Availability','Sa',NULL,'Green'),(258,'Rate','Sunday',0,NULL,'CheckBox','Label','Availability','Su',NULL,'Red'),(259,'Rate','Sunday',1,NULL,'CheckBox','Label','Availability','Su',NULL,'Green'),(260,'Rate','ServiceType',0,NULL,'InputText','Label','Service Type',NULL,NULL,'Default'),(261,'Rate','RecordType',0,NULL,'InputText','Label','Record Type',NULL,NULL,'Default'),(262,'Rate','StartTime',0,NULL,'InputText','Label','Starting At',NULL,NULL,'Default'),(263,'Rate','EndTime',0,NULL,'InputText','Label','Ending At',NULL,NULL,'Default'),(264,'RecordType','Name',0,NULL,'InputText','Label','Record Type',NULL,NULL,'Default'),(265,'Rate','AvailableDays',0,NULL,'','Label','Availability',NULL,NULL,'Default'),(266,'Rate','AvailableTime',0,NULL,'','Label','Available Times',NULL,NULL,'Default'),(267,'Rate','Duration',0,NULL,'InputText','Label','Duration','',NULL,'Default'),(268,'Contact','Id',0,NULL,'InputText','Label','Contact Id',NULL,NULL,'Default'),(269,'Contact','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(270,'Contact','Title',0,'IsNotEmptyString','InputText','Label','Title',NULL,NULL,'Default'),(271,'Contact','FirstName',0,'IsNotEmptyString','InputText','Label','First Name',NULL,NULL,'Default'),(272,'Contact','LastName',0,'IsNotEmptyString','InputText','Label','Last Name',NULL,NULL,'Default'),(273,'Contact','FullName',0,NULL,'NA','Label','Contact',NULL,NULL,'Default'),(274,'Contact','DOB',0,'ShortDate','InputText','Label','Date of Birth',NULL,'ShortDate','Default'),(275,'Contact','JobTitle',0,'IsNotEmptyString','InputText','Label','Job Title',NULL,NULL,'Default'),(276,'Contact','Phone',0,NULL,'InputText','Label','Phone Number',NULL,NULL,'Default'),(277,'Contact','Mobile',0,NULL,'InputText','Label','Mobile Number',NULL,NULL,'Default'),(278,'Contact','UserName',0,'IsNotEmptyString','InputText','Label','Username',NULL,NULL,'Default'),(279,'Contact','CustomerContact',0,NULL,'RadioButtons','Label','Account Access',NULL,NULL,'Default'),(280,'Contact','Archived',0,NULL,'CheckBox','Label','Archived',NULL,NULL,'Default'),(281,'Note','IsAccountNote',0,NULL,'CheckBox2','Label','Show in Account Notes',NULL,NULL,'Default'),(282,'Note','NoteGroupId',0,NULL,'NA','Label','Note Group Id (Should never be output)',NULL,NULL,'Default'),(283,'Note','NoteClass',0,NULL,'NA','Label','Note Class (should never be output)',NULL,NULL,'Default'),(284,'Contact','Fax',0,NULL,'InputText','Label','Fax Number',NULL,NULL,'Default'),(285,'Contact','PassWord',0,NULL,'InputText','Label','Password',NULL,NULL,'Default'),(286,'Service','Id',0,NULL,'InputText','Label','Service ID',NULL,NULL,'Default'),(287,'Service','FNN',0,NULL,'InputText','Label','FNN',NULL,NULL,'Default'),(288,'Service','ServiceType',0,NULL,'InputText','Label','Service Type',NULL,NULL,'Default'),(289,'Service','Indial100',0,NULL,'CheckBox2','Label','Indial 100',NULL,'BooleanYesNo','Default'),(290,'Service','CreatedOn',0,NULL,'InputText','Label','Created On',NULL,'LongDate','Default'),(291,'Service','ClosedOn',0,NULL,'InputText','Label','Closed On',NULL,'LongDate','Default'),(292,'Service','ELB',0,NULL,'CheckBox2','Label','ELB',NULL,'BooleanYesNo','Default'),(293,'Service','TotalUnbilledCharges',0,NULL,'NA','Label','Unbilled Charges (inc GST)',NULL,'Currency2DecPlaces','Currency'),(294,'RatePlan','Name',1,NULL,'InputText','Label','Current Plan',NULL,NULL,'Default'),(295,'Service','FNNConfirm',0,NULL,'InputText','Label','Confirm FNN',NULL,NULL,'Default'),(296,'Service','Archive',0,NULL,'CheckBox','Label','Archive this Service',NULL,NULL,'Default'),(299,'Service','TotalUnbilled',0,NULL,'NA','Label','Total Unbilled (inc GST)',NULL,'Method:MoneyValue(<value>, 2, FALSE)','Currency'),(300,'Service','CurrentFNN',0,NULL,'NA','NA','',NULL,NULL,''),(301,'RecordType','DisplayType',0,NULL,'NA','Label','Display Type',NULL,NULL,'Default'),(302,'CDR','StartDatetime',0,NULL,'NA','Label','Start',NULL,'LongDateAndTime','Default'),(303,'CDR','Destination',0,NULL,'NA','Label','Destination',NULL,NULL,'Default'),(304,'CDR','Units',0,NULL,'NA','Label','Units',NULL,NULL,'Default'),(305,'CDR','Charge',0,NULL,'NA','Label','Charge',NULL,'Currency2DecPlaces','Default'),(306,'CDR','Credit',0,NULL,'NA','Label','Credit',NULL,NULL,'Default'),(307,'Service','ArchiveService',0,NULL,'CheckBox','','Archive This Service',NULL,NULL,'Default'),(308,'Service','ActivateService',0,NULL,'CheckBox','','Activate This Service',NULL,NULL,'Default'),(309,'Service','CancelScheduledClosure',0,NULL,'CheckBox','','Activate This Service',NULL,NULL,'Default'),(311,'Service','CostCentre',0,NULL,'InputText','Label','Cost Centre',NULL,NULL,'Default'),(312,'CostCentre','Name',0,NULL,'InputText','Label','Cost Centre',NULL,NULL,'Default'),(313,'ServiceMobileDetail','SimPUK',0,NULL,'InputText','Label','PUK #',NULL,NULL,'Default'),(314,'ServiceMobileDetail','SimESN',0,NULL,'InputText','Label','ESN #',NULL,NULL,'Default'),(315,'ServiceMobileDetail','SimState',0,NULL,'InputText','Label','State',NULL,NULL,'Default'),(316,'ServiceMobileDetail','DOB',0,'ShortDate','InputText','Label','Date of Birth',NULL,'ShortDate','Default'),(317,'ServiceMobileDetail','Comments',0,NULL,'InputText','Label','Comments',NULL,NULL,'Default'),(318,'ServiceInboundDetail','AnswerPoint',0,NULL,'InputText','Label','AnswerPoint',NULL,NULL,'Default'),(319,'ServiceInboundDetail','Configuration',0,NULL,'TextArea','Label','Configuration',NULL,NULL,'Default'),(323,'KnowledgeBase','ArticleId',0,NULL,'NA','Label','Article Id',NULL,NULL,'Default'),(324,'KnowledgeBase','CreatedBy',0,NULL,'NA','Label','Created By',NULL,NULL,'Default'),(325,'KnowledgeBase','AuthorisedBy',0,NULL,'NA','Label','Authorised By',NULL,NULL,'Default'),(326,'Account','UnbilledAdjustments',0,NULL,'NA','Label','Unbilled Adjustments',NULL,'Currency2DecWithNegAsCR','Currency'),(327,'Account','UnbilledCDRs',0,NULL,'NA','Label','Unbilled Calls',NULL,'Currency2DecWithNegAsCR','Currency'),(328,'Account','CustomerBalance',0,NULL,'NA','Label','Balance',NULL,'Currency2DecWithNegAsCR','Currency'),(329,'Service','Account',0,NULL,'InputText','Label','Account Number',NULL,NULL,'Default'),(331,'ServiceRateGroup','Id',0,NULL,'NA','Label','Id','','','Default'),(332,'ServiceRateGroup','RateGroup',0,NULL,'NA','Label','RateGroup',NULL,NULL,'Default'),(333,'ServiceRateGroup','CreatedOn',0,NULL,'NA','Label','CreatedOn',NULL,'ShortDate','Default'),(334,'ServiceRateGroup','StartDatetime',0,NULL,'NA','Label','StartDate',NULL,'ShortDate','Default'),(335,'ServiceRateGroup','EndDatetime',0,NULL,'NA','Label','EndDate',NULL,'ShortDate','Default'),(336,'RateGroup','Id',0,NULL,'NA','Label','Id',NULL,NULL,'Default'),(337,'RateGroup','Name',0,'IsNotEmptyString','InputText','Label','Name',NULL,NULL,'Default'),(338,'RateGroup','RecordType',0,NULL,'InputText','Label','Record Type',NULL,NULL,'Default'),(340,'RateGroup','Description',0,'IsNotEmptyString','InputText','Label','Description',NULL,NULL,'Default'),(341,'RateGroup','Fleet',0,NULL,'CheckBox2','Label','Fleet',NULL,'BooleanYesNo','Default'),(342,'RateGroup','RecordTypeName',0,NULL,'NA','Label','RecordType',NULL,NULL,'Default'),(343,'Service','ClosedOn',1,NULL,'InputText','Label','Closed On','No Close Pending',NULL,'Default'),(344,'ServiceAddress','AccountGroup',0,NULL,'InputText','Label','Account Group',NULL,NULL,'Default'),(345,'ServiceAddress','Account',0,NULL,'InputText','Label','Account',NULL,NULL,'Default'),(346,'ServiceAddress','Service',0,NULL,'InputText','Label','Service',NULL,NULL,'Default'),(347,'ServiceAddress','Residential',0,NULL,'RadioButtons','Label','Residential',NULL,NULL,'Default'),(348,'ServiceAddress','BillName',0,NULL,'InputText','Label','Bill Name',NULL,NULL,'Default'),(349,'ServiceAddress','BillAddress1',0,NULL,'InputText','Label','Address (line 1)',NULL,NULL,'Default'),(350,'ServiceAddress','BillAddress2',0,NULL,'InputText','Label','Address (line 2)',NULL,NULL,'Default'),(351,'ServiceAddress','BillLocality',0,NULL,'InputText','Label','Locality',NULL,NULL,'Default'),(352,'ServiceAddress','BillPostcode',0,NULL,'InputText','Label','Postcode',NULL,NULL,'Default'),(353,'ServiceAddress','EndUserTitle',0,NULL,'InputText','Label','End User Title',NULL,NULL,'Default'),(354,'ServiceAddress','EndUserGivenName',0,NULL,'InputText','Label','End User Given Name',NULL,NULL,'Default'),(355,'ServiceAddress','EndUserFamilyName',0,NULL,'InputText','Label','End User Family Name',NULL,NULL,'Default'),(356,'ServiceAddress','EndUserCompanyName',0,NULL,'InputText','Label','End User Company Name',NULL,NULL,'Default'),(357,'ServiceAddress','DateOfBirth',0,NULL,'InputText','Label','Date Of Birth',NULL,NULL,'Default'),(358,'ServiceAddress','Employer',0,NULL,'InputText','Label','Employer',NULL,NULL,'Default'),(359,'ServiceAddress','Occupation',0,NULL,'InputText','Label','Occupation',NULL,NULL,'Default'),(360,'ServiceAddress','ABN',0,NULL,'InputText','Label','ABN',NULL,NULL,'Default'),(361,'ServiceAddress','TradingName',0,NULL,'InputText','Label','Trading Name',NULL,NULL,'Default'),(362,'ServiceAddress','ServiceAddressType',0,NULL,'InputText','Label','Address Type',NULL,NULL,'Default'),(363,'ServiceAddress','ServiceAddressTypeNumber',0,NULL,'InputText','Label','AddressTypeNumber',NULL,NULL,'Default'),(364,'ServiceAddress','ServiceAddressTypeSuffix',0,NULL,'InputText','Label','AddressTypeSuffix',NULL,NULL,'Default'),(365,'ServiceAddress','ServiceStreetNumberStart',0,NULL,'InputText','Label','StreetNumberStart',NULL,NULL,'Default'),(366,'ServiceAddress','ServiceStreetNumberEnd',0,NULL,'InputText','Label','StreetNumberEnd',NULL,NULL,'Default'),(367,'ServiceAddress','ServiceStreetNumberSuffix',0,NULL,'InputText','Label','StreetNumberSuffix',NULL,NULL,'Default'),(368,'ServiceAddress','ServiceStreetName',0,NULL,'InputText','Label','StreetName',NULL,NULL,'Default'),(369,'ServiceAddress','ServiceStreetType',0,NULL,'InputText','Label','StreetType',NULL,NULL,'Default'),(370,'ServiceAddress','ServiceStreetTypeSuffix',0,NULL,'InputText','Label','StreetTypeSuffix',NULL,NULL,'Default'),(371,'ServiceAddress','ServicePropertyName',0,NULL,'InputText','Label','PropertyName',NULL,NULL,'Default'),(372,'ServiceAddress','ServiceLocality',0,NULL,'InputText','Label','Locality',NULL,NULL,'Default'),(373,'ServiceAddress','ServiceState',0,NULL,'InputText','Label','State',NULL,NULL,'Default'),(374,'ServiceAddress','ServicePostcode',0,NULL,'InputText','Label','Postcode',NULL,NULL,'Default'),(376,'RatePlan','Shared',2,NULL,'CheckBox2','Label','Shared',NULL,NULL,'Default'),(377,'RatePlan','CarrierFullService',0,'IsNotEmptyString','InputText','Label','Carrier Full Service',NULL,NULL,'Default'),(378,'RatePlan','CarrierPreselection',0,'IsNotEmptyString','InputText','Label','Carrier Preselection',NULL,NULL,'Default'),(379,'Rate','StdMarkup',0,NULL,'InputText','Label','Markup on Cost ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(380,'Rate','StdPercentage',0,NULL,'InputText','Label','Markup on Cost (%)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(382,'Rate','CapUnits',0,NULL,'InputText','Label','Start Capping at (Units)',NULL,NULL,'Default'),(383,'Rate','CapCost',0,NULL,'InputText','Label','Start Capping at ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(384,'Rate','Prorate',0,NULL,'CheckBox2','Label','Prorate',NULL,'BooleanYesNo','Default'),(385,'Rate','Fleet',0,NULL,'CheckBox2','Label','Fleet',NULL,'Method:BooleanYesNo(<value>)','Default'),(386,'Rate','Uncapped',0,NULL,'CheckBox2','Label','Excluded from Plan Cap',NULL,'BooleanYesNo','Default'),(387,'Rate','CapLimit',0,NULL,'InputText','Label','Stop Capping at ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(388,'Rate','CapUsage',0,NULL,'InputText','Label','Stop Capping at (Units)',NULL,NULL,'Default'),(389,'Rate','ExsUnits',0,NULL,'InputText','Label','Excess Billing Units',NULL,NULL,'Default'),(390,'Rate','ExsRatePerUnit',0,NULL,'InputText','Label','Excess Charge ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(391,'Rate','ExsMarkup',0,NULL,'InputText','Label','Excess Markup on Cost ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(392,'Rate','ExsPercentage',0,NULL,'InputText','Label','Excess Markup on Cost (%)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(393,'Rate','ExsFlagfall',0,NULL,'InputText','Label','Excess Flagfall ($)',NULL,'Method:FormatFloat(<value>, 2)','Default'),(394,'RateGroup','ServiceType',0,NULL,'InputText','Label','Service Type',NULL,NULL,'Default'),(395,'RecordType','Description',0,NULL,'NA','Label','Record Type',NULL,NULL,'Default'),(396,'Rate','PassThrough',0,NULL,'CheckBox2','Label','Charge at Cost',NULL,'BooleanYesNo','Default'),(397,'ServiceRateGroup','Name',0,NULL,'NA','Label','',NULL,NULL,'Default'),(398,'ServiceRateGroup','Description',0,NULL,'NA','Label','',NULL,NULL,'Default'),(399,'ServiceRateGroup','Fleet',0,NULL,'NA','Label','',NULL,NULL,'Default'),(400,'ServiceRateGroup','RecordType',0,NULL,'NA','Label','',NULL,NULL,'Default'),(401,'ServiceRateGroup','Fleet',0,NULL,'NA','Label','Fleet',NULL,NULL,'Default'),(402,'ServiceRateGroup','IsPartOfRatePlan',0,NULL,'NA','Label','Overridden','Testing',NULL,'Default'),(403,'Rate','Archived',0,NULL,'Checkbox','Checkbox','Archived',NULL,NULL,'Default'),(404,'Payment','ImportedOn',0,NULL,'NA','Label','Imported On',NULL,'ShortDate','Default'),(405,'Service','Status',0,NULL,'TextBox','Label','Service Status',NULL,NULL,'Default'),(406,'Service','Status',1,NULL,'NA','Label','Service Status','Not Specified',NULL,'Red'),(407,'RatePlanRateGroup','RateGroupName',0,NULL,'NA','Label','RateGroup',NULL,NULL,'Default'),(408,'Service','LineStatus',0,NULL,'NA','Label','Line Status',NULL,NULL,'Default'),(412,'Rate','Untimed',0,NULL,'Checkbox2','Checkbox2','Untimed',NULL,NULL,'Default'),(413,'Charge','FNN',0,NULL,'NA','Label','Service FNN',NULL,NULL,'Default'),(414,'RecurringCharge','FNN',0,NULL,'NA','Label','Service FNN',NULL,NULL,'Default'),(415,'DataReport','Name',0,'IsNotEmptyString','InputText','Label','Name',NULL,NULL,'Default'),(416,'DataReport','Summary',0,'IsNotEmptyString','TextArea','Label','Summary',NULL,NULL,'Default'),(417,'DataReport','FileName',0,NULL,'InputText','Label','FileName',NULL,NULL,'Default'),(418,'DataReport','RenderMode',0,NULL,'RadioButtons','RadioButtons','Render Mode',NULL,NULL,'Default'),(419,'DataReport','Priviledges',0,NULL,'RadioButtons','RadioButtons','Priviledges',NULL,NULL,'Default'),(420,'DataReport','SQLTable',0,'IsNotEmptyString','TextArea','Label','Tables (SQL \'FROM\')',NULL,NULL,'Default'),(421,'DataReport','SQLWhere',0,NULL,'TextArea','Label','Constraints (SQL \'WHERE\')',NULL,NULL,'Default'),(422,'DataReport','SQLGroupBy',0,NULL,'InputText','Label','Grouping (SQL \'GROUP BY\')',NULL,NULL,'Default'),(423,'DataReport','SQLOrderBy',0,NULL,'InputText','Label','Ordering (SQL \'ORDER BY\')',NULL,NULL,'Default'),(424,'DataReport','SQLQuery',0,NULL,'Label','Label','Compiled Query',NULL,NULL,'Default'),(425,'Rate','Destination',0,NULL,'NA','Label','Destination',NULL,NULL,'Default'),(426,'RecurringCharge','TimesToCharge',0,NULL,'NA','Label','Times To Charge',NULL,NULL,'Default'),(427,'RecurringCharge','EndDate',0,NULL,'NA','Label','End Date',NULL,NULL,'Default'),(428,'CurrentRatePlan','Name',0,NULL,'NA','Label','Current Plan',NULL,NULL,'Default'),(429,'FutureRatePlan','Name',0,NULL,'NA','Label','Future Plan',NULL,NULL,'Default'),(430,'RatePlan','StartDatetime',0,NULL,'NA','Label','Starting',NULL,'LongDate','Default'),(431,'RatePlan','EndDatetime',0,NULL,'NA','Label','Ending',NULL,'LongDate','Default'),(432,'ServiceRateGroup','StartDate',0,NULL,'InputText','Label','Start Date (dd/mm/yyyy)',NULL,NULL,'Default'),(433,'ServiceRateGroup','EndDate',0,NULL,'InputText','Label','End Date (dd/mm/yyyy)',NULL,NULL,'Default'),(434,'Destination','Description',0,NULL,'NA','Label','Destination',NULL,NULL,'Default'),(435,'Payment','CreditCardNum',0,NULL,'InputText','Label','Credit Card Number',NULL,NULL,'Default'),(436,'Payment','ChargeSurcharge',0,NULL,'CheckBox2','Label','Charge Surcharge',NULL,'BooleanYesNo','Default'),(437,'Account','Sample',0,NULL,'ComboBox','Label','Sample',NULL,NULL,'Default'),(438,'Account','DisableLatePayment',1,NULL,'RadioButtons2','Label','Late Payment Fee','Don\'t charge late payment for next <value>invoices',NULL,'Default'),(439,'Account','DisableLateNotices',0,NULL,'ComboBox','Label','Late Notices',NULL,NULL,'Default'),(440,'Account','DisableLateNotices',1,NULL,'RadioButtons','Label','Late Notices',NULL,NULL,'Default'),(441,'Account','DisableDDR',1,NULL,'CheckBox2','Label','Do not charge an admin fee',NULL,'BooleanYesNo','Default'),(442,'CustomerGroup','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(443,'CustomerGroup','InternalName',0,'IsNotEmptyString','InputText','Label','Internal Name',NULL,NULL,'Default'),(444,'CustomerGroup','OutboundEmail',0,'IsValidEmail','InputText','Label','Outbound Email',NULL,NULL,'Default'),(445,'CustomerGroup','ExternalName',0,'IsNotEmptyString','InputText','Label','External Name',NULL,NULL,'Default'),(446,'ConfigConstant','Id',0,NULL,'InputText','Label','Constant Id',NULL,NULL,'Default'),(447,'ConfigConstant','ConstantGroup',0,NULL,'InputText','Label','ConstantGroup',NULL,NULL,'Default'),(448,'ConfigConstant','Name',0,NULL,'InputText','Label','Name',NULL,NULL,'Default'),(449,'ConfigConstant','Description',0,NULL,'TextArea','Label','Description',NULL,NULL,'Default'),(450,'ConfigConstant','Value',0,NULL,'TextArea','Label','Value',NULL,NULL,'Default'),(451,'ConfigConstant','Type',0,NULL,'InputText','Label','Data Type',NULL,NULL,'Default'),(452,'ConfigConstant','ValueIsNull',0,NULL,'CheckBox2','Label','Set to Null',NULL,'BooleanYesNo','Default'),(453,'ConfigConstantGroup','Id',0,NULL,'InputText','Label','Id',NULL,NULL,'Default'),(454,'ConfigConstantGroup','Name',0,NULL,'InputText','Label','Name',NULL,NULL,'Default'),(455,'ConfigConstantGroup','Description',0,NULL,'InputText','Label','Description',NULL,NULL,'Default'),(456,'ConfigConstantGroup','Type',0,NULL,'InputText','Label','Type',NULL,NULL,'Default'),(457,'ConfigConstant','Editable',0,NULL,'CheckBox2','Label','Editable',NULL,'BooleanYesNo','Default'),(458,'ConfigConstant','Deletable',0,NULL,'CheckBox2','Label','Deletable',NULL,'BooleanYesNo','Default'),(459,'Account','LatePaymentAmnesty',0,NULL,'InputText','Label','Late Notices',NULL,NULL,'Default'),(460,'Rate','Times',0,NULL,'InputText','Label','Times',NULL,NULL,'Default'),(461,'Rate','CapStart',0,NULL,'InputText','Label','Start Capping at',NULL,NULL,'Default'),(462,'Rate','CapStop',0,NULL,'InputText','Label','Stop Capping at',NULL,NULL,'Default'),(463,'Rate','Status',0,NULL,'InputText','Label','Status',NULL,NULL,'Default'),(464,'Account','ChargeAdminFee',0,NULL,'CheckBox2','Label','Charge Admin Fee',NULL,'BooleanYesNo','Default'),(465,'Rate','SearchString',0,NULL,'InputText','Label','Search String',NULL,NULL,'Default'),(466,'RatePlan','InAdvance',0,NULL,'CheckBox2','Label','Charged in Advance',NULL,'BooleanYesNo','Default'),(467,'Service','ForceInvoiceRender',0,NULL,'CheckBox2','Label','Always shown on invoice',NULL,'BooleanYesNo','Default'),(468,'RatePlan','ContractTerm',0,'Optional: UnsignedInteger','InputText','Label','Contract Term (months)',NULL,NULL,'Default'),(469,'RatePlan','RecurringCharge',0,'Optional: IsMoneyValue','InputText','Label','Recurring Charge ($)',NULL,'Method:MoneyValue(<value>)','Default'),(470,'RatePlan','discount_cap',0,'Optional: IsMoneyValue','InputText','Label','Discount Cap ($)',NULL,'Method:MoneyValue(<value>)','Default'),(471,'Rate','discount_percentage',0,'Optional: UnsignedFloat','InputText','Label','Discount (%)',NULL,'Method:FormatFloat(<value>, 2)','Default');
/*!40000 ALTER TABLE `UIAppDocumentation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UIAppDocumentationOptions`
--

DROP TABLE IF EXISTS `UIAppDocumentationOptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UIAppDocumentationOptions` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Object` varchar(50) NOT NULL,
  `Property` varchar(50) NOT NULL,
  `Context` int(10) unsigned NOT NULL,
  `Value` varchar(255) NOT NULL DEFAULT '',
  `OutputLabel` varchar(255) DEFAULT NULL COMMENT 'This is what is output when the property is equal to Value.  You can use the placeholder <value> in this label',
  `InputLabel` varchar(255) DEFAULT NULL COMMENT 'This is what is output when the property is equal to Value.  You can use the placeholder <value> in this label',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='Relates to the UIAppDocumentation table.  To be used with ra';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UIAppDocumentationOptions`
--

LOCK TABLES `UIAppDocumentationOptions` WRITE;
/*!40000 ALTER TABLE `UIAppDocumentationOptions` DISABLE KEYS */;
INSERT INTO `UIAppDocumentationOptions` VALUES (7,'Account','DisableLatePayment',0,'0','Charge a late payment fee','Charge a late payment fee'),(11,'ChargeType','Nature',0,'CR','Credit','CR'),(12,'ChargeType','Nature',0,'DR','Debit','DR'),(13,'ChargeTypesAvailable','RecurringFreqType',0,'1','Day(s)','Day(s)'),(14,'ChargeTypesAvailable','RecurringFreqType',0,'2','Month(s)','Month(s)'),(15,'ChargeTypesAvailable','RecurringFreqType',0,'3','Half-Months','Half-Months'),(16,'ChargeTypesAvailable','Nature',0,'CR','Credit','Credit'),(17,'ChargeTypesAvailable','Nature',0,'DR','Debit','Debit'),(18,'Charge','Nature',0,'CR','Credit','CR'),(19,'Charge','Nature',0,'DR','Debit','DR'),(20,'Contact','CustomerContact',0,'0','Primary Account Only','Primary Account Only'),(21,'Contact','CustomerContact',0,'1','All Associated Accounts','All Associated Accounts'),(22,'Contact','Archived',0,'0','Active','Active'),(23,'Contact','Archived',0,'1','Archived','Archived'),(24,'CDR','Credit',0,'0','Debit','Debit'),(25,'CDR','Credit',0,'1','Credit','Credit'),(26,'ServiceAddress','Residential',0,'0','Business','Business'),(27,'ServiceAddress','Residential',0,'1','Residential','Residential'),(28,'ServiceRateGroup','Fleet',0,'0','No',NULL),(29,'ServiceRateGroup','Fleet',0,'1','Yes',NULL),(30,'ServiceRateGroup','IsPartOfRatePlan',0,'0','No',NULL),(31,'ServiceRateGroup','IsPartOfRatePlan',0,'1','Yes',NULL),(32,'Rate','PassThrough',0,'0','No',NULL),(33,'Rate','PassThrough',0,'1','Yes',NULL),(34,'Rate','Archived',0,'0','No',NULL),(35,'Rate','Archived',0,'1','Yes',NULL),(36,'Rate','Uncapped',0,'0','No',NULL),(37,'Rate','Uncapped',0,'1','Yes',NULL),(38,'Rate','Fleet',0,'0','No',NULL),(39,'Rate','Fleet',0,'1','Yes',NULL),(40,'Rate','Prorate',0,'0','No',NULL),(41,'Rate','Prorate',0,'1','Yes',NULL),(42,'DataReport','RenderMode',0,'0','Render Instantly','Render Instantly'),(43,'DataReport','RenderMode',0,'1','Render then Email','Render then Email'),(44,'DataReport','Priviledges',0,'1','Live Access','Live Access'),(45,'DataReport','Priviledges',0,'2147483648','Debug Access','Debug Access'),(46,'RecurringCharge','Nature',0,'CR','Credit','CR'),(47,'RecurringCharge','Nature',0,'DR','Debit','DR'),(48,'Account','Sample',0,'0','Don\'t Sample','Don\'t Sample'),(49,'Account','Sample',0,'1','Always Sample','Always Sample'),(50,'Account','Sample',0,'-1','Sample for 1 month','Sample for 1 month'),(51,'Account','DisableLatePayment',1,'0','Charge a late payment fee','Charge a late payment fee'),(52,'Account','DisableLatePayment',1,'-1','Don\'t charge a late payment fee on the next invoice','Don\'t charge a late payment fee on the next invoice'),(53,'Account','DisableLatePayment',1,'1','Never charge a late payment fee','Never charge a late payment fee'),(54,'Account','DisableLateNotices',0,'0','Send late notices','Send late notices'),(55,'Account','DisableLateNotices',0,'-1','Don\'t send late notices until next invoice','Don\'t send late notices until next invoice'),(56,'Account','DisableLateNotices',0,'1','Never send late notices','Never send late notices'),(57,'Account','DisableLateNotices',1,'0','Send late notices','Send late notices'),(58,'Account','DisableLateNotices',1,'-1','Don\'t send late notices until next invoice','Don\'t send late notices until next invoice'),(59,'Account','DisableLateNotices',1,'1','Never send late notices','Never send late notices'),(60,'Account','DisableLatePayment',0,'-1','Don\'t charge a late payment fee on the next invoice','Don\'t charge a late payment fee on the next invoice'),(61,'Account','DisableLatePayment',0,'1','Never charge a late payment fee','Never charge a late payment fee');
/*!40000 ALTER TABLE `UIAppDocumentationOptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_action`
--

DROP TABLE IF EXISTS `account_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account',
  `action_id` bigint(20) unsigned NOT NULL COMMENT '(FK) action',
  PRIMARY KEY (`id`),
  KEY `fk_account_action_account_id` (`account_id`),
  KEY `fk_account_action_action_id` (`action_id`),
  CONSTRAINT `fk_account_action_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_account_action_action_id` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_action`
--

LOCK TABLES `account_action` WRITE;
/*!40000 ALTER TABLE `account_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_barring_level`
--

DROP TABLE IF EXISTS `account_barring_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_barring_level` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  `authorised_datetime` datetime DEFAULT NULL,
  `authorised_employee_id` bigint(20) unsigned DEFAULT NULL,
  `barring_level_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_account_barring_level_created_datetime` (`created_datetime`),
  KEY `in_account_barring_level_authorised_datetime` (`authorised_datetime`),
  KEY `fk_account_barring_level_created_employee_id` (`created_employee_id`),
  KEY `fk_account_barring_level_authorised_employee_id` (`authorised_employee_id`),
  KEY `fk_account_barring_level_account_id` (`account_id`),
  KEY `fk_account_barring_level_barring_level_id` (`barring_level_id`),
  CONSTRAINT `fk_account_barring_level_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_barring_level_authorised_employee_id` FOREIGN KEY (`authorised_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_barring_level_barring_level_id` FOREIGN KEY (`barring_level_id`) REFERENCES `barring_level` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_barring_level_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_barring_level`
--

LOCK TABLES `account_barring_level` WRITE;
/*!40000 ALTER TABLE `account_barring_level` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_barring_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_class`
--

DROP TABLE IF EXISTS `account_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_class` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `collection_scenario_id` bigint(20) unsigned NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_class_collection_scenario_id` (`collection_scenario_id`),
  KEY `fk_account_class_status_id` (`status_id`),
  CONSTRAINT `fk_account_class_collection_scenario_id` FOREIGN KEY (`collection_scenario_id`) REFERENCES `collection_scenario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_class_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_class`
--

LOCK TABLES `account_class` WRITE;
/*!40000 ALTER TABLE `account_class` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_collection_event_history`
--

DROP TABLE IF EXISTS `account_collection_event_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_collection_event_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `collectable_id` bigint(20) unsigned NOT NULL,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `collection_scenario_collection_event_id` bigint(20) unsigned DEFAULT NULL,
  `scheduled_datetime` datetime NOT NULL,
  `completed_datetime` datetime DEFAULT NULL,
  `completed_employee_id` bigint(20) unsigned DEFAULT NULL,
  `account_collection_event_status_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_account_collection_event_history_scheduled_datetime` (`scheduled_datetime`),
  KEY `in_account_collection_event_history_completed_datetime` (`completed_datetime`),
  KEY `fk_account_collection_event_history_account_id` (`account_id`),
  KEY `fk_account_collection_event_history_scenario_event_id` (`collection_scenario_collection_event_id`),
  KEY `fk_account_collection_event_history_completed_employee_id` (`completed_employee_id`),
  KEY `fk_account_collection_event_history_collectable_id` (`collectable_id`),
  KEY `fk_account_collection_event_history_collection_event_id` (`collection_event_id`),
  KEY `fk_account_collection_event_history_account_event_status_id` (`account_collection_event_status_id`),
  CONSTRAINT `fk_account_collection_event_history_account_event_status_id` FOREIGN KEY (`account_collection_event_status_id`) REFERENCES `account_collection_event_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_event_history_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_event_history_collectable_id` FOREIGN KEY (`collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_event_history_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_event_history_completed_employee_id` FOREIGN KEY (`completed_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_event_history_scenario_event_id` FOREIGN KEY (`collection_scenario_collection_event_id`) REFERENCES `collection_scenario_collection_event` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_collection_event_history`
--

LOCK TABLES `account_collection_event_history` WRITE;
/*!40000 ALTER TABLE `account_collection_event_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_collection_event_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_collection_event_status`
--

DROP TABLE IF EXISTS `account_collection_event_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_collection_event_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_collection_event_status`
--

LOCK TABLES `account_collection_event_status` WRITE;
/*!40000 ALTER TABLE `account_collection_event_status` DISABLE KEYS */;
INSERT INTO `account_collection_event_status` VALUES (1,'Scheduled','Scheduled','SCHEDULED','ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED'),(2,'Completed','Completed','COMPLETED','ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED'),(3,'Cancelled','Cancelled','CANCELLED','ACCOUNT_COLLECTION_EVENT_STATUS_CANCELLED');
/*!40000 ALTER TABLE `account_collection_event_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_collection_scenario`
--

DROP TABLE IF EXISTS `account_collection_scenario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_collection_scenario` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `collection_scenario_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT '9999-12-31 23:59:59',
  PRIMARY KEY (`id`),
  KEY `in_account_collection_scenario_created_datetime` (`created_datetime`),
  KEY `in_account_collection_scenario_start_datetime` (`start_datetime`),
  KEY `in_account_collection_scenario_end_datetime` (`end_datetime`),
  KEY `fk_account_collection_scenario_account_id` (`account_id`),
  KEY `fk_account_collection_scenario_collection_scenario_id` (`collection_scenario_id`),
  CONSTRAINT `fk_account_collection_scenario_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_collection_scenario_collection_scenario_id` FOREIGN KEY (`collection_scenario_id`) REFERENCES `collection_scenario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_collection_scenario`
--

LOCK TABLES `account_collection_scenario` WRITE;
/*!40000 ALTER TABLE `account_collection_scenario` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_collection_scenario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_history`
--

DROP TABLE IF EXISTS `account_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
  `change_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time at which the change in the account record was made',
  `employee_id` bigint(20) unsigned DEFAULT NULL COMMENT 'reference to the employee who changed the state of the account record',
  `account_id` bigint(20) unsigned NOT NULL COMMENT 'foreign key into Account table',
  `billing_type` int(10) unsigned DEFAULT NULL COMMENT 'defines how the invoice is paid by the customer',
  `credit_card_id` bigint(20) unsigned DEFAULT NULL COMMENT 'defines the (direct debit) credit card details',
  `direct_debit_id` bigint(20) unsigned DEFAULT NULL COMMENT 'defines the (direct debit) bank account details',
  `billing_method` int(10) unsigned DEFAULT NULL COMMENT 'defines how the invoice is sent to the customer',
  `disable_ddr` tinyint(1) DEFAULT NULL COMMENT 'boolean flagging the disabling of the admin fee. 0 = charge lpf, 1 = don''t charge lpf',
  `late_payment_amnesty` date DEFAULT NULL COMMENT 'If this is set, no late payment notices are generated until after this date',
  `tio_reference_number` varchar(150) DEFAULT NULL COMMENT 'reference number when dealing with the T.I.O.',
  `account_class_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `employee_id` (`employee_id`),
  KEY `fk_account_history_account_class_id` (`account_class_id`),
  CONSTRAINT `fk_account_history_account_class_id` FOREIGN KEY (`account_class_id`) REFERENCES `account_class` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='history of account table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_history`
--

LOCK TABLES `account_history` WRITE;
/*!40000 ALTER TABLE `account_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_letter_log`
--

DROP TABLE IF EXISTS `account_letter_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_letter_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account this log entry belongs to',
  `invoice_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Invoice this log relates to',
  `document_template_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Document Template Type this log refers to',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Creation Timestamp',
  PRIMARY KEY (`id`),
  KEY `fk_account_letter_log_account_id` (`account_id`),
  KEY `fk_account_letter_log_invoice_id` (`invoice_id`),
  KEY `fk_account_letter_log_document_template_type_id` (`document_template_type_id`),
  CONSTRAINT `fk_account_letter_log_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_letter_log_document_template_type_id` FOREIGN KEY (`document_template_type_id`) REFERENCES `DocumentTemplateType` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_letter_log_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `Invoice` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores a log of all Account notices/letters generated';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_letter_log`
--

LOCK TABLES `account_letter_log` WRITE;
/*!40000 ALTER TABLE `account_letter_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_letter_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_oca_referral`
--

DROP TABLE IF EXISTS `account_oca_referral`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_oca_referral` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `account_collection_event_history_id` bigint(20) unsigned NOT NULL,
  `file_export_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_run_id` bigint(20) unsigned DEFAULT NULL,
  `account_oca_referral_status_id` int(10) unsigned NOT NULL,
  `actioned_datetime` datetime DEFAULT NULL,
  `actioned_employee_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_account_oca_referral_actioned_datetime` (`actioned_datetime`),
  KEY `fk_account_oca_referral_account_id` (`account_id`),
  KEY `fk_account_oca_referral_account_collection_event_history_id` (`account_collection_event_history_id`),
  KEY `fk_account_oca_referral_file_export_id` (`file_export_id`),
  KEY `fk_account_oca_referral_invoice_run_id` (`invoice_run_id`),
  KEY `fk_account_oca_referral_account_oca_referral_status_id` (`account_oca_referral_status_id`),
  KEY `fk_account_oca_referral_actioned_employee_id` (`actioned_employee_id`),
  CONSTRAINT `fk_account_oca_referral_account_collection_event_history_id` FOREIGN KEY (`account_collection_event_history_id`) REFERENCES `account_collection_event_history` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_oca_referral_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_oca_referral_account_oca_referral_status_id` FOREIGN KEY (`account_oca_referral_status_id`) REFERENCES `account_oca_referral_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_oca_referral_actioned_employee_id` FOREIGN KEY (`actioned_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_oca_referral_file_export_id` FOREIGN KEY (`file_export_id`) REFERENCES `FileExport` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_oca_referral_invoice_run_id` FOREIGN KEY (`invoice_run_id`) REFERENCES `InvoiceRun` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_oca_referral`
--

LOCK TABLES `account_oca_referral` WRITE;
/*!40000 ALTER TABLE `account_oca_referral` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_oca_referral` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_oca_referral_status`
--

DROP TABLE IF EXISTS `account_oca_referral_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_oca_referral_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_oca_referral_status`
--

LOCK TABLES `account_oca_referral_status` WRITE;
/*!40000 ALTER TABLE `account_oca_referral_status` DISABLE KEYS */;
INSERT INTO `account_oca_referral_status` VALUES (1,'Pending','Pending','PENDING','ACCOUNT_OCA_REFERRAL_STATUS_PENDING'),(2,'Complete','Complete','COMPLETE','ACCOUNT_OCA_REFERRAL_STATUS_COMPLETE'),(3,'Cancelled','Cancelled','CANCELLED','ACCOUNT_OCA_REFERRAL_STATUS_CANCELLED');
/*!40000 ALTER TABLE `account_oca_referral_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `account_services`
--

DROP TABLE IF EXISTS `account_services`;
/*!50001 DROP VIEW IF EXISTS `account_services`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `account_services` (
  `account_id` bigint(20) unsigned,
  `service_id` bigint(20) unsigned,
  `fnn` char(25)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `account_status`
--

DROP TABLE IF EXISTS `account_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the status',
  `name` varchar(50) NOT NULL COMMENT 'Name of the status',
  `can_invoice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Account can be Invoiced; 0: Account cannot be Invoiced',
  `deliver_invoice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Invoices can be delivered; 0: Invoices are never delivered',
  `can_bar` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether or not the account can be barred',
  `send_late_notice` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether or not to send late notices for account',
  `allow_customer_login` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'The Date on which the Billing Period starts',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Credit Control Status for accounts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_status`
--

LOCK TABLES `account_status` WRITE;
/*!40000 ALTER TABLE `account_status` DISABLE KEYS */;
INSERT INTO `account_status` VALUES (0,'Active',1,1,1,1,1,'Active','ACCOUNT_STATUS_ACTIVE'),(1,'Archived',0,0,0,0,1,'Archived','ACCOUNT_STATUS_ARCHIVED'),(2,'Closed',1,1,1,1,1,'Closed','ACCOUNT_STATUS_CLOSED'),(3,'Debt Collection',1,0,0,0,1,'Debt Collection','ACCOUNT_STATUS_DEBT_COLLECTION'),(4,'Suspended',0,0,0,0,1,'Suspended','ACCOUNT_STATUS_SUSPENDED'),(5,'Pending Activation',0,0,0,0,1,'Pending Activation','ACCOUNT_STATUS_PENDING_ACTIVATION');
/*!40000 ALTER TABLE `account_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_status_history`
--

DROP TABLE IF EXISTS `account_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for the historic event',
  `account` bigint(20) unsigned NOT NULL COMMENT 'Affected account',
  `from_status` bigint(20) unsigned NOT NULL COMMENT 'The original account status (Account.Archived)',
  `to_status` bigint(20) unsigned NOT NULL COMMENT 'The new account status (Account.Archived)',
  `employee` bigint(20) unsigned NOT NULL COMMENT 'Employee who effected the change',
  `change_datetime` datetime DEFAULT NULL COMMENT 'Date/Time of the change to this status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Account status change history';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_status_history`
--

LOCK TABLES `account_status_history` WRITE;
/*!40000 ALTER TABLE `account_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_tio_complaint`
--

DROP TABLE IF EXISTS `account_tio_complaint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_tio_complaint` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `collection_suspension_id` bigint(20) unsigned NOT NULL,
  `tio_reference_number` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_tio_complaint_account_id` (`account_id`),
  KEY `fk_account_tio_complaint_collection_suspension_id` (`collection_suspension_id`),
  CONSTRAINT `fk_account_tio_complaint_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_tio_complaint_collection_suspension_id` FOREIGN KEY (`collection_suspension_id`) REFERENCES `collection_suspension` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_tio_complaint`
--

LOCK TABLES `account_tio_complaint` WRITE;
/*!40000 ALTER TABLE `account_tio_complaint` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_tio_complaint` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_user`
--

DROP TABLE IF EXISTS `account_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `username` varchar(30) NOT NULL COMMENT 'Unique Username',
  `password` char(40) NOT NULL COMMENT 'Password (SHA1 Hash)',
  `given_name` varchar(50) NOT NULL COMMENT 'Given/First/Christian Name',
  `family_name` varchar(50) DEFAULT NULL COMMENT 'Family/Last Name',
  `email` varchar(256) NOT NULL COMMENT 'Email Address (for password recovery)',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account to which this User has access',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Active/Inactive Status',
  PRIMARY KEY (`id`),
  KEY `fk_account_user_account_id` (`account_id`),
  KEY `fk_account_user_status_id` (`status_id`),
  CONSTRAINT `fk_account_user_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_account_user_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_user`
--

LOCK TABLES `account_user` WRITE;
/*!40000 ALTER TABLE `account_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_user_log`
--

DROP TABLE IF EXISTS `account_user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_user_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_user_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account User that logged in',
  `created_datetime` datetime NOT NULL COMMENT 'Datetime of the login attempt',
  PRIMARY KEY (`id`),
  KEY `fk_account_user_log_account_user_idd` (`account_user_id`),
  CONSTRAINT `fk_account_user_log_account_user_idd` FOREIGN KEY (`account_user_id`) REFERENCES `account_user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_user_log`
--

LOCK TABLES `account_user_log` WRITE;
/*!40000 ALTER TABLE `account_user_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_user_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action`
--

DROP TABLE IF EXISTS `action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `action_type_id` smallint(5) unsigned NOT NULL COMMENT '(FK) type of action',
  `details` varchar(32767) DEFAULT NULL COMMENT 'Additional Details for the Action',
  `performed_by_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who performed the Action',
  `created_by_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who logged the Action',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Timestamp',
  PRIMARY KEY (`id`),
  KEY `fk_action_action_type_id` (`action_type_id`),
  KEY `fk_action_created_by_employee_id` (`created_by_employee_id`),
  KEY `fk_action_performed_by_employee_id` (`performed_by_employee_id`),
  KEY `in_action_created_on` (`created_on`),
  CONSTRAINT `fk_action_action_type_id` FOREIGN KEY (`action_type_id`) REFERENCES `action_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_action_created_by_employee_id` FOREIGN KEY (`created_by_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_action_performed_by_employee_id` FOREIGN KEY (`performed_by_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action`
--

LOCK TABLES `action` WRITE;
/*!40000 ALTER TABLE `action` DISABLE KEYS */;
/*!40000 ALTER TABLE `action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action_association_type`
--

DROP TABLE IF EXISTS `action_association_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action_association_type` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Action Association Type',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Action Association Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias of the Action Association Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action_association_type`
--

LOCK TABLES `action_association_type` WRITE;
/*!40000 ALTER TABLE `action_association_type` DISABLE KEYS */;
INSERT INTO `action_association_type` VALUES (1,'Account','Account','ACTION_ASSOCIATION_TYPE_ACCOUNT'),(2,'Service','Service','ACTION_ASSOCIATION_TYPE_SERVICE'),(3,'Contact','Contact','ACTION_ASSOCIATION_TYPE_CONTACT');
/*!40000 ALTER TABLE `action_association_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action_type`
--

DROP TABLE IF EXISTS `action_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action_type` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Action Type',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Action Type',
  `action_type_detail_requirement_id` smallint(5) unsigned NOT NULL COMMENT '(FK) User Input Requirements',
  `is_automatic_only` tinyint(3) unsigned NOT NULL COMMENT '0 = anyone can manually log this action, 1 = only the system can log this action',
  `is_system` tinyint(3) unsigned NOT NULL COMMENT '1 = the system specifically uses this action type, 0 = it doesn''t',
  `active_status_id` smallint(5) unsigned NOT NULL COMMENT 'FK into active_status table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_action_type_name` (`name`),
  KEY `fk_action_type_detail_requirement_id` (`action_type_detail_requirement_id`),
  KEY `fk_action_type_active_status_id` (`active_status_id`),
  CONSTRAINT `fk_action_type_active_status_id` FOREIGN KEY (`active_status_id`) REFERENCES `active_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_action_type_detail_requirement_id` FOREIGN KEY (`action_type_detail_requirement_id`) REFERENCES `action_type_detail_requirement` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action_type`
--

LOCK TABLES `action_type` WRITE;
/*!40000 ALTER TABLE `action_type` DISABLE KEYS */;
INSERT INTO `action_type` VALUES (7,'Payment Made','Payment Made',2,1,1,2),(12,'Charge Requested','Charge Requested',3,1,1,2),(13,'Charge Request Outcome','Charge Request Outcome',3,1,1,2),(14,'Recurring Charge Requested','Recurring Charge Requested',3,1,1,2),(15,'Recurring Charge Request Outcome','Recurring Charge Request Outcome',3,1,1,2),(23,'Adjustment Requested','Adjustment Requested',3,1,1,2),(24,'Adjustment Request Outcome','Adjustment Request Outcome',3,1,1,2),(31,'EFT One Time Payment','EFT One Time Payment',3,1,1,2);
/*!40000 ALTER TABLE `action_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action_type_action_association_type`
--

DROP TABLE IF EXISTS `action_type_action_association_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action_type_action_association_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `action_type_id` smallint(5) unsigned NOT NULL COMMENT 'FK into action_type table',
  `action_association_type_id` smallint(5) unsigned NOT NULL COMMENT 'FK into action_association_type table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_action_type_id_action_association_type_id` (`action_type_id`,`action_association_type_id`),
  KEY `fk_action_type_action_association_type_association_type_id` (`action_association_type_id`),
  CONSTRAINT `fk_action_type_action_association_type_action_type_id` FOREIGN KEY (`action_type_id`) REFERENCES `action_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_action_type_action_association_type_association_type_id` FOREIGN KEY (`action_association_type_id`) REFERENCES `action_association_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action_type_action_association_type`
--

LOCK TABLES `action_type_action_association_type` WRITE;
/*!40000 ALTER TABLE `action_type_action_association_type` DISABLE KEYS */;
INSERT INTO `action_type_action_association_type` VALUES (7,7,1),(21,12,1),(22,12,2),(19,13,1),(20,13,2),(25,14,1),(26,14,2),(23,15,1),(24,15,2),(34,23,1),(35,23,2),(36,24,1),(37,24,2),(45,31,1);
/*!40000 ALTER TABLE `action_type_action_association_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action_type_detail_requirement`
--

DROP TABLE IF EXISTS `action_type_detail_requirement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action_type_detail_requirement` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Action Type Detail Requirement',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Action Type Detail Requirement',
  `const_name` varchar(512) DEFAULT NULL COMMENT 'Constant Alias of the Action Type Detail Requirement',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action_type_detail_requirement`
--

LOCK TABLES `action_type_detail_requirement` WRITE;
/*!40000 ALTER TABLE `action_type_detail_requirement` DISABLE KEYS */;
INSERT INTO `action_type_detail_requirement` VALUES (1,'None','No Details','ACTION_TYPE_DETAIL_REQUIREMENT_NONE'),(2,'Optional','Details Optional','ACTION_TYPE_DETAIL_REQUIREMENT_OPTIONAL'),(3,'Required','Details Required','ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED');
/*!40000 ALTER TABLE `action_type_detail_requirement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `active_status`
--

DROP TABLE IF EXISTS `active_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `active_status` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'The name of the status',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Flag - 1 for active or 0 for inactive',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Description of active status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Active statuses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `active_status`
--

LOCK TABLES `active_status` WRITE;
/*!40000 ALTER TABLE `active_status` DISABLE KEYS */;
INSERT INTO `active_status` VALUES (1,'Inactive',0,'Inactive','ACTIVE_STATUS_INACTIVE'),(2,'Active',1,'Active','ACTIVE_STATUS_ACTIVE');
/*!40000 ALTER TABLE `active_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `address_locality`
--

DROP TABLE IF EXISTS `address_locality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_locality` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name of the Locality',
  `postcode` int(10) unsigned DEFAULT NULL COMMENT 'Postcode of the Locality',
  `state_id` bigint(20) unsigned NOT NULL COMMENT '(FK) State (Top-level Subdivision) of the Locality',
  PRIMARY KEY (`id`),
  KEY `fk_address_locality_state_id` (`state_id`),
  CONSTRAINT `fk_address_locality_state_id` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_locality`
--

LOCK TABLES `address_locality` WRITE;
/*!40000 ALTER TABLE `address_locality` DISABLE KEYS */;
/*!40000 ALTER TABLE `address_locality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment`
--

DROP TABLE IF EXISTS `adjustment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_type_id` int(10) unsigned NOT NULL,
  `amount` decimal(13,4) unsigned NOT NULL COMMENT 'Inclusive of tax_component',
  `tax_component` decimal(13,4) unsigned NOT NULL,
  `balance` decimal(13,4) unsigned NOT NULL,
  `effective_date` date DEFAULT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime DEFAULT NULL,
  `reviewed_employee_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_datetime` datetime DEFAULT NULL,
  `adjustment_nature_id` int(10) unsigned NOT NULL,
  `adjustment_review_outcome_id` int(10) unsigned DEFAULT NULL,
  `adjustment_status_id` int(10) unsigned NOT NULL,
  `reversed_adjustment_id` bigint(20) unsigned DEFAULT NULL,
  `adjustment_reversal_reason_id` int(10) unsigned DEFAULT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(1000) DEFAULT NULL COMMENT 'Note to provide additional related details about the adjustment',
  `invoice_run_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_adjustment_created_datetime` (`created_datetime`),
  KEY `in_adjustment_reviewed_datetime` (`reviewed_datetime`),
  KEY `in_adjustment_effective_date` (`effective_date`),
  KEY `fk_adjustment_adjustment_type_id` (`adjustment_type_id`),
  KEY `fk_adjustment_created_employee_id` (`created_employee_id`),
  KEY `fk_adjustment_reviewed_employee_id` (`reviewed_employee_id`),
  KEY `fk_adjustment_adjustment_nature_id` (`adjustment_nature_id`),
  KEY `fk_adjustment_adjustment_review_outcome_id` (`adjustment_review_outcome_id`),
  KEY `fk_adjustment_adjustment_status_id` (`adjustment_status_id`),
  KEY `fk_adjustment_reversed_adjustment_id` (`reversed_adjustment_id`),
  KEY `fk_adjustment_adjustment_reversal_reason_id` (`adjustment_reversal_reason_id`),
  KEY `fk_adjustment_account_id` (`account_id`),
  KEY `fk_adjustment_service_id` (`service_id`),
  KEY `fk_adjustment_invoice_id` (`invoice_id`),
  KEY `fk_adjustment_invoice_run_id` (`invoice_run_id`),
  CONSTRAINT `fk_adjustment_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_adjustment_nature_id` FOREIGN KEY (`adjustment_nature_id`) REFERENCES `adjustment_nature` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_adjustment_reversal_reason_id` FOREIGN KEY (`adjustment_reversal_reason_id`) REFERENCES `adjustment_reversal_reason` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_adjustment_review_outcome_id` FOREIGN KEY (`adjustment_review_outcome_id`) REFERENCES `adjustment_review_outcome` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_adjustment_status_id` FOREIGN KEY (`adjustment_status_id`) REFERENCES `adjustment_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_adjustment_type_id` FOREIGN KEY (`adjustment_type_id`) REFERENCES `adjustment_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `Invoice` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_invoice_run_id` FOREIGN KEY (`invoice_run_id`) REFERENCES `InvoiceRun` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_reversed_adjustment_id` FOREIGN KEY (`reversed_adjustment_id`) REFERENCES `adjustment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_reviewed_employee_id` FOREIGN KEY (`reviewed_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment`
--

LOCK TABLES `adjustment` WRITE;
/*!40000 ALTER TABLE `adjustment` DISABLE KEYS */;
/*!40000 ALTER TABLE `adjustment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_charge`
--

DROP TABLE IF EXISTS `adjustment_charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_charge` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_id` bigint(20) unsigned NOT NULL,
  `charge_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_adjustment_charge_adjustment_id` (`adjustment_id`),
  KEY `fk_adjustment_charge_charge_id` (`charge_id`),
  CONSTRAINT `fk_adjustment_charge_adjustment_id` FOREIGN KEY (`adjustment_id`) REFERENCES `adjustment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_charge_charge_id` FOREIGN KEY (`charge_id`) REFERENCES `Charge` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_charge`
--

LOCK TABLES `adjustment_charge` WRITE;
/*!40000 ALTER TABLE `adjustment_charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `adjustment_charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_nature`
--

DROP TABLE IF EXISTS `adjustment_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_nature` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  `value_multiplier` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_nature`
--

LOCK TABLES `adjustment_nature` WRITE;
/*!40000 ALTER TABLE `adjustment_nature` DISABLE KEYS */;
INSERT INTO `adjustment_nature` VALUES (1,'Adjustment','Adjustment','ADJUSTMENT','ADJUSTMENT_NATURE_ADJUSTMENT',1),(2,'Reversal','Reversal','REVERSAL','ADJUSTMENT_NATURE_REVERSAL',-1);
/*!40000 ALTER TABLE `adjustment_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_reversal_reason`
--

DROP TABLE IF EXISTS `adjustment_reversal_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_reversal_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_adjustment_reversal_reason_status_id` (`status_id`),
  CONSTRAINT `fk_adjustment_reversal_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_reversal_reason`
--

LOCK TABLES `adjustment_reversal_reason` WRITE;
/*!40000 ALTER TABLE `adjustment_reversal_reason` DISABLE KEYS */;
INSERT INTO `adjustment_reversal_reason` VALUES (1,'Reversal','Reversal','REVERSAL',1);
/*!40000 ALTER TABLE `adjustment_reversal_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_review_outcome`
--

DROP TABLE IF EXISTS `adjustment_review_outcome`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_review_outcome` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  `adjustment_review_outcome_type_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_adjustment_review_outcome_status_id` (`status_id`),
  KEY `fk_adjustment_review_outcome_adjustment_review_outcome_type_id` (`adjustment_review_outcome_type_id`),
  CONSTRAINT `fk_adjustment_review_outcome_adjustment_review_outcome_type_id` FOREIGN KEY (`adjustment_review_outcome_type_id`) REFERENCES `adjustment_review_outcome_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_review_outcome_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_review_outcome`
--

LOCK TABLES `adjustment_review_outcome` WRITE;
/*!40000 ALTER TABLE `adjustment_review_outcome` DISABLE KEYS */;
INSERT INTO `adjustment_review_outcome` VALUES (1,'Approved','Approved','APPROVED',1,1),(2,'Declined','Declined','DECLINED',1,2);
/*!40000 ALTER TABLE `adjustment_review_outcome` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_review_outcome_type`
--

DROP TABLE IF EXISTS `adjustment_review_outcome_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_review_outcome_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_review_outcome_type`
--

LOCK TABLES `adjustment_review_outcome_type` WRITE;
/*!40000 ALTER TABLE `adjustment_review_outcome_type` DISABLE KEYS */;
INSERT INTO `adjustment_review_outcome_type` VALUES (1,'Approved','Approved','APPROVED','ADJUSTMENT_REVIEW_OUTCOME_TYPE_APPROVED'),(2,'Declined','Declined','DECLINED','ADJUSTMENT_REVIEW_OUTCOME_TYPE_DECLINED');
/*!40000 ALTER TABLE `adjustment_review_outcome_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_status`
--

DROP TABLE IF EXISTS `adjustment_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_status`
--

LOCK TABLES `adjustment_status` WRITE;
/*!40000 ALTER TABLE `adjustment_status` DISABLE KEYS */;
INSERT INTO `adjustment_status` VALUES (1,'Pending','Pending','PENDING','ADJUSTMENT_STATUS_PENDING'),(2,'Approved','Approved','APPROVED','ADJUSTMENT_STATUS_APPROVED'),(3,'Declined','Declined','DECLINED','ADJUSTMENT_STATUS_DECLINED'),(4,'Deleted','Deleted','DELETED','ADJUSTMENT_STATUS_DELETED');
/*!40000 ALTER TABLE `adjustment_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_type`
--

DROP TABLE IF EXISTS `adjustment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `amount` decimal(13,4) DEFAULT NULL,
  `is_amount_fixed` tinyint(4) NOT NULL DEFAULT '0',
  `transaction_nature_id` int(10) unsigned NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  `adjustment_type_invoice_visibility_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_adjustment_type_transaction_nature_id` (`transaction_nature_id`),
  KEY `fk_adjustment_type_status_id` (`status_id`),
  KEY `fk_adjustment_type_adjustment_type_invoice_visibility_id` (`adjustment_type_invoice_visibility_id`),
  CONSTRAINT `fk_adjustment_type_adjustment_type_invoice_visibility_id` FOREIGN KEY (`adjustment_type_invoice_visibility_id`) REFERENCES `adjustment_type_invoice_visibility` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_type_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_type_transaction_nature_id` FOREIGN KEY (`transaction_nature_id`) REFERENCES `transaction_nature` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_type`
--

LOCK TABLES `adjustment_type` WRITE;
/*!40000 ALTER TABLE `adjustment_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `adjustment_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_type_invoice_visibility`
--

DROP TABLE IF EXISTS `adjustment_type_invoice_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_type_invoice_visibility` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_type_invoice_visibility`
--

LOCK TABLES `adjustment_type_invoice_visibility` WRITE;
/*!40000 ALTER TABLE `adjustment_type_invoice_visibility` DISABLE KEYS */;
INSERT INTO `adjustment_type_invoice_visibility` VALUES (1,'Hidden','Not shown on Invoice','HIDDEN','ADJUSTMENT_TYPE_INVOICE_VISIBILITY_HIDDEN'),(2,'Visible','Shown on Invoice','VISIBLE','ADJUSTMENT_TYPE_INVOICE_VISIBILITY_VISIBLE');
/*!40000 ALTER TABLE `adjustment_type_invoice_visibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_type_system`
--

DROP TABLE IF EXISTS `adjustment_type_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_type_system` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_type_system`
--

LOCK TABLES `adjustment_type_system` WRITE;
/*!40000 ALTER TABLE `adjustment_type_system` DISABLE KEYS */;
INSERT INTO `adjustment_type_system` VALUES (1,'Write-off','Write-off Adjustment','WRITE_OFF','ADJUSTMENT_TYPE_SYSTEM_WRITE_OFF'),(2,'Write-back','Write-back Adjustment','WRITE_BACK','ADJUSTMENT_TYPE_SYSTEM_WRITE_BACK'),(3,'Rerate','Rerate Adjustment','RERATE','ADJUSTMENT_TYPE_SYSTEM_RERATE'),(4,'Payment Surcharge Reversal','Payment Surcharge Reversal','PAYMENT_SURCHARGE_REVERSAL','ADJUSTMENT_TYPE_SYSTEM_PAYMENT_SURCHARGE_REVERSAL');
/*!40000 ALTER TABLE `adjustment_type_system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustment_type_system_config`
--

DROP TABLE IF EXISTS `adjustment_type_system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjustment_type_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_type_system_id` int(10) unsigned NOT NULL,
  `adjustment_type_id` int(10) unsigned NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_adjustment_type_system_config_start_datetime` (`start_datetime`),
  KEY `in_adjustment_type_system_config_end_datetime` (`end_datetime`),
  KEY `fk_adjustment_type_system_config_adjustment_type_id` (`adjustment_type_id`),
  KEY `fk_adjustment_type_system_config_adjustment_type_system_id` (`adjustment_type_system_id`),
  CONSTRAINT `fk_adjustment_type_system_config_adjustment_type_id` FOREIGN KEY (`adjustment_type_id`) REFERENCES `adjustment_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjustment_type_system_config_adjustment_type_system_id` FOREIGN KEY (`adjustment_type_system_id`) REFERENCES `adjustment_type_system` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustment_type_system_config`
--

LOCK TABLES `adjustment_type_system_config` WRITE;
/*!40000 ALTER TABLE `adjustment_type_system_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `adjustment_type_system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automated_invoice_run_process`
--

DROP TABLE IF EXISTS `automated_invoice_run_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automated_invoice_run_process` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `invoice_run_id` bigint(20) unsigned NOT NULL COMMENT 'FK to InvoiceRun table',
  `commencement_date_normal` date NOT NULL COMMENT 'Date on which to commence process (normal customers)',
  `commencement_date_first` date NOT NULL COMMENT 'Date on which to commence process (first timers)',
  `commencement_date_vip` date NOT NULL COMMENT 'Date on which to commence process (VIP customers)',
  `last_processed_date` date DEFAULT NULL COMMENT 'Date on which last processed',
  `last_listing_date` date DEFAULT NULL COMMENT 'Date on which last listed',
  `completed_date` date DEFAULT NULL COMMENT 'Date on which process completed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='TEMPORARY table for staggered barring';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automated_invoice_run_process`
--

LOCK TABLES `automated_invoice_run_process` WRITE;
/*!40000 ALTER TABLE `automated_invoice_run_process` DISABLE KEYS */;
/*!40000 ALTER TABLE `automated_invoice_run_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automated_invoice_run_process_config`
--

DROP TABLE IF EXISTS `automated_invoice_run_process_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automated_invoice_run_process_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to CustomerGroup table',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether or not this feature is enabled',
  `days_from_invoice_normal` tinyint(1) unsigned NOT NULL DEFAULT '21' COMMENT 'Default number of days from invoicing to start process for normal accs',
  `days_from_invoice_first` tinyint(1) unsigned NOT NULL DEFAULT '24' COMMENT 'Default number of days from invoicing to start process for fist timers',
  `days_from_invoice_vip` tinyint(1) unsigned NOT NULL DEFAULT '26' COMMENT 'Default number of days from invoicing to start process for VIP accs',
  `listing_time_of_day` time DEFAULT '00:00:00' COMMENT 'Time of day to list service',
  `barring_time_of_day` time DEFAULT '00:00:00' COMMENT 'Time of day to bar services',
  `barring_days` varchar(20) DEFAULT '' COMMENT 'csv list of day.ids for days on which to bar services',
  `max_barrings_per_day` int(10) unsigned NOT NULL COMMENT 'Maximum number of accounts to bar on each day of barring',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='TEMPORARY config table for staggered barring';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automated_invoice_run_process_config`
--

LOCK TABLES `automated_invoice_run_process_config` WRITE;
/*!40000 ALTER TABLE `automated_invoice_run_process_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `automated_invoice_run_process_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_barring_status`
--

DROP TABLE IF EXISTS `automatic_barring_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_barring_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the status',
  `name` varchar(50) NOT NULL COMMENT 'Name of the status',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Automatic barring statuses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_barring_status`
--

LOCK TABLES `automatic_barring_status` WRITE;
/*!40000 ALTER TABLE `automatic_barring_status` DISABLE KEYS */;
INSERT INTO `automatic_barring_status` VALUES (1,'None','None','AUTOMATIC_BARRING_STATUS_NONE'),(2,'Barred','Barred','AUTOMATIC_BARRING_STATUS_BARRED'),(3,'Unbarred','Unbarred','AUTOMATIC_BARRING_STATUS_UNBARRED');
/*!40000 ALTER TABLE `automatic_barring_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_barring_status_history`
--

DROP TABLE IF EXISTS `automatic_barring_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_barring_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for the historic event',
  `account` bigint(20) unsigned NOT NULL COMMENT 'Affected account',
  `from_status` bigint(20) unsigned NOT NULL COMMENT 'The original automatic_barring_status.id',
  `to_status` bigint(20) unsigned NOT NULL COMMENT 'The new automatic_barring_status.id',
  `reason` varchar(255) NOT NULL COMMENT 'Reason for change',
  `change_datetime` datetime DEFAULT NULL COMMENT 'Date/Time of the change to this status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Automatic barring status change history';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_barring_status_history`
--

LOCK TABLES `automatic_barring_status_history` WRITE;
/*!40000 ALTER TABLE `automatic_barring_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_barring_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_invoice_action`
--

DROP TABLE IF EXISTS `automatic_invoice_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_invoice_action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the action',
  `name` varchar(50) NOT NULL COMMENT 'Name of the action',
  `description` varchar(255) NOT NULL COMMENT 'Description of the action',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='Automatic invoice actions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_invoice_action`
--

LOCK TABLES `automatic_invoice_action` WRITE;
/*!40000 ALTER TABLE `automatic_invoice_action` DISABLE KEYS */;
INSERT INTO `automatic_invoice_action` VALUES (1,'None','None','AUTOMATIC_INVOICE_ACTION_NONE'),(2,'Overdue Notice','Overdue Notice sent','AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE'),(3,'Suspension Notice','Suspension Notice sent','AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'),(4,'Final Demand','Final Demand sent','AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND'),(5,'Overdue Notice List','Overdue Notice list sent','AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST'),(6,'Suspension Notice List','Suspension Notice list sent','AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST'),(7,'Final Demand List','Final Demand list sent','AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST'),(8,'Friendly Reminder List','Friendly Reminder list sent','AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST'),(9,'Friendly Reminder','Friendly Reminder sent','AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER'),(10,'Late Fees List','Late Fees list sent','AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST'),(11,'Late Fees','Late Fees applied','AUTOMATIC_INVOICE_ACTION_LATE_FEES'),(12,'Automatic Barring List','Automatic Barring list sent','AUTOMATIC_INVOICE_ACTION_BARRING_LIST'),(13,'Automatic Barring','Automatic Barring applied','AUTOMATIC_INVOICE_ACTION_BARRING'),(14,'Automatic Unbarring','Automatic Unbarring applied','AUTOMATIC_INVOICE_ACTION_UNBARRING'),(17,'Direct Debit','Direct Debit applied','AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT'),(18,'Friendly Reminder SMS','Automatic Friendly Reminder SMS Sent','AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_SMS'),(19,'Barring Notification SMS','Automatic Barring Notification SMS Sent','AUTOMATIC_INVOICE_ACTION_BARRING_NOTIFICATION_SMS');
/*!40000 ALTER TABLE `automatic_invoice_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_invoice_action_config`
--

DROP TABLE IF EXISTS `automatic_invoice_action_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_invoice_action_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
  `automatic_invoice_action_id` bigint(20) DEFAULT NULL COMMENT 'FK to automatic_invoice_action table',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'FK to CustomerGroup table',
  `days_from_invoice` smallint(5) DEFAULT '0',
  `can_schedule` tinyint(3) DEFAULT '0' COMMENT 'Whether or not this action can be scheduled',
  `response_days` smallint(5) DEFAULT '7' COMMENT 'Number of days from event that an external response must be made in',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Automatic invoice action configuration settings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_invoice_action_config`
--

LOCK TABLES `automatic_invoice_action_config` WRITE;
/*!40000 ALTER TABLE `automatic_invoice_action_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_invoice_action_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_invoice_action_correspondence_template`
--

DROP TABLE IF EXISTS `automatic_invoice_action_correspondence_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_invoice_action_correspondence_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `automatic_invoice_action_id` bigint(20) unsigned NOT NULL,
  `correspondence_template_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_automatic_invoice_action_correspondence_template_action` (`automatic_invoice_action_id`),
  KEY `fk_automatic_invoice_action_correspondence_template_template` (`correspondence_template_id`),
  CONSTRAINT `fk_automatic_invoice_action_correspondence_template_action` FOREIGN KEY (`automatic_invoice_action_id`) REFERENCES `automatic_invoice_action` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_automatic_invoice_action_correspondence_template_template` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_invoice_action_correspondence_template`
--

LOCK TABLES `automatic_invoice_action_correspondence_template` WRITE;
/*!40000 ALTER TABLE `automatic_invoice_action_correspondence_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_invoice_action_correspondence_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_invoice_action_dependency`
--

DROP TABLE IF EXISTS `automatic_invoice_action_dependency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_invoice_action_dependency` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the automatic invoice action dependency',
  `dependent_automatic_invoice_action_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the dependent automatic invoice action',
  `prerequisite_automatic_invoice_action_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the prerequisite automatic invoice action',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_action_dependency` (`dependent_automatic_invoice_action_id`,`prerequisite_automatic_invoice_action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Automatic invoice action dependencies';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_invoice_action_dependency`
--

LOCK TABLES `automatic_invoice_action_dependency` WRITE;
/*!40000 ALTER TABLE `automatic_invoice_action_dependency` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_invoice_action_dependency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_invoice_action_history`
--

DROP TABLE IF EXISTS `automatic_invoice_action_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_invoice_action_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for the historic event',
  `account` bigint(20) unsigned NOT NULL COMMENT 'Affected account',
  `from_action` bigint(20) unsigned NOT NULL COMMENT 'The original automatic_invoice_action.id',
  `to_action` bigint(20) unsigned NOT NULL COMMENT 'The new automatic_invoice_action.id',
  `reason` varchar(255) NOT NULL COMMENT 'Reason for change',
  `change_datetime` datetime DEFAULT NULL COMMENT 'Date/Time of the change to this action',
  `invoice_run_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the invoice run for the event',
  PRIMARY KEY (`id`),
  KEY `invoice_run_id_account` (`invoice_run_id`,`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Automatic invoice action change history';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_invoice_action_history`
--

LOCK TABLES `automatic_invoice_action_history` WRITE;
/*!40000 ALTER TABLE `automatic_invoice_action_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_invoice_action_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automatic_invoice_run_event`
--

DROP TABLE IF EXISTS `automatic_invoice_run_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_invoice_run_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the automatic invoice run event',
  `automatic_invoice_action_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the automatic invoice action for the event',
  `invoice_run_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the invoice run for the event',
  `scheduled_datetime` datetime DEFAULT NULL COMMENT 'Date/Time at which the action can be taken',
  `actioned_datetime` datetime DEFAULT NULL COMMENT 'Date/Time at which the action was taken',
  `update_user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'The user who scheduled the event',
  `update_datetime` datetime DEFAULT NULL COMMENT 'Date/Time at which the user scheduled the event',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_run_action` (`automatic_invoice_action_id`,`invoice_run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email address usage';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_invoice_run_event`
--

LOCK TABLES `automatic_invoice_run_event` WRITE;
/*!40000 ALTER TABLE `automatic_invoice_run_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_invoice_run_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barring_level`
--

DROP TABLE IF EXISTS `barring_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barring_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barring_level`
--

LOCK TABLES `barring_level` WRITE;
/*!40000 ALTER TABLE `barring_level` DISABLE KEYS */;
INSERT INTO `barring_level` VALUES (1,'Unrestricted','Unrestricted','UNRESTRICTED','BARRING_LEVEL_UNRESTRICTED'),(2,'Barred','Barred','BARRED','BARRING_LEVEL_BARRED'),(3,'Temporary Disconnection','Temporary Disconnection','TEMPORARY_DISCONNECTION','BARRING_LEVEL_TEMPORARY_DISCONNECTION');
/*!40000 ALTER TABLE `barring_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_charge_module`
--

DROP TABLE IF EXISTS `billing_charge_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_charge_module` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Billing Charge Module Instance',
  `class` varchar(1024) NOT NULL COMMENT 'Module Class Name',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'Customer Group that this Module applies to (NULL = ALL)',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Optional Description for the Instance',
  `active_status_id` bigint(20) NOT NULL DEFAULT '1' COMMENT 'FK to active_status table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_charge_module`
--

LOCK TABLES `billing_charge_module` WRITE;
/*!40000 ALTER TABLE `billing_charge_module` DISABLE KEYS */;
/*!40000 ALTER TABLE `billing_charge_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_charge_module_config`
--

DROP TABLE IF EXISTS `billing_charge_module_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_charge_module_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Billing Charge Module Config Field',
  `billing_charge_module_id` bigint(20) NOT NULL COMMENT 'FK to billing_charge_module table',
  `name` varchar(256) DEFAULT NULL COMMENT 'Name of the field',
  `data_type_id` bigint(20) DEFAULT NULL COMMENT 'FK to data_type table',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description for the field',
  `value` varchar(4096) NOT NULL DEFAULT '1' COMMENT 'Value of the field',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_charge_module_config`
--

LOCK TABLES `billing_charge_module_config` WRITE;
/*!40000 ALTER TABLE `billing_charge_module_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `billing_charge_module_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_type`
--

DROP TABLE IF EXISTS `billing_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name of the Billing Type',
  `description` varchar(512) NOT NULL COMMENT 'Description of the Billing Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias of the Billing Type',
  `system_name` varchar(512) NOT NULL COMMENT 'System Name of the Billing Type',
  `payment_method_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Payment Method',
  PRIMARY KEY (`id`),
  KEY `fk_billing_type_payment_method_id` (`payment_method_id`),
  CONSTRAINT `fk_billing_type_payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_type`
--

LOCK TABLES `billing_type` WRITE;
/*!40000 ALTER TABLE `billing_type` DISABLE KEYS */;
INSERT INTO `billing_type` VALUES (1,'Direct Debit: EFT','Direct Debit via EFT','BILLING_TYPE_DIRECT_DEBIT','DIRECT_DEBIT',2),(2,'Direct Debit: Credit Card','Direct Debit via Credit Card','BILLING_TYPE_CREDIT_CARD','CREDIT_CARD',2),(3,'Account','Account Billing','BILLING_TYPE_ACCOUNT','ACCOUNT',1),(4,'Rebill','Rebill','BILLING_TYPE_REBILL','REBILL',3);
/*!40000 ALTER TABLE `billing_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event`
--

DROP TABLE IF EXISTS `calendar_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name of the Event',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Event',
  `department_responsible` varchar(256) DEFAULT NULL COMMENT 'Department responsible for this Event',
  `start_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Event Start Timestamp',
  `end_timestamp` timestamp NULL DEFAULT NULL COMMENT 'Event End Timestamp',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who created/scheduled the Event',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Timestamp',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Status',
  PRIMARY KEY (`id`),
  KEY `fk_calendar_event_created_employee_id` (`created_employee_id`),
  KEY `fk_calendar_event_status_id` (`status_id`),
  CONSTRAINT `fk_calendar_event_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_event_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event`
--

LOCK TABLES `calendar_event` WRITE;
/*!40000 ALTER TABLE `calendar_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_config`
--

DROP TABLE IF EXISTS `carrier_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_carrier_id` bigint(20) NOT NULL,
  `created_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_carrier_config_vendor_carrier_id` (`vendor_carrier_id`),
  CONSTRAINT `fk_carrier_config_vendor_carrier_id` FOREIGN KEY (`vendor_carrier_id`) REFERENCES `Carrier` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_config`
--

LOCK TABLES `carrier_config` WRITE;
/*!40000 ALTER TABLE `carrier_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_module_type`
--

DROP TABLE IF EXISTS `carrier_module_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_module_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Carrier Module Type',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Carrier Module Type',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Carrier Module Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Carrier Module Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=518 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_module_type`
--

LOCK TABLES `carrier_module_type` WRITE;
/*!40000 ALTER TABLE `carrier_module_type` DISABLE KEYS */;
INSERT INTO `carrier_module_type` VALUES (500,'Provisioning (Incoming)','Provisioning (Incoming)','MODULE_TYPE_PROVISIONING_INPUT'),(501,'Provisioning (Outgoing)','Provisioning (Outgoing)','MODULE_TYPE_PROVISIONING_OUTPUT'),(502,'Collection','Collection','MODULE_TYPE_COLLECTION'),(503,'CDR Normalisation','CDR Normalisation','MODULE_TYPE_NORMALISATION_CDR'),(504,'Payment Normalisation','Payment Normalisation','MODULE_TYPE_NORMALISATION_PAYMENT'),(505,'Direct Debit Requests','Direct Debit Requests','MODULE_TYPE_PAYMENT_DIRECT_DEBIT'),(507,'Telemarketing Proposed FNN Files','Telemarketing Proposed FNN Files','MODULE_TYPE_TELEMARKETING_PROPOSED_IMPORT'),(508,'Telemarketing DNCR Request Files','Telemarketing DNCR Request Files','MODULE_TYPE_TELEMARKETING_DNCR_EXPORT'),(509,'Telemarketing DNCR Response Files','Telemarketing DNCR Response Files','MODULE_TYPE_TELEMARKETING_DNCR_IMPORT'),(510,'Telemarketing Permitted FNN Files','Telemarketing Permitted FNN Files','MODULE_TYPE_TELEMARKETING_PERMITTED_EXPORT'),(511,'Telemarketing Dialler Report Files','Telemarketing Dialler Report Files','MODULE_TYPE_TELEMARKETING_DIALLER_IMPORT'),(512,'Invoice Run Export','Invoice Run Export','MODULE_TYPE_INVOICE_RUN_EXPORT'),(513,'Motorpass Provisioning Import','Motorpass Provisioning Import','MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT'),(514,'Motorpass Provisioning Export','Motorpass Provisioning Export','MODULE_TYPE_MOTORPASS_PROVISIONING_IMPORT'),(515,'File Deliver','File Deliver','MODULE_TYPE_FILE_DELIVER'),(516,'Correspondence Export','Correspondence Export','MODULE_TYPE_CORRESPONDENCE_EXPORT'),(517,'OCA Referral File','Outside Collection Agency Referral File','MODULE_TYPE_OCA_REFERRAL_FILE');
/*!40000 ALTER TABLE `carrier_module_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_payment_type`
--

DROP TABLE IF EXISTS `carrier_payment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_payment_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `carrier_id` bigint(20) NOT NULL COMMENT '(FK) Carrier',
  `payment_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Payment Type',
  `surcharge_percent` decimal(4,4) DEFAULT NULL COMMENT 'Merchant Fee defined as a percentage of Payment value',
  `description` varchar(128) NOT NULL COMMENT 'Description of the Payment Type/Carrier combination',
  PRIMARY KEY (`id`),
  KEY `fk_carrier_payment_type_carrier_id` (`carrier_id`),
  KEY `fk_carrier_payment_type_payment_charge_id` (`payment_type_id`),
  CONSTRAINT `fk_carrier_payment_type_carrier_id` FOREIGN KEY (`carrier_id`) REFERENCES `Carrier` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_carrier_payment_type_payment_charge_id` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_payment_type`
--

LOCK TABLES `carrier_payment_type` WRITE;
/*!40000 ALTER TABLE `carrier_payment_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_payment_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_provisioning_support`
--

DROP TABLE IF EXISTS `carrier_provisioning_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_provisioning_support` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `carrier_id` bigint(20) unsigned NOT NULL COMMENT 'FK to Carrier table',
  `provisioning_type_id` bigint(20) unsigned NOT NULL COMMENT 'FK to provisioning_type table',
  `status_id` smallint(5) unsigned NOT NULL COMMENT 'FK to active_status table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores status of provisioning_type for CarrierModule';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_provisioning_support`
--

LOCK TABLES `carrier_provisioning_support` WRITE;
/*!40000 ALTER TABLE `carrier_provisioning_support` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_provisioning_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_translation`
--

DROP TABLE IF EXISTS `carrier_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_translation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `carrier_translation_context_id` bigint(20) unsigned NOT NULL,
  `in_value` varchar(1024) NOT NULL,
  `out_value` varchar(1024) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_carrier_translation_carrier_translation_context_id` (`carrier_translation_context_id`),
  CONSTRAINT `fk_carrier_translation_carrier_translation_context_id` FOREIGN KEY (`carrier_translation_context_id`) REFERENCES `carrier_translation_context` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_translation`
--

LOCK TABLES `carrier_translation` WRITE;
/*!40000 ALTER TABLE `carrier_translation` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_translation_context`
--

DROP TABLE IF EXISTS `carrier_translation_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_translation_context` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Carrier Translation Context',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Carrier Translation Context',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_translation_context`
--

LOCK TABLES `carrier_translation_context` WRITE;
/*!40000 ALTER TABLE `carrier_translation_context` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_translation_context` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_type`
--

DROP TABLE IF EXISTS `carrier_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrier_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of this Carrier Type',
  `description` varchar(512) NOT NULL COMMENT 'Description of this Carrier Type',
  `const_name` varchar(255) NOT NULL COMMENT 'Constant name for this Carrier Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='Different Types of Carrier that Flex Supports';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_type`
--

LOCK TABLES `carrier_type` WRITE;
/*!40000 ALTER TABLE `carrier_type` DISABLE KEYS */;
INSERT INTO `carrier_type` VALUES (1,'Telecom','Telecommunications Carrier','CARRIER_TYPE_TELECOM'),(2,'Payment','Payments Carrier','CARRIER_TYPE_PAYMENT'),(3,'Sales Call Centre','Sales Call Centre','CARRIER_TYPE_SALES_CALL_CENTRE'),(4,'Telecom Authority','Telecommunications Authority','CARRIER_TYPE_TELECOM_AUTHORITY'),(5,'Rebiller','Rebiller','CARRIER_TYPE_REBILLER'),(6,'Mailing House','Mailing House','CARRIER_TYPE_MAILINGHOUSE'),(7,'OCA','Outside Collections Agency','CARRIER_TYPE_OCA'),(8,'Inventory Supplier','Inventory Supplier','CARRIER_TYPE_INVENTORY_SUPPLIER');
/*!40000 ALTER TABLE `carrier_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cdr_credit_link`
--

DROP TABLE IF EXISTS `cdr_credit_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdr_credit_link` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `credit_cdr_id` bigint(20) unsigned NOT NULL,
  `debit_cdr_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cdr_credit_link`
--

LOCK TABLES `cdr_credit_link` WRITE;
/*!40000 ALTER TABLE `cdr_credit_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `cdr_credit_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cdr_delinquent_writeoff`
--

DROP TABLE IF EXISTS `cdr_delinquent_writeoff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdr_delinquent_writeoff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `cdr_id` bigint(20) NOT NULL COMMENT 'Deliquent CDR record that was written off',
  `created_datetime` datetime NOT NULL COMMENT 'When the write off occured',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee, who executed the write off',
  PRIMARY KEY (`id`),
  KEY `fk_cdr_delinquent_writeoff_created_employee_id` (`created_employee_id`),
  CONSTRAINT `fk_cdr_delinquent_writeoff_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Log for a deliquent CDR record that has been written off.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cdr_delinquent_writeoff`
--

LOCK TABLES `cdr_delinquent_writeoff` WRITE;
/*!40000 ALTER TABLE `cdr_delinquent_writeoff` DISABLE KEYS */;
/*!40000 ALTER TABLE `cdr_delinquent_writeoff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charge_model`
--

DROP TABLE IF EXISTS `charge_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charge_model` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name',
  `description` varchar(512) NOT NULL COMMENT 'Description',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias',
  `system_name` varchar(256) NOT NULL COMMENT 'System Alias',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charge_model`
--

LOCK TABLES `charge_model` WRITE;
/*!40000 ALTER TABLE `charge_model` DISABLE KEYS */;
INSERT INTO `charge_model` VALUES (1,'Charge','Charge','CHARGE_MODEL_CHARGE','CHARGE'),(2,'Adjustment','Adjustment','CHARGE_MODEL_ADJUSTMENT','ADJUSTMENT');
/*!40000 ALTER TABLE `charge_model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charge_recurring_charge`
--

DROP TABLE IF EXISTS `charge_recurring_charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charge_recurring_charge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `charge_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Charge. (has uniqueness constraint)',
  `recurring_charge_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Recurring Charge',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_charge_recurring_charge_charge_id` (`charge_id`),
  KEY `fk_charge_recurring_charge_recurring_charge_id` (`recurring_charge_id`),
  CONSTRAINT `fk_charge_recurring_charge_charge_id` FOREIGN KEY (`charge_id`) REFERENCES `Charge` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_charge_recurring_charge_recurring_charge_id` FOREIGN KEY (`recurring_charge_id`) REFERENCES `RecurringCharge` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charge_recurring_charge`
--

LOCK TABLES `charge_recurring_charge` WRITE;
/*!40000 ALTER TABLE `charge_recurring_charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `charge_recurring_charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charge_type_system`
--

DROP TABLE IF EXISTS `charge_type_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charge_type_system` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name of the System Charge Type',
  `description` varchar(512) NOT NULL COMMENT 'Description of the System Charge Type',
  `const_name` varchar(256) NOT NULL COMMENT 'Constant Alias of the System Charge Type',
  `system_name` varchar(256) NOT NULL COMMENT 'System Name of the System Charge Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='A System Charge Type';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charge_type_system`
--

LOCK TABLES `charge_type_system` WRITE;
/*!40000 ALTER TABLE `charge_type_system` DISABLE KEYS */;
INSERT INTO `charge_type_system` VALUES (1,'Rerate','Rerate Adjustment','CHARGE_TYPE_SYSTEM_RERATE','RERATE');
/*!40000 ALTER TABLE `charge_type_system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charge_type_system_config`
--

DROP TABLE IF EXISTS `charge_type_system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charge_type_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `charge_type_system_id` int(10) unsigned NOT NULL COMMENT '(FK) charge_type_system, the System Charge Type being configured',
  `charge_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) ChargeType, the Charge Type which the System Charge Type represents',
  `start_datetime` datetime NOT NULL COMMENT 'When the System Charge Type configuration is valid from',
  `end_datetime` datetime NOT NULL COMMENT 'When the System Charge Type configuration is valid to',
  PRIMARY KEY (`id`),
  KEY `fk_charge_type_system_config_charge_type_system_id` (`charge_type_system_id`),
  KEY `fk_charge_type_system_config_charge_type_id` (`charge_type_id`),
  CONSTRAINT `fk_charge_type_system_config_charge_type_id` FOREIGN KEY (`charge_type_id`) REFERENCES `ChargeType` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_charge_type_system_config_charge_type_system_id` FOREIGN KEY (`charge_type_system_id`) REFERENCES `charge_type_system` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='A configuration of a System Charge Type';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charge_type_system_config`
--

LOCK TABLES `charge_type_system_config` WRITE;
/*!40000 ALTER TABLE `charge_type_system_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `charge_type_system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charge_type_visibility`
--

DROP TABLE IF EXISTS `charge_type_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charge_type_visibility` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description',
  `system_name` varchar(256) NOT NULL COMMENT 'System Name',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charge_type_visibility`
--

LOCK TABLES `charge_type_visibility` WRITE;
/*!40000 ALTER TABLE `charge_type_visibility` DISABLE KEYS */;
INSERT INTO `charge_type_visibility` VALUES (1,'Visible','Visible to all Users','VISIBLE','CHARGE_TYPE_VISIBILITY_VISIBLE'),(2,'Hidden','Hidden from all Users','HIDDEN','CHARGE_TYPE_VISIBILITY_HIDDEN'),(3,'Credit Control','Visible to Credit Controllers only','CREDIT_CONTROL','CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL');
/*!40000 ALTER TABLE `charge_type_visibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collectable`
--

DROP TABLE IF EXISTS `collectable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collectable` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(13,4) NOT NULL,
  `balance` decimal(13,4) DEFAULT NULL,
  `created_datetime` datetime NOT NULL,
  `due_date` date NOT NULL,
  `collection_promise_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collectable_created_datetime` (`created_datetime`),
  KEY `in_collectable_due_date` (`due_date`),
  KEY `fk_collectable_account_id` (`account_id`),
  KEY `fk_collectable_collection_promise_id` (`collection_promise_id`),
  KEY `fk_collectable_invoice_id` (`invoice_id`),
  CONSTRAINT `fk_collectable_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collectable_collection_promise_id` FOREIGN KEY (`collection_promise_id`) REFERENCES `collection_promise` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collectable_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `Invoice` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collectable`
--

LOCK TABLES `collectable` WRITE;
/*!40000 ALTER TABLE `collectable` DISABLE KEYS */;
/*!40000 ALTER TABLE `collectable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collectable_adjustment`
--

DROP TABLE IF EXISTS `collectable_adjustment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collectable_adjustment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_id` bigint(20) unsigned NOT NULL,
  `collectable_id` bigint(20) unsigned NOT NULL,
  `balance` decimal(13,4) DEFAULT NULL,
  `created_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collectable_adjustment_created_datetime` (`created_datetime`),
  KEY `fk_collectable_adjustment_adjustment_id` (`adjustment_id`),
  KEY `fk_collectable_adjustment_collectable_id` (`collectable_id`),
  CONSTRAINT `fk_collectable_adjustment_adjustment_id` FOREIGN KEY (`adjustment_id`) REFERENCES `adjustment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collectable_adjustment_collectable_id` FOREIGN KEY (`collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collectable_adjustment`
--

LOCK TABLES `collectable_adjustment` WRITE;
/*!40000 ALTER TABLE `collectable_adjustment` DISABLE KEYS */;
/*!40000 ALTER TABLE `collectable_adjustment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collectable_payment`
--

DROP TABLE IF EXISTS `collectable_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collectable_payment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `collectable_id` bigint(20) unsigned NOT NULL,
  `balance` decimal(13,4) DEFAULT NULL,
  `create_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collectable_payment_create_datetime` (`create_datetime`),
  KEY `fk_collectable_payment_payment_id` (`payment_id`),
  KEY `fk_collectable_payment_collectable_id` (`collectable_id`),
  CONSTRAINT `fk_collectable_payment_collectable_id` FOREIGN KEY (`collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collectable_payment_payment_id` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collectable_payment`
--

LOCK TABLES `collectable_payment` WRITE;
/*!40000 ALTER TABLE `collectable_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `collectable_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collectable_transfer_balance`
--

DROP TABLE IF EXISTS `collectable_transfer_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collectable_transfer_balance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_collectable_id` bigint(20) unsigned NOT NULL,
  `to_collectable_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `balance` decimal(13,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collectable_transfer_balance_created_datetime` (`created_datetime`),
  KEY `fk_collectable_transfer_balance_from_collectable_id` (`from_collectable_id`),
  KEY `fk_collectable_transfer_balance_to_collectable_id` (`to_collectable_id`),
  CONSTRAINT `fk_collectable_transfer_balance_from_collectable_id` FOREIGN KEY (`from_collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collectable_transfer_balance_to_collectable_id` FOREIGN KEY (`to_collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collectable_transfer_balance`
--

LOCK TABLES `collectable_transfer_balance` WRITE;
/*!40000 ALTER TABLE `collectable_transfer_balance` DISABLE KEYS */;
/*!40000 ALTER TABLE `collectable_transfer_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collectable_transfer_value`
--

DROP TABLE IF EXISTS `collectable_transfer_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collectable_transfer_value` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_collectable_id` bigint(20) unsigned NOT NULL,
  `to_collectable_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `amount` decimal(13,4) NOT NULL,
  `balance` decimal(13,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collectable_transfer_value_created_datetime` (`created_datetime`),
  KEY `fk_collectable_transfer_value_from_collectable_id` (`from_collectable_id`),
  KEY `fk_collectable_transfer_value_to_collectable_id` (`to_collectable_id`),
  CONSTRAINT `fk_collectable_transfer_value_from_collectable_id` FOREIGN KEY (`from_collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collectable_transfer_value_to_collectable_id` FOREIGN KEY (`to_collectable_id`) REFERENCES `collectable` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collectable_transfer_value`
--

LOCK TABLES `collectable_transfer_value` WRITE;
/*!40000 ALTER TABLE `collectable_transfer_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `collectable_transfer_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event`
--

DROP TABLE IF EXISTS `collection_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `collection_event_type_id` int(10) unsigned NOT NULL,
  `collection_event_invocation_id` int(10) unsigned DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_collection_event_type_id` (`collection_event_type_id`),
  KEY `fk_collection_event_collection_event_invocation_id` (`collection_event_invocation_id`),
  KEY `fk_collection_event_status_id` (`status_id`),
  CONSTRAINT `fk_collection_event_collection_event_invocation_id` FOREIGN KEY (`collection_event_invocation_id`) REFERENCES `collection_event_invocation` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_collection_event_type_id` FOREIGN KEY (`collection_event_type_id`) REFERENCES `collection_event_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event`
--

LOCK TABLES `collection_event` WRITE;
/*!40000 ALTER TABLE `collection_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_action`
--

DROP TABLE IF EXISTS `collection_event_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `action_type_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_action_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_event_action_action_type_id` (`action_type_id`),
  CONSTRAINT `fk_collection_event_action_action_type_id` FOREIGN KEY (`action_type_id`) REFERENCES `action_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_action_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_action`
--

LOCK TABLES `collection_event_action` WRITE;
/*!40000 ALTER TABLE `collection_event_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_charge`
--

DROP TABLE IF EXISTS `collection_event_charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_charge` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `charge_type_id` bigint(20) unsigned NOT NULL,
  `minimum_amount` decimal(13,4) DEFAULT NULL,
  `maximum_amount` decimal(13,4) DEFAULT NULL,
  `percentage_outstanding_debt` decimal(3,2) DEFAULT NULL,
  `allow_recharge` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_charge_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_event_charge_charge_type_id` (`charge_type_id`),
  CONSTRAINT `fk_collection_event_charge_charge_type_id` FOREIGN KEY (`charge_type_id`) REFERENCES `ChargeType` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_charge_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_charge`
--

LOCK TABLES `collection_event_charge` WRITE;
/*!40000 ALTER TABLE `collection_event_charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event_charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_correspondence`
--

DROP TABLE IF EXISTS `collection_event_correspondence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_correspondence` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `correspondence_template_id` bigint(20) NOT NULL,
  `document_template_type_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_correspondence_correspondence_template_id` (`correspondence_template_id`),
  KEY `fk_collection_event_correspondence_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_event_correspondence_document_template_type_id` (`document_template_type_id`),
  CONSTRAINT `fk_collection_event_correspondence_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_correspondence_correspondence_template_id` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_correspondence_document_template_type_id` FOREIGN KEY (`document_template_type_id`) REFERENCES `DocumentTemplateType` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_correspondence`
--

LOCK TABLES `collection_event_correspondence` WRITE;
/*!40000 ALTER TABLE `collection_event_correspondence` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event_correspondence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_invocation`
--

DROP TABLE IF EXISTS `collection_event_invocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_invocation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_invocation`
--

LOCK TABLES `collection_event_invocation` WRITE;
/*!40000 ALTER TABLE `collection_event_invocation` DISABLE KEYS */;
INSERT INTO `collection_event_invocation` VALUES (1,'Automatic','Automatic','AUTOMATIC','COLLECTION_EVENT_INVOCATION_AUTOMATIC'),(2,'Manual','Manual','MANUAL','COLLECTION_EVENT_INVOCATION_MANUAL');
/*!40000 ALTER TABLE `collection_event_invocation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_oca`
--

DROP TABLE IF EXISTS `collection_event_oca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_oca` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `legal_fee_charge_type_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_oca_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_event_oca_legal_fee_charge_type_id` (`legal_fee_charge_type_id`),
  CONSTRAINT `fk_collection_event_oca_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_oca_legal_fee_charge_type_id` FOREIGN KEY (`legal_fee_charge_type_id`) REFERENCES `ChargeType` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_oca`
--

LOCK TABLES `collection_event_oca` WRITE;
/*!40000 ALTER TABLE `collection_event_oca` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event_oca` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_report`
--

DROP TABLE IF EXISTS `collection_event_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_report` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `report_sql` varchar(32767) DEFAULT NULL,
  `email_notification_id` bigint(20) unsigned NOT NULL,
  `collection_event_report_output_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_report_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_event_report_email_notification_id` (`email_notification_id`),
  KEY `fk_collection_event_report_collection_event_report_output_id` (`collection_event_report_output_id`),
  CONSTRAINT `fk_collection_event_report_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_report_collection_event_report_output_id` FOREIGN KEY (`collection_event_report_output_id`) REFERENCES `collection_event_report_output` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_report_email_notification_id` FOREIGN KEY (`email_notification_id`) REFERENCES `email_notification` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_report`
--

LOCK TABLES `collection_event_report` WRITE;
/*!40000 ALTER TABLE `collection_event_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_report_output`
--

DROP TABLE IF EXISTS `collection_event_report_output`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_report_output` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  `file_type_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_report_output_file_type_id` (`file_type_id`),
  CONSTRAINT `fk_collection_event_report_output_file_type_id` FOREIGN KEY (`file_type_id`) REFERENCES `file_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_report_output`
--

LOCK TABLES `collection_event_report_output` WRITE;
/*!40000 ALTER TABLE `collection_event_report_output` DISABLE KEYS */;
INSERT INTO `collection_event_report_output` VALUES (1,'CSV','CSV','CSV','COLLECTION_EVENT_REPORT_OUTPUT_CSV',15),(2,'Excel','MS Excel','EXCEL','COLLECTION_EVENT_REPORT_OUTPUT_EXCEL',1),(3,'Excel 2007','MS Excel 2007 XML','EXCEL_2007','COLLECTION_EVENT_REPORT_OUTPUT_EXCEL_2007',16);
/*!40000 ALTER TABLE `collection_event_report_output` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_severity`
--

DROP TABLE IF EXISTS `collection_event_severity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_severity` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `collection_severity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_severity_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_event_severity_collection_severity_id` (`collection_severity_id`),
  CONSTRAINT `fk_collection_event_severity_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_severity_collection_severity_id` FOREIGN KEY (`collection_severity_id`) REFERENCES `collection_severity` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_severity`
--

LOCK TABLES `collection_event_severity` WRITE;
/*!40000 ALTER TABLE `collection_event_severity` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_event_severity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_type`
--

DROP TABLE IF EXISTS `collection_event_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `collection_event_type_implementation_id` int(10) unsigned NOT NULL,
  `collection_event_invocation_id` int(10) unsigned DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_event_type_collection_event_type_implementation_id` (`collection_event_type_implementation_id`),
  KEY `fk_collection_event_type_collection_event_invocation` (`collection_event_invocation_id`),
  KEY `fk_collection_event_type_status_id` (`status_id`),
  CONSTRAINT `fk_collection_event_type_collection_event_invocation` FOREIGN KEY (`collection_event_invocation_id`) REFERENCES `collection_event_invocation` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_type_collection_event_type_implementation_id` FOREIGN KEY (`collection_event_type_implementation_id`) REFERENCES `collection_event_type_implementation` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_event_type_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_type`
--

LOCK TABLES `collection_event_type` WRITE;
/*!40000 ALTER TABLE `collection_event_type` DISABLE KEYS */;
INSERT INTO `collection_event_type` VALUES (1,'Correspondence','Correspondence','CORRESPONDENCE',1,NULL,1),(2,'Report','Report','REPORT',2,NULL,1),(3,'Action','Action','ACTION',3,NULL,1),(4,'Severity','Severity','SEVERITY',4,NULL,1),(5,'Barring','Barring','BARRING',5,NULL,1),(6,'OCA','OCA','OCA',6,NULL,1),(7,'TDC','TDC','TDC',7,NULL,1),(8,'Charge','Charge','CHARGE',8,NULL,1),(9,'Exit Collections','Exit Collections','EXIT_COLLECTIONS',9,NULL,1),(10,'Milestone','Milestone','MILESTONE',10,NULL,1);
/*!40000 ALTER TABLE `collection_event_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_event_type_implementation`
--

DROP TABLE IF EXISTS `collection_event_type_implementation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_event_type_implementation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  `class_name` varchar(256) NOT NULL,
  `is_scenario_event` tinyint(4) NOT NULL DEFAULT '1',
  `enforced_collection_event_invocation_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_implementation_collection_event_invocation_id` (`enforced_collection_event_invocation_id`),
  CONSTRAINT `fk_implementation_collection_event_invocation_id` FOREIGN KEY (`enforced_collection_event_invocation_id`) REFERENCES `collection_event_invocation` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_event_type_implementation`
--

LOCK TABLES `collection_event_type_implementation` WRITE;
/*!40000 ALTER TABLE `collection_event_type_implementation` DISABLE KEYS */;
INSERT INTO `collection_event_type_implementation` VALUES (1,'Correspondence','Correspondence','CORRESPONDENCE','COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE','Logic_Collection_Event_Correspondence',1,NULL),(2,'Report','Report','REPORT','COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT','Logic_Collection_Event_Report',1,NULL),(3,'Action','Action','ACTION','COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION','Logic_Collection_Event_Action',1,NULL),(4,'Severity','Severity','SEVERITY','COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY','Logic_Collection_Event_Severity',1,NULL),(5,'Barring','Barring','BARRING','COLLECTION_EVENT_TYPE_IMPLEMENTATION_BARRING','Logic_Collection_Event_Barring',1,NULL),(6,'OCA','OCA','OCA','COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA','Logic_Collection_Event_OCA',1,NULL),(7,'TDC','TDC','TDC','COLLECTION_EVENT_TYPE_IMPLEMENTATION_TDC','Logic_Collection_Event_TDC',1,NULL),(8,'Charge','Charge','CHARGE','COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE','Logic_Collection_Event_Charge',1,NULL),(9,'Exit Collections','Exit Collections','EXIT_COLLECTIONS','COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS','Logic_Collection_Event_ExitCollections',0,1),(10,'Milestone','Milestone','MILESTONE','COLLECTION_EVENT_TYPE_IMPLEMENTATION_MILESTONE','Logic_Collection_Event_Milestone',1,NULL);
/*!40000 ALTER TABLE `collection_event_type_implementation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_permissions_config`
--

DROP TABLE IF EXISTS `collection_permissions_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_permissions_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permissions` bigint(20) unsigned NOT NULL,
  `suspension_maximum_days` int(10) unsigned NOT NULL,
  `suspension_maximum_suspensions_per_collections_period` int(10) unsigned NOT NULL,
  `promise_start_delay_maximum_days` int(10) unsigned NOT NULL,
  `promise_maximum_days_between_due_and_end` int(10) unsigned NOT NULL,
  `promise_instalment_maximum_interval_days` int(10) unsigned NOT NULL,
  `promise_instalment_minimum_promised_percentage` decimal(3,2) unsigned NOT NULL,
  `promise_can_replace` tinyint(3) unsigned NOT NULL,
  `promise_create_maximum_severity_level` int(10) unsigned NOT NULL,
  `promise_amount_maximum` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_permissions_config`
--

LOCK TABLES `collection_permissions_config` WRITE;
/*!40000 ALTER TABLE `collection_permissions_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_permissions_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_promise`
--

DROP TABLE IF EXISTS `collection_promise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_promise` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `collection_promise_reason_id` int(10) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  `use_direct_debit` tinyint(4) NOT NULL DEFAULT '0',
  `completed_datetime` datetime DEFAULT NULL,
  `collection_promise_completion_id` int(10) unsigned DEFAULT NULL,
  `completed_employee_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collection_promise_created_datetime` (`created_datetime`),
  KEY `in_collection_promise_completed_datetime` (`completed_datetime`),
  KEY `fk_collection_promise_collection_promise_completion_id` (`collection_promise_completion_id`),
  KEY `fk_collection_promise_completed_employee_id` (`completed_employee_id`),
  KEY `fk_collection_promise_created_employee_id` (`created_employee_id`),
  KEY `fk_collection_promise_account_id` (`account_id`),
  KEY `fk_collection_promise_collection_promise_reason_id` (`collection_promise_reason_id`),
  CONSTRAINT `fk_collection_promise_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_promise_collection_promise_completion_id` FOREIGN KEY (`collection_promise_completion_id`) REFERENCES `collection_promise_completion` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_promise_collection_promise_reason_id` FOREIGN KEY (`collection_promise_reason_id`) REFERENCES `collection_promise_reason` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_promise_completed_employee_id` FOREIGN KEY (`completed_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_promise_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_promise`
--

LOCK TABLES `collection_promise` WRITE;
/*!40000 ALTER TABLE `collection_promise` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_promise` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_promise_completion`
--

DROP TABLE IF EXISTS `collection_promise_completion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_promise_completion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_promise_completion`
--

LOCK TABLES `collection_promise_completion` WRITE;
/*!40000 ALTER TABLE `collection_promise_completion` DISABLE KEYS */;
INSERT INTO `collection_promise_completion` VALUES (1,'Kept','Kept','KEPT','COLLECTION_PROMISE_COMPLETION_KEPT'),(2,'Broken','Broken','BROKEN','COLLECTION_PROMISE_COMPLETION_BROKEN'),(3,'Cancelled','Cancelled','CANCELLED','COLLECTION_PROMISE_COMPLETION_CANCELLED');
/*!40000 ALTER TABLE `collection_promise_completion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_promise_instalment`
--

DROP TABLE IF EXISTS `collection_promise_instalment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_promise_instalment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_promise_id` bigint(20) unsigned NOT NULL,
  `due_date` date DEFAULT NULL,
  `amount` decimal(13,4) DEFAULT NULL,
  `created_datetime` datetime NOT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collection_promise_instalment_due_date` (`due_date`),
  KEY `in_collection_promise_instalment_created_datetime` (`created_datetime`),
  KEY `fk_collection_promise_instalment_collection_promise_id` (`collection_promise_id`),
  KEY `fk_collection_promise_instalment_created_employee_id` (`created_employee_id`),
  CONSTRAINT `fk_collection_promise_instalment_collection_promise_id` FOREIGN KEY (`collection_promise_id`) REFERENCES `collection_promise` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_promise_instalment_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_promise_instalment`
--

LOCK TABLES `collection_promise_instalment` WRITE;
/*!40000 ALTER TABLE `collection_promise_instalment` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_promise_instalment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_promise_reason`
--

DROP TABLE IF EXISTS `collection_promise_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_promise_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_promise_reason_status_id` (`status_id`),
  CONSTRAINT `fk_collection_promise_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_promise_reason`
--

LOCK TABLES `collection_promise_reason` WRITE;
/*!40000 ALTER TABLE `collection_promise_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_promise_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_restriction`
--

DROP TABLE IF EXISTS `collection_restriction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_restriction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_restriction`
--

LOCK TABLES `collection_restriction` WRITE;
/*!40000 ALTER TABLE `collection_restriction` DISABLE KEYS */;
INSERT INTO `collection_restriction` VALUES (1,'Disallow Automatic Unbarring','Disallow Automatic Unbarring','DISALLOW_AUTOMATIC_UNBARRING','COLLECTION_RESTRICTION_DISALLOW_AUTOMATIC_UNBARRING'),(2,'Debt Consolidation','Debt Consolidation','DEBT_CONSOLIDATION','COLLECTION_RESTRICTION_DEBT_CONSOLIDATION');
/*!40000 ALTER TABLE `collection_restriction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_scenario`
--

DROP TABLE IF EXISTS `collection_scenario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_scenario` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `day_offset` int(10) unsigned NOT NULL DEFAULT '0',
  `working_status_id` int(10) unsigned NOT NULL,
  `threshold_percentage` int(11) NOT NULL,
  `threshold_amount` decimal(13,4) NOT NULL,
  `initial_collection_severity_id` int(10) unsigned DEFAULT NULL,
  `allow_automatic_unbar` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_collection_scenario_working_status_id` (`working_status_id`),
  KEY `fk_collection_scenario_initial_collection_severity_id` (`initial_collection_severity_id`),
  CONSTRAINT `fk_collection_scenario_initial_collection_severity_id` FOREIGN KEY (`initial_collection_severity_id`) REFERENCES `collection_severity` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_scenario_working_status_id` FOREIGN KEY (`working_status_id`) REFERENCES `working_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_scenario`
--

LOCK TABLES `collection_scenario` WRITE;
/*!40000 ALTER TABLE `collection_scenario` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_scenario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_scenario_collection_event`
--

DROP TABLE IF EXISTS `collection_scenario_collection_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_scenario_collection_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_scenario_id` bigint(20) unsigned NOT NULL,
  `collection_event_id` bigint(20) unsigned NOT NULL,
  `collection_event_invocation_id` int(10) unsigned DEFAULT NULL,
  `day_offset` int(11) DEFAULT NULL,
  `prerequisite_collection_scenario_collection_event_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_scenario_collection_event_collection_scenario_id` (`collection_scenario_id`),
  KEY `fk_collection_scenario_collection_event_collection_event_id` (`collection_event_id`),
  KEY `fk_collection_scenario_collection_event_event_invocation_id` (`collection_event_invocation_id`),
  KEY `fk_collection_scenario_collection_event_prerequisite_event_id` (`prerequisite_collection_scenario_collection_event_id`),
  CONSTRAINT `fk_collection_scenario_collection_event_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_scenario_collection_event_collection_scenario_id` FOREIGN KEY (`collection_scenario_id`) REFERENCES `collection_scenario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_scenario_collection_event_event_invocation_id` FOREIGN KEY (`collection_event_invocation_id`) REFERENCES `collection_event_invocation` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_scenario_collection_event_prerequisite_event_id` FOREIGN KEY (`prerequisite_collection_scenario_collection_event_id`) REFERENCES `collection_scenario_collection_event` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_scenario_collection_event`
--

LOCK TABLES `collection_scenario_collection_event` WRITE;
/*!40000 ALTER TABLE `collection_scenario_collection_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_scenario_collection_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_scenario_system`
--

DROP TABLE IF EXISTS `collection_scenario_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_scenario_system` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_scenario_system`
--

LOCK TABLES `collection_scenario_system` WRITE;
/*!40000 ALTER TABLE `collection_scenario_system` DISABLE KEYS */;
INSERT INTO `collection_scenario_system` VALUES (1,'Broken Promise to Pay','Broken Promise to Pay','BROKEN_PROMISE_TO_PAY','COLLECTION_SCENARIO_SYSTEM_BROKEN_PROMISE_TO_PAY'),(2,'Dishonoured Payment','Dishonoured Payment','DISHONOURED_PAYMENT','COLLECTION_SCENARIO_SYSTEM_DISHONOURED_PAYMENT'),(3,'Force Collections Exit','Force Collections Exit','','');
/*!40000 ALTER TABLE `collection_scenario_system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_scenario_system_config`
--

DROP TABLE IF EXISTS `collection_scenario_system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_scenario_system_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_scenario_system_id` int(10) unsigned NOT NULL,
  `collection_scenario_id` bigint(20) unsigned NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collection_scenario_system_config_start_datetime` (`start_datetime`),
  KEY `in_collection_scenario_system_config_end_datetime` (`end_datetime`),
  KEY `fk_collection_scenario_system_config_scenario_system_id` (`collection_scenario_system_id`),
  KEY `fk_collection_scenario_system_config_collection_scenario_id` (`collection_scenario_id`),
  CONSTRAINT `fk_collection_scenario_system_config_collection_scenario_id` FOREIGN KEY (`collection_scenario_id`) REFERENCES `collection_scenario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_scenario_system_config_scenario_system_id` FOREIGN KEY (`collection_scenario_system_id`) REFERENCES `collection_scenario_system` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_scenario_system_config`
--

LOCK TABLES `collection_scenario_system_config` WRITE;
/*!40000 ALTER TABLE `collection_scenario_system_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_scenario_system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_severity`
--

DROP TABLE IF EXISTS `collection_severity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_severity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `severity_level` int(10) unsigned NOT NULL,
  `working_status_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `in_collection_severity_severity_level` (`severity_level`),
  KEY `fk_collection_severity_working_status_id` (`working_status_id`),
  CONSTRAINT `fk_collection_severity_working_status_id` FOREIGN KEY (`working_status_id`) REFERENCES `working_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_severity`
--

LOCK TABLES `collection_severity` WRITE;
/*!40000 ALTER TABLE `collection_severity` DISABLE KEYS */;
INSERT INTO `collection_severity` VALUES (1,'Zero','Unrestricted','UNRESTRICTED',0,2),(2,'Liquidations / Bankruptcy Warning','Liquidations / Bankruptcy Warning',NULL,3,2),(3,'Collections Warning Message','Collections Warning Message',NULL,1,2),(4,'Manual Unbar Event','Manual Unbar Event',NULL,4,2),(5,'Under Review Warning Message','Under Review Warning Message',NULL,2,2);
/*!40000 ALTER TABLE `collection_severity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_severity_restriction`
--

DROP TABLE IF EXISTS `collection_severity_restriction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_severity_restriction` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_restriction_id` int(10) unsigned NOT NULL,
  `collection_severity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_severity_restriction_collection_severity_id` (`collection_severity_id`),
  KEY `fk_collection_severity_restriction_collection_restriction_id` (`collection_restriction_id`),
  CONSTRAINT `fk_collection_severity_restriction_collection_restriction_id` FOREIGN KEY (`collection_restriction_id`) REFERENCES `collection_restriction` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_severity_restriction_collection_severity_id` FOREIGN KEY (`collection_severity_id`) REFERENCES `collection_severity` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_severity_restriction`
--

LOCK TABLES `collection_severity_restriction` WRITE;
/*!40000 ALTER TABLE `collection_severity_restriction` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_severity_restriction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_severity_warning`
--

DROP TABLE IF EXISTS `collection_severity_warning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_severity_warning` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `collection_warning_id` int(10) unsigned NOT NULL,
  `collection_severity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_severity_warning_collection_warning_id` (`collection_warning_id`),
  KEY `fk_collection_severity_warning_collection_severity_id` (`collection_severity_id`),
  CONSTRAINT `fk_collection_severity_warning_collection_severity_id` FOREIGN KEY (`collection_severity_id`) REFERENCES `collection_severity` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_severity_warning_collection_warning_id` FOREIGN KEY (`collection_warning_id`) REFERENCES `collection_warning` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_severity_warning`
--

LOCK TABLES `collection_severity_warning` WRITE;
/*!40000 ALTER TABLE `collection_severity_warning` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_severity_warning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_suspension`
--

DROP TABLE IF EXISTS `collection_suspension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_suspension` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `start_datetime` datetime NOT NULL,
  `proposed_end_datetime` datetime NOT NULL,
  `start_employee_id` bigint(20) unsigned NOT NULL,
  `collection_suspension_reason_id` int(10) unsigned NOT NULL,
  `effective_end_datetime` datetime DEFAULT NULL,
  `end_employee_id` bigint(20) unsigned DEFAULT NULL,
  `collection_suspension_end_reason_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_collection_suspension_start_datetime` (`start_datetime`),
  KEY `in_collection_suspension_proposed_end_datetime` (`proposed_end_datetime`),
  KEY `in_collection_suspension_effective_end_datetime` (`effective_end_datetime`),
  KEY `fk_collection_suspension_collection_suspension_reason_id` (`collection_suspension_reason_id`),
  KEY `fk_collection_suspension_collection_suspension_end_reason_id` (`collection_suspension_end_reason_id`),
  KEY `fk_collection_suspension_account_id` (`account_id`),
  KEY `fk_collection_suspension_start_employee_id` (`start_employee_id`),
  KEY `fk_collection_suspension_end_employee_id` (`end_employee_id`),
  CONSTRAINT `fk_collection_suspension_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_suspension_collection_suspension_end_reason_id` FOREIGN KEY (`collection_suspension_end_reason_id`) REFERENCES `collection_suspension_end_reason` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_suspension_collection_suspension_reason_id` FOREIGN KEY (`collection_suspension_reason_id`) REFERENCES `collection_suspension_reason` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_suspension_end_employee_id` FOREIGN KEY (`end_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_suspension_start_employee_id` FOREIGN KEY (`start_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_suspension`
--

LOCK TABLES `collection_suspension` WRITE;
/*!40000 ALTER TABLE `collection_suspension` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_suspension` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_suspension_end_reason`
--

DROP TABLE IF EXISTS `collection_suspension_end_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_suspension_end_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  `collection_suspension_reason_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_suspension_end_reason_status_id` (`status_id`),
  KEY `fk_collection_suspension_end_reason_suspension_reason_id` (`collection_suspension_reason_id`),
  CONSTRAINT `fk_collection_suspension_end_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collection_suspension_end_reason_suspension_reason_id` FOREIGN KEY (`collection_suspension_reason_id`) REFERENCES `collection_suspension_reason` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_suspension_end_reason`
--

LOCK TABLES `collection_suspension_end_reason` WRITE;
/*!40000 ALTER TABLE `collection_suspension_end_reason` DISABLE KEYS */;
INSERT INTO `collection_suspension_end_reason` VALUES (1,'Expired','Suspension Expired','EXPIRED',1,NULL),(2,'Cancelled','Suspension Cancelled','CANCELLED',1,NULL),(3,'Issue/Dispute resolved','Issue/Dispute resolved',NULL,1,NULL),(4,'Payment Received/located','Payment Received/located',NULL,1,NULL),(5,'Suspended in error','Suspended in error',NULL,1,NULL),(6,'Timeframe not required','Timeframe not required',NULL,1,NULL),(7,'Management Approved','Management Approved',NULL,1,NULL);
/*!40000 ALTER TABLE `collection_suspension_end_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_suspension_reason`
--

DROP TABLE IF EXISTS `collection_suspension_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_suspension_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_suspension_reason_status_id` (`status_id`),
  CONSTRAINT `fk_collection_suspension_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_suspension_reason`
--

LOCK TABLES `collection_suspension_reason` WRITE;
/*!40000 ALTER TABLE `collection_suspension_reason` DISABLE KEYS */;
INSERT INTO `collection_suspension_reason` VALUES (1,'Suspension','Suspension','SUSPENSION',1),(2,'TIO Complaint','TIO Complaint','TIO_COMPLAINT',2),(3,'Extension','Extension','EXTENSION',2),(4,'Sending to Debt Collection','Sending to Debt Collection','SENDING_TO_DEBT_COLLECTION',2),(5,'With Debt Collection','With Debt Collection','WITH_DEBT_COLLECTION',2),(6,'Win Back','Win Back','WIN_BACK',2),(7,'Payment Plan','Payment Plan','PAYMENT_PLAN',2),(8,'Cooling Off','Cooling Off','COOLING_OFF',2),(9,'Billing issues','Billing issues',NULL,1),(10,'Charge disputes ','Charge disputes ',NULL,1),(11,'Missing Payment','Missing Payment',NULL,1),(12,'Natural Disasters','Natural Disasters',NULL,1),(13,'Management Approved','Management Approved',NULL,1),(14,'Payment in Transit','Payment in Transit',NULL,1);
/*!40000 ALTER TABLE `collection_suspension_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_warning`
--

DROP TABLE IF EXISTS `collection_warning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_warning` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `message` varchar(1024) NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_collection_warning_status_id` (`status_id`),
  CONSTRAINT `fk_collection_warning_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_warning`
--

LOCK TABLES `collection_warning` WRITE;
/*!40000 ALTER TABLE `collection_warning` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_warning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_config`
--

DROP TABLE IF EXISTS `collections_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_datetime` datetime NOT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  `promise_instalment_leniency_days` int(10) unsigned NOT NULL DEFAULT '0',
  `direct_debit_due_date_offset` int(11) NOT NULL DEFAULT '0',
  `promise_direct_debit_due_date_offset` int(11) NOT NULL DEFAULT '0',
  `oca_final_invoice_delivery_method_id` bigint(20) unsigned DEFAULT NULL,
  `promise_instalment_leniency_dollars` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `event_action_offset_days` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `in_collections_config_created_datetime` (`created_datetime`),
  KEY `fk_collections_config_created_employee_id` (`created_employee_id`),
  KEY `fk_collections_config_oca_final_invoice_delivery_method_id` (`oca_final_invoice_delivery_method_id`),
  CONSTRAINT `fk_collections_config_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collections_config_oca_final_invoice_delivery_method_id` FOREIGN KEY (`oca_final_invoice_delivery_method_id`) REFERENCES `delivery_method` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections_config`
--

LOCK TABLES `collections_config` WRITE;
/*!40000 ALTER TABLE `collections_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `collections_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_schedule`
--

DROP TABLE IF EXISTS `collections_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections_schedule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `day` int(10) unsigned DEFAULT NULL COMMENT 'Day of Month',
  `month` int(10) unsigned DEFAULT NULL COMMENT 'Month of Year',
  `year` int(10) unsigned DEFAULT NULL COMMENT 'Full Year (e.g. 2011)',
  `monday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Monday',
  `tuesday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Tuesday',
  `wednesday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Wednesday',
  `thursday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Thursday',
  `friday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Friday',
  `saturday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Saturday',
  `sunday` tinyint(3) unsigned DEFAULT NULL COMMENT 'Every Sunday',
  `collection_event_id` bigint(20) unsigned DEFAULT NULL COMMENT '(Optional) Event to apply to exclusively',
  `is_direct_debit` tinyint(3) unsigned DEFAULT NULL COMMENT '(Optional) If given apply direct debiting exclusively',
  `eligibility` tinyint(3) unsigned NOT NULL COMMENT '1: Collections/Event is eligible, 0: Collections/Event is NOT eligible',
  `precedence` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Represents the importance of this schedule rule over any conflicting rules, can be any positive number',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) status, if inactive will be ignored',
  PRIMARY KEY (`id`),
  KEY `fk_collections_schedule_status_id` (`status_id`),
  KEY `fk_collections_schedule_collection_event_id` (`collection_event_id`),
  CONSTRAINT `fk_collections_schedule_collection_event_id` FOREIGN KEY (`collection_event_id`) REFERENCES `collection_event` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_collections_schedule_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections_schedule`
--

LOCK TABLES `collections_schedule` WRITE;
/*!40000 ALTER TABLE `collections_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `collections_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_scale`
--

DROP TABLE IF EXISTS `commission_scale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commission_scale` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scale_type` smallint(6) NOT NULL,
  `rate_id` bigint(6) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `is_percentage` smallint(1) unsigned NOT NULL,
  `level_1` decimal(10,0) NOT NULL,
  `level_2` decimal(10,0) NOT NULL,
  `royalty_scale` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `royalty_scale` (`royalty_scale`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_scale`
--

LOCK TABLES `commission_scale` WRITE;
/*!40000 ALTER TABLE `commission_scale` DISABLE KEYS */;
/*!40000 ALTER TABLE `commission_scale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compression_algorithm`
--

DROP TABLE IF EXISTS `compression_algorithm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compression_algorithm` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Compression Algorithm',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Compression Algorithm',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Compression Algorithm',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Compression Algorithm',
  `file_extension` varchar(10) NOT NULL COMMENT 'File Extension (including ''.'') for the Compressed File',
  `php_stream_wrapper` varchar(50) DEFAULT NULL COMMENT 'PHP fopen() stream wrapper prefix',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compression_algorithm`
--

LOCK TABLES `compression_algorithm` WRITE;
/*!40000 ALTER TABLE `compression_algorithm` DISABLE KEYS */;
INSERT INTO `compression_algorithm` VALUES (1,'None','No compression','COMPRESSION_ALGORITHM_NONE','',NULL),(2,'bzip2','bzip2','COMPRESSION_ALGORITHM_BZIP2','.bz2','compress.bzip2://'),(3,'gzip','gzip','COMPRESSION_ALGORITHM_GZIP','.gz','compress.zlib://');
/*!40000 ALTER TABLE `compression_algorithm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_action`
--

DROP TABLE IF EXISTS `contact_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `contact_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Contact',
  `action_id` bigint(20) unsigned NOT NULL COMMENT '(FK) action',
  PRIMARY KEY (`id`),
  KEY `fk_contact_action_contact_id` (`contact_id`),
  KEY `fk_contact_action_action_id` (`action_id`),
  CONSTRAINT `fk_contact_action_action_id` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_contact_action_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_action`
--

LOCK TABLES `contact_action` WRITE;
/*!40000 ALTER TABLE `contact_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_title`
--

DROP TABLE IF EXISTS `contact_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_title` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this record',
  `name` varchar(255) NOT NULL COMMENT 'Unique title',
  `description` varchar(255) NOT NULL COMMENT 'Description',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_contact_title_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines contact titles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_title`
--

LOCK TABLES `contact_title` WRITE;
/*!40000 ALTER TABLE `contact_title` DISABLE KEYS */;
INSERT INTO `contact_title` VALUES (1,'Dr','Doctor'),(2,'Mr','Mister'),(3,'Mrs','Missus'),(4,'Mstr','Master'),(5,'Miss','Miss'),(6,'Ms','Ms'),(7,'Esq','Esquire'),(8,'Prof','Professor');
/*!40000 ALTER TABLE `contact_title` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contract_breach_reason`
--

DROP TABLE IF EXISTS `contract_breach_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract_breach_reason` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Contract Breach Reason',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Contract Breach Reason',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Contract Breach Reason',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Contract Breach Reason',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract_breach_reason`
--

LOCK TABLES `contract_breach_reason` WRITE;
/*!40000 ALTER TABLE `contract_breach_reason` DISABLE KEYS */;
INSERT INTO `contract_breach_reason` VALUES (1,'Churned','Service Churned','CONTRACT_BREACH_REASON_CHURNED'),(2,'Disconnected','Service Disconnected','CONTRACT_BREACH_REASON_DISCONNECTED'),(3,'Upgrade','Plan Upgraded','CONTRACT_BREACH_REASON_UPGRADE'),(4,'Downgrade','Plan Downgraded','CONTRACT_BREACH_REASON_DOWNGRADE'),(5,'Crossgrade','Plan Crossgraded','CONTRACT_BREACH_REASON_CROSSGRADE'),(6,'Moved','Service Moved to another Account','CONTRACT_BREACH_REASON_MOVED'),(7,'Other','Other','CONTRACT_BREACH_REASON_OTHER');
/*!40000 ALTER TABLE `contract_breach_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contract_status`
--

DROP TABLE IF EXISTS `contract_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Contract Status',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Contract Status',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Contract Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Contract Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract_status`
--

LOCK TABLES `contract_status` WRITE;
/*!40000 ALTER TABLE `contract_status` DISABLE KEYS */;
INSERT INTO `contract_status` VALUES (1,'Active','Contract Active','CONTRACT_STATUS_ACTIVE'),(2,'Expired','Contract Expired','CONTRACT_STATUS_EXPIRED'),(3,'Breached','Contract Breached','CONTRACT_STATUS_BREACHED');
/*!40000 ALTER TABLE `contract_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contract_terms`
--

DROP TABLE IF EXISTS `contract_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract_terms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this version of the Contract Terms',
  `created_by` bigint(20) NOT NULL COMMENT '(FK) Employee who created this version',
  `created_on` datetime NOT NULL COMMENT 'Date this version was created',
  `contract_payout_minimum_invoices` int(10) NOT NULL COMMENT 'Minimum number of invoices for the contract before Contract Payouts are charged',
  `exit_fee_minimum_invoices` int(10) NOT NULL COMMENT 'Minimum number of invoices for the contract before Exit Fees are charged',
  `payout_charge_type_id` bigint(20) DEFAULT NULL COMMENT '(FK) The ChargeType for the Contract Payout Fee',
  `exit_fee_charge_type_id` bigint(20) DEFAULT NULL COMMENT '(FK) The ChargeType for the Contract Exit Fee',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract_terms`
--

LOCK TABLES `contract_terms` WRITE;
/*!40000 ALTER TABLE `contract_terms` DISABLE KEYS */;
/*!40000 ALTER TABLE `contract_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence`
--

DROP TABLE IF EXISTS `correspondence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_run_id` bigint(20) NOT NULL,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `customer_group_id` bigint(20) NOT NULL,
  `correspondence_delivery_method_id` bigint(20) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `suburb` varchar(255) NOT NULL,
  `postcode` char(4) NOT NULL,
  `state` varchar(3) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(25) DEFAULT NULL,
  `landline` varchar(25) DEFAULT NULL,
  `pdf_file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_run_id` (`correspondence_run_id`),
  KEY `account_id` (`account_id`),
  KEY `correspondence_delivery_method_id` (`correspondence_delivery_method_id`),
  KEY `fk_correspondence_customergroup_id` (`customer_group_id`),
  CONSTRAINT `fk_correspondence_account` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_customergroup_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_delivery_method_id` FOREIGN KEY (`correspondence_delivery_method_id`) REFERENCES `correspondence_delivery_method` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run` FOREIGN KEY (`correspondence_run_id`) REFERENCES `correspondence_run` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence`
--

LOCK TABLES `correspondence` WRITE;
/*!40000 ALTER TABLE `correspondence` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_data`
--

DROP TABLE IF EXISTS `correspondence_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `value` varchar(2048) DEFAULT NULL,
  `correspondence_template_column_id` bigint(20) NOT NULL,
  `correspondence_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_template_column_id` (`correspondence_template_column_id`),
  KEY `correspondence_id` (`correspondence_id`),
  CONSTRAINT `fk_correspondence_data_correspondence` FOREIGN KEY (`correspondence_id`) REFERENCES `correspondence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_data_correspondence_template_column` FOREIGN KEY (`correspondence_template_column_id`) REFERENCES `correspondence_template_column` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_data`
--

LOCK TABLES `correspondence_data` WRITE;
/*!40000 ALTER TABLE `correspondence_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_delivery_method`
--

DROP TABLE IF EXISTS `correspondence_delivery_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_delivery_method` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `const_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_delivery_method`
--

LOCK TABLES `correspondence_delivery_method` WRITE;
/*!40000 ALTER TABLE `correspondence_delivery_method` DISABLE KEYS */;
INSERT INTO `correspondence_delivery_method` VALUES (1,'Post','Post','POST','CORRESPONDENCE_DELIVERY_METHOD_POST'),(2,'Email','Email','EMAIL','CORRESPONDENCE_DELIVERY_METHOD_EMAIL'),(3,'SMS','SMS','SMS','CORRESPONDENCE_DELIVERY_METHOD_SMS');
/*!40000 ALTER TABLE `correspondence_delivery_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_run`
--

DROP TABLE IF EXISTS `correspondence_run`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_run` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_template_id` bigint(20) NOT NULL,
  `file_import_id` bigint(20) unsigned DEFAULT NULL,
  `processed_datetime` timestamp NULL DEFAULT NULL,
  `scheduled_datetime` datetime NOT NULL,
  `delivered_datetime` timestamp NULL DEFAULT NULL,
  `created_employee_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `preprinted` tinyint(3) unsigned NOT NULL,
  `correspondence_run_error_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_template_id` (`correspondence_template_id`),
  KEY `fk_correspondence_run_correspondence_error_id` (`correspondence_run_error_id`),
  KEY `fk_correspondence_run_file_import_id` (`file_import_id`),
  CONSTRAINT `fk_correspondence_run_correspondence_error_id` FOREIGN KEY (`correspondence_run_error_id`) REFERENCES `correspondence_run_error` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run_file_import_id` FOREIGN KEY (`file_import_id`) REFERENCES `FileImport` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_template_id` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_run`
--

LOCK TABLES `correspondence_run` WRITE;
/*!40000 ALTER TABLE `correspondence_run` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_run` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_run_batch`
--

DROP TABLE IF EXISTS `correspondence_run_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_run_batch` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `batch_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_run_batch`
--

LOCK TABLES `correspondence_run_batch` WRITE;
/*!40000 ALTER TABLE `correspondence_run_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_run_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_run_dispatch`
--

DROP TABLE IF EXISTS `correspondence_run_dispatch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_run_dispatch` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_run_id` bigint(20) NOT NULL,
  `correspondence_run_batch_id` bigint(20) DEFAULT NULL,
  `data_file_export_id` bigint(20) unsigned DEFAULT NULL,
  `pdf_file_export_id` bigint(20) unsigned DEFAULT NULL,
  `correspondence_template_carrier_module_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_correspondence_run_dispatch_correspondence_run_id` (`correspondence_run_id`),
  KEY `fk_correspondence_run_dispatch_correspondence_delivery_batch` (`correspondence_run_batch_id`),
  KEY `fk_correspondence_run_dispatch_file_export_id` (`data_file_export_id`),
  KEY `fk_correspondence_run_dispatch_pdf_file_export_id` (`pdf_file_export_id`),
  KEY `fk_correspondence_run_dispatch_ct_carrier_id` (`correspondence_template_carrier_module_id`),
  CONSTRAINT `fk_correspondence_run_dispatch_correspondence_delivery_batch` FOREIGN KEY (`correspondence_run_batch_id`) REFERENCES `correspondence_run_batch` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run_dispatch_correspondence_run_id` FOREIGN KEY (`correspondence_run_id`) REFERENCES `correspondence_run` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run_dispatch_ct_carrier_id` FOREIGN KEY (`correspondence_template_carrier_module_id`) REFERENCES `correspondence_template_carrier_module` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run_dispatch_file_export_id` FOREIGN KEY (`data_file_export_id`) REFERENCES `FileExport` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run_dispatch_pdf_file_export_id` FOREIGN KEY (`pdf_file_export_id`) REFERENCES `FileExport` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_run_dispatch`
--

LOCK TABLES `correspondence_run_dispatch` WRITE;
/*!40000 ALTER TABLE `correspondence_run_dispatch` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_run_dispatch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_run_dispatch_delivery_method`
--

DROP TABLE IF EXISTS `correspondence_run_dispatch_delivery_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_run_dispatch_delivery_method` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_run_dispatch_id` bigint(20) NOT NULL,
  `correspondence_delivery_method_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_correspondence_run_dispatch_deliverymethod_cr_fexport_id` (`correspondence_run_dispatch_id`),
  KEY `fk_correspondence_run_dispatch_deliverymethod_c_del_mth_id` (`correspondence_delivery_method_id`),
  CONSTRAINT `fk_correspondence_run_dispatch_deliverymethod_cr_fexport_id` FOREIGN KEY (`correspondence_run_dispatch_id`) REFERENCES `correspondence_run_dispatch` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_run_dispatch_deliverymethod_c_del_mth_id` FOREIGN KEY (`correspondence_delivery_method_id`) REFERENCES `correspondence_delivery_method` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_run_dispatch_delivery_method`
--

LOCK TABLES `correspondence_run_dispatch_delivery_method` WRITE;
/*!40000 ALTER TABLE `correspondence_run_dispatch_delivery_method` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_run_dispatch_delivery_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_run_error`
--

DROP TABLE IF EXISTS `correspondence_run_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_run_error` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `const_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_run_error`
--

LOCK TABLES `correspondence_run_error` WRITE;
/*!40000 ALTER TABLE `correspondence_run_error` DISABLE KEYS */;
INSERT INTO `correspondence_run_error` VALUES (1,'Sql Syntax Error','Sql Syntax Error','SQL_SYNTAX','CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX'),(2,'Malformed Input','Malformed Input','MALFORMED_INPUT','CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT'),(3,'No Data','No Data','NO_DATA','CORRESPONDENCE_RUN_ERROR_NO_DATA'),(4,'Dataset Mismatch','Dataset Mismatch','DATASET_MISMATCH','CORRESPONDENCE_RUN_ERROR_DATASET_MISMATCH');
/*!40000 ALTER TABLE `correspondence_run_error` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_source`
--

DROP TABLE IF EXISTS `correspondence_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_source` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_source_type_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source_type_id` (`correspondence_source_type_id`),
  CONSTRAINT `fk_correspondence_source_type_id` FOREIGN KEY (`correspondence_source_type_id`) REFERENCES `correspondence_source_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_source`
--

LOCK TABLES `correspondence_source` WRITE;
/*!40000 ALTER TABLE `correspondence_source` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_source` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_source_sql`
--

DROP TABLE IF EXISTS `correspondence_source_sql`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_source_sql` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_source_id` bigint(20) NOT NULL,
  `sql_syntax` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_source_id` (`correspondence_source_id`),
  CONSTRAINT `fk_correspondence_source_sql_correspondence_source_id` FOREIGN KEY (`correspondence_source_id`) REFERENCES `correspondence_source` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_source_sql`
--

LOCK TABLES `correspondence_source_sql` WRITE;
/*!40000 ALTER TABLE `correspondence_source_sql` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_source_sql` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_source_sql_accounts`
--

DROP TABLE IF EXISTS `correspondence_source_sql_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_source_sql_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `correspondence_source_id` bigint(20) NOT NULL,
  `sql_syntax` longtext,
  `enforce_account_set` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_correspondence_source_sql_accounts_correspondence_source_id` (`correspondence_source_id`),
  CONSTRAINT `fk_correspondence_source_sql_accounts_correspondence_source_id` FOREIGN KEY (`correspondence_source_id`) REFERENCES `correspondence_source` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_source_sql_accounts`
--

LOCK TABLES `correspondence_source_sql_accounts` WRITE;
/*!40000 ALTER TABLE `correspondence_source_sql_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_source_sql_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_source_type`
--

DROP TABLE IF EXISTS `correspondence_source_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_source_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(510) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `const_name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `is_user_selectable` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_source_type`
--

LOCK TABLES `correspondence_source_type` WRITE;
/*!40000 ALTER TABLE `correspondence_source_type` DISABLE KEYS */;
INSERT INTO `correspondence_source_type` VALUES (1,'System','System','SYSTEM','CORRESPONDENCE_SOURCE_TYPE_SYSTEM','Correspondence_Source_System',0),(2,'CSV','CSV','CSV','CORRESPONDENCE_SOURCE_TYPE_CSV','Correspondence_Source_CSV',1),(3,'SQL','SQL','SQL','CORRESPONDENCE_SOURCE_TYPE_SQL','Correspondence_Source_SQL',1),(4,'SQL Accounts','SQL with a placeholder for Account Ids','SQL_ACCOUNTS','CORRESPONDENCE_SOURCE_TYPE_SQL_ACCOUNTS','Correspondence_Source_SQLAccounts',0);
/*!40000 ALTER TABLE `correspondence_source_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_template`
--

DROP TABLE IF EXISTS `correspondence_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_template` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(510) NOT NULL,
  `created_employee_id` bigint(20) NOT NULL,
  `created_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `correspondence_source_id` bigint(20) NOT NULL,
  `status_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_source_id` (`correspondence_source_id`),
  CONSTRAINT `fk_correspondence_template_correspondence_source_id` FOREIGN KEY (`correspondence_source_id`) REFERENCES `correspondence_source` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_template`
--

LOCK TABLES `correspondence_template` WRITE;
/*!40000 ALTER TABLE `correspondence_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_template_carrier_module`
--

DROP TABLE IF EXISTS `correspondence_template_carrier_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_template_carrier_module` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `carrier_module_id` bigint(20) unsigned NOT NULL,
  `template_code` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_correspondence_template_carrier_module_carrier_module_id` (`carrier_module_id`),
  CONSTRAINT `fk_correspondence_template_carrier_module_carrier_module_id` FOREIGN KEY (`carrier_module_id`) REFERENCES `CarrierModule` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_template_carrier_module`
--

LOCK TABLES `correspondence_template_carrier_module` WRITE;
/*!40000 ALTER TABLE `correspondence_template_carrier_module` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_template_carrier_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_template_column`
--

DROP TABLE IF EXISTS `correspondence_template_column`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_template_column` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL,
  `column_index` int(5) NOT NULL,
  `correspondence_template_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_template_id` (`correspondence_template_id`),
  CONSTRAINT `fk_correspondence_template_column_correspondence_template` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_template_column`
--

LOCK TABLES `correspondence_template_column` WRITE;
/*!40000 ALTER TABLE `correspondence_template_column` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_template_column` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondence_template_correspondence_template_carrier_module`
--

DROP TABLE IF EXISTS `correspondence_template_correspondence_template_carrier_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correspondence_template_correspondence_template_carrier_module` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `correspondence_template_id` bigint(20) NOT NULL,
  `correspondence_template_carrier_module_id` bigint(20) NOT NULL,
  `correspondence_delivery_method_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `correspondence_template_correspondence_template_carrier_module_1` (`correspondence_template_id`),
  KEY `correspondence_template_correspondence_template_carrier_module_2` (`correspondence_template_carrier_module_id`),
  KEY `correspondence_template_correspondence_template_carrier_module_3` (`correspondence_delivery_method_id`),
  CONSTRAINT `fk_correspondence_template_correspondence_template_c_m_1` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_correspondence_template_correspondence_template_c_m_2` FOREIGN KEY (`correspondence_template_carrier_module_id`) REFERENCES `correspondence_template_carrier_module` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_correspondence_template_correspondence_template_c_m_3` FOREIGN KEY (`correspondence_delivery_method_id`) REFERENCES `correspondence_delivery_method` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondence_template_correspondence_template_carrier_module`
--

LOCK TABLES `correspondence_template_correspondence_template_carrier_module` WRITE;
/*!40000 ALTER TABLE `correspondence_template_correspondence_template_carrier_module` DISABLE KEYS */;
/*!40000 ALTER TABLE `correspondence_template_correspondence_template_carrier_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this record',
  `name` varchar(255) NOT NULL COMMENT 'Unique name of the country',
  `code_3_char` char(3) DEFAULT NULL COMMENT '3-character Country Code (ISO 3166-1 alpha-3)',
  `code_2_char` char(2) DEFAULT NULL COMMENT '2-character Country Code (ISO 3166-1 alpha-2)',
  `has_postcode` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1: This Country uses Postcodes; 0: No Postcodes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_country_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines various countries';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country`
--

LOCK TABLES `country` WRITE;
/*!40000 ALTER TABLE `country` DISABLE KEYS */;
INSERT INTO `country` VALUES (1,'Australia','AUS','AU',1),(2,'India','IND','IN',1);
/*!40000 ALTER TABLE `country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_card_payment_config`
--

DROP TABLE IF EXISTS `credit_card_payment_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_card_payment_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_group_id` bigint(20) unsigned NOT NULL COMMENT 'FK to CustomerGroup table',
  `merchant_id` varchar(255) NOT NULL COMMENT 'Merchant Id (E.g. Secure Pay Merchant Id)',
  `password` varchar(255) NOT NULL COMMENT 'Merchant Password',
  `confirmation_text` mediumtext NOT NULL COMMENT 'Message displayed to confirm payment',
  `direct_debit_text` mediumtext NOT NULL COMMENT 'Message displayed to confirm direct debit setup',
  `confirmation_email` mediumtext NOT NULL COMMENT 'Body of email sent to confirm payment',
  `direct_debit_email` mediumtext NOT NULL COMMENT 'Body of email sent to confirm direct debit setup',
  `direct_debit_disclaimer` mediumtext NOT NULL COMMENT 'Terms and conditions displayed to user when opting for direct debit',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Details of credit card payment config for customer groups';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_card_payment_config`
--

LOCK TABLES `credit_card_payment_config` WRITE;
/*!40000 ALTER TABLE `credit_card_payment_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_card_payment_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_card_payment_history`
--

DROP TABLE IF EXISTS `credit_card_payment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_card_payment_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL COMMENT 'FK to Account table',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT 'FK to Employee table',
  `contact_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to Contact table',
  `receipt_number` varchar(255) NOT NULL COMMENT 'Receipt number for payment',
  `amount` decimal(13,4) DEFAULT NULL,
  `payment_datetime` datetime DEFAULT NULL COMMENT 'Date/Time of the payment',
  `txn_id` varchar(255) DEFAULT NULL COMMENT 'TXN Id issued by credit card payment service provider',
  `payment_id` bigint(20) NOT NULL COMMENT 'FK to the Payment table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Details of credit card payments made';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_card_payment_history`
--

LOCK TABLES `credit_card_payment_history` WRITE;
/*!40000 ALTER TABLE `credit_card_payment_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_card_payment_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_card_type`
--

DROP TABLE IF EXISTS `credit_card_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_card_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the card',
  `description` varchar(255) NOT NULL COMMENT 'Description of the card',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  `surcharge` decimal(6,3) DEFAULT NULL COMMENT 'The percentage surcharge added to payments made by this card type',
  `valid_lengths` varchar(255) NOT NULL COMMENT 'CSL of valid card number lengths',
  `valid_prefixes` varchar(255) NOT NULL COMMENT 'CSL of valid card number prefixes',
  `cvv_length` tinyint(4) NOT NULL COMMENT 'Length of CVV number for card type',
  `minimum_amount` decimal(13,4) NOT NULL COMMENT 'Minimum value of a credit card transaction',
  `maximum_amount` decimal(13,4) NOT NULL COMMENT 'Maximum value of a credit card transaction',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='Credit card types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_card_type`
--

LOCK TABLES `credit_card_type` WRITE;
/*!40000 ALTER TABLE `credit_card_type` DISABLE KEYS */;
INSERT INTO `credit_card_type` VALUES (1,'VISA','VISA','CREDIT_CARD_TYPE_VISA',0.015,'13,16','4',3,5.0000,10000.0000),(2,'MasterCard','MasterCard','CREDIT_CARD_TYPE_MASTERCARD',0.015,'16','51,52,53,54,55',3,5.0000,10000.0000),(4,'American Express','American Express','CREDIT_CARD_TYPE_AMEX',0.030,'15','34,37',4,5.0000,10000.0000),(5,'Diners Club','Diners Club','CREDIT_CARD_TYPE_DINERS',0.030,'14','30,36,38',3,5.0000,10000.0000);
/*!40000 ALTER TABLE `credit_card_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_control_status`
--

DROP TABLE IF EXISTS `credit_control_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_control_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the status',
  `name` varchar(50) NOT NULL COMMENT 'Name of the status',
  `can_bar` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether or not the account can be barred',
  `send_late_notice` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether or not to send late notices for account',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Credit Control Status for accounts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_control_status`
--

LOCK TABLES `credit_control_status` WRITE;
/*!40000 ALTER TABLE `credit_control_status` DISABLE KEYS */;
INSERT INTO `credit_control_status` VALUES (1,'Up to date',1,1,'Can be barred.','CREDIT_CONTROL_STATUS_UP_TO_DATE'),(2,'Extension',0,1,'Do not bar.','CREDIT_CONTROL_STATUS_EXTENSION'),(3,'Sending to Debt Collection',0,0,'No late notices or barring.','CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION'),(4,'With Debt Collection',0,0,'No late notices or barring.','CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION'),(5,'Win Back',0,1,'Do not bar.','CREDIT_CONTROL_STATUS_WIN_BACK'),(6,'Payment Plan',0,0,'No late notices or barring.','CREDIT_CONTROL_STATUS_PAYMENT_PLAN'),(7,'Cooling Off',0,0,'Do not bar or send Late Notices.','CREDIT_CONTROL_STATUS_COOLING_OFF');
/*!40000 ALTER TABLE `credit_control_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_control_status_history`
--

DROP TABLE IF EXISTS `credit_control_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_control_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for the historic event',
  `account` bigint(20) unsigned NOT NULL COMMENT 'Affected account',
  `from_status` bigint(20) unsigned NOT NULL COMMENT 'The original credit_control_status.id',
  `to_status` bigint(20) unsigned NOT NULL COMMENT 'The new credit_control_status.id',
  `employee` bigint(20) unsigned NOT NULL COMMENT 'Employee who effected the change',
  `change_datetime` datetime DEFAULT NULL COMMENT 'Date/Time of the change to this status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Credit control status change history';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_control_status_history`
--

LOCK TABLES `credit_control_status_history` WRITE;
/*!40000 ALTER TABLE `credit_control_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_control_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `current_service_account`
--

DROP TABLE IF EXISTS `current_service_account`;
/*!50001 DROP VIEW IF EXISTS `current_service_account`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `current_service_account` (
  `serviceId` bigint(20) unsigned,
  `accountId` bigint(20) unsigned
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `customer_faq`
--

DROP TABLE IF EXISTS `customer_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_faq` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT 'Field to record faq ids',
  `title` varchar(255) DEFAULT NULL COMMENT 'field for faq title',
  `contents` text COMMENT 'field for faq contents',
  `time_added` timestamp NULL DEFAULT NULL COMMENT 'time faq was added',
  `time_updated` timestamp NULL DEFAULT NULL COMMENT 'time faq was updated',
  `download` blob COMMENT 'faq related download',
  `customer_group_id` varchar(10) DEFAULT NULL COMMENT 'customer group for faq',
  `hits` mediumint(11) DEFAULT NULL COMMENT 'hits or amount of times clicked',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `title` (`title`,`contents`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table for customer faqs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_faq`
--

LOCK TABLES `customer_faq` WRITE;
/*!40000 ALTER TABLE `customer_faq` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_faq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_group_delivery_method`
--

DROP TABLE IF EXISTS `customer_group_delivery_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_group_delivery_method` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `customer_group_id` bigint(20) NOT NULL COMMENT '(FK) Customer Group',
  `delivery_method_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Delivery Method',
  `minimum_invoice_value` decimal(13,4) NOT NULL COMMENT 'Minimum Invoice value for this Delivery Method to apply',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who defined this setting',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Timestamp',
  PRIMARY KEY (`id`),
  KEY `fk_customer_group_delivery_method_customer_group_id` (`customer_group_id`),
  KEY `fk_customer_group_delivery_method_delivery_method_id` (`delivery_method_id`),
  KEY `fk_customer_group_delivery_method_employee_id` (`employee_id`),
  CONSTRAINT `fk_customer_group_delivery_method_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_customer_group_delivery_method_delivery_method_id` FOREIGN KEY (`delivery_method_id`) REFERENCES `delivery_method` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_customer_group_delivery_method_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_group_delivery_method`
--

LOCK TABLES `customer_group_delivery_method` WRITE;
/*!40000 ALTER TABLE `customer_group_delivery_method` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_group_delivery_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_group_payment_method`
--

DROP TABLE IF EXISTS `customer_group_payment_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_group_payment_method` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `customer_group_id` bigint(20) NOT NULL COMMENT '(FK) Customer Group',
  `payment_method_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Payment Method',
  `surcharge_percent` decimal(4,4) DEFAULT NULL COMMENT 'Payment Method-level Surcharge for this Customer Group',
  PRIMARY KEY (`id`),
  KEY `fk_customer_group_payment_method_customer_group_id` (`customer_group_id`),
  KEY `fk_customer_group_payment_method_payment_method_id` (`payment_method_id`),
  CONSTRAINT `fk_customer_group_payment_method_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_customer_group_payment_method_payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_group_payment_method`
--

LOCK TABLES `customer_group_payment_method` WRITE;
/*!40000 ALTER TABLE `customer_group_payment_method` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_group_payment_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_group_rebill_type`
--

DROP TABLE IF EXISTS `customer_group_rebill_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_group_rebill_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `customer_group_id` bigint(20) NOT NULL COMMENT '(FK) Customer Group',
  `rebill_type_id` int(10) unsigned NOT NULL COMMENT '(FK) Rebill Type',
  `surcharge_percent` decimal(4,4) DEFAULT NULL COMMENT 'Rebill Type-level Surcharge for this Customer Group',
  PRIMARY KEY (`id`),
  KEY `fk_customer_group_rebill_type_customer_group_id` (`customer_group_id`),
  KEY `fk_customer_group_rebill_type_rebill_type_id` (`rebill_type_id`),
  CONSTRAINT `fk_customer_group_rebill_type_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_customer_group_rebill_type_rebill_type_id` FOREIGN KEY (`rebill_type_id`) REFERENCES `rebill_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_group_rebill_type`
--

LOCK TABLES `customer_group_rebill_type` WRITE;
/*!40000 ALTER TABLE `customer_group_rebill_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_group_rebill_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_status`
--

DROP TABLE IF EXISTS `customer_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this Customer Status',
  `name` varchar(50) NOT NULL COMMENT 'The status name',
  `description` varchar(1000) NOT NULL COMMENT 'A description of the criteria required to satisfy this status',
  `default_action_description` varchar(1000) NOT NULL COMMENT 'Default description of what the user should do with customer',
  `default_overdue_action_description` varchar(1000) NOT NULL COMMENT 'Default description of what the user should do with the customer when the customer is overdue',
  `precedence` int(10) NOT NULL COMMENT 'Order in which the statuses are tested. Customer status with precedence 1 will be tested before customer status with precidence 2.  Customer is assigned the first status which it satisfies',
  `test` varchar(255) NOT NULL COMMENT 'Identifies the ''test'' which is used to check that the criteria has been met',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines the Customer Statuses and how they are tested';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_status`
--

LOCK TABLES `customer_status` WRITE;
/*!40000 ALTER TABLE `customer_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_status_action`
--

DROP TABLE IF EXISTS `customer_status_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_status_action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id',
  `customer_status_id` bigint(20) unsigned NOT NULL COMMENT 'FK customer_status table',
  `user_role_id` bigint(20) unsigned NOT NULL COMMENT 'FK user_role table',
  `description` varchar(1000) NOT NULL COMMENT 'description of the required action that the employee must take when of this role/customer_status',
  `overdue_description` varchar(1000) NOT NULL COMMENT 'description of the required action that the employee must take when of this role/customer_status and the customer has an overdue amount',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Action to be taken, for a given customer status';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_status_action`
--

LOCK TABLES `customer_status_action` WRITE;
/*!40000 ALTER TABLE `customer_status_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_status_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_status_history`
--

DROP TABLE IF EXISTS `customer_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id',
  `account_id` bigint(20) unsigned NOT NULL COMMENT 'FK Account table',
  `invoice_run_id` bigint(20) unsigned NOT NULL COMMENT 'FK InvoiceRun table',
  `last_updated` datetime NOT NULL COMMENT 'time at which the status was last calculated',
  `customer_status_id` bigint(20) unsigned NOT NULL COMMENT 'FK customer_status table',
  PRIMARY KEY (`id`),
  KEY `account_id_invoice_run_id` (`account_id`,`invoice_run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Customer status for a given invoice run/account';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_status_history`
--

LOCK TABLES `customer_status_history` WRITE;
/*!40000 ALTER TABLE `customer_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_report_employee`
--

DROP TABLE IF EXISTS `data_report_employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_report_employee` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `data_report_id` bigint(20) unsigned NOT NULL COMMENT '(FK) DataReport',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee',
  PRIMARY KEY (`id`),
  KEY `fk_data_report_employee_data_report_id` (`data_report_id`),
  KEY `fk_data_report_employee_employee_id` (`employee_id`),
  CONSTRAINT `fk_data_report_employee_data_report_id` FOREIGN KEY (`data_report_id`) REFERENCES `DataReport` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_data_report_employee_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines a relationship between a data_report and an employee';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_report_employee`
--

LOCK TABLES `data_report_employee` WRITE;
/*!40000 ALTER TABLE `data_report_employee` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_report_employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_report_operation_profile`
--

DROP TABLE IF EXISTS `data_report_operation_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_report_operation_profile` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `data_report_id` bigint(20) unsigned NOT NULL COMMENT '(FK) DataReport',
  `operation_profile_id` int(10) unsigned NOT NULL COMMENT '(FK) operation_profile',
  PRIMARY KEY (`id`),
  KEY `fk_data_report_operation_profile_data_report_id` (`data_report_id`),
  KEY `fk_data_report_operation_profile_operation_profile_id` (`operation_profile_id`),
  CONSTRAINT `fk_data_report_operation_profile_data_report_id` FOREIGN KEY (`data_report_id`) REFERENCES `DataReport` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_data_report_operation_profile_operation_profile_id` FOREIGN KEY (`operation_profile_id`) REFERENCES `operation_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines a relationship between a data_report and an operatio';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_report_operation_profile`
--

LOCK TABLES `data_report_operation_profile` WRITE;
/*!40000 ALTER TABLE `data_report_operation_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_report_operation_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_report_status`
--

DROP TABLE IF EXISTS `data_report_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_report_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Status name',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Status description',
  `system_name` varchar(256) NOT NULL COMMENT 'System name',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant alias',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Defines a data report status';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_report_status`
--

LOCK TABLES `data_report_status` WRITE;
/*!40000 ALTER TABLE `data_report_status` DISABLE KEYS */;
INSERT INTO `data_report_status` VALUES (1,'Draft','Incomplete Data Report','DRAFT','DATA_REPORT_STATUS_DRAFT'),(2,'Active','Active Data Report','ACTIVE','DATA_REPORT_STATUS_ACTIVE'),(3,'Inactive','Inactive Data Report','INACTIVE','DATA_REPORT_STATUS_INACTIVE');
/*!40000 ALTER TABLE `data_report_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_type`
--

DROP TABLE IF EXISTS `data_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Data Type',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Data Type',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Data Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Data Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_type`
--

LOCK TABLES `data_type` WRITE;
/*!40000 ALTER TABLE `data_type` DISABLE KEYS */;
INSERT INTO `data_type` VALUES (1,'String','String','DATA_TYPE_STRING'),(2,'Integer','Integer','DATA_TYPE_INTEGER'),(3,'Float','Float','DATA_TYPE_FLOAT'),(4,'Boolean','Boolean','DATA_TYPE_BOOLEAN'),(5,'Serialised','Serialised','DATA_TYPE_SERIALISED'),(6,'Array','String','DATA_TYPE_ARRAY');
/*!40000 ALTER TABLE `data_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_version`
--

DROP TABLE IF EXISTS `database_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_version` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` bigint(20) unsigned NOT NULL,
  `rolled_out_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_version`
--

LOCK TABLES `database_version` WRITE;
/*!40000 ALTER TABLE `database_version` DISABLE KEYS */;
INSERT INTO `database_version` VALUES (1,0,'1970-01-01 00:00:00'),(2,3,'2008-06-13 07:55:49'),(3,7,'2008-06-20 10:46:11'),(4,11,'2008-06-26 17:50:39'),(5,14,'2008-07-15 07:50:43'),(6,15,'2008-07-15 07:54:35'),(7,16,'2008-07-16 07:46:10'),(8,21,'2008-07-31 18:18:01'),(9,27,'2008-08-14 17:58:20'),(10,28,'2008-08-15 14:49:57'),(12,46,'2008-08-31 23:16:59'),(13,52,'2008-09-09 18:05:18'),(14,60,'2008-10-02 00:29:25'),(15,65,'2008-10-02 00:31:07'),(16,66,'2008-10-02 00:31:07'),(17,67,'2008-10-02 00:31:08'),(18,68,'2008-10-02 00:31:08'),(19,69,'2008-10-02 00:31:08'),(20,70,'2008-10-02 00:31:09'),(21,71,'2008-10-02 00:31:10'),(22,72,'2008-10-02 00:31:33'),(23,74,'2008-10-02 00:31:34'),(24,75,'2008-10-02 00:31:34'),(25,76,'2008-10-07 18:16:16'),(26,77,'2008-10-13 18:19:52'),(27,78,'2008-10-13 18:19:53'),(28,79,'2008-10-13 18:20:00'),(29,80,'2008-10-13 18:20:00'),(30,81,'2008-10-20 18:04:22'),(31,82,'2008-10-20 18:04:37'),(32,83,'2008-10-20 18:04:42'),(33,84,'2008-10-20 18:04:50'),(34,85,'2008-11-16 14:55:06'),(35,86,'2008-11-16 14:55:33'),(36,87,'2008-11-16 15:03:48'),(37,88,'2008-11-16 15:03:50'),(38,89,'2008-11-16 15:04:55'),(39,90,'2008-11-16 15:04:59'),(40,91,'2008-11-16 15:05:50'),(41,92,'2008-11-16 15:13:44'),(42,93,'2008-11-16 15:13:49'),(43,94,'2008-11-16 15:13:50'),(44,95,'2008-11-16 15:13:51'),(45,96,'2008-11-18 01:19:01'),(46,97,'2008-11-21 00:17:52'),(47,98,'2008-11-27 23:44:41'),(48,99,'2008-11-27 23:44:42'),(49,100,'2008-11-27 23:44:42'),(50,101,'2008-11-27 23:44:43'),(51,102,'2008-11-27 23:44:44'),(52,103,'2008-11-27 23:44:45'),(53,104,'2008-11-27 23:44:45'),(54,105,'2008-11-27 23:44:46'),(55,106,'2008-12-12 15:47:10'),(56,107,'2008-12-12 15:47:11'),(57,108,'2008-12-12 15:47:11'),(58,109,'2008-12-12 15:47:12'),(59,110,'2008-12-12 15:49:28'),(60,111,'2008-12-12 15:49:29'),(61,112,'2008-12-12 15:49:30'),(62,113,'2008-12-23 08:31:26'),(63,114,'2008-12-23 08:31:27'),(64,115,'2008-12-23 08:31:28'),(65,116,'2008-12-23 08:31:28'),(66,117,'2008-12-23 08:31:29'),(67,118,'2008-12-23 08:31:29'),(68,119,'2008-12-23 08:31:31'),(69,120,'2008-12-23 08:31:31'),(70,121,'2008-12-23 08:31:32'),(71,122,'2008-12-23 08:31:33'),(72,123,'2008-12-24 13:02:10'),(73,124,'2009-02-06 15:48:45'),(74,125,'2009-02-06 15:48:46'),(75,126,'2009-02-06 15:48:46'),(76,127,'2009-02-06 15:48:47'),(77,128,'2009-02-06 15:48:47'),(78,129,'2009-02-06 15:48:48'),(79,130,'2009-02-06 15:49:26'),(80,131,'2009-02-06 15:49:26'),(81,132,'2009-02-06 15:49:27'),(82,133,'2009-02-06 15:49:28'),(83,134,'2009-02-11 17:13:25'),(84,135,'2009-02-11 17:13:26'),(85,136,'2009-02-11 17:13:27'),(86,137,'2009-02-11 17:13:28'),(87,138,'2009-02-17 14:37:02'),(88,139,'2009-02-17 14:37:35'),(89,140,'2009-02-26 18:05:25'),(90,141,'2009-02-26 18:05:25'),(91,142,'2009-02-26 18:05:27'),(92,143,'2009-02-26 18:05:31'),(93,144,'2009-02-26 18:05:32'),(94,145,'2009-02-26 18:05:33'),(95,146,'2009-03-04 18:07:04'),(96,147,'2009-03-04 18:07:04'),(97,148,'2009-03-05 18:14:00'),(98,149,'2009-03-05 18:14:01'),(99,150,'2009-03-20 17:58:10'),(100,151,'2009-03-20 17:58:11'),(101,152,'2009-03-20 17:58:12'),(102,153,'2009-03-20 17:58:14'),(103,154,'2009-03-20 17:58:15'),(104,155,'2009-03-20 17:58:15'),(105,156,'2009-03-20 17:58:16'),(106,157,'2009-03-20 17:58:17'),(107,158,'2009-03-20 17:58:20'),(108,159,'2009-03-20 17:58:21'),(109,160,'2009-03-20 17:58:23'),(110,161,'2009-03-20 17:58:24'),(111,162,'2009-04-23 18:28:41'),(112,163,'2009-04-23 18:28:42'),(113,164,'2009-04-23 18:28:43'),(114,165,'2009-04-23 18:28:44'),(115,166,'2009-04-23 18:36:13'),(116,167,'2009-04-23 18:36:17'),(117,168,'2009-04-23 18:36:17'),(118,169,'2009-04-23 18:36:19'),(119,170,'2009-04-23 18:37:08'),(120,171,'2009-04-23 18:37:09'),(121,172,'2009-04-23 18:37:16'),(122,173,'2009-04-23 18:37:17'),(123,174,'2009-06-16 21:13:38'),(124,175,'2009-06-16 21:13:44'),(125,176,'2009-06-16 21:13:45'),(126,177,'2009-06-16 21:13:52'),(127,178,'2009-06-16 21:13:53'),(128,179,'2009-07-02 20:43:41'),(129,180,'2009-07-02 20:43:48'),(130,181,'2009-07-02 20:43:50'),(131,182,'2009-07-02 20:43:51'),(132,183,'2009-07-07 20:05:24'),(133,184,'2009-07-09 18:17:28'),(134,185,'2009-10-02 18:10:56'),(135,186,'2009-10-02 18:10:58'),(136,187,'2009-10-02 18:11:00'),(137,188,'2009-10-02 18:11:14'),(138,189,'2009-10-02 18:11:14'),(139,190,'2009-10-02 18:11:15'),(140,191,'2009-10-02 18:11:16'),(141,192,'2009-10-02 18:14:40'),(142,193,'2009-10-02 18:21:05'),(143,194,'2009-10-02 18:21:09'),(144,195,'2009-10-28 18:40:09'),(145,196,'2009-10-28 19:12:18'),(146,197,'2009-11-02 17:37:29'),(147,198,'2009-11-27 17:34:08'),(148,199,'2009-11-27 17:34:09'),(149,200,'2009-11-27 17:34:10'),(150,201,'2009-11-27 17:34:13'),(151,202,'2009-12-17 18:07:13'),(152,203,'2010-02-12 12:38:54'),(153,204,'2010-04-08 18:24:45'),(154,205,'2010-05-02 17:09:22'),(155,206,'2010-05-02 17:09:22'),(156,207,'2010-05-02 17:09:37'),(157,208,'2010-05-02 17:09:37'),(158,209,'2010-05-02 17:09:38'),(159,210,'2010-05-02 17:09:39'),(160,211,'2010-05-11 08:01:33'),(161,212,'2010-05-11 08:01:34'),(162,213,'2010-05-11 08:06:25'),(163,214,'2010-05-13 16:14:29'),(166,215,'2010-05-31 06:54:58'),(167,216,'2010-05-31 06:54:58'),(168,217,'2010-05-31 06:54:59'),(169,218,'2010-07-14 06:55:04'),(170,219,'2010-07-14 06:55:05'),(171,220,'2010-08-25 07:38:22'),(172,221,'2010-08-25 07:38:22'),(173,222,'2010-08-25 07:52:51'),(174,223,'2010-08-25 07:52:51'),(175,224,'2010-09-08 14:31:06'),(176,225,'2010-10-24 21:26:36'),(177,226,'2010-10-24 21:26:37'),(178,227,'2010-10-24 21:26:38'),(179,228,'2010-10-24 21:26:39'),(180,229,'2010-10-24 21:26:39'),(181,230,'2010-10-24 21:26:40'),(182,231,'2011-03-19 12:15:17'),(183,232,'2011-03-19 12:15:39'),(184,233,'2011-03-19 12:15:40'),(185,234,'2011-03-19 12:16:26'),(186,235,'2011-03-19 12:16:27'),(187,236,'2011-03-19 12:16:28'),(188,237,'2011-03-19 12:16:29'),(189,238,'2011-03-19 12:16:30'),(190,239,'2011-03-19 12:18:52'),(191,240,'2011-03-19 17:51:31'),(192,241,'2011-03-25 08:36:48'),(193,242,'2011-03-25 08:36:50'),(194,243,'2011-03-31 14:53:51'),(195,244,'2011-04-15 14:30:55'),(196,245,'2011-04-15 14:30:57'),(197,246,'2011-04-19 11:12:02'),(198,247,'2011-07-17 16:47:59'),(199,248,'2011-08-09 07:42:41'),(200,249,'2011-09-11 20:47:51'),(201,250,'2011-09-22 07:35:17'),(202,251,'2011-11-14 13:54:03'),(203,252,'2011-11-22 12:45:35'),(205,253,'2012-02-28 18:13:06'),(216,254,'2012-04-12 17:23:02'),(217,255,'2012-04-13 13:09:49');
/*!40000 ALTER TABLE `database_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `day`
--

DROP TABLE IF EXISTS `day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `day` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of the day',
  `description` varchar(512) NOT NULL COMMENT 'Description of the day',
  `const_name` varchar(255) NOT NULL COMMENT 'Constant name for this day',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COMMENT='Days of the week: ISO-8601 numeric representation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `day`
--

LOCK TABLES `day` WRITE;
/*!40000 ALTER TABLE `day` DISABLE KEYS */;
INSERT INTO `day` VALUES (1,'Mon','Monday','DAY_MONDAY'),(2,'Tues','Tuesday','DAY_TUESDAY'),(3,'Wed','Wednesday','DAY_WEDNESDAY'),(4,'Thu','Thursday','DAY_THURSDAY'),(5,'Fri','Friday','DAY_FRIDAY'),(6,'Sat','Saturday','DAY_SATURDAY'),(7,'Sun','Sunday','DAY_SUNDAY');
/*!40000 ALTER TABLE `day` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer`
--

DROP TABLE IF EXISTS `dealer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dealer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `up_line_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into the dealer table, defining the direct ''up line'' manager of the dealer',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `can_verify` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 = dealer can verify sales other than those made by dealers under their management; 0 = dealer can only verify sales made by dealers under their management',
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `title_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into the contact_title table, defining the title/salutation used by the dealer',
  `business_name` varchar(255) DEFAULT NULL,
  `trading_name` varchar(255) DEFAULT NULL,
  `abn` char(11) DEFAULT NULL,
  `abn_registered` tinyint(1) unsigned DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `suburb` varchar(255) DEFAULT NULL,
  `state_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into the state table, defining the state in which the dealer is primarily located',
  `country_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into the country table, defining the country in which the dealer is primarily located',
  `postcode` varchar(255) DEFAULT NULL,
  `postal_address_line_1` varchar(255) DEFAULT NULL,
  `postal_address_line_2` varchar(255) DEFAULT NULL,
  `postal_suburb` varchar(255) DEFAULT NULL,
  `postal_state_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into the state table, defining the state used by the postal address of the dealer',
  `postal_country_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into the country table, defining the country used by the postal address of the dealer',
  `postal_postcode` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `commission_scale` bigint(20) unsigned DEFAULT NULL,
  `royalty_scale` bigint(20) unsigned DEFAULT NULL,
  `bank_account_bsb` char(6) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `gst_registered` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 = YES, 0 = NO',
  `termination_date` date DEFAULT NULL,
  `dealer_status_id` bigint(20) unsigned NOT NULL COMMENT 'FK into the dealer_status table, defininng the current status of the dealer',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time at which the dealer record was created',
  `clawback_period` int(11) NOT NULL DEFAULT '0' COMMENT 'clawback period for sales (in hours)',
  `employee_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into Employee table',
  `carrier_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Sale Call Centre Carrier that this Dealer belongs to',
  `sync_sale_constraints` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'If TRUE AND up_line_id IS NOT NULL, then sale constraints should be kept in sync with those of up_line_id',
  PRIMARY KEY (`id`),
  KEY `fk_dealer_title_id_contact_title_id` (`title_id`),
  KEY `fk_dealer_state_id` (`state_id`),
  KEY `fk_dealer_country_id` (`country_id`),
  KEY `fk_dealer_postal_state_id_state_id` (`postal_state_id`),
  KEY `fk_dealer_postal_country_id_country_id` (`postal_country_id`),
  KEY `fk_dealer_dealer_status_id` (`dealer_status_id`),
  KEY `fk_dealer_up_line_id_dealer_id` (`up_line_id`),
  KEY `fk_dealer_employee_id_Employee_Id` (`employee_id`),
  CONSTRAINT `fk_dealer_country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_dealer_status_id` FOREIGN KEY (`dealer_status_id`) REFERENCES `dealer_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_employee_id_Employee_Id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_postal_country_id_country_id` FOREIGN KEY (`postal_country_id`) REFERENCES `country` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_postal_state_id_state_id` FOREIGN KEY (`postal_state_id`) REFERENCES `state` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_state_id` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_title_id_contact_title_id` FOREIGN KEY (`title_id`) REFERENCES `contact_title` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_up_line_id_dealer_id` FOREIGN KEY (`up_line_id`) REFERENCES `dealer` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines dealers';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer`
--

LOCK TABLES `dealer` WRITE;
/*!40000 ALTER TABLE `dealer` DISABLE KEYS */;
/*!40000 ALTER TABLE `dealer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer_config`
--

DROP TABLE IF EXISTS `dealer_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dealer_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
  `default_employee_manager_dealer_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into dealer table; default manager for employee dealers',
  PRIMARY KEY (`id`),
  KEY `fk_dealer_config_default_employee_manager_dealer_id_dealer_id` (`default_employee_manager_dealer_id`),
  CONSTRAINT `fk_dealer_config_default_employee_manager_dealer_id_dealer_id` FOREIGN KEY (`default_employee_manager_dealer_id`) REFERENCES `dealer` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='dealer configuration';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer_config`
--

LOCK TABLES `dealer_config` WRITE;
/*!40000 ALTER TABLE `dealer_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `dealer_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer_customer_group`
--

DROP TABLE IF EXISTS `dealer_customer_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dealer_customer_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
  `dealer_id` bigint(20) unsigned NOT NULL COMMENT 'FK into dealer table',
  `customer_group_id` bigint(20) NOT NULL COMMENT 'FK into CustomerGroup table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_dealer_customer_group_dealer_id_customer_group_id` (`dealer_id`,`customer_group_id`),
  KEY `fk_dealer_customer_group_customer_group_id` (`customer_group_id`),
  CONSTRAINT `fk_dealer_customer_group_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_customer_group_dealer_id` FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='dealer - customer group relationships';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer_customer_group`
--

LOCK TABLES `dealer_customer_group` WRITE;
/*!40000 ALTER TABLE `dealer_customer_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `dealer_customer_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer_rate_plan`
--

DROP TABLE IF EXISTS `dealer_rate_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dealer_rate_plan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
  `dealer_id` bigint(20) unsigned NOT NULL COMMENT 'FK into dealer table',
  `rate_plan_id` bigint(20) unsigned NOT NULL COMMENT 'FK into RatePlan table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_dealer_rate_plan_dealer_id_rate_plan_id` (`dealer_id`,`rate_plan_id`),
  KEY `fk_dealer_rate_plan_rate_plan_id` (`rate_plan_id`),
  CONSTRAINT `fk_dealer_rate_plan_dealer_id` FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_rate_plan_rate_plan_id` FOREIGN KEY (`rate_plan_id`) REFERENCES `RatePlan` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='dealer - rate plan relationships';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer_rate_plan`
--

LOCK TABLES `dealer_rate_plan` WRITE;
/*!40000 ALTER TABLE `dealer_rate_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `dealer_rate_plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer_sale_type`
--

DROP TABLE IF EXISTS `dealer_sale_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dealer_sale_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
  `dealer_id` bigint(20) unsigned NOT NULL COMMENT 'FK into dealer table',
  `sale_type_id` bigint(20) unsigned NOT NULL COMMENT 'FK into sale_type table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_dealer_sale_type_dealer_id_sale_type_id` (`dealer_id`,`sale_type_id`),
  KEY `fk_dealer_sale_type_sale_type_id` (`sale_type_id`),
  CONSTRAINT `fk_dealer_sale_type_dealer_id` FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dealer_sale_type_sale_type_id` FOREIGN KEY (`sale_type_id`) REFERENCES `sale_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='dealer - sale type relationships';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer_sale_type`
--

LOCK TABLES `dealer_sale_type` WRITE;
/*!40000 ALTER TABLE `dealer_sale_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `dealer_sale_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer_status`
--

DROP TABLE IF EXISTS `dealer_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dealer_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this record',
  `name` varchar(255) NOT NULL COMMENT 'Unique name for the dealer status',
  `description` varchar(255) NOT NULL COMMENT 'Description of the dealer status',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_dealer_status_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines dealer statuses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer_status`
--

LOCK TABLES `dealer_status` WRITE;
/*!40000 ALTER TABLE `dealer_status` DISABLE KEYS */;
INSERT INTO `dealer_status` VALUES (1,'Active','Active'),(2,'Inactive','Inactive');
/*!40000 ALTER TABLE `dealer_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `default_rate_plan`
--

DROP TABLE IF EXISTS `default_rate_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `default_rate_plan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_group` bigint(20) unsigned NOT NULL,
  `service_type` int(10) unsigned NOT NULL,
  `rate_plan` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `default_rate_plan`
--

LOCK TABLES `default_rate_plan` WRITE;
/*!40000 ALTER TABLE `default_rate_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `default_rate_plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_method`
--

DROP TABLE IF EXISTS `delivery_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_method` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Delivery Method',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Delivery Method',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for this Delivery Method',
  `account_setting` tinyint(1) NOT NULL COMMENT '1: Can be used as an Account setting; 0: System-only setting',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_method`
--

LOCK TABLES `delivery_method` WRITE;
/*!40000 ALTER TABLE `delivery_method` DISABLE KEYS */;
INSERT INTO `delivery_method` VALUES (0,'Post','Post','DELIVERY_METHOD_POST',1),(1,'Email','Email','DELIVERY_METHOD_EMAIL',1),(2,'Withheld','Do Not Send','DELIVERY_METHOD_DO_NOT_SEND',0),(3,'Email Sent','Email (Sent)','DELIVERY_METHOD_EMAIL_SENT',0);
/*!40000 ALTER TABLE `delivery_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destination_context`
--

DROP TABLE IF EXISTS `destination_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destination_context` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Destination Context',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Destination Context',
  `fallback_destination_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Destination Id to use when no valid Destination can be found',
  PRIMARY KEY (`id`),
  KEY `fk_destination_context_fallback_destination_id` (`fallback_destination_id`),
  CONSTRAINT `fk_destination_context_fallback_destination_id` FOREIGN KEY (`fallback_destination_id`) REFERENCES `Destination` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destination_context`
--

LOCK TABLES `destination_context` WRITE;
/*!40000 ALTER TABLE `destination_context` DISABLE KEYS */;
/*!40000 ALTER TABLE `destination_context` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direct_debit`
--

DROP TABLE IF EXISTS `direct_debit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direct_debit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account this Direct Debit belongs to',
  `direct_debit_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Direct Debit Type (eg. Credit Card, Bank Account)',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who created this Direct Debit',
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Creation Timestamp',
  `dealer_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Dealer who obtained the Direct Debit details',
  `modified_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who last modified this Direct Debit',
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last Modification Timestamp',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Active/Inactive Status of this Direct Debit',
  PRIMARY KEY (`id`),
  KEY `fk_direct_debit_account_id` (`account_id`),
  KEY `fk_direct_debit_direct_debit_type_id` (`direct_debit_type_id`),
  KEY `fk_direct_debit_created_employee_id` (`created_employee_id`),
  KEY `fk_direct_debit_dealer_id` (`dealer_id`),
  KEY `fk_direct_debit_modified_employee_id` (`modified_employee_id`),
  KEY `fk_direct_debit_status_id` (`status_id`),
  CONSTRAINT `fk_direct_debit_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_direct_debit_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_direct_debit_dealer_id` FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_direct_debit_direct_debit_type_id` FOREIGN KEY (`direct_debit_type_id`) REFERENCES `direct_debit_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_direct_debit_modified_employee_id` FOREIGN KEY (`modified_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_direct_debit_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direct_debit`
--

LOCK TABLES `direct_debit` WRITE;
/*!40000 ALTER TABLE `direct_debit` DISABLE KEYS */;
/*!40000 ALTER TABLE `direct_debit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direct_debit_bank_account`
--

DROP TABLE IF EXISTS `direct_debit_bank_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direct_debit_bank_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `direct_debit_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Direct Debit record that this details',
  `bank_name` varchar(512) NOT NULL COMMENT 'Name of the Bank which holds the Account',
  `bank_bsb` char(6) NOT NULL COMMENT 'Bank/State/Branch Number',
  `account_number` varchar(24) NOT NULL COMMENT 'Bank Account Number',
  `account_name` varchar(512) NOT NULL COMMENT 'Name on the Bank Account',
  PRIMARY KEY (`id`),
  KEY `fk_direct_debit_bank_account_direct_debit_id` (`direct_debit_id`),
  CONSTRAINT `fk_direct_debit_bank_account_direct_debit_id` FOREIGN KEY (`direct_debit_id`) REFERENCES `direct_debit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direct_debit_bank_account`
--

LOCK TABLES `direct_debit_bank_account` WRITE;
/*!40000 ALTER TABLE `direct_debit_bank_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `direct_debit_bank_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direct_debit_credit_card`
--

DROP TABLE IF EXISTS `direct_debit_credit_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direct_debit_credit_card` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `direct_debit_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Direct Debit record that this details',
  `credit_card_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Credit Card Type',
  `card_name` varchar(512) NOT NULL COMMENT 'Customer Name on the Credit Card',
  `card_number` varchar(24) NOT NULL COMMENT 'Credit Card Number',
  `expiry_month` tinyint(2) unsigned NOT NULL COMMENT 'Month in which the Credit Card expires',
  `expiry_year` smallint(4) unsigned NOT NULL COMMENT 'Year in which the Credit Card expires',
  `cvv` varchar(8) NOT NULL COMMENT 'Card Verification Value for the Credit Card',
  PRIMARY KEY (`id`),
  KEY `fk_direct_debit_credit_card_direct_debit_id` (`direct_debit_id`),
  KEY `fk_direct_debit_credit_card_credit_card_type_id` (`credit_card_type_id`),
  CONSTRAINT `fk_direct_debit_credit_card_credit_card_type_id` FOREIGN KEY (`credit_card_type_id`) REFERENCES `credit_card_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_direct_debit_credit_card_direct_debit_id` FOREIGN KEY (`direct_debit_id`) REFERENCES `direct_debit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direct_debit_credit_card`
--

LOCK TABLES `direct_debit_credit_card` WRITE;
/*!40000 ALTER TABLE `direct_debit_credit_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `direct_debit_credit_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direct_debit_type`
--

DROP TABLE IF EXISTS `direct_debit_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direct_debit_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Direct Debit Type',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Direct Debit Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias of the Direct Debit Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direct_debit_type`
--

LOCK TABLES `direct_debit_type` WRITE;
/*!40000 ALTER TABLE `direct_debit_type` DISABLE KEYS */;
INSERT INTO `direct_debit_type` VALUES (1,'Credit Card','Credit Card','DIRECT_DEBIT_TYPE_CREDIT_CARD'),(2,'Bank Account','Bank Account','DIRECT_DEBIT_TYPE_BANK_ACCOUNT');
/*!40000 ALTER TABLE `direct_debit_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount`
--

DROP TABLE IF EXISTS `discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name for the Discount which will appear on the Invoice',
  `description` varchar(512) DEFAULT NULL COMMENT 'Description for the Discount',
  `charge_limit` decimal(13,4) DEFAULT NULL COMMENT 'Dollar limit of usage to discount',
  `unit_limit` int(11) DEFAULT NULL COMMENT 'Unit limit of usage to discount (takes priority over charge_limit)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount`
--

LOCK TABLES `discount` WRITE;
/*!40000 ALTER TABLE `discount` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_record_type`
--

DROP TABLE IF EXISTS `discount_record_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount_record_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `discount_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Discount',
  `record_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Record Type',
  PRIMARY KEY (`id`),
  KEY `fk_discount_record_type_discount_id` (`discount_id`),
  KEY `fk_discount_record_type_record_type_id` (`record_type_id`),
  CONSTRAINT `fk_discount_record_type_discount_id` FOREIGN KEY (`discount_id`) REFERENCES `discount` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_discount_record_type_record_type_id` FOREIGN KEY (`record_type_id`) REFERENCES `RecordType` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_record_type`
--

LOCK TABLES `discount_record_type` WRITE;
/*!40000 ALTER TABLE `discount_record_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_record_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document`
--

DROP TABLE IF EXISTS `document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `document_nature_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Document Nature',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date the Document was created',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who created the Document',
  `is_system_document` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: Standard Document; 1: System Document (hidden from general users)',
  PRIMARY KEY (`id`),
  KEY `fk_document_document_nature_id` (`document_nature_id`),
  KEY `fk_document_employee_id` (`employee_id`),
  CONSTRAINT `fk_document_document_nature_id` FOREIGN KEY (`document_nature_id`) REFERENCES `document_nature` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_document_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document`
--

LOCK TABLES `document` WRITE;
/*!40000 ALTER TABLE `document` DISABLE KEYS */;
/*!40000 ALTER TABLE `document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_content`
--

DROP TABLE IF EXISTS `document_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_content` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `document_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Document this belongs to',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Document',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description of the Document',
  `file_type_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The Document''s File Type',
  `content` mediumblob COMMENT 'Binary content of the Document (compressed with BZIP2)',
  `parent_document_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The Document this is a child of',
  `changed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date the Document was changed',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who modified the Document',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Active Status of the Document',
  `constant_group` varchar(255) DEFAULT NULL COMMENT 'Constant Group to dereference the name field against for a ''friendly'' name',
  `uncompressed_file_size` int(10) unsigned DEFAULT NULL COMMENT 'Size in Bytes of the uncompressed Content',
  PRIMARY KEY (`id`),
  KEY `fk_document_content_document_id` (`document_id`),
  KEY `fk_document_content_file_type_id` (`file_type_id`),
  KEY `fk_document_content_employee_id` (`employee_id`),
  KEY `fk_document_content_status_id` (`status_id`),
  KEY `fk_document_content_parent_document_id` (`parent_document_id`),
  CONSTRAINT `fk_document_content_document_id` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_content_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_document_content_file_type_id` FOREIGN KEY (`file_type_id`) REFERENCES `file_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_document_content_parent_document_id` FOREIGN KEY (`parent_document_id`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_content_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_content`
--

LOCK TABLES `document_content` WRITE;
/*!40000 ALTER TABLE `document_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_nature`
--

DROP TABLE IF EXISTS `document_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_nature` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Document Nature',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Document Nature',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name of the Document Nature',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_nature`
--

LOCK TABLES `document_nature` WRITE;
/*!40000 ALTER TABLE `document_nature` DISABLE KEYS */;
INSERT INTO `document_nature` VALUES (1,'Folder','Folder','DOCUMENT_NATURE_FOLDER'),(2,'File','File','DOCUMENT_NATURE_FILE');
/*!40000 ALTER TABLE `document_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_resource_type_file_type`
--

DROP TABLE IF EXISTS `document_resource_type_file_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_resource_type_file_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `document_resource_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Document Resource Type',
  `file_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) File Type',
  PRIMARY KEY (`id`),
  KEY `fk_document_resource_type_file_type_document_resource_type_id` (`document_resource_type_id`),
  KEY `fk_document_resource_type_file_type_file_type_id` (`file_type_id`),
  CONSTRAINT `fk_document_resource_type_file_type_document_resource_type_id` FOREIGN KEY (`document_resource_type_id`) REFERENCES `DocumentResourceType` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_resource_type_file_type_file_type_id` FOREIGN KEY (`file_type_id`) REFERENCES `FileType` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_resource_type_file_type`
--

LOCK TABLES `document_resource_type_file_type` WRITE;
/*!40000 ALTER TABLE `document_resource_type_file_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_resource_type_file_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `recipients` varchar(16384) NOT NULL COMMENT 'Recipients of the email',
  `sender` varchar(512) NOT NULL COMMENT 'Sender of the email',
  `subject` varchar(1024) NOT NULL COMMENT 'Subject of the email',
  `text` varchar(16384) NOT NULL COMMENT 'Text body of the email',
  `html` varchar(16384) DEFAULT NULL COMMENT 'HTML body of the email',
  `email_queue_id` bigint(20) unsigned NOT NULL COMMENT '(FK) email_queue. Queue that the email belongs to',
  `email_status_id` int(11) NOT NULL COMMENT '(FK) email_status. Current status',
  `created_datetime` datetime NOT NULL COMMENT 'Timestamp for record creation',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee. Employee who created the record',
  PRIMARY KEY (`id`),
  KEY `fk_email_created_employee_id` (`created_employee_id`),
  KEY `fk_email_email_queue_id` (`email_queue_id`),
  KEY `fk_email_email_status_id` (`email_status_id`),
  CONSTRAINT `fk_email_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_email_queue_id` FOREIGN KEY (`email_queue_id`) REFERENCES `email_queue` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_email_status_id` FOREIGN KEY (`email_status_id`) REFERENCES `email_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='An email that belongs to a queue';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email`
--

LOCK TABLES `email` WRITE;
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
/*!40000 ALTER TABLE `email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_account`
--

DROP TABLE IF EXISTS `email_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `email_id` bigint(20) unsigned NOT NULL COMMENT '(FK) email, the email',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account, the account that the email is linked to',
  PRIMARY KEY (`id`),
  KEY `fk_email_account_email_id` (`email_id`),
  KEY `fk_email_account_account_id` (`account_id`),
  CONSTRAINT `fk_email_account_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_account_email_id` FOREIGN KEY (`email_id`) REFERENCES `email` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='A relationship between an email and an Account.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_account`
--

LOCK TABLES `email_account` WRITE;
/*!40000 ALTER TABLE `email_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_address_usage`
--

DROP TABLE IF EXISTS `email_address_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_address_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the email address usage',
  `name` varchar(50) NOT NULL COMMENT 'Name of the email address usage (to, cc, bcc or from)',
  `description` varchar(255) NOT NULL COMMENT 'Description of the email address usage',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Email address usage';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_address_usage`
--

LOCK TABLES `email_address_usage` WRITE;
/*!40000 ALTER TABLE `email_address_usage` DISABLE KEYS */;
INSERT INTO `email_address_usage` VALUES (1,'to','Primary recipient of email','EMAIL_ADDRESS_USAGE_TO'),(2,'cc','Secondary recipient of email','EMAIL_ADDRESS_USAGE_CC'),(3,'bcc','Undisclosed recipient of email','EMAIL_ADDRESS_USAGE_BCC'),(4,'from','Sender of email','EMAIL_ADDRESS_USAGE_FROM');
/*!40000 ALTER TABLE `email_address_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_attachment`
--

DROP TABLE IF EXISTS `email_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_attachment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `content` mediumblob NOT NULL COMMENT 'Content of the attachment file',
  `mime_type` varchar(512) NOT NULL COMMENT 'Mime type of the attachment content',
  `disposition` varchar(64) DEFAULT NULL COMMENT 'Disposition of the attachment: attachment or inline',
  `encoding` varchar(128) DEFAULT NULL COMMENT 'Encoding of the attachment: 7bit, 8bit, quoted-printable or base64',
  `filename` varchar(1024) NOT NULL COMMENT 'Filename of the attachment',
  `email_id` bigint(20) unsigned NOT NULL COMMENT '(FK) email. The Email that the attachment is part of',
  `created_datetime` datetime NOT NULL COMMENT 'Timestamp for record creation',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee. Employee who created the record',
  PRIMARY KEY (`id`),
  KEY `fk_email_attachment_email_id` (`email_id`),
  KEY `fk_email_attachment_created_employee_id` (`created_employee_id`),
  CONSTRAINT `fk_email_attachment_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_attachment_email_id` FOREIGN KEY (`email_id`) REFERENCES `email` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='An attachment that is part of an email';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_attachment`
--

LOCK TABLES `email_attachment` WRITE;
/*!40000 ALTER TABLE `email_attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_notification`
--

DROP TABLE IF EXISTS `email_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_notification` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the email notification',
  `name` varchar(50) NOT NULL COMMENT 'Name of the email notification',
  `description` varchar(255) NOT NULL COMMENT 'Description of the email notification',
  `system_name` varchar(255) DEFAULT NULL,
  `allow_customer_group_emails` tinyint(1) unsigned NOT NULL COMMENT 'Whether or not to allow emails to be sent to customer group contacts',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='Emails generated by the system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_notification`
--

LOCK TABLES `email_notification` WRITE;
/*!40000 ALTER TABLE `email_notification` DISABLE KEYS */;
INSERT INTO `email_notification` VALUES (1,'Late Notice List','Email listing accounts that will be sent late notices','LATE_NOTICE_LIST',0),(2,'Late Notice Report','Email listing accounts that have been sent late notices','LATE_NOTICE_REPORT',0),(3,'Late Notice','Email to customer with late notice attachment','LATE_NOTICE',1),(4,'Late Fee List','Email listing accounts that will have late fees applied','LATE_FEE_LIST',0),(5,'Late Fee Report','Email listing accounts that had late fees applied','LATE_FEE_REPORT',0),(6,'Automatic Barring List','Email listing accounts that will be automatically barred','AUTOMATIC_BARRING_LIST',0),(7,'Automatic Barring Report','Email listing accounts that have been automatically barred','AUTOMATIC_BARRING_REPORT',0),(8,'Automatic Unbarring Report','Email listing accounts that have been automatically unbarred','AUTOMATIC_UNBARRING_REPORT',0),(9,'Failed Email Report','Report of emails that failed to be delivered to customers','FAILED_EMAIL_REPORT',1),(10,'Ticketeting System','Messages sent from ticketing system','TICKETING_SYSTEM',1),(11,'Ticketeting System Admin Message','Messages sent from ticketing system administration message','TICKETING_SYSTEM_ADMIN_MESSAGE',0),(15,'Direct Debit Report','Email listing Accounts that are being Direct Debited with output files attached','DIRECT_DEBIT_REPORT',0),(16,'Payment Confirmation','Email sent to customers to acknowledge payments and direct debit setup','PAYMENT_CONFIRMATION',1),(17,'Support Form','Support form','SUPPORT_FORM',1),(19,'Voice mail files','Files containing voice mail message','VOICE_MAIL',0),(20,'Invoice Samples','Email listing Sample Accounts for the specified Invoice Run','INVOICE_SAMPLES',1),(21,'Invoice Samples Internal','Email listing Sample Accounts for the specified Internal Invoice Run','INVOICE_SAMPLES_INTERNAL',1),(22,'Sale Import Report','Report on the outcome of the Sale Import batch process','SALE_IMPORT_REPORT',0),(23,'Sale Automatic Provisioning Report','Report on the outcome of the Sale Automatic Provisioning batch process','SALE_AUTOMATIC_PROVISIONING_REPORT',0),(24,'Credit Control Status Change','Notification email for when an Account\'s Credit Control Status is changed','CREDIT_CONTROL_STATUS_CHANGE',1),(25,'Alert','Generic system alert','ALERT',0),(26,'Payment Alert','Payment related alert','PAYMENT_ALERT',0),(27,'Recurring Charge Report','Report generated by the Recurring Charge batch process','RECURRING_CHARGE_REPORT',0),(28,'1st Interim Invoice Report','1st Interim Invoice Report','FIRST_INTERIM_INVOICE_REPORT',0),(29,'Correspondence','Correspondence Notification Emails','CORRESPONDENCE',0),(30,'Delinquents Report','Delinquents Report',NULL,0),(31,'Follow-Ups Management Report','Follow-Ups Management Report',NULL,0),(32,'High Tolling Report - Wireless 3G Data','High Tolling Report - Wireless 3G Data',NULL,0),(33,'Motorpass Report','Motorpass Report',NULL,0),(35,'Unbar Report','Unbar Report',NULL,0),(36,'Usage Monitoring Report','Usage Monitoring Report',NULL,0),(37,'Tolling Report','Tolling Report',NULL,0),(38,'Plan Charge Cleanup Report','Plan Charge Cleanup Report',NULL,0),(39,'Pending Activation Report','Pending Activation Report',NULL,0),(40,'Multiline Exceptions Report','Multiline Exceptions Report',NULL,0),(41,'Fair Usage Policy Report','Fair Usage Policy Report',NULL,0),(42,'Fair Usage Policy Watch Report','Fair Usage Policy Watch Report',NULL,0),(43,'Daily Winback Report','Daily Winback Report',NULL,0),(44,'Collections Batch Process Report','Collections Batch Process Report','COLLECTIONS_BATCH_PROCESS_REPORT',0),(45,'Daily Welcome Report','Daily Welcome Report',NULL,0),(46,'FUP Charges Report','FUP Charges Report',NULL,0),(47,'Monthly Welcome Report','Monthly Welcome Report',NULL,0),(48,'Monthly Winback Report','Monthly Winback Report',NULL,0),(49,'Duplicate Payment Requests Report','Duplicate Payment Requests Report',NULL,0),(50,'Fortnightly Referral Lead Generation Report','Fortnightly Referral Lead Generation Report',NULL,0),(51,'Daily Sales Report','Daily Sales Report',NULL,0),(52,'Recontracting Campaign Report','Recontracting Campaign Report',NULL,0),(53,'Late Fee Reversal','Late Fee Reversal',NULL,0),(54,'Dishonoured Payment Report','Dishonoured Payment Report',NULL,0),(55,'Excessive 3G Data Cost Report','Excessive 3G Data Cost Report',NULL,0),(57,'Barring Report','Barring Report',NULL,0),(58,'Updated Customers in Collections Report','Updated Customers in Collections Report',NULL,0),(59,'Profit and Loss Report','Profit and Loss Report',NULL,0),(60,'Monthly Write-off report','Monthly Write-off report',NULL,0),(61,'Monthly Mobile Usage Report','Monthly Mobile Usage Report',NULL,0),(62,'Reconnection Fee','Reconnection Fee',NULL,0);
/*!40000 ALTER TABLE `email_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_notification_address`
--

DROP TABLE IF EXISTS `email_notification_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_notification_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the email notification address',
  `email_notification_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the email notification',
  `email_address_usage_id` bigint(20) unsigned NOT NULL COMMENT 'Id of the email address usage',
  `email_address` varchar(255) NOT NULL COMMENT 'The email address',
  `customer_group_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Id of the customer group or NULL to apply to ALL customer groups',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email notification address';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_notification_address`
--

LOCK TABLES `email_notification_address` WRITE;
/*!40000 ALTER TABLE `email_notification_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_notification_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_queue`
--

DROP TABLE IF EXISTS `email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `scheduled_datetime` datetime NOT NULL COMMENT 'Datetime the queue is to be delivered',
  `delivered_datetime` datetime DEFAULT NULL COMMENT 'Datetime the queue was delivered',
  `email_queue_batch_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) email_queue_batch. The batch that the queue was placed in at delivery time',
  `created_datetime` datetime NOT NULL COMMENT 'Timestamp for record creation',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee. Employee who created the record',
  `email_queue_status_id` int(11) NOT NULL COMMENT '(FK) email_queue_status. The status of the queue',
  `description` varchar(512) NOT NULL COMMENT 'The description of the Email Queue',
  PRIMARY KEY (`id`),
  KEY `fk_email_queue_email_queue_batch_id` (`email_queue_batch_id`),
  KEY `fk_email_queue_created_employee_id` (`created_employee_id`),
  KEY `fk_email_queue_email_queue_status_id` (`email_queue_status_id`),
  CONSTRAINT `fk_email_queue_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_queue_email_queue_batch_id` FOREIGN KEY (`email_queue_batch_id`) REFERENCES `email_queue_batch` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_queue_email_queue_status_id` FOREIGN KEY (`email_queue_status_id`) REFERENCES `email_queue_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='A queue of emails that is scheduled for delivery';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_queue`
--

LOCK TABLES `email_queue` WRITE;
/*!40000 ALTER TABLE `email_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_queue_batch`
--

DROP TABLE IF EXISTS `email_queue_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_queue_batch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `created_datetime` datetime NOT NULL COMMENT 'Timestamp for record creation',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='A batch of email queues that were delivered together';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_queue_batch`
--

LOCK TABLES `email_queue_batch` WRITE;
/*!40000 ALTER TABLE `email_queue_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_queue_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_queue_status`
--

DROP TABLE IF EXISTS `email_queue_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_queue_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the status',
  `description` varchar(128) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(128) NOT NULL COMMENT 'Constant alias for the status',
  `system_name` varchar(128) NOT NULL COMMENT 'System name for the status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_queue_status`
--

LOCK TABLES `email_queue_status` WRITE;
/*!40000 ALTER TABLE `email_queue_status` DISABLE KEYS */;
INSERT INTO `email_queue_status` VALUES (1,'Scheduled','Scheduled for delivery','EMAIL_QUEUE_STATUS_SCHEDULED','SCHEDULED'),(2,'Delivered','Successfully delivered','EMAIL_QUEUE_STATUS_DELIVERED','DELIVERED'),(3,'Cancelled','Delivered cancelled','EMAIL_QUEUE_STATUS_CANCELLED','CANCELLED');
/*!40000 ALTER TABLE `email_queue_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_status`
--

DROP TABLE IF EXISTS `email_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(512) DEFAULT NULL COMMENT 'Name of the status',
  `description` varchar(512) DEFAULT NULL COMMENT 'Description of the status',
  `const_name` varchar(512) DEFAULT NULL COMMENT 'Constant alias for the status',
  `system_name` varchar(512) DEFAULT NULL COMMENT 'System name for the status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='The status of an email that has been queued and scheduled fo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_status`
--

LOCK TABLES `email_status` WRITE;
/*!40000 ALTER TABLE `email_status` DISABLE KEYS */;
INSERT INTO `email_status` VALUES (1,'Awaiting Send','Awaiting Send','EMAIL_STATUS_AWAITING_SEND','AWAITING_SEND'),(2,'Sent','Sent','EMAIL_STATUS_SENT','SENT'),(3,'Not Sent','Not Sent','EMAIL_STATUS_NOT_SENT','NOT_SENT'),(4,'Sending Failed','Sending Failed','EMAIL_STATUS_SENDING_FAILED','SENDING_FAILED');
/*!40000 ALTER TABLE `email_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template`
--

DROP TABLE IF EXISTS `email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `system_name` varchar(45) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template`
--

LOCK TABLES `email_template` WRITE;
/*!40000 ALTER TABLE `email_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template_correspondence`
--

DROP TABLE IF EXISTS `email_template_correspondence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template_correspondence` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `email_template_id` bigint(20) NOT NULL COMMENT '(FK) email_template',
  `datasource_sql` text NOT NULL COMMENT 'Query to retrieve the variables that are required for the email template',
  `correspondence_template_id` bigint(20) NOT NULL COMMENT '(FK) correspondence_template',
  PRIMARY KEY (`id`),
  KEY `fk_email_template_correspondence_email_template_id` (`email_template_id`),
  KEY `fk_email_template_correspondence_correspondence_template_id` (`correspondence_template_id`),
  CONSTRAINT `fk_email_template_correspondence_correspondence_template_id` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_template_correspondence_email_template_id` FOREIGN KEY (`email_template_id`) REFERENCES `email_template` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template_correspondence`
--

LOCK TABLES `email_template_correspondence` WRITE;
/*!40000 ALTER TABLE `email_template_correspondence` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_correspondence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template_customer_group`
--

DROP TABLE IF EXISTS `email_template_customer_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template_customer_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_group_id` bigint(20) NOT NULL,
  `email_template_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_email_template_customer_group_id` (`customer_group_id`),
  KEY `fk_email_template_email_template_type_id` (`email_template_id`),
  CONSTRAINT `fk_email_template_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_email_template_email_template_id` FOREIGN KEY (`email_template_id`) REFERENCES `email_template` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template_customer_group`
--

LOCK TABLES `email_template_customer_group` WRITE;
/*!40000 ALTER TABLE `email_template_customer_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_customer_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template_details`
--

DROP TABLE IF EXISTS `email_template_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `description` varchar(512) NOT NULL,
  `email_template_customer_group_id` bigint(20) NOT NULL,
  `email_text` mediumtext NOT NULL,
  `email_html` mediumtext,
  `email_subject` varchar(512) NOT NULL,
  `created_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_employee_id` bigint(20) NOT NULL,
  `effective_datetime` date NOT NULL,
  `end_datetime` date NOT NULL,
  `email_from` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_email_template_details_email_template` (`email_template_customer_group_id`),
  CONSTRAINT `fk_email_template_details_email_template_customer_group_id` FOREIGN KEY (`email_template_customer_group_id`) REFERENCES `email_template_customer_group` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template_details`
--

LOCK TABLES `email_template_details` WRITE;
/*!40000 ALTER TABLE `email_template_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_account_log`
--

DROP TABLE IF EXISTS `employee_account_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_account_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who accessed the Account',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account that was accessed',
  `contact_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Contact that was accessed',
  `viewed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(FK) Contact that was accessed',
  `accepted_severity_warnings` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Employee` (`employee_id`),
  KEY `Account` (`account_id`),
  KEY `Contact` (`contact_id`),
  KEY `RequestedOn` (`viewed_on`),
  CONSTRAINT `fk_employee_account_log_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_account_log_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_account_log_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_account_log`
--

LOCK TABLES `employee_account_log` WRITE;
/*!40000 ALTER TABLE `employee_account_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_account_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_message`
--

DROP TABLE IF EXISTS `employee_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this message',
  `created_on` datetime NOT NULL COMMENT 'timestamp for when this record was created',
  `effective_on` datetime NOT NULL COMMENT 'time at which this message will come into effect',
  `message` longtext NOT NULL COMMENT 'the message',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Messages for employees';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_message`
--

LOCK TABLES `employee_message` WRITE;
/*!40000 ALTER TABLE `employee_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_operation`
--

DROP TABLE IF EXISTS `employee_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_operation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee',
  `operation_id` int(10) unsigned NOT NULL COMMENT '(FK) Permitted Operation',
  `start_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Effective Start Datetime for this permission',
  `end_datetime` datetime NOT NULL DEFAULT '9999-12-31 23:59:59' COMMENT 'Effective End Datetime for this permission',
  `assigned_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when the permission was assigned',
  `assigned_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who assigned this permission',
  PRIMARY KEY (`id`),
  KEY `fk_employee_operation_employee_id` (`employee_id`),
  KEY `fk_employee_operation_operation_id` (`operation_id`),
  KEY `fk_employee_operation_assigned_employee_id` (`assigned_employee_id`),
  CONSTRAINT `fk_employee_operation_assigned_employee_id` FOREIGN KEY (`assigned_employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_operation_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_operation_operation_id` FOREIGN KEY (`operation_id`) REFERENCES `operation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_operation`
--

LOCK TABLES `employee_operation` WRITE;
/*!40000 ALTER TABLE `employee_operation` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_operation_log`
--

DROP TABLE IF EXISTS `employee_operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_operation_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee',
  `operation_id` int(10) unsigned NOT NULL COMMENT '(FK) Operation',
  `was_authorised` tinyint(4) NOT NULL COMMENT '1: Authorised; 0: Restricted',
  `operation_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of the Operation',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_operation_log`
--

LOCK TABLES `employee_operation_log` WRITE;
/*!40000 ALTER TABLE `employee_operation_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_operation_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_operation_log_account`
--

DROP TABLE IF EXISTS `employee_operation_log_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_operation_log_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `employee_operation_log_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee Operation Log',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account',
  PRIMARY KEY (`id`),
  KEY `fk_employee_operation_log_account_employee_operation_log_id` (`employee_operation_log_id`),
  KEY `fk_employee_operation_log_account_employee_account_id` (`account_id`),
  CONSTRAINT `fk_employee_operation_log_account_employee_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_operation_log_account_employee_operation_log_id` FOREIGN KEY (`employee_operation_log_id`) REFERENCES `employee_operation_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_operation_log_account`
--

LOCK TABLES `employee_operation_log_account` WRITE;
/*!40000 ALTER TABLE `employee_operation_log_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_operation_log_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_operation_log_service`
--

DROP TABLE IF EXISTS `employee_operation_log_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_operation_log_service` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `employee_operation_log_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee Operation Log',
  `service_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Service',
  PRIMARY KEY (`id`),
  KEY `fk_employee_operation_log_service_employee_operation_log_id` (`employee_operation_log_id`),
  KEY `fk_employee_operation_log_service_employee_service_id` (`service_id`),
  CONSTRAINT `fk_employee_operation_log_service_employee_operation_log_id` FOREIGN KEY (`employee_operation_log_id`) REFERENCES `employee_operation_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_operation_log_service_employee_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_operation_log_service`
--

LOCK TABLES `employee_operation_log_service` WRITE;
/*!40000 ALTER TABLE `employee_operation_log_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_operation_log_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_operation_profile`
--

DROP TABLE IF EXISTS `employee_operation_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_operation_profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee',
  `operation_profile_id` int(10) unsigned NOT NULL COMMENT '(FK) Permitted Operation Profile',
  `start_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Effective Start Datetime for this permission',
  `end_datetime` datetime NOT NULL DEFAULT '9999-12-31 23:59:59' COMMENT 'Effective End Datetime for this permission',
  `assigned_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when the permission was assigned',
  `assigned_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who assigned this permission',
  PRIMARY KEY (`id`),
  KEY `fk_employee_operation_profile_employee_id` (`employee_id`),
  KEY `fk_employee_operation_profile_operation_profile_id` (`operation_profile_id`),
  KEY `fk_employee_operation_profile_assigned_employee_id` (`assigned_employee_id`),
  CONSTRAINT `fk_employee_operation_profile_assigned_employee_id` FOREIGN KEY (`assigned_employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_operation_profile_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_operation_profile_operation_profile_id` FOREIGN KEY (`operation_profile_id`) REFERENCES `operation_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_operation_profile`
--

LOCK TABLES `employee_operation_profile` WRITE;
/*!40000 ALTER TABLE `employee_operation_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_operation_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_import_data`
--

DROP TABLE IF EXISTS `file_import_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_import_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `file_import_id` bigint(20) unsigned NOT NULL COMMENT '(FK) FileImport. The source file.',
  `sequence_no` varchar(128) NOT NULL COMMENT 'Position within the source file.',
  `data` varchar(32767) DEFAULT NULL COMMENT 'Raw data, if null it has been archived and stored on disk',
  `file_import_data_status_id` int(11) NOT NULL COMMENT '(FK) file_import_data_status',
  `reason` varchar(512) DEFAULT NULL COMMENT '(optional) Reason for the status',
  PRIMARY KEY (`id`),
  KEY `fk_file_import_data_file_import_id` (`file_import_id`),
  KEY `fk_file_import_data_file_import_data_status_id` (`file_import_data_status_id`),
  CONSTRAINT `fk_file_import_data_file_import_data_status_id` FOREIGN KEY (`file_import_data_status_id`) REFERENCES `file_import_data_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_file_import_data_file_import_id` FOREIGN KEY (`file_import_id`) REFERENCES `FileImport` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_import_data`
--

LOCK TABLES `file_import_data` WRITE;
/*!40000 ALTER TABLE `file_import_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_import_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_import_data_status`
--

DROP TABLE IF EXISTS `file_import_data_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_import_data_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the status',
  `description` varchar(128) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(128) NOT NULL COMMENT 'Constant alias for the status',
  `system_name` varchar(128) NOT NULL COMMENT 'System name for the status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_import_data_status`
--

LOCK TABLES `file_import_data_status` WRITE;
/*!40000 ALTER TABLE `file_import_data_status` DISABLE KEYS */;
INSERT INTO `file_import_data_status` VALUES (1,'Imported','Imported','FILE_IMPORT_DATA_STATUS_IMPORTED','IMPORTED'),(2,'Normalised','Normalised','FILE_IMPORT_DATA_STATUS_NORMALISED','NORMALISED'),(3,'Normalisation Failed','Normalisation Failed','FILE_IMPORT_DATA_STATUS_NORMALISATION_FAILED','NORMALISATION_FAILED'),(4,'Ignored','Ignored','FILE_IMPORT_DATA_STATUS_IGNORED','IGNORED');
/*!40000 ALTER TABLE `file_import_data_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_type`
--

DROP TABLE IF EXISTS `file_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the File Type',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the File Type',
  `extension` varchar(32) NOT NULL COMMENT 'File Type Extension',
  `icon_16x16` mediumblob COMMENT 'Small Icon for the File Type',
  `icon_64x64` mediumblob COMMENT 'Large Icon for the File Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_type`
--

LOCK TABLES `file_type` WRITE;
/*!40000 ALTER TABLE `file_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_type_mime_type`
--

DROP TABLE IF EXISTS `file_type_mime_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_type_mime_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `file_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) File Type',
  `mime_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) MIME Type',
  `is_preferred_mime_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1: Preferred Export MIME Type; 0: Alternate MIME Type',
  PRIMARY KEY (`id`),
  KEY `fk_file_type_mime_type_file_type_id` (`file_type_id`),
  KEY `fk_file_type_mime_type_mime_type_id` (`mime_type_id`),
  CONSTRAINT `fk_file_type_mime_type_file_type_id` FOREIGN KEY (`file_type_id`) REFERENCES `file_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_file_type_mime_type_mime_type_id` FOREIGN KEY (`mime_type_id`) REFERENCES `mime_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_type_mime_type`
--

LOCK TABLES `file_type_mime_type` WRITE;
/*!40000 ALTER TABLE `file_type_mime_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_type_mime_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flex_config`
--

DROP TABLE IF EXISTS `flex_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flex_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `created_by` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who created this version of the Flex Config',
  `created_on` datetime NOT NULL COMMENT 'When this version of the Flex Config was created',
  `internal_contact_list_html` varchar(50000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_flex_config_created_by` (`created_by`),
  CONSTRAINT `fk_flex_config_created_by` FOREIGN KEY (`created_by`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flex_config`
--

LOCK TABLES `flex_config` WRITE;
/*!40000 ALTER TABLE `flex_config` DISABLE KEYS */;
INSERT INTO `flex_config` VALUES (1,0,'0000-00-00 00:00:00',NULL);
/*!40000 ALTER TABLE `flex_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flex_module`
--

DROP TABLE IF EXISTS `flex_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flex_module` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique id for the module',
  `name` varchar(1024) NOT NULL COMMENT 'Unique name for the module',
  `description` varchar(1024) NOT NULL COMMENT 'description',
  `const_name` varchar(1024) NOT NULL COMMENT 'constant name',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'boolean value (0 = module is turned off, 1 = module is turned on)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines flex modules and whether they are to be used by flex';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flex_module`
--

LOCK TABLES `flex_module` WRITE;
/*!40000 ALTER TABLE `flex_module` DISABLE KEYS */;
INSERT INTO `flex_module` VALUES (1,'Online Credit Card Payments','Online Credit Card Payments Module','FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS',1),(2,'Customer Status','Customer Status','FLEX_MODULE_CUSTOMER_STATUS',1),(3,'Sales Portal','Sales Portal Module','FLEX_MODULE_SALES_PORTAL',1),(4,'Knowledge Base','Knowledge Base Module','FLEX_MODULE_KNOWLEDGE_BASE',1),(5,'Ticketing System','Ticketing System Module','FLEX_MODULE_TICKETING',1),(6,'Document Management','Document Management Module','FLEX_MODULE_DOCUMENT_MANAGEMENT',1),(7,'Contact List','Internal Contact List Module','FLEX_MODULE_CONTACT_LIST',1),(8,'Plan Brochures','Plan Brochures Module','FLEX_MODULE_PLAN_BROCHURE',1),(9,'Authorisation Scripts',' Plan Change Voice Authorisation Scripts Module','FLEX_MODULE_PLAN_AUTH_SCRIPT',1),(10,'Interim Invoices','Interim Invoices Module','FLEX_MODULE_INVOICE_INTERIM',1),(11,'Final Invoices','Final Invoices Module','FLEX_MODULE_INVOICE_FINAL',1),(12,'Telemarketing','Telemarketing Module','FLEX_MODULE_TELEMARKETING',1),(13,'Contract Management','Contract Management Module','FLEX_MODULE_CONTRACT_MANAGEMENT',1),(14,'Calendar','Calendar Module','FLEX_MODULE_CALENDAR',1);
/*!40000 ALTER TABLE `flex_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup`
--

DROP TABLE IF EXISTS `followup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `assigned_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who is assigned the FollowUp',
  `created_datetime` datetime NOT NULL COMMENT 'Time that the FollowUp is created',
  `due_datetime` datetime NOT NULL COMMENT 'DateTime that the FollowUp is due',
  `followup_type_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_type',
  `followup_category_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_category',
  `followup_closure_id` int(10) unsigned DEFAULT NULL COMMENT '(fk) followup_closure - reason for closing, is linked to followup_closure_type',
  `closed_datetime` datetime DEFAULT NULL COMMENT 'Time that the FollowUp is closed',
  `followup_recurring_id` int(10) unsigned DEFAULT NULL COMMENT '(fk) followup_recurring - optional link to a Recurring FollowUp',
  `modified_datetime` datetime NOT NULL COMMENT 'Last time that the FollowUp was modified',
  `modified_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who was last to modify the FollowUp',
  PRIMARY KEY (`id`),
  KEY `fk_followup_assigned_employee_id` (`assigned_employee_id`),
  KEY `fk_followup_followup_type_id` (`followup_type_id`),
  KEY `fk_followup_followup_category_id` (`followup_category_id`),
  KEY `fk_followup_followup_closure_id` (`followup_closure_id`),
  KEY `fk_followup_modified_employee_id` (`modified_employee_id`),
  CONSTRAINT `fk_followup_assigned_employee_id` FOREIGN KEY (`assigned_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_followup_category_id` FOREIGN KEY (`followup_category_id`) REFERENCES `followup_category` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_followup_closure_id` FOREIGN KEY (`followup_closure_id`) REFERENCES `followup_closure` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_followup_type_id` FOREIGN KEY (`followup_type_id`) REFERENCES `followup_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_modified_employee_id` FOREIGN KEY (`modified_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup`
--

LOCK TABLES `followup` WRITE;
/*!40000 ALTER TABLE `followup` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_action`
--

DROP TABLE IF EXISTS `followup_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_action` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_id` int(10) unsigned NOT NULL COMMENT '(fk) followup',
  `action_id` bigint(20) unsigned NOT NULL COMMENT '(fk) action - that the FollowUp relates to',
  PRIMARY KEY (`id`),
  KEY `fk_followup_action_followup_id` (`followup_id`),
  KEY `fk_followup_action_action_id` (`action_id`),
  CONSTRAINT `fk_followup_action_action_id` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_action_followup_id` FOREIGN KEY (`followup_id`) REFERENCES `followup` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_action`
--

LOCK TABLES `followup_action` WRITE;
/*!40000 ALTER TABLE `followup_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_category`
--

DROP TABLE IF EXISTS `followup_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the FollowUp Category',
  `description` varchar(256) NOT NULL COMMENT 'Description of the FollowUp Category',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(fk) status',
  PRIMARY KEY (`id`),
  KEY `fk_followup_category_status_id` (`status_id`),
  CONSTRAINT `fk_followup_category_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_category`
--

LOCK TABLES `followup_category` WRITE;
/*!40000 ALTER TABLE `followup_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_closure`
--

DROP TABLE IF EXISTS `followup_closure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_closure` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_closure_type_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_closure_type - the base type for this closure',
  `name` varchar(128) NOT NULL COMMENT 'Name of the FollowUp Closure',
  `description` varchar(256) NOT NULL COMMENT 'Reason for the closure of a FollowUp',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(fk) status',
  PRIMARY KEY (`id`),
  KEY `fk_followup_closure_followup_closure_type_id` (`followup_closure_type_id`),
  KEY `fk_followup_closure_status_id` (`status_id`),
  CONSTRAINT `fk_followup_closure_followup_closure_type_id` FOREIGN KEY (`followup_closure_type_id`) REFERENCES `followup_closure_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_closure_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_closure`
--

LOCK TABLES `followup_closure` WRITE;
/*!40000 ALTER TABLE `followup_closure` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_closure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_closure_type`
--

DROP TABLE IF EXISTS `followup_closure_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_closure_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the FollowUp Closure Type',
  `description` varchar(256) NOT NULL COMMENT 'Description of the FollowUp Closure Type',
  `const_name` varchar(256) NOT NULL COMMENT 'Constant Alias of the FollowUp Closure Type',
  `system_name` varchar(128) NOT NULL COMMENT 'System Name of the FollowUp Closure Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_closure_type`
--

LOCK TABLES `followup_closure_type` WRITE;
/*!40000 ALTER TABLE `followup_closure_type` DISABLE KEYS */;
INSERT INTO `followup_closure_type` VALUES (1,'Completed','The Follow-Up has been completed','FOLLOWUP_CLOSURE_TYPE_COMPLETED','COMPLETED'),(2,'Dismissed','The Follow-Up has been dismissed','FOLLOWUP_CLOSURE_TYPE_DISMISSED','DISMISSED');
/*!40000 ALTER TABLE `followup_closure_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_history`
--

DROP TABLE IF EXISTS `followup_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_id` int(10) unsigned NOT NULL COMMENT '(fk) followup',
  `due_datetime` datetime NOT NULL COMMENT 'DateTime that the FollowUp was due',
  `assigned_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who was assigned the FollowUp',
  `modified_datetime` datetime NOT NULL COMMENT 'Time that the FollowUp was modified',
  `modified_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who modified the FollowUp',
  PRIMARY KEY (`id`),
  KEY `fk_followup_history_followup_id` (`followup_id`),
  KEY `fk_followup_history_assigned_employee_id` (`assigned_employee_id`),
  KEY `fk_followup_history_modified_employee_id` (`modified_employee_id`),
  CONSTRAINT `fk_followup_history_assigned_employee_id` FOREIGN KEY (`assigned_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_history_followup_id` FOREIGN KEY (`followup_id`) REFERENCES `followup` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_history_modified_employee_id` FOREIGN KEY (`modified_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_history`
--

LOCK TABLES `followup_history` WRITE;
/*!40000 ALTER TABLE `followup_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_history_modify_reason`
--

DROP TABLE IF EXISTS `followup_history_modify_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_history_modify_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `history_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_history',
  `modify_reason_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_modify_reason',
  PRIMARY KEY (`id`),
  KEY `fk_followup_history_modify_reason_history_id` (`history_id`),
  KEY `fk_followup_history_modify_reason_modify_reason_id` (`modify_reason_id`),
  CONSTRAINT `fk_followup_history_modify_reason_history_id` FOREIGN KEY (`history_id`) REFERENCES `followup_history` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_history_modify_reason_modify_reason_id` FOREIGN KEY (`modify_reason_id`) REFERENCES `followup_modify_reason` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_history_modify_reason`
--

LOCK TABLES `followup_history_modify_reason` WRITE;
/*!40000 ALTER TABLE `followup_history_modify_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_history_modify_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_history_reassign_reason`
--

DROP TABLE IF EXISTS `followup_history_reassign_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_history_reassign_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `history_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_history',
  `reassign_reason_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_reassign_reason',
  PRIMARY KEY (`id`),
  KEY `fk_followup_history_reassign_reason_history_id` (`history_id`),
  KEY `fk_followup_history_reassign_reason_modify_reason_id` (`reassign_reason_id`),
  CONSTRAINT `fk_followup_history_reassign_reason_history_id` FOREIGN KEY (`history_id`) REFERENCES `followup_history` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_history_reassign_reason_modify_reason_id` FOREIGN KEY (`reassign_reason_id`) REFERENCES `followup_reassign_reason` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_history_reassign_reason`
--

LOCK TABLES `followup_history_reassign_reason` WRITE;
/*!40000 ALTER TABLE `followup_history_reassign_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_history_reassign_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_modify_reason`
--

DROP TABLE IF EXISTS `followup_modify_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_modify_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the FollowUp Modification Reason',
  `description` varchar(256) NOT NULL COMMENT 'Description of the FollowUp Modification Reason',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(fk) status',
  PRIMARY KEY (`id`),
  KEY `fk_followup_modify_reason_status_id` (`status_id`),
  CONSTRAINT `fk_followup_modify_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_modify_reason`
--

LOCK TABLES `followup_modify_reason` WRITE;
/*!40000 ALTER TABLE `followup_modify_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_modify_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_note`
--

DROP TABLE IF EXISTS `followup_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_note` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_id` int(10) unsigned NOT NULL COMMENT '(fk) followup',
  `note_id` bigint(20) unsigned NOT NULL COMMENT '(fk) note - that the FollowUp relates to',
  PRIMARY KEY (`id`),
  KEY `fk_followup_note_followup_id` (`followup_id`),
  KEY `fk_followup_note_note_id` (`note_id`),
  CONSTRAINT `fk_followup_note_followup_id` FOREIGN KEY (`followup_id`) REFERENCES `followup` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_note_note_id` FOREIGN KEY (`note_id`) REFERENCES `Note` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_note`
--

LOCK TABLES `followup_note` WRITE;
/*!40000 ALTER TABLE `followup_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_reassign_reason`
--

DROP TABLE IF EXISTS `followup_reassign_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_reassign_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the Follow-Up Reassign Reason',
  `description` varchar(256) NOT NULL COMMENT 'Description of the Follow-Up Reassign Reason',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(fk) status',
  PRIMARY KEY (`id`),
  KEY `fk_followup_reassign_reason_status_id` (`status_id`),
  CONSTRAINT `fk_followup_reassign_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_reassign_reason`
--

LOCK TABLES `followup_reassign_reason` WRITE;
/*!40000 ALTER TABLE `followup_reassign_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_reassign_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurrence_period`
--

DROP TABLE IF EXISTS `followup_recurrence_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurrence_period` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the FollowUp Recurrence Period',
  `description` varchar(256) NOT NULL COMMENT 'Description of the FollowUp Recurrence Period',
  `const_name` varchar(256) NOT NULL COMMENT 'Constant Alias of the FollowUp Recurrence Period',
  `system_name` varchar(128) NOT NULL COMMENT 'System Name of the FollowUp Recurrence Period',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurrence_period`
--

LOCK TABLES `followup_recurrence_period` WRITE;
/*!40000 ALTER TABLE `followup_recurrence_period` DISABLE KEYS */;
INSERT INTO `followup_recurrence_period` VALUES (1,'Week','Week','FOLLOWUP_RECURRENCE_PERIOD_WEEK','WEEK'),(2,'Month','Month','FOLLOWUP_RECURRENCE_PERIOD_MONTH','MONTH');
/*!40000 ALTER TABLE `followup_recurrence_period` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring`
--

DROP TABLE IF EXISTS `followup_recurring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `assigned_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who is assigned the Recurring Followup',
  `created_datetime` datetime NOT NULL COMMENT 'Time the Recurring FollowUp is created',
  `start_datetime` datetime NOT NULL COMMENT 'DateTime that the Recurring FollowUp will start recurring',
  `end_datetime` datetime DEFAULT '9999-12-31 23:59:00' COMMENT 'DateTime that the Recurring FollowUp will stop recurring',
  `followup_type_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_type',
  `followup_category_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_category',
  `recurrence_multiplier` int(10) unsigned NOT NULL COMMENT 'How many recurrence periods should pass between iterations',
  `followup_recurrence_period_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurrence_period_id',
  `modified_datetime` datetime NOT NULL COMMENT 'Last time that the Recurring FollowUp was modified',
  `modified_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who was last to modify the Recurring FollowUp',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_assigned_employee_id` (`assigned_employee_id`),
  KEY `fk_followup_recurring_followup_type_id` (`followup_type_id`),
  KEY `fk_followup_recurring_followup_category_id` (`followup_category_id`),
  KEY `fk_followup_recurring_followup_recurrence_period_id` (`followup_recurrence_period_id`),
  KEY `fk_followup_recurring_modified_employee_id` (`modified_employee_id`),
  CONSTRAINT `fk_followup_recurring_assigned_employee_id` FOREIGN KEY (`assigned_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_followup_category_id` FOREIGN KEY (`followup_category_id`) REFERENCES `followup_category` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_followup_recurrence_period_id` FOREIGN KEY (`followup_recurrence_period_id`) REFERENCES `followup_recurrence_period` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_followup_type_id` FOREIGN KEY (`followup_type_id`) REFERENCES `followup_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_modified_employee_id` FOREIGN KEY (`modified_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring`
--

LOCK TABLES `followup_recurring` WRITE;
/*!40000 ALTER TABLE `followup_recurring` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_action`
--

DROP TABLE IF EXISTS `followup_recurring_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_action` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_recurring_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring',
  `action_id` bigint(20) unsigned NOT NULL COMMENT '(fk) action - that the Recurring FollowUp relates to',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_action_followup_recurring_id` (`followup_recurring_id`),
  KEY `fk_followup_recurring_action_action_id` (`action_id`),
  CONSTRAINT `fk_followup_recurring_action_action_id` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_action_followup_recurring_id` FOREIGN KEY (`followup_recurring_id`) REFERENCES `followup_recurring` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_action`
--

LOCK TABLES `followup_recurring_action` WRITE;
/*!40000 ALTER TABLE `followup_recurring_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_history`
--

DROP TABLE IF EXISTS `followup_recurring_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_recurring_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring',
  `assigned_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who was assigned the Recurring FollowUp',
  `end_datetime` datetime DEFAULT NULL COMMENT 'DateTime that the Recurring FollowUp will stop recurring',
  `modified_datetime` datetime NOT NULL COMMENT 'Time that the Recurring FollowUp was modified',
  `modified_employee_id` bigint(20) unsigned NOT NULL COMMENT '(fk) Employee - who modified the Recurring FollowUp',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_history_followup_recurring_id` (`followup_recurring_id`),
  KEY `fk_followup_recurring_history_assigned_employee_id` (`assigned_employee_id`),
  KEY `fk_followup_recurring_history_modified_employee_id` (`modified_employee_id`),
  CONSTRAINT `fk_followup_recurring_history_assigned_employee_id` FOREIGN KEY (`assigned_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_history_followup_recurring_id` FOREIGN KEY (`followup_recurring_id`) REFERENCES `followup_recurring` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_history_modified_employee_id` FOREIGN KEY (`modified_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_history`
--

LOCK TABLES `followup_recurring_history` WRITE;
/*!40000 ALTER TABLE `followup_recurring_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_history_modify_reason`
--

DROP TABLE IF EXISTS `followup_recurring_history_modify_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_history_modify_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `history_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring_history',
  `modify_reason_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring_modify_reason',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_history_modify_reason_history_id` (`history_id`),
  KEY `fk_followup_recurring_history_modify_reason_modify_reason_id` (`modify_reason_id`),
  CONSTRAINT `fk_followup_recurring_history_modify_reason_history_id` FOREIGN KEY (`history_id`) REFERENCES `followup_recurring_history` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_history_modify_reason_modify_reason_id` FOREIGN KEY (`modify_reason_id`) REFERENCES `followup_recurring_modify_reason` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_history_modify_reason`
--

LOCK TABLES `followup_recurring_history_modify_reason` WRITE;
/*!40000 ALTER TABLE `followup_recurring_history_modify_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_history_modify_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_history_reassign_reason`
--

DROP TABLE IF EXISTS `followup_recurring_history_reassign_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_history_reassign_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `history_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring_history',
  `reassign_reason_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_reassign_reason',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_history_reassign_reason_history_id` (`history_id`),
  KEY `fk_followup_recurring_history_reassign_reason_modify_reason_id` (`reassign_reason_id`),
  CONSTRAINT `fk_followup_recurring_history_reassign_reason_history_id` FOREIGN KEY (`history_id`) REFERENCES `followup_recurring_history` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_history_reassign_reason_modify_reason_id` FOREIGN KEY (`reassign_reason_id`) REFERENCES `followup_reassign_reason` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_history_reassign_reason`
--

LOCK TABLES `followup_recurring_history_reassign_reason` WRITE;
/*!40000 ALTER TABLE `followup_recurring_history_reassign_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_history_reassign_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_modify_reason`
--

DROP TABLE IF EXISTS `followup_recurring_modify_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_modify_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the Recurring FollowUp Modification Reason',
  `description` varchar(256) NOT NULL COMMENT 'Description of the Recurring FollowUp Modification Reason',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(fk) status',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_reason_status_id` (`status_id`),
  CONSTRAINT `fk_followup_recurring_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_modify_reason`
--

LOCK TABLES `followup_recurring_modify_reason` WRITE;
/*!40000 ALTER TABLE `followup_recurring_modify_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_modify_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_note`
--

DROP TABLE IF EXISTS `followup_recurring_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_note` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_recurring_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring',
  `note_id` bigint(20) unsigned NOT NULL COMMENT '(fk) note - that the Recurring FollowUp relates to',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_note_followup_recurring_id` (`followup_recurring_id`),
  KEY `fk_followup_recurring_note_note_id` (`note_id`),
  CONSTRAINT `fk_followup_recurring_note_followup_recurring_id` FOREIGN KEY (`followup_recurring_id`) REFERENCES `followup_recurring` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_note_note_id` FOREIGN KEY (`note_id`) REFERENCES `Note` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_note`
--

LOCK TABLES `followup_recurring_note` WRITE;
/*!40000 ALTER TABLE `followup_recurring_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_recurring_ticketing_correspondence`
--

DROP TABLE IF EXISTS `followup_recurring_ticketing_correspondence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_recurring_ticketing_correspondence` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_recurring_id` int(10) unsigned NOT NULL COMMENT '(fk) followup_recurring',
  `ticketing_correspondence_id` bigint(20) unsigned NOT NULL COMMENT '(fk) ticketing_correspondence - that the Recurring FollowUp relates to',
  PRIMARY KEY (`id`),
  KEY `fk_followup_recurring_ticketing_correspondence_f_r_id` (`followup_recurring_id`),
  KEY `fk_followup_recurring_ticketing_correspondence_t_c_id` (`ticketing_correspondence_id`),
  CONSTRAINT `fk_followup_recurring_ticketing_correspondence_f_r_id` FOREIGN KEY (`followup_recurring_id`) REFERENCES `followup_recurring` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_recurring_ticketing_correspondence_t_c_id` FOREIGN KEY (`ticketing_correspondence_id`) REFERENCES `ticketing_correspondance` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_recurring_ticketing_correspondence`
--

LOCK TABLES `followup_recurring_ticketing_correspondence` WRITE;
/*!40000 ALTER TABLE `followup_recurring_ticketing_correspondence` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_recurring_ticketing_correspondence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_ticketing_correspondence`
--

DROP TABLE IF EXISTS `followup_ticketing_correspondence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_ticketing_correspondence` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `followup_id` int(10) unsigned NOT NULL COMMENT '(fk) followup',
  `ticketing_correspondence_id` bigint(20) unsigned NOT NULL COMMENT '(fk) ticketing_correspondence - that the FollowUp relates to',
  PRIMARY KEY (`id`),
  KEY `fk_followup_ticketing_correspondence_followup_id` (`followup_id`),
  KEY `fk_followup_ticketing_correspondence_ticketing_correspondence_id` (`ticketing_correspondence_id`),
  CONSTRAINT `fk_followup_ticketing_correspondence_followup_id` FOREIGN KEY (`followup_id`) REFERENCES `followup` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_followup_ticketing_correspondence_ticketing_correspondence_id` FOREIGN KEY (`ticketing_correspondence_id`) REFERENCES `ticketing_correspondance` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_ticketing_correspondence`
--

LOCK TABLES `followup_ticketing_correspondence` WRITE;
/*!40000 ALTER TABLE `followup_ticketing_correspondence` DISABLE KEYS */;
/*!40000 ALTER TABLE `followup_ticketing_correspondence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_type`
--

DROP TABLE IF EXISTS `followup_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `followup_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the FollowUp Type',
  `description` varchar(256) NOT NULL COMMENT 'Description of the FollowUp Type',
  `const_name` varchar(256) NOT NULL COMMENT 'Constant Alias of the FollowUp Type',
  `system_name` varchar(128) NOT NULL COMMENT 'System Name of the FollowUp Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_type`
--

LOCK TABLES `followup_type` WRITE;
/*!40000 ALTER TABLE `followup_type` DISABLE KEYS */;
INSERT INTO `followup_type` VALUES (1,'Note','Relates to a Note','FOLLOWUP_TYPE_NOTE','NOTE'),(2,'Action','Relates to an Action','FOLLOWUP_TYPE_ACTION','ACTION'),(3,'Ticket Correspondence','Relates to a piece of Ticket Correspondence','FOLLOWUP_TYPE_TICKET_CORRESPONDENCE','TICKET_CORRESPONDENCE');
/*!40000 ALTER TABLE `followup_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_run_schedule`
--

DROP TABLE IF EXISTS `invoice_run_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_run_schedule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Scheduled Invoice Run',
  `customer_group_id` bigint(20) unsigned NOT NULL COMMENT 'CusotmerGroup this InvoiceRun applies to',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Scheduled Invoice Run',
  `invoice_day_offset` int(11) NOT NULL COMMENT 'Offset in days from the Billing Date that this will run',
  `invoice_run_type_id` bigint(20) NOT NULL COMMENT 'The Type of Invoice Run',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_run_schedule`
--

LOCK TABLES `invoice_run_schedule` WRITE;
/*!40000 ALTER TABLE `invoice_run_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_run_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_run_status`
--

DROP TABLE IF EXISTS `invoice_run_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_run_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Invoice Run Status',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Invoice Run Status',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Invoice Run Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Invoice Run Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_run_status`
--

LOCK TABLES `invoice_run_status` WRITE;
/*!40000 ALTER TABLE `invoice_run_status` DISABLE KEYS */;
INSERT INTO `invoice_run_status` VALUES (1,'Generating','Generating Invoices','INVOICE_RUN_STATUS_GENERATING'),(2,'Temporary','Temporary','INVOICE_RUN_STATUS_TEMPORARY'),(3,'Revoking','Revoking Invoices','INVOICE_RUN_STATUS_REVOKING'),(4,'Revoked','Revoked','INVOICE_RUN_STATUS_REVOKED'),(5,'Committing','Committing Invoices','INVOICE_RUN_STATUS_COMMITTING'),(6,'Committed','Committed','INVOICE_RUN_STATUS_COMMITTED');
/*!40000 ALTER TABLE `invoice_run_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_run_type`
--

DROP TABLE IF EXISTS `invoice_run_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_run_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Invoice Run Type',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Invoice Run Type',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Invoice Run Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Invoice Run Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_run_type`
--

LOCK TABLES `invoice_run_type` WRITE;
/*!40000 ALTER TABLE `invoice_run_type` DISABLE KEYS */;
INSERT INTO `invoice_run_type` VALUES (1,'Live Run','Live Run','INVOICE_RUN_TYPE_LIVE'),(2,'Internal Samples','Internal Samples','INVOICE_RUN_TYPE_INTERNAL_SAMPLES'),(3,'Samples','Samples','INVOICE_RUN_TYPE_SAMPLES'),(4,'Interim Invoice','Interim Invoice','INVOICE_RUN_TYPE_INTERIM'),(5,'Final Invoice','Final Invoice','INVOICE_RUN_TYPE_FINAL'),(6,'Interim First Invoice','Interim First Invoice','INVOICE_RUN_TYPE_INTERIM_FIRST'),(7,'Invoice Rerate','Invoice Rerate','INVOICE_RUN_TYPE_RERATE');
/*!40000 ALTER TABLE `invoice_run_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_run_type_correspondence_template`
--

DROP TABLE IF EXISTS `invoice_run_type_correspondence_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_run_type_correspondence_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_run_type_id` bigint(20) unsigned NOT NULL,
  `correspondence_template_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_invoice_run_type_correspondence_template_invoice_run_type` (`invoice_run_type_id`),
  KEY `fk_invoice_run_type_correspondence_template_template` (`correspondence_template_id`),
  CONSTRAINT `fk_invoice_run_type_correspondence_template_invoice_run_type` FOREIGN KEY (`invoice_run_type_id`) REFERENCES `invoice_run_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_run_type_correspondence_template_template` FOREIGN KEY (`correspondence_template_id`) REFERENCES `correspondence_template` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_run_type_correspondence_template`
--

LOCK TABLES `invoice_run_type_correspondence_template` WRITE;
/*!40000 ALTER TABLE `invoice_run_type_correspondence_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_run_type_correspondence_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mime_type`
--

DROP TABLE IF EXISTS `mime_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mime_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Mime Type',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Mime Type',
  `mime_content_type` varchar(255) NOT NULL COMMENT 'Mime Content Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mime_type`
--

LOCK TABLES `mime_type` WRITE;
/*!40000 ALTER TABLE `mime_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `mime_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_account`
--

DROP TABLE IF EXISTS `motorpass_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_number` int(9) unsigned DEFAULT NULL,
  `account_name` varchar(256) NOT NULL,
  `abn` char(11) NOT NULL,
  `motorpass_promotion_code_id` bigint(20) unsigned NOT NULL,
  `business_commencement_date` date NOT NULL,
  `motorpass_business_structure_id` bigint(20) unsigned NOT NULL,
  `business_structure_description` varchar(128) NOT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `email_invoice` int(11) NOT NULL,
  `street_address_id` bigint(20) unsigned NOT NULL,
  `postal_address_id` bigint(20) unsigned DEFAULT NULL,
  `motorpass_contact_id` bigint(20) unsigned NOT NULL,
  `motorpass_card_id` bigint(20) unsigned NOT NULL,
  `external_sale_id` bigint(20) unsigned NOT NULL,
  `external_sale_datetime` datetime NOT NULL,
  `file_export_id` bigint(20) unsigned DEFAULT NULL,
  `file_import_id` bigint(20) unsigned DEFAULT NULL,
  `motorpass_account_status_id` bigint(20) unsigned NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_employee_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_account_motorpass_promotion_code_id` (`motorpass_promotion_code_id`),
  KEY `fk_motorpass_account_motorpass_business_structure_id` (`motorpass_business_structure_id`),
  KEY `fk_motorpass_account_street_address_id` (`street_address_id`),
  KEY `fk_motorpass_account_postal_address_id` (`postal_address_id`),
  KEY `fk_motorpass_account_motorpass_contact_id` (`motorpass_contact_id`),
  KEY `fk_motorpass_account_motorpass_card_id` (`motorpass_card_id`),
  KEY `fk_motorpass_account_motorpass_account_status_id` (`motorpass_account_status_id`),
  KEY `fk_motorpass_account_file_export_id` (`file_export_id`),
  KEY `fk_motorpass_account_file_import_id` (`file_import_id`),
  CONSTRAINT `fk_motorpass_account_file_export_id` FOREIGN KEY (`file_export_id`) REFERENCES `FileExport` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_file_import_id` FOREIGN KEY (`file_import_id`) REFERENCES `FileImport` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_motorpass_account_status_id` FOREIGN KEY (`motorpass_account_status_id`) REFERENCES `motorpass_account_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_motorpass_business_structure_id` FOREIGN KEY (`motorpass_business_structure_id`) REFERENCES `motorpass_business_structure` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_motorpass_card_id` FOREIGN KEY (`motorpass_card_id`) REFERENCES `motorpass_card` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_motorpass_contact_id` FOREIGN KEY (`motorpass_contact_id`) REFERENCES `motorpass_contact` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_motorpass_promotion_code_id` FOREIGN KEY (`motorpass_promotion_code_id`) REFERENCES `motorpass_promotion_code` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_postal_address_id` FOREIGN KEY (`postal_address_id`) REFERENCES `motorpass_address` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_account_street_address_id` FOREIGN KEY (`street_address_id`) REFERENCES `motorpass_address` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_account`
--

LOCK TABLES `motorpass_account` WRITE;
/*!40000 ALTER TABLE `motorpass_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_account` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`rdavis`@`localhost`*/ /*!50003 TRIGGER `rebill_motorpass_account_name_and_number` AFTER UPDATE ON `motorpass_account`
 FOR EACH ROW UPDATE  rebill_motorpass rm
                                SET   rm.account_number = NEW.account_number,
                                    rm.account_name = NEW.account_name
                                WHERE rm.motorpass_account_id = NEW.id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `motorpass_account_status`
--

DROP TABLE IF EXISTS `motorpass_account_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_account_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(128) NOT NULL,
  `system_name` varchar(128) NOT NULL,
  `const_name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_account_status`
--

LOCK TABLES `motorpass_account_status` WRITE;
/*!40000 ALTER TABLE `motorpass_account_status` DISABLE KEYS */;
INSERT INTO `motorpass_account_status` VALUES (1,'Awaiting Dispatch','Awaiting Dispatch','AWAITING_DISPATCH','MOTORPASS_ACCOUNT_STATUS_AWAITING_DISPATCH'),(2,'Dispatched','Dispatched','DISPATCHED','MOTORPASS_ACCOUNT_STATUS_DISPATCHED'),(3,'Approved','Approved','APPROVED','MOTORPASS_ACCOUNT_STATUS_APPROVED'),(4,'Declined','Declined','DECLINED','MOTORPASS_ACCOUNT_STATUS_DECLINED'),(5,'Withdrawn','Withdrawn','WITHDRAWN','MOTORPASS_ACCOUNT_STATUS_WITHDRAWN');
/*!40000 ALTER TABLE `motorpass_account_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_address`
--

DROP TABLE IF EXISTS `motorpass_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `line_1` varchar(45) NOT NULL,
  `line_2` varchar(45) DEFAULT NULL,
  `suburb` varchar(45) NOT NULL,
  `state_id` bigint(20) unsigned NOT NULL,
  `postcode` varchar(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_address_state_id` (`state_id`),
  CONSTRAINT `fk_motorpass_address_state_id` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_address`
--

LOCK TABLES `motorpass_address` WRITE;
/*!40000 ALTER TABLE `motorpass_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_business_structure`
--

DROP TABLE IF EXISTS `motorpass_business_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_business_structure` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(128) NOT NULL,
  `system_name` varchar(128) NOT NULL,
  `const_name` varchar(128) NOT NULL,
  `code_numeric` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_business_structure`
--

LOCK TABLES `motorpass_business_structure` WRITE;
/*!40000 ALTER TABLE `motorpass_business_structure` DISABLE KEYS */;
INSERT INTO `motorpass_business_structure` VALUES (1,'Unlisted Pty Ltd','Unlisted Pty Ltd','UNLISTED_PTY_LTD','MOTORPASS_BUSINESS_STRUCTURE_UNLISTED_PTY_LTD',1),(2,'Listed Ltd Co','Listed Ltd Co','LISTED_LTD_CO','MOTORPASS_BUSINESS_STRUCTURE_LISTED_LTD_CO',2),(3,'Trust','Trust','TRUST','MOTORPASS_BUSINESS_STRUCTURE_TRUST',5),(4,'Partnership','Partnership','PARTNERSHIP','MOTORPASS_BUSINESS_STRUCTURE_PARTNERSHIP',6),(5,'Sole Trader','Sole Trader','SOLE_TRADER','MOTORPASS_BUSINESS_STRUCTURE_SOLE_TRADER',7),(6,'Govt Department','Govt Department','GOVT_DEPARTMENT','MOTORPASS_BUSINESS_STRUCTURE_GOVT_DEPARTMENT',8),(7,'Subsidiary of Foreign Co.','Subsidiary of Foreign Co.','SUBSIDIARY_OF_FOREIGN_CO','MOTORPASS_BUSINESS_STRUCTURE_SUBSIDIARY_OF_FOREIGN_CO',9),(8,'Association','Association','ASSOCIATION','MOTORPASS_BUSINESS_STRUCTURE_ASSOCIATION',10),(9,'Trustee','Trustee','TRUSTEE','MOTORPASS_BUSINESS_STRUCTURE_TRUSTEE',11),(10,'Trading Subsidiary','Trading Subsidiary','TRADING_SUBSIDIARY','MOTORPASS_BUSINESS_STRUCTURE_TRADING_SUBSIDIARY',12),(11,'Non Profit Organisation','Non Profit Organisation','NON_PROFIT_ORGANISATION','MOTORPASS_BUSINESS_STRUCTURE_NON_PROFIT_ORGANISATION',13),(12,'Incorporated Body','Incorporated Body','INCORPORATED_BODY','MOTORPASS_BUSINESS_STRUCTURE_INCORPORATED_BODY',14),(13,'Other','Other','OTHER','MOTORPASS_BUSINESS_STRUCTURE_OTHER',15);
/*!40000 ALTER TABLE `motorpass_business_structure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_card`
--

DROP TABLE IF EXISTS `motorpass_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_card` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `holder_contact_title_id` bigint(20) unsigned DEFAULT NULL,
  `holder_first_name` varchar(100) DEFAULT NULL,
  `holder_last_name` varchar(100) DEFAULT NULL,
  `shared` int(11) NOT NULL,
  `vehicle_model` varchar(45) DEFAULT NULL,
  `vehicle_rego` varchar(10) DEFAULT NULL,
  `vehicle_make` varchar(45) DEFAULT NULL,
  `motorpass_card_type_id` bigint(20) unsigned NOT NULL,
  `card_type_description` varchar(128) NOT NULL,
  `card_expiry_date` date NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_employee_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_card_motorpass_card_type_id` (`motorpass_card_type_id`),
  KEY `fk_card_holder_contact_title_id` (`holder_contact_title_id`),
  CONSTRAINT `fk_card_holder_contact_title_id` FOREIGN KEY (`holder_contact_title_id`) REFERENCES `contact_title` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_card_motorpass_card_type_id` FOREIGN KEY (`motorpass_card_type_id`) REFERENCES `motorpass_card_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_card`
--

LOCK TABLES `motorpass_card` WRITE;
/*!40000 ALTER TABLE `motorpass_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_card` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`rdavis`@`localhost`*/ /*!50003 TRIGGER `rebill_motorpass_card_expiry_date` AFTER UPDATE ON `motorpass_card`
 FOR EACH ROW UPDATE  rebill_motorpass rm
                                SET   rm.card_expiry_date = NEW.card_expiry_date
                                WHERE rm.motorpass_account_id IN (
                                      SELECT  ma.id
                                      FROM  motorpass_account ma
                                      WHERE ma.motorpass_card_id = NEW.id
                                    ) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `motorpass_card_type`
--

DROP TABLE IF EXISTS `motorpass_card_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_card_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(128) NOT NULL,
  `system_name` varchar(128) NOT NULL,
  `const_name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_card_type`
--

LOCK TABLES `motorpass_card_type` WRITE;
/*!40000 ALTER TABLE `motorpass_card_type` DISABLE KEYS */;
INSERT INTO `motorpass_card_type` VALUES (1,'All Products','All Products','ALL_PRODUCTS','MOTORPASS_CARD_TYPE_ALL_PRODUCTS'),(2,'Fuel Only','Fuel Only','FUEL_ONLY','MOTORPASS_CARD_TYPE_FUEL_ONLY'),(3,'Fuel And Oil Only','Fuel And Oil Only','FUEL_AND_OIL_ONLY','MOTORPASS_CARD_TYPE_FUEL_AND_OIL_ONLY'),(4,'All Vehicle Expenses','All Vehicle Expenses','ALL_VEHICLE_EXPENSES','MOTORPASS_CARD_TYPE_ALL_VEHICLE_EXPENSES'),(5,'Other','Other','OTHER','MOTORPASS_CARD_TYPE_OTHER');
/*!40000 ALTER TABLE `motorpass_card_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_contact`
--

DROP TABLE IF EXISTS `motorpass_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_contact` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contact_title_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `dob` date NOT NULL,
  `drivers_licence` varchar(20) DEFAULT NULL,
  `position` varchar(45) NOT NULL,
  `landline_number` varchar(25) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_employee_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_contact_contact_title_id` (`contact_title_id`),
  CONSTRAINT `fk_motorpass_contact_contact_title_id` FOREIGN KEY (`contact_title_id`) REFERENCES `contact_title` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_contact`
--

LOCK TABLES `motorpass_contact` WRITE;
/*!40000 ALTER TABLE `motorpass_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_promotion_code`
--

DROP TABLE IF EXISTS `motorpass_promotion_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_promotion_code` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(128) NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_promotion_code_status_id` (`status_id`),
  CONSTRAINT `fk_motorpass_promotion_code_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_promotion_code`
--

LOCK TABLES `motorpass_promotion_code` WRITE;
/*!40000 ALTER TABLE `motorpass_promotion_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_promotion_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_promotioncode_rateplan`
--

DROP TABLE IF EXISTS `motorpass_promotioncode_rateplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_promotioncode_rateplan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `motorpass_promotioncode_id` bigint(20) unsigned NOT NULL,
  `rateplan_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_promotioncode_rateplan_promotion_code` (`motorpass_promotioncode_id`),
  KEY `fk_motorpass_promotioncode_rateplan_rateplan` (`rateplan_id`),
  CONSTRAINT `fk_motorpass_promotioncode_rateplan_promotion_code` FOREIGN KEY (`motorpass_promotioncode_id`) REFERENCES `motorpass_promotion_code` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_promotioncode_rateplan_rateplan` FOREIGN KEY (`rateplan_id`) REFERENCES `RatePlan` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_promotioncode_rateplan`
--

LOCK TABLES `motorpass_promotioncode_rateplan` WRITE;
/*!40000 ALTER TABLE `motorpass_promotioncode_rateplan` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_promotioncode_rateplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motorpass_trade_reference`
--

DROP TABLE IF EXISTS `motorpass_trade_reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motorpass_trade_reference` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `motorpass_account_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `phone_number` varchar(25) NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_motorpass_trade_reference_motorpass_sale_id` (`motorpass_account_id`),
  KEY `fk_motorpass_trade_reference_status_id` (`status_id`),
  CONSTRAINT `fk_motorpass_trade_reference_motorpass_sale_id` FOREIGN KEY (`motorpass_account_id`) REFERENCES `motorpass_account` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorpass_trade_reference_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motorpass_trade_reference`
--

LOCK TABLES `motorpass_trade_reference` WRITE;
/*!40000 ALTER TABLE `motorpass_trade_reference` DISABLE KEYS */;
/*!40000 ALTER TABLE `motorpass_trade_reference` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation`
--

DROP TABLE IF EXISTS `operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description',
  `system_name` varchar(256) NOT NULL COMMENT 'System Name',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias',
  `is_assignable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1: Can be assigned in Flex; 0: Cannot be assigned in Flex',
  `flex_module_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Flex Module that contains this Operation',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Status',
  PRIMARY KEY (`id`),
  KEY `fk_operation_flex_module_id` (`flex_module_id`),
  KEY `fk_operation_status_id` (`status_id`),
  CONSTRAINT `fk_operation_flex_module_id` FOREIGN KEY (`flex_module_id`) REFERENCES `flex_module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_operation_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation`
--

LOCK TABLES `operation` WRITE;
/*!40000 ALTER TABLE `operation` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_prerequisite`
--

DROP TABLE IF EXISTS `operation_prerequisite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operation_prerequisite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `operation_id` int(10) unsigned NOT NULL COMMENT '(FK) Dependent Operation',
  `prerequisite_operation_id` int(10) unsigned NOT NULL COMMENT '(FK) Prerequisite Operation',
  PRIMARY KEY (`id`),
  KEY `fk_operation_prerequisite_operation_id` (`operation_id`),
  KEY `fk_operation_prerequisite_prerequisite_operation_id` (`prerequisite_operation_id`),
  CONSTRAINT `fk_operation_prerequisite_operation_id` FOREIGN KEY (`operation_id`) REFERENCES `operation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_operation_prerequisite_prerequisite_operation_id` FOREIGN KEY (`prerequisite_operation_id`) REFERENCES `operation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_prerequisite`
--

LOCK TABLES `operation_prerequisite` WRITE;
/*!40000 ALTER TABLE `operation_prerequisite` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation_prerequisite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_profile`
--

DROP TABLE IF EXISTS `operation_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operation_profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description',
  `status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Status',
  PRIMARY KEY (`id`),
  KEY `fk_operation_profile_status_id` (`status_id`),
  CONSTRAINT `fk_operation_profile_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_profile`
--

LOCK TABLES `operation_profile` WRITE;
/*!40000 ALTER TABLE `operation_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_profile_children`
--

DROP TABLE IF EXISTS `operation_profile_children`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operation_profile_children` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `parent_operation_profile_id` int(10) unsigned NOT NULL COMMENT '(FK) Parent Operation Profile',
  `child_operation_profile_id` int(10) unsigned NOT NULL COMMENT '(FK) Child Operation Profile',
  PRIMARY KEY (`id`),
  KEY `fk_operation_profile_children_parent_operation_profile_id` (`parent_operation_profile_id`),
  KEY `fk_operation_profile_children_child_operation_profile_id` (`child_operation_profile_id`),
  CONSTRAINT `fk_operation_profile_children_child_operation_profile_id` FOREIGN KEY (`child_operation_profile_id`) REFERENCES `operation_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_operation_profile_children_parent_operation_profile_id` FOREIGN KEY (`parent_operation_profile_id`) REFERENCES `operation_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_profile_children`
--

LOCK TABLES `operation_profile_children` WRITE;
/*!40000 ALTER TABLE `operation_profile_children` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation_profile_children` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_profile_operation`
--

DROP TABLE IF EXISTS `operation_profile_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operation_profile_operation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `operation_profile_id` int(10) unsigned NOT NULL COMMENT '(FK) Operation Profile',
  `operation_id` int(10) unsigned NOT NULL COMMENT '(FK) Operation',
  PRIMARY KEY (`id`),
  KEY `fk_operation_profile_operation_operation_profile_id` (`operation_profile_id`),
  KEY `fk_operation_profile_operation_operation_id` (`operation_id`),
  CONSTRAINT `fk_operation_profile_operation_operation_id` FOREIGN KEY (`operation_id`) REFERENCES `operation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_operation_profile_operation_operation_profile_id` FOREIGN KEY (`operation_profile_id`) REFERENCES `operation_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_profile_operation`
--

LOCK TABLES `operation_profile_operation` WRITE;
/*!40000 ALTER TABLE `operation_profile_operation` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation_profile_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `carrier_id` bigint(20) DEFAULT NULL,
  `created_datetime` datetime DEFAULT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  `paid_date` date NOT NULL,
  `payment_type_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_reference` varchar(128) DEFAULT NULL,
  `payment_nature_id` int(10) unsigned NOT NULL,
  `amount` decimal(13,4) unsigned NOT NULL,
  `balance` decimal(13,4) unsigned NOT NULL,
  `surcharge_charge_id` bigint(20) unsigned DEFAULT NULL,
  `latest_payment_response_id` bigint(20) unsigned DEFAULT NULL,
  `reversed_payment_id` bigint(20) unsigned DEFAULT NULL,
  `payment_reversal_type_id` int(10) unsigned DEFAULT NULL,
  `payment_reversal_reason_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_payment_created_datetime` (`created_datetime`),
  KEY `in_payment_paid_date` (`paid_date`),
  KEY `fk_payment_tbl_account_id` (`account_id`),
  KEY `fk_payment_tbl_payment_type_id` (`payment_type_id`),
  KEY `fk_payment_tbl_surcharge_charge_id` (`surcharge_charge_id`),
  KEY `fk_payment_tbl_created_employee_id` (`created_employee_id`),
  KEY `fk_payment_tbl_latest_payment_response_id` (`latest_payment_response_id`),
  KEY `fk_payment_tbl_carrier_id` (`carrier_id`),
  KEY `fk_payment_tbl_payment_nature_id` (`payment_nature_id`),
  KEY `fk_payment_tbl_payment_reversal_type_id` (`payment_reversal_type_id`),
  KEY `fk_payment_tbl_payment_reversal_reason_id` (`payment_reversal_reason_id`),
  KEY `fk_payment_tbl_reversed_payment_id` (`reversed_payment_id`),
  CONSTRAINT `fk_payment_tbl_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_carrier_id` FOREIGN KEY (`carrier_id`) REFERENCES `Carrier` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_latest_payment_response_id` FOREIGN KEY (`latest_payment_response_id`) REFERENCES `payment_response` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_payment_nature_id` FOREIGN KEY (`payment_nature_id`) REFERENCES `payment_nature` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_payment_reversal_reason_id` FOREIGN KEY (`payment_reversal_reason_id`) REFERENCES `payment_reversal_reason` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_payment_reversal_type_id` FOREIGN KEY (`payment_reversal_type_id`) REFERENCES `payment_reversal_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_payment_type_id` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_reversed_payment_id` FOREIGN KEY (`reversed_payment_id`) REFERENCES `payment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_tbl_surcharge_charge_id` FOREIGN KEY (`surcharge_charge_id`) REFERENCES `Charge` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_method`
--

DROP TABLE IF EXISTS `payment_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_method` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Payment Method',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Payment Method',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias of the Payment Method',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_method`
--

LOCK TABLES `payment_method` WRITE;
/*!40000 ALTER TABLE `payment_method` DISABLE KEYS */;
INSERT INTO `payment_method` VALUES (1,'Account','Account Billing','PAYMENT_METHOD_ACCOUNT'),(2,'Direct Debit','Direct Debit','PAYMENT_METHOD_DIRECT_DEBIT'),(3,'Rebill','Rebill','PAYMENT_METHOD_REBILL');
/*!40000 ALTER TABLE `payment_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_nature`
--

DROP TABLE IF EXISTS `payment_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_nature` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  `value_multiplier` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_nature`
--

LOCK TABLES `payment_nature` WRITE;
/*!40000 ALTER TABLE `payment_nature` DISABLE KEYS */;
INSERT INTO `payment_nature` VALUES (1,'Payment','Payment','PAYMENT','PAYMENT_NATURE_PAYMENT',-1),(2,'Reversal','Reversal','REVERSAL','PAYMENT_NATURE_REVERSAL',1);
/*!40000 ALTER TABLE `payment_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_request`
--

DROP TABLE IF EXISTS `payment_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_request` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account. The Account that the request is for',
  `amount` decimal(13,4) NOT NULL COMMENT 'The amount of the payment which is being requested',
  `payment_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) payment_type. The type of payment which is being requested',
  `payment_request_status_id` int(11) NOT NULL COMMENT '(FK) payment_request_status. The status of the request',
  `invoice_run_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) InvoiceRun. An optional Invoice Run that the payment request originated from',
  `file_export_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) FileExport. The file export record that shows details of the request post-export',
  `payment_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Payment. The (optional) payment that this request is associated with',
  `created_datetime` datetime NOT NULL COMMENT 'When the record was created',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee. The Employee that created the record',
  PRIMARY KEY (`id`),
  KEY `fk_payment_request_account_id` (`account_id`),
  KEY `fk_payment_request_payment_type_id` (`payment_type_id`),
  KEY `fk_payment_request_payment_request_status_id` (`payment_request_status_id`),
  KEY `fk_payment_request_invoice_run_id` (`invoice_run_id`),
  KEY `fk_payment_request_file_export_id` (`file_export_id`),
  KEY `fk_payment_request_created_employee_id` (`created_employee_id`),
  KEY `fk_payment_request_payment_v2_id` (`payment_id`),
  CONSTRAINT `fk_payment_request_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_file_export_id` FOREIGN KEY (`file_export_id`) REFERENCES `FileExport` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_invoice_run_id` FOREIGN KEY (`invoice_run_id`) REFERENCES `InvoiceRun` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_payment_request_status_id` FOREIGN KEY (`payment_request_status_id`) REFERENCES `payment_request_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_payment_type_id` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_payment_v2_id` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='A payment request, to be dispatched as soon as possible';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_request`
--

LOCK TABLES `payment_request` WRITE;
/*!40000 ALTER TABLE `payment_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_request_collection_promise_instalment`
--

DROP TABLE IF EXISTS `payment_request_collection_promise_instalment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_request_collection_promise_instalment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_request_id` bigint(20) unsigned NOT NULL,
  `collection_promise_instalment_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payment_request_promise_instalment_payment_request_id` (`payment_request_id`),
  KEY `fk_payment_request_promise_instalment_promise_instalment_id` (`collection_promise_instalment_id`),
  CONSTRAINT `fk_payment_request_promise_instalment_payment_request_id` FOREIGN KEY (`payment_request_id`) REFERENCES `payment_request` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_promise_instalment_promise_instalment_id` FOREIGN KEY (`collection_promise_instalment_id`) REFERENCES `collection_promise_instalment` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_request_collection_promise_instalment`
--

LOCK TABLES `payment_request_collection_promise_instalment` WRITE;
/*!40000 ALTER TABLE `payment_request_collection_promise_instalment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_request_collection_promise_instalment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_request_invoice`
--

DROP TABLE IF EXISTS `payment_request_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_request_invoice` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_request_id` bigint(20) unsigned NOT NULL,
  `invoice_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payment_request_invoice_payment_request_id` (`payment_request_id`),
  KEY `fk_payment_request_invoice_invoice_id` (`invoice_id`),
  CONSTRAINT `fk_payment_request_invoice_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `Invoice` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_request_invoice_payment_request_id` FOREIGN KEY (`payment_request_id`) REFERENCES `payment_request` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_request_invoice`
--

LOCK TABLES `payment_request_invoice` WRITE;
/*!40000 ALTER TABLE `payment_request_invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_request_invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_request_status`
--

DROP TABLE IF EXISTS `payment_request_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_request_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the status',
  `description` varchar(128) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(128) NOT NULL COMMENT 'Constant alias for the status',
  `system_name` varchar(128) NOT NULL COMMENT 'System name for the status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='The status of a payment request';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_request_status`
--

LOCK TABLES `payment_request_status` WRITE;
/*!40000 ALTER TABLE `payment_request_status` DISABLE KEYS */;
INSERT INTO `payment_request_status` VALUES (1,'Pending','Request is awaiting dispatch','PAYMENT_REQUEST_STATUS_PENDING','PENDING'),(2,'Dispatched','Request has been dispatched','PAYMENT_REQUEST_STATUS_DISPATCHED','DISPATCHED'),(3,'Cancelled','Request has been cancelled, will not be dispatched','PAYMENT_REQUEST_STATUS_CANCELLED','CANCELLED');
/*!40000 ALTER TABLE `payment_request_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_response`
--

DROP TABLE IF EXISTS `payment_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_response` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_group_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) AccountGroup',
  `account_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Account',
  `paid_date` date DEFAULT NULL COMMENT 'Effective date of the payment',
  `payment_type_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) payment_type',
  `amount` decimal(13,4) DEFAULT NULL COMMENT 'Amount in dollars',
  `file_import_data_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) file_import_data, (optional) Raw payment data',
  `transaction_reference` varchar(256) DEFAULT NULL COMMENT 'Transaction reference for the payment',
  `payment_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) Payment. The (optional) payment that this response is associated with',
  `payment_response_type_id` int(11) NOT NULL COMMENT '(FK) payment_response_type',
  `payment_response_status_id` int(11) NOT NULL COMMENT '(FK) payment_response_status',
  `created_datetime` datetime NOT NULL COMMENT 'Timestamp for creation',
  `payment_reversal_type_id` int(10) unsigned DEFAULT NULL,
  `payment_reversal_reason_id` int(10) unsigned DEFAULT NULL,
  `origin_id` int(10) unsigned DEFAULT NULL COMMENT 'Reference to the origin of the payment, e.g. CreditCard or DirectDebit id',
  PRIMARY KEY (`id`),
  KEY `fk_payment_response_account_group_id` (`account_group_id`),
  KEY `fk_payment_response_account_id` (`account_id`),
  KEY `fk_payment_response_payment_type_id` (`payment_type_id`),
  KEY `fk_payment_response_file_import_data_id` (`file_import_data_id`),
  KEY `fk_payment_response_payment_response_type_id` (`payment_response_type_id`),
  KEY `fk_payment_response_payment_response_status_id` (`payment_response_status_id`),
  KEY `fk_payment_response_payment_reversal_type_id` (`payment_reversal_type_id`),
  KEY `fk_payment_response_payment_reversal_reason_id` (`payment_reversal_reason_id`),
  KEY `fk_payment_response_payment_v2_id` (`payment_id`),
  CONSTRAINT `fk_payment_response_account_group_id` FOREIGN KEY (`account_group_id`) REFERENCES `AccountGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_file_import_data_id` FOREIGN KEY (`file_import_data_id`) REFERENCES `file_import_data` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_payment_response_status_id` FOREIGN KEY (`payment_response_status_id`) REFERENCES `payment_response_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_payment_response_type_id` FOREIGN KEY (`payment_response_type_id`) REFERENCES `payment_response_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_payment_reversal_reason_id` FOREIGN KEY (`payment_reversal_reason_id`) REFERENCES `payment_reversal_reason` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_payment_reversal_type_id` FOREIGN KEY (`payment_reversal_type_id`) REFERENCES `payment_reversal_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_payment_type_id` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_response_payment_v2_id` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_response`
--

LOCK TABLES `payment_response` WRITE;
/*!40000 ALTER TABLE `payment_response` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_response` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_response_status`
--

DROP TABLE IF EXISTS `payment_response_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_response_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the status',
  `description` varchar(128) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(128) NOT NULL COMMENT 'Constant alias for the status',
  `system_name` varchar(128) NOT NULL COMMENT 'System name for the status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_response_status`
--

LOCK TABLES `payment_response_status` WRITE;
/*!40000 ALTER TABLE `payment_response_status` DISABLE KEYS */;
INSERT INTO `payment_response_status` VALUES (1,'Imported','The response has been imported','PAYMENT_RESPONSE_STATUS_IMPORTED','IMPORTED'),(2,'Processed','The response has been processed','PAYMENT_RESPONSE_STATUS_PROCESSED','PROCESSED'),(3,'Processing Failed','The response failed to be processed','PAYMENT_RESPONSE_STATUS_PROCESSING_FAILED','PROCESSING_FAILED');
/*!40000 ALTER TABLE `payment_response_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_response_type`
--

DROP TABLE IF EXISTS `payment_response_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_response_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name of the type',
  `description` varchar(128) NOT NULL COMMENT 'Description of the type',
  `const_name` varchar(128) NOT NULL COMMENT 'Constant alias for the type',
  `system_name` varchar(128) NOT NULL COMMENT 'System name for the type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_response_type`
--

LOCK TABLES `payment_response_type` WRITE;
/*!40000 ALTER TABLE `payment_response_type` DISABLE KEYS */;
INSERT INTO `payment_response_type` VALUES (1,'Confirmation','Payment confirmed/settled/completed','PAYMENT_RESPONSE_TYPE_CONFIRMATION','CONFIRMATION'),(2,'Rejection','Payment rejected','PAYMENT_RESPONSE_TYPE_REJECTION','REJECTION');
/*!40000 ALTER TABLE `payment_response_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_reversal_reason`
--

DROP TABLE IF EXISTS `payment_reversal_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_reversal_reason` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) DEFAULT NULL,
  `payment_reversal_type_id` int(10) unsigned NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payment_reversal_reason_payment_reversal_type_id` (`payment_reversal_type_id`),
  KEY `fk_payment_reversal_reason_status_id` (`status_id`),
  CONSTRAINT `fk_payment_reversal_reason_payment_reversal_type_id` FOREIGN KEY (`payment_reversal_type_id`) REFERENCES `payment_reversal_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_reversal_reason_status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_reversal_reason`
--

LOCK TABLES `payment_reversal_reason` WRITE;
/*!40000 ALTER TABLE `payment_reversal_reason` DISABLE KEYS */;
INSERT INTO `payment_reversal_reason` VALUES (1,'Agent Reversal','Payment reversed by an Agent','AGENT_REVERSAL',1,1),(2,'Dishonour Reversal','Payment Dishonoured','DISHONOUR_REVERSAL',2,1),(3,'Overpayment Refund','Overpayment Refund',NULL,1,1);
/*!40000 ALTER TABLE `payment_reversal_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_reversal_type`
--

DROP TABLE IF EXISTS `payment_reversal_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_reversal_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_reversal_type`
--

LOCK TABLES `payment_reversal_type` WRITE;
/*!40000 ALTER TABLE `payment_reversal_type` DISABLE KEYS */;
INSERT INTO `payment_reversal_type` VALUES (1,'Agent','Reversed by an Agent','AGENT','PAYMENT_REVERSAL_TYPE_AGENT'),(2,'Dishonour','Reversed because of dishonoured payment','DISHONOUR','PAYMENT_REVERSAL_TYPE_DISHONOUR');
/*!40000 ALTER TABLE `payment_reversal_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_status`
--

DROP TABLE IF EXISTS `payment_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Payment Status',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Payment Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Payment Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_status`
--

LOCK TABLES `payment_status` WRITE;
/*!40000 ALTER TABLE `payment_status` DISABLE KEYS */;
INSERT INTO `payment_status` VALUES (100,'Imported','Imported','PAYMENT_IMPORTED'),(101,'Waiting','Waiting','PAYMENT_WAITING'),(103,'Paying','Paying','PAYMENT_PAYING'),(150,'Finished','Finished','PAYMENT_FINISHED'),(200,'Import Failed','Import Failed','PAYMENT_BAD_IMPORT'),(201,'Processing Failed','Processing Failed','PAYMENT_BAD_PROCESS'),(202,'Normalisation Failed','Normalisation Failed','PAYMENT_BAD_NORMALISE'),(203,'Header','File Header (Ignored)','PAYMENT_CANT_NORMALISE_HEADER'),(204,'Footer','File Footer (Ignored)','PAYMENT_CANT_NORMALISE_FOOTER'),(205,'Invalid','Invalid Data','PAYMENT_CANT_NORMALISE_INVALID'),(206,'Delinquent','Delinquent','PAYMENT_BAD_OWNER'),(207,'Invalid Check Digit','Invalid Check Digit','PAYMENT_INVALID_CHECK_DIGIT'),(250,'Reversed','Reversed','PAYMENT_REVERSED');
/*!40000 ALTER TABLE `payment_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_terms`
--

DROP TABLE IF EXISTS `payment_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_terms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_day` smallint(5) unsigned NOT NULL COMMENT 'Day of month on which to invoice',
  `payment_terms` smallint(5) unsigned NOT NULL COMMENT 'Number of days after invoicing when payment becomes due',
  `minimum_balance_to_pursue` decimal(4,2) unsigned NOT NULL DEFAULT '27.01' COMMENT 'The minimum balance required for automatic notice generation to be applied',
  `late_payment_fee` decimal(4,2) NOT NULL DEFAULT '17.27' COMMENT 'The late payment fee charged, excluding GST',
  `employee` bigint(20) unsigned DEFAULT NULL COMMENT 'Employee who effected the change',
  `created` datetime NOT NULL COMMENT 'Date/Time at which the payment terms were created',
  `direct_debit_days` smallint(6) NOT NULL COMMENT 'Number of days after invoicing that Direct Debits will be applied',
  `direct_debit_minimum` decimal(4,2) NOT NULL COMMENT 'Minimum Debt in order to be Direct Debited',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'FK to CustomerGroup table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='System-wide payment terms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_terms`
--

LOCK TABLES `payment_terms` WRITE;
/*!40000 ALTER TABLE `payment_terms` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transaction_data`
--

DROP TABLE IF EXISTS `payment_transaction_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_transaction_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `value` varchar(1024) NOT NULL,
  `data_type_id` bigint(20) unsigned NOT NULL,
  `payment_id` bigint(20) unsigned DEFAULT NULL,
  `payment_response_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payment_transaction_data_data_type_id` (`data_type_id`),
  KEY `fk_payment_transaction_data_payment_id` (`payment_id`),
  KEY `fk_payment_transaction_data_payment_response_id` (`payment_response_id`),
  CONSTRAINT `fk_payment_transaction_data_data_type_id` FOREIGN KEY (`data_type_id`) REFERENCES `data_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_transaction_data_payment_id` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_transaction_data_payment_response_id` FOREIGN KEY (`payment_response_id`) REFERENCES `payment_response` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transaction_data`
--

LOCK TABLES `payment_transaction_data` WRITE;
/*!40000 ALTER TABLE `payment_transaction_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_transaction_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_type`
--

DROP TABLE IF EXISTS `payment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Payment Status',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Payment Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Payment Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_type`
--

LOCK TABLES `payment_type` WRITE;
/*!40000 ALTER TABLE `payment_type` DISABLE KEYS */;
INSERT INTO `payment_type` VALUES (1,'BillExpress','BillExpress','PAYMENT_TYPE_BILLEXPRESS'),(2,'BPAY','BPAY','PAYMENT_TYPE_BPAY'),(3,'Cheque','Cheque','PAYMENT_TYPE_CHEQUE'),(4,'SecurePay','SecurePay','PAYMENT_TYPE_SECUREPAY'),(5,'Credit Card','Credit Card','PAYMENT_TYPE_CREDIT_CARD'),(6,'EFT','EFT','PAYMENT_TYPE_EFT'),(7,'Cash','Cash','PAYMENT_TYPE_CASH'),(8,'Debt Collector','Debt Collector','PAYMENT_TYPE_AUSTRAL'),(9,'Contra','Contra','PAYMENT_TYPE_CONTRA'),(10,'Bank Transfer','Bank Transfer','PAYMENT_TYPE_BANK_TRANSFER'),(11,'Rebill Payout','Rebill Payout','PAYMENT_TYPE_REBILL_PAYOUT'),(12,'Direct Debit via EFT','Direct Debit via EFT','PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT'),(13,'Direct Debit via Credit Card','Direct Debit via Credit Card','PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD');
/*!40000 ALTER TABLE `payment_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Indentifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Product',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description of the Product',
  `product_type_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Type of the Product',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT '(FK) Customer Group this Product belongs to',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Employee who created the Product',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp for the Product',
  `product_sale_priority_id` bigint(20) unsigned NOT NULL COMMENT '(FK) How actively the Product is being sold',
  `product_status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Status of the Product',
  PRIMARY KEY (`id`),
  KEY `fk_product_product_type_id` (`product_type_id`),
  KEY `fk_product_customer_group_id` (`customer_group_id`),
  KEY `fk_product_employee_id` (`employee_id`),
  KEY `fk_product_product_sale_priority_id` (`product_sale_priority_id`),
  KEY `fk_product_product_status_id` (`product_status_id`),
  CONSTRAINT `fk_product_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_product_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_product_product_sale_priority_id` FOREIGN KEY (`product_sale_priority_id`) REFERENCES `product_sale_priority` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_product_product_status_id` FOREIGN KEY (`product_status_id`) REFERENCES `product_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_product_product_type_id` FOREIGN KEY (`product_type_id`) REFERENCES `product_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sale_priority`
--

DROP TABLE IF EXISTS `product_sale_priority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_sale_priority` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Indentifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Product Priority',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Product Priority',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name of the Product Priority',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sale_priority`
--

LOCK TABLES `product_sale_priority` WRITE;
/*!40000 ALTER TABLE `product_sale_priority` DISABLE KEYS */;
INSERT INTO `product_sale_priority` VALUES (1,'Active','Actively Sold','PRODUCT_SALE_PRIORITY_ACTIVE'),(2,'Passive','Passively Sold','PRODUCT_SALE_PRIORITY_PASSIVE');
/*!40000 ALTER TABLE `product_sale_priority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_status`
--

DROP TABLE IF EXISTS `product_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Indentifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Product Status',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Product Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name of the Product Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_status`
--

LOCK TABLES `product_status` WRITE;
/*!40000 ALTER TABLE `product_status` DISABLE KEYS */;
INSERT INTO `product_status` VALUES (1,'Draft','Draft','PRODUCT_STATUS_DRAFT'),(2,'Active','Active','PRODUCT_STATUS_ACTIVE'),(3,'Inactive','Inactive','PRODUCT_STATUS_INACTIVE');
/*!40000 ALTER TABLE `product_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_type`
--

DROP TABLE IF EXISTS `product_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Indentifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Product Type',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Product Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name of the Product Type',
  `product_type_nature_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Nature of this Product Type',
  PRIMARY KEY (`id`),
  KEY `fk_product_type_product_type_nature_id` (`product_type_nature_id`),
  CONSTRAINT `fk_product_type_product_type_nature_id` FOREIGN KEY (`product_type_nature_id`) REFERENCES `product_type_nature` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_type`
--

LOCK TABLES `product_type` WRITE;
/*!40000 ALTER TABLE `product_type` DISABLE KEYS */;
INSERT INTO `product_type` VALUES (1,'Landline','Landline','PRODUCT_TYPE_LANDLINE',1),(2,'ADSL','ADSL','PRODUCT_TYPE_ADSL',1),(3,'Wireless','Wireless Broadband','PRODUCT_TYPE_WIRELESS',1),(4,'Mobile','Mobile','PRODUCT_TYPE_MOBILE',1),(5,'Inbound','Inbound','PRODUCT_TYPE_INBOUND',1);
/*!40000 ALTER TABLE `product_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_type_nature`
--

DROP TABLE IF EXISTS `product_type_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_type_nature` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Indentifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Product Type Nature',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Product Type Nature',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name of the Product Type Nature',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_type_nature`
--

LOCK TABLES `product_type_nature` WRITE;
/*!40000 ALTER TABLE `product_type_nature` DISABLE KEYS */;
INSERT INTO `product_type_nature` VALUES (1,'Service','Service','PRODUCT_TYPE_NATURE_SERVICE'),(2,'Hardware','Hardware','PRODUCT_TYPE_NATURE_HARDWARE');
/*!40000 ALTER TABLE `product_type_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provisioning_request_status`
--

DROP TABLE IF EXISTS `provisioning_request_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provisioning_request_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Request Status',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Request Status',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Request Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Request Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=310 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provisioning_request_status`
--

LOCK TABLES `provisioning_request_status` WRITE;
/*!40000 ALTER TABLE `provisioning_request_status` DISABLE KEYS */;
INSERT INTO `provisioning_request_status` VALUES (300,'Awaiting Dispatch','Awaiting Dispatch','REQUEST_STATUS_WAITING'),(301,'Pending','Pending','REQUEST_STATUS_PENDING'),(302,'Rejected by Carrier','Rejected by Carrier','REQUEST_STATUS_REJECTED'),(303,'Completed','Completed','REQUEST_STATUS_COMPLETED'),(304,'Cancelled','Cancelled','REQUEST_STATUS_CANCELLED'),(305,'Duplicated','Duplicated (Ignored)','REQUEST_STATUS_DUPLICATE'),(306,'Exporting','Currently Exporting','REQUEST_STATUS_EXPORTING'),(307,'Delivered','Awaiting Carrier Response','REQUEST_STATUS_DELIVERED'),(308,'Not Supported by Flex','Request Not Supported by Flex','REQUEST_STATUS_NO_MODULE'),(309,'Rejected by Flex','Rejected by Flex','REQUEST_STATUS_REJECTED_FLEX');
/*!40000 ALTER TABLE `provisioning_request_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provisioning_response_status`
--

DROP TABLE IF EXISTS `provisioning_response_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provisioning_response_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Response Status',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Response Status',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Response Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Response Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provisioning_response_status`
--

LOCK TABLES `provisioning_response_status` WRITE;
/*!40000 ALTER TABLE `provisioning_response_status` DISABLE KEYS */;
INSERT INTO `provisioning_response_status` VALUES (400,'Unable to Normalise','Unable to Normalise','RESPONSE_STATUS_CANT_NORMALISE'),(401,'Unable to Find Owner','Unable to Find Owner','RESPONSE_STATUS_BAD_OWNER'),(402,'Imported','Successfully Imported','RESPONSE_STATUS_IMPORTED'),(403,'Redundant','Redundant','RESPONSE_STATUS_REDUNDANT'),(404,'Duplicate','Duplicate','RESPONSE_STATUS_DUPLICATE');
/*!40000 ALTER TABLE `provisioning_response_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provisioning_type`
--

DROP TABLE IF EXISTS `provisioning_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provisioning_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Name of provisioning type',
  `inbound` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether or not inbound messaging is supported',
  `outbound` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether or not outbound messaging is supported',
  `provisioning_type_nature` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Description of provisioning type',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=918 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provisioning_type`
--

LOCK TABLES `provisioning_type` WRITE;
/*!40000 ALTER TABLE `provisioning_type` DISABLE KEYS */;
INSERT INTO `provisioning_type` VALUES (900,'Full Service',1,1,1,'Full service request','PROVISIONING_TYPE_FULL_SERVICE'),(901,'Pre-Selection',1,1,2,'Pre-selection request','PROVISIONING_TYPE_PRESELECTION'),(902,'Bar',1,1,2,'Bar request','PROVISIONING_TYPE_BAR'),(903,'Unbar',1,1,2,'Unbar request','PROVISIONING_TYPE_UNBAR'),(904,'Activation',1,1,2,'Activation request','PROVISIONING_TYPE_ACTIVATION'),(905,'Deactivation',1,1,2,'Deactivation request','PROVISIONING_TYPE_DEACTIVATION'),(906,'Pre-Selection Reverse',1,1,2,'Pre-selection reverse request','PROVISIONING_TYPE_PRESELECTION_REVERSE'),(907,'Full Service Reverse',1,1,1,'Full service reverse request','PROVISIONING_TYPE_FULL_SERVICE_REVERSE'),(908,'Temporary Disconnection',0,0,1,'Temporary Disconnection','PROVISIONING_TYPE_DISCONNECT_TEMPORARY'),(909,'Temporary Disconnection Reversal',0,0,1,'Temporary Disconnection Reversal','PROVISIONING_TYPE_RECONNECT_TEMPORARY'),(910,'Full Service Lost (Churned)',1,0,1,'Full Service Lost (Churned)','PROVISIONING_TYPE_LOSS_FULL'),(911,'Preselection Lost (Churned)',1,0,2,'Preselection Lost (Churned)','PROVISIONING_TYPE_LOSS_PRESELECT'),(912,'Address Change',1,0,1,'Address Change','PROVISIONING_TYPE_CHANGE_ADDRESS'),(913,'Virtual Pre-Selection',1,1,2,'Virtual pre-selection request','PROVISIONING_TYPE_VIRTUAL_PRESELECTION'),(914,'Virtual Pre-Selection Reverse',1,1,2,'Virtual pre-selection reverse request','PROVISIONING_TYPE_VIRTUAL_PRESELECTION_REVERSE'),(915,'Virtual Preselection Lost',1,0,2,'Virtual Preselection Lost','PROVISIONING_TYPE_LOSS_VIRTUAL_PRESELECTION'),(916,'Full Service Lost (Disconnected)',1,0,1,'Full Service Lost (Disconnected)','PROVISIONING_TYPE_DISCONNECT_FULL'),(917,'Preselection Lost (Disconnected)',1,0,2,'Preselection Lost (Disconnected)','PROVISIONING_TYPE_DISCONNECT_PRESELECT');
/*!40000 ALTER TABLE `provisioning_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provisioning_type_nature`
--

DROP TABLE IF EXISTS `provisioning_type_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provisioning_type_nature` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Nature',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Nature',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Nature',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Nature',
  `service_type` bigint(20) NOT NULL COMMENT 'Service Type that this Nature corresponds to',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provisioning_type_nature`
--

LOCK TABLES `provisioning_type_nature` WRITE;
/*!40000 ALTER TABLE `provisioning_type_nature` DISABLE KEYS */;
INSERT INTO `provisioning_type_nature` VALUES (1,'Full Service','Land Line: Full Service','REQUEST_TYPE_NATURE_FULL_SERVICE',102),(2,'Preselection','Land Line: Preselection','REQUEST_TYPE_NATURE_PRESELECTION',102),(3,'Mobile','Mobile','REQUEST_TYPE_NATURE_MOBILE',101),(4,'Inbound','Inbound','REQUEST_TYPE_NATURE_INBOUND',103),(5,'ADSL','ADSL','REQUEST_TYPE_NATURE_ADSL',100);
/*!40000 ALTER TABLE `provisioning_type_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_class`
--

DROP TABLE IF EXISTS `rate_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_class` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(128) NOT NULL COMMENT 'Name for the Rate Class',
  `description` varchar(256) NOT NULL COMMENT 'Description of the Rate Class',
  `invoice_code` varchar(30) DEFAULT NULL COMMENT 'Code to identify Usage in this Rate Class on the Invoice',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_class`
--

LOCK TABLES `rate_class` WRITE;
/*!40000 ALTER TABLE `rate_class` DISABLE KEYS */;
/*!40000 ALTER TABLE `rate_class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_plan_discount`
--

DROP TABLE IF EXISTS `rate_plan_discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_plan_discount` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `rate_plan_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Rate Plan',
  `discount_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Discount',
  PRIMARY KEY (`id`),
  KEY `fk_rate_plan_discount_rate_plan_id` (`rate_plan_id`),
  KEY `fk_rate_plan_discount_discount_id` (`discount_id`),
  CONSTRAINT `fk_rate_plan_discount_discount_id` FOREIGN KEY (`discount_id`) REFERENCES `discount` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_plan_discount_rate_plan_id` FOREIGN KEY (`rate_plan_id`) REFERENCES `RatePlan` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_plan_discount`
--

LOCK TABLES `rate_plan_discount` WRITE;
/*!40000 ALTER TABLE `rate_plan_discount` DISABLE KEYS */;
/*!40000 ALTER TABLE `rate_plan_discount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rebill`
--

DROP TABLE IF EXISTS `rebill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rebill` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Account',
  `rebill_type_id` int(10) unsigned NOT NULL COMMENT '(FK) Rebill Type',
  `created_employee_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Employee who created the Rebill definition',
  `created_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp the Rebill definition was created',
  PRIMARY KEY (`id`),
  KEY `fk_rebill_account_id` (`account_id`),
  KEY `fk_rebill_rebill_type_id` (`rebill_type_id`),
  KEY `fk_rebill_created_employee_id` (`created_employee_id`),
  CONSTRAINT `fk_rebill_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rebill_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rebill_rebill_type_id` FOREIGN KEY (`rebill_type_id`) REFERENCES `rebill_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rebill`
--

LOCK TABLES `rebill` WRITE;
/*!40000 ALTER TABLE `rebill` DISABLE KEYS */;
/*!40000 ALTER TABLE `rebill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rebill_motorpass`
--

DROP TABLE IF EXISTS `rebill_motorpass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rebill_motorpass` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `rebill_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Rebill record this defines',
  `account_number` int(11) DEFAULT NULL,
  `account_name` varchar(256) DEFAULT NULL,
  `card_expiry_date` date DEFAULT NULL,
  `motorpass_account_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_rebill_motorpass_rebill_id` (`rebill_id`),
  KEY `fk_rebill_motorpass_motorpass_account_id` (`motorpass_account_id`),
  CONSTRAINT `fk_rebill_motorpass_motorpass_account_id` FOREIGN KEY (`motorpass_account_id`) REFERENCES `motorpass_account` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_rebill_motorpass_rebill_id` FOREIGN KEY (`rebill_id`) REFERENCES `rebill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rebill_motorpass`
--

LOCK TABLES `rebill_motorpass` WRITE;
/*!40000 ALTER TABLE `rebill_motorpass` DISABLE KEYS */;
/*!40000 ALTER TABLE `rebill_motorpass` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`rdavis`@`localhost`*/ /*!50003 TRIGGER `rebill_motorpass_insert` BEFORE INSERT ON `rebill_motorpass`
 FOR EACH ROW BEGIN
                                DECLARE acc_num INTEGER;
                                DECLARE expiry DATE;
                                DECLARE acc_name VARCHAR(256);

                                IF (NEW.motorpass_account_id IS NOT NULL) THEN
                                  SELECT  ma.account_name
                                  INTO  acc_name
                                  FROM  motorpass_account ma
                                  WHERE ma.id = NEW.motorpass_account_id;

                                  SELECT  ma.account_number
                                  INTO  acc_num
                                  FROM  motorpass_account ma
                                  WHERE ma.id = NEW.motorpass_account_id;

                                  SELECT  mc.card_expiry_date
                                  INTO  expiry
                                  FROM  motorpass_account ma
                                  JOIN  motorpass_card mc
                                        ON ma.motorpass_card_id = mc.id
                                  WHERE ma.id = NEW.motorpass_account_id;

                                  SET NEW.account_name = acc_name;
                                  SET NEW.account_number = acc_num;
                                  SET NEW.card_expiry_date = expiry;
                                END IF;
                              END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`rdavis`@`localhost`*/ /*!50003 TRIGGER `rebill_motorpass_update` BEFORE UPDATE ON `rebill_motorpass`
 FOR EACH ROW BEGIN
                                DECLARE acc_num INTEGER;
                                DECLARE expiry DATE;
                                DECLARE acc_name VARCHAR(256);

                                IF (NEW.motorpass_account_id IS NOT NULL) THEN
                                  SELECT  ma.account_name
                                  INTO  acc_name
                                  FROM  motorpass_account ma
                                  WHERE ma.id = NEW.motorpass_account_id;

                                  SELECT  ma.account_number
                                  INTO  acc_num
                                  FROM  motorpass_account ma
                                  WHERE ma.id = NEW.motorpass_account_id;

                                  SELECT  mc.card_expiry_date
                                  INTO  expiry
                                  FROM  motorpass_account ma
                                  JOIN  motorpass_card mc
                                        ON ma.motorpass_card_id = mc.id
                                  WHERE ma.id = NEW.motorpass_account_id;

                                  SET NEW.account_name = acc_name;
                                  SET NEW.account_number = acc_num;
                                  SET NEW.card_expiry_date = expiry;
                                END IF;
                              END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `rebill_type`
--

DROP TABLE IF EXISTS `rebill_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rebill_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name of the Rebill Type',
  `description` varchar(512) NOT NULL COMMENT 'Description of the Rebill Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias of the Rebill Type',
  `system_name` varchar(512) NOT NULL COMMENT 'System Name of the Rebill Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rebill_type`
--

LOCK TABLES `rebill_type` WRITE;
/*!40000 ALTER TABLE `rebill_type` DISABLE KEYS */;
INSERT INTO `rebill_type` VALUES (1,'Motorpass','ReD Motorpass','REBILL_TYPE_MOTORPASS','MOTORPASS');
/*!40000 ALTER TABLE `rebill_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_charge_status`
--

DROP TABLE IF EXISTS `recurring_charge_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_charge_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name',
  `description` varchar(256) NOT NULL COMMENT 'Description',
  `system_name` varchar(256) NOT NULL COMMENT 'System Name',
  `const_name` varchar(256) NOT NULL COMMENT 'Constant Alias',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_recurring_charge_status_name` (`name`),
  UNIQUE KEY `un_recurring_charge_status_system_name` (`system_name`),
  UNIQUE KEY `un_recurring_charge_status_const_name` (`const_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_charge_status`
--

LOCK TABLES `recurring_charge_status` WRITE;
/*!40000 ALTER TABLE `recurring_charge_status` DISABLE KEYS */;
INSERT INTO `recurring_charge_status` VALUES (1,'Awaiting Approval','Awaiting Approval','AWAITING_APPROVAL','RECURRING_CHARGE_STATUS_AWAITING_APPROVAL'),(2,'Declined','Declined','DECLINED','RECURRING_CHARGE_STATUS_DECLINED'),(3,'Cancelled','Cancelled','CANCELLED','RECURRING_CHARGE_STATUS_CANCELLED'),(4,'Active','Active','ACTIVE','RECURRING_CHARGE_STATUS_ACTIVE'),(5,'Completed','Completed','COMPLETED','RECURRING_CHARGE_STATUS_COMPLETED');
/*!40000 ALTER TABLE `recurring_charge_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_type`
--

DROP TABLE IF EXISTS `resource_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Resource Type',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Resource Type',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Resource Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Resource Type',
  `resource_type_nature` bigint(20) NOT NULL COMMENT 'Nature of this Resource Type',
  `file_name_regex` varchar(1024) DEFAULT NULL COMMENT 'File Name Validation Perl RegEx',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10043 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_type`
--

LOCK TABLES `resource_type` WRITE;
/*!40000 ALTER TABLE `resource_type` DISABLE KEYS */;
INSERT INTO `resource_type` VALUES (1000,'Unitel Preselection File','Unitel Preselection File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_UNITEL_PRESELECTION',2,NULL),(1001,'Unitel Daily Order File','Unitel Daily Order File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_UNITEL_DAILY_ORDER',2,NULL),(1100,'AAPT EOE Request File','AAPT EOE Request File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_EOE',2,NULL),(1200,'Optus Preselection File','Optus Preselection File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_PRESELECTION',2,NULL),(1201,'Optus Barring File','Optus Barring File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_BAR',2,NULL),(1202,'Optus Suspension File','Optus Suspension File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_SUSPEND',2,NULL),(1203,'Optus Restoration File','Optus Restoration File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_RESTORE',2,NULL),(1204,'Optus Preselection Reversal File','Optus Preselection Reversal File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_PRESELECTION_REVERSAL',2,NULL),(1205,'Optus Deactivation File','Optus Deactivation File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_DEACTIVATION',2,NULL),(1300,'SecurePay Credit Card Debit File','SecurePay Credit Card Debit File','RESOURCE_TYPE_FILE_EXPORT_SECUREPAY_CREDIT_CARD_FILE',2,NULL),(1301,'SecurePay Bank Transfer Debit File','SecurePay Bank Transfer Debit File','RESOURCE_TYPE_FILE_EXPORT_SECUREPAY_BANK_TRANSFER_FILE',2,NULL),(3000,'Westpac BPay File','Westpac BPay File','RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC',1,NULL),(3100,'BillExpress Standard Payments File','BillExpress Standard Payments File','RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD',1,NULL),(3200,'SecurePay Standard Payments File','SecurePay Standard Payments File','RESOURCE_TYPE_FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD',1,NULL),(4000,'Unitel Land Line CDR File','Unitel Land Line CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD',1,NULL),(4001,'Unitel Mobile CDR File','Unitel Mobile CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE',1,NULL),(4002,'Unitel Service & Equipment CDR File','Unitel Service & Equipment CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E',1,NULL),(4100,'Optus Standard CDR File','Optus Standard CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD',1,NULL),(4200,'AAPT Standard CDR File','AAPT Standard CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD',1,NULL),(4300,'iSeek ADSL1 Usage CDR File','iSeek ADSL1 Usage CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_ADSL1',1,NULL),(4400,'M2 Standard CDR File','M2 Standard CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD',1,NULL),(5000,'Unitel Daily Order Report','Unitel Daily Order Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER',1,NULL),(5001,'Unitel Daily Status Changes Report','Unitel Daily Status Changes Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS',1,NULL),(5002,'Unitel Agreed Baskets Report','Unitel Agreed Baskets Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS',1,NULL),(5003,'Unitel Preselection Report','Unitel Preselection Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION',1,NULL),(5004,'Unitel Line Status Report','Unitel Line Status Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS',1,NULL),(5100,'Optus PPR Report','Optus PPR Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_OPTUS_PPR',1,NULL),(5200,'AAPT EOE Return File','AAPT EOE Return File','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN',1,NULL),(5201,'AAPT Line Status Database File','AAPT Line Status Database File','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD',1,NULL),(5202,'AAPT Rejections Report','AAPT Rejections Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT',1,NULL),(5203,'AAPT Loss Report','AAPT Loss Report','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS',1,NULL),(10000,'FTP File Server','FTP File Server','RESOURCE_TYPE_FILE_RESOURCE_FTP',3,NULL),(10001,'SSH2 File Server','SSH2 File Server','RESOURCE_TYPE_FILE_RESOURCE_SSH2',3,NULL),(10002,'AAPT XML File Resource','AAPT XML File Resource','RESOURCE_TYPE_FILE_RESOURCE_AAPT',3,NULL),(10003,'Optus XML File Resource','Optus XML File Resource','RESOURCE_TYPE_FILE_RESOURCE_OPTUS',3,NULL),(10004,'Local Path','Local Path','RESOURCE_TYPE_FILE_RESOURCE_LOCAL',3,NULL),(10005,'Australian Direct Entry File','Australian Direct Entry File','RESOURCE_TYPE_FILE_EXPORT_DIRECT_DEBIT_AUSTRALIAN_DIRECT_ENTRY_FILE',2,NULL),(10006,'Australian Direct Entry Report','Australian Direct Entry Report','RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT',1,NULL),(10007,'Salescom Proposed Dialling List','Salescom Proposed Dialling List','RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_SALESCOM_PROPOSED_DIALLING_LIST',1,NULL),(10008,'ACMA DNCR Request','ACMA Do Not Call Register Request','RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_ACMA_DNCR_REQUEST',2,NULL),(10009,'ACMA DNCR Response','ACMA Do Not Call Register Response','RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_ACMA_DNCR_RESPONSE',1,NULL),(10010,'Salescom Permitted Dialling List','Salescom Permitted Dialling List','RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_SALESCOM_PERMITTED_DIALLING_LIST',2,NULL),(10011,'Salescom Dialler Report','Salescom Dialler Report','RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_SALESCOM_DIALLER_REPORT',1,NULL),(10012,'SFTP File Server','SFTP File Server','RESOURCE_TYPE_FILE_RESOURCE_SFTP',3,NULL),(10013,'iSeek Data Usage File','iSeek Data Usage File','RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA',1,NULL),(10014,'LinxOnline Daily Event File','LinxOnline Daily Event File','RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE',1,NULL),(10015,'LinxOnline Monthly Invoice File','LinxOnline Monthly Invoice File','RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE',1,NULL),(10016,'Motorpass Invoice Payout File','Motorpass Invoice Payout File','RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT',1,NULL),(10017,'Motorpass Billing Export File','Motorpass Billing Export File','RESOURCE_TYPE_FILE_EXPORT_INVOICE_RUN_MOTORPASS',2,NULL),(10018,'Yellow Billing Invoice XML File','Yellow Billing Invoice XML File','RESOURCE_TYPE_FILE_EXPORT_INVOICE_RUN_XML',2,NULL),(10019,'AAPT E-Systems Preselection File','AAPT E-Systems Preselection File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_PRESELECTION',2,NULL),(10020,'AAPT E-Systems Full Service Rebill File','AAPT E-Systems Full Service Rebill File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_FULLSERVICEREBILL',2,NULL),(10021,'AAPT E-Systems Deactivations File','AAPT E-Systems Deactivations File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_DEACTIVATIONS',2,NULL),(10022,'AAPT E-Systems Daily Event File','AAPT E-Systems Daily Event File','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT',1,NULL),(10023,'ReD Motorpass Applications File','ReD Motorpass Applications File','RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_RETAILDECISIONS_APPLICATIONS',2,NULL),(10024,'ReD Motorpass Approvals File','ReD Motorpass Approvals File','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_APPROVALS',1,NULL),(10025,'ReD Motorpass Declines File','ReD Motorpass Declines File','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_DECLINES',1,NULL),(10026,'ReD Motorpass Withdraws File','ReD Motorpass Withdraws File','RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_WITHDRAWS',1,NULL),(10027,'Filesystem','Filesystem (fopen stream)','RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM',5,NULL),(10028,'FTP (Filesystem)','FTP (fopen stream)','RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM_FTP',5,NULL),(10029,'FTP','FTP (ftp_connect)','RESOURCE_TYPE_FILE_DELIVERER_FTP',5,NULL),(10030,'AAPT E-Systems CTOP File','AAPT E-Systems CTOP File','RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP',1,NULL),(10031,'Yellow Billing Correspondence File Export CSV File','Yellow Billing Correspondence File Export CSV File','RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLING_CSV',2,NULL),(10032,'Yellow Billing Correspondence File Export TAR File','Yellow Billing Correspondence File Export TAR File','RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLING_TAR',2,NULL),(10033,'Yellow Billing Correspondence File Import CSV File','Yellow Billing Correspondence File Import CSV File','RESOURCE_TYPE_FILE_IMPORT_CORRESPONDENCE_YELLOWBILLING_CSV',1,NULL),(10034,'AAPT E-Systems COCE File','AAPT E-Systems COCE File','RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE',1,NULL),(10035,'Acenet CDR File','Acenet CDR File','RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET',1,NULL),(10036,'Email','Email','RESOURCE_TYPE_FILE_DELIVERER_EMAIL',5,NULL),(10037,'Flex Correspondence API','Flex Correspondence API','RESOURCE_TYPE_FLEX_CORRESPONDENCE_API',4,NULL),(10038,'Dunn & Bradstreet Referral File','Dunn & Bradstreet Referral File','RESOURCE_TYPE_FILE_EXPORT_DUNN_AND_BRADSTREET_REFERRAL_FILE',2,NULL),(10039,'Email Notification','Email Notification','RESOURCE_TYPE_FILE_DELIVERER_EMAIL_NOTIFICATION',5,NULL),(10041,'ispONE Secure URL Repository','ispONE Secure URL Repository','RESOURCE_TYPE_FILE_RESOURCE_ISPONE',3,NULL),(10042,'ispONE Daily Event File','ispONE Daily Event File','RESOURCE_TYPE_FILE_IMPORT_CDR_ISPONE',1,NULL);
/*!40000 ALTER TABLE `resource_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_type_nature`
--

DROP TABLE IF EXISTS `resource_type_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_type_nature` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Resource Type Nature',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Resource Type Nature',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Resource Type Nature',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Resource Type Nature',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_type_nature`
--

LOCK TABLES `resource_type_nature` WRITE;
/*!40000 ALTER TABLE `resource_type_nature` DISABLE KEYS */;
INSERT INTO `resource_type_nature` VALUES (1,'Import File','Import File','RESOURCE_TYPE_NATURE_IMPORT_FILE'),(2,'Export File','Export File','RESOURCE_TYPE_NATURE_EXPORT_FILE'),(3,'File Repository','File Repository','RESOURCE_TYPE_NATURE_FILE_REPOSITORY'),(4,'API','API','RESOURCE_TYPE_NATURE_API'),(5,'File Deliverer','File Deliverer','RESOURCE_TYPE_NATURE_FILE_DELIVERER');
/*!40000 ALTER TABLE `resource_type_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale`
--

DROP TABLE IF EXISTS `sale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `external_reference` varchar(255) NOT NULL COMMENT 'Defines how to reference this sale in the sales database',
  `account_id` bigint(20) unsigned NOT NULL COMMENT 'FK into Account table',
  `verified_on` datetime NOT NULL COMMENT 'The Date and Time at which this Sale was Verified',
  `sale_type_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'FK into sale_type table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_sale_external_reference` (`external_reference`),
  KEY `fk_sale_account_id` (`account_id`),
  KEY `fk_sale_sale_type_id` (`sale_type_id`),
  CONSTRAINT `fk_sale_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sale_sale_type_id` FOREIGN KEY (`sale_type_id`) REFERENCES `sale_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines sales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale`
--

LOCK TABLES `sale` WRITE;
/*!40000 ALTER TABLE `sale` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_item`
--

DROP TABLE IF EXISTS `sale_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_item` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `external_reference` varchar(255) NOT NULL COMMENT 'Defines how to reference this sale_item in the sales database',
  `sale_id` bigint(20) unsigned NOT NULL COMMENT 'FK into sale table',
  `service_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into Service table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_sale_item_external_reference` (`external_reference`),
  KEY `fk_sale_item_sale_id` (`sale_id`),
  KEY `fk_sale_item_service_id` (`service_id`),
  CONSTRAINT `fk_sale_item_sale_id` FOREIGN KEY (`sale_id`) REFERENCES `sale` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sale_item_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines sale items';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_item`
--

LOCK TABLES `sale_item` WRITE;
/*!40000 ALTER TABLE `sale_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_type`
--

DROP TABLE IF EXISTS `sale_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_type` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this record',
  `name` varchar(255) NOT NULL COMMENT 'Unique name for the sale type',
  `description` varchar(255) NOT NULL COMMENT 'Description of the sale type',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_sale_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines sale types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_type`
--

LOCK TABLES `sale_type` WRITE;
/*!40000 ALTER TABLE `sale_type` DISABLE KEYS */;
INSERT INTO `sale_type` VALUES (1,'New Customer','New Customer'),(2,'Existing Customer','Existing Customer'),(3,'Win Back','Win Back');
/*!40000 ALTER TABLE `sale_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_action`
--

DROP TABLE IF EXISTS `service_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_action` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `service_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Service',
  `action_id` bigint(20) unsigned NOT NULL COMMENT '(FK) action',
  PRIMARY KEY (`id`),
  KEY `fk_service_action_service_id` (`service_id`),
  KEY `fk_service_action_action_id` (`action_id`),
  CONSTRAINT `fk_service_action_action_id` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_service_action_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_action`
--

LOCK TABLES `service_action` WRITE;
/*!40000 ALTER TABLE `service_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_barring_level`
--

DROP TABLE IF EXISTS `service_barring_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_barring_level` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `created_employee_id` bigint(20) unsigned NOT NULL,
  `authorised_datetime` datetime DEFAULT NULL,
  `authorised_employee_id` bigint(20) unsigned DEFAULT NULL,
  `actioned_datetime` datetime DEFAULT NULL,
  `actioned_employee_id` bigint(20) unsigned DEFAULT NULL,
  `account_barring_level_id` bigint(20) unsigned DEFAULT NULL,
  `barring_level_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_service_barring_level_created_datetime` (`created_datetime`),
  KEY `in_service_barring_level_authorised_datetime` (`authorised_datetime`),
  KEY `in_service_barring_level_actioned_datetime` (`actioned_datetime`),
  KEY `fk_service_barring_level_service_id` (`service_id`),
  KEY `fk_service_barring_level_created_employee_id` (`created_employee_id`),
  KEY `fk_service_barring_level_authorised_employee_id` (`authorised_employee_id`),
  KEY `fk_service_barring_level_actioned_employee_id` (`actioned_employee_id`),
  KEY `fk_service_barring_level_account_barring_level_id` (`account_barring_level_id`),
  KEY `fk_service_barring_level_barring_level_id` (`barring_level_id`),
  CONSTRAINT `fk_service_barring_level_account_barring_level_id` FOREIGN KEY (`account_barring_level_id`) REFERENCES `account_barring_level` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_service_barring_level_actioned_employee_id` FOREIGN KEY (`actioned_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_service_barring_level_authorised_employee_id` FOREIGN KEY (`authorised_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_service_barring_level_barring_level_id` FOREIGN KEY (`barring_level_id`) REFERENCES `barring_level` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_service_barring_level_created_employee_id` FOREIGN KEY (`created_employee_id`) REFERENCES `Employee` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_service_barring_level_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_barring_level`
--

LOCK TABLES `service_barring_level` WRITE;
/*!40000 ALTER TABLE `service_barring_level` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_barring_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_line_status`
--

DROP TABLE IF EXISTS `service_line_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_line_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Line Status',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Nature',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Nature',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Nature',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=508 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_line_status`
--

LOCK TABLES `service_line_status` WRITE;
/*!40000 ALTER TABLE `service_line_status` DISABLE KEYS */;
INSERT INTO `service_line_status` VALUES (500,'Pending','Pending Connection','SERVICE_LINE_PENDING'),(501,'Active','Active','SERVICE_LINE_ACTIVE'),(502,'Disconnected','Disconnected','SERVICE_LINE_DISCONNECTED'),(503,'Barred','Barred','SERVICE_LINE_BARRED'),(504,'Temporarily Disconnected','Temporarily Disconnected','SERVICE_LINE_TEMPORARY_DISCONNECT'),(505,'Rejected','Churn Request Rejected','SERVICE_LINE_REJECTED'),(506,'Churned','Churned Away','SERVICE_LINE_CHURNED'),(507,'Reversed','Churn Reversed','SERVICE_LINE_REVERSED');
/*!40000 ALTER TABLE `service_line_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_line_status_update`
--

DROP TABLE IF EXISTS `service_line_status_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_line_status_update` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id',
  `current_line_status` bigint(20) DEFAULT NULL,
  `provisioning_type` bigint(20) NOT NULL COMMENT 'Request Type',
  `new_line_status` bigint(20) NOT NULL COMMENT 'Resulting Line Status',
  `provisioning_request_status` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_line_status_update`
--

LOCK TABLES `service_line_status_update` WRITE;
/*!40000 ALTER TABLE `service_line_status_update` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_line_status_update` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_status`
--

DROP TABLE IF EXISTS `service_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Service Status',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Service Status',
  `can_invoice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: Service can be Invoiced; 0: Service cannot be Invoiced',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Service Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Service Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_status`
--

LOCK TABLES `service_status` WRITE;
/*!40000 ALTER TABLE `service_status` DISABLE KEYS */;
INSERT INTO `service_status` VALUES (400,'Active',1,'Active','SERVICE_ACTIVE'),(402,'Disconnected',1,'Disconnected','SERVICE_DISCONNECTED'),(403,'Archived',0,'Archived','SERVICE_ARCHIVED'),(404,'Pending',0,'Pending Activation','SERVICE_PENDING');
/*!40000 ALTER TABLE `service_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_total_service`
--

DROP TABLE IF EXISTS `service_total_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_total_service` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the ServiceTotal-Service relationship',
  `service_total_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Service Total',
  `service_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Service',
  PRIMARY KEY (`id`),
  KEY `fk_service_total_service_service_total_id` (`service_total_id`),
  KEY `fk_service_total_service_service_id` (`service_id`),
  CONSTRAINT `fk_service_total_service_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_service_total_service_service_total_id` FOREIGN KEY (`service_total_id`) REFERENCES `ServiceTotal` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_total_service`
--

LOCK TABLES `service_total_service` WRITE;
/*!40000 ALTER TABLE `service_total_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_total_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_type`
--

DROP TABLE IF EXISTS `service_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Service Type',
  `name` varchar(256) NOT NULL COMMENT 'Name for the Service Type',
  `description` varchar(512) NOT NULL COMMENT 'Description for the Service Type',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name for the Service Type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_type`
--

LOCK TABLES `service_type` WRITE;
/*!40000 ALTER TABLE `service_type` DISABLE KEYS */;
INSERT INTO `service_type` VALUES (100,'ADSL','ADSL','SERVICE_TYPE_ADSL'),(101,'Mobile','Mobile','SERVICE_TYPE_MOBILE'),(102,'Land Line','Land Line','SERVICE_TYPE_LAND_LINE'),(103,'Inbound','Inbound 13/1300/1800','SERVICE_TYPE_INBOUND'),(104,'Dialup','Dialup Internet','SERVICE_TYPE_DIALUP');
/*!40000 ALTER TABLE `service_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state`
--

DROP TABLE IF EXISTS `state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this record',
  `name` varchar(255) NOT NULL COMMENT 'Name of the state',
  `country_id` bigint(20) unsigned NOT NULL COMMENT 'FK into country table, defining the country that the state belongs to',
  `code` varchar(255) NOT NULL COMMENT 'Abbreviation of the state''s name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_country_id_name` (`country_id`,`name`),
  CONSTRAINT `fk_state_country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Geographical States';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state`
--

LOCK TABLES `state` WRITE;
/*!40000 ALTER TABLE `state` DISABLE KEYS */;
INSERT INTO `state` VALUES (1,'Australian Capital Territory',1,'ACT'),(2,'New South Wales',1,'NSW'),(3,'Northern Territory',1,'NT'),(4,'Queensland',1,'QLD'),(5,'South Australia',1,'SA'),(6,'Tasmania',1,'TAS'),(7,'Victoria',1,'VIC'),(8,'Western Australia',1,'WA');
/*!40000 ALTER TABLE `state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Status',
  `description` varchar(1024) NOT NULL COMMENT 'Description of the Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name of the Status',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (1,'Active','Active','STATUS_ACTIVE'),(2,'Inactive','Inactive','STATUS_INACTIVE');
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey`
--

DROP TABLE IF EXISTS `survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'this field is used to store the unique id of each survey name',
  `creation_date` timestamp NULL DEFAULT NULL COMMENT 'date survey was created',
  `start_date` timestamp NULL DEFAULT NULL COMMENT 'date survey starts displaying',
  `end_date` timestamp NULL DEFAULT NULL COMMENT 'date survey ends',
  `created_by` bigint(20) DEFAULT NULL COMMENT 'person who created the survey, this should represent the staff members User Id not the alpha Username',
  `title` varchar(255) DEFAULT NULL COMMENT 'title or description of survey',
  `conditions` longtext COMMENT 'survey conditions',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'This field represents the CustomerGroup that is allowed to view the survey.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='this table contains surveys title, start and end times and t';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey`
--

LOCK TABLES `survey` WRITE;
/*!40000 ALTER TABLE `survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_completed`
--

DROP TABLE IF EXISTS `survey_completed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_completed` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'the id of each survey which is completed',
  `date_completed` timestamp NULL DEFAULT NULL COMMENT 'the date which the user completed the survey.',
  `contact_id` bigint(20) DEFAULT NULL COMMENT 'the contact id of the user who completed the survey',
  `survey_id` bigint(20) DEFAULT NULL COMMENT 'the id of the survey which was completed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='this table stores a record of completed surveys';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_completed`
--

LOCK TABLES `survey_completed` WRITE;
/*!40000 ALTER TABLE `survey_completed` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_completed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_completed_response`
--

DROP TABLE IF EXISTS `survey_completed_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_completed_response` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'the id of the response submitted by the user',
  `survey_question_id` bigint(20) DEFAULT NULL COMMENT 'the question id which this response is for.',
  `response_text` longtext COMMENT 'this field contains the actual response',
  `survey_completed_id` bigint(20) DEFAULT NULL COMMENT 'the id of the completed survey which this references',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains all the responses for the survey.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_completed_response`
--

LOCK TABLES `survey_completed_response` WRITE;
/*!40000 ALTER TABLE `survey_completed_response` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_completed_response` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_completed_response_option`
--

DROP TABLE IF EXISTS `survey_completed_response_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_completed_response_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'the id of this respone',
  `survey_question_option_id` bigint(20) DEFAULT NULL,
  `option_text` longtext COMMENT 'this is the text/response from the user',
  `survey_completed_response_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains responses to options in the survey.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_completed_response_option`
--

LOCK TABLES `survey_completed_response_option` WRITE;
/*!40000 ALTER TABLE `survey_completed_response_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_completed_response_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_question`
--

DROP TABLE IF EXISTS `survey_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_question` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'the id of the question',
  `survey_id` bigint(20) DEFAULT NULL COMMENT 'this field tells us which survey the question belongs to',
  `question` varchar(32767) DEFAULT NULL COMMENT 'the question text, e.g. how old are you',
  `response_required` tinyint(1) DEFAULT NULL COMMENT 'response required forces the user to respond to the question if set to 1',
  `survey_question_response_type_id` varchar(255) DEFAULT NULL COMMENT 'response type represents how the question is displayed, e.g. select, text, checkbox',
  `question_num` bigint(20) DEFAULT NULL COMMENT 'if there are 10 questions in a survey and this is the 3rd question then this would be set to 3.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains the questions for the survey';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_question`
--

LOCK TABLES `survey_question` WRITE;
/*!40000 ALTER TABLE `survey_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_question_option`
--

DROP TABLE IF EXISTS `survey_question_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_question_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'the id of the option',
  `survey_question_id` bigint(20) DEFAULT NULL COMMENT 'the question id the option belongs to, multiple options can belong to one question, e.g. for a select box.',
  `option_name` varchar(255) DEFAULT NULL COMMENT 'the option name, the is displayed next to the field(checkbox) or within the field (select)',
  `survey_question_option_response_type_id` bigint(20) DEFAULT NULL COMMENT 'the option type id represents if there is an additional field available in this question, e.g. a select box and a text box next to it.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains a list of options for each question.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_question_option`
--

LOCK TABLES `survey_question_option` WRITE;
/*!40000 ALTER TABLE `survey_question_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_question_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_question_option_response_type`
--

DROP TABLE IF EXISTS `survey_question_option_response_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_question_option_response_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'the id of the question response type.',
  `name` varchar(255) DEFAULT NULL COMMENT 'the name of the question response type. e.g. text, select, checkbox',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='this table stores options for questions, e.g. select, radial';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_question_option_response_type`
--

LOCK TABLES `survey_question_option_response_type` WRITE;
/*!40000 ALTER TABLE `survey_question_option_response_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_question_option_response_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_question_response_type`
--

DROP TABLE IF EXISTS `survey_question_response_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_question_response_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'this field sets a unique identifer for each response type.',
  `name` varchar(255) DEFAULT NULL COMMENT 'this field contains options for displaying a question, e.g. select, checkbox, radial.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains the questions for the survey';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_question_response_type`
--

LOCK TABLES `survey_question_response_type` WRITE;
/*!40000 ALTER TABLE `survey_question_response_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_question_response_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_type`
--

DROP TABLE IF EXISTS `tax_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Tax Rate',
  `name` varchar(255) NOT NULL,
  `description` varchar(512) NOT NULL COMMENT 'Description for the Tax',
  `rate_percentage` decimal(13,4) NOT NULL COMMENT 'The Tax Rate Percentage (eg. 0.10 for 10%)',
  `global` tinyint(1) NOT NULL COMMENT '1: This Tax Rate is applied to everything except exempted charges (should only be one of these); 0: This tax is only applied to specific charges',
  `start_datetime` datetime NOT NULL COMMENT 'The date this tax becomes effective',
  `end_datetime` datetime NOT NULL COMMENT 'The date this tax expires',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_type`
--

LOCK TABLES `tax_type` WRITE;
/*!40000 ALTER TABLE `tax_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_blacklist`
--

DROP TABLE IF EXISTS `telemarketing_fnn_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_blacklist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `fnn` varchar(20) NOT NULL COMMENT 'Service Number',
  `cached_on` datetime NOT NULL COMMENT 'Date the blacklisting comes into effect',
  `expired_on` datetime NOT NULL COMMENT 'Date the blacklisting expires',
  `telemarketing_fnn_blacklist_nature_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Nature of this Blacklisting',
  `file_import_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The File that this was imported from',
  PRIMARY KEY (`id`),
  KEY `fk_telemarketing_fnn_blacklist_nature_id` (`telemarketing_fnn_blacklist_nature_id`),
  KEY `fk_telemarketing_fnn_blacklist_file_import_id` (`file_import_id`),
  CONSTRAINT `fk_telemarketing_fnn_blacklist_file_import_id` FOREIGN KEY (`file_import_id`) REFERENCES `FileImport` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_blacklist_nature_id` FOREIGN KEY (`telemarketing_fnn_blacklist_nature_id`) REFERENCES `telemarketing_fnn_blacklist_nature` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_blacklist`
--

LOCK TABLES `telemarketing_fnn_blacklist` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `telemarketing_fnn_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_blacklist_nature`
--

DROP TABLE IF EXISTS `telemarketing_fnn_blacklist_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_blacklist_nature` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Short Name for the Nature',
  `description` varchar(1024) NOT NULL COMMENT 'Long Description for the Nature',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_blacklist_nature`
--

LOCK TABLES `telemarketing_fnn_blacklist_nature` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_blacklist_nature` DISABLE KEYS */;
INSERT INTO `telemarketing_fnn_blacklist_nature` VALUES (1,'Opt-Out','Internal Opt-Out','TELEMARKETING_FNN_BLACKLIST_NATURE_OPTOUT'),(2,'Do Not Call Register','Do Not Call Register','TELEMARKETING_FNN_BLACKLIST_NATURE_DNCR');
/*!40000 ALTER TABLE `telemarketing_fnn_blacklist_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_dialled`
--

DROP TABLE IF EXISTS `telemarketing_fnn_dialled`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_dialled` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `fnn` varchar(20) NOT NULL COMMENT 'The Service Number Dialled',
  `customer_group_id` bigint(20) NOT NULL COMMENT '(FK) The Customer Group represented',
  `file_import_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Dialler Report this was imported from',
  `dealer_id` bigint(20) unsigned NOT NULL COMMENT '(FK) Dealer who made the call',
  `dialled_by` varchar(512) DEFAULT NULL COMMENT 'Salesperson who made the call',
  `dialled_on` datetime NOT NULL COMMENT 'When the call was made',
  `telemarketing_fnn_dialled_result_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Result of the Call',
  PRIMARY KEY (`id`),
  KEY `fk_telemarketing_fnn_dialled_customer_group_id` (`customer_group_id`),
  KEY `fk_telemarketing_fnn_dialled_file_import_id` (`file_import_id`),
  KEY `fk_telemarketing_fnn_dialled_dealer_id` (`dealer_id`),
  KEY `fk_telemarketing_fnn_dialled_telemarketing_fnn_dialled_result_id` (`telemarketing_fnn_dialled_result_id`),
  KEY `in_telemarketing_fnn_dialled_fnn` (`fnn`),
  KEY `in_telemarketing_fnn_dialled_dialled_on` (`dialled_on`),
  CONSTRAINT `fk_telemarketing_fnn_dialled_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_dialled_dealer_id` FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_dialled_file_import_id` FOREIGN KEY (`file_import_id`) REFERENCES `FileImport` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_dialled_telemarketing_fnn_dialled_result_id` FOREIGN KEY (`telemarketing_fnn_dialled_result_id`) REFERENCES `telemarketing_fnn_dialled_result` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_dialled`
--

LOCK TABLES `telemarketing_fnn_dialled` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_dialled` DISABLE KEYS */;
/*!40000 ALTER TABLE `telemarketing_fnn_dialled` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_dialled_result`
--

DROP TABLE IF EXISTS `telemarketing_fnn_dialled_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_dialled_result` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Short Name for the Result',
  `description` varchar(1024) NOT NULL COMMENT 'Long Description for the Result',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name',
  `telemarketing_fnn_dialled_result_category_id` int(10) unsigned NOT NULL COMMENT '(FK) Category for this Call Result',
  PRIMARY KEY (`id`),
  KEY `fk_telemarketing_fnn_dialled_result_dialled_result_category_id` (`telemarketing_fnn_dialled_result_category_id`),
  CONSTRAINT `fk_telemarketing_fnn_dialled_result_dialled_result_category_id` FOREIGN KEY (`telemarketing_fnn_dialled_result_category_id`) REFERENCES `telemarketing_fnn_dialled_result_category` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_dialled_result`
--

LOCK TABLES `telemarketing_fnn_dialled_result` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_dialled_result` DISABLE KEYS */;
INSERT INTO `telemarketing_fnn_dialled_result` VALUES (101,'Line Busy','Line Busy','TELEMARKETING_FNN_DIALLED_RESULT_LINE_BUSY',1),(102,'No Ring Tone','No Ring Tone','TELEMARKETING_FNN_DIALLED_RESULT_NO_RING_TONE',1),(103,'No Answer','No Answer','TELEMARKETING_FNN_DIALLED_RESULT_NO_ANSWER',1),(104,'Blank Call','Blank Call','TELEMARKETING_FNN_DIALLED_RESULT_BLANK_CALL',1),(201,'Documentation Requested','Documentation Requested','TELEMARKETING_FNN_DIALLED_RESULT_DOCUMENTATION_REQUESTED',2),(202,'Brochure Request','Brochure Request by Fax/Email','TELEMARKETING_FNN_DIALLED_RESULT_BROCHURE_REQUEST',2),(203,'Follow Up','Blank Call','TELEMARKETING_FNN_DIALLED_RESULT_CALL_BACK',2),(301,'Hung Up','Hung Up','TELEMARKETING_FNN_DIALLED_RESULT_HUNG_UP',3),(302,'Do Not Call','Do Not Call','TELEMARKETING_FNN_DIALLED_RESULT_DO_NOT_CALL',3),(401,'Answering Machine','Answering Machine','TELEMARKETING_FNN_DIALLED_RESULT_ANSWERING_MACHINE',4),(402,'Fax Machine','Fax Machine','TELEMARKETING_FNN_DIALLED_RESULT_FAX_MACHINE',4),(501,'Sales Done','Sales Done','TELEMARKETING_FNN_DIALLED_RESULT_SALES_DONE',5),(601,'ABN Mismatch','ABN Not Matching','TELEMARKETING_FNN_DIALLED_RESULT_ABN_MISMATCH',6),(602,'Bad Credit History','Bad Credit History','TELEMARKETING_FNN_DIALLED_RESULT_BAD_CREDIT_HISTORY',6),(603,'Suspicious Customer','Suspicious Customer','TELEMARKETING_FNN_DIALLED_RESULT_SUSPICIOUS_CUSTOMER',6),(604,'Unauthorised Person','Unauthorised Person','TELEMARKETING_FNN_DIALLED_RESULT_UNAUTHORISED_PERSON',6),(605,'Foreign Language','Foreign Language','TELEMARKETING_FNN_DIALLED_RESULT_FOREIGN_LANGUAGE',6),(606,'Wrong Number','Wrong Number','TELEMARKETING_FNN_DIALLED_RESULT_WRONG_NUMBER',6),(701,'Static on Line','Static on the Line','TELEMARKETING_FNN_DIALLED_RESULT_STATIC_ON_LINE',7),(702,'Unknown Termination','Call Terminated For Unknown Reasons','TELEMARKETING_FNN_DIALLED_RESULT_UNKNOWN_TERMINATION',7);
/*!40000 ALTER TABLE `telemarketing_fnn_dialled_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_dialled_result_category`
--

DROP TABLE IF EXISTS `telemarketing_fnn_dialled_result_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_dialled_result_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(256) NOT NULL COMMENT 'Name',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description',
  `system_name` varchar(256) NOT NULL COMMENT 'System Name',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Alias',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_dialled_result_category`
--

LOCK TABLES `telemarketing_fnn_dialled_result_category` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_dialled_result_category` DISABLE KEYS */;
INSERT INTO `telemarketing_fnn_dialled_result_category` VALUES (1,'Not Available','Not Available','NOT_AVAILABLE','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_AVAILABLE'),(2,'Call Back','Call Back','CALL_BACK','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_BACK'),(3,'Not Interested','Not Interested','NOT_INTERESTED','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_INTERESTED'),(4,'Automated Answer','Automated Answer','AUTOMATED_ANSWER','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_AUTOMATED_ANSWER'),(5,'Sale Completed','Sale Completed','SALE_COMPLETED','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_SALE_COMPLETED'),(6,'Not Qualified','Not Qualified','NOT_QUALIFIED','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED'),(7,'Call Dropped','Call Dropped','CALL_DROPPED','TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_DROPPED');
/*!40000 ALTER TABLE `telemarketing_fnn_dialled_result_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_proposed`
--

DROP TABLE IF EXISTS `telemarketing_fnn_proposed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_proposed` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `fnn` varchar(20) NOT NULL COMMENT 'Service Number',
  `customer_group_id` bigint(20) NOT NULL COMMENT '(FK) Customer Group which will be represented in the pitch',
  `proposed_list_file_import_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Proposed Dialling List File this was Imported from',
  `do_not_call_file_export_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The DNCR Washing File this was exported to',
  `permitted_list_file_export_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The Permitted Dialling List this was Exported to',
  `call_period_start` datetime NOT NULL COMMENT 'The Earliest Date this FNN can be called',
  `call_period_end` datetime NOT NULL COMMENT 'The Latest Date this FNN can be called',
  `dealer_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Dealer who requested this FNN',
  `telemarketing_fnn_proposed_status_id` bigint(20) unsigned NOT NULL COMMENT '(FK) The Status of this FNN Request',
  `telemarketing_fnn_withheld_reason_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The Reason why this FNN was withheld',
  `raw_record` varchar(4092) NOT NULL COMMENT 'Raw record data from the source file',
  `do_not_call_file_import_id` bigint(20) unsigned DEFAULT NULL COMMENT '(FK) The DNCR Wash List this FNN was verified against',
  PRIMARY KEY (`id`),
  KEY `fk_telemarketing_fnn_proposed_customer_group_id` (`customer_group_id`),
  KEY `fk_telemarketing_fnn_proposed_proposed_list_file_import_id` (`proposed_list_file_import_id`),
  KEY `fk_telemarketing_fnn_proposed_do_not_call_file_export_id` (`do_not_call_file_export_id`),
  KEY `fk_telemarketing_fnn_proposed_permitted_list_file_export_id` (`permitted_list_file_export_id`),
  KEY `fk_telemarketing_fnn_proposed_dealer_id` (`dealer_id`),
  KEY `fk_telemarketing_fnn_proposed_telemarketing_fnn_proposed_status` (`telemarketing_fnn_proposed_status_id`),
  KEY `fk_telemarketing_fnn_proposed_telemarketing_fnn_withheld_reason` (`telemarketing_fnn_withheld_reason_id`),
  KEY `fk_telemarketing_fnn_proposed_do_not_call_file_import_id` (`do_not_call_file_import_id`),
  KEY `in_telemarketing_fnn_proposed_fnn` (`fnn`),
  KEY `in_telemarketing_fnn_proposed_call_period_start` (`call_period_start`),
  KEY `in_telemarketing_fnn_proposed_call_period_end` (`call_period_end`),
  CONSTRAINT `fk_telemarketing_fnn_proposed_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_dealer_id` FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_do_not_call_file_export_id` FOREIGN KEY (`do_not_call_file_export_id`) REFERENCES `FileExport` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_do_not_call_file_import_id` FOREIGN KEY (`do_not_call_file_import_id`) REFERENCES `FileImport` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_permitted_list_file_export_id` FOREIGN KEY (`permitted_list_file_export_id`) REFERENCES `FileExport` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_proposed_list_file_import_id` FOREIGN KEY (`proposed_list_file_import_id`) REFERENCES `FileImport` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_telemarketing_fnn_proposed_status` FOREIGN KEY (`telemarketing_fnn_proposed_status_id`) REFERENCES `telemarketing_fnn_proposed_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_telemarketing_fnn_proposed_telemarketing_fnn_withheld_reason` FOREIGN KEY (`telemarketing_fnn_withheld_reason_id`) REFERENCES `telemarketing_fnn_withheld_reason` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_proposed`
--

LOCK TABLES `telemarketing_fnn_proposed` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_proposed` DISABLE KEYS */;
/*!40000 ALTER TABLE `telemarketing_fnn_proposed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_proposed_status`
--

DROP TABLE IF EXISTS `telemarketing_fnn_proposed_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_proposed_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Short Name for the Status',
  `description` varchar(1024) NOT NULL COMMENT 'Long Description for the Status',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_proposed_status`
--

LOCK TABLES `telemarketing_fnn_proposed_status` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_proposed_status` DISABLE KEYS */;
INSERT INTO `telemarketing_fnn_proposed_status` VALUES (1,'Imported','Imported','TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED'),(2,'Withheld','Withheld','TELEMARKETING_FNN_PROPOSED_STATUS_WITHHELD'),(3,'Exported','Exported','TELEMARKETING_FNN_PROPOSED_STATUS_EXPORT');
/*!40000 ALTER TABLE `telemarketing_fnn_proposed_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemarketing_fnn_withheld_reason`
--

DROP TABLE IF EXISTS `telemarketing_fnn_withheld_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemarketing_fnn_withheld_reason` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Identifier',
  `name` varchar(255) NOT NULL COMMENT 'Short Name for the Reason',
  `description` varchar(1024) NOT NULL COMMENT 'Long Description for the Reason',
  `const_name` varchar(512) NOT NULL COMMENT 'Constant Name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemarketing_fnn_withheld_reason`
--

LOCK TABLES `telemarketing_fnn_withheld_reason` WRITE;
/*!40000 ALTER TABLE `telemarketing_fnn_withheld_reason` DISABLE KEYS */;
INSERT INTO `telemarketing_fnn_withheld_reason` VALUES (1,'Do Not Call Register','Do Not Call Register','TELEMARKETING_FNN_WITHHELD_REASON_DNCR'),(2,'Opt-Out','Internal Opt-Out','TELEMARKETING_FNN_WITHHELD_REASON_OPTOUT'),(3,'Tolling','Currently Tolling','TELEMARKETING_FNN_WITHHELD_REASON_TOLLING'),(4,'Call Period Conflict','Call Period Conflict','TELEMARKETING_FNN_WITHHELD_REASON_CALL_PERIOD_CONFLICT'),(5,'Active Contact','Active Contact','TELEMARKETING_FNN_WITHHELD_REASON_FLEX_CONTACT'),(6,'Active Service','Active Service','TELEMARKETING_FNN_WITHHELD_REASON_FLEX_SERVICE');
/*!40000 ALTER TABLE `telemarketing_fnn_withheld_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_commissions`
--

DROP TABLE IF EXISTS `temp_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_commissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cust_account_id` bigint(20) unsigned NOT NULL,
  `service_number` bigint(20) unsigned NOT NULL,
  `rate_plan` bigint(20) unsigned NOT NULL,
  `connection_date` date NOT NULL,
  `volume` decimal(10,0) NOT NULL,
  `cost` decimal(10,0) NOT NULL,
  `dealer_id` bigint(20) unsigned NOT NULL,
  `commission` decimal(10,0) NOT NULL,
  `gst` decimal(10,0) NOT NULL,
  `commission_inc_gst` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cust_account_id` (`cust_account_id`,`service_number`,`rate_plan`,`dealer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temp_commissions`
--

LOCK TABLES `temp_commissions` WRITE;
/*!40000 ALTER TABLE `temp_commissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `temp_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_attachment`
--

DROP TABLE IF EXISTS `ticketing_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_attachment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `correspondance_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_correspondance table',
  `file_name` varchar(255) NOT NULL COMMENT 'Name of the file',
  `attachment_type_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_attachment_type table',
  `file_content` mediumblob NOT NULL COMMENT 'The binary contents of the attachment file',
  `blacklist_override` smallint(5) unsigned DEFAULT NULL COMMENT 'FK to the active_status table',
  PRIMARY KEY (`id`),
  KEY `fk_ticketing_attachment_correspondance_id_t_correspondance_id` (`correspondance_id`),
  KEY `fk_ticketing_attachment_attachment_type_id_t_attachment_type_id` (`attachment_type_id`),
  KEY `fk_ticketing_attachment_blacklist_override_active_status_id` (`blacklist_override`),
  CONSTRAINT `fk_ticketing_attachment_attachment_type_id_t_attachment_type_id` FOREIGN KEY (`attachment_type_id`) REFERENCES `ticketing_attachment_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_attachment_blacklist_override_active_status_id` FOREIGN KEY (`blacklist_override`) REFERENCES `active_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_attachment_correspondance_id_t_correspondance_id` FOREIGN KEY (`correspondance_id`) REFERENCES `ticketing_correspondance` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attachments to correspondances in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_attachment`
--

LOCK TABLES `ticketing_attachment` WRITE;
/*!40000 ALTER TABLE `ticketing_attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_attachment_blacklist_status`
--

DROP TABLE IF EXISTS `ticketing_attachment_blacklist_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_attachment_blacklist_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the status',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket attachment blacklist statuses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_attachment_blacklist_status`
--

LOCK TABLES `ticketing_attachment_blacklist_status` WRITE;
/*!40000 ALTER TABLE `ticketing_attachment_blacklist_status` DISABLE KEYS */;
INSERT INTO `ticketing_attachment_blacklist_status` VALUES (0,'Grey Listed','Grey Listed','TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY'),(1,'White Listed','White Listed','TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE'),(2,'Black Listed','Black Listed','TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK');
/*!40000 ALTER TABLE `ticketing_attachment_blacklist_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_attachment_type`
--

DROP TABLE IF EXISTS `ticketing_attachment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_attachment_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `extension` varchar(255) NOT NULL COMMENT 'File extension',
  `mime_type` varchar(255) NOT NULL COMMENT 'MIME type',
  `blacklist_status_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_attachment_blacklist_status table',
  PRIMARY KEY (`id`),
  KEY `fk_ticketing_attachment_type_blacklist_status_id` (`blacklist_status_id`),
  CONSTRAINT `fk_ticketing_attachment_type_blacklist_status_id` FOREIGN KEY (`blacklist_status_id`) REFERENCES `ticketing_attachment_blacklist_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticketing attachment file type';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_attachment_type`
--

LOCK TABLES `ticketing_attachment_type` WRITE;
/*!40000 ALTER TABLE `ticketing_attachment_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_attachment_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_category`
--

DROP TABLE IF EXISTS `ticketing_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_category` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the category',
  `description` varchar(255) NOT NULL COMMENT 'Description of the category',
  `css_name` varchar(255) NOT NULL COMMENT 'The css class name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ticketing_category_name` (`name`),
  UNIQUE KEY `un_ticketing_category_css_name` (`css_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Categories of ticketing system tickets';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_category`
--

LOCK TABLES `ticketing_category` WRITE;
/*!40000 ALTER TABLE `ticketing_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_config`
--

DROP TABLE IF EXISTS `ticketing_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `protocol` varchar(50) NOT NULL DEFAULT 'Pop3' COMMENT 'Currently only POP3, IMAP, MBOX and MailDir are supported',
  `host` varchar(255) NOT NULL COMMENT 'Host machine (POP3 or IMAP) or directory path (MBOX or MailDir)',
  `port` bigint(20) DEFAULT NULL COMMENT 'Port for mail retrieval on host machine (NULL uses default port)',
  `username` varchar(255) DEFAULT NULL COMMENT 'Username to use when retrieving emails (or backup dir for XML files)',
  `password` varchar(255) DEFAULT NULL COMMENT 'Password (encrypted) to use when retrieving emails (or dir for junk XML files)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Configuration setting for the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_config`
--

LOCK TABLES `ticketing_config` WRITE;
/*!40000 ALTER TABLE `ticketing_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_contact`
--

DROP TABLE IF EXISTS `ticketing_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_contact` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(4) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` char(25) DEFAULT NULL,
  `mobile` char(25) DEFAULT NULL,
  `fax` char(25) DEFAULT NULL,
  `status` smallint(5) unsigned NOT NULL COMMENT 'FK to active_status table',
  `auto_reply` smallint(5) unsigned NOT NULL COMMENT 'FK to active_status table',
  PRIMARY KEY (`id`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`),
  KEY `email` (`email`),
  KEY `phone` (`phone`),
  KEY `mobile` (`mobile`),
  KEY `fax` (`fax`),
  KEY `fk_ticketing_contact_status_active_status_id` (`status`),
  KEY `fk_ticketing_contact_auto_reply_active_status_id` (`auto_reply`),
  CONSTRAINT `fk_ticketing_contact_auto_reply_active_status_id` FOREIGN KEY (`auto_reply`) REFERENCES `active_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_contact_status_active_status_id` FOREIGN KEY (`status`) REFERENCES `active_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Customer contacts in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_contact`
--

LOCK TABLES `ticketing_contact` WRITE;
/*!40000 ALTER TABLE `ticketing_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_contact_account`
--

DROP TABLE IF EXISTS `ticketing_contact_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_contact_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `ticketing_contact_id` bigint(20) unsigned NOT NULL COMMENT 'FK to ticketing_contact table',
  `account_id` bigint(20) unsigned NOT NULL COMMENT 'FK to Account table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ticketing_contact_account_ticketing_contact_id_account_id` (`ticketing_contact_id`,`account_id`),
  KEY `fk_ticketing_contact_account_account_id_account_id` (`account_id`),
  CONSTRAINT `fk_ticketing_contact_account_account_id_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_contact_account_ticketing_contact_id_t_contact_id` FOREIGN KEY (`ticketing_contact_id`) REFERENCES `ticketing_contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Accounts associated with ticketing contact';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_contact_account`
--

LOCK TABLES `ticketing_contact_account` WRITE;
/*!40000 ALTER TABLE `ticketing_contact_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_contact_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_correspondance`
--

DROP TABLE IF EXISTS `ticketing_correspondance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_correspondance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `ticket_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_ticket table',
  `summary` varchar(255) NOT NULL COMMENT 'Summary of correspondance (subject)',
  `details` mediumtext NOT NULL COMMENT 'Correspondance details (message)',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_user table (null if created by contact)',
  `contact_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_contact table(null if created by user)',
  `customer_group_email_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_customer_group_email table',
  `source_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_correspondance_source table',
  `delivery_status_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_correspondance_delivery_status table',
  `creation_datetime` datetime DEFAULT NULL COMMENT 'Date/Time that the correspondance was received/created',
  `delivery_datetime` datetime DEFAULT NULL COMMENT 'Date/Time that the correspondance was received/delivered',
  PRIMARY KEY (`id`),
  KEY `fk_ticketing_correspondance_ticket_id_ticketing_ticket_id` (`ticket_id`),
  KEY `fk_ticketing_correspondance_user_id_ticketing_user_id` (`user_id`),
  KEY `fk_ticketing_correspondance_contact_id_ticketing_contact_id` (`contact_id`),
  KEY `fk_ticketing_correspondance_customer_group_email_id_tcge_id` (`customer_group_email_id`),
  KEY `fk_ticketing_correspondance_source_id_tc_source_id` (`source_id`),
  KEY `fk_ticketing_correspondance_delivery_status_id_tcds_id` (`delivery_status_id`),
  KEY `in_ticketing_correspondance_creation_datetime` (`creation_datetime`),
  KEY `in_ticketing_correspondance_delivery_datetime` (`delivery_datetime`),
  CONSTRAINT `fk_ticketing_correspondance_contact_id_ticketing_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `ticketing_contact` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_correspondance_customer_group_email_id_tcge_id` FOREIGN KEY (`customer_group_email_id`) REFERENCES `ticketing_customer_group_email` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_correspondance_delivery_status_id_tcds_id` FOREIGN KEY (`delivery_status_id`) REFERENCES `ticketing_correspondance_delivery_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_correspondance_source_id_tc_source_id` FOREIGN KEY (`source_id`) REFERENCES `ticketing_correspondance_source` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_correspondance_ticket_id_ticketing_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `ticketing_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_correspondance_user_id_ticketing_user_id` FOREIGN KEY (`user_id`) REFERENCES `ticketing_user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Correspondances for tickets in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_correspondance`
--

LOCK TABLES `ticketing_correspondance` WRITE;
/*!40000 ALTER TABLE `ticketing_correspondance` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_correspondance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_correspondance_delivery_status`
--

DROP TABLE IF EXISTS `ticketing_correspondance_delivery_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_correspondance_delivery_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the status',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The delivery status of a ticketing system correspondance';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_correspondance_delivery_status`
--

LOCK TABLES `ticketing_correspondance_delivery_status` WRITE;
/*!40000 ALTER TABLE `ticketing_correspondance_delivery_status` DISABLE KEYS */;
INSERT INTO `ticketing_correspondance_delivery_status` VALUES (1,'Received','Received','TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED'),(2,'Not Sent','Not Sent','TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT'),(3,'Sent','Sent','TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT');
/*!40000 ALTER TABLE `ticketing_correspondance_delivery_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_correspondance_source`
--

DROP TABLE IF EXISTS `ticketing_correspondance_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_correspondance_source` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the source',
  `description` varchar(255) NOT NULL COMMENT 'Description of the source',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The source of a ticketing system correspondance';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_correspondance_source`
--

LOCK TABLES `ticketing_correspondance_source` WRITE;
/*!40000 ALTER TABLE `ticketing_correspondance_source` DISABLE KEYS */;
INSERT INTO `ticketing_correspondance_source` VALUES (0,'XML','XML','TICKETING_CORRESPONDANCE_SOURCE_XML'),(1,'Email','Email','TICKETING_CORRESPONDANCE_SOURCE_EMAIL'),(2,'Web','Web','TICKETING_CORRESPONDANCE_SOURCE_WEB'),(3,'Phone','Phone','TICKETING_CORRESPONDANCE_SOURCE_PHONE');
/*!40000 ALTER TABLE `ticketing_correspondance_source` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_customer_group_config`
--

DROP TABLE IF EXISTS `ticketing_customer_group_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_customer_group_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
  `acknowledge_email_receipts` smallint(5) unsigned NOT NULL DEFAULT '2' COMMENT 'FK to active_status; Whether or not to acknowledge email receipts',
  `email_receipt_acknowledgement` mediumtext COMMENT 'The body of the email sent to acknowledge an email receipt',
  `default_email_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_customer_group_email table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ticketing_customer_group_config_customer_group_id` (`customer_group_id`),
  KEY `fk_ticketing_customer_group_config_acknowledge_email_receipts` (`acknowledge_email_receipts`),
  KEY `fk_ticketing_customer_group_config_default_email_id_tcge_id` (`default_email_id`),
  CONSTRAINT `fk_ticketing_customer_group_config_acknowledge_email_receipts` FOREIGN KEY (`acknowledge_email_receipts`) REFERENCES `active_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_customer_group_config_customer_group_id_cg_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_customer_group_config_default_email_id_tcge_id` FOREIGN KEY (`default_email_id`) REFERENCES `ticketing_customer_group_email` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attachments to correspondances in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_customer_group_config`
--

LOCK TABLES `ticketing_customer_group_config` WRITE;
/*!40000 ALTER TABLE `ticketing_customer_group_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_customer_group_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_customer_group_email`
--

DROP TABLE IF EXISTS `ticketing_customer_group_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_customer_group_email` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `customer_group_id` bigint(20) NOT NULL COMMENT 'FK to the CustomerGroup table',
  `email` varchar(255) NOT NULL COMMENT 'Email address for accepted emails',
  `name` varchar(255) NOT NULL COMMENT 'Email name for outbound emails',
  `auto_reply` smallint(5) unsigned NOT NULL COMMENT 'FK to active_status table',
  `archived_on_datetime` datetime DEFAULT NULL COMMENT 'Time at which this record was archived',
  PRIMARY KEY (`id`),
  KEY `fk_ticketing_customer_group_email_customer_group_id_cg_id` (`customer_group_id`),
  KEY `fk_ticketing_customer_group_email_auto_reply_active_status_id` (`auto_reply`),
  CONSTRAINT `fk_ticketing_customer_group_email_auto_reply_active_status_id` FOREIGN KEY (`auto_reply`) REFERENCES `active_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_customer_group_email_customer_group_id_cg_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attachments to correspondances in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_customer_group_email`
--

LOCK TABLES `ticketing_customer_group_email` WRITE;
/*!40000 ALTER TABLE `ticketing_customer_group_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_customer_group_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_priority`
--

DROP TABLE IF EXISTS `ticketing_priority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_priority` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the priority',
  `description` varchar(255) NOT NULL COMMENT 'Description of the priority',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket priorities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_priority`
--

LOCK TABLES `ticketing_priority` WRITE;
/*!40000 ALTER TABLE `ticketing_priority` DISABLE KEYS */;
INSERT INTO `ticketing_priority` VALUES (1,'Low','Low priority','TICKETING_PRIORITY_LOW'),(2,'Medium','Medium priority','TICKETING_PRIORITY_MEDIUM'),(3,'High','High priority','TICKETING_PRIORITY_HIGH'),(4,'Urgent','Urgent priority','TICKETING_PRIORITY_URGENT');
/*!40000 ALTER TABLE `ticketing_priority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_status`
--

DROP TABLE IF EXISTS `ticketing_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_status` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the status',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  `status_type_id` bigint(20) unsigned NOT NULL COMMENT 'FK into ticketing_status_type table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`),
  KEY `fk_ticketing_status_status_type_id_ticketing_status_type_id` (`status_type_id`),
  CONSTRAINT `fk_ticketing_status_status_type_id_ticketing_status_type_id` FOREIGN KEY (`status_type_id`) REFERENCES `ticketing_status_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket workflow statuses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_status`
--

LOCK TABLES `ticketing_status` WRITE;
/*!40000 ALTER TABLE `ticketing_status` DISABLE KEYS */;
INSERT INTO `ticketing_status` VALUES (1,'Unassigned','Not yet assigned to anyone','TICKETING_STATUS_UNASSIGNED',1),(2,'With Customer','Awaiting response from customer','TICKETING_STATUS_WITH_CUSTOMER',2),(3,'With Carrier','Awaiting response from carrier','TICKETING_STATUS_WITH_CARRIER',2),(4,'In Progress','Currently being worked on','TICKETING_STATUS_IN_PROGRESS',2),(5,'Completed','The issue has been resolved','TICKETING_STATUS_COMPLETED',3),(6,'Deleted','The ticket has been deleted','TICKETING_STATUS_DELETED',3),(7,'Assigned','Assigned to someone, but not started','TICKETING_STATUS_ASSIGNED',1),(8,'With Internal','Awaiting response from internal source','TICKETING_STATUS_WITH_INTERNAL',2);
/*!40000 ALTER TABLE `ticketing_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_status_type`
--

DROP TABLE IF EXISTS `ticketing_status_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_status_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `name` varchar(255) NOT NULL COMMENT 'Name of the status type',
  `description` varchar(255) NOT NULL COMMENT 'Description of the status type',
  `const_name` varchar(255) NOT NULL COMMENT 'the constant name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Types of ticket statuses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_status_type`
--

LOCK TABLES `ticketing_status_type` WRITE;
/*!40000 ALTER TABLE `ticketing_status_type` DISABLE KEYS */;
INSERT INTO `ticketing_status_type` VALUES (1,'Pending','Pending Opening','TICKETING_STATUS_TYPE_PENDING'),(2,'Open','Open','TICKETING_STATUS_TYPE_OPEN'),(3,'Closed','Closed','TICKETING_STATUS_TYPE_CLOSED');
/*!40000 ALTER TABLE `ticketing_status_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_ticket`
--

DROP TABLE IF EXISTS `ticketing_ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_ticket` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `group_ticket_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to ticket that this ticket belongs to',
  `subject` varchar(255) NOT NULL COMMENT 'Name of the category',
  `priority_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_priority table',
  `owner_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_user table',
  `contact_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_contact table; Primary contact for ticket.',
  `status_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_status table',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
  `account_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the Account table',
  `category_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_category table',
  `creation_datetime` datetime DEFAULT NULL COMMENT 'Date/Time that the ticket was created',
  `modified_datetime` datetime DEFAULT NULL COMMENT 'Date/Time that the ticket was modified',
  `modified_by_user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into ticketing_user; the user who last modified the ticket',
  PRIMARY KEY (`id`),
  KEY `fk_ticketing_ticket_modified_by_user_id_ticketing_user_id` (`modified_by_user_id`),
  KEY `fk_ticketing_ticket_group_ticket_id_ticketing_ticket_id` (`group_ticket_id`),
  KEY `fk_ticketing_ticket_priority_id_ticketing_priority_id` (`priority_id`),
  KEY `fk_ticketing_ticket_owner_id_ticketing_user_id` (`owner_id`),
  KEY `fk_ticketing_ticket_contact_id_ticketing_contact_id` (`contact_id`),
  KEY `fk_ticketing_ticket_status_id_ticketing_status_id` (`status_id`),
  KEY `fk_ticketing_ticket_customer_group_id_customer_group_id` (`customer_group_id`),
  KEY `fk_ticketing_ticket_account_id_account_id` (`account_id`),
  KEY `fk_ticketing_ticket_category_id_ticketing_category_id` (`category_id`),
  KEY `in_ticketing_ticket_creation_datetime` (`creation_datetime`),
  KEY `in_ticketing_ticket_modified_datetime` (`modified_datetime`),
  CONSTRAINT `fk_ticketing_ticket_account_id_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_category_id_ticketing_category_id` FOREIGN KEY (`category_id`) REFERENCES `ticketing_category` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_contact_id_ticketing_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `ticketing_contact` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_customer_group_id_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_group_ticket_id_ticketing_ticket_id` FOREIGN KEY (`group_ticket_id`) REFERENCES `ticketing_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_modified_by_user_id_ticketing_user_id` FOREIGN KEY (`modified_by_user_id`) REFERENCES `ticketing_user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_owner_id_ticketing_user_id` FOREIGN KEY (`owner_id`) REFERENCES `ticketing_user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_priority_id_ticketing_priority_id` FOREIGN KEY (`priority_id`) REFERENCES `ticketing_priority` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_status_id_ticketing_status_id` FOREIGN KEY (`status_id`) REFERENCES `ticketing_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tickets in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_ticket`
--

LOCK TABLES `ticketing_ticket` WRITE;
/*!40000 ALTER TABLE `ticketing_ticket` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_ticket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_ticket_contact`
--

DROP TABLE IF EXISTS `ticketing_ticket_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_ticket_contact` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `ticket_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to ticketing_ticket',
  `contact_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to ticketing_contact',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ticketing_ticket_contact_ticket_id_contact_id` (`ticket_id`,`contact_id`),
  KEY `fk_ticketing_ticket_contact_contact_id_ticketing_contact_id` (`contact_id`),
  CONSTRAINT `fk_ticketing_ticket_contact_contact_id_ticketing_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `ticketing_contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_contact_ticket_id_ticketing_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `ticketing_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contacts for tickets in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_ticket_contact`
--

LOCK TABLES `ticketing_ticket_contact` WRITE;
/*!40000 ALTER TABLE `ticketing_ticket_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_ticket_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_ticket_history`
--

DROP TABLE IF EXISTS `ticketing_ticket_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_ticket_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `ticket_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticket table',
  `group_ticket_id` bigint(20) unsigned NOT NULL COMMENT 'FK to ticket that this ticket belongs to',
  `subject` varchar(50) NOT NULL COMMENT 'Name of the category',
  `priority_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_priority table',
  `owner_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the ticketing_user table',
  `contact_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_contact table',
  `status_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_status table',
  `customer_group_id` bigint(20) DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
  `account_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the Account table',
  `category_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_category table',
  `creation_datetime` datetime DEFAULT NULL COMMENT 'Date/Time that the ticket was created',
  `modified_datetime` datetime DEFAULT NULL COMMENT 'Date/Time that the ticket was modified',
  `modified_by_user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK into ticketing_user; the user who modified the ticket',
  PRIMARY KEY (`id`),
  KEY `fk_ticketing_ticket_history_modified_by_user_id_ticketing_user` (`modified_by_user_id`),
  KEY `fk_ticketing_ticket_history_ticket_id_ticketing_ticket_id` (`ticket_id`),
  KEY `fk_ticketing_ticket_history_group_ticket_id_ticketing_ticket_id` (`group_ticket_id`),
  KEY `fk_ticketing_ticket_history_priority_id_ticketing_priority_id` (`priority_id`),
  KEY `fk_ticketing_ticket_history_owner_id_ticketing_user_id` (`owner_id`),
  KEY `fk_ticketing_ticket_history_contact_id_ticketing_contact_id` (`contact_id`),
  KEY `fk_ticketing_ticket_history_status_id_ticketing_status_id` (`status_id`),
  KEY `fk_ticketing_ticket_history_customer_group_id_customer_group_id` (`customer_group_id`),
  KEY `fk_ticketing_ticket_history_account_id_account_id` (`account_id`),
  KEY `fk_ticketing_ticket_history_category_id_ticketing_category_id` (`category_id`),
  KEY `in_ticketing_ticket_history_creation_datetime` (`creation_datetime`),
  KEY `in_ticketing_ticket_history_modified_datetime` (`modified_datetime`),
  CONSTRAINT `fk_ticketing_ticket_history_account_id_account_id` FOREIGN KEY (`account_id`) REFERENCES `Account` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_category_id_ticketing_category_id` FOREIGN KEY (`category_id`) REFERENCES `ticketing_category` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_contact_id_ticketing_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `ticketing_contact` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_customer_group_id_customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `CustomerGroup` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_group_ticket_id_ticketing_ticket_id` FOREIGN KEY (`group_ticket_id`) REFERENCES `ticketing_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_modified_by_user_id_ticketing_user` FOREIGN KEY (`modified_by_user_id`) REFERENCES `ticketing_user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_owner_id_ticketing_user_id` FOREIGN KEY (`owner_id`) REFERENCES `ticketing_user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_priority_id_ticketing_priority_id` FOREIGN KEY (`priority_id`) REFERENCES `ticketing_priority` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_status_id_ticketing_status_id` FOREIGN KEY (`status_id`) REFERENCES `ticketing_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_history_ticket_id_ticketing_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `ticketing_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='History of tickets in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_ticket_history`
--

LOCK TABLES `ticketing_ticket_history` WRITE;
/*!40000 ALTER TABLE `ticketing_ticket_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_ticket_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_ticket_service`
--

DROP TABLE IF EXISTS `ticketing_ticket_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_ticket_service` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `ticket_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_ticket table',
  `service_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the Service table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ticketing_ticket_service_ticket_id_service_id` (`ticket_id`,`service_id`),
  KEY `fk_ticketing_ticket_service_service_id_service_id` (`service_id`),
  CONSTRAINT `fk_ticketing_ticket_service_service_id_service_id` FOREIGN KEY (`service_id`) REFERENCES `Service` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_ticket_service_ticket_id_ticketing_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `ticketing_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Services associated with tickets in the ticketing system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_ticket_service`
--

LOCK TABLES `ticketing_ticket_service` WRITE;
/*!40000 ALTER TABLE `ticketing_ticket_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_ticket_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_user`
--

DROP TABLE IF EXISTS `ticketing_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
  `employee_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the Employee table',
  `permission_id` bigint(20) unsigned NOT NULL COMMENT 'FK to the ticketing_user_permission table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ticketing_user_employee_id` (`employee_id`),
  KEY `fk_ticketing_user_permission_id_ticketing_user_permission_id` (`permission_id`),
  CONSTRAINT `fk_ticketing_user_employee_id_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketing_user_permission_id_ticketing_user_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `ticketing_user_permission` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Categories of ticketing system tickets';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_user`
--

LOCK TABLES `ticketing_user` WRITE;
/*!40000 ALTER TABLE `ticketing_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticketing_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticketing_user_permission`
--

DROP TABLE IF EXISTS `ticketing_user_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticketing_user_permission` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id for the table',
  `name` varchar(50) NOT NULL COMMENT 'Name of the permission',
  `description` varchar(255) NOT NULL COMMENT 'Description of the permission',
  `const_name` varchar(255) NOT NULL COMMENT 'The constant name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User permission level (user or admin)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticketing_user_permission`
--

LOCK TABLES `ticketing_user_permission` WRITE;
/*!40000 ALTER TABLE `ticketing_user_permission` DISABLE KEYS */;
INSERT INTO `ticketing_user_permission` VALUES (0,'None','Not a ticketing system user','TICKETING_USER_PERMISSION_NONE'),(1,'User','Ticketing system user','TICKETING_USER_PERMISSION_USER'),(2,'Administrator','Ticketing system administrator','TICKETING_USER_PERMISSION_ADMIN'),(3,'External User','Ticketing system external user','TICKETING_USER_PERMISSION_USER_EXTERNAL');
/*!40000 ALTER TABLE `ticketing_user_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_nature`
--

DROP TABLE IF EXISTS `transaction_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_nature` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` varchar(45) NOT NULL,
  `system_name` varchar(45) NOT NULL,
  `const_name` varchar(45) NOT NULL,
  `code` enum('CR','DR') NOT NULL,
  `value_multiplier` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_nature`
--

LOCK TABLES `transaction_nature` WRITE;
/*!40000 ALTER TABLE `transaction_nature` DISABLE KEYS */;
INSERT INTO `transaction_nature` VALUES (1,'Debit','Debit','DEBIT','TRANSACTION_NATURE_DEBIT','DR',1),(2,'Credit','Credit','CREDIT','TRANSACTION_NATURE_CREDIT','CR',-1);
/*!40000 ALTER TABLE `transaction_nature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Unique Id for this user role',
  `name` varchar(255) NOT NULL COMMENT 'name of the user role',
  `description` varchar(255) NOT NULL COMMENT 'description of the user role',
  `const_name` varchar(255) NOT NULL COMMENT 'constant name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines the various User Roles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
INSERT INTO `user_role` VALUES (1,'Flex Admin','Flex System Administrator','USER_ROLE_FLEX_ADMIN'),(2,'Manager','Manager','USER_ROLE_MANAGER'),(3,'Team Leader','Team Leader','USER_ROLE_TEAM_LEADER'),(4,'Customer Service Representative','Customer Service Representative','USER_ROLE_CUSTOMER_SERVICE_REPRESENTATIVE'),(5,'Credit Control Manager','Credit Control Manager','USER_ROLE_CREDIT_CONTROL_MANAGER'),(6,'Sales','Sales','USER_ROLE_SALES'),(7,'Admin Manager','Admin Manager','USER_ROLE_ADMIN_MANAGER'),(8,'Credit Control','Credit Control','USER_ROLE_CREDIT_CONTROL'),(9,'Accounts','Accounts','USER_ROLE_ACCOUNTS'),(10,'Admin-Sales Support','Admin-Sales Support','USER_ROLE_ADMIN_SALES_SUPPORT'),(11,'Customer Service Supervisor','Customer Service Supervisor','USER_ROLE_CUSTOMER_SERVICE_SUPERVISOR'),(12,'Trainer','Trainer','USER_ROLE_TRAINER'),(13,'Wholesale Manager','Wholesale Manager','USER_ROLE_WHOLESALE_MANAGER');
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `working_status`
--

DROP TABLE IF EXISTS `working_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `working_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system_name` varchar(256) NOT NULL,
  `const_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `working_status`
--

LOCK TABLES `working_status` WRITE;
/*!40000 ALTER TABLE `working_status` DISABLE KEYS */;
INSERT INTO `working_status` VALUES (1,'Draft','Draft','DRAFT','WORKING_STATUS_DRAFT'),(2,'Active','Active','ACTIVE','WORKING_STATUS_ACTIVE'),(3,'Inactive','Inactive','INACTIVE','WORKING_STATUS_INACTIVE');
/*!40000 ALTER TABLE `working_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `account_services`
--

/*!50001 DROP TABLE IF EXISTS `account_services`*/;
/*!50001 DROP VIEW IF EXISTS `account_services`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ybs_admin`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `account_services` AS select `Service`.`Account` AS `account_id`,`Service`.`Id` AS `service_id`,`Service`.`FNN` AS `fnn` from (`Service` join `current_service_account` on(((`Service`.`Account` = `current_service_account`.`accountId`) and (`Service`.`Id` = `current_service_account`.`serviceId`) and (`Service`.`Status` in (400,402,403))))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `current_service_account`
--

/*!50001 DROP TABLE IF EXISTS `current_service_account`*/;
/*!50001 DROP VIEW IF EXISTS `current_service_account`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ybs_admin`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `current_service_account` AS select max(`Service`.`Id`) AS `serviceId`,max(`Account`.`Id`) AS `accountId` from (`Service` join `Account`) where ((`Account`.`Id` = `Service`.`Account`) and (isnull(`Service`.`ClosedOn`) or (now() < `Service`.`ClosedOn`)) and (`Service`.`CreatedOn` < now())) group by `Account`.`Id`,`Service`.`FNN` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-09-09 11:52:42
