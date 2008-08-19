<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DB_ACCESS
//----------------------------------------------------------------------------//
/**
 * DB_ACCESS
 *
 * Handles DB interaction
 *
 * Handles DB interaction.  Currently limited to MySQL
 *
 * @file		db_access.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// DataAccess
//----------------------------------------------------------------------------//
/**
 * DataAccess
 *
 * Provides connection to the MySQL server
 *
 * Provides connection to the MySQL server
 *
 *
 * @prefix		dba
 *
 * @package		framework
 * @class		DataAccess
 */
 
$thisDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
require_once $thisDir.'../data/model/Flex_Data_Model.php';
require_once $thisDir.'../classes/DataAccess.php';
require_once $thisDir.'../classes/DatabaseAccess.php';
require_once $thisDir.'../classes/Statement.php';
require_once $thisDir.'../classes/Query.php';
require_once $thisDir.'../classes/MySQLFunction.php';


//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
// New database frame work.
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
require_once $thisDir.'../classes/db/DB_Data_Source_Handler.php';
require_once $thisDir.'../classes/db/DB_Database_Data_Source.php';
require_once $thisDir.'../classes/db/DB_Database.php';
require_once $thisDir.'../classes/db/DB_MSSQL_Database.php';
require_once $thisDir.'../classes/db/DB_MySQL_Database.php';
require_once $thisDir.'../classes/db/DB_Postgres_Database.php';
require_once $thisDir.'../classes/db/DB_SOAP_Data_Source.php';


//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
// QUERY CLASSES
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
require_once $thisDir.'../classes/QueryFetch.php';
require_once $thisDir.'../classes/QueryCreate.php';
require_once $thisDir.'../classes/QuerySelectInto.php';
require_once $thisDir.'../classes/QueryCopyTable.php';
require_once $thisDir.'../classes/QueryListTables.php';
require_once $thisDir.'../classes/QueryTruncate.php';
require_once $thisDir.'../classes/QueryDropTable.php';


//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
// STATEMENT CLASSES
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
require_once $thisDir.'../classes/StatementSelect.php';
require_once $thisDir.'../classes/StatementSelectReport.php';
require_once $thisDir.'../classes/StatementInsert.php';
require_once $thisDir.'../classes/StatementUpdate.php';
require_once $thisDir.'../classes/StatementUpdateById.php';

require_once $thisDir.'../classes/VixenWhere.php';


?>
