
Sale.ProductTypeModule.Service_Mobile = Class.create();

Object.extend(Sale.ProductTypeModule.Service_Mobile, Sale.ProductTypeModule);
Sale.ProductTypeModule.Service_Mobile.prototype = {};

Object.extend(Sale.ProductTypeModule.Service_Mobile, {
	
	product_type_module: 'Service_Mobile',
	
	unique: 1//,

	//staticData: {} // Can use states from Sale.state which is populated by default
	
});

Object.extend(Sale.ProductTypeModule.Service_Mobile.prototype, Sale.ProductTypeModule.prototype);

Object.extend(Sale.ProductTypeModule.Service_Mobile.prototype, {

	_getBlankDetailsObject: function()
	{
		return {
			id: null,
			fnn: null,
			sim_puk: null,
			sim_state_id: null,
			dob: null,
			current_provider: null,
			current_account_number: null,
			service_mobile_origin_id: null,
			comments: null
		};
	},
	
	buildGUI: function()
	{
		var id = 'service-mobile-table-' + (Sale.ProductTypeModule.Service_Mobile.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'"></table>';

		var table = $ID(id);

		this.elementGroups.service_mobile_origin_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Mobile.staticData.serviceMobileOrigin.id, 
			Sale.ProductTypeModule.Service_Mobile.staticData.serviceMobileOrigin.description, 
			this.getServiceMobileOriginId()
		);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Origin', this.elementGroups.service_mobile_origin_id);

		this.elementGroups.fnn = Sale.GUIComponent.createTextInputGroup(this.getFNN());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Mobile Phone Number', this.elementGroups.fnn);

		this.elementGroups.sim_puk = Sale.GUIComponent.createTextInputGroup(this.getSimPUK());
		Sale.GUIComponent.appendElementGroupToTable(table, 'SIM PUK', this.elementGroups.sim_puk);

		this.elementGroups.sim_state_id = Sale.GUIComponent.createDropDown(Sale.states.ids, Sale.states.labels, this.getSimStateId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'SIM State', this.elementGroups.sim_state_id);

		this.elementGroups.dob = Sale.GUIComponent.createDateGroup(this.getDOB());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Date of Birth', this.elementGroups.dob);

		this.elementGroups.current_provider = Sale.GUIComponent.createTextInputGroup(this.getCurrentProvider());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Current Provider', this.elementGroups.current_provider);

		this.elementGroups.current_account_number = Sale.GUIComponent.createTextInputGroup(this.getExistingAccountNumber());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Existing Account Number', this.elementGroups.current_account_number);

		this.elementGroups.comments = Sale.GUIComponent.createTextInputGroup(this.getComments());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Comments', this.elementGroups.comments);
	},
	
	isValid: function()
	{
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_mobile_origin_id);
		this.object.service_mobile_origin_id = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.fnn);
		this.object.fnn = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.sim_puk);
		this.object.sim_puk = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.sim_state_id);
		this.object.sim_state_id = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.dob);
		this.object.dob = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_provider);
		this.object.current_provider = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_account_number);
		this.object.current_account_number = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.comments);
		this.object.comments = value;

		return true;
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
	
	

	
	setServiceMobileOriginId: function(value)
	{
		this.object.service_mobile_origin_id = value;
	},
	
	getServiceMobileOriginId: function()
	{
		return this.object.service_mobile_origin_id;
	},
	
	setFNN: function(value)
	{
		this.object.fnn = value;
	},
	
	getFNN: function()
	{
		return this.object.fnn;
	},
	
	setSimPUK: function(value)
	{
		this.object.sim_puk = value;
	},
	
	getSimPUK: function()
	{
		return this.object.sim_puk;
	},
	
	setExistingAccountNumber: function(value)
	{
		this.object.current_account_number = value;
	},
	
	getExistingAccountNumber: function()
	{
		return this.object.current_account_number;
	},
	
	setCurrentProvider: function(value)
	{
		this.object.current_provider = value;
	},
	
	getCurrentProvider: function()
	{
		return this.object.current_provider;
	},
	
	setSimStateId: function(value)
	{
		this.object.sim_state_id = value;
	},
	
	getSimStateId: function()
	{
		return this.object.sim_state_id;
	},
	
	setDOB: function(value)
	{
		this.object.dob = value;
	},
	
	getDOB: function()
	{
		return this.object.dob;
	},
	
	setComments: function(value)
	{
		this.object.comments = value;
	},
	
	getComments: function()
	{
		return this.object.comments;
	}

});


// Load the static data required by this module
Sale.ProductTypeModule.Service_Mobile.autoloadAndRegister();
