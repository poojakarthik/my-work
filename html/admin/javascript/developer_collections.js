
var Developer_Collections	= Class.create(Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(70);
		
		this.aScenarios 			= [];
		this.oSpecialScenarios		= {promise_unmet: {oScenario: null, sName: 'Unmet Promise'}, standard: {oScenario: null, sName: 'Standard'}};
		this.aEventTypes			= [];
		this._aInvoices				= [];
		this._aCollectables			= [];
		this._aPayments 			= [];
		this._aPromises				= [];
		this._aTransfers			= [];
		this._aEventInstances		= [];
		this._aEventPredictions		= [];
		this._aSuspensions			= [];
		this._hIds					= {};
		this._oCurrentEventInstance	= null;
		this._oScenario				= null;
		this._oSuspension			= null;
		
		this._oTimelinePopup	= $T.div({class: 'developer-collections-timeline-data'});
		this._oTimelinePopup.hide();
		document.body.appendChild(this._oTimelinePopup);
		
		this._oDate	= new Date();
		this._oDate.setHours(0);
		this._oDate.setMinutes(0);
		this._oDate.setSeconds(0);
		this._oDate.setMilliseconds(0);
		this._oLatestDate = new Date(this._oDate.getTime());
		
		this._buildUI();
		this._createScenarioEventTypes();
		
		// Get scenarios from the cookie
		var aScenarios	= this._getScenarioConfig();
		if (aScenarios)
		{
			// Load from config
			this.aScenarios = aScenarios;
			for (var i = 0; i < this.aScenarios.length; i++)
			{
				var oScenario = this.aScenarios[i];
				if (oScenario.extra)
				{
					this.oSpecialScenarios[oScenario.extra].oScenario = oScenario;
				}
			}
		}
		else
		{
			// No config, initialise & save to cookie
			this._createScenarios();
			this._setScenarioConfig();
		}
		
		this._refreshScenarioList();
		
		this._oScenario	= this.oSpecialScenarios.standard.oScenario;
		this._updateScenarioSelect();
		
		this._refresh();
		this.display();
	},
	
	// Public
	
	storeScenarios	: function()
	{
		this._setScenarioConfig();
	},
	
	isCurrentScenario	: function(oScenario)
	{
		return (this._oScenario == oScenario);
	},
	
	deleteScenario	: function(oScenario)
	{
		for (var i = 0; i < this.aScenarios.length; i++)
		{
			if (this.aScenarios[i] == oScenario)
			{
				if (oScenario.extra)
				{
					this.oSpecialScenarios[oScenario.extra].oScenario = null;
					Reflex_Popup.alert('There is no longer a ' + this.oSpecialScenarios[oScenario.extra].sName + ' scenario, please choose one.');
				}
				
				this.aScenarios.splice(i, 1);
				break;
			}
		}
		
		this._refreshScenarioList();
		this._setScenarioConfig();
	},
	
	newScenario	: function(sName, aEvents, sSpecialType, oScenario, oEvent)
	{
		if (!sName && !aEvents)
		{
			// Show popup for creating scenario
			new Developer_Collections_Scenario(this);
			return;
		}
		
		// Add the new scenario and refresh the select list
		if (oScenario)
		{
			// A scenario was edited
			if (oScenario == this._oScenario)
			{
				// The edited scenario is currently active, cache the changes and make them when/if the scenario is cancelled
				this._oCurrentReplacementScenario = this.newObject('scenario', sName, aEvents, sSpecialType);
				Reflex_Popup.alert('The scenario that you edited is currently active, the changes will be made when it is no longer so.');
			}
			else
			{
				// The edited scenario is currently inactive, update it's properties 
				oScenario.name		= sName;
				oScenario.events	= aEvents;
				oScenario.extra		= sSpecialType;
			}
		}
		else
		{
			// A new scenario was created
			oScenario = this.newObject('scenario', sName, aEvents, sSpecialType);
			this.aScenarios.push(oScenario);
		}
		
		if (sSpecialType)
		{
			// A special type name is provided, clear any other scenarios against that name
			for (var i = 0; i < this.aScenarios.length; i++)
			{
				if ((this.aScenarios[i] != oScenario) && (this.aScenarios[i].extra == sSpecialType))
				{
					this.aScenarios[i].extra = null;
				}
			}
			
			// ...then set the new one
			this.oSpecialScenarios[sSpecialType].oScenario = oScenario;
		}
		
		this._refreshScenarioList();
		
		return oScenario;
	},
	
	newObject	: function(sType)
	{
		var oObj	= Object.clone(Developer_Collections[sType]);
		var i		= 1;
		for (var sProperty in oObj)
		{
			if (!Object.isUndefined(arguments[i]) && (arguments[i] !== null))
			{
				oObj[sProperty]	= arguments[i];
			}
			i++;
		}
		
		oObj.id	= this._newId(sType);
		return oObj;
	},
	
	// Private
	
	_buildUI	: function()
	{
		var oContentDiv	= 	$T.div({class: 'developer-collections'},  
								$T.div({class: 'developer-collections-buttons'},
									$T.button('Invoice').observe('click', this._newInvoice.bind(this, null)),
									$T.button('Promise').observe('click', this._newPromise.bind(this, null)),
									$T.button('Payment').observe('click', this._newPayment.bind(this, null)),
									$T.button('Suspension').observe('click', this._newSuspension.bind(this, null)),
									$T.button('Scenarios').observe('click', this.newScenario.bind(this, null, null, null, null)),
									$T.select({class: 'developer-collections-scenario'}).observe('change', this._scenarioSelected.bind(this)),
									$T.button('<<').observe('click', this._prevDay.bind(this)),
									$T.button('>>').observe('click', this._nextDay.bind(this))
								),
								$T.table({class: 'developer-collections-detail'},
									$T.tbody(
										$T.tr(
											$T.td('Invoice'),
											$T.td({class: 'developer-collections-invoice'})
										),
										$T.tr(
											$T.td('Collectable'),
											$T.td({class: 'developer-collections-collectable'})
										),
										$T.tr(
											$T.td('Promise'),
											$T.td({class: 'developer-collections-promise'})
										),
										$T.tr(
											$T.td('Payment'),
											$T.td({class: 'developer-collections-payment'})
										),
										$T.tr(
											$T.td('Event'),
											$T.td({class: 'developer-collections-event'})
										),
										$T.tr(
											$T.td('Suspension'),
											$T.td({class: 'developer-collections-suspension'})
										)
									),
									$T.tfoot(
										$T.tr(
											$T.td('Date'),
											$T.td({class: 'developer-collections-date'})
										),
										$T.tr(
											$T.td('Balance'),
											$T.td({class: 'developer-collections-balance'})
										),
										$T.tr(
											$T.td('Overdue'),
											$T.td({class: 'developer-collections-overdue'})
										)
									)
								),
								$T.textarea({class: 'developer-collections-log'}),
								$T.table({class: 'developer-collections-timeline'},
									$T.tbody(
										$T.tr({class: 'developer-collections-months'}),
										$T.tr({class: 'developer-collections-days'}),
										$T.tr({class: 'developer-collections-timeline-invoice'}),
										$T.tr({class: 'developer-collections-timeline-collectable'}),
										$T.tr({class: 'developer-collections-timeline-promise'}),
										$T.tr({class: 'developer-collections-timeline-payment'}),
										$T.tr({class: 'developer-collections-timeline-event'}),
										$T.tr({class: 'developer-collections-timeline-suspension'})
									)
								)
							);
		
		this._oTable				= oContentDiv.select('.developer-collections-detail').first();
		this._oInvoiceDisplay		= oContentDiv.select('.developer-collections-invoice').first();
		this._oCollectableDisplay	= oContentDiv.select('.developer-collections-collectable').first();
		this._oPromiseDisplay		= oContentDiv.select('.developer-collections-promise').first();
		this._oPaymentDisplay		= oContentDiv.select('.developer-collections-payment').first();
		this._oEventDisplay			= oContentDiv.select('.developer-collections-event').first();
		this._oSuspensionDisplay	= oContentDiv.select('.developer-collections-suspension').first();
		this._oDateDisplay			= oContentDiv.select('.developer-collections-date').first();
		this._oBalanceDisplay		= oContentDiv.select('.developer-collections-balance').first();
		this._oOverdueDisplay		= oContentDiv.select('.developer-collections-overdue').first();
		this._oLogDisplay			= oContentDiv.select('.developer-collections-log').first();
		this._oMonthsDisplay		= oContentDiv.select('.developer-collections-months').first();
		this._oDaysDisplay			= oContentDiv.select('.developer-collections-days').first();
		this._oScenarioSelect		= oContentDiv.select('.developer-collections-scenario').first();
		
		this._oInvoiceButton	= oContentDiv.select('button')[0];
		this._oPromiseButton	= oContentDiv.select('button')[1];
		this._oPaymentButton	= oContentDiv.select('button')[2];
		this._oSuspensionButton	= oContentDiv.select('button')[3];
		
		this._oTimelineInvoiceDisplay		= oContentDiv.select('.developer-collections-timeline-invoice').first();
		this._oTimelineCollectableDisplay	= oContentDiv.select('.developer-collections-timeline-collectable').first();
		this._oTimelinePromiseDisplay		= oContentDiv.select('.developer-collections-timeline-promise').first();
		this._oTimelinePaymentDisplay		= oContentDiv.select('.developer-collections-timeline-payment').first();
		this._oTimelineEventDisplay			= oContentDiv.select('.developer-collections-timeline-event').first();
		this._oTimelineSuspensionDisplay	= oContentDiv.select('.developer-collections-timeline-suspension').first();
		
		this.setTitle('Collections');
		this.addCloseButton();
		this.setContent(oContentDiv);
	},
	
	_refreshScenarioList	: function()
	{
		this._oScenarioSelect.innerHTML = '';
		var iValue = null;
		for (var i = 0; i < this.aScenarios.length; i++)
		{
			var oScenario = this.aScenarios[i];
			if (this._oScenario == oScenario)
			{
				iValue = i;
			}
			
			this._oScenarioSelect.appendChild(
				$T.option({value: i},
					oScenario.name + (oScenario.extra ? ' (' + this.oSpecialScenarios[oScenario.extra].sName + ')' : '')
				)
			);
		}
		this._oScenarioSelect.value = iValue;
	},
	
	_refresh	: function()
	{
		this._applyPayments();
		
		// Suspension (done first because we also determine whether we're in suspension here)
		this._oSuspensionDisplay.innerHTML 	= '';
		this._oSuspension 					= null;
		for (var i = 0; i < this._aSuspensions.length; i++)
		{
			var oSuspension	= this._aSuspensions[i];
			if ((oSuspension.start.getTime() <= this._oLatestDate.getTime()) && (oSuspension.end.getTime() >= this._oLatestDate.getTime()))
			{
				this._oSuspension = oSuspension;
			}
			
			if ((oSuspension.start.getTime() <= this._oDate.getTime()) && (oSuspension.end.getTime() >= this._oDate.getTime()))
			{
				var iDay 		= Math.ceil((this._oDate.getTime() - oSuspension.start.getTime()) / Developer_Collections.MS_IN_DAY) + 1;
				var oDisplay	= $T.div('Day ' + iDay + ' of ' + oSuspension.days);
				if (this._oSuspension && (oSuspension.end.getTime() > this._oDate.getTime()) && (this._oDate.getTime() == this._oLatestDate.getTime()))
				{
					// The suspension is current (show a cancel button)
					oDisplay.appendChild($T.button('Cancel').observe('click', this._cancelSuspension.bind(this)));
				}
				this._oSuspensionDisplay.appendChild(oDisplay);
				
				// Can only have one suspension at a time
				break;
			}
		}
		
		// Promise
		this._oPromiseDisplay.innerHTML	= '';
		
		var iYesterday	= this._oDate.getTime() - Developer_Collections.MS_IN_DAY;
		for (var i = 0; i < this._aPromises.length; i++)
		{
			var oPromise 	= this._aPromises[i];
			var iOverdueDay	= oPromise.due.getTime() + Developer_Collections.MS_IN_DAY;
			if (!oPromise.closed && (oPromise.created.getTime() <= this._oDate.getTime()) && (iOverdueDay >= this._oDate.getTime()))
			{
				var fPaid = this._getPromiseAmountPaid(oPromise);
				this._oPromiseDisplay.appendChild($T.div('Paid: ' + fPaid));
				
				var fOverdue = 0;
				for (var j = 0; j < oPromise.instalments.length; j++)
				{
					var oInstalment = oPromise.instalments[j];
					oInstalment.paid = false;
					if (fPaid == 0)
					{
						fOverdue += oInstalment.amount;
					}
					else if (oInstalment.amount > fPaid)
					{
						fOverdue	+= oInstalment.amount - fPaid;
						fPaid 		= 0;
					}
					else
					{
						fPaid				-= oInstalment.amount;
						oInstalment.paid	= true;
					}
					
					if (oInstalment.due.getTime() == this._oDate.getTime())
					{
						// Instalment due date
						if (fOverdue > 0)
						{
							this._oPromiseDisplay.appendChild($T.div('Instalment ' + (j + 1) + ' of ' + oPromise.instalments.length + ' Due -- $' + fOverdue + ' owed'));
						}
						else
						{
							this._oPromiseDisplay.appendChild($T.div('Instalment ' + (j + 1) + ' of ' + oPromise.instalments.length + ' Due -- PAID'));
						}
					}
					else if (oInstalment.due.getTime() == iYesterday)
					{
						// After instalment due date
						if (fOverdue > 0)
						{
							//  Unmet instalment, cancel promise
							this._oPromiseDisplay.appendChild($T.div('Instalment ' + (j + 1) + ' of ' + oPromise.instalments.length + ' OVERDUE -- $' + fOverdue + ' owed'));
							this._oPromiseDisplay.appendChild($T.div('PROMISE UNMET'));
							
							this._cancelPromise(oPromise);
							this._cancelScenario(this._oScenario);
							this._oScenario = this.oSpecialScenarios.promise_unmet.oScenario;
							this._updateScenarioSelect();
							this._log('Promise unmet, scenario changed', true);
						}
					}
				}
				
				if (!oPromise.closed)
				{
					if (oPromise.due.getTime() < this._oDate.getTime())
					{
						this._oPromiseDisplay.appendChild($T.div('PROMISE MET'));
						this._log('Promise met, marked as closed', true);
						oPromise.closed = new Date(this._oDate.getTime());
					}
					else if (oPromise.due.getTime() == this._oDate.getTime())
					{
						this._oPromiseDisplay.appendChild($T.div('Due: TODAY'));
					}
					else
					{
						this._oPromiseDisplay.appendChild($T.div('Due: ' + this._formatDate(oPromise.due)));
					}
				}
			}
		}
		
		// Invoice
		this._oInvoiceDisplay.innerHTML	= '';
		for (var i = 0; i < this._aInvoices.length; i++)
		{
			var oInvoice	= this._aInvoices[i];
			if ((oInvoice.created.getTime() <= this._oDate.getTime()) && (oInvoice.due.getTime() >= this._oDate.getTime()))
			{
				var sDue	= 'Due: ' + this._formatDate(oInvoice.due);
				if (oInvoice.due.getTime() == this._oDate.getTime())
				{
					sDue	= 'TODAY';
				}
				this._oInvoiceDisplay.appendChild($T.div('Amount: ' + oInvoice.amount + ', ' + sDue));
			}
		}
		
		// Collectable
		this._oCollectableDisplay.innerHTML	= '';
		for (var i = 0; i < this._aCollectables.length; i++)
		{
			var oCollectable	= this._aCollectables[i];
			if ((oCollectable.created.getTime() <= this._oDate.getTime()) && (oCollectable.due.getTime() >= this._oDate.getTime()))
			{
				var sDue	= 'Due: ' + this._formatDate(oCollectable.due);
				if (oCollectable.due.getTime() == this._oDate.getTime())
				{
					sDue	= 'DUE TODAY';
				}
				this._oCollectableDisplay.appendChild($T.div('Amount: ' + oCollectable.amount + ', Balance: ' + oCollectable.balance + ', ' + sDue));
			}
		}
		
		// Payment
		this._oPaymentDisplay.innerHTML	= '';
		for (var i = 0; i < this._aPayments.length; i++)
		{
			var oPayment	= this._aPayments[i];
			if (oPayment.created.getTime() == this._oDate.getTime())
			{
				this._oPaymentDisplay.appendChild($T.div((oPayment.reversed ? '[REVERSED] ' : '') + 'Amount: ' + oPayment.amount + ', Unpaid: ' + oPayment.unpaid));
			}
		}
		
		// Date
		this._oDateDisplay.innerHTML = this._formatDate(this._oDate);
		
		// Balances
		var fBalance		= 0;
		var fOverdue		= 0;
		var oSourceOverdue	= null;
		var oSourceNotDue	= null;
		for (var i = 0; i < this._aCollectables.length; i++)
		{
			var oCollectable = this._aCollectables[i];
			if (!oCollectable.promise)
			{
				fBalance	+= oCollectable.balance;
				if (this._oDate.getTime() > oCollectable.due.getTime())
				{
					fOverdue	+= oCollectable.balance;
					
					if (!oSourceOverdue && (oCollectable.balance > 0))
					{
						oSourceOverdue	= oCollectable;
					}
				}
				else if (!oSourceNotDue && (oCollectable.balance > 0))
				{
					oSourceNotDue	= oCollectable;
				}
			}
		}
		
		// Clear predictions, will be recreated if there is a source collectable
		this._aEventPredictions	= [];
		
		// Generate the next event, also predict the rest of the events in the scenario
		var oSource	= (oSourceOverdue ? oSourceOverdue : oSourceNotDue);
		if (oSource)
		{
			if (!this._oSuspension)
			{
				this._getTodaysEvents(oSource);
			}
			
			// Build list of event predictions
			var oLastEventInstance	= null;
			if (this._oCurrentEventInstance)
			{
				// Predict from the latest actual event (assume automatic completion, as of now)
				oLastEventInstance	= this._oCurrentEventInstance;
			}
			else
			{
				// Predict from the first event
				for (var i = 0; i < this._oScenario.events.length; i++)
				{
					var oEvent	= this._oScenario.events[i];
					if (oEvent.prerequisite == null)
					{
						if (this._oSuspension && (this._oSuspension.end.getTime() > oSource.due.getTime()))
						{
							// Work from the end of the suspension
							oCreated = new Date(this._oSuspension.end.getTime());
						}
						else
						{
							// Work from the due date of the source
							oCreated = new Date(oSource.due.getTime());
						}
						
						oCreated.setDate(oCreated.getDate() + oEvent.offset);
						oLastEventInstance	= this.newObject('event_prediction', oEvent, oCreated, new Date(oCreated.getTime()), this._oScenario);
						this._aEventPredictions.push(oLastEventInstance);
					}
				}
			}
			
			// ... predictions continued
			if (oLastEventInstance)
			{
				bGotNext	= true;
				while (bGotNext)
				{
					var oLastEvent	= oLastEventInstance.event;
					bGotNext		= false;
					for (var i = 0; i < this._oScenario.events.length; i++)
					{
						var oEvent	= this._oScenario.events[i];
						if (oEvent.prerequisite == oLastEvent)
						{
							// Determine when the event would be created, not including day offset (if not actioned, say from now)
							var oCreated	= new Date();
							if (oLastEventInstance.actioned)
							{
								oCreated.setTime(oLastEventInstance.actioned.getTime());
							}
							else
							{
								oCreated.setTime(this._oLatestDate.getTime());
							}
							
							// Add day offset & cache the instance
							oCreated.setDate(oCreated.getDate() + oEvent.offset);
							oLastEventInstance	= this.newObject('event_prediction', oEvent, oCreated, new Date(oCreated.getTime()), this._oScenario);
							this._aEventPredictions.push(oLastEventInstance);
							bGotNext	= true;
							break;
						}
					}
				}
			}
		}
		else if (this._oCurrentEventInstance && !this._oSuspension)
		{
			// Exit collections
			var oCreated = new Date(this._oDate.getTime());
			this._newEventInstance(this._oExitEvent, oCreated, new Date(this._oDate.getTime()));
		}
		
		// Show current event
		if (!this._oSuspension)
		{
			var bShowCurrent	= true;
			this._oEventDisplay.innerHTML	= '';
			for (var i = 0; i < this._aEventInstances.length; i++)
			{
				var oInstance	= this._aEventInstances[i];
				if (!oInstance)
				{
					continue;
				}
				
				var oEventDate = (oInstance.actioned ? oInstance.actioned : this._oLatestDate);
				if (oEventDate.getTime() == this._oDate.getTime())
				{
					var sEventName = oInstance.event.name;
					this._oEventDisplay.appendChild(this._getEventInstanceItem(oInstance));
					this._log('Event: ' + sEventName, true);
					
					if (bShowCurrent && (oInstance == this._oCurrentEventInstance))
					{
						bShowCurrent = false;
					}
				}
			}
			
			// Show the current event (if manual and not already shown)
			if (bShowCurrent && this._oCurrentEventInstance && this._oCurrentEventInstance.event.type.manual && !this._oCurrentEventInstance.actioned && (this._oCurrentEventInstance.created.getTime() <= this._oDate.getTime()))
			{
				var sEventName	= this._oCurrentEventInstance.event.name;
				this._oEventDisplay.appendChild(this._getEventInstanceItem(this._oCurrentEventInstance));
				this._log('Event (Manual): ' + sEventName, true);
			}
		}
		
		this._oBalanceDisplay.innerHTML	= fBalance;
		this._oOverdueDisplay.innerHTML	= fOverdue;
		this._oLogDisplay.style.height	= this._oTable.clientHeight + 'px';
		
		this._refreshTimeline();
		this._refreshScenarioList();
	},
	
	_refreshTimeline	: function()
	{
		// Clear
		this._oMonthsDisplay.innerHTML				= '';
		this._oDaysDisplay.innerHTML				= '';
		this._oTimelineInvoiceDisplay.innerHTML		= '';
		this._oTimelineCollectableDisplay.innerHTML	= '';
		this._oTimelinePromiseDisplay.innerHTML		= '';
		this._oTimelinePaymentDisplay.innerHTML		= '';
		this._oTimelineEventDisplay.innerHTML		= '';
		this._oTimelineSuspensionDisplay.innerHTML	= '';
		
		this._oMonthsDisplay.appendChild($T.td('Month'));
		this._oDaysDisplay.appendChild($T.td('Day'));
		this._oTimelineInvoiceDisplay.appendChild($T.td('Invoice'));
		this._oTimelineCollectableDisplay.appendChild($T.td('Collectable'));
		this._oTimelinePromiseDisplay.appendChild($T.td('Promise'));
		this._oTimelinePaymentDisplay.appendChild($T.td('Payment'));
		this._oTimelineEventDisplay.appendChild($T.td('Event'));
		this._oTimelineSuspensionDisplay.appendChild($T.td('Suspension'));
		
		var iNow	= this._oDate.getTime();
		var iStart	= iNow - (4 * Developer_Collections.MS_IN_DAY);
		var iEnd	= iNow + (35 * Developer_Collections.MS_IN_DAY);
		
		// Recreate, highlighting current day
		var iMonth			= null;
		var oMonthElement	= null;
		for (var iDay = iStart; iDay <= iEnd; iDay += Developer_Collections.MS_IN_DAY)
		{
			var oDate	= new Date(iDay);

			// Month
			if ((iMonth == null) || (oDate.getMonth() !== iMonth))
			{
				iMonth			= oDate.getMonth();
				var iMonthOut	= iMonth + 1;
				oMonthElement	= $T.td(iMonthOut < 10 ? '0' + iMonthOut : iMonthOut);
				oMonthElement.setAttribute('colspan', 1);
				this._oMonthsDisplay.appendChild(oMonthElement);
			}
			else
			{
				oMonthElement.setAttribute('colspan', parseInt(oMonthElement.getAttribute('colspan')) + 1);
			}
			
			if (iMonth == this._oDate.getMonth())
			{
				oMonthElement.addClassName('developer-collections-currentmonth');
			}
			else if (iMonth == this._oLatestDate.getMonth())
			{
				oMonthElement.addClassName('developer-collections-latestmonth');
			}
			
			// Day
			var iDate			= oDate.getDate();
			var oDateElement	= $T.td(iDate < 10 ? '0' + iDate : iDate);
			this._oDaysDisplay.appendChild(oDateElement);
			
			if ((oDate.getDate() == this._oDate.getDate()) && (oDate.getMonth() == this._oDate.getMonth()))
			{
				oDateElement.addClassName('developer-collections-currentday');
			}
			else if ((oDate.getDate() == this._oLatestDate.getDate()) && (oDate.getMonth() == this._oLatestDate.getMonth()))
			{
				oDateElement.addClassName('developer-collections-latestday');
			}
			
			// Highlight any items on this day
			this._oTimelineInvoiceDisplay.appendChild($T.td());
			this._oTimelineCollectableDisplay.appendChild($T.td());
			this._oTimelinePromiseDisplay.appendChild($T.td());
			this._oTimelinePaymentDisplay.appendChild($T.td());
			this._oTimelineEventDisplay.appendChild($T.td());
			this._oTimelineSuspensionDisplay.appendChild($T.td());
			
			// Invoices
			for (var i = 0; i < this._aInvoices.length; i++)
			{
				var oItem	= null;
				if ((this._aInvoices[i].created.getTime() <= iDay) && (this._aInvoices[i].due.getTime() >= iDay))
				{
					oItem	= this._newTimelineItem('invoice', this._aInvoices[i], 'developer-collections-timeline-item-invoice');
				}
				else
				{
					oItem	= $T.div({class: 'developer-collections-timeline-item'});
				}
				this._oTimelineInvoiceDisplay.lastChild.appendChild(oItem);
			}
			
			// Collectables
			for (i = 0; i < this._aCollectables.length; i++)
			{
				var oItem	= null;
				if ((this._aCollectables[i].created.getTime() <= iDay) && (this._aCollectables[i].due.getTime() >= iDay))
				{
					oItem	= this._newTimelineItem('collectable', this._aCollectables[i], 'developer-collections-timeline-item-collectable');
				}
				else
				{
					oItem	= $T.div({class: 'developer-collections-timeline-item'});
				}
				this._oTimelineCollectableDisplay.lastChild.appendChild(oItem);
			}
			
			// Promises
			for (i = 0; i < this._aPromises.length; i++)
			{
				var oItem	= null;
				if ((this._aPromises[i].created.getTime() <= iDay) && (this._aPromises[i].due.getTime() >= iDay) && ((this._aPromises[i].closed == null) || (this._aPromises[i].closed.getTime() >= iDay)))
				{
					var aInstalments	= this._aPromises[i].instalments;
					for (var j = 0; j < aInstalments.length; j++)
					{
						if (aInstalments[j].due.getTime() == iDay)
						{
							var sClass	= (aInstalments[j].paid ? '-paid' : '');
							oItem		= this._newTimelineItem('promise_instalment', aInstalments[j], 'developer-collections-timeline-item-promise' + sClass);
							break;
						}
						else if (aInstalments[j].due.getTime() > iDay)
						{
							break;
						}
					}
				}
				
				if (!oItem)
				{
					oItem	= $T.div({class: 'developer-collections-timeline-item'});
				}
				this._oTimelinePromiseDisplay.lastChild.appendChild(oItem);
			}
			
			// Payments
			for (i = 0; i < this._aPayments.length; i++)
			{
				if (this._aPayments[i].created.getTime() == iDay)
				{
					var sClass	= (this._aPayments[i].reversed ? 'developer-collections-timeline-item-payment-reversed' : 'developer-collections-timeline-item-payment');
					var oItem	= this._newTimelineItem('payment', this._aPayments[i], sClass);
					if (!this._aPayments[i].reversed)
					{
						oItem.appendChild($T.img({src: '../admin/img/template/delete.png'}).observe('click', this._reversePayment.bind(this, this._aPayments[i])));
					}
					this._oTimelinePaymentDisplay.lastChild.appendChild(oItem);
				}
			}
						
			// Events
			for (i = 0; i < this._aEventInstances.length; i++)
			{
				var oDate	= (this._aEventInstances[i].actioned ? this._aEventInstances[i].actioned : this._oLatestDate);
				if (oDate.getTime() == iDay)
				{
					var oItem	= this._newTimelineItem('event_instance', this._aEventInstances[i], 'developer-collections-timeline-item-event');
					this._oTimelineEventDisplay.lastChild.appendChild(oItem);
				}
			}
			
			// Event predictions
			for (i = 0; i < this._aEventPredictions.length; i++)
			{
				if (this._aEventPredictions[i].actioned.getTime() == iDay)
				{
					var oItem	= this._newTimelineItem('event_prediction', this._aEventPredictions[i], 'developer-collections-timeline-item-eventprediction');
					this._oTimelineEventDisplay.lastChild.appendChild(oItem);
				}
			}
			
			// Suspensions
			for (var i = 0; i < this._aSuspensions.length; i++)
			{
				var oSuspension = this._aSuspensions[i];
				if ((oSuspension.start.getTime() <= iDay) && (oSuspension.end.getTime() >= iDay))
				{
					var oItem	= this._newTimelineItem('suspension', oSuspension, 'developer-collections-timeline-item-suspension');
					this._oTimelineSuspensionDisplay.lastChild.appendChild(oItem);
				}
			}
		}
	},
	
	_newId	: function(sType)
	{
		if (Object.isUndefined(this._hIds[sType]))
		{
			this._hIds[sType] = 0;
		}
		else
		{
			this._hIds[sType]++;
		}
		return this._hIds[sType];
	},
	
	_newInvoice	: function(hPromptData)
	{
		if (!hPromptData)
		{
			Developer_Collections._numberPrompt('Invoice', ['Amount:100'], this._newInvoice.bind(this));
			return;
		}
		
		var oCreated	= new Date(this._oDate.getTime());
		var oDue		= new Date(this._oDate.getTime());
		oDue.setDate(oDue.getDate() + 14);
		
		var oInv			= this.newObject('invoice', hPromptData['Amount'], oCreated, oDue);
		var oColl			= this.newObject('collectable', oInv.amount, oInv.amount, oInv, oCreated, oInv.due);
		oInv.collectable	= oColl;
		
		this._aInvoices.push(oInv);
		this._aCollectables.push(oColl);
		this._log('New Invoice - Amount: $' + oInv.amount);
		this._refresh();
	},
	
	_newPromise	: function(hPromptData)
	{
		if (this._oSuspension)
		{
			Reflex_Popup.alert('There is a suspension in effect, a promise cannot be created.');
			return;
		}
		
		if (!hPromptData)
		{
			Developer_Collections._numberPrompt('Promise', ['Amount:100', 'Instalment Amount:50', 'Days Between Instalments:2'], this._newPromise.bind(this));
			return;
		}
		
		var oSourceCollectable	= null;
		for (var i = 0; i < this._aCollectables.length; i++)
		{
			var oCollectable	= this._aCollectables[i];
			if (!oCollectable.promise && oCollectable.balance > 0)
			{
				oSourceCollectable	= oCollectable;
				break;
			}
		}
		
		if (!oSourceCollectable)
		{
			Reflex_Popup.alert('Cannot create a promise when there are no unpaid collectables');
			return;
		}
		
		var oPromise = null;
		for (var i = 0; i < this._aPromises.length; i++)
		{
			if (!this._aPromises[i].closed)
			{
				oPromise = this._aPromises[i];
				break;
			}
		}
		
		var fAmount 					= (oSourceCollectable.balance >= hPromptData['Amount'] ? hPromptData['Amount'] : oSourceCollectable.balance);
		var fInstalmentAmount 			= hPromptData['Instalment Amount'];
		var iNumberOfInstalments 		= Math.ceil(fAmount / fInstalmentAmount);
		var iDaysBetweenInstalments	= hPromptData['Days Between Instalments'];
		
		if (oPromise)
		{
			debugger;
			var oStartDate = new Date(oPromise.due.getTime());
			oStartDate.setDate(oStartDate.getDate() + 1);
			
			for (var i = 0; i < iNumberOfInstalments; i++)
			{
				var iDays	= (i + 1) * iDaysBetweenInstalments;
				var oDue	= new Date(oStartDate.getTime());
				oDue.setDate(oDue.getDate() + iDays);
				oPromise.instalments.push(this.newObject('promise_instalment', fInstalmentAmount, oDue));
			}
			
			oPromise.due = oDue;

			this._log('Existing Promise, $' + fAmount + ' added (' + iNumberOfInstalments + ' instalments of $' + fInstalmentAmount + ')');
		}
		else
		{
			var aInstalments = [];
			for (var i = 0; i < iNumberOfInstalments; i++)
			{
				var iDays	= (i + 1) * iDaysBetweenInstalments;
				var oDue 	= new Date(this._oDate.getTime());
				oDue.setDate(oDue.getDate() + iDays);
				aInstalments.push(this.newObject('promise_instalment', fInstalmentAmount, oDue));
			}
			
			oPromise = this.newObject('promise', aInstalments, new Date(this._oDate.getTime()), oDue);
			this._aPromises.push(oPromise);
			this._log('New Promise - Amount: $' + fAmount + ' (' + iNumberOfInstalments + ' instalments of $' + fInstalmentAmount + ')');
		}
		
		var oPromiseCollectable = this.newObject('collectable', 0, 0, null, new Date(this._oDate.getTime()), new Date(oSourceCollectable.due.getTime()), oPromise);
		this._aCollectables.push(oPromiseCollectable);
		
		this._newTransfer(oSourceCollectable, oPromiseCollectable, fAmount, fAmount);
		this._refresh();
	},
	
	_newPayment	: function(hPromptData)
	{
		if (!hPromptData)
		{
			Developer_Collections._numberPrompt('Payment', ['Amount:50'], this._newPayment.bind(this));
			return;
		}
		
		var fAmount		= hPromptData['Amount'];
		var oCreated	= new Date(this._oDate.getTime());		
		var oPayment	= this.newObject('payment', fAmount, fAmount, oCreated, [], [], false);
		this._aPayments.push(oPayment);
		this._log('New Payment - Amount: $' + fAmount);
		this._refresh();
	},
	
	_reversePayment	: function(oPayment, oEvent)
	{
		// Reverse the payment
		for (var j = 0; j < oPayment.collectables.length; j++)
		{
			var oCollectable		= oPayment.collectables[j];
			var fAmount				= oPayment.amounts[j];
			oCollectable.balance	+= fAmount;
			oPayment.unpaid			+= fAmount;
		}
		
		oPayment.collectables	= [];
		oPayment.amounts		= [];
		oPayment.reversed		= true;
		
		this._log('Payment Reversed - Amount: $' + oPayment.amount);
		this._refresh();
	},
	
	_newSuspension	: function(hPromptData)
	{
		// Check if there is already a suspension
		if (this._oSuspension)
		{
			Reflex_Popup.alert('There is already a suspension in effect.');
			return;
		}
		
		// Check if there is an open promise
		var oPromise = null;
		for (var i = 0; i < this._aPromises.length; i++)
		{
			if (!this._aPromises[i].closed)
			{
				oPromise = this._aPromises[i];
				break;
			}
		}
		
		if (oPromise)
		{
			Reflex_Popup.alert('There is current promise, suspension is not allowed.');
			return;
		}
		
		if (!hPromptData)
		{
			Developer_Collections._numberPrompt('Suspension', ['Number of Days:7'], this._newSuspension.bind(this));
			return;
		}
		
		var iDays		= Math.round(hPromptData['Number of Days']);
		var oStart		= new Date(this._oDate.getTime());
		var oEnd		= new Date(oStart.getTime() + ((iDays - 1) * Developer_Collections.MS_IN_DAY));
		var oSuspension	= this.newObject('suspension', oStart, oEnd, iDays);
		this._aSuspensions.push(oSuspension);
		this._log('New Suspension - ' + iDays + ' from ' + this._formatDate(oStart));
		this._refresh();
	},
	
	_cancelSuspension	: function()
	{
		if (this._oSuspension)
		{
			this._oSuspension.end	= new Date(this._oDate.getTime());
			this._log('Suspension cancelled');
			this._refresh();
		}
	},
	
	_newTransfer	: function(oFrom, oTo, fAmount, fBalance)
	{
		oFrom.amount	-= fAmount;
		oFrom.balance	-= fBalance;
		
		oTo.amount 	+= fAmount;
		oTo.balance	+= fBalance;
		
		var oTransfer = this.newObject('transfer', oFrom, oTo, fAmount, fBalance, new Date(this._oDate.getTime()));
		this._aTransfers.push(oTransfer);
		this._log('Transfer - Amount: $' + fAmount + ', Balance: $' + fBalance, true);
	},
	
	_prevDay	: function()
	{
		this._oDate.setDate(this._oDate.getDate() - 1);
		
		// Disable the action buttons, cannot affect the past
		this._oInvoiceButton.disabled		= true;
		this._oPromiseButton.disabled		= true;
		this._oPaymentButton.disabled		= true;
		this._oSuspensionButton.disabled	= true;
		this._oScenarioSelect.disabled		= true;
		
		this._refresh();
	},
	
	_nextDay	: function()
	{
		this._oDate.setDate(this._oDate.getDate() + 1);
		
		if (this._oDate.getTime() >= this._oLatestDate.getTime())
		{
			// Gone forward into the future, enable the action buttons
			this._oLatestDate	= new Date(this._oDate.getTime());
			
			this._oInvoiceButton.disabled		= false;
			this._oPromiseButton.disabled		= false;
			this._oPaymentButton.disabled		= false;
			this._oSuspensionButton.disabled	= false;
			this._oScenarioSelect.disabled		= false;
		}
		
		this._refresh();
	},
	
	_scenarioSelected	: function(oEvent)
	{
		var oScenario	= this.aScenarios[oEvent.target.value];
		if (oScenario && oScenario != this._oScenario)
		{
			this._cancelScenario(this._oScenario);
			this._oScenario				= oScenario;
			this._oCurrentEventInstance	= null;
			this._log('Scenario changed to: ' + oScenario.name);
			this._refresh();
		}
	},
	
	_cancelScenario	: function(oScenario)
	{
		// Action any un-actioned event instances
		for (var i = 0; i < this._aEventInstances.length; i++)
		{
			var oInstance = this._aEventInstances[i];
			if ((oInstance.scenario == oScenario) && !oInstance.actioned)
			{
				oInstance.actioned = new Date(this._oLatestDate.getTime())
			}
		}
		
		// Replace this scenario with the current replacement (if there is one), this means changes were made to it while it was active
		if (this._oCurrentReplacementScenario)
		{
			oScenario.name 		= this._oCurrentReplacementScenario.name;
			oScenario.events	= this._oCurrentReplacementScenario.events;
			oScenario.extra		= this._oCurrentReplacementScenario.extra;
			this._oCurrentReplacementScenario = null;
		}
	},
	
	_applyPayments	: function()
	{
		var aCollectables = this._getPrioritisedCollectables();
		
		// Build list of non-reversed payments
		var aNonReversed	= [];
		for (var i = 0; i < this._aPayments.length; i++)
		{
			var oPayment	= this._aPayments[i];
			if (!oPayment.reversed)
			{
				/*for (var j = 0; j < oPayment.collectables.length; j++)
				{
					var oCollectable	= oPayment.collectables[j];
					var fAmount			= oPayment.amounts[j];
					
					oCollectable.balance	+= fAmount;
					oPayment.unpaid			+= fAmount;
				}
				
				oPayment.collectables	= [];
				oPayment.amounts		= [];*/
				
				aNonReversed.push(oPayment);
			}
		}
		
		// Apply payments
		var iPayment		= 0;
		var iCollectable	= 0;
		while (iCollectable < aCollectables.length)
		{
			var oCollectable	= aCollectables[iCollectable];
			if (oCollectable.balance < 0)
			{
				this._redistributeCreditCollectable(oCollectable)
				iCollectable++;
			}
			else
			{
				var oPayment	= aNonReversed[iPayment];
				if (!oPayment)
				{
					iCollectable++;
					continue;
				}
				
				var fAmount	= oPayment.unpaid;
				if (fAmount > oCollectable.balance)
				{
					fAmount	= oCollectable.balance;
				}
				
				oPayment.unpaid			-= fAmount;
				oCollectable.balance	-= fAmount;
				oPayment.collectables.push(oCollectable);
				oPayment.amounts.push(fAmount);
				
				if (oPayment.unpaid == 0)
				{
					iPayment++;
					continue;
				}
				
				if (oCollectable.balance == 0)
				{
					iCollectable++;
				}
			}
		}
	},
	
	_redistributeCreditCollectable	: function(oCollectable)
	{
		var iCollectable	= 0;
		var fCredit			= Math.abs(oCollectable.balance);
		while (iCollectable < this._aCollectables.length)
		{
			var oCollectable	= this._aCollectables[iCollectable];
			if (oCollectable.balance > 0)
			{
				var fAmount	= fCredit;
				if (fAmount > oCollectable.balance)
				{
					fAmount	= oCollectable.balance;
				}
				
				fCredit					-= fAmount;
				oCollectable.balance	-= fAmount;
				
				if (oCollectable.balance == 0)
				{
					iCollectable++;
				}
				
				if (fCredit == 0)
				{
					break;
				}
			}
		}
		
		this._log('Credit Collectable Redistributed', true);
	},
	
	_updateScenarioSelect	: function()
	{
		if (this._oScenarioSelect)
		{
			for (var i = 0; i < this._oScenarioSelect.options.length; i++)
			{
				if (this._oScenarioSelect.options[i].innerHTML == this._oScenario.name)
				{
					this._oScenarioSelect.value	= this._oScenarioSelect.options[i].value;
					break;
				}
			}
		}
	},
	
	_formatDate	: function(oDate)
	{
		return oDate.toString().split(oDate.getFullYear())[0] + oDate.getFullYear();
	},
	
	_getPromiseAmountPaid	: function(oPromise)
	{
		var fPaid = 0;
		for (var j = 0; j < this._aCollectables.length; j++)
		{
			if (this._aCollectables[j] && this._aCollectables[j].promise == oPromise)
			{
				fPaid += this._aCollectables[j].amount - this._aCollectables[j].balance;
			}		
		}
		return fPaid;
	},
	
	_getPromiseBalance	: function(oPromise)
	{
		var fBalance = 0;
		for (var j = 0; j < this._aCollectables.length; j++)
		{
			if (this._aCollectables[j] && this._aCollectables[j].promise == oPromise)
			{
				fBalance += this._aCollectables[j].balance;
			}		
		}
		return fBalance;
	},
	
	_cancelPromise	: function(oPromise)
	{
		var fBalance = 0;
		for (var j = 0; j < this._aCollectables.length; j++)
		{
			if (this._aCollectables[j] && this._aCollectables[j].promise == oPromise)
			{
				this._aCollectables[j].promise = null;
			}
		}
		
		oPromise.closed = new Date(this._oDate.getTime());
		this._log('Promise cancelled', true);
	},
	
	_log	: function(sMessage, bSystemMessage)
	{
		var sDate 					= new Date().$format('d/m/y H:i:s');
		this._oLogDisplay.value 	+= (bSystemMessage ? '> ' : '') + sMessage + " (" + sDate + ")\n";
		this._oLogDisplay.scrollTop	= this._oLogDisplay.scrollHeight;
	},
	
	_newTimelineItem	: function(sType, oData, sExtraClass)
	{
		var oItem	= $T.div({class: 'developer-collections-timeline-item' + (sExtraClass ? ' ' + sExtraClass : '')});
		oItem.observe('mouseover', this._showTimelineItemDetails.bind(this, sType, oData));
		oItem.observe('mouseout', this._oTimelinePopup.hide.bind(this._oTimelinePopup));
		return oItem;
	},
	
	_showTimelineItemDetails	: function(sType, oData, oEvent)
	{
		// Show details of the item
		var oItem	= oEvent.target;
		var oTBody	= $T.tbody();
		for (var sField in Developer_Collections[sType])
		{
			var mValue	= oData[sField];
			if (mValue instanceof Date)
			{
				// Date, format it
				mValue	= this._formatDate(mValue);
			}
			else if (Object.isArray(mValue) || (typeof mValue == 'object'))
			{
				// Ignore arrays
				continue;
			}
			
			if (mValue == null)
			{
				mValue	= '-';
			}
			
			var sFieldProper	= sField[0].toUpperCase() + sField.substr(1);
			oTBody.appendChild(
				$T.tr(
					$T.td(sFieldProper),
					$T.td(mValue)
				)
			);
		}
		
		// Extra details
		this._extraTimelineItemDetails(sType, oData, oTBody);
		
		// Show the popup
		var sTitle	= Developer_Collections.TIMELINE_ITEM_TITLES[sType];
		this._oTimelinePopup.innerHTML	= '';
		this._oTimelinePopup.appendChild(
			$T.div(
				$T.div({class: 'developer-collections-timeline-item-title'},
					sTitle ? sTitle : ''
				),
				$T.table(oTBody)
			)
		);
		this._oTimelinePopup.style.left	= (oEvent.clientX + oItem.clientWidth) + 'px';
		this._oTimelinePopup.style.top	= (oEvent.clientY + oItem.clientHeight) + 'px';
		this._oTimelinePopup.show();
	},
	
	_actionEventInstance	: function(oInstance)
	{
		var sEventName 		= oInstance.event.name;
		oInstance.actioned	= new Date(this._oDate.getTime());
		this._invokeEventInstance(oInstance);
		this._log('Manual Event actioned: ' + sEventName);
		this._refresh();
	},
	
	_getEventInstanceManual	: function(oInstance)
	{
		return (oInstance.event.type ? oInstance.event.type.manual : oInstance.event.manual);
	},
	
	_getEventInstanceItem	: function(oInstance)
	{
		var sEventName	= 	oInstance.event.name;
		var bManual		= 	this._getEventInstanceManual(oInstance);
		var oItem		= 	$T.div(
								$T.span(sEventName + (bManual ? ' (Manual)' : ''))
							);
		
		if (!oInstance.actioned && bManual)
		{
			// Manual, and incomplete show button
			var oButton	= $T.button('Complete');
			oButton.observe('click', this._actionEventInstance.bind(this, oInstance));
			oItem.appendChild(oButton);
		}
		
		return oItem;
	},
	
	_getTodaysEvents	: function(oSourceCollectable)
	{
		var oEvent	= this._getNextEvent(oSourceCollectable);
		while (oEvent)
		{
			oEvent	= this._getNextEvent(oSourceCollectable);
		}
	},
	
	_getNextEvent	: function(oSourceCollectable)
	{
		var oEvent	= null;
		var oFrom	= null;
		if (this._oCurrentEventInstance)
		{
			// Offset from previous event
			for (var i = 0; i < this._oScenario.events.length; i++)
			{
				if (this._oScenario.events[i].prerequisite == this._oCurrentEventInstance.event)
				{
					oEvent	= this._oScenario.events[i];
					break;
				}
			}
			
			oFrom	= this._oCurrentEventInstance.actioned;
		}
		else
		{
			// First event
			for (var i = 0; i < this._oScenario.events.length; i++)
			{
				if (this._oScenario.events[i].prerequisite == null)
				{
					oEvent	= this._oScenario.events[i];
					break;
				}
			}
			
			oFrom	= oSourceCollectable.due;
		}
		
		if (oEvent && oFrom)
		{
			var iDays	= (this._oLatestDate.getTime() - oFrom.getTime()) / Developer_Collections.MS_IN_DAY;
			if (oEvent.offset <= iDays)
			{
				// Event has been reached, schedule it
				var oCreated	= new Date(this._oDate.getTime());
				var oActioned	= (oEvent.type.manual ? null : new Date(this._oDate.getTime()));
				var oInstance	= this._newEventInstance(oEvent, oCreated, oActioned, this._oScenario);
				
				return oInstance;
			}
			else
			{
				return null;
			}
		}
	},
	
	_newEventInstance	: function()
	{
		var aArgs = $A(arguments);
		aArgs.unshift('event_instance');
		
		var oInstance = this.newObject.apply(this, aArgs);
		this._aEventInstances.push(oInstance);
		this._oCurrentEventInstance = oInstance;
		
		// Invoke any functionality tied to the event
		if (!this._getEventInstanceManual(oInstance))
		{
			this._invokeEventInstance(oInstance);
		}
		
		return oInstance;
	},
	
	_extraTimelineItemDetails	: function(sType, oData, oTBody)
	{
		switch (sType)
		{
			case 'collectable':
				if (oData.promise)
				{
					oTBody.appendChild(
						$T.tr(
							$T.td('Promise'),
							$T.td('Yes')
						)
					);
				}
				break;
			case 'event_instance':
			case 'event_prediction':
				// Type of event
				oTBody.insertBefore(
					$T.tr(
						$T.td('Name'),
						$T.td(oData.event.name)
					),
					oTBody.firstChild
				);
				
				// Day offset
				oTBody.appendChild(
					$T.tr(
						$T.td('Offset'),
						$T.td(oData.event.offset || '-')
					)
				);
				
				// Scenario
				oTBody.appendChild(
					$T.tr(
						$T.td('Scenario'),
						$T.td(oData.scenario ? oData.scenario.name : '-')
					)
				);
				break;
		}
	},
	
	_getPrioritisedCollectables	: function()
	{
		// Sort the collectables by their due date
		var aByDueDate 	= {};
		var aDueDates	= [];
		for (var i = 0; i < this._aCollectables.length; i++)
		{
			var oCollectable = this._aCollectables[i];
			if (oCollectable.balance == 0)
			{
				continue;
			}
			
			// Determine due date
			var iDue = oCollectable.due.getTime();
			if (!!oCollectable.promise)
			{
				// Tied to a promise, the due date becomes the due date of the next unpaid instalment
				for (var j = 0; j < oCollectable.promise.instalments.length; j++)
				{
					if (!oCollectable.promise.instalments[j].paid)
					{
						iDue = oCollectable.promise.instalments[j].due.getTime();
						break;
					}
				}
			}
			
			if (!aByDueDate[iDue])
			{
				aByDueDate[iDue] = [];
			}
			
			// Cache due date so it can be sorted, keep distinct list
			if (aDueDates.indexOf(iDue) == -1)
			{
				aDueDates.push(iDue);
			}
		
			// Not a promise, give it priority based on when it was created
			var iCreated	= oCollectable.created.getTime();
			var bPlaced		= false;
			for (var j = 0; j < aByDueDate[iDue].length; j++)
			{
				var bGotPromise = !!aByDueDate[iDue][j].promise;
				if (((bGotPromise && !!oCollectable.promise) || (!bGotPromise && !oCollectable.promise)) && (iCreated < aByDueDate[iDue][j].created.getTime()))
				{
					aByDueDate[iDue].splice(j, 0, oCollectable);
					bPlaced = true;
					break;
				}
			}
			
			if (!bPlaced)
			{
				// Not placed yet
				if (oCollectable.promise)
				{
					// Promise, put at front
					aByDueDate[iDue].unshift(oCollectable);
				}
				else
				{
					// Non promise, append
					aByDueDate[iDue].push(oCollectable);
				}
			}
		}
		
		var aCollectables = [];
		if (aDueDates.length > 0)
		{
			// Sort the due dates
			aDueDates = aDueDates.sort();
			
			// Create a list of the collectables from the sorted data
			for (var i = 0; i < aDueDates.length; i++)
			{
				iDue = aDueDates[i];
				if (isNaN(iDue))
				{
					continue;
				}
				
				for (var j = 0; j < aByDueDate[iDue].length; j++)
				{
					aCollectables.push(aByDueDate[iDue][j]);
				}
			}
		}
		
		return aCollectables;
	},
	
	_invokeEventInstance	: function(oInstance)
	{
		if (oInstance.event.type && oInstance.event.type.invoke)
		{
			oInstance.event.type.invoke(oInstance);
		}
		else if (oInstance.event.invoke)
		{
			oInstance.event.invoke(oInstance)
		}
	},
	
	_exitCollections	: function()
	{
		// Change scenario back to standard
		this._oCurrentEventInstance = null;
		this._cancelScenario(this._oScenario);
		this._oScenario = this.oSpecialScenarios.standard.oScenario;
		this._updateScenarioSelect();
		this._log('Exiting collections, scenario changed to standard', true);
	},
	
	_consolidateDebt	: function()
	{
		// Cancel promise
		var oPromise = null;
		for (var i = 0; i < this._aPromises.length; i++)
		{
			if (!this._aPromises[i].closed)
			{
				oPromise = this._aPromises[i];
				break;
			}
		}
		
		if (oPromise)
		{
			this._cancelPromise(oPromise);
		}
	},
	
	_createScenarioEventTypes	: function()
	{
		// Event types
		this._oReminderEvent			= this.newObject('event_type', 'Reminder', false);
		this._oCallEvent				= this.newObject('event_type', 'Call', true);
		this._oSuspendEvent				= this.newObject('event_type', 'Suspend', false);
		this._oExitEvent				= this.newObject('event_type', 'Exit Collections', false, this._exitCollections.bind(this));
		this._oTDCEvent					= this.newObject('event_type', 'TDC', false);
		this._oOCAEvent					= this.newObject('event_type', 'OCA', false);
		this._oListEvent1				= this.newObject('event_type', 'List Event 1', false);
		this._oListEvent2				= this.newObject('event_type', 'List Event 2', false);
		this._oDebtConsolidationEvent	= this.newObject('event_type', 'Debt Consolidation', true, this._consolidateDebt.bind(this));
		
		this.aEventTypes = 	[
                		   		this._oReminderEvent, 
                		   		this._oCallEvent, 
                		   		this._oSuspendEvent,
                		   		this._oExitEvent,
                		   		this._oTDCEvent,
                		   		this._oOCAEvent,
                		   		this._oListEvent1,
                		   		this._oListEvent2,
                		   		this._oDebtConsolidationEvent
                		   	];
	},
	
	_createScenarios	: function()
	{
		// Standard scenario
		var oReminder	= this.newObject('event', 'Friendly Reminder', this._oReminderEvent, null, 1);
		var oCall		= this.newObject('event', 'Phone Call', this._oCallEvent, oReminder, 2);
		var oSuspend	= this.newObject('event', 'Suspend Account', this._oSuspendEvent, oCall, 3);
		this.oSpecialScenarios.standard.oScenario = this.newScenario('Standard', [oReminder, oCall, oSuspend], 'standard');
		
		// List scenario
		var oListEvent1	= this.newObject('event', 'List 1', this._oListEvent1, null, 3);
		var oReminder	= this.newObject('event', 'Friendly Reminder', this._oReminderEvent, oListEvent1, 3);
		var oCall		= this.newObject('event', 'Phone Call', this._oCallEvent, oReminder, 3);
		var oListEvent2	= this.newObject('event', 'List 2', this._oListEvent2, oCall, 0);
		this.newScenario('List', [oListEvent1, oReminder, oCall, oListEvent2]);
		
		// Promise unmet scenario
		var oCall	= this.newObject('event', 'Phone Call', this._oCallEvent, null, 1);
		var oTDC	= this.newObject('event', 'TDC', this._oTDCEvent, oCall, 2);
		var oConsol	= this.newObject('event', 'Debt Consolidation', this._oDebtConsolidationEvent, oTDC, 2);
		var oOCA	= this.newObject('event', 'OCA', this._oOCAEvent, oConsol, 2);
		this.oSpecialScenarios.promise_unmet.oScenario = this.newScenario('Unmet Promise', [oCall, oTDC, oConsol, oOCA], 'promise_unmet');		
	},
	
	_setScenarioConfig	: function()
	{
		var aCookieData = [];
		for (var i = 0; i < this.aScenarios.length; i++)
		{
			var oScenario	= this.aScenarios[i];
			var oCopy 		= this.newObject('scenario', oScenario.name, [], oScenario.extra);
			var bContinue 	= true;
			var oPrereq 	= null;
			while (bContinue)
			{
				bContinue 	= false;
				for (var j = 0; j < oScenario.events.length; j++)
				{
					var oEvent = oScenario.events[j];
					if (oEvent.prerequisite == oPrereq)
					{
						oPrereq 	= oEvent;
						bContinue	= true;
						
						// Add the event copy with type id instead of instance, and leaving out the prereq because they are stored in order
						oCopy.events.push(this.newObject('event', oEvent.name, oEvent.type.id, null, oEvent.offset));
						break;
					}
				}
			}
			
			aCookieData[i] = oCopy;
		}
		
		var sCookie 			= aCookieData.toJSON();
		var iNewCookieLength	= document.cookie.toString().length + sCookie.length;
		if (iNewCookieLength > 4096)
		{
			Reflex_Popup.alert('There are too many scenarios to be saved, please delete one.');
			return;
		}
		
		Flex.cookie.create(Developer_Collections.SCENARIO_COOKIE_NAME, sCookie, 7);
	},
	
	_getScenarioConfig	: function()
	{
		var sCookie = Flex.cookie.read(Developer_Collections.SCENARIO_COOKIE_NAME)
		if (sCookie)
		{
			var aScenarios	= [];
			var aCookieData = sCookie.evalJSON();
			for (var i = 0; i < aCookieData.length; i++)
			{
				var oCopy		= aCookieData[i];
				var oScenario 	= this.newObject('scenario', oCopy.name, [], oCopy.extra);
				var oPrereq		= null;
				for (var j = 0; j < oCopy.events.length; j++)
				{
					// Create an event using the type id to grab the instance and using the previous event as the prerequisite
					var oEventCopy 	= oCopy.events[j];
					var oEvent 		= this.newObject('event', oEventCopy.name, this.aEventTypes[oEventCopy.type], oPrereq, oEventCopy.offset);
					oPrereq 		= oEvent;
					oScenario.events.push(oEvent);
				}
				aScenarios[i] = oScenario;
			}
			return aScenarios;
		}
		return null;
	}
});


