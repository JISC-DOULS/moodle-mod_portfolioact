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
 * Portfolioact Scaffold module renderering methods are defined here
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
global $CFG;
require_once($CFG->dirroot.'/mod/portfolioact/renderer.php');
require_once('lang/en/portfolioactmode_scaffold.php');


/**
 * Class to build a table of scaffolds.
 *
 * @package portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

class portfolioactmode_scaffold_table implements renderable {
    public function __construct($data, $cmid) {
        $this->caption = get_string('scaffoldmanagertablecaption', 'portfolioactmode_scaffold');
        $this->data = $data;
        $this->cmid = $cmid;

    }
}


/**
 * Renderer class for scaffolds.
 *
 * @package portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */


class portfolioactmode_scaffold_renderer extends mod_portfolioact_renderer {



    /**
     * Function to build a table to display scaffolds.
     *
     * @param portfolioactmode_scaffold_table $tabledata
     */


    protected function render_portfolioactmode_scaffold_table(
        portfolioactmode_scaffold_table $tabledata) {

        $table = new html_table();
        $name=get_string('name', 'portfolioactmode_scaffold');
        $created=get_string('created', 'portfolioactmode_scaffold');
        $modified=get_string('modified', 'portfolioactmode_scaffold');
        $used = get_string('used', 'portfolioactmode_scaffold');
        $actions = get_string('actions', 'portfolioactmode_scaffold');
        $table->head = array($name, $created, $modified, $used, $actions);

        foreach ($tabledata->data as $scaffoldinfo) {

            $editlink =  new moodle_url('/mod/portfolioact/mode/scaffold/designer.php',
                array('scaffoldid'=>$scaffoldinfo->id, 'id'=>$tabledata->cmid));
            $editbutton =  $this->output->action_icon($editlink,
                new pix_icon('t/edit', get_string('edit')));

            $deletelink =  new moodle_url('/mod/portfolioact/mode/scaffold/delete.php',
                array('scaffold'=>$scaffoldinfo->id, 'id'=>$tabledata->cmid));
            $deletebutton =  $this->output->action_icon($deletelink,
                new pix_icon('t/delete', get_string('delete')));

            $timecreated = userdate($scaffoldinfo->timecreated,
                get_string('strftimedatetime', 'langconfig'));
            if (!is_null($scaffoldinfo->timemodified)) {
                $timemodified = userdate($scaffoldinfo->timemodified,
                    get_string('strftimedatetime', 'langconfig'));
            } else {
                $timemodified = '';
            }

            $table->data[] = array($scaffoldinfo->name,
                $timecreated, $timemodified, $scaffoldinfo->used, $editbutton . $deletebutton);
        }

        return html_writer::table($table);

    }


    /**
     * Heading for scaffold pages.
     *
     * @return string
     *
     */

    public function render_portfolioactmode_scaffold_editlist() {
        return $this->output->heading(get_string('listheading', 'portfolioactmode_scaffold'), 2);
    }

    /**
     * Text generator for scaffolds
     *
     * @param string $text
     * @param string class
     * @return string
     */

    public function render_portfolioactmode_scaffold_simpletext($text, $class) {
        return $this->output->container($text, $class);
    }

    /**
     * Returns a level 3 header
     *
     * @param string $text
     * @return string
     */

    public function render_portfolioactmode_scaffold_generich3($text) {
        return $this->output->heading($text, 3);
    }

    /**
     * Create button to add a new scaffold
     *
     * @param int $cmid
     * @return string
     */

    public function render_portfolioactmode_scaffold_createnewbutton($cmid) {
        $buttontext = get_string('createscaffold', 'portfolioactmode_scaffold');
        $url = new moodle_url('/mod/portfolioact/mode/scaffold/addscaffold.php',
            array('id'=>$cmid));
              return $this->output->single_button($url,
            $buttontext, 'get', array('disabled'=>false, 'title'=>$buttontext));
    }


    /**
     * Updated message
     *
     * @return string
     */

    public function render_portfolioactmode_scaffold_updated() {
        return html_writer::tag('p', get_string('updated', 'portfolioactmode_scaffold'));
    }

    /**
     * Produces two buttons for confirm delete
     *
     * @param int $cmid
     * @param string $scaffoldname
     * @param int $scaffoldid
     * @return string
     */

