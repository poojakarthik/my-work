
var Popup_CDR	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType)
	{
			$super(80);		
			this._oLoadingPopup	= new Reflex_Popup.Loading();
			this._sStart = strStartDate;
			this._sEnd = strEndDate;
			this._sFNN = strFNN;
			this._iCarrier = intCarrier;
			this._iServiceType = intServiceType;
			
			this._buildUI();
		
	},
	
	
	_buildUI	: function(oResponse)
	{
		if (!oResponse)
		{
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'CDR', 'GetDelinquentCDRs');
			fnRequest(this._sStart,this._sEnd, this._sFNN, this._iCarrier, this._iServiceType);
		
		}
		else
		{
					this._oContentDiv 	=  $T.div({class: 'content'},
											 $T.table({class: 'reflex highlight-rows'},
												$T.thead(
													// Column headings
													$T.tr(
														$T.th('Record #'),
														$T.th('Start Time'),
														$T.th('Cost'),
														$T.th('New Owner'),
														$T.th(' ')
													)
													
												),
												this._tBody = $T.tbody({class: 'alternating'}
													
												)
											)
										);
										
		
		// Add the rows
		
			this._oData	= oResponse.aRecords;
			var keys = Object.keys(oResponse.aRecords.CDRs);
			
			for (var i = 0;i<keys.length;i++)
			{				
				this._tBody.appendChild(this._createTableRow(oResponse.aRecords.CDRs[keys[i]], i+1));
			}
			
			
			this.setTitle('Delinquent CDRs');
			this.addCloseButton();
			this.setContent(this._oContentDiv);
			this._oLoadingPopup.hide();
			this.display();		
		}
		
		
							
					
		
	},
	
	_showServicesPopup: function(iId)
	{
	
	
	},
	
	_writeOff: function (iId)
	{
	
	
	},
	
	_createTableRow	: function(oCDR, iCount)
	{
		

		var writeOff = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/delete.png", alt: 'Write Off', title: 'Write Off'}).observe('click', this._writeOff.bind(this, this, oCDR.Id));
		var assign = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/telephone_add.png", alt: 'Assign to Service', title: 'Assign to Service'}).observe('click', this._showServicesPopup.bind(this, oCDR.Id));
		//var viewDetails = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/magnifier.png", alt: 'Show Details', title: 'Show Details'}).observe('click', this._showCDRPopup.bind(this, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType));

			
			var	oTR	=	$T.tr(
							$T.td(iCount),
							$T.td(oCDR.Time),
							$T.td(oCDR.Cost),
							$T.td(oCDR.Service),
							$T.td({class : "followup-list-all-action-icons"},assign, writeOff)
							//$T.td({class : "followup-list-all-action-icons"},writeOff, assign, viewDetails)
						);
			

			
			return oTR;
		
	}
	});