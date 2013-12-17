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

class folder_filling_manual {
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
                'title' => "Autofill this container using manual list",
                'content_url' => 'html/folder_filling_manual.html'
                );
    }

    function get() {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if (isset($datas['serverslist']) &&  isset($datas['serverslist']['manuallist'])) {
            $r = array();
            foreach (split("\n", $datas['serverslist']['manuallist']) as $l) {
                $r[] = trim($l);
            }
            return $r;
        } else { return array(); }
    }

    function save ($list) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['manuallist'] = $list;
        $jstree->set_datas($this->item['id'], $datas);
        return true;
    }
}

?>
