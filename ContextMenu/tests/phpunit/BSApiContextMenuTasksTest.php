<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceContextMenu
 */
class BSApiContextMenuTasksTest extends BSApiTasksTestBase {

	protected function setUp() {
		parent::setUp();

		$this->setMwGlobals([
			'wgEmailAuthentication' => false
		]);

		$file = RepoGroup::singleton()->getLocalRepo()->newFile(
			Title::makeTitle( NS_FILE, 'File.txt' )
		);

		$filepath = __DIR__.'/data/file.txt';
		$archive = $file->publish( $filepath );
		$props = FSFile::getPropsFromPath( $filepath );
		$file->recordUpload2( $archive->value, 'Test', 'Test', $props, false );
	}

	protected function getModuleName() {
		return 'bs-contextmenu-tasks';
	}

	/**
	 * @dataProvider provideGetMenuItemData
	 */
	public function testGetMenuItems( $title, $expectedResultFlag, $expectedNoOfEintries ) {
		$response = $this->executeTask( 'getMenuItems', [
			'title' => $title
		] );

		$this->assertEquals( $expectedResultFlag, $response->success, 'The "success" flag did not match expectations' );

		$items = [];
		if( $response->success === true ) {
			$response = (array) $response;
			$items = $response['payload']['items'];
		}

		$this->assertEquals( $expectedNoOfEintries, count( $items ), 'The number of returned items did not match expectations' );
	}

	public function provideGetMenuItemData() {
		return [
			'no title set ' => [ '', false, 0 ],
			'normal wiki page' => [ 'UTPage', true, 5 ],
			'normal non existing wiki page' => [ 'Page does not exist', true, 1 ],
			'non existing user page' => [ 'User:UTSysop', true, 2 ],
			'file page' => [ 'File:File.txt', true, 8 ],
		];
	}
}