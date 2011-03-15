
var Component_Account_Charges = Class.create(
{
	initialize	: function(oContainerDiv, iAccountId)
	{
		this._oContainerDiv	= oContainerDiv;
		this._iAccountId	= iAccountId;
		
		this._hFilters	= {};
		this._oOverlay 	= new Reflex_Loading_Overlay();
		this._oElement	= $T.div({class: 'component-account-charges'});
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Account_Charges.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
	
	// Protected
	
	_buildUI : function()
	{
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
		}
		
		// Title
		oSection.setTitleContent(
			$T.div({class: 'component-account-charges-title'},
				$T.img({src: Component_Account_Charges.ICON_IMAGE_SOURCE, alt: 'Charges', title: 'Charges'}),
				$T.span('Charges')
			),
			true
		);
		
		// Header options
		this._oRequestButton =	$T.button({class: 'icon-button'},
									$T.img({src: '../admin/img/template/new.png'}),
									$T.span('Request Charge')	
								).observe('click', this._requestCharge.bind(this));
		oSection.addToHeaderOptions(this._oRequestButton);
		
		// Main content -- tab group
		this._oSingleChargeList 	= new Component_Account_Charge_List(null, this._iAccountId, this._updatePagination.bind(this));
		this._oRecurringChargeList 	= new Component_Account_Recurring_Charge_List(null, this._iAccountId, this._updatePagination.bind(this));
		
		this._oTabGroup 	= new Control_Tab_Group(oSection.getContentElement(), true, this._tabChange.bind(this));
		this._oTabGroup.addTab(
			'single',
			new Control_Tab(Component_Account_Charges.TAB_CHARGE, this._oSingleChargeList.getElement())
		);
		this._oTabGroup.addTab(
			'recurring',
			new Control_Tab(Component_Account_Charges.TAB_RECURRING_CHARGE, this._oRecurringChargeList.getElement())
		);
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-account-charges-options'},
				$T.li(
					$T.button({class: 'component-account-charges-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-account-charges-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-account-charges-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-account-charges-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-account-charges-paginationbutton');
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
	},

	_tabChange : function(oTab)
	{
		this._sSelectedTabName = oTab.getName();
		
		var sButtonText = 'Error!';
		switch (this._sSelectedTabName)
		{
			case Component_Account_Charges.TAB_CHARGE:
				sButtonText = 'Request Charge';
				this._oSingleChargeList.refresh();
				break;
			case Component_Account_Charges.TAB_RECURRING_CHARGE:
				sButtonText = 'Request Recurring Charge';
				this._oRecurringChargeList.refresh();
				break;
		}
		this._oRequestButton.select('span').first().innerHTML = sButtonText;
	},
	
	// _changePage: Executes the given function (name) on the dataset pagination object.
	_changePage	: function(sFunction)
	{
		this._getVisibleList().changePage(sFunction);
	},

	_getVisibleList : function()
	{
		switch (this._sSelectedTabName)
		{
			case Component_Account_Charges.TAB_CHARGE:
				return this._oSingleChargeList;
				break;
			case Component_Account_Charges.TAB_RECURRING_CHARGE:
				return this._oRecurringChargeList;
				break;
		}
	},
	
	_updatePagination : function(iCurrentPage, iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oLastPage.disabled 		= true;
		
		if (iCurrentPage != Pagination.PAGE_FIRST)
		{
			// Enable the first and previous buttons
			this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
			this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
		}
		if (iCurrentPage < (iPageCount - 1) && iPageCount)
		{
			// Enable the next and last buttons
			this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
			this.oPaginationButtons.oBottom.oLastPage.disabled 	= false;
		}
	},
	
	_requestCharge : function()
	{
		// TODO: CR137 - for recurring charges
		//"AddRecurringChargePopupId\", \"medium\", \"Request Recurring Charge\", \"Charge\", \"AddRecurring\", $strJsonCode"
		
		var sExtraName = '';
		switch (this._sSelectedTabName)
		{
			case Component_Account_Charges.TAB_RECURRING_CHARGE:
				sExtraName = 'Recurring';
				break;
		}
		
		Vixen.Popup.ShowAjaxPopup(
			'Add' + sExtraName + 'ChargePopupId',
			'medium', 
			'Request ' + sExtraName + 'Charge', 
			'Charge',
			'Add' + sExtraName,
			{
				'Account': {'Id': this._iAccountId}
			}
		);
	}
});

// Static

Object.extend(Component_Account_Charges,
{
	REQUIRED_CONSTANT_GROUPS	: [],
	MAX_RECORDS_PER_PAGE		: 20,
	ICON_IMAGE_SOURCE			: '../admin/img/template/payment.png',
	
	TAB_CHARGE 				: 'Charge',
	TAB_RECURRING_CHARGE 	: 'Recurring',
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	}
});
