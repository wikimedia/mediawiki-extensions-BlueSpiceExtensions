<?php

/**
 * Review Extension for BlueSpice
 *
 * Adds workflow functionality to pages.
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
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @version    2.27.1
 * @package    BlueSpice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Main class for Review extension
 * @package    BlueSpice_Extensions
 * @subpackage Review
 */
class Review extends BsExtensionMW {

	/**
	 * Stores the current logger that writes to MW log
	 * @var LogPage Logger object that writes to MW log
	 */
	protected $oLogger;
	/**
	 * Initialization of Review extension
	 */
	protected function initExt() {
		// Register style in constructor in order to have it loaded on special pages
		BsConfig::registerVar( 'MW::Review::CheckOwner', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-review-pref-checkowner', 'toggle' );
		BsConfig::registerVar( 'MW::Review::EmailNotifyOwner', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-review-pref-emailnotifyowner', 'toggle' );
		BsConfig::registerVar( 'MW::Review::EmailNotifyReviewer', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-review-pref-emailnotifyreviewer', 'toggle' );
		BsConfig::registerVar(
		  'MW::Review::Permissions', $GLOBALS[ 'bsgDefaultReviewAdditionalPermissions' ], BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-review-pref-permissions', 'multiselectex'
		);

		$this->mCore->registerPermission( 'workflowview', array( 'user' ), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'workflowedit', array(), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'workflowlist', array(), array( 'type' => 'global' ) );

		global $wgLogActionsHandlers;
		$wgLogActionsHandlers[ 'bs-review/create' ] = array( $this, 'logCreate' );
		$wgLogActionsHandlers[ 'bs-review/modify' ] = array( $this, 'logModify' );
		$wgLogActionsHandlers[ 'bs-review/delete' ] = array( $this, 'logDelete' );
		$wgLogActionsHandlers[ 'bs-review/approve' ] = array( $this, 'logApprove' );
		$wgLogActionsHandlers[ 'bs-review/deny' ] = array( $this, 'logDeny' );
		$wgLogActionsHandlers[ 'bs-review/finish' ] = array( $this, 'logFinish' );

		$this->oLogger = new LogPage( 'bs-review', false );

		BSNotifications::registerNotificationCategory( 'bs-review-assignment-cat' );
		BSNotifications::registerNotificationCategory( 'bs-review-action-cat' );
		BSNotifications::registerNotification(
			'bs-review-assign',
			'bs-review-assignment-cat',
			'notification-bs-review-assign-summary',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-assign-subject',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-assign-body',
			array( 'agent', 'title', 'titlelink' ),
			array(
				'formatter-class' => 'ReviewFormatter',
				'payload' => array( 'comment' )
			)
		);
		BSNotifications::registerNotification(
			'bs-review-accept',
			'bs-review-action-cat',
			'notification-bs-review-accept-summary',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-accept-subject',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-accept-body',
			array( 'agent', 'title', 'titlelink' ),
			array(
				'formatter-class' => 'ReviewFormatter',
				'payload' => array( 'comment' )
			)
		);
		BSNotifications::registerNotification(
			'bs-review-deny',
			'bs-review-action-cat',
			'notification-bs-review-deny-summary',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-deny-subject',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-deny-body',
			array( 'agent', 'title', 'titlelink' ),
			array(
				'formatter-class' => 'ReviewFormatter',
				'payload' => array( 'comment' )
			)
		);
		BSNotifications::registerNotification(
			'bs-review-deny-and-restart',
			'bs-review-action-cat',
			'notification-bs-review-deny-and-restart-summary',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-deny-and-restart-subject',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-deny-and-restart-body',
			array( 'agent', 'title', 'titlelink' ),
			array(
				'formatter-class' => 'ReviewFormatter',
				'payload' => array( 'comment' )
			)
		);
		BSNotifications::registerNotification(
			'bs-review-finish',
			'bs-review-action-cat',
			'notification-bs-review-finish-summary',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-finish-subject',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-finish-body',
			array( 'agent', 'title', 'titlelink' ),
			array(
				'formatter-class' => 'ReviewFormatter',
				'payload' => array( 'comment' )
			)
		);
		BSNotifications::registerNotification(
			'bs-review-finish-and-autoflag',
			'bs-review-action-cat',
			'notification-bs-review-finish-and-autoflag-summary',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-finish-and-autoflag-subject',
			array( 'agent', 'title', 'titlelink' ),
			'notification-bs-review-finish-and-autoflag-body',
			array( 'agent', 'title', 'titlelink' ),
			array(
				'formatter-class' => 'ReviewFormatter',
				'payload' => array( 'comment' )
			)
		);
	}

	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		switch ( $event->getType() ) {
			case 'bs-review-assign':
				$extra = $event->getExtra();
				if ( !$extra || !isset( $extra[ 'next-users' ] ) ) {
					break;
				}
				$aNextUsers = $extra[ 'next-users' ];
				foreach ( $aNextUsers as $aUser ) {
					$users[ $aUser[ 'id' ] ] = User::newFromId( $aUser[ 'id' ] );
				}
				break;
			case 'bs-review-accept':
			case 'bs-review-deny':
			case 'bs-review-deny-and-restart':
			case 'bs-review-finish':
			case 'bs-review-finish-and-autoflag':
				$extra = $event->getExtra();
				if ( !$extra || !isset( $extra[ 'owner' ] ) ) {
					break;
				}
				$oOwner = $extra[ 'owner' ];
				$users[ $oOwner->getId() ] = $oOwner;
				break;
		}

		return true;
	}

	/**
	 * Sets up required database tables
	 *
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 *
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtModifiedFields, $wgExtNewIndexes, $wgExtNewFields;
		$sDir = __DIR__ . DS;

		if ( $wgDBtype == 'mysql' ) {
			$updater->addExtensionTable(
				'bs_review',
				$sDir . 'db/mysql/review.sql'
			);

			$dbr = wfGetDB( DB_SLAVE );

			if ( $dbr->tableExists( 'bs_review' ) ) {
				if ( !$dbr->fieldExists( 'bs_review', 'rev_sequential' ) ) {
					$updater->addExtensionField(
						'bs_review',
						'rev_sequential',
						$sDir . 'db/mysql/review.patch.rev_sequential.sql'
					);
				}
				if ( !$dbr->fieldExists( 'bs_review', 'rev_abortable' ) ) {
					$updater->addExtensionField(
						'bs_review',
						'rev_abortable',
						$sDir . 'db/mysql/review.patch.rev_abortable.sql'
					);
				}
			}
			if ( $dbr->tableExists( 'bs_review_steps' ) && !$dbr->fieldExists( 'bs_review_steps', 'delegate_to' ) ) {
				$updater->addExtensionField(
					'bs_review_steps',
					'revs_delegate_to',
					$sDir . 'db/mysql/review.patch.revs_delegate_to.sql'
				);
			}
			if ( $dbr->tableExists( 'bs_review' ) ) {
				if ( !$dbr->fieldExists( 'bs_review_templates', 'revt_editable' ) ) {
					$updater->addExtensionField(
						'bs_review_templates',
						'revt_editable',
						$sDir . 'db/mysql/review_templates.patch.revt_editable.sql'
					);
				}
				if ( !$dbr->fieldExists( 'bs_review_templates', 'revt_sequential' ) ) {
					$updater->addExtensionField(
						'bs_review_templates',
						'revt_sequential',
						$sDir . 'db/mysql/review_templates.patch.revt_sequential.sql'
					);
				}
				if ( !$dbr->fieldExists( 'bs_review_templates', 'revt_abortable' ) ) {
					$updater->addExtensionField(
						'bs_review_templates',
						'revt_abortable',
						$sDir . 'db/mysql/review_templates.patch.revt_abortable.sql'
					);
				}
			}

			$updater->modifyExtensionField( 'bs_review', 'id', $sDir . 'db/mysql/review.patch.id.sql' );
			$updater->modifyExtensionField( 'bs_review', 'pid', $sDir . 'db/mysql/review.patch.pid.sql' );
			$updater->modifyExtensionField( 'bs_review', 'editable', $sDir . 'db/mysql/review.patch.editable.sql' );
			$updater->modifyExtensionField( 'bs_review', 'mode', $sDir . 'db/mysql/review.patch.mode.sql' );
			$updater->modifyExtensionField( 'bs_review', 'rev_mode', $sDir . 'db/mysql/review.patch.rev_mode.sql' );
			$updater->modifyExtensionField( 'bs_review', 'startdate', $sDir . 'db/mysql/review.patch.startdate.sql' );
			$updater->modifyExtensionField( 'bs_review', 'enddate', $sDir . 'db/mysql/review.patch.enddate.sql' );
			$updater->modifyExtensionField( 'bs_review', 'owner', $sDir . 'db/mysql/review.patch.owner.sql' );

			$updater->modifyExtensionField( 'bs_review_steps', 'id', $sDir . 'db/mysql/review_steps.patch.id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'review_id', $sDir . 'db/mysql/review_steps.patch.review_id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'user_id', $sDir . 'db/mysql/review_steps.patch.user_id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'status', $sDir . 'db/mysql/review_steps.patch.status.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'sort_id', $sDir . 'db/mysql/review_steps.patch.sort_id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'comment', $sDir . 'db/mysql/review_steps.patch.comment.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'delegate_to', $sDir . 'db/mysql/review_steps.patch.delegate_to.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'timestamp', $sDir . 'db/mysql/review_steps.patch.timestamp.sql' );

			$updater->modifyExtensionField( 'bs_review_steps', 'id', $sDir . 'db/mysql/review_steps.patch.id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'review_id', $sDir . 'db/mysql/review_steps.patch.review_id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'user_id', $sDir . 'db/mysql/review_steps.patch.user_id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'status', $sDir . 'db/mysql/review_steps.patch.status.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'sort_id', $sDir . 'db/mysql/review_steps.patch.sort_id.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'comment', $sDir . 'db/mysql/review_steps.patch.comment.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'delegate_to', $sDir . 'db/mysql/review_steps.patch.delegate_to.sql' );
			$updater->modifyExtensionField( 'bs_review_steps', 'timestamp', $sDir . 'db/mysql/review_steps.patch.timestamp.sql' );

			$updater->modifyExtensionField( 'bs_review_templates', 'id', $sDir . 'db/mysql/review_templates.patch.id.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'name', $sDir . 'db/mysql/review_templates.patch.name.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'owner', $sDir . 'db/mysql/review_templates.patch.owner.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'user', $sDir . 'db/mysql/review_templates.patch.user.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'mode', $sDir . 'db/mysql/review_templates.patch.mode.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'revt_mode', $sDir . 'db/mysql/review_templates.patch.revt_mode.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'revt_mode', $sDir . 'db/mysql/review_templates.patch.revt_mode.sql' );
			$updater->modifyExtensionField( 'bs_review_templates', 'public', $sDir . 'db/mysql/review_templates.patch.public.sql' );
		} elseif ( $wgDBtype == 'postgres' ) {
			$wgExtNewTables[] = array( 'bs_review', $sDir . 'db/postgres/review.pg.sql' );

			$dbr = wfGetDB( DB_MASTER );
			if ( $dbr->tableExists( 'bs_review_steps' ) && !$dbr->fieldExists( 'bs_review_steps', 'delegate_to' ) && !$dbr->fieldExists( 'bs_review_steps', 'revs_delegate_to' ) ) {
				//PW(25.06.2012) wont work on mw 1.16.5
				//$wgExtNewFields[ ] = array( 'bs_review_steps', 'revs_delegate_to', $sDir . 'db/postgres/review.patch.delegate_to.pg.sql' );
				$dbr->query( "ALTER TABLE " . $dbr->tableName( "bs_review_steps" ) . " ADD revs_delegate_to SMALLINT NOT NULL DEFAULT '0' AFTER revs_comment;" );
			}

			$aFieldsToPrefix = array(
				'bs_review' => array(
					'id' => 'rev_id',
					'pid' => 'rev_pid',
					'editable' => 'rev_editable',
					'mode' => 'rev_mode',
					'startdate' => 'rev_startdate',
					'enddate' => 'rev_enddate',
					'owner' => 'rev_owner' ),
				'bs_review_steps' => array(
					'id' => 'revs_id',
					'review_id' => 'revs_review_id',
					'user_id' => 'revs_user_id',
					'status' => 'revs_status',
					'sort_id' => 'revs_sort_id',
					'comment' => 'revs_comment',
					'delegate_to' => 'revs_delegate_to',
					'timestamp' => 'revs_timestamp' ),
				'bs_review_templates' => array(
					'id' => 'revt_id',
					'name' => 'revt_name',
					'owner' => 'revt_owner',
					'user' => 'revt_user',
					'mode' => 'revt_mode',
					'public' => 'revt_public' )
			);

			foreach ( $aFieldsToPrefix as $sTable => $aField ) {
				echo $sTable;
				foreach ( $aField as $sOld => $sNew ) {
					if ( $dbr->fieldExists( $sTable, $sOld ) ) {
						if ( $sOld == 'user' )
							$sOld = '"' . $sOld . '"'; //PW: user is a keyword on modify
						$dbr->query( 'ALTER TABLE ' . $dbr->tableName( $sTable ) . ' RENAME ' . $sOld . ' TO ' . $sNew . ';' );
					}
				}
			}
			if ( $dbr->tableExists( 'bs_review_steps' ) ) {
				$dbr->query( 'ALTER TABLE ONLY ' . $dbr->tableName( 'bs_review_steps' ) . ' ALTER COLUMN revs_timestamp set DEFAULT CURRENT_TIMESTAMP' );
			}

			$wgExtNewIndexes[] = array( 'bs_review', 'rev_pid', $sDir . 'db/postgres/review.patch.rev_pid.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review', 'rev_startdate', $sDir . 'db/postgres/review.patch.rev_startdate.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review', 'rev_owner', $sDir . 'db/postgres/review.patch.rev_owner.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_steps', 'revs_review_id', $sDir . 'db/postgres/review_steps.patch.revs_review_id.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_steps', 'revs_user_id', $sDir . 'db/postgres/review_steps.patch.revs_user_id.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_steps', 'revs_status', $sDir . 'db/postgres/review_steps.patch.revs_status.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_templates', 'revt_owner', $sDir . 'db/postgres/review_templates.patch.revt_owner.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_templates', 'revt_name', $sDir . 'db/postgres/review_templates.patch.revt_name.index.pg.sql' );
		} elseif ( $wgDBtype == 'oracle' ) {
			$wgExtNewTables[] = array( 'bs_review', $sDir . 'review.oci.sql' );

			$dbr = wfGetDB( DB_SLAVE );
			if ( !$dbr->fieldExists( 'bs_review_steps', 'delegate_to' ) ) {
				$wgExtNewFields[] = array( 'bs_review_steps', 'revs_delegate_to', $sDir . 'db/oracle/review.patch.revs_delegate_to.oci.sql' );
			} else {
				if ( !$dbr->fieldExists( 'bs_review_steps', 'revs_delegate_to' ) ) {
					$dbr->query( 'ALTER TABLE ' . $dbr->tableName( 'bs_review_steps' ) . ' RENAME COLUMN delegate_to TO revs_delegate_to' );
					//wont work on linux for NO reason ...
					//$wgExtModifiedFields[ ] = array( 'bs_review_steps', 'delegate_to', $sDir . 'db/oracle/review_steps.patch.delegate_to.sql' );
				}
			}

			$wgExtModifiedFields[] = array( 'bs_review_steps', 'revs_timestamp', $sDir . 'db/oracle/review_steps.patch.revs_timestamp.sql' );

			$wgExtNewIndexes[] = array( 'bs_review', 'rev_pid', $sDir . 'db/oracle/review.patch.pid.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_review', 'rev_startdate', $sDir . 'db/oracle/review.patch.startdate.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_review', 'rev_owner', $sDir . 'db/oracle/review.patch.owner.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_steps', 'revs_review_id', $sDir . 'db/oracle/review.patch.review_id.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_steps', 'revs_user_id', $sDir . 'db/oracle/review.patch.user_id.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_steps', 'revs_status', $sDir . 'db/oracle/review.patch.status.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_review_templates', 'revt_name', $sDir . 'db/oracle/review.patch.name.index.oci.sql' );
		} elseif ( $wgDBtype == 'sqlite' ) {
			$updater->addExtensionTable(
				'bs_review',
				$sDir . 'db/mysql/review.sql'
			);
		}

		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 *
	 * @param array $aSortTopVars
	 *
	 * @return boolean Always true to keep hook running
	 */
	public static function onStatebarAddSortTopVars( &$aSortTopVars ) {
		$aSortTopVars[ 'statebartopreview' ] = wfMessage( 'bs-review-review' )->plain();

		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 *
	 * @param array $aSortBodyVars
	 *
	 * @return boolean Always true to keep hook running
	 */
	public static function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars[ 'statebarbodyreview' ] = wfMessage( 'bs-review-review' )->plain();

		return true;
	}

	/**
	 * Adds the "Review" menu entry in view mode
	 *
	 * @param SkinTemplate $oSkinTemplate
	 * @param array        $links
	 *
	 * @return boolean Always true to keep hook running
	 */
	public static function onSkinTemplateNavigation( SkinTemplate $oSkinTemplate, &$links ) {
		if ( $oSkinTemplate->getTitle()->exists() === false ) {
			return true;
		}
		if ( $oSkinTemplate->getTitle()->userCan( 'workflowview' ) === false ) {
			return true;
		}

		$links[ 'actions' ][ 'review' ] = array(
			'text' => wfMessage( 'bs-review-menu-entry' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-review'
		);

		return true;
	}

	public static function getData() {
		global $wgUser, $wgDBtype;

		$dbr = wfGetDB( DB_SLAVE );
		$tbl_rev = $dbr->tableName( 'bs_review' );
		$tbl_step = $dbr->tableName( 'bs_review_steps' );
		$tbl_page = $dbr->tableName( 'page' );
		$tbl_user = $dbr->tableName( 'user' );

		$sql = 'SELECT  r.rev_id, r.rev_pid, p.page_title, p.page_namespace, u.user_name, u.user_real_name, u.user_id, r.rev_editable, r.rev_sequential, r.rev_abortable, rs.revs_status, u2.user_name AS owner_name, u2.user_real_name AS owner_real_name, rs.revs_delegate_to AS revs_delegate_to, ';
		switch ( $wgDBtype ) {
			case 'postgres' : {
				$sql .= "        EXTRACT(EPOCH FROM TO_TIMESTAMP(r.rev_enddate, 'YYYYMMDDHH24MISS')) AS endtimestamp, TO_CHAR(TO_DATE(r.rev_startdate, 'YYYYMMDDHH24MISS'), 'DD.MM.YYYY') AS startdate, ";
				$sql .= "        TO_CHAR(TO_DATE(r.rev_enddate, 'YYYYMMDDHH24MISS'), 'DD.MM.YYYY') AS enddate, TO_CHAR(rs.revs_timestamp::timestamp, 'DD.MM') AS stepdate ";
				break;
			}
			case 'oracle' : {
				$sql .= '        (ROUND(TO_DATE(r.rev_enddate, \'YYYYMMDDHH24MISS\') - TO_DATE(\'19700101\', \'YYYYMMDDHH24MISS\')) * 86400) endtimestamp, TO_CHAR(TO_DATE(r.rev_startdate, \'YYYYMMDDHH24MISS\'), \'DD.MM.YYYY\') startdate, ';
				$sql .= '        TO_CHAR(TO_DATE(r.rev_enddate, \'YYYYMMDDHH24MISS\'), \'DD.MM.YYYY\') enddate, TO_CHAR(rs.revs_timestamp, \'DD.MM\') stepdate ';
				break;
			}
			default: {
				$sql .= '        UNIX_TIMESTAMP(r.rev_enddate) AS endtimestamp, DATE_FORMAT(r.rev_startdate, "%d.%m.%Y") AS startdate, ';
				$sql .= '        DATE_FORMAT(r.rev_enddate, "%d.%m.%Y") AS enddate, DATE_FORMAT(rs.revs_timestamp, "%d.%m") AS stepdate ';
			}
		}
		$sql .= 'FROM ' . $tbl_rev . ' AS r, ' . $tbl_step . ' AS rs, ' . $tbl_page . ' AS p, ' . $tbl_user . ' AS u, ' . $tbl_user . ' AS u2 ';
		$sql .= 'WHERE r.rev_pid=p.page_id AND r.rev_id=rs.revs_review_id AND rs.revs_user_id=u.user_id AND r.rev_owner=u2.user_id ';

		// What is the user allowed to see?
		if ( $wgUser->isAllowed( 'workflowlist' ) ) {
			global $wgRequest;
			$iUserId = $wgRequest->getInt( 'user', $wgRequest->getInt( 'userID', $wgUser->mId ) );
			// if( intval($_GET['user']) )
			if ( $iUserId ) { // <== getParam returns default (false) if INT is expected and param is not numeric
				//$sql.= 'AND (r.owner="'. $_GET['user'] .'" OR "'. $_GET['user'] .'" IN (SELECT hrs.user_id FROM hw_review_steps AS hrs WHERE hrs.review_id=r.id)) ';
				$sql .= 'AND (r.rev_owner=' . $iUserId . ' OR EXISTS (SELECT 1 FROM ' . $tbl_step . ' AS hrs WHERE hrs.revs_review_id=r.rev_id AND (hrs.revs_user_id = ' . $iUserId . ' OR hrs.revs_delegate_to = ' . $iUserId . '))) ';
			}
		} else {
			$sql .= 'AND (r.rev_owner=' . $wgUser->mId . ' OR EXISTS (SELECT 1 FROM ' . $tbl_step . ' AS hrs WHERE hrs.revs_review_id=r.rev_id AND (hrs.revs_user_id = ' . $wgUser->mId . ' OR hrs.revs_delegate_to = ' . $wgUser->mId . '))) ';
		}

		$sql .= 'ORDER BY r.rev_startdate DESC, rs.revs_sort_id';
		$res = $dbr->query( $sql );

		// Sorting the data because of the status column (accepted status)
		$arrList = array();
		while ($row = $dbr->fetchRow( $res )) {

			if ( !isset( $arrList[ $row[ 'rev_id' ] ] ) ) {
				$arrList[ $row[ 'rev_id' ] ][ 'array' ] = $row;
			}

			$objReview = BsReviewProcess::newFromPid( $row[ 'rev_pid' ] );
			$arrList[ $row[ 'rev_id' ] ][ 'revs_status' ] = $objReview->getStatus( $row[ 'endtimestamp' ] );

			switch ( $row[ 'revs_status' ] ) {
				case '-1':
					$arrList[ $row[ 'rev_id' ] ][ 'total' ] = isset( $arrList[ $row[ 'rev_id' ] ][ 'total' ] ) ? $arrList[ $row[ 'rev_id' ] ][ 'total' ] + 1 : 1;
					break;
				case '0':
					//case '-3':
					$arrList[ $row[ 'rev_id' ] ][ 'rejected' ] = isset( $arrList[ $row[ 'rev_id' ] ][ 'rejected' ] ) ? $arrList[ $row[ 'rev_id' ] ][ 'rejected' ] + 1 : 1;
					$arrList[ $row[ 'rev_id' ] ][ 'total' ] = isset( $arrList[ $row[ 'rev_id' ] ][ 'total' ] ) ? $arrList[ $row[ 'rev_id' ] ][ 'total' ] + 1 : 1;
					break;
				case '1':
					//case '-2':
					$arrList[ $row[ 'rev_id' ] ][ 'accepted' ] = isset( $arrList[ $row[ 'rev_id' ] ][ 'accepted' ] ) ? $arrList[ $row[ 'rev_id' ] ][ 'accepted' ] + 1 : 1;
					$arrList[ $row[ 'rev_id' ] ][ 'total' ] = isset( $arrList[ $row[ 'rev_id' ] ][ 'total' ] ) ? $arrList[ $row[ 'rev_id' ] ][ 'total' ] + 1 : 1;
					break;
			}
			$row[ 'revs_delegate_to_real_name' ]
				= $row[ 'revs_delegate_to_name' ]
				= '';

			if( !empty( $row[ 'revs_delegate_to' ] ) ) {
				$oDelegateUser = User::newFromId(
					(int)$row[ 'revs_delegate_to' ]
				);
				if( !$oDelegateUser->isAnon() ) {
					$row[ 'revs_delegate_to_name' ] = $oDelegateUser->getName();
					$row[ 'revs_delegate_to_real_name' ]
						= empty( $oDelegateUser->getRealName() )
						? $oDelegateUser->getName()
						: $oDelegateUser->getRealName()
					;
				}
			}

			$arrList[ $row[ 'rev_id' ] ][ 'assessors' ][] = array(
				'name' => $row[ 'user_name' ],
				'real_name' => $row[ 'user_real_name' ],
				'revs_status' => $row[ 'revs_status' ],
				'timestamp' => $row[ 'stepdate' ],
				'delegate_to' => $row[ 'revs_delegate_to' ],
				'delegate_to_real_name' => $row[ 'revs_delegate_to_real_name' ],
				'delegate_to_name' => $row[ 'revs_delegate_to_name' ],
			);

		}
		return $arrList;
	}

	/**
	 * @return LogPage
	 */
	public function getLogger() {
		return $this->oLogger;
	}

	/**
	 * Produces a log message for bs-review/create.
	 *
	 * @param string $type            Log type as defined for MediaWiki.
	 * @param string $action          Log type as defined for MediaWiki.
	 * @param Title  $title           Title of the page for which an action is being logged.
	 * @param Skin   $skin            Skin object.
	 * @param array  $params          Not used.
	 * @param bool   $filterWikilinks Not used.
	 *
	 * @return string Internationalized log message.
	 */
	public function logCreate( $type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false ) {
		$oUser = $this->getUser();

		return wfMessage( 'bs-review-created-review', $oUser->getName(), Linker::link( $title, $title->getText() ) )->text();
	}

	// TODO RBV (30.06.11 13:07): Maybe a callback function would have done the trick, that chooses the return value according to $action?
	/**
	 * Produces a log message for bs-review/modify.
	 *
	 * @param string $type            Log type as defined for MediaWiki.
	 * @param string $action          Log type as defined for MediaWiki.
	 * @param Title  $title           Title of the page for which an action is being logged.
	 * @param Skin   $skin            Skin object.
	 * @param array  $params          Not used.
	 * @param bool   $filterWikilinks Not used.
	 *
	 * @return string Internationalized log message.
	 */
	public function logModify( $type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false ) {
		$oUser = $this->getUser();

		return wfMessage( 'bs-review-modified-review', $oUser->getName(), Linker::link( $title, $title->getText() ) )->text();
	}

	/**
	 * Produces a log message for bs-review/delete.
	 *
	 * @param string $type            Log type as defined for MediaWiki.
	 * @param string $action          Log type as defined for MediaWiki.
	 * @param Title  $title           Title of the page for which an action is being logged.
	 * @param Skin   $skin            Skin object.
	 * @param array  $params          Not used.
	 * @param bool   $filterWikilinks Not used.
	 *
	 * @return string Internationalized log message.
	 */
	public function logDelete( $type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false ) {
		$oUser = $this->getUser();

		return wfMessage( 'bs-review-deleted-review', $oUser->getName(), Linker::link( $title, $title->getText() ) )->text();
	}

	/**
	 * Produces a log message for bs-review/approve.
	 *
	 * @param string $type            Log type as defined for MediaWiki.
	 * @param string $action          Log type as defined for MediaWiki.
	 * @param Title  $title           Title of the page for which an action is being logged.
	 * @param Skin   $skin            Skin object.
	 * @param array  $params          Not used.
	 * @param bool   $filterWikilinks Not used.
	 *
	 * @return string Internationalized log message.
	 */
	public function logApprove( $type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false ) {
		$oUser = $this->getUser();

		return wfMessage( 'bs-review-approved-review', $oUser->getName(), Linker::link( $title, $title->getText() ) )->text();
	}

	/**
	 * Produces a log message for bs-review/Deny.
	 *
	 * @param string $type            Log type as defined for MediaWiki.
	 * @param string $action          Log type as defined for MediaWiki.
	 * @param Title  $title           Title of the page for which an action is being logged.
	 * @param Skin   $skin            Skin object.
	 * @param array  $params          Not used.
	 * @param bool   $filterWikilinks Not used.
	 *
	 * @return string Internationalized log message.
	 */
	public function logDeny( $type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false ) {
		$oUser = $this->getUser();

		return wfMessage( 'bs-review-denied-review', $oUser->getName(), Linker::link( $title, $title->getText() ) )->text();
	}

	/**
	 * Produces a log message for bs-review/finish.
	 *
	 * @param string $type            Log type as defined for MediaWiki.
	 * @param string $action          Log type as defined for MediaWiki.
	 * @param Title  $title           Title of the page for which an action is being logged.
	 * @param Skin   $skin            Skin object.
	 * @param array  $params          Not used.
	 * @param bool   $filterWikilinks Not used.
	 *
	 * @return string Internationalized log message.
	 */
	public function logFinish( $type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false ) {
		$oUser = $this->getUser();

		return wfMessage( 'bs-review-finished-review', $oUser->getName(), Linker::link( $title, $title->getText() ) )->text();
	}

	/**
	 * Can a user edit a particular page?
	 *
	 * @param Title  $oTitle  Title object of current page.
	 * @param User   $oUser   Currently authenticated user.
	 * @param string $sAction Action for which a permission is being requested.
	 * @param bool   $bRight  Is user currently allowed to do the action on the page? If this is set to false,
	 *                        permission will be denied.
	 *
	 * @return bool Allow other hooked methods to be executed. False if edit right is denied.
	 */
	public static function checkReviewPermissions( $oTitle, $oUser, $sAction, &$bRight ) {
		$aActionsBlacklist = array( 'edit', 'delete', 'move', 'protect', 'rollback' );

		$oRev = BsReviewProcess::newFromPid( $oTitle->getArticleID() );
		if ( !$oRev ) {
			return true; // There is no review on the page
		}


		// Because of FlaggedRevs is it now allowed to edit when a workflow is finished...
		$bResult = false;
		wfRunHooks( 'checkPageIsReviewable', array( $oTitle, &$bResult ) );

		if ( ( $oRev->isActive() ) || ( $oRev->isStarted() && $bResult == false ) ) {
			// Restrict access only after review process has been started
			if ( !$oRev->isEditable() ) {
				$bRight = false;

				return false;
			}

			// check, if current user can currently review.
			$aPages = BsReviewProcess::listReviews( $oUser->getId() );
			if ( !in_array( $oTitle->getArticleID(), $aPages ) ) {
				$bRight = false;

				return false;
			}
		}

		return true;
	}

	/**
	 * Prevents the FlaggedRevsConnector form from being shown when a workflow is active
	 *
	 * @param Title $oCurrentTitle
	 * @param array $aFlagInfo
	 *
	 * @return boolean
	 */
	public static function onBSFlaggedRevsConnectorCollectFlagInfo( $oCurrentTitle, &$aFlagInfo ) {
		$oRev = BsReviewProcess::newFromPid( $oCurrentTitle->getArticleID() );
		if ( $oRev instanceof BsReviewProcess && $oRev->isActive() ) {
			$aFlagInfo[ 'user-can-review' ] = false;

			return false;
		}

		return true;
	}

	/**
	 * Edits a review process
	 * @param Title $oTitle
	 * @param stdClass $oParams
	 * @param User $oUser
	 * @param BsReviewProcess $oReviewProcess
	 * @return Status
	 */
	public static function doEditReview( Title $oTitle, stdClass $oParams, User $oUser = null, BsReviewProcess $oReviewProcess = null ) {
		$oStatus = Status::newGood();

		if( !$oUser ) {
			$oUser = RequestContext::getMain()->getUser();
		}

		if( !$oTitle || $oTitle->isSpecialPage() || !$oTitle->exists() ) {
			return $oStatus->merge(
				Status::newFatal( 'bs-review-save-noid' )
			);
		}

		if( !$oReviewProcess ) {
			$oReviewProcess = BsReviewProcess::newFromPid(
				(int) $oTitle->getArticleID()
			);
		}

		$bIsEdit = false;
		if( $oReviewProcess && $oReviewProcess->hasSteps() ) {
			$bIsEdit = true;
		}

		$oStatus = BsReviewProcess::newFromObject( $oParams );

		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		$oNewReviewProcess = $oStatus->getValue();
		$oNewReviewProcess->setOwner( $oUser->getID() );

		if( isset( $oParams->save_tmpl ) ) {
			$paramTmplChoice = !empty( $oParams->tmpl_choice )
				? $oParams->tmpl_choice
				: -1
			;
			$paramTmplName = !empty( $oParams->tmpl_name )
				? $oParams->tmpl_name
				: ''
			;
			$oNewReviewProcess->asTemplate( $paramTmplChoice, $paramTmplName );
		}

		$bTemplateFailed =
			empty( $oNewReviewProcess->steps )
			|| !is_array( $oNewReviewProcess->steps )
		;
		if( $bTemplateFailed ) {
			return $oStatus->merge(
				Status::newFatal( 'bs-review-save-nosteps' )
			);
		}

		if( $bIsEdit ) {
			BsReviewProcess::removeReviewSteps(
				(int)$oReviewProcess->getPid()
			);
		}
		//TODO: Here is a good place for a hook!
		if( !$oNewReviewProcess->store( $bIsEdit ) ) {
			return $oStatus->merge(
				Status::newFatal( 'bs-review-save-error' )
			);
		}

		$oTitle->invalidateCache();
		//TODO: Deprecated since 1.27
		$oWatchlist = WatchedItem::fromUserTitle( $oUser, $oTitle );
		if ( !$oWatchlist->isWatched() ) {
			$oWatchlist->addWatch();
		}

		$aParams = array(
			'action' => $bIsEdit ? 'modify' : 'create',
			'target' => $oTitle,
			'comment' => '',
			'params' => null,
			'doer' => $oUser
		);
		$oReview = BsExtensionManager::getExtension( 'Review' );
		$oReview->getLogger()->addEntry(
			$aParams[ 'action' ],
			$aParams[ 'target' ],
			$aParams[ 'comment' ],
			$aParams[ 'params' ],
			$aParams[ 'doer' ]
		);

		$oReview->emailNotifyNextUsers( $oNewReviewProcess );

		return Status::newGood( $oNewReviewProcess );
	}

	/**
	 * Deletes a review process
	 * @param Title $oTitle
	 * @param User $oUser
	 * @param BsReviewProcess $oReviewProcess
	 * @return Status
	 */
	public static function doDeleteReview( Title $oTitle, User $oUser = null, BsReviewProcess $oReviewProcess = null ) {
		$oStatus = Status::newGood();
		if( !$oUser ) {
			$oUser = RequestContext::getMain()->getUser();
		}

		if( !$oTitle || $oTitle->isSpecialPage() || !$oTitle->exists() ) {
			return $oStatus->merge(
				Status::newFatal( 'bs-review-save-noid' )
			);
		}

		if( !$oReviewProcess ) {
			$oReviewProcess = BsReviewProcess::newFromPid(
				(int) $oTitle->getArticleID()
			);
			if( !$oReviewProcess ) {
				return $oStatus->merge(
					Status::newFatal( 'bs-review-save-noid' )
				);
			}
		}

		if( $oReviewProcess && BsConfig::get( 'MW::Review::CheckOwner' ) ) {
			$bCeckUser =
				$oReviewProcess->owner != $oUser->getID()
				//sysops always can edit
				&& !in_array( 'sysop', $oUser->getGroups() )
			;
			if( $bCeckUser ) {
				return $oStatus->merge(
					Status::newFatal( 'bs-review-save-norights' )
				);
			}
		}

		BsReviewProcess::removeReviews( (int) $oTitle->getArticleID() );
		$oTitle->invalidateCache();

		//TODO: Deprecated since 1.27
		$oWatchlist = WatchedItem::fromUserTitle( $oUser, $oTitle );
		if ( $oWatchlist->isWatched() ) {
			$oWatchlist->removeWatch();
		}
		$aParams = array(
			'action' => 'delete',
			'target' => $oTitle,
			'comment' => '',
			'params' => null,
			'doer' => $oUser
		);
		$oReview = BsExtensionManager::getExtension( 'Review' );
		$oReview->getLogger()->addEntry(
			$aParams[ 'action' ],
			$aParams[ 'target' ],
			$aParams[ 'comment' ],
			$aParams[ 'params' ],
			$aParams[ 'doer' ]
		);
		//TODO: Notify user?
		return $oStatus;
	}

	public static function userCanEdit( BsReviewProcess $oReviewProcess, User $oUser = null) {
		if( !BsConfig::get( 'MW::Review::CheckOwner' ) ) {
			return true;
		}
		if( !$oUser ) {
			$oUser = RequestContext::getMain()->getUser();
		}
		return
			(int) $oReviewProcess->owner == (int) $oUser->getID()
			//sysops always can edit
			|| in_array( 'sysop', $oUser->getGroups() )
		;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 *
	 * @param StateBar $oStateBar
	 * @param User     $oUser
	 * @param Title    $oTitle
	 * @param array    $aTopViews
	 *
	 * @return boolean Always true to keep hook running
	 */
	public static function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		$sIcon = 'bs-infobar-workflow-open';
		$oRev = BsReviewProcess::newFromPid( $oTitle->getArticleID() );
		if ( $oRev ) {
			if ( $res = $oRev->isFinished() ) {
				if ( $oRev->isSequential() ) {
					switch ( $res ) {
						case 'date' :
							$sIcon = "bs-infobar-workflow-dismissed";
							break;
						case 'status' :
							$sIcon = "bs-infobar-workflow-ok";
							break;
						case 'denied' :
							$sIcon = "bs-infobar-workflow-dismissed";
							break;
					}
				} else {
					$res = $oRev->currentStatus();
					$res = explode( ';', $res );

					if ( $res[ 2 ] > $res[ 1 ] ) {
						$sIcon = "bs-infobar-workflow-ok";
					} else if ( $res[ 2 ] < $res[ 1 ] ) {
						$sIcon = "bs-infobar-workflow-dismissed";
					} else {
						$sIcon = "bs-infobar-workflow-open";
					}
				}
			}

			$aNextUsers = $oRev->getNextUsers();
			foreach ( $aNextUsers as $aNextUser ) {
				if ( (int)$aNextUser[ 'id' ] === $oUser->getId() ) {
					$sIcon = "bs-infobar-workflow-active";
					break;
				}
			}

			$sIcon .= ".png";

			$aTopViews[ 'statebartopreview' ] = self::makeStateBarTopReview( $sIcon, $oTitle );
		}

		return true;
	}

	/**
	 * Adds information to an data object that is needed to properly initialise
	 * 'BS.Review.ReviewPanel'
	 *
	 * @param BsReviewProcess $oReview
	 *
	 * @return \stdClass
	 */
	protected static function makeJSDataObject( $oReview, Title $oTitle ) {
		//Defaults
		$oData = new stdClass();

		if ( !is_null( $oTitle ) ) {
			$oData->page_id = $oTitle->getArticleID();
		}

		if ( $oReview ) {
			$oData->startdate = strtotime( $oReview->startdate );
			$oData->enddate = strtotime( $oReview->enddate );
			$oData->owner_user_id = $oReview->getOwner();
			$oData->owner_user_name = User::newFromId( $oReview->getOwner() )->getName();
			$oData->page_id = $oReview->getPid();
			$oData->page_prefixed_text = Title::newFromID( $oReview->getPid() )->getPrefixedText();
			$oData->editable = $oReview->isEditable();
			$oData->sequential = $oReview->isSequential();
			$oData->abortable = $oReview->isAbortWhenDenied();
			$oData->steps = array();

			foreach ( $oReview->steps as $oStep ) {
				if ( $oStep instanceof BsReviewProcessStep == false )
					continue;

				$oUser = User::newFromId( $oStep->user );

				$aStep = array(
					'user_id' => $oStep->user,
					'user_name' => $oUser->getName(),
					'user_display_name' => BsUserHelper::getUserDisplayName(
						$oUser
					),
					'comment' => $oStep->comment,
					'status' => $oStep->status,
					'sort_id' => $oStep->sort_id,
				);

				$oData->steps[] = $aStep;
			}
		}

		wfRunHooks( 'BsReviewAfterMakeJSDataObject', array( $oReview, &$oData ) );

		return $oData;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 *
	 * @param StateBar $oStateBar
	 * @param array    $aBodyViews
	 *
	 * @return boolean Always true to keep hook running
	 */
	public static function onStateBarBeforeBodyViewAdd( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
		$text = '';
		$oRev = BsReviewProcess::newFromPid( $oTitle->getArticleID() );
		$pages = BsReviewProcess::listReviews( $oUser->getId() );

		if ( !$oRev ) {
			return true;
		}

		$oReviewView = new ViewStateBarBodyElementReview();
		$oReviewView->setReview( $oRev );
		$oReviewView->addButton(
			'bs-review-dismiss', 'bs-icon-decline', wfMessage( 'bs-review-i-dismiss' )->plain(), wfMessage( 'bs-review-i-dismiss' )->plain()
		);
		$oReviewView->addButton(
			'bs-review-ok', 'bs-icon-accept', wfMessage( 'bs-review-i-agree' )->plain(), wfMessage( 'bs-review-i-agree' )->plain()
		);


		if ( $res = $oRev->isFinished() ) {
			//$text = wfMessage( 'bs-review-review-finished' )->plain();
			$oReviewView->setStatusText( wfMessage( 'bs-review-review-finished' )->plain() );
			if ( $oRev->isSequential() ) {
				switch ( $res ) {
					case 'date' :
						$text .= wfMessage( 'bs-review-date' )->plain();
						break;
					case 'status' :
						$text .= wfMessage( 'bs-review-agreed' )->plain();
						break;
					case 'denied' :
						$text .= wfMessage( 'bs-review-denied-disagreed' )->plain();
						break;
				}
			} else {
				$res = $oRev->currentStatus();
				$res = explode( ';', $res );
				if ( $res[ 2 ] ) {
					$text .= "<br />" . wfMessage( 'bs-review-accepted', $res[ 2 ] )->plain();
				}
				if ( $res[ 1 ] ) {
					$text .= "<br />" . wfMessage( 'bs-review-rejected', $res[ 1 ] )->plain();
				}
				if ( $res[ 0 ] ) {
					$text .= "<br />" . wfMessage( 'bs-review-abstain', $res[ 0 ] )->plain();
				}
			}
			$oReviewView->setStatusReasonText( $text );
		} else {
			$text = wfMessage( 'bs-review-reviewed-till', $oRev->getStartdate(), $oRev->getEnddate() )->plain();

			$user = User::newFromId( $oRev->owner );
			$sName = BsUserHelper::getUserDisplayName( $user );
			$text .= '<br />' . wfMessage( 'bs-review-reviewed-till-extra', $user->getName(), $sName )->text();

			$oReviewView->setStatusText( $text );
		}

		// Flagged Revision: Only show the "not accepted" icon on the template page an not on the released page, which is accepted.
		$obj = false;
		$bResult = false;
		wfRunHooks( 'checkPageIsReviewable', array( $oTitle, &$bResult ) );
		if ( $bResult ) {
			$obj = FlaggedRevision::newFromStable( $oTitle );
		}

		$aComments = array();
		foreach ( $oRev->steps as $_step ) {
			if ( !empty( $_step->comment ) && $_step->status != -1 ) {
				$aComments[] = $_step->comment;
			}
		}
		$oReviewView->setPreceedingCommentsList( $aComments );

		if ( empty( $pages ) || !in_array( $oTitle->getArticleID(), $pages ) ) {
			$aBodyViews[ 'statebarbodyreview' ] = $oReviewView;

			return true;
		}

		$step = $oRev->currentStep( $oUser->getId() );
		if ( !is_object( $step ) ) {
			$aBodyViews[ 'statebarbodyreview' ] = $oReviewView;

			return true;
		}

		$oReviewView->setVotable( true );
		$sUserName = BsUserHelper::getUserDisplayName( $oUser );
		$oReviewView->setComment( "<em>{$sUserName}:</em> {$step->comment}" );

		wfRunHooks( 'BsReview::checkStatus::afterMessage', array( $step, $oReviewView ) );
		if ( $oTitle->userCan( "workflowview", $oUser ) ) {
			$aBodyViews[ 'statebarbodyreview' ] = $oReviewView;
		}

		return true;
	}

	/**
	 * Renders status output to StatusBar top secion.
	 *
	 * @param string $sIcon Filename of the icon to be displayed. Relative to extension image dir.
	 *
	 * @return ViewStateBarTopElement View that is part of StateBar.
	 */
	public static function makeStateBarTopReview( $sIcon, Title $oTitle ) {
		$oReviewView = new ViewStateBarTopElement();

		if ( is_object( $oTitle ) ) {
			global $wgScriptPath;
			$oReviewView->setKey( 'Review' );
			$oReviewView->setIconSrc( $wgScriptPath . '/extensions/BlueSpiceExtensions/Review/resources/images/' . $sIcon );
			$oReviewView->setIconAlt( wfMessage( 'bs-review-review' )->plain() );
			$oReviewView->setText( wfMessage( 'bs-review-review' )->plain() );
			$oReviewView->setIconTogglesBody( true );
		}

		return $oReviewView;
	}

	/**
	 * Adds Special:Review link to wiki wide widget
	 * @param UserSidebar $oUserSidebar
	 * @param User $oUser
	 * @param array $aLinks
	 * @param string $sWidgetTitle
	 * @return boolean
	 */
	public static function onBSUserSidebarGlobalActionsWidgetGlobalActions( UserSidebar $oUserSidebar, User $oUser, &$aLinks, &$sWidgetTitle ) {
		$oSpecialReview = SpecialPageFactory::getPage( 'Review' );
		if( !$oSpecialReview ) {
			return true;
		}
		$aLinks[] = array(
			'target' => $oSpecialReview->getPageTitle(),
			'text' => $oSpecialReview->getDescription(),
			'attr' => array(),
			'position' => 600,
			'permissions' => array(
				'read',
				'workflowview'
			),
		);
		return true;
	}

	/**
	 * Adds a info to bs_personal_info
	 *
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 *
	 * @return boolean Always true to keep hook running
	 */
	public static function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		$oUser = $sktemplate->getUser();
		if ( $oUser->isAllowed( 'workflowview' ) === false ) {
			return true;
		}

		$iCountReviews = count( BsReviewProcess::listReviews( $oUser->getId() ) );
		$iCountFinishedReviews = BsReviewProcess::userHasWaitingReviews( $oUser );

		if ( $iCountReviews <= 0 && !$iCountFinishedReviews ) {
			return true;
		}

		$tpl->data[ 'bs_personal_info' ][ 20 ] = array(
			'id' => 'pi-review',
			'href' => SpecialPage::getTitleFor( 'Review', $oUser->getName() )->getLocalURL(),
			'text' => $iCountReviews . "|" . $iCountFinishedReviews,
			'class' => 'icon-eye',
			'active' => $iCountReviews > 0 ? true : false
		);

		return true;
	}

	/**
	 * Handles Review votes.
	 * @param Title $oTitle
	 * @param stdClass $oParams - Known param keys are vote and comment
	 * @param User $oUser
	 * @return Status - Use Status->getValue() to get the BsRevieProcess on a
	 * good Status
	 */
	public static function doVote( Title $oTitle, stdClass $oParams = null, User $oUser = null ) {
		$iArticleId = (int) $oTitle->getArticleID();
		$sVote = empty( $oParams->vote )
			? ''
			: (string)$oParams->vote
		;
		$sComment = empty( $oParams->comment )
			? ''
			: (string)$oParams->comment
		;
		if ( empty( $iArticleId ) || empty( $sVote ) || $iArticleId === 0 ) {
			return Status::newFatal( 'bs-review-review-error' );
		}

		if( !$oUser instanceof User ) {
			$oUser = RequestContext::getMain()->getUser();
		}
		//tbd: make bs-review-review-error more explicit
		if ( !$oTitle->exists() || !$oUser ) {
			return Status::newFatal( 'bs-review-review-error' );
		}
		if ( !$oTitle->userCan( "workflowview", $oUser ) ) {
			return Status::newFatal(
				'bs-review-error-insufficient-permissions',
				'workflowview'
			)->text();
		}

		$oReview = BsExtensionManager::getExtension( 'Review' );
		$oNext = null;

		$dbw = wfGetDB( DB_MASTER );
		// Get ID of the apropriate step
		$tables = array();
		$tables[] = 'bs_review';
		$tables[] = 'bs_review_steps';

		$tbl_rev = $dbw->tableName( 'bs_review' );
		$tbl_step = $dbw->tableName( 'bs_review_steps' );

		$conds = array();
		$conds[] = $tbl_step . '.revs_review_id = ' . $tbl_rev . '.rev_id';  // join tables
		$conds[] = $tbl_rev . '.rev_pid=' . $iArticleId; // reviews only for current article
		$conds[] = $tbl_step . '.revs_status=-1';  // prevent user from confirming twice
		$conds[] = $tbl_step . ".revs_user_id='{$oUser->getId()}'"; // make sure we select a dataset for the current user

		$options = array( 'ORDER BY' => 'revs_sort_id ASC' );
		$join_conds = array();
		$fields = $tbl_step . '.*';

		$oStatus = Status::newGood();
		wfRunHooks( 'BsReview::buildDbQuery', array(
			'getVoteResponse',
			&$tables,
			&$fields,
			&$conds,
			&$options,
			&$join_conds,
			$oTitle,
			$oParams,
			$oUser,
			&$oStatus
		));
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}

		$res = $dbw->select( $tables, $fields, $conds, __METHOD__, $options, $join_conds );
		if ( !$row = $dbw->fetchRow( $res ) ) {
			return $oStatus->merge(
				Status::newFatal( 'bs-review-review-error' )
			);
		}

		// Unexpectedly, no review could be found.
		if ( $dbw->numRows( $res ) == 0 ) {
			return $oStatus->merge(
				Status::newFatal( 'bs-review-review-secondtime' )
			);
		} elseif ( $dbw->numRows( $res ) > 1 ) {
			$oNext = $dbw->fetchObject( $res );
		}

		$dbw->freeResult( $res );

		$oParams->stepid = $step_id = $row[ 'revs_id' ];
		$initial_comment = $row[ 'revs_comment' ];

		// update data
		$data = array();
		switch ( $sVote ) {
			case "yes" :
				$data[ 'revs_status' ] = 1;
				$oReview->getLogger()->addEntry(
					'approve',
					$oTitle,
					'',
					null,
					$oUser
				);
				break;
			case "no" :
				$data[ 'revs_status' ] = 0;
				$oReview->getLogger()->addEntry(
					'deny',
					$oTitle,
					'',
					null,
					$oUser
				);
				break;
			default :
				$data[ 'revs_status' ] = -1;
				break;
		}

		// Identify owner
		$oReviewProcess = BsReviewProcess::newFromPid( $iArticleId );
		$oOwner = User::newFromID( $oReviewProcess->getOwner() );

		$sUserName = BsUserHelper::getUserDisplayName( $oUser );
		$sOwnerName = BsUserHelper::getUserDisplayName( $oOwner );
		if ( !empty( $initial_comment ) ) {
			$initial_comment = "<em>{$sOwnerName}: </em>{$initial_comment}";
		}
		if ( !empty( $sComment ) ) {
			$data[ 'revs_comment' ] = "<em>{$sUserName}: </em>{$sComment}";

			//Prepend original comment
			if ( !empty( $initial_comment ) ) {
				$data[ 'revs_comment' ] = $initial_comment . " &rArr; " . $data[ 'revs_comment' ];
			}
		} else {
			$data[ 'revs_comment' ] = $initial_comment;
		}

		wfRunHooks( 'BsReview::dataBeforeSafe', array(
			'getVoteResponse',
			&$data,
			$oTitle,
			$oParams,
			$oUser,
			&$oStatus,
			$oReviewProcess
		));

		if( !$oStatus->isOK() ) {
			return $oStatus;
		}

		$dbw->update( 'bs_review_steps', $data, array( 'revs_id' => $step_id ) );

		$oTitle->invalidateCache();
		//RELOAD ReviewProcess!
		$oReviewProcess = BsReviewProcess::newFromPid(
			(int) $oTitle->getArticleID()
		);

		if ( $sVote == 'no' ) {
			if ( $oReviewProcess->isSequential() ) {
				$oReviewProcess->reset( $sComment );
				BSNotifications::notify(
					'bs-review-deny-and-restart',
					$oUser,
					$oTitle,
					array(
						'owner' => $oOwner,
						'comment' => $sComment
					)
				);
			} else {
				BSNotifications::notify(
					'bs-review-deny',
					$oUser,
					$oTitle,
					array(
						'owner' => $oOwner,
						'comment' => $sComment
					)
				);
			}
		} else {
			if ( $oReviewProcess->isSequential() && !$oReviewProcess->isFinished() ) {
				$oReview->emailNotifyNextUsers( $oReviewProcess );
			}
			BSNotifications::notify(
				'bs-review-accept',
				$oUser,
				$oTitle,
				array(
					'owner' => $oOwner,
					'comment' => $sComment
				)
			);
		}

		$oStatus = Status::newGood( $oReviewProcess );
		Hooks::run( 'BSReviewVoteComplete', array(
			$oReview,
			$step_id,
			$oReviewProcess,
			$oTitle,
			$oParams,
			$oUser,
			$oStatus,
		));

		if( !$oStatus->isOK() ) {
			return $oStatus;
		}
		if ( $oReviewProcess->isFinished() === false ) {
			return $oStatus;
		}

		// Let flagged revision know that it's all goooooood (or not approved)
		$bResult = true;
		wfRunHooks( 'checkPageIsReviewable', array( $oTitle, &$bResult ) );
		//all autorevie related stuff should be moved to FlaggedRevsConnector
		//send autoflag mail only when it is acitvated
		$bAutoReview = class_exists( 'FlaggedRevsConnector' )
			&& BsConfig::get('MW::FlaggedRevsConnector::autoReview') === true
		;
		if ( $bResult && $oReviewProcess->isFinished() == 'status' && $bAutoReview ) {
			BSNotifications::notify(
				'bs-review-finish-and-autoflag',
				$oUser,
				$oTitle,
				array(
					'owner' => $oOwner,
					'comment' => $sComment
				)
			);
		} else {
			BSNotifications::notify(
				'bs-review-finish',
				$oUser,
				$oTitle,
				array(
					'owner' => $oOwner,
					'comment' => $sComment
				)
			);
		}

		// Unfortunately, there is no way of verifying the result :(
		return Status::newGood( $oReviewProcess );
	}

	/**
	 * Remove review when an article is deleted.
	 *
	 * @param Article $article Article object of deleted article.
	 * @param User    $user    Currently logged in user.
	 * @param string  $reason  Reason for page deletion.
	 * @param int     $id      ID of the page deleted.
	 *
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public static function onArticleDeleteComplete( &$article, &$user, $reason, $id ) {
		BsReviewProcess::removeReviews( $id );

		return true;
	}

	/**
	 * Adds CSS to Page
	 *
	 * @param OutputPage $out
	 * @param Skin       $skin
	 *
	 * @return boolean
	 */
	public static function onBeforePageDisplay( &$out, &$skin ) {
		$out->addModuleStyles( 'ext.bluespice.review.styles' );

		if ( $out->getTitle()->getNamespace() <= -1 )
			return true;
		if ( $out->getTitle()->exists() == false )
			return true;
		//if( $out->getTitle()->userCan('workflowread') == false ) return true;

		$out->addModules( 'ext.bluespice.review' );

		$bUserCanEdit = $out->getTitle()->userCan( 'workflowedit' );
		$out->addJsConfigVars( 'bsReviewUserCanEdit', $bUserCanEdit );

		$oRev = BsReviewProcess::newFromPid(
			$out->getTitle()->getArticleID()
		);
		$out->addJsConfigVars(
			'bsReview',
			self::makeJSDataObject( $oRev, $out->getTitle() )
	);

		return true;
	}

	/**
	 * Send email notification to next user(s) on review list.
	 *
	 * @param BsReviewProcess $oReviewProcess Review process users should be notified for.
	 *
	 * @return Status
	 */
	public function emailNotifyNextUsers( $oReviewProcess ) {

		$oAgent = User::newFromId( $oReviewProcess->getOwner() );
		$oTitle = Title::newFromID( $oReviewProcess->getPid() );
		$aNextUsers = $oReviewProcess->getNextUsers();

		BSNotifications::notify( 'bs-review-assign', $oAgent, $oTitle, array( 'next-users' => $aNextUsers ) );

	}

	/**
	 * The preferences plugin callback
	 *
	 * @param string   $sAdapterName
	 * @param BsConfig $oVariable
	 *
	 * @return array MediaWiki preferences options array
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array();
		wfRunHooks( 'BSReviewRunPreferencePlugin', array( &$sAdapterName, &$oVariable, &$aPrefs ) );

		if ( $oVariable->getName() == "Permissions" ) {
                       $aPermissions = array_diff(
			  User::getAllRights(), WikiAdmin::get( 'ExcludeRights' )
			);
			return array(
				'type' => 'multiselectex',
				'options' => array_combine( $aPermissions, $aPermissions ),
			);
		}

		return $aPrefs;
	}

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		$GLOBALS["bssDefinitions"]["_REVIEW"] = array(
			"id" => "___REVIEW",
			"type" => 2,
			"show" => false,
			"msgkey" => "prefs-review",
			"alias" => "prefs-review",
			"label" => "Review",
			"mapping" => "Review::smwDataMapping"
		);

		if( !isset( $GLOBALS['bsgDefaultReviewAdditionalPermissions'] ) ) {
			$GLOBALS['bsgDefaultReviewAdditionalPermissions'] = array(
				'edit',
			);
		}
	}

	/**
	 * Callback for BlueSpiceSMWConnector that adds a semantic special property
	 * @param SMW\SemanticData $oSemanticData
	 * @param WikiPage $oWikiPage
	 * @param SMW\DIProperty $oProperty
	 */
	public static function smwDataMapping( SMW\SemanticData $oSemanticData, WikiPage $oWikiPage, SMW\DIProperty $oProperty ) {
		$oReviewProcess = BsReviewProcess::newFromPid( $oWikiPage->getId() );
		if( $oReviewProcess instanceof BsReviewProcess ) {
			//get review status: 'in review', 'denied', 'approved', 'ongoing', 'cancelled'
			$sStatus = $oReviewProcess->getStatus( time() );
			$oSemanticData->addPropertyObjectValue(
				$oProperty, new SMWDIBlob( $sStatus )
			);
		}
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public static function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['review:pages'] = array(
			'class' => 'Database',
			'config' => array(
				'identifier' => 'bs-usagetracker-review',
				'descriptionKey' => 'bs-usagetracker-review',
				'table' => 'bs_review',
				'uniqueColumns' => array( 'rev_pid' )
			)
		);
		return true;
	}


	/**
	 * Add edit right permission for current logged in user if review process
	 * add edit permission
	 * @param User $user
	 * @param type $rights
	 * @return boolean
	 */
	public static function onUserGetRights( User $user, &$aRights ) {
		if( !RequestContext::getMain()->hasTitle() ){
			return true;
		}
		$oTitle = RequestContext::getMain()->getTitle();
		//permission handler
		$oReviewProcess = BsReviewProcess::newFromPid( $oTitle->getArticleID() );
		if( $oReviewProcess == null ){
			return true;
		}

		$aUser = $oReviewProcess->getNextUsers();
		foreach ( $aUser as $mUser ) {
			if ( $mUser[ "id" ] == $user->getId() && $oReviewProcess->isEditable() ) {
				$bsConfigReview = BsConfig::get( "MW::Review::Permissions" );
				$aRights = array_merge( $aRights, $bsConfigReview );
			}
		}

		return true;
	}

}
