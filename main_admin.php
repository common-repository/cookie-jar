<?php
global $wpdb;
$options_table = $wpdb->prefix . "options";

/*
 * secure $_POST output
 */
function cookie_monster_fn_protectSQL($post_output)
{
	$post_output = preg_replace("/'/", "`", $post_output);
	$post_output = preg_replace("/\"/", "&#34;", $post_output);
	$post_output = preg_replace("/(\<script)(.*?)(script>)/si", "", $post_output);
	$post_output = strip_tags($post_output);
	$post_output = str_replace(array("\"",">","<","\\"), "", $post_output);

	return $post_output;
}

function cookie_monster_fn_dropdown($f_name, $f_value, $f_array, $f_default='', $f_css='', $f_tabindex='', $f_js='')
{
    echo '<select name="' . $f_name . '" id="' . $f_name . '"';

    if ($f_css != '') echo ' class="' . $f_css . '"';
    if ($f_js != '') echo ' ' . $f_js;
    if ($f_tabindex != '') echo ' tabindex="' . $f_tabindex . '"';

    echo ">";

    if($f_default != '') echo "\n<option value=\"\">" . $f_default . "</option>\n";

        while(list($key, $val) = each($f_array))
        {
        $output = "<option value=\"" . $key . "\"";
            if ($key == $f_value)
            {
            $output .= " selected";
            }
        $output .= ">" . $val . "</option>\n";
        echo $output;
        }

    echo"</select>";
}

function cookie_monster_fn_checkboxes($f_name, $f_value, $f_array) {
    echo '<div>';
    echo "\n";

        $count_it = 0;
        while(list($key, $val) = each($f_array))
        {
            $cookie_image_output = plugin_dir_url(__FILE__) . 'img/' . $key . '.png';
            $get_checked = $f_value == $key ? ' checked' : '';
            echo '<label>
                <input type="radio"
                    name="' . $f_name . '"
                    value="' . $key . '"
                    id="cookie_jar_main_image_' . $count_it . '"
                    ' . $get_checked . '
                >
                ' . $val . '
                <img src="' . $cookie_image_output . '" />
            </label>
            ';
            $count_it ++;
        }

    echo '</div>';
    echo "\n";
}

echo '<h3>' . esc_html__('Cookie Jar Settings', 'cookie-monster') . '</h1>';

if ( ! empty( $_POST ) && check_admin_referer( 'update_jar_option', 'cookie_jar_custom_nonce' ) )
{

    /**
     * check if current user has ability to update cookie policy link
     */
    if (!current_user_can('administrator')) {
        echo '<div class="error"><p>' . esc_html__('Sorry, you do not have the privileges to perform this action.', 'cookie-monster') . '</p></div>';
        return;
    }

    /**
     * clean $_POST output
     */
    foreach ($_POST as $key => $val) $_POST[$key] = cookie_monster_fn_protectSQL($_POST[$key]);

        /**
         * sanitize dropdown output
         */
        $cookie_page_id = sanitize_text_field( $_POST['jar_privacy'] );

        /**
         * get cookie policy slug based on dropdown ID
         */
        $link = get_post( $cookie_page_id );
        $get_privacy_link = $link->post_name;

        $cookie_image = sanitize_text_field( $_POST['cookie_jar_main_image'] );
        $cookie_image_align = sanitize_text_field( $_POST['cookie_img_align'] );

        /**
         * encode information to be stored in the DB
         */
        $to_store = array ($cookie_page_id, $cookie_image, $cookie_image_align, $get_privacy_link);
        $store = json_encode($to_store);

    update_option( 'cookie_jar_privacy_link', $store, 'no' );
    echo '<div class="updated"><p>' . esc_html__('Cookie Jar settings have been updated!', 'cookie-monster') . '</p></div>';

}
else
{
    /**
     * get ID stored in the DB
     */
    $selected = json_decode( get_option( 'cookie_jar_privacy_link' ) );

    /**
     * display form
     */

    $assign_arr = array (
        'left' => esc_html__('Left', 'cookie-monster'),
        'right' => esc_html__('Right', 'cookie-monster')
    );

    $checkboxes_arr = array (
        'cookies' => esc_html__('Default', 'cookie-monster'),
        'cookies2' => esc_html__('Cookie', 'cookie-monster'),
        'cookies3' => esc_html__('Cookies', 'cookie-monster'),
        'cookie_jar' => esc_html__('Jar of Cookies', 'cookie-monster')
    );

    echo '<form method="post" action="" class="manage_cookie_monster">';

        echo '<h4>' . esc_html__('Select image to be displayed', 'cookie-monster') . '</h4>';
        cookie_monster_fn_checkboxes('cookie_jar_main_image', $selected[1], $checkboxes_arr);

        echo '<h4>' . esc_html__('Cookie DIV should be placed on the:', 'cookie-monster') . '</h4>';
        cookie_monster_fn_dropdown('cookie_img_align', $selected[2], $assign_arr, esc_html__('&mdash; Select &mdash;', 'cookie-monster'), '', '', '');

        echo '<h4>' . esc_html__('Select the Cookie Policy page', 'cookie-monster') . '</h4>';
        wp_dropdown_pages(
            array(
                'id' => 'jar_privacy',
                'name' => 'jar_privacy',
                'class' => 'main_stuff',
                'selected' => $selected[0],
                'show_option_none' => esc_html__('&mdash; Select &mdash;', 'cookie-monster'),
            )
        );

        wp_nonce_field( 'update_jar_option', 'cookie_jar_custom_nonce' );
        submit_button(esc_html__('Update settings', 'cookie-monster'), 'primary', 'submit', TRUE);
    echo '</form>';

}