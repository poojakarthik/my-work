<?php

class Account
{
	const BALANCE_REDISTRIBUTION_REGULAR = 1;
	const BALANCE_REDISTRIBUTION_FORCED = 2;
	const BALANCE_REDISTRIBUTION_FORCED_INCLUDING_ARCHIVED = 3;

	protected static $cache = array();

	private	$_arrTidyNames	= array();
	private	$_arrProperties	= array();

	public function __construct($arrProperties=NULL, $bolPropertiesIncludeEmployeeDetails=FALSE, $bolLoadById=FALSE)
	{
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('Account');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$this->_arrProperties[$strName]					= NULL;
			$this->_arrTidyNames[self::tidyName($strName)]	= $strName;
		}
		$this->_arrProperties[$arrTableDefine['Id']]				= NULL;
		$this->_arrTidyNames[self::tidyName($arrTableDefine['Id'])]	= $arrTableDefine['Id'];

		// Automatically load the Invoice using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : (($arrProperties['id']) ? $arrProperties['id'] : NULL);
		if ($bolLoadById && $intId)
		{
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId)))
			{
				$arrProperties	= $selById->Fetch();
			}
			elseif ($selById->Error())
			{
				throw new Exception_Database("DB ERROR: ".$selById->Error());
			}
			else
			{
				throw new Exception(__CLASS__." with Id {$intId} does not exist!");
			}
		}

		// Set Properties
		if (is_array($arrProperties))
		{
			foreach ($arrProperties as $strName=>$mixValue)
			{
				// Load from the Database
				$this->{$strName}	= $mixValue;
			}
		}
	}

	public function getBalance()
	{
		// TODO: Implement the account balance functionality here
		$framework = function_exists('Framework') ? Framework() : Flex::framework();
		return $framework->GetAccountBalance($this->id);
	}

	/**
	 * listServices
	 *
	 * returns array of Service objects defining the current service records associated with this account, for active services (based on the status)
	 *
	 * returns array of Service objects defining the current service records associated with this account, for active services (based on the status)
	 *
	 * @return	array of Service objects
	 */
	public function listActiveServices()
	{
		return $this->listServices(array(SERVICE_ACTIVE));
	}

	/**
	 * listServices
	 *
	 * returns array of Service objects defining the current service records associated with this account
	 *
	 * returns array of Service objects defining the current service records associated with this account
	 *
	 * @param	array	$arrStatuses	Defines the services to retrieve based on the statuses
	 * 									(Optional, defaults to NULL, in which services of all statuses will be retrieved)
	 *
	 * @return	array of Service objects
	 */
	public function listServices($arrStatuses=NULL)
	{
		if (is_array($arrStatuses) && count($arrStatuses))
		{
			$strStatusConstraint = "AND Status IN (". implode(", ", $arrStatuses) .")";
		}
		else
		{
			$strStatusConstraint = "";
		}

		$qryQuery = new Query();
		$strQuery = "
				SELECT *
				FROM Service
				WHERE Id IN (
					/* Find the maximum id for each FNN associated with the account, as this record defines the current state of the service for this account */
					SELECT Max(Id)
					FROM Service
					WHERE Account = {$this->id}
					GROUP BY Account, FNN
				)
				AND Account = {$this->id}
				AND (ClosedOn IS NULL OR ClosedOn >= CreatedOn)
				$strStatusConstraint
				ORDER BY FNN;";

		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception_Database("Failed to retrieve services for Account: {$this->id} - " . $qryQuery->Error() ." - Query: $strQuery");
		}

		$arrServices = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrServices[$arrRecord['Id']] = new Service($arrRecord, FALSE);
		}

		return $arrServices;
	}

	// Returns the id of all service records that are associated with the account
	// If 3 Service records model the one logical service on an account, then all 3 record ids will be returned
	public function getAllServiceRecords($bolAsObjects=FALSE)
	{
		$qryQuery = new Query();

		// The only records we don't want to retrieve are those where ClosedOn < CreatedOn
		$strColumns = ($bolAsObjects)? "*" : "Id";

		$strQuery = "SELECT $strColumns FROM Service WHERE Account = {$this->id} AND (ClosedOn IS NULL OR ClosedOn >= CreatedOn);";

		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception_Database("Failed to retrieve services for Account: {$this->id} - " . $qryQuery->Error() ." - Query: $strQuery");
		}

		$arrServices = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrServices[$arrRecord['Id']] = ($bolAsObjects)? new Service($arrRecord) : $arrRecord['Id'];
		}

		return $arrServices;
	}



	public function getName()
	{
		return $this->businessName ? $this->businessName : ($this->tradingName ? $this->tradingName : '');
	}

	public function getCustomerGroup()
	{
		return Customer_Group::getForId($this->customerGroup);
	}

	// Returns a list of ContactIds or Contact objects, defining the contacts that can be associated with this account
	// In both cases, the key to the array will be the id of the contact
	// This will return an empty array if there are no Contacts for this account
	public function getContacts($bolAsObjects=FALSE)
	{
		$strQuery = "	SELECT c.Id AS ContactId
						FROM Account AS a INNER JOIN Contact AS c ON (c.CustomerContact = 1 AND a.AccountGroup = c.AccountGroup) OR (c.Account = a.Id) OR (c.Id = a.PrimaryContact)
						WHERE a.Id = {$this->id}";
		$qryQuery = new Query();

		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception_Database("Failed to retrieve contacts for account: {$this->id} - " . $qryQuery->Error());
		}

		$arrContacts = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrContacts[$arrRecord['ContactId']] = ($bolAsObjects)? Contact::getForId($arrRecord['ContactId']) : $arrRecord['ContactId'];
		}

		return $arrContacts;
	}

	public static function getForTicket(Ticketing_Ticket $objTicket)
	{
		return Account::getForId($objTicket->accountId);
	}

	/**
	 * Applies a payment to an account
	 *
	 * *** THIS HAS ONLY BEEN TESTED FOR CREDIT CARD PAYMENTS ***
	 */
	public function applyPayment($intEmployeeId, $contact, $time, $totalAmount, $txnId, $strUniqueReference, $paymentType, $creditCardNumber=NULL, $creditCardType=NULL, $surcharge=NULL)
	{
		$arrCCH = array();
		$arrPayment = array();
		$arrCharge = array();

		$arrCCH['account_id'] = $arrPayment['Account'] = $arrCharge['Account'] = $this->id;
		$arrPayment['AccountGroup'] = $arrCharge['AccountGroup'] = $this->accountGroup;
		$arrCCH['employee_id'] = $arrPayment['EnteredBy'] = $arrCharge['CreatedBy'] = $intEmployeeId;
		$arrCCH['contact_id'] = $contact->id;
		$arrCCH['receipt_number'] = $strUniqueReference;
		$arrCCH['amount'] = $arrPayment['Amount'] = $arrPayment['Balance'] = $totalAmount;
		$arrCharge['Amount'] = RemoveGST($surcharge);
		$arrCCH['payment_datetime'] = date('Y-m-d H:i:s', $time);
		$arrPayment['PaidOn'] = $arrCharge['CreatedOn'] = $arrCharge['ChargedOn'] = date('Y-m-d', $time);
		$arrCCH['txn_id'] = $arrPayment['TXNReference'] = $txnId;
		$arrPayment['OriginId'] = ($paymentType == PAYMENT_TYPE_CREDIT_CARD) ? (substr($creditCardNumber, 0, 6) . '...' . substr($creditCardNumber, -3)) : '';
		$arrPayment['Status'] = $arrCharge['Status'] = CHARGE_APPROVED;
		$arrCharge['LinkType'] = CHARGE_LINK_PAYMENT;
		$arrCharge['ChargeType'] = "CCS";
		$arrCharge['Nature'] = "DR";
		$arrCharge['global_tax_exempt'] = 0;
		$arrCharge['Description'] 		= ($paymentType == PAYMENT_TYPE_CREDIT_CARD) ? ($creditCardType->name . ' Surcharge for Payment on ' . date('d/m/Y', $time) . ' (' . $totalAmount . ') @ ' . (round(floatval($creditCardType->surcharge)*100, 2)) . '%') : '';
		$arrCharge['charge_model_id']	= CHARGE_MODEL_CHARGE;
		$arrPayment['Payment'] = $arrCharge['Notes'] = '';
		$arrPayment['PaymentType'] = $arrPayment['OriginType'] = $paymentType;
		$arrPayment['created_datetime']	= DataAccess::getDataAccess()->getNow();

		$insPayment = new StatementInsert('Payment');
		if (($paymentId = $insPayment->Execute($arrPayment)) === FALSE)
		{
			// Eak!!
			throw new Exception_Database('Failed to create payment record: ' . $insPayment->Error());
		}

		if ($paymentType == PAYMENT_TYPE_CREDIT_CARD)
		{
			$arrCCH['payment_id'] = $arrCharge['LinkId'] = $paymentId;

			$insCharge = new StatementInsert('Charge');
			if (($id = $insCharge->Execute($arrCharge)) === FALSE)
			{
				// Eak!!
				throw new Exception_Database('Failed to create payment charge: ' . $insCharge->Error());
			}

			$insCreditCardHistory = new StatementInsert('credit_card_payment_history');
			if (($id = $insCreditCardHistory->Execute($arrCCH)) === FALSE)
			{
				// Eak!!
				throw new Exception_Database('Failed to create credit card payment history: ' . $insCreditCardHistory->Error());
			}
		}
	}

	public function getInterimInvoiceType()
	{
		switch ($this->Archived)
		{
			case ACCOUNT_STATUS_ACTIVE:
				return INVOICE_RUN_TYPE_INTERIM;
				break;

			case ACCOUNT_STATUS_CLOSED:
			case ACCOUNT_STATUS_DEBT_COLLECTION:
				return INVOICE_RUN_TYPE_FINAL;
				break;
		}
		return null;
	}

	/**
	 * getBillingPeriodStart()
	 *
	 * Calculates the start of the current Billing Period for this Account
	 *
	 * @param	[string	$strEffectiveDate]				Only include Invoice Runs from before this date (defaults to Today)
	 *
	 * @return	string									Billing Period Start Date
	 *
	 * @method
	 */
	public function getBillingPeriodStart($strEffectiveDate=null, $bolProductionInvoiceRunsOnly=false)
	{
		$strEffectiveDate	= (strtotime($strEffectiveDate)) ? date("Y-m-d", strtotime($strEffectiveDate)) : date("Y-m-d");

		// Get the Account's last Invoice date
		$strAccountLastInvoiceDate			= $this->getLastInvoiceDate($strEffectiveDate, $bolProductionInvoiceRunsOnly);
		$intAccountLastInvoiceDate			= strtotime($strAccountLastInvoiceDate);

		// Get the CustomerGroup's last Invoice date (or predicted last Invoice date)
		$strCustomerGroupLastInvoiceDate	= Invoice_Run::getLastInvoiceDateByCustomerGroup($this->CustomerGroup, $strEffectiveDate);
		$intCustomerGroupLastInvoiceDate	= strtotime($strCustomerGroupLastInvoiceDate);

		return date("Y-m-d", max($intAccountLastInvoiceDate, $intCustomerGroupLastInvoiceDate));
	}

	/**
	 * getLastInvoiceDate()
	 *
	 * Retrieves (or calculates) the Last Invoice Date for this Account
	 *
	 * @param	[string	$strEffectiveDate]				Only include Invoice Runs from before this date (defaults to Today)
	 *
	 * @return	string									Date of the last Invoice Run
	 *
	 * @method
	 */
	public function getLastInvoiceDate($strEffectiveDate=null, $bolProductionInvoiceRunsOnly=false)
	{
		$strEffectiveDate	= strtotime($strEffectiveDate) ? date("Y-m-d", strtotime($strEffectiveDate)) : date("Y-m-d");

		$selPaymentTerms	= self::_preparedStatement('selPaymentTerms');

		$selInvoiceRun	= self::_preparedStatement('selLastInvoiceRun');
		if ($selInvoiceRun->Execute(Array('Account' => $this->Id, 'EffectiveDate'=>$strEffectiveDate, 'ProductionOnly'=>$bolProductionInvoiceRunsOnly)))
		{
			// We have an old InvoiceRun
			$arrLastInvoiceRun	= $selInvoiceRun->Fetch();
			return $arrLastInvoiceRun['BillingDate'] . ' 00:00:00';
		}
		elseif ($selInvoiceRun->Error())
		{
			throw new Exception_Database("DB ERROR: ".$selInvoiceRun->Error());
		}
		elseif ($selPaymentTerms->Execute(Array('customer_group_id' => $this->CustomerGroup)))
		{
			$arrPaymentTerms	= $selPaymentTerms->Fetch();

			// No InvoiceRuns, so lets calculate when it should have been
			$intInvoiceDatetime	= strtotime(date("Y-m-{$strDay} 00:00:00", strtotime($strEffectiveDate)));
			if ((int)date("d", strtotime($strEffectiveDate)) < $arrPaymentTerms['invoice_day'])
			{
				// Billing Date is last Month
				$intInvoiceDatetime	= strtotime("-1 month", $intInvoiceDatetime);
			}
			return date("Y-m-d H:i:s", $intInvoiceDatetime);
		}
		elseif ($selPaymentTerms->Error())
		{
			throw new Exception_Database("DB ERROR: ".$selPaymentTerms->Error());
		}
		else
		{
			throw new Exception("No Payment Terms specified for Customer Group {$intCustomerGroup}");
		}
	}

  	 /**
         * returns an array of Account objects representing all accounts which are:
         * 1 are currently in collections, defined by most recent account_collection_event_history record not being for the 'exit collections' event
         * 2 OR are not in collections (as defined under 1) but have collectables with a balance > 0 that are not part of an active promise
         *
         */
        public static function getForBatchCollectionsProcess($aAccountsToExclude)
        {
            $sExcluded = implode(",",$aAccountsToExclude );
            $sExcludeSql = $sExcluded == "" ? "" : "AND a.Id NOT IN ($sExcluded)";
            $sSql = "   SELECT *
                        FROM(

                                  /*retrieve accounts for which the last event was not the exit event*/
                                select a.*
                                FROM Account a
                                JOIN account_collection_event_history ach ON (  a.Id = ach.account_id
                                                                                AND ach.id = (	select max(id)
                                                                                                FROM account_collection_event_history
                                                                                                WHERE account_id = a.Id

                                                                                                )
                                                                                AND a.Id  NOT in(select account_id from collection_suspension cs where cs.start_datetime < NOW() AND cs.effective_end_datetime IS NULL)
                                                                                $sExcludeSql
                                                                              )
                                JOIN collection_event ce ON (ce.id = ach.collection_event_id)
                                JOIN collection_event_type cet ON (cet.id = ce.collection_event_type_id and (cet.system_name <> 'EXIT_COLLECTIONS' OR ach.completed_datetime IS NULL))

                                UNION

                                /*retrieve accounts with a non-promise balance > 0 */
                                select a.*
                                FROM Account a
                                JOIN account_status ast ON (a.Archived = ast.id AND ast.send_late_notice = 1
                                                            AND a.Id  NOT in(select account_id from collection_suspension cs where cs.start_datetime < now() AND cs.effective_end_datetime IS NULL)
                                                            )
                                JOIN collectable c ON (a.Id = c.account_id AND c.balance > 0 AND c.due_date < NOW())
                                LEFT JOIN collection_promise cp ON (c.collection_promise_id = cp.id )
                                WHERE  (c.collection_promise_id is null OR cp.completed_datetime is not null)
                                 $sExcludeSql


                        ) accounts 
            ";

            $oQuery = new Query();
            $mResult = $oQuery->Execute($sSql);
            $aResult = array();
            if ($mResult)
                {
                    while ($aRow = $mResult->fetch_assoc())
                    {
                            $aResult[]= new self($aRow);
                    }
                }
            return $aResult;


        }

        public function getPayables()
        {
            $oQuery = new Query();
                    $sSQL = "   CREATE TEMPORARY TABLE IF NOT EXISTS account_payable
                                (
                                        id BIGINT(20) UNSIGNED,
                                        account_id BIGINT UNSIGNED,
                                        amount DECIMAL(13,4),
                                        balance DECIMAL(13,4),
                                        created_datetime DATETIME,
                                        due_date DATE,
                                        collection_promise_id BIGINT(20) UNSIGNED,
                                        invoice_id BIGINT(20) UNSIGNED,
                                        created_employee_id BIGINT(20) UNSIGNED,
                                        modified_datetime DATETIME,
                                        modified_employee_id BIGINT(20) UNSIGNED,
                                        status_id BIGINT(20),
                                        INDEX in_account_payable_due_date (due_date)
                                )";

                    $oQuery->Execute($sSQL);

                    $oQuery->Execute("DELETE FROM account_payable");

                    $sSQL = "INSERT INTO account_payable (id, account_id, amount, balance, created_datetime, due_date, collection_promise_id, invoice_id )
                                SELECT c.*
                                FROM collectable c
                                LEFT JOIN collection_promise cp ON ( c.collection_promise_id = cp.id )
                                WHERE ( c.amount > 0  AND c.account_id = $this->Id AND (c.collection_promise_id IS NULL OR cp.completed_datetime is NOT NULL) )";

                    $oQuery->Execute($sSQL);

                    $sSQL = "INSERT INTO account_payable (id, collection_promise_id, due_date, amount, created_datetime,created_employee_id,  account_id)
							SELECT cpi.*, cp.account_id
							FROM collection_promise_instalment cpi
							JOIN collection_promise cp ON ( cp.id = cpi.collection_promise_id
															AND cp.completed_datetime IS NULL AND cp.account_id = $this->Id)";
                    $oQuery->Execute($sSQL);

                    $sSQL = "SELECT *
							  FROM account_payable
							  ORDER BY due_date, balance asc"; //ordering by balance will ensure that promise instalments come first as their balance is NULL in the temp table

                    $mResult = $oQuery->Execute($sSQL);
                    $aResult = array();
                    if ($mResult)
                    {
                        while ($aRecord = $mResult->fetch_assoc())
                        {
                            $oItem;
                            if ($aRecord['balance'] === null)
                            {
                                unset ($aRecord['account_id']);
                                unset ($aRecord['balance']);
                                $oORM = new Collection_Promise_Instalment($aRecord );
                                $aResult[] = new Logic_Collection_Promise_Instalment($oORM);
                            }
                            else
                            {
                                unset($aRecord['created_employee_id']);
                                unset($aRecord['modified_datetime']);
                                unset($aRecord['modified_employee_id']);
                                unset($aRecord['status_id']);
                                $oORM = new Collectable($aRecord);
                                $aResult[] = Logic_Collectable::getInstance($oORM, TRUE);
                            }

                        }
                    }
                    mysqli_free_result($mResult);
                    return $aResult;

        }



        public static function getForBalanceRedistribution($iRedistributionType = Account::BALANCE_REDISTRIBUTION_REGULAR, $iAccountId = NULL)
        {

            $aReport = array('gaps' =>array(), 'distributable_balance' => array(), 'amount_balance_problem' => array());
			$aAccountsForRedistribution = array();
			$iCount = 0;

            switch($iRedistributionType)
            {
                case  Account::BALANCE_REDISTRIBUTION_REGULAR:
					$sSingleAccountClause = $iAccountId === NULL ? NULL : "AND c.account_id = {$iAccountId}";
					Log::getLog()->log("Retrieving accounts that have for some reason been wrongly distributed");
                    //1 create the temporary table, and populate it with all collectables that are not part of a promise					
                    $sSQL = "   CREATE  TEMPORARY TABLE tmp_payable
                                SELECT  due_date, amount, c.account_id, balance
                                FROM collectable c
                                LEFT JOIN collection_promise cp ON ( c.collection_promise_id = cp.id)
                                WHERE ( c.collection_promise_id IS NULL OR cp.completed_datetime is NOT NULL ) {$sSingleAccountClause}";

                    Query::run($sSQL);

                    //2 for all active promises retrieve all promise instalments and amounts paid for each promise

                    $sSQL = "   SELECT cp.id as promise_id, cpi.due_date as due_date, cpi.amount as amount, cp.account_id as account_id, SUM(c.amount)-SUM(c.balance) as paid
                                FROM collection_promise_instalment cpi
                                JOIN collection_promise cp ON ( cp.id = cpi.collection_promise_id
                                                                AND cp.completed_datetime IS NULL  )
                                JOIN collectable c ON (cp.id = c.collection_promise_id {$sSingleAccountClause})
								GROUP BY cpi.id
                                ORDER BY cp.id, cpi.due_date ASC";

					$mResult =  Query::run($sSQL);
					$aResult = array();
                    if ($mResult)
                    {
						while ($aRow = $mResult->fetch_assoc())
						{
							if (!array_key_exists($aRow['promise_id'], $aResult))
								$aResult[$aRow['promise_id']]= array();
							$aResult[$aRow['promise_id']][] = $aRow;
						}
                    }

                    if (count($aResult) > 0)
                    {
                        //3 distribute the paid amount on each promise over each instalment, and insert these records into the temporary table
                        $aInsertRecords = array();
                        foreach ($aResult as $iPromiseId=>$aInstalments)
                        {
                            $fPaidAmount = $aInstalments[0]['paid'];
                            foreach ($aInstalments as $aInstalmentRecord)
                            {
                                $fAmountToApply = $aInstalmentRecord['amount'] >= $fPaidAmount ?  $fPaidAmount :  $aInstalmentRecord['amount'];
                                $fPaidAmount -= $fAmountToApply;
                                $aRecord = array('due_date' => $aInstalmentRecord['due_date'], 'amount'=>$aInstalmentRecord['amount'], 'account_id'=>$aInstalmentRecord['account_id'], 'balance'=>$aInstalmentRecord['amount'] - $fAmountToApply);
                               // $x = new StatementInsert('tmp_payable', array_keys($aRecord));
                               // $x->Execute($aRecord);
                                $sSQL = "INSERT into tmp_payable VALUES ('".$aInstalmentRecord['due_date']."', ".$aInstalmentRecord['amount'].",". $aInstalmentRecord['account_id'].",". ($aInstalmentRecord['amount'] - $fAmountToApply).")";
								Query::run($sSQL);
                            }
                        }


                    }


                    //4 retrieve the account ids of accounts that need balance redistribution from the temporary table

                    $sSQL =     "SELECT 		p.account_id as account_id,
                                MIN(IF(p.balance = 0, p.due_date, NULL))                           AS min_fully_paid,
                                MIN(IF(p.balance > 0 AND p.balance < p.amount, p.due_date, NULL))  AS min_partially_paid,
                                MIN(IF(p.balance = p.amount, p.due_date, NULL))                    AS min_fully_unpaid,
                                MAX(IF(p.balance = 0, p.due_date, NULL))                           AS max_fully_paid,
                                MAX(IF(p.balance > 0 AND p.balance < p.amount, p.due_date, NULL))  AS max_partially_paid,
                                MAX(IF(p.balance = p.amount, p.due_date, NULL))                    AS max_fully_unpaid
                                FROM     	tmp_payable p
                                WHERE p.amount > 0
                                GROUP BY 	p.account_id
                                HAVING  	min_partially_paid 	!= max_partially_paid
                                                OR max_fully_paid 	> min_partially_paid
                                                OR min_fully_unpaid 	< max_partially_paid
                                                OR min_fully_unpaid 	< max_fully_paid";
                    $mResult = Query::run($sSQL);

                    if ($mResult)
                    {
                        while ($aRow = $mResult->fetch_assoc())
                        {
                                   $aAccountsForRedistribution[] = $aRow['account_id'];
								   $aReport['gaps'][] = $aRow;
                        }
                    }
					$iCount = count($aAccountsForRedistribution);
					Log::getLog()->log("$iCount Accounts found.");

                    //5 retrieve accounts that have both distributable collectables and collectables with outstanding balances
					Log::getLog()->Log("Retrieving accounts with distributable balance AND with room for distributing these to payables");
                    $sSQL = "   SELECT DISTINCT c.account_id as account_id
                                FROM collectable c
                                JOIN collectable c2 ON (c2.account_id = c.account_id  AND c2.balance > 0 AND c.balance < 0 {$sSingleAccountClause})
                                ";
                    $mResult = Query::run($sSQL);
                    if ($mResult)
                    {
                        while ($aRow = $mResult->fetch_assoc())
                        {
							$aAccountsForRedistribution[] = $aRow['account_id'];
							$aReport['distributable_balance'][] = $aRow;
                        }
                    }

					//6 retrieve accounts that have both distributable payments and collectables these can be distributed to
                    $sSQL = "   SELECT DISTINCT c.account_id
								FROM collectable c
								JOIN payment p ON (	c.account_id = p.account_id
													AND c.amount > 0
													AND (
														  (c.balance > 0 AND p.balance > 0 AND p.payment_nature_id = 1)
														  OR (c.balance <> c.amount AND  p.balance > 0 AND p.payment_nature_id = 2)
														)
													{$sSingleAccountClause}
												  )
                                ";
                    $mResult = Query::run($sSQL);
                    if ($mResult)
                    {
                        while ($aRow = $mResult->fetch_assoc())
                        {
							$aAccountsForRedistribution[] = $aRow['account_id'];
							$aReport['distributable_balance'][] = $aRow;
                        }
                    }

					//7 retrieve accounts that have both distributable adjustments and collectables these can be distributed to
                    $sSQL = "   SELECT DISTINCT c.account_id
								FROM collectable c
								JOIN adjustment a ON (c.account_id = a.account_id AND a.balance > 0 and c.amount > 0 {$sSingleAccountClause})
								JOIN adjustment_type at ON (at.id = a.adjustment_type_id )
								JOIN transaction_nature tn ON (tn.id = at.transaction_nature_id)
								JOIN adjustment_nature an ON (an.id = a.adjustment_nature_id)
								JOIN adjustment_status ast ON (a.adjustment_status_id = ast.id and ast.const_name = 'ADJUSTMENT_STATUS_APPROVED')
								WHERE  ((c.balance > 0 AND an.value_multiplier*tn.value_multiplier = -1   ) OR (c.balance = c.amount AND an.value_multiplier*tn.value_multiplier = 1))
                                ";
                    $mResult = Query::run($sSQL);
                    if ($mResult)
                    {
                        while ($aRow = $mResult->fetch_assoc())
                        {
							$aAccountsForRedistribution[] = $aRow['account_id'];
							$aReport['distributable_balance'][] = $aRow;
                        }
                    }
					$aReport['distributable_balance'] = array_unique($aReport['distributable_balance']);
					$iCount = count($aAccountsForRedistribution) - $iCount;
					Log::getLog()->log("$iCount cases found.");
					//8 retrieve accounts that have a discrepancy between SUM(amount) and SUM(balance)
					Log::getLog()->log("retrieving accounts that have a discrepancy between SUM(amount) and SUM(balance). This points at more 'serious' cases of data corruption and should possibly be investigated.");

					$mResult = Query::run("SELECT	c2.account_id AS account_id,
													COALESCE((
																SELECT 	SUM(p.amount * pn.value_multiplier)
																FROM 	payment p
																JOIN 	payment_nature pn ON (pn.id = p.payment_nature_id )
																WHERE  p.account_id = c2.account_id
															), 0)
													+
													COALESCE((
																SELECT 	SUM(adj.amount*an.value_multiplier*tn.value_multiplier)
																FROM 	adjustment adj
																JOIN 	adjustment_type at ON (at.id = adj.adjustment_type_id )
																JOIN 	transaction_nature tn ON (tn.id = at.transaction_nature_id)
																JOIN 	adjustment_nature an ON (an.id = adj.adjustment_nature_id)
																JOIN 	adjustment_status ast ON (adj.adjustment_status_id = ast.id and ast.const_name = 'ADJUSTMENT_STATUS_APPROVED')
																WHERE adj.account_id = c2.account_id
													), 0)
													+
													COALESCE((
																SELECT 	SUM(c.amount)
																FROM 	collectable c
																WHERE 	c.account_id = c2.account_id
													), 0)
													AS balance_amounts,

													COALESCE((
																SELECT 	SUM(p.balance * pn.value_multiplier)
																FROM 	payment p
																JOIN 	payment_nature pn ON (pn.id = p.payment_nature_id )
																WHERE  p.account_id = c2.account_id
															), 0)

													+

												  COALESCE((
																SELECT 	SUM(adj.balance*an.value_multiplier*tn.value_multiplier)
																FROM 	adjustment adj
																JOIN 	adjustment_type at ON (at.id = adj.adjustment_type_id )
																JOIN 	transaction_nature tn ON (tn.id = at.transaction_nature_id)
																JOIN 	adjustment_nature an ON (an.id = adj.adjustment_nature_id)
																JOIN 	adjustment_status ast ON (adj.adjustment_status_id = ast.id and ast.const_name = 'ADJUSTMENT_STATUS_APPROVED')
																WHERE adj.account_id = c2.account_id
															), 0)

												  +

												  COALESCE ((SELECT SUM(c3.balance) FROM collectable c3 WHERE c3.account_id = c2.account_id),0)
												  AS balance_balances
												  FROM collectable c2
												  GROUP BY c2.account_id
												  HAVING (abs(balance_balances -balance_amounts) > 0.01)");


					if ($mResult)
                    {
                        while ($aRow = $mResult->fetch_assoc())
                        {
							$aAccountsForRedistribution[] = $aRow['account_id'];
							$aReport['amount_balance_problem'][] = $aRow;
                        }
                    }
					
					$iCount = count($aAccountsForRedistribution) - $iCount;
					Log::getLog()->log("$iCount Accounts found.");

					$aAccountsForRedistribution = array_unique($aAccountsForRedistribution);
					$aResult = array();
					foreach ($aAccountsForRedistribution as $iAccountId)
					{
						$aResult[] = self::getForId($iAccountId);

					}

					$aAccountsForRedistribution = $aResult;
					
					///EMAIL THE REPORT
					
					$oEmail = Correspondence_Email::getForEmailNotificationSystemName('ALERT');
					$oEmail->setSubject("Account Balance Redistribution Process Report");										
					
					if (count($aReport['gaps']) > 0 || count($aReport['distributable_balance']) || count($aReport['amount_balance_problem']))
					{
						$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
						$sFilename	= "Account_Balance_Redistribution_$sTimeStamp.csv";
						$oSpreadsheet = new Logic_Spreadsheet(array());

						foreach ($aReport as $type => $aReportRows)
						{
							if (count($aReportRows) > 0)
							{
								$oSpreadsheet->addRecord(array($type));
								self::addReportRows($oSpreadsheet, $aReportRows);
							}
							else
							{
								$oSpreadsheet->addRecord(array("No cases of '$type' were found"));
							}
						}

						$sPath = FILES_BASE_PATH.'temp/';
						$oSpreadsheet->saveAs($sPath.$sFilename, "CSV");
						$sFile = file_get_contents($sPath.$sFilename);
						$oEmail->addAttachment($sFile, $sFilename, 'text/csv');
						$oEmail->addTextHeader(4, "Report Summary (see attached csv for full report):");
						$table =& $oEmail->setTable();
						$oEmail->addPivotTableRow("gaps", count($aReport['gaps']));
						$oEmail->addPivotTableRow("distributable balance", count($aReport['distributable_balance']));
						$oEmail->addPivotTableRow("SUM(amount)<>SUM(balance)", count($aReport['amount_balance_problem']));
					}
					else
					{
						$oEmail->addTextHeader(4,"No Accounts needed balance redistribution");
					}

					$oEmployee = Employee::getForId(Flex::getUserId());
					if ($oEmployee!= null && $oEmployee->email!=null)
						$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

					$oEmail->appendSignature();
					$oEmail->setBodyHTML();
					$oEmail->send();

			/////END EMAIL REPORT		
					
					
                    break;
				case Account::BALANCE_REDISTRIBUTION_FORCED:

					$sSingleAccountClause = $iAccountId === NULL ? NULL : "AND a.Id = {$iAccountId}";
					$sSQL = "    SELECT DISTINCT a.*
								FROM Account a
								WHERE a.Archived <> ".ACCOUNT_STATUS_ARCHIVED." {$sSingleAccountClause} LIMIT 200 ";

					$mResult = Query::run($sSQL);
					if ($mResult)
					{
						while ($aRow = $mResult->fetch_assoc())
						{
							$aAccountsForRedistribution[] = new self($aRow);
						}
					}

					break;
              case Account::BALANCE_REDISTRIBUTION_FORCED_INCLUDING_ARCHIVED:
				  $sSingleAccountClause = $iAccountId === NULL ? NULL : "WHERE  a.Id = {$iAccountId}";

                   $sSQL = "    SELECT DISTINCT a.*
                                FROM Account a {$sSingleAccountClause}
                                ";				              

                    $mResult = Query::run($sSQL);
                    if ($mResult)
                    {
                        while ($aRow = $mResult->fetch_assoc())
                        {
                            $aAccountsForRedistribution[] = new self($aRow);
                        }
                    }
               default:
                       //try to resolve it to an account id
					   if ($iAccountId != NULL)
					   {
						   $mResult = self::getForId($iAccountId);
						   if ($mResult != null)
							   $aAccountsForRedistribution[] = $mResult;
					   }

            }

           return $aAccountsForRedistribution;


        }

		private static function addReportRows(&$oReport, $aRows = array())
		{
			$aColumns = array_keys($aRows[0]);
			$oReport->addRecord($aColumns);
			foreach($aRows as $aRecord)
			{
				$aValues = array_values($aRecord);
				$oReport->addRecord($aValues);

			}
		}

        /**
         * adapted from (functions) ListLatePaymentAccounts
         * this method will return account data in the right format for the pdf generation functions to work properly
         */
        public static function getAccountDataForLateNotice($aAccountIds)
        {
            $sAccountIds = implode(", ", $aAccountIds);
            $arrColumns = array(

		'AccountId'			=> "a.Id",
		'BusinessName'			=> "a.BusinessName",
		'CustomerGroup'			=> "cg.Id",
                'CustomerGroupName'		=> "cg.external_name",
		'DeliveryMethod'		=> "a.BillingMethod",
		'FirstName'			=> "c.FirstName",
		'LastName'			=> "c.LastName",
		'Email'				=> "c.Email",
		'Title'				=> "c.Title",
		'AddressLine1'			=> "a.Address1",
		'AddressLine2'			=> "a.Address2",
		'Suburb'			=> "UPPER(a.Suburb)",
		'Postcode'			=> "a.Postcode",
		'State'				=> "a.State",
                'Mobile'			=> "c.Mobile",
		'Landline'			=> "c.Phone",
		'InvoiceId'			=> "'InvoiceId'",
		'OutstandingNotOverdue'		=> "'OutstandingNotOverdue'",
		'Overdue'			=> "'Overdue'",
		'TotalOutstanding'		=> "'TotalOutstanding'",

            );

            $strTables	= " Account a
                                JOIN Contact c ON (c.Id = a.PrimaryContact)
                                JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)";
            $strWhere	= "a.Id in ($sAccountIds)";
            $strOrderBy	= "a.Id ASC";

            $oAccounts = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "");
            $mxdReturn =  $oAccounts->Execute();
            if ($mxdReturn !== FALSE)
            {
                    $mxdReturn =  $oAccounts->FetchAll();
            }
            return $mxdReturn;

        }

	private static function getFor($where, $arrWhere, $bolAsArray=FALSE)
	{
		$selUsers = new StatementSelect(
			"Account",
			self::getColumns(),
			$where);
		if (($outcome = $selUsers->Execute($arrWhere)) === FALSE)
		{
			throw new Exception_Database("Failed to check for existing account: " . $selUsers->Error());
		}
		if (!$outcome && !$bolAsArray)
		{
			return NULL;
		}

		$records = array();
		while ($props = $selUsers->Fetch())
		{
			if (!array_key_exists($props['Id'], self::$cache))
			{
				self::$cache[$props['Id']] = new Account($props);
			}
			$records[] = self::$cache[$props['Id']];
			if (!$bolAsArray)
			{
				return $records[0];
			}
		}
		return $records;
	}

	public static function getForId($id)
	{
		if (array_key_exists($id, self::$cache))
		{
			return self::$cache[$id];
		}
		$account = self::getFor("Id = <Id>", array("Id" => $id));
		return $account;
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach($arrColumns as $strColumn)
		{
			if ($strColumn == 'id')
			{
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	}

	//	$intEmployeeId is used for the account_history record which records the state of the account
	//		Setting this to NULL will make it use the system employee id (Account_History::SYSTEM_ACTION_EMPLOYEE_ID)
	//
	//	$bRecordInHistory should almost always be TRUE.
	//		The only exception is Account Creation where we need an Account Id in order to save our Primary Contact
	//
	public function save($intEmployeeId=NULL, $bRecordInHistory=true)
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}

		// Do we have an Id for this instance?
		if ($this->Id !== NULL)
		{
			// Update
			$ubiSelf	= self::_preparedStatement("ubiSelf");
			if ($ubiSelf->Execute($this->toArray()) === FALSE)
			{
				throw new Exception_Database("DB ERROR: ".$ubiSelf->Error());
			}
		}
		else
		{
			// Insert
			$insSelf	= self::_preparedStatement("insSelf");
			$mixResult	= $insSelf->Execute($this->toArray());
			if ($mixResult === FALSE)
			{
				throw new Exception_Database("DB ERROR: ".$insSelf->Error());
			}
			if (is_int($mixResult))
			{
				$this->Id	= $mixResult;
			}
			else
			{
				throw new Exception_Database('Failed to save account details: ' . $statement->Error());
			}
		}

		if ($bRecordInHistory)
		{
			// Record the new state of the account
			Account_History::recordCurrentState($this->Id, $intEmployeeId);
		}

		$this->_saved = TRUE;
		return TRUE;
	}

	// Gets the latest rebill for the account (if any)
	public function getRebill()
	{
		return Rebill::getForAccountId($this->Id);
	}

	// Gets the available payment methods for this accounts customer group
	public function getPaymentMethods()
	{
		$oCustomerGroup	= Customer_Group::getForId($this->CustomerGroup);
		return $oCustomerGroup->getPaymentMethods();
	}

	public function getPaymentMethod()
	{
		$oBillingType	= Billing_Type::getForId($this->BillingType);
		return Payment_Method::getForId($oBillingType->payment_method_id);
	}

	public function getPaymentMethodDetails()
	{
		$oBillingType	= Billing_Type::getForId($this->BillingType);
		$mPaymentMethod	= null;

		switch ($oBillingType->payment_method_id)
		{
			case PAYMENT_METHOD_ACCOUNT:
				$mPaymentMethod	= PAYMENT_METHOD_ACCOUNT;
				break;

			case PAYMENT_METHOD_DIRECT_DEBIT:
				if ($this->CreditCard)
				{
					$mPaymentMethod	= Credit_Card::getForId($this->CreditCard);
				}
				else if ($this->DirectDebit)
				{
					$mPaymentMethod	= DirectDebit::getForId($this->DirectDebit);
				}
				break;

			case PAYMENT_METHOD_REBILL:
				$mPaymentMethod	= Rebill::getForAccountId($this->Id);
				break;

			default:
				$mPaymentMethod	= PAYMENT_METHOD_ACCOUNT;
		}

		return $mPaymentMethod;
	}

	public function getUnbilledAdjustments()
	{
	    $sSQL = "	Select COALESCE (SUM(adj.balance*an.value_multiplier*tn.value_multiplier), 0) balance
			FROM adjustment adj
			JOIN adjustment_type at ON (at.id = adj.adjustment_type_id and adj.account_id = {$this->Id} AND adj.invoice_run_id IS NULL)
			JOIN transaction_nature tn ON (tn.id = at.transaction_nature_id)
			JOIN adjustment_nature an ON (an.id = adj.adjustment_nature_id)
			JOIN adjustment_status ast ON (adj.adjustment_status_id = ast.id and ast.const_name = 'ADJUSTMENT_STATUS_APPROVED')";


			$oQuery = new Query();
			$mResult = $oQuery->Execute($sSQL);
			if ($mResult)
			{
			    $aResult	= $mResult->fetch_assoc();
			    return (float)$aResult['balance'];
			}
			else
			{
			    throw new Exception_Database($oQuery->Error());
			}

	}

	public function oldGetUnbilledAdjustments($bIncludeCreditAdjustments=true, $bIncludeDebitAdjustments=true)
	{
		$iIncludeCreditAdjustments	= ($bIncludeCreditAdjustments) ? 1 : 0;
		$iIncludeDebitAdjustments	= ($bIncludeDebitAdjustments) ? 1 : 0;

		// This query uses a logarithm workaround to simulate a PRODUCT() aggregate function
		// Defined at: http://codeidol.com/sql/sql-hack/Number-Crunching/Multiply-Across-a-Result-Set/
		// Essentially says that to replicate a PRODUCT() aggregate function, use EXP(SUM(LN(rate))) to get the compound rate
		$oQuery		= new Query();
		$mResult	= $oQuery->Execute("	SELECT		COALESCE(
															SUM(
																COALESCE(
																	IF(
																		c.Nature = 'CR',
																		0 - c.Amount,
																		c.Amount
																	), 0
																)
																*
																IF(
																	c.global_tax_exempt = 1,
																	1,
																	(
																		SELECT		COALESCE(EXP(SUM(LN(1 + tt.rate_percentage))), 1)
																		FROM		tax_type tt
																		WHERE		c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
																					AND tt.global = 1
																	)
																)
															), 0
														)																						AS unbilled_adjustments
											FROM		Charge c
											WHERE		c.Account = {$this->Id}
														AND c.Status IN (101, 102)	/* Approved or Temp Invoice */
														AND c.charge_model_id IN (SELECT id FROM charge_model WHERE system_name = 'ADJUSTMENT')
														AND
														(
															(1 = 1 AND c.Nature = 'CR')
															OR
															(0 = 1 AND c.Nature = 'DR')
														)");
		if ($mResult === false)
		{
			throw new Exception_Database($oQuery->Error());
		}
		else
		{
			$aResult	= $mResult->fetch_assoc();
			return (float)$aResult['unbilled_adjustments'];
		}
	}

	public function setBarringLevel($iBarringLevel, $iAuthorisedEmployeeId = null)
	{
	    $oAccountBarringLevel = new  Account_Barring_Level();
	    $sNow = Data_Source_Time::currentTimestamp();
	    $oAccountBarringLevel->account_id = $this->Id;
	    $iUserId = Flex::getUserId()!==null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
	    if ($iAuthorisedEmployeeId !== null)
	    {

		$oAccountBarringLevel-> authorised_datetime = $sNow;
		$oAccountBarringLevel->authorised_employee_id = $iAuthorisedEmployeeId;
	    }
	    $oAccountBarringLevel->created_datetime = $sNow;
	    $oAccountBarringLevel->created_employee_id =  $iUserId;
	    $oAccountBarringLevel->barring_level_id = $iBarringLevel;
	    $oAccountBarringLevel->save();

	    //TODO: process the barring level for each service on the account
	    //create a record for each service in the service_barring_level table (retrieve the services as follows.......)
	    $aServices = $this->getServicesForBarring();

	    foreach ($aServices as $oService)
	    {
		$oServiceBarringLevel                           = new Service_Barring_Level();
		$oServiceBarringLevel->service_id               = $oService->id;
		$oServiceBarringLevel->barring_level_id         = $iBarringLevel;
		$oServiceBarringLevel->created_datetime         = DataAccess::getDataAccess()->getNow();
		$oServiceBarringLevel->created_employee_id      =  $iUserId;

		$oServiceBarringLevel->account_barring_level_id = $oAccountBarringLevel->id;


		if ($iAuthorisedEmployeeId !== null)
		{
		    $oServiceBarringLevel-> authorised_datetime = $sNow;
		    $oServiceBarringLevel->authorised_employee_id = $iAuthorisedEmployeeId;
		}

		$oServiceBarringLevel->save();

		if ($iAuthorisedEmployeeId !== null)
		{
		    if (Logic_Service::canServiceBeAutomaticallyBarred($oServiceBarringLevel->service_id, $oServiceBarringLevel->barring_level_id))
		    {
			  // ... it is possible action & create provisioning request
			  $oServiceBarringLevel->action();
			  switch ($oServiceBarringLevel->barring_level_id)
			  {
				case BARRING_LEVEL_UNRESTRICTED:
				      $iProvisioningTypeId = PROVISIONING_TYPE_UNBAR;
				      break;
				case BARRING_LEVEL_BARRED:
				      $iProvisioningTypeId = PROVISIONING_TYPE_BAR;
				      break;
				case BARRING_LEVEL_TEMPORARY_DISCONNECTION:
				      $iProvisioningTypeId = PROVISIONING_TYPE_DISCONNECT_TEMPORARY;
				      break;
			  }

			  Logic_Service::createProvisioningRequest($oServiceBarringLevel->service_id, $iProvisioningTypeId, $sNow, $iAuthorisedEmployeeId);
		    }
		}
	    }
	}

	public function getServicesForBarring()
	{
	    // Get all barrable services for the account
	    $oQuery = new Query();
	    $sSQL = " SELECT *
					  FROM   Service
					  INNER JOIN  (
								SELECT MAX(Service.Id) serviceId
								FROM Service
								WHERE
								(
										Service.ClosedOn IS NULL
										OR NOW() < Service.ClosedOn
								)
								AND Service.CreatedOn < NOW()
								AND Service.FNN IN (SELECT FNN FROM Service WHERE Account = {$this->Id})
								GROUP BY Service.FNN
						    ) CurrentService ON (
											Service.Account = {$this->Id}
											AND Service.Id = CurrentService.serviceId
											AND Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.", ".SERVICE_ARCHIVED.")
									)
					  ORDER BY            Service.FNN ASC;";
	    $mResult = $oQuery->Execute( $sSQL);
	    if ($mResult === false)
	    {
			    throw new Exception("Failed to get barrable services for account.".$oQuery->Error());
	    }
	    $aResult = array();
	    while ( $aRecord = $mResult->fetch_assoc())
	    {
		$aResult[] = new Service($aRecord);
	    }

	    return $aResult;

	}

	public function close($bSetDefaultPlanOnServices = false)
	{

	    //set the status on the account
	    $this->Archived		= ACCOUNT_STATUS_CLOSED;
	    $this->save();
	    $oHistory		    = new  Account_Status_History();
	    $oHistory->account	    = $this->Id;
	    $oHistory->from_status	    = $this->Archived;
	    $oHistory->to_status	    = ACCOUNT_STATUS_CLOSED;
	    $oHistory->employee	    = AuthenticatedUser()->GetUserId();
	    $oHistory->change_datetime  = Data_Source_Time::currentTimestamp();
	    $oHistory->save();

	    //disconnect all services and set them to the default plan
	    $aServices = $this->getAllServiceRecords(true);

	    foreach ($aServices as $oService)
	    {
		$aServicesForFNN			= Service::getFNNInstances($oService->FNN, $this->id, false);
		$oServiceToDisconnect		= Logic_Service::getForId(array_pop($aServicesForFNN)->Id);
		if($oServiceToDisconnect->Status	!= SERVICE_ARCHIVED && $oServiceToDisconnect->Status !=SERVICE_DISCONNECTED)
		{
		    $mResult = $oServiceToDisconnect->ChangeStatus(SERVICE_DISCONNECTED);
		    if ($bSetDefaultPlanOnServices)
		    {
			$mDefaultRatePlan = $oServiceToDisconnect->getDefaultRatePlan();
			$mCurrentPlan = $oServiceToDisconnect->getCurrentPlan();
			if ($mDefaultRatePlan !== null && $mCurrentPlan->Id != $mDefaultRatePlan->Id)
			{
			    $oServiceToDisconnect->changePlan($mDefaultRatePlan->Id);
			    $aCDRs = $oServiceToDisconnect->getCDRsForStatus(array(CDR_NORMALISED));
			    foreach ($aCDRs as $oCDR)
			    {
				$oCDR->rate();
				$oCDR->save();
			    }

			    if (is_numeric($mResult))
			    {
				$oNewService = Logic_Service::getForId($mResult);
				$oNewService->changePlan($mDefaultRatePlan->Id);
			    }
			}
		    }
		}
	    }
	}



	public function getAccountBalance()
	{
	    return $this->_getBalance(Data_Source_Time::END_OF_TIME, true);
	}

	public function getOverdueBalance($sEffectiveDate=null)
	{
	    $sEffectiveDate	= (is_int($iEffectiveDate = strtotime($sEffectiveDate))) ? date('Y-m-d', $iEffectiveDate) : date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
	    return max(0.0, $this->_getBalance($sEffectiveDate, FALSE));
	}

	protected function _getBalance($sEffectiveDate, $bIncludeActivePromises)
	{
	    if ($bIncludeActivePromises)
	    {
	    	// Don't exclude promise collectables
	    	$sCollectableFilterClause = "	WHERE 	c.account_id = {$this->Id} 
											AND 	(
												    	c.due_date < '{$sEffectiveDate}' 
												    	OR c.amount < 0
												    )";
	    }
	    else
	    {
	    	// Exclude promise collectables
	    	$sCollectableFilterClause = "	LEFT JOIN 	collection_promise cp ON (c.collection_promise_id = cp.id) 
											WHERE 		(c.collection_promise_id IS NULL OR cp.completed_datetime IS NOT NULL) 
											AND 		c.account_id = {$this->Id} 
											AND 		(
												            c.due_date < '{$sEffectiveDate}' 
												            OR c.amount < 0
												        )";
	    }
	    
	    $oQuery = new Query();
	    $sQuery = "	SELECT	COALESCE((
								SELECT 	SUM(p.amount * pn.value_multiplier)
								FROM 	payment p
								JOIN 	payment_nature pn ON (pn.id = p.payment_nature_id AND p.account_id = {$this->Id})
							), 0)
							+
							COALESCE((
								SELECT 	SUM(adj.amount*an.value_multiplier*tn.value_multiplier)
								FROM 	adjustment adj
								JOIN 	adjustment_type at ON (at.id = adj.adjustment_type_id and adj.account_id = {$this->Id})
								JOIN 	transaction_nature tn ON (tn.id = at.transaction_nature_id)
								JOIN 	adjustment_nature an ON (an.id = adj.adjustment_nature_id)
								JOIN 	adjustment_status ast ON (adj.adjustment_status_id = ast.id and ast.const_name = 'ADJUSTMENT_STATUS_APPROVED')
							), 0)
							+
							COALESCE((
								SELECT 	SUM(c.amount)
								FROM 	collectable c
								{$sCollectableFilterClause}
							), 0)
							AS balance";
	    $mResult = $oQuery->Execute ($sQuery);

	    if ($mResult === false)
	    {
		    throw new Exception_Database($oQuery->Error());
	    }
	    else
	    {
		    $aResult	= $mResult->fetch_assoc();
		    return (float)$aResult['balance'];
	    }

	}
	
	// getHistoricalBalance: 	Retrieves the balance as at the given effective date. Can optionally return the overdue balance.
	// 							Can optionally return the 'revised' balance, which means that payment reversals that affect payments 
	//							before the effective date are included.
	public function getHistoricalBalance($mEffectiveDate=null, $bOverdueBalance=false, $bRevisedBalance=false) {
		// Effective (start) date defaults to the now if not given
		$sEffectiveDate = ($mEffectiveDate === null) ? DataAccess::getDataAccess()->getNow() : $mEffectiveDate;
		
		// The effective end date is calculated to be the end of the current billing period or if at the end of the current
		// billing period, the end of the next billing period. Effective date +1 second is given as the effective when calculating
		// the end of the current billing period to account for the 'at end of current billing period' scenario.
		$sEffectiveEndDate = self::getNextBillingPeriodEndDatetime(date('Y-m-d H:i:s', strtotime($sEffectiveDate) + 1));
		
		// Add overdue clause if needed
		$sOverdueClause = '';
		if ($bOverdueBalance) {
			$sOverdueClause	= "	AND (
						            c.due_date < <effective_date> 
						            OR c.amount < 0
						        )";
		}
		
		// Payments are selected differently depending on bRevised
		$sPaymentBalanceClause = "AND p.created_datetime < <effective_date>";
		if ($bRevisedBalance) {
			$sPaymentBalanceClause = "	AND (
											/* Payment was created prior to the Billing Period Start, or is reversing a Payment prior to the Billing Period Start */
											p.created_datetime < <effective_date>
											OR (
												p_reversed.id IS NOT NULL
												AND p_reversed.created_datetime < <effective_date>
											)
										)";
		}
		
		// Got effective end date, get the balance
		$mResult	= Query::run("
			SELECT		COALESCE((
							SELECT	SUM(c.amount)
							FROM	collectable c
							JOIN	Invoice i ON (i.Id = c.invoice_id)
							WHERE	c.account_id = a.Id
									AND i.billing_period_end_datetime <= <effective_date>
									{$sOverdueClause}
						), 0)
						+
						COALESCE((
							SELECT	SUM(p.amount * pn.value_multiplier)
							FROM	payment p
									JOIN payment_nature pn ON (pn.id = p.payment_nature_id)
									LEFT JOIN payment p_reversed ON (p_reversed.id = p.reversed_payment_id)
							WHERE	p.account_id = a.Id
									{$sPaymentBalanceClause}
						), 0)
						+
						COALESCE((
							SELECT	SUM(adj.amount * adjn.value_multiplier * tn.value_multiplier)
							FROM	adjustment adj
									JOIN adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
									JOIN adjustment_type_invoice_visibility adjtiv ON (adjtiv.id = adjt.adjustment_type_invoice_visibility_id)
									JOIN adjustment_nature adjn ON (adjn.id = adj.adjustment_nature_id)
									JOIN transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
									JOIN adjustment_status adjs ON (adjs.id = adj.adjustment_status_id)
									LEFT JOIN adjustment adj_reversed ON (adj_reversed.id = adj.reversed_adjustment_id)
									LEFT JOIN adjustment_status adjs_reversed ON (adjs_reversed.id = adj_reversed.adjustment_status_id)
							WHERE	adjs.system_name = 'APPROVED'
									AND adj.account_id = a.id
									AND (
										/* Adjustment was charged and approved prior to the Billing Period Start, or is reversing an Adjustment charged and approved prior to the Billing Period Start */
										(
											adj.created_datetime < <effective_date>
											AND adj.effective_date < <effective_date>
											AND adj.reviewed_datetime < <effective_date>
										)
										OR (
											adj_reversed.id IS NOT NULL
											AND adjs_reversed.system_name = 'APPROVED'
											AND adj_reversed.created_datetime < <effective_date>
											AND adj_reversed.effective_date < <effective_date>
											AND adj_reversed.reviewed_datetime < <effective_date>
										)
										/* We also want to include any Adjustments intentionally hidden between the Billing Period Start and End */
										OR (
											adjtiv.system_name = 'HIDDEN'
											AND adj.effective_date BETWEEN <effective_date> AND <effective_end_date>
											AND adj.reviewed_datetime BETWEEN <effective_date> AND <effective_end_date>
										)
									)
						), 0) AS balance
			FROM		Account a
			WHERE		a.Id = <account_id>
		", array(
			'account_id'			=> (int)$this->Id,
			'effective_date'		=> $sEffectiveDate,
			'effective_end_date'	=> $sEffectiveEndDate
		));
		
		return ($mResult) ? array_value($mResult->fetch_assoc(), 'balance') : 0.0;
	}

	public function oldGetOverdueBalance($sDueDate=null, $bIncludeCreditAdjustments=true, $bIncludeDebitAdjustments=false, $bIncludePayments=true)
	{
		$sDueDate	= (is_int($iDueDate = strtotime($sDueDate))) ? date('Y-m-d', $iDueDate) : date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
		return max(0.0, $this->_getBalance($sDueDate, $bIncludeCreditAdjustments, $bIncludeDebitAdjustments, $bIncludePayments));
	}

	public function oldGetAccountBalance($bIncludeCreditAdjustments=true, $bIncludeDebitAdjustments=false, $bIncludePayments=true)
	{
		return $this->_getBalance(Data_Source_Time::END_OF_TIME, $bIncludeCreditAdjustments, $bIncludeDebitAdjustments, $bIncludePayments);
	}

	protected function _oldGetBalance($sDueDate, $bIncludeCreditAdjustments=true, $bIncludeDebitAdjustments=false, $bIncludePayments=true)
	{
		$iIncludeCreditAdjustments	= ($bIncludeCreditAdjustments)	? 1 : 0;
		$iIncludeDebitAdjustments	= ($bIncludeDebitAdjustments)	? 1 : 0;
		$iIncludePayments			= ($bIncludePayments)			? 1 : 0;

		// This query uses a logarithm workaround to simulate a PRODUCT() aggregate function
		// Defined at: http://codeidol.com/sql/sql-hack/Number-Crunching/Multiply-Across-a-Result-Set/
		// Essentially says that to replicate a PRODUCT() aggregate function, use EXP(SUM(LN(rate))) to get the compound rate
		$oQuery		= new Query();
		$mResult	= $oQuery->Execute("	SELECT		COALESCE(SUM(i.Balance), 0)
														+
														(
															SELECT		COALESCE(
																			SUM(
																				COALESCE(
																					IF(
																						c.Nature = 'CR',
																						0 - c.Amount,
																						c.Amount
																					), 0
																				)
																				*
																				IF(
																					c.global_tax_exempt = 1,
																					1,
																					(
																						SELECT		COALESCE(EXP(SUM(LN(1 + tt.rate_percentage))), 1)
																						FROM		tax_type tt
																						WHERE		c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
																									AND tt.global = 1
																					)
																				)
																			), 0
																		)
															FROM		Charge c
															WHERE		c.Account = a.Id
																		AND c.Status IN (101, 102)	/* Approved or Temp Invoice */
																		AND c.charge_model_id IN (SELECT id FROM charge_model WHERE system_name = 'ADJUSTMENT')
																		AND
																		(
																			({$iIncludeCreditAdjustments} = 1 AND c.Nature = 'CR')
																			OR
																			({$iIncludeDebitAdjustments} = 1 AND c.Nature = 'DR')
																		)
														)
														-
														IF({$iIncludePayments} = 0, 0,	COALESCE((
																							SELECT	SUM(p.Balance)	AS balance
																							FROM	Payment p
																							WHERE	p.Status IN (101, 103, 150)
																									AND p.Account = a.Id
																						), 0)
																					)																															AS balance

											FROM		Account a
														LEFT JOIN Invoice i ON	(
																					a.Id = i.Account
																					AND i.Status != 106	/* Ignore Written Off */
																					AND i.DueOn < '{$sDueDate}'
																				)
														LEFT JOIN InvoiceRun ir ON	(
																						i.invoice_run_id = ir.Id
																					)

											WHERE		a.Id = {$this->Id}
														AND
														(
															ir.Id IS NULL
															OR ir.invoice_run_status_id = (SELECT id FROM invoice_run_status WHERE const_name = 'INVOICE_RUN_STATUS_COMMITTED')
														);");
		if ($mResult === false)
		{
			throw new Exception_Database($oQuery->Error());
		}
		else
		{
			$aResult	= $mResult->fetch_assoc();
			return (float)$aResult['balance'];
		}
	}

	public function getActivePromise() {
		return Collection_Promise::getCurrentForAccountId($this->Id);
	}

	public function getActiveSuspension() {
		return Collection_Suspension::getActiveForAccount($this->Id);
	}

	public function getNextBillingPeriodEndDatetime($sEffectiveDate=null)
	{
		$sEffectiveDate = ($sEffectiveDate === null ? DataAccess::getDataAccess()->getNow() : $sEffectiveDate);
		
		// Look for an invoice with a billing period end datetime after (or on) the effective date
		$aRow = Query::run("SELECT	*
							FROM	Invoice
							WHERE	billing_period_end_datetime >= <effective_datetime>
							AND		Account = <account_id>
							ORDER BY CreatedOn ASC
							LIMIT 	1",
							array('effective_datetime' => $sEffectiveDate, 'account_id' => $this->Id))->fetch_assoc();
		if ($aRow)
		{
			return $aRow['billing_period_end_datetime'];
		}
		
		// Calculate the effective end date
		$aPaymentTerms = Query::run("	SELECT	*
										FROM	payment_terms
										WHERE	customer_group_id = <customer_group_id>
										ORDER BY id DESC
										LIMIT 1",
									array('customer_group_id' => $this->CustomerGroup))->fetch_assoc();
		if (!$aPaymentTerms)
		{
			throw new Exception("No Payment Terms specified for Customer Group {$this->CustomerGroup}");
		}
		
		// See if the effective date is after (or at) the end of next billing period, if so move to the end of the next one (+1 month)
		$sEndDay		= str_pad(($aPaymentTerms['invoice_day'] - 1), 2, '0', STR_PAD_LEFT);
		$iEndDatetime	= strtotime(date("Y-m-{$sEndDay} 23:59:59", strtotime($sEffectiveDate)));
		if (strtotime($sEffectiveDate) > $iEndDatetime)
		{
			// End Date is next Month
			$iEndDatetime = strtotime("+1 month", $iEndDatetime);
		}
		
		return date("Y-m-d H:i:s", $iEndDatetime);
	}

	// Empties the cache
	public static function emptyCache()
	{
		self::$cache = array();
	}

	protected static function getColumns()
	{
		return array(
			'Id',
			'BusinessName',
			'TradingName',
			'ABN',
			'ACN',
			'Address1',
			'Address2',
			'Suburb',
			'Postcode',
			'State',
			'Country',
			'BillingType',
			'PrimaryContact',
			'CustomerGroup',
			'CreditCard',
			'DirectDebit',
			'AccountGroup',
			'LastBilled',
			'BillingDate',
			'BillingFreq',
			'BillingFreqType',
			'BillingMethod',
			'PaymentTerms',
			'CreatedBy',
			'CreatedOn',
			'DisableDDR',
			'DisableLatePayment',
			'DisableLateNotices',
			'LatePaymentAmnesty',
			'Sample',
			'Archived',
			'credit_control_status',
			'last_automatic_invoice_action',
			'last_automatic_invoice_action_datetime',
			'automatic_barring_status',
			'automatic_barring_datetime',
			'tio_reference_number',
			'vip',
			'account_class_id',
			'collection_severity_id'
		);
	}

	public function __get($strName)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	public function __set($strName, $mxdValue)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;

		if (array_key_exists($strName, $this->_arrProperties))
		{
			$mixOldValue					= $this->_arrProperties[$strName];
			$this->_arrProperties[$strName]	= $mxdValue;

			if ($mixOldValue !== $mxdValue)
			{
				$this->_saved = FALSE;
			}
		}
		else
		{
			$this->{$strName} = $mxdValue;
		}
	}

	private function tidyName($name)
	{
		if (preg_match("/^[A-Z]+$/", $name)) $name = strtolower($name);
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}

	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray()
	{
		return $this->_arrProperties;
	}

	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	private static function _preparedStatement($strStatement)
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Account", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selLastInvoiceRun':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun JOIN Invoice ON Invoice.invoice_run_id = InvoiceRun.Id", "InvoiceRun.Id AS InvoiceRunId, BillingDate", "Invoice.Account = <Account> AND InvoiceRun.BillingDate < <EffectiveDate> AND (invoice_run_type_id = ".INVOICE_RUN_TYPE_LIVE." OR <ProductionOnly> = 0) AND invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED, "BillingDate DESC", 1);
					break;
				case 'selPaymentTerms':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("payment_terms", "*", "customer_group_id = <customer_group_id>", "id DESC", 1);
					break;
				case 'selByAccountGroup':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Account", "*", "AccountGroup = <AccountGroup> AND Archived = <Archived>");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Account");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Account");
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
