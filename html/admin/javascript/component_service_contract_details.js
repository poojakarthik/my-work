
Component_Service_Contract_Details	= Class.create(/* extends */Reflex_Component, {

	initialize	: function ($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'iServiceRatePlanId'	: {}
		}, this.CONFIG || {});

		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));

		this.NODE.addClassName('component-service-contract-details');
	},

	_load	: function (oResponse) {
		//debugger;
		if (!oResponse || oResponse.element) {
			// No Response (or Response is an Event): Request Data
			(new Reflex_AJAX_Request('Service_Contract', 'getDetails', this._load.bind(this))).send(this.get('iServiceRatePlanId'));
		} else if (oResponse.hasException()) {
			// Error
			Reflex_Popup.alert(oResponse.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Data Error',
				sDebugContent	: oResponse.getDebugLog()
			});
		} else {
			// Success
			this._oData	= oResponse.getData();
			this._syncUI();
		}
	},

	_buildUI	: function () {
		// Core Node
		this.NODE	= $T.div(
			new Component_Section({sIcon:'../admin/img/template/account.png',sTitle:'Customer'},
				$T.dl({'class':'reflex-propertylist component-service-contract-details-customer'},
					$T.dt({title:'Account'}, 'Account'),
					$T.dd(
						$T.p({'class':'component-service-contract-details-customer-account'},
							$T.span({'class':'component-service-contract-details-customer-account-id'},'[ACCOUNT_ID]'),
							$T.span(': '),
							$T.span({'class':'component-service-contract-details-customer-account-name'},'[ACCOUNT_NAME]')
						),
						$T.p({'class':'component-service-contract-details-customer-customergroup'}, '[CUSTOMER_GROUP]')
					),

					$T.dt({title:'Service'}, 'Service'),
					$T.dd({'class':'component-service-contract-details-customer-service'},
						$T.img({'class':'icon component-service-contract-details-customer-service-servicetype',alt:'[SERVICE_TYPE]',width:16,height:16,title:'[SERVICE_TYPE]'}),
						$T.span({'class':'component-service-contract-details-customer-service-identifier', title:'[SERVICE_ID]'}, '[SERVICE_IDENTIFIER]')
					)
				)
			),
			new Component_Section({sIcon:'../admin/img/template/contract.png',sTitle:'Contract'},
				$T.dl({'class':'reflex-propertylist component-service-contract-details-contract'},
					$T.dt({title:'Rate Plan'}, 'Rate Plan'),
					$T.dd({'class':'component-service-contract-details-contract-rateplan'},
						$T.p({'class':'component-service-contract-details-contract-rateplan-name'}, 'RATE_PLAN_NAME'),
						$T.p({'class':'component-service-contract-details-contract-rateplan-charge'},
							$T.span('$'),
							$T.span({'class':'component-service-contract-details-contract-rateplan-charge-value'}, '[RATE_PLAN_CHARGE]'),
							$T.span('/month')
						)
					),

					$T.dt({title:'Starting Date'}, 'Starting Date'),
					$T.dd({'class':'component-service-contract-details-contract-startingdate'}, '[START_DATE]'),

					$T.dt({title:'Ending Date'}, 'Ending Date'),
					$T.dd({'class':'component-service-contract-details-contract-endingdate'}, '[END_DATE]'),

					$T.dt({title:'Contract Status'}, 'Contract Status'),
					$T.dd({'class':'component-service-contract-details-contract-status'},
						$T.p({'class':'component-service-contract-details-contract-status-description'}),
						$T.p({'class':'component-service-contract-details-contract-status-expiry'},
							$T.span('('),
							$T.span({'class':'component-service-contract-details-contract-status-expiry-term'}, '[EXPIRY_TERM]'),
							$T.span(' on '),
							$T.time({'class':'component-service-contract-details-contract-status-expiry-date'}, '[EXPIRY_DATE]'),
							$T.span(')')
						)
					),

					$T.dt({title:'Contract Term'}, 'Contract Term'),
					$T.dd({'class':'component-service-contract-details-contract-term'},
						$T.span(
							$T.span({'class':'component-service-contract-details-contract-term-value'}, '[CONTRACT_TERM]'),
							$T.span(' months')
						),
						$T.span({'class':'component-service-contract-details-contract-term-remaining'},
							' (',
							$T.span({'class':'component-service-contract-details-contract-term-remaining-value'}, '[CONTRACT_MONTHS_REMAINING]'),
							' months remaining)'
						)
					),

					$T.dt({title:'Contract Value'}, 'Contract Value'),
					$T.dd({'class':'component-service-contract-details-contract-value'},
						$T.span({'class':'component-service-contract-details-contract-value-total'},
							'$',
							$T.span({'class':'component-service-contract-details-contract-value-total-value'},'[CONTRACT_VALUE_TOTAL]')
						),
						$T.span({'class':'component-service-contract-details-contract-value-remaining'},
							' ($',
							$T.span({'class':'component-service-contract-details-contract-value-remaining-value'}, '[CONTRACT_VALUE_REMAINING]'),
							' remaining)'
						)
					)
				)
			),
			new Component_Section({sIcon:'../admin/img/template/charge_small.png',sTitle:'Penalties'},
				$T.dl({'class':'reflex-propertylist component-service-contract-details-penalties'},
					$T.dt({title:'Penalty Status'}, 'Penalty Status'),
					$T.dd({'class':'component-service-contract-details-penalties-status'},
						$T.p({'class':'component-service-contract-details-penalties-status-value'}, '[PENALTY_STATUS]'),	// e.g. Not Applicable, Pending Approval, Applied, Waived
						$T.p({'class':'component-service-contract-details-penalties-status-audit'},
							$T.span('by '),
							$T.span({'class':'component-service-contract-details-penalties-status-employee'}, '[EMPLOYEE]'),
							$T.span(' on '),
							$T.time({'class':'component-service-contract-details-penalties-status-date'}, '[DATE]')
						)
					),

					$T.dt({title:'Contract Payout'}, 'Contract Payout'),
					$T.dd({'class':'component-service-contract-details-penalties-payout'},
						$T.dl({'class':'reflex-propertylist'},
							$T.dt({title:'Percentage of Remaining'}, '% of Remaining'),
							$T.dd({'class':'component-service-contract-details-penalties-payout-percentage'},
								$T.span({'class':'component-service-contract-details-penalties-payout-percentage-value'}, '[PERCENTAGE]'),
								'%'
							),

							$T.dt({title:'Recommended Payout'}, 'Recommended Payout'),
							$T.dd({'class':'component-service-contract-details-penalties-payout-recommended'},
								'$',
								$T.span({'class':'component-service-contract-details-penalties-payout-recommended-value'}, '[RECOMMENDED_VALUE]')
							),

							$T.dt({title:'Outcome'}, 'Outcome'),
							$T.dd({'class':'component-service-contract-details-penalties-payout-effective'},
								$T.span({'class':'component-service-contract-details-penalties-payout-effective-action'}, '[ACTION]'),
								$T.span({'class':'component-service-contract-details-penalties-payout-effective-details'},
									$T.span(' $'),
									$T.span({'class':'component-service-contract-details-penalties-payout-effective-details-payout'}, '[PAYOUT]'),
									$T.span(' ('),
									$T.span({'class':'component-service-contract-details-penalties-payout-effective-details-percentage'}, '[PERCENTAGE]'),
									$T.span('%)')
								)
							)
						)
					),

					$T.dt({title:'Early Exit Fee'}, 'Early Exit Fee'),
					$T.dd({'class':'component-service-contract-details-penalties-earlyexit'},
						$T.dl({'class':'reflex-propertylist'},
							$T.dt({title:'Recommended Fee'}, 'Recommended Fee'),
							$T.dd({'class':'component-service-contract-details-penalties-earlyexit-recommended'},
								'$',
								$T.span({'class':'component-service-contract-details-penalties-earlyexit-recommended-value'}, '[RECOMMENDED_VALUE]')
							),

							$T.dt({title:'Outcome'}, 'Outcome'),
							$T.dd({'class':'component-service-contract-details-penalties-earlyexit-effective'},
								$T.span({'class':'component-service-contract-details-penalties-earlyexit-effective-action'}, '[ACTION]'),
								$T.span({'class':'component-service-contract-details-penalties-earlyexit-effective-details'},
									$T.span(' $'),
									$T.span({'class':'component-service-contract-details-penalties-earlyexit-effective-details-amount'}, '[AMOUNT]')
								)
							)
						)
					)
				)
			)
		);

		// Attachment Nodes
		//oComponentSection.getAttachmentNode('header-actions').appendChild(oContactsButton);
		//oComponentSection.getAttachmentNode('header-actions').appendChild(oNewAccountButton);
	},

	_syncUI	: function () {
		//this.NODE.select('.component-section').first().oReflexComponent.set('sTitle', "Accounts Linked to " + this.get('iAccountId'));
		//this._onReady();return;

		if (!this._oData) {
			// Need to load additional data first
			//----------------------------------------------------------------//
			this._load();
		} else {
			// Fill in the gaps
			//----------------------------------------------------------------//
			// Customer: Account
			this.$$('.component-service-contract-details-customer-account-id')[0].innerHTML		= this._oData.account.id;
			this.$$('.component-service-contract-details-customer-account-name')[0].innerHTML	= this._oData.account.account_name;

			var oCustomerGroup	= this.$$('.component-service-contract-details-customer-customergroup')[0];
			oCustomerGroup.innerHTML	= this._oData.account.customer_group.internal_name;
			if (this._oData.account.customer_group.primary_colour) {
				oCustomerGroup.setStyle({color:'#'+this._oData.account.customer_group.primary_colour});
			}

			// Customer: Service
			var	oServiceIdentifier	= this.$$('.component-service-contract-details-customer-service-identifier')[0];
			oServiceIdentifier.innerHTML	= this._oData.service.service_identifier;
			oServiceIdentifier.setAttribute('title', 'Service ID: '+this._oData.service.id);
			
			var	oServiceType	= this.$$('.component-service-contract-details-customer-service-servicetype')[0];
			oServiceType.setAttribute('src', '../admin/img/template/phonetypes/' + (Component_Service_Contract_Details.PHONE_TYPES[this._oData.service.service_type.constant] || Component_Service_Contract_Details.PHONE_TYPES['__DEFAULT__'])+'.png');
			oServiceType.setAttribute('alt', this._oData.service.service_type.name);
			oServiceType.setAttribute('title', this._oData.service.service_type.name);

			// Contract: Rate Plan
			this.$$('.component-service-contract-details-contract-rateplan-name')[0].innerHTML			= this._oData.rate_plan.name.escapeHTML();
			this.$$('.component-service-contract-details-contract-rateplan-charge-value')[0].innerHTML	= parseFloat(this._oData.rate_plan.minimum_charge).toFixed(2).escapeHTML();
			
			// Contract: Starting Date
			var	oStartingDate	= this.$$('.component-service-contract-details-contract-startingdate')[0];
			oStartingDate.innerHTML	= Date.$parseDate(this._oData.contract_start_datetime, 'Y-m-d H:i:s').$format('j M Y');
			oStartingDate.setAttribute('datetime', Date.$parseDate(this._oData.contract_start_datetime, 'Y-m-d H:i:s').$format('Y-m-d\\TH:i:s') + '+10:00');

			// Contract: Ending Date
			var	oEndingDate	= this.$$('.component-service-contract-details-contract-endingdate')[0];
			oEndingDate.innerHTML		= (this._oData.contract_end_datetime === '9999-12-31 23:59:59') ? 'Indefinite' : Date.$parseDate(this._oData.contract_end_datetime, 'Y-m-d H:i:s').$format('j M Y');
			oEndingDate.setAttribute('datetime', Date.$parseDate(this._oData.contract_end_datetime, 'Y-m-d H:i:s').$format('Y-m-d\\TH:i:s') + '+10:00');
			
			// Contract: Status
			var	oStatus				= this.$$('.component-service-contract-details-contract-status')[0],
				oStatusDescription	= this.$$('.component-service-contract-details-contract-status-description')[0],
				oExpiryTerm			= this.$$('.component-service-contract-details-contract-status-expiry-term')[0],
				oExpiryDate			= this.$$('.component-service-contract-details-contract-status-expiry-date')[0];
			oStatusDescription.innerHTML	= this._oData.contract_status.name;
			oStatus.setAttribute('data-status', this._oData.contract_status.constant);
			switch (this._oData.contract_status.constant) {
				case 'CONTRACT_STATUS_BREACHED':
					// Breached
					oExpiryTerm.innerHTML	= this._oData.contract_breach_reason_description;
					oExpiryDate.innerHTML	= Date.$parseDate(this._oData.contract_effective_end_datetime, 'Y-m-d H:i:s').$format('j M Y');
					oExpiryDate.setAttribute('datetime', Date.$parseDate(this._oData.contract_effective_end_datetime, 'Y-m-d H:i:s').$format('Y-m-d\\TH:i:s') + '+10:00');
					break;
				case 'CONTRACT_STATUS_EXPIRED':
					// Expired
					oExpiryTerm.innerHTML	= 'Expired';
					oExpiryDate.innerHTML	= Date.$parseDate(this._oData.contract_effective_end_datetime, 'Y-m-d H:i:s').$format('j M Y');
					oExpiryDate.setAttribute('datetime', Date.$parseDate(this._oData.contract_effective_end_datetime, 'Y-m-d H:i:s').$format('Y-m-d\\TH:i:s') + '+10:00');
					break;
				case 'CONTRACT_STATUS_ACTIVE':
					// Active
					oExpiryTerm.innerHTML	= 'Expires';
					oExpiryDate.innerHTML	= Date.$parseDate(this._oData.contract_scheduled_end_datetime, 'Y-m-d H:i:s').$format('j M Y');
					oExpiryDate.setAttribute('datetime', Date.$parseDate(this._oData.contract_scheduled_end_datetime, 'Y-m-d H:i:s').$format('Y-m-d\\TH:i:s') + '+10:00');
					break;
				default:
					throw "Unhandled Contract Status "+this._oData.contract_status.id+': '+this._oData.contract_status.name;
					break;
			}

			// Contract: Term
			this.$$('.component-service-contract-details-contract-term-value')[0].innerHTML				= (this._oData.rate_plan.contract_term || 0);
			this.$$('.component-service-contract-details-contract-term-remaining-value')[0].innerHTML	= this._oData.contract_term_remaining;

			// Contract: Value
			this.$$('.component-service-contract-details-contract-value-total-value')[0].innerHTML		= ((this._oData.rate_plan.contract_term || 0) * parseFloat(this._oData.rate_plan.minimum_charge)).toFixed(2).escapeHTML();
			this.$$('.component-service-contract-details-contract-value-remaining-value')[0].innerHTML	= ((this._oData.contract_term_remaining || 0) * parseFloat(this._oData.rate_plan.minimum_charge)).toFixed(2).escapeHTML();

			// Penalties: Status
			var	oPenaltiesStatus		= this.$$('.component-service-contract-details-penalties-status')[0],
				oPenaltiesStatusValue	= this.$$('.component-service-contract-details-penalties-status-value')[0],
				oPenaltiesStatusDate	= this.$$('.component-service-contract-details-penalties-status-date')[0]
				bPenaltiesReviewed		= false;
			if (!this._oData.rate_plan.contract_term) {
				// No Contract
				oPenaltiesStatusValue.innerHTML	= 'No Contract';
				oPenaltiesStatus.setAttribute('data-status', 'no-contract');
			} else if (this._oData.contract_status.constant === 'CONTRACT_STATUS_ACTIVE') {
				// Contract Active
				oPenaltiesStatusValue.innerHTML	= 'Contract Active';
				oPenaltiesStatus.setAttribute('data-status', 'contract-active');
			} else if (this._oData.contract_status.constant === 'CONTRACT_STATUS_EXPIRED') {
				// Complete
				oPenaltiesStatusValue.innerHTML	= 'Contract Complete: No Penalties Apply';
				oPenaltiesStatus.setAttribute('data-status', 'expired');
			} else if (!this._oData.contract_breach_fees_datetime && !this._oData.contract_breach_fees_reason) {
				// Pending Review
				oPenaltiesStatusValue.innerHTML	= 'Contract Breached: Pending Review';
				oPenaltiesStatus.setAttribute('data-status', 'pending-review');
			} else {
				// Penalties Applied/Waived
				bPenaltiesReviewed	= true;
				
				var	sAction	= (this._oData.contract_payout_charge_id || this._oData.exit_fee_charge_id) ? 'Applied' : 'Waived';
				oPenaltiesStatusValue.innerHTML	= 'Contract Breached: Penalties ' + sAction;
				
				this.$$('.component-service-contract-details-penalties-status-employee')[0].innerHTML	= (this._oData.contract_breach_fees_employee.first_name + ' ' + this._oData.contract_breach_fees_employee.last_name).escapeHTML();
				oPenaltiesStatusDate.innerHTML	= Date.$parseDate(this._oData.contract_breach_fees_datetime, 'Y-m-d H:i:s').$format('j M Y');
				oPenaltiesStatusDate.setAttribute('datetime', Date.$parseDate(this._oData.contract_breach_fees_datetime, 'Y-m-d H:i:s').$format('Y-m-d\\TH:i:s') + '+10:00');

				oPenaltiesStatus.setAttribute('data-status', sAction.toLowerCase());
			}

			// Penalties: Payout
			var	oContractPayout	= this.$$('.component-service-contract-details-penalties-payout')[0];

			// Penalties: Payout: % Remaining
			this.$$('.component-service-contract-details-penalties-payout-percentage-value')[0].innerHTML	= parseFloat(this._oData.rate_plan.contract_payout_percentage).toFixed(2);

			// Penalties: Payout: Recommended
			this.$$('.component-service-contract-details-penalties-payout-recommended-value')[0].innerHTML	= (this._oData.contract_payout_recommended) ? parseFloat(this._oData.contract_payout_recommended).toFixed(2) : '0.00';

			// Penalties: Payout: Effective
			var	oContractPayoutEffectiveAction		= this.$$('.component-service-contract-details-penalties-payout-effective-action')[0],
				oContractPayoutEffectivePayout		= this.$$('.component-service-contract-details-penalties-payout-effective-details-payout')[0],
				oContractPayoutEffectivePercentage	= this.$$('.component-service-contract-details-penalties-payout-effective-details-percentage')[0];
			if (this._oData.contract_payout_charge_id) {
				// Applied
				oContractPayoutEffectiveAction.innerHTML		= 'Applied';
				oContractPayoutEffectivePayout.innerHTML		= parseFloat(this._oData.contract_payout_charge.amount).toFixed(2);
				oContractPayoutEffectivePercentage.innerHTML	= parseFloat(this._oData.contract_payout_percentage).toFixed(2);
				oContractPayout.setAttribute('data-status', 'applied');
			} else if (bPenaltiesReviewed) {
				// Waived
				oContractPayoutEffectiveAction.innerHTML		= 'Waived';
				oContractPayoutEffectivePayout.innerHTML		= '0.00';
				oContractPayoutEffectivePercentage.innerHTML	= '0.00';
				oContractPayout.setAttribute('data-status', 'waived');
			} else if (this._oData.contract_status.constant === 'CONTRACT_STATUS_BREACHED') {
				// Pending
				oContractPayoutEffectiveAction.innerHTML		= 'Pending Review';
				oContractPayoutEffectivePayout.innerHTML		= '0.00';
				oContractPayoutEffectivePercentage.innerHTML	= '0.00';
				oContractPayout.setAttribute('data-status', 'pending-review');
			} else if (this._oData.contract_status.constant === 'CONTRACT_STATUS_ACTIVE') {
				// Contract Active
				oContractPayoutEffectiveAction.innerHTML		= 'Contract Active';
				oContractPayoutEffectivePayout.innerHTML		= '0.00';
				oContractPayoutEffectivePercentage.innerHTML	= '0.00';
				oContractPayout.setAttribute('data-status', 'contract-active');
			} else {
				// Contract Expired
				oContractPayoutEffectiveAction.innerHTML		= 'Not Applicable';
				oContractPayoutEffectivePayout.innerHTML		= '0.00';
				oContractPayoutEffectivePercentage.innerHTML	= '0.00';
				oContractPayout.setAttribute('data-status', 'not-applicable');
			}

			// Penalties: Early Exit
			var	oExitFee	= this.$$('.component-service-contract-details-penalties-earlyexit')[0];

			// Penalties: Early Exit: Recommended
			this.$$('.component-service-contract-details-penalties-earlyexit-recommended-value')[0].innerHTML	= (this._oData.rate_plan.contract_payout_recommended) ? parseFloat(this._oData.exit_fee_recommended).toFixed(2) : '0.00';

			// Penalties: Early Exit: Effective
			var	oExitFeeEffectiveAction		= this.$$('.component-service-contract-details-penalties-earlyexit-effective-action')[0],
				oExitFeeEffectiveAmount		= this.$$('.component-service-contract-details-penalties-earlyexit-effective-details-amount')[0];
			if (this._oData.exit_fee_charge_id) {
				// Applied
				oExitFeeEffectiveAction.innerHTML		= 'Applied';
				oExitFeeEffectiveAmount.innerHTML		= parseFloat(this._oData.exit_fee_charge.amount).toFixed(2);
				oExitFee.setAttribute('data-status', 'applied');
			} else if (bPenaltiesReviewed) {
				// Waived
				oExitFeeEffectiveAction.innerHTML		= 'Waived';
				oExitFeeEffectiveAmount.innerHTML		= '0.00';
				oExitFee.setAttribute('data-status', 'waived');
			} else if (this._oData.contract_status.constant === 'CONTRACT_STATUS_BREACHED') {
				// Pending
				oExitFeeEffectiveAction.innerHTML		= 'Pending Review';
				oExitFeeEffectiveAmount.innerHTML		= '0.00';
				oExitFee.setAttribute('data-status', 'pending-review');
			} else if (this._oData.contract_status.constant === 'CONTRACT_STATUS_ACTIVE') {
				// Contract Active
				oExitFeeEffectiveAction.innerHTML		= 'Contract Active';
				oExitFeeEffectiveAmount.innerHTML		= '0.00';
				oExitFee.setAttribute('data-status', 'contract-active');
			} else {
				// Contract Expired
				oExitFeeEffectiveAction.innerHTML		= 'Not Applicable';
				oExitFeeEffectiveAmount.innerHTML		= '0.00';
				oExitFee.setAttribute('data-status', 'not-applicable');
			}

			// Component is ready
			//----------------------------------------------------------------//
			this._onReady();
		}
	},

	getMetadata	: function () {
		if (!this._oData) {
			throw "Data has not been loaded yet";
		}
		return {
			iServiceRatePlanId	: this.get('iServiceRatePlanId'),
			iServiceId			: this._oData.service_id,
			sFNN				: this._oData.service.service_identifier,
			iRatePlanId			: this._oData.rate_plan_id,
			sRatePlanName		: this._oData.rate_plan.name
		};
	}
});

