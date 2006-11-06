<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// WARNING : Distribution of this file may be illegal.
//
// Portions of this file are covered by an open source license that
// does not allow distribution as part of a proprietary (non open source)
// application.
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Documentor
//----------------------------------------------------------------------------//
/**
 * documentor
 *
 * Documentation Generator
 *
 * Generates Documentation from in-file doc-blocks
 * This file contains some of the nastiest most badly
 * commented code ever written, ironic really isn't it
 *
 * This has been taken from the APhPLIX project and is GPL licensed
 *
 * @file		doc.php
 * @language	PHP
 * @package		doc
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	aphplix.org
 * @license		GPL
 * @public
 */
 
 
 
 	//----------------------------------------------------------------------------//
	// Config
	//----------------------------------------------------------------------------//
	
	// add a directory to be documented (NON-Recursive)
	//$file[] = '/full/path/to/directory';
	
	// add a single file to be documented
	//$file[] = '/full/path/to/file.php';
	
	// input locations
	$file[] = '';
	$file[] = '/home/flame/vixen/framework';
	$file[] = '/home/flame/vixen/normalisation_app';
	$file[] = '/home/flame/vixen/normalisation_app/normalisation_modules';
	$file[] = '/home/flame/vixen/client_app';

	// output location (include trailing slash)
	$output = '/home/flame/vixen/documentation/content/reference/';


	//----------------------------------------------------------------------------//
	// Logic
	//----------------------------------------------------------------------------//
	
	// define constants
	define("OUTPUT_LOCATION", $output);
	
	// build documentation
	$docbook = document($file);
	
	
	
	//----------------------------------------------------------------------------//
	// Functions
	//----------------------------------------------------------------------------//
	
	
	
	//----------------------------------------------------------------------------//
	// Document Files
	//----------------------------------------------------------------------------//
	function document($files)
	{
		// read in all files
		if (!is_array($files))
		{
			$files = array($files);
		}
		
		foreach($files as $key=>$value)
		{
			unset($read);
			if (is_dir($value))
			{
				// get a list of files in the dir
				if ($handle = opendir($value))
				{
					while (false !== ($file = readdir($handle)))
					{
						if ($file != "." && $file != "..")
						{
							if (is_file($value.'/'.$file))
							{
								$read[] = $value.'/'.$file;
							}
						}
					}
					closedir($handle);
				}
				else
				{
					echo "could not open dir : $value<br>";
					continue;
				}
			}
			elseif(is_file($value))
			{
				$read = array($value);
			}
			else
			{
				echo "file not found : $value<br>";
				continue;
			}
			foreach($read as $rkey=>$file)
			{
				$doc_files[] = docu_read_file($file);
				echo "reading : $file<br>";
			}
			echo "DONE<br>";
		}
		
		// build the master array
		foreach($doc_files as $key=>$file)
		{
			$use = FALSE;
			unset($class);
			unset($instance);
			unset($package);
			unset($class_package);
			if (!is_array($file))
			{
				continue;
			}
			foreach($file as $dkey=>$docblock)
			{
				if ($use == FALSE)
				{
					// only use files with a 'file' docblock
					if ($docblock['docblock_type'] == 'file')
					{
						$use = TRUE;
						$package = $docblock['package'];
						$language = $docblock['language'];
						$master[$package]['file'][$docblock['file']] = $docblock;
					}
				}
				else
				{
					// DON'T ADD PRIVATE
					if ($docblock['private'] === TRUE)
					{
						continue;
					}
				
					if (!trim($docblock['language']))
					{
						$docblock['language'] = $language;
					}
				
					switch($docblock['docblock_type'])
					{
						case 'class':
							$class = $docblock['title'];
							$instance = $class;
							$class_package = $docblock['package'];
							if ($docblock['class'])
							{
								$instance = $docblock['class'];
								if ($docblock['parent'])
								{
									$instance = $docblock['parent'].'.'.$instance;
								}
							}
							$master[$class_package]['class'][$instance] = $docblock;
							break;
							
						case 'property':
							$docblock['class'] = $instance;
							$docblock['package'] = $class_package;
							$master[$class_package]['class'][$instance]['property'][$docblock['title']] = $docblock;
							break;
						case 'method':
							$docblock['class'] = $instance;
							$docblock['package'] = $class_package;
							$master[$class_package]['class'][$instance]['method'][$docblock['title']] = $docblock;
							break;
							
						case 'function':
							$docblock['package'] = $package;
							$master[$package]['function'][$docblock['title']] = $docblock;
							break;
						case 'variable':
							$docblock['package'] = $package;
							$master[$package]['variable'][$docblock['title']] = $docblock;
							break;
						case 'constant':
							$docblock['package'] = $package;
							$master[$package]['constant'][$docblock['title']] = $docblock;
							break;
							
						default:
					}
				}
			}
		}
		
		$main_index = array();
		$main_index['docblock_type'] = 'index';
		
		// build package index
		foreach ($master as $pkey=>$package)
		{
			// build main index
			$main_index[package][] = $pkey;
			
			if (is_array($package['class']))
			{
				foreach ($package['class'] as $key=>$class)
				{
					// build class index
					$package['class'][$key] = $class['short_description'];
					
					if (is_array($class['property']))
					{
						foreach ($class['property'] as $ckey=>$property)
						{
							// build property page
							docu_write_file("$pkey.$key.$ckey.php", $property);
							
							// build property index
							$class['property'][$ckey] = $property['short_description'];
						}
						ksort($class['property']);
					}
					
					if (is_array($class['method']))
					{
						foreach ($class['method'] as $ckey=>$method)
						{
							// build method page
							docu_write_file("$pkey.$key.$ckey.php", $method);
							
							// build method index
							$class['method'][$ckey] = $method['short_description'];
						}
						ksort($class['method']);
					}
					
					// build class page
					docu_write_file("$pkey.$key.php", $class);
				}
				ksort($package['class']);
			}
			
			if (is_array($package['variable']))
			{
				foreach ($package['variable'] as $key=>$variable)
				{
					// build variable index
					$package['variable'][$key] = $variable['short_description'];
					
					// build variable page
					docu_write_file("$pkey.$key.php", $variable);
				}
				ksort($package['variable']);
			}
			
			if (is_array($package['constant']))
			{
				foreach ($package['constant'] as $key=>$constant)
				{
					// build constant index
					$package['constant'][$key] = $constant['short_description'];
					
					// build variable page
					docu_write_file("$pkey.$key.php", $constant);
				}
				ksort($package['constant']);
			}
			
			if (is_array($package['function']))
			{
				foreach ($package['function'] as $key=>$function)
				{
					// build function index
					$package['function'][$key] = $function['short_description'];
					
					// build function page
					docu_write_file("$pkey.$key.php", $function);
				}
				ksort($package['function']);
			}
			
			// package contents
			$package['docblock_type'] = 'package';
			$package['package'] = $pkey;
			docu_write_file("package.$pkey.php", $package);
		}
		
		// main index
		docu_write_file("index.php", $main_index);
		
	}



	//----------------------------------------------------------------------------//
	// Write File
	//----------------------------------------------------------------------------//
	function docu_write_file($file, $contents)
	{
		$serial = serialize($contents);
		$data = 
"<?php
\$document = unserialize(stripslashes('".addslashes($serial)."'));
?>";
		
		// sanitize the filename
		$file = str_replace('->', '.', $file);
		$file = trim(preg_replace('/[^a-z0-9\-_.]/i', '', $file));
		
		$filename = OUTPUT_LOCATION.$file;
		
		return file_put_contents($filename, $data);
	}



	//----------------------------------------------------------------------------//
	// Read File
	//----------------------------------------------------------------------------//
	function docu_read_file($file)
	{
		$matches = array();
		$output = '';
		
		// read in file
		$file_contents = file_get_contents($file);

		// find all documentation comments
		$pattern = '/\/\*\*[\s\S]*?\*\//';
		preg_match_all ($pattern, $file_contents, $matches);
		
		foreach ($matches[0] as $key=>$value)
		{
			$output[] = decode_docblock($value);
		}
		
		// return output
		return $output;
	}
	
	
	//----------------------------------------------------------------------------//
	// Decode DocBlock
	//----------------------------------------------------------------------------//
	function decode_docblock($docblock)
	{	
		// split on \n
		$docblock = trim($docblock,'/');
		$bits = explode("\n",$docblock);
		$mode = 'title';
		
		foreach ($bits as $key=>$value)
		{
			// work out the line (mode) we are reading
			$value = ltrim($value);
			$value = ltrim($value,'*');
			$test = ltrim($value);
			if ($test[0] == '@')
			{
				$test = ltrim($test,'@');
				$test = ltrim($test);
				$mode_array = preg_split("/[\s]+/", $test);
				$mode = $mode_array[0];
				$line = $mode;
				$value = substr($test, (strlen($mode)+1));
			}
			else
			{
				$line = FALSE;
			}

			$trim_value = trim($value);
			
			unset($param);

			switch ($mode)
			{
				case 'title':
					if ($trim_value)
					{
						$return[$mode] = trim($trim_value,'()');
						$title = substr(strrchr($return[$mode], "."), 1);
						if ($title)
						{
							$return[$mode] = $title;
						}
						$mode = 'short_description';
					}
					break;
				
				case 'short_description':
					if ($trim_value)
					{
						$return[$mode] = $trim_value;
						$mode = 'long_description';
					}
					break;
					
				case 'long_description':
					if ($trim_value || $return[$mode])
					{
						$return[$mode] .= $value."\n";
					}
					break;
				
				case 'param':
					if ($trim_value)
					{
						if ($line)
						{
							$value_array = preg_split("/[\s]+/", $trim_value);
							$param['type'] = trim(array_shift($value_array),'{}');
							$param['name'] = array_shift($value_array);
							if ($value_array[0] == 'optional')
							{
								$param['optional'] = TRUE;
							}
							$description = trim(implode(' ', $value_array));
							if ($description)
							{
								$param['description'] = $description;
							}
							$return[$mode][] = $param;
						}
						else
						{
							$param = array_pop($return[$mode]);
							if ($param['description'])
							{
								$param['description'] .= ' '.$trim_value;
							}
							else
							{
								$param['description'] = $trim_value;
							}
							$return[$mode][] = $param;
						}
					}
					break;
					
				case 'return':
					if ($trim_value)
					{
						$value_array = preg_split("/[\s]+/", $trim_value);
						$param['type'] = array_shift($value_array);
						$description = trim(implode(' ', $value_array));
						if ($description)
						{
							$param['description'] = $description;
						}
						$return[$mode] = $param;
					}
					break;
					
				case 'see':
					if ($trim_value)
					{
						$return[$mode][] = $trim_value;
					}
					break;
				
				case 'ignore':
					return FALSE;
					break;
				
				default:
					if (!$trim_value && !$return[$mode])
					{
						$return[$mode] = TRUE;
					}
					elseif ($trim_value && $return[$mode] && $return[$mode] !== TRUE)
					{
						$return[$mode] .= ' '.$trim_value;
					}
					elseif ($trim_value)
					{
						$return[$mode] = $trim_value;
					}
			}
			
		}
		
		if ($return['long_description'])
		{
			$return['long_description'] = trim($return['long_description']);
		}
		
		// determine docblock type
		if ($return['method'] === TRUE)
		{
			$return['docblock_type'] = 'method';
		}
		elseif ($return['property'] === TRUE)
		{
			$return['docblock_type'] = 'property';
		}
		elseif ($return['function'] === TRUE)
		{
			$return['docblock_type'] = 'function';
		}
		elseif ($return['variable'] === TRUE)
		{
			$return['docblock_type'] = 'variable';
		}
		elseif ($return['constant'] === TRUE)
		{
			$return['docblock_type'] = 'constant';
		}
		elseif ($return['class'] || $return['parent'])
		{
			$return['docblock_type'] = 'class';
		}
		elseif ($return['file'])
		{
			$return['docblock_type'] = 'file';
		}
		elseif ($return['return'] || $return['param'])
		{
			$return['docblock_type'] = 'method';
		}
		elseif ($return['type'])
		{
			$return['docblock_type'] = 'property';
		}
		else
		{
			$return['docblock_type'] = 'unknown';
		}
		
		return $return;
	}
?>
