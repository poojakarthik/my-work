
var Component_FollowUp_Context_List	= Class.create
({
	initialize	: function(oContainerElement, iFollowUpType, iTypeDetail)
	{
		this._oContainerElement	= oContainerElement;
		this._iFollowUpType		= iFollowUpType;
		this._iTypeDetail		= iTypeDetail;
		this._buildUI();
	},
	
	//---------------//
	// Public methods
	//---------------//
	
	refresh	: function()
	{
		//alert('refreshing');
	},
	
	//-----------------//
	// Private methods
	//-----------------//
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			var fnGetFollowUps	=	jQuery.json.jsonFunction(
										this._buildUI.bind(this), 
										this._ajaxError.bind(this), 
										'FollowUp', 
										'getFollowUpsFromContext'
									);
			fnGetFollowUps(this._iFollowUpType, this._iTypeDetail);
		}
		else if (oResponse.Success)
		{
			// Create UI
			var sAddAlt		= 'Create a new Follow-Up';
			var oAddLink	= 	$T.div({class: 'followup-context-list-add'},
									$T.img({src: Component_FollowUp_Context_List.ADD_ICON_IMAGE_SOURCE + '_off.png', alt: sAddAlt, title: sAddAlt})
								);
			oAddLink.observe('click', this._showAddPopup.bind(this));
			oAddLink.observe('mouseover', this._updateAddLinkIcon.bind(this, oAddLink, '_hover.png'));
			oAddLink.observe('mouseout', this._updateAddLinkIcon.bind(this, oAddLink, '_off.png'));
			
			var aChildren	= [{class: 'followup-context-list'}, oAddLink];
			var aAll		= oResponse.aFollowUps.concat(oResponse.aFollowUpRecurrings);
			if (aAll.length)
			{
				// Sort the array, by first name ascending then last name ascending
				var oSorter	= 	new Reflex_Sorter(
									[
									 	{
											sField		: 'created_datetime', 
											bReverse	: true, 
											fnCompare	: Reflex_Sorter.stringGreaterThan
										}
									]
								);
				oSorter.sort(aAll);
				
				for (var i = 0; i < aAll.length; i++)
				{
					aChildren.push(this._createIcon(aAll[i]));
				}
			}
			
			this._oContainerElement.appendChild(
				$T.div.apply($T, aChildren)
			);
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_showAddPopup	: function()
	{
		FollowUpLink.showAddFollowUpPopup(this._iFollowUpType, this._iTypeDetail);
	},
	
	_ajaxError	: function(oResponse)
	{
		var oConfig	= {sTitle: 'FollowUp Context List Error'};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	},
	
	_createIcon	: function(oFollowUp)
	{
		var oImg	= null;
		if (oFollowUp.due_datetime)
		{
			// Once off
			var sAlt	= 'Follow-Up: Due on ' + Date.$format('l jS M Y g:i A', new Date(Date.parse(oFollowUp.due_datetime.replace(/-/g, '/'))));
			oImg		= $T.img({src: Component_FollowUp_Context_List.ONCE_OFF_ICON_IMAGE_SOURCE, alt: sAlt, title: sAlt});
			
		}
		else
		{
			// Recurring
			var iMulitplier			= oFollowUp.recurrence_multiplier;
			var sRecurrencePeriod	= Flex.Constant.arrConstantGroups.followup_recurrence_period[oFollowUp.followup_recurrence_period_id].Name;
			var sAlt				= 'Recurring Follow-Up: Due Every ' + iMulitplier + ' ' + sRecurrencePeriod + (iMulitplier == 1 ? '' : 's' );
			oImg					= $T.img({src: Component_FollowUp_Context_List.RECURRING_ICON_IMAGE_SOURCE, alt: sAlt, title: sAlt});
		}
		
		oImg.observe('click', this._iconClick.bind(this, oFollowUp));
		return 	$T.div({class: 'followup-context-list-followup'},
					oImg
				);
	},
	
	_updateAddLinkIcon	: function(oAddLinkDiv, sIconSuffix)
	{
		oAddLinkDiv.select('img').first().src	= Component_FollowUp_Context_List.ADD_ICON_IMAGE_SOURCE + sIconSuffix;
	},
	
	_iconClick	: function(oFollowUp)
	{
		var oPopup	= new Popup_FollowUp_View(this._iFollowUpType, this._iTypeDetail, oFollowUp);
	}
});

Component_FollowUp_Context_List.ADD_ICON_IMAGE_SOURCE		= '../admin/img/template/followup_add';
Component_FollowUp_Context_List.ONCE_OFF_ICON_IMAGE_SOURCE	= '../admin/img/template/followup.png';
Component_FollowUp_Context_List.RECURRING_ICON_IMAGE_SOURCE	= '../admin/img/template/followup_recurring.png';
