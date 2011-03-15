<?php

class Logic_Collection_Event_Type
{
	protected $oDO;
    public function __construct($mDefinition)
    {
        if (is_numeric($mDefinition))
    	{
            $this->oDO = Collection_Event_Type::getForId($mDefinition);
    	}
    	else if ($mDefinition instanceof Collection_Event_Type)
    	{
            $this->oDO = $mDefinition;
    	}
    	else
    	{
            throw new Exception("Invalid definition passed to ".get_class($this));
    	}
    }
	
	public function __get($sField) 
    {
    	switch ($sField)
    	{
    		default:
    			return $this->oDO->$sField;
    	}
        
    }

    public function __set($sField, $mValue) 
    {
        $this->oDO->$sField = $mValue;
    }
    
    public function save() 
    {
        $this->oDO->save();
    }
    
    public function toArray() 
    {
		return $this->oDO->toArray();
    }
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$sFrom	= "	collection_event_type cet
					JOIN		collection_event_type_implementation ceti ON (ceti.id = cet.collection_event_type_implementation_id)
					LEFT JOIN	collection_event_invocation cei ON (cei.id = cet.collection_event_invocation_id)
					JOIN		status s ON (s.id = cet.status_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(cet.id) AS event_type_count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "cet.*, 
						ceti.name AS collection_event_type_implementation_name, 
						cei.name AS collection_event_invocation_name, 
						s.name AS status_name";	
			$sOrderBy =	Statement::generateOrderBy(
							array(
								'id' 			=> 'cet.id', 
								'name' 			=> 'cet.name', 
								'description' 	=> 'cet.description', 
								'system_name' 	=> 'cet.system_name'
							), 
							get_object_vars($oSort)
						);
			$sLimit = Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere 	= Statement::generateWhere(null, get_object_vars($oFilter));
		$oSelect	=	new StatementSelect(
							$sFrom, 
							$sSelect, 
							$aWhere['sClause'], 
							$sOrderBy, 
							$sLimit
						);
		
		if ($oSelect->Execute($aWhere['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to get Event Types. ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['event_type_count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']] = $aRow;
		}
		
		return $aResults;
	}
}

?>