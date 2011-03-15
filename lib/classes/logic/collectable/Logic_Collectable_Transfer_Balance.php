<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collectable_Logic_Transfer
 *
 * @author JanVanDerBreggen
 */
class Logic_Collectable_Transfer_Balance extends Logic_Transfer_Balance implements DataLogic
{
   

    public function getSource()
    {
        if ($this->oSource === null)
            Logic_Collectable::getInstance($this->to_collectable_id);
        return $this->oSource;
    }

    public function getTarget()
    {
        if ($this->oTarget === null)
            Logic_Collectable::getInstance($this->to_collectable_id);
        return $this->oTarget;
    }

    public static function create($oSource, $oTarget, $fMaxAmount = null)
    {       
       

        $oTransfer = new Collectable_Transfer_Balance();
        $oTransfer->from_collectable_id = $oSource->id;
        $oTransfer->to_collectable_id = $oTarget->id;
        $oTransfer->collectable_transfer_type_id = $iType;
        $oTransfer->balance = $oTarget->balance >= abs($oSource->balance) ? $oSource->balance : -$oTarget->balance;
        if ($fMaxAmount!== null && abs($oTransfer->balance) > $fMaxAmount )
               $oTransfer->balance = - $fMaxAmount;
         $oTransfer->created_datetime = Data_Source_Time::currentTimestamp();
        return new self($oTransfer, $oSource, $oTarget);
    }
    

    public function __get($sField) 
    {
        return $this->oDO->$sField;
    }
    
    public function __set($sField, $mValue) 
    {
        $this->oDO->sField = $mValue;
    }  
    
    public function toArray() 
    {

    }
}
?>
