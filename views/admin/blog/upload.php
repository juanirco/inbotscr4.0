<!-- Upload Interface -->
<div class="admin-header">
    <h1>Subir Imagen</h1>
    <div class="admin-actions">
        <a href="/admin/blog" class="btn-secondary">← Volver al listado</a>
        <?php if ($post_id): ?>
            <a href="/admin/blog/edit?id=<?php echo $post_id; ?>" class="btn-secondary">← Volver al post</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$post_id): ?>
    <div class="error-message">
        <p>No se ha especificado un post. Debes editar un post existente o crear uno nuevo para subir imágenes.</p>
        <a href="/admin/blog/create" class="btn-primary">Crear Nuevo Post</a>
    </div>
<?php else: ?>
    <div class="upload-container">
        <div class="upload-section">
            <h3>Subir Nueva Imagen</h3>
            
            <div class="upload-area" id="uploadArea">
                <input type="file" id="imageFile" accept="image/*" hidden>
                <input type="hidden" id="postId" value="<?php echo $post_id; ?>">
                <div class="upload-content">
                    <div class="upload-icon">📁</div>
                    <p>Arrastra una imagen aquí o <button type="button" class="upload-trigger">selecciona un archivo</button></p>
                    <small>Formatos soportados: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                </div>
            </div>
            
            <div class="upload-progress" id="uploadProgress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="progressText">Subiendo...</p>
            </div>
            
            <div class="upload-result" id="uploadResult" style="display: none;">
                <div class="result-image">
                    <img id="resultImage" src="" alt="Imagen subida">
                </div>
                <div class="result-info">
                    <label>URL de la imagen:</label>
                    <input type="text" id="imageUrl" readonly>
                    <button type="button" class="btn-copy" onclick="copyUrl()">Copiar URL</button>
                </div>
                <div class="result-markdown">
                    <label>Código Markdown:</label>
                    <input type="text" id="markdownCode" readonly>
                    <button type="button" class="btn-copy" onclick="copyMarkdown()">Copiar Markdown</button>
                </div>
            </div>
        </div>
        
        <!-- Imágenes del Post -->
        <?php if (!empty($images)): ?>
        <div class="recent-images">
            <h3>Imágenes del Post</h3>
            <div class="images-grid">
                <?php foreach ($images as $image): ?>
                    <div class="image-item">
                        <img src="/build/uploads/<?php echo $image['filename']; ?>" alt="<?php echo htmlspecialchars($image['original_name']); ?>">
                        <div class="image-actions">
                            <button type="button" class="btn-copy-url" data-url="/build/uploads/<?php echo $image['filename']; ?>">Copiar URL</button>
                            <button type="button" class="btn-copy-markdown" data-markdown="![<?php echo htmlspecialchars($image['original_name']); ?>](/build/uploads/<?php echo $image['filename']; ?>)">Markdown</button>
                        </div>
                        <small><?php echo htmlspecialchars($image['original_name']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function copyUrl() {
    const urlField = document.getElementById('imageUrl');
    if (urlField) {
        urlField.select();
        document.execCommand('copy');
        alert('URL copiada al portapapeles');
    }
}

function copyMarkdown() {
    const markdownField = document.getElementById('markdownCode');
    if (markdownField) {
        markdownField.select();
        document.execCommand('copy');
        alert('Código Markdown copiado al portapapeles');
    }
}

// Event listeners para botones de galería
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-copy-url').forEach(btn => {
        btn.addEventListener('click', function() {
            navigator.clipboard.writeText(this.dataset.url);
            alert('URL copiada al portapapeles');
        });
    });

    document.querySelectorAll('.btn-copy-markdown').forEach(btn => {
        btn.addEventListener('click', function() {
            navigator.clipboard.writeText(this.dataset.markdown);
            alert('Markdown copiado al portapapeles');
        });
    });
});
</script>