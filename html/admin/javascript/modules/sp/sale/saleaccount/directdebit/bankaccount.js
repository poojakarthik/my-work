var Class = require('fw/class');
var TextInputGroup = require('sp/guicomponent/textinputgroup');
var Sale = require('../../../sale'),
	SaleType = require('../../saletype'),
	BillPaymentType = require('../../billpaymenttype'),
	DirectDebitType = require('../../directdebittype'),
	Validation = require('sp/validation');

var self = new Class({
	extends : require('sp/guicomponent'),

	construct : function (obj) {
		if (obj == null) {
			this.object = {
				id: null,
				bank_name: null,
				bank_bsb: null,
				account_number: null,
				account_name: null
			};
		} else {
			this.object = obj;
		}

		this.elementGroups = {};
	},

	buildGUI : function () {
		this.setWorkingTable(this.detailsContainer);

		var fncIsMandatoryFunction = function () {
			var bolBillPaymentMethod = (Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id.getValue() == BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);
			var bolDirectDebitType = (Sale.getInstance().getSaleAccount().elementGroups.direct_debit_type_id.getValue() == DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT);
			return (bolBillPaymentMethod && bolDirectDebitType);
		};
		this.addElementGroup('bank_name', new TextInputGroup(this.getBankName(), fncIsMandatoryFunction.bind(this)),'Bank Name');
		this.addElementGroup('bank_bsb', new TextInputGroup(this.getBankBSB(), fncIsMandatoryFunction.bind(this), Validation.bsb.bind(this)),'BSB');
		this.addElementGroup('account_number', new TextInputGroup(this.getAccountNumber(), fncIsMandatoryFunction.bind(this), Validation.integerPositive.bind(this)),'Account Number');
		this.addElementGroup('account_name', new TextInputGroup(this.getAccountName(), fncIsMandatoryFunction.bind(this)),'Account Name');
		
		// Disable the inputs if the Sale is to an existing customer
		switch (Sale.getInstance().getSaleTypeId()) {
			case SaleType.SALE_TYPE_EXISTING:
			case SaleType.SALE_TYPE_WINBACK:
				for (var sElementGroup in this.elementGroups) {
					this.elementGroups[sElementGroup].disable();
				}
				break;
		}
	},

	// updateFromGUI: function()
	// {
		// if (this.isValid())
		// {
			// // Validate all the fields ...
			// var value;
			// this.object.bank_name = this.elementGroups.bank_name.getValue();
			// this.object.bank_bsb = this.elementGroups.bank_bsb.getValue();
			// this.object.account_number = this.elementGroups.account_number.getValue();
			// this.object.account_name = this.elementGroups.account_name.getValue();
		// }
		// else
		// {
			// return false;
		// }

		// return true;
	// },

	showValidationTip : function () {
		return false;
	},

	setBankName : function (value) {
		this.object.bank_name = value;
	},

	getBankName : function () {
		return this.object.bank_name;
	},

	setBankBSB : function (value) {
		this.object.bank_bsb = value;
	},

	getBankBSB : function () {
		return this.object.bank_bsb;
	},

	setAccountNumber : function (value) {
		this.object.account_number = value;
	},

	getAccountNumber : function () {
		return this.object.account_number;
	},

	setAccountName : function (value) {
		this.object.account_name = value;
	},

	getAccountName : function () {
		return this.object.account_name;
	}
});

return self;