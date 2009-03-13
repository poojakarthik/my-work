<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		rating_application
 * @author		Jared 'flame' Herbohn
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationRating
//----------------------------------------------------------------------------//
/**
 * ApplicationRating
 *
 * Rating Module
 *
 * Rating Module
 *
 *
 * @prefix		app
 *
 * @package		rating_application
 * @class		ApplicationRating
 */
 class ApplicationRating extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// _rptRatingReport
	//------------------------------------------------------------------------//
	/**
	 * _rptRatingReport
	 *
	 * Rating report
	 *
	 * Rating Report, including information on errors, failed ratings,
	 * and a total of each
	 *
	 * @type		Report
	 *
	 * @property
	 */
	private $_rptRatingReport;


	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Rating Application
	 *
	 * Constructor for the Rating Application
	 *
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function __construct($arrConfig=NULL)
 	{
		$this->arrConfig = $arrConfig;
		parent::__construct();

	 	// Initialise framework components
		if ($arrConfig['Reporting'] === TRUE)
		{
			$this->_rptRatingReport = new Report("Rating Report for " . date("Y-m-d H:i:s"), "flame@telcoblue.com.au");

			$this->_rptRatingReport->AddMessage("\n".MSG_HORIZONTAL_RULE.MSG_RATING_TITLE, FALSE);
		}

		// Init Statement

		$ServiceTotalsColumns = Array();
		$ServiceTotalsColumns['UncappedCharge']		= new MySQLFunction("(UncappedCharge + <AddCharge>)");
        $this->_ubiServiceTotalsUncapped    		= new StatementUpdateById("Service", $ServiceTotalsColumns);

		$ServiceTotalsColumns = Array();
		$ServiceTotalsColumns['CappedCharge']		= new MySQLFunction("(CappedCharge + <AddCharge>)");
		$this->_ubiServiceTotalsCapped    			= new StatementUpdateById("Service", $ServiceTotalsColumns);

		$this->_selFleetAccount		= new StatementSelect(	"RateGroup JOIN ServiceRateGroup ON RateGroup.Id = ServiceRateGroup.RateGroup, Service",
															"Service.Account AS Account",
															"ServiceRateGroup.Service = <Service>" .
															" AND RateGroup.RecordType = <RecordType>" .
															" AND RateGroup.ServiceType = <ServiceType>" .
															" AND Service.Id = ServiceRateGroup.Service");

		// not used in actual rating... may not be used at all???
		$strWhere					= "(ISNULL(ClosedOn) OR ClosedOn > <Date>) ";
		$strWhere					.="AND (FNN = <FNN> OR (FNN != <FNN> AND Indial100 = 1 AND FNN LIKE <Prefix>))";
		$this->_selServiceByFNN		= new StatementSelect(	"Service",
															"Id",
															$strWhere, 'CreatedOn DESC, Account DESC', '1');


		/*$strWhere					= "(ISNULL(ClosedOn) OR ClosedOn > <Date>) ";
		$strWhere					.="AND (FNN = <FNN> OR (FNN != <FNN> AND Indial100 = 1 AND FNN LIKE <Prefix>) OR FNN LIKE <SNN>)";
		$this->_selDestinationDetails	=new StatementSelect(	"Service",
																"Id, Account",
																$strWhere, 'CreatedOn DESC, Account DESC', '1');*/
		$strWhere					= "(ISNULL(ClosedOn) OR ClosedOn > <Date>) ";
		$strWhere					.= "AND Account = <Account> ";
		$strWhere					.="AND (FNN = <FNN> OR (FNN != <FNN> AND Indial100 = 1 AND FNN LIKE <Prefix>) OR FNN LIKE <SNN>)";
		$this->_selDestinationDetails	=new StatementSelect(	"Service",
																"Id, Account",
																$strWhere, NULL, '1');

/*
		$strTables					=	"Rate JOIN RateGroupRate ON Rate.Id = RateGroupRate.Rate, " .
										"RateGroup JOIN RateGroupRate AS RateGroupRate2 ON RateGroup.Id = RateGroupRate2.RateGroup, " .
										"ServiceRateGroup JOIN RateGroup AS RateGroup2 ON ServiceRateGroup.RateGroup = RateGroup2.Id";

		$strWhere					=	"RateGroupRate.Id 				= RateGroupRate2.Id AND \n" .
										"RateGroup.Id 					= RateGroup2.Id AND \n" .
										//"ServiceRateGroup.Service 		= <Service> AND \n" .
										//"ServiceRateGroup.StartDateTime	<= <DateTime> AND \n" .
										//"ServiceRateGroup.EndDateTime	>= <DateTime> AND \n" .
										"Rate.RecordType				= <RecordType> AND \n" .
										"Rate.Destination 				= <Destination> AND \n" .
										"Rate.StartTime					<= <Time> AND \n" .
										"Rate.EndTime 					>= <Time> AND \n" .
										"( Rate.Monday					= <Monday> OR \n" .
										"Rate.Tuesday					= <Tuesday> OR \n" .
										"Rate.Wednesday					= <Wednesday> OR \n" .
										"Rate.Thursday					= <Thursday> OR \n" .
										"Rate.Friday					= <Friday> OR \n" .
										"Rate.Saturday					= <Saturday> OR \n" .
										"Rate.Sunday					= <Sunday> ) \n";
*/

		// Init Rate finding (aka Dirty Huge Donkey) Query
		/*$strTables					=	"Rate JOIN RateGroupRate ON Rate.Id = RateGroupRate.Rate, " .
										"RateGroup JOIN ServiceRateGroup ON RateGroup.Id = ServiceRateGroup.RateGroup";

		$strWhere					=	"RateGroup.Id 					= RateGroupRate.RateGroup AND \n" .
										"Rate.RecordType				= <RecordType> AND \n" .
										"Rate.Destination 				= <Destination> AND \n" .
										"( Rate.Monday					= <Monday> OR \n" .
										"Rate.Tuesday					= <Tuesday> OR \n" .
										"Rate.Wednesday					= <Wednesday> OR \n" .
										"Rate.Thursday					= <Thursday> OR \n" .
										"Rate.Friday					= <Friday> OR \n" .
										"Rate.Saturday					= <Saturday> OR \n" .
										"Rate.Sunday					= <Sunday> ) AND \n" .
										"<Time> BETWEEN Rate.StartTime AND Rate.EndTime AND\n" .
										"ServiceRateGroup.Id = \n" .
										"	(	SELECT ServiceRateGroupSub.Id \n " .
										"		FROM ServiceRateGroup ServiceRateGroupSub \n " .
										"		WHERE ServiceRateGroupSub.Service = <Service> AND \n" .
										"		<DateTime> BETWEEN ServiceRateGroupSub.StartDatetime AND ServiceRateGroupSub.EndDatetime \n" .
										"		ORDER BY ServiceRateGroupSub.CreatedOn DESC \n " .
										"		LIMIT 1 )";*/

		$strTables					=	"ServiceRateGroup JOIN RateGroup ON ServiceRateGroup.RateGroup = RateGroup.Id, " .
										"RateGroupRate JOIN Rate ON RateGroupRate.Rate = Rate.Id";

		$strWhere					=	"RateGroup.Id					= RateGroupRate.RateGroup AND \n" .
										"( Rate.Monday					= <Monday> OR \n" .
										"Rate.Tuesday					= <Tuesday> OR \n" .
										"Rate.Wednesday					= <Wednesday> OR \n" .
										"Rate.Thursday					= <Thursday> OR \n" .
										"Rate.Friday					= <Friday> OR \n" .
										"Rate.Saturday					= <Saturday> OR \n" .
										"Rate.Sunday					= <Sunday> ) AND \n" .
										"<Time> BETWEEN Rate.StartTime AND Rate.EndTime AND \n" .
										"ServiceRateGroup.Service = <Service> AND \n";

		$strStandardWhere			= 	"<DateTime> BETWEEN ServiceRateGroup.StartDatetime AND ServiceRateGroup.EndDatetime\n";
		$strOldCDRWhere				=	"<DateTime> < ServiceRateGroup.StartDatetime\n";

		//FAKE : for testing only
		//$strTables = "Rate";
		//$strWhere  = "1 = 1";
		//$this->_selFindRate			= new StatementSelect($strTables, "Rate.*", $strWhere, "", 1);
		$strMyWhere					=	" AND Rate.RecordType				= <RecordType>";
		$strMyWhere					.=	" AND Rate.Destination 				= <Destination>";
		$strMyWhere					.=	" AND Rate.Fleet 				= 0 \n";
		$this->_selFindRate			= new StatementSelect($strTables, "Rate.*", $strWhere.$strStandardWhere.$strMyWhere, "ServiceRateGroup.CreatedOn DESC, ServiceRateGroup.Id DESC", 1);

		// rate query if CDR is older than oldest RateGroup
		$this->_selFindLastRate		= new StatementSelect($strTables, "Rate.*", $strWhere.$strOldCDRWhere.$strMyWhere, "ServiceRateGroup.CreatedOn ASC, ServiceRateGroup.Id ASC", 1);

		// fleet rate query
		//TODO!flame! this is broken and fleet rates sms... needs to have RecordType and Destination ?? will that work?
		$strMyWhere					 =	" AND Rate.RecordType				= <RecordType>";
		$strMyWhere					.=	" AND Rate.Destination 				= <Destination>";
		$strMyWhere					.=	" AND Rate.Fleet 				= 1 \n";
		$this->_selFindFleetRate	= new StatementSelect($strTables, "Rate.*", $strWhere.$strStandardWhere.$strMyWhere, "ServiceRateGroup.CreatedOn DESC, ServiceRateGroup.Id DESC", 1);

		$strMyWhere					=	" AND Rate.Fleet 				= 1 \n";
		$this->_selFindDestFleetRate	= new StatementSelect($strTables, "Rate.*", $strWhere.$strStandardWhere.$strMyWhere, "ServiceRateGroup.CreatedOn DESC, ServiceRateGroup.Id DESC", 1);


		// Update CDR Query
		$arrDefine = Array();
		$arrDefine['Rate']		= TRUE;
		$arrDefine['Status']	= TRUE;
		$arrDefine['Charge']	= TRUE;
		$arrDefine['RatedOn']	= new MySQLFunction("NOW()");
		$this->_updUpdateCDRs	= new StatementUpdateById("CDR", $arrDefine);

		// Cost & Charge totals
		$this->_fltTotalCost	= 0;
		$this->_fltTotalCharge	= 0;

		$arrCols = Array();
		$arrCols['Id']			= NULL;
		$arrCols['EarliestCDR']	= NULL;
		$arrCols['LatestCDR']	= NULL;
		$this->_selService		= new StatementSelect("Service", $arrCols, "Id = <Service>");
		$this->_ubiService		= new StatementUpdateById("Service", $arrCols);

		// New Rating Query
		$strWhere							=	"( Rate.Monday	= <Monday> OR \n" .
												"Rate.Tuesday	= <Tuesday> OR \n" .
												"Rate.Wednesday	= <Wednesday> OR \n" .
												"Rate.Thursday	= <Thursday> OR \n" .
												"Rate.Friday	= <Friday> OR \n" .
												"Rate.Saturday	= <Saturday> OR \n" .
												"Rate.Sunday	= <Sunday> ) AND \n" .
												"<Time> BETWEEN Rate.StartTime AND Rate.EndTime AND \n" .
												"ServiceRateGroup.Service = <Service> AND \n" .
												"Rate.Fleet = <Fleet> AND \n" .
												"Destination = <Destination> AND \n" .
												"(Rate.RecordType = <RecordType> OR <RecordType> = ".DONKEY.") AND \n" .
												"(<StartDatetime> BETWEEN ServiceRateGroup.StartDatetime AND ServiceRateGroup.EndDatetime OR <ClosestRate> = 1)";

		$this->_selRate	= new StatementSelect(	"((ServiceRateGroup JOIN RateGroup ON RateGroup.Id = ServiceRateGroup.RateGroup) JOIN RateGroupRate ON RateGroupRate.RateGroup = RateGroup.Id) JOIN Rate ON Rate.Id = RateGroupRate.Rate",
												"Rate.*, ServiceRateGroup.StartDatetime, ServiceRateGroup.EndDatetime",
												$strWhere,
												"(RateGroup.Fleet = Rate.Fleet) DESC, ServiceRateGroup.StartDatetime DESC");

		$this->_selCDRTotalDetails	= new StatementSelect("Service LEFT JOIN CDR ON (Service.Id = CDR.Service AND CDR.Credit = 0 AND CDR.Status = 150)", "cdr_count, cdr_amount, discount_start_datetime, COUNT(CDR.Id) AS new_cdr_count, SUM(CDR.Charge) AS new_cdr_amount", "Service.Id = <Service>", NULL, NULL, "Service.Id");

		$arrCols			= Array();
		$arrCols['Status']	= CDR_RERATE;
		$this->_updReRateService	= new StatementUpdate("CDR", "Service = <Service>", $arrCols);

		$this->_selRatePlan			= new StatementSelect(	"ServiceRatePlan JOIN RatePlan ON RatePlan.Id = ServiceRatePlan.RatePlan",
															"RatePlan.*",
															"Service = <Service> AND <StartDatetime> BETWEEN StartDatetime AND EndDatetime",
															"ServiceRatePlan.CreatedOn DESC",
															"1");

		$arrCols	= Array();
		$arrCols['Status']				= CDR_RERATE;
		$this->_updReRateDiscountCDRs	= new StatementUpdate(	"CDR",
														"Service = <Service> AND Status = 150 AND StartDatetime >= <StartDatetime>",
														$arrCols);

		$arrService	= Array();
		$arrService['discount_start_datetime']	= NULL;
		$this->_ubiDiscountStartDatetime	= new StatementUpdateById("Service", $arrService);
 	}

	//------------------------------------------------------------------------//
	// RateCDR
	//------------------------------------------------------------------------//
	/**
	 * RateCDR()
	 *
	 * Rate a single CDR Record
	 *
	 * Rate a single CDR Record
	 *
	 * @param	array	$arrCDR			CDR Array from database
	 * @param	bool	$bolReturnCDR	optional Return the whole CDR instead of just the rated amount
	 * @return	float	Rated Charge
	 * @method
	 */
	function RateCDR($mixCDR, $bolReturnCDR = FALSE)
	{
		if (is_array($mixCDR))
		{
			$arrCDR = $mixCDR;
		}
		elseif ((int)$mixCDR)
		{
			$intCDR = $mixCDR;
			// Get the CDR
			$selCDR = new StatementSelect("CDR", "*", "CDR.Id = <Id>");
			if (!$selCDR->Execute(Array('Id' => $intCDR)))
			{
				return FALSE;
			}
			$arrCDR = $selCDR->Fetch();
		}
		else
		{
			return FALSE;
		}

		$arrCDR['Cost'] 	= (float)$arrCDR['Cost'];
		$arrCDR['Charge'] 	= (float)$arrCDR['Charge'];

		//Debug($arrCDR);

		// Does this call qualify for a discount?
		$arrCDRTotalDetails	= Array();
		$bolDiscount		= FALSE;
		if (!$this->_selCDRTotalDetails->Execute($this->_arrCurrentCDR))
		{
			// Error
			CliEcho("Service {$this->_arrCurrentCDR['Service']} not found!");
			return FALSE;
		}
		else
		{
			$arrCDRTotalDetails	= $this->_selCDRTotalDetails->Fetch();

			// Is the current CDR older than the latest CDR in the cap?
			if ($arrCDRTotalDetails['discount_start_datetime'] !== NULL && strtotime($arrCDRTotalDetails['discount_start_datetime']) > strtotime($this->_arrCurrentCDR['StartDatetime']))
			{
				// Because this is only for debug, we can just assume that this CDR would be in the Cap, and therefore not discounted
				$bolDiscount	= FALSE;
			}
			else
			{
				$bolDiscount	= TRUE;
			}
		}

		// set current CDR
		$this->_arrCurrentCDR = $arrCDR;

		// Find Rate for this CDR
		if (!$this->_arrCurrentRate = $this->_FindRateNew())
		{
			$this->_Debug("No rate found!");
			return FALSE;
		}

		if ($this->_arrCurrentRate['PassThrough'])
		{
			// Calculate Passthrough rate
			$fltCharge = $this->_CalculatePassThrough();
		}
		else
		{
			// Calculate other rate types

			// Calculate Charge
			$fltCharge = $this->_CalculateCharge();
			if ($fltCharge === FALSE)
			{
				//Debug("_CalculateCharge() Failed!");
				return FALSE;
			}

			// Calculate Cap Rate
			$fltCharge = $this->_CalculateCap();
			if ($fltCharge === FALSE)
			{
				//Debug("_CalculateCap() Failed!");
				return FALSE;
			}

			// Calculate Prorate
			$fltCharge = $this->_CalculateProrate();
			if ($fltCharge === FALSE)
			{
				//Debug("_CalculateProrate() Failed!");
				return FALSE;
			}

			// Rounding
			$this->_Rounding();
		}

		// Has the Service reached its cap?
		$this->_selRatePlan->Execute($this->_arrCurrentCDR);
		$arrRatePlan	= $this->_selRatePlan->Fetch();
		if ($bolDiscount && ($arrRatePlan['discount_cap'] !== NULL))
		{
			if (((float)$arrCDRTotalDetails['cdr_amount_new']) >= ((float)$arrRatePlan['discount_cap']))
			{
				// Apply the discount
				if ($this->_arrCurrentRate['discount_percentage'])
				{
					$dp				= (float)$this->_arrCurrentRate['discount_percentage'];
					$fltDiscount	= $fltCharge * $dp;
					$fltCharge		= $fltCharge - $$fltDiscount;
				}
			}
		}

		if ($bolReturnCDR)
		{
			$this->_arrCurrentCDR['Rate'] = $this->_arrCurrentRate['Id'];
			return $this->_arrCurrentCDR;
		}
		else
		{
			return $this->_arrCurrentCDR['Charge'];
		}
	}

 	//------------------------------------------------------------------------//
	// Rate
	//------------------------------------------------------------------------//
	/**
	 * Rate()
	 *
	 * Rate CDR Records
	 *
	 * Rates CDR Records
	 * Rates the next batch of 1000 normalised ready to rate CDRs in the database
	 *
	 * @param	bool	$bolOnlyNew		optional	Only Rate new CDRs [FALSE]
	 *
	 * @return	bool	returns true untill all CDRs have been rated
	 * @method
	 */
	 function Rate($bolOnlyNew = FALSE, $intLimit = 1000)
	 {
		// Is there a Bill Run active?
		if (isInvoicing())
		{
			CliEcho("WARNING: Rating will not run while there is a Temporary Invoice Run active!");
			return FALSE;
		}

		$strWhere = "Status = ".CDR_NORMALISED;
		if (!$bolOnlyNew)
		{
			$strWhere .= " OR Status = ".CDR_RERATE;
		}

		// Select CDR Query
		$arrColumns = $this->db->FetchClean("CDR");
		unset($arrColumns['CDR']);
		unset($arrColumns['Description']);
		unset($arrColumns['CarrierRef']);
		unset($arrColumns['File']);
		unset($arrColumns['Carrier']);
		unset($arrColumns['NormalisedOn']);
		unset($arrColumns['SequenceNo']);
		$arrColumns['Id'] = 0;
		//$this->_selGetCDRs = new StatementSelect("CDR", $arrColumns, "Status = ".CDR_NORMALISED." OR Status = ".CDR_RERATE, "Status ASC", "1000");
		$this->_selGetCDRs = new StatementSelect("CDR", $arrColumns, $strWhere, "StartDatetime ASC", $intLimit);

	 	// get list of CDRs to rate (limit results to 1000)
	 	$intCDRCount	= $this->_selGetCDRs->Execute();
		$arrCDRList 	= $this->_selGetCDRs->FetchAll();

		// we will return FALSE if there are no CDRs to rate
		$bolReturn = FALSE;

		// set up the update query
		$arrDefine = Array();
		$arrDefine['Rate'] = TRUE;
		$arrDefine['Status'] = TRUE;
		$arrDefine['Charge'] = TRUE;
		$arrDefine['RatedOn'] = new MySQLFunction("NOW()");
		$updSaveCDR = new StatementUpdateById("CDR", $arrDefine);

		$this->Framework->StartWatch();

		$intPassed = 0;
		$intFailed = 0;

		// Loop through each CDR
		$intTotalTime	= 0;
		$intSplit		= 0;
		$intCurrentTime	= time();
		$intCDR			= 0;
		foreach($arrCDRList as $arrCDR)
		{
			// return TRUE if we have rated (or tried to rate) any CDRs
			$bolReturn = TRUE;

			// cast MySQL strings to floats so they don't break our shit
			$arrCDR['Cost'] 	= (float)$arrCDR['Cost'];
			$arrCDR['Charge'] 	= (float)$arrCDR['Charge'];

			/*$this->_selService->Execute($arrCDR);
			$arrService	= $this->_selService->Fetch($arrCDR);

			$intEarliest	= strtotime($arrService['EarliestCDR']);
			$intLatest		= strtotime($arrService['LatestCDR']);
			$intCDR			= strtotime($arrCDR['StartDatetime']);

			if (($intCDR < $intEarliest && $intCDR) || !$arrService['EarliestCDR'])
			{
				$arrService['EarliestCDR']	= $arrCDR['StartDatetime'];
			}
			if (($intCDR > $intLatest && $intCDR) || !$arrService['LatestCDR'])
			{
				$arrService['LatestCDR']	= $arrCDR['StartDatetime'];
			}

			$this->_ubiService->Execute($arrService);*/

			// Report
			$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
			$this->_rptRatingReport->AddMessageVariables(MSG_LINE, $arrAlises, FALSE);
			$intCDR++;
			CliEcho(" ($intCDR/$intCDRCount)");


			// Set Service Earliest/Latest CDR
			$qryEarliestLatestCDR	= new Query();
			$strEarliestLatestCDR	=	"UPDATE Service \n " .
										"SET EarliestCDR = IF(EarliestCDR > '{$arrCDR['StartDatetime']}' OR ISNULL(EarliestCDR), '{$arrCDR['StartDatetime']}', EarliestCDR), " .
										"LatestCDR = IF(LatestCDR < '{$arrCDR['StartDatetime']}' OR ISNULL(LatestCDR), '{$arrCDR['StartDatetime']}', LatestCDR) \n " .
										"WHERE Id = {$arrCDR['Service']}";
			if ($qryEarliestLatestCDR->Execute($strEarliestLatestCDR) === FALSE)
			{
				// ERROR
				CliEcho("\n WARNING -- Unable to updated Service Earliest & Latest CDR fields! (Service: {$arrCDR['Service']}; CDR: {$arrCDR['Id']})");
			}

			// set current CDR
			$this->_arrCurrentCDR = $arrCDR;

			// Find Rate for this CDR
			if (!$this->_arrCurrentRate = $this->_FindRateNew())
			{
				// rate not found
				// set status in database
				$arrCDR['Status']	= CDR_RATE_NOT_FOUND;
				$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
				$this->_updUpdateCDRs->Execute($arrCDR);

				// add to report
				$arrAlises['<Reason>'] = "Rate not found";
				$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
				$this->_rptRatingReport->AddMessageVariables(MSG_LINE.MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);

				$intFailed++;
				continue;
			}

			// Does this call qualify for a discount?
			$bolDiscount	= FALSE;
			$arrCDRTotalDetails	= Array();
			if ($this->_selRatePlan->Execute($this->_arrCurrentCDR))
			{
				$arrRatePlan	= $this->_selRatePlan->Fetch();

				// Does the RatePlan have Discounting enabled?
				if ((float)$arrRatePlan['discount_cap'] >= 0.01)
				{
					if (!$this->_selCDRTotalDetails->Execute($this->_arrCurrentCDR))
					{
						// Error
						CliEcho("Service {$this->_arrCurrentCDR['Service']} not found!");
						$intFailed++;
						continue;
					}
					else
					{
						$arrCDRTotalDetails	= $this->_selCDRTotalDetails->Fetch();

						// Is the current CDR older than the latest CDR in the cap?
						if ($arrCDRTotalDetails['discount_start_datetime'] !== NULL && strtotime($arrCDRTotalDetails['discount_start_datetime']) > strtotime($this->_arrCurrentCDR['StartDatetime']))
						{
							// Hold up, we have a CDR that is older than the last CDR in the Discount Cap
							// This means we need to rerate every CDR on or after this StartDatetime (including this CDR)
							$arrCols					= Array();
							$arrCols['Status']			= CDR_RERATE;
							$arrWhere					= Array();
							$arrWhere['StartDatetime']	= $this->_arrCurrentCDR['StartDatetime'];
							$arrWhere['Service']		= $this->_arrCurrentCDR['Service'];
							if ($this->_updReRateDiscountCDRs->Execute($arrCols, $arrWhere) === FALSE)
							{
								// Error
								CliEcho("\$updReRateDiscountCDRs failed: ".$updReRateDiscountCDRs->Error());
								$intFailed++;
								continue;
							}

							// We also need to update the Service to reflect the new discount_start_datetime
							$arrService	= Array();
							$arrService['Id']						= $this->_arrCurrentCDR['Service'];
							$arrService['discount_start_datetime']	= $this->_arrCurrentCDR['StartDatetime'];
							if ($this->_ubiDiscountStartDatetime->Execute($arrService) === FALSE)
							{
								// Error
								CliEcho("\$ubiDiscountStartDatetime failed: ".$this->_ubiDiscountStartDatetime->Error());
								$intFailed++;
								continue;
							}

							CliEcho("Service {$this->_arrCurrentCDR['Service']} is being rerated from {$this->_arrCurrentCDR['StartDatetime']}");
							break;
						}
						else
						{
							// Nothing needs to be rerated
							$bolDiscount	= TRUE;
						}
					}
				}
			}

			// Calculate Charge from Rate
			if ($this->_arrCurrentRate['PassThrough'])
			{
				// Calculate Passthrough rate
				$fltCharge = $this->_CalculatePassThrough();
			}
			else
			{
				// Calculate other rate types

				// Calculate Charge
				$fltCharge = $this->_CalculateCharge();
				if ($fltCharge === FALSE)
				{
					// Charge calculation failed
					// THIS SHOULD NEVER HAPPEN

					$arrCDR['Status'] = CDR_UNABLE_TO_RATE;
					$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
					$this->_updUpdateCDRs->Execute($arrCDR);

					// add to report
					$arrAlises['<Reason>'] = "Base charge calculation failed";
					$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
					$this->_rptRatingReport->AddMessageVariables(MSG_LINE.MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);

					$intFailed++;
					continue;
				}

				// Calculate Cap Rate
				$fltCharge = $this->_CalculateCap();
				if ($fltCharge === FALSE)
				{
					// Charge calculation failed
					// THIS SHOULD NEVER HAPPEN

					$arrCDR['Status'] = CDR_UNABLE_TO_CAP;
					$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
					$this->_updUpdateCDRs->Execute($arrCDR);

					// add to report
					$arrAlises['<Reason>'] = "Unable to cap CDR";
					$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
					$this->_rptRatingReport->AddMessageVariables(MSG_LINE.MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);

					$intFailed++;
					continue;
				}

				// Calculate Prorate
				$fltCharge = $this->_CalculateProrate();
				if ($fltCharge === FALSE)
				{
					// Charge calculation failed
					// THIS SHOULD NEVER HAPPEN

					$arrCDR['Status'] = CDR_UNABLE_TO_PRORATE;
					$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
					$this->_updUpdateCDRs->Execute($arrCDR);

					// add to report
					$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
					$arrAlises['<Reason>'] = "ProRating failed";
					$this->_rptRatingReport->AddMessageVariables(MSG_LINE.MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);

					$intFailed++;
					continue;
				}
			}

			// Has the Service reached its cap?
			if ($bolDiscount && ($arrRatePlan['discount_cap'] !== NULL))
			{
				if (((float)$arrCDRTotalDetails['cdr_amount_new']) >= ((float)$arrRatePlan['discount_cap']))
				{
					// Apply the discount
					if ($this->_arrCurrentRate['discount_percentage'])
					{
						$dp				= (float)$this->_arrCurrentRate['discount_percentage'];
						$fltDiscount	= $fltCharge * $dp;
						$fltCharge		= $fltCharge - $$fltDiscount;
					}
				}
				else
				{
					// Update the Service to reflect the lastest CDR in the discount cap
					$arrService	= Array();
					$arrService['Id']						= $this->_arrCurrentCDR['Service'];
					$arrService['discount_start_datetime']	= $this->_arrCurrentCDR['StartDatetime'];
					if ($this->_ubiDiscountStartDatetime->Execute($arrService) === FALSE)
					{
						// Error
						CliEcho("\$ubiDiscountStartDatetime failed: ".$this->_ubiDiscountStartDatetime->Error());
						$intFailed++;
						continue;
					}
				}
			}

			// Rounding
			$fltCharge = $this->_Rounding();

			// Update Service & Account Totals
			$mixResult = $this->_UpdateTotals($arrCDR['Service']);
			if ($mixResult === FALSE)
			{
				// problem updating totals
				// set status in database
				$arrCDR['Status'] = CDR_TOTALS_UPDATE_FAILED;
				$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
				$this->_updUpdateCDRs->Execute($arrCDR);

				// add to report
				$arrAlises['<Reason>'] = "Totals updating failed";
				$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
				$this->_rptRatingReport->AddMessageVariables(MSG_LINE.MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);

				$intFailed++;
				continue;
			}
			elseif (!$mixResult)
			{
				// add to report
				$arrAlises['<Reason>'] = "Totals didn't change";
				$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
				$this->_rptRatingReport->AddMessageVariables(MSG_LINE.MSG_IGNORE.MSG_FAIL_LINE, $arrAlises, FALSE);
			}

			// Check for overlimit accounts
			// Check if an account is over its limit and do something if it is
			// implement this some time in the future

			// Report
			//$this->_rptRatingReport->AddMessage(MSG_OK, FALSE);

			// save CDR back to database
			$arrCDR['Rate'] = $this->_arrCurrentRate['Id'];
			$arrCDR['Charge'] = $this->_arrCurrentCDR['Charge'];
			$arrCDR['Status'] = CDR_RATED;
			$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
			$this->_updUpdateCDRs->Execute($arrCDR);
			$intPassed++;

			// Add to Cost/Charge Totals
			$this->_fltTotalCost	+= $this->_arrCurrentCDR['Cost'];
			$this->_fltTotalCharge	+= $this->_arrCurrentCDR['Charge'];
			$this->_intTotalRated++;
		}

		// Report footer
		$arrAliases['<Total>']	= $intFailed + $intPassed;
		$arrAliases['<Time>']	= $this->Framework->SplitWatch();
		$arrAliases['<Pass>']	= $intPassed;
		$arrAliases['<Fail>']	= $intFailed;
		$this->_rptRatingReport->AddMessageVariables("\n".MSG_HORIZONTAL_RULE.MSG_REPORT, $arrAliases, FALSE);

		// Deliver the report
		$this->_rptRatingReport->Finish();

		// Return Number of CDRs rated or FALSE
		return ($intCDR > 0) ? $intCDR : FALSE;
	 }

	//------------------------------------------------------------------------//
	// DeRate
	//------------------------------------------------------------------------//
	/**
	 * DeRate()
	 *
	 * DeRate CDR Records
	 *
	 * DeRate CDR Records
	 * DeRate un-invoiced CDRs
	 *
	 * @param	mixed	$mixWhere	array to be used as a WHERE clause when selecting
	 *								CDRs to derate. Most usefull would be;
	 *								$arrWhere['Account']	Id of account
	 *								$arrWhere['Service']	Id of service
	 *								$arrWhere['File']		Id of file
	 *								can also be a string WHERE clause
	 *
	 * @return	int		number of records derated
	 * @method
	 */
	 function DeRate($mixWhere)
	 {

		$strWhere = $this->_selFindRate->PrepareWhere($mixWhere);

		// don't derate invoiced or temp invoiced CDRs
		if ($strWhere)
		{
			$strWhere .= " AND Status <> ".CDR_INVOICED." AND Status <> ".CDR_TEMP_INVOICE." ";
		}
		else
		{
			$strWhere .= " Status <> ".CDR_INVOICED." AND Status <> ".CDR_TEMP_INVOICE." ";
		}

		// set up derating database object
		//TODO!!!!
		// $updDeRate = new ....

		// run the update query
		//TODO!!!!
		//$updDeRate->Execute($arrData, $arrWhere);

		// return the number of affected Rows
		// TODO!!!!
	 }

	//------------------------------------------------------------------------//
	// _FindRate
	//------------------------------------------------------------------------//
	/**
	 * _FindRate()
	 *
	 * Find the appropriate rate for the current CDR
	 *
	 * Find the appropriate rate for the current CDR
	 *
	 * @return	mixed	array	rate details
	 * 					bool	FALSE if rate not found
	 * @method
	 */
	 private function _FindRate()
	 {
	 	$bolFleet = FALSE;

		$arrAliases = Array();

	 	// Set up the rate-finding query
		$arrAliases['Destination']	= $this->_arrCurrentCDR['DestinationCode'];
		if (!$this->_arrCurrentCDR['DestinationCode'])
		{
			// TODO!!!! - Context
			$arrAliases['Destination']	= 0;
		}
	 	$arrAliases['DateTime']		= $this->_arrCurrentCDR['StartDatetime'];
	 	$intTime					= strtotime($this->_arrCurrentCDR['StartDatetime']);
	 	$arrAliases['Time']			= date("H:i:s", $intTime);
	 	$strDay						= date("l", $intTime);
	 	$arrAliases['Monday']		= ($strDay == "Monday") ? TRUE : DONKEY;
	 	$arrAliases['Tuesday']		= ($strDay == "Tuesday") ? TRUE : DONKEY;
	 	$arrAliases['Wednesday']	= ($strDay == "Wednesday") ? TRUE : DONKEY;
	 	$arrAliases['Thursday']		= ($strDay == "Thursday") ? TRUE : DONKEY;
	 	$arrAliases['Friday']		= ($strDay == "Friday") ? TRUE : DONKEY;
	 	$arrAliases['Saturday']		= ($strDay == "Saturday") ? TRUE : DONKEY;
	 	$arrAliases['Sunday']		= ($strDay == "Sunday") ? TRUE : DONKEY;
		$arrAliases['RecordType']	= $this->_arrCurrentCDR['RecordType'];

		//Debug($arrAliases);

		// find destination account & Service
		$arrWhere['Prefix']			= substr($this->_arrCurrentCDR['Destination'], 0, -2).'__';
		$arrWhere['SNN']			= '0_'.substr($this->_arrCurrentCDR['Destination'], -8);
		$arrWhere['FNN']			= $this->_arrCurrentCDR['Destination'];
		$arrWhere['Date']			= $this->_arrCurrentCDR['StartDatetime'];
		$arrWhere['Account']		= $this->_arrCurrentCDR['Account'];
//Debug($arrWhere['FNN']);
//Debug($arrWhere['SNN']);
		$this->_selDestinationDetails->Execute($arrWhere);
		$arrDestinationDetails 		= $this->_selDestinationDetails->Fetch();
		$intDestinationAccount 		= $arrDestinationDetails['Account'];
		$intDestinationService 		= $arrDestinationDetails['Id'];
//Debug($intDestinationAccount);
//Debug($this->_arrCurrentCDR['Account']);
		// is the destination service on the same account
		if ($intDestinationAccount && $intDestinationAccount == $this->_arrCurrentCDR['Account'])
		{
			// does the destination have a fleet rate
		 	$arrAliases['Service']	= $intDestinationService;
			$this->_selFindDestFleetRate->Execute($arrAliases);
			$arrSourceRate = $this->_selFindDestFleetRate->Fetch();
			if ($arrSourceRate['Id'])
			{
				$bolFleet = TRUE;
			}
		}
//Debug($bolFleet);
		// Set This Service
		$arrAliases['Service']		= $this->_arrCurrentCDR['Service'];

		// no rate selected
		$arrRate = FALSE;

		// find the appropriate rate
		if ($bolFleet === TRUE)
		{
			// look for a fleet rate
			$this->_selFindFleetRate->Execute($arrAliases);
			if (!($arrRate = $this->_selFindFleetRate->Fetch()))
			{
				// no fleet rate, look for a normal rate
				$this->_selFindRate->Execute($arrAliases);
			}
		}
		else
		{
			// look for a normal rate
			$this->_selFindRate->Execute($arrAliases);
		}

		//FAKE : For testing only
		//$this->_selFindRate->Execute();

		// check if we found a rate
		if (!$arrRate && !($arrRate = $this->_selFindRate->Fetch()))
		{
			// Look for the most recent rate
			if (!$this->_selFindLastRate->Execute($arrAliases))
			{
				return FALSE;
			}
			$arrRate = $this->_selFindLastRate->Fetch();
		}

		/* DIRTY HUGE DONKEY QUERY
		 *
		 * SELECT	Rate.*
		 *
		 * FROM		Rate INNER JOIN RateGroupRate ON Rate.Id = RateGroupRate.Rate,
		 * 			RateGroup INNER JOIN RateGroupRate ON RateGroup.Id = RateGroupRate.RateGroup,
		 * 			ServiceRateGroup INNER JOIN RateGroup ON ServiceRateGroup = RateGroup.Id
		 *
		 * WHERE	ServiceRateGroup.Service		= <Service>			AND
		 * 			Rate.RecordType					= <RecordType>		AND
		 * 			Rate.Destination				LIKE <Destination>	AND
		 * 			ServiceRateGroup.StartDateTime	<= <DateTime>		AND
		 * 			ServiceRateGroup.EndDateTime	>= <DateTime>		AND
		 * 			Rate.StartTime					<= <Time>			AND
		 * 			Rate.EndTime					>= <Time>			AND
		 * 				( Rate.Monday 				= <Monday>			OR
		 * 				  Rate.Tuesday				= <Tuesday>			OR
		 * 				  Rate.Wednesday			= <Wednesday>		OR
		 * 				  Rate.Thursday				= <Thursday>		OR
		 * 				  Rate.Friday				= <Friday>			OR
		 * 				  Rate.Saturday				= <Saturday>		OR
		 * 				  Rate.Sunday				= <Sunday> )
		 *
		 * ORDER BY ServiceRateGroup.CreatedOn DESC
		 *
		 * LIMIT 1
		 */

		// set the current rate
		$this->_arrCurrentRate = $arrRate;

		// cast MySQL strings to floats so they don't break our shit
		$this->_arrCurrentRate['StdRatePerUnit'] 	= (float)$this->_arrCurrentRate['StdRatePerUnit'];
		$this->_arrCurrentRate['StdFlagfall'] 		= (float)$this->_arrCurrentRate['StdFlagfall'];
		$this->_arrCurrentRate['StdPercentage'] 	= (float)$this->_arrCurrentRate['StdPercentage'];
		$this->_arrCurrentRate['StdMarkup'] 		= (float)$this->_arrCurrentRate['StdMarkup'];
		$this->_arrCurrentRate['StdMinCharge'] 		= (float)$this->_arrCurrentRate['StdMinCharge'];
		$this->_arrCurrentRate['ExsRatePerUnit'] 	= (float)$this->_arrCurrentRate['ExsRatePerUnit'];
		$this->_arrCurrentRate['ExsFlagfall'] 		= (float)$this->_arrCurrentRate['ExsFlagfall'];
		$this->_arrCurrentRate['ExsPercentage'] 	= (float)$this->_arrCurrentRate['ExsPercentage'];
		$this->_arrCurrentRate['ExsMarkup'] 		= (float)$this->_arrCurrentRate['ExsMarkup'];
		$this->_arrCurrentRate['CapCost'] 			= (float)$this->_arrCurrentRate['CapCost'];
		$this->_arrCurrentRate['CapLimit'] 			= (float)$this->_arrCurrentRate['CapLimit'];

		// return something
		return $this->_arrCurrentRate;
	 }

	//------------------------------------------------------------------------//
	// _Rounding
	//------------------------------------------------------------------------//
	/**
	 * _Rounding()
	 *
	 * Calculate Rounding for the current CDR Record
	 *
	 * Calculate Rounding for the current CDR Record
	 *
	 * @return		VOID
	 * @method
	 */
	 private function _Rounding()
	 {
	 	// get the current charge (in $)
		$fltCharge = $this->_arrCurrentCDR['Charge'];

		// round up to nearest 10th of a cent (in cents)
		$fltRoundedCharge = Ceil($fltCharge * 1000) / 10;

		// round to nearest cent (in $)
		$fltRoundedCharge = round($fltRoundedCharge) / 100;

		// take the difference and deposit into an offshore bank account ;)
		$this->_DonkeyAccount = ($fltRoundedCharge - $fltCharge) + $this->_DonkeyAccount;

		// set the current charge
		$this->_arrCurrentCDR['Charge'] = $fltRoundedCharge;

		// return the charge amount
		return;
	 }

	//------------------------------------------------------------------------//
	// _CalculateCharge
	//------------------------------------------------------------------------//
	/**
	 * _CalculateCharge()
	 *
	 * Calculate the base charge for the current CDR Record
	 *
	 * Calculate the base charge for the current CDR Record
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 * @method
	 */
	 private function _CalculateCharge()
	 {
	 	// call Zeemus magic rating formula
		$fltCharge = $this->_ZeemusMagicRatingFormula();

		// apply minimum charge
		$fltCharge = Max($fltCharge, $this->_arrCurrentRate['StdMinCharge']);

		// set the current charge
		$this->_arrCurrentCDR['Charge'] = $fltCharge;

		// return the charge amount
		return $fltCharge;
	 }

	//------------------------------------------------------------------------//
	// _CalculatePassThrough
	//------------------------------------------------------------------------//
	/**
	 * _CalculatePassThrough()
	 *
	 * Calculate the Pass Through charge for the current CDR Record
	 *
	 * Calculate the Pass Through charge for the current CDR Record
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 * @method
	 */
	 private function _CalculatePassThrough()
	 {
	 	// get the current charge
		$fltCharge		= $this->_arrCurrentCDR['Charge'];

	 	if ($this->_arrCurrentRate['PassThrough'])
		{
			// add the cost
			$fltCharge = $this->_arrCurrentCDR['Cost'];

			// add the flagfall
			$fltCharge += $this->_arrCurrentRate['StdFlagfall'];

			// apply minimum charge
			$fltCharge = Max($fltCharge, $this->_arrCurrentRate['StdMinCharge']);

			// set the current charge
			$this->_arrCurrentCDR['Charge'] = $fltCharge;
		}

		// return the charge amount
		return $fltCharge;
	 }

	//------------------------------------------------------------------------//
	// _CalculateCap
	//------------------------------------------------------------------------//
	/**
	 * _CalculateCap()
	 *
	 * Calculate the cap charge for the current CDR Record
	 *
	 * Calculate the cap charge for the current CDR Record
	 *
	 * Cap can be set in $ (CapCost) or units (CapUnits) but not both*.
	 * Cap Limit can be set in $ (CapLimit) or units (CapUsage) but not both*.
	 * *If both are specified, units will be used.
	 * The type of Cap & Cap Limit can be mixed, eg. Cap in $, Cap Limit in Units.
	 *
	 * if CapLimit is set ($ limit to capping) then only the standard rate will
	 * be used to calculate the charge. If CapUsage is set then the excess rate
	 * will be used to calculate the over cap charge.
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 *function
	 * @method
	 */
	 private function _CalculateCap()
	 {
	 	// get cap details
	 	$intCapUnits	= $this->_arrCurrentRate['CapUnits'];
		$fltCapCost		= $this->_arrCurrentRate['CapCost'];
		$intCapUsage	= $this->_arrCurrentRate['CapUsage'];
		$fltCapLimit	= $this->_arrCurrentRate['CapLimit'];

		// get CDR details
		$fltCharge		= $this->_arrCurrentCDR['Charge'];
		$fltFullCharge	= $fltCharge;
		$intUnits		= $this->_arrCurrentCDR['Units'];

	 	// is this a capped charge
		if (!$intCapUnits && !$fltCapCost && !$intCapUsage && !$fltCapLimit)
		{
			// not a capped rate, don't do anything
		}
		elseif (($fltCapCost && $fltCharge <= $fltCapCost) || $intUnits <= $intCapUnits)
		{
			// under the cap, don't do anything
		}
		else
		{
			// calculate cap charge
			if ($intCapUnits && $intUnits > $intCapUnits)
			{
				// over cap units, cap at cap units
				//resend to Zeemus magic rating formular with less units
				$fltCharge = $this->_ZeemusMagicRatingFormula('Std', $intCapUnits);
			}
			elseif ($fltCharge > $fltCapCost)
			{
				// over cap cost, cap at cap cost
				$fltCharge = $fltCapCost;
			}

			// calculate over cap limit charges
			if ($intCapUsage && $intUnits > $intCapUsage)
			{
				// calculate excess units
				$intExsUnits = $intUnits - $intCapUsage;

				// resend to Zeemus magic rating formular with excess units
				$fltCharge += $this->_ZeemusMagicRatingFormula('Exs', $intExsUnits);
			}
			elseif ($fltCapLimit && $fltFullCharge > $fltCapLimit)
			{
				// calculate excess charge
				$fltCharge += ($fltFullCharge - $fltCapLimit) + $this->_arrCurrentRate['ExsFlagfall'];
			}
		}

		// set the current charge
		$this->_arrCurrentCDR['Charge'] = $fltCharge;

		// return the charge amount
		return $fltCharge;
	 }

	//------------------------------------------------------------------------//
	// _CalculateProrate
	//------------------------------------------------------------------------//
	/**
	 * _CalculateProrate()
	 *
	 * Calculate the prorate charge for the current CDR Record
	 *
	 * Calculate the prorate charge for the current CDR Record
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _CalculateProrate()
	 {
	 	// get current charge
		$fltCharge = $this->_arrCurrentCDR['Charge'];

	 	// is this a prorate charge
		if ($this->_arrCurrentRate['Prorate'])
		{
			// Yes it is...
			$intEndDay		= floor(strtotime($this->_arrCurrentCDR['EndDatetime'])/86400);
			$intStartDay	= floor(strtotime($this->_arrCurrentCDR['StartDatetime'])/86400);
			$intEndMonth	= floor((strtotime("+ 1 month", strtotime($this->_arrCurrentCDR['StartDatetime']))/86400) - 1);
			$intDaysInMonth = $intEndMonth - $intStartDay;

			// is StartDate -> EndDate a whole month
			if ($intEndDay < $intEndMonth)
			{
				// calculate prorate
				$intDaysInCharge = $intEndDay - $intStartDay;
				try
				{
					// Calculate and set the current charge
					$fltCharge = ($fltCharge / $intDaysInMonth ) * $intDaysInCharge;
					$this->_arrCurrentCDR['Charge'] = $fltCharge;
				}
				catch (Exception $excException)
				{
					// Divide by zero
					return FALSE;
				}
			}
		}

		// return the charge amount
		return $fltCharge;
	 }

	//------------------------------------------------------------------------//
	// _UpdateTotals
	//------------------------------------------------------------------------//
	/**
	 * _UpdateTotals()
	 *
	 * Update the Service & Account totals
	 *
	 * Update the Service & Account totals
	 *
	 * @return	bool	you guessed it, TRUE is good / FALSE is bad / DONKEYs are some place in the middle
	 *
	 * @method
	 */
	 private function _UpdateTotals()
	 {
	 	// update service totals
		$fltCharge = $this->_arrCurrentCDR['Charge'];

		// don't update totals (or fail) if charge is zero
		if ($fltCharge == 0)
		{
			return 1;
		}

		// don't update totals (or fail) if charge is a credit
		if ($this->_arrCurrentCDR['Credit'] == TRUE)
		{
			return 1;
		}

		// set service Id
		$arrData['Id'] = $this->_arrCurrentCDR['Service'];

		if ($this->_arrCurrentRate['Uncapped'])
		{
			$arrData['UncappedCharge']    = new MySQLFunction("(UncappedCharge + <AddCharge>)", Array("AddCharge" => $fltCharge));
			return $this->_ubiServiceTotalsUncapped->Execute($arrData);
		}
		else
		{
			$arrData['CappedCharge']        = new MySQLFunction("(CappedCharge + <AddCharge>)", Array("AddCharge" => $fltCharge));
			return $this->_ubiServiceTotalsCapped->Execute($arrData);
		}
	 }

	//------------------------------------------------------------------------//
	// _ZeemusMagicRatingFormula
	//------------------------------------------------------------------------//
	/**
	 * _ZeemusMagicRatingFormula()
	 *
	 * Calculate the charge for the current CDR Record
	 *
	 * Calculate the charge for the current CDR Record
	 * This is where the actual work of applying the magic Zeemu rating formula
	 * is done.
	 *
	 * @param	string	$strType	optional Rate type to use, 'Std' or 'Exs'
	 * @param	int		$intUnits	optional units to use when calculating ($q)
	 *
	 * @return	mixed	float : charge amount
	 * 					FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _ZeemusMagicRatingFormula($strType = 'Std', $intUnits = 0)
	 {
	 	// select rate type to use (Std or Exs
		if ($strType != 'Std' && $strType != 'Exs')
		{
			return FALSE;
		}

		// ------------------------------------------------ //
		// rate details
		// ------------------------------------------------ //
		// a rate should never have a per unit rate & a markup
		// as it would be redundant (the rate should be set as a
		// $ markup rather then a rate)
		$r	= $this->_arrCurrentRate[$strType.'RatePerUnit'];	// rate per unit
		$f	= $this->_arrCurrentRate[$strType.'Flagfall'];		// flagfall
		$p	= $this->_arrCurrentRate[$strType.'Percentage'];	// percentage markup
		$d	= $this->_arrCurrentRate[$strType.'Markup'];		// dollar markup per unit
		$u	= $this->_arrCurrentRate[$strType.'Units'];			// units to charge in
		// ------------------------------------------------ //

		// ------------------------------------------------ //
		// CDR details
		// ------------------------------------------------ //
		$c	= $this->_arrCurrentCDR['Cost'];		// our cost (total)
		$q	= $this->_arrCurrentCDR['Units'];		// number of units (total)

		// ------------------------------------------------ //

		// ------------------------------------------------ //
		// Units Passed to Method
		// ------------------------------------------------ //
		if ((int)$intUnits > 0)
		{
			$q = (int)$intUnits;
		}

		// ------------------------------------------------ //

		// calculate number of units to charge
		if ($u === NULL)
		{
			Debug($this->_arrCurrentRate);
			die;
		}
		$n = ceil($q / $u);

		// ------------------------------------------------ //
		// apply the rate
		// ------------------------------------------------ //

		$fltCharge = 0;

		// apply per unit rate & flagfall
		// always applied, will equate to zero if there is
		// no per unit rate and no flagfall
		$fltCharge = ($n * $r + $f);

		// apply % and $ markup
		// only applied if the rate has a markup on cost
		// this will add our cost + markup to the charge
		// if there is no cost and no $ markup this will
		// equate to zero
		if ($p || $d)
		{
			$fltCharge += ($c + $p * $c / 100 + $n * $d);
		}

		// ------------------------------------------------ //

		// return the charge
		return $fltCharge;
	 }


	//------------------------------------------------------------------------//
	// ReRate()
	//------------------------------------------------------------------------//
	/**
	 * ReRate()
	 *
	 * Changes CDR Status from specified value to CDR_RERATE
	 *
	 * Forces the Rating engine to attempt to Rate the CDRs
	 * on the next Rating Run
	 *
	 * @param	integer		$intStatus			Status to look for
	 *
	 * @return	integer							Number of CDRs affected
	 *
	 * @method
	 */
	 function ReRate($intStatus)
	 {
	 	$intStatus = (int)$intStatus;
	 	$arrColumns['Status']	= CDR_RERATE;
	 	$updReRate = new StatementUpdate("CDR", "Status = $intStatus", $arrColumns);
	 	$mixReturn = $updReRate->Execute($arrColumns, NULL);
	 	return (int)$mixReturn;
	 }


	//------------------------------------------------------------------------//
	// UnRate()
	//------------------------------------------------------------------------//
	/**
	 * UnRate()
	 *
	 * UnRates 1000 CDRs with the status CDR_UNRATE
	 *
	 * UnRates a maximum of 1000 CDRs with the status CDR_UNRATE
	 *
	 * @return	integer							Number of CDRs affected
	 *
	 * @method
	 */
	 function UnRate()
	 {

	 	//TODO!flame! this method needs fixing

	 	// Select the CDRs
	 	$selCDRs = new StatementSelect(	"CDR JOIN Rate ON CDR.Rate = Rate.Id",
	 									"CDR.Id AS Id, CDR.Service AS Service, CDR.Charge AS Charge, Rate.Uncapped AS Uncapped",
	 									"CDR.Status = ".CDR_UNRATE,
	 									"CDR.Id ASC",
	 									"1000");
	 	if (($mixResult = $selCDRs->Execute()) === FALSE)
	 	{
	 		Debug("Selecting CDRs failed: ".$selCDRs->Error());
	 		return FALSE;
	 	}
	 	elseif (!$mixResult)
	 	{
	 		Debug("No CDRs to unrate");
	 		return 0;
	 	}

	 	// For each of the CDRs
	 	$intMinCDRId = NULL;
	 	$arrColumns = Array();
	 	$arrColumns['UncappedCharge']	= new MySQLFunction("UncappedCharge - <UncappedCharge>");
	 	$arrColumns['CappedCharge']		= new MySQLFunction("CappedCharge - <CappedCharge>");
	 	$updServiceTotals = new StatementUpdate("Service", "Id = <Service>", $arrColumns);
	 	while($arrCDR = $selCDRs->Fetch())
	 	{
		 	// Set min CDR Id value
		 	if ($intMinCDRId)
		 	{
		 		$intMinCDRId = min($intMinCDRId, $arrCDR['Id']);
		 	}
		 	else
		 	{
		 		$intMinCDRId = $arrCDR['Id'];
		 	}

		 	// Uncapped or Capped
		 	if ($arrCDR['Uncapped'])
		 	{
		 		$arrColumns['UncappedCharge']	= new MySQLFunction("UncappedCharge - <UncappedCharge>", Array('UncappedCharge' => $arrCDR['Charge']));
		 		$arrColumns['CappedCharge']		= new MySQLFunction("CappedCharge - <CappedCharge>", Array('CappedCharge' => 0));
		 	}
		 	else
		 	{
		 		$arrColumns['CappedCharge']		= new MySQLFunction("CappedCharge - <CappedCharge>", Array('CappedCharge' => $arrCDR['Charge']));
		 		$arrColumns['UncappedCharge']	= new MySQLFunction("UncappedCharge - <UncappedCharge>", Array('UncappedCharge' => 0));
		 	}

		 	// Update the Service
		 	if ($updServiceTotals->Execute($arrColumns, Array('Service' => $arrCDR['Service'])) === FALSE)
		 	{
		 		Debug("Couldn't update Service: ".$updServiceTotals->Error());
		 		return FALSE;
		 	}
	 	}

	 	// Set the CDR statuses
	 	$arrColumns = Array();
	 	$arrColumns['Status']	= CDR_NORMALISED;
	 	$updCDRStatus = new StatementUpdate("CDR", "Id >= $intMinCDRId AND Status = ".CDR_UNRATE, $arrColumns, 1000);
	 	return $updCDRStatus->Execute($arrColumns);
	 }



	//------------------------------------------------------------------------//
	// GetMargin
	//------------------------------------------------------------------------//
	/**
	 * GetMargin()
	 *
	 * Gets the Profit Margin for this current Rating Run
	 *
	 * Gets the Profit Margin for this current Rating Run.  If it breaches the
	 * Margin warning level, it will email an admin
	 *
	 * @param	integer	$intWarningLevel	Profit Margin Warning Level (percentage)
	 * @param	integer	$intWarningCount	Minimum number of CDRs to Rate before Warning
	 * @param	string	$strEmailAddress	Admin's email address
	 *
	 * @return	float						Profit Margin
	 * @method
	 */
	 function GetMargin($intWarningLevel, $intWarningCount, $strEmailAddress)
	 {
	 	// Calculate Margin
	 	$fltMargin = ($this->_fltTotalCharge) ? (($this->_fltTotalCharge - $this->_fltTotalCost) / abs($this->_fltTotalCharge)) * 100 : 0;

	 	// Did we exceed?
	 	/*if ($fltMargin >= $intWarningLevel && $this->_intTotalRated >= $intWarningCount)
	 	{
	 		// Email
			$strContent =	"Rating Profit Margin Warning (".date("Y-m-d H:i:s").")\n\n" .
							"\tCDRs Rated\t\t:$this->_intTotalRated\n" .
							"\tTotal Cost\t\t: $this->_fltTotalCost\n" .
							"\tTotal Charge\t\t: $this->_fltTotalCharge\n" .
							"\tProfit Margin\t\t: $fltMargin% (Limit: $intWarningLevel%)";

			$arrHeaders = Array();
			$arrHeaders['From']		= 'rating@yellowbilling.com.au';
			$arrHeaders['Subject']	= "Rating Profit Margin Warning (".date("Y-m-d H:i:s").")";
 			$mimMime = new Mail_mime("\n");
 			$mimMime->setTXTBody($strContent);
			$strBody = $mimMime->get();
			$strHeaders = $mimMime->headers($arrHeaders);
 			$emlMail =& Mail::factory('mail');

 			// Send the email
 			if (!$emlMail->send($strEmailAddress, $strHeaders, $strBody))
 			{
 				$this->_rptCollectionReport->AddMessage("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
 				continue;
 			}
	 	}*/

	 	return $fltMargin;
	 }


	//------------------------------------------------------------------------//
	// UpdateServiceTypeTotal
	//------------------------------------------------------------------------//
	/**
	 * UpdateServiceTypeTotal()
	 *
	 * Creates or Updates the ServiceTypeTotal for this CDR
	 *
	 * Creates or Updates the ServiceTypeTotal for this CDR
	 *
	 * @param	array	$arrCDR				CDR to use
	 *
	 * @return	array						Updated CDR Record
	 * @method
	 */
	 function UpdateServiceTypeTotal($arrCDR)
	 {
	 	// TODO
	 }


	//------------------------------------------------------------------------//
	// _FindRateNew()
	//------------------------------------------------------------------------//
	/**
	 * _FindRateNew()
	 *
	 * Find the appropriate rate for the current CDR
	 *
	 * Find the appropriate rate for the current CDR
	 *
	 * @return	mixed	array	rate details
	 * 					bool	FALSE if rate not found
	 * @method
	 */
	protected function _FindRateNew()
	{
	 	// Set up the rate-finding query
	 	$intTime					= strtotime($this->_arrCurrentCDR['StartDatetime']);
	 	$strDay						= date("l", $intTime);

	 	$arrWhere					= Array();
	 	$arrWhere['StartDatetime']	= $this->_arrCurrentCDR['StartDatetime'];
	 	$arrWhere['Time']			= date("H:i:s", $intTime);
	 	$arrWhere['Monday']			= ($strDay == "Monday")		? TRUE : DONKEY;
	 	$arrWhere['Tuesday']		= ($strDay == "Tuesday")	? TRUE : DONKEY;
	 	$arrWhere['Wednesday']		= ($strDay == "Wednesday")	? TRUE : DONKEY;
	 	$arrWhere['Thursday']		= ($strDay == "Thursday")	? TRUE : DONKEY;
	 	$arrWhere['Friday']			= ($strDay == "Friday")		? TRUE : DONKEY;
	 	$arrWhere['Saturday']		= ($strDay == "Saturday")	? TRUE : DONKEY;
	 	$arrWhere['Sunday']			= ($strDay == "Sunday")		? TRUE : DONKEY;
		$arrWhere['RecordType']		= $this->_arrCurrentCDR['RecordType'];
		$arrWhere['Destination']	= ($this->_arrCurrentCDR['DestinationCode']) ? $this->_arrCurrentCDR['DestinationCode'] : 0;
		$arrWhere['ClosestRate']	= FALSE;

		$this->_Debug("General WHERE Data: \n".print_r($arrWhere, TRUE));

		// Could this be a Fleet call?
		$bolFleet				= FALSE;
		$arrDestinationOwner	= FindFNNOwner($this->_arrCurrentCDR['Destination'], $this->_arrCurrentCDR['StartDatetime']);
		if ($arrDestinationOwner['Account'] === $this->_arrCurrentCDR['Account'])
		{
			$this->_Debug("Destination is on the same Account: Trying to find a Destination Fleet Rate...");

			$arrFleetWhere					= $arrWhere;
			$arrFleetWhere['Account']		= $arrDestinationOwner['Account'];
			$arrFleetWhere['AccountGroup']	= $arrDestinationOwner['AccountGroup'];
			$arrFleetWhere['Service']		= $arrDestinationOwner['Service'];
			$arrFleetWhere['Fleet']			= TRUE;
			$arrFleetWhere['RecordType']	= DONKEY;							// Must be DONKEY, because Fleet calls can occur between ServiceTypes, and therefore between RecordTypes
			if ($this->_selRate->Execute($arrFleetWhere) === FALSE)
			{
				// Error
				Debug($this->_selRate->Error());
			}
			elseif ($arrDestinationRate = $this->_selRate->Fetch())
			{
				$this->_Debug("Found a Destination Fleet Rate!");
				// Found a Fleet Rate
				$bolFleet	= TRUE;
			}
		}

		// Find the Rate
		$arrWhere['Account']		= $this->_arrCurrentCDR['Account'];
		$arrWhere['AccountGroup']	= $this->_arrCurrentCDR['AccountGroup'];
		$arrWhere['Service']		= $this->_arrCurrentCDR['Service'];
		$arrWhere['Fleet']			= $bolFleet;
		$this->_arrCurrentRate		= NULL;
		if ($this->_selRate->Execute($arrWhere) === FALSE)
		{
			// Error
			Debug($this->_selRate->Error());
		}
		elseif ($arrRate = $this->_selRate->Fetch())
		{
			$this->_Debug("Found a Source ".(($bolFleet) ? "Fleet" : "Standard")." Rate!");
			// Found a Rate
			$this->_arrCurrentRate	= $arrRate;
		}
		elseif ($bolFleet)
		{
			// Didn't find a Fleet Rate, try to find a normal rate
			$arrWhere['Fleet']	= 0;
			if ($this->_selRate->Execute($arrWhere) === FALSE)
			{
				// Error
				Debug($this->_selRate->Error());

			}
			elseif ($arrRate = $this->_selRate->Fetch())
			{
				$this->_Debug("Couldn't find a Fleet Rate, found a Standard Rate!");
				// Found a Standard Rate
				$this->_arrCurrentRate	= $arrRate;
			}
		}

		// If there is still no Rate, then check for a close match
		if (!$this->_arrCurrentRate)
		{
			$this->_Debug("Couldn't find a direct match Rate, looking for a close match...");
			$arrWhere['ClosestRate']	= TRUE;
			$arrWhere['Fleet']			= 0;
			if (($intCount = $this->_selRate->Execute($arrWhere)) === FALSE)
			{
				// Error
				Debug($this->_selRate->Error());
			}
			$this->_Debug("Found $intCount close matches...");

			// Process each Rate candidate to find the best match
			$arrBestMatch				= Array();
			$arrBestMatch['Distance']	= PHP_INT_MAX;
			while ($arrRate = $this->_selRate->Fetch())
			{
				if ($arrRate['StartDatetime'] > $this->_arrCurrentCDR['StartDatetime'])
				{
					// Rate is after CDR
					$arrRate['Distance']	= strtotime($arrRate['StartDatetime']) - strtotime($this->_arrCurrentCDR['StartDatetime']);
					$arrBestMatch			= ($arrRate['Distance'] <= $arrBestMatch['Distance']) ? $arrRate : $arrBestMatch;
				}
				else
				{
					// Rate is before CDR
					$arrRate['Distance']	= strtotime($this->_arrCurrentCDR['StartDatetime']) - strtotime($arrRate['StartDatetime']);
					$arrBestMatch			= ($arrRate['Distance'] < $arrBestMatch['Distance']) ? $arrRate : $arrBestMatch;
				}
			}

			// Select the best match
			if ($arrBestMatch['Id'])
			{
				$this->_Debug("Found a close match!");
				$this->_arrCurrentRate	= $arrBestMatch;
			}
			else
			{
				$this->_Debug("Could not find a close match!");
			}
		}

		// Cast MySQL strings to floats so they don't break our shit
		if ($this->_arrCurrentRate)
		{
			$this->_arrCurrentRate['StdRatePerUnit'] 	= (float)$this->_arrCurrentRate['StdRatePerUnit'];
			$this->_arrCurrentRate['StdFlagfall'] 		= (float)$this->_arrCurrentRate['StdFlagfall'];
			$this->_arrCurrentRate['StdPercentage'] 	= (float)$this->_arrCurrentRate['StdPercentage'];
			$this->_arrCurrentRate['StdMarkup'] 		= (float)$this->_arrCurrentRate['StdMarkup'];
			$this->_arrCurrentRate['StdMinCharge'] 		= (float)$this->_arrCurrentRate['StdMinCharge'];
			$this->_arrCurrentRate['ExsRatePerUnit'] 	= (float)$this->_arrCurrentRate['ExsRatePerUnit'];
			$this->_arrCurrentRate['ExsFlagfall'] 		= (float)$this->_arrCurrentRate['ExsFlagfall'];
			$this->_arrCurrentRate['ExsPercentage'] 	= (float)$this->_arrCurrentRate['ExsPercentage'];
			$this->_arrCurrentRate['ExsMarkup'] 		= (float)$this->_arrCurrentRate['ExsMarkup'];
			$this->_arrCurrentRate['CapCost'] 			= (float)$this->_arrCurrentRate['CapCost'];
			$this->_arrCurrentRate['CapLimit'] 			= (float)$this->_arrCurrentRate['CapLimit'];
			return $this->_arrCurrentRate;
		}
		else
		{
			$this->_Debug("Could not find a Rate!");
			return FALSE;
		}
	}


	//------------------------------------------------------------------------//
	// CDRFindRate()
	//------------------------------------------------------------------------//
	/**
	 * CDRFindRate()
	 *
	 * Finds the appropriate Rate for a given CDR
	 *
	 * Finds the appropriate Rate for a given CDR
	 *
	 * @return	mixed	array	rate details
	 * 					bool	FALSE if rate not found
	 * @method
	 */
	public function CDRFindRate($arrCDR)
	{
		$this->_arrCurrentCDR	= $arrCDR;

		return $this->_FindRateNew();
	}



	//------------------------------------------------------------------------//
	// _Debug()
	//------------------------------------------------------------------------//
	/**
	 * _Debug()
	 *
	 * Outputs a message if in RATING_DEBUG mode
	 *
	 * Outputs a message if in RATING_DEBUG mode
	 *
	 * @return	mixed	array	rate details
	 * 					bool	FALSE if rate not found
	 * @method
	 */
	protected function _Debug($strMessage, $bolNewLine = TRUE)
	{
		if (RATING_DEBUG === TRUE)
		{
			CliEcho($strMessage, $bolNewLine);
		}
	}
 }


?>
