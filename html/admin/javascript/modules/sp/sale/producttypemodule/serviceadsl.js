var Class = require('fw/class');
var TextInputGroup = require('sp/guicomponent/textinputgroup'),
	DropDown = require('sp/guicomponent/dropdown');
var Sale = require('sp/sale'),
	ProductTypeModule = require('../producttypemodule'),
	Validation = require('sp/validation');

var self = new Class({
	extends : ProductTypeModule,

	_getBlankDetailsObject : function () {
		var saleAccount = Sale.getInstance().getSaleAccount();
		//saleAccount.updateFromGUI();

		return {
			id: null,
			fnn: null,
			address_line_1: saleAccount.getAddressLine1(),
			address_line_2: saleAccount.getAddressLine2(),
			suburb: saleAccount.getSuburb(),
			postcode: saleAccount.getPostcode(),
			state_id: saleAccount.getStateId()
		};
	},

	updateSummary : function (suggestion) {
		this.summaryContainer.appendChild(document.createTextNode(suggestion + "; DSL Phone Number: " + ((this.object.fnn == undefined || this.object.fnn == null || this.object.fnn == '') ? '[Not set]' : this.object.fnn)));
	},

	buildGUI : function () {
		var id = 'service-adsl-table-' + (self.unique++);
		this.detailsContainer.innerHTML = '<table id="'+id+'"></table>';

		this.setWorkingTable($ID(id));
		this.addElementGroup('fnn', new TextInputGroup(this.getFNN(), true, Validation.fnnADSL.bind(this)),'DSL Phone Number');
		this.addElementGroup('address_line_1', new TextInputGroup(this.getAddressLine1(), true),'Address (Line 1)');
		this.addElementGroup('address_line_2', new TextInputGroup(this.getAddressLine2(), false),'Address (Line 2)');
		this.addElementGroup('suburb', new TextInputGroup(this.getSuburb(), false),'Suburb');
		this.addElementGroup('postcode', new TextInputGroup(this.getPostcode(), true, Validation.postcode.bind(this)),'Postcode');
		this.addElementGroup('state_id', new DropDown(Sale.states.ids, Sale.states.labels, this.getStateId(), true),'State');
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

	setAddressLine1 : function (value) {
		this.object.address_line_1 = value;
	},

	getAddressLine1 : function () {
		return this.object.address_line_1;
	},

	setAddressLine2 : function (value) {
		this.object.address_line_2 = value;
	},

	getAddressLine2 : function () {
		return this.object.address_line_2;
	},

	setSuburb : function (value) {
		this.object.suburb = value;
	},

	getSuburb : function () {
		return this.object.suburb;
	},

	setPostcode : function (value) {
		this.object.postcode = value;
	},

	getPostcode : function () {
		return this.object.postcode;
	},

	setStateId : function (value) {
		this.object.state_id = value;
	},

	getStateId : function () {
		return this.object.state_id;
	},

	statics : Object.extend(Object.extend({}, ProductTypeModule.STATIC_INHERITANCE), {
		product_type_module: 'Service_ADSL',

		unique: 1,

		staticData: {} // Can use states from Sale.state which is populated by default
	})
});

self.autoloadAndRegister();

return self;