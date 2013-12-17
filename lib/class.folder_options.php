<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2012 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */

class folder_options {
    private $item = array();

    function __construct($item) {
        $this->item =& $item;
    }

    function is_compatible() {
        switch($this->item['pwtype']) {
            case 'container':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    function get_info() {
        $obt = "";
        switch($this->item['pwtype']) {
            case 'container': $obt = "container"; break;
            case 'server': $obt = "server"; break;
            case 'selection': $obt = "selection"; break;
            default: $obt = "??? (bug ?)";
        }

        return array(
                'title' => "$obt options",
                'content_url' => 'html/folder_options.html'
                );
    }

    /* Note: this function may be useless. To be checked. */
    function save ($list) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['manuallist'] = $list;
        $jstree->set_datas($this->item['id'], $datas);
        return true;
    }

    function save_sort ($sort) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        $datas['sort'] = $sort;
        $jstree->set_datas($this->item['id'], $datas);
        return true;
    }

    function get_config_list () {
        global $collectd_sources;
        return array_keys($collectd_sources);
    }

    function save_cdsrc ($cdsrc) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if($cdsrc == "Inherit from parent") {
            unset($datas['CdSrc']);
        } else {
            $datas['CdSrc'] = $cdsrc;
        }
        $jstree->set_datas($this->item['id'], $datas);
        return true;
    }
}

?>
