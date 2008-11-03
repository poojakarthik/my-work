<?php

class JSON_Handler_Dealer extends JSON_Handler
{
	// Builds the "Edit Dealer" popup
	public function buildEditDealerPopup($intDealerId, $intEmployeeId)
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
			</div> <!-- TabBodyContainer -->
		</div> <!-- TabCollection -->
	</form>

	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' value='Save' onclick='Dealer.saveDealerDetails()'></input>
			<input type='button' value='Close' onclick='Vixen.Popup.Close(this)'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>

</div>
";
			$arrData = array(	"Dealer"		=> $objDealer->toArray(),
								"DealerName"	=> htmlspecialchars($objDealer->getName()),
								"CountryStates"	=> $arrCountryStates
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
			$objDealer->save();
			
			return array(	"Success"		=> TRUE,
							"Dealer"		=> $objDealer->toArray()
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
}

?>
