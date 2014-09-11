<?php 

//----------------------------------------------------------------------------//
// class_builder.php
//----------------------------------------------------------------------------//
/**
 * class_builder
 *
 * Accept shorthand and build classes with methods from it
 *
 * Accept shorthand and build classes with methods from it
 *
 * @file	class_builder
 * @language	PHP
 * @package	<package_name>
 * @author	Andrew White
 * @version	6.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */

class ClassBuilderJS
{
	function Process($strLine)
	{
		return FALSE;
	}
}

class ClassBuilderPHP
{
	//------------------------------------------------------------------------//
	// __Construct
	//------------------------------------------------------------------------//
	/**
	 * __Construct()
	 *
	 * Take the given input and make into an array of lines
	 *
	 * Take the given input and make into an array of lines
	 *
	 * @param	string	$strInput	The file of shorthand to convert
	 * 
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 
 
 	function __Construct()
	{
		
		// keeps track of whether or not the method or class is the first one
		// therefore don't add a closing bracket
		$this->bolFirstClass 	= TRUE;
		$this->bolFirstMethod 	= TRUE;
		
		// keeps track of tabbing space for comments
		$this->bolCommentClass 	= FALSE;
		$this->bolCommentMethod = FALSE;
 	}
 
 
   	//------------------------------------------------------------------------//
	// Process
	//------------------------------------------------------------------------//
	/**
	 * Process()
	 *
	 * Work through $arrInput and convert the shorthand
	 *
	 * Work through $arrInput and convert the shorthand
	 *
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	 
 	function Process($strLine)
	{
			$this->intLineNumber++;
			$strLine = trim(str_replace("\t", ' ', $strLine));
			$strFirstChar = strtolower(substr($strLine, 0, 1));
			$intKey = ''; // This clearly makes no sense, but without declaring it, this is what it defaults to!
			switch ($strFirstChar)
			{
				// Class
				case 'c':
					if (($this->arrOutput[$intKey] = $this->BuildClass($strLine)) === FALSE)
					{
						return FALSE;
					}
					break;
				
				// Method
				case 'm':
				case 'f':
					$this->arrOutput[$intKey] = $this->BuildMethod($strLine);
					if ($this->arrOutput[$intKey] === FALSE) 
					{
						return FALSE;
					}
					break;
				
				// Comment
				case '/':
					$this->arrOutput[$intKey] = $this->BuildComment($strLine);
					if ($this->arrOutput[$intKey] === FALSE) 
					{
						return FALSE;
					}
					break;
				
				// Blank line
				case '':
					break;
				
				// Invalid
				default:
					return FALSE;
		}
		
		$this->strOutput = '';
		foreach ($this->arrOutput as $strLine)
		{
			$this->strOutput .= $strLine;
			$this->strOutput .= "\n";
		}
		
		return $this->strOutput;
	}
	
 	//------------------------------------------------------------------------//
	// BuildClass
	//------------------------------------------------------------------------//
	/**
	 * BuildClass()
	 *
	 * Converts shorthand into a class
	 *
	 * Converts shorthand into a class
	 *
	 * @param	string	$strClassInput	The shorthand to convert
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function BuildClass($strClassInput)
	{
		$strClass = '';
		
		//add previos method and class closing bracket
		if(!$this->bolFirstMethod)
		{
			$strClassDoc .= "\n\t}";
			$strClassDoc .= "\n";
		}
		if(!$this->bolFirstClass)
		{
		$strClassDoc .= '}';
		}
		
		$strClassDoc .= "\n";
		$strClassDoc .= "\n";
		
		// set commenting info
		$this->bolCommentClass = TRUE;
		$this->bolCommentMethod = FALSE;
		
		$this->bolFirstMethod = TRUE;
		
		if($this->bolFirstClass)
		{
			// don't add closing brackets
			//echo "T $this->intLineNumber ";
			$this->bolFirstClass = FALSE;
		}
		else
		{

		}
		
		$strClass .= "\n";
		
		// split the input into separate words
		$arrClassData = explode(' ', $strClassInput);
		
		// save class name for documentation
		$strClassName = $arrClassData[1];
		
		if (!$strClassName)
		{
			$this->strError = "Missing class name on line : " . $this->intLineNumber;
			return FALSE;
		}
		
		$strClassName[0]=strtoupper($strClassName[0]);
						
		// start of the class
		$strClass .= "class $strClassName ";
		
		// check for additional parameters
		if($arrClassData[2] == 'e')
		{
			$strClassExtends = $arrClassData[3];
			$strClassExtends[0]=strtoupper($strClassExtends[0]);
			$strClass .= "extends $strClassExtends ";
			// save class extends for documentation
			
		}
		
		if($arrClassData[2] == 'i')
		{
			$strClassImplements = $arrClassData[3];
			$strClassImplements[0]=strtoupper($strClassImplements[0]);	
			$strClass .= "implements $strClassImplements";	
						
		}
		elseif($arrClassData[4] == 'i')
		{
			$strClassImplements = $arrClassData[5];
			$strClassImplements[0]=strtoupper($strClassImplements[0]);				
			$strClass .= "implements $strClassImplements";
			
		}
		
		$strClass .= " \n";
		$strClass .= '{';
		$strClassDoc .= $this->BuildClassDoc($strClassName, $strClassExtends);
		
		
		return $strClassDoc . $strClass;
	}
	
	//------------------------------------------------------------------------//
	// BuildMethod
	//------------------------------------------------------------------------//
	/**
	 * BuildMethod()
	 *
	 * Converts shorthand into a method
	 *
	 * Converts shorthand into a method
	 *
	 * @param	string	$strMethodInput	The shorthand to convert
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function BuildMethod($strMethodInput)
	{
		$strMethod = '';
		
				
		
		$arrSplitGreaterThan = explode('>', $strMethodInput, 2); 
		
		// save return type
		$strReturnType = $arrSplitGreaterThan[1];
		
		// explode the left side to get method name and parameters
		$arrMethodData = explode(' ', trim($arrSplitGreaterThan[0]));
		
		// array for saving parameters
		$arrParameters = array();
		
		
		// set commenting info
		$this->bolCommentClass = FALSE;
		$this->bolCommentMethod = TRUE;
				
		if($this->bolFirstMethod)
		{
			// don't add closing brackets
			if($arrMethodData[1] == '__construct' || $arrMethodData[1] == '__Construct')
			{
			
			}
			else
			{
				$strConstruct = $this->BuildMethod('m __construct $strVar $arrVar [$intVar]');
				$strConstruct .= "\n \t} \n \n";
			}
			
			$this->bolFirstMethod = FALSE;
		}
		else
		{
			//add method closing bracket
			$strMethodDoc .= "\t}";
			$strMethodDoc .= "\n";
			$strMethodDoc .= "\n";

		}
	
		$strMethod .= "\n";
		
		foreach($arrMethodData as $intKey=>$arrValue)
		{
			
			if($intKey == 0)
			{
			
			}
			elseif($intKey == 1)
			{
				$strMethodName = $arrMethodData[1];
				
					
				
				// check for protected
				if(substr($strMethodName, 0, 1) == "_")
				{
					
					
					if(substr($strMethodName, 1, 1) == "_")
					{
						// its magic
						$strMethodName[2]=strtoupper($strMethodName[2]);
						$strMethod .= "\t function $strMethodName(";
					}
					else
					{
						$strMethodName[1]=strtoupper($strMethodName[1]);
						$strMethod .= "\t protected function $strMethodName(";
					}
				}
				else
				{
					$strMethodName[0]=strtoupper($strMethodName[0]);
					$strMethod .= "\t function $strMethodName(";
					
				}
			}
			else
			{
				if(strlen($arrMethodData[$intKey]) < 4)
					{
						$this->strError = "Invalid parameter on line : " . $this->intLineNumber;
						return FALSE;
					}
					
				// add comma afterwards if it isn't the first parameters
				if($intKey > 2)
				{
					$strMethod .= ', ';
				}
				
				
				// check if the first character is a dollar sign
				// if not add one
				$strFirstChar = substr($arrMethodData[$intKey], 0, 1);
				if($strFirstChar == "$")
				{
					$strParameter = $arrMethodData[$intKey];
					$strParameterDoc = $strParameter;
				}
				elseif($strFirstChar == "[")
				{
					// parameter is optional
					$strSecondChar = substr($arrMethodData[$intKey], 1, 1);
					if($strSecondChar == "$")
					{
						$strParameter = $arrMethodData[$intKey];
						$strParameterDoc = $strParameter;
					}
					else 
					{
						$strParameter = '[$';
						$strParameter .= substr($arrMethodData[$intKey], 1);
						$strParameterDoc = $strParameter;
					}
				}
				else
				{
					$strParameter = "$$arrMethodData[$intKey]"; 
					$strParameterDoc = $strParameter;
				}
				
				$arrSplitEqual = explode("=", $strParameter, 2);
				
				if($arrSplitEqual[1] == NULL)
				{
					if($strFirstChar == "[")
					{
						$strParameter = substr($strParameter, 1, strlen($strParameter) - 2);
						$strParameter .= '=NULL';
						$strParameter[4]=strtoupper($strParameter[4]);
						$strParameterDoc[5]=strtoupper($strParameterDoc[5]);
					}
					else
					{
						$strParameter[4]=strtoupper($strParameter[4]);
						$strParameterDoc[4]=strtoupper($strParameterDoc[4]);
					}
				}
				else
				{
					if($strFirstChar == "[")
					{
						$strParameter = substr($strParameter, 1, strlen($strParameter) - 2);
						$strParameterDoc = $arrSplitEqual[0] . ']';
						$strParameter[5]=strtoupper($strParameter[5]);
						$strParameterDoc[5]=strtoupper($strParameterDoc[5]);
					}
					else
					{
						$strParameterDoc = $arrSplitEqual[0];
						$strParameterDoc .= "\t[optional]";
						$strParameter[4]=strtoupper($strParameter[4]);
						$strParameterDoc[4]=strtoupper($strParameterDoc[4]);
					}
				}
				
				
				
				$arrParameters[$intKey - 2] = $strParameterDoc;
				$strMethod .= $strParameter;
			}
		}
		
		$strMethod .= ')';
		$strMethod .= "\n";
		$strMethod .= "\t{";
		$strMethod .= "\n";
		
		$strMethodDoc .= $this->BuildMethodDoc($strMethodName, $arrParameters, $strReturnType);
		
		return $strConstruct . $strMethodDoc . $strMethod;
	}

	//------------------------------------------------------------------------//
	// BuildComment
	//------------------------------------------------------------------------//
	/**
	 * BuildComment()
	 *
	 * inserts a comment into the current method or class
	 *
	 * inserts a comment into the current method or class
	 *
	 * @param	string	$strCommentInput	The comment to insert
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function BuildComment($strCommentInput)
	{
		if ($strCommentInput[1]!='/')
		{
			$strCommentInput = '/'.$strCommentInput;
		}
		if($this->bolCommentClass)
		{
			$strComment = "\t";
			$strComment .= $strCommentInput;
		}
		elseif($this->bolCommentMethod)
		{
			$strComment = "\t \t";
			$strComment .= $strCommentInput;
		}
		else
		{
			$strComment = $strCommentInput;
		}
		
		return $strComment . "\n";
	}
 
	//------------------------------------------------------------------------//
	// BuildClassDoc
	//------------------------------------------------------------------------//
	/**
	 * BuildClassDoc()
	 *
	 * Builds a class doc-block
	 *
	 * Builds a class doc-block
	 *
	 * @param	string	$strClassName	The name of the class
	 * @param	string	$strClassExtends	[optional]	The name of the extended class
	 * @param	string	$strClassImplements	[optional]	The name of the implemented class
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */

