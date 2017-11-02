<?php
/**
 * BSF Companion
 *
 * @copyright Copyright 2017 id[+] Technology - All Rights Reserved
 */

require_once dirname(__FILE__) . '/helpers/BsfCompanionFunctions.php';
 
/**
 * Bsf Companion plugin.
 */
class BsfCompanionPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
		'install', 'uninstall', 'upgrade',
		'config_form', 'config',
		'admin_package_show_sidebar',
		'admin_package_export_json', // La commande n'est lancée que pour le json
        'admin_head',
		// 'admin_package_export_csv',
		// 'admin_package_export_yaml',
	);

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array();

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
		'bsf_companion_binary_path'  => "omeka-to-pkg" ,
        'bsf_companion_export_path'  => __DIR__ . DIRECTORY_SEPARATOR . "_export" . DIRECTORY_SEPARATOR,
        'bsf_companion_package_path' => __DIR__ . DIRECTORY_SEPARATOR . "_export" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR,
        'bsf_companion_url_prefix'   => "http://localhost/",
    );
	
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
		$this->_installOptions();
	}

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
		// Remove options
        $this->_uninstallOptions();
    }

    /**
     * Upgrade the plugin.
     *
     * @param array $args contains: 'old_version' and 'new_version'
     */
    public function hookUpgrade($args)
    {
		$oldVersion = $args['old_version'];
		$newVersion = $args['new_version'];

		if (version_compare($oldVersion, '1.0.1', '<')) {
			// add new options
			set_option('bsf_companion_binary_path', $this->_options['bsf_companion_binary_path']);
			set_option('bsf_companion_export_path', $this->_options['bsf_companion_export_path']);
		}
		if (version_compare($oldVersion, '1.0.2', '<')) {
			// add new options
			set_option('bsf_companion_package_path', $this->_options['bsf_companion_package_path']);
			set_option('bsf_companion_url_prefix', $this->_options['bsf_companion_url_prefix']);
		}
    }

    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    /**
     * Set the options from the config form input.
     */
    public function hookConfig($args)
    {
        set_option('bsf_companion_binary_path', (!empty($args['post']['bsf_companion_binary_path'])) ? $args['post']['bsf_companion_binary_path'] : $this->_options['bsf_companion_binary_path']);
        set_option('bsf_companion_export_path', (!empty($args['post']['bsf_companion_export_path'])) ? $args['post']['bsf_companion_export_path'] : $this->_options['bsf_companion_export_path']);
        set_option('bsf_companion_package_path', (!empty($args['post']['bsf_companion_package_path'])) ? $args['post']['bsf_companion_package_path'] : $this->_options['bsf_companion_package_path']);
        set_option('bsf_companion_url_prefix', (!empty($args['post']['bsf_companion_url_prefix'])) ? $args['post']['bsf_companion_url_prefix'] : $this->_options['bsf_companion_url_prefix']);
    }

    /**
     * Package sidebar display hook
     *
     */
    public function hookAdminPackageShowSidebar($args)
    {
		$view = $args['view'];

		$file_prefix = $view->package_manager_package->id.'_'.$view->package_manager_package->slug;
		$output_report = get_option('bsf_companion_export_path') . $file_prefix . '.txt';
		if(file_exists($output_report)){
			$trimmed_content = file($output_report, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$yaml_index = array_search("Yaml to use is: ", $trimmed_content);
			if($yaml_index){
				$meta = array_slice($trimmed_content, 0, $yaml_index);
				$yaml = implode("\r\n", array_slice($trimmed_content, $yaml_index + 1))."\r\n";
				$yaml_array = spyc_load($yaml);
				$content = current($yaml_array['all']);
				$content['_yaml_raw'] = substr($yaml, strpos($yaml, "all:\r\n")+strlen("all:\r\n"));
				$content['_result'] = array_pop($meta);
				$err = array();
				foreach($meta as $key=>$val){
					$err[] = (preg_match('/^[0-9]+$/', $key)) ? $val : $key." : ".$val;
				}
				$error = implode("<br/>", $err);
			}
			else{
				$content = false;
				$error = (is_array($trimmed_content)) ? implode("<br/>", $trimmed_content) : "";
				$error .= (!empty($error)) ? "<br/><br/>=> " . date ("d/m/Y H:i:s", filemtime($output_report)) : "-- en cours --"; 
			}
			$report = array("status"=>200, "content" => $content, "error" => $error);
		}
		else{
			$report = array("status"=>404, "content" => array('_result' => $output_report . " non exporté"), "error"=>false);
		}
		$view->assign('report' , $report);
		echo get_view()->showStatus($args['package'], $view);
    }
	
    /**
     * Export package to csv hook
     *
     */
    public function hookAdminPackageExportCsv($args)
    {
		$this->launchCustomCommand($args, "csv");
    }
	
    /**
     * Export package to json hook
     *
     */
    public function hookAdminPackageExportJson($args)
    {
		$this->launchCustomCommand($args, "json");
    }
	
    /**
     * Export package to yaml hook
     *
     */
    public function hookAdminPackageExportYaml($args)
    {
		$this->launchCustomCommand($args, "yaml");
    }

    /**
     * Add external js/css files
     */
    public function hookAdminHead()
    {
        queue_js_file('BsfCompanion');
        queue_css_file('BsfCompanion');
    }

	protected function launchCustomCommand($args, $type = false){
        $view = $args['view'];
        $data = $args['data'];
		switch($type){
			case 'csv':
				$fields = array_keys($data[0]);
				$content = package_manager_array_to_csv( $fields, ',', '"' )."\r\n";
				foreach($data as $val){
					$content .= package_manager_array_to_csv( $val, ',', '"' )."\r\n";
				}
			break;
			case 'yaml':
				$content = spyc_dump($data);
			break;
			case 'json':
				$content = json_encode($data, JSON_UNESCAPED_UNICODE);
			break;
			default:
				return;
			break;
		}
		
		$exe = get_option('bsf_companion_binary_path');
		$file_prefix = $view->package_manager_package->id.'_'.$view->package_manager_package->slug;
		$metadata_path = get_option('bsf_companion_export_path') . $file_prefix . '.' . $type;
		$package_file = $view->package_manager_package->slug . ".zip";
		$package_path = '"'.get_option('bsf_companion_package_path') . $package_file . '"';
		$output_report = '"'.get_option('bsf_companion_export_path') . $file_prefix . '.txt"';

		@file_put_contents($metadata_path, $content, LOCK_EX);
		
		$arguments = array(
			"package-id"  => $view->package_manager_package->slug,
			"name"        => str_replace('"', '\"', $view->package_manager_package->ideascube_name),
			"description" => str_replace('"', '\"', $view->package_manager_package->description),
			"language"    => str_replace('"', '\"', $view->package_manager_package->language),
			"url"         => get_option('bsf_companion_url_prefix') . $package_file,
		);
		foreach ($arguments as $arg=>$val){
			$options[] = $arg.' "'.$val.'"';
		}
		$args = '--'.implode(' --',$options);
		$launched = bsf_companion_bg_command($exe." ".$args." \"".$metadata_path."\" ".$package_path, $output_report);
		if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . "debug.dbg"))
			echo "\r\n=> AROK from BSF Companion for ".$type . " : " . $exe." ".$metadata_path."\r\n".$launched;
	}
}
