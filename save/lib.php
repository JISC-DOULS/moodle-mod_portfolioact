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
 * Library for portfolioact mode sub-plugins
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $CFG;
require_once($CFG->dirroot.'/mod/portfolioact/mode/lib.php');


/**
 * Class to add functionality to the basic core google api class
 *
 *  *
 * @package portfolioact
 * @subpackage portfolioact_save
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Class for save plugins
 *
 * Helper class to support saving
 *
 * @package portfolioact
 * @subpackage portfolioact_save
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



class portfolioact_save {

    const TYPE_DISABLED = 0;
    const TYPE_ENABLED = 1;
    const TYPE_FORCED = 2;

    /**
     * Returns an export module object
     *
     * @param string $type
     * @param int $actid
     * @param int $cmid
     * @return mixed
     */
    public static function get_export_module($type, $actid, $cmid) {
        global $CFG;
        include_once($CFG->dirroot.'/mod/portfolioact/save/'.$type.'/lib.php');
        $class     = 'portfolioact_'.$type.'_save';
        if (! class_exists($class)) {
            throw new coding_exception('The requested export plugin appears to be missing.');
        }

        return $class::get_instance($actid, $cmid);

    }


    public function __construct() {

    }

    /**
     * Return available sub-plugins for given activity
     *
     * @param int $actid - id of activity in portfolioact table
     * @return array of save types
     */

    public static function get_save_types($actid = null) {
        global $DB;
        $savesavail = get_plugin_list('portfolioactsave');

        $saves = array();

        $actsetting = '';
        if ($actid) {
            //Do here so only make 1 db call for all types
            if ($result = $DB->get_record('portfolioact', array('id'=>$actid), 'savetypes')) {
                $actsetting = $result->savetypes;
            }
        }

        foreach ($savesavail as $savetype => $filepath) {
            $saveinfo = new stdClass();
            $saveinfo->name = get_string('pluginname', 'portfolioactsave_' . $savetype);
            if ($actid) {
                $saveinfo->actenabled = self::save_type_enabled($savetype, $actid, $actsetting);
            }
            $saveinfo->siteenabled =
                get_config('portfolioactsave_' . $savetype, $savetype . '_enabled');
            $saves[$savetype] = $saveinfo;
        }

        return $saves;
    }

    /**
     * Returns if type is available (optionally against activity)
     *
     * @param string $type
     * @param int $actid activity id in portfolio act table
     * @param string $actsetting savetypes field from db for actid
     * @return boolean
     */

    public static function save_type_enabled($type, $actid = null, $actsetting = null) {
        global $DB;

        $configset = get_config('portfolioactsave_' . $type, $type . '_enabled');
        //First check site wide setting - is type enabled?
        if ($configset == self::TYPE_DISABLED) {
            return false;//never enabled
        } else if ($configset == self::TYPE_FORCED) {
            return true;//always enabled
        }

        if ($actid) {
            //Check activity setting - is type in setting so turned on
            if ($actsetting == null) {
                if ($rec = $DB->get_record('portfolioact', array('id'=>$actid), 'savetypes')) {
                    $actsetting = $rec->savetypes;
                } else {
                    $actsetting = '';//TODO Throw error or ignore?
                }
            }
            $enabledarr = explode(',', $actsetting);
            if (in_array($type, $enabledarr)) {
                return true;
            } else {
                return false;
            }
        }

        //If not a specific activity
        return true;
    }

    public static function save_type_settings_choice() {
        return array(
        self::TYPE_DISABLED => get_string('save_disabled', 'portfolioact'),
        self::TYPE_ENABLED => get_string('save_enabled', 'portfolioact'),
        self::TYPE_FORCED => get_string('save_force', 'portfolioact')
        );
    }

}

/**
 * Abstract Class for save plugins
 *
 * Extendable class to define what an export plugin must support
 *
 * @package portfolioact
 * @subpackage portfolioact_save
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



abstract class portfolioact_save_plugin {

    protected $exportdata;
    protected $metadata;
    protected $savetype;
    protected $mode;
    public $errormessage;

    /**
     * All subclasses need to implement a method like this:
     *
     * A singleton contsructor
     * @param int $actid
     */
    // public static function get_instance($actid, $cmid);

    /**
     * Each save plugin must provide an export method.
     *
     * @param mixed $option
     */

    abstract public function export_data($option);

    /**
     * Constructor
     * @param int $actid
     * @param int $cmid
     */

    protected function __construct($actid, $cmid) {
        global $DB;
        $portfolioact = $DB->get_record('portfolioact',
        array('id' => $actid), '*', MUST_EXIST);
        $this->portfolioact = $portfolioact;

    }

    /**
     * Each class must implement a save button which initiates the process
     *
     * @param int $cmid course module id
     * @return string
     */

    abstract public function output_save_button();

    /**
     * A title for the save
     * @return string
     */

    abstract public function get_title();

    /**
     * Obtain the data from the mode plugin
     *
     * Obtain the data from the mode plugin in the format in which it comes
     * and sets it onto exportdata member. (The problem with this is that
     * the save classes which implement the various save modes e.g. file and google have to know
     * what the original mode class was (e.g. template or scaffold) and fork the code accrordingly.
     * It would be MUCH nicer if this method would massage the data into a shared,
     * intermediate format e.g. for extensability - prob.  based on the Moodle File API
     * The issue is one of efficiency. TODO).
     *
     *
     * @param string $mode
     * @param int $actid
     * @return boolean
     *
     */

    public function retrieve_data($mode, $actid) {
        global $CFG, $DB;
        $this->mode = $mode;
        $class = 'portfolioact_mode_'.$mode;
        //cld be in any format returned by the mode
        //plugins template - html string. scaffold - array of files

        if (! class_exists($class)) {
            include($CFG->dirroot.'/mod/portfolioact/mode/'.$mode.'/lib.php');
        }

        $this->exportdata = $class::get_data_for_export($actid);

        if (empty($this->exportdata)) {
            $this->error_message = 'error';//TODO
            return false;
        }

        $modeinstance = $class::get_mode_operational_instance($actid);
        $rec = $DB->get_record('course', array('id'=>$modeinstance->course), 'shortname');
        $this->shortcoursename = $rec->shortname;
        $this->modeinstancename = $modeinstance->name;
        $this->modeinstance = $modeinstance;

        return true;
    }






}


//helper funcs

/**
 * Return the path to the temp directory on this system
 *
 * @return string
 */

function portfolioactsave_get_temp_file_name() {

    global $CFG;

    $ret = make_upload_directory('/temp/portfolioact');

    if ($ret === false) {
        return false;
    }

    $tmpfile = tempnam($ret, 'scaffold');

    return $tmpfile;

}
