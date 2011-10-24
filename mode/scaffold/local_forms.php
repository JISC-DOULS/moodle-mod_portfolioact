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
 * Forms for managing the templates
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright 2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Template creation form
 *
 * This is used by the template code to add a template
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_scaffold_create_form extends moodleform {

    /**
     * Definition of the setting form elements
     */
    public function definition() {

        $mform  = $this->_form;
        $mform->addElement('header', 'settings', get_string('createscaffold',
            'portfolioactmode_scaffold'));
        $mform->addElement('text', 'scaffoldname', get_string('scaffoldname',
            'portfolioactmode_scaffold'));
        $mform->addRule('scaffoldname', null, 'maxlength', 250, 'client');
        $mform->addRule('scaffoldname', null, 'required', null, 'client');
        $this->add_action_buttons();

    }
}

/**
 * Scaffold settings form
 *
 * This is used by the scaffold code to edit the portfolio activity scaffold settings
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_scaffold_edit_form extends moodleform {


    public $formhandle;

    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform  = $this->_form;

    }

    public function __construct($url) {
        parent::__construct($url);
        $this->formhandle =   $this->_form;
    }

}



/**
 * Scaffold files and folders form
 *
 * Creation of the scaffolds. (A file and folder structure).
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_scaffold_creation_form extends moodleform {


    public $formhandle;

    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform  = $this->_form;

    }

    public function __construct($url) {
        parent::__construct($url);
        $this->formhandle =   $this->_form;
    }

}


/**
 * Scaffold name update form
 *
 * This allows user to update the name of a scaffold
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_scaffold_update_name extends moodleform {

    public $formhandle;


    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform  = $this->_form;

    }

    /**
     * Construct the form
     *
     * Construct the form and expose the form object externally.
     *
     * @param string $url
     */

    public function __construct($url) {
        parent::__construct($url);
        $this->formhandle =   $this->_form;
    }

}
