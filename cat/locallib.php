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
 * Internal library of functions for module cat
 *
 * All the cat specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage cat
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */
//function cat_do_something_useful(array $things) {
//    return new stdClass();
//}

// Taken from http://www.jonasjohn.de/snippets/php/array2object.htm
function cat_objectToarray($obj) {
    if (is_object($obj)) {
        foreach ($obj as $field => $value) {
            $array[$field] = $value;
        }
    }
    else {
        $array = $obj;
    }
    return $array;
}


function cat_output_array($array)
{
	foreach ($array as $field => $value) {
  	echo $field . ': ' . $value . '<br/><br/>';
  }
}

// a modified version of lib/moodlelib make_grades_menu function
function cat_make_grades_menu($gradingtype) {    
    $value = 0;
    while ($value <= $gradingtype) {
    	//$grades[$value] = $value . ' / ' . $gradingtype;
    	$grades[] = $value . ' / ' . $gradingtype;    	
    	$value += 0.5; 
    }
    
    $grades = array_reverse($grades);
    return $grades;
}

/*
	Create the default ranks in the Database
*/
function set_default_ranks() {
	global $DB;
	
	$rankid = $DB->insert_record('cat_rank', array('name' => get_string('rank_default', 'local_cat'), 'nextpriority' => 7, 'courseid' => 0));
	$elements = array(get_string('rank_poor', 'local_cat'), get_string('rank_minimal', 'local_cat'), 
										get_string('rank_modest', 'local_cat'), get_string('rank_good', 'local_cat'), 
										get_string('rank_verygood', 'local_cat'), get_string('rank_perfect', 'local_cat'));
	$i = 0;
	foreach ($elements as $el) {
		$i++;
		$DB->insert_record('cat_ranks', array('description' => $el, 'priority' => $i, 'rankid' => $rankid));
	}
	
	//return $DB->get_records('cat_ranks', array('rankid' => $rankid));
	return $rankid;
}
