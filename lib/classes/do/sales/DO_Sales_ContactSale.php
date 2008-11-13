<?php

class DO_Sales_ContactSale extends DO_Sales_Base_ContactSale
{
	
	/*
	 * function getSaleHistory()
	 *
	 * Returns an array of all sales.
	 */
	static function getSaleHistory()
	{

		$dataSource = self::getDataSource();

		$strSQL = "
			SELECT sa.business_name, sa.trading_name, ct.name AS title, c.first_name, c.last_name, ss.name AS status, ssh.changed_on, d.username AS dealer, s.created_on
							
			FROM contact_sale AS cs
			
			INNER JOIN contact AS c
			ON cs.contact_id = c.id
			
			INNER JOIN sale AS s
			ON cs.sale_id = s.id
			
			INNER JOIN sale_account AS sa
			ON sa.sale_id= s.id
			
			LEFT JOIN contact_title AS ct
			ON ct.id = c.contact_title_id
			
			INNER JOIN sale_status AS ss
			ON ss.id = s.sale_status_id
			
			INNER JOIN sale_status_history AS ssh
			ON ssh.sale_id = s.id
			
			INNER JOIN dealer AS d
			ON d.id = s.created_by";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to get contact methods: " . $result->getMessage());
		}

		$arrSaleHistory = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		return $arrSaleHistory;

	}
	
}

?>