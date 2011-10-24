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
 * TODO This is a one-line short description of the file
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_portfolioact_activity_task
 */

/**
 * Structure step to restore one portfolioact activity
 */
class restore_portfolioact_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $portfolioact = new restore_path_element('portfolioact', '/activity/portfolioact');
        $paths[] = $portfolioact;

        // $overwrite_templates = $this->get_setting_value('overwrite_templates');

        $this->add_subplugin_structure('portfolioactmode', $portfolioact);

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_portfolioact($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        //$data->timeopen = $this->apply_date_offset($data->timeopen);
        //$data->timeclose = $this->apply_date_offset($data->timeclose);
        //$data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timemodified = time();
        $data->timecreated = time();

        // insert the portfolioact record
        $newitemid = $DB->insert_record('portfolioact', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }


    protected function after_execute() {

    }
}
