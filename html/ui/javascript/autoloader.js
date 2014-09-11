
//script
if (VixenIncludedJSFiles == undefined)
{
	var VixenIncludedJSFiles = new Array();
	var VixenJSBaseDir = "";
}


function VixenSetJavascriptBaseDir(strBaseDir)
{
	VixenJSBaseDir = strBaseDir;
}

function VixenIncludeJavascript(strFilename, strElement)
{
	var html_doc = document.getElementsByTagName('head').item(0);
    var js = document.createElement('script');

	js.setAttribute('language', 'javascript');
    js.setAttribute('type', 'text/javascript');
    js.setAttribute('src', VixenJSBaseDir + "javascript/" + strFilename + ".js");
    html_doc.appendChild(js);
}


function VixenIncludeJSOnce(strFilename, strElement) 
{
    if (!VixenInArray(strFilename, VixenIncludedJSFiles)) 
	{
        VixenIncludedJSFiles[VixenIncludedJSFiles.length] = strFilename;
        VixenIncludeJavascript(strFilename, strElement);
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
