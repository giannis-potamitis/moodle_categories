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
 * Library of interface functions and constants for module cat
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the cat specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    local
 * @subpackage cat
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function cat_supports($feature) {
    switch($feature) {
        default:                        return null;
    }
}

/*
	The cron function. It will mainly delete any unwanted records from the DB that are
	related with an already deleted course
*/
function local_cat_cron() {
	global $DB;
	$rank = $DB->get_records('cat_rank');
	$del = 0;
	foreach ($rank as $r) {
		if ($r->courseid == 0) {
			continue;
		}
		if (!$DB->record_exists('course', array('id' => $r->courseid))) {	
			$del += $DB->count_records('cat_ranks', array('rankid' => $r->id));
			$DB->delete_records('cat_ranks', array('rankid' => $r->id));
			$DB->delete_records('cat_rank', array('id' => $r->id));
			$del++;
		}
	}
	echo $del . ' records deleted from cat_rank and cat_ranks tables...';
	return true;
}
