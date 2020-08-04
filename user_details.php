<?php
    $user_id = $_GET['user_id'];
    $get_user_data = get_userdata($user_id);
    if(isset($_POST) && count($_POST) > 0) {

        // update user data
        wp_update_user([
            'ID' => $user_id,
            'first_name'    => $_POST['first_name'],
            'last_name'     => $_POST['last_name'],
            'user_url'      => $_POST['url'],
            'user_email'    => $_POST['email'],
            'role'          => $_POST['grupa_kupca'],
        ]);

        $metas = array(
            'nickname'              => $_POST['nickname'],
            'billing_first_name'    => $_POST['billing_first_name'],
            'billing_last_name'     => $_POST['billing_last_name'],
            'billing_company'       => $_POST['billing_company'],
            'billing_address_1'     => $_POST['billing_address_1'],
            'billing_address_2'     => $_POST['billing_address_2'],
            'billing_city'          => $_POST['billing_city'],
            'billing_postcode'      => $_POST['billing_postcode'],
            'billing_state'         => $_POST['billing_state'],
            'billing_phone'         => $_POST['billing_phone'],
            'billing_email'         => $_POST['billing_email'],
            'billing_oib'           => $_POST['billing_oib'],
            'shipping_first_name'   => $_POST['shipping_first_name'],
            'shipping_last_name'    => $_POST['shipping_last_name'],
            'shipping_company'      => $_POST['shipping_company'],
            'shipping_address_1'    => $_POST['shipping_address_1'],
            'shipping_address_2'    => $_POST['shipping_address_2'],
            'shipping_city'         => $_POST['shipping_city'],
            'shipping_postcode'     => $_POST['shipping_postcode'],
            'shipping_state'        => $_POST['shipping_state'],
            'shipping_phone'        => $_POST['shipping_phone'],
            'shipping_email'        => $_POST['shipping_email'],
            'shipping_oib'          => $_POST['shipping_oib'],
            '_user_blocked'          => $_POST['blocked'] == 1 ? 1 : 0
        );

        foreach($metas as $key => $value) {
            update_user_meta( $user_id, $key, $value );
        }

        if(isset($_POST['coupon_amount']) AND $_POST['coupon_amount'] > 0) {
            $post_id = post_exists( $get_user_data->user_login,'','','shop_coupon');
            if($post_id AND get_post_status ( $post_id ) == 'publish') {
                wp_redirect( get_home_url() . '/wp-admin/edit.php?post_type=shop_coupon&paged=1&ids=' . $post_id );
                exit;
            } else {
                $add_new_coupon = [
                    'post_title' => $get_user_data->user_login,
                    'post_content' => '',
                    'post_excerpt' => 'Partner program (-' . $_POST['coupon_amount'] . '%)',
                    'post_status' => 'publish',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_type' => 'shop_coupon'
                ];

                $coupon_id = wp_insert_post($add_new_coupon);
                $metas = [
                    '_wjecf_customer_ids' => $user_id,
                    '_wjecf_apply_silently' => 'no',
                    '_wjecf_is_auto_coupon' => 'yes',
                    '_wjecf_categories_and' => 'no',
                    '_wjecf_products_and' => 'no',
                    'exclude_sale_items' => 'yes',
                    'individual_use' => 'yes',
                    'free_shipping' => 'no',
                    'coupon_amount' => $_POST['coupon_amount'],
                    'discount_type' => 'percent',
                ];
                foreach($metas as $key => $value) {
                    add_post_meta($coupon_id, $key, $value);
                }
            }
        }
        wp_redirect(get_home_url() . '/wp-admin/admin.php?page=kupci&user_id=' . $user_id);
        exit();
    }
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Detalji kupca</h1>
    <a href="<?php echo get_home_url() ?>/wp-admin/admin.php?page=kupci" class="page-title-action">Lista kupaca</a>
