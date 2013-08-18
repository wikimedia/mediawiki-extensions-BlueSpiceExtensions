<?php
class SpecialWikiAdmin extends BsSpecialPage {

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'SpecialWikiAdmin', 'wikiadmin' ); // SpecialPage($name, $restriction)
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function execute( $par ) {
		parent::execute( $par );
		global $wgOut;
		$output = '';

		# Get request data from, e.g.
		$wa_mode = BsCore::getParam( 'mode', 'WikiAdmin' );
		$wa_mode_Instance = null;
		$wa_mode_Credits = null;

		$runningModules = WikiAdmin::getRunningModules();
		foreach ( $runningModules as $moduleName => $moduleInstance ) {
			if ( strcasecmp( $wa_mode, $moduleName ) == 0 ) {
				$wa_mode_Credits = WikiAdmin::getRegisteredModule( $moduleName );
				if ( !BsCore::getInstance( 'MW' )->getAdapter()->User->isAllowed( $wa_mode_Credits['level'] ) ) {
					$output = wfMsg( 'bs-wikiadmin-not_allowed' );
					BsCore::getInstance( 'MW' )->getAdapter()->Out->addHTML( $output );
					return;
				}
				$wa_mode = $moduleName;
				$wa_mode_Instance = $moduleInstance;

				$moduleNameLower = strtolower( $moduleName );
				// Give grep a chance to find the usages:
				// bs-extendedsearch-label, bs-extendedsearchadmin-label, bs-extendedsearch-label,
				// bs-extendedsearchadmin-label, bs-extensioninfo-label, bs-groupmanager-label,
				// bs-interwikilinks-label, bs-namespacemanager-label, bs-pagetemplatesadmin-label,
				// bs-permissionmanager-label, bs-preferences-label, bs-usermanager-label
				$wgOut->setPagetitle( 'WikiAdmin - ' . wfMsg( 'bs-' . $moduleNameLower.'-label' ) );
			}
		}

		if ( $wa_mode_Instance != null ) {
			$output .= $wa_mode_Instance->getForm();
		}
		else {
			$output = $this->getForm();
		}
		# Output
		$wgOut->addHTML( '<div class="bs-admincontrolbtns clearfix">'.$output.'</div>' );
	}

	public function getForm() {
		$form = '';
		$registeredModules = WikiAdmin::getRegisteredModules();
		foreach ( $registeredModules as $module => $params ) {
			$moduleNameLower = strtolower( $module );
			$title = Title::newFromText( 'Special:WikiAdmin' );
			$url = $title->getLocalURL( 'mode='.$module );
			// TODO SU (04.07.11 10:56): Gehört eigentlich in eine view.
			$form .= '<div class="bs-admincontrolbtn">';
			$form .= '<a href="'.$url.'">';
			$form .= '<img src="'.BsConfig::get( 'MW::ScriptPath' ).$params['image'].'" alt="'.$module.'" title="'.$module.'">';
			$form .= '<div class="bs-admin-label">';
			// Give grep a chance to find the usages:
			// bs-extendedsearch-label, bs-extendedsearchadmin-label, bs-extendedsearch-label,
			// bs-extendedsearchadmin-label, bs-extensioninfo-label, bs-groupmanager-label,
			// bs-interwikilinks-label, bs-namespacemanager-label, bs-pagetemplatesadmin-label,
			// bs-permissionmanager-label, bs-preferences-label, bs-usermanager-label
			$form .= wfMsg( 'bs-' . $moduleNameLower . '-label' );
			$form .= '</div>';
			$form .= '</a>';
			$form .= '</div>';

		}
		return $form;
	}

}