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
 * Module settings - sub plugins must be manually added
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// include the settings of mode subplugins
$plugs = get_plugin_list('portfolioactmode');
foreach ($plugs as $plugin => $path) {
    if (file_exists($settingsfile = $path . '/settings.php')) {
        $settings->add(new admin_setting_heading('portfolioactsetting'.$plugin,
        get_string('pluginname', 'portfolioact') . ' - '
           . get_string('pluginname', 'portfolioactmode_' . $plugin), ''));
        include($settingsfile);
    }
}

// include the settings of save subplugins
$plugs = get_plugin_list('portfolioactsave');
foreach ($plugs as $plugin => $path) {
    if (file_exists($settingsfile = $path . '/settings.php')) {
        $settings->add(new admin_setting_heading('portfolioactsetting'.$plugin,
        get_string('pluginname', 'portfolioact') . ' - ' .
           get_string('pluginname', 'portfolioactsave_' . $plugin), ''));
        include($settingsfile);
    }
}
