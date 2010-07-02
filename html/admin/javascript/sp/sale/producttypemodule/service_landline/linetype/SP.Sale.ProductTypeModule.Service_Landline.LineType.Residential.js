FW.Package.create('SP.Sale.ProductTypeModule.Service_Landline.LineType.Residential', {
	requires: ['FW.GUIComponent','FW.GUIComponent.DateGroup'],
	extends: 'SP.Sale.ProductTypeModule',
	_getBlankDetailsObject: function()
	{
		var sale = SP.Sale.getInstance();
		//sale.getSaleAccount().updateFromGUI();
		var contact = sale.getContacts()[0];
		//contact.updateFromGUI();

		var titleText = contact.elementGroups.contact_title_id.oDisplay.textContent;
		var titleId = null;
		for (var i = 0, l = SP.Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description.length; i < l; i++)
		{
			if (titleText == SP.Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description[i])
			{
				titleId = SP.Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.id[i];
				break;
			}
		}

		return {
			id: null,
			landline_end_user_title_id: titleId,
			end_user_given_name: contact.getFirstName(),
			end_user_family_name: contact.getLastName(),
			end_user_dob: contact.getDateOfBirth(),
			end_user_employer: sale.getSaleAccount().getBusinessName(),
			end_user_occupation: contact.getPositionTitle()
		};
	},

	buildGUI: function()
	{
		this.setWorkingTable(this.detailsContainer);

		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc =	function(intMaxLength)
											{
												return window._validate.getStringLengthValidationFunc(intMaxLength);
											}

		var lengths = SP.Sale.ProductTypeModule.Service_Landline.staticData.lengths.landlineResidential;

		this.addElementGroup('landline_end_user_title_id', new FW.GUIComponent.DropDown(
			SP.Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.id,
			SP.Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description,
			this.getLandlineEndUserTitleId(),
			true),'Title');
		this.addElementGroup('end_user_given_name', new FW.GUIComponent.TextInputGroup(this.getEndUserGivenName(), true, fncGetStringValidationFunc(lengths.endUserGivenName)),'Given Name');
		this.addElementGroup('end_user_family_name', new FW.GUIComponent.TextInputGroup(this.getEndUserFamilyName(), true, fncGetStringValidationFunc(lengths.endUserFamilyName)),'Family Name');
		this.addElementGroup('end_user_dob', new FW.GUIComponent.DateGroup(this.getEndUserDOB(), true, window._validate.date.bind(this)),'Date Of Birth');
		this.addElementGroup('end_user_employer', new FW.GUIComponent.TextInputGroup(this.getEndUserEmployer(), false, fncGetStringValidationFunc(lengths.endUserEmployer)),'Employer');
		this.addElementGroup('end_user_occupation', new FW.GUIComponent.TextInputGroup(this.getEndUserOccupation(), false, fncGetStringValidationFunc(lengths.endUserOccupation)),'Occupation');
		
	},

	showValidationTip: function()
	{
		return false;
	},

	renderDetails: function(readOnly)
	{

	},

	renderSummary: function(readOnly)
	{

	},

	setLandlineEndUserTitleId: function(value)
	{
		this.object.landline_end_user_title_id = value;
	},

	getLandlineEndUserTitleId: function()
	{
		return this.object.landline_end_user_title_id;
	},

	setEndUserGivenName: function(value)
	{
		this.object.end_user_given_name = value;
	},

	getEndUserGivenName: function()
	{
		return this.object.end_user_given_name;
	},

	setEndUserFamilyName: function(value)
	{
		this.object.end_user_family_name = value;
	},

	getEndUserFamilyName: function()
	{
		return this.object.end_user_family_name;
	},

	setEndUserDOB: function(value)
	{
		this.object.end_user_dob = value;
	},

	getEndUserDOB: function()
	{
		return this.object.end_user_dob;
	},

	setEndUserEmployer: function(value)
	{
		this.object.end_user_employer = value;
	},

	getEndUserEmployer: function()
	{
		return this.object.end_user_employer;
	},

	setEndUserOccupation: function(value)
	{
		this.object.end_user_occupation = value;
	},

	getEndUserOccupation: function()
	{
		return this.object.end_user_occupation;
	}

});