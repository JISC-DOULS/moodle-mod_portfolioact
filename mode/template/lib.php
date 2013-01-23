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
 * Code to support the Portfolioact Template
 *
 * Classes and functions to support the template mode.
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * require code
 */

require_once(dirname(dirname(dirname(__FILE__))).'/locallib.php');
defined('MOODLE_INTERNAL') || die();




/**
 * Class for portfolioact template mode sub-plugin
 *
 * This extends the mode plugin class and provides a renderer
 * and other functionality to manage the template specific settings for
 * this activity
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class portfolioact_mode_template extends portfolioact_mode_plugin {

    public $settings;
    const TABLE_NAME = 'tmpl';

    /**
     * Constructor for portfolioact_mode_template
     * @param int $actid
     * @param int $templateid
     * @return portfolioact_mode_template with or without settings
     */


    public function __construct() {
        global $DB;

        parent::__construct('template');

        $settings = $DB->get_record('portfolioact_tmpl_settings',
        array('actid'=>$this->portfolioact->id), '*');
        //false if no record
        //this can be if the activity has been created but they have not yet
        //been to the edit screen
        if ($settings === false) {
            $this->settings = null;
        } else {
            $this->settings = $settings;
        }
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
        $settings = $DB->get_record('portfolioact_tmpl_settings',
        array('actid'=>$actid), '*');
        //false if no record
        if ($settings === false) {
            return null;
        } else {
            $templateid = $settings->template;
            return new portfolioact_template($templateid);
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
        $settings = $DB->get_record('portfolioact_tmpl_settings',
        array('actid'=>$actid), '*');
        //false if no record
        if ($settings === false) {
            return null;
        } else {
            return $settings;
        }
    }

    /**
     * Returns the formatted template
     *
     * Returns the formatted template with questions and answers as html
     *
     * @param int $actid
     * @return NULL|string
     */
    public static function get_data_for_export($actid) {
        $data = "";
        global $DB, $CFG, $PAGE, $USER;
        $template = self::get_mode_operational_instance($actid);
        if (is_null($template)) {
            return null;
        }

        $pagesintemplate = $template->get_pages(true);

        $pageorder = array_keys($pagesintemplate);

        $settings = $DB->get_record('portfolioact_tmpl_settings', array('actid'=>$actid), '*');

        if ($settings === false) {
            throw new moodle_exception('missingsettings', 'portfolioactmode_template');
        }

        //apply the page filter if it exists and fix up the pageorder array
        /*if (! empty($settings->page)) {
            $allowedpages = explode(",", $settings->page);
            $pageorder = array_intersect($pageorder, $allowedpages);
        }*/

        if (empty($pageorder)) {
            //no data to export
            return null;
        }

        //get the items per page in order based on the order string
        $pageitemorders = array();
        $itemsintemplate = array();

        foreach ($pageorder as $page) {
            $template_page = new portfolioact_template_page($page);
            $itemsonpage = $template_page->get_item_order_string();
            if (! is_null($itemsonpage)) {
                $pageitemorders[$page] = explode(",", $itemsonpage );
                $itemsintemplate = array_merge($itemsintemplate,
                $pageitemorders[$page] );
            }
        }

        if (empty ($itemsintemplate)) {
            //no data to export
            return null;
        }

        $itemsintemplate = array_unique($itemsintemplate);
        $itemsintemplate = array_filter($itemsintemplate, "portfolioactmode_template_isnumeric");
        //$itemsintemplate = implode(",", $itemsintemplate);

        /*$sql = <<<EOD
        SELECT i.id as itemid, i.name, i.type, i.settings, i.reference
        FROM {portfolioact_tmpl_items} i
        WHERE i.id IN ( ? )

        EOD;*/
        //we are getting the raw table records rather than instantiating
        //an item object for each one to minimize the number of sql queries

        //$items = $DB->get_records_sql($sql, array($itemsintemplate));
        //Use moodle select instead
        list($in_sql, $in_params) = $DB->get_in_or_equal($itemsintemplate);
        $items = $DB->get_records_select('portfolioact_tmpl_items', "id $in_sql",
            $in_params, '', 'id, name, type, settings, reference');

        if (empty ($items)) {
            //no data to export
            return null;
        }

        //2. GET THE ENTRIES
        $itemswithentries = array();
        $referenceitems = array();

        foreach ($items as $item) {

            if (in_array($item->type, portfolioact_template_item::$readonly)) {
                continue;// No user entries.
            }

            if ($item->type == 'reference') {//we'll deal with separately
                $referenceitems[$item->id] = $item->reference;
                continue;
            }

            $itemswithentries[] = $item->id;

        }

        //get data for items that may have it other than reference items
        $itemswithdata = array();
        if (! empty($itemswithentries)) {

            $sql2params = array($USER->id);

            if ($settings->datamode == 0) {//data is linked to the activity not the course
                $sql2params[] = $actid;
                $extrawhere = ' AND actid = ?';
            } else {
                //$sql2params[] = 'null';
                $extrawhere = ' AND actid IS NULL';
            }


            /*$itemsstring = implode(",", $itemswithentries);
            $sql2 = <<<EOD
            SELECT  e.id, e.itemid, e.entry FROM
            {portfolioact_tmpl_entries} e
            WHERE e.itemid IN ( ? ) AND e.userid = $USER->id {$extrawhere};
            EOD;

            $itemswithdata = $DB->get_records_sql($sql2, array($itemsstring));*/
            //use moodle select instead
            list($in_sql, $in_params) = $DB->get_in_or_equal($itemswithentries);
            $where = "itemid $in_sql AND userid = ? $extrawhere";
            $sql2params = array_merge($in_params, $sql2params);
            $itemswithdata = $DB->get_records_select('portfolioact_tmpl_entries',
                $where, $sql2params, '', 'id, itemid, entry');
        }

        $hasheditemswithdata = array();
        if (! empty($itemswithdata) ) {
            foreach ($itemswithdata as $itemdata) {
                //given the unique index on itemid, userid, actid we will not have duplicate itemids
                //for this user for this act - in Activity mode
                //potentially we could have in Course mode as actid null is allowed
                //multiple times in the unique index. that would be an error state in the data
                //TODO cld test for that but what to do if we find it?
                $hasheditemswithdata[$itemdata->itemid] = $itemdata;
            }
        }

        //get data for ref items
        $hashedrefitemswithdata = array();
        if (! empty($referenceitems)) {

            $sql3params = array($USER->id);

            if ($settings->datamode == 0) {//data is linked to the activity not the course
                $sql3params[] = $actid;
                $extrawhere = ' AND actid = ?';
            } else {
                //$sql3params[] = 'null';
                $extrawhere = ' AND actid IS NULL';
            }

            /*$itemsstring = implode(",", array_values($referenceitems));
            $sql3 = <<<EOD
            SELECT  e.id, e.itemid, e.entry FROM
            {portfolioact_tmpl_entries} e
            WHERE e.itemid IN ( ? ) AND e.userid = $USER->id  $extrawhere

            EOD;
            $refitemswithdata = $DB->get_records_sql($sql3, array($itemsstring));*/
            //Use Moodle select instead of sql
            list($in_sql, $in_params) = $DB->get_in_or_equal($referenceitems);
            $where = "itemid $in_sql AND userid = ? $extrawhere";
            $sql3params = array_merge($in_params, $sql3params);
            $refitemswithdata = $DB->get_records_select('portfolioact_tmpl_entries',
                $where, $sql3params, '', 'id, itemid, entry');

            if (! empty($refitemswithdata)) {
                foreach ($refitemswithdata as $itemdata) {
                    $hashedrefitemswithdata[$itemdata->itemid] = $itemdata;
                }
            }
        }

        //patch up the data
        foreach ($items as $item) {
            if (isset($hasheditemswithdata[$item->id])) {
                $item->entry = $hasheditemswithdata[$item->id]->entry;
                $item->entryid = $hasheditemswithdata[$item->id]->id;
            } else if ( (! is_null($item->reference) ) && (
            isset($hashedrefitemswithdata[$item->reference]))) {
                $item->entry = $hashedrefitemswithdata[$item->reference]->entry;
                $item->entryid = $hashedrefitemswithdata[$item->reference]->id;
            } else {
                $item->entry = null;
                $item->entryid = null;
            }
        }

        $output = "";
        $r = $PAGE->get_renderer('portfolioactmode_template');
        $hr = $r->render_portfolioactmode_template_hr();

        $br = $r->render_portfolioactmode_template_break();
        $ctr = 1;
        $pagecount = count($pageorder);
        $formatoptions = portfolioactmode_template_formatoptions();

        foreach ($pageitemorders as $page => $ordereditems) {

            //$output.= $r->render_portfolioactmode_template_page_head
            //($pagesintemplate[$page]->name);

            foreach ($ordereditems as $itemid) {

                if (! is_numeric($itemid) || (! array_key_exists($itemid, $items))) {
                    continue;
                }

                $settingskeys = portfolioactmode_template_settingskeys_out($items[$itemid]->settings);

                if ($items[$itemid]->type == 'reference') {
                    $sourceitem = portfolioact_template_item::getitem($items[$itemid]->reference);
                    //swap the settings keys so the ref item now has those of its source item
                    $settingskeys =  $sourceitem->settingskeys;
                    //Swap type so refered to as per original
                    $items[$itemid]->type = $sourceitem->type;
                }

                $itemclass = 'portfolioact_template_item_'.$items[$itemid]->type;

                if (method_exists($itemclass, 'format_entry_for_export')) {

                    $items[$itemid]->entry = $itemclass::format_entry_for_export($items[$itemid]);
                }

                if ($settingskeys['savewithexport'] == 1) {
                    $outputline = '';
                    $outputline = format_text($settingskeys['questiontext'], FORMAT_HTML,
                        $formatoptions);
                    if (method_exists($itemclass, 'format_question_for_export')) {
                        $outputline = $itemclass::format_question_for_export($outputline, $items[$itemid], $actid);
                    }
                    if (  (isset($items[$itemid]->entry)) ) {
                        $outputline.= format_text($items[$itemid]->entry, FORMAT_HTML,
                            $formatoptions);
                    }

                    if ($items[$itemid]->type == 'checkbox') {
                        $outputline = $r->render_portfolioactmode_template_para
                        ($outputline);
                    }

                    $outputline.= $br;
                    $output.= $outputline;
                }

            }
            if ($ctr < $pagecount) {
                //$output.= $r->render_portfolioactmode_template_page_break();
            }
            $ctr++;
        }

        return $output;
    }


    /**
     * Produces a menu item for the side navigation for edit settings
     * @param mixed $node
     */

    public function nav_menu_item(&$node) {

        $url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
        array('id'=>$this->cm->id));
        $node->add(get_string('edittemplatesettings', 'portfolioactmode_template'), $url);
    }

    /**
     * Adds a new template @see portfolioact_mode_plugin->add_new()
     *
     * @param string $name
     * @param int $courseid
     *
     * @return boolean
     */

    public function add_new($name, $courseid) {

        global $DB;

        $newtemplate = new stdClass;
        $newtemplate->id = null;
        $newtemplate->name = $name;
        $newtemplate->timecreated = time();
        $newtemplate->course = $courseid;
        $DB->insert_record('portfolioact_template', $newtemplate);

        return true;
    }


    /**
     *
     * Gets currently selected pages for this template-activity
     * @return array|NULL
     */

    public function getpages() {
        if (! empty($this->settings) && (! empty($this->settings->page)) ) {
            return explode(',', $this->settings->page);
        } else {
            return null;
        }
    }

    /**
     * Sets the settings on portfolioact_mode_template after it has been
     * constructed
     * @param int $actid
     */

    public function setsettings($actid) {
        $settings = $DB->get_record('portfolioact_template',
        array('actid'=>$actid), '*', MUST_EXIST);
        $this->settings = $settings;
    }

}

