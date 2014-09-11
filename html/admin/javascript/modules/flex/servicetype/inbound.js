"use strict";

var Create = require('flex/servicetype/inbound/component/create');
return {
	identifierPattern: '^(13\\d{4}|1[38]00\\d{6})$',

	getCreateNode: function () {
		return (new Create()).getNode();
	}
};