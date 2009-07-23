var Employee	= Class.create
({
	initialize	: function(intEmployeeId, fncCallback)
	{
		if (intEmployeeId)
		{
			// Load via JSON
			var fncJSON	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this._load.bind(this, fncCallback)), null, 'Employee', 'getForId');
			fncJSON(intEmployeeId, true);
		}
		else
		{
			// New Employee
			this.objProperties	= {};
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
	
	getPermissions	: function(fCallback, oResponse)
	{
		if (oResponse)
		{
			// Process JSON Response
			fCallback(oResponse.aOperationIds, oResponse.aOperationProfileIds);
		}
		else if (!this.objProperties.id && this.objProperties !== 0)
		{
			// New Employee -- can't have any permissions yet
			fCallback([], []);
		}
		else
		{
			// Make JSON Request
			var iEmployeeId	= (this.objProperties.Id === undefined) ? null : this.objProperties.Id;
			
			var fncJSON	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this.getPermissions.bind(this, fCallback, bGetForEditing)), null, 'Employee', 'getPermissions');
			fncJSON(iEmployeeId);
		}
	},
	
	getControls	: function()
	{
		if (!this.objPropertyControls)
		{
			this.objPropertyControls	= {};
			for (strProperty in Employee.objProperties)
			{
				this.objPropertyControls[strProperty]	= Control_Field.factory(Employee.objProperties[strProperty].strType, Employee.objProperties[strProperty].objDefinition);

				// Populate with existing values
				if (Object.keys(this.objProperties).length)
				{
					this.objPropertyControls[strProperty].setValue(this.objProperties[strProperty]);
				}
				else
				{
					// FIXME: Default values instead?
					this.objPropertyControls[strProperty].setValue('');
				}
			}
		}
		
		return this.objPropertyControls;
	},
	
	_load	: function(fncCallback, objResponse)
	{
		// Set properties
		this.objProperties	= objResponse.objEmployee;
		
		// Callback
		if (fncCallback)
		{
			fncCallback(this);
		}
	}
});

//----------------------------------------------------------------------------//
// Static Methods
//----------------------------------------------------------------------------//
Employee.getForId	= function(intEmployeeId, fncCallback)
{
	return new Employee(intEmployeeId, fncCallback);
}
//----------------------------------------------------------------------------//

// Static Members
Employee.objProperties	= {};

// Id
Employee.objProperties.Id				= {};
//Employee.objProperties.Id.strType		= 'hidden';
Employee.objProperties.Id.strType		= 'text';

Employee.objProperties.Id.objDefinition				= {};
Employee.objProperties.Id.objDefinition.strLabel	= 'Id';

// First Name
Employee.objProperties.FirstName				= {};
Employee.objProperties.FirstName.strType		= 'text';

Employee.objProperties.FirstName.objDefinition				= {};
Employee.objProperties.FirstName.objDefinition.strLabel		= 'First Name';
Employee.objProperties.FirstName.objDefinition.mixEditable	= true;
Employee.objProperties.FirstName.objDefinition.mixMandatory	= true;
Employee.objProperties.FirstName.objDefinition.mixAutoTrim	= true;
Employee.objProperties.FirstName.objDefinition.intMaxLength	= 255;

// Last Name
Employee.objProperties.LastName				= {};
Employee.objProperties.LastName.strType		= 'text';

Employee.objProperties.LastName.objDefinition				= {};
Employee.objProperties.LastName.objDefinition.strLabel		= 'Last Name';
Employee.objProperties.LastName.objDefinition.mixEditable	= true;
Employee.objProperties.LastName.objDefinition.mixMandatory	= true;
Employee.objProperties.LastName.objDefinition.mixAutoTrim	= true;
Employee.objProperties.LastName.objDefinition.intMaxLength	= 255;

// Username
Employee.objProperties.UserName				= {};
Employee.objProperties.UserName.strType		= 'text';

Employee.objProperties.UserName.objDefinition				= {};
Employee.objProperties.UserName.objDefinition.strLabel		= 'Username';
Employee.objProperties.UserName.objDefinition.mixEditable	= true;
Employee.objProperties.UserName.objDefinition.mixMandatory	= true;
Employee.objProperties.UserName.objDefinition.mixAutoTrim	= true;
Employee.objProperties.UserName.objDefinition.intMaxLength	= 31;

// Date of Birth
Employee.objProperties.DOB				= {};
Employee.objProperties.DOB.strType		= 'date-picker';

Employee.objProperties.DOB.objDefinition				= {};
Employee.objProperties.DOB.objDefinition.strLabel		= 'Date of Birth';
Employee.objProperties.DOB.objDefinition.mixEditable	= true;
Employee.objProperties.DOB.objDefinition.mixMandatory	= true;
//Employee.objProperties.DOB.objDefinition.fncValidate	= Reflex_Validation.date.bind(Reflex_Validation);

// Email
Employee.objProperties.Email				= {};
Employee.objProperties.Email.strType		= 'text';