/**
 * Class for portfolioact template
 *
 * This object represents the template qua template - and provides
 * methods it access its pages
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_template {

    private $name;
    private $course;
    private $timecreated;
    private $timemodified;

    /**
     * Constructs an instance of portfolioact template
     * @param int $templateid
     * @return object portfolioact_template
     */

    public function __construct($templateid) {

        global $DB;
        $data = $DB->get_record('portfolioact_template',
        array('id'=>$templateid), '*');
        //false if no record - this could happen if the template had
        //been deleted by another user. (unlikely).

        if ($data === false ) {
            throw new moodle_exception('thistemplatedoesnotexist',
                'portfolioactmode_template');
        }

        $this->id = $data->id;
        $this->name = $data->name;
        $this->course = $data->course;
        $this->timecreated = $data->timecreated;
        $this->timemodified = $data->timemodified;
        $this->DB = $DB;

    }

    /**
     * Internal sorting function. Sorts pages by position.
     * @param mixed $a
     * @param mixed $b
     */

    static protected function page_order($a, $b) {

        if ($a->position == $b->position) {//shouldn't happen
            return 0;
        }

        return ($a->position < $b->position) ? -1 : 1;

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
     * Update the template name
     *
     * @param string $name
     */

    public function updatename($name) {
        $rec = new stdClass();
        $rec->id = $this->id;
        $rec->name = $name;
        $rec->timemodified = time();
        $this->DB->update_record('portfolioact_template', $rec);
        $this->name = $name;
    }

    /**
     * PHP magic getter for this class
     * @param unknown_type $key
     */

    public function __get($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }





    /**
     * Gets the pages for this template with their position if possible
     *
     * Returns them sorted by position
     * @param boolean $keyed true if you want the resultant array keyed by pageid
     * @return array of assoc arrays id , name ,  template, itemorder
     */

    public function get_pages($keyed = false) {
        $pages = $this->DB->get_records('portfolioact_tmpl_pages',
        array('template' => $this->id), null, 'id,name');

        //work out position
        $get_page_order_string = $this->get_page_order_string();
        $orders_from_db = explode(",", $get_page_order_string);
        $orders = array();
        //remove from $orders page which do not exist (i.e. the page
        //was deleted but not removed from the pageorder list)
        //this is unlikely - we remove from the orderstring when we
        //delete a page
        foreach ($orders_from_db as $pageid) {
            if ( array_key_exists($pageid, $pages)) {
                $orders[] = $pageid;
            }
        }

        //set the position on the page if it is in the order string
        //or false if not.
        //we don't really expect it to occur to have an page but no index
        //in the orderstring

        $highestposition = 0;
        foreach ($pages as $page) {
            $page->position = array_search( $page->id, $orders);
            if ($page->position !== false) {
                //correct for the zero based index returned by array_search
                $page->position = $page->position + 1;
                if ($page->position > $highestposition ) {
                    $highestposition = $page->position;
                }
            }
        }

        //now deal with pages which were not in the pageorder string
        //it was not in the pageorder string. just add it to the end.
        foreach ($pages as $page) {
            if ($page->position === false) {
                $page->position =     $highestposition + 1;
                $highestposition++;
            }

        }

        //now order them by position
        usort($pages, array("portfolioact_template", "page_order" ));

        if ($keyed === true) {

            $keyedpages = array();
            foreach ($pages as $page) {
                $keyedpages[$page->id] = $page;
            }

            return $keyedpages;

        } else {
            return $pages;
        }

    }

    /**
     * Add a new page to the template and adds it at the end of the order list
     * in the template table
     * @param string $name
     * @return int id of new page
     */
    public function add_page($name) {

        $newpage = new stdClass;
        $newpage->id = null;
        $newpage->name = $name;
        $newpage->template =  $this->id;
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        $pageid = $DB->insert_record('portfolioact_tmpl_pages', $newpage);

        $rec = $DB->get_record('portfolioact_template', array('id'=>$this->id),
            'pageorder');
        $pageorder = $rec->pageorder;
        $template = new stdClass;
        $template->id = $this->id;
        $template->timemodified = time();

        if (is_null($pageorder ) || ($pageorder === "")) {
            $template->pageorder = $pageid;
        } else {
            //add it at the end = last
            $template->pageorder = $pageorder . "," . $pageid;
        }

        $DB->update_record('portfolioact_template', $template);
        //IF one of the db operations in this transaction fails
        //that will throw an error and we won't get to this commit
        //(the only problem could be if that error was trapped and ignored
        //somewhere above it
        //-the db would thus be in a state of having had a BEGIN issued but
        //no commit or rollback
        //and it will very quickly error again if another BEGIN is issued.
        $transaction->allow_commit();

        return $pageid;

    }

    /**
     * Sets the position string for this template
     * @param array $positions assoc array with pageid => pos
     * @return boolean
     */

    public function set_page_order($positions) {

        //build the string
        if (empty($positions)) {
            return false;
        }

        //get current page list
        $pages = $this->DB->get_records('portfolioact_tmpl_pages',
        array('template' => $this->id), null, 'id');
        $pagekeys = array_keys($pages);

        //check that none of the pages have been deleted
        //if they have remove them from the sort order
        foreach ($positions as $key => $pos) {
            if (! in_array($key, $pagekeys)) {
                unset($positions[$key]);
            }
        }

        //detect collision. this detects if a page has been added since
        //the user who is now saving position loaded the page
        //if so we abort the attempt to save position and message the user
        //they will now see the new page and can try again
        $collision= false;
        foreach ($pagekeys as $key) {
            if (! array_key_exists($key, $positions )) {//a new page
                $collision = true;
                break;
            }

        }

        if ($collision) {
            return false;
        }

        asort($positions, SORT_NUMERIC);
        $keys = implode(",", array_keys($positions));
        $template = new stdClass;
        $template->id = $this->id;
        $template->pageorder = $keys;
        $template->timemodified = time();
        $this->DB->update_record('portfolioact_template', $template);
        return true;
    }

    /**
     * Static method to delete a template
     *
     * Cascade deletes its pages and their items. (Could unset it and
     * handle that in a destructor but ...)
     *
     * @param int $templateid
     * @return boolean
     */

    public static function delete($templateid) {
        global $DB, $USER;
        $transaction = $DB->start_delegated_transaction();

        $pages = $DB->get_records('portfolioact_tmpl_pages',
                array('template' => $templateid), null, 'id');

        $pageskeyed = array_keys($pages);

        if (!empty($pageskeyed)) {
            $templ = new portfolioact_template($templateid);
            foreach ($pageskeyed as $pageid) {
                $templ->delete_page($pageid, false);
            }
        }

        $DB->delete_records('portfolioact_template', array('id' => $templateid));

        $transaction->allow_commit();

        return true;

    }


    /**
     * Deletes a page and removes it from the order
     *
     * Cascade deletes its items.
     *
     * @param int $pageid
     * @param bool $updateparent Update Template info to reflect delete
     */

    public function delete_page($pageid, $updateparent = true) {

        global $USER;

        $transaction = $this->DB->start_delegated_transaction();
        $itemsrecs = $this->DB->get_records('portfolioact_tmpl_items',
                array('page' => $pageid), null, 'id');
        //empty array if empty
        $items = array_keys($itemsrecs);

        // Cascade delete of items (Slower, but more flexible).
        if (!empty($items)) {
            foreach ($items as $itemid) {
                $item = portfolioact_template_item::getitem($itemid);
                $item->delete_item(false);
            }
        }

        if ($updateparent) {
            // remove the pageid from the pageorder string leaving others unchanged.
            $rec = $this->DB->get_record('portfolioact_template',
                    array('id' => $this->id), 'pageorder');
            $pageorder = $rec->pageorder;
            $template = new stdClass;
            $template->id = $this->id;
            $template->timemodified = time();

            if (!is_null($pageorder)) {
                $list = explode(",", $pageorder);
                $newlist = array();

                foreach ($list as $pageorderelement) {
                    if ($pageorderelement != $pageid ) {
                        $newlist[] = $pageorderelement;
                    }
                }
                $template->pageorder = implode(",", $newlist );
                $this->DB->update_record('portfolioact_template', $template);
            }
        }

        // delete the page.
        $this->DB->delete_records('portfolioact_tmpl_pages', array('id' => $pageid));

        $transaction->allow_commit();

    }


    /**
     * returns the page order string (id1,id2,id5,id4) in desc order
     * for the template
     *
     * @return string
     */

    public function get_page_order_string() {
        $positions = $this->DB->get_record
        ('portfolioact_template', array('id'=>$this->id), 'pageorder');
        return $positions->pageorder;

    }
}


