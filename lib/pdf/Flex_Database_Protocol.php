<?php

/**
* Flex_Database_Protocol
*
* Wrapper for accessing Flex_Database_Protocol (fdbp://) file resources.
*
* Conceptually, this handles the database resources as though they were in a
* file system with the following directory structure: -
*
*  CustomerGroup
*        |- PlaceholderName
*                   |- ResourceId
*
* Format of paths must be one of:
*
* fdbp://CustomerGroupNameOrId/ResourcePlaceholderName/ResourceId
*  - this refers to a specific file resource
*
* fdbp://CustomerGroupNameOrId/ResourcePlaceholderName
* fdbp://CustomerGroupNameOrId/ResourcePlaceholderName/
*  - this references a full list of effective resources for a specific placeholder
*
* fdbp://CustomerGroupNameOrId
* fdbp://CustomerGroupNameOrId/
*  - this references a list of all resource names
*
* *** OR ***
*
* fdbp://CustomerGroupNameOrId/ResourcePlaceholderName#EffectiveDate
* fdbp://CustomerGroupNameOrId/ResourcePlaceholderName/#EffectiveDate
*  - this refers to the first resource available on or prior to the effective date
*
*/
class Flex_Database_Protocol
{
	private $strFileContents = "";

	const FDBP_PROTOCOL = "fdbp";

	const FDBP_URL_PROTOCOL = "scheme";
	const FDBP_URL_CUSTOMER_GROUP_ID = "host";
	const FDBP_URL_PLACEHOLDER_NAME = "path";
	const FDBP_URL_RESOURCE_ID = "resource";
	const FDBP_URL_EFFECTIVE_DATE = "fragment";
	const FDBP_URL_PARSED = "url";

	const FDBP_CONTEXT_EFFECTIVE_TO_DATE = "EffectiveToDate";
	const FDBP_CONTEXT_FILE_TYPE = "FileType";
	const FDBP_CONTEXT_ORIGINAL_NAME = "OriginalName";

	private $bolRaiseErrors = FALSE;

	private $intCustomerGroupdId = -1;
	private $intEffectiveDate = 0;
	private $strResourceName = "";
	private $intResourceId = NULL;

	private $intStreamIndex = 0;
	private $intCreatedTime = 0;

	private $bolChangesToSave = FALSE;

	private $isFile = FALSE;
	private $isDir = FALSE;
	private $isOpenForReading = FALSE;
	private $isOpenForWriting = FALSE;
	private $isOpenForDeleting = FALSE;
	private $urlParts;
	private $dirListing = array();

	private $effectiveToDate = NULL;
	private $originalName = NULL;
	private $fileType = NULL;

	public function __construct()
	{
		$this->intCreatedTime = time();
	}

	private function reset()
	{
		$this->intCustomerGroupdId = -1;
		$this->intStreamIndex = 0;
		$this->bolChangesToSave = FALSE;
		$this->isFile = FALSE;
		$this->isDir = FALSE;
		$this->isOpenForReading = FALSE;
		$this->isOpenForWriting = FALSE;
		$this->dirListing = array();
		unset($this->urlParts);
	}

	/**
	* Opens the file stream
	*
	* This method is called immediately after this stream object is created.
	*
	* @param 	string	$path			specifies the URL that was passed to fopen() and that this object is expected to retrieve. You can use parse_url()  to break it apart.
	* @param 	string	$mode			is the mode used to open the file, as detailed for fopen(). You are responsible for checking that mode is valid for the path requested.
	* @param 	int		$options		holds additional flags set by the streams API. It can hold one or more of the following values OR'd together.
	* 									Flag					Description
	* 									STREAM_USE_PATH 		If the date part should match an earlier date (e.g. when opening an effective resource).
	* 									STREAM_REPORT_ERRORS 	If this flag is set, errors will be raised using trigger_error() during opening of the stream. If this flag is not set, errors are not raised.
	* @param 	string	$opened_path	If the path is opened successfully, and STREAM_USE_PATH is set in options, opened_path is set to the full path of the file/resource that was actually opened.
	*
	* @return 	bool	TRUE if the requested resource was opened successfully, FALSE otherwise
	*/
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->reset();

