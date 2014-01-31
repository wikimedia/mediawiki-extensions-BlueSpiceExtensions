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
 * For further information visit http://www.blue-spice.org
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * - Added/changed e-mail notifications
 * v1.1.1
 * - Hooking into FlaggedRevsConnector to avoid display of review form if workflow is active
 * v1.1.0
 * - Added indexes to database tables to improve performance
 * - Fixed some I18N issues
 * - Fixed ExtJS bug
 * - Replaced logging mechanism
 * v1.0.0
 * - Raised to stable
 * - Code Review
 * v0.1
 * - initial commit
 */

// Last Code Review RBV (30.06.2011)

/**
 * Main class for Review extension
 * @package BlueSpice_Extensions
 * @subpackage Review
 */
class Review extends BsExtensionMW {

	/**
	 * Stores the current logger that writes to MW log
	 * @var LogPage Logger object that writes to MW log
	 */
	protected $oLogger;

	/**
	 * Constructor of Review class
	 */
	public function __construct() {

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME => 'Review',
			EXTINFO::DESCRIPTION => 'Adds workflow functionality to pages.',
			EXTINFO::AUTHOR => 'Markus Glaser',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array(
				'bluespice' => '2.22.0',
				'StateBar' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::Review';
	}

	/**
	 * Initialization of Review extension
	 */
	protected function initExt() {
		// Register style in constructor in order to have it loaded on special pages
		BsConfig::registerVar('MW::Review::CheckOwner', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-review-pref-CheckOwner', 'toggle');
		BsConfig::registerVar('MW::Review::ShowNameInTooltip', true, BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_BOOL, 'bs-review-pref-ShowNameInTooltip', 'toggle');
		BsConfig::registerVar('MW::Review::EmailNotifyOwner', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-review-pref-EmailNotifyOwner', 'toggle');
		BsConfig::registerVar('MW::Review::EmailNotifyReviewer', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-review-pref-EmailNotifyReviewer', 'toggle');
		BsConfig::registerVar('MW::Review::ShowAssessor', true, BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_BOOL, 'bs-review-pref-ShowAssessor', 'toggle');

		$this->setHook('SkinTemplateOutputPageBeforeExec', 'checkReviewStatus');
		$this->setHook('SkinTemplateNavigation::Universal', 'onSkinTemplateNavigationUniversal');
		$this->setHook('SkinTemplateTabs', 'addReviewTab'); //Unused: This feature was removed completely in version 1.18.0.
		$this->setHook('userCan', 'checkReviewPermissions');
		$this->setHook('BSBlueSpiceSkinUserBarBeforeLogout', 'makeUserBar');
		$this->setHook('ArticleDeleteComplete');
		$this->setHook('BSFlaggedRevsConnectorCollectFlagInfo');
		$this->setHook('BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars');
		$this->setHook('BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars');
		$this->setHook('BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd');
		$this->setHook('BSStateBarBeforeBodyViewAdd', 'onStateBarBeforeBodyViewAdd');
		$this->setHook('BeforePageDisplay');

		$this->mCore->registerPermission('workflowview', array('user'));
		$this->mCore->registerPermission('workflowedit');
		$this->mCore->registerPermission('workflowlist');

		global $wgLogActionsHandlers, $wgLogTypes, $wgFilterLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[] = 'bs-review';
		$wgFilterLogTypes['bs-review'] = true;
		$wgLogNames['bs-review'] = 'bs-review-logpage';
		$wgLogHeaders['bs-review'] = 'bs-review-logpagetext';

		$wgLogActionsHandlers['bs-review/create'] = array($this, 'logCreate');
		$wgLogActionsHandlers['bs-review/modify'] = array($this, 'logModify');
		$wgLogActionsHandlers['bs-review/delete'] = array($this, 'logDelete');
		$wgLogActionsHandlers['bs-review/approve'] = array($this, 'logApprove');
		$wgLogActionsHandlers['bs-review/deny'] = array($this, 'logDeny');
		$wgLogActionsHandlers['bs-review/finish'] = array($this, 'logFinish');

		$this->oLogger = new LogPage('bs-review', false);
	}

	/**
	 * Sets up required database tables
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtModifiedFields, $wgExtNewIndexes, $wgExtNewFields;
		$sDir = __DIR__ . DS;

		if ($wgDBtype == 'mysql') {
			$updater->addExtensionTable(
				'bs_review',
				$sDir . 'db/mysql/review.sql'
			);

			$dbr = wfGetDB(DB_SLAVE);

			if ($dbr->tableExists('bs_review')) {
				if (!$dbr->fieldExists('bs_review', 'rev_sequential')) {
					$updater->addExtensionField(
						'bs_review',
						'rev_sequential',
						$sDir . 'db/mysql/review.patch.rev_sequential.sql'
					);
				}
				if (!$dbr->fieldExists('bs_review', 'rev_abortable')) {
					$updater->addExtensionField(
						'bs_review',
						'rev_abortable',
						$sDir . 'db/mysql/review.patch.rev_abortable.sql'
					);
				}
			}
			if ($dbr->tableExists('bs_review_steps') && !$dbr->fieldExists('bs_review_steps', 'delegate_to')) {
				$updater->addExtensionField(
					'bs_review_steps',
					'revs_delegate_to',
					$sDir . 'db/mysql/review.patch.revs_delegate_to.sql'
				);
			}
			if ($dbr->tableExists('bs_review')) {
				if (!$dbr->fieldExists('bs_review_templates', 'revt_editable')) {
					$updater->addExtensionField(
						'bs_review_templates',
						'revt_editable',
						$sDir . 'db/mysql/review_templates.patch.revt_editable.sql'
					);
				}
				if (!$dbr->fieldExists('bs_review_templates', 'revt_sequential')) {
					$updater->addExtensionField(
						'bs_review_templates',
						'revt_sequential',
						$sDir . 'db/mysql/review_templates.patch.revt_sequential.sql'
					);
				}
				if (!$dbr->fieldExists('bs_review_templates', 'revt_abortable')) {
					$updater->addExtensionField(
						'bs_review_templates',
						'revt_abortable',
						$sDir . 'db/mysql/review_templates.patch.revt_abortable.sql'
					);
				}
			}

			$updater->modifyExtensionField('bs_review', 'id', $sDir . 'db/mysql/review.patch.id.sql');
			$updater->modifyExtensionField('bs_review', 'pid', $sDir . 'db/mysql/review.patch.pid.sql');
			$updater->modifyExtensionField('bs_review', 'editable', $sDir . 'db/mysql/review.patch.editable.sql');
			$updater->modifyExtensionField('bs_review', 'mode', $sDir . 'db/mysql/review.patch.mode.sql');
			$updater->modifyExtensionField('bs_review', 'rev_mode', $sDir . 'db/mysql/review.patch.rev_mode.sql');
			$updater->modifyExtensionField('bs_review', 'startdate', $sDir . 'db/mysql/review.patch.startdate.sql');
			$updater->modifyExtensionField('bs_review', 'enddate', $sDir . 'db/mysql/review.patch.enddate.sql');
			$updater->modifyExtensionField('bs_review', 'owner', $sDir . 'db/mysql/review.patch.owner.sql');

			$updater->modifyExtensionField('bs_review_steps', 'id', $sDir . 'db/mysql/review_steps.patch.id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'review_id', $sDir . 'db/mysql/review_steps.patch.review_id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'user_id', $sDir . 'db/mysql/review_steps.patch.user_id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'status', $sDir . 'db/mysql/review_steps.patch.status.sql');
			$updater->modifyExtensionField('bs_review_steps', 'sort_id', $sDir . 'db/mysql/review_steps.patch.sort_id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'comment', $sDir . 'db/mysql/review_steps.patch.comment.sql');
			$updater->modifyExtensionField('bs_review_steps', 'delegate_to', $sDir . 'db/mysql/review_steps.patch.delegate_to.sql');
			$updater->modifyExtensionField('bs_review_steps', 'timestamp', $sDir . 'db/mysql/review_steps.patch.timestamp.sql');

			$updater->modifyExtensionField('bs_review_steps', 'id', $sDir . 'db/mysql/review_steps.patch.id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'review_id', $sDir . 'db/mysql/review_steps.patch.review_id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'user_id', $sDir . 'db/mysql/review_steps.patch.user_id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'status', $sDir . 'db/mysql/review_steps.patch.status.sql');
			$updater->modifyExtensionField('bs_review_steps', 'sort_id', $sDir . 'db/mysql/review_steps.patch.sort_id.sql');
			$updater->modifyExtensionField('bs_review_steps', 'comment', $sDir . 'db/mysql/review_steps.patch.comment.sql');
			$updater->modifyExtensionField('bs_review_steps', 'delegate_to', $sDir . 'db/mysql/review_steps.patch.delegate_to.sql');
			$updater->modifyExtensionField('bs_review_steps', 'timestamp', $sDir . 'db/mysql/review_steps.patch.timestamp.sql');

			$updater->modifyExtensionField('bs_review_templates', 'id', $sDir . 'db/mysql/review_templates.patch.id.sql');
			$updater->modifyExtensionField('bs_review_templates', 'name', $sDir . 'db/mysql/review_templates.patch.name.sql');
			$updater->modifyExtensionField('bs_review_templates', 'owner', $sDir . 'db/mysql/review_templates.patch.owner.sql');
			$updater->modifyExtensionField('bs_review_templates', 'user', $sDir . 'db/mysql/review_templates.patch.user.sql');
			$updater->modifyExtensionField('bs_review_templates', 'mode', $sDir . 'db/mysql/review_templates.patch.mode.sql');
			$updater->modifyExtensionField('bs_review_templates', 'revt_mode', $sDir . 'db/mysql/review_templates.patch.revt_mode.sql');
			$updater->modifyExtensionField('bs_review_templates', 'revt_mode', $sDir . 'db/mysql/review_templates.patch.revt_mode.sql');
			$updater->modifyExtensionField('bs_review_templates', 'public', $sDir . 'db/mysql/review_templates.patch.public.sql');
		} elseif ($wgDBtype == 'postgres') {
			$wgExtNewTables[] = array('bs_review', $sDir . 'db/postgres/review.pg.sql');

			$dbr = wfGetDB(DB_MASTER);
			if ($dbr->tableExists('bs_review_steps') && !$dbr->fieldExists('bs_review_steps', 'delegate_to') && !$dbr->fieldExists('bs_review_steps', 'revs_delegate_to')) {
				//PW(25.06.2012) wont work on mw 1.16.5
				//$wgExtNewFields[ ] = array( 'bs_review_steps', 'revs_delegate_to', $sDir . 'db/postgres/review.patch.delegate_to.pg.sql' );
				$dbr->query("ALTER TABLE " . $dbr->tableName("bs_review_steps") . " ADD revs_delegate_to SMALLINT NOT NULL DEFAULT '0' AFTER revs_comment;");
			}

			$aFieldsToPrefix = array(
				'bs_review' => array(
					'id' => 'rev_id',
					'pid' => 'rev_pid',
					'editable' => 'rev_editable',
					'mode' => 'rev_mode',
					'startdate' => 'rev_startdate',
					'enddate' => 'rev_enddate',
					'owner' => 'rev_owner'),
				'bs_review_steps' => array(
					'id' => 'revs_id',
					'review_id' => 'revs_review_id',
					'user_id' => 'revs_user_id',
					'status' => 'revs_status',
					'sort_id' => 'revs_sort_id',
					'comment' => 'revs_comment',
					'delegate_to' => 'revs_delegate_to',
					'timestamp' => 'revs_timestamp'),
				'bs_review_templates' => array(
					'id' => 'revt_id',
					'name' => 'revt_name',
					'owner' => 'revt_owner',
					'user' => 'revt_user',
					'mode' => 'revt_mode',
					'public' => 'revt_public')
			);

			foreach ($aFieldsToPrefix as $sTable => $aField) {
				echo $sTable;
				foreach ($aField as $sOld => $sNew) {
					if ($dbr->fieldExists($sTable, $sOld)) {
						if ($sOld == 'user')
							$sOld = '"' . $sOld . '"'; //PW: user is a keyword on modify
						$dbr->query('ALTER TABLE ' . $dbr->tableName($sTable) . ' RENAME ' . $sOld . ' TO ' . $sNew . ';');
					}
				}
			}
			if ($dbr->tableExists('bs_review_steps')) {
				$dbr->query('ALTER TABLE ONLY ' . $dbr->tableName('bs_review_steps') . ' ALTER COLUMN revs_timestamp set DEFAULT CURRENT_TIMESTAMP');
			}

			$wgExtNewIndexes[] = array('bs_review', 'rev_pid', $sDir . 'db/postgres/review.patch.rev_pid.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review', 'rev_startdate', $sDir . 'db/postgres/review.patch.rev_startdate.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review', 'rev_owner', $sDir . 'db/postgres/review.patch.rev_owner.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review_steps', 'revs_review_id', $sDir . 'db/postgres/review_steps.patch.revs_review_id.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review_steps', 'revs_user_id', $sDir . 'db/postgres/review_steps.patch.revs_user_id.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review_steps', 'revs_status', $sDir . 'db/postgres/review_steps.patch.revs_status.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review_templates', 'revt_owner', $sDir . 'db/postgres/review_templates.patch.revt_owner.index.pg.sql');
			$wgExtNewIndexes[] = array('bs_review_templates', 'revt_name', $sDir . 'db/postgres/review_templates.patch.revt_name.index.pg.sql');
		} elseif ($wgDBtype == 'oracle') {
			$wgExtNewTables[] = array('bs_review', $sDir . 'review.oci.sql');

			$dbr = wfGetDB(DB_SLAVE);
			if (!$dbr->fieldExists('bs_review_steps', 'delegate_to')) {
				$wgExtNewFields[] = array('bs_review_steps', 'revs_delegate_to', $sDir . 'db/oracle/review.patch.revs_delegate_to.oci.sql');
			} else {
				if (!$dbr->fieldExists('bs_review_steps', 'revs_delegate_to')) {
					$dbr->query('ALTER TABLE ' . $dbr->tableName('bs_review_steps') . ' RENAME COLUMN delegate_to TO revs_delegate_to');
					//wont work on linux for NO reason ...  
					//$wgExtModifiedFields[ ] = array( 'bs_review_steps', 'delegate_to', $sDir . 'db/oracle/review_steps.patch.delegate_to.sql' );
				}
			}

			$wgExtModifiedFields[] = array('bs_review_steps', 'revs_timestamp', $sDir . 'db/oracle/review_steps.patch.revs_timestamp.sql');

			$wgExtNewIndexes[] = array('bs_review', 'rev_pid', $sDir . 'db/oracle/review.patch.pid.index.oci.sql');
			$wgExtNewIndexes[] = array('bs_review', 'rev_startdate', $sDir . 'db/oracle/review.patch.startdate.index.oci.sql');
			$wgExtNewIndexes[] = array('bs_review', 'rev_owner', $sDir . 'db/oracle/review.patch.owner.index.oci.sql');
			$wgExtNewIndexes[] = array('bs_review_steps', 'revs_review_id', $sDir . 'db/oracle/review.patch.review_id.index.oci.sql');
			$wgExtNewIndexes[] = array('bs_review_steps', 'revs_user_id', $sDir . 'db/oracle/review.patch.user_id.index.oci.sql');
			$wgExtNewIndexes[] = array('bs_review_steps', 'revs_status', $sDir . 'db/oracle/review.patch.status.index.oci.sql');
			$wgExtNewIndexes[] = array('bs_review_templates', 'revt_name', $sDir . 'db/oracle/review.patch.name.index.oci.sql');
		}
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 * @param array $aSortTopVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortTopVars(&$aSortTopVars) {
		$aSortTopVars['statebartopreview'] = wfMessage('bs-review-statebartopreview')->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars(&$aSortBodyVars) {
		$aSortBodyVars['statebarbodyreview'] = wfMessage('bs-review-statebarbodyreview')->plain();
		$aSortBodyVars['statebarbodydoreview'] = wfMessage('bs-review-statebarbodydoreview')->plain();
		return true;
	}

	public function onSkinTemplateNavigationUniversal($oSkinTemplate, &$links) {
		if ($this->getTitle()->isContentPage() === false)
			return true;
		if ($this->getTitle()->exists() === false)
			return true;
		if ($this->getTitle()->userCan('workflowview') === false)
			return true;

		$links['actions']['review'] = array(
			'text' => wfMessage('bs-review-menu_entry')->plain(),
			'href' => '#',
			'class' => false
		);
		return true;
	}

	/**
	 * Adds review menu item to action tabs. Called by SkinTemplateTabs hook.
	 * @param Skin $skin MediaWiki skin object.
	 * @param array $content_actions Current array of tab actions. The function adds the action to this array.
	 * @return bool Allow other hooked methods to be executed. always true.
	 * @deprecated This feature was removed completely in version 1.18.0. (https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateTabs)
	 */
	public function addReviewTab($skin, &$content_actions) {
		if ($this->getTitle()->exists() === false)
			return true;
		if ($this->getTitle()->userCan('workflowview'))
			return true;

		$content_actions['review'] = array(
			"text" => wfMessage('bs-review-menu_entry')->plain(),
			"href" => '#',
			"class" => false
		);
		return true;
	}

	/**
	 * Wrapper method for the process of sending notification mails
	 * 
	 * @param string $sType a key which identifies the messages keys for the mail (accept, decline etc)
	 * @param User $oReceiver the user object of the user which should get the notification
	 * @param array $aParams additional parameters for the message
	 * @param string $sRelatedLink if set, the email contains this link at the end of the message
	 * @param User $oInvolvedUser if set, this users data can be used in the mails subject and text
	 */
	public static function sendNotification($sType, $oReceiver, $aParams = array(), $sRelatedLink = null, $oInvolvedUser = null) {
		static $sSitename;

		// save the basic message key for this mail
		$sBaseMessageKey = "bs-review-mail-" . strtolower($sType);

		// if the receiver deactivated mail notifications, we stop right here
		if (!BsConfig::getVarForUser('MW::Review::EmailNotifyReviewer', $oReceiver->getName())) {
			return;
		}

		// if the site name is not allready loaded, we load it here
		if (!$sSitename) {
			$sSiteName = BsConfig::get('MW::Sitename');
		}

		// get the required informations of the receiver
		$sReceiverMail = $oReceiver->getEmail();
		$sReceiverLang = $oReceiver->getOption('language');

		// if there is no email adress, we stop here
		if (empty($sReceiverMail)) {
			return;
		}

		array_unshift($aParams, $sSiteName);

		if (!is_null($oInvolvedUser)) {
			$aParams[] = BsCore::getUserDisplayName($oInvolvedUser);
		}

		$sSubject = wfMessage("{$sBaseMessageKey}-header", $aParams)->inLanguage($sReceiverLang)->plain();
		$sMessage = wfMessage("{$sBaseMessageKey}-body", $aParams)->inLanguage($sReceiverLang)->plain();

		if (!empty($sRelatedLink)) {
			$sMessage .= wfMessage('bs-review-mail-link-to-page', $sRelatedLink)->inLanguage($sReceiverLang)->plain();
		}

		BsMailer::getInstance('MW')->send($oReceiver, $sSubject, $sMessage);
	}

	public static function getData() {
		global $wgUser, $wgDBtype;

		$dbr = wfGetDB(DB_SLAVE);
		$tbl_rev = $dbr->tableName('bs_review');
		$tbl_step = $dbr->tableName('bs_review_steps');
		$tbl_page = $dbr->tableName('page');
		$tbl_user = $dbr->tableName('user');

		$bShowAssessor = BsConfig::get('MW::Review::ShowAssessor');

		$sql = 'SELECT  r.rev_id, r.rev_pid, p.page_title, p.page_namespace, u.user_name, u.user_real_name, u.user_id, r.rev_editable, r.rev_sequential, r.rev_abortable, rs.revs_status, u2.user_name AS owner_name, u2.user_real_name AS owner_real_name, ';
		switch ($wgDBtype) {
			case 'postgres' : {
					$sql.= "        EXTRACT(EPOCH FROM TO_TIMESTAMP(r.rev_enddate, 'YYYYMMDDHH24MISS')) AS endtimestamp, TO_CHAR(TO_DATE(r.rev_startdate, 'YYYYMMDDHH24MISS'), 'DD.MM.YYYY') AS startdate, ";
					$sql.= "        TO_CHAR(TO_DATE(r.rev_enddate, 'YYYYMMDDHH24MISS'), 'DD.MM.YYYY') AS enddate, TO_CHAR(rs.revs_timestamp::timestamp, 'DD.MM') AS stepdate ";
					break;
				}
			case 'oracle' : {
					$sql.= '        (ROUND(TO_DATE(r.rev_enddate, \'YYYYMMDDHH24MISS\') - TO_DATE(\'19700101\', \'YYYYMMDDHH24MISS\')) * 86400) endtimestamp, TO_CHAR(TO_DATE(r.rev_startdate, \'YYYYMMDDHH24MISS\'), \'DD.MM.YYYY\') startdate, ';
					$sql.= '        TO_CHAR(TO_DATE(r.rev_enddate, \'YYYYMMDDHH24MISS\'), \'DD.MM.YYYY\') enddate, TO_CHAR(rs.revs_timestamp, \'DD.MM\') stepdate ';
					break;
				}
			default: {
					$sql.= '        UNIX_TIMESTAMP(r.rev_enddate) AS endtimestamp, DATE_FORMAT(r.rev_startdate, "%d.%m.%Y") AS startdate, ';
					$sql.= '        DATE_FORMAT(r.rev_enddate, "%d.%m.%Y") AS enddate, DATE_FORMAT(rs.revs_timestamp, "%d.%m") AS stepdate ';
				}
		}
		$sql.= 'FROM ' . $tbl_rev . ' AS r, ' . $tbl_step . ' AS rs, ' . $tbl_page . ' AS p, ' . $tbl_user . ' AS u, ' . $tbl_user . ' AS u2 ';
		$sql.= 'WHERE r.rev_pid=p.page_id AND r.rev_id=rs.revs_review_id AND rs.revs_user_id=u.user_id AND r.rev_owner=u2.user_id ';

		// What is the user allowed to see?
		if ($wgUser->isAllowed('workflowlist')) {
			global $wgRequest;
			$iUserId = $wgRequest->getInt('user', $wgUser->mId);
			// if( intval($_GET['user']) )
			if (!( $iUserId === false )) { // <== getParam returns default (false) if INT is expected and param is not numeric
				//$sql.= 'AND (r.owner="'. $_GET['user'] .'" OR "'. $_GET['user'] .'" IN (SELECT hrs.user_id FROM hw_review_steps AS hrs WHERE hrs.review_id=r.id)) ';
				$sql.= 'AND (r.rev_owner=' . $iUserId . ' OR EXISTS (SELECT 1 FROM ' . $tbl_step . ' AS hrs WHERE hrs.revs_review_id=r.rev_id AND hrs.revs_user_id = ' . $iUserId . ')) ';
			}
		} else {
			$sql.= 'AND (r.rev_owner=' . $wgUser->mId . ' OR EXISTS (SELECT 1 FROM ' . $tbl_step . ' AS hrs WHERE hrs.revs_review_id=r.rev_id AND hrs.revs_user_id = ' . $wgUser->mId . ')) ';
		}

		$sql.= 'ORDER BY r.rev_startdate DESC, rs.revs_sort_id';
		$res = $dbr->query($sql);

		// Sorting the data because of the status column (accepted status)
		$arrList = array();
		while ($row = $dbr->fetchRow($res)) {

			if (!isset($arrList[$row['rev_id']])) {
				$arrList[$row['rev_id']]['array'] = $row;
			}

			$objReview = BsReviewProcess::newFromPid($row['rev_pid']);
			$arrList[$row['rev_id']]['revs_status'] = $objReview->getStatus($row['endtimestamp']);

			switch ($row['revs_status']) {
				case '-1':
					$arrList[$row['rev_id']]['total'] = isset($arrList[$row['rev_id']]['total']) ? $arrList[$row['rev_id']]['total'] + 1 : 1;
					break;
				case '0':
					//case '-3':
					$arrList[$row['rev_id']]['rejected'] = isset($arrList[$row['rev_id']]['rejected']) ? $arrList[$row['rev_id']]['rejected'] + 1 : 1;
					$arrList[$row['rev_id']]['total'] = isset($arrList[$row['rev_id']]['total']) ? $arrList[$row['rev_id']]['total'] + 1 : 1;
					break;
				case '1':
					//case '-2':
					$arrList[$row['rev_id']]['accepted'] = isset($arrList[$row['rev_id']]['accepted']) ? $arrList[$row['rev_id']]['accepted'] + 1 : 1;
					$arrList[$row['rev_id']]['total'] = isset($arrList[$row['rev_id']]['total']) ? $arrList[$row['rev_id']]['total'] + 1 : 1;
					break;
			}

			// If show assessor is true then insert also the assessors in the array
			if ($bShowAssessor) {
				$arrList[$row['rev_id']]['assessors'][] = array(
					'name' => $row['user_name'],
					'real_name' => $row['user_real_name'],
					'revs_status' => $row['revs_status'],
					'timestamp' => $row['stepdate']
				);
			}
		}

		return $arrList;
	}

	/**
	 * Produces a log message for bs-review/create.
	 * @param string $type Log type as defined for MediaWiki.
	 * @param string $action Log type as defined for MediaWiki.
	 * @param Title $title Title of the page for which an action is being logged.
	 * @param Skin $skin Skin object.
	 * @param array $params Not used.
	 * @param bool $filterWikilinks Not used.
	 * @return string Internationalized log message.
	 */
	public function logCreate($type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false) {
		if ($skin === null)
			return true;
		return wfMessage('bs-review-created-review', $skin->link($title))->plain();
	}

	// TODO RBV (30.06.11 13:07): Maybe a callback function would have done the trick, that chooses the return value according to $action?
	/**
	 * Produces a log message for bs-review/modify.
	 * @param string $type Log type as defined for MediaWiki.
	 * @param string $action Log type as defined for MediaWiki.
	 * @param Title $title Title of the page for which an action is being logged.
	 * @param Skin $skin Skin object.
	 * @param array $params Not used.
	 * @param bool $filterWikilinks Not used.
	 * @return string Internationalized log message.
	 */
	public function logModify($type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false) {
		if (is_null($skin))
			return true;
		return wfMessage('bs-review-modified-review', $skin->link($title))->plain();
	}

	/**
	 * Produces a log message for bs-review/delete.
	 * @param string $type Log type as defined for MediaWiki.
	 * @param string $action Log type as defined for MediaWiki.
	 * @param Title $title Title of the page for which an action is being logged.
	 * @param Skin $skin Skin object.
	 * @param array $params Not used.
	 * @param bool $filterWikilinks Not used.
	 * @return string Internationalized log message.
	 */
	public function logDelete($type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false) {
		if (is_null($skin))
			return true;
		return wfMessage('bs-review-deleted-review', $skin->link($title))->plain();
	}

	/**
	 * Produces a log message for bs-review/approve.
	 * @param string $type Log type as defined for MediaWiki.
	 * @param string $action Log type as defined for MediaWiki.
	 * @param Title $title Title of the page for which an action is being logged.
	 * @param Skin $skin Skin object.
	 * @param array $params Not used.
	 * @param bool $filterWikilinks Not used.
	 * @return string Internationalized log message.
	 */
	public function logApprove($type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false) {
		if (is_null($skin))
			return true;
		return wfMessage('bs-review-approved-review', $skin->link($title))->plain();
	}

	/**
	 * Produces a log message for bs-review/Deny.
	 * @param string $type Log type as defined for MediaWiki.
	 * @param string $action Log type as defined for MediaWiki.
	 * @param Title $title Title of the page for which an action is being logged.
	 * @param Skin $skin Skin object.
	 * @param array $params Not used.
	 * @param bool $filterWikilinks Not used.
	 * @return string Internationalized log message.
	 */
	public function logDeny($type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false) {
		if (is_null($skin))
			return true;
		return wfMessage('bs-review-denied-review', $skin->link($title))->plain();
	}

	/**
	 * Produces a log message for bs-review/finish.
	 * @param string $type Log type as defined for MediaWiki.
	 * @param string $action Log type as defined for MediaWiki.
	 * @param Title $title Title of the page for which an action is being logged.
	 * @param Skin $skin Skin object.
	 * @param array $params Not used.
	 * @param bool $filterWikilinks Not used.
	 * @return string Internationalized log message.
	 */
	public function logFinish($type, $action, $title = NULL, $skin = NULL, $params = array(), $filterWikilinks = false) {
		if (is_null($skin))
			return true;
		return wfMessage('bs-review-finished-review', $skin->link($title))->plain();
	}

	/**
	 * Returns a JSON encoded list of users. Called by review handler
	 * @return bool Just some return value. Looks nice.
	 */
	public static function getUsers() {
		if (BsCore::checkAccessAdmission('read') === false)
			return true;
		$aJsonOut = array();
		$aJsonOut['users'] = array();
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->select('user', 'user_id, user_name', '', '', array('ORDER BY' => 'user_name'));
		while ($row = $dbw->fetchRow($res)) {
			$aUser = array();
			$oUser = User::newFromName($row['user_name']);
			$aUser['username'] = $oUser->getName();
			$aUser['userid'] = $row['user_id'];
			$aUser['displayname'] = BsCore::getInstance()->getUserDisplayName($oUser);
			#$oReviewFormView->addAssessor( $oUser->getName(), $this->mCore->getUserDisplayName( $oUser ) );
			//}
			$aJsonOut['users'][] = $aUser;
		}
		return json_encode($aJsonOut);
	}

	/**
	 * Can a user edit a particular page?
	 * @param Title $oTitle Title object of current page.
	 * @param User $oUser Currently authenticated user.
	 * @param string $sAction Action for which a permission is being requested.
	 * @param bool $bRight Is user currently allowed to do the action on the page? If this is set to false, permission will be denied.
	 * @return bool Allow other hooked methods to be executed. False if edit right is denied. 
	 */
	public function checkReviewPermissions($oTitle, $oUser, $sAction, &$bRight) {
		$aActionsBlacklist = array('edit', 'delete', 'move', 'protect', 'rollback');
		if (!in_array($sAction, $aActionsBlacklist))
			return true;

		$oRev = BsReviewProcess::newFromPid($oTitle->getArticleID());
		if ($oRev == false)
			return true; // There is no review on the page


			
// Because of FlaggedRevs is it now allowed to edit when a workflow is finished...
		$bResult = false;
		wfRunHooks('checkPageIsReviewable', array($oTitle, &$bResult));

		if (( $oRev->isActive() ) || ( $oRev->isStarted() && $bResult == false )) {
			// Restrict access only after review process has been started
			if (!$oRev->isEditable()) {
				$bRight = false;
				return false;
			}

			// check, if current user can currently review.
			$aPages = BsReviewProcess::listReviews($oUser->getId());
			if (!in_array($oTitle->getArticleID(), $aPages)) {
				$bRight = false;
				return false;
			}
		}

		return true;
	}

	/**
	 * Prevents the FlaggedRevsConnector form from being shown when a workflow is active
	 * @param Title $oCurrentTitle
	 * @param array $aFlagInfo
	 * @return boolean 
	 */
	public function onBSFlaggedRevsConnectorCollectFlagInfo($oCurrentTitle, &$aFlagInfo) {
		$oRev = BsReviewProcess::newFromPid($oCurrentTitle->getArticleID());
		if ($oRev instanceof BsReviewProcess && $oRev->isActive()) {
			$aFlagInfo['user-can-review'] = false;
			return false;
		}
		return true;
	}

	// TODO RBV (30.06.11 13:18): Coding Conventions for parameters
	/**
	 * Checks whether the current user or the current page has a review and produces signs accordingly. Called by SkinTemplateOutputPageBeforeExec.
	 * @param SkinTemplate $oSkin MediaWiki SkinTemplate object.
	 * @param QuickTemplate $tpl Current MediaWiki OutputPage object.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function checkReviewStatus(&$oSkin, &$tpl) {
		// get all reviews for current user;

		$oLoggedInUser = $this->getUser();
		$oRev = BsReviewProcess::newFromPid($tpl->data['articleid']);
		$pages = BsReviewProcess::listReviews($oLoggedInUser->getId());
		$oTitle = Title::newFromID($tpl->data['articleid']);

		if ($oRev) {
			// Flagged Revision: Only show the "not accepted" icon on the template page an not on the released page, which is accepted.
			$obj = false;
			$bResult = false;
			wfRunHooks('checkPageIsReviewable', array($oTitle, &$bResult));
			if ($bResult) {
				$obj = FlaggedRevision::newFromStable($oTitle);
			}
		}

		$num = count($pages);
		if ($num) {
			// print pages to be reviewed
			$text = wfMessage('bs-review-to_be_reviewed', $num)->plain();
			$text .= "<ul>";
			foreach ($pages as $page) {
				$oTitle = Title::newFromID($page);
				//this is a dirty workaround to page being delete while workflow is going on
				if (!$oTitle)
					continue;

				$rev_page = BsReviewProcess::newFromPid($page);
				$url = $oTitle->getFullUrl();

				// TODO RBV (30.06.11 13:27): All these "if  FlaggedRevs" should be using hooks/events.
				// If FlaggedRevs is active, we redirect to the "unstable" version
				$bResult = false;
				wfRunHooks('checkPageIsReviewable', array($oTitle, &$bResult));
				if ($bResult) {
					// TODO RBV (30.06.11 13:29): URLs with Parametern e.g. via Title::getFullUrl( array( 'stable' => 0 ) );
					if (strstr($url, "?title")) {
						$url.= "&";
					} else {
						$url.= "?";
					}
					$url.= "stable=0";
				}

				// Show the name of the user who created the workflow?
				$name = '';
				if (BsConfig::get('MW::Review::ShowNameInTooltip')) {
					$user = User::newFromId($rev_page->getOwner());
					$name = $user->getName() . ', ';
				}
				// TODO RBV (30.06.11 13:33): Use views.
				$text .= '<li><a href=\\\'' . $url . '\\\'>' . $oTitle->getText() . '</a> <i>(' . $name . 'bis ' . $rev_page->getEnddate() . ')</i></li>';
			}
			$text .= '</ul>';
			$this->setHook('BlueSpiceSkin:BeforeUserBar', 'makeUserBar');

			// check if current page is to be reviewed
			if (in_array($tpl->data['articleid'], $pages)) {
				BsExtensionManager::setContext('MW::ReviewShow');
			}
		}
		return true;
	}

	/**
	 * Creates or changes a review for a page. Called by remote handler.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public static function doEditReview() {
		if (BsCore::checkAccessAdmission('workflowedit') === false)
			return true;
		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);
		$oUser = BsCore::loadCurrentUser();
		$oReview = BsExtensionManager::getExtension('Review');

		$userIsSysop = in_array('sysop', $oUser->getGroups()); //TODO: getEffectiveGroups()?

		if (!$userIsSysop && !$oUser->isAllowed('workflowedit')) {
			$aAnswer['success'] = false;
			$aAnswer['messages'][] = wfMessage('bs-review-save_norights')->plain();
			return json_encode($aAnswer);
		}

		global $wgRequest;
		$paramRvPid = $wgRequest->getInt('pid', -1);
		// Check for id 0 prevents special pages to be put on a review
		if (empty($paramRvPid)) {
			$aAnswer['success'] = false;
			$aAnswer['messages'][] = wfMessage('bs-review-save_noid')->plain();
			return json_encode($aAnswer);
		}

		$oReviewProcess = BsReviewProcess::newFromPid($paramRvPid);
		$bIsEdit = false;

		if (is_object($oReviewProcess) && $oReviewProcess->hasSteps())
			$bIsEdit = true;
		if (!$userIsSysop && $oReviewProcess && BsConfig::get('MW::Review::CheckOwner') && ( $oReviewProcess->owner != $oUser->getID() )) {

			$aAnswer['success'] = false;
			$aAnswer['messages'][] = wfMessage('bs-review-save_norights')->plain();
			return json_encode($aAnswer);
		}

		$paramCmd = $wgRequest->getVal('cmd', '');
		$paramSaveTmpl = $wgRequest->getInt('save_tmpl', 0);

		if (!( $paramCmd === false )) {
			switch ($paramCmd) {
				case 'insert' :
					$aErrors = array();
					$review = BsReviewProcess::newFromJSON($wgRequest->getVal('review', ''), $aErrors);

					if (is_array($aErrors) && count($aErrors) > 0) {
						$aAnswer['success'] = false;
						foreach ($aErrors as $sError) {
							$aAnswer['messages'][] = wfMessage('bs-review-' . $sError)->plain();
						}
						return json_encode($aAnswer);
					};

					$review->setOwner($oUser->getID());
					$oOldReview = BsReviewProcess::newFromPid($paramRvPid);
					$update = is_object($oOldReview) ? $oOldReview->getPid() : false;
					BsReviewProcess::removeReviewSteps($paramRvPid);
					if ($paramSaveTmpl == 1) {
						$paramTmplChoice = $wgRequest->getInt('tmpl_choice', -1);
						$paramTmplName = $wgRequest->getVal('tmpl_name', '');
						$review->asTemplate($paramTmplChoice, $paramTmplName);
					}

					if (!is_array($review->steps)) {
						$aAnswer['success'] = false;
						$aAnswer['messages'][] = wfMessage('bs-review-save_nosteps')->plain();
						return json_encode($aAnswer);
					}
					if ($review->store($update)) {
						$oTitle = Title::newFromID($paramRvPid);
						$oTitle->invalidateCache();
						$oWatchlist = WatchedItem::fromUserTitle($oUser, $oTitle);
						if (!$oWatchlist->isWatched()) {
							$oWatchlist->addWatch();
						}

						$aParams = array(
							'action' => $bIsEdit ? 'modify' : 'create',
							'target' => $oTitle,
							'comment' => '',
							'params' => null,
							'doer' => $oUser
						);
						$oReview->oLogger->addEntry($aParams['action'], $aParams['target'], $aParams['comment'], $aParams['params'], $aParams['doer']);

						$aAnswer['messages'][] = wfMessage('bs-review-save_success')->plain();

						// Identify owner
						$oReviewProcess = BsReviewProcess::newFromPid($paramRvPid);

						$oReview->emailNotifyNextUsers($oReviewProcess);

						return json_encode($aAnswer);
					} else {
						$aAnswer['success'] = false;
						$aAnswer['messages'][] = wfMessage('bs-review-save_error')->plain();
						return json_encode($aAnswer);
					}
					break; // 22.08.13 STM: WTF?
				case 'delete' :
					BsReviewProcess::removeReviews($paramRvPid);
					$oTitle = Title::newFromID($paramRvPid);
					$oTitle->invalidateCache();
					$oWatchlist = WatchedItem::fromUserTitle($oUser, $oTitle);
					if ($oWatchlist->isWatched()) {
						$oWatchlist->removeWatch();
					}
					$aParams = array(
						'action' => 'delete',
						'target' => $oTitle,
						'comment' => '',
						'params' => null,
						'doer' => $oUser
					);
					$oReview->oLogger->addEntry($aParams['action'], $aParams['target'], $aParams['comment'], $aParams['params'], $aParams['doer']);

					$aAnswer['messages'][] = wfMessage('bs-review-save_removed')->plain();
					return json_encode($aAnswer);
					break;
			}
		}
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @return boolean Always true to keep hook running 
	 */
	public function onStateBarBeforeTopViewAdd($oStateBar, &$aTopViews, $oUser, $oTitle) {
		$sIcon = 'bs-infobar-workflow-open';
		$oRev = BsReviewProcess::newFromPid($oTitle->getArticleID());
		if ($oRev !== false) {
			if ($res = $oRev->isFinished()) {
				if ($oRev->isSequential()) {
					switch ($res) {
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
					$res = explode(';', $res);

					if ($res[2] > $res[1]) {
						$sIcon = "bs-infobar-workflow-ok";
					} else if ($res[2] < $res[1]) {
						$sIcon = "bs-infobar-workflow-dismissed";
					} else {
						$sIcon = "bs-infobar-workflow-open";
					}
				}
			}
			$sIcon .= ".png";

			//This hook is too late for OutputPage::addJsConfigVars
			$this->getOutput()->addHTML(
					Html::inlineScript(
							'var bsReview = ' . Xml::encodeJsVar($this->makeJSDataObject($oRev)) . ';'
					)
			);
			$aTopViews['statebartopreview'] = $this->makeStateBarTopReview($sIcon);
		}
		return true;
	}

	/**
	 * Adds information to an data object that is needed to properly initialise
	 * 'BS.Review.ReviewPanel'
	 * @param BsReviewProcess $oReview
	 * @return \stdClass
	 */
	protected function makeJSDataObject($oReview) {
		//TODO: Allow injection of data by ExtendedReview or other extensions
		$oData = new stdClass();
		$oData->startdate = strtotime($oReview->startdate);
		$oData->enddate = strtotime($oReview->enddate);
		$oData->owner_user_id = $oReview->getOwner();
		$oData->owner_user_name = User::newFromId($oReview->getOwner())->getName();
		$oData->page_id = $oReview->getPid();
		$oData->page_prefixed_text = Title::newFromID($oReview->getPid())->getPrefixedText();
		$oData->editable = $oReview->isEditable();
		$oData->sequential = $oReview->isSequential();
		$oData->abortable = $oReview->isAbortWhenDenied();
		$oData->steps = array();

		foreach ($oReview->steps as $oStep) {
			if ($oStep instanceof BsReviewProcessStep == false)
				continue;

			$oUser = User::newFromId($oStep->user);

			$aStep = array(
				'user_id' => $oStep->user,
				'user_name' => $oUser->getName(),
				'user_display_name' => BsCore::getUserDisplayName($oUser),
				'comment' => $oStep->comment,
				'status' => $oStep->status,
				'sort_id' => $oStep->sort_id,
			);

			$oData->steps[] = $aStep;
		}

		return $oData;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aBodyViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeBodyViewAdd($oStateBar, &$aBodyViews, $oUser, $oTitle) {
		$text = '';
		$oRev = BsReviewProcess::newFromPid($oTitle->getArticleID());
		$pages = BsReviewProcess::listReviews($oUser->getId());

		if ($oRev === false)
			return true;

		$oReviewView = new ViewStateBarBodyElementReview();
		$oReviewView->setReview($oRev);
		$oReviewView->addButton(
				'bs-review-dismiss', 'bs-icon-decline', wfMessage('bs-review-i-dismiss')->plain(), wfMessage('bs-review-i-dismiss')->plain()
		);
		$oReviewView->addButton(
				'bs-review-ok', 'bs-icon-accept', wfMessage('bs-review-i-agree')->plain(), wfMessage('bs-review-i-agree')->plain()
		);
		

		if ($res = $oRev->isFinished()) {
			//$text = wfMessage( 'bs-review-review_finished' )->plain();
			$oReviewView->setStatusText(wfMessage('bs-review-review_finished')->plain());
			if ($oRev->isSequential()) {
				switch ($res) {
					case 'date' :
						$text .= wfMessage('bs-review-date_')->plain();
						break;
					case 'status' :
						$text .= wfMessage('bs-review-agreed')->plain();
						break;
					case 'denied' :
						$text .= wfMessage('bs-review-denied_')->plain();
						break;
				}
			} else {
				$res = $oRev->currentStatus();
				$res = explode(';', $res);
				if ($res[2]) {
					$text .= "<br />" . wfMessage('bs-review-accepted')->plain() . ":" . $res[2];
				}
				if ($res[1]) {
					$text .= "<br />" . wfMessage('bs-review-rejected')->plain() . ":" . $res[1];
				}
				if ($res[0]) {
					$text .= "<br />" . wfMessage('bs-review-abstain')->plain() . ":" . $res[0];
				}
			}
			$oReviewView->setStatusReasonText($text);
		} else {

			$text = wfMessage('bs-review-reviewed_till', $oRev->getStartdate(), $oRev->getEnddate())->plain();

			// Show who created the workflow?
			if (BsConfig::get('MW::Review::ShowNameInTooltip')) {
				$user = User::newFromId($oRev->owner);
				$sName = BsCore::getUserDisplayName($user);
				$text.= wfMessage('bs-review-reviewed_till_extra', $sName)->plain();
			}
			$oReviewView->setStatusText($text);
		}

		// Flagged Revision: Only show the "not accepted" icon on the template page an not on the released page, which is accepted.
		$obj = false;
		$bResult = false;
		wfRunHooks('checkPageIsReviewable', array($oTitle, &$bResult));
		if ($bResult) {
			$obj = FlaggedRevision::newFromStable($oTitle);
		}

		if (empty($pages) || !in_array($oTitle->getArticleID(), $pages)) {
			$aBodyViews['statebarbodyreview'] = $oReviewView;
			return true;
		}

		$step = $oRev->currentStep($oUser->getId());
		//BsExtensionManager::setContext( 'MW::ReviewShow' );
		if (!is_object($step))
			return true;

		$oReviewView->setVotable();
		if (!empty($step->comment)) {
			$oReviewView->setComment($step->comment);
		}

		wfRunHooks('BsReview::checkStatus::afterMessage', array($step, $oReviewView));
		$aBodyViews['statebarbodyreview'] = $oReviewView;
		return true;
	}

	/**
	 * Renders status output to StatusBar top secion.
	 * @param string $sIcon Filename of the icon to be displayed. Relative to extension image dir.
	 * @return ViewStateBarTopElement View that is part of StateBar.
	 */
	public function makeStateBarTopReview($sIcon) {
		$oReviewView = new ViewStateBarTopElement();
		global $wgScriptPath;
		if (is_object($this->getTitle())) {
			$oReviewView->setKey('Review');
			// TODO MRG (12.06.11 23:54): Use abstraction getImagePath
			$oReviewView->setIconSrc($wgScriptPath . '/extensions/BlueSpiceExtensions/Review/resources/images/' . $sIcon);
			$oReviewView->setIconAlt(wfMessage('bs-review-statebar-top')->plain());
			$oReviewView->setText(wfMessage('bs-review-statebar-top')->plain());
			//$oReviewView->setTextLink( $sArticleEditPageLink );
			//$oReviewView->setTextLinkTitle( wfMsg( 'statebar-top' ) );
		}
		return $oReviewView;
	}

	/**
	 * Renders user bar icon if current user has an open review.
	 * @param array $aViews Array of views in TopBar.
	 * @param User $oUser Current user.
	 * @param object $oSender The sender of that event.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function makeUserBar(&$aViews, $oUser, $oSender) {
		global $wgScriptPath;
		
		if( $oUser->isAllowed('workflowview') === false ) {
			return true;
		}
		
		$iCountReviews = count(BsReviewProcess::listReviews($oUser->getId()));
		$iCountFinishedReviews = BsReviewProcess::userHasWaitingReviews($oUser);

		if ($iCountReviews <= 0 && !$iCountFinishedReviews)
			return true;

		$oUserBarElementView = new ViewUserBarElement();
		$oUserBarElementView->setId('review-userbar-element');
		$oUserBarElementView->setLink(SpecialPage::getTitleFor('Review')->getFullURL() . '/' . $oUser->getName());
		$oUserBarElementView->setIcon($wgScriptPath . '/extensions/BlueSpiceExtensions/Review/resources/images/bs-icon-review.png');

		$oUserBarElementView->setText($iCountReviews ."|". $iCountFinishedReviews);

		$aViews[] = $oUserBarElementView;

		return true;
	}

	/**
	 * Called when a review vote is cast. Handles votes. Called by remote handler.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public static function getVoteResponse() {
		global $wgRequest;

		$iArticleId = $wgRequest->getInt('articleID', 0);
		$sVote = $wgRequest->getVal('vote', '');
		$sComment = $wgRequest->getVal('comment', '');

		if (BsCore::checkAccessAdmission('workflowview') === false)
			return '';

		if (empty($iArticleId) || empty($sVote)) {
			return wfMessage('bs-review-review_error')->plain();
		}

		$oReview = BsExtensionManager::getExtension('Review');
		$oUser = RequestContext::getMain()->getUser();
		$oTitle = Title::newFromID($iArticleId);
		$sTitleText = $oTitle->getPrefixedText();
		$sTitleUrl = $oTitle->getFullURL();
		$oNext = null;

		$dbw = wfGetDB(DB_MASTER);
		// Get ID of the apropriate step
		$tables = array();
		$tables[] = 'bs_review';
		$tables[] = 'bs_review_steps';

		$tbl_rev = $dbw->tableName('bs_review');
		$tbl_step = $dbw->tableName('bs_review_steps');

		$conds = array();
		$conds[] = $tbl_step . '.revs_review_id = ' . $tbl_rev . '.rev_id';  // join tables
		$conds[] = $tbl_rev . '.rev_pid=' . $iArticleId; // reviews only for current article
		$conds[] = $tbl_step . '.revs_status=-1';  // prevent user from confirming twice

		$options = array('ORDER BY' => 'revs_sort_id ASC');
		$join_conds = array();

		wfRunHooks('BsReview::buildDbQuery', array('getVoteResponse', &$tables, &$fields, &$conds, &$options, &$join_conds));

		$res = $dbw->select($tables, $tbl_step . '.*', $conds, __METHOD__, $options, $join_conds);
		$row = $dbw->fetchRow($res);

		// Unexpectedly, no review could be found.
		if ($dbw->numRows($res) == 0) {
			return wfMessage('bs-review-review_secondtime')->plain();
		} elseif ($dbw->numRows($res) > 1) {
			$oNext = $dbw->fetchObject($res);
		}

		$dbw->freeResult($res);

		$step_id = $row['revs_id'];

		// update data
		$data = array();
		switch ($sVote) {
			case "yes" :
				$data['revs_status'] = 1;
				$oReview->oLogger->addEntry('approve', $oTitle, '', null, $oUser);
				if (empty($sComment) || is_null($oNext) || !$oNext)
					break;
				$sUserName = BsCore::getUserDisplayName($oUser);
				$dbw->update(
						'bs_review_steps', array('revs_comment' => $oNext->revs_comment . "<br /><b>$sUserName: </b>$sComment"), array('revs_id' => $oNext->revs_id)
				);
				break;
			case "no" :
				$data['revs_status'] = 0;
				$oReview->oLogger->addEntry('deny', $oTitle, '', null, $oUser);
				break;
			default :
				$data['revs_status'] = -1;
				break;
		}

		wfRunHooks('BsReview::dataBeforeSafe', array('getVoteResponse', &$data));

		$dbw->update('bs_review_steps', $data, array('revs_id' => $step_id));

		$oTitle->invalidateCache();

		// Identify owner
		$oReviewProcess = BsReviewProcess::newFromPid($iArticleId);
		$oOwner = User::newFromID($oReviewProcess->getOwner());
		$sOwnerMail = $oOwner->getEmail();

		if ($sVote == 'yes') {
			self::sendNotification('accept', $oOwner, array($sTitleText, date('Y-m-d')), $sTitleUrl, $oUser);
		} elseif ($sVote == 'no') {
			if ($oReviewProcess->isSequential()) {
				$oReviewProcess->reset($sComment);
				self::sendNotification('deny-and-restart', $oOwner, array($sTitleText, date('Y-m-d')), $sTitleUrl, $oUser);
			} else {
				self::sendNotification('deny', $oOwner, array($sTitleText, date('Y-m-d')), $sTitleUrl, $oUser);
			}
		}

		wfRunHooks('BsReview::getVoteResponseOnMailAction', array($row, $oTitle, $oOwner));

		// Let flagged revision know that it's all goooooood (or not approved)
		$bResult = true;
		wfRunHooks('checkPageIsReviewable', array($oTitle, &$bResult));
		if ($bResult) {
			if ($oReviewProcess->isFinished() == 'status') {
				if (!$oUser->isAllowed('review')) {
					self::sendNotification('finish', $oOwner, array($sTitleText), $sTitleUrl);
				} else {
					self::sendNotification('finish-and-review', $oOwner, array($sTitleText), $sTitleUrl);
				}
			}
		} else {
			if ($sOwnerMail) {
				self::sendNotification('finish-no-flagged-revs', $oOwner, array($sTitleText), $sTitleUrl);
			}
		}

		// Unfortunately, there is no way of verifying the result :(
		return wfMessage('bs-review-review_saved')->plain();
	}

	/**
	 * Remove review when an article is deleted.
	 * @param Article $article Article object of deleted article.
	 * @param User $user Currently logged in user.
	 * @param string $reason Reason for page deletion.
	 * @param int $id ID of the page deleted.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function onArticleDeleteComplete(&$article, &$user, $reason, $id) {
		BsReviewProcess::removeReviews($id);
		return true;
	}

	/**
	 * Adds CSS to Page
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean 
	 */
	public function onBeforePageDisplay(&$out, &$skin) {
		$out->addModuleStyles('ext.bluespice.review.styles');

		if ($out->getTitle()->isContentPage() == false)
			return true;
		if ($out->getTitle()->exists() == false)
			return true;
		//if( $out->getTitle()->userCan('workflowread') == false ) return true;

		$out->addModules('ext.bluespice.review');

		//PW TODO: find better way
		//this always was loaded too late, no matter what dependency or position
		$out->addScript(
				'<script>' .
				"$(document).on( 'BsStateBarRegisterToggleClickElements', function(event, aRegisteredToggleClickElements) {" .
				"aRegisteredToggleClickElements.push($('#sb-Review'));" .
				"});" .
				'</script>'
		);

		$bUserCanEdit = $out->getTitle()->userCan('workflowedit');
		$out->addJsConfigVars('bsReviewUserCanEdit', $bUserCanEdit);

		return true;
	}

	/**
	 * Send email notification to next user(s) on review list.
	 * @param BsReviewProcess $oReviewProcess Review process users should be notified for.
	 * @return Status
	 */
	public function emailNotifyNextUsers($oReviewProcess) {
		$aNextUsers = $oReviewProcess->getNextUsers();

		// Identify owner
		$oOwner = User::newFromId($oReviewProcess->getOwner());
		$sOwnerName = $this->mCore->getUserDisplayName($oOwner);

		$oTitle = Title::newFromID($oReviewProcess->pid);
		$sTitleText = $oTitle->getPrefixedText();
		$sTitleUrl = $oTitle->getFullURL();

		foreach ($aNextUsers as $aReviewer) {
			// dirty workaround, sometimes id comes as username
			if (is_numeric($aReviewer['id'])) {
				$oReviewer = User::newFromId($aReviewer['id']);
			} else {
				$oReviewer = User::newFromName($aReviewer['id']);
			}

			if (!BsConfig::getVarForUser('MW::Review::EmailNotifyReviewer', $oReviewer->getName())) {
				continue;
			}

			// Identify reviewer
			$sReviewerMail = $oReviewer->getEmail();
			if (!$sReviewerMail)
				continue;

			$sReviewerLang = $oReviewer->getOption('language');

			$sSubject = wfMessage(
							'bs-review-mail-invite-header', BsConfig::get('MW::Sitename'), $sTitleText
					)->inLanguage($sReviewerLang)->plain();

			$sMsg = wfMessage(
							'bs-review-mail-invite-body', $sOwnerName, $sTitleText
					)->inLanguage($sReviewerLang)->plain();

			$sMsg .= wfMessage(
							'bs-review-mail-link-to-page', $sTitleUrl
					)->inLanguage($sReviewerLang)->plain();

			if ($aReviewer['comment']) {
				$sMsg .= wfMessage(
								'bs-review-mail-comment', $aReviewer['comment']
						)->inLanguage($sReviewerLang)->plain();
			}

			//Send mail to next user in queue
			return BsMailer::getInstance('MW')->send($oReviewer, $sSubject, $sMsg);
		}
	}

	/**
	 * The preferences plugin callback
	 * @param string $sAdapterName
	 * @param BsConfig $oVariable
	 * @return array MediaWiki preferences options array
	 */
	public function runPreferencePlugin($sAdapterName, $oVariable) {
		$aPrefs = array();
		wfRunHooks('BSReviewRunPreferencePlugin', array(&$sAdapterName, &$oVariable, &$aPrefs));
		return $aPrefs;
	}

}