/**
 * Class for portfolioact template page
 *
 * This object represents a page of a template and provides methods to
 * manipulate the
 * page.
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_template_page {

    private $name;


    /**
     * Constructs an instance of portfolioact template page
     * @param $pageid
     * @return portfolioact_template_page
     */

    public function __construct($pageid) {

        global $DB;
        $data = $DB->get_record('portfolioact_tmpl_pages',
        array('id'=>$pageid), '*');
        //false if no record
        $this->id = $data->id;
        $this->name = $data->name;
        $this->template = $data->template;
        $this->itemorder = $data->itemorder;

        $this->DB = $DB;

    }

    /**
     * Internal sorting function. Sorts items by position.
     * @param mixed $a
     * @param mixed $b
     */

    static protected function item_order($a, $b) {

        if ($a->position == $b->position) {//shouldn't happen
            return 0;
        }

        return ($a->position < $b->position) ? -1 : 1;

    }



    /**
     * PHP magic setter for this class
     * @param mixed $key
     * @param mixed $value
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
     * Gets the items for this page with their position if possible
     *
     * Returns them sorted by position
     *
     * @return array of assoc arrays id , name , position
     */

    public function get_items() {
        $items = $this->DB->get_records('portfolioact_tmpl_items',
        array('page' => $this->id));

        //work out position
        $get_item_order_string = $this->get_item_order_string();
        $orders_from_db = explode(",", $get_item_order_string);

        $orders = array();

        //remove from $orders item which do not exist (i.e. the item was
        //deleted but not removed from the itemorder list)
        //this is unlikely - we remove from the orderstring when we delete
        //an item
        foreach ($orders_from_db as $itemid) {
            if ( array_key_exists($itemid, $items)) {
                $orders[] = $itemid;
            }

        }

        //set the position on the item if it is in the order string
        //or false if not.

        $highestposition = 0;
        foreach ($items as $item) {
            $item->position = array_search( $item->id, $orders);
            if ($item->position !== false) {
                //correct for the zero based index returned by array_search
                $item->position = $item->position + 1;
                if ($item->position > $highestposition ) {
                    $highestposition = $item->position;
                }
            }
        }

        //now deal with item which were not in the itemorder string
        //it was not in the itemorder string. just add it to the end.
        //we don't really expect it to occur to have an item but no index
        //in the orderstring: but this could just result from an error
        //in _item::create_element (where we do not use transactions)
        //but we cope if we do.
        foreach ($items as $item) {
            if ($item->position === false) {
                $item->position =     $highestposition + 1;
                $highestposition++;
            }

        }

        //now order them by position
        usort($items, array("portfolioact_template_page", "item_order" ));
        //TODO do just need to be sure that this will not break the order
        $keyeditems = array();
        foreach ($items as $item => $data) {
            $keyeditems[$data->id] = $data;
        }

        return $keyeditems;
    }



    /**
     * Sets the position string for this page
     * @param array $positions assoc array with itemid => pos
     * @return boolean
     */

    public function set_item_order($positions) {

        //build the string
        if (empty($positions)) {
            return false;
        }

        //get current page list
        $items = $this->DB->get_records('portfolioact_tmpl_items',
        array('page' => $this->id), null, 'id');
        $itemkeys = array_keys($items);

        //check that none of the pages have been deleted
        //if they have remove them from the sort order
        foreach ($positions as $key => $pos) {
            if (! in_array($key, $itemkeys)) {
                unset($positions[$key]);
            }
        }

        //detect collision. this detects if an item has been added since
        //the user who is now saving position loaded the page
        //if so we abort the attempt to save position and message the user
        //they will now see the new item and can try again
        $collision= false;
        foreach ($itemkeys as $key) {
            if (! array_key_exists($key, $positions )) {//a new item
                $collision = true;
                break;
            }

        }

        if ($collision) {
            return false;
        }

        asort($positions, SORT_NUMERIC);
        $keys = implode(",", array_keys($positions));

        $page = new stdClass;
        $page->id = $this->id;
        $page->itemorder = $keys;
        $this->DB->update_record('portfolioact_tmpl_pages', $page);
        return true;
    }



    /**
     * returns the item order string (id1,id2,id5,id4) in desc order for
     * the page
     *
     * @return string
     */

    public function get_item_order_string() {
        $positions = $this->DB->get_record('portfolioact_tmpl_pages',
        array('id'=>$this->id), 'itemorder');
        return $positions->itemorder;

    }

    /**
     * Add a field to the form to store the pagination index in
     *
     * @param mixed $form
     */

    public function add_pagination_field(&$form, $page) {

        $uniquename = "pageidx";
        $pagefield = $form->addElement('hidden', $uniquename );
        $pagefield->setAttributes(array('id'=>'pageidx', 'name'=>'pageidx',
            'type'=>'hidden', 'value'=>$page));
    }

    /**
     * Update the page name
     *
     * @param string $name
     */

    public function updatename($name) {
        $rec = new stdClass();
        $rec->id = $this->id;
        $rec->name = $name;
        $rec->timemodified = time();
        $this->DB->update_record('portfolioact_tmpl_pages', $rec);
        $this->name = $name;
    }


}



