<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// rate_group
//----------------------------------------------------------------------------//
/**
 * rate_group-------------------------------------------------//
/**
 * rate_group
 *
 * contains all ApplicationTemplate extended classes relating to Rate Group functionality
 *
 * contains all ApplicationTemplate extended classes relating to Rate Group functionality
 *
 * @file		rate_group.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateRateGroup
//----------------------------------------------------------------------------//
/**
 * AppTemplateRateGroup
 *
 * The AppTemplateGroup class
 *
 * The AppTemplateGroup class.  This incorporates all logic for all pages
 * relating to Rate Groups
 *
 *
 * @package	ui_app
 * @class	AppTemplateRateGroup
 * @extends	ApplicationTemplate
 */
class AppTemplateRateGroup extends ApplicationTemplate
{
	
	//------------------------------------------------------------------------//
	// _arrRateAllocationSummaryPerDestination
	//------------------------------------------------------------------------//
	/**
	 * _arrRateAllocationSummaryPerDestination
	 *
	 * For each time interval of each destination, this stores a list of Ids of the rates that are being applied
	 *
	 * For each time interval of each destination (of the RecordType of the RateGroup)
	 * this stores a list of Ids of the rates being applied
	 * Intervals are in 15 minute blocks after midnight.  For example the 3rd time Interval represents the time period
	 * of 00:30:00 to 00:44:59
	 *
	 * @type		array	[intDestinationCode OR 0][strWeekday ("Monday".."Sunday")][intInterval (0..95)][] = Rate Id of a rate that applies to these conditions
	 *
	 * @property
	 */
	private $_arrRateAllocationSummaryPerDestination = NULL;
	
	
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Rate Group webpage
	 * 
	 * Performs the logic for the Add Rate Group webpage
	 *		DBO()->CallingPage->AddRatePlan		Set to TRUE if this popup is being called from the "Add Rate Plan" page
	 *		DBO()->RateGroup->Id		If you want to edit an existing draft Rate Group
	 *		XOR
	 *		DBO()->BaseRateGroup->Id	If you want to add a new Rate Group, based on an existing one defined by this value
	 *		The "Add Rate Group" popup does not make use of the new Custom-Event Model, which is why it is concerned with
	 *		knowing what the calling page is.  If, in the future, this popup can be opened from numerous pages, then I would
	 *		recommend modifying it to use the new Custom-Event Model.
	 *		The user needs PERMISSION_RATE_MANAGEMENT and PERMISSION_ADMIN permissions to view this page
	 *		
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		// Handle form submittion
		if (SubmittedForm('RateGroup', 'Commit') || SubmittedForm('RateGroup', 'Save as Draft'))
		{
			$mixResult = $this->_ValidateRateGroup();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				// The RateGroup did not pass validation and an error message has been returned
				Ajax()->AddCommand("Alert", $mixResult);
				return TRUE;
			}
			elseif ($mixResult === FALSE)
			{
				// The RateGroup did not pass validation.  No error message was specified, so it is assumed appropriate action has already taken place
				return TRUE;
			}
			else
			{
				// The RateGroup passed validation. Save it.
				TransactionStart();
				
				$mixResult = $this->_SaveRateGroup();
				if ($mixResult !== TRUE && $mixResult !== FALSE)
				{
					// Saving the RateGroup failed, and an error message has been returned
					TransactionRollback();
					Ajax()->AddCommand("Alert", $mixResult);
					return TRUE;
				}
				elseif ($mixResult === FALSE)
				{
					// Saving the RateGroup failed, and no error message was specified, so it is assumed appropraite actions have already taken place
					TransactionRollback();
					return TRUE;
				}
				else
				{
					// Saving the RateGroup was successfull
					TransactionCommit();
					
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					if (SubmittedForm('RateGroup', 'Commit'))
					{
						Ajax()->AddCommand("Alert", "The Rate Group was successfully committed to the database");
					}
					else
					{
						Ajax()->AddCommand("Alert", "The Rate Group was successfully saved as a draft");
					}

					// Check if this popup was called from the "Add Rate Plan" page
					if (DBO()->CallingPage->AddRatePlan->Value)
					{
						// This popup was called from the "Add Rate Plan" page.  We have to update the appropriate combobox within the "Add Rate Plan" page
						$this->_UpdateAddRatePlanPage();
						return TRUE;
					}
					else
					{
						// Close the popup normally
						return TRUE;
					}
				}
			}
		}
		
		// Check if there has been a BaseRateGroup.Id specified, to base the new RateGroup on
		if (DBO()->BaseRateGroup->Id->Value)
		{
			// There is, so load it
			DBO()->RateGroup->Id = DBO()->BaseRateGroup->Id->Value;
			if (!DBO()->RateGroup->Load())
			{
				// Could not load the RateGroup
				Ajax()->AddCommand("Alert", "ERROR: The RateGroup to base the new rate group on, could not be found");
				return TRUE;
			}
			
			// Reset the Id of the RateGroup, because we are creating a new one, not editing an existing one
			DBO()->RateGroup->Id = 0;
		}
		elseif (DBO()->RateGroup->Id->Value)
		{
			// Display the existing rate group.  (It must be a draft)
			if (!DBO()->RateGroup->Load())
			{
				// Could not load the RateGroup
				Ajax()->AddCommand("Alert", "ERROR: The RateGroup could not be found");
				return TRUE;
			}
		}
		else
		{
			// We want to add a new RateGroup
			DBO()->RateGroup->Id = 0;
		}
		
		// Declare which PageTemplate to use
		$this->LoadPage('rate_group_add');

		return TRUE;
	}
	
	// Validates the Rate Group
	private function _ValidateRateGroup()
	{
		/* 
		 * Validation process:
		 *		Check that a Name and Description have been declared	(implemented)
		 *		Check that a service type has been declared				(implemented)
		 *		Check that a record type has been declared				(implemented)
		 *		Check that the Name is unique when compared with all other Rate Groups										(implemented)
		 *		For every distination associated with the context of the RecordType of the RateGroup:	
		 *			Check that every minute of every day of the week is accounted for by a Rate and there are no overlaps	(implemented)
		 */
	
		// Validate the fields
		if (DBO()->RateGroup->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		if (!DBO()->RateGroup->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: A Service Type must be selected";
		}
		if (!DBO()->RateGroup->RecordType->Value)
		{
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: A Record Type must be selected";
		}
		
		// Check that the name is unique
		if (DBO()->RateGroup->Id->Value == 0)
		{
			// The Rate Group name should not be in the database
			$strWhere = "Name=<Name>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other RateGroup
			$strWhere = "Name=<Name> AND Id != ". DBO()->RateGroup->Id->Value;
		}
		$selRateGroupName = new StatementSelect("RateGroup", "Id", $strWhere);
		if ($selRateGroupName->Execute(Array("Name" => DBO()->RateGroup->Name->Value)) > 0)
		{
			// The Name is already being used by another rate group
			DBO()->RateGroup->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: This name is already used by another RateGroup<br />Please choose a unique name";
		}
		
		
		// Make sure there are rates specified (This should be handled by the next validation step (checking that a rate covers all hours of all days))
		if (!DBO()->SelectedRates->ArrId->Value)
		{
			// No rates have been specified
			return "ERROR: No rates have been added to the rate group";
		}
		
		// Check that the selected Rates cover all hours of the week and don't overlap unless they are destination based
		$arrRateSummary = $this->_BuildRateSummary(DBO()->RateGroup->RecordType->Value, DBO()->SelectedRates->ArrId->Value);
		foreach ($arrRateSummary as $arrIntervals)
		{
			foreach ($arrIntervals as $intIntervalStatus)
			{
				switch ($intIntervalStatus)
				{
					case RATE_ALLOCATION_STATUS_OVER_ALLOCATED:
						return "ERROR: An over allocation of rates has been detected<br />Please review the rate summary";
						break;
					case RATE_ALLOCATION_STATUS_UNDER_ALLOCATED:
						// Under allocations are only allowed if the RateGroup is a Fleet Rate Group
						if (DBO()->RateGroup->Fleet->Value != TRUE)
						{
							return "ERROR: An under allocation of rates has been detected<br />Please review the rate summary";
						}
						break;
					default:
						break;
				}
			}
		}
		
		$strWhere = "Id IN (". implode(",", DBO()->SelectedRates->ArrId->Value) .")";
		DBL()->Rate->Where->SetString($strWhere);
		DBL()->Rate->SetColumns("Id");
		DBL()->Rate->Load();
		
		// All Validation is complete, the RateGroup is valid
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _SaveRateGroup
	//------------------------------------------------------------------------//
	/**
	 * _SaveRateGroup()
	 *
	 * Performs saving the records to the database, required for defining a RateGroup
	 * 
	 * Performs saving the records to the database, required for defining a RateGroup
	 * This will only work with the "Add Rate Group" popup webpage as it assumes specific DBObjects have been defined within DBO()
	 *
	 * @return		mix				returns TRUE if the new RateGroup saved successfully, else it returns
	 *								a specific error message detailing why the RateGroup could not be saved
	 * @method
	 *
	 */
	private function _SaveRateGroup()
	{
		/* 
		 * Saving process:
		 *		Set up values for properties of the RateGroup object that are not already defined										(DONE)
		 *		Save the record to the RateGroup table 																					(DONE)
		 *		Remove any records in the RateGroupRate table relating to this rate group 												(DONE)
		 *		For each rate belonging to this Rate Group:
		 *			add a record to the RateGroupRate table																				(DONE)
		 *		For each draft rate belonging to this Rate Group:
		 *			update the Archived property of the Rate in the Rate table so that it is now a committed Rate, not a draft rate		(DONE)
		 */
	
		// Define values for all fields that have not already been specified
		if (SubmittedForm('RateGroup', 'Save as Draft'))
		{
			// Flag it as a draft
			DBO()->RateGroup->Archived = RATE_STATUS_DRAFT;
		}
		else
		{
			DBO()->RateGroup->Archived = RATE_STATUS_ACTIVE;
		}
		
		// Declare which fields you want to set
		DBO()->RateGroup->SetColumns("Name, Description, RecordType, ServiceType, Fleet, Archived");
		
		// Add the RateGroup Record
		if (!DBO()->RateGroup->Save())
		{
			return "ERROR: Saving the RateGroup record to the database failed, unexpectedly.<br />The Rate Group has not been saved";
		}
		
		// Remove all records from the RateGroupRate table where RateGroup == DBO()->RateGroup->Id->Value
		$delRateGroupRate = new Query();
		if ($delRateGroupRate->Execute("DELETE FROM RateGroupRate WHERE RateGroup = " . DBO()->RateGroup->Id->Value) === FALSE)
		{
			return "ERROR: Deleting old records from the RateGroupRate table failed, unexpectedly.<br />The Rate Group has not been saved";
		}
				
		// Add a record to the RateGroupRate table for each rate associated with this rategroup
		// StatementInsert is being used rather than a DBObject, as it is quicker, and this could require about 1000 records being added
		$insRateGroupRate = new StatementInsert("RateGroupRate");
		$arrInsertValues = Array("RateGroup" => DBO()->RateGroup->Id->Value);
		foreach (DBL()->Rate as $dboRate)
		{
			$arrInsertValues['Rate'] = $dboRate->Id->Value;
			if (!$insRateGroupRate->Execute($arrInsertValues))
			{
				// Inserting one of the records failed
				return "ERROR: Saving a record to the RateGroupRate table of the database failed, unexpectedly.<br />The Rate Group has not been saved";
			}
		}
		
		// If the RateGroup is being committed to the database, as opposed to being saved, make sure all its associated rates are also committed
		if (DBO()->RateGroup->Archived->Value == RATE_STATUS_ACTIVE)
		{
			$arrUpdate = Array("Archived" => RATE_STATUS_ACTIVE);
			$updRates = new StatementUpdate("Rate", "Archived = ". RATE_STATUS_DRAFT ." AND Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = <RateGroup>)", $arrUpdate);
			
			if ($updRates->Execute($arrUpdate, Array("RateGroup" => DBO()->RateGroup->Id->Value)) === FALSE)
			{
				// Updating the Rate table failed
				return "ERROR: A problem occurred committing draft rates used by this Rate Group. <br />The Rate Group has not been saved";
			}
		}
		
		// The Rate Group has been saved successfully
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// PreviewRateSummary
	//------------------------------------------------------------------------//
	/**
	 * PreviewRateSummary()
	 *
	 * Displays the Rate Summary, for a RateGroup, in a popup
	 * 
	 * Displays the Rate Summary, for a RateGroup, in a popup
	 *		DBO()->RecordType->Id			RecordType of the RateGroup
	 *		DBO()->RateGroup->Fleet			TRUE if the RateGroup is a Fleet RateGroup, else FALSE
	 *		DBO()->SelectedRates->ArrId		Indexed array of Rate Ids which the RateGroup comprises of
	 *
	 * @return		void
	 *
	 * @method
	 *
	 */
	function PreviewRateSummary()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		// Build the Problem Report of the rate summary
		DBO()->RateSummary->ProblemReport = $this->_BuildRateSummaryProblemReport(DBO()->RecordType->Id->Value, DBO()->SelectedRates->ArrId->Value, DBO()->RateGroup->Fleet->Value);
		
		// Build the RateSummary
		$arrRateSummary = $this->_BuildRateSummary(DBO()->RecordType->Id->Value, DBO()->SelectedRates->ArrId->Value);
		
		// The rate summary has to be wrapped in a DBObject so that it can be accessible from the Html Template that displays the summary
		DBO()->RateSummary->ArrSummary = $arrRateSummary;
		
		$this->LoadPage('rate_summary');
	}

	//------------------------------------------------------------------------//
	// _BuildRateSummaryProblemReport
	//------------------------------------------------------------------------//
	/**
	 * _BuildRateSummaryProblemReport()
	 *
	 * Builds a Report outlining the various problems with the RateGroup
	 * 
	 * Builds a Report outlining the various problems with the RateGroup
	 *
	 * @param		integer		$intRecordType	The RecordType of the RateGroup
	 * @param		array		$arrRateIds		indexed array of Ids of the Rates that belong to this RateGroup
	 * @param		bool		$bolIsFleet		TRUE if the RateGroup is a Fleet RateGroup, else FALSE
	 *
	 * @return		string		Report outlining the various problems with the RateGroup.
	 *
	 * @method
	 *
	 */
	private function _BuildRateSummaryProblemReport($intRecordType, $arrRateIds, $bolIsFleet)
	{
		// I think for the moment, I should make this summary as brief as possible.  For each Destination that isn't "just-right"
		// for the entire week, list whether it is over allocated, under, or both, and list which Rates are associated with it, if there
		// are any
		
		// Check if $this->_arrRateAllocationSummaryPerDestination has not already been built
		if ($this->_arrRateAllocationSummaryPerDestination === NULL)
		{
			$this->_BuildRateAllocationSummaryPerDestination($intRecordType, $arrRateIds);
		}
		
		// Build the list of required destinations for the RecordType
		$arrDestinationSummary = Array();
		// We need to retrieve the description of each destination, if the rates are subject to destinations
		$selRecordType = new StatementSelect("RecordType", "Id, Context", "Id = <Id>");
		$selRecordType->Execute(Array("Id" => $intRecordType));
		$arrRecordType = $selRecordType->Fetch();
		
		if ($arrRecordType['Context'] != 0)
		{
			// The RecordType is destination based, retrieve the descriptions of each destination
			$selDestinations = new StatementSelect("Destination", "Description, Code, Context", "Context = <Context>");
			$selDestinations->Execute(Array("Context" => $arrRecordType['Context']));
			$arrDestinations = $selDestinations->FetchAll();
			
			foreach ($arrDestinations as $arrDestination)
			{
				$arrDestinationSummary[$arrDestination['Code']]['Description'] = $arrDestination['Description'];
			}
		}
		else
		{
			// The RecordType does not make use of Destinations
			$arrDestinationSummary[0]['Description'] = NULL;
		}
		
		// Build a list of names of the various Rates that are being applied to this RateGroup
		$arrRateNames = Array();
		$strWhere = "Id IN (". implode(",", $arrRateIds) .")";
		$selRates = new StatementSelect("Rate", "Id, Name", $strWhere);
		$selRates->Execute();
		$arrRates = $selRates->FetchAll();
		foreach ($arrRates as $arrRate)
		{
			$arrRateNames[$arrRate['Id']] = $arrRate['Name'];
		}
		
		// Traverse the RateAllocationSummaryPerDestination and find any occurrences of there being over allocations or under allocations
		foreach ($this->_arrRateAllocationSummaryPerDestination as $intDestination=>$arrWeekdayRateAllocations)
		{
			$arrDestinationSummary[$intDestination]['Rates']				= Array();
			$arrDestinationSummary[$intDestination]['HasOverAllocation']	= FALSE;
			$arrDestinationSummary[$intDestination]['HasUnderAllocation']	= FALSE;
			
			foreach ($arrWeekdayRateAllocations as $strWeekday=>$arrIntervalRateAllocations)
			{
				foreach ($arrIntervalRateAllocations as $intInterval=>$arrRateIds)
				{
					$intRateCount = count($arrRateIds);
					if ($intRateCount > 1)
					{
						// The Destination has an over allocation of rates during this time interval
						// Flag it as having an over allocation
						$arrDestinationSummary[$intDestination]['HasOverAllocation'] = TRUE;
						
						// Add the rates to the list of rates associated with this destination
						foreach ($arrRateIds as $intRateId)
						{
							$arrDestinationSummary[$intDestination]['Rates'][$intRateId] = $arrRateNames[$intRateId];
						}
					}
					elseif ($intRateCount == 1)
					{
						// The Destination has the correct number of rates (1 rate) allocated to this time interval
						// Add the rate to the list of rates associated with this destination
						$intRateId = $arrRateIds[0];
						$arrDestinationSummary[$intDestination]['Rates'][$intRateId] = $arrRateNames[$intRateId];
					}
					else
					{
						// The Destination has an under-allocation of rates during this time interval
						// Flag it as having an under-allocation
						$arrDestinationSummary[$intDestination]['HasUnderAllocation'] = TRUE;
					}
				}
			}
		}
		
		// Now we have a list of rates applied to each destination, (along with their names) and we can tell if there
		// has been an under-allocation or over-allocation (for a destination) during the week
		$strRateGroupSummary = "";
		$bolProblemDetected = FALSE;
		foreach ($arrDestinationSummary as $intDestination=>$arrDestination)
		{
			$strDestinationSummary = "";
			$strOverAllocation = "";
			$strUnderAllocation = "";

			if ($arrDestination['HasOverAllocation'])
			{
				$strOverAllocation = "\t\tOver Allocation at some point during the week\n";
			}

			if (($arrDestination['HasUnderAllocation']) && (!$bolIsFleet))
			{
				$strUnderAllocation = "\t\tUnder Allocation at some point during the week\n";
			}

			if (($arrDestination['HasOverAllocation']) || (($arrDestination['HasUnderAllocation']) && (!$bolIsFleet)))
			{
				// The Destination has either an over-allocation of rates, or (an under-allocation and is not being applied to a fleet RateGroup)
				// Fleet RateGroups are allowed under-allocations
				$bolProblemDetected = TRUE;
				
				if ($intDestination == 0)
				{
					// RecordType does not have destinations 
					$strDestinationSummary = "\tThe Rate Group has:\n" . $strUnderAllocation . $strOverAllocation;
				}
				else
				{
					// RecordType has multiple destinations
					$strDestinationSummary = "\tDestination '{$arrDestination['Description']}' has:\n" . $strOverAllocation . $strUnderAllocation;
					
					// List the Rates belonging to the rate group, which apply to this destination
					if (count($arrDestination['Rates']) > 0)
					{
						$strDestinationSummary .= "\t\tRates associated with this destination are:\n";
						foreach ($arrDestination['Rates'] as $strRateName)
						{
							$strDestinationSummary .= "\t\t\t$strRateName\n";
						}
					}
					else
					{
						// There are currently no rates associated with this Destination
						$strDestinationSummary .= "\t\tThere are currently no Rates in the Rate Group associated with this destination\n";
					}
				}

			}
			
			// Add the Destination Summary to the TotalSummary
			$strRateGroupSummary .= $strDestinationSummary;
		}
		
		// Check if there were any problems detected
		if ($bolProblemDetected)
		{
			$strRateGroupSummary = "The following problems have been detected:\n" . $strRateGroupSummary;
		}
		else
		{
			$strRateGroupSummary = "No problems have been detected.\n";
		}
		
		if ($bolIsFleet)
		{
			$strRateGroupSummary .= "Since this is a fleet Rate Group, under-allocations are allowed.\n";
		}
			
		return $strRateGroupSummary;
	}


	//------------------------------------------------------------------------//
	// _BuildRateAllocationSummaryPerDestination
	//------------------------------------------------------------------------//
	/**
	 * _BuildRateAllocationSummaryPerDestination()
	 *
	 * Builds a Rate Allocation Summary, for each destination of the RecordType, as a multi-dimensional array
	 * 
	 * Builds a Rate Allocation Summary, for each destination of the RecordType, as a multi-dimensional array
	 * This array is stored in the private member varibale $this->_arrRateAllocationSummaryPerDestination
	 * And is of the form [Destination][Weekday][Interval][] = Id of Rate applied
	 * This is being stored as a private member variable as several functions utilise it, and it makes sense to
	 * cache it as opposed to building it on the fly each time.
	 *
	 * @param		integer		$intRecordType	Id of the RecordType of the RateGroup
	 * @param		array		$arrRateIds		indexed array of Ids of the Rates that belong to this RateGroup
	 *
	 * @return		void
	 *
	 * @method
	 *
	 */
	private function _BuildRateAllocationSummaryPerDestination($intRecordType, $arrRateIds)
	{
		// Build the structure which will store what times of the week are covered
		$arrWeekdays = Array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		$arrIntervals = Array();
		$arrAllocations = Array();
		for ($i=0; $i < 96; $i++)
		{
			// This array will store the id of each Rate that applies to the interval for the given destination
			$arrIntervals[$i] = Array();
		}
		foreach ($arrWeekdays as $strWeekday)
		{
			$arrAllocations[$strWeekday] = $arrIntervals;
		}
		
		// Initialise the cached Summary
		$this->_arrRateAllocationSummaryPerDestination = Array();
		
		//The key of $arrIntervals represents the 15minute interval after midnight.  $arrIntervals[interval] is an array storing
		//the Id of each Rate that is applied to that interval, for a given day and destination.
		//For example if $arrIntervals[2] = {3, 6, 19} then this means the third time interval
		//(00:30:00 to 00:44:59) has Rates 3, 6 and 19 applied to it, for the given weekday and destination code.  Properly allocated
		//Rates will result in only 1 rate being applied for each interval for each day for each destination
		
		// We need to retrieve a list of all destinations, if the rates are subject to destinations
		$selRecordType = new StatementSelect("RecordType", "Id, Context", "Id = <Id>");
		$selRecordType->Execute(Array("Id" => $intRecordType));
		$arrRecordType = $selRecordType->Fetch();
		
		if ($arrRecordType['Context'] != 0)
		{
			// The RateGroups of this RecordType must have a rate covering all times of the week for all destinations
			
			// Retrieve a list of destinations
			$selDestinations = new StatementSelect("Destination", "Code, Context", "Context = <Context>");
			$selDestinations->Execute(Array("Context" => $arrRecordType['Context']));
			$arrDestinations = $selDestinations->FetchAll();
			
			foreach ($arrDestinations as $arrDestination)
			{
				$this->_arrRateAllocationSummaryPerDestination[$arrDestination['Code']] = $arrAllocations;
			}
		}
		else
		{
			// The RecordType does not make use of Destinations
			$this->_arrRateAllocationSummaryPerDestination[0] = $arrAllocations;
		}
		
		// Retrieve the rates selected
		$strWhere = "Id IN (". implode(",", $arrRateIds) .")";
		$selRates = new StatementSelect("Rate", "Id, StartTime, EndTime, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday, Destination", $strWhere);
		$selRates->Execute();
		$arrRates = $selRates->FetchAll();
		
		// Loop through each rate, and mark each interval that it covers
		$intMidnight = mktime(0, 0, 0);
		foreach ($arrRates as $arrRate)
		{
			// Convert the start time and end time into seconds relative to midnight
			$intStartTime = strtotime($arrRate['StartTime']) - $intMidnight;
			$intEndTime = strtotime($arrRate['EndTime']) - $intMidnight;
			
			foreach ($arrWeekdays as $strWeekday)
			{
				if ($arrRate[$strWeekday] == 1)
				{
					// The rate is applied to this day.  Check which intervals of the day it applies to
					// There are 96 15 minute intervals in a day
					for ($intInterval=0; $intInterval < 96; $intInterval++)
					{
						// Check if the rate applies to this interval
						// $intStartOfInterval	= interval * 15 minutes * 60 seconds
						// $intEndOfInterval	= $intStartOfInterval + ((15 minutes * 60 seconds) - 1 second)

						$intStartOfInterval		= ($intInterval * 15 * 60);
						$intEndOfInterval		= $intStartOfInterval + 899;
						
						$bolCovered = ($intStartTime <= $intStartOfInterval && $intEndTime >= $intEndOfInterval);
						
						if ($bolCovered)
						{
							// Append the Rate's Id to the list of Rates that are applied to this interval, for this destination
							$this->_arrRateAllocationSummaryPerDestination[$arrRate['Destination']][$strWeekday][$intInterval][] = $arrRate['Id'];
						}
					}
				}
			}
		}
		
		// We now have a structure which stores the id of each rate applied to each individual interval of each individual day, 
		// for each individual destination associated with the RecordType
		// $this->_arrRateAllocationSummaryPerDestination[Destination][Weekday][Interval][] = Id of Rate applied
		// This can be used to work out which Destinations, or times of the week are over/under allocated, and if so, which
		// rates are causing the confliction
	}


	//------------------------------------------------------------------------//
	// _BuildRateSummary
	//------------------------------------------------------------------------//
	/**
	 * _BuildRateSummary()
	 *
	 * Builds the Rate Summary as a multi-dimensional array
	 * 
	 * Builds the Rate Summary as a multi-dimensional array
	 *
	 * @param	integer		$intRecordType	Id of the RecordType of the RateGroup
	 * @param	array		$arrRateIds		indexed array of Ids of the Rates that belong to this RateGroup
	 *
	 * @return		array		$arrRateSummary[strWeekday][intInterval] = intIntervalStatus
	 *							WHERE intIntervalStatus = RATE_ALLOCATION_STATUS_ALLOCATED | RATE_ALLOCATION_STATUS_UNDER_ALLOCATED | RATE_ALLOCATION_STATUS_OVER_ALLOCATED
	 *
	 * @method
	 *
	 */
	private function _BuildRateSummary($intRecordType, $arrRateIds)
	{
		// Check if $this->_arrRateAllocationSummaryPerDestination has not already been built
		if ($this->_arrRateAllocationSummaryPerDestination === NULL)
		{
			$this->_BuildRateAllocationSummaryPerDestination($intRecordType, $arrRateIds);
		}

		// We now have a structure which stores the Id of each Rate which is applied to each individual interval of each day, 
		// for each destination associated with the RecordType
		// $this->_arrRateAllocationSummaryPerDestination[Destination][Weekday][Interval] = Indexed array of IDs of applied rates

		// For each interval, add the number of rates covering it together (across destinations).  
		// If the number of rates covering any given interval is equal to the number of destinations then it is correctly allocated.
		// If the number of rates covering any given interval is less than the number of destinations then it is under-allocated.
		// If the number of rates covering any given interval is greater than the number of destinations then it is over-allocated.

		// This will store the summary of the week
		$arrRateSummary = Array();

		foreach ($this->_arrRateAllocationSummaryPerDestination as $arrAllocations)
		{
			foreach ($arrAllocations as $strWeekday=>$arrIntervals)
			{
				foreach ($arrIntervals as $intInterval=>$arrRatesApplied)
				{
					// Add the number of Applied Rates to the running total number of rates, for the interval
					$arrRateSummary[$strWeekday][$intInterval] += count($arrRatesApplied);
				}
			}
		}
		
		// Store how many rates should be applied to each interval
		$intNumOfDestinations = count($this->_arrRateAllocationSummaryPerDestination);
		
		foreach ($arrRateSummary as $strWeekday=>$arrIntervals)
		{
			foreach ($arrIntervals as $intInterval=>$intRateCount)
			{
				// Check that the correct number of rates have been applied to the interval
				if ($intRateCount == $intNumOfDestinations)
				{
					// The interval has been properly allocated
					$arrRateSummary[$strWeekday][$intInterval] = RATE_ALLOCATION_STATUS_ALLOCATED;
				}
				elseif ($intRateCount < $intNumOfDestinations)
				{
					// The interval has been under-allocated
					$arrRateSummary[$strWeekday][$intInterval] = RATE_ALLOCATION_STATUS_UNDER_ALLOCATED;
				}
				else
				{
					// The interval has been over-allocated
					$arrRateSummary[$strWeekday][$intInterval] = RATE_ALLOCATION_STATUS_OVER_ALLOCATED;
				}
			}
		}
		
		return $arrRateSummary;
	}

	//------------------------------------------------------------------------//
	// SetRateSelectorControl
	//------------------------------------------------------------------------//
	/**
	 * SetRateSelectorControl()
	 *
	 * Draws the Rate Selector Control used in the "Add Rate Group" form
	 * 
	 * Draws the Rate Selector Control used in the "Add Rate Group" form
	 * This will only work with the "Add Rate Group" popup webpage as it assumes specific DBObjects have been defined within DBO()
	 * This function expects DBO()->RecordType->Id to be set, as it only displays the Rates for a specified RecordType.
	 * It also expects DBO()->RecordType->IsFleet to be set to either TRUE or FALSE
	 * If (DBO()->RateGroup->Id is set XOR DBO()->BaseRateGroup->Id is set) then it will flag which Rates are currently used by the RateGroup
	 * 
	 *
	 * @return		bool			TRUE
	 * @method
	 *
	 */
	function SetRateSelectorControl()
	{
		$intFleet = (DBO()->RecordType->IsFleet->Value) ? 1 : 0;
		$selRates = new StatementSelect("Rate", "Id, Name, Description, Fleet, Archived", "RecordType=<RecordType> AND Fleet=<Fleet> AND Archived != 1", "Name", NULL);
		$selRates->Execute(Array("RecordType" => DBO()->RecordType->Id->Value, "Fleet" => $intFleet));
		$arrRecords = $selRates->FetchAll();

		// If a RateGroup.Id xor BaseRateGroup.Id has been specified then we want to mark which of these rates belong to it
		if (DBO()->RateGroup->Id->Value)
		{
			$intRateGroupId = DBO()->RateGroup->Id->Value;
		}
		elseif (DBO()->BaseRateGroup->Id->Value)
		{
			$intRateGroupId = DBO()->BaseRateGroup->Id->Value;
		}
		
		if (IsSet($intRateGroupId))
		{
			$selRateGroupRates = new StatementSelect("RateGroupRate", "Id, RateGroup, Rate", "RateGroup=<RateGroup>", NULL, NULL);
			$selRateGroupRates->Execute(Array("RateGroup" => $intRateGroupId));
			$arrRateGroupRates = $selRateGroupRates->FetchAll();
		}

		$arrRates = Array();
		$arrRate = Array();
		foreach ($arrRecords as $arrRecord)
		{
			$arrRate['Id']			= $arrRecord['Id'];
			$arrRate['Name']		= $arrRecord['Name'];
			$arrRate['Description']	= $arrRecord['Description'];
			$arrRate['Draft']		= ($arrRecord['Archived'] == RATE_STATUS_DRAFT);
			$arrRate['Fleet']		= ($arrRecord['Fleet'] == 1);
			
			// Check if this Rate currently belongs to the specified RateGroup
			$arrRate['Selected']	= FALSE;
			if (IsSet($intRateGroupId))
			{
				foreach ($arrRateGroupRates as $arrRateGroupRate)
				{
					if ($arrRateGroupRate['Rate'] == $arrRate['Id'])
					{
						// This Rate belongs to the RateGroup
						$arrRate['Selected'] = TRUE;
						break;
					}
				}
			}
			
			// Add the rate to the list of rates
			$arrRates[] = $arrRate;
		}
		
		// Wrap the list of rates in a property of a DBObject so that it is accessible by HtmlTemplates
		DBO()->Rates->ArrRates = $arrRates;
		
		// Render the RateSelectorControl HtmlTemplate
		Ajax()->RenderHtmlTemplate("RateGroupAdd", HTML_CONTEXT_RATES, "RateSelectorControlDiv");
		return TRUE;
	}


	//------------------------------------------------------------------------//
	// _UpdateAddRatePlanPage
	//------------------------------------------------------------------------//
	/**
	 * _UpdateAddRatePlanPage()
	 *
	 * Executes javascript associated with the "Add Rate Plan" page, in order to update it, after a Rate Group has been saved
	 * 
	 * Executes javascript associated with the "Add Rate Plan" page, in order to update it, after a Rate Group has been saved
	 * It is assumed DBO()->RateGroup contains a valid RateGroup
	 *
	 * @return		void
	 * @method
	 *
	 */
	private function _UpdateAddRatePlanPage()
	{
		$arrRateGroup['Id'] = DBO()->RateGroup->Id->Value;
		$arrRateGroup['Name'] = DBO()->RateGroup->Name->Value;
		$arrRateGroup['RecordType'] = DBO()->RateGroup->RecordType->Value;
		$arrRateGroup['Fleet'] = DBO()->RateGroup->Fleet->Value ? 1 : 0;
		$arrRateGroup['Draft'] = (DBO()->RateGroup->Archived->Value == RATE_STATUS_DRAFT) ? 1 : 0;

		$objRateGroup = Json()->encode($arrRateGroup);
		
		$strJavascript = "Vixen.RatePlanAdd.AddRateGroupPopupOnClose($objRateGroup);";
		Ajax()->AddCommand("ExecuteJavascript", $strJavascript);
	}
	
	//------------------------------------------------------------------------//
	// Override
	//------------------------------------------------------------------------//
	/**
	 * Override()
	 *
	 * Performs the logic for the "Override Rate Group" popup
	 * 
	 * Performs the logic for the "Override Rate Group" popup
	 *		This assumes the following data is passed:
	 *			DBO()->Service->Id				Id of the service that the Override will take place on
	 *			DBO()->RecordType->Id		Id of the RecordType which is being overridden
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Override()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Load the Service Record
		DBO()->Service->Load();
		
		// Load the Account Record
		DBO()->Account->Id = DBO()->Service->Account->Value;
		DBO()->Account->Load();
		
		// Load the RecordType Record
		DBO()->RecordType->Load();
		
		// Load the current Plan (if there is one)
		DBO()->RatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		if (DBO()->RatePlan->Id->Value)
		{
			DBO()->RatePlan->Load();
		}
		
		// Retrieve all RateGroups matching the RecordType
		DBL()->RateGroup->RecordType = DBO()->RecordType->Id->Value;
		DBL()->RateGroup->OrderBy("Name");
		DBL()->RateGroup->Load();
	
		// Handle form submittion
		if (SubmittedForm('RateGroupOverride', 'Apply Changes'))
		{
			$strCurrentDate = GetCurrentDateForMySQL();
			$strCurrentDateAndTime = GetCurrentDateAndTimeForMySQL();
			$strChangesNote = "";
			$strChangesNote .= "RecordType: " . DBO()->RecordType->Name->Value . "\n";
			$strChangesNote .= "RateGroup: " . DBO()->RateGroup->Name->Value . "\n";
			
			// Convert Current date into seconds
			$intCurrentDate = strtotime($strCurrentDate);
			$intStartDate = strtotime(ConvertUserDateToMySqlDate(DBO()->ServiceRateGroup->StartDate->Value));
			$intEndDate  = strtotime(ConvertUserDateToMySqlDate(DBO()->ServiceRateGroup->EndDate->Value));	

			// If the immediateStart checkbox isnt checked
			if (DBO()->RateGroup->ImmediateStart->Value != 1)
			{
				// If user date entered is valid & convert into seconds
				if ($intStartDate)
				{
					// If User date is in the past
					if ($intStartDate < $intCurrentDate)
					{
						DBO()->ServiceRateGroup->StartDate->SetToInvalid();
						Ajax()->AddCommand("Alert", "ERROR: The override cannot start in the past");
						Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;							
					}
					// If User date equals the current date
					if ($intStartDate == $intCurrentDate)
					{
						DBO()->ServiceRateGroup->StartDate->SetToInvalid();
						Ajax()->AddCommand("Alert", "ERROR: The override cannot start in the past.  Please specify an immediate start if you want the override to start today");
						Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;
					}
				}
				else
				{
					// Start time is invalid and the end time maybe as well so don't return TRUE in this conditional block
					// Else user date entered is invalid
					DBO()->ServiceRateGroup->StartDate->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: The start date is not in the correct format of dd/mm/yyyy");
					Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
			}
			// If the indefinateEnd checkbox isnt checked
			if (DBO()->RateGroup->IndefinateEnd->Value != 1)
			{
				// Validate the user supplied End Date (if there is one)
				if ($intEndDate)
				{	
					if ($intEndDate < $intCurrentDate)
					{
						// The End Date is in the past
						DBO()->ServiceRateGroup->EndDate->SetToInvalid();
						Ajax()->AddCommand("Alert", "ERROR: Can not have a date ending in the past");
						Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;							
					}
					elseif (DBO()->RateGroup->ImmediateStart->Value == 1 && $intEndDate < $intStartDate)
					{
						// The End Date is earlier than the start date
						DBO()->ServiceRateGroup->EndDate->SetToInvalid();
						Ajax()->AddCommand("Alert", "ERROR: Can not have a date ending before the start date");
						Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;							
					}
				}
				else
				{
					// Else user date entered is invalid
					DBO()->ServiceRateGroup->EndDate->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: The Start date is not in the correct format of dd/mm/yyyy");
					Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
			}

			// Work out the StartDatetime
			if (DBO()->RateGroup->ImmediateStart->Value == 1)
			{
				// Use the current Date and Time as the start time
				$strStartTime = $strCurrentDateAndTime;
			}
			else
			{
				// Set the StartDatetime to the Date supplied by the user (midnight)
				$strStartTime = date("Y-m-d", $intStartDate) . " 00:00:00";
			}
			$strChangesNote .= "Start time: $strStartTime\n";				
			
			// Work out the EndDatetime
			if (DBO()->RateGroup->IndefinateEnd->Value == 1)
			{
				// Set the EndDatetime to indefinate
				$strEndTime = END_OF_TIME;
				$strChangesNote .= "End time: Indefinate\n";				
			}
			else
			{
				// Set the EndDatetime to the Date supplied by the user (11:59:59 pm)
				$strEndTime = date("Y-m-d", $intEndDate) . " 23:59:59";
				$strChangesNote .= "End time: $strEndTime\n";				
			}
		
			DBO()->ServiceRateGroup->Service		= DBO()->Service->Id->Value;
			DBO()->ServiceRateGroup->RateGroup		= DBO()->ServiceRateGroup->Selected->Value;
			DBO()->ServiceRateGroup->CreatedBy		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRateGroup->CreatedOn		= $strCurrentDateAndTime;
			DBO()->ServiceRateGroup->StartDatetime	= $strStartTime;
			DBO()->ServiceRateGroup->EndDatetime	= $strEndTime;
			DBO()->ServiceRateGroup->Active			= 1;

			DBO()->Service->SetColumns("Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active");

			// Save the ServiceRateGroup record
			if (!DBO()->ServiceRateGroup->Save())
			{
				// inserting record into the database failed unexpectedly
				Ajax()->AddCommand("Alert", "ERROR: Saving the overriding RateGroup failed unexpectedly");
				return TRUE;
			}
			
			// Create System note
			$strChangesNote = "An overriding RateGroup has been declared.  Its details are as follows:\n$strChangesNote";
			SaveSystemNote($strChangesNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value, NULL, DBO()->Service->Id->Value);
			
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The overriding RateGroup was successfully defined");

			// Build event object
			// The contents of this object should be declared in the doc block of this method
			$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
			Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
			
			// Fire the OnNewNote Event
			Ajax()->FireOnNewNoteEvent(DBO()->Service->Account->Value, DBO()->Service->Id->Value);
			
			return TRUE;
		}

		// Declare which PageTemplate to use
		$this->LoadPage('rate_group_override');
		return TRUE;
	}
}
