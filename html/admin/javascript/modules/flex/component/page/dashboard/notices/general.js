
var H			= require('fw/dom/factory'), // HTML
	S			= H.S, // SVG
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	XHRRequest	= require('fw/xhrrequest');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-dashboard-notices-general');
	},


	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		this.NODE = H.section(
			H.header(
				H.img({'src':'/admin/img/template/building.png'}),
				H.h3('General Notices')
			),
			this._oNotice = H.div(),
			this._oLoading = H.div({'class':'flex-page-dashboard-loading -enabled'})
		);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		if (!this._bInitialised) {
			// First call.
			this._loadData(function(oData) {
				this._populateNotice(oData);
				this._oLoading.classList.remove('-enabled'); // Hide loading message
			}.bind(this));
		} else {
			// Every other call
		}
		this._onReady();
	},


	_populateNotice : function(oData) {
		this._oNotice.update(oData.message);
	},

	_loadData : function(fnCallback) {
		new Ajax.Request('/admin/reflex_json.php/Employee_Message/getLastestMessageForConstant', {

			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify(['EMPLOYEE_MESSAGE_TYPE_GENERAL'])),

			onSuccess: function (oResponse){
				var oData = JSON.parse(oResponse.responseText);
				fnCallback(oData)
			}.bind(this)

		});
	},


	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {}


});

return self;

