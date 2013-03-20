<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 
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

class folder_aggregator {
    private $datas = array();

    function __construct($datas) {
        $this->datas =& $datas;
    }

    function is_compatible() {
        switch($this->datas['type']) {
            case 'folder':
            case 'drive':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    function get_info() {
        global $folder_filling_plugins, $grouped_type;
        return array(
                'title' => "Aggregated metrics from servers under this ".$this->datas['type'],
                'content_url' => 'html/folder_aggregator.html',
                'grouped_type' => $grouped_type
                );
    }

    function add_plugin($plugin, $cf) {
        global $jstree;
        $datas = $jstree->get_datas($this->datas['id']);
        if (!isset($datas['plugins'])) { $datas['plugins'] = array(); }
        if (!isset($datas['plugins'][$plugin[0].'-'.$cf])) {
            $datas['plugins'][$plugin[0].'-'.$cf] = true;
            $jstree->set_datas($this->datas['id'], $datas);
        }
    }

    function del_plugin($plugin) {
        global $jstree;
        $datas = $jstree->get_datas($this->datas['id']);
        unset($datas['plugins'][$plugin]);
        $jstree->set_datas($this->datas['id'], $datas);
    }
}

?>
