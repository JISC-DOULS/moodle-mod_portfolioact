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
class portfolioact_template_create_form extends moodleform {

    /**
     * Definition of the setting form elements
     */
    public function definition() {

        $mform  = $this->_form;
        $mform->addElement('header', 'settings', get_string('createtemplate',
            'portfolioactmode_template'));
        $mform->addElement('text', 'templatename', get_string('templatename',
            'portfolioactmode_template'));
        $mform->addRule('templatename', null, 'maxlength', 250, 'client');
        $mform->addRule('templatename', null, 'required', null, 'client');
        $this->add_action_buttons();

    }
}




/**
 * Template settings form
 *
 * This is used by the template code to edit the portfolio activity
 * template settings
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_template_edit_form extends moodleform {


    public $formhandle;

    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform  = $this->_form;

    }

    /**
     * Constructor for form
     *
     * Set the form onto an public property so that we can maniuplate it
     * outside.
     *
     * @param string $url
     */

    public function __construct($url) {
        parent::__construct($url);
        $this->formhandle =   $this->_form;
    }

}

/**
 * Template settings sub form
 *
 * This is used when we introduce a confirm stage to the main form.
 * This form is used to store the state of the main form during the confirm step.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioact_template_edit_confirm_form extends moodleform {

    public $saved;

    /**
     * Display the form
     *
     * Set the data onto the form object and then display it.
     *
     * @param string $data
     */

    public function display() {
         parent::display();
    }

     /**
      * Form defintion function
      */

    public function definition() {

        global $DB;
        $mform2 = $this->_form;
        $mform2->addElement('hidden', 'saveddata');
        $this->add_action_buttons(get_string('cancel'),
            get_string('absolutelyconfirm', 'portfolioactmode_template'));
    }

    /**
     * Set the saved data onto the element after the form has been created.
     *
     */

    public function definition_after_data() {
        $mform =& $this->_form;
        $el =& $mform->getElement('saveddata');
        $el->setValue($this->saved);
    }

}


/**
 * Page creation form
 *
 * This is used by the template code to add a page
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_create_page_form extends moodleform {

    /**
     * Definition of the setting form elements
     */
    public function definition() {

        $mform  = $this->_form;
        $mform->addElement('header', 'settings', get_string('createpage',
            'portfolioactmode_template'));
        $mform->addElement('text', 'pagename', get_string('pagename',
            'portfolioactmode_template'));
        $mform->addRule('pagename', null, 'maxlength', 250, 'client');
        $mform->addRule('pagename', null, 'required', null, 'client');
        $this->add_action_buttons();

    }
}




/**
 * Position update form for order of pages
 *
 * This is used by the template code to send a change of position of pages back to the server
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_update_position extends moodleform {

    public $formhandle;

    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform  = $this->_form;
        $pageorderfield = $mform->addElement('hidden', 'pageorderlist');
        $pageorderfield->setAttributes(array('id'=>'pageorderlist',
            'name'=>'pageorderlist', 'type'=>'hidden', 'value'=>''));

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


/**
 * Position update form for order of items
 *
 * This is used by the template code to send a change of position of items back to the server
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_update_items_position extends moodleform {

    public $formhandle;

    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform  = $this->_form;
        $pageorderfield = $mform->addElement('hidden', 'itemorderlist');
        $pageorderfield->setAttributes(array('id'=>'itemorderlist',
            'name'=>'itemorderlist', 'type'=>'hidden', 'value'=>''));

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

/**
 * Template / Page name update form
 *
 * This allows user to update the name of a template
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_update_name extends moodleform {

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

/**
 * Page editor update form
 *
 * This form allows users to add items to a page
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_items extends moodleform {

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

/**
 * Item settings form
 *
 * This form allows users to add items to a page
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_item_settings extends moodleform {

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


/**
 * User template form
 *
 * This form collects template data from the user for storing in the database
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioact_template_templatedata extends moodleform {

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