<form action="#" method="POST">
        <div class="row">
            <div class="column">
                <h2>Osobni podaci</h2>
                <table class="form-table" role="presentation">
                    <tbody><tr class="user-user-login-wrap">
                        <th><label for="user_login">Korisničko ime</label></th>
                        <td><input type="text" name="user_login" id="user_login" value="<?php echo $get_user_data->user_login  ?>" disabled="disabled" class="regular-text"> <span class="description">Korisnička imena ne mogu biti izmijenjena.</span></td>
                    </tr>

                    <tr class="user-first-name-wrap">
                        <th><label for="first_name">Ime</label></th>
                        <td><input type="text" name="first_name" id="first_name" value="<?php echo get_user_meta($user_id, 'first_name', true) ?>" class="regular-text"></td>
                    </tr>

                    <tr class="user-last-name-wrap">
                        <th><label for="last_name">Prezime</label></th>
                        <td><input type="text" name="last_name" id="last_name" value="<?php echo get_user_meta($user_id, 'last_name', true) ?>" class="regular-text"></td>
                    </tr>

                    <tr class="user-nickname-wrap">
                        <th><label for="nickname">Nadimak <span class="description">(obavezno)</span></label></th>
                        <td><input type="text" name="nickname" id="nickname" value="<?php echo get_user_meta($user_id,'nickname',true) ?>" class="regular-text"></td>
                    </tr>

                    <tr class="user-email-wrap">
                        <th><label for="email">E-pošta <span class="description">(obavezno)</span></label></th>
                        <td><input type="email" name="email" id="email" aria-describedby="email-description" value="<?php echo $get_user_data->user_email ?>" class="regular-text ltr">
                        </td>
                    </tr>

                    <tr class="user-url-wrap">
                        <th><label for="url">Web-stranica</label></th>
                        <td><input type="url" name="url" id="url" value="<?php echo $get_user_data->user_url ?>" class="regular-text code"></td>
                    </tr>

                    <tr class="user-url-wrap">
                        <th><label for="grupa_kupca">Grupa kupca</label></th>
                        <td>
                            <select name="grupa_kupca" id="grupa_kupca" class="regular-text">
                                <option value="customer">GOST (neregistrirani)</option>
                                <option value="customer_maloprodaja" <?php echo in_array('customer_maloprodaja', $get_user_data->roles) ? 'selected=""' : ''?>>Maloprodajni (fizička osoba)</option>
                                <option value="customer_veleprodaja" <?php echo in_array('customer_veleprodaja', $get_user_data->roles) ? 'selected=""' : ''?>>Veleprodajni (tvrtka / obrt)</option>
                                <option value="customer_partner" <?php echo in_array('customer_partner', $get_user_data->roles) ? 'selected=""' : ''?>>Partner (daljnja prodaja)</option>
                                <option value="administrator" <?php echo in_array('administrator', $get_user_data->roles) ? 'selected=""' : ''?>>ADMINISTRATOR</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="user-url-wrap">
                        <?php if(get_user_meta($user_id, '_user_blocked', true) == 1) { ?>
                            <th style="color: red;"><label for="url">BLOKIRAN</label></th>
                            <td style="color: red;"><input type="checkbox" name="blocked" id="blocked" value="1" checked="checked"> ZABRANJEN PRISTUP</td>
                        <?php } else { ?>
                            <th><label for="url">BLOKIRAJ</label></th>
                            <td><input type="checkbox" name="blocked" id="blocked" value="1"> Zabrani pristup ovom kupcu</td>
                        <?php } ?>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="column">
                <h2>Partner</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <?php
                        $args = array(
                            'post_type'		=>	'shop_coupon',
                            'meta_query'	=>	array(
                                array(
                                    'key'	=>	'_wjecf_customer_ids',
                                    'value' => $user_id
                                )
                            )
                        );
                        $my_query = new WP_Query( $args );

                        if ( $my_query->have_posts() ) {
                            while ($my_query->have_posts()) {
                                $my_query->the_post();
                                $post_id = get_the_id();

                                switch (get_post_meta($post_id, 'discount_type', true)) {
                                    case 'percent':
                                        $discount_type = '%';
                                        break;
                                    case 'fixed_cart':
                                        $discount_type = 'kn po narudžbi';
                                        break;
                                    default:
                                        $discount_type = 'kn';
                                }
                                ?>
                                <tr class="user-user-login-wrap">
                                    <th><label for="coupon_amount">Popust</label></th>
                                    <td><input type="text" id="coupon_amount" value="<?php echo get_post_meta($post_id, 'coupon_amount', true) . ' ' . $discount_type; ?>" disabled="disabled" class="regular-text"> <span class="description">Više detalja postavite <a href="<?php echo get_home_url() . '/wp-admin/post.php?post=' . $post_id. '&action=edit&classic-editor'; ?>">ovdje</a></span></td>
                                </tr>
                                <?
                            }
                        } else { ?>
                            <tr class="user-user-login-wrap">
                                <th><label for="coupon_amount">Popust %</label></th>
                                <td><input type="number" id="coupon_amount" value="0" name="coupon_amount" class="regular-text"> <span class="description">Više detalja možete kasnije postaviti</span></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <hr />
                <h2>INFO</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th>Registriran:</th>
                        <td><?php echo date('d.m.Y. H:i', strtotime($get_user_data->user_registered)); ?></td>
                    </tr>
                    <tr>
                        <th>Narudžbe</th>
                        <td>
                            <?php if(wc_get_customer_order_count($user_id) > 0) { ?>
                                <a href="<?php echo get_home_url(); ?>/wp-admin/edit.php?post_type=shop_order&_customer_user=<?php echo $user_id ?>>">
                                    Pogledaj sve narudžbe od ovog kupca
                                </a>
                                ( <?php echo wc_get_customer_order_count($user_id) ?> )
                            <?php } else { ?>
                                Ovaj kupac nema narudžbi
                            <?php } ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <div class="row">
    <hr />
    <div class="column">
        <h2>Adresa za račun</h2>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th>
                    <label for="billing_first_name">Ime</label>
                </th>
                <td>
                    <input type="text" name="billing_first_name" id="billing_first_name" value="<?php echo get_user_meta($user_id, 'billing_first_name', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_last_name">Prezime</label>
                </th>
                <td>
                    <input type="text" name="billing_last_name" id="billing_last_name" value="<?php echo get_user_meta($user_id, 'billing_last_name', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_company">Tvrtka</label>
                </th>
                <td>
                    <input type="text" name="billing_company" id="billing_company" value="<?php echo get_user_meta($user_id, 'billing_company', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_oib">OIB tvrtke</label>
                </th>
                <td>
                    <input type="number" id="billing_oib" name="billing_oib" value="<?php echo get_user_meta($user_id, 'billing_oib', true); ?>"  class="regular-text"></td>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_address_1">Adresa</label>
                </th>
                <td>
                    <input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo get_user_meta($user_id, 'billing_address_1', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_address_2">Adresa 2</label>
                </th>
                <td>
                    <input type="text" name="billing_address_2" id="billing_address_2" value="<?php echo get_user_meta($user_id, 'billing_address_2', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_city">Grad</label>
                </th>
                <td>
                    <input type="text" name="billing_city" id="billing_city" value="<?php echo get_user_meta($user_id, 'billing_city', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_postcode">Poštanski broj</label>
                </th>
                <td>
                    <input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo get_user_meta($user_id, 'billing_postcode', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_state">Županija</label>
                </th>
                <td>
                    <input type="text" id="billing_state" name="billing_state" value="<?php echo get_user_meta($user_id, 'billing_state', true) ?>" class="js_field-state regular-text">
                    <p class="description">Država/Zemlja ili kod države</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_phone">Telefon</label>
                </th>
                <td>
                    <input type="text" name="billing_phone" id="billing_phone" value="<?php echo get_user_meta($user_id, 'billing_phone', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="billing_email">Email adresa</label>
                </th>
                <td>
                    <input type="text" name="billing_email" id="billing_email" value="<?php echo get_user_meta($user_id, 'billing_email', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="column">
        <h2>Adresa za dostavu</h2>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th>
                    <label for="shipping_first_name">Ime</label>
                </th>
                <td>
                    <input type="text" name="shipping_first_name" id="shipping_first_name" value="<?php echo get_user_meta($user_id, 'shipping_first_name', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_last_name">Prezime</label>
                </th>
                <td>
                    <input type="text" name="shipping_last_name" id="shipping_last_name" value="<?php echo get_user_meta($user_id, 'shipping_last_name', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_company">Tvrtka</label>
                </th>
                <td>
                    <input type="text" name="shipping_company" id="shipping_company" value="<?php echo get_user_meta($user_id, 'shipping_company', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <th>
                <label for="shipping_oib">OIB tvrtke</label>
            </th>
            <td class="acf-input">
                <input type="number" id="shipping_oib" name="shipping_oib" value="<?php echo get_user_meta($user_id, 'shipping_oib', true); ?>"  class="regular-text"></td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_address_1">Adresa</label>
                </th>
                <td>
                    <input type="text" name="shipping_address_1" id="shipping_address_1" value="<?php echo get_user_meta($user_id, 'shipping_address_1', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_address_2">Adresa 2</label>
                </th>
                <td>
                    <input type="text" name="shipping_address_2" id="shipping_address_2" value="<?php echo get_user_meta($user_id, 'shipping_address_2', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_city">Grad</label>
                </th>
                <td>
                    <input type="text" name="shipping_city" id="shipping_city" value="<?php echo get_user_meta($user_id, 'shipping_city', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_postcode">Poštanski broj</label>
                </th>
                <td>
                    <input type="text" name="shipping_postcode" id="shipping_postcode" value="<?php echo get_user_meta($user_id, 'shipping_postcode', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_state">Županija</label>
                </th>
                <td>
                    <input type="text" id="shipping_state" name="shipping_state" value="<?php echo get_user_meta($user_id, 'shipping_state', true) ?>" class="js_field-state regular-text">
                    <p class="description">Država/Zemlja ili kod države</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_phone">Telefon</label>
                </th>
                <td>
                    <input type="text" name="shipping_phone" id="shipping_phone" value="<?php echo get_user_meta($user_id, 'shipping_phone', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="shipping_email">Email adresa</label>
                </th>
                <td>
                    <input type="text" name="shipping_email" id="shipping_email" value="<?php echo get_user_meta($user_id, 'shipping_email', true) ?>" class="regular-text">
                    <p class="description"></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
    <hr />
    <input type="submit" value="Spremi promjene" class="button button-primary">
</form>
</div>
