"use strict";

var	H = require('fw/dom/factory'), // HTML
	S = H.S, // SVG
	Class = require('fw/class'),
	Component = require('fw/component'),
	XHRRequest = require('fw/xhrrequest'),
	Dataset	= require('fw/dataset'),
	DatasetXHR = require('fw/dataset/xhr'),
	Sort = require('fw/dataset/sort'),
	Filter = require('fw/dataset/filter'),
	Pagination = require('fw/dataset/pagination'),
	Edit = require('./edit'),
	Run	= require('./run'),
	Schedule = require('./schedule/add'),
	Popup = require('fw/component/popup'),
	Form = require('fw/component/form');

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
		var oRequest	= new XHRRequest('reflex_json.php/Report/getAll');
		this._oSort		= new Sort(true);
		this._oFilter	= new Filter();
		this.oDataset 	= new DatasetXHR(Dataset.CACHE_MODE_NO_CACHING, oRequest, this._oSort, this._oFilter);
		this.oPagination	= new Pagination(10, this.oDataset);

		var sButtonPathBase	= '../admin/img/template/resultset_';
		this.NODE = H.section(
			H.header({class: 'flex-page-report-manage-heading'},
				H.h2('Manage Reports')
			),
			this._oConfiguration = H.table({class: 'reflex highlight-rows'},
				H.caption(
					H.div({id: 'caption_bar', class: 'caption_bar'},
						H.div({id: "caption_title", class: "caption_title"}, 
							H.span('Manage Reports'),
							H.span({class: 'flex-page-report-manage-pagination-info'})
						),
						H.div({id: 'caption_options', class: 'caption_options'},
							H.div({class: 'flex-page-report-manage-pagination'},
								H.span({class: 'pagination-loading'},
									'Loading...'
								),
								H.button({class: 'flex-page-report-manage-pagination-button'},
									H.img({src: sButtonPathBase + 'first.png'})
								),
								H.button({class: 'flex-page-report-manage-pagination-button'},
									H.img({src: sButtonPathBase + 'previous.png'})
								),
								H.button({class: 'flex-page-report-manage-pagination-button'},
									H.img({src: sButtonPathBase + 'next.png'})
								),
								H.button({class: 'flex-page-report-manage-pagination-button'},
									H.img({src: sButtonPathBase + 'last.png'})
								)
							)
						)
					)
				),
				H.thead(
					H.tr({class: 'First'},
						H.th({class: 'pointer'}, 'Name').observe('click', this._toggleSort.bind(this, 'name')),
						H.th({class: 'pointer'}, 'Created').observe('click', this._toggleSort.bind(this, 'created_datetime')),
						H.th({class: 'pointer'}, 'Created By').observe('click',this._toggleSort.bind(this,'created_employee_full_name')),
						H.th({class: 'pointer'}, 'Report Category').observe('click',this._toggleSort.bind(this,'report_category')),
						H.th('Options')
					)
				),
				this._oReports = H.tbody()
			),
			H.fieldset({class: 'flex-page-report-manage-buttons'},
				this._oSaveButton = H.button({type:'button', name:'create'}, 'Create New Report').observe('click', this._new.bind(this, null))
			)
		);

		

		// Bind events to the pagination buttons
		var aTopPageButtons = this.NODE.select('div.caption_options button.flex-page-report-manage-pagination-button');
		
		// First
		aTopPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		
		//Previous		
		aTopPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		
		// Next
		aTopPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		
		// Last
		aTopPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			oTop	: {
				oFirstPage		: aTopPageButtons[0],
				oPreviousPage	: aTopPageButtons[1],
				oNextPage		: aTopPageButtons[2],
				oLastPage		: aTopPageButtons[3]
			}
		};
		// Add to DOM
		$('.flex-page')[0].appendChild(this.NODE);
	},

	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {
			if (!this._bInitialised) {
				this.oPagination.observe('update', this._pageLoaded.bind(this));
				// Send the initial sorting parameters to dataset ajax 
				this._oSort.refreshData(true);
				this._oFilter.refreshData(true);
				this.oPagination.getCurrentPage();
				this._oSort.registerField('name', Sort.DIRECTION_OFF);
				this._oSort.registerField('created_employee_full_name', Sort.DIRECTION_OFF);
				this._oSort.registerField('report_category', Sort.DIRECTION_OFF);
				this._oSort.registerField('created_datetime', Sort.DIRECTION_OFF);
				//this._getReports(this._populateReports.bind(this));
			} else {
				// Every other call
			}
			this._onReady();
		} catch (oException) {
			// Fail
			// this._handleException(oException);
		}
	},
	// ---------------------------------------------------------------------------------- //
	// Sorting Pagination code Start
	// ---------------------------------------------------------------------------------- //
	
	_pageLoaded : function(oEvent) {
		var oResult	= oEvent.getData();
		this._populateReports(oResult.oResultSet);
		
		this._updatePagination();
		this._showLoading(false);
	},

	_showLoading	: function(bShow) {
		var oLoading	= this.NODE.select('span.pagination-loading').first();
		if (bShow) {
			oLoading.show();
		} else {
			oLoading.hide();
		}
	},
	
	_changePage	: function(sFunction) {
		this._showLoading(true);
		this.oPagination[sFunction]();
	},

	_updatePagination : function(iPageCount) {
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oTop.oFirstPage.disabled		= true;
		this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oTop.oNextPage.disabled			= true;
		this.oPaginationButtons.oTop.oLastPage.disabled			= true;
		
		if (iPageCount == undefined) {
			// Get the page count
			this.oPagination.getPageCount(this._updatePagination.bind(this));
		} else {
			// Update Page ? of ?, show 1 for page count if it is 0 because there is technically still a page even though it's empty
			var oPageInfo		= this.NODE.select('span.flex-page-report-manage-pagination-info').first();
			oPageInfo.innerHTML	= ' (Page '+ (this.oPagination.iCurrentPage + 1) +' of ' + (iPageCount == 0 ? 1 : iPageCount) + ')';
			
			if (this.oPagination.iCurrentPage != Pagination.PAGE_FIRST) {
				// Enable the first and previous buttons
				this.oPaginationButtons.oTop.oFirstPage.disabled 		= false;
				this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.iCurrentPage < (iPageCount - 1) && iPageCount) {
				// Enable the next and last buttons
				this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
			}
		}
	},
	
	_toggleSort : function(sField) {
		this._oSort.toggleField(sField);
		this._showLoading(true);
		this._refreshData();
	},

	_refreshData : function() {
		this._oSort.refreshData();
		this.oPagination.getCurrentPage();
	},
	// ---------------------------------------------------------------------------------- //
	// Sorting Pagination code End
	// ---------------------------------------------------------------------------------- //
	_new : function() {
		var oPopup = Edit.createAsAddPopup( {
			oncomplete : function(formData) {
				this.oPagination.getCurrentPage();
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
				this.oPagination.getCurrentPage();
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
			'sReportTitle': this._aReportTitles[iReportId],
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
		this._aReportTitles = new Array();
		var bCanAdd = false;
		for (var i in aData) {
			if (aData.hasOwnProperty(i)) {

				if (aData[i].message != undefined) {
					var oReportNode = H. tr(
						H.td({colspan: 5}, aData[i].message)						
					);
					this._oReports.appendChild(oReportNode);
					if (aData[i].bCanManage) {
						bCanAdd = true;
					}
					break;
				}

				this._aReportTitles[aData[i].id] = aData[i].name;
				// Build the report dom elements.
				var oReportNode = H.tr(
					H.td({}, aData[i].name),
					H.td(aData[i].created_datetime),
					H.td(aData[i].created_employee_full_name),
					H.td(aData[i].report_category)
				);
				if (aData[i].bCanManage) {
					oReportNode.appendChild(
						H.td(
							H.button({type: 'button', name: 'configure'}, 'Configure').observe('click', this._edit.bind(this, aData[i].id)),
							H.button({type: 'button', name: 'schedule'}, 'Schedule').observe('click', this._schedule.bind(this, aData[i].id)),
							H.button({type: 'button', name: 'run'}, 'Run').observe('click', this._run.bind(this, aData[i].id))
						)
					);
					bCanAdd = true;
				} else {
					oReportNode.appendChild(
						H.td(
							H.button({type: 'button', name: 'schedule'}, 'Schedule').observe('click', this._schedule.bind(this, aData[i].id)),
							H.button({type: 'button', name: 'run'}, 'Run').observe('click', this._run.bind(this, aData[i].id))
						)
					);
				}
				// Attach the report to the list.
				this._oReports.appendChild(oReportNode);
			}
		}
		if (bCanAdd) {
			$('.flex-page-report-manage-buttons').show();
		}
	},

    statics : {}
});

return self;
