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
 * Manages saving and editing scaffolds
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );

require_once(dirname(dirname(dirname(__FILE__))).'/locallib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/renderer.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/local_forms.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/db/access.php');

global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;

$action = optional_param('action', 'list', PARAM_ALPHA);


$scaffold_plugin = new portfolioact_mode_scaffold();
$actid = $scaffold_plugin->portfolioact->id;


$context = context_module::instance($scaffold_plugin->cm->id);
require_capability('portfolioactmode/scaffold:editscaffolds', $context );


$url = new moodle_url('/mod/portfolioact/mode/scaffold/editscaffold.php',
    array('id'=>$scaffold_plugin->cm->id));
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('editactivitysettings', 'portfolioactmode_scaffold'));
$PAGE->set_title($scaffold_plugin->portfolioact->name);


//get the scaffolds available for this course
$scaffolds_data = portfolioactmode_scaffold_get_scaffolds($scaffold_plugin->course->id, 'name');
$scaffolds = array('--' => get_string('select', 'portfolioactmode_scaffold'));

//if there are no scaffolds don't present the edit settings form
//as the user will not be able to select one.
if (empty($scaffolds_data)) {
    echo $scaffold_plugin->renderer->header();
    echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_editlist();
    echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_noscaffoldsmessage
        ($scaffold_plugin->cm->id);
    echo $scaffold_plugin->renderer->footer();
    exit();

}

foreach ($scaffolds_data as $scaffoldid => $scaffoldname) {
    $scaffolds[$scaffoldid] = $scaffoldname;
}



if (!empty($scaffold_plugin->settings) ) {
    $scaffoldsettingsid = $scaffold_plugin->settings->id;
    $scaffoldid = $scaffold_plugin->settings->scaffold;
} else {
    $scaffoldsettingsid = '';
    $scaffoldid='';
}


$url = new moodle_url('/mod/portfolioact/mode/scaffold/editscaffold.php',
    array('id'=>$scaffold_plugin->cm->id));


$mform = new  portfolioact_scaffold_edit_form($url);
//set up the form
$class = $mform->formhandle->_attributes['class'];
$mform->formhandle->_attributes['class'] = $class . ' portfolioact-scaffold-settingsform';

$mform->formhandle->addElement('header', 'settings', get_string('settingsheading',
            'portfolioactmode_scaffold', $scaffold_plugin->portfolioact->name));

$scaffelement = $mform->formhandle->addElement('select', 'scaffold', get_string('choosescaffold',
           'portfolioactmode_scaffold'), $scaffolds);

$mform->formhandle->addRule('scaffold', get_string('youmustchooseascaffold',
    'portfolioactmode_scaffold'), 'numeric', null, 'server');

if (empty($scaffolddata)) {
    $mform->formhandle->addHelpButton('scaffold', 'editscaffoldnoscaffolds',
                'portfolioactmode_scaffold');
} else {
    $mform->formhandle->addHelpButton('scaffold', 'editscaffold', 'portfolioactmode_scaffold');
}


if (! empty($scaffold_plugin->settings)  &&
         (! empty($scaffold_plugin->settings->scaffold) ) ) {
         $mform->formhandle->getElement('scaffold')->setSelected
             ($scaffold_plugin->settings->scaffold);
} else {
           $mform->formhandle->getElement('scaffold')->setSelected('--');

}

$mform->add_action_buttons();

$formsaved = false;

if ($mform->is_cancelled()) {

    $url = new moodle_url('/mod/portfolioact/mode/scaffold/editscaffold.php',
       array('id'=>$scaffold_plugin->cm->id, 'action'=>'list'));
    redirect($url);
} else if ($fromform=$mform->get_data()) {

    $newscaffoldid = $fromform->scaffold;

    if ($newscaffoldid != '--') {
        $scaffold_plugin->set_settings($newscaffoldid);

        $newdata = new stdClass();
        $newdata->scaffold = $newscaffoldid;
        $mform->set_data($newdata);
        $formsaved = true;
    }

}

    echo $scaffold_plugin->renderer->header();
    echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_editlist();

if ($formsaved) {
    echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_formmessage
        (get_string('updated', 'portfolioactmode_scaffold'));
}


$mform->display();




echo $scaffold_plugin->renderer->footer();
