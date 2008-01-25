<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>viXen Monitor</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
			a
			{
				text-decoration: 	none;
				font-weight: 		normal;
				font-family: 		arial, sans-serif;
				font-size: 			12px;
			}
			.Indent
			{
				padding-left:		25px;
			}
			.Spacer
			{
				height:				5px;
			}
			.LargeSpacer
			{
				height:				20px;
			}
			.File
			{
				font-weight:		bold;
				color:			#0000FF;
			}
			.Todo
			{
				color:			#000044;
			}
			.Urgent
			{
				color:			#FF0000;
			}
		</style>
	</head>

	<body bgcolor="#FFFFFF" text="#000000" link="#000000" alink="#000000" vlink="#000000">
		<div align="center">
			<font style="font-size: 20px;" ><?php echo $arrPage['Title'] ?></font>
			<br>		
		</div>

<?php
echo $arrPage['Body'];
?>
		
		<div align="center">
			<br>
			<br>
		</div>
	</body>
</html>
