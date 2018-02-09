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
 * This page lets users manage purposes.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$context = \context_system::instance();

$url = new moodle_url("/admin/tool/dataprivacy/purposes.php");
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

require_login();
// TODO Check that data privacy is enabled.
require_capability('tool/dataprivacy:managedataregistry', $context);

$output = $PAGE->get_renderer('tool_dataprivacy');
$title = get_string('purposes', 'tool_dataprivacy');
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $output->header();

$purposes = \tool_dataprivacy\api::get_purposes();
$renderable = new \tool_dataprivacy\output\purposes_page($purposes);

echo $output->render($renderable);
echo $output->footer();