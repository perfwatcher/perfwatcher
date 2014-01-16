<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2012 Cyril Feraudet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2012 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

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
        $cdsrc = $jstree->get_node_collectd_source($this->item['id']);
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
