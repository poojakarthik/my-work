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

	function splitTableSchema($table) {
		$ret = array();
		if (strpos($table, '.') !== false) {
			return explode('.', $table);
		}
		return array(null, $table);
	}

    function queryCol($query, $type = null, $colnum = 0) {
        $result = $this->query($query, $type);
        $col = $result->fetchCol($colnum);
        return $col;
    }


    function getIndexName($idx) {
        return sprintf('%s_idx', preg_replace('/[^a-z0-9_\-\$.]/i', '_', $idx));
    }

	function fixIndexName($idx) {
		$idx_pattern = '/^'.preg_replace('/%s/', '([a-z0-9_]+)', '%s_idx').'$/i';
		$idx_name = preg_replace($idx_pattern, '\\1', $idx);
		if ($idx_name && !strcasecmp($idx, $this->getIndexName($idx_name))) {
			return $idx_name;
		}
		return $idx;
	}

	function queryRow($query, $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT) {
		$result = $this->query($query, $types);
		$row = $result->fetchRow($fetchmode);
		return $row;
	}

}