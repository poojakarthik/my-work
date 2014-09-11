var Tabs = {
	TAB_COLLECTION_CLASS_NAME	: "tab-collection",
	TAB_HEADER_CLASS_NAME		: "tab-header",
	TAB_BODY_CLASS_NAME			: "tab-body",
	
	// Stores references to all tab collections
	tabCollections : {},

	// elmRoot is the parent element in which to look for tab collections
	// Nested tab collections are not currently supported
	initialiseTabCollections : function(elmRoot)
	{
		var arrTabCollections = elmRoot.getElementsByClassName(this.TAB_COLLECTION_CLASS_NAME);
		
		for (var i=0, j=arrTabCollections.length; i < j; i++)
		{
			this.initialiseTabCollection(arrTabCollections[i]);
		}
	},
	
	initialiseTabCollection : function(elmCollection)
	{
		var strName = elmCollection.getAttribute("name");
		
		var arrDetails = {};
		
		var tabIndex;
		
		// Grab all the tabs headers
		var arrHeaders = elmCollection.getElementsByClassName(this.TAB_HEADER_CLASS_NAME);
		for (var i=0, j=arrHeaders.length; i < j; i++)
		{
			tabIndex = arrHeaders[i].getAttribute("tab");
			arrDetails[tabIndex] = {header : arrHeaders[i]};
			
			// Define event listener
			Event.startObserving(arrHeaders[i], "click", this.selectTab.bind(this, strName, tabIndex), true);
		}
		
		// Grab all the tab bodies
		var arrBodies = elmCollection.getElementsByClassName(this.TAB_BODY_CLASS_NAME);
		for (i=0, j=arrBodies.length; i < j; i++)
		{
			tabIndex = arrBodies[i].getAttribute("tab");
			arrDetails[tabIndex].body = arrBodies[i];
		}
		
		this.tabCollections[strName] = arrDetails;
		
		// Default to the first tab
		this.selectTab(strName, 0);
	},
	
	selectTab : function(strTabCollection, tabIndex)
	{
		var objCollection = this.tabCollections[strTabCollection];
		
		// Hide all the tabs
		for (i in objCollection)
		{
			objCollection[i].body.style.display = "none";
			objCollection[i].header.className = "tab-header";
			objCollection[i].header.setAttribute("isSelected", false);
		}
		
		// Display the one requested
		objCollection[tabIndex].header.className = "tab-header selected";
		objCollection[tabIndex].body.style.display = "block";
		objCollection[tabIndex].header.setAttribute("isSelected", true);
	},

	getSelectedTabIndex : function(strTabCollection)
	{
		var objCollection = this.tabCollections[strTabCollection];

		// Hide all the tabs
		for (i in objCollection)
		{
			if (objCollection[i].header.getAttribute("isSelected"))
			{
				return i;
			}
		}
		return null;
	}
	
};
