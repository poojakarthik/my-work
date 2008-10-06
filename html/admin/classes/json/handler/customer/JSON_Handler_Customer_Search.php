<?php

class JSON_Handler_Customer_Search extends JSON_Handler
{
	const RESULTS_PER_PAGE = 10;
	
	//------------------------------------------------------------------------//
	// search
	//------------------------------------------------------------------------//
	/**
	 * search()
	 *
	 * Handles ajax request from client, to search for Accounts/Contacts/Services
	 * 
	 * Handles ajax request from client, to search for Accounts/Contacts/Services
	 *
	 * @param	string	$strConstraint				String to search on
	 * @param	string	$intConstraintType			Search string type. Defaults to NULL, in which all logical interpretations of the search string are used to search on 
	 * @param	string	$strSearchType				[optional] Search type.  Must be one of the Customer_Search::SEARCH_TYPE_ constants.  defaults to Customer_Search::SEARCH_TYPE_ACCOUNTS
	 * @param	bool	$bolIncludeArchived			[optional] flag defining whether or not to include archived items in the search. Defaults to FALSE
	 * @param	int		$intOffset					Offset into the result set
	 * @param	bool	$bolForceRefresh			[optional].  If set to FALSE the function will use the cached results for this search if they exist.  Defaults to TRUE
	 * 
	 * @return	array		["Success"]				TRUE if search was executed successfully, else FALSE
	 * 						["RecordCount"]			Number of records returned by the search (only defined on success)
	 * 						["Results"]				html defining the Results and how they are displayed(only defined on success)
	 *						["SearchType"]			$intSearchType
	 *						["Constraint"]			$strConstraint
	 *						["ConstraintType"]		$intConstraintType
	 *						["IncludeArchived"]		$bolIncludeArchived 
	 * 						["ErrorMessage"]		Declares what went wrong (only defined when Success == FALSE)
	 * @method
	 */
	public function search($intSearchType, $strConstraint, $intConstraintType=NULL, $bolIncludeArchived=FALSE, $intOffset=0, $bolForceRefresh=TRUE)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		try
		{
			// Check if the results for this search are currently cached
			if (!$bolForceRefresh && $this->_hasCachedResults($intSearchType, $strConstraint, $intConstraintType, $bolIncludeArchived))
			{
				// The user already has the results to this search, cached in their session details, and have not requested a refresh
				$arrResults = $_SESSION['CustomerSearch']['Results'];
			}
			else
			{
				// The search is not cached, or a refresh has been forced
				$strConstraint = trim($strConstraint);
				
				if ($strConstraint == "")
				{
					throw new Exception("Invalid search string");
				}
				
				// Make the constraint safe for embedding in queries
				$qryQuery = new Query();
				$strEscapedConstraint = $qryQuery->EscapeString($strConstraint);
	
				$arrResults = Customer_Search::findFor($intSearchType, $strEscapedConstraint, $intConstraintType, $bolIncludeArchived);

				// Cache the details of the search
				$_SESSION['CustomerSearch']['Results']			= $arrResults;
				$_SESSION['CustomerSearch']['SearchType']		= $intSearchType;
				$_SESSION['CustomerSearch']['Constraint']		= $strConstraint;
				$_SESSION['CustomerSearch']['ConstraintType']	= $intConstraintType;
				$_SESSION['CustomerSearch']['IncludeArchived']	= $bolIncludeArchived;
			}
			
			switch ($intSearchType)
			{
				case Customer_Search::SEARCH_TYPE_ACCOUNTS:
					$strResultsHtml = $this->_buildAccountResultsTable($arrResults, $intOffset);
					break;
					
				case Customer_Search::SEARCH_TYPE_SERVICES:
					$strResultsHtml = $this->_buildServiceResultsTable($arrResults, $intOffset);
					break;
					
				case Customer_Search::SEARCH_TYPE_CONTACTS:
					$strResultsHtml = $this->_buildContactResultsTable($arrResults, $intOffset);
					break;
			}
			
			
			return array(	"Success"			=> TRUE,
							"RecordCount"		=> count($arrResults),
							"Results"			=> $strResultsHtml,
							"SearchType"		=> $intSearchType,
							"Constraint"		=> $strConstraint,
							"ConstraintType"	=> $intConstraintType,
							"IncludeArchived"	=> $bolIncludeArchived
						);
			
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	
	// Builds the CustomerSearchPopup and populates it with the search passed, if there is one
	public function buildCustomerSearchPopup()
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		try
		{
			// Build data for the combo boxes
			$arrSearchTypes = Customer_Search::getSearchTypes();
			foreach ($arrSearchTypes as $intSearchType=>&$arrSearchType)
			{
				$arrSearchType['AllowableConstraintTypes'] = Customer_Search::getAllowableConstraintTypes($intSearchType);
			}
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody'>
	<div class='GroupedContent'>
		<form id='CustomerSearchPopupForm'>
			<table style='width:100%' cellpadding='0' cellspacing='0'>
				<tr>
					<td>Search For <select id='SearchType' name='SearchType'></select></td>
					<td>By <select id='ConstraintType' name='ConstraintType'></select></td>
					<td>With <input type='text' id='Constraint' name='Constraint' value='' maxlength='100' ></input></td>
					<td>Show Archived <input type='checkbox' id='IncludeArchived' name='IncludeArchived'/></td>
					<td align='right'><input type='button' onclick='FlexSearch.submitSearch()' value='Search'></input></td>
				</tr>
			</table>
		</form>
	</div>
	<!-- <div id='CustomerSearchPopupResultsContainer' class='GroupedContent' style='margin-top:5px;display:none'></div> -->
	<div id='CustomerSearchPopupResultsContainer' style='margin-top:5px;display:none'></div>
	<div style='padding-top:3px;height:auto:width:100%'>
		<input type='button' value='Close' onclick='Vixen.Popup.Close(this)' style='float:right'></input>
		<div style='clear:both;float:none'></div>
	</div>
</div>
";

			return array(	"Success"	=> TRUE,
							"PopupContent"	=> $strHtml,
							"SearchTypes"	=> $arrSearchTypes);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	// checks if the search has been cached in the user's session details
	private function _hasCachedResults($intSearchType, $strConstraint, $intConstraintType, $bolIncludeArchived)
	{
		return 	(isset($_SESSION) && is_array($_SESSION) && 
				array_key_exists("CustomerSearch", $_SESSION) && is_array($_SESSION['CustomerSearch']) &&
				array_key_exists("Results", $_SESSION['CustomerSearch']) &&	is_array($_SESSION['CustomerSearch']['Results']) &&
				$_SESSION['CustomerSearch']['SearchType'] == $intSearchType &&
				$_SESSION['CustomerSearch']['Constraint'] == $strConstraint &&
				$_SESSION['CustomerSearch']['ConstraintType'] == $intConstraintType &&
				$_SESSION['CustomerSearch']['IncludeArchived'] == $bolIncludeArchived);
				
	}

	private function _getPaginationDetails($intRecCount, $intOffset=0)
	{
		if ($intRecCount == 0)
		{
			// There are no records
			return array(	"FirstRecordIndex"	=> NULL,
							"LastRecordIndex"	=> NULL,
							"PreviousOffset"	=> NULL,
							"NextOffset"		=> NULL,
							"FirstOffset"		=> NULL,
							"LastOffset"		=> NULL,
							"FirstRecord"		=> NULL,
							"LastRecord"		=> NULL
						);
		}
		
		if ($intOffset < 0)
		{
			$intOffset = 0;
		}
		if ($intRecCount <= self::RESULTS_PER_PAGE)
		{
			$intOffset = 0;
		}
		elseif ($intOffset > $intRecCount - 1)
		{
			$intOffset = $intRecCount - self::RESULTS_PER_PAGE;
		}
		
		$intFirstRecordIndex = $intOffset;
		$intLastRecordIndex	= min($intRecCount - 1, $intFirstRecordIndex + self::RESULTS_PER_PAGE - 1);
		
		$intPreviousOffset	= max(0, $intFirstRecordIndex - self::RESULTS_PER_PAGE);
		$intPreviousOffset	= ($intFirstRecordIndex < self::RESULTS_PER_PAGE)? NULL : $intPreviousOffset;
		$intNextOffset		= ($intLastRecordIndex + 1 <= $intRecCount - 1)? $intLastRecordIndex + 1 : NULL;
		$intLastOffset		= floor(($intRecCount - 1) / self::RESULTS_PER_PAGE) * self::RESULTS_PER_PAGE;
		$intLastOffset		= ($intFirstRecordIndex < $intLastOffset)? $intLastOffset : NULL; 
		$intFirstOffset		= ($intFirstRecordIndex != 0)? 0 : NULL;
		
		return array(	"FirstRecordIndex"	=> $intFirstRecordIndex,
						"LastRecordIndex"	=> $intLastRecordIndex,
						"PreviousOffset"	=> $intPreviousOffset,
						"NextOffset"		=> $intNextOffset,
						"FirstOffset"		=> $intFirstOffset,
						"LastOffset"		=> $intLastOffset,
						"FirstRecord"		=> $intFirstRecordIndex + 1,
						"LastRecord"		=> $intLastRecordIndex + 1
					);
		
	}
	
	
	private function _buildServiceResultsTable($arrServiceIds, $intOffset=0)
	{
		return "[INSERT ServiceResultsTable HERE]";
	}

	// Builds a html table displaying details of the accounts
	private function _buildAccountResultsTable($arrAccountIds, $intOffset=0)
	{
		$bolCanOverrideVerification = AuthenticatedUser()->UserHasPerm(JSON_Handler_Customer_Verification::REQUIRED_PERMISSIONS_TO_OVERRIDE_VERIFICATION);

		$strRows = "";
		$bolAlt = FALSE;
		
		// Work out what records we are showing
		$intRecCount = count($arrAccountIds);
		
		$arrPageDetails = $this->_getPaginationDetails($intRecCount, $intOffset);
		
		if ($intRecCount == 0)
		{
			$strRows = "<tr><td colspan='5'>No records to display.  Please broaden your search criteria</td></tr>";
			$strPageInfo = "&nbsp;";
			
		}
		else
		{
			$strPageInfo = "Records {$arrPageDetails['FirstRecord']} to {$arrPageDetails['LastRecord']} of $intRecCount";
			
			for ($i = $arrPageDetails['FirstRecordIndex']; $i <= $arrPageDetails['LastRecordIndex']; $i++)
			{
				$objAccount = Account::getForId($arrAccountIds[$i]);
	
				$strRowClass = ($bolAlt)? "class='alt'" : "";
				
				$strAccountId		= $objAccount->id;
				$strBusinessName	= htmlspecialchars($objAccount->businessName);
				$strTradingName		= htmlspecialchars($objAccount->tradingName);
				$strStatus			= htmlspecialchars(GetConstantDescription($objAccount->archived, "account_status"));
			
				$strVerifyCustomerLink = "FlexCustomerVerification.load(null, {$objAccount->id})";
				if ($bolCanOverrideVerification)
				{
					$strLink = Href()->RecordCustomerInHistory(TRUE, Application_Handler_CustomerHistory::NEXT_PAGE_ACCOUNT_OVERVIEW, $objAccount->id, NULL);
					$strAccountIdCell = "<a href='$strLink' title='Account Overview'>$strAccountId</a>";
				}
				else
				{
					$strAccountIdCell = $strAccountId;
				}
			
				$strRows .= "
<tr $strRowClass>
	<td>$strAccountIdCell</td>
	<td>$strBusinessName</td>
	<td>$strTradingName</td>
	<td>$strStatus</td>
	<td align='right'><a href='javascript:$strVerifyCustomerLink'>Verify</a></td>
</tr>";

				$bolAlt = !$bolAlt;
			}
		}
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['FirstOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['FirstOffset']})' title='First'>&lt;&lt;</a>";
		}
		if ($arrPageDetails['PreviousOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['PreviousOffset']})' title='Previous'>&lt;</a>";
		}
		$strBackwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['NextOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['NextOffset']})' title='Next'>&gt;</a>";
		}
		if ($arrPageDetails['LastOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['LastOffset']})' title='Last'>&gt;&gt;</a>";
		}
		$strForwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		
		$strHtml = "
<table class='reflex highlight-rows' id='CustomerSearchPopupResultsTable' name='CustomerSearchPopupResultsTable'>
	<thead>
		<tr>
			<th>Account #</th>
			<th>Business Name</th>
			<th>Trading Name</th>
			<th>Status</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		$strRows
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th colspan='5'>
				<div style='text-align:center'>
					<div style='float:left'>$strBackwardsPaginationOptions</div>
					<div style='float:right'>$strForwardsPaginationOptions</div>
					$strPageInfo
				</div>
			</th>
		</tr>
	</tfoot>
</table>
";
		return $strHtml;
	}

	private function _buildContactResultsTable($arrContactIds, $intOffset=0)
	{
		$bolCanOverrideVerification = AuthenticatedUser()->UserHasPerm(JSON_Handler_Customer_Verification::REQUIRED_PERMISSIONS_TO_OVERRIDE_VERIFICATION);

		$strRows = "";
		$bolAlt = FALSE;
		
		// Work out what records we are showing
		$intRecCount = count($arrContactIds);
		
		$arrPageDetails = $this->_getPaginationDetails($intRecCount, $intOffset);
		
		if ($intRecCount == 0)
		{
			$strRows = "<tr><td colspan='6'>No records to display.  Please broaden your search criteria</td></tr>";
			$strPageInfo = "&nbsp;";
		}
		else
		{
			$strPageInfo = "Records {$arrPageDetails['FirstRecord']} to {$arrPageDetails['LastRecord']} of $intRecCount";

			for ($i = $arrPageDetails['FirstRecordIndex']; $i <= $arrPageDetails['LastRecordIndex']; $i++)
			{
				$objContact = Contact::getForId($arrContactIds[$i]);
				$objAccount = Account::getForId($objContact->account);

				$strRowClass = ($bolAlt)? "class='alt'" : "";

				$strContactNameCell	= htmlspecialchars($objContact->getName());
				$strContactStatus	= ($objContact->archived)? "Archived" : "Active";
				
				if ($objAccount != NULL)
				{
					$strAccountIdCell	= $objAccount->id;
					$strAccountName		= htmlspecialchars($objAccount->getName());
					$strAccountStatus	= htmlspecialchars(GetConstantDescription($objAccount->archived, "account_status"));
					if ($bolCanOverrideVerification)
					{
						$strAccountLink = Href()->RecordCustomerInHistory(TRUE, Application_Handler_CustomerHistory::NEXT_PAGE_ACCOUNT_OVERVIEW, $objAccount->id, $objContact->id);
						$strAccountIdCell = "<a href='$strAccountLink' title='Account Overview'>{$objAccount->id}</a>";
					}
				}
				else
				{
					$strAccountIdCell	= "";
					$strAccountName		= "";
					$strAccountStatus	= "";
				}
				
				$strAccountId = ($objAccount != NULL)? $objAccount->id : "null";
				$strVerifyCustomerLink = "FlexCustomerVerification.load({$objContact->id}, $strAccountId)";
				if ($bolCanOverrideVerification)
				{
					$intAccountId		= ($objAccount != NULL)? $objAccount->id : NULL;
					$strContactLink		= Href()->RecordCustomerInHistory(TRUE, Application_Handler_CustomerHistory::NEXT_PAGE_CONTACT_DETAILS, $intAccountId, $objContact->id);
					$strContactNameCell = "<a href='$strContactLink' title='View Contact'>{$objContact->getName()}</a>";
				}
			
				$strRows .= "
<tr $strRowClass>
	<td>$strContactNameCell</td>
	<td>$strContactStatus</td>
	<td>$strAccountIdCell</td>
	<td>$strAccountName</td>
	<td>$strAccountStatus</td>
	<td align='right'><a href='javascript:$strVerifyCustomerLink'>Verify</a></td>
</tr>";
				$bolAlt = !$bolAlt;
			}
		}
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['FirstOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['FirstOffset']})' title='First'>&lt;&lt;</a>";
		}
		if ($arrPageDetails['PreviousOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['PreviousOffset']})' title='Previous'>&lt;</a>";
		}
		$strBackwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['NextOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['NextOffset']})' title='Next'>&gt;</a>";
		}
		if ($arrPageDetails['LastOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['LastOffset']})' title='Last'>&gt;&gt;</a>";
		}
		$strForwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		

		$strHtml = "
<table class='reflex highlight-rows' id='CustomerSearchPopupResultsTable' name='CustomerSearchPopupResultsTable'>
	<thead>
		<tr>
			<th>Contact</th>
			<th>Status</th>
			<th>Account #</th>
			<th>Name</th>
			<th>Status</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		$strRows
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th colspan='6'>
				<div style='text-align:center'>
					<div style='float:left'>$strBackwardsPaginationOptions</div>
					<div style='float:right'>$strForwardsPaginationOptions</div>
					$strPageInfo
				</div>
			</th>
		</tr>
	</tfoot>
</table>
";
		return $strHtml;
	}

	// DEPRICATED
	// Builds a html table displaying details of the accounts
	private function _buildAccountResultsTableOld($arrAccountIds, $intOffset=0)
	{
		$strRows = "";
		$bolAlt = FALSE;
		
		// Work out what records we are showing
		$intRecCount = count($arrAccountIds);
		
		$arrPageDetails = $this->_getPaginationDetails($intRecCount, $intOffset);
		
		if ($intRecCount == 0)
		{
			$strRows = "<tr><td colspan='4'>No records to display</td></tr>";
			$strPageInfo = "";
			
		}
		else
		{
			$strPageInfo = "Records {$arrPageDetails['FirstRecord']} to {$arrPageDetails['LastRecord']} of $intRecCount";
			
			for ($i = $arrPageDetails['FirstRecordIndex']; $i <= $arrPageDetails['LastRecordIndex']; $i++)
			{
				$objAccount = Account::getForId($arrAccountIds[$i]);
	
				$strRowClass = ($bolAlt)? "class='alt'" : "";
				
				$strAccountId		= $objAccount->id;
				$strBusinessName	= htmlspecialchars($objAccount->businessName);
				$strTradingName		= htmlspecialchars($objAccount->tradingName);
				$strStatus			= htmlspecialchars(GetConstantDescription($objAccount->archived, "account_status"));
			
				$strRows .= "
<tr $strRowClass onclick='FlexSearch.loadAccount($strAccountId, event)'>
	<td>$strAccountId</td>
	<td>$strBusinessName</td>
	<td>$strTradingName</td>
	<td>$strStatus</td>
</tr>";


				$bolAlt = !$bolAlt;
			}
		}
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['FirstOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['FirstOffset']})' title='First'>&lt;&lt;</a>";
		}
		if ($arrPageDetails['PreviousOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['PreviousOffset']})' title='Previous'>&lt;</a>";
		}
		$strBackwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['NextOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['NextOffset']})' title='Next'>&gt;</a>";
		}
		if ($arrPageDetails['LastOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['LastOffset']})' title='Last'>&gt;&gt;</a>";
		}
		$strForwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		
		$strHtml = "
<table class='reflex highlight-rows' id='CustomerSearchPopupResultsTable' name='CustomerSearchPopupResultsTable'>
	<thead>
		<tr>
			<th>Account #</th>
			<th>Business Name</th>
			<th>Trading Name</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody style='cursor:pointer'>
		$strRows
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th style='text-align:left'>
				$strBackwardsPaginationOptions
			</th>
			<th colspan='2' style='text-align:center'>
				$strPageInfo
			</th>
			<th style='text-align:right'>
				$strForwardsPaginationOptions
			</th>
		</tr>
	</tfoot>
</table>
";
		return $strHtml;
	}

	// DEPRICATED
	private function _buildContactResultsTableOld($arrContactIds, $intOffset=0)
	{
		$strRows = "";
		$bolAlt = FALSE;
		
		// Work out what records we are showing
		$intRecCount = count($arrContactIds);
		
		$arrPageDetails = $this->_getPaginationDetails($intRecCount, $intOffset);
		
		if ($intRecCount == 0)
		{
			$strRows = "<tr><td colspan='4'>No records to display</td></tr>";
			$strPageInfo = "";
			
		}
		else
		{
			$strPageInfo = "Records {$arrPageDetails['FirstRecord']} to {$arrPageDetails['LastRecord']} of $intRecCount";
			
			for ($i = $arrPageDetails['FirstRecordIndex']; $i <= $arrPageDetails['LastRecordIndex']; $i++)
			{
				$objContact = Contact::getForId($arrContactIds[$i]);
				$objAccount = Account::getForId($objContact->account);
	
				$strRowClass = ($bolAlt)? "class='alt'" : "";
				
				$strContactName		= $objContact->getName();
				$strContactStatus	= ($objContact->archived)? "Archived" : "Active";
				
				if ($objAccount != NULL)
				{
					$strAccountId		= $objAccount->id;
					$strAccountName		= htmlspecialchars($objAccount->getName());
					$strAccountStatus	= htmlspecialchars(GetConstantDescription($objAccount->archived, "account_status"));
				}
				else
				{
					$strAccountId		= "";
					$strAccountName		= "";
					$strAccountStatus	= "";
				}
			
				$strRows .= "
<tr $strRowClass onclick='FlexSearch.loadContact({$objContact->id}, event)'>
	<td>$strContactName</td>
	<td>$strContactStatus</td>
	<td>$strAccountId</td>
	<td>$strAccountName</td>
	<td>$strAccountStatus</td>
</tr>";
				$bolAlt = !$bolAlt;
			}
		}
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['FirstOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['FirstOffset']})' title='First'>&lt;&lt;</a>";
		}
		if ($arrPageDetails['PreviousOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['PreviousOffset']})' title='Previous'>&lt;</a>";
		}
		$strBackwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		$arrPaginationOptions = array();
		if ($arrPageDetails['NextOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['NextOffset']})' title='Next'>&gt;</a>";
		}
		if ($arrPageDetails['LastOffset'] !== NULL)
		{
			$arrPaginationOptions[] = "<a onclick='FlexSearch.getResults({$arrPageDetails['LastOffset']})' title='Last'>&gt;&gt;</a>";
		}
		$strForwardsPaginationOptions = implode(" &nbsp ", $arrPaginationOptions);
		
		
		$strHtml = "
<table class='reflex highlight-rows' id='CustomerSearchPopupResultsTable' name='CustomerSearchPopupResultsTable'>
	<thead>
		<tr>
			<th>Contact</th>
			<th>Status</th>
			<th>Account #</th>
			<th>Name</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody style='cursor:pointer'>
		$strRows
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th style='text-align:left'>
				$strBackwardsPaginationOptions
			</th>
			<th colspan='3' style='text-align:center'>
				$strPageInfo
			</th>
			<th style='text-align:right'>
				$strForwardsPaginationOptions
			</th>
		</tr>
	</tfoot>
</table>
";
		return $strHtml;
	}

	
}

?>
