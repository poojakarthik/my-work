var Popup_Cost_Centres	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(40);
		
		// This array to hold a reference to the LI in the main UL for each cost centre
		this.hLiMap = {};
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
								$T.ul(),
								$T.button(
									'Add'
								)
							);
		this.oMainUL 	= oContent.select('ul').first();
		
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
		oSaveButton.observe('click', this._saveCostCentreChanges.bind(this, mCostCentre));
		
		// Add the new LI to the LI map (only if valid)
		if (iId != null)
		{
			oCancelButton.observe('click', this._setCostCentreEditMode.bind(this, mCostCentre, false));
			this.hLiMap[iId] = oNewLi;
		} else {
			oCancelButton.observe('click', this._removeCostCentre.bind(this, mCostCentre));
		}
		
		this._setCostCentreEditMode(mCostCentre, bInEditMode);
	},
	
	_setCostCentreEditMode	: function(mCostCentre, bInEditMode)
	{
		// Retrieve the LI for the given cost centre (either the id or the LI itself)
		var oLiCostCentre = this._getCostCentreLi(mCostCentre);
				
		// Hide/show the relevant elements
		if (oLiCostCentre)
		{
			var oSpan 			= oLiCostCentre.select('ul > li > span').first();
			var oText 			= oLiCostCentre.select('ul > li > input').first();
			var oEditButton 	= oLiCostCentre.select('ul > li > button.popup-cost-centre-edit').first();
			var oSaveButton 	= oLiCostCentre.select('ul > li > button.popup-cost-centre-save').first();
			var oCancelButton 	= oLiCostCentre.select('ul > li > button.popup-cost-centre-cancel').first();
			
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
		// TODO: AJAX request to save changes
		// On completion call this._setCostCentreEditMode(id, false) giving the id of the cost centre
	},
	
	_removeCostCentre		: function(mCostCentre)
	{
		var oLiCostCentre = this._getCostCentreLi(mCostCentre);
		this.oMainUL.removeChild(oLiCostCentre);
	},
	
	_getCostCentreLi		: function(mCostCentre)
	{
		if (isNaN(mCostCentre))
		{
			return mCostCentre;
		}
		else 
		{
			return this.hLiMap[mCostCentre];
		}
		
		return false;
	}
});

