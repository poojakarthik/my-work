<?php

class DO_Sales_Sale extends DO_Sales_Base_Sale
{
	const SEARCH_CONSTRAINT_VENDOR_ID		= "vendor";
	const SEARCH_CONSTRAINT_SALE_TYPE_ID	= "sale_type";
	const SEARCH_CONSTRAINT_SALE_STATUS_ID	= "sale_status";
	const SEARCH_CONSTRAINT_DEALER_ID		= "dealer";
	const SEARCH_CONSTRAINT_MANAGER_ID		= "manager"; // includes sales from all dealers under the manager
	const SEARCH_CONSTRAINT_SEARCH_STRING	= "search_string";
	
	
	const ORDER_BY_CONTACT_NAME		= "contact_name";
	const ORDER_BY_ACCOUNT_NAME		= "account_name";
	const ORDER_BY_SALE_ID			= "sale_id";
	const ORDER_BY_LAST_ACTIONED_ON	= "last_actioned_on";
	const ORDER_BY_SALE_STATUS_ID	= "sale|sale_status_id";
	const ORDER_BY_CREATED_BY		= "sale|created_by";
	const ORDER_BY_CREATED_ON		= "sale|created_on";
	const ORDER_BY_SALE_TYPE_ID		= "sale|sale_type_id";
	const ORDER_BY_SALE_ACCOUNT_VENDOR_ID = "sale_account|vendor_id";
	const ORDER_BY_VERIFIED_ON		= "verified_on";
	
	
	private static $_arrPaginationDetails = array(	"TotalRecordCount"	=> NULL,
													"PageRecordCount"	=> NULL,
													"CurrentOffset"		=> NULL,
													"FirstOffset"		=> NULL,
													"PreviousOffset"	=> NULL,
													"NextOffset"		=> NULL,
													"LastOffset"		=> NULL
												);
	
	// Note that this currently only handles "prop IS NULL", "prop IN (list of unquoted values)", "prop = unquoted value"
	private static function _prepareSearchConstraint($strProp, $mixValue)
	{
		$strSearch = "";
		if ($mixValue === NULL)
		{
			$strSearch = "$strProp IS NULL";
		}
		elseif (is_array($mixValue))
		{
			$strSearch = "$strProp IN (". implode(", ", $mixValue) .")";
		}
		else
		{
			$strSearch = "$strProp = $mixValue";
		}
		return $strSearch;
	}
	
