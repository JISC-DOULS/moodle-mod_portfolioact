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
 * Shows the pages available for a template and provides various editing functions.
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/locallib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/renderer.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/local_forms.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/db/access.php');


global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;

$action = optional_param('action', 'list', PARAM_ALPHA);
$templateid = required_param('templateid', PARAM_INT);

$portfolioacttemplate = new portfolioact_mode_template();
$template = new portfolioact_template($templateid);

$context = context_module::instance($portfolioacttemplate->cm->id);
require_capability('portfolioactmode/template:edittemplates', $context );


$url = new moodle_url('/mod/portfolioact/mode/template/designer.php',
    array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid));
$PAGE->set_url($url);
$navurl = new moodle_url('/mod/portfolioact/mode/template/manager.php',
    array('id'=>$portfolioacttemplate->cm->id, 'action'=>'list'));
$PAGE->navbar->add(get_string('templatemanager', 'portfolioactmode_template'), $navurl);
$PAGE->navbar->add(get_string('templatedesigner', 'portfolioactmode_template'));
$PAGE->set_title($portfolioacttemplate->portfolioact->name);

$nameform = new portfolioact_template_update_name(qualified_me());
$class = $nameform->formhandle->_attributes['class'];
// $class .
$nameform->formhandle->_attributes['class'] = ' portfolioact-template-nameform';
$namefield = $nameform->formhandle->addElement('text', 'templatename', get_string('templatename',
            'portfolioactmode_template'));
$nameform->formhandle->addRule('templatename', null, 'maxlength', 250, 'client');
$nameform->formhandle->addRule('templatename', null, 'required', null, 'client');
$namefield->setValue($template->name);
$nameform->add_action_buttons(false, get_string('updatetemplatename', 'portfolioactmode_template'));


if ($fromform=$nameform->get_data()) {
    $template->updatename($fromform->templatename);
    $nameform->set_data(array());
}


echo $portfolioacttemplate->renderer->header();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_list();

echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich3($template->name);



$nameform->display();

$updateform = new portfolioact_template_update_position(qualified_me());
$class = $updateform->formhandle->_attributes['class'];
//set the class so in JS code we can get a handle on the form
$updateform->formhandle->_attributes['class'] = $class . ' portfolioact-template-posform';


$formsumbit = false;
if ($fromform=$updateform->get_data()) {
    $formsumbit = true;
    $ret = portfolioactmode_template_update_page_position($fromform->pageorderlist, $template);
    if ($ret) {
        $text = get_string('positionupdated', 'portfolioactmode_template');
    } else {
        $text = get_string('positionupdatefailed', 'portfolioactmode_template');
    }
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
        ($text, 'portfolioact-template-template-feedbackmessage');
}

$pages = $template->get_pages();

if (!empty($pages)) {

    if (($action == 'updated') && (! $formsumbit )) {
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
            (get_string('updated', 'portfolioactmode_template'),
                'portfolioact-template-template-feedbackmessage');
    }


    echo $portfolioacttemplate->renderer->render_nojsmessage_pages();
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_pages_table
        ($pages, $portfolioacttemplate->cm->id, $templateid);
    $updateform->display();

} else {
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_startpages();
}

echo $portfolioacttemplate->renderer->render_portfolioactmode_template_createnewpagebutton
    ($portfolioacttemplate->cm->id, $templateid);

$PAGE->requires->js_init_call('M.portfolioactmode_template.init_page',
    array($templateid, $portfolioacttemplate->cm->id), true,
    portfolioactmode_template_get_js_module());

echo $portfolioacttemplate->renderer->footer();
