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

if (!auth_isvalid()) {
	auth_block();
}

if ($id = $_REQUEST['id']) {
	if (!($id = dba_valid(_DB_PREF_ . '_featureAutorespond', 'id', $id))) {
		auth_block();
	}
}

switch (_OP_) {
	case "autorespond_list":
		$content = _dialog() . "
			<h2>" . _('Manage autorespond') . "</h2>
			";
		if (auth_isadmin()) {
			$content .= _button('index.php?app=main&inc=feature_autorespond&op=autorespond_add', _('Add SMS autorespond'));
		} else {
			$query_user_only = "AND uid='" . $user_config['uid'] . "'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutorespond WHERE flag_deleted='0' " . $query_user_only . " ORDER BY service_name, regex, sms_receiver";
		$db_result = dba_query($db_query);
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>";
		if (auth_isadmin()) {
			$content .= "
				<thead><tr>
					<th width=20%>" . _('Service') . "</th>
					<th width=20%>" . _('Regex') . "</th>
					<th width=30%>" . _('Respond message') . "</th>
					<th width=20%>" . _('User') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		} else {
			$content .= "
				<thead><tr>
					<th width=20%>" . _('Service') . "</th>
					<th width=20%>" . _('Regex') . "</th>
					<th width=50%>" . _('Respond message') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		}
		$content .= "<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				if (auth_isadmin()) {
					$action = "<a href=\"" . _u('index.php?app=main&inc=feature_autorespond&op=autorespond_edit&id=' . $db_row['id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
					$action .= "<a href=\"javascript: ConfirmURL('" . sprintf(_('Are you sure you want to delete SMS autorespond %s ?'), $db_row['service_name']) . "','" . _u('index.php?app=main&inc=feature_autorespond&op=autorespond_del&id=' . $db_row['id']) . "')\">" . $icon_config['delete'] . "</a>";
				} else {
					$action = _hint('Please contact service provider to manage this service');
				}
				$sms_receiver = '';
				if ($db_row['sms_receiver']) {
					$sms_receiver = "<div name=autorespond_sms_receiver><span class=\"playsms-icon glyphicon glyphicon-inbox\" alt=\"" . _('Receiver number') . "\" title=\"" . _('Receiver number') . "\"></span>" . $db_row['sms_receiver'] . "</div>";
				}
				$message = $db_row['message'];
				if (auth_isadmin()) {
					$show_owner = "<td>" . $owner . "</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>" . $db_row['service_name'] . "</td>
						<td>" . $db_row['regex'] . "</td>
						<td>" . $db_row['message'] . "</td>
						" . $show_owner . "
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			";
		if (auth_isadmin()) {
			$content .= _button('index.php?app=main&inc=feature_autorespond&op=autorespond_add', _('Add SMS autorespond'));
		}
		_p($content);
		break;
	
	case "autorespond_add":
		if (!auth_isadmin()) {
			auth_block();
		}
		
		$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('add_smsc') . "</td></tr>";
		
		$content .= _dialog() . "
			<h2>" . _('Manage autorespond') . "</h2>
			<h3>" . _('Add SMS autorespond') . "</h3>
			<form action=index.php?app=main&inc=feature_autorespond&op=autorespond_add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _mandatory(_('Service')) . "</td><td><input type=text size=30 maxlength=255 name=add_service_name value=\"" . _lastpost('add_service_name') . "\"></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Regex')) . "</td><td><input type=text size=140 maxlength=140 name=add_regex value=\"" . _lastpost('add_regex') . "\"> " . _hint(_('Regular expression to match with incoming SMS')) . "</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Respond message')) . "</td><td><input type=text name=add_message value=\"" . _lastpost('add_message') . "\"></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('User')) . "</td><td>" . themes_select_users_single('add_uid', 1) . "</td>
				</tr>
				<tr>
					<td>" . _('Receiver number') . "</td><td><input type=text size=30 maxlength=20 name=add_sms_receiver value=\"" . _lastpost('add_sms_receiver') . "\"></td>
				</tr>
				" . $select_reply_smsc . "
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_autorespond&op=autorespond_list');
		_p($content);
		break;
	
	case "autorespond_add_yes":
		if (!auth_isadmin()) {
			auth_block();
		}
		
		$add_service_name = trim($_POST['add_service_name']);
		$add_regex = trim($_POST['add_regex']);
		$add_message = trim($_POST['add_message']);
		$add_uid = (int) $_POST['add_uid'];
		$add_sms_receiver = trim($_POST['add_sms_receiver']);
		$add_smsc = trim($_POST['add_smsc']);
		
		if ($add_service_name && $add_regex && $add_message && $add_uid) {
			$db_query = "INSERT INTO " . _DB_PREF_ . "_featureAutorespond (created,last_update,uid,service_name,regex,message,sms_receiver,smsc) VALUES ('" . core_get_datetime() . "','" . core_get_datetime() . "','$add_uid','$add_service_name','$add_regex','$add_message','$add_sms_receiver','$add_smsc')";
			if ($new_uid = @dba_insert_id($db_query)) {
				$_SESSION['dialog']['info'][] = sprintf(_('SMS autorespond %s has been added'), $add_service_name, $add_regex);
				_lastpost_empty();
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to add SMS autorespond');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		
		header("Location: " . _u('index.php?app=main&inc=feature_autorespond&op=autorespond_add'));
		exit();
		break;
	
	case "autorespond_edit":
		if (!auth_isadmin()) {
			auth_block();
		}
		
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutorespond WHERE id='$id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_service_name = (_lastpost('edit_service_name') ? _lastpost('edit_service_name') : $db_row['service_name']);
		$edit_regex = (_lastpost('edit_regex') ? _lastpost('edit_regex') : $db_row['regex']);
		$edit_message = (_lastpost('edit_message') ? _lastpost('edit_message') : $db_row['message']);
		$edit_uid = $db_row['uid'];
		$edit_sms_receiver = $db_row['sms_receiver'];
		$edit_smsc = $db_row['smsc'];
		
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('edit_smsc', $edit_smsc) . "</td></tr>";
		}
		
		$content .= _dialog() . "
			<h2>" . _('Manage autorespond') . "</h2>
			<h3>" . _('Edit SMS autorespond') . "</h3>
			<form action=index.php?app=main&inc=feature_autorespond&op=autorespond_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=id value=$id>
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _mandatory(_('Service')) . "</td><td><input type=text size=30 maxlength=255 name=edit_service_name value=\"" . $edit_service_name . "\"></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Regex')) . "</td><td><input type=text size=140 maxlength=140 name=edit_regex value=\"" . $edit_regex . "\"> " . _hint(_('Regular expression to match with incoming SMS')) . "</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Respond message')) . "</td><td><input type=text name=edit_message value=\"" . $edit_message . "\"></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('User')) . "</td><td>" . themes_select_users_single('edit_uid', $edit_uid) . "</td>
				</tr>
				<tr>
					<td>" . _('Receiver number') . "</td><td><input type=text size=30 maxlength=20 name=edit_sms_receiver value=\"" . $edit_sms_receiver . "\"></td>
				</tr>
				" . $select_reply_smsc . "
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_autorespond&op=autorespond_list');
		_p($content);
		break;
	
	case "autorespond_edit_yes":
		if (!auth_isadmin()) {
			auth_block();
		}
		
		$edit_service_name = trim($_POST['edit_service_name']);
		$edit_regex = trim($_POST['edit_regex']);
		$edit_message = trim($_POST['edit_message']);
		$edit_uid = (int) $_POST['edit_uid'];
		$edit_sms_receiver = trim($_POST['edit_sms_receiver']);
		$edit_smsc = trim($_POST['edit_smsc']);
		
		if ($id && $edit_service_name && $edit_regex && $edit_message && $edit_uid) {
			$db_query = "UPDATE " . _DB_PREF_ . "_featureAutorespond SET last_update='" . core_get_datetime() . "',service_name='$edit_service_name',regex='$edit_regex',message='$edit_message',uid='$edit_uid',sms_receiver='$edit_sms_receiver',smsc='$edit_smsc' WHERE id='$id' AND flag_deleted='0'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = sprintf(_('SMS autorespond %s has been saved'), $edit_service_name);
				_lastpost_empty();
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save SMS autorespond');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_autorespond&op=autorespond_edit&id=' . $id));
		exit();
		break;
	
	case "autorespond_del":
		if (!auth_isadmin()) {
			auth_block();
		}
		
		$db_query = "SELECT service_name FROM " . _DB_PREF_ . "_featureAutorespond WHERE id='$id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($id && $db_row['service_name']) {
			$db_query = "UPDATE " . _DB_PREF_ . "_featureAutorespond SET last_update='" . core_get_datetime() . "',flag_deleted='1' WHERE id='$id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = sprintf(_('SMS autorespond %s has been deleted'), $db_row['service_name']);
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to delete SMS autorespond');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_autorespond&op=autorespond_list'));
		exit();
		break;
}
