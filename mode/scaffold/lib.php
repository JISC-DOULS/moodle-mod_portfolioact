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
 * Code to support the Portfolioact Scaffold
 *
 * Classes and functions to support the scaffold mode.
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * require code
 */

require_once(dirname(dirname(dirname(__FILE__))).'/locallib.php');
defined('MOODLE_INTERNAL') || die();

//functionality shared by all modes
//???? load settings, save settings, present a settings page (used by the parent module)
//present pages for managing content i.e pages and
//items in case of template mode (used by the parent module)
//This class relates to the activity settings (albeit in template mode) not to the template

/**
 * Class for portfolioact scaffold mode sub-plugin
 *
 * This extends the mode plugin class and provides a renderer
 * and other functionality to manage the scaffold specific settings for
 * this activity
 *
 * @package portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



class portfolioact_mode_scaffold extends portfolioact_mode_plugin {

    public $settings;
    public $actid;
    const TABLE_NAME = 'scaff';

    /**
     * Constructor for portfolioact_mode_scaffold
     * @param int $actid
     * @param int $scaffold
     * @return portfolioact_mode_scaffold with or without settings
     */


    public function __construct() {
        global $DB;

        parent::__construct('scaffold');

            $settings = $DB->get_record('portfolioact_scaff_settings',
                array('actid'=>$this->portfolioact->id), '*');
            //false if no record
        if ($settings === false) {
            $this->settings = null;
        } else {
            $this->settings = $settings;
        }

        $this->DB = $DB;

    }

    /**
     * Return the table name
     *
     * @see portfolioact_mode_plugin::table_name
     *
     *
     */

    public static function table_name() {
        return self::TABLE_NAME;

    }

    /*
     * Return an instance of the mode class from the activity id
     *
     * @see portfolioact_mode_plugin->get_mode_operational_instance()
     * @param int $actid
     * @return mixed
     */

    public static function get_mode_operational_instance($actid) {
        global $DB;
        $settings = $DB->get_record('portfolioact_scaff_settings',
                array('actid'=>$actid), '*');
            //false if no record
        if ($settings === false) {
            return null;
        } else {
            $scaffid = $settings->scaffold;
            return new portfolioact_scaffold($scaffid);
        }
    }

    /*
     * Return the settings
     *
     * @param int 4actid
     * @return mixed
     *
     */


    public static function get_settings($actid) {

        global $DB;
        $settings = $DB->get_record('portfolioact_scaff_settings',
                array('actid'=>$actid), '*');
            //false if no record
        if ($settings === false) {
            return null;
        } else {
            return $settings;
        }
    }


    /**
     * Returns a collection of files
     *
     * Returns a collection of files for the given activity. Scaffold id can be passed.
     * If Scaffold id is not passed it will use the scaffold associated with the passed $actid .
     * If Scaffold id is passed it will get the data associated with that scaffold
     * and use the current act id to get context.
     *
     * This allows for the case that you may be getting scaffold data
     * while on another activity e.g. exporting a template with a scaffold attached.
     *
     * @param int $actid
     * @param int $scaffoldid
     * @return mixed 1. array of files - moodle file objects 2. the meta data needed by the export
     */

    public static function get_data_for_export($actid, $scaffoldid = null) {
        global $DB;

        if (is_null($scaffoldid)) {
            $scaffold = self::get_mode_operational_instance($actid);
            if (is_null($scaffold)) {
                return null;
            }
        } else {
            $scaffold = new portfolioact_scaffold($scaffoldid);
        }

        $fs = get_file_storage();
        $portfolioact = $DB->get_record('portfolioact', array('id'=>$actid), 'course' );
        $context = get_context_instance(CONTEXT_COURSE, $portfolioact->course);

         //TODO maybe make the component here and in /scaffold/designer a constant
        $fileinfo = array(
            'component' => 'mod_portfolioactmode_scaffold',
            'filearea' => 'scaffoldset',
            'itemid' => $scaffold->id,
            'contextid' => $context->id);

        $tree = $fs->get_area_tree($fileinfo['contextid'], $fileinfo['component'],
            $fileinfo['filearea'], $fileinfo['itemid']);

        if (empty($tree['files']) && empty($tree['subdirs'])) {
            return null;
        }

        return $tree;
    }


    /**
     * Produces a menu item for the side navigation for edit settings
     * @param mixed $node
     */

    public function nav_menu_item(&$node) {

        $url = new moodle_url('/mod/portfolioact/mode/scaffold/editscaffold.php',
            array('id'=>$this->cm->id, 'action'=>'list'));
        $node->add(get_string('editscaffoldsettings', 'portfolioactmode_scaffold'), $url);
    }



    /**
     * Adds a new scaffold @see portfolioact_mode_plugin->add_new()
     *
     * @param string $name
     * @param int $courseid
     *
     * @return boolean
     */

    public function add_new($name, $courseid) {

        global $DB;

        $newscaffold = new stdClass;
        $newscaffold->id = null;
        $newscaffold->name = $name;
        $newscaffold->timecreated = time();
        $newscaffold->course = $courseid;
        $DB->insert_record('portfolioact_scaffolds', $newscaffold);

        return true;
    }


    /**
     * Set the scaffold for this activity
     *
     * @param int $scaffoldid
     * @return boolean
     */


    public function set_settings($scaffoldid) {
        $settings = new stdClass();
        $settings->scaffold = $scaffoldid;

        if (! empty($this->settings) ) {
            $settings->id = $this->settings->id;
            $this->DB->update_record('portfolioact_scaff_settings', $settings);
        } else {//no settings yet
            $settings->actid = $this->portfolioact->id;
            $this->DB->insert_record('portfolioact_scaff_settings', $settings);

        }

    }

}




