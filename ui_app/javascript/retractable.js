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
	this.slideInUse = new Object();
	this.slideTarget = "";
	
	this.Slide =function(objId) 
	{
		this.obj = document.getElementById(objId);
		this.duration = 1;
		this.height = parseInt(this.obj.style.height);
		
		this.up = function() 
		{
			this.curHeight = this.height;
			this.newHeight = '0';
			if(Vixen.Slide.slideInUse[objId].Sliding != TRUE) 
			{
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+objId+"').finishup("+this.height+");",finishTime);
			}
			else
			{
				
				//Vixen.Slide.slideUp = FALSE;
			}
		}
	
		this.down = function() 
		{
			this.newHeight = this.height;
			this.curHeight = '0';
			if(Vixen.Slide.slideInUse[objId].Sliding != TRUE) 
			{
				this.obj.style.height = '0px';
				//this.obj.style.display = 'none';
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+objId+"').finishdown("+this.height+");",finishTime);
			}
			else
			{
				
				//Vixen.Slide.slideUp = TRUE;
			}
		}
		
		this.slide = function() 
		{
			Vixen.Slide.slideInUse[objId].Sliding = TRUE;
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
			
			//window.setTimeout("Vixen.Slide.slideInUse['"+objId+"'] = FALSE; debug('slideup:' + Vixen.Slide.slideUp);",tIncrement * i);
			window.setTimeout("Vixen.Slide.slideInUse['"+objId+"'].Sliding = FALSE;",tIncrement * i);			
	
			return tIncrement * i;
			
		}
		
		this.finishup = function(height) 
		{
			this.obj.style.display = 'none';
			this.obj.style.height = height + 'px';
			Vixen.Slide.slideInUse[objId].Up = TRUE;
		}
		this.finishdown = function(height) 
		{
			this.obj.style.display = 'block';
			this.obj.style.height = height + 'px';
			Vixen.Slide.slideInUse[objId].Up = FALSE;
		}
		
		return this;
	
	}
	
	this.ToggleSlide =function(target)
	{
		if (!this.slideInUse[target] || this.slideInUse[target].Up == TRUE)
		{
			this.slideInUse[target] = new Array();
			if (this.slideTarget == target || this.slideTarget == "")
			{
				this.Slide(target).down();
			}
			else
			{
				if (this.slideInUse[this.slideTarget].Up == TRUE)
				{
					this.Slide(this.slideTarget).down();
				}
				else
				{
					this.Slide(this.slideTarget).up();
					this.slideTarget = "";
				}
				this.Slide(target).down();				
			}
			//this.slideTarget = target;
			
		}
		else
		{
			if (this.slideTarget == target || this.slideTarget == "")
			{
				this.Slide(target).up();
				this.slideTarget = "";
			}
			else
			{
				if (this.slideInUse[this.slideTarget].Up == TRUE)
				{
					this.Slide(this.slideTarget).down();
				}
				else
				{
					this.Slide(this.slideTarget).up();
					this.slideTarget = "";
				}
				this.Slide(target).up();
			}
		}
		document.getElementById(target).style.display ="block";
	}
	
	this.Attach =function (strTableId, totalRows)
	{
		for (i = 1; i <=totalRows; i++)
		{
			var elmRow = document.getElementById(strTableId + '_' + i);
			elmRow.addEventListener('mousedown', MouseDownHandler, TRUE);
		}
	}
	
	function MouseDownHandler ()
	{
		Vixen.Slide.ToggleSlide(this.id + "DIV");
	}
}

Vixen.Slide = new VixenSlidingClass();
