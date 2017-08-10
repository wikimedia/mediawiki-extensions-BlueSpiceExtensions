<?php

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceReadConfirmation
 */
class BSApiVisualEditorTasksTest extends BSApiTasksTestBase {
	protected function getModuleName() {
		return 'bs-visualeditor-tasks';
	}

	public function setUp() {
		parent::setUp();
		$this->insertPage( 'Dummy', 'Some random text' );
		$this->insertPage( 'File:Test' );
	}

	public function testCheckLinks() {
		$aData = array(
			'Dummy' => true,
			'Idontexist' => false,
			'Media:Test' => true,
			'File:Test' => true
		);

		$oResponse = $this->executeTask(
			'checkLinks',
			array_keys( $aData )
		);

		$this->assertTrue( $oResponse->success, 'checkLinks task failed' );
		$aPayload = $oResponse->payload;
		$this->assertEquals( array_values( $aData ), $aPayload, 'Response is not as expected' );
	}

	public function testSaveArticle() {
		$oTitle = Title::newFromText( 'Dummy' );
		$iArticleID = $oTitle->getArticleID();

		$oResponse = $this->executeTask(
			'saveArticle',
			[
				'articleId' => $iArticleID,
				'text' => 'Sample text',
				'pageName' => 'Dummy',
				'summary' => 'API test change',
				'editsection' => false
			]
		);

		$this->assertTrue( $oResponse->success, 'checkLinks task failed' );
		$oWikiPage = WikiPage::factory( $oTitle );
		$sText = $oWikiPage->getText();
		$this->assertEquals( 'Sample text', $sText );
	}
}
