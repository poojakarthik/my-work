<?php

/**
 * Description of Collection_Logic_Severity
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Severity {

    protected $oDO;

    /*
     *  array of collection_restriction ORMs
     */
    protected $aRestrictions;

     /*
     * array of collection_warning ORMs
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
           	$this->aRestrictions = Collection_Restriction::getForSeverityId($this->id);
       	return $this->aRestrictions;
    }

    public function getWarnings()
    {
		if ($this->aWarnings === null)
       		$this->aWarnings = Collection_Warning::getForSeverityId($this->id);
       	return $this->aWarnings;
    }

    public static function getForId($iId)
    {
        return new self(Collection_Severity::getForId($iId));
    }

	public function save()
	{
	}
	
	public function toArray()
	{
	}
	   
	public function __get($sField)
	{
	}
	
	public function __set($sField, $mValue)
	{
	}  
}
?>
