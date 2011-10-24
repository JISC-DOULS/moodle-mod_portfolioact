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

$newtemplateid = optional_param('newtemplateid', null, PARAM_INT);

$portfolioacttemplate = new portfolioact_mode_template();
$templates = portfolioactmode_template_get_templates($portfolioacttemplate->course->id, 'name');

$context = get_context_instance(CONTEXT_MODULE, $portfolioacttemplate->cm->id);
require_capability('portfolioactmode/template:edittemplates', $context );

$url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
    array('id'=>$portfolioacttemplate->portfolioact->id));
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('editactivitysettings', 'portfolioactmode_template'));
$PAGE->set_title($portfolioacttemplate->portfolioact->name);


//if there are no templates don't present the edit settings form
//as the user will not be able to select one.
if (empty($templates)) {
    echo $portfolioacttemplate->renderer->header();
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_notemplatesmessage
        ($portfolioacttemplate->cm->id);
    echo $portfolioacttemplate->renderer->footer();
    exit();

}

//create the main form
//in moodle the url of the form needs to be the same as the url of the page it is on
//or you get some unexpected results
if (! is_null($newtemplateid)) {

    $url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
        array('id'=>$portfolioacttemplate->cm->id, 'newtemplateid' => $newtemplateid ));
} else {

    $url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
        array('id'=>$portfolioacttemplate->cm->id));
}

$mform = new  portfolioact_template_edit_form($url);
$class = $mform->formhandle->_attributes['class'];
$mform->formhandle->_attributes['class'] = $class . ' portfolioact-template-settingsform';
$mform->formhandle->addElement('header', 'settings', get_string('settingsheading',
            'portfolioactmode_template', $portfolioacttemplate->portfolioact->name));

//cmid (id) field is passed around because the JS which reloads the page after a template
//change checks this field to build the query string
$cmidfield = $mform->formhandle->addElement('hidden', 'cmid');
$cmidfield->setAttributes(array('id'=>'cmid', 'name'=>'cmid', 'type'=>'hidden',
    'value'=>$portfolioacttemplate->cm->id));

//scaffolds
$scaffolddata = portfolioactmode_template_get_available_scaffolds
    ($portfolioacttemplate->course->id, 'name');
$scaffolds = array('--'=>get_string('select', 'portfolioactmode_template'));

foreach ($scaffolddata as $scaffoldid => $scaffoldname) {
    $scaffolds[$scaffoldid] = $scaffoldname;
}
$mform->formhandle->addElement('select', 'scaffold', get_string('choosescaffold',
           'portfolioactmode_template'), $scaffolds);

if (empty($scaffolddata)) {
    $mform->formhandle->addHelpButton('scaffold', 'editscaffoldnoscaffolds',
                'portfolioactmode_template');
} else {
    $mform->formhandle->addHelpButton('scaffold', 'editscaffold', 'portfolioactmode_template');
}

if (! empty($portfolioacttemplate->settings)  &&
         (! empty($portfolioacttemplate->settings->scaffold) ) ) {
         $mform->formhandle->getElement('scaffold')->setSelected
             ($portfolioacttemplate->settings->scaffold);
}

$templateslist = array('--'=>get_string('select', 'portfolioactmode_template'));
foreach ($templates as $tmplid => $tmplname) {
    $templateslist[$tmplid] = $tmplname;
}

$mform->formhandle->addElement('select', 'template',
           get_string('choosetemplate', 'portfolioactmode_template'), $templateslist);
$mform->formhandle->addRule('template', null, 'required', null, 'server');
//since all values are template id except the -- for 'Select..'
//this enforces the rule that the user must chose one
$mform->formhandle->addRule('template', get_string('youmustselectatemplate',
    'portfolioactmode_template'), 'numeric', null, 'server');
$mform->formhandle->addHelpButton('template', 'edittemplate', 'portfolioactmode_template');

//this is special case called from JS after user has changed the template selected
// - we redisplay without submitting the form.
////cover case it's been deleted (an activity can reference a template which has been deleted)
if (! is_null($newtemplateid)) {
    if (array_key_exists($newtemplateid, $templates) ) {
        $mform->formhandle->getElement('template')->setSelected($newtemplateid);
    } else {
        $mform->formhandle->getElement('template')->setSelected('--');
    }
} else {
    if (! empty($portfolioacttemplate->settings)) {
        if (array_key_exists($portfolioacttemplate->settings->template, $templates) ) {
            $mform->formhandle->getElement('template')->setSelected
                ($portfolioacttemplate->settings->template);
        } else {
            $mform->formhandle->getElement('template')->setSelected('--');
        }
    }
}

