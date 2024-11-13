<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.26.1/dist/axios.min.js"></script>
</head>
<body>
<div id="app" class="container mt-5">
    <h2>Image Processing</h2>

    <!-- Form Section -->
    <form @submit.prevent="submitForm">
        <div class="mb-3">
            <label for="url" class="form-label">Image URL</label>
            <input type="text" class="form-control" v-model="url" @change="validateImage" required>
        </div>
        <div class="mb-3">
            <label for="width" class="form-label">Width</label>
            <input type="number" class="form-control" v-model="width" @input="checkDimensions" required>
        </div>
        <div class="mb-3">
            <label for="height" class="form-label">Height</label>
            <input type="number" class="form-control" v-model="height" @input="checkDimensions" required>
        </div>
        <div class="mb-3">
            <label for="overlay_text" class="form-label">Overlay Text</label>
            <input type="text" class="form-control" v-model="overlayText" required>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="imageValidationError">Submit</button>
        <div v-if="imageValidationError" class="text-danger mt-2">
            The entered dimensions exceed the original image size. Please enter smaller width and height.
        </div>
    </form>

    <div id="gallery" class="mt-4">
        <h4>Uploaded Images:</h4>
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
                        alert('Error loading images.');
                    });
            },
            submitForm() {
                const formData = {
                    url: this.url,
                    width: this.width,
                    height: this.height,
                    overlayText: this.overlayText,
                    _token: '{{ csrf_token() }}'
                };

                axios.post('/images', formData)
                    .then(response => {
                        if (response.data.message) {
                            alert(response.data.message);
                            this.fetchImages();
                        } else {
                            alert('Error: ' + response.data.error);
                        }
                    })
                    .catch(error => {
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
                    alert("Check your image URL.");
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
