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

	const DEBUG_LOGGING = true;
	
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
		Log::getLog()->logIf(self::DEBUG_LOGGING, "[*] ".($bDebit ? 'Debit' : 'Credit'));
		
		foreach($aPayables as $iKey => $oPayable) {
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".get_class($oPayable)." #{$oPayable->id} (".$oPayable->getBalance().'/'.$oPayable->getAmount().')');
			if ($this->balance == 0) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [~] Skipped: No Distributable Balance remaining");
				break;
			}
			if (!$bDebit && $oPayable->getBalance() > 0) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] Crediting");
				$oPayable->processDistributable($this);
				if ($oPayable->getBalance() == 0) {
					Log::getLog()->logIf(self::DEBUG_LOGGING, "      [*] Payable's Credit Balance depleted");
					unset($aPayables[$iKey]);
				}

			} else if ($bDebit && $oPayable->getBalance() < $oPayable->getAmount()) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] Debiting");
				$oPayable->processDistributable($this);
				if ($oPayable->getBalance() == $oPayable->getAmount()) {
					Log::getLog()->logIf(self::DEBUG_LOGGING, "      [*] Payable's Debit Balance depleted");
					unset($aPayables[$iKey]);
				}
			}
			
		}
	}

}
?>
