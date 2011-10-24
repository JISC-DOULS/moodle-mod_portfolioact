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
 * The main portfolioact configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package mod
 * @subpackage portfolioact
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/locallib.php');

class mod_portfolioact_mod_form extends moodleform_mod {



    public function definition() {

        global $COURSE;
        $mform =& $this->_form;

        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);

        //-------------------------------------------------------------------------------
        //Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('portfolioactname', 'portfolioact'),
            array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        /// Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor(false);

        //-------------------------------------------------------------------------------
        /// Adding the rest of portfolioact settings, spreading all them into
        // this fieldset or adding more fieldsets ('header' elements) if needed for better
        // logic

        $modes = portfolioact_mode::return_modes();
        $mform->addElement('select', 'modename', get_string('modename', 'portfolioact'), $modes);

        if (isset ($this->_cm)) {
            $saves = portfolioact_save::get_save_types($this->_cm->instance);
        } else {
            $saves = portfolioact_save::get_save_types(null);
        }

        //Create interface based on selection - Any optional types to turn on.
        //Show multi-select with selected based on savetypes value
        $savetypes = array();
        foreach ($saves as $type => $info) {
            if ($info->siteenabled == portfolioact_save::TYPE_ENABLED) {
                $savetypes[$type] = $info->name;
            }
        }

        $select = &$mform->addElement('select',
            'savetypes', get_string('savetypesname', 'portfolioact'),
            $savetypes);
        $select->setMultiple(true);
        $mform->addHelpButton('savetypes', 'savetypesname', 'portfolioact');

        //TODO Add elements for selecting save types, overriding default
        //-------------------------------------------------------------------------------
        //add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        //add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    public function get_data() {
        $data = parent::get_data();

        //Bug in moodle forms lib means multi-select array doesn't get turned into string
        if (isset($data->savetypes)) {
            $data->savetypes = implode(',', $data->savetypes);
        }

        return $data;
    }

    public function data_preprocessing(&$default_values) {
        /*
        if (isset($default_values['savetypes'])) {
            TODO Any data preprocessing for save types (delete this function if not needed)
        }
        */
    }
}
