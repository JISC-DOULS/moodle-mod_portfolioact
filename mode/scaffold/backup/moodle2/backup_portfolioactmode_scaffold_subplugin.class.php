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
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2010 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup grading evaluation method 'Comparison with the best assessment'
 *
 * This evaluator just stores a single integer value - the recently used comparison
 * strictness factor. It adds its XML data to workshop tag.
 */
class backup_portfolioactmode_scaffold_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to workshop element
     */
    protected function define_portfolioact_subplugin_structure() {

        global $DB;
        $ret = $DB->record_exists('portfolioact', array('modename' => 'scaffold', 'id' => $this->task->get_activityid()) );
        if (! $ret ) {
            return;
        }

        // create XML elements
        $subplugin = $this->get_subplugin_element(); // virtual optigroup element
        $subplugin_table_settings = new backup_nested_element('portfolioact_scaffold_settings', array('id'), array('scaffold'));
        $subplugin_table_scaffolds = new backup_nested_element('portfolioact_scaffold',
            array('id'), array('name', 'timecreated', 'timemodified'));

        // connect XML elements into the tree
        $subplugin->add_child($subplugin_table_settings);
        $subplugin_table_settings->add_child($subplugin_table_scaffolds);

        // set source to populate the data
        $subplugin_table_settings->set_source_table('portfolioact_scaff_settings', array('actid' => backup::VAR_ACTIVITYID));
        $subplugin_table_scaffolds->set_source_table('portfolioact_scaffolds', array('id' => '../scaffold'));

        $context = context_course::instance($this->task->get_courseid());

        $subplugin_table_scaffolds->annotate_files('portfolioactmode_scaffold', 'scaffoldset', 'id', $context->id);

        //the return value is not checked
        //return $subplugin;
    }
}
