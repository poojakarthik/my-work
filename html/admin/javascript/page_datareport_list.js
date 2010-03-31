
var Page_DataReport_List = Class.create(
{
	initialize	: function(oContainerDiv, iMaxRecordsPerPage)
	{
		this.oContainerDiv 	= oContainerDiv;
		this.hDataReports	= {};
		this._buildUI();
	},

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// AJAX request to get datareports
			this._getDataReports	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'DataReport', 'getAll');
			this._getDataReports();
		}
		else if(oResponse.Success)
		{
			// Create the page HTML
			this.oContentDiv 	= $T.div(
									$T.table({class: 'reflex highlight-rows'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Report Listing'
													)
												),
												$T.div({class: 'caption_options'},
													$T.ul({class: 'reset horizontal datareport-legend'},
														$T.li(
															$T.img({src: Page_DataReport_List.INSTANT_REPORT_IMAGE_SOURCE, alt: 'Immediate', title: 'Immediate'}),
															$T.span(' : Immediate')
														),
														$T.li(
															$T.img({src: Page_DataReport_List.EMAIL_REPORT_IMAGE_SOURCE, alt: 'Emailed', title: 'Emailed'}),
															$T.span(' : Emailed')
														)
													)
												)
											),
											$T.thead(
												$T.tr(
													$T.th('Report Name'),
													$T.th('Type'),
													$T.th('Report Summary')
												)
											),
											$T.tbody({class: 'alternating'}
												// ...
											),
											$T.tfoot( 
												// ...
											)
										)
									);
			
			// Add rows
			var oTBody 	= this.oContentDiv.select('tbody').first();
			var	aData	= jQuery.json.arrayAsObject(oResponse.aRecords);
			
			for (var iId in aData)
			{
				oTBody.appendChild(this._createRow(aData[iId]));
			}
			
			// Attach content
			this.oContainerDiv.appendChild(this.oContentDiv);
		}
		else
		{
			// Error
			Page_DataReport_List.ajaxError(oResponse);
		}
	},
	
	_createRow	: function(oData)
	{
		if (oData.Id != null)
		{
			// Add a row with the datareports details, alternating class applied
			var sTypeImage	= '';
			var sTypeAlt 	= '';
			
			switch (oData.RenderMode)
			{
				case 1:
					sTypeImage	= Page_DataReport_List.EMAIL_REPORT_IMAGE_SOURCE;
					sTypeAlt	= 'Emailed';
					break;
				case 0:
					sTypeImage	= Page_DataReport_List.INSTANT_REPORT_IMAGE_SOURCE;
					sTypeAlt	= 'Immediate';
					break;
					
			}	
			
			var	oTR	=	$T.tr(
							$T.td({class: 'datareport-name'},
								$T.a(oData.Name)
							),
							$T.td(
								$T.img({src: sTypeImage, alt: sTypeAlt, title: sTypeAlt})
							),
							$T.td(oData.Summary)
						);
			
			if (oData.bHidden)
			{
				oTR.addClassName('datareport-hidden');
			}
			
			// Attach click event to the name anchor
			var oA	= oTR.select('a').first();
			oA.observe('click', this._runReport.bind(this, oData.Id));
			
			return oTR;
		}
	},
	
	_runReport	: function(iId)
	{
		new Popup_DataReport(iId);
	}
});

// Image constants
Page_DataReport_List.INSTANT_REPORT_IMAGE_SOURCE	= '../admin/img/template/report_instant.png';
Page_DataReport_List.EMAIL_REPORT_IMAGE_SOURCE		= '../admin/img/template/report_email.png';

Page_DataReport_List.ajaxError	= function(oResponse)
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
