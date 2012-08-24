
var Component_Collections_Severity = Class.create( 
{
	initialize : function(oContainerDiv, iSeverityId, bRenderMode, fnOnCommit, fnOnCancel)
	{
		this._oContainerDiv = oContainerDiv;
		this._iSeverityId	= (iSeverityId ? iSeverityId : null);
		this._fnOnCommit	= fnOnCommit;
		this._fnOnCancel	= fnOnCancel;
		
		this._bRenderMode			= bRenderMode;
		this._aControls 			= [];
		this._hSeverityLevelsTaken	= {};
		this._oSeverity				= null;
		
		Flex.Constant.loadConstantGroup(Component_Collections_Severity.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
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
		oNameControl.setRenderMode(this._bRenderMode);
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
		oDescriptionControl.setRenderMode(this._bRenderMode);
		this._oDescriptionControl = oDescriptionControl;
		this._aControls.push(oDescriptionControl);
		
		var oSeverityLevelControl =	Control_Field.factory(
										'text', 
										{
											sLabel		: 'Severity Level',
											fnValidate	: Reflex_Validation.digits,
											mMandatory	: true,
											mEditable	: true, 
											fnValidate	: this._validateSeverityLevel.bind(this)
										}
									);
		oSeverityLevelControl.setRenderMode(this._bRenderMode);
		this._oSeverityLevelControl = oSeverityLevelControl;
		this._aControls.push(oSeverityLevelControl);
		
		// Transfer selects
		this._oWarningSelect 		= this._createTransferSelect(this._getWarningOptions.bind(this), 5);
		this._oRestrictionSelect	= this._createTransferSelect(this._getRestrictionOptions.bind(this), 5);
		
		// Create ui content
		this._oContentDiv = $T.div({class: 'component-collections-severity'},
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
											$T.th('Severity Level'),
											$T.td(this._oSeverityLevelControl.getElement())
										),
										$T.tr(
											$T.th('Warnings'),
											$T.td(this._oWarningSelect)
										),
										$T.tr({class: 'component-collections-severity-add-warning'},
											$T.th(''),
											$T.td(
												$T.button({class: 'icon-button'},
													$T.img({src: '../admin/img/template/new.png'}),
													$T.span('Add Warning')
												).observe('click', this._addWarning.bind(this))
											)
										),
										$T.tr(
											$T.th('Restrictions'),
											$T.td(this._oRestrictionSelect)	
										)
									)
								),
								$T.div({class: 'component-collections-severity-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/page_white_edit.png'}),
										$T.span('Save as Draft')
									).observe('click', this._saveAsDraft.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save and Commit')
									).observe('click', this._saveAndCommit.bind(this)),
									(this._fnOnCancel ?	$T.button({class: 'icon-button'},
															$T.img({src: '../admin/img/template/delete.png'}),
															$T.span('Cancel')
														).observe('click', this._cancel.bind(this)) : null)
								)
							);
		
		var aButtons 		= this._oContentDiv.select('.component-collections-severity-buttons > button');
		this._oDraftButton 	= aButtons[0];
		this._oCommitButton	= aButtons[1];
		this._oAddWarningTR	= this._oContentDiv.select('.component-collections-severity-add-warning').first();
		
		this._oWarningMessageDiv =	$T.div({class: 'component-collections-severity-warning-message'});
		
		// Attach content
		this._oContainerDiv.appendChild(this._oContentDiv);
		
		if (this._iSeverityId)
		{
			// Hide the buttons, they will be reshown if the loaded severity has a working status of draft
			this._oDraftButton.hide();
			this._oCommitButton.hide();
			this._oAddWarningTR.hide();
			
			this._loadFromSeverity(this._iSeverityId);
		}
	},
	
	_saveAsDraft : function()
	{
		this._save($CONSTANT.WORKING_STATUS_DRAFT);
	},

	_saveAndCommit : function()
	{
		this._save($CONSTANT.WORKING_STATUS_ACTIVE);
	},
	
	_save : function(iWorkingStatusId, oResponse)
	{
		if (!oResponse)
		{
			// Remove the severity level validation function (mandatory-ness will still be checked)
			this._oSeverityLevelControl.setValidateFunction(null);
			
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aControls.length; i++)
			{
				try
				{
					this._aControls[i].validate(false);
					this._aControls[i].save(true);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			// Validate (?) the restriction and warning linkages
			var aWarningIds 	= this._getValuesFromTransferSelect(this._oWarningSelect);
			var aRestrictionIds	= this._getValuesFromTransferSelect(this._oRestrictionSelect);
			
			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Component_Collections_Severity._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				id							: this._iSeverityId,
				name 						: this._oNameControl.getValue(),
				description 				: this._oDescriptionControl.getValue(),
				severity_level				: this._oSeverityLevelControl.getValue(),
				working_status_id			: iWorkingStatusId,
				collection_warning_ids		: aWarningIds,
				collection_restriction_ids	: aRestrictionIds
			};
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this, iWorkingStatusId);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Severity', 'createSeverity');
			fnReq(oDetails);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Severity._ajaxError(oResponse, 'Could not save the Severity');
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (iWorkingStatusId == $CONSTANT.WORKING_STATUS_ACTIVE)
		{
			this._bRenderMode = false;
			this._loadFromSeverity(oResponse.iSeverityId);
			Reflex_Popup.alert('Severity committed successfully');
			
			if (this._fnOnCommit)
			{
				this._fnOnCommit(oResponse.iSeverityId);
			}
		}
	},

	_loadFromSeverity : function(iSeverityId, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._loadFromSeverity.bind(this, iSeverityId);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Severity', 'getExtendedDetailsForId');
			fnReq(iSeverityId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Severity._ajaxError(oResponse);
			return;
		}
		
		var oSeverity = oResponse.oSeverity;
		
		// Set control field values
		this._oNameControl.setValue(oSeverity.name);
		this._oDescriptionControl.setValue(oSeverity.description);
		this._oSeverityLevelControl.setValue(oSeverity.severity_level);
		
		// Special handling for the transfer selects
		for (var i = 0; i < oSeverity.collection_warning_ids.length; i++)
		{
			var oOption = this._getFromItem(this._oWarningSelect, oSeverity.collection_warning_ids[i]);
			if (oOption)
			{
				this._transferTo(this._oWarningSelect, oOption);
			}
		}
		
		for (var i = 0; i < oSeverity.collection_restriction_ids.length; i++)
		{
			var oOption = this._getFromItem(this._oRestrictionSelect, oSeverity.collection_restriction_ids[i]);
			if (oOption)
			{
				this._transferTo(this._oRestrictionSelect, oOption);
			}
		}
		
		// Update render modes
		this._oNameControl.setRenderMode(this._bRenderMode);
		this._oDescriptionControl.setRenderMode(this._bRenderMode);
		this._oSeverityLevelControl.setRenderMode(this._bRenderMode);
		this._setTransferSelectRenderMode(this._oWarningSelect, this._bRenderMode);
		this._setTransferSelectRenderMode(this._oRestrictionSelect, this._bRenderMode);
		
		if (!this._bRenderMode || (this._oSeverity && (this._oSeverity.working_status_id != $CONSTANT.WORKING_STATUS_DRAFT)))
		{
			this._oDraftButton.hide();
			this._oCommitButton.hide();
			this._oAddWarningTR.hide();
		}
		else
		{
			this._oDraftButton.show();
			this._oCommitButton.show();
			this._oAddWarningTR.show();
		}
		
		this._iSeverityId	= oSeverity.id;
		this._oSeverity 	= oSeverity;
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
	},
	
	_createTransferSelect : function(fnPopulate, iSize)
	{
		var oContainer			= $T.div({class: 'component-collections-severity-transfer'});
		var oFromSelect 		= $T.select({size: iSize, multiple: true});
		var oToSelect			= $T.select({size: iSize, multiple: true});
		var oTransferToButton	=	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/icon_moveright.png'})
									).observe('click', this._transferTo.bind(this, oContainer, null));
		var oTransferFromButton	= 	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/icon_moveleft.png'})
									).observe('click', this._transferFrom.bind(this, oContainer, null));
		var oViewModeDiv		= $T.div({class: 'component-collections-severity-transfer-view'});
		
		oContainer.appendChild(
			$T.ul({class: 'reset horizontal'},
				$T.li(
					$T.div({class: 'component-collections-severity-transfer-select-title'},
						'Unassigned'
					),
					oFromSelect
				),
				$T.li(
					$T.div({class: 'component-collections-severity-transfer-buttons'},
						$T.div(oTransferToButton),
						$T.div(oTransferFromButton)
					)
				),
				$T.li(
					$T.div({class: 'component-collections-severity-transfer-select-title'},
						'Assigned'
					),
					oToSelect
				)
			)
		);
		oContainer.appendChild(oViewModeDiv);
		
		fnPopulate(this._populateTransferFromSelect.bind(this, oContainer));
		this._setTransferSelectRenderMode(oContainer, this._bRenderMode);
		
		return oContainer;
	},
	
	_populateTransferFromSelect : function(oSelectContainer, aOptions)
	{
		var aSelects	= oSelectContainer.select('select');
		var oFromSelect = aSelects.first();
		var oToSelect 	= aSelects.last();
		while (oFromSelect.firstChild)
		{
			oFromSelect.firstChild.remove();
		}
		
		var bCheckForMatchingToItems = !!oToSelect.options.length;
		for (var i = 0; i < aOptions.length; i++)
		{
			if (bCheckForMatchingToItems)
			{
				var oToItem = this._getToItem(oSelectContainer, aOptions[i].value);
				if (oToItem)
				{
					// Update the positon (and skip adding this to the from select) of the to 
					// item so that if it is brought back it slots into the right position.
					oToItem.iPosition = i;
					continue;
				}
			}
			
			aOptions[i].iPosition = i;
			oFromSelect.appendChild(aOptions[i]);
		}
	},
	
	_transferTo : function(oSelectContainer, oOption)
	{
		var aSelects	= oSelectContainer.select('select');
		var oFromSelect = aSelects.first();
		var oToSelect 	= aSelects.last();
		
		if (oOption)
		{
			oToSelect.appendChild(oOption);
		}
		else
		{
			for (var i = 0; i < oFromSelect.options.length; i++)
			{
				oOption = oFromSelect.options[i];
				if (oOption.selected)
				{
					oToSelect.appendChild(oOption);
					i--;
				}
			}
		}
	},
	
	_transferFrom : function(oSelectContainer)
	{
		var aSelects	= oSelectContainer.select('select');
		var oFromSelect = aSelects.first();
		var oToSelect 	= aSelects.last();
		if (oToSelect.selectedIndex == -1)
		{
			return;
		}
		
		for (var i = 0; i < oToSelect.options.length; i++)
		{
			oOption = oToSelect.options[i];
			if (oOption.selected)
			{
				// Find the option in the from select that the one coming from the to select should sit behind
				var bMoved = false;
				for (var j = 0; j < oFromSelect.options.length; j++)
				{
					if (oFromSelect.options[j].iPosition > oOption.iPosition)
					{
						oFromSelect.insertBefore(oOption, oFromSelect.options[j]);
						bMoved = true;
						break;
					}
				}
				
				if (!bMoved)
				{
					oFromSelect.appendChild(oOption);
				}
				
				i--;
			}
		}
	},
	
	_getFromItem : function(oSelectContainer, mValue)
	{
		return this._getSelectItem(oSelectContainer.select('select').first(), mValue);
	},
	
	_getToItem : function(oSelectContainer, mValue)
	{
		return this._getSelectItem(oSelectContainer.select('select').last(), mValue);
	},
	
	_getSelectItem : function(oSelect, mValue)
	{
		for (var i = 0; i < oSelect.options.length; i++)
		{
			if (oSelect.options[i].value == mValue)
			{
				return oSelect.options[i];
			}
		}
	},
	
	_addWarning : function()
	{
		new Popup_Collections_Warning(this._warningAdded.bind(this));
	},
	
	_warningAdded : function()
	{
		this._getWarningOptions(this._populateTransferFromSelect.bind(this, this._oWarningSelect));
	},
	
	_getValuesFromTransferSelect : function(oSelectContainer)
	{
		var oToSelect 	= oSelectContainer.select('select').last();
		var aValues		= [];
		for (var i = 0; i < oToSelect.options.length; i++)
		{
			aValues.push(parseInt(oToSelect.options[i].value));
		}
		return aValues;
	},

	_validateSeverityLevel : function(mValue)
	{
		var iLevel = parseInt(mValue);
		if (isNaN(iLevel))
		{
			return false;
		}
		
		if (!this._hSeverityLevelsTaken[iLevel])
		{
			this._hSeverityLevelsTaken[iLevel] = Component_Collections_Severity.SEVERITY_LEVEL_TAKEN_CHECKING;
			this._isSeverityLevelTaken(iLevel);
		}
		else
		{
			var iTaken = this._hSeverityLevelsTaken[iLevel];
			delete this._hSeverityLevelsTaken[iLevel];
			return (iTaken == Component_Collections_Severity.SEVERITY_LEVEL_TAKEN_NO);
		}
	},
	
	_isSeverityLevelTaken : function(iLevel, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._isSeverityLevelTaken.bind(this, iLevel);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Severity', 'isSeverityLevelTaken');
			fnReq(iLevel, this._iSeverityId);
			return false;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Severity._ajaxError(oResponse);
			return false;
		}
		
		this._hSeverityLevelsTaken[iLevel] = oResponse.bTaken ? Component_Collections_Severity.SEVERITY_LEVEL_TAKEN_YES : Component_Collections_Severity.SEVERITY_LEVEL_TAKEN_NO;
		this._oSeverityLevelControl.validate();
	},
	
	_setTransferSelectRenderMode : function(oSelectContainer, bRenderMode)
	{
		var aSelects	= oSelectContainer.select('select');
		var oFromSelect = aSelects.first();
		var oToSelect 	= aSelects.last();
		
		var oEditModeUL			= oSelectContainer.select('ul.reset.horizontal').first();
		var oViewModeDiv		= oSelectContainer.select('.component-collections-severity-transfer-view').first();
		oViewModeDiv.innerHTML 	= '';
		oViewModeDiv.hide();
		oEditModeUL.hide();
		
		if (bRenderMode)
		{
			// Edit
			oEditModeUL.show();
		}
		else
		{
			// View
			if (oToSelect.options.length)
			{
				// List the selected options
				for (var i = 0; i < oToSelect.options.length; i++)
				{
					oViewModeDiv.appendChild($T.div(oToSelect.options[i].sName));
				}
			}
			else
			{
				// No selected options
				oViewModeDiv.appendChild($T.div('None'));
			}
			oViewModeDiv.show();
		}
	},

	_getWarningOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._getWarningOptions.bind(this, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Warning', 'getAll');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Severity._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var i in oResponse.aResults)
		{
			var oMessageIcon 	= $T.img({class: 'component-collections-severity-warning-message-icon', src: '../admin/img/template/magnifier.png'});
			var oOption 		= 	$T.option({value: i},
										oMessageIcon,
										$T.span(oResponse.aResults[i].name)
									);
			oOption.sName 		= oResponse.aResults[i].name;
			oOption.observe('mouseover', this._showWarningMessage.bind(this, oMessageIcon, oResponse.aResults[i].message));
			oOption.observe('mouseout', this._hideWarningMessage.bind(this));
			aOptions.push(oOption);
		}
		
		fnCallback(aOptions);
	}, 
	
	_getRestrictionOptions : function(fnCallback)
	{
		if (!Flex.Constant.arrConstantGroups.collection_restriction)
		{
			Flex.Constant.loadConstantGroup('collection_restriction', this._getRestrictionOptions.bind(this, fnCallback));
			return;
		}
		
		var aOptions 	= [];
		var aConstants	= Flex.Constant.arrConstantGroups.collection_restriction;
		for (var i in aConstants)
		{
			var oOption =	$T.option({value: i},
								$T.span(aConstants[i].Name)
							);
			oOption.sName = aConstants[i].Name;
			aOptions.push(oOption);
		}
		
		fnCallback(aOptions);
	},
	
	_showWarningMessage : function(oIcon, sMessage)
	{
		var oPositionedOffset 				= oIcon.positionedOffset();
		this._oWarningMessageDiv.style.left	= (oPositionedOffset.left + oIcon.getWidth()) + 'px';
		this._oWarningMessageDiv.style.top 	= (oPositionedOffset.top + oIcon.getHeight()) + 'px';
		this._oWarningMessageDiv.innerHTML 	= sMessage;
		this._oContentDiv.appendChild(this._oWarningMessageDiv);
	},
	
	_hideWarningMessage : function()
	{
		this._oWarningMessageDiv.remove();
	}
});

// Static

Object.extend(Component_Collections_Severity, 
{
	REQUIRED_CONSTANT_GROUPS : ['collection_restriction', 'working_status'],
	
	SEVERITY_LEVEL_TAKEN_CHECKING	: 1,
	SEVERITY_LEVEL_TAKEN_YES		: 2,
	SEVERITY_LEVEL_TAKEN_NO			: 3,
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Component_Collections_Severity._validationError(oResponse.aErrors);
		}
		else
		{
			// Exception
			jQuery.json.errorPopup(oResponse, sMessage);
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
	}
});
