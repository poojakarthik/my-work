
var Component_Collections_Event_Type = Class.create( 
{
	initialize : function(oContainerDiv, fnOnComplete, fnOnCancel, oLoadingPopup)
	{
		this._oContainerDiv = oContainerDiv;
		this._fnOnComplete	= fnOnComplete;
		this._fnOnCancel	= fnOnCancel;
		this._oLoadingPopup	= oLoadingPopup;
		
		this._aControls = [];
		
		this._buildUI();
	},
	
	_buildUI : function(bConstantsLoaded, bImplementationsLoaded)
	{
		if (!bConstantsLoaded)
		{
			Flex.Constant.loadConstantGroup(Component_Collections_Event_Type._aRequiredConstantGroups, this._buildUI.bind(this, true));
			return;
		}
		
		if (!bImplementationsLoaded)
		{
			Component_Collections_Event_Type._getAllEventTypeImplementations(this._buildUI.bind(this, true, true));
			return;
		}
		
		// Create control fields
		var oNameControl = 	Control_Field.factory(
								'text', 
								{
									sLabel		: 'Name',
									fnValidate	: Reflex_Validation.stringOfLength.curry(null, 256),
									mMandatory	: true,
									mEditable	: true
								}
							);
		oNameControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oNameControl = oNameControl;
		this._aControls.push(oNameControl);
		
		var oDescriptionControl = 	Control_Field.factory(
										'text', 
										{
											sLabel		: 'Description',
											fnValidate	: Reflex_Validation.stringOfLength.curry(null, 256),
											mMandatory	: true,
											mEditable	: true
										}
									);
		oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oDescriptionControl = oDescriptionControl;
		this._aControls.push(oDescriptionControl);
		
		var oImplementationControl = 	Control_Field.factory(
											'select', 
											{
												sLabel		: 'Implementation',
												mMandatory	: true,
												mEditable	: true,
												fnPopulate	: Component_Collections_Event_Type._getConstantOptions.curry('collection_event_type_implementation', null)
											}
										);
		oImplementationControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oImplementationControl.addOnChangeCallback(this._implementationChange.bind(this));
		this._oImplementationControl = oImplementationControl;
		this._aControls.push(oImplementationControl);
		
		var oInvocationControl = 	Control_Field.factory(
										'select', 
										{
											sLabel		: 'Is Manual',
											mMandatory	: true,
											mEditable	: true,
											fnPopulate	: this._getInvocationOptions.bind(this)
										}
									);
		oInvocationControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oInvocationControl = oInvocationControl;
		this._aControls.push(oInvocationControl);
		
		// Create ui content
		this._oContentDiv = $T.div({class: 'component-collections-event-type'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Name'),
											$T.td(this._oNameControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(this._oDescriptionControl.getElement())
										),
										$T.tr(
											$T.th('Implementation'),
											$T.td(this._oImplementationControl.getElement())
										),
										$T.tr(
											$T.th('Is Manual'),
											$T.td(this._oInvocationControl.getElement())
										)
									)
								),
								$T.div({class: 'component-collections-event-type-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/delete.png'}),
										$T.span('Cancel')
									).observe('click', this._cancel.bind(this))
								)
							);
		
		// Attach content
		this._oContainerDiv.appendChild(this._oContentDiv);
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aControls.length; i++)
			{
				try
				{
					this._aControls[i].validate(false);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Component_Collections_Event_Type._validationError(aErrors);
				return;
			}
			
			// Build the details object using base controls
			var iInvocationId = parseInt(this._oInvocationControl.getElementValue());
			if (iInvocationId === Component_Collections_Event_Type.OPTIONAL_INVOCATION)
			{
				iInvocationId = null;
			}
			
			var oDetails = 	
			{
				name 									: this._oNameControl.getElementValue(),
				description 							: this._oDescriptionControl.getElementValue(),
				collection_event_type_implementation_id	: parseInt(this._oImplementationControl.getElementValue()),
				collection_event_invocation_id	 		: iInvocationId
			};
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Type', 'createEventType');
			fnReq(oDetails);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event_Type._ajaxError(oResponse, 'Could not save the Event Type');
			return;
		}
		
		Reflex_Popup.alert('Event Type saved successfully');
		
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iEventTypeId);
		}
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
	},
	
	_getInvocationOptions : function(fnCallback)
	{
		var aOptions 		= [];
		var oImplementation	= this._getSelectedImplementation();
		if (oImplementation && oImplementation.enforced_collection_event_invocation_id)
		{
			// The implementation has an enforced invocation, only allowed option
			aOptions.push(
				$T.option({value: Component_Collections_Event_Type.OPTIONAL_INVOCATION},
					'Default (' + Flex.Constant.arrConstantGroups.collection_event_invocation[oImplementation.enforced_collection_event_invocation_id].Name + ')'
				)
			);
		}
		else
		{
			// Add no (optional) invocation item
			aOptions.push(
				$T.option({value: Component_Collections_Event_Type.OPTIONAL_INVOCATION},
					'Optional'
				)
			);
			
			// Show all invocation options
			var aData = Flex.Constant.arrConstantGroups.collection_event_invocation;
			for (var i in aData)
			{
				aOptions.push(
					$T.option({value : i},
						aData[i].Name	
					)
				);
			}
		}
		
		fnCallback(aOptions);
	},
	
	_getSelectedImplementation : function()
	{
		var iImplementationId = parseInt(this._oImplementationControl.getElementValue());
		return Component_Collections_Event_Type._hEventTypeImplementations[iImplementationId];
	},
	
	_implementationChange : function()
	{
		this._oInvocationControl.populate();
	}
});

// Static

Object.extend(Component_Collections_Event_Type, 
{
	OPTIONAL_INVOCATION : 0,
	
	_hEventTypeImplementations	: null,
	_aRequiredConstantGroups 	: ['collection_event_invocation', 'collection_event_type_implementation'],
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Component_Collections_Event_Type._validationError(oResponse.aErrors);
		}
		else
		{
			// Exception
			Reflex_Popup.alert(
				(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
				{sTitle: 'Error'}
			);
		}
	},
	
	_validationError : function(aErrors)
	{
		var oErrorElement = $T.ul();
		for (var i = 0; i < aErrors.length; i++)
		{
			oErrorElement.appendChild($T.li(aErrors[i]));
		}
		
		Reflex_Popup.alert(
			$T.div({class: 'alert-validation-error'},
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	},
	
	_getAllEventTypeImplementations : function(fnCallback, oResponse)
	{
		if (Component_Collections_Event_Type._hEventTypeImplementations)
		{
			// Already loaded
			setTimeout(fnCallback, 10);
			return;
		}
		
		if (!oResponse)
		{
			var fnResp 	= Component_Collections_Event_Type._getAllEventTypeImplementations.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Type', 'getAllEventTypeImplementations');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event_Type._ajaxError(oResponse);
			return;
		}
		
		Component_Collections_Event_Type._hEventTypeImplementations = oResponse.aEventTypeImplementations;
		
		if (fnCallback)
		{
			fnCallback();
		}
	},
	
	_getConstantOptions : function(sConstantGroup, sFieldName, fnCallback)
	{
		// Create options & callback
		var aData 		= Flex.Constant.arrConstantGroups[sConstantGroup];
		var aOptions	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i][sFieldName ? sFieldName : 'Name']
				)
			);
		}
		fnCallback(aOptions);
	}
});
