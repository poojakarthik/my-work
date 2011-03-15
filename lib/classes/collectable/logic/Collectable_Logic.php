<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collectable_Logic
 *
 * @author JanVanDerBreggen
 */
class Collectable_Logic implements DataLogic 
{
    protected $oDO;

    protected $oAccount;

    protected $aFromTransfers;

    protected $aToTransfers;

    protected $aPayments;

    protected $aAdjustments;

    public static function getForAccount($oAccount)
    {

    }

    public function __construct($mDefinition)
    {
        $this->oDO = is_numeric($mDefinition) ? Collectable::getForId($mDefinition) : (get_class($mDefinition) == 'Collectable' ? $mDefinition : null);
    }

    /**
     * if the due date is in the past and the balance > 0 and this is not part of an active promise to pay: return true
     * else return false
     */
    public function isCurrentlyDue()
    {

    }

    public static function getCollectablesForBatchProcess() 
    {
       $aORMs =  Collectable::getCollectablesForBatchProcess();
       $aCollectables = array();
       $aPromises = array();
       foreach($aORMs as $oORM)
       {
            $oCollectable = new self($oORM);           
            if (!in_array($oORM->account_id, array_keys($aCollectables)))
            {
            	$aCollectables[$oORM->account_id] = array();
            }
       		$aCollectables[$oORM->account_id][] = $oCollectable;

       }

       return  $aCollectables;

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
