<?php

/**
 * Version 201 of database update.
 * This version: -
 *	
 *	1:	Convert RatePlan Capping properties to Discounts
 *	2:	Correct Data Rate cap inclusiveness
 *
 */

class Flex_Rollout_Version_000201 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Convert RatePlan Capping properties to Discounts
		//	Get list of Rate Plans that have RatePlan.UsageCap or RatePlan.included_usage > 0
		$this->outputMessage("Retrieving list of Rate Plans that have a Usage Limit (RatePlan.UsageLimit) or Included Data (RatePlan.included_data)\n");
		$sPlanSelectSQL	= "	SELECT	*
							FROM	RatePlan
							WHERE	(
										(
											included_data IS NOT NULL
											AND included_data > 0
										)
										OR
										UsageCap > 0
									);";
		$oPlanSelectResult	= $dbAdmin->query($sPlanSelectSQL);
		if (PEAR::isError($oPlanSelectResult))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve Rate Plans with a Usage Limit or Included Data. ' . $oPlanSelectResult->getMessage() . " (DB Error: " . $oPlanSelectResult->getUserInfo() . ")");
		}
		$iRatePlanCount			= $oPlanSelectResult->numRows();
		$iRatePlanProgression	= 0;
		while ($aRatePlan = $oPlanSelectResult->fetchRow())
		{
			$iRatePlanProgression++;
			$this->outputMessage("\t[+] ({$iRatePlanProgression}/{$iRatePlanCount}) Processing RatePlan #{$aRatePlan['Id']}...\n");
			
			$iIncludedDataKB	= max(0, (int)$aRatePlan['included_data']);
			$fUsageLimit		= max(0, (int)$aRatePlan['UsageCap']);
			
			$this->outputMessage("\t\t[*] Included Data: {$iIncludedDataKB}KB\n");
			$this->outputMessage("\t\t[*] Included General Usage: \${$fUsageLimit}\n");
			
			//	1.1:	Create a Discount for Data
			if ($iIncludedDataKB)
			{
				$fIncludedDataMB			= $iIncludedDataKB / 1024;
				$sLimitDescription			= ($fIncludedDataMB >= 1024) ? (floor(($fIncludedDataMB / 1024) * 100) / 100).'GB' : floor($fIncludedDataMB).'MB';
				$sDataDiscountName			= "Data Usage ({$sLimitDescription})";
				$sDataDiscountDescription	= "{$aRatePlan['Name']} - Data Usage - \${$sLimitDescription}";
				
				$this->outputMessage("\t\t[+] Defining Data Discount '{$sDataDiscountDescription}'...\n");
				
				//	1.1.1	Insert Discount for Data
				$this->outputMessage("\t\t\t[+] Inserting Data Discount...\n");
				$sDataDiscountInsertSQL	= "	INSERT INTO	discount
												(name	, description	, unit_limit)
											VALUES
												(".$dbAdmin->quote($sDataDiscountName, 'text').", ".$dbAdmin->quote($sDataDiscountDescription, 'text').", {$iIncludedDataKB});";
				$oDataDiscountInsertResult	= $dbAdmin->exec($sDataDiscountInsertSQL);
				if (PEAR::isError($oDataDiscountInsertResult))
				{
					throw new Exception(__CLASS__ . ' Failed to insert a Data Discount for Rate Plan #'.$aRatePlan['Id'].'. ' . $oDataDiscountInsertResult->getMessage() . " (DB Error: " . $oDataDiscountInsertResult->getUserInfo() . ")");
				}
				$iDataDiscountId		= $dbAdmin->lastInsertID();
				$this->rollbackSQL[]	= "DELETE FROM discount WHERE id = {$iDataDiscountId};";
				
				//	1.1.2	Insert Discount <-> RecordType links
				$this->outputMessage("\t\t\t[+] Inserting Data Discount <-> RecordType links...\n");
				$sDataDiscountRecordTypeInsertSQL	= "	INSERT INTO	discount_record_type
															(discount_id	, record_type_id)
														SELECT		{$iDataDiscountId},
																	rt.Id
														FROM		RecordType rt
														WHERE		rt.ServiceType = {$aRatePlan['ServiceType']}
																	AND rt.DisplayType = 3;";
				$oDataDiscountRecordTypeInsertResult	= $dbAdmin->exec($sDataDiscountRecordTypeInsertSQL);
				if (PEAR::isError($oDataDiscountRecordTypeInsertResult))
				{
					throw new Exception(__CLASS__ . ' Failed to insert Data Discount <-> RecordType link for Discount #'.$iDataDiscountId.' for Rate Plan #'.$aRatePlan['Id'].'. ' . $oDataDiscountRecordTypeInsertResult->getMessage() . " (DB Error: " . $oDataDiscountRecordTypeInsertResult->getUserInfo() . ")");
				}
				
				//	1.1.3	Insert Discount <-> RatePlan link
				$this->outputMessage("\t\t\t[+] Inserting Data Discount <-> Rate Plan links...\n");
				$sDataRatePlanDiscountInsertSQL	= "	INSERT INTO	rate_plan_discount
														(rate_plan_id	, discount_id)
													VALUES
														({$aRatePlan['Id']}, {$iDataDiscountId});";
				$oDataRatePlanDiscountInsertResult	= $dbAdmin->exec($sDataRatePlanDiscountInsertSQL);
				if (PEAR::isError($oDataRatePlanDiscountInsertResult))
				{
					throw new Exception(__CLASS__ . ' Failed to insert Data Discount <-> RatePlan link for Discount #'.$iDataDiscountId.' for Rate Plan #'.$aRatePlan['Id'].'. ' . $oDataRatePlanDiscountInsertResult->getMessage() . " (DB Error: " . $oDataRatePlanDiscountInsertResult->getUserInfo() . ")");
				}
			}
			else
			{
				$this->outputMessage("\t\t[!] No Included Data\n");
			}
			
			//	1.2:	Create a Discount for General Usage
			if ($fUsageLimit)
			{
				$sGeneralDiscountName			= "Included Usage";
				$sGeneralDiscountDescription	= "{$aRatePlan['Name']} - {$sGeneralDiscountName} - {$fUsageLimit}";
				
				$this->outputMessage("\t\t[+] Defining General Usage Discount '{$sGeneralDiscountDescription}'...\n");
				
				//	1.2.1	Insert Discount for General Usage
				$this->outputMessage("\t\t\t[+] Inserting General Usage Discount...\n");
				$sGeneralDiscountInsertSQL	= "	INSERT INTO	discount
													(name	, description	, charge_limit)
												VALUES
													(".$dbAdmin->quote($sGeneralDiscountName, 'text').", ".$dbAdmin->quote($sGeneralDiscountDescription, 'text').", {$fUsageLimit});";
				$oGeneralDiscountInsertResult	= $dbAdmin->exec($sGeneralDiscountInsertSQL);
				if (PEAR::isError($oGeneralDiscountInsertResult))
				{
					throw new Exception(__CLASS__ . ' Failed to insert a General Usage Discount for Rate Plan #'.$aRatePlan['Id'].'. ' . $oGeneralDiscountInsertResult->getMessage() . " (DB Error: " . $oGeneralDiscountInsertResult->getUserInfo() . ")");
				}
				$iGeneralDiscountId		= $dbAdmin->lastInsertID();
				$this->rollbackSQL[]	= "DELETE FROM discount WHERE id = {$iGeneralDiscountId};";
				
				//	1.2.2	Insert Discount <-> RecordType links
				$this->outputMessage("\t\t\t[+] Inserting General Usage Discount <-> RecordType links...\n");
				$sGeneralDiscountRecordTypeInsertSQL	= "	INSERT INTO	discount_record_type
																(discount_id	, record_type_id)
															SELECT		{$iGeneralDiscountId},
																		rt.Id
															FROM		RecordType rt
															WHERE		rt.ServiceType = {$aRatePlan['ServiceType']}
																		AND rt.DisplayType != 3;";
				$oGeneralDiscountRecordTypeInsertResult	= $dbAdmin->exec($sGeneralDiscountRecordTypeInsertSQL);
				if (PEAR::isError($oGeneralDiscountRecordTypeInsertResult))
				{
					throw new Exception(__CLASS__ . ' Failed to insert General Usage Discount <-> RecordType link for Discount #'.$iGeneralDiscountId.' for Rate Plan #'.$aRatePlan['Id'].'. ' . $oGeneralDiscountRecordTypeInsertResult->getMessage() . " (DB Error: " . $oGeneralDiscountRecordTypeInsertResult->getUserInfo() . ")");
				}
				
				//	1.2.3	Insert Discount <-> RatePlan link
				$this->outputMessage("\t\t\t[+] Inserting General Usage Discount <-> Rate Plan links...\n");
				$sGeneralRatePlanDiscountInsertSQL	= "	INSERT INTO	rate_plan_discount
															(rate_plan_id	, discount_id)
														VALUES
															({$aRatePlan['Id']}, {$iGeneralDiscountId});";
				$oGeneralRatePlanDiscountInsertResult	= $dbAdmin->exec($sGeneralRatePlanDiscountInsertSQL);
				if (PEAR::isError($oGeneralRatePlanDiscountInsertResult))
				{
					throw new Exception(__CLASS__ . ' Failed to insert General Usage Discount <-> RatePlan link for Discount #'.$iDataDiscountId.' for Rate Plan #'.$aRatePlan['Id'].'. ' . $oGeneralRatePlanDiscountInsertResult->getMessage() . " (DB Error: " . $oGeneralRatePlanDiscountInsertResult->getUserInfo() . ")");
				}
			}
			else
			{
				$this->outputMessage("\t\t[!] No Included General Usage\n");
			}
		}
		
		// 2:	Correct Data Rate cap inclusiveness
		$this->outputMessage("[+] Fixing Data Rate cap inclusiveness...\n");
		$sDataRateFixSQL	= "	UPDATE  Rate r
								        JOIN RecordType rt ON (rt.Id = r.RecordType AND rt.DisplayType = 3)
								        JOIN RateGroupRate rgr ON (rgr.Rate = r.Id)
								        JOIN RatePlanRateGroup rprg ON (rprg.RateGroup = rgr.RateGroup)
								        JOIN RatePlan rp ON (rp.Id = rprg.RatePlan)
								SET	    r.Uncapped = 0
								WHERE   rp.included_data IS NOT NULL
								        AND rp.included_data > 0
								        AND r.Uncapped = 1;";
		$oDataRateFixResult	= $dbAdmin->exec($sDataRateFixSQL);
		if (PEAR::isError($oDataRateFixResult))
		{
			throw new Exception(__CLASS__ . ' Failed to correct Data Rate cap inclusiveness. ' . $oDataRateFixResult->getMessage() . " (DB Error: " . $oDataRateFixResult->getUserInfo() . ")");
		}
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>