/**
 * Class for portfolioact scaffold
 *
 * This object represents the scaffold  qua scaffold. It requires a scaffold id to initialise.
 *
 * @package portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_scaffold  {

    private $name;
    private $course;
    private $timecreated;
    private $timemodified;
    private $shortcoursename;


    /**
     * Constructs an instance of portfolioact scaffold
     * @param int $scaffoldid
     * @return object portfolioact_scaffold
     */

    public function __construct($scaffoldid) {

        global $DB;
        $data = $DB->get_record('portfolioact_scaffolds', array('id'=>$scaffoldid), '*');
         //false if no record#

        if ($data === false ) {
            throw new moodle_exception('thisscaffolddoesnotexist',
                'portfolioactmode_template');
        }

        if ($data !== false ) {
            $this->id = $data->id;
            $this->name = $data->name;
            $this->course = $data->course;
            $this->timecreated = $data->timecreated;
            $this->timemodified = $data->timemodified;

        }

        $this->DB = $DB;

    }



    /**
     * PHP magic setter for this class
     * @param $key
     * @param $value
     */

    public function __set($key, $value) {
        $this->$key = $value;
    }

    /**
     * PHP magic getter for this class
     * @param mixed $key
     */

    public function __get($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }


    /**
     * Return the coursename and courseshortname for this course
     *
     * @return mixed
     */

    public function get_course_name() {

        $rec = $this->DB->get_record('course', array('id'=>$this->course),  'fullname,shortname');
        return $rec;

    }

    /**
     * Return if scaffold actually has any files in it.
     *
     * If $ignoredirs is true, the default, it will only test for files
     * and a scaffold which has no files but some (empty) directories will be
     * reported as empty.
     *
     * @param boolean $ignoredirs
     * @return boolean
     */

    public function is_empty($ignoredirs = true) {

        global $DB;

        $fs = get_file_storage();
        $context = get_context_instance(CONTEXT_COURSE, $this->course);

         //TODO maybe make the component here and in /scaffold/designer a constant
        $fileinfo = array(
            'component' => 'mod_portfolioactmode_scaffold',
            'filearea' => 'scaffoldset',
            'itemid' => $this->id,
            'contextid' => $context->id);

        $isempty = $fs->is_area_empty($fileinfo['contextid'], $fileinfo['component'],
            $fileinfo['filearea'], $fileinfo['itemid'], $ignoredirs);

        return $isempty;
    }



    /**
     * Update the scaffold name
     *
     * @param string $name
     */

    public function updatename($name) {
        $rec = new stdClass();
        $rec->id = $this->id;
        $rec->name = $name;
        $rec->timemodified = time();
        $this->DB->update_record('portfolioact_scaffolds', $rec);
        $this->name = $name;
    }

    /**
     * Updates all fields on the scaffold
     *
     */

    public function save() {

        $save_scaffold = new stdClass();
        $save_scaffold->id = $this->id;
        $save_scaffold->name = $this->name;
        $save_scaffold->course = $this->course;
        $save_scaffold->timemodified = time();
        $this->DB->update_record('portfolioact_scaffolds', $save_scaffold);

    }

    /**
     * Updates the time modified field on a scaffold
     *
     */

    public function update() {
        $save_scaffold = new stdClass();
        $save_scaffold->id = $this->id;
        $save_scaffold->timemodified = time();
        $this->DB->update_record('portfolioact_scaffolds', $save_scaffold);
    }

    /**
     * Static method to delete a scaffold
     *
     *
     * @param int $scaffoldid
     * @return boolean
     */


    public static function delete($scaffoldid) {
        global $DB;

        $DB->delete_records_select('portfolioact_scaffolds', "id = ".$scaffoldid);

        return true;
    }

}



/**
 * Checks if browser is safe browser
 *
 * @return true, if browser is safe browser else false
 */


function portfolioactmode_scaffold_check_safe_browser() {
    return strpos($_SERVER['HTTP_USER_AGENT'], "SEB") !== false;
}

/**
 * Used to include custom Javascript for this module
 *
 */

function portfolioactmode_scaffold_get_js_module() {
    global $PAGE;
    return array(
        'name' => 'portfolioactmode_scaffold',
        'fullpath' => '/mod/portfolioact/mode/sacffold/module.js',
        'requires' => array('base', 'dom',  'io', 'node', 'json')
    );
}




/**
 * Gets list of scaffolds in the system for current course
 *
 * @param int $courseid
 * @param string $sort optional name of field to sort on
 * @return array an array of associative arrays - template ids and names
 */


