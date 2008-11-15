<?php

class JSON_Handler_Dealer extends JSON_Handler
{
	// Builds the "Edit Dealer" popup
	public function buildEditDealerPopup($intDealerId, $intEmployeeId, $intCallingPage=NULL)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			// Check if we are editing an existing dealer, or defining a new one
			if ($intDealerId !== NULL)
			{
				// We are editing an existing dealer.  Retrieve the dealer object
				$objDealer = Dealer::getForId(intval($intDealerId));
				if ($objDealer === NULL)
				{
					// The dealer could not be found
					throw new Exception("Dealer with id: ". intval($intDealerId) .", could not be found");
				}
			}
			else
			{
				// We are creating a new dealer
				$objDealer = new Dealer();
				
				// Check if the dealer is an employee
				if ($intEmployeeId !== NULL)
				{
					// The dealer is an employee.  Copy across the employee details
					$objEmployee = Employee::getForId(intval($intEmployeeId));
					if ($objEmployee === NULL)
					{
						// The employee could not be found
						throw new Exception("Employee with id: ". intval($intEmployeeId) .", could not be found");
					}
					
					$objDealer->firstName	= $objEmployee->firstName;
					$objDealer->lastName	= $objEmployee->lastName;
					$objDealer->username	= $objEmployee->username;
					$objDealer->password	= $objEmployee->password;
					$objDealer->phone		= $objEmployee->phone;
					$objDealer->mobile		= $objEmployee->mobile;
					$objDealer->email		= $objEmployee->email;
					$objDealer->employeeId = $objEmployee->id;
				}
			}
			
			
			// Build data for the combo boxes
			
			// Dealer Title combo box
			$arrContactTitles = Contact_Title::getAll();
			$strTitleComboOptions = "<option value='0'>&nbsp;</option>";
			foreach ($arrContactTitles as $objTitle)
			{
				$strTitleComboOptions .= "<option value='{$objTitle->id}'>". htmlspecialchars($objTitle->name) ."</option>";
			}
			
			// Country combo boxes (and data for the state combo boxes)
			$arrCountryStates = array();
			$arrCountries = Country::getAll();
			$strCountryComboOptions = "<option value='0'>&nbsp;</option>";
			foreach ($arrCountries as $objCountry)
			{
				$arrStates = State::getForCountry($objCountry->id);
				foreach ($arrStates as $objState)
				{
					// Record the states against the country they belong to
					$arrCountryStates[$objCountry->id][$objState->id] = htmlspecialchars($objState->name);
				}
				$strCountryComboOptions .= "<option value='{$objCountry->id}'>". htmlspecialchars($objCountry->name) ."</option>"; 
			}
			
			// Dealer status combo box
			$arrDealerStatuses = Dealer_Status::getAll();
			$strDealerStatusComboOptions = "";
			foreach ($arrDealerStatuses as $objStatus)
			{
				$strDealerStatusComboOptions .= "<option value='{$objStatus->id}'>". htmlspecialchars($objStatus->name) ."</option>";
			}
			
			// Dealer Manager combo box
			$arrPossibleManagers = Dealer::getAllowableManagersForDealer($objDealer->id);
			$strManagerComboOptions = "<option value='0'>&nbsp;</option>";
			foreach ($arrPossibleManagers as $objPossibleManager)
			{
				$strManagerComboOptions .= "<option value='{$objPossibleManager->id}'>". htmlspecialchars($objPossibleManager->getName()) ."</option>";
			}
			
			// Build SaleType Data
			$arrObjSaleTypes = Sale_Type::getAll();
			$arrSaleTypeDetails = array();
			$strSaleTypeComboOptions = "";
			foreach ($arrObjSaleTypes as $objSaleType)
			{
				$arrSaleTypeDetails[$objSaleType->id] = array(	"id"	=> $objSaleType->id,
																"name"	=> htmlspecialchars($objSaleType->name)
																);
			}
			
			// Build CustomerGroup data
			$arrObjCustomerGroups = Customer_Group::getAll();
			$arrCustomerGroupDetails = array();
			foreach ($arrObjCustomerGroups as $objCustomerGroup)
			{
				$arrCustomerGroupDetails[$objCustomerGroup->id] = array("id"	=> $objCustomerGroup->id,
																		"name"	=> htmlspecialchars($objCustomerGroup->internalName)
																		);
			}
			
