
var Popup_Correspondence_Template = Class.create(Reflex_Popup, {
	initialize : function ($super, iTemplateId, fnOnComplete) {
		$super(50);
		
		this._iTemplateId = iTemplateId;
		this._fnOnComplete = fnOnComplete;
		this._aControls = [];
		this._hSourceTypeControls = {};
		this._bRenderMode = (!!iTemplateId ? Control_Field.RENDER_MODE_VIEW : Control_Field.RENDER_MODE_EDIT);
		
		this._oLoading = new Reflex_Popup.Loading();
		this._oLoading.display();
		
		Flex.Constant.loadConstantGroup(Popup_Correspondence_Template.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function () {
		// Create controls
		var oNameControl = Control_Field.factory('text', {
			sLabel : 'Name',
			mEditable : true,
			mMandatory : true,
			fnValidate : Reflex_Validation.Exception.stringOfLength.curry(0, 255)
		});
		var oDescriptionControl = Control_Field.factory('text', {
			sLabel : 'Description',
			mEditable : true,
			mMandatory : true,
			fnValidate : Reflex_Validation.Exception.stringOfLength.curry(0, 510)
		});
		var oSourceTypeControl = Control_Field.factory('select', {
			sLabel : 'Source Type',
			mEditable : true,
			mMandatory : true,
			fnPopulate : Popup_Correspondence_Template._getSourceTypeOptions
		});
		oSourceTypeControl.addOnChangeCallback(this._sourceTypeChange.bind(this, oSourceTypeControl));
		
		this._oAdditionalColumnControl = new Component_Correspondence_Template_Additional_Columns(this._bRenderMode);
		
		this._hCarrierModuleControls = {};
		var oCarrierModuleTBody = $T.tbody();
		for (var iId in Flex.Constant.arrConstantGroups.correspondence_delivery_method) {
			var sName = Flex.Constant.arrConstantGroups.correspondence_delivery_method[iId].Name;
			var oControl = Control_Field.factory('select', {
				sLabel : sName,
				mMandatory : false, 
				mEditable : true,
				fnPopulate : Popup_Correspondence_Template._getCarrierModuleOptions
			});
			oControl.setRenderMode(this._bRenderMode);
			oCarrierModuleTBody.appendChild(
				$T.tr(
					$T.th(sName),
					$T.td(oControl.getElement())
				) 
			);
			this._hCarrierModuleControls[iId] = oControl;
		}
		
		oNameControl.setRenderMode(this._bRenderMode);
		oDescriptionControl.setRenderMode(this._bRenderMode);
		oSourceTypeControl.setRenderMode(this._bRenderMode);
		
		this._aControls.push(oNameControl);
		this._aControls.push(oDescriptionControl);
		this._aControls.push(oSourceTypeControl);
		
		var oContentDiv = $T.div({'class': 'popup-correspondence-template'},
			$T.table({'class': 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th('Name'),
						$T.td(oNameControl.getElement())
					),
					$T.tr(
						$T.th('Description'),
						$T.td(oDescriptionControl.getElement())
					),
					$T.tr(
						$T.th('Source Type'),
						$T.td(oSourceTypeControl.getElement())
					),
					$T.tr({'class': 'popup-correspondence-template-additional-columns'},
						$T.th('Additional Columns'),
						$T.td(this._oAdditionalColumnControl.getElement())
					),
					$T.tr(
						$T.th('Template Carrier Modules'),
						$T.td(
							$T.table({'class': 'popup-correspondence-template-carrier-modules'},
								oCarrierModuleTBody
							),
							this._bRenderMode ? $T.button({'class': 'popup-correspondence-template-new-templatecarriermodule icon-button'},
								$T.img({src: '../admin/img/template/new.png'}),
								$T.span('Add New Template Carrier Module')
							).observe('click', this._addTemplateCarrierModule.bind(this)) : null
						)
					)
				)
			),
			$T.div({'class': 'popup-correspondence-template-buttons'},
					this._bRenderMode ? $T.button({'class': 'icon-button'},
					$T.img({src: '../admin/img/template/approve.png'}),
					$T.span('Save')
				).observe('click', this._doSave.bind(this)) : null,
				$T.button({'class': 'icon-button'},
					$T.img({src: '../admin/img/template/decline.png'}),
					$T.span('Cancel')
				).observe('click', this.hide.bind(this))
			)
		);
		this._oAdditionalColumnsRow = oContentDiv.select('.popup-correspondence-template-additional-columns').first();
		this.setTitle((!!this._iTemplateId ? 'View' : 'New') + ' Correspondence Template');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (this._iTemplateId) {
			this._loadTemplate();
		}
	},
	
	_loadTemplate : function (oResponse) {
		if (!oResponse) {
			var fnResp = this._loadTemplate.bind(this);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'Correspondence_Template', 'getEditingDetailsForId');
			fnReq(this._iTemplateId);
			return;
		}
		
		if (!oResponse.bSuccess) {
			Popup_Correspondence_Template._ajaxError(oResponse);
			return;
		}
		
		// Template details
		this._aControls[0].setValue(oResponse.oTemplate.name);
		this._aControls[1].setValue(oResponse.oTemplate.description);
		this._aControls[2].setValue(oResponse.oTemplate.correspondence_source.correspondence_source_type_id);
		
		// Source type details
		this._sourceTypeChange(this._aControls[2]);
		for (var sField in oResponse.oTemplate.correspondence_source.details) {
			if (this._hSourceTypeControls[sField]) {
				this._hSourceTypeControls[sField].oControl.setValue(oResponse.oTemplate.correspondence_source.details[sField]);
			}
		}
		
		// Additional columns
		this._oAdditionalColumnControl.setColumns(oResponse.aColumns);
		
		// Carrier modules
		for (var iId in this._hCarrierModuleControls) {
			this._hCarrierModuleControls[iId].setValue(oResponse.aTemplateCarrierModules[iId] ? oResponse.aTemplateCarrierModules[iId] : null);
		}
	},
	
	_doSave : function () {
		// Validate base controls
		var aErrors = [];
		for (var i = 0; i < this._aControls.length; i++) {
			try {
				this._aControls[i].validate(false);
				this._aControls[i].save(true);
			} catch (oException) {
				aErrors.push(oException);
			}
		}
		
		// Validate source type controls
		var oSourceDetails = {};
		for (var sField in this._hSourceTypeControls) {
			try {
				this._hSourceTypeControls[sField].oControl.validate(false);
				oSourceDetails[sField] = this._hSourceTypeControls[sField].oControl.getElementValue();
			} catch (oException) {
				aErrors.push(oException);
			}
		}
					
		if (aErrors.length) {
			// There were validation errors, show all in a popup
			Popup_Correspondence_Template._validationError(aErrors);
			return;
		}
		
		// Build the details object
		var oDetails = {
			id : this._iTemplateId,
			name : this._aControls[0].getValue(),
			description : this._aControls[1].getValue(),
			correspondence_source_type_id : parseInt(this._aControls[2].getValue(), 10),
			correspondence_source_details : oSourceDetails,
			columns : this._oAdditionalColumnControl.getColumns(),
			template_carrier_modules : {}
		};
		
		// Add template carrier module values to details
		for (var iId in this._hCarrierModuleControls) {
			var sValue = this._hCarrierModuleControls[iId].getElementValue();
			if (sValue !== null) {
				oDetails.template_carrier_modules[iId] = parseInt(sValue, 10);
			}
		}
		
		// Confirmation popup
		Reflex_Popup.yesNoCancel(
			$T.div({'class': 'alert-content'},
				$T.div('If you save this Template, no more changes can be made to it.'),
				$T.div('Are you sure you wish to save?')
			), 
			{fnOnYes: this._makeSaveRequest.bind(this, oDetails)}
		);
	},
	
	_makeSaveRequest : function (oDetails, oResponse) {
		if (!oResponse) {
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp = this._makeSaveRequest.bind(this, oDetails);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'Correspondence_Template', 'saveTemplate');
			fnReq(oDetails);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess) {
			Popup_Correspondence_Template._ajaxError(oResponse, 'Could not save the Record');
			return;
		}
		
		this.hide();
		
		if (this._fnOnComplete) {
			this._fnOnComplete(oResponse.iRecordId);
		}
	},
	
	_sourceTypeChange : function (oControl) {
		// Remove all current source type controls
		for (var sField in this._hSourceTypeControls) {
			this._hSourceTypeControls[sField].oTR.remove();
			this._hSourceTypeControls[sField].oTR = null;
			this._hSourceTypeControls[sField].oControl = null;
			delete this._hSourceTypeControls[sField];
		}
		
		// Create new controls
		var iSourceTypeId = parseInt(oControl.getElementValue(), 10);
		this._hSourceTypeControls = {};
		switch (iSourceTypeId) {
			case $CONSTANT.CORRESPONDENCE_SOURCE_TYPE_SQL:
				oControl = Control_Field.factory('textarea', {
					sLabel : 'SQL Syntax',
					mMandatory : true,
					mEditable : true,
					rows : 7,
					sExtraClass : 'popup-correspondence-template-sql-syntax'
				});
				oControl.cancelFocusShiftOnTab();
				oControl.setRenderMode(this._bRenderMode);
				this._hSourceTypeControls.sql_syntax = {oControl: oControl}; 
				break;
		}
		
		// Add new controls to table
		var oTBody = this._oAdditionalColumnsRow.up();
		for (sField in this._hSourceTypeControls) {
			var oTR = $T.tr(
				$T.th(this._hSourceTypeControls[sField].oControl.getLabel()),
				$T.td(this._hSourceTypeControls[sField].oControl.getElement())
			);
			oTBody.insertBefore(oTR, this._oAdditionalColumnsRow);
			this._hSourceTypeControls[sField].oTR = oTR;
		}
	},
	
	_addTemplateCarrierModule : function () {
		new Popup_Correspondence_Template_Carrier_Module(this._templateCarrierModuleAdded.bind(this));
	},
	
	_templateCarrierModuleAdded : function () {
		// Re-populate the carrier module selects
		for (var i in this._hCarrierModuleControls) {
			var oControl = this._hCarrierModuleControls[i];
			var mValue = oControl.getElementValue();
			oControl.populate();
			oControl.setValue(mValue);
		}
	}
});

