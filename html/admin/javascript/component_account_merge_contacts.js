
var Component_Account_Merge_Contacts = Class.create(Reflex_Component, {
	
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'hContacts'		: {},
			'fnOnComplete'	: {}
		}, this.CONFIG || {});
	
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this._hContactControls 		= null;
		this._aMergingContactIds	= null;
		
		this.NODE.addClassName('component-account-merge-contacts');
	},
	
	// Public
	
	doMerge : function() {
		this._doMerge();
	},
	
	hideTitle : function() {
		this.NODE.select('.component-section .component-section-header h3').first().hide();
	},
	
	// Protected
	
	_load	: function () {
		this._hData = this.get('hContacts');
		this._syncUI.bind(this).defer();
	},

	_buildUI	: function () {
		this.NODE	= $T.div(
			new Component_Section({sIcon:'../admin/img/template/contact_small.png', sTitle: 'Contact Details'},
				$T.table({class: 'reflex input'},
					$T.tbody({class: 'component-account-merge-contacts-mergedetails'})	
				)
			)
		);
		
		// Set details section title
		this.NODE.select('.component-section').last().oReflexComponent.set('sTitle', "Contact Details");
	},

	_syncUI	: function () {
		if (!this._hData) {
			// Need to load additional data first
			this._load();
		} else {
			// Clear merging contacts array
			this._aMergingContactIds = [];
			
			// Remove existing details controls
			var oDetailsTBody = this.NODE.select('.component-account-merge-contacts-mergedetails').first();
			oDetailsTBody.select('tr').each(Element.remove);
			
			// Add control fields to details section (hidden to start)
			this._hContactControls = {};
			for (var sField in Component_Account_Merge_Contacts.CONTACT_PROPERTY_DEFINITION) {
				var oDefinition	= Component_Account_Merge_Contacts.CONTACT_PROPERTY_DEFINITION[sField];
				var oControl 	= Control_Field.factory(oDefinition.sType, oDefinition.oConfig);
				oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				
				// Add tr to the tbody
				var oTR = $T.tr(
					$T.th(oDefinition.oConfig.sLabel)
				);
				
				// Add a td for each contact
				for (var i in this._hData)
				{
					oTR.appendChild(this._displayContactProperty(this._hData[i], sField));
				}
				
				oTR.appendChild($T.td(oControl.getElement()));
				oDetailsTBody.appendChild(oTR);
				
				// Cache the control reference
				this._hContactControls[sField] = oControl;
			}
			
			// Component is ready
			//----------------------------------------------------------------//
			this._onReady();
		}
	},
	
	_displayContactProperty : function(oContact, sField)
	{
		var mValue		= oContact[sField];
		var mContent	= null;
		var fnOnClick	= this._useContactProperty.bind(this, sField, mValue);
		switch (sField)
		{
			case 'Title':
			case 'FirstName':
			case 'LastName':
			case 'JobTitle':
			case 'Email':
			case 'Phone':
			case 'Mobile':
			case 'Fax':
				mContent = mValue;
				break;
				
			case 'DOB':
				mContent = Date.$parseDate(mValue, 'Y-m-d').$format('d/m/Y');
				break;
				
			case 'CustomerContact':
				mContent = (mValue == 1 ? 'Yes' : 'No');
				break;
				
			case 'password_contact_id':
				mContent	= $T.input({type: 'radio', name: 'password', onclick: this._setPasswordContactId.bind(this, oContact.Id)});
				fnOnClick	= this._useContactPassword.bind(this, oContact.Id, mContent);
				break;
		}
		
		return $T.td({class: 'component-account-merge-contacts-contact-property component-account-merge-contacts-contact-property-contact' + oContact.Id, onclick: fnOnClick},
			mContent
		);
	},
	
	_setPasswordContactId : function(iContactId)
	{
		this._hContactControls['password_contact_id'].setValue(iContactId);
	},
	
	_useContactPassword : function(iContactId, oRadioButton)
	{
		oRadioButton.checked = true;
		this._setPasswordContactId(iContactId);
	},
	
	_useContactProperty : function(sField, mValue)
	{
		this._hContactControls[sField].setValue(mValue);
	},
	
	_doMerge : function()
	{
		var oNewContact = {};
		var aErrors		= [];
		for (var sField in this._hContactControls) {
			try {
				this._hContactControls[sField].validate(false);
				this._hContactControls[sField].save(true);
				oNewContact[sField] = this._hContactControls[sField].getValue();
			} catch (oException) {
				aErrors.push(oException);
			}
		}
		
		if (aErrors.length)
		{
			var oErrorElement = $T.ul();
			for (var i = 0; i < aErrors.length; i++)
			{
				oErrorElement.appendChild($T.li(aErrors[i]));
			}
			
			Reflex_Popup.alert(
				$T.div({class: 'alert-validation-error'},
					oErrorElement
				),
				{sTitle: 'Validation Error', iWidth: 30}
			);
			return;
		}
		
		// Add the smallest id as the one to use
		var iMinContactId = null;
		for (var i in this._hData)
		{
			var iContactId = parseInt(i);
			if ((iMinContactId === null) || (iContactId < iMinContactId))
			{
				iMinContactId = iContactId;
			}
		}
		
		// Build list of merged contacts (to be discarded)
		var aMergedContactIds = [];
		for (var i in this._hData)
		{
			var iContactId = parseInt(i);
			if (iContactId != iMinContactId)
			{
				aMergedContactIds.push(iContactId);
			}
		}
		
		oNewContact.Id = iMinContactId;
		
		// Save the new contact
		Reflex_Popup.yesNoCancel(
			'Are you sure you want to merge these ' + (aMergedContactIds.length + 1) + ' Contacts?',
			{
				sYesIconSource	: '../admin/img/template/tick.png',
				sNoIconSource	: '../admin/img/template/decline.png',
				fnOnYes			: this._saveContact.bind(this, oNewContact, aMergedContactIds)
			}
		);
	},
	
	_saveContact : function(oMergedContact, aMergedContactIds, oResponse)
	{
		if (!oResponse || oResponse.element) {
			// No Response (or Response is an Event): Request Data
			var fnResp 		= this._saveContact.bind(this, oMergedContact, aMergedContactIds);
			var fnReq		= jQuery.json.jsonFunction(fnResp, fnResp, 'Contact', 'mergeContacts');
			this._oLoading 	= new Reflex_Popup.Loading();
			this._oLoading.display();
			fnReq(oMergedContact, aMergedContactIds);
		} else if (!oResponse.bSuccess) {
			this._oLoading.hide();
			delete this._oLoading;
			
			// Error
			Reflex_Popup.alert(oResponse.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.sDebug
			});
		} else {
			this._oLoading.hide();
			delete this._oLoading;
			
			// Success
			var fnOnComplete = this.get('fnOnComplete');
			if (fnOnComplete)
			{
				fnOnComplete();
			}
		}
	}
});

