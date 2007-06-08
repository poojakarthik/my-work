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
	this.slideInUse = new Array();
	this.slideUp = TRUE;
	
	this.Slide =function(objId) 
	{
		this.obj = document.getElementById(objId);
		this.duration = 1;
		this.height = parseInt(this.obj.style.height);
		
		this.up = function() 
		{
			this.curHeight = this.height;
			this.newHeight = '0';
			if(Vixen.Slide.slideInUse[objId] != TRUE) 
			{
				var finishTime = this.slide();
				window.setTimeout("Vixen.Slide.Slide('"+objId+"').finishup("+this.height+");",finishTime);
			}
		}
	
		this.down = function() 
		{
			this.newHeight = this.height;
			this.curHeight = '0';
			if(!Vixen.Slide.slideInUse[objId]) 
			{
				this.obj.style.height = '0px';
				this.obj.style.display = 'block';
				this.slide();
			}
		}
		
		this.slide = function() 
		{
			Vixen.Slide.slideInUse[objId] = TRUE;
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
			
			window.setTimeout("delete(Vixen.Slide.slideInUse['"+objId+"']);",tIncrement * i);
			
	
			return tIncrement * i;
			
		}
		
		this.finishup = function(height) 
		{
			this.obj.style.display = 'none';
			this.obj.style.height = height + 'px';
		}
		
		return this;
	
	}
	
	this.ToggleSlide =function(target)
	{
		if (this.slideUp)
		{
			this.Slide(target).down();
			document.getElementById(target).style.display ="block";
			this.slideUp = FALSE;
			
		}
		else
		{
			this.Slide(target).up();
			document.getElementById(target).style.display ="block";
			this.slideUp = TRUE;
		}
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
