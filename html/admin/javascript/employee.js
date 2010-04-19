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
	
	save	: function()
	{
		// Prepare Operations
		// TODO
		
		// Save
		// TODO
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
Employee.oProperties.Archived.oDefinition.mMandatory	= true;
