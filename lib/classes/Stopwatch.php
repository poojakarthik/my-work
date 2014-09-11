<?php
class Stopwatch
{
	protected	$_fStartTimestamp;
	protected	$_fLastLapTimestamp;

	public function __construct($bAutoStart=false) {
		if ($bAutoStart) {
			$this->start();
		}
	}
	
	public function start($iPrecision=null) {
		$this->_fStartTimestamp		= microtime(true);
		$this->_fLastLapTimestamp	= $this->_fStartTimestamp;
		return self::_round($this->_fStartTimestamp, $iPrecision);
	}
	
	public function split($iPrecision=null) {
		$fSplit	= microtime(true) - $this->_fStartTimestamp;
		return self::_round(microtime(true) - $this->_fStartTimestamp, $iPrecision);
	}
	
	public function lap($iPrecision=null) {
		$fLapTimestamp				= microtime(true);
		$fLapTime					= $fLapTimestamp - $this->_fLastLapTimestamp;
		$this->_fLastLapTimestamp	= $fLapTimestamp;
		return self::_round($fLapTime, $iPrecision);
	}

	// Number of seconds since the last Lap (without starting a new lap)
	public function lapSplit($iPrecision=null) {
		return self::_round(microtime(true) - $this->_fLastLapTimestamp, $iPrecision);
	}
	
	public function getStartTime($iPrecision=null) {
		return self::_round($this->_fStartTimestamp, $iPrecision);
	}

	// Convenience inline function
	protected static function _round($fValue, $iPrecision=0) {
		return ($iPrecision === null) ? $fValue : round($fValue, $iPrecision);
	}
}
?>