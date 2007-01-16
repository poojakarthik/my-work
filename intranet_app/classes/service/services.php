<?php

	//----------------------------------------------------------------------------//
	// services.php
	//----------------------------------------------------------------------------//
	/**
	 * services.php
	 *
	 * Contains the Class that Controls Service Searching
	 *
	 * Contains the Class that Controls Service Searching
	 *
	 * @file		services.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Services
	//----------------------------------------------------------------------------//
	/**
	 * Services
	 *
	 * Controls Searching for an existing Service
	 *
	 * Controls Searching for an existing Service
	 *
	 *
	 * @prefix		svs
	 *
	 * @package		intranet_app
	 * @class		Services
	 * @extends		dataObject
	 */
	
	class Services extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Service Searching Routine
		 *
		 * Constructs an Service Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Services', 'Service', 'Service');
		}
		
		//------------------------------------------------------------------------//
		// UnarchivedFNN
		//------------------------------------------------------------------------//
		/**
		 * UnarchivedFNN()
		 *
		 * Retrieves an Unarchived Service based on its FNN
		 *
		 * Retrieves an Unarchived Service based on its FNN
		 *
		 * @param	String		$strFNN		The Full National Number of the Service
		 * @return	Service
		 *
		 * @method
		 */
		
		public static function UnarchivedFNN ($strFNN)
		{
			// Search for the Serivce
			$selService = new StatementSelect ('Service', 'Id', 'FNN = <FNN> AND Now() >= CreatedOn AND (ClosedOn IS NULL OR ClosedOn <= Now())', null, 1);
			$selService->Execute (Array ('FNN' => $strFNN));
			
			// If it wasn't found - throw an error
			if ($selService->Count () <> 1)
			{
				throw new Exception ('Service not found');
			}
			
			$arrService = $selService->Fetch ();
			
			// Return the Service that was Found
			return new Service ($arrService ['Id']);
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Add a new Service to the Database
		 *
		 * Add a new Service to the Database
		 *
		 * @param	AuthenticatedEmployee		$aemAuthenticatedEmployee		The Current Authenticated Employee
		 * @param	Account						$actAccount						The Account where the Service will be Added
		 * @param	RatePlan					$rrpPlan						The Plan this Service will be on
		 * @param	Array						$arrData						Associative array of Information
		 * @return	Service
		 *
		 * @method
		 */
		
		public static function Add (AuthenticatedEmployee $aemAuthenticatedEmployee, Account $actAccount, RatePlan $rrpPlan, $arrData)
		{
			// In this particular TRY block - we want to hit the Catch area
			// Reason being, if we find an Unarchived FNN - then the number
			// cannot be added to the database.
			try
			{
				$srvService = Services::UnarchivedFNN ($strFNN);
			}
			catch (Exception $e)
			{
			}
			
			// If there is a Service ... Throw an Exception
			if ($srvService != NULL)
			{
				throw new Exception ('Unarchived FNN Exists');
			}
			
			$arrService = Array (
				'FNN'					=> $arrData ['FNN'],
				'ServiceType'			=> $arrData ['ServiceType'],
				'Indial100'				=> ($arrData ['ServiceType'] == SERVICE_TYPE_LAND_LINE && $arrData ['Indial100'] == TRUE) ? '1' : '0',
				
				'MinMonthly'			=> 0,
				'ChargeCap'				=> 0,
				'UsageCap'				=> 0,
				
				'AccountGroup'			=> $actAccount->Pull ('AccountGroup')->getValue (),
				'Account'				=> $actAccount->Pull ('Id')->getValue (),
				'CappedCharge'			=> 0,
				'UncappedCharge'		=> 0,
				'CreatedOn'				=> date ('Y-m-d')
				
			);
			
			$insService = new StatementInsert ('Service');
			$intService = $insService->Execute ($arrService);
			
			$srvService = new Service ($intService);
			$srvService->PlanSelect ($aemAuthenticatedEmployee, $rrpPlan);
			
			return $srvService;
		}
	}
	
?>
