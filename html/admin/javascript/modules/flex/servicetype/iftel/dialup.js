"use strict";

var Create = require('flex/servicetype/iftel/dialup/component/create');
return {
	getCreateNode: function () {
		return (new Create()).getNode();
	}
};