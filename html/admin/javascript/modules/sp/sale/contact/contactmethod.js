var Class = require('fw/class');

var self = new Class({
	extends : require('sp/guicomponent'),

	construct : function (obj) {
		if (obj == null) {
			this.object = {
				id: null,
				contact_method_type_id: null,
				details: null,
				is_primary: false
			};
		} else {
			this.object = obj;
		}
	},

	isValid : function () {
		// Validate the values and invoke the isValid method of child objects

		// Validate all the fields ...
		//value = Sale.GUIComponent.getValue(this.elementGroups.contact_method_type_id);
		//this.object.contact_method_type_id = value;

		//value = Sale.GUIComponent.getValue(this.elementGroups.details);
		//this.object.details = value;

		//value = Sale.GUIComponent.getValue(this.elementGroups.is_primary);
		//this.object.primary = value;

		// WIP

		return true;
	},

	showValidationTip : function () {
		return false;
	},

	setContactMethodTypeId : function (value) {
		this.object.contact_method_type_id = value;
	},

	getContactMethodTypeId : function () {
		return this.object.contact_method_type_id;
	},

	setDetails : function (value) {
		this.object.details = value;
	},

	getDetails : function () {
		return this.object.details;
	},

	setIsPrimary : function (value) {
		this.object.is_primary = value;
	},

	getIsPrimary : function () {
		return this.object.is_primary;
	},

	statics : {
		CONTACT_METHOD_TYPE_EMAIL : 1,
		CONTACT_METHOD_TYPE_FAX : 2,
		CONTACT_METHOD_TYPE_PHONE : 3,
		CONTACT_METHOD_TYPE_MOBILE : 4
	}
});

return self;