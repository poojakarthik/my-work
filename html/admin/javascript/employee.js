var Employee	= Class.create
({
	initialize	: function(iEmployeeId, fnCallback, bLoadPermissions)
	{
		if (iEmployeeId)
		{
			// Only load details if a callback function provided
			if (fnCallback)
			{
				this._bLoadPermissions	= bLoadPermissions;
				this._load(iEmployeeId, fnCallback)
			}
			else
			{
				// Store the Id only
				this.oProperties	= {Id: iEmployeeId};
			}
		}
		else
		{
			// New Employee
			this.oProperties	= {};
		}
	},
	
	addNew	: function()
	{
		
	},
	
	view	: function()
	{
		
	},
	
	edit	: function()
	{
		
	},
	
	getPermissions	: function(fnCallback, oResponse)
	{
		if (oResponse)
		{
			this.aOperationIds			= oResponse.aOperationIds;
			this.aOperationProfileIds	= oResponse.aOperationProfileIds;
			
			// Process JSON Response
			fnCallback(this);
		}
		else if (!this.oProperties.Id && this.oProperties.Id !== 0)
		{
			// New Employee -- can't have any permissions yet
			fnCallback([], []);
		}
		else
		{
			// Make JSON Request
			var iEmployeeId	= (this.oProperties.Id === undefined) ? null : this.oProperties.Id;
			
			var fnJSON	= jQuery.json.jsonFunction(this.getPermissions.bind(this, fnCallback), null, 'Employee', 'getPermissions');
			fnJSON(iEmployeeId);
		}
	},
	
	setPermissions	: function(aOperationProfileIds, aOperationIds, fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make ajax request
			if (this.oProperties.Id !== 'undefined')
			{
				// Create json function
				var fnJSON	= 	jQuery.json.jsonFunction(
									this.setPermissions.bind(
										this,
										aOperationProfileIds, 
										aOperationIds, 
										fnCallback
									), 
									null, 
									'Employee', 
									'setPermissions'
								);
				fnJSON(this.oProperties.Id, aOperationProfileIds, aOperationIds);
			}
		}
		else if(oResponse.Success)
		{
			//this.aOperationProfileIds 	= aOperationProfileIds;
			//this.aOperationIds			= aOperationIds;
			
			if (fnCallback)
			{
				fnCallback(oResponse);
			}
		}
		else
		{
			// AJAX Error
			Reflex_Popup.alert(oResponse.Message ? oResponse.Message : '');
		}
	},
	
	getControls	: function()
	{
		this._refreshControls();
		
		return this.oPropertyControls;
	},
	
	_refreshControls	: function()
	{
		if (!this.oPropertyControls)
		{
			this.oPropertyControls	= {};
			for (sProperty in Employee.oProperties)
			{
				this.oPropertyControls[sProperty]	= Control_Field.factory(Employee.oProperties[sProperty].sType, Employee.oProperties[sProperty].oDefinition);
			}
		}
		
		for (sProperty in this.oPropertyControls)
		{
			if (Object.keys(this.oProperties).length)
			{
				this.oPropertyControls[sProperty].setValue(this.oProperties[sProperty]);
			}
			else
			{
				// FIXME: Default values instead?
				this.oPropertyControls[sProperty].setValue('');
			}
		}
	},
	
	_load	: function(iEmployeeId, fnCallback, oResponse)
	{
		if (oResponse)
		{
			// Set properties
			this.oProperties	= oResponse.objEmployee;
			
			// Callback
			if (fnCallback)
			{
				// Load permissions or callback, depending on setup
				if (this._bLoadPermissions)
				{
					this.getPermissions(fnCallback);
				}
				else
				{
					fnCallback(this);
				}
			}
		}
		else
		{
			var fnJSON	= jQuery.json.jsonFunction(this._load.bind(this, iEmployeeId, fnCallback), null, 'Employee', 'getForId');
			fnJSON(iEmployeeId, true);
		}
	},
	
	refresh	: function()
	{
		if (this.oProperties && this.oProperties.Id !== null && this.oProperties.Id !== undefined)
		{
			this._load(this.oProperties.Id, this._refreshControls.bind(this));
		}
	},
	
	save	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Validate control values
			var aValidationErrors	= [];
			var oControl			= null;
			var mValidationResult	= null;
			for (var sName in this.oPropertyControls)
			{
				oControl			= this.oPropertyControls[sName];
				mValidationResult	= oControl.validate();
				
				if (mValidationResult !== true)
				{
					aValidationErrors.push(mValidationResult);
				}
			}
			
			// Return with errors if there were any, otherwise continue
			if (aValidationErrors.length)
			{
				this.showValidationErrors(aValidationErrors);
				return;
			}
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Build employee details for json handler
			var oDetails	= {
				sUserName				: this.oPropertyControls['UserName'].getValue(true),
				sFirstName				: this.oPropertyControls['FirstName'].getValue(true),
				sLastName				: this.oPropertyControls['LastName'].getValue(true),
				sDOB					: this.oPropertyControls['DOB'].getValue(true),
				sEmail					: this.oPropertyControls['Email'].getValue(true),
				sExtension				: this.oPropertyControls['Extension'].getValue(true),
				sPhone					: this.oPropertyControls['Phone'].getValue(true),
				sMobile					: this.oPropertyControls['Mobile'].getValue(true),
				sPassword				: this.oPropertyControls['PassWord'].getValue(true),
				sPasswordConfirm		: this.oPropertyControls['PassWordConfirm'].getValue(true),
				iArchived				: (this.oPropertyControls['Archived'].getValue(true) ? 1 : 0),
				iTicketingPermission	: this.oPropertyControls['ticketing_permission'].getValue(true),
				iUserRoleId				: this.oPropertyControls['user_role_id'].getValue(true),
				iPriviledges			: this.oPropertyControls['Privileges'].getValue(true)	// TODO REMOVE ME
			}
			
			// Make ajax request
			var fnSave	= 	jQuery.json.jsonFunction(
								this.save.bind(this, fnCallback),
								this.save.bind(this, fnCallback), 
								'Employee', 
								'save'
							);
			fnSave(this.oProperties.Id, oDetails)
		}
		else if (oResponse.Success)
		{
			// All good!
			fnCallback();
			
			this.oLoading.hide();
			delete this.oLoading;
		}
		else
		{
			// Error occurred, either validation or exception
			if (oResponse.aValidationErrors)
			{
				this.showValidationErrors(oResponse.aValidationErrors);
			}
			else if (oResponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message);
			}
			else
			{
				Reflex_Popup.alert('An error occured saving the employee information');
			}
		}
	},
	
	showValidationErrors	: function(aValidationErrors)
	{
		// Create a UL to list the errors and then show a reflex alert
		var oAlertDom	=	$T.div({class: 'employee-validation-errors'},
								$T.div('There were errors in the employee information: '),
								$T.ul(
									// Added here...
								)
							);
		var oUL	= oAlertDom.select('ul').first();
		
		for (var i = 0; i < aValidationErrors.length; i++)
		{
			oUL.appendChild($T.li(aValidationErrors[i]));
		}
		
		Reflex_Popup.alert(oAlertDom, {iWidth: 30});
	}
});

