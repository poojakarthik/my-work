var Class = require('fw/class');
var TextInputGroup = require('sp/guicomponent/textinputgroup');
var Sale = require('sp/sale'),
	ServiceLandline = require('../../servicelandline'),
	Validation = require('sp/validation');

var self = new Class({
	extends : require('../../../producttypemodule'),

	_getBlankDetailsObject : function () {
		var saleAccount = Sale.getInstance().getSaleAccount();
		//saleAccount.updateFromGUI();

		return {
			id: null,
			company_name: saleAccount.getBusinessName(),
			abn: saleAccount.getABN(),
			trading_name: saleAccount.getTradingName()
		};
	},

	buildGUI : function () {
		this.setWorkingTable(this.detailsContainer);

		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc = function (intMaxLength) {
			return Validation.getStringLengthValidationFunc(intMaxLength);
		};

		// TODO: Ensure this still works!
		var lengths = ServiceLandline.staticData.lengths.landlineBusiness;
		this.addElementGroup('company_name', new TextInputGroup(this.getCompanyName(), true, fncGetStringValidationFunc(lengths.companyName)),'Company Name');
		this.addElementGroup('abn', new TextInputGroup(this.getABN(), true, Validation.australianBusinessNumber.bind(this)),'ABN');
		this.addElementGroup('trading_name', new TextInputGroup(this.getTradingName(), false, fncGetStringValidationFunc(lengths.tradingName)),'Trading Name');
	},

	showValidationTip : function () {
		return false;
	},

	renderDetails : function (readOnly) {

	},

	renderSummary : function (readOnly) {

	},

	setCompanyName : function (value) {
		this.object.company_name = value;
	},

	getCompanyName : function () {
		return this.object.company_name;
	},

	setABN : function (value) {
		this.object.abn = value;
	},

	getABN : function () {
		return this.object.abn;
	},

	setTradingName : function (value) {
		this.object.trading_name = value;
	},

	getTradingName : function () {
		return this.object.trading_name;
	}
});

return self;