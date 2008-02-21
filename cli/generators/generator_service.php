<?php
require_once("../../flex.require.php");

$selRandomCustomer	= new StatementSelect("Account", "*", "Archived = 0", "RAND()", 1);
$insService			= new StatementInsert("Service");

$intServices = rand(1, 20);
while (($intCustomerServices = rand(1, $intServices)) > 0)
{
	$selRandomCustomer->Execute();
	$arrCustomer = $selRandomCustomer->Fetch();
	
	$arrService['AccountGroup']		= $arrCustomer['AccountGroup'];
	$arrService['Account']			= $arrCustomer['Id'];
	$arrService['CreatedOn']		= date('Y-m-d');
	$arrService['Carrier']			= CARRIER_UNITEL;
	$arrService['CarrierPreselect']	= CARRIER_OPTUS;
	$arrService['Status']			= SERVICE_ACTIVE;
	
	for ($i = 1; $i < $intServices; $i++)
	{
		switch (rand(0, 20))
		{
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
				// Mobile
				$arrService['FNN']			= '04'.str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
				$arrService['ServiceType']	= SERVICE_TYPE_MOBILE;
				break;
			
			case 18:
				// 13
				$arrService['FNN']			= rand(130000, 139999);
				$arrService['ServiceType']	= SERVICE_TYPE_MOBILE;
				break;
			
			case 19:
				// 1300
				$arrService['FNN']			= rand(1300000000, 1300999999);
				$arrService['ServiceType']	= SERVICE_TYPE_INBOUND;
				break;
			
			case 20:
				// 1800
				$arrService['FNN']			= rand(1800000000, 1800999999);
				$arrService['ServiceType']	= SERVICE_TYPE_INBOUND;
				break;
			
			case 8:
			case 9:
			case 10:
			case 11:
			case 12:
			case 13:
			case 14:
			case 15:
			case 16:
			case 17:
				// Land Line
				switch ($arrCustomer['State'])
				{
					case 'NSW':
					case 'ACT':
						$arrService['FNN']	= '02';
						break;
					
					case 'QLD':
						$arrService['FNN']	= '07';
						break;
					
					case 'SA':
					case 'NT':
					case 'WA':
						$arrService['FNN']	= '08';
						break;
					
					case 'VIC':
					case 'TAS':
						$arrService['FNN']	= '03';
						break;
				}
				$arrService['FNN']			.= str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
				$arrService['ServiceType']	= SERVICE_TYPE_LAND_LINE;
				
				// Indial 100
				if (rand(0, 20) == 10)
				{
					$arrService['Indial100']	= 1;
				}
				else
				{
					$arrService['Indial100']	= 0;
				}
		}
		
		Debug("\tAdding Service #{$arrService['FNN']}...");
		// !!!UNCOMMENT ME!!!
		//$insService->Execute($arrService);
	}
	
	$intServices -= $intCustomerServices;
}
?>