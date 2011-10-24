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
 * Internal library of functions for module portfolioact
 *
 * All the portfolioact specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod
 * @subpackage portfolioact
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/mode/lib.php');
require_once(dirname(__FILE__) . '/save/lib.php');

class portfolioact {

    public $portfolioact;
    public $cm;
    public $course;

    public $renderer;

    public function __construct() {
        global $DB, $PAGE;

        $id = optional_param('id', 0, PARAM_INT); // course_module ID, or
        $cm = optional_param('cm', 0, PARAM_INT); //course module ID - as called by settings navigation
        $n = optional_param('n', 0, PARAM_INT);  // portfolioact instance ID

        if ($cm) {
            $id = $cm;
        }

        if ($id) {
            $cm = get_coursemodule_from_id('portfolioact', $id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            $portfolioact = $DB->get_record('portfolioact',
                array('id' => $cm->instance), '*', MUST_EXIST);
        } else if ($n) {
            $portfolioact = $DB->get_record('portfolioact', array('id' => $n), '*', MUST_EXIST);
            $course = $DB->get_record('course',
                array('id' => $portfolioact->course), '*', MUST_EXIST);
            $cm = get_coursemodule_from_instance('portfolioact',
                $portfolioact->id, $course->id, false, MUST_EXIST);
        } else if (isset($PAGE->cm->id) && (! empty($PAGE->cm->id))) {
            $cm = get_coursemodule_from_id('portfolioact', $PAGE->cm->id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course',
                array('id' => $cm->course), '*', MUST_EXIST);
            $portfolioact = $DB->get_record('portfolioact',
                array('id' => $cm->instance), '*', MUST_EXIST);
        } else {
            error('You must specify a course_module ID or an instance ID');
        }

        require_login($course, false, $cm);

        $this->portfolioact = $portfolioact;
        $this->cm = $cm;
        $this->course = $course;
        $this->pa_renderer = $PAGE->get_renderer('mod_portfolioact');
    }

    /**
     * Returns the allowed save types for this activity
     *
     * Returns the allowed save types for this activity by applying the filter
     * for this activity to the possible system ones.
     * @return array
     */

    public function get_allowed_save_types() {

        global $DB;

        $saves = get_plugin_list('portfolioactsave');

        $rec = $DB->get_record('portfolioact', array('id'=>$this->portfolioact->id), 'savetypes');

        $availtypes = array();

        foreach ($saves as $type => $path) {
            if (portfolioact_save::save_type_enabled($type,
               $this->portfolioact->id, $rec->savetypes)) {
                $availtypes[] = $type;
            }
        }
        return $availtypes;
    }

}
