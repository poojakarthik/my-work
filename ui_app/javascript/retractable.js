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
		objId = tblId + '_' + intRow + 'DIV-DETAIL';
		this.obj = document.getElementById(objId);
		this.duration = 1;
		
		this.height =function()
		{
			if (this.obj.style.height)
			{
				this.myheight = this.obj.style.height;
			}
			else
			{
				this.myheight = this.obj.clientHeight;
			}
			return parseInt(this.myheight);
		}
		
		this.up = function() 
		{
			this.curHeight = this.height();
			this.newHeight = '0';
			if(Vixen.table[tblId].row[intRow].Sliding != TRUE) 
			{
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+tblId+"','"+intRow+"').finishup("+this.height()+");",finishTime);
			}
			else
			{
				
				//Vixen.Slide.slideUp = FALSE;
			}
		}
	
		this.down = function() 
		{
			this.newHeight = this.height();
			this.curHeight = '0';
			if(Vixen.table[tblId].row[intRow].Sliding != TRUE) 
			{
				this.obj.style.height = '1px';
				//this.obj.style.display = 'block';
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+tblId+"','"+intRow+"').finishdown("+this.newHeight+");",finishTime);
			}
			else
			{
				
				//Vixen.Slide.slideUp = TRUE;
			}
		}
		
		this.slide = function() 
		{
			Vixen.table[tblId].row[intRow].Sliding = TRUE;
			var frames = 15 * this.duration; // Running at 30 fps
	
			var tIncrement = (this.duration*500) / frames;
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

			window.setTimeout("Vixen.table['"+tblId+"'].row['"+intRow+"'].Sliding = FALSE;",tIncrement * i);			
	
			return tIncrement * i;
			
		}
		
		this.finishup = function(height) 
		{
			//debug (this.obj.clientHeight + "up" + height + ":myheight:" + this.height());
			this.obj.style.display = 'none';
			this.obj.style.height = height + 'px';
			Vixen.table[tblId].row[intRow].Up = TRUE;
		}
		this.finishdown = function(height) 
		{
			//debug (this.obj.clientHeight + "down" + height);
			this.obj.style.display = 'block';
			this.obj.style.height = height + 'px';
			Vixen.table[tblId].row[intRow].Up = FALSE;
		}
		
		return this;
	
	}
	
	this.CollapseAll =function(strTableId)
	{
		objTable = Vixen.table[strTableId];
		
		for (var i=0; i<=objTable.totalRows; i++)
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
		// get row number from strTargetId
		intIndex = strTargetId.lastIndexOf('_');
		intIndex = strTargetId.substr(intIndex + 1);

		// hack until changes
		strTargetId += "DIV-DETAIL";

		objTable = Vixen.table[strTableId];

		if (objTable.collapseAll)
		{
			Vixen.Slide.CollapseAll(strTableId);
		}
		if (objTable.row[intIndex].Up == TRUE)
		{
			this.Slide(strTableId, intIndex).down();
			objTable.row[intIndex].Up = FALSE;			
		}
		else
		{
			this.Slide(strTableId, intIndex).up();
			objTable.row[intIndex].Up = TRUE;
		}
		document.getElementById(strTargetId).style.display ="block";
	}
	
	this.Attach =function (strTableId, totalRows, bolOneOnly)
	{
	
		//debug ("Table-- " + strTableId);
		//debug (Vixen.table[strTableId], 1);
		
		objTable = Vixen.table[strTableId];
		objTable.collapseAll = bolOneOnly;
		objTable.totalRows = totalRows;
		
		for (var i=0; i <=totalRows; i++)
		{	
			objTable.row[i].Up = TRUE;			
			
			var elmRow = document.getElementById(strTableId + '_' + i);
			
			elmRow.addEventListener('mousedown', MouseDownHandler, FALSE);
			
			intHeight = Vixen.Slide.Slide(strTableId, i).height();
			Vixen.Slide.Slide(strTableId, i).finishup(intHeight);
		}
	}
	
	function MouseDownHandler ()
	{
		//debug (Vixen.table, 1);
		Vixen.Slide.ToggleSlide(this.parentNode.parentNode.id, this.id);
	}
}

Vixen.Slide = new VixenSlidingClass();