//----------------------------------------------------------------------------//
// Static Methods
//----------------------------------------------------------------------------//
Employee.getForId	= function(iEmployeeId, fnCallback, bLoadPermissions)
{
	return new Employee(iEmployeeId, fnCallback, bLoadPermissions);
}
//----------------------------------------------------------------------------//

// Static Members
Employee.oProperties	= {};

// Id
Employee.oProperties.Id			= {};
Employee.oProperties.Id.sType	= 'text';

Employee.oProperties.Id.oDefinition			= {};
Employee.oProperties.Id.oDefinition.sLabel	= 'Id';

// First Name
Employee.oProperties.FirstName			= {};
Employee.oProperties.FirstName.sType	= 'text';

Employee.oProperties.FirstName.oDefinition				= {};
Employee.oProperties.FirstName.oDefinition.sLabel		= 'First Name';
Employee.oProperties.FirstName.oDefinition.mEditable	= true;
Employee.oProperties.FirstName.oDefinition.mMandatory	= true;
Employee.oProperties.FirstName.oDefinition.mAutoTrim	= true;
Employee.oProperties.FirstName.oDefinition.iMaxLength	= 255;

// Last Name
Employee.oProperties.LastName		= {};
Employee.oProperties.LastName.sType	= 'text';

Employee.oProperties.LastName.oDefinition				= {};
Employee.oProperties.LastName.oDefinition.sLabel		= 'Last Name';
Employee.oProperties.LastName.oDefinition.mEditable		= true;
Employee.oProperties.LastName.oDefinition.mMandatory	= true;
Employee.oProperties.LastName.oDefinition.mAutoTrim		= true;
Employee.oProperties.LastName.oDefinition.iMaxLength	= 255;

