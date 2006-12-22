//************************************************************************************
// Copyright (C) 2006, Massimo Beatini
//
// This software is provided "as-is", without any express or implied warranty. In 
// no event will the authors be held liable for any damages arising from the use 
// of this software.
//
// Permission is granted to anyone to use this software for any purpose, including 
// commercial applications, and to alter it and redistribute it freely, subject to 
// the following restrictions:
//
// 1. The origin of this software must not be misrepresented; you must not claim 
//    that you wrote the original software. If you use this software in a product, 
//    an acknowledgment in the product documentation would be appreciated but is 
//    not required.
//
// 2. Altered source versions must be plainly marked as such, and must not be 
//    misrepresented as being the original software.
//
// 3. This notice may not be removed or altered from any source distribution.
//
//************************************************************************************

//
// global variables
//
var isMozilla;
var objDiv = null;
var originalDivHTML = "";
var DivID = "";
var over = false;

//
// dinamically add a div to 
// dim all the page
//
function buildDimmerDiv()
{
    var dimmer = document.createElement ("DIV");
    dimmer.id = 'dimmer';
    dimmer.className = 'dimmer';
    dimmer.style.width = window.innerWidth + 'px';
    dimmer.style.height = window.innerHeight +'px';
//    document.body.appendChild (dimmer);
}


//
//
//
function displayFloatingDiv(divId, title, width, height, left, top) 
{
	DivID = divId;
	
	if (document.getElementById('dimmer'))
	{
		document.getElementById('dimmer').style.visibility = "visible";
		document.getElementById('dimmer').style.display = "";
	}

    document.getElementById(divId).style.width = width + 'px';
    document.getElementById(divId).style.height = height + 'px';
    document.getElementById(divId).style.left = left + 'px';
    document.getElementById(divId).style.top = top + 'px';
	
	var addHeader;
	
	if (originalDivHTML == "")
	    originalDivHTML = document.getElementById(divId).innerHTML;
	
	addHeader = '<table cellpadding="0" cellspacing="0" border="0" style="width:' + width + 'px" class="floatingHeader">' +
	            '<tr><td ondblclick="void(0);" onmouseover="over=true;" onmouseout="over=false;" style="cursor:move;height:18px">' + title + '</td>' + 
	            '<td style="width:18px" align="right"><a href="javascript:hiddenFloatingDiv(\'' + divId + '\');void(0);">' + 
	            '<img alt="Close..." title="Close..." src="img/template/close.png" class="closewindow" border="0"></a></td></tr></table>';
	

    // add to your div an header	
	document.getElementById(divId).innerHTML = addHeader + originalDivHTML;
	
	
	document.getElementById(divId).className = 'dimming';
	document.getElementById(divId).style.visibility = "visible";
	document.getElementById(divId).style.display = "";


}


//
//
//
function hiddenFloatingDiv(divId) 
{
	document.getElementById(divId).innerHTML = originalDivHTML;
	document.getElementById(divId).style.visibility='hidden';
	document.getElementById('dimmer').style.visibility = 'hidden';
	document.getElementById(divId).style.display='none';
	document.getElementById('dimmer').style.display = 'none';
	
	DivID = "";
}

//
//
//
function MouseDown(e) 
{
    if (over)
    {
        if (isMozilla) {
            objDiv = document.getElementById(DivID);
            X = e.layerX;
            Y = e.layerY;
            return false;
        }
        else {
            objDiv = document.getElementById(DivID);
            objDiv = objDiv.style;
            X = event.offsetX;
            Y = event.offsetY;
        }
    }
}


//
//
//
function MouseMove(e) 
{
    if (objDiv) {
        if (isMozilla) {
            objDiv.style.top = (e.pageY-Y) + 'px';
            objDiv.style.left = (e.pageX-X) + 'px';
            return false;
        }
        else 
        {
            objDiv.pixelLeft = event.clientX-X + document.body.scrollLeft;
            objDiv.pixelTop = event.clientY-Y + document.body.scrollTop;
            return false;
        }
    }
}

//
//
//
function MouseUp() 
{
    objDiv = null;
}


//
//
//
function init()
{
    // check browser
    isMozilla = (document.all) ? 0 : 1;


    if (isMozilla) 
    {
        document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE | Event.MOUSEUP);
    }

    document.onmousedown = MouseDown;
    document.onmousemove = MouseMove;
    document.onmouseup = MouseUp;

    // add the div
    // used to dim the page
	buildDimmerDiv();

}

// call init
init();






function displayDebugWindow()
{
    var w, h, l, t;
    w = 550;
    h = 300;
    l = (window.innerWidth / 2) - (w / 2);
    t = (window.innerHeight / 2) - (h / 2);;
    
    // no title		        
    // displayFloatingDiv('windowcontent', '', w, h, l, t);

    // with title		        
    displayFloatingDiv('windowcontent', 'System Debug', w, h, l, t);
}
