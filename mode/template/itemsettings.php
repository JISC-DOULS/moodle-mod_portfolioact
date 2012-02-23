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
 * Manages editing item settings and adding new items
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
$itemid = optional_param('itemid', null, PARAM_INT);
$new = optional_param('new', null, PARAM_INT);

$portfolioacttemplate = new portfolioact_mode_template();
 $page = new portfolioact_template_page($pageid);


$context = get_context_instance(CONTEXT_MODULE, $portfolioacttemplate->cm->id);
require_capability('portfolioactmode/template:edittemplates', $context );

$url = new moodle_url('/mod/portfolioact/mode/template/itemsettings.php',
    array('template'=>$templateid, 'pageid'=>$pageid, 'id'=>$portfolioacttemplate->cm->id,
        'itemid'=>$itemid));
$PAGE->set_url($url);
$navurl = new moodle_url('/mod/portfolioact/mode/template/manager.php',
     array('id'=>$portfolioacttemplate->cm->id, 'action'=>'list'));
$PAGE->navbar->add(get_string('templatemanager', 'portfolioactmode_template'), $navurl);
$navurl2 = new moodle_url('/mod/portfolioact/mode/template/designer.php',
    array('id'=>$portfolioacttemplate->cm->id,
    'templateid'=>$templateid));
$PAGE->navbar->add(get_string('templatedesigner', 'portfolioactmode_template'), $navurl2);
$navurl = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
    array('templateid'=>$templateid, 'pageid'=>$pageid, 'id'=>$portfolioacttemplate->cm->id));
$PAGE->navbar->add(get_string('pagedesigner', 'portfolioactmode_template'), $navurl);
$PAGE->navbar->add(get_string('itemsettings', 'portfolioactmode_template'));
$PAGE->set_title($portfolioacttemplate->portfolioact->name);


//sort out the parameters and get existing settings for edited items
//we may be doing a new item or an update. the params and setup is different in each case
$settingsdata = array();

if ($new == 1) {
    $name = '';
    $type = required_param('contenttype', PARAM_ALPHAEXT);
    $itemafter = required_param('itemafter', PARAM_TEXT);
    $contenttype = required_param('contenttype', PARAM_ALPHAEXT);
} else {
    $item = portfolioact_template_item::getitem($itemid);

    if (is_null($item)) {
        $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
        array('templateid'=>$templateid, 'pageid'=>$pageid,
            'id'=>$portfolioacttemplate->cm->id));
        throw new moodle_exception('itemnotexist', 'portfolioactmode_template', $url );
    }

    $type = $item->type;
    $name = $item->name;
    $settingsdata = $item->settingskeys;
}
//the settings
$class = 'portfolioact_template_item_'.$type;
$settings = $class::getsettingtypes($pageid);


if ( ($type == 'reference' ) && (empty($settings['sourceitem']['values']) ) ) {
    echo $portfolioacttemplate->renderer->header();
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich2
        (get_string('listheading', 'portfolioactmode_template'));
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich3($page->name);
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
       (get_string('noitemsyet', 'portfolioactmode_template'));
    echo  $portfolioacttemplate->renderer->render_portfolioactmode_template_noitems
        ($portfolioacttemplate->cm->id, $templateid, $pageid);
    echo $portfolioacttemplate->renderer->footer();
    exit;
}

//url for the form
$url = new moodle_url('/mod/portfolioact/mode/template/itemsettings.php',
          array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid,
          'pageid'=>$pageid, 'itemid'=>$itemid));

//create the form

$settingsform = new portfolioact_template_item_settings($url);
$class = $settingsform->formhandle->_attributes['class'];
$settingsform->formhandle->_attributes['class'] = $class .
    ' portfolioact-template-items-settings-form';
$settingsform->formhandle->addElement('header', 'settings', get_string('itemsettings_'.$type,
            'portfolioactmode_template'));

$nameattributes = array('value'=>$name);
$namefield = $settingsform->formhandle->addElement('text', 'itemname', get_string('itemname',
            'portfolioactmode_template'), $nameattributes);

$settingsform->formhandle->addRule('itemname', get_string('maximumchars', '', 250), 'maxlength',
    250, 'client');
$settingsform->formhandle->addRule('itemname', get_string('required'), 'required', null, 'client');
$settingsform->formhandle->addHelpButton('itemname', 'itemnametype', 'portfolioactmode_template');


//for new ones we transfer these fields from the incomming form data via params (the form
//on the previous page) to hidden fields in this form, so they are available when
//we submit.
if (! is_null($new)) {
    $settingsform->formhandle->addElement('hidden', 'itemafter', $itemafter);
    $settingsform->formhandle->addElement('hidden', 'contenttype', $contenttype);
    $settingsform->formhandle->addElement('hidden', 'new', $new);
}



//this bit sets up the form - covers both cases of new and edited items
//you need to extend this if you add new item types which require new controls


