<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2014 Felix Nagel <info@felixnagel.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * Class being included by TCEmain using a hook
 *
 * @package t3extblog
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_T3extblog_Hooks_Tcemain {

	/**
	 * Fields to check for changes
	 *
	 * @var array
	 */
	var $watchedFields = array(
		'approved',
		'spam'
	);

	/**
	 * notificationService
	 *
	 * @var Tx_T3extblog_Service_NotificationService
	 */
	protected $notificationService = NULL;

	/**
	 * objectContainer
	 *
	 * @var Tx_Extbase_Object_Container_Container
	 */
	protected $objectContainer = NULL;

	/**
	 * Hook: processDatamap_afterDatabaseOperations
	 *
	 * Note: When using the hook after INSERT operations, you will only get the temporary NEW... id passed to your hook as $id,
	 *         but you can easily translate it to the real uid of the inserted record using the $this->substNEWwithIDs array.
	 *
	 * @param    string $status : (reference) Status of the current operation, 'new' or 'update'
	 * @param    string $table : (refrence) The table currently processing data for
	 * @param    string $id : (reference) The record uid currently processing data for, [integer] or [string] (like 'NEW...')
	 * @param    array  $fields : (reference) The field array of a record	 *
	 * @param    t3lib_TCEmain $tce
	 *
	 * @return    void
	 */
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fields, $tceMain) {
		if (!is_numeric($id)) {
			$id = $tceMain->substNEWwithIDs[$id];
		}

		if ($table == 'tx_t3blog_post') {
			if (isset($GLOBALS['_POST']['_savedokview_x'])) {
				$pagesTsConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['_POST']['popViewId']);

				if ($pagesTsConfig['tx_t3extblog.']['singlePid']) {
					$record = t3lib_BEfunc::getRecord('tx_t3blog_post', $id);

					$parameters = array(
//						'tx_t3extblog_blogsystem[controller]' => 'Post',
						'tx_t3extblog_blogsystem[action]' => 'preview',
						'tx_t3extblog_blogsystem[previewPost]' => $record['uid'],
					);
					if ($record['sys_language_uid'] > 0) {
						if ($record['l10n_parent'] > 0) {
							$parameters['tx_t3extblog_blogsystem[previewPost]'] = $record['l10n_parent'];
						}
						$parameters['L'] = $record['sys_language_uid'];
					}

					$GLOBALS['_POST']['popViewId_addParams'] = t3lib_div::implodeArrayForUrl('', $parameters, '', FALSE, TRUE);
					$GLOBALS['_POST']['popViewId'] = $pagesTsConfig['tx_t3extblog.']['singlePid'];
				}
			}
		}

		if ($table == 'tx_t3blog_com') {
			if ($status == 'update') {
				// get history Record
				$hr = $tceMain->historyRecords[$table . ':' . $id]['newRecord'];

				if (is_array($hr) === TRUE) {
					$changedfields = array_keys($hr);

					if (is_array($changedfields) === TRUE) {
						$updatefields = array_intersect($changedfields, $this->watchedFields);

						if (is_array($updatefields) === TRUE && count($updatefields) > 0) {
							// extbase fix
							$this->getObjectContainer()
								->getInstance("Tx_T3extblog_Service_SettingsService")
								->setPageUid($tceMain->checkValue_currentRecord['pid']);

							$this->getNotificationService()->processCommentStatusChanged($this->getComment($id));
						}
					}
				}
			}

			if ($status == 'new') {
				// extbase fix
				$this->getObjectContainer()
					->getInstance("Tx_T3extblog_Service_SettingsService")
					->setPageUid($fields['pid']);

				$this->getNotificationService()->processCommentAdded($this->getComment($id), FALSE);
			}
		}
	}

	/**
	 * Get comment
	 *
	 * @param integer $uid Page uid
	 *
	 * @return Tx_T3extblog_Domain_Model_Comment
	 */
	protected function getComment($uid) {
		$commentRepository = $this->getObjectContainer()->getInstance("Tx_T3extblog_Domain_Repository_CommentRepository");
		$comment = $commentRepository->findByUid($uid);

		return $comment;
	}

	/**
	 * Get object container
	 *
	 * @return Tx_Extbase_Object_Container_Container
	 */
	protected function getObjectContainer() {
		if ($this->objectContainer == NULL) {
			$this->objectContainer = t3lib_div::makeInstance("Tx_Extbase_Object_Container_Container");
		}

		return $this->objectContainer;
	}

	/**
	 * Get notification service
	 *
	 * @return Tx_T3extblog_Service_NotificationService
	 */
	protected function getNotificationService() {
		if ($this->notificationService == NULL) {
			$this->notificationService = $this->getObjectContainer()->getInstance("Tx_T3extblog_Service_NotificationService");
		}

		return $this->notificationService;
	}

}

?>