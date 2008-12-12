<?php

/*
 *  Extends the DO_Sales_Sale class, for actions specific to the Flex project
 */
class Sales_Sale extends DO_Sales_Sale
{
	public static function getForId($intSaleId)
	{
		$doSale = parent::getForId($intSaleId);
		
		if ($doSale !== NULL)
		{
			$arrProps = parent::getPropertyNames();
			$arrData = array();
			
			foreach ($arrProps as $strProp)
			{
				$arrData[$strProp] = $doSale->{$strProp};
			}
			return new self($arrData, TRUE);
		}
		else
		{
			return NULL;
		}
	}

	// Overrides the DO_Sales_Sale->cancel() function to include the flex specific operations that must be carried out, when a sale is cancelled
	// This assumes it is being executed within a transaction on the flex database
	public function cancel($dealerId, $strReason=NULL)
	{
		if ($strReason === NULL)
		{
			$strReason = "Sale cancelled";
		}
		
		$dataSource = $this->getDataSource();
		$strTransactionName = 'CancelSale' . $this->id;

		// Begin a transaction on the Sales database
		$dataSource->beginTransaction($strTransactionName);
		
		// Create a new savepoint within the transaction on the Flex database
		$objQuery = new Query(FLEX_DATABASE_CONNECTION_DEFAULT);
		if ($objQuery->Execute("SAVEPOINT {$strTransactionName}") === FALSE)
		{
			throw new Exception("Failed to create the transaction savepoint '$strTransactionName' on the flex database - ". print_r($objQuery->Error(), TRUE));
		}
		
		try
		{
			$this->saleStatusId = DO_Sales_SaleStatus::CANCELLED;
			$this->save($dealerId, $strReason);

			// Check if there is an external reference in the sale account record
			$doSaleAccount = DO_Sales_SaleAccount::getForSale($this, TRUE);
			
			if ($doSaleAccount->externalReference !== NULL && substr($doSaleAccount->externalReference, 0, 11) == "Account.Id=")
			{
				// The external reference is in the format "Account.Id=123"
				$intAccountId = intval(substr($doSaleAccount->externalReference, 11));
				
				if ($intAccountId === 0)
				{
					throw new Exception("Invalid external reference to the account associated with the sale (external reference: {$doSaleAccount->externalReference})");
				}
				
				$objDealer = Dealer::getForId($dealerId);
				$intEmployeeId = ($objDealer->employeeId !== NULL)? $objDealer->employeeId : Employee::SYSTEM_EMPLOYEE_ID;

				$objAccount = Account::getForId($intAccountId);
				if ($objAccount === NULL)
				{
					throw new Exception("Could not find account with id: $intAccountId");
				}

				// Modifications to the Account, depends on what sort of sale it is
				switch ($this->saleTypeId)
				{
					case DO_Sales_SaleType::NEW_CUSTOMER:
						//TODO! do NEW CUSTOMER SaleType specific actions to the account here
						break;
						
					case DO_Sales_SaleType::EXISTING_CUSTOMER:
						//TODO! do EXISTING CUSTOMER SaleType specific actions to the account here
						break;
						
					case DO_Sales_SaleType::WIN_BACK:
						//TODO! do WIN BACK SaleType specific actions to the account here
						break;
						
					default:
						throw new Exception("Unknown Sale Type: {$this->saleTypeId}");
						break;
				}
			}
			
			// We also want to cancel all of the sale items
			$saleItems = Sales_SaleItem::listForSale($this);
			foreach ($saleItems as $saleItem)
			{
				if ($saleItem->saleItemStatusId != DO_Sales_SaleItemStatus::CANCELLED)
				{
					// The item can be cancelled
					$saleItem->cancel($dealerId, $strReason);
				}
			}
			
			// Create a system Note for the flex Account, if an account exists
			if (isset($objAccount) && $objAccount !== NULL)
			{
				$strNote = "Sale {$this->id} has been cancelled during its cooling off period.\nReason given: $strReason";
				Note::createSystemNote($strNote, $objDealer->employeeId, $objAccount->accountGroup, $objAccount->id);
			}
			
			$dataSource->commit($strTransactionName);
			if ($objQuery->Execute("RELEASE SAVEPOINT {$strTransactionName}") === FALSE)
			{
				throw new Exception("Failed to release the transaction savepoint '$strTransactionName' on the flex database - ". $objQuery->Error());
			}
		}
		catch (Exception $e)
		{
			$dataSource->rollback($strTransactionName);
			if ($objQuery->Execute("ROLLBACK TO SAVEPOINT {$strTransactionName}") === FALSE)
			{
				throw new Exception($e->getMessage() . "\n- Also: Failed to rollback the transaction savepoint '$strTransactionName' on the flex database - ". $objQuery->Error());
			}
			throw $e;
		}
	}
}
?>
