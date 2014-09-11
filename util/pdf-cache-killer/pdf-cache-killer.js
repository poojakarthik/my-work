
// Arguments
var	ARGUMENTS	= require('optimist').
			options({
				'd'	: {
					alias		: 'days-offset',
					'default'	: 30
				}
			}).
			demand(1).
			argv;

console.log(ARGUMENTS);

// Execute
(function (sPath, iDaysOffset) {
	var	fs	= require('fs');

	// Look into sPath for PDFs to ba-lete
	fs.readdir(sPath, function (ERROR, aFiles) {

		var	oInvoiceRunPaths = {};

		// This should be a list of directories representing Invoice Runs
		aFiles.forEach(function (mValue, iIndex) {
			oInvoiceRunPaths[mValue] = null;

		});

	});
})(ARGUMENTS._[0], ARGUMENTS.d);
