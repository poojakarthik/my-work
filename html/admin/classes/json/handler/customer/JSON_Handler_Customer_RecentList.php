<?php

class JSON_Handler_Customer_RecentList extends JSON_Handler
{

	public function getRecentAccounts() {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

			$iEmployeeId = AuthenticatedUser()->_arrUser['Id'];

			$aCustomer = array();

			$mResult = Query::run("
				SELECT			a.Id AS account_id,
								a.BusinessName AS business_name,
									a.TradingName AS trading_name,
								MAX(eal.viewed_on) AS viewed_on

				FROM			employee_account_log eal
									JOIN Account a ON (a.Id = eal.account_id)

				WHERE			eal.employee_id = {$iEmployeeId}

				GROUP BY		eal.account_id
				ORDER BY		viewed_on DESC

				LIMIT			20;");

			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aCustomer[] = $aRow;
				}
			}

			return $aCustomer;

		} catch (Exception $oException) {
			return array(	'Success'		=> false,
							'ErrorMessage'	=> $oException->getMessage());
		}
	}

}

?>