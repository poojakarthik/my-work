var Reflex_Debug	= Class.create
({
	
});

Reflex_Debug.asHTML	= function(mDebug)
{
	var domTable	= document.createElement('table');
	var domTBody	= document.createElement('tbody');
	domTable.appendChild(domTBody);
	
	
	var domTypeTR		= document.createElement('tr');
	domTBody.appendChild(domTypeTR);
	var domTypeTH		= document.createElement('th');
	domTypeTH.innerHTML	= 'Type : '
	domTypeTR.appendChild(domTypeTH);
	var domTypeTD		= document.createElement('td');
	domTypeTR.appendChild(domTypeTD);
	
	var domValueTR			= document.createElement('tr');
	domTBody.appendChild(domValueTR);
	var domValueTH			= document.createElement('th');
	domValueTH.innerHTML	= 'Value : '
	domValueTR.appendChild(domValueTH);
	var domValueTD			= document.createElement('td');
	domValueTR.appendChild(domValueTD);
	
	if (Object.isArray(mDebug))
	{
		// Debug as an Array
		domTypeTD.innerHTML	= 'array (' + mDebug.length + ')';
		var domUL	= document.createElement('ul');
		domValueTD.appendChild(domUL);
		for (var i = 0; i < mDebug.length; i++)
		{
			var domChildLI		= document.createElement('li');
			domUL.appendChild(domChildLI);
			var domChildTable	= document.createElement('table');
			domChildLI.appendChild(domChildTable);
			var domChildTBody	= document.createElement('tbody');
			domChildTable.appendChild(domChildTBody);
			var domChildTR		= document.createElement('tr');
			domChildTBody.appendChild(domChildTR);
			
			var domKeyTH		= document.createElement('th');
			domKeyTH.innerHTML	= "["+i+"] => ";
			domChildTR.appendChild(domKeyTH);
			
			var domValueTD		= document.createElement('td');
			domValueTD.appendChild(Reflex_Debug.asHTML(mDebug[i]));
			domChildTR.appendChild(domValueTD);
		}
	}
	else
	{
		domTypeTD.innerHTML	= typeof mDebug;
		
		switch (typeof mDebug)
		{
			// Debug as an Object
			case 'object':
				mDebug	= {test1: 'value', test2: 'value2'};
				
				domTypeTD.innerHTML	+= '(' + mDebug.length + ')';
				
				alert("mDebug Keys: " + Object.keys(mDebug));
				
				var domUL	= document.createElement('ul');
				domValueTD.appendChild(domUL);
				for (mIndex in mDebug)
				{
					alert("Parsing " + mIndex + " in mDebug");
					var domChildLI	= document.createElement('li');
					domUL.appendChild(domChildLI);
					var domChildTable	= document.createElement('table');
					domChildLI.appendChild(domChildTable);
					var domChildTBody	= document.createElement('tbody');
					domChildTable.appendChild(domChildTBody);
					var domChildTR		= document.createElement('tr');
					domChildTBody.appendChild(domChildTR);
					
					var domKeyTH		= document.createElement('th');
					domKeyTH.innerHTML	= "['" + mIndex + "'] => ";
					domChildTR.appendChild(domKeyTH);
					
					var domValueTD		= document.createElement('td');
					domValueTD.appendChild(Reflex_Debug.asHTML(mDebug[mIndex]));
					domChildTR.appendChild(domValueTD);
				}
				break;
			
			// Debug with native toString()
			case 'function':
			default:
				domValueTD.innerHTML	= String(mDebug).escapeHTML();
				break;
		}
	}
	
	return domTable;
};

Reflex_Debug.asHTMLPopup	= function(mDebug)
{
	var domDebugDIV	= document.createElement('div');
	domDebugDIV.addClassName('popup-debug');
	domDebugDIV.appendChild(Reflex_Debug.asHTML(mDebug));
	Reflex_Popup.alert(domDebugDIV);
};

Reflex_Debug.backtrace	= function()
{
	var fCurrent	= Reflex_Debug.backtrace.caller;
	var sBacktrace	= '';
	while (fCurrent)
	{
		if (fCurrent.caller)
		{
			sBacktrace	+= (fCurrent.caller.name ? fCurrent.caller.name : '[anonymous]') + '\n';
		}
		else
		{
			sBacktrace	+= "[original]\n";
		}
		fCurrent	= fCurrent.caller;
	}
	
	alert(sBacktrace);
};
/*
// DEBUG
var _____oTest	= {test1: 'value', test2: 'value2'};
for (mIndex in _____oTest)
{
	alert(mIndex + ": " + _____oTest[mIndex]);
}
*/
