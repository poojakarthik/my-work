
var Popup_DataReport	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iDataReportId)
	{
		$super(70);
		this.iDataReportId 	= iDataReportId;
		this.hConstraints	= {};
		this.aSelectDetails	= [];
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// Show loading
			this.oLoadingPopup	= new Reflex_Popup.Loading('Getting report details...');
			this.oLoadingPopup.display();
			
			// AJAX request to get datareport details
			this._getDataReportForId	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'DataReport', 'getForId');
			this._getDataReportForId(this.iDataReportId);
		}
		else if (oResponse.Success) 
		{
			// Hide loading
			this.oLoadingPopup.hide();
			delete this.oLoadingPopup;
			
			// Build UI given the datareport details
			var oDataReport	= oResponse.oDataReport;
			var oContent 	=	$T.div({class: 'datareport'},
									$T.div({class: 'datareport-tables'},
										$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Report Details'
													)
												)
											),
											$T.tbody(
												$T.tr(
													$T.th({class: 'label'},
														'Report Name :'
													),
													$T.td(oDataReport.Name)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Report Summary :'
													),
													$T.td(oDataReport.Summary)
												)
											)
										),
										$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Report Select Options'
													)
												)
											),
											$T.tbody(
												$T.tr(
													$T.td(
														$T.div({class: 'datareport-select-options'})
													)
												)
											)
										),
										$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Report Constraints'
													)
												)
											),
											$T.tbody({class: 'datareport-select-constraints'},
												$T.tr(
													$T.th({class: 'label'},
														'Maximum Results :'
													),
													$T.td({class: 'datareport-constraint-limit'},
														$T.input({type: 'text'})
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Output Format :'
													),
													$T.td(
														$T.table(
															$T.tbody({class: 'datareport-output-format'}
																// Output format radio buttons added later
															)
														)
													)
												)
											)
										)
									),
									$T.div ({class: 'datareport-buttons'},
										$T.button(
											$T.img({src: Popup_DataReport.RUN_REPORT_IMAGE_SOURCE, alt: '', title: 'Run Report'}),
											$T.span('Run Report')
										),
										$T.button(
											$T.img({src: Popup_DataReport.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										)
									)
								);
			
			// Insert report select details
			var oSelectDetailsDiv	= oContent.select('div.datareport-select-options').first();
			var iSelectCount		= 0;
			
			for (var sFieldName in oDataReport.SQLSelect)
			{
				oSelectDetailsDiv.appendChild($T.div(sFieldName));
				iSelectCount++;
				
				// Store for later, when executing the report
				this.aSelectDetails.push(sFieldName);
			}
			
			if (iSelectCount >= Popup_DataReport.MAX_SELECT_OPTIONS_COLUMN_HEIGHT)
			{
				oSelectDetailsDiv.removeClassName('datareport-select-options');
				oSelectDetailsDiv.addClassName('datareport-select-options-columns');
			}
					
			// Insert constraint input table if necessary
			if (oDataReport.aInputData.length > 0)
			{
				this._createReportConstraints(oDataReport.aInputData, oContent.select('tbody.datareport-select-constraints').first());
			}
			
			// Insert the output format options
			var oOutputFormatTBody	= oContent.select('tbody.datareport-output-format').first();
			var oExcelRadio			= $T.input({type: 'radio', value: 0, name: 'outputformat'});
			var oCSVRadio			= $T.input({type: 'radio', value: 1, name: 'outputformat'});
			
			switch (oDataReport.RenderTarget)
			{
				case 0:
					oExcelRadio.checked = true;
					oCSVRadio.disabled = true;
					break;
				case 1:
					oExcelRadio.disabled = true;
					oCSVRadio.checked = true;
					break;
				default:
					oExcelRadio.checked = true;
			}
			
			oOutputFormatTBody.appendChild(	
											$T.tr(
												$T.td(
													oExcelRadio,
													' Excel'
												),											
												$T.td(
													oCSVRadio,
													' CSV'
												)
											)
										);
			
			// Set the save buttons event handler
			var oRunButton	= oContent.select( 'button' ).first();
			oRunButton.observe('click', this._executeReport.bind(this));
			
			// Set the cancel buttons event handler
			var oCancelButton = oContent.select( 'button' ).last();
			oCancelButton.observe('click', this.hide.bind(this));
			
			this.oContent = oContent; 
			
			this.setTitle('Run Data Report');
			this.setIcon('../admin/img/template/report_small.png');
			this.setContent(oContent);
			this.addCloseButton();
			this.display();
		}
		else
		{
			// Hide loading
			this.oLoadingPopup.hide();
			delete this.oLoadingPopup;
			
			// Error in AJAX request
			Popup_DataReport._ajaxError(oResponse);
		}
	},
	
	_createReportConstraints	: function(aInputData, oParentTBody)
	{
		var oInputInfo 	= null;
		var oInput 		= null;
		var oTRLimit	= oParentTBody.select('tr').first();
		
		for (var i = 0; i < aInputData.length; i++)
		{
			oInputInfo = aInputData[i];
			oInput = null;
			
			// Create the input based on it's type and using the data given
			switch (oInputInfo.sType)
			{
				case Popup_DataReport.CONSTRAINT_STATEMENT_SELECT:
				case Popup_DataReport.CONSTRAINT_QUERY:
					oInput	= Popup_DataReport._createQuerySelect(oInputInfo);
					break;
				case Popup_DataReport.CONSTRAINT_INTEGER:
					oInput	= $T.input();
					break;
				case Popup_DataReport.CONSTRAINT_STRING:
					oInput	= $T.input();
					break;
				case Popup_DataReport.CONSTRAINT_BOOLEAN:
					oInput	= Popup_DataReport._createBooleanSelect(oInputInfo);
					break;
				case Popup_DataReport.CONSTRAINT_FLOAT:
					oInput	= $T.input();
					break;
				case Popup_DataReport.CONSTRAINT_DATE:
				case Popup_DataReport.CONSTRAINT_DATE_TIME:
					oInput = Popup_DataReport._createDateTimeSelect(oInputInfo);
					break;
			}
			
			if (oInput)
			{
				// Add a new row to the tbody element
				oParentTBody.insertBefore(
								$T.tr(
									$T.th({class: 'label'},
											(oInputInfo.sLabel ? oInputInfo.sLabel : oInputInfo.sFieldName) + ' :'
									),
									$T.td(oInput)
								),
								oTRLimit
							);
				
				// Add to constraints hash
				this.hConstraints[oInputInfo.sName]	= {oInput: oInput, sType: oInputInfo.sType};
			}
		}
		
		return oParentTBody;
	},
	
	_executeReport	: function()
	{
		var hRequestData	= 	{
									iId			: this.iDataReportId,
									aSelect		: this.aSelectDetails,
									hInput		: {},
									sLimit		: null,
									iOutputCSV	: null
								};
		var oInputInfo		= null;
		var mValue			= null;
		
		// Determine the value for each constraint
		for (var sFieldName in this.hConstraints)
		{
			oInputInfo	= this.hConstraints[sFieldName];
			mValue		= null;
			
			// Check type, get value
			switch (oInputInfo.sType)
			{
				case Popup_DataReport.CONSTRAINT_STATEMENT_SELECT:
				case Popup_DataReport.CONSTRAINT_QUERY:
					mValue	= oInputInfo.oInput.value;
					break;
				case Popup_DataReport.CONSTRAINT_INTEGER:
					mValue	= parseInt(oInputInfo.oInput.value);
					break;
				case Popup_DataReport.CONSTRAINT_STRING:
					mValue	= oInputInfo.oInput.value;
					break;
				case Popup_DataReport.CONSTRAINT_FLOAT:
					mValue	= parseFloat(oInputInfo.oInput.value);
					break;
				case Popup_DataReport.CONSTRAINT_BOOLEAN:
					// Boolean as integer
					var aBooleanInputs	= oInputInfo.oInput.select('td > input');
					var bYesRadio		= aBooleanInputs.first();
					var bNoRadio		= aBooleanInputs.last();
					
					if (bYesRadio.checked)
					{
						mValue	= parseInt(bYesRadio.value);
					}
					else
					{
						mValue	= parseInt(bNoRadio.value);
					}
					break;
				case Popup_DataReport.CONSTRAINT_DATE:
					// Object containing date parts
					var aSelects	= oInputInfo.oInput.select('select');
					mValue			= 	{
											day		: parseInt(aSelects[0].value),
											month	: parseInt(aSelects[1].value),
											year	: parseInt(aSelects[2].value)
										};
					break;
				case Popup_DataReport.CONSTRAINT_DATE_TIME:
					// Object containing date & time parts
					var aSelects	= oInputInfo.oInput.select('select');
					mValue			= 	{
											day		: parseInt(aSelects[0].value),
											month	: parseInt(aSelects[1].value),
											year	: parseInt(aSelects[2].value),
											hour	: parseInt(aSelects[3].value),
											minute	: parseInt(aSelects[4].value),
											second	: parseInt(aSelects[5].value)
										};
					break;
			}
			
			if (mValue !== null)
			{
				hRequestData.hInput[sFieldName]	= mValue;
			}
		}
		
		// Get the limit and output format values
		var aOutputFormatInputs	= this.oContent.select('tbody.datareport-output-format > tr input');
		var bExcelRadio			= aOutputFormatInputs.first();
		var bCSVRadio			= aOutputFormatInputs.last();
		
		if (bExcelRadio.checked)
		{
			hRequestData.iOutputCSV	= parseInt(bExcelRadio.value);
		}
		else
		{
			hRequestData.iOutputCSV	= parseInt(bCSVRadio.value);
		}
		
		hRequestData.sLimit	= this.oContent.select('td.datareport-constraint-limit > input').first().value;
		
		// Make AJAX request
		this.oGeneratingPopup	= new Reflex_Popup.Loading('Running Report...');
		this.oGeneratingPopup.display();
		
		this._executReportAJAX	= jQuery.json.jsonFunction(this._executeReponse.bind(this), this._executeReponse.bind(this), 'DataReport', 'executeReport');
		this._executReportAJAX(hRequestData);
	},
	
	_executeReponse	: function(oResponse)
	{
		// Hide loading popup
		this.oGeneratingPopup.hide();
		delete this.oGeneratingPopup;
		
		if (oResponse.Success)
		{
			if (oResponse.sEmail != false)
			{
				Reflex_Popup.alert(
					'The data report will be emailed to ' + oResponse.sEmail + ' when it has been generated',
					{
						sTitle	: 'Email Report',
						fnClose	: this.hide.bind(this)
					}
				);
			}
			else 
			{
				if(oResponse.bNoRecords)
				{
					// No records, show popup
					Reflex_Popup.alert('The data report returned no results');
				}
				else
				{
					// All good, let's show them the report
					window.location = 'reflex.php/DataReport/Download/?sFileName=' + encodeURIComponent(oResponse.sPath) + '&iCSV=' + (oResponse.sPath.match(/\.csv/) ? 1 : 0);
				}
			}
		}
		else
		{
			// Error occurred in execution
			Popup_DataReport._ajaxError(oResponse);
		}
	}
});

