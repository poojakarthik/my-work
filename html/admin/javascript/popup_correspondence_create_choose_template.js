
var Popup_Correspondence_Create_Choose_Template	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(29);
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function()
	{
		var oTemplateSelect		= 	Control_Field.factory(
										'select', 
										{
											sLabel		: 'Template',
											mEditable	: true,
											mMandatory	: true,
											fnPopulate	: Correspondence_Template.getAllWithNonSystemSourcesAsSelectOptions
										}
									);
		oTemplateSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oTemplateSelect.disableValidationStyling();
		this._oTemplateSelect	= oTemplateSelect;

		this._oContent	=	$T.div({class: 'popup-correspondence-create-choose-template'},
								$T.div(
									oTemplateSelect.getElement()
								),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'}, 
										'OK'
									).observe('click', this._useTemplate.bind(this))
								)
							);
		
		this.setTitle('Create Correspondence - Choose a Template');
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
	},
	
	_useTemplate	: function()
	{
		var mTemplate	= this._oTemplateSelect.getElementValue();
		if (!mTemplate)
		{
			Reflex_Popup.alert('Please choose a Correspondence Template.', {sTitle: 'Reminder'});
			return;
		}
		
		new Popup_Correspondence_Create(parseInt(mTemplate));
		this.hide();
	}
});