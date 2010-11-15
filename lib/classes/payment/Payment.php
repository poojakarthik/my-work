<?php
/**
 * Payment
 *
 * Represents a Record in the Payment table
 *
 * @class	Payment
 */
class Payment extends ORM_Cached
{
	protected 			$_strTableName			= "Payment";
	protected static	$_strStaticTableName	= "Payment";
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	public function applySurcharges()
	{
		// Get Payment Merchant details
		if ($oCarrierPaymentType = Carrier_Payment_Type::getForCarrierAndPaymentType($this->carrier, $this->PaymentType))
		{
			// Calculate Surcharge
			$fSurcharge	= $oCarrierPaymentType->calculateSurcharge($this->Amount);
			
			// Apply Charge
			$oCharge	= null;
			if ($fSurcharge > 0.0)
			{
				$oChargeType	= Charge_Type::getByCode('PMF');
				
				$oCharge					= new Charge();
				
				$oCharge->AccountGroup		= $this->AccountGroup;
				$oCharge->Account			= $this->Account;
				$oCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
				$oCharge->CreatedOn			= date('Y-m-d');
				$oCharge->ApprovedBy		= Employee::SYSTEM_EMPLOYEE_ID;
				$oCharge->ChargeType		= $oChargeType->ChargeType;
				$oCharge->charge_type_id	= $oChargeType->Id;
				$oCharge->Description		= $oCarrierPaymentType->description
											.' Surcharge for Payment on '.date('d/m/Y', strtotime($this->PaidOn))
											.' of $'.(number_format($this->Amount, 2, '.', ''))
											.' @ '.round($oCarrierPaymentType->surcharge_percent * 100, 2).'%';
				$oCharge->ChargedOn			= $this->PaidOn;
				$oCharge->Nature			= 'DR';
				$oCharge->Amount			= round($fSurcharge, 2);
				$oCharge->LinkType			= CHARGE_LINK_PAYMENT;
				$oCharge->LinkId			= $this->Id;
				$oCharge->Status			= CHARGE_APPROVED;
				$oCharge->Notes				= '';
				$oCharge->global_tax_exempt	= 0;
				
				$oCharge->save();
			}
			
			return $oCharge;
		}
		else
		{
			return null;
		}
	}
	
	// Override
	public function save()
	{
		if ($this->id == NULL)
		{
			// New payment, set the created_datetime value
			$this->created_datetime	= date('Y-m-d H:i:s');
		}
		parent::save();
	}
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>