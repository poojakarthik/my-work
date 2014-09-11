
var Popup_Login	= Class.create(Reflex_Popup,
{
	initialize	: function($super, sHandler, sMethod, aParameters, fnOnSuccess, fnOnFailure)
	{
		$super(36);
		
		this._sHandler		= sHandler;
		this._sMethod		= sMethod;
		this._aParameters	= aParameters;
		this._fnOnSuccess	= fnOnSuccess;
		this._fnOnFailure	= fnOnFailure;
		
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		this._oContent	=	$T.div({class: 'popup-login'},
								$T.div({class: 'MsgNotice'},
									'Your session has expired. Please enter your login details'
								),
								$T.div({class: 'GroupedContent'},
									$T.table(
										$T.tbody(
											$T.tr(
												$T.td('User Name'),
												$T.td(
													$T.input({type: 'text'})
												)
											),
											$T.tr(
												$T.td('Password'),
												$T.td(
													$T.input({type:'password'})
												)
											)
										)
									)
								),
								$T.div({class:'reflex-popup-footer'},
									$T.button({class: 'icon-button'},
										$T.span('OK')
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Attach button events
		var aButtons	= this._oContent.select('button');
		aButtons[0].observe('click', this._login.bindAsEventListener(this));
		aButtons[1].observe('click', this.hide.bind(this));
		
		// Window keypress event
		document.body.observe('keypress', this._keyPress.bindAsEventListener(this));
		
		this.setTitle('Login');
		this.setContent(this._oContent);
		this.display();
	},
	
	_login	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request
			var sUsername	= this._oContent.select('input')[0].value;
			var sPassword	= this._oContent.select('input')[1].value;
			var fnLogin		=	jQuery.json.jsonFunction(
									this._login.bind(this, null),
									this._login.bind(this, null),
									'Login',
									''
								);
			fnLogin(sUsername, sPassword);
		}
		else if (oResponse.Success)
		{
			// Login Successful, perform the request that failed previously then hide this popup
			if (!this.fire('beforerecovery').isCancelled())
			{
				var fnRecovery	=	jQuery.json.jsonFunction(
										this._fnOnSuccess,
										this._fnOnFailure,
										this._sHandler,
										this._sMethod
									);
				fnRecovery.apply(null, this._aParameters);
			}
			
			this.hide();
		}
		else
		{
			// Error
			Reflex_Popup.alert('Login failed, please try again');
		}
	},
	
	_keyPress	: function(oEvent)
	{
		if (oEvent.keyCode == 13)
		{
			this._login(null);
			oEvent.stop();
		}
	}
});

Reflex.mixin(Popup_Login, Observable);
Reflex.mixin(Popup_Login, Pluginable);
