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
 * Configure portfolioact file save type
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$choices = array(
        0 => get_string('save_disabled', 'portfolioact'),
        1 => get_string('save_enabled', 'portfolioact'),
        2 => get_string('save_force', 'portfolioact')
);

$settings->add(new admin_setting_configselect('portfolioactsave_google/google_enabled',
    get_string('enabled', 'portfolioactsave_google'),
    get_string('enabled_desc', 'portfolioact'), 1, $choices
    ));

$settings->add(new admin_setting_configtext('portfolioactsave_google/google_domain',
    get_string('admingoogle_domain', 'portfolioactsave_google'),
    get_string('admingoogle_domain_desc', 'portfolioactsave_google'), ''
    )
);
