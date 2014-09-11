
var Popup_Destination_Import_Manual	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super, iDestinationContext, oCarrierDestination, cOnSelectCallback)
	{
		//debugger;
		$super(40);
		this.setTitle('Destination Search');
		this.addCloseButton();
		
		this._iDestinationContext	= (!iDestinationContext && iDestinationContext !== 0) ? null : parseInt(iDestinationContext, 10);
		this._oCarrierDestination	= oCarrierDestination;
		this._cOnSelectCallback		= (typeof cOnSelectCallback === 'function') ? cOnSelectCallback : null;
		this._buildContent();
	},
	
	_buildContent	: function()
	{
		this._oDatasetAjax	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {sObject:'Destination', sMethod:'SearchDestinationContext'});
		
		var	oSearchFieldConfig	= {
			sLabel					: 'Search',
			oDatasetAjax			: this._oDatasetAjax,
			sDisplayValueProperty	: 'Description',
			oColumnProperties		: {'Description':{},'Code':{}},
			iResultLimit			: 10,
			sResultPaneClass		: 'destination-import-manual-results',
			
			mVisible					: true,
			mEditable					: true,
			mMandatory					: true,
			bDisableValidationStyling	: true
		}
		this._oSearchField	= Control_Field.factory('text_ajax', oSearchFieldConfig);
		this._oSearchField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		var	oSearchFieldFilter	= this._oSearchField.getFilter();
		oSearchFieldFilter.addFilter(Popup_Destination_Import_Manual.FILTER_FIELD_DESTINATION_CONTEXT, {iType: Filter.FILTER_TYPE_VALUE});
		oSearchFieldFilter.setFilterValue(Popup_Destination_Import_Manual.FILTER_FIELD_DESTINATION_CONTEXT, this._iDestinationContext);
		
		this.setContent(
			$T.div({class:'destination-import-manual'},
				$T.div({class:'section'},
					$T.div({class:'section-content'},
						/*$T.div(
							$T.label('Search')
						),*/
						$T.div({class:'destination-import-manual-search'},
							this._oSearchField.getElement()
						)
					)
				)
			)
		);
		
		this.oCancelButton	= $T.button(
								$T.img({class:'icon',src:'../admin/img/template/delete.png'}),
								$T.span('Cancel')
							);
		this.oCancelButton.observe('click', this.hide.bind(this));
		
		this.oOKButton	= $T.button(
								$T.img({class:'icon',src:'../admin/img/template/tick.png'}),
								$T.span('OK')
							);
		this.oOKButton.observe('click', this._onOKClick.bind(this));
		this.setFooterButtons([this.oOKButton, this.oCancelButton], true);
	},
	
	_onOKClick	: function()
	{
		// Get the selected Destination
		var	oDestination	= this._oSearchField.getValue();
		if (!oDestination)
		{
			Reflex_Popup.alert("Please choose a Destination");
			return false;
		}
		
		// Send back to main popup
		if (this._cOnSelectCallback)
		{
			this._cOnSelectCallback(oDestination);
		}
		
		// Hide this popup
		this.hide();
	}
});

Popup_Destination_Import_Manual.FILTER_FIELD_DESTINATION_CONTEXT	= 'destination_context_id';
