<?php

class BsResponsibleEditor {
	protected $mUser               = null;
	protected $mAssignments        = array();
	protected $mAssignmentsLoaded  = false;
	protected $mAssignedTitles     = null;

	/**
	 * Internal contructor method. Use static factory methods for instantiation.
	 * @param User $oUser MediaWiki User object
	 */
	private function __construct( $oUser ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->mUser = $oUser;
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	private function loadAssignments() {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'bs_responsible_editors',
			array( 're_page_id', 're_position' ),
			array( 're_user_id' => $this->mUser->getId() )
			);

		foreach( $res as $row ) {
			$this->mAssignments[ $row->re_page_id ] = $row->re_position;
		}

		$this->mAssignmentsLoaded = true;
	}

	/**
	 * Fetch all Titles for which the user is responsible.
	 * @param Integer $iPosition Filter for grade of assignement. NOT YET IMPLEMENTED!
	 * @param Boolean $bForceDatabaseQuery Disable chaching mechanism.
	 * @return SplObjectStorage Contains all MediaWiki Title objects the user is responsible for.
	 */
	public function getAssignedTitles( $iPosition = -1, $bForceDatabaseQuery = false ){
		if( $this->mAssignedTitles !== null && $bForceDatabaseQuery === false ){
			return $this->mAssignedTitles;
		}
		$this->mAssignedTitles     = new SplObjectStorage();
		if( $this->mAssignmentsLoaded === false ) $this->loadAssignments();

		foreach ( $this->mAssignments as $iAssignedArticleId => $iAssignmentPosition ) {
			$this->mAssignedTitles->attach( Title::newFromID( $iAssignedArticleId ) );
		}

		return $this->mAssignedTitles;
	}

	/**
	 * Check wether or not the User is responsible for an article.
	 * @param Integer $iArticleId
	 * @return Boolean true if the user is responsible for the article with the provided id, false if he is not.
	 */
	public function isAssignedToArticleId( $iArticleId ) {
		if( $this->mAssignmentsLoaded === false ) $this->loadAssignments();

		if( array_key_exists( $iArticleId, $this->mAssignments ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check wether or not the User is responsible for an article. Shortcut to BsResponsibleEditor::isAssignedToArticleId()
	 * @param Title $oTitle
	 * @return Boolean true if the user is responsible for the provided article, false if he is not.
	 */
	public function isAssignedToTitle( $oTitle ) {
		return $this->isAssignedToArticleId( $oTitle->getArticleID() );
	}

	/**
	 *
	 * @param Integer $iArticleId
	 * @return Integer The position number or -1 if the User is not responsible for the provided article id.
	 */
	public function getPositionForArticleId( $iArticleId ) {
		if( $this->mAssignmentsLoaded === false ) $this->loadAssignments();

		if( !isset( $this->mAssignments[ $iArticleId ] ) ) return -1;
		return $this->mAssignments[ $iArticleId ];
	}

	/**
	 * Shortcut to BsResponsibleEditor::getPositionForTitle()
	 * @param Title $oTitle
	 * @return Integer The position number or -1 if the User is not responsible for the provided article id.
	 */
	public function getPositionForTitle( $oTitle ) {
		return $this->getPositionForArticleId( $oTitle->getArticleID() );
	}

	/**
	 *
	 * @param User $oUser
	 * @return ResponsibleEditor
	 */
	public static function newFromUser( $oUser ) {
		return new BsResponsibleEditor( $oUser );
	}

	/**
	 *
	 * @param Integer $iUserId
	 * @return ResponsibleEditor
	 */
	public static function newFromUserId( $iUserId ) {
		return self::newFromUser( User::newFromId( $iUserId ) );
	}


	/**
	 *
	 * @param Integer $iArticleId
	 * @return SplObjectStorage The collection of ResponsibleUser's wor the provieded article id.
	 */
	public static function getResponsibleEditorsForArticleId( $iArticleId ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'bs_responsible_editors',
			're_user_id',
			array( 're_page_id' => $iArticleId )
			);

		$oResponsbileEditorCollection = new SplObjectStorage();
		foreach( $res as $row ) {
			$oResponsbileEditorCollection->attach( self::newFromUserId( $row->re_user_id) );
		}

		return $oResponsbileEditorCollection;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return SplObjectStorage The collection of ResponsibleUser's for the provided article.
	 */
	public static function getResponsibleEditorsForTitle( Title $oTitle ) {
		return self::getResponsibleEditorsForArticleId( $oTitle->getArticleID() );
	}
}