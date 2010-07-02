Object.extend(SP.Sale.prototype, {

	buildGUI: function()
	{
		// The 'Verify' and the 'Awaiting Dispatch' actions are in the same function.  They probably shouldn't be
		var strVerifyButtonLabel = (SP.Sale.canBeSetToAwaitingDispatch) ? "Dispatch Sale" : "Verify Sale";

		var buttons = (SP.Sale.canAmendSale ? '&nbsp;<input type="button" value="Amend Sale" onclick="SP.Sale.getInstance().amendSale()">&nbsp;' : '') +
					  (SP.Sale.canCancelSale ? '&nbsp;<input type="button" value="Cancel Sale" onclick="SP.Sale.getInstance().cancelSale()">&nbsp;' : '') +
					  (SP.Sale.canRejectSale ? '&nbsp;<input type="button" value="Reject Sale" onclick="SP.Sale.getInstance().rejectSale()">&nbsp;' : '') +
					  (SP.Sale.canVerifySale ? '&nbsp;<input type="button" value="'+ strVerifyButtonLabel +'" onclick="SP.Sale.getInstance().verifySale()">&nbsp;' : '');

		// Add contents to this.detailsContainer
		this.detailsContainer.innerHTML = ''
		+ '<div class="MediumSpace" style="width: 965px;"></div>'
		+ '<div class="Title" style="position: relative;">Sale Status<input type="button" value="View History" onclick="SP.Sale.showHistory(' + this.getId() + ')" style="position: absolute; right: 0px; bottom: -1px;"/></div><div class="Page" style="width: 963px;"><table class="data-table" cellpadding="0" cellspacing="0" border="0" style="width: 100%;"><tr><td>Status:</td><td>' + this.getStatus() + '</td></tr><tr><td>Description:</td><td>' + this.getStatusDescription() + '</td></tr></table></div><div class="MediumSpace"></div>'
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
							+ '<td><input type="button" value="Add Item" onclick="SP.Sale.getInstance().addSaleItem();" /></td>'
						+ '</TR>'
					+ '</TABLE>'
				+ '</div>'
			+ '</div>'
		+ '</div>'
		+ '<div class="MediumSpace"></div>'
		+ '<div style="position: relative;" class="Title">Sale Items<input type="button" value="Collapse All" style="position: absolute; right: 0px; bottom: -1px;" onclick="if (this.value == \'Collapse All\') { this.value = \'Expand All\'; SP.Sale.Item.collapseAll();} else { this.value = \'Collapse All\'; SP.Sale.Item.expandAll();} " /></div>'
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
		+ '</div>'
		+ '<div class="MediumSpace"></div>'
		+ '<div class="Title" style="position: relative;"><span>Sale Notes</span><div style="position: absolute; right: 0px; bottom: -1px;"><button class="data-entry" onclick="SP.Sale.Note.registerNote(new SP.Sale.Note());">Add Note</button>&nbsp;<button id="sale-notes-collapse-all" onclick="SP.Sale.Note.toggleExpandedAll();">Collapse All</button></div></div>'
		+ '<div class="Page">'
		+ '<div class="FieldContent" style="padding:0; margin:0;">'
			+ '<table id="sale-notes-table" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 0; padding: 0; width:100%;">'
			+ '</table>'
		+ '</div>'
		+ '</div>'
		+ '<div class="MediumSpace"></div>'
		+ '<span id="submit-button-panel" class="data-entry"><input type="button" value="Submit" onclick="SP.Sale.getInstance().submit()">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="SP.Sale.getInstance().cancelAmend()"></span>'
		+ '<span id="commit-button-panel"><input type="button" value="Commit" onclick="SP.Sale.getInstance().commit()">&nbsp;&nbsp;<input type="button" value="Edit" onclick="SP.Sale.getInstance().cancel()"></span>'

		+ '<span id="after-commit-button-panel">' + buttons + '</span>'


		// WIP: This is for debug purposes only!!! Remove it before deployment!
		//+ '<br/><br/><input type="button" value="Toggle Entry/Display" onclick="$ID(\'submit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'inline\' : \'none\'); $ID(\'commit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'none\' : \'inline\'); document.body.className = (document.body.className == \'data-display\' ? \'data-entry\' : \'data-display\')">'

		+ '<span id="amend-button-panel" class="data-display"></span>'

		+ '';

		$ID('commit-button-panel').style.display = 'none';
		$ID('submit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';

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

		//this.loadNotes();
		SP.Sale.Note.buildGUI();

		Event.observe($ID('sale_product_type_list'), 'change', this.changeProductType.bind(this), true);

		SP.Sale.endLoading();
	},

	getStatus: function()
	{
		if (this.isNewSale()) return "New Sale";
		return this.object.status;
	},

	getStatusDescription: function()
	{
		if (this.isNewSale()) return "Details not yet submitted.";
		return this.object.status_description;
	},

	_commitOK: function($saleId)
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';
		alert("The sale has been saved. The reference number for this sale is " + $saleId + ".");
		//document.location = document.location.toString().replace(/\/Sales\/.*/i, '/Sales/ListSales/Last');
	},

	cancelSale: function()
	{
		var strReason = new String;

		strReason = prompt("Are you sure you want to cancel this sale?  Please supply a reason for cancelling it.", "");

		if (strReason == null)
		{
			return;
		}

		this._remoteSaleFunctionCall('cancelSale', this._cancelOK, strReason);
	},

	_cancelOK: function()
	{
		window.scroll(0,0);
		alert("The sale has been cancelled.");
		document.location = document.location.toString().replace(/\/Sales\/.*/i, '/Sales/ListSales/Last');
	},

	rejectSale: function()
	{
		var strReason = new String;

		strReason = prompt("Are you sure you want to reject this sale?  Please supply a reason for rejecting it.", "");

		if (strReason == null)
		{
			return;
		}

		this._remoteSaleFunctionCall('rejectSale', this._rejectOK, strReason);
	},

	_rejectOK: function()
	{
		window.scroll(0,0);
		alert("The sale has been rejected.");
		document.location = document.location.toString().replace(/\/Sales\/.*/i, '/Sales/ListSales/Last');
	},

	verifySale: function()
	{
		var strMsg = (SP.Sale.canBeSetToAwaitingDispatch) ? "Are you sure you want to dispatch this sale?" : "Are you sure you want to verify this sale?";

		if (confirm(strMsg))
		{
			this._remoteSaleFunctionCall('verifySale', this._verifyOK);
		}
	},

	_verifyOK: function()
	{
		var strMsg = (SP.Sale.canBeSetToAwaitingDispatch) ? "The sale is awaiting dispatch." : "The sale has been verified.";

		window.scroll(0,0);
		alert(strMsg);
		document.location = document.location.toString().replace(/\/Sales\/.*/i, '/Sales/ListSales/Last');
	},

	_remoteSaleFunctionCall: function($remoteFunName, $okFunc /*, RemoteArg2, RemoteArg3, etc */)
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		var remote = SalesPortal.getRemoteFunction('Sale', $remoteFunName, $okFunc.bind(this), this._processError.bind(this));

		// Add in variables (sale id and RemoteArg2, etc)

		// First variable is always the sale id
		remote = remote.curry(SP.Sale.getInstance().getId());

		for (var i=2, j=arguments.length; i < j; i++)
		{
			remote = remote.curry(arguments[i]);
		}

		// Call the remote method
		remote();
	},

	_processError: function($return)
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';
		alert($return['ERROR']);
	},

	amendSale: function()
	{
		this.cancel();
	},

	cancelAmend: function()
	{
		// Must reload the page as the sale object may have been amended and we need to show the original version
		document.location.reload();
		return;
	}
});

