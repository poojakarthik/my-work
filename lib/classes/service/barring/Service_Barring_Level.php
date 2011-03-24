<?php
/**
 * Service_Barring_Level
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Service_Barring_Level
 */
class Service_Barring_Level extends ORM_Cached
{
	protected 			$_strTableName			= "service_barring_level";
	protected static	$_strStaticTableName	= "service_barring_level";

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize()
	{
		return 100;
	}

	public static function getLastActionedBarringLevelForAccount($iAccountId)
    {
    	$aResult = Query::run("	SELECT	a.Id AS account_id,
										sbl.barring_level_id AS barring_level_id,
										COUNT(DISTINCT sbl.service_id) AS service_count,
										sbl.actioned_datetime AS actioned_datetime
								FROM	service_barring_level sbl
								JOIN	Service s ON (s.Id = sbl.service_id)
								JOIN	Account a ON (a.Id = s.Account)
								WHERE	sbl.actioned_datetime = (
											SELECT	MAX(sbl_2.actioned_datetime)
											FROM	service_barring_level sbl_2
											JOIN	Service s_2 ON (s_2.Id = sbl_2.service_id)
											JOIN	Account a_2 ON (a_2.Id = s_2.Account)
											WHERE	a_2.Id = a.Id
											AND		sbl_2.actioned_datetime IS NOT NULL
										)
								AND		a.Id = <account_id>;",
								array('account_id' => $iAccountId))->fetch_assoc();
		
		return (($aResult['barring_level_id'] !== null) ? $aResult : null);
    }

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public function authorise()
	{
		$this->authorised_datetime 		= DataAccess::getDataAccess()->getNow();
		$this->authorised_employee_id	= Flex::getUserId()!==null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
		$this->save();
	}

	public function action()
	{
		$this->actioned_datetime 	= DataAccess::getDataAccess()->getNow();
		$this->actioned_employee_id	= Flex::getUserId()!==null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
		$this->save();
	}

	public function getScheduledCountOnDayForBarringLevel($sDate=null, $iBarringLevelId)
	{
		if ($sDate === null)
		{
			$sDate = date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
		}

		$oSelect 	= self::_preparedStatement('selScheduleOnDateForBarringLevel');
		$mResult	= $oSelect->Execute(array('effective_date' => $sDate, 'barring_level_id' => $iBarringLevelId));
		if ($mResult === false)
		{
			throw new Exception("Failed to get count of scheduled barrings on '{$sDate}', for barring level '{$iBarringLevel}'.".$oSelect->Error());
		}

		return $oSelect->Count();
	}

	public static function getAuthorisationLedger($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		// Build where clause using filter object
		$aWhereLines = array();

		if ($oFilter)
		{
			// account_id
			if ($oFilter->account_id)
			{
				$aWhereLines[] = "barring_level_union.account_id = {$oFilter->account_id}";
			}

			// barring_level_id
			if ($oFilter->barring_level_id)
			{
				$aWhereLines[] = "barring_level_union.barring_level_id = {$oFilter->barring_level_id}";
			}

			// current_barring_level_id
			if ($oFilter->current_barring_level_id)
			{
				$aWhereLines[] = "barring_level_union.current_barring_level_id = {$oFilter->current_barring_level_id}";
			}

			// created_employee_id
			if ($oFilter->created_employee_id)
			{
				$aWhereLines[] = "barring_level_union.created_employee_id = {$oFilter->created_employee_id}";
			}

			// created_datetime
			if ($oFilter->created_datetime)
			{
				if ($oFilter->created_datetime->mFrom && $oFilter->created_datetime->mTo)
				{
					$aWhereLines[] = "barring_level_union.created_datetime BETWEEN '{$oFilter->created_datetime->mFrom}' AND '{$oFilter->created_datetime->mTo}'";
				}
				else if ($oFilter->created_datetime->mFrom)
				{
					$aWhereLines[] = "barring_level_union.created_datetime >= '{$oFilter->created_datetime->mFrom}'";
				}
				else if ($oFilter->created_datetime->mTo)
				{
					$aWhereLines[] = "barring_level_union.created_datetime <= '{$oFilter->created_datetime->mTo}'";
				}
			}

			// collection_scenario_id
			if ($oFilter->collection_scenario_id)
			{
				$aWhereLines[] = "barring_level_union.collection_scenario_id = {$oFilter->collection_scenario_id}";
			}
		}

		// Finalise where clause
		$sWhere	= (count($aWhereLines) > 0 ? "WHERE ".implode(" AND ", $aWhereLines) : '');

		// Build other clauses depending on bCountOnly
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(DISTINCT account_id) AS count";
			$sOrderBy 	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect	= "	account_id,
					        account_name,
					        customer_group_id,
					        customer_group_name,
					        COUNT(service_barring_level_id) AS service_barring_level_id_count,
					        account_barring_level_id,
					        barring_level_id,
					        barring_level_name,
					        current_barring_level_id,
					        IF (barring_level_id = current_barring_level_id, concat(current_barring_level_name, ' (With Pending Barring Request)'), current_barring_level_name) AS current_barring_level_name,
					        created_datetime,
					        created_employee_id,
					        created_employee_name,
					        collection_scenario_id,
					        collection_scenario_name";
			$sOrderBy 	= Statement::generateOrderBy(null, get_object_vars($oSort));
			$sOrderBy 	= ($sOrderBy != '' ? "ORDER BY {$sOrderBy}" : '');
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
			$sLimit 	= ($sLimit != '' ? "LIMIT {$sLimit}" : '');
		}

