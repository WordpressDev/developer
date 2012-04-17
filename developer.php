<?php /*

**************************************************************************

Plugin Name:  Developer
Plugin URI:   http://wordpress.org/extend/plugins/developer/
Description:  The first stop for every WordPress developer
Version:      1.0.0
Author:       Automattic
Author URI:   http://automattic.com/wordpress-plugins/
License:      GPLv2 or later

Text Domain:  a8c-developer
Domain Path:  /languages/

**************************************************************************/

class Automattic_Developer {

	public $settings                   = array();
	public $default_settings           = array();

	// Using "private" for read-only functionality. See __get().
	private $option_name               = 'a8c_developer';
	private $settings_page_slug        = 'a8c_developer';

	function __construct() {
		add_action( 'init',           array( &$this, 'init' ) );
		add_action( 'admin_init',     array( &$this, 'admin_init' ) );

		add_action( 'admin_menu',     array( &$this, 'register_settings_page' ) );

		add_action( 'admin_bar_menu', array( &$this, 'add_node_to_admin_bar' ) );
	}

	// Allows private variables to be read. Basically implements read-only variables.
	function __get( $var ) {
		return ( isset( $this->$var ) ) ? $this->$var : null;
	}

	public function init() {
		$this->default_settings = array(
			'project_type' => false,
		);

		//delete_option( $this->option_name );

		$this->settings = wp_parse_args( (array) get_option( $this->option_name ), $this->default_settings );
	}

	public function admin_init() {
		register_setting( $this->option_name, $this->option_name, array( &$this, 'settings_validate' ) );

		wp_register_style( 'a8c-developer', plugins_url( 'developer.css', __FILE__ ), array(), '1.0.0' );

		if ( ! get_option( $this->option_name ) ) {
			add_action( 'admin_enqueue_scripts', array( &$this, 'load_colorbox' ) );
			add_action( 'admin_footer', array( &$this, 'output_setup_box_html' ) );
		}
	}

	public function register_settings_page() {
		add_options_page( esc_html__( 'Automattic Developer Helper', 'a8c-developer' ), esc_html__( 'Developer', 'a8c-developer' ), 'manage_options', $this->settings_page_slug, array( &$this, 'settings_page' ) );
	}

	public function add_node_to_admin_bar( $wp_admin_bar ) {
		$wp_admin_bar->add_node( array(
			'id'     => $this->settings_page_slug,
			'title'  => esc_html__( 'Developer', 'a8c-developer' ),
			'parent' => 'top-secondary', // Off on the right side
			'href'   => admin_url( 'options-general.php?page=' . $this->settings_page_slug ),
			'meta'   => array(
				'title' => esc_html__( 'View the Automattic Developer Helper settings and status page', 'a8c-developer' ),
			),
		) );
	}

	public function load_colorbox() {
		wp_enqueue_script( 'colorbox', plugins_url( 'colorbox/jquery.colorbox-min.js', __FILE__ ), array( 'jquery' ), '1.3.19' );
		wp_enqueue_style( 'a8c-developer-colorbox', plugins_url( 'colorbox/colorbox.css', __FILE__ ), array(), '1.3.19' );
		wp_enqueue_style( 'a8c-developer' );
	}

	public function output_setup_box_html() { ?>

		<div style="display:none">
			<div id="a8c-developer-setup-dialog-step-1" class="a8c-developer-dialog">
				<p><em>TODO: Copy+formatting+i18n</em></p>

				<strong>Thanks for installing Automattic's Developer helper plugin!</strong>

				<p>Before we begin, what type of website are you developing?</p>

				<form id="a8c-developer-setup-dialog-step-1-form" action="#somewhere">
					<!-- todo: make not crap -->
					<p><label><input type="radio" name="a8c_developer_project_type" value="wporg" checked="checked" /> A normal WordPress.org website</label></p>
					<p><label><input type="radio" name="a8c_developer_project_type" value="wpcom-vip" /> A website hosted on WordPress.com VIP</label></p>

					<?php submit_button( null, 'primary', 'a8c-developer-setup-dialog-step-1-submit' ); ?>
				</form>
			</div>
			<div id="a8c-developer-setup-dialog-step-2" class="a8c-developer-dialog">

			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				$.colorbox({
					inline: true,
					href: '#a8c-developer-setup-dialog-step-1',
					title: 'Developer: Plugin Setup',
					innerWidth: 500,
					transition: 'none', // No animation to show it

					internetexplorer: 'sucks' // TODO: Remove this temporary item preventing me from breaking IE with a trailing comma
				});

				$('#a8c-developer-setup-dialog-step-1-form').submit(function(e){
					$('#a8c-developer-setup-dialog-step-1-submit').val('Saving...');

					// Save form via AJAX here, then load step 2

					e.preventDefault();
				});
			});
		</script>