/**
 * Class for portfolioact template items
 *
 * This object represents a page of a template and provides methods to
 * manipulate the page.
 *
 * (To  create a new item type you need a new class which inherits from
 * this one.
 * Most types of controls are already supported - see the existing classes
 * settings for examples).
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class  portfolioact_template_item {

    //TODO - figure out how to make this automatic
    static public $items = array('instruction'=>'Instruction', 'text'=>'Text Entry',
        'checkbox'=>'Checkbox', 'reference'=>'Reference', 'datepicker'=>'Date selector',
        'numeric'=>'Numeric', 'duration' => 'Duration');
    static public $readonly = array('instruction', null);// Types that don't save, always inc null.
    protected $id;
    protected $name;
    protected $pageid;
    protected $settings;
    protected $settingskeys;
    protected $entryid;
    protected $type;
    protected $formatoptions;



    /**
     * Constructs an instance of portfolioact template item
     * @param $itemid
     * @return portfolioact_template_item
     */

    public function __construct($itemid) {

        global $DB;
        global $PAGE;
        $data = $DB->get_record('portfolioact_tmpl_items',
        array('id'=>$itemid), '*');
        //false if no record
        $this->id = $data->id;
        $this->name = $data->name;
        $this->pageid = $data->page;
        $this->type = $data->type;
        $this->settings = $data->settings;
        $this->reference = $data->reference;
        $this->settingskeys = array();
        //will be set if there is an entry by getData()
        $this->entryid = null;

        if (! empty($this->settings)) {
            $this->settingskeys = portfolioactmode_template_settingskeys_out($this->settings);
        }

        //put savewithexport onto the settingskeys for all types except reference
        if ($this->type != 'reference') {
            $class = 'portfolioact_template_item_'.$this->type;
            $this->settingskeys['savewithexport'] = $class::savewithexport($this->settingskeys);
        }

        $this->DB = $DB;

        $this->renderer = $PAGE->get_renderer('portfolioactmode_template');

        $this->formatoptions = portfolioactmode_template_formatoptions();
    }

    /**
     * Extending classes must provide a method which determines if an item
     * is saved with the export.
     *
     * It will look for a savewithexport param on the settings and use
     * that if present. Or it will use another param or return a default.
     *
     * @param array $settings
     * @return int
     *
     */

    public static function savewithexport($settings) {}




    /**
     * Extending classes must provide a method which takes a form object
     * and adds a form element to it suitable for the control.
     *
     * The form control is also populated with the user data if appropriate.
     * The function modifies the form element which is passed in by reference.
     * By default the element will get the name of the item. But in the case of
     * reference items which are calling their source item display method
     * we pass the id of the reference item and use that - so data is stored
     * against the id of the reference item.
     *
     * @param mixed $form
     * @param $actid int
     * @param $id int
     *
     *
     */


    abstract protected function display(&$form, $actid = null, $id = null);


    /**
     * Extending classes must return an assoc array of settings and
     * setting data type they can take.
     *
     * Note that not all html controls are necessarily supported.
     * @see itemsettings.php
     *
     * @see portfolioact_template_item_instruction::getSettingsTypes()
     * for an example of implementation
     *
     * All items must return: questiontext
     *
     * @param mixed $filter optional param to filter the settings by
     *
     * @return array of arrays with control, default value, label,
     * helptextstring identifier, helpstringfile identifier to define settings
     */
    protected static function getsettingtypes($filter = null) {}



    /**
     * All items must implement a save method.
     *
     * The savedata method is aware of the field(s) returned by the
     * forms system for this type of control
     *
     * All implementations of setsettings MUST save a key 'savewithexport'
     * which must be 1 or 0.
     *
     * @param mixed $entry
     * @param mixed $actid
     *
     */

    abstract protected function savedata($entry, $actid = null);


    /**
     * Create a new item and add it to the database. Optionally
     * return an object of this type.
     *
     * If you over-ride this method for an item your method MUST
     * put a savewithexport key onto the settings data.
     *
     * @param int $pageid
     * @param string $type one of valid $items
     * @param string $name
     * @param array $settings
     * @param mixed $itemafter will be an int id of the item to put
     * the new one after or 'start' if at the beginning
     * @param boolean $objectrequired true if you want an
     * instance of the new item back
     * @return mixed an object of the type or just true if
     * object not required or false on failure
     */
    public static function create_element($pageid, $type, $name,
    $settings = array(), $itemafter = null) {

        //TODO make this a transaction
        //TODO check $type in $items
        global $DB;
        $newitem = new stdClass;
        $newitem->id = null;
        $newitem->name = $name;
        $newitem->page =  $pageid;
        $newitem->type =  $type;
        $class = get_called_class();

        $settings['savewithexport'] = $class::savewithexport($settings);

        //the backup xml_writer class xml_site_utf8 method
        //replaces crlf with lf which breaks the serialisation
        //so we just remove any cr's here
        if (isset($settings['questiontext'])) {
            $settings['questiontext'] = preg_replace("/\r\n|\r/", "\n", $settings['questiontext']);
        }

        $newitem->settings = portfolioactmode_template_settingskeys_in($settings);

        $itemid = $DB->insert_record('portfolioact_tmpl_items', $newitem);

        //add it to the end of the position order
        $rec = $DB->get_record('portfolioact_tmpl_pages',
        array('id'=>$pageid), 'itemorder');
        $itemorder = $rec->itemorder;
        $page = new stdClass;
        $page->id =  $pageid;
        //this is the first one in the index so it will be last/first anyway what ever was
        //passed in
        if (is_null($itemorder ) || ($itemorder === "")) {
            $page->itemorder = $itemid;
        } else {//we have an index already

            if (! is_null($itemafter)) {
                //code to insert it in correct position
                if ($itemafter == 'start') {//specialcase
                    $currentidx = explode(",", $itemorder);
                    array_unshift($currentidx, $itemid);
                    $page->itemorder = implode(",", $currentidx);

                } else if ($itemafter == 'end') {
                    $currentidx = explode(",", $itemorder);
                    array_push($currentidx, $itemid);
                    $page->itemorder = implode(",", $currentidx);

                } else {

                    $currentidx = explode(",", $itemorder);
                    $pos = array_search($itemafter, $currentidx );

                    if ($pos !== false) {
                        $pos++;
                        array_splice($currentidx, $pos, 0, $itemid  );
                        $page->itemorder = implode(",", $currentidx);
                    } else {
                        //we didn't find it (another user has changed the index)
                        //because we can't leave the transaction unfinished and
                        //we don't want to issue our own rollback we are not using
                        //transactions here.
                        return false;
                    }
                }

            } else { //the position for the new item was not
                //specified - put it at end
                $page->itemorder = $itemorder . "," . $itemid;//add it at the end
            }
        }

        $DB->update_record('portfolioact_tmpl_pages', $page);

        if (!empty($settings['itemid'])) {
            $itemclass = "portfolioact_template_item_".$type;
            $obj = new $itemclass($itemid);
            return $obj->update_element($name, $settings);
        } else {
            return true;
        }

    }

    /**
     * Factory method. Returns an object of the correct item type.
     *
     * @param int $itemid
     * @return mixed
     */

    public static function getitem($itemid) {

        global $DB;
        $data = $DB->get_record('portfolioact_tmpl_items',
        array('id'=>$itemid), 'type');

        if ($data === false) {
            return null;//TODO maybe throw error?
        }

        $type = $data->type;

        $itemclass = "portfolioact_template_item_".$type;

        $obj = new $itemclass($itemid);
        return $obj;

    }

    /**
     * Updates an item name and settings
     *
     * If you over-ride this method for an item your method MUST
     * put a savewithexport key onto the settings data.
     *
     * @param string $name
     * @param mixed $settings
     * @return boolean
     */


    public function update_element($name, $settings = array()) {
        global $PAGE;
        if (!empty($settings['questiontext']) && !empty($settings['itemid'])) {
            // Add file bits.
            $fromform = new stdClass();
            $fromform->body = null;
            $fromform->body_editor = array(
                    'text' => $settings['questiontext'],
                    'itemid' => $settings['itemid'],
                    'format' => FORMAT_HTML
            );
            $context = context_course::instance($PAGE->course->id);
            $uploadopts = array('trusttext'=>true, 'subdirs'=>false, 'maxfiles'=>99,
                    'maxbytes'=>$PAGE->course->maxbytes, 'context'=>$context);
            // save and relink embedded images.
            $fromform = file_postupdate_standard_editor($fromform, 'body', $uploadopts, $context,
                    'portfolioactmode_template', 'question', $this->id);
            $settings['questiontext'] = $fromform->body;
            unset($settings['itemid']);
        }
        $item = new stdClass;
        $item->id = $this->id;
        $item->name = $name;

        $class = get_class($this);
        $settings['savewithexport'] = $class::savewithexport($settings);

        //the backup xml_writer class xml_site_utf8 method
        //replaces crlf with lf which breaks the serialisation
        //so we just remove any cr's here
        if (isset($settings['questiontext'])) {
            $settings['questiontext'] = preg_replace("/\r\n|\r/", "\n", $settings['questiontext']);
        }

        $item->settings = portfolioactmode_template_settingskeys_in($settings);
        $res = $this->DB->update_record("portfolioact_tmpl_items", $item);

        return $res;
    }


    /**
     * Deletes an item and removes it from the order
     *
     * Deletes an item and removes it from the order. First checks if it is
     * a the source of a reference item. If it is rerurns some information
     * about the reference item and does not delete.
     *
     * @return boolean|array
     *
     */

    public function delete_item($updateparent = true) {

        global $CFG, $USER, $PAGE;

        $transaction = $this->DB->start_delegated_transaction();

        // Get any references to this item and delete them also.
        $sql = <<<EOD
        SELECT i.id, i.page FROM
        {portfolioact_tmpl_items} i
        INNER JOIN {portfolioact_tmpl_pages} p ON p.id = i.page
        INNER JOIN {portfolioact_template} t ON t.id = p.template
        WHERE i.reference = ?
EOD;

        $refitems = $this->DB->get_records_sql($sql, array($this->id));

        if (!empty($refitems)) {
            foreach ($refitems as $itemid) {
                $item = portfolioact_template_item::getitem($itemid->id);
                // Update parent always.
                $item->delete_item(true);
            }
        }

        $this->DB->delete_records('portfolioact_tmpl_items', array('id' => $this->id));
        if ($updateparent) {
            // remove the itemid from the pageorder string leaving others unchanged.
            $rec = $this->DB->get_record('portfolioact_tmpl_pages',
                    array('id' => $this->pageid), 'itemorder');
            $itemorder = $rec->itemorder;
            $page = new stdClass();
            $page->id = $this->pageid;

            if (!is_null($itemorder)) {

                $list = explode(",", $itemorder);
                $newlist = array();

                foreach ($list as $itemorderelement) {
                    if ($itemorderelement != $this->id ) {
                        $newlist[] = $itemorderelement;
                    }
                }

                $page->itemorder = implode(",", $newlist);
                $this->DB->update_record('portfolioact_tmpl_pages', $page);
            }
        }

        $transaction->allow_commit();

        // Delete any files in question.
        if ($this->type != 'reference') {
            if (isset($PAGE->course->id) && $PAGE->course->id != SITEID) {
                $context = context_course::instance($PAGE->course->id);
            } else {
                // Get course id from template item (e.g. if deleting course).
                $sql = <<<EOD
                    SELECT t.course FROM
                    {portfolioact_tmpl_items} i
                    INNER JOIN {portfolioact_tmpl_pages} p ON p.id = i.page
                    INNER JOIN {portfolioact_template} t ON t.id = p.template
                    WHERE i.id = ?
EOD;
                $courseid = $this->DB->get_record_sql($sql, array($this->id));
                $context = context_course::instance($courseid->course);
            }
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'portfolioactmode_template', 'question', $this->id);
            portfolioactmode_template_delete_entries(array($this->id), $context->id);
        } else {
            portfolioactmode_template_delete_entries(array($this->id));
        }
        return true;

    }

    /**
     * Returns the stored data for the item for the current user or null
     * if there is none.
     *
     *
     *
     * @return string|null
     */

    public function getdata($actid = null) {
        global $USER;

        $conditions = array('itemid'=>$this->id, 'userid'=>$USER->id);
        $conditions['actid'] = $actid;

        $entry = $this->DB->get_record('portfolioact_tmpl_entries',
        $conditions , 'id,entry');

        if ($entry === false) {
            return null;
        } else {
            $this->entryid = $entry->id;
            return $entry->entry;
        }
    }

    public function format_question_for_display($text) {
        global $PAGE;
        $context = context_course::instance($PAGE->course->id);
        return file_rewrite_pluginfile_urls($text, 'pluginfile.php',
                $context->id, 'portfolioactmode_template', 'question', $this->id);
    }

    /**
     * Called by get_data_for_export()
     * Formats text ready for export by adding images
     * @param string $output
     * @param object $item
     * @param int $actid
     * @return string
     */
    public static function format_question_for_export($output, $item, $actid) {
        global $DB;
        if (stripos($output, '@@PLUGINFILE@@') !== false) {
            $portfolioact = $DB->get_record('portfolioact', array('id' => $actid), 'course', MUST_EXIST);
            $context = context_course::instance($portfolioact->course);
            if (!empty($item->reference)) {
                $item->id = $item->reference;
            }
            $output = file_rewrite_pluginfile_urls($output,
                    'mod/portfolioact/mode/template/pluginfile.php',
                    $context->id, 'portfolioactmode_template', 'question', $item->id);
        }
        return $output;
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
     * @param unknown_type $key
     */

    public function __get($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }

}


