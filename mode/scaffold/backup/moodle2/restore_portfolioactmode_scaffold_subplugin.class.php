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
 * @package    portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore subplugin class that provides the necessary information
 * needed to restore one portfolioactmode_scaffold subplugin.
 */
class restore_portfolioactmode_scaffold_subplugin extends restore_subplugin {


    private $old_scaffoldid = null;


    ////////////////////////////////////////////////////////////////////////////
    // mappings of XML paths to the processable methods
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the paths to be handled by the subplugin at workshop level
     */
    protected function define_portfolioact_subplugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('portfolioactmode_scaffold',
            '/activity/portfolioact/portfolioact_scaffold_settings/portfolioact_scaffold');
        $paths[] = new restore_path_element('portfolioactmode_scaffold_settings',
            '/activity/portfolioact/portfolioact_scaffold_settings');

        return $paths; // And we return the interesting paths
    }

    ////////////////////////////////////////////////////////////////////////////
    // defined path elements are dispatched to the following methods
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Processes one scaffold_settings element
     *
     * @param mixed $data
     */
    public function process_portfolioactmode_scaffold_settings($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->scaffold;
        $data->actid = $this->get_new_parentid('portfolioact');
        $data->scaffold = 0; //we don't know it yet
        $newid = $DB->insert_record('portfolioact_scaff_settings', $data);
        $orig_course_context = $this->task->get_info()->original_course_contextid;
        $this->set_mapping('portfolioactmode_scaffold_settings', $oldid, $newid, false, $orig_course_context );

    }


    /**
     * Processes one scaffold element
     *
     * @param mixed $data
     */

    public function process_portfolioactmode_scaffold($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $mappingid = $this->get_mappingid('portfolioact_scaffold', $oldid);

        if ($mappingid) {

            //update settings table with new scaffold - re-used.
            $settings = new stdClass();
            $settings->id = $this->get_new_parentid('portfolioactmode_scaffold_settings');
            $settings->scaffold = $mappingid;
            $settings->timemodified = time();
            $DB->update_record('portfolioact_scaff_settings', $settings);
            $this->old_scaffoldid =  $mappingid;

        } else {

            $this->old_scaffoldid = $oldid;
            $data->course = $this->task->get_courseid();
            $data->timemodified = time();
            $data->timecreated = time();
            $newitemid = $DB->insert_record('portfolioact_scaffolds', $data);
            $orig_course_context = $this->task->get_info()->original_course_contextid;
            $this->set_mapping('portfolioact_scaffold', $oldid, $newitemid, true, $orig_course_context );
            //update settings table with new scaffold
            $settings = new stdClass();
            $settings->id = $this->get_new_parentid('portfolioactmode_scaffold_settings');
            $settings->scaffold = $newitemid;
            $DB->update_record('portfolioact_scaff_settings', $settings);

        }

    }

    public function after_execute_portfolioact() {

        if (! is_null($this->old_scaffoldid)) {
            $orig_course_context = $this->task->get_info()->original_course_contextid;
            $this->add_related_files('portfolioactmode_scaffold', 'scaffoldset',
                'portfolioact_scaffold' ,  $orig_course_context, $this->old_scaffoldid );

        }

    }



}
