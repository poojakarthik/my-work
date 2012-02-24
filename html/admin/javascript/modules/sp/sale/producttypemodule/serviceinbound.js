var Class = require('fw/class');
var TextInputGroup = require('sp/guicomponent/textinputgroup'),
	CheckboxGroup = require('sp/guicomponent/checkboxgroup');
var ProductTypeModule = require('../producttypemodule'),
	Validation = require('sp/validation');

var self = new Class({
	extends : ProductTypeModule,

	_getBlankDetailsObject : function () {
		return {
			id: null,
			fnn: null,
			answer_point: null,
			has_complex_configuration: null,
			configuration: null
		};
	},

	updateSummary : function (suggestion) {
		this.summaryContainer.appendChild(document.createTextNode(suggestion + "; Inbound Phone Number: " + ((this.object.fnn == undefined || this.object.fnn == null || this.object.fnn == '') ? '[Not set]' : this.object.fnn)));
	},

	buildGUI : function () {
		var id = 'service-inbound-table-' + (self.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'"></table>';
		this.setWorkingTable($ID(id));
		this.addElementGroup('fnn', new TextInputGroup(this.getFNN(), true, Validation.fnnInbound.bind(this)), 'Inbound Phone Number');
		this.addElementGroup('answer_point', new TextInputGroup(this.getAnswerPoint(), false, Validation.fnnInboundAnswerPoint.bind(this)), 'Answer Point' );
		this.addElementGroup('has_complex_configuration',new CheckboxGroup(this.getHasComplexConfiguration(), false), 'Has Complex Configuration');
		this.addElementGroup('configuration', new TextInputGroup(this.getConfiguration(), false),'Configuration');
		
	},

	showValidationTip : function () {
		return false;
	},

	renderDetails : function (readOnly) {

	},

	renderSummary : function (readOnly) {

	},

	setFNN : function (value) {
		this.object.fnn = value;
	},

	getFNN : function () {
		return this.object.fnn;
	},

	setAnswerPoint : function (value) {
		this.object.answer_point = value;
	},

	getAnswerPoint : function () {
		return this.object.answer_point;
	},

	setHasComplexConfiguration : function (value) {
		this.object.has_complex_configuration = value;
	},

	getHasComplexConfiguration : function () {
		return this.object.has_complex_configuration;
	},

	setConfiguration : function (value) {
		this.object.configuration = value;
	},

	getConfiguration : function () {
		return this.object.configuration;
	},

	statics : Object.extend(Object.extend({}, ProductTypeModule.STATIC_INHERITANCE), {
		product_type_module: 'Service_Inbound',

		unique: 1,

		// Nothing to autoload from server - this prevents a wasted request for nothing
		staticData: {}
	})
});

// Load the static data required by this module
self.autoloadAndRegister();

return self;