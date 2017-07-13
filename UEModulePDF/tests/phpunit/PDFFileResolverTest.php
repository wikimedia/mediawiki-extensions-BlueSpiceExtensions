<?php

/**
 * @group medium
 * @group BlueSpice
 * @group BlueSpiceExtensions
 */
class PDFFileResolverTest extends BSApiTestCase {
	protected $aFiles = null;
	protected $oDOM = null;

	protected $aNames = array(
		'Test.JPG' => 'test.JPG',
		'WithQS.JPG' => 'test.JPG',
		'Template:Dummy.JPG' => 'dummy.JPG'
	);

	protected function setUp() {
		parent::setUp();

		foreach( $this->aNames as $sName => $sFile ) {
			$this->uploadFile( $sName, $sFile );
		}
		$this->createDOM();
	}

	public function testPDFFileResolver() {
		$oImageElements = $this->oDOM->getElementsByTagName( 'img' );

		foreach( $oImageElements as $oImageElement ) {
			$oResolver = new PDFFileResolver( $oImageElement, BsCore::getMediaWikiWebrootPath() );
			$sFileName = $oResolver->getFileName();
			$sAbsoluteFilesystemPath = $oResolver->getAbsoluteFilesystemPath();

			$this->assertArrayHasKey( $sFileName, $this->aFiles, "File name retrieved is not correct" );
			$this->assertTrue( file_exists( $sAbsoluteFilesystemPath ), "File does not exist in the location retrieved" );
			if( $sFileName == "Test.JPG" || $sFileName == "WithQS.JPG" ) {
				$this->assertEquals( '137', $oImageElement->getAttribute( 'width' ) );
			} else if ( $sFileName == "Template:Dummy.JPG" ) {
				$this->assertEquals( '700', $oImageElement->getAttribute( 'width' ) );
			}
		}
	}

	protected function uploadFile( $sName, $sFile ) {
		$oFileTitle = Title::makeTitleSafe( NS_FILE, $sName );
		$this->oFileTitle = $oFileTitle;
		$sOrigName = dirname(__FILE__) . "/data/" . $sFile;
		$oFileObject = wfLocalFile( $oFileTitle );

		$oFileObject->upload( $sOrigName, '', '' );
		$this->aFiles[ $oFileTitle->getText() ] = $oFileObject;
	}

	protected function createDOM() {
		$oDOM = new DOMDocument();
		foreach( $this->aFiles as $sFileName => $oFile ) {
			$oAnchor = $oDOM->createElement( 'a' );
			$oFileTitle = $oFile->getOriginalTitle();
			if( $oFileTitle->getText() !== "WithQS.JPG" ) {
				$oAnchor->setAttribute( 'data-bs-title', $oFileTitle->getFullText() );
			}
			$oImg = $oDOM->createElement( 'img' );
			$oImg->setAttribute( 'src', $oFile->getUrl() );
			if( $oFileTitle->getText() !== "WithQS.JPG" ) {
				$oImg->setAttribute( 'src', $oFile->getUrl() . '?qs=' );
			}
			$oAnchor->appendChild( $oImg );
			$oDOM->appendChild( $oAnchor );
		}
		$this->oDOM = $oDOM;
	}
}

