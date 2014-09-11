"use strict";

var Create = require('flex/servicetype/landline/component/create');
return {
	identifierPattern: '^0[2378]\\d{8}$',

	getCreateNode: function () {
		return (new Create()).getNode();
	}
};