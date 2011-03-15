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
        $oDO->adjustment_id = $oAdjustment->id;
        $oDO->collectable_id = $oCollectable->id;
        if ($oAdjustment->isCredit())
        {
             $oDO->balance = $oCollectable->balance >= $oAdjustment->balance ? $oAdjustment->balance *$iValueMultiplier : $oCollectable->balance * $iValueMultiplier;
        }
        else
        {
             $oDO->balance = ($oCollectable->amount - $oCollectable->balance) >= $oAdjustment->balance ? $oAdjustment->balance *$iValueMultiplier : ($oCollectable->amount - $oCollectable->balance) * $iValueMultiplier;

        }

         if ($fMaxAmount !== null && abs($oDO->balance)> $fMaxAmount)
                $oDO->balance = $fMaxAmount*$iValueMultiplier;

        $oDO->created_datetime = Data_Source_Time::currentTimestamp();
        return new self ($oDO, $oAdjustment, $oCollectable );

                //this query indicates how to determine whether an adjustment is credit or debit
            //        SELECT  (sum(adj.amount + adj.tax) * tn.value_multiplier) * IF(adj.adjustment_nature_id = 1, 1, -1)
            //FROM    adjustment adj
            //JOIN    adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
            //JOIN    transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
            //JOIN    adjustment_review_outcome aro ON (aro.id = adj.adjustment_review_outcome_id)
            //JOIN    adjustment_review_outcome_type arot ON (arot.id = aro.adjustment_review_outcome_type_id)
            //WHERE   adj.account_id = a.Id
            //AND     arot.system_name = 'APPROVED'
            //AND     adjustment_status_id = 2

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