		$this->effectiveToDate = $this->getContextValue(self::FDBP_CONTEXT_EFFECTIVE_TO_DATE);
		$this->originalName = $this->getContextValue(self::FDBP_CONTEXT_ORIGINAL_NAME);
		$this->fileType = $this->getContextValue(self::FDBP_CONTEXT_FILE_TYPE);

		$this->bolRaiseErrors = $options & STREAM_REPORT_ERRORS;
		$usePath = $options & STREAM_USE_PATH;

		$path = strtoupper($path);

		$this->urlParts = self::parseURL($path, $this->intCreatedTime);

		if (!$this->urlParts)
		{
			$this->trigger_error("Invalid path passed to fdbp:// file handler: $path");
			return FALSE;
		}

		// Check that the path has everything that it needs
		if (  !array_key_exists(self::FDBP_URL_CUSTOMER_GROUP_ID, $this->urlParts)
		   || !array_key_exists(self::FDBP_URL_PLACEHOLDER_NAME, $this->urlParts)
		   || !$this->urlParts[self::FDBP_URL_PLACEHOLDER_NAME])
		{
			$this->trigger_error("Invalid file path passed to fdbp:// file handler: $path");
			return FALSE;
		}

		$rw = strpos($mode, "+") !== FALSE;

		$accessMode = strtolower(preg_replace("/[^xawrd]*/i", "", $mode));

		if (strlen($accessMode) != 1)
		{
			$this->trigger_error("Invalid file access mode passed '$mode' to fdbp:// file handler");
			return FALSE;
		}

		$createFileIfNotExists = (strpos("wa", $accessMode) !== FALSE);
		$blankFile = $accessMode == "w";
		$startAtEndOfFile = $accessMode == "a";
		$errorIfFileExists = $accessMode == "x";
		$errorIfNotFileExists = !$createFileIfNotExists;
		$openForWriting = $rw || (strpos("waxd", $accessMode) !== FALSE);
		$openForDeleting = $accessMode == "d";
		$onlyOpenForReading = $accessMode == "r" && !$rw;

