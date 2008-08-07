var Reflex_Popup = Class.create();

Object.extend(Reflex_Popup.prototype, {

	overlay: null,
	container: null,
	titlePane: null,
	titleButtonPane: null,
	contentPane: null,
	footerPane: null,

	initialize: function()
	{
		this.overlay = document.createElement('div');
		this.overlay.className = 'reflex-popup-overlay';

		this.container = document.createElement('div');
		this.container.className = 'reflex-popup';
		this.overlay.appendChild(this.container);

		var tb = document.createElement('div');
		tb.className = 'reflex-popup-title-bar';
		this.container.appendChild(tb);

		this.titlePane = document.createElement('div');
		this.titlePane.className = 'reflex-popup-title';
		tb.appendChild(this.titlePane);

		this.titleButtonPane = document.createElement('div');
		this.titleButtonPane.className = 'reflex-popup-title-bar-buttons';
		tb.appendChild(this.titleButtonPane);

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
		where.appendChild(this.overlay);
	},

	hide: function()
	{
		if (this.overlay.parentNode)
		{
			this.overlay.parentNode.removeChild(this.overlay);
		}
	}
});
