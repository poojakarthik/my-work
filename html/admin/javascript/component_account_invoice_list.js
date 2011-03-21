
var Component_Account_Invoice_List = Class.create(
{
	initialize	: function(oContainerDiv, iAccountId, iPageSize)
	{
		this._register();
		
		this._oContainerDiv	= oContainerDiv;
		this._iAccountId	= iAccountId;
		
		this._iPageSize					= iPageSize;
		this._oElement					= $T.div({class: 'component-account-invoice-list'});
		this._oOverlay 					= new Reflex_Loading_Overlay();
		this._bAccountHasOCAReferral	= false;
		this._oPermissions				= null;
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Account_Invoice_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},

	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	getSection : function()
	{
		return this._oSection;
	},
	
	refresh : function()
	{
		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
		this._showLoading(true);
	},
	
	deregister : function()
	{
		var iIndex = Component_Account_Invoice_List._aInstances.indexOf(this);
		Component_Account_Invoice_List._aInstances.splice(iIndex, 1);
	},
	
	// Protected
	
	_register : function()
	{
		Component_Account_Invoice_List._aInstances.push(this);
	},
	
	_buildUI : function(oPermissions, bAccountHasOCAReferral, iInterimInvoiceType)
	{
		if (!oPermissions)
		{
			Component_Account_Invoice_List._getUserPermissions(this._buildUI.bind(this));
			return;
		}
		
		if (Object.isUndefined(bAccountHasOCAReferral))
		{
			Component_Account_Invoice_List._hasAccountGotOCAReferral(this._iAccountId, this._buildUI.bind(this, oPermissions));
			return;
		}
		
		if (Object.isUndefined(iInterimInvoiceType))
		{
			Component_Account_Invoice_List._getAllowedInterimInvoiceType(this._iAccountId, this._buildUI.bind(this, oPermissions, bAccountHasOCAReferral));
			return;
		}
		
		this._oPermissions 				= oPermissions;
		this._bAccountHasOCAReferral	= bAccountHasOCAReferral;
		this._iInterimInvoiceType		= iInterimInvoiceType;
		
		// Create Dataset & pagination object
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Account_Invoice_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									(this._iPageSize ? this._iPageSize : Component_Account_Invoice_List.MAX_RECORDS_PER_PAGE), 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Account_Invoice_List.FILTER_FIELDS)
		{
			if (Component_Account_Invoice_List.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Account_Invoice_List.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Account_Invoice_List.DATA_SET_DEFINITION);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.div({class: 'component-account-invoice-list-title'},
				$T.img({src: Component_Account_Invoice_List.ICON_IMAGE_SOURCE, alt: 'Invoices', title: 'Invoices'}),
				$T.span('Invoices')
			),
			true
		);
		
		// Redistribute balances button (god users only)
		if (this._oPermissions.bUserIsGod)
		{
			oSection.addToHeaderOptions(
				$T.button({class: 'icon-button'},
					$T.span('Redistribute Account Balance')	
				).observe('click', this._redistributeAccountBalance.bind(this, null))
			);
		}
		
		// Add in button to Generate a Final/Interim Invoice
		if (this._iInterimInvoiceType && this._oPermissions.bUserHasInterimPerm)
		{
			oSection.addToHeaderOptions(
				$T.button({class: 'icon-button'},
					$T.img({src: '../admin/img/template/new.png'}),
					$T.span('Generate ' + $CONSTANT_GROUP.invoice_run_type[this._iInterimInvoiceType].Description)	
				).observe('click', this._generateInterimInvoice.bind(this))
			);
		}
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-account-invoice-list-table reflex highlight-rows listing-fw3'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-account-invoice-list-headerrow'},
						$T.th(''),	// Invoice Run Type/Status
						$T.th('Date'),
						$T.th('Invoice #'),
						$T.th('Due On'),
						$T.th({class: 'component-account-invoice-list-currency'},
							'New Charges'
						),
						$T.th({class: 'component-account-invoice-list-currency'},
							'Amount Owing'
						),
						$T.th(''), // Actions
						$T.th(''),
						$T.th(''),
						$T.th(''),
						$T.th('')
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-account-invoice-list-options'},
				$T.li(
					$T.button({class: 'component-account-invoice-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-account-invoice-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-account-invoice-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-account-invoice-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);
		
		this._oTBody = this._oElement.select('table > tbody').first();

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-account-invoice-list-paginationbutton');
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aBottomPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));

		// Setup pagination button object
		this.oPaginationButtons = {
			oBottom	: {
				oFirstPage		: aBottomPageButtons[0],
				oPreviousPage	: aBottomPageButtons[1],
				oNextPage		: aBottomPageButtons[2],
				oLastPage		: aBottomPageButtons[3]
			}
		};

		this._oFilter.addFilter('account_id', {iType: Filter.FILTER_TYPE_VALUE});
		this._oFilter.setFilterValue('account_id', this._iAccountId);
		this._oSort.registerField('created_on', Sort.DIRECTION_DESC);
		
		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
		}
		
		this.refresh();
	},

	_showLoading	: function(bShow)
	{
		if (bShow)
		{
			this._oOverlay.attachTo(this._oElement.select('table > tbody').first());
		}
		else
		{
			this._oOverlay.detach();
		}
	},

	// _changePage: Executes the given function (name) on the dataset pagination object.
	_changePage	: function(sFunction)
	{
		this._showLoading(true);
		this.oPagination[sFunction]();
	},

	// _updateTable: Page load callback from the dataset pagination object.
	_updateTable	: function(oResultSet)
	{
		// Remove all existing rows
		while (this._oTBody.firstChild)
		{
			// Remove event handlers from the action buttons
			var oEditButton = this._oTBody.firstChild.select('img').first();

			if (oEditButton)
			{
				oEditButton.stopObserving();
			}

			// Remove the row
			this._oTBody.firstChild.remove();
		}
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			this._oTBody.appendChild(this._createNoRecordsRow());
		}
		else
		{
			// Add the rows
			var aData	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			var iCount	= 0;
			for (var i in aData)
			{
				iCount++;
				this._createTableRow(aData[i], parseInt(i), oResultSet.intTotalResults);
			}
		}

		this._updatePagination();
		
		this._showLoading(false);
	},

	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'no-rows', colspan: 9},
				(bOnLoad ? 'Loading...' : 'No invoices to display')
			)
		);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		var bIsSample 				= (oData.invoice_run_status_id == $CONSTANT.INVOICE_RUN_STATUS_TEMPORARY && oData.invoice_status == $CONSTANT.INVOICE_TEMP);
		var bUserHasViewPerm 		= this._oPermissions.bUserHasViewPerm;
		var bUserHasExternalPerm 	= this._oPermissions.bUserHasExternalPerm;
		var bUserHasOperatorPerm 	= this._oPermissions.bUserHasOperatorPerm;
		var bUserHasInterimPerm 	= this._oPermissions.bUserHasInterimPerm;
		
		if (oData.id && (bUserHasViewPerm || bUserHasExternalPerm))
		{
			var oTypeIcon			= Component_Account_Invoice_List._getInvoiceRunTypeIcon(oData.invoice_run_type_id);
			var oStatusIcon			= $T.img({src: Component_Account_Invoice_List.TEMP_INVOICE_IMAGE_SOURCE, alt: 'Temporary Invoice', title: 'Temporary Invoice'});
			var oStatusAndTypeTD 	= 	$T.td({class: 'component-account-invoice-list-icon-cell-double'},
											oTypeIcon,
											oStatusIcon
										);
			
			// Hide temp status icon if invoice isn't temp 
			if (oData.invoice_status !== $CONSTANT.INVOICE_TEMP)
			{
				oStatusIcon.toggle();
			}
			
			var oPdfTD 			= $T.td({class: 'component-account-invoice-list-icon-cell'});
			var oEmailTD		= $T.td({class: 'component-account-invoice-list-icon-cell'});
			var oViewInvoiceTD	= $T.td({class: 'component-account-invoice-list-icon-cell'});
			var oExportCSVTD	= $T.td({class: 'component-account-invoice-list-icon-cell'});
			var oRerateTD		= $T.td({class: 'component-account-invoice-list-icon-cell'});
			
			var oDate 	= Date.$parseDate(oData.created_on, 'Y-m-d').shift(-1, 'months');
			var iYear 	= oDate.getFullYear();
			var iMonth	= oDate.getMonth();
			
			if ((bUserHasExternalPerm || bUserHasViewPerm) && oData.pdf_exists)
			{
				oPdfTD.appendChild(
					$T.a({href: 'reflex.php/Invoice/PDF/' + oData.id + '/?Account=' + oData.account_id + '&Invoice_Run_Id=' + oData.invoice_run_id + '&Year=' + iYear + '&Month=' + iMonth},
						$T.img({src: Component_Account_Invoice_List.PDF_IMAGE_SOURCE, title: 'View PDF Invoice', alt: 'View PDF Invoice'})
					)
				);
				
				// Build "Email invoice pdf" link, if the user has OPERATOR or OPERATOR_EXTERNAL privileges
				if (!bIsSample)
				{
					if (bUserHasOperatorPerm || bUserHasExternalPerm)
					{
						oEmailTD.appendChild(
							$T.img({class: 'pointer', src: Component_Account_Invoice_List.EMAIL_IMAGE_SOURCE, title: 'Email PDF Invoice', alt: 'Email PDF Invoice'}).observe('click', this._emailInvoicePDF.bind(this, oData, iYear, iMonth))
						);
					}
				}
			}
			
			if (!this._bAccountHasOCAReferral && bIsSample && bUserHasInterimPerm && (oData.invoice_run_type_id == $CONSTANT.INVOICE_RUN_TYPE_INTERIM || oData.invoice_run_type_id == $CONSTANT.INVOICE_RUN_TYPE_FINAL || oData.invoice_run_type_id == $CONSTANT.INVOICE_RUN_TYPE_INTERIM_FIRST))
			{
				// If this is an Temporary Interim/Final Invoice and has sufficient privileges, replace the Email button with a Commit button
				var sCommitType = '';
				switch (oData.invoice_run_type_id)
				{
					case $CONSTANT.INVOICE_RUN_TYPE_INTERIM:
						sCommitType	= 'Interim';
						break;
					case $CONSTANT.INVOICE_RUN_TYPE_INTERIM_FIRST:
						sCommitType	= 'Interim First';
						break;
					case $CONSTANT.INVOICE_RUN_TYPE_FINAL:
						sCommitType	= 'Final';
						break;
				}
				
				var oCommitIcon = $T.img({class: 'pointer', src: Component_Account_Invoice_List.COMMIT_IMAGE_SOURCE, title: 'Approve ' + sCommitType + ' Invoice', alt: 'Approve ' + sCommitType + ' Invoice'});
				oCommitIcon.observe('click', this._commitInvoice.bind(this, oData));
				
				var oRejectIcon = $T.img({class: 'pointer', src: Component_Account_Invoice_List.REVOKE_IMAGE_SOURCE, title: 'Reject ' + sCommitType + ' Invoice', alt: 'Reject ' + sCommitType + ' Invoice'});
				oRejectIcon.observe('click', this._rejectInvoice.bind(this, oData));
				
				oEmailTD =	$T.td({class: 'component-account-invoice-list-icon-cell-double'},
								oCommitIcon,
								oRejectIcon
							);
			}
			
			if (bUserHasViewPerm && oData.has_unarchived_cdrs)
			{
				// Build the "View Invoice Details" link
				var oViewInvoiceIcon = $T.img({class: 'pointer', src: Component_Account_Invoice_List.ICON_IMAGE_SOURCE, alt: 'View Invoice Details', title: 'View Invoice Details'});
				oViewInvoiceIcon.observe('click', this._viewInvoice.bind(this, oData.id));
				oViewInvoiceTD.appendChild(oViewInvoiceIcon);
				
				// Build the "Export Invoice as CSV" link
				oExportCSVTD.appendChild(
					$T.a({href: 'flex.php/Invoice/ExportAsCSV/?Invoice.Id=' + oData.id},
						$T.img({src: Component_Account_Invoice_List.EXPORT_IMAGE_SOURCE, alt: 'Export as CSV', title: 'Export as CSV'})
					)
				);
			}
			
			// Rerating link
			if (oData.has_unarchived_cdrs)
			{
				var oRerateIcon = $T.img({class: 'pointer', src: Component_Account_Invoice_List.RERATE_IMAGE_SOURCE, alt: 'Rerate Invoice', title: 'Rerate Invoice'});
				oRerateIcon.observe('click', this._rerateInvoice.bind(this, oData));
				oRerateTD.appendChild(oRerateIcon);
			}
			
			var	oTR	=	$T.tr(
							oStatusAndTypeTD,
							$T.td(Date.$parseDate(oData.created_on, 'Y-m-d').$format('d-m-Y')),
							$T.td(oData.id),
							$T.td(Date.$parseDate(oData.due_on, 'Y-m-d').$format('d-m-Y')),
							$T.td({class: 'component-account-invoice-list-currency Currency'},
								'$' + new Number(oData.new_charges).toFixed(2)
							),
							$T.td({class: 'component-account-invoice-list-currency Currency'},
								'$' + new Number(oData.amount_owing).toFixed(2)
							),
							oPdfTD,
							oEmailTD,
							oViewInvoiceTD,
							oExportCSVTD,
							oRerateTD
						);
		}
		else
		{
			// Invalid, return empty row
			oTR = $T.tr();
		}
		
		this._oTBody.appendChild(oTR);
	},

	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oLastPage.disabled 		= true;

		if (iPageCount == undefined)
		{
			// Get the page count
			this.oPagination.getPageCount(this._updatePagination.bind(this));
		}
		else
		{
			if (this.oPagination.intCurrentPage != Pagination.PAGE_FIRST)
			{
				// Enable the first and previous buttons
				this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
				this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oLastPage.disabled 	= false;
			}
		}
	},
	
	_generateInterimInvoice : function()
	{
		Flex.Invoice.getPreGenerateValues(this._iAccountId);
	},
	
	_emailInvoicePDF : function(oData, iYear, iMonth)
	{
		Vixen.Popup.ShowAjaxPopup(
			'EmailPDFInvoicePopupId', 
			'medium', 
			'Email Invoice PDF',
			'Invoice', 
			'EmailPDFInvoice', 
			{
				Account :
				{
					Id : oData.account_id
				},
				Invoice :
				{
					Id 				: oData.id,
					invoice_run_id	: oData.invoice_run_id,
					Year			: iYear,
					Month			: iMonth
				}
			}
		)
	},
	
	_commitInvoice : function(oData)
	{
		Flex.Invoice.commitInterimInvoiceConfirm(
			oData.id, 
			(oData.invoice_run_type_id == $CONSTANT.INVOICE_RUN_TYPE_INTERIM_FIRST)
		);
	},
	
	_rejectInvoice : function(oData)
	{
		Flex.Invoice.revokeInterimInvoiceConfirm(oData.id);
	},
	
	_rerateInvoice : function(oData)
	{
		JsAutoLoader.loadScript(
			'javascript/plan.js',
			function()
			{
				new Popup_Invoice_Rerate(oData.id);
			}
		);
	},
	
	_viewInvoice : function(iInvoiceId)
	{
		new Popup_Invoice_View(iInvoiceId);
	},
	
	_redistributeAccountBalance : function(oResponse, oEvent)
	{
		if (!oResponse)
		{
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			
			var fnResp	= this._redistributeAccountBalance.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'redistributeBalance');
			fnReq(this._iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Account_Invoice_List._ajaxError(oResponse);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		// Update various components
		if (Component_Account_Payment_List)
		{
			Component_Account_Payment_List.refreshInstances();
		}
		
		if (Component_Account_Adjustment_List)
		{
			Component_Account_Adjustment_List.refreshInstances();
		}
		
		if (Component_Account_Collections)
		{
			Component_Account_Collections.refreshInstances();
		}
		
		if (Component_Account_Invoice_List)
		{
			Component_Account_Invoice_List.refreshInstances();
		}
		
		if (Vixen.AccountDetails)
		{
			Vixen.AccountDetails.CancelEdit();
		}
	}
});

