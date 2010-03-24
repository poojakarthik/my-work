var Popup_Cost_Centres	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(40);
		
		// This array to hold a reference to the LI in the main UL for each cost centre
		this.hTRMap = {};
		this.iCostCentreCount = 0;
		this.iAccountId = iAccountId;
		
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
		var oContent 	=	$T.div(
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
											$T.th(
												$T.div()
											)
										)
									),
									$T.tfoot(
										$T.tr(
											$T.th(
												$T.button(
													'Add'
												)
											)
										)
									),
									$T.tbody(
										/*$T.tr(
											$T.td(
												$T.ul({class: 'reset cost-centre-list'})
											)
										)*/
									)
								)
							);
		//this.oMainUL 	= oContent.select('ul').first();
		this.oTBody 	= oContent.select('tbody').first();
		
		// Set the add buttons event handler
		var oAddButton	= oContent.select( 'button' ).first();
		oAddButton.observe('click', this._addCostCentre.bind(this, null, '', true));
		
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
		
		// Attach the new LI to the main UL
		/*var oNewLi = $T.li({class: sAltClass},
						$T.ul({class: 'reset horizontal'},
							$T.li({class: 'cost-centre-name'},
								$T.span(
									sName
								),
								$T.input({type: 'text', style: 'display: none', value: sName})
							),
							$T.li({class: 'cost-centre-buttons'},
								$T.button({class: 'popup-cost-centre-edit'},
									'Edit'
								),
								$T.button({style: 'display: none', class: 'popup-cost-centre-save'},
									'Save'
								),
								$T.button({style: 'display: none', class: 'popup-cost-centre-cancel'},
									'Cancel'
								)
							)
						)
					);*/
		var oNewTR 		=	$T.tr({class: sAltClass},
								$T.td({class: 'cost-centre-name'},
									$T.span(
										sName
									),
									$T.input({type: 'text', style: 'display: none', value: sName})
								),
								$T.td({class: 'cost-centre-buttons'},
									$T.button({class: 'popup-cost-centre-edit'},
										'Edit'
									),
									$T.button({style: 'display: none', class: 'popup-cost-centre-save'},
										'Save'
									),
									$T.button({style: 'display: none', class: 'popup-cost-centre-cancel'},
										'Cancel'
									)
								),
							);
		var mCostCentre	= (iId != null ? iId : oNewTR);
		this.oTBody.appendChild(oNewTR);
		
		// Bind events to the buttons (edit, save & cancel)
		var oEditButton		= oNewTR.select( 'button.popup-cost-centre-edit' ).first();
		var oCancelButton 	= oNewTR.select( 'button.popup-cost-centre-cancel' ).first();
		var oSaveButton 	= oNewTR.select( 'button.popup-cost-centre-save' ).first();
		oEditButton.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, true));
		oSaveButton.observe('click', this._saveCostCentreChanges.bind(this, mCostCentre));
		
		// Add the new LI to the LI map (only if valid)
		if (iId != null)
		{
			oCancelButton.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, false));
			this.hTRMap[iId] = oNewTR;
		} else {
			oCancelButton.observe('click', this._removeCostCentre.bind(this, mCostCentre));
		}
		
		this._setCostCentreEditMode(mCostCentre, bInEditMode);
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
			var oEditButton 	= oTRCostCentre.select('td > button.popup-cost-centre-edit').first();
			var oSaveButton 	= oTRCostCentre.select('td > button.popup-cost-centre-save').first();
			var oCancelButton 	= oTRCostCentre.select('td > button.popup-cost-centre-cancel').first();
			
			if (bInEditMode)
			{
				// In edit mode, show the text box and the save & cancel buttons
				oText.value = oSpan.innerHTML;
				oText.show();
				oSaveButton.show();
				oCancelButton.show();
				
				oSpan.hide();
				oEditButton.hide();
			}
			else 
			{
				// NOT in edit mode, show the span and the edit button
				oSpan.show();
				oEditButton.show();
				
				oText.hide();
				oSaveButton.hide();
				oCancelButton.hide();
			}
		}
	},
	
	_saveCostCentreChanges	: function(mCostCentre)
	{
		// Get the name and id, pass to saveCostCentre
		var iId				= isNaN(mCostCentre) ? null : mCostCentre;
		var oTRCostCentre 	= this._getCostCentreTR(mCostCentre);
		var sNewName		= oTRCostCentre.select('td > input').first().value;
		
		// AJAX request to save changes
		this._saveCostCentre = jQuery.json.jsonFunction(this._updateCostCentreAfterSave.bind(this, mCostCentre), this._saveCostCentreError.bind(this), 'Account', 'saveCostCentre');
		this._saveCostCentre(this.iAccountId, iId, sNewName);
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
	
	_saveCostCentreError	: function()
	{
		// Show a Reflex_Popup.alert explaining the error
		Reflex_Popup.alert('There was an error saving the cost centre' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Save Error'});
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