// Username
Employee.oProperties.UserName		= {};
Employee.oProperties.UserName.sType	= 'text';

Employee.oProperties.UserName.oDefinition				= {};
Employee.oProperties.UserName.oDefinition.sLabel		= 'Username';
Employee.oProperties.UserName.oDefinition.mEditable		= true;
Employee.oProperties.UserName.oDefinition.mMandatory	= true;
Employee.oProperties.UserName.oDefinition.mAutoTrim		= true;
Employee.oProperties.UserName.oDefinition.iMaxLength	= 31;

// Date of Birth
Employee.oProperties.DOB			= {};
Employee.oProperties.DOB.sType		= 'date-picker';

Employee.oProperties.DOB.oDefinition			= {};
Employee.oProperties.DOB.oDefinition.sLabel		= 'Date of Birth';
Employee.oProperties.DOB.oDefinition.mEditable	= true;
Employee.oProperties.DOB.oDefinition.mMandatory	= true;
//Employee.oProperties.DOB.oDefinition.fnValidate	= Reflex_Validation.date.bind(Reflex_Validation);

// Email
Employee.oProperties.Email			= {};
Employee.oProperties.Email.sType	= 'text';

Employee.oProperties.Email.oDefinition				= {};
Employee.oProperties.Email.oDefinition.sLabel		= 'Email';
Employee.oProperties.Email.oDefinition.mEditable	= true;
Employee.oProperties.Email.oDefinition.mMandatory	= true;
Employee.oProperties.Email.oDefinition.mAutoTrim	= true;
Employee.oProperties.Email.oDefinition.iMaxLength	= 255;
Employee.oProperties.Email.oDefinition.fnValidate	= Reflex_Validation.email.bind(Reflex_Validation);

// Extension
Employee.oProperties.Extension			= {};
Employee.oProperties.Extension.sType	= 'text';

Employee.oProperties.Extension.oDefinition				= {};
Employee.oProperties.Extension.oDefinition.sLabel		= 'Extension';
Employee.oProperties.Extension.oDefinition.mEditable	= true;
Employee.oProperties.Extension.oDefinition.mAutoTrim	= true;
Employee.oProperties.Extension.oDefinition.iMaxLength	= 15;
Employee.oProperties.Extension.oDefinition.fnValidate	= Reflex_Validation.digits.bind(Reflex_Validation);

// Phone
Employee.oProperties.Phone			= {};
Employee.oProperties.Phone.sType	= 'text';

Employee.oProperties.Phone.oDefinition				= {};
Employee.oProperties.Phone.oDefinition.sLabel		= 'Phone';
Employee.oProperties.Phone.oDefinition.mEditable	= true;
Employee.oProperties.Phone.oDefinition.mAutoTrim	= function(strPhone){return strPhone.replace(/\s+/, '');};
Employee.oProperties.Phone.oDefinition.iMaxLength	= 25;
Employee.oProperties.Phone.oDefinition.fnValidate	= Reflex_Validation.fnnFixedLine.bind(Reflex_Validation);