	function BuildClassDoc($strClassName, $strClassExtends = NULL, $strClassImplements = NULL)
	{
		$strClassDoc = '';
		$strClassDoc .= '//----------------------------------------------------------------------------//';
		$strClassDoc .= "\n";
		$strClassDoc .= '// ';
		$strClassDoc .= $strClassName;
		$strClassDoc .= "\n";
		$strClassDoc .= '//----------------------------------------------------------------------------//';
		$strClassDoc .= "\n";
		$strClassDoc .=	'/**';
		$strClassDoc .= "\n";
		$strClassDoc .= '* ';
		$strClassDoc .= $strClassName;
		$strClassDoc .= "\n";
		$strClassDoc .= '*';
		$strClassDoc .= "\n";
		$strClassDoc .= '* <short description>';
		$strClassDoc .= "\n";
		$strClassDoc .= '*';
		$strClassDoc .= "\n";
		$strClassDoc .= '* <long description>';
		$strClassDoc .= "\n";
		$strClassDoc .= '*';
		$strClassDoc .= "\n";
		$strClassDoc .= '*';
		$strClassDoc .= "\n";
		$strClassDoc .= "* @prefix	<prefix>";
		$strClassDoc .= "\n";
		$strClassDoc .= '*';
		$strClassDoc .= "\n";
		$strClassDoc .= '* @package	<package_name>';
		$strClassDoc .= "\n";
		$strClassDoc .= "* @class	$strClassName";
		$strClassDoc .= "\n";
		if($strClassExtends)
		{
			$strClassDoc .= "* @extends \t$strClassExtends\n";
		}
		$strClassDoc .= '*/';
		$strClassDoc .= "\n";
		return $strClassDoc;
	}

