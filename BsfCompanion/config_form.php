<?php $view = get_view(); ?>
<div class='field'>
	<div class="three columns alpha">
		<label for="bsf_companion_binary_path"><?php echo __("Localisation du binaire"); ?></label>
	</div>
	<div class='inputs four columns omega'>
		<p class='explanation'><?php echo __("Renseignez le chemin complet vers l'exécutable ou simplement le nom du binaire s'il est présent dans votre PATH."); ?></p>
		<div class='input-block'>
			<?php echo $view->formText('bsf_companion_binary_path', get_option('bsf_companion_binary_path'), array('id' => 'bsf_companion_binary_path')); ?>
		</div>        
	</div>
</div>
<div class='field'>
	<div class="three columns alpha">
		<label for="bsf_companion_export_path"><?php echo __("Localisation du dossier de travail"); ?></label>
	</div>
	<div class='inputs four columns omega'>
		<p class='explanation'><?php echo __("Renseignez le chemin complet vers le dossier où seront exportés les données et les rapports. Attention, la variable <strong>'path'</strong> du paquet doit être relative à cet emplacement."); ?></p>
		<div class='input-block'>
			<?php echo $view->formText('bsf_companion_export_path', get_option('bsf_companion_export_path'), array('id' => 'bsf_companion_export_path')); ?>
		</div>        
	</div>
</div>
<div class='field'>
	<div class="three columns alpha">
		<label for="bsf_companion_package_path"><?php echo __("Localisation du dossier d'export des Packages"); ?></label>
	</div>
	<div class='inputs four columns omega'>
		<p class='explanation'><?php echo __("Renseignez le chemin complet vers le dossier où seront exportés les Packages constitués."); ?></p>
		<div class='input-block'>
			<?php echo $view->formText('bsf_companion_package_path', get_option('bsf_companion_package_path'), array('id' => 'bsf_companion_package_path')); ?>
		</div>        
	</div>
</div>
<div class='field'>
	<div class="three columns alpha">
		<label for="bsf_companion_url_prefix"><?php echo __("URL canonique des Packages"); ?></label>
	</div>
	<div class='inputs four columns omega'>
		<p class='explanation'><?php echo __("Renseignez l'url canonique où les Packages seront accessibles."); ?></p>
		<div class='input-block'>
			<?php echo $view->formText('bsf_companion_url_prefix', get_option('bsf_companion_url_prefix'), array('id' => 'bsf_companion_url_prefix')); ?>
		</div>        
	</div>
</div>