		$sBarringUnrestricted = Constant_Group::getConstantGroup('barring_level')->getConstantName(BARRING_LEVEL_UNRESTRICTED);

		// Build query using all clauses
		$sQuery	= "	SELECT  {$sSelect}
					FROM    (
					            (
					                SELECT      a.Id AS account_id,
					                            a.BusinessName AS account_name,
												a.CustomerGroup AS customer_group_id,
												cg.internal_name AS customer_group_name,
												sbl.id AS service_barring_level_id,
					                            abl.id AS account_barring_level_id,
												sbl.barring_level_id AS barring_level_id,
					                            bl.name AS barring_level_name,
												COALESCE(current_barring_level.barring_level_id, ".BARRING_LEVEL_UNRESTRICTED.") AS current_barring_level_id,
												COALESCE(current_barring_level.barring_level_name, '{$sBarringUnrestricted}') AS current_barring_level_name,
												sbl.created_datetime AS created_datetime,
												sbl.created_employee_id AS created_employee_id,
												CONCAT(e.FirstName, ' ', e.LastName) AS created_employee_name,
												cs.id AS collection_scenario_id,
												cs.name AS collection_scenario_name
					                FROM        service_barring_level sbl
					                JOIN        Service s ON (s.Id = sbl.service_id)
					                JOIN        Account a ON (a.Id = s.Account)
									JOIN		CustomerGroup cg ON (cg.Id = a.CustomerGroup)
					                JOIN		barring_level bl ON (bl.id = sbl.barring_level_id)
									JOIN		Employee e ON (e.Id = sbl.created_employee_id)
									LEFT JOIN	account_collection_scenario acs ON (
													acs.id = (
														SELECT	id
														FROM	account_collection_scenario USE INDEX (fk_account_collection_scenario_account_id)
														WHERE	account_id = a.Id
														AND		NOW() BETWEEN start_datetime AND end_datetime
														ORDER BY created_datetime DESC
														LIMIT	1
													)
												)
									LEFT JOIN	collection_scenario cs ON (cs.id = acs.collection_scenario_id)
					                LEFT JOIN   account_barring_level abl ON (
					                                abl.id = sbl.account_barring_level_id
					                                AND abl.authorised_datetime IS NULL
					                                AND abl.authorised_employee_id IS NULL
					                            )
									LEFT JOIN	(
													SELECT  abl.account_id, abl.barring_level_id, bl.name AS barring_level_name
													FROM    account_barring_level abl
													JOIN    service_barring_level sbl ON (
													            sbl.account_barring_level_id = abl.id
													            AND sbl.actioned_datetime  = (
													                SELECT  MAX(actioned_datetime)
													                FROM    service_barring_level
													                WHERE   account_barring_level_id = abl.id
													                AND     actioned_datetime IS NOT NULL
													            )
													        )
													JOIN	barring_level bl ON (bl.id = abl.barring_level_id)
													GROUP BY abl.account_id
												) current_barring_level ON (current_barring_level.account_id = a.Id)
									WHERE       sbl.authorised_datetime IS NULL
					                AND         sbl.authorised_employee_id IS NULL
									AND sbl.created_datetime = (SELECT MAX(created_datetime) FROM service_barring_level WHERE service_barring_level.service_id = sbl.service_id)
					            )
					            UNION
					            (
					                SELECT      abl.account_id AS account_id,
												a.BusinessName AS account_name,
												a.CustomerGroup AS customer_group_id,
												cg.internal_name AS customer_group_name,
					                            sbl.id AS service_barring_level_id,
					                            abl.id AS account_barring_level_id,
												abl.barring_level_id AS barring_level_id,
					                            bl.name AS barring_level_name,
												COALESCE(current_barring_level.barring_level_id, ".BARRING_LEVEL_UNRESTRICTED.") AS current_barring_level_id,
												COALESCE(current_barring_level.barring_level_name, '{$sBarringUnrestricted}') AS current_barring_level_name,
												abl.created_datetime AS created_datetime,
												abl.created_employee_id AS created_employee_id,
												CONCAT(e.FirstName, ' ', e.LastName) AS created_employee_name,
												cs.id AS collection_scenario_id,
												cs.name AS collection_scenario_name
					                FROM        account_barring_level abl
									JOIN		Account a ON (a.Id = abl.account_id)
									JOIN		CustomerGroup cg ON (cg.Id = a.CustomerGroup)
					                JOIN		barring_level bl ON (bl.id = abl.barring_level_id)
									JOIN		Employee e ON (e.Id = abl.created_employee_id)
									LEFT JOIN	account_collection_scenario acs ON (
													acs.id = (
														SELECT	id
														FROM	account_collection_scenario USE INDEX (fk_account_collection_scenario_account_id)
														WHERE	account_id = a.Id
														AND		NOW() BETWEEN start_datetime AND end_datetime
														ORDER BY created_datetime DESC
														LIMIT	1
													)
												)
									LEFT JOIN	collection_scenario cs ON (cs.id = acs.collection_scenario_id)
					                LEFT JOIN   service_barring_level sbl ON (
					                                sbl.account_barring_level_id = abl.id
					                                AND sbl.authorised_datetime IS NULL
					                                AND sbl.authorised_employee_id IS NULL
					                            )
									LEFT JOIN	(
													SELECT  abl.account_id, abl.barring_level_id, bl.name AS barring_level_name
													FROM    account_barring_level abl
													JOIN    service_barring_level sbl ON (
													            sbl.account_barring_level_id = abl.id
													            AND sbl.actioned_datetime  = (
													                SELECT  MAX(actioned_datetime)
													                FROM    service_barring_level
													                WHERE   account_barring_level_id = abl.id
													                AND     actioned_datetime IS NOT NULL
													            )
													        )
													JOIN	barring_level bl ON (bl.id = abl.barring_level_id)
													GROUP BY abl.account_id
												) current_barring_level ON (current_barring_level.account_id = a.Id)
									WHERE       abl.authorised_datetime IS NULL
					                AND         abl.authorised_employee_id IS NULL
									AND abl.created_datetime = (SELECT max(created_datetime) FROM account_barring_level WHERE account_id = abl.account_id )
					            )
					        ) barring_level_union
					{$sWhere}
					GROUP BY account_id, barring_level_id
					{$sOrderBy}
					{$sLimit};";

