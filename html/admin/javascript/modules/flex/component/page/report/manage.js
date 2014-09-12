"use strict";

var H			= require('fw/dom/factory'), // HTML
	S			= H.S, // SVG
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	XHRRequest	= require('fw/xhrrequest'),
	Edit		= require('./edit'),
	Run			= require('./run'),
	Schedule	= require('./schedule/add'),
    Popup		= require('fw/component/popup'),
	Form		= require('fw/component/form');

var self = new Class({
	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-report-manage');
	},

	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		this.NODE = H.div(
			this._oConfiguration = new Form({onsubmit: function() {/*this._handleSubmit();*/}.bind(this)},
				H.table({class: 'reflex highlight-rows'},
					H.caption(
						H.div({id: 'caption_bar', class: 'caption_bar'},
							H.div({id: "caption_title", class: "caption_title"}, 'Manage Reports'),
							H.div({id: 'caption_options', class: 'caption_options'})
						)
					),
					H.thead(
						H.tr({class: 'First'},
							H.th({align: 'Left'}, 'Name'),
							H.th({width: '160px', align: 'Left'}, 'Created'),
							H.th({width: '160px', align: 'Left'}, 'Created By'),
							H.th({width: '240px', align: 'Left'}, 'Options')
						)
					),
					this._oReports = H.tbody()
				),
				H.fieldset({
					class: 'flex-page-report-manage-buttons',
					style: 'border: 0; margin:0 auto; float: right;'
					},
					this._oSaveButton = H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/new.png','width':'16','height':'16'}),
						H.span('Create New Report')
					).observe('click', this._new.bind(this, null))
				)
			)
		);
		// Add to DOM
		$$('.flex-page')[0].appendChild(this.NODE);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {
			if (!this._bInitialised) {
				this._getReports(this._populateReports.bind(this));
			} else {
				// Every other call
			}
			this._onReady();
		} catch (oException) {
			// Fail
			// this._handleException(oException);
		}
	},

	_new : function() {
		var oPopup = Edit.createAsPopup({
			oncomplete : function(formData) {
				this._getReports(this._populateReports.bind(this));
				oPopup.hide();
			}.bind(this),
			onready : function () {
				oPopup.display();
			}.bind(this),
			oncancel : function() {
				oPopup.hide();
			}
		});
	},

	_edit : function(iReportId) {
		var oPopup = Edit.createAsPopup({
			'iReportId' : iReportId,
			oncomplete : function(oData) {
				this._getReports(this._populateReports.bind(this));
				oPopup.hide();
			}.bind(this),
			onready : function () {
				oPopup.display();
			}.bind(this),
			oncancel : function() {
				oPopup.hide();
			}
		});
	},

	_run : function(iReportId) {
		var oPopup = Run.createAsPopup({
			'iReportId' : iReportId,
			oncomplete : function(oData) {
				oPopup.hide();
			}.bind(this),
			onready : function () {
				oPopup.display();
			}.bind(this),
			oncancel : function() {
				oPopup.hide();
			}
		});
	},

	_schedule : function(iReportId) {
		var oPopup = Schedule.createAsPopup({
			'iReportId' : iReportId,
			oncomplete : function(oData) {
				oPopup.hide();
			}.bind(this),
			onready : function () {
				oPopup.display();
			}.bind(this),
			oncancel : function() {
				oPopup.hide();
			}
		});
	},

	_getReports : function(fnCallback, oXHREvent) {
		if (!oXHREvent) {
			// Request
			var oReq = new XHRRequest('reflex_json.php/Report/getAll', this._getReports.curry(fnCallback));
			oReq.send();
		} else {
			// Got response
			var oResponse	= oXHREvent.getData();
			var aData		= oResponse.getData();
			return fnCallback(aData.aReport);
		}
	},

	_populateReports : function(aData) {
		this._oReports.innerHTML = '';
		for(var i in aData){
			if(aData.hasOwnProperty(i)){
				// Build the report dom elements.
				var oReportNode = H.tr(
					H.td({}, aData[i].name),
					H.td(aData[i].created_datetime),
					H.td(aData[i].created_employee_full_name),
					H.td(
						H.button({type: 'button'},
							H.img({src:'img/template/options.png'}),
							H.span('Configure')
						).observe('click', this._edit.bind(this, aData[i].id)),
						H.button({type: 'button'},
							H.img({src:'img/template/clock.png'}),
							H.span('Schedule')
						).observe('click', this._schedule.bind(this, aData[i].id)),
						H.button({type: 'button'},
							H.img({src:'img/template/play.png'}),
							H.span('Run')
						).observe('click', this._run.bind(this, aData[i].id))
					)
				);
				// Attach the report to the list.
				this._oReports.appendChild(oReportNode);
			}
		}
	},

    statics : {}
});

return self;