var Reflex_Popup = Class.create();

// Static class variables are defined here
Object.extend(Reflex_Popup, {
	
	// Stores the current z index of the overlay div
	intOverlayZIndex : null,
	
	// Stores the container and opacity divs
	overlay: document.createElement('div'),
	opaquePane: document.createElement('div'),

	display: function(popup)
	{
		// If the overlay only contains the opaque pane, we need to add the overlay to the page
		if (!Reflex_Popup.overlay.select('div.reflex-popup').length)
		{
			document.body.appendChild(Reflex_Popup.overlay);
			if (document.all)
			{
				Reflex_Popup.fakeFixedPosition();
			}
		}
		// We can now add the popup container to the overlay
		Reflex_Popup.overlay.appendChild(Reflex_Popup.opaquePane);
		Reflex_Popup.overlay.appendChild(popup.container);
		Reflex_Popup.position(popup.container);
	},

	hide: function(popup)
	{
		// If the popup is in the page, remove it
		popup.container.remove();
		//alert("Container Removed");
		
		// If the overlay only contains the opaque pane, remove the overlay from the page
		var aPopups	= Reflex_Popup.overlay.select('div.reflex-popup');
		if (!aPopups.length)
		{
			Reflex_Popup.overlay.remove();
		}
		// Otherwise we can re-append the second to last element (the previous popup in the stack)
		else
		{
			//alert(Reflex_Popup.overlay.childNodes.length);
			//Reflex_Popup.overlay.appendChild(aPopups.last());
			Reflex_Popup.overlay.insertBefore(Reflex_Popup.opaquePane, aPopups.last());
		}
	},

	position: function(childNode)
	{
		// Centre the popup
		childNode.style.left	= ((Reflex_Popup.overlay.clientWidth - childNode.clientWidth)/2)+"px";
		childNode.style.top		= ((Reflex_Popup.overlay.clientHeight - childNode.clientHeight)/2)+"px";
	},


	initFakeFixedPosition: function()
	{
		Reflex_Popup.overlay.style.position = 'absolute';
		Event.observe(window, 'resize', Reflex_Popup.fakeFixedPosition);
		Event.observe(window, 'scroll', Reflex_Popup.fakeFixedPosition);
	},
	fakeFixedPosition: function()
	{
		if (Reflex_Popup.overlay)
		{
			Reflex_Popup.overlay.style.top = '' + document.body.scrollTop + 'px';
			Reflex_Popup.overlay.style.left = '' + document.body.scrollLeft + 'px';
		}
	},
	
	alert	: function(mContent, oConfig)
	{
		// Config Defaults
		oConfig	 				= (typeof oConfig !== 'undefined') 		? oConfig 					: {};
		oConfig.sTitle 			= oConfig.sTitle 						? oConfig.sTitle 			: Reflex_Popup.DEFAULT_ALERT_TITLE;
		oConfig.iWidth 			= (parseInt(oConfig.iWidth)) 			? oConfig.iWidth			: Reflex_Popup.DEFAULT_ALERT_WIDTH;
		oConfig.sButtonLabel 	= oConfig.sButtonLabel 					? oConfig.sButtonLabel		: Reflex_Popup.DEFAULT_ALERT_BUTTON_LABEL;
		oConfig.fnOnClose 		= typeof oConfig.fnClose === 'function' ? oConfig.fnClose 			: null;
		oConfig.sIconSource 	= oConfig.sIconSource 					? oConfig.sIconSource 		: Reflex_Popup.DEFAULT_ALERT_ICON_SOURCE;
		
		var oPopup	= new Reflex_Popup(oConfig.iWidth);
		
		oPopup.setTitle(oConfig.sTitle);
		oPopup.setIcon(oConfig.sIconSource);
			
		var oCloseButton = 	$T.button(
								oConfig.sButtonLabel.escapeHTML()
							);
		
		// Close function
		var fnOnClose = function(oPopup, fnCallback)
		{
			oPopup.hide();
			
			if( typeof oConfig.fnClose === 'function')
			{
				fnCallback();
			}
		}
		
		oCloseButton.observe('click', fnOnClose.curry(oPopup, oConfig.fnOnClose));
		oPopup.setFooterButtons([oCloseButton], true);
		
		// Apply padding if text only content
		if (typeof mContent == 'string')
		{
			mContent = 	$T.div({class: 'alert-content'},
							mContent
						);
		}
		
		oPopup.setContent(mContent);
		oPopup.display();
	},
	
	yesNoCancel	: function(mContent, oConfig)
	{
		// Config Defaults
		oConfig	 					= (typeof oConfig !== 'undefined') 			? oConfig 					: {};
		oConfig.sTitle 				= oConfig.sTitle 							? oConfig.sTitle 			: Reflex_Popup.DEFAULT_YESNOCANCEL_TITLE;
		oConfig.iWidth 				= (parseInt(oConfig.iWidth)) 				? oConfig.iWidth			: Reflex_Popup.DEFAULT_ALERT_WIDTH;
		oConfig.sIconSource 		= oConfig.sIconSource 						? oConfig.sIconSource 		: Reflex_Popup.DEFAULT_ALERT_ICON_SOURCE;
		oConfig.sYesLabel 			= oConfig.sYesLabel 						? oConfig.sYesLabel			: Reflex_Popup.DEFAULT_YESNOCANCEL_YES_LABEL;
		oConfig.sNoLabel 			= oConfig.sNoLabel 							? oConfig.sNoLabel			: Reflex_Popup.DEFAULT_YESNOCANCEL_NO_LABEL;
		oConfig.sCancelLabel 		= oConfig.sCancelLabel 						? oConfig.sCancelLabel		: Reflex_Popup.DEFAULT_YESNOCANCEL_CANCEL_LABEL;
		oConfig.bShowCancel 		= oConfig.bShowCancel 						? oConfig.bShowCancel		: false;
		oConfig.fnOnYes 			= typeof oConfig.fnOnYes === 'function' 	? oConfig.fnOnYes 			: null;
		oConfig.fnOnNo 				= typeof oConfig.fnOnNo === 'function' 		? oConfig.fnOnNo 			: null;
		oConfig.fnOnCancel 			= typeof oConfig.fnOnCancel === 'function' 	? oConfig.fnOnCancel 		: null;
		oConfig.bOverrideStyle		= oConfig.bOverrideStyle					? oConfig.bOverrideStyle	: false; 
		oConfig.sYesIconSource		= oConfig.sYesIconSource 					? oConfig.sYesIconSource	: null;
		oConfig.sNoIconSource		= oConfig.sNoIconSource 					? oConfig.sNoIconSource		: null;
		oConfig.sCancelIconSource	= oConfig.sCancelIconSource 				? oConfig.sCancelIconSource	: null;
		
		var oPopup	= new Reflex_Popup(oConfig.iWidth);
		oPopup.setTitle(oConfig.sTitle);
		oPopup.setIcon(oConfig.sIconSource);
		
		// Create footer buttons
		var aFooterButtons	= [];
		
		// Yes
		if (oConfig.sYesIconSource)
		{
			var oYesButton 	= 	$T.button({class: 'icon-button'},
									$T.img({src: oConfig.sYesIconSource}),
									$T.span(oConfig.sYesLabel.escapeHTML())
								);
		}
		else
		{
			var oYesButton 	= 	$T.button(
									oConfig.sYesLabel.escapeHTML()
								);
		}
		
		// No
		if (oConfig.sNoIconSource)
		{
			var oNoButton 	= 	$T.button({class: 'icon-button'},
									$T.img({src: oConfig.sNoIconSource}),
									$T.span(oConfig.sNoLabel.escapeHTML())
								);
		}
		else
		{
			var oNoButton 	= 	$T.button(
									oConfig.sNoLabel.escapeHTML()
								);
		}
		
		aFooterButtons.push(oYesButton);
		aFooterButtons.push(oNoButton);
		
		// Handler for all button events
		var fnClosePopup = function(oPopup, fnCallback)
		{
			oPopup.hide();
			
			if( typeof fnCallback === 'function')
			{
				fnCallback();
			}
		}
		
		oYesButton.observe('click', fnClosePopup.curry(oPopup, oConfig.fnOnYes));
		oNoButton.observe('click', fnClosePopup.curry(oPopup, oConfig.fnOnNo));
		
		// Add the cancel button and it's event handler if specified
		if (oConfig.bShowCancel)
		{
			if (oConfig.sCancelIconSource)
			{
				var oCancelButton	= 	$T.button({class: 'icon-button'},
											$T.img({src: oConfig.sCancelIconSource}),
											$T.span(oConfig.sCancelLabel.escapeHTML())
										);
			}
			else
			{
				var oCancelButton	= 	$T.button(
											oConfig.sCancelLabel.escapeHTML()
										);
			}
			
			aFooterButtons.push(oCancelButton);
			oCancelButton.observe('click', fnClosePopup.curry(oPopup, oConfig.fnOnCancel));
		}
		
		oPopup.setFooterButtons(aFooterButtons, true);
		
		// Apply padding if text only content
		if ((typeof mContent == 'string') || !oConfig.bOverrideStyle)
		{
			mContent = 	$T.div({class: 'alert-content'},
							mContent
						);
		}
		
		oPopup.setContent(mContent);
		oPopup.display();
	},
	
	debug	: function(sText)
	{
		Reflex_Popup.alert(
			$T.textarea({class: 'popup-debug-text'},
				sText
			), 
			{
				sTitle			: 'Debug',
				iWidth			: 61, 
				bOverrideStyles	: false
			}
		);
	}
});

