<?php

/**
 * Flex_Pdf_Barcode_CODE128
 *
 * based on:
 *
 * Image_Barcode_code128 class
 * from PEAR/Image_Barcode:
 * @author	 Jeffrey K. Brown <jkb@darkfantastic.net>
 * @copyright  2005 The PHP Group
 * @license	http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version	CVS: $Id: code128.php,v 1.3 2006/12/13 19:29:30 cweiske Exp $
 * @link	   http://pear.php.net/package/Image_Barcode
 *
 * Renders Code128 barcodes
 * Code128 is a high density encoding for alphanumeric strings.
 * This module prints the Code128B representation of the most common
 * ASCII characters (32 to 134).
 *
 * These are the components of a Code128 Bar code:
 * - 10 Unit Quiet Zone
 * - 6 Unit Start Character
 * - (n * 6) Unit Message
 * - 6 Unit "Check Digit" Character
 * - 7 Unit Stop Character
 * - 10 Unit Quiet Zone
 *
 * The Code128B bar codes produced by the algorithm have been validated
 * using a Cue-Cat bar code reader.
 *
 */

require_once dirname(__FILE__) . "/../Flex_Pdf_Barcode.php";

class Flex_Pdf_Barcode_CODE128 extends Flex_Pdf_Barcode
{
	private $_type = 'code128';
	private $code;

	const CLASS_A = 101;
	const CLASS_B = 100;
	const CLASS_C = 99;
	const CLASS_NOT_SET = -1;

	const START_A = 103;
	const START_B = 104;
	const START_C = 105;

	const STOP = 106;

	private $_currentClass = self::CLASS_NOT_SET;


	/**
	 * Draws a Code128 image barcode
	 *
	 * @param  string $text	 A text that should be in the image barcode
	 * @param  string $imgtype  The image type that will be generated
	 *
	 * @return image			The corresponding interleaved 2 of 5 image barcode
	 *
	 * @access public
	 *
	 * @author Jeffrey K. Brown <jkb@darkfantastic.net>
	 *
	 * @internal
	 * The draw() method is broken into three sections.  First, we take
	 * the input string and convert it to a string of barcode widths.
	 * Then, we size and allocate the image.  Finally, we print the bars to
	 * the image along with the barcode text and display it to the beholder.
	 *
	 */
	public function getRaw($text, $bottom, $left, $height, $width)
	{
		// We start with the Code128 Start Code character.
		// We initialize checksum to the value of the start code character..
		// We then add the startcode bars to $allbars, the main string
		// containing the bar sizes for the entire code.
		$checksum = $this->getStartCode($text);
		$allbars = $this->getBarWidths($checksum);


		// Next, we read the $text string that was passed to the
		// method and for each character, we determine the bar
		// pattern and add it to the end of the $allbars string.
		// In addition, we continually add the character's value
		// to the checksum
		$barCount = 0;
		while(strlen($text) > 0)
		{
			$newClass = $this->getClassChange($text);

			if ($newClass !== FALSE)
			{
				$allbars .= $this->getBarWidths($newClass);
				$barCount++;
				$checksum += ($newClass * $barCount);
			}

			$val = $this->getCharNumber($text);

			$barCount++;
			$checksum += ($val * $barCount);

			$allbars .= $this->getBarWidths($val);
		}


		// Then, Take the Mod 103 of the total to get the index
		// of the Code128 Check Character.  We get its bar
		// pattern and add it to $allbars in the next section.
		$checkdigit = $checksum % 103;
		$check = $this->getBarWidths($checkdigit);


		// Finally, we get the Stop Code pattern and put the
		// remaining pieces together.  We are left with the
		// string $allbars containing all of the bar widths
		// and can now think about writing it to the image.

		$stopcode = $this->getStopCode();
		$allbars = $allbars . $check . $stopcode;

		//------------------------------------------------------//
		// Next, we will calculate the width of the resulting
		// bar code. All characters have a bar width of 11, except
		// the STOP code which has a width of 13 and always occurs
		// once only. Also, all characters are 6 integers long,
		// except for the STOP code which is 7 integers long.

		$barcodewidth = (floor(strlen($allbars)/6) * 11) + 2;

		$barwidth = $width ? (floatval($width) / $barcodewidth) : 1;

		$h = self::toNumString(floatval($height));
		$b = self::toNumString(floatval($bottom));

		$raw = "q\nn\n0 0 0 rg\n0 w\n";

		// We set $xpos to $left so we start bar printing at the start
		$xpos = floatval($left);

		// We will now process each of the characters in the $allbars
		// array.  The number in each position is read and then alternating
		// black bars and spaces are drawn with the corresponding width.
		$bar = TRUE;
		for ($i=0; $i < strlen($allbars); ++$i) {
			$nval = intval($allbars[$i]);
			$width = $nval * $barwidth;

			if ($bar)
			{
				$l = self::toNumString($xpos);
				$w = self::toNumString($width);
				$raw .= "$l $b $w $h re f\n";
				$xpos += $width;
				$bar = FALSE;
			}
			else
			{
				$xpos += $width;
				$bar = TRUE;
			}
		}
		$raw .= "Q";

		return $raw;
	} // function getRaw()


