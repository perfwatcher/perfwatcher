#! /usr/bin/php
<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et :

chdir(dirname(__FILE__)."/..");
$upgrade_scripts = array(
    "install/upgrade_scripts/upgrade_to_1.1.php",
);

foreach ($upgrade_scripts as $script) {
    print "Executing '$script'\n";
    chmod ($script, 0755);
    passthru($script);
}



?>
