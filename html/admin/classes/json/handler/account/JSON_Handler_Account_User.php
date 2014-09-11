<?php

class JSON_Handler_Account_User extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	
	public function getDataset($bCountOnly, $iLimit, $iOffset, $oSort, $oFilter) {
		$iRecordCount = self::_getDataset(true, $iLimit, $iOffset, $oSort, $oFilter);
		if ($bCountOnly) {
			return array('iRecordCount' => $iRecordCount);
		}
		
		$iLimit		= ($iLimit === null ? 0 : $iLimit);
		$iOffset	= ($iOffset === null ? 0 : $iOffset);
		$aData	 	= self::_getDataset(false, $iLimit, $iOffset, $oSort, $oFilter);
		$aResults	= array();
		$i			= $iOffset;
		
		foreach ($aData as $aRecord) {
			$aResults[$i] = $aRecord;
			$i++;
		}
		
		return array(
			'aRecords'		=> $aResults,
			'iRecordCount'	=> $iRecordCount
		);
	}
	
	public function save($oDetails) {		
		// Validation
		$oExceptionSet = new Exception_Set();
		if (!$oDetails->username || empty($oDetails->username)) {
			$oExceptionSet->push("Username must be supplied");
		} else if (!$oDetails->id && !self::_isUniqueUsername($oDetails->username)) {
			$oExceptionSet->push("Username is already in use, please choose another");
		}
		
		if (!$oDetails->given_name || empty($oDetails->given_name)) {
			$oExceptionSet->push("Given Name must be supplied");
		}
		
		if (!$oDetails->account_id || empty($oDetails->account_id)) {
			$oExceptionSet->push("Account Id must be supplied");
		}
		
		if (!$oDetails->email || empty($oDetails->email)) {
			$oExceptionSet->push("Email Address must be supplied");
		} else if (!EmailAddressValid($oDetails->email)) {
			$oExceptionSet->push("Invalid Email Address supplied");
		}
		
		if ($oDetails->new_password) {
			if ($oDetails->new_password != $oDetails->confirm_password) {
				$oExceptionSet->push("Password confirmation must match the new password");
			}
		}
		
		if (!$oExceptionSet->isEmpty()) {
			throw $oExceptionSet;
		}
		
		if ($oDetails->id) {
			// Existing user
			$oAccountUser 				= Account_User::getForId($oDetails->id);
			$oAccountUser->status_id 	= (int)$oDetails->status_id;
		} else {
			// New user
			$oAccountUser 				= new Account_User();
			$oAccountUser->status_id 	= STATUS_ACTIVE;
			$oAccountUser->account_id 	= (int)$oDetails->account_id;
			$oAccountUser->username 	= $oDetails->username;
		}
		
		$oAccountUser->given_name 	= $oDetails->given_name;
		$oAccountUser->family_name 	= $oDetails->family_name;
		$oAccountUser->email 		= $oDetails->email;
		
		if (($oDetails->id && $oDetails->change_password) || (!$oDetails->id && $oDetails->new_password)) {
			$oAccountUser->password = sha1($oDetails->new_password);
		}
		
		$oAccountUser->save();
	}
	
	public function getForId($iId) {
		return Account_User::getForId($iId)->toArray();
	}
	
	public function checkUsername($sUsername) {
		return array('bUnique' => self::_isUniqueUsername($sUsername));
	}
	
	private static function _getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$aAliases = array(
			'id'			=> "au.id",
			'username'		=> "au.username",
			'given_name'	=> "au.given_name",
			'family_name'	=> "au.family_name",
			'email'			=> "au.email",
			'status_id'		=> "au.status_id",
			'status_name'	=> "s.name"
		);
		
		$sFrom = "	account_user au
					JOIN status s ON (s.id = au.status_id)";
		if ($bCountOnly) {
			$sSelect 	= "COUNT(au.id) AS count";
			$sOrderBy	= "";
			$sLimit		= "";
		} else {
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause) {
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect	= implode(', ', $aSelectLines);
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere	= $aWhere['sClause'];
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false) {
			throw new Exception_Database("Failed to get search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly) {
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
	}
	
	private static function _isUniqueUsername($sUsername) {
		$aRow = Query::run("SELECT	id
							FROM	account_user
							WHERE	username = <username>",
							array('username' => $sUsername))->fetch_assoc();
		return ($aRow ? false : true);
	}
}

?>