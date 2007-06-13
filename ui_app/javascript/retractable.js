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
	
	this.Slide =function(tblId, objId) 
	{
		this.obj = document.getElementById(objId);
		this.duration = 1;
		//this.height = parseInt(this.obj.style.height);
		
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
			if(Vixen.Slide.table[tblId].row[objId].Sliding != TRUE) 
			{
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+tblId+"','"+objId+"').finishup("+this.height()+");",finishTime);
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
			if(Vixen.Slide.table[tblId].row[objId].Sliding != TRUE) 
			{
				this.obj.style.height = '1px';
				//this.obj.style.display = 'block';
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+tblId+"','"+objId+"').finishdown("+this.newHeight+");",finishTime);
			}
			else
			{
				
				//Vixen.Slide.slideUp = TRUE;
			}
		}
		
		this.slide = function() 
		{
			Vixen.Slide.table[tblId].row[objId].Sliding = TRUE;
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

			window.setTimeout("Vixen.Slide.table['"+tblId+"'].row['"+objId+"'].Sliding = FALSE;",tIncrement * i);			
	
			return tIncrement * i;
			
		}
		
		this.finishup = function(height) 
		{
			//debug (this.obj.clientHeight + "up" + height + ":myheight:" + this.height());
			this.obj.style.display = 'none';
			this.obj.style.height = height + 'px';
			Vixen.Slide.table[tblId].row[objId].Up = TRUE;
		}
		this.finishdown = function(height) 
		{
			//debug (this.obj.clientHeight + "down" + height);
			this.obj.style.display = 'block';
			this.obj.style.height = height + 'px';
			Vixen.Slide.table[tblId].row[objId].Up = FALSE;
		}
		
		return this;
	
	}
	
	this.ToggleSlide =function(strTableId, strTargetId)
	{
		
		if (this.table[strTableId].collapseAll == TRUE)
		{
			for (var key in this.table[strTableId].row) {
  				var objRow = this.table[strTableId].row[key];
				if (this.table[strTableId].row[key].Up != TRUE)
				{
					this.Slide(strTableId, key).up();
					this.table[strTableId].row[key].Up = TRUE;
				}
			}
		}
		if (this.table[strTableId].row[strTargetId].Up == TRUE)
		{
			this.Slide(strTableId, strTargetId).down();
			this.table[strTableId].row[strTargetId].Up = FALSE;			
		}
		else
		{
			this.Slide(strTableId, strTargetId).up();
			this.table[strTableId].row[strTargetId].Up = TRUE;
		}
		document.getElementById(strTargetId).style.display ="block";
	}
	
	this.Attach =function (strTableId, totalRows, bolOneOnly)
	{
		Vixen.Slide.table[strTableId] = new Object();
		Vixen.Slide.table[strTableId].collapseAll = bolOneOnly;	
		Vixen.Slide.table[strTableId].row = new Object();
		for (i = 1; i <=totalRows; i++)
		{	
			Vixen.Slide.table[strTableId].row[strTableId + '_' + i + "DIV"] = new Object();
			Vixen.Slide.table[strTableId].row[strTableId + '_' + i + "DIV"].Up = TRUE;			
			var elmRow = document.getElementById(strTableId + '_' + i);
			elmRow.addEventListener('mousedown', MouseDownHandler, TRUE);
			intHeight = Vixen.Slide.Slide(strTableId, strTableId + '_' + i + 'DIV').height();
			Vixen.Slide.Slide(strTableId, strTableId + '_' + i + 'DIV').finishup(intHeight);
		}
		//debug (Vixen.Slide.table[strTableId], 1);
	}
	
	function MouseDownHandler ()
	{
		Vixen.Slide.ToggleSlide(this.parentNode.parentNode.id, this.id + "DIV");
	}
}

Vixen.Slide = new VixenSlidingClass();
