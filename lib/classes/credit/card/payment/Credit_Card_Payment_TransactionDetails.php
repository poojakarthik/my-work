<?php

class Credit_Card_Payment_TransactionDetails
{
	public $iTime					= null;
	public $fAmount					= null;
	public $fSurcharge				= null;
	public $iAccountId				= null;
	public $fTotal					= null;
	public $fBalanceBefore			= null;
	public $sCardNumber				= null;
	public $sTransactionId			= null;
	public $sPurchaseOrderNumber	= null;
	
	public function __construct($aDetails)
	{
		$this->iTime				= isset($aDetails['iTime']) ? $aDetails['iTime'] : null;
		$this->fAmount				= isset($aDetails['fAmount']) ? $aDetails['fAmount'] : null;
		$this->fSurcharge			= isset($aDetails['fSurcharge']) ? $aDetails['fSurcharge'] : null;
		$this->iAccountId			= isset($aDetails['iAccountId']) ? $aDetails['iAccountId'] : null;
		$this->fTotal				= isset($aDetails['fTotal']) ? $aDetails['fTotal'] : null;
		$this->fBalanceBefore		= isset($aDetails['fBalanceBefore']) ? $aDetails['fBalanceBefore'] : null;
		$this->sCardNumber			= isset($aDetails['sCardNumber']) ? $aDetails['sCardNumber'] : null;
		$this->sTransactionId		= isset($aDetails['sTransactionId']) ? $aDetails['sTransactionId'] : null;
		$this->sPurchaseOrderNumber	= isset($aDetails['sPurchaseOrderNumber']) ? $aDetails['sPurchaseOrderNumber'] : null;
	}
}

?>