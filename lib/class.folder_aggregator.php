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
        global $grouped_type;
        return array(
                'title' => "Aggregated metrics from servers under this ".$this->datas['type'],
                'content_url' => 'html/folder_aggregator.html',
                'grouped_type' => $grouped_type
                );
    }

    function add_aggregator($cdsrc, $aggregator, $cf) {
        global $jstree;
        $datas = $jstree->get_datas($this->datas['id']);
        if (!isset($datas['aggregators'])) { $datas['aggregators'] = array(); }
        if (!isset($datas['aggregators'][$aggregator[0].'-'.$cf])) {
            $datas['aggregators'][$cdsrc."/".$aggregator[0].'-'.$cf] = array( 'CdSrc' => $cdsrc, 'plugin' => $aggregator[0].'-'.$cf );
            $jstree->set_datas($this->datas['id'], $datas);
        }
    }

    function del_aggregator($cdsrc, $aggregator) {
        global $jstree;
        $datas = $jstree->get_datas($this->datas['id']);
        unset($datas['aggregators'][$cdsrc."/".$aggregator]);
        $jstree->set_datas($this->datas['id'], $datas);
    }
}

?>
