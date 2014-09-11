
var Popup_Collection_Event_Action = Class.create(Reflex_Popup,
{
	initialize : function($super, aEventInstances, hActionTypes, fnOnComplete, fnOnCancel)
	{
		$super(40);
		
		this._aEventInstances 		= aEventInstances;
		this._hActionTypes			= hActionTypes;
		this._fnOnComplete 			= fnOnComplete;
		this._fnOnCancel 			= (fnOnCancel ? fnOnCancel : fnOnComplete);
		this._iCurrentInstanceIndex	= 0;
		this._aForms				= [];
		
		this._buildUI();
		this._showNextActionInterface();
	},
	
	_buildUI : function()
	{
		// Create container
		var oContent =	$T.div({class: 'popup-collection-event-action'},
							$T.div({class: 'popup-collection-event-action-container'},
								$T.div({class: 'popup-collection-event-action-container-complete'},
									'There are no more actions to create.'	
								)
							),
							$T.div({class: 'popup-collection-event-action-buttons'},
								$T.span({class: 'popup-collection-event-action-counter'}),
								$T.button('Next').observe('click', this._nextAction.bind(this, false)),
								$T.button('Close').observe('click', this._complete.bind(this)),
								$T.button('Cancel').observe('click', this._cancel.bind(this))
							)
						);
		
		this._oFormContainer 	= oContent.select('.popup-collection-event-action-container').first();
		this._oCompleteElement	= oContent.select('.popup-collection-event-action-container-complete').first();
		this._oCounterElement	= oContent.select('.popup-collection-event-action-counter').first();
		this._oNextButton		= oContent.select('.popup-collection-event-action-buttons > button').first();
		this._oCloseButton		= oContent.select('.popup-collection-event-action-buttons > button')[1];
		this._oCancelButton		= oContent.select('.popup-collection-event-action-buttons > button')[2];
		
		this._oCompleteElement.hide();
		this._oCloseButton.hide();
		
		// Create individual forms
		for (var i = 0; i < this._aEventInstances.length; i++)
		{
			var oInstance	= 	this._aEventInstances[i];
			var oActionType = 	this._hActionTypes[oInstance.collection_event.detail.action_type_id];
			var oForm 		=	$T.div(
									$T.div(
										$T.span('New Action ('),
										$T.span(oActionType.name),
										$T.span(')')
									),
									$T.div({class: 'popup-collection-event-action-eventsummary'},
										$T.div(
											$T.span('Account: '),
											$T.span(
												$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oInstance.account.Id},
														oInstance.account.Id + ': ' + oInstance.account.BusinessName
												)
											)
										),
										$T.div(
											$T.span('Event: '),
											$T.span(oInstance.collection_event.name)
										)
									),
									$T.div({class: 'popup-collection-event-action-textarea-container'},
										$T.textarea()
									)
								);
			oForm.hide();
			this._aForms.push(oForm);
			this._oFormContainer.appendChild(oForm);
		}
		
		this.setTitle('Complete Collections Event: Create Action(s)');
		this.setContent(oContent);
		this.display();
	},
	
	_showNextActionInterface : function()
	{
		var oForm = this._aForms[this._iCurrentInstanceIndex];
		if (oForm)
		{
			oForm.show();
			this._oCounterElement.innerHTML = (this._iCurrentInstanceIndex + 1) + ' of ' + this._aForms.length;
		}
	},
	
	_nextAction : function(bSaved, oEvent)
	{
		if (!bSaved)
		{
			// Save the current action first
			var oInstance	= this._aEventInstances[this._iCurrentInstanceIndex];
			var sDetail		= this._aForms[this._iCurrentInstanceIndex].select('textarea').first().value;
			var oActionType = this._hActionTypes[oInstance.collection_event.detail.action_type_id];
			if ((sDetail == '') && (oActionType.action_type_detail_requirement_id == $CONSTANT.ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED))
			{
				// The details are empty, and are required
				Reflex_Popup.alert('Please supply details for the Action.');
				return;
			}
			
			// Invoke the event
			var hEventInstanceDetails 			= {};
			hEventInstanceDetails[oInstance.id] = sDetail;
			Collection_Event_Action.invokeEventInstances(hEventInstanceDetails, this._nextAction.bind(this, true));
			return;
		}
		
		// Action saved, move to then next one
		this._aForms[this._iCurrentInstanceIndex].hide();
		this._iCurrentInstanceIndex++;
		if (this._aForms[this._iCurrentInstanceIndex])
		{
			// Show the next interface
			this._showNextActionInterface();
		}
		else
		{
			// No more to create, show the close button
			this._oCompleteElement.show();
			this._oNextButton.hide();
			this._oCancelButton.hide();
			this._oCloseButton.show();
			this._oCounterElement.innerHTML = '';
		}
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
		this.hide();
	},
	
	_complete : function()
	{
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
		this.hide();
	}
});
