<?php
/**
 * Abstract index builder for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Abstract index builder for ExtendedSearch for MediaWiki
 */
abstract class AbstractBuildIndexMwLinked extends AbstractBuildIndexLinked {

	/**
	 * Checks system settings before indexing.
	 * @param array &$aErrorMessageKeys with keys to be filled as keys of error messages
	 * @return bool true if system's settings are ok to run with
	 */
	public static function areYouAbleToRunWithSystemSettings( &$aErrorMessageKeys = array() ) {
		global $wgUrlProtocols;
		$urlProtocols = $wgUrlProtocols;
		$bUrlProtocolFileActivated = false;
		foreach ( $urlProtocols as $urlProtocol ) {
			if ( strpos( $urlProtocol, 'file:' ) === 0 ) {
				$bUrlProtocolFileActivated = true;
			}
		}
		if ( !$bUrlProtocolFileActivated ) {
			$aErrorMessageKeys['bs-extendedsearch-file-protocol-not-activated'] = true;
		}
		return parent::areYouAbleToRunWithSystemSettings( $aErrorMessageKeys );
	}

	/**
	 * Contains current db prefix
	 * @var string Db prefix
	 */
	protected $sDbPrefix = '';
	/**
	 * Files in this path are not to be indexed.
	 * @var string Path to be filtered
	 */
	// CR MRG (25.06.11 16:11): Sollte das nicht ein array werden?
	protected $sLinkedPathFilter = null;

	/**
	 * Setter for db prefix
	 * @param string $sDbPrefix Database prefix.
	 * @return AbstractBuildIndexMwLinked Return self for method chaining.
	 */
	public function setDbPrefix( $sDbPrefix ) {
		$this->sDbPrefix = $sDbPrefix;
		return $this;
	}

	/**
	 * Compares two timestamps
	 * @param string $timestamp1 MW timestamp
	 * @param string $timestamp2 MW timestamp
	 * @return bool True if timestamp1 is younger than timestamp2
	 */
	public function isTimestamp1YoungerThanTimestamp2( $iTimestamp1, $iTimestamp2 ) {
		$iTs_unix1 = wfTimestamp( TS_UNIX, $iTimestamp1 );
		$iTs_unix2 = wfTimestamp( TS_UNIX, $iTimestamp2 );
		return ( $iTs_unix1 > $iTs_unix2 );
	}

	/**
	 * Setter for sLinkedPathFilter
	 * @param string $sLinkedPathFilter Files in this path are not to be indexed.
	 * @return AbstractBuildIndexMwLinked Return self for method chaining.
	 */
	public function setLinkedPathFilter( $sLinkedPathFilter ) {
		$this->sLinkedPathFilter = $sLinkedPathFilter;
		return $this;
	}

	/**
	 * Matches $possibleMatch against sLinkedPathFilter
	 * @param string $possibleMatch String to match.
	 * @return int Number of matches
	 */
	public function doesLinkedPathFilterMatch( $possibleMatch ) {
		if ( $this->sLinkedPathFilter === null ) return false;
		return ( preg_match( $this->sLinkedPathFilter, $possibleMatch ) );
	}

}