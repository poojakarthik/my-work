<?php
class AppTemplateEmployee extends ApplicationTemplate
{
	function ViewRecentCustomers() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Retrieve that last 20 customers that were verified by the user
		$arrColumns = array(
			"RequestedOn" => "eal.viewed_on",
			"AccountId" => "A.Id",
			"BusinessName" => "A.BusinessName",
			"TradingName" => "A.TradingName",
			"ContactId" => "C.Id",
			"Title" => "C.Title",
			"FirstName" => "C.FirstName",
			"LastName" => "C.LastName"
		);
		$strTables = "Contact AS C RIGHT JOIN employee_account_log AS eal ON eal.contact_id = C.Id INNER JOIN Account AS A ON eal.account_id = A.Id";
		$strWhere = "eal.employee_id = <UserId>";
		$arrWhere = array("UserId" => AuthenticatedUser()->_arrUser['Id']);
		$strOrderBy = "eal.viewed_on DESC";
		$strLimit = "20";

		DBL()->RecentCustomers->SetColumns($arrColumns);
		DBL()->RecentCustomers->SetTable($strTables);
		DBL()->RecentCustomers->Where->Set($strWhere, $arrWhere);
		DBL()->RecentCustomers->OrderBy($strOrderBy);
		DBL()->RecentCustomers->SetLimit($strLimit);

		DBL()->RecentCustomers->Load();

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('recent_customers_view');

