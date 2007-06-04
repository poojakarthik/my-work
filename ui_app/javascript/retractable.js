var slideInUse = new Array();
var slideup = true;
var oldClasses = new Array();


function Slide(objId, options) 
{
	this.obj = document.getElementById(objId);
	this.duration = 1;
	this.height = parseInt(this.obj.style.height);

	if(typeof options != 'undefined') 
	{ 
		this.options = options; 
	} 
	else 
	{ 
		this.options = {}; 
	}
	
	if(this.options.duration) 
	{ 
		this.duration = this.options.duration; 
	}
		
	this.up = function() 
	{
		this.curHeight = this.height;
		this.newHeight = '0';
		if(slideInUse[objId] != true) 
		{
			var finishTime = this.slide();
			window.setTimeout("Slide('"+objId+"').finishup("+this.height+");",finishTime);
		}
	}

	this.down = function() 
	{
		this.newHeight = this.height;
		this.curHeight = '0';
		if(!slideInUse[objId]) 
		{
			this.obj.style.height = '0px';
			this.obj.style.display = 'block';
			this.slide();
		}
	}
	
	this.slide = function() 
	{
		slideInUse[objId] = true;
		var frames = 15 * duration; // Running at 30 fps

		var tIncrement = (duration*500) / frames;
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
		
		window.setTimeout("delete(slideInUse['"+objId+"']);",tIncrement * i);
		
		if(this.options.onComplete) 
		{
			window.setTimeout(this.options.onComplete, tIncrement * (i-2));
		}
		
		return tIncrement * i;
	}
	
	this.finishup = function(height) 
	{
		this.obj.style.display = 'none';
		this.obj.style.height = height + 'px';
	}
	
	return this;

}

function ToggleSlide(target)
{
	if (slideup)
	{
		Slide(target).down();
		document.getElementById(target).style.display ="block";
		slideup = false;
		
	}
	else
	{
		Slide(target).up();
		document.getElementById(target).style.display ="block";
		slideup = true;
	}
}