/////////////////////////////
// Class constants
/////////////////////////////

// Images
Popup_DataReport.CANCEL_IMAGE_SOURCE 		= '../admin/img/template/delete.png';
Popup_DataReport.RUN_REPORT_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

// Size/Position
Popup_DataReport.MAX_SELECT_OPTIONS_COLUMN_HEIGHT	= 10;

// Report constraint types
Popup_DataReport.CONSTRAINT_STATEMENT_SELECT	= 'StatementSelect';
Popup_DataReport.CONSTRAINT_QUERY				= 'Query';
Popup_DataReport.CONSTRAINT_INTEGER				= 'dataInteger';
Popup_DataReport.CONSTRAINT_STRING				= 'dataString';
Popup_DataReport.CONSTRAINT_BOOLEAN				= 'dataBoolean';
Popup_DataReport.CONSTRAINT_FLOAT				= 'dataFloat';
Popup_DataReport.CONSTRAINT_DATE				= 'dataDate';
Popup_DataReport.CONSTRAINT_DATE_TIME			= 'dataDatetime';

/////////////////////////////
// End Class constants
/////////////////////////////

Popup_DataReport._createQuerySelect		= function(oInputInfo)
{
	var oSelect		= $T.select();
	var oOptionInfo	= null
	
	for (var i = 0; i < oInputInfo.aOptions.length; i++)
	{
		oOptionInfo	= oInputInfo.aOptions[i];
		oSelect.appendChild(
						$T.option({value: oOptionInfo.mValue},
							oOptionInfo.sLabel
						)
					);
	}
	
	return oSelect;
};