<?php
	}

	public function settings_page() {
		add_settings_section( 'a8c_developer_main', esc_html__( 'Main Configuration', 'a8c-developer' ), '__return_false', $this->settings_page_slug . '_settings' );
		add_settings_field( 'a8c_developer_project_type', esc_html__( 'Project Type', 'a8c-developer' ), array( &$this, 'settings_field_select' ), $this->settings_page_slug . '_settings', 'a8c_developer_main', array(
			'name'        => 'project_type',
			'description' => __( 'Are you developing plugins and themes for <a href="http://wordpress.org/">self-hosted blogs</a> or are you working on a <a href="http://vip.wordpess.com/">WordPress.com VIP</a> project?', 'a8c-developer' ),
			'options'     => array(
				'wporg'     => esc_html__( 'WordPress.org', 'a8c-developer' ),
				'wpcom-vip' => esc_html__( 'WordPress.com VIP', 'a8c-developer' ),
			),
		) );


		add_settings_section( 'a8c_developer_plugins', esc_html__( 'Plugins', 'a8c-developer' ), array( &$this, 'settings_section_plugins' ), $this->settings_page_slug . '_status' );

		$recommended_plugins = array(
			'debug-bar' => array(
				'name'   => esc_html__( 'Debug Bar', 'a8c-developer' ),
				'active' => class_exists( 'Debug_Bar' ),
			),
			'debug-bar-cron' => array(
				'name'   => esc_html__( 'Debug Bar Cron', 'a8c-developer' ),
				'active' => function_exists( 'zt_add_debug_bar_cron_panel' ),
			),
			'log-deprecated-notices' => array(
				'name'   => esc_html__( 'Log Deprecated Notices', 'a8c-developer' ),
				'active' => class_exists( 'Deprecated_Log' ),
			),
			'foobar' => array(
				'name'     => 'Dummy Test Plugin',
				'active'   => false,
				'filename' => 'blah.php',
			),
			// TODO: Add more?
		);

		if ( 'wpcom-vip' == $this->settings['project_type'] ) {
			/*
			$recommended_plugins['jetpack'] = array(
				'name'   => esc_html__( 'Jetpack', 'a8c-developer' ),
				'active' => class_exists( 'Jetpack' ),
			);
			*/
			$recommended_plugins['grunion-contact-form'] = array(
				'name'   => esc_html__( 'Grunion Contact Form', 'a8c-developer' ),
				'active' => defined( 'GRUNION_PLUGIN_DIR' ),
			);
			$recommended_plugins['polldaddy'] = array(
				'name'   => esc_html__( 'Polldaddy Polls & Ratings', 'a8c-developer' ),
				'active' => class_exists( 'WP_Polldaddy' ),
			);
		}

		foreach ( $recommended_plugins as $plugin_slug => $plugin_details ) {
			$plugin_details = array_merge( array( 'slug' => $plugin_slug ), $plugin_details );
			add_settings_field( 'a8c_developer_plugin_' . $plugin_slug, $plugin_details['name'], array( &$this, 'settings_field_plugin' ), $this->settings_page_slug . '_status', 'a8c_developer_plugins', $plugin_details );
		}


		add_settings_section( 'a8c_developer_constants', esc_html__( 'Constants', 'a8c-developer' ), array( &$this, 'settings_section_constants' ), $this->settings_page_slug . '_status' );

		$recommended_constants = array(
			'WP_DEBUG'    => __( 'Enables <a href="http://codex.wordpress.org/Debugging_in_WordPress" target="_blank">debug mode</a> which helps identify and resolve issues', 'a8c-developer' ),
			'SAVEQUERIES' => esc_html__( 'Logs database queries to an array so you can review them. The Debug Bar plugin will list out database queries if you set this constant.', 'a8c-developer' ),
			'FOOBAR'      => 'A dummy constant for showing a missing constant',
		);

		foreach ( $recommended_constants as $constant => $description ) {
			add_settings_field( 'a8c_developer_constant_' . $constant, $constant, array( &$this, 'settings_field_constant' ), $this->settings_page_slug . '_status', 'a8c_developer_constants', array(
				'constant'    => $constant,
				'description' => $description,
			) );
		}


		add_settings_section( 'a8c_developer_settings', esc_html__( 'Settings', 'a8c-developer' ), array( &$this, 'settings_section_settings' ), $this->settings_page_slug . '_status' );
		add_settings_field( 'a8c_developer_setting_permalink_structure', esc_html__( 'Pretty Permalinks', 'a8c-developer' ), array( &$this, 'settings_field_setting_permalink_structure' ), $this->settings_page_slug . '_status', 'a8c_developer_settings' );



		# Add more sections and fields here as needed
?>

		<div class="wrap">

		<?php screen_icon(); // TODO: Better icon? ?>

		<h2><?php esc_html_e( 'Automattic Developer Helper', 'vehicle-info' ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( $this->option_name ); // matches value from register_setting() ?>

			<?php do_settings_sections( $this->settings_page_slug . '_settings' ); // matches values from add_settings_section/field() ?>

			<?php submit_button(); ?>

			<?php do_settings_sections( $this->settings_page_slug . '_status' ); ?>
		</form>

		<h3 style="margin-top:150px">Current Settings Value:</h3>
		<?php var_dump( get_option( $this->option_name ) ); ?>

		</div>
<?php
	}

	public function settings_field_select( $args ) {
		if ( empty( $args['name'] ) || ! is_array( $args['options'] ) )
			return false;

		$selected = ( isset( $this->settings[ $args['name'] ] ) ) ? $this->settings[ $args['name'] ] : '';

		echo '<select name="a8c_developer[' . esc_attr( $args['name'] ) . ']">';

		foreach ( (array) $args['options'] as $value => $label )
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $value, $selected, false ) . '>' . $label . '</option>';

		echo '</select>';

		if ( ! empty( $args['description'] ) )
			echo ' <span class="description">' . $args['description'] . '</span>';
	}

	public function settings_section_plugins() {
		echo '<p>' . esc_html__( 'We recommend you have the following plugins installed:', 'a8c-developer' ) . '</p>';
	}

	// TODO: Make this not shitty
	public function settings_field_plugin( $args ) {
		if ( $args['active'] ) {
			echo '<span style="font-weight:bold;color:green;">' . esc_html__( 'ACTIVE', 'a8c-developer' ) . '</span>';
		} else {
			$filename = ( ! empty( $args['filename'] ) ) ? $args['filename'] : $args['slug'] . '.php';

			if ( file_exists( WP_PLUGIN_DIR . '/' . $args['slug'] . '/' . $filename ) ) {
				// Installed but not activated
				$rel_path = $args['slug'] . '/' . $filename;
				echo '<a style="font-weight:bold;color:darkred;" href="' . esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $rel_path ), 'activate-plugin_' . $rel_path ) ) . '" title="' . esc_attr__( 'Click here to activate', 'a8c-developer' ) . '">' . esc_html__( 'INACTIVE', 'a8c-developer' ) . '</a>';
			} else {
				// Needs to be installed
				echo '<a style="font-weight:bold;color:darkred;" href="' . esc_url( wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=' . $args['slug'] ), 'install-plugin_' . $args['slug'] ) ) . '" title="' . esc_attr__( 'Click here to install', 'a8c-developer' ) . '">' . esc_html__( 'NOT INSTALLED', 'a8c-developer' ) . '</a>';
			}
		}
	}

	public function settings_section_constants() {
		echo '<p>' . __( 'We recommend you set the following constants to <code>true</code> in your <code>wp-config.php</code> file. <a href="http://codex.wordpress.org/Editing_wp-config.php" target="_blank">Need help?</a>', 'a8c-developer' ) . '</p>';
	}

	// TODO: Make this not shitty
	public function settings_field_constant( $args ) {
		if ( defined( $args['constant'] ) && constant( $args['constant'] ) ) {
			echo '<span style="font-weight:bold;color:green;">' . esc_html__( 'SET', 'a8c-developer' ) . '</span>';
		} else {
			echo '<span style="font-weight:bold;color:darkred;">' . esc_html__( 'NOT SET', 'a8c-developer' ) . '</span>';
		}

		if ( ! empty( $args['description'] ) )
			echo '<br /><span class="description">' . $args['description'] . '</span>';
	}


	public function settings_section_settings() {
		echo '<p>' . esc_html__( 'We recommend the following settings and configurations.', 'a8c-developer' ) . '</p>';
	}

	public function settings_field_setting_permalink_structure() {
		if ( get_option( 'permalink_structure' ) ) {
			echo '<span style="font-weight:bold;color:green;">' . esc_html__( 'ENABLED', 'a8c-developer' ) . '</span>';
		} else {
			echo '<a style="font-weight:bold;color:darkred;" href="' . admin_url( 'options-permalink.php' ) . '">' . esc_html__( 'DISABLED', 'a8c-developer' ) . '</a> ' . __( '<a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">Need help?</a>', 'a8c-developer' );
		}
	}

	public function settings_validate( $raw_settings ) {
		$settings = array();

		$settings['project_type'] = ( ! empty( $raw_settings['project_type'] ) && 'wpcom-vip' == $raw_settings['project_type'] ) ? 'wpcom-vip' : 'wporg';

		return $settings;
	}
}

$Automattic_Developer = new Automattic_Developer();

?>