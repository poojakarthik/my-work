<?php

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
class Logic_Service_Inbound extends Logic_Service
{ 

	//------------------------------------------------------------------------//
	// _CopySupplementaryDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopySupplementaryDetails()
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * All Supplementary details associated with the current service will be copied and
	 * associated with the new service id and its Account
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 *
	 * @return	bool							TRUE on success, FALSE on Failure
	 *
	 * @method
	 * @protected
	 */
	protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
		// Copy the ServiceInboundDetail record to the new Service
		$selExtraDetail = new StatementSelect("ServiceInboundDetail", "*", "Service = <ServiceId>");
		if ($selExtraDetail->Execute(array("ServiceId" => $this->Id)) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to make a copy of the ServiceInboundDetail record for service Id: {$this->_intCurrentId}";
			return FALSE;
		}

		// Check that there actually is a ServiceInboundDetail record
		if (($arrExtraDetail = $selExtraDetail->Fetch()) === FALSE)
		{
			// There is no ServiceMobileDetail record
			return NULL;
		}

		// A ServiceInboundDetail record exists
		// make a copy, referencing the new Service
		$arrExtraDetail['Id']		= NULL;
		$arrExtraDetail['Service']	= $intDestServiceId;
		$insExtraDetail				= new StatementInsert("ServiceInboundDetail");

		if ($insExtraDetail->Execute($arrExtraDetail) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to insert a record into the ServiceInboundDetail table";
			return FALSE;
		}

		return TRUE;
	}
   
}
?>