Object.extend(Popup_Correspondence_Template, {
	REQUIRED_CONSTANT_GROUPS : ['correspondence_delivery_method', 'correspondence_source_type'],
	
	_ajaxError : function (oResponse, sMessage) {
		if (oResponse.aErrors) {
			// Validation errors
			Popup_Correspondence_Template._validationError(oResponse.aErrors);
		} else {
			// Exception
			Reflex_Popup.alert(
				(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.',
				{sTitle: 'Error', sDebugContent: oResponse.sDebug}
			);
		}
	},
	
	_validationError : function (aErrors) {
		var oErrorElement = $T.ul();
		for (var i = 0; i < aErrors.length; i++) {
			oErrorElement.appendChild($T.li(aErrors[i]));
		}
		
		Reflex_Popup.alert(
			$T.div(
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	},
	
	_getSourceTypeOptions : function (fnCallback, oResponse) {
		if (!oResponse) {
			var fnResp = Popup_Correspondence_Template._getSourceTypeOptions.curry(fnCallback);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'Correspondence_Template', 'getSelectableSourceTypes');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess) {
			Popup_Correspondence_Template._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [],
			i, il;
		for (i = 0, il = oResponse.aSourceTypes.length; i < il; i++) {
			aOptions.push(
				$T.option({value: i},
					oResponse.aSourceTypes[i].name 
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getCarrierModuleOptions : function (fnCallback, oResponse) {
		if (!oResponse) {
			var fnResp = Popup_Correspondence_Template._getCarrierModuleOptions.curry(fnCallback);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'Correspondence_Template', 'getCorrespondenceTemplateCarrierModules');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess) {
			Popup_Correspondence_Template._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [],
			oModule,
			i, il;
		for (i = 0, il = oResponse.aModules.length; i < il; i++) {
			oModule = oResponse.aModules[i];
			aOptions.push(
				$T.option({value: i},
					oModule.template_code + ' - ' + oModule.carrier_name + ' : ' + oModule.carrier_module_type_name + (oModule.customer_group_name !== null ? ' (' + oModule.customer_group_name + ')' : '')
				)
			);
		}
		fnCallback(aOptions);
	}
});