	//------------------------------------------------------------------------//
	// BuildMethodDoc
	//------------------------------------------------------------------------//
	/**
	 * BuildMethodDoc()
	 *
	 * Builds one of these things
	 *
	 * Builds one of these things
	 *
	 * @param	string	$strMethodName	name of the method
	 * @param	array	$arrParameters	[optional] array of parameters
	 * @param	array	$strReturnType	[optional] return type
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */

	function BuildMethodDoc($strMethodName, $arrParameters = array(), $strReturnType = 'void')
	{
		$strMethodDoc = '';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '//------------------------------------------------------------------------//';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '// ';
		$strMethodDoc .= $strMethodName;
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '//------------------------------------------------------------------------//';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '/**';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '* ';
		$strMethodDoc .= $strMethodName;
		$strMethodDoc .= '()';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '*';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '* <short description>';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '*';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '* <long description>';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '*';
		$strMethodDoc .= "\n";
		foreach ($arrParameters as $intKey=>$arrValue)
		{
			$strMethodDoc .= "\t";
			$strMethodDoc .= '* @param';
			$strFirstChar = substr($arrParameters[$intKey], 0, 1);
			// check optional
			if($strFirstChar == '[')
			{
				$strType = substr($arrParameters[$intKey], 2, 3);
				$strParameter = substr($arrParameters[$intKey], 1, strlen($arrParameters[$intKey]) - 2);
				$strParameter .= "\t";
				$strParameter .= "[optional]";
				$strParameter .= "\t";
			}
			else
			{
				$strType = substr($arrParameters[$intKey], 1, 3);
				$strParameter = $arrParameters[$intKey];
				$strParameter .= "\t";
			}
			
			$strType = strtolower($strType);
			
			// find full name of parameter
			if($strType == 'str')
			{
				$strType = 'string';
			}
			elseif($strType == 'int')
			{
				$strType = 'integer';
			}
			elseif($strType =='bol')
			{
				$strType = 'boolean';
			}
			elseif($strType == 'arr')
			{
				$strType = 'array';
			}
			elseif($strType == 'mix')
			{
				$strType = 'mix';
			}
			else
			{
				$strType = 'type';
			}
			
			$strMethodDoc .= "\t";
			$strMethodDoc .= $strType;
			$strMethodDoc .= "\t";
			$strMethodDoc .= $strParameter;
			$strMethodDoc .= "\t";
			$strMethodDoc .= 'description';
			$strMethodDoc .= "\n";
		}
		$strMethodDoc .= "\t";
		$strMethodDoc .= '* @return ';
		$strMethodDoc .= $strReturnType;
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '*';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '*';
		$strMethodDoc .= "\n";
		$strMethodDoc .= "\t";
		$strMethodDoc .= '* @method';
		$strMethodDoc .= "\n";			
		$strMethodDoc .= "\t";
		$strMethodDoc .= '* @see	<MethodName()||typePropertyName>';
		$strMethodDoc .= "\n";	
		$strMethodDoc .= "\t";
		$strMethodDoc .= '*/';
		$strMethodDoc .= "\n";	
		
		return $strMethodDoc;
	}

