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
	 *										[LastInterval]	= The interval of the day which this rate is last applied
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
	
	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the View Rate Group popup
	 * 
	 * Performs the logic for the View Rate Group popup
	 * 		DBO()->RateGroup->Id		Id of the RateGroup to view
	 * 		DBO()->Rate->SearchString	This search string is used on the Rate.Name and Rate.Description properties
	 *
	 * @return		void
	 * @method
	 */
	function View()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		// Load the RateGroup
		DBO()->RateGroup->Load();
		$intRateGroupId = DBO()->RateGroup->Id->Value;
		
		// Load the RecordType record associated with the RateGroup
		DBO()->RecordType->Id = DBO()->RateGroup->RecordType->Value;
		DBO()->RecordType->Load();
		
		$arrColumns = Array("Id", "Name", "Description", "Archived");
		DBL()->Rate->SetColumns($arrColumns);
		if (DBO()->Rate->SearchString->IsSet)
		{
			// Retrieve only those Rates that satisfy the search criterea
			$strSearchString = trim(DBO()->Rate->SearchString->Value);
			if ($strSearchString == "")
			{
				// The Search string is empty and considered invalid  
				Ajax()->AddCommand("Alert", "ERROR: Please specify a name or partial name to search");
				return TRUE;
			}
			
			// Escape any special characters
			$strSearchString = str_replace("'", "\'", $strSearchString);
			
			$strLimitToRateGroup = "Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = $intRateGroupId)";
			$strWhere = "(Name LIKE '%$strSearchString%' OR Description LIKE '%$strSearchString%') AND $strLimitToRateGroup";
			DBL()->Rate->Where->SetString($strWhere);
		}
		else
		{
			// A search string has not been specified
			// Load the Rates belonging to the RateGroup (Limit to 11)
			DBL()->Rate->Where->SetString("Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = $intRateGroupId)");
			DBL()->Rate->OrderBy("Name");
			DBL()->Rate->SetLimit(10);
		}
		DBL()->Rate->Load();
		
		// Retrieve the number of Rates belonging to the RateGroup
		$selRateCount = new StatementSelect("Rate", Array("RateCount"=>"Count(Id)"), "Id IN (SELECT RATE FROM RateGroupRate WHERE RateGroup = $intRateGroupId)");
		$selRateCount->Execute();
		$arrRateCount = $selRateCount->Fetch();
		DBO()->RateGroup->TotalRateCount = $arrRateCount['RateCount']; 
		
		$this->LoadPage('rate_group_view');
		return TRUE;
	}
	
	
	//------------------------------------------------------------------------//
	// _ValidateRateGroup
	//------------------------------------------------------------------------//
	/**
	 * _ValidateRateGroup()
	 *
	 * Validates a RateGroup which has been defined in the (Add/Edit)RateGroup Popup
	 * 
	 * Validates a RateGroup which has been defined in the (Add/Edit)RateGroup Popup
	 * This will only work with the "Add Rate Group" popup webpage as it assumes specific DBObjects have been defined within DBO()
	 *
	 * @return		mix				returns TRUE if the new RateGroup is valid
	 *								returns an appropriate error message (string) if something was found to be invalid
	 *								
	 * @method
	 *
	 */
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
		if (DBO()->RateGroup->HasCapLimit->Value)
		{
			// A cap limit has been specified
			if (!Validate("IsMoneyValue", DBO()->RateGroup->CapLimit->Value))
			{
				return "ERROR: Cap Limit is invalid";
			}
		}
		else
		{
			// No Cap Limit has been specified
			DBO()->RateGroup->CapLimit = NULL;
		}
		
		// Check that the name is unique
		if (DBO()->RateGroup->Id->Value == 0)
		{
			// The Rate Group name should not be in the database
			$strWhere = "Name LIKE <Name> AND RecordType=<RecordType>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other RateGroup
			$strWhere = "Name LIKE <Name> AND RecordType=<RecordType> AND Id != ". DBO()->RateGroup->Id->Value;
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
		DBO()->RateGroup->SetColumns("Name, Description, RecordType, ServiceType, Fleet, CapLimit, Archived");
		
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
	 * (This functionality should be modified so that it makes use of the javascript custom event handler (event_handler.js))
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
		
		$strJavascript = "if (Vixen.RatePlanAdd) {Vixen.RatePlanAdd.UpdateRateGroupCombo($objRateGroup);}";
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
	 *			DBO()->Service->Id			Id of the service that the Override will take place on
	 *			DBO()->RecordType->Id		Id of the RecordType which is being overridden
	 *
	 *		If the RateGroup override is successful then it will fire the EVENT_ON_SERVICE_RATE_GROUPS_UPDATE
	 *		Event passing Service.Id and RecordType.Id
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

		// Overriding RateGroups can not be done while billing is in progress
		if (Invoice_Run::checkTemporary(DBO()->Account->CustomerGroup->Value, DBO()->Account->Id->Value))
		{
			Ajax()->AddCommand("Alert", "This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			return TRUE;
		}
		
		// Load the RecordType Record
		DBO()->RecordType->Load();
		
		// Retrieve all RateGroups matching the RecordType
		DBL()->RateGroup->RecordType = DBO()->RecordType->Id->Value;
		DBL()->RateGroup->Archived = 0;
		DBL()->RateGroup->OrderBy("Name");
		DBL()->RateGroup->Load();
	
		// Handle form submittion
		if (SubmittedForm('RateGroupOverride', 'Apply Override'))
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
			$strChangesNote .= "Start time: ". date("H:i:s d/m/Y", strtotime($strStartTime)) ."\n";
			
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
				$strChangesNote .= "End time: 23:59:59 ". date("d/m/Y", $intEndDate) . "\n";
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

			// Although we should fire an EVENT_ON_NEW_NOTE event, the page where you access this
			// functionality does not list any notes, so it is not required at this stage

			// Fire the EVENT_ON_SERVICE_RATE_GROUPS_UPDATE event
			$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
			$arrEvent['RecordType']['Id'] = DBO()->RecordType->Id->Value;
			Ajax()->FireEvent(EVENT_ON_SERVICE_RATE_GROUPS_UPDATE, $arrEvent);
			
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
	 *			DBO()->RateGroup->Fleet			The Fleet Property of the RateGroup
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
		
		$arrRateGroupColumns = Array("RateGroup Id", "Name", "Description", "Service Type",	"Record Type", "Fleet", "CapLimit (optional)");		

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
												"Allow CDR Hiding",
												"Minimum Charge (\$)",
												"Discount (%) (1.5 = 1.5%) (optional)",
												"Standard Flagfall (\$)",
												"Standard Billing Units",
												"Charge Per Single Unit (\$)",
												"Markup on Cost (\$)",
												"Markup on Cost (%) (1.5 = 1.5%)",
												"Start Capping at (Units)",
												"Start Capping at (\$)",
												"Stop Capping at (Units)",
												"Stop Capping at (\$)",
												"Excess Flagfall (\$)",
												"Excess Billing Units",
												"Charge Per Single Excess Unit (\$)",
												"Excess Markup on Cost (\$)",
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
										DBO()->RateGroup->RecordType->Value,
										DBO()->RateGroup->Fleet->Value,
										DBO()->RateGroup->CapLimit->Value
									);
			
			$arrColumnNames = Array("RateId"					=> "R.Id",
									"Editable"					=> "IF(R.Archived = ". RATE_STATUS_DRAFT .", \"Yes\", \"No\")",
									"DestinationCode"			=> "D.Code",
									"DestinationDescription"	=> "D.Description",
									"RateName"					=> "R.Name",
									"RateDescription"			=> "R.Description",
									"StartTime"					=> "R.StartTime",
									"EndTime"					=> "R.EndTime",
									"Monday"					=> "R.Monday",
									"Tuesday"					=> "R.Tuesday",
									"Wednesday"					=> "R.Wednesday",
									"Thursday"					=> "R.Thursday",
									"Friday"					=> "R.Friday",
									"Saturday"					=> "R.Saturday",
									"Sunday"					=> "R.Sunday",
									"PassThrough"				=> "R.PassThrough",
									"Uncapped"					=> "R.Uncapped",
									"Prorate"					=> "R.Prorate",
									"allow_cdr_hiding"			=> "R.allow_cdr_hiding",
									"StdMinCharge"				=> "R.StdMinCharge",
									"discount_percentage"		=> "R.discount_percentage",
									"StdFlagfall"				=> "R.StdFlagfall",
									"StdUnits"					=> "R.StdUnits",
									"StdRatePerUnit"			=> "R.StdRatePerUnit",
									"StdMarkup"					=> "R.StdMarkup",
									"StdPercentage"				=> "R.StdPercentage",
									"CapUnits"					=> "R.CapUnits",
									"CapCost"					=> "R.CapCost",
									"CapUsage"					=> "R.CapUsage",
									"CapLimit"					=> "R.CapLimit",
									"ExsFlagfall"				=> "R.ExsFlagfall",
									"ExsUnits"					=> "R.ExsUnits",
									"ExsRatePerUnit"			=> "R.ExsRatePerUnit",
									"ExsMarkup"					=> "R.ExsMarkup",
									"ExsPercentage"				=> "R.ExsPercentage");
			
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
			
			$arrBlankRateGroup = Array(NULL,NULL,NULL, DBO()->RecordType->ServiceType->Value, DBO()->RecordType->Id->Value, DBO()->RateGroup->Fleet->Value, NULL);
			
			// Default Values (including precision)
			$arrRate = Array(NULL, "Editable"=>"Yes", "DestinationCode"=>NULL, "DestinationDescription"=>NULL, "RateName"=>"<RateGroupName> - <Destination>", "RateDescription"=>"<RateGroupName> - <Destination>",
								"00:00:00",	"23:59:59", 1, 1, 1, 1, 1, 1,1,
											"PassThrough"			=> 0,
											"Uncapped"				=> 0,
											"Prorate"				=> 0,
											"allow_cdr_hiding"		=> 0,
											"StdMinCharge"			=> "0.0000",
											"discount_percentage"	=> NULL,
											"StdFlagfall"			=> "0.0000",
											"StdUnits"				=> 1,
											"StdRatePerUnit"		=> "0.00000000",
											"StdMarkup"				=> "0.00000000",
											"StdPercentage"			=> "0.0000",
											"CapUnits"				=> 0,
											"CapCost"				=> "0.0000",
											"CapUsage"				=> 0,
											"CapLimit"				=> "0.0000",
											"ExsFlagfall"			=> "0.0000",
											"ExsUnits"				=> 0,
											"ExsRatePerUnit"		=> "0.00000000",
											"ExsMarkup"				=> "0.00000000",
											"ExsPercentage"			=> "0.0000"
										);
										
			$strRateGroupCSV .= MakeCSVLine($arrRateGroupColumns);
			$strRateGroupCSV .= MakeCSVLine($arrBlankRateGroup);
			$strRateGroupCSV .= "\n";
			$strRateGroupCSV .= MakeCSVLine($arrRateColumnNames);	
			
			if (DBO()->RecordType->Context->Value > 0)
			{
				// load the destinations
				DBL()->Destination->Context = DBO()->RecordType->Context->Value;
				DBL()->Destination->OrderBy("Description ASC");
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
		header("Content-Type: text/csv");
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
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		DBO()->RecordType->Load();
		
		$this->LoadPage('rate_group_import');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// ImportCSV
	//------------------------------------------------------------------------//
	/**
	 * ImportCSV()
	 *
	 * Logic for the embedded Import RateGroup page required for uploading the csv file
	 * 
	 * Logic for the embedded Import RateGroup page required for uploading the csv file
	 * This method expects the following values to be defined:
	 *		DBO()->RecordType->Id			RecordType of the RateGroup
	 *		DBO()->RateGroup->Fleet			TRUE if you want to import the RateGroup as a Fleet RateGroup; 
	 *										FALSE for importing normal Rate Groups
	 *		$_FILES['RateGroupCSVFile']		References the RateGroupCSV file uploaded
	 *
	 * @return		void
	 * @method
	 */
	function ImportCSV()
	{
		// Check user authorization and permissions
		//TODO! If the user is not logged in, this will try and load the login page into the iframe
		// for the file upload.  We obviously don't want it to do this 
		//AuthenticatedUser()->CheckAuth();
		//AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		// Check if the form has been submitted
		if (SubmittedForm("ImportRateGroup", "Import as Draft") || SubmittedForm("ImportRateGroup", "Import and Commit"))
		{
			// Check which button was pressed
			$bolCommit = SubmittedForm("ImportRateGroup", "Import and Commit");
			
			DBO()->RecordType->Load();
			$arrRecordType = DBO()->RecordType->AsArray();

			$arrReport = Array();
			$arrRateGroup = $this->_ImportCSV($arrRecordType, DBO()->RateGroup->Fleet->Value, $bolCommit, $arrReport);
			
			// Convert $arrReport into a string and store it
			$strReport = implode("<br />", $arrReport);
			$strReport = str_replace("\n", "<br />", $strReport);
			DBO()->RateGroupImport->Report = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $strReport);

			if ($arrRateGroup === FALSE)
			{
				// The import failed
				DBO()->RateGroupImport->Success = FALSE;
			}
			else
			{
				// The import was successful
				DBO()->RateGroupImport->Success = TRUE;
				$arrRateGroup['Draft'] = ($arrRateGroup['Archived'] == RATE_STATUS_DRAFT)? 1 : 0;
				$arrRateGroup['Fleet'] = ($arrRateGroup['Fleet'])? 1 : 0; 
				DBO()->RateGroupImport->ArrRateGroup = $arrRateGroup;
			}
		}
		
		// Declare the page template
		$this->LoadPage('rate_group_import_component');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _ImportCSV
	//------------------------------------------------------------------------//
	/**
	 * _ImportCSV()
	 *
	 * Parses the uploaded CSV file, validates, saves and reports on the proceedure
	 * 
	 * Parses the uploaded CSV file, validates, saves and reports on the proceedure
	 * This method expects the following values to be defined:
	 *		$_FILES['RateGroupCSVFile']		References the RateGroupCSV file uploaded
	 *
	 * @param	array	$arrRecordType		RecordType database record, of the RateGroup being imported
	 * @param	bool	$bolIsFleet			TRUE if the RateGroup is to be a fleet RateGroup
	 * 										FALSE for normal RateGroups
	 * @param	bool	$bolCommit			TRUE if the RateGroup is to be saved and commited
	 * 										FALSE if the RateGroup is to be saved as a draft
	 * @param	array	&$arrReport			The import report (this will be passed by reference, and 
	 * 										updated appropriately)
	 *
	 * @return	mix							returns $arrRateGroup (defining the imported rate group) on successful importing
	 * 										else returns FALSE if the importing was not successful
	 * @method
	 */
	private function _ImportCSV($arrRecordType, $bolIsFleet, $bolCommit, &$arrReport)
	{
		// Declare the keys for the records stored in the csv file
		$arrRateGroupKeys	= Array("Id", "Name", "Description", "ServiceType", "RecordType", "Fleet", "CapLimit");
		$arrRateKeys		= Array("Id", "Editable", "Destination", "DestinationDescription", "Name", "Description", 
									"StartTime", "EndTime", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
									"Sunday", "PassThrough", "Uncapped", "Prorate", "allow_cdr_hiding", "StdMinCharge", "discount_percentage", "StdFlagfall", "StdUnits",
									"StdRatePerUnit", "StdMarkup", "StdPercentage", "CapUnits", "CapCost", "CapUsage", "CapLimit",
									"ExsFlagfall", "ExsUnits", "ExsRatePerUnit", "ExsMarkup", "ExsPercentage");

		// Stores all the rates belonging to the rate group
		$arrRates	= Array();
		
		// This will store the report regarding how the import went; why it was unsuccessful, etc
		$arrReport	= Array();

		$arrReport[] = ($bolCommit)? "Importing and committing RateGroup\n" : "Importing RateGroup as Draft\n";
		
		// Get a file pointer to the uploaded CSV file which defines the RateGroup
		$arrReport[] = "Opening File: {$_FILES['RateGroupCSVFile']['name']} ...";
		
		$intFileStatus = $_FILES['RateGroupCSVFile']['error'];
		if ($intFileStatus != UPLOAD_ERR_OK)
		{
			// The file was not successfully uploaded
			$arrReport[] = "\nFATAL ERROR: File could not be uploaded.  PHP upload error code: $intFileStatus\nImport Aborted";
			return FALSE;
		}
		
		$ptrFile = fopen($_FILES['RateGroupCSVFile']['tmp_name'], "r");
		if ($ptrFile === FALSE)
		{
			// The file could not be opened
			$arrReport[] = "\nFATAL ERROR: File was successfully uploaded, but could not be opened once uploaded.\nImport Aborted";
			return FALSE;
		}
		$arrReport[] = "\tCSV file successfully opened";
		
		// Read the RateGroup details
		$arrReport[] = "\nReading RateGroup details from file (2nd line)...";
		// Skip the first line of the csv file
		fgetcsv($ptrFile);
		$arrRateGroup = fgetcsv($ptrFile);
		if ($arrRateGroup === FALSE || count($arrRateGroup) < count($arrRateGroupKeys))
		{
			// The RateGroup line could not be read from the file
			$arrReport[] = "\nFATAL ERROR: Could not read the RateGroup details from line 2 of the file.\nImport Aborted";
			return FALSE;
		}
	
		// We only want the first 7 values of the csv record
		$arrRateGroup = array_combine($arrRateGroupKeys, array_slice($arrRateGroup, 0, count($arrRateGroupKeys)));
		$arrReport[] = "\tOk";
	
		// Do preliminary Validation of the RateGroup
		$bolSuccess = $this->_ValidateImportedRateGroupDetails($arrRateGroup, $arrRecordType, $bolIsFleet, $arrReport);
		if (!$bolSuccess)
		{
			// The imported RateGroup failed validation
			$arrReport[] = "\nProblems were encountered.\nImport Aborted";
			return FALSE;
		}
		
		// Read and validate each Rate from the CSV file
		$arrReport[] = "\nReading Rates from file (line 5 onwards)...";
		
		// Skip the third and forth lines of the csv file
		fgetcsv($ptrFile);
		if (fgetcsv($ptrFile) === FALSE)
		{
			// End of file has been met
			$arrReport[] = "\nFATAL ERROR: Could not read any Rates.\nImport Aborted";
			return FALSE;
		}
		
		$intLine = 4;
		while (($arrRate = fgetcsv($ptrFile)) !== FALSE)
		{
			$intLine++;
			
			if (count($arrRate) != count($arrRateKeys))
			{
				$arrReport[] = "\nFATAL ERROR: Could not read the Rate details from line $intLine of the file.\nImport Aborted";
				return FALSE;
			}
			
			// Combine the Rate with appropriate keys
			$arrRate = array_combine($arrRateKeys, $arrRate);
			
			// Update the placeholders in the Rate's Name and Description and trim whitespace
			$arrRate['Name'] = str_replace("<RateGroupName>", $arrRateGroup['Name'], $arrRate['Name']);
			$arrRate['Name'] = str_replace("<Destination>", $arrRate['DestinationDescription'], $arrRate['Name']);
			$arrRate['Name'] = trim($arrRate['Name']);
			
			$arrRate['Description'] = str_replace("<RateGroupName>", $arrRateGroup['Name'], $arrRate['Description']);
			$arrRate['Description'] = str_replace("<Destination>", $arrRate['DestinationDescription'], $arrRate['Description']);
			$arrRate['Description'] = trim($arrRate['Description']);
			
			// Set the RecordType, ServiceType and Fleet
			$arrRate['Fleet'] = $bolIsFleet;
			$arrRate['RecordType'] = $arrRecordType['Id'];
			$arrRate['ServiceType'] = $arrRecordType['ServiceType'];
			
			
			$arrReport[] = "\nValidating Rate: '". $arrRate['Name'] ."' ...";
			
			// Do preliminary validation of the Rate
			$bolSuccess = $this->_ValidateImportedRate($arrRate, $arrRecordType, $arrReport);
			if (!$bolSuccess)
			{
				// The imported Rate failed validation
				$arrReport[] = "\nProblems were encountered.\nImport Aborted";
				return FALSE;
			}

			// Check that this rate does not have the same name as any of the other rates 
			// that have already been read from the csv file (case insensitive)
			foreach ($arrRates as $arrRateFromCSVFile)
			{
				if (strtolower($arrRate['Name']) == strtolower($arrRateFromCSVFile['Name']))
				{
					// A Rate Name conflict has been found
					$arrReport[] = "\tERROR: This Rate's name is being used by another rate declared in this CSV file.\n\nProblems were encountered.\nImport Aborted";
					return FALSE;
				}
			}

			// The Rate passed the initial validation
			// Add the rate to the list of Rates belonging to the RateGroup
			$arrRates[] = $arrRate;
		}
		
		// The Rates all passed their initial validation
		if ($bolIsFleet)
		{
			$arrReport[] = "\nChecking for over-allocations... (fleet RateGroups are permitted under-allocations)";
		}
		else
		{
			$arrReport[] = "\nChecking for Over and Under allocations...";
		}
		
		// Start the transaction
		TransactionStart();
		
		// Save the RateGroup
		$arrRateGroup['Archived'] = ($bolCommit)? RATE_STATUS_ACTIVE : RATE_STATUS_DRAFT;
		if ($arrRateGroup['Id'] != NULL)
		{
			// The RateGroup is currently stored in the database as a draft
			$updRateGroup = new StatementUpdateById("RateGroup");
			
			if ($updRateGroup->Execute($arrRateGroup) === FALSE)
			{
				// Updating the RateGroup record failed
				TransactionRollback();
				$arrReport[] = "\nFATAL ERROR: Updating the RateGroup in the database failed, unexpectedly.\nImport Aborted";
				return FALSE;
			}
		}
		else
		{
			// The RateGroup is new
			$insRateGroup = new StatementInsert("RateGroup");
			$intRateGroupId = $insRateGroup->Execute($arrRateGroup);
			if (!$intRateGroupId)
			{
				// Inserting the RateGroup record failed
				TransactionRollback();
				$arrReport[] = "\nFATAL ERROR: Adding the RateGroup to the database failed, unexpectedly.\nImport Aborted";
				return FALSE;
			}
			$arrRateGroup['Id'] = $intRateGroupId;
		}
		
		// Create the Database objects used to insert/update the Rate records
		$insRate = new StatementInsert("Rate");
		$updRate = new StatementUpdateById("Rate");
		
		// An Array of Rate Ids is required for the Over/under-allocation validation step
		$arrRateIds = Array();
		
		// Save the Rates
		foreach ($arrRates as &$arrRate)
		{
			// Check if the Rate is already in the database
			if ($arrRate['Id'] != NULL)
			{
				// The Rate currently exists in the database
				if ($arrRate['IsCurrentlyActive'])
				{
					// The Rate is currently active and therefor cannot be modified
					// (Nothing to do here)
				}
				else
				{
					// The Rate is stored in the database as a DRAFT rate.  Update it
					$arrRate['Archived'] = ($bolCommit)? RATE_STATUS_ACTIVE : RATE_STATUS_DRAFT;
					
					if ($updRate->Execute($arrRate) === FALSE)
					{
						// Updating the Rate record failed
						TransactionRollback();
						$arrReport[] = "\nFATAL ERROR: Updating the Rate in the database failed, unexpectedly.\nImport Aborted";
						return FALSE;
					}
				}
			}
			else
			{
				// The Rate is new
				$arrRate['Archived'] = ($bolCommit)? RATE_STATUS_ACTIVE : RATE_STATUS_DRAFT;

				$intRateId = $insRate->Execute($arrRate);
				if (!$intRateId)
				{
					// Inserting the Rate record failed
					TransactionRollback();
					$arrReport[] = "\nFATAL ERROR: Adding the Rate '{$arrRate['Name']}' to the database failed, unexpectedly.\nImport Aborted";
					return FALSE;
				}
				// Store the Rate's id
				$arrRate['Id'] = $intRateId;
			}
			$arrRateIds[] = $arrRate['Id'];
		}
		unset($arrRate);
		
		// Validate the RateGroup with respect to over allocations and under allocations
		$this->_BuildRateSummary($arrRecordType['Id'], $arrRateIds);
		if (($this->_arrDestinationRateSummary['OverAllocated']) || ($this->_arrDestinationRateSummary['UnderAllocated'] && (!$bolIsFleet)))
		{
			// The RateGroup is either OverAllocated OR (UnderAllocated AND not a fleet RateGroup) 
			$strAllocationReport = $this->_BuildRateSummaryProblemReport($arrRecordType['Id'], $arrRateIds, $bolIsFleet);
			TransactionRollback();
			$arrReport[] = "\nFATAL ERROR: ". $strAllocationReport ."\nImport Aborted";
			return FALSE;
		}
		
		// The RateGroup is valid
		// Remove all records from the RateGroupRate table where RateGroup == $arrRateGroup['Id']
		$delRateGroupRate = new Query();
		if ($delRateGroupRate->Execute("DELETE FROM RateGroupRate WHERE RateGroup = {$arrRateGroup['Id']}") === FALSE)
		{
			TransactionRollback();
			$arrReport[] = "\nFATAL ERROR: Deleting old records from the RateGroupRate table failed, unexpectedly.\nImport Aborted";
			return FALSE;
		}
				
		// Add a record to the RateGroupRate table for each rate associated with this rategroup
		$insRateGroupRate = new StatementInsert("RateGroupRate");
		$arrInsertValues = Array("RateGroup" => $arrRateGroup['Id']);
		foreach ($arrRateIds as $intRateId)
		{
			$arrInsertValues['Rate'] = $intRateId;
			if (!$insRateGroupRate->Execute($arrInsertValues))
			{
				// Inserting one of the records failed
				TransactionRollback();
				$arrReport[] = "\nFATAL ERROR: Saving a record to the RateGroupRate table of the database failed, unexpectedly.\nImport Aborted";
				return FALSE;
			}
		}
		
		// Commit everything
		TransactionCommit();
		
		// The RateGroup was successfully imported
		$strFinalComment = "\tOk\n\nThe RateGroup, '{$arrRateGroup['Name']}', ";
		if ($arrRateGroup['Archived'] == RATE_STATUS_ACTIVE)
		{
			// The RateGroup has been committed
			$strFinalComment .= ($arrRateGroup['DraftUpdate']) ? "has been updated and committed to the database." : "has been committed to the database."; 
		}
		else
		{
			// The RateGroup has been saved as a draft
			$strFinalComment .= ($arrRateGroup['DraftUpdate']) ? "has been updated in the database." : "has been saved to the database as a draft.";
		}
		
		$arrReport[] = $strFinalComment;
		
		return $arrRateGroup;
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
	 * @param		array	$arrRateGroup			The Rate Group record, pulled from the csv file
	 * 												(this will be passed by reference, and updated appropriately.
	 * 												It will set $arrRateGroup['DraftUpdate'] to TRUE, if the RateGroup
	 * 												was found in the database as a draft)
	 * @param		array	$arrRecordType			RecordType record of the importing RateGroup
	 * @param		bool	$bolIsFleet				TRUE if the RateGroup is supposed to be Fleet, else FALSE
	 * @param		array	&$arrReport				The import report (this will be passed by reference, and 
	 * 												updated appropriately)
	 *
	 * @return		bool	TRUE if the RateGroup is Valid, else FALSE
	 * 						($arrReport will be updated appropriately)
	 *
	 * @method
	 */
	private function _ValidateImportedRateGroupDetails(&$arrRateGroup, $arrRecordType, $bolIsFleet, &$arrReport)	
	{
		$bolFailed = FALSE;
		
		// Type cast the values of $arrRateGroup to their appropriate types
		$arrRateGroup['Id']				= (int)$arrRateGroup['Id'];
		$arrRateGroup['RecordType']		= (int)$arrRateGroup['RecordType'];
		$arrRateGroup['ServiceType']	= (int)$arrRateGroup['ServiceType'];
		$arrRateGroup['Fleet']			= (int)$arrRateGroup['Fleet'];
		$arrRateGroup['CapLimit']		= (strlen($arrRateGroup['CapLimit']) == 0)? NULL : (float)$arrRateGroup['CapLimit'];
		
		// Trim whitespace from the Name and Description
		$arrRateGroup['Name'] = trim($arrRateGroup['Name']);
		$arrRateGroup['Description'] = trim($arrRateGroup['Description']);
		
		$arrRecordType['RecordType'] = $arrRecordType['Id'];
		$arrRecordType['Fleet'] = $bolIsFleet;
		
		// Check if an Id has been specified for the RateGroup
		if ($arrRateGroup['Id'] != NULL)
		{
			// The RateGroup must already be in the database
			// Retrieve the RateGroup from the database
			$arrReport[] = "An Id has been specified for the RateGroup.\nChecking if RateGroup exists in the database as a Draft RateGroup...";
			
			$selRateGroup = new StatementSelect("RateGroup", "Id, Name, Description, ServiceType, RecordType, Fleet, Archived", "Id = <RateGroup>");
			$intNumRecords = $selRateGroup->Execute(Array("RateGroup"=> $arrRateGroup['Id']));
			if (!$intNumRecords)
			{
				// The RateGroup could not be found in the database	
				$arrReport[] = "FATAL ERROR: RateGroup with Id = {$arrRateGroup['Id']} could not be found in the database.";
				return FALSE;
			}
			
			// The RateGroup was successfully retrieved from the database
			$arrExistingRateGroup = $selRateGroup->Fetch();
			if ($arrExistingRateGroup['Archived'] != RATE_STATUS_DRAFT)
			{
				// Only Draft RateGroups can be modified, and this isn't one of them
				$arrReport[] = 	"FATAL ERROR: The RateGroup with Id = {$arrRateGroup['Id']} currently already exists as an ". GetConstantDescription($arrExistingRateGroup['Archived'], "RateStatus") .
								" RateGroup.  It cannot be modified.";
				return FALSE;
			}
			
			// The RateGroup must be a Draft RateGroup
			$arrReport[] = "\tFound as ". $arrExistingRateGroup['Name'];
			$arrRateGroup['DraftUpdate'] = TRUE;

			// Check that the existing RateGroup details match that of the RecordType
			$arrReport[] = "Checking RateGroup from database against RecordType details...";
			$arrDiscrepancies = CompareArrays($arrExistingRateGroup, $arrRecordType, Array("RecordType", "ServiceType", "Fleet"), "\tERROR: <Key> does not match");
			if (count($arrDiscrepancies) > 0)
			{
				$bolFailed = TRUE;
				$arrReport = array_merge($arrReport, $arrDiscrepancies);
			}
			else
			{
				$arrReport[] = "\tOk";
			}
		}
		
		// Check that the RateGroup defined by $arrRateGroup has the same ServiceType and RecordType as that defined in $arrRecordType
		$arrReport[] = "Checking RateGroup details against RecordType details...";
		$arrDiscrepancies = CompareArrays($arrRateGroup, $arrRecordType, Array("RecordType", "ServiceType", "Fleet"), "\tERROR: <Key> does not match");
		if (count($arrDiscrepancies) > 0)
		{
			$bolFailed = TRUE;
			$arrReport = array_merge($arrReport, $arrDiscrepancies);
		}
		else
		{
			$arrReport[] = "\tOk";
		}
		
		
		$arrReport[] = "Checking if RateGroup name is already in use...";
		
		if ($arrRateGroup['Id'] === NULL)
		{
			// The RateGroup is not currently in the database
			// Check that a RateGroup doesn't already exist in the database with the same name and same RecordType
			$strWhere = "RecordType = <RecordType> AND Name LIKE <Name>";
			$arrWhere = Array("RecordType" => $arrRateGroup['RecordType'], "Name" => $arrRateGroup['Name']);
		}
		else
		{
			// The RateGroup is currently in the database as a draft
			// Check that a RateGroup doesn't already exist in the database with the same name and same RecordType unless its Id == $arrRateGroup['Id']
			$strWhere = "RecordType = <RecordType> AND Name LIKE <Name> AND Id != <RateGroupId>";
			$arrWhere = Array("RecordType" => $arrRateGroup['RecordType'], "Name" => $arrRateGroup['Name'], "RateGroupId" => $arrRateGroup['Id']);
		}
		$selRateGroup = new StatementSelect("RateGroup", "Id", $strWhere, "", "1");
		$intNumRecords = $selRateGroup->Execute($arrWhere);
		
		if ($intNumRecords)
		{
			$bolFailed = TRUE;
			$arrReport[] = "FATAL ERROR: The name is currently used by an existing RateGroup";
		}
		else
		{
			$arrReport[] = "\tOk";
		}
		
		return (!$bolFailed);
	}
	
	
	//------------------------------------------------------------------------//
	// _ValidateImportedRate
	//------------------------------------------------------------------------//
	/**
	 * _ValidateImportedRate()
	 *
	 * Validates the Details for an imported Rate
	 * 
	 * Validates the Details for an imported Rate
	 *
	 * @param		array	$arrRate		The Rate record, pulled from the csv file
	 * 										(this is passed by reference, and can be modified by this function)
	 * 										It will set $arrRate['IsCurrentlyActive'] to TRUE if $arrRate
	 * 										makes reference to an active Rate in the database, else
	 * 										this property will not be set
	 * @param		array	$arrRecordType	RecordType record of the importing RateGroup
	 * @param		array	&$arrReport		The import report (this will be passed by reference, and 
	 * 										updated appropriately)
	 *
	 * @return		bool	TRUE if the Rate is Valid, else FALSE
	 * 						($arrReport will be updated appropriately)
	 *
	 * @method
	 */
	private function _ValidateImportedRate(&$arrRate, $arrRecordType, &$arrReport)
	{
		$bolFailed = FALSE;
		
		// This static variable is used to cache the Destination records between calls to this method
		static $arrDestinations;
		
		// This static variable is used to cache the object used to retrieve existing Rates from the database
		static $selExistingRate;
		
		// These StatementSelect objects are used to check if the Rate's name is currently in use
		static $selRateName;
		static $selRateNameOmittingId;
		if (!isset($selRateName))
		{
			$selRateName = new StatementSelect("Rate", "Id", "RecordType = <RecordType> AND Name LIKE <Name>", "", "1");
		}
		if (!isset($selRateNameOmittingId))
		{
			$selRateNameOmittingId = new StatementSelect("Rate", "Id", "RecordType = <RecordType> AND Name LIKE <Name> AND Id != <RateId>", "", "1");
		}
		
		// Check if $arrRate references a Rate that is already in the database
		if ($arrRate['Id'])
		{
			$arrReport[] = "Rate has Id = {$arrRate['Id']}.  Checking if the rate exists in the database...";
			
			// Retrieve the Rate
			if (!isset($selExistingRate))
			{
				$selExistingRate = new StatementSelect("Rate", "Id, RecordType, ServiceType, Fleet, Archived", "Id = <Id>");
			}
			$bolFound = $selExistingRate->Execute(Array("Id" => $arrRate['Id']));
			
			if (!$bolFound)
			{
				// The Rate could not be found in the database	
				$arrReport[] = "\tFATAL ERROR: The Rate could not be found in the database.";
				return FALSE;
			}
			
			// The Rate was found in the database, check that it's not archived
			$arrExistingRate = $selExistingRate->Fetch();
			if ($arrExistingRate['Archived'] == RATE_STATUS_ARCHIVED)
			{
				$arrReport[] = "\tFATAL ERROR: The Rate is currently archived.";
				return FALSE;
			}
			elseif ($arrExistingRate['Archived'] == RATE_STATUS_ACTIVE)
			{
				$arrRate['IsCurrentlyActive'] = TRUE;
				
				$arrReport[] = "\tFound (This Rate is already active and cannot be modified)";
			}
			else
			{
				// Must be stored in the database as a Draft Rate
				$arrReport[] = "\tFound Draft Rate";
			}
			
			// Compare the RecordType and Fleet properties of the existing Rate against those defined by the popup (which are stored in $arrRate)
			$arrReport[] = "Comparing Rate from database against RecordType details...";
			$arrDiscrepancies = CompareArrays($arrExistingRate, $arrRate, Array("RecordType", "ServiceType", "Fleet"), "\tERROR: <Key> does not match");
			if (count($arrDiscrepancies) > 0)
			{
				$arrReport = array_merge($arrReport, $arrDiscrepancies);
				return FALSE;
			}
			else
			{
				$arrReport[] = "\tOk";
			}
			
			// Check if the Rate is already Active (and thus can't be modified)
			if ($arrRate['IsCurrentlyActive'])
			{
				// The rate is considered valid
				return TRUE;
			}
			
			// To have reached this far, the Rate must be a draft rate
		}
		
		// Now validate each value of $arrRate much like the validation of a new Rate in the AddRate popup
		$arrReport[] = "Validating the rate's properties...";
		
		//TODO! I should probably check that the name and description properties are not 
		// longer than 255 chars, otherwise they will be truncated
		
		// Validate StartTime (00:00:00, 00:15:00, ..., 23:45:00)
		$intMidnight = mktime(0, 0, 0);
		$intStartTime = strtotime($arrRate['StartTime']) - $intMidnight;
		if (($intStartTime === FALSE) || ($intStartTime % 900) !== 0)
		{
			// StartTime is not divisable by 15 minutes (900 seconds) and is therfore invalid
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: Valid Start Times are of the form: 00:00:00, 00:15:00, ..., 23:45:00";
		}
		
		// Validate EndTime (00:14:59, 00:29:59, ..., 23:59:59)
		$intEndTime = strtotime($arrRate['EndTime']) - $intMidnight + 1;
		if (($intEndTime == 0) || ($intEndTime % 900) !== 0)
		{
			// EndTime is not valid
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: Valid End Times are of the form: 00:14:59, 00:29:59, ..., 23:59:59";
		}
		
		if ($intStartTime > $intEndTime)
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: Start Time ({$arrRate['StartTime']}) cannot be after End Time ({$arrRate['EndTime']})";
		}
		
		// Validate all properties that can only be set to 0 or 1
		$arrBooleanProperties = Array(	"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
										"Sunday", "PassThrough", "Uncapped", "Prorate");
		foreach ($arrBooleanProperties as $strKey)
		{
			// Convert any NULL value to zero (symbolising false)
			if ($arrRate[$strKey] == NULL)
			{
				$arrRate[$strKey] = 0;
			}
			
			if ((!is_numeric($arrRate[$strKey])) || (($arrRate[$strKey] != 0) && ($arrRate[$strKey] != 1)))
			{
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: $strKey must be either 0 or 1. it equals {$arrRate[$strKey]}";
			}
			
			// Type cast it to an int
			$arrRate[$strKey] = (int)$arrRate[$strKey];
		}

		// Validate all properties that must be integers
		$arrIntProperties = Array("Destination", "StdUnits", "CapUnits", "CapUsage", "ExsUnits");
		foreach ($arrIntProperties as $strKey)
		{
			// Convert any NULL value to zero
			if ($arrRate[$strKey] == NULL)
			{
				$arrRate[$strKey] = 0;
			}
			
			if (!is_numeric($arrRate[$strKey]))
			{
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: $strKey must be an integer";
			}
			
			// The value may still be considered a string.  Type cast it to an integer
			$arrRate[$strKey] = (int)$arrRate[$strKey];
		}
		
		// Validate all properties that must be floats
		$arrFloatProperties = Array(	"StdMinCharge", "StdFlagfall", "StdRatePerUnit", "StdMarkup", "StdPercentage", 
										"CapCost", "CapLimit", "ExsFlagfall", "ExsRatePerUnit", "ExsMarkup", "ExsPercentage");
		foreach ($arrFloatProperties as $strKey)
		{
			// Convert any NULL value to zero
			if ($arrRate[$strKey] == NULL)
			{
				$arrRate[$strKey] = 0.0;
			}
			
			if (!is_numeric($arrRate[$strKey]))
			{
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: $strKey must be numerical";
			}
			
			// The value may still be considered a string.  Type cast it to a float
			$arrRate[$strKey] = (float)$arrRate[$strKey];
		}
		
		// Validate all properties must be floats or NULL
		$arrOptionalFloatProperties = array("discount_percentage");
		foreach ($arrOptionalFloatProperties as $strKey)
		{
			if (strlen($arrRate[$strKey]) == 0)
			{
				// The value is NULL
				$arrRate[$strKey] = NULL;
			}
			elseif (!is_numeric($arrRate[$strKey]))
			{
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: $strKey must be numerical";
			}
			else
			{
				// The value may still be considered a string.  Type cast it to a float
				$arrRate[$strKey] = (float)$arrRate[$strKey];
			}
		}
		
		
		
		// If validation has already failed, do not continue
		if ($bolFailed)
		{
			return FALSE;
		}
		
		// Check that the name is not currently being used by another Rate
		if ($arrRate['Id'] == NULL)
		{
			
			// The Rate is not currently in the database
			// Check that a Rate doesn't already exist in the database with the same name and same RecordType
			$arrWhere = Array("RecordType" => $arrRate['RecordType'], "Name" => $arrRate['Name']);
			$intNumRecords = $selRateName->Execute($arrWhere);
		}
		else
		{
			// Type cast the id to an int (as it might be considered a string)
			if (is_numeric($arrRate['Id']))
			{
				$arrRate['Id'] = (int)$arrRate['Id'];
			}
			else
			{
				// The Id is not an integer
				$arrReport[] = "\tERROR: Id must be an integer";
				return FALSE;
			}

			// The Rate is currently in the database as a draft
			// Check that a RateGroup doesn't already exist in the database with the same name and same RecordType unless its Id == $arrRate['Id']
			$arrWhere = Array("RecordType" => $arrRate['RecordType'], "Name" => $arrRate['Name'], "RateId" => $arrRate['Id']);
			$intNumRecords = $selRateNameOmittingId->Execute($arrWhere);
		}
		
		if ($intNumRecords)
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: This rate's name is currently used by an existing Rate";
		}
		
		// Force an acceptable value for StdUnits, if it doesn't already have one
		if ($arrRate['PassThrough'])
		{
			// StdUnits must be set to zero if PassThrough is set to one (although logically, it should be irrelevent)
			$arrRate['StdUnits'] = 0;
			
			// Prorate cannot be set to 1 if the Rate is a PassThrough Rate
			if ($arrRate['Prorate'])
			{
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: PassThrough rates can not be prorated";
			}
		}
		else
		{
			if ($arrRate['StdUnits'] == 0)
			{
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: Standard Billing Units must be greater than 0 if the Rate is not a PassThrough Rate";
			}
		}
		
		// At most only one of StdRatePerUnit, StdMarkup or StdPercentage, can be greater than 0
		$intCount  = ($arrRate['StdRatePerUnit'])? 1 : 0;
		$intCount += ($arrRate['StdMarkup'])? 1 : 0;
		$intCount += ($arrRate['StdPercentage'])? 1 : 0;
		if ($intCount > 1)
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: Only one of 'Charge Per Single Unit', 'Markup on Cost(\$)' or 'Markup on Cost(%)' can be specified";	
		}
		
		// At most only one of ExsRatePerUnit, ExsMarkup or ExsPercentage, can be greater than 0
		$intCount  = ($arrRate['ExsRatePerUnit'])? 1 : 0;
		$intCount += ($arrRate['ExsMarkup'])? 1 : 0;
		$intCount += ($arrRate['ExsPercentage'])? 1 : 0;
		if ($intCount > 1)
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: Only one of 'Charge Per Single Excess Unit', 'Excess Markup on Cost(\$)' or 'Excess Markup on Cost(%)' can be specified";
		}
		
		//  You cannot specify both a standard markup on cost, and an excess markup on cost
		$intCount  = ($arrRate['StdMarkup'] || $arrRate['StdPercentage'])? 1 : 0;
		$intCount += ($arrRate['ExsMarkup'] || $arrRate['ExsPercentage'])? 1 : 0;
		if ($intCount > 1)
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: You cannot specify both a standard markup on cost, and an excess markup on cost";
		}
		
		// You shouldn't specify both CapUnits and CapCost
		if ($arrRate['CapUnits'] && $arrRate['CapCost'])
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: Start of capping has been specified in both Units and Cost.  Only one of these should be declared";
		}
		
		// You shouldn't specify both CapUsage and CapLimit
		if ($arrRate['CapUsage'] && $arrRate['CapLimit'])
		{
			$bolFailed = TRUE;
			$arrReport[] = "\tERROR: End of capping has been specified in both Units and Cost.  Only one of these should be declared";
		}

		if ($arrRate['ExsUnits'] < 1)
		{
			if ($arrRate['CapUsage'] || $arrRate['CapLimit'])
			{
				// A Cap has been declared, but ExsUnits is less than 1
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: Since a cap has been declared, 'Excess Billing Units' must be greater than 0";
			}
			else
			{
				// No cap has been declared, but ExsUnits is less than 1.  Set it to 1
				// (I think this is a requirement of the Rating app; although I don't know why it needs to be)
				$arrRate['ExsUnits'] = 1;
			}
		}
		
		// Check that the Destination code and destination description are valid for this record type
		// The Destination description doesn't really matter, because the code is used to declare the 
		// destination. I just think it would help the user, to make sure they haven't changed the 
		// destination description, thinking it will do something
		if ($arrRecordType['Context'])
		{
			// The RecordType is Destination based (all imported RateGroups probably will be)
			if (!isset($arrDestinations))
			{
				// Cache the Destination details relating to this RecordType			
				$arrDestinations = Array();
				
				$selDestinations = new StatementSelect("Destination", "Code, Description", "Context = <Context>");
				$selDestinations->Execute(Array("Context" => $arrRecordType['Context']));
				$arrDestinationsTemp = $selDestinations->FetchAll();
				foreach ($arrDestinationsTemp as $arrDestination)
				{
					$arrDestinations[$arrDestination['Code']] = $arrDestination['Description'];
				}
			}
			
			if (!array_key_exists($arrRate['Destination'], $arrDestinations))
			{
				// The destination code declared does not belong to this RecordType
				$bolFailed = TRUE;
				$arrReport[] = "\tERROR: Destination code {$arrRate['Destination']} does not relate to this RecordType";
			}
			
			// Check that the Destination description matches
			if ($arrRate['DestinationDescription'] != $arrDestinations[$arrRate['Destination']])
			{
				$bolFailed = TRUE;
				$arrReport[] =	"\tERROR: Discrepancy found in the destination description.  ".
								"Destination Code {$arrRate['DestinationCode']} has a proper description of '".
								$arrDestinations[$arrRate['Destination']] ."' where as the import file lists it as being '".
								$arrRate['DestinationDescription'] ."'";
			}
		}

		// Everything has been tested now
		if ($bolFailed)
		{
			return FALSE;
		}
		else
		{
			$arrReport[] = "\tOk";
			return TRUE;
		}
	}
	
	
	//------------------------------------------------------------------------//
	// IsValidRateGroup
	//------------------------------------------------------------------------//
	/**
	 * IsValidRateGroup()
	 *
	 * Validates a RateGroup that is already stored in the database 
	 * 
	 * Validates a RateGroup that is already stored in the database
	 * This only checks for over or under-allocations
	 * If a RateGroup is in the database then it should already be valid, however
	 * draft RateGroups can be stored in the database, and then have their draft 
	 * rates change, which would turn the RateGroup invalid
	 *
	 * @param		int		$intRateGroupId		Id of the RateGroup to validate
	 *
	 * @return		bool	TRUE if the RateGroup is valid, else FALSE
	 *
	 * @method
	 */
	function IsValidRateGroup($intRateGroupId)
	{
		// Load the RateGroup
		DBO()->RateGroup->Id = $intRateGroupId;
		DBO()->RateGroup->Load();
		
		// Load the Rates belonging to the RateGroup
		$selRateGroupRates = new StatementSelect("RateGroupRate", "Rate", "RateGroup = $intRateGroupId");
		$intNumRates = $selRateGroupRates->Execute();
		if (!$intNumRates)
		{
			// Either there are no Rates in the RateGroup, or something screwed up
			return FALSE;
		}
		
		// Convert this to a list of Rate Ids
		$arrRateGroupRates = $selRateGroupRates->FetchAll();
		$arrRates = array();
		foreach ($arrRateGroupRates as $arrRate)
		{
			$arrRates[] = $arrRate['Rate']; 
		}
		
		// Check that the selected Rates cover all hours of the week and don't overlap unless they are destination based
		$this->_arrDestinationRates = NULL;
		$this->_BuildRateSummary(DBO()->RateGroup->RecordType->Value, $arrRates);
		if (($this->_arrDestinationRateSummary['OverAllocated']) || ($this->_arrDestinationRateSummary['UnderAllocated'] && !DBO()->RateGroup->Fleet->Value)) 
		{
			return FALSE;
		}
		
		return TRUE;		
	}
	
}


