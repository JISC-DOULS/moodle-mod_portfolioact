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
 * Manages saving and editing templates
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/save/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/lib.php');

global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;

$actid = required_param('actid', PARAM_INT);
$cmid = required_param('id', PARAM_INT);
$savetype = required_param('savetype', PARAM_ALPHA);
$stage = optional_param('stage', 0, PARAM_INT);

$portfolioact = $DB->get_record('portfolioact', array('id' => $actid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $portfolioact->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('portfolioact',
$portfolioact->id, $course->id, false, MUST_EXIST);

require_login($course, false, $cm);

$subplug = portfolioact_save::get_export_module($savetype, $actid, $cm->id);

$context = get_context_instance(CONTEXT_MODULE, $PAGE->cm->id);

require_capability('mod/portfolioact:canview', $context );

$url = new moodle_url('/mod/portfolioact/save/save.php',
array('actid'=>$actid, 'type'=>$savetype));
$PAGE->set_url($url);
$title = $subplug->get_title();

$PAGE->navbar->add($title);
$PAGE->set_title($title);

if (!portfolioact_save::save_type_enabled($savetype, $portfolioact->id)) {
    //Someone hacked the save type and it is not available
    throw new moodle_exception('unauthorizedaccess', 'portfolioact');
}

if ($savetype == 'google') {

    $modetype = portfolioact_mode::get_plugin_mode($actid);

    $ajaxenabled = ajaxenabled();

    echo $subplug->renderer->header();
    echo $subplug->renderer->render_page_header();

    include_once($CFG->dirroot.'/lib/googleapi.php');
    include_once($CFG->dirroot.'/mod/portfolioact/save/google/lib.php');
    $class     = 'portfolioact_google_save';
    if (! class_exists($class)) {
        throw new coding_exception('The requested export plugin appears to be missing.');
    }

    //set up the google docs api and make sure there is a course folder
    if (! class_exists('portfoliaoctsave_google_authsub')) {
        include_once($CFG->dirroot.'/mod/portfolioact/save/google/lib.php');
    }

    $savedgoogletoken = portfolioactsave_google_docs::get_sesskey($USER->id, $cm->id);

    if ($stage == 0) {

        $realm =  'http://docs.google.com/feeds/ http://spreadsheets.google.com/feeds/ http://docs.googleusercontent.com/';
        $return = qualified_me() . '&stage=1';

        if (empty($savedgoogletoken)) {
            $uri = portfolioactsave_google_authsub::login_url($return, $realm, $cm->id);
            echo $subplug->renderer->render_googlesignin($uri);
            echo $subplug->renderer->footer();
            exit;

        } else {
            try {
                $google_authsub =
                    new portfolioactsave_google_authsub($savedgoogletoken,
                        '', array('debug'=>0), $cm->id);
                //attempt to log them on with the stored token failed. Start again.
            } catch (Exception $e) {
                portfolioactsave_google_docs::delete_sesskey($USER->id, $cm->id);
                $uri = $google_authsub::login_url($return, $realm );
                echo $subplug->renderer->render_googlesignin($uri);
                echo $subplug->renderer->footer();
                exit;
            }
        }

    } else if ($stage == 1) {//come back from google.

        $authtoken = $_GET['token'];
        try {
            $google_authsub = new portfolioactsave_google_authsub('',
                $authtoken , array('debug'=>0), $cm->id);
        } catch (Excception $e) {
            $url = new moodle_url('/mod/portfolioact/view.php',
                array('id' => $cm->id ));
            throw new moodle_exception('exportgoogleerror',
                'portfolioactsave_google', $url);
        }
        //we can save the upgraded token
        portfolioactsave_google_docs::set_sesskey($google_authsub->get_sessiontoken(),
            $USER->id, $cm->id);

    } else {
        throw new coding_exception('Script called with unexpected parameter');
    }




    if (! $ajaxenabled) {
        $result = $subplug->retrieve_data($modetype, $actid);
        if ($result === false) {
            $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid));
            throw new moodle_exception('exportfailed', 'portfolioact',
                $url, $subplug->error_message);
        }
    }


    if ($ajaxenabled) {
        echo $subplug->renderer->render_javascript_disabled_message();
    }



    if ($ajaxenabled) {
        echo $subplug->renderer->render_be_patient('hide');
        echo $subplug->renderer->render_ajax_spinner('hide');
    } else {
        echo $subplug->renderer->render_be_patient('show');
        echo $subplug->renderer->render_ajax_spinner('show');
    }

    echo $subplug->renderer->render_message_area();

    if (ob_get_contents() !== false ) {
        ob_flush();
    }

    //this MAY help make sure the
    //spinner and patient message arrive before the start of
    //the wait time for Google
    flush();

    //catch exceptions in the google libs
    //so we can turn off the spinner and
    //provide a better continue link


    if (! $ajaxenabled) {
        try {
            $result = $subplug->export_data($google_authsub);
        } catch (Exception $e) {
            $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid));
            $PAGE->requires->js_init_call('M.portfolioactsave_google.googleexport',
            array(''), true, portfolioactsave_google_get_js_module());
            throw new moodle_exception('exportfailed',
                'portfolioact', $url, $subplug->error_message);
        }

        if ($result === true) {
            if ($subplug->error_files > 0) {
                $message = get_string('exportgooglesuccesssome_errors', 'portfolioactsave_google');
            } else {
                $message = get_string('exportgooglesucces', 'portfolioactsave_google');
            }


            $PAGE->requires->js_init_call('M.portfolioactsave_google.googleexport',
            array(''), true, portfolioactsave_google_get_js_module());
            echo $subplug->renderer->export_message($message);
        } else {
            $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid));
            $PAGE->requires->js_init_call('M.portfolioactsave_google.googleexport',
            array(''), true, portfolioactsave_google_get_js_module());
            throw new moodle_exception('exportfailed',
                'portfolioact', $url, $subplug->error_message);
        }


    }

    if ($ajaxenabled) {
        echo $subplug->renderer->render_message_area_success();
        echo $subplug->renderer->render_message_area_error();
        $PAGE->requires->js_init_call('M.portfolioactsave_google.init',
        array($actid, $modetype, $cmid, 'google'), true, portfolioactsave_google_get_js_module());
    }

    add_to_log($course->id, 'portfolioact', 'save', "save.php?id=" . $cm->id,
        'Export to Google', $cm->id);
    echo $subplug->renderer->footer();



} //end savetype google

