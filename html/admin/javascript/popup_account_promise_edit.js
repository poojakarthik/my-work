
var	Popup_Account_Promise_Edit	= Class.create(Reflex_Popup, {

	initialize	: function ($super, iAccountId) {
		$super(50);
		this.setTitle('' + iAccountId + ': New Promise to Pay');
		this.addCloseButton();

		this._iAccountId	= iAccountId;

		this._getData(this._buildUI.bind(this));
	},

	_getData	: function (fnCallback, oResponse) {
		
		/* DEBUG 
		fnCallback(Popup_Account_Promise_Edit.DUMMY_DATA);
		/* DEBUG */
		
		if (typeof oResponse === 'undefined') {
			// AJAX!
			jQuery.json.jsonFunction(
				this._getData.bind(this, fnCallback),
				this._getData.bind(this, fnCallback),
				'Collection_Promise',
				'getDetailsForAccount'
			)(this._iAccountId);
		} else if (!oResponse.bSuccess) {
			// Error!
			Reflex_Popup.alert(oResponse.sMessage, {
				sTitle		: 'Database Error',
				fnOnClose	: this.hide.bind(this)
			});

		} else {
			//debugger;
			fnCallback(oResponse.oData);
		}
	},

	_submit	: function (oResponse) {
		//debugger;
		if (!oResponse || typeof oResponse.target !== 'undefined') {
			
			// Validate
			//----------------------------------------------------------------//
			var	aControls	= [];

			// Common Controls
			for (var sName in this.CONTROLS) {
				if (this.CONTROLS[sName] instanceof Control_Field) {
					aControls.push(this.CONTROLS[sName]);
				}
			}

			// Invoice Controls
			for (var iInvoiceId in this.CONTROLS.oInvoices) {
				aControls.push(this.CONTROLS.oInvoices[iInvoiceId].oPromisedBalance);
			}

			// Instalment Controls
			//debugger;
			var	aInstalmentControls	= $A(this.contentPane.select('.popup-account-promise-edit-instalments-instalment .control-field'));
			for (var i = 0, j = aInstalmentControls.length; i < j; i++) {
				aControls.push(aInstalmentControls[i].oControlField);
			}

			// Validate
			var	aValidationErrors	= [];
			for (i = 0, j = aControls.length; i < j; i++) {
				try {
					aControls[i].validate(false);
				} catch (mException) {
					aValidationErrors.push(String(mException));
				}
			}

			if (!aInstalmentControls.length) {
				aValidationErrors.push("There are no instalments defined");
			}
			
			// Show Validation Error Messages
			if (aValidationErrors.length) {
				// Errors found
				var	oValidationErrorsList	= $T.ol();

				for (i = 0, j = aValidationErrors.length; i < j; i++) {
					oValidationErrorsList.appendChild($T.li(aValidationErrors[i]));
				}

				Reflex_Popup.alert($T.div(
					$T.p('There following '+aValidationErrors.length+' error(s) were found with your data:'),
					oValidationErrorsList,
					$T.p('Please correct these errors and try again.')
				), {
					sTitle	: 'Validation Error',
					iWidth	: 30
				});

				return false;
			}

			// Build Dataset
			//----------------------------------------------------------------//
			var	oData	= {
				iAccountId					: this._iAccountId,
				bUseDirectDebit				: this.CONTROLS.oDirectDebitInstalments.getElementValue(),
				iCollectionPromiseReasonId	: parseInt(this.CONTROLS.oPromiseReason.getElementValue(), 10),
				aInvoices					: [],
				aInstalments				: []
			};
			
			for (iInvoiceId in this.CONTROLS.oInvoices) {
				oData.aInvoices.push({
					iInvoiceId		: iInvoiceId,
					fPromisedAmount	: parseFloat(this.CONTROLS.oInvoices[iInvoiceId].oPromisedBalance.getElementValue())
				});
			}
			
			var	aInstalments	= $A(this.contentPane.select('.popup-account-promise-edit-instalments-instalment'));
			for (var x = 0, y = aInstalments.length; x < y; x++) {
				oData.aInstalments.push({
					sDueDate	: aInstalments[x].select('.popup-account-promise-edit-instalments-instalment-duedate .control-field').first().oControlField.getElementValue(),
					fAmount		: parseFloat(aInstalments[x].select('.popup-account-promise-edit-instalments-instalment-amount .control-field').first().oControlField.getElementValue())
				});
			}

			//debugger;
			
			this._oLoadingPopup	= new Reflex_Popup.Loading('Saving...', true);
			
			// Perform AJAX Request
			jQuery.json.jsonFunction(
				this._submit.bind(this),
				this._submit.bind(this),
				'Collection_Promise',
				'save'
			)(oData);
			
			return true;
		} else if (!oResponse.bSuccess) {
			// Error!
			this._oLoadingPopup.hide();
			//debugger;
			Reflex_Popup.alert(oResponse.sMessage, {
				sTitle			: 'Database Error',
				fnOnClose		: this.hide.bind(this),
				sDebugContent	: oResponse.sDebug
			});
		} else {
			// Success!
			//debugger;
			this._oLoadingPopup.hide();
			Reflex_Popup.alert($T.p({'class':'alert-content'},'Promise to Pay was successfully saved.'), {
				sTitle			: 'Promise to Pay Saved',
				fnOnClose		: (function () {
					this.hide();
					this._oLoadingPopup	= new Reflex_Popup.Loading('Refreshing...', true);
					window.location.reload();
				}).bind(this),
				sDebugContent	: oResponse.sDebug
			});
		}
	},

	_getEarliestInvoiceDueDate	: function (bPromisedOnly) {
		bPromisedOnly	= !!bPromisedOnly;
		var	oEarliestDate,
			oInvoiceDueDate;

		for (var iInvoiceId in this._oData.outstanding_invoices) {
			oInvoiceDueDate	= new Date(this._oData.outstanding_invoices[iInvoiceId].due_date);
			if (!bPromisedOnly || parseFloat(this.CONTROLS.oInvoices[iInvoiceId].oPromisedBalance.getElementValue()) > 0.0) {
				if (!oEarliestDate || oEarliestDate.getTime() > oInvoiceDueDate.getTime()) {
					oEarliestDate	= oInvoiceDueDate;
				}
			}
		}

		return oEarliestDate ? oEarliestDate.truncate(Date.DATE_INTERVAL_DAY) : oEarliestDate;
	},

	_getTotalBalance	: function () {
		var	fTotalBalance	= 0.0;

		for (var iOutstandingInvoiceId in this._oData.outstanding_invoices) {
			fTotalBalance	+= this._oData.outstanding_invoices[iOutstandingInvoiceId].balance;
		}

		return fTotalBalance;
	},

	_getTotalPromised	: function () {
		var	fTotalPromised	= 0.0,
			fInvoicePromised;

		for (var iInvoiceId in this.CONTROLS.oInvoices) {
			fInvoicePromised	= parseFloat(this.CONTROLS.oInvoices[iInvoiceId].oPromisedBalance.getElementValue());
			if (fInvoicePromised) {
				fTotalPromised	+= fInvoicePromised;
			}
		}

		return fTotalPromised;
	},

	_updateTotalPromised	: function (bForceControlValue) {
		var	fTotalPromised	= this._getTotalPromised(),
			fTotalBalance	= this._getTotalBalance();

		if (bForceControlValue === true || !this.CONTROLS.oSpecifyTotalPromised.getElementValue()) {
			this.CONTROLS.oTotalPromised.setValue(fTotalPromised);
		}
		
		this.contentPane.select('.popup-account-promise-edit-invoices-totalpromised').first().innerHTML	= fTotalPromised.toFixed(2);
		this.contentPane.select('.popup-account-promise-edit-invoices-totalbalance').first().innerHTML	= (fTotalBalance - fTotalPromised.toFixed(2)).toFixed(2);

		this._updatePromisedAmountRemaining();
	},

	_updatePromisedAmountRemaining	: function () {
		var	fPromisedRemaining	= this._getTotalPromised(),
			aInstalments		= $A(this.contentPane.select('.popup-account-promise-edit-instalments-instalment-amount .control-field')),
			fInstalmentAmount;

		for (var i = 0, j = aInstalments.length; i < j; i++) {
			fInstalmentAmount	= parseFloat(aInstalments[i].oControlField.getElementValue());
			if (fInstalmentAmount) {
				fPromisedRemaining	-= fInstalmentAmount;
			}
		}
		
		this.contentPane.select('.popup-account-promise-edit-instalments-remaining').first().innerHTML	= fPromisedRemaining.toFixed(2);
	},

	_distributeBalancesOverInstalments	: function () {
		var	fBalanceRemaining	= this._getTotalPromised(),
			aInstalments		= $A(this.contentPane.select('.popup-account-promise-edit-instalments-instalment-amount .control-field')),
			fEvenDistribution	= (Math.ceil((fBalanceRemaining / aInstalments.length) * 100) / 100),
			fOldBalanceRemaining,
			fInstalmentAmount;
		
		for (var i = 0, j = aInstalments.length; i < j; i++) {
			fOldBalanceRemaining	= parseFloat(fBalanceRemaining.toFixed(2));
			fBalanceRemaining		-= (i == j-1) ? fOldBalanceRemaining : Math.max(0, fEvenDistribution);
			fInstalmentAmount		= (fOldBalanceRemaining - fBalanceRemaining).toFixed(2);

			aInstalments[i].oControlField.setValue(fInstalmentAmount);
		}
		
		this._updatePromisedAmountRemaining();
	},

	_distributeTotalPromisedOverInvoices	: function () {
		var	fPromisedRemaining	= parseFloat(this.CONTROLS.oTotalPromised.getElementValue()),
			fOldPromisedRemaining,
			fInvoiceAmount;

		for (var iInvoiceId in this.CONTROLS.oInvoices) {
			fOldPromisedRemaining	= parseFloat(fPromisedRemaining.toFixed(2));
			fPromisedRemaining		-= Math.max(0, Math.min(fOldPromisedRemaining, this._oData.outstanding_invoices[iInvoiceId].balance));
			fInvoiceAmount			= (fOldPromisedRemaining - fPromisedRemaining).toFixed(2);

			this.CONTROLS.oInvoices[iInvoiceId].oPromisedBalance.setValue(fInvoiceAmount);
		}

		this._updateTotalPromised();
	},

	_onSpecifyTotalPromisedChange	: function () {
		//debugger;
		var	oTotalPromisedContainer	= this.contentPane.select('.popup-account-promise-edit-instalments-details-totalpromised').first(),
			bSpecifyTotalPromised	= !!this.CONTROLS.oSpecifyTotalPromised.getElementValue();

		if (bSpecifyTotalPromised) {
			oTotalPromisedContainer.show();
			this.CONTROLS.oTotalPromised.setMandatory(true);
		} else {
			oTotalPromisedContainer.hide();
			this.CONTROLS.oTotalPromised.setMandatory(false);
		}

		for (var iInvoiceId in this.CONTROLS.oInvoices) {
			this.CONTROLS.oInvoices[iInvoiceId].oPromisedBalance.setRenderMode(!bSpecifyTotalPromised);
		}
	},

	_scheduleInstalments	: function (aInstalments) {
		if (!aInstalments || typeof aInstalments.target !== 'undefined') {
			new Popup_Account_Promise_Edit_Schedule({
				/* CONFIG */
				permissions			: this._oData.permissions,
				oEarliestDueDate	: this._getEarliestInvoiceDueDate(true) || this._getEarliestInvoiceDueDate(),
				fnCallback			: this._scheduleInstalments.bind(this)
			});
		} else {
			//debugger;
			// Remove existing Instalments
			var	aExistingInstalments	= $A(this.contentPane.select('.popup-account-promise-edit-instalments-instalment'));
			for (var i = 0, j = aExistingInstalments.length; i < j; i++) {
				aExistingInstalments[i].remove();
			}
			this.contentPane.select('.popup-account-promise-edit-instalments-empty').first().show();
			this._updatePromisedAmountRemaining();

			// Add Scheduled Instalments
			for (var x = 0, y = aInstalments.length; x < y; x++) {
				this._addInstalment(aInstalments[x]);
			}
		}
	},

	_addInstalment	: function (sDate) {
		var	oCurrentDate	= new Date(),
			oDueDate		= Control_Field.factory('date-picker', {
				sLabel			: 'Due Date',
				mEditable		: true,
				mMandatory		: true,
				sDateFormat		: 'Y-m-d',
				bTimePicker		: false,
				iYearStart		: oCurrentDate.getFullYear(),
				iYearEnd		: oCurrentDate.getFullYear() + 1,
				bValidateField	: true,
				fnValidate		: (function (oControlField) {
					// Check against other instalment dates
					var	aInstalments					= $A(this.contentPane.select('.popup-account-promise-edit-instalments-instalment-duedate .control-field')),
						oOwnDate						= (new Date(oControlField.getElementValue())).truncate(Date.DATE_INTERVAL_DAY),
						iOwnDateInstances				= 0,
						oEarliestPromisedInvoiceDueDate	= this._getEarliestInvoiceDueDate(true) || this._getEarliestInvoiceDueDate(),
						bFoundSelf						 = false,
						oInstalmentDueDate,
						sInstalmentDueDate,
						oPreviousInstalmentDueDate;
					
					if (oCurrentDate.getTime() >= oOwnDate.getTime()) {
						throw "Instalment is earlier than tomorrow";
					}
					
					for (var i = 0, j = aInstalments.length; i < j; i++) {
						sInstalmentDueDate	= aInstalments[i].oControlField.getElementValue();
						oInstalmentDueDate	= (new Date(sInstalmentDueDate)).truncate(Date.DATE_INTERVAL_DAY);
						
						// Check for copies of the same date
						if (oInstalmentDueDate.getTime() == oOwnDate.getTime()) {
							iOwnDateInstances++;
						}
						
						// We've found ourselves!
						if (aInstalments[i].oControlField === oControlField) {
							bFoundSelf	= true;
							// If we're the first, check against promise_start_delay_maximum_days permission
							if (i == 0 && (new Date(oCurrentDate)).shift(this._oData.permissions.promise_start_delay_maximum_days, Date.DATE_INTERVAL_DAY).getTime() < oOwnDate.getTime()) {
								throw "First instalment is later than "+this._oData.permissions.promise_start_delay_maximum_days+" day(s) after today";
							}
							
							// If we're the last, check against promise_maximum_days_between_due_and_end permission
							if (i == j-1 && oEarliestPromisedInvoiceDueDate && (new Date(oEarliestPromisedInvoiceDueDate)).shift(this._oData.permissions.promise_maximum_days_between_due_and_end, Date.DATE_INTERVAL_DAY).getTime() < oOwnDate.getTime()) {
								throw "Final instalment is later than "+this._oData.permissions.promise_maximum_days_between_due_and_end+" day(s) after the earliest promised due invoice";
							}
							
							// If we're not first, check against previous instalment date
							if (i > 0 && oPreviousInstalmentDueDate) {
								if ((oPreviousInstalmentDueDate.getTime() - oOwnDate.getTime()) / Date.MILLISECONDS_IN_DAY > this._oData.permissions.promise_instalment_maximum_interval_days) {
									throw "Instalment is more than "+this._oData.permissions.promise_instalment_maximum_interval_days+" days after the previous instalment";
								}
							}
						} else if (sInstalmentDueDate && !isNaN(oInstalmentDueDate.valueOf())) {
							// Check against other instalment dates
							if (!bFoundSelf && oInstalmentDueDate.getTime() > oOwnDate.getTime()) {
								// We're listed after a later instalment
								aInstalments[i].oControlField.validate();
							} else if (bFoundSelf && oInstalmentDueDate.getTime() < oOwnDate.getTime()) {
								// We're listed before an earlier instalment
								throw "Instalment is out of order";
							} else if (oInstalmentDueDate.getTime() == oOwnDate.getTime()) {
								// We share the same Date
								if (bFoundSelf) {
									// We're listed before the other instalment
									aInstalments[i].oControlField.validate();
								} else {
									// We're listed after the other instalment
									throw "There is already an instalment scheduled for " + oOwnDate.$format('d/m/Y');;
								}
							}
						}
						
						oPreviousInstalmentDueDate	= (sInstalmentDueDate && !isNaN(oInstalmentDueDate.valueOf())) ? oInstalmentDueDate : null;
					}
					
					if (iOwnDateInstances > 1) {
						throw "There are "+iOwnDateInstances+" instalments with the date " + oOwnDate.$format('d/m/Y');
					}
					
					return true;
				}).bind(this)
			}),
			oAmount			= Control_Field.factory('number', {
				sLabel			: 'Amount',
				mEditable		: true,
				mMandatory		: true,
				fMinimumValue	: 0.01,
				fMaximumValue	: Math.max(0.01, this._oData.permissions.promise_amount_maximum),
				bValidateField	: true,
				fnValidate		: (function (oControlField) {
					// Check that our value is greater than the minimum percentage of promised value allowed
					var	fTotalPromised	= this._getTotalPromised(),
						fMinimumValue	= fTotalPromised * this._oData.permissions.promise_instalment_minimum_promised_percentage,
						fPercentage		= this._oData.permissions.promise_instalment_minimum_promised_percentage * 100;
					if (oControlField !== this.contentPane.select('.popup-account-promise-edit-instalments-instalment-amount .control-field').last().oControlField
						&& parseFloat(oControlField.getElementValue()) < fMinimumValue) {
						throw "Instalment is worth less than "+fPercentage+"% of $"+fTotalPromised.toFixed(2);
					}
					return true;
				}).bind(this)
			}),
			oDeleteButton	= $T.img({src:'../admin/img/template/remove.png','class':'popup-account-promise-edit-instalments-instalment-delete'}),
			oTR				= $T.tr({'class':'popup-account-promise-edit-instalments-instalment'},
				$T.td({'class':'popup-account-promise-edit-instalments-instalment-duedate'},
					oDueDate.getElement()
				),
				$T.td({'class':'popup-account-promise-edit-instalments-instalment-amount'},
					'$',
					oAmount.getElement()
				),
				$T.td(oDeleteButton)
			),
			oTBody			= this.contentPane.select('.popup-account-promise-edit-instalments > tbody').first(),
			oEmptyTR		= oTBody.select('.popup-account-promise-edit-instalments-empty').first();

		// onDelete
		oDeleteButton.observe('click', (function () {
			if (oTR.parentNode.childElements().length <= 2) {
				oEmptyTR.show();
			}
			oTR.remove();
			this._updatePromisedAmountRemaining();
		}).bind(this));

		// Amount.onChange
		oAmount.addOnChangeCallback(this._updatePromisedAmountRemaining.bind(this));

		// Render Modes
		oDueDate.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oAmount.setRenderMode(Control_Field.RENDER_MODE_EDIT);

		// Initial State
		var	iDate	= (new Date(sDate)).getTime();
		if (iDate || iDate === iDate) {
			oDueDate.setValue(sDate);
		}

		// Update DOM
		oEmptyTR.hide();
		oTBody.appendChild(oTR);
	},

	_buildUI	: function (oData, bReplaceExistingPromise, bReplaceExistingSuspension) {
		//debugger;
		if (typeof oData === 'undefined') {
			// Need to retrieve the data
			this._getData(this._buildUI.bind(this));
		} else if (oData.existing_promise && bReplaceExistingPromise !== true) {
			var	sExistingPromiseSummary	= 'This Account already has an active Promise to Pay arrangement scheduled to end on '+(new Date(oData.existing_promise.aInstalments.last().due_date)).$format('j M Y')+'.';
			// We have an existing Promise
			if (oData.permissions.promise_can_replace) {
				// We are allowed to replace Promises
				return new Reflex_Popup.yesNoCancel(
					$T.div(
						$T.p(sExistingPromiseSummary),
						$T.p("Would you like to replace the existing Promise to Pay with a new arrangement?")
					), {
						sTitle	: 'Existing Promise to Pay',
						iWidth	: 40,
						fnOnYes	: this._buildUI.bind(this, oData, true, !!bReplaceExistingSuspension),
						fnOnNo	: this.hide.bind(this)
					}
				);
			} else {
				// We are not allowed to replace Promises
				return new Reflex_Popup.alert(
					$T.div({'class':'alert-content'},
						$T.p(sExistingPromiseSummary),
						$T.p('You do not have the ability to replace this Promise to Pay.  If the Promise needs to be replaced, please escalate this to your Manager.')
					), {
						sTitle		: 'Existing Promise to Pay',
						iWidth		: 40,
						fnOnClose	: this.hide.bind(this)
					}
				);
			}
		} else if (oData.existing_promise && bReplaceExistingSuspension !== true) {
			var	sExistingSuspensionSummary	= 'This Account already has an active Promise to Pay arrangement scheduled to end on '+(new Date(oData.existing_promise.aInstalments.last().due_date)).$format('j M Y')+'.';
			// We have an existing Suspension
			if (oData.permissions.suspension_can_replace) {
				// We are allowed to replace Suspensions
				return new Reflex_Popup.yesNoCancel(
					$T.div(
						$T.p(sExistingSuspensionSummary),
						$T.p("Would you like to replace the existing Suspension with a Promise to Pay arrangement?")
					), {
						sTitle	: 'Existing Suspension',
						iWidth	: 40,
						fnOnYes	: this._buildUI.bind(this, oData, !!bReplaceExistingPromise, true),
						fnOnNo	: this.hide.bind(this)
					}
				);
			} else {
				// We are not allowed to replace Suspensions
				return new Reflex_Popup.alert(
					$T.div({'class':'alert-content'},
						$T.p(sExistingSuspensionSummary),
						$T.p('You do not have the ability to replace this Suspension.  If you need to replace this Suspension with a Promise to Pay arrangement, please escalate this to your Manager.')
					), {
						sTitle		: 'Existing Suspension',
						iWidth		: 40,
						fnOnClose	: this.hide.bind(this)
					}
				);
			}
		} else {
			//debugger;
			// Build our UI!
			this._oData	= oData;

			var	oDirectDebitInstalments	= Control_Field.factory('checkbox', {
											sLabel			: 'Direct Debit Instalments',
											mEditable		: !!(oData.payment_method_system === 'DIRECT_DEBIT'),
											mMandatory		: true
										}),
				oPromiseReason			= Control_Field.factory('select', {
											sLabel		: 'Reason for Promise',
											fnPopulate	: function (fnCallback) {
												var	aPromiseReasons	= [];
												for (var iCollectionPromiseReasonId in oData.collection_promise_reason) {
													if (oData.collection_promise_reason.hasOwnProperty(iCollectionPromiseReasonId)) {
														aPromiseReasons.push($T.option({'value':iCollectionPromiseReasonId},
															oData.collection_promise_reason[iCollectionPromiseReasonId].name
														));
													}
												}
												fnCallback(aPromiseReasons);
											},
											mVisible	: true,
											mEditable	: true,
											mMandatory	: true
										}),
				oSpecifyTotalPromised	= Control_Field.factory('checkbox', {
											sLabel			: 'Specify Total Promise',
											mEditable		: true,
											mMandatory		: true,
											mValue			: false
										}),
				oTotalPromised			= Control_Field.factory('number', {
											sLabel			: 'Total Promised',
											mEditable		: true,
											fMinimumValue	: 0.01,
											fMaximumValue	: this._getTotalBalance().toFixed(2),
											iDecimalPlaces	: 2,
											mMandatory		: function () {
												return !!oSpecifyTotalPromised.getElementValue();
											}
										}),
				oInvoices				= $T.table({'class':'reflex popup-account-promise-edit-invoices'},
											$T.caption(
												$T.div({'class':'caption_bar'},
													$T.div({'class':'caption_title'},
														$T.img({src:'../admin/img/template/invoice.png','class':'icon'}),
														$T.span('Outstanding Invoices')
													)
												)
											),
											$T.thead(
												$T.tr(
													$T.th('Invoice Date'),
													$T.th('Due Date'),
													$T.th('New Charges'),
													$T.th('Remaining Balance'),
													$T.th('Promised')
												)
											),
											$T.tbody({'class':'popup-account-promise-edit-invoices-detail alternating'}),
											$T.tfoot(
												$T.tr(
													$T.th({colspan:4},
														'Total Promised:'
													),
													$T.th(
														'$',
														$T.span({'class':'popup-account-promise-edit-invoices-totalpromised'})
													)
												),
												$T.tr(
													$T.th({colspan:4},
														'Total Balance Remaining:'
													),
													$T.th(
														'$',
														$T.span({'class':'popup-account-promise-edit-invoices-totalbalance'})
													)
												)
											)
										),
				oInstalments			= $T.table({'class':'reflex popup-account-promise-edit-instalments'},
											$T.caption(
												$T.div({'class':'caption_bar'},
													$T.div({'class':'caption_title'},
														$T.img({src:'../admin/img/template/date.png','class':'icon'}),
														$T.span('Instalments')
													),
													$T.div({'class':'caption_options'},
														$T.button({'type':'button','class':'popup-account-promise-edit-instalments-distribute'},
															$T.img({'src':'../admin/img/template/sum.png','class':'icon'}),
															'Distribute'
														),
														$T.button({'type':'button','class':'popup-account-promise-edit-instalments-schedule'},
															$T.img({'src':'../admin/img/template/clock.png','class':'icon'}),
															'Recurring Schedule'
														),
														$T.button({'type':'button','class':'popup-account-promise-edit-instalments-add'},
															$T.img({'src':'../admin/img/template/new.png','class':'icon'}),
															'Add Instalment'
														)
													)
												)
											),
											$T.thead(
												$T.tr(
													$T.th('Due Date'),
													$T.th('Amount Due'),
													$T.th()
												)
											),
											$T.tbody({'class':'alternating'},
												$T.tr({'class':'popup-account-promise-edit-instalments-empty'},
													$T.td({colspan:3},
														'There are no instalments defined'
													)
												)
											),
											$T.tfoot(
												$T.tr(
													$T.th(
														'Promised Amount Remaining:'
													),
													$T.th(
														'$',
														$T.span({'class':'popup-account-promise-edit-instalments-remaining'})
													),
													$T.th()
												)
											)
										),
				oDOM					= $T.div({'class':'popup-account-promise-edit'},
											$T.form({action:'#', method:'ajax'},
												$T.table({'class':'popup-account-promise-edit-details reflex'},
													$T.caption(
														$T.div({'class':'caption_bar'},
															$T.div({'class':'caption_title'},
																$T.img({src:'../admin/img/template/payment.png','class':'icon'}),
																$T.span('Promise Details')
															)
														)
													),
													$T.tbody(
														$T.tr(
															$T.th({'class':'label'}, 'Account'),
															$T.td({'class':'input'},
																$T.div({'class':'popup-account-promise-edit-details-account'},
																	$T.a({href:'../admin/flex.php/Account/Overview/?Account.Id='+this._iAccountId.toString()},
																		this._iAccountId.toString() + ': ' + oData.account_name
																	)
																),
																$T.div({'class':'popup-account-promise-edit-details-customergroup'}, oData.customer_group_name)
															)
														),
														$T.tr(
															$T.th({'class':'label'}, 'Reason for Promise'),
															$T.td({'class':'input'}, oPromiseReason.getElement())
														),
														$T.tr(
															$T.th({'class':'label'}, 'Direct Debit Instalments'),
															$T.td({'class':'input'},
																(oData.payment_method_system === 'DIRECT_DEBIT') ? oDirectDebitInstalments.getElement() : $T.span('Instalments must be paid manually')
															)
														),
														$T.tr(
															$T.th({'class':'label'}, 'Total Promised'),
															$T.td({'class':'input'},
																$T.label(
																	oSpecifyTotalPromised.getElement(),
																	$T.span('Specify a Total Promise')
																),
																$T.label({'class':'popup-account-promise-edit-instalments-details-totalpromised'},
																	$T.span('$'),
																	oTotalPromised.getElement()
																)
															)
														)
													)
												),
												oInvoices,
												oInstalments
											)
										);

			// Controls
			this.CONTROLS	= {
				oDirectDebitInstalments	: oDirectDebitInstalments,
				oPromiseReason			: oPromiseReason,
				oSpecifyTotalPromised	: oSpecifyTotalPromised,
				oTotalPromised			: oTotalPromised,
				oInvoices				: {}
			};

			// Invoices
			var	oInvoicesDetails	= oInvoices.select('tbody').first();
			if (!Object.keys(oData.outstanding_invoices).length) {
				// No Invoices
				oInvoicesDetails.appendChild($T.tr(
					$T.td({colspan:5,'class':'popup-account-promise-edit-invoices-empty'},
						'No Invoices to Promise'
					)
				));
			} else {
				// Plenty of Invoices!
				for (var iInvoiceId in oData.outstanding_invoices) {

					var	oPromisedBalance	= Control_Field.factory('number', {
							sLabel		: 'Promised Value',
							fMinimumValue	: 0,
							fMaximumValue	: oData.outstanding_invoices[iInvoiceId].balance.toFixed(2),
							iDecimalPlaces	: 2,
							mEditable	: true,
							mMandatory	: false
						});
					oPromisedBalance.addOnChangeCallback(this._updateTotalPromised.bind(this, oPromisedBalance));
					oPromisedBalance.setRenderMode(Control_Field.RENDER_MODE_EDIT);
					oInvoicesDetails.appendChild(
						$T.tr(
							$T.td((new Date(oData.outstanding_invoices[iInvoiceId].invoice_date)).$format('j M Y')),
							$T.td((new Date(oData.outstanding_invoices[iInvoiceId].due_date)).$format('j M Y')),
							$T.td('$' + oData.outstanding_invoices[iInvoiceId].grand_total.toFixed(2)),
							$T.td('$',
								$T.span(oData.outstanding_invoices[iInvoiceId].balance.toFixed(2))
							),
							$T.td({'class':'popup-account-promise-edit-invoices-promisedvalue'},
								'$',
								oPromisedBalance.getElement()
							)
						)
					);

					this.CONTROLS.oInvoices[iInvoiceId]	= {
						oPromisedBalance	: oPromisedBalance
					};
				}
			}

			// Buttons
			oDOM.select('.popup-account-promise-edit-instalments-add').first().observe('click', this._addInstalment.bind(this));
			oDOM.select('.popup-account-promise-edit-instalments-distribute').first().observe('click', this._distributeBalancesOverInstalments.bind(this));
			oDOM.select('.popup-account-promise-edit-instalments-schedule').first().observe('click', this._scheduleInstalments.bind(this));

			this.setFooterButtons([
				$T.button({type:'button','class':'popup-account-promise-edit-submit'},
					$T.img({src:'../admin/img/template/tick.png','class':'icon'}),
					$T.span('Create Promise')
				),
				$T.button({type:'button','class':'popup-account-promise-edit-close'},
					$T.img({src:'../admin/img/template/delete.png','class':'icon'}),
					$T.span('Cancel')
				)]
			, true);

			this.footerPane.select('.popup-account-promise-edit-submit').first().observe('click', this._submit.bind(this));
			this.footerPane.select('.popup-account-promise-edit-close').first().observe('click', this.hide.bind(this));

			// Control Field Event Observers
			oSpecifyTotalPromised.addOnChangeCallback(this._onSpecifyTotalPromisedChange.bind(this));
			oTotalPromised.addOnChangeCallback(this._distributeTotalPromisedOverInvoices.bind(this));

			// Control Field Render Modes
			if (oData.payment_method_system === 'DIRECT_DEBIT') {
				oDirectDebitInstalments.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			}
			oPromiseReason.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oSpecifyTotalPromised.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oTotalPromised.setRenderMode(Control_Field.RENDER_MODE_EDIT);

			// Set Content
			this.setContent(oDOM);

			// Set initial state
			this._updateTotalPromised();
			this._onSpecifyTotalPromisedChange();

			// Display the Popup
			this.display();
		}

	}

});

