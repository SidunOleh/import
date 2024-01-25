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
    const container = document.querySelector('#wpcontent')
    const failedList = document.querySelector('.failed')
    const btn = document.querySelector('#import')
    btn.addEventListener('click', function (e) {
        container.classList.add('loading')
        const imagesCount = document.querySelector('#images_count').value
        const reviewsCount = document.querySelector('#reviews_count').value
        const urls = document.querySelector('#urls').value
        var data = new FormData()
        data.append('config[images_count]', imagesCount)
        data.append('config[reviews_count]', reviewsCount)
        data.append('urls', urls)
        data.append('action', 'import_restaurants')
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: data,
        }).then(async (res) => {
            container.classList.remove('loading')

            if (res.status != 200) {
                alert('Error.')
                return
            }

            const data = await res.json()
            if (data.failed.length) {
                failedList.innerHTML = '<h2>Failed import</h2>'
                failedList.innerHTML += data.failed.join('<br>')
                alert('Try again to import data which were not imported.')
            } else {
                failedList.innerHTML = ''
                alert('Successfully imported.')
            }
        })
    })
</script>

<style>
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