if ($savetype == 'file') {

    $modetype = portfolioact_mode::get_plugin_mode($actid);

    $result = $subplug->retrieve_data($modetype, $actid);


    if ($result === false) {
        $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid));
        throw new moodle_exception('exportfailed', 'portfolioact', $url, $subplug->error_message);
    }

    $result = $subplug->export_data();

    if ($result === false) {
        $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid));
        throw new moodle_exception('exportfailed', 'portfolioact', $url, $subplug->error_message);
    }

    if (isset($subplug->immediateoutput)) {
        if (isset($subplug->immediateoutput['fullpath'])) {

            add_to_log($course->id, 'portfolioact', 'save', "save.php?id=" . $cm->id,
                'Save zip', $cm->id);
            send_file($subplug->immediateoutput['fullpath'],
                $subplug->immediateoutput['filename'], 1,
                0, false, true);
        } else if (isset($subplug->immediateoutput['filedata'])) {
             add_to_log($course->id, 'portfolioact', 'save', "save.php?id=" . $cm->id,
                'Save file', $cm->id);
             send_file($subplug->immediateoutput['filedata'],
                $subplug->immediateoutput['filename'], 1,  0, true, true);

        } else {
            throw new coding_exception
                ('There is neither filedata nor a fullpath on the output object');
        }
    } else {
        $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid));
        throw new moodle_exception('exportfailed', 'portfolioact', $url, $subplug->error_message);

    }

}
