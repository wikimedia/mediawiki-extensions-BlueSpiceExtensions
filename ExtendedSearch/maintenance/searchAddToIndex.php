<?php
//useage: php searchAddToIndex.php --input aritclenames.txt

require_once( __DIR__ . '/../../../BlueSpiceFoundation/maintenance/BSMaintenance.php' );

class searchAddToIndex extends BSMaintenance {

	protected $sBasePath = '';

	public function __construct() {
		parent::__construct();
		$this->addOption( 'input', 'plain text list of article names ( NS:Title )', true, true );
		$this->requireExtension( 'BlueSpiceExtensions' );
	}

	/**
	 * Get List and Update Seach Index
	 * @param WikiPage $oPage
	 * @param Article $oArticle The article that is created.
	 * @param string $sText New text.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function execute() {
		$sSrc = file_get_contents( $this->getOption( 'input' ) );
		$aSrc = explode( "\n", $sSrc );

		foreach ( $aSrc as $sLine ) {
			if( trim( $sLine ) === '' ){
				continue;
			}
			$oTitle = Title::newFromText( trim( $sLine ) );
			if( $oTitle instanceof  Title === false ) {
				$this->error( "Error: '$sLine' is not a valid title" );
				continue;
			}

			if( $oTitle->exists() === false ) {
				$this->error( "Error: '{$oTitle->getPrefixedDBkey()}' does not exist" );
				continue;
			}

			$oArticle = new Article( $oTitle );
			$this->addArticle( $oArticle );
		}
	}

	/*
	 * Copy of ExtendedSearch.class.php onArticleSaveComplete
	 */
	protected function addArticle( &$oArticle ) {
		try {
			BuildIndexMainControl::getInstance()->updateIndexWiki( $oArticle );
			$oTitle = $oArticle->getTitle();
			$this->output( "Indexing article '{$oTitle->getPrefixedDBkey()}'...");

			if ( $oTitle->getNamespace() === NS_FILE ) {
				$oFile = wfFindFile( $oTitle );
				$this->output( "Indexing file '{$oTitle->getPrefixedDBkey()}'...");

				BuildIndexMainControl::getInstance()->deleteIndexFile( $oFile->getPath(), 'repo' );
				BuildIndexMainControl::getInstance()->updateIndexFile( $oFile );
			}
		} catch ( BsException $e ) {
			$this->error( "Error: " . $e->getMessage() );
		}
		return true;
	}

}

$maintClass = 'searchAddToIndex';

require_once RUN_MAINTENANCE_IF_MAIN;
