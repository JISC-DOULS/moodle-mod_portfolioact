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
 * Portfolioact Template module renderering methods are defined here
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
global $CFG;
require_once($CFG->dirroot.'/mod/portfolioact/renderer.php');

/**
 * Portfolioact template module renderer class
 *
 * @see core_renderer Core renderer (you can call methods in this)
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioactmode_template_renderer extends mod_portfolioact_renderer {


    /**
     * Output a level 2 heading
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_template_generich2($text) {
        return $this->output->heading($text, 2);
    }


    /**
     * Output a level 3 heading
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_template_generich3($text) {
        return $this->output->heading($text, 3);
    }

    /**
     * Output a level 4 heading
     *
     * @param string $text
     * @return string
     */
    public function render_portfolioactmode_template_generich4($text) {
        return $this->output->heading($text, 4);
    }


    /**
     * Output a page heading for Templates
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_template_list() {
        return $this->output->heading(get_string('listheading', 'portfolioactmode_template'), 2);
    }

    /**
     * Output some text in a div
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_template_simpletext($text, $class) {
        return $this->output->container($text, $class);
    }

    /**
     * Output information message about creating a page
     *
     * @return string
     *
     */

    public function render_portfolioactmode_template_startpages() {

        $string = $this->output->container
        (get_string('startpages', 'portfolioactmode_template'), '');
        $string.= '<br />';
        return $string;
    }

    /**
     * Output button to create template
     *
     * @param int $cmid
     * @return string
     */

    public function render_portfolioactmode_template_createnewbutton($cmid) {
        $buttontext = get_string('createtemplate', 'portfolioactmode_template');
        $url = new moodle_url('/mod/portfolioact/mode/template/addtemplate.php',
        array('id'=>$cmid));
        return $this->output->single_button($url,
        $buttontext, 'get', array('disabled'=>false, 'title'=>$buttontext));
    }


    /**
     * Generates a button to add a new page to a template
     *
     * @param int $cmid
     * @param int $templateid
     */

    public function render_portfolioactmode_template_createnewpagebutton($cmid, $templateid) {
        $buttontext = get_string('createpage', 'portfolioactmode_template');
        $url = new moodle_url('/mod/portfolioact/mode/template/addpage.php',
        array('id'=>$cmid, 'templateid'=>$templateid));
        echo $this->output->single_button($url,
        $buttontext, 'get', array('disabled'=>false, 'title'=>$buttontext));
    }


    /**
     * Generates message and back button in case of no templates on settings page.
     *
     * @param int $cmid
     * @return string html
     */

    public function render_portfolioactmode_template_notemplatesmessage($cmid) {
        $msg = get_string('notemplatesavailablemessage', 'portfolioactmode_template');
        $button = get_string('notemplatesavailablecontinue', 'portfolioactmode_template');
        $url = new moodle_url('/mod/portfolioact/mode/template/manager.php',
        array('id'=>$cmid, 'action'=>'list'));
        $string = $this->output->container($msg, '');
        $string.= $this->output->single_button($url, $button, 'get',
        array('disabled'=>false, 'title'=>$button));
        return $string;
    }

    /**
     * Output confirm template delete package
     *
     * @param int $cmid
     * @param string $templatename
     * @param int $templateid
     * @return string
     */

    public function render_portfolioactmode_template_confirmdelete($cmid, $templatename,
    $templateid) {
        $label = get_string('confirmdelete', 'portfolioactmode_template',
        $templatename );
        $buttonoktext = get_string('ok', 'portfolioactmode_template');
        $buttonnotext = get_string('no', 'portfolioactmode_template');
        $string = $this->output->container($label, '');
        $url = new moodle_url('/mod/portfolioact/mode/template/delete.php',
        array('id'=>$cmid,
            'template'=>$templateid, 'actual'=>1));

        $okbutton =  $this->output->single_button($url, $buttonoktext, 'get',
        array('disabled'=>false, 'title'=>$buttonoktext,
            'class'=>'portfolioact-template-okbutton'));

        $string.= $okbutton;
        $url = new moodle_url('/mod/portfolioact/mode/template/manager.php',
        array('id'=>$cmid, 'action'=>'list'));
        $string.= $this->output->single_button($url, $buttonnotext, 'get',
        array('disabled'=>false, 'title'=>$buttonnotext,
            'class'=>'portfolioact-template-cancelbutton'));
        return $string;
    }

    /**
     * Output confirm page delete package
     *
     * @param int $cmid
     * @param string $pagename
     * @param int $templateid
     * @param int $pageid
     * @return string
     */


    public function render_portfolioactmode_template_confirmpagedelete
    ($cmid, $pagename, $templateid, $pageid) {
        $label = get_string('confirmpagedelete', 'portfolioactmode_template',
        $pagename );
        $buttonoktext = get_string('ok', 'portfolioactmode_template');
        $buttonnotext = get_string('no', 'portfolioactmode_template');
        $string =  $this->output->container($label, '');
        $url = new moodle_url('/mod/portfolioact/mode/template/pagedelete.php',
        array('id'=>$cmid,
            'templateid'=>$templateid, 'pageid'=>$pageid, 'actual'=>1));

        $okbutton = $this->output->single_button($url, $buttonoktext, 'get',
        array('disabled'=>false, 'title'=>$buttonoktext,
            'class'=>'portfolioact-template-okbutton'));

        $string.= $okbutton;

        $url = new moodle_url('/mod/portfolioact/mode/template/designer.php',
        array('id'=>$cmid, 'templateid'=>$templateid));
        $string.= $this->output->single_button($url, $buttonnotext, 'get',
        array('disabled'=>false, 'title'=>$buttonnotext,
            'class'=>'portfolioact-template-cancelbutton'));
        return $string;
    }

    /**
     * Output confirm item delete package
     *
     * @param int $cmid
     * @param string $itemname
     * @param int $templateid
     * @param int $pageid
     * @param int $itemid
     * @return string
     *
     */


    public function render_portfolioactmode_template_confirmitemdelete(
    $cmid, $itemname, $templateid, $pageid, $itemid) {
        $buttonoktext = get_string('ok', 'portfolioactmode_template');
        $buttonnotext = get_string('no', 'portfolioactmode_template');

        $string = html_writer::tag('p', get_string('confirmitemdelete',
            'portfolioactmode_template', $itemname) );

        $url = new moodle_url('/mod/portfolioact/mode/template/itemdelete.php',
        array('id'=>$cmid, 'templateid'=>$templateid, 'pageid'=>$pageid, 'itemid'=>$itemid,
                'actual'=>1));

        $okbutton = $this->output->single_button($url, $buttonoktext, 'get',
        array('disabled'=>false, 'title'=>$buttonoktext,
            'class'=>'portfolioact-template-okbutton'));

        $string.= $okbutton;
        $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
        array('id'=>$cmid, 'templateid'=>$templateid, 'pageid'=>$pageid));
        $string.= $this->output->single_button($url, $buttonnotext, 'get',
        array('disabled'=>false, 'title'=>$buttonnotext,
            'class'=>'portfolioact-template-cancelbutton'));
        return $string;
    }


    /**
     * Builds a table for displaying templates
     *
     * @param mixed $data
     * @param int $cmid
     */


    public function render_portfolioactmode_template_table($data, $cmid) {

        $caption = get_string('templatemanagertablecaption', 'portfolioactmode_template');

        $table = new html_table();
        $name=get_string('name', 'portfolioactmode_template');
        $created=get_string('created', 'portfolioactmode_template');
        $modified=get_string('modified', 'portfolioactmode_template');
        $used = get_string('used', 'portfolioactmode_template');
        $actions = get_string('actions', 'portfolioactmode_template');
        $table->head = array($name, $created, $modified, $used, $actions);
        $table->attributes['class'] = 'generaltable portfolioact_designertable';

        foreach ($data as $templateinfo) {

            $editlink =  new moodle_url('/mod/portfolioact/mode/template/designer.php',
            array('templateid'=>$templateinfo->id, 'id'=>$cmid));
            $editbutton =  $this->output->action_icon($editlink,
            new pix_icon('t/edit', get_string('edit')));

            $deletelink =  new moodle_url('/mod/portfolioact/mode/template/delete.php',
            array('template'=>$templateinfo->id, 'id'=>$cmid));
            $deletebutton =  $this->output->action_icon($deletelink,
            new pix_icon('t/delete', get_string('delete')));

            $timecreated = userdate($templateinfo->timecreated,
            get_string('strftimedatetime', 'langconfig'));
            if (!is_null($templateinfo->timemodified)) {
                $timemodified = userdate($templateinfo->timemodified,
                get_string('strftimedatetime', 'langconfig'));
            } else {
                $timemodified = '';
            }

            $table->data[] = array($templateinfo->name,
            $timecreated, $timemodified, $templateinfo->used, $editbutton . $deletebutton);
        }

        return html_writer::table($table);

    }


    /**
     * Builds the table to display the pages in a template
     *
     * @param array $pages
     * @param int $cmid
     * @param int $templateid
     * @return string html data
     */

    public function render_portfolioactmode_template_pages_table(
    $pages, $cmid, $templateid) {

        $this->caption = get_string('templatedesignertablecaption',
            'portfolioactmode_template');

        $table = new html_table();

        $name=get_string('pagename', 'portfolioactmode_template');
        $position = get_string('position', 'portfolioactmode_template');
        $actions = get_string('actions', 'portfolioactmode_template');
        $reorder = get_string('reorder', 'portfolioactmode_template');
        $table->head = array($position, $name, $reorder,  $actions);
        $table->attributes['class'] = 'generaltable portfolioact_designertable';

        $max = count($pages);
        $ctr = 0;

        foreach ($pages as $pageinfo) {

            $editlink =  new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
            array('templateid'=>$templateid, 'pageid'=>$pageinfo->id,
                     'id'=>$cmid));
            $editbutton =  $this->output->action_icon($editlink,
            new pix_icon('t/edit', get_string('edit')));

            $deletelink =  new moodle_url('/mod/portfolioact/mode/template/pagedelete.php',
            array('templateid'=>$templateid, 'pageid'=>$pageinfo->id,
                    'id'=>$cmid));
            $deletebutton =  $this->output->action_icon($deletelink,
            new pix_icon('t/delete', get_string('delete')));

            $position = '<span class="portfolioact-template-pos" id="page_position_' .
            $pageinfo->id . '">' . $pageinfo->position . '</span>';

            if ($max < 2) { //only one item
                $arrows = '';
            } else if ($ctr == 0) {//first of more than one
                $sortdown =  '<span id="page_down_' . $pageinfo->id .
                '" class="portfolioact-template-sort-arrow portfolioact-template-sort-arrow-down">'
                . $this->pix_icon('sortorder-down', get_string('down',
                      'portfolioactmode_template'), 'portfolioactmode_template') . '</span>';

                $arrows =  $sortdown;

            } else if ($ctr == ($max - 1)) {//last of more than one

                $sortup =  '<span id="page_up_' . $pageinfo->id .
                    '" class="portfolioact-template-sort-arrow '.
                ' portfolioact-template-sort-arrow-up">'
                . $this->pix_icon('sortorder-up',
                get_string('up', 'portfolioactmode_template'),
                 'portfolioactmode_template') . '</span>';

                $arrows = $sortup;

            } else { //any other case
                $sortup =  '<span id="page_up_' . $pageinfo->id .
                '" class="portfolioact-template-sort-arrow portfolioact-template-sort-arrow-up">' .
                $this->pix_icon('sortorder-up',
                get_string('up', 'portfolioactmode_template'), 'portfolioactmode_template') .
                 '</span>';

                $sortdown =  '<span id="page_down_' . $pageinfo->id .
                 '" class="portfolioact-template-sort-arrow portfolioact-template-sort-arrow-down">'
                 . $this->pix_icon('sortorder-down',
                 get_string('down', 'portfolioactmode_template'),
                        'portfolioactmode_template') . '</span>';

                 $arrows = $sortup . $sortdown;
            }

            $table->data[] = array($position, $pageinfo->name,
            $arrows, $editbutton . $deletebutton);
            $ctr++;
        }

        return html_writer::table($table);

    }

    /**
     * Build the table which lists items in a template
     *
     * @param array $items
     * @param int $cmid
     * @param int $templateid
     * @param int $pageid
     * @return string
     */

    public function render_portfolioactmode_template_items_table(
    $items, $cmid, $templateid, $pageid) {

        $table = new html_table();
        $table->attributes = array('class'=>'portfolio-template-items-list');
        $name=get_string('itemname', 'portfolioactmode_template');
        $position = get_string('position', 'portfolioactmode_template');
        $actions = get_string('actions', 'portfolioactmode_template');
        $reorder = get_string('reorder', 'portfolioactmode_template');
        $table->head = array('', $position, $name, $reorder, $actions);
        $table->attributes['class'] = 'generaltable portfolioact_designertable';

        $max = count($items);
        $ctr = 0;

        foreach ($items as $iteminfo) {

            $icon = $this->render(
            new pix_icon('item-'.$iteminfo->type, get_string('item'.$iteminfo->type,
                      'portfolioactmode_template'), 'portfolioactmode_template'));

            $editlink =  new moodle_url('/mod/portfolioact/mode/template/itemsettings.php',
            array( 'templateid'=>$templateid, 'pageid'=>$pageid, 'itemid'=>$iteminfo->id,
                   'id'=>$cmid));
            $editbutton =  $this->output->action_icon($editlink,
            new pix_icon('t/edit', get_string('edit')));

            $deletelink =  new moodle_url('/mod/portfolioact/mode/template/itemdelete.php',
            array( 'templateid'=>$templateid, 'pageid'=>$pageid, 'itemid'=>$iteminfo->id,
                    'id'=>$cmid));
            $deletebutton =  $this->output->action_icon($deletelink,
            new pix_icon('t/delete', get_string('delete')));

            $position = '<span class="portfolioact-template-pos" id="item_position_' .
            $iteminfo->id . '">' . $iteminfo->position . '</span>';

            if ($max < 2) { //only 1 item to display

                $arrows = '';

            } else if ( ($ctr == 0)  ) {//first one of more than one
                $sortdown =  '<span id="item_down_' . $iteminfo->id .
                '" class="portfolioact-template-sort-arrow portfolioact-template-sort-arrow-down">'
                . $this->pix_icon('sortorder-down',
                get_string('down', 'portfolioactmode_template'),    'portfolioactmode_template') .
                 '</span>';

                $arrows =  $sortdown;

            } else if ($ctr == ($max - 1) ) {//last one of more than one

                $sortup =  '<span id="item_up_' . $iteminfo->id .
                   '" class="portfolioact-template-sort-arrow portfolioact-template-sort-arrow-up">'
                   . $this->pix_icon('sortorder-up',
                   get_string('up', 'portfolioactmode_template'),    'portfolioactmode_template') .
                    '</span>';

                   $arrows = $sortup;

            } else { //in the middle of a list need both arrows
                $sortup =  '<span id="item_up_' . $iteminfo->id .
                    '" class="portfolioact-template-sort-arrow '.
                    'portfolioact-template-sort-arrow-up">' .
                $this->pix_icon('sortorder-up',
                get_string('up', 'portfolioactmode_template'),    'portfolioactmode_template') .
                    '</span>';

                $sortdown =  '<span id="item_down_' . $iteminfo->id .
                    '" class="portfolioact-template-sort-arrow '.
                    ' portfolioact-template-sort-arrow-down">' .
                $this->pix_icon('sortorder-down',
                get_string('down', 'portfolioactmode_template'),
                          'portfolioactmode_template') . '</span>';

                $arrows = $sortup . $sortdown;
            }

            $position = $position;
            $table->data[] = array($icon, $position, $iteminfo->name,
            $arrows, $editbutton . $deletebutton);
            $ctr++;
        }

        return html_writer::table($table);
    }

    /**
     * Output header for view page
     * @return string
     *
     */

    public function render_portfolioactmode_template_viewhead(portfolioact_mode_plugin $subplug,
        $intro = true) {
        global $OUTPUT;
        $topofpage = $OUTPUT->heading($subplug->cm->name, 2, 'portfolioacthead');
        if ($intro) {
            $topofpage .= html_writer::tag('div', $subplug->portfolioact->intro,
                array('id' => 'portfolioact_intro_text'));
        }
        return $topofpage;
    }

    /**
     * Return a br element
     *
     * @return string
     *
     */

    public function render_portfolioactmode_template_break( ) {
        return '<br />';
    }

    /**
     * Return a horizontal ruler
     *
     * @return string
     */

    public function render_portfolioactmode_template_hr( ) {
        return '<hr />';
    }

    /**
     * Return a colon followed by a space
     *
     * @return string
     */

    public function render_portfolioactmode_template_colonspace( ) {
        return ': ';
    }


    /**
     * Return text in a p tag
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_template_para($text) {
        return html_writer::tag('p', $text);
    }

    /**
     * The text for an instruction item type
     *
     * @param string $text
     * @return string
     */
    public function render_portfolioactmode_template_instructioncontrol($text) {
        return $this->output->container($text, 'portfolioact-template-template-instruction');
    }

    /**
     * Label text fro a question item type
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_template_textlabel($text) {
        return $this->output->container($text, 'portfolioact-template-template-question');

    }

    /**
     * Prints a message above a form for user feedback.
     *
     * @param string $msg
     * @param string $class optional param for class for the div
     * @return string
     */

    public function render_portfolioactmode_template_formmessage($msg, $class = "") {
        return $this->output->container($msg, $class);
    }

    /**
     * Build the message which tells the user they can't delete the item
     *
     * Build the message which tells the user they can't delete the item because
     * reference items depend on it.
     * @param array $items
     * @param int $cmid
     * @param int $templateid
     * @param int $pageid
     * @return string
     *
     */
    public function render_portfolioactmode_template_blockitemdelete(
    $items, $cmid, $templateid, $pageid) {
        if (count($items) == 1) {
            $string = html_writer::tag('p', get_string('blockitemdeletesingular',
                'portfolioactmode_template') );

        } else {
            $string = html_writer::tag('p', get_string('blockitemdeleteplural',
                'portfolioactmode_template') );
        }

        $table = new html_table();
        $table->head = array(get_string('itemid', 'portfolioactmode_template'),
        get_string('itemname', 'portfolioactmode_template'),
        get_string('usedintemplate', 'portfolioactmode_template'));

        foreach ($items as $item) {
            $table->data[] = array($item->id, $item->itemname, $item->templatename);
        }

        $string.= html_writer::table($table);

        $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
        array('templateid'=>$templateid, 'pageid'=>$pageid,
            'id'=>$cmid, 'action'=>'list'));

        $string.= $this->output->single_button($url,
        get_string('cancel', 'portfolioactmode_template'), 'get', array('disabled'=>false,
                 'title'=>get_string('cancel', 'portfolioactmode_template')));
        return $string;
    }

    /**
     * Information message that the Activity has not yet been set up
     *
     * @return string
     *
     */

    public function render_portfolioactmode_template_notyetsetup() {

        $string = html_writer::tag('p', get_string('notyetsetup',
                'portfolioactmode_template') );
        return $string;

    }

    /**
     * Outputs a cancel button
     * @return string
     *
     */

    public function render_portfolioactmode_template_noitems($cmid, $templateid, $pageid) {

        $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
        array('id'=>$cmid, 'templateid'=>$templateid, 'pageid'=>$pageid));

        $cancel = get_string('cancel', 'portfolioactmode_template');

        return $this->output->single_button($url, $cancel, 'get',
        array('disabled'=>false,   'title'=>$cancel));
    }

    /**
     * Return a message in case of Activity which has a template but no pages.
     *
     * @param $cmid;
     * @param int  $templateid
     * @param boolean $canedit
     * @return string
     */

    public function render_portfolioactmode_nopages($cmid, $templateid, $canedit) {

        $string = html_writer::tag('p', get_string('notyetsetup',
                'portfolioact') );

        if ($canedit) {

            $string = html_writer::tag('p', get_string('nopages',
                'portfolioactmode_template') );

            $url = new moodle_url('/mod/portfolioact/mode/template/designer.php',
            array('id'=>$cmid, 'templateid'=>$templateid));
            $nopagesbutton = get_string('nopagesbutton', 'portfolioactmode_template');
            $string.= $this->output->single_button($url, $nopagesbutton, 'get',
            array('disabled'=>false,   'title'=>$nopagesbutton));

        }

        return $string;
    }


    /**
     * Format a string to display as a heading in a select box
     *
     * @param string $text
     * @return string
     */

    public function select_box_heading($text) {
        return '--' . strtoupper($text) . '--';

    }

    /*
     * Format the page heading
     *
     * @param $text string
     * @return string
     *
     */


    public function render_portfolioactmode_template_page_head($text) {
        return html_writer::tag('h1', $text );

    }


    /*
     * Produce the html which will be converted to a page break
     *
     * @see /local/html2rtf/xhtml2rtf.xsl
     *
     * @return string
     */

    public function render_portfolioactmode_template_page_break() {
        return html_writer::tag('p', '', array('style'=>'page-break-after:always') );

    }

    /*
     * Produce a list (in table form) of Activities in which the template is
     * used.
     *
     * @param int $cmid
     * @param string $templatename
     * @param int $templateid
     * @param array $usedinactivities actid => act name
     * @return string
     *
     */

    public function render_portfolioactmode_template_deletenotallowed
    ($cmid, $templatename, $templateid, $usedinactivities) {

        if (count($usedinactivities) == 1) {
            $string = html_writer::tag('p', get_string('blocktemplatedeletesingular',
                'portfolioactmode_template', $templatename ) );

        } else {
            $string = html_writer::tag('p', get_string('blocktemplatedeleteplural',
                'portfolioactmode_template' , $templatename ) );
        }

        $table = new html_table();
        $table->head = array(get_string('actid', 'portfolioactmode_template'),
        get_string('actname', 'portfolioactmode_template'));

        foreach ($usedinactivities as $id => $activity) {
            $table->data[] = array($id, $activity->name );
        }

        $string.= html_writer::table($table);

        $url = new moodle_url('/mod/portfolioact/mode/template/manager.php',
        array('id'=>$cmid, 'action'=>'list'));

        $string.= $this->output->single_button($url,
        get_string('cancel', 'portfolioactmode_template'), 'get', array('disabled'=>false,
                 'title'=>get_string('cancel', 'portfolioactmode_template')));

        return $string;

    }

    /**
     * Returns a not yet set up message
     *
     * @return string
     *
     */

    public function render_notyetsetup() {

        return html_writer::tag('p', get_string('notyetsetup', 'portfolioactmode_template'));
    }

    /**
     * Returns a message to display if there is no JS class on the body
     *
     * @return string
     *
     */

    public function render_nojsmessage_pages() {
        return html_writer::tag('p', get_string('javascript_disabled_position_pages',
            'portfolioactmode_template'),
        array('class'=>'portfolioactmode_template_javascript_disabled'));
    }

    /**
     * Returns a message to display if there is no JS class on the body
     *
     * @return string
     *
     */

    public function render_nojsmessage_items() {
        return html_writer::tag('p', get_string('javascript_disabled_position_items',
            'portfolioactmode_template'),
            array('class'=>'portfolioactmode_template_javascript_disabled'));
    }


    /*
     * Returns a message about the codes which can be used in instructions
     *
     *
     */

    public function render_instructionmessage() {
              return html_writer::tag('p', get_string('instructionspecialmessage',
                  'portfolioactmode_template'),
        array('class'=>'portfolioactmode_template_instructionmessage'));
    }




    /**
     * Return a message in case of Scaffold with no files.
     *
     * @param $cmid;
     * @param boolean $canedit
     * @param int $scaffoldid
     * @return string
     */

    public function render_emptyscaffold($cmid, $canedit, $scaffoldid) {

        $string = html_writer::tag('p', get_string('notyetsetup',
                'portfolioact') );

        if ($canedit) {

            $string.= html_writer::tag('p', get_string('emptyscaffold',
                'portfolioactmode_template'));

            $url = new moodle_url('/mod/portfolioact/mode/scaffold/designer.php',
            array('id'=>$cmid, 'scaffoldid' => $scaffoldid));
            $edit = get_string('setitup', 'portfolioact');
            $string.= $this->output->single_button($url, $edit, 'get',
            array('disabled'=>false,   'title'=>$edit));

        }

        return $string;
    }

}
