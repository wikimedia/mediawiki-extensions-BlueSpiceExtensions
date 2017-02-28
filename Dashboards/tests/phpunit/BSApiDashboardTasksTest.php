<?php

/*
 * Test BlueSpiceDashboards API Endpoints
 */

/**
 * @group BlueSpiceDashboards
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */

class BSApiDashboardTasksTest extends BSApiTasksTestBase {

	function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	protected function getModuleName() {
		return 'bs-dashboards-tasks';
	}

	public function testSaveAdminDashboardConfig() {

		//json_encode is needed here, according to
		//BSApiDashboardTasks::task_saveUserDashboardConfig:27 (json_decode( $aPortletConfig );)
		$data = $this->executeTask(
			'saveAdminDashboardConfig', [
				'portletConfig' => [ json_encode( ["someKey" => "someValue", "isFalse" => "true"] ) ]
			]
		);

		$this->assertEquals( true, $data->success );

		return $data;
	}

	public function testSaveUserDashboardConfig() {

		$data = $this->executeTask(
			'saveUserDashboardConfig',
			[
				'portletConfig' => [ json_encode( ["someKey" => "someValue", "isFalse" => "true"] ) ]
			]
		);

		$this->assertEquals( true, $data->success );

		return $data;
	}

}
