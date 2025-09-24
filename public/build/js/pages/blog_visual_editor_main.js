import { ToolbarManager } from './ToolbarManager.js';
import { BlockManager } from './BlockManager.js';
import { ContentRenderer } from './ContentRenderer.js';
import { TextEditor } from './TextEditor.js';
import { DataManager } from './DataManager.js';

export class BlogVisualEditor {
    constructor() {
        this.editor = null;
        this.postId = window.postId || null;
        this.selectedBlock = null;
        this.selectedTextElement = null;
        
        this.toolbarManager = null;
        this.blockManager = null;
        this.contentRenderer = null;
        this.textEditor = null;
        this.dataManager = null;
        
        this.init();
    }

    init() {
        this.editor = document.getElementById('visualEditor');
        if (!this.editor) return;
        
        this.setupModules();
        this.setupInitialState();
        this.setupEventListeners();
        this.dataManager.loadExistingContent();
    }

    setupModules() {
        const callbacks = this.createCallbacks();
        
        this.toolbarManager = new ToolbarManager(this.editor, callbacks);
        this.blockManager = new BlockManager(this.editor, callbacks);
        this.contentRenderer = new ContentRenderer(callbacks);
        this.textEditor = new TextEditor(callbacks);
        this.dataManager = new DataManager(this.editor, callbacks);
        
        this.toolbarManager.createSidebarToolbar();
    }

    createCallbacks() {
        return {
            createContentBlock: (type) => this.contentRenderer.createContentBlock(type),
            setupBlockEvents: (block) => this.contentRenderer.setupBlockEvents(block),
            createRow: () => this.blockManager.createRow(),
            createColumn: () => this.blockManager.createColumn(),
            updateColumnWidths: (row) => this.blockManager.updateColumnWidths(row),
            showAddContentButton: () => this.blockManager.showAddContentButton(),
            updateContentBlocks: () => this.dataManager.updateContentBlocks(),
            updateToolbarState: () => this.toolbarManager.updateToolbarState(),
            
            applyBlockSize: (block, size) => this.blockManager.applyBlockSize(block, size),
            applyBlockAlign: (block, align) => this.blockManager.applyBlockAlign(block, align),
            duplicateBlock: (block) => this.blockManager.duplicateBlock(block),
            deleteBlock: (block) => this.blockManager.deleteBlock(block),
            
            handleTextFormat: (content, format) => this.textEditor.handleTextFormat(content, format),
            applyTextColor: (content, color) => this.textEditor.applyTextColor(content, color),
            applyTextSize: (content, size) => this.textEditor.applyTextSize(content, size),
            applyTextAlign: (element, align) => this.textEditor.applyTextAlign(element, align),
            
            replaceImage: (container) => this.contentRenderer.replaceImage(container),
            loadGifPreview: (url, preview) => this.contentRenderer.loadGifPreview(url, preview),
            loadVideoPreview: (url, preview) => this.contentRenderer.loadVideoPreview(url, preview),
            
            onBlockSelected: (block) => this.onBlockSelected(block),
            onBlockDeselected: () => this.onBlockDeselected()
        };
    }

    setupInitialState() {
        this.editor.innerHTML = '';
        this.blockManager.showAddContentButton();
    }

    setupEventListeners() {
        this.editor.addEventListener('click', (e) => {
            const block = e.target.closest('.content-block');
            if (block) {
                this.blockManager.selectBlock(block);
                
                const textContent = block.querySelector('.text-content');
                if (textContent) {
                    this.selectedTextElement = textContent;
                    this.toolbarManager.setSelectedTextElement(textContent);
                } else {
                    this.selectedTextElement = null;
                    this.toolbarManager.setSelectedTextElement(null);
                }
            }
        });

        document.addEventListener('selectionchange', () => {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const textContent = range.commonAncestorContainer.closest ? 
                    range.commonAncestorContainer.closest('.text-content') :
                    range.commonAncestorContainer.parentElement?.closest('.text-content');
                
                if (textContent && this.editor.contains(textContent)) {
                    this.selectedTextElement = textContent;
                    this.toolbarManager.setSelectedTextElement(textContent);
                    const block = textContent.closest('.content-block');
                    if (block) {
                        this.blockManager.selectBlock(block);
                    }
                }
            }
        });

        document.addEventListener('input', (e) => {
            if (this.editor.contains(e.target)) {
                this.dataManager.updateContentBlocks();
            }
        });
    }

    onBlockSelected(block) {
        this.selectedBlock = block;
        this.toolbarManager.setSelectedBlock(block);
        this.toolbarManager.updateToolbarState();
    }

    onBlockDeselected() {
        this.selectedBlock = null;
        this.selectedTextElement = null;
        this.toolbarManager.setSelectedBlock(null);
        this.toolbarManager.setSelectedTextElement(null);
        this.toolbarManager.updateToolbarState();
    }

    prepareFormSubmission() {
        this.dataManager.prepareFormSubmission();
    }
}