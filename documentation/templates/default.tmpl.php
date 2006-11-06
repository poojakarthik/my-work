<?php
// template class
class aphplix_template_def
{
	function aphplix_template_def()
	{
		// set replacements
		$this->replacements["<~@copyright~>"] = "&copy; Copyright 2006, VOIPTEL";
	}
	
	function set($page, $content, $meta, $menu)
	{
		$this->page_name = $page;
		$this->content = $content;
		$this->meta = $meta;
		$this->menu_data = $menu;
	}
	
	function menu()
	{
		// menu header
		$this->menu_html = '
			<table border="0" cellspacing="3" cellpadding="3" width="100%">
			';
			
		// menu items
		foreach ($this->menu_data as $key=>$value)
		{
			$this->menu_html .= '
				<tr>
					<td align="left" class="td_grey">
						<a href="'.$value.'">'.$key.'</a>
					</td>
				</tr>';
		}
		$this->menu_html .= '
			</table>';
	}
	
	function head()
	{
		$value = '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
			<head>
				<title>'.$this->meta['title'].'</title>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
				<style type="text/css">
					a {
						text-decoration: none;
						font-weight: normal;
						font-family: arial, sans-serif;
						font-size: 12px;
					}
					* {
						font-family: arial, sans-serif;
						font-size: 12px;
					}
					.td_white {
						background-color: #FFFFFF;
						color: #000000;
						font-weight: normal;
						font-family: arial, sans-serif;
						font-size: 12px;
					}
					.td_content {
						background-color: #FFFFFF;
						color: #000000;
						font-weight: normal;
						font-family: arial, sans-serif;
						font-size: 12px;
					}
					.td_blue {
						background-color: #6657d0;
						color: #FFFFFF;
						font-weight: bold;
						font-family: arial, sans-serif;
						font-size: 13px;
					}
					.td_black {
						background-color: #000000;
						color: #FFFFFF;
						font-weight: normal;
						font-family: arial, sans-serif;
						font-size: 12px;
					}
					.td_grey {
						background-color: #e0e0e0;
						color: #000000;
						font-weight: normal;
						font-family: arial, sans-serif;
						font-size: 12px;
					}
				</style>
			</head>
			<body bgcolor="#FFFFFF" text="#000000" link="#6657d0" alink="#6657d0" vlink="#6657d0">';
			return $value;
	}
	
	function foot()
	{
		$value = '
			</body>
		</html>';
		return $value;
	}
	
	function page()
	{
		// build the page
		$page = '
		<table border="0" cellspacing="3" cellpadding="2" width="100%">
			<tr>
				<td colspan="4" width="100%" align="left" class="td_blue">
					<table border="0" cellspacing="3" cellpadding="2" width="100%">
        				<tr>
          					<td width="20%" align="left" valign="top" class="td_white">';
		  				
		// menu
		$page .= $this->menu_html;
		
		$page .= '
							</td>
							<td align="left" valign="top" class="td_content">
								<p>';
		
		// content
		$page .= $this->content;
		
		$page .= '
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
		&nbsp;&copy; copyright 2006 VOIPTEL
		<br>
		<br>
		';
		return $page;
	}
	
}
?>
