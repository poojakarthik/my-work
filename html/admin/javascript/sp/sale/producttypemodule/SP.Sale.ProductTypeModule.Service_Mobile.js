FW.Package.create('SP.Sale.ProductTypeModule.Service_Mobile', {
	requires: ['FW.GUIComponent','FW.GUIComponent.DateGroup'],
	extends: 'SP.Sale.ProductTypeModule',
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


	updateSummary: function(suggestion)
	{
		var noFNN = (this.object.fnn == undefined || this.object.fnn == null || this.object.fnn == '');
		this.summaryContainer.appendChild(document.createTextNode(suggestion + "; " + ((this.isNewService() && noFNN) ? "[New Service - No Mobile Phone Number specified]" : ("Mobile Phone Number: " + (noFNN ? '[Not set]' : this.object.fnn)))));
	},

	buildGUI: function()
	{
		var id = 'service-mobile-table-' + (SP.Sale.ProductTypeModule.Service_Mobile.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'" class="data-table"></table>';

		this.setWorkingTable($ID(id));

		this.addElementGroup('service_mobile_origin_id', new FW.GUIComponent.DropDown(
			SP.Sale.ProductTypeModule.Service_Mobile.staticData.serviceMobileOrigin.id,
			SP.Sale.ProductTypeModule.Service_Mobile.staticData.serviceMobileOrigin.description,
			this.getServiceMobileOriginId(),
			true
		),'Origin');
		

		this.addElementGroup('fnn', new FW.GUIComponent.TextInputGroup(this.getFNN(), this.isExistingService.bind(this), window._validate.fnnMobile.bind(this)),'Mobile Phone Number');		
		this.addElementGroup('sim_puk', new FW.GUIComponent.TextInputGroup(this.getSimPUK(), false, window._validate.getStringLengthValidationFunc(SP.Sale.ProductTypeModule.Service_Mobile.staticData.lengths.simPuk)),'SIM PUK');	
		this.addElementGroup('sim_state_id', new FW.GUIComponent.DropDown(SP.Sale.states.ids, SP.Sale.states.labels, this.getSimStateId()),'SIM State');		
		this.addElementGroup('dob', new FW.GUIComponent.DateGroup(this.getDOB(), this.isExistingPrePaid.bind(this), window._validate.date.bind(this)),'Date of Birth');		
		
		
		this.addElementGroup('current_provider', new FW.GUIComponent.TextInputGroup(this.getCurrentProvider() ),'Current Provider');
		fncMandatoryCurrentProvider	= function()
										{
											//alert(this.isExistingPostPaid() + " || (" + this.isExistingPrePaid() + " && " + Sale.GUIComponent.getValue(this.elementGroups.current_account_number) + ")");
											return (this.isExistingPostPaid() || (this.isExistingPrePaid() && this.elementGroups.current_account_number.getValue()))
										};
		this.elementGroups.current_provider.mIsMandatory = fncMandatoryCurrentProvider.bind(this);								
		
		
		fncMandatoryCurrentAccount	= function()
										{
											//alert(this.isExistingPostPaid() + " || (" + this.isExistingPrePaid() + " && " + Sale.GUIComponent.getValue(this.elementGroups.current_provider) + ")");
											return (this.isExistingPostPaid() || (this.isExistingPrePaid() && this.elementGroups.current_provider.getValue()))
										};
		this.addElementGroup('current_account_number', new FW.GUIComponent.TextInputGroup(this.getExistingAccountNumber(), fncMandatoryCurrentAccount.bind(this)),'Existing Account Number');
		
		this.addElementGroup('comments', new FW.GUIComponent.TextInputGroup(this.getComments()),'Comments');
		
		Event.observe(this.elementGroups.service_mobile_origin_id.aInputs[0], 'change', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_provider.aInputs[0], 'change', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_provider.aInputs[0], 'keyup', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_account_number.aInputs[0], 'change', this.isValid.bind(this));
		Event.observe(this.elementGroups.current_account_number.aInputs[0], 'keyup', this.isValid.bind(this));
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
		intValue	= this.elementGroups.service_mobile_origin_id.getValue();
		//alert(intValue);
		return (intValue == 2);
	},

	isExistingPostPaid: function()
	{
		intValue	= this.elementGroups.service_mobile_origin_id.getValue();
		//alert(intValue);
		return (intValue == 3);
	},

	isExistingService: function()
	{
		intValue	= this.elementGroups.service_mobile_origin_id.getValue();
		//alert(intValue);
		return (intValue == 2 || intValue == 3);
	},

	isNewService: function()
	{
		intValue	= this.elementGroups.service_mobile_origin_id.getValue();
		//alert(intValue);
		return (intValue == 1);
	}
}, false);


FW.Package.extend(SP.Sale.ProductTypeModule.Service_Mobile, SP.Sale.ProductTypeModule);
//SP.Sale.ProductTypeModule.Service_Mobile.prototype = {};

FW.Package.extend(SP.Sale.ProductTypeModule.Service_Mobile, {

	product_type_module: 'Service_Mobile',

	unique: 1//,

	//staticData: {} // Can use states from Sale.state which is populated by default

});
SP.Sale.ProductTypeModule.Service_Mobile.autoloadAndRegister();

FW.Package.setDefined(SP.Sale.ProductTypeModule.Service_Mobile, true);