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
 * File : yearselection.php
 * Enter year to creat then launch script.
 */

include('../../config.php');
require_once('yearselection_form.php');

require_login();

$context = context_system::instance();

$PAGE->set_context($context);

$currenturl = new moodle_url('/local/pedagooffer/yearselection.php');

$PAGE->set_url($currenturl);

$contextsystem = context_system::instance();

require_capability('local/pedagooffer:create', $contextsystem);

$mform = new pedagooffer_yearselection_form();

if ($mform->is_cancelled()) {

    $originurl = new moodle_url('/admin/search.php');

    redirect($originurl);

} else if ($fromform = $mform->get_data()) {

    $scripturl = new moodle_url('/local/pedagooffer/createpedagooffer.php',
            array('year' => $fromform->year));

    redirect($scripturl);

} else {

    echo $OUTPUT->header();

    $mform->display();
}

echo $OUTPUT->footer();