// Alert constants
Reflex_Popup.DEFAULT_ALERT_WIDTH 		= 40;
Reflex_Popup.DEFAULT_ALERT_TITLE 		= 'Notification';
Reflex_Popup.DEFAULT_ALERT_BUTTON_LABEL = 'OK';
Reflex_Popup.DEFAULT_ALERT_ICON_SOURCE 	= '../admin/img/template/MsgNotice.png';

// Yes/No/Cancel constants
Reflex_Popup.DEFAULT_YESNOCANCEL_TITLE			= 'Confirmation';
Reflex_Popup.DEFAULT_YESNOCANCEL_YES_LABEL		= 'Yes';
Reflex_Popup.DEFAULT_YESNOCANCEL_NO_LABEL		= 'No';
Reflex_Popup.DEFAULT_YESNOCANCEL_CANCEL_LABEL	= 'Cancel';

Reflex_Popup.overlay.className = 'reflex-popup-overlay';
Reflex_Popup.opaquePane.className = 'reflex-popup-opaque';
Reflex_Popup.overlay.appendChild(Reflex_Popup.opaquePane);

if (document.all)
{
	Event.observe(window, 'load', Reflex_Popup.initFakeFixedPosition);
}

Object.extend(Reflex_Popup.prototype, {

	container		: null,
	titlePane		: null,
	titleButtonPane	: null,
	contentPane		: null,
	footerPane		: null,
	titleText		: null,
	icon			: null,

	// intWidth should be specified in units of em
	initialize: function(intWidth)
	{
		this.container = document.createElement('div');
		this.container.className = 'reflex-popup';
		this.container.style.width = intWidth + "em";
		

		var tb = document.createElement('div');
		tb.className = 'reflex-popup-title-bar';
		this.container.appendChild(tb);

		this.titlePane = document.createElement('div');
		this.titlePane.className = 'reflex-popup-title';
		tb.appendChild(this.titlePane);
		
		this.icon	= document.createElement('img');
		this.icon.className		= 'reflex-popup-title-icon';
		this.icon.style.display	= 'none';
		this.titlePane.appendChild(this.icon);
		
		this.titleText	= document.createElement('span');
		this.titleText.className	= 'reflex-popup-title-text';
		this.titlePane.appendChild(this.titleText);

		this.titleButtonPane = document.createElement('div');
		this.titleButtonPane.className = 'reflex-popup-title-bar-buttons';
		tb.appendChild(this.titleButtonPane);
		
		this.titlePaneClear = document.createElement('div');
		this.titlePaneClear.className = 'clear';
		tb.appendChild(this.titlePaneClear);
		

		this.contentPane = document.createElement('div');
		this.contentPane.className = 'reflex-popup-content';
		this.container.appendChild(this.contentPane);

		this.footerPane = document.createElement('div');
		this.footerPane.className = 'reflex-popup-footer';
		this.footerPane.style.display	= 'none';
		this.container.appendChild(this.footerPane);
		
		// Dragging the Title Bar
		Event.observe(tb, "mousedown", this.dragStart.bind(this));
		Event.observe(document.body, "mousemove", this.drag.bind(this));
		Event.observe(document.body, "mouseup", this.dragEnd.bind(this));
		Event.observe(document.body, "drag", this.dragCancel.bind(this));
		
		// Dragging the Content (only in special cases)
		Event.observe(this.container, 'mousedown', this.dragStartComplex.bind(this));
		Event.observe(document, 'keydown', this.dragStartComplex.bind(this));
		Event.observe(document, 'keyup', this.dragStartComplex.bind(this));
		
		this.KEYSTATES	= {};
		
		// Fancy FX init
		this.fx	=	{
						display	: null,
						hide	: null
					};
		this.container.style.opacity	= 0;
	},
	
	draggedFrom: null,
	
	dragStartComplex	: function(oEvent)
	{
		var	iKeyCode	= oEvent.which || oEvent.keyCode;
		if (oEvent.type == 'keyup')
		{
			this.KEYSTATES.CTRL	= (iKeyCode == Reflex_Popup.KEYS.CTRL)	? false	: !!this.KEYSTATES.CTRL;
			this.KEYSTATES.ALT	= (iKeyCode == Reflex_Popup.KEYS.ALT)	? false	: !!this.KEYSTATES.ALT;
		}
		else if (oEvent.type == 'keydown')
		{
			this.KEYSTATES.CTRL	= (iKeyCode == Reflex_Popup.KEYS.CTRL)	? true	: !!this.KEYSTATES.CTRL;
			this.KEYSTATES.ALT	= (iKeyCode == Reflex_Popup.KEYS.ALT)	? true	: !!this.KEYSTATES.ALT;
		}
		else if (oEvent.type == 'mousedown')
		{
			// Start the drag, if our hotkeys are currently depressed
			if (this.KEYSTATES.CTRL && this.KEYSTATES.ALT)
			{
				//debugger;
				this.dragStart(oEvent);
			}
		}
	},
	
	dragStart: function(event)
	{
		var pointer = Event.pointer(event ? event : window.event);
		var eventX = pointer.x;
		var eventY = pointer.y;
		this.draggedFrom = {
			originLeft: this.container.offsetLeft,
			originTop: this.container.offsetTop,
			eventX: eventX,
			eventY: eventY
		}
		this.drag(event);
	},
	
	drag: function(event)
	{
		if (this.draggedFrom == null) return;
		var pointer = Event.pointer(event ? event : window.event);
		var eventX = pointer.x;
		var eventY = pointer.y;
		this.container.style.left = (this.draggedFrom.originLeft + (eventX - this.draggedFrom.eventX)) + "px";
		this.container.style.top = (this.draggedFrom.originTop + (eventY - this.draggedFrom.eventY)) + "px";
	},
	
	dragEnd: function(event)
	{
		if (this.draggedFrom != null) this.drag(event);
		this.draggedFrom = null;
	},
	
	dragCancel: function(event)
	{
		this.draggedFrom = null;
	},

	addCloseButton: function(callback)
	{
		if (!callback)
		{
			callback = this.hide.bind(this);
		}
		var button = document.createElement('img');
		button.src = 'img/template/close.png';
		button.className = 'PopupBoxClose';
		this.titleButtonPane.appendChild(button);
		Event.observe(button, 'click', callback);
	},

	setTitle: function(title)
	{
		this.titleText.innerHTML = '';
		this.titleText.appendChild(document.createTextNode(title));
	},
	
	setTitleElement: function(elmTitle)
	{
		this.titleText.innerHTML = '';
		this.titleText.appendChild(elmTitle);
	},

	setContent: function(content)
	{
		if (content == null) 
		{
			content = '';
		}
		if (typeof content == 'string')
		{
			this.contentPane.innerHTML = content;
		}
		else
		{
			this.contentPane.innerHTML = '';
			this.contentPane.appendChild(content);
		}
	},

	setHeaderButtons: function(buttons)
	{
		this.titleButtonPane.innerHTML = '';
		for (var i = 0, l = buttons.length; i < l; i++)
		{
			this.titleButtonPane.appendChild(buttons[i]);
		}
	},

	setFooterButtons: function(buttons, bolCentred)
	{
		this.footerPane.innerHTML = '';
		for (var i = 0, l = buttons.length; i < l; i++)
		{
			this.footerPane.appendChild(buttons[i]);
		}
		
		this.footerPane.style.display	= (buttons.length) ? 'block' : 'none';
		
		this.footerPane.style.textAlign	= (bolCentred != undefined && bolCentred) ? 'center' : 'right';
	},
	
	setIcon	: function(strImageSource)
	{
		if (strImageSource)
		{
			this.icon.src	= strImageSource;
			this.icon.style.display	= 'inline';
		}
		else
		{
			this.icon.style.display	= 'none';
		}
	},

	recentre: function()
	{
		Reflex_Popup.position(this.container);
	},

	display: function()
	{
		if (this.fx.hide)
		{
			this.fx.hide.cancel();
		}
		Reflex_Popup.display(this);
		
		// Animate
		this.fx.display	= new Reflex_FX_Morph(this.container, {opacity: 1}, 0.1, 'ease-out');
		this.fx.display.start();
	},

	hide: function()
	{
		if (this.fx.display)
		{
			this.fx.display.cancel();
		}
		
		// Animate
		this.fx.hide	= new Reflex_FX_Morph(this.container, {opacity: 0}, 0.1, 'ease', Reflex_Popup.hide.bind(Reflex_Popup, this));
		this.fx.hide.start();
	}
});

Reflex_Popup.KEYS	= {
	SHIFT	: 16,
	CTRL	: 17,
	ALT		: 18
};

Reflex_Popup.Loading = Class.create();

Object.extend(Reflex_Popup.Loading, {

});


Object.extend(Reflex_Popup.Loading.prototype, Reflex_Popup.prototype);

Object.extend(Reflex_Popup.Loading.prototype, {
	parentInitialize: Reflex_Popup.prototype.initialize,
	
	loading: null,
	
	initialize: function(sMessage)
	{
		this.parentInitialize(20);
		
		var bShowTitle	= (sMessage ? true : false);
		sMessage		= sMessage ? String(sMessage) : 'Loading...';
		
		this.loading	= 	$T.div({class: 'reflex-loading-image'},
								$T.div(sMessage),
								$T.div('Please wait.')
							);
		this.loading.observe('dblclick', this._hideOverride.bind(this));
		
		// Hide the title bar
		this.titlePane.up().hide();
		
		this.setContent(this.loading);
		this.setHeaderButtons(new Array());
		this.setFooterButtons(new Array());
	},
	
	_hideOverride	: function(oEvent)
	{
		if (oEvent.ctrlKey)
		{
			this.hide();
		}
	}
});