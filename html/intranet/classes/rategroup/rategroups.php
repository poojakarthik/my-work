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
		
		public function UnarchivedNameExists ($strName, $intServiceType)
		{
			$selRate = new StatementSelect (
				"RateGroup", 
				"count(*) AS Length", 
				"Name = <Name> AND ServiceType = <ServiceType> AND Archived = 0"
			);
			
			$selRate->Execute (Array ("Name" => $strName, "ServiceType" => $intServiceType));
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
			
			// - Week Days
			for ($i=0; $i < 7; ++$i)
			{
				$oblarrAvailabilityDay [$arrDaysOfWeek[$i]] = $oblarrAvailability->Push (new dataArray ('Availability-Day'));
				$oblarrAvailabilityDay [$arrDaysOfWeek[$i]]->setAttribute ("name", $arrDaysOfWeek[$i]);
				
				// - Hours
				for ($j=0; $j < 24; ++$j)
				{
					$oblarrAvailabilityHour [$arrDaysOfWeek[$i]][$j] = $oblarrAvailabilityDay [$arrDaysOfWeek[$i]]->Push (new dataArray ('Availability-Hour'));
					$oblarrAvailabilityHour [$arrDaysOfWeek[$i]][$j]->setAttribute ("number", $j);
					
					// Quarter Hours
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
			foreach ($arrSelectedRates as $intRate)
			{
				$rrrRate = new Rate ($intRate);
				
				// This section deals with Putting Rates in their Allocations
				// Loop through each Day (Mon-Sun)
				for ($i=0; $i < 7; ++$i)
				{
					// For each of these days...
					
					// Check that the rate applies on this day
					if ($rrrRate->Pull ($arrDaysOfWeek [$i])->getValue () == 1)
					{
						// Loop through each 1/4 hour in this day
						for ($j=0; $j < 96; ++$j)
						{
							// Put the Rate where it Belongs (between StartTime and EndTime)
							if (
							$j >= $rrrRate->Pull ("quarter-first")->getValue () && 
							$j < $rrrRate->Pull ("quarter-first")->getValue () + $rrrRate->Pull ("quarter-length")->getValue ())
							{
								$oblarrAvailabilityQuarter [$arrDaysOfWeek [$i]][$j]->Push (
									new dataString ('Rate', $rrrRate->Pull ('Id')->getValue ())
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