$datamodes = array('0'=>'Activity linked', '1'=>'Course linked');

$mform->formhandle->addElement('select', 'datamode', get_string('choosedatamode',
           'portfolioactmode_template'), $datamodes);
$mform->formhandle->addRule('datamode', null, 'required', null, 'client');
$mform->formhandle->addHelpButton('datamode', 'editdatamode', 'portfolioactmode_template');

if (! empty($portfolioacttemplate->settings)) {
    $mform->formhandle->getElement('datamode')->setSelected
        ($portfolioacttemplate->settings->datamode);
} else {
    $mform->formhandle->getElement('datamode')->setSelected(0);
}

$pagesdataraw = array();

if ( (! is_null($newtemplateid))  ) {
    // user changed template in JS. reloading without submiting.
    if (array_key_exists($newtemplateid, $templates)) {
        $pagesdataraw = portfolioactmode_template_get_pages($newtemplateid);
    }

} else {

    if ( (! empty($portfolioacttemplate->settings)) &&
       (! empty($portfolioacttemplate->settings->template) ) && (array_key_exists
           ($portfolioacttemplate->settings->template, $templates))) {
         $pagesdataraw = portfolioactmode_template_get_pages
             ($portfolioacttemplate->settings->template);
    }
}


$pages = array();

foreach ($pagesdataraw as $page) {
    $pages[$page['id']] =  $page['name'];
}

if (is_null($newtemplateid)) {

    $selectedpages = $portfolioacttemplate->getpages();

    if (! is_null($selectedpages)) {
        $pageselector = 1;
    } else {
        $pageselector = 0;
    }

} else {
    $selectedpages = null;
    $pageselector = 0;
}

$pagechoice = array('0'=>get_string('alltext', 'portfolioactmode_template'),
            '1'=>get_string('customtext', 'portfolioactmode_template'));


$pagespecificelement = $mform->formhandle->addElement('select', 'pagespecific',
    get_string('pagespecific',
            'portfolioactmode_template'), $pagechoice );
$mform->formhandle->addRule('pagespecific', null, 'required', null, 'client');
$mform->formhandle->addHelpButton('pagespecific', 'editpagespecific',
    'portfolioactmode_template');
$mform->formhandle->getElement('pagespecific')->setSelected($pageselector);


$showpagesstyle = 'display:none';
if (! empty($selectedpages)) {
    $showpagesstyle = 'display:block';
}

$pageselement = $mform->formhandle->addElement('select', 'pages', get_string('choosepages',
            'portfolioactmode_template'), $pages, array('style'=>$showpagesstyle, 'id'=>'pages' ));
$pageselement->setMultiple(true);
$mform->formhandle->addHelpButton('pages', 'editpages', 'portfolioactmode_template');


if (empty($selectedpages)) {//a new template or just one with no page filter on it array_keys($pages)
    $mform->formhandle->getElement('pages')->setSelected(null);
} else {
    $mform->formhandle->getElement('pages')->setSelected($selectedpages);
}

$savemodes = array('0'=>get_string('hidesavebutton', 'portfolioactmode_template'),
           '1'=>get_string('showsavebutton', 'portfolioactmode_template'));

$mform->formhandle->addElement('select', 'showsave', get_string('savemode',
            'portfolioactmode_template'), $savemodes);

$mform->formhandle->addRule('showsave', null, 'required', null, 'client');

$mform->formhandle->addHelpButton('showsave', 'editsavemode', 'portfolioactmode_template');

if (! empty($portfolioacttemplate->settings) &&
            (! is_null($portfolioacttemplate->settings->showsave))) {
            $mform->formhandle->getElement('showsave')->setSelected
                ($portfolioacttemplate->settings->showsave);
} else {    //enforce the default, show save button
            $mform->formhandle->getElement('showsave')->setSelected(1);
}

$mform->add_action_buttons();

//end build main form.

//create, test for submission and if so process a second form
//which gives the user a confirmation page if they are changing the template or data mode
//it preserves state by packaging up the contents of the main form into one field with
//a JSON string


if (! is_null($portfolioacttemplate->settings) ) {
    $url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
       array('id'=>$portfolioacttemplate->cm->id, 'actid' =>
           $portfolioacttemplate->portfolioact->id,
      'templatesettingsid' => $portfolioacttemplate->settings->id ));
} else {
    $url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
        array('id'=>$portfolioacttemplate->cm->id,
            'actid' => $portfolioacttemplate->portfolioact->id,
           'templatesettingsid' => '' ));
}

