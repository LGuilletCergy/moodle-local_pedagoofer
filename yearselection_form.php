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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Create all courses categories of the current year and fill the local_pedagooffer table
 *
 * @package   local_pedagooffer
 * @copyright 2017 Laurent Guillet <laurent.guillet@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : yearselection_form.php
 * Form of the yearselection page
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class pedagooffer_yearselection_form extends moodleform {

    public function definition() {

        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('text', 'year', get_string('year', 'local_pedagooffer')); // Add elements to your form
        $mform->setType('year', PARAM_NOTAGS); //Set type of element
        $mform->setDefault('year', $CFG->thisyear);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {

        return array();
    }
}