// Static


Object.extend(Developer_Collections,
{
	MS_IN_DAY	: 1000 * 60 * 60 * 24,
	
	invoice	: 
	{
		amount		: null,
		created		: null,
		due			: null,
		collectable	: null
	},
	
	collectable	: 
	{
		amount	: null,
		balance	: null,
		invoice	: null,
		created	: null,
		due		: null,
		promise	: null
	},
	
	payment	: 
	{
		amount			: null,
		unpaid			: null,
		created			: null,
		collectables	: null,
		amounts			: null,
		reversed		: null
	},
	
	promise	: 
	{
		instalments	: null,
		created			: null,
		due				: null,
		closed			: null
	},
	
	promise_instalment	: 
	{
		amount	: null,
		due		: null,
		paid	: null
	},
	
	transfer	: 
	{
		from	: null,
		to		: null,
		amount	: null,
		balance	: null,
		created	: null
	},
	
	event_type	:
	{
		name	: null,
		manual	: null,
		invoke	: null
	},
	
	event	: 
	{
		name			: null,
		type			: null,
		prerequisite	: null,
		offset			: null
	},
	
	event_instance	: 
	{
		event		: null,
		created		: null,
		actioned	: null,
		scenario	: null
	},
	
	event_prediction	: 
	{
		event		: null,
		created		: null,
		actioned	: null,
		scenario	: null
	},
	
	scenario	: 
	{
		name	: null,
		events	: null,
		extra	: null
	},
	
	suspension	:
	{
		start	: null,
		end		: null,
		days	: null
	},
	
	TIMELINE_ITEM_TITLES	:
	{
		invoice				: 'Invoice',
		collectable			: 'Collectable',
		payment				: 'Payment',
		promise				: 'Promise',
		promise_instalment	: 'Promise Instalment',
		event_instance		: 'Event',
		event_prediction	: 'Predicted Event',
		suspension			: 'Suspension'
	},
	
	_numberPrompt	: function(sTitle, aFields, fnCallback)
	{
		var oContentDiv	= $T.div({class: 'developer-collections-prompt'});
		var hInputs		= [];
		var oTBody		= $T.tbody();
		for (var i = 0; i < aFields.length; i++)
		{
			var sField		= aFields[i];
			var aSplit		= sField.split(':');
			sField			= aSplit[0];
			hInputs[sField]	= $T.input({type: 'text'});
			if (aSplit[1])
			{
				// Value given (:?)
				hInputs[sField].value	= aSplit[1];
			}
			oContentDiv.appendChild(
				$T.tr(
					$T.th(sField),
					$T.td(hInputs[sField])
				)
			);
		}
	
		var oPopup	= new Reflex_Popup();
		oPopup.setTitle(sTitle);
		oPopup.addCloseButton();
		
		var oOK	= $T.button('OK');
		oOK.observe('click', Developer_Collections._numberPromptFinished.curry(fnCallback, oPopup, hInputs))
		var oCancel	= $T.button('Cancel');
		oCancel.observe('click', oPopup.hide.bind(oPopup));
		
		oContentDiv.appendChild(
			$T.div({class: 'buttons'},
				oOK,
				oCancel
			)	
		);
		
		oPopup.setContent(oContentDiv);
		oPopup.display();
		oOK.focus();
	},
	
	_numberPromptFinished	: function(fnCallback, oPopup, hInputs)
	{
		var oData	= {};
		for (var sField in hInputs)
		{
			if (isNaN(parseFloat(hInputs[sField].value)))
			{
				hInputs[sField].value	= 0;
			}
			oData[sField]	= parseFloat(hInputs[sField].value);
		}
		
		oPopup.hide();
		fnCallback(oData);
	},
	
	SCENARIO_COOKIE_NAME : 'scenario_cookie'
});

