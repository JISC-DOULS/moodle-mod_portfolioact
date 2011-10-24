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



class restore_portfolioactmode_template_subplugin extends restore_subplugin {


    //private $storedsettingsid;
    private $oldpageorder = array();
    private $olditemorder = array();
    private $newtemplateid;
    private $referenceitems = array();
    private $stopprocessing = false;
    private $oldscaffoldid = null;
    private $newsettings = false;




    ////////////////////////////////////////////////////////////////////////////
    // mappings of XML paths to the processable methods
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the paths to be handled by the subplugin at workshop level
     */


    protected function define_portfolioact_subplugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('portfolioactmode_tmpl_settings', '/activity/portfolioact/portfolioact_tmpl_settings');
        $paths[] = new restore_path_element('portfolioactmode_template',
            '/activity/portfolioact/portfolioact_tmpl_settings/portfolioact_template');
        $paths[] = new restore_path_element('portfolioactmode_pages',
            '/activity/portfolioact/portfolioact_tmpl_settings/portfolioact_template/portfolioact_template_pages');
        $paths[] = new restore_path_element('portfolioactmode_items',
            '/activity/portfolioact/portfolioact_tmpl_settings/portfolioact_template/portfolioact_template_pages/portfolioact_template_items');
        $paths[] = new restore_path_element('portfolioactmode_entries',
            '/activity/portfolioact/portfolioact_tmpl_settings/portfolioact_template/portfolioact_template_pages/portfolioact_template_items/portfolioact_template_entries');
        $paths[] = new restore_path_element('portfolioactmode_scaffold',
            '/activity/portfolioact/portfolioact_tmpl_settings/portfolioact_scaffold');

        //referenced templates
        $paths[] = new restore_path_element('portfolioactmode_referenced_template',
            '/activity/portfolioact/referenced_templates/portfolioact_referenced_template');
        $paths[] = new restore_path_element('portfolioactmode_referenced_pages',
            '/activity/portfolioact/referenced_templates/portfolioact_referenced_template/portfolioact_referenced_template_pages');
        $paths[] = new restore_path_element('portfolioactmode_referenced_items',
            '/activity/portfolioact/referenced_templates/portfolioact_referenced_template/portfolioact_referenced_template_pages/portfolioact_referenced_template_items');
        $paths[] = new restore_path_element('portfolioactmode_referenced_entries',
            '/activity/portfolioact/referenced_templates/portfolioact_referenced_template/portfolioact_referenced_template_pages/portfolioact_referenced_template_items/portfolioact_referenced_template_entries');

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
    public function process_portfolioactmode_tmpl_settings($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->template;

        $oldactid = $data->actid;

        $data->actid = $this->get_new_parentid('portfolioact');

        $this->portfolioactmode_scaffold->oldid = $data->scaffold;
        $data->scaffold = null; //we don't know it yet and it may just be null anyway
        $data->template = 0; //we don't know it yet
        $newid = $DB->insert_record('portfolioact_tmpl_settings', $data);
        $orig_course_context = $this->task->get_info()->original_course_contextid;
        $this->set_mapping('portfolioactmode_tmpl_settings', $data->id, $newid, false, $orig_course_context );
        $this->newsettings = true;

    }

    /**
     * Processes one template element
     *
     * @param mixed $data
     */

    public function process_portfolioactmode_template($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $mappingid = $this->get_mappingid('portfolioactmode_template', $oldid);
        //will be false or a string with the number in

        if ($mappingid) {//use the same template

            //update the settings table with the new template id
            $settings = new stdClass();
            $settings->id = $this->get_new_parentid('portfolioactmode_tmpl_settings');
            $settings->template = $mappingid;
            $DB->update_record('portfolioact_tmpl_settings', $settings);

        } else {

            $data->course = $this->task->get_courseid();
            $data->timecreated = time();
            $data->timemodified = time();
            $savedpageorder = $data->pageorder;
            $data->pageorder = '';//we don't know the new page ids yet
            $newitemid = $DB->insert_record('portfolioact_template', $data);

            if ( is_null($savedpageorder) || ($savedpageorder === "") ) {
                $this->oldpageorder[$newitemid] = null;
            } else {
                $this->oldpageorder[$newitemid] = explode(",", $savedpageorder);
            }
            //handles possible error case of empty values in the order string. should not happen. but.
            $this->oldpageorder = array_filter($this->oldpageorder, "portfolioactmode_template_removeblanks");

            $orig_course_context = $this->task->get_info()->original_course_contextid;
            $this->set_mapping('portfolioactmode_template', $oldid, $newitemid, false, $orig_course_context );

            //update the settings table with the new template id
            $settings = new stdClass();
            $settings->id = $this->get_new_parentid('portfolioactmode_tmpl_settings');;
            $settings->template = $newitemid;
            $DB->update_record('portfolioact_tmpl_settings', $settings);

        }

    }


