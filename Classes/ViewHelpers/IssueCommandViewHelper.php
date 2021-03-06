<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Felix Kopp <felix-source@phorax.com>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Issue command ViewHelper, see TYPO3 Core Engine method issueCommand
 *
 * @author Felix Kopp <felix-source@phorax.com>
 * @package TYPO3
 * @subpackage t3extblog
 */
class Tx_T3extblog_ViewHelpers_IssueCommandViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Returns a URL with a command to TYPO3 Core Engine (tce_db.php)
	 *
	 * @param string $parameters Is a set of GET params to send to tce_db.php. Example: "&cmd[tt_content][123][move]=456" or "&data[tt_content][123][hidden]=1&data[tt_content][123][title]=Hello%20World"
	 * @param string $redirectUrl Redirect URL if any other that t3lib_div::getIndpEnv('REQUEST_URI') is wished
	 *
	 * @return string URL to tce_db.php + parameters
	 * @see t3lib_BEfunc::editOnClick()
	 * @see template::issueCommand()
	 */
	public function render($parameters, $redirectUrl = '') {
		$redirectUrl = $redirectUrl ? $redirectUrl : t3lib_div::getIndpEnv('REQUEST_URI');

		return
			$GLOBALS['BACK_PATH'] .
			'tce_db.php?' . $parameters .
			'&vC=' . rawurlencode($GLOBALS['BE_USER']->veriCode()) .
			t3lib_BEfunc::getUrlToken('tceAction') .
			'&prErr=1&uPT=1' .
			'&redirect=' . ($redirectUrl == '' ? "' + T3_THIS_LOCATION + '" : rawurlencode($redirectUrl));
	}
}

?>