			// Build RatePlan data
			$arrObjRatePlans = Rate_Plan::getAll();
			$arrRatePlanDetails = array();
			foreach ($arrObjRatePlans as $objRatePlan)
			{
				if ($objRatePlan->Archived != RATE_STATUS_ACTIVE)
				{
					// We are only concerned with active RatePlans
					continue;
				}
				$arrRatePlanDetails[$objRatePlan->Id] = array(	"id"	=> $objRatePlan->Id,
																"name"	=> $arrCustomerGroupDetails[$objRatePlan->customer_group]['name'] ." - ". GetConstantDescription($objRatePlan->ServiceType, "service_type") ." - ". htmlspecialchars($objRatePlan->Name),
																"customerGroup"	=> $objRatePlan->customer_group
															);
			}
			
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody' style='padding:3px'>
	<form id='EditDealerPopupForm' name='EditDealerPopupForm'>
		<div class='tab-collection' id='EditDealerTabCollection' name='EditDealerTabCollection'>
			<div class='tab-header-container' style='float:none;clear:both;width:100%'>
				<div class='tab-header' id='TabHeaderGeneralDetails' name='TabHeaderMiscDetails' tab='0' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>General</span>
				</div>
				<div class='tab-header' id='TabHeaderBusinessDetails' name='TabHeaderBusinessDetails' tab='1' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Business</span>
				</div>
				<div class='tab-header' id='TabHeaderPaymentDetails' name='TabHeaderPaymentDetails' tab='2' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Payment</span>
				</div>
				<div class='tab-header' id='TabHeaderAddressDetails' name='TabHeaderAddressDetails' tab='3' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Address</span>
				</div>
				<div class='tab-header' id='TabHeaderSalesConstraintsDetails' name='TabHeaderSalesConstraintsDetails' tab='4' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Sales Constraints</span>
				</div>
				<div style='float:none;clear:both'></div>
			</div> <!-- TabHeaderContainer -->
			<div class='tab-body-container'>
				<div class='tab-body' id='TabBodyGeneralDetails' name='TabBodyMiscDetails' tab='0' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Title</td>
							<td><select id='titleId' name='titleId'>$strTitleComboOptions</select></td>
						</tr>
						<tr>
							<td class='title'>First Name</td>
							<td><input type='text' id='firstName' name='firstName' class='required' maxlength='255' style='width:100%'></input></td>
						</tr>
						<tr>
							<td class='title'>Last Name</td>
							<td><input type='text' id='lastName' name='lastName' class='required' maxlength='255' style='width:100%'></input></td>
						</tr>
						<tr>
							<td class='title'>Username</td>
							<td><input type='text' id='username' name='username' class='required' maxlength='255' style='width:100%'></input></td>
						</tr>
						<tr>
							<td class='title'>Password</td>
							<td><input type='password' id='password' name='password' maxlength='255'></input> Leave blank to retain existing</td>
						</tr>
						<tr>
							<td class='title'>Password Again</td>
							<td><input type='password' id='password2' name='password2' maxlength='255'></input></td>
						</tr>
						<tr>
							<td class='title'>Up Line Manager</td>
							<td><select id='upLineId' name='upLineId' style='width:100%'>$strManagerComboOptions</select></td>
						</tr>
						<tr>
							<td class='title'>Can Verify Sales</td>
							<td><input type='checkbox' id='canVerify' name='canVerify'></input></td>
						</tr>
						<tr>
							<td class='title'>Phone</td>
							<td><input type='text' id='phone' name='phone' maxlength='30' style='width:70%'></input></td>
						</tr>
						<tr>
							<td class='title'>Mobile</td>
							<td><input type='text' id='mobile' name='mobile' maxlength='30' style='width:70%'></input></td>
						</tr>
						<tr>
							<td class='title'>Fax</td>
							<td><input type='text' id='fax' name='fax' maxlength='30' style='width:70%'></input></td>
						</tr>
						<tr>
							<td class='title'>Email</td>
							<td><input type='text' id='email' name='email' maxlength='255' style='width:70%'></input></td>
						</tr>
						<tr>
							<td class='title'>Termination Date</td>
							<td><input type='text' id='terminationDate' name='terminationDate' maxlength='10' inputmask='shortdate'></input></td>
						</tr>
						<tr>
							<td class='title'>Status</td>
							<td><select id='dealerStatusId' name='dealerStatusId'>$strDealerStatusComboOptions</select></td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodyBusinessDetails' name='TabBodyBusinessDetails' tab='1' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Business Name</td>
							<td><input type='text' id='businessName' name='businessName' maxlength='255' style='width:100%'></input></td>
						</tr>
						<tr>
							<td class='title'>Trading Name</td>
							<td><input type='text' id='tradingName' name='tradingName' maxlength='255' style='width:100%'></input></td>
						</tr>
						<tr>
							<td class='title'>ABN</td>
							<td><input type='text' id='abn' name='abn' maxlength='11'></input> (<input type='checkbox' id='abnRegistered' name='abnRegistered'>ABN is registered</input>)</td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodyPaymentDetails' name='TabBodyPaymentDetails' tab='2' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Commission Scale</td>
							<td><input type='text' id='commissionScale' name='commissionScale' maxlength='5'></input></td>
						</tr>
						<tr>
							<td class='title'>Royalty Scale</td>
							<td><input type='text' id='royaltyScale' name='royaltyScale' maxlength='5'></input></td>
						</tr>
						<tr>
							<td class='title'>Bank BSB</td>
							<td><input type='text' id='bankAccountBsb' name='bankAccountBsb' maxlength='10'></input></td>
						</tr>
						<tr>
							<td class='title'>Bank Account Number</td>
							<td><input type='text' id='bankAccountNumber' name='bankAccountNumber' maxlength='20'></input></td>
						</tr>
						<tr>
							<td class='title'>Bank Account Name</td>
							<td><input type='text' id='bankAccountName' name='bankAccountName' maxlength='255'></input></td>
						</tr>
						<tr>
							<td class='title'>GST Registered</td>
							<td><input type='checkbox' id='gstRegistered' name='gstRegistered'></input></td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodyAddressDetails' name='TabBodyAddressDetails' tab='3' style='display:none'>
					<div class='GroupedContent'>
						<table class='form-data'>
							<tr>
								<td class='title' style='width:30%'>Physical Address</td>
								<td></td>
							</tr>
							<tr>
								<td class='title'>Address Line 1</td>
								<td><input type='text' id='addressLine1' name='addressLine1' maxlength='255' style='width:100%'></input></td>
							</tr>
							<tr>
								<td class='title'>Address Line 2</td>
								<td><input type='text' id='addressLine2' name='addressLine2' maxlength='255' style='width:100%'></input></td>
							</tr>
							<tr>
								<td class='title'>Suburb</td>
								<td><input type='text' id='suburb' name='suburb' maxlength='255' style='width:100%'></input></td>
							</tr>
							<tr>
								<td class='title'>Postcode</td>
								<td><input type='text' id='postcode' name='postcode' maxlength='10'></input></td>
							</tr>
							<tr>
								<td class='title'>Country</td>
								<td><select id='countryId' name='countryId'>$strCountryComboOptions</select></td>
							</tr>
							<tr>
								<td class='title'>State</td>
								<td><select id='stateId' name='stateId'></select></td>
							</tr>
						</table>
					</div>
					<div class='GroupedContent' style='padding-top:5px'>
						<table class='form-data'>
							<tr>
								<td class='title' style='width:30%'>Postal Address</td>
								<td><input type='button' value='Copy from Physical Address' onclick='Dealer.copyAddressDetails()'></input></td>
							</tr>
							<tr>
								<td class='title'>Address Line 1</td>
								<td><input type='text' id='postalAddressLine1' name='postalAddressLine1' maxlength='255' style='width:100%'></input></td>
							</tr>
							<tr>
								<td class='title'>Address Line 2</td>
								<td><input type='text' id='postalAddressLine2' name='postalAddressLine2' maxlength='255' style='width:100%'></input></td>
							</tr>
							<tr>
								<td class='title'>Suburb</td>
								<td><input type='text' id='postalSuburb' name='postalSuburb' maxlength='255' style='width:100%'></input></td>
							</tr>
							<tr>
								<td class='title'>Postcode</td>
								<td><input type='text' id='postalPostcode' name='postalPostcode' maxlength='10'></input></td>
							</tr>
							<tr>
								<td class='title'>Country</td>
								<td><select id='postalCountryId' name='postalCountryId'>$strCountryComboOptions</select></td>
							</tr>
							<tr>
								<td class='title'>State</td>
								<td><select id='postalStateId' name='postalStateId'></select></td>
							</tr>
						</table>
					</div>
				</div>
				<div class='tab-body' id='TabBodySalesConstraintsDetails' name='TabBodySalesConstraintsDetails' tab='4' style='display:none'>
					<div>
						<div id='saleTypesContainer' name='saleTypesContainer' style='float:left;width:48%'>
							<span>Sale Types</span>
							<select id='saleTypes' name='saleTypes' size='4' multiple='multiple' style='width:100%'></select>
						</div>
						<div id='customerGroupsContainer' name='customerGroupsContainer' style='float:right;width:48%'>
							<span>Customer Groups</span>
							<select id='customerGroups' name='customerGroups' size='4' multiple='multiple' style='width:100%'></select>
						</div>
						<div style='clear:both;float:none'></div>
					</div>
					<div>
						<span>Available RatePlans</span>
						<select id='availableRatePlans' name='availableRatePlans' size='8' multiple='multiple' style='width:100%'></select>
						<div style='width:100%'>
							<div style='float:left'>
								RatePlans that the dealer can sell
							</div>
							<div style='float:right'>
								<img src='img/template/icon_movedown.png' onclick='Dealer.ratePlanButtonAddOnClick()' title='Add RatePlans'></img>
								<img src='img/template/icon_moveup.png' onclick='Dealer.ratePlanButtonRemoveOnClick()' title='Remove RatePlans'></img>
							</div>
							<div style='clear:both;float:none'></div>
						</div>
						<select id='selectedRatePlans' name='selectedRatePlans' size='8' multiple='multiple' style='width:100%'></select>
					</div>
					<input type='button' id='salesConstraintsRevertButton' name='salesConstraintsRevertButton' value='Revert to saved details' onclick='Dealer.initialiseSalesConstraints()'></input>
				</div>