	// Performs a sale search based on lots of different things
	// It is assumed that none of the arguments are escaped yet
	public static function searchFor($arrFilter=NULL, $arrSort=NULL, $intLimit=NULL, $intOffset=NULL)
	{
		$dataSource			= self::getDataSource();
		$arrWhereParts		= array();
		$arrOrderByParts	= array();
		
		// Build WHERE clause
		$arrWhereClauseParts = array();
		if (is_array($arrFilter))
		{
			foreach ($arrFilter as $arrConstraint)
			{
				if (!(is_array($arrConstraint) && array_key_exists("Type", $arrConstraint)))
				{
					// Search constraint declaration is invalid
					continue;
				}
				
				switch ($arrConstraint['Type'])
				{
					case self::SEARCH_CONSTRAINT_MANAGER_ID:
						$intDealerId = intval($arrConstraint['Value']);
						if (($doManager = DO_Sales_Dealer::getForId($intDealerId)) === NULL)
						{
							throw new Exception(__METHOD__ ." can't find dealer with id: $intDealerId");
						}
						
						$arrDealers = $doManager->getSubordinates();
						$arrDealerIds = array($intDealerId);
						foreach ($arrDealers as $doDealer)
						{
							$arrDealerIds[] = $doDealer->id;
						}
						$arrWhereClauseParts[] = self::_prepareSearchConstraint("sale.created_by", $arrDealerIds);
						break;
	
					case self::SEARCH_CONSTRAINT_DEALER_ID:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint("sale.created_by", $arrConstraint['Value']);
						//$arrWhereClauseParts[] = "sale.created_by = $intDealerId";
						break;
						
					case self::SEARCH_CONSTRAINT_SALE_TYPE_ID:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint("sale.sale_type_id", $arrConstraint['Value']);
						//$arrWhereClauseParts[] = "sale.sale_type_id = $intSaleTypeId";
						break;
						
					case self::SEARCH_CONSTRAINT_SALE_STATUS_ID:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint("sale.sale_status_id", $arrConstraint['Value']);
						//$arrWhereClauseParts[] = "sale.sale_status_id = $intSaleStatusId";
						break;
						
					case self::SEARCH_CONSTRAINT_VENDOR_ID:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint("sale_account.vendor_id", $arrConstraint['Value']);
						//$arrWhereClauseParts[] = "sale_account.vendor_id = $intVendorId";
						break;
						
					case self::SEARCH_CONSTRAINT_SEARCH_STRING:
						// Simplify whitespace 
						$strSearchString = trim(preg_replace('/\s+/', " ", $dataSource->escape($arrConstraint['Value'])));
						if (strlen($strSearchString) == 0)
						{
							// There is no search string
							// Switch statements are considered a continuable construct in php so we need to break out of 2 loops
							continue 2;
						}
						
						// A string has been supplied.  Check it against sale id, account name and contact name (this could be quite slow especially because I don't think we index these fields)
						// Maybe we should index them, but it shouldn't be an issue for a while because in the last 5 months there has only been about 1000 sales in the system
						$arrSearchStringConstraintParts = array();
						
						// Sale Id
						$intSearchStringAsNumber = intval($strSearchString);
						if ($intSearchStringAsNumber > 0)
						{
							$arrSearchStringConstraintParts[] = "(sale.id = $intSearchStringAsNumber)";
						}
						
						// Account Name
						// The account name must have each token of the search string in it (but limit it to 5 tokens)
						$arrSearchStringTokens = explode(" ", $strSearchString, 5);
						
						$arrBusinessNamePartChecks = array();
						$arrTradingNamePartChecks = array();
						
						foreach ($arrSearchStringTokens as $strToken)
						{
							$arrBusinessNamePartChecks[]	= "sale_account.business_name ILIKE '%{$strToken}%'";
							$arrTradingNamePartChecks[]		= "sale_account.trading_name ILIKE '%{$strToken}%'";
							
						}
						
						$arrSearchStringConstraintParts[] = "(". implode(" AND ", $arrBusinessNamePartChecks) .")";
						$arrSearchStringConstraintParts[] = "(". implode(" AND ", $arrTradingNamePartChecks) .")";
						
						// Contact Name (only the primary contact)
						// Only bother checking the contact's name if the search string isn't numeric
						if (!is_numeric($strSearchString))
						{
							$arrContactNamePartChecks = array();
							
							foreach ($arrSearchStringTokens as $strToken)
							{
								$arrContactNamePartChecks[]	= "(contact.first_name || COALESCE(' ' || contact.middle_names, '') || ' ' || contact.last_name) ILIKE '%{$strToken}%'";
							}
							$arrSearchStringConstraintParts[] = "(". implode(" AND ", $arrContactNamePartChecks) .")";
						}
						
						$arrWhereClauseParts[] = "(". implode(" OR ", $arrSearchStringConstraintParts) .")";
						
					default:
						// Unknown Search constraint
						// Switch statements are considered a continuable construct in php so we need to break out of 2 loops
						continue 2;
				}
			}
		}
		$strWhereClause = (count($arrWhereClauseParts))? "WHERE ". implode(" AND ", $arrWhereClauseParts) : "";
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				switch ($strColumn)
				{
					case self::ORDER_BY_CONTACT_NAME:
					case self::ORDER_BY_ACCOUNT_NAME:
					case self::ORDER_BY_SALE_ID:
					case self::ORDER_BY_LAST_ACTIONED_ON:
					case self::ORDER_BY_SALE_STATUS_ID:
					case self::ORDER_BY_CREATED_BY:
					case self::ORDER_BY_CREATED_ON:
					case self::ORDER_BY_SALE_TYPE_ID:
					case self::ORDER_BY_SALE_ACCOUNT_VENDOR_ID:
					case self::ORDER_BY_VERIFIED_ON:
						$arrOrderByParts[] = str_replace('|', '.', $strColumn) . ($bolAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__METHOD__ ." - Illegal sorting identifier: $strColumn");
						break;
				}
			}
		}
		
		$strOrderByClause = (count($arrOrderByParts) > 0)? "ORDER BY ". implode(", ", $arrOrderByParts) : NULL;
		
		// Build LIMIT clause
		if ($intLimit !== NULL)
		{
			$strLimitClause = "LIMIT ". intval($intLimit);
			if ($intOffset !== NULL)
			{
				$strLimitClause .= " OFFSET ". intval($intOffset);
			}
			else
			{
				$intOffset = 0;
			}
		}
		else
		{
			$strLimitClause = "";
		}
		
		// Build SELECT statement
		$strSaleTableName	= self::getDataSourceObjectName();
		$arrSaleTableProps	= self::getPropertyDataSourceMappings();
		$strSaleId			= $strSaleTableName .".". self::getDataSourceIdName();
		$strSaleSaleStatus	= $strSaleTableName .".". $arrSaleTableProps["saleStatusId"];
		$strSaleCreatedBy	= $strSaleTableName .".". $arrSaleTableProps["createdBy"];
		$strSaleCreatedOn	= $strSaleTableName .".". $arrSaleTableProps["createdOn"];
		$strSaleSaleTypeId	= $strSaleTableName .".". $arrSaleTableProps["saleTypeId"];
		
		$strSaleAccountTableName	= DO_Sales_SaleAccount::getDataSourceObjectName();
		$arrSaleAccountTableProps	= DO_Sales_SaleAccount::getPropertyDataSourceMappings();
		$strSaleAccountSaleId		= $strSaleAccountTableName .".". $arrSaleAccountTableProps["saleId"];
		$strSaleAccountBusinessName	= $strSaleAccountTableName .".". $arrSaleAccountTableProps["businessName"];
		$strSaleAccountTradingName	= $strSaleAccountTableName .".". $arrSaleAccountTableProps["tradingName"];
		
		$strSaleStatusHistoryTableName	= DO_Sales_SaleStatusHistory::getDataSourceObjectName();
		$arrSaleStatusHistoryTableProps	= DO_Sales_SaleStatusHistory::getPropertyDataSourceMappings();
		$strSaleStatusHistorySaleId		= $strSaleStatusHistoryTableName .".". $arrSaleStatusHistoryTableProps["saleId"];
		$strSaleStatusHistoryChangedOn	= $strSaleStatusHistoryTableName .".". $arrSaleStatusHistoryTableProps["changedOn"];

		$strVerifiedOnTableName	= $strSaleStatusHistoryTableName ."_verified";
		$strVerifiedOnChangedOn	= $strVerifiedOnTableName .".". $arrSaleStatusHistoryTableProps["changedOn"];
		$strVerifiedOnStatusId	= $strVerifiedOnTableName .".". $arrSaleStatusHistoryTableProps["saleStatusId"];
		$strVerifiedOnSaleId	= $strVerifiedOnTableName .".". $arrSaleStatusHistoryTableProps["saleId"];
		
		$strContactSaleTableName				= DO_Sales_ContactSale::getDataSourceObjectName();
		$arrContactSaleTableProps				= DO_Sales_ContactSale::getPropertyDataSourceMappings();
		$strContactSaleSaleId					= $strContactSaleTableName .".". $arrContactSaleTableProps["saleId"];
		$strContactSaleContactAssociationTypeId	= $strContactSaleTableName .".". $arrContactSaleTableProps["contactAssociationTypeId"];
		$strContactSaleContactId				= $strContactSaleTableName .".". $arrContactSaleTableProps["contactId"];
		
		$strContactTableName	= DO_Sales_Contact::getDataSourceObjectName();
		$arrContactTableProps	= DO_Sales_Contact::getPropertyDataSourceMappings();
		$strContactId			= $strContactTableName .".". DO_Sales_Contact::getDataSourceIdName();
		$strContactFirstName	= $strContactTableName .".". $arrContactTableProps["firstName"];
		$strContactLastName		= $strContactTableName .".". $arrContactTableProps["lastName"];
		$strContactMiddleNames	= $strContactTableName .".". $arrContactTableProps["middleNames"];
		
		$intContactAssociationTypePrimary	= DO_Sales_ContactAssociationType::PRIMARY;
		$intSaleStatusIdVerified			= DO_Sales_SaleStatus::VERIFIED;
		
		$strFromClause = "FROM $strSaleTableName 
