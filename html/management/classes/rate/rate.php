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
			
			$selRate = new StatementSelect ('Rate', '*', 'Id = <Id>', null, 1);
			$selRate->useObLib (TRUE);
			$selRate->Execute (Array ('Id' => $intId));
			
			if ($selRate->Count () <> 1)
			{
				throw new Exception ('Rate Not Found: ' . $intId);
			}
			
			$selRate->Fetch ($this);
			
			// Get Named ServiceType information
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
			
			// Get Named RecordType Information
			$intRecordType = $this->Pop ('RecordType')->getValue ();
			$rdtRecordDisplayType = $this->Push (new RecordType ($intRecordType));
			
			// Get Record Display Type Information
			$rdtRecordDisplayType = $this->Push (new RecordDisplayType ($rdtRecordDisplayType->Pull ('DisplayType')->getValue ()));
			
			
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
		
		//------------------------------------------------------------------------//
		// RateGroups
		//------------------------------------------------------------------------//
		/**
		 * RateGroups()
		 *
		 * Pulls a list of Rate Groups which use this Rate
		 *
		 * Pulls a list of Rate Groups which use this Rate
		 *
		 * @return 		dataArray [RateGroup]
		 *
		 * @method
		 */
		
		public function RateGroups ()
		{
			$oblarrGroups	= new dataArray ('RateGroups', 'RateGroup');
			
			$selRateGroups	= new StatementSelect ('RateGroupRate', 'RateGroup', 'Rate = <Rate>');
			$selRateGroups->Execute (Array ('Rate' => $this->Pull ('Id')->getValue ()));
			
			foreach ($selRateGroups->FetchAll () as $arrRate)
			{
				$oblarrGroups->Push (new RateGroup ($arrRate ['RateGroup']));
			}
			
			return $oblarrGroups;
		}
	}
	
?>
