<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Collection_Test_Collection_Promise
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Test_Collection_Promise
{

	public static function testIsBroken() {


	}

	public static function testProcess() {
		$bPassed = self::testIsBroken();

	}
   
	public static function testGetActivePromises()
	{
		$aData = $_SESSION['Data']['Collection_Promise'];
	   
		$x = Logic_Collection_Promise::getActivePromises();
		
		 $iActive;
		foreach($aData as $data)
		{
			if ($data['completed_datetime']===null)
				$iActive++;
		}

		if ($iActive!= count($x))
			return false;

		foreach ($x as $oObject)
		{
			if ($oObject->completed_datetime!==null)
					return false;
		}

		return true;

	}


}
?>
