"use strict";

var Create = require('flex/servicetype/iftel/ethernet/component/create');
return {
	getCreateNode: function () {
		return (new Create()).getNode();
	}
};