////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
// NEW SCENARIO POPUP
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////

var Developer_Collections_Scenario = Class.create(Reflex_Popup,
{
	initialize	: function($super, oParentPopup)
	{
		$super(50);
		
		this._oParent = oParentPopup;
		this._buildUI();
		this._updateEvents();
	},
	
	_buildUI	: function()
	{
		var oContent =	$T.div({class: 'developer-collections-scenario-popup'},
							$T.table({class: 'reflex input'},
								$T.tbody(
									$T.tr(
										$T.th('Choose Scenario'),
										$T.td(this._getScenarioSelect())
									),
									$T.tr(
										$T.th('Name'),
										$T.td(
											$T.input({class: 'developer-collections-scenario-popup-name'})
										)	
									),
									$T.tr(
										$T.th('Events'),
										$T.td(
											$T.div({class: 'developer-collections-scenario-popup-event-count-container'},
												$T.div('Number of Events'),
												$T.input({class: 'developer-collections-scenario-popup-event-count', value: 3}).observe('change', this._updateEvents.bind(this))
											),
											$T.table({class: 'developer-collections-scenario-popup-events-table'},
												$T.thead(
													$T.tr(
														$T.td('Name'),
														$T.td('Type'),
														$T.td('Day Offset')
													)
												),
												$T.tbody({class: 'developer-collections-scenario-popup-events'})	
											)
										)
									),
									$T.tr(
										$T.th('Special Scenario'),
										$T.td(
											this._getSpecialSelect()
										)
									)
								)
							),
							$T.div({class: 'developer-collections-scenario-popup-buttons'},
								$T.button('Save').observe('click', this._save.bind(this)),
								$T.button('Delete').observe('click', this._delete.bind(this, null)),
								$T.button('Cancel').observe('click', this.hide.bind(this))
							)
						);
		
		this._oNameInput		= oContent.select('.developer-collections-scenario-popup-name').first();
		this._oEventCountInput	= oContent.select('.developer-collections-scenario-popup-event-count').first();
		this._oEventTBody		= oContent.select('.developer-collections-scenario-popup-events').first();
		this._oScenarioSelect	= oContent.select('.developer-collections-scenario-popup-scenario').first();
		this._oDeleteButton		= oContent.select('button')[1];
		this._oDeleteButton.hide();
		
		this.setTitle('New Scenario');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();
	},
	
	_refresh	: function()
	{
		this._refreshSpecialSelect();
		this._oNameInput.value 			= (this._oScenario ? this._oScenario.name : '');
		this._oEventCountInput.value 	= (this._oScenario ? this._oScenario.events.length : 3);
		this._oSpecialSelect.value		= ((this._oScenario && this._oScenario.extra) ? this._oScenario.extra : 'NONE');
		this._updateEvents();
	},
	
	_updateEvents	: function()
	{
		var iCount	= parseInt(this._oEventCountInput.value);
		var aRows	= this._oEventTBody.select('tr');
		for (var i = 0; i < iCount; i++)
		{
			var oRow = aRows[i];
			if (!oRow)
			{
				oRow = this._newEventRow();
				this._oEventTBody.appendChild(oRow);
			}
			
			var oNameInput 	= oRow.select('.developer-collections-scenario-popup-event-name').first();
			var oTypeSelect	= oRow.select('.developer-collections-scenario-popup-event-type').first();
			var oDayInput 	= oRow.select('.developer-collections-scenario-popup-event-day').first();
			if (this._oScenario && this._oScenario.events[i])
			{
				var oEvent			= this._oScenario.events[i];
				oNameInput.value 	= oEvent.name;
				oTypeSelect.value 	= oEvent.type.id;
				oDayInput.value 	= oEvent.offset;
			}
			else
			{
				oNameInput.value 	= '';
				oTypeSelect.value 	= '';
				oDayInput.value 	= '1';
			}
		}
		
		// Remove extra event rows
		for (var i = iCount; i < aRows.length; i++)
		{
			if (aRows[i])
			{
				aRows[i].remove();
			}
		}
	},
	
	_newEventRow	: function()
	{
		return 	$T.tr(
					$T.td($T.input({class: 'developer-collections-scenario-popup-event-name'})),
					$T.td(this._getTypeSelect()),
					$T.td($T.input({class: 'developer-collections-scenario-popup-event-day', value: 1}))
				);
	},
	
	_getTypeSelect	: function()
	{
		var oSelect = $T.select({class: 'developer-collections-scenario-popup-event-type'});
		for (var i = 0; i < this._oParent.aEventTypes.length; i++)
		{
			var oType = this._oParent.aEventTypes[i];
			oSelect.appendChild(
				$T.option({value: i},
					oType.name + (oType.manual ? ' (Manual)' : '')
				)
			);
		}
		return oSelect;
	},
	
	_getScenarioSelect	: function()
	{
		var oSelect = $T.select({class: 'developer-collections-scenario-popup-scenario'});
		oSelect.observe('change', this._scenarioChanged.bind(this));
		oSelect.appendChild(
			$T.option({value: 'NEW'}, 
				'-- New --'
			)
		);
		
		for (var i = 0; i < this._oParent.aScenarios.length; i++)
		{
			var oItem = this._oParent.aScenarios[i];
			oSelect.appendChild(
				$T.option({value: i},
					oItem.name
				)
			);
		}
		return oSelect;
	},
	
	_scenarioChanged	: function(oEvent)
	{
		var oSelect = this._oScenarioSelect;
		if (oSelect.value == 'NEW')
		{
			// New
			this._oScenario = null;
			this._oDeleteButton.hide();
		}
		else
		{
			// Edit
			this._oScenario = this._oParent.aScenarios[oSelect.value];
			this._oDeleteButton.show();
		}
		this._refresh();
	},
	
	_getSpecialSelect	: function()
	{
		var oSelect 			= $T.select({class: 'developer-collections-scenario-popup-special'});
		this._oSpecialSelect 	= oSelect;
		this._refreshSpecialSelect();
		return oSelect;
	},
	
	_refreshSpecialSelect	: function()
	{
		this._oSpecialSelect.innerHTML = '';
		this._oSpecialSelect.appendChild(
			$T.option({value: 'NONE'},
				'-- None --'
			)
		);
		
		for (var sType in this._oParent.oSpecialScenarios)
		{
			var oSpecialScenario = this._oParent.oSpecialScenarios[sType];
			this._oSpecialSelect.appendChild(
				$T.option({value: sType},
					oSpecialScenario.sName + (oSpecialScenario.oScenario ? ' (currently assigned to ' + oSpecialScenario.oScenario.name + ')' : '')
				)
			);
		}
	},
	
	_save	: function()
	{
		var aNames	= this._oEventTBody.select('.developer-collections-scenario-popup-event-name');
		var aTypes	= this._oEventTBody.select('.developer-collections-scenario-popup-event-type');
		var aDays	= this._oEventTBody.select('.developer-collections-scenario-popup-event-day');
		
		var aEvents 	= [];
		var oPrevious	= null;
		for (var i = 0; i < aNames.length; i++)
		{
			var oEvent	= 	this._oParent.newObject(
								'event', 
								aNames[i].value, 							// name 
								this._oParent.aEventTypes[aTypes[i].value],	// type 
								oPrevious, 									// prerequisite
								Math.abs(parseInt(aDays[i].value)) 			// offset (non-negative)
							);
			oPrevious	= 	oEvent;
			aEvents.push(oEvent);
		}
		
		var sSpecialType = ((this._oSpecialSelect.value == 'NONE') ? null : this._oSpecialSelect.value);
		this._oParent.newScenario(this._oNameInput.value, aEvents, sSpecialType, this._oScenario);
		this._oParent.storeScenarios();
		this._oScenarioSelect.value = 'NEW';
		this._scenarioChanged();
	},
	
	_delete	: function(bConfirm, oEvent)
	{
		if (bConfirm === null)
		{
			if (this._oParent.isCurrentScenario(this._oScenario))
			{
				Reflex_Popup.alert('This scenario is active, it cannot be deleted.');
			}
			else
			{
				Reflex_Popup.yesNoCancel('Are you sure you want to delete ' + this._oScenario.name + '?', {fnOnYes: this._delete.bind(this, true)});
			}
			return;
		}
		
		if (bConfirm)
		{
			this._oParent.deleteScenario(this._oScenario);
			this._oScenarioSelect.value = 'NEW';
			this._scenarioChanged();
		}
	}
});

