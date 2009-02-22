<?php

/**
 * Abstract class for wrappers of DOMNode objects
 * 
 * @abstract
 * 
 * @author Hadrian Oliver
 * @link http://au.php.net/manual/en/class.domnode.php Class synopsis of DOMNode
 */

abstract class Flex_Dom_Object
{
	/**
	 * Array in which child Flex_Dom_Element's are cached
	 */
	protected $_arrChildren = array();

	/**
	 * Accessor function for the wrapped DOMNode object
	 * 
	 * Accessor function for the wrapped DOMNode object
	 * 
	 * @param void
	 * 
	 * @return DOMNode wrapped by this Flex_Dom_Object
	 * 
	 * @abstract
	 */
	abstract protected function _getDomNode();

	/**
	 * Accessor function for the DOMDocument object to which the wrapped DOMNode object belongs
	 * 
	 * Accessor function for the DOMDocument object to which the wrapped DOMNode object belongs
	 * 
	 * @param void
	 * 
	 * @return DOMDocument to which the wrapped DOMNode object belongs
	 * 
	 * @abstract
	 */
	abstract public function getDomDocument();

	/**
	 * Magic function that creates and/or accesses a child element of a given name.
	 * 
	 * Magic function that creates and/or accesses a child element of a given name.
	 * The name is the XML element name in the parent XML node.
	 * 
	 * @param String $strChildName of child element being created and/or accessed
	 * 
	 * @return Flex_Pdf_Element A child element of the given name
	 */
	public function __get($strChildName)
	{
		// Check that the element wrapper exists in the cache
		if (!array_key_exists($strChildName, $this->_arrChildren))
		{
			// It doesn't, so create and add it to the cache
			$childNode = $this->getDomDocument()->createElement($strChildName);
			$this->_getDomNode()->appendChild($childNode);
			$this->_arrChildren[$strChildName] = new Flex_Dom_Element($childNode, $this);
		}
		// Return the cached wrapper
		return $this->_arrChildren[$strChildName];
	}

	/**
	 * Magic function that creates and/or accesses a child element of a given name and index.
	 * 
	 * Magic function that creates and/or accesses a child element of a given name and index.
	 * The name is the XML element name in the parent XML node and the index is the
	 * position of that child within the list of elements of the same parent name, indexed from zero.
	 * 
	 * @param String $strChildName of child element being created and/or accessed
	 * @param array $index containing a single integer value representing the index of child element 
	 *                   being created and/or accessed (default=NULL). 
	 *                   If NULL, a new element is created and appended to the list of
	 * 					 child elements of that name.
	 * 
	 * @return Flex_Pdf_Element A child element of the given name and index
	 */
	public function __call($strChildName, $index=NULL)
	{
		// Check that an entry exists in the cache elements of the given name
		if (!array_key_exists($strChildName, $this->_arrChildren))
		{
			// It doesn't, so create an array in the cache to store them in
			$this->_arrChildren[$strChildName] = array();
		}
		// An entry exists, so check that it's an array
		else if (!is_array($this->_arrChildren[$strChildName]))
		{
			// It isn't an array, so place the current entry into an array and cache the array instead
			$this->_arrChildren[$strChildName] = array(0 => $this->_arrChildren[$strChildName]);
		}
		// Get the integer value for the index
		$index = count($index) ? $index[0] : -1;

		// Remember whether we have appended a new child to the end of the list (needed for resorting)
		$bolAppended = FALSE;
		
		// If the index is less than 0, we must be appending
		if ($index < 0)
		{
			// Find the next available index for a child of the given name
			$keys = array_keys($this->_arrChildren[$strChildName]);
			// Check to see if this is the first one
			if (!count($keys))
			{
				$index = 0;
			}
			// Others already exist
			else
			{
				// Find the next available key (NOT assuming that they are sequential)
				$index = max($keys) + 1;
			}
			// Record that we are appending the element to the end of the list
			$bolAppended = TRUE;
		}

		// Check to see if the element of the given name and index exists in the cache
		if (!array_key_exists($index, $this->_arrChildren[$strChildName]))
		{
			// It doesn't, so create it...
			$childNode = $this->getDomDocument()->createElement($strChildName);
			$this->_getDomNode()->appendChild($childNode);
			// ... and cache it.
			$this->_arrChildren[$strChildName][$index] = new Flex_Dom_Element($childNode, $this);
			
			// If not appending the element to the end of the list, resort the elements to correct their positions
			if (!$bolAppended && ksort($this->_arrChildren[$strChildName]))
			{
				$keys = array_keys($this->_arrChildren[$strChildName]);
				for ($i = 0, $l = count($keys); $i < $l; $i++)
				{
					$this->_getDomNode()->appendChild($this->_arrChildren[$strChildName][$keys[$i]]->_getDomNode());
				}
			}
		}

		// Return the wrapper of the given name and index from the cache
		return $this->_arrChildren[$strChildName][$index];
	}
}


?>
