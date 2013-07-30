<?php
class ViewWidgetList extends ViewBaseElement {
	protected $_mWidgets = array();

	public function execute( $params = false ) {

		$aOut = array();
		$aOut[] = '<div id="bs-widget-container" >';
		$aOut[] = '  <div id="bs-widget-tab" title="' . wfMsg( 'bs-widget-container-tooltip' ) . '" tabindex="100"></div>';
		$aOut[] = '  <div id="bs-flyout">';
		$aOut[] = '    <div id="bs-flyout-top"></div>';
		$aOut[] = '    <h4 id="bs-flyout-heading">' . wfMsg( 'bs-widget-flyout-heading' ) . '</h4>';
		$aOut[] = '    <div id="bs-flyout-content">';

		foreach( $this->_mWidgets as $oWidgetView ) {
			if( $oWidgetView instanceof ViewWidget ) {
				$aOut[] = $oWidgetView->execute();
			}
			else{
				wfDebugLog( 'BS::Core', 'ViewWidgetList::execute: Invalid widget.' );
			}
		}

		$aOut[] = '    </div>';
		$aOut[] = '    <div id="bs-flyout-bottom"></div>';
		$aOut[] = '  </div>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

	public function addWidget( ViewBaseElement $oWidgetView ) {
		$this->_mWidgets[] = $oWidgetView;
		return $this;
	}

	/**
	 *
	 * @param array $aWidgets
	 * @return ViewWidgetList
	 */
	public function setWidgets( $aWidgets ) {
		$this->_mWidgets = $aWidgets;
		return $this;
	}
}