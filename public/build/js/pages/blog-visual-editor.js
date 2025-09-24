export class BlogVisualEditor {
    constructor() {
        this.editor = null;
        this.contentBlocks = [];
        this.postId = window.postId || null;
        this.maxColumns = 4;
        this.selectedBlock = null;
        this.selectedTextElement = null;
        this.toolbar = null;
        this.init();
    }

    init() {
        this.editor = document.getElementById('visualEditor');
        if (!this.editor) return;
        
        this.createSidebarToolbar();
        this.setupInitialState();
        this.setupEventListeners();
        this.loadExistingContent();
    }

    createSidebarToolbar() {
        // Crear contenedor de la barra lateral
        const toolbarContainer = document.createElement('div');
        toolbarContainer.className = 'editor-sidebar-toolbar';
        toolbarContainer.innerHTML = `
            <div class="toolbar-section">
                <h4>Bloques</h4>
                <div class="toolbar-group block-controls">
                    <div class="toolbar-row">
                        <label>Tamaño:</label>
                        <button type="button" class="toolbar-btn size-btn" data-size="small" title="Pequeño (50%)">S</button>
                        <button type="button" class="toolbar-btn size-btn active" data-size="medium" title="Mediano (80%)">M</button>
                        <button type="button" class="toolbar-btn size-btn" data-size="large" title="Grande (95%)">L</button>
                    </div>
                    <div class="toolbar-row">
                        <label>Alinear:</label>
                        <button type="button" class="toolbar-btn align-btn" data-align="left" title="Izquierda">◀</button>
                        <button type="button" class="toolbar-btn align-btn active" data-align="center" title="Centro">■</button>
                        <button type="button" class="toolbar-btn align-btn" data-align="right" title="Derecha">▶</button>
                    </div>
                </div>
            </div>
            
            <div class="toolbar-section">
                <h4>Texto</h4>
                <div class="toolbar-group text-controls">
                    <div class="toolbar-row">
                        <button type="button" class="toolbar-btn format-btn" data-format="bold" title="Negrita"><strong>B</strong></button>
                        <button type="button" class="toolbar-btn format-btn" data-format="italic" title="Cursiva"><em>I</em></button>
                        <button type="button" class="toolbar-btn format-btn" data-format="underline" title="Subrayado"><u>U</u></button>
                        <button type="button" class="toolbar-btn format-btn" data-format="link" title="Enlace">🔗</button>
                    </div>
                    <div class="toolbar-row">
                        <label>Color:</label>
                        <select class="toolbar-select color-select">
                            <option value="">Normal</option>
                            <option value="primary">Primario</option>
                            <option value="secondary">Secundario</option>
                            <option value="accent">Acento</option>
                            <option value="success">Éxito</option>
                            <option value="warning">Advertencia</option>
                            <option value="danger">Peligro</option>
                        </select>
                    </div>
                    <div class="toolbar-row">
                        <label>Tamaño:</label>
                        <select class="toolbar-select size-select">
                            <option value="">Normal</option>
                            <option value="small">Pequeño</option>
                            <option value="large">Grande</option>
                            <option value="xl">Extra Grande</option>
                        </select>
                    </div>
                    <div class="toolbar-row">
                        <label>Alinear texto:</label>
                        <button type="button" class="toolbar-btn text-align-btn" data-align="left" title="Izquierda">◀</button>
                        <button type="button" class="toolbar-btn text-align-btn active" data-align="center" title="Centro">■</button>
                        <button type="button" class="toolbar-btn text-align-btn" data-align="right" title="Derecha">▶</button>
                    </div>
                </div>
            </div>
            
            <div class="toolbar-section">
                <h4>Acciones</h4>
                <div class="toolbar-group action-controls">
                    <button type="button" class="toolbar-btn action-btn" data-action="duplicate" title="Duplicar bloque">📋</button>
                    <button type="button" class="toolbar-btn action-btn danger" data-action="delete" title="Eliminar bloque">🗑️</button>
                </div>
            </div>
        `;

        // Insertar la barra antes del editor
        this.editor.parentNode.insertBefore(toolbarContainer, this.editor);
        this.toolbar = toolbarContainer;

        // Inicializar como deshabilitada
        this.disableToolbar();
        this.setupToolbarEvents();
    }

    setupToolbarEvents() {
        // Botones de tamaño de bloque
        this.toolbar.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!this.selectedBlock) return;
                
                const size = btn.dataset.size;
                this.applyBlockSize(this.selectedBlock, size);
                this.updateToolbarState();
            });
        });

        // Botones de alineación de bloque
        this.toolbar.querySelectorAll('.align-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!this.selectedBlock) return;
                
                const align = btn.dataset.align;
                this.applyBlockAlign(this.selectedBlock, align);
                this.updateToolbarState();
            });
        });

        // Botones de formato de texto
        this.toolbar.querySelectorAll('.format-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!this.selectedTextElement) return;
                
                const format = btn.dataset.format;
                this.handleTextFormat(this.selectedTextElement, format);
            });
        });

        // Selectores de color y tamaño
        this.toolbar.querySelector('.color-select').addEventListener('change', (e) => {
            if (!this.selectedTextElement) return;
            this.applyTextColor(this.selectedTextElement, e.target.value);
        });

        this.toolbar.querySelector('.size-select').addEventListener('change', (e) => {
            if (!this.selectedTextElement) return;
            this.applyTextSize(this.selectedTextElement, e.target.value);
        });

        // Botones de alineación de texto
        this.toolbar.querySelectorAll('.text-align-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!this.selectedTextElement) return;
                
                const align = btn.dataset.align;
                this.applyTextAlign(this.selectedTextElement, align);
                this.updateToolbarState();
            });
        });

        // Botones de acción
        this.toolbar.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!this.selectedBlock) return;
                
                const action = btn.dataset.action;
                if (action === 'duplicate') {
                    this.duplicateBlock(this.selectedBlock);
                } else if (action === 'delete') {
                    this.deleteBlock(this.selectedBlock);
                }
            });
        });
    }

    applyBlockSize(block, size) {
        // Remover clases de tamaño existentes
        block.classList.remove('block-size-small', 'block-size-medium', 'block-size-large');
        
        // Aplicar nueva clase
        block.classList.add(`block-size-${size}`);
        this.updateContentBlocks();
    }

    applyBlockAlign(block, align) {
        // Remover clases de alineación existentes
        block.classList.remove('block-align-left', 'block-align-center', 'block-align-right');
        
        // Aplicar nueva clase
        block.classList.add(`block-align-${align}`);
        this.updateContentBlocks();
    }

    applyTextAlign(textElement, align) {
        // Remover clases de alineación existentes
        textElement.classList.remove('text-align-left', 'text-align-center', 'text-align-right');
        
        // Aplicar nueva clase
        textElement.classList.add(`text-align-${align}`);
        this.updateContentBlocks();
    }

    enableToolbar() {
        this.toolbar.classList.remove('disabled');
    }

    disableToolbar() {
        this.toolbar.classList.add('disabled');
    }

    updateToolbarState() {
        if (!this.selectedBlock) {
            this.disableToolbar();
            return;
        }

        this.enableToolbar();

        // Actualizar estado de botones de tamaño de bloque
        this.toolbar.querySelectorAll('.size-btn').forEach(btn => {
            btn.classList.remove('active');
            if (this.selectedBlock.classList.contains(`block-size-${btn.dataset.size}`)) {
                btn.classList.add('active');
            }
        });

        // Actualizar estado de botones de alineación de bloque
        this.toolbar.querySelectorAll('.align-btn').forEach(btn => {
            btn.classList.remove('active');
            if (this.selectedBlock.classList.contains(`block-align-${btn.dataset.align}`)) {
                btn.classList.add('active');
            }
        });

        // Actualizar estado de alineación de texto si hay texto seleccionado
        if (this.selectedTextElement) {
            this.toolbar.querySelectorAll('.text-align-btn').forEach(btn => {
                btn.classList.remove('active');
                if (this.selectedTextElement.classList.contains(`text-align-${btn.dataset.align}`)) {
                    btn.classList.add('active');
                }
            });
        }
    }

    setupInitialState() {
        this.editor.innerHTML = '';
        this.showAddContentButton();
    }

    showAddContentButton() {
        if (this.editor.children.length === 0) {
            const addButton = document.createElement('div');
            addButton.className = 'add-content-btn';
            addButton.innerHTML = `
                <button type="button" class="main-add-btn">
                    ➕ Agregar contenido
                </button>
                <div class="content-options" style="display: none;">
                    <button type="button" data-type="text">📝 Texto</button>
                    <button type="button" data-type="image">🖼️ Imagen</button>
                    <button type="button" data-type="code">💻 Código</button>
                    <button type="button" data-type="quote">💬 Cita</button>
                    <button type="button" data-type="video">🎥 Video</button>
                    <button type="button" data-type="gif">🎭 GIF</button>
                    <button type="button" data-type="card">📄 Card</button>
                </div>
            `;
            this.editor.appendChild(addButton);
            this.setupContentOptions(addButton);
        }
    }

    setupContentOptions(container) {
        const mainBtn = container.querySelector('.main-add-btn');
        const options = container.querySelector('.content-options');
        const optionButtons = container.querySelectorAll('.content-options button');
        
        mainBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            options.style.display = options.style.display === 'flex' ? 'none' : 'flex';
        });
        
        optionButtons.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const type = e.target.dataset.type;
                this.addContentBlock(type);
                container.remove();
            });
        });
    }

    setupEventListeners() {
        // Evento para seleccionar bloques
        this.editor.addEventListener('click', (e) => {
            const block = e.target.closest('.content-block');
            if (block) {
                this.selectBlock(block);
                
                // Si es un bloque de texto, también seleccionar el elemento de texto
                const textContent = block.querySelector('.text-content');
                if (textContent) {
                    this.selectedTextElement = textContent;
                } else {
                    this.selectedTextElement = null;
                }
                
                this.updateToolbarState();
            }
        });

        // Evento para selección de texto
        document.addEventListener('selectionchange', () => {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const textContent = range.commonAncestorContainer.closest ? 
                    range.commonAncestorContainer.closest('.text-content') :
                    range.commonAncestorContainer.parentElement?.closest('.text-content');
                
                if (textContent && this.editor.contains(textContent)) {
                    this.selectedTextElement = textContent;
                    const block = textContent.closest('.content-block');
                    if (block) {
                        this.selectBlock(block);
                        this.updateToolbarState();
                    }
                }
            }
        });

        // Actualizar content blocks en cambios
        document.addEventListener('input', (e) => {
            if (this.editor.contains(e.target)) {
                this.updateContentBlocks();
            }
        });
    }

    selectBlock(block) {
        // Remover selección anterior
        this.editor.querySelectorAll('.content-block.selected').forEach(b => {
            b.classList.remove('selected');
        });
        
        // Seleccionar nuevo bloque
        block.classList.add('selected');
        this.selectedBlock = block;
    }

    addContentBlock(type, position = 'bottom', targetRow = null, columnIndex = null) {
        const row = this.createRow();
        const block = this.createContentBlock(type);
        const column = this.createColumn();
        
        // Aplicar tamaño por defecto (80%)
        block.classList.add('block-size-medium', 'block-align-center');
        
        column.appendChild(block);
        row.appendChild(column);
        
        if (position === 'bottom' || !targetRow) {
            this.editor.appendChild(row);
        } else if (position === 'top') {
            this.editor.insertBefore(row, targetRow);
        } else if (position === 'left' || position === 'right') {
            this.addColumnToRow(targetRow, block, position, columnIndex);
            return;
        }
        
        // Seleccionar el nuevo bloque
        this.selectBlock(block);
        this.updateToolbarState();
        this.updateContentBlocks();
    }

    addColumnToRow(targetRow, block, position, columnIndex) {
        const columns = targetRow.querySelectorAll('.content-column');
        if (columns.length >= this.maxColumns) return;
        
        const newColumn = this.createColumn();
        newColumn.appendChild(block);
        
        if (position === 'left') {
            targetRow.insertBefore(newColumn, columns[columnIndex]);
        } else if (position === 'right') {
            if (columnIndex + 1 < columns.length) {
                targetRow.insertBefore(newColumn, columns[columnIndex + 1]);
            } else {
                targetRow.appendChild(newColumn);
            }
        }
        
        this.updateColumnWidths(targetRow);
        this.updateContentBlocks();
    }

    updateColumnWidths(row) {
        const columns = row.querySelectorAll('.content-column');
        const width = 100 / columns.length;
        columns.forEach(column => {
            column.style.width = `${width}%`;
        });
    }

    createRow() {
        const row = document.createElement('div');
        row.className = 'content-row';
        return row;
    }

    createColumn() {
        const column = document.createElement('div');
        column.className = 'content-column';
        return column;
    }

    createContentBlock(type) {
        const block = document.createElement('div');
        block.className = `content-block content-${type}`;
        block.dataset.type = type;
        
        switch(type) {
            case 'text':
                block.innerHTML = `
                    <div class="text-content" contenteditable="true" placeholder="Escribe tu texto aquí..."></div>
                `;
                break;
            case 'image':
                block.innerHTML = this.createImageBlock();
                break;
            case 'gif':
                block.innerHTML = this.createGifBlock();
                break;
            case 'video':
                block.innerHTML = this.createVideoBlock();
                break;
            case 'code':
                block.innerHTML = this.createCodeBlock();
                break;
            case 'quote':
                block.innerHTML = this.createQuoteBlock();
                break;
            case 'card':
                block.innerHTML = this.createCardBlock();
                break;
        }
        
        this.setupBlockEvents(block);
        return block;
    }

    createImageBlock() {
        return `
            <div class="image-upload">
                <div class="upload-placeholder">
                    <p>📸 Haz clic para subir una imagen</p>
                    <input type="file" accept="image/*" style="display: none;">
                </div>
            </div>
        `;
    }

    createGifBlock() {
        return `
            <div class="gif-embed">
                <input type="text" class="gif-url" placeholder="Pega la URL del GIF (Giphy, Tenor, o URL directa)">
                <div class="gif-preview"></div>
            </div>
        `;
    }

    createVideoBlock() {
        return `
            <div class="video-embed">
                <input type="text" class="video-url" placeholder="Pega la URL del video de YouTube">
                <div class="video-preview"></div>
            </div>
        `;
    }

    createCodeBlock() {
        return `
            <div class="code-editor">
                <div class="code-header">
                    <select>
                        <option value="javascript">JavaScript</option>
                        <option value="python">Python</option>
                        <option value="html">HTML</option>
                        <option value="css">CSS</option>
                        <option value="php">PHP</option>
                        <option value="sql">SQL</option>
                        <option value="bash">Bash</option>
                    </select>
                </div>
                <textarea placeholder="Escribe tu código aquí..."></textarea>
            </div>
        `;
    }

    createQuoteBlock() {
        return `
            <div class="quote-editor">
                <div class="quote-text" contenteditable="true" placeholder="Escribe tu cita aquí..."></div>
                <div class="quote-author" contenteditable="true" placeholder="— Autor (opcional)"></div>
            </div>
        `;
    }

    createCardBlock() {
        return `
            <div class="card-editor">
                <div class="card-header" contenteditable="true" placeholder="Título de la card..."></div>
                <div class="card-body" contenteditable="true" placeholder="Contenido de la card..."></div>
                <div class="card-footer" contenteditable="true" placeholder="Footer opcional..."></div>
            </div>
        `;
    }

    setupBlockEvents(block) {
        const type = block.dataset.type;
        
        switch(type) {
            case 'image':
                this.setupImageBlock(block);
                break;
            case 'gif':
                this.setupGifBlock(block);
                break;
            case 'video':
                this.setupVideoBlock(block);
                break;
        }
    }

    setupImageBlock(block) {
        const input = block.querySelector('input[type="file"]');
        const placeholder = block.querySelector('.upload-placeholder');
        
        placeholder.addEventListener('click', () => {
            input.click();
        });
        
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadImage(file, block.querySelector('.image-upload'));
            }
        });
    }

    setupGifBlock(block) {
        const urlInput = block.querySelector('.gif-url');
        const preview = block.querySelector('.gif-preview');
        
        urlInput.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            this.loadGifPreview(url, preview);
        });
    }

    setupVideoBlock(block) {
        const urlInput = block.querySelector('.video-url');
        const preview = block.querySelector('.video-preview');
        
        urlInput.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            this.loadVideoPreview(url, preview);
        });
    }

    uploadImage(file, container) {
        const formData = new FormData();
        formData.append('image', file);
        
        // Mostrar estado de carga
        container.innerHTML = '<div class="upload-loading"><div class="loading-spinner"></div><p>Subiendo imagen...</p></div>';
        
        fetch('/admin/upload-image', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const img = document.createElement('img');
                img.src = data.url;
                img.alt = data.filename;
                
                container.innerHTML = '';
                container.appendChild(img);
                
                // Agregar funcionalidad de reemplazo
                img.addEventListener('click', () => {
                    this.replaceImage(container);
                });
                
                this.updateContentBlocks();
            } else {
                container.innerHTML = '<div class="upload-error">Error al subir la imagen</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="upload-error">Error al subir la imagen</div>';
        });
    }

    replaceImage(imageContainer) {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadImage(file, imageContainer);
            }
        });
        
        input.click();
    }

    loadGifPreview(url, preview) {
        if (!url) {
            preview.innerHTML = '';
            return;
        }
        
        // Detectar tipo de GIF
        if (url.includes('giphy.com')) {
            const giphyId = this.extractGiphyId(url);
            if (giphyId) {
                preview.innerHTML = `<iframe src="https://giphy.com/embed/${giphyId}" width="100%" height="300" frameBorder="0"></iframe>`;
            }
        } else if (url.includes('tenor.com')) {
            const tenorId = this.extractTenorId(url);
            if (tenorId) {
                preview.innerHTML = `<iframe src="https://tenor.com/embed/${tenorId}" width="100%" height="300" frameBorder="0"></iframe>`;
            }
        } else if (url.match(/\.(gif|webp)$/i)) {
            preview.innerHTML = `<img src="${url}" alt="GIF" style="max-width: 100%; height: auto;">`;
        } else {
            preview.innerHTML = '<p style="color: var(--danger-color);">URL de GIF no válida</p>';
        }
        
        this.updateContentBlocks();
    }

    loadVideoPreview(url, preview) {
        if (!url) {
            preview.innerHTML = '';
            return;
        }
        
        const videoId = this.extractYouTubeId(url);
        if (videoId) {
            preview.innerHTML = `
                <iframe width="100%" height="315" 
                        src="https://www.youtube.com/embed/${videoId}" 
                        frameborder="0" allowfullscreen></iframe>
            `;
        } else {
            preview.innerHTML = '<p style="color: var(--danger-color);">URL de YouTube no válida</p>';
        }
        
        this.updateContentBlocks();
    }

    extractYouTubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    extractGiphyId(url) {
        const regExp = /giphy\.com\/gifs\/.*-([a-zA-Z0-9]+)(?:\?|$)/;
        const match = url.match(regExp);
        return match ? match[1] : null;
    }

    extractTenorId(url) {
        const regExp = /tenor\.com\/view\/.*-([0-9]+)(?:\?|$)/;
        const match = url.match(regExp);
        return match ? match[1] : null;
    }

    // Métodos mejorados para formato de texto
    handleTextFormat(content, action) {
        content.focus();
        
        switch(action) {
            case 'bold':
                document.execCommand('bold');
                break;
            case 'italic':
                document.execCommand('italic');
                break;
            case 'underline':
                document.execCommand('underline');
                break;
            case 'link':
                this.createLink(content);
                break;
        }
    }

    createLink(content) {
        const selection = window.getSelection();
        if (!selection.toString().trim()) {
            alert('Selecciona texto para convertir en enlace');
            return;
        }
        
        const url = prompt('Ingresa la URL:');
        if (url) {
            const range = selection.getRangeAt(0);
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            
            try {
                range.surroundContents(link);
            } catch (e) {
                const contents = range.extractContents();
                link.appendChild(contents);
                range.insertNode(link);
            }
            
            selection.removeAllRanges();
            this.updateContentBlocks();
        }
    }

    applyTextColor(content, color) {
        const selection = window.getSelection();
        if (!selection.toString().trim()) {
            return;
        }
        
        this.applyTextStyle(selection, 'color', color);
        this.updateContentBlocks();
    }

    applyTextSize(content, size) {
        const selection = window.getSelection();
        if (!selection.toString().trim()) {
            return;
        }
        
        this.applyTextStyle(selection, 'font', size);
        this.updateContentBlocks();
    }

    applyTextStyle(selection, styleType, value) {
        if (!selection.toString().trim()) return;
        
        const range = selection.getRangeAt(0);
        
        // Verificar si la selección ya está dentro de un span con estilo
        let parentSpan = range.commonAncestorContainer.nodeType === Node.TEXT_NODE ? 
            range.commonAncestorContainer.parentElement : 
            range.commonAncestorContainer;
        
        // Buscar el span más cercano que contenga estilos
        while (parentSpan && parentSpan.tagName !== 'SPAN' && !parentSpan.classList.contains('text-content')) {
            parentSpan = parentSpan.parentElement;
        }
        
        if (parentSpan && parentSpan.tagName === 'SPAN' && this.hasStyleClasses(parentSpan)) {
            // Ya existe un span con estilos, modificarlo
            this.updateExistingSpan(parentSpan, styleType, value, range);
        } else {
            // Crear nuevo span con el estilo
            this.createNewStyledSpan(range, styleType, value);
        }
        
        // Limpiar selección
        selection.removeAllRanges();
    }

    hasStyleClasses(element) {
        const styleClasses = ['color-primary', 'color-secondary', 'color-accent', 'color-success', 'color-warning', 'color-danger',
                             'font-small', 'font-normal', 'font-large', 'font-xl'];
        return styleClasses.some(className => element.classList.contains(className));
    }

    updateExistingSpan(span, styleType, value, range) {
        // Remover clases del tipo de estilo que estamos cambiando
        if (styleType === 'color') {
            span.classList.remove('color-primary', 'color-secondary', 'color-accent', 
                                 'color-success', 'color-warning', 'color-danger');
            if (value) {
                span.classList.add(`color-${value}`);
            }
        } else if (styleType === 'font') {
            span.classList.remove('font-small', 'font-normal', 'font-large', 'font-xl');
            if (value) {
                span.classList.add(`font-${value}`);
            }
        }
        
        // Si el span no tiene más clases de estilo, remover el span
        if (!this.hasStyleClasses(span)) {
            const parent = span.parentNode;
            while (span.firstChild) {
                parent.insertBefore(span.firstChild, span);
            }
            parent.removeChild(span);
        }
    }

    createNewStyledSpan(range, styleType, value) {
        if (!value) return; // No crear span si no hay valor
        
        const span = document.createElement('span');
        const className = `${styleType === 'color' ? 'color' : 'font'}-${value}`;
        span.classList.add(className);
        
        try {
            range.surroundContents(span);
        } catch (e) {
            // Fallback para selecciones complejas
            const contents = range.extractContents();
            span.appendChild(contents);
            range.insertNode(span);
        }
    }

    duplicateBlock(block) {
        const newBlock = block.cloneNode(true);
        const row = this.createRow();
        const column = this.createColumn();
        
        column.appendChild(newBlock);
        row.appendChild(column);
        
        const currentRow = block.closest('.content-row');
        currentRow.parentNode.insertBefore(row, currentRow.nextSibling);
        
        this.setupBlockEvents(newBlock);
        this.selectBlock(newBlock);
        this.updateToolbarState();
        this.updateContentBlocks();
    }

    deleteBlock(block) {
        if (confirm('¿Estás seguro de eliminar este bloque?')) {
            const row = block.closest('.content-row');
            row.remove();
            
            this.selectedBlock = null;
            this.selectedTextElement = null;
            this.disableToolbar();
            
            if (this.editor.children.length === 0) {
                this.showAddContentButton();
            }
            
            this.updateContentBlocks();
        }
    }

    loadExistingContent() {
        if (!window.contentBlocks || !Array.isArray(window.contentBlocks) || window.contentBlocks.length === 0) {
            return;
        }
        
        // Clear initial state and load existing blocks
        this.editor.innerHTML = '';
        
        window.contentBlocks.forEach((rowData, rowIndex) => {
            if (rowData.columns && Array.isArray(rowData.columns)) {
                const row = this.createRow();
                
                rowData.columns.forEach((columnData) => {
                    if (columnData.content_data) {
                        const column = this.createColumn();
                        const block = this.createContentBlockFromData(columnData.content_data);
                        if (block) {
                            column.appendChild(block);
                            row.appendChild(column);
                        }
                    }
                });
                
                if (row.children.length > 0) {
                    this.editor.appendChild(row);
                    this.updateColumnWidths(row);
                }
            }
        });
        
        if (this.editor.children.length === 0) {
            this.showAddContentButton();
        }
    }

    createContentBlockFromData(data) {
        if (!data || !data.type) return null;
        
        const block = this.createContentBlock(data.type);
        
        // Aplicar clases de tamaño y alineación si existen
        if (data.blockSize) {
            block.classList.remove('block-size-medium'); // Remover por defecto
            block.classList.add(`block-size-${data.blockSize}`);
        }
        
        if (data.blockAlign) {
            block.classList.remove('block-align-center'); // Remover por defecto
            block.classList.add(`block-align-${data.blockAlign}`);
        }
        
        // Populate block with existing data
        switch(data.type) {
            case 'text':
                const textContent = block.querySelector('.text-content');
                if (textContent && data.content) {
                    textContent.innerHTML = data.content;
                    
                    // Aplicar alineación de texto si existe
                    if (data.textAlign) {
                        textContent.classList.add(`text-align-${data.textAlign}`);
                    }
                }
                break;
                
            case 'image':
                if (data.url) {
                    const img = document.createElement('img');
                    img.src = data.url;
                    img.alt = data.alt || '';
                    
                    const imageContainer = block.querySelector('.image-upload');
                    if (imageContainer) {
                        imageContainer.innerHTML = '';
                        imageContainer.appendChild(img);
                        
                        // Agregar funcionalidad de reemplazo
                        img.addEventListener('click', () => {
                            this.replaceImage(imageContainer);
                        });
                    }
                }
                break;
                
            case 'gif':
                if (data.url) {
                    const gifEmbed = block.querySelector('.gif-embed');
                    const urlInput = gifEmbed.querySelector('.gif-url');
                    const preview = gifEmbed.querySelector('.gif-preview');
                    
                    urlInput.value = data.url;
                    this.loadGifPreview(data.url, preview);
                }
                break;
                
            case 'video':
                if (data.url) {
                    const videoEmbed = block.querySelector('.video-embed');
                    const urlInput = videoEmbed.querySelector('.video-url');
                    const preview = videoEmbed.querySelector('.video-preview');
                    
                    urlInput.value = data.url;
                    this.loadVideoPreview(data.url, preview);
                }
                break;
                
            case 'code':
                const codeTextarea = block.querySelector('textarea');
                const languageSelect = block.querySelector('select');
                if (data.content) {
                    codeTextarea.value = data.content;
                }
                if (data.language) {
                    languageSelect.value = data.language;
                }
                break;
                
            case 'quote':
                const quoteText = block.querySelector('.quote-text');
                const quoteAuthor = block.querySelector('.quote-author');
                if (data.text) {
                    quoteText.innerHTML = data.text;
                }
                if (data.author) {
                    quoteAuthor.innerHTML = data.author;
                }
                break;
                
            case 'card':
                const cardHeader = block.querySelector('.card-header');
                const cardBody = block.querySelector('.card-body');
                const cardFooter = block.querySelector('.card-footer');
                if (data.header) {
                    cardHeader.innerHTML = data.header;
                }
                if (data.body) {
                    cardBody.innerHTML = data.body;
                }
                if (data.footer) {
                    cardFooter.innerHTML = data.footer;
                }
                break;
        }
        
        return block;
    }

    updateContentBlocks() {
        this.contentBlocks = [];
        const rows = this.editor.querySelectorAll('.content-row');
        
        rows.forEach((row, rowIndex) => {
            const columns = row.querySelectorAll('.content-column');
            const rowData = {
                row: rowIndex,
                columns: []
            };
            
            columns.forEach((column, columnIndex) => {
                const block = column.querySelector('.content-block');
                if (block) {
                    const blockData = this.getBlockData(block);
                    rowData.columns.push({
                        column: columnIndex,
                        content_data: {
                            ...blockData,
                            row: rowIndex,
                            column: columnIndex
                        }
                    });
                }
            });
            
            if (rowData.columns.length > 0) {
                this.contentBlocks.push(rowData);
            }
        });
        
        // Actualizar el campo hidden del formulario
        const hiddenField = document.getElementById('content_blocks');
        if (hiddenField) {
            hiddenField.value = JSON.stringify(this.contentBlocks);
        }
    }

    getBlockData(block) {
        const data = {
            type: block.dataset.type
        };
        
        // Capturar tamaño y alineación del bloque
        if (block.classList.contains('block-size-small')) {
            data.blockSize = 'small';
        } else if (block.classList.contains('block-size-large')) {
            data.blockSize = 'large';
        } else {
            data.blockSize = 'medium';
        }
        
        if (block.classList.contains('block-align-left')) {
            data.blockAlign = 'left';
        } else if (block.classList.contains('block-align-right')) {
            data.blockAlign = 'right';
        } else {
            data.blockAlign = 'center';
        }
        
        switch(data.type) {
            case 'text':
                const textContent = block.querySelector('.text-content');
                data.content = textContent ? textContent.innerHTML : '';
                
                // Capturar alineación de texto
                if (textContent && textContent.classList.contains('text-align-left')) {
                    data.textAlign = 'left';
                } else if (textContent && textContent.classList.contains('text-align-right')) {
                    data.textAlign = 'right';
                } else {
                    data.textAlign = 'center';
                }
                break;
                
            case 'image':
                const img = block.querySelector('img');
                if (img) {
                    data.url = img.src;
                    data.alt = img.alt;
                }
                break;
                
            case 'gif':
                const gifUrl = block.querySelector('.gif-url');
                data.url = gifUrl ? gifUrl.value : '';
                break;
                
            case 'video':
                const videoUrl = block.querySelector('.video-url');
                data.url = videoUrl ? videoUrl.value : '';
                break;
                
            case 'code':
                const codeTextarea = block.querySelector('textarea');
                const languageSelect = block.querySelector('select');
                data.content = codeTextarea ? codeTextarea.value : '';
                data.language = languageSelect ? languageSelect.value : 'javascript';
                break;
                
            case 'quote':
                const quoteText = block.querySelector('.quote-text');
                const quoteAuthor = block.querySelector('.quote-author');
                data.text = quoteText ? quoteText.innerHTML : '';
                data.author = quoteAuthor ? quoteAuthor.innerHTML : '';
                break;
                
            case 'card':
                const cardHeader = block.querySelector('.card-header');
                const cardBody = block.querySelector('.card-body');
                const cardFooter = block.querySelector('.card-footer');
                data.header = cardHeader ? cardHeader.innerHTML : '';
                data.body = cardBody ? cardBody.innerHTML : '';
                data.footer = cardFooter ? cardFooter.innerHTML : '';
                break;
        }
        
        return data;
    }

    prepareFormSubmission() {
        this.updateContentBlocks();
    }
}