/**
 * Class for portfolioact template item instructions
 *
 * This object represents an item of type instructions
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_instruction extends
portfolioact_template_item {



    /**
     * Returns the possible setting types and default values
     * for this class of item
     *
     *
     * @see portfolioact_template_item::getsettingtypes()
     */
    public static function getsettingtypes($filter = null) {

        global $PAGE;
        if (! class_exists('portfolioactmode_template_renderer')) {
            include_once($CFG->dirroot.'/mod/portfolioact/mode/template/renderer.php');
        }

        $r = $PAGE->get_renderer('portfolioactmode_template');

        $types = array('message'=>array('control'=>'html',
            'label' => $r->render_instructionmessage()),
                 'questiontext'=>array('control'=>'editor',
                 'defaultvalue'=>'', 'label'=>get_string('itemlargetextlabel',
                 'portfolioactmode_template'), 'helptextstring'=>'itemlargetextassist',
                 'helptextfile'=>'portfolioactmode_template',
                 'filearea' => 'instruction'),
                 'displaysavestatus'=>array('control'=>'select',
                 'defaultvalue'=>'0',
             'values'=>array('0'=>get_string('showandsave', 'portfolioactmode_template'),
                 '1'=>get_string('saveonly', 'portfolioactmode_template'),
                 '2'=>get_string('showonly', 'portfolioactmode_template')  ),
                 'label'=>get_string('showandsavelabel', 'portfolioactmode_template'),
                 'helptextstring'=>'showandsavelabelassist',
                 'helptextfile'=>'portfolioactmode_template')

        );

        /* example of using multiselect
         *
         'displaysavestatustest'=>array('control'=>'multiselect',
         'defaultvalue'=>array('0', '1'),
         'values'=>array('0'=>get_string('showandsave',
         'portfolioactmode_template'),
         '1'=>get_string('saveonly', 'portfolioactmode_template'),
         '2'=>get_string('showonly', 'portfolioactmode_template')  ),
         'label'=>get_string('showandsavelabel', 'portfolioactmode_template'),
         'helptextstring'=>'showandsavelabelassist',
         'helptextfile'=>'portfolioactmode_template'),
         *
         */
        return $types;
    }




    /**
     * Returns if the item should be saved with the export.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     */

    public static function savewithexport($settings) {
        if ( $settings['displaysavestatus'] == 0) {
            return 1;
        } else if ($settings['displaysavestatus'] == 1) {
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * Returns the the question that the item poses.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     * @return string
     *
     */

    public function itemkey() {
        return 'instruction';

    }


    /**
     * Not implemented.
     *
     * Instructions do not have user data.
     *
     * @see portfolioact_template_item->savedata()
     */
    public function savedata($entry, $actid = null) {
        $url = new moodle_url('/mod/portfolioact/mode/template/view.php', array('id'=>$cm->id));
        throw new moodle_exception('cantsavedataforaninstruction', 'portfolioactmode_template',
        $url);
    }

    /**
     * Add the element to the form
     *
     * If $id is passed this is the $id of a reference element which is using
     * this one as its source one - in this case we want the id to be of the
     * referencing element.
     *
     * @see portfolioact_template_item->display()
     */
    public function display(&$form, $actid = null, $id = null) {
        global $PAGE;
        $form = &$form->formhandle;
        $instructiontext = $this->settingskeys['questiontext'];
        $instructiontext = self::substitute_codes($instructiontext, $actid);
        $instructiontext = format_text($instructiontext,
            FORMAT_HTML, $this->formatoptions);

        $instructiontext = $this->format_question_for_display($instructiontext);

        if (! is_null($id)) {
            $uniquename = "item_".$id;
        } else {
            $uniquename = "item_".$this->id;
        }

        if ($this->settingskeys['displaysavestatus'] != 1) {
            $form->addElement('html',
            $this->renderer->render_portfolioactmode_template_instructioncontrol
            ($instructiontext));
        }

        //$form->addElement
        //('html', $this->renderer->render_portfolioactmode_template_break());

    }

    // Override parent to add in substitution codes into export.
    public static function format_question_for_export($output, $item, $actid) {
        $instructiontext = self::substitute_codes($output, $actid);
        $instructiontext = parent::format_question_for_export($instructiontext, $item, $actid);
        return $instructiontext;
    }


    /**
     * Returns instruction text with codes interpolated
     *
     * Returns instruction text with codes interpolated. Supported codes
     * are {date} {courseshortname}  {activityname}  {username}
     * @param string $text
     * @param int $actid
     * @return $text
     */


    public static function substitute_codes($text, $actid) {

        global $COURSE, $USER, $DB;

        $patterns = array('/{date}/', '/{courseshortname}/', '/{activityname}/', '/{username}/',
            '/{pi}/');
        $date = userdate(time(), get_string('strftimedate', 'langconfig') );
        $courseshortname = $COURSE->shortname;
        $activityname = '';
        //only do the db lookup if we will actually need it
        if (preg_match('/{activityname}/', $text)) {

            $act = $DB->get_record('portfolioact', array('id'=>$actid), 'name');
            if (! empty($act)) {
                $activityname = $act->name;
            }
        }

        $username = fullname($USER);

        $substitutions = array($date, $courseshortname, $activityname, $username, $USER->idnumber);
        $newtext = preg_replace($patterns, $substitutions, $text);

        return $newtext;
    }

}

/**
 * Class for portfolioact template item text
 *
 * This object represents an item of type text
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_text extends portfolioact_template_item {

    /**
     * Returns the possible setting types and default values for
     * this class of item
     *
     * @see portfolioact_template_item::getsettingtypes()
     *
     */

    public static function getsettingtypes($filter = null) {
        $types = array('questiontext'=>array('control'=>'editor', 'defaultvalue'=>'',
                'label'=>get_string('itemquestionlabel', 'portfolioactmode_template'),
                    'helptextstring'=>'itemquestionlabelassist',
                    'helptextfile'=>'portfolioactmode_template'),
                'htmlformat'=>array('control'=>'checkbox', 'defaultvalue'=>1 ,
                    'label'=>get_string('itemquestionformatlabel',
                    'portfolioactmode_template'),
                    'helptextstring'=>'itemquestionformatlabelassist',
                    'helptextfile'=>'portfolioactmode_template'),
                'savewithexport'=>array('control'=>'select', 'defaultvalue'=>'1',
                'values'=>array('1'=>get_string('responsesaved', 'portfolioactmode_template'),
                    '0'=>get_string('responsenotsaved', 'portfolioactmode_template')  ),
                    'label'=>get_string('itemsavewithexportlabel', 'portfolioactmode_template'),
                    'helptextstring'=>'itemsavewithexportlabelassist',
                    'helptextfile'=>'portfolioactmode_template')
        );
        return $types;
    }


    /**
     * Returns if the item should be saved with the export.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     */

    public static function savewithexport($settings) {
        return $settings['savewithexport'];
    }



    /**
     * Saves the data for this type of control
     *
     * @see portfolioact_template_item->savedata()
     */

    public function savedata($entry, $actid = null) {

        global $USER, $COURSE;

        $data = new stdClass();
        $data->timemodified = time();
        $data->actid = $actid;

        if (is_scalar($entry)) {//it was a textarea
            $data->entry = $entry;
        } else {
            $data->entry = $entry['text']; //it was a rich text control
        }

        $entryrecord = $this->DB->get_record('portfolioact_tmpl_entries',
        array('userid'=>$USER->id, 'itemid'=>$this->id, 'actid' => $actid), 'id');

        if ($entryrecord) {
            $data->id = $entryrecord->id;
            $this->DB->update_record('portfolioact_tmpl_entries', $data);
            $entryid = $entryrecord->id;
        } else {
            $data->itemid = $this->id;
            $data->userid = $USER->id;

            $entryid = $this->DB->insert_record('portfolioact_tmpl_entries', $data, true);
        }

        if (strpos($data->entry, 'draftfile.php') != false) {
            // Add files only when needed.
            $fromform = new stdClass();
            $fromform->body = null;
            $fromform->body_editor = $entry;
            $ccontext = context_course::instance($COURSE->id);
            $maxsize = get_user_max_upload_file_size($ccontext, 0, $COURSE->maxbytes);
            $uploadopts = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 99,
                    'maxbytes' => $maxsize, 'context' => $ccontext);
            // save and relink embedded images.
            $fromform = file_postupdate_standard_editor($fromform, 'body', $uploadopts, $ccontext,
                    'portfolioactmode_template', 'entry', $entryid);
            $data = new stdClass();
            $data->id = $entryid;
            $data->entry = $fromform->body;
            $this->DB->update_record('portfolioact_tmpl_entries', $data);
        }

    }

    /**
     * Adds an appropriate element to a form
     *
     * @see portfolioact_template_item->display()
     */

    public function display(&$form, $actid = null, $id = null) {
        global $USER, $COURSE;
        $form->formhandle->addelement('html', html_writer::start_tag('div',
            array('class' => 'pat_item_' . $this->type)));
        $userdata=$this->getdata($actid);

        if (!is_null($id)) {
            $uniquename = "item_".$id;
        } else {
            $uniquename = "item_".$this->id;
        }

        $question_text = $this->settingskeys['questiontext'];
        $question_text = format_text($question_text,
            FORMAT_HTML, $this->formatoptions);

        $question_text = $this->format_question_for_display($question_text);

        $form->formhandle->addElement('html', $this->renderer->render_portfolioactmode_template_textlabel
        ($question_text));

        if ($this->settingskeys['htmlformat'] == 1) {
            // Add image upload support.
            $data = new stdClass();
            $settingformat = $uniquename . 'format';
            $data->$settingformat = FORMAT_HTML;
            $data->$uniquename = $userdata;
            $ccontext = context_course::instance($COURSE->id);
            $maxsize = get_user_max_upload_file_size($ccontext, 0, $COURSE->maxbytes);
            $uploadopts = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 99,
                    'maxbytes' => $maxsize, 'context' => $ccontext);
            $data = file_prepare_standard_editor($data, $uniquename,
                    $uploadopts, $ccontext, 'portfolioactmode_template',
                    'entry', $this->entryid);
            $editor = $form->formhandle->addElement('editor', $uniquename . '_editor',
                    '', null, $uploadopts);
            $form->set_data($data);
        } else {
            $editor = $form->formhandle->addElement('textarea', $uniquename, '', null);
            $editor->setValue($userdata);

        }

        //$form->addElement
        //('html', $this->renderer->render_portfolioactmode_template_break());
        $form->formhandle->addelement('html', html_writer::end_tag('div'));

    }

    /**
     * Takes entry record and rewrites pluginfile.
     * @param object $data
     * @return string
     */
    public static function format_entry_for_export($data) {
        global $USER, $COURSE;
        if (is_null($data->entry)) {
            return '';
        }
        if (strpos($data->entry, '@@PLUGINFILE@@') !== false) {
            // Add image upload support.
            $ccontext = context_course::instance($COURSE->id);
            $data = file_rewrite_pluginfile_urls($data->entry,
                    'mod/portfolioact/mode/template/pluginfile.php', $ccontext->id,
                    'portfolioactmode_template', 'entry', $data->entryid);
            return $data;
        }
        return $data->entry;
    }

}