		if (   $openForWriting
		    && $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE]
		    && $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] < $this->intCreatedTime)
		{
			$this->trigger_error("Files with an effective date in the past cannot be amended by fdbp:// handler");
			return FALSE;
		}

		if ($openForDeleting && !$this->urlParts[self::FDBP_URL_RESOURCE_ID])
		{
			$this->trigger_error("Files must be referenced by resource id for deletion by fdbp:// handler");
			return FALSE;
		}

		if ($this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] || $this->urlParts[self::FDBP_URL_RESOURCE_ID])
		{
			try
			{
				$loadingExisting = TRUE && $this->urlParts[self::FDBP_URL_RESOURCE_ID];
				$this->loadFile(!$onlyOpenForReading || $createFileIfNotExists, $blankFile);
				if ($loadingExisting && !$this->urlParts[self::FDBP_URL_EFFECTIVE_DATE])
				{
					$this->trigger_error("File not found in path '$path' by fdbp:// handler");
					return FALSE;
				}
			}
			catch (Exception $e)
			{
				$this->trigger_error("The following error occurred during loading of '$path' by fdbp:// handler: " . $e->getMessage());
				return FALSE;
			}
		}
		else
		{
			$this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] = $this->intCreatedTime;
		}

		// If the file does not exist
		if (!$this->urlParts[self::FDBP_URL_RESOURCE_ID])
		{
			if ($errorIfNotFileExists)
			{
				$this->trigger_error("File '$path' not found by fdbp:// file handler");
				return FALSE;
			}
			// We must need to create it...
			else
			{
				// As there is no data to save, don't create it just yet...
				$this->bolChangesToSave = TRUE;
			}
		}
		else
		{
			if ($errorIfFileExists)
			{
				$this->trigger_error("File '$path' already exists");
				return FALSE;
			}

			if ($openForWriting && $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] < $this->intCreatedTime)
			{
				$this->trigger_error("Files with an effective date in the past cannot be amended by fdbp:// handler");
				return FALSE;
			}

			if ($blankFile)
			{
				// We need to blank the file, but don't do it just yet...
				$this->strFileContents = "";
				$this->bolChangesToSave = TRUE;
			}

			if ($startAtEndOfFile)
			{
				$this->intStreamIndex = strlen($this->strFileContents);
			}
		}

		$this->isOpenForReading = !$openForDeleting && ($rw || !$openForWriting);
		$this->isOpenForWriting = !$openForDeleting && ($rw || $openForWriting);
		$this->isOpenForDeleting = $openForDeleting;

		self::urlForParts($this->urlParts);
		if ($usePath)
		{
			$opened_path = $this->urlParts[self::FDBP_URL_PARSED];
		}

		$this->isFile = TRUE;

		return TRUE;
	}


	/**
	* Closes the stream and releases used resources
	*
	* This method is called when the stream is closed, using fclose().
	* It releases any resources that were locked or allocated by the stream.
	*
	* @param void
	*
	* @return void
	*/
	public function stream_close()
	{
		$this->reset();
	}

	/**
	* This method is called in response to fread() and fgets() calls on the stream.
	* It returns up-to count bytes of data from the current read/write position as a string.
	* If there are less than count bytes available, as many as possible are returned.
	* If no more data is available, an empty string is returned. It also updates the read/write
	* position of the stream by the number of bytes that were successfully read.
	*
	* @param int $count maximum number of bytes to be read
	*
	* @return bytes that were read
	*/
	public function stream_read($count)
	{
		if (!$this->isOpenForReading) return "";
		$return = substr($this->strFileContents, $this->intStreamIndex, $count);
		$this->intStreamIndex += strlen($return);
		return $return;
	}


	/**
	* This method is called in response to fwrite() calls on the stream.
	* It stores data into the underlying storage. If there is not enough room, as many bytes as
	* possible are stored. The number of bytes that were successfully stored in the stream, or 0
	* if none could be stored, is returned. This also updates the read/write position of the stream
	* by the number of bytes that were successfully written.
	*
	* @param string $data to be written
	*
	* @return int number of bytes that were written
	*/
	public function stream_write($data)
	{
		if (!$this->isOpenForWriting) return 0;
		$origFileContents = $this->strFileContents;
		$dataLen = strlen($data);
		$this->strFileContents = substr($this->strFileContents, 0, $this->intStreamIndex) . $data . substr($this->strFileContents, $this->intStreamIndex + $dataLen);
		$this->bolChangesToSave = TRUE;
		if ($this->saveFile())
		{
			$this->intStreamIndex += $dataLen;
			return $dataLen;
		}
		$this->strFileContents = $origFileContents;
		return 0;
	}


	/**
	* This method is called in response to feof() calls on the stream.
	* Returns TRUE if the read/write position is at the end of the stream and if no more data
	* is available to be read, or FALSE otherwise.
	*
	* @param void
	*
	* @return
	*/
	public function stream_eof()
	{
		return $this->intStreamIndex >= strlen($this->strFileContents);
	}


	/**
	* This method is called in response to ftell() calls on the stream.
	* It returns the current read/write position of the stream.
	*/
	public function stream_tell()
	{
		return $this->intStreamIndex;
	}


	/**
	* This method is called in response to fseek() calls on the stream.
	* It updates the read/write position of the stream according to offset and whence.
	* See fseek() for more information about these parameters.
	* Returns TRUE if the position was updated, FALSE otherwise.
	*/
	public function stream_seek($offset, $whence)
	{
		switch($whence)
		{
			// Set position equal to offset  bytes.
			case SEEK_SET:
				$this->intStreamIndex = $offset;
				break;
			case SEEK_CUR:
				$this->intStreamIndex += $offset;
				break;
			case SEEK_END:
				$this->intStreamIndex = strlen($this->strFileContents) + $offset;
				break;
			default:
				return FALSE;
		}
		return TRUE;
	}


	/**
	* This method is called in response to fflush()  calls on the stream.
	* If there is cached data in the stream which has not yet been stored, it will be stored now.
	* Returns TRUE if the cached data was successfully stored (or if there was no data to store),
	* or FALSE if the data could not be stored.
	*/
	public function stream_flush()
	{
		if (!$this->isOpenForWriting) return FALSE;
		try
		{
			$this->saveFile();
			return TRUE;
		}
		catch(Exception $e)
		{
			return FALSE;
		}
	}


	/**
	* This method is called in response to fstat() calls on the stream.
	* It returns return an array containing the same values as appropriate for the stream.
	*/
	/* The mode value is a combination of (in octal): -
	* IFMT   0170000  - type of file
	* IFIFO  0010000  - named pipe (fifo)
	* IFCHR  0020000  - character special
	* IFDIR  0040000  - directory
	* IFBLK  0060000  - block special
	* IFREG  0100000  - regular
	* IFLNK  0120000  - symbolic link
	* IFSOCK 0140000  - socket
	* IFWHT  0160000  - whiteout
	* ISUID  0004000  - set user id on execution
	* ISGID  0002000  - set group id on execution
	* ISVTX  0001000  - save swapped text even after use
	* IRUSR  0000400  - read permission, owner
	* IWUSR  0000200  - write permission, owner
	* IXUSR  0000100  - execute/search permission, owner
	*/
	public function stream_stat()
	{
		$size = strlen($this->strFileContents);

		$mode = 0;
		if ($this->isFile)
		{
			$mode = 0100000 | ($this->isOpenForReading ? 0400 : 0) | ($this->isOpenForWriting ? 0200 : 0);
		}
		if ($this->isDir)
		{
			$mode = 040000 | 0400 | 0200 | 0100;
		}


		$return = array
		(
			"customer_group_id" => $this->urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID],
			"effective_date" => $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"resource_id" => $this->urlParts[self::FDBP_URL_RESOURCE_ID],

			//*** WIP *** See http://au.php.net/manual/en/function.stat.php for more details on what is required here!

			// device number
			0		=> 0,
			"dev"	=> 0,

			// inode number
			1		=> 0,
			"ino"	=> 0,

			// inode protection mode
			2		=> $mode,
			"mode"	=> $mode,

			// number of links
			3		=> 0,
			"nlink"	=> 0,

			// userid of owner
			4		=> $this->urlParts[self::FDBP_URL_RESOURCE_ID],
			"uid"	=> $this->urlParts[self::FDBP_URL_RESOURCE_ID],

			// groupid of owner
			5		=> $this->urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID],
			"gid"	=> $this->urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID],

			// device type, if inode device *
			6		=> -1,
			"rdev"	=> -1,

			// size in bytes
			7		=> $size,
			"size"	=> $size,

			// time of last access (Unix timestamp)
			8		=> $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"atime"	=> $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],

			// time of last modification (Unix timestamp)
			9		=> $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"mtime"	=> $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],

			// time of last inode change (Unix timestamp)
			10		=> $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"ctime"	=> $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE],

			// blocksize of filesystem IO *
			11			=> -1,
			"blksize"	=> -1,

			// number of blocks allocated *
			12			=> -1,
			"blocks"	=> -1,
		);

		//var_dump($return);

		return $return;
	}


	/**
	* This method is called in response to stat()  calls on the URL paths associated with the wrapper and should return as many elements in common with the system function as possible. Unknown or unavailable values should be set to a rational value (usually 0).
	*
	* flags holds additional flags set by the streams API. It can hold one or more of the following values OR'd together.
	* Flag					Description
	* STREAM_URL_STAT_LINK 	For resources with the ability to link to other resource (such as an HTTP Location: forward, or a filesystem symlink). This flag specified that only information about the link itself should be returned, not the resource pointed to by the link. This flag is set in response to calls to lstat(), is_link(), or filetype().
	* STREAM_URL_STAT_QUIET If this flag is set, your wrapper should not raise any errors. If this flag is not set, you are responsible for reporting errors using the trigger_error() function during stating of the path.
	*/
	public function url_stat($path, $flags)
	{

		$logErrors = !($flags & STREAM_URL_STAT_QUIET);

		$linkInfoOnly = $flags & STREAM_URL_STAT_LINK;

		$isFile = FALSE;
		$isDir = FALSE;

		$urlParts = self::parseURL($path);
		if (!$urlParts) return FALSE;
		if (!is_int($urlParts[self::FDBP_URL_RESOURCE_ID]) && !is_int($urlParts[self::FDBP_URL_EFFECTIVE_DATE]))
		{
			$isDir = TRUE;
		}
		else
		{
			$isFile = TRUE;
		}

		$isReadable = TRUE;
		$isWritable = TRUE;

		$size = 0;

		if ($isFile)
		{
			$file = new Flex_Database_Protocol();
			$p = "";
			$isReadable = $file->stream_open($path, "r", 0, $p);
			if (!$isReadable)
			{
				return FALSE;
			}

			$stat = $file->stream_stat();
			$urlParts[self::FDBP_URL_EFFECTIVE_DATE] = $stat["mtime"];
			$size = $stat["size"];
			$urlParts[self::FDBP_URL_RESOURCE_ID] = $stat["resource_id"];

			$file->stream_close();
			$isWritable = $file->stream_open($path, "r+", 0, $p);
			$file->stream_close();
		}

		$mode = 0;
		if ($isFile)
		{
			$mode = 0100000 | 0400 | ($isWritable ? 0200 : 0);
		}
		if ($isDir)
		{
			$mode = 040000 | 0400 | 0200 | 0100;
		}

		$return = array
		(
			"customer_group_id" => $urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID],
			"effective_date" => $urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"resource_id" => $urlParts[self::FDBP_URL_RESOURCE_ID],

			//*** WIP *** See http://au.php.net/manual/en/function.stat.php for more details on what is required here!

			// device number
			0		=> 0,
			"dev"	=> 0,

			// inode number
			1		=> 0,
			"ino"	=> 0,

			// inode protection mode
			2		=> $mode,
			"mode"	=> $mode,

			// number of links
			3		=> 0,
			"nlink"	=> 0,

			// userid of owner
			4		=> $urlParts[self::FDBP_URL_RESOURCE_ID],
			"uid"	=> $urlParts[self::FDBP_URL_RESOURCE_ID],

			// groupid of owner
			5		=> $urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID],
			"gid"	=> $urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID],

			// device type, if inode device *
			6		=> -1,
			"rdev"	=> -1,

			// size in bytes
			7		=> $size,
			"size"	=> $size,

			// time of last access (Unix timestamp)
			8		=> $urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"atime"	=> $urlParts[self::FDBP_URL_EFFECTIVE_DATE],

			// time of last modification (Unix timestamp)
			9		=> $urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"mtime"	=> $urlParts[self::FDBP_URL_EFFECTIVE_DATE],

			// time of last inode change (Unix timestamp)
			10		=> $urlParts[self::FDBP_URL_EFFECTIVE_DATE],
			"ctime"	=> $urlParts[self::FDBP_URL_EFFECTIVE_DATE],

			// blocksize of filesystem IO *
			11			=> -1,
			"blksize"	=> -1,

			// number of blocks allocated *
			12			=> -1,
			"blocks"	=> -1,
		);

		//var_dump($return);

		return $return;
	}




	/**
	* This method is called in response to unlink() calls on URL paths associated with the wrapper
	* and attempt to delete the item specified by path.
	* It returns TRUE on success or FALSE on failure.
	*/
	public function unlink($path)
	{
		// TODO: Complete implementation to make fully functional
		return FALSE;

		/* This worked for file writes, but should be modified to perform db deletes
		$f = new Flex_Database_Protocol();
		$p = "";
		return ($f->stream_open($path, "d", STREAM_REPORT_ERRORS, $p) && $f->deleteFile());
		*/
	}


	/**
	* This method would be called in response to rename() calls on URL paths associated with the wrapper
	* and would attempt to rename the item specified by path_from to the specification given by path_to.
	* It would return TRUE on success or FALSE on failure.
	* In order for the appropriate error message to be returned, and as renaming resources makes no sense,
	* this method is not defined.
	*/
	//public function rename($path_from, $path_to){}


	/**
	* This method is called in response to mkdir() calls on URL paths associated with the wrapper and
	* would attempt to create the directory specified by path . It would return TRUE on success or FALSE on
	* failure.
	* In order for the appropriate error message to be returned, and as making directories makes no sense
	* for resource files, this method is not implemented.
	* Posible values for options include STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE.
	*/
	//public function mkdir($path, $mode, $options){}


	/**
	* This method is called in response to rmdir() calls on URL paths associated with the wrapper and would
	* attempt to remove the directory specified by path . It would return TRUE on success or FALSE on failure.
	* In order for the appropriate error message to be returned, and as removing directories makes no sense
	* for resource files, this method is not implemented.
	* Possible values for options include STREAM_REPORT_ERRORS.
	*/
	//public function rmdir($path, $options){}







	/**
	* This method is called immediately when your stream object is created for examining directory contents
	* with opendir().
	* path specifies the URL that was passed to opendir() and that this object is expected to explore.
	* Possible values for options include STREAM_REPORT_ERRORS.
	*/
	public function dir_opendir($path, $options)
	{
		$this->reset();
		$this->bolRaiseErrors = $options & STREAM_REPORT_ERRORS;
		$this->urlParts = self::parseURL($path);
		if ($this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] || $this->urlParts[self::FDBP_URL_RESOURCE_ID])
		{
			$this->trigger_error("Invalid directory path passed to fdbp:// directory handler: $path");
			return FALSE;
		}


		// TODO: Complete implementation to make fully functional
		return FALSE;
		/*
		// If we have a placeholder name then we should get all of the available resources for it
		if ($this->urlParts[self::FDBP_URL_PLACEHOLDER_NAME])
		{

		}

		// We need to get all of the placeholder names
		else
		{

		}
		*/
	}


	/**
	* This method is called in response to readdir()
	* It returns a string representing the next filename in the location opened by dir_opendir().
	*/
	public function dir_readdir()
	{
		if ($this->intStreamIndex >= count($this->dirListing))
		{
			return FALSE;
		}
		return $this->dirListing[$this->intStreamIndex++];
	}


	/**
	* This method is called in response to rewinddir()
	* It resets the output generated by dir_readdir().
	* i.e.: The next call to dir_readdir() would return the first entry in the location returned by dir_opendir().
	*/
	public function dir_rewinddir()
	{
		$this->intStreamIndex = 0;
	}


	/**
	* This method is called in response to closedir().
	* It releases any resources which were locked or allocated during the opening and use of the directory stream.
	*/
	public function dir_closedir()
	{
		$this->reset();
	}










	private function trigger_error($strErrorMessage, $errorLevel=E_USER_WARNING)
	{
		if ($this->bolRaiseErrors)
		{
			trigger_error($strErrorMessage, $errorLevel);
		}
	}

	private function saveFile()
	{
		// TODO: Complete implementation to make fully functional
		return FALSE;
		/* The following worked for file writes, but needs modifying for db.
		if (!$this->bolChangesToSave)
		{
			return TRUE;
		}
		if (!$this->urlParts[self::FDBP_URL_RESOURCE_ID])
		{
			return $this->createFile();
		}

		// try to save the changed contents *** WIP ***
		$savedOK = TRUE;



		// This should be replaced by a suitable database update statement...
		$padId = substr(str_repeat("0", 20).$this->urlParts[self::FDBP_URL_RESOURCE_ID], -20);
		$cgDir = "resources/" . $this->urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID] . "/";
		if (!file_exists($cgDir)) mkdir($cgDir);
		$phDir = $cgDir . "/" . $this->urlParts[self::FDBP_URL_PLACEHOLDER_NAME] . "/";
		if (!file_exists($phDir)) mkdir($phDir);
		$rsFile = fopen($phDir . $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE]  . "." . $padId, "w");
		fwrite($rsFile, $this->strFileContents);
		fclose($rsFile);



		$this->bolChangesToSave = !$savedOK;
		return $savedOK;
		*/
	}

	private function createFile()
	{
		// *** WIP ***
		// This should be replaced with a suitable database insert statement...
		// Find the next available id
		$ids = array();
		/* Check that we have the following: -
		$this->effectiveToDate - nullable
		$this->originalName
		$this->fileType
		*/

		return FALSE;


		$phDir = "resources/" . $this->urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID] . "/" . $this->urlParts[self::FDBP_URL_PLACEHOLDER_NAME] . "/";
		preg_match_all("/\.([0-9]+)_/", implode("_", (is_dir($phDir) ? scandir($phDir) : $ids))."_", $ids);
		$ids = array_map("intval", $ids[1]);
		$this->urlParts[self::FDBP_URL_RESOURCE_ID] = 1 + (count($ids) ? max($ids) : 0);
		return $this->saveFile();
	}

	private function getContextValue($name)
	{
		$value = NULL;
		if (isset($this->context))
		{
			$o = stream_context_get_options($this->context);
			if (array_key_exists(self::FDBP_PROTOCOL, $o) && array_key_exists($name, $o[self::FDBP_PROTOCOL]))
			{
				$value = $o[self::FDBP_PROTOCOL][$name];
			}
		}
		return $value;
	}

	private function deleteFile()
	{
		if (!$this->isOpenForDeleting) return FALSE;
		// *** WIP ***
		// TODO
		return FALSE;
		// This should be replaced with a suitable database delete statement...
		$padId = substr(str_repeat("0", 20).$this->urlParts[self::FDBP_URL_RESOURCE_ID], -20);
		$phDir = "resources/" . $this->urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID] . "/" . $this->urlParts[self::FDBP_URL_PLACEHOLDER_NAME] . "/";
		return unlink($phDir . $this->urlParts[self::FDBP_URL_EFFECTIVE_DATE]  . "." . $padId);
	}

	private function loadFile($matchDate=FALSE, $ignoreContent=FALSE)
	{
		// Try to load the file from the database into $this->strFileContents
		// Also, populate the $this->intResourceId property iff a match is found
		// If it does not exist then return FALSE
		// else return the effective date of the resource

		if ($this->urlParts[self::FDBP_URL_RESOURCE_ID])
		{
			$strTables = "DocumentResource DR, DocumentResourceType RT";

			$arrColumns = Array( "FileContent" 	=> "DR.FileContent",
			 					 "StartDatetime"=> "DR.StartDatetime",
			 					 "EndDatetime" 	=> "DR.EndDatetime");

			$strWhere = "DR.Id = <ResourceId> AND DR.Type = RT.Id";

			$arrWhere = Array("ResourceId" => $this->urlParts[self::FDBP_URL_RESOURCE_ID]);

			$selDocumentResource = new StatementSelect($strTables, $arrColumns, $strWhere);

			$mixResult = $selDocumentResource->Execute($arrWhere);

			if ($mixResult === FALSE)
			{
				$this->trigger_error("An error occurred when fetching the resource for Document Resource Id '" . $this->urlParts[self::FDBP_URL_RESOURCE_ID] . "'.");
			}

			$arrRecord = $selDocumentResource->Fetch();

			if (!$arrRecord)
			{
				return FALSE;
			}

			$this->strFileContents = $arrRecord["FileContent"];
			$this->effectiveToDate = intval($arrRecord["EndDatetime"]);
			$this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] = intval($arrRecord["StartDatetime"]);
		}

		else //This must be TRUE >> if ($this->urlParts[self::FDBP_URL_EFFECTIVE_DATE])
		{
			// Load the resource that was effective on that effective date
			// TODO: Complete implementation to make fully functional
			$arrRecord = array(); // Should be result of db query!!
			$this->strFileContents = $arrRecord["FileContent"];
			$this->effectiveToDate = intval($arrRecord["EndDatetime"]);
			$this->urlParts[self::FDBP_URL_EFFECTIVE_DATE] = intval($arrRecord["StartDatetime"]);
			$this->urlParts[self::FDBP_URL_RESOURCE_ID] = intval($arrRecord["ResourceId"]);
		}
	}


	public static function parseURL($urlPassed, $now=0)
	{
		if (!$now)
		{
			$now = time();
		}
		$url = strtoupper($urlPassed);
		$urlParts = parse_url($url);

		if (  !array_key_exists(self::FDBP_URL_PROTOCOL, $urlParts)
		   || $urlParts[self::FDBP_URL_PROTOCOL] != strtoupper(self::FDBP_PROTOCOL))
		{
			$urlParts = FALSE;
		}
		else
		{
			$urlParts[self::FDBP_URL_RESOURCE_ID] = 0;
			if (array_key_exists(self::FDBP_URL_PLACEHOLDER_NAME, $urlParts))
			{
				$path = $urlParts[self::FDBP_URL_PLACEHOLDER_NAME];
				if ($path[0] == "/")
				{
				  $path = substr($path, 1);
				}
				$parts = explode("/", $path);
				if (count($parts))
				{
					$urlParts[self::FDBP_URL_PLACEHOLDER_NAME] = array_shift($parts);
				}
				else
				{
					$urlParts[self::FDBP_URL_PLACEHOLDER_NAME] = "";
				}
				if (count($parts))
				{
					// intval will strip off any file extension present - we don't need it, so that's ok
					$urlParts[self::FDBP_URL_RESOURCE_ID] = intval(array_shift($parts));
				}
			}

			if (!$urlParts[self::FDBP_URL_RESOURCE_ID] && array_key_exists(self::FDBP_URL_EFFECTIVE_DATE, $urlParts))
			{
				$urlParts[self::FDBP_URL_EFFECTIVE_DATE] = self::getTimestampForDate($urlParts[self::FDBP_URL_EFFECTIVE_DATE], $now);
				if (!$urlParts[self::FDBP_URL_EFFECTIVE_DATE])
				{
					$urlParts = FALSE;
				}
			}
			else
			{
				$urlParts[self::FDBP_URL_EFFECTIVE_DATE] = "";
			}

			if ($urlParts && array_key_exists(self::FDBP_URL_CUSTOMER_GROUP_ID, $urlParts))
			{
				$urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID] = self::getCustromerGroupIdForGroup($urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID]);
				if (!$urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID])
				{
					$urlParts = FALSE;
				}
			}
		}

		self::urlForParts($urlParts);

		return $urlParts;
	}

	private static function urlForParts(&$urlParts)
	{
		if (!$urlParts)
		{
			$url = FALSE;
		}
		else
		{
			$url = self::FDBP_PROTOCOL . "://"
				. $urlParts[self::FDBP_URL_CUSTOMER_GROUP_ID]
				. "/"
				. (!$urlParts[self::FDBP_URL_PLACEHOLDER_NAME] ? "" : ($urlParts[self::FDBP_URL_PLACEHOLDER_NAME]
				. "/"
				. ($urlParts[self::FDBP_URL_RESOURCE_ID]
					? $urlParts[self::FDBP_URL_RESOURCE_ID]
					: ($urlParts[self::FDBP_URL_EFFECTIVE_DATE] ? ("#" . $urlParts[self::FDBP_URL_EFFECTIVE_DATE]) : ""))));
			$urlParts[self::FDBP_URL_PARSED] = $url;
		}
		return $url;
	}

	private static function getCustromerGroupIdForGroup($group)
	{
		if (is_numeric($group))
		{
			// Assume it is a CustomerGroup.ID
			$group = intval($group);
		}
		else
		{
			if (defined("CUSTOMER_GROUP_$group"))
			{
				$group = constant("CUSTOMER_GROUP_$group");
			}
			else
			{
				$group = FALSE;
			}
		}
		return $group;
	}

	private static function getTimestampForDate($date, $now)
	{
		$arr = array();
		if (is_numeric($date))
		{
			// Assume it is a Unix timestamp
			$date = intval($date);
		}
		else if (!$date)
		{
			$date = $now;
		}
		else if (preg_match("/^([0-9]{4,4})-([0-9]{2,2})-([0-9]{2,2}) ([0-9]{2,2}):([0-9]{2,2}):([0-9]{2,2})$/", $date, $arr))
		{
			$Y = ($arr[1]);
			$m = intval($arr[2]);
			$d = intval($arr[3]);
			$H = intval($arr[4]);
			$i = intval($arr[5]);
			$s = intval($arr[6]);
			if (   $Y > 9999 || $Y <= 2000
				|| $m > 12   || $m <= 0
				|| $d > 31   || $d <= 0
				|| $H > 23   || $H <  0
				|| $i > 59   || $i <  0
				|| $s > 59   || $s <  0
				|| !checkdate($m, $d, $Y))
			{
				$date = 0;
			}
			else
			{
				$date = mktime($H, $i, $s, $m, $d, $Y);
			}
		}
		return $date;
	}


	public static function urlForResource($resource, $placeholderName)
	{
		$stat = fstat($resource);
		return self::FDBP_PROTOCOL."://".$stat["gid"]."/$placeholderName/" . $stat["uid"];
	}

	public static function createResource($customerGroup, $placeholderName, $fileTypeId, $contents, $originalName, $effectiveFromDate, $effectiveToDate=NULL)
	{
		$c = stream_context_create(array(
					self::FDBP_PROTOCOL => array(
						self::FDBP_CONTEXT_EFFECTIVE_TO_DATE => $effectiveToDate,
						self::FDBP_CONTEXT_FILE_TYPE => $fileTypeId,
						self::FDBP_CONTEXT_ORIGINAL_NAME => $originalName
					)
				)
			);
		$f = fopen("fdbp://$customerGroup/$placeholderName/#$effectiveFromDate", "w", 0, $c);
		fwrite($f, $contents);
		$fp = self::urlForResource($f, $placeholderName);
		fclose($f);
		return $fp;
	}
}

if (!stream_wrapper_register("fdbp", "Flex_Database_Protocol"))
{
	trigger_error("Attempt to register 'Flex_Database_Protocol' as 'fdbp://' stream wrapper.", E_USER_ERROR);
}

?>
