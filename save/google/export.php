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
 * AJAX export page for google
 *
 * @package    mod
 * @subpackage mymodule
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/save/lib.php');

$actid = required_param('actid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$savetype = required_param('savetype', PARAM_ALPHA);
$mode = required_param('mode', PARAM_ALPHA);

$portfolioact = $DB->get_record('portfolioact', array('id' => $actid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $portfolioact->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('portfolioact',
    $portfolioact->id, $course->id, false, MUST_EXIST);

$PAGE->set_cm($cm, $course);

if (! isloggedin()) {
    echo '{"status": 0}';
    exit;
}
require_login($course, false, $cm);//Check user access to course+cm

$subplug = portfolioact_save::get_export_module($savetype, $actid, $cmid);

try {
    // Set return url to save.php?
    $returnurl = new moodle_url('mod/portfolioact/save/save.php');
    $returnurl->param('savetype', $savetype);
    $returnurl->param('actid', $actid);
    $returnurl->param('id', $cmid);

    $clientid = '';
    $secret = '';
    if ($record = $DB->get_record('portfolio_instance', array('plugin' => 'googledocs', 'visible' => 1))) {
        $id = $record->id;
        if ($configs = $DB->get_records('portfolio_instance_config', array('instance' => $id))) {
            foreach ($configs as $config) {
                if ($config->name == 'clientid' && !empty($config->value)) {
                    $clientid = $config->value;
                }
                if ($config->name == 'secret' && !empty($config->value)) {
                    $secret = $config->value;
                }
            }
        }
    }

    $google_authsub = new portfolioactsave_google_authsub($clientid, $secret, $returnurl,
            portfolioactsave_google_docs::REALM);
    $google_authsub->set_context(context_module::instance($cm->id));
} catch (Exception $e) {
    $msg = new stdClass();
    $msg->status = 0;
    $msg->error_message = get_string('unknownerror', 'portfolioactsave_google');
    echo json_encode($msg, JSON_FORCE_OBJECT );
    exit;
}


$result = $subplug->retrieve_data($mode, $actid);

if ($result === false) {
    $msg = new stdClass();
    $msg->status = 0;
    $msg->error_message = $subplug->error_message;
    echo json_encode($msg, JSON_FORCE_OBJECT );
    exit;
}


try {
    $result = $subplug->export_data($google_authsub);
} catch (Exception $e) {
    $msg = new stdClass();
    $msg->status = 0;
    $msg->error_message = get_string('unknownerror', 'portfolioactsave_google');
    echo json_encode($msg, JSON_FORCE_OBJECT );
    exit;
}

if ($result === true) {

    $msg = new stdClass();
    $msg->status = 1;
    $msg->error_message = '';

    if ($subplug->error_files > 0) {
        $msg->optional_message =
            get_string('exportgooglesuccesssome_errors', 'portfolioactsave_google');
    } else {
        $msg->optional_message = '';
    }
    echo json_encode($msg, JSON_FORCE_OBJECT );
    exit;
} else {
    $msg = new stdClass();
    $msg->status = 0;
    $msg->error_message =
        get_string('exportgoogleerror', 'portfolioactsave_google') .
           ' ' . $subplug->error_message;
    echo json_encode($msg, JSON_FORCE_OBJECT );
    exit;
}
