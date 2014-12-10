<?php

class InsertFileAJAXBackend {

	public static function getLicenses() {
		$oLicenses = new JsonLicenses();
		return $oLicenses->getJsonOutput();
	}

	/**
	 * Calculate on which page an file is shown and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public static function getFilePage() {
		global $wgDBtype;
		$oRequest = RequestContext::getMain()->getRequest();
		$filename = $oRequest->getVal( 'filename', false );
		$type = $oRequest->getVal( 'type', 'image' );
		$pagesize = $oRequest->getInt( 'pagesize', 12 );

		if ( strstr( $filename, 'index.php' ) ) {
			$token = array( );
			parse_str( $filename, $token );
			if ( isset( $token[ 'f' ] ) ) {
				$token = explode( ':', $token[ 'f' ] );
				$filename = $token[ 1 ];
			}
		}

		if ( !$filename ) {
			return FormatJson::encode(
				array(
					'file' => '',
					'page' => 0
				)
			);
		}

		$dbr = wfGetDB( DB_SLAVE );

		switch ( $type ) {
			// TODO MRG (01.07.11 12:08): see above: Tiff special case only makes sense with PAgedTiffHandler activated.
			case 'image':
				switch ( $wgDBtype ) {
					case 'postgres':
						$sql = "SELECT insertfile_getImagePosition('{$filename}') AS rank";
						break;
					case 'oracle':
						$sql = "SELECT if_getImagePosition('{$filename}') AS rank FROM dual";
						break;
					case 'mysql':
					default:
						$tbl = $dbr->tableName( 'image' );
						$sql = "SELECT tmp.rank FROM
									(SELECT @row:=@row+1 rank, i.img_name
									 FROM {$tbl} i, (SELECT @row:=0) r
									 WHERE (i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')
									 ORDER BY i.img_name ASC) tmp
								WHERE tmp.img_name = '{$filename}'";
						break;
				}
				break;
			case 'file':
				switch ( $wgDBtype ) {
					case 'postgres':
						$sql = "SELECT insertfile_getFileUploadPosition('{$filename}') AS rank";
						break;
					case 'oracle':
						$sql = "SELECT if_getFileUploadPosition('{$filename}') AS rank FROM dual";
						break;
					case 'mysql':
					default:
						$tbl = $dbr->tableName( 'image' );
						$sql = "SELECT tmp.rank FROM
									(SELECT @row:=@row+1 rank, i.img_name
									 FROM {$tbl} i, (SELECT @row:=0) r
									 WHERE (i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')
									 ORDER BY i.img_name ASC) tmp
								WHERE tmp.img_name = '{$filename}'";
						break;
				}
				break;
		}

		$res = $dbr->query( $sql );
		if ( $res && $res->numRows() ) {
			$row = $res->fetchObject();
			$page = ceil( $row->rank / $pagesize );
		} else {
			$page = 0;
		}
		return FormatJson::encode(
			array(
				'file' => $filename,
				'page' => $page
			)
		);
	}

	/**
	 * Calculate on which page an uploaded file is shown and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public static function getUploadedFilePage() {
		global $wgDBtype;
		$oRequest = RequestContext::getMain()->getRequest();
		$type = $oRequest->getVal( 'type', 'image' );
		$sort = $oRequest->getVal( 'sort', 'name' );
		$pagesize = $oRequest->getInt( 'pagesize', 12 );

		switch ( $sort ) {
			case 'size':
				$sql_sort = 'i.img_size DESC';
				break;
			case 'lastmod':
				$sql_sort = 'i.img_timestamp DESC';
				break;
			case 'name':
			default:
				$sql_sort = 'i.img_name ASC';
		}

		$dbr = wfGetDB( DB_SLAVE );

		if ( $wgDBtype == 'mysql' ) {
			if ( $type == 'image' ) {
				$tbl = $dbr->tableName( 'image' );
				$sql = "SELECT tmp.rank, tmp.img_name FROM
							(SELECT @row:=@row+1 rank, i.img_name, i.img_timestamp
							 FROM {$tbl} i, (SELECT @row:=0) r
							 WHERE (i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')
							 ORDER BY {$sql_sort}) tmp
						ORDER BY tmp.img_timestamp DESC
						LIMIT 1";
			} else {
				$tbl = $dbr->tableName( 'image' );
				$sql = "SELECT tmp.rank, tmp.img_name FROM
							(SELECT @row:=@row+1 rank, i.img_name, i.img_timestamp
							 FROM {$tbl} i, (SELECT @row:=0) r
							 WHERE (i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')
							 ORDER BY {$sql_sort}) tmp
						ORDER BY tmp.img_timestamp DESC
						LIMIT 1";
			}

			$filename = '';

			$res = $dbr->query( $sql );
			if ( $res && $res->numRows() ) {
				$row = $res->fetchObject();
				$page = ceil( $row->rank / $pagesize );
				$filename = $row->img_name;
			} else {
				$page = 0;
			}
		} else {
			if ( $type == 'image' ) {
				$tbl = $dbr->tableName( 'image' );
				$sql = "SELECT i.img_name, i.img_timestamp
						 FROM {$tbl} i
						 WHERE (i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')
						 ORDER BY {$sql_sort}";
			} else {
				$tbl = $dbr->tableName( 'image' );
				$sql = "SELECT i.img_name, i.img_timestamp
						 FROM {$tbl} i
						 WHERE (i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')
						 ORDER BY {$sql_sort}";
			}

			$filename = '';

			$res = $dbr->query( $sql );
			$rows = array();
			$rank = 0;
			$newestRow = null;
			if ( $res && $res->numRows() ) {
				while($row = $res->fetchObject()) {
					$rank++;
					$rows[$rank] = array('rank' => $rank, 'filename' => $row->img_name, 'time' => $row->img_timestamp);
					if(!$newestRow || $newestRow['time'] < $row->img_timestamp) {
						$newestRow = &$rows[$rank];
					}
				}
				$page = ceil( $newestRow['rank'] / $pagesize );
				$filename = $newestRow['filename'];
			} else {
				$page = 0;
			}
		}

		return FormatJson::encode(
			array(
				'file' => $filename,
				'page' => $page
			)
		);
	}

	public static function getExistsWarning( $sFilename ) {
		$oFile = wfFindFile( $sFilename );
		if( !$oFile ) {
			$oFile = wfLocalFile( $sFilename );
		}

		$s = '&#160;';
		if ( $oFile ) {
			$exists = UploadBase::getExistsWarning( $oFile );
			$warning = SpecialUpload::getExistsWarning( $exists );
			if ( $warning !== '' ) {
				if ( BsExtensionManager::getExtension( 'SecureFileStore' ) !== null ) {
					$warning = SecureFileStore::secureStuff( $warning );
				}
				$s = "<div>$warning</div>";
			}
		}

		return $s;
	}

	/**
	 * Process the dataset for the ExtJS file store and put it to ajax output.
	 */
	public static function getFiles() {
		$thumbs_width  = 128;
		$thumbs_height = 128;

		$oStoreParams = BsExtJSStoreParams::newFromRequest();
		$sFileType    = $oStoreParams->getRequest()->getVal('type', 'image');
		//$aFileExtensions = $oStoreParams->getRequest()->getArray('type'); //TODO: For future use

		$sStart = $oStoreParams->getStart();
		$sLimit = $oStoreParams->getLimit();

		switch ( $oStoreParams->getSort() ) {
			case 'size':
				$sSort = 'i.img_size';
				break;
			case 'name':
				$sSort = 'i.img_name';
				break;
			case 'lastmod':
			default:
				$sSort = 'i.img_timestamp';
		}
		$sSort .= ' '.$oStoreParams->getDirection();

		$sType = '';
		switch ( $sFileType ) {
			case 'image':
				$sType = "(i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')";
				break;
			case 'file':
				$sType = "(i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')";
				break;
		}

		$aConds = array();
		$aConds[] = $sType;

		$aNameFilters = array();
		//We need to replace spaces, because the DB value does not have spaces
		$aNameFilters[] = str_replace( ' ', '_', $oStoreParams->getQuery() );

		$dbr = wfGetDB( DB_SLAVE );
		$sImageTable = $dbr->tableName( 'image' );
		$sCategoryLinksTable = $dbr->tableName( 'categorylinks' );
		$sPageTable = $dbr->tableName( 'page' );

		wfRunHooks( 'BSInsertFileGetFilesBeforeQuery', array( &$aConds, &$aNameFilters ) );

		global $wgDBtype;
		$sNameFilters = self::buildNameFiltersSQL( $aNameFilters, $wgDBtype );
		if( !empty( $sNameFilters ) ) $aConds[] = $sNameFilters;
		$sConds = implode( ' AND ', $aConds );

		// Searching for images/files in categories with name $sCategoryFilter
		// TODO SW: verify that this image is found by category, may be join in statement
		$sCategoryFilter = self::buildNameFiltersSQL( $aNameFilters, $wgDBtype, 'cl_to' );
		$sConds .= " OR i.img_name IN (SELECT page_title FROM $sCategoryLinksTable, $sPageTable where $sCategoryFilter and page_namespace=6 and page_id=cl_from) AND $sType";

		if ( $wgDBtype == 'oracle' ) {
			$sql =
				"SELECT * FROM
					(
						SELECT i.img_name, i.img_size, i.img_width, i.img_height, (ROUND(TO_DATE(TO_CHAR(i.img_timestamp, 'YYYYMMDDHH24MISS'), 'YYYYMMDDHH24MISS') - TO_DATE('19700101', 'YYYYMMDDHH24MISS')) * 86400) AS img_timestamp,
								row_number() over (ORDER BY {$sSort}) rnk
						FROM {$sImageTable} i
						WHERE {$sConds}
					)
				WHERE rnk BETWEEN {$sStart}+1 AND " . ( $sStart + $sLimit );
		} elseif ( $wgDBtype == 'postgres' ) {
			$sql = "SELECT i.img_name, i.img_size, i.img_width, i.img_height, ROUND(DATE_PART('epoch', i.img_timestamp)) as img_timestamp
				FROM {$sImageTable} i
				WHERE {$sConds}
				ORDER BY {$sSort}
				OFFSET {$sStart}
				LIMIT {$sLimit}";
		} else {
			$sql = "SELECT i.img_name, i.img_size, i.img_width, i.img_height, UNIX_TIMESTAMP(i.img_timestamp) as img_timestamp
				FROM {$sImageTable} i
				WHERE {$sConds}
				ORDER BY {$sSort}
				LIMIT {$sStart}, {$sLimit}";
		}

		$rowTotal = $dbr->selectRow(
			array( 'i' => 'image' ),
			array( 'total' => 'COUNT(img_name)' ),
			$sConds
		);

		$aOutput = array(
			'total' => $rowTotal->total,
			'images' => array()
		);

		$res = $dbr->query( $sql );

		foreach ( $res as $row ) {
			$img = self::newFromName( $row->img_name );

			$url = $img->createThumb( 48, 48 );//img size for preview in grid cell

			if ( BsExtensionManager::isContextActive( 'MW::SecureFileStore::Active' ) ) {
				$url = SecureFileStore::secureStuff( $url, true );
			}

			$aOutput['images'][] = array(
				'name'    => $row->img_name,
				'url'     => $url,
				'size'    => $row->img_size,
				'lastmod' => $row->img_timestamp,
				'width'   => $row->img_width,
				'height'  => $row->img_height
			);
		}
		return FormatJson::encode( $aOutput );
	}


