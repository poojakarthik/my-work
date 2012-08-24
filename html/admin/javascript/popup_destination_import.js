
var Popup_Destination_Import	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(60);
		this.setTitle('Destination Import');
		this.addCloseButton();
		
		this.CONTROLS	= {};
		this.CONTROLS.oDestinationContext	= Control_Field.factory('select', {
			fnPopulate	: this._getDestinationContexts.bind(this),
			
			mVisible					: true,
			mEditable					: true,
			mMandatory					: true,
			bDisableValidationStyling	: true
		});
		this.CONTROLS.oDestinationContext.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this.CONTROLS.oCarrier	= Control_Field.factory('select', {
			fnPopulate	: this._getCarriers.bind(this),
			
			mVisible					: true,
			mEditable					: true,
			mMandatory					: true,
			bDisableValidationStyling	: true
		});
		this.CONTROLS.oCarrier.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._buildContent();
	},
	
	_buildContent	: function()
	{
		this.setContent(
			$T.div({class:'destination-import'},
				$T.div({class:'section'},
					$T.div({class:'section-header'},
						$T.div({class:'section-header-title'},
							$T.span('Match Carrier Destinations')
						)
					),
					$T.div({class:'section-content'},
						$T.div({class:'destination-import-input'},
							$T.form({id: 'destination_import', name: 'destination_import', method: 'POST', action: '../admin/reflex.php/Developer/MatchDestinationsFromCSV/', enctype: 'multipart/form-data'},
								$T.table({class:'reflex input'},
									$T.tbody(
										$T.tr({class:'destination-import-input-carrier'},
												$T.th($T.label('Carrier')),
												$T.td(this.CONTROLS.oCarrier.getElement())
											),
										$T.tr({class:'destination-import-input-destinationcontext'},
												$T.th($T.label('Destination Context')),
												$T.td(this.CONTROLS.oDestinationContext.getElement())
											),
										$T.tr({class:'destination-import-input-file'},
											$T.th($T.label('CSV File')),
											$T.td($T.input({name:'destinations', type:'file'}))
										),
										$T.tr({class:'destination-import-input-ignorewords'},
											$T.th(
												$T.label('Ignore Words'),
												$T.span({class:'label-description'},'Words that will be ignored by the matching algorithm (separated by spaces)')
											),
											$T.td($T.input({name:'ignore_words', type:'text'}))
										)
									)
								)
							)
						),
						$T.div({class:'destination-import-output'},
							$T.table({class:'reflex'},
								$T.thead(
									$T.tr(
										$T.th('Carrier Code'),
										$T.th('Carrier Description'),
										$T.th({colspan:2}, 'Flex Destination')
									)
								),
								$T.tbody(
									$T.tr(
										$T.td({class:'destination-output-message',colspan:4}, 'Please upload a CSV file to match against')
									)
								)
							)
						)
					)
				)
			)
		);

		this.contentPane.select('.destination-import-output').first().hide();
		
		this.oUploadForm	= this.contentPane.select('form').first();
		this.oUploadForm.observe('submit', this._matchCarrierDestinationsFromCSVFile.bind(this));
		
		this.oOutputTable	= this.contentPane.select('.destination-import-output > table').first();
		
		this.oCancelButton	= $T.button({type:'button'},
								$T.img({class:'icon',src:'../admin/img/template/delete.png'}),
								$T.span('Cancel')
							);
		this.oCancelButton.observe('click', this.hide.bind(this));
		
		this.oUploadButton	= $T.button({type:'button'},
								$T.img({class:'icon',src:'../admin/img/template/import.png'}),
								$T.span('Upload CSV File')
							);
		this.oUploadButton.observe('click', this._matchCarrierDestinationsFromCSVFile.bind(this));
		
		this.oSaveButton	= $T.button({type:'button'},
								$T.img({class:'icon',src:'../admin/img/template/invoice_commit.png'}),
								$T.span('Import Translations')
							);
		this.oSaveButton.observe('click', this.save.bindAsEventListener(this));
		
		this.setFooterButtons([this.oUploadButton, this.oCancelButton], true);
	},
	
	_matchCarrierDestinationsFromCSVFile	: function(oResponse)
	{
		// If there is no response, or oResponse is actually an Event
		if (typeof oResponse === null || typeof oResponse.stop === 'function')
		{
			// Ensure we have selected a Destination Context
			if (this.CONTROLS.oCarrier.getElement().select('select').first().selectedIndex === -1)
			{
				Reflex_Popup.alert('Please select the Carrier to import for.');
				return false;
			}
			if (this.CONTROLS.oDestinationContext.getElement().select('select').first().selectedIndex === -1)
			{
				Reflex_Popup.alert('Please select a Destination Context to search against before continuing.');
				return false;
			}
			else if (!this.oUploadForm.select('input[type="file"]').first().value)
			{
				Reflex_Popup.alert('Please select a CSV File to import before continuing.');
				return false;
			}
			
			// Send Request
			if (jQuery.json.jsonIframeFormSubmit(this.oUploadForm, this._matchCarrierDestinationsFromCSVFile.bind(this)))
			{
				this.oUploadForm.submit();

				this.CONTROLS.oDestinationContext.save(true);
				this.CONTROLS.oDestinationContext.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				this.CONTROLS.oCarrier.save(true);
				this.CONTROLS.oCarrier.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				this.oUploadForm.select('input').each(function(oElement){oElement.setAttribute('disabled', 'disabled');});
				
				this.oLoadingSplash	= new Reflex_Popup.Loading('Matching Destinations...');
				this.oLoadingSplash.display();

				this.setFooterButtons([this.oCancelButton], true);
			}
		}
		else
		{
			if (this.oLoadingSplash)
			{
				this.oLoadingSplash.hide();
				delete this.oLoadingSplash;
			}
			
			// Handle Response
			if (oResponse.Success)
			{
				this._populateTable(oResponse.aResults);

				this.oSaveButton.setAttribute('disabled', 'disabled');
				this.setFooterButtons([this.oSaveButton, this.oCancelButton], true);
			}
			else
			{
				Reflex_Popup.alert("There was an error accessing the database: " + oResponse.Message);
			}
		}
	},
	
	_populateTable	: function(aData)
	{
		//debugger;
		var	oTableBody		= this.oOutputTable.select('tbody').first(),
			oPopulateQueue	= $Q();
		oTableBody.childElements().each(Element.remove);
		for (var mCarrierCode in aData)
		{
			// Add to the queue (each item will wait for an empty JS stack before continuing)
			oPopulateQueue.push((function(oCarrierDestination){oTableBody.appendChild(this._buildTableRow(oCarrierDestination))}).bind(this, aData[mCarrierCode]), true);
		}
		oPopulateQueue.push(this.oSaveButton.removeAttribute.bind(this.oSaveButton, 'disabled'), true);	// Recentre the popup
		oPopulateQueue.push(this.recentre.bind(this), true);	// Recentre the popup
		
		this.contentPane.select('.destination-import-output').first().show();
		
		// Execute the Queue once the JS execution stack is clear
		oPopulateQueue.execute.bind(oPopulateQueue).defer();
	},
	
	_buildTableRow	: function(oCarrierDestination)
	{
		//debugger;
		var	oOptGroup			= $T.optgroup({class:'destination-import-matched', label:'Matched Destinations'}),
			//oManualSelection	= $T.option({class:'destination-import-manual-select', value: null}, 'Manual Selection...'),
			oUnknownDestination	= $T.option({class:'destination-import-unknown-destination', value:Popup_Destination_Import.DESTINATION_MATCH_UNKNOWN}, 'Unknown Destination'),
			oSelect				= $T.select(
									oOptGroup,
									oUnknownDestination
								),
			oSearch				= $T.img({class:'icon button', src:'../admin/img/template/magnifier.png', alt:'Search', title:'Search'}),
			oTR					= $T.tr({'data-destination-carrier-code':oCarrierDestination.mCarrierCode},
									$T.td(oCarrierDestination.mCarrierCode),
									$T.td(oCarrierDestination.sCarrierDescription),
									$T.td(oSelect),
									$T.td(oSearch)
								);
		var iMatches	= oCarrierDestination.aMatches.length;
		
		if (iMatches > 0)
		{
			for (var i = 0; i < iMatches; i++)
			{
				var	oDestination		= oCarrierDestination.aMatches[i],
					oOption				= Popup_Destination_Import._createDestinationOption(oDestination);
				
				// Add this Destination as an Option
				oOptGroup.appendChild(oOption);
				
				// If this was a perfect match, then mark it as selected
				if (oDestination.bPerfectMatch)
				{
					oOption.setAttribute('selected', 'selected');
					oSelect.setAttribute('data-destination-match-perfect', oDestination.Code);
				}
				
				oOption.observe('mouseup', Popup_Destination_Import._onDestinationSelect);
				oOption.observe('keyup', Popup_Destination_Import._onDestinationSelect);
			}
		}
		else
		{
			oUnknownDestination.setAttribute('selected', 'selected');
		}
		
		oUnknownDestination.observe('mouseup', Popup_Destination_Import._onDestinationSelect);
		oUnknownDestination.observe('keyup', Popup_Destination_Import._onDestinationSelect);
		
		oSelect.observe('mouseup', Popup_Destination_Import._onDestinationSelect);
		oSelect.observe('keyup', Popup_Destination_Import._onDestinationSelect);
		oSelect.observe('change', Popup_Destination_Import._onDestinationSelect);
		Popup_Destination_Import._onDestinationSelect(null, oSelect);
		
		oSearch.observe('click', this._selectManualTranslation.bind(this, oCarrierDestination));
		
		return oTR;
	},
	
	_selectManualTranslation	: function(oCarrierDestination)
	{
		var	iDestinationContextId	= this.CONTROLS.oDestinationContext.getValue(),
			oManualPopup			= new Popup_Destination_Import_Manual(iDestinationContextId, oCarrierDestination, this._setManualTranslation.bind(this, oCarrierDestination));
		oManualPopup.display();
	},
	
	_setManualTranslation	: function(oCarrierDestination, oDestination)
	{
		var	oSelect	= this.oOutputTable.select('tr[data-destination-carrier-code="'+oCarrierDestination.mCarrierCode+'"] select').first(),
			oOption	= oSelect.select('option[value="'+oDestination.Code+'"]').first();
		
		// Create the option if it doesn't already exist
		if (!oOption)
		{
			oOption	= Popup_Destination_Import._createDestinationOption(oDestination);
			oSelect.select('optgroup').first().appendChild(oOption);
		}
		
		// Select the option
		oOption.setAttribute('selected', 'selected');
		
		// Update Select
		Popup_Destination_Import._onDestinationSelect(null, oSelect);
	},
	
	save	: function(oEvent, oResponse)
	{
		if (oResponse)
		{
			// Hide & clean up the splash
			this.oSavingSplash.hide();
			delete this.oSavingSplash;
			
			// Handle response
			if (oResponse.Success || oResponse.bSuccess)
			{
				// Success
				Reflex_Popup.alert('Destination translations were successfully imported', {
					sTitle		: 'Destination Import Successful',
					sIconSource	: '../admin/img/template/invoice_commit.png',
					fnClose		: this.hide.bind(this)
				});
			}
			else
			{
				// Failure
				jQuery.json.errorPopup(oResponse, "There was an error while importing the Destination translations, please retry");
			}
			
			// Debug information will only show if the user is God
			if (oResponse.sDebug)
			{
				Reflex_Popup.alert(oResponse.sDebug);
			}
		}
		else
		{
			//debugger;
			
			// Prepare dataset for POSTing
			var	oDestinations			= {},
				aCarrierDestinationTRs	= $A(this.oOutputTable.select('tr[data-destination-carrier-code]'));
			
			for (var i = 0, j = aCarrierDestinationTRs.length; i < j; i++)
			{
				var	oTR					= aCarrierDestinationTRs[i],
					oSelect				= oTR.select('select').first(),
					iDestinationCode	= parseInt(oSelect.options[oSelect.selectedIndex].value, 10),
					mCarrierCode		= oTR.getAttribute('data-destination-carrier-code');
				
				oDestinations[mCarrierCode]	= {
					mCarrierCode		: mCarrierCode,
					iDestinationCode	: iDestinationCode,
					sDescription		: oTR.select('td')[1].innerHTML.unescapeHTML()
				};
			}
			
			// POST!
			jQuery.json.jsonFunction(this.save.bind(this, null), this.save.bind(this, null), 'Destination', 'ImportTranslations')(oDestinations, parseInt(this.CONTROLS.oCarrier.getValue(), 10));
			
			this.oSavingSplash	= new Reflex_Popup.Loading('Importing Destination Translations');
			this.oSavingSplash.display();
		}
	},
	
	_getCarriers	: function(cCallback, oResponse)
	{
		if (oResponse)
		{
			// Handle response
			if (oResponse.Success || oResponse.bSuccess)
			{
				// Success
				var	aOptions	= [];
				for (var i = 0, j = oResponse.aRecords.length; i < j; i++)
				{
					var	oCarrier	= oResponse.aRecords[i],
						oOption		= $T.option({value:oCarrier.Id}, oCarrier.Name);
					aOptions.push(oOption);
				}
				
				cCallback(aOptions);
			}
			else
			{
				// Failure
				jQuery.json.errorPopup(oResponse);
			}
		}
		else
		{
			// Perform the Request
			jQuery.json.jsonFunction(this._getCarriers.bind(this, cCallback), this._getCarriers.bind(this, cCallback), 'Carrier', 'getCarriers')('CARRIER_TYPE_TELECOM');
		}
	},
	
	_getDestinationContexts	: function(cCallback, oResponse)
	{
		if (oResponse)
		{
			// Handle response
			if (oResponse.Success || oResponse.bSuccess)
			{
				// Success
				var	aOptions	= [];
				for (var i = 0, j = oResponse.aRecords.length; i < j; i++)
				{
					var	oDestinationContext	= oResponse.aRecords[i],
						oOption				= $T.option({value:oDestinationContext.id}, oDestinationContext.description);
					aOptions.push(oOption);
				}
				
				cCallback(aOptions);
			}
			else
			{
				// Failure
				jQuery.json.errorPopup(oResponse);
			}
		}
		else
		{
			// Perform the Request
			jQuery.json.jsonFunction(this._getDestinationContexts.bind(this, cCallback), this._getDestinationContexts.bind(this, cCallback), 'Destination', 'getDestinationContexts')();
		}
	}
});

