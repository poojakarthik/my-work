<?php

/**
 * class Flex_Pdf_Element_Array
 * 
 * Extension of the Zend_Pdf_Element_Array class to allow addition
 * of elements after construction.
 */

class Flex_Pdf_Element_Array extends Zend_Pdf_Element_Array
{
	//************************************************************************
	// add($offset=NULL, $value)
	//************************************************************************
	/**
	 * Adds an element to the array with the given key
	 * 
	 * Adds an element to the array with the given key.
	 * The key can be NULL if a numerical key should be assigned
	 * automatically.
	 * 
	 * @param mixed $key for the value in the array
	 * @param Zend_Pdf_Element $value element to be added to the array
	 * 
	 * @return void
	 */
	public function add($key=NULL, Zend_Pdf_Element $value)
	{
		$this->items->offsetSet($key, $value);
	}
	
	//************************************************************************
	// adoptItems(Zend_Pdf_Element_Array $array)
	//************************************************************************
	/**
	 * Makes this array adopt the elements of a given array
	 * 
	 * Makes this array adopt the elements of a given array, losing all 
	 * elements currently held by this array.
	 * Note: This currently just takes a reference to the Array object of the
	 * given array, so changes to each Element_Array will effect the other.
	 * 
	 * @param Zend_Pdf_Element_Array $array element array to adopt values of
	 * 
	 * @return void
	 */
	public function adoptItems(Zend_Pdf_Element_Array $array)
	{
		$this->__items = $array->items;
	}
}

?>
