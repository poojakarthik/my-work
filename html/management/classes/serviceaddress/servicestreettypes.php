<?php

	//----------------------------------------------------------------------------//
	// ServiceStreetTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetTypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		ServiceStreetTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceStreetTypes
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * @prefix	stl
	 *
	 * @package	intranet_app
	 * @class	ServiceStreetTypes
	 * @extends	dataEnumerative
	 */
	
	class ServiceStreetTypes extends dataEnumerative
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
		 * @param	String		$strServiceStreetType			[Optional] An String representation of a Service Street type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strServiceStreetType=null)
		{
			parent::__construct ('ServiceStreetTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_ACCESS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ACCESS));
			$this->_ALLEY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ALLEY));
			$this->_ALLEYWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ALLEYWAY));
			$this->_AMBLE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_AMBLE));
			$this->_ANCHORAGE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ANCHORAGE));
			$this->_APPROACH					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_APPROACH));
			$this->_ARCADE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ARCADE));
			$this->_ARTERIAL					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ARTERIAL));
			$this->_ARTERY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ARTERY));
			$this->_AVENUE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_AVENUE));
			$this->_AVENUE_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_AVENUE_2));
			$this->_BANK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BANK));
			$this->_BARRACKS					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BARRACKS));
			$this->_BASIN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BASIN));
			$this->_BAY							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BAY));
			$this->_BAY_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BAY_2));
			$this->_BREACH						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BEACH));
			$this->_BEND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BEND));
			$this->_BLOCK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BLOCK));
			$this->_BOULEVARD					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BOULEVARD));
			$this->_BOULEVARD_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BOULEVARD_2));
			$this->_BOUNDARY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BOUNDARY));
			$this->_BOWL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BOWL));
			$this->_BRACE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BRACE));
			$this->_BRACE_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BRACE_2));
			$this->_BRAE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BRAE));
			$this->_BRANCH						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BRANCH));
			$this->_BREA						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BREA));
			$this->_BREAK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BREAK));
			$this->_BRIDGE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BRIDGE));
			$this->_BRIDGE_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BRIDGE_2));
			$this->_BROADWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BROADWAY));
			$this->_BROW						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BROW));
			$this->_BYPASS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BYPASS));
			$this->_BYWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_BYWAY));
			$this->_CAUSEWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CAUSEWAY));
			$this->_CENTRE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CENTRE));
			$this->_CENTRE_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CENTRE_2));
			$this->_CENTREWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CENTREWAY));
			$this->_CHASE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CHASE));
			$this->_CIRCLE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CIRCLE));
			$this->_CIRCLET						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CIRCLET));
			$this->_CIRCUIT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CIRCUIT));
			$this->_CIRCUIT_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CIRCUIT_2));
			$this->_CIRCUS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CIRCUS));
			$this->_CLOSE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CLOSE));
			$this->_COLONNADE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COLONNADE));
			$this->_COMMON						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COMMON));
			$this->_COMMUNITY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COMMUNITY));
			$this->_CONCOURSE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CONCOURSE));
			$this->_CONNECTION					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CONNECTION));
			$this->_COPSE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COPSE));
			$this->_CORNER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CORNER));
			$this->_CORSO						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CORSO));
			$this->_COURSE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COURSE));
			$this->_COURT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COURT));
			$this->_COURTYARD					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COURTYARD));
			$this->_COVE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_COVE));
			$this->_CREEK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CREEK));
			$this->_CREEK_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CREEK_2));
			$this->_CRESCENT					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CRESCENT));
			$this->_CRESCENT_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CRESCENT_2));
			$this->_CREST						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CREST));
			$this->_CRIEF						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CRIEF));
			$this->_CROSS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CROSS));
			$this->_CROSSING					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CROSSING));
			$this->_CROSSROADS					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CROSSROADS));
			$this->_CROSSWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CROSSWAY));
			$this->_CRUISEWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CRUISEWAY));
			$this->_CUL_DE_SAC					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CUL_DE_SAC));
			$this->_CUTTING						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_CUTTING));
			$this->_DALE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DALE));
			$this->_DELL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DELL));
			$this->_DEVIATION					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DEVIATION));
			$this->_DIP							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DIP));
			$this->_DISTRIBUTOR					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DISTRIBUTOR));
			$this->_DOWNS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DOWNS));
			$this->_DRIVE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DRIVE));
			$this->_DRIVE_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DRIVE_2));
			$this->_DRIVEWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_DRIVEWAY));
			$this->_EASEMENT					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_EASEMENT));
			$this->_EDGE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_EDGE));
			$this->_ELBOW						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ELBOW));
			$this->_END							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_END));
			$this->_ENTRANCE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ENTRANCE));
			$this->_ESPLANADE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ESPLANADE));
			$this->_ESTATE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ESTATE));
			$this->_EXPRESSWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_EXPRESSWAY));
			$this->_EXPRESSWAY_2				= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_EXPRESSWAY_2));
			$this->_EXTENSION					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_EXTENSION));
			$this->_EXTENSION_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_EXTENSION_2));
			$this->_FAIR						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FAIR));
			$this->_FAIRWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FAIRWAY));
			$this->_FIRE_TRACK					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FIRE_TRACK));
			$this->_FIRETRAIL					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FIRETRAIL));
			$this->_FIRETRALL					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FIRETRALL));
			$this->_FLAT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FLAT));
			$this->_FOLLOW						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FOLLOW));
			$this->_FOOTWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FOOTWAY));
			$this->_FORESHORE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FORESHORE));
			$this->_FORMATION					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FORMATION));
			$this->_FREEWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FREEWAY));
			$this->_FREEWAY_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FREEWAY_2));
			$this->_FRONT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FRONT));
			$this->_FRONTAGE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_FRONTAGE));
			$this->_GAP							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GAP));
			$this->_GARDEN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GARDEN));
			$this->_GARDENS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GARDENS));
			$this->_GATE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GATE));
			$this->_GATES						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GATES));
			$this->_GATEWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GATEWAY));
			$this->_GLADE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GLADE));
			$this->_GLEN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GLEN));
			$this->_GRANGE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GRANGE));
			$this->_GREEN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GREEN));
			$this->_GROUND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GROUND));
			$this->_GROVE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GROVE));
			$this->_GROVE_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GROVE_2));
			$this->_GULLY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_GULLY));
			$this->_HEATH						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HEATH));
			$this->_HEIGHTS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HEIGHTS));
			$this->_HIGHROAD					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HIGHROAD));
			$this->_HIGHWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HIGHWAY));
			$this->_HILL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HILL));
			$this->_HILLSIDE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HILLSIDE));
			$this->_HOUSE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_HOUSE));
			$this->_INTERCHANGE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_INTERCHANGE));
			$this->_INTERSECTION				= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_INTERSECTION));
			$this->_ISLAND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ISLAND));
			$this->_JUNCTION					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_JUNCTION));
			$this->_JUNCTION_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_JUNCTION_2));
			$this->_KEY							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_KEY));
			$this->_KNOLL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_KNOLL));
			$this->_LANDING						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LANDING));
			$this->_LANE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LANE));
			$this->_LANE_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LANE_2));
			$this->_LANE_3						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LANE_3));
			$this->_LANEWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LANEWAY));
			$this->_LEES						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LEES));
			$this->_LINE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LINE));
			$this->_LINK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LINK));
			$this->_LITTLE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LITTLE));
			$this->_LOCATION					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LOCATION));
			$this->_LOOKOUT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LOOKOUT));
			$this->_LOOP						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LOOP));
			$this->_LOWER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_LOWER));
			$this->_MALL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MALL));
			$this->_MARKETLAND					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MARKETLAND));
			$this->_MARKETTOWN					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MARKETTOWN));
			$this->_MEAD						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MEAD));
			$this->_MEANDER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MEANDER));
			$this->_MEW							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MEW));
			$this->_MEWS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MEWS));
			$this->_MOTORWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MOTORWAY));
			$this->_MOUNT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MOUNT));
			$this->_MOUNTAIN					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_MOUNTAIN));
			$this->_NOOK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_NOOK));
			$this->_NOT_REQUIRED				= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_NOT_REQUIRED));
			$this->_OUTLOOK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_OUTLOOK));
			$this->_OVAL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_OVAL));
			$this->_PARADE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PARADE));
			$this->_PARADISE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PARADISE));
			$this->_PARK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PARK));
			$this->_PARK_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PARK_2));
			$this->_PARKLANDS					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PARKLANDS));
			$this->_PARKWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PARKWAY));
			$this->_PART						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PART));
			$this->_PASS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PASS));
			$this->_PATH						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PATH));
			$this->_PATHWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PATHWAY));
			$this->_PATHWAY_2					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PATHWAY_2));
			$this->_PENINSULA					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PENINSULA));
			$this->_PIAZZA						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PIAZZA));
			$this->_PIER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PIER));
			$this->_PLACE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PLACE));
			$this->_PLATEAU						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PLATEAU));
			$this->_PLAZA						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PLAZA));
			$this->_POCKET						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_POCKET));
			$this->_POINT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_POINT));
			$this->_PORT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PORT));
			$this->_PORT_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PORT_2));
			$this->_PROMENADE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PROMENADE));
			$this->_PURSUIT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_PURSUIT));
			$this->_QUAD						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_QUAD));
			$this->_QUADRANGLE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_QUADRANGLE));
			$this->_QUADRANT					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_QUADRANT));
			$this->_QUAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_QUAY));
			$this->_QUAYS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_QUAYS));
			$this->_RACECOURSE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RACECOURSE));
			$this->_RAMBLE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RAMBLE));
			$this->_RAMP						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RAMP));
			$this->_RANGE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RANGE));
			$this->_REACH						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_REACH));
			$this->_RESERVE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RESERVE));
			$this->_REST						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_REST));
			$this->_RETREAT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RETREAT));
			$this->_RETURN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RETURN));
			$this->_RIDE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIDE));
			$this->_RIDGE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIDGE));
			$this->_RIDGEWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIDGEWAY));
			$this->_RIGHT_OF_WAY				= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIGHT_OF_WAY));
			$this->_RING						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RING));
			$this->_RISE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RISE));
			$this->_RIVER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIVER));
			$this->_RIVERWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIVERWAY));
			$this->_RIVIERA						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RIVIERA));
			$this->_ROAD						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROAD));
			$this->_ROADS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROADS));
			$this->_ROADSIDE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROADSIDE));
			$this->_ROADWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROADWAY));
			$this->_RONDE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RONDE));
			$this->_ROSEBOWL					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROSEBOWL));
			$this->_ROTARY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROTARY));
			$this->_ROUND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROUND));
			$this->_ROUTE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROUTE));
			$this->_ROW							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROW));
			$this->_ROWE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_ROWE));
			$this->_RUE							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RUE));
			$this->_RUN							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_RUN));
			$this->_SECTION						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SECTION));
			$this->_SERVICE_WAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SERVICE_WAY));
			$this->_SIDING						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SIDING));
			$this->_SLOPE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SLOPE));
			$this->_SOUND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SOUND));
			$this->_SPUR						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SPUR));
			$this->_SQUARE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SQUARE));
			$this->_STAIRS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STAIRS));
			$this->_STATE_HIGHWAY				= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STATE_HIGHWAY));
			$this->_STATION						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STATION));
			$this->_STEPS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STEPS));
			$this->_STOP						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STOP));
			$this->_STRAIGHT					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STRAIGHT));
			$this->_STRAND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STRAND));
			$this->_STREET						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STREET));
			$this->_STRIP						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STRIP));
			$this->_STRIP_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_STRIP_2));
			$this->_SUBWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_SUBWAY));
			$this->_TARN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TARN));
			$this->_TERRACE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TERRACE));
			$this->_THOROUGHFARE				= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_THOROUGHFARE));
			$this->_TOLLWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TOLLWAY));
			$this->_TOP							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TOP));
			$this->_TOR							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TOR));
			$this->_TOWER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TOWER));
			$this->_TOWERS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TOWERS));
			$this->_TRACK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TRACK));
			$this->_TRAIL						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TRAIL));
			$this->_TRAILER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TRAILER));
			$this->_TRIANGLE					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TRIANGLE));
			$this->_TRUNKWAY					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TRUNKWAY));
			$this->_TURN						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_TURN));
			$this->_UNDERPASS					= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_UNDERPASS));
			$this->_UPPER						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_UPPER));
			$this->_VALE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VALE));
			$this->_VALLEY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VALLEY));
			$this->_VIADUCT						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VIADUCT));
			$this->_VIEW						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VIEW));
			$this->_VILLAGE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VILLAGE));
			$this->_VILLAS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VILLAS));
			$this->_VISTA						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_VISTA));
			$this->_WADE						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WADE));
			$this->_WALK						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WALK));
			$this->_WALK_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WALK_2));
			$this->_WALKWAY						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WALKWAY));
			$this->_WATERS						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WATERS));
			$this->_WAY							= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WAY));
			$this->_WAY_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WAY_2));
			$this->_WEST						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WEST));
			$this->_WHARF						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WHARF));
			$this->_WHARF_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WHARF_2));
			$this->_WOOD						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WOOD));
			$this->_WYND						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_WYND));
			$this->_YARD						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_YARD));
			$this->_YARD_2						= $this->Push (new ServiceStreetType (SERVICE_STREET_TYPE_YARD_2));
				
			$this->setValue ($strServiceStreetType);
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
		
		public function setValue ($strServiceStreetType)
		{
			// Select the value
			switch ($strServiceStreetType)
			{
				case SERVICE_STREET_TYPE_ALLEY:					$this->Select ($this->_ALLEY);					return true;
				case SERVICE_STREET_TYPE_ALLEYWAY:				$this->Select ($this->_ALLEYWAY);				return true;
				case SERVICE_STREET_TYPE_AMBLE:					$this->Select ($this->_AMBLE);					return true;
				case SERVICE_STREET_TYPE_ANCHORAGE:				$this->Select ($this->_ANCHORAGE);				return true;
				case SERVICE_STREET_TYPE_APPROACH:				$this->Select ($this->_APPROACH);				return true;
				case SERVICE_STREET_TYPE_ARCADE:				$this->Select ($this->_ARCADE);					return true;
				case SERVICE_STREET_TYPE_ARTERIAL:				$this->Select ($this->_ARTERIAL);				return true;
				case SERVICE_STREET_TYPE_ARTERY:				$this->Select ($this->_ARTERY);					return true;
				case SERVICE_STREET_TYPE_AVENUE:				$this->Select ($this->_AVENUE);					return true;
				case SERVICE_STREET_TYPE_AVENUE_2:				$this->Select ($this->_ABENUE_2);				return true;
				case SERVICE_STREET_TYPE_BANK:					$this->Select ($this->_BANK);					return true;
				case SERVICE_STREET_TYPE_BARRACKS:				$this->Select ($this->_BARRACKS);				return true;
				case SERVICE_STREET_TYPE_BASIN:					$this->Select ($this->_BASIN);					return true;
				case SERVICE_STREET_TYPE_BAY:					$this->Select ($this->_BAY);					return true;
				case SERVICE_STREET_TYPE_BAY_2:					$this->Select ($this->_BAY_2);					return true;
				case SERVICE_STREET_TYPE_BEACH:					$this->Select ($this->_BEACH);					return true;
				case SERVICE_STREET_TYPE_BEND:					$this->Select ($this->_BEND);					return true;
				case SERVICE_STREET_TYPE_BLOCK:					$this->Select ($this->_BLOCK);					return true;
				case SERVICE_STREET_TYPE_BOULEVARD:				$this->Select ($this->_BOULEVARD);				return true;
				case SERVICE_STREET_TYPE_BOULEVARD_2:			$this->Select ($this->_BOULEVARD);				return true;
				case SERVICE_STREET_TYPE_BOUNDARY:				$this->Select ($this->_BOUNDARY);				return true;
				case SERVICE_STREET_TYPE_BOWL:					$this->Select ($this->_BOWL);					return true;
				case SERVICE_STREET_TYPE_BRACE:					$this->Select ($this->_BRACE);					return true;
				case SERVICE_STREET_TYPE_BRACE_2:				$this->Select ($this->_BRACE_2);				return true;
				case SERVICE_STREET_TYPE_BRAE:					$this->Select ($this->_BRAE);					return true;
				case SERVICE_STREET_TYPE_BRANCH:				$this->Select ($this->_BRANCH);					return true;
				case SERVICE_STREET_TYPE_BREA:					$this->Select ($this->_BREA);					return true;
				case SERVICE_STREET_TYPE_BREAK:					$this->Select ($this->_BREAK);					return true;
				case SERVICE_STREET_TYPE_BRIDGE:				$this->Select ($this->_BRIDGE);					return true;
				case SERVICE_STREET_TYPE_BRIDGE_2:				$this->Select ($this->_BRIDGE_2);				return true;
				case SERVICE_STREET_TYPE_BROADWAY:				$this->Select ($this->_BROADWAY);				return true;
				case SERVICE_STREET_TYPE_BROW:					$this->Select ($this->_BROW);					return true;
				case SERVICE_STREET_TYPE_BYPASS:				$this->Select ($this->_BYPASS);					return true;
				case SERVICE_STREET_TYPE_BYWAY:					$this->Select ($this->_BYWAY);					return true;
				case SERVICE_STREET_TYPE_CAUSEWAY:				$this->Select ($this->_CAUSEWAY);				return true;
				case SERVICE_STREET_TYPE_CENTRE:				$this->Select ($this->_CENTRE);					return true;
				case SERVICE_STREET_TYPE_CENTRE_2:				$this->Select ($this->_CENTRE_2);				return true;
				case SERVICE_STREET_TYPE_CENTREWAY:				$this->Select ($this->_CENTREWAY);				return true;
				case SERVICE_STREET_TYPE_CHASE:					$this->Select ($this->_CHASE);					return true;
				case SERVICE_STREET_TYPE_CIRCLE:				$this->Select ($this->_CIRCLE);					return true;
				case SERVICE_STREET_TYPE_CIRCLET:				$this->Select ($this->_CIRCLET);				return true;
				case SERVICE_STREET_TYPE_CIRCUIT:				$this->Select ($this->_CIRCUIT);				return true;
				case SERVICE_STREET_TYPE_CIRCUIT_2:				$this->Select ($this->_CIRCUIT_2);				return true;
				case SERVICE_STREET_TYPE_CIRCUS:				$this->Select ($this->_CIRCUS);					return true;
				case SERVICE_STREET_TYPE_CLOSE:					$this->Select ($this->_CLOSE);					return true;
				case SERVICE_STREET_TYPE_COLONNADE:				$this->Select ($this->_COLONNADE);				return true;
				case SERVICE_STREET_TYPE_COMMON:				$this->Select ($this->_COMMON);					return true;
				case SERVICE_STREET_TYPE_COMMUNITY:				$this->Select ($this->_COMMUNITY);				return true;
				case SERVICE_STREET_TYPE_CONCOURSE:				$this->Select ($this->_CONCOURSE);				return true;
				case SERVICE_STREET_TYPE_CONNECTION:			$this->Select ($this->_CONNECTION);				return true;
				case SERVICE_STREET_TYPE_COPSE:					$this->Select ($this->_COPSE);					return true;
				case SERVICE_STREET_TYPE_CORNER:				$this->Select ($this->_CORNER);					return true;
				case SERVICE_STREET_TYPE_CORSO:					$this->Select ($this->_CORSO);					return true;
				case SERVICE_STREET_TYPE_COURSE:				$this->Select ($this->_COURSE);					return true;
				case SERVICE_STREET_TYPE_COURT:					$this->Select ($this->_COURT);					return true;
				case SERVICE_STREET_TYPE_COURTYARD:				$this->Select ($this->_COURTYARD);				return true;
				case SERVICE_STREET_TYPE_COVE:					$this->Select ($this->_COVE);					return true;
				case SERVICE_STREET_TYPE_CREEK:					$this->Select ($this->_CREEK);					return true;
				case SERVICE_STREET_TYPE_CREEK_2:				$this->Select ($this->_CREEK_2);				return true;
				case SERVICE_STREET_TYPE_CRESCENT:				$this->Select ($this->_CRESCENT);				return true;
				case SERVICE_STREET_TYPE_CRESCENT_2:			$this->Select ($this->_CRESCENT_2);				return true;
				case SERVICE_STREET_TYPE_CREST:					$this->Select ($this->_CREST);					return true;
				case SERVICE_STREET_TYPE_CRIEF:					$this->Select ($this->_CRIEF);					return true;
				case SERVICE_STREET_TYPE_CROSS:					$this->Select ($this->_CROSS);					return true;
				case SERVICE_STREET_TYPE_CROSSING:				$this->Select ($this->_CROSSING);				return true;
				case SERVICE_STREET_TYPE_CROSSROADS:			$this->Select ($this->_CROSSROADS);				return true;
				case SERVICE_STREET_TYPE_CROSSWAY:				$this->Select ($this->_CROSSWAY);				return true;
				case SERVICE_STREET_TYPE_CRUISEWAY:				$this->Select ($this->_CRUISEWAY);				return true;
				case SERVICE_STREET_TYPE_CUL_DE_SAC:			$this->Select ($this->_CUL_DE_SAC);				return true;
				case SERVICE_STREET_TYPE_CUTTING:				$this->Select ($this->_CUTTING);				return true;
				case SERVICE_STREET_TYPE_DALE:					$this->Select ($this->_DALE);					return true;
				case SERVICE_STREET_TYPE_DELL:					$this->Select ($this->_DELL);					return true;
				case SERVICE_STREET_TYPE_DEVIATION:				$this->Select ($this->_DEVIATION);				return true;
				case SERVICE_STREET_TYPE_DIP:					$this->Select ($this->_DIP);					return true;
				case SERVICE_STREET_TYPE_DISTRIBUTOR:			$this->Select ($this->_DISTRIBUTOR);			return true;
				case SERVICE_STREET_TYPE_DOWNS:					$this->Select ($this->_DOWNS);					return true;
				case SERVICE_STREET_TYPE_DRIVE:					$this->Select ($this->_DRIVE);					return true;
				case SERVICE_STREET_TYPE_DRIVE_2:				$this->Select ($this->_DRIVE_2);				return true;
				case SERVICE_STREET_TYPE_DRIVEWAY:				$this->Select ($this->_DRIVEWAY);				return true;
				case SERVICE_STREET_TYPE_EASEMENT:				$this->Select ($this->_EASEMENT);				return true;
				case SERVICE_STREET_TYPE_EDGE:					$this->Select ($this->_EDGE);					return true;
				case SERVICE_STREET_TYPE_ELBOW:					$this->Select ($this->_ELBOW);					return true;
				case SERVICE_STREET_TYPE_END:					$this->Select ($this->_END);					return true;
				case SERVICE_STREET_TYPE_ENTRANCE:				$this->Select ($this->_ENTRANCE);				return true;
				case SERVICE_STREET_TYPE_ESPLANADE:				$this->Select ($this->_ESPLANADE);				return true;
				case SERVICE_STREET_TYPE_ESTATE:				$this->Select ($this->_ESTATE);					return true;
				case SERVICE_STREET_TYPE_EXPRESSWAY:			$this->Select ($this->_EXPRESSWAY);				return true;
				case SERVICE_STREET_TYPE_EXPRESSWAY_2:			$this->Select ($this->_EXPRESSWAY_2);			return true;
				case SERVICE_STREET_TYPE_EXTENSION:				$this->Select ($this->_EXTENSION);				return true;
				case SERVICE_STREET_TYPE_EXTENSION_2:			$this->Select ($this->_EXTENSION_2);			return true;
				case SERVICE_STREET_TYPE_FAIR:					$this->Select ($this->_FAIR);					return true;
				case SERVICE_STREET_TYPE_FAIRWAY:				$this->Select ($this->_FAIRWAY);				return true;
				case SERVICE_STREET_TYPE_FIRE_TRACK:			$this->Select ($this->_FIRE_TRACK);				return true;
				case SERVICE_STREET_TYPE_FIRETRAIL:				$this->Select ($this->_FIRETRAIL);				return true;
				case SERVICE_STREET_TYPE_FIRETRALL:				$this->Select ($this->_FIRETRALL);				return true;
				case SERVICE_STREET_TYPE_FLAT:					$this->Select ($this->_FLAT);					return true;
				case SERVICE_STREET_TYPE_FOLLOW:				$this->Select ($this->_FOLLOW);					return true;
				case SERVICE_STREET_TYPE_FOOTWAY:				$this->Select ($this->_FOOTWAY);				return true;
				case SERVICE_STREET_TYPE_FORESHORE:				$this->Select ($this->_FORESHORE);				return true;
				case SERVICE_STREET_TYPE_FORMATION:				$this->Select ($this->_FORMATION);				return true;
				case SERVICE_STREET_TYPE_FREEWAY:				$this->Select ($this->_FREEWAY);				return true;
				case SERVICE_STREET_TYPE_FREEWAY_2:				$this->Select ($this->_FREEWAY_2);				return true;
				case SERVICE_STREET_TYPE_FRONT:					$this->Select ($this->_FRONT);					return true;
				case SERVICE_STREET_TYPE_FRONTAGE:				$this->Select ($this->_FRONTAGE);				return true;
				case SERVICE_STREET_TYPE_GAP:					$this->Select ($this->_GAP);					return true;
				case SERVICE_STREET_TYPE_GARDEN:				$this->Select ($this->_GARDEN);					return true;
				case SERVICE_STREET_TYPE_GARDENS:				$this->Select ($this->_GARDENS);				return true;
				case SERVICE_STREET_TYPE_GATE:					$this->Select ($this->_GATE);					return true;
				case SERVICE_STREET_TYPE_GATES:					$this->Select ($this->_GATES);					return true;
				case SERVICE_STREET_TYPE_GATEWAY:				$this->Select ($this->_GATEWAY);				return true;
				case SERVICE_STREET_TYPE_GLADE:					$this->Select ($this->_GLADE);					return true;
				case SERVICE_STREET_TYPE_GLEN:					$this->Select ($this->_GLEN);					return true;
				case SERVICE_STREET_TYPE_GRANGE:				$this->Select ($this->_GRANGE);					return true;
				case SERVICE_STREET_TYPE_GREEN:					$this->Select ($this->_GREEN);					return true;
				case SERVICE_STREET_TYPE_GROUND:				$this->Select ($this->_GROUND);					return true;
				case SERVICE_STREET_TYPE_GROVE:					$this->Select ($this->_GROVE);					return true;
				case SERVICE_STREET_TYPE_GROVE_2:				$this->Select ($this->_GROVE_2);				return true;
				case SERVICE_STREET_TYPE_GULLY:					$this->Select ($this->_GULLY);					return true;
				case SERVICE_STREET_TYPE_HEATH:					$this->Select ($this->_HEATH);					return true;
				case SERVICE_STREET_TYPE_HEIGHTS:				$this->Select ($this->_HEIGHTS);				return true;
				case SERVICE_STREET_TYPE_HIGHROAD:				$this->Select ($this->_HIGHROAD);				return true;
				case SERVICE_STREET_TYPE_HIGHWAY:				$this->Select ($this->_HIGHWAY);				return true;
				case SERVICE_STREET_TYPE_HILL:					$this->Select ($this->_HILL);					return true;
				case SERVICE_STREET_TYPE_HILLSIDE:				$this->Select ($this->_HILLSIDE);				return true;
				case SERVICE_STREET_TYPE_HOUSE:					$this->Select ($this->_HOUSE);					return true;
				case SERVICE_STREET_TYPE_INTERCHANGE:			$this->Select ($this->_INTERCHANGE);			return true;
				case SERVICE_STREET_TYPE_INTERSECTION:			$this->Select ($this->_INTERSECTION);			return true;
				case SERVICE_STREET_TYPE_ISLAND:				$this->Select ($this->_ISLAND);					return true;
				case SERVICE_STREET_TYPE_JUNCTION:				$this->Select ($this->_JUNCTION);				return true;
				case SERVICE_STREET_TYPE_JUNCTION_2:			$this->Select ($this->_JUNCTION_2);				return true;
				case SERVICE_STREET_TYPE_KEY:					$this->Select ($this->_KEY);					return true;
				case SERVICE_STREET_TYPE_KNOLL:					$this->Select ($this->_KNOLL);					return true;
				case SERVICE_STREET_TYPE_LANDING:				$this->Select ($this->_LANDING);				return true;
				case SERVICE_STREET_TYPE_LANE:					$this->Select ($this->_LANE);					return true;
				case SERVICE_STREET_TYPE_LANE_2:				$this->Select ($this->_LANE_2);					return true;
				case SERVICE_STREET_TYPE_LANE_3:				$this->Select ($this->_LANE_3);					return true;
				case SERVICE_STREET_TYPE_LANEWAY:				$this->Select ($this->_LANEWAY);				return true;
				case SERVICE_STREET_TYPE_LEES:					$this->Select ($this->_LEES);					return true;
				case SERVICE_STREET_TYPE_LINE:					$this->Select ($this->_LINE);					return true;
				case SERVICE_STREET_TYPE_LINK:					$this->Select ($this->_LINK);					return true;
				case SERVICE_STREET_TYPE_LITTLE:				$this->Select ($this->_LITTLE);					return true;
				case SERVICE_STREET_TYPE_LOCATION:				$this->Select ($this->_LOCATION);				return true;
				case SERVICE_STREET_TYPE_LOOKOUT:				$this->Select ($this->_LOOKOUT);				return true;
				case SERVICE_STREET_TYPE_LOOP:					$this->Select ($this->_LOOP);					return true;
				case SERVICE_STREET_TYPE_LOWER:					$this->Select ($this->_LOWER);					return true;
				case SERVICE_STREET_TYPE_MALL:					$this->Select ($this->_MALL);					return true;
				case SERVICE_STREET_TYPE_MARKETLAND:			$this->Select ($this->_MARKETLAND);				return true;
				case SERVICE_STREET_TYPE_MARKETTOWN:			$this->Select ($this->_MARKETTOWN);				return true;
				case SERVICE_STREET_TYPE_MEAD:					$this->Select ($this->_MEAD);					return true;
				case SERVICE_STREET_TYPE_MEANDER:				$this->Select ($this->_MEANDER);				return true;
				case SERVICE_STREET_TYPE_MEW:					$this->Select ($this->_MEW);					return true;
				case SERVICE_STREET_TYPE_MEWS:					$this->Select ($this->_MEWS);					return true;
				case SERVICE_STREET_TYPE_MOTORWAY:				$this->Select ($this->_MOTORWAY);				return true;
				case SERVICE_STREET_TYPE_MOUNT:					$this->Select ($this->_MOUNT);					return true;
				case SERVICE_STREET_TYPE_MOUNTAIN:				$this->Select ($this->_MOUNTAIN);				return true;
				case SERVICE_STREET_TYPE_NOOK:					$this->Select ($this->_NOOK);					return true;
				case SERVICE_STREET_TYPE_NOT_REQUIRED:			$this->Select ($this->_NOT_REQUIRED);			return true;
				case SERVICE_STREET_TYPE_OUTLOOK:				$this->Select ($this->_OUTLOOK);				return true;
				case SERVICE_STREET_TYPE_OVAL:					$this->Select ($this->_OVAL);					return true;
				case SERVICE_STREET_TYPE_PARADE:				$this->Select ($this->_PARADE);					return true;
				case SERVICE_STREET_TYPE_PARADISE:				$this->Select ($this->_PARADISE);				return true;
				case SERVICE_STREET_TYPE_PARK:					$this->Select ($this->_PARK);					return true;
				case SERVICE_STREET_TYPE_PARK_2:				$this->Select ($this->_PARK_2);					return true;
				case SERVICE_STREET_TYPE_PARKLANDS:				$this->Select ($this->_PARKLANDS);				return true;
				case SERVICE_STREET_TYPE_PARKWAY:				$this->Select ($this->_PARKWAY);				return true;
				case SERVICE_STREET_TYPE_PART:					$this->Select ($this->_PART);					return true;
				case SERVICE_STREET_TYPE_PASS:					$this->Select ($this->_PASS);					return true;
				case SERVICE_STREET_TYPE_PATH:					$this->Select ($this->_PATH);					return true;
				case SERVICE_STREET_TYPE_PATHWAY:				$this->Select ($this->_PATHWAY);				return true;
				case SERVICE_STREET_TYPE_PATHWAY_2:				$this->Select ($this->_PATHWAY_2);				return true;
				case SERVICE_STREET_TYPE_PENINSULA:				$this->Select ($this->_PENINSULA);				return true;
				case SERVICE_STREET_TYPE_PIAZZA:				$this->Select ($this->_PIAZZA);					return true;
				case SERVICE_STREET_TYPE_PIER:					$this->Select ($this->_PIER);					return true;
				case SERVICE_STREET_TYPE_PLACE:					$this->Select ($this->_PLACE);					return true;
				case SERVICE_STREET_TYPE_PLATEAU:				$this->Select ($this->_PLATEAU);				return true;
				case SERVICE_STREET_TYPE_PLAZA:					$this->Select ($this->_PLAZA);					return true;
				case SERVICE_STREET_TYPE_POCKET:				$this->Select ($this->_POCKET);					return true;
				case SERVICE_STREET_TYPE_POINT:					$this->Select ($this->_POINT);					return true;
				case SERVICE_STREET_TYPE_PORT:					$this->Select ($this->_PORT);					return true;
				case SERVICE_STREET_TYPE_PORT_2:				$this->Select ($this->_PORT_2);					return true;
				case SERVICE_STREET_TYPE_PROMENADE:				$this->Select ($this->_PROMENADE);				return true;
				case SERVICE_STREET_TYPE_PURSUIT:				$this->Select ($this->_PURSUIT);				return true;
				case SERVICE_STREET_TYPE_QUAD:					$this->Select ($this->_QUAD);					return true;
				case SERVICE_STREET_TYPE_QUADRANGLE:			$this->Select ($this->_QUADRANGLE);				return true;
				case SERVICE_STREET_TYPE_QUADRANT:				$this->Select ($this->_QUADRANT);				return true;
				case SERVICE_STREET_TYPE_QUAY:					$this->Select ($this->_QUAY);					return true;
				case SERVICE_STREET_TYPE_QUAYS:					$this->Select ($this->_QUAYS);					return true;
				case SERVICE_STREET_TYPE_RACECOURSE:			$this->Select ($this->_RACECOURSE);				return true;
				case SERVICE_STREET_TYPE_RAMBLE:				$this->Select ($this->_RAMBLE);					return true;
				case SERVICE_STREET_TYPE_RAMP:					$this->Select ($this->_RAMP);					return true;
				case SERVICE_STREET_TYPE_RANGE:					$this->Select ($this->_RANGE);					return true;
				case SERVICE_STREET_TYPE_REACH:					$this->Select ($this->_REACH);					return true;
				case SERVICE_STREET_TYPE_RESERVE:				$this->Select ($this->_RESERVE);				return true;
				case SERVICE_STREET_TYPE_REST:					$this->Select ($this->_REST);					return true;
				case SERVICE_STREET_TYPE_RETREAT:				$this->Select ($this->_RETREAT);				return true;
				case SERVICE_STREET_TYPE_RETURN:				$this->Select ($this->_RETURN);					return true;
				case SERVICE_STREET_TYPE_RIDE:					$this->Select ($this->_RIDE);					return true;
				case SERVICE_STREET_TYPE_RIDGE:					$this->Select ($this->_RIDGE);					return true;
				case SERVICE_STREET_TYPE_RIDGEWAY:				$this->Select ($this->_RIDGEWAY);				return true;
				case SERVICE_STREET_TYPE_RIGHT_OF_WAY:			$this->Select ($this->_RIGHT_OF_WAY);			return true;
				case SERVICE_STREET_TYPE_RING:					$this->Select ($this->_RING);					return true;
				case SERVICE_STREET_TYPE_RISE:					$this->Select ($this->_RISE);					return true;
				case SERVICE_STREET_TYPE_RIVER:					$this->Select ($this->_RIVER);					return true;
				case SERVICE_STREET_TYPE_RIVERWAY:				$this->Select ($this->_RIVERWAY);				return true;
				case SERVICE_STREET_TYPE_RIVIERA:				$this->Select ($this->_RIVIERA);				return true;
				case SERVICE_STREET_TYPE_ROAD:					$this->Select ($this->_ROAD);					return true;
				case SERVICE_STREET_TYPE_ROADS:					$this->Select ($this->_ROADS);					return true;
				case SERVICE_STREET_TYPE_ROADSIDE:				$this->Select ($this->_ROADSIDE);				return true;
				case SERVICE_STREET_TYPE_ROADWAY:				$this->Select ($this->_ROADWAY);				return true;
				case SERVICE_STREET_TYPE_RONDE:					$this->Select ($this->_RONDE);					return true;
				case SERVICE_STREET_TYPE_ROSEBOWL:				$this->Select ($this->_ROSEBOWL);				return true;
				case SERVICE_STREET_TYPE_ROTARY:				$this->Select ($this->_ROTARY);					return true;
				case SERVICE_STREET_TYPE_ROUND:					$this->Select ($this->_ROUND);					return true;
				case SERVICE_STREET_TYPE_ROUTE:					$this->Select ($this->_ROUTE);					return true;
				case SERVICE_STREET_TYPE_ROW:					$this->Select ($this->_ROW);					return true;
				case SERVICE_STREET_TYPE_ROWE:					$this->Select ($this->_ROWE);					return true;
				case SERVICE_STREET_TYPE_RUE:					$this->Select ($this->_RUE);					return true;
				case SERVICE_STREET_TYPE_RUN:					$this->Select ($this->_RUN);					return true;
				case SERVICE_STREET_TYPE_SECTION:				$this->Select ($this->_SECTION);				return true;
				case SERVICE_STREET_TYPE_SERVICE_WAY:			$this->Select ($this->_SERVICE_WAY);			return true;
				case SERVICE_STREET_TYPE_SIDING:				$this->Select ($this->_SIDING);					return true;
				case SERVICE_STREET_TYPE_SLOPE:					$this->Select ($this->_SLOPE);					return true;
				case SERVICE_STREET_TYPE_SOUND:					$this->Select ($this->_SOUND);					return true;
				case SERVICE_STREET_TYPE_SPUR:					$this->Select ($this->_SPUR);					return true;
				case SERVICE_STREET_TYPE_SQUARE:				$this->Select ($this->_SQUARE);					return true;
				case SERVICE_STREET_TYPE_STAIRS:				$this->Select ($this->_STAIRS);					return true;
				case SERVICE_STREET_TYPE_STATE_HIGHWAY:			$this->Select ($this->_STATE_HIGHWAY);			return true;
				case SERVICE_STREET_TYPE_STATION:				$this->Select ($this->_STATION);				return true;
				case SERVICE_STREET_TYPE_STEPS:					$this->Select ($this->_STEPS);					return true;
				case SERVICE_STREET_TYPE_STOP:					$this->Select ($this->_STOP);					return true;
				case SERVICE_STREET_TYPE_STRAIGHT:				$this->Select ($this->_STRAIGHT);				return true;
				case SERVICE_STREET_TYPE_STRAND:				$this->Select ($this->_STRAND);					return true;
				case SERVICE_STREET_TYPE_STREET:				$this->Select ($this->_STREET);					return true;
				case SERVICE_STREET_TYPE_STRIP:					$this->Select ($this->_STRIP);					return true;
				case SERVICE_STREET_TYPE_STRIP_2:				$this->Select ($this->_STRIP_2);				return true;
				case SERVICE_STREET_TYPE_SUBWAY:				$this->Select ($this->_SUBWAY);					return true;
				case SERVICE_STREET_TYPE_TARN:					$this->Select ($this->_TARN);					return true;
				case SERVICE_STREET_TYPE_TERRACE:				$this->Select ($this->_TERRACE);				return true;
				case SERVICE_STREET_TYPE_THOROUGHFARE:			$this->Select ($this->_THOROUGHFARE);			return true;
				case SERVICE_STREET_TYPE_TOLLWAY:				$this->Select ($this->_TOLLWAY);				return true;
				case SERVICE_STREET_TYPE_TOP:					$this->Select ($this->_TOP);					return true;
				case SERVICE_STREET_TYPE_TOR:					$this->Select ($this->_TOR);					return true;
				case SERVICE_STREET_TYPE_TOWER:					$this->Select ($this->_TOWER);					return true;
				case SERVICE_STREET_TYPE_TOWERS:				$this->Select ($this->_TOWERS);					return true;
				case SERVICE_STREET_TYPE_TRACK:					$this->Select ($this->_TRACK);					return true;
				case SERVICE_STREET_TYPE_TRAIL:					$this->Select ($this->_TRAIL);					return true;
				case SERVICE_STREET_TYPE_TRAILER:				$this->Select ($this->_TRAILER);				return true;
				case SERVICE_STREET_TYPE_TRIANGLE:				$this->Select ($this->_TRIANGLE);				return true;
				case SERVICE_STREET_TYPE_TRUNKWAY:				$this->Select ($this->_TRUNKWAY);				return true;
				case SERVICE_STREET_TYPE_TURN:					$this->Select ($this->_TURN);					return true;
				case SERVICE_STREET_TYPE_UNDERPASS:				$this->Select ($this->_UNDERPASS);				return true;
				case SERVICE_STREET_TYPE_UPPER:					$this->Select ($this->_UPPER);					return true;
				case SERVICE_STREET_TYPE_VALE:					$this->Select ($this->_VALE);					return true;
				case SERVICE_STREET_TYPE_VALLEY:				$this->Select ($this->_VALLEY);					return true;
				case SERVICE_STREET_TYPE_VIADUCT:				$this->Select ($this->_VIADUCT);				return true;
				case SERVICE_STREET_TYPE_VIEW:					$this->Select ($this->_VIEW);					return true;
				case SERVICE_STREET_TYPE_VILLAGE:				$this->Select ($this->_VILLAGE);				return true;
				case SERVICE_STREET_TYPE_VILLAS:				$this->Select ($this->_VILLAS);					return true;
				case SERVICE_STREET_TYPE_VISTA:					$this->Select ($this->_VISTA);					return true;
				case SERVICE_STREET_TYPE_WADE:					$this->Select ($this->_WADE);					return true;
				case SERVICE_STREET_TYPE_WALK:					$this->Select ($this->_WALK);					return true;
				case SERVICE_STREET_TYPE_WALK_2:				$this->Select ($this->_WALK_2);					return true;
				case SERVICE_STREET_TYPE_WALKWAY:				$this->Select ($this->_WALKWAY);				return true;
				case SERVICE_STREET_TYPE_WATERS:				$this->Select ($this->_WATERS);					return true;
				case SERVICE_STREET_TYPE_WAY:					$this->Select ($this->_WAY);					return true;
				case SERVICE_STREET_TYPE_WAY_2:					$this->Select ($this->_WAY_2);					return true;
				case SERVICE_STREET_TYPE_WEST:					$this->Select ($this->_WEST);					return true;
				case SERVICE_STREET_TYPE_WHARF:					$this->Select ($this->_WHARF);					return true;
				case SERVICE_STREET_TYPE_WHARF_2:				$this->Select ($this->_WHARF_2);				return true;
				case SERVICE_STREET_TYPE_WOOD:					$this->Select ($this->_WOOD);					return true;
				case SERVICE_STREET_TYPE_WYND:					$this->Select ($this->_WYND);					return true;
				case SERVICE_STREET_TYPE_YARD:					$this->Select ($this->_YARD);					return true;
				case SERVICE_STREET_TYPE_YARD_2:				$this->Select ($this->_YARD_2);					return true;
				default:						return false;
			}
		}
	}
	
?>