foreach ($settings as $setting => $controldetails) {

    if (isset($settingsdata[$setting])) {
        $controldetails['defaultvalue'] =  $settingsdata[$setting];
    }

    switch ($controldetails['control']) {
        case "editor":
            $editor = $settingsform->formhandle->addElement('editor', $setting,
            $controldetails['label'], null, null);
            $editor->setValue(array('text'=>$controldetails['defaultvalue']));
            $settingsform->formhandle->addHelpButton($setting, $controldetails['helptextstring'],
                'portfolioactmode_template');
            $settingsform->formhandle->addRule($setting,
                get_string('required'), 'required', null, 'client');


        break;

        case "html":
            $settingsform->formhandle->addElement('html', $controldetails['label']);
        break;

        case "text":
            $atts = array('value'=>$controldetails['defaultvalue']);
            $settingsform->formhandle->addElement('text', $setting,
            $controldetails['label'], $atts);
            $settingsform->formhandle->addHelpButton($setting, $controldetails['helptextstring'],
                'portfolioactmode_template');

            if ( isset($controldetails['required']) && ($controldetails['required'] === true)) {
                $settingsform->formhandle->addRule($setting,
                    get_string('required'), 'required', null, 'client');

            }

        break;
        case "checkbox":
             $checkbox = $settingsform->formhandle->addElement('advcheckbox', $setting,
                 get_string('itemquestionformatlabel', 'portfolioactmode_template'), null, null,
                      array(0, 1));  //1 is sent if checked, 0 is sent if not checked.
                 $checkbox->setChecked($controldetails['defaultvalue']);
                 $settingsform->formhandle->addHelpButton($setting,
                     $controldetails['helptextstring'], 'portfolioactmode_template');

        break;
        case "select":
            $atts = $controldetails['values'];
            $select = $settingsform->formhandle->addElement('select', $setting,
            $controldetails['label'], $atts);
            if (array_key_exists($controldetails['defaultvalue'], $atts)) {
                $select->setSelected($controldetails['defaultvalue']);
            }
            if (isset($controldetails['validatationtype'])) {

                $settingsform->formhandle->addRule($setting,
                    $controldetails['validatationtype']['label'],
                        $controldetails['validatationtype']['type'],
                            $controldetails['validatationtype']['format'], 'client');
            }
            $settingsform->formhandle->addHelpButton($setting, $controldetails['helptextstring'],
                'portfolioactmode_template');

        break;

        case "multiselect":
            $atts = $controldetails['values'];
            $select = $settingsform->formhandle->addElement('select', $setting,
            $controldetails['label'], $atts);
            $select->setMultiple(true);
            //in case of an update and this is comming from the database not the class
            //we need to upgrade it to an array
            if (! is_array($controldetails['defaultvalue'])) {
                $controldetails['defaultvalue'] = explode(",", $controldetails['defaultvalue']);
            }
            $select->setSelected($controldetails['defaultvalue']);
            $settingsform->formhandle->addHelpButton($setting, $controldetails['helptextstring'],
                'portfolioactmode_template');

        break;
    }
}


$settingsform->add_action_buttons();



if ($settingsform->is_cancelled() ) {

    $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
        array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid, 'pageid'=>$pageid));
    redirect($url);

}

//process the data. this may be a new item or an edited one
if ($fromform=$settingsform->get_data()) {

        $newname = $fromform->itemname;
        $settings = array();

    foreach ($fromform as $fieldname => $fieldvalue) {
            //lose the fields not custom for this control type
        if ( ($fieldname == 'itemname') || ($fieldname == 'submitbutton') ||
                ($fieldname == 'itemafter') || ($fieldname == 'contenttype') ||
                    ($fieldname == 'new') ) {
                 continue;
        }
            //straightforward string case
        if (is_scalar($fieldvalue)) {
            $settings[$fieldname] = $fieldvalue;
            //handle case of tinymce editor which reurns array with text and format (1=html)
        } else if (is_array($fieldvalue) && (array_key_exists('text', $fieldvalue) &&
            array_key_exists('format', $fieldvalue) )) {
            $settings[$fieldname] = $fieldvalue['text'];
        } else if (is_array($fieldvalue)) {//support multiple selects
            $settings[$fieldname] = implode(",", $fieldvalue);
        }

    }

    if ($new == 1) {

        if ($itemafter !== 'start' && $itemafter !== 'end') {
            $itemafter = (int)$itemafter;    //make it safe we allowed non int params
        }

        $class = 'portfolioact_template_item_'.$type;
        $res = $class::create_element($pageid, $type, $newname, $settings,
                $itemafter, false );

        if ($res === false) {
            $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
            array('templateid'=>$templateid, 'pageid'=>$pageid,
                'id'=>$portfolioacttemplate->cm->id));
            throw new moodle_exception('newitemnotadded', 'portfolioactmode_template', $url );
        }

        $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
            array('templateid'=>$templateid, 'pageid'=>$pageid, 'id'=>$portfolioacttemplate->cm->id,
                'action'=>'updated'));
        redirect($url);

    } else {//an update

        $class = "portfolioact_template_item_".$type;
        $item = new $class($itemid);
        $res = $item->update_element($newname, $settings );

        if ($res === false) {
            $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
            array('templateid'=>$templateid, 'pageid'=>$pageid,
                'id'=>$portfolioacttemplate->cm->id));
            throw new moodle_exception('newitemnotadded', 'portfolioactmode_template', $url );
        }
        $url = new moodle_url('/mod/portfolioact/mode/template/pageeditor.php',
            array('templateid'=>$templateid, 'pageid'=>$pageid, 'id'=>$portfolioacttemplate->cm->id,
            'action'=>'updated'));
        redirect($url);

    }

} else {//display the form

    echo $portfolioacttemplate->renderer->header();
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich2
        (get_string('listheading', 'portfolioactmode_template'));
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich3($page->name);
    $settingsform->display();
} //end form display block


echo $portfolioacttemplate->renderer->footer();
