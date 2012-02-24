var Class = require('fw/class');
var TextInputGroup = require('sp/guicomponent/textinputgroup'),
	DropDown = require('sp/guicomponent/dropdown'),
	CreditCardExpiryGroup = require('sp/guicomponent/creditcardexpirygroup');
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
				credit_card_type_id: null,
				card_name: null,
				card_number: null,
				expiry_month: null,
				expiry_year: null,
				cvv: null
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
			var bolDirectDebitType = (Sale.getInstance().getSaleAccount().elementGroups.direct_debit_type_id.getValue() == DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD);
			return (bolBillPaymentMethod && bolDirectDebitType);
		};
		this.addElementGroup('credit_card_type_id', new DropDown(Sale.credit_card_type.ids, Sale.credit_card_type.labels, this.getCreditCardTypeId(), fncIsMandatoryFunction.bind(this)), 'Card Type');
		this.addElementGroup('card_name', new TextInputGroup(this.getCardName(), fncIsMandatoryFunction.bind(this)), 'Card Holder Name');
		
		var fncCreditCardValidation = function (strNumber) {
			var intCreditCardType = (Sale.getInstance().getSaleAccount().directDebitDetails.elementGroups.credit_card_type_id.getValue());
			return Validation.creditCardNumber(strNumber, intCreditCardType);
		};
		this.addElementGroup('card_number', new TextInputGroup(this.getCardNumber(), fncIsMandatoryFunction.bind(this), fncCreditCardValidation.bind(this)), 'Card Number');
		
		var fncCCVValidation = function(strCVV) {
			var intCreditCardType = (Sale.getInstance().getSaleAccount().directDebitDetails.elementGroups.credit_card_type_id.getValue());
			return Validation.creditCardCVV(strCVV, intCreditCardType);
		};
		this.addElementGroup('cvv', new TextInputGroup(this.getCVV(), fncIsMandatoryFunction.bind(this), fncCCVValidation.bind(this)), 'CVV');
		
		this.addElementGroup('expiry_date', new CreditCardExpiryGroup(this.getExpiryDate(), fncIsMandatoryFunction.bind(this), Validation.creditCardExpiry.bind(this)),'Expiry Date',['expiry_month', 'expiry_year']);
		
		// onchange Events
		var fncChangeCreditCardType = function () {
			this.elementGroups.card_number.isValid();
			this.elementGroups.cvv.isValid();
		};
		Event.observe(this.elementGroups.credit_card_type_id.aInputs[0], 'change', fncChangeCreditCardType.bind(this));

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

	showValidationTip : function () {
		return false;
	},

	getExpiryDate : function () {
		return [this.getExpiryMonth(), this.getExpiryYear()];
	},

	setExpiryDate : function ($values) {
		this.setExpiryMonth($values[0]);
		this.setExpiryYear($values[1]);
	},

	setCreditCardTypeId : function (value) {
		this.object.credit_card_type_id = value;
	},

	getCreditCardTypeId : function () {
		return this.object.credit_card_type_id;
	},

	setCardName : function (value) {
		this.object.card_name = value;
	},

	getCardName : function () {
		return this.object.card_name;
	},

	setCardNumber : function (value) {
		this.object.card_number = value;
	},

	getCardNumber : function () {
		return this.object.card_number;
	},

	setExpiryMonth : function (value) {
		this.object.expiry_month = value;
	},

	getExpiryMonth : function () {
		return this.object.expiry_month;
	},

	setExpiryYear : function (value) {
		this.object.expiry_year = value;
	},

	getExpiryYear : function () {
		return this.object.expiry_year;
	},

	setCVV : function (value) {
		this.object.cvv = value;
	},

	getCVV : function () {
		return this.object.cvv;
	}
});

return self;