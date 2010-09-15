
var Popup_Correspondence_Data	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, iCorrespondenceId)
	{
		$super(50);
		
		this._iId	= iCorrespondenceId;
		
		this._oLoading	= new Reflex_Popup.Loading();
		this._oLoading.display();
		
		this._buildUI();
	},
	
	_buildUI	: function(aData, aAdditionalColumns)
	{
		if (!aData)
		{
			// Get data
			Correspondence.getForId(this._iId, this._buildUI.bind(this));
		}
		else
		{
			this._oLoading.hide();
			delete this._oLoading;
			
			// Default columns
			var oTBody	= $T.tbody();
			
			for (var sProperty in Popup_Correspondence_Data.COLUMNS)
			{
				var mValue	= aData[sProperty];
				
				// Certain properties have extra (not included in the raw correspondence data) information
				switch (sProperty)
				{
					case 'customer_group_id':
						// Customer Group name
						mValue	= mValue.toString() + ' (' + aData.customer_group_name + ')';
						break;
					case 'correspondence_delivery_method_id':
						// Correspondence Delivery Method name
						mValue	= mValue.toString() + ' (' + aData.correspondence_delivery_method_name + ')';
						break;
				}
				
				oTBody.appendChild(
					$T.tr(
						$T.th(Popup_Correspondence_Data.COLUMNS[sProperty]),
						$T.td(mValue)
					)
				);
			}
			
			// Additional columns
			for (var i = 0; i < aAdditionalColumns.length; i++)
			{
				oTBody.appendChild(
					$T.tr(
						$T.th(aAdditionalColumns[i]),
						$T.td(aData[aAdditionalColumns[i]])
					)
				);
			}
			
			var oSection	= new Section();
			oSection.setContent(
				$T.table({class: 'reflex input'},
					oTBody
				)
			);
			
			this._oContent	=	$T.div({class: 'popup-correspondence-data'},
									oSection.getElement()
								);
			this.addCloseButton();
			this.setTitle('Correspondence Data');
			this.setContent(this._oContent);
			this.display();
		}
	}
});

// Static

Object.extend(Popup_Correspondence_Data, 
{
	COLUMNS	: 
	{
		id									: 'Correspondence Id',
		customer_group_id					: 'Customer Group',
		account_id							: 'Account Id',
		account_name						: 'Account Name',
		title								: 'Addressee Title',
		first_name							: 'Addressee First Name',
		last_name							: 'Addressee Last Name',
		address_line_1						: 'Address Line 1',
		address_line_2						: 'Address Line 2',
		suburb								: 'Suburb',
		postcode							: 'Post Code',
		state								: 'State',
		email								: 'Email Address',
		mobile								: 'Mobile',
		landline							: 'Landline',
		correspondence_delivery_method_id	: 'Delivery Method'
	}
});