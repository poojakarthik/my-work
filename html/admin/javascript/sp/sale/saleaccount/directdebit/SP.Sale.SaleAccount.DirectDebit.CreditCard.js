//2801 - 3001
FW.Package.create('SP.Sale.SaleAccount.DirectDebit.CreditCard', {

	requires: ['FW.GUIComponent.CreditCardExpiryGroup','FW.GUIComponent.DropDown', 'FW.GUIComponent.TextInputGroup'],
	extends: 'FW.GUIComponent',


	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,
				credit_card_type_id: null,
				card_name: null,
				card_number: null,
				expiry_month: null,
				expiry_year: null,
				cvv: null
			};
		}
		else
		{
			this.object = obj;
		}

		this.elementGroups = {};
	},

	buildGUI: function()
	{
		this.setWorkingTable(this.detailsContainer);

		var fncIsMandatoryFunction	= function()
									{
										bolBillPaymentMethod	= (SP.Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id.getValue() == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);
										bolDirectDebitType		= (SP.Sale.getInstance().getSaleAccount().elementGroups.direct_debit_type_id.getValue() == SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD);
										return (bolBillPaymentMethod && bolDirectDebitType);
									};
		this.addElementGroup('credit_card_type_id', new FW.GUIComponent.DropDown(SP.Sale.credit_card_type.ids, SP.Sale.credit_card_type.labels, this.getCreditCardTypeId(), fncIsMandatoryFunction.bind(this)),'Card Type');
		

		this.addElementGroup('card_name', new FW.GUIComponent.TextInputGroup(this.getCardName(), fncIsMandatoryFunction.bind(this)),'Card Holder Name');
		

		var fncCreditCardValidation	= function(strNumber)
									{
										intCreditCardType	= (SP.Sale.getInstance().getSaleAccount().directDebitDetails.elementGroups.credit_card_type_id.getValue());
										return window._validate.creditCardNumber(strNumber, intCreditCardType);
									}
		this.addElementGroup('card_number', new FW.GUIComponent.TextInputGroup(this.getCardNumber(), fncIsMandatoryFunction.bind(this), fncCreditCardValidation.bind(this)),'Card Number');
		


		var fncCCVValidation	= function(strCVV)
								{
									intCreditCardType	= (SP.Sale.getInstance().getSaleAccount().directDebitDetails.elementGroups.credit_card_type_id.getValue());
									return window._validate.creditCardCVV(strCVV, intCreditCardType);
								}
		this.addElementGroup('cvv', new FW.GUIComponent.TextInputGroup(this.getCVV(), fncIsMandatoryFunction.bind(this), fncCCVValidation.bind(this)),'CVV');
		
		
		this.addElementGroup('expiry_date',new FW.GUIComponent.CreditCardExpiryGroup(this.getExpiryDate(), fncIsMandatoryFunction.bind(this), window._validate.creditCardExpiry.bind(this)),'Expiry Date',['expiry_month', 'expiry_year']);
		
		
		
		// onchange Events
		var fncChangeCreditCardType	= function()
									{
										this.elementGroups.card_number.isValid();
										this.elementGroups.cvv.isValid();
									}
		Event.observe(this.elementGroups.credit_card_type_id.aInputs[0], 'change', fncChangeCreditCardType.bind(this));

		// Disable the inputs if the Sale is to an existing customer
		switch (SP.Sale.getInstance().getSaleTypeId())
		{
			case SP.Sale.SaleType.SALE_TYPE_EXISTING:
			case SP.Sale.SaleType.SALE_TYPE_WINBACK:
				for (var sElementGroup in this.elementGroups)
				{
					this.elementGroups[sElementGroup].disable();
				}
				break;
		}
	},

	

	showValidationTip: function()
	{
		return false;
	},

	getExpiryDate: function()
	{
		return new Array(this.getExpiryMonth(), this.getExpiryYear());
	},

	setExpiryDate: function($values)
	{
		this.setExpiryMonth($values[0]);
		this.setExpiryYear($values[1]);
	},


	setCreditCardTypeId: function(value)
	{
		this.object.credit_card_type_id = value;
	},

	getCreditCardTypeId: function()
	{
		return this.object.credit_card_type_id;
	},

	setCardName: function(value)
	{
		this.object.card_name = value;
	},

	getCardName: function()
	{
		return this.object.card_name;
	},

	setCardNumber: function(value)
	{
		this.object.card_number = value;
	},

	getCardNumber: function()
	{
		return this.object.card_number;
	},

	setExpiryMonth: function(value)
	{
		this.object.expiry_month = value;
	},

	getExpiryMonth: function()
	{
		return this.object.expiry_month;
	},

	setExpiryYear: function(value)
	{
		this.object.expiry_year = value;
	},

	getExpiryYear: function()
	{
		return this.object.expiry_year;
	},

	setCVV: function(value)
	{
		this.object.cvv = value;
	},

	getCVV: function()
	{
		return this.object.cvv;
	}

		}
);