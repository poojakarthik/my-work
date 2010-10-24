<?php

require_once('Spreadsheet/Excel/Writer.php');

/**
 * Cli_App_Rating
 *
 * @parent	Cli
 */
class Cli_App_Rating extends Cli
{
	//const	SWITCH_TEST_RUN				= 't';
	const	SWITCH_CDR_ID				= 'i';
	const	SWITCH_INCLUDE_RERATE		= 'r';
	const	SWITCH_COMPARISON_MODE		= 'c';
	const	SWITCH_COMPARISON_KEEP_RATE	= 'k';
	const	SWITCH_COMPARISON_EMAIL		= 'e';
	const	SWITCH_RATING_LIMIT			= 'm';
	
	const	RATING_LIMIT_NONE			= null;
	const	DEFAULT_RATING_LIMIT		= null;
	
	const	REPORT_COLUMN_CDR_ID			= 0;
	const	REPORT_COLUMN_RATE_ORIGINAL		= 1;
	const	REPORT_COLUMN_RATE_NEW			= 2;
	const	REPORT_COLUMN_COST				= 3;
	const	REPORT_COLUMN_CHARGE_ORIGINAL	= 4;
	const	REPORT_COLUMN_CHARGE_NEW		= 5;
	const	REPORT_COLUMN_CHARGE_DIFFERENCE	= 6;
	
	const	COMPARISON_CHARGE_DIFFERENCE_THRESHOLD	= 0.01;
	
	const	RATING_EXCEPTION_CONTINUE		= true;
	const	DELETE_COMPARISON_REPORT_FILE	= false;
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			$iCDRId	= (isset($this->_arrArgs[self::SWITCH_CDR_ID])) ? (int)$this->_arrArgs[self::SWITCH_CDR_ID] : null;
			
