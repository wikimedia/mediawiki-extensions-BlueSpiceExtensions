<?php

class ViewPAStateBarBodyElement extends ViewStateBarBodyElement {

	public function execute($params = false) {

		$this->sBodyText = '<ul>';
		foreach ( $this->_mData as $oAssignee ) {
			if( $oAssignee instanceof BSAssignableBase );
			$this->sBodyText .= '<li><span class="bs-icon-'.$oAssignee->toStdClass()->type.' bs-typeicon"></span>'.$oAssignee->toStdClass()->anchor.'</li>';
		}
		$this->sBodyText .= '</ul>';
		return parent::execute($params);
	}
}