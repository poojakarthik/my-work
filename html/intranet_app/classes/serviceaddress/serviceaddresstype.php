<?php

	//----------------------------------------------------------------------------//
	// ServiceAddressType.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceAddressType.php
	 *
	 * Contains the ServiceAddressType object
	 *
	 * Contains the ServiceAddressType object
	 *
	 * @file		ServiceAddressType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceAddressType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceAddressType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Address Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Address Types
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	ServiceAddressType
	 * @extends	dataEnumerative
	 */
	
	class ServiceAddressType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrType
		//------------------------------------------------------------------------//
		/**
		 * _oblstrType
		 *
		 * The Id of the Service Type
		 *
		 * The Id of the Service Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrType;
		
		//------------------------------------------------------------------------//
		// _oblstrName
		//------------------------------------------------------------------------//
		/**
		 * _oblstrName
		 *
		 * The name of the Service Type
		 *
		 * The name of the Service Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	String		$strType			The Id of the Service Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($strType)
		{
			parent::__construct ('ServiceAddressType');
			
			$strName = 'Unknown';
			
			switch ($strType)
			{
				case SERVICE_ADDR_TYPE_APARTMENT:
					$strName = "Apartment";
					break;
					
				case SERVICE_ADDR_TYPE_ATCO_PORTABLE_DWELLING:
					$strName = "Atco Portable Dwelling";
					break;
					
				case SERVICE_ADDR_TYPE_BASEMENT:
					$strName = "Basement";
					break;
					
				case SERVICE_ADDR_TYPE_BAY:
					$strName = "Bay";
					break;
					
				case SERVICE_ADDR_TYPE_BERTH:
					$strName = "Berth";
					break;
					
				case SERVICE_ADDR_TYPE_BLOCK:
					$strName = "Block";
					break;
					
				case SERVICE_ADDR_TYPE_BUILDING:
					$strName = "Building";
					break;
					
				case SERVICE_ADDR_TYPE_BUILDING_2:
					$strName = "Building";
					break;
					
				case SERVICE_ADDR_TYPE_CARAVAN:
					$strName = "Caravan";
					break;
					
				case SERVICE_ADDR_TYPE_CARE_PO:
					$strName = "Care PO";
					break;
					
				case SERVICE_ADDR_TYPE_CHAMBERS:
					$strName = "Chambers";
					break;
					
				case SERVICE_ADDR_TYPE_CMA:
					$strName = "CMA";
					break;
					
				case SERVICE_ADDR_TYPE_CMB:
					$strName = "CMB";
					break;
					
				case SERVICE_ADDR_TYPE_COMPLEX:
					$strName = "Complex";
					break;
					
				case SERVICE_ADDR_TYPE_COTTAGE:
					$strName = "Cottage";
					break;
					
				case SERVICE_ADDR_TYPE_COUNTER:
					$strName = "Counter";
					break;
					
				case SERVICE_ADDR_TYPE_DUPLEX:
					$strName = "Duplex";
					break;
					
				case SERVICE_ADDR_TYPE_ENTRANCE:
					$strName = "Entrance";
					break;
					
				case SERVICE_ADDR_TYPE_FACTORY:
					$strName = "Factory";
					break;
					
				case SERVICE_ADDR_TYPE_FARM:
					$strName = "Farm";
					break;
					
				case SERVICE_ADDR_TYPE_FLAT:
					$strName = "Flat";
					break;
					
				case SERVICE_ADDR_TYPE_FLAT_2:
					$strName = "Flat";
					break;
					
				case SERVICE_ADDR_TYPE_FLAT_3:
					$strName = "Flat";
					break;
					
				case SERVICE_ADDR_TYPE_FLOOR:
					$strName = "Floor";
					break;
					
				case SERVICE_ADDR_TYPE_GATE:
					$strName = "Gate";
					break;
					
				case SERVICE_ADDR_TYPE_GATE_A:
					$strName = "Gate";
					break;
					
				case SERVICE_ADDR_TYPE_GPO_BOX:
					$strName = "GPO Box";
					break;
					
				case SERVICE_ADDR_TYPE_GROUND_GROUND_FLOOR:
					$strName = "Ground/Ground Floor";
					break;
					
				case SERVICE_ADDR_TYPE_HANGAR:
					$strName = "Hangar";
					break;
					
				case SERVICE_ADDR_TYPE_HOUSE:
					$strName = "House";
					break;
					
				case SERVICE_ADDR_TYPE_IGLOO:
					$strName = "Igloo";
					break;
					
				case SERVICE_ADDR_TYPE_JETTY:
					$strName = "Jetty";
					break;
					
				case SERVICE_ADDR_TYPE_KIOSK:
					$strName = "Kiosk";
					break;
					
				case SERVICE_ADDR_TYPE_LANE:
					$strName = "Lane";
					break;
					
				case SERVICE_ADDR_TYPE_LEVEL:
					$strName = "Level";
					break;
					
				case SERVICE_ADDR_TYPE_LEVEL_2:
					$strName = "Level";
					break;
					
				case SERVICE_ADDR_TYPE_LOCKED_BAG:
					$strName = "Locked Bag";
					break;
					
				case SERVICE_ADDR_TYPE_LOT:
					$strName = "Lot";
					break;
					
				case SERVICE_ADDR_TYPE_LOWER_GROUND_FLOOR:
					$strName = "Lower Ground Floor";
					break;
					
				case SERVICE_ADDR_TYPE_MAISONETTE:
					$strName = "Maisonette";
					break;
					
				case SERVICE_ADDR_TYPE_MEZZANINE:
					$strName = "Mezzanine";
					break;
					
				case SERVICE_ADDR_TYPE_MS:
					$strName = "MS";
					break;
					
				case SERVICE_ADDR_TYPE_OFFICE:
					$strName = "Office";
					break;
					
				case SERVICE_ADDR_TYPE_OFFICE_2:
					$strName = "Office";
					break;
					
				case SERVICE_ADDR_TYPE_PENTHOUSE:
					$strName = "Penthouse";
					break;
					
				case SERVICE_ADDR_TYPE_PIER:
					$strName = "Pier";
					break;
					
				case SERVICE_ADDR_TYPE_PO_BOX:
					$strName = "PO Box";
					break;
					
				case SERVICE_ADDR_TYPE_POST_OFFICE:
					$strName = "Post Office";
					break;
					
				case SERVICE_ADDR_TYPE_PRIVATE_BAG:
					$strName = "Private Bag";
					break;
					
				case SERVICE_ADDR_TYPE_PRIVATE_BAG_2:
					$strName = "Private Bag";
					break;
					
				case SERVICE_ADDR_TYPE_RMB:
					$strName = "RMB";
					break;
					
				case SERVICE_ADDR_TYPE_RMS:
					$strName = "RMS";
					break;
					
				case SERVICE_ADDR_TYPE_ROOM:
					$strName = "Room";
					break;
					
				case SERVICE_ADDR_TYPE_RSD:
					$strName = "RSD";
					break;
					
				case SERVICE_ADDR_TYPE_RURAL_MAIL_DELIVERY:
					$strName = "Rural Mail Delivery";
					break;
					
				case SERVICE_ADDR_TYPE_SHED:
					$strName = "Shed";
					break;
					
				case SERVICE_ADDR_TYPE_SHED_2:
					$strName = "Shed";
					break;
					
				case SERVICE_ADDR_TYPE_SHOP:
					$strName = "Shop";
					break;
					
				case SERVICE_ADDR_TYPE_SHOP_2:
					$strName = "Shop";
					break;
					
				case SERVICE_ADDR_TYPE_SITE:
					$strName = "Site";
					break;
					
				case SERVICE_ADDR_TYPE_STALL:
					$strName = "Stall";
					break;
					
				case SERVICE_ADDR_TYPE_STALL_2:
					$strName = "Stall";
					break;
					
				case SERVICE_ADDR_TYPE_STUDIO:
					$strName = "Studio";
					break;
					
				case SERVICE_ADDR_TYPE_SUITE:
					$strName = "Suite";
					break;
					
				case SERVICE_ADDR_TYPE_TIER:
					$strName = "Tier";
					break;
					
				case SERVICE_ADDR_TYPE_TOWER:
					$strName = "Tower";
					break;
					
				case SERVICE_ADDR_TYPE_TOWER_2:
					$strName = "Tower";
					break;
					
				case SERVICE_ADDR_TYPE_TOWNHOUSE:
					$strName = "Townhouse";
					break;
					
				case SERVICE_ADDR_TYPE_UNIT:
					$strName = "Unit";
					break;
					
				case SERVICE_ADDR_TYPE_UNIT_2:
					$strName = "Unit";
					break;
					
				case SERVICE_ADDR_TYPE_UPPER_GROUND_FLOOR:
					$strName = "Upper Ground Floor";
					break;
					
				case SERVICE_ADDR_TYPE_VILLA:
					$strName = "Villa";
					break;
					
				case SERVICE_ADDR_TYPE_WARD:
					$strName = "Ward";
					break;
					
				case SERVICE_ADDR_TYPE_WHARF:
					$strName = "Warf";
					break;
					
			}
			
			$this->oblstrType		= $this->Push (new dataString	('Id',		$strType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
