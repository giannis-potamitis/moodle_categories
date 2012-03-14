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
$rankid = required_param('rankid', PARAM_INT);
$privilege = optional_param('privilege', 0, PARAM_INT);

require_login($cid); // NOTE: if cid is a valid course, it will show the Course Administration Settings and also set the navbar

if ($cid < 0 || $rankid < 0 || $privilege < 0) {
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

$rank = $DB->get_record('cat_rank', array('id' => $rankid), '*', MUST_EXIST);

if ($rank->courseid != $cid) {
	print_error(get_string('norightpermissions', 'local_cat'));
	die;	
}

/// Print the page header
$PAGE->set_context($context); // not needed if we have set it before in require_login
$PAGE->set_url('/local/cat/editranks.php', array('cid' => $cid, 'privilege' => $privilege, 'rankid' => $rankid));
$PAGE->set_title(format_string(get_string('editranks', 'local_cat')));
$PAGE->set_heading(format_string(get_string('editranks', 'local_cat')));
$PAGE->set_pagelayout('mydashboard');


$r_url = $CFG->wwwroot . '/local/cat/subranks.php?cid=' . $cid . '&privilege=' . $privilege;

// Set the navbar
//$PAGE->navbar->ignore_active();
//$PAGE->navbar->add(get_string('home'), new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add(get_string('ranks', 'local_cat'), new moodle_url($r_url));
$PAGE->navbar->add(get_string('editranks', 'local_cat'));

// Set the settings navigation
/*
$course_settings = $PAGE->settingsnav->get('courseadmin');
if (!empty($course_settings) && has_capability('local/cat:editingteacher', $PAGE->context)) {
	print_r($course_settings->get_children_key_list());
}*/
	
// rank form	
$url = $CFG->wwwroot . '/local/cat/editranks.php?cid=' . $cid . '&privilege=' . $privilege . '&rankid=' . $rankid;

$ranks = $DB->get_records('cat_ranks', array('rankid' => $rankid), 'priority ASC');
$ranks_custom = new stdClass();
$ranks_custom->ranks = $ranks;

$rankform = new local_cat_rank_form($url, $ranks_custom);
$submit = $rankform->get_data();
 
$redefine = false;

if ($submit) {
	// for group name
	if (isset($submit->change) || isset($submit->back)) {
		$rank->name = $submit->rankname;
		$DB->update_record('cat_rank', $rank);		
	}
	
	// for elements
	foreach ($ranks as $r) {
		$update = 'update' . $r->id;
		$des = 'description' . $r->id;
    $delete = 'delete' . $r->id;
    $up = 'up' . $r->id;
    $down = 'down' . $r->id;
    
    if (isset($submit->$update) || isset($submit->back)) { // update
    	$r->description = $submit->$des;
    	$DB->update_record('cat_ranks', $r);
    }
    
   	if (isset($submit->$delete)) { // delete
    	$DB->delete_records('cat_ranks', array('id' => $r->id));
    	$newranks = $ranks;
    	unset($newranks[$r->id]);
    	$rank->nextpriority -= 1;
    	$DB->update_record('cat_rank', $rank);
    	$i = 0;
    	foreach ($newranks as &$rr) { // update priority values
    		$i++;
    		$rr->priority = $i;
    		$DB->update_record('cat_ranks', $rr);
    	}
    	$ranks = $newranks;
    	$redefine = true;
    }
    
		if (isset($submit->$up) || isset($submit->$down)) { // move up or down
    	$swappriority = $r->priority + (isset($submit->$up) ? -1 : 1);
    	$swap = $DB->get_record('cat_ranks', array('rankid' => $rankid, 'priority' => $swappriority), '*', MUST_EXIST);
    	$swap->priority += isset($submit->$up) ? 1 : -1;
    	$DB->update_record('cat_ranks', $swap);
    	
    	$r->priority += isset($submit->$up) ? -1 : 1;
    	$DB->update_record('cat_ranks', $r);
    	// get ranks in new order
			$ranks = $DB->get_records('cat_ranks', array('rankid' => $rankid), 'priority ASC');
    	$redefine = true;			
    }	        
      		
	}
	
	// new element
	if (isset($submit->addnew) || (isset($submit->back) && !empty($submit->newrank))) {
		$DB->insert_record('cat_ranks', array('rankid' => $rankid, 'description' => $submit->newrank, 'priority' => $rank->nextpriority));
		$rank->nextpriority++;
		$DB->update_record('cat_rank', $rank);
		$ranks = $DB->get_records('cat_ranks', array('rankid' => $rankid), 'priority ASC');
		$redefine = true;		
	}
	
	if (isset($submit->back)) {
		redirect($r_url);
	}
	
}

if ($redefine) {// need to redefine ranks form
	$ranks_custom->ranks = $ranks;
	$rankform = new local_cat_rank_form($url, $ranks_custom);  	
}	


// set rank data
$rankdata = new stdClass();
$rankdata->rankname = $rank->name;
foreach	($ranks as $r) {
	$id = 'id' . $r->id;
	$rankdata->$id = $r->id;
	$description = 'description' . $r->id;
	$rankdata->$description = $r->description;
}	

$rankform->set_data($rankdata);	
	
// Output starts here
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('editranks', 'local_cat'));


$rankform->display();

// JavaScript to empty newrank
$html = '<script type="text/javascript">
								function clearNewRank() {
									var nrank = document.getElementsByName("newrank")[0];
									nrank.value = "";
								}
								
								window.onload = clearNewRank();
 
					</script>';
echo $html;


//echo '<a href="' . $r_url . '">' . get_string('ranks_return', 'local_cat') . '</a>';

// Finish the page
echo $OUTPUT->footer();

