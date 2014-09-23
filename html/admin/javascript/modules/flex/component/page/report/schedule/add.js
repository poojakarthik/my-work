"use strict";
var     H               = require('fw/dom/factory'), // HTML
        Class           = require('fw/class'),
        Component       = require('fw/component'),
        Alert			= require('fw/component/popup/alert'),
        XHRRequest      = require('fw/xhrrequest'),
		jhr				= require('xhr/json-handler'),
        Popup           = require('fw/component/popup'),
        Form            = require('fw/component/form'),
        Number			= require('fw/component/control/number'),
        Hidden			= require('fw/component/control/hidden'),
        Text			= require('fw/component/control/text'),
        Select			= require('fw/component/control/select'),
        Radio			= require('fw/component/control/radio'),
        Datetime		= require('fw/component/control/datetime'),
        Checkbox		= require('fw/component/control/checkbox'),
		Textarea		= require('fw/component/control/textarea'),
        jsonForm = require('json-form');

var     self = new Class({

        'extends' : Component,
        _iScheduleCount : 0,
        _aFrequencyTypes : [],

        construct : function() {
			this.CONFIG = Object.extend({
				iReportId : {},
				aReportSchedule : {}
			}, this.CONFIG || {});
			// Call the parent constructor
			this._super.apply(this, arguments);
			// Class specific to our component
			this.NODE.addClassName('flex-page-report-schedule-add');
		},

		_buildUI : function() {
			this._oForm = new Form({onsubmit: this._save.bind(this, null)},
				new Hidden({
					sName : 'id',
					mValue: this.get('iReportId')
				}),
				new Hidden({
					sName : 'selectedDeliveryEmployees'
				}),
				H.fieldset({'class': 'flex-page-report-schedule-add-details'},
					H.label('Frequency Type'),
					this._oReportFrequencyType = new Select({
						sName		: 'report_frequency_type_id',
						sLabel		: 'Frequency Type',
						mMandatory	: true,
						fnPopulate	: this._populateFrequencyTypes.bind(this)
					}),
					H.label('Frequency Multiple'),
					new Number({
						sName		: 'frequency_multiple',
						sLabel		: 'Frequency Multiple',
						mMandatory	: true,
						fnValidate	: function(oControl) {
							if(oControl.getValue().length>256) {
								throw new Error("Max length is 256 characters");
							}
							return true;
						}
					}),
					H.label('Start Datetime'),
					new Datetime({
						bTimePicker	: true,
						sName		: 'schedule_datetime',
						sLabel		: 'Schedule Datetime',
						mMandatory	: true
					}),
					H.label('End DateTime'),
					new Datetime({
						bTimePicker	: true,
						sName		: 'schedule_end_datetime',
						sLabel		: 'Schedule End Datetime',
						mMandatory	: false
					}),
					H.label('Filename'),
					new	Text({
						sExtraClass	: 'flex-page-report-schedule-add-details-filename',
						sName		: 'filename',
						sLabel		: 'Filename',
						mMandatory	: false,
						fnValidate	: function(oControl) {
							if(oControl.getValue().length>100) {
								throw new Error("Max length is 100 characters");
							}
							return true;
						}
					}),
					this._oConstraintContainer = H.div(),
					H.label('Delivery Format'),
					this._oDeliveryFormatContainer = H.div({class: 'flex-page-report-schedule-add-details-deliveryformat-label'}),
					H.label('Delivery Method'),
					this._oDeliveryMethodContainer = H.div({class: 'flex-page-report-schedule-add-details-deliverymethod-label'}),
					this._oDeliveryEmployeeContainer = H.div({class: 'flex-page-report-schedule-add-details-deliveryEmployeeContainer',style: 'display: none'},
						H.label('Delivery Employee'),
						this._oEmployeeContainer = H.div({style: 'max-height: 150px; max-width: 200px; overflow-y: scroll; overflow-x: hidden;'})
					),
					H.fieldset({class: 'flex-page-report-schedule-add-buttonset'},
						H.button({type: 'button', name: 'run'},
							H.img({src: 'img/template/tick.png','width':'16','height':'16'}),
							H.span('Save')
						).observe('click',this._save.bind(this, null)),
						H.button({type: 'button', name: 'cancel'},
							H.img({src: '/admin/img/template/decline.png','width':'16','height':'16'}),
							H.span('Cancel')
						).observe('click',this._cancel.bind(this, null))
					)
				),
				this._oSchedule = new Form({onsubmit: this._save.bind(this, null)},
					H.table({class: 'reflex highlight-rows'},
						H.caption(
							H.div({id: 'caption_bar', class: 'caption_bar'},
								H.div({id: "caption_title", class: "caption_title"}, 'Schedule List'),
								H.div({id: 'caption_options', class: 'caption_options'})
							)
						),
						H.thead(
							H.tr({class: 'First'},
								H.th({align: 'Left'}, 'Frequency Multiple'),
								H.th({align: 'Left'}, 'Frequency Type'),
								H.th({align: 'Left'}, 'Schedule Datetime'),
								H.th({align: 'Left'}, 'Action')
							)
						),
						this._oScheduleList = H.tbody({class: 'flex-component-report-schedule-list'})
					)
				)
			);
			this.NODE = this._oForm.getNode();
		},

		_syncUI : function() {
			if (!this._bInitialised || !this._onReady) {
		
				if(this.get('iReportId')) {
					this._oForm.control('id').set('mValue', this.get('iReportId'));
					this._loadSchedules(this.get('iReportId'));
					this._loadReportConstraints();
					this._loadDeliveryFormats();
					this._loadDeliveryMethods();
				}
				this._onReady();
			}
		},
		
		_loadSchedules: function(iReportId) {
			var oData = {
				iReportId : iReportId
			};
			new Ajax.Request('/admin/reflex_json.php/Report/getScheduleForReportId', {
				method		: 'post',
				contentType	: 'application/x-www-form-urlencoded',
				postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
				onSuccess: function (oResponse){
					var oServerResponse = JSON.parse(oResponse.responseText);
					this._populateReportSchedule(oServerResponse.aReportSchedule);
				}.bind(this)
			});
		},

		_loadDeliveryFormats: function() {
			jhr('Report_Delivery_Format', 'getAll', {arguments: []}).then(
				function success(request) {
					var response = request.parseJSONResponse();
					for (var i = 0; i < response.report_delivery_formats.length; i++) {
						var oReportDeliveryFormat = response.report_delivery_formats[i];
						this._oDeliveryFormatContainer.appendChild(
							H.label({title: oReportDeliveryFormat.name},
								new Radio({
									sName		: 'delivery_format',
									sLabel		: oReportDeliveryFormat.name,
									mMandatory	: false,
									mValue		: oReportDeliveryFormat.id
								}),
								H.span(oReportDeliveryFormat.name)
							)
						);
					}
				}.bind(this),
				function (error) {
					// TODO: Handle Error
				}
			);
		},

		_loadDeliveryMethods: function() {
			jhr('Report_Delivery_Method', 'getAll', {arguments: []}).then(
				function success(request) {
					var response = request.parseJSONResponse();
					for (var i = 0; i < response.report_delivery_methods.length; i++) {
						var oReportDeliveryMethod = response.report_delivery_methods[i];
						this._oDeliveryMethodContainer.appendChild(
							H.label({title: oReportDeliveryMethod.name},
								new Radio({
									sName		: 'delivery_method',
									sLabel		: oReportDeliveryMethod.name,
									mMandatory	: false,
									mValue		: oReportDeliveryMethod.id
								}),
								H.span(oReportDeliveryMethod.name)
							).observe('click', this._showDeliveryEmployees.bind(this, oReportDeliveryMethod.name))
						);

					}
				}.bind(this),
				function (error) {
					// TODO: Handle Error
				}
			);
		},

		_loadReportConstraints : function() {
			var oData = {
				iReportId : this.get('iReportId')
			};
			new Ajax.Request('/admin/reflex_json.php/Report_Constraint/getForReportId', {
				method		: 'post',
				contentType	: 'application/x-www-form-urlencoded',
				postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
				onSuccess: function (oResponse){
					var oServerResponse = JSON.parse(oResponse.responseText);
					
					for (var i = 0;i < oServerResponse.length; i++) {
						//Check for type here

						if(oServerResponse[i]['component_type'] == "Text") {
							if(oServerResponse[i]['validation_regex'] == "null") {
								this._oConstraintContainer.appendChild(
									H.div({class: 'flex-page-report-schedule-add-details-constraintContainer'},
										H.label(oServerResponse[i]['name']),
										new Text({
											sName		: oServerResponse[i]['name'],
											sLabel		: oServerResponse[i]['name'],
											mMandatory	: true
										})
									)
								);
							}
							else {
								this._oConstraintContainer.appendChild(
									H.div({class: 'flex-page-report-schedule-add-details-constraintContainer'},
										H.label(oServerResponse[i]['name']),
										new Text({
											sName		: oServerResponse[i]['name'],
											sLabel		: oServerResponse[i]['name'],
											mMandatory	: true,
											fnValidate	: function(oControl) {
												if(!preg_match(oServerResponse['validation_regex'], oControl.getValue())) {
													throw new Error("Pattern validation failed");
												}
												return true;
											}
										})
									)
								);
							}
						}
						else if(oServerResponse[i]['component_type'] == "Select") {
							//debugger;
							this._oConstraintContainer.appendChild(
								H.div({class: 'flex-page-report-schedule-add-details-constraintContainer'},
									H.label(oServerResponse[i]['name']),
									new Select({
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true,
										fnPopulate : function(fnCallback) {
											var aOptions = [];
											for (var j = 0; j < oServerResponse[i]['source_data'].length; j++) {
												aOptions.push(
													H.option({value: oServerResponse[i]['source_data'][j]['value']},
														oServerResponse[i]['source_data'][j]['label']
													)
												);
											}
											fnCallback(aOptions);
										}
									})
								)
							);

						}
						if(oServerResponse[i]['component_type'] == "Date") {	
							this._oConstraintContainer.appendChild(
								H.div({class: 'flex-page-report-schedule-add-details-constraintContainer'},
									H.label(oServerResponse[i]['name']),
									new Datetime({
										bTimePicker	: false,
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true
									})
								)
							);
						}
						else if(oServerResponse[i]['component_type'] == "DateTime") {
							this._oConstraintContainer.appendChild(
								H.div({class: 'flex-page-report-schedule-add-details-constraintContainer'},
									H.label(oServerResponse[i]['name']),
									new Datetime({
										bTimePicker	: true,
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true
									})
								)
							);

						}
					}	
				}.bind(this)
			});
		},

		_setFrequencyTypesPropertyForArray : function(aData) {
			var aFrequencyTypes	= {};
			for(var i in aData){
				if(aData.hasOwnProperty(i)){
					// Save Frequency Type
					var iId = aData[i].id;
					var oFrequencyType = aData[i];
					aFrequencyTypes[iId] = oFrequencyType;
				}
			}
			this._aFrequencyTypes.push(aFrequencyTypes);
		},

		_getFrequencyTypes : function(fnCallback, oEvent, oTest) {
			if (!oEvent) {
				// Request
				var oReq = new XHRRequest('reflex_json.php/Report_Frequency_Type/getAll', this._populateFrequencyTypes.bind(this, fnCallback));
				oReq.send();
			} else {
				// Got response
				var oResponse	= oEvent.getData();
				var aData	= oResponse.getData();
				this._setFrequencyTypesPropertyForArray(aData.report_frequency_types);
				if(fnCallback) {
					fnCallback(mData);
				}
			}
		},

		_populateFrequencyTypes : function(fnCallback, oEvent, oTest) {
			if (!oEvent) {
				// Request
				var oReq = new XHRRequest('reflex_json.php/Report_Frequency_Type/getAll', this._populateFrequencyTypes.bind(this, fnCallback));
				oReq.send();
			} else {
				// Got response
				var oResponse	= oEvent.getData();
				var aData	= oResponse.getData();
				var aOptions = [];

				for(var i in aData.report_frequency_types){
					if(aData.report_frequency_types.hasOwnProperty(i)){
						aOptions.push(
							H.option({value: aData.report_frequency_types[i].id},
								aData.report_frequency_types[i].name
							)
						);
					}
				}
				if(fnCallback) {
					fnCallback(aOptions);
				}
			}
		},

		_populateReportSchedule : function(aData) {
			
			for (var iKey=0; iKey<aData.length; iKey++) {
				var oReportSchedule = aData[iKey];
				
				this._oScheduleList.appendChild(
					H.tr({class: 'flex-component-report-schedule-list-schedule', id: 'flex-component-report-schedule-list-row-'+oReportSchedule.id},
						H.td(
							new Hidden({sName: 'frequency_schedule['+this._iScheduleCount+'].frequency_multiple', mValue: oReportSchedule.frequency_multiple}),
							H.span(oReportSchedule.frequency_multiple)
						),
						H.td(
							new Hidden({sName: 'frequency_schedule['+this._iScheduleCount+'].report_frequency_type_id', mValue: oReportSchedule.report_frequency_type_id}),
							H.span(oReportSchedule.report_frequency_type_id)
						),
						H.td(
							new Hidden({sName: 'frequency_schedule['+this._iScheduleCount+'].schedule_datetime', mValue: oReportSchedule.schedule_datetime}),
							H.span(oReportSchedule.schedule_datetime)
						),
						H.td(
							H.button({type: 'button'},
								H.img({src: '/admin/img/template/archive.png','width':'16','height':'16'}),
								H.span('Archive')
							).observe('click', this._archiveSchedule.bind(this,oReportSchedule.id)) // Original function(){ this.parentElement.parentElement.remove();
						)
					)
						
				);
				this._iScheduleCount++;
			};
		},

		_archiveSchedule: function(iReportScheduleId) {
			var oData = {
				iReportScheduleId : iReportScheduleId
			};
			$('#flex-component-report-schedule-list-row-'+iReportScheduleId).remove();
			new Ajax.Request('/admin/reflex_json.php/Report_Schedule/archiveSchedule', {
				method		: 'post',
				contentType	: 'application/x-www-form-urlencoded',
				postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
				onSuccess: function (oResponse){

				}.bind(this)
			});
		},

		_showDeliveryEmployees: function(sReportDeliveryName) {
			if(sReportDeliveryName == "Email") {
				this._oDeliveryEmployeeContainer.show();
				this._loadDeliveryEmployees();
			}
			else {
				this._oDeliveryEmployeeContainer.hide();
			}
		},

		_loadDeliveryEmployees: function() {
			jhr('Report', 'getEmployees', {arguments: []}).then(
				function success(request) {
					var response = request.parseJSONResponse();
					response.employees.forEach(function (oEmployee) {
						this._oEmployeeContainer.appendChild(
							H.div({class: 'flex-component-report-schedule-add-deliveryemployee-div-container'},
								H.label({class: 'flex-component-report-schedule-add-deliveryemployee-div-container-label'},oEmployee.FirstName + ' ' + oEmployee.LastName),
								new Checkbox({
									bChecked	: (oEmployee.report_id) ? true : false,
									sName		: 'delivery_employee[]',
									sLabel		: 'Delivery Employee',
									mValue		: oEmployee.Id,
									sExtraClass	: 'flex-page-report-schedule-add-deliveryemployee'
								})
							)
						);
					}.bind(this));
				}.bind(this),
				function (error) {
					// TODO: Handle Error
				}
			);
		},

		_cancel : function(event) {
			//var formData = jsonForm(this._oForm);
			//this.fire('complete', formData);
			this.fire('complete');
        },

		_save : function(event) {
			var bValidation = this._oForm.validate();
			if(bValidation) {
				this._oForm.control('selectedDeliveryEmployees').set('mValue', this._getSelectedDeliveryEmployees());
				jhr('Report_Schedule', 'saveSchedule', {arguments: this._oForm.getData()}).then(
					function success(request) {
						var oResponse = request.parseJSONResponse();
						new Alert(oResponse.sMessage);
						this.fire('complete');
					}.bind(this),
					function (error) {
						// TODO: Handle Error
					}
				);
			}
        },

        _getSelectedDeliveryEmployees : function() {
			var aElements = this._oEmployeeContainer.select('input:checked');
			var aEmployee = [];
			for(var i in aElements) {
				if(aElements.hasOwnProperty(i)) {
					var oElement = aElements[i];
					var iEmployeeId = parseInt(oElement.value);
					aEmployee.push(iEmployeeId);
				}
			}
			return aEmployee;
		},

        statics : {
			createAsPopup : function() {
				var oComponent      = self.applyAsConstructor($A(arguments)),
					oPopup                  = new Popup({
					sExtraClass             : 'css-class-name',
					sTitle                  : 'Add Report Schedules',
					sIconURI                : './img/template/pencil.png',
					bCloseButton    : true
				},
				oComponent.getNode()
			);
			return oPopup;
		}
			
	}
});

return self;
