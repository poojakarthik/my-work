<?php
//class JSON_Handler_Javascript_Module extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
class JSON_Handler_Javascript_Module extends JSON_Handler implements JSON_Handler_Catchable {
//class JSON_Handler_Javascript_Module extends JSON_Handler {
	
	const	STANDARD_MODULES_1		= 'Modules/1';
	const	STANDARD_MODULES_2D8	= 'Modules/2.0d8';
	
	const	MODULE_SHARED_APP	= '/ui/';
	const	MODULE_RELATIVE_DIR	= '/javascript/modules/';
	
	const	ERROR_MESSAGE_FRIENDLY	= "There was an error loading a software dependency.  If this problem occurs more than once, please contact the system administrator.";
	
	public function get($aModuleIdentifiers, $bResolveDependencies=false, $aIgnoreDependencies=array()) {
		/*try {
			echo "BIG TEST, YO";
			echo "Parameters: " . print_r(array(
				'aModuleIdentifiers' => $aModuleIdentifiers,
				'bResolveDependencies' => $bResolveDependencies,
				'aIgnoreDependencies' => $aIgnoreDependencies
			), true);
			echo "Results: " . print_r(self::getModule($aModuleIdentifiers, $bResolveDependencies, $aIgnoreDependencies), true);
			echo "Log: " . $this->_sJSONDebug;
		} catch (Exception $oException) {
			echo "Exception" . print_r($oException, true);
		}
		die;*/
		return self::getModule($aModuleIdentifiers, $bResolveDependencies, $aIgnoreDependencies);
	}
	
	public function getAsModules2d8($aModuleIdentifiers, $bResolveDependencies=false) {
		// TODO
	}
	
	protected static function _findModules2d8Dependencies($sSource) {
		// TODO
	}

	public static function getModule($aModuleIdentifiers, $bResolveDependencies, $aIgnoreDependencies) {
		Log::get()->log("Loading modules");

		$aIgnoreDependencies 	= ($aIgnoreDependencies ? $aIgnoreDependencies : array());
		$aModuleSources			= array();
		reset($aModuleIdentifiers);
		while (list($iKey, $sModuleIdentifier) = each($aModuleIdentifiers)) {
			Log::get()->log("Checking source for: {$sModuleIdentifier}");
			$aModuleSources[$sModuleIdentifier]	= self::_getJavascriptSource($sModuleIdentifier);
			
			// Do static analysis for module dependencies
			// This means we can load all dependent files in one request, saving trips
			if ($bResolveDependencies) {
				Log::get()->log("Finding dependencies for: {$sModuleIdentifier}");
				$aDependencies	= self::_findRequires($aModuleSources[$sModuleIdentifier]);
				foreach ($aDependencies as $sDependencyIdentifier) {
					// Dependency Identifiers are optionally relative, so we need to handle them
					$sRealDependencyIdentifier	= self::_realIdentifier($sDependencyIdentifier, $sModuleIdentifier);
					Log::get()->log("Checking: {$sRealDependencyIdentifier}");
					// Add the Dependency to our list of Modules to load (if it already isn't)
					if (!in_array($sRealDependencyIdentifier, $aModuleIdentifiers)) {
						// var_dump($sRealDependencyIdentifier);
						$aModuleIdentifiers[]	= $sRealDependencyIdentifier;
						Log::get()->log("Appending: {$sRealDependencyIdentifier}");
					}
				}
			}
		}
		
		// Remove any Modules that have already been provided to the requesting environment
		if ($aIgnoreDependencies) {
			foreach ($aIgnoreDependencies as $sIgnoredDependency) {
				if (isset($aModuleSources[$sIgnoredDependency])) {
					unset($aModuleSources[$sIgnoredDependency]);
				}
			}
		}
		
		return $aModuleSources;
	}

