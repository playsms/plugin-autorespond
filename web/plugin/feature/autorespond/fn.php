<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

function autorespond_hook_recvsms_intercept_after($sms_datetime, $sms_sender, $message, $sms_receiver, $feature, $status, $uid, $smsc) {
	$ret = array();
	$hooked = FALSE;
	
	// process only when the previous feature is not 'incoming'
	if (($feature != 'incoming') && $status) {
		return $ret;
	}
	
	if ($message) {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutorespond WHERE flag_deleted='0'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$continue = TRUE;
			
			// only check sms receiver if set
			if ($db_row['sms_receiver']) {
				if ($sms_receiver != $db_row['sms_receiver']) {
					$continue = FALSE;
				}
			}
			
			if ($continue) {
				// match SMS with regex
				if (preg_match($db_row['regex'], $message)) {
					
					// match found, send respond
					$c_uid = $db_row['uid'];
					$c_username = user_uid2username($c_uid);
					$c_message = $db_row['message'];
					if (core_detect_unicode($c_message)) {
						$unicode = 1;
					}
					$smsc = gateway_decide_smsc($smsc, $db_row['smsc']);
					
					_log("match found dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " uid:" . $c_uid . " username:" . $c_username . " service:[" . $db_row['service_name'] . "] regex:[" . $db_row['regex'] . "] m:[" . $message . "] smsc:" . $smsc, 3, "autorespond");
					
					sendsms_helper($c_username, $sms_sender, $c_message, 'text', $unicode, $smsc);
					
					// log it
					$hooked = TRUE;
					
					// found then stop
					break;
				}
			}
		}
	}
	
	if ($c_uid && $hooked) {
		_log("hooked dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " uid:" . $c_uid . " username:" . $c_username . " service:[" . $db_row['service_name'] . "] regex:[" . $db_row['regex'] . "] m:[" . $message . "] smsc:" . $smsc, 3, "autorespond");
		$ret['modified'] = TRUE;
		$ret['param']['feature'] = 'autorespond';
		$ret['param']['status'] = 1;
		$ret['param']['uid'] = $c_uid;
		$ret['hooked'] = $hooked;
	}
	
	return $ret;
}