/**
 * Class for portfolioact template item numeric
 *
 * This object represents an item of type numeric
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_numeric extends portfolioact_template_item {

    /**
     * Returns the possible setting types and default values for
     * this class of item
     *
     * @see portfolioact_template_item::getsettingtypes()
     *
     */

    public static function getsettingtypes($filter = null) {
        $types = array('questiontext'=>array('control'=>'editor', 'defaultvalue'=>'',
                'label'=>get_string('itemquestionlabel', 'portfolioactmode_template'),
                    'helptextstring'=>'itemquestionlabelassist',
                    'helptextfile'=>'portfolioactmode_template')

        );
        return $types;
    }


    /**
     * Returns if the item should be saved with the export.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     */

    public static function savewithexport($settings) {

        return 1;
    }



    /**
     * Saves the data for this type of control
     *
     * @see portfolioact_template_item->savedata()
     */

    public function savedata($entry, $actid = null) {

        global $USER;

        $data = new stdClass();
        $data->timemodified = time();
        $data->actid = $actid;

        $data->entry = $entry;

        $entryrecord = $this->DB->get_record('portfolioact_tmpl_entries',
        array('userid'=>$USER->id, 'itemid'=>$this->id, 'actid' => $actid), 'id');

        if ($entryrecord) {
            $data->id = $entryrecord->id;
            $this->DB->update_record('portfolioact_tmpl_entries', $data);
        } else {
            $data->itemid = $this->id;
            $data->userid = $USER->id;

            $this->DB->insert_record('portfolioact_tmpl_entries', $data, false);
        }

    }

    /**
     * Adds an appropriate element to a form
     *
     * @see portfolioact_template_item->display()
     */

    public function display(&$form, $actid = null, $id = null) {
        $form = &$form->formhandle;
        $form->addelement('html', html_writer::start_tag('div',
            array('class' => 'pat_item_' . $this->type)));
        $userdata=$this->getdata($actid);

        if (! is_null($id)) {
            $uniquename = "item_".$id;
        } else {
            $uniquename = "item_".$this->id;
        }

        $question_text = $this->settingskeys['questiontext'];

        $question_text = format_text($question_text,
            FORMAT_HTML, $this->formatoptions);
        $question_text = $this->format_question_for_display($question_text);
        $form->addElement('html', $this->renderer->render_portfolioactmode_template_textlabel
        ( $question_text));

        $field = $form->addElement('text', $uniquename,
            '', null);
        $form->addRule($uniquename, get_string('enteranumericvalue',
            'portfolioactmode_template'), 'numeric' , null, 'client');
        $form->setDefault($uniquename, $userdata);

        //$form->addElement
        //('html', $this->renderer->render_portfolioactmode_template_break());
        $form->addelement('html', html_writer::end_tag('div'));

    }

}


/**
 * Class for portfolioact template item duration
 *
 * This object represents an item of type duration
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_duration extends portfolioact_template_item {

    /**
     * Returns the possible setting types and default values for
     * this class of item
     *
     * @see portfolioact_template_item::getsettingtypes()
     *
     */

    public static function getsettingtypes($filter = null) {
        $types = array('questiontext'=>array('control'=>'editor', 'defaultvalue'=>'',
                'label'=>get_string('itemquestionlabel', 'portfolioactmode_template'),
                    'helptextstring'=>'itemquestionlabelassist',
                    'helptextfile'=>'portfolioactmode_template')

        );
        return $types;
    }


    /**
     * Returns if the item should be saved with the export.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     */

    public static function savewithexport($settings) {

        return 1;
    }



    /**
     * Saves the data for this type of control
     *
     * @see portfolioact_template_item->savedata()
     */

    public function savedata($entry, $actid = null) {

        global $USER;

        $data = new stdClass();
        $data->timemodified = time();
        $data->actid = $actid;

        $data->entry = $entry;

        $entryrecord = $this->DB->get_record('portfolioact_tmpl_entries',
        array('userid'=>$USER->id, 'itemid'=>$this->id, 'actid' => $actid), 'id');

        if ($entryrecord) {
            $data->id = $entryrecord->id;
            $this->DB->update_record('portfolioact_tmpl_entries', $data);
        } else {
            $data->itemid = $this->id;
            $data->userid = $USER->id;

            $this->DB->insert_record('portfolioact_tmpl_entries', $data, false);
        }

    }

    /**
     * Adds an appropriate element to a form
     *
     * @see portfolioact_template_item->display()
     */

    public function display(&$form, $actid = null, $id = null) {
        $form = &$form->formhandle;
        $form->addelement('html', html_writer::start_tag('div',
            array('class' => 'pat_item_' . $this->type)));
        $userdata=$this->getdata($actid);

        if (! is_null($id)) {
            $uniquename = "item_".$id;
        } else {
            $uniquename = "item_".$this->id;
        }

        $question_text = $this->settingskeys['questiontext'];

        $question_text = format_text($question_text,
            FORMAT_HTML, $this->formatoptions);
        $question_text = $this->format_question_for_display($question_text);
        $form->addElement('html', $this->renderer->render_portfolioactmode_template_textlabel
        ($question_text));

        $field = $form->addElement('duration', $uniquename,
            '', array('optional' => false));
        $form->setDefault($uniquename, $userdata);

        //$form->addElement
        //('html', $this->renderer->render_portfolioactmode_template_break());
        $form->addelement('html', html_writer::end_tag('div'));

    }

    /**
     * Return data for export.
     *
     * Return data for export. The user entry may be in form suitable for the
     * item control but not for export. Format it for export.
     *
     * @param $data
     * @return $data
     */

    public static function format_entry_for_export($data) {
        $data = $data->entry;
        if (is_null($data)) {
            return "";
        }
        list($value, $unit) = self::seconds_to_unit($data);
        return $value . ' ' . $unit;

    }

    /**
     * Used for determining untis from seconds.
     *
     *
     * @return array unit length in seconds => string unit name.
     */
    private static function get_units() {

            $units = array(
                86400 => get_string('days'),
                3600 => get_string('hours'),
                60 => get_string('minutes'),
                1 => get_string('seconds'),
            );

        return $units;
    }

    /**
     * Return the duration formatted for display
     *
     * @param $seconds an amout of time in seconds.
     * @return array($number, $unit) Conver an interval to the best possible unit.
     *      for example 1800 -> array(30, 60) = 30 minutes.
     */
    private static function seconds_to_unit($seconds) {
        $units = self::get_units();

        if ($seconds == 0) {
            return array(0, $units[60]);
        }

        foreach ($units as $unit => $label) {
            if (fmod($seconds, $unit) == 0) {
                return array($seconds / $unit, $label);
            }
        }
        return array($seconds, $units[1]);
    }

}






/**
 * Class for portfolioact template item datepicker
 *
 * This object represents an item of type datepicker
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_datepicker extends portfolioact_template_item {

    /**
     * Returns the possible setting types and default values for
     * this class of item
     *
     * @see portfolioact_template_item::getsettingtypes()
     *
     */

     const TIMEZONE = 99;

    public static function getsettingtypes($filter = null) {
        $types = array('questiontext'=>array('control'=>'editor', 'defaultvalue'=>'',
                'label'=>get_string('itemquestionlabel', 'portfolioactmode_template'),
                    'helptextstring'=>'itemquestionlabelassist',
                    'helptextfile'=>'portfolioactmode_template')
        );
        return $types;
    }


    /**
     * Return data for export.
     *
     * Return data for export. The user entry may be in form suitable for the
     * item control but not for export. Format it for export.
     *
     * @param $data
     * @return $data
     */

    public static function format_entry_for_export($data) {
        $data = $data->entry;
        if (empty($data)) {
            return "";
        }
        //the timezone 99 means use user's tiemzone
        //if the user changed their timezone between
        //using the control and the export the results might
        //be unpredictable - but worst case is it would be a day out.
        if (! empty($data)) {
            $data = userdate($data,
                get_string('strftimedate', 'langconfig'),
                    self::TIMEZONE);
        }

        return $data;

    }


    /**
     * Returns if the item should be saved with the export.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     */

    public static function savewithexport($settings) {

        return 1;
    }



    /**
     * Saves the data for this type of control
     *
     * @see portfolioact_template_item->savedata()
     */

    public function savedata($entry, $actid = null) {

        global $USER;

        $data = new stdClass();
        $data->timemodified = time();
        $data->actid = $actid;

        $data->entry = $entry;

        $entryrecord = $this->DB->get_record('portfolioact_tmpl_entries',
        array('userid'=>$USER->id, 'itemid'=>$this->id, 'actid' => $actid), 'id');

        if ($entryrecord) {
            $data->id = $entryrecord->id;
            $this->DB->update_record('portfolioact_tmpl_entries', $data);
        } else {
            $data->itemid = $this->id;
            $data->userid = $USER->id;

            $this->DB->insert_record('portfolioact_tmpl_entries', $data, false);
        }

    }

    /**
     * Adds an appropriate element to a form
     *
     * @see portfolioact_template_item->display()
     */

    public function display(&$form, $actid = null, $id = null) {
        $form = &$form->formhandle;
        $form->addelement('html', html_writer::start_tag('div',
            array('class' => 'pat_item_' . $this->type)));

        $userdata=$this->getdata($actid);

        if (! is_null($id)) {
            $uniquename = "item_".$id;
        } else {
            $uniquename = "item_".$this->id;
        }

        $question_text = $this->settingskeys['questiontext'];

        $question_text = format_text($question_text,
            FORMAT_HTML, $this->formatoptions);
        $question_text = $this->format_question_for_display($question_text);

         $form->addElement('html', $this->renderer->render_portfolioactmode_template_textlabel
        ($question_text));
        $stopyear = gmdate("Y") + 15;

        $datepicker = $form->addElement('date_selector', $uniquename, '', array('startyear'=>1920,
            'stopyear' => $stopyear, 'timezone' => self::TIMEZONE, 'optional'=>true));

        $form->setDefault($uniquename, $userdata);

        //$form->addElement
        //('html', $this->renderer->render_portfolioactmode_template_break());
        $form->addelement('html', html_writer::end_tag('div'));

    }

}





