	function weekPlanner(objPlanner)
	{
		this.objPlanner = objPlanner;
		this.arrSelectionAreas = new Array();
		this.objTimeBar = null;
		
		this.init = 
		function()
		{
			this.init_SelectionAreas();
			this.objTimeBar = new this.TimeBar(this);
		}
		
		this.init_SelectionAreas = 
		function()
		{
			arrSelectionAreas = this.objPlanner.getElementsByTagName("DIV");
			for (var objSelectionArea in arrSelectionAreas)
			{
				objSelectionArea = arrSelectionAreas[objSelectionArea];
				if (objSelectionArea.className == "weekScheduler_appointmentHour")
				{
					this.arrSelectionAreas[this.arrSelectionAreas.length] = objSelectionArea;
				}
			}
		}
		
		this.TimeBar = 
		function(objWeekPlanner)
		{
			this.objWeekPlanner = objWeekPlanner;
			this.objContainer = null;
			this.objBar_Moving = null;
			this.objBar_Resize = null;
			this.objLabel = null;
			
			this.init = 
			function()
			{
				this.objContainer = document.createElement("DIV");
				this.objContainer.className = "Appointment";
				this.objContainer.style.position = "relative";
				this.objContainer = document.getElementById("weekScheduler_Content").appendChild (this.objContainer);
				this.objBar_Moving = document.createElement("DIV");
				this.objBar_Moving = this.objContainer.appendChild(this.objBar_Moving);
				this.objBar_Moving.objWeekPlanner = this.objWeekPlanner;
				this.objBar_Moving.className = "Moving";

				//setting left most block of movable time block
				var strStartTime = document.getElementById("Rate.StartTime").value;
				var arrStartTime = new Array();
				arrStartTime = strStartTime.split(':');
				
				var StrStartHours = arrStartTime[0];
				var StrStartMinutes = arrStartTime[1];				
				var StrStartSeconds = arrStartTime[2];
				
				// using base10 as the output for hours/mins
				var IntStartHours = (parseInt(StrStartHours,10)*24);
				var IntStartMinutes = (parseInt(StrStartMinutes,10));

				this.objContainer.style.left = IntStartHours;

				// determines how much to increase the block, each hour is 24px in size
				// and half and quarter hour are divisions of this
				// 
				// 1hr 		= 24px
				// 45mins	= 18px
				// 30mins	= 12px
				// 15mins	= 6px	
				switch(IntStartMinutes)
				{
					case 0:
						this.objContainer.style.left = IntStartHours;	
						break;						
					case 14:
						this.objContainer.style.left = IntStartHours + 6;	
						break;
					case 29:
						this.objContainer.style.left = IntStartHours + 12;
						break;
					case 44:
						this.objContainer.style.left = IntStartHours + 18;
						break;
					case 59:
						this.objContainer.style.left = IntStartHours + 24;
						break;
				}

				var strEndTime = document.getElementById("Rate.EndTime").value;
				var arrEndTime = new Array();
				arrEndTime = strEndTime.split(':');
				
				var StrEndHours = arrEndTime[0];
				var StrEndMinutes = arrEndTime[1];				
				var StrEndSeconds = arrEndTime[2];
				
				// using base10 as the output for hours/mins/secs
				var IntEndHours = (parseInt(StrEndHours,10) * 24);
				var IntEndMinutes = (parseInt(StrEndMinutes,10));
				
				// how much to set the width of the time block as in endhours minus the start hours
				// gives which 'hour' the time block ends in, a switch statement then determines how 
				// much to increment the minutes
				this.objContainer.style.width = (IntEndHours - IntStartHours);
								
				switch(IntEndMinutes)
				{
					case 0:
						this.objContainer.style.width = (IntEndHours - IntStartHours);	
						break;						
					case 14:
						this.objContainer.style.width = (IntEndHours - IntStartHours) + 6;	
						break;
					case 29:
						this.objContainer.style.width = (IntEndHours - IntStartHours) + 12;
						break;
					case 44:
						this.objContainer.style.width = (IntEndHours - IntStartHours) + 18;
						break;
					case 59:
						this.objContainer.style.width = (IntEndHours - IntStartHours) + 24;
						break;
				}				
				
				var IntStartTimeDuration = parseInt(arrStartTime[0]+arrStartTime[1]);
				var IntEndTimeDuration = parseInt(arrEndTime[0]+arrEndTime[1]);
				
				// Convert HH:MM to minutes
				var intStartMinutes	= parseInt(arrStartTime[1])	+ (parseInt(arrStartTime[0]) * 60);
				var intEndMinutes	= parseInt(arrEndTime[1])	+ (parseInt(arrEndTime[0]) * 60);
				var intDuration		= intEndMinutes - intStartMinutes;
				
				// Convert minutes to HH:MM
				var strDurationHours	= Math.floor(intDuration / 60) + "";
				var strDurationMinutes	= intDuration % 60 + "";
				
				if (parseInt(strDurationHours) < 9)
				{
					strDurationHours = "0" + strDurationHours;
				}
				if (parseInt(strDurationMinutes) < 9)
				{
					strDurationMinutes = "0" + strDurationMinutes;
				}
				
				var StrTimeDuration = strDurationHours + ":" + strDurationMinutes;
								
				document.getElementById("Rate.Duration").value = StrTimeDuration; 
				
				this.objBar_Moving.addEventListener("mousedown",function(e)
				{
					element = e.target.objWeekPlanner.objTimeBar.objContainer;
					element.dragX = true;
					element.dragY = false;
					element.dragMode = "Move";
					element.minX = 0;
					element.maxX = document.getElementById("weekScheduler_Content").offsetWidth;
					element.snap = document.getElementById("weekScheduler_Content").offsetWidth / 24 / 4;
					element.callbackMove = e.target.objWeekPlanner.objTimeBar.UpdateValues;
					draggingObject.drag (e, element);
				},true);
				
				this.objBar_Resize = document.createElement("DIV");
				this.objBar_Resize = this.objContainer.appendChild(this.objBar_Resize);
				this.objBar_Resize.objWeekPlanner = this.objWeekPlanner;
				this.objBar_Resize.className = "Resize";
				
				this.objBar_Resize.addEventListener("mousedown",function(e)
				{
					element = e.target.objWeekPlanner.objTimeBar.objContainer;
					element.dragX = true;
					element.dragY = false;
					element.dragMode = "Resize";
					element.minX = Math.ceil (document.getElementById("weekScheduler_Content").offsetWidth / 24);
					element.maxX = document.getElementById ("weekScheduler_Content").offsetWidth;
					element.snap = Math.floor (document.getElementById("weekScheduler_Content").offsetWidth / 24 / 4);
					element.callbackMove = e.target.objWeekPlanner.objTimeBar.UpdateValues;
					draggingObject.drag(e, element);
				},true);
			}
			
			this.UpdateValues = 
			function(intX, intY, intW, intH, intSnap)
			{
				startHour = Math.floor (Math.ceil (intX / intSnap * 15) / 60);
				startMinute = (Math.floor (intX / intSnap) * 15) % 60;
				
				durationHours = Math.floor (Math.ceil (intW / intSnap * 15) / 60);
				durationMinutes = ((Math.floor (intW / intSnap) * 15) % 60);
				
				if (durationMinutes == -1)
				{
					durationHours -= 1;
					durationMinutes = 59;
				}
				
				ceaseHour = Math.floor (((startHour * 60) + (durationHours * 60) + startMinute + durationMinutes - 1) / 60);
				ceaseMinute = Math.floor (((startHour * 60) + (durationHours * 60) + startMinute + durationMinutes - 1) % 60);
				
				if (startMinute.toString ().length == 1)
				{
					startMinute = "0" + startMinute.toString();
				}				
				
				if (ceaseMinute.toString ().length == 1)
				{
					ceaseMinute = "0" + ceaseMinute.toString();
				}
				
				if (durationMinutes.toString ().length == 1)
				{
					durationMinutes = "0" + durationMinutes.toString();
				}				
				
				if (startHour.toString ().length == 1)
				{			
					startHour = "0" + startHour.toString();
				}				
				
				if (ceaseHour.toString ().length == 1)
				{			
					ceaseHour = "0" + ceaseHour.toString();
				}				
				
				if (durationHours.toString ().length == 1)
				{				
					durationHours = "0" + durationHours.toString();
				}
			
				// update the values in the textbox
				document.getElementById("Rate.StartTime").value = startHour + ":" + startMinute + ":00";
				document.getElementById("Rate.EndTime").value = ceaseHour + ":" + ceaseMinute + ":59";
				document.getElementById("Rate.Duration").value = durationHours + ":" + durationMinutes;
			}
			this.init();
		}
		this.init();
	}
	
	function dragging()
	{
		this.objDragging = null;
		
		this.intMouseInitialX = 0;
		this.intMouseInitialY = 0;
		
		this.intElementInitialY = 0;
		this.intElementInitialX = 0;
		this.intElementInitialW = 0;
		this.intElementInitialH = 0;
		
		this.drag = 
		function(event, obj)
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
				intX = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
				intY = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
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
			
			if (window.attachEvent)
			{
				document.attachEvent("onmouseup", draggingObject.drop);
				document.attachEvent("onmousemove", draggingObject.move);
				window.event.cancelBubble = true;
				window.event.returnValue = false;
			}
			
			if (window.addEventListener)
			{
				document.addEventListener("mousemove", draggingObject.move, true);
				document.addEventListener("mouseup", draggingObject.drop, true);
				event.preventDefault();
			}
		}
		
		this.move = 
		function(event)
		{
			var intX;
			var intY;
			
			if (window.attachEvent)
			{
				intX = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
				intY = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
			}

			if (window.addEventListener)
			{
				intX = event.clientX + window.scrollX;
				intY = event.clientY + window.scrollY;
			}
			
			var drag_left = parseInt(draggingObject.intElementInitialX + intX - draggingObject.intMouseInitialX);
			var drag_top = parseInt(draggingObject.intElementInitialY  + intY - draggingObject.intMouseInitialY);
			
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
			
			// where the intx and inty values are set when the bar is moved
			draggingObject.objDragging.callbackMove(
				(draggingObject.objDragging.style.left != "") ? parseInt (draggingObject.objDragging.style.left) : 0, 0, 
				(draggingObject.objDragging.style.width != "") ? parseInt (draggingObject.objDragging.style.width) : 0, 0, 
				draggingObject.objDragging.snap
			);
			
			if (window.attachEvent)
			{
				window.event.returnValue = false;
				window.event.cancelBubble = true;
			}
			
			if (window.addEventListener)
			{
				event.preventDefault();
			}
		}
		
		this.drop = 
		function(evt)
		{
			if (window.attachEvent)
			{
				document.detachEvent("onmousemove", draggingObject.move);
				document.detachEvent("onmouseup", draggingObject.drop);
			}
			
			if (window.addEventListener)
			{
				document.removeEventListener("mousemove", draggingObject.move, true);
				document.removeEventListener("mouseup", draggingObject.drop, true);
			}
			draggingObject.objDragging = null;
		}
	}
	
	var draggingObject = new dragging();

	window.addEventListener("load",
		function()
		{
			new weekPlanner(document.getElementById ("weekScheduler_Container"));
		},true);
