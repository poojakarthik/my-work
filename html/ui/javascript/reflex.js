
/* Reflex JS 'Namespace' */
Reflex	= {};

// Function.construct
/*Function.construct	= function (fnConstructor, aArguments) {
	var	fnFakeConstructor	= function(){
			fnConstructor.apply(this, $A(aArguments))
		};
	fnFakeConstructor.prototype	= fnConstructor.prototype;
	return new fnFakeConstructor();
};*/

Function.prototype.construct	= function () {
	var	self				= this,
		aArguments			= $A(arguments),
		fnFakeConstructor	= function () {
			self.apply(this, aArguments)
		};
	fnFakeConstructor.prototype	= this.prototype;
	return new fnFakeConstructor();
};

Function.prototype.constructApply	= function (aArguments) {
	return this.construct.apply(this, aArguments);
};
