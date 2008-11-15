
Sale.ProductTypeModule.Service_ADSL = Class.create();

Object.extend(Sale.ProductTypeModule.Service_ADSL, Sale.ProductTypeModule);
Sale.ProductTypeModule.Service_ADSL.prototype = {};

Object.extend(Sale.ProductTypeModule.Service_ADSL, {
	
	product_type_module: 'Service_ADSL',
	
	unique: 1,
	
	staticData: {} // Can use states from Sale.state which is populated by default

});


Object.extend(Sale.ProductTypeModule.Service_ADSL.prototype, Sale.ProductTypeModule.prototype);

Object.extend(Sale.ProductTypeModule.Service_ADSL.prototype, {

	_getBlankDetailsObject: function()
	{
		return {
			id: null,
			fnn: null,
			address_line_1: null,
			address_line_2: null,
			suburb: null,
			postcode: null,
			state_id: null
		};
	},
	
	buildGUI: function()
	{
		var id = 'service-adsl-table-' + (Sale.ProductTypeModule.Service_ADSL.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'"></table>';

		var table = $ID(id);

		this.elementGroups.fnn = Sale.GUIComponent.createTextInputGroup(this.getFNN());
		Sale.GUIComponent.appendElementGroupToTable(table, 'DSL Phone Number', this.elementGroups.fnn);

		this.elementGroups.address_line_1 = Sale.GUIComponent.createTextInputGroup(this.getAddressLine1());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 1)', this.elementGroups.address_line_1);

		this.elementGroups.address_line_2 = Sale.GUIComponent.createTextInputGroup(this.getAddressLine2());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 2)', this.elementGroups.address_line_2);

		this.elementGroups.suburb = Sale.GUIComponent.createTextInputGroup(this.getSuburb());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Suburb', this.elementGroups.suburb);

		this.elementGroups.postcode = Sale.GUIComponent.createTextInputGroup(this.getPostcode());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.postcode);

		this.elementGroups.state_id = Sale.GUIComponent.createDropDown(Sale.states.ids, Sale.states.labels, this.getStateId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'State', this.elementGroups.state_id);
	},
	
	isValid: function()
	{
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.fnn);
		this.object.fnn = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.address_line_1);
		this.object.address_line_1 = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.address_line_2);
		this.object.address_line_2 = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.suburb);
		this.object.suburb = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.postcode);
		this.object.postcode = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.state_id);
		this.object.state_id = value;

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
	
	
	
	
	
	
	
	setFNN: function(value)
	{
		this.object.fnn = value;
	},
	
	getFNN: function()
	{
		return this.object.fnn;
	},
	
	setAddressLine1: function(value)
	{
		this.object.address_line_1 = value;
	},
	
	getAddressLine1: function()
	{
		return this.object.address_line_1;
	},
	
	setAddressLine2: function(value)
	{
		this.object.address_line_2 = value;
	},
	
	getAddressLine2: function()
	{
		return this.object.address_line_2;
	},
	
	setSuburb: function(value)
	{
		this.object.suburb = value;
	},
	
	getSuburb: function()
	{
		return this.object.suburb;
	},
	
	setPostcode: function(value)
	{
		this.object.postcode = value;
	},
	
	getPostcode: function()
	{
		return this.object.postcode;
	},
	
	setStateId: function(value)
	{
		this.object.state_id = value;
	},
	
	getStateId: function()
	{
		return this.object.state_id;
	}

});


// Load the static data required by this module
Sale.ProductTypeModule.Service_ADSL.autoloadAndRegister();

