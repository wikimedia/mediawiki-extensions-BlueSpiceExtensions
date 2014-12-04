<?php
/**
 * Admin section for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * Base class for ExtendedSearch admin section
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ExtendedSearchAdmin {

	/**
	 * Instance of ExtendedSearchAdmin
	 * @var Object
	 */
	protected static $oInstance = null;

	/**
	 * Constructor of ExtendedSearchAdmin class
	 */
	public function __construct() {}

	/**
	 * Return a instance of ExtendedSearchAdmin.
	 * @return ExtendedSearchAdmin Instance of ExtendedSearchAdmin
	 */
	public static function getInstance() {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( self::$oInstance === null ) {
			self::$oInstance = new self();
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return self::$oInstance;
	}

	/**
	 * Return progress information when update index is called.
	 * @param string $sMode which progress should be rendered
	 */
	public static function getProgressBar( $sMode ) {
		switch ( $sMode ) {
			case 'createForm' :
				$sOutput = ExtendedSearchAdmin::getInstance()->getCreateForm();
				break;
			case 'create':
				$sOutput = ExtendedSearchAdmin::getInstance()->getCreateFeedback();
				break;
			case 'delete':
				$sOutput = ExtendedSearchAdmin::getInstance()->getDeleteFeedback();
				break;
			case 'deleteLock':
				$sOutput = ExtendedSearchAdmin::getInstance()->checkLockExistence( $sMode );
				break;
			default:
				$sOutput = '';
		}
		return $sOutput;
	}

	/**
	 * Renders the HTML for the admin section within WikiAdmin
	 * @return string HTML output to be displayed
	 */
	public function getForm() {
		if ( wfReadOnly() ) {
			throw new ReadOnlyError;
		}
		global $wgScriptPath;

		RequestContext::getMain()->getOutput()->addModules( 'ext.bluespice.extendedsearch.admin' );

		$sForm = '';

		if ( SearchService::getInstance()->ping( 2 ) === false ) {
			RequestContext::getMain()->getOutput()->addHTML(
				'<br /><div style="color:#F00; font-size:20px;">' . wfMessage( 'bs-extendedsearch-server-not-available' )->escaped() . '</div><br />'
			);
			return false;
		}

		if ( !ExtendedSearchBase::isCurlActivated() ) {
			RequestContext::getMain()->getOutput()->addHTML(
				'<br /><div style="color:#F00; font-size:20px;">' . wfMessage( 'bs-extendedsearch-curl-not-active' )->escaped() . '</div><br />'
			);
			return false;
		}

		if ( $this->checkLockExistence() === false ) {
			$aSearchAdminButtons = array(
				'create' => array(
					'href' => '#',
					'onclick' => 'bs.util.toggleMessage( bs.util.getAjaxDispatcherUrl( \'ExtendedSearchAdmin::getProgressBar\', [\'createForm\'] ), \'' . addslashes( wfMessage( 'bs-extendedsearch-create-index' )->plain() ) . '\', 400, 300);setTimeout(\'bsExtendedSearchStartCreate()\', 1000);',
					'label' => wfMessage( 'bs-extendedsearch-create-index' )->escaped(),
					'image' => "$wgScriptPath/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images/bs-searchindex-rebuild.png"
				),
				'delete' => array(
					'href' => '#',
					'onclick' => 'bs.util.toggleMessage( bs.util.getAjaxDispatcherUrl( \'ExtendedSearchAdmin::getProgressBar\', [\'delete\'] ), \'' . addslashes( wfMessage( 'bs-extendedsearch-delete-index' )->plain() ) . '\', 400, 300);',
					'label' => wfMessage( 'bs-extendedsearch-delete-index' )->escaped(),
					'image' => "$wgScriptPath/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images/bs-searchindex-delete.png"
				),
				'overwrite' => array(
					'href' => '#',
					'onclick' => 'bs.util.toggleMessage( bs.util.getAjaxDispatcherUrl( \'ExtendedSearchAdmin::getProgressBar\', [\'createForm\'] ), \'' . addslashes( wfMessage( 'bs-extendedsearch-overwrite-index' )->plain() ) . '\', 400, 300);setTimeout(\'bsExtendedSearchStartCreate()\', 1000);',
					'label' => wfMessage( 'bs-extendedsearch-overwrite-index' )->escaped(),
					'image' => "$wgScriptPath/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images/bs-searchindex-optimization.png"
				)
			);
		} else {
			$aSearchAdminButtons = array(
				'deleteLock' => array(
					'href' => '#',
					'onclick' => 'bsExtendedSearchConfirm( \'' . wfMessage( 'bs-extendedsearch-warning' )->escaped() . '\', \'' . wfMessage( 'bs-extendedsearch-lockfiletext' )->escaped() . '\')',
					'label' => wfMessage( 'bs-extendedsearch-delete-lock' )->escaped(),
					'image' => "$wgScriptPath/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images/bs-searchindex-delete.png"
				)
			);
			$sForm .= '<h3><font color=\'red\'>' . wfMessage( 'bs-extendedsearch-indexinginprogress' )->escaped() . '</font></h3><br />';
		}

		wfRunHooks( 'BSExtendedSearchAdminButtons', array( $this, &$aSearchAdminButtons ) );

		foreach ( $aSearchAdminButtons as $key => $params ) {
			$sForm .= '<div class="bs-admincontrolbtn">';
			$sForm .= '<a href="'.$params['href'].'"';
			if ( $params['onclick'] ) $sForm .= ' onclick="'.$params['onclick'].'"';
			$sForm .= '>';
			$sForm .= '<img src="'.$params['image'].'" alt="'.$params['label'].'" title="'.$params['label'].'">';
			$sForm .= '<div class="bs-admin-label">';
			$sForm .= $params['label'];
			$sForm .= '</div>';
			$sForm .= '</a>';
			$sForm .= '</div>';
		}

		return $sForm;
	}

	/**
	 * Checks if lock file exists
	 * @param String $sMode
	 * @return bool existence
	 */
	public function checkLockExistence( $sMode = '' ) {
		if ( file_exists( BSDATADIR.DS.'ExtendedSearch.lock' ) ) {
			if ( $sMode == 'deleteLock' ) {
				unlink( BSDATADIR.DS.'ExtendedSearch.lock' );
			} else {
				return true;
			}
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Returns content of create index dialogue.
	 * @return string HTML to be rendered.
	 */
	public function getCreateForm() {
		return '<div id="hwstatus">
					<span id="BsExtendedSearchMode">'. wfMessage( 'bs-extendedsearch-status' )->plain().'</span>:
					<span id="BsExtendedSearchMessage">' . wfMessage( 'bs-extendedsearch-about_to_start' )->plain() . '</span>
				</div>
				<div id="BsExtendedSearchProgress">&nbsp;</div>';
	}

	/**
	 * Returns status information of create index progress.
	 * Error is indicated by return false or return null
	 * An ApacheAjaxResponse is expected
	 * If you return a string $s a new ApacheAjaxResponse($s) is created
	 * @return string Progress in percent or error message.
	 */
	public function getCreateFeedback() {
		// delete the old Index
		$this->getDeleteFeedback();
		// build the new Index
		$vRes = BuildIndexMainControl::getInstance()->buildIndex();
		/* Beware of returntype:
		 * Error is indicated by return false or return null
		 * An ApacheAjaxResponse is expected
		 * If you return a string $s a new ApacheAjaxResponse($s) is created
		 */

		return $vRes;
	}

	/**
	 * Returns status information of delete index progress.
	 * Error is indicated by return false or return null
	 * @return string information about the Progress or error message.
	 */
	public function getDeleteFeedback() {
		$sForm = '';
		$oSolr = SearchService::getInstance();
		if ( $oSolr === null ) return '';

		try {
			$iStatus = $oSolr->deleteIndex();
			if ( $iStatus == 200 ) {
				$iStatus = $oSolr->deleteIndex();
				$sForm .= wfMessage( 'bs-extendedsearch-index-successfully-deleted' )->plain() . '<br />';
			} else {
				$sForm .= wfMessage( 'bs-extendedsearch-index-error-deleting', $iStatus )->plain() . '<br />';
			}
		} catch ( Exception $e ) {
			$sForm .= wfMessage( 'bs-extendedsearch-no-success-deleting', $e->getMessage() )->plain() . '<br />';
			$sForm .= $e->getMessage();
		}

		return $sForm;
	}

}