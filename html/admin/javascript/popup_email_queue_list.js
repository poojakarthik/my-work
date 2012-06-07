
var Popup_Email_Queue_List = Class.create(Reflex_Popup, {
	
	initialize : function($super) {
		$super(80);
		
		this._oLoading = new Reflex_Popup.Loading();
		this._oLoading.display();
		
		this._buildUI();
	},
	
	_buildUI : function() {
		var oDataset 	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, new Reflex_AJAX_Request('Email_Queue', 'getDataset'));
		var oPagination = new Pagination(null, Popup_Email_Queue_List.PAGE_SIZE, oDataset);
		var oFilter		= new Filter(oDataset, oPagination);
		var oSort 		= new Sort(oDataset, oPagination, true);
		var oComponent 	= new Component_Dataset_AJAX_Table(
			{
				sTitle		: 'Email Queues',
				sIcon		: '/admin/img/template/email.png',
				oPagination	: oPagination,
				oFilter		: oFilter,
				oSort		: oSort,
				hFields		: {
				
					id : {
						sDisplayName	: '#',
						mSortField		: true,
						sSortDirection	: Sort.DIRECTION_ASC
					},
					
					description : {
						sDisplayName 	: 'Description',
						mSortField		: true
					},
					
					scheduled_datetime : {
						sDisplayName	: 'Scheduled On',
						mSortField		: true,
						oFilterConfig	: this._generateDatePickerFilterConfig('scheduled_datetime'),
						fnCreateCell 	: this._getCell.bind(this, 'scheduled_datetime')
					},
					
					delivered_datetime : {
						sDisplayName	: 'Delivered On',
						mSortField		: true,
						oFilterConfig	: this._generateDatePickerFilterConfig('delivered_datetime'),
						fnCreateCell 	: this._getCell.bind(this, 'delivered_datetime')
					},
					
					created_datetime : {
						sDisplayName	: 'Created On',
						mSortField		: true,
						oFilterConfig	: this._generateDatePickerFilterConfig('created_datetime'),
						fnCreateCell 	: this._getCell.bind(this, 'created_datetime')
					},
					
					created_employee_id : {
						sDisplayName	: 'Created By',
						mSortField		: 'created_employee_name',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition : {
									fnPopulate : Employee.getAllAsSelectOptions
								}
							},
							fnGetDisplayText : this._getFilterValueDisplayText.bind(this, 'created_employee_id')
						},	
						fnCreateCell : this._getCell.bind(this, 'created_employee_id')
					},
					
					email_queue_status_id : {
						sDisplayName	: 'Status',
						mSortField		: 'email_queue_status_name',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition	: {
									fnPopulate 	: Flex.Constant.getConstantGroupOptions.curry('email_queue_status')
								}
							},
							fnGetDisplayText : this._getFilterValueDisplayText.bind(this, 'email_queue_status_id')
						},	
						fnCreateCell : this._getCell.bind(this, 'email_queue_status_id')
					},
					
					actions : {
						sDisplayName	: '',
						fnCreateCell	: this._getCell.bind(this, 'actions')
					}
				},
				aRequiredConstantGroups	: ['email_queue_status'],
				fnOnReady				: this._tableReady.bind(this),
				sExtraClass				: 'popup-email-queue-list'
			}
		);
		
		this.setTitle(oComponent.get('sTitle'));
		this.addCloseButton();
		this.setContent(oComponent.getNode());
		
		this._oComponent	= oComponent;
		this._oFilter 		= oFilter;
	},
	
	_generateDatePickerFilterConfig : function(sFieldName) {
		return {
			iType			: Filter.FILTER_TYPE_RANGE,
			bFrom			: true,
			sFrom			: 'Start Date',
			bTo				: true,
			sTo				: 'End Date',
			sFromOption		: 'On Or After',
			sToOption		: 'On Or Before',
			sBetweenOption	: 'Between',
			oOption			: {
				sType		: 'date-picker',
				oDefinition	: {
					sDateFormat	: 'Y-m-d H:i:s',
					iYearStart	: Popup_Email_Queue_List.DATE_PICKER_START_YEAR,
					iYearEnd	: Popup_Email_Queue_List.DATE_PICKER_END_YEAR,
					bTimePicker	: true
				}
			},
			fnGetDisplayText : this._getFilterValueDisplayText.bind(this, sFieldName)
		}
	},
	
	_tableReady : function() {
		this._oLoading.hide();
		delete this._oLoading;
		this.display();
	},
	
	_getFilterValueDisplayText : function(sFieldName) {
		var aControls = $A(arguments);
		aControls.shift();
		
		switch (sFieldName) {
			case 'scheduled_datetime':
			case 'delivered_datetime':
			case 'created_datetime':
				var mValue			= this._oFilter.getFilterValue(sFieldName);
				var oFilterState	= this._oFilter.getFilterState(sFieldName);
				var sFrom			= (mValue.mFrom ? Date.$parseDate(mValue.mFrom, 'Y-m-d H:i:s').$format('d/m/y g:i A') : null);
				var sTo				= (mValue.mTo ? Date.$parseDate(mValue.mTo, 'Y-m-d H:i:s').$format('d/m/y g:i A') : null);
				switch (parseInt(oFilterState.oRangeTypeSelect.value)) {
					case Filter.RANGE_TYPE_FROM:
						sValue	= ['On Or After', sFrom].join(' ');
						break;
					case Filter.RANGE_TYPE_TO:
						sValue	= ['On Or Before', sTo].join(' ');
						break;
					case Filter.RANGE_TYPE_BETWEEN:
						sValue	= ['Between', sFrom, 'and', sTo].join(' ');
						break;
				}
				return sValue;
				break;
				
			case 'created_employee_id':
			case 'email_queue_status_id':
				return aControls[0].getElementText();
				break;
		}
	},
	
	_getCell : function(sFieldName, oData) {
		switch (sFieldName) {
			case 'scheduled_datetime':
			case 'delivered_datetime':
			case 'created_datetime':
				return $T.td(Popup_Email_Queue_List._getDateTimeElement(oData[sFieldName]));

			case 'created_employee_id':
			case 'email_queue_status_id':
				var sNameField = sFieldName.replace(/_id/, '_name');
				return $T.td(oData[sNameField]);
			
			case 'actions':
				var bShowDelete = (oData.email_queue_status_id == $CONSTANT.EMAIL_QUEUE_STATUS_SCHEDULED);
				return $T.td(
					bShowDelete ? $T.img({class: 'pointer', src: '../admin/img/template/delete.png', alt: 'Cancel Delivery', title: 'Cancel Delivery', onclick: this._cancelQueueDelivery.bind(this, oData.id, false)}) : null,
					$T.img({class: 'pointer', src: '../admin/img/template/magnifier.png', alt: 'View Emails', title: 'View Emails', onclick: this._viewQueueEmails.bind(this, oData.id)})
				);
		}
	},
	
	_cancelQueueDelivery : function(iQueueId, bConfirmed, oResponse) {
		if (!bConfirmed) {
			Reflex_Popup.yesNoCancel(
				'Are you sure you want to cancel the delivery of this Email Queue?', 
				{fnOnYes: this._cancelQueueDelivery.bind(this, iQueueId, true)}
			);
			return;
		}
		
		if (!oResponse || !Object.isUndefined(oResponse.target)) {
			// Request
			var oReq 		= new Reflex_AJAX_Request('Email_Queue', 'cancelQueueDelivery', this._cancelQueueDelivery.bind(this, iQueueId, bConfirmed));
			this._oLoading 	= new Reflex_Popup.Loading();
			this._oLoading.display();
			oReq.send(iQueueId);
		} else {
			this._oLoading.hide();
			delete this._oLoading;
			
			if (oResponse.hasException()) {
				// Error
				var oException = oResponse.getException();
				Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
					sTitle			: 'Database Error',
					sDebugContent	: oResponse.getDebugLog()
				});
				return;
			}
			
			// Success
			this._oComponent.refresh();
		}
	},
	
	_viewQueueEmails : function(iQueueId) {
		new Popup_Email_Queue_Email_List(iQueueId);
	}
});

Object.extend(Popup_Email_Queue_List, {
	DATE_PICKER_START_YEAR	: 2010,
	DATE_PICKER_END_YEAR	: new Date().getFullYear(),
	PAGE_SIZE 				: 10,
	
	_getDateTimeElement : function(sDatetime) {
		if (!sDatetime) {
			return $T.div('');
		}
	
		var oDate = Date.$parseDate(sDatetime, 'Y-m-d H:i:s');
		return $T.div(
			$T.div(oDate.$format('d/m/Y')),
			$T.div({class: 'datetime-time'},
					oDate.$format('h:i A')
			)
		);
	}
});