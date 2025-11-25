/**
 * Load Models from API
 * Tải danh sách models từ API và cập nhật UI
 */

class ModelLoader {
    constructor() {
        // Sử dụng BACKEND_URL và ENDPOINTS từ CONFIG (đã được cấu hình trong config.js)
        // Auto-detect HTTPS để tránh Mixed Content error
        function getSafeBackendUrl() {
            // Nếu trang đang HTTPS, luôn dùng domain hiện tại (không dùng IP)
            if (window.location.protocol === 'https:') {
                // Nếu có CONFIG và getSafeBackendUrl
                if (window.CONFIG?.getSafeBackendUrl) {
                    const url = window.CONFIG.getSafeBackendUrl();
                    // Double check: nếu vẫn là IP, chuyển sang domain
                    if (url && (url.includes('103.77.243.190') || url.match(/^\d+\.\d+\.\d+\.\d+/))) {
                        return window.location.protocol + '//' + window.location.hostname;
                    }
                    return url;
                }
                // Nếu có CONFIG.BACKEND_URL
                if (window.CONFIG?.BACKEND_URL) {
                    let url = window.CONFIG.BACKEND_URL;
                    // Nếu là IP, chuyển sang domain
                    if (url.includes('103.77.243.190') || url.match(/^\d+\.\d+\.\d+\.\d+/)) {
                        return window.location.protocol + '//' + window.location.hostname;
                    }
                    // Đảm bảo HTTPS
                    if (url.startsWith('http://') && !url.includes('localhost') && !url.includes('127.0.0.1')) {
                        url = url.replace('http://', 'https://');
                    }
                    return url;
                }
                // Fallback: dùng domain hiện tại với HTTPS
                return window.location.protocol + '//' + window.location.hostname;
            }
            // HTTP: dùng CONFIG hoặc origin
            return window.CONFIG?.BACKEND_URL || window.location.origin;
        }
        
        const BACKEND_URL = getSafeBackendUrl();
        this.apiUrl = window.CONFIG?.API?.url('MODELS') || BACKEND_URL + (window.CONFIG?.ENDPOINTS?.MODELS || '/api/models');
        
        // Final check: đảm bảo HTTPS và domain (không dùng IP)
        if (window.location.protocol === 'https:') {
            // Nếu là IP, chuyển sang domain
            if (this.apiUrl.includes('103.77.243.190') || this.apiUrl.match(/http:\/\/\d+\.\d+\.\d+\.\d+/) || this.apiUrl.match(/https:\/\/\d+\.\d+\.\d+\.\d+/)) {
                this.apiUrl = window.location.protocol + '//' + window.location.hostname + '/api/models';
            } else if (this.apiUrl.startsWith('http://') && !this.apiUrl.includes('localhost') && !this.apiUrl.includes('127.0.0.1')) {
                this.apiUrl = this.apiUrl.replace('http://', 'https://');
            }
        }
        
        console.log('🔗 Models API URL:', this.apiUrl);
        this.models = null;
        this.defaultModel = 'qwen3-235b-a22b';
    }

