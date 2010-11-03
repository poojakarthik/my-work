
var Popup_CDR	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iStatus, fCallback)
	{
		
			$super(80);	
			this._fCallback = fCallback;
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
		else if (!oResponse.Success)
		{
		
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Retrieval Error');
		
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
														$T.th('CDR ID'),
														$T.th('Start Date/Time'),
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
													this._bulkAddButton = $T.button({class: 'icon-button'},
													 $T.img({src: '../admin/img/template/telephone_add.png', alt: '', title: 'Close'}),
													$T.span('Add all to Service')
													).observe('click', this._bulkAssign.bind(this, false, false)),
													this._bulkWriteOffButton =$T.button({class: 'icon-button'},
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
			this._refreshDataTable(oResponse.aRecords.CDRs);
			
			
			this.setTitle('Delinquent CDRs');
			
			this.addCloseButton(this._close.bind(this));
			this.setContent(this._oContentDiv);
			this._oLoadingPopup.hide();
				
			
			this.display();		
		}
		
		
							
					
		
	},
	
	_close: function()
	{
		this._fCallback();
		this.hide();
	
	
	},
	
	_getCurrentStatusBreakDown: function ()
	{
		
		var keys = Object.keys(this._oData.CDRs);
		var oResult = {'delinquent':0, 'writeoff': 0, 'assigned': 0};
			for (var i = 0;i<keys.length;i++)
			{				
				this._oData.CDRs[keys[i]].StatusId ==107?oResult.delinquent++:this._oData.CDRs[keys[i]].StatusId ==203?oResult.writeoff++:oResult.assigned++;
				
			}
	
		return oResult;
	
	},
	
	_bulkWriteOff : function (bConfirm, oResponse)
	{		
		if (!oResponse)
		{
			if (!bConfirm)
			{
			
					var oStatusBreakDown = this._getCurrentStatusBreakDown();
					var sWriteOffMessage = '';
					var sAssignedMessage = '';
					var sDelinquentMessage = oStatusBreakDown.delinquent + " CDRs will be written off";
					if (oStatusBreakDown.writeoff>0)
					{
						sWriteOffMessage = oStatusBreakDown.writeoff + " CDRs were written off already. ";
					}
					
					if (oStatusBreakDown.assigned>0)
					{
						sAssignedMessage = oStatusBreakDown.assigned + " CDRs were assigned to a service already. ";
					
					}
					
					if (sAssignedMessage !='' || sWriteOffMessage!='')
					{
					
					sDelinquentMessage = sDelinquentMessage + " (" + sAssignedMessage + sWriteOffMessage + ").";
					
					}
					sDelinquentMessage = sDelinquentMessage + " Do you wish to continue?";
			
						Reflex_Popup.yesNoCancel(
													sDelinquentMessage,
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
				
				this._oLoadingPopup.display();
				var fnRequest     = jQuery.json.jsonFunction(this._bulkWriteOff.bind(this, true), null, 'CDR', 'bulkWriteOffForFNN');
				fnRequest(this._sStart, this._sEnd, this._sFNN, this._iCarrier, this._iServiceType);		
				
			}
		}
		else if (!oResponse.Success)
		{
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Write Off Error');
		
		}
		else
		{
			Reflex_Popup.alert('All CDRs for FNN ' + this._sFNN + ' have been written off succesfully');
			this._oLoadingPopup.hide();
			this._refreshDataSetAndDisplay(false);
			//this._refreshDataTable(oResponse.aData);
			
			
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
				var fnRequest     = jQuery.json.jsonFunction(this._bulkAssign.bind(this,iServiceId), null, 'CDR', 'BulkAssignCDRsToServices');
				fnRequest(this._sFNN,this._iCarrier,   this._iServiceType,this._sStart, this._sEnd, iServiceId);
			}
		
		}
		else if (!oResponse.Success)
		{
			
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Assignment Error');
		
		}
		else
		{			
			this._refreshDataSetAndDisplay(false);
			//this._refreshDataTable(oResponse.aData);
			Reflex_Popup.alert('All CDRs were assigned succesfully');
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
			if (!oResponse.Success)
			{
				this._oLoadingPopup.hide();
				Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CSV Error');
			
			}
			else
			{
			sFilename	= oResponse.FileName.replace(/\//g, "\\");
			window.location	= 'reflex.php/CDR/DownloadCSV/' + encodeURIComponent(sFilename);
			this._oLoadingPopup.hide();
			}
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
		else if (!oResponse.Success)
		{
			
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Assignment Error');
		
		}
		else
		{			
			Reflex_Popup.alert('CDR ' +  iCDRId + ' has been successfully assigned');
			this._refreshDataSetAndDisplay(false, false);		
		}
		
	
	
	},
	
	_writeOff : function (td, iId, bConfirm, oResponse)
	{
		
		
		if (!oResponse)
		{
			if (!bConfirm)
			{
						Reflex_Popup.yesNoCancel(
													"This will set the status for CDR with ID: " +  iId + " to 'Delinquent Usage - Written Off'. Is that what you want to do?",
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
		else if (!oResponse.Success)
		{
			
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Write Off Error');
		
		}
		else
		{
			this._oLoadingPopup.hide();
			Reflex_Popup.alert('CDR ' +  iId + 'has been written off.');
			//td.innerHTML = 'Delinquent Usage - Written Off';
			this._refreshDataSetAndDisplay(false, false);
		}
	
	},
	
	_refreshDataSetAndDisplay: function(bShowOnlyDelinquents, oResponse)
	{
		
		if (!oResponse)
		{
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._refreshDataSetAndDisplay.bind(this, bShowOnlyDelinquents), this._refreshDataSetAndDisplay.bind(this), 'CDR', 'GetStatusInfoForCDRs');
			fnRequest(Object.keys(this._oData.CDRs), bShowOnlyDelinquents);
		
		}
		else if (!oResponse.Success)
		{
			
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Screen Refresh Error');
		
		}
		else
		{
			this._oLoadingPopup.hide();
			this._oData.CDRs	= oResponse.aData;
			this._refreshDataTable(oResponse.aData);
		
		
		}
	
	},
	
	
		_refreshDataTable: function (oData)
	{
		
		//this._fCallback();
		while(this._tBody.childElementCount>0)
		{
		 this._tBody.deleteRow(this._tBody.childElementCount-1);

		}	


		//var keys = Object.keys(oData);	

		this._bulkAddButton.disabled = true; 
		this._bulkWriteOffButton.disabled = true;
		
		var keys = Object.keys(oData);
			
			for (var i = 0;i<keys.length;i++)
			{				
				this._tBody.appendChild(this._createTableRow(oData[keys[i]], i+1));
				if (this._bulkAddButton.disabled && oData[keys[i]].StatusId == 107)
				{
					this._bulkAddButton.disabled = false; 
					this._bulkWriteOffButton.disabled = false;
				
				}
			}
		
		

	
	
	
	},
	
	_createTableRow	: function(oCDR, iCount)
	{
		

		
		var writeOff = "";
		var assign = "";
		//var viewDetails = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/magnifier.png", alt: 'Show Details', title: 'Show Details'}).observe('click', this._showCDRPopup.bind(this, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType));
		var statusCell = $T.td({class: 'status'}, oCDR.Status);
		if (oCDR.StatusId ==107)
		{
		 writeOff = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/delete.png", alt: 'Write Off', title: 'Write Off'}).observe('click', this._writeOff.bind(this, statusCell, oCDR.Id, false, false));
		 assign = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/telephone_add.png", alt: 'Assign to Service', title: 'Assign to Service'}).observe('click', this._showServicesPopup.bind(this, oCDR.Id, statusCell));

		
		}
		

		
			var	oTR	=	$T.tr(
							$T.td(oCDR.Id),
							$T.td(new Date(Date.parse(oCDR.Time.replace(/-/g, '/'))).$format('d/m/Y h:i:s' )),
							$T.td(parseFloat(oCDR.Cost).toFixed(2)),
							statusCell,
							$T.td({class : "followup-list-all-action-icons"},assign, writeOff)
							//$T.td({class : "followup-list-all-action-icons"},writeOff, assign, viewDetails)
						);
			

			
			return oTR;

		
	},
	
	
	
	});