Object.extend(Component_Account_Merge_Contacts, {
	
	CONTACT_PROPERTY_DEFINITION : {
		'Title' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Title',
				mEditable	: true,
				mMandatory	: false
			}
		},
		'FirstName' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'First Name',
				mEditable	: true,
				mMandatory	: true
			}
		},
		'LastName' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Last Name',
				mEditable	: true,
				mMandatory	: true
			}
		},
		'DOB' : {
			sType 	: 'date_picker',
			oConfig	: {
				sLabel		: 'D.O.B',
				mEditable	: true,
				mMandatory	: true
			}
		},
		'JobTitle' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Job Title',
				mEditable	: true,
				mMandatory	: false
			}
		},
		'Email' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Email',
				mEditable	: true,
				mMandatory	: true
			}
		},
		'CustomerContact' : {
			sType 	: 'checkbox',
			oConfig	: {
				sLabel		: 'Allow access to all Associated Accounts',
				mEditable	: true,
				mMandatory	: false
			}
		},
		'Phone' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Phone',
				mEditable	: true,
				mMandatory	: false
			}
		},
		'Mobile' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Mobile',
				mEditable	: true,
				mMandatory	: false
			}
		},
		'Fax' : {
			sType 	: 'text',
			oConfig	: {
				sLabel		: 'Fax',
				mEditable	: true,
				mMandatory	: false
			}
		},
		'password_contact_id' : {
			sType 	: 'hidden',
			oConfig	: {
				sLabel		: 'Password',
				mEditable	: true,
				mMandatory	: true
			}
		}
	},
	
	// Public
	
	createAsPopup : function () {
		var	oComponent 			= Component_Account_Merge_Contacts.constructApply($A(arguments)),
			oPopup				= new Reflex_Popup(),
			oFooterSaveButton	= $T.button(
				$T.img({src:'../admin/img/template/tick.png', class: 'icon', alt:''}),
				$T.span('Merge')
			),
			oFooterCancelButton	= $T.button(
				$T.img({src:'../admin/img/template/decline.png', class: 'icon', alt:''}),
				$T.span('Cancel')
			);
		
		oFooterSaveButton.observe('click', oComponent.doMerge.bind(oComponent));
		oFooterCancelButton.observe('click', oPopup.hide.bind(oPopup));
		
		// Hide the header
		oComponent.hideTitle();
		
		oPopup.setTitle('Merge Contacts');
		oPopup.setIcon('../admin/img/template/contacts.png');
		oPopup.addCloseButton();
		oPopup.setFooterButtons([
			oFooterSaveButton,
			oFooterCancelButton
		], true);
		oPopup.setContent(oComponent.getNode());
		
		return oPopup;
	}
});
