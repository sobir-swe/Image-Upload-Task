<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rasmni ishlov berish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.26.1/dist/axios.min.js"></script>
</head>
<body>
<div id="app" class="container mt-5">
    <h2>Rasmni ishlov berish</h2>

    <!-- Form Section -->
    <form @submit.prevent="submitForm">
        <div class="mb-3">
            <label for="url" class="form-label">Rasm URL</label>
            <input type="text" class="form-control" v-model="url" @change="validateImage" required>
        </div>
        <div class="mb-3">
            <label for="width" class="form-label">Kenglik</label>
            <input type="number" class="form-control" v-model="width" required>
        </div>
        <div class="mb-3">
            <label for="height" class="form-label">Balandlik</label>
            <input type="number" class="form-control" v-model="height" required>
        </div>
        <div class="mb-3">
            <label for="overlay_text" class="form-label">Yopishtirilgan matn</label>
            <input type="text" class="form-control" v-model="overlayText" required>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="imageValidationError">Yuborish</button>
        <div v-if="imageValidationError" class="text-danger mt-2">
            Iltimos, kichik width va height kiriting.
        </div>
    </form>

    <div id="gallery" class="mt-4">
        <h4>Yuklangan rasmlar:</h4>
        <div class="row row-cols-2 row-cols-md-4 g-4">
            <div class="col" v-for="image in images" :key="image.url">
                <img :src="image.url" class="img-fluid mb-2" width="200" height="200">
            </div>
        </div>
    </div>
</div>

<script>
    new Vue({
        el: '#app',
        data: {
            url: '',
            width: '',
            height: '',
            overlayText: '',
            images: [],
            imageWidth: 0,
            imageHeight: 0,
            imageValidationError: false,
        },
        methods: {
            fetchImages() {
                axios.get('/images')
                    .then(response => {
                        this.images = response.data.images;
                    })
                    .catch(error => {
                        alert('Rasmlarni yuklashda xatolik yuz berdi.');
                    });
            },
            submitForm() {
                const formData = {
                    url: this.url,
                    width: this.width,
                    height: this.height,
                    text: this.overlayText,
                    _token: '{{ csrf_token() }}'
                };

                axios.post('/images', formData)
                    .then(response => {
                        if (response.data.message) {
                            alert(response.data.message);
                            this.fetchImages(); // Update image gallery
                        } else {
                            alert('Xatolik: ' + response.data.error);
                        }
                    })
                    .catch(error => {
                        alert('Rasmni yuklashda xatolik yuz berdi.');
                    });
            },
            validateImage() {
                const image = new Image();
                image.onload = () => {
                    this.imageWidth = image.width;
                    this.imageHeight = image.height;
                    this.checkDimensions();
                };
                image.onerror = () => {
                    alert("Rasm URL manzilingizni tekshiring.");
                };
                image.src = this.url;
            },
            checkDimensions() {
                if (this.width > this.imageWidth || this.height > this.imageHeight) {
                    this.imageValidationError = true;
                } else {
                    this.imageValidationError = false;
                }
            }
        },
        mounted() {
            this.fetchImages();
        }
    });
</script>

</body>
</html>
