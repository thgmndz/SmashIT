<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Catálogo de Productos</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createProductModal">
            <i class="fa fa-plus me-1"></i> Nuevo Producto
        </button>
    </div>

    <!-- Mensajes de Alerta -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-25 text-danger rounded-3" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-25 text-success rounded-3" role="alert">
            <i class="fa fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Buscador de Productos -->
    <div class="mb-4">
        <div class="glass-panel p-3 rounded-4 shadow-sm">
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa fa-search"></i></span>
                <input type="text" id="catalog_product_search" class="form-control form-control-crm" placeholder="Buscar producto por nombre, categoría o descripción...">
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio Base</th>
                        <th>Etiqueta / Badge</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay productos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($prod['image_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="rounded-3 me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-3 me-3 bg-secondary d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="fa fa-hamburger text-white-50"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-weight-semibold text-white"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 300px;"><?php echo htmlspecialchars($prod['description']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                                </td>
                                <td><strong><?php echo number_format($prod['base_price'], 0, ',', '.'); ?> Gs.</strong></td>
                                <td>
                                    <?php if (!empty($prod['badge'])): ?>
                                        <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($prod['badge']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($prod['is_available']): ?>
                                        <span class="badge bg-success"><i class="fa fa-check me-1"></i> DISPONIBLE</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fa fa-times me-1"></i> AGOTADO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-crm-primary rounded-3"
                                        onclick="openEditProductModal(
                                            '<?php echo $prod['id']; ?>',
                                            '<?php echo addslashes($prod['name']); ?>',
                                            '<?php echo $prod['base_price']; ?>',
                                            '<?php echo $prod['is_available']; ?>',
                                            '<?php echo addslashes($prod['badge'] ?? ''); ?>'
                                        )">
                                        <i class="fa fa-edit me-1"></i> Modificar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Editar Producto -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=products" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="product_edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editProductModalLabel">Actualizar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="product_edit_name" class="form-label text-muted small">Nombre del Producto</label>
                        <input type="text" id="product_edit_name" class="form-control form-control-crm" readonly>
                        <div class="form-text text-muted">El nombre del producto no se puede cambiar desde el CRM.</div>
                    </div>
                    <div class="mb-3">
                        <label for="product_edit_price" class="form-label text-muted small">Precio Base (Gs.) *</label>
                        <input type="number" name="base_price" id="product_edit_price" min="0" step="100" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_edit_badge" class="form-label text-muted small">Etiqueta / Badge (Opcional)</label>
                        <input type="text" name="badge" id="product_edit_badge" class="form-control form-control-crm" placeholder="Ej. FAVORITA o TOP SELLER">
                    </div>
                    <div class="mb-3">
                        <label for="product_edit_image" class="form-label text-muted small">Imagen del Producto</label>
                        <input type="file" name="image" id="product_edit_image" class="form-control form-control-crm" accept="image/*">
                        <div class="form-text text-muted">Suba una imagen (JPG, PNG) para actualizar la foto del producto en la tienda.</div>
                    </div>
                    <div class="form-check form-switch mt-4 p-0 ps-5">
                        <input class="form-check-input ms-n5" type="checkbox" name="is_available" id="product_edit_available" style="cursor: pointer;">
                        <label class="form-check-label text-white small" for="product_edit_available" style="cursor: pointer;">¿El producto está disponible para venta?</label>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Crear Producto -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=products" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createProductModalLabel">Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="product_create_category" class="form-label text-muted small">Categoría *</label>
                        <select name="category_id" id="product_create_category" class="form-select form-control-crm" required>
                            <option value="">Seleccione una Categoría</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_create_name" class="form-label text-muted small">Nombre del Producto *</label>
                        <input type="text" name="name" id="product_create_name" class="form-control form-control-crm" placeholder="Ej. Cheeseburger Especial" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_create_desc" class="form-label text-muted small">Descripción *</label>
                        <textarea name="description" id="product_create_desc" rows="3" class="form-control form-control-crm" placeholder="Ingredientes o detalles del producto" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="product_create_price" class="form-label text-muted small">Precio Base (Gs.) *</label>
                            <input type="number" name="base_price" id="product_create_price" min="0" step="100" class="form-control form-control-crm" required>
                        </div>
                        <div class="col-6">
                            <label for="product_create_badge" class="form-label text-muted small">Etiqueta / Badge (Opcional)</label>
                            <input type="text" name="badge" id="product_create_badge" class="form-control form-control-crm" placeholder="Ej. ¡NUEVO! o 🔥 RECOMENDADO">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="product_create_image" class="form-label text-muted small">Imagen del Producto</label>
                        <input type="file" name="image" id="product_create_image" class="form-control form-control-crm" accept="image/*">
                        <div class="form-text text-muted">Suba una imagen (JPG, PNG). Si no sube ninguna, se asignará una imagen por defecto.</div>
                    </div>
                    <div class="d-flex gap-4 mt-4">
                        <div class="form-check form-switch p-0 ps-5">
                            <input class="form-check-input ms-n5" type="checkbox" name="is_available" id="product_create_available" checked style="cursor: pointer;">
                            <label class="form-check-label text-white small" for="product_create_available" style="cursor: pointer;">Disponible para venta</label>
                        </div>
                        <div class="form-check form-switch p-0 ps-5">
                            <input class="form-check-input ms-n5" type="checkbox" name="is_featured" id="product_create_featured" style="cursor: pointer;">
                            <label class="form-check-label text-white small" for="product_create_featured" style="cursor: pointer;">Destacado</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Crear Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('catalog_product_search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.custom-table tbody tr');
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return;
                
                const name = row.querySelector('.font-weight-semibold') ? row.querySelector('.font-weight-semibold').textContent.toLowerCase() : '';
                const desc = row.querySelector('.text-muted') ? row.querySelector('.text-muted').textContent.toLowerCase() : '';
                const category = row.querySelector('.badge.bg-secondary') ? row.querySelector('.badge.bg-secondary').textContent.toLowerCase() : '';
                
                if (name.includes(query) || desc.includes(query) || category.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
