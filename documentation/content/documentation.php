
<?php

$path = "content/reference/";
$template_dir = "templates/reference/";
$link = "index.php?page=documentation&doc=";

// validate the requested document
$doc = trim(preg_replace('/[^a-z0-9\-_.]/i', '', $_REQUEST['doc']));

if (!$doc)
{
	$doc = 'index';
}

$name = $doc;
$doc = $doc.'.php';

// read the document
$loaded = @include($path.$doc);
if (!$loaded)
{
	unset($document);
}

$package = $document['package'];
$class = $document['class'];
if (trim($document['parent']))
{
	$instance = "{$document['parent']}.{$document['class']}";
}
else
{
	$instance = $document['class'];
}
if (!trim($document['long_description']) && trim($document['short_description']))
{
	$document['long_description'] = $document['short_description'];
}

if ($document['language'] == 'PHP')
{
	$join = '->';
}
else
{
	$join = '.';
}

// build menu
unset($menu);
$menu['Vixen Home'] = 'index.php';
$menu['Documentation Home'] = 'index.php?page=documentation';

// build output


// check that we have a valid document
if (is_array($document))
{

	// work out what the document is
	$type = trim($document['docblock_type']);

	// load the template
	if ($type)
	{
		require($template_dir.$type.'.tmpl.php');
	}
	else
	{
		// error
		require($template_dir.'error.tmpl.php');		
	}
}
else
{
	require($template_dir.'error.tmpl.php');
}

// build menu
unset($document);
$lclass = trim(preg_replace('/[^a-z0-9\-_.]/i', '', $class));

switch ($type)
{
	case 'package':
		// show list of packages
		$loaded = @include($path.'index.php');
		if ($loaded && is_array($document))
		{
			foreach ($document['package'] as $key=>$package)
			{
				$menu['&nbsp;&nbsp;'.$package] = "{$link}package.$package";
			}
		}
		break;
		
	case 'class':
		$menu[$package] = "{$link}package.$package";
		// show list of classes
		$loaded = @include("{$path}package.$package.php");
		if ($loaded && is_array($document))
		{
			foreach ($document['class'] as $class=>$description)
			{
				$menu['&nbsp;&nbsp;'.$class] = "{$link}$package.$class";
			}
		}
		break;
	
	case 'property':
	case 'method':
		$menu[$package] = "{$link}package.$package";
		$menu['&nbsp;&nbsp;'.$class] = "{$link}$package.$class";
		// show list
		$loaded = @include("$path$package.$lclass.php");
		if ($loaded && is_array($document))
		{
			foreach ($document[$type] as $key=>$value)
			{
				$menu['&nbsp;&nbsp;&nbsp;&nbsp;'.$key] = "{$link}$package.$instance.$key";
			}
		}
		break;
		
	case 'variable':		
	case 'function':
	case 'constant':
		$menu[$package] = "{$link}package.$package";
		// show list
		$loaded = @include("{$path}package.$package.php");
		if ($loaded && is_array($document))
		{
			foreach ($document[$type] as $key=>$value)
			{
				$menu['&nbsp;&nbsp;'.$key] = "{$link}$package.$key";
			}
		}
		break;
		
	
}

// modify the menu (if needed)
if (is_array($menu))
{
	$GLOBALS['menu'] = $menu;
}


?>
