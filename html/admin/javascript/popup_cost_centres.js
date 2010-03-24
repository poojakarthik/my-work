var Popup_Cost_Centres	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(40);
		
		// This array to hold a reference to the LI in the main UL for each cost centre
		this.aLiMap = []; 
		
		// Make AJAX request to get cost centres
		// TODO
		
		var aDummy = [];
		aDummy.push({id: 1, name: 'Cost centre 1'});
		aDummy.push({id: 2, name: 'Cost centre 2'});
		aDummy.push({id: 3, name: 'Cost centre 3'});
		aDummy.push({id: 4, name: 'Cost centre 4'});
		aDummy.push({id: 5, name: 'Cost centre 5'});
		
		this._buildUI(aDummy);
	},

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// Make AJAX Request
			// TODO
			return;
		}
		else
		{
			// DEBUG
			oResponse = {};
		}
		
		// Build UI
		var oContent 	=	$T.div(
								$T.ul(),
								$T.button(
									'Add'
								)
							);
		this.oMainUL 	= oContent.select('ul').first();
		
		// Add all cost centres from response
		var aCostCentres = oResponse;
		
		for (var i = 0; i < aCostCentres.length; i++)
		{
			this._addCostCentre(aCostCentres[i].id, aCostCentres[i].name);
		}
		
		// Set the add buttons event handler
		var oAddButton	= oContent.select( 'button' ).first();
		oAddButton.observe('click', this._addCostCentre.bind(this, null, '', true));
		
		this.setTitle('Manage Cost Centres');
		//this.setIcon()
		this.addCloseButton();
		this.setContent(oContent);
		this.display();
	},
	
	_addCostCentre	: function(iId, sName, bInEditMode)
	{
		var sLiStyle = 'display:inline; list-style-type:none;';
		
		// Attach the new LI to the main UL
		var oNewLi = $T.li(
						{style: sLiStyle},
						$T.ul(
								$T.li(
									{style: sLiStyle},
									$T.span(
										sName
									),
									$T.input(
											{type: 'text', style: 'display: none', value: sName}
									)
								),
								$T.li(
									{style: sLiStyle},
									$T.button(
										{class: 'popup-cost-centre-edit'},
										'Edit'
									),
									$T.button(
										{style: 'display: none', class: 'popup-cost-centre-save'},
										'Save'
									),
									$T.button(
										{style: 'display: none', class: 'popup-cost-centre-cancel'},
										'Cancel'
									)
								)
							)
					 	);
		this.oMainUL.appendChild(oNewLi);
		
		var mCostCentre = (iId != null ? iId : oNewLi);
		
		// Bind events to the buttons (edit, save & cancel)
		var oEditButton		= oNewLi.select( 'button.popup-cost-centre-edit' ).first();
		var oCancelButton 	= oNewLi.select( 'button.popup-cost-centre-cancel' ).first();
		var oSaveButton 	= oNewLi.select( 'button.popup-cost-centre-save' ).first();
		oEditButton.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, true));
		oCancelButton.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, false));
		oSaveButton.observe('click', this._saveCostCentreChanges.bind(this, mCostCentre));
		
		// Add the new LI to the LI map (only if valid)
		if (!isNaN(mCostCentre))
		{
			this.aLiMap[iId] = oNewLi;
		}
		
		this._setCostCentreEditMode(mCostCentre, bInEditMode);
	},
	
	_setCostCentreEditMode	: function(mCostCentre, bInEditMode)
	{
		// Retrieve the LI for the given cost centre (either the id or the LI itself)
		var oLiCostCentre = null;
		
		if (isNaN(mCostCentre))
		{
			oLiCostCentre = mCostCentre;
		}
		else 
		{
			oLiCostCentre = this.aLiMap[mCostCentre];
		}
		
		// Hide/show the relevant elements
		if (oLiCostCentre)
		{
			var oSpan 			= oLiCostCentre.select('ul > li > span').first();
			var oText 			= oLiCostCentre.select('ul > li > input').first();
			var oEditButton 	= oLiCostCentre.select('ul > li > button.popup-cost-centre-edit').first();
			var oSaveButton 	= oLiCostCentre.select('ul > li > button.popup-cost-centre-save').first();
			var oCancelButton 	= oLiCostCentre.select('ul > li > button.popup-cost-centre-cancel').first();
			debugger;
			if (bInEditMode)
			{
				// In edit mode, show the text box and the save & cancel buttons
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
		// TODO: AJAX request to save changes, on completion call 
		// this._setCostCentreEditMode(id, false) giving the id of the cost centre
	}
});

