//----------------------------------------------------------------------------//
// VixenSlidingClass
//----------------------------------------------------------------------------//
/**
 * VixenSlidingClass
 *
 * Vixen sliding class
 *
 * Vixen sliding class
 *
 *
 *
 * @package	framework_ui
 * @class	Vixen.Slide
 */
 
function VixenSlidingClass()
{
	this.table = new Object();
	this.slideTarget = "";
	
	this.Slide =function(tblId, intRow) 
	{
		// Grab the div which we want to slide
		objId = tblId + '_' + intRow + 'DIV-DETAIL';
		this.obj = $ID(objId);
		
		// How long to slide for (seconds)
		this.duration = 1;
		
		this.height = function()
		{
			if (!this.obj)
			{
				return FALSE;
			}
			if (this.obj.style.height)
			{
				// style will only be set AFTER sliding
				this.myheight = this.obj.style.height;
			}
			else
			{
				// grab the size it appears to be instead
				this.myheight = this.obj.clientHeight;
			}
			return parseInt(this.myheight);
		}
		
		this.up = function() 
		{
			// The div is down, lets bring it up
			if (!this.obj)
			{
				return FALSE;
			}
			this.curHeight = this.height();
			this.newHeight = '0';
			// If the div is not currently sliding, start sliding
			if(Vixen.table[tblId].row[intRow].Sliding != TRUE) 
			{
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+tblId+"','"+intRow+"').finishup("+this.height()+");",finishTime);
			}
		}
	
		this.down = function() 
		{
			// The div is up, lets take it down
			this.newHeight = this.height();
			this.curHeight = '0';
			// If the div is not currently sliding, start sliding
			if(Vixen.table[tblId].row[intRow].Sliding != TRUE) 
			{
				this.obj.style.height = '1px';
				//this.obj.style.display = 'block';
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+tblId+"','"+intRow+"').finishdown("+this.newHeight+");",finishTime);
			}
		}
		
		this.slide = function() 
		{
			// Generic function to change height of div over time
			Vixen.table[tblId].row[intRow].Sliding = TRUE;
			var intFPS = 20; // Running at 10 fps
			var frames = intFPS * (this.duration); 
	
			var tIncrement = 500 / intFPS;
			tIncrement = Math.round(tIncrement);
			var sIncrement = (this.curHeight-this.newHeight) / frames;
	
			var frameSizes = new Array();
			for(var i=0; i < frames; i++) 
			{
				if(i < frames/2) 
				{
					frameSizes[i] = (sIncrement * (i/frames))*4;
				} 
				else 
				{
					frameSizes[i] = (sIncrement * (1-(i/frames)))*4;
				}
			}
			
			for(var i=0; i < frames; i++) 
			{
				this.curHeight = this.curHeight - frameSizes[i];
				window.setTimeout("document.getElementById('"+objId+"').style.height='"+Math.round(this.curHeight)+"px';",tIncrement * i);
			}
			
			// Once we finish sliding
			window.setTimeout("Vixen.table['"+tblId+"'].row['"+intRow+"'].Sliding = FALSE;",tIncrement * i);			

			return tIncrement * i;
			
		}
		
		this.finishup = function(height) 
		{
			// Make sure the div can't be seen
			this.obj.style.display = 'none';
			this.obj.style.height = height + 'px';
			Vixen.table[tblId].row[intRow].Up = TRUE;
		}
		this.finishdown = function(height) 
		{
			// Make sure the div is quite visible
			this.obj.style.display = 'block';
			this.obj.style.height = height + 'px';
			Vixen.table[tblId].row[intRow].Up = FALSE;
		}
		
		return this;
	
	}
	
	this.CollapseAll = function(strTableId)
	{
		// Slide up any divs which are currently down
		objTable = Vixen.table[strTableId];
		
		if (!objTable)
		{
			return;
		}
		
		for (var i=0; i < objTable.totalRows; i++)
		{
			var objRow = objTable.row[i];
			if (objTable.row[i].Up != TRUE)
			{
				this.Slide(strTableId, i).up();
				objTable.row[i].Up = TRUE;
			}
		}
	}
	
	this.ToggleSlide =function(strTableId, strTargetId)
	{
		// Slide this div
		
		// get row number from strTargetId
		intIndex = strTargetId.lastIndexOf('_');
		intIndex = strTargetId.substr(intIndex + 1);

		// hardcoded until a naming convention is figured out
		strTargetId += "DIV-DETAIL";

		objTable = Vixen.table[strTableId];
		
		if (!objTable)
		{
			return;
		}
		
		if (objTable.collapseAll)
		{
			Vixen.Slide.CollapseAll(strTableId);
		}
		if (objTable.row[intIndex].Up == TRUE)
		{
			// Our div is up, lets send it down
			this.Slide(strTableId, intIndex).down();
			objTable.row[intIndex].Up = FALSE;			
		}
		else
		{
			// Our div is down, bring it back up
			this.Slide(strTableId, intIndex).up();
			objTable.row[intIndex].Up = TRUE;
		}
		$ID(strTargetId).style.display = "block";
	}
	
	this.Attach = function(strTableId, bolOneOnly)
	{
		// Add behaviour to the rows of the table
		// Grab the table object
		objTable				= Vixen.table[strTableId];
		objTable.collapseAll	= bolOneOnly;
		
		for (var i=0; i < objTable.totalRows; i++)
		{
			objTable.row[i].Up = TRUE;			
			var elmRow = $ID(strTableId + '_' + i);
			elmRow.intRowIndex = i;
			elmRow.addEventListener('click', SingleClickHandler, true);
			elmRow.addEventListener('dblclick', DoubleClickHandler, true);
			
			// Table is just loaded, collapse all the expanded divs
			//  this is needed so the div will figure out the correct height itself
			intHeight = Vixen.Slide.Slide(strTableId, i).height();
			Vixen.Slide.Slide(strTableId, i).finishup(intHeight);
		}
	}
	
	function DoubleClickHandler()
	{
		// MouseDown on row, slide the div
		Vixen.Slide.ToggleSlide(this.parentNode.parentNode.id, this.id);
	}
	
	// If the row that is clickedOn isn't the currently highlighted row, then retract all other drop-down details
	function SingleClickHandler(objEvent)
	{
		var elmRow	= objEvent.currentTarget;
		if (Vixen.table[elmRow.parentNode.parentNode.id].row[elmRow.intRowIndex].Up)
		{
			Vixen.Slide.CollapseAll(elmRow.parentNode.parentNode.id);
		}
	}
}

// Create an instance of the Vixen Sliding class
if (Vixen.Slide == undefined)
{
	Vixen.Slide = new VixenSlidingClass();
}
