<?php
	$plugins_list = array(

		array(
			'title' 	=> 'Extra Notes',
			'type' 		=> 'section',
			'id' 		=> 'addons',
		),

		array(
			'title' 	=> 'MoceanAPI',
			'dashicon'  => 'dashicons-admin-site-alt2',
			'desc' 		=> 'Get your API credentials for this plugin right now at Moceanapi.com by just signing up an account. Free trial credits provided too!',
			'visit_us' 		=> 'https://moceanapi.com/',
		),

	)
?>

<a class="mo-sidebar-toggle">Hide</a>
<div class="mo-other-plugins">
	<ul class="mo-op-list">
		<?php foreach($plugins_list as $plugin): ?>

			<?php if( isset( $plugin['type'] ) && $plugin['type'] === 'section' ): ?>
				<li class="mo-sidebar-head section-<?php echo $plugin['id']; ?>"><?php echo $plugin['title']; ?></li>
			<?php continue; endif; ?>

				<li class="mo-op-plugin">
					<div class="mo-op-plugin-icon">
						<span class="dashicons <?php echo $plugin['dashicon']; ?>"></span>
					</div>

					<div class="mo-op-plugin-details">
						<span class="mo-op-plugin-head"><?php echo $plugin['title']; ?></span>
						<span class="mo-op-plugin-about"><?php echo $plugin['desc']; ?></span>
						<a href="<?php echo $plugin['visit_us']; ?>">Visit Us Now!</a>
						<?php if(isset( $plugin['download'] )): ?>
							<a href="<?php echo $plugin['download']; ?>">Download</a>
						<?php endif; ?>
						<?php if( isset( $plugin['pluginpage'] ) ): ?>
							<a href="<?php echo $plugin['pluginpage'] ?>">Plugin Page</a>
						<?php endif; ?> 
					</div>
				</li>
		<?php endforeach; ?>
	</ul>
</div>