 	//------------------------------------------------------------------------//
	// OutputString
	//------------------------------------------------------------------------//
	/**
	 * OutputString()
	 *
	 * Output the converted shorthand as a string
	 *
	 * Output the converted shorthand as a string
	 *
	 * @param	<type>	<$name>	[optional] <description>
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function OutputString() 
	{
		echo $this->strOutput;
	}
 
}
 
 
//----------------------------------------------------------------------------//
// ClassBuilder
//----------------------------------------------------------------------------//
/**
 * ClassBuilder
 *
 * Build a class from the given input
 *
 * Build a class from the given input
 *
 *
 * @prefix	<prefix>
 *
 * @package	<package_name>
 * @parent	<full.parent.path>
 * @class	<ClassName||InstanceName>
 * @extends	<ClassName>
 */
 
 class ClassBuilder {
 
 	//------------------------------------------------------------------------//
	// __Construct
	//------------------------------------------------------------------------//
	/**
	 * __Construct()
	 *
	 * Take the given input and make into an array of lines
	 *
	 * Take the given input and make into an array of lines
	 *
	 * @param	string	$strInput	The file of shorthand to convert
	 * 
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 
 
 	function __Construct($strInput)
	{
		$this->arrInput = explode("\n", $strInput);
		
		// load language modules
		$this->_arrModules = Array();
		$this->_arrModules['php'] = new ClassBuilderPHP();
		$this->_arrModules['js'] = new ClassBuilderJS();
		
		// set defaflt language
		$this->strLanguage = 'php';
		
		// keeps track of whether or not the method or class is the first one
		// therefore don't add a closing bracket
		$this->bolFirstClass 	= TRUE;
		$this->bolFirstMethod 	= TRUE;
		
		// keeps track of tabbing space for comments
		$this->bolCommentClass 	= FALSE;
		$this->bolCommentMethod = FALSE;
 	}
 
 
   	//------------------------------------------------------------------------//
	// Process
	//------------------------------------------------------------------------//
	/**
	 * Process()
	 *
	 * Work through $arrInput and convert the shorthand
	 *
	 * Work through $arrInput and convert the shorthand
	 *
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	 
 	function Process()
	{
		$this->intLineNumber = 0;
		$this->arrOutput = array();
		
		foreach ($this->arrInput as $intKey=>$strLine)
		{
			$this->intLineNumber++;
			$strLine = trim(str_replace("\t", ' ', $strLine));
			$strFirstChar = strtolower(substr($strLine, 0, 1));
			switch ($strFirstChar)
			{	
				// Blank lines and ignored comments
				case '':
				case '#':
					break;
				
				// Language
				case 'l':
					$arrLanguage = explode(' ', $strLine);
					switch (trim(strtolower($arrLanguage[1])))
					{
						// javascript
						case 'js':
						case 'javascript':
							$this->strLanguage = 'js';
							break;
						
						// php
						case 'php':
							$this->strLanguage = 'php';
							break;
						
						// invalid
						default:
							return FALSE;
					}
					break;
				
				// process line
				default:
					if (($this->arrOutput[$intKey] = $this->_arrModules[$this->strLanguage]->Process($strLine)) === FALSE)
					{
						return FALSE;
					}	
			}
		}
		
		
		
		
		$this->strOutput = '';
		foreach ($this->arrOutput as $strLine)
		{
			$this->strOutput .= $strLine;
			$this->strOutput .= "\n";
		}
		
		// close the class and methods
		$this->strOutput .= "\t}";
		$this->strOutput .= "\n";
		$this->strOutput .= '}';
		
		return $this->strOutput;
	}
}
 
 ?>
