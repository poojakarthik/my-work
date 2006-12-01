<html>
<body>

<?php
//----------------------------------------------------------------------------//
// index.php
//----------------------------------------------------------------------------//
/**
 * index.php
 *
 * i hate documentation
 *
 * i hate documentation
 *
 * @file	index.php
 * @language	PHP
 * @package	garage
 * @author	Zeemu
 * @version	6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// Car
//----------------------------------------------------------------------------//
/**
 * Car
 *
 * Car Class
 *
 * Car Class
 *
 *
 * @prefix	car
 *
 * @package	garage
 * @class	Car
 */

class Car
{

	//------------------------------------------------------------------------//
	// strName
	//------------------------------------------------------------------------//
	/**
	 * strName
	 *
	 * The name of the car
	 *
	 * The name of the car
	 *
	 * @type	String
	 *
	 * @property
	 */
	public $strName;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * builds the car
	 *
	 * builds the car
	 *
	 * @param	String	$strName	The name of the car
	 * @return	Void
	 *
	 * @method
	 */
	function __construct($strName)
	{
		echo $strName, " says brrrm<br>";
		
		$this->strName = $strName;
	}
	
	//------------------------------------------------------------------------//
	// getName
	//------------------------------------------------------------------------//
	/**
	 * getName()
	 *
	 * Get the cars' name
	 *
	 * Get the cars' name
	 *
	 * @return	Void
	 *
	 * @method
	 */
	public function getName()
	{
		echo $this->strName . " says hello<br>";
	}
}



//----------------------------------------------------------------------------//
// Procedural Crap
//----------------------------------------------------------------------------//
$carMicra = new Car("micra");
$carZed = new Car("zed");

//$carZed->getName();
echo $carZed->strName . "<br>";

$carZed->strName = "300ZX";

echo $carZed->getName() . "<br>";

?>

</body>
</html>
