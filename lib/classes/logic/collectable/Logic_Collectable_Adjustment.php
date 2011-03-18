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
class Logic_Collectable_Adjustment extends Logic_Transfer_Balance implements DataLogic
{

    

    public function getSource()
    {
        if ($this->oSource === null)
            Logic_Adjustment::getForId($this->from_collectable_id);
        return $this->oSource;
    }

    public function getTarget()
    {
        if ($this->oTarget === null)
            Logic_Collectable::getInstance($this->to_collectable_id);
        return $this->oTarget;
    }
    


    public static function create($oAdjustment, $oCollectable, $fMaxAmount = null)
    {
        $iValueMultiplier = $oAdjustment->getMultiplier();
        $oDO = new Collectable_Adjustment();
	$oCollectableAdjustment = new self ($oDO, $oAdjustment, $oCollectable );
        $oDO->adjustment_id = $oAdjustment->id;
        $oDO->collectable_id = $oCollectable->id;
        if ($oAdjustment->isCredit())
        {
             $oCollectableAdjustment->balance = $oCollectable->balance >= $oAdjustment->balance ? $oAdjustment->balance *$iValueMultiplier : $oCollectable->balance * Rate::roundToRatingStandard($iValueMultiplier, 4);
        }
        else
        {
            $oCollectableAdjustment->balance = ($oCollectable->amount - $oCollectable->balance) >= $oAdjustment->balance ? $oAdjustment->balance *$iValueMultiplier : ($oCollectable->amount - $oCollectable->balance) * $iValueMultiplier;

        }

         if ($fMaxAmount !== null && abs($oDO->balance)> $fMaxAmount)
                $oCollectableAdjustment->balance = $fMaxAmount*$iValueMultiplier;

        $oCollectableAdjustment->created_datetime = Data_Source_Time::currentTimestamp();
        return $oCollectableAdjustment;

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
