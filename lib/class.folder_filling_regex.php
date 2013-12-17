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

class folder_filling_regex {
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
        return array(
                'title' => "Autofill this container using regex",
                'content_url' => 'html/folder_filling_regex.html'
                );
    }

    function test ($regex) {
        global $jstree;
        return implode("\n", $this->get($regex));
    }

    function get ($regex = null) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if ($regex === null) {
            if (isset($this->datas['serverslist']) && isset($this->datas['serverslist']['servernameregex'])) {
                $regex = $this->datas['serverslist']['servernameregex'];
            }
        }
        if (! $regex) {
            return array();
        }
        $cdsrc = $jstree->get_node_collectd_source($this->object['id']);
        $list = get_list_of_hosts_having_rrds($cdsrc, false);
        $out = preg_grep("/${regex}/", array_keys($list));
        return $out;
    }

    function save ($regex) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['servernameregex'] = $regex;
        $jstree->set_datas($this->item['id'], $datas);
        return true;
    }
}

?>