$mform2 = new  portfolioact_template_edit_confirm_form($url);

if ((!is_null($mform2->get_data())) || ($mform2->is_cancelled())) {

    if ($mform2->is_cancelled()) {
        //TODO
        //return to the main form. ideally we'd save its state
        //by passing the json package preservestate to its
        //display and  set its starting values
        echo $portfolioacttemplate->renderer->header();
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich2
            (get_string('listheading', 'portfolioactmode_template'));
        $mform->display();
        echo $portfolioacttemplate->renderer->footer();
    } else {
        $data = $mform2->get_data();
        $preservestate = $data->saveddata;

        $templatesettings = json_decode($preservestate);
        $templatesettings->id = $templatesettings->templatesettingsid;
        if (! is_numeric($templatesettings->scaffold)) {
                $templatesettings->scaffold = null;
        }
        unset($templatesettings->templatesettingsid);

        if ($templatesettings->pagespecific == 0) {
                $templatesettings->page = null;
        } else {
            if (isset($templatesettings->pages) && (! empty($templatesettings->pages))) {
                    $templatesettings->page = implode(",", $templatesettings->pages);
            } else {
                    $templatesettings->page = null;
            }
        }
        $newselectedpages = $templatesettings->pages;
        if (isset($templatesettings->pages)) {
            unset($templatesettings->pages);
        }
        if (isset($templatesettings->cmid)) {
            unset($templatesettings->cmid);
        }

        $id = $DB->update_record('portfolioact_tmpl_settings', $templatesettings);

        echo $portfolioacttemplate->renderer->header();
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich2
            (get_string('listheading', 'portfolioactmode_template'));
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
            (get_string('templatesettingsupdated', 'portfolioactmode_template'), '');
        $templatesettings->templatesettingsid = $templatesettings->id;
        unset($templatesettings->id);

        //we've only passed the page ids around and we need to re-create the pages
        //select box for the new template
        //so we need to get them again
        //the options are changing. set_data will not reset the new options.
        //it  only changes the selected or default value
        //so we do that too
        //1. get the new pages
        $newpagesdataraw = portfolioactmode_template_get_pages($templatesettings->template);
        $newpages = array();

        foreach ($newpagesdataraw as $page) {
            $newpages[$page['id']] =  $page['name'];
        }
        //2. clear out the old ones and add the new ones, setting the selected in one go
        $pageselement->removeOptions();
        $pageselement->loadArray($newpages, $newselectedpages );
        if ($templatesettings->pagespecific == 1) {
            $pageselement->updateAttributes(array('style'=>'display:block'));
        }
        //set the data saved in the second form back onto the first form
        $mform->set_data($templatesettings);

        $mform->display();
        $PAGE->requires->js_init_call('M.portfolioactmode_template.init', null, true,
            portfolioactmode_template_get_js_module());
        echo $portfolioacttemplate->renderer->footer();

    }
    exit;
}

//end second form.

//handle main form - only if the second form was not submitted
$formmessage="";

