<?php
/**
 * ownCloud - filesmv
 *
 * This file is licensed under the General Public License version 2 or
 * later. See the COPYING file.
 *
 * @author eotryx <mhfiedler@gmx.de>
 * @copyright eotryx 2015
 */

namespace OCA\Files_Mv\AppInfo;

\OCP\Util::addScript( 'files_mv', "move" );
\OCP\Util::addStyle('files_mv', 'mv');

/*
\OCP\Util::addScript("3rdparty", "chosen/chosen.jquery.min");
\OCP\Util::addStyle('3rdparty', 'chosen/chosen');
 */

/*
\OCP\App::addNavigationEntry([
	// the string under which your app will be referenced in owncloud
	'id' => 'filesmv',

	// sorting weight for the navigation. The higher the number, the higher
	// will it be listed in the navigation
	'order' => 10,

	// the route that will be shown on startup
	'href' => \OCP\Util::linkToRoute('filesmv.page.index'),

	// the icon that will be shown in the navigation
	// this file needs to exist in img/
	'icon' => \OCP\Util::imagePath('filesmv', 'app.svg'),

	// the title of your application. This will be used in the
	// navigation or on the settings page of your app
	'name' => \OC_L10N::get('filesmv')->t('Files Mv')
]);
 */
