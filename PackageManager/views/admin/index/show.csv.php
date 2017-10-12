<?php
$csv_header = array("title","summary","lang","credits","tags","path","preview");
$export = array();
echo package_manager_array_to_csv( $csv_header, ',', '"' );
echo "\r\n";
foreach($contents as $item_id){
	$item = get_record_by_id('item', $item_id);
	if(!$item || empty($item)) continue;
	$preview = $item->Files;
	$fields = array(
		html_entity_decode(metadata($item, array('Dublin Core', 'Title'), array('no_escape'=>true)), ENT_QUOTES | ENT_XHTML, 'UTF-8'),
		html_entity_decode(metadata($item, array('Dublin Core', 'Description'), array('no_escape'=>true)), ENT_QUOTES | ENT_XHTML, 'UTF-8'),
		strip_formatting(metadata($item, array('Dublin Core', 'Language'))),
		metadata($item, array('Dublin Core', 'Creator')),
		tag_string($item, null, ','),
		(element_exists('Item Type Metadata', 'Path')) ? metadata($item, array('Item Type Metadata', 'Path')) : "",
		($preview && element_exists('Item Type Metadata', 'Path')) ? metadata($item, array('Item Type Metadata', 'Path')).".png" : ""
	);
	$export[] = array_combine($csv_header, $fields);
	echo package_manager_array_to_csv( $fields, ',', '"' );
	echo "\r\n";
	release_object($item);
}
fire_plugin_hook('admin_package_export_csv', array('data' => $export, 'view' => $this));