		// Execute the query
		$oQuery 	= new Query();
		$mResult	= $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception("Failed to get barring authorise ledger dataset. ".$oQuery->Error());
		}

		if ($bCountOnly)
		{
			// Return the record count only
			$aCountRow = $mResult->fetch_assoc();
			return ($aCountRow['count'] !== null ? $aCountRow['count'] : 0);
		}

		// Group results by account_id
		$aResults = array();
		while ($aRow = $mResult->fetch_assoc())
		{
			$aResults[] = $aRow;
		}

		return $aResults;
	}

	public static function getActionLedger($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases = array(
						'id' 						=> "sbl.id",
						'account_id' 				=> "s.Account",
						'account_name' 				=> "a.BusinessName",
						'customer_group_name' 		=> "cg.internal_name",
						'service_fnn' 				=> "s.FNN",
						'created_datetime' 			=> "sbl.created_datetime",
						'created_employee_id' 		=> "sbl.created_employee_id",
						'created_employee_name'		=> "CONCAT(e_created.FirstName, ' ', e_created.LastName)",
						'authorised_datetime' 		=> "sbl.authorised_datetime",
						'authorised_employee_id' 	=> "sbl.authorised_employee_id",
						'authorised_employee_name'	=> "CONCAT(e_authorised.FirstName, ' ', e_authorised.LastName)",
						'barring_level_id' 			=> "sbl.barring_level_id",
						'barring_level_name' 		=> "bl.name",
						'service_type_id'			=> "st.id",
						'service_type_name'			=> "st.name",
						'carrier_id'				=> "(
												            if(
												                st.const_name = 'SERVICE_TYPE_LAND_LINE',
												                if(
												                    bl.const_name = 'BARRING_LEVEL_BARRED', /* Barring */
												                    c_pre.Id,
												                    c_full.Id
												                ),
												                c_full.Id
												            )
														)",
				        'carrier_name'		        => "(
												            if(
												                st.const_name = 'SERVICE_TYPE_LAND_LINE',
												                if(
												                    bl.const_name = 'BARRING_LEVEL_BARRED', /* Barring */
												                    c_pre.Name,
												                    c_full.Name
												                ),
												                c_full.Name
												            )
												        )"
					);

		$sFrom	= "	service_barring_level sbl
					JOIN		Service s ON (s.Id = sbl.service_id)
					JOIN		Account a ON (a.Id = s.Account)
					JOIN		CustomerGroup cg ON (cg.Id = a.CustomerGroup)
					JOIN 		barring_level bl ON (bl.id = sbl.barring_level_id)
					JOIN 		Employee e_authorised ON (e_authorised.Id = sbl.authorised_employee_id)
					JOIN 		Employee e_created ON (e_created.Id = sbl.created_employee_id)
					JOIN    	service_type st ON (st.id = s.ServiceType)
					JOIN    	ServiceRatePlan srp ON (
					           		srp.Service = s.Id
					           		AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
					        	)
					JOIN    	RatePlan rp ON (rp.Id = srp.RatePlan)
					JOIN    	Carrier c_pre ON (c_pre.Id = rp.CarrierPreselection)
					JOIN    	Carrier c_full ON (c_full.Id = rp.CarrierFullService)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(sbl.id) AS count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "{$aAliases['id']} AS service_barring_level_id,
						{$aAliases['account_id']} AS account_id,
						{$aAliases['account_name']} AS account_name,
						{$aAliases['customer_group_name']} AS customer_group_name,
						{$aAliases['service_fnn']} AS service_fnn,
						{$aAliases['authorised_datetime']} AS authorised_datetime,
						{$aAliases['authorised_employee_id']} AS authorised_employee_id,
						{$aAliases['authorised_employee_name']} AS authorised_employee_name,
						{$aAliases['created_datetime']} AS created_datetime,
						{$aAliases['created_employee_id']} AS created_employee_id,
						{$aAliases['created_employee_name']} AS created_employee_name,
						{$aAliases['barring_level_id']} AS barring_level_id,
						{$aAliases['barring_level_name']} AS barring_level_name,
						{$aAliases['service_type_id']} AS service_type_id,
						{$aAliases['service_type_name']} AS service_type_name,
						{$aAliases['carrier_id']} AS carrier_id,
						{$aAliases['carrier_name']} AS carrier_name";
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}

		$aWhere 	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere		= $aWhere['sClause'];
		$sWhere		.= ($sWhere != '' ? " AND " : '')."sbl.created_datetime = (SELECT MAX(created_datetime) FROM service_barring_level WHERE service_barring_level.service_id = sbl.service_id AND service_barring_level.authorised_datetime IS NOT NULL) AND sbl.actioned_datetime IS NULL AND sbl.actioned_employee_id IS NULL";
		$oSelect	=	new StatementSelect(
							$sFrom,
							$sSelect,
							$sWhere,
							$sOrderBy,
							$sLimit
						);

		if ($oSelect->Execute($aWhere['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to get Service Barring Level items. ". $oSelect->Error());
		}

		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}

		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['service_barring_level_id']] = $aRow;
		}

		return $aResults;
	}

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selScheduleOnDateForBarringLevel':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "(<effective_date> BETWEEN authorised_datetime AND actioned_datetime) AND barring_level_id = <barring_level_id>", "id ASC");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>