<?php

class SpecialExtendedSearchAdmin extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'ExtendedSearchAdmin', 'extendedsearchadmin-viewspecialpage' );

	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
	 * @param type $sParameter
	 * @return type
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
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

		if ( ExtendedSearchAdmin::checkLockExistence() === false ) {
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

		$this->getOutput()->addHTML( $sForm );
	}

}
