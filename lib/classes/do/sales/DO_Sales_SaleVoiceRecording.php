<?php

class DO_Sales_SaleVoiceRecording extends DO_Sales_Base_SaleVoiceRecording
{
	// Returns the number of recordings attached to a sale
	public static function countForSale($doSale)
	{
		return self::countFor(array("saleId"=>$doSale->id));
	}
	
}

?>