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
 * Class containing helper methods for processing data requests.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing helper methods for processing data requests.
 *
 * @copyright  2018 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadata_registry {

    public function do_what_i_want() {
        $manager = new \core_privacy\manager();
        $metadata = $manager->get_metadata_for_components();
        // print_object($metadata);
        $components = $this->get_component_list();

        // print_object($this->get_component_list_full());
        // $componentdata = array_map(function($component) use ($manager, $metadata) {
        return array_map(function($component) use ($manager, $metadata) {
            $internaldata = ['component' => $component];
            if ($manager->component_is_compliant($component)) {
                $internaldata['compliant'] = true;
                if (isset($metadata[$component])) {
                    $collection = $metadata[$component]->get_collection();
                    foreach ($collection as $collectioninfo) {
                        $privacyfields = $collectioninfo->get_privacy_fields();
                        $fields = '';
                        if (!empty($privacyfields)) {
                            $fields = array_map(function($key, $field) use ($component) {
                                return [
                                    'field_name' => $key,
                                    'field_summary' => get_string($field, $component)
                                ];
                            }, array_keys($privacyfields), $privacyfields);
                        }
                        // Can the metadata types be located somewhere else besides core?
                        $items = explode('\\', get_class($collectioninfo));
                        $type = array_pop($items);
                        $internaldata['metadata'][] = [
                            'name' => $collectioninfo->get_name(),
                            'type' => $type,
                            'fields' => $fields,
                            'summary' => get_string($collectioninfo->get_summary(), $component)
                        ];
                    }
                } else {
                    // Call get_reason for null provider.
                    $internaldata['nullprovider'] = get_string($manager->get_null_provider_reason($component), $component);
                }
            } else {
                $internaldata['compliant'] = false;
            }
            return $internaldata;
        }, $components);
    }

    public function do_what_i_want_take_2() {
        $manager = new \core_privacy\manager();
        $metadata = $manager->get_metadata_for_components();
        $fullyrichtree = $this->get_full_component_list();
        foreach ($fullyrichtree as $key => $values) {
            $plugins = array_map(function($component) use ($manager, $metadata) {
                $internaldata = ['component' => $component];
                if ($manager->component_is_compliant($component)) {
                    $internaldata['compliant'] = true;
                    if (isset($metadata[$component])) {
                        $collection = $metadata[$component]->get_collection();
                        foreach ($collection as $collectioninfo) {
                            $privacyfields = $collectioninfo->get_privacy_fields();
                            $fields = '';
                            if (!empty($privacyfields)) {
                                $fields = array_map(function($key, $field) use ($component) {
                                    return [
                                        'field_name' => $key,
                                        'field_summary' => get_string($field, $component)
                                    ];
                                }, array_keys($privacyfields), $privacyfields);
                            }
                            // Can the metadata types be located somewhere else besides core?
                            $items = explode('\\', get_class($collectioninfo));
                            $type = array_pop($items);
                            $internaldata['metadata'][] = [
                                'name' => $collectioninfo->get_name(),
                                'type' => $type,
                                'fields' => $fields,
                                'summary' => get_string($collectioninfo->get_summary(), $component)
                            ];
                        }
                    } else {
                        // Call get_reason for null provider.
                        $internaldata['nullprovider'] = get_string($manager->get_null_provider_reason($component), $component);
                    }
                } else {
                    $internaldata['compliant'] = false;
                }
                return $internaldata;
            }, $values['plugins']);
            $fullyrichtree[$key]['plugins'] = $plugins;
        }
        return $fullyrichtree;
    }

    /**
     * Returns a list of frankenstyle names of core components (plugins and subsystems).
     *
     * @return array the array of frankenstyle component names.
     */
    protected function get_component_list() {
        $components = [];
        // Get all plugins.
        $plugintypes = \core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $typedir) {
            $plugins = \core_component::get_plugin_list($plugintype);
            foreach ($plugins as $pluginname => $plugindir) {
                $components[] = $plugintype . '_' . $pluginname;
            }
        }
        // Get all subsystems.
        foreach (\core_component::get_core_subsystems() as $name => $path) {
            if (isset($path)) {
                $components[] = 'core_' . $name;
            }
        }
        return $components;
    }

    protected function get_full_component_list() {
        $root = \core_component::get_plugin_types();
        $root = array_map(function($plugintype) {
            return [
                'plugin_type' => $plugintype,
                'plugins' => array_map(function($pluginname) use ($plugintype) {
                    return $plugintype . '_' . $pluginname;
                }, array_keys(\core_component::get_plugin_list($plugintype)))
            ];
        }, array_keys($root));
        // Add subsystems.
        $corenames = array_map(function($name) {
            return 'core_' . $name;
        }, array_keys(array_filter(\core_component::get_core_subsystems(), function($path) {
                if (isset($path)) {
                    return true;
                }
        })));
        $root[] = ['plugin_type' => 'core', 'plugins' => $corenames];
        return $root;
    }
}
