
var Popup_Email_Queue_Email_List = Class.create(Reflex_Popup, {
	
	initialize : function($super, iEmailQueueId) {
		$super(80);
		
		this._iEmailQueueId = iEmailQueueId;
		
		this._oLoading = new Reflex_Popup.Loading();
		this._oLoading.display();
		
		this._buildUI();
	},
	
	_buildUI : function() {
		var oDataset 	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, new Reflex_AJAX_Request('Email', 'getDataset'));
		var oPagination = new Pagination(null, Popup_Email_Queue_List.PAGE_SIZE, oDataset);
		var oFilter		= new Filter(oDataset, oPagination);
		
		// Default filter (email_queue_id)
		oFilter.addFilter('email_queue_id', {iType: Filter.FILTER_TYPE_VALUE});
		oFilter.setFilterValue('email_queue_id', this._iEmailQueueId, true);
		
		var oSort 		= new Sort(oDataset, oPagination, true);
		var oComponent 	= new Component_Dataset_AJAX_Table(
			{
				sTitle		: 'Emails for Queue #' + this._iEmailQueueId,
				oPagination	: oPagination,
				oFilter		: oFilter,
				oSort		: oSort,
				hFields		: {
				
					id : {
						sDisplayName	: '#',
						mSortField		: true,
						sSortDirection	: Sort.DIRECTION_ASC
					},
					
					recipients : {
						sDisplayName 	: 'Recipients',
						mSortField		: true
					},
					
					sender : {
						sDisplayName	: 'Sender',
						mSortField		: true
					},
					
					subject : {
						sDisplayName	: 'Subject',
						mSortField		: true
					},
					
					created_datetime : {
						sDisplayName	: 'Created On',
						mSortField		: true,
						oFilterConfig	: {
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
							fnGetDisplayText : this._getFilterValueDisplayText.bind(this, 'created_datetime')
						},	
						fnCreateCell : this._getCell.bind(this, 'created_datetime')
					},
					
					created_employee_id : {
						sDisplayName	: 'Created By',
						mSortField		: true,
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
					
					email_status_id : {
						sDisplayName	: 'Status',
						mSortField		: true,
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition : {
									fnPopulate : Flex.Constant.getConstantGroupOptions.curry('email_status')
								}
							},
							fnGetDisplayText : this._getFilterValueDisplayText.bind(this, 'email_status_id')
						},
						fnCreateCell : this._getCell.bind(this, 'email_status_id')
					},
					
					actions : {
						sDisplayName	: '',
						fnCreateCell	: this._getCell.bind(this, 'actions')
					}
				},
				aRequiredConstantGroups	: ['email_status'],
				fnOnReady				: this._tableReady.bind(this),
				sExtraClass				: 'popup-email-queue-email-list'
			}
		);
		
		this.setTitle(oComponent.get('sTitle'));
		this.addCloseButton();
		this.setContent(oComponent.getNode());
		
		
		
		this._oComponent	= oComponent;
		this._oFilter 		= oFilter;
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
			case 'email_status_id':
				return aControls[0].getElementText();
				break;
		}
	},
	
	_getCell : function(sFieldName, oData) {
		switch (sFieldName) {
			case 'created_datetime':
				return $T.td(Popup_Email_Queue_Email_List._getDateTimeElement(oData[sFieldName]));
			
			case 'created_employee_id':
			case 'email_status_id':
				var sNameField = sFieldName.replace(/_id/, '_name');
				return $T.td(oData[sNameField]);
			
			case 'actions':
				return $T.td(
					$T.img({class: 'pointer', src: '../admin/img/template/magnifier.png', alt: 'Preview', title: 'Preview', onclick: this._previewEmail.bind(this, oData.id)})
				);
		}
	},
	
	_previewEmail : function(iEmailId) {
		var oLoading = new Reflex_Popup.Loading();
		oLoading.display();
		var oPopup = new Component_Email_Queue_Email_Preview.createAsPopup(
			{
				iEmailId	: iEmailId,
				fnOnReady	: function() {
					oPopup.display();
					oLoading.hide();
				}
			}
		);
	}
});

Object.extend(Popup_Email_Queue_Email_List, {
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
})