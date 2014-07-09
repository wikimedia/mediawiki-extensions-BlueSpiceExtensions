<?php
class ViewBlueSpiceProjectFeedbackHelperPanel extends ViewStateBarTopElement {

	public function execute( $params = false ) {
		$aOut = array();
		$aOut[] = '<div id="bs-bluespiceprojectfeedbackhelperpanel">';
		$aOut[] = ' <div id="bs-bluespiceprojectfeedbackhelperpanel-text">';
		$aOut[] = wfMessage( 'bs-bluespiceprojectfeedbackhelper-hint' )->parse();
		$aOut[] = ' </div>';
		$aOut[] = ' <div id="bs-bluespiceprojectfeedbackhelperpanel-closebutton" title="'
					. wfMessage('bs-bluespiceprojectfeedbackhelper-closebutton')->plain() . '" data-confirm-msg="'
					. wfMessage('bs-bluespiceprojectfeedbackhelper-confirm')->escaped() . '"></div>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

}
