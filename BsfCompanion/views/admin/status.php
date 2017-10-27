<?php if($package):?>
    <div class="package_status panel">
        <h4>BSF Companion</h4>
        <div>
			<p>Package id : <?php echo $package->id;?></p>
			<hr/>
			<?php if(isset($view->report['error']) && !empty($view->report['error'])):?> 
				<div class="plugins browse">
                    <p class="error cannotload">
                        <div id="bsfcompanion_error_report" style="display:none">
                            <pre style="font-size:small; text-align: left; overflow: auto;"><?php echo $view->report['error'];?></pre>
                        </div>
                        <div class="bsfcompanion_error_message">
                            Export failed ! <a class="bsfcompanion_show_errors" href="#">Show errors</a>
                        </div>
                    </p>
				</div>
			<?php endif;?>
			<?php if(isset($view->report['content']) && !empty($view->report['content'])):?>
				<?php $class = ($view->report['status']==200) ? "success" : "";?>
 				<h5 class="<?php echo $class;?>"><?php echo $view->report['content']['_result'];?></h5>
				<?php 
					$allowed_var = array("version","url","size","sha256sum");
					$display_data = array_intersect_key($view->report['content'], array_flip($allowed_var));
					if(is_array($display_data) && count($display_data)>0){
						echo"<ul>";
						foreach($display_data as $key=>$var){
							if($key=='url') $var = "<a href='".$var."' target='_blank'>".$var."</a>";
							echo "<li><strong>" . $key . "</strong> : " . $var . "</li>";
						}
						echo"</ul>";
					}
				?>
                <?php if(isset($view->report['content']['_yaml_raw']) && !empty($view->report['content']['_yaml_raw'])):?>
                    <button class="right" onclick="copyTagContentToClipboard('plugin_bsfcompanion_export_yaml_raw')">Copy YAML to clipboard</button>
                    <textarea id="plugin_bsfcompanion_export_yaml_raw"><?php echo $view->report['content']['_yaml_raw']; ?></textarea>
			    <?php endif;?>
			<?php endif;?>
        </div>
    </div>	
<?php endif;?>