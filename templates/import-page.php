<div class="wrap">

    <p>
        <label for="images_count">
            <?php _e('Images count') ?>
        </label>
        <input 
            name="images_count" 
            type="number" step="1" 
            min="-1" 
            id="images_count" 
            value="50" 
            class="small-text">
    </p>

    <p>
        <label for="reviews_count">
            <?php _e('Reviews count') ?>
        </label>
        <input 
            name="reviews_count" 
            type="number" 
            step="1" 
            min="-1" 
            id="reviews_count" 
            value="100" 
            class="small-text">
    </p>

    <textarea 
        name="urls" 
        rows="10" 
        cols="50" 
        id="urls" 
        class="large-text code"></textarea>

    <p>
        <input 
            type="submit"
            id="import" 
            name="import" 
            class="button button-primary" 
            value="<?php _e('Import') ?>">
    </p>

    <div class="failed">

    </div>

</div>

<script>
    const container = document.querySelector('#wpbody')
    const failedUrls = document.querySelector('.failed')
    const importBtn = document.querySelector('#import')
    importBtn.addEventListener('click', async function (e) {
        container.classList.add('loading')
        failedUrls.innerHTML = ''

        const imagesCount = document.querySelector('#images_count').value
        const reviewsCount = document.querySelector('#reviews_count').value
        const urls = document.querySelector('#urls').value.split(/\r?\n/)

        const data = new FormData();
        data.append('action', 'import_items')
        data.append('config[images_count]', imagesCount)
        data.append('config[reviews_count]', reviewsCount)
        urls.forEach(url => data.append('urls[]', url))

        try {
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data,
            }).then(async res => {
                container.classList.remove('loading')
                
                const progress = await res.json()

                if (progress.failed_urls.length) {
                    failedUrls.innerHTML = '<h2>Failed imports</h2>'
                    failedUrls.innerHTML += progress.failed_urls.join('<br>')
                    alert('Some imports failed. Try again.')
                } else {
                    alert('Successfully imported.')
                }
            })
        } catch {
            alert('Something goes wrong. Try again.')
            
            container.classList.remove('loading')
        }
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