Popup_Account_Promise_Edit.DUMMY_DATA	= {
	"account_id"			: this._iAccountId,
	"account_name"			: 'ACCOUNT NAME',
	"customer_group_id"		: 1,
	"customer_group_name"	: 'CUSTOMER GROUP',
	"payment_method_id"		: 2,
	"payment_method_system"	: "DIRECT_DEBIT",
	"existing"				: null,
	"permissions"			: {
		"promise_start_delay_maximum_days "					: 7,
		"promise_maximum_days_between_due_and_end "			: 60,
		"promise_instalment_maximum_interval_days "			: 14,
		"promise_instalment_minimum_promised_percentage "	: 0.15,
		"promise_can_replace"								: true,
		"promise_create_maximum_severity_level"				: 100,
		"promise_amount_maximum"							: 10000.00
	},
	"collection_promise_reason"	: {
		"1"	: {
			"id"			: 1,
			"name"			: "Promise to Pay",
			"description"	: "Promise to Pay",
			"status_id"		: 1
		},
		"2"	: {
			"id"			: 2,
			"name"			: "TIO Resolution",
			"description"	: "TIO Resolution",
			"status_id"		: 1
		},
		"3"	: {
			"id"			: 3,
			"name"			: "Bill Shock",
			"description"	: "Bill Shock",
			"status_id"		: 1
		}
	},
	"outstanding_invoices"	: {
		"3000135684"	: {
			"id"						: 3000135684,
			"invoice_date"				: "2011-01-04",
			"due_date"					: "2011-01-18",
			"grand_total"				: 322.12,
			"balance"					: 102.12,
			"invoice_run_type_id"		: 6,
			"invoice_run_type_name"		: "Interim First Invoice",
			"invoice_run_type_constant"	: "INVOICE_RUN_TYPE_INTERIM_FIRST",
			"invoice_run_id"			: 3584
		},
		"3000159781"	: {
			"id"						: 3000159781,
			"invoice_date"				: "2011-01-11",
			"due_date"					: "2011-01-25",
			"grand_total"				: 1592.55,
			"balance"					: 1592.55,
			"invoice_run_type_id"		: 1,
			"invoice_run_type_name"		: "Live",
			"invoice_run_type_constant"	: "INVOICE_RUN_TYPE_LIVE",
			"invoice_run_id"			: 3611
		}
	}
};
