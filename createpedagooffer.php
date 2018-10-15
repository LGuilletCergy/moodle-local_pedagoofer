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
 * File : createpedagooffer.php
 * Script to create categories
 */

include('../../config.php');
require_once($CFG->libdir.'/coursecatlib.php');

$year = required_param('year', PARAM_INT);

$contextsystem = context_system::instance();

require_capability('local/pedagooffer:create', $contextsystem);

$codeyear = "Y$year";

if ($DB->record_exists('course_categories', array('idnumber' => $codeyear, 'parent' => 0))) {

    $yearcategory = $DB->get_record('course_categories', array('idnumber' => $codeyear, 'parent' => 0));
} else {

    $endyear = $year+1;

    $datayear = new stdClass();
    $datayear->name = "Cours $year-$endyear";
    $datayear->idnumber = $codeyear;
    $datayear->parent = 0;
    $datayear->visible = 1;

    $yearcategory = coursecat::create($datayear);
}

$xmldoc = new DOMDocument();
$xmldoc->load('data/basicstructure.xml');
$xpathvar = new Domxpath($xmldoc);

$querycomposante = $xpathvar->query('//Composante');

foreach ($querycomposante as $composante) {

    $composantename = $composante->getAttribute('nom');
    $composantecode = $yearcategory->idnumber."-".$composante->getAttribute('code_composante');

    if ($DB->record_exists('course_categories',
            array('idnumber' => $composantecode, 'parent' => $yearcategory->id))) {

        $composantecategory = $DB->get_record('course_categories',
            array('idnumber' => $composantecode, 'parent' => $yearcategory->id));
    } else {

        $datacomposante = new stdClass();
        $datacomposante->name = $composantename;
        $datacomposante->idnumber = $composantecode;
        $datacomposante->parent = $yearcategory->id;
        $datacomposante->visible = 1;

        $composantecategory = coursecat::create($datacomposante);
    }

    foreach ($composante->childNodes as $level) {

        if ($level->nodeType !== 1) {
            continue;
        }

        $levelname = $level->getAttribute('nom');
        $levelcode = $composantecode.$level->getAttribute('code_niveau');

        if ($DB->record_exists('course_categories',
                array('idnumber' => $levelcode, 'parent' => $composantecategory->id))) {

            $levelcategory = $DB->get_record('course_categories',
                array('idnumber' => $levelcode, 'parent' => $composantecategory->id));
        } else {

            $datalevel = new stdClass();
            $datalevel->name = $levelname;
            $datalevel->idnumber = $levelcode;
            $datalevel->parent = $composantecategory->id;
            $datalevel->visible = 1;

            $levelcategory = coursecat::create($datalevel);
        }

        foreach ($level->childNodes as $vet) {

            if ($vet->nodeType !== 1) {
                continue;
            }

            $vetname = $vet->getAttribute('nom');
            $vetcode = $composantecode.$vet->getAttribute('code_vet');

            if ($DB->record_exists('course_categories',
                    array('idnumber' => $vetcode, 'parent' => $levelcategory->id))) {

                $vetcategory = $DB->get_record('course_categories',
                    array('idnumber' => $vetcode, 'parent' => $levelcategory->id));
            } else {

                $datavet = new stdClass();
                $datavet->name = $vetname;
                $datavet->idnumber = $vetcode;
                $datavet->parent = $levelcategory->id;
                $datavet->visible = 1;

                $vetcategory = coursecat::create($datavet);
            }
        }
    }
}

$xmldocdokeos = new DOMDocument();
$xmldocdokeos->load('/home/referentiel/dokeos_offre_pedagogique.xml');
$xpathvardokeos = new Domxpath($xmldocdokeos);

$queryvet = $xpathvardokeos->query('//Etape');

foreach($queryvet as $vet){

    $codevet = $vet->getAttribute('Code_etape');
    $codevetyear = "$codeyear-$codevet";
    $nomvet = $vet->getAttribute('Lib_etape');
    $updatedvetname = "($codevet) $nomvet";
    $updatedcategoryname = "($codevetyear) $nomvet";

    if ($DB->record_exists('course_categories', array('idnumber' => $codevetyear))) {

        $vetcategory = $DB->get_record('course_categories', array('idnumber' => $codevetyear));
        $currentvetname = $vetcategory->name;
        if ($currentvetname != $updatedvetname) {

            echo "MISE A JOUR CATEGORIE VET : $currentvetname -> $updatedvetname<br/>\n";

            $vetcategory->name = $updatedvetname;
            $DB->update_record('course_categories', $vetcategory);
        }
    } else {

        // Déterminer parent

        $ufr = substr($codevet, 0, 1);
        $yearufr = $codeyear."-".$ufr;

        $testdulp = substr($nomvet, 0, 2);
        $testlicence = substr($nomvet, 0, 7);
        $testmaster = substr($nomvet, 0, 6);
        $testcmi = substr($nomvet, 0, 3);
        $vetlastchar = substr($nomvet, -1);

        if ($testlicence == "Licence") {

            if ($vetlastchar == "1") {

                $levelcode = $yearufr."L1";
            } else if ($vetlastchar == "2") {

                $levelcode = $yearufr."L2";
            } else {

                $levelcode = $yearufr."L3";
            }
        } else if ($testdulp == "LP") {

            $levelcode = $yearufr."LP";
        } else if ($testdulp == "DU") {

            $levelcode = $yearufr."DU";
        } else if ($testmaster == "Master") {

            if ($vetlastchar == "1") {

                $levelcode = $yearufr."M1";
            } else {

                $levelcode = $yearufr."M2";
            }
        } else if ($testcmi = "CMI") {

            $levelcode = $yearufr."CM";
        } else {

            $levelcode = $yearufr."AU";
        }

        if ($DB->record_exist('course_categories', array('idnumber' => $levelcode))) {

            $levelid = $DB->get_record('course_categories', array('idnumber' => $levelcode))->id;

            $vetdata = new stdClass();
            $vetdata->name = $updatedvetname;
            $vetdata->idnumber = $codevetyear;
            $vetdata->parent = $levelid;
            $vetdata->visible = 1;

            $vetcategory = coursecat::create($vetdata);
        }
    }

    foreach ($vet->childNodes as $versionetape) {

        if ($versionetape->nodeType !== 1) {
            continue;
        }

        foreach ($versionetape->childNodes as $elp) {

            if ($elp->nodeType !== 1) {
                continue;
            }

            if ($DB->record_exists('local_pedagooffer',
                    array('categoryid' => $vetcategory->id,
                        'codeelp' => $elp->getAttribute('Code_ELP')))) {

                $elprecord = $DB->get_record('local_pedagooffer',
                        array('categoryid' => $vetcategory->id,
                            'codeelp' => $elp->getAttribute('Code_ELP')));

                $elprecord->name = $elp->getAttribute('Lib_ELP');

                $DB->update_record('local_pedagooffer', $elprecord);
            } else {

                $elpdata = new stdClass();
                $elpdata->categoryid = $vetcategory->id;
                $elpdata->codeelp = $elp->getAttribute('Code_ELP');
                $elpdata->name = $elp->getAttribute('Lib_ELP');

                $DB->insert_record('local_pedagooffer', $elpdata);
            }
        }
    }
}



$settingsurl = new moodle_url('/admin/search.php');

redirect($settingsurl);

