<?php

class Application_Handler_Package extends Application_Handler
{
	private $sJsPath;
	private $sPackageName;




	// Handle a request for the home page of the Sales (Flex) system
	public function Load($sPackageName)
	{
		$this->sJsPath = Flex::applicationBase()."javascript/";
		$this->sPackageName = $sPackageName[0];

		$aProcessedFiles = array();
		if ($this->isWildCardRequest($sPackageName[0]))
		{
			$sPackage = $this->stripWildCardChars($sPackageName[0]);
			$aProcessedFiles = $this->processPackageDefinitionFile($sPackage);
			$this->loadAllInPackage($this->sJsPath.strtolower($sPackage), $aProcessedFiles);
		}
		else
		{
			$this->processPackageDefinitionFile($sPackageName[0]);

		}
		echo 'FW.bSuppressRequires = false;';
		die;

	}


	private function isWildCardRequest($sPackageSpecification)
	{
		if (preg_match("/\*$/",$sPackageSpecification ))
		{
			return true;
		}
		return false;
	}

	 private function stripWildCardChars($sPackage)
	{
		return preg_replace("/\.\*$/", '',$sPackage);
	}

	private function processPackageDefinitionFile($sPackage)
	{
		$sFile = $sPackage.".package.json";
		$aProcessedFiles = array();
		if ($sPackage!='FW')
			echo 'FW.bSuppressRequires = true;';


		if (file_exists($this->sJsPath.strtolower($sPackage)."/$sFile"))
		{
			$oPackageConfig = json_decode(file_get_contents($this->sJsPath.strtolower($sPackage)."/$sFile"));

			if (count($oPackageConfig->pre)>0)
				$aProcessedFiles = array_merge($aProcessedFiles, $this->parseFiles($oPackageConfig->pre));

			$this->parseFiles($sPackage,$sJsPath);

			if (count($oPackageConfig->post)>0)
				$aProcessedFiles = array_merge($aProcessedFiles, $this->parseFiles($oPackageConfig->post));
		}
		return $aProcessedFiles;
	}


	private function parseFiles($aFiles)
	{
		$aProcessedFiles = array();
		if (!is_array($aFiles))
		{
			$aFiles = array($aFiles);
		}

		foreach($aFiles as $sFile)
		{
			if ($this->isWildCardRequest($sFile))
			{
				$sFile = $this->stripWildCardChars($sFile);
				$sFilePath = $this->convertPackageToFile($sFile);
				if (!in_array($sFilePath,$aProcessedFiles))
				{
					array_push($aProcessedFiles,$sFilePath);
					$sFileString = file_get_contents($sFilePath);
					$this->doOutPut($sFileString);
				}
				$temp = loadAllInPackage($this->convertPackageToDirectory($sFile), $aProcessedFiles);
				$aProcessedFiles = array_merge($aProcessedFiles, $temp);
			}
			else
			{
				$sFilePath = $this->convertPackageToFile($sFile);
				if (!in_array($sFilePath,$aProcessedFiles ))
				{
					array_push($aProcessedFiles,$sFilePath);
					$sFileString = file_get_contents($sFilePath);
					$this->doOutPut($sFileString);
				}
			}
		}

		return $aProcessedFiles;
	}

	private function convertPackageToDirectory($sFile)
	{
		$aPackageTokens =explode(".", $sFile);
		$sScriptPath = $this->sJsPath;
		for ($x =0; $x<count($aPackageTokens)-1; $x++)
		{
			$sScriptPath.= strtolower($aPackageTokens[$x]).'/';
		}

		return $sScriptPath.strtolower($aPackageTokens[$x]);
	}



	private function convertPackageToFile($sFile)
	{
		$aPackageTokens =explode(".", $sFile);
		$sScriptPath = $this->sJsPath;
		for ($x =0; $x<count($aPackageTokens)-1; $x++)
		{
			$sScriptPath.= strtolower($aPackageTokens[$x]).'/';
		}
		$sScriptPath.=$sFile.'.js';
		return $sScriptPath;
	}

 	private function loadAllInPackage($starting_directory = '/', $aAlreadyLoadedFiles)
	{
		if (file_exists($starting_directory))
		{
			if($oDirectory = dir($starting_directory))
			{
				while($this_entry = $oDirectory->read())
				{
					if (!preg_match("/^\./" ,$this_entry))
					{

						if(is_dir("$starting_directory/$this_entry"))
						{
							$aAlreadyLoadedFiles = array_merge($aAlreadyLoadedFiles, loadAllInPackage("$starting_directory/$this_entry", $aAlreadyLoadedFiles));
						}
						else
						{
							if (preg_match("/.js$/", "$starting_directory/$this_entry") && !(in_array("$starting_directory/$this_entry",$aAlreadyLoadedFiles)))
							{
								$this->doOutPut(file_get_contents("$starting_directory/$this_entry"));
								array_push($aAlreadyLoadedFiles,"$starting_directory/$this_entry" );
							}
						}

					}

				}

				$oDirectory->close();
			}
			else
			{
				echo "Error!  Couldn't load .* files. Could not read directory $starting_directory for some reason!";
				exit;
			}

		}
		return $aAlreadyLoadedFiles;
	}


	private function doOutPut($sFileString)
	{
		if ($GLOBALS['bDebug'])
		{

			echo $sFileString;

		}
		else
		{
			//strips out inline comments - we won't this one for now as it is not fully safe
			//$sFileString = preg_replace('/\/\/.*/', "\n", $sFileString);
			//strips out block comments
			$sFileString = preg_replace('/\/\*([^\/]*)\*\/(\s+)/s', "\n", $sFileString);
			//strips out redundant line breaks
			$sFileString = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n",$sFileString);
			//strips out tabs
			//$sFileString = preg_replace( '[\t]','',$sFileString);
			echo $sFileString;
		}

	}




}

?>
