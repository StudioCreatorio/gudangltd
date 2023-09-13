<?php
/**
 * Automatic.css Settings_Page UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin;

use Automatic_CSS\Admin\UI_Elements\Color;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Model\Config\Variables;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Admin\UI_Modules\Tab;
use Automatic_CSS\Exceptions\Invalid_Form_Values;
use Automatic_CSS\Helpers\Locale;
use Automatic_CSS\Model\Config\UI;
use Automatic_CSS\Traits\Singleton;

/**
 * Settings_Page UI class.
 */
class Settings_Page {

	use Singleton;

	/**
	 * Capability needed to operate the plugin
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * Initialize the Settings_Page class
	 *
	 * @return Settings_Page
	 */
	public function init() {
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 500 );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_filter( 'automaticcss_admin_stylesheets', array( $this, 'enqueue_admin_styles' ) );
			add_filter( 'automaticcss_admin_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'wp_ajax_automaticcss_save_settings', array( $this, 'save_settings' ) );
		}
		return $this;
	}

	/**
	 * Render the plugin's settings page
	 *
	 * @return void
	 */
	public function render() {
		Locale::fix_locale();
		$this->set_render_hooks();
		$tabs = ( new UI() )->load();
		$model = Database_Settings::get_instance();
		$values = $model->get_vars();
		?>
		<div class="acss-wrapper">
			<div class="acss-form-wrapper">
				<form id="acss-settings-form" method="post" novalidate="true">
					<div class="acss-settings-wrapper">
						<div class="acss-nav-wrapper">
							<h2 class="acss-logo-wrapper">
								<img class="acss-logo" src="<?php echo esc_url( ACSS_ASSETS_URL . '/img/logo-white.svg' ); ?>" alt="Automatic CSS" />
							</h2>
							<ul class="acss-nav">
								<?php foreach ( $tabs as $tab_id => $tab_options ) : ?>
									<li><a href="#acss-tab-<?php echo esc_attr( $tab_id ); ?>"><?php echo esc_html( $tab_options['title'] ); ?></a></li>
								<?php endforeach; ?>
							</ul> <!-- .acss-nav -->
							<?php submit_button(); ?>
							<div class="acss-settings__version">Version <?php echo esc_html( \Automatic_CSS\Plugin::get_plugin_version() ); ?></div>
						</div> <!-- .acss-nav-wrapper -->
						<div class="acss-tabs-wrapper">
							<div id="acss-settings__response-message" class="acss-settings__message"></div>
							<?php foreach ( $tabs as $tab_id => $tab_options ) : ?>
								<?php Tab::render( $tab_id, $tab_options, $values ); ?>
							<?php endforeach; ?>
						</div> <!-- .acss-tabs-wrapper -->
					</div> <!-- .acss-settings-wrapper -->
				</form> <!-- #acss-settings-form -->
			</div> <!-- .acss-form-wrapper -->
			<div class="acss-loading-wrapper hidden">
				<div class="acss-loading"></div>
			</div>
		</div> <!-- .acss-wrapper -->
		<?php
		Locale::restore_locale();
	}

	/**
	 * Save the plugin's settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		$timer = new Timer();
		Logger::log( sprintf( '%s: starting', __METHOD__ ) );
		if ( ! check_ajax_referer( 'automatic_css_save_settings', 'nonce', false ) ) {
			Logger::log( sprintf( '%s: failed nonce check - quitting early', __METHOD__ ), Logger::LOG_LEVEL_ERROR );
			wp_send_json_error( 'Failed nonce check.', 400 );
		}
		if ( ! current_user_can( $this->capability ) ) {
			Logger::log( sprintf( '%s: capability check failed - quitting early', __METHOD__ ), Logger::LOG_LEVEL_ERROR );
			wp_send_json_error( 'You cannot save these settings.', 403 );
		}
		// Sanitize and validate input data.
		$form_variables = json_decode( filter_input( INPUT_POST, 'variables' ), true );
		if ( ! is_array( $form_variables ) || empty( $form_variables ) ) {
			Logger::log( sprintf( '%s: did not receive form variables in the expected format - quitting early', __METHOD__ ), Logger::LOG_LEVEL_ERROR );
			wp_send_json_error( 'Received empty settings or in an unexpected format.', 400 );
		}
		Logger::log( sprintf( "%s: received these form variables:\n%s", __METHOD__, print_r( $form_variables, true ) ), Logger::LOG_LEVEL_NOTICE );
		// Save settings.
		try {
			Locale::fix_locale();
			$model = Database_Settings::get_instance();
			$save_info = $model->save_vars( $form_variables );
			if ( true === $save_info['has_changed'] ) {
				$time = $timer->get_time();
				$generated_files = $save_info['generated_files_number'];
				// Settings were saved and CSS regenerated.
				Logger::log( sprintf( '%s: settings saved and %d CSS files regenerated - done in %s seconds', __METHOD__, $generated_files, $time ) );
				Locale::restore_locale();
				wp_send_json_success( sprintf( 'Settings updated and %d CSS file(s) generated correctly in %s seconds.', $generated_files, $time ) );
			} else {
				$time = $timer->get_time();
				// Settings were not saved because no changed was detected.
				// Please note: this is not an error! Those are thrown through exceptions.
				Logger::log( sprintf( '%s: no changes detected, did not save or regenerate CSS files - done in %s seconds', __METHOD__, $time ) );
				Locale::restore_locale();
				wp_send_json_success( 'No changes detected. No updates made to stylesheets.' );
			}
		} catch ( Invalid_Form_Values $e ) {
			$error_message = $e->getMessage();
			$errors = $e->get_errors();
			Locale::restore_locale();
			wp_send_json_error(
				array(
					'message' => $error_message,
					'errors' => $errors
				),
				422 // Unprocessable Entity.
			);
		} catch ( \Exception $e ) {
			$error_message = $e->getMessage();
			Logger::log( sprintf( '%s: caught this error: %s', __METHOD__, $error_message ), Logger::LOG_LEVEL_ERROR );
			Logger::log( debug_backtrace(), Logger::LOG_LEVEL_ERROR );
			Locale::restore_locale();
			wp_send_json_error( $error_message, 500 );
		}
	}

	/**
	 * Enqueue admin styles
	 *
	 * @param array $styles The existing styles.
	 * @return array
	 */
	public function enqueue_admin_styles( $styles ) {
		$styles['automaticcss-coloris'] = array(
			'filename'   => '/css/coloris.min.css',
			'dependency' => array(),
			'hook'       => array( 'toplevel_page_automatic-css', 'automatic-css_page_automatic-css-updater', 'automatic-css_page_automatic-css-import-export' ),
		);
		$styles['automaticcss-admin'] = array(
			'filename'   => '/css/automatic-admin.css',
			'dependency' => array( 'automaticcss-coloris' ),
			'hook'       => array( 'toplevel_page_automatic-css', 'automatic-css_page_automatic-css-updater', 'automatic-css_page_automatic-css-import-export' ),
		);
		return $styles;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param array $scripts The existing scripts.
	 * @return array
	 */
	public function enqueue_admin_scripts( $scripts ) {
		$scripts['automaticcss-coloris'] = array(
			'filename'   => '/js/coloris.min.js',
			'dependency' => array(),
			'hook'       => 'toplevel_page_automatic-css',
		);
		$tab_ids = array();
		foreach ( ( new UI() )->load() as $tab_id => $tab_options ) {
			$tab_ids[] = 'acss-tab-' . $tab_id;
		}
		$scripts['automaticcss-admin'] = array(
			'filename'   => '/js/automatic-admin.js',
			'dependency' => array( 'automaticcss-coloris' ),
			'hook'       => 'toplevel_page_automatic-css',
			'localize'   => array(
				'name' => 'automatic_css_settings',
				'options' => array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'automatic_css_save_settings' ),
					'assets_url' => ACSS_ASSETS_URL,
					'variables' => ( new Variables() )->load(),
					'tab_ids' => $tab_ids
				)
			),
		);
		return $scripts;
	}

	/**
	 * Add admin bar item
	 *
	 * @param \WP_Admin_Bar $admin_bar The Admin Bar object.
	 * @return void
	 */
	public function add_admin_bar_item( \WP_Admin_Bar $admin_bar ) {
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}
		$model = Database_Settings::get_instance();
		$admin_bar_option = $model->get_var( 'admin-bar-enabled' );
		$admin_bar_enabled = null === $admin_bar_option ? true : ( 'on' === $admin_bar_option ? true : false );
		if ( ! $admin_bar_enabled ) {
			return;
		}
		$admin_bar->add_menu(
			array(
				'id'    => 'automatic-css-admin-bar',
				'parent' => null,
				'group'  => null,
				'title' => 'Automatic CSS', // you can use img tag with image link. it will show the image icon Instead of the title.
				'href'  => admin_url( 'admin.php?page=automatic-css' )
			)
		);
	}

	/**
	 * Add the plugin's settings page to the menu
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		$model = Database_Settings::get_instance();
		$admin_position_option = $model->get_var( 'admin-link-position' );
		$admin_position = null === $admin_position_option ? 90 : $admin_position_option;
		add_menu_page(
			'Automatic CSS', // page_title.
			'Automatic CSS', // menu_title.
			$this->capability, // capability.
			'automatic-css', // menu_slug.
			array( $this, 'render' ), // function.
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA5NC4wNSA3MS40MyI+CiAgPGRlZnM+CiAgICA8c3R5bGU+LmNscy0xe2ZpbGw6I2ZmZjt9PC9zdHlsZT4KICA8L2RlZnM+CiAgPGcgaWQ9IkxheWVyXzIiIGRhdGEtbmFtZT0iTGF5ZXIgMiI+CiAgICA8ZyBpZD0iTGF5ZXJfMS0yIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgICA8cG9seWdvbiBjbGFzcz0iY2xzLTEiIHBvaW50cz0iOTQuMDUgNDguNjMgMTkuNTcgNTQuMiA0MS4yNCAxNi42NiA1OS4yMiA0Ny44IDY4LjQ0IDQ3LjExIDQxLjI0IDAgMCA3MS40MyA2My45MiA1NS45NCA2Ny43OCA2Mi42NiAzMy4wNyA3MS40MyA4Mi40OCA3MS40MyA3Mi4zNiA1My45IDk0LjA1IDQ4LjYzIj48L3BvbHlnb24+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4K', // icon_url.
			$admin_position // position.
		);
	}

	/**
	 * Allow components to setup their hooks before rendering the page.
	 *
	 * @return void
	 */
	private function set_render_hooks() {
		// Allow Color to hook into automaticcss_render_options_default.
		Color::hook_in_automaticcss_render_options_default();
	}

}
