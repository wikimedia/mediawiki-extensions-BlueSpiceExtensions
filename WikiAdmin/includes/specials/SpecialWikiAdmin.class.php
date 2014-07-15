<?php
class SpecialWikiAdmin extends BsSpecialPage {

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'WikiAdmin', 'wikiadmin' ); // SpecialPage($name, $restriction)
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function execute( $par ) {
		parent::execute( $par );
		$oOut = $this->getOutput();
		$output = '';

		# Get request data from, e.g.
		$wa_mode = $this->getRequest()->getVal( 'mode', 'WikiAdmin' );
		$wa_mode_Instance = null;
		$wa_mode_Credits = null;

		$runningModules = WikiAdmin::getRunningModules();
		foreach ( $runningModules as $moduleName => $moduleInstance ) {
			if ( strcasecmp( $wa_mode, $moduleName ) == 0 ) {
				$wa_mode_Credits = WikiAdmin::getRegisteredModule( $moduleName );
				if ( !$this->getUser()->isAllowed( $wa_mode_Credits['level'] ) ) {
					$output = wfMessage( 'bs-wikiadmin-notallowed' )->plain();
					$oOut->addHTML( $output );
					return;
				}
				$wa_mode = $moduleName;
				$wa_mode_Instance = $moduleInstance;

				$oOut->setPagetitle( 'WikiAdmin - ' . wfMessage( $wa_mode_Credits['message'] )->plain() );
			}
		}

		if ( $wa_mode_Instance != null ) {
			$output .= $wa_mode_Instance->getForm();
		} else {
			$output = $this->getForm();
		}
		# Output
		$oOut->addHTML( '<div class="bs-admincontrolbtns clearfix">'.$output.'</div>' );
	}

	public function getForm() {
		global $wgScriptPath;
		$form = '';
		$registeredModules = WikiAdmin::getRegisteredModules();
		foreach ( $registeredModules as $module => $params ) {
			$title = SpecialPage::getTitleFor( 'WikiAdmin' );
			$url = $title->getLocalURL( 'mode='.$module );
			// TODO SU (04.07.11 10:56): Gehört eigentlich in eine view.
			$form .= '<div class="bs-admincontrolbtn">';
			$form .= '<a href="'.$url.'">';
			$form .= '<img src="'.$wgScriptPath.$params['image'].'" alt="'.$module.'" title="'.$module.'">';
			$form .= '<div class="bs-admin-label">';
			$form .= wfMessage( $params['message'] )->plain();
			$form .= '</div>';
			$form .= '</a>';
			$form .= '</div>';
		}

		return $form;
	}

}