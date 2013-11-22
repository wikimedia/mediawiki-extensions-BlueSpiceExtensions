<?php
/**
 * Notifications extension for BlueSpice
 *
 * Sends changes in the wiki via email.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Stefan Widmann <widmann@hallowelt.biz>
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage Notifications
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
BsExtensionManager::registerExtension('Notifications', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);
// Hooks
// Unfortunately i forgot, why this hook is here in the setup file, but maybe it makes sense?
$wgHooks['BSBlueSpiceSkinUserBarBeforeLogout'][] = 'Notifications::onBSBlueSpiceSkinUserBarBeforeLogout';

// MessageFiles
$wgExtensionMessagesFiles['Notifications'] = __DIR__ . '/Notifications.i18n.php';

// Autoloader
$wgAutoloadClasses['BsNotificationsFormatter'] = __DIR__.'/includes/BsNotificationsFormatter.class.php';

$wgResourceModules['ext.bluespice.notifications.icons'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/Notifications/resources/bluespice.notifications.icons.css',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);
/**
 * Setting defaults for users
 * Webprefix: echo-subscriptions-web-
 * Emailprefix: echo-subscriptions-email-
 */
// Email
$wgDefaultUserOptions['echo-subscriptions-email-edit-user-talk'] = true;
$wgDefaultUserOptions['echo-subscriptions-email-shoutbox-cat'] = true;

// Web
$wgDefaultUserOptions['echo-subscriptions-web-shoutbox-cat'] = true;

// Change initial values from Echo extension
$wgEchoNotificationCategories['edit-user-talk']['no-dismiss'] = array();

// Email
$wgDefaultUserOptions['echo-subscriptions-email-mention'] = false;

// Web
$wgDefaultUserOptions['echo-subscriptions-web-article-linked'] = true;
$wgDefaultUserOptions['echo-subscriptions-web-mention'] = true;