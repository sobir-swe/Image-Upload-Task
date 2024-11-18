<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rasm Yuklash va Galereya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<!-- Kun/Tungi rejim tugmasi -->
<div class="container mt-4">
    <label for="chk" class="form-label">Kun / Tungi rejim</label>
    <input type="checkbox" id="chk" aria-label="Kun / Tungi rejim">
</div>

<!-- Rasm yuklash formasi -->
<div class="container mt-4">
    <form id="imageForm">
        <div class="mb-3">
            <label for="imageUrl" class="form-label">Rasm URL</label>
            <input type="url" class="form-control" id="imageUrl" placeholder="Rasm URL kiriting" required>
        </div>
        <div class="mb-3">
            <label for="width" class="form-label">Kenglik (px)</label>
            <input type="number" class="form-control" id="width" value="200" required>
        </div>
        <div class="mb-3">
            <label for="height" class="form-label">Balandlik (px)</label>
            <input type="number" class="form-control" id="height" value="200" required>
        </div>
        <div class="mb-3">
            <label for="overlayText" class="form-label">Matn</label>
            <input type="text" class="form-control" id="overlayText" placeholder="Rasmga matn qo‘shing" required>
        </div>
        <button type="submit" class="btn btn-primary">Yuklash</button>
    </form>
</div>

<!-- Rasm galereyasi -->
<div class="container mt-4" id="gallery">
    <h3>Yuklangan Rasmlar</h3>
    <div class="row" id="imageGallery"></div>
</div>

<!-- Loader -->
<div id="loader" class="text-center" style="display:none;">
    <img src="https://i.gifer.com/7bk5.gif" alt="loading" width="50">
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
