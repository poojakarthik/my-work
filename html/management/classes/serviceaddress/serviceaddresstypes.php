<?php

	//----------------------------------------------------------------------------//
	// serviceaddresstypes.php
	//----------------------------------------------------------------------------//
	/**
	 * serviceaddresstypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		serviceaddresstypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceAddressTypes
	//----------------------------------------------------------------------------//
	/**
	 * ServiceAddressTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * @prefix	svt
	 *
	 * @package	intranet_app
	 * @class	ServiceAddressTypes
	 * @extends	dataEnumerative
	 */
	
	class ServiceAddressTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of ServiceType
		 *
		 * Controls a List of ServiceType
		 *
		 * @param	Integer		$strServiceAddressType			[Optional] An String representation of a Service Addres type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strServiceAddressType=null)
		{
			parent::__construct ('ServiceAddressTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_APARTMENT					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_APARTMENT));
			$this->_ATCO_PORTABLE_DWELLING		= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_ATCO_PORTABLE_DWELLING));
			$this->_BASEMENT					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_BASEMENT));
			$this->_BAY							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_BAY));
			$this->_BERTH						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_BERTH));
			$this->_BLOCK						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_BLOCK));
			$this->_BUILDING					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_BUILDING));
			$this->_BUILDING_2					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_BUILDING_2));
			$this->_CARAVAN						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_CARAVAN));
			$this->_CARE_PO						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_CARE_PO));
			$this->_CHAMBERS					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_CHAMBERS));
			$this->_CMA							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_CMA));
			$this->_CMB							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_CMB));
			$this->_COMPLEX						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_COMPLEX));
			$this->_COTTAGE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_COTTAGE));
			$this->_COUNTER						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_COUNTER));
			$this->_DUPLEX						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_DUPLEX));
			$this->_ENTRANCE					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_ENTRANCE));
			$this->_FACTORY						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_FACTORY));
			$this->_FARM						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_FARM));
			$this->_FLAT						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_FLAT));
			$this->_FLAT_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_FLAT_2));
			$this->_FLAT_3						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_FLAT_3));
			$this->_FLOOR						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_FLOOR));
			$this->_GATE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_GATE));
			$this->_GATE_A						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_GATE_A));
			$this->_GPO_BOX						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_GPO_BOX));
			$this->_GROUND_GROUND_FLOOR			= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_GROUND_GROUND_FLOOR));
			$this->_HANGAR						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_HANGAR));
			$this->_HOUSE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_HOUSE));
			$this->_IGLOO						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_IGLOO));
			$this->_JETTY						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_JETTY));
			$this->_KIOSK						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_KIOSK));
			$this->_LANE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_LANE));
			$this->_LEVEL						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_LEVEL));
			$this->_LEVEL_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_LEVEL_2));
			$this->_LOCKED_BAG					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_LOCKED_BAG));
			$this->_LOT							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_LOT));
			$this->_LOWER_GROUND_FLOOR			= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_LOWER_GROUND_FLOOR));
			$this->_MAISONETTE					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_MAISONETTE));
			$this->_MEZZANINE					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_MEZZANINE));
			$this->_MS							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_MS));
			$this->_OFFICE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_OFFICE));
			$this->_OFFICE_2					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_OFFICE_2));
			$this->_PENTHOUSE					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_PENTHOUSE));
			$this->_PIER						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_PIER));
			$this->_PO_BOX						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_PO_BOX));
			$this->_POST_OFFICE					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_POST_OFFICE));
			$this->_PRIVATE_BAG					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_PRIVATE_BAG));
			$this->_PRIVATE_BAG_2				= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_PRIVATE_BAG_2));
			$this->_RMB							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_RMB));
			$this->_RMS							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_RMS));
			$this->_ROOM						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_ROOM));
			$this->_RSD							= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_RSD));
			$this->_RURAL_MAIL_DELIVERY			= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_RURAL_MAIL_DELIVERY));
			$this->_SHED						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_SHED));
			$this->_SHED_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_SHED_2));
			$this->_SHOP						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_SHOP));
			$this->_SHOP_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_SHOP_2));
			$this->_SITE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_SITE));
			$this->_STALL						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_STALL));
			$this->_STALL_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_STALL_2));
			$this->_STUDIO						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_STUDIO));
			$this->_SUITE						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_SUITE));
			$this->_TIER						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_TIER));
			$this->_TOWER						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_TOWER));
			$this->_TOWER_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_TOWER_2));
			$this->_TOWNHOUSE					= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_TOWNHOUSE));
			$this->_UNIT						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_UNIT));
			$this->_UNIT_2						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_UNIT_2));
			$this->_UPPER_GROUND_FLOOR			= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_UPPER_GROUND_FLOOR));
			$this->_VILLA						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_VILLA));
			$this->_WARD						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_WARD));
			$this->_WHARF						= $this->Push (new ServiceAddressType (SERVICE_ADDR_TYPE_WHARF));
			
			$this->setValue ($strServiceAddressType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Service Type
		 *
		 * Change the Selected Service Type to another Service Type
		 *
		 * @param	Integer		$strServiceAddressType		The value of the ServiceAddressType Constant wishing to be set
		 * @return	Boolean									Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($strServiceAddressType)
		{
			// Select the value
			switch ($strServiceAddressType)
			{
				case SERVICE_ADDR_TYPE_APARTMENT:					$this->Select ($this->_APARTMENT);					return true;
				case SERVICE_ADDR_TYPE_ATCO_PORTABLE_DWELLING:		$this->Select ($this->_ATCO_PORTABLE_DWELLING);		return true;
				case SERVICE_ADDR_TYPE_BASEMENT:					$this->Select ($this->_BASEMENT);					return true;
				case SERVICE_ADDR_TYPE_BAY:							$this->Select ($this->_BAY);						return true;
				case SERVICE_ADDR_TYPE_BERTH:						$this->Select ($this->_BERTH);						return true;
				case SERVICE_ADDR_TYPE_BLOCK:						$this->Select ($this->_BLOCK);						return true;
				case SERVICE_ADDR_TYPE_BUILDING:					$this->Select ($this->_BUILDING);					return true;
				case SERVICE_ADDR_TYPE_BUILDING_2:					$this->Select ($this->_BUILDING_2);					return true;
				case SERVICE_ADDR_TYPE_CARAVAN:						$this->Select ($this->_CARAVAN);					return true;
				case SERVICE_ADDR_TYPE_CARE_PO:						$this->Select ($this->_CARE_PO);					return true;
				case SERVICE_ADDR_TYPE_CHAMBERS:					$this->Select ($this->_CHAMBERS);					return true;
				case SERVICE_ADDR_TYPE_CMA:							$this->Select ($this->_CMA);						return true;
				case SERVICE_ADDR_TYPE_CMB:							$this->Select ($this->_CMB);						return true;
				case SERVICE_ADDR_TYPE_COMPLEX:						$this->Select ($this->_COMPLEX);					return true;
				case SERVICE_ADDR_TYPE_COTTAGE:						$this->Select ($this->_COTTAGE);					return true;
				case SERVICE_ADDR_TYPE_COUNTER:						$this->Select ($this->_COUNTER);					return true;
				case SERVICE_ADDR_TYPE_DUPLEX:						$this->Select ($this->_DUPLEX);						return true;
				case SERVICE_ADDR_TYPE_ENTRANCE:					$this->Select ($this->_ENTRANCE);					return true;
				case SERVICE_ADDR_TYPE_FACTORY:						$this->Select ($this->_FACTORY);					return true;
				case SERVICE_ADDR_TYPE_FARM:						$this->Select ($this->_FARM);						return true;
				case SERVICE_ADDR_TYPE_FLAT:						$this->Select ($this->_FLAT);						return true;
				case SERVICE_ADDR_TYPE_FLAT_2:						$this->Select ($this->_FLAT_2);						return true;
				case SERVICE_ADDR_TYPE_FLAT_3:						$this->Select ($this->_FLAT_3);						return true;
				case SERVICE_ADDR_TYPE_FLOOR:						$this->Select ($this->_FLOOR);						return true;
				case SERVICE_ADDR_TYPE_GATE:						$this->Select ($this->_GATE);						return true;
				case SERVICE_ADDR_TYPE_GATE_A:						$this->Select ($this->_GATE_A);						return true;
				case SERVICE_ADDR_TYPE_GPO_BOX:						$this->Select ($this->_GPO_BOX);					return true;
				case SERVICE_ADDR_TYPE_GROUND_GROUND_FLOOR:			$this->Select ($this->_GROUND_GROUND_FLOOR);		return true;
				case SERVICE_ADDR_TYPE_HANGAR:						$this->Select ($this->_HANGAR);						return true;
				case SERVICE_ADDR_TYPE_HOUSE:						$this->Select ($this->_HOUSE);						return true;
				case SERVICE_ADDR_TYPE_IGLOO:						$this->Select ($this->_IGLOO);						return true;
				case SERVICE_ADDR_TYPE_JETTY:						$this->Select ($this->_JETTY);						return true;
				case SERVICE_ADDR_TYPE_KIOSK:						$this->Select ($this->_KIOSK);						return true;
				case SERVICE_ADDR_TYPE_LANE:						$this->Select ($this->_LANE);						return true;
				case SERVICE_ADDR_TYPE_LEVEL:						$this->Select ($this->_LEVEL);						return true;
				case SERVICE_ADDR_TYPE_LEVEL_2:						$this->Select ($this->_LEVEL_2);					return true;
				case SERVICE_ADDR_TYPE_LOCKED_BAG:					$this->Select ($this->_LOCKED_BAG);					return true;
				case SERVICE_ADDR_TYPE_LOT:							$this->Select ($this->_LOT);						return true;
				case SERVICE_ADDR_TYPE_LOWER_GROUND_FLOOR:			$this->Select ($this->_LOWER_GROUND_FLOOR);			return true;
				case SERVICE_ADDR_TYPE_MAISONETTE:					$this->Select ($this->_MAISONETTE);					return true;
				case SERVICE_ADDR_TYPE_MEZZANINE:					$this->Select ($this->_MEZZANINE);					return true;
				case SERVICE_ADDR_TYPE_MS:							$this->Select ($this->_MS);							return true;
				case SERVICE_ADDR_TYPE_OFFICE:						$this->Select ($this->_OFFICE);						return true;
				case SERVICE_ADDR_TYPE_OFFICE_2:					$this->Select ($this->_OFFICE_2);					return true;
				case SERVICE_ADDR_TYPE_PENTHOUSE:					$this->Select ($this->_PENTHOUSE);					return true;
				case SERVICE_ADDR_TYPE_PIER:						$this->Select ($this->_PIER);						return true;
				case SERVICE_ADDR_TYPE_PO_BOX:						$this->Select ($this->_PO_BOX);						return true;
				case SERVICE_ADDR_TYPE_POST_OFFICE:					$this->Select ($this->_POST_OFFICE);				return true;
				case SERVICE_ADDR_TYPE_PRIVATE_BAG:					$this->Select ($this->_PRIVATE_BAG);				return true;
				case SERVICE_ADDR_TYPE_PRIVATE_BAG_2:				$this->Select ($this->_PRIVATE_BAG_2);				return true;
				case SERVICE_ADDR_TYPE_RMB:							$this->Select ($this->_RMB);						return true;
				case SERVICE_ADDR_TYPE_RMS:							$this->Select ($this->_RMS);						return true;
				case SERVICE_ADDR_TYPE_ROOM:						$this->Select ($this->_ROOM);						return true;
				case SERVICE_ADDR_TYPE_RSD:							$this->Select ($this->_RSD);						return true;
				case SERVICE_ADDR_TYPE_RURAL_MAIL_DELIVERY:			$this->Select ($this->_RURAL_MAIL_DELIVERY);		return true;
				case SERVICE_ADDR_TYPE_SHED:						$this->Select ($this->_SHED);						return true;
				case SERVICE_ADDR_TYPE_SHED_2:						$this->Select ($this->_SHED_2);						return true;
				case SERVICE_ADDR_TYPE_SHOP:						$this->Select ($this->_SHOP);						return true;
				case SERVICE_ADDR_TYPE_SHOP_2:						$this->Select ($this->_SHOP_2);						return true;
				case SERVICE_ADDR_TYPE_SITE:						$this->Select ($this->_SITE);						return true;
				case SERVICE_ADDR_TYPE_STALL:						$this->Select ($this->_STALL);						return true;
				case SERVICE_ADDR_TYPE_STALL_2:						$this->Select ($this->_STALL_2);					return true;
				case SERVICE_ADDR_TYPE_STUDIO:						$this->Select ($this->_STUDIO);						return true;
				case SERVICE_ADDR_TYPE_SUITE:						$this->Select ($this->_SUITE);						return true;
				case SERVICE_ADDR_TYPE_TIER:						$this->Select ($this->_TIER);						return true;
				case SERVICE_ADDR_TYPE_TOWER:						$this->Select ($this->_TOWER);						return true;
				case SERVICE_ADDR_TYPE_TOWER_2:						$this->Select ($this->_TOWER_2);					return true;
				case SERVICE_ADDR_TYPE_TOWNHOUSE:					$this->Select ($this->_TOWNHOUSE);					return true;
				case SERVICE_ADDR_TYPE_UNIT:						$this->Select ($this->_UNIT);						return true;
				case SERVICE_ADDR_TYPE_UNIT_2:						$this->Select ($this->_UNIT_2);						return true;
				case SERVICE_ADDR_TYPE_UPPER_GROUND_FLOOR:			$this->Select ($this->_UPPER_GROUND_FLOOR);			return true;
				case SERVICE_ADDR_TYPE_VILLA:						$this->Select ($this->_VILLA);						return true;
				case SERVICE_ADDR_TYPE_WARD:						$this->Select ($this->_WARD);						return true;
				case SERVICE_ADDR_TYPE_WHARF:						$this->Select ($this->_WHARF);						return true;
				default:																								return false;
			}
		}
	}
	
?>
