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

<div class="progress">
    <div class="progress__body">

        <span id="total">0</span>
        <br>
        <br>
        <?php _e('Success: ') ?> <span id="success">0</span>,
        <?php _e('Fail: ') ?> <span id="fail">0</span>
        <br>
        <br>
        <a id="stop" class="stop">
            <?php _e( 'Stop import' ) ?>
        </a>
    </div>
</div>

<script>
    const container = document.querySelector('#wpbody')
    const progressEl = document.querySelector('.progress')
    const total = progressEl.querySelector('#total')
    const success = progressEl.querySelector('#success')
    const fail = progressEl.querySelector('#fail')
    const failedUrls = document.querySelector('.failed')
    const importBtn = document.querySelector('#import')
    let pid = null
    importBtn.addEventListener('click', async function (e) {
        container.classList.add('loading')
        failedUrls.innerHTML = ''

        const imagesCount = document.querySelector('#images_count').value
        const reviewsCount = document.querySelector('#reviews_count').value
        const urls = document.querySelector('#urls').value

        const params = new URLSearchParams({
            'action': 'import_items',
            'config[images_count]': imagesCount,
            'config[reviews_count]': reviewsCount,
            'urls': urls,
        })

        const source = new EventSource(`/wp-admin/admin-ajax.php?${params.toString()}`)

        source.addEventListener('progress', function (e) {
            const progress = JSON.parse(e.data)

            progressEl.classList.add('show')
            total.innerHTML = `${progress.success + progress.fail} / ${progress.total}`
            success.innerHTML = progress.success
            fail.innerHTML = progress.fail
            pid = progress.pid
        })

        source.addEventListener('end', function (e) {
            const progress = JSON.parse(e.data)

            if (progress.failed_urls.length) {
                failedUrls.innerHTML = '<h2>Failed imports</h2>'
                failedUrls.innerHTML += progress.failed_urls.join('<br>')
                alert('Some imports failed. Try again.')
            } else {
                alert('Successfully imported.')
            }

            container.classList.remove('loading')
            progressEl.classList.remove('show')
            source.close()
        })

        source.addEventListener('stop', function(e) {
            alert('Import was stopped.')
            container.classList.remove('loading')
            progressEl.classList.remove('show')
            source.close()
        })

        source.addEventListener('error', function(e) {
            alert('Something goes wrong. Try again.')
            container.classList.remove('loading')
            progressEl.classList.remove('show')
            source.close()
        })
    })

    const stopLink = document.querySelector('#stop')
    stopLink.addEventListener('click', function (e) {
        e.preventDefault()

        stopLink.classList.add('loading')

        try {
            fetch(`/wp-admin/admin-ajax.php?action=stop_import&pid=${pid}`).then(async res => {
                const data = await res.json()

                if (! data.success) {
                    alert('Can not stop import.')
                }

                stopLink.classList.remove('loading')
            })
        } catch {
            alert('Can not stop import.')
            stopLink.classList.remove('loading')
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

    .progress {
        position: absolute;
        z-index: 1000;
        top: -50px;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        display: none;
    }

    .progress.show {
        display: flex;
    }

    .progress__body {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
    }

    .stop {
        font-size: 18px;
        cursor: pointer;
    }
</style>