var Popup_Cost_Centres	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(40);
		
		// This array to hold a reference to the LI in the main UL for each cost centre
		this.hTRMap = {};
		this.iCostCentreCount = 0;
		this.iAccountId = iAccountId;
		this.oTBody = null;
		this.sCurrentEditedInputValue = null;
		
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
			Reflex_Popup.alert('There was an error accessing the database' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Database Error', fnOnClose: this.hide.bind(this)});
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
									),
									$T.tfoot(
										$T.tr(
											$T.th(
												$T.button({class: 'cost-centre-add'},
													'Add'
												)
											),
											$T.th()
										)
									),
									$T.colgroup(
										$T.col({class: 'cost-centre-name-col'}),
										$T.col({class: 'cost-centre-buttons-col'})
									),
									$T.tbody()
								),
								$T.button(
									'Save'
								)
							);
		this.oTBody 	= oContent.select('tbody').first();
		
		// Set the add buttons event handler
		var oAddButton	= oContent.select( 'button' ).first();
		oAddButton.observe('click', this._addCostCentre.bind(this, null, '', true));
		
		// Set the save buttons event handler
		var oSaveButton	= oContent.select( 'button' ).last();
		oSaveButton.observe('click', this._saveChanges.bind(this));
		
		// Add all cost centres from response
		var aCostCentres = jQuery.json.arrayAsObject(oResponse.aCostCentres);
		
		for (var i in aCostCentres)
		{
			this._addCostCentre(aCostCentres[i].Id, aCostCentres[i].Name);
		}
		
		this.setTitle('Manage Cost Centres');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();
	},
	
	_addCostCentre	: function(iId, sName, bInEditMode)
	{
		this.iCostCentreCount++;
		
		var sAltClass = (this.iCostCentreCount % 2) ? 'alt' : '';
		
		// Attach a new TR to the main TBODY
		var oNewTR 		=	$T.tr({class: sAltClass},
								$T.td({class: 'cost-centre-name'},
									$T.span(
										sName
									),
									$T.input({type: 'text', style: 'display: none', value: sName})
								),
								$T.td({class: 'cost-centre-buttons'},
									$T.img({src: '../admin/img/template/user_edit.png'})
								)
							);
		var mCostCentre	= (iId != null ? iId : oNewTR);
		this.oTBody.appendChild(oNewTR);
		
		// Bind events to the elements (edit & text)
		var oEditImage	= oNewTR.select( 'td > img' ).first();
		var oText		= oNewTR.select( 'td > input' ).first();
		oEditImage.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, true));
		oText.observe('blur', this._checkForValueChange.bind(this, mCostCentre));
		
		// Add the new LI to the LI map (only if valid)
		if (iId != null)
		{
			this.hTRMap[iId] = oNewTR;
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
				// In edit mode, show the text box and the save & cancel buttons
				oText.value = oSpan.innerHTML;
				oText.show();
				oText.focus();
				
				oSpan.hide();
				oEditImage.hide();
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
		var aChanges = {};
		var sName = null;
		var iChangeCount = 0;
		
		for (var iId in this.hTRMap)
		{
			sName = this.hTRMap[iId].select('td > input').first().value;
			
			if (sName != '')
			{
				aChanges[iId] = sName;
				iChangeCount++;
			}
		}
		
		if (iChangeCount)
		{
			// AJAX request to save changes
			this._saveCostCentres = jQuery.json.jsonFunction(this.hide.bind(this), this._saveCostCentresError.bind(this), 'Account', 'saveCostCentreChanges');
			this._saveCostCentres(this.iAccountId, aChanges);
		}
		else 
		{
			Reflex_Popup.alert('There are no changes to save');
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
	
	_saveCostCentresError	: function()
	{
		// Show a Reflex_Popup.alert explaining the error
		Reflex_Popup.alert('There was an error saving the cost centre changes' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Save Error'});
	},
	
	_removeCostCentre		: function(mCostCentre)
	{
		var oLiCostCentre = this._getCostCentreTR(mCostCentre);
		this.oTBody.removeChild(oLiCostCentre);
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

