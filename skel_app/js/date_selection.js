	
	function weekPlanner (objPlanner)
	{
		
		this.objPlanner = objPlanner;
		
		this.arrSelectionAreas = new Array ();
		
		this.objTimeBar = null;
		
		this.init = function ()
		{
			this.init_SelectionAreas ();
			
			this.objTimeBar = new this.TimeBar (this);
		}
		
		this.init_SelectionAreas = function ()
		{
			arrSelectionAreas = this.objPlanner.getElementsByTagName ("DIV");
			
			for (var objSelectionArea in arrSelectionAreas)
			{
				objSelectionArea = arrSelectionAreas [objSelectionArea];
				
				if (objSelectionArea.className == "weekScheduler_appointmentHour")
				{
					this.arrSelectionAreas [this.arrSelectionAreas.length] = objSelectionArea;
				}
			}
		}
		
		this.TimeBar = function (objWeekPlanner)
		{
			this.objWeekPlanner = objWeekPlanner;
			
			this.objContainer = null;
			
			this.objBar_Moving = null;
			this.objBar_Resize = null;
			
			this.objLabel = null;
			
			this.init = function ()
			{
				this.objContainer = document.createElement ("DIV");
				this.objContainer.className = "Appointment";
				this.objContainer.style.position = "relative";
				this.objContainer = document.getElementById ("weekScheduler_Content").appendChild (this.objContainer);
				
				this.objBar_Moving = document.createElement ("DIV");
				this.objBar_Moving = this.objContainer.appendChild (this.objBar_Moving);
				this.objBar_Moving.objWeekPlanner = this.objWeekPlanner;
				this.objBar_Moving.className = "Moving";
				
				this.objBar_Moving.addEventListener (
					"mousedown",
					function (e)
					{
						element = e.target.objWeekPlanner.objTimeBar.objContainer;
						
						element.dragX = true;
						element.dragY = false;
						
						element.dragMode = "Move";
						
						element.minX = 0;
						element.maxX = document.getElementById ("weekScheduler_Content").offsetWidth;
						
						element.snap = document.getElementById ("weekScheduler_Content").offsetWidth / 24 / 4;
						
						element.callbackMove = e.target.objWeekPlanner.objTimeBar.UpdateValues;
						
						draggingObject.drag (e, element);
					},
					true
				);
				
				this.objBar_Resize = document.createElement ("DIV");
				this.objBar_Resize = this.objContainer.appendChild (this.objBar_Resize);
				this.objBar_Resize.objWeekPlanner = this.objWeekPlanner;
				this.objBar_Resize.className = "Resize";
				
				this.objBar_Resize.addEventListener (
					"mousedown",
					function (e)
					{
						element = e.target.objWeekPlanner.objTimeBar.objContainer;
						
						element.dragX = true;
						element.dragY = false;
						
						element.dragMode = "Resize";
						
						element.minX = Math.ceil (document.getElementById ("weekScheduler_Content").offsetWidth / 24);
						element.maxX = document.getElementById ("weekScheduler_Content").offsetWidth - e.target.offsetWidth;
						
						element.snap = document.getElementById ("weekScheduler_Content").offsetWidth / 24 / 4;
						
						element.callbackMove = e.target.objWeekPlanner.objTimeBar.UpdateValues;
						
						draggingObject.drag (e, element);
					},
					true
				);
			}
			
			this.UpdateValues = function (intX, intY, intW, intH, intSnap)
			{
				startHour = Math.floor (Math.ceil (intX / intSnap * 15) / 60);
				startMinute = (Math.floor (intX / intSnap) * 15) % 60;
				
				ceaseHour = 0;
				ceaseMinute = 0;
				
				durationHours = 0;
				durationMinutes = 0;
				
				document.forms [0].elements [0].value = startHour;
				document.forms [0].elements [1].value = startMinute;
			}
			
			this.init ();
		}
		
		this.init ();
	}
	
	function dragging ()
	{
		this.objDragging = null;
		
		this.intMouseInitialX = 0;
		this.intMouseInitialY = 0;
		
		this.intElementInitialY = 0;
		this.intElementInitialX = 0;
		this.intElementInitialW = 0;
		this.intElementInitialH = 0;
		
		this.drag = function (event, obj)
		{
			var intX;
			var intY;
			
			if (this.objDragging !== null)
			{
				this.drop ();
				return;
			}
			
			this.objDragging = obj;
			
			if (window.attachEvent)
			{
				intX = 
					window.event.clientX + 
					document.documentElement.scrollLeft + 
					document.body.scrollLeft;
					
				intY = 
					window.event.clientY + 
					document.documentElement.scrollTop + 
					document.body.scrollTop;
			}

			if (window.addEventListener)
			{
				intX = event.clientX + window.scrollX;
				intY = event.clientY + window.scrollY;
			}
			
			this.intMouseInitialX = intX;
			this.intMouseInitialY = intY;
			
			this.intElementInitialY = (obj.style.top == "") ? 0 : parseInt (obj.style.top);
			this.intElementInitialX = (obj.style.left == "") ? 0 : parseInt (obj.style.left);
			this.intElementInitialW = obj.offsetWidth;
			this.intElementInitialH = obj.offsetHeight;
			
			if (window.attachEvent) {
				document.attachEvent ("onmouseup", draggingObject.drop);
				document.attachEvent ("onmousemove", draggingObject.move);
				
				window.event.cancelBubble = true;
				window.event.returnValue = false;
			}
			
			if (window.addEventListener) {
				document.addEventListener ("mousemove", draggingObject.move, true);
				document.addEventListener ("mouseup", draggingObject.drop, true);
				
				event.preventDefault ();
			}
		}
		
		this.move = function (event)
		{
			var intX;
			var intY;
			
			if (window.attachEvent)
			{
				intX = 
					window.event.clientX + 
					document.documentElement.scrollLeft + 
					document.body.scrollLeft;
					
				intY = 
					window.event.clientY + 
					document.documentElement.scrollTop + 
					document.body.scrollTop;
			}

			if (window.addEventListener)
			{
				intX = event.clientX + window.scrollX;
				intY = event.clientY + window.scrollY;
			}
			
			var drag_left = parseInt (draggingObject.intElementInitialX + intX - draggingObject.intMouseInitialX);
			var drag_top = parseInt (draggingObject.intElementInitialY  + intY - draggingObject.intMouseInitialY);
			
			if (draggingObject.objDragging.snap)
			{
				drag_left = Math.round (drag_left / draggingObject.objDragging.snap) * draggingObject.objDragging.snap;
			}
			
			if (draggingObject.objDragging.dragX != false)
			{
				if (draggingObject.objDragging.dragMode == "Resize")
				{
					drag_left += draggingObject.intElementInitialW - draggingObject.intElementInitialX;
					
					if (draggingObject.objDragging.minX !== null && drag_left < draggingObject.objDragging.minX)
					{
						drag_left = draggingObject.objDragging.minX;
					}
					else if (draggingObject.objDragging.maxX && drag_left > draggingObject.objDragging.maxX - draggingObject.intElementInitialX)
					{
						drag_left = draggingObject.objDragging.maxX - draggingObject.intElementInitialX;
					}
					
					draggingObject.objDragging.style.width = parseInt (drag_left) + "px";
				}
				else
				{
					if (draggingObject.objDragging.minX !== null && drag_left < draggingObject.objDragging.minX)
					{
						drag_left = draggingObject.objDragging.minX;
					}
					else if (draggingObject.objDragging.maxX && drag_left > draggingObject.objDragging.maxX - draggingObject.intElementInitialW)
					{
						drag_left = draggingObject.objDragging.maxX - draggingObject.intElementInitialW;
					}
					
					draggingObject.objDragging.style.left = parseInt (drag_left) + "px";
				}
			}
			
			if (draggingObject.objDragging.dragY != false)
			{
				if (draggingObject.objDragging.dragMode == "Resize")
				{
//
				}
				else
				{
					if (draggingObject.objDragging.minY !== null && drag_top < draggingObject.objDragging.minY)
					{
						drag_top = draggingObject.objDragging.minY;
					}
					else if (draggingObject.objDragging.maxY && drag_top > draggingObject.objDragging.maxY - draggingObject.intElementInitialH)
					{
						drag_top = draggingObject.objDragging.maxY - draggingObject.intElementInitialY;
					}
					
					draggingObject.objDragging.style.top = parseInt (drag_top) + "px";
				}
			}
			
			draggingObject.objDragging.callbackMove (
				parseInt (draggingObject.objDragging.style.left), 
				0, 
				0, 
				0, 
				draggingObject.objDragging.snap
			);
			
			if (window.attachEvent) {
				window.event.returnValue = false;
				window.event.cancelBubble = true;
			}
			
			if (window.addEventListener)
				event.preventDefault();
		}
		
		this.drop = function (evt)
		{
			if (window.attachEvent)
			{
				document.detachEvent("onmousemove", draggingObject.move);
				document.detachEvent("onmouseup",   draggingObject.drop);
			}
			
			if (window.addEventListener)
			{
				document.removeEventListener("mousemove", draggingObject.move, true);
				document.removeEventListener("mouseup",   draggingObject.drop, true);
			}
			
			draggingObject.objDragging = null;
		}
	}
	
	var draggingObject = new dragging ();
	
	window.addEventListener (
		"load",
		function ()
		{
			new weekPlanner (
				document.getElementById ("weekScheduler_Container")
			);
		},
		true
	);
	
