<?php
	
	//----------------------------------------------------------------------------//
	// rate.php
	//----------------------------------------------------------------------------//
	/**
	 * rate.php
	 *
	 * File containing Rate Class
	 *
	 * File containing Rate Class
	 *
	 * @file		rate.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Rate
	//----------------------------------------------------------------------------//
	/**
	 * Rate
	 *
	 * Holds Rate Information
	 *
	 * Holds Rate Information
	 *
	 *
	 * @prefix		rte
	 *
	 * @package		intranet_app
	 * @class		Rate
	 * @extends		dataObject
	 */
	
	class Rate extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new Rate with its information
		 *
		 * Constructs a new Rate with its information
		 *
		 * @param	Integer		$intId		The Id of the Rate being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('Rate', $intId);
			
			$selRate = new StatementSelect ('Rate', '*', 'Id = <Id>');
			$selRate->useObLib (TRUE);
			$selRate->Execute (Array ('Id' => $intId));
			$selRate->Fetch ($this);
			
			// Get Named ServiceType information
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
			
			// Get Named RecordType Information
			$intRecordType = $this->Pop ('RecordType')->getValue ();
			$this->Push (new RecordType ($intRecordType));
			
			
			
			// Work out what Quarter Hour the Rate lies between
			// This next section deals with the calculations of
			// 1. The Number of the First Time Quarter
			// 2. The Number of Quarter Hours that there are in the Time Equasion
			
			$intStartTime = ($this->Pull ('StartTime')->Pull ('hour')->getValue () * 60) + ($this->Pull ('StartTime')->Pull ('minute')->getValue ());
			$intEndTime = ($this->Pull ('EndTime')->Pull ('hour')->getValue () * 60) + ($this->Pull ('EndTime')->Pull ('minute')->getValue () + 1);
			
			// (1) Location of First Quarter
			$this->Push (new dataInteger ('quarter-first',  
				($this->Pull ('StartTime')->Pull ('hour')->getValue () * 4) + ($this->Pull ('StartTime')->Pull ('minute')->getValue () / 15)
			));
			
			// (2) Number of Quarters
			$this->Push (new dataInteger ('quarter-length', ($intEndTime - $intStartTime) / 15));
		}
	}
	
?>
