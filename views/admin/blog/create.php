<?php
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) {
    header('Location: /admin/blog');
    exit;
}

$isEdit = isset($post) && $post;
$pageTitle = $isEdit ? 'Editar Post' : 'Crear Post';
?>

<main class="admin-main">
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $pageTitle; ?></h1>
            <div class="admin-actions">
                <a href="/admin/blog" class="btn-secondary">← Volver al listado</a>
            </div>
        </div>

        <form method="POST" action="/admin/blog/store" class="blog-form" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <!-- CORRECCIÓN 1: Campo oculto para imagen actual -->
                <?php if (!empty($post['featured_image'])): ?>
                    <input type="hidden" name="current_featured_image" value="<?php echo htmlspecialchars($post['featured_image']); ?>">
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="form-container">
                <div class="form-section">
                    <h3>Información del Post</h3>
                    
                    <div class="form-group">
                        <label for="title">Título *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            required 
                            value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>"
                            placeholder="Título del post"
                        >
                    </div>

                    <!-- CORRECCIÓN 2: Campo 'summary' corregido para que coincida con el modelo -->
                    <div class="form-group">
                        <label for="summary">Resumen</label>
                        <textarea 
                            id="summary" 
                            name="summary" 
                            rows="3"
                            placeholder="Breve descripción del post"
                        ><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select id="status" name="status">
                                <option value="draft" <?php echo (($post['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>
                                    Borrador
                                </option>
                                <option value="published" <?php echo (($post['status'] ?? '') === 'published') ? 'selected' : ''; ?>>
                                    Publicado
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="featured">Destacado</label>
                            <select id="featured" name="featured">
                                <option value="0" <?php echo (($post['featured'] ?? 0) == 0) ? 'selected' : ''; ?>>
                                    No
                                </option>
                                <option value="1" <?php echo (($post['featured'] ?? 0) == 1) ? 'selected' : ''; ?>>
                                    Sí
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- CORRECCIÓN 3: Mejorar manejo de imagen destacada -->
                    <div class="form-group">
                        <label for="featured_image">Imagen Destacada</label>
                        <input type="file" id="featured_image" name="featured_image" accept="image/*">
                        
                        <!-- Preview de imagen actual -->
                        <?php if ($isEdit && !empty($post['featured_image'])): ?>
                            <div class="current-image" id="currentImagePreview">
                                <p>Imagen actual:</p>
                                <img src="/build/img/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                     alt="Imagen destacada actual" 
                                     style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Preview para imagen nueva -->
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <p>Nueva imagen:</p>
                            <img id="previewImg" 
                                 style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px;" 
                                 alt="Preview de nueva imagen">
                        </div>
                        
                        <small>Esta imagen se usará como preview del post en el listado del blog. Formatos soportados: JPG, PNG, GIF, WebP</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Contenido del Post</h3>
                    <p class="form-help">
                        Usa los botones + alrededor de cada bloque para agregar más contenido. 
                        Selecciona cualquier bloque para usar la barra de herramientas lateral.
                    </p>
                    
                    <div id="visualEditor" class="visual-editor"></div>
                    
                    <input type="hidden" id="content_blocks" name="content_blocks" value="">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="window.location.href='/admin/blog'">
                        Cancelar
                    </button>
                    
                    <button type="submit" name="action" value="publish" class="btn-primary">
                        <?php echo $isEdit ? 'Actualizar Post' : 'Publicar Post'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- CORRECCIÓN: Pasar datos de contenido a JavaScript -->
<?php if ($isEdit && isset($content_blocks)): ?>
<script>
    // Pasar datos del contenido existente a JavaScript
    window.contentBlocks = <?php echo json_encode($content_blocks); ?>;
    window.postId = <?php echo $post['id']; ?>;
</script>
<?php endif; ?>