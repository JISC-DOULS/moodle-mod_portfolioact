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
 * @copyright  The Open University 2011
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup grading evaluation method 'Comparison with the best assessment'
 *
 * This evaluator just stores a single integer value - the recently used comparison
 * strictness factor. It adds its XML data to workshop tag.
 */




class backup_portfolioactmode_template_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to portfolioact element
     */
    protected function define_portfolioact_subplugin_structure() {

        global $DB, $CFG;

        $pa_id = $this->task->get_activityid();
        $ret = $DB->record_exists('portfolioact', array('modename' => 'template', 'id' => $pa_id) );
        if (! $ret ) {
            return;
        }

        $userinfo = $this->get_setting_value('userinfo');

        // create XML elements
        $subplugin = $this->get_subplugin_element(); // virtual optigroup element

        $subplugin_table_settings = new backup_nested_element('portfolioact_tmpl_settings',
            array('id'), array( 'scaffold', 'actid', 'template', 'datamode', 'page', 'showsave'));
        $subplugin_table_template = new backup_nested_element('portfolioact_template',
            array('id'), array( 'name', 'timecreated', 'timemodified', 'pageorder'));
        $subplugin_table_pages = new backup_nested_element('portfolioact_template_pages',
            array('id'), array( 'name', 'itemorder'));
        $subplugin_table_items = new backup_nested_element('portfolioact_template_items',
            array('id'), array( 'name', 'type', 'settings', 'reference'));
        $subplugin_table_optional_scaffold = new backup_nested_element('portfolioact_scaffold',
            array('id'), array('name', 'timecreated', 'timemodified'));
        $subplugin_table_entries = new backup_nested_element('portfolioact_template_entries',
            array('id'), array(  'userid', 'actid', 'itemid', 'entry', 'timemodified'));

        //structure for referenced elements
        $referenced_wrapper = new backup_nested_element('referenced_templates');
        $subplugin_table_referenced_template = new backup_nested_element('portfolioact_referenced_template',
            array('id'), array( 'name', 'timecreated', 'timemodified', 'pageorder'));
        $subplugin_table_referenced_pages = new backup_nested_element('portfolioact_referenced_template_pages',
            array('id'), array( 'name', 'itemorder', 'template'));
        $subplugin_table_referenced_items = new backup_nested_element('portfolioact_referenced_template_items',
            array('id'), array( 'name', 'type', 'settings', 'reference', 'page'));
        $subplugin_table_referenced_entries = new backup_nested_element('portfolioact_referenced_template_entries',
            array('id'), array(  'userid', 'actid', 'itemid', 'entry', 'timemodified'));

        // connect XML elements into the tree
        // $subplugin->add_child($subplugin_wrapper);
        $subplugin->add_child($subplugin_table_settings);
        $subplugin_table_settings->add_child($subplugin_table_template);
        $subplugin_table_settings->add_child($subplugin_table_optional_scaffold);
        $subplugin_table_template->add_child($subplugin_table_pages);
        $subplugin_table_pages->add_child($subplugin_table_items);

        // set source to populate the data
        $subplugin_table_settings->set_source_table('portfolioact_tmpl_settings', array('actid' => backup::VAR_ACTIVITYID));
        $subplugin_table_optional_scaffold->set_source_table('portfolioact_scaffolds', array('id' => '../scaffold'));
        $subplugin_table_template->set_source_table('portfolioact_template', array('id' => '../template'));
        $subplugin_table_pages->set_source_table('portfolioact_tmpl_pages', array('template' => '../id'));
        $subplugin_table_items->set_source_table('portfolioact_tmpl_items', array('page' => '../id'));

        if ($userinfo) {

            //we have to work out the datamode first
            $current_actid = $this->task->get_activityid();
            $rec = $DB->get_record('portfolioact_tmpl_settings', array('actid'=>$current_actid), 'datamode');
            $subplugin_table_items->add_child($subplugin_table_entries);
            $datamode = $rec->datamode;
            if ($datamode == 0) {
                $subplugin_table_entries->set_source_table('portfolioact_tmpl_entries',
                    array('itemid'=>'../id', 'actid'=> backup::VAR_ACTIVITYID));
            } else {
                $subplugin_table_entries->set_source_table('portfolioact_tmpl_entries',
                    array('itemid'=>'../id', 'actid' => backup_helper::is_sqlparam(null)));
            }

            $subplugin_table_entries->annotate_ids('user', 'userid');
        }

        $context = get_context_instance(CONTEXT_COURSE, $this->task->get_courseid());
        $subplugin_table_optional_scaffold->annotate_files('mod_portfolioactmode_scaffold', 'scaffoldset', 'id', $context->id);

        //now - get the templates which may be referenced, recursively

        $rec = $DB->get_record('portfolioact_tmpl_settings', array('actid'=>$pa_id), 'template');
        $referenced_templates = array();
        portfolioactmode_template_get_referenced_templates($rec->template, $referenced_templates);
        //shldn't be necessary
        $referenced_templates = array_unique($referenced_templates);

        if (! empty($referenced_templates)) {
            $where = implode(",", $referenced_templates);
            $subplugin->add_child($referenced_wrapper);
            $referenced_wrapper->add_child($subplugin_table_referenced_template);
            $subplugin_table_referenced_template->add_child($subplugin_table_referenced_pages);
            $subplugin_table_referenced_pages->add_child($subplugin_table_referenced_items);

            $subplugin_table_referenced_template->set_source_sql
            ('SELECT * FROM {portfolioact_template} WHERE id IN (' . $where . ')', array());
            //var_dump($where);
            $subplugin_table_referenced_pages->set_source_table('portfolioact_tmpl_pages', array('template' => '../id'));
            $subplugin_table_referenced_items->set_source_table('portfolioact_tmpl_items', array('page' => '../id'));

            //same issue with the datamode
            //note that here we get all user data for referenced entries
            //will/may include user data for users who are not represented in the current activity
            //who will also be imported on a restore

            if ($userinfo) {
                $subplugin_table_referenced_items->add_child($subplugin_table_referenced_entries);
                if ($datamode == 0) {
                    $subplugin_table_referenced_entries->set_source_table('portfolioact_tmpl_entries',
                        array('itemid'=>'../id', 'actid'=> backup::VAR_ACTIVITYID));
                } else {
                    $subplugin_table_referenced_entries->set_source_table('portfolioact_tmpl_entries',
                        array('itemid'=>'../id', 'actid' => backup_helper::is_sqlparam(null)));
                }

                $subplugin_table_referenced_entries->annotate_ids('user', 'userid');
            }

        }
        //the return value is not checked
        //return $subplugin;
    }
}



