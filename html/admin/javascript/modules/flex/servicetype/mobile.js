"use strict";

var Create = require('flex/servicetype/mobile/component/create');
return {
	identifierPattern: '^04\\d{8}$',

	getCreateNode: function () {
		return (new Create()).getNode();
	}
};