//2290 - 27997
FW.Package.create('SP.Sale.SaleAccount', {

	requires: ['SP.Sale.BillPaymentType','SP.Sale.DirectDebitType','SP.Sale.SaleAccount.DirectDebit.BankAccount','SP.Sale.SaleAccount.DirectDebit.CreditCard','FW.GUIComponent.DropDown','FW.GUIComponent.TextInputGroup','FW.GUIComponent.MultipleSelect','FW.GUIComponent.RadioButtonsGroup','FW.GUIComponent.PasswordInputGroup'],
	extends: 'FW.GUIComponent',


	directDebitDetails: null,

	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,

				state_id: null,
				vendor_id: null,

				bill_delivery_type_id: null,
				bill_payment_type_id: null,
				direct_debit_type_id: null,

				sale_account_direct_debit_credit_card_id: null,
				sale_account_direct_debit_credit_card: null,
				sale_account_direct_debit_bank_account_id: null,
				sale_account_direct_debit_bank_account: null,

				external_reference: null,
				business_name: null,
				trading_name: null,
				abn: null,
				acn: null,
				address_line_1: null,
				address_line_2: null,
				suburb: null,
				postcode: null
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
		// Account Details
		this.detailsContainer.innerHTML = '<table id="account_details_table" class="data-table"></table>';

		
		this.setWorkingTable($ID('account_details_table'));
		this.addElementGroup('vendor', new FW.GUIComponent.DropDown(SP.Sale.vendors.ids, SP.Sale.vendors.labels, this.getVendorId(), true),'Vendor','vendor_id');
		this.addElementGroup('businessName', new FW.GUIComponent.TextInputGroup(this.getBusinessName(), true),'Business Name','business_name');
		this.addElementGroup('tradingName', new FW.GUIComponent.TextInputGroup(this.getTradingName()),'Trading Name','trading_name');
		this.addElementGroup('abn',new FW.GUIComponent.TextInputGroup(this.getABN(), false, window._validate.australianBusinessNumber.bind(this)),'ABN');
		this.addElementGroup('acn',new FW.GUIComponent.TextInputGroup(this.getACN(), false, window._validate.australianCompanyNumber.bind(this)),'ACN');
		this.addElementGroup('addressLine1',new FW.GUIComponent.TextInputGroup(this.getAddressLine1(), true),'Address (Line 1)','address_line_1');
		this.addElementGroup('addressLine2',new FW.GUIComponent.TextInputGroup(this.getAddressLine2()),'Address (Line 2)','address_line_2');
		this.addElementGroup('suburb',new FW.GUIComponent.TextInputGroup(this.getSuburb(), true),'Suburb');
		this.addElementGroup('postcode',new FW.GUIComponent.TextInputGroup(this.getPostcode(), true, window._validate.postcode.bind(this)),'Postcode');
		this.addElementGroup('state',new FW.GUIComponent.DropDown(SP.Sale.states.ids, SP.Sale.states.labels, this.getStateId(), true),'State','state_id');
	
		
		this.setWorkingTable($ID('bill-delivery-type-table'));
		this.addElementGroup('bill_delivery_type_id',new FW.GUIComponent.DropDown(SP.Sale.bill_delivery_type.ids, SP.Sale.bill_delivery_type.labels, this.getBillDeliveryTypeId(), true),'Bill Delivery Method');
		
		//for existing accounts we should not display the details of the direct debit arrangments
		if (this.getBillPaymentTypeId() == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT && SP.Sale.getInstance().getSaleTypeId() != SP.Sale.SaleType.SALE_TYPE_NEW)
		{
			this.setBillPaymentTypeId(SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_ACCOUNT);
		
		}
		
		this.setWorkingTable($ID('bill-payment-type-table'));
		this.addElementGroup('bill_payment_type_id',new FW.GUIComponent.DropDown(SP.Sale.bill_payment_type.ids, SP.Sale.bill_payment_type.labels, this.getBillPaymentTypeId(), true),'Bill Payment Method');
		
		this.setWorkingTable($ID('direct-debit-type-table'));
		var isMandatoryFunction	= function(){return (SP.Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id.getValue() == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);};
		this.addElementGroup('direct_debit_type_id',new FW.GUIComponent.DropDown(SP.Sale.direct_debit_type.ids, SP.Sale.direct_debit_type.labels, this.getDirectDebitTypeId(), isMandatoryFunction.bind(this)),'Direct Debit Type');
		

		this.setBillPaymentTypeId(this.object.bill_payment_type_id);

		if (SP.Sale.vendors.ids.length == 1)
		{
			var nonOption = this.elementGroups.vendor.aInputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.vendor.isValid();
		}

		if (SP.Sale.bill_delivery_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_delivery_type_id.aInputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_delivery_type_id.isValid();
		}

		if (SP.Sale.bill_payment_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_payment_type_id.aInputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_payment_type_id.isValid();
		}

		if (SP.Sale.direct_debit_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.direct_debit_type.aInputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.direct_debit_type.isValid();
		}

		SP.Sale.getInstance().changeVendor();
		this.changeBillPaymentType();
		this.changeDirectDebitType();

		Event.observe(this.elementGroups.vendor.aInputs[0], 'change', SP.Sale.getInstance().changeVendor.bind(SP.Sale.getInstance()), true);

		Event.observe(this.elementGroups.bill_delivery_type_id.aInputs[0], 'change', this.changeBillDeliveryType.bind(this));
		Event.observe(this.elementGroups.bill_payment_type_id.aInputs[0], 'change', this.changeBillPaymentType.bind(this));
		Event.observe(this.elementGroups.direct_debit_type_id.aInputs[0], 'change', this.changeDirectDebitType.bind(this));

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

	changeBillDeliveryType: function()
	{
		this.setBillDeliveryTypeId(this.elementGroups.bill_delivery_type_id.getValue());

		// re-Validate the Email field
		arrContacts	= SP.Sale.getInstance().getContacts();
		for (var i = 0; i < arrContacts.length; i++)
		{
			arrContacts[i].elementGroups.email.isValid();
		}
	},

	changeBillPaymentType: function()
	{
		// Update the Bill Payment Type
		this.setBillPaymentTypeId(this.elementGroups.bill_payment_type_id.getValue());

		// Rerun Direct Debit Type Validation
		this.elementGroups.direct_debit_type_id.isValid();
	},

	changeDirectDebitType: function()
	{
		this.setDirectDebitTypeId(this.elementGroups.direct_debit_type_id.getValue());
	},


	updateFromGUI: function($super)
	{
		var bUpdateOk = $super();
		if(bUpdateOk)
		{		
			//update the child objects
			if (this.object.bill_payment_type_id == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
			{
				this.getSaleAccountDirectDebitTypeDetails().updateFromGUI();
			}
		}
		return bUpdateOk;
	},

	isValid: function($super)
	{
		if(!$super())
		{
			return false;
		}

		// And the child objects ...
		if (this.object.bill_payment_type_id == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			if (this.object.direct_debit_type_id != SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD && this.object.direct_debit_type_id != SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				// Invalid DD method selected!
				return false;
			}
			if (!this.getSaleAccountDirectDebitTypeDetails().isValid()) return false;
		}
		else if (this.object.bill_payment_type_id != SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_ACCOUNT)
		{
			// Invalid bill payment method selected!
			return false;
		}
		return true;
	},

	updateChildObjectsDisplay: function($readOnly)
	{
		if (this.object.bill_payment_type_id == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			if (this.object.direct_debit_type_id != SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD && this.object.direct_debit_type_id != SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				return;
			}
			this.getSaleAccountDirectDebitTypeDetails().updateDisplay();
		}
	},

	showValidationTip: function()
	{
		// Validate the values and invoke the isValid method of child objects
		$isValid = true;


		// Validate all the fields ...

		// WIP

		if (!$isValid) return true;

		// And the child objects ...

		if (this.object.bill_payment_type_id == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			if (this.object.direct_debit_type_id == SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD
			 || this.object.direct_debit_type_id == SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				if (this.getSaleAccountDirectDebitTypeDetails().showValidationTip()) return true;
			}
			else
			{
				// NOTHING SELECTED!!!
				return true;
			}
		}
		else if (this.object.bill_payment_type_id != SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_ACCOUNT)
		{
			// NOT VALID! MUST BE DD OR ACCOUNT.
			return true;
		}

		return false;
	},

	getAccountNumber	: function()
	{
		return (this.object.account_number) ? this.object.account_number : '[ New Account ]';
	},

	setStateId: function(state_id)
	{
		this.object.state_id = state_id;
	},

	getStateId: function()
	{
		return this.object.state_id;
	},

	setVendorId: function(vendor_id)
	{
		this.object.vendor_id = vendor_id;
	},

	getVendorId: function()
	{
		return this.object.vendor_id;
	},

	setBillDeliveryTypeId: function(value)
	{
		this.object.bill_delivery_type_id = value;
	},

	getBillDeliveryTypeId: function()
	{
		return this.object.bill_delivery_type_id;
	},

	setBillPaymentTypeId: function(bill_payment_type_id)
	{
		if (this.object.bill_payment_type_id != bill_payment_type_id)
		{
			this.destroyDirectDebitDetails();
		}
		this.object.bill_payment_type_id = bill_payment_type_id;
		$ID('direct-debit-type-table').style.visibility = (this.object.bill_payment_type_id == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT ? 'visible' : 'hidden');
		this.getSaleAccountDirectDebitTypeDetails();
	},

	getBillPaymentTypeId: function()
	{
		return this.object.bill_payment_type_id;
	},

	setDirectDebitTypeId: function(direct_debit_type_id)
	{
		if (   (this.object.direct_debit_type_id != direct_debit_type_id)
			|| (direct_debit_type_id == SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD && this.directDebitDetails instanceof SP.Sale.SaleAccount.DirectDebit.BankAccount)
			|| (direct_debit_type_id == SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT && this.directDebitDetails instanceof SP.Sale.SaleAccount.DirectDebit.CreditCard))
		{
			this.destroyDirectDebitDetails();
		}
		this.object.direct_debit_type_id = direct_debit_type_id;
		this.getSaleAccountDirectDebitTypeDetails();
	},

	getDirectDebitTypeId: function()
	{
		return this.object.direct_debit_type_id;
	},

	destroyDirectDebitDetails: function()
	{
		if (this.directDebitDetails != null)
		{
			this.directDebitDetails.destroy();
			this.directDebitDetails = null;
			var oldTable = $ID('direct-debit-detail-table');
			var newTable = document.createElement('table');
			oldTable.parentNode.replaceChild(newTable, oldTable);
			newTable.id = oldTable.id;
		}
	},

	getSaleAccountDirectDebitTypeDetails: function()
	{
		if (this.object.bill_payment_type_id != SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			return null;
		}
		if (this.object.direct_debit_type_id != SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT && this.object.direct_debit_type_id != SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD) return null;
		if (this.directDebitDetails == null)
		{
			if (this.object.direct_debit_type_id == SP.Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				this.directDebitDetails = new SP.Sale.SaleAccount.DirectDebit.BankAccount(this.object.sale_account_direct_debit_bank_account);
				this.object.sale_account_direct_debit_bank_account = this.directDebitDetails.object;
			}
			else
			{
				this.directDebitDetails = new SP.Sale.SaleAccount.DirectDebit.CreditCard(this.object.sale_account_direct_debit_credit_card);
				this.object.sale_account_direct_debit_credit_card = this.directDebitDetails.object;
			}
			this.directDebitDetails.setContainers($ID('direct-debit-detail-table'));
		}
		return this.directDebitDetails;
	},

	setExternalReference: function(value)
	{
		this.object.external_reference = value;
	},

	getExternalReference: function()
	{
		return this.object.external_reference;
	},

	setBusinessName: function(value)
	{
		this.object.business_name = value;
	},

	getBusinessName: function()
	{
		return this.object.business_name;
	},

	setTradingName: function(value)
	{
		this.object.trading_name = value;
	},

	getTradingName: function()
	{
		return this.object.trading_name;
	},

	setABN: function(value)
	{
		this.object.abn = value;
	},

	getABN: function()
	{
		return this.object.abn;
	},

	setACN: function(value)
	{
		this.object.acn = value;
	},

	getACN: function()
	{
		return this.object.acn;
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
	}

}
);