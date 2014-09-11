"use strict";

var Create = require('flex/servicetype/iftel/engin/voip/component/create');
return {
	getCreateNode: function () {
		return (new Create()).getNode();
	}
};