if ($mform->is_cancelled()) {

    $url = new moodle_url('/mod/portfolioact/mode/template/edittemplate.php',
       array('id'=>$portfolioacttemplate->cm->id));
    redirect($url);
} else if ($fromform=$mform->get_data()) {

    $newdata = new stdClass();

    //first part of this if clause is case we have a template in
    //settings and it has not been deleted-in which case we are starting again --
    //and it or its datamode is being changed
    if ((! empty($portfolioacttemplate->settings))  &&
         ( array_key_exists($portfolioacttemplate->settings->template, $templates)) &&
             ( (($portfolioacttemplate->settings->template != $fromform->template) ||
         ($portfolioacttemplate->settings->datamode != $fromform->datamode ) ))     ) {

        //process the form data into a json package
        $copydata = new stdClass();
        //xx $copydata->actid = $fromform->actid;
        $copydata->actid = $portfolioacttemplate->portfolioact->id;
        $copydata->cmid =  $fromform->cmid;
        $copydata->templatesettingsid =  $portfolioacttemplate->settings->id;
        $copydata->scaffold =  $fromform->scaffold;
        $copydata->template =  $fromform->template;
        $copydata->datamode =  $fromform->datamode;
        $copydata->pagespecific =  $fromform->pagespecific;

        if (isset($fromform->pages)) {
            $copydata->pages =  $fromform->pages;
        } else {
            $copydata->pages=null;
        }
        $copydata->showsave =  $fromform->showsave;

        $saveddata = json_encode($copydata);
        $message = get_string('confirmformsave', 'portfolioactmode_template');

        if ($portfolioacttemplate->settings->template != $fromform->template) {
            $message.=  get_string('confirmformsavetemplate', 'portfolioactmode_template');
        }

        if ($portfolioacttemplate->settings->datamode != $fromform->datamode) {
            $message.=  get_string('confirmformsaveactivitymode', 'portfolioactmode_template');
        }
        echo $portfolioacttemplate->renderer->header();
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_list();
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
            ($message, '');
        $mform2->display($saveddata);
        echo $portfolioacttemplate->renderer->footer();
        exit;

    } else { //this is first time the settings have been set or it is a simple update

        $current_settings = $DB->get_record('portfolioact_tmpl_settings',
            array('actid'=>$portfolioacttemplate->portfolioact->id) , 'id');

        if (empty($current_settings)) {

            $templatesettings = new stdClass;
            $templatesettings->id = null;
            //xx $templatesettings->actid = $fromform->actid;
            $templatesettings->actid = $portfolioacttemplate->portfolioact->id;

            if (isset($fromform->scaffold) &&  (is_numeric($fromform->scaffold ))) {
                $templatesettings->scaffold = $fromform->scaffold;
            } else {
                $templatesettings->scaffold = null;
            }

             $templatesettings->template = $fromform->template;
             $templatesettings->datamode = $fromform->datamode;

            //pages to save
            if ($fromform->pagespecific == 0) {
                 $templatesettings->page = null;
            } else {
                if (! empty($fromform->pages)) {
                     $templatesettings->page = implode(',', $fromform->pages);
                } else {
                     $templatesettings->page = null;
                }
            }

             $templatesettings->showsave = $fromform->showsave;

             $id = $DB->insert_record('portfolioact_tmpl_settings', $templatesettings);
             $newdata->templatesettingsid = $id;
             $formmessage = get_string('templatesettingscreated', 'portfolioactmode_template');



        } else {
             //this is an update to an existing template
            // (the template or its datamode did not change)
            // OR the template was deleted and is being reset

             $templatesettings = new stdClass;
            //xx $templatesettings->actid = $fromform->actid;
            $templatesettings->actid = $portfolioacttemplate->portfolioact->id;

            if (isset($fromform->scaffold) &&  (is_numeric($fromform->scaffold)) ) {
                $templatesettings->scaffold = $fromform->scaffold;
            } else {
                $templatesettings->scaffold = null;
            }


             $templatesettings->template = $fromform->template;
             $templatesettings->datamode = $fromform->datamode;

             //pages to save
            if ($fromform->pagespecific == 0) {
                 $templatesettings->page = null;
            } else {

                if (! empty($fromform->pages)) {
                     $templatesettings->page = implode(',', $fromform->pages);
                } else {
                    $templatesettings->page = null;
                }

            }

            $templatesettings->showsave = $fromform->showsave;
            $templatesettings->id = $current_settings->id;

            $id = $DB->update_record('portfolioact_tmpl_settings', $templatesettings);
            $formmessage = get_string('templatesettingsupdated',
                'portfolioactmode_template');
        }

        //need to set the controls to the new data.
        //set_data appears to get the data out of the submit & does not need to
        //be explicitly set - indeed in this context that doesn't work anyway.
        //see onQuickFormEvent in select.php which it calls.
        //anything you pass to it is set onto defaults but the update method looks at
        //the submit data anyway
        //this would only not be the case if setConstants had been called on the form.
        //so in a form submit block set_data has the effect of setting the submitted
        //data onto the form & ignores the default values passed
        //but outside a submit form block it sets the values depending on
        // what is passed

        //set_dara WILL TAKE THE SUBMIT VALUE IF THE ELEMENT EXISTS
        //NOT WHAT YOU PASS TO set_data . set_data CANNOT BE USED TO OVER
        //RIDE SUBMITTED VALUES WHEREVEVER IT IS CALLED.
        //see HTML_QuickForm_element->onQuickFormEvent for updateValue


        if ($fromform->pagespecific == 1) {
            $pageselement->updateAttributes(array('style'=>'display:block'));
        } else {
            $pageselement->updateAttributes(array('style'=>'display:none'));
        }

        $mform->set_data(array());//it gets the values out the submit anyway

    } //end processing data block

}


echo $portfolioacttemplate->renderer->header();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_list();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
    ($formmessage, '');


$mform->display();
$PAGE->requires->js_init_call('M.portfolioactmode_template.init', null, true,
    portfolioactmode_template_get_js_module());

echo $portfolioacttemplate->renderer->footer();
