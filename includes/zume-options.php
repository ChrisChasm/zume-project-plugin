<?php

const SECTION_ID = "zume_project_plugin_main";
const PAGE_ID = "zume_project_plugin";
const SETTING_ID_MAILCHIMP_KEY = "zume_mailchimp_api_key";
const SETTING_ID_MAILCHIMP_DC = "zume_mailchimp_dc";
const SETTING_ID_MAILCHIMP_LIST_ID = "zume_mailchimp_list_id";

add_action('admin_menu', 'zume_plugin_admin_menu');

function zume_plugin_admin_menu() {
    add_options_page('Zúme Project Plugin', 'Zúme Project Plugin', 'manage_options', 'zume-project-plugin', 'zume_options_page');
}

function zume_options_page() {
    ?>
    <div class="wrap">
        <h1><?php _e("Zúme Project Plugin"); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields(SECTION_ID);
            do_settings_sections(PAGE_ID);
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'zume_plugin_admin_init');

function zume_plugin_admin_init() {
    register_setting(SECTION_ID, SETTING_ID_MAILCHIMP_KEY, 'trim');
    register_setting(SECTION_ID, SETTING_ID_MAILCHIMP_DC, 'trim');
    register_setting(SECTION_ID, SETTING_ID_MAILCHIMP_LIST_ID, 'trim');

    add_settings_section(SECTION_ID, 'MailChimp Settings', 'zume_section_text', PAGE_ID);
    add_settings_field(SETTING_ID_MAILCHIMP_KEY, 'MailChimp API key', 'zume_mailchimp_api_key_input_html', PAGE_ID, SECTION_ID);
    add_settings_field(SETTING_ID_MAILCHIMP_DC, 'MailChimp data center', 'zume_mailchimp_dc_input_html', PAGE_ID, SECTION_ID);
    add_settings_field(SETTING_ID_MAILCHIMP_LIST_ID, 'MailChimp List ID', 'zume_mailchimp_list_id_html', PAGE_ID, SECTION_ID);
}

function zume_section_text() {
    echo '<p>';
    _e("Please fill in your MailChimp API key here for use in the website.");
    echo '</p>';
}

function zume_mailchimp_api_key_input_html() {
    $option = get_option(SETTING_ID_MAILCHIMP_KEY);
    printf(
            '<input id="%s" name="%s" size="40" type="text" value="%s">',
            esc_attr(SETTING_ID_MAILCHIMP_KEY), esc_attr(SETTING_ID_MAILCHIMP_KEY), htmlspecialchars($option)
    );
}

function zume_mailchimp_dc_input_html() {
    $option = get_option(SETTING_ID_MAILCHIMP_DC);
    printf(
            '<input id="%s" name="%s" size="40" type="text" value="%s">',
            esc_attr(SETTING_ID_MAILCHIMP_DC), esc_attr(SETTING_ID_MAILCHIMP_DC), htmlspecialchars($option)
    );
}

function zume_mailchimp_list_id_html() {
    $option = get_option(SETTING_ID_MAILCHIMP_LIST_ID);
    printf(
            '<input id="%s" name="%s" size="40" type="text" value="%s">',
            esc_attr(SETTING_ID_MAILCHIMP_LIST_ID), esc_attr(SETTING_ID_MAILCHIMP_LIST_ID), htmlspecialchars($option)
    );
}
