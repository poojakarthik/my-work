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
	},
	
	view	: function()
	{
		
	},
	
	edit	: function()
	{
		
	},
	
	_load	: function(fncCallback, objResponse)
	{
		// Set properties
		this.objProperties	= objResponse.objEmployee;
		
		alert(Object.inspect(objReponse));
		
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
Employee.objProperties		= {};

// Id
Employee.objProperties.Id					= {};
Employee.objProperties.Id.strLabel			= 'Id';
Employee.objProperties.Id.mixHidden			= true;

Employee.objProperties.Id.input				= {};
Employee.objProperties.Id.input.type		= 'hidden';

// First Name
Employee.objProperties.FirstName					= {};
Employee.objProperties.FirstName.strLabel			= 'First Name';
Employee.objProperties.FirstName.mixEditable		= true;

Employee.objProperties.FirstName.input				= {};
Employee.objProperties.FirstName.input.type			= 'text';
Employee.objProperties.FirstName.input.fncValidate	= Reflex_Validation.stringNotEmptyTrimmed;
Employee.objProperties.FirstName.input.mixTrim		= true;
Employee.objProperties.FirstName.input.mixMandatory	= true;

// Last Name
Employee.objProperties.LastName						= {};
Employee.objProperties.LastName.strLabel			= 'Last Name';

Employee.objProperties.LastName.input				= {};
Employee.objProperties.LastName.input.type			= 'text';
Employee.objProperties.LastName.input.fncValidate	= Reflex_Validation.stringNotEmpty;
Employee.objProperties.LastName.input.mixTrim		= true;
Employee.objProperties.LastName.input.mixMandatory	= true;

Employee.objProperties.LastName.mixEditable			= true;

// Date of Birth
Employee.objProperties.DOB						= {};
Employee.objProperties.DOB.strLabel				= 'Date of Birth';

Employee.objProperties.DOB.input				= {};
//Employee.objProperties.DOB.input.type			= Control_Date;
Employee.objProperties.DOB.input.type			= 'text';
Employee.objProperties.DOB.input.fncValidate	= Reflex_Validation.date;
Employee.objProperties.DOB.input.mixMandatory	= true;

Employee.objProperties.DOB.mixEditable			= true;

// Email
Employee.objProperties.Email					= {};
Employee.objProperties.Email.strLabel			= 'Email';

Employee.objProperties.Email.input				= {};
Employee.objProperties.Email.input.type			= 'text';
Employee.objProperties.Email.input.fncValidate	= Reflex_Validation.email;
Employee.objProperties.Email.input.mixTrim		= true;
Employee.objProperties.Email.input.mixMandatory	= true;

Employee.objProperties.Email.mixEditable		= true;

// Extension
Employee.objProperties.Extension					= {};
Employee.objProperties.Extension.strLabel			= 'Extension';

Employee.objProperties.Extension.input				= {};
Employee.objProperties.Extension.input.type			= 'text';
Employee.objProperties.Extension.input.mixTrim		= true;
Employee.objProperties.Extension.input.mixMandatory	= false;

Employee.objProperties.Extension.mixEditable		= true;

// Phone
Employee.objProperties.Phone					= {};
Employee.objProperties.Phone.strLabel			= 'Phone';

Employee.objProperties.Phone.input				= {};
Employee.objProperties.Phone.input.type			= 'text';
Employee.objProperties.Phone.input.fncValidate	= Reflex_Validation.fnn;
Employee.objProperties.Phone.input.mixTrim		= function(strPhone){return strPhone.replace(/.+/, '');};
Employee.objProperties.Phone.input.mixMandatory	= false;

Employee.objProperties.Phone.mixEditable		= true;

// Mobile
Employee.objProperties.Mobile						= {};
Employee.objProperties.Mobile.strLabel				= 'Mobile';

Employee.objProperties.Mobile.input					= {};
Employee.objProperties.Mobile.input.type			= 'text';
Employee.objProperties.Mobile.input.fncValidate		= Reflex_Validation.stringNotEmpty;
Employee.objProperties.Mobile.input.mixTrim			= function(strPhone){return strPhone.replace(/.+/, '');};
Employee.objProperties.Mobile.input.mixMandatory	= false;

Employee.objProperties.Mobile.mixEditable			= true;

// Password
Employee.objProperties.PassWord						= {};
Employee.objProperties.PassWord.strLabel			= 'Password';
Employee.objProperties.PassWord.mixEditable			= true;

Employee.objProperties.PassWord.input				= {};
Employee.objProperties.PassWord.input.type			= 'password';
Employee.objProperties.PassWord.input.fncValidate	= Reflex_Validation.stringNotEmpty;
Employee.objProperties.PassWord.input.mixTrim		= function(strPhone){return strPhone.replace(/.+/, '');};
Employee.objProperties.PassWord.input.mixMandatory	= function(objEmployee){return (objEmployee.objProperties.Id.value)};

// Role
Employee.objProperties.user_role_id						= {};
Employee.objProperties.user_role_id.strLabel			= 'Role';
Employee.objProperties.user_role_id.mixEditable			= true;

Employee.objProperties.user_role_id.input				= {};
Employee.objProperties.user_role_id.input.type			= 'select';
//Employee.objProperties.user_role_id.input.fncPopulate	= User_Role.getAll;
Employee.objProperties.user_role_id.input.fncValidate	= Reflex_Validation.stringNotEmpty;
Employee.objProperties.user_role_id.input.mixTrim		= function(strPhone){return strPhone.replace(/.+/, '');};
Employee.objProperties.user_role_id.input.mixMandatory	= false;

// is_god
Employee.objProperties.is_god						= {};
Employee.objProperties.is_god.strLabel				= 'Archived';
Employee.objProperties.is_god.mixEditable			= false;

// Archived
Employee.objProperties.Archived						= {};
Employee.objProperties.Archived.strLabel			= 'Archived';
Employee.objProperties.Archived.mixEditable			= function(objEmployee){return (objEmployee.objProperties.Id.value)};
Employee.objProperties.Archived.mixHidden			= function(objEmployee){return !(objEmployee.objProperties.Id.value)};

Employee.objProperties.Archived.input				= {};
Employee.objProperties.Archived.input.type			= 'checkbox';
Employee.objProperties.Archived.input.fncValidate	= Reflex_Validation.stringNotEmpty;
Employee.objProperties.Archived.input.mixTrim		= function(strPhone){return strPhone.replace(/.+/, '');};
Employee.objProperties.Archived.input.mixMandatory	= false;