    /**
     * Load models from API
     */
    async loadModels() {
        try {
            console.log('🔄 Loading models from API...');
            const response = await fetch(this.apiUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.data) {
                this.models = data.data;
                console.log('✅ Models loaded successfully:', this.models);
                return this.models;
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('❌ Error loading models:', error);
            // Fallback to default models
            this.models = this.getDefaultModels();
            return this.models;
        }
    }

    /**
     * Get default models as fallback
     */
    getDefaultModels() {
        return {
            key4u: {
                chat: ['gpt-4-turbo', 'gpt-4o', 'claude-3-5-sonnet-20241022', 'gemini-2.0-flash'],
                image: ['dall-e-3', 'flux-kontext-max'],
                audio: ['whisper-1', 'tts-1-hd'],
                video: ['veo2', 'runwayml-gen4_turbo-10']
            },
            qwen: ['qwen3-235b-a22b', 'qwen3-30b-a3b', 'qwen3-32b'],
            default_chat_model: this.defaultModel
        };
    }

    /**
     * Generate model options HTML
     */
    generateModelOptions() {
        if (!this.models) return '';

        let html = '';
        
        // Add default Qwen option first
        html += `<option value="${this.defaultModel}" selected>🤖 Qwen3-235B (Mặc định)</option>`;
        
        // Add ensemble option
        html += `<option value="ensemble">🎯 Chế độ Ensemble</option>`;
        
        // Add separator
        html += `<option disabled>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</option>`;
        
        // Add Qwen models
        if (this.models.qwen && this.models.qwen.length > 0) {
            html += `<option disabled>🟠 QWEN MODELS</option>`;
            this.models.qwen.forEach(model => {
                const displayName = this.formatModelName(model);
                html += `<option value="${model}">${displayName}</option>`;
            });
            html += `<option disabled>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</option>`;
        }
        
        // Add Key4U models by category
        if (this.models.key4u) {
            // Chat models
            if (this.models.key4u.chat && this.models.key4u.chat.length > 0) {
                html += `<option disabled>🟢 CHAT MODELS</option>`;
                this.models.key4u.chat.slice(0, 50).forEach(model => {
                    const displayName = this.formatModelName(model);
                    html += `<option value="${model}">${displayName}</option>`;
                });
                html += `<option disabled>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</option>`;
            }
            
            // Image models
            if (this.models.key4u.image && this.models.key4u.image.length > 0) {
                html += `<option disabled>🎨 IMAGE MODELS</option>`;
                this.models.key4u.image.slice(0, 20).forEach(model => {
                    const displayName = this.formatModelName(model);
                    html += `<option value="${model}">${displayName}</option>`;
                });
                html += `<option disabled>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</option>`;
            }
            
            // Audio models
            if (this.models.key4u.audio && this.models.key4u.audio.length > 0) {
                html += `<option disabled>🎵 AUDIO MODELS</option>`;
                this.models.key4u.audio.forEach(model => {
                    const displayName = this.formatModelName(model);
                    html += `<option value="${model}">${displayName}</option>`;
                });
                html += `<option disabled>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</option>`;
            }
            
            // Video models
            if (this.models.key4u.video && this.models.key4u.video.length > 0) {
                html += `<option disabled>🎬 VIDEO MODELS</option>`;
                this.models.key4u.video.forEach(model => {
                    const displayName = this.formatModelName(model);
                    html += `<option value="${model}">${displayName}</option>`;
                });
            }
        }
        
        return html;
    }

    /**
     * Format model name for display
     */
    formatModelName(model) {
        // Convert model name to display format
        let displayName = model;
        
        // Add provider icons
        if (model.includes('gpt')) {
            displayName = `🟢 ${displayName}`;
        } else if (model.includes('claude')) {
            displayName = `🅰️ ${displayName}`;
        } else if (model.includes('gemini')) {
            displayName = `🔵 ${displayName}`;
        } else if (model.includes('qwen')) {
            displayName = `🟠 ${displayName}`;
        } else if (model.includes('flux') || model.includes('dall')) {
            displayName = `🎨 ${displayName}`;
        } else if (model.includes('whisper') || model.includes('tts')) {
            displayName = `🎵 ${displayName}`;
        } else if (model.includes('veo') || model.includes('runway') || model.includes('kling')) {
            displayName = `🎬 ${displayName}`;
        } else {
            displayName = `🤖 ${displayName}`;
        }
        
        return displayName;
    }

    /**
     * Update model select element
     */
    updateModelSelect() {
        const modelSelect = document.getElementById('model-select');
        if (!modelSelect) {
            console.error('❌ Model select element not found');
            return;
        }

        // Clear existing options
        modelSelect.innerHTML = '';
        
        // Add new options
        const optionsHtml = this.generateModelOptions();
        modelSelect.innerHTML = optionsHtml;
        
        // Set default selection
        const defaultOption = modelSelect.querySelector(`option[value="${this.defaultModel}"]`);
        if (defaultOption) {
            defaultOption.selected = true;
        }
        
        console.log('✅ Model select updated with', modelSelect.options.length, 'models');
    }

    /**
     * Initialize model loading
     */
    async init() {
        try {
            await this.loadModels();
            this.updateModelSelect();
            
            // Update model count
            this.updateModelCount();
            
            console.log('✅ Model loader initialized successfully');
        } catch (error) {
            console.error('❌ Error initializing model loader:', error);
        }
    }

    /**
     * Update model count display
     */
    updateModelCount() {
        const modelSelect = document.getElementById('model-select');
        const totalModels = document.querySelector('.total-models');
        const filteredModels = document.querySelector('.filtered-models');
        
        if (modelSelect && totalModels) {
            const total = modelSelect.options.length;
            const filtered = Array.from(modelSelect.options).filter(opt => !opt.disabled).length;
            
            totalModels.textContent = `Tổng: ${total} models`;
            if (filteredModels) {
                filteredModels.textContent = `Hiển thị: ${filtered} models`;
            }
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const modelLoader = new ModelLoader();
    modelLoader.init();
});

// Export for global use
window.ModelLoader = ModelLoader;

