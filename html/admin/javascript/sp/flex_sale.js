
Object.extend(Sale.prototype, {

	buildGUI: function()
	{
		// Add contents to this.detailsContainer
		this.detailsContainer.innerHTML = '' 
		+ '<div class="MediumSpace"></div>' 
		+ '<table cellpadding="0" cellspacing="0" border="0" width="975">' 
			+ '<tr>' 
				+ '<td width="480">' 
					+ '<div class="PartTitle">Account Details</div>' 
						+ '<div class="PartPage" id="account_details_holder"></div>' 
					+ '</td>' 
					+ '<td width="15"></td>' 
					+ '<td width="480">' 
						+ '<div class="PartTitle">Primary Contact Details</div>' 
						+ '<div class="PartPage" id="primary_contact_details_holder"></div>' 
				+ '</td>' 
			+ '</tr>' 
		+ '</table>' 
		+ '<div class="data-entry">'
			+ '<div class="MediumSpace"></div>' 
			+ '<div class="Title">Available Products</div>' 
			+ '<div class="Page">' 
				+ '<div class="FieldContent">' 
					+ '<TABLE cellpadding="0" cellspacing="0" border="0">' 
						+ '<TR>' 
							+ '<TD>' 
								+ '<div id="divProductType">' 
									+ '<select name="product_type_id" id="sale_product_type_list">' 
										+ '<option value="">Product Type</option>' 
									+ '</select>' 
								+ '</div>' 
							+ '</TD>' 
							+ '<TD>' 
								+ '<div id="divProductList">' 
									+ '<select name="product_id" id="sale_product_list">' 
										+ '<option value="">Product</option>' 
									+ '</select>' 
								+ '</div>' 
							+ '</TD>' 
							+ '<td><input type="button" value="Add Item" onclick="Sale.getInstance().addSaleItem();" /></td>' 
						+ '</TR>' 
					+ '</TABLE>' 
				+ '</div>' 
			+ '</div>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>' 
		+ '<div class="Title">Sale Items</div>' 
		+ '<div class="Page">' 
			+ '<div class="FieldContent" style="padding:0; margin:0;">' 
				+ '<table id="sale-items-table" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 0; padding: 0; width:100%;">' 
				+ '</table>' 
			+ '</div>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>' 
		+ '<div class="Title">Billing Details</div>' 
		+ '<div class="Page">'
				+ '<div><TABLE id="bill-details" cellpadding="0" cellspacing="0" border="0" class="data-table"></TABLE></div>' 
			+ '<table cellpadding="0" cellspacing="0" border="0" id="direct-debit-detail-table" class="data-table read-only"></table>' 
			+ '<div id="PaymentDetails" style="display: none;"></div>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>'
		+ '<span id="submit-button-panel" class="data-entry"><input type="button" value="Submit" onclick="Sale.getInstance().submit()"></span>'
		+ '<span id="commit-button-panel"><input type="button" value="Commit" onclick="Sale.getInstance().commit()">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="Sale.getInstance().cancel()"></span>'
		+ '<span id="after-commit-button-panel"><input type="button" value="Amend Sale" onclick="document.location.reload()">&nbsp;&nbsp;<input type="button" value="Cancel Sale" onclick="document.location.reload()">&nbsp;&nbsp;<input type="button" value="Reject Sale" onclick="document.location.reload()">&nbsp;&nbsp;<input type="button" value="Verify Sale" onclick="document.location.reload()"></span>'
		
		
		// WIP: This is for debug purposes only!!! Remove it before deployment!
		//+ '<br/><br/><input type="button" value="Toggle Entry/Display" onclick="$ID(\'submit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'inline\' : \'none\'); $ID(\'commit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'none\' : \'inline\'); document.body.className = (document.body.className == \'data-display\' ? \'data-entry\' : \'data-display\')">'
		
		
		+ '';
		
		$ID('commit-button-panel').style.display = 'none';
		$ID('submit-button-panel').style.display = 'inline';
		$ID('after-commit-button-panel').style.display = 'none';
		
		var saleAccount = this.getSaleAccount();
		saleAccount.setContainers($ID('account_details_holder'));
		
		var contacts = this.getContacts();
		if (contacts.length == 0)
		{
			this.addContact();
		}
		contacts[0].setContainers($ID('primary_contact_details_holder'));
		
		for (var i = 0, l = this.saleItems.length; i < l; i++)
		{
			this.addSaleItem(this.saleItems[i]);
		}
		
		Event.observe($ID('sale_product_type_list'), 'change', this.changeProductType.bind(this), true);
	}
});