/**
 * Class for portfolioact template item checkbox
 *
 * This object represents an item of type checkbox
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_checkbox extends portfolioact_template_item {

    /**
     * Returns the possible setting types and default values for this class of item
     *
     * @see portfolioact_template_item::getsettingtypes()
     *
     */
    public static function getsettingtypes($filter = null) {
        $types = array( 'checkedbydefault'=>array('control'=>'select',
                'defaultvalue'=>'0',
                 'values'=>array('1'=>get_string('defaultstatechecked',
                      'portfolioactmode_template'),
                      '0'=>get_string('defaultstatenotchecked',
                      'portfolioactmode_template')  ),
                      'label'=>get_string('defaultstatelabel',
                      'portfolioactmode_template'),
                      'helptextstring'=>'defaultstatelabelassist',
                      'helptextfile'=>'portfolioactmode_template'),
                  'questiontext'=>array('control'=>'text',
                       'defaultvalue'=>'',
                        'required' =>true,
                       'label'=>get_string('checkboxlabel',
                       'portfolioactmode_template'),
                       'helptextstring'=>'checkboxlabelassist',
                       'helptextfile'=>'portfolioactmode_template')

        );
        return $types;
    }

    /**
     * Return data for export.
     *
     * Return data for export. The user entry may be in form suitable for the
     * item control but not for export. Format it for export.
     *
     * @param $data
     * @return $data
     */

    public static function format_entry_for_export($data) {

        global $PAGE;

        if (! class_exists('portfolioactmode_template_renderer')) {
            include_once($CFG->dirroot.'/mod/portfolioact/mode/template/renderer.php');
        }

        $r = $PAGE->get_renderer('portfolioactmode_template');
        $data = $data->entry;
        if (is_null($data)) {
            return "";
        }

        $colonspace = $r->render_portfolioactmode_template_colonspace();
        if ($data == 1) {
            return $colonspace . get_string('yesword',
                             'portfolioactmode_template');
        } else {
                return $colonspace . get_string('noword', 'portfolioactmode_template');
        }
    }



    /**
     * Returns if the item should be saved with the export.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     */

    public static function savewithexport($settings) {
        return 1;
    }




    /**
     * Saves the data for this type of control
     *
     * @see portfolioact_template_item->savedata()
     */

    public function savedata($entry, $actid = null) {

        global $USER;

        $data = new stdClass();
        $data->entry = $entry;
        $data->actid = $actid;
        $data->timemodified = time();

        //use record_exists & cjheck for entryid?
        $entryrecord = $this->DB->get_record
        ('portfolioact_tmpl_entries', array('userid'=>$USER->id,
                'itemid'=>$this->id, 'actid'=>$actid), 'id');

        if ($entryrecord) {
            $data->id = $entryrecord->id;
            $data->timemodified = time();
            $this->DB->update_record
            ('portfolioact_tmpl_entries', $data);

        } else {
            $data->itemid = $this->id;
            $data->userid = $USER->id;
            $this->DB->insert_record('portfolioact_tmpl_entries', $data,
            false);
        }

    }

    /**
     * Adds an appropriate element to a form
     *
     * @see portfolioact_template_item->display()
     */

    public function display(&$form, $actid = null, $id = null) {
        $form = &$form->formhandle;
        $form->addelement('html', html_writer::start_tag('div',
            array('class' => 'pat_item_' . $this->type)));

        $userdata = $this->getdata($actid);

        if (! is_null($id)) {
            $uniquename = "item_".$id;
        } else {
            $uniquename = "item_".$this->id;
        }
        $options = array('class'=>'portfolioact-template-template-checkbox');

        if (! is_null($userdata) ) {
            $checkedstate = $userdata;
        } else {
            $checkedstate = $this->settingskeys['checkedbydefault'];
        }
        //unchecked / checked
        $question_text = format_text($this->settingskeys['questiontext'],
            FORMAT_HTML, $this->formatoptions);
        $checkbox = $form->addElement('advcheckbox', $uniquename,
        $question_text . ' ', null,
        array('class' => 'portfolioactmode_template_checkbox', 'group' => 1), array(0, 1));

        if ($checkedstate == 1) {
            $checkbox->setChecked(true);
        } else {
            $checkbox->setChecked(false);
        }

        $form->addelement('html', html_writer::end_tag('div'));

    }


}



/**
 * Class for portfolioact template item reference
 *
 * This object represents an item of type checkbox
 *
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class  portfolioact_template_item_reference extends portfolioact_template_item {

    /**
     * Returns the possible setting types and default values for this class of item
     *
     * @see portfolioact_template_item::getsettingtypes()
     *
     */
    public static function getsettingtypes($filter = null) {

        //dynamically get items linked to current course
        $items = portfolioactmode_template_get_available_items($filter);

        $types = array(
                 'sourceitem'=>array('control'=>'select',
                      'defaultvalue'=>'--',
                      'values'=>$items,
                      'validatationtype' => array('label' =>
        get_string('chooseanitem', 'portfolioactmode_template'),
                            'type' => 'numeric', 'format' => null),
                      'label'=>get_string('choosesourceitem',
                      'portfolioactmode_template'),
                      'helptextstring'=>'choosesourceitemassist',
                      'helptextfile'=>'portfolioactmode_template')

        );
        return $types;
    }

    /**
     * Create a new item and add it to the database. Optionally
     * return an object of this type.
     *
     * In this method which over-rides its parent class one we additionally
     * put the source item id in the reference column.
     *
     * @param int $pageid
     * @param string $type one of valid $items
     * @param string $name
     * @param array $settings
     * @param mixed $itemafter will be an int id of the item to put
     * the new one after or 'start' if at the beginning
     * @param boolean $objectrequired true if you want an
     * instance of the new item back
     * @return mixed an object of the type or just true if
     * object not required or false on failure
     */

    public static function create_element($pageid, $type, $name,
    $settings = null, $itemafter = null, $objectrequired = false) {
        //TODO make this a transaction
        //TODO check $type in $items
        global $DB;
        $newitem = new stdClass;
        $newitem->id = null;
        $newitem->name = $name;
        $newitem->page =  $pageid;
        $newitem->type =  $type;

        if (empty($settings) ||  (! isset($settings['sourceitem']))) {
            throw new coding_exception
            ('Should not be trying to create a reference item without a source item');
        }
        $newitem->reference = $settings['sourceitem'];

        //$settings is an array of setting name => value pairs
        //the backup xml_writer class xml_site_utf8 method
        //replaces crlf with lf which breaks the serialisation
        //so we just remove any cr's here
        if (isset($settings['questiontext'])) {
            $settings['questiontext'] = preg_replace("/\r\n|\r/", "\n", $settings['questiontext']);
        }
        $newitem->settings = portfolioactmode_template_settingskeys_in($settings);
        $itemid = $DB->insert_record('portfolioact_tmpl_items', $newitem);

        //add it to the end of the position order
        $rec = $DB->get_record('portfolioact_tmpl_pages',
        array('id'=>$pageid), 'itemorder');
        $itemorder = $rec->itemorder;
        $page = new stdClass;
        $page->id =  $pageid;
        //this is the first one in the index so it will be last/first anyway what ever was
        //passed in
        if (is_null($itemorder ) || ($itemorder === "")) {
            $page->itemorder = $itemid;
        } else {//we have an index already

            if (! is_null($itemafter)) {
                //code to insert it in correct position

                if ($itemafter == 'start') {//specialcase

                    $currentidx = explode(",", $itemorder);
                    array_unshift($currentidx, $itemid);
                    $page->itemorder = implode(",", $currentidx);
                } else if ($itemafter == 'end') {
                    $currentidx = explode(",", $itemorder);
                    array_push($currentidx, $itemid);
                    $page->itemorder = implode(",", $currentidx);

                } else {
                    $currentidx = explode(",", $itemorder);
                    $pos = array_search($itemafter, $currentidx );

                    if ($pos !== false) {
                        $pos++;
                        array_splice($currentidx, $pos, 0, $itemid  );
                        $page->itemorder = implode(",", $currentidx);
                    } else {
                        //we didn't find it (another user has changed the index)
                        //because we can't leave the transaction unfinished and
                        //we don't want to issue our own rollback we are not using
                        //transactions here.
                        return false;
                    }
                }

            } else { //the position for the new item was not
                //specified - put it at end
                $page->itemorder = $itemorder . "," . $itemid;//add it at the end
            }
        }

        $DB->update_record('portfolioact_tmpl_pages', $page);

        if ($objectrequired) {
            $itemclass = "portfolioact_template_item_".$type;
            $obj = new $itemclass($itemid);
            return $obj;
        } else {
            return true;
        }

    }


    /**
     * Updates an item name and settings
     *
     * In this method which over-rides its parent class one we additionally
     * put the source item id in the reference column.
     *
     * @param string $name
     * @param mixed $settings
     * @return boolean
     */

    public function update_element($name, $settings = array()) {

        $item = new stdClass;
        $item->id = $this->id;
        $item->name = $name;
        $item->reference = $settings['sourceitem'];

        if (empty($settings) ||  (! isset($settings['sourceitem']))) {
            throw new coding_exception
            ('Should not be trying to create a reference item without a source item');
        }

        //$settings is an array of setting name => value pairs
        //the backup xml_writer class xml_site_utf8 method
        //replaces crlf with lf which breaks the serialisation
        //so we just remove any cr's here
        if (isset($settings['questiontext'])) {
            $settings['questiontext'] = preg_replace("/\r\n|\r/", "\n", $settings['questiontext']);
        }
        if (! is_null($settings)) {
            $item->settings = portfolioactmode_template_settingskeys_in($settings);
        }

        $res = $this->DB->update_record("portfolioact_tmpl_items", $item);

        return $res;
    }


    /**
     * Saves the data for this type of control
     *
     * Reference items save the data with the source item.
     *
     * @see portfolioact_template_item->savedata()
     */

    public function savedata($entry, $actid = null) {

        $sourceid = $this->settingskeys['sourceitem'];
        $sourceitem = portfolioact_template_item::getitem($sourceid);
        $sourceitem->savedata($entry, $actid);

    }

    /**
     * Adds an appropriate element to a form
     *
     * @see portfolioact_template_item->display()
     */

    public function display(&$form, $actid = null, $id = null) {

        $id = $this->id;
        $sourceid = $this->settingskeys['sourceitem'];
        $sourceitem = portfolioact_template_item::getitem($sourceid);

        if (empty($sourceitem)) {
            throw new moodle_exception('missingitem', 'portfolioactmode_template');
        }

        $sourceitem->display($form, $actid, $id);
    }

    /**
     * Returns if the item should be saved with the export.
     *
     * Not implemented for reference items which should always
     * be getting this from the source item.
     *
     * @see portfolioact_template_item->savewithexport()
     *
     *
     *
     */

    public static function savewithexport($settings) {

        throw new coding_exception
        ("Not allowed attempt to call savewithexport() on a reference item");

    }

}





