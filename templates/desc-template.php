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

    <?php echo wp_editor($template['text'] ?? '', 'template', [
        'textarea_name' => "settings[templates][{$i}][text]",
        'textarea_rows' => 10,
    ]) ?>

</p>