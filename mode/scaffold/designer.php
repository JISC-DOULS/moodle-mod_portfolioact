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
 * Manages deleting scaffolds
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/local_forms.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/renderer.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/db/access.php');

global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;

define('MAXBYTES', 100 * 1024 * 1024);

$scaffoldid = required_param('scaffoldid', PARAM_INT);
$action = optional_param('action', 'list', PARAM_ALPHA);

$scaffold_plugin = new portfolioact_mode_scaffold();
$scaffold = new portfolioact_scaffold($scaffoldid);

//CONTEXT_COURSE because we could be in any module
//and the scaffold data is specific to the scaffold whatever module
//the user or admin is on.
//if we use CONTEXT_MODULE then if the scaffold is used in another
//Activity we don't find the data
$context = get_context_instance(CONTEXT_COURSE, $scaffold->course);

require_capability('portfolioactmode/scaffold:editscaffolds', $context );

$url = new moodle_url('/mod/portfolioact/mode/scaffold/designer.php',
    array('id'=>$scaffold_plugin->cm->id, 'scaffoldid'=>$scaffoldid));
$PAGE->set_url($url);
$navurl = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
    array('id'=>$scaffold_plugin->cm->id, 'action'=>'list'));
$PAGE->navbar->add(get_string('scaffoldmanager', 'portfolioactmode_scaffold'), $navurl);
$PAGE->navbar->add(get_string('scaffolddesigner', 'portfolioactmode_scaffold'));
$PAGE->set_title($scaffold_plugin->portfolioact->name);

$nameform = new portfolioact_scaffold_update_name(qualified_me());
$class = $nameform->formhandle->_attributes['class'];
// $class .
$nameform->formhandle->_attributes['class'] = ' portfolioact-scaffold-nameform';
$namefield = $nameform->formhandle->addElement('text', 'scaffoldname', get_string('scaffoldname',
            'portfolioactmode_scaffold'));
$nameform->formhandle->addRule('scaffoldname', null, 'maxlength', 250, 'client');
$nameform->formhandle->addRule('scaffoldname', null, 'required', null, 'client');
$namefield->setValue($scaffold->name);
$nameform->add_action_buttons(false, get_string('updatescaffoldname', 'portfolioactmode_scaffold'));


if ($fromform=$nameform->get_data()) {
    $scaffold->updatename($fromform->scaffoldname);
    $nameform->set_data(array());
}


$form = new portfolioact_scaffold_creation_form(qualified_me());




$form->formhandle->addElement('header', 'settings', get_string('scaffoldeditor',
            'portfolioactmode_scaffold'));

$form->formhandle->addHelpButton('settings', 'emptydirectoriesmessage',
                'portfolioactmode_scaffold');

$form->formhandle->addElement('filemanager', 'scaffoldset',
    get_string('uploadafile'), null,  array('subdirs' => true,
        'maxbytes' => 2097152, 'accepted_types' => array('*') ));//maxfiles should be unlimited






//should probably be file_prepare_draft_area
//might work if we called the control scaffoldset_filemanager...
//the first, data, param is the object you are going to pass to set_data
//$obj = new stdClass();
//file_prepare_standard_filemanager($obj, 'files', array('subdirs' => true,
//    'maxbytes' => MAXBYTES), $context, 'mod_portfolioactmode_scaffold',
//   'scaffoldset', $scaffold->id);

//will be 0 on the first get then the id of the field on the subsequent submit
$draftitemid = file_get_submitted_draft_itemid('scaffoldset');

//takes all the actual files for this scaffold and creates drafts (they may have been cleaned up by the system)
file_prepare_draft_area($draftitemid, $context->id, 'portfolioactmode_scaffold', 'scaffoldset',
    $scaffold->id, array('subdirs' => true));

$currentset = new stdClass();
$currentset->id = null;
$currentset->scaffoldset =  $draftitemid;
$form->set_data($currentset);//associate all the drafts with the control


$form->add_action_buttons();
$formsaved = false;

if ($form->is_cancelled()) {
    $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
    array('id'=>$scaffold_plugin->cm->id, 'action'=>'list'));
    redirect($url);
} else if ($fromform=$form->get_data()) {

    //copy alll the drafts to create real rows
    file_save_draft_area_files($draftitemid, $context->id,
     'portfolioactmode_scaffold', 'scaffoldset', $scaffold->id,
      array('subdirs' => true, 'maxbytes' => 2097152));

    $scaffold->update();

    $formsaved = true;

}


echo $scaffold_plugin->renderer->header();
echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_editlist();

if ($formsaved) {
    echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_updated();
}

echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_generich3($scaffold->name);
$nameform->display();

//this block populates the file manager element with
//the files/folders from the repository by itemid
//if (is_null($draftitemid)) {
///    $draftitemid = 0;
//}





//passing it 'files' means it looks for a parameter files_filemanager which
//does not exist so the draft area idea will always be 0, the default,
//which means when this calls file_prepare_draft_area
//that that func. will always generate a new draft id each time
//so - there will be multiple drafts which are per page load
//if save is called - the current draft is passed to file_save_draft_area_files
//so will save just the most recent - save means create rows with component, filearea=scaffoldset
//and itemid as  the id of the scaffold





$form->display();


echo $scaffold_plugin->renderer->footer();
