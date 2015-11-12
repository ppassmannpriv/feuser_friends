<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Juergen Furrer <juergen.furrer@gmail.com>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(t3lib_extMgm::extPath('feuser_friends').'lib/class.tx_feuserfriends_pagerenderer.php');

/**
 * Plugin 'Frontend-user friends' for the 'feuser_friends' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_feuserfriends
 */
class tx_feuserfriends_pi1 extends tslib_pibase
{
	var $prefixId      = 'tx_feuserfriends_pi1';
	var $scriptRelPath = 'pi1/class.tx_feuserfriends_pi1.php';
	var $extKey        = 'feuser_friends';
	var $pi_checkCHash = true;
	var $detailId      = null;
	var $templateFile  = null;
	var $templateFileJS = null;
	var $templatePart = null;
	var $contentKey = null;
	var $jsFiles = array();
	var $js = array();
	var $cssFiles = array();
	var $css = array();
	var $feuserId      = null;
	var $friends_array = null;
	var $piFlexForm = array();
	var $img_fields = array();
	var $url_fields = array();
	var $rte_fields = array();

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)
	{
		$this->conf = $conf;

		$this->pagerenderer = t3lib_div::makeInstance('tx_feuserfriends_pagerenderer');
		$this->pagerenderer->setConf($this->conf);

		$this->pi_USER_INT_obj = 1;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		if (! is_numeric($this->conf['ajaxTypeNum'])) {
			$this->conf['ajaxTypeNum'] = 500;
		}
		$this->internal['currentTable'] = 'fe_users';
		$GLOBALS["TSFE"]->set_no_cache();
		//
		$this->img_fields = t3lib_div::trimExplode(',', $this->conf['imageFields'], TRUE);
		$this->url_fields = t3lib_div::trimExplode(',', $this->conf['linkFields'], TRUE);
		$this->rte_fields = t3lib_div::trimExplode(',', $this->conf['rteFields'], TRUE);
		// define the id for detaile
		$this->detailId = (is_numeric($this->conf['detailId']) ? $this->conf['detailId'] : $GLOBALS['TSFE']->id);
		// define the loggedin feuser
		$this->feuserId = $GLOBALS['TSFE']->fe_user->user['uid'];
		// The template
		if ($this->conf['templateFile']) {
			$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		} else {
			return '<p>NO TEMPLATE FOUND!</p>';
		}
		// prepare for handling by code in class piBase
		$this->conf['pidList'] = $this->cObj->stdWrap($this->conf['pidList'], $this->conf['pidList.']);
		unset($this->conf['pidList.']);
		$this->conf['recursive'] = $this->cObj->stdWrap($this->conf['recursive'], $this->conf['recursive.']);
		unset($this->conf['recursive.']);
		// look for the view type
		if ($this->cObj->data['tx_feuserfriends_view'] == 1) {
			// detail view
			$content = $this->singleView();
		} elseif ($this->cObj->data['tx_feuserfriends_view'] == 2) {
			// friends request view
			$content = $this->friendsRequestView();
		} elseif ($this->cObj->data['tx_feuserfriends_view'] == 3) {
			// friends view (list)
			$content = $this->listView(true);
		} elseif ($this->cObj->data['tx_feuserfriends_view'] == 4) {
			// message view (list)
			$content = $this->messageView();
		} else {
			if ($this->conf['ajax']) {
				// AJAX
				if ($this->piVars['addfriendrequest']) {
					return $this->addfriendrequestView(
						$this->piVars['addfriendrequest'],
						$this->piVars['send']
					);
				}
				if ($this->piVars['removefriend']) {
					return $this->removeFriendView(
						$this->piVars['removefriend'],
						$this->piVars['send']
					);
				}
				if ($this->piVars['showfriendrequest']) {
					return $this->showfriendrequestView($this->piVars['showfriendrequest']);
				}
				if ($this->piVars['acceptfriend']) {
					return $this->acceptFriend($this->piVars['acceptfriend']);
				}
				if ($this->piVars['rejectfriend']) {
					return $this->rejectFriend($this->piVars['rejectfriend']);
				}
				if ($this->piVars['sendmessage']) {
					return $this->sendMessageView(
						$this->piVars['sendmessage'],
						$this->piVars['send']
					);
				}
			} else {
				// Fallback View
				if ($this->piVars['showUid']) {
					$content = $this->singleView();
				} else {
					$content = $this->listView(false);
				}
			}
		}
		$this->pi_addJS();
		$this->pagerenderer->addCssFile($this->conf['jQueryUIstyle']);

		// checks if t3jquery is loaded
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->conf['jQueryLibrary'], true);
			$this->pagerenderer->addJsFile($this->conf['jQueryEasing']);
			$this->pagerenderer->addJsFile($this->conf['jQueryUI']);
		}

		$this->pagerenderer->addResources();

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * AJAX
	 */

	/**
	 * Shows the dialog to add user as friend
	 * @param $uID
	 * @param $send
	 * @return string
	 */
	function addfriendrequestView($uID, $send=0)
	{
		$this->mode = 'smallView.';
		// get the user
		$this->internal['currentRow'] = $this->pi_getRecord('fe_users', $uID);
		if ($send == 1) {
			// Insert the new request
			if (! $this->conf['pidFriends']) {
				// missconfiguration!
				return "<p>pidFriends not configured</p>";
			}
			if (in_array($uID, $this->getFriendsId())) {
				// allready friends
				$content = sprintf($this->pi_getLL('friends_add_message_already'), $this->internal['currentRow']['name']);
				$wrap = $this->conf['messageWrapError'];
			} else {
				// write new record
				$now = time();
				$friendsRec = array();
				$friendsRec['pid']        = intval($this->conf['pidFriends']);
				$friendsRec['crdate']     = $now;
				$friendsRec['tstamp']     = $now;
				$friendsRec['user_from']  = intval($this->feuserId);
				$friendsRec['user_to']    = intval($uID);
				$friendsRec['invitation'] = $this->piVars['invitation'];
				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_feuserfriends_friends',
					$friendsRec
				);
				if ($res) {
					$content = sprintf($this->pi_getLL('friends_add_message_ok'), $this->internal['currentRow']['name']);
					$wrap = $this->conf['messageWrapOk'];
				} else {
					$content = sprintf($this->pi_getLL('friends_add_message_error'), $this->internal['currentRow']['name']);
					$wrap = $this->conf['messageWrapError'];
				}
			}
			return $this->cObj->wrap($content, $wrap);
		} else {
			$tplCode = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_ADD_FRIEND_DIALOG###');
			$markerArray['UID']   = $uID;
			$markerArray['NAME']  = $this->internal['currentRow']['name'];
			$markerArray['IMAGE'] = $this->getFieldContent('image');
			return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
		}
	}

	/**
	 * Shows the dialog to remove a friend
	 * @param $uID
	 * @param $send
	 * @return string
	 */
	function removeFriendView($user_id, $send=0)
	{
		$this->mode = 'smallView.';
		// get the user
		$this->internal['currentRow'] = $this->pi_getRecord('fe_users', $user_id);
		if ($send == 1) {
			if ($this->removeFriend($user_id)) {
				$content = sprintf($this->pi_getLL('friend_remove_message_ok'), $this->internal['currentRow']['name']);
				$wrap = $this->conf['messageWrapOk'];
			} else {
				$content = sprintf($this->pi_getLL('friend_remove_message_error'), $this->internal['currentRow']['name']);
				$wrap = $this->conf['messageWrapError'];
			}
			return $this->cObj->wrap($content, $wrap);
		} else {
			$tplCode = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_REMOVE_FRIEND_DIALOG###');
			$markerArray['MESSAGE']  = "Bist du dir wirklich sicher?";
			$markerArray['NAME']     = $this->internal['currentRow']['name'];
			$markerArray['COMMENTS'] = $this->internal['currentRow']['comments'];
			$markerArray['IMAGE']    = $this->getFieldContent('image');
			return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
		}
	}

	/**
	 * Returns all friends uid as array
	 * @return array
	 */
	function getFriendsId()
	{
		$return_array = array();
		if (is_numeric($this->feuserId)) {
			if ($this->friends_array != null) {
				// the friends_array will be returned if its set
				return $this->friends_array;
			}
			$query = "
			SELECT DISTINCT IF(user_from='{$this->feuserId}', user_to, user_from) AS friend
			FROM tx_feuserfriends_friends
			WHERE
			(
				user_from='{$this->feuserId}' OR
				user_to='{$this->feuserId}'
			)
			AND
			(
				accept=1 OR
				(
					accept=0 AND
					user_from='{$this->feuserId}'
				)
			)
			AND deleted=0
			AND hidden=0";
			$res = $GLOBALS['TYPO3_DB']->sql_query(TYPO3_db, $query);
			if ($res) {
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$return_array[] = $row['friend'];
				}
			}
		}
		$this->friends_array = $return_array;
		return $return_array;
	}

	/**
	 *
	 * @param $uID
	 * @return boolean
	 */
	function showfriendrequestView($uID)
	{
		$this->mode = 'showfriendrequestView.';
		$user_request = $this->pi_getRecord('tx_feuserfriends_friends', $uID);
		$this->internal['currentRow'] = $this->pi_getRecord(
			'fe_users',
			$user_request['user_from']
		);
		$tplCode = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_FRIENDS_REQUEST###');
		$markerArray['TITLE']      = $this->internal['currentRow']['name'];
		$markerArray['INVITATION'] = nl2br($user_request['invitation']);
		$markerArray['IMAGE']      = $this->getFieldContent('image');

		return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
	}

	/**
	 *
	 * @param $uID
	 * @return boolean
	 */
	function acceptFriend($uID)
	{
		if (is_numeric($uID)) {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_feuserfriends_friends',
				"uid={$uID} AND user_to='{$this->feuserId}'",
				array(
					'deleted' => 0,
					'accept' => 1,
				)
			);
			if ($res) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * @param $uID
	 * @return boolean
	 */
	function rejectFriend($uID)
	{
		if (is_numeric($uID)) {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_feuserfriends_friends',
				"uid={$uID} AND user_to='{$this->feuserId}'",
				array(
					'deleted' => 1,
					'accept' => 0
				)
			);
			if ($res) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Remove a feuser from friendlist
	 * @param $user_id
	 * @return boolean
	 */
	function removeFriend($user_id)
	{
		if (is_numeric($user_id)) {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_feuserfriends_friends',
				"(user_from='{$user_id}' AND user_to='{$this->feuserId}') OR (user_from='{$this->feuserId}' AND user_to='{$user_id}')",
				array(
					'deleted' => 1,
					'accept' => 0,
				)
			);
			if ($res) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 *
	 */
	function sendMessageView($user_id, $send=0)
	{

		if ($send == 1) {
			// Insert the new request
			if (! $this->conf['pidMessage']) {
				// missconfiguration!
				return "<p>pidMessage not configured</p>";
			}
			// write new record
			$now = time();
			$friendsRec = array();
			$friendsRec['pid']        = intval($this->conf['pidMessage']);
			$friendsRec['crdate']     = $now;
			$friendsRec['tstamp']     = $now;
			$friendsRec['user_from']  = intval($this->feuserId);
			$friendsRec['user_to']    = intval($user_id);
			$friendsRec['message']    = $this->piVars['message'];
			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
				'tx_feuserfriends_message',
				$friendsRec
			);
			if ($res) {
				$content = sprintf($this->pi_getLL('friends_message_message_ok'), $this->internal['currentRow']['name']);
				$wrap = $this->conf['messageWrapOk'];
			} else {
				$content = sprintf($this->pi_getLL('friends_message_message_error'), $this->internal['currentRow']['name']);
				$wrap = $this->conf['messageWrapError'];
			}
			return $this->cObj->wrap($content, $wrap);
		} else {
			$tplCode = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_MESSAGE_DIALOG###');
			$markerArray['UID']   = $user_id;
			$markerArray['NAME']  = $this->internal['currentRow']['name'];
			$markerArray['IMAGE'] = $this->getFieldContent('image');
			return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
		}
	}

	/**
	 * NORMAL
	 */

	/**
	 * Shows all requests from people who wants to be friends
	 * @return string
	 */
	function friendsRequestView()
	{
		$this->mode = 'friendsRequestView.';
		$markerArray = array();
		$old_pid_list = $this->conf['pidList'];
		$this->conf['pidList'] = ($this->cObj->data['pages'] ? $this->cObj->data['pages'] : $this->conf['pidFriends']);
		$where = "
		AND user_to='{$this->feuserId}'
		AND NOT accept";
		$res = $this->pi_exec_query(
			'tx_feuserfriends_friends',
			0,
			$where,
			'',
			'',
			'crdate DESC'
		);
		$this->conf['pidList'] = $old_pid_list;
		$rowContent = '';
		$tplCode = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_FRIENDS_REQUEST_LIST###');
		$tplRow = $this->cObj->getSubpart($tplCode, '###ROW###');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// get the Information of the User who send the request
			$user_from = $this->pi_getRecord(
				'fe_users',
				$row['user_from']
			);
			$rowArray = array();
			$rowArray['VALUE']   = $user_from['name'];
			$rowArray['HREF']    = "javascript:void(0);";
			$rowArray['ONCLICK'] = "feuser_friends.dialogToAccept('{$row['uid']}')";
			$rowArray['ID']      = "friendsrequest_link_".$row['uid'];
			$rowContent .= $this->cObj->substituteMarkerArray($tplRow, $rowArray, '###|###', 0);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($rowContent == '') {
			return '';
		}
		$tplCode = $this->cObj->substituteSubpart($tplCode, '###ROW###', $rowContent, 0);
		// the language vars
		$markerArray['TITLE'] = $this->pi_getLL('friends_request_title');

		return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
	}

	/**
	 *
	 */
	function messageView()
	{
		$this->mode = 'messageView.';
		$markerArray = array();
		$old_pid_list = $this->conf['pidList'];
		$this->conf['pidList'] = ($this->cObj->data['pages'] ? $this->cObj->data['pages'] : $this->conf['pidMessage']);
		$where = "AND user_to='{$this->feuserId}'";
		$res = $this->pi_exec_query(
			'tx_feuserfriends_message',
			0,
			$where,
			'',
			'',
			'crdate DESC'
		);
		$this->conf['pidList'] = $old_pid_list;
		$tplCode = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_MESSAGE_LIST###');
		$tplRow = $this->cObj->getSubpart($tplCode, '###ROW###');
		$rowContent = '';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// get the Information of the User who send the message
			$user_from = $this->pi_getRecord(
				'fe_users',
				$row['user_from']
			);
			$rowArray = array();
			$rowArray['USER_FROM'] = $user_from['name'];
			$rowArray['MESSAGE']   = $row['message'];
			$rowContent .= $this->cObj->substituteMarkerArray($tplRow, $rowArray, '###|###', 0);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($rowContent == '') {
			return '';
		}
		$tplCode = $this->cObj->substituteSubpart($tplCode, '###ROW###', $rowContent, 0);

		return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
	}

	/**
	 * Shows the list of feusers
	 * @param $friends
	 * @return string
	 */
	function listView($friends=false)
	{

		$this->mode = 'listView.';
		$markerArray = array();
		$where = '';
		if (!isset($this->piVars['pointer'])) {
			$this->piVars['pointer'] = 0;
		}
		// Initializing the query parameters:
		list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		if (class_exists(t3lib_utility_VersionNumber) && t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$this->internal['results_at_a_time']  = t3lib_utility_Math::forceIntegerInRange($this->conf[$this->mode]['itemsPerPage'], 0, 1000, 10);
			$this->internal['maxPages']           = t3lib_utility_Math::forceIntegerInRange($this->conf[$this->mode]['maxPages'], 0, 1000, 5);
			$this->internal['showFirstLast']      = t3lib_utility_Math::forceIntegerInRange($this->conf[$this->mode]['showFirstLast'], 0, 1, 0);
			$this->internal['dontLinkActivePage'] = t3lib_utility_Math::forceIntegerInRange($this->conf[$this->mode]['dontLinkActivePage'], 0, 1, 0);
		} else {
			$this->internal['results_at_a_time']  = t3lib_div::intInRange($this->conf[$this->mode]['itemsPerPage'], 0, 1000, 10);
			$this->internal['maxPages']           = t3lib_div::intInRange($this->conf[$this->mode]['maxPages'], 0, 1000, 5);
			$this->internal['showFirstLast']      = t3lib_div::intInRange($this->conf[$this->mode]['showFirstLast'], 0, 1, 0);
			$this->internal['dontLinkActivePage'] = t3lib_div::intInRange($this->conf[$this->mode]['dontLinkActivePage'], 0, 1, 0);
		}
		$this->conf['searchFields'] = 'username,name,firstname,usergroup,email,address,city,zip';
		$this->internal['searchFieldList'] = $this->conf['searchFields'];

		$this->conf['orderByFields'] = 'username,name,firstname,usergroup,email,address,zip,city';
		$this->internal['orderByList'] = $this->conf['orderByFields'];
		if ($this->conf[$this->mode]['pagefloat']) {
			// Where in the list is the current page shown. The value 'center' puts it in the middle.
			$this->internal['pagefloat'] = $this->conf[$this->mode]['pagefloat'];
		}
		if (!$this->conf['neverHideUser'] && $this->conf['hideUserField']) {
			$where = "AND NOT ".$this->conf['hideUserField'];
		}
		// Show only friends
		if ($friends === true) {
			$friends_array = $this->getFriendsId();
			if (count($friends_array) == 0) {
				$friends_array = array(0);
			}
			$where .= " AND uid IN (".implode(',', $friends_array).")";
		}
		$userGroupsToShow = t3lib_div::intExplode(',', $this->conf['userGroupToShow'], TRUE);
		if (count($userGroupsToShow) > 0) {
			$groupWhere = array();
			foreach ($userGroupsToShow as $userGroupToShow) {
				$groupWhere[] = " CONCAT(',', usergroup, ',') LIKE '%,$userGroupToShow,%'";
			}
			$where .= " AND (".implode(" OR ", $groupWhere).")";
		}
		// Get number of records:
		$res = $this->pi_exec_query($this->internal['currentTable'], 1, $where);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		// Make listing query, pass query to MySQL:
		$res = $this->pi_exec_query($this->internal['currentTable'], 0, $where, '', '', $this->conf['orderByFields']);
		// Adds the search box:
		$markerArray['SEARCH'] = str_replace('value="Search"', 'value="Suche"', $this->pi_list_searchBox());
		// Adds the result browser:
		$markerArray['BROWSE'] = $this->pi_list_browseresults();

		if ($this->conf['have_GSI_hack']) {
			$markerArray['BROWSE_RESULTS'] = $this->pi_list_browseresults(1, '', 0);
			$markerArray['BROWSE_PAGES'] = $this->pi_list_browseresults(0, '', 1);
		}
		// sort field headers
		foreach(t3lib_div::trimExplode(',', $this->conf['orderByFields'], 1) as $f) {
			$markerArray['SORT_'.$f] = $this->pi_linkTP_keepPIvars($this->getFieldLabel($f),array('sort'=>$f.':'.($this->internal['descFlag']?0:1)));
		}
		$tplCode = '';
		// different templates for login users
		if ($GLOBALS['TSFE']->loginUser) {
			$tplCode = $this->cObj->getSubpart($this->templateFile,'###TEMPLATE_LIST_LOGIN###');
		}
		// fallback to standard template
		if ($tplCode == '') {
			$tplCode = $this->cObj->getSubpart($this->templateFile,'###TEMPLATE_LIST###');
		}
		$this->rowTplCode = $this->cObj->getSubpart($tplCode,'###SUB_TEMPLATE_ITEM###');
		$tplCode = $this->cObj->getSubpart($tplCode,'###SUB_TEMPLATE_ITEMS###');
		if ($tplCode == '') {
			return '<p>USER LIST (LIST VIEW) - EMPTY TEMPLATE!</p>';
		}
		// Adds the whole list table
		$markerArray['ITEMS'] = $this->list_makelist($res, $friends);
		// the language vars
		$markerArray['LIST_PERSON'] = $this->pi_getLL('list_person');
		$markerArray['LIST_USERNAME'] = $this->pi_getLL('list_username');
		$markerArray['LIST_NAME'] = $this->pi_getLL('list_name');
		$markerArray['LIST_FIRSTNAME'] = $this->pi_getLL('list_firstname');
		$markerArray['LIST_KORP'] = $this->pi_getLL('list_korp');


		return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0);
	}

	/**
	 *
	 * @param $res
	 * @return string
	 */
	function list_makelist($res, $friends=false)
	{
		$tRows = array();
		$this->internal['currentRow'] = '';
		$pointer = Array();
		if ($this->piVars['pointer']) {
			$pointer['pointer'] = $this->piVars['pointer'];
		}
		// Make list table rows
		$c = 0;
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tRows[] = $this->pi_list_row($c, $pointer, $friends);
			$c++;
		}
		$out = implode('', $tRows);

		return $out;
	}

	/**
	 *
	 * @param $c
	 * @param $pointer
	 * @return string
	 */
	function pi_list_row($c, $pointer=array(), $friends=false)
	{
		$markerArray = array();
		$template = $this->rowTplCode;
		// fields

		$this->conf['listView.']['fields'] = 'username,name,firstname,usergroup,email,image,firstname,city,address,zip';
		foreach(t3lib_div::trimExplode(',', $this->conf['listView.']['fields'], 1) as $f) {
			$markerArray['FIELD_'.$f] = $this->getFieldContent($f, $pointer);

		}

		$markerArray['LINK'] = $this->pi_list_linkSingle(
			'',
			$this->internal['currentRow']['uid'],
			1,
			$this->getPointer($pointer),
			true,
			$this->detailId
		);
		if ($friends === true) {
			$markerArray['ID'] = "friends_".$this->internal['currentRow']['uid'];
		} else {
			$markerArray['ID'] = "person_".$this->internal['currentRow']['uid'];
		}
		// Add as friend link
		if ($this->feuserId != $this->internal['currentRow']['uid']) {
			if (in_array($this->internal['currentRow']['uid'], $this->getFriendsId())) {
				$markerFriend['VALUE']   = $this->pi_getLL('remove_friend');
				$markerFriend['HREF']    = "javascript:void(0);";
				$markerFriend['ONCLICK'] = "feuser_friends.dialogToRemove('{$this->internal['currentRow']['uid']}')";
			} else {
				$markerFriend['VALUE']   = $this->pi_getLL('add_as_friend');
				$markerFriend['HREF']    = "javascript:void(0);";
				$markerFriend['ONCLICK'] = "feuser_friends.dialogToAdd('{$this->internal['currentRow']['uid']}')";
			}
			$snipet = $this->cObj->getSubpart($this->rowTplCode, '###ADD_AS_FRIEND###');
			$markerContent = $this->cObj->substituteMarkerArray($snipet, $markerFriend, '###|###', 0);
		}
		$template = $this->cObj->substituteSubpart($template, '###ADD_AS_FRIEND###', $markerContent, 0);
		// Send Message
		if ($this->feuserId != $this->internal['currentRow']['uid']) {
			$markerFriend['VALUE']   = $this->pi_getLL('send_message');
			$markerFriend['HREF']    = "javascript:void(0);";
			$markerFriend['ONCLICK'] = "feuser_friends.dialogSendMessage('{$this->internal['currentRow']['uid']}')";
			$snipet = $this->cObj->getSubpart($this->rowTplCode, '###SEND_MESSAGE###');
			$markerContent = $this->cObj->substituteMarkerArray($snipet, $markerFriend, '###|###', 0);
		}
		$template = $this->cObj->substituteSubpart($template, '###SEND_MESSAGE###', $markerContent, 0);
		// View Profile
		$snipet = $this->cObj->getSubpart($this->rowTplCode, '###VIEW_PROFILE###');
		$link = $this->pi_list_linkSingle(
			$this->pi_getLL('view_profile'),
			$this->internal['currentRow']['uid'],
			1,
			$this->getPointer($pointer),
			false,
			$this->detailId
		);
		$markerContent = $this->cObj->substituteMarker($snipet, '###LINK_TO###', $link);
		$template = $this->cObj->substituteSubpart($template, '###VIEW_PROFILE###', $markerContent, 0);
		// Hook for any additional handling of listView fields
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['listViewPreSubstitute'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['listViewPreSubstitute'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->listViewPreSubstitute($this, $markerArray, $template);
			}
		}

		return $this->cObj->substituteMarkerArray($template, $markerArray, '###|###', 0);
	}

	/**
	 * Return the pointer for links
	 *
	 */
	function getPointer($pointer=array())
	{
		if (isset($pointer['returnUrl'])) {
			return $pointer;
		} else {
			return array_merge($pointer, array('returnUrl' => t3lib_div::getIndpEnv('REQUEST_URI')));
		}
	}
	/**
	 *
	 * @return string
	 */
	function singleView()
	{

		$this->conf['singleView.']['fields'] = 'username,name,email,city,country,www,image,firstname,address,birthdate,zip,usergroup';

		$markerArray = array();
		$this->mode = 'singleView.';
		$this->internal['currentRow'] = $this->pi_getRecord(
			'fe_users',
			($this->piVars['showUid'] ? $this->piVars['showUid'] : $this->feuserId)
		);
		// special template if no such user has been found
		if ($this->internal['currentRow'] == false) {
			$tplCode = $this->cObj->getSubpart($this->templateFile,'###TEMPLATE_ITEM_NOUSER###');
		}
		// special template if it is a hidden user
		elseif (isset($this->conf['hideUserField']) &&
			$this->internal['currentRow'][$this->conf['hideUserField']]) {
			$tplCode = $this->cObj->getSubpart($this->templateFile,'###TEMPLATE_ITEM_HIDDENUSER###');
		}
		// different templates for login users
		elseif ($GLOBALS['TSFE']->loginUser) {
			$tplCode = $this->cObj->getSubpart($this->templateFile,'###TEMPLATE_ITEM_LOGIN###');
		}
		// fallback to standard template
		if (!$tplCode) {
			$tplCode = $this->cObj->getSubpart($this->templateFile,'###TEMPLATE_ITEM###');
		}
		if ($tplCode == '') {
			return '<p>USER LIST (SINGLE VIEW) - EMPTY TEMPLATE!</p>';
		}
		// fields and labels
		foreach(t3lib_div::trimExplode(',', $this->conf['singleView.']['fields'],1) as $f) {
			$markerArray['FIELD_'.$f] = $this->getFieldContent($f);
			$markerArray['LABEL_'.$f] = $this->getFieldLabel($f);
		}
		// back url
		if ($this->piVars['returnUrl']) {
			$markerArray['BACK_URL'] = $this->piVars['returnUrl'];
		} else {
			$pointer = array();
			if ($this->piVars['pointer']) {
				$pointer['tx_feuserfriends_pi1[pointer]'] = $this->piVars['pointer'];
			}
			$this->pi_linkTP($GLOBALS['TSFE']->id, $pointer);
			$markerArray['BACK_URL'] = $this->cObj->lastTypoLinkUrl;
		}
		// the language vars
		$markerArray['BACK_TEXT'] = $this->pi_getLL('back_text');
		// Hook for any additional handling of singleView fields
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['singleViewPreSubstitute'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['singleViewPreSubstitute'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->singleViewPreSubstitute($this, $markerArray, $tplCode);
			}
		}

		return $this->cObj->substituteMarkerArray($tplCode, $markerArray, '###|###', 0, true);
	}

	/**
	 *
	 * @param $fN
	 * @param $pointer
	 * @return string
	 */
	function getFieldContent($fN, $pointer=array())
	{
		$content = '';
		switch($fN) {
			case 'uid':
				$content = $this->pi_list_linkSingle(
					$this->pi_getLL('more', $this->internal['currentRow'][$fN]),
					$this->internal['currentRow']['uid'],
					1,
					$this->getPointer($pointer)
				);
				break;
			case 'is_online';
				// time fields
				$content = strftime('%H:%M', $this->internal['currentRow'][$fN]);
				break;
			case 'crdate':
				// date fields
				$content = strftime($this->conf[$this->mode]['timeFormat'],$this->internal['currentRow'][$fN]);
				break;
			case 'name':
			case 'username':
				if ($this->mode == 'listView.' && $this->conf['linkName']) {
					$content = $this->pi_list_linkSingle(
						$this->internal['currentRow'][$fN],
						$this->internal['currentRow']['uid'],
						1,
						$this->getPointer($pointer),
						false,
						$this->detailId
					);
				} else {
					$content = $this->internal['currentRow'][$fN];
				}
				break;
			case 'usergroup':
				$string = '';
				$query = "SELECT `title` FROM `fe_groups` WHERE `uid`='".$this->internal['currentRow']['usergroup']."'";
				//$res = $GLOBALS['TYPO3_DB']->sql_query(TYPO3_db, $query);
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title', 'fe_groups', 'uid='.$this->internal['currentRow']['usergroup']);
				if($res)
				{
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
						{
							$string = $row['title'];
						}
				}

				$content = $string;
				break;
			case 'email':
				/*if ($this->conf['alwaysShowEmail'] || // yes, always show email!
					!isset($this->conf['showEmailField']) || // don't test field, so show email
					isset($this->internal['currentRow'][$this->conf['showEmailField']])
				) { // FE user wants it, so show email
					$link_conf = $this->conf[$this->mode]['emailWrap.'];
					if (!is_array($link_conf)) {
						$link_conf = array();
					}
					$link_conf['parameter'] = $this->internal['currentRow']['email'];
					$content = $this->cObj->typolink('',$link_conf);
				}
				break;*/
			default:
				if (in_array($fN, $this->img_fields)) {
					// Image fields
					$img_conf = $this->conf[$this->mode]['imgWraps.']["$fN."];
					if (!is_array($img_conf)) {
						$img_conf = array();
					}
					$img_dir = $this->conf['imgDirs.'][$fN];
					if (!$img_dir) {
						t3lib_div::loadTCA($this->internal['currentTable']);
						$img_dir = $GLOBALS['TCA'][$this->internal['currentTable']]['columns'][$fN]['config']['uploadfolder'];
					}
					$img_dir .= '/';
					$imgages = t3lib_div::trimExplode(",", $this->internal['currentRow'][$fN]);
					foreach ($imgages as $img) {
						$img_conf['file'] = $img_dir.$img;
						$content .= $this->cObj->IMAGE($img_conf);
					}
				} elseif (in_array($fN, $this->rte_fields)) {
					// RTE fields
					$content = $this->pi_RTEcssText($this->internal['currentRow'][$fN]);
				} elseif (in_array($fN, $this->url_fields)) {
					// URL fields
					$link_conf = $this->conf[$this->mode]['linkWraps.']["$fN."];
					if (!is_array($link_conf))	$link_conf = array();
					$link_conf['parameter'] = $this->internal['currentRow'][$fN];
					$content = $this->cObj->typolink('', $link_conf);
				} elseif (is_array($this->conf[$this->mode]['showValues.']["$fN."])) {
					$content = $this->conf[$this->mode]['showValues.']["$fN."][$this->internal['currentRow'][$fN]];
				} elseif (is_array($this->conf[$this->mode]['showValuesBitmask.']["$fN."])) {

					$showValues = array();
					reset($this->conf[$this->mode]['showValuesBitmask.']["$fN."]);
					while(list($bit,$val)=each($this->conf[$this->mode]['showValuesBitmask.']["$fN."])) {
						if ($this->internal['currentRow'][$fN] & pow(2,$bit)) {
							$showValues[] = $val;
						}
					}
					$content = join(', ', $showValues);
				} else {
					$content = $this->internal['currentRow'][$fN];
				}
				break;

		}
		if ($this->conf[$this->mode]['fieldWraps.']["$fN."]) {

			$content = $this->cObj->stdWrap($content, $this->conf[$this->mode]['fieldWraps.']["$fN."]);
		}

		return $content;
	}

	/**
	 *
	 * @param $fN
	 * @return string
	 */
	function getFieldLabel($fN)
	{
		// user supplied
		if ($this->conf['labels.'][$fN]) {
			$label = $this->conf['labels.'][$fN];
		}
		// system
		if (!$label) {
			t3lib_div::loadTCA($this->internal['currentTable']);
			$label = $GLOBALS['TSFE']->sL($GLOBALS['TCA'][$this->internal['currentTable']]['columns'][$fN]['label']);
		}
		// fallback
		if (!$label) {
			"[$fN]";
		}
		if ($this->conf['labelWraps.']["$fN."]) {
			$label = $this->cObj->stdWrap($label,$this->conf['labelWraps.']["$fN."]);
		}

		return $label;
	}

	/**
	 *
	 * @return void
	 */
	function pi_addJS()
	{
		$this->pagerenderer->addJS("
var feuser_friends = {
	dialogToAdd: function(uid) {
		jQuery('#dialog').remove();
		jQuery.ajax({
			type: 'get',
			url:  'index.php',
			data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[addfriendrequest]='+uid,
			success: function(html, status) {
				jQuery('body').append('<div id=\"dialog\">'+html+'</div>');
				jQuery('#dialog').dialog({
					buttons: {
						'".t3lib_div::slashJS($this->pi_getLL('ok'))."': function() {
							jQuery.ajax({
								type: 'get',
								url:  'index.php',
								data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[addfriendrequest]='+uid+'&tx_feuserfriends_pi1[send]=1'+'&'+jQuery('textarea[name=\"tx_feuserfriends_pi1[invitation]\"]').serialize(),
								success: function(html, status) {
									if ('{$this->conf['messageTagID']}' == '') {
										alert(html);
									} else {
										jQuery('#".$this->conf['messageTagID']."').html(html);
									}
								}
							});
							jQuery(this).dialog('close');
						},
						'".t3lib_div::slashJS($this->pi_getLL('cancel'))."': function() {
							jQuery(this).dialog('close');
						}
					},
					title: '".t3lib_div::slashJS($this->pi_getLL('friends_add_title'))."'
				});
			}
		});
	},
	dialogToRemove: function(uid) {
		jQuery('#dialog').remove();
		jQuery.ajax({
			type: 'get',
			url:  'index.php',
			data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[removefriend]='+uid,
			success: function(html, status) {
				jQuery('body').append('<div id=\"dialog\">'+html+'</div>');
				jQuery('#dialog').dialog({
					buttons: {
						'".t3lib_div::slashJS($this->pi_getLL('cancel'))."': function() {
							jQuery(this).dialog('close');
						},
						'".t3lib_div::slashJS($this->pi_getLL('ok'))."': function() {
							jQuery.ajax({
								type: 'get',
								url:  'index.php',
								data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[removefriend]='+uid+'&tx_feuserfriends_pi1[send]=1',
								success: function(html, status) {
									if ('{$this->conf['messageTagID']}' == '') {
										alert(html);
									} else {
										jQuery('#".$this->conf['messageTagID']."').html(html);
									}
									jQuery('#friends_'+uid).hide('blind');
								}
							});
							jQuery(this).dialog('close');
						}
					},
					title: '".t3lib_div::slashJS($this->pi_getLL('friends_remove_title'))."'
				});
			}
		});
	},
	dialogToAccept: function(uid) {
		jQuery('#dialog').remove();
		jQuery.ajax({
			type: 'get',
			url:  'index.php',
			data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[showfriendrequest]='+uid,
			success: function(html, status) {
				jQuery('body').append('<div id=\"dialog\">'+html+'</div>');
				jQuery('#dialog').dialog({
					buttons: {
						'".t3lib_div::slashJS($this->pi_getLL('friends_request_accept'))."': function() {
							jQuery.ajax({
								type: 'get',
								url:  'index.php',
								data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[acceptfriend]='+uid,
								success: function(html, status) {
									jQuery('#friendsrequest_link_'+uid).hide('blind');
								}
							});
							jQuery(this).dialog('close');
						},
						'".t3lib_div::slashJS($this->pi_getLL('friends_request_reject'))."': function() {
							jQuery.ajax({
								type: 'get',
								url:  'index.php',
								data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[rejectfriend]='+uid,
								success: function(html, status) {
									jQuery('#friendsrequest_link_'+uid).hide('blind');
								}
							});
							jQuery(this).dialog('close');
						}
					},
					title: '".t3lib_div::slashJS($this->pi_getLL('friends_request_title'))."'
				});
			}
		});
	},
	dialogSendMessage: function(uid) {
		jQuery('#dialog').remove();
		jQuery.ajax({
			type: 'get',
			url:  'index.php',
			data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[sendmessage]='+uid,
			success: function(html, status) {
				jQuery('body').append('<div id=\"dialog\">'+html+'</div>');
				jQuery('#dialog').dialog({
					buttons: {
						'".t3lib_div::slashJS($this->pi_getLL('ok'))."': function() {
							jQuery.ajax({
								type: 'get',
								url:  'index.php',
								data: 'type={$this->conf['ajaxTypeNum']}&tx_feuserfriends_pi1[sendmessage]='+uid+'&tx_feuserfriends_pi1[send]=1'+'&'+jQuery('textarea[name=\"tx_feuserfriends_pi1[message]\"]').serialize(),
								success: function(html, status) {
									if ('{$this->conf['messageTagID']}' == '') {
										alert(html);
									} else {
										jQuery('#".$this->conf['messageTagID']."').html(html);
									}
								}
							});
							jQuery(this).dialog('close');
						},
						'".t3lib_div::slashJS($this->pi_getLL('cancel'))."': function() {
							jQuery(this).dialog('close');
						}
					},
					title: '".t3lib_div::slashJS($this->pi_getLL('friends_add_title'))."'
				});
			}
		});
	}
};");
	}

	/**
	* Set the piFlexform data
	*
	* @return void
	*/
	protected function setFlexFormData()
	{
		if (! count($this->piFlexForm)) {
			$this->pi_initPIflexForm();
			$this->piFlexForm = $this->cObj->data['pi_flexform'];
		}
	}

	/**
	 * Extract the requested information from flexform
	 * @param string $sheet
	 * @param string $name
	 * @param boolean $devlog
	 * @return string
	 */
	protected function getFlexformData($sheet='', $name='', $devlog=true)
	{
		$this->setFlexFormData();
		if (! isset($this->piFlexForm['data'])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform Data not set", $this->extKey, 1);
			}
			return null;
		}
		if (! isset($this->piFlexForm['data'][$sheet])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform sheet '{$sheet}' not defined", $this->extKey, 1);
			}
			return null;
		}
		if (! isset($this->piFlexForm['data'][$sheet]['lDEF'][$name])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform Data [{$sheet}][{$name}] does not exist", $this->extKey, 1);
			}
			return null;
		}
		if (isset($this->piFlexForm['data'][$sheet]['lDEF'][$name]['vDEF'])) {
			return $this->pi_getFFvalue($this->piFlexForm, $name, $sheet);
		} else {
			return $this->piFlexForm['data'][$sheet]['lDEF'][$name];
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuser_friends/pi1/class.tx_feuserfriends_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuser_friends/pi1/class.tx_feuserfriends_pi1.php']);
}

?>
