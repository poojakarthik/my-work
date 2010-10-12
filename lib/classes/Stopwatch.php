<?php
class Stopwatch
{
	protected	$_fStartTimestamp;
	protected	$_fLastLapTimestamp;
	
	public function start()
	{
		$this->_fStartTimestamp		= microtime(true);
		$this->_fLastLapTimestamp	= $this->_fStartTimestamp;
		return $this->_fStartTimestamp;
	}
	
	public function split()
	{
		return microtime(true) - $this->_fStartTimestamp;
	}
	
	public function lap()
	{
		return microtime(true) - $this->_fLastLapTimestamp;
	}
}
?>