<?php
class MDB2_Driver {

	// Equivalent to the default MDB2 fetch mode.
	private $_iPDOFetchMode = PDO::FETCH_ASSOC;

	// Convert MDB2 fetchmodes into PDO modes.
	public function getPDOFetchMode($iMDB2FetchMode) {
		switch($iMDB2FetchMode) {
			
			// MDB2 Modes
			case MDB2_FETCHMODE_DEFAULT:
				return $this->_iPDOFetchMode;
				break;
			case MDB2_FETCHMODE_ORDERED:
				return PDO::FETCH_NUM;
				break;
			case MDB2_FETCHMODE_ASSOC:
				return PDO::FETCH_ASSOC;
				break;
			case MDB2_FETCHMODE_OBJECT:
				return PDO::FETCH_OBJ;
				break;

			default:
				throw new Exception('Unimplemented/unknown MDB2 fetchmode: ' . var_export($iMDB2FetchMode, true));

		}
	}

	public function setPDOFetchMode($iFetchMode) {
		$this->_iPDOFetchMode = $this->getPDOFetchMode($iFetchMode);
	}

}