INNER JOIN $strSaleAccountTableName ON $strSaleId = $strSaleAccountSaleId
INNER JOIN (	SELECT $strSaleStatusHistorySaleId, MAX($strSaleStatusHistoryChangedOn) AS last_actioned_on
				FROM $strSaleStatusHistoryTableName
				GROUP BY $strSaleStatusHistorySaleId
				) AS $strSaleStatusHistoryTableName ON $strSaleId = $strSaleStatusHistorySaleId
LEFT JOIN $strContactSaleTableName ON ($strSaleId = $strContactSaleSaleId AND $strContactSaleContactAssociationTypeId = $intContactAssociationTypePrimary)
LEFT JOIN $strContactTableName ON ($strContactId = $strContactSaleContactId)
LEFT JOIN $strSaleStatusHistoryTableName AS $strVerifiedOnTableName ON ($strSaleId = $strVerifiedOnSaleId AND $strVerifiedOnStatusId = $intSaleStatusIdVerified)
";

		// Create query to find out how many rows there are in total
		$strRowCountQuery = "SELECT COUNT($strSaleId) as row_count $strFromClause $strWhereClause;";
		
		// Create proper query
		$strQuery = "SELECT $strSaleId AS sale_id,
COALESCE($strSaleAccountBusinessName, $strSaleAccountTradingName) AS account_name,
($strContactFirstName || COALESCE(' ' || $strContactMiddleNames, '') || ' ' || $strContactLastName) AS contact_name, 
$strSaleStatusHistoryTableName.last_actioned_on AS last_actioned_on,
$strVerifiedOnChangedOn AS verified_on
$strFromClause $strWhereClause $strOrderByClause $strLimitClause;";
		
		// Execute the query
		if (PEAR::isError($results = $dataSource->query($strQuery)))
		{
			throw new Exception('Failed to retreive records for '. __METHOD__ ." - using query: $strQuery - ". $results->getMessage());
		}

		$arrRecordSet = $results->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		$arrSales = array();
		foreach ($arrRecordSet as $arrRecord)
		{
			// I don't think the recordset will have things properly type cast, so do it here
			$intSaleId = intval($arrRecord['sale_id']);
			$arrSales[$intSaleId] = self::getForId($intSaleId);
		}

		// Perform Pagination Calculations
		if (PEAR::isError($results = $dataSource->query($strRowCountQuery)))
		{
			throw new Exception('Failed to calculate row count for '. __METHOD__ ." - ". $results->getMessage());
		}

		$intTotalRows = intval($results->fetchOne());
		
		if ($intLimit === NULL)
		{
			// All records were retrieved
			self::$_arrPaginationDetails = array(	"TotalRecordCount"	=> $intTotalRows,
													"PageRecordCount"	=> count($arrSales),
													"CurrentOffset"		=> 0,
													"FirstOffset"		=> 0,
													"PreviousOffset"	=> 0,
													"NextOffset"		=> 0,
													"LastOffset"		=> 0
												);
		}
		else
		{
			$intTotalRecordCount	= $intTotalRows;
			$intPageRecordCount		= count($arrSales);
			$intCurrentOffset		= $intOffset;
			$intFirstOffset			= 0;
			$intPreviousOffset		= max($intCurrentOffset - $intLimit, 0);
			$intLastOffset			= max(floor(($intTotalRecordCount - 1) / $intLimit) * $intLimit, 0);
			$intNextOffset			= min($intCurrentOffset + $intLimit, $intLastOffset);
			
			self::$_arrPaginationDetails = array(	"TotalRecordCount"	=> $intTotalRecordCount,
													"PageRecordCount"	=> $intPageRecordCount,
													"CurrentOffset"		=> $intCurrentOffset,
													"FirstOffset"		=> $intFirstOffset,
													"PreviousOffset"	=> $intPreviousOffset,
													"NextOffset"		=> $intNextOffset,
													"LastOffset"		=> $intLastOffset
												);
		}
		
		return $arrSales;
	}

	public static function getPaginationDetails()
	{
		return self::$_arrPaginationDetails;
	}
	
	public function save($dealerId, $comment=NULL)
	{
		$dealer = DO_Sales_Dealer::getForId($dealerId);
		if ($dealer == null)
		{
			throw new Exception('Invalid dealer ' . $dealerId . '. Unable to save ' . $this->getObjectLabel() . '.');
		}
		
		$new = $this->id == null;

		$return = parent::save();
		
		
		DO_Sales_SaleStatusHistory::recordHistoryForSale($this, $dealerId, $comment);
		
		return $return;
	}
	
	public function getSaleAccount()
	{
		$arrSaleAccounts	= DO_Sales_SaleAccount::listForSale($this);
		$intCount			= count($arrSaleAccounts);
		if ($intCount > 1)
		{
			// Multiple sale_account records relate to $doSale, which should never happen
			throw new Exception(__METHOD__ ." multiple accounts are associated with sale: {$this->id}");
		}
		elseif ($intCount == 1)
		{
			// The sale_account record was found
			return $arrSaleAccounts[0];
		}
		else
		{
			// There is no sale_account record
			return NULL;
		}
	}
	
	public function verify($dealerId)
	{
		$dataSource = $this->getDataSource();
		$strTransactionName = 'VerifySale' . $this->id;

		$dataSource->beginTransaction($strTransactionName);
		
		try
		{
			$saleItems = null;
			
			if ($this->saleStatusId == DO_Sales_SaleStatus::SUBMITTED)
			{
				$this->saleStatusId = DO_Sales_SaleStatus::VERIFIED;
				$this->save($dealerId, 'Sale verified');
	
				// We also want to verify all of the sale items
				$saleItems = DO_Sales_SaleItem::listForSale($this);
				foreach ($saleItems as $saleItem)
				{
					$saleItem->verify($dealerId);
				}
			}
			
			$this->saleStatusId = DO_Sales_SaleStatus::AWAITING_DISPATCH;
			$this->save($dealerId, 'Sale awaiting dispatch');

			// We also want to verify all of the sale items
			$saleItems = $saleItems ? $saleItems : DO_Sales_SaleItem::listForSale($this);
			foreach ($saleItems as $saleItem)
			{
				$saleItem->setAwaitingDispatch($dealerId);
			}
			
			$dataSource->commit($strTransactionName);
		}
		catch (Exception $e)
		{
			$dataSource->rollback($strTransactionName);
			throw $e;
		}
	}
	
	public function reject($dealerId)
	{
		$this->saleStatusId = DO_Sales_SaleStatus::REJECTED;
		$this->save($dealerId, 'Sale rejected');
	}
	
	public function cancel($dealerId, $strReason=NULL)
	{
		if ($strReason === NULL)
		{
			$strReason = "Item cancelled";
		}
		
		$dataSource = $this->getDataSource();
		$strTransactionName = 'CancelSale' . $this->id;

		$dataSource->beginTransaction($strTransactionName);
		
		try
		{
			$this->saleStatusId = DO_Sales_SaleStatus::CANCELLED;
			$this->save($dealerId, $strReason);

			// We also want to cancel all of the sale items
			$saleItems = DO_Sales_SaleItem::listForSale($this);
			foreach ($saleItems as $saleItem)
			{
				if ($saleItem->saleItemStatusId != DO_Sales_SaleItemStatus::CANCELLED)
				{
					// The item can be cancelled
					$saleItem->cancel($dealerId, $strReason);
				}
			}
			
			$dataSource->commit($strTransactionName);
		}
		catch (Exception $e)
		{
			$dataSource->rollback($strTransactionName);
			throw $e;
		}
	}
	
	// This will set the sale to COMPLETED, if all of its related sale items are either completed or cancelled
	// It will not do anything if the sale has already been set to completed
	// It is a precondition that the sale has been saved to the database, as well as all its sale items
	public function setCompletedOrCancelledBasedOnSaleItems($dealerId)
	{
		$arrSaleItems		= DO_Sales_SaleItem::listForSale($this);
		$intNumSaleItems	= count($arrSaleItems);
		$intNumCancelled	= 0;
		$intNumCompleted	= 0;
		$intNewStatus		= NULL;
		$strStatusDesc		= NULL;
		
		foreach ($arrSaleItems as $doSaleItem)
		{
			switch ($doSaleItem->saleItemStatusId)
			{
				case DO_Sales_SaleItemStatus::CANCELLED:
					$intNumCancelled++;
					break;
					
				case DO_Sales_SaleItemStatus::COMPLETED:
					$intNumCompleted++;
					break;
			}
		}
		
		if ($intNumCancelled == $intNumSaleItems)
		{
			// All Sale Items have been cancelled
			// Therefore the sale is cancelled
			$intNewStatus = DO_Sales_SaleStatus::CANCELLED;
			$strStatusDesc = "Cancelled because all items have been cancelled";
		}
		elseif ($intNumCompleted == $intNumSaleItems)
		{
			// All Sale Items have completed
			// Therefore the sale is completed
			$intNewStatus = DO_Sales_SaleStatus::COMPLETED;
			$strStatusDesc = "Completed because all items have been completed";
		}
		elseif (($intNumCancelled + $intNumCompleted) == $intNumSaleItems)
		{
			// All Sale Items have either been cancelled or completed, but at least one item has been completed
			// Therefore the sale is completed
			$intNewStatus = DO_Sales_SaleStatus::COMPLETED;
			$strStatusDesc = "Completed because all items have been completed or cancelled";
		}
		else
		{
			// The Sale is neither Completed or Cancelled
			$intNewStatus = $this->saleStatusId;
		}
		
		if ($intNewStatus != $this->saleStatusId)
		{
			// The sale's status needs to be updated
			$this->saleStatusId = $intNewStatus;
			$this->save($dealerId, $strStatusDesc);
		}
	}
	
	// Returns the time at which the sale was verified, or the DO_Sales_SaleStatusHistory object relating to this status milestone
	// Returns NULL if the sale has not been verified
	public function getVerificationTimestamp($bolAsObject=FALSE)
	{
		$doHistory = DO_Sales_SaleStatusHistory::getFirstOccuranceOfStatus($this, DO_Sales_SaleStatus::VERIFIED);
		
		if ($doHistory === NULL)
		{
			return NULL;
		}
		
		return ($bolAsObject) ? $doHistory : $doHistory->changedOn;
	}

	// Returns NULL if there is no cooling off period for the vendor that this sale is with
	public function getEndOfCoolingOffPeriodTimestamp()
	{
		// Get the Sale Account object
		$doSaleAccount = DO_Sales_SaleAccount::getForSale($this, TRUE);
		
		$doVendor = DO_Sales_Vendor::getForId($doSaleAccount->vendorId);
		
		return $doVendor->getEndOfCoolingOffPeriodTimestamp($this->getVerificationTimestamp());
	}

}

?>