//------------------------------------------------------------------------//
// CompareArrays
//------------------------------------------------------------------------//
/**
 * CompareArrays()
 *
 * Makes a soft comparison against the corresponding elements of the two arrays, and reports on any discrepancies
 * 
 * Makes a soft comparison against the corresponding elements of the two arrays, and reports on any discrepancies
 * This is used by the _ValidateImportedRateGroupDetails() and _ValidateImportedRate() functions
 *
 * @param		array	$arr1					First of the two arrays to compare
 * @param		array	$arr2					Second of the two arrays to compare
 * @param		array	$arrKeys				List of keys of the elements to compare in the two arrays
 * @param		string	$strErrorMsgTemplate	Template for the error msg for when the elements do not match
 * 												This can contain the placeholders <Key>, <Arr1Value> and <Arr2Value>
 *
 * @return		array							A list of Error messages generated
 * @function
 */
function CompareArrays($arr1, $arr2, $arrKeys, $strErrorMsgTemplate)
{
	$arrErrorMsgs = Array();
	foreach ($arrKeys as $mixKey)
	{
		if ($arr1[$mixKey] != $arr2[$mixKey])
		{
			$strErrorMsg = str_replace("<Key>", $mixKey, $strErrorMsgTemplate);
			$strErrorMsg = str_replace("<Arr1Value>", $arr1[$mixKey], $strErrorMsg);
			$strErrorMsg = str_replace("<Arr2Value>", $arr2[$mixKey], $strErrorMsg);
			
			$arrErrorMsgs[] = $strErrorMsg;
		}
	}
	return $arrErrorMsgs;
}
?>