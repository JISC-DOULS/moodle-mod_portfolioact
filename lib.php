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
 * Library of interface functions and constants for module portfolioact
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the portfolioact specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package mod
 * @subpackage portfolioact
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function portfolioact_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $portfolioact An object from the form in mod_form.php
 * @return int The id of the newly inserted portfolioact record
 */
function portfolioact_add_instance($portfolioact) {
    global $DB;

    $portfolioact->timecreated = time();
    $portfolioact->timemodified = $portfolioact->timecreated;
    # You may have to add extra stuff in here #

    return $DB->insert_record('portfolioact', $portfolioact);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $portfolioact An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function portfolioact_update_instance($portfolioact) {
    global $DB;

    $portfolioact->timemodified = time();
    $portfolioact->id = $portfolioact->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('portfolioact', $portfolioact);
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in portfolioact activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function portfolioact_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function portfolioact_cron () {
    //TODO - Clear out templates and scaffolds from deleted courses
    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of portfolioact. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $portfolioactid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function portfolioact_get_participants($portfolioactid) {
    return false;
}

/**
 * This function returns if a scale is being used by one portfolioact
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $portfolioactid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function portfolioact_scale_used($portfolioactid, $scaleid) {
    global $DB;

    $return = false;

    return $return;
}

/**
 * Checks if scale is being used by any instance of portfolioact.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any portfolioact
 */
function portfolioact_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $chatnode The node to add module settings to
 */
function portfolioact_extend_settings_navigation(settings_navigation
    $settings, navigation_node $portfolioactnode) {
    global  $PAGE;

    require_once(dirname(__FILE__) . '/mode/lib.php');

    $url = new moodle_url('/mod/portfolioact/mode/template/manager.php',
        array('id'=>$PAGE->cm->id, 'action'=>'list'));
    $portfolioactnode->add(get_string('managetemplates', 'portfolioactmode_template'), $url);

    $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
        array('id'=>$PAGE->cm->id, 'action'=>'list'));
    $portfolioactnode->add(get_string('managescaffolds', 'portfolioactmode_scaffold'), $url);

    $mode = portfolioact_mode::get_plugin_mode($PAGE->cm->instance);
    $plugin = portfolioact_mode::get_mode_instance($PAGE->cm->instance, $mode);
    $plugin->nav_menu_item($portfolioactnode);

}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function portfolioact_delete_instance($id) {
    global $DB, $CFG;

    require_once(dirname(__FILE__) . '/mode/lib.php');

    if (!$portfolioact = $DB->get_record('portfolioact', array('id'=>$id))) {
        return false;
    }
    # Delete any dependent records here #

    $mode = portfolioact_mode::get_plugin_mode($portfolioact->id);
    $class = 'portfolioact_mode_'.$mode;

    if (! class_exists($class)) {
            include($CFG->dirroot.'/mod/portfolioact/mode/'.$mode.'/lib.php');
    }

    $table = $class::table_name();
    $table_name = 'portfolioact_'.$table.'_settings';

    $DB->delete_records('portfolioact', array('id' => $portfolioact->id));

    $DB->delete_records($table_name, array('actid' => $portfolioact->id));

    if ($mode == 'template') {
        $DB->delete_records('portfolioact_tmpl_entries', array('actid' => $portfolioact->id));
    }

    return true;
}

/**
 * Called by Moodle on course delete
 * Clear out data
 * @param Object $course
 */
function portfolioact_delete_course($course) {
    global $CFG, $DB;
    //clean up data for each mode (does not delegate - so extra modes must add in here)
    //TEMPLATES (will also delete pages + items)
    require_once($CFG->dirroot.'/mod/portfolioact/mode/template/lib.php');
    if ($templates = $DB->get_records('portfolioact_template', array('course' => $course->id))) {
        foreach ($templates as $template) {
            portfolioact_template::delete($template->id);
        }
    }
    //SCAFFOLDS are deleted automatically as in File table with context of course
}