    /**
     * Process one page element
     *
     * @param mixed $data
     */

    public function process_portfolioactmode_pages($data) {
        global $DB;

        $newtemplateid = $this->get_new_parentid('portfolioactmode_template');

        //we did not set a mapping for a new one as we are re-using the template and hence its pages
        if (is_null($newtemplateid)) {
            return;
        }

        $data = (object)$data;
        $oldid = $data->id;
        $data->template = $newtemplateid;

        $saveditemorder = $data->itemorder;
        $data->itemorder = '';//we don't know the new itemids yet
        $newitemid = $DB->insert_record('portfolioact_tmpl_pages', $data);

        if (is_null($saveditemorder) || ($saveditemorder === "")) {
            $this->olditemorder[$newitemid] = null;
        } else {
            $this->olditemorder[$newitemid] = explode(",", $saveditemorder);
            $this->olditemorder[$newitemid] = array_filter($this->olditemorder[$newitemid], "portfolioactmode_template_removeblanks");
        }

        $orig_course_context = $this->task->get_info()->original_course_contextid;
        $this->set_mapping('portfolioactmode_pages', $oldid, $newitemid, false, $orig_course_context );

    }


    /**
     * Processed one item element
     *
     * @param mixed $data
     */

    public function process_portfolioactmode_items($data) {
        global $DB;

        $newpageid = $this->get_new_parentid('portfolioactmode_pages');

        if (is_null($newpageid)) {
            return;
        }

        $data = (object)$data;
        $oldid = $data->id;
        $data->page = $newpageid;
        $newitemid = $DB->insert_record('portfolioact_tmpl_items', $data);

        //if the type if a reference the one it refers to will also be a newitemid
        //however since that is sorted in the referenced_templates section
        //we may not have the mapping yet!
        if ($data->type == 'reference') {
            $this->referenceitems[] = $newitemid;
        }

        $orig_course_context = $this->task->get_info()->original_course_contextid;
        $this->set_mapping('portfolioactmode_items', $oldid, $newitemid, false, $orig_course_context );
    }


    /**
     * Process one entry element
     *
     * @param mixed $data
     *
     */

    public function process_portfolioactmode_entries($data) {
        global $DB;
        if ( $this->get_setting_value('userinfo') == 0) {
            return;
        }

        $data = (object)$data;
        $oldid = $data->id;
        //$data->timemodified = time();

        $itemid = $this->get_new_parentid('portfolioactmode_items');

        if (! is_null($itemid)) {
            $data->itemid = $itemid;
        } else {//no new item; we already have one, map it - occurs in repeated template case
            //means we still write the data for a repeated template as it may belong to
            //a different activity even through we don't re-write the template/pages/items
            $data->itemid = $this->get_mappingid('portfolioactmode_items', $data->itemid);
        }

        if (! is_null($data->actid)) {
            $data->actid = $this->get_new_parentid('portfolioact');
        }

        $data->userid = $this->get_mappingid('user', $data->userid);
        $DB->insert_record('portfolioact_tmpl_entries', $data);

    }


    /**
     * Process one scaffold element
     *
     * @param mixed $data
     */


    public function process_portfolioactmode_scaffold($data) {
        global $DB;

        /*
         $data = (object)$data;
         $oldid = $data->id;
         $this->oldscaffoldid = $oldid;
         $data->course = $this->task->get_courseid();
         $data->timemodified = time();
         $newitemid = $DB->insert_record('portfolioact_scaffolds', $data);

         //for files table
         $orig_course_context = $this->task->get_info()->original_course_contextid;
         $this->set_mapping('portfolioact_scaffold', $oldid, $newitemid, true, $orig_course_context );

         $settings = new stdClass();
         $settings->id = $this->get_new_parentid('portfolioactmode_tmpl_settings');
         $settings->scaffold = $newitemid;

         $DB->update_record('portfolioact_tmpl_settings', $settings);
         */

        $data = (object)$data;
        $oldid = $data->id;

        $mappingid = $this->get_mappingid('portfolioact_scaffold', $oldid);

        if ($mappingid) {

            //update settings table with new scaffold - re-used.
            $settings = new stdClass();
            $settings->id = $this->get_new_parentid('portfolioactmode_tmpl_settings');
            $settings->scaffold = $mappingid;
            $settings->timemodified = time();
            $DB->update_record('portfolioact_tmpl_settings', $settings);
            $this->oldscaffoldid =  $mappingid;

        } else {

            $this->oldscaffoldid = $oldid;
            $data->course = $this->task->get_courseid();
            $data->timemodified = time();
            $data->timecreated = time();
            $newitemid = $DB->insert_record('portfolioact_scaffolds', $data);
            $orig_course_context = $this->task->get_info()->original_course_contextid;
            $this->set_mapping('portfolioact_scaffold', $oldid, $newitemid, true, $orig_course_context );

            //update settings table with new scaffold
            $settings = new stdClass();
            $settings->id = $this->get_new_parentid('portfolioactmode_tmpl_settings');
            $settings->scaffold = $newitemid;
            $DB->update_record('portfolioact_tmpl_settings', $settings);

        }

    }





