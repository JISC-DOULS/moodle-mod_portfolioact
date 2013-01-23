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
 * Copy of pluginfile for (Google) export use - can be SSO disabled for public access.
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_template
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Make sure this is only used for potfolioact stuff...
$params = explode('/', $_SERVER['PATH_INFO']);

if (!in_array('portfolioactmode_template', $params)) {
    exit;
}

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/pluginfile.php';
