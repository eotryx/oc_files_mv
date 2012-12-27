<?php
/**
*  file mover for owncloud 4
*/

OCP\Util::addScript( 'files_mv', "move" );
OCP\Util::addStyle('files_mv', 'mv');

OCP\Util::addScript("3rdparty", "chosen/chosen.jquery.min");
OCP\Util::addStyle('3rdparty', 'chosen/chosen');

/**
* this is from owncloud 3
*/
/*
OC_Util::addScript('files_mv','move');
OC_Util::addStyle('files_mv','mv');
OC_Util::addScript('3rdparty','chosen/chosen.jquery.min');
OC_Util::addStyle('3rdparty','chosen/chosen');
OC_APP::register(array(
	'order' => 90,
	'id' => 'files_mv',
	'name' => 'File move'
	));

*/
?>
