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
        <span id="total">0 / 0</span>
        <br>
        <br>
        <?php _e('Success: ') ?> <span id="success">0</span>,
        <?php _e('Fail: ') ?> <span id="fail">0</span>
    </div>
</div>

<script>
    const container = document.querySelector('#wpbody')
    const failedImportsList = document.querySelector('.failed')
    const importBtn = document.querySelector('#import')
    const progressBar =  document.querySelector('.progress')

    importBtn.addEventListener('click', async e => {
        container.classList.add('loading')
        failedImportsList.innerHTML = ''

        const config = {}
        config.images_count = document.querySelector('#images_count').value
        config.reviews_count = document.querySelector('#reviews_count').value

        const urls = document.querySelector('#urls').value.split(/\r?\n/)
        const ulrsCount = 5
        const urlsChunks = chunk(urls, ulrsCount)

        const progress = {
            total: urls.length,
            success: 0,
            fail: 0,
            failed_imports: [],
        }
        showProgress(progress)
        
        let importResponse = null
        for (let i = 0; i < urlsChunks.length; i++) { 
            try {
                importResponse = await importItems(urlsChunks[i], config)
                progress.success += importResponse.success
                progress.fail += importResponse.fail
                progress.failed_imports = progress.failed_imports.concat(importResponse.failed_imports)
            } catch {
                progress.fail += urlsChunks[i].length
                progress.failed_imports = progress.failed_imports.concat(urlsChunks[i])
            }

            showProgress(progress)
        }

        if (progress.failed_imports.length) {
            failedImportsList.innerHTML = '<h2>Failed imports</h2>'
            failedImportsList.innerHTML += progress.failed_imports.join('<br>')
            alert('Some imports failed. Try it again.')
        } else {
            alert('Successfully imported.')
        }

        progressBar.classList.remove('show')
        container.classList.remove('loading')
    })

    function importItems(urls, config) {
        const data = new FormData()
        data.append('action', 'import_items')

        urls.forEach(url => data.append('urls[]', url))
        
        for (let param in config) {
            data.append(`config[${param}]`, config[param])
        }

        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest()
            xhr.timeout = 1000 * 3600
            xhr.open('POST', '/wp-admin/admin-ajax.php')
            xhr.onload = () => {
                if (xhr.status == 200) {
                    resolve(JSON.parse(xhr.response))
                } else {
                    reject()
                }
            }
            xhr.onerror = () => reject()
            xhr.timeout = () => reject()
            xhr.send(data)
        })
    }

    function chunk(arr, length) {
        const chunks = []

        let chunk = []
        arr.forEach(el => {
            if (chunk.length == length) {
                chunks.push(chunk)
                chunk = []
            }

            chunk.push(el)
        })

        if (chunk.length) {
            chunks.push(chunk)
        }

        return chunks
    }

    function showProgress(progress) {
        progressBar.classList.add('show')
        progressBar.querySelector('#total').innerHTML = 
            `${progress.success + progress.fail} / ${progress.total}`
        progressBar.querySelector('#success').innerHTML = 
            progress.success
        progressBar.querySelector('#fail').innerHTML = 
            progress.fail
    }
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
</style>