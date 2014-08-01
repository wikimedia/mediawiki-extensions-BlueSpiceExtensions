<?php
class ViewWidgetList extends ViewBaseElement {
	protected $_mWidgets = array();

	public function execute( $params = false ) {

		$sEditLinkText = wfMessage('bs-widget-edit')->text();
		$oTitle = Title::makeTitle( NS_USER, RequestContext::getMain()->getUser()->getName().'/Widgetbar' );
		$sEditLink = Linker::link(
			$oTitle,
			Html::rawElement('span', array(), $sEditLinkText ),
			array(
				'id' => 'bs-widgetbar-edit',
				'class' => 'icon-pencil clearfix'
			),
			array(
				'action' => 'edit',
				'preload' => ''
			)
		);

		$aOut = array();
		$aOut[] = '<div id="bs-widget-container" >';
		$aOut[] = '  <div class="icon-plus" id="bs-widget-tab" title="' . wfMessage( 'bs-widget-container-tooltip' )->text() . '" tabindex="100">[+/-]</div>';
		$aOut[] = '  <div id="bs-flyout">';
		$aOut[] = '    <h4 id="bs-flyout-heading">' . wfMessage( 'bs-widget-flyout-heading' )->text() . '</h4>';
		$aOut[] = '    <div id="bs-flyout-content">';
		$aOut[] = '      <div id="bs-flyout-content-widgets">';
		$aOut[] = '        <h4 id="bs-flyout-content-widgets-header">'.wfMessage("bs-widget-flyout-heading")->plain().$sEditLink.'</h4>';

		foreach( $this->_mWidgets as $oWidgetView ) {
			if( $oWidgetView instanceof ViewWidget ) {
				$aOut[] = $oWidgetView->execute();
			}
			else{
				wfDebug( __METHOD__.': Invalid widget.' );
			}
		}

		$aOut[] = '      </div>';
		$aOut[] = '    </div>';
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