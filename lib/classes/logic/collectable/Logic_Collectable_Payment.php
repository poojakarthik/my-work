<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collectable_Logic_Adjustment
 *
 * @author JanVanDerBreggen
 */
class Logic_Collectable_Payment extends Logic_Transfer_Balance implements DataLogic
{
 
    

    
    public function getSource()
    {
        if ($this->oSource === null)
            Logic_Payment::getForId($this->from_collectable_id);
        return $this->oSource;
    }

    public function getTarget()
    {
        if ($this->oTarget === null)
                Logic_Collectable::getInstance($this->to_collectable_id);
        return $this->oTarget;
    }

    public function getPaymentNature()
    {
        Payment_Nature::getForId($this->payment_nature_id);
    }


    public static function create($oPayment, $oCollectable, $fMaxAmount = null)
    {

        $iValueMultiplier = $oPayment->getPaymentNature()->value_multiplier;
        $oDO = new Collectable_Payment();
        $oDO->payment_id = $oPayment->id;
        $oDO->collectable_id = $oCollectable->id;
        

        if ($oPayment->isCredit())
        {
             $oDO->balance = $oCollectable->balance <= $oPayment->balance ? $oCollectable->balance*$iValueMultiplier : $oPayment->balance*$iValueMultiplier;
        }
        else
        {
             $oDO->balance = ($oCollectable->amount - $oCollectable->balance) >= $oPayment->balance ? $oPayment->balance *$iValueMultiplier : ($oCollectable->amount - $oCollectable->balance) * $iValueMultiplier;

        }

        if ($fMaxAmount !== null && abs($oDO->balance)> $fMaxAmount)
                $oDO->balance = $fMaxAmount*$iValueMultiplier;

        


        $oDO->created_datetime = Data_Source_Time::currentTimestamp();
        return new self ($oDO, $oPayment, $oCollectable );
    }

    public function __get($sField)
    {
        return $this->oDO->{$sField};
    }

    public function __set($sField, $mValue)
    {
        $this->oDO->{$sField} = $mValue;
    }
    
    public function save()
    {
        $this->oDO->save();
    }
	
    public function toArray()
    {

    }
}
?>
