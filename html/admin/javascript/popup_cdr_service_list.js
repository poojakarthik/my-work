
var Popup_CDR_Service_List	= Class.create(Reflex_Popup, 
{
	initialize	: function($super,  fCallback, aServiceList, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType)
	{
			$super(50);		
			this._oLoadingPopup	= new Reflex_Popup.Loading();
			//this._iCDRId = iCDRId;
			this._fCallback = fCallback;
			if (aServiceList)
			{
			this._aServiceList = aServiceList;
			this._buildUI();
			}
			else
			{
				this._sStart = strStartDate;
				this._sEnd = strEndDate;
				this._sFNN = strFNN;
				this._iCarrier = intCarrier;
				this._iServiceType = intServiceType;				
				this._getData();
			
			}
			
			
		
	},
	
	_getData: function(oResponse)
	{			
	
		if ( !oResponse)
		{
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._getData.bind(this), null, 'CDR', 'GetDelinquentCDRs');
			fnRequest(this._sStart,this._sEnd, this._sFNN, this._iCarrier, this._iServiceType);
		}
		else
		{
			this._aServiceList = oResponse.aRecords.Services;
			this._buildUI();			
		}	
	
	},
	
	_buildUI	: function()
	{		
					this._oContentDiv 	=  $T.div({class: 'delinquent-service-content'},
											 $T.table({class: 'reflex highlight-rows'},
												$T.thead(
													// Column headings
													$T.tr(
														$T.th('Account'),
														$T.th('Name'),
														$T.th('Status'),
														$T.th('Created'),
														$T.th('Service ID'),
														$T.th('')
													)
													
												),
												this._tBody = $T.tbody({class: 'alternating'}
													
												)
											)
										);
										
		
			var keys = Object.keys(this._aServiceList);
			
			for (var i = 0;i<keys.length;i++)
			{				
				this._tBody.appendChild(this._createTableRow(this._aServiceList[keys[i]], i+1));
			}
			
			
			this.setTitle('Possible Services');
			this.addCloseButton();
			this.setContent(this._oContentDiv);
			this._oLoadingPopup.hide();
			this.display();		
			
					
		
	},
	
	_callBack: function (iServiceId)
	{
		
		this._fCallback(iServiceId);
			this.hide();
	},
	
	_createTableRow	: function(oService)
	{			
			 var accountLink = document.createElement("A");
			 //set the href using rowIndex (+1 since 0-indexed)
   accountLink.href = 'http://localhost/flex/html/admin/flex.php/Account/Overview/?Account.Id=' + oService.Account ;

   //set the inner text using current cell's inner text
   accountLink.innerHTML = oService.Account;
		var button = $T.button({class: 'icon-button'},
																
																$T.span('Select')
																	).observe('click', this._callBack.bind(this, oService.Id))
													
			var	oTR	=	$T.tr(
							
							$T.td(accountLink),
							$T.td(oService.AccountName),							
							$T.td(oService.DateRange),
							$T.td(oService.CreatedOn),
							$T.td(oService.Id),
							$T.td(button)
							//$T.td({class : "followup-list-all-action-icons"},assign, writeOff)
							//$T.td({class : "followup-list-all-action-icons"},writeOff, assign, viewDetails)
						);
			

			//oTR.observe('click', this._callBack.bind(this, oService.Id));
			return oTR;
		
	}
	});