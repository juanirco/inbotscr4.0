<!-- Admin Header -->
<div class="admin-header">
    <div class="admin-header-left">
        <h1>Gestión del Blog</h1>
        <div class="admin-user-info">
        </div>
    </div>
    
    <div class="admin-center">
        <a href="/admin/logout" class="btn-logout" onclick="return confirmLogout()">
            <span class="logout-icon">🚪</span>
        </a>
    </div>
    
    <div class="admin-actions">
        <a href="/admin/blog/create" class="btn-primary">Crear Post</a>
    </div>
</div>

<!-- Published Posts -->
<section class="admin-section">
    <h2>Posts Publicados (<?php echo count($posts); ?>)</h2>
    
    <?php if (empty($posts)): ?>
        <div class="empty-admin">
            <p>No hay posts publicados aún.</p>
        </div>
    <?php else: ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Slug</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                <?php if ($post['excerpt']): ?>
                                    <br><small><?php echo substr(htmlspecialchars($post['excerpt']), 0, 100) . '...'; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo $post['slug']; ?></code></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                            <td class="actions">
                                <a href="/blog/<?php echo $post['slug']; ?>" target="_blank" class="btn-view">Ver</a>
                                <a href="/admin/blog/edit?id=<?php echo $post['id']; ?>" class="btn-edit">Editar</a>
                                <form method="POST" action="/admin/blog/delete" style="display: inline;" onsubmit="return confirm('¿Estás seguro?')">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn-delete">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Draft Posts -->
<section class="admin-section">
    <h2>Borradores (<?php echo count($drafts); ?>)</h2>
    
    <?php if (empty($drafts)): ?>
        <div class="empty-admin">
            <p>No hay borradores guardados.</p>
        </div>
    <?php else: ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drafts as $draft): ?>
                        <tr class="draft-row">
                            <td>
                                <strong><?php echo htmlspecialchars($draft['title']); ?></strong>
                                <span class="draft-badge">Borrador</span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($draft['created_at'])); ?></td>
                            <td class="actions">
                                <a href="/admin/blog/edit?id=<?php echo $draft['id']; ?>" class="btn-edit">Editar</a>
                                <form method="POST" action="/admin/blog/delete" style="display: inline;" onsubmit="return confirm('¿Estás seguro?')">
                                    <input type="hidden" name="id" value="<?php echo $draft['id']; ?>">
                                    <button type="submit" class="btn-delete">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Quick Stats -->
<section class="admin-stats">
    <div class="stat-card">
        <h3>Total Posts</h3>
        <span class="stat-number"><?php echo count($posts) + count($drafts); ?></span>
    </div>
    <div class="stat-card">
        <h3>Publicados</h3>
        <span class="stat-number"><?php echo count($posts); ?></span>
    </div>
    <div class="stat-card">
        <h3>Borradores</h3>
        <span class="stat-number"><?php echo count($drafts); ?></span>
    </div>
</section>

<script>
function confirmLogout() {
    return confirm('¿Estás seguro que deseas cerrar sesión?');
}
</script>