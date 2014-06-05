"use strict";

var Create = require('flex/servicetype/iftel/email/component/create');
return {
	getCreateNode: function () {
		return (new Create()).getNode();
	}
};