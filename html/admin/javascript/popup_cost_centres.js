
var Popup_Cost_Centres	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(40);
		
		// This array to hold a reference to the LI in the main UL for each cost centre
		this.hTRMap 				= {};
		this.aNewTRArray 			= [];
		this.iCostCentreCount 		= 0;
		this.iAccountId 			= iAccountId;
		this.oTBody 				= null;
		this.oAddNewCostCentreTR	= null;
		
		this._buildUI();
	},

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// Make AJAX Request
			this._getCostCentres	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Account', 'getCostCentres');
			this._getCostCentres(this.iAccountId);
			return;
		}
		
		if (oResponse.Success == false)
		{
			jQuery.json.errorPopup(oResponse, null, this.hide.bind(this));
			return;
		}
		
		// Build UI
		var oContent 	=	$T.div({class: 'cost-centre-list'},
								$T.table({class: 'reflex'},
									$T.caption(
										$T.div({id: 'caption_bar', class: 'caption_bar'},
											$T.div({id: 'caption_title', class: 'caption_title'},
												'Cost Centres'
											)
										)
									),
									$T.thead(
										$T.tr(
											$T.th(),
											$T.th()
										)
									)
								),
								$T.div(
									$T.table({class: 'reflex'},
										$T.colgroup(
											$T.col({class: 'cost-centre-name-col'}),
											$T.col({class: 'cost-centre-buttons-col'})
										),
										$T.tbody({class: 'alternating'})
									)
								),
								$T.table({class: 'reflex'},
									$T.tfoot(
										$T.tr(
											$T.th(),
											$T.th()
										)
									)
								),
								$T.button(
									$T.img({src: Popup_Cost_Centres.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
									$T.span('Save')
								),
								$T.button(
									$T.img({src: Popup_Cost_Centres.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
									$T.span('Cancel')
								)
							);
		this.oTBody	= oContent.select('tbody').first();
		
		// Set the save buttons event handler
		var oSaveButton	= oContent.select( 'button' ).last().previous();
		oSaveButton.observe('click', this._saveChanges.bind(this));
		
		// Set the cancel buttons event handler
		var oCancelButton = oContent.select( 'button' ).last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));
		
		// Add all cost centres from response
		var aCostCentres = jQuery.json.arrayAsObject(oResponse.aCostCentres);
		var iCostCentreCount = 0;
		
		for (var i in aCostCentres)
		{
			this._addCostCentre(aCostCentres[i].Id, aCostCentres[i].Name);
		}
		
		// Create the 'Add' row
		this.oAddNewCostCentreTR = $T.tr(
										$T.td({class: 'cost-centre-name add', colspan: '2'},
											$T.img({src: Popup_Cost_Centres.ADD_IMAGE_SOURCE, alt: 'Add', title: 'Add'}),
											$T.span(
												'Click to add a new Cost Centre...'
											)
										)
									);
		this.oAddNewCostCentreTR.observe('click', this._addCostCentre.bind(this, null, '', true));
		
		this.oTBody.appendChild(this.oAddNewCostCentreTR);
		this.setTitle('Manage Cost Centres');
		this.setIcon('../admin/img/template/building.png');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();
	},
	
	_showCancelConfirmation	: function()
	{
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},
	
	_addCostCentre	: function(iId, sName, bInEditMode)
	{
		this.iCostCentreCount++;
		
		// Attach a new TR to the main TBODY
		var oNewTR 		=	$T.tr(
								$T.td({class: 'cost-centre-name'},
									$T.span(
										sName
									),
									$T.input({type: 'text', style: 'display: none', value: sName})
								),
								$T.td({class: 'cost-centre-buttons'},
									$T.img({src: Popup_Cost_Centres.EDIT_IMAGE_SOURCE, alt: 'Edit', title: 'Edit'})
								)
							);
		var mCostCentre	= (iId != null ? iId : oNewTR);
		this.oTBody.insertBefore(oNewTR, this.oAddNewCostCentreTR);
		
		// Bind events to the elements (edit & text)
		var oEditImage	= oNewTR.select( 'td > img' ).first();
		var oNameTD		= oNewTR.select( 'td.cost-centre-name' ).first();
		var oText		= oNewTR.select( 'td > input' ).first();
		oEditImage.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, true));
		oNameTD.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, true));
		oText.observe('blur', this._checkForValueChange.bind(this, mCostCentre));
		
		// Add the new LI to the LI map (only if valid)
		if (iId != null)
		{
			this.hTRMap[iId] = oNewTR;
		} 
		else 
		{
			this.aNewTRArray.push(oNewTR);
		}	
				
		this._setCostCentreEditMode(mCostCentre, bInEditMode);
	},
	
	_checkForValueChange	: function(mCostCentre)
	{
		var oTRCostCentre = this._getCostCentreTR(mCostCentre);
		
		if (oTRCostCentre)
		{
			// Check both text and span values
			var spanValue = oTRCostCentre.select('td > span').first().innerHTML;
			var textValue = oTRCostCentre.select('td > input').first().value;
			
			// If the text is NOT different...
			if (textValue == spanValue)
			{
				// Remove if a new cost centre, set back to non-edit mode if existing
				if (isNaN(mCostCentre))
				{
					this._removeCostCentre(mCostCentre);
				}
				else
				{
					this._setCostCentreEditMode(mCostCentre, false);
				}
			}
		}
	},
	
	_setCostCentreEditMode	: function(mCostCentre, bInEditMode)
	{
		// Retrieve the LI for the given cost centre (either the id or the LI itself)
		var oTRCostCentre = this._getCostCentreTR(mCostCentre);
				
		// Hide/show the relevant elements
		if (oTRCostCentre)
		{
			var oSpan 			= oTRCostCentre.select('td > span').first();
			var oText 			= oTRCostCentre.select('td > input').first();
			var oEditImage 		= oTRCostCentre.select('td > img').first();
			
			if (bInEditMode)
			{
				if (!oText.visible())
				{
					// In edit mode, show the text box and the save & cancel buttons
					oText.value = oSpan.innerHTML;
					oText.show();
					oText.focus();
					
					oSpan.hide();
					oEditImage.hide();
				}
			}
			else 
			{
				// NOT in edit mode, show the span and the edit button
				oSpan.show();
				oEditImage.show();
				
				oText.hide();
				oText.value = '';
			}
		}
	},
	
	_saveChanges	: function()
	{
		// Get the name and id, pass to saveCostCentre
		var aChanges = [];
		var sName = null;
		var iChangeCount = 0;
		
		// Add the changes
		for (var iId in this.hTRMap)
		{
			sName = this.hTRMap[iId].select('td > input').first().value;
			
			if (sName != '')
			{
				aChanges.push({iId: iId, sName: sName});
				iChangeCount++;
			}
		}
		
		// Add the new cost centres
		for (var i = 0; i < this.aNewTRArray.length; i++)
		{
			sName = this.aNewTRArray[i].select('td > input').first().value;
			
			if (sName != '')
			{
				aChanges.push({iId: null, sName: sName});
				iChangeCount++;
			}
		}
		
		if (iChangeCount)
		{
			// Create a Popup to show 'saving...' close it when save complete
			var oPopup = new Reflex_Popup.Loading('Saving...');
			oPopup.display();
			
			// AJAX request to save changes
			this._saveCostCentres = jQuery.json.jsonFunction(this._saveComplete.bind(this,oPopup), this._saveCostCentresError.bind(this), 'Account', 'saveCostCentreChanges');
			this._saveCostCentres(this.iAccountId, aChanges);
		}
		else 
		{
			// Behave as though save is complete, there are no changes
			this._saveComplete();
		}
	},
	
	_saveComplete	: function(oPopup, oResponse)
	{
		if (oPopup)
		{
			oPopup.hide();
		}
		
		if (!oResponse || oResponse.Success)
		{
			this.hide();
		}
		else
		{
			this._saveCostCentresError(oResponse);
			
			// Show validation errors
			if (oResponse.aValidationErrors)
			{
				Popup_Cost_Centres._showValidationErrorPopup(oResponse.aValidationErrors);
			}
		}
	},
	
	_updateCostCentreAfterSave	: function(mCostCentre, oResponse)
	{
		var iId = oResponse.iId;
		var oTRCostCentre = this._getCostCentreTR(mCostCentre);
		
		if (oTRCostCentre)
		{
			// Set the span's content to new name
			oTRCostCentre.select('td > span').first().innerHTML = oResponse.sName;
			
			// Disable edit mode for the cost centre
			this._setCostCentreEditMode(oTRCostCentre, false);
		}
	},
	
	_saveCostCentresError : function(oResponse) {
		// Show a Reflex_Popup.alert explaining the error
		jQuery.json.errorPopup(oResponse);
	},
	
	_removeCostCentre		: function(mCostCentre)
	{
		var oTRCostCentre = this._getCostCentreTR(mCostCentre);
		
		// Remove the reference to the TR from the NewTRArray
		for (var i = 0; i < this.aNewTRArray.length; i++)
		{
			if (oTRCostCentre === this.aNewTRArray[i])
			{
				this.aNewTRArray.splice(i, 1);
				break;
			}
		}
		
		this.oTBody.removeChild(oTRCostCentre);
	},
	
	_getCostCentreTR		: function(mCostCentre)
	{
		if (isNaN(mCostCentre))
		{
			return mCostCentre;
		}
		else 
		{
			return this.hTRMap[mCostCentre];
		}
		
		return false;
	}
});

// Interface constants
Popup_Cost_Centres.EDIT_IMAGE_SOURCE	= '../admin/img/template/pencil.png';
Popup_Cost_Centres.ADD_IMAGE_SOURCE 	= '../admin/img/template/new.png';
Popup_Cost_Centres.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Cost_Centres.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

Popup_Cost_Centres._showValidationErrorPopup	= function(aErrors)
{
	// Build UL of error messages
	var oValidationErrors = $T.ul();
	
	for (var i = 0; i < aErrors.length; i++)
	{
		oValidationErrors.appendChild(
							$T.li(aErrors[i])
						);
	}
	
	// Show a popup containing the list
	Reflex_Popup.alert(
					$T.div({style: 'margin: 0.5em'},
						'The following errors have occured: ',
						oValidationErrors
					),
					{
						iWidth	: 30,
						sTitle	: 'Validation Errors'
					}
				);
};