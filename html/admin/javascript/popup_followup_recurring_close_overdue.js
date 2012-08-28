
var Popup_FollowUp_Recurring_Close_Overdue	= Class.create( /* extends */ Reflex_Popup,
{
	initialize	: function($super, iId, mOccurrences, fnCompletionCallback, bCompleteOnEmpty)
	{
		$super(35);
		
		this._iId					= iId;
		this._fnCompletionCallback	= fnCompletionCallback;
		
		if (mOccurrences)
		{
			this._bCompleteOnEmpty	= false;
			this._hOccurrences		= mOccurrences;
			this._buildUI();
		}
		else
		{
			this._bCompleteOnEmpty	= bCompleteOnEmpty;
			this._getOverdueOccurrences(this._buildUI.bind(this));
		}
	},
	
	_buildUI	: function()
	{
		// Create the section (for the list)
		var oSection	= new Section(false, 'followup-recurring-close-all-overdue');
		oSection.setTitleText('Follow-Ups');
		this._oOccurrenceSection	= oSection;
		
		this._oContent	= 	$T.div({class: 'popup-followup-close'},
								$T.div({class: 'followup-recurring-close-all-overdue-info'},
									$T.p('There are Overdue Follow-Ups for this Recurring Follow-Up.'),
									$T.p('If you still wish to end the Recurring Follow-Up please close all of the Overdue Follow-Ups:')
								),
								oSection.getElement(),
								$T.div({class: 'popup-followup-close-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('OK')
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Button events
		var oOKButton	= this._oContent.select('div.popup-followup-close-buttons > button.icon-button').first();
		oOKButton.observe('click', this._checkCompleteAndClose.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-close-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
		
		this._refreshContent();
		
		// Popup setup
		this.setTitle('Close Overdue Follow-Ups');
		this.setContent(this._oContent);
		this.display();
	},
	
	_refreshContent	: function()
	{
		// Clear previous content
		this._oOccurrenceSection.setContent('');
		
		// Populate the content
		var bEmpty	= true;
		for (var i in this._hOccurrences)
		{
			this._oOccurrenceSection.addToContent(this._buildListItem(i, this._hOccurrences[i]));
			bEmpty	= false;
		}
		
		if (bEmpty)
		{
			this._oOccurrenceSection.setContent(
				$T.div({class: 'no-data'},
					'There are no more Overdue Follow-Ups'
				)
			);
		}
		
		this._bEmpty	= bEmpty;
	},
	
	_buildListItem	: function(iIteration, oOccurrence)
	{
		var oCompleteImage	= $T.img({src: Popup_FollowUp_View.COMPLETE_IMAGE_SOURCE, alt: 'Complete', title: 'Complete'});
		var oDismissImage	= $T.img({src: Popup_FollowUp_View.DISMISS_IMAGE_SOURCE, alt: 'Dismiss', title: 'Dismiss'});
		oCompleteImage.observe('click', this._closeFollowUp.bind(this, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED, iIteration));
		oDismissImage.observe('click', this._closeFollowUp.bind(this, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED, iIteration));

		var oDueDate		= 	Date.$parseDate(
									oOccurrence.sDueDatetime, 
									Popup_FollowUp_Recurring_Close_Overdue.DATE_FORMAT_DATA
								);

		var oItemDiv	=	$T.div({class: 'followup-recurring-close-all-overdue-item'},
								oDueDate.$format(Popup_FollowUp_Recurring_Close_Overdue.DATE_FORMAT_OUTPUT),
								$T.div({class: 'close-buttons'},
									oCompleteImage,
									oDismissImage
								)
							);
		
		return oItemDiv;
	},
	
	_getOverdueOccurrences	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get the array of occurrences
			var fnGetOccurrences	=	jQuery.json.jsonFunction(
											this._getOverdueOccurrences.bind(this, fnCallback),
											this._ajaxError.bind(this),
											'FollowUp_Recurring',
											'getOverdueOccurrences'
										);
			fnGetOccurrences(this._iId, (new Date().getTime()) / 1000);
		}
		else if (oResponse.Success)
		{
			// Got it, cache array
			this._hOccurrences	= jQuery.json.arrayAsObject(oResponse.aOccurrences);
			
			if ((oResponse.iCount == 0) && this._bCompleteOnEmpty && this._fnCompletionCallback)
			{
				this._fnCompletionCallback();
			}
			else if (fnCallback)
			{
				this._bCompleteOnEmpty	= false;
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_checkCompleteAndClose	: function()
	{
		if (this._bEmpty)
		{
			// None left, callback & hide
			if (this._fnCompletionCallback)
			{
				this._fnCompletionCallback();
			}
			
			this.hide();
		}
		else
		{
			// Show alert to remind
			var oError	= Reflex_Popup.alert('You must close all of the Overdue Follow-Ups first.');
		}
	},
	
	_closeFollowUp	: function(iFollowUpClosureTypeId, iIteration)
	{
		var fnOnScriptLoad	= function(iFollowUpClosureTypeId)
		{
			var oPopup	= 	new Popup_FollowUp_Close(
								iFollowUpClosureTypeId,
								null, 
								this._iId,
								iIteration,
								this._refreshAfterClosure.bind(this)
							);
		};
		
		JsAutoLoader.loadScript(
			'javascript/popup_followup_close.js', fnOnScriptLoad.bind(this, iFollowUpClosureTypeId)
		);
	},
	
	_refreshAfterClosure	: function()
	{
		this._getOverdueOccurrences(this._refreshContent.bind(this));
	},
	
	_ajaxError : function(oResponse) {
		if (this.oLoading) {
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		jQuery.json.errorPopup(oResponse);
	}
});

Popup_FollowUp_Recurring_Close_Overdue.DATE_FORMAT_DATA			= 'Y-m-d H:i:s';
Popup_FollowUp_Recurring_Close_Overdue.DATE_FORMAT_OUTPUT		= 'l jS M Y g:i A';

Popup_FollowUp_Recurring_Close_Overdue.COMPLETE_IMAGE_SOURCE	= '../admin/img/template/approve.png';
Popup_FollowUp_Recurring_Close_Overdue.DISMISS_IMAGE_SOURCE		= '../admin/img/template/decline.png';