			</div> <!-- TabBodyContainer -->
		</div> <!-- TabCollection -->
	</form>

	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' value='Save' onclick='Dealer.saveDealerDetails()'></input>
			<input type='button' value='Close' onclick='Dealer.closeEditDealerPopup($intCallingPage)'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>

</div>
";

			$arrData = array(	"Dealer"			=> $objDealer->toArray(),
								"DealerName"		=> htmlspecialchars($objDealer->getName()),
								"CountryStates"		=> $arrCountryStates,
								"SaleTypes"			=> $arrSaleTypeDetails,
								"CustomerGroups"	=> $arrCustomerGroupDetails,
								"RatePlans"			=> $arrRatePlanDetails
							);


			return array(	"Success"		=> TRUE,
							"PopupContent"	=> $strHtml,
							"Data"			=> $arrData
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	public function buildNewDealerSelectionPopup()
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			// Build data for the combo boxes
			
			// Dealer/Employee combo box
			$arrEmployeeIds = Dealer::getEmployeesWhoArentYetDealers();
			$strEmployeeOptions = "<option value='0'>New Non-Flex Employee Dealer</option>";
			foreach ($arrEmployeeIds as $intEmployeeId)
			{
				$objEmployee = Employee::getForId($intEmployeeId);
				if ($objEmployee === NULL)
				{
					// Couldn't retrieve the employee record for some reason
					// Not to worry; just move on
					continue;
				}
				
				$strName = htmlspecialchars($objEmployee->firstName) . " " . htmlspecialchars($objEmployee->lastName);
				$strEmployeeOptions .= "<option value='{$intEmployeeId}'>$strName</option>";
			}
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody' style='padding:3px'>
	<form id='NewDealerPopupForm' name='NewDealerPopupForm'>
		<div class='GroupedContent'>
			Select the employee to base this dealer on
			<table class='form-data'>
				<tr>
					<td class='title' style='width:20%'>Employee</td>
					<td><select id='NewDealerPopupEmployeeIdCombo' name='NewDealerPopupEmployeeIdCombo' style='width:100%'>$strEmployeeOptions</select></td>
				</tr>
			</table>
		</div>
	</form>

	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' value='Ok' onclick='Dealer.newDealerPopupOkButtonOnClick()'></input>
			<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>

</div>
";
			return array(	"Success"		=> TRUE,
							"PopupContent"	=> $strHtml,
							"EmployeeCount"	=> count($arrEmployeeIds)
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	// Builds the "View Dealer" popup
	public function buildViewDealerPopup($intDealerId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			// Retrieve the dealer object
			$objDealer = Dealer::getForId(intval($intDealerId));
			if ($objDealer === NULL)
			{
				// The dealer could not be found
				throw new Exception("Dealer with id: ". intval($intDealerId) .", could not be found");
			}
			
			// Prepare Data
			$arrDetails['name']		= htmlspecialchars($objDealer->getName(TRUE));
			$arrDetails['username']	= htmlspecialchars($objDealer->username);
			
			if ($objDealer->upLineId !== NULL)
			{
				// The dealer has a manager
				$objManager = Dealer::getForId($objDealer->upLineId);
				$strManagerName = ($objManager !== NULL)? $objManager->getName(TRUE) : "Dealer with id: {$objDealer->upLineId} could not be found";
			}
			else
			{
				$strManagerName = "No up line manager";
			}
			$arrDetails['upLineManager']	= htmlspecialchars($strManagerName);  
			$arrDetails['canVerify']		= ($objDealer->canVerify)? "Yes" : "No";
			$arrDetails['phone']			= ($objDealer->phone !== NULL)? htmlspecialchars($objDealer->phone) : "[Not Specified]";
			$arrDetails['mobile']			= ($objDealer->mobile !== NULL)? htmlspecialchars($objDealer->mobile) : "[Not Specified]";
			$arrDetails['fax']				= ($objDealer->fax !== NULL)? htmlspecialchars($objDealer->fax) : "[Not Specified]";
			$arrDetails['email']			= ($objDealer->email !== NULL)? htmlspecialchars($objDealer->email) : "[Not Specified]";
			
			$arrDetails['terminationDate']	= ($objDealer->terminationDate !== NULL)? substr($objDealer->terminationDate, 8, 2) ." / ". substr($objDealer->terminationDate, 5, 2) ." / ". substr($objDealer->terminationDate, 0, 4) : "[Not Specified]";
			$objDealerStatus = Dealer_Status::getForId($objDealer->dealerStatusId);
			$arrDetails['status'] = ($objDealerStatus !== NULL)? htmlspecialchars($objDealerStatus->name) : "Status: {$objDealer->dealerStatusId} could not be found";
			$arrDetails['isEmployee'] = ($objDealer->employeeId !== NULL)? "Yes" : "No";
			
			$arrDetails['businessName'] = ($objDealer->businessName !== NULL)? htmlspecialchars($objDealer->businessName): "[Not Specified]";
			$arrDetails['tradingName'] = ($objDealer->tradingName !== NULL)? htmlspecialchars($objDealer->tradingName): "[Not Specified]";
			if ($objDealer->abn !== NULL)
			{
				$arrDetails['abn'] = htmlspecialchars($objDealer->abn) . ($objDealer->abnRegistered ? " (registered)" : " (not registered)"); 
			}
			else
			{
				$arrDetails['abn'] = "[Not Specified]";
			}
			
			$arrDetails['commissionScale']		= ($objDealer->commissionScale !== NULL)? htmlspecialchars($objDealer->commissionScale) : "[Not Specified]";
			$arrDetails['royaltyScale']			= ($objDealer->royaltyScale !== NULL)? htmlspecialchars($objDealer->royaltyScale) : "[Not Specified]";
			$arrDetails['bankAccountBsb']		= ($objDealer->bankAccountBsb !== NULL)? htmlspecialchars($objDealer->bankAccountBsb) : "[Not Specified]";
			$arrDetails['bankAccountNumber']	= ($objDealer->bankAccountNumber !== NULL)? htmlspecialchars($objDealer->bankAccountNumber) : "[Not Specified]";
			$arrDetails['bankAccountName']		= ($objDealer->bankAccountName !== NULL)? htmlspecialchars($objDealer->bankAccountName) : "[Not Specified]";
			$arrDetails['gstRegistered']		= ($objDealer->gstRegistered)? "Yes" : "No";
			
			
			$arrDetails['addressLine1']	= ($objDealer->addressLine1 !== NULL)? htmlspecialchars($objDealer->addressLine1) : "";
			$arrDetails['addressLine2']	= ($objDealer->addressLine2 !== NULL)? htmlspecialchars($objDealer->addressLine2) : "";
			$arrDetails['suburb']		= ($objDealer->suburb !== NULL)? htmlspecialchars($objDealer->suburb) : "";
			$arrDetails['postcode']	= ($objDealer->postcode !== NULL)? htmlspecialchars($objDealer->postcode) : "";
			
			
			$arrDetails['addressLine1']	= ($objDealer->addressLine1 !== NULL)? htmlspecialchars($objDealer->addressLine1) : "";
			$arrDetails['addressLine1']	= ($objDealer->addressLine1 !== NULL)? htmlspecialchars($objDealer->addressLine1) : "";
			
			// Build address
			$arrAddressParts = array();
			if ($objDealer->addressLine1 !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objDealer->addressLine1);
			}
			if ($objDealer->addressLine2 !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objDealer->addressLine2);
			}
			if ($objDealer->suburb !== NULL || $objDealer->postcode !== NULL)
			{
				$arrAddressParts[] = trim(htmlspecialchars($objDealer->suburb ." ". $objDealer->postcode));
			}
			if ($objDealer->stateId !== NULL && ($objState = State::getForId($objDealer->stateId)) !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objState->name);
			}
			if ($objDealer->countryId !== NULL && ($objCountry = Country::getForId($objDealer->countryId)) !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objCountry->name);
			}
			$arrDetails['address'] = (count($arrAddressParts) > 0)? implode("<br />", $arrAddressParts) : "[Not Specified]";
			
			// Build postal address
			$arrAddressParts = array();
			if ($objDealer->postalAddressLine1 !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objDealer->postalAddressLine1);
			}
			if ($objDealer->postalAddressLine2 !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objDealer->postalAddressLine2);
			}
			if ($objDealer->postalSuburb !== NULL || $objDealer->postalPostcode !== NULL)
			{
				$arrAddressParts[] = trim(htmlspecialchars($objDealer->postalSuburb ." ". $objDealer->postalPostcode));
			}
			if ($objDealer->postalStateId !== NULL && ($objState = State::getForId($objDealer->postalStateId)) !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objState->name);
			}
			if ($objDealer->postalCountryId !== NULL && ($objState = Country::getForId($objDealer->postalCountryId)) !== NULL)
			{
				$arrAddressParts[] = htmlspecialchars($objCountry->name);
			}
			$arrDetails['postalAddress'] = (count($arrAddressParts) > 0)? implode("<br />", $arrAddressParts) : "[Not Specified]";
			
			$arrSaleTypeIds		= $objDealer->getSaleTypes();
			$arrAllSaleTypes	= Sale_Type::getAll();
			$arrSaleTypeNames	= Array();
			foreach ($arrSaleTypeIds as $intSaleTypeId)
			{
				$arrSaleTypeNames[] = htmlspecialchars($arrAllSaleTypes[$intSaleTypeId]->name);
			}
			$arrDetails['saleTypes'] = (count($arrSaleTypeNames) > 0)? implode("<br />", $arrSaleTypeNames) : "[None Specified]";
			
			$arrCustomerGroupIds	= $objDealer->getCustomerGroups();
			$arrAllCustomerGroups	= Customer_Group::getAll();
			$arrCustomerGroupNames	= Array();
			foreach ($arrCustomerGroupIds as $intCustomerGroupId)
			{
				$arrCustomerGroupNames[] = htmlspecialchars($arrAllCustomerGroups[$intCustomerGroupId]->internalName);
			}
			$arrDetails['customerGroups'] = (count($arrCustomerGroupNames) > 0)? implode("<br />", $arrCustomerGroupNames) : "[None Specified]";
			
			$arrRatePlanIds		= $objDealer->getRatePlans();
			$arrAllRatePlans	= Rate_Plan::getAll();
			$arrRatePlanNames	= Array();
			foreach ($arrRatePlanIds as $intRatePlanId)
			{
				$objRatePlan		= $arrAllRatePlans[$intRatePlanId];
				$strServiceType		= GetConstantDescription($objRatePlan->ServiceType, "service_type");
				$strCustomerGroup	= $arrAllCustomerGroups[$objRatePlan->customer_group]->internalName;
				$arrRatePlanNames[]	= htmlspecialchars("{$strCustomerGroup} - {$strServiceType} - {$objRatePlan->Name}");
			}
			$arrDetails['ratePlans'] = (count($arrRatePlanNames) > 0)? implode("<br />", $arrRatePlanNames) : "[None Specified]";
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody' style='padding:3px'>
	<form id='EditDealerPopupForm' name='EditDealerPopupForm'>
		<div class='tab-collection' id='EditDealerTabCollection' name='EditDealerTabCollection'>
			<div class='tab-header-container' style='float:none;clear:both;width:100%'>
				<div class='tab-header' id='TabHeaderGeneralDetails' name='TabHeaderMiscDetails' tab='0' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>General</span>
				</div>
				<div class='tab-header' id='TabHeaderBusinessDetails' name='TabHeaderBusinessDetails' tab='1' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Business</span>
				</div>
				<div class='tab-header' id='TabHeaderPaymentDetails' name='TabHeaderPaymentDetails' tab='2' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Payment</span>
				</div>
				<div class='tab-header' id='TabHeaderAddressDetails' name='TabHeaderAddressDetails' tab='3' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Address</span>
				</div>
				<div class='tab-header' id='TabHeaderSalesConstraintsDetails' name='TabHeaderSalesConstraintsDetails' tab='4' style='float:left;margin-right:1em'>
					<span style='font-weight:bold'>Sales Constraints</span>
				</div>
				<div style='float:none;clear:both'></div>
			</div> <!-- TabHeaderContainer -->
			<div class='tab-body-container'>
				<div class='tab-body' id='TabBodyGeneralDetails' name='TabBodyMiscDetails' tab='0' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Name</td>
							<td>{$arrDetails['name']}</td>
						</tr>
						<tr>
							<td class='title'>Username</td>
							<td>{$arrDetails['username']}</td>
						</tr>
						<tr>
							<td class='title'>Up Line Manager</td>
							<td>{$arrDetails['upLineManager']}</td>
						</tr>
						<tr>
							<td class='title'>Can Verify Sales</td>
							<td>{$arrDetails['canVerify']}</td>
						</tr>
						<tr>
							<td class='title'>Phone</td>
							<td>{$arrDetails['phone']}</td>
						</tr>
						<tr>
							<td class='title'>Mobile</td>
							<td>{$arrDetails['mobile']}</td>
						</tr>
						<tr>
							<td class='title'>Fax</td>
							<td>{$arrDetails['fax']}</td>
						</tr>
						<tr>
							<td class='title'>Email</td>
							<td>{$arrDetails['email']}</td>
						</tr>
						<tr>
							<td class='title'>Termination Date</td>
							<td>{$arrDetails['terminationDate']}</td>
						</tr>
						<tr>
							<td class='title'>Is Internal Employee</td>
							<td>{$arrDetails['isEmployee']}</td>
						</tr>
						<tr>
							<td class='title'>Status</td>
							<td>{$arrDetails['status']}</td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodyBusinessDetails' name='TabBodyBusinessDetails' tab='1' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Business Name</td>
							<td>{$arrDetails['businessName']}</td>
						</tr>
						<tr>
							<td class='title'>Trading Name</td>
							<td>{$arrDetails['tradingName']}</td>
						</tr>
						<tr>
							<td class='title'>ABN</td>
							<td>{$arrDetails['abn']}</td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodyPaymentDetails' name='TabBodyPaymentDetails' tab='2' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Commission Scale</td>
							<td>{$arrDetails['commissionScale']}</td>
						</tr>
						<tr>
							<td class='title'>Royalty Scale</td>
							<td>{$arrDetails['royaltyScale']}</td>
						</tr>
						<tr>
							<td class='title'>Bank BSB</td>
							<td>{$arrDetails['bankAccountBsb']}</td>
						</tr>
						<tr>
							<td class='title'>Bank Account Number</td>
							<td>{$arrDetails['bankAccountNumber']}</td>
						</tr>
						<tr>
							<td class='title'>Bank Account Name</td>
							<td>{$arrDetails['bankAccountName']}</td>
						</tr>
						<tr>
							<td class='title'>gstRegistered</td>
							<td>{$arrDetails['gstRegistered']}</td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodyAddressDetails' name='TabBodyAddressDetails' tab='3' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:30%'>Address</td>
							<td>{$arrDetails['address']}</td>
						</tr>
						<tr>
							<td class='title'>Postal Address</td>
							<td>{$arrDetails['postalAddress']}</td>
						</tr>
					</table>
				</div>
				<div class='tab-body' id='TabBodySalesConstraintsDetails' name='TabBodySalesConstraintsDetails' tab='4' style='display:none'>
					<table class='form-data'>
						<tr>
							<td class='title' style='width:20%'>Sale Types</td>
							<td>
								<div style='overflow:auto;max-height:5em;width:100%;'>
									{$arrDetails['saleTypes']}
								</div>
							</td>
						</tr>
						<tr>
							<td class='title'>Customer Groups</td>
							<td>
								<div style='overflow:auto;max-height:5em;width:100%;'>
									{$arrDetails['customerGroups']}
								</div>
							</td>
						</tr>
						<tr>
							<td class='title'>Rate Plans</td>
							<td>
								<div style='overflow:auto;max-height:15em;width:100%;'>
									{$arrDetails['ratePlans']}
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div> <!-- TabBodyContainer -->
		</div> <!-- TabCollection -->
	</form>

	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' value='Edit' onclick='Dealer.editDealer($intDealerId, null, Dealer.CALLING_PAGE_VIEW_DEALER_POPUP)'></input>
			<input type='button' value='Close' onclick='Dealer.closeViewDealerPopup()'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>

