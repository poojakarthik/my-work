"use strict";

var H = require('fw/dom/factory'), // HTML
	S = H.S, // SVG
	Class = require('fw/class'),
	Component = require('fw/component'),
	XHRRequest = require('fw/xhrrequest'),
	Form = require('fw/component/form'),
	Checkbox = require('fw/component/control/checkbox');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({
			iCustomerGroupId : {},
			sCustomerGroupInternalName : {},
			iCustomerGroupDefaultRecordTypeVisibility : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-customer-group-record-type-visibility');
	},

	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		// Overriding default layout style.
		// $$('.maximum-area-body')[0].addClassName('flex-page-customer-group-record-type-visibility-container');

		this.NODE = H.div(
			this._oConfiguration = new Form({onsubmit: function() {/*this._handleSubmit();*/}.bind(this)},
				H.table({class: 'reflex highlight-rows'},
					H.caption(
						H.div({id: 'caption_bar', class: 'caption_bar'},
							H.div({id: "caption_title", class: "caption_title"}, 'Customer Group Configuration'),
							H.div({id: 'caption_options', class: 'caption_options'})
						)
					),
					H.thead(
						H.tr({class: 'First'},
							H.th({align: 'Left'}, 'Setting'),
							H.th({width: '160px', align: 'Left'}, 'Visibility')
						)
					),
					H.tbody(
						H.tr(
							H.td({class: 'configuration-default-record-type-visibility-label'}, 'Default for all Record Types'),
							H.td(
								H.fieldset({class: 'flex-page-customer-group-record-type-visibility-customergroup-setting'},
									this._oConfigurationVisible = H.label({title: 'Always Visible'},
										H.input({type: 'radio', name: 'default_record_type_visibility', value: 'visible'}),
										H.span('Visible')
									),
									this._oConfigurationNotVisible = H.label({title: 'Always Hidden'},
										H.input({type: 'radio', name: 'default_record_type_visibility', value: 'hidden'}),
										H.span('Hidden')
									)
								)
							)
						)
					)
				),
				H.fieldset({class: 'flex-page-customer-group-record-type-visibility-customergroup-buttons'},
					this._oConfigurationSaveButton = H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
						H.span('Save Customer Group Setting')
					).observe('click', this._handleSaveConfiguration.bind(this))
				)
			),
			this._oForm = new Form({onsubmit: function() {/*this._handleSubmit();*/}.bind(this)},
				H.table({class: 'reflex highlight-rows'},
					H.caption(
						H.div({id: 'caption_bar', class: 'caption_bar'},
							H.div({id: "caption_title", class: "caption_title"}, 'Customer Group Invoice Itemisation'),
							H.div({id: 'caption_options', class: 'caption_options'})
						)
					),
					H.thead(
						H.tr({class: 'First'},
							H.th({align: 'Left'}, 'Record Type'),
							H.th({align: 'Left'}, 'Service Type'),
							H.th({width: '160px', align: 'Left'}, 'Visibility')
						)
					),
					H.tbody()/*,
					H.tfoot(
						H.th({colspan: '3'},
							this._oSaveButton = H.button({'type':'button', 'class':'icon-button'},
								H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
								H.span('Save')
							).observe('click', this._handleSaveCustomerGroupRecordTypes.bind(this))
						)
					)*/
				),
				H.fieldset({class: 'flex-page-customer-group-record-type-visibility-recordtype-buttons'},
					this._oSaveButton = H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
						H.span('Save Individual Settings')
					).observe('click', this._handleSaveCustomerGroupRecordTypes.bind(this))
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

				if(this.get('sCustomerGroupInternalName')) {
					this.NODE.select('.configuration-default-record-type-visibility-label').first().update('Default for all Record Types on '+this.get('sCustomerGroupInternalName'));
				}

				// Set the Default Record Type Visibility Radio.
				if(this.get('iCustomerGroupDefaultRecordTypeVisibility') === 1) {
					this._oConfigurationVisible.select('input').first().checked = true;
				}
				if(this.get('iCustomerGroupDefaultRecordTypeVisibility') === 0) {
					this._oConfigurationNotVisible.select('input').first().checked = true;
				}
				// Populate Service Type Visibility Data
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
				var oInheritElement = H.label({class: 'visibility-inherit', title: 'Inherit from Customer Group Setting'},
					H.input({type: 'radio', name: 'record_types.'+oRecordTypes[iIndex].Id+'.visibility', value: 'inherit'}),
					H.span({}, 'Inherit')
				);
				var oNotVisibleElement = H.label({class: 'visibility-inherit', title: 'Always Hidden'},
					H.input({type: 'radio', name: 'record_types.'+oRecordTypes[iIndex].Id+'.visibility', value: 'hidden'}),
					H.span({}, 'Hidden')
				);
				var oVisibleElement = H.label({class: 'visibility-inherit', title: 'Always Visible'},
					H.input({type: 'radio', name: 'record_types.'+oRecordTypes[iIndex].Id+'.visibility', value: 'visible'}),
					H.span({}, 'Visible')
				);
				var oCustomerGroupRecordTypeVisibilityRecord = H.fieldset({class: 'flex-page-customer-group-record-type-visibility-recordtype-setting'},
					oVisibleElement,
					oInheritElement,
					oNotVisibleElement
				);
				// Select an option
				if(oRecordTypes[iIndex].customer_group_record_type_visibility_id && oRecordTypes[iIndex].is_visible) {
					// Visible
					oVisibleElement.select('input').first().checked = true;
				} else if(oRecordTypes[iIndex].customer_group_record_type_visibility_id && !oRecordTypes[iIndex].is_visible) {
					// Hidden
					oNotVisibleElement.select('input').first().checked = true;
				} else {
					// Inherit/Default
					oInheritElement.select('input').first().checked = true;
				}

				// Append to DOM
				var oDomElements = H.tr(
					H.td(oRecordTypes[iIndex].Name),
					H.td(oRecordTypes[iIndex].service_type_name),
					H.td(oCustomerGroupRecordTypeVisibilityRecord)
				);
				if(iIndex % 2) {
					oDomElements.addClassName('alt');
				}
				oNewTbody.appendChild(oDomElements);
			}
		}
		oOldTbody.parentNode.replaceChild(oNewTbody, oOldTbody);
	},


	_getFormData : function() {
		var aData = [];
		var aRecordTypes = this.NODE.querySelectorAll(".flex-page-customer-group-record-type-visibility-recordtype-setting input:checked");
		for(var iIndex=0; iIndex<aRecordTypes.length; iIndex++){
			if(aRecordTypes.hasOwnProperty(iIndex) && iIndex !== 'length') {
				var iRecordTypeId	= parseInt(aRecordTypes[iIndex].getAttribute('name').split(".")[1]);
				var sVisibility		= aRecordTypes[iIndex].getAttribute("value");
				if(iRecordTypeId) {
					aData.push({
						'record_type_id': iRecordTypeId,
						'customer_group_id': this.get('iCustomerGroupId'),
						'visibility': sVisibility
					});
				}
			}
		}
		return aData;
	},

	_handleSaveConfiguration : function() {
		try {
			// Get configuration
			var oNode = this.NODE.querySelectorAll(".flex-page-customer-group-record-type-visibility-customergroup-setting input:checked");
			var sVisibility = oNode[0].getAttribute("value");
			var iDefaultRecordTypeVisibility = (sVisibility == "visible") ? 1 : 0;
			var oData = {
					'customer_group_id': this.get('iCustomerGroupId'),
					'default_record_type_visibility': iDefaultRecordTypeVisibility
				};
			// Save
			this._oConfigurationSaveButton.disable();
			this._oConfigurationSaveButton.select('span')[0].update('Saving...');
			this._saveConfiguration(oData, function() {
				this._saveCompleted();
				this._oConfigurationSaveButton.enable();
				this._oConfigurationSaveButton.select('span')[0].update('Save Customer Group Setting');
			}.bind(this));
		} catch (sError) {
			// Alert
			this._handleException({'message':sError});
		}
	},
	_handleSaveCustomerGroupRecordTypes : function() {
		try {
			this._oSaveButton.disable();
			this._oSaveButton.select('span')[0].update('Saving...');
			this._save(this._getFormData(), function() {
				this._saveCompleted();
				this._oSaveButton.enable();
				this._oSaveButton.select('span')[0].update('Save Individual Settings');
			}.bind(this));
		} catch (sError) {
			// Alert
			this._handleException({'message':sError});
		}
	},

	_saveConfiguration : function(oData, fnCallback) {
		new Ajax.Request('/admin/reflex_json.php/Customer_Group/updateDefaultReordTypeVisibility', {
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

	_save : function(oData, fnCallback) {
		new Ajax.Request('/admin/reflex_json.php/Customer_Group_Record_Type_Visibility/updateReordTypeVisibilityForArray', {
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
			'sTitle'		: 'Save Completed'
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
				if (fnCallback && oServerResponse && oServerResponse.aRecordTypes) {
					fnCallback(oServerResponse.aRecordTypes);
				} else {
					return (oServerResponse && oServerResponse.aRecordTypes) ? oServerResponse.aRecordTypes : null;
				}
			}.bind(this)
		});
	}
});

return self;