<?php
/**
 * Resource_Type_File_Export_Payment
 *
 * @class	Resource_Type_File_Export_Payment
 */
abstract class Resource_Type_File_Export_Payment extends Resource_Type_File_Export
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_PAYMENT_DIRECT_DEBIT;
	
	public static function exportDirectDebits($bDeliver=false)
	{
		$aDirectDebitCarrierModules	= Carrier_Module::getForCarrierModuleType(self::CARRIER_MODULE_TYPE);
		foreach ($aDirectDebitCarrierModules as $oCarrierModule)
		{
			Log::getLog()->log("\nResource type handler {$oCarrierModule->Module}, customer group {$oCarrierModule->customer_group}");
			
			$oDataAccess	= DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception("Failed to START db transaction for customer group {$oCarrierModule->customer_group}");
			}
			Log::getLog()->log("Transaction started");
			
			// Create the file export resource type
			$sModuleClassName		= $oCarrierModule->Module;
			$oResourceTypeHandler	= new $sModuleClassName($oCarrierModule);
			
			// Get all pending payment requests for the customer group & payment type associated 
			// with the carrier module
			$aPaymentRequests	=	Payment_Request::getForStatusAndCustomerGroupAndPaymentType(
										PAYMENT_REQUEST_STATUS_PENDING, 
										$oCarrierModule->customer_group,
										$oResourceTypeHandler->getAssociatedPaymentType()
									);
			
			if (count($aPaymentRequests) == 0)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to ROLLBACK db transaction for customer group {$oCarrierModule->customer_group}");
				}
				Log::getLog()->log("No payment requests, transaction rolled back");
				
				continue;
			}
			
			foreach ($aPaymentRequests as $oPaymentRequest)
			{
				try
				{
					Log::getLog()->log("Payment request {$oPaymentRequest->id}");
					$oResourceTypeHandler->addRecord($oPaymentRequest);
					continue;	
					// Update the status of the payment request
					$oPaymentRequest->payment_request_status_id	= PAYMENT_REQUEST_STATUS_DISPATCHED;
					$oPaymentRequest->save();
				}
				catch (Exception $oException)
				{
					// Continue processing other requests
					Log::getLog()->log("Failed to export payment request, id={$oPaymentRequest->id}. ".$oException->getMessage());
				}
			}
			
			try
			{
				Log::getLog()->log("Rendering to file...");
				$oResourceTypeHandler->render()->save();
				
				if ($bDeliver)
				{
					Log::getLog()->log("Delivering...");
					$oResourceTypeHandler->deliver();
				}
				
				if ($oDataAccess->TransactionCommit() === false)
				{
					throw new Exception("Failed to COMMIT db transaction for customer group {$oCarrierModule->customer_group}");
				}
				Log::getLog()->log("Transaction commited");
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
	}

	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."payment/{$iCarrier}/{$sClass}/";
	}
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>