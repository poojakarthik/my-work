"use strict";

var H			= require('fw/dom/factory'), // HTML
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	jhr			= require('xhr/json-handler'),
	jsonForm	= require('json-form'),
    Popup		= require('fw/component/popup'),
    Alert 		= require('fw/component/popup/alert'),
	Hidden		= require('fw/component/control/hidden'),
	Select		= require('fw/component/control/select'),
	Checkbox 	= require('fw/component/control/checkbox'),
	Text		= require('fw/component/control/text'),
	Radio		= require('fw/component/control/radio'),
	Form		= require('fw/component/form');

var self = new Class({

	extends: Component,
	_aReportSchedule : {},
	_oReport : {},

	construct   : function() {
		this.CONFIG = Object.extend({
			// Load Existing?
			iReportId : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-report-run');
	},

	_buildUI: function () {
		this._oForm = new Form(//{onsubmit: this._save.bind(this, null)},
			new Hidden({
				sName : 'id',
				mValue: this.get('iReportId')
			}),
			new Hidden({
				sName : 'selectedDeliveryEmployees'
			}),
			H.fieldset({'class': 'flex-page-report-run-details'},
				this._oConstraintContainer = H.div(),
				H.div({class: 'flex-page-report-run-deliveryformat-details'},
					H.label('Delivery Format'),
					H.label({title: 'Excel Format'},
						new Radio({
							sName		: 'delivery_format',
							sLabel		: 'XLS',
							mMandatory	: true,
							mValue		: 'XLS'
						}),
						H.span('XLS')
					),
					H.label({title: 'CSV Format'},
						new Radio({
							sName		: 'delivery_format',
							sLabel		: 'CSV',
							mMandatory	: true,
							mValue		: 'CSV'
						}),
						H.span('CSV')
					)
				),
				H.div({class: 'flex-page-report-run-deliverymethod-details'},
					H.label('Delivery Format'),
					H.label({title: 'Browser Download'},
						new Radio({
							sName		: 'delivery_method',
							sLabel		: 'DOWNLOAD',
							mMandatory	: true,
							mValue		: 'DOWNLOAD'
						}),
						H.span('Download')
					).observe('click', this._hideDeliveryEmployees.bind(this, null)),
					H.label({title: 'Email Report to Selected Employees'},
						new Radio({
							sName		: 'delivery_method',
							sLabel		: 'EMAIL',
							mMandatory	: true,
							mValue		: 'EMAIL'
						}),
						H.span('Email')
					).observe('click', this._showDeliveryEmployees.bind(this, null))
				),
				this._oDeliveryEmployeeContainer = H.div({class: 'flex-page-report-run-deliverymethod-details', style: 'display: none'},
					H.label('Delivery Employee'),
					H.span(
						this._oEmployeeContainer = H.div({style: 'max-height: 150px; max-width: 200px; overflow-y: scroll; overflow-x: hidden;'})
					)
				),
				H.fieldset({class: 'flex-page-report-run-buttonset'},
					H.button({type: 'button', name: 'run'},
						H.img({src: 'img/template/play.png','width':'16','height':'16'}),
						H.span('Run')
					).observe('click',this._executeReport.bind(this, null)),
					H.button({type: 'button', name: 'cancel'},
						H.img({src: '/admin/img/template/decline.png','width':'16','height':'16'}),
						H.span('Cancel')
					).observe('click',this._cancel.bind(this, null))
				)
			)
		);

		this.NODE = this._oForm.getNode();
		// Add to DOM
		//$('.flex-page')[0].appendChild(this.NODE);
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			if (this.get('iReportId')) {
				// Get Report Constraints
				this._loadConstraints();
				this._oForm.control('id').set('mValue', this.get('iReportId'));
			}
			this._onReady();
		}
	},

	_showDeliveryEmployees: function() {
		this._oDeliveryEmployeeContainer.show();
		this._loadDeliveryEmployees();
	},

	_hideDeliveryEmployees: function() {
		this._oDeliveryEmployeeContainer.hide();
	},

	_loadDeliveryEmployees: function() {
		jhr('Report', 'getEmployees', {arguments: []}).then(
			function success(request) {
				var response = request.parseJSONResponse();
				response.employees.forEach(function (oEmployee) {
					this._oEmployeeContainer.appendChild(
						H.div({class: 'flex-component-report-run-deliverytemployee-div-container'},
							H.span({class: 'flex-component-report-run-deliveryemployee-div-container-label'},oEmployee.FirstName + ' ' + oEmployee.LastName),
							new Checkbox({
								bChecked	: (oEmployee.report_id) ? true : false,
								sName		: 'delivery_employee[]',
								sLabel		: 'Delivery Employee',
								mValue		: oEmployee.Id,
								sExtraClass	: 'flex-page-report-run-delivery-employee'
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

	_loadConstraints:function() {

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
								H.div({class: 'flex-page-report-run-details-constraintContainer'},
									H.label(oServerResponse[i]['name']),
									new Text({
										sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true
									})
								)
							);
						}
						else {
							this._oConstraintContainer.appendChild(
								H.div({class: 'flex-page-report-run-details-constraintContainer'},
									H.label(oServerResponse[i]['name']),
									new Text({
										sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
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
							H.div({class: 'flex-page-report-run-details-constraintContainer'},
								H.label(oServerResponse[i]['name']),
								new Select({
									sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
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
							H.div({class: 'flex-page-report-run-details-constraintContainer'},
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
							H.div({class: 'flex-page-report-run-details-constraintContainer'},
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
				};
				
			}.bind(this)
		});
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

	_executeReport: function() {
		this._oForm.control('selectedDeliveryEmployees').set('mValue', this._getSelectedDeliveryEmployees());
		
		jhr('Report', 'generate', {arguments: this._oForm.getData()}).then(
			function success(request) {
				var oResponse = request.parseJSONResponse();
				this._executeReponse(oResponse);
				this.fire('complete');
			}.bind(this),
			function (error) {
				// TODO: Handle Error
			}
		);
	},

	_executeReponse	: function(oResponse)
	{
		if (oResponse.bSuccess)
		{
			if (oResponse.bIsEmail)
			{
				new Alert(oResponse.sMessage);
			}
			else {
				window.location = 'reflex.php/Report/Download/?sFileName=' + encodeURIComponent(oResponse.sFilename) + '&iCSV=' + (oResponse.sFilename.match(/\.csv/) ? 1 : 0);
			}
		}
		else
		{
			// Error occurred in execution
			new Alert(oResponse.sMessage);
		}
	},

	_cancel : function(event) {
		this.fire('cancel');
	},

	
	statics : {
		createAsPopup : function() {
			var oComponent      = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-run-popup',
					sTitle          : 'Run Report',
					sIconURI        : './img/template/play.png',
					bCloseButton    : true
				}, oComponent.getNode()
			);
			return oPopup;
		}
	}

});

return self;