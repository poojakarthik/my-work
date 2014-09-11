<?php

//----------------------------------------------------------------------------//
// QueryFetch
//----------------------------------------------------------------------------//
/**
 * QueryFetch
 *
 * Fetch Query
 *
 * Implements a Fetch query using mysqli
 * Used for fetching recordsets from large tables
 * Fetches and caches batches of records
 *
 *
 * @prefix		fch
 *
 * @package		framework
 * @class		QueryFetch
 */
 class QueryFetch extends Query
 {
 	//------------------------------------------------------------------------//
	// QueryFetch() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * QueryFetch()
	 *
	 * Constructor for QueryFetch
	 *
	 * Constructor for QueryFetch Class
	 *
	 * @param	string	$strQuery	SQL Query string, without a LIMIT clause
	 *								This Query should return the full resultset
	 *								You wish to fetch
	 * @param	int		$intLimit	optional, number of records to cache per batch
	 *								default 100
	 *
	 * @return		void
	 *
	 * @method
	 */ 
 	function __construct($strQuery, $intLimit=NULL, $strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		parent::__construct($strConnectionType);
		
		// set query
		$this->strQuery = $strQuery;
		
		// set limit
		if ((int)$intLimit)
		{
			$this->intLimit 	= (int)$intLimit;
		}
		else
		{
			$this->intLimit 	= 100;
		}
		
		// init records
		$this->intRecords	 	= 0;
		
		// init counter
		$this->intCounter 		= 0;
	}
		
 	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 * 
	 * @alias	FetchNext
	 * @method
	 */ 
	function Execute()
	{
		Return $this->FetchNext();
	}
	 
	//------------------------------------------------------------------------//
	// _Next()
	//------------------------------------------------------------------------//
	/**
	 * FetchNext()
	 *
	 * Fetch Next Record
	 *
	 * Fetch Next Record
	 * 
	 * @return		Void
	 * @method
	 */ 
	function _Next()
	{
	 	// free the current result
		if ($this->sqlResult)
		{
			$this->sqlResult->free();
		}
			
	 	// get results
	 	$this->sqlResult = mysqli_query($this->db->refMysqliConnection, $this->strQuery.' LIMIT '.$this->intRecords.','.$this->intLimit);

		//increment Limit
		$this->intRecords += $this->intLimit;
	}
	 
	//------------------------------------------------------------------------//
	// FetchNext()
	//------------------------------------------------------------------------//
	/**
	 * FetchNext()
	 *
	 * Fetch Next Record
	 *
	 * Fetch Next Record
	 * 
	 * @return		Mixed	Array	Associative Array of Data
	 *						FALSE	on error or when there are no more records to fetch
	 * @method
	 */ 
	function FetchNext()
	{
		// increment the counter
		$this->intCounter++;
		
		// check if we need to get the next batch of results
		if ($this->intCounter > $this->intRecords)
		{
			// get more results
			$this->_Next();
		}
		
		if ($this->sqlResult)
		{
			// return next record
			return $this->sqlResult->fetch_assoc();
		}
		else
		{
			// or false
			return FALSE;
		}
	}
}

?>