    public function render_portfolioactmode_scaffold_confirmdelete(
        $cmid,  $scaffoldname, $scaffoldid) {
        $buttonoktext = get_string('ok', 'portfolioactmode_scaffold');
        $buttonnotext = get_string('no', 'portfolioactmode_scaffold');
        $string = $this->output->container(get_string('confirmdelete',
            'portfolioactmode_scaffold', $scaffoldname), '');
        $url = new moodle_url('/mod/portfolioact/mode/scaffold/delete.php',
            array('id'=>$cmid,
            'scaffold'=>$scaffoldid, 'actual'=>1));
        $okbutton = $this->output->single_button($url, $buttonoktext, 'get',
            array('disabled'=>false,   'title'=>$buttonoktext,
                'class'=>'portfolioact-scaffold-okbutton'));

        $string.= $okbutton;

            $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
            array('id'=>$cmid, 'action'=>'list'));
        $string.= $this->output->single_button($url, $buttonnotext, 'get',
            array('disabled'=>false, 'title'=>$buttonnotext));
        return $string;
    }

    /**
     * Handles case of no scaffolds and provides escape button
     *
     * @param int $cmid
     * @return string
     */


    public function render_portfolioactmode_scaffold_noscaffoldsmessage($cmid) {
        $msg = get_string('noscaffoldsavailablemessage', 'portfolioactmode_scaffold');
        $button = get_string('noscaffoldsavailablecontinue', 'portfolioactmode_scaffold');
        $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
            array('id'=>$cmid, 'action'=>'list'));
        $string = $this->output->container($msg, '');
        $string.= $this->output->single_button($url, $button, 'get',
            array('disabled'=>false, 'title'=>$button));
        return $string;
    }

    public function render_noscaffolds() {
        return $this->output->container(get_string('noscaffolds', 'portfolioactmode_scaffold'));

    }

    /**
     * Prints a message above a form for user feedback.
     *
     * @param string $msg
     * @return string
     */

    public function render_portfolioactmode_scaffold_formmessage($msg) {
        return $this->output->container($msg, '');
    }

    /**
     * Prints a page ehading on the scaffold activity page
     *
     * @return string
     *
     */

    public function render_portfolioactmode_scaffold_viewhead(portfolioact_mode_plugin $subplug) {
        global $OUTPUT;
        $topofpage = $OUTPUT->heading($subplug->cm->name, 2, 'portfolioacthead');
        $context = context_module::instance($subplug->cm->id);
        $introtext = file_rewrite_pluginfile_urls($subplug->portfolioact->intro, 'pluginfile.php',
                $context->id, 'mod_portfolioact', 'intro', null);
        $topofpage .= html_writer::tag('div', $introtext,
            array('id' => 'portfolioact_intro_text'));
        return $topofpage;
    }


    /*
     * Produce a list (in table form) of Activities in which the scaffold is
     * used.
     *
     * @param int $cmid
     * @param string $scaffoldname
     * @param int $scaffoldid
     * @param array $usedinactivities actid => act name
     * @return string
     *
     */

    public function render_portfolioactmode_scaffold_deletenotallowed($cmid, $scaffoldname,
        $scaffoldid, $usedinactivities) {
        if (count($usedinactivities) == 1) {
            $string = html_writer::tag('p', get_string('blockscaffolddeletesingular',
                'portfolioactmode_scaffold', $scaffoldname) );

        } else {
            $string = html_writer::tag('p', get_string('blockscaffolddeleteplural',
                'portfolioactmode_scaffold', $scaffoldname) );
        }

        $table = new html_table();
        $table->head = array(get_string('actid', 'portfolioactmode_scaffold'),
            get_string('actname', 'portfolioactmode_scaffold'),
                get_string('mode', 'portfolioactmode_scaffold'));

        foreach ($usedinactivities as $id => $activity) {
             $table->data[] = array($id, $activity->name, $activity->mode );
        }

        $string.= html_writer::table($table);

        $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
        array('id'=>$cmid, 'action'=>'list'));

        $string.= $this->output->single_button($url,
            get_string('cancel', 'portfolioactmode_scaffold'), 'get', array('disabled'=>false,
                 'title'=>get_string('cancel', 'portfolioactmode_scaffold')));

        return $string;

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
                'portfolioactmode_scaffold'));

            $url = new moodle_url('/mod/portfolioact/mode/scaffold/designer.php',
                array('id'=>$cmid, 'scaffoldid' => $scaffoldid));
            $edit = get_string('setitup', 'portfolioact');
            $string.= $this->output->single_button($url, $edit, 'get',
            array('disabled'=>false,   'title'=>$edit));

        }

        return $string;
    }

}
