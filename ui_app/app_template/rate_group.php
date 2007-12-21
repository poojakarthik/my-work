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
	// _arrDestinationRates
	//------------------------------------------------------------------------//
	/**
	 * _arrDestinationRates
	 *
	 * This stores a list of Rates for each destination of the RateGroup.  It stores the times of the week that the rate applies to the destination
	 *
	 * This stores a list of Rates for each destination of the RateGroup.  It stores the times of the week that the rate applies to the destination
	 *
	 * @type		array	[intDestinationCode OR 0][] = $arrRate
	 *						where $arrRate	[Id] = Id of the Rate
	 *										[StartTime] = The time of day that the rate starts being applied (in seconds past midnight)
	 *										[EndTime]	= The time of day that the rate ends being applied (in seconds past midnight)
	 *										[FirstInterval]	= The interval of the day which this rate is first applied 
	 *															(00:00:00 - 00:14:59 = interval 1, 00:15:00 - 00:29:59 = interval 2)
	 *															There are 96 intervals in a day
	 *										[LastInterval]	= The interval of the dat which this rate is last applied
	 *										[Monday] - [Sunday]	= Booleans. TRUE if the Rate is applied on this day
	 *
	 * @property
	 */
	private $_DestinationRates = NULL;
	
	//------------------------------------------------------------------------//
	// _arrDestinationRateSummary
	//------------------------------------------------------------------------//
	/**
	 * _arrDestinationRateSummary
	 *
	 * This declares whether there are over allocations or under allocations in the RateGroup
	 *
	 * This declares whether there are over allocations or under allocations in the RateGroup
	 *
	 * @type		array	('OverAllocated' => boolean, 'UnderAllocated' => boolean, 'Destinations' => Array)	
	 * 	where					['OverAllocated']	= boolean, TRUE if at least 1 of the Destinations is Over Allocated at some point during the week
	 * 							['UnderAllocated']	= boolean, TRUE if at least 1 of the Destinations is Under Allocated at some point during the week
	 *							['Destinations'][DestinationCode] = $arrDestinationSummary
	 *
	 *	where	arrDestinationSummary	['OverAllocated']		= boolean, TRUE if the Destination is Over Allocated at some point during the week
	 *									['UnderAllocated']		= boolean, TRUE if the Destination is Under Allocated at some point during the week
	 *									[Weekday]['OverAllocations'][]	= $arrIntervalRange (defining the range of intervals affected by an Over Allocation for that day)
	 *									[Weekday]['UnderAllocations'][]	= $arrIntervalRange (defining the range of intervals affected by an Under Allocation for that day)
	 *
	 *	where	arrIntervalRange		['Start']	= The first interval of the day in which the Over Allocation or Under Allocation applies
	 *									['End']		= The last interval of the day in which the Over Allocation or Under Allocation applies
	 *
	 * @property
	 */
	private $_arrDestinationRateSummary = NULL;
	
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
		 *		Check that the Name is unique when compared with all other Rate Groups of the declared RecordType			(implemented)
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
			$strWhere = "Name=<Name> AND RecordType=<RecordType>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other RateGroup
			$strWhere = "Name=<Name> AND RecordType=<RecordType> AND Id != ". DBO()->RateGroup->Id->Value;
		}
		$selRateGroupName = new StatementSelect("RateGroup", "Id", $strWhere);
		if ($selRateGroupName->Execute(Array("Name" => DBO()->RateGroup->Name->Value, "RecordType" => DBO()->RateGroup->RecordType->Value)) > 0)
		{
			// The Name is already being used by another rate group of this RecordType
			DBO()->RateGroup->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: This name is already used by another RateGroup of this Record Type<br />Please choose a unique name";
		}
		
		
		// Make sure there are rates specified (This should be handled by the next validation step (checking that a rate covers all hours of all days))
		if (!DBO()->SelectedRates->ArrId->Value)
		{
			// No rates have been specified
			return "ERROR: No rates have been added to the rate group";
		}
		
		// Check that the selected Rates cover all hours of the week and don't overlap unless they are destination based
		$this->_BuildRateSummary(DBO()->RateGroup->RecordType->Value, DBO()->SelectedRates->ArrId->Value);
		if ($this->_arrDestinationRateSummary['OverAllocated'])
		{
			return "ERROR: An over allocation of rates has been detected<br />Please review the rate summary";
		}
		elseif ($this->_arrDestinationRateSummary['UnderAllocated'])
		{
			// Under allocations are only allowed if the RateGroup is a Fleet Rate Group
			if (DBO()->RateGroup->Fleet->Value != TRUE)
			{
				return "ERROR: An under allocation of rates has been detected<br />Please review the rate summary";
			}
		}

		// Load the Rates belonging to the rategroup
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
		$arrRateSummary = $this->_BuildGraphicalRateSummary(DBO()->RecordType->Id->Value, DBO()->SelectedRates->ArrId->Value);
		
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
		
		$intProblemCount = 0;
		$intMaxProblems = 20;
		
		// Check if $this->_arrDestinationRateSummary has not already been built
		if ($this->_arrDestinationRateSummary === NULL)
		{
			$this->_BuildRateSummary($intRecordType, $arrRateIds);
		}
		
		// Build the list of required destinations for the RecordType
		$arrDestinationNames = Array();
		$selDestinations = new StatementSelect("Destination", "Description, Code, Context", "Context IN (SELECT Context FROM RecordType WHERE Id = <RecordType>)");
		$intNumOfDestinations = $selDestinations->Execute(Array("RecordType" => $intRecordType));
		
		if ($intNumOfDestinations > 0)
		{
			// The RecordType is destination based
			$arrDestinations = $selDestinations->FetchAll();
			foreach ($arrDestinations as $arrDestination)
			{
				$arrDestinationNames[$arrDestination['Code']] = $arrDestination['Description'];
			}
		}
		else
		{
			// The RecordType does not make use of Destinations
			$arrDestinationNames[0]['Description'] = NULL;
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
		
		$strRateGroupSummary = "";
		$bolProblemDetected = FALSE;
		foreach ($this->_arrDestinationRateSummary['Destinations'] as $intDestination=>$arrDestination)
		{
			$strDestinationSummary = "";
			$strOverAllocation = "";
			$strUnderAllocation = "";

			if ($arrDestination['OverAllocated'])
			{
				$strOverAllocation = "\t\tOver Allocation at some point during the week\n";

				// Increment the number of errors encountered so far
				$intProblemCount += 1;
			}

			if (($arrDestination['UnderAllocated']) && (!$bolIsFleet))
			{
				$strUnderAllocation = "\t\tUnder Allocation at some point during the week\n";

				// Increment the number of errors encountered so far
				$intProblemCount += 1;
			}

			if (($arrDestination['OverAllocated']) || (($arrDestination['UnderAllocated']) && (!$bolIsFleet)))
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
					$strDestinationSummary = "\tDestination '{$arrDestinationNames[$intDestination]}' has:\n" . $strOverAllocation . $strUnderAllocation;
					
					// List the Rates belonging to the rate group, which apply to this destination
					if (count($this->_arrDestinationRates[$intDestination]) > 0)
					{
						$strDestinationSummary .= "\t\tRates associated with this destination are:\n";
						foreach ($this->_arrDestinationRates[$intDestination] as $arrRate)
						{
							$strDestinationSummary .= "\t\t\t{$arrRateNames[$arrRate['Id']]}\n";
						}
					}
					else
					{
						// There are currently no rates associated with this Destination
						$strDestinationSummary .= "\t\tThere are currently no Rates in the Rate Group associated with this destination\n";
					}
				}
			}
			
			if ($intProblemCount <= $intMaxProblems)
			{
				// Add the Destination Summary to the TotalSummary
				$strRateGroupSummary .= $strDestinationSummary;
			}
		}
		
		// Check if there were any problems detected
		if ($bolProblemDetected)
		{
			$intProblemsDisplayed = ($intProblemCount < $intMaxProblems) ? $intProblemCount : $intMaxProblems;
			$strRateGroupSummary = "The following problems have been detected:\n" . $strRateGroupSummary . "\nShowing $intProblemsDisplayed of $intProblemCount problems\n";
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
	// _BuildDestinationRates
	//------------------------------------------------------------------------//
	/**
	 * _BuildDestinationRates()
	 *
	 * Builds a Rate Allocation Summary, for each destination of the RecordType, as a multi-dimensional array
	 * 
	 * Builds a Rate Allocation Summary, for each destination of the RecordType, as a multi-dimensional array
	 * This array is stored in the private member varibale $this->_arrDestinationRates
	 * And is of the form [Destination][]	= $arrRateDetails
	 * where $arrRateDetails[Id] 			= Id of the Rate
	 *						[StartTime]		= Time of day, at which the rate starts applying (in seconds after midnight)
	 *						[EndTime]		= the latest time of the day, at which the rate still applies (in seconds after midnight)
	 *						[FirstInterval]	= the 15 minute interval which StartTime relates to
	 *						[LastInterval]	= the 15 minute interval which EndTime relates to
	 *						[Monday] - [Sunday]	= booleans, TRUE if the Rate applies to this day
	 *	Note: each day has intervals 1 through 96
	 *			Interval 1 is from	00:00:00 till 00:14:59
	 *			Interval 2 is from	00:15:00 till 00:29:59
	 *			Interval 96 is from	23:45:00 till 23:59:59
	 *
	 * @param		integer		$intRecordType	Id of the RecordType of the RateGroup
	 * @param		array		$arrRateIds		indexed array of Ids of the Rates that belong to this RateGroup
	 *
	 * @return		void
	 *
	 * @method
	 */
	private function _BuildDestinationRates($intRecordType, $arrRateIds)
	{
		// Initialise the cached Array
		$this->_arrDestinationRates = Array();
		
		// We need to retrieve a list of all destinations, if the rates are subject to destinations
		// Retrieve a list of destinations
		$selDestinations = new StatementSelect("Destination", "Code, Context", "Context IN (SELECT Context FROM RecordType WHERE Id = <RecordType>)");
		$intNumOfDestinations = $selDestinations->Execute(Array("RecordType" => $intRecordType));
		
		if ($intNumOfDestinations > 0)
		{
			// The RateGroups of this RecordType must have a rate covering all times of the week for all destinations
			$arrDestinations = $selDestinations->FetchAll();
			
			foreach ($arrDestinations as $arrDestination)
			{
				$this->_arrDestinationRates[$arrDestination['Code']] = Array();
			}
		}
		else
		{
			// The RecordType does not make use of Destinations
			$this->_arrDestinationRates[0] = Array();
		}
		
		// Retrieve the rates selected
		$strWhere = "Id IN (". implode(",", $arrRateIds) .")";
		$selRates = new StatementSelect("Rate", "Id, StartTime, EndTime, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday, Destination", $strWhere, "StartTime ASC");
		$selRates->Execute();
		$arrRates = $selRates->FetchAll();
		
		// Loop through each rate, and append its time details against the destination it applys to
		$intMidnight = mktime(0, 0, 0);
		foreach ($arrRates as $arrRate)
		{
			// Work out the start interval
			// Interval 1 starts at 00:00:00. Interval 2 starts at 00:15:00.  Interval 96 starts at 23:45:00
			$arrRate['StartTime']		= strtotime($arrRate['StartTime']) - $intMidnight;
			$arrRate['FirstInterval']	= ($arrRate['StartTime'] / (15 * 60)) + 1;
			
			// Work out the end interval
			// Interval 1 ends at 00:14:59.  Interval 2 ends at 00:29:59.  Interval 96 ends at 23:59:59
			$arrRate['EndTime']			= strtotime($arrRate['EndTime']) - $intMidnight;
			$arrRate['LastInterval']	= ($arrRate['EndTime'] + 1) / (15 * 60);
			
			// Convert the start time and end time into seconds relative to midnight
			$intDestination				= $arrRate['Destination'];
			unset($arrRate['Destination']);
			
			// Append the rate to the list of rates for this destination
			$this->_arrDestinationRates[$intDestination][] = $arrRate;
		}
		
		// We now have a structure which stores the Rate application times (ordered by StartTime ascending), for each destination of the RecordType
	}

	//------------------------------------------------------------------------//
	// _BuildRateSummary
	//------------------------------------------------------------------------//
	/**
	 * _BuildRateSummary()
	 *
	 * Builds the Rate Summary as a multi-dimensional array
	 * 
	 * Builds the Rate Summary as a multi-dimensional array.  This defines which Destinations are Over or Under allocated, and the time ranges
	 * of the week where they are over or under allocated
	 * This summary is stored in the private data attribute _arrDestinationRateSummary, the structure of which is:
	 * _arrDestinationRateSummary	['OverAllocated']	= boolean, TRUE if at least 1 of the Destinations is Over Allocated at some point during the week
	 * 								['UnderAllocated']	= boolean, TRUE if at least 1 of the Destinations is Under Allocated at some point during the week
	 *								['Destinations'][DestinationCode] = $arrDestinationSummary where:
	 * arrDestinationSummary	['OverAllocated']		= boolean, TRUE if the Destination is Over Allocated at some point during the week
	 *							['UnderAllocated']		= boolean, TRUE if the Destination is Under Allocated at some point during the week
	 *							[Weekday]['OverAllocations'][]	= $arrIntervalRange (defining the range of intervals affected by an Over Allocation for that day)
	 *							[Weekday]['UnderAllocations'][]	= $arrIntervalRange (defining the range of intervals affected by an Under Allocation for that day)
	 * arrIntervalRange	['Start']	= The first interval of the day in which the Over Allocation or Under Allocation applies
	 *					['End']		= The last interval of the day in which the Over Allocation or Under Allocation applies
	 *	
	 *
	 * @param	integer		$intRecordType	Id of the RecordType of the RateGroup
	 * @param	array		$arrRateIds		indexed array of Ids of the Rates that belong to this RateGroup
	 *
	 * @method
	 *
	 */
	private function _BuildRateSummary($intRecordType, $arrRateIds)
	{
		// There are 24 hours in a day and 4 intervals in an hour
		$intLastIntervalForDay = 4 * 24;
	
		// Check if $this->$arrDestinationSummary has not already been built
		if ($this->_arrDestinationRates === NULL)
		{
			$this->_BuildDestinationRates($intRecordType, $arrRateIds);
		}

		// We now have a structure which stores the Rate application times, for each destination of the RecordType
		// The docblock for _BuildDestinationRates() describes the _arrDestinationRates array structure
		
		$arrDestinationSummary = Array();
		$bolOverAllocated	= FALSE;
		$bolUnderAllocated	= FALSE;
		
		$arrWeekdays = Array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		
		// For each Destination work out whether or not it is over allocated or under allocated
		// Note that a destination can be both over allocated and under allocated at the same time
		foreach ($this->_arrDestinationRates as $intDestination=>$arrRates)
		{
			$arrDestinationSummary[$intDestination] = Array();
			$arrDestinationSummary[$intDestination]['OverAllocated'] = FALSE;
			$arrDestinationSummary[$intDestination]['UnderAllocated'] = FALSE;
			
			foreach ($arrWeekdays as $strDay)
			{
				$arrDestinationSummary[$intDestination][$strDay] = Array();
				$arrDestinationSummary[$intDestination][$strDay]['OverAllocations'] = Array();
				$arrDestinationSummary[$intDestination][$strDay]['UnderAllocations'] = Array();
			
				// Initialise variables
				$intNextIntervalToAccountFor	= 1;
				$intLatestLastInterval			= 0;
				
				// Go through each Rate assigned to this destination
				foreach ($arrRates as $arrRate)
				{
					// Check that the rate applies for this day
					if ($arrRate[$strDay])
					{
						if ($arrRate['FirstInterval'] > $intNextIntervalToAccountFor)
						{
							// There is a gap in the Rate applying to this day.  This means there is an underallocation for the day
							$arrDestinationSummary[$intDestination]['UnderAllocated'] = TRUE;
							$bolUnderAllocated = TRUE;
							$arrDestinationSummary[$intDestination][$strDay]['UnderAllocations'][] = Array(	"Start" => $intNextIntervalToAccountFor, 
																											"End" => $arrRate['FirstInterval'] - 1);
						}
						elseif ($arrRate['FirstInterval'] < $intNextIntervalToAccountFor)
						{
							// There is an overlap in the rates applying to this day.  This means there is an over allocation for the day
							$arrDestinationSummary[$intDestination]['OverAllocated'] = TRUE;
							$bolOverAllocated = TRUE;
							
							// Work out when to end the Over Allocation
							if ($arrRate['LastInterval'] < $intNextIntervalToAccountFor)
							{
								// The current Rate ends before (or when) the last one finished
								$intEndInterval = $arrRate['LastInterval'];
							}
							else
							{
								// The current Rate ends after the last one finished
								$intEndInterval = $intNextIntervalToAccountFor - 1;
							}
							
							$arrDestinationSummary[$intDestination][$strDay]['OverAllocations'][] = Array(	"Start" => $arrRate['FirstInterval'],
																											"End" => $intEndInterval);
						}
						
						// Update the Next Interval to account for, variable
						if ($arrRate['LastInterval'] >= $intNextIntervalToAccountFor)
						{
							$intNextIntervalToAccountFor = $arrRate['LastInterval'] + 1;
							$intLatestLastInterval = $arrRate['LastInterval'];
						}
					}
				}
				
				// Check that the latest LastInterval is the last interval of the day 
				if ($intLatestLastInterval < $intLastIntervalForDay)
				{
					// There must be an under allocation which won't have been picked up yet
					$arrDestinationSummary[$intDestination]['UnderAllocated'] = TRUE;
					$bolUnderAllocated = TRUE;
					$arrDestinationSummary[$intDestination][$strDay]['UnderAllocations'][] = Array(	"Start" => $intLatestLastInterval + 1,
																									"End" => $intLastIntervalForDay);
				}
			}
		}
		
		$this->_arrDestinationRateSummary = Array();
		$this->_arrDestinationRateSummary['OverAllocated'] = $bolOverAllocated;
		$this->_arrDestinationRateSummary['UnderAllocated'] = $bolUnderAllocated;
		$this->_arrDestinationRateSummary['Destinations'] = $arrDestinationSummary;
	}

	//------------------------------------------------------------------------//
	// _BuildGraphicalRateSummary
	//------------------------------------------------------------------------//
	/**
	 * _BuildGraphicalRateSummary()
	 *
	 * Builds the array required to display the graphical representation of the Rate Summary
	 * 
	 * Builds the array required to display the graphical representation of the Rate Summary
	 *
	 * @param	integer		$intRecordType	Id of the RecordType of the RateGroup
	 * @param	array		$arrRateIds		indexed array of Ids of the Rates that belong to this RateGroup
	 *
	 * @return	array		[Weekday][Interval]	= Status
	 *						where:	Weekday		= Monday - Sunday
	 *								Interval	= 1 - 96 (the 15 minute interval since midnight. See _BuildDestinationRates() docblock)
	 *								Status 		= RATE_ALLOCATION_STATUS_(CORRECTLY | OVER | UNDER | BOTH_OVER_AND_UNDER)_ALLOCATED
	 *
	 * @method
	 */
	private function _BuildGraphicalRateSummary($intRecordType, $arrRateIds)
	{
		if ($this->_arrDestinationRateSummary === NULL)
		{
			$this->_BuildRateSummary($intRecordType, $arrRateIds);
		}
		
		$arrWeekdays = Array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		
		// Initialise the Summary as every interval being OK
		$arrSummary = Array();
		$arrIntervals = Array();
		for ($i=1; $i<=96; $i++)
		{
			$arrIntervals[$i] = RATE_ALLOCATION_STATUS_CORRECTLY_ALLOCATED;
		}
		foreach ($arrWeekdays as $strDay)
		{
			$arrSummary[$strDay] = $arrIntervals;
		}
		
		// For each destination
		foreach ($this->_arrDestinationRateSummary['Destinations'] as $arrDestination)
		{
			// Check if this Destination has been flagged as being under or over allocated at some point during the week
			if ($arrDestination['UnderAllocated'] || $arrDestination['OverAllocated'])
			{
				// This destination has an under allocation, or over allocation at some time during the week
				// Update the intervals of $arrSummary accordingly
				foreach ($arrWeekdays as $strDay)
				{
					// Mark all the intervals that are under allocated
					foreach ($arrDestination[$strDay]['UnderAllocations'] as $arrIntervalRange)
					{
						for ($i=$arrIntervalRange['Start']; $i <= $arrIntervalRange['End']; $i++)
						{
							switch ($arrSummary[$strDay][$i])
							{
								case RATE_ALLOCATION_STATUS_OVER_ALLOCATED:
									// flag it as being both under allocated and over allocated
									$arrSummary[$strDay][$i] = RATE_ALLOCATION_STATUS_BOTH_OVER_AND_UNDER_ALLOCATED;
									break;
									
								case RATE_ALLOCATION_STATUS_CORRECTLY_ALLOCATED:
									// flag it as being under allocated
									$arrSummary[$strDay][$i] = RATE_ALLOCATION_STATUS_UNDER_ALLOCATED;
									break;
									
								case RATE_ALLOCATION_STATUS_BOTH_OVER_AND_UNDER_ALLOCATED:
								default:
									// already flagged as under allocated
									break;
							}
						}
					}
					
					// Mark all the intervals that are over allocated
					foreach ($arrDestination[$strDay]['OverAllocations'] as $arrIntervalRange)
					{
						for ($i=$arrIntervalRange['Start']; $i <= $arrIntervalRange['End']; $i++)
						{
							switch ($arrSummary[$strDay][$i])
							{
								case RATE_ALLOCATION_STATUS_UNDER_ALLOCATED:
									// flag it as being both under allocated and over allocated
									$arrSummary[$strDay][$i] = RATE_ALLOCATION_STATUS_BOTH_OVER_AND_UNDER_ALLOCATED;
									break;
									
								case RATE_ALLOCATION_STATUS_CORRECTLY_ALLOCATED:
									// flag it as being under allocated
									$arrSummary[$strDay][$i] = RATE_ALLOCATION_STATUS_OVER_ALLOCATED;
									break;
									
								case RATE_ALLOCATION_STATUS_BOTH_OVER_AND_UNDER_ALLOCATED:
								default:
									// already flagged as over allocated
									break;
							}
						}
					}
				}
			}
		}
		
		return $arrSummary;
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
			//DBO()->RateGroup->Id = DBO()->ServiceRateGroup->Selected->Value;
			//DBO()->RateGroup->Load();
		
			$strCurrentDate = GetCurrentDateForMySQL();
			$strCurrentDateAndTime = GetCurrentDateAndTimeForMySQL();
			$strChangesNote = "";
			$strChangesNote .= "RecordType: " . DBO()->RecordType->Name->Value . "\n";
			$strChangesNote .= "RateGroup: " . DBO()->RateGroup->Name->Value . "\n";
			
			// Convert Current date into seconds
			$intCurrentDate = strtotime($strCurrentDate);
			$intStartDate = strtotime(ConvertUserDateToMySqlDate(DBO()->ServiceRateGroup->StartDate->Value));
			$intEndDate  = strtotime(ConvertUserDateToMySqlDate(DBO()->ServiceRateGroup->EndDate->Value));	

			if (DBO()->RateGroup->ImmediateStart->Value != 1 && DBO()->RateGroup->IndefinateEnd->Value != 1)
			{
				if ($intEndDate < $intStartDate)
				{
					// The End Date is in the past
					DBO()->ServiceRateGroup->EndDate->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: Can not have a date ending in the past");
					Ajax()->RenderHtmlTemplate("RateGroupOverride", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;								
				}
			}

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
					Ajax()->AddCommand("Alert", "ERROR: The End date is not in the correct format of dd/mm/yyyy");
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
			
			// Fire the OnNewNote Event (Ajax()->FireOnNewNote(accountId, serviceId))
						
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The overriding RateGroup was successfully defined");

			// Build event object
			// The contents of this object should be declared in the doc block of this method
			$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
			Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
			
			// Fire the EVENT_ON_SERVICE_PLAN_UPDATE Event (this constant hasn't been made yet)
			//TODO! for now the EVENT_ON_SERVICE_UPDATE will suffice
			return TRUE;
		}
		
		$this->LoadPage('rate_group_override');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// Export
	//------------------------------------------------------------------------//
	/**
	 * Export()
	 *
	 * Exports a RateGroup as a csv file
	 * 
	 * Exports a RateGroup as a csv file
	 * This method expects the following values to be defined:
	 *	Either:
	 *	(
	 *			When exporting a skeleton csv file for a rate group
	 *			DBO()->RecordType->Id			RecordType of the RateGroup
	 *	)
	 *	OR
	 *	(
	 *			When exporting a csv file based on a rate group
	 *			DBO()->RateGroup->Id			Id of the RateGroup to export
	 *	)
	 *
	 * @return		void
	 *
	 * @method
	 */
	function Export()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		// Initialise variables
		$strRateGroupCSV = "";
		$strFilename = "";
		
		$arrRateGroupColumns = Array("RateGroup Id", "Name", "Description", "Service Type",	"Record Type");		

		$arrRateColumnNames = Array("Rate Id", 	"Editable", "Destination Code",
												"Destination",												
												"Name", 
												"Description",
												"Start Time", 
												"End Time", 
												"Monday", 
												"Tuesday", 
												"Wednesday" , 
												"Thursday", 
												"Friday", 
												"Saturday", 
												"Sunday",
												"Pass through at cost",
												"Excluded from Cap Plan",
												"Prorate",
												"Minimum Charge ($)",
												"Standard Flagfall ($)",
												"Standard Billing Units",
												"Charge Per Single Unit ($)",
												"Markup on Cost ($)",
												"Markup on Cost (%) (1.5 = 1.5%)",
												"Start Capping at (Units)",
												"Start Capping at ($)",
												"Stop Capping at (Units)",
												"Stop Capping at ($)",
												"Excess Flagfall ($)",
												"Excess Billing Units",
												"Charge Per Single Excess Unit ($)",
												"Excess Markup on Cost ($)",
												"Excess Markup on Cost (%) (1.5 = 1.5%)");
		if (DBO()->RateGroup->Id->Value)
		{
			// Export the RateGroup defined in DBO()->RateGroup
			DBO()->RateGroup->Load();
			DBO()->RecordType->Id = DBO()->RateGroup->RecordType->Value;
			DBO()->RecordType->Load();
			
			$strFilename = DBO()->RecordType->Name->Value ." - ". DBO()->RateGroup->Name->Value;
			
			$arrRateGroup = Array	(
										DBO()->RateGroup->Id->Value,
										DBO()->RateGroup->Name->Value,
										DBO()->RateGroup->Description->Value,
										DBO()->RateGroup->ServiceType->Value,
										DBO()->RateGroup->RecordType->Value
									);
			
			$arrColumnNames = Array("RateId"=>"R.Id", "Editable"=>"IF(R.Archived = ". RATE_STATUS_DRAFT .", \"Yes\", \"No\")",
														"DestinationCode"=>"D.Code", "DestinationDescription"=>"D.Description", "RateName"=>"R.Name", "RateDescription"=>"R.Description",
														"StartTime"=>"R.StartTime", "EndTime"=>"R.EndTime", "Monday"=>"R.Monday", "Tuesday"=>"R.Tuesday", "Wednesday"=>"R.Wednesday",
														"Thursday"=>"R.Thursday", "Friday"=>"R.Friday", "Saturday"=>"R.Saturday", "Sunday"=>"R.Sunday", "PassThrough"=>"R.PassThrough",
														"Uncapped"=>"R.Uncapped", "Prorate"=>"R.Prorate", "StdMinCharge"=>"R.StdMinCharge", "StdFlagfall"=>"R.StdFlagfall", "StdUnits"=>"R.StdUnits",
														"StdRatePerUnit"=>"R.StdRatePerUnit", "StdMarkup"=>"R.StdMarkup", "StdPercentage"=>"R.StdPercentage", "CapUnits"=>"R.CapUnits",
														"CapCost"=>"R.CapCost", "CapUsage"=>"R.CapUsage", "CapLimit"=>"R.CapLimit", "ExsFlagfall"=>"R.ExsFlagfall", "ExsUnits"=>"R.ExsUnits",
														"ExsRatePerUnit"=>"R.ExsRatePerUnit", "ExsMarkup"=>"R.ExsMarkup","ExsPercentage"=>"R.ExsPercentage");
			
			$selRates = new StatementSelect("Rate AS R LEFT OUTER JOIN Destination AS D ON R.Destination = D.Code", $arrColumnNames, "R.Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = <RateGroupId>)","D.Description, R.Name");
			
			$mixNumRecords = $selRates->Execute(Array("RateGroupId" => DBO()->RateGroup->Id->Value));
			$arrRates = $selRates->FetchAll();

			$strRateGroupCSV .= MakeCSVLine($arrRateGroupColumns);
			$strRateGroupCSV .= MakeCSVLine($arrRateGroup);
			$strRateGroupCSV .= "\n";
			$strRateGroupCSV .= MakeCSVLine($arrRateColumnNames);
			foreach ($arrRates as $arrRate)
			{
				$strRateGroupCSV .= MakeCSVLine($arrRate);
			}
		}
		elseif (DBO()->RecordType->Id->Value)
		{
			// Export a skeleton csv for the given RecordType defined in RecordType
			DBO()->RecordType->Load();
			
			$arrBlankRateGroup = Array(NULL,NULL,NULL,DBO()->RecordType->ServiceType->Value,DBO()->RecordType->Id->Value);
			
			$arrRate = Array(NULL, "Editable"=>"Yes", "DestinationCode"=>NULL, "DestinationDescription"=>NULL, "RateName"=>"<RateGroupName> - <Destination>", "RateDescription"=>"<RateGroupName> - <Destination>",
								"00:00:00",	"23:59:59", 1, 1, 1, 1, 1, 1,
											1,
											0,
											0,
											0,
											0,
											0,
											1,
											0,
											0,
											0,
											0,
											0,
											0,
											0,
											0,
											0,
											0,
											0,
											0
										);
										
			$strRateGroupCSV .= MakeCSVLine($arrRateGroupColumns);
			$strRateGroupCSV .= MakeCSVLine($arrBlankRateGroup);
			$strRateGroupCSV .= "\n";
			$strRateGroupCSV .= MakeCSVLine($arrRateColumnNames);	
			
			if (DBO()->RecordType->Context->Value > 0)
			{
				// load the destinations
				DBL()->Destination->Context = DBO()->RecordType->Context->Value;
				DBL()->Destination->Load();
				
				foreach(DBL()->Destination as $dboDestination)
				{
					$arrRate['DestinationCode'] = $dboDestination->Code->Value;
					$arrRate['DestinationDescription'] = $dboDestination->Description->Value;
					
					$strRateGroupCSV .= MakeCSVLine($arrRate);
				}
			}
			else
			{
				// not destination based so set destination code and destination description to null when output into CSV rate Id is always set to null as no rates
				$arrRate['RateName'] = "<RateGroupName>";
				$arrRate["RateDescription"] = "<RateGroupName>";
				
				$strRateGroupCSV .= MakeCSVLine($arrRate);
			}


			$strFilename = DBO()->RecordType->Name->Value ." - Skeleton";
		}
		else
		{
			// The Input parameters have not been set up properly for this function
			//TODO! The user should probably be warned, however this function is not being triggered via an ajax call, 
			// so we can't use popups.  For now it is acceptable to just have the process die
			die;
		}
		
		// Convert the filename to lower case and use underscores instead of spaces
		$strFilename = str_replace('"', "'", $strFilename);
		$strFilename = strtolower($strFilename) . ".csv";
		$strFilename = str_replace(" ", "_", $strFilename);
		
		// Send the csv file to the user
		header("Content-Type: text/plain");
		header("Content-Disposition: attachment; filename=\"$strFilename\"");
		echo $strRateGroupCSV;
		exit;
	}
	
	//------------------------------------------------------------------------//
	// Import
	//------------------------------------------------------------------------//
	/**
	 * Import()
	 *
	 * Logic for the Import RateGroup popup
	 * 
	 * Logic for the Import RateGroup popup
	 * This method expects the following values to be defined:
	 *		DBO()->RecordType->Id			RecordType of the RateGroup
	 *		DBO()->RateGroup->Fleet			TRUE if you want to import the RateGroup as a Fleet RateGroup; 
	 *										FALSE for importing normal Rate Groups
	 *
	 * @return		void
	 *
	 * @method
	 */
	function Import()
	{
		// Check user authorization and permissions
		//AuthenticatedUser()->CheckAuth();
		//AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		// The import algorithm
		// no need to check for submitted form as wont be submitted via AJAX request
		//if (SubmittedForm("ImportRateGroup", "Import As Draft") || SubmittedForm("ImportRateGroup", "Import And Commit") )
		//{
		
			/* The following commented out block of code is a loose algorithm as to how to import and validate a RateGroup*/
			//---->>

			// The $arrRateGroup and $arrRate are dynamically assigned values at run-time

			$arrRateGroupKeys	= Array();
			$arrRateKeys		= Array();
			
			$arrRateGroup = Array();
			$arrRate = Array();
			
			$bolIsFleet			= DBO()->RateGroup->Fleet->Value;
			
			DBO()->RecordType->Load();
			
			// This will store the Rate Ids of the Rates belonging to the RateGroup, it will be used to 
			$arrRateIds			= Array();
			
			// This will store the report regarding how the import went; why it was unsuccessful, etc
			// Even if something (Rate/RateGroup) is invalid, we want to parse the entire csv file, so that we can report
			// on as many errors as possible, in one hit.  We might want to limit this so that if 20 errors are met, then 
			// further processing is halted, otherwise the user could recieve an error report that is more than 1 MB large.
			// This has been seen to be the case with the report generated on the ViewRateSummary popup
			$strImportReport	= "";
			$intTotalErrors		= 0;
			$intMaxErrors		= 50; // This should probably be a constant defined in ui_app/definitions.php
		
			//Do whatever you have to do to get a file pointer to the file so that you can use the fgetcsv() function
			//TODO
			
			// When a file is uploaded it is temporarily stored and you're able to read from it an error code number 
			// indicating a number of successful uploads or failures
			
			$intErrorCode = $_FILES['userfile']['error'];
			switch($intErrorCode)
			{
				// If the file has been uploaded ok
				case UPLOAD_ERR_OK:
				// If a pointer is able to be obtained to the uploaded file open it but as readonly
				if ($objFilePointer = fopen($_FILES['userfile']['tmp_name'], "r"))
				{
					// count the rows
					$intRowCounter = 1;
					// while there are more lines in the CSV file to read...
					while (($arrData = fgetcsv($objFilePointer)) !== FALSE)
					{
						// header and values of RateGroups
						// read the first line save the header in the array and then continue
						if ($intRowCounter == 1)
						{
							foreach ($arrData as $intKeys => $value)
							{
								$arrRateGroupKeys[$value] = $value;
							}
						}
						elseif ($intRowCounter == 2)
						{	
							// Read the second line of the file (RateGroup details)
							$arrRateGroup = array_combine($arrRateGroupKeys, $arrData);
							 
							if ($arrRateGroup['Id'] != NULL)
							{
								// The RateGroup must already be in the database
								//Retrieve the RateGroup from the database
								DBO()->RateGroup->Id = $arrRateGroup['Id'];
								DBO()->RateGroup->Load();
								if (!DBO()->RateGroup->Id->Value)
								{
									//exit alerting the user that they are referencing a RateGroup that could not be found in the database
									DBO()->RateGroup->StatusCode = RATEGROUP_NOTHING_UPLOADED;
									DBO()->RateGroup->StatusMessage = "referencing a RateGroup that could not be found in the database";
									$this->LoadPage('rate_group_upload');
									return TRUE;				
									break;
								}
								// The RateGroup was successfully retrieved from the database
								if ((DBO()->RateGroup->Archived->Value == 0) || (DBO()->RateGroup->Archived->Value == 1))
								//if (the RateGroup is a committed RateGroup (Archived == 0) or an archived RateGroup (Archived == 1))
								{
									// you cannot modify a committed or archived RateGroup
									//exit alerting the user that they cannot modify a (committed|archived) RateGroup
									//pass a status code and description back
									// --------------------------------------------------------------------------------------------------------
									// There are two extra properties in the UI app documentation for Rategroup, these being StatusCode
									// and StatusMessage, as the doesnt use AJAX this was the next most logical way to retain a 'state' and/or
									// pass messages back to the HTML page. StatusCode uses the pre-existing constants of error segment in the 
									// file array that is created during the file upload
									// --------------------------------------------------------------------------------------------------------
									DBO()->RateGroup->StatusCode = RATEGROUP_NOTHING_UPLOADED;
									DBO()->RateGroup->StatusMessage = "cannot modify a (committed|archived) RateGroup";
									$this->LoadPage('rate_group_upload');
									return TRUE;				
									break;
								}
								
								// The RateGroup must be a Draft RateGroup, save a reference to it, to compare against the one being imported
								// It will basically just compare the ServiceType and RecordType to make sure they match
								$dboExistingRateGroup = DBO()->RateGroup;
							}
							else
							{
								// The RateGroup is not already in the database
								$dboExistingRateGroup = NULL;
							}
							
							
							// Do preliminary Validation of the RateGroup (this should be wrapped in its own function)
							$mixErrorMsg = this->_ValidateImportedRateGroupDetails($arrRateGroup, DBO()->RecordType, $dboExistingRateGroup);
							if ($mixErrorMsg != NULL)
							{
								// The imported RateGroup failed validation
								$strImportReport .= $mixErrorMsg;
								$intTotalErrors += 1;
							}
							
							TransactionStart();
							if ($intTotalErrors == 0)
							{
								// The preliminary RateGroup details are valid.  save the RateGroup
								//TODO! Save Record to the RateGroup table, or update the existing one, if it already exists as a draft
								$strWhere = "Name=<Name> AND RecordType=<RecordType> AND Archived=" . ARCHIVE_STATUS_DRAFT; //use constant!
								$selRateGroupName = new StatementSelect("RateGroup", "Id", "Archived", $strWhere);
								if ($selRateGroupName->Execute(Array("Name" => $arrRateGroupKeys['Name'], "RecordType" => $arrRateGroupKeys['RecordType'])) > 0)
								{
									DBO()->RateGroup->Id = $arrGroupKeys['Id'];
								}
								
								DBO()->RateGroup->Where->Name = $arrRateGroupKeys['Name'];
								DBO()->RateGroup->Name = $arrRateGroupKeys['Name'];
								DBO()->RateGroup->Description = $arrRateGroupKeys['Description'];
								DBO()->RateGroup->RecordType = $arrRateGroupKeys['Record Type'];
								DBO()->RateGroup->Service = $arrRateGroupKeys['Service Type'];
								
								if (!DBO()->RateGroup->Save())
								{
									//can not save alert user
									DBO()->RateGroup->StatusCode = RATEGROUP_NOTHING_UPLOADED;
									DBO()->RateGroup->StatusMessage = "Can not save";
									$this->LoadPage('rate_group_upload');
									return true;
									break;-
								}
								
							}
						}
						$mixResult = NULL;
							
						if ($intRowCounter == 4)
						{
							foreach ($arrData as $intKeys => $value)
							{
								$arrRateKeys[$value] = $value;
							}
						}
						elseif ($intRowCounter > 4)
						{
							$arrRate = array_combine($arrRateKeys, $arrData);
						}	
						
						if ($arrRate['Id'] != NULL)
						{
							// The user has specified a rate that already exists in the database. Retrieve this Rate
							DBO()->Rate->Id = $arrRate['Id'];
							DBO()->Rate->Load();
							if (DBO()->Rate->Id->Value == 0)
							{
								$intTotalErrors += 1;
								if ($intTotalErrors < $intMaxErrors)
								{
									$strImportReport .= "Rate {$arrRate['Name']} could not be found in the database.  If it is a new rate, then please remove the Rate Id from the line";
									// Move on to the next Rate
									continue;
								}
								else
								{
									// Max number of errors has been reached
									TransactionRollback();
									// Stop processing the csv file, and return the "Import Report" to the user
									//TODO!
								}
							}
							
							// The Rate was successfully retrieved
							if (DBO()->Rate->Archived->Value == RATE_STATUS_ACTIVE)
							{
								// Make sure the Rate's recordType is correct
								if (DBO()->Rate->RecordType->Value != DBO()->RecordType->Id->Value)
								{
									// This Rate cannot be associated with this RateGroup
									//TODO! add this as an error
									$strImportReport .= "This rate cannot be associated with this RateGroup";
									$intTotalErrors += 1;
								}
							
								// Don't bother updating the rate, but you will still have to create a record in the RateGroupRate table to link this Rate
								// to the RateGroup
								$arrRateIds[] = DBO()->Rate->Id->Value;
								continue;
							}
							elseif (DBO()->Rate->Archived->Value == RATE_STATUS_ARCHIVED)
							{
								// You cannot include archived rates in a new rate group
								$intTotalErrors += 1;
								if ($intTotalErrors < $intMaxErrors)
								{
									$strImportReport .= "Rate " . DBO()->Rate->Name->Value . " is currently archived.  It cannot be used for new RateGroups";
									continue;
								}
								else
								{
									// Max number of errors has been reached
									TransactionRollback();
									// Stop processing the csv file, and return the "Import Report" to the user
									//TODO!
								}
							}
							
							// The Rate must be an existing Draft Rate
							$dboExistingRate = DBO()->Rate;
						}
						else
						{
							// The Rate is not already in the database
							$dboExistingRate = NULL;
						}
						
						// Validate the Rate (this will be wrapped in its own function (if $arrRate['Id'] is set, then it references an existing draft rate in the database)) 
						$mixResult = this->_ValidateImportedRate($arrRate, DBO()->RecordType, $dboExistingRate);
						
						if (is_string($mixResult))
						{
							// The rate failed validation
							$intTotalErrors += 1;
							if ($intTotalErrors < $intMaxErrors)
							{
								$strImportReport .= $mixErrorMsg;
							}
							else
							{
								// Max number of errors has been reached
								TransactionRollback();
								// Stop processing the csv file, and return the "Import Report" to the user
								// TODO!
							}
							
							// Move on to the next Rate
							continue;
						}
						
						// The Rate is valid. Save/update it in the database
						//TODO! If $arrRate['Id'] != NULL then the Rate already exists in the database as a draft rate.  Use a prebuilt StatementUpdate
						//		object to update the record.
						//		If $arrRate['Id'] == NULL then the Rate is a new one.  Use a prebuilt StatementInsert object to insert the record.
						//		The StatementInsert->Execute method will return the new Id assigned to the Rate
						// Don't forget to replace the place holders from the Name And Description values
						//		replace <RateGroupName> for the actual RateGroupName and <Destination> for the actual Destination
						$intRateId = $insRate->Execute($arrRate);
						
						// Add the Rate's assigned Id to the list of Rates to link to the RateGroup
						$arrRateIds[] = $arrRate['Id'] or $intRateId;
										
						if ($intTotalErrors != 0)
						{
							// Don't continue processing if there are any errors.  Return the Import Report to the user
							TransactionRollback();
							//TODO
						}
						
						// Each Rate belonging to the RateGroup is now stored in the Rate table, and no errors have been encountered so far
						// Perform the RateGroup validation which works out if each Rate Group is over allocated, under allocated or correctly allocated
						
						
						// Validate the Rate allocations for the RateGroup
						$this->_BuildRateSummary(DBO()->RecordType->Id->Value, $arrRateIds);
						if ($this->_arrDestinationRateSummary['OverAllocated'] OR ($this->_arrDestinationRateSummary['UnderAllocated'] AND $bolIsFleet != TRUE))
						{
							// The RateGroup either has over allocations or (under allocations and isn't a fleet RateGroup).  Fleet RateGroups can have under
							// allocations
							
							// Build the Rate summary report and return it to the user (this is the report that is generated on the PreviewRateSummary popup)
							$strReport = _BuildRateSummaryProblemReport(DBO()->RecordType->Id->Value, $arrRateIds, $bolIsFleet)
							TransactionRollback();  // This has to be done after the report to built, not before it
							exit
						}
						
						// Remove all the links between this rate group, and the rates belonging to it (there may not be any)
						$qryDeleteRateGroupRate = new Query();
						$qryDeleteRateGroupRate->Execute("DELETE FROM RateGroupRate WHERE RateGroup = " . (id of the RateGroup));
						
						// Add a record to the RateGroupRate table, linking the RateGroup to each of its Rates
						$arrRateGroupRateValues = Array("RateGroup" => DBO()->RateGroup->Id->Value, "Rate"=>NULL);
						$insRateGroupRate = new StatementInsert("RateGroupRate", $arrRateGroupRateValues);
						foreach ($arrRateIds as $intRateId)
						{
							$arrRateGroupRateValues['Rate'] = $intRateId;
							$insRateGroupRate->Execute($arrRateGroupRateValues);
						}
						}
						// The RateGroup was successfully imported
						//TODO! Notify the user
						//TODO! Update the appropriate combobox on the AddRatePlan Page  (much like how the AddRateGroup popup updates this page)
						
						
						// This page template will have to use a new layout_template, which doesn't load any of the standard page stuff
						// like css, js, the menu, etc
						// or failing that we could just echo the summary html here and not bother with the page template, layout template and html template
						$this->LoadPage('rate_group_import_summary');
						return TRUE;
					}
					break;
			case UPLOAD_ERR_INI_SIZE:
				// unsuccessful upload exceeds upload size in php.ini
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_INI_SIZE;
				$this->LoadPage('rate_group_upload');
				return TRUE;				
				break;
			case UPLOAD_ERR_FORM_SIZE:
				// unsuccessful upload exceeds upload size in HTML form
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_FORM_SIZE;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_PARTIAL:
				// unsuccessful upload only partial upload
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_PARTIAL;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				// unsuccessful upload no temporary folder
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_NO_TMP_DIR;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_CANT_WRITE:
				// unsuccessful upload failed to write to disc
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_CANT_WRITE;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_EXTENSION:
				// unsuccessful upload stopped by extension ... wtf?
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_EXTENSION;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			default:
				DBO()->RateGroup->StatusCode = RATEGROUP_NOTHING_UPLOAD;
				$this->LoadPage('rate_group_upload');
				return TRUE;				
				break;
			}
		}
		
		
		//DBO()->RecordType->Load();
		
		//$this->LoadPage('rate_group_import');
		//return TRUE;
	}

	/*function ValidateCSV()
	{
		/*$fileFile = $_FILES['userfile'];
		$fileTemporary = $fileFile['tmp_name'];
		if (is_uploaded_file($fileTemporary))
		{
			$this->LoadPage('rate_group_validupload');
			return TRUE;
		}
		else
		{
			DBO()->RateGroup->Name = $_FILES['userfile']['error'];
			$this->LoadPage('rate_group_upload');
			return TRUE;
		}*/
		/*
		$intErrorCode = $_FILES['userfile']['error'];
		switch($intErrorCode)
		{
			case UPLOAD_ERR_OK:
				// successful upload
				
				$arrRateGroupHeader = array();
				$arrRateHeader = array();
				//$arrRateGroupKeys	= Array("Id", "Name", "Description", "ServiceType", "RecordType");
				//$arrRateKeys		= Array("Id", "DestinationCode", "DestinationDescription", "Name", "Description", "PassThrough", etc);
				

				$strTemp = "";
				if ($filFileHandle = fopen($_FILES['userfile']['tmp_name'], "r"))
				{
					$arrRateGroupKeys = Array();
					$intRowCounter = 1;
					while (($arrData = fgetcsv($filFileHandle)) !== FALSE)
					{
						$intColumns = count($arrData);
						
						// header and values of RateGroups
						if ($intRowCounter == 1)
						{
							foreach ($arrData as $intKeys => $value)
							{
								$arrRateGroupHeader[$value] = $value;
							}
						}elseif ($intRowCounter == 2)
						{
							// rudimentary validation
							$arrRateGroupKeys = array_combine($arrRateGroupHeader, $arrData);
							// if array_combine has returned FALSE it cannot combine the arrays
							if (!$arrRateGroupKeys)
							{
								// first count elements in both arrays
								$intNumElementsHeader = count($arrRateGroupHeader);
								$intNumElementsValue = count($arrData);
																
								// if the header elements are less than the number of values																
								if ($intNumElementsHeader < $intNumElementsValue)
								{								
									// work out the size to pad the 'at fault' array to
									$intArrayPadSize = $intNumElementsValue - $intNumElementsHeader;
									array_pad($intNumElementsHeader, $intArrayPadSize, "automatically generated header")
								}
								// if the header elements are greater than the number of values
								if ($intNumElementsHeader > $intNumElementsValue)
								{									
									// work out the size to pad the 'at fault' array to
									$intArrayPadSize = $intNumElementsHeader - $intNumElementsValue;
									array_pad($intNumElementsValue, $intArrayPadSize, "automatically generated values")
								}
							}
						}	
							
						// header and values of Rate(s)
						if ($intRowCounter == 4)
						{
							foreach ($arrData as $intKeys => $value)
							{
								$arrRateHeader[$value] = $value;
							}
						}elseif ($intRowCounter > 4)
						{
							$arrRateKeys= array_combine($arrRateHeader, $arrData);
						}								
							
						//$strTemp .= "{$arrRateKeys['Destination']}\n";	
							
							//$intCounter = 0;
							//foreach ($arrRateGroupKeys as $intKeys => $value)
							//{
								
								//$arrRateGroupKeys[$value] = $arrData[$intCounter];
							//	$strTemp .= "{$arrRateGroupKeys[$value]}<------>{$arrData[$intCounter]}\n";
								//$strTemp .= "{$arrRateGroupKeys[$value]}\n";
							//	$intCounter += 1;
							//}							
						
						/*
						if ($intRowCounter == 2)
						{
							foreach ($arrData as $intKeys => $value)
							{
								$intCounter = 0;
								$arrRateGroupKeys[$intCounter] = $value;
								$intCounter += 1;
								
								
							}							
						}
						*/
						
						
						/*if ($intRowCounter == 2)
						{
							$intcounter = 0;
							foreach ($arrData as $key => $value)
							{
								$arrRateGroupKeyes[$intcounter] = $arrData[$intcounter];
								$intcounter += 1;
								
								$strTemp .= $arrRateGroupKeyes[$intcounter];
							}
						}*/
						
						//$intRowCounter += 1;
						
							//foreach ($arrRateGroupKeys as $intKey=>$intValue)
							//{
							//	$intValue = $arrData[$intColumnCounter];						
							//	$strTemp .= "{$arrData[$intColumnCounter]}\n";
							//	$intColumnCounter += 1;
							//}
						
						// if $intRowCounter is 4 then this is a header of a rate
						/*if ($intRowCounter == 4)
						{
							$intColumnCounter = 0;
							foreach ($arrRateKeys as $intKey=>$intValue)
							{
								$intValue = $arrData[$intColumnCounter];									
								$intColumnCounter += 1;
							}
						}*/
						
						//$strTemp .= $arrRateGroupKeys['Id'];
						//$strTemp .= "{$arrRateGroupKeyes}<---\n";