			if ($this->_arrArgs[self::SWITCH_COMPARISON_MODE])
			{
				Log::getLog()->log('[ Comparison Mode ]');
				
				// Comparison Mode (higher priority than re-Rate mode)
				$aCDRIds	= array();
				if ($iCDRId > 0)
				{
					// Compare 1 CDR
					$aCDRIds	= array($iCDRId);
				}
				else
				{
					// Full Comparison Run
					$aCDRIds	= $this->_getComparisonCDRs();
				}
				
				// Enable Rating Logging
				Rate::setRateLoggingEnabled(true);
				
				// Encase Comparison in a Transaction
				if (!DataAccess::getDataAccess()->TransactionStart())
				{
					throw new Exception_Database_Transaction("Unable to start a Transaction for Comparison Run");
				}
				
				try
				{
					$aCDRChanges	= $this->_rateCDRs($aCDRIds);
				}
				catch (Exception $oComparisonException)
				{
					// Do nothing just yet
				}
				
				// Always Rollback in Comparison Mode
				if (!DataAccess::getDataAccess()->TransactionRollback())
				{
					throw new Exception_Database_Transaction("Unable to roll back a Transaction for Comparison Run -- some data may have been inadvertently saved!");
				}
				
				// Rethrow the Exception (if one occurred)
				if ($oComparisonException)
				{
					throw $oComparisonException;
				}
				
				if (count($aCDRIds) > 1)
				{
					// Generate Comparison Report (only if there is more than 1 CDR)
					$sComparisonReportContent	= $this->_buildComparisonReport($aCDRChanges);
					$sComparisonReportFileName	= 'rating-comparison-report-'.date('YmdHis').'.xls';
					
					if ($this->_arrArgs[self::SWITCH_COMPARISON_EMAIL])
					{
						Log::getLog()->log("Sending Rating Comparison Report to {$this->_arrArgs[self::SWITCH_COMPARISON_EMAIL]}");
						
						// Email Comparison Report
						$oEmail	= new Email_Flex();
						$oEmail->setSubject("Rating Comparison Report from ".date('d/m/y H:i:s')." (".count($aCDRIds)." CDRs)");
						$oEmail->setBodyText("Rating Comparison Report for ".count($aCDRIds)." CDRs attached.");
						$oEmail->addTo($this->_arrArgs[self::SWITCH_COMPARISON_EMAIL]);
						$oEmail->createAttachment($sComparisonReportContent, 'application/vnd.ms-excel', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $sComparisonReportFileName);
						$oEmail->send();
					}
				}
			}
			else
			{
				Log::getLog()->log('[ Standard Rating Mode ]');
				//Rate::setRateLoggingEnabled(true);
				
				// Standard Rating mode
				$aCDRIds	= array();
				if ($iCDRId > 0)
				{
					// Rate 1 CDR
					$aCDRIds	= array($iCDRId);
				}
				else
				{
					// Full Rating Run
					$aCDRIds	= $this->_getPendingCDRIds($this->_arrArgs[self::SWITCH_RATING_LIMIT], $this->_arrArgs[self::SWITCH_INCLUDE_RERATE]);
				}
				$aCDRChanges	= $this->_rateCDRs($aCDRIds);
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	protected function _getComparisonCDRs()
	{
		$aCDRStatuses	= array(
			'Rated'			=> CDR_RATED,
			'ReRate'		=> CDR_RERATE,
			'Temp Invoice'	=> CDR_TEMP_INVOICE
		);
		
		Log::getLog()->log("Pulling Comparison CDRs (Statuses: ".implode(', ', array_keys($aCDRStatuses)).")");
		
		$oQuery	= new Query();
		$sSQL	= "	SELECT		Id
					FROM		CDR
					WHERE		Status IN (".implode(', ', $aCDRStatuses).")
								AND Charge IS NOT NULL
					ORDER BY	StartDatetime DESC,
								Id DESC"/*DEBUG.' LIMIT 1000'/**/;
		if (($mResult = $oQuery->Execute($sSQL)) === false)
		{
			throw new Exception($oQuery->Error());
		}
		
		$aCDRIds	= array();
		while ($aCDRId = $mResult->fetch_assoc())
		{
			$aCDRIds[]	= (int)$aCDRId['Id'];
		}
		
		return $aCDRIds;
	}
	
	protected function _getPendingCDRIds($iLimit=self::DEFAULT_RATING_LIMIT, $bIncludeReRateCDRs=false)
	{
		$iLimit	= (int)$iLimit;
		$iLimit	= ($iLimit > 0 || $iLimit === self::RATING_LIMIT_NONE) ? $iLimit : self::RATING_LIMIT_NONE;
		$sLimit	= ($iLimit > 0) ? "LIMIT		{$iLimit}" : '';
		
		$aCDRStatuses	= array('Normalised'=>CDR_NORMALISED);
		if ($bIncludeReRateCDRs)
		{
			$aCDRStatuses['ReRate']	= CDR_RERATE;
		}
		
		Log::getLog()->log("Pulling Pending CDRs (Limit: ".(($iLimit === null) ? 'No Limit' : $iLimit)."; Statuses: ".implode(', ', array_keys($aCDRStatuses)).")");
		
		$oQuery	= new Query();
		$sSQL	= "	SELECT		Id
					FROM		CDR
					WHERE		Status IN (".implode(', ', $aCDRStatuses).")
					ORDER BY	StartDatetime DESC,
								Id DESC
					{$sLimit}";
		if (($mResult = $oQuery->Execute($sSQL)) === false)
		{
			throw new Exception($oQuery->Error());
		}
		
		$aCDRIds	= array();
		while ($aCDRId = $mResult->fetch_assoc())
		{
			$aCDRIds[]	= (int)$aCDRId['Id'];
		}
		
		return $aCDRIds;
	}
	
	protected function _rateCDRs(array $aCDRIds)
	{
		$oStopwatch	= new Stopwatch();
		$fStartTime	= $oStopwatch->start();
		
		$aCDRChanges	= array();
		
		$iTotalCDRs		= count($aCDRIds);
		$iBatchProgress	= 0;
		
		Log::getLog()->log("Rating {$iTotalCDRs} CDRs");
		foreach ($aCDRIds as $iCDRId)
		{
			$iBatchProgress++;
			
			// Clear the cache before each CDR (to lower memory usage)
			CDR::clearCache();
			
			try
			{
				if (!DataAccess::getDataAccess()->TransactionStart())
				{
					throw new Exception_Database_Transaction("Unable to start a Transaction for CDR {$iCDRId}: ".DataAccess::getDataAccess()->Error());
				}
				
				try
				{
					Log::getLog()->log("[ CDR {$iCDRId} ({$iBatchProgress}/{$iTotalCDRs}) ]", false);
					
					if (Rate::isRateLoggingEnabled())
					{
						Log::getLog()->log('');
						Log::getLog()->log('{');
					}
					
					$oCDR	= CDR::getForId($iCDRId);
					
					// Rate
					$oCDRPreRate	= $oCDR->toStdClass();
					
					try
					{
						$oCDR->rate($this->_arrArgs[self::SWITCH_COMPARISON_MODE], $this->_arrArgs[self::SWITCH_COMPARISON_MODE] && $this->_arrArgs[self::SWITCH_COMPARISON_KEEP_RATE]);
					}
					catch (Exception_Rating_RateNotFound $oException)
					{
						// Do nothing -- we don't care if this happens
					}
					
					if (Rate::isRateLoggingEnabled())
					{
						Log::getLog()->log('');
						Log::getLog()->log('}', false);
					}
					
					$oCDRPostRate	= $oCDR->toStdClass();
					
					$aCDRChanges[$oCDR->Id]			= array();
					$aCDRChanges[$oCDR->Id]['PRE']	= $oCDRPreRate;
					$aCDRChanges[$oCDR->Id]['POST']	= $oCDRPostRate;
					
					// If we have two Charges to compare...
					if ($oCDRPreRate->Rate && $oCDRPostRate->Rate)
					{
						// Compare our two rated values
						$aCDRChanges[$oCDR->Id]['DIFF']	= (object)array(
							'Charge'	=> $oCDRPostRate->Charge - $oCDRPreRate->Charge,
						);
					}
					
					Log::getLog()->log(" Rate: ".(($oCDRPostRate->Rate) ? $oCDRPostRate->Rate : 'No Rate Found!'), false);
					if ($oCDRPreRate->Rate)
					{
						Log::getLog()->log(" (previously: {$oCDRPreRate->Rate})", false);
					}
					
					Log::getLog()->log("; Cost: \$".number_format($oCDRPostRate->Cost, Rate::RATING_PRECISION, '.', ''), false);
					
					if ($oCDRPostRate->Rate)
					{
						Log::getLog()->log("; Charge: \$".number_format($oCDRPostRate->Charge, Rate::RATING_PRECISION, '.', ''), false);
					}
					if ($oCDRPreRate->Rate)
					{
						Log::getLog()->log(" (previously: \$".number_format($oCDRPreRate->Charge, Rate::RATING_PRECISION, '.', '').")", false);
					}
					
					if ($oCDRPreRate->Rate && $oCDRPostRate->Rate)
					{
						if ($oCDRPreRate->Rate !== $oCDRPostRate->Rate)
						{
							Log::getLog()->log(' [RATE DIFFERENCE]', false);
						}
						if (isset($aCDRChanges[$oCDR->Id]['DIFF']->Charge) && round($aCDRChanges[$oCDR->Id]['DIFF']->Charge, 2) != 0.0)
						{
							Log::getLog()->log(' [CHARGE DIFFERENCE: $'.number_format($aCDRChanges[$oCDR->Id]['DIFF']->Charge, Rate::RATING_PRECISION, '.', '').']', false);
						}
					}
					
					// Force line break
					Log::getLog()->log(' in '.round($oStopwatch->lap(), 2).' seconds');
				}
				catch (Exception $oException)
				{
					DataAccess::getDataAccess()->TransactionRollback();
					throw $oException;
				}
				
				// Commit our changes
				if (!DataAccess::getDataAccess()->TransactionCommit())
				{
					throw new Exception_Database_Transaction("Unable to commit Transaction for CDR {$iCDRId}: ".DataAccess::getDataAccess()->Error());
				}
			}
			catch (Exception_Database_Transaction $oException)
			{
				// Transaction Error -- fail all the way out
				throw $oException;
			}
			catch (Exception $oException)
			{
				if (self::RATING_EXCEPTION_CONTINUE)
				{
					// Any other Exception -- just continue to the next CDR
					Log::getLog()->log("[ ERROR: ".$oException->getMessage()." ]");
				}
				else
				{
					// Until we're in production & completely happy, we'll fail out completely
					throw $oException;
				}
			}
		}
		
		$fTotalTime	= $oStopwatch->split();
		Log::getLog()->log("Rated {$iTotalCDRs} CDRs in ".round($fTotalTime, 2).' seconds (average '.round(($fTotalTime / $iTotalCDRs), 2).' CDRs/second)');
		
		return $aCDRChanges;
	}
	
	protected function _buildComparisonReport(array $aCDRChanges)
	{
		if (($sTempFileName = tempnam(sys_get_temp_dir(), 'flex-rating-comparison-report.')) === false)
		{
			throw new Exception("Unable to create a temporary file for the Comparison Report: ".$php_errormsg);
		}
		
		Log::getLog()->log("Writing Comparison Report to '{$sTempFileName}'...");
		
		// Create an XLS
		$oWorkbook	= new Spreadsheet_Excel_Writer($sTempFileName);
		
		$aWorksheets	= array(
			'SIGNIFICANT'	=> array(
				'oWorksheet'	=> $oWorkbook->addWorksheet('Significant Differences'),
				'iCount'		=> 0
			),
			'MINOR'			=> array(
				'oWorksheet'	=> $oWorkbook->addWorksheet('Minor Differences'),
				'iCount'		=> 0
			),
			'NONE'			=> array(
				'oWorksheet'	=> $oWorkbook->addWorksheet('No Differences'),
				'iCount'		=> 0
			),
			'NO_OLD_RATE'	=> array(
				'oWorksheet'	=> $oWorkbook->addWorksheet('No Old Rate'),
				'iCount'		=> 0
			),
			'NO_NEW_RATE'	=> array(
				'oWorksheet'	=> $oWorkbook->addWorksheet('No New Rate'),
				'iCount'		=> 0
			),
			'NO_NEW_OLD_RATE'	=> array(
				'oWorksheet'	=> $oWorkbook->addWorksheet('No New or Old Rate'),
				'iCount'		=> 0
			)
		);
		
		// Formats
		$aFormats	= array(
			'HEADER'	=> $oWorkbook->addFormat(
				array(
					'Bold'		=> 1,
					'Color'		=> 'white',
					'FgColor'	=> 63
				)
			),
			'CURRENCY'	=> $oWorkbook->addFormat(
				array(
					'NumFormat'		=> '$0.0000'
				)
			)
		);
		
		// Header Rows & Columns
		foreach ($aWorksheets as $sAlias=>$aWorksheetDefinition)
		{
			$oWorksheet	= $aWorksheetDefinition['oWorksheet'];
			
			// Columns Headers
			$oWorksheet->write(0, self::REPORT_COLUMN_CDR_ID			, 'CDR Id'			, $aFormats['HEADER']);
			$oWorksheet->write(0, self::REPORT_COLUMN_RATE_ORIGINAL		, 'Original Rate'	, $aFormats['HEADER']);
			$oWorksheet->write(0, self::REPORT_COLUMN_RATE_NEW			, 'New Rate'		, $aFormats['HEADER']);
			$oWorksheet->write(0, self::REPORT_COLUMN_COST				, 'Cost'			, $aFormats['HEADER']);
			$oWorksheet->write(0, self::REPORT_COLUMN_CHARGE_ORIGINAL	, 'Original Charge'	, $aFormats['HEADER']);
			$oWorksheet->write(0, self::REPORT_COLUMN_CHARGE_NEW		, 'New Charge'		, $aFormats['HEADER']);
			
			// Column Widths
			$oWorksheet->setColumn(self::REPORT_COLUMN_CDR_ID	, self::REPORT_COLUMN_CHARGE_NEW	, 15);
		}
		
		// Data
		foreach ($aCDRChanges as $iCDRId=>$aCDRData)
		{
			$aWorksheetDefinition	= null;
			if ($aCDRData['DIFF'])
			{
				if ($aCDRData['DIFF']->Charge == 0.0)
				{
					$aWorksheetDefinition	= &$aWorksheets['NONE'];
				}
				elseif (Rate::roundToRatingStandard(abs($aCDRData['DIFF']->Charge)) <= self::COMPARISON_CHARGE_DIFFERENCE_THRESHOLD)
				{
					$aWorksheetDefinition	= &$aWorksheets['MINOR'];
				}
				else
				{
					$aWorksheetDefinition	= &$aWorksheets['SIGNIFICANT'];
				}
			}
			elseif ($aCDRData['PRE']->Rate)
			{
				// We have a PRE Rate, so we must be missing a POST Rate
				$aWorksheetDefinition	= &$aWorksheets['NO_NEW_RATE'];
			}
			elseif ($aCDRData['POST']->Rate)
			{
				// We have a POST Rate, so we must be missing a PRE Rate
				$aWorksheetDefinition	= &$aWorksheets['NO_OLD_RATE'];
			}
			else
			{
				// We don't have a PRE or POST Rate
				$aWorksheetDefinition	= &$aWorksheets['NO_NEW_OLD_RATE'];
			}
			
			$aWorksheetDefinition['iCount']++;
			
			//Log::getLog()->log("Logging CDR {$iCDRId} to '".$aWorksheetDefinition['oWorksheet']->getName()."' in row {$aWorksheetDefinition['iCount']}");
			
			$oWorksheet	= $aWorksheetDefinition['oWorksheet'];
			
			$oWorksheet->write($aWorksheetDefinition['iCount'], self::REPORT_COLUMN_CDR_ID			, (int)$iCDRId);
			$oWorksheet->write($aWorksheetDefinition['iCount'], self::REPORT_COLUMN_COST			, (float)$aCDRData['PRE']->Cost										, $aFormats['CURRENCY']);
			
			if ($aCDRData['PRE']->Rate !== null)
			{
				$oWorksheet->write($aWorksheetDefinition['iCount'], self::REPORT_COLUMN_RATE_ORIGINAL	, ($aCDRData['PRE']->Rate) ? (int)$aCDRData['PRE']->Rate : null);
				$oWorksheet->write($aWorksheetDefinition['iCount'], self::REPORT_COLUMN_CHARGE_ORIGINAL	, (float)$aCDRData['PRE']->Charge									, $aFormats['CURRENCY']);
			}
			if ($aCDRData['POST']->Rate !== null)
			{
				$oWorksheet->write($aWorksheetDefinition['iCount'], self::REPORT_COLUMN_RATE_NEW		, ($aCDRData['POST']->Rate) ? (int)$aCDRData['POST']->Rate : null);
				$oWorksheet->write($aWorksheetDefinition['iCount'], self::REPORT_COLUMN_CHARGE_NEW		, (float)$aCDRData['POST']->Charge									, $aFormats['CURRENCY']);
			}
			
			unset($aWorksheetDefinition);
		}
		
		// Close the Workbook
		$oWorkbook->close();
		
		// Pull data from our temp file
		if (($sTempFileData = @file_get_contents($sTempFileName)) === false)
		{
			throw new Exception("Unable to get Comparison Report temp file data from '{$sTempFileName}': ".$php_errormsg);
		}
		
		if (self::DELETE_COMPARISON_REPORT_FILE)
		{
			// Remove the temp file (we don't really care if it fails)
			@unlink($sTempFileData);
		}
		
		return $sTempFileData;
	}
	
	function getCommandLineArguments()
	{
		return array(
			/*self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),*/
		
			self::SWITCH_INCLUDE_RERATE => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Includes CDRs marked for ReRating in the Rating Run",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_COMPARISON_MODE => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Compares current CDR details to its rerated details",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_COMPARISON_KEEP_RATE => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Retains the current rate in comparison mode (for comparing Rating engine changes)",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_CDR_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "CDR_ID",
				self::ARG_DESCRIPTION	=> "CDR Id (limits run to this CDR only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_RATING_LIMIT => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "RATING_LIMIT",
				self::ARG_DESCRIPTION	=> "Maximum number of CDRs to Rate (normal rating mode only)",
				self::ARG_DEFAULT		=> self::DEFAULT_RATING_LIMIT,
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_COMPARISON_EMAIL => array(
				self::ARG_LABEL			=> "EMAIL_ADDRESS",
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Email address to send the Comparison Report to",
				self::ARG_DEFAULT		=> "ybs-admin@ybs.net.au",
				self::ARG_VALIDATION	=> 'Cli::_validString("%1$s")'
			)
		);
	}
}

?>