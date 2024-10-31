<?php
    if(oelwm_fs()->is_tracking_allowed()) {
        ?>
            <!-- Yandex.Metrika counter -->
            <script type="text/javascript" >
                (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
                (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

                ym(89907625, "init", {
                        clickmap:true,
                        trackLinks:true,
                        accurateTrackBounce:true,
                        webvisor:true
                });
            </script>
            <noscript><div><img src="https://mc.yandex.ru/watch/88073586" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
            <!-- /Yandex.Metrika counter -->

        <?php
    }
?>

<div class="mo-tabs">
	<?php

	$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'phone';

	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $tabs as $tab_key => $tab_caption ) {
		$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
		echo '<a class="nav-tab ' . $active . '" href="?page=oelm&tab=' . $tab_key . '">' . $tab_caption . '</a>';
	}
	echo '</h2>';

	if( $current_tab === 'balance_tab_boys' ){
		$option_name = 'balance_tab';
	}
	else{
		$option_name = 'oelm-'.$current_tab.'-options';
	}

	?>
</div>


<div class="mo-container">
	<div class="mo-main">

		<?php do_action( 'oelm_admin_settings_start' ); ?>

		<?php if( $option_name === 'balance_tab' ): ?>

			<?php  include(plugin_dir_path(__FILE__).'oelm-balance-info.php'); ?>

		<?php else: ?>


			<form method="post" action="options.php">
				<?php

				settings_fields( $option_name ); // Output nonces

				do_settings_sections( $option_name ); // Display Sections & settings

				submit_button( 'Save Settings' );	// Display Save Button
				?>

			</form>

		<?php endif; ?>

	</div>

	<div class="mo-sidebar">
		<?php include oelm_PATH.'/admin/templates/sidebar.php'; ?>
	</div>
</div>

