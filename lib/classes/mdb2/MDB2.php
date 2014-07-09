<?php


define('MDB2_OK', true); // Used by transactions

define('MDB2_FETCHMODE_DEFAULT', 0);
define('MDB2_FETCHMODE_ORDERED', 1);
define('MDB2_FETCHMODE_ASSOC', 2);
define('MDB2_FETCHMODE_OBJECT', 3);
//define('MDB2_FETCHMODE_FLIPPED', 4); // Not implemented: Exception will be thrown if you try to use.

define('MDB2_PORTABILITY_NONE', 0);
define('MDB2_PORTABILITY_FIX_CASE', 1);					// Not allowed: Use of throws exception.
define('MDB2_PORTABILITY_RTRIM', 2);					// Implemented.
define('MDB2_PORTABILITY_DELETE_COUNT', 4);				// Not implemented: MySQL AND PostgreSQL return affected rows.
define('MDB2_PORTABILITY_NUMROWS', 8);					// Not implemented: Oracle only.
define('MDB2_PORTABILITY_ERRORS', 16);					// Not implemented: Not used by this MDB2 Wrapper.
define('MDB2_PORTABILITY_EMPTY_TO_NULL', 32);			// Not allowed: Use of throws exception.
define('MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES', 64);	// Implemented.
define('MDB2_PORTABILITY_ALL', 127);

class MDB2 {

	public static function connect($aDSN, $aOptions=false) {
		// TODO: Validate whether class exists
		$sDriverClassName = "MDB2_Driver_" . $aDSN['phptype'];
		try {
			return new $sDriverClassName($aDSN, $aOptions);
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public static function isError($oInstance, $sCode=null) {
		return ($oInstance instanceof MDB2_Error);
	}

}