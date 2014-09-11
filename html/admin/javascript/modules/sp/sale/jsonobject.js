var Class = require('fw/class');

/*
	A class to represent an object containing all data for a sale, to be sent back to the PHP layer for processing into the database
*/
var self = new Class({
	id:null,					//integer
	sale_status_id:null,		//integer
	created_on:null,			//integer
	created_by:null,			//integer
	commission_paid_on:null,	//??
	sale_type_id:null,			//integer

	//The following are only supplied when retrieving a sale from the database
	status: null,				//string, eg"Submitted",
	status_description:null,	// string eg"New sale",

	//the other objects to send back to the server
	sale_account:null,			//SP.Sale.SaleAccount.object
	contacts:null,				//array of SP.Sale.Contact.object
	items:null,					//array of SP.Sale.Item.object
	notes:null,					//array of SP.Sale.Note.object

	statics : {
		instance: null,

		getInstance : function () {
			if (this.instance == null) {
				this.instance = new self();
			}
			return this.instance;
		},

		setSaleData : function (iId, iSaleStatusId, iCreatedOn, iCreatedBy, iCommissionPaidOn, iSaleTypeId) {
			this.instance = this.getInstance();
			if (iId != null) {
				this.instance.id = iId;
			}
			if (iSaleStatusId != null) {
				this.instance.sale_status_id = iSaleStatusId;
			}
			if (iCreatedOn != null) {
				this.instance.created_on = iCreatedOn;
			}
			if (iCreatedBy != null) {
				this.instance.created_by = iCreatedBy;
			}
			if (iCommissionPaidOn != null) {
				this.instance.commission_paid_on = iCommissionPaidOn;
			}
			if (iSaleTypeId != null) {
				this.instance.sale_type_id = iSaleTypeId;
			}
		},

		setSaleAccount : function (oSaleAccount) {
			this.instance = this.getInstance();
			this.instance.sale_account = oSaleAccount.object;
		},

		setSaleContacts : function (aContacts) {
			this.instance = this.getInstance();
			var aContactObjects = [];
			for (var i=0; i < aContacts.length; i++) {
				aContactObjects.push(aContacts[i].object);
			}

			this.instance.contacts = aContactObjects;
		},

		setSaleItems : function (aItems) {
			this.instance = this.getInstance();

			var aItemObjects = [];
			for (var i=0; i < aItems.length; i++) {
				aItemObjects.push(aItems[i].object);
			}

			this.instance.items = aItemObjects;
		},

		setSaleNotes : function (aNotes) {
			this.instance = this.getInstance();

			var aNoteObjects = [];
			for (var i=0; i < aNotes.length; i++) {
				aNoteObjects.push(aNotes[i].object);
			}

			this.instance.notes = aNoteObjects;
		}
	}
});

return self;