<?php
	
	//----------------------------------------------------------------------------//
	// provisioningrecord.php
	//----------------------------------------------------------------------------//
	/**
	 * provisioningrecord.php
	 *
	 * File containing Provisioning Record Class
	 *
	 * File containing Provisioning Record Class
	 *
	 * @file		provisioningrecord.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningRecord
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRecord
	 *
	 * A Provisioning Record in the Database
	 *
	 * A Provisioning Record in the Database
	 *
	 *
	 * @prefix	pvr
	 *
	 * @package		intranet_app
	 * @class		ProvisioningRecord
	 * @extends		dataObject
	 */
	
	class ProvisioningRecord extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Provisioning Record
		 *
		 * Constructor for a new Provisioning Record
		 *
		 * @param	Integer		$intId		The Id of the Provisioning Record being Retrieved from the ProvisioningLog
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the ProvisioningRecord information and Store it ...
			$selProvisioningRecord = new StatementSelect ('ProvisioningLog', '*', 'Id = <Id>', null, 1);
			$selProvisioningRecord->useObLib (TRUE);
			$selProvisioningRecord->Execute (Array ('Id' => $intId));
			
			if ($selProvisioningRecord->Count () <> 1)
			{
				throw new Exception ('Provisioning Record does not exist.');
			}
			
			$selProvisioningRecord->Fetch ($this);
			
			$this->Push (new ProvisioningResponseType ($this->Pull ('Type')->getValue ()));
			
			$this->Push (new Carrier ($this->Pop ('Carrier')->getValue ()));
			
			// Construct the object
			parent::__construct ('ProvisioningRecord', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
