<?php
class ViewBlueSpiceProjectFeedbackHelperPanel extends ViewStateBarTopElement {

	public function execute( $params = false ) {
		$sOut = '';
		$sOut .= Xml::openElement( 'div' , array(
			'id' => 'bs-bluespiceprojectfeedbackhelperpanel'
		));

		$sOut .= Xml::openElement(
			'div' ,
			array( 'id' => 'bs-bluespiceprojectfeedbackhelperpanel-text' )
		);
		$sOut .= wfMessage( 'bs-bluespiceprojectfeedbackhelper-hint' )->parse();
		$sOut .= Xml::closeElement( 'div' );

		$oCloseMsg = wfMessage('bs-bluespiceprojectfeedbackhelper-closebutton');
		$oConfirmMsg = wfMessage('bs-bluespiceprojectfeedbackhelper-confirm');
		$sOut .= Xml::openElement( 'div', array(
			'id' => 'bs-bluespiceprojectfeedbackhelperpanel-closebutton',
			'title' => $oCloseMsg->plain(),
			'data-confirm-msg' => $oConfirmMsg->escaped(),
		));
		$sOut .= Xml::closeElement( 'div' );

		$sOut .= Xml::closeElement( 'div' );

		return $sOut;
	}
}