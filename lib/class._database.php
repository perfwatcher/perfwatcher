<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 
/**
 * Tree lib adapted from JStree http://www.jstree.com/
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

class _database {
    private $link		= false;
    private $sth		= false;
    private $result		= false;
    private $row		= false;

    public $settings	= array(
            "dbtype" => "mysql",
            "servername"=> "localhost",
            "serverport"=> "3306",
            "username"	=> false,
            "password"	=> false,
            "database"	=> false,
            "persist"	=> false,
            "dieonerror"=> false,
            "show_error"	=> false,
            "error_file"=> true,
            "charset" 	=> "utf8",
            );

    function __construct() {
        global $db_config;
        $this->settings = array_merge($this->settings, $db_config);
        if($this->settings["error_file"] === true) $this->settings["error_file"] = dirname(__FILE__)."/__mysql_errors.log";
    }

    function connect() {
        if (!$this->link) {
            $dsn = array(
                    'phptype' => $this->settings["dbtype"],
                    'username' => $this->settings["username"],
                    'password' => $this->settings["password"],
                    'hostspec' => $this->settings["servername"],
                    'port' => $this->settings["serverport"],
                    'database' => $this->settings["database"],
                    );
            $options = array(
                    'debug' => 2,
                    'portability' => MDB2_PORTABILITY_ALL,
                    'persistent' => (($this->settings["persist"]) ? true : false),
                    );
            $this->link =& MDB2::factory($dsn, $options);
            if(PEAR::isError($this->link)) {
                $this->error($this->link->getMessage());
                unset($this->link);
            }
            unset($this->sth);
        }
        /* if($this->link) mysql_query("SET NAMES 'utf8'"); */

        return ($this->link) ? true : false;
    }

    function query($sql) {
        if (!$this->link && !$this->connect()) $this->error("No connection");
        $this->result = $this->link->query($sql);
#debug#		$this->log("QUERY : ".$sql);
        if(PEAR::isError($this->result)) {
            $this->error($this->result->getMessage(), "$sql");
            unset($this->result);
            return false;
        }
        return true;
    }

    function prepare($sql, $types = null, $result_types = null, $lobs = array()) {
        if (!$this->link && !$this->connect()) $this->error("No connection");
        if(isset($this->sth)) {
            $this->sth->free();
            if(PEAR::isError($this->sth)) {
                $this->error($this->sth->getMessage(), "$sql");
                unset($this->sth);
                return false;
            }
        }
        $this->sth = $this->link->prepare($sql, $types, $result_types, $lobs?$lobs:array());
        unset($this->result);
#debug#		$this->log($sql);
        if(PEAR::isError($this->sth)) {
            $this->error($this->sth->getMessage(), "$sql");
            unset($this->sth);
            return false;
        }
        return true;
    }

    function execute($values = null, $result_class = true, $result_wrap_class = false) {
        if (!$this->link && !$this->connect()) $this->error("No connection");
        if(!isset($this->sth)) {
            $this->error("No prepared query");
            return false;
        }
        unset($this->result);
#debug#		$this->log("execute : ".serialize($values));
        $this->result = $this->sth->execute($values, $result_class, $result_wrap_class);
        if(PEAR::isError($this->result)) {
            $this->error($this->result->getMessage());
            unset($this->result);
            return false;
        }
        return true;
    }

    function free() {
        if (!$this->link && !$this->connect()) $this->error("No connection");
        if(!isset($this->sth)) {
            $this->error("No prepared query");
            return false;
        }
        $this->sth->free();
        if(PEAR::isError($this->sth)) {
            $this->error($this->sth->getMessage(), "$sql");
            unset($this->sth);
            return false;
        }
        unset($this->sth);
        return true;
    }

    function nextr($mode = "assoc") {
        if(!isset($this->result)) {
            $this->error("No query pending");
            return false;
        }
        unset($this->row);
        switch($mode) {
            case "assoc" : $this->row = $this->result->fetchRow(MDB2_FETCHMODE_ASSOC); break;
            case "num"   : $this->row = $this->result->fetchRow(MDB2_FETCHMODE_ORDERED); break;
            default      : $this->row = $this->result->fetchRow(MDB2_FETCHMODE_ASSOC); break;
        }
#		$this->log("numrow/nextr  : ".$this->result->numRows()."   ".serialize($this->row));
        return ($this->row) ? true : false ;
    }

    function get_row($mode = "assoc") {
        if(!$this->row) return false;

        $return = array();
        switch($mode) {
            case "assoc":
                foreach($this->row as $k => $v) {
                    if(!is_int($k)) $return[$k] = $v;
                }
            break;
            case "num":
                foreach($this->row as $k => $v) {
                    if(is_int($k)) $return[$k] = $v;
                }
            break;
            default:
            $return = $this->row;
            break;
        }
        return array_map("stripslashes",$return);
    }

    function get_all($mode = "assoc", $key = false) {
        if(!$this->result) {
            $this->error("No query pending");
            return false;
        }
        $return = array();
        while($this->nextr($mode)) {
            if($key !== false) $return[$this->f($key)] = $this->get_row($mode);
            else $return[] = $this->get_row($mode);
        }
        return $return;
    }

    function f($index) {
        if(!$this->row) return false;
        return stripslashes($this->row[$index]);
    }

    /* Unsupported */
    /* For developers, check if $this->result->seek() could fit */
    /*
       function go_to($row) {
       if(!$this->result) {
       $this->error("No query pending");
       return false;
       }
       if(!mysql_data_seek($this->result, $row)) $this->error();
       }
     */

    function nf() {
        if (($numb = $this->result->numRows())  === false) $this->error($this->result->getMessage());
#debug#		$this->log("nf : ".$this->result->numRows()." / $numb");
        return $numb;
    }
    function af() {
        return $this->result->affectedRows();
    }
    function log($string="") {
        if(isset($this->settings["show_error"]) && $this->settings["show_error"]) echo $string;
        if($this->settings["error_file"] !== false) {
            $handle = @fopen($this->settings["error_file"], "a+");
            if($handle) {
                @fwrite($handle, "[".date("Y-m-d H:i:s")."] INFO : ".$string."\n");
                @fclose($handle);
            }
        }
    }
    function error($error, $string="") {
        if(isset($this->settings["show_error"]) && $this->settings["show_error"]) echo $error;
        if($this->settings["error_file"] !== false) {
            $handle = @fopen($this->settings["error_file"], "a+");
            if($handle) {
                @fwrite($handle, "[".date("Y-m-d H:i:s")."] ".$string." <".$error.">\n");
                @fclose($handle);
            }
        }
        if($this->settings["dieonerror"]) {
            if(isset($this->result)) $this->result->free();
            $this->link->disconnect();
            die();
        }
    }
    function insert_id($table = null, $field = null) {
        if(!$this->link) return false;
        if(isset($table) && isset($field)) $r = $this->link->lastInsertID($table,$field);
        else $r = $this->link->lastInsertID(); /* Not supported on all RDBMS */
#debug#		$this->log("last id = $r");
        return($r);
    }
    function setLimit($limit, $offset=null) {
        if(!$this->link) return false;
        $this->link->setLimit($limit, $offset);
    }
    function escape($string){
        if(!$this->link) return addslashes($string);
        return $this->link->quote($string);
    }

    function destroy(){
        if (isset($this->result) && $this->result !== false) $this->result->free();
        if (isset($this->link)) $this->link->disconnect();
    }


}
?>
