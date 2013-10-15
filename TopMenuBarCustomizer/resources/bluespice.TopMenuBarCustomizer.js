/**
 * Js for TopMenuBarCustomizer extension
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>

 * @package    Bluespice_Extensions
 * @subpackage TopMenuBarCustomizer
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$(document).ready(function(){
    $('.menu-item-container').hover( function(){
			$(this).siblings('ul.bs-apps-child').stop(true,true).slideDown('fast');
		},
		function(){
			$(this).siblings('ul.bs-apps-child').stop(true,true).delay(100).slideUp('fast');
		}
	)

	$('ul.bs-apps-child').hover( function(){
			$(this).stop(true,true).slideDown('fast');
		},
		function(){
			$(this).stop(true,true).delay(100).slideUp('fast');
		}
	)
});