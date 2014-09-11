<?php
require_once('../../lib/classes/Flex.php');
Flex::load();

$aConfig = LoadApplication();

// Arguments
$aArgs = _arguments(array(
	'i' => array(
		'alias' => 'carrier-module-id'
	),
	'c' => array(
		'alias' => 'module-carrier'
	),
	'm' => array(
		'alias' => 'module-class'
	),
	'n' => array(
		'alias' => 'file-name',
		'default' => '' // Default is to not have a filename
	)
), $argv);

// Carrier Module
if ($aArgs['i']) {
	$aCarrierModule = Query::run('
		SELECT		cm.*,
					c.name AS carrier_name
		FROM		CarrierModule cm
					JOIN Carrier c ON (
						c.Id = cm.Carrier
					)
		WHERE		cm.Id = <carrier-module-id>
		ORDER BY	cm.Id DESC
	', $aArgs)->fetch_assoc();
} elseif ($aArgs['c'] && $aArgs['m']) {
	$aCarrierModule = Query::run("
		SELECT		cm.*,
					c.name AS carrier_name
		FROM		CarrierModule cm
					JOIN carrier_module_type cmt ON (
						cmt.id = cm.Type
						AND cmt.const_name = 'MODULE_TYPE_NORMALISATION_CDR'
					)
					JOIN Carrier c ON (
						c.Id = cm.Carrier
						AND (
							c.Id = <module-carrier>
							OR c.Name LIKE <module-carrier>
						)
					)
		WHERE		cm.Module = <module-class>
		ORDER BY	cm.Id DESC
	", $aArgs)->fetch_assoc();
} else {
	throw new Exception('You must supply either a Carrier Module Id (-i) or a Carrier/Class pair (-c, -m)');
}

_log('Using CarrierModule #%d (%s: %s [%s])', $aCarrierModule['Id'], $aCarrierModule['carrier_name'], $aCarrierModule['description'], $aCarrierModule['Module']);

$sNormaliser = $aCarrierModule['Module'];
$oNormaliser = new $sNormaliser($aCarrierModule['Carrier']);

_log('Testing %d CDR data strings:', count($aArgs['_']));
foreach ($aArgs['_'] as $i=>$sCDRData) {
	_log('  #%d:', $i + 1);
	_log($oNormaliser->Normalise(array(
		'Carrier' => $aCarrierModule['Carrier'],
		'FileName' => $aArgs['file-name'],
		'CDR' => $sCDRData,
		'DestinationCode' => 0
	)));
}

exit(0);












function _log() {
	$aArgs = func_get_args();

	$sMessage = '';
	if (count($aArgs)) {
		$aArgsAsStrings = array();
		if (is_string($aArgs[0]) && count($aArgs) > 1 && ($iElementCount = preg_match_all('/%[bcdeEufFgGosxX]/', $aArgs[0], $aMatches = array()))) {
			// First argument is a sprintf pattern
			$sPattern = array_shift($aArgs);
			$aElements = array_splice($aArgs, 0, $iElementCount);
			$sMessage = call_user_func_array('sprintf', array_merge(array($sPattern), $aElements));

			// Fall through to allow remaining arguments to be rendered normally
			$aArgsAsStrings []= $sMessage;
		}

		foreach ($aArgs as $mArg) {
			$aArgsAsStrings []= is_scalar($mArg) ? var_export($mArg, true) : print_r($mArg, true);
		}
		$sMessage = implode(' ', $aArgsAsStrings);
	}
	fwrite(STDOUT, $sMessage . "\n");
}

function _arguments(array $aOptions, $aArgs) {
	// First argument is the script name
	$sScriptName = array_shift($aArgs);
	
	$aNonOptions = array();
	$aParsed = array();

	// Map aliases
	$aAliasMap = array();
	foreach ($aOptions as $sShort=>$aOption) {
		if (isset($aOption['alias'])) {
			$aAliasMap[$aOption['alias']] = $sShort;
		}
	}

	// Parse
	$bFlagsComplete = false;
	$sContext = null;
	foreach ($aArgs as $sArgument) {
		if ($sArgument === '--') {
			// All args after now are non-options
			$sContext = null;
			$bFlagsComplete = true;
			continue;
		}

		$aMatches = array();
		if (!$bFlagsComplete && preg_match('/^((-(?<short>([a-z0-9]+)))|(--(?<long>[a-z0-9]+(-[a-z0-9]+)?))(<?value>=.*)?)$/', $sArgument, $aMatches)) {
			// Named option
			if (isset($aMatches['short'])) {
				// Short option (possibly grouped)
				$aShorts = str_split($aMatches['short']);
				foreach ($aShorts as $sShort) {
					$aParsed[$sShort] = true;
				}
				if (!isset($aOptions['boolean']) || !$aOptions['boolean']) {
					// Non-booleans pass context along
					$sContext = end($aShorts);
				}
			} else {
				// Long option
				$sContext = (isset($aAliasMap[$aMatches['long']])) ? $aAliasMap[$aMatches['long']] : $aMatches['long'];
				if (!isset($aOptions['boolean']) || !$aOptions['boolean']) {
					// Boolean: no value
					$aParsed[$sContext] = true;
					$sContext = null;
				} elseif (isset($aMatches['value'])) {
					// Long option w/ value
					$aParsed[$sContext] = _processArgument($aMatches['value'], isset($aOptions[$sContext]) ? $aOptions[$sContext] : array());
					$sContext = null;
				} else {
					// No value supplied
					$aParsed[$sContext] = true;
				}
			}
			continue;
		}

		if ($sContext) {
			// Value
			if ($aParsed[$sContext] === true) {
				// Single value
				$aParsed[$sContext] = _processArgument($sArgument, isset($aOptions[$sContext]) ? $aOptions[$sContext] : array());
			} else {
				// Multiple values
				if (!is_array($aParsed[$sContext])) {
					$aParsed[$sContext] = array($aParsed[$sContext]);
				}
				$aParsed[$sContext] []= _processArgument($sArgument, isset($aOptions[$sContext]) ? $aOptions[$sContext] : array());
			}

			$sContext = null;
			continue;
		}

		// Non-option argument
		$aNonOptions []= _processArgument($sArgument);
	}

	// Verify against supplied options
	foreach ($aOptions as $sShort=>$aOption) {
		// Apply defaults
		if (!isset($aParsed[$sShort])) {
			if (isset($aOption['boolean'])) {
				$aParsed[$sShort] = false;
			} elseif (isset($aOption['default'])) {
				$aParsed[$sShort] = $aOption['default'];
			} else {
				$aParsed[$sShort] = null;
			}
		}

		// Enforce mandatory options
		if (isset($aOption['demand']) && $aOption['demand'] && !isset($aParsed[$sShort])) {
			throw new Exception('Missing required option: ' . $sShort);
		}

		// Alias to long form
		if (isset($aOption['alias']) && isset($aParsed[$sShort])) {
			$aParsed[$aOption['alias']] = $aParsed[$sShort];
		}
	}
	$aParsed['_'] = $aNonOptions;

	return $aParsed;
}

function _processArgument($mValue, $aDefinition=array()) {
	// Casting
	if (isset($aDefinition['string']) && $aDefinition['string']) {
		// Prevent casting
		return $sValue;
	} elseif (is_bool($mValue) || (isset($aDefinition['boolean']) && $aDefinition['boolean'])) {
		// Present/not present
		return !!$mValue;
	} elseif (preg_match('/^[-+]?\d+$/', $mValue)) {
		// Integer-like
		return (int)$mValue;
	} elseif (preg_match('/^[-+]?(\d+)\.\d+?$/', $mValue)) {
		// Float-like
		return (float)$mValue;
	} else {
		// Leave as-is
		return $mValue;
	}
}