	/**
	 * Builds filter conditions for SQL query
	 * @param array $nameFilters
	 * @param string $dbType
	 * @return string A SQL fragment containing all filter conditions
	 */
	protected static function buildNameFiltersSQL( $nameFilters, $dbType, $sTableName = 'i.img_name' ) {
		//HINT: CONVERT is needed because field type is VARBINARY.
		//Converting to UTF8 is just a heuristics. SQL is probably
		//nonstandard.
		$sFormat = "LOWER(CONVERT($sTableName USING 'UTF8')) LIKE %s";

		if( $dbType == 'oracle' || $dbType == 'postgres') {
			$sFormat = "LOWER($sTableName) LIKE %s";
		}
		$dbr = wfGetDB( DB_SLAVE );
		$aFormattedFilters = array();
		foreach( $nameFilters as $nameFilter ) {
			//if( empty($nameFilter) ) continue;
			$aFormattedFilters[] = sprintf(
				$sFormat,
				$dbr->addQuotes('%'.strtolower($nameFilter).'%')
			);
		}

		$sNameFilters = implode( ' OR ', $aFormattedFilters );

		if( !empty($sNameFilters) ) {
			$sNameFilters = '('.$sNameFilters.')';
		}

		return $sNameFilters;
	}

	protected static function newFromName( $name ) {
		$title = Title::makeTitleSafe( NS_FILE, $name );
		if ( is_object( $title ) ) {
				$img = wfFindFile( $title );
				if ( !$img ) {
					$img = wfLocalFile( $title );
				}
				return $img;
		} else {
				return null;
		}
	}
}