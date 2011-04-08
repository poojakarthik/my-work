
Component_Account_Activity_Log = Class.create(/* extends */Reflex_Component, {

	initialize : function ($super) {
		// Additional Configuration
		this.CONFIG = Object.extend({
			'iAccountId'	: {}
		}, this.CONFIG || {});

		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this._iStartDate	= null;
		this._iNumRows		= null;
		this._sNowDate		= new Date().$format('Y-m-d');
		this._oListTooltip	= new Component_List_Tooltip(null, Component_List_Tooltip.POSITION_RIGHT);
		
		this.NODE.addClassName('component-account-activity-log');
	},

	// Public
	
	hideTitle : function()
	{
		this.NODE.select('.component-section header > h3').first().hide();
	},
	
	// Protected
	
	_load : function (oResponse) {
		if (!oResponse || oResponse.element) {
			// No Response (or Response is an Event): Request Data
			var fnResp	= this._load.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'getActivityLog');
			fnReq(this.get('iAccountId'));
		} else if (!oResponse.bSuccess) {
			// Error
			Reflex_Popup.alert(oResponse.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.sDebug
			});
		} else {
			// Success
			this._aData = oResponse.aActivityData;
			Flex.Constant.loadConstantGroup(Component_Account_Activity_Log.REQUIRED_CONSTANT_GROUPS, this._syncUI.bind(this));
		}
	},

	_buildUI : function () {
		this.NODE = $T.div(
			new Component_Section({sTitle: 'Account Activity Log'},
				$T.table({class: 'reflex highlight-rows'},
					$T.thead(
						$T.th({class: 'component-account-activity-log-date'},
							'Date'
						),
						$T.th({class: 'component-account-activity-log-invoice'},
							'Invoice'
						),
						$T.th({class: 'component-account-activity-log-payment'},
							'Payment'
						),
						$T.th({class: 'component-account-activity-log-promise'},
							'Promise'
						),
						$T.th({class: 'component-account-activity-log-adjustment'},
							'Adjustment'
						),
						$T.th({class: 'component-account-activity-log-collection'},
							'Collection'
						),
						$T.th({class: 'component-account-activity-log-historicbalance'},
							''
						)
					)
				),
				$T.div({class: 'component-account-activity-log-list-container'},
					$T.table({class: 'reflex highlight-rows'},
						$T.tbody({class: 'alternating component-account-activity-log-list'})	
					)
				)
			)
		);
		
		var oComponentSection = this.NODE.select('.component-section').first().oReflexComponent;
		oComponentSection.getAttachmentNode('header-actions').appendChild(
			$T.div({class: 'component-account-activity-log-list-navigation'},
				// Num rows
				$T.span('Show: '),
				$T.select({class: 'component-account-activity-log-list-navigation-showmore'},
					$T.option({value: Component_Account_Activity_Log.VIEW_SIZE_WEEK},
						'1 week'	
					),
					$T.option({value: Component_Account_Activity_Log.VIEW_SIZE_2_WEEK},
						'2 weeks'
					),
					$T.option({value: Component_Account_Activity_Log.VIEW_SIZE_4_WEEK},
						'4 weeks'
					),
					$T.option({value: Component_Account_Activity_Log.VIEW_SIZE_6_MONTH},
						'6 months (approx.)'
					),
					$T.option({value: Component_Account_Activity_Log.VIEW_SIZE_1_YEAR},
						'1 year (approx.)'
					)
				),
				$T.button({class: 'icon-button'},
					$T.span('Refresh')
				).observe('click', this._refreshViewSize.bind(this)),
				// Shift buttons
				$T.button({class: 'icon-button'},
					$T.span('Previous '),
					$T.span({class: 'component-account-activity-log-list-navigation-navperiod'})
				).observe('click', this._shiftUp.bind(this)),
				$T.button({class: 'icon-button'},
					$T.span('Next '),
					$T.span({class: 'component-account-activity-log-list-navigation-navperiod'})
				).observe('click', this._shiftDown.bind(this)),
				// Find date
				$T.span('Find Date (dd/mm/yyyy): '),
				$T.input({class: 'component-account-activity-log-list-navigation-finddate', type: 'text'}).observe('keydown', this._findDateKeyDown.bind(this)),
				$T.button({class: 'icon-button'},
					$T.span('Find')	
				).observe('click', this._findDate.bind(this))
			)
		);
	},

	_syncUI	: function () {
		if (!this._aData) {
			// Need to load data first
			this._load();
		} else {
			// Default state
			this._sStartDate = this._sNowDate;
			this.NODE.select('.component-account-activity-log-list-navigation-showmore').first().value = 14;
			this._refreshViewSize();
			
			// Component is ready
			this._onReady();
		}
	},
	
	_disableNavButtons : function() {
		// Disable nav buttons
		var aButtons = this.NODE.select('.component-account-activity-log-list-navigation > .icon-button');
		for (var i = 0; i < aButtons.length; i++) {
			aButtons[i].disabled = true;
		}
	},
	
	_enableNavButtons : function() {
		// Re-enable nav buttons
		var aButtons = this.NODE.select('.component-account-activity-log-list-navigation > .icon-button');
		for (var i = 0; i < aButtons.length; i++) {
			aButtons[i].disabled = false;
		}
		
		// Update the navigation period on the shift buttons
		var sPeriod = this._iNumRows + ' days';
		switch (this._iNumRows) {
			case Component_Account_Activity_Log.VIEW_SIZE_WEEK:
				sPeriod = 'Week';
				break;
			case Component_Account_Activity_Log.VIEW_SIZE_2_WEEK:
				sPeriod = '2 Weeks';
				break;
			case Component_Account_Activity_Log.VIEW_SIZE_4_WEEK:
				sPeriod = '4 Weeks';
				break;
			case Component_Account_Activity_Log.VIEW_SIZE_6_MONTH:
				sPeriod = '6 months';
				break;
			case Component_Account_Activity_Log.VIEW_SIZE_1_YEAR:
				sPeriod = 'Year';
				break;
		}
		var aNavPeriodSpans = this.NODE.select('.component-account-activity-log-list-navigation-navperiod');
		for (var i = 0; i < aNavPeriodSpans.length; i++) {
			aNavPeriodSpans[i].innerHTML = sPeriod;
		}
	},
	
	_findDate : function() {
		if (this._oQueue && !this._oQueue.hasFinished()) {
			return;
		}
		
		var sDate = this.NODE.select('.component-account-activity-log-list-navigation-finddate').first().value;
		var oDate = Date.$parseDate(sDate, 'd/m/Y');
		if (oDate)
		{
			this._sStartDate = oDate.$format('Y-m-d');
			this._showSet();
		}
	},
	
	_findDateKeyDown : function(oEvent)
	{
		if (oEvent.keyCode == Event.KEY_RETURN)
		{
			this._findDate();
		}
	},
	
	_refreshViewSize : function() {
		if (this._oQueue && !this._oQueue.hasFinished()) {
			return;
		}
		
		this._iNumRows = parseInt(this.NODE.select('.component-account-activity-log-list-navigation-showmore').first().value);
		this._showSet();
	},
	
	_shiftUp : function() {
		if (this._oQueue && !this._oQueue.hasFinished()) {
			return;
		}
		
		var iRowCount 		= this.NODE.select('.component-account-activity-log-list').first().childNodes.length;
		this._sStartDate	= Date.$parseDate(this._sStartDate, 'Y-m-d').shift(-iRowCount, 'days').$format('Y-m-d');
		this._showSet();
	},
	
	_shiftDown : function() {
		if (this._oQueue && !this._oQueue.hasFinished()) {
			return;
		}
		
		var iRowCount 		= this.NODE.select('.component-account-activity-log-list').first().childNodes.length
		this._sStartDate	= Date.$parseDate(this._sStartDate, 'Y-m-d').shift(iRowCount, 'days').$format('Y-m-d');
		this._showSet();
	},
	
	_showSet : function() {
		this._oQueue = $Q();
		
		// Check if the start date is past the last date
		var sProposedEndDate = Date.$parseDate(this._sStartDate, 'Y-m-d').shift(this._iNumRows, 'days').$format('Y-m-d');
		if (sProposedEndDate > this._sNowDate) {
			// Set the start date to be {num rows - 1} before the last date
			this._sStartDate = Date.$parseDate(this._sNowDate, 'Y-m-d').shift(-1 * (this._iNumRows - 1), 'days').$format('Y-m-d');
		}
		
		// Get up to the start date
		var sPreviousDate 	= null;
		var bFoundDate		= false;
		for (var i = 0, j = this._aData.length; i < j; i++) {
			var sDate = this._aData[i].sDate;
			if (sDate > this._sStartDate) {
				// Gone past the date, set the previous date so that the missing days are filled in
				sPreviousDate = this._sStartDate;
				break;
			} else if (sDate == this._sStartDate) {
				// Found the date
				break;
			}
		}
		
		if (sPreviousDate === null) {
			// Set it to the start date, there is no data to show, will happen if showing the last bit of data (up to today)
			sPreviousDate = this._sStartDate;
		}
		
		// Generate the correct amount of rows from the start date
		var iRowCount = 0;
		while (iRowCount < this._iNumRows) {
			var oData 	= this._aData[i];
			var sDate	= (oData ? oData.sDate : this._sNowDate);
			if (sPreviousDate !== null) {
				// Fill in days from the previous date
				var oDate = Date.$parseDate(sPreviousDate, 'Y-m-d');
				if (iRowCount > 0) {
					// Shift the previous date if there are rows, because it must be the previous date of an already added record
					oDate.shift(1, 'days');
				}
				
				while (oDate.$format('Y-m-d') != sDate) {
					this._oQueue.push(
						function(oData) {
							this._buildItemUI(oData, true);
						}.bind(this, {sDate: oDate.$format('Y-m-d')})
					);
					
					iRowCount++;
					
					// Make sure we haven't exceed the row limit
					if (iRowCount >= this._iNumRows) {
						break;
					}
					
					oDate.shift(1, 'days');
				}
			}

			// Make sure we haven't exceed the row limit
			if (iRowCount >= this._iNumRows) {
				break;
			}
			
			if (oData) {
				// Add data row
				this._oQueue.push(
					function(oData) {
						this._buildItemUI(oData);
					}.bind(this, oData, false)
				);
				
				sPreviousDate = oData.sDate;
				iRowCount++;
				i++;
			}
			else {
				// No more data, add a date only record and then exit
				this._oQueue.push(
					function(oData) {
						this._buildItemUI(oData, true);
					}.bind(this, {sDate: sDate})
				);
				break;
			}
		}
		
		// Clear the table
		this._disableNavButtons();
		this.NODE.select('.component-account-activity-log-list').first().innerHTML = '';
		this._oListTooltip.clearRegisteredRows();
		
		// Start the queue
		this._oQueue.push(this._enableNavButtons.bind(this));
		this._oQueue.execute.bind(this._oQueue).defer();
	},
	
	_buildItemUI : function(oItem, bInBetween) {
		var oTBody = this.NODE.select('.component-account-activity-log-list').first();
		
		var oInvoiceTD 		= $T.td({class: 'component-account-activity-log-invoice'}),
			oPaymentTD		= $T.td({class: 'component-account-activity-log-payment'}),
			oPromiseTD		= $T.td({class: 'component-account-activity-log-promise'}),
			oAdjustmentTD	= $T.td({class: 'component-account-activity-log-adjustment'}),
			oCollectionTD	= $T.td({class: 'component-account-activity-log-collection'});
		
		if (!bInBetween) {
			for (var i = 0; i < oItem.aInvoices.length; i++) {
				var oInvoice = oItem.aInvoices[i];
				
				// Determine the invoice run type icon
				var sFilename 	= null;
				var sAlt		= null;
				switch (oInvoice.invoice_run_type_id) {
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
				
				var sIcon = (sFilename ? '../admin/img/template/' + sFilename + '.png' : null); 
				oInvoiceTD.appendChild(
					this._getCurrencyElement(
						parseFloat(oInvoice.Total) + parseFloat(oInvoice.Tax), 
						{
							'Id'			: oInvoice.Id,
							'Created On'	: Date.$parseDate(oInvoice.CreatedOn, 'Y-m-d').$format('d/m/Y'),
							'Due On'		: Date.$parseDate(oInvoice.DueOn, 'Y-m-d').$format('d/m/Y'),
							'New Charges'	: new Number(parseFloat(oInvoice.Total) + parseFloat(oInvoice.Tax)).toFixed(2),
							'Amount Owing'	: new Number(oInvoice.collectable_balance).toFixed(2)
						},
						sIcon,
						sAlt,
						'component-account-activity-log-item-invoice'
					)
				);
			}
			
			for (var i = 0; i < oItem.aPayments.length; i++) {
				var oPayment = oItem.aPayments[i];
				oPaymentTD.appendChild(
					this._getCurrencyElement(
						oPayment.proper_amount,
						{
							'Id'				: oPayment.id,
							'Type'				: (oPayment.payment_type_id ? Flex.Constant.arrConstantGroups.payment_type[oPayment.payment_type_id].Name : 'N/A'),
							'Amount Applied'	: new Number(Math.abs(oPayment.proper_amount - oPayment.proper_balance)).toFixed(2),
							'Balance'			: new Number(Math.abs(oPayment.proper_balance)).toFixed(2),
							'Reversal Reason'	: (oPayment.reversal_reason ?  oPayment.reversal_reason : 'N/A')
						}, 
						(oPayment.reversed_payment_id ? Component_Account_Activity_Log.REVERSED_IMAGE_SOURCE : ''), 
						(oPayment.reversed_payment_id ? 'Reversal' : ''), 
						'component-account-activity-log-item-payment' + ((oPayment.is_reversed || oPayment.reversed_payment_id) ? ' component-account-activity-log-semitransparent' : '')
					)
				);
			}
			
			for (var i = 0; i < oItem.aCreatedPromises.length; i++) {
				var oPromise = oItem.aCreatedPromises[i];
				oPromiseTD.appendChild(
					this._getItemElement(
						'Created',
						'component-account-activity-log-item-promise',
						{
							'Id' 		: oPromise.id,
							'Reason'	: oPromise.reason
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aCompletedPromises.length; i++) {
				var oPromise = oItem.aCompletedPromises[i];
				oPromiseTD.appendChild(
					this._getItemElement(
						'Completed', 
						'component-account-activity-log-item-promise',
						{
							'Id' 				: oPromise.id,
							'Reason'			: oPromise.reason,
							'Created On'		: Date.$parseDate(oPromise.created_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A'),
							'Completion Type'	: Flex.Constant.arrConstantGroups.collection_promise_completion[oPromise.collection_promise_completion_id].Name
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aDuePromiseInstalments.length; i++) {
				var oInstalment = oItem.aDuePromiseInstalments[i];
				oPromiseTD.appendChild(
						this._getItemElement(
						$T.div(
							$T.span('Instalment Due: '),
							$T.span({class: 'component-account-activity-log-currency'},
								'$' + new Number(oInstalment.amount).toFixed(2)
							)
						),
						'component-account-activity-log-item-promiseinstalment' + (!oInstalment.within_promise_range ? ' component-account-activity-log-semitransparent' : ''),
						{
							'Promise Id' : oInstalment.collection_promise_id
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aAdjustments.length; i++) {
				var oAdjustment = oItem.aAdjustments[i];
				oAdjustmentTD.appendChild(
					this._getCurrencyElement(
						oAdjustment.proper_amount, 
						{
							'Id'				: oAdjustment.id,
							'Requested By'		: oAdjustment.created_employee_name,
							'Approved By'		: oAdjustment.reviewed_employee_name,
							'Status'			: Flex.Constant.arrConstantGroups.adjustment_status[oAdjustment.adjustment_status_id].Name,
							'Reversal Reason'	: (oAdjustment.reversal_reason ? oAdjustment.reversal_reason : 'N/A')
						}, 
						(oAdjustment.reversed_adjustment_id ? Component_Account_Activity_Log.REVERSED_IMAGE_SOURCE : ''), 
						(oAdjustment.reversed_adjustment_id ? 'Reversal' : ''),
						'component-account-activity-log-item-adjustment' + ((oAdjustment.is_reversed || oAdjustment.reversed_adjustment_id) ? ' component-account-activity-log-semitransparent' : '')
					)
				);
			}
			
			for (var i = 0; i < oItem.aCollectionEvents.length; i++) {
				var oEvent = oItem.aCollectionEvents[i];
				oCollectionTD.appendChild(
					this._getItemElement(
						oEvent.collection_event_name, 
						'component-account-activity-log-item-collectionevent',
						{
							'Scheduled On' 	: Date.$parseDate(oEvent.scheduled_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A'),
							'Completed On' 	: Date.$parseDate(oEvent.completed_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A'),
							'Completed By' 	: oEvent.completed_employee_name,
							'Invocation'	: Flex.Constant.arrConstantGroups.collection_event_invocation[oEvent.collection_event_invocation_id].Name
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aStartedCollectionSuspensions.length; i++) {
				var oSuspension = oItem.aStartedCollectionSuspensions[i];
				oCollectionTD.appendChild(
					this._getItemElement(
						'Suspension Start', 
						'component-account-activity-log-item-collectionsuspension',
						{
							'Id'			: oSuspension.id,
							'Started By'	: oSuspension.start_employee_name,
							'Start Reason'	: oSuspension.reason
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aProposedCompleteCollectionSuspensions.length; i++) {
				var oSuspension = oItem.aProposedCompleteCollectionSuspensions[i];
				oCollectionTD.appendChild(
					this._getItemElement(
						'Suspension Proposed End', 
						'component-account-activity-log-item-collectionsuspension' + (oSuspension.completed_datetime !== null ? ' component-account-activity-log-semitransparent' : ''),
						{
							'Id'			: oSuspension.id,
							'Started By'	: oSuspension.start_employee_name,
							'Start Reason'	: oSuspension.reason,
							'Started On'	: Date.$parseDate(oSuspension.start_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A')
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aCompletedCollectionSuspensions.length; i++) {
				var oSuspension = oItem.aCompletedCollectionSuspensions[i];
				oCollectionTD.appendChild(
					this._getItemElement(
						'Suspension Completed', 
						'component-account-activity-log-item-collectionsuspension',
						{
							'Id'			: oSuspension.id,
							'Started By'	: oSuspension.start_employee_name,
							'Start Reason'	: oSuspension.reason,
							'Ended By'		: oSuspension.end_employee_name,
							'End Reason'	: oSuspension.end_reason,
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aScenarioChangeStart.length; i++) {
				var oScenarioChange = oItem.aScenarioChangeStart[i];
				oCollectionTD.appendChild(
					this._getItemElement(
						'Scenario Change Start', 
						'component-account-activity-log-item-collectionscenario',
						{
							'Scenario' : oScenarioChange.collection_scenario_name
						}
					)
				);
			}
			
			for (var i = 0; i < oItem.aScenarioChangeEnd.length; i++) {
				var oScenarioChange = oItem.aScenarioChangeStart[i];
				oCollectionTD.appendChild(
					this._getItemElement(
						'Scenario Change End', 
						'component-account-activity-log-item-collectionscenario',
						{
							'Scenario' : oScenarioChange.collection_scenario_name
						}
					)
				);
			}
		}
		
		var	oTR = $T.tr({class: 'component-account-activity-log-row-' + oItem.sDate},
			$T.td({class: 'component-account-activity-log-date'},
				Date.$parseDate(oItem.sDate, 'Y-m-d').$format('d/m/Y')
			),
			oInvoiceTD,
			oPaymentTD,
			oPromiseTD,
			oAdjustmentTD,
			oCollectionTD,
			$T.td({class: 'component-account-activity-log-historicbalance component-account-activity-log-currency'})
		);
		
		oTR.observe('click', this._calculateHistoricBalance.bind(this, oItem.sDate));
		
		if (bInBetween)
		{
			oTR.addClassName('component-account-activity-log-row-inbetween-minimised');
		}
		
		oTBody.appendChild(oTR);
	},
	
	_calculateHistoricBalance : function(sDate, oResponse) {
		if (!oResponse || oResponse.element) {
			// No Response (or Response is an Event): Request Data
			var fnResp	= this._calculateHistoricBalance.bind(this, sDate);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'getHistoricBalance');
			fnReq(this.get('iAccountId'), sDate);
		} else if (!oResponse.bSuccess) {
			// Error
			Reflex_Popup.alert(oResponse.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.sDebug
			});
		} else {
			// Success
			var oTR		= this.NODE.select('tr.component-account-activity-log-row-' + sDate).first();
			var oCell 	= oTR.select('.component-account-activity-log-historicbalance').first();
			oCell.innerHTML = '$' + new Number(oResponse.fBalance).toFixed(2);
			
			if (oTR.hasClassName('component-account-activity-log-row-inbetween-minimised'))
			{
				oTR.removeClassName('component-account-activity-log-row-inbetween-minimised');
			}
		}
	},
	
	_getItemElement : function(mValue, sExtraClass, hExtraInfo)
	{
		var oDiv = $T.div({class: 'component-account-activity-log-item' + (sExtraClass ? ' ' + sExtraClass : '')},
			mValue
		);
		
		if (hExtraInfo)
		{
			this._oListTooltip.registerRow(oDiv, hExtraInfo);
		}
		return oDiv;
	},

	_getCurrencyElement : function(mValue, hExtraInfo, sIconSrc, sIconAlt, sExtraClass)
	{
		var fValue 		= new Number(mValue);
		var fAbsValue	= Math.abs(fValue);
		var sValue		= '$' + fAbsValue.toFixed(2);
		if (fValue < 0)
		{
			sValue += ' CR';
		}
		
		return this._getItemElement(
			$T.div(
				sIconSrc ? $T.img({class: 'component-account-activity-log-currency-icon', src: sIconSrc, alt: sIconAlt, title: sIconAlt}) : null,
				sValue
			),
			'component-account-activity-log-currency' + (sExtraClass ? ' ' + sExtraClass : ''), 
			hExtraInfo
		);
	}
});

Component_Account_Activity_Log.createAsPopup = function() {
	var	oComponent	= Component_Account_Activity_Log.constructApply($A(arguments)),
		oPopup		= new Reflex_Popup(75);
	oComponent.hideTitle();
	
	oPopup.setTitle('Account Activity Log');
	oPopup.addCloseButton();
	oPopup.setContent(oComponent.getNode());

	return oPopup;
};

Object.extend(Component_Account_Activity_Log, 
{
	REQUIRED_CONSTANT_GROUPS : ['invoice_run_type', 
	                            'payment_type', 
	                            'adjustment_status',
	                            'collection_promise_completion',
	                            'collection_event_invocation'],
	
	REVERSED_IMAGE_SOURCE : '../admin/img/template/arrow_revert.png',
	
	VIEW_SIZE_WEEK		: 7,
	VIEW_SIZE_2_WEEK	: 14,
	VIEW_SIZE_4_WEEK	: 28,
	VIEW_SIZE_6_MONTH	: 180,
	VIEW_SIZE_1_YEAR	: 365
});