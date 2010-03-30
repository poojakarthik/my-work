
var Popup_DataReport	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iDataReportId)
	{
		$super(70);
		this.iDataReportId 	= iDataReportId;
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
														'Report Constraint Input'
													)
												)
											),
											$T.tbody({class: 'datareport-select-constraints'},
												$T.tr(
													$T.th({class: 'label'},
														'Maximum Results :'
													),
													$T.td(
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
			}
			
			if (iSelectCount >= Popup_DataReport.MAX_SELECT_OPTIONS_COLUMN_HEIGHT)
			{
				oSelectDetailsDiv.removeClassName('datareport-select-options');
				oSelectDetailsDiv.addClassName('datareport-select-options-columns');
			}
					
			// Insert constraint input table if necessary
			if (oDataReport.aInputData.length > 0)
			{
				Popup_DataReport._createReportConstraints(oDataReport.aInputData, oContent.select('tbody.datareport-select-constraints').first());
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
			var oSaveButton	= oContent.select( 'button' ).first();
			oSaveButton.observe('click', this._runReport.bind(this));
			
			// Set the cancel buttons event handler
			var oCancelButton = oContent.select( 'button' ).last();
			oCancelButton.observe('click', this.hide.bind(this));
			
			this.oContent = oContent; 
			
			this.setTitle('Run Data Report');
			this.setIcon('../admin/img/template/report_small.png');
			this.setContent(oContent);
			this.display();
		}
		else
		{
			// Error in AJAX request
			Reflex_Popup.alert('There was an error accessing the database' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Database Error'});
		}
	},
	
	_runReport	: function()
	{
		
	}
});

Popup_DataReport.CANCEL_IMAGE_SOURCE 		= '../admin/img/template/delete.png';
Popup_DataReport.RUN_REPORT_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

Popup_DataReport.MAX_SELECT_OPTIONS_COLUMN_HEIGHT	= 10;

Popup_DataReport._createReportConstraints	= function(aInputData, oParentTBody)
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
			case 'StatementSelect':
			case 'Query':
				oInput	= Popup_DataReport._createQuerySelect(oInputInfo);
				break;
			case 'dataInteger':
				oInput	= $T.input();
				break;
			case 'dataString':
				oInput	= $T.input();
				break;
			case 'dataBoolean':
				oInput	= Popup_DataReport._createBooleanSelect(oInputInfo);
				break;
			case 'dataFloat':
				oInput	= $T.input();
				break;
			case 'dataDate':
			case 'dataDatetime':
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
		}
	}
	
	return oParentTBody;
}

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
}

Popup_DataReport._createDateTimeSelect	= function(oInputInfo)
{
	var oDiv	=	$T.div(
						Popup_DataReport._createNumberSelect(1, 31),
						'/',
						Popup_DataReport._createNumberSelect(1, 12),
						'/',
						Popup_DataReport._createNumberSelect(2000, 2010)
					);
	
	if (oInputInfo.Type == 'dataDatetime')
	{
		oDiv.appendChild(Popup_DataReport._createNumberSelect(0, 24));
		oDiv.innerHTML += ':';
		oDiv.appendChild(Popup_DataReport._createNumberSelect(0, 59));
		oDiv.innerHTML += ':';
		oDiv.appendChild(Popup_DataReport._createNumberSelect(0, 59));
	}
	
	return oDiv;
}

Popup_DataReport._createNumberSelect	= function(iLowest, iHighest)
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
	
	return oSelect;
}

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
							$T.input({type: 'radio', value: 0, name: oInputInfo.sName}),
							' No'
						)
					)
				)
			);
}
