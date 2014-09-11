<?php

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
class Logic_Service_ADSL extends Logic_Service
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
		// ADSL services don't have any extra details
		return TRUE;
	}
   
}
?>
