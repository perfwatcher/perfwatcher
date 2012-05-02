<?php

if (!isset($_GET['id']) and !isset($_POST['id'])) {
    die('Error : POST or GET id missing !!');
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];
} else {
    die('Error : No valid id found !!!');
}

if (!isset($_GET['action'])) { die('No action submited !'); }
switch($_GET['action']) {
	case 'get_status':
		require "lib/ofc/open-flash-chart.php";
		$jstree = new json_tree();

		$nodelist = $jstree->get_nodechildren_id($id);
		$status = get_status($nodelist);


		$d = array();
		$d[] = new pie_value(count($status['up']), "Up");
		$d[] = new pie_value(count($status['down']), "Down");
		$d[] = new pie_value(count($status['unknown']), "Unknown");

		$pie = new pie();
		$pie->set_animate( true );
		//$pie->add_animation( new pie_fade() );
		$pie->set_label_colour( '#432BAF' );
		$pie->set_start_angle( 35 );
		//$pie->set_alpha( 0.75 );
		//
		// This is where we turn of the labels,
		// but we use them inside the tooltip:
		//
		$pie->set_tooltip( '#label#<br>#val# hosts (#percent#)' );
		$pie->set_colours(array('#77CC6D', '#FF5973', '#838282'));

		$pie->set_values( $d );
		$pie->on_click('piesliceclicked');

		$chart = new open_flash_chart();
		$chart->set_bg_colour( '#FFFFFF' );
		$chart->add_element( $pie );

		echo $chart->toPrettyString();
	break;
	case 'get_servers':
		$jstree = new json_tree();
		$nodelist = $jstree->get_nodechildren_id($id);
		$status = get_status($nodelist, true);
		foreach($status[$_GET['status']] as $server) {
			echo $server."<br/>\n";
		}
	break;
}

?>
