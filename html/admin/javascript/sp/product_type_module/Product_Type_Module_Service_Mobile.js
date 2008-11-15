
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
			this.getServiceMobileOriginId(),
			true
		);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Origin', this.elementGroups.service_mobile_origin_id);
		
		this.elementGroups.fnn = Sale.GUIComponent.createTextInputGroup(this.getFNN(), this.isExistingService.bind(this), window._validate.fnnMobile.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Mobile Phone Number', this.elementGroups.fnn);

		this.elementGroups.sim_puk = Sale.GUIComponent.createTextInputGroup(this.getSimPUK());
		Sale.GUIComponent.appendElementGroupToTable(table, 'SIM PUK', this.elementGroups.sim_puk);

		this.elementGroups.sim_state_id = Sale.GUIComponent.createDropDown(Sale.states.ids, Sale.states.labels, this.getSimStateId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'SIM State', this.elementGroups.sim_state_id);

		this.elementGroups.dob = Sale.GUIComponent.createDateGroup(this.getDOB(), this.isExistingPrePaid(), window._validate.date.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Date of Birth', this.elementGroups.dob);
		
		fncMandatoryCurrentProvider	= function()
										{
											//alert(this.isExistingPostPaid() + " || (" + this.isExistingPrePaid() + " && " + Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_account_number) + ")");
											return (this.isExistingPostPaid() || (this.isExistingPrePaid() && Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_account_number)))
										};
		this.elementGroups.current_provider = Sale.GUIComponent.createTextInputGroup(this.getCurrentProvider(), fncMandatoryCurrentProvider.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Current Provider', this.elementGroups.current_provider);

		fncMandatoryCurrentAccount	= function()
										{
											//alert(this.isExistingPostPaid() + " || (" + this.isExistingPrePaid() + " && " + Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_provider) + ")");
											return (this.isExistingPostPaid() || (this.isExistingPrePaid() && Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_provider)))
										};
		this.elementGroups.current_account_number = Sale.GUIComponent.createTextInputGroup(this.getExistingAccountNumber(), fncMandatoryCurrentAccount.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Existing Account Number', this.elementGroups.current_account_number);

		this.elementGroups.comments = Sale.GUIComponent.createTextInputGroup(this.getComments());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Comments', this.elementGroups.comments);

		Event.observe(this.elementGroups.service_mobile_origin_id.inputs[0], 'change', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_provider.inputs[0], 'change', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_provider.inputs[0], 'keyup', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_account_number.inputs[0], 'change', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_account_number.inputs[0], 'keyup', this.isValid.bind(this));
	},
	
	isValid: function()
	{
		bolValid	= true;
		
		bolValid	= (this.elementGroups.service_mobile_origin_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_mobile_origin_id);
		this.object.service_mobile_origin_id = value;

		bolValid	= (this.elementGroups.fnn.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.fnn);
		this.object.fnn = value;

		bolValid	= (this.elementGroups.sim_puk.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.sim_puk);
		this.object.sim_puk = value;

		bolValid	= (this.elementGroups.sim_state_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.sim_state_id);
		this.object.sim_state_id = value;

		bolValid	= (this.elementGroups.dob.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.dob);
		this.object.dob = value;

		bolValid	= (this.elementGroups.current_provider.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_provider);
		this.object.current_provider = value;

		bolValid	= (this.elementGroups.current_account_number.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.current_account_number);
		this.object.current_account_number = value;

		bolValid	= (this.elementGroups.comments.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.comments);
		this.object.comments = value;

		return bolValid;
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
	},
	
	isExistingPrePaid: function()
	{
		intValue	= Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_mobile_origin_id);
		//alert(intValue);
		return (intValue == 2);
	},
	
	isExistingPostPaid: function()
	{
		intValue	= Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_mobile_origin_id);
		//alert(intValue);
		return (intValue == 3);
	},
	
	isExistingService: function()
	{
		intValue	= Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_mobile_origin_id);
		//alert(intValue);
		return (intValue == 2 || intValue == 3);
	},
	
	isNewService: function()
	{
		intValue	= Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_mobile_origin_id);
		//alert(intValue);
		return (intValue == 1);
	}

});


// Load the static data required by this module
Sale.ProductTypeModule.Service_Mobile.autoloadAndRegister();
