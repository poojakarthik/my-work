<?php
//----------------------------------------------------------------------------//
// PaginationDetails
//----------------------------------------------------------------------------//
/**
 * PaginationDetails
 *
 * Used to calculate and model Pagination details
 *
 * Used to calculate and model Pagination details
 *
 * @class	PaginationDetails
 */
class PaginationDetails extends stdClass
{	
	public $maxRecordsPerPage;
		
	public $totalRecordCount;
	public $pageRecordCount;
	
	// Note that offset numbering starts at 0
	public $firstPageOffset;
	public $previousPageOffset;	// will be set to currentPageOffset if there is no pervious page
	public $currentPageOffset;
	public $nextPageOffset;		// will be set to currentPageOffset if there is no next page
	public $lastPageOffset;

	// Note that record numbering starts at 1
	// These will be set to NULL if there are no records in the page
	public $firstRecordInPage;
	public $lastRecordInPage;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor - calculates all pagination details
	 *
	 * constructor - calculates all pagination details
	 * 
	 *
	 * @param	int		$intTotalRecordCount	The total number of records in the list being paginated
	 * @param	int		$intMaxRecordsPerPage	The maximum number of records that can be in a single page (must be greater than 0)
	 * @param	int		$intCurrentPageOffset	Optional.  Defaults to 0.  The offset, into the list, of the first record to be displayed in the current page (it is assumed that this is correct)
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	public function __construct($intTotalRecordCount, $intMaxRecordsPerPage, $intCurrentPageOffset=0)
	{
		// Calculate pagination details
		if ($intTotalRecordCount == 0)
		{
			// There are no records
			$this->maxRecordsPerPage	= $intMaxRecordsPerPage;
			
			$this->totalRecordCount		= 0;
			$this->pageRecordCount		= 0;
			
			// Note that offset numbering starts at 0
			$this->firstPageOffset			= 0;
			$this->previousPageOffset		= 0;
			$this->currentPageOffset		= 0;
			$this->nextPageOffset			= 0;
			$this->lastPageOffset			= 0;
		
			// Note that record numbering starts at 1
			$this->firstRecordInPage	= NULL;
			$this->lastRecordInPage		= NULL;
			return;
		}

		// There is at least 1 record
		// Make sure  $intCurrentPageOffset is valid
		if ($intCurrentPageOffset >= $intTotalRecordCount || $intCurrentPageOffset < 0)
		{
			throw new Exception(__METHOD__ .':: CurrentPageOffset is outside the bounds of the Recordset');
		}
		
		// Work out all the other offsets based on the assumption that $intCurrentPageOffset is the offset of the first record to display in the page
		
		$intPreviousPageOffset	= max($intCurrentPageOffset - $intMaxRecordsPerPage, 0);
		$intFirstPageOffset		= 0;
		$intNextPageOffset		= ($intCurrentPageOffset + $intMaxRecordsPerPage < $intTotalRecordCount)? $intCurrentPageOffset + $intMaxRecordsPerPage : $intCurrentPageOffset;

		// Calculate last page offset		
		if ($intTotalRecordCount % $intMaxRecordsPerPage)
		{
			// There is a remainder, meaning the last page will not have MaxRecords in it
			$intLastPageOffset = floor($intTotalRecordCount / $intMaxRecordsPerPage) * $intMaxRecordsPerPage;
		}
		else
		{
			// There is no remainder, meaning the last page will be a full page of records
			$intLastPageOffset = (floor($intTotalRecordCount / $intMaxRecordsPerPage) - 1) * $intMaxRecordsPerPage;
		}
		
		// Note that the nextPageOffset can be greater than lastPageOffset, if currentPageOffset is out of sync with the proper page offsets
		// But I doub't anyone cares
		
		// Calculate PageRecordCount
		if ($intCurrentPageOffset >= $intLastPageOffset)
		{
			// Currently on the last page
			$intPageRecordCount = $intTotalRecordCount - $intCurrentPageOffset;
		}
		else
		{
			// Not currently on the last page, therefore it is safe to assume that the maximum number of records is being shown on this page
			$intPageRecordCount = $intMaxRecordsPerPage;
		}
		
		$intFirstRecordInPage	= $intCurrentPageOffset + 1;
		$intLastRecordInPage	= $intCurrentPageOffset + $intPageRecordCount;
		
		
		$this->maxRecordsPerPage	= $intMaxRecordsPerPage;
		$this->totalRecordCount		= $intTotalRecordCount;
		$this->pageRecordCount		= $intPageRecordCount;
		$this->firstPageOffset		= $intFirstPageOffset;
		$this->previousPageOffset	= $intPreviousPageOffset;
		$this->currentPageOffset	= $intCurrentPageOffset;
		$this->nextPageOffset		= $intNextPageOffset;
		$this->lastPageOffset		= $intLastPageOffset;
		$this->firstRecordInPage	= $intFirstRecordInPage;
		$this->lastRecordInPage		= $intLastRecordInPage;
	}
}

?>
