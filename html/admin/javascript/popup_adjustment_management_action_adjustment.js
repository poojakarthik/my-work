
var Popup_Adjustment_Management_Action_Adjustment = Class.create(Reflex_Popup, 
{
	initialize : function($super, aAdjustmentIds, iAction, fnOnComplete)
	{
		$super(35);
		
		this._aAdjustmentIds 	= aAdjustmentIds;
		this._iAction			= iAction;
		this._fnOnComplete		= fnOnComplete;
		
		this._oLoadingPopup = new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		
		switch (iAction)
		{
			case Page_Adjustment_Management.ACTION_APPROVE:
				this._sAction 	= 'Approve';
				this._sIcon		= '../admin/img/template/approve.png';
				break;
			
			case Page_Adjustment_Management.ACTION_REJECT:
				this._sAction 	= 'Reject';
				this._sIcon		= '../admin/img/template/decline.png';
				break;
			
			default:
				throw('Invalid adjustment action');
		}
		
		Flex.Constant.loadConstantGroup(Popup_Adjustment_Management_Action_Adjustment.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(hAdjustments)
	{
		if (!hAdjustments)
		{
			Popup_Adjustment_Management_Action_Adjustment._getAdjustments(this._aAdjustmentIds, this._buildUI.bind(this));
			return;
		}
		
		var iTotalAdjustments 	= 0;
		var aAdjustmentTypes 	= [];
		var aAccountsAffected	= [];
		var fTotalCredits		= 0;
		var fTotalDebits		= 0;
		for (var iId in hAdjustments)
		{
			var oAdjustment = hAdjustments[iId];
			
			if (aAdjustmentTypes.indexOf(oAdjustment.adjustment_type_id) == -1)
			{
				aAdjustmentTypes.push(oAdjustment.adjustment_type_id);
			}
			
			if (aAccountsAffected.indexOf(oAdjustment.account_id) == -1)
			{
				aAccountsAffected.push(oAdjustment.account_id);
			}
			
			var fAmount = parseFloat(oAdjustment.amount);
			if (oAdjustment.adjustment_type.transaction_nature == $CONSTANT.TRANSACTION_NATURE_DEBIT)
			{
				fTotalDebits += fAmount;
			}
			else
			{
				fTotalCredits += fAmount;
			}
			
			iTotalAdjustments++;
		}
		
		var oContentDiv = 	$T.div({class: 'popup-adjustment-management-action-adjustment'},
								$T.div({class: 'popup-adjustment-management-action-adjustment-paragraph'},
									'Are you sure you want to ' + this._sAction.toLowerCase() + ' these Adjustments?'
								),
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Total Adjustments'),
											$T.td(iTotalAdjustments)
										),
										$T.tr(
											$T.th('Total Adjustment Types'),
											$T.td(aAdjustmentTypes.length)
										),
										$T.tr(
											$T.th('Total Accounts Affected'),
											$T.td(aAccountsAffected.length)
										),
										$T.tr(
											$T.th('Total Credits'),
											$T.td('$' + new Number(fTotalCredits).toFixed(2))
										),
										$T.tr(
											$T.th('Total Debits'),
											$T.td('$' + new Number(fTotalDebits).toFixed(2))
										)
									)
								),
								$T.div({class: 'popup-adjustment-management-action-adjustment-reason'}
									// Placeholder
								),
								$T.div({class: 'popup-adjustment-management-action-adjustment-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Yes')
									).observe('click', this._doAction.bind(this)),
									$T.button({class: 'icon-button'},
										$T.span('No')
									).observe('click', this.hide.bind(this))
								)
							);
		
		if (this._iAction == Page_Adjustment_Management.ACTION_REJECT)
		{
			var oReasonContainer	= oContentDiv.select('.popup-adjustment-management-action-adjustment-reason').first();
			var oReasonControl		= 	Control_Field.factory(
											'select',
											{
												sLabel		: 'Rejection Reason',
												mEditable	: true,
												mMandatory	: true,
												fnPopulate	: Popup_Adjustment_Management_Action_Adjustment._getDeclineReasonOptions
											}
										);
			this._oReasonControl	= oReasonControl;
			oReasonControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oReasonContainer.appendChild(
				$T.div({class: 'popup-adjustment-management-action-adjustment-paragraph'},
					'Please choose a reason why you are rejecting these Adjustments:'
				)
			);
			oReasonContainer.appendChild(oReasonControl.getElement());
		}
		
		this._oLoadingPopup.hide();
		delete this._oLoadingPopup;
		
		this.setIcon(this._sIcon);
		this.setTitle(this._sAction + ' Adjustments');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_doAction : function()
	{
		this._action();
	},
	
	_action : function(oResponse)
	{
		if (!oResponse)
		{
			var sDesc 	= '';
			var sMethod	= '';
			var mReason	= null;
			switch (this._iAction)
			{
				case Page_Adjustment_Management.ACTION_APPROVE:
					sDesc 	= 'Approving';
					sMethod	= 'approveAdjustmentRequests';
					break;
					
				case Page_Adjustment_Management.ACTION_REJECT:
					sDesc 	= 'Rejecting';
					sMethod	= 'declineAdjustmentRequests';
					mReason	= this._oReasonControl.getElementValue();
					break;
			}
			
			this._oLoadingPopup = new Reflex_Popup.Loading(sDesc + ' Adjustments...');
			this._oLoadingPopup.display();
			
			var fnResp	= this._action.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment', sMethod);
			fnReq(this._aAdjustmentIds, mReason);
			return;
		}
		
		this._oLoadingPopup.hide();
		delete this._oLoadingPopup;
		
		if (!oResponse.bSuccess)
		{
			Page_Adjustment_Management._ajaxError(oResponse);
			return;
		}
		
		this.hide();
		
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	}
});

Object.extend(Popup_Adjustment_Management_Action_Adjustment, 
{
	REQUIRED_CONSTANT_GROUPS : ['transaction_nature',
	                            'adjustment_review_outcome_type'],
	
	_ajaxError : function(oResponse, sMessage)
	{
		// Exception
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error'}
		);
	},
	
	_getAdjustments : function(aAdjustmentIds, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Popup_Adjustment_Management_Action_Adjustment._getAdjustments.curry(aAdjustmentIds, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment', 'getForIds');
			fnReq(aAdjustmentIds);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Adjustment_Management_Action_Adjustment._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.aAdjustments);
		}
	},
	
	_getDeclineReasonOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Popup_Adjustment_Management_Action_Adjustment._getDeclineReasonOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment_Review_Outcome', 'getForAdjustmentReviewOutcomeType');
			fnReq($CONSTANT.ADJUSTMENT_REVIEW_OUTCOME_TYPE_DECLINED, true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Adjustment_Management_Action_Adjustment._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var i in oResponse.aOutcomes)
		{
			if (oResponse.aOutcomes[i].id)
			{
				aOptions.push(
					$T.option({value: i},
						oResponse.aOutcomes[i].name
					)
				);
			}
		}
		
		fnCallback(aOptions);
	}
});