Employee.objProperties.Email.objDefinition				= {};
Employee.objProperties.Email.objDefinition.strLabel		= 'Email';
Employee.objProperties.Email.objDefinition.mixEditable	= true;
Employee.objProperties.Email.objDefinition.mixMandatory	= true;
Employee.objProperties.Email.objDefinition.mixAutoTrim	= true;
Employee.objProperties.Email.objDefinition.intMaxLength	= 255;
Employee.objProperties.Email.objDefinition.fncValidate	= Reflex_Validation.email.bind(Reflex_Validation);

// Extension
Employee.objProperties.Extension				= {};
Employee.objProperties.Extension.strType		= 'text';

Employee.objProperties.Extension.objDefinition				= {};
Employee.objProperties.Extension.objDefinition.strLabel		= 'Extension';
Employee.objProperties.Extension.objDefinition.mixEditable	= true;
Employee.objProperties.Extension.objDefinition.mixAutoTrim	= true;
Employee.objProperties.Extension.objDefinition.intMaxLength	= 15;
Employee.objProperties.Extension.objDefinition.fncValidate	= Reflex_Validation.digits.bind(Reflex_Validation);

// Phone
Employee.objProperties.Phone				= {};
Employee.objProperties.Phone.strType		= 'text';

Employee.objProperties.Phone.objDefinition				= {};
Employee.objProperties.Phone.objDefinition.strLabel		= 'Phone';
Employee.objProperties.Phone.objDefinition.mixEditable	= true;
Employee.objProperties.Phone.objDefinition.mixAutoTrim	= function(strPhone){return strPhone.replace(/\s+/, '');};
Employee.objProperties.Phone.objDefinition.intMaxLength	= 25;
Employee.objProperties.Phone.objDefinition.fncValidate	= Reflex_Validation.fnnFixedLine.bind(Reflex_Validation);

// Mobile
Employee.objProperties.Mobile				= {};
Employee.objProperties.Mobile.strType		= 'text';

Employee.objProperties.Mobile.objDefinition					= {};
Employee.objProperties.Mobile.objDefinition.strLabel		= 'Mobile';
Employee.objProperties.Mobile.objDefinition.mixEditable		= true;
Employee.objProperties.Mobile.objDefinition.mixAutoTrim		= function(strPhone){return strPhone.replace(/\s+/, '');};
Employee.objProperties.Mobile.objDefinition.intMaxLength	= 25;
Employee.objProperties.Mobile.objDefinition.fncValidate		= Reflex_Validation.fnnMobile.bind(Reflex_Validation);

// Password
Employee.objProperties.PassWord				= {};
Employee.objProperties.PassWord.strType		= 'password';

Employee.objProperties.PassWord.objDefinition				= {};
Employee.objProperties.PassWord.objDefinition.strLabel		= 'Password';
Employee.objProperties.PassWord.objDefinition.mixEditable	= true;
Employee.objProperties.PassWord.objDefinition.mixMandatory	= true;
Employee.objProperties.PassWord.objDefinition.intMaxLength	= 40;

// Password Confirmation
Employee.objProperties.PassWordConfirm				= {};
Employee.objProperties.PassWordConfirm.strType		= 'password';

Employee.objProperties.PassWordConfirm.objDefinition				= {};
Employee.objProperties.PassWordConfirm.objDefinition.strLabel		= 'Confirm Password';
Employee.objProperties.PassWordConfirm.objDefinition.mixEditable	= true;
Employee.objProperties.PassWordConfirm.objDefinition.mixMandatory	= true;
Employee.objProperties.PassWordConfirm.objDefinition.intMaxLength	= 40;

// Role
Employee.objProperties.user_role_id				= {};
Employee.objProperties.user_role_id.strType		= 'select';

Employee.objProperties.user_role_id.objDefinition				= {};
Employee.objProperties.user_role_id.objDefinition.strLabel		= 'Role';
Employee.objProperties.user_role_id.objDefinition.mixEditable	= true;
Employee.objProperties.user_role_id.objDefinition.mixMandatory	= true;
Employee.objProperties.user_role_id.objDefinition.fncPopulate	= User_Role.getAllAsSelectOptions.bind(User_Role);

// is_god
Employee.objProperties.is_god				= {};
Employee.objProperties.is_god.strType		= 'checkbox';

Employee.objProperties.is_god.objDefinition					= {};
Employee.objProperties.is_god.objDefinition.strLabel		= 'GOD User';
Employee.objProperties.is_god.objDefinition.mixMandatory	= true;

// Archived
Employee.objProperties.Archived				= {};
Employee.objProperties.Archived.strType		= 'checkbox';

Employee.objProperties.Archived.objDefinition				= {};
Employee.objProperties.Archived.objDefinition.strLabel		= 'Archived';
Employee.objProperties.Archived.objDefinition.mixEditable	= true;
Employee.objProperties.Archived.objDefinition.mixMandatory	= true;