// Static

Object.extend(Component_Account_Invoice_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Invoice', sMethod: 'getDatasetForAccount'},
	MAX_RECORDS_PER_PAGE		: 10,
	ICON_IMAGE_SOURCE			: '../admin/img/template/invoice.png',
	TEMP_INVOICE_IMAGE_SOURCE	: '../admin/img/template/temp_invoice.png',
	PDF_IMAGE_SOURCE			: '../admin/img/template/pdf_small.png',
	EMAIL_IMAGE_SOURCE			: '../admin/img/template/email.png',
	COMMIT_IMAGE_SOURCE			: '../admin/img/template/invoice_commit.png',
	REVOKE_IMAGE_SOURCE			: '../admin/img/template/invoice_revoke.png',
	EXPORT_IMAGE_SOURCE			: '../admin/img/template/export.png',
	RERATE_IMAGE_SOURCE			: '../admin/img/template/rerate.png',
	REQUIRED_CONSTANT_GROUPS	: ['InvoiceStatus', 'invoice_run_type', 'invoice_run_status'],

	_aInstances	: [],
	
	refreshInstances : function()
	{
		for (var i = 0; i < Component_Account_Invoice_List._aInstances.length; i++)
		{
			if (Component_Account_Invoice_List._aInstances[i] instanceof Component_Account_Invoice_List)
			{
				Component_Account_Invoice_List._aInstances[i].refresh();
			}
		}
	},
	
	_ajaxError : function(oResponse, sMessage)
	{
		var sMessage = (sMessage ? sMessage + '. ' : '') + (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error', sDebugContent: oResponse.sDebug});
	},
	
	_getInvoiceRunTypeIcon : function(iInvoiceRunTypeId)
	{
		var sFilename 	= 'MsgError';
		var sAlt		= 'Unknown Invoice Run Type';
		switch (iInvoiceRunTypeId)
		{
			case $CONSTANT.INVOICE_RUN_TYPE_LIVE:
				sFilename 	= 'invoice_run_production_run';
				sAlt		= 'Production';
				break;
			case $CONSTANT.INVOICE_RUN_TYPE_SAMPLES:
				sFilename 	= 'invoice_run_sample';
				sAlt		= 'Sample';
				break;
			case $CONSTANT.INVOICE_RUN_TYPE_INTERNAL_SAMPLES:
				sFilename 	= 'invoice_run_sample';
				sAlt		= 'Internal Sample';
				break;
			case $CONSTANT.INVOICE_RUN_TYPE_INTERIM:
				sFilename 	= 'invoice_run_interim';
				sAlt		= 'Interim';
				break;
			case $CONSTANT.INVOICE_RUN_TYPE_FINAL:
				sFilename 	= 'invoice_run_final';
				sAlt		= 'Final';
				break;
			case $CONSTANT.INVOICE_RUN_TYPE_INTERIM_FIRST:
				sFilename 	= 'invoice_run_interim_first';
				sAlt		= 'Interim First';
				break;
				
		}
		return $T.img({src: '../admin/img/template/' + sFilename + '.png', alt: sAlt, title: sAlt});
	},
	
	_getUserPermissions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Component_Account_Invoice_List._getUserPermissions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Invoice', 'getInvoiceListPermissions');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Account_Invoice_List._ajaxError(oResponse, 'Unable to determine permissions for the logged in Employee');
			return;
		}
		
		fnCallback(oResponse.oPermissions);
	},
	
	_hasAccountGotOCAReferral : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Component_Account_Invoice_List._hasAccountGotOCAReferral.curry(iAccountId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'hasAccountGotOCAReferral');
			fnReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Account_Invoice_List._ajaxError(oResponse, 'Unable to determine if Account has an oca referral outstanding');
			return;
		}
		
		fnCallback(oResponse.bAccountHasOCAReferral);
	},
	
	_getAllowedInterimInvoiceType : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Component_Account_Invoice_List._getAllowedInterimInvoiceType.curry(iAccountId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'getAllowedInterimInvoiceType');
			fnReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Account_Invoice_List._ajaxError(oResponse, 'Unable to determine the allowable Interim Invoice type for the Account');
			return;
		}
		
		fnCallback(oResponse.iInterimInvoiceRunType);
	}
});

