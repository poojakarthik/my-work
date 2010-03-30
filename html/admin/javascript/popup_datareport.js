
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
			// AJAX request to get datareport details
			this._getDataReportForId	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'DataReport', 'getForId');
			this._getDataReportForId(this.iDataReportId);
		}
		else if (oResponse.Success) 
		{
			debugger;
			// Build UI
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
													$T.th('Report Name :')/*,
													$T.td(oDataReport.Name)*/
												),
												$T.tr(
													$T.th('Report Summary :')/*,
													$T.td(oDataReport.Summary)*/
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
											$T.tbody({class: 'datareport-select-options'}
												
											)
										),
										$T.table({class: 'reflex datareport-limit'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Report Limit'
													)
												)
											),
											$T.tbody(
												$T.tr(
													$T.th('Maximum Results :'),
													$T.td(
														$T.input({type: 'text'})
													)
												)
											)
										),
										$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Output Format'
													)
												)
											),
											$T.tbody(
												$T.tr(
													$T.th('Output Format'),
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
			/*var oSelectDetailsTBody	= oContent.select('tbody.datareport-select-options').first();
			
			for (var sFieldName in oDataReport.SQLSelect)
			{
				oSelectDetailsTBody.appendChild(
											$T.tr(
												$T.td(sFieldName)
											)
										);
			}*/
			
			// Insert constraint input table if necessary
			/*var oFields 	= jQuery.json.arrayAsObject(oDataReport.SQLFields);
			
			if (typeof oFields !== 'undefined')
			{
				var oLimitTable				= oContent.select('table.datareport-limit').first();
				var oConstraintInputTable	= 	$T.table({class: 'reflex'},
													$T.caption(
														$T.div({class: 'caption_bar'},						
															$T.div({class: 'caption_title'},
																'Report Constraint Input'
															)
														)
													),
													$T.tbody(
														
													)
												);
				
				// Insert constraint table before limit table
				oContent.select('div.datareport-tables').first().insertBefore(oConstraintInputTable, oLimitTable);
			}*/
			
			// Insert the output format options
			/*var oOutputFormatTBody	= oContent.select('tbody.datareport-output-format').first();
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
												$T.td('Excel'),
												$T.td(oExcelRadio)
											)
										);
			oOutputFormatTBody.appendChild(
											$T.tr(
												$T.td('CSV'),
												$T.td(oCSVRadio)
											)
										);*/
			
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
