<?php
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );

function my_theme_register_required_plugins() {

	$plugins = array(
		array(
			'name'               => 'Gravity Forms',
			'slug'               => 'tgm-example-plugin',
			'source'             => dirname(__FILE__) . '/plugins/gravityforms.zip',
			'required'           => false,
		),
//		array(
//			'name'      => 'Breadcrumb NavXT',
//			'slug'      => 'breadcrumb-navxt',
//			'required'           => false,
//			'force_activation'   => false,
//		),
		array(
			'name'        => 'WordPress SEO by Yoast',
			'slug'        => 'wordpress-seo',
			'required'           => false,
		),
		array(
			'name'        => 'Classic Editor',
			'slug'        => 'classic-editor',
			'required'           => true,
			'force_activation'   => true,
		),
		array(
			'name'        => 'WP Sitemap Page',
			'slug'        => 'wp-sitemap-page',
			'required'           => false,
		),
		array(
			'name'        => '301 Redirects – Easy Redirect Manager',
			'slug'        => 'eps-301-redirects',
			'required'           => false,
		),
		array(
			'name'        => 'Insert Headers and Footers',
			'slug'        => 'insert-headers-and-footers',
			'required'           => true,
			'force_activation'   => true,
		),
		array(
			'name'        => 'Breeze – WordPress Cache Plugin',
			'slug'        => 'breeze',
			'required'           => false,
		),

	);
	$config = array(
		'id'           => 'tgmpa',
		'default_path' => '',
		'menu'         => 'tgmpa-install-plugins',
		'parent_slug'  => 'themes.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => true,
		'dismiss_msg'  => '',
		'is_automatic' => false,
		'message'      => '',

		/*
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
			'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
			/* translators: %s: plugin name. * /
			'installing'                      => __( 'Installing Plugin: %s', 'theme-slug' ),
			/* translators: %s: plugin name. * /
			'updating'                        => __( 'Updating Plugin: %s', 'theme-slug' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'theme-slug' ),
			'notice_can_install_required'     => _n_noop(
				/* translators: 1: plugin name(s). * /
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'theme-slug'
			),
			'notice_can_install_recommended'  => _n_noop(
				/* translators: 1: plugin name(s). * /
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'theme-slug'
			),
			'notice_ask_to_update'            => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'theme-slug'
			),
			'notice_ask_to_update_maybe'      => _n_noop(
				/* translators: 1: plugin name(s). * /
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'theme-slug'
			),
			'notice_can_activate_required'    => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'theme-slug'
			),
			'notice_can_activate_recommended' => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'theme-slug'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'theme-slug'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'theme-slug'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'theme-slug'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'theme-slug' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'theme-slug' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'theme-slug' ),
			/* translators: 1: plugin name. * /
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'theme-slug' ),
			/* translators: 1: plugin name. * /
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'theme-slug' ),
			/* translators: 1: dashboard link. * /
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'theme-slug' ),
			'dismiss'                         => __( 'Dismiss this notice', 'theme-slug' ),
			'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'theme-slug' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'theme-slug' ),

			'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
		),
		*/
	);

	tgmpa( $plugins, $config );
}
