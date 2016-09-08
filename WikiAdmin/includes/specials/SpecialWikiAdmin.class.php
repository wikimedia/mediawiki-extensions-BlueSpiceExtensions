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

		$output = $this->getForm();
		# Output
		$oOut->addHTML( '<div class="bs-admincontrolbtns clearfix">'.$output.'</div>' );
	}

	public function getForm() {
		global $wgScriptPath;
		$form = '';
		$registeredModules = WikiAdmin::getRegisteredModules();
		foreach ( $registeredModules as $module => $params ) {
			$title = SpecialPage::getTitleFor( $module );
			$action = "";
			if( !$title->isKnown() || isset( $params['compatibility_mode'] ) ){
				$title = SpecialPage::getTitleFor( 'WikiAdmin' );
				$action = 'mode=' . $module;
			}
			$url = $title->getLocalURL( $action );

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

	protected function getGroupName() {
		return 'bluespice';
	}
}