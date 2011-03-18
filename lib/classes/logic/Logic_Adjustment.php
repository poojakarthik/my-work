<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Adjustment
 *
 * @author JanVanDerBreggen
 */
class Logic_Adjustment implements DataLogic, Logic_Distributable{

    const DEBIT = 0;
    const CREDIT = 1;
    
     protected $oDO;
     protected $iSignType;
      public $iMultiplier;

    public function __construct($mDefinition)
    {
        $this->oDO = $mDefinition;

       $aSignType = $this->oDO->getSignType();
       $this->iSignType = $aSignType['sign'];
       $this->iMultiplier = $aSignType['multiplier'];

    }

    public static function getForId($iId)
    {
        $oAdjustment = Adjustment::getForId($iId);
        return $oAdjustment!== null ? new self($oAdjustment) : null;
    }

    public static function getForAccount($oAccount, $iSignType)
    {
        $aORM = Adjustment::getForAccountId($oAccount->id, $iSignType);
        $aResult = array();
        foreach ($aORM as $oORM)
        {
            $aResult[] = new self($oORM);
        }
        return $aResult;
    }

    public function getMultiplier()
    {
        return $this->iMultiplier;
    }
	
	public function reverse($iReversalReasonId)
	{
		$oDataAccess = DataAccess::getDataAccess();
		try
		{
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception("Failed to start transaction");
			}
			
			$oReversal	= $this->oDO->reverse($iReversalReasonId);
			$oAccount	= Logic_Account::getInstance($this->oDO->account_id);
			$oAccount->processDistributable(new Logic_Adjustment($oReversal));
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception("Failed to commit transaction");
			}
		}
		catch (Exception $oEx)
		{
			$oDataAccess->TransactionRollback();			
			throw $oEx;
		}
	}

   public function __get($sField) {

       switch($sField)
       {
           case 'balance':
           case 'amount':
               return Rate::roundToRatingStandard($this->oDO->$sField, 4);
            default:
                return $this->oDO->$sField;
       }

    }

    public function __set($sField, $mValue) {

        switch($sField)
       {
           case 'balance':
           case 'amount':
               $this->oDO->$sField = Rate::roundToRatingStandard($mValue, 4);
            default:
               $this->oDO->$sField = $mValue;
       }
    }

    public function save() {
        $this->oDO->save();
    }

    public function toArray() {

    }

    public function isCredit() {
        return $this->iSignType == self::CREDIT;
    }

    public function isDebit() {
        return $this->iSignType == self::DEBIT;
    }
    //put your code here
}
?>