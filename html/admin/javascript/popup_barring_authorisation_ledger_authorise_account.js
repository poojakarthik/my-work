
var Popup_Barring_Authorisation_Ledger_Authorise_Account = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAccountId, iAccountBarringLevelId, aServiceBarringLevelIds, iBarringLevelId, fnOnComplete)
	{
		$super(40);
		
		this._iAccountId 				= iAccountId;
		this._iAccountBarringLevelId	= iAccountBarringLevelId;
		this._aServiceBarringLevelIds	= (aServiceBarringLevelIds ? aServiceBarringLevelIds : null);
		this._iBarringLevelId			= iBarringLevelId;
		this._fnOnComplete				= fnOnComplete;
		
		this._buildUI();
		this._getServices();
	},
	
	_buildUI : function(oResponse)
	{
		// Success, build list of services
		var oContentDiv =	$T.div({class: 'popup-barring-authorisation-ledger-authorise-account'},
								$T.div('Please review the list of Services that will be affected.'),
								$T.div('Check the box next to any Services that you want to complete the barring level change for immediately.'),
								$T.div('NOTE: Services that can be automatically provisioned for the barring level change will not have checkboxes next to them, they will be completed automatically.'),
								$T.div({class: 'popup-barring-authorisation-ledger-authorise-account-list'}),
								$T.div({class: 'popup-barring-authorisation-ledger-authorise-account-buttons'},
									$T.button('Authorise Account').observe('click', this._authorise.bind(this, null)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		
		this._oListDiv = oContentDiv.select('.popup-barring-authorisation-ledger-authorise-account-list').first();
		
		this.setTitle('Authorise Barring Level Change');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_getServices : function(oResponse)
	{
		if (!oResponse)
		{
			// Show loading
			this._oLoading = new Reflex_Popup.Loading('Getting List of Services...');
			this._oLoading.display();
			
			// Request to get the services that are to be authorised
			var fnResp	= this._getServices.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Barring', 'getServicesForAccountAuthorisation');
			fnReq(this._iAccountId, this._iBarringLevelId, this._aServiceBarringLevelIds)
			return;
		}
		
		// Hide loading
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		// Check for no services
		if (Object.isArray(oResponse.aServices) && (oResponse.aServices.length == 0))
		{
			Reflex_Popup.alert('There are no services to bar.');
			return;
		}
		
		this._hServices = oResponse.aServices;
		
		var oService = null;
		for (var iServiceId in oResponse.aServices)
		{
			oService = oResponse.aServices[iServiceId];
			this._oListDiv.appendChild(
				$T.div(
					$T.ul({class: 'reset horizontal'},
						$T.li(
							oService.auto_barrable ? null : $T.input({type: 'checkbox', value: iServiceId})
						),
						$T.li(oService.fnn).observe('click', this._checkBox.bind(this))
					)
				)
			);
		}
	},
	
	_authorise : function(oResponse, oEvent)
	{
		if (!oResponse)
		{
			// Request to get the services that are to be authorised
			var fnResp	= this._authorise.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Barring', 'authoriseAccount');
			
			// Build hash of service_id to service_barring_level_id
			var hServiceBarringLevelIdsByServiceId = {};
			for (var iServiceId in this._hServices)
			{
				hServiceBarringLevelIdsByServiceId[iServiceId] = this._hServices[iServiceId].service_barring_level_id;
			}
			
			// Build list of services to action immediately
			var aCheckboxes 		= this._oListDiv.select('input[type="checkbox"]');
			var aServiceIdsToAction	= [];
			for (var i = 0; i < aCheckboxes.length; i++)
			{
				if (aCheckboxes[i].checked)
				{
					aServiceIdsToAction.push(aCheckboxes[i].value);
				}
			}
			
			// Call json function
			fnReq(
				this._iAccountId, 
				this._iAccountBarringLevelId, 
				this._iBarringLevelId,
				hServiceBarringLevelIdsByServiceId, 
				aServiceIdsToAction
			)
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		this.hide();
		
		// Success
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	},
	
	_checkBox : function(oEvent)
	{
		var oCheckbox = oEvent.target.previousSibling.firstChild;
		if (!oCheckbox)
		{
			return;
		}
		if (oCheckbox.checked)
		{
			oCheckbox.checked = null;
		}
		else
		{
			oCheckbox.checked = true;
		}
	}
});
