//JS Framework popup classes and functionality

FW.Popup = Class.create();

// Static class variables are defined here
Object.extend(FW.Popup, {
	
	// Stores the current z index of the overlay div
	intOverlayZIndex : null,
	
	// Stores the container and opacity divs
	overlay: document.createElement('div'),
	opaquePane: document.createElement('div'),

	display: function(popup)
	{
		// If the overlay only contains the opaque pane, we need to add the overlay to the page
		if (FW.Popup.overlay.childNodes.length == 1)
		{
			document.body.appendChild(FW.Popup.overlay);
			if (document.all)
			{
				FW.Popup.fakeFixedPosition();
			}
		}
		// We can now add the popup container to the overlay
		FW.Popup.overlay.appendChild(popup.container);
		FW.Popup.position(popup.container);
	},

	hide: function(popup)
	{
		// If the popup is in the page, remove it
		if (popup.container.parentNode)
		{
			popup.container.parentNode.removeChild(popup.container);
		}
		// If the overlay only contains the opaque pane, remove the overlay from the page
		if (FW.Popup.overlay.childNodes.length == 1)
		{
			if (FW.Popup.overlay.parentNode)
			{
				FW.Popup.overlay.parentNode.removeChild(FW.Popup.overlay);
			}
		}
		// Otherwise we can re-append the second to last element (the previous popup in the stack)
		else
		{
			FW.Popup.overlay.appendChild(FW.Popup.overlay.childNodes[FW.Popup.overlay.childNodes.length - 2])
		}
	},

	position: function(childNode)
	{
		// Centre the popup
		childNode.style.left	= ((FW.Popup.overlay.clientWidth - childNode.clientWidth)/2)+"px";
		childNode.style.top		= ((FW.Popup.overlay.clientHeight - childNode.clientHeight)/2)+"px";
	},


	initFakeFixedPosition: function()
	{
		FW.Popup.overlay.style.position = 'absolute';
		Event.observe(window, 'resize', FW.Popup.fakeFixedPosition);
		Event.observe(window, 'scroll', FW.Popup.fakeFixedPosition);
	},
	fakeFixedPosition: function()
	{
		if (FW.Popup.overlay)
		{
			FW.Popup.overlay.style.top = '' + document.body.scrollTop + 'px';
			FW.Popup.overlay.style.left = '' + document.body.scrollLeft + 'px';
		}
	}
});

FW.Popup.overlay.className = 'reflex-popup-overlay';
FW.Popup.opaquePane.className = 'reflex-popup-opaque';
FW.Popup.overlay.appendChild(FW.Popup.opaquePane);

if (document.all)
{
	Event.observe(window, 'load', FW.Popup.initFakeFixedPosition);
}

Object.extend(FW.Popup.prototype, {

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
		
		Event.observe(tb, "mousedown", this.dragStart.bind(this));
		Event.observe(document.body, "mousemove", this.drag.bind(this));
		Event.observe(document.body, "mouseup", this.dragEnd.bind(this));
		Event.observe(document.body, "drag", this.dragCancel.bind(this));
	},
	
	draggedFrom: null,
	
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
		try
		{
			button.src = jQuery.json.baseURI + '/img/close.png';
		}
		catch(error)
		{
			button.src ='img/close.png';
		}
		button.className = 'reflex-popup-close-box';
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
		FW.Popup.position(this.container);
	},

	display: function()
	{
		FW.Popup.display(this);
	},

	hide: function()
	{
		FW.Popup.hide(this);
	}
});


FW.Popup.Loading = Class.create();

Object.extend(FW.Popup.Loading, {

});


Object.extend(FW.Popup.Loading.prototype, FW.Popup.prototype);

Object.extend(FW.Popup.Loading.prototype, {
	parentInitialize: FW.Popup.prototype.initialize,
	
	loading: null,
	
	initialize: function()
	{
		this.parentInitialize(20);
		var loading = this.loading = document.createElement('div');
		loading.className = "reflex-loading-image";
		loading.style.height = '8em';
		p = document.createElement('p');
		loading.appendChild(p);
		p.appendChild(document.createTextNode('Loading...'));
		loading.appendChild(document.createElement('br'));
		p = document.createElement('p');
		loading.appendChild(p);
		p.appendChild(document.createTextNode('Please wait.'));
		
		this.setTitle('Loading...');
		this.setContent(this.loading);
		this.setHeaderButtons(new Array());
		this.setFooterButtons(new Array());
	}
});


