<?php
/**
 * Resource_Type_File_Export_OCA_Referral
 *
 * @class	Resource_Type_File_Export_OCA_Referral
 */
abstract class Resource_Type_File_Export_OCA_Referral extends Resource_Type_File_Export
{
	const CARRIER_MODULE_TYPE = MODULE_TYPE_OCA_REFERRAL_FILE;
	
	public function getFileExportRecord()
	{
		return $this->_oFileExport;
	}
	
	public static function exportOCAReferrals($aAccountOCAReferralIds)
	{
		$oCarrierModule = Carrier_Module::getLatestActiveForCarrierModuleType(self::CARRIER_MODULE_TYPE);
		if (!$oCarrierModule)
		{
			throw new Exception("Failed to find latest active oca referral carrier module.");
		}
		
		Log::getLog()->log("\nResource type handler {$oCarrierModule->Module}");
		
		$oDataAccess = DataAccess::getDataAccess();
		if ($oDataAccess->TransactionStart() === false)
		{
			throw new Exception("Failed to START db transaction for customer group {$oCarrierModule->customer_group}");
		}
		Log::getLog()->log("Transaction started");
		
		// Create the file export resource type
		$sModuleClassName		= $oCarrierModule->Module;
		$oResourceTypeHandler	= new $sModuleClassName($oCarrierModule);
		
		// Add each account to the file export class
		$aAccountOCAReferrals = array();
		foreach ($aAccountOCAReferralIds as $iAccountOCAReferralId)
		{
			try
			{
				// Add to the output
				$oAccountOCAReferral = Account_OCA_Referral::getForId($iAccountOCAReferralId);
				$oResourceTypeHandler->addRecord($oAccountOCAReferral);
				$aAccountOCAReferrals[] = $oAccountOCAReferral;
			}
			catch (Exception $oException)
			{
				// Continue processing other requests
				Log::getLog()->log("Failed to export OCA referral, id={$iAccountOCAReferralId}. ".$oException->getMessage());
			}
		}
		
		if (count($aAccountOCAReferrals) == 0)
		{
			Log::getLog()->log("No OCA referrals exported");
			if ($oDataAccess->TransactionRollback() === false)
			{
				throw new Exception("Failed to ROLLBACK db transaction for customer group {$oCarrierModule->customer_group}");
			}
			Log::getLog()->log("Transaction rolled back");
			continue;
		}
		
		try
		{
			Log::getLog()->log("Rendering to file...");
			$oResourceTypeHandler->render()->save();
			
			// Update the file_export_id for each account_oca_referral record'
			$iFileExportId = $oResourceTypeHandler->getFileExportRecord()->Id;
			foreach ($aAccountOCAReferrals as $oAccountOCAReferral)
			{
				// Update and save the ORM object (and record)
				$oAccountOCAReferral->file_export_id = $iFileExportId;
				$oAccountOCAReferral->save();
			}
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception("Failed to COMMIT db transaction for customer group {$oCarrierModule->customer_group}");
			}
			
			Log::getLog()->log("Transaction commited");
			
			return $oResourceTypeHandler;
		}
		catch (Exception $oException)
		{
			if ($oDataAccess->TransactionRollback() === false)
			{
				throw new Exception("Failed to ROLLBACK db transaction for customer group {$oCarrierModule->customer_group}");
			}
			Log::getLog()->log("Transaction rolled back");
			
			throw $oException;
		}
	}

	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."oca_referral/{$iCarrier}/{$sClass}/";
	}
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, null, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>