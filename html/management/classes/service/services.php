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
			$selService = new StatementSelect ('Service', 'Id', 'FNN = <FNN> AND Now() >= CreatedOn AND (ClosedOn IS NULL OR ClosedOn > Now())', null, 1);
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
		// DoesFNNExist
		//------------------------------------------------------------------------//
		/**
		 * DoesFNNExist()
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
		
		public static function DoesFNNExist ($strFNN)
		{
			// Search for the Serivce
			$selService = new StatementSelect ('Service', 'Id', 'FNN = <FNN> AND Now() >= CreatedOn AND (ClosedOn IS NULL OR ClosedOn > Now())', null, 1);
			$selService->Execute (Array ('FNN' => $strFNN));
			
			// If it wasn't found - throw an error
			if ($selService->Count () <> 1)
			{
				return 0;
			}
			else
			{			
				$arrService = $selService->Fetch ();
				return $arrService ['Id'];
			}
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
			
			$strFNN = preg_replace ("/\s/", "", $arrData ['FNN']);
			
			if ($strFNN <> "" && !IsValidFNN ($strFNN))
			{
				throw new Exception ("FNN ServiceType");
			}
			
			if ($strFNN <> "" && ServiceType ($strFNN) <> $arrData ['ServiceType'])
			{
				throw new Exception ("FNN ServiceType");
			}
			
			if ($arrData ['FNN'] <> "")
			{
				try
				{
					$srvService = Services::UnarchivedFNN ($arrData ['FNN']);
				}
				catch (Exception $e)
				{
				}
				
				// If there is a Service ... Throw an Exception
				if ($srvService)
				{
					throw new Exception ('Unarchived FNN Exists');
				}
			}
			
			$arrService = Array (
				'AccountGroup'			=> $actAccount->Pull ('AccountGroup')->getValue (),
				'Account'				=> $actAccount->Pull ('Id')->getValue (),
				
				'FNN'					=> $arrData ['FNN'],
				'ServiceType'			=> $arrData ['ServiceType'],
				'Indial100'				=> ($arrData ['ServiceType'] == SERVICE_TYPE_LAND_LINE && $arrData ['Indial100'] == TRUE) ? '1' : '0',
				
				'CostCentre'			=> $arrData ['CostCentre'],
				
				'CappedCharge'			=> 0,
				'UncappedCharge'		=> 0,
				
				'Carrier'				=> $rrpPlan->Pull('CarrierFullService')->getValue(),
				'CarrierPreselect'		=> $rrpPlan->Pull('CarrierPreselection')->getValue(),
				
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue ()
			);

			$insService = new StatementInsert('Service', $arrService);
			$intService = $insService->Execute($arrService);
			
			// Check if creating the service worked
			if ($intService)
			{
				// Add the system note
				$intEmployeeId		= $aemAuthenticatedEmployee->Pull('Id')->getValue();
				$strServiceType		= GetConstantDescription($arrService['ServiceType'], "service_type");
				$intAccountGroup	= $arrService['AccountGroup'];
				$intAccount			= $arrService['Account'];
				$strServiceFNN		= $arrService['FNN'];
				$strNote			= "New $strServiceType service has been created\nFNN: $strServiceFNN";
				$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, $intEmployeeId, $intAccountGroup, $intAccount, $intService);
			}
			
			$srvService = new Service ($intService);
			$srvService->PlanSelect ($aemAuthenticatedEmployee, $rrpPlan);
	
			return $srvService;
		}
	}
	
?>