Object.extend(SP.Sale.SaleAccount.prototype, {

	buildGUI: function()
	{
		this.detailsContainer.innerHTML = '<table id="account_details_table" class="data-table"></table>';




		this.setWorkingTable($ID('account_details_table'));
		this.addElementGroup('vendor', new FW.GUIComponent.DropDown(SP.Sale.vendors.ids, SP.Sale.vendors.labels, this.getVendorId(), true),'Vendor','vendor_id');
		this.getWorkingTable().rows[this.getWorkingTable().rows.length - 1].className += " read-only";
		this.elementGroups.vendor.disable();
		this.addElementGroup('businessName', new FW.GUIComponent.TextInputGroup(this.getBusinessName(), true),'Business Name','business_name');
		this.addElementGroup('tradingName', new FW.GUIComponent.TextInputGroup(this.getTradingName()),'Trading Name','trading_name');
		this.addElementGroup('abn',new FW.GUIComponent.TextInputGroup(this.getABN(), false, window._validate.australianBusinessNumber.bind(this)),'ABN');
		this.addElementGroup('acn',new FW.GUIComponent.TextInputGroup(this.getACN(), false, window._validate.australianCompanyNumber.bind(this)),'ACN');
		this.addElementGroup('addressLine1',new FW.GUIComponent.TextInputGroup(this.getAddressLine1(), true),'Address (Line 1)','address_line_1');
		this.addElementGroup('addressLine2',new FW.GUIComponent.TextInputGroup(this.getAddressLine2()),'Address (Line 2)','address_line_2');
		this.addElementGroup('suburb',new FW.GUIComponent.TextInputGroup(this.getSuburb(), true),'Suburb');
		this.addElementGroup('postcode',new FW.GUIComponent.TextInputGroup(this.getPostcode(), true, window._validate.postcode.bind(this)),'Postcode');
		this.addElementGroup('state',new FW.GUIComponent.DropDown(SP.Sale.states.ids, SP.Sale.states.labels, this.getStateId(), true),'State','state_id');

		this.setWorkingTable($ID('bill-details'));

		this.addElementGroup('bill_delivery_type_id',new FW.GUIComponent.DropDown(SP.Sale.bill_delivery_type.ids, SP.Sale.bill_delivery_type.labels, this.getBillDeliveryTypeId(), true),'Bill Delivery Method');

		//this.elementGroups.bill_payment_type_id = SP.Sale.GUIComponent.createDropDown(SP.Sale.bill_payment_type.ids, SP.Sale.bill_payment_type.labels, this.getBillPaymentTypeId(), true);
		//SP.Sale.GUIComponent.appendElementGroupToTable(table, 'Bill Payment Method', this.elementGroups.bill_payment_type_id);
		this.addElementGroup('bill_payment_type_id',new FW.GUIComponent.DropDown(SP.Sale.bill_payment_type.ids, SP.Sale.bill_payment_type.labels, this.getBillPaymentTypeId(), true),'Bill Payment Method');

		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += ' read-only';

		// var isMandatoryFunction	= function(){return (SP.Sale.GUIComponent.getElementGroupValue(SP.Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id) == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);};
		// this.elementGroups.direct_debit_type_id = SP.Sale.GUIComponent.createDropDown(SP.Sale.direct_debit_type.ids, SP.Sale.direct_debit_type.labels, this.getDirectDebitTypeId(), isMandatoryFunction.bind(this));
		// SP.Sale.GUIComponent.appendElementGroupToTable(table, 'Direct Debit Type', this.elementGroups.direct_debit_type_id);
		var isMandatoryFunction	= function(){return (SP.Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id.getValue() == SP.Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);};
		this.addElementGroup('direct_debit_type_id',new FW.GUIComponent.DropDown(SP.Sale.direct_debit_type.ids, SP.Sale.direct_debit_type.labels, this.getDirectDebitTypeId(), isMandatoryFunction.bind(this)),'Direct Debit Type');


		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += ' read-only';
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].id = 'direct-debit-type-table';

		this.setBillPaymentTypeId(this.object.bill_payment_type_id);

		if (SP.Sale.vendors.ids.length == 1)
		{
			var nonOption = this.elementGroups.vendor.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.vendor.isValid();
		}

		if (SP.Sale.bill_delivery_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_delivery_type_id.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_delivery_type_id.isValid();
		}

		if (SP.Sale.bill_payment_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_payment_type_id.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_payment_type_id.isValid();
		}

		if (SP.Sale.direct_debit_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.direct_debit_type.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.direct_debit_type.isValid();
		}

		SP.Sale.getInstance().changeVendor();
		this.changeBillPaymentType();
		this.changeDirectDebitType();
		var oDirectDebitDetails = this.getSaleAccountDirectDebitTypeDetails();
		if (oDirectDebitDetails!=null)
		{
			this.directDebitDetails.disable();
		}
		// Event.observe(this.elementGroups.vendor.inputs[0], 'change', SP.Sale.getInstance().changeVendor.bind(SP.Sale.getInstance()), true);

		// Event.observe(this.elementGroups.bill_delivery_type_id.inputs[0], 'change', this.changeBillDeliveryType.bind(this));
		// Event.observe(this.elementGroups.bill_payment_type_id.inputs[0], 'change', this.changeBillPaymentType.bind(this));
		// Event.observe(this.elementGroups.direct_debit_type_id.inputs[0], 'change', this.changeDirectDebitType.bind(this));

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
					//SP.Sale.GUIComponent.disableElementGroup(this.elementGroups[sElementGroup]);
					this.elementGroups[sElementGroup].disable();
				}
				break;
		}
	}
	
	
})