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
 * Deletes the scaffolds
 *
 * @return boolean
 */

function xmldb_portfolioactmode_scaffold_uninstall() {
    global $DB;

    $fs = get_file_storage();
    $file_records = $DB->get_records('files', array('component'=>'mod_portfolioactmode_scaffold',
        'filearea'=>'scaffoldset'));
    foreach ($file_records as $file_record) {
        $file = $fs->get_file_instance($file_record);
        $file->delete();
    }

    return true;

}