// Mobile
Employee.oProperties.Mobile			= {};
Employee.oProperties.Mobile.sType	= 'text';

Employee.oProperties.Mobile.oDefinition				= {};
Employee.oProperties.Mobile.oDefinition.sLabel		= 'Mobile';
Employee.oProperties.Mobile.oDefinition.mEditable	= true;
Employee.oProperties.Mobile.oDefinition.mAutoTrim	= function(strPhone){return strPhone.replace(/\s+/, '');};
Employee.oProperties.Mobile.oDefinition.iMaxLength	= 25;
Employee.oProperties.Mobile.oDefinition.fnValidate	= Reflex_Validation.fnnMobile.bind(Reflex_Validation);

// Password
Employee.oProperties.PassWord		= {};
Employee.oProperties.PassWord.sType	= 'password';

Employee.oProperties.PassWord.oDefinition				= {};
Employee.oProperties.PassWord.oDefinition.sLabel		= 'Password';
Employee.oProperties.PassWord.oDefinition.mEditable		= true;
Employee.oProperties.PassWord.oDefinition.mMandatory	= true;
Employee.oProperties.PassWord.oDefinition.iMaxLength	= 40;

// Password Confirmation
Employee.oProperties.PassWordConfirm		= {};
Employee.oProperties.PassWordConfirm.sType	= 'password';

Employee.oProperties.PassWordConfirm.oDefinition			= {};
Employee.oProperties.PassWordConfirm.oDefinition.sLabel		= 'Confirm Password';
Employee.oProperties.PassWordConfirm.oDefinition.mEditable	= true;
Employee.oProperties.PassWordConfirm.oDefinition.mMandatory	= true;
Employee.oProperties.PassWordConfirm.oDefinition.iMaxLength	= 40;

// Role
Employee.oProperties.user_role_id		= {};
Employee.oProperties.user_role_id.sType	= 'select';

Employee.oProperties.user_role_id.oDefinition				= {};
Employee.oProperties.user_role_id.oDefinition.sLabel		= 'Role';
Employee.oProperties.user_role_id.oDefinition.mEditable		= true;
Employee.oProperties.user_role_id.oDefinition.mMandatory	= true;
Employee.oProperties.user_role_id.oDefinition.fnPopulate	= User_Role.getAllAsSelectOptions.bind(User_Role);

// is_god
Employee.oProperties.is_god			= {};
Employee.oProperties.is_god.sType	= 'checkbox';

Employee.oProperties.is_god.oDefinition				= {};
Employee.oProperties.is_god.oDefinition.sLabel		= 'GOD User';
Employee.oProperties.is_god.oDefinition.mMandatory	= true;

// Archived
Employee.oProperties.Archived		= {};
Employee.oProperties.Archived.sType	= 'checkbox';

Employee.oProperties.Archived.oDefinition				= {};
Employee.oProperties.Archived.oDefinition.sLabel		= 'Archived';
Employee.oProperties.Archived.oDefinition.mEditable		= true;
//Employee.oProperties.Archived.oDefinition.mMandatory	= true;

Employee.oProperties.ticketing_permission							= {};
Employee.oProperties.ticketing_permission.sType						= 'select';
Employee.oProperties.ticketing_permission.oDefinition				= {};
Employee.oProperties.ticketing_permission.oDefinition.sLabel		= 'Ticketing System';
Employee.oProperties.ticketing_permission.oDefinition.mEditable		= true;
Employee.oProperties.ticketing_permission.oDefinition.mMandatory	= true;
Employee.oProperties.ticketing_permission.oDefinition.fnPopulate	= Ticketing_User_Permission.getAllAsSelectOptions.bind(Ticketing_User_Permission);

// TODO REMOVE ME
Employee.oProperties.Privileges						= {};
Employee.oProperties.Privileges.sType				= 'text';
Employee.oProperties.Privileges.oDefinition			= {};
Employee.oProperties.Privileges.oDefinition.sLabel	= 'ERROR This should not be seen';
