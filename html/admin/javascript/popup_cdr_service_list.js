
var Popup_CDR_Service_List	= Class.create(Reflex_Popup,
{
	initialize	: function($super,  fCallback, strFNN, intServiceType)
	{
			$super(60);
			this._oLoadingPopup	= new Reflex_Popup.Loading();
			//this._iCDRId = iCDRId;
			this._fCallback = fCallback;
this._sFNN = strFNN;
this._iServiceType = intServiceType;
			this._getData();
	},

	_getData: function(oResponse)
	{

		if ( !oResponse)
		{
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._getData.bind(this), null, 'CDR', 'getPossibleOwnersForFNN');
			fnRequest(this._sFNN, this._iServiceType);
		}
		else
		{

			if (oResponse.aData.length>0)
			{
				this._aServiceList = oResponse.aData;
				this._buildUI();
			}
			else
			{
				Reflex_Popup.alert('There are no Services in Flex for FNN ' + this._sFNN);
				this._oLoadingPopup.hide();
			}

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
														$T.th('FNN'),
														$T.th('Created On'),
														$T.th('Closed On'),
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
							$T.td(oService.FNN),
							$T.td(oService.CreatedOn),
							$T.td(oService.ClosedOn),
							$T.td(button)
							//$T.td({class : "followup-list-all-action-icons"},assign, writeOff)
							//$T.td({class : "followup-list-all-action-icons"},writeOff, assign, viewDetails)
						);


			//oTR.observe('click', this._callBack.bind(this, oService.Id));
			return oTR;

	}
	});