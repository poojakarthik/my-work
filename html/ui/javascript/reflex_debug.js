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
		domTypeTD.innerHTML	= 'array';
		var domUL	= document.createElement('ul');
		domValueTD.appendChild(domUL);
		for (var i = 0; i < mDebug.length; i++)
		{
			var domKeyLI		= document.createElement('li');
			domKeyLI.innerHTML	= "["+i+"] => ";
			domUL.appendChild(domKeyLI);
			
			var domValueLI	= document.createElement('li');
			domValueLI.appendChild(Reflex_Debug.asHTML(mDebug[i], bOrdered));
			domUL.appendChild(domValueLI);
		}
	}
	else
	{
		domTypeLI.innerHTML	= typeof mDebug;
		
		switch (typeof mDebug)
		{
			// Debug as an Object
			case 'object':
				var domUL	= document.createElement('ul');
				domValueTD.appendChild(domUL);
				for (i in mDebug)
				{
					var domKeyLI		= document.createElement('li');
					domKeyLI.innerHTML	= "['"+i+"'] => ";
					domUL.appendChild(domKeyLI);
					
					var domValueLI	= document.createElement('li');
					domValueLI.appendChild(Reflex_Debug.asHTML(mDebug[i], bOrdered));
					domUL.appendChild(domValueLI);
				}
				break;
			
			// Debug with native toString()
			case 'function':
			default:
				domLI.innerHTML	= String(mDebug).escapeHTML();
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
	$Alert(domDebugDIV, 'large', null, 'modal', 'Developer Debug');
};