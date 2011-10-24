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


require_once(dirname(dirname(__FILE__)).'/locallib.php');

/**
 * Library for portfolioact mode sub-plugins
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_mode {
    //Static mode class instance
    static protected $modeinstance;
    //name of current mode
    static protected $mode;

    /**
     * Returns array of available modes
     * [mode] => mode display name
     */
    static public function return_modes() {
        $modesavail = get_plugin_list('portfolioactmode');
        $modes = array();
        foreach ($modesavail as $mode => $modpath) {

            $modes[$mode] = get_string('portfolioactmode_'.$mode, 'portfolioactmode_'.$mode);
        }
        return $modes;
    }


    /**
     * Returns the mode name from the actid
     *
     * @param int $actid
     * @return string
     */

    static public function get_plugin_mode($actid) {
        global $DB;
        $portfolioact = $DB->get_record('portfolioact', array('id' => $actid), '*', MUST_EXIST);
        return $portfolioact->modename;
    }

    /**
     * Returns instance of mode as specified by name (optional if already called)
     * Static - so if already created will return instance
     * @param $actid null|int
     * @param $name string Name of the mode plugin (folder name)
     * @returns portfolioact_mode_plugin
     */
    static public function get_mode_instance($actid, $name = null) {
        global $CFG, $PAGE;// because we require other libs here
        //check for a bad call
        if (is_null(self::$modeinstance) && $name == null) {
            throw new coding_exception('Invalid call to get_mode_instance: mode not set');
        }
        //if instance not set or different mode needed create instance
        if (is_null(self::$modeinstance) ||
            (isset(self::$mode) && $name != null && $name != self::$mode)) {
            $modelib = dirname(__FILE__) . '/' . $name . '/lib.php';
            if (is_readable($modelib)) {
                require_once($modelib);
            } else {
                throw new coding_exception('the mode subplugin must contain library ' . $modelib);
            }
            $classname = 'portfolioact_mode_' . $name;

            if (!in_array('portfolioact_mode_plugin', class_parents($classname))) {
                throw new coding_exception($classname .
                    ' does not implement mode sub-plugin class');
            }

            if (is_null(self::$modeinstance)) {
                self::$mode = $name;
                self::$modeinstance = new $classname($name);
            } else {
                //Assume wanted different method to current one module is using
                return new $classname($name);
            }
        }
        return self::$modeinstance;
    }

}

/**
 * This class should be extended by the mode sub-plugins
 * Should be called portfolioact_mode_* (* = folder name)
 * in mode/* /lib.php
 * @author The Open University
 *
 */


abstract class portfolioact_mode_plugin extends portfolioact {

    public $renderer;

    /**
     * Return the table name for the mode
     *
     * Return the table name for the mode. This is necessary because
     * due to limits (Moodle limits) on the length of table names
     * we cannot assume that any tables for the mode use
     * the mode name without shortening it.
     *
     * @return string
     *
     */

    public abstract static function table_name();

    public function __construct($name) {

        global $PAGE;
        //Auto add in renderer (these should extend mod_portfolioact_renderer)

        parent::__construct();

        if (is_readable(dirname(__FILE__)  . '/' . $name . '/renderer.php') ) {
            $this->renderer = $PAGE->get_renderer('portfolioactmode_' . $name);
        }
    }


    /*
     * Return an instance of the mode actual class from the activity id
     *
     * Usage: when the activity id is known but we may not be on that page
     *
     * @param int $actid
     * @return mixed
     */

    public abstract static function get_mode_operational_instance($actid);

    /*
     * Return the data of the mode together with the metadata (course name etc)
     *
     * Usage: when the activity id is known but we may not be on that page
     *
     *
     * @param int $actid
     * @return mixed exportdata
     *
     */

    public abstract static function get_data_for_export($actid);

    /**
     * Produces a menu item for the side navigation for edit settings
     *
     * @param mixed $node
     */

    public abstract function nav_menu_item(&$node);

    /**
     * Add a new element of the mode type to a given course
     *
     * @param string $name
     * @param int $courseid
     * @return boolean
     */

    public abstract function add_new($name, $courseid);


}
