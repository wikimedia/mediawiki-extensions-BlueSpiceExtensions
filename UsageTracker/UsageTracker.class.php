<?php

/**
 * BlueSpice MediaWiki
 * Extension: UsageTracker
 * Description:
 * Authors: Markus Glaser
 *
 * Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.bluespice.com
 * @author     Your Name <glaser@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Usage Tracker
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

class UsageTracker extends BsExtensionMW {

	/**
	 * Contains the configuration for collectors
	 * @var array Config array
	 */
	public $aCollectorsConfig = array();

	/**
	 * Contains all potential collectors
	 * @var array Object array of BS\\UsageTracker\\Collectors\\Base
	 */
	protected $aCollectors = array();

	/**
	 * Basic initialisation of extension, e.g. hooks, permissions, etc.
	 */
	public function initExt() {
		$this->mCore->registerPermission( 'usagetracker-update', [ 'sysop' ], [ 'type' => 'global' ] );
	}

	/**
	 * Collects usage data from one or several collectors. If $aConfig is not set
	 * it fetches all collectors and adds them to job queue. If $aConfig is set,
	 * it actually collects from the collectors set in config (typically invoked
	 * from job queue and only one collector)
	 * @param array $aConfig
	 * @return BS\UsageTracker\CollectorResult[]
	 */
	public function getUsageData( $aConfig = null ) {
		$this->initializeCollectors( $aConfig );

		// If there is no specific collector, register all known collectors and
		// add them to job queue for deferred collecting
		if ( is_null( $aConfig ) ) {
			foreach ( $this->aCollectors as $oCollector ) {
				$oCollector->registerJob();
			}
			return $this->aCollectors;
		}

		foreach ( $this->aCollectors as $oCollector ) {
			$aData[] = $oCollector->getUsageData( $aConfig );
		}

		// Store collected data in DB for future access
		$dbw = wfGetDB( DB_MASTER );
		foreach ( $aData as $oData ) {
			// Each usage number is only stored once. So delete any old values first.
			$dbw->delete(
				'bs_usagetracker',
				['ut_identifier' => $oData->identifier]
			);
			// Update the count
			$dbw->insert(
				'bs_usagetracker',
				[
					'ut_identifier' => $oData->identifier,
					'ut_count' => $oData->count,
					'ut_type' => $oData->type,
					'ut_timestamp' => wfTimestampNow()
				],
				__METHOD__
			);
		}

		return $aData;
	}

	/**
	 * Load existing data from the database instead of collecting it on the fly,
	 * as collecting data might be very ressource intense.
	 * @param array $aConfig
	 * @return BS\UsageTracker\CollectorResult[]
	 */
	public function getUsageDataFromDB( $aConfig = null ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'bs_usagetracker',
			[
				'ut_identifier',
				'ut_count',
				'ut_type',
				'ut_timestamp'
			],
			[],
			__METHOD__,
			['ORDER BY' => 'ut_identifier']
		);
		$aData = array();
		while( $oRow = $dbr->fetchObject( $res ) ) {
			$aData[] = BS\UsageTracker\CollectorResult::newFromDBRow( $oRow );
		}
		return $aData;
	}

	/**
	 * Gets all available collector if $aConfig is null, otherwise uses collectors
	 * as given in config
	 * @param array $aConfig
	 * @return boolean
	 */
	protected function initializeCollectors( $aConfig = null ) {

		if ( is_null( $aConfig ) ) {
			// Get all the collectors definitions
			Hooks::run( 'BSUsageTrackerRegisterCollectors', array( &$this->aCollectorsConfig ) );
		} else {
			$this->aCollectorsConfig = array();
			$this->aCollectorsConfig[] = $aConfig;
		}

		// Instantiate all collectors from definitions
		// Check if class exists and inherits from Base as configs may
		// contain typos and deprecated declarations.
		foreach ( $this->aCollectorsConfig as $aCollectorConfig ) {
			if ( strpos( $aCollectorConfig['class'], "\\" ) === false ) {
				$classname = "BS\\UsageTracker\\Collectors\\" . $aCollectorConfig['class'];
			}
			if ( class_exists( $classname ) ) {
				$oCollector = new $classname( $aCollectorConfig['config'] );
				if ( $oCollector instanceof BS\UsageTracker\Collectors\Base ) {
					$this->aCollectors[] = new $classname( $aCollectorConfig );
				} else {
					wfDebugLog( "BSUsageTracker", "Class $classname must be inherited from Base" );
				}
			} else {
				wfDebugLog( "BSUsageTracker", "Class $classname must does not exist" );
			}
		}

		return true;
	}

	/**
	 * Adds the table to the database
	 * @param DatabaseUpdater $updater
	 * @return boolean Always true to keep hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_usagetracker',
			__DIR__ .'/db/mysql/UsageTracker.sql'
		);
		return true;
	}
}