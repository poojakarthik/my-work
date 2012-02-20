FW.Package.create('SP.Sale.ProductTypeModule.Service_Inbound', {
	requires: ['FW.GUIComponent','FW.GUIComponent.CheckboxGroup','FW.GUIComponent.TextInputGroup'],
	extends: 'SP.Sale.ProductTypeModule',
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
		var id = 'service-inbound-table-' + (SP.Sale.ProductTypeModule.Service_Inbound.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'"></table>';
		this.setWorkingTable($ID(id));
		this.addElementGroup('fnn', new FW.GUIComponent.TextInputGroup(this.getFNN(), true, window._validate.fnnInbound.bind(this)), 'Inbound Phone Number');
		this.addElementGroup('answer_point', new FW.GUIComponent.TextInputGroup(this.getAnswerPoint(), false, window._validate.fnnInboundAnswerPoint.bind(this)), 'Answer Point' );
		this.addElementGroup('has_complex_configuration',new FW.GUIComponent.CheckboxGroup(this.getHasComplexConfiguration(), false), 'Has Complex Configuration');
		this.addElementGroup('configuration', new FW.GUIComponent.TextInputGroup(this.getConfiguration(), false),'Configuration');
		
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
}, false);

FW.Package.extend(SP.Sale.ProductTypeModule.Service_Inbound, SP.Sale.ProductTypeModule);
//SP.Sale.ProductTypeModule.Service_Inbound.prototype = {};

FW.Package.extend(SP.Sale.ProductTypeModule.Service_Inbound, {

	product_type_module: 'Service_Inbound',

	unique: 1,

	// Nothing to autoload from server - this prevents a wasted request for nothing
	staticData: {}

});

// Load the static data required by this module
SP.Sale.ProductTypeModule.Service_Inbound.autoloadAndRegister();

FW.Package.setDefined(SP.Sale.ProductTypeModule.Service_Inbound, true);