function portfolioactmode_scaffold_get_scaffolds($courseid, $sort = 'id') {
    global $DB;

    $records = $DB->get_records('portfolioact_scaffolds',
        array('course' => $courseid), $sort, 'id, name');
     $scaffolds = array();

    foreach ($records as $scaffold) {
        $scaffolds[$scaffold->id] = $scaffold->name;

    }

    return $scaffolds;

}




/**
 * Gets list of scaffolds in the system for current course with used count
 *
 * Gets list of scaffolds in the system for current course with used count and returns array of
 * record objects or empty array if none. The function is also aware of case of scaffolds used in
 * templates and those are added to the count.
 *
 * @param int $courseid
 * @param string $sort optional name of field to sort on
 * @return array of objects
 *
 */

function portfolioactmode_scaffold_get_scaffolds_with_count($courseid, $sort = 'id') {
    global $DB, $CFG;

    //get all scaffolds

    $scaffolds = $DB->get_records('portfolioact_scaffolds', array('course' => $courseid),
        $sort, 'id, name, timecreated, timemodified');

    //get those which have an activity mode of scaffold
    //this won't get those which have never been used (have no settings)
    //or have an entry in the settings table but their activity is set to template
    //but that is fine as these ones will be used = zero
    $sql = <<<EOD
    SELECT  sc.id , p.modename , COUNT(sc.id) as used  FROM
    {portfolioact_scaffolds} sc
    LEFT JOIN {portfolioact_scaff_settings} s ON s.scaffold  = sc.id
    LEFT JOIN {portfolioact} p ON s.actid  = p.id
    WHERE sc.course = ? AND p.modename = 'scaffold' GROUP BY sc.id, p.modename

EOD;

    $scaffolds2 = $DB->get_records_sql($sql, array($courseid));
    foreach ($scaffolds as $scaffoldid => $scaffold) {
        if (array_key_exists($scaffoldid, $scaffolds2)   ) {
            $scaffold->used = $scaffolds2[$scaffoldid]->used;
        } else {
              $scaffold->used = 0;
        }
    }

    //additonally scaffolds may be used with template activities
    //and in this case too we have to check that that activty is in fact a template activity

    $sql = <<<EOD
    SELECT s.id,  COUNT(s.id) as used  FROM
    {portfolioact_scaffolds} s
    LEFT JOIN {portfolioact_tmpl_settings} st ON s.id = st.scaffold
    LEFT JOIN {portfolioact} p on p.id = st.actid
    WHERE s.course = ? AND st.scaffold IS NOT NULL AND p.modename = 'template'
    GROUP BY s.id

EOD;

    $scaffoldsinatemplate = $DB->get_records_sql($sql, array($courseid));

    foreach ($scaffolds as $scaffoldid => $scaffold) {
        if (array_key_exists($scaffoldid, $scaffoldsinatemplate)   ) {
            $scaffold->used = $scaffold->used  + $scaffoldsinatemplate[$scaffoldid]->used;
        }
    }
    return $scaffolds;

}




/**
 * Return an array of activities which use this scaffold.
 *
 * Return an array of activities which use this scaffold. Also includes template activities
 * which use this scaffold.
 *
 * @param int $scaffid
 * @param int $courseid
 * @return array of objects
 */


function portfolioactmode_scaffold_inactivities($scaffid, $courseid) {

    global $CFG, $DB;

    $activities = array();

     //get those which have an activity mode of scaffold
    //this won't get those which have never been used (have no settings)
    //or have an entry in the settings table but their activity is set to template
    //but that is fine as these ones will be used = zero
    $sql = <<<EOD
    SELECT  p.id, p.name   FROM
    {portfolioact_scaffolds} sc
    LEFT JOIN {portfolioact_scaff_settings} s ON s.scaffold  = sc.id
    LEFT JOIN {portfolioact} p ON s.actid  = p.id
    WHERE sc.course = ? AND p.modename = 'scaffold' AND sc.id = ?

EOD;

    $activities1 = $DB->get_records_sql($sql, array($courseid, $scaffid));//empty array or array of objects
    //additonally scaffolds may be used with template activities
    //and in this case too we have to check that that activty is in fact a template activity

    $sql = <<<EOD
    SELECT  p.id, p.name FROM
    {portfolioact_scaffolds} s
    LEFT JOIN {portfolioact_tmpl_settings} st ON s.id = st.scaffold
    LEFT JOIN {portfolioact} p on p.id = st.actid
    WHERE s.course = ? AND st.scaffold IS NOT NULL
    AND p.modename = 'template' AND  s.id = ?

EOD;

    $activities2 = $DB->get_records_sql($sql, array($courseid, $scaffid));//empty array or array of objects

    $scaffmode = get_string('scaffmode', 'portfolioactmode_scaffold');
    $tmplmode = get_string('tmplmode', 'portfolioactmode_scaffold');

    foreach ($activities1 as $id => $activity) {
        $activity->mode = $scaffmode;
    }

    foreach ($activities2 as $id => $activity) {
        $activity->mode = $tmplmode;
    }

    return array_merge($activities1, $activities2);
}
