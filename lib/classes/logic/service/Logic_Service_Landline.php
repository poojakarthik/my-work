<?php

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
class Logic_Service_Landline extends Logic_Service
{ 

		protected $_bolELB = false;

		public function __construct($mDefinition)
		{
			parent::__construct($mDefinition);

			if ($this->inDial100)
			{
					$selELB		= new StatementSelect("ServiceExtension", "Id", "Service = <ServiceId>", "Id", "1");
					$mixResult	= $selELB->Execute(Array("ServiceId" => $this->Id));

					if ($mixResult === FALSE)
					{
							// Database error occured
							$this->_strErrorMsg = "Unexpected Database error occurred while trying to work out if the service '{$this->_intCurrentId}' has Extension Level Billing";
							return FALSE;
					}
					$this->_bolELB = ($mixResult == 1)? TRUE : FALSE;
			}

		}





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
	 * NOTE: this only copies the ServiceAddress Record if $intDestAccountId == $this->_intAccount
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
			// Check if the Destination account is the same as the current account
			if ($intDestAccountId == $this->Account)
			{
					// It is safe to copy the ServiceAddress record
					if ($this->_CopyAddressDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup) === FALSE)
					{
							// An error occured
							return FALSE;
					}
			}

			// Copy the ServiceExtension records if ELB is turned on
			if ($this->_bolELB)
			{
					$strServiceExtensionQuery = "	INSERT INTO ServiceExtension (Service, Name, RangeStart, RangeEnd, Archived)
																					SELECT $intDestServiceId, Name, RangeStart, RangeEnd, Archived
																					FROM ServiceExtension
																					WHERE Service = {$this->Id}
																			";
					$qryServiceExtension = new Query();
					if ($qryServiceExtension->Execute($strServiceExtensionQuery) === FALSE)
					{
							$this->_strErrorMsg = "Unexpected database error while trying to copy ServiceExtension records";
							return FALSE;
					}
			}
	}

	//------------------------------------------------------------------------//
	// _CopyAddressDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopyAddressDetails()
	 *
	 * Copies the ServiceAddress record
	 *
	 * Copies the ServiceAddress record
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
	protected function _CopyAddressDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
			$selAddress = new StatementSelect("ServiceAddress", "*", "Service = <ServiceId>");
			if ($selAddress->Execute(array("ServiceId" => $this->Id)) === FALSE)
			{
					$this->_strErrorMsg = "Unexpected database error occurred while trying to make a copy of the ServiceAddress record for service Id: {$this->_intCurrentId}";
					return FALSE;
			}

			// Check that there actually is a ServiceAddress record, as not all landlines have them defined
			if (($arrAddress = $selAddress->Fetch()) === FALSE)
			{
					// There is no ServiceAddress record
					return NULL;
			}

			// A ServiceAddress record exists
			// make a copy, referencing the new Service
			$arrAddress['Id']			= NULL;
			$arrAddress['Service']		= $intDestServiceId;
			$arrAddress['Account']		= $intDestAccountId;
			$arrAddress['AccountGroup']	= $intDestAccountGroup;
			$insAddress					= new StatementInsert("ServiceAddress");

			if ($insAddress->Execute($arrAddress) === FALSE)
			{
					$this->_strErrorMsg = "Unexpected database error occurred while trying to insert a record into the ServiceAddress table";
					return FALSE;
			}

			return TRUE;
	}

   
}
?>
