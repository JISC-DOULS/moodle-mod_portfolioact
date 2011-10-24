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
 * Manages deleting templates
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/renderer.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/local_forms.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/db/access.php');

global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;


$templateid = required_param('templateid', PARAM_INT);
$pageid = required_param('pageid', PARAM_INT);
$action = optional_param('action', 'list', PARAM_ALPHA);

$portfolioacttemplate = new portfolioact_mode_template();
$page = new portfolioact_template_page($pageid);
$template = new portfolioact_template($templateid);
$page =  new portfolioact_template_page($pageid);

$context = get_context_instance(CONTEXT_MODULE, $portfolioacttemplate->cm->id);
require_capability('portfolioactmode/template:edittemplates', $context );

$url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
    array('id'=>$portfolioacttemplate->cm->id, 'pageid'=>$pageid, 'template'=>$templateid));
$PAGE->set_url($url);
$navurl = new moodle_url('/mod/portfolioact/mode/template/manager.php',
    array('id'=>$portfolioacttemplate->cm->id, 'action'=>'list'));
$PAGE->navbar->add(get_string('templatemanager', 'portfolioactmode_template'), $navurl);
$navurl2 = new moodle_url('/mod/portfolioact/mode/template/designer.php',
    array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid));
$PAGE->navbar->add(get_string('templatedesigner', 'portfolioactmode_template'), $navurl2);
$PAGE->navbar->add(get_string('pagedesigner', 'portfolioactmode_template'));
$PAGE->set_title($portfolioacttemplate->portfolioact->name);



$nameform = new portfolioact_template_update_name(qualified_me());
$class = $nameform->formhandle->_attributes['class'];
// $class .
$nameform->formhandle->_attributes['class'] = ' portfolioact-page-nameform';
$namefield = $nameform->formhandle->addElement('text', 'pagename', get_string('pagename',
            'portfolioactmode_template'));
$nameform->formhandle->addRule('pagename', null, 'maxlength', 250, 'client');
$nameform->formhandle->addRule('pagename', null, 'required', null, 'client');
$namefield->setValue($page->name);
$nameform->add_action_buttons(false, get_string('updatepagename', 'portfolioactmode_template'));


if ($fromform=$nameform->get_data()) {
    $page->updatename($fromform->pagename);
    $nameform->set_data(array());
}

echo $portfolioacttemplate->renderer->header();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_list();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich3($page->name);


$nameform->display();


$items = $page->get_items();



$url = new moodle_url('/mod/portfolioact/mode/template/itemsettings.php',
            array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid,
                'pageid'=>$pageid, 'new'=>1));
$controlform = new portfolioact_template_items($url);

$class = $controlform->formhandle->_attributes['class'];
$controlform->formhandle->_attributes['class'] = $class . ' portfolioact-template-items-form';
$controlform->formhandle->addElement('header', 'settings', get_string('createitem',
            'portfolioactmode_template'));

$contenttypes = portfolioact_template_item::$items;

$controlform->formhandle->addElement('select', 'contenttype',
           get_string('contenttypes', 'portfolioactmode_template'), $contenttypes);
$controlform->formhandle->addRule('contenttype', null, 'required', null, 'client');
$controlform->formhandle->addHelpButton('contenttype', 'editcontenttypes',
    'portfolioactmode_template');


$itemnames = array('start'=>'At the beginning');
foreach ($items as $item) {
    $itemnames[$item->id]    = $item->name;
}

$controlform->formhandle->addElement('select', 'itemafter',
       get_string('itemafter', 'portfolioactmode_template'), $itemnames);
$controlform->formhandle->addRule('itemafter', null, 'required', null, 'client');
$controlform->formhandle->addHelpButton('itemafter', 'edititemafter',
    'portfolioactmode_template');

$controlform->formhandle->addElement('submit', 'submitbutton',
    get_string('addcontent', 'portfolioactmode_template'));

//the new item form is posted to itemsettings.php

//the position form - with its hidden field for order
$updateform = new portfolioact_template_update_items_position(qualified_me());
$class = $updateform->formhandle->_attributes['class'];
//set the class so in JS code we can get a handle on the form
$updateform->formhandle->_attributes['class'] = $class .
    ' portfolioact-template-posform';


$updateformsubmit = false;
if ($fromform=$updateform->get_data()) {

    $itemorderlist = $fromform->itemorderlist;

    $order = explode('#', $itemorderlist);
    $positions = array();
    foreach ($order as $itempos) {
        if ((! empty($itempos)) ) {//&& strrpos('|', $pagepos)
            $item_position = explode('|', $itempos);
            $positions[$item_position[0]] = $item_position[1];
        }
    }

    $ret = $page->set_item_order($positions);
    if ($ret) {
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
           (get_string('positionupdated', 'portfolioactmode_template'));
        $items = $page->get_items();
    } else {
         echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
            (get_string('positionupdatefailed', 'portfolioactmode_template'));
    }

    $updateformsubmit = true;
}




if (($action == 'updated') && (! $updateformsubmit ) ) {
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
       (get_string('updated', 'portfolioactmode_template'),
           'portfolioact-template-template-feedbackmessage');
}
echo $portfolioacttemplate->renderer->render_nojsmessage_items();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_items_table
    ($items, $portfolioacttemplate->cm->id, $templateid, $pageid);


$controlform->display();
$updateform->display();



$PAGE->requires->js_init_call('M.portfolioactmode_template.init_items',
    array($pageid, $portfolioacttemplate->cm->id), true,
    portfolioactmode_template_get_js_module());


echo $portfolioacttemplate->renderer->footer();
