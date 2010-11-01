
var Popup_CDR	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iStatus)
	{
			debugger;
			$super(80);		
			this._oLoadingPopup	= new Reflex_Popup.Loading();
			this._sStart = strStartDate;
			this._sEnd = strEndDate;
			this._sFNN = strFNN;
			this._iCarrier = intCarrier;
			this._iServiceType = intServiceType;
			this._iStatus = iStatus;
			this._buildUI();
		
	},
	
	
	_buildUI	: function(oResponse)
	{
		if (!oResponse)
		{
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'CDR', 'GetDelinquentCDRs');
			fnRequest(this._sStart,this._sEnd, this._sFNN, this._iCarrier, this._iServiceType, this._iStatus);
		
		}
		else
		{
					this._oContentDiv 	=  $T.div({class: 'content'},
											 $T.div({class: 'content-delinquent-cdrs'},
											 $T.div( {class: 'content-delinquent-cdrs-table'},
											 $T.table({class: 'reflex highlight-rows'},
												$T.thead(
													// Column headings
													$T.tr(
														$T.th('Record #'),
														$T.th('Start Time'),
														$T.th('Cost'),
														$T.th('Status'),
														$T.th(' ')
													)
													
												),
												this._tBody = $T.tbody({class: 'alternating'}
													
												)
											)),
											
											$T.div({class: 'buttons'},
											
											
													$T.button({class: 'icon-button'},
																$T.img({src: "../admin/img/template/table.png", alt: '', title: 'Refresh List'}),
																$T.span('Export to CSV')
																	).observe('click', this._downloadCSV.bind(this, false)),																
													$T.button({class: 'icon-button'},
													$T.img({src: '../admin/img/template/telephone_add.png', alt: '', title: 'Close'}),
													$T.span('Add all to Service')
													).observe('click', this._bulkAssign.bind(this, false, false)),
													$T.button({class: 'icon-button'},
													$T.img({src: '../admin/img/template/delete.png', alt: '', title: 'Close'}),
													$T.span('Write off all')
													).observe('click', this._bulkWriteOff.bind(this, false, false)),
													$T.button({class: 'icon-button float-right'},
													$T.img({src: '../admin/img/template/delete.png', alt: '', title: 'Close'}),
													$T.span('Close')
													).observe('click', this._close.bind(this))
																	
												))
													
											
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
	
	_bulkWriteOff : function (bConfirm, oResponse)
	{		
		if (!oResponse)
		{
			if (!bConfirm)
			{
						Reflex_Popup.yesNoCancel(
													"This will set the status for all CDRs with FNN " + this.sFNN + " to Write Off. Is that what you want to do?",
													{
														sNoLabel		: 'No', 
														sYesLabel		: 'Yes',														
														bOverrideStyle	: true,
														iWidth			: 45,
														sTitle			: 'CDR Writeoff',
														fnOnYes			: this._bulkWriteOff.bind(this,true, false)														
													}
												);
			
			}
			else
			{
				debugger;
				this._oLoadingPopup.display();
				var fnRequest     = jQuery.json.jsonFunction(this._bulkWriteOff.bind(this, true), null, 'CDR', 'bulkWriteOffForFNN');
				fnRequest(this._sStart, this._sEnd, this._sFNN, this._iCarrier, this._iServiceType);		
				
			}
		}
		else
		{
			this._oLoadingPopup.hide();
			this._refreshDataTable(oResponse.aData);
			Reflex_Popup.alert('All CDRs for FNN ' + this._sFNN + ' have been written off succesfully');
		}
	
	},
	
	_refreshDataTable: function (aData)
	{
		
		while(this._tBody.childElementCount>0)
		{
		 this._tBody.deleteRow(this._tBody.childElementCount-1);

		}	

debugger;		
		//var keys = Object.keys(oData);			
		for (var i = 0;i<aData.length;i++)
		{				
			this._tBody.appendChild(this._createTableRow(aData[i], i+1));
		}
	
	
	
	},
	
	_bulkAssign: function (iServiceId, oResponse)
	{
		if (!oResponse)
		{
			if (!iServiceId)
			{
				new Popup_CDR_Service_List(this._bulkAssign.bind(this), this._oData.Services);			
			}
			else
			{
				var fnRequest     = jQuery.json.jsonFunction(this._bulkAssign.bind(this), null, 'CDR', 'BulkAssignCDRsToServices');
				fnRequest(this._sFNN,this._iCarrier,   this._iServiceType,this._sStart, this._sEnd, iServiceId);
			}
		
		}
		else
		{			
			alert('reset all statusses');		
		}	
	},
	
	
	
	_downloadCSV	: function(oResponse)
	{		
		if (!oResponse)
		{	
			
			
			//this._oSort.refreshData(true);
			//this._oFilter.refreshData(true);
			
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._downloadCSV.bind(this), null, 'CDR', 'ExportToCSV');
			//fnRequest(this.oDataSet._hSort, this.oDataSet._hFilter);
			fnRequest(Object.keys(this._oData.CDRs));
			//window.location	= 'reflex.php/CDR/ExportToCSV/' + this.oDataSet._hSort + '/' + this.oDataSet._hFilter;
		}
		else
		{
			sFilename	= oResponse.FileName.replace(/\//g, "\\");
			window.location	= 'reflex.php/CDR/DownloadCSV/' + encodeURIComponent(sFilename);
			this._oLoadingPopup.hide();
		}
			
	},
	
	_showServicesPopup: function(iId, oStatusCell)
	{	
		
		//new Popup_CDR_Service_List(iId, null, null, this._sStart, this._sEnd, this._sFNN, this._iCarrier, this._iServiceType);
		new Popup_CDR_Service_List(this._setService.bind(this, oStatusCell, iId, false), this._oData.Services);

	},
	
	_setService : function (oStatusCell, iCDRId, oResponse, iServiceId)
	{
		if (!oResponse)
		{
			
			 
			// AssignCDRsToServices($sFNN, $iCarrier, $iServiceType, $sCDRs)
			
			var fnRequest     = jQuery.json.jsonFunction(this._setService.bind(this, oStatusCell, iCDRId), null, 'CDR', 'AssignCDRsToServices');
			//fnRequest(this.oDataSet._hSort, this.oDataSet._hFilter);
			fnRequest(this._sFNN, this._iCarrier, this._iServiceType, [{Id: iCDRId, Service: iServiceId}] );
		
		}
		else
		{
			
			oStatusCell.innerHTML = 'Assigned to Account: ' + oResponse.aData[iCDRId].account_id + ', FNN: ' + oResponse.aData[iCDRId].fnn;
			
		
		
		}
		
	
	
	},
	
	_writeOff : function (td, iId, bConfirm, oResponse)
	{
		
		
		if (!oResponse)
		{
			if (!bConfirm)
			{
						Reflex_Popup.yesNoCancel(
													"This will set the status for all CDR with ID: " +  iId + " to Write Off. Is that what you want to do?",
													{
														sNoLabel		: 'No', 
														sYesLabel		: 'Yes',														
														bOverrideStyle	: true,
														iWidth			: 45,
														sTitle			: 'CDR Writeoff',
														fnOnYes			: this._writeOff.bind(this,td, iId, true, false)														
													}
												);
			
			}
			else
			{
				
				this._oLoadingPopup.display();
				var fnRequest     = jQuery.json.jsonFunction(this._writeOff.bind(this, td, iId, true), null, 'CDR', 'writeOffCDRs');
				fnRequest([iId]);
			}
		}
		else
		{
			this._oLoadingPopup.hide();
			Reflex_Popup.alert('CDR with ID: ' +  iId + 'has been written off succesfully');
			td.innerHTML = 'Delinquent Usage - Written Off';
		}
	
	},
	
	_createTableRow	: function(oCDR, iCount)
	{
		

		var writeOff = "";
		var assign = "";
		//var viewDetails = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/magnifier.png", alt: 'Show Details', title: 'Show Details'}).observe('click', this._showCDRPopup.bind(this, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType));
		var statusCell = $T.td(oCDR.Status);
		if (oCDR.StatusId ==107)
		{
		 writeOff = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/delete.png", alt: 'Write Off', title: 'Write Off'}).observe('click', this._writeOff.bind(this, statusCell, oCDR.Id, false, false));
		 assign = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/telephone_add.png", alt: 'Assign to Service', title: 'Assign to Service'}).observe('click', this._showServicesPopup.bind(this, oCDR.Id, statusCell));

		
		}
		
	
			
			var	oTR	=	$T.tr(
							$T.td(oCDR.Id),
							$T.td(oCDR.Time),
							$T.td(oCDR.Cost),
							statusCell,
							$T.td({class : "followup-list-all-action-icons"},assign, writeOff)
							//$T.td({class : "followup-list-all-action-icons"},writeOff, assign, viewDetails)
						);
			

			
			return oTR;
		
	},
	
	
		_close : function ()
	{
		this.hide();
		//this._fnCallback();
	
	}
	});