		return true;
	}

	function Logout() {
		// Logout the employee
		AuthenticatedUser()->Logout();
		// Redirect the user to the login page
		header("Location: " . preg_replace("/flex.php\/.*$/", "index.php", $_SERVER['REQUEST_URI']));
		die;
	}

	function EmployeeList() {
		// Removed until permissions release. rmctainsh 20100429
		/*
		//////////////////////////////
		//////////////////////////////
		// DEPRECATED FEATURE
		try {
			// Create an assertion
			Flex::assert(
				false, 
				"CLASS: AppTemplateEmployee, the function EmployeeList was called.", 
				null, 
				"Deprecated Function Accessed: AppTemplateEmployee::EmployeeList"
			);
		} catch (Exception_Assertion $e) {
			// The assert function has sent the email already, redirect to the new version
			header('Location: '.preg_replace("/flex.php\/.*$/", "reflex.php/Employee/EmployeeList/", $_SERVER['REQUEST_URI']));
			die;
		}
		// DEPRECATED FEATURE
		//////////////////////////////
		//////////////////////////////
		*/
		
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Context menu
		// (Nothing to add)

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Employee List");

		// Retrieve all Employees except the system user
		$strWhere = "Id != ". USER_ID;

		if (!array_key_exists('Archived', $_POST) || $_POST['Archived'] != 1) {
			$strWhere .= " AND Archived = 0";
		}
		DBL()->Employee->Where->SetString($strWhere);

		DBL()->Employee->OrderBy("LastName, FirstName, UserName");

		DBL()->Employee->Load();

		$this->LoadPage('employee_list');

		return true;
	}

	function EmployeeListAjax() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Breadcrumb menu
		Breadcrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Employee List");

		// If only want non-archived Employees...
		$strWhere = "Id != ". USER_ID;
		if (DBO()->Search->Archived->Value == "0") {
			$strWhere .= " AND Archived = 0";
		}

		$orderBy = "LastName, FirstName, UserName";
		if (DBO()->Search->OrderBy->Value != "") {
			$orderBy = DBO()->Search->OrderBy->Value;
			if (DBO()->Search->OrderDesc->Value) {
				$orderBy .= " desc";
			}
		}

		DBL()->Employee->Where->SetString($strWhere);
		DBL()->Employee->OrderBy($orderBy);

		DBL()->Employee->Load();

		$this->LoadPage('employee_view_list');

		return true;
	}

	function EmployeeDetails() {
		AuthenticatedUser()->CheckAuth();
		DBO()->Employee->Id = AuthenticatedUser()->GetUserId();
		DBO()->Employee->Load();
		return $this->Edit(true);
	}

	function Edit($bolEditSelf=false) {
		AuthenticatedUser()->CheckAuth();
		$bolAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$bolProperAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolUserIsSelf = false;

		if (DBO()->Employee->Id->Value != AuthenticatedUser()->GetUserId()) {
			AuthenticatedUser()->PermissionOrDie($bolProperAdminUser);
		} else {
			$bolUserIsSelf = true;
		}

		$bolEditSelf = $bolEditSelf || DBO()->Employee->EditSelf->Value;

		//check if the form was submitted
		if (SubmittedForm('Employee', 'Save')) {
			// Start the transaction
			TransactionStart();

			try {
				// Determine if this is to be a new employee
				$bolCreateNew = DBO()->Employee->Id->Value < 0;

				$arrValidationErrors = array();

				if ($bolCreateNew) {
					DBO()->Employee->UserName = trim(DBO()->Employee->UserName->Value);
					DBO()->Employee->UserName->ValidateProperty($arrValidationErrors, true);
					if (!DBO()->Employee->UserName->IsInvalid()) {
						$strWhere = "Employee.UserName = <UserName>";
						$arrWhere = array("UserName" => DBO()->Employee->UserName->Value);
						DBL()->RecentCustomers->SetColumns(array("UserName"));
						DBL()->RecentCustomers->SetTable("Employee");
						DBL()->RecentCustomers->Where->Set($strWhere, $arrWhere);
						DBL()->RecentCustomers->SetLimit(1);
						DBL()->RecentCustomers->Load();
						if (DBL()->RecentCustomers->RecordCount() > 0) {
							DBO()->Employee->UserName->SetToInvalid();
							$strLabel = DBO()->Employee->UserName->strGetLabel();
							$arrValidationErrors[] = "$strLabel \"" . DBO()->Employee->UserName->Value . "\" is already in use.";
						}
					}
					DBO()->Employee->is_god = 0;
				}

				if (!$bolEditSelf && $bolAdminUser) {
					DBO()->Employee->FirstName = trim(DBO()->Employee->FirstName->Value);
					DBO()->Employee->FirstName->ValidateProperty($arrValidationErrors, true, CONTEXT_DEFAULT, "IsNotEmptyString");
					DBO()->Employee->LastName = trim(DBO()->Employee->LastName->Value);
					DBO()->Employee->LastName->ValidateProperty ($arrValidationErrors, true, CONTEXT_DEFAULT, "IsNotEmptyString");

					DBO()->Employee->DOB = trim(DBO()->Employee->DOB->Value);
					DBO()->Employee->DOB = UnmaskShortDate(DBO()->Employee->DOB->Value);
					DBO()->Employee->DOB->ValidateProperty ($arrValidationErrors, true, CONTEXT_DEFAULT, "IsValidDate");
					DBO()->Employee->DOB->ValidateProperty ($arrValidationErrors, true, CONTEXT_DEFAULT, "IsValidDateInPast", "<label> must be in the past.");
				}

				DBO()->Employee->Email = trim(DBO()->Employee->Email->Value);
				DBO()->Employee->Email->ValidateProperty ($arrValidationErrors, false, CONTEXT_DEFAULT, "IsValidEmail");
				DBO()->Employee->Extension = trim(DBO()->Employee->Extension->Value);
				DBO()->Employee->Extension->ValidateProperty($arrValidationErrors, false, CONTEXT_DEFAULT);

				DBO()->Employee->Phone = trim(DBO()->Employee->Phone->Value);
				DBO()->Employee->Phone->ValidateProperty ($arrValidationErrors, false, CONTEXT_DEFAULT, "IsValidPhoneNumber");

				DBO()->Employee->Mobile = trim(DBO()->Employee->Mobile->Value);
				DBO()->Employee->Mobile->ValidateProperty ($arrValidationErrors, false, CONTEXT_DEFAULT, "IsValidPhoneNumber");

				// Check that the password has been entered and confirmed, as appropriate
				$this->_ValidatePassword($arrValidationErrors, $bolCreateNew);

				if (!$bolEditSelf && $bolAdminUser) {
					// Sanitize the permissions that have been set
					$this->_SetPrivileges();
				}

				if (!$bolCreateNew) {
					// Restrict the fields that can be updated
					$updatedColumns = array();
					$updatedColumns[] = "Email";
					$updatedColumns[] = "Extension";
					$updatedColumns[] = "Phone";
					$updatedColumns[] = "Mobile";

					// Only change the following through the admin console, not when editing self
					if (!$bolEditSelf && $bolAdminUser) {
						$updatedColumns[] = "FirstName";
						$updatedColumns[] = "LastName";
						$updatedColumns[] = "DOB";
						$updatedColumns[] = "Archived";
						$updatedColumns[] = "Privileges";
						$updatedColumns[] = "user_role_id";
					}

					// If changing the password, allow  it to be updated
					if (!DBO()->Employee->Password->IsInvalid() && strlen(DBO()->Employee->Password->Value) > 0) {
						$updatedColumns[] = "PassWord";
					}

					// Fill in any blanks from the original
					DBO()->Employee->LoadMerge();

					// Need to specifiy the columns to be updated to ensure no other values are changed
					DBO()->Employee->SetColumns($updatedColumns);
				} else {
					DBO()->Employee->DOB = GetCurrentDateForMySQL();

					// Apply default values for non-nullable fields
					DBO()->Employee->SessionId = "";
					DBO()->Employee->SessionExpire = GetCurrentDateAndTimeForMySQL();
					DBO()->Employee->Session = "";
					DBO()->Employee->Karma = 0;
					DBO()->Employee->PabloSays = PABLO_TIP_POLITE;
					DBO()->Employee->Archived = 0;
				}

				//Save the employee
				if (!DBO()->Employee->IsInvalid()) {
					// This could update multiple tables, so needs to be done within a single transaction

					//echo "Employee is NOT invalid Employee would be saved";

					if (DBO()->Employee->Save()) {
						if (Flex_Module::isActive(FLEX_MODULE_TICKETING)) {
							$currentUserTicketingPermission = Ticketing_User::getPermissionForEmployeeId(AuthenticatedUser()->GetUserId());
							if ($bolUserIsSelf) {
								$displayUserTicketingPermission = $currentUserTicketingPermission;
							} else {
								$displayUserTicketingPermission = Ticketing_User::getPermissionForEmployeeId(DBO()->Employee->Id->Value);
							}
							if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) || (!$bolUserIsSelf && $currentUserTicketingPermission == TICKETING_USER_PERMISSION_ADMIN)) {
								Ticketing_User::setPermissionForEmployeeId(DBO()->Employee->Id->Value, intval(DBO()->ticketing_user->permission->Value));
							}
						}

						// If the user has the Sales privilege then create/update their dealer record
						$intEmployeePerms = DBO()->Employee->Privileges->Value;
						if (Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL)) {
							try {
								$bolModifiedDealerTable = false;
								$objDealerConfig = Dealer_Config::getConfig();
								$objDefaultEmployeeManagerDealer = ($objDealerConfig->defaultEmployeeManagerDealerId !== null)? Dealer::getForId($objDealerConfig->defaultEmployeeManagerDealerId) : null;
								if (($intEmployeePerms & PERMISSION_SALES) == PERMISSION_SALES) {
									// The Employee has the Sales permission
									// Check if they already have a dealer record
									$objDealer = Dealer::getForEmployeeId(DBO()->Employee->Id->Value);

									if ($objDealer === null && DBO()->Employee->Archived->Value == 0) {
										// A dealer record doesn't exist, but the employee is active, so create one
										$objDealer = new Dealer();

										// All employees get the "can verify" flag, initially
										$objDealer->canVerify = true;
										$objDealer->gstRegistered = false;
										$objDealer->syncSaleConstraints = true;
										$objDealer->clawbackPeriod = 0;
										$objDealer->createdOn = GetCurrentISODateTime();
										$objDealer->upLineId = $objDealerConfig->defaultEmployeeManagerDealerId;
									}

									if ($objDealer !== null) {
										if ($objDealer->id !== null && DBO()->Employee->Archived->Value) {
											// The dealer is already established, but has been archived
											// Make sure they are not currently set as the default Employee Manager
											if ($objDefaultEmployeeManagerDealer !== null && $objDealer->id === $objDefaultEmployeeManagerDealer->id) {
												throw new Exception("This employee is currently set up as the Default Manager for employee dealers and therefore can not be archived.  If you wish to archive this employee, then please declare a different Default Manager for employee dealers.");
											}
										}

										// Update the record
										$objDealer->firstName = DBO()->Employee->FirstName->Value;
										$objDealer->lastName = DBO()->Employee->LastName->Value;
										$objDealer->username = DBO()->Employee->UserName->Value;
										$objDealer->password = DBO()->Employee->PassWord->Value;
										$objDealer->phone = DBO()->Employee->Phone->Value;
										$objDealer->mobile = DBO()->Employee->Mobile->Value;
										$objDealer->email = DBO()->Employee->Email->Value;
										$objDealer->dealerStatusId = (DBO()->Employee->Archived->Value == 0)? Dealer_Status::ACTIVE : Dealer_Status::INACTIVE;
										$objDealer->employeeId = DBO()->Employee->Id->Value;
										$objDealer->save();
										$bolModifiedDealerTable = true;
									}
								} else {
									// The employee doesn't have the sales permission
									// If they have a related dealer record, then de-activate it, but check that they are not currently set as the Default Manager for Employee Dealers
									$objDealer = Dealer::getForEmployeeId(DBO()->Employee->Id->Value);
									if ($objDealer !== null && $objDealer->dealerStatusId != Dealer_Status::INACTIVE) {
										// A dealer record exists and it isn't set to inactive, so set it

										// Check that the dealer isn't currently set as the Default Manager for Employee Dealers
										if ($objDefaultEmployeeManagerDealer !== null && $objDefaultEmployeeManagerDealer->id == $objDealer->id) {
											throw new Exception("This employee is currently set up as the Default Manager for employee dealers and therefore must keep the sales permission.  If you wish to remove this employee's sales permission, then please declare a different Default Manager for employee dealers.");
										}

										$objDealer->dealerStatusId = Dealer_Status::INACTIVE;
										$objDealer->save();
										$bolModifiedDealerTable = true;
									}
								}

							} catch (Exception $e) {
								TransactionRollback();
								Ajax()->AddCommand("Alert", "Error doing dealer stuff<br />". $e->getMessage());
								return true;
							}
						}

						// All Database interactions were successfull
						TransactionCommit();

						if (isset($bolModifiedDealerTable) && $bolModifiedDealerTable && Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL)) {
							// Dealer table has been modified, trigger the sync operation
							try {
								Cli_App_Sales::pushAll();
							} catch (Exception $e) {
								// Pushing the data failed
								$strWarning = "Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.  (Error message: ". htmlspecialchars($e->getMessage()) .")";
							}
						}


						$scriptInit = "";
						$scriptOnClose = "";
						if ($bolEditSelf) {
							$scriptInit = "document.getElementsByTagName('TABLE')[0].style.display = 'none';";
							$scriptOnClose .= "Vixen.Popup.Close('CloseFlexModalWindow');";
						} else {
							$scriptInit .= "Vixen.Popup.Close('" . $this->_objAjax->strId . "');";
							//$scriptInit .= "EmployeeView.Update();";
							$scriptOnClose .= "EmployeeView.Update();";
						}
						$scriptInit = "Vixen.Popup.Close('{$this->_objAjax->strId}');";
						$scriptOnClose = "if (window.EmployeeView) {EmployeeView.Update();}";

						$arrParams = array();
						$arrParams["Message"] = "The information was successfully saved.";
						if (isset($strWarning)) {
							$arrParams["Message"] .= "<br />$strWarning";
						}
						$arrParams["ScriptInit"] = $scriptInit;
						$arrParams["ScriptOnClose"] = $scriptOnClose;

						Ajax()->AddCommand("AlertAndExecuteJavascript", $arrParams);
						return true;
					}
				}
			} catch(Exception $e) {

			}

			// Rollback the changes
			TransactionRollback();

			Ajax()->AddCommand("Alert", "The information could not be saved.<br />Please correct the following: -<br />" . implode("<br />\n", $arrValidationErrors));
			return true;
		}
		DBO()->Employee->Load();
		DBO()->Employee->EditSelf = $bolEditSelf;

		if (!$this->IsModal()) {
			$this->LoadPage('employee_edit');
		} else {
			$this->LoadPage('flex_modal_window');

			// set the page title
			$this->Page->SetName('Employee Detail');

			$this->Page->AddObject('EmployeeEdit', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);

		}

		return true;
	}

	function Create() {
		return $this->Edit();
	}

	private function _SetPrivileges() {
		// This is the old way, when each permission was a single bit, but now, some permissions automatically include others
		//$proposedPrivileges = array_sum(DBO()->Employee->Privileges->Value);

		// Logically OR all the privileges together
		$arrPrivileges = DBO()->Employee->Privileges->Value;
		$proposedPrivileges = 0;
		foreach ($arrPrivileges as $intPrivilege) {
			$proposedPrivileges = $proposedPrivileges | $intPrivilege;
		}

		$originalPrivileges = 0;
		if (DBO()->Employee->Id->Value >= 0) {
			// Need to get the current privileges of the user
			DBO()->CurrentEmployee->SetTable("Employee");
			DBO()->CurrentEmployee->Id = DBO()->Employee->Id->Value;
			DBO()->CurrentEmployee->Load();
			$originalPrivileges = DBO()->CurrentEmployee->Privileges->Value;
		}

		// Don't allow super admin, god or debug privileges to be changed
		//$proposed = $this->_PreservePrivileges($originalPrivileges, $proposedPrivileges, PERMISSION_SUPER_ADMIN | PERMISSION_DEBUG);
		$proposed = $this->_PreservePrivileges($originalPrivileges, $proposedPrivileges, array(PERMISSION_DEBUG, USER_PERMISSION_GOD));

		if (!AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN)) {
			// The user isn't a SuperAdmin, so don't allow them to change the CustomerGroupAdmin privilege, or the KB_ADMIN_USER privilege or the Credit Management privilege
			$proposed = $this->_PreservePrivileges($originalPrivileges, $proposed, array(PERMISSION_CUSTOMER_GROUP_ADMIN, PERMISSION_KB_ADMIN_USER, PERMISSION_CREDIT_MANAGEMENT));
		}

		DBO()->Employee->Privileges = $proposed;
	}

	private function _PreservePrivileges($intOriginal, $intProposed, $mixPrivileges) {
		$arrPrivileges = (is_array($mixPrivileges))? $mixPrivileges : array($mixPrivileges);

		//TODO! iterate through the array of privileges, and if it is in $original, then remove it from $proposed
		foreach ($arrPrivileges as $intPrivilege) {
			if (($intOriginal & $intPrivilege) == $intPrivilege) {
				// The privilege is in the original.  Add it to the proposed
				$intProposed = $intProposed | $intPrivilege;
			} else {
				// The privilege is not in the original.  Remove it from the proposed, but only if it is in the proposed
				if (($intProposed & $intPrivilege) == $intPrivilege) {
					$intProposed = $intProposed - $intPrivilege;
				}

			}
		}

		return $intProposed;

		/*OLD method which doesn't handle privilege groups such as SuperAdmin

		// Prevent the privilege being added by proposition
		$proposed = $proposed - ($proposed & $privileges);
		// Add the privileges if originally present
		$proposed = $proposed | ($original & $privileges);
		// Return the proposed privileges with the preserved values
		return $proposed;
		*/
	}

	private function _ValidatePassword(&$arrValidationErrors, $bolCreateNew) {
		// Validate that the password has been submitted as a 2 value array
		if (!is_array(DBO()->Employee->Password->Value) || count(DBO()->Employee->Password->Value) != 2) {
			DBO()->Employee->Password = "";
			DBO()->Employee->Password->SetToInvalid();
			$arrValidationErrors[] = "Password must be entered twice.";
		}
		// Check that neither value is empty
		else if (strlen(DBO()->Employee->Password->Value[0]) == 0 || strlen(DBO()->Employee->Password->Value[1]) == 0) {
			$bolPasswordEntered = (strlen(DBO()->Employee->Password->Value[0]) + strlen(DBO()->Employee->Password->Value[1])) > 0;
			DBO()->Employee->Password = "";
			if ($bolCreateNew || $bolPasswordEntered) {
				DBO()->Employee->Password->SetToInvalid();
				$arrValidationErrors[] = "Both Password and Password Confirmation are required.";
			}
		}
		// Check that the values are the same
		else if (DBO()->Employee->Password->Value[0] != DBO()->Employee->Password->Value[1]) {
			DBO()->Employee->Password = "";
			DBO()->Employee->Password->SetToInvalid();
			$arrValidationErrors[] = "Password does not match Password Confirmation.";
		} else {
			// Set the validated password value into the password property
			DBO()->Employee->PassWord = sha1(DBO()->Employee->Password->Value[0]);
			DBO()->Employee->Password = DBO()->Employee->Password->Value[0];
		}
	}
}