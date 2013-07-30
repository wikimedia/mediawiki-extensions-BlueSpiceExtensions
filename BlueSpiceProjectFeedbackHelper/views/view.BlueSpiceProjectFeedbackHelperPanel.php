<?php
class ViewBlueSpiceProjectFeedbackHelperPanel extends ViewStateBarTopElement {

	public function execute($params=false) {
		$aOut = array();
		$aOut[] = '<div id="bs-bluespiceprojectfeedbackhelperpanel">';
		$aOut[] = ' <div id="bs-bluespiceprojectfeedbackhelperpanel-text">';
		$aOut[] = wfMsgExt('bs-bluespiceprojectfeedbackhelper-hint', array( 'parse' ) );
		$aOut[] = ' </div>';
		$aOut[] = ' <div id="bs-bluespiceprojectfeedbackhelperpanel-closebutton" title="'.wfMsg('bs-bluespiceprojectfeedbackhelper-closebutton').'" data-confirm-msg="'.wfMessage('bs-bluespiceprojectfeedbackhelper-confirm')->escaped().'"></div>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}
}
