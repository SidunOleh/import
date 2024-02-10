<div class="wrap">

    <?php
    $settings = get_option('import_settings', []);
    ?>

    <p>
        <label for="twocaptcha_key">
            <?php _e('2Captcha key') ?>
        </label>
        <input 
            type="text"
            name="twocaptcha_key" 
            value="<?php echo $settings['twocaptcha_key'] ?? '' ?>"
            id="twocaptcha_key">
    </p>

    <p>
        <input 
            type="submit"
            id="update-btn" 
            name="update-btn" 
            class="button button-primary" 
            value="<?php _e('Update') ?>">
    </p>

</div>


<script>
    const container = document.querySelector('#wpbody')
    const updateBtn = document.querySelector('#update-btn')

    updateBtn.addEventListener('click', async e => {
        container.classList.add('loading')

        const data = new FormData()
        data.append('action', 'update_settings')
        data.append('settings[twocaptcha_key]', document.querySelector('#twocaptcha_key').value)

        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data,
            })

            if (response.status) {
                alert('Successfully updated.')
            } else {
                throw new Error()
            }
        } catch {
            alert('Error. Try again.')
        }

        container.classList.remove('loading')
    })
</script>

<style>
    #wpbody {
        min-height: 100vh;
    }
    
    .loading {
        position: relative;
    }

    .loading::before {
        content: "";
        position: absolute;
        z-index: 10;
        top: 0;
        left: 0;
        background: -webkit-gradient(linear, left top, right bottom, color-stop(40%, #eeeeee), color-stop(50%, #dddddd), color-stop(60%, #eeeeee));
        background: linear-gradient(to bottom right, #eeeeee 40%, #dddddd 50%, #eeeeee 60%);
        background-size: 200% 200%;
        background-repeat: no-repeat;
        -webkit-animation: placeholderShimmer 2s infinite linear;
        animation: placeholderShimmer 2s infinite linear;
        height: 100%;
        width: 100%;
        opacity: 0.6;
    }

    @-webkit-keyframes placeholderShimmer {
        0% {
            background-position: 100% 100%;
        }
        100% {
            background-position: 0 0;
        }
    }

    @keyframes placeholderShimmer {
        0% {
            background-position: 100% 100%;
        }
        100% {
            background-position: 0 0;
        }
    }
</style>