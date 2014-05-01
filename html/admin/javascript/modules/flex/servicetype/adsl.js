"use strict";

var Create = require('flex/servicetype/adsl/component/create');
return {
	identifierPattern: '^0[2378]\\d{6}i$',

	getCreateNode: function () {
		return (new Create()).getNode();
	}
};