/*
						$intRowCounter += 1;
					}
				}
				
				DBO()->RateGroup->Description = $strTemp;
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_OK;
				$this->LoadPage('rate_group_upload');
				return TRUE;				
				break;
			case UPLOAD_ERR_INI_SIZE:
				// unsuccessful upload exceeds upload size in php.ini
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_INI_SIZE;
				$this->LoadPage('rate_group_upload');
				return TRUE;				
				break;
			case UPLOAD_ERR_FORM_SIZE:
				// unsuccessful upload exceeds upload size in HTML form
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_FORM_SIZE;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_PARTIAL:
				// unsuccessful upload only partial upload
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_PARTIAL;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				// unsuccessful upload no temporary folder
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_NO_TMP_DIR;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_CANT_WRITE:
				// unsuccessful upload failed to write to disc
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_CANT_WRITE;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			case UPLOAD_ERR_EXTENSION:
				// unsuccessful upload stopped by extension ... wtf?
				DBO()->RateGroup->StatusCode = UPLOAD_ERR_EXTENSION;
				$this->LoadPage('rate_group_upload');
				return TRUE;
				break;
			default:
				DBO()->RateGroup->StatusCode = RATEGROUP_NOTHING_UPLOAD;
				$this->LoadPage('rate_group_upload');
				return TRUE;				
				break;
		}
	}*/

	function DisplayFormElements()
	{
		$this->LoadPage('rate_group_upload');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// _ValidateImportedRateGroupDetails
	//------------------------------------------------------------------------//
	/**
	 * _ValidateImportedRateGroupDetails()
	 *
	 * Validates the RateGroup Details for an imported RateGroup
	 * 
	 * Validates the RateGroup Details for an imported RateGroup
	 *
	 * @param		array		$arrRateGroup			The Rate Group record, pulled from the csv file
	 * @param		DBObject	$dboRecordType			RecordType record of the importing RateGroup
	 * @param		DBObject	$dboExistingRateGroup	optional, DBObject representing the existing RateGroup 
	 *													with the same Id as $arrRateGroup['Id']
	 *													It is assumed this is a draft RateGroup
	 *
	 * @return		mixed		returns an error message if an error was found, else NULL if no errors were found
	 *
	 * @method
	 */
	function _ValidateImportedRateGroupDetails($arrRateGroup, $dboRecordType, $dboExistingRateGroup=NULL)	
	{
		// Check that the RateGroup defined by $arrRateGroup has the same ServiceType and RecordType as that defined in $dboRecordType
		//TODO!
		
		if ($dboExistingRateGroup === NULL)
		{
			// The RateGroup is not currently in the database
			// Check that a RateGroup doesn't already exist in the database with the same name and same RecordType
			//$arrRateGroup['Name']
			//$strWhere = "Name=<RateGroupName> AND RecordType=<RateGroupRecordType>";
			//DBO()->RateGroup->Where->Set($strWhere, Array("RateGroupName" => $arrRateGroup['Name'], "RateGroupRecordType" => $dboRecordType));
			//DBO()->RateGroup->Load();
			
			
			
			// If all these tests are passed, then return NULL, else return an appropriate error message
		}
		else
		{
			// The RateGroup is currently in the database, and is assumed to be a draft RateGroup
			// Check that the ServiceType and RecordType of $dboExistingRateGroup match that of $dboRecordType
			// Check that a RateGroup doesn't already exist in the database with the same name and same RecordType unless its Id == $dboExistingRateGroup->Id->Value
			// If all these tests are passed, then return NULL, else return an appropriate error message
		}
	}
	
	//------------------------------------------------------------------------//
	// _ValidateImportedRate
	//------------------------------------------------------------------------//
	/**
	 * _ValidateImportedRate()
	 *
	 * Validates a single Rate Record taken from an imported csv file
	 * 
	 * Validates a single Rate Record taken from an imported csv file
	 *
	 * @param		array		$arrRate				The Rate record, pulled from the csv file
	 * @param		DBObject	$dboRecordType			RecordType record of the importing RateGroup
	 * @param		DBObject	$dboExistingRate		optional, DBObject representing the existing Rate
	 *													with the same Id as $arrRate['Id']
	 *													It is assumed this is a draft Rate
	 *
	 * @return		mixed		returns an error message if an error was found, else NULL if no errors were found
	 *
	 * @method
	 */
	function _ValidateImportedRate($arrRate, $dboRecordType, $dboExistingRate=NULL)
	{
		// Check that the Rate defined by $arrRate has the same ServiceType and RecordType as that defined in $dboRecordType
		//TODO!
		
		// Validate each property of $arrRate, which has constraints on it
		// For example StartTime has to be a multiple of 15 minutes and has to start on the minute (acceptable values are 00:00:00, 00:15:00, ..., 23:45:00)
		// EndTime has to be of the form 00:14:59, 00:29:59, ..., 23:59:59
		// Where values are NULL, save them as being 0
		// Certain fields cannot be null
		// You can't specify both a Standard Markup on Cost, and an Excess Markup on Cost.  Check out the Rate Validation code in app_template/rate.php
		
		
		if ($dboExistingRate === NULL)
		{
			// The Rate is not currently in the database
			// Check that a Rate doesn't already exist in the database with the same name and same RecordType
			// If all these tests are passed, then return NULL, else return an appropriate error message
		}
		else
		{
			// The Rate is currently in the database, and is assumed to be a draft Rate
			// Check that the ServiceType and RecordType of $dboExistingRate match that of $dboRecordType
			// Check that a Rate doesn't already exist in the database with the same name and same RecordType unless its Id == $dboExistingRate->Id->Value
			// If all these tests are passed, then return NULL, else return an appropriate error message
		}
	}


}