    /**
     * Processes one referenced template element
     *
     * @param mixed $data
     */
    public function process_portfolioactmode_referenced_template($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $mappingid = $this->get_mappingid('portfolioactmode_template', $oldid);

        //will be false or a string with the number in

        if ($mappingid) {//use the same template
            //its pages and items will already have been processed
            //but we still need to process its entries for the current activity
            //so we can't just stop processing the children
            //return -991399;
            $this->stopprocessing = true;

        } else {

            $this->stopprocessing = false;

            $data->course = $this->task->get_courseid();
            $data->timecreated = time();
            $data->timemodified = time();
            $savedpageorder = $data->pageorder;
            $data->pageorder = '';//we don't know the new page ids yet
            $newitemid = $DB->insert_record('portfolioact_template', $data);

            if ( is_null($savedpageorder) || ($savedpageorder === "") ) {
                $this->oldpageorder[$newitemid] = null;
            } else {
                $this->oldpageorder[$newitemid] = explode(",", $savedpageorder);
            }
            //handles possible error case of empty values in the order string. should not happen. but.
            $this->oldpageorder = array_filter($this->oldpageorder, "portfolioactmode_template_removeblanks");

            $orig_course_context = $this->task->get_info()->original_course_contextid;
            $this->set_mapping('portfolioactmode_template', $oldid, $newitemid, false, $orig_course_context );

        }

    }



    /**
     * Processes one referenced page element
     *
     * @param mixed $data
     */
    public function process_portfolioactmode_referenced_pages($data) {
        global $DB;

        if ($this->stopprocessing) {
            return;
        }

        $data = (object)$data;
        $oldid = $data->id;
        $data->template = $data->template = $this->get_mappingid('portfolioactmode_template', $data->template);
        $saveditemorder = $data->itemorder;
        $data->itemorder = '';//we don't know the new itemids yet
        $newitemid = $DB->insert_record('portfolioact_tmpl_pages', $data);

        if (is_null($saveditemorder) || ($saveditemorder === "")) {
            $this->olditemorder[$newitemid] = null;
        } else {
            $this->olditemorder[$newitemid] = explode(",", $saveditemorder);
            $this->olditemorder[$newitemid] = array_filter($this->olditemorder[$newitemid], "portfolioactmode_template_removeblanks");
        }

        $orig_course_context = $this->task->get_info()->original_course_contextid;
        $this->set_mapping('portfolioactmode_pages', $oldid, $newitemid, false, $orig_course_context );

    }




    /**
     * Processes one referenced item element
     *
     * @param mixed $data
     */
    public function process_portfolioactmode_referenced_items($data) {
        global $DB;

        if ($this->stopprocessing) {
            return;
        }

        $data = (object)$data;
        $oldid = $data->id;
        $data->page = $this->get_mappingid('portfolioactmode_pages', $data->page);

        $newitemid = $DB->insert_record('portfolioact_tmpl_items', $data);

        //if the type if a reference the one it refers to will also be a newitemid
        //however since that is sorted in the referenced_templates section
        //we may not have the mapping yet!
        if ($data->type == 'reference') {
            $this->referenceitems[] = $newitemid;
        }

        $orig_course_context = $this->task->get_info()->original_course_contextid;
        $this->set_mapping('portfolioactmode_items', $oldid, $newitemid, false, $orig_course_context );

    }


