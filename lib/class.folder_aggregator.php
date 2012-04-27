<?php
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
		global $folder_filling_plugins;
		return array(
			'title' => "Aggregated data from from under this ".$this->datas['type'],
			'content_url' => 'html/folder_aggregator.html'
		);
	}

	function test ($regex) {
		global $gprimeserver;
		$out = '';
        $list = file($gprimeserver);
        $i=0;
        foreach($list as $line) {
            if ($i == 0) {
                list($header, $line) = split('<br>', $line, 2);
                $out .= "$header<br/><br/>";
                $out .= "2000 First results<br/><br/>";
                $i++;
            }
            $line = trim(str_replace('<br>', '', $line));
            if ($i == 100) { break; }
            if ($line == '') { continue; }
            if (@ereg($regex, $line)) {
                $out .= "$line<br/>";
                $i++;
            }
        }
		return $out;
	}

	function save ($regex) {
		global $jstree, $id;
		$datas = $jstree->get_datas($this->datas['id']);
		if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
		$datas['serverslist']['gprimeregex'] = $regex;
        $jstree->set_datas($id, $datas);
		return true;
	}
}

?>
