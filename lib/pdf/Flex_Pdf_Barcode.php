<?php

abstract class Flex_Pdf_Barcode
{
	protected function __construct()
	{

	}

    public function &create($type = 'CODE128')
    {
        //Make sure no bad files are included
        $type = strtoupper($type);
        $className = 'Flex_Pdf_Barcode_' . $type;
        $classFile = dirname(__FILE__) . '/barcode/' . $className . '.php';

        if (!preg_match('/^[A-Z0-9_-]+$/', $type) || !file_exists($classFile))
        {
            throw new Exception("Unsupported barcode type: $type");
        }

		require_once $classFile;
		$barcode = new $className();
        return $barcode;
    }

	public abstract function getRaw($strValue, $bottom, $left, $height, $width);

	protected function toNumString($numeric)
	{
		if (is_integer($numeric)) {
			return (string)$numeric;
		}

		$prec = 0; $v = $numeric;
		while (abs( floor($v) - $v ) > 1e-10)
		{
			$prec++; $v *= 10;
		}
		return sprintf("%.{$prec}F", $numeric);
	}
}




?>
