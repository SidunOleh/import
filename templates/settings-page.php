<?php 
$settings = get_option('import_settings', []); 
$cuisines = get_terms( [
    'taxonomy' => 'restaurant_cuisine',
    'hide_empty' => false,
] );
$locations = get_terms( [
    'taxonomy' => 'restaurant_location',
    'hide_empty' => false,
] );
?>

<div class="wrap">

    <form 
        action="<?php echo admin_url('admin-ajax.php') ?>" 
        method="POST">

        <input type="hidden" name="action" value="update_settings">

        <p>
            <label for="twocaptcha_key">
                <?php _e('2Captcha key') ?>
            </label>
            <input 
                type="text"
                name="settings[twocaptcha_key]" 
                value="<?php echo $settings['twocaptcha_key'] ?? '' ?>"
                id="twocaptcha_key">
        </p>

        <?php 
        $templates = $settings['templates'] ?? [];
        foreach ($templates as $i => $template): 
        ?>
        <p>
            <?php _e('Description template') ?>
            <br>
            <br>
            <select name="settings[templates][<?php echo $i ?>][rule][cuisine]">
                    <option selected disabled>
                        <?php _e('Select cuisine') ?>
                    </option>
                    <option 
                        value="*"
                        <?php echo '*' == ($template['rule']['cuisine'] ?? '') ? 'selected' : '' ?>>
                        <?php _e('All') ?>
                    </option>
                <?php foreach ($cuisines as $cuisine): ?>
                    <option 
                        value="<?php echo $cuisine->slug ?>" 
                        <?php echo urldecode($cuisine->slug) == urldecode($template['rule']['cuisine'] ?? '') ? 'selected' : '' ?>>
                        <?php echo $cuisine->name ?>
                    </option>
                <?php endforeach ?>
            </select>
            <select name="settings[templates][<?php echo $i ?>][rule][location]">
                    <option selected disabled>
                        <?php _e('Select location') ?>
                    </option>
                    <option 
                        value="*"
                        <?php echo '*' == ($template['rule']['location'] ?? '') ? 'selected' : '' ?>>
                        <?php _e('All') ?>
                    </option>
                <?php foreach ($locations as $location): ?>
                    <option 
                        value="<?php echo $location->slug ?>"
                        <?php echo urldecode($location->slug) == urldecode($template['rule']['location'] ?? '') ? 'selected' : '' ?>>
                        <?php echo $location->name ?>
                    </option>
                <?php endforeach ?>
            </select>
            <?php echo wp_editor($template['text'], 'template', [
                'textarea_name' => "settings[templates][{$i}][text]",
                'textarea_rows' => 10,
            ]) ?>
        </p>
        <?php endforeach ?>

        <p>
            <?php _e('Description template') ?>
            <br>
            <br>
            <select name="settings[templates][<?php echo count($templates) ?>][rule][cuisine]">
                    <option selected disabled>
                        <?php _e('Select cuisine') ?>
                    </option>
                    <option value="*">
                        <?php _e('All') ?>
                    </option>
                <?php foreach ($cuisines as $cuisine): ?>
                    <option value="<?php echo $cuisine->slug ?>">
                        <?php echo $cuisine->name ?>
                    </option>
                <?php endforeach ?>
            </select>
            <select name="settings[templates][<?php echo count($templates) ?>][rule][location]">
                    <option selected disabled>
                        <?php _e('Select location') ?>
                    </option>
                    <option value="*">
                        <?php _e('All') ?>
                    </option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?php echo $location->slug ?>">
                        <?php echo $location->name ?>
                    </option>
                <?php endforeach ?>
            </select>
            <?php echo wp_editor('', 'template', [
                'textarea_name' => 'settings[templates][' . count($templates) . '][text]',
                'textarea_rows' => 10,
            ]) ?>
        </p>

        <p>
            <input 
                type="submit"
                id="update-btn" 
                name="update-btn" 
                class="button button-primary" 
                value="<?php _e('Update') ?>">
        </p>

    </form>   
        
</div>

<style>
    #wpbody {
        min-height: 100vh;
    }
</style>