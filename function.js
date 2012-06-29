
// WARNING: Extends Native Function.prototype
//debugger;

// Allows a constructor function to be called with injected parameters a la Function.prototype.call
if (!Function.prototype.callAsConstructor) {
	Function.prototype.callAsConstructor	= function () {
		return Function.prototype.applyAsConstructor(this, arguments);
	};
}

// Allows a constructor function to be called with injected parameters a la Function.prototype.apply
if (!Function.prototype.applyAsConstructor) {
	Function.prototype.applyAsConstructor	= function (aArguments) {
		var	self				= this,
			WrappedConstructor	= function () {
				self.apply(this, aArguments)
			};
		WrappedConstructor.prototype	= this.prototype;
		return new WrappedConstructor();
	};
}
