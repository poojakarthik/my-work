<?php
/**
 * Description of Collection_Logic_Severity
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Severity implements DataLogic
{
    protected $oDO;

    /*
     *  Array of collection_restriction ORMs
     */
    protected $aRestrictions;

	/*
     *	Array of collection_warning ORMs
     */
    protected $aWarnings;





    public function __construct($mDefinition)
    {
        if (is_numeric($mDefinition))
        {
            //implement
        }
        else if (get_class($mDefinition) == 'Collection_Severity')
        {
            $this->oDO = $mDefinition;
        }
        else
        {
            throw new Exception('bad parameter passed into Collection_Severity constructor');
        }
    }

    public function getRestrictions()
    {
      	if ($this->aRestrictions === null)
    	{
    		$aSeverityRestrictions	= Collection_Severity_Restriction::getForSeverityId($this->id);
    		$aRestrictions			= array();
    		foreach ($aSeverityRestrictions as $oRecord)
    		{
    			if ($oRecord->id !== null)
    			{
    				$aRestrictions[] = $oRecord->getRestriction();
    			}
    		}
    		$this->aRestrictions = $aRestrictions;
    	}
       	return $this->aRestrictions;
    }

    public function hasRestriction($iRestrictionId)
    {
        $aRestrictions = $this->getRestrictions();
        foreach($aRestrictions as $oRestriction)
        {
            if ($oRestriction->id == $iRestrictionId)
            {
            	return true;
            }
        }
        return false;
    }

    public function getWarnings()
    {
		if ($this->aWarnings === null)
		{
	    	$aSeverityWarnings	= Collection_Severity_Warning::getForSeverityId($this->id);
    		$aWarnings			= array();
    		foreach ($aSeverityWarnings as $oRecord)
    		{
    			if ($oRecord->id !== null)
    			{
    				$aWarnings[] = $oRecord->getWarning();
    			}
    		}
    		$this->aWarnings = $aWarnings;
		}
       	return $this->aWarnings;
    }

    public static function getForAccount($oAccount)
    {
        return self::getForId($oAccount->collection_severity_id);
    }

    public static function getForId($iId)
    {
        return new self(Collection_Severity::getForId($iId));
    }

    public function toArray()
	{
		return $this->oDO->toArray();
	}

	public function __get($sField)
	{
    	return $this->oDO->$sField;
	}
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$sFrom	= "			collection_severity cs
					JOIN	working_status ws ON (ws.id = cs.working_status_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(cs.id) AS count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "cs.*, ws.name AS working_status_name";	
			$sOrderBy =	Statement::generateOrderBy(
							array(
								'id' 			=> 'cs.id', 
								'name' 			=> 'cs.name', 
								'description' 	=> 'cs.description'
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
			throw new Exception_Database("Failed to get Severities. ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']] = $aRow;
		}
		
		return $aResults;
	}

    public function __set($sField, $mValue)
    {
        $this->oDO->$sField = $mValue;
    }

    public function save() {

    }
}
?>
