<?php

	//----------------------------------------------------------------------------//
	// ServiceStreetType.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetType.php
	 *
	 * Contains the ServiceStreetType object
	 *
	 * Contains the ServiceStreetType object
	 *
	 * @file		ServiceStreetType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceStreetType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Street Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Sreet Types
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	ServiceStreetType
	 * @extends	dataEnumerative
	 */
	
	class ServiceStreetType extends dataObject
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
		 * Holds Service Street Type Constant Information
		 *
		 * Holds Service Street Type Constant Information
		 *
		 * @param	String		$strType			The Id of the Service Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($strType)
		{
			parent::__construct ('ServiceStreetType');
			
			$strName = 'Unknown';
			
			switch ($strType)
			{
				case SERVICE_STREET_TYPE_ACCESS:
					$strName = "Access";
					break;
					
				case SERVICE_STREET_TYPE_ALLEY:
					$strName = "Alley";
					break;
					
				case SERVICE_STREET_TYPE_ALLEYWAY:
					$strName = "Alleyway";
					break;
					
				case SERVICE_STREET_TYPE_AMBLE:
					$strName = "Amble";
					break;
					
				case SERVICE_STREET_TYPE_ANCHORAGE:
					$strName = "Anchorage";
					break;
					
				case SERVICE_STREET_TYPE_APPROACH:
					$strName = "Approach";
					break;
					
				case SERVICE_STREET_TYPE_ARCADE:
					$strName = "Arcade";
					break;
					
				case SERVICE_STREET_TYPE_ARTERIAL:
					$strName = "Arterial";
					break;
					
				case SERVICE_STREET_TYPE_ARTERY:
					$strName = "Artery";
					break;
					
				case SERVICE_STREET_TYPE_AVENUE:
					$strName = "Avenue";
					break;
					
				case SERVICE_STREET_TYPE_AVENUE_2:
					$strName = "Avenue";
					break;
					
				case SERVICE_STREET_TYPE_BANK:
					$strName = "Bank";
					break;
					
				case SERVICE_STREET_TYPE_BARRACKS:
					$strName = "Barracks";
					break;
					
				case SERVICE_STREET_TYPE_BASIN:
					$strName = "Basin";
					break;
					
				case SERVICE_STREET_TYPE_BAY:
					$strName = "Bay";
					break;
					
				case SERVICE_STREET_TYPE_BAY_2:
					$strName = "Bay";
					break;
					
				case SERVICE_STREET_TYPE_BEACH:
					$strName = "Beach";
					break;
					
				case SERVICE_STREET_TYPE_BEND:
					$strName = "Bend";
					break;
					
				case SERVICE_STREET_TYPE_BLOCK:
					$strName = "Block";
					break;
					
				case SERVICE_STREET_TYPE_BOULEVARD:
					$strName = "Boulevard";
					break;
					
				case SERVICE_STREET_TYPE_BOULEVARD_2:
					$strName = "Boulevard";
					break;
					
				case SERVICE_STREET_TYPE_BOUNDARY:
					$strName = "Boundary";
					break;
					
				case SERVICE_STREET_TYPE_BOWL:
					$strName = "Bowl";
					break;
					
				case SERVICE_STREET_TYPE_BRACE:
					$strName = "Brace";
					break;
					
				case SERVICE_STREET_TYPE_BRACE_2:
					$strName = "Brace";
					break;
					
				case SERVICE_STREET_TYPE_BRAE:
					$strName = "Brae";
					break;
					
				case SERVICE_STREET_TYPE_BRANCH:
					$strName = "Branch";
					break;
					
				case SERVICE_STREET_TYPE_BREA:
					$strName = "Brea";
					break;
					
				case SERVICE_STREET_TYPE_BREAK:
					$strName = "Break";
					break;
					
				case SERVICE_STREET_TYPE_BRIDGE:
					$strName = "Bridge";
					break;
					
				case SERVICE_STREET_TYPE_BRIDGE_2:
					$strName = "Bridge";
					break;
					
				case SERVICE_STREET_TYPE_BROADWAY:
					$strName = "Broadway";
					break;
					
				case SERVICE_STREET_TYPE_BROW:
					$strName = "Brow";
					break;
					
				case SERVICE_STREET_TYPE_BYPASS:
					$strName = "Bypass";
					break;
					
				case SERVICE_STREET_TYPE_BYWAY:
					$strName = "Byway";
					break;
					
				case SERVICE_STREET_TYPE_CAUSEWAY:
					$strName = "Causeway";
					break;
					
				case SERVICE_STREET_TYPE_CENTRE:
					$strName = "Centre";
					break;
					
				case SERVICE_STREET_TYPE_CENTRE_2:
					$strName = "Centre";
					break;
					
				case SERVICE_STREET_TYPE_CENTREWAY:
					$strName = "Centreway";
					break;
					
				case SERVICE_STREET_TYPE_CHASE:
					$strName = "Chase";
					break;
					
				case SERVICE_STREET_TYPE_CIRCLE:
					$strName = "Circle";
					break;
					
				case SERVICE_STREET_TYPE_CIRCLET:
					$strName = "Circlet";
					break;
					
				case SERVICE_STREET_TYPE_CIRCUIT:
					$strName = "Circuit";
					break;
					
				case SERVICE_STREET_TYPE_CIRCUIT_2:
					$strName = "Circuit";
					break;
					
				case SERVICE_STREET_TYPE_CIRCUS:
					$strName = "Circus";
					break;
					
				case SERVICE_STREET_TYPE_CLOSE:
					$strName = "Close";
					break;
					
				case SERVICE_STREET_TYPE_COLONNADE:
					$strName = "Colonnade";
					break;
					
				case SERVICE_STREET_TYPE_COMMON:
					$strName = "Common";
					break;
					
				case SERVICE_STREET_TYPE_COMMUNITY:
					$strName = "Community";
					break;
					
				case SERVICE_STREET_TYPE_CONCOURSE:
					$strName = "Concourse";
					break;
					
				case SERVICE_STREET_TYPE_CONNECTION:
					$strName = "Connection";
					break;
					
				case SERVICE_STREET_TYPE_COPSE:
					$strName = "Copse";
					break;
					
				case SERVICE_STREET_TYPE_CORNER:
					$strName = "Corner";
					break;
					
				case SERVICE_STREET_TYPE_CORSO:
					$strName = "Corso";
					break;
					
				case SERVICE_STREET_TYPE_COURSE:
					$strName = "Course";
					break;
					
				case SERVICE_STREET_TYPE_COURT:
					$strName = "Court";
					break;
					
				case SERVICE_STREET_TYPE_COURTYARD:
					$strName = "Courtyard";
					break;
					
				case SERVICE_STREET_TYPE_COVE:
					$strName = "Cove";
					break;
					
				case SERVICE_STREET_TYPE_CREEK:
					$strName = "Creek";
					break;
					
				case SERVICE_STREET_TYPE_CREEK_2:
					$strName = "Creek";
					break;
					
				case SERVICE_STREET_TYPE_CRESCENT:
					$strName = "Crescent";
					break;
					
				case SERVICE_STREET_TYPE_CRESCENT_2:
					$strName = "Crescent";
					break;
					
				case SERVICE_STREET_TYPE_CREST:
					$strName = "Crest";
					break;
					
				case SERVICE_STREET_TYPE_CRIEF:
					$strName = "Crief";
					break;
					
				case SERVICE_STREET_TYPE_CROSS:
					$strName = "Cross";
					break;
					
				case SERVICE_STREET_TYPE_CROSSING:
					$strName = "Crossing";
					break;
					
				case SERVICE_STREET_TYPE_CROSSROADS:
					$strName = "Crossroads";
					break;
					
				case SERVICE_STREET_TYPE_CROSSWAY:
					$strName = "Crossway";
					break;
					
				case SERVICE_STREET_TYPE_CRUISEWAY:
					$strName = "Cruiseway";
					break;
					
				case SERVICE_STREET_TYPE_CUL_DE_SAC:
					$strName = "Cul de sac";
					break;
					
				case SERVICE_STREET_TYPE_CUTTING:
					$strName = "Cutting";
					break;
					
				case SERVICE_STREET_TYPE_DALE:
					$strName = "Dale";
					break;
					
				case SERVICE_STREET_TYPE_DELL:
					$strName = "Dell";
					break;
					
				case SERVICE_STREET_TYPE_DEVIATION:
					$strName = "Deviation";
					break;
					
				case SERVICE_STREET_TYPE_DIP:
					$strName = "Dip";
					break;
					
				case SERVICE_STREET_TYPE_DISTRIBUTOR:
					$strName = "Distributor";
					break;
					
				case SERVICE_STREET_TYPE_DOWNS:
					$strName = "Downs";
					break;
					
				case SERVICE_STREET_TYPE_DRIVE:
					$strName = "Drive";
					break;
					
				case SERVICE_STREET_TYPE_DRIVE_2:
					$strName = "Drive";
					break;
					
				case SERVICE_STREET_TYPE_DRIVEWAY:
					$strName = "Driveway";
					break;
					
				case SERVICE_STREET_TYPE_EASEMENT:
					$strName = "Easement";
					break;
					
				case SERVICE_STREET_TYPE_EDGE:
					$strName = "Edge";
					break;
					
				case SERVICE_STREET_TYPE_ELBOW:
					$strName = "Elbow";
					break;
					
				case SERVICE_STREET_TYPE_END:
					$strName = "End";
					break;
					
				case SERVICE_STREET_TYPE_ENTRANCE:
					$strName = "Entrance";
					break;
					
				case SERVICE_STREET_TYPE_ESPLANADE:
					$strName = "Esplanade";
					break;
					
				case SERVICE_STREET_TYPE_ESTATE:
					$strName = "Estate";
					break;
					
				case SERVICE_STREET_TYPE_EXPRESSWAY:
					$strName = "Expressway";
					break;
					
				case SERVICE_STREET_TYPE_EXPRESSWAY_2:
					$strName = "Expressway";
					break;
					
				case SERVICE_STREET_TYPE_EXTENSION:
					$strName = "Extension";
					break;
					
				case SERVICE_STREET_TYPE_EXTENSION_2:
					$strName = "Extension";
					break;
					
				case SERVICE_STREET_TYPE_FAIR:
					$strName = "Fair";
					break;
					
				case SERVICE_STREET_TYPE_FAIRWAY:
					$strName = "Fairway";
					break;
					
				case SERVICE_STREET_TYPE_FIRE_TRACK:
					$strName = "Fire Track";
					break;
					
				case SERVICE_STREET_TYPE_FIRETRAIL:
					$strName = "Firetrail";
					break;
					
				case SERVICE_STREET_TYPE_FIRETRALL:
					$strName = "Firetrall";
					break;
					
				case SERVICE_STREET_TYPE_FLAT:
					$strName = "Flat";
					break;
					
				case SERVICE_STREET_TYPE_FOLLOW:
					$strName = "Follow";
					break;
					
				case SERVICE_STREET_TYPE_FOOTWAY:
					$strName = "Footway";
					break;
					
				case SERVICE_STREET_TYPE_FORESHORE:
					$strName = "Foreshore";
					break;
					
				case SERVICE_STREET_TYPE_FORMATION:
					$strName = "Formation";
					break;
					
				case SERVICE_STREET_TYPE_FREEWAY:
					$strName = "Freeway";
					break;
					
				case SERVICE_STREET_TYPE_FREEWAY_2:
					$strName = "Freeway";
					break;
					
				case SERVICE_STREET_TYPE_FRONT:
					$strName = "Front";
					break;
					
				case SERVICE_STREET_TYPE_FRONTAGE:
					$strName = "Frontage";
					break;
					
				case SERVICE_STREET_TYPE_GAP:
					$strName = "Gap";
					break;
					
				case SERVICE_STREET_TYPE_GARDEN:
					$strName = "GARDEN";
					break;
					
				case SERVICE_STREET_TYPE_GARDENS:
					$strName = "GARDENS";
					break;
					
				case SERVICE_STREET_TYPE_GATE:
					$strName = "Gate";
					break;
					
				case SERVICE_STREET_TYPE_GATES:
					$strName = "Gates";
					break;
					
				case SERVICE_STREET_TYPE_GATEWAY:
					$strName = "Gateway";
					break;
					
				case SERVICE_STREET_TYPE_GLADE:
					$strName = "Glade";
					break;
					
				case SERVICE_STREET_TYPE_GLEN:
					$strName = "Glen";
					break;
					
				case SERVICE_STREET_TYPE_GRANGE:
					$strName = "Grange";
					break;
					
				case SERVICE_STREET_TYPE_GREEN:
					$strName = "Green";
					break;
					
				case SERVICE_STREET_TYPE_GROUND:
					$strName = "Ground";
					break;
					
				case SERVICE_STREET_TYPE_GROVE:
					$strName = "Grove";
					break;
					
				case SERVICE_STREET_TYPE_GROVE_2:
					$strName = "Grove";
					break;
					
				case SERVICE_STREET_TYPE_GULLY:
					$strName = "Gully";
					break;
					
				case SERVICE_STREET_TYPE_HEATH:
					$strName = "Heath";
					break;
					
				case SERVICE_STREET_TYPE_HEIGHTS:
					$strName = "Heights";
					break;
					
				case SERVICE_STREET_TYPE_HIGHROAD:
					$strName = "Highroad";
					break;
					
				case SERVICE_STREET_TYPE_HIGHWAY:
					$strName = "Highway";
					break;
					
				case SERVICE_STREET_TYPE_HILL:
					$strName = "Hill";
					break;
					
				case SERVICE_STREET_TYPE_HILLSIDE:
					$strName = "Hillside";
					break;
					
				case SERVICE_STREET_TYPE_HOUSE:
					$strName = "House";
					break;
					
				case SERVICE_STREET_TYPE_INTERCHANGE:
					$strName = "Interchange";
					break;
					
				case SERVICE_STREET_TYPE_INTERSECTION:
					$strName = "Intersection";
					break;
					
				case SERVICE_STREET_TYPE_ISLAND:
					$strName = "Island";
					break;
					
				case SERVICE_STREET_TYPE_JUNCTION:
					$strName = "Junction";
					break;
					
				case SERVICE_STREET_TYPE_JUNCTION_2:
					$strName = "Junction";
					break;
					
				case SERVICE_STREET_TYPE_KEY:
					$strName = "Key";
					break;
					
				case SERVICE_STREET_TYPE_KNOLL:
					$strName = "Knoll";
					break;
					
				case SERVICE_STREET_TYPE_LANDING:
					$strName = "Landing";
					break;
					
				case SERVICE_STREET_TYPE_LANE:
					$strName = "Lane";
					break;
					
				case SERVICE_STREET_TYPE_LANE_2:
					$strName = "Lane";
					break;
					
				case SERVICE_STREET_TYPE_LANE_3:
					$strName = "Lane";
					break;
					
				case SERVICE_STREET_TYPE_LANEWAY:
					$strName = "Laneway";
					break;
					
				case SERVICE_STREET_TYPE_LEES:
					$strName = "Lees";
					break;
					
				case SERVICE_STREET_TYPE_LINE:
					$strName = "Line";
					break;
					
				case SERVICE_STREET_TYPE_LINK:
					$strName = "Link";
					break;
					
				case SERVICE_STREET_TYPE_LITTLE:
					$strName = "Little";
					break;
					
				case SERVICE_STREET_TYPE_LOCATION:
					$strName = "Location";
					break;
					
				case SERVICE_STREET_TYPE_LOOKOUT:
					$strName = "Lookout";
					break;
					
				case SERVICE_STREET_TYPE_LOOP:
					$strName = "Loop";
					break;
					
				case SERVICE_STREET_TYPE_LOWER:
					$strName = "Lower";
					break;
					
				case SERVICE_STREET_TYPE_MALL:
					$strName = "Mall";
					break;
					
				case SERVICE_STREET_TYPE_MARKETLAND:
					$strName = "Marketland";
					break;
					
				case SERVICE_STREET_TYPE_MARKETTOWN:
					$strName = "Markettown";
					break;
					
				case SERVICE_STREET_TYPE_MEAD:
					$strName = "Mead";
					break;
					
				case SERVICE_STREET_TYPE_MEANDER:
					$strName = "Meander";
					break;
					
				case SERVICE_STREET_TYPE_MEW:
					$strName = "Mew";
					break;
					
				case SERVICE_STREET_TYPE_MEWS:
					$strName = "Mews";
					break;
					
				case SERVICE_STREET_TYPE_MOTORWAY:
					$strName = "Motorway";
					break;
					
				case SERVICE_STREET_TYPE_MOUNT:
					$strName = "Mount";
					break;
					
				case SERVICE_STREET_TYPE_MOUNTAIN:
					$strName = "Mountain";
					break;
					
				case SERVICE_STREET_TYPE_NOOK:
					$strName = "Nook";
					break;
					
				case SERVICE_STREET_TYPE_NOT_REQUIRED:
					$strName = "Not Required";
					break;
					
				case SERVICE_STREET_TYPE_OUTLOOK:
					$strName = "Outlook";
					break;
					
				case SERVICE_STREET_TYPE_OVAL:
					$strName = "Oval";
					break;
					
				case SERVICE_STREET_TYPE_PARADE:
					$strName = "Parade";
					break;
					
				case SERVICE_STREET_TYPE_PARADISE:
					$strName = "Paradise";
					break;
					
				case SERVICE_STREET_TYPE_PARK:
					$strName = "Park";
					break;
					
				case SERVICE_STREET_TYPE_PARK_2:
					$strName = "Park";
					break;
					
				case SERVICE_STREET_TYPE_PARKLANDS:
					$strName = "Parklands";
					break;
					
				case SERVICE_STREET_TYPE_PARKWAY:
					$strName = "Parkway";
					break;
					
				case SERVICE_STREET_TYPE_PART:
					$strName = "Part";
					break;
					
				case SERVICE_STREET_TYPE_PASS:
					$strName = "Pass";
					break;
					
				case SERVICE_STREET_TYPE_PATH:
					$strName = "Path";
					break;
					
				case SERVICE_STREET_TYPE_PATHWAY:
					$strName = "Pathway";
					break;
					
				case SERVICE_STREET_TYPE_PATHWAY_2:
					$strName = "Pathway";
					break;
					
				case SERVICE_STREET_TYPE_PENINSULA:
					$strName = "Peninsula";
					break;
					
				case SERVICE_STREET_TYPE_PIAZZA:
					$strName = "Piazza";
					break;
					
				case SERVICE_STREET_TYPE_PIER:
					$strName = "Pier";
					break;
					
				case SERVICE_STREET_TYPE_PLACE:
					$strName = "Place";
					break;
					
				case SERVICE_STREET_TYPE_PLATEAU:
					$strName = "Plateau";
					break;
					
				case SERVICE_STREET_TYPE_PLAZA:
					$strName = "Plaza";
					break;
					
				case SERVICE_STREET_TYPE_POCKET:
					$strName = "Pocket";
					break;
					
				case SERVICE_STREET_TYPE_POINT:
					$strName = "Point";
					break;
					
				case SERVICE_STREET_TYPE_PORT:
					$strName = "Port";
					break;
					
				case SERVICE_STREET_TYPE_PORT_2:
					$strName = "Port";
					break;
					
				case SERVICE_STREET_TYPE_PROMENADE:
					$strName = "Promenade";
					break;
					
				case SERVICE_STREET_TYPE_PURSUIT:
					$strName = "Pursuit";
					break;
					
				case SERVICE_STREET_TYPE_QUAD:
					$strName = "Quad";
					break;
					
				case SERVICE_STREET_TYPE_QUADRANGLE:
					$strName = "Quadrangle";
					break;
					
				case SERVICE_STREET_TYPE_QUADRANT:
					$strName = "Quadrant";
					break;
					
				case SERVICE_STREET_TYPE_QUAY:
					$strName = "Quay";
					break;
					
				case SERVICE_STREET_TYPE_QUAYS:
					$strName = "Quays";
					break;
					
				case SERVICE_STREET_TYPE_RACECOURSE:
					$strName = "Racecourse";
					break;
					
				case SERVICE_STREET_TYPE_RAMBLE:
					$strName = "Ramble";
					break;
					
				case SERVICE_STREET_TYPE_RAMP:
					$strName = "Ramp";
					break;
					
				case SERVICE_STREET_TYPE_RANGE:
					$strName = "Range";
					break;
					
				case SERVICE_STREET_TYPE_REACH:
					$strName = "Reach";
					break;
					
				case SERVICE_STREET_TYPE_RESERVE:
					$strName = "Reserve";
					break;
					
				case SERVICE_STREET_TYPE_REST:
					$strName = "Rest";
					break;
					
				case SERVICE_STREET_TYPE_RETREAT:
					$strName = "Retreat";
					break;
					
				case SERVICE_STREET_TYPE_RETURN:
					$strName = "Return";
					break;
					
				case SERVICE_STREET_TYPE_RIDE:
					$strName = "Ride";
					break;
					
				case SERVICE_STREET_TYPE_RIDGE:
					$strName = "Ridge";
					break;
					
				case SERVICE_STREET_TYPE_RIDGEWAY:
					$strName = "Ridgeway";
					break;
					
				case SERVICE_STREET_TYPE_RIGHT_OF_WAY:
					$strName = "Right of Way";
					break;
					
				case SERVICE_STREET_TYPE_RING:
					$strName = "Ring";
					break;
					
				case SERVICE_STREET_TYPE_RISE:
					$strName = "Rise";
					break;
					
				case SERVICE_STREET_TYPE_RIVER:
					$strName = "River";
					break;
					
				case SERVICE_STREET_TYPE_RIVERWAY:
					$strName = "Riverway";
					break;
					
				case SERVICE_STREET_TYPE_RIVIERA:
					$strName = "Riviera";
					break;
					
				case SERVICE_STREET_TYPE_ROAD:
					$strName = "Road";
					break;
					
				case SERVICE_STREET_TYPE_ROADS:
					$strName = "Roads";
					break;
					
				case SERVICE_STREET_TYPE_ROADSIDE:
					$strName = "Roadside";
					break;
					
				case SERVICE_STREET_TYPE_ROADWAY:
					$strName = "Roadway";
					break;
					
				case SERVICE_STREET_TYPE_RONDE:
					$strName = "Ronde";
					break;
					
				case SERVICE_STREET_TYPE_ROSEBOWL:
					$strName = "Rosebowl";
					break;
					
				case SERVICE_STREET_TYPE_ROTARY:
					$strName = "Rotary";
					break;
					
				case SERVICE_STREET_TYPE_ROUND:
					$strName = "Round";
					break;
					
				case SERVICE_STREET_TYPE_ROUTE:
					$strName = "Route";
					break;
					
				case SERVICE_STREET_TYPE_ROW:
					$strName = "Row";
					break;
					
				case SERVICE_STREET_TYPE_ROWE:
					$strName = "Rowe";
					break;
					
				case SERVICE_STREET_TYPE_RUE:
					$strName = "Rue";
					break;
					
				case SERVICE_STREET_TYPE_RUN:
					$strName = "Run";
					break;
					
				case SERVICE_STREET_TYPE_SECTION:
					$strName = "Section";
					break;
					
				case SERVICE_STREET_TYPE_SERVICE_WAY:
					$strName = "Service Way";
					break;
					
				case SERVICE_STREET_TYPE_SIDING:
					$strName = "Siding";
					break;
					
				case SERVICE_STREET_TYPE_SLOPE:
					$strName = "Slope";
					break;
					
				case SERVICE_STREET_TYPE_SOUND:
					$strName = "Sound";
					break;
					
				case SERVICE_STREET_TYPE_SPUR:
					$strName = "Spur";
					break;
					
				case SERVICE_STREET_TYPE_SQUARE:
					$strName = "Square";
					break;
					
				case SERVICE_STREET_TYPE_STAIRS:
					$strName = "Stairs";
					break;
					
				case SERVICE_STREET_TYPE_STATE_HIGHWAY:
					$strName = "State Highway";
					break;
					
				case SERVICE_STREET_TYPE_STATION:
					$strName = "Station";
					break;
					
				case SERVICE_STREET_TYPE_STEPS:
					$strName = "Steps";
					break;
					
				case SERVICE_STREET_TYPE_STOP:
					$strName = "Stop";
					break;
					
				case SERVICE_STREET_TYPE_STRAIGHT:
					$strName = "Straight";
					break;
					
				case SERVICE_STREET_TYPE_STRAND:
					$strName = "Strand";
					break;
					
				case SERVICE_STREET_TYPE_STREET:
					$strName = "Street";
					break;
					
				case SERVICE_STREET_TYPE_STRIP:
					$strName = "Strip";
					break;
					
				case SERVICE_STREET_TYPE_STRIP_2:
					$strName = "Strip";
					break;
					
				case SERVICE_STREET_TYPE_SUBWAY:
					$strName = "Subway";
					break;
					
				case SERVICE_STREET_TYPE_TARN:
					$strName = "Tarn";
					break;
					
				case SERVICE_STREET_TYPE_TERRACE:
					$strName = "Terrace";
					break;
					
				case SERVICE_STREET_TYPE_THOROUGHFARE:
					$strName = "Thoroughfare";
					break;
					
				case SERVICE_STREET_TYPE_TOLLWAY:
					$strName = "Tollway";
					break;
					
				case SERVICE_STREET_TYPE_TOP:
					$strName = "Top";
					break;
					
				case SERVICE_STREET_TYPE_TOR:
					$strName = "Tor";
					break;
					
				case SERVICE_STREET_TYPE_TOWER:
					$strName = "Tower";
					break;
					
				case SERVICE_STREET_TYPE_TOWERS:
					$strName = "Towers";
					break;
					
				case SERVICE_STREET_TYPE_TRACK:
					$strName = "Track";
					break;
					
				case SERVICE_STREET_TYPE_TRAIL:
					$strName = "Trail";
					break;
					
				case SERVICE_STREET_TYPE_TRAILER:
					$strName = "Trailer";
					break;
					
				case SERVICE_STREET_TYPE_TRIANGLE:
					$strName = "Triangle";
					break;
					
				case SERVICE_STREET_TYPE_TRUNKWAY:
					$strName = "Trunkway";
					break;
					
				case SERVICE_STREET_TYPE_TURN:
					$strName = "Turn";
					break;
					
				case SERVICE_STREET_TYPE_UNDERPASS:
					$strName = "Underpass";
					break;
					
				case SERVICE_STREET_TYPE_UPPER:
					$strName = "Upper";
					break;
					
				case SERVICE_STREET_TYPE_VALE:
					$strName = "Vale";
					break;
					
				case SERVICE_STREET_TYPE_VALLEY:
					$strName = "Valley";
					break;
					
				case SERVICE_STREET_TYPE_VIADUCT:
					$strName = "Viaduct";
					break;
					
				case SERVICE_STREET_TYPE_VIEW:
					$strName = "View";
					break;
					
				case SERVICE_STREET_TYPE_VILLAGE:
					$strName = "Village";
					break;
					
				case SERVICE_STREET_TYPE_VILLAS:
					$strName = "Villas";
					break;
					
				case SERVICE_STREET_TYPE_VISTA:
					$strName = "Vista";
					break;
					
				case SERVICE_STREET_TYPE_WADE:
					$strName = "Wade";
					break;
					
				case SERVICE_STREET_TYPE_WALK:
					$strName = "Walk";
					break;
					
				case SERVICE_STREET_TYPE_WALK_2:
					$strName = "Walk";
					break;
					
				case SERVICE_STREET_TYPE_WALKWAY:
					$strName = "Walkway";
					break;
					
				case SERVICE_STREET_TYPE_WATERS:
					$strName = "Waters";
					break;
					
				case SERVICE_STREET_TYPE_WAY:
					$strName = "Way";
					break;
					
				case SERVICE_STREET_TYPE_WAY_2:
					$strName = "Way";
					break;
					
				case SERVICE_STREET_TYPE_WEST:
					$strName = "West";
					break;
					
				case SERVICE_STREET_TYPE_WHARF:
					$strName = "Wharf";
					break;
					
				case SERVICE_STREET_TYPE_WHARF_2:
					$strName = "Wharf";
					break;
					
				case SERVICE_STREET_TYPE_WOOD:
					$strName = "Wood";
					break;
					
				case SERVICE_STREET_TYPE_WYND:
					$strName = "Wynd";
					break;
					
				case SERVICE_STREET_TYPE_YARD:
					$strName = "Yard";
					break;
					
				case SERVICE_STREET_TYPE_YARD_2:
					$strName = "Yard";
					break;
			}
			
			$this->oblstrType		= $this->Push (new dataString	('Id',		$strType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
