<?php
/**
 * Plugin Name: Dominant Core - Custom User List
 * Description: Custom User List
 * Plugin URI:  https://dominant-core.hr
 * Version:     2020.07.
 * Author:      Domagoj Rogošić
 * Author URI:  https://dominant-core.hr
 * Licence:     MIT
 * License URI: http://opensource.org/licenses/MIT
 */

add_action( 'admin_menu', array ( 'Custom_User_List', 'admin_menu' ) );

class Custom_User_List
{
	public static function admin_menu()
	{
		$main = add_menu_page(
			'Kupci',
			'Kupci',
			'manage_options',
			'kupci',
			array ( __CLASS__, 'render_users_include' ),
            'dashicons-admin-users',
            '57'
		);

		$sub = add_submenu_page(
			'kupci',
			'Maloprodajni',
			'Maloprodajni',
			'manage_options',
			'kupci&role=customer_maloprodaja&order=asc&s',
            array ( __CLASS__, 'render_maloprodaja' )
		);

        $sub = add_submenu_page(
            'kupci',
            'Veleprodajni',
            'Veleprodajni',
            'manage_options',
            'kupci&role=customer_veleprodaja&order=asc&s',
            array ( __CLASS__, 'render_users_include' )
        );

        $sub = add_submenu_page(
            'kupci',
            'Partneri',
            'Partneri',
            'manage_options',
            'kupci&role=customer_partner&order=asc&s',
            array ( __CLASS__, 'render_users_include' )
        );

		foreach ( array ( $main, $sub ) as $slug )
		{
			add_action(
				"admin_print_styles-$slug",
				array ( __CLASS__, 'enqueue_style' )
			);
			add_action(
				"admin_print_scripts-$slug",
				array ( __CLASS__, 'enqueue_script' )
			);
		}
    }

	public static function render_users_include()
	{
		$file = plugin_dir_path( __FILE__ ) . "users.php";

		if ( file_exists( $file ) )
			require $file;
	}

	public static function enqueue_style()
	{
		wp_register_style(
			'dominant_users',
			plugins_url( 'dominant_users.css', __FILE__ )
		);
		wp_enqueue_style( 'dominant_users' );
	}

	public static function enqueue_script()
	{
		wp_register_script(
			'dominant_users_js',
			plugins_url( 'dominant_users.js', __FILE__ ),
			array(),
			FALSE,
			TRUE
		);
		wp_enqueue_script( 'dominant_users_js' );
	}
}
