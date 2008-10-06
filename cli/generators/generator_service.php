<?php
require_once("../../flex.require.php");

$selCustomerCount		= new StatementSelect("Account", "Id", "Archived = 0");
$intCustomerCount		= $selCustomerCount->Execute();

$insService				= new StatementInsert("Service");
$selPlan				= new StatementSelect("RatePlan", "Id", "ServiceType = <ServiceType>", "RAND()", 1);
$qryServiceRateGroup	= new Query();
$strServiceRateGroup	= "INSERT INTO ServiceRateGroup (Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active)" .
							" SELECT <Service>, RateGroup, 22, CURDATE(), NOW(), '9999-12-31 23:59:59', 1" .
							" FROM RatePlanRateGroup" .
							" WHERE RatePlan = <RatePlan>";
$arrCols = Array();
$arrCols['Service']			= NULL;
$arrCols['RatePlan']		= NULL;
$arrCols['CreatedBy']		= NULL;
$arrCols['CreatedOn']		= new MySQLFunction("CURDATE()");
$arrCols['StartDatetime']	= new MySQLFunction("NOW()");
$arrCols['EndDatetime']		= NULL;
$arrCols['Active']			= NULL;
$insServiceRatePlan		= new StatementInsert("ServiceRatePlan", $arrCols);

$intServices = rand(25, 75);
//while (($intCustomerServices = rand(1, ceil($intServices / 4))) > 0)
for ($i = 0; $i < $intServices; $i++)
{
	$intRandCustomer		= rand(0, $intCustomerCount - 1);
	CliEcho($intRandCustomer);
	$selRandomCustomer              = new StatementSelect("Account", "*", "Archived = 0", NULL, "$intRandCustomer, 1");
	$selRandomCustomer->Execute();
	$arrCustomer = $selRandomCustomer->Fetch();
	
	$arrService['AccountGroup']		= $arrCustomer['AccountGroup'];
	$arrService['Account']			= $arrCustomer['Id'];
	$arrService['CreatedOn']		= date('Y-m-d');
	$arrService['CreatedBy']		= 1;
	$arrService['Carrier']			= CARRIER_UNITEL;
	$arrService['CarrierPreselect']	= CARRIER_OPTUS;
	$arrService['Status']			= SERVICE_ACTIVE;
	$arrService['CappedCharge']		= 0.0;
	$arrService['UncappedCharge']	= 0.0;
	$arrService['Indial100']	= 0;
	$arrService['ForceInvoiceRender']	= 0;
	$arrService['Cost']			= 0.0;
	
	/*for ($i = 1; $i < $intServices; $i++)
	{*/
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
				/*$arrService['FNN']			= rand(130000, 139999);
				$arrService['ServiceType']	= SERVICE_TYPE_MOBILE;
				break;*/
			
			case 19:
				// 1300
				/*$arrService['FNN']			= rand(1300000000, 1300999999);
				$arrService['ServiceType']	= SERVICE_TYPE_INBOUND;
				break;*/
			
			case 20:
				// 1800
				/*$arrService['FNN']			= rand(1800000000, 1800999999);
				$arrService['ServiceType']	= SERVICE_TYPE_INBOUND;
				break;*/
			
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
		}
		
		Debug("\tAdding Service #{$arrService['FNN']}...");
		$intService = $insService->Execute($arrService);
		
		if (!$intService)
		{
			Debug($insService->Error());
			die;
		}
		
		// Add ServiceRatePlan and ServiceRateGroup
		$selPlan->Execute($arrService);
		$arrPlan	= $selPlan->Fetch();
		
		$arrCols = Array();
		$arrCols['Service']			= $intService;
		$arrCols['RatePlan']		= $arrPlan['Id'];
		$arrCols['CreatedBy']		= 22;
		$arrCols['CreatedOn']		= new MySQLFunction("CURDATE()");;
		$arrCols['StartDatetime']	= new MySQLFunction("NOW()");
		$arrCols['EndDatetime']		= '9999-12-31 23:59:59';
		$arrCols['Active']			= 1;
		$insServiceRatePlan->Execute($arrCols);
		
		$strQuery = str_replace('<Service>', $intService, $strServiceRateGroup);
		$strQuery = str_replace('<RatePlan>', $arrPlan['Id'], $strQuery);
		$qryServiceRateGroup->Execute($strQuery);
	//}
	
	//$intServices -= $intCustomerServices;
}
?>
