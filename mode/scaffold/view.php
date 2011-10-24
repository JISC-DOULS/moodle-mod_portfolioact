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
 *
 * This file is designed to be included by the parent module view.php file.
 *
 * @package   portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */




require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/locallib.php');
require_once($CFG->dirroot.'/mod/portfolioact/save/lib.php');


if (! defined('PORTFOLIOACTVIEWER')) {
    throw new moodle_exception('youdonothavepermission', 'portfolioact');
}


/**
 * Handles display of the Scaffold Activity
 *
 * @param mixed $subplug an instance of the mode e.g. template, scaffold
 */

function portfolioactmode_scaffold_view($subplug) {

    global $OUTPUT;

    $context = get_context_instance(CONTEXT_MODULE, $subplug->cm->id);
    require_capability('mod/portfolioact:canview', $context );

    $scaffoldid = $subplug->settings->scaffold;

    echo $OUTPUT->header();
    echo $subplug->renderer->render_portfolioactmode_scaffold_viewhead($subplug);

    try {
        $scaffold = new portfolioact_scaffold($scaffoldid);
    } catch (Exception $e) {
        $canedit = has_capability('mod/portfolioact:canedit', $context );

        echo $subplug->pa_renderer->render_portfolioactmode_notyetsetup($subplug->cm->id,
        $canedit, 'scaffold');
        echo $OUTPUT->footer();
        exit;
    }

    //false means if a scaffold has just empty dirs and no files we say it is not empty

    if ( $scaffold->is_empty(false)  ) {
        $canedit = has_capability('mod/portfolioact:canedit', $context );

        echo $subplug->renderer->render_emptyscaffold($subplug->cm->id,
        $canedit, $scaffold->id);
        echo $OUTPUT->footer();
        exit;
    }

    $savetypes = $subplug->get_allowed_save_types();
    echo html_writer::start_tag('div', array('class' => 'portfolioact_buttons'));
    foreach ($savetypes as $savetype) {
        $exportmodule = portfolioact_save::get_export_module
        ($savetype, $subplug->portfolioact->id, $subplug->cm->id);
        echo $exportmodule->output_save_button($subplug->cm->id);
    }
    echo html_writer::end_tag('div');
}
