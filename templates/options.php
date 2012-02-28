<div class="wrap">
	<div id="icon-ementor" class="icon32"><br /></div>
	<h2>Ustawienia eMentor.pl</h2>

	<form action="options.php" method="post">
		<?php settings_fields('wp-ementor-general'); ?>
		<?php do_settings_sections('wp-ementor-settings'); ?>
		<input type="submit" class="button-primary"  value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>


<!--	--><?php //foreach($this->plugin->getOptionGroups() as $name=>$descr):?>
<!--		<form action="options.php" method="post">-->
		<?php
//			settings_fields($name);
////			do_settings_sections('wp-ementor-settings');
//			do_settings_fields('wp-ementor-settings',$name);
//		?>
<!--		<input type="submit" class="button-primary"  value="--><?php //esc_attr_e('Save Changes'); ?><!--" />-->
<!--	</form>-->
<!--	--><?php //endforeach;?>

</div>

<?php

?>
