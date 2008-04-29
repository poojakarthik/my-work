<?php

require_once("Image/Barcode.php");


abstract class Flex_Barcode
{
	protected function __construct()
	{

	}

    public function &create($type = 'CODE128')
    {
        //Make sure no bad files are included
        $type = strtoupper($type);
        $className = 'Flex_Barcode_' . $type;
        $classFile = dirname(__FILE__) . '/types/' . $className . '.php';

        if (!preg_match('/^[A-Z0-9_-]+$/', $type) || !file_exists($classFile))
        {
            throw new Exception("Unsupported barcode type: $type");
        }

		require_once $classFile;
		$barcode = new $className();
        return $barcode;
    }

	public abstract function draw($strValue, $strImgType='png');

	public abstract function setBarcodeHeight($pxHeight);
}




?>