    /**
     * Processes one referenced entries element
     *
     * @param mixed $data
     */
    public function process_portfolioactmode_referenced_entries($data) {
        global $DB;

        if ( $this->get_setting_value('userinfo') == 0) {
            return;
        }

        $data = (object)$data;
        //$data->timemodified = time();

        $itemid = $this->get_mappingid('portfolioactmode_items', $data->itemid);

        if (! is_null($itemid)) {
            $data->itemid = $itemid;
        } else {//no new item; we already have one, map it - occurs in repeated template case
            //means we still write the data for a repeated template as it may belong to
            //a different activity even through we don't re-write the template/pages/items
            $data->itemid = $this->get_mappingid('portfolioactmode_items', $data->itemid);
        }

        if (! is_null($data->actid)) {
            $data->actid = $this->get_new_parentid('portfolioact');
        }

        $data->userid = $this->get_mappingid('user', $data->userid);
        $DB->insert_record('portfolioact_tmpl_entries', $data);

    }


    /**
     * Perform post execution activities
     *
     */


    public function after_execute_portfolioact() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/portfolioact/mode/template/lib.php');

        //fix up the files
        if (! is_null($this->oldscaffoldid)) {
            $orig_course_context = $this->task->get_info()->original_course_contextid;
            $this->add_related_files('mod_portfolioactmode_scaffold', 'scaffoldset',
                'portfolioact_scaffold',  $orig_course_context, $this->oldscaffoldid );
        }
        //fix up the page order in the template

        if (! empty($this->oldpageorder)) {

            foreach ($this->oldpageorder as $newtemplateid => $oldpageorder) {

                $newpageorder = array();

                foreach ($oldpageorder as $oldpage) {
                    $mapping = $this->get_mapping('portfolioactmode_pages', $oldpage);
                    $newpageorder[] = $mapping->newitemid;
                }

                $settings = new stdClass();
                $settings->id = $newtemplateid;
                $settings->pageorder = implode(",", $newpageorder );
                $DB->update_record('portfolioact_template', $settings);

            }

        }

        //itemorder
        if (! empty($this->olditemorder)) {

            foreach ($this->olditemorder as $newpageid => $olditemorder) {
                if (! is_null($olditemorder)) {

                    $newitemorder = array();
                    foreach ($olditemorder as $olditemid) {
                        $mapping = $this->get_mapping('portfolioactmode_items', $olditemid);
                        $newitemorder[] = $mapping->newitemid;
                    }

                    $settings = new stdClass();
                    $settings->id = $newpageid;
                    $settings->itemorder = implode(",", $newitemorder);
                    $DB->update_record('portfolioact_tmpl_pages', $settings);
                }

            }

        }

        //fix up the page filter in the settings - this only applies to the main template
        //not any referenced ones
        $actid = $this->task->get_activityid();

        if ($this->newsettings) {
            $newsettings = $DB->get_record('portfolioact_tmpl_settings', array('actid'=>$actid));
            if ((! is_null($newsettings->page )) && ($newsettings->page !== "") ) {
                $pagesfilter = explode(",", $newsettings->page);
                $pagesfilter = array_filter($pagesfilter, "portfolioactmode_template_removeblanks");
                $newpagesfilter = array();
                foreach ($pagesfilter as $oldpage) {
                    $newpagesfilter[] = $this->get_mappingid('portfolioactmode_pages', $oldpage);
                }
                $updatedsettings = new stdClass();
                $updatedsettings->id = $newsettings->id;
                $updatedsettings->page = implode(",", $newpagesfilter);
                $DB->update_record('portfolioact_tmpl_settings', $updatedsettings);
            }
        }

        //fix up the reference items
        if (! empty($this->referenceitems)) {
            $refitems = implode(",", $this->referenceitems);
            $sql = "SELECT id, settings, reference FROM {portfolioact_tmpl_items} WHERE id IN ($refitems)";
            $recs = $DB->get_records_sql($sql, array());

            foreach ($recs as $newitemrecord) {
                $newref = $this->get_mappingid('portfolioactmode_items', $newitemrecord->reference);
                $settings = portfolioactmode_template_settingskeys_out($newitemrecord->settings);
                $settings['sourceitem'] = $newref;
                $newsettings = portfolioactmode_template_settingskeys_in($settings);
                $updateditem = new stdClass();
                $updateditem->id = $newitemrecord->id;
                $updateditem->settings = $newsettings;
                $updateditem->reference = $newref;
                $DB->update_record('portfolioact_tmpl_items', $updateditem);
            }

        }

        //

    }

}




function portfolioactmode_template_removeblanks($el) {

    if ($el === "") {
        return false;
    } else {
        return $el;
    }

}
