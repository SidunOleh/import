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
        <?php include_template('desc-template.php', [
            'i' => $i,
            'template' => $template,
            'cuisines' => $cuisines,
            'locations' => $locations,
        ]) ?>
        <?php endforeach ?>

        <?php include_template('desc-template.php', [
            'i' => count($templates),
            'template' => [],
            'cuisines' => $cuisines,
            'locations' => $locations,
        ]) ?>

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