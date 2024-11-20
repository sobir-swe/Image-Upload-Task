<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
            width: 24px;
            height: 24px;
            border: 4px solid #fff;
            border-bottom-color: #FF3D00;
            border-radius: 50%;
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
            <label for="url" class="form-label">URL</label>
            <input type="text" class="form-control" id="url" required>
        </div>
        <div class="mb-3 d-flex">
            <div class="me-3" style="flex: 1;">
                <label for="width" class="form-label">Min Width (px)</label>
                <input type="number" class="form-control" id="width" min="200" value="200" required>
            </div>
            <div class="me-3" style="flex: 1;">
                <label for="height" class="form-label">Min Height (px)</label>
                <input type="number" class="form-control" id="height" min="200" value="200" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="overlayText" class="form-label">Overlay Text</label>
            <input type="text" class="form-control" id="overlayText" required>
        </div>
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span id="loader" class="loader" style="display: none;"></span>
            <span id="submitText">Submit</span>
        </button>
        <div id="errorMessage" class="text-danger mt-2" style="display: none;">Invalid dimensions or image size too large!</div>
    </form>

    <!-- Theme Toggle -->
    <div class="form-check form-switch mt-3">
        <input class="form-check-input" type="checkbox" id="themeToggle">
        <label class="form-check-label" for="themeToggle">Dark Mode</label>
    </div>

    <!-- Gallery Section -->
    <div id="gallery" class="mt-4">
        <h4>Uploaded Images:</h4>
        <div class="row row-cols-2 row-cols-md-4 g-4" id="imageGallery"></div>
    </div>
</div>

<script>
    let images = [];

    document.addEventListener("DOMContentLoaded", () => {
        const savedTheme = localStorage.getItem("theme") || "light-mode";
        document.body.classList.add(savedTheme);
        document.getElementById("themeToggle").checked = savedTheme === "dark-mode";

        document.getElementById("themeToggle").addEventListener("change", toggleTheme);
        fetchImages();
    });

    function toggleTheme() {
        const isDarkMode = document.getElementById("themeToggle").checked;
        document.body.className = isDarkMode ? "dark-mode" : "light-mode";
        localStorage.setItem("theme", isDarkMode ? "dark-mode" : "light-mode");
    }

    function fetchImages() {
        axios.get("/api/images")
            .then((response) => {
                images = response.data.images || [];
                displayImages();
            })
            .catch(console.error);
    }

    function displayImages() {
        const gallery = document.getElementById("imageGallery");
        gallery.innerHTML = images.map((image, index) => `
        <div class="col">
            <div class="image-container">
                <img src="${image.url}" class="img-fluid mb-2" alt="Uploaded Image">
                <div class="delete-icon" onclick="deleteImage(${index})">&times;</div>
            </div>
        </div>
    `).join("");
    }

    function submitForm(event) {
        event.preventDefault();

        const url = document.getElementById("url").value;
        const width = Math.max(200, parseInt(document.getElementById("width").value));
        const height = Math.max(200, parseInt(document.getElementById("height").value));
        const overlayText = document.getElementById("overlayText").value;

        document.getElementById("submitBtn").disabled = true;
        document.getElementById("loader").style.display = "inline-block";
        document.getElementById("submitText").style.display = "none";

        axios.post("/api/images", { url, width, height, text: overlayText })
            .then(fetchImages)
            .catch(() => {
                document.getElementById("errorMessage").style.display = "block";
            })
            .finally(() => {
                document.getElementById("submitBtn").disabled = false;
                document.getElementById("loader").style.display = "none";
                document.getElementById("submitText").style.display = "inline-block";
            });
    }

    function deleteImage(index) {
        const imageId = images[index].id;
        axios.delete(`/api/images/${imageId}`)
            .then(() => {
                images.splice(index, 1);
                displayImages();
            })
            .catch(console.error);
    }
</script>
</body>
</html>
