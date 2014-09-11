"use strict";

var Create = require('flex/servicetype/iftel/businessconnect/component/create');
return {
	getCreateNode: function () {
		return (new Create()).getNode();
	}
};