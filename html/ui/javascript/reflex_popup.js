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
		if (Reflex_Popup.overlay.childNodes.length == 1)
		{
			document.body.appendChild(Reflex_Popup.overlay);
		}
		// We can now add the popup container to the overlay
		Reflex_Popup.overlay.appendChild(popup.container);
		Reflex_Popup.position(popup.container);
	},

	hide: function(popup)
	{
		// If the popup is in the page, remove it
		if (popup.container.parentNode)
		{
			popup.container.parentNode.removeChild(popup.container);
		}
		// If the overlay only contains the opaque pane, remove the overlay from the page
		if (Reflex_Popup.overlay.childNodes.length == 1)
		{
			if (Reflex_Popup.overlay.parentNode)
			{
				Reflex_Popup.overlay.parentNode.removeChild(Reflex_Popup.overlay);
			}
		}
		// Otherwise we can re-append the second to last element (the previous popup in the stack)
		else
		{
			Reflex_Popup.overlay.appendChild(Reflex_Popup.overlay.childNodes[Reflex_Popup.overlay.childNodes.length - 2])
		}
	},

	position: function(childNode)
	{
		// Centre the popup
		childNode.style.left	= ((Reflex_Popup.overlay.clientWidth - childNode.clientWidth)/2)+"px";
		childNode.style.top		= ((Reflex_Popup.overlay.clientHeight - childNode.clientHeight)/2)+"px";
	}
});

Reflex_Popup.overlay.className = 'reflex-popup-overlay';
Reflex_Popup.opaquePane.className = 'reflex-popup-opaque';
Reflex_Popup.overlay.appendChild(Reflex_Popup.opaquePane);

Object.extend(Reflex_Popup.prototype, {

	container		: null,
	titlePane		: null,
	titleButtonPane	: null,
	contentPane		: null,
	footerPane		: null,

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
		this.container.appendChild(this.footerPane);
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
		this.titlePane.innerHTML = '';
		this.titlePane.appendChild(document.createTextNode(title));
	},

	setContent: function(content)
	{
		this.contentPane.innerHTML = '';
		this.contentPane.appendChild(content);
	},

	setHeaderButtons: function(buttons)
	{
		this.titleButtonPane.innerHTML = '';
		for (var i = 0, l = buttons.length; i < l; i++)
		{
			this.titleButtonPane.appendChild(buttons[i]);
		}
	},

	setFooterButtons: function(buttons)
	{
		this.footerPane.innerHTML = '';
		for (var i = 0, l = buttons.length; i < l; i++)
		{
			this.footerPane.appendChild(buttons[i]);
		}
	},

	recentre: function()
	{
		Reflex_Popup.position(this.container);
	},

	display: function()
	{
		Reflex_Popup.display(this);
	},

	hide: function()
	{
		Reflex_Popup.hide(this);
	}
});