	// _getJavascriptSource: Returns file contents
	protected static function _getJavascriptSource($sModuleIdentifier) {
		// Get a list of our supported paths: Shared App + CWD
		// CWD has higher priority than the Shared App
		$aPaths	= array(
			realpath(getcwd().self::MODULE_RELATIVE_DIR),
			realpath(getcwd().'/../'.self::MODULE_SHARED_APP.self::MODULE_RELATIVE_DIR)
		);
		
		$sModuleIdentifier	= trim(rtrim($sModuleIdentifier, '/'));
		
		// Loop through our search paths
		foreach ($aPaths as $sBasePath) {
			// The path must match exactly.  No fancy alternate paths like with PHP files.
			$sPath	= $sBasePath.'/'.$sModuleIdentifier.'.js';
			if (file_exists($sPath) && is_readable($sPath)) {
				return file_get_contents($sPath);
			}
		}
		
		throw new JSON_Handler_Exception_Javascript_Module_NoSuchModule($sModuleIdentifier, $aPaths);
		/*throw new Exception("Unable to load Javascript Module {$sModuleIdentifier}. Search paths are: ".print_r($aPaths, true));*/
	}
	
	// _realIdentifier: Returns the real identifer
	protected static function _realIdentifier($sIdentifier, $sBaseIdentifier='') {
		// Debug
		// Log::get()->log("function call: _realIdentifier({$sIdentifier}, {$sBaseIdentifier})");
		$sFullIdentifier	= $sIdentifier;
		if ($sIdentifier[0] === '.') {
			// Relative identifier
			$sFullIdentifier	= $sBaseIdentifier.'/../'.$sIdentifier;
		}
		
		// Break Identifier into terms
		$aIdentifier	= explode('/', $sFullIdentifier);
		
		$aRealIdentifier	= array();
		foreach ($aIdentifier as $sTerm) {
			if (!$sTerm) {
				continue;
			}
			
			switch ($sTerm) {
				case '..':
					// Parent Directory
					if (!count($aRealIdentifier)) {
						throw new JSON_Handler_Exception_Javascript_Module_IdentifierJailbreak($sIdentifier, $sBaseIdentifier);
					}
					array_pop($aRealIdentifier);
					break;
				
				case '.':
					// Same directory
					continue;
					break;
				
				default:
					// Other Term
					$aRealIdentifier[]	= $sTerm;
					break;
			}
		}
		
		// NOTE: If the path should be pointing to a module, then the Caller should check that the RealIdentifier !== ''
		return implode('/', $aRealIdentifier);
	}

	// _findRequires: Searches for javascript files that need inclusion, require('js/file')
	protected static function _findRequires($sSource) {
		// Look for statements that look pretty much anything like require().  Be conservative to so as to avoid errors.
		// FIXME: If your require() is commented out, then it will still be accepted!
		// FIXME: If your require() has a comment within it (that is still syntactically correct), it will be ignored!
		// FIXME: Very strict matching rules: MUST be in the form require('module/path/here'), with no spaces (single or double quotes allowed)
		$aMatches = array();
		preg_match_all('/require\(([\'"])(((\.{1,2}|[a-zA-Z0-9\-\_]+)\/)*([a-zA-Z0-9\-\_]+))(\1)\)/', $sSource, $aMatches);
		$aDependencies = $aMatches[2];		
		return $aDependencies;
	}
}

class JSON_Handler_Exception_Javascript_Module_NoSuchModule extends Exception implements JSON_Handler_Exception {
	
	public function __construct($sModuleIdentifier, $aPaths=array()) {
		$this->_sModuleIdentifier	= $sModuleIdentifier;
		$this->_aPaths				= $aPaths;
		
		parent::__construct($this->getFriendlyMessage());
	}
	
	public function getFriendlyMessage() {
		return JSON_Handler_Javascript_Module::ERROR_MESSAGE_FRIENDLY;
	}
	
	public function getDetailedMessage() {

		return "Unable to load Javascript Module '{$this->_sModuleIdentifier}'.  The following paths were checked: ".(implode('; ', $this->_aPaths));
	}
	
	public function getData() {
		return null;
	}
}

class JSON_Handler_Exception_Javascript_Module_IdentifierJailbreak extends Exception implements JSON_Handler_Exception {
	
	public function __construct($sModuleIdentifier, $sRelativePath) {
		$this->_sModuleIdentifier	= $sModuleIdentifier;
		$this->_sRelativePath		= $sRelativePath;
		
		parent::__construct($this->getFriendlyMessage());
	}
	
	public function getFriendlyMessage() {
		return JSON_Handler_Javascript_Module::ERROR_MESSAGE_FRIENDLY;
	}
	
	public function getDetailedMessage() {
		return "Javascript Module Identifier '{$this->_sModuleIdentifier}' breaks its relative jail of '{$this->_sRelativePath}'";
	}
	
	public function getData() {
		return null;
	}
}
?>