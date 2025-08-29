<!-- Main content for menu categories -->
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Menús por categoría</h2>
                <a href="/menus/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Agregar Ítem de Menú
                </a>
            </div>

            <!-- Acordeones de categorías (cerrados por defecto) -->
            <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
                <!-- Almuerzo -->
                <details class="category" data-category="almuerzo">
                    <summary class="cursor-pointer px-6 py-4 text-lg font-medium flex items-center justify-between">
                        <span>Almuerzo</span>
                        <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-xs px-2 py-0.5 count-badge hidden" data-count-for="almuerzo">0</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <div class="text-sm text-gray-500 mb-2 loading" data-loading-for="almuerzo">Cargando...</div>
                        <div class="space-y-2 items-container" data-items-for="almuerzo"></div>
                        <div class="text-sm text-gray-400 italic empty-msg hidden" data-empty-for="almuerzo">No hay ítems disponibles en esta categoría.</div>
                    </div>
                </details>
                <!-- Merienda -->
                <details class="category" data-category="merienda">
                    <summary class="cursor-pointer px-6 py-4 text-lg font-medium flex items-center justify-between">
                        <span>Merienda</span>
                        <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-xs px-2 py-0.5 count-badge hidden" data-count-for="merienda">0</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <div class="text-sm text-gray-500 mb-2 loading" data-loading-for="merienda">Cargando...</div>
                        <div class="space-y-2 items-container" data-items-for="merienda"></div>
                        <div class="text-sm text-gray-400 italic empty-msg hidden" data-empty-for="merienda">No hay ítems disponibles en esta categoría.</div>
                    </div>
                </details>
                <!-- Bebidas -->
                <details class="category" data-category="bebidas">
                    <summary class="cursor-pointer px-6 py-4 text-lg font-medium flex items-center justify-between">
                        <span>Bebidas</span>
                        <span class="ml-2 inline-flex items-center rounded-full bg-blue-50 text-blue-700 text-xs px-2 py-0.5 count-badge hidden" data-count-for="bebidas">0</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <div class="text-sm text-gray-500 mb-2 loading" data-loading-for="bebidas">Cargando...</div>
                        <div class="space-y-2 items-container" data-items-for="bebidas"></div>
                        <div class="text-sm text-gray-400 italic empty-msg hidden" data-empty-for="bebidas">No hay ítems disponibles en esta categoría.</div>
                    </div>
                </details>
                <!-- Postres -->
                <details class="category" data-category="postres">
                    <summary class="cursor-pointer px-6 py-4 text-lg font-medium flex items-center justify-between">
                        <span>Postres</span>
                        <span class="ml-2 inline-flex items-center rounded-full bg-purple-50 text-purple-700 text-xs px-2 py-0.5 count-badge hidden" data-count-for="postres">0</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <div class="text-sm text-gray-500 mb-2 loading" data-loading-for="postres">Cargando...</div>
                        <div class="space-y-2 items-container" data-items-for="postres"></div>
                        <div class="text-sm text-gray-400 italic empty-msg hidden" data-empty-for="postres">No hay ítems disponibles en esta categoría.</div>
                    </div>
                </details>
            </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cargar ítems al abrir cada acordeón, con caché por categoría
        const loaded = {};
        const containerFor = (cat) => document.querySelector(`[data-items-for="${cat}"]`);
        const loadingFor = (cat) => document.querySelector(`[data-loading-for="${cat}"]`);
        const emptyFor = (cat) => document.querySelector(`[data-empty-for="${cat}"]`);
        const countBadgeFor = (cat) => document.querySelector(`[data-count-for="${cat}"]`);
        // Prefetch de resultados para poder mostrar el contador siempre visible
        const prefetched = {};
        function setBadge(cat, count) {
            const badge = countBadgeFor(cat);
            if (badge) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            }
        }

        function renderItems(cat, items) {
            const list = containerFor(cat);
            list.innerHTML = '';
            if (!items || items.length === 0) {
                emptyFor(cat)?.classList.remove('hidden');
                setBadge(cat, 0);
                return;
            }
            emptyFor(cat)?.classList.add('hidden');
            const frag = document.createDocumentFragment();
            items.forEach((it) => {
                const row = document.createElement('div');
                row.className = 'flex items-start justify-between p-3 bg-gray-50 rounded';
                row.setAttribute('data-item-id', String(it.id));
                row.setAttribute('data-item-name', it.name ? String(it.name) : '');
                row.setAttribute('data-item-desc', it.description ? String(it.description) : '');
                row.setAttribute('data-item-price', String(it.price ?? ''));
                row.innerHTML = `
                    <div class="pr-3">
                        <div class=\"font-medium text-gray-800\">${escapeHtml(it.name || '')}</div>
                        ${it.description ? `<div class=\"text-xs text-gray-500\">${escapeHtml(it.description)}</div>` : ''}
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class=\"text-sm font-semibold text-gray-700\">₲${formatPrice(it.price)}</div>
                        <div class="flex items-center space-x-2">
                            <button type="button" class="px-2 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300 text-gray-800 btn-view">Ver</button>
                            <button type="button" class="px-2 py-1 text-xs rounded bg-indigo-600 hover:bg-indigo-700 text-white btn-edit">Editar</button>
                        </div>
                    </div>
                `;
                frag.appendChild(row);
            });
            list.appendChild(frag);
            setBadge(cat, items.length);
        }

        function formatPrice(p) {
            const n = Number(p || 0);
            return n.toLocaleString('es-PY');
        }

        function escapeHtml(s) {
            return String(s)
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
        }

        async function loadCategory(cat) {
            if (loaded[cat]) return; // usar caché
            loaded[cat] = true;
            if (loadingFor(cat)) loadingFor(cat).classList.remove('hidden');
            // Si ya tenemos datos prefeteados, renderizar sin volver a pedir.
            if (prefetched[cat]) {
                try {
                    renderItems(cat, prefetched[cat]);
                } finally {
                    if (loadingFor(cat)) loadingFor(cat).classList.add('hidden');
                }
                return;
            }
            try {
                const res = await fetch(`/api/weekly-items?category=${encodeURIComponent(cat)}`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                renderItems(cat, (data && data.items) ? data.items : []);
            } catch (e) {
                const list = containerFor(cat);
                if (list) list.innerHTML = '<div class="text-sm text-red-600">Ocurrió un error al cargar esta categoría.</div>';
                setBadge(cat, 0);
            } finally {
                if (loadingFor(cat)) loadingFor(cat).classList.add('hidden');
            }
        }

        document.querySelectorAll('details.category').forEach((details) => {
            details.addEventListener('toggle', () => {
                if (details.open) {
                    const cat = details.getAttribute('data-category');
                    if (cat) loadCategory(cat);
                }
            });
        });

        // Prefetch de conteos para que el badge sea visible aún con el acordeón cerrado
        const allCats = Array.from(document.querySelectorAll('details.category'))
            .map(d => d.getAttribute('data-category'))
            .filter(Boolean);
        allCats.forEach(async (cat) => {
            try {
                const res = await fetch(`/api/weekly-items?category=${encodeURIComponent(cat)}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json().catch(() => ({}));
                const items = (data && data.items) ? data.items : [];
                prefetched[cat] = items;
                setBadge(cat, items.length);
            } catch (_) {
                setBadge(cat, 0);
            }
        });
        // Los acordeones inician cerrados, pero ya mostramos el contador.

        // Delegación de eventos para Ver/Editar
        document.querySelectorAll('.items-container').forEach((container) => {
            container.addEventListener('click', (e) => {
                const target = e.target;
                if (!(target instanceof Element)) return;
                const row = target.closest('[data-item-id]');
                if (!row) return;
                const id = row.getAttribute('data-item-id');
                const name = row.getAttribute('data-item-name') || '';
                const desc = row.getAttribute('data-item-desc') || '';
                const price = row.getAttribute('data-item-price') || '';

                if (target.classList.contains('btn-view')) {
                    openViewModal({ id, name, desc, price });
                } else if (target.classList.contains('btn-edit')) {
                    openEditModal({ id, name, desc, price, row });
                }
            });
        });

        // Modales
        const viewModal = createModal('modal-view', `
            <div class=\"space-y-3\">
                <div><span class=\"text-xs text-gray-500\">Nombre</span><div class=\"text-sm font-medium\" id=\"mv-name\"></div></div>
                <div><span class=\"text-xs text-gray-500\">Descripción</span><div class=\"text-sm\" id=\"mv-desc\"></div></div>
                <div><span class=\"text-xs text-gray-500\">Precio</span><div class=\"text-sm\" id=\"mv-price\"></div></div>
            </div>
        `);
        const editModal = createModal('modal-edit', `
            <form id=\"edit-form\" class=\"space-y-3\">
                <input type=\"hidden\" id=\"me-id\" />
                <div>
                    <label class=\"text-xs text-gray-600\" for=\"me-name\">Nombre</label>
                    <input id=\"me-name\" type=\"text\" class=\"mt-1 w-full border rounded px-2 py-1 text-sm\" required />
                </div>
                <div>
                    <label class=\"text-xs text-gray-600\" for=\"me-desc\">Descripción</label>
                    <textarea id=\"me-desc\" class=\"mt-1 w-full border rounded px-2 py-1 text-sm\" rows=\"3\"></textarea>
                </div>
                <div>
                    <label class=\"text-xs text-gray-600\" for=\"me-price\">Precio</label>
                    <input id=\"me-price\" type=\"number\" step=\"1\" min=\"0\" class=\"mt-1 w-full border rounded px-2 py-1 text-sm\" required />
                </div>
                <div class=\"pt-2 flex items-center justify-between\">
                    <button type=\"button\" id=\"me-delete\" class=\"px-3 py-1 text-sm rounded bg-red-600 hover:bg-red-700 text-white\">Borrar</button>
                    <div class=\"flex space-x-2\">
                        <button type=\"button\" class=\"px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300\" data-close>Cancelar</button>
                        <button type=\"submit\" class=\"px-3 py-1 text-sm rounded bg-indigo-600 hover:bg-indigo-700 text-white\">Guardar</button>
                    </div>
                </div>
                <div id=\"me-error\" class=\"text-xs text-red-600 mt-1 hidden\"></div>
                <div id=\"me-delete-confirm\" class=\"mt-3 p-3 bg-red-50 border border-red-200 rounded hidden\">
                    <p class=\"text-sm text-red-700 mb-2\">¿Seguro que deseas borrar este ítem? Esta acción no se puede deshacer.</p>
                    <div class=\"flex justify-end space-x-2\">
                        <button type=\"button\" id=\"me-delete-cancel\" class=\"px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300\">Cancelar</button>
                        <button type=\"button\" id=\"me-delete-confirm-btn\" class=\"px-3 py-1 text-sm rounded bg-red-600 hover:bg-red-700 text-white\">Eliminar</button>
                    </div>
                </div>
            </form>
        `);

        function openViewModal({ id, name, desc, price }) {
            viewModal.querySelector('#mv-name').textContent = name;
            viewModal.querySelector('#mv-desc').textContent = desc || '—';
            viewModal.querySelector('#mv-price').textContent = `₲${formatPrice(price)}`;
            showModal(viewModal, 'Detalle del ítem');
        }

        let currentEditRow = null;
        function openEditModal({ id, name, desc, price, row }) {
            currentEditRow = row || null;
            editModal.querySelector('#me-id').value = id || '';
            editModal.querySelector('#me-name').value = name || '';
            editModal.querySelector('#me-desc').value = desc || '';
            editModal.querySelector('#me-price').value = price || '';
            editModal.querySelector('#me-error').classList.add('hidden');
            const delBox = editModal.querySelector('#me-delete-confirm');
            if (delBox) delBox.classList.add('hidden');
            showModal(editModal, 'Editar ítem');
        }

        // Guardar edición
        editModal.querySelector('#edit-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = editModal.querySelector('#me-id').value;
            const name = editModal.querySelector('#me-name').value.trim();
            const description = editModal.querySelector('#me-desc').value.trim();
            const price = Number(editModal.querySelector('#me-price').value);
            const err = editModal.querySelector('#me-error');
            err.classList.add('hidden');
            try {
                const res = await fetch(`/api/weekly-items/${encodeURIComponent(id)}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ name, description, price })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) throw new Error(data.message || ('HTTP ' + res.status));

                // Actualizar UI
                if (currentEditRow) {
                    currentEditRow.setAttribute('data-item-name', name);
                    currentEditRow.setAttribute('data-item-desc', description);
                    currentEditRow.setAttribute('data-item-price', String(price));
                    const nameEl = currentEditRow.querySelector('.font-medium');
                    if (nameEl) nameEl.textContent = name;
                    const descEl = currentEditRow.querySelector('.text-xs.text-gray-500');
                    if (descEl) {
                        if (description) { descEl.textContent = description; }
                        else { descEl.remove(); }
                    } else if (description) {
                        const info = document.createElement('div');
                        info.className = 'text-xs text-gray-500';
                        info.textContent = description;
                        currentEditRow.querySelector('.pr-3').appendChild(info);
                    }
                    const priceEl = currentEditRow.querySelector('.text-sm.font-semibold');
                    if (priceEl) priceEl.textContent = `₲${formatPrice(price)}`;
                }
                hideModal(editModal);
            } catch (ex) {
                err.textContent = 'No se pudo guardar los cambios. ' + ex.message;
                err.classList.remove('hidden');
            }
        });

        // Eliminar ítem - flujo de confirmación dentro del modal
        editModal.querySelector('#me-delete').addEventListener('click', () => {
            const box = editModal.querySelector('#me-delete-confirm');
            if (box) box.classList.remove('hidden');
        });
        editModal.querySelector('#me-delete-cancel').addEventListener('click', () => {
            const box = editModal.querySelector('#me-delete-confirm');
            if (box) box.classList.add('hidden');
        });
        editModal.querySelector('#me-delete-confirm-btn').addEventListener('click', async () => {
            const id = editModal.querySelector('#me-id').value;
            const err = editModal.querySelector('#me-error');
            err.classList.add('hidden');
            if (!id) return;
            try {
                const res = await fetch(`/api/weekly-items/${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) throw new Error(data.message || ('HTTP ' + res.status));

                // Quitar de la UI y actualizar contadores
                if (currentEditRow) {
                    const list = currentEditRow.closest('.items-container');
                    const details = currentEditRow.closest('details.category');
                    const cat = details ? details.getAttribute('data-category') : null;
                    currentEditRow.remove();
                    if (list && cat) {
                        const count = list.querySelectorAll('[data-item-id]').length;
                        const badge = countBadgeFor(cat);
                        if (badge) {
                            if (count > 0) {
                                badge.textContent = count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                        if (count === 0) {
                            emptyFor(cat)?.classList.remove('hidden');
                        }
                    }
                }
                hideModal(editModal);
            } catch (ex) {
                err.textContent = 'No se pudo borrar el ítem. ' + ex.message;
                err.classList.remove('hidden');
            }
        });

        // Helpers de modal
        function createModal(id, innerHtml) {
            const wrapper = document.createElement('div');
            wrapper.id = id;
            wrapper.className = 'fixed inset-0 z-50 hidden';
            wrapper.innerHTML = `
                <div class=\"absolute inset-0 bg-black bg-opacity-40\"></div>
                <div class=\"absolute inset-0 flex items-center justify-center p-4\">
                    <div class=\"bg-white rounded shadow-lg w-full max-w-md\">
                        <div class=\"px-4 py-3 border-b flex items-center justify-between\">
                            <div class=\"font-medium\" data-title>Modal</div>
                            <button type=\"button\" class=\"text-gray-500 hover:text-gray-700\" data-close>&times;</button>
                        </div>
                        <div class=\"p-4\">${innerHtml}</div>
                    </div>
                </div>
            `;
            document.body.appendChild(wrapper);
            wrapper.addEventListener('click', (e) => {
                if (e.target === wrapper || (e.target instanceof Element && e.target.hasAttribute('data-close'))) {
                    hideModal(wrapper);
                }
            });
            return wrapper;
        }

        function showModal(el, title) {
            const t = el.querySelector('[data-title]');
            if (t) t.textContent = title || 'Detalle';
            el.classList.remove('hidden');
        }
        function hideModal(el) {
            el.classList.add('hidden');
        }
    });
    </script>
