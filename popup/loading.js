var Class = require('fw/class');

var self = new Class({
	extends : require('../popup'),

	loading: null,

	construct : function () {
		this._super(20);
		var loading = this.loading = document.createElement('div');
		loading.className = "reflex-loading-image";
		loading.style.height = '8em';
		var p = document.createElement('p');
		loading.appendChild(p);
		p.appendChild(document.createTextNode('Loading...'));
		loading.appendChild(document.createElement('br'));
		p = document.createElement('p');
		loading.appendChild(p);
		p.appendChild(document.createTextNode('Please wait.'));
		
		this.setTitle('Loading...');
		this.setContent(this.loading);
		this.setHeaderButtons([]);
		this.setFooterButtons([]);
	}
});

return self;