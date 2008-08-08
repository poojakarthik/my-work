var Reflex_Popup = Class.create();

// Static class variables are defined here
Object.extend(Reflex_Popup, {
	
	collection	: null,
	
	// Stores the current z index of the overlay div
	intOverlayZIndex : null
	
	// Stores the 

});

Object.extend(Reflex_Popup.prototype, {

	overlay			: null,
	container		: null,
	titlePane		: null,
	titleButtonPane	: null,
	contentPane		: null,
	footerPane		: null,

	// intWidth should be specified in units of em
	initialize: function(intWidth)
	{
		// Build Overlay
		this.overlay = document.createElement('div');
		this.overlay.className = 'reflex-popup-overlay';

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

	display: function(where)
	{
		if (!where)
		{
			where = document.body;
		}
		
		// Add the overlay div
		where.appendChild(this.overlay);
		
		this.container.style.visibility = "hidden";
		
		// set the top of the popup to the body.scrollTop, so that it doesn't move the page when it is added to it
		this.container.style.top = document.body.scrollTop + "px";
		where.appendChild(this.container);

		// Centre the popup
		this.container.style.left	= (((window.innerWidth / 2) - (this.container.offsetWidth / 2)) + document.body.scrollLeft) + "px";
		this.container.style.top	= (((window.innerHeight / 2) - (this.container.offsetHeight / 2)) + document.body.scrollTop) + "px";
		this.container.style.visibility = "visible";
		
	},

	hide: function()
	{
		if (this.overlay.parentNode)
		{
			this.overlay.parentNode.removeChild(this.overlay);
		}
		
		if (this.container.parentNode)
		{
			this.container.parentNode.removeChild(this.container);
		}
		
	}
});