/*
 * Helper function for changing order of pages in a template
 *
 * @param array $pageorderlist
 * @return boolean true or false if a collision has occured
 * @see portfolioact_template_item->set_page_order()
 */

function portfolioactmode_template_update_page_position($pageorderlist, $template) {

    $order = explode('#', $pageorderlist);
    $positions = array();
    foreach ($order as $pagepos) {
        if ((! empty($pagepos)) ) {//&& strrpos('|', $pagepos)
            $template_position = explode('|', $pagepos);
            $positions[$template_position[0]] = $template_position[1];
        }
    }

    $result = $template->set_page_order($positions);
    return $result;

}


/**
 * Helper function which gets list of all possible pages for the template
 *
 * @param int $templateid
 * @return array an array of associative arrays -  ids and names
 */

function portfolioactmode_template_get_pages($templateid) {

    $template = new portfolioact_template($templateid);
    $pages = $template->get_pages();

    if (empty($pages)) {
        return $pages;
    } else {
        $newpages = array();
        foreach ($pages as $page) {
            $newpages[] = array('id'=>$page->id, 'name'=>$page->name);
        }
        return $newpages;
    }

}

/**
 * Gets list of templates in the system for current course
 *
 *
 * @param int $courseid
 * @param string $sort optional name of field to sort on
 * @return array an array of associative arrays - template ids and names
 */


function portfolioactmode_template_get_templates($courseid, $sort = 'id') {
    global $DB;

    $records = $DB->get_records('portfolioact_template',
    array('course' => $courseid), $sort, 'id, name');
    $templates = array();

    foreach ($records as $template) {
        $templates[$template->id] = $template->name;

    }

    return $templates;

}

/**
 * Gets list of templates in the system for current course with used count
 *
 * Gets list of templates in the system for current course with used count and returns array of
 * record objects or empty array if none.
 *
 * @param int $courseid
 * @param string $sort optional name of field to sort on
 * @return array of objects
 *
 */

function portfolioactmode_template_get_templates_with_count($courseid, $sort = 'id') {
    global $DB, $CFG;

    //get all templates
    /*$sql = <<<EOD
    SELECT t.id, t.name, t.timecreated, t.timemodified FROM
    {portfolioact_template} t
    WHERE t.course = ?
    ORDER BY ?

    EOD;

    $templates = $DB->get_records_sql($sql, array($courseid, $sort));*/
    $templates = $DB->get_records('portfolioact_template', array('course' => $courseid),
        $sort, 'id, name, timecreated, timemodified');

    //get those which have an activity mode of template
    //this won't get those which have never been used (have no settings)
    //or have an entry in the settings table but their activity is set to scaffold
    //but that is fine as these ones will be used = zero
    $sql = <<<EOD
    SELECT  t.id , p.modename , COUNT(t.id) as used  FROM
    {portfolioact_template} t
    LEFT JOIN {portfolioact_tmpl_settings} s ON s.template  = t.id
    LEFT JOIN {portfolioact} p ON s.actid  = p.id
    WHERE t.course = ? AND p.modename = 'template' GROUP BY t.id, p.modename

EOD;

    $templates2 = $DB->get_records_sql($sql, array($courseid));

    foreach ($templates as $templateid => $template) {
        if (array_key_exists($templateid, $templates2)   ) {
            $template->used = $templates2[$templateid]->used;
        } else {
            $template->used = 0;
        }

    }

    return $templates;

}



/**
 * Gets list of scaffolds in the system for current course
 *
 * @param int $courseid
 * @param string $sort optional name of field to sort on
 * @return array an array of associative arrays - scaffold ids and names
 */


function portfolioactmode_template_get_available_scaffolds($courseid, $sort = 'id') {
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
 * Checks if browser is safe browser
 *
 * @return true, if browser is safe browser else false
 */

function portfolioactmode_template_check_safe_browser() {
    return strpos($_SERVER['HTTP_USER_AGENT'], "SEB") !== false;
}

/**
 * Used to include custom Javascript for this module
 *
 * @return array
 */

function portfolioactmode_template_get_js_module() {
    global $PAGE;
    return array(
        'name' => 'portfolioactmode_template',
        'fullpath' => '/mod/portfolioact/mode/template/module.js',
        'requires' => array('base', 'dom',  'io', 'node', 'json',
        'node-event-simulate')
    );
}

/**
 * Returns all available items except reference items for the current course
 *
 * @param int $pageid - optional param: EXCLUDE items on this page
 * @return array
 *
 */

function portfolioactmode_template_get_available_items($pageid = null) {

    global $PAGE, $DB, $CFG;

    $courseid = $PAGE->course->id;

    $extrawhere='';
    if (! is_null($pageid)) {
        $extrawhere = ' AND p.id != ' . $pageid;
    }

    $sql = <<<EOD
    SELECT i.id as itemid, i.name as itemname, t.id as templateid, t.name as templatename FROM
    {portfolioact_tmpl_items} i
    INNER JOIN {portfolioact_tmpl_pages} p ON p.id  = i.page
    INNER JOIN {portfolioact_template} t ON t.id  = p.template
    WHERE t.course = ? AND i.type != 'reference' $extrawhere
    ORDER BY t.name, i.name
EOD;

    $items = $DB->get_records_sql($sql, array($courseid));

    $renderer = $PAGE->get_renderer('portfolioactmode_template');
    $ret = array();
    $templates = array();
    $ctr=1;
    foreach ($items as $id => $itemdata) {

        if (! in_array($itemdata->templateid, $templates )) {

            $ret['--'.$ctr] = $renderer->select_box_heading($itemdata->templatename);
            $templates[] =  $itemdata->templateid;
            $ctr++;

        }

        $ret[$id] = $itemdata->itemname;

    }

    return $ret;

}

/**
 * Converts a settings array ready to be added to DB
 *
 *
 * @param array $settings
 * @return array
 */

function portfolioactmode_template_settingskeys_in($settings) {
    $todb = json_encode($settings);
    //remove url slashes for backup/restore
    $todb = str_replace('\/', '/', $todb);
    return $todb;
}

/**
 * Converts a settings string back to an array
 *
 *
 * @param string $settings
 * @return array
 */

function portfolioactmode_template_settingskeys_out($settings) {

    return json_decode($settings, true);
}

/**
 * Return an array of activities which use this template.
 *
 * Return an array of activities which use this scaffold. Also includes template activities
 * which use this scaffold.
 *
 * @param int $scaffid
 * @param int $courseid
 * @return array of objects
 */


function portfolioactmode_template_inactivities($templateid, $courseid) {

    global $CFG, $DB;

    $sql = <<<EOD
    SELECT  p.id, p.name   FROM
    {portfolioact_template} t
    LEFT JOIN {portfolioact_tmpl_settings} s ON s.template  = t.id
    LEFT JOIN {portfolioact} p ON s.actid  = p.id
    WHERE t.course = ? AND p.modename = 'template' AND t.id = ?

EOD;

    $activities = $DB->get_records_sql($sql, array($courseid, $templateid));//empty array or array of objects

    return $activities;
}


/**
 * Return true if value is numeric
 *
 * @param mixed $a
 * @return boolean
 */

function portfolioactmode_template_isnumeric($a) {

    if (is_numeric($a)) {
        return true;
    } else {
        return false;
    }

}

/**
 * Delete some entries
 *
 * Delete some entries for the given user (not necessarily the logged in user).
 *
 * @param array $itemids
 * @param int $filecontext ID of (course) context of any files against entry
 * @return boolean
 *
 */

function portfolioactmode_template_delete_entries($itemids, $filecontext = null) {
    global $DB;

    if (empty($itemids)) {
        return true;
    }
    $params = array();
    list($in_sql, $in_params) = $DB->get_in_or_equal($itemids);
    $where = "itemid $in_sql";
    $params = array_merge($params, $in_params);

    $DB->delete_records_select('portfolioact_tmpl_entries', $where, $params);

    if (!is_null($filecontext)) {
        $fs = get_file_storage();
        foreach ($itemids as $id) {
            $fs->delete_area_files($filecontext, 'portfolioactmode_template', 'entry', $id);
        }
    }

    return true;

}

/**
 * Return array to pass to format_text
 *
 * @return array
 */

function portfolioactmode_template_formatoptions() {

       return array(
            'filter'  => true
        );

}

/**
 * Core hook that is called when downloading template files.
 * @param unknown_type $course
 * @param unknown_type $cm
 * @param unknown_type $context
 * @param unknown_type $filearea
 * @param unknown_type $args
 * @param unknown_type $forcedownload
 * @return boolean
 */
function portfolioactmode_template_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE && $context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    $fileareas = array('question', 'entry');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $versionid = (int)array_shift($args);

    $checklogin = true;
    if (!empty($_SERVER['PHP_SELF'])) {
        if (strpos($_SERVER['PHP_SELF'], 'mod/portfolioact/mode/template/pluginfile.php')) {
            $checklogin = false;
        }
    }
    if ($checklogin) {
        require_course_login($course, true, $cm);
        // Where file is stored against course context this is not so precise...
        require_capability('mod/portfolioact:canview', $context);

        if ($filearea == 'entry') {
            // Make sure only owner can see.
            $result = $DB->get_record('portfolioact_tmpl_entries', array('id' => $versionid));
            if (!$result || $USER->id != $result->userid) {
                return false;
            }
        }
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/portfolioactmode_template/$filearea/$versionid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    if (($filearea == 'question' || $filearea == 'entry') &&
            !file_extension_in_typegroup($file->get_filename(), 'image')) {
        return false;
    }

    send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}
