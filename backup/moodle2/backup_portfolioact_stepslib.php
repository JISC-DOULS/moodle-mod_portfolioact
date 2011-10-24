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
 * Define all the backup steps that will be used by the backup_portfolioact_activity_task
 */

/**
 * Define the complete portfolioact structure for backup, with file and id annotations
 */
class backup_portfolioact_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // $overwrite_templates = $this->get_setting_value('overwrite_templates');

        // Define each element separated
        $portfolioact = new backup_nested_element('portfolioact', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified', 'modename',
            'savetypes'));

        $this->add_subplugin_structure('portfolioactmode', $portfolioact, true);

        // Define sources
        $portfolioact->set_source_table('portfolioact', array('id' => backup::VAR_ACTIVITYID));

        // Return the root element (portfolioact), wrapped into standard activity structure
        return $this->prepare_activity_structure($portfolioact);
    }
}