</div>
";

			$arrData = array(	"Dealer"			=> $objDealer->toArray(),
								"DealerName"		=> htmlspecialchars($objDealer->getName())
							);


			return array(	"Success"		=> TRUE,
							"PopupContent"	=> $strHtml,
							"Data"			=> $arrData
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	
	// It is a precondition that all properties passed are of their correct data type
	// Empty strings will be converted to NULLs
	public function saveDealerDetails($objDetails)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		try
		{
			// Convert the details object into an associative array
			$arrDetails = array();
			foreach ($objDetails as $strPropName=>$mixPropValue)
			{
				$arrDetails[$strPropName] = $mixPropValue;
			}
			
			// Validate the details
			$mixResult = Dealer::parseDealerDetails($arrDetails);
			
			if (is_array($mixResult))
			{
				// $mixResult is an array of strings defining the problems encountered when parsing $arrDetails
				throw new Exception("The following problems were found in the submitted dealer details: ". implode(", ", $mixResult));
			}

			$objDealer = $mixResult;
			
			// Save the dealer (if they are new, then this will set the id of the dealer)
			
			TransactionStart();
			$objDealer->save();
			TransactionCommit();
			
			// Do a PUSH of all sales related data in flex, to the sales database
			try
			{
				Cli_App_Sync_SalesPortal::pushAll();
			}
			catch (Exception $e)
			{
				// Pushing the data failed
				$strWarning = "Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.  (Error message: ". htmlspecialchars($e->getMessage()) .")";
			}
			
			$arrReturn = array(	"Success"	=> TRUE,
								"Dealer"	=> $objDealer->toArray()
								);
			if (isset($strWarning) && strlen($strWarning))
			{
				$arrReturn['Warning'] = $strWarning;
			}
			
			return $arrReturn;
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
}

?>
