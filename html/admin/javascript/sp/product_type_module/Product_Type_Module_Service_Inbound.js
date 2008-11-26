
Sale.ProductTypeModule.Service_Inbound = Class.create();

Object.extend(Sale.ProductTypeModule.Service_Inbound, Sale.ProductTypeModule);
Sale.ProductTypeModule.Service_Inbound.prototype = {};

Object.extend(Sale.ProductTypeModule.Service_Inbound, {
	
	product_type_module: 'Service_Inbound',
	
	unique: 1,
	
	// Nothing to autoload from server - this prevents a wasted request for nothing
	staticData: {}
	
});

Object.extend(Sale.ProductTypeModule.Service_Inbound.prototype, Sale.ProductTypeModule.prototype);

Object.extend(Sale.ProductTypeModule.Service_Inbound.prototype, {

	_getBlankDetailsObject: function()
	{
		return {
			id: null,
			fnn: null,
			answer_point: null,
			has_complex_configuration: null,
			configuration: null
		};
	},
	
	updateSummary: function(suggestion)
	{
		this.summaryContainer.appendChild(document.createTextNode(suggestion + "; Inbound Phone Number: " + ((this.object.fnn == undefined || this.object.fnn == null || this.object.fnn == '') ? '[Not set]' : this.object.fnn)));
	},

	buildGUI: function()
	{
		var id = 'service-inbound-table-' + (Sale.ProductTypeModule.Service_Inbound.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'"></table>';

		var table = $ID(id);

		this.elementGroups.fnn = Sale.GUIComponent.createTextInputGroup(this.getFNN(), true, window._validate.fnnInbound.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Inbound Phone Number', this.elementGroups.fnn);

		this.elementGroups.answer_point = Sale.GUIComponent.createTextInputGroup(this.getAnswerPoint(), false);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Answer Point', this.elementGroups.answer_point);

		this.elementGroups.has_complex_configuration = Sale.GUIComponent.createCheckboxGroup(this.getHasComplexConfiguration(), false);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Has Complex Configuration', this.elementGroups.has_complex_configuration);

		this.elementGroups.configuration = Sale.GUIComponent.createTextInputGroup(this.getConfiguration(), false);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Configuration', this.elementGroups.configuration);
	},
	
	isValid: function()
	{
		var bolValid = true;
		
		bolValid	= (this.elementGroups.fnn.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.fnn);
		this.object.fnn = value;

		bolValid	= (this.elementGroups.answer_point.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.answer_point);
		this.object.answer_point = value;

		bolValid	= (this.elementGroups.has_complex_configuration.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.has_complex_configuration);
		this.object.has_complex_configuration = value;

		bolValid	= (this.elementGroups.configuration.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.configuration);
		this.object.configuration = value;

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
	
	
	
	
	
	

	setFNN: function(value)
	{
		this.object.fnn = value;
	},
	
	getFNN: function()
	{
		return this.object.fnn;
	},
	
	setAnswerPoint: function(value)
	{
		this.object.answer_point = value;
	},
	
	getAnswerPoint: function()
	{
		return this.object.answer_point;
	},
	
	setHasComplexConfiguration: function(value)
	{
		this.object.has_complex_configuration = value;
	},
	
	getHasComplexConfiguration: function()
	{
		return this.object.has_complex_configuration;
	},
	
	setConfiguration: function(value)
	{
		this.object.configuration = value;
	},
	
	getConfiguration: function()
	{
		return this.object.configuration;
	}

});


// Load the static data required by this module
Sale.ProductTypeModule.Service_Inbound.autoloadAndRegister();
