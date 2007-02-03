<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php $objPage->Display('PageTitle'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
			a
			{
				text-decoration: 	none;
				font-weight: 		normal;
				font-family: 		arial, sans-serif;
				font-size: 			12px;
			}
			
			.StandardTitle
			{
				font-weight:		bold;
			}
			
			.StandardText
			{
				text-decoration: 	none;
				font-weight: 		normal;
				font-family: 		arial, sans-serif;
				font-size: 			12px;
			}
			
			.StandardTable
			{
				border-width: 0px 0px 0px 0px;
				border-spacing: 0px;
				border-style: outset outset outset outset;
				border-collapse: collapse;
			}
			.StandardTr
			{
				border-width: 0px 0px 0px 0px;
				padding: 0px 0px 0px 0px;
				border-style: inset inset inset inset;
			}
			.StandardTd
			{
				border-width: 0px 0px 0px 0px;
				padding: 1px 2px 1px 2px;
				border-style: inset inset inset inset;
			}
			
			.BorderTable
			{
				border-width: 1px 1px 1px 1px;
				border-spacing: 0px;
				border-style: outset outset outset outset;
				border-collapse: collapse;
			}
			.BorderTr
			{
				border-width: 1px 1px 1px 1px;
				padding: 0px 0px 0px 0px;
				border-style: inset inset inset inset;
			}
			.BorderTd
			{
				border-width: 1px 1px 1px 1px;
				padding: 1px 3px 1px 3px;
				border-style: inset inset inset inset;
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

	<body bgcolor="#FFFFFF" text="#000000" link="#0000FF" alink="#0000FF" vlink="#0000FF">
		<div align="center">
			<font style="font-size: 20px;" ><?php $objPage->Display('PageTitle'); ?></font>
			<br>
			<br>
			<font style="font-color: FF0000;" ><?php $objPage->Display('Error'); ?></font>	
		</div>
<?php $objPage->Display('Body'); ?>
		
		<div align="center">
			<br>
<?php
if ($objPage->Pagination == TRUE)
{
?>
<a href="<?php $objPage->Display('PaginationFirst'); ?>">|&lt;</a> 
&nbsp; &nbsp; 
<a href="<?php $objPage->Display('PaginationPrevious'); ?>">&lt;&lt;</a>
&nbsp; &nbsp; &nbsp; &nbsp; 
<a href="<?php $objPage->Display('PaginationNext'); ?>">&gt;&gt;</a>
&nbsp; &nbsp;
<?php if ($objPage->Display('PaginationLast')) { ?>
<a href="<?php $objPage->Display('PaginationLast'); ?>">&gt;|</a>
<?php } ?>
<?php
}
?>
			<br>
		</div>
	</body>
</html>
