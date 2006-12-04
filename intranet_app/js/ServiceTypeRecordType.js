	
	var ServiceTypeCombo = document.getElementById ("ServiceType");
	var RecordTypeCombo = document.getElementById ("RecordType");
	
	var RecordTypes = new Array ();
	
	RecordTypes [0] = Array ();
	RecordTypes [0][0] = new Option ("", "");
	
	window.addEventListener (
		"load",
		function ()
		{
			ServiceTypeCombo.onchange = function (e) {
				for (var i=RecordTypeCombo.options.length - 1; i >= 0; i--)
				{
					RecordTypeCombo.options [i] = null;
				}
				
				for (var i=0; i < RecordTypes [ServiceTypeCombo.selectedIndex].length; i++)
				{
					RecordTypeCombo.options [i] = new Option (
						RecordTypes [ServiceTypeCombo.selectedIndex][i].text,
						RecordTypes [ServiceTypeCombo.selectedIndex][i].value
					);
				}
				
				RecordTypeCombo.options [0].selected = true;
			}
		},
		true
	);
	