Object.extend(Sale.SaleAccount.prototype, {

	buildGUI: function()
	{
		this.detailsContainer.innerHTML = '<table id="account_details_table" class="data-table"></table>';

		var table = $ID('account_details_table');

		this.elementGroups.vendor = Sale.GUIComponent.createDropDown(Sale.vendors.ids, Sale.vendors.labels, this.getVendorId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Vendor', this.elementGroups.vendor);
		table.rows[table.rows.length - 1].className += " read-only";

		this.elementGroups.businessName = Sale.GUIComponent.createTextInputGroup(this.getBusinessName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Business Name', this.elementGroups.businessName);

		this.elementGroups.tradingName = Sale.GUIComponent.createTextInputGroup(this.getTradingName());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Trading Name', this.elementGroups.tradingName);

		this.elementGroups.abn = Sale.GUIComponent.createTextInputGroup(this.getABN(), false, window._validate.australianBusinessNumber.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'ABN', this.elementGroups.abn);

		this.elementGroups.acn = Sale.GUIComponent.createTextInputGroup(this.getACN(), false, window._validate.australianCompanyNumber.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'ACN', this.elementGroups.acn);

		this.elementGroups.addressLine1 = Sale.GUIComponent.createTextInputGroup(this.getAddressLine1(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 1)', this.elementGroups.addressLine1);

		this.elementGroups.addressLine2 = Sale.GUIComponent.createTextInputGroup(this.getAddressLine2());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 2)', this.elementGroups.addressLine2);

		this.elementGroups.suburb = Sale.GUIComponent.createTextInputGroup(this.getSuburb(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Suburb', this.elementGroups.suburb);

		this.elementGroups.postcode = Sale.GUIComponent.createTextInputGroup(this.getPostcode(), true, window._validate.postcode.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.postcode);

		this.elementGroups.state = Sale.GUIComponent.createDropDown(Sale.states.ids, Sale.states.labels, this.getStateId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'State', this.elementGroups.state);
		
		var table = $ID('bill-details');
		this.elementGroups.bill_delivery_type_id = Sale.GUIComponent.createDropDown(Sale.bill_delivery_type.ids, Sale.bill_delivery_type.labels, this.getBillDeliveryTypeId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Bill Delivery Method', this.elementGroups.bill_delivery_type_id);

		this.elementGroups.bill_payment_type_id = Sale.GUIComponent.createDropDown(Sale.bill_payment_type.ids, Sale.bill_payment_type.labels, this.getBillPaymentTypeId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Bill Payment Method', this.elementGroups.bill_payment_type_id);
		table.rows[table.rows.length-1].className += ' read-only';

		var isMandatoryFunction	= function(){return (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id) == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);};
		this.elementGroups.direct_debit_type_id = Sale.GUIComponent.createDropDown(Sale.direct_debit_type.ids, Sale.direct_debit_type.labels, this.getDirectDebitTypeId(), isMandatoryFunction.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Direct Debit Type', this.elementGroups.direct_debit_type_id);
		table.rows[table.rows.length-1].className += ' read-only';
		table.rows[table.rows.length-1].id = 'direct-debit-type-table';

		this.setBillPaymentTypeId(this.object.bill_payment_type_id);
		
		if (Sale.vendors.ids.length == 1)
		{
			var nonOption = this.elementGroups.vendor.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.vendor.isValid();
		}

		if (Sale.bill_delivery_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_delivery_type_id.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_delivery_type_id.isValid();
		}

		if (Sale.bill_payment_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_payment_type_id.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_payment_type_id.isValid();
		}

		if (Sale.direct_debit_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.direct_debit_type.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.direct_debit_type.isValid();
		}

		Sale.getInstance().changeVendor();
		this.changeBillPaymentType();
		this.changeDirectDebitType();

		Event.observe(this.elementGroups.vendor.inputs[0], 'change', Sale.getInstance().changeVendor.bind(Sale.getInstance()), true);
		
		Event.observe(this.elementGroups.bill_delivery_type_id.inputs[0], 'change', this.changeBillDeliveryType.bind(this));
		Event.observe(this.elementGroups.bill_payment_type_id.inputs[0], 'change', this.changeBillPaymentType.bind(this));
		Event.observe(this.elementGroups.direct_debit_type_id.inputs[0], 'change', this.changeDirectDebitType.bind(this));
	}
	
})