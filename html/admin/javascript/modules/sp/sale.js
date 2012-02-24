var Class = require('fw/class');
var SalesPortal = require('./salesportal'),
	JSONObject = require('./sale/jsonobject'),
	SaleType = require('./sale/saletype'),
	SaleItem = require('./sale/item');
// NOTE: These requires must run later to prevent circular dependencies
var	SaleAccount,
	Contact,
	Note;

var self = new Class({
	extends : require('sp/guicomponent'),

	newSale: false,
	remote$listProductTypesForVendor: null,
	remote$listProductsForProductTypeModuleAndVendor: null,
	contacts: [],
	saleAccount: null,
	saleItems: null,

	construct : function (container, saleId, initialStyle) {
		self.startLoading();

		// Fulfill remaining requires
		SaleAccount = require('./sale/saleaccount');
		Contact = require('./sale/contact');
		Note = require('./sale/note');

		this.object = {
			id: null,
			sale_type_id: null, //FK to sale_type table
			sale_status_id: null,
			created_on: null,
			created_by: null, // dealer_id - FK to dealer table
			commission_paid_on: null,
			//The following are only supplied when retrieving a sale from the database
			status: null,				//string, eg"Submitted",
			status_description:null		// string eg"New sale",
		};

		this.detailsContainer = container;
		document.body.originalClassName = document.body.className;
		document.body.className = document.body.originalClassName + " " + initialStyle;
		self.instance = this;
		this.saleItems = [];
		this.elementGroups = {};
		this.contacts = [];
		if (saleId == null) {
			this.newSale = true;

			if (!self.bDebug) {
				this.buildPageForObject(this.object);
			} else {
				this.buildPageForObject(require('./testsale'));
			}
		} else {
			var onLoadFunc = this.buildPageForObject.bind(this);
			var remote = SalesPortal.getRemoteFunction('Sale', 'load', onLoadFunc);
			remote(saleId);
		}
	},

	buildPageForObject : function (object) {
		// If we don't have a Sale Type yet, throw a selection popup to prompt user
		if (!object.sale_type_id) {
			if (self.sale_type.ids.length == 1 && self.sale_type.ids.first() == SaleType.SALE_TYPE_NEW) {
				// If the only permitted Sale Type is New Customers, then skip the prompt
				object.sale_type_id	= SaleType.SALE_TYPE_NEW;
				this.buildPageForObject(object);
				return;
			}

			var	oSaleTypePopup	= new Reflex_Popup(40);
			oSaleTypePopup.setTitle('Sale Type');

			var	sSalePopupContent	= "" +
"	<div class='Page' style='width: auto;'>" +
"		<table style='width: 100%'>" +
"			<thead>" +
"				<tr>" +
"					<th style='width: 180px;'>Sale Type:</th>" +
"					<td>" +
"						<select id='sale_type_select'>" +
"						</select>" +
"					</td>" +
"				</tr>" +
"			</thead>" +
"			<tbody id='sale_type_existing_details'>" +
"				<tr>" +
"					<th style='width: 180px;'>Existing Account:</th>" +
"					<td>" +
"						<input type='text' id='sale_type_existing_id' style='width: 90%; margin: 0;' />" +
"						<div id='sale_type_existing_id_results' style='width: 90%; display: none;'>" +
"							<table style='width: 100%; margin: 0;'>" +
"								<tbody></tbody>" +
"							</table>" +
"						</div>" +
"					</td>" +
"				</tr>" +
"			</tbody>" +
"		</table>" +
"	</div>" +
"	<div style='text-align: center;'>" +
"		<button id='sale_type_popup_ok'>OK</button>" +
"		<button id='sale_type_popup_cancel'>Cancel</button>" +
"	</div>";

			oSaleTypePopup.setContent(sSalePopupContent);

			// Add available Sale Types
			var	oSaleTypeSelect	= oSaleTypePopup.contentPane.select('#sale_type_select').first();
			for (var i = 0, j = Sale.sale_type.ids.length; i < j; i++) {
				var	oOption	= document.createElement('option');
				oOption.value	= Sale.sale_type.ids[i];
				oOption.text	= Sale.sale_type.labels[i];

				oSaleTypeSelect.add(oOption, null);
			}

			// Select Event Handlers
			oSaleTypeSelect.observe('click', this.changeSaleType.bind(this));
			oSaleTypeSelect.observe('change', this.changeSaleType.bind(this));
			oSaleTypeSelect.observe('keyup', this.changeSaleType.bind(this));

			// Search Event Handlers
			var	oSaleTypeExistingId	= oSaleTypePopup.contentPane.select('#sale_type_existing_id').first();
			oSaleTypeExistingId.observe('change', this.existingCustomerSearch.bind(this));
			oSaleTypeExistingId.observe('keyup', this.existingCustomerSearch.bind(this));

			// Button Event Handlers
			oSaleTypePopup.submit = function(oSaleData, fnCallback) {
				var	oSaleTypeSelect		= this.contentPane.select('#sale_type_select').first();

				if (oSaleTypeSelect.selectedIndex >= 0) {
					oSaleData.sale_type_id = parseInt(oSaleTypeSelect.options[oSaleTypeSelect.selectedIndex].value, 10);
					switch (oSaleData.sale_type_id) {
						case SaleType.SALE_TYPE_EXISTING:
						case SaleType.SALE_TYPE_WINBACK:
							// Retrieve Account Details via AJAX
							var	fnGetAccountDetails	= SalesPortal.getRemoteFunction('Sale', 'getAccountDetails', this._submit.bind(this, oSaleData, fnCallback));
							fnGetAccountDetails($ID('sale_type_existing_id').value);
							break;

						case SaleType.SALE_TYPE_NEW:
						default:
							this._submit(oSaleData, fnCallback);
							break;
					}
				} else {
					alert("Please select a Sale Type");
				}
			};

			oSaleTypePopup._submit = function(oSaleData, fnCallback, oAccountDetails) {
				// If there are Account Details, merge them with the Sale object
				if (oAccountDetails) {
					// This should work fine, though a tad hacky
					oAccountDetails.sale_type_id = oSaleData.sale_type_id;
					oSaleData = oAccountDetails;
				}

				this.hide();
				fnCallback(oSaleData);
			};

			oSaleTypePopup.contentPane.select('#sale_type_popup_ok').first().observe('click', oSaleTypePopup.submit.bind(oSaleTypePopup, object, this.buildPageForObject.bind(this)));
			oSaleTypePopup.contentPane.select('#sale_type_popup_cancel').first().observe('click', (function () {
				window.location.href = $$('base').first().href;
				this.hide();
			}).bind(oSaleTypePopup));

			oSaleTypePopup.display();

			this.changeSaleType();

			// When the Popup is submitted, we'll rebuild the page, so just return for now
			return;
		} else {
			// set the data members and build the UI
			this.setObject(object);
			this.loadSaleAccount(object);
			this.loadContacts(object);
			this.loadItems(object);
			this.loadNotes(object);
			//this does the actual GUI building
			this.setContainers(this.detailsContainer);
		}
	},

	setObject : function (object) {
		if (object.id != null) {
			this.object.id = object.id;
		}
		if (object.sale_status_id != null) {
			this.object.sale_status_id = object.sale_status_id;
		}
		if (object.created_on != null) {
			this.object.created_on = object.created_on;
		}
		if (object.created_by != null) {
			this.object.created_by = object.created_by;
		}
		if (object.commission_paid_on != null) {
			this.object.commission_paid_on = object.commission_paid_on;
		}
		if (object.sale_type_id != null) {
			this.object.sale_type_id=object.sale_type_id;
		}
		//these are only set when retrieving a sale from the server
		if (object.status != null) {
			this.object.status = object.status;
		}
		if (object.status_description != null) {
			this.object.status_description = object.status_description;
		}
	},

	changeSaleType : function () {
		var	oSaleTypeSelect	= $ID('sale_type_select');
		switch (parseInt(oSaleTypeSelect.options[oSaleTypeSelect.selectedIndex].value, 10)) {
			case SaleType.SALE_TYPE_EXISTING:
			case SaleType.SALE_TYPE_WINBACK:
				$ID('sale_type_existing_details').show();
				break;

			case SaleType.SALE_TYPE_NEW:
			default:
				$ID('sale_type_existing_details').hide();
				break;
		}
	},

	existingCustomerSearch : function () {
		var	sSearchTerm	= $ID('sale_type_existing_id').value.strip();

		this.sLastSearchTerm = sSearchTerm;
		if (sSearchTerm && sSearchTerm.length > 3) {
			var	fnSearch = SalesPortal.getRemoteFunction('Sale', 'searchExistingCustomers', this._existingCustomerSearchResults.bind(this, sSearchTerm));
			fnSearch(sSearchTerm);
		} else {
			// Nothing to search, hide results
			$ID('sale_type_existing_id_results').hide();
		}
	},

	_existingCustomerSearchResults : function (sSearchTerm, aCustomers) {
		// We only want to render the most recent result
		if (this.sLastSearchTerm === sSearchTerm) {
			var	oResultsTableBody = $ID('sale_type_existing_id_results').select('tbody').first();
			oResultsTableBody.innerHTML	= '';
			for (var i = 0, j = aCustomers.length; i < j; i++) {
				var	oRow = document.createElement('tr');

				var	oAccountId = document.createElement('th'),
					oAccountName = document.createElement('td');

				oAccountId.innerHTML = aCustomers[i].account_id;
				oAccountName.innerHTML = aCustomers[i].account_name.escapeHTML();

				oRow.appendChild(oAccountId);
				oRow.appendChild(oAccountName);

				var	fnSelect	= this.existingCustomerSearchSelect.bind(this, aCustomers[i].account_id);
				oRow.observe('click', fnSelect);

				oResultsTableBody.appendChild(oRow);
			}
			$ID('sale_type_existing_id_results').show();
		}
	},

	existingCustomerSearchSelect : function (iAccountId) {
		$ID('sale_type_existing_id_results').hide();
		$ID('sale_type_existing_id').value = iAccountId;

		this.sLastSearchTerm = null;
	},

	// Validates the details client side and then submits the details to the server for validation
	submit : function () {
		window.scroll(0,0);
		if (this.updateFromGUI()) {
			document.body.className = document.body.originalClassName + " data-display";
			$ID('submit-button-panel').style.display = 'none';
			$ID('commit-button-panel').style.display = 'none';
			$ID('after-commit-button-panel').style.display = 'none';
			$ID('amend-button-panel').style.display = 'none';
			var submit = SalesPortal.getRemoteFunction('Sale', 'submit', this._submitOK.bind(this), this._submitError.bind(this));
			submit(this.createJSONObject());
		} else {
			alert("Please correct all errors (fields in red) and try again.");
		}
	},

	_submitOK : function () {
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'inline';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
		alert("Please check that all details are correct and then click 'Commit' to save or 'Edit' to make corrections.");
	},

	_submitError : function ($return) {
		window.scroll(0,0);
		document.body.className = document.body.originalClassName + " data-entry";
		$ID('submit-button-panel').style.display = 'inline';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
		alert($return['ERROR']);
	},

	// Confirms the sale and submits the details to the server to be saved
	commit : function () {
		window.scroll(0,0);
		document.body.className = document.body.originalClassName + " data-display";
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
		var submit = SalesPortal.getRemoteFunction('Sale', 'confirm', this._commitOK.bind(this), this._submitError.bind(this));
		submit(this.createJSONObject());
	},

	cancel : function () {
		window.scroll(0,0);
		document.body.className = document.body.originalClassName + " data-entry";
		$ID('submit-button-panel').style.display = 'inline';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
	},

	addNewSale : function () {
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sale');
	},

	amendSale : function () {
		return this.cancel();
	},

	cancelAmend : function () {
		// Must reload the page as the sale object may have been amended and we need to show the original version
		document.location.reload();
		return;
	},

	_commitOK : function ($saleId) {
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';
		alert("The sale has been saved. The reference number for this sale is " + $saleId + ".");
		if (this.isNewSale()) {
			document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
		} else {
			// reload the page, so that the correct options are shown (Hacky, I know, but screw you sir)
			document.location.reload();
		}
	},

	cancelSale : function () {
		var strReason = '';

		strReason = prompt("Are you sure you want to cancel this sale?  Please supply a reason for cancelling it.", "");

		if (strReason == null) {
			return;
		}

		this._remoteSaleFunctionCall('cancelSale', this._cancelOK, strReason);
	},

	_cancelOK : function () {
		window.scroll(0,0);
		alert("The sale has been cancelled.");
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},

	rejectSale : function () {
		var strReason = '';

		strReason = prompt("Are you sure you want to reject this sale?  Please supply a reason for rejecting it.", "");

		if (strReason == null) {
			return;
		}

		this._remoteSaleFunctionCall('rejectSale', this._rejectOK, strReason);
	},

	_rejectOK : function () {
		window.scroll(0,0);
		alert("The sale has been rejected.");
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},

	verifySale : function () {
		var strMsg = (self.canBeSetToAwaitingDispatch) ? "Are you sure you want to dispatch this sale?" : "Are you sure you want to verify this sale?";

		if (confirm(strMsg)) {
			this._remoteSaleFunctionCall('verifySale', this._verifyOK);
		}
	},

	_verifyOK : function () {
		var strMsg = (self.canBeSetToAwaitingDispatch) ? "The sale is awaiting dispatch." : "The sale has been verified.";

		window.scroll(0,0);
		alert(strMsg);
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},

	_remoteSaleFunctionCall : function ($remoteFunName, $okFunc /*, RemoteArg2, RemoteArg3, etc */) {
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		var remote = SalesPortal.getRemoteFunction('Sale', $remoteFunName, $okFunc.bind(this), this._processError.bind(this));

		// Add in variables (sale id and RemoteArg2, etc)

		// First variable is always the sale id
		remote = remote.curry(self.getInstance().getId());

		for (var i=2, j=arguments.length; i < j; i++) {
			remote = remote.curry(arguments[i]);
		}

		// Call the remote method
		remote();
	},

	_processError : function ($return) {
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';
		alert($return['ERROR']);
	},

	isNewSale : function () {
		return this.newSale;
	},

	buildGUI : function () {
		// The 'Verify' and the 'Awaiting Dispatch' actions are in the same function.  They probably shouldn't be
		var strVerifyButtonLabel = (self.canBeSetToAwaitingDispatch) ? "Dispatch Sale" : "Verify Sale";

		var buttons = (self.canAmendSale ? '&nbsp;<input class="sale-amend" type="button" value="Amend Sale">&nbsp;' : '') +
					(self.canCancelSale ? '&nbsp;<input class="sale-cancel" type="button" value="Cancel Sale">&nbsp;' : '') +
					(self.canRejectSale ? '&nbsp;<input class="sale-reject" type="button" value="Reject Sale">&nbsp;' : '') +
					(self.canVerifySale ? '&nbsp;<input class="sale-verify" type="button" value="'+ strVerifyButtonLabel +'">&nbsp;' : '');

		// Add contents to this.detailsContainer
		this.detailsContainer.innerHTML = '' +
		'<div class="Page">' +
/*			+ '<div class="FieldContent" align="right">' +
				'Sale Type:' +
				'<select name="SaleType">' +
					'<option value="New">New</option>'
			//		+ '<option value="Existing" DISABLED>Existing</option>'
			//		+ '<option value="Winback" DISABLED>Winback</option>' +
				'</select>' +
			'</div>' + */
		'</div>' +
		'<div class="MediumSpace"></div>' +
		'<div class="Title" style="position: relative;">Sale Status<input class="sale-viewhistory" type="button" value="View History" style="'+/*'width: 85px;'+*/' position: absolute; right: 0px; bottom: -1px;"/></div><div class="Page" style=""><table class="data-table" cellpadding="0" cellspacing="0" border="0" style="width: 100%;"><tr><td>Status:</td><td>' + this.getStatus() + '</td></tr><tr><td>Description:</td><td>' + this.getStatusDescription() + '</td></tr></table></div>' +
		'<div class="MediumSpace"></div>' +
		'<table cellpadding="0" cellspacing="0" border="0" width="975">' +
			'<tr>' +
				'<td width="480">' +
					'<div class="PartTitle">Account Details</div>' +
					'<div class="PartPage" id="account_details_holder"></div>' +
				'</td>' +
				'<td width="15"></td>' +
				'<td width="480">' +
					'<div class="PartTitle">Primary Contact Details</div>' +
					'<div class="PartPage" id="primary_contact_details_holder"></div>' +
				'</td>' +
			'</tr>' +
		'</table>' +
		'<div class="data-entry">' +
			'<div class="MediumSpace"></div>' +
			'<div class="Title">Available Products</div>' +
			'<div class="Page">' +
				'<div class="FieldContent">' +
					'<table cellpadding="0" cellspacing="0" border="0">' +
						'<tr>' +
							'<td>' +
								'<div id="divProductType">' +
									'<select name="product_type_id" id="sale_product_type_list">' +
										'<option value="">Product Type</option>' +
									'</select>' +
								'</div>' +
							'</td>' +
							'<td>' +
								'<div id="divProductList">' +
									'<select name="product_id" id="sale_product_list">' +
										'<option value="">Product</option>' +
									'</select>' +
								'</div>' +
							'</td>' +
							'<td><input class="sale-items-add" type="button" value="Add Item" /></td>' +
						'</tr>' +
					'</table>' +
				'</div>' +
			'</div>' +
		'</div>' +
		'<div class="MediumSpace"></div>' +
		'<div style="position: relative;" class="Title">Sale Items<input class="sale-items-collapse" type="button" value="Collapse All" style="'+/*'width: 80px;'+*/'position: absolute; right: 0px; bottom: -1px;" /></div>' +
		'<div class="Page">' +
			'<div class="FieldContent" style="padding:0; margin:0;">' +
				'<table id="sale-items-table" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 0; padding: 0; width:100%;">' +
				'</table>' +
			'</div>' +
		'</div>' +
		'<div class="MediumSpace"></div>' +
		'<div class="Title">Billing Details</div>' +
		'<div class="Page">' +
				'<div><table cellpadding="0" cellspacing="0" border="0">' +
					'<tr>' +
						'<td>' +
							'<table id="bill-delivery-type-table"></table>' +
						'</td>' +
						'<td' + (self.canAmendSale ? ' class="read-only"' : '') + '>' +
							'<table id="bill-payment-type-table"></table>' +
						'</td>' +
						'<td' + (self.canAmendSale ? ' class="read-only"' : '') + '>' +
							'<table id="direct-debit-type-table"></table>' +
						'</td>' +
					'</tr>' +
				'</table></div>' +
			'<table cellpadding="0" cellspacing="0" border="0" id="direct-debit-detail-table" class="data-table' + (self.canAmendSale ? ' read-only' : '') + '"></table>' +
		'</div>' +
		'<div class="MediumSpace"></div>' +
		'<div class="Title" style="position: relative;"><span>Sale Notes</span><div style="position: absolute; right: 0px; bottom: -1px;"><button class="data-entry sale-note-add">Add Note</button>&nbsp;<button id="sale-notes-collapse-all">Collapse All</button></div></div>' +
		'<div class="Page">' +
		'<div class="FieldContent" style="padding:0; margin:0;">' +
			'<table id="sale-notes-table" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 0; padding: 0; width:100%;">' +
			'</table>' +
		'</div>' +
		'</div>' +
		'<div class="MediumSpace"></div>' +
		'<span id="amend-button-panel" class="data-display">&nbsp;<input class="sale-addnew" type="button" value="Add New Sale">&nbsp;' + buttons + '</span>' +
		'<span id="submit-button-panel" class="data-entry"><input class="sale-submit" type="button" value="Submit">' +
		(self.canAmendSale ? '&nbsp;&nbsp;<input class="sale-revert" type="button" value="Cancel">' : '') +
		'</span>' +
		'<span id="commit-button-panel"><input type="button" class="sale-commit" value="Commit">&nbsp;&nbsp;<input class="sale-edit" type="button" value="Edit"></span>' +
		'<span id="after-commit-button-panel">&nbsp;<input class="sale-addnew" type="button" value="Add New Sale">&nbsp;' + buttons + '</span>';

		// WIP: This is for debug purposes only!!! Remove it before deployment!
		// + '<br/><br/><input type="button" value="Toggle Entry/Display" onclick="$ID(\'submit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'inline\' : \'none\'); $ID(\'commit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'none\' : \'inline\'); document.body.className = (document.body.className == \'data-display\' ? \'data-entry\' : \'data-display\')">';

		// Event handlers
		if (self.canAmendSale) {
			this.detailsContainer.select('.sale-amend')[0].observe('click', this.amendSale.bind(this));
		}
		if (self.canCancelSale) {
			this.detailsContainer.select('.sale-cancel')[0].observe('click', this.cancelSale.bind(this));
		}
		if (self.canRejectSale) {
			this.detailsContainer.select('.sale-reject')[0].observe('click', this.rejectSale.bind(this));
		}
		if (self.canVerifySale) {
			this.detailsContainer.select('.sale-verify')[0].observe('click', this.verifySale.bind(this));
		}
		this.detailsContainer.select('.sale-viewhistory')[0].observe('click', self.showHistory.bind(self, this.getId()));
		this.detailsContainer.select('.sale-items-add')[0].observe('click', this.addSaleItem.bind(this));
		this.detailsContainer.select('.sale-items-collapse')[0].observe('click', function () {
			if (this.value == 'Collapse All') {
				this.value = 'Expand All';
				SaleItem.collapseAll();
			} else {
				this.value = 'Collapse All';
				SaleItem.expandAll();
			}
		});
		this.detailsContainer.select('.sale-note-add')[0].observe('click', function () {
			Note.registerNote(new Note());
		});
		this.detailsContainer.select('#sale-notes-collapse-all')[0].observe('click', Note.toggleExpandedAll.bind(Note));
		this.detailsContainer.select('.sale-submit')[0].observe('click', this.submit.bind(this));
		this.detailsContainer.select('.sale-revert').each(function (oButton) {
			oButton.observe('click', this.cancelAmend.bind(this));
		}, this);
		this.detailsContainer.select('.sale-commit')[0].observe('click', this.commit.bind(this));
		// FIXME: This looks odd... simply copied the apparently incorrect functionality...
		this.detailsContainer.select('.sale-edit')[0].observe('click', this.cancel.bind(this));
		this.detailsContainer.select('.sale-addnew').each(function (oButton) {
			oButton.observe('click', this.addNewSale.bind(this));
		}, this);

		var startInEditMode = (this.object.id == null);

		$ID('submit-button-panel').style.display = !startInEditMode ? 'none' : 'inline';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = !startInEditMode ? 'inline' : 'none';

		// Build Detail Content
		var saleAccount = this.getSaleAccount();
		saleAccount.setContainers($ID('account_details_holder'));

		var contacts = this.getContacts();
		if (contacts.length === 0) {
			this.addContact();
		}
		contacts[0].setContainers($ID('primary_contact_details_holder'));

		for (var i = 0, l = this.saleItems.length; i < l; i++) {
			this.addSaleItem(this.saleItems[i]);
		}

		//this.loadNotes();
		Note.buildGUI();

		Event.observe($ID('sale_product_type_list'), 'change', this.changeProductType.bind(this), true);

		self.endLoading();
	},


	getStatus : function () {
		if (this.isNewSale()) return "New Sale";
		return this.object.status;
	},

	getStatusDescription : function () {
		if (this.isNewSale()) return "Details not yet submitted.";
		return this.object.status_description;
	},

	changeVendor : function () {
		this.populateProductTypes({ids: [], labels: []});

		//var vendorId = FW.GUIComponent.getValue(this.getSaleAccount().elementGroups.vendor);
		var vendorId = this.getSaleAccount().elementGroups.vendor.getValue();
		if (!vendorId || vendorId === 0 || vendorId === '' || vendorId == null) return;

		if (this.remote$listProductTypesForVendor == null) {
			var onSuccess = this.populateProductTypes.bind(this);
			this.remote$listProductTypesForVendor = SalesPortal.getRemoteFunction('Sale', 'listProductTypesForVendor', onSuccess);
		}
		this.remote$listProductTypesForVendor(vendorId);

		// Re-Validate
		this.getSaleAccount().elementGroups.vendor.isValid();
	},

	populateProductTypes : function (arrProductTypeIdName) {
		// Need to reset the products list
		this.populateProducts({ids: [], labels: []});

		this.populateSelect($ID('sale_product_type_list'), arrProductTypeIdName, 0, (arrProductTypeIdName.ids.length > 0) ? '[Select Product Type]' : '[No Products Types Available]');
	},

	changeProductType : function () {
		// Need to reset the products list
		this.populateProducts({ids: [], labels: []});

		var vendorId = this.getSaleAccount().elementGroups.vendor.getValue();
		if (!vendorId || vendorId == 0 || vendorId == '' || vendorId == null) return;

		var productTypeModule = $ID('sale_product_type_list').options[$ID('sale_product_type_list').selectedIndex].value;
		if (!productTypeModule || productTypeModule == 0 || productTypeModule == '' || productTypeModule == null) return;

		if (this.remote$listProductsForProductTypeModuleAndVendor == null) {
			var onSuccess = this.populateProducts.bind(this);
			this.remote$listProductsForProductTypeModuleAndVendor = SalesPortal.getRemoteFunction('Sale', 'listProductsForProductTypeModuleAndVendor', onSuccess);
		}
		this.remote$listProductsForProductTypeModuleAndVendor(productTypeModule, vendorId);
	},

	populateProducts : function (arrProductIdName) {
		this.populateSelect($ID('sale_product_list'), arrProductIdName, 0, (arrProductIdName.ids.length > 0) ? '[Select Product]' : '[No Products Available]')
	},

	populateSelect : function (select, arrIdName, noneValue, noneLabel) {
		select.innerHTML = '';
		var option = document.createElement('option');
		select.appendChild(option);
		option.value = noneValue;
		option.appendChild(document.createTextNode(noneLabel));
		for (var i = 0, l = arrIdName.ids.length; i < l; i++) {
			option = document.createElement('option');
			select.appendChild(option);
			option.value = arrIdName.ids[i];
			option.appendChild(document.createTextNode(arrIdName.labels[i]));
		}
	},

	addSaleItem : function (saleItem) {
		var newOne = (saleItem == null);
		
		var productType = null;
		var productTypeModule = null;
		var productName = null;
		var productId = null;

		if (newOne) {
			productTypeModule = $ID('sale_product_type_list').options[$ID('sale_product_type_list').selectedIndex].value;
			if (!productTypeModule || productTypeModule == 0 || productTypeModule == '' || productTypeModule == null) return alert('Please select a product type and product.');

			productId = $ID('sale_product_list').options[$ID('sale_product_list').selectedIndex].value;
			if (!productId || productId == 0 || productId == '' || productId == null) return alert('Please select a product.');

			productType = $ID('sale_product_type_list').options[$ID('sale_product_type_list').selectedIndex].textContent;
			productName = $ID('sale_product_list').options[$ID('sale_product_list').selectedIndex].textContent;
		} else {
			productTypeModule = saleItem.object.product_type_module;
			productId = saleItem.object.product_id;
			productType = saleItem.object.product_type;
			productName = saleItem.object.product_name;
		}

		var item = newOne ? this.addItem() : saleItem;
		item.setProduct(productTypeModule, productId, productType, productName);

		var saleItemTable = $ID('sale-items-table');

		var odd = (saleItemTable.rows.length % 4) === 0;

		var header = saleItemTable.insertRow(-1);
		header.style.backgroundColor = odd ? '#fff' : '#eaeaea';
		header.id = item.instanceId + '-header';
		var summary = header.insertCell(-1);
		summary.id = item.instanceId + '-summary';
		var controls = header.insertCell(-1);
		controls.width = '200px';
		controls.style.textAlign = 'right';
		controls.id = item.instanceId + '-controls';

		var remove = document.createElement('input');
		remove.type = 'button';
		remove.value = 'Remove';
		//remove.style.width = '80px';
		remove.className = "data-entry";
		var f = function() {
			if (confirm("Are you sure you want to remove this item?")) {
				self.getInstance().removeSaleItem(this.id);
			}
		};
		var func = f.bind({ id: item.instanceId });
		Event.observe(remove, 'click', func, true);
		controls.appendChild(remove);

		var expand = document.createElement('input');
		expand.type = 'button';
		expand.value = 'Collapse';
		expand.id = item.instanceId + '-expand';
		//expand.style.width = '80px';

		func = item.toggleExpanso.bind(item);
		Event.observe(expand, 'click', func, true);
		controls.appendChild(expand);

		var body = saleItemTable.insertRow(-1);
		body.id = item.instanceId + '-body';
		body.style.backgroundColor = odd ? '#fff' : '#eaeaea';
		var container = body.insertCell(-1);
		container.style.borderTop = '2px dashed ' + (odd ? '#eaeaea' : '#fff');
		container.colSpan = 2;
		container.id = item.instanceId + '-container';
		container.innerHTML = '[No configuration required]';

		item.setContainers(container, summary);
	},

	removeSaleItem : function (id) {
		var saleItem = SaleItem.getInstance(id);
		this.removeItem(saleItem);
		var header = $ID(id + '-header');
		var body = $ID(id + '-body');
		header.parentNode.removeChild(header);
		body.parentNode.removeChild(body);

		var saleItemTable = $ID('sale-items-table');
		var odd = true;
		for (var i = 0, l = saleItemTable.rows.length; i < l; i += 2) {
			saleItemTable.rows[i].style.backgroundColor = odd ? '#fff' : '#eaeaea';
			saleItemTable.rows[i+1].style.backgroundColor = odd ? '#fff' : '#eaeaea';
			saleItemTable.rows[i+1].cells[0].style.borderColor = odd ? '#eaeaea': '#fff';
			odd = !odd;
		}
	},

	updateFromGUI : function () {
		if (this.isValid()) {
			// And the child objects ...
			this.getSaleAccount().updateFromGUI();

			var $instances = this.getContacts();
			for (var $i = 0, $l = $instances.length; $i < $l; $i++) {
				$instances[$i].updateFromGUI();
			}

			$instances = this.getItems();
			for (var $i = 0, $l = $instances.length; $i < $l; $i++) {
				$instances[$i].updateFromGUI();
			}

			$instances = this.getNotes();
			for (var $i = 0, $l = $instances.length; $i < $l; $i++) {
				$instances[$i].updateFromGUI();
			}
		} else {
			return false;
		}
		return true;
	},

	isValid : function () {
		if(!this._super()) {
			return false;
		}
		
		// And the child objects ...
		if (!this.getSaleAccount().isValid()) return false;

		$instances = this.getContacts();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++) {
			if ($instances[$i] && !$instances[$i].isValid()) return false;
		}

		$instances = this.getItems();
		for ($i = 0, $l = $instances.length; $i < $l; $i++) {
			if ($instances[$i] && !$instances[$i].isValid()) return false;
		}

		$instances = this.getNotes();
		for ($i = 0, $l = $instances.length; $i < $l; $i++) {
			if (!$instances[$i].isValid) {
				alert($instances[$i].toSource());
			}
			if ($instances[$i] && !$instances[$i].isValid()) return false;
		}

		return true;
	},

	updateChildObjectsDisplay : function ($readOnly) {
		this.getSaleAccount().updateDisplay();

		$instances = this.getContacts();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++) {
			if ($instances[$i]) $instances[$i].updateDisplay();
		}

		$instances = this.getItems();
		for ($i = 0, $l = $instances.length; $i < $l; $i++) {
			if ($instances[$i]) $instances[$i].updateDisplay();
		}
	},

	showValidationTip : function () {
		// Validate the values and invoke the isValid method of child objects
		$isValid = true;

		// Validate all the fields ...

		// WIP

		if (!$isValid) return true;

		// And the child objects ...

		if (this.getSaleAccount().showValidationTip()) return true;

		$instances = this.getContacts();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++) {
			if ($instances[$i].showValidationTip()) return true;
		}

		$instances = this.getItems();
		for ($i = 0, $l = $instances.length; $i < $l; $i++) {
			if ($instances[$i].showValidationTip()) return true;
		}

		return false;
	},

	getId : function () {
		return this.object.id;
	},

	setSaleTypeId : function (sale_type_id) {
		this.object.sale_type_id = sale_type_id;
	},

	getSaleTypeId : function () {
		return this.object.sale_type_id;
	},

	setCreatedBy : function (created_by) {
		this.object.created_by = created_by;
	},

	getCreatedBy : function () {
		return this.object.created_by;
	},

	getSaleAccount : function () {
		return this.saleAccount;
	},

	loadSaleAccount : function (oObject) {
		if (typeof oObject.sale_account != 'undefined') {
			this.saleAccount = new SaleAccount(oObject.sale_account);
		} else {
			this.saleAccount = new SaleAccount();
		}
	},

	loadContacts : function (oObject) {
		if (typeof oObject.contacts != 'undefined') {
			for (var i = 0, l = oObject.contacts.length; i < l; i++) {
				this.contacts[this.contacts.length] = new Contact(oObject.contacts[i]);
			}
		}
	},

	addContact : function () {
		var contact = new Contact(null);
		this.contacts[this.contacts.length] = contact;
		return contact;
	},

	removeContact : function (instance) {
		for (var i = 0, l = this.contacts.length; i < l; i++) {
			if (this.contacts[i] == instance) {
				instance.destroy();
				//delete this.object.contacts[i];
				delete this.contacts[i];
			}
		}
	},

	getContacts : function () {
		return this.contacts;
	},

	loadItems : function (oObject) {
		if (typeof oObject.items != 'undefined') {
			for (var i = 0, l = oObject.items.length; i < l; i++) {
				this.saleItems[this.saleItems.length] = new SaleItem(oObject.items[i]);
			}
		}
	},

	addItem : function () {
		var instance = new SaleItem(null);
		this.saleItems[this.saleItems.length] = instance;
		return instance;
	},

	removeItem : function (instance) {
		Item.deregister(instance);
		
		for (var i = 0, l = this.saleItems.length; i < l; i++) {
			if (this.saleItems[i] == instance) {
				instance.destroy();
				this.saleItems.splice(i,1);
				return;
			}
		}
	},

	getItems : function () {
		return this.saleItems;
	},

	loadNotes : function (oObject) {
		var	aSaleNotes	= [];
		if (typeof oObject.notes != 'undefined') {
			for (var i = 0, l = oObject.notes.length; i < l; i++) {
				aSaleNotes.push(new Note(oObject.notes[i]));
			}
		}
		Note.registerNotes(aSaleNotes);
	},

	addNote : function () {
		Note.registerNote(new Note());
	},

	removeNote : function (instance) {
		Note.deleteNote(instance);
	},

	getNotes : function () {
		return Note.getNotesAsArray();
	},

	createJSONObject : function () {
		JSONObject.setSaleData(this.object.id, this.object.sale_status_id, this.object.created_on, this.object.created_by, this.object.commission_paid_on, this.object.sale_type_id);
		JSONObject.setSaleAccount(this.getSaleAccount());
		JSONObject.setSaleContacts(this.getContacts());
		JSONObject.setSaleItems(this.getItems());
		JSONObject.setSaleNotes(this.getNotes());
		return JSONObject.getInstance();
	},

	statics : {
		instance: null,
		canCreateSale: false,
		canCancelSale: false,
		canAmendSale: false,
		canVerifySale: false,
		canRejectSale: false,
		canBeSetToAwaitingDispatch: false,
		loading: null,
		historyPopup: null,

		getInstance : function () {
			return self.instance;
		},

		startLoading : function () {
			if (this.loading != null) return;
			this.loading = new Reflex_Popup.Loading();
			this.loading.display();
		},

		endLoading : function () {
			if (this.loading == null) return;
			this.loading.hide();
			this.loading = null;
		},

		hideHistory : function () {
			if (self.historyPopup != null) {
				self.historyPopup.hide();
			}
		},

		showHistory : function (saleId) {
			self.hideHistory();
			if (saleId == null) {
				return alert("This is a new sale. It has no history.");
			}
			var showHistory = SalesPortal.getRemoteFunction('Sale', 'history', self.onHistoryLoad.bind({saleId: saleId}));
			showHistory(saleId);
		},

		onHistoryLoad : function (historyHTML) {
			if (self.historyPopup == null) {
				self.historyPopup = new Reflex_Popup(72.2);
				self.historyPopup.addCloseButton();
			}
			self.historyPopup.setTitle("History of Sale " + this.saleId);
			self.historyPopup.setContent(historyHTML);
			self.historyPopup.display();
			self.historyPopup.recentre();
		}
	}
});

return self;