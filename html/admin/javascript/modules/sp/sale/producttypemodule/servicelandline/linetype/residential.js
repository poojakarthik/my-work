var Class = require('fw/class');
var DropDown = require('sp/guicomponent/dropdown'),
	DateGroup = require('sp/guicomponent/dategroup'),
	TextInputGroup = require('sp/guicomponent/textinputgroup');
var Sale = require('sp/sale'),
	ServiceLandLine = require('../../servicelandline'),
	Validation = require('sp/validation');

var self = new Class({
	extends : require('../../../producttypemodule'),

	_getBlankDetailsObject : function () {
		var sale = Sale.getInstance();
		//sale.getSaleAccount().updateFromGUI();
		var contact = sale.getContacts()[0];
		//contact.updateFromGUI();

		var titleText = contact.elementGroups.contact_title_id.oDisplay.textContent;
		var titleId = null;
		for (var i = 0, l = ServiceLandLine.staticData.landlineEndUserTitle.description.length; i < l; i++) {
			if (titleText == ServiceLandLine.staticData.landlineEndUserTitle.description[i]) {
				titleId = ServiceLandLine.staticData.landlineEndUserTitle.id[i];
				break;
			}
		}

		return {
			id: null,
			landline_end_user_title_id: titleId,
			end_user_given_name: contact.getFirstName(),
			end_user_family_name: contact.getLastName(),
			end_user_dob: contact.getDateOfBirth(),
			end_user_employer: sale.getSaleAccount().getBusinessName(),
			end_user_occupation: contact.getPositionTitle()
		};
	},

	buildGUI : function () {
		this.setWorkingTable(this.detailsContainer);

		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc = function (intMaxLength) {
			return Validation.getStringLengthValidationFunc(intMaxLength);
		};

		var lengths = ServiceLandLine.staticData.lengths.landlineResidential;

		this.addElementGroup('landline_end_user_title_id', new DropDown(
			ServiceLandLine.staticData.landlineEndUserTitle.id,
			ServiceLandLine.staticData.landlineEndUserTitle.description,
			this.getLandlineEndUserTitleId(),
			true
		), 'Title');
		this.addElementGroup('end_user_given_name', new TextInputGroup(this.getEndUserGivenName(), true, fncGetStringValidationFunc(lengths.endUserGivenName)),'Given Name');
		this.addElementGroup('end_user_family_name', new TextInputGroup(this.getEndUserFamilyName(), true, fncGetStringValidationFunc(lengths.endUserFamilyName)),'Family Name');
		this.addElementGroup('end_user_dob', new DateGroup(this.getEndUserDOB(), true, Validation.date.bind(this)),'Date Of Birth');
		this.addElementGroup('end_user_employer', new TextInputGroup(this.getEndUserEmployer(), false, fncGetStringValidationFunc(lengths.endUserEmployer)),'Employer');
		this.addElementGroup('end_user_occupation', new TextInputGroup(this.getEndUserOccupation(), false, fncGetStringValidationFunc(lengths.endUserOccupation)),'Occupation');
	},

	showValidationTip : function () {
		return false;
	},

	renderDetails : function (readOnly) {

	},

	renderSummary : function (readOnly) {

	},

	setLandlineEndUserTitleId : function (value) {
		this.object.landline_end_user_title_id = value;
	},

	getLandlineEndUserTitleId : function () {
		return this.object.landline_end_user_title_id;
	},

	setEndUserGivenName : function (value) {
		this.object.end_user_given_name = value;
	},

	getEndUserGivenName : function () {
		return this.object.end_user_given_name;
	},

	setEndUserFamilyName : function (value) {
		this.object.end_user_family_name = value;
	},

	getEndUserFamilyName : function () {
		return this.object.end_user_family_name;
	},

	setEndUserDOB : function (value) {
		this.object.end_user_dob = value;
	},

	getEndUserDOB : function () {
		return this.object.end_user_dob;
	},

	setEndUserEmployer : function (value) {
		this.object.end_user_employer = value;
	},

	getEndUserEmployer : function () {
		return this.object.end_user_employer;
	},

	setEndUserOccupation : function (value) {
		this.object.end_user_occupation = value;
	},

	getEndUserOccupation : function () {
		return this.object.end_user_occupation;
	}
});

return self;