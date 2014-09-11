<?php


var H = require('fw/dom/factory'), // HTML
 Class = require('fw/class'),
 Component = require('fw/component')
;
 
var self = new Class({
 extends: Component,

 _buildUI: function () {
  this.NODE = H.section(
   H.h1('Report List'),
   H.form({method: "post"},
    H.div({},
     H.label('Title'),
     H.input({type: 'text', placeholder: 'Enter Report Title Here', name: 'title'})
    ),
    
    }
    });
class HtmlTemplate_DataReportTest_List extends FlexHtmlTemplate
{
	
	public function Render()
	{
		echo "
<div id='DataReportTestListContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			var oDataReportList = new Page_DataReportTest_List(\$ID('DataReportTestListContainer'));
		}, false)
</script>\n";

	}
}

?>