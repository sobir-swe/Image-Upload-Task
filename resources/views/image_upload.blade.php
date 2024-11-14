<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.26.1/dist/axios.min.js"></script>
    <style>
        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #FFF;
            border-bottom-color: #FF3D00;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
<div id="app" class="container mt-5">
    <h2>Image Processing</h2>

    <form @submit.prevent="submitForm">
        <div class="mb-3">
            <label for="url" class="form-label">Image URL</label>
            <input type="text" class="form-control" v-model="url" @change="validateImage" required>
        </div>

        <div class="mb-3 d-flex">
            <div class="me-3" style="flex: 1;">
                <label for="width" class="form-label">Width</label>
                <input type="number" class="form-control" v-model="width" @input="checkDimensions" required>
            </div>
            <div class="me-3" style="flex: 1;">
                <label for="height" class="form-label">Height</label>
                <input type="number" class="form-control" v-model="height" @input="checkDimensions" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="overlay_text" class="form-label">Overlay Text</label>
            <input type="text" class="form-control" v-model="overlayText" required>
        </div>

        <button type="submit" class="btn btn-primary" :disabled="imageValidationError || loading">
            <span v-if="loading" class="loader"></span>
            <span v-else>Submit</span>
        </button>

        <div v-if="imageValidationError" class="text-danger mt-2">
            The entered dimensions exceed the original image size. Please enter smaller width and height.
        </div>
    </form>

    <!-- Gallery of uploaded images -->
    <div id="gallery" class="mt-4">
        <h4>Uploaded Images:</h4>
        <div class="row row-cols-2 row-cols-md-4 g-4">
            <div class="col" v-for="image in images" :key="image.url" class="text-center">
                <img :src="image.url" class="img-fluid mb-2" :width="image.aspectRatioWidth" :height="image.aspectRatioHeight">
{{--                 <p>Aspect Ratio: {{ image.aspectRatio }}</p>--}}
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
            loading: false,
        },
        methods: {
            fetchImages() {
                axios.get('/images')
                    .then(response => {
                        if (response.data.images && response.data.images.length > 0) {
                            this.images = response.data.images.map(image => {
                                const aspectRatio = image.width / image.height;
                                return {
                                    ...image,
                                    aspectRatio: aspectRatio.toFixed(2),
                                    aspectRatioWidth: 200,
                                    aspectRatioHeight: 200 / aspectRatio
                                };
                            });
                        } else {
                            alert('No images found!');
                        }
                    })
                    .catch(error => {
                        alert('Error loading images: ' + error);
                    });
            },

            submitForm() {
                this.loading = true;
                const formData = {
                    url: this.url,
                    width: this.width,
                    height: this.height,
                    text: this.overlayText,
                    _token: '{{ csrf_token() }}'
                };

                axios.post('/images', formData)
                    .then(response => {
                        this.loading = false;
                        if (response.data.message) {
                            alert(response.data.message);
                            this.fetchImages();
                        } else {
                            alert('Error: ' + response.data.error);
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        if (error.response && error.response.data && error.response.data.error) {
                            this.imageValidationError = true;
                            alert(error.response.data.error);
                        } else {
                            alert('Error uploading image.');
                        }
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
                    alert("Rasm URL manzilini tekshirib ko'ring.");
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
