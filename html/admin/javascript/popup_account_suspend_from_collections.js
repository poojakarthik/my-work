
var Popup_Account_Suspend_From_Collections = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAccountId, fnOnComplete, oTIOComplaintDetails)
	{
		$super(40);
		
		this._iAccountId			= iAccountId;
		this._fnOnComplete			= fnOnComplete;
		this._oTIOComplaintDetails	= (oTIOComplaintDetails ? oTIOComplaintDetails : null);
		
		this._getSuspensionAvailabilityInfo(iAccountId);
	},
	
	_buildUI : function()
	{
		var oContentDiv = $T.div();
		this.setTitle('Suspend Account ' + this._iAccountId + ' from Collections');
		this.setContent(oContentDiv);
		this.addCloseButton();
		new Component_Collections_Suspension(
			this._iAccountId, 
			oContentDiv, 
			this._createComplete.bind(this), 
			this._createCancelled.bind(this),
			null, 
			this._oTIOComplaintDetails,
			this.display.bind(this)
		);
	},
	
	_createComplete : function(iId)
	{
		if (this._fnOnComplete)
		{
			this._fnOnComplete(iId);
		}
		this.hide();
	},
	
	_createCancelled : function()
	{
		this.hide();
	},
	
	_getSuspensionAvailabilityInfo : function(iAccountId, oResponse)
	{
		if (!oResponse)
		{
			// Request
			this._oLoading = new Reflex_Popup.Loading('');
			this._oLoading.display();
			
			var fnResp	= this._getSuspensionAvailabilityInfo.bind(this, iAccountId);
   			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'getSuspensionAvailabilityInfo');
   			fnReq(iAccountId);
   			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		if (oResponse.oSuspension && !this._oTIOComplaintDetails)
		{
			// Account is already suspended from collections
			var sEndDate = Date.$parseDate(oResponse.oSuspension.proposed_end_datetime, 'Y-m-d H:i:s').$format('l jS M Y, g:i A');
			Reflex_Popup.alert(
				$T.div({class: 'alert-content'},
					$T.div('This Account is currently suspended from collections until:'),
					$T.div({class: 'component-collections-suspension-has-suspension-end-date'},
						sEndDate
					)
				),
				{
					iWidth	: 30,
					sTitle	: 'Cannot Suspend Account'
				}
			);
			return;
		}
		else if (oResponse.oPromise)
		{
			// Account has a promise
			Reflex_Popup.alert('This Account has a current Promise to Pay and as such cannot be suspended from Collections.', {iWidth: 45, sTitle: 'Cannot Suspend Account'});
			return;
		}
		else if (oResponse.bSuspensionLimitExceeded)
		{
			// Number of suspensions this collections period has reached the limit for this employee
			Reflex_Popup.alert("You are not permitted to suspend this Account. It has reached it's suspension limit since entering Collections.", {iWidth: 45, sTitle: 'Cannot Suspend Account'});
			return;
		}
		
		this._buildUI();
	}
});
