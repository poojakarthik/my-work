<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author JanVanDerBreggen
 */
abstract class  Logic_Distributable {
	
	abstract public function isDebit();

	abstract public function isCredit();

	/**
	 * Distributes the balance of this distributable to the array of payables passed in.
	 * It is up to the caller of this function to makes sure the payables are sorted correctly:
	 * if $this->isDebit()	: sorted by due date desc
	 * if $this->isCredit() : sorted by due date asc
	 * @param <type> $aPayables 
	 */
	public function distributeToPayables(&$aPayables)
	{
		$bDebit = $this->isDebit();
				
		foreach($aPayables as $iKey => $oPayable)
		{
			if ($this->balance == 0)
				break;
			if (!$bDebit && $oPayable->getBalance() > 0)
			{
				$oPayable->processDistributable($this);
				if ($oPayable->getBalance() == 0)
					unset($aPayables[$iKey]);

			}
			else if ($bDebit && $oPayable->getBalance() < $oPayable->getAmount())
			{
				$oPayable->processDistributable($this);
				if ($oPayable->getBalance() == $oPayable->getAmount())
					unset($aPayables[$iKey]);
			}
			
		}
	}

}
?>
