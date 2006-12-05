<?php
	
	//----------------------------------------------------------------------------//
	// rategroups.php
	//----------------------------------------------------------------------------//
	/**
	 * rategroups.php
	 *
	 * File that contains a Controller for Rate Groups
	 *
	 * File that contains a Controller for Rate Groups
	 *
	 * @file		rategroups.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// RateGroups
	//----------------------------------------------------------------------------//
	/**
	 * RateGroups
	 *
	 * Rate Group Searching
	 *
	 * Class for Searching and Evaluating Multiple Rate Groups
	 *
	 *
	 * @prefix		rgl
	 *
	 * @package		intranet_app
	 * @class		RateGroups
	 * @extends		Search
	 */
	
	class RateGroups extends Search
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new Rate Group Searching Object
		 *
		 * Constructs a new Rate Group Searching Object
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('RateGroups', 'RateGroup', 'RateGroup');
		}
		
		//------------------------------------------------------------------------//
		// UnarchivedNameExists
		//------------------------------------------------------------------------//
		/**
		 * UnarchivedNameExists()
		 *
		 * Check for Name Availability
		 *
		 * Check to see whether or not a particular Name exists for an Unarchived Item.
		 * This is used mainly for Adding new Items without Duplicate Names
		 *
		 * @param	String		$strName		The name of the Unarchived Item being searched for
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function UnarchivedNameExists ($strName)
		{
			$selRate = new StatementSelect (
				"RateGroup", 
				"count(*) AS Length", 
				"Name = <Name> AND Archived = 0"
			);
			
			$selRate->Execute (Array ("Name" => $strName));
			$arrLength = $selRate->Fetch ();
			
			return $arrLength ['Length'] <> 0;
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Add a new Rate Group
		 *
		 * Add a new Rate Group to the Database
		 *
		 * @param	Array		$arrRateGroup		Contains database information for a new Rate Group
		 * @param	Array		$arrRates			Contains an Indexed array of Rates in the Rate Group
		 * @return	Integer							The Id of the New Rate Group
		 *
		 * @method
		 */
		
		public function Add ($arrRateGroup, $arrRates)
		{
			// Insert the Rate Group
			$insRateGroup = new StatementInsert ("RateGroup");
			$intRateGroup = $insRateGroup->Execute ($arrRateGroup);
			
			// Add the Rates
			foreach ($arrRates AS $intRate)
			{
				$arrRate = Array (
					"Rate"			=> $intRate, 
					"RateGroup"		=> $intRateGroup
				);
				
				$insRate = new StatementInsert ("RateGroupRate");
				$intRate = $insRate->Execute ($arrRate);
			}
			
			// Return the Id of the new Group
			return $intRateGroup;
		}
		
		//------------------------------------------------------------------------//
		// RateAvailability
		//------------------------------------------------------------------------//
		/**
		 * RateAvailability()
		 *
		 * Returns Cross Referencing for Rates
		 *
		 * This method is specifically used to figure out if specifically where
		 * Rates have or have not been applied, but also where rates are applied
		 * and overlap ontop of eachother. The presentation of this information is
		 * done in XSLT.
		 *
		 * @param	Array		$arrSelectedRate		An indexed array of Rates we wish to 
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function RateAvailability ($arrSelectedRates)
		{
			// This is a Conversion array for us to identify the days of the week
			$arrDaysOfWeek = Array (
				0	=> "Monday",
				1	=> "Tuesday",
				2	=> "Wednesday",
				3	=> "Thursday",
				4	=> "Friday",
				5	=> "Saturday",
				6	=> "Sunday"
			);
			
			
			// Build a multidimentional array with all the hours and the days in it
			// Example:
			// 0	(Monday)
				// 0	(12 AM)
					// 0	(12:00)
					// 15	(12:15)
					// ...
				// 1	(12 AM)
					// 0	(1:00)
					// 15	(1:15)
					// ...
			// 0	(Tuesday)
			// etc
			
			$oblarrAvailability = new dataArray ('Availability');
			
			$oblarrAvailabilityDay = Array ();
			$oblarrAvailabilityHour = Array ();
			$oblarrAvailabilityQuarter = Array ();
			
			for ($i=0; $i < 7; ++$i)
			{
				$oblarrAvailabilityDay [$arrDaysOfWeek[$i]] = $oblarrAvailability->Push (new dataArray ('Availability-Day'));
				$oblarrAvailabilityDay [$arrDaysOfWeek[$i]]->setAttribute ("name", $arrDaysOfWeek[$i]);
				
				for ($j=0; $j < 24; ++$j)
				{
					$oblarrAvailabilityHour [$arrDaysOfWeek[$i]][$j] = $oblarrAvailabilityDay [$arrDaysOfWeek[$i]]->Push (new dataArray ('Availability-Hour'));
					$oblarrAvailabilityHour [$arrDaysOfWeek[$i]][$j]->setAttribute ("number", $j);
					
					for ($k=0; $k < 4; ++$k)
					{
						$oblarrAvailabilityQuarter [$arrDaysOfWeek[$i]][($j * 4) + $k] = $oblarrAvailabilityHour [$arrDaysOfWeek[$i]][$j]->Push (
							new dataArray ('Availability-Quarter')
						);
						
						$oblarrAvailabilityQuarter [$arrDaysOfWeek[$i]][($j * 4) + $k]->setAttribute ("number", $k * 15);
					}
				}
			}
			
			// Now - Populate the Array with all the Values
			
			// Firstly, draw up an abstract query. We won't be doing any error checking
			// because the information sent through should not be tained
			
			$selRate = new StatementSelect (
				"Rate", 
				"Id, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday, StartTime, EndTime", 
				"Id = <Id>",
				null,
				1
			);
			
			foreach ($arrSelectedRates as $intRate)
			{
				$selRate->Execute (Array ("Id" => $intRate));
				$arrRate = $selRate->Fetch ();
				
				// If the rate doesn't exist, just skip it. No error checking
				// because it is assumed that values are not Tainted.
				if ($arrRate == null)
				{
					continue;
				}
				
				// This next section deals with the calculations of
				// 1. The Number of Quarter Hours that there are in the Time Equasion
				// 2. The Number of the First Time Quarter
				preg_match ("/^(\d\d):(\d\d):(\d\d)$/", $arrRate ['StartTime'], $arrStartTime);
				preg_match ("/^(\d\d):(\d\d):(\d\d)$/", $arrRate ['EndTime'], $arrEndTime);
				
				$intStartTime = ($arrStartTime [1] * 60) + ($arrStartTime [2]);
				$intEndTime = ($arrEndTime [1] * 60) + ($arrEndTime [2] + 1);
				
				// (1) Number of Quarters
				$intDifferenceInQuarters = ($intEndTime - $intStartTime) / 15;
				
				// (2) Location of First Quarter
				$intFirstQuarter = ($arrStartTime [1] * 4) + ($arrStartTime [2] / 15);
				
				// Loop through each Day (Mon-Sun)
				for ($i=0; $i < 7; ++$i)
				{
					// For each of these days...
					
					// Check that the rate applies on this day
					if ($arrRate [$arrDaysOfWeek [$i]])
					{
						
						// Loop through each 1/4 hour in this day
						for ($j=0; $j < 96; ++$j)
						{
							
							// Put the Rate where it Belongs (between StartTime and EndTime)
							if ($j >= $intFirstQuarter && $j < $intFirstQuarter + $intDifferenceInQuarters)
							{
								$oblarrAvailabilityQuarter [$arrDaysOfWeek [$i]][$j]->Push (
									new dataString ('Rate', $arrRate ['Id'])
								);
							}
						}
					}
				}
			}
			
			return $oblarrAvailability;
		}
	}
	
?>
