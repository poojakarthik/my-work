
var H						= require('fw/dom/factory'), // HTML
	S						= H.S, // SVG
	Class					= require('fw/class'),
	Component				= require('fw/component'),
	XHRRequest				= require('fw/xhrrequest'),
	Form					= require('fw/component/form'),
	Checkbox				= require('fw/component/control/checkbox'),
	RecordTypeVisibilityNew	= require('./record-type-visibility/new');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({
			iCustomerGroupId : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-customer-group-record-type-visibility');
	},

	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		// Overriding default layout style.
		$$('.maximum-area-body')[0].addClassName('flex-page-customer-group-record-type-visibility-container');

		// onclic: this._getRecordTypes.bind(this, function() {console.log('_getRecordTypes callback called')});
		this.NODE = H.div(
			this._oForm = new Form({onsubmit: function() {/*this._handleSubmit();*/}.bind(this)},
				H.table({class: 'reflex highlight-rows'},
					H.caption(
						H.div({id: 'caption_bar', class: 'caption_bar'},
							H.div({id: "caption_title", class: "caption_title"}, 'Customer Group Record Type Visibility'),
							H.div({id: 'caption_options', class: 'caption_options'})
						)
					),
					H.thead(
						H.tr({class: 'First'},
							H.th({width: '60px', align: 'Left'}, 'Visible'),
							H.th({align: 'Left'}, 'Name')
						)
					),
					H.tbody({class: 'alternating'}),
					H.tfoot(
						H.th({colspan: '2'},
							this._oSaveButton = H.button({'type':'button', 'class':'icon-button'},
								H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
								H.span('Save')
							).observe('click', this._handleSave.bind(this)),
							H.button({'type':'button', 'class':'icon-button'},
								H.img({src: '/admin/img/template/new.png','width':'16','height':'16'}),
								H.span('New')
							).observe('click', this._new.bind(this))
						)
					)
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
				this._getRecordTypes(this._populate.bind(this));
			} else {
				// Every other call
				this._getRecordTypes(this._populate.bind(this));
			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
		}
	},

	_handleException : function(oException) {
		if (oException && oException.message) {
			console.log('An exception has occurred with the message: "' + oException.message + '"');
			console.log('Exception: "' + oException + '"');
		} else {
			console.log('An unknown error has occurred.');
		}
	},

	_populate : function(oData) {
		var oRecordTypes = oData;
		var oOldTbody = this._oForm.select('table > tbody').first();
		var oNewTbody = H.tbody();
		for(var iIndex in oRecordTypes){
			if(oRecordTypes.hasOwnProperty(iIndex)) {
				var oCheckbox = new Checkbox({
					bChecked: oRecordTypes[iIndex].is_visible,
					sName: oRecordTypes[iIndex].customer_group_record_type_visibility_id
				});
				var oDomElements = H.tr(
					H.td(oCheckbox),
					H.td(oRecordTypes[iIndex].Name)
				);
				//this._oForm.select('table > tbody').first().appendChild(oDomElements);
				oNewTbody.appendChild(oDomElements);
			}
		}
		oOldTbody.parentNode.replaceChild(oNewTbody, oOldTbody);
	},

	_getFormData : function() {
		var aData = [];
		var aControls = this._oForm.getControls();
		for(var iIndex in aControls){
			if(aControls.hasOwnProperty(iIndex)) {
				console.log(aControls[iIndex].CONFIG.sName.mValue);
				aData.push({
					'id': aControls[iIndex].CONFIG.sName.mValue,
					'is_visible': (aControls[iIndex].CONFIG.bChecked.mValue) ? 1 : 0
				});
			}
		}
		return aData;
	},

	_handleSave : function() {
		try {
			this._oSaveButton.disable();
			this._oSaveButton.select('span')[0].update('Saving...');
			this._save(this._getFormData(), function() {
			this._saveCompleted();
			this._oSaveButton.enable();
			this._oSaveButton.select('span')[0].update('Save');
			}.bind(this));
		} catch (sError) {
			// Alert
			this._handleException({'message':sError});
		}
	},

	_save : function(oData, fnCallback) {
		new Ajax.Request('/admin/reflex_json.php/Customer_Group_Record_Type_Visibility/updateIsVisibleForId', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				if (fnCallback) {
					fnCallback(oServerResponse);
				} else {
					return (oServerResponse) ? oServerResponse : null;
				}
			}.bind(this)
		});
	},

	_saveCompleted : function() {
		Reflex_Popup.alert('Save Completed', {
			'iWidth'		: '30',
			'sTitle'		: 'Update Record Type Visibility'
		});
	},

	_new : function() {
		var oPopup = RecordTypeVisibilityNew.createAsPopup({
			iCustomerGroupId : this.get('iCustomerGroupId'),
			oncomplete	: function() {
				oPopup.hide();
				this._syncUI();
			}.bind(this),
			onready : function () {
				oPopup.display();
			},
			oncancel : function() {
				oPopup.hide();
			}
		});
	},

	_getRecordTypes : function(fnCallback, oXHREvent) {
		var oData = {
			iCustomerGroupId : this.get('iCustomerGroupId')
		};
		new Ajax.Request('/admin/reflex_json.php/Customer_Group_Record_Type_Visibility/getRecordTypesForCustomerGroupId', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				if (fnCallback) {
					fnCallback(oServerResponse);
				} else {
					return (oServerResponse) ? oServerResponse : null;
				}
			}.bind(this)
		});
	},

	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {
		STATIC_DEFINITION : null,
		staticMethod : function() {
			// Sample
		}
	}

});

return self;
