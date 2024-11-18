<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios@0.26.1/dist/axios.min.js"></script>
    <style>
        body {
            transition: background-color 0.5s, color 0.5s;
        }

        .dark-mode {
            background-color: #121212;
            color: white;
        }

        .light-mode {
            background-color: #ffffff;
            color: black;
        }

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

        .image-container {
            position: relative;
            display: inline-block;
        }

        .delete-icon {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            border-radius: 50%;
            cursor: pointer;
            padding: 5px;
        }
    </style>
</head>
<body class="light-mode">
<div class="container mt-5">
    <h2>Image Processing</h2>

    <form id="imageForm" onsubmit="submitForm(event)">
        <div class="mb-3">
            <label for="url" class="form-label">Site URL</label>
            <input type="text" class="form-control" id="url" required>
        </div>

        <div class="mb-3 d-flex">
            <div class="me-3" style="flex: 1;">
                <label for="width" class="form-label">MIN Width</label>
                <input type="number" class="form-control" id="width" required>
            </div>
            <div class="me-3" style="flex: 1;">
                <label for="height" class="form-label">MIN Height</label>
                <input type="number" class="form-control" id="height" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="overlay_text" class="form-label">Overlay Text</label>
            <input type="text" class="form-control" id="overlayText" required>
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span id="loader" class="loader" style="display: none;"></span>
            <span id="submitText">Submit</span>
        </button>
        <div id="errorMessage" class="text-danger mt-2" style="display: none;">
            The entered dimensions exceed the original image size. Please enter smaller width and height.
        </div>
    </form>

    <div class="fixed top-[30%] -end-2 z-50">
        <span class="relative inline-block rotate-90">
            <input type="checkbox" class="checkbox opacity-0 absolute" id="chk" />
            <label class="label bg-slate-900 dark:bg-white shadow dark:shadow-gray-700 cursor-pointer rounded-full flex justify-between items-center p-1 w-14 h-8" for="chk">
                <i data-feather="moon" class="size-[18px] text-yellow-500"></i>
                <i data-feather="sun" class="size-[18px] text-yellow-500"></i>
                <span class="ball bg-white dark:bg-slate-900 rounded-full absolute top-[2px] left-[2px] size-7"></span>
            </label>
        </span>
    </div>

    <!-- Gallery of uploaded images -->
    <div id="gallery" class="mt-4">
        <h4>Uploaded Images:</h4>
        <div class="row row-cols-2 row-cols-md-4 g-4" id="imageGallery">
            <!-- Images will be displayed here -->
        </div>
    </div>
</div>

<script>
    let images = [];

    document.getElementById('chk').addEventListener('change', () => {
        if (document.getElementById('chk').checked) {
            document.body.classList.remove('light-mode');
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
            document.body.classList.add('light-mode');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        fetchImages();
    });

    function fetchImages() {
        axios.get('/api/sites')
            .then(response => {
                if (response.data.images && response.data.images.length > 0) {
                    images = response.data.images;
                    displayImages();
                } else {
                    alert('No images found!');
                }
            })
            .catch(error => {
                alert('Error loading images: ' + error);
            });
    }

    function displayImages() {
        const gallery = document.getElementById('imageGallery');
        gallery.innerHTML = '';

        images.forEach((image, index) => {
            const aspectRatio = image.width / image.height;
            const aspectRatioWidth = 200;
            const aspectRatioHeight = 200 / aspectRatio;

            const div = document.createElement('div');
            div.classList.add('col');
            div.innerHTML = `
                <div class="image-container">
                    <img src="${image.url}" class="img-fluid mb-2" width="${aspectRatioWidth}" height="${aspectRatioHeight}">
                    <div class="delete-icon" onclick="deleteImage(${index})">&times;</div>
                </div>
            `;
            gallery.appendChild(div);
        });
    }

    function submitForm(event) {
        event.preventDefault();

        const url = document.getElementById('url').value;
        const width = document.getElementById('width').value;
        const height = document.getElementById('height').value;
        const overlayText = document.getElementById('overlayText').value;

        const formData = {
            url,
            width,
            height,
            text: overlayText
        };

        document.getElementById('submitBtn').disabled = true;
        document.getElementById('loader').style.display = 'inline-block';
        document.getElementById('submitText').style.display = 'none';

        axios.post('/api/sites', formData)
            .then(response => {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('loader').style.display = 'none';
                document.getElementById('submitText').style.display = 'inline-block';

                if (response.data.message) {
                    alert(response.data.message);
                    fetchImages();
                } else {
                    alert('Error: ' + response.data.error);
                }
            })
            .catch(error => {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('loader').style.display = 'none';
                document.getElementById('submitText').style.display = 'inline-block';

                if (error.response && error.response.data && error.response.data.error) {
                    document.getElementById('errorMessage').style.display = 'block';
                } else {
                    alert('Error uploading image.');
                }
            });
        const imageElements = document.querySelectorAll('img');
        let largeImagesCount = 0;

        imageElements.forEach(image => {
            if (image.complete) {
                if (image.naturalWidth > width && image.naturalHeight > height) {
                    largeImagesCount++;
                }
            } else {
                image.onload = () => {
                    if (image.naturalWidth > width && image.naturalHeight > height) {
                        largeImagesCount++;
                    }
                };
            }
        });
    }

    function deleteImage(index) {
        const imageId = images[index].id;

        axios.delete(`/api/sites/${imageId}`)
            .then(response => {
                alert('Image deleted successfully!');
                images.splice(index, 1);
                displayImages();
            })
            .catch(error => {
                alert('Error deleting image: ' + error);
            });
    }

    function displayProcessedImages(images) {
        const gallery = document.getElementById('imageGallery');
        gallery.innerHTML = ''; // Clear previous images

        images.forEach(image => {
            const imgElement = document.createElement('img');
            imgElement.src = image.url;
            imgElement.alt = 'Processed Image';
            imgElement.classList.add('img-fluid', 'rounded');
            gallery.appendChild(imgElement);
        });
    }

</script>
</body>
</html>
