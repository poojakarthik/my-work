<?php

class DO_Sales_SaleAccount extends DO_Sales_Base_SaleAccount
{
	public static function getForSale(DO_Sales_Base_Sale $doSale)
	{
		$arrSaleAccounts		= self::listForFkSaleAccountSaleId($doSale);
		$intCount = count($arrSaleAccounts);
		if ($intCount > 1)
		{
			// Multiple sale_account records relate to $doSale, which should never happen
			throw new Exception(__METHOD__ ." multiple accounts are associated with sale: {$doSale->id}");
		}
		elseif ($intCount == 1)
		{
			// The sale_account record was found
			return $arrSaleAccounts[0];
		}
		else
		{
			// There is no sale_account record
			return NULL;
		}
	}
}

?>