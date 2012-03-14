<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of mycat
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage mycat
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once('rank_form.php');



if (isguestuser()) {
			print_error(get_string('norightpermissions', 'local_cat'));
	die;
}

$cid = optional_param('cid', 0, PARAM_INT); // course id
$privilege = optional_param('privilege', 0, PARAM_INT);

require_login($cid); // NOTE: if cid is a valid course, it will show the Course Administration Settings and also set the navbar

if ($cid < 0 || $privilege < 0) {
	print_error(get_string('nonegative', 'local_cat'));
	die;
}

if (($cid > 0 && $privilege == 1) || ($cid == 0 && $privilege == 0)) {
	print_error(get_string('wrongcombparameters', 'local_cat'));
	die;
}

$context_user = get_context_instance(CONTEXT_USER, $USER->id);
if ($privilege == 1 && !has_capability('local/cat:admin', $context_user)) {
	print_error(get_string('norightpermissions', 'local_cat'));
	die;	
}
else {
	$context = get_context_instance(CONTEXT_SYSTEM);
}


if ($cid > 0) {
	$course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
	$context_course = get_context_instance(CONTEXT_COURSE, $cid);
	if (!has_capability('local/cat:editingteacher', $context_course)) {
		print_error(get_string('norightpermissions', 'local_cat'));
		die;	
	}
	else {
		$context = $context_course;
	}
}

/// Print the page header
$PAGE->set_context($context); // not needed if we have set it before in require_login
$PAGE->set_url('/local/cat/subranks.php', array('cid' => $cid, 'privilege' => $privilege));
$PAGE->set_title(format_string(get_string('ranks', 'local_cat')));
$PAGE->set_heading(format_string(get_string('ranks', 'local_cat')));
$PAGE->set_pagelayout('mydashboard');

$url = new moodle_url('/local/cat/subranks.php', array('cid' => $cid, 'privilege' => $privilege));


// Set the navbar
//$PAGE->navbar->ignore_active();
//$PAGE->navbar->add(get_string('home'), new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add(get_string('ranks', 'local_cat'));
//$PAGE->navbar->add(get_string('editranks', 'local_cat'));

$rank_groups = $DB->get_records('cat_rank', array('courseid' => $cid));
$data = new stdClass();
$data->rank_groups = $rank_groups;
$data->cid = $cid;
$data->privilege = $privilege;
$list_form = new local_cat_list_form($url, $data);
$listsubmit = $list_form->get_data(); 

$addform = new local_cat_add_group_form($url);
$addsubmit = $addform->get_data();	

if ($addsubmit) { // add form submit
	$rankid = $DB->insert_record('cat_rank', array('name' => $addsubmit->newgroup, 'courseid' => $cid, 'nextpriority' => 3));
	$DB->insert_record('cat_ranks', array('rankid' => $rankid, 'description' => 'Default 1', 'priority' => 1));
	$DB->insert_record('cat_ranks', array('rankid' => $rankid, 'description' => 'Default 2', 'priority' => 2));	
	$rank_groups = $DB->get_records('cat_rank', array('courseid' => $cid));
	$data->rank_groups = $rank_groups;
	$list_form = new local_cat_list_form($url, $data);		
} else if ($listsubmit) {
	$d = $listsubmit;
	foreach($rank_groups as $g) {
		$delete = 'delete' . $g->id;
		if (isset($d->$delete)) {
			$tempranks = $DB->get_records('cat_ranks', array('rankid' => $g->id));
			foreach ($tempranks as $rr) {
				$DB->delete_records('cat_subcat_submission', array('ranksid' => $rr->id)); // clear all subcategory submissions
			}
			$DB->delete_records('cat_ranks', array('rankid' => $g->id)); // remove all its ranks
			
			$cat = $DB->get_records('cat', array('rankid' => $g->id)); 
			foreach ($cat as $c) {
				$c->rankid = 0; // set all categories whose subcategories were using that group of ranks, to default
				$DB->update_record('cat', $c);
			}
			
			$DB->delete_records('cat_rank', array('id' => $g->id)); // finally delete that group name
			break;
		}
	}
	
	// redefine rank_groups
	$rank_groups = $DB->get_records('cat_rank', array('courseid' => $cid));
	$data->rank_groups = $rank_groups;
	$list_form = new local_cat_list_form($url, $data);		
}


/*
// set rank data
$rankdata = new stdClass();
$rankdata->rankname = $rank->name;
$rankform->set_data($rankdata);	*/

	
// Output starts here
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('ranks', 'local_cat'));


	
$list_form->display();
$addform->display();

//echo $OUTPUT->heading(get_string('elements', 'local_cat'));

// JavaScript to empty newrank
$html = '<script type="text/javascript">
								function clearNewRank() {
									var nrank = document.getElementsByName("newgroup")[0];
									nrank.value = "";
								}
								
								window.onload = clearNewRank();
 
					</script>';
echo $html;

// Finish the page
echo $OUTPUT->footer();

