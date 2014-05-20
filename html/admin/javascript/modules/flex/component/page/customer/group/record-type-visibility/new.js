
var	H			= require('fw/dom/factory'),
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	Popup		= require('fw/component/popup'),
	Alert		= require('fw/component/popup/alert'),
	Function	= require('fw/function'),
	Form		= require('fw/component/form'),
	XHRRequest	= require('fw/xhrrequest'),
	Hidden		= require('fw/component/control/hidden'),
	Checkbox	= require('fw/component/control/checkbox'),
	Select		= require('fw/component/control/select');

var	self = new Class({
	'extends' : Component,

	construct : function() {
		this.CONFIG = Object.extend({
			iCustomerGroupId : {}
		}, this.CONFIG || {});

		// Call the parent constructor
		this._super.apply(this, arguments);

		// Class specific to our component
		this.NODE.addClassName('flex-page-customer-group-record-type-visibility-new');
	},

	_buildUI : function() {
		this._oForm = new Form({onsubmit: this._save.bind(this, null)},
			new Hidden({
				sName : 'id'
			}),
			H.fieldset(
				H.div(
					H.span('Record Type')
				),
				H.div(
					this._oRecordType = new Select({
						fnPopulate : this._getUnusedRecordTypesForCustomerGroupId.bind(this)
					})
				),
				H.div(
					H.span('Visibility')
				),
				H.div(
					this._oRecordTypeVisibility = new Checkbox({
						bChecked: 0,
						sName: 'is_visible'
					})
				)
			),
			H.div({'class': 'flex-page-customer-group-record-type-visibility-new-buttons'},
				H.button({'type':'button', 'class':'icon-button'},
					H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
					H.span('Save')
				).observe('click', this._save.bind(this)),
				H.button({'type':'button', 'class':'icon-button'},
					H.img({src: '/admin/img/template/delete.png','width':'16','height':'16'}),
					H.span('Cancel')
				).observe('click', this._cancel.bind(this))
			)
		);

		this.NODE = this._oForm.getNode();
	},

	_syncUI : function() {
		this._onReady();
	},

	_save : function(fnCallback, oXHREvent) {
		var oData = {
			iCustomerGroupId	: this.get('iCustomerGroupId'),
			iRecordTypeId		: this._oRecordType.getValue(),
			iIsVisible			: (this._oRecordTypeVisibility.CONFIG.bChecked.mValue) ? 1 : 0
		};
		new Ajax.Request('/admin/reflex_json.php/Customer_Group_Record_Type_Visibility/saveRecordType', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				// Success
				this.fire('complete');
				// Success
				/*
				if(fnCallback) {
					fnCallback(oServerResponse);
				} else {
					return oServerResponse;
				}
				*/
			}.bind(this)
		});
	},

	_cancel : function() {
		this.fire('cancel');
	},

	_getUnusedRecordTypesForCustomerGroupId : function(fnCallback, oXHREvent) {
		var oData = {
			iCustomerGroupId : this.get('iCustomerGroupId')
		};
		new Ajax.Request('/admin/reflex_json.php/Customer_Group_Record_Type_Visibility/getUnusedRecordTypesForCustomerGroupId', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				// Success
				var aOptions	= [];
				for (var i in oServerResponse) {
					if(oServerResponse.hasOwnProperty(i)) {
						aOptions.push(
							H.option({value: oServerResponse[i].Id},
								oServerResponse[i].Name
							)
						);
					}
				}
				fnCallback(aOptions);


			}.bind(this)
		});
	}

});

Object.extend(self, {

	createAsPopup : function() {
		var	oComponent	= self.applyAsConstructor($A(arguments)),
		oPopup			= new Popup({
				sIconURI		: '/admin/img/template/edit.png',
				sTitle			: 'New Customer Group Record Type Visibility',
				bCloseButton	: true
			},
			oComponent.getNode()
		);
		return oPopup;
	}
});

return self;