	/**
	* @internal
	* Initialize the $code array, containing the bar and
	* space pattern for the Code128 B character set.
	*/
	public function __construct()
	{
									 // CLASS B		CLASS C
		$this->code[0] = "212222";   // " "			00
		$this->code[1] = "222122";   // "!"			01
		$this->code[2] = "222221";   // "{QUOTE}"	02
		$this->code[3] = "121223";   // "#"			03
		$this->code[4] = "121322";   // "$"			04
		$this->code[5] = "131222";   // "%"			05
		$this->code[6] = "122213";   // "&"			06
		$this->code[7] = "122312";   // "'"			07
		$this->code[8] = "132212";   // "("			08
		$this->code[9] = "221213";   // ")"			09
		$this->code[10] = "221312";  // "*"			10
		$this->code[11] = "231212";  // "+"			11
		$this->code[12] = "112232";  // ","			12
		$this->code[13] = "122132";  // "-"			13
		$this->code[14] = "122231";  // "."			14
		$this->code[15] = "113222";  // "/"			15
		$this->code[16] = "123122";  // "0"			16
		$this->code[17] = "123221";  // "1"			17
		$this->code[18] = "223211";  // "2"			18
		$this->code[19] = "221132";  // "3"			19
		$this->code[20] = "221231";  // "4"			20
		$this->code[21] = "213212";  // "5"			21
		$this->code[22] = "223112";  // "6"			22
		$this->code[23] = "312131";  // "7"			23
		$this->code[24] = "311222";  // "8"			24
		$this->code[25] = "321122";  // "9"			25
		$this->code[26] = "321221";  // ":"			26
		$this->code[27] = "312212";  // ";"			27
		$this->code[28] = "322112";  // "<"			28
		$this->code[29] = "322211";  // "="			29
		$this->code[30] = "212123";  // ">"			30
		$this->code[31] = "212321";  // "?"			31
		$this->code[32] = "232121";  // "@"			32
		$this->code[33] = "111323";  // "A"			33
		$this->code[34] = "131123";  // "B"			34
		$this->code[35] = "131321";  // "C"			35
		$this->code[36] = "112313";  // "D"			36
		$this->code[37] = "132113";  // "E"			37
		$this->code[38] = "132311";  // "F"			38
		$this->code[39] = "211313";  // "G"			39
		$this->code[40] = "231113";  // "H"			40
		$this->code[41] = "231311";  // "I"			41
		$this->code[42] = "112133";  // "J"			42
		$this->code[43] = "112331";  // "K"			43
		$this->code[44] = "132131";  // "L"			44
		$this->code[45] = "113123";  // "M"			45
		$this->code[46] = "113321";  // "N"			46
		$this->code[47] = "133121";  // "O"			47
		$this->code[48] = "313121";  // "P"			48
		$this->code[49] = "211331";  // "Q"			49
		$this->code[50] = "231131";  // "R"			50
		$this->code[51] = "213113";  // "S"			51
		$this->code[52] = "213311";  // "T"			52
		$this->code[53] = "213131";  // "U"			53
		$this->code[54] = "311123";  // "V"			54
		$this->code[55] = "311321";  // "W"			55
		$this->code[56] = "331121";  // "X"			56
		$this->code[57] = "312113";  // "Y"			57
		$this->code[58] = "312311";  // "Z"			58
		$this->code[59] = "332111";  // "["			59
		$this->code[60] = "314111";  // "\"			60
		$this->code[61] = "221411";  // "]"			61
		$this->code[62] = "431111";  // "^"			62
		$this->code[63] = "111224";  // "_"			63
		$this->code[64] = "111422";  // "`"			64
		$this->code[65] = "121124";  // "a"			65
		$this->code[66] = "121421";  // "b"			66
		$this->code[67] = "141122";  // "c"			67
		$this->code[68] = "141221";  // "d"			68
		$this->code[69] = "112214";  // "e"			69
		$this->code[70] = "112412";  // "f"			70
		$this->code[71] = "122114";  // "g"			71
		$this->code[72] = "122411";  // "h"			72
		$this->code[73] = "142112";  // "i"			73
		$this->code[74] = "142211";  // "j"			74
		$this->code[75] = "241211";  // "k"			75
		$this->code[76] = "221114";  // "l"			76
		$this->code[77] = "413111";  // "m"			77
		$this->code[78] = "241112";  // "n"			78
		$this->code[79] = "134111";  // "o"			79
		$this->code[80] = "111242";  // "p"			80
		$this->code[81] = "121142";  // "q"			81
		$this->code[82] = "121241";  // "r"			82
		$this->code[83] = "114212";  // "s"			83
		$this->code[84] = "124112";  // "t"			84
		$this->code[85] = "124211";  // "u"			85
		$this->code[86] = "411212";  // "v"			86
		$this->code[87] = "421112";  // "w"			87
		$this->code[88] = "421211";  // "x"			88
		$this->code[89] = "212141";  // "y"			89
		$this->code[90] = "214121";  // "z"			90
		$this->code[91] = "412121";  // "{"			91
		$this->code[92] = "111143";  // "|"			92
		$this->code[93] = "111341";  // "}"			93
		$this->code[94] = "131141";  // "~"			94
		$this->code[95] = "114113";  // DEL			95
		$this->code[96] = "114311";  // FNC3		96
		$this->code[97] = "411113";  // FNC2		97
		$this->code[98] = "411311";  // SHIFT		98
		$this->code[99] = "113141";  // Code C		99
		$this->code[100] = "114131"; // FNC4		Code B
		$this->code[101] = "311141"; // Code A		Code A
		$this->code[102] = "411131"; // FNC1		FNC1

		$this->code[self::START_A] 	= "211412";  // START A
		$this->code[self::START_B] 	= "211214";  // START B
		$this->code[self::START_C] 	= "211232";  // START C
		$this->code[self::STOP] 	= "2331112"; // STOP - for all classes
	}

