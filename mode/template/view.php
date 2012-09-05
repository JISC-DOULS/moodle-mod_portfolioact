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
 * Main activity view
 * *
 * @package   portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/local_forms.php');
require_once($CFG->dirroot.'/mod/portfolioact/save/lib.php');


if (! defined('PORTFOLIOACTVIEWER')) {
    throw new moodle_exception('youdonothavepermission', 'portfolioact');
}

/**
 * Handles display of the Template Activity
 *
 * @param mixed $subplug an instance of the mode e.g. template, scaffold
 */


function portfolioactmode_template_view($subplug) {

    global $PAGE, $CFG, $OUTPUT;

    $page = optional_param('page', 0, PARAM_INT);

    $context = get_context_instance(CONTEXT_MODULE, $subplug->cm->id);
    require_capability('mod/portfolioact:canview', $context );

    $templateid = $subplug->settings->template;

    try {
        $template = new portfolioact_template($templateid);
    } catch (Exception $e) {
        $canedit = has_capability('mod/portfolioact:canedit', $context );
        echo $OUTPUT->header();
        echo $subplug->renderer->render_portfolioactmode_template_viewhead($subplug);
        echo $subplug->pa_renderer->render_portfolioactmode_notyetsetup($subplug->cm->id,
            $canedit, 'template');
        echo $OUTPUT->footer();
        exit;
    }

    $savetypes = $subplug->get_allowed_save_types();
    $pages = $template->get_pages();

    if (! is_null($subplug->settings->page)) {
        $pagesfiltered = explode(",", $subplug->settings->page);

        $showpages = array();

        foreach ($pages as $pagedata) {
            if ( in_array( $pagedata->id, $pagesfiltered)) {
                $showpages[] = $pagedata;
            }

        }
        $pages = $showpages;
    }

    $pagecount = count($pages);

    if ($pagecount < 1) {
        echo $OUTPUT->header();
        echo $subplug->renderer->render_portfolioactmode_template_viewhead($subplug);

        $canedit = has_capability('mod/portfolioact:canedit', $context );
        echo $subplug->renderer->render_portfolioactmode_nopages($subplug->cm->id,
            $templateid, $canedit);

        echo $OUTPUT->footer();
        exit;
    }

    $templatepage = new  portfolioact_template_page($pages[$page]->id);
    $items = $templatepage->get_items();

    $form = new portfolioact_template_templatedata(qualified_me());
    $class = $form->formhandle->_attributes['class'];
    $form->formhandle->_attributes['class'] = $class . ' portfolioact-template-templateform';

    $showsave = false;

    foreach ($items as &$item) {

        $class = "portfolioact_template_item_".$item->type;
        $item->object =   new $class($item->id);

        $form->formhandle->addElement('header', 'item' . $item->id, '');

        if (($subplug->settings->datamode == 0) || ($item->type == 'instruction') ) {//activity mode
            $item->object->display($form->formhandle, $subplug->settings->actid );
        } else {
            $item->object->display($form->formhandle);//course wide
        }

        $item->typecheck = $item->type;
        if ($item->type == 'reference') {
            // Special check for reference type to find original type.
            $sourceid = $item->object->settingskeys['sourceitem'];
            $sourceitem = portfolioact_template_item::getitem($sourceid);
            if (!empty($sourceitem)) {
                $item->typecheck = $sourceitem->type;
            } else {
                $item->typecheck = null;
            }
        }
        // Work out if save button is needed on page.
        if (!in_array($item->typecheck, portfolioact_template_item::$readonly)) {
            $showsave = true;
        }

    }
    if ($showsave) {
        $form->formhandle->addElement('header', 'saveitems', '');
        //add the save button
        $form->formhandle->addElement('submit', 'submitbutton', get_string('saveitems',
            'portfolioactmode_template'));
    }

    if ($form->is_cancelled()) {
        exit;
    } else if ($fromform=$form->get_data()) {

        foreach ($fromform as $field => $data) {
            if (preg_match('/^item_(\d*)/', $field, $matches)) {
                $itemid =     $matches[1];
                //if datamode is course set actid to null or set it to the actid
                //if data mode is activity
                if (  ($subplug->settings->datamode == 1) ) {
                    $items[$itemid]->object->savedata($data);
                } else {
                    $items[$itemid]->object->savedata($data, $subplug->portfolioact->id);
                }
            }

        }
        $form->set_data(array());

    }

    echo $OUTPUT->header();
    if ($page === 0) {
        echo $subplug->renderer->render_portfolioactmode_template_viewhead($subplug);
    } else {
        echo $subplug->renderer->render_portfolioactmode_template_viewhead($subplug, false);
    }

    $form->display();

    //on the last one

    if (  (($pagecount - 1)  == $page) && ($subplug->settings->showsave == 1)  ) {
        echo html_writer::start_tag('div', array('class' => 'portfolioact_buttons'));
        foreach ($savetypes as $savetype) {
            $exportmodule = portfolioact_save::get_export_module($savetype,
            $subplug->portfolioact->id, $subplug->cm->id);
            echo $exportmodule->output_save_button($subplug->cm->id);
        }
        echo html_writer::end_tag('div');
    }

    $cmid = $id = optional_param('id', 0, PARAM_INT);
    $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $cmid ));
    $page = optional_param('page', 0, PARAM_INT);
    echo $OUTPUT->paging_bar($pagecount, $page, 1, $url, 'page' );

}
