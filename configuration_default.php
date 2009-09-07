<?php
/**
 * This file contains a base configuration for the Cache Cleaner
 * It will be loaded if no configuration is found in $TYPO3_CONF_VARS
 * It can also be used as an example for one's own configuration
 *
 * @author		Francois Suter <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_cachecleaner
 *
 *  $Id$
 */
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cachecleaner'] = array(
	'tables' => array(
		'cache_pages' => array(
			'expireField' => 'expires'
		),
		'cache_hash' => array(
			'dateField' => 'tstamp',
			'expirePeriod' => '7d'
		)
	)
);
?>
