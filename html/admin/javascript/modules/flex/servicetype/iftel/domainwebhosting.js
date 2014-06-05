"use strict";

var Create = require('flex/servicetype/iftel/domainwebhosting/component/create');
return {
	getCreateNode: function () {
		return (new Create()).getNode();
	}
};