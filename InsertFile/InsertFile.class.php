<?php

/**
 * InsertFile extension for BlueSpice
 *
 * Dialogbox to upload files and enter a file link.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @version    1.22.0 stable
 * @version    $Id: InsertFile.class.php 9745 2013-06-14 12:09:29Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage InsertFile
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v1.20.0
 *
 * v1.1.0
 * Added external ExtJS resources and components
 * v1.0.0
 * - raised to stable
 * v0.1
 * - initial commit
 */

// Last review MRG (01.07.11 11:58)

/**
 * Class for file upload and management assistent
 * @package BlueSpice_Extensions
 * @subpackage InsertFile
 */
class InsertFile extends BsExtensionMW {

	/**
	 * Constructor of InsertFile
	 */
	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['InsertFile'] = dirname( __FILE__ ) . '/InsertFile.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'InsertFile',
			EXTINFO::DESCRIPTION => 'Dialogbox to upload files and enter a file link.',
			EXTINFO::AUTHOR => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS => 'stable',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::InsertFile';

		BsCore::registerClass( 'JsonLicenses', dirname( __FILE__ ), 'JsonLicenses.php' );
		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Initialise the InsertFile extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'OutputPageBeforeHTML' );
		$this->setHook( 'RunOnRemote' );
		$this->setHook( 'AlternateEdit' );
		$this->setHook( 'VisualEditorConfig' );

		// Read the mediawiki settings for file upload (read-only)
		// because we need this for the javascript-gui.
		global $wgEnableUploads;
		BsConfig::registerVar( 'MW::InsertFile::EnableUploads', $wgEnableUploads, BsConfig::LEVEL_ADAPTER | BsConfig::RENDER_AS_JAVASCRIPT );

		// Scripts
		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/InsertFile/js/lib', 'FileUploadField', true, true, true, 'ShowInsertFile' );
		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/InsertFile', 'InsertFile', true, false, false, 'ShowInsertFile' );
		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/InsertFile', 'Uploader', true, false, false, 'ShowInsertFile' );
		// Styles
		$this->registerStyleSheet( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/InsertFile/InsertFile.css', true, 'ShowInsertFile' );
		$this->registerStyleSheet( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/InsertFile/css/lib/fileuploadfield.css', true, 'ShowInsertFile' );

		// Remote-Handler
		$this->mAdapter->addRemoteHandler( 'InsertFile', $this, 'getFileRealLink', 'edit' );
		$this->mAdapter->addRemoteHandler( 'InsertFile', $this, 'getFilePage', 'edit' );
		$this->mAdapter->addRemoteHandler( 'InsertFile', $this, 'getUploadedFilePage', 'edit' );
		$this->mAdapter->addRemoteHandler( 'InsertFile', $this, 'getFiles', 'edit' );
		$this->mAdapter->addRemoteHandler( 'InsertFile', $this, 'getPages', 'edit' );
		$this->mAdapter->addRemoteHandler( 'InsertFile', $this, 'getLicenses', 'edit' );

		global $wgFileExtensions;
		$aFileExtensions = BsConfig::get( 'MW::FileExtensions' );
		$aImageExtensions = BsConfig::get( 'MW::ImageExtensions' );

		$wgFileExtensions = array_merge( $aFileExtensions, $aImageExtensions );
		$wgFileExtensions = array_values( array_unique( $wgFileExtensions ) );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	public function onParserFirstCallInit( &$oParser ) {
		BsConfig::registerVar( 'MW::InsertFile::ImageTag', wfMsg ( 'bs-insertfile-image_tag' ), BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT );
		BsConfig::registerVar( 'MW::InsertFile::FileTag', wfMsg ( 'bs-insertfile-file_tag' ), BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT );
		return true;
	}

	public function onOutputPageBeforeHTML( OutputPage &$out, &$text ) {
		global $wgRequest;
		if ( $wgRequest->getVal('action') != 'edit' ) return true;
		global $wgMaxUploadSize;
		$iMaxPhpUploadSize = (int) ini_get('upload_max_filesize');
		$aMaxUploadSize = array(
			'file' => 1024*1024*$iMaxPhpUploadSize,
			'*' => $wgMaxUploadSize
		);
		$out->addJsConfigVars( 'bsMaxUploadSize', $aMaxUploadSize );

		return true;
	}
	
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite) {
		// TODO SW: use string as parameter !!
		$iIndexStandard = array_search( 'unlink',$aConfigStandard["theme_advanced_buttons2"] );
		array_splice( $aConfigStandard["theme_advanced_buttons2"], $iIndexStandard + 1, 0, "hwimage" );
		array_splice( $aConfigStandard["theme_advanced_buttons2"], $iIndexStandard + 2, 0, "hwfile" );

		$iIndexOverwrite = array_search( 'unlink',$aConfigOverwrite["theme_advanced_buttons1"] );
		array_splice( $aConfigOverwrite["theme_advanced_buttons1"], $iIndexOverwrite + 1, 0, "hwimage" );
		return true;
	}

	public function getPages( &$output ) {
		$wgContLang = $this->mAdapter->get( 'ContLang' );
		$sNamespace = $wgContLang->getNsText( 0 );
		$oTestTitle = Title::newFromText( $sNamespace . ':Test' );

		$data = array( 'items' => array( ) );
		if ( is_object( $oTestTitle ) && $oTestTitle->userCan( 'read' ) ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select( array( 'page' ), array( 'page_title', 'page_namespace', 'page_id' ), array( 'page_namespace' => 0 ), null, array( 'ORDER BY' => 'page_title' ) );
			while ( $page = $dbr->fetchRow( $res ) ) {
				$data[ 'items' ][ ] = array(
					'name' => addslashes( $page[ 'page_title' ] ),
					'label' => addslashes( $page[ 'page_title' ] )
				);
			}
		}
		$output = json_encode( $data );
	}

	public function getLicenses( &$output ) {
		$oLicenses = new JsonLicenses();
		$output = $oLicenses->getJsonOutput();
	}

	/**
	 * Add the action buttons to MediaWiki editor.
	 * @return type bool
	 */
	public function onAlternateEdit() {
		BsExtensionManager::setContext( 'ShowInsertFile' );
		$this->mAdapter->addEditButton( 'InsertImage', array(
			'id' => 'btnInsertImage',
			'msg' =>wfMsg( 'bs-insertfile-insert_image' ),
			'image' => '/extensions/BlueSpiceExtensions/InsertFile/images/btn_image.gif',
			'onclick' => "BsFileManager.show('image');"
		) );
		$this->mAdapter->addEditButton( 'InsertFile', array(
			'id' => 'btnInsertFile',
			'msg' =>wfMsg( 'bs-insertfile-insert_file' ),
			'image' => '/extensions/BlueSpiceExtensions/InsertFile/images/btn_insertfile.gif',
			'onclick' => "BsFileManager.show('file');"
		) );
		return true;
	}

	/**
	 * Set an parameter in the session, to recognise an remote upload.
	 * We need this to handle remote uploads within invisible iframes.
	 * Error reporting is set to a low level to prevent the destruction
	 * of ajax outputs.
	 * @param type $uploadForm current UploadForm object
	 * @return type bool Allow other hooked methods to be executed.
	 */
	public function onUploadFormBeforeProcessing( &$uploadForm ) {
		// TODO SU (04.07.11 11:48): global
		global $wgFileExtensions;
		$wgFileExtensions = BsConfig::get( 'MW::FileExtensions' );
		$action = BsCore::getParam( 'action', 'view', BsPARAM::REQUEST | BsPARAMTYPE::STRING );
		$filetype = BsCore::getParam( 'filetype', 'image', BsPARAM::REQUEST | BsPARAMTYPE::STRING );
		if ( $action == 'remote' ) {
			// TODO SU (04.07.11 11:48): MRG: Nonono. Wir setzen keine error_reportings irgendwo im code!
			// TODO SU (04.07.11 11:48): @MRG Muss hier aber sein, da sonst ein zu empfindlich gesetzter ErrorLevel
			// den Upload zerschiesst und wir keine Fehlermeldungen abfangen können.
			error_reporting( E_ERROR );
			$_SESSION[ 'bluespice' ][ 'remote_upload' ] = $filetype;
		}
		return true;
	}

	/**
	 * Check if the page is shown after a remote upload.
	 * If it is true, an error or a warning occurs and we have to filter it out and give back as an ajax output.
	 * @param type $out The OutputPage object.
	 * @param type $sk Skin object that will be used to generate the page, added in 1.13.
	 * @return type bool Allow other hooked methods to be executed.
	 */
	public function onBeforePageDisplay( &$out, &$sk ) {
		// TODO MRG (01.07.11 12:07): muss SESSION['bluespice'] noch auf isset geprüft werden?
		// TODO SU (04.07.11 11:50): @MRG Ja, weil onBeforePageDisplay immer aufgerufen wird,
		// SESSION['bluespice'] aber nur direkt nach einem Upload existiert.
		if ( isset( $_SESSION[ 'bluespice' ][ 'remote_upload' ] ) && $_SESSION[ 'bluespice' ][ 'remote_upload' ] ) {
			if ( preg_match( '%<span class=".+?">(.+?)</span>%s', $out->mBodytext, $regs ) ) {
				echo json_encode( array( 'success' => false, 'msg' => strip_tags( $regs[ 1 ] ) ) );
			}
			elseif ( preg_match( '%<ul class=".+?">(.+?)</ul>%s', $out->mBodytext, $regs ) ) {
				preg_match( '%<li>(.+?)</li>%s', $regs[ 1 ], $_regs );
				echo json_encode( array( 'success' => false, 'msg' => strip_tags( $_regs[ 1 ] ) ) );
			}
			elseif ( preg_match( '%<div class=".+?">(.+?)</div>%s', $out->mBodytext, $regs ) ) {
				echo json_encode( array( 'success' => false, 'msg' => strip_tags( $regs[ 1 ] ) ) );
			}
			else {
				echo json_encode( array( 'success' => true, 'msg' =>wfMsg( 'bs-insertfile-saved_successful' ), 'page' => isset( $_SESSION[ 'bluespice' ][ 'remote_upload_page' ] ) ? $_SESSION[ 'bluespice' ][ 'remote_upload_page' ] : 1 ) );
			}
			$_SESSION[ 'bluespice' ][ 'remote_upload' ] = false;

			// force the output of ajax data and stops the MediaWiki processing
			// TODO MRG (01.07.11 12:07): besser: return true
			// TODO SU (04.07.11 11:50): @MRG Nein, da dann die Special:Upload gerendert wird und uns das JSON unbrauchbar macht.
			die();
		}
		return true;
	}

	/**
	 * Calculate on which page an file is shown and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public function getFilePage( &$output ) {
		global $wgDBtype;
		$filename = BsCore::getParam( 'filename', false, BsPARAM::GET | BsPARAMTYPE::SQL_STRING );
		$type = BsCore::getParam( 'type', 'image', BsPARAM::GET | BsPARAMTYPE::STRING );
		$pagesize = BsCore::getParam( 'pagesize', 12, BsPARAM::GET | BsPARAMTYPE::INT );

		if ( strstr( $filename, 'index.php' ) ) {
			$token = array( );
			parse_str( $filename, $token );
			if ( isset( $token[ 'f' ] ) ) {
				$token = explode( ':', $token[ 'f' ] );
				$filename = $token[ 1 ];
			}
		}

		if ( !$filename ) {
			$output = json_encode(
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

		$image_table = $dbr->tableName( 'image' );

		$res = $dbr->query( $sql );
		if ( $res && $res->numRows() ) {
			$row = $res->fetchObject();
			$page = ceil( $row->rank / $pagesize );
		}
		else {
			$page = 0;
		}
		$output = json_encode(
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
	public function getUploadedFilePage( &$output ) {
		global $wgDBtype;
		$type = BsCore::getParam( 'type', 'image', BsPARAM::GET | BsPARAMTYPE::STRING );
		$sort = BsCore::getParam( 'sort', 'name', BsPARAM::POST | BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING );
		$pagesize = BsCore::getParam( 'pagesize', 12, BsPARAM::GET | BsPARAMTYPE::INT );

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
			}
			else {
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
			}
			else {
				$page = 0;
			}
		}
		else {
			if ( $type == 'image' ) {
				$tbl = $dbr->tableName( 'image' );
				$sql = "SELECT i.img_name, i.img_timestamp
						 FROM {$tbl} i
						 WHERE (i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')
						 ORDER BY {$sql_sort}";
			}
			else {
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
			}
			else {
				$page = 0;
			}
		}

		$output = json_encode(
			array(
				'file' => $filename,
				'page' => $page
			)
		);
	}

	/**
	 * Calculate the real file path of an image to show an preview.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public function getFileRealLink( &$output ) {
		$file = BsCore::getParam( 'filename', false, BsPARAM::REQUEST | BsPARAMTYPE::STRING );
		// TODO MRG (27.09.10 13:13): security: Param durch sanitizer schicken und evtl durch
		// Validator für Filenamen. Fehlermeldung, wenn's nicht passt.
		$url = self::imageUrl( $file );
		if ( BsExtensionManager::isContextActive( 'MW::SecureFileStore::Active' ) ) {
			$url = SecureFileStore::secureStuff( $url, true );
		}
		$output = json_encode( array( 'file' => $file, 'url' => $url ) );
	}

	/**
	 * Process the dataset for the ExtJS file store and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public function getFiles( &$output ) {
		// TODO MRG (27.09.10 13:25): könnte man die auch als parameter setzen?
		$thumbs_width = 80;
		$thumbs_height = 80;
		$file_type = BsCore::getParam( 'type', 'image', BsPARAM::POST | BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING );

		$ucFirstChars = strtoupper( BsCore::getParam( 'firstchars', '', BsPARAM::POST | BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING ) );
		$lcFirstChars = strtolower( $ucFirstChars );
		// TODO MRG (27.09.10 13:13): security: Param durch sanitizer schicken und evtl durch
		// Validator für Filenamen. Fehlermeldung, wenn's nicht passt.
		$start = BsCore::getParam( 'start', 0, BsPARAM::POST | BsPARAMTYPE::INT );
		$limit = BsCore::getParam( 'limit', 12, BsPARAM::POST | BsPARAMTYPE::INT );
		// TODO MRG (27.09.10 13:13): security: Param durch sanitizer schicken und evtl durch
		// Validator für Filenamen. Fehlermeldung, wenn's nicht passt.
		switch ( BsCore::getParam( 'sort', 'name', BsPARAM::POST | BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING ) ) { // switch($_REQUEST['sort'])
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
		$sql_type = '';
		// TODO MRG (27.09.10 13:13): security: Param durch sanitizer schicken und evtl durch
		// Validator für Filenamen. Fehlermeldung, wenn's nicht passt.
		switch ( $file_type ) {
			case 'image':
				$sql_type = "(i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')";
				break;
			case 'file':
				$sql_type = "(i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')";
				break;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$image_table = $dbr->tableName( 'image' );
		global $wgDBtype;
		// TODO: TL (18.08.2011, 15:00)
		// replace global
		if ( $wgDBtype == 'oracle' ) {
			$sql = "SELECT * FROM (SELECT i.img_name, i.img_size, i.img_width, i.img_height, (ROUND(TO_DATE(TO_CHAR(i.img_timestamp, 'YYYYMMDDHH24MISS'), 'YYYYMMDDHH24MISS') - TO_DATE('19700101', 'YYYYMMDDHH24MISS')) * 86400) as img_timestamp,
				row_number() over (order by {$sql_sort}) rnk
				FROM {$image_table} i
				WHERE {$sql_type}
				  AND (UPPER(i.img_name) LIKE '{$ucFirstChars}%' OR LOWER(i.img_name) LIKE '{$lcFirstChars}%'))
				WHERE rnk BETWEEN {$start}+1 AND " . ( $start + $limit );
		}
		elseif ( $wgDBtype == 'postgres' ) {
			$sql = "SELECT i.img_name, i.img_size, i.img_width, i.img_height, ROUND(DATE_PART('epoch', i.img_timestamp)) as img_timestamp
				FROM {$image_table} i
				WHERE {$sql_type}
				  AND (UPPER(i.img_name) LIKE '{$ucFirstChars}%' OR LOWER(i.img_name) LIKE '{$lcFirstChars}%')
				ORDER BY {$sql_sort}
				OFFSET {$start}
				LIMIT {$limit}";
		}
		else {
			// TODO MRG (05.06.11 13:04): CONVERT is needed because type is varbinary. Converting to UTF8 is just a heuristics. SQL is probably nonstandard.
			$sql = "SELECT i.img_name, i.img_size, i.img_width, i.img_height, UNIX_TIMESTAMP(i.img_timestamp) as img_timestamp
				FROM {$image_table} i
				WHERE {$sql_type}
				  AND (UPPER(CONVERT(i.img_name USING 'UTF8')) LIKE '{$ucFirstChars}%' OR LOWER(CONVERT(i.img_name USING 'UTF8')) LIKE '{$lcFirstChars}%')
				ORDER BY {$sql_sort}
				LIMIT {$start}, {$limit}";
		}
		$resAmount = $dbr->query( "SELECT COUNT(*) as amount
							  FROM {$image_table} i
							  WHERE {$sql_type}
							    AND (UPPER(i.img_name) LIKE '{$ucFirstChars}%' OR LOWER(i.img_name) LIKE '{$lcFirstChars}%')" );
		$row = $dbr->fetchRow( $resAmount );
		$totalProperty = $row[ 'amount' ];
		$res = $dbr->query( $sql );

		$aOutput = array(
			'totalCount' => $totalProperty,
			'items' => array()
		);
		while ( $row = $dbr->fetchRow( $res ) ) {
			$img = self::newFromName( $row[ 'img_name' ] );
			
			// small fix for images that are smaller than the default thumb
			if( $thumbs_width > $img->getWidth() ) {
				$thumbs_width = ( $img->getWidth() - 1 );
			}
			if( $thumbs_height > $img->getHeight() ) {
				$thumbs_height = ( $img->getHeight() - 1) ;
			}
			// TODO MRG (27.09.10 13:16): Hier haben wir ein Performance-Problem, wenn es sehr viele
			// Thumbs sind, die auf einmal erzeugt werden. Das kann man momentan nicht lösen.
			// Allerdings ist ein Vermerk sinnvoll.
			$thumb = $img->createThumb( $thumbs_width, $thumbs_height );
			//TODO: test ($thumb != null) necessary?
			$aThumbs = $img->getThumbnails();
			if( $file_type === 'file' ) {
				$url = $thumb;
			} else {
				$url = $img->getThumbUrl( array_pop( $aThumbs ) ).'?ck='.md5($row['img_timestamp']);
			}
			
			if ( BsExtensionManager::isContextActive( 'MW::SecureFileStore::Active' ) ) {
				$url = SecureFileStore::secureStuff( $url, true );
			}
			$aOutput['items'][] = array(
				'name' => $row[ 'img_name' ],
				'url' => $url,
				'size' => $row[ 'img_size' ],
				'lastmod' => $row[ 'img_timestamp' ],
				'width' => $row[ 'img_width' ],
				'height' => $row[ 'img_height' ]
			);
		}
		$output = json_encode( $aOutput );
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
	
	protected static function imageUrl( $name, $fromSharedDirectory = false ) {
		$image = null;
		if( $fromSharedDirectory ) {
			$image = wfFindFile( $name );
		}
		if( !$image ) {
			$image = wfLocalFile( $name );
		}
		return $image->getUrl();
	}

}