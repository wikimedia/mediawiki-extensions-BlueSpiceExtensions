<?php
/**
 * Renders the <bs:wantedarticles /> tag.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the <bs:wantedarticles /> tag.
 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 */
class ViewWantedArticleTag extends ViewBaseElement {

	protected $mTitle       = '';
	protected $mListEntries = array();
	protected $mCount       = 5;
	protected $mType        = 'list';
	protected $mOrder       = 'ASC';
	protected $mSort        = 'time';

	public function execute( $params = false ) {
		$aOut         = array();
		$aVisibleList = array();
		$aHiddenList  = array();
		$sDataTypes = 'data-type="'.$this->mType.'"'
						.' data-count="'.$this->mCount.'"'
						.' data-order="'.$this->mOrder.'"'
						.' data-sort="'.$this->mSort.'"';

		foreach( $this->mListEntries as $oTitle ){
			$sLink = sprintf( '<a class="internal %s" href="%s" title="%s">%s</a>',
				$oTitle->exists() == false ? 'new' : '',
				$oTitle->getLocalURL(),
				$oTitle->getPrefixedText(),
				$oTitle->getPrefixedText()
			);
			if( count( $aVisibleList ) < $this->mCount ) {
				$aVisibleList[] = $sLink;
			}
			else {
				$aHiddenList[]  = $sLink;
			}
		}
		$aOut[] = '<div class="bs-wantedarticle-tag" '.$sDataTypes.'>';

		if( !empty( $this->mTitle ) ) {
			$aOut[] = '<h3>'.$this->mTitle.'</h3>';
		}

		if( $this->mType == 'queue' ) {
			$aOut[] = '<p>';
			$aOut[] = '<span class="bs-wantedarticle-suggestion">';
			$aOut[] = implode( '</span>, <span class="bs-wantedarticle-suggestion">', $aVisibleList );
			$aOut[] = '</span>';
			$aOut[] = '</p>';
		}
		else {
			$aOut[] = '<ul>';
			$aOut[] = '  <li class="bs-wantedarticle-suggestion">';
			$aOut[] = implode( '</li><li class="bs-wantedarticle-suggestion">', $aVisibleList );
			$aOut[] = '  </li>';
			$aOut[] = '</ul>';
		}
		if( !empty ( $aHiddenList ) ) {
			if( $this->mType == 'queue' ) {
				$aOut[] = sprintf( ', <a class="togglemore-queue" href="javascript:void(0);" title="%s">%s</a>',
					wfMessage( 'bs-wantedarticle-tag-more-linktext' )->text(),
					wfMessage( 'bs-wantedarticle-tag-more-linktext' )->text()
				);
				$aOut[] = '<span style="display:none">';
				$aOut[] = '<span class="bs-wantedarticle-suggestion">';
				$aOut[] = implode( '</span>, <span class="bs-wantedarticle-suggestion">', $aHiddenList );
				$aOut[] = '</span>';
				$aOut[] = '</span>';
			}
			else {
				$aOut[] = sprintf( '<a class="togglemore" href="javascript:void(0)" title="%s">%s</a>',
					wfMessage( 'bs-wantedarticle-tag-more-linktext' )->text(),
					wfMessage( 'bs-wantedarticle-tag-more-linktext' )->text()
				);
				$aOut[] = '<ul style="display:none">';
				$aOut[] = '  <li class="bs-wantedarticle-suggestion">';
				$aOut[] = implode( '</li><li class="bs-wantedarticle-suggestion">', $aHiddenList );
				$aOut[] = '  </li>';
				$aOut[] = '</ul>';
			}
		}

		$aOut[] = '</div>';
		return implode( "\n", $aOut);
	}

	public function setTitle( $sTitle ) {
		$this->mTitle = $sTitle;
		return $this;
	}

	public function setList( $aList ) {
		$this->mListEntries = $aList;
		return $this;
	}

	public function setCount( $iCount ) {
		$this->mCount = $iCount;
		return $this;
	}

	public function setType( $sType ) {
		$this->mType = $sType;
		return $this;
	}

	public function setOrder( $sOrder ) {
		$this->mOrder = $sOrder;
		return $this;
	}

	public function setSort( $sSort ) {
		$this->mSort = $sSort;
		return $this;
	}
}