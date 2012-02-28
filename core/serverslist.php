<?php
/**
 *
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
 * @copyright 2011 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */ 

switch ($_POST['action']) {
/*    case 'gprimetest': 
        $list = file($serverlisturl);
        $i=0;
        foreach($list as $line) {
            if ($i == 0) {
                list($header, $line) = split('<br>', $line, 2);
                echo "$header<br/><br/>";
                echo "2000 First results<br/><br/>";
                $i++;
            }
            $line = trim(str_replace('<br>', '', $line));
            if ($i == 100) { break; }
            if ($line == '') { continue; }
            if (@ereg($_POST['gprimeregex'], $line)) {
                echo "$line<br/>";
                $i++;
            }
        }
    break;
    case 'gprimesave':
        if(!ereg("/admin/", $_SERVER["REQUEST_URI"]) || !is_numeric($_SERVER["PHP_AUTH_USER"])) { die(); }
        echo "Regex saved";
        $jstree = new json_tree();
        $datas = $jstree->get_datas($_GET['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['gprimeregex'] = $_POST['gprimeregex'];
        $jstree->set_datas($_GET['id'], $datas);
    break;*/
    case 'servernametest':
        $dh = opendir($rrds_path);
        while ($hostdir = readdir($dh)) {
            if ($hostdir == '..' || $hostdir == '.' || $hostdir == '_csv') { continue; }
            if (@ereg($_POST['servernameregex'], $hostdir)) {
                echo "$hostdir<br/>";
            }
            
        }
        closedir($dh);
    break;
    case 'servernamesave':
        //if(!ereg("/admin/", $_SERVER["REQUEST_URI"]) || !is_numeric($_SERVER["PHP_AUTH_USER"])) { die(); }
        echo "Regex saved";
        $jstree = new json_tree();
        $datas = $jstree->get_datas($_GET['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['servernameregex'] = $_POST['servernameregex'];
        $jstree->set_datas($_GET['id'], $datas);
    break;
/*    case 'switchtest':
        echo "#hostname;switchportname<br/><br/>";
        echo "2000 First results<br/><br/>";
        $i = 0;
        if ($fp = fopen('/var/www/html/phpcollectd/tmp/netlist.txt', 'r')) {
            while ($line = fgets($fp, 4096)) {
                if ($i == 2000) { continue; }
                if (@ereg($_POST['switchregex'], trim($line))) {
                    echo trim($line)."<br/>";
                    $i++;
                }
            }
            fclose($fp);
        }
    break;
    case 'switchsave':
        if(!ereg("/admin/", $_SERVER["REQUEST_URI"]) || !is_numeric($_SERVER["PHP_AUTH_USER"])) { die(); }
        echo "Regex saved";
        $jstree = new json_tree();
        $datas = $jstree->get_datas($_GET['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['switchregex'] = $_POST['switchregex'];
        $jstree->set_datas($_GET['id'], $datas);
    break;*/
    case 'manuallistsave':
        //if(!ereg("/admin/", $_SERVER["REQUEST_URI"]) || !is_numeric($_SERVER["PHP_AUTH_USER"])) { die(); }
        echo "Manual list saved";
        $jstree = new json_tree();
        $datas = $jstree->get_datas($_GET['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        $datas['serverslist']['manuallist'] = $_POST['manuallist'];
        $jstree->set_datas($_GET['id'], $datas);
    break;
}



?>
