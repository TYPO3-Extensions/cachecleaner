<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2009 Francois Suter (typo3@cobweb.ch)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('cachecleaner', 'class.tx_cachecleaner.php'));

/** 
 * This class provides the functionality for the tx_cachecleaner_cache module of the lowlevel_cleaner
 *
 * @author		Francois Suter <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_cachecleaner
 *
 *  $Id$
 */
class tx_cachecleaner_lowlevel extends tx_lowlevel_cleaner_core {
	protected $extKey = 'cachecleaner';	// The extension key
	protected $extConf = array(); // The extension configuration

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::tx_lowlevel_cleaner_core();

			// If no cleaning configuration exists, load the default one
			// TODO: remove this when finished testing
		if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['tables'])) {
			require_once(t3lib_extMgm::extPath($this->extKey, 'configuration_default.php'));
		}
		$this->cleanerConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['tables'];

			// Load the language file and set base messages for the lowlevel interface
		$GLOBALS['LANG']->includeLLFile('EXT:' . $this->extKey . '/locallang.xml');
		$this->cli_help['name'] = $GLOBALS['LANG']->getLL('name');
		$this->cli_help['description'] = trim($GLOBALS['LANG']->getLL('description'));
		$this->cli_help['author'] = 'Francois Suter, (c) 2009';
		$this->cli_options[] = array('--optimize', $GLOBALS['LANG']->getLL('options.optimize'));
	}

	/**
	 * This method is called by the lowlevel_cleaner script when running without the AUTOFIX option
	 * It just returns a preview of could happen if the script was run for real
	 *
	 * @return	array	Result structure, as expected by the lowlevel_cleaner
	 * @see tx_lowlevel_cleaner_core::cli_main()
	 */
	public function main() {
			// Initialize result array
		$resultArray = array(
			'message' => $this->cli_help['name'] . chr(10) . chr(10) . $this->cli_help['description'],
			'headers' => array(
				'RECORDS_TO_CLEAN' => array(
					$GLOBALS['LANG']->getLL('cleantest.header'), $GLOBALS['LANG']->getLL('cleantest.description'), 1
				)
			),
			'RECORDS_TO_CLEAN' => array()
		);

			// Loop on all configured tables
		foreach ($this->cleanerConfiguration as $table => $tableConfiguration) {
				// Handle tables that have an explicit expiry field
			if (isset($tableConfiguration['expireField'])) {
				$field = $tableConfiguration['expireField'];
				$dateLimit = $GLOBALS['EXEC_TIME'];

				// Handle tables with a date field and a lifetime
			} elseif (isset($tableConfiguration['dateField'])) {
				$field = $tableConfiguration['dateField'];
				$dateLimit = $GLOBALS['EXEC_TIME'] - (7 * 86400);
			}
				// Perform the actual query and write down the results
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*) AS total', $table, $field . " <= '" . $dateLimit . "'");
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$resultArray['RECORDS_TO_CLEAN'][] = sprintf($GLOBALS['LANG']->getLL('recordsToDelete'), $table, $row[0]);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $resultArray;
	}

	/**
	 * This method is called by the lowlevel_cleaner script when running *with* the AUTOFIX option
	 * 
	 * @return	void
	 * @see tx_lowlevel_cleaner_core::cli_main()
	 */
	public function main_autofix() {
			// Loop on all configured tables
		foreach ($this->cleanerConfiguration as $table => $tableConfiguration) {
			echo 'Cleaning old records for table "' . $table . '":' . chr(10);
			if (($bypass = $this->cli_noExecutionCheck($table))) {
				echo $bypass;
			} else {
				echo 'DONE';
			}
			echo chr(10);
		}
	}
}
?>