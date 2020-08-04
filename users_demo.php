<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$search_term = sanitize_text_field(stripslashes($_GET['s']));
$page = isset($_GET['p']) ? $_GET['p'] : 1;
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
if(isset($_GET['orderby']) AND !empty($_GET['orderby'])) { $orderByUser = $_GET['orderby']; } else { $orderByUser = 'first_name'; }
if(isset($_GET['orderby']) AND !empty($_GET['orderby'])) { $orderByMeta = $_GET['orderby']; } else { $orderByMeta = 'billing_company'; }
$role = isset($_GET['role']) ? $_GET['role'] : '';

$args_users  = array(
    'role' => $role,
    'search' => '*' . $search_term . '*',
);
$wp_user_count_query_1 = new WP_User_Query($args_users);

$args_user_meta = array(
    'role'      => $role,
    'meta_query'    => array(
        'relation'  => 'OR',
        array(
            'key'     => 'billing_company',
            'value'   => $search_term,
            'compare' => 'LIKE'
        ),
        array(
            'key'     => 'billing_oib',
            'value'   => $search_term,
            'compare' => 'LIKE'
        ),
    ),
);
$wp_user_count_query_2 = new WP_User_Query($args_user_meta);

$user_count_query = new WP_User_Query();
$user_count_query->results = array_unique( array_merge( $wp_user_count_query_1->results, $wp_user_count_query_2->results ), SORT_REGULAR );

//$user_count_query = new WP_User_Query($count_args);
$user_count = $user_count_query->get_results();

// count the number of users found in the query
$total_users = $user_count ? count($user_count) : 1;

// how many users to show per page
$users_per_page = 30;

// calculate the total number of pages.
$total_pages = 1;
$offset      = $users_per_page * ($page - 1);
$total_pages = ceil($total_users / $users_per_page);

// main user query
$args_users  = array(
    'role' => $role,
    'meta_key' => $orderByUser,
    'orderby' => 'meta_value',
    'order' => $order,
    'search' => '*' . $search_term . '*',
    'number'    => $users_per_page,
    'offset'    => $offset // skip the number of users that we have per page
);
$wp_user_query_1 = new WP_User_Query($args_users);

$args_user_meta = array(
    'orderby'   => 'meta_value',
    'order'     => $order,
    'meta_key'  => $orderByMeta,
    'role'      => $role,
    'number'    => $users_per_page,
    'offset'    => $offset,
    'meta_query'    => array(
        'relation'  => 'OR',
        array(
            'key'     => 'billing_company',
            'value'   => $search_term,
            'compare' => 'LIKE'
        ),
        array(
            'key'     => 'billing_oib',
            'value'   => $search_term,
            'compare' => 'LIKE'
        ),
    ),
);
$wp_user_query_2 = new WP_User_Query($args_user_meta);

$wp_user_query = new WP_User_Query();
$wp_user_query->results = array_unique( array_merge( $wp_user_query_1->results, $wp_user_query_2->results ), SORT_REGULAR );

// Get the results
$users = $wp_user_query->get_results();

// grab the current query parameters
$query_string = $_SERVER['QUERY_STRING'];

// The $base variable stores the complete URL to our page, including the current page arg

