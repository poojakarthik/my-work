"use strict";

var H = require('fw/dom/factory');

return {
	identifierPattern: '\\S',

	getCreateNode: function () {
		return H.div({class: 'flex-servicetype-dialup-create'}, 'There are no special properties for Dialup services.');
	}
};