/**
 * Returns referenced templates for an Activity
 *
 * Returns referenced templates for an Activity. Works recursively.
 *
 *
 * @param int $pad_id
 * @return array of template ids which are referenced
 */

function portfolioactmode_template_get_referenced_templates($templateid, &$referenced_templates) {
    global $DB, $CFG;

    $reference_items = portfolioactmode_template_get_referenced_items_from_template($templateid);

    foreach ($reference_items as $itemid) {
        $reftemplateid = portfolioactmode_template_get_template_id_from_item_id($itemid);

        if (! in_array($reftemplateid, $referenced_templates)
        && ($reftemplateid != $templateid)) {

            $referenced_templates[] = $reftemplateid;
            portfolioactmode_template_get_referenced_templates($reftemplateid, $referenced_templates);
        }
    }
}


/**
 * Returns the id of the template which the item belongs to
 *
 * @param int $itemid
 * @return int
 */


function portfolioactmode_template_get_template_id_from_item_id($itemid) {

    global $DB, $CFG;

    $sql = <<<EOF

    SELECT t.id FROM {portfolioact_template} t
    INNER JOIN {portfolioact_tmpl_pages} p ON p.template = t.id
    INNER JOIN {portfolioact_tmpl_items} i ON i.page = p.id
    WHERE i.id = ?

EOF;

    $rec = $DB->get_record_sql($sql, array($itemid));

    if ($rec === false) {
        throw new moodle_exception('unexpected', 'portfolioactmode_template');
    }

    return $rec->id;

}

/**
 * Returns a list of referenced items in a template
 *
 * @param int $itemid
 * @return array
 */

function portfolioactmode_template_get_referenced_items_from_template($templateid) {

    global $DB, $CFG;

    $sql = <<<EOF

    SELECT i.id , i.reference FROM {$CFG->prefix}portfolioact_template t
    INNER JOIN {portfolioact_tmpl_pages} p ON p.template = t.id
    INNER JOIN {portfolioact_tmpl_items} i ON i.page = p.id
    WHERE t.id = ? AND i.type = 'reference'

EOF;

    $reference_items = $DB->get_records_sql($sql, array($templateid));
    $reference_items_ids = array();
    foreach ($reference_items as $itemrec) {
        $reference_items_ids[] = $itemrec->reference;
    }

    return $reference_items_ids;

}