Component_Service_Contract_Details.PHONE_TYPES	= {
	'__DEFAULT__'				: 'blank',
	'SERVICE_TYPE_ADSL'			: 'adsl',
	'SERVICE_TYPE_INBOUND'		: 'inbound',
	'SERVICE_TYPE_LAND_LINE'	: 'landline',
	'SERVICE_TYPE_MOBILE'		: 'mobile'
};

Component_Service_Contract_Details.createAsPopup	= function () {
	var	aArguments							= $A(arguments),
		oLoading							= new Reflex_Popup.Loading("Fetching Details...", true);
		oPopup								= new Reflex_Popup(40),
		oFooterCloseButton					= $T.button(
			$T.img({src:'../admin/img/template/tick.png','class':'icon',alt:''}),
			$T.span('OK')
		);

	aArguments[0].fnOnReady	= function(oEvent){
		//debugger;
		var	oDetails	= oEvent.getTarget().getMetadata();
		oPopup.setTitle('Contract for '+oDetails.sFNN+' on '+oDetails.sRatePlanName);
		oPopup.display();

		oLoading.hide();
	};

	var	oComponentServiceContractDetails	= Component_Service_Contract_Details.constructApply(aArguments);
	oFooterCloseButton.observe('click', oPopup.hide.bind(oPopup));

	oPopup.setTitle('Contract for [SERVICE] on [PLAN]');
	oPopup.setIcon('../admin/img/template/contract.png');
	oPopup.addCloseButton();
	oPopup.setFooterButtons([
		oFooterCloseButton
	], true);

	oPopup.setContent(oComponentServiceContractDetails.getNode());

	return oPopup;
};
