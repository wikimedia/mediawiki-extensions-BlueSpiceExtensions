/**
 * Review extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.0.0 beta

 * @package    Bluespice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
* Open status bar if review is clicked.
*/

(function(mw, $, bs, undefined){
	
	//Wire up content action link
	$('#ca-review').find('a').on( 'click', function( e ) {
		e.preventDefault();
		var me = this;

		Ext.require( 'BS.Review.Dialog', function(){
			BS.Review.Dialog.clearListeners();
			BS.Review.Dialog.on( 'ok', function( btn, data ){
				window.location.reload();
			});
			
			var data = { 
				page_id: mw.config.get('wgArticleId'),
				steps: []
			};
			if( typeof bsReview != 'undefined' ) {
				data = bsReview;
			}
			data.userCanEdit = mw.config.get('bsReviewUserCanEdit');

			BS.Review.Dialog.setData( data );
			BS.Review.Dialog.show( me );
		});

		return false;
	});
	
	//Wire up accept/decline links
	$(document).on('click', 'a#bs-review-ok', function() {
		$.ajax({
			url: bs.util.getAjaxDispatcherUrl('Review::getVoteResponse'),
			data: {
				articleID: mw.config.get('wgArticleId'),
				vote: 'yes',
				comment: $('#bs-review-voteresponse-comment').val() || ''
			},
			type: "POST",
			success: function( data, textStatus, jqXHR ) {
				bs.util.alert(
					'bs-review-alert',
					{
						text: data
					},
					{
						ok: function() {
							window.location.reload();
						}
					}
				);
			}
		});
	});
	$(document).on('click', 'a#bs-review-dismiss', function() {
		$.ajax({
			url: bs.util.getAjaxDispatcherUrl('Review::getVoteResponse'),
			data: {
				articleID: mw.config.get('wgArticleId'),
				vote: 'no',
				comment: $('#bs-review-voteresponse-comment').val() || ''
			},
			type: "POST",
			success: function( data, textStatus, jqXHR ) {
				bs.util.alert(
					'bs-review-alert',
					{
						text: data
					},
					{
						ok: function() {
							window.location.reload();
						}
					}
				);
			}
		});
	});
}( mediaWiki, jQuery, blueSpice ));