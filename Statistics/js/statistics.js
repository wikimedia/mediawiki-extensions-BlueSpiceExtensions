/**
 * Statistics extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.0 beta
 * @version    $Id$
 * @package    Bluespice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
var stat_cal_count = 0;
self.hw_show_stat_cal = function()
{
    if (stat_cal_count > 100) return;
    el = document.getElementById('hwpFrom');
    if (!el) {	stat_cal_count++; setTimeout("hw_show_stat_cal()", 100); return; }

    var startdate = new Ext.form.DateField({
		applyTo:'hwpFrom',
		format:'m/d/Y'
	});
	var enddate = new Ext.form.DateField({
		applyTo:'hwpTo',
		format:'m/d/Y'
	});
}
hw_show_stat_cal();