	/**
	* Return the Start Code for Code128
	*/
	function getStartCode($text) {
		$this->getClassChange($text);
		if ($this->_currentClass == self::CLASS_A)
		{
			$retval = self::START_A;
		}
		else if ($this->_currentClass == self::CLASS_B)
		{
			$retval = self::START_B;
		}
		else // Must be Class C
		{
			$retval = self::START_C;
		}
		return $retval;
	}

	/**
	* Return the Stop Code for Code128
	*/
	function getStopCode() {
		return $this->code[self::STOP];
	}

	/**
	* Return the Code128 code equivalent of a character number
	*/
	function getBarWidths($index) {
		$retval = $this->code[$index];
		return $retval;
	}

	/**
	* Return the Code128 numerical equivalent of the first representable character sequence and remove that sequence from the string.
	*/
	function getCharNumber(&$text) {
		if ($this->_currentClass == self::CLASS_B)
		{
			$retval = ord($text[0]) - 32;
			$text = substr($text, 1);
		}
		else if ($this->_currentClass == self::CLASS_C)
		{
			$retval = intval(substr($text, 0, 2));
			$text = substr($text, 2);
		}
		return $retval;
	}

	/**
	 * Returns the Code128 numerical equivalent of the best class for the string, or FALSE if already in use
	 *
	 * Class B is good for encoding text, but Class C is more efficient for encoding sequences of
	 *
	 */
	 function getClassChange($text)
	 {
	 	$newClass = FALSE;
	 	// If begins with 6 or more digits, or class is already C and it begins with 2 or more digits, class C is best
	 	if (($this->_currentClass == self::CLASS_C && preg_match("/^[0-9]{2,}/", $text)) || preg_match("/^[0-9]{4,}/", $text))
	 	{
	 		if ($this->_currentClass != self::CLASS_C)
	 		{
	 			$newClass = $this->_currentClass = self::CLASS_C;
	 		}
	 	}
	 	// If there are less than 2 digits at start, or it isn't worth changing to class C, use class B
	 	else
	 	{
	 		if ($this->_currentClass != self::CLASS_B)
	 		{
	 			$newClass = $this->_currentClass = self::CLASS_B;
	 		}
	 	}
	 	return $newClass;
	 }

} // class


?>