// if in the admin, your base should be the admin URL + your page
$base = admin_url('admin.php') . '?' . remove_query_arg('p', $query_string) . '%_%';
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Lista kupaca</h1>
    <a href="<?php echo get_home_url() ?>/wp-admin/admin.php?page=kupci" class="page-title-action">SVI KUPCI</a>
    <a href="<?php echo get_home_url() ?>/wp-admin/admin.php?page=kupci&role=customer_maloprodaja&order=asc&s" class="page-title-action">Maloprodajni</a>
    <a href="<?php echo get_home_url() ?>/wp-admin/admin.php?page=kupci&role=customer_veleprodaja&order=asc&s" class="page-title-action">Veleprodajni</a>
    <a href="<?php echo get_home_url() ?>/wp-admin/admin.php?page=kupci&role=customer_partner&order=asc&s" class="page-title-action">Partneri</a>

    <form method="get" action="<?php echo admin_url('admin.php');?>">
        <p>
            <label class="screen-reader-text" for="user-search-input">Search Users:</label>
            <input type="hidden" id="user-search-input" name="page" value="kupci">

            <select name="role" id="dropdown_product_type" style="margin-bottom: 4px;">
                <option value="">Sve grupe kupaca</option>
                <option value="customer_veleprodaja" <?php if($_GET['role'] == 'customer_veleprodaja') { echo 'selected=""'; } ?>>Veleprodaja (tvrtka / obrt)</option>
                <option value="customer_maloprodaja" <?php if($_GET['role'] == 'customer_maloprodaja') { echo 'selected=""'; } ?>>Maloprodajni (fizička osoba)</option>
                <option value="customer_partner" <?php if($_GET['role'] == 'customer_partner') { echo 'selected=""'; } ?>>Partner (daljnja prodaja)</option>
            </select>

            <select name="orderby" id="dropdown_product_type" style="margin-bottom: 4px;">
                <option value="">Sortiraj: Ime i prezime</option>
                <option value="billing_company" <?php if($_GET['orderby'] == 'billing_company') { echo 'selected=""'; } ?>>Sortiraj: Tvrtka</option>
            </select>

            <select name="order" id="dropdown_product_type" style="margin-bottom: 4px;">
                <option value="asc">Uzlazno</option>
                <option value="desc" <?php if($_GET['order'] == 'desc') { echo 'selected=""'; } ?>>Silazno</option>
            </select>

            <input type="search" id="user-search-input" name="s" value="<?php echo $_GET['s']; ?>" placeholder="Upišite pojam" autofocus>
            <input type="submit" id="search-submit" class="button" value="Pretraži kupce">
            Pronađeno kupaca: <?php echo $total_users ?>
        </p>

        <table class="wp-list-table widefat fixed striped users">
            <thead>
            <tr>
                <th>Naziv kupca</th>
                <th>Grupa kupca</th>
                <th>Tvrtka</th>
                <th>OIB</th>
                <th>Email</th>

            </tr>
            </thead>

            <tbody id="the-list" data-wp-lists="list:user">
            <?php if(!empty($users)){
                foreach($users as $key => $user){
                    $username       =   $user->user_login;
                    $useremail      =   $user->user_email;

                    $mail = get_user_meta( $user->ID, 'billing_email', true);
                    $user_role = get_userdata($user->ID)->roles[0];

                    switch($user_role) {
                        case 'customer_maloprodaja':
                            $role = 'Maloprodajni (fizička osoba)';
                            break;
                        case 'customer_veleprodaja':
                            $role = 'Veleprodaja (tvrtka / obrt)';
                            break;
                        case 'customer_partner':
                            $role = 'Partner (daljnja prodaja)';
                            break;
                        default:
                            $role = 'GOST (neregistrirani)';
                    }

                    ?>
                    <tr>
                        <td>
                            <a href="/wp/wp-admin/admin.php?page=kupci&user_id=<?php echo $user->ID ?>"><?php echo get_user_meta( $user->ID, 'first_name', true) . ' ' . get_user_meta( $user->ID, 'last_name', true); ?></a>
                        </td>
                        <td><?php echo $role; ?></td>
                        <td><?php echo get_user_meta( $user->ID, 'billing_company', true); ?></td>
                        <td><?php echo get_user_meta( $user->ID, 'billing_oib', true); ?></td>
                        <td><a href="mailto:<?php echo $mail?>"><?php echo $mail;?></a></td>
                    </tr>

                <?php } ?>
            <?php } else { ?>
                <tr class="no-items"><td class="colspanchange" colspan="3">Nema kupaca prema zadanim kriterijima pretrage.</td></tr>
            <?php }?>
            </tbody>

            <tfoot>
            <tr>
                <th scope="col" id="username" class="manage-column column-name">Naziv kupca</th>
                <th scope="col" id="email" class="manage-column column-email">Grupa kupca</th>
                <th scope="col" id="email" class="manage-column column-email">Tvrtka</th>
                <th scope="col" id="email" class="manage-column column-email">OIB</th>
                <th scope="col" id="email" class="manage-column column-email">Email</th>
            </tr>
            </tfoot>
        </table>
        <style>
            .page-numbers {
                font-size: 14px;
                border: 1px solid;
                padding: 7px 10px;
                background: #ffffff;
            }
            .tablenav .tablenav-pages {
                margin: 10px 0 9px;
            }
        </style>
        <?php // if on the front end, your base is the current page
        //$base = get_permalink( get_the_ID() ) . '?' . remove_query_arg('p', $query_string) . '%_%';
        echo '<div class="tablenav bottom"><div class="tablenav-pages">';
        echo paginate_links( array(
            'base'      => $base, // the base URL, including query arg
            'format'    => '&p=%#%', // this defines the query parameter that will be used, in this case "p"
            'prev_text' => __('&laquo; Previous'), // text for previous page
            'next_text' => __('Next &raquo;'), // text for next page
            'total'     => $total_pages, // the total number of pages we have
            'current'   => $page, // the current page
            'end_size'  => 1,
            'mid_size'  => 5,
        ));
        echo '</div></div>';

        ?>
    </form>
</div>
