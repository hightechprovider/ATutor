<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2004 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
/* linked from admin/users.php                                  */
/* deletes a user from the system.                              */
/****************************************************************/

$_user_location = 'admin';

define('AT_INCLUDE_PATH', '../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
if ($_SESSION['course_id'] > -1) { exit; }

$id = intval($_GET['id']);

if (isset($_GET['delete'])) {
	$sql	= "DELETE FROM ".TABLE_PREFIX."course_enrollment WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	$sql	= "DELETE FROM ".TABLE_PREFIX."forums_accessed WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	$sql	= "DELETE FROM ".TABLE_PREFIX."forums_subscriptions WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	/* can't delete the posts b/c it'll affect the page count and the reply count */
	$sql	= "UPDATE ".TABLE_PREFIX."forums_threads SET body='[i]This post was deleted along with its owner.[/i]', subject='Deleted', member_id=0, login='Deleted' WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	$sql	= "DELETE FROM ".TABLE_PREFIX."instructor_approvals WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	$sql	= "DELETE FROM ".TABLE_PREFIX."messages WHERE from_member_id=$id OR to_member_id=$id";
	$result = mysql_query($sql, $db);

	$sql	= "DELETE FROM ".TABLE_PREFIX."users_online WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	$sql	= "DELETE FROM ".TABLE_PREFIX."members WHERE member_id=$id";
	$result = mysql_query($sql, $db);

	Header('Location: users.php?f='.urlencode_feedback(AT_FEEDBACK_USER_DELETED));
	exit;
}
if ($_GET['cancel'] == 1) {
	Header('Location: users.php?f='.urlencode_feedback(AT_FEEDBACK_CANCELLED));
	exit;
}
require(AT_INCLUDE_PATH.'header.inc.php'); 
?>
<h2><?php echo _AT('atutor_administration') ?></h2>
<h3><?php echo _AT('delete_user') ?></h3>

<?php
	if (isset($_GET['f'])) { 
		$f = intval($_GET['f']);
		if ($f <= 0) {
			/* it's probably an array */
			$f = unserialize(urldecode($_GET['f']));
		}
		print_feedback($f);
	}
	if (isset($errors)) { print_errors($errors); }
	if(isset($warnings)){ print_warnings($warnings); }
	$sql	= "SELECT * FROM ".TABLE_PREFIX."members WHERE member_id=$id";
	$result = mysql_query($sql, $db);
	if (!($row = mysql_fetch_assoc($result))) {
		echo _AT('no_user_found');
	} else {
		$sql	= "SELECT * FROM ".TABLE_PREFIX."courses WHERE member_id=$id";
		$result = mysql_query($sql, $db);
		if (($row2 = mysql_fetch_assoc($result))) {
			$errors[]=AT_ERROR_NODELETE_USER;
			print_errors($errors);
		} else {
			$warnings[]=array(AT_WARNING_DELETE_USER, AT_print($row['login'], 'members.login'));
			print_warnings($warnings);
			echo '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.SEP.'delete=1">'._AT('yes_delete').'</a>';
			echo ' <span class="bigspacer">|</span> ';
			echo '<a href="'.$_SERVER['PHP_SELF'].'?cancel=1">'._AT('no_cancel').'</a>.';
		}
	}

require(AT_INCLUDE_PATH.'footer.inc.php'); 
?>