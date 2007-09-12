
//script
if (VixenIncludedJSFiles == undefined)
{
	var VixenIncludedJSFiles = new Array();
	//var VixenJSFilesWaitingToBeIncluded = new Array();
	var VixenJSBaseDir = "";
	var VixenJSReadyToLoad = false;
}


function VixenSetJavascriptBaseDir(strBaseDir)
{
	VixenJSBaseDir = strBaseDir;
}

function VixenIncludeJavascript(strFilename, strElement)
{
	var html_doc = document.getElementsByTagName('head').item(0);
    var js = document.createElement('script');

	//js.setAttribute('language', 'javascript');
    js.setAttribute('type', 'text/javascript');
    js.setAttribute('src', VixenJSBaseDir + "javascript/" + strFilename + ".js");
    html_doc.appendChild(js);
}


function VixenIncludeJSOnce(strFilename, strElement) 
{
    if (!VixenInArray(strFilename, VixenIncludedJSFiles)) 
	{
        VixenIncludedJSFiles[VixenIncludedJSFiles.length] = strFilename;
		
		// Only load the file if the page is ready for files to be loaded
		if (VixenJSReadyToLoad)
		{
        	VixenIncludeJavascript(strFilename, strElement);
		}
    }
}

function VixenInArray(strNeedle, arrHaystack) 
{
    for (var i = 0; i < arrHaystack.length; i++)
	{
        if (arrHaystack[i] == strNeedle)
		{
            return true;
        }
    }
    return false;
}

function VixenLoadJSFiles()
{
	// Javascript files can now be loaded willy-nilly
	VixenJSReadyToLoad = true;
	
	// Load them
	for (var i=0; i < VixenIncludedJSFiles.length; i++)
	{
		VixenIncludeJavascript(VixenIncludedJSFiles[i]);
	}
}
