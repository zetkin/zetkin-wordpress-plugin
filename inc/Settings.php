<?php

namespace Zetkin\ZetkinWordPressPlugin;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Settings
{
    const ORGANIZATION_ID_OPTION = "zetkin_organization_id";
    const STAGING_ENVIRONMENT_OPTION = "zetkin_staging_environment";

    public static function init()
    {
        add_action('admin_menu', function () {
            add_options_page(
                'Zetkin Settings',
                'Zetkin',
                'manage_options',
                'zetkin-settings',
                function () {
                    self::renderSettingsPage();
                }
            );
        });

        add_action('admin_init', function () {
            self::registerSettings();
        });

        add_action('enqueue_block_editor_assets', function () {
            wp_add_inline_script(
                'wp-block-editor',
                'window.zetkinSettings = ' . wp_json_encode([
                    'organizationId' => get_option(self::ORGANIZATION_ID_OPTION, ''),
                    'stagingEnvironment' => get_option(self::STAGING_ENVIRONMENT_OPTION, ''),
                ]) . ';',
                'before'
            );
        });
    }

    private static function renderSettingsPage()
    {
?>
        <div class="wrap">
            <h1>Zetkin Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('zetkin_settings_group');
                do_settings_sections('zetkin-settings');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    private static function registerSettings()
    {
        add_settings_section(
            'zetkin_main_section',
            'Main Settings',
            null,
            'zetkin-settings'
        );

        // Note: boolean settings should use '1' for true and '' for false
        // Because the wp_options table casts everything to a string.
        self::registerOrganizationIdSetting();
        self::registerStagingSiteSetting();
    }

    private static function registerOrganizationIdSetting()
    {
        $settingId = self::ORGANIZATION_ID_OPTION;
        register_setting('zetkin_settings_group', $settingId, [
            'sanitize_callback' => function ($input) use ($settingId) {
                $is_int = filter_var($input, FILTER_VALIDATE_INT) !== false;
                $is_positive = (int) $input > 0;
                if (!$is_int || !$is_positive) {
                    add_settings_error(
                        $settingId,
                        "{$settingId}_invalid",
                        'Organization ID must be a positive number.',
                        'error'
                    );
                    return get_option($settingId);
                }
                return (int) $input;
            }
        ]);

        add_settings_field(
            $settingId,
            'Organization ID',
            function () use ($settingId) {
                $value = get_option($settingId, '');
                echo '<input required type="number" name="' . $settingId . '" value="' . esc_attr($value) . '" />';
            },
            'zetkin-settings',
            'zetkin_main_section'
        );
    }

    private static function registerStagingSiteSetting()
    {
        $settingId = self::STAGING_ENVIRONMENT_OPTION;
        register_setting('zetkin_settings_group', $settingId, [
            'sanitize_callback' => function ($input) {
                return $input === '1' ? '1' : '';
            }
        ]);

        add_settings_field(
            $settingId,
            'Use Zetkin Staging Environment',
            function () use ($settingId) {
                $value = get_option($settingId, '');
                echo '<input id="' . $settingId . '" type="checkbox" name="' . $settingId . '" value="1"' . checked('1', $value, '') . ' />';
                echo '<label for="' . $settingId . '" class="description">Check this box if you are using the Zetkin staging environment. Only Zetkin developers should use this.</label>';
            },
            'zetkin-settings',
            'zetkin_main_section'
        );
    }
}
