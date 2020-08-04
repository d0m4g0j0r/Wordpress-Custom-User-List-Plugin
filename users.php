<?

if(isset($_GET['users'])) {
    $file = plugin_dir_path( __FILE__ ) . "users_list.php";

    if(file_exists($file))
        require $file;

} elseif(isset($_GET['user_id'])) {
    $file = plugin_dir_path( __FILE__ ) . "user_details.php";

    if ( file_exists( $file ) )
        require $file;
} else {
    $file = plugin_dir_path( __FILE__ ) . "users_demo.php";

    if ( file_exists( $file ) )
        require $file;
}