Popup_Destination_Import.DESTINATION_MATCH_UNKNOWN	= -1;

Popup_Destination_Import._onDestinationSelect	= function(oEvent, oSelect)
{
	var	oSelect	= (oSelect) ? oSelect : oEvent.findElement(),
		oOption	= (oSelect.selectedIndex > -1) ? oSelect.options[oSelect.selectedIndex] : null;
	
	// Remove Classes
	oSelect.removeClassName('destination-import-match-unknown').
				removeClassName('destination-import-match-perfect').
				removeClassName('destination-import-match-manual');
	
	if (oOption === null)
	{
		// No selected option
		return;
	}
	else if (oOption.value == oSelect.getAttribute('data-destination-match-perfect'))
	{
		// Perfect match
		oSelect.addClassName('destination-import-match-perfect');
	}
	else if (oOption.value == Popup_Destination_Import.DESTINATION_MATCH_UNKNOWN)
	{
		// Unknown Destination
		oSelect.addClassName('destination-import-match-unknown');
	}
	else
	{
		oSelect.addClassName('destination-import-match-manual');
	}
};

Popup_Destination_Import._createDestinationOption	= function(oDestination)
{
	var	//sOptionDescription	= '('+oDestination.Code+') '+oDestination.Description,
		sOptionDescription	= oDestination.Description,	// Using this one allows better keyboard navigation
		oOption				= $T.option({value:oDestination.Code}, sOptionDescription);
	return oOption;
};