Popup_DataReport._createDateTimeSelect	= function(oInputInfo)
{
	// Create date select
	var oDate	= new Date();
	var oDiv	=	$T.div(
						Popup_DataReport._createNumberSelect(1, 31, oDate.getDate()),
						'/',
						Popup_DataReport._createNumberSelect(1, 12, oDate.getMonth() + 1),
						'/',
						Popup_DataReport._createNumberSelect(2000, (new Date()).getFullYear(), oDate.getFullYear()),
						' '
					);
	
	// Add time select if needed (type is dataDatetime)
	if (oInputInfo.sType == Popup_DataReport.CONSTRAINT_DATE_TIME)
	{
		oDiv.appendChild(Popup_DataReport._createNumberSelect(0, 24, oDate.getHours()));
		oDiv.appendChild(document.createTextNode(':'));
		oDiv.appendChild(Popup_DataReport._createNumberSelect(0, 59, oDate.getMinutes()));
		oDiv.appendChild(document.createTextNode(':'));
		oDiv.appendChild(Popup_DataReport._createNumberSelect(0, 59, oDate.getSeconds()));
	}
	
	return oDiv;
};

Popup_DataReport._createNumberSelect	= function(iLowest, iHighest, defaultValue)
{
	var oSelect	= $T.select();
	
	for (var iValue = iLowest; iValue <= iHighest; iValue++)
	{
		oSelect.appendChild(
						$T.option({value: iValue},
							(iValue < 10 ? '0' + iValue : iValue)
						)
					);
	}
	
	if (defaultValue !== null)
	{
		oSelect.value = defaultValue;
	}
	
	return oSelect;
};

Popup_DataReport._createBooleanSelect	= function(oInputInfo)
{
	return 	$T.table(
				$T.tbody(
					$T.tr(
						$T.td(
							$T.input({type: 'radio', value: 1, name: oInputInfo.sName}),
							' Yes'
						),
						$T.td(
							$T.input({type: 'radio', value: 0, name: oInputInfo.sName, checked: true}),
							' No'
						)
					)
				)
			);
};

Popup_DataReport._ajaxError	= function(oResponse)
{
	if (oResponse.Message)
	{
		Reflex_Popup.alert(oResponse.Message, {sTitle: 'Error'});
	}
	else if (oResponse.ERROR)
	{
		Reflex_Popup.alert(oResponse.ERROR, {sTitle: 'Error'});
	}
}
