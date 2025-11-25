/*
    🚀 THƯ VIỆN AI - SCRIPT BACKEND
    Frontend sử dụng Backend PHP thay vì gọi API trực tiếp
*/

// ===== CẤU HÌNH =====
// Sử dụng BACKEND_URL từ CONFIG (đã được cấu hình trong config.js)
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

// Debug: log BACKEND_URL để kiểm tra
if (window.location.protocol === 'https:') {
    console.log('🔒 HTTPS mode - Backend URL:', BACKEND_URL);
    if (BACKEND_URL.includes('103.77.243.190') || BACKEND_URL.match(/^\d+\.\d+\.\d+\.\d+/)) {
        console.warn('⚠️ Warning: BACKEND_URL is still an IP address:', BACKEND_URL);
        // Force to domain
        const safeUrl = window.location.protocol + '//' + window.location.hostname;
        window.BACKEND_URL = safeUrl;
        console.log('✅ Fixed BACKEND_URL to:', safeUrl);
    } else {
        // Expose BACKEND_URL to window for inline scripts
        window.BACKEND_URL = BACKEND_URL;
    }
} else {
    // Expose BACKEND_URL to window for inline scripts
    window.BACKEND_URL = BACKEND_URL;
}

let currentUser = null;
let selectedCategory = '';
let selectedProvider = '';
let isTyping = false;
let conversations = [];
let uploadedDocument = null;
let currentConversation = null;

const DEFAULT_DOCUMENT_PROMPT = 'Hãy tóm tắt tài liệu này bằng tiếng Việt và liệt kê các ý chính quan trọng.';
const FILE_FORMAT_ALIASES = {
    python: 'py',
    py: 'py',
    txt: 'txt',
    text: 'txt',
    markdown: 'md',
    md: 'md',
    json: 'json',
    html: 'html',
    css: 'css',
    javascript: 'js',
    js: 'js',
    typescript: 'ts',
    ts: 'ts',
    sql: 'sql',
    shell: 'sh',
    bash: 'sh',
    sh: 'sh',
    yaml: 'yaml',
    yml: 'yaml'
};

const MIME_TYPES_BY_EXTENSION = {
    txt: 'text/plain;charset=utf-8',
    py: 'text/x-python;charset=utf-8',
    md: 'text/markdown;charset=utf-8',
    json: 'application/json;charset=utf-8',
    html: 'text/html;charset=utf-8',
    css: 'text/css;charset=utf-8',
    js: 'application/javascript;charset=utf-8',
    ts: 'application/typescript;charset=utf-8',
    sql: 'application/sql;charset=utf-8',
    sh: 'application/x-sh;charset=utf-8',
    yaml: 'text/yaml;charset=utf-8'
};

// ===== AUTHENTICATION =====
// Debug function để kiểm tra trạng thái
function debugUserStatus() {
    console.log('🔍 DEBUG USER STATUS:');
    console.log('- currentUser:', currentUser);
    console.log('- localStorage user_data:', localStorage.getItem('user_data'));
    console.log('- localStorage user_token:', localStorage.getItem('user_token'));
    
    // Kiểm tra DOM elements
    const userSection = document.getElementById('user-section');
    const authSection = document.getElementById('auth-section');
    console.log('- userSection display:', userSection ? userSection.style.display : 'not found');
    console.log('- authSection display:', authSection ? authSection.style.display : 'not found');
}

// Force reload user data
function forceReloadUser() {
    let userData = localStorage.getItem('user_data');
    if (!userData) {
        userData = localStorage.getItem('user');
    }
    if (!userData) {
        userData = localStorage.getItem('userData');
    }
    
    if (userData) {
        try {
            currentUser = JSON.parse(userData);
            console.log('🔄 Force reloaded currentUser:', currentUser);
            return true;
        } catch (error) {
            console.error('❌ Error force reloading user:', error);
            return false;
        }
    }
    return false;
}

// Kiểm tra trạng thái đăng nhập
async function checkLoginStatus() {
    try {
        // Thử tìm user data với các key khác nhau
        let userData = localStorage.getItem('user_data');
        if (!userData) {
            userData = localStorage.getItem('user');
        }
        if (!userData) {
            userData = localStorage.getItem('userData');
        }
        
        console.log('🔍 Checking login status, userData:', userData);
        
        if (userData) {
            currentUser = JSON.parse(userData);
            console.log('✅ User logged in:', currentUser);
            showUserSection();
            return true;
        }
        
        console.log('❌ No user data found');
        return false;
    } catch (error) {
        console.error('Lỗi kiểm tra đăng nhập:', error);
        return false;
    }
}

// Hiển thị section user
function showUserSection() {
    const authSection = document.getElementById('auth-section');
    const userSection = document.getElementById('user-section');
    
    if (authSection) authSection.style.display = 'none';
    if (userSection) {
        userSection.style.display = 'block';
        document.getElementById('user-name').textContent = currentUser.username;
        document.getElementById('user-credits').textContent = `${currentUser.credits || 0} credits`;
    }
}

// Đăng xuất
function logout() {
    localStorage.removeItem('user_data');
    localStorage.removeItem('user_token');
    localStorage.removeItem('token'); // Xóa cả key 'token' nếu có
    localStorage.removeItem('user');
    localStorage.removeItem('userData');
    currentUser = null;
    
    const authSection = document.getElementById('auth-section');
    const userSection = document.getElementById('user-section');
    
    if (authSection) authSection.style.display = 'block';
    if (userSection) userSection.style.display = 'none';
    
    location.reload();
}

// ===== API FUNCTIONS =====
// Gọi API với authentication
// Helper function để đảm bảo URL luôn dùng HTTPS và domain khi trang đang HTTPS
function ensureSecureUrl(url) {
    if (!url) return url;
    
    // Nếu trang đang HTTPS
    if (window.location.protocol === 'https:') {
        // Nếu URL là IP (HTTP hoặc HTTPS), chuyển sang domain
        if (url.includes('103.77.243.190') || 
            url.match(/https?:\/\/(\d{1,3}\.){3}\d{1,3}/)) {
            // Giữ lại path và query string
            try {
                const urlObj = new URL(url, window.location.origin);
                const pathAndQuery = urlObj.pathname + urlObj.search;
                return window.location.protocol + '//' + window.location.hostname + pathAndQuery;
            } catch (e) {
                // Nếu không parse được, chỉ chuyển IP sang domain
                const match = url.match(/https?:\/\/[\d.]+(.*)/);
                if (match) {
                    return window.location.protocol + '//' + window.location.hostname + match[1];
                }
                return window.location.protocol + '//' + window.location.hostname;
            }
        }
        // Đảm bảo HTTPS
        if (url.startsWith('http://') && !url.includes('localhost') && !url.includes('127.0.0.1')) {
            return url.replace('http://', 'https://');
        }
    }
    return url;
}

async function fetchAPI(url, options = {}) {
    // Đảm bảo URL luôn secure trước khi fetch
    const originalUrl = url;
    url = ensureSecureUrl(url);
    
    // Debug: log URL nếu thay đổi hoặc nếu là HTTPS
    if (window.location.protocol === 'https:') {
        if (url !== originalUrl) {
            console.log('🔒 Fixed URL:', originalUrl, '→', url);
        }
        // Always log URL in HTTPS mode for debugging
        if (originalUrl.includes('103.77.243.190') || originalUrl.match(/http:\/\/\d+\.\d+\.\d+\.\d+/) || originalUrl.match(/https:\/\/\d+\.\d+\.\d+\.\d+/)) {
            console.warn('⚠️ fetchAPI received URL with IP:', originalUrl, '→ Fixed to:', url);
        }
    }
    // Tìm token với các key khác nhau (hỗ trợ cả 'token' và 'user_token')
    let token = localStorage.getItem('user_token');
    if (!token) {
        token = localStorage.getItem('token');
    }
    
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };
    
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    
    try {
        const response = await fetch(url, {
            ...options,
            headers
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// ===== MODEL MANAGEMENT =====
// Load danh sách models từ config
async function loadModels() {
    try {
        // Sử dụng models từ config.js hoặc tạo danh sách mặc định
        let models = [];
        
        // Thử load từ window.APP_CONFIG trước
        if (window.APP_CONFIG?.MODELS && window.APP_CONFIG.MODELS.length > 0) {
            models = window.APP_CONFIG.MODELS;
        } else {
            // Tạo danh sách models mặc định nếu không có
            models = [
                'gpt-4-turbo', 'gpt-4o', 'gpt-4o-mini', 'gpt-3.5-turbo',
                'claude-3-5-sonnet', 'claude-3-haiku', 'claude-3-opus',
                'gemini-2-5-pro', 'gemini-1-5-pro', 'gemini-1-5-flash',
                'deepseek-v3', 'deepseek-coder', 'deepseek-chat',
                'qwen-2-5-72b', 'qwen-2-5-32b', 'qwen-2-5-14b',
                'llama-3-1-405b', 'llama-3-1-70b', 'llama-3-1-8b',
                'mixtral-8x7b', 'mixtral-8x22b', 'mixtral-8x3b',
                'dall-e-3', 'dall-e-2', 'midjourney', 'flux',
                'whisper-1', 'tts-1', 'tts-1-hd'
            ];
        }
        
        const modelSelect = document.getElementById('model-select');
        if (modelSelect) {
            modelSelect.innerHTML = '';
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });
        }
        
        console.log(`✅ Loaded ${models.length} models`);
        return models;
    } catch (error) {
        console.error('Lỗi load models:', error);
        return [];
    }
}

// Lọc models theo provider
function filterModels() {
    const modelSelect = document.getElementById('model-select');
    const searchInput = document.getElementById('model-search');
    
    if (!modelSelect) return;
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const options = Array.from(modelSelect.options);
    
    options.forEach(option => {
        const modelName = option.textContent.toLowerCase();
        const matchesSearch = !searchTerm || modelName.includes(searchTerm);
        const matchesProvider = !selectedProvider || modelName.includes(selectedProvider);
        
        option.style.display = matchesSearch && matchesProvider ? 'block' : 'none';
    });
}

// ===== PROVIDER FILTERING =====
// Khởi tạo provider filtering
function initProviderFiltering() {
    const providerOptions = document.querySelectorAll('.provider-option');
    
    providerOptions.forEach(option => {
        option.addEventListener('click', () => {
            // Remove active class from all options
            providerOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            option.classList.add('active');
            
            // Update selected provider
            selectedProvider = option.dataset.value || '';
            
            // Filter models
            filterModels();
        });
    });
}

// ===== SEARCH FUNCTIONALITY =====
// Khởi tạo search
function initSearch() {
    const searchInput = document.getElementById('model-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterModels);
    }
}

// ===== DOCUMENT UPLOAD =====
function formatFileSize(bytes) {
    if (typeof bytes !== 'number' || Number.isNaN(bytes)) {
        return '';
    }

    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;

    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex += 1;
    }

    const formatted = unitIndex === 0 ? Math.round(size).toString() : size.toFixed(1);
    return `${formatted} ${units[unitIndex]}`;
}

function showDocumentInfo(file) {
    const info = document.getElementById('document-info');
    const docName = document.getElementById('doc-name');

    if (!info || !docName) return;

    const sizeText = typeof file.size === 'number' ? ` (${formatFileSize(file.size)})` : '';
    docName.textContent = `${file.name}${sizeText}`;
    info.style.display = 'block';
}

function clearDocumentSelection(fileInput) {
    uploadedDocument = null;

    if (fileInput) {
        fileInput.value = '';
    }

    const info = document.getElementById('document-info');
    const docName = document.getElementById('doc-name');

    if (docName) {
        docName.textContent = '';
    }

    if (info) {
        info.style.display = 'none';
    }
}

function extractFilenameFromDisposition(disposition) {
    if (!disposition) return null;

    let match = disposition.match(/filename\*=UTF-8''([^;]+)/i);
    if (match && match[1]) {
        try {
            return decodeURIComponent(match[1]);
        } catch (error) {
            console.warn('Không thể decode filename UTF-8:', error);
        }
    }

    match = disposition.match(/filename="?([^";]+)"?/i);
    if (match && match[1]) {
        return match[1];
    }

    return null;
}

function initDocumentUpload() {
    const uploadBtn = document.getElementById('upload-btn');
    const fileInput = document.getElementById('document-upload');
    const removeBtn = document.getElementById('remove-doc');

    if (uploadBtn && fileInput) {
        uploadBtn.addEventListener('click', () => {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (event) => {
            const files = event.target.files;
            const file = files && files[0];
            if (!file) return;

            uploadedDocument = file;
            showDocumentInfo(file);
            addMessage(`📎 Đã chọn tài liệu "${file.name}". Hãy nhập yêu cầu rồi nhấn Gửi để xử lý.`, 'assistant', false);

            event.target.value = '';
        });
    }

    if (removeBtn && fileInput) {
        removeBtn.addEventListener('click', () => {
            clearDocumentSelection(fileInput);
            addMessage('📎 Đã bỏ chọn tài liệu đính kèm.', 'assistant', false);
        });
    }
}

function triggerFileDownload(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(() => {
        window.URL.revokeObjectURL(url);
    }, 1000);
}

function displayAIToolResult(result) {
    // Hàm quy về dạng tin nhắn hiển thị trong chat dựa trên kiểu dữ liệu trả về
    if (typeof result === 'string') {
        addMessage(result, 'assistant');
        return;
    }

    if (!result || typeof result !== 'object') {
        addMessage('AI đã xử lý tài liệu.', 'assistant');
        return;
    }

    const type = result.type || (typeof result.data === 'object' ? 'json' : 'text');
    const data = result.data !== undefined ? result.data : result.result;

    if (type === 'json') {
        const pretty = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
        addMessage('📄 Kết quả JSON:\n' + pretty, 'assistant');
        return;
    }

    if (type === 'file') {
        addMessage('📁 AI đã tạo file kết quả. Vui lòng kiểm tra phần tải xuống.', 'assistant');
        return;
    }

    if (type === 'text') {
        addMessage(data || 'AI đã xử lý tài liệu.', 'assistant');
        return;
    }

    if (data !== undefined && data !== null) {
        const content = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
        addMessage(content, 'assistant');
        return;
    }

    addMessage('AI đã xử lý tài liệu.', 'assistant');
}

function resolveOutputFormat(keyword) {
    if (!keyword) return null;
    const normalized = keyword.trim().toLowerCase();
    return FILE_FORMAT_ALIASES[normalized] || null;
}

function createDownloadLink(data, filename, extensionHint) {
    try {
        let blob;
        if (data instanceof Blob) {
            blob = data;
        } else {
            const ext = (extensionHint || filename.split('.').pop() || '').toLowerCase();
            const mime = MIME_TYPES_BY_EXTENSION[ext] || 'text/plain;charset=utf-8';
            blob = new Blob([String(data ?? '')], { type: mime });
        }

        const url = window.URL.createObjectURL(blob);
        const cleanup = () => {
            window.URL.revokeObjectURL(url);
        };

        return { url, filename, cleanup };
    } catch (error) {
        console.error('❌ Không thể tạo link tải file:', error);
        return null;
    }
}

function addDownloadLinkMessage(description, linkInfo) {
    if (!linkInfo) return;

    const messagesContainer = document.getElementById('chat-area');
    if (!messagesContainer) return;

    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', 'assistant');

    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';

    const paragraph = document.createElement('p');
    paragraph.appendChild(document.createTextNode(description + ' '));

    const anchor = document.createElement('a');
    anchor.href = linkInfo.url;
    anchor.download = linkInfo.filename;
    anchor.target = '_blank';
    anchor.rel = 'noopener';
    anchor.textContent = 'Tải xuống';
    paragraph.appendChild(anchor);

    contentDiv.appendChild(paragraph);
    messageDiv.appendChild(contentDiv);

    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    addMessageToConversation(`${description} (${linkInfo.filename})`, 'assistant');

    setTimeout(() => {
        linkInfo.cleanup();
    }, 5 * 60 * 1000);
}

function extractTextResult(result) {
    if (result == null) return null;
    if (typeof result === 'string') return result;

    if (typeof result === 'object') {
        if (typeof result.data === 'string') {
            return result.data;
        }
        if (result.type === 'json' && result.data) {
            try {
                return typeof result.data === 'string'
                    ? result.data
                    : JSON.stringify(result.data, null, 2);
            } catch (error) {
                console.warn('Không thể chuyển JSON thành chuỗi:', error);
            }
        }
        if (typeof result.result === 'string') {
            return result.result;
        }
    }

    return null;
}

// Tạo phần tử <div class="message-content"> với nội dung xuống dòng đúng định dạng
function createMessageContent(text) {
    const wrapper = document.createElement('div');
    wrapper.className = 'message-content';

    const paragraph = document.createElement('p');
    const lines = String(text ?? '').split('\n');
    lines.forEach((line, index) => {
        paragraph.appendChild(document.createTextNode(line));
        if (index < lines.length - 1) {
            paragraph.appendChild(document.createElement('br'));
        }
    });

    wrapper.appendChild(paragraph);
    return wrapper;
}

// Thêm tin nhắn vào chat
function addMessage(content, type, saveToHistory = true) {
    console.log('🔍 addMessage called:', { content, type, saveToHistory });
    
    const messagesContainer = document.getElementById('chat-area');
    console.log('🔍 messagesContainer:', messagesContainer);
    
    if (!messagesContainer) {
        console.log('❌ messagesContainer not found');
        return;
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message');

    const loweredType = (type || '').toLowerCase();
    if (loweredType.includes('user')) {
        messageDiv.classList.add('user');
    } else {
        messageDiv.classList.add('assistant');
    }
    if (loweredType.includes('error')) {
        messageDiv.classList.add('error');
    }

    // Create avatar for message (assistant or user)
    try {
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';

        // choose avatar icon: prefer provider-based for assistant
        if (messageDiv.classList.contains('assistant')) {
            // try to pick icon based on selected model
            let modelText = '';
            const select = document.getElementById('model-select');
            if (select && select.selectedOptions && select.selectedOptions[0]) {
                modelText = (select.selectedOptions[0].value || select.selectedOptions[0].textContent || '').toLowerCase();
            } else if (localStorage.getItem('selected_model')) {
                modelText = localStorage.getItem('selected_model').toLowerCase();
            }

            if (modelText.includes('claude') || modelText.includes('anthropic')) avatar.textContent = '🧠';
            else if (modelText.includes('gemini') || modelText.includes('google')) avatar.textContent = '🔷';
            else if (modelText.includes('dall') || modelText.includes('image') || modelText.includes('mj')) avatar.textContent = '🎨';
            else avatar.textContent = '🤖';
        } else {
            // user avatar
            // attempt to use username initial if available
            let userInitial = '';
            try {
                const user = JSON.parse(localStorage.getItem('user') || 'null');
                if (user && user.username) userInitial = String(user.username).trim().charAt(0).toUpperCase();
            } catch (e) {}
            avatar.textContent = userInitial || '👤';
        }

        // Insert avatar before content so CSS flex handles positioning (row / row-reverse)
        messageDiv.appendChild(avatar);
    } catch (e) {
        console.debug('Không thể tạo avatar cho message:', e?.message);
    }

    messageDiv.appendChild(createMessageContent(content));
    
    // If assistant message, inject a small model-info showing the currently selected model
    if (messageDiv.classList.contains('assistant')) {
        try {
            const select = document.getElementById('model-select');
            let modelText = 'Chưa chọn';
            if (select && select.selectedOptions && select.selectedOptions[0]) {
                // Prefer visible text, fall back to value
                modelText = (select.selectedOptions[0].textContent || select.selectedOptions[0].value).trim();
            } else if (window.APP_CONFIG && window.APP_CONFIG.DEFAULT_MODEL) {
                modelText = window.APP_CONFIG.DEFAULT_MODEL;
            } else if (localStorage.getItem('selected_model')) {
                modelText = localStorage.getItem('selected_model');
            }

            const modelInfo = document.createElement('div');
            modelInfo.className = 'model-info';
            const label = document.createElement('span');
            label.textContent = 'Model:';
            const name = document.createElement('strong');
            name.className = 'model-name-inline';
            name.textContent = modelText;

            modelInfo.appendChild(label);
            modelInfo.appendChild(name);

            // Insert model info into the message-content wrapper (top of message body)
            const contentWrapper = messageDiv.querySelector('.message-content');
            if (contentWrapper) {
                contentWrapper.insertBefore(modelInfo, contentWrapper.firstChild);
            } else {
                // fallback: insert at top of messageDiv
                messageDiv.insertBefore(modelInfo, messageDiv.firstChild);
            }
        } catch (e) {
            console.debug('Không thể thêm model-info vào message:', e?.message);
        }
    }
    
    console.log('🔍 Created messageDiv:', messageDiv);
    console.log('🔍 Appending to container...');
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Thêm class has-messages để ẩn welcome screen
    messagesContainer.classList.add('has-messages');
    
    // Lưu vào lịch sử nếu cần
    if (saveToHistory) {
        // Lưu lại nội dung thô để khôi phục khi người dùng mở lại lịch sử
        addMessageToConversation(content, type);
    }
    
    console.log('✅ Message added successfully');
}

// Hiển thị typing indicator
function showTypingIndicator() {
    if (isTyping) return;
    
    isTyping = true;
    const messagesContainer = document.getElementById('chat-area');
    if (!messagesContainer) return;
    
    // Build a chat-style bubble for typing indicator (avatar + message-content + typing dots)
    const typingDiv = document.createElement('div');
    typingDiv.className = 'message assistant loading';

    // Avatar
    try {
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        // pick assistant icon similar to addMessage
        let modelText = '';
        const select = document.getElementById('model-select');
        if (select && select.selectedOptions && select.selectedOptions[0]) {
            modelText = (select.selectedOptions[0].value || select.selectedOptions[0].textContent || '').toLowerCase();
        } else if (localStorage.getItem('selected_model')) {
            modelText = localStorage.getItem('selected_model').toLowerCase();
        }
        if (modelText.includes('claude') || modelText.includes('anthropic')) avatar.textContent = '🧠';
        else if (modelText.includes('gemini') || modelText.includes('google')) avatar.textContent = '🔷';
        else if (modelText.includes('dall') || modelText.includes('image') || modelText.includes('mj')) avatar.textContent = '🎨';
        else avatar.textContent = '🤖';
        typingDiv.appendChild(avatar);
    } catch (e) { console.debug('avatar for typing failed', e?.message); }

    // Message content wrapper
    const contentWrapper = document.createElement('div');
    contentWrapper.className = 'message-content';

    // Optionally add model-info inside content wrapper
    try {
        const select = document.getElementById('model-select');
        let modelText = 'Chưa chọn';
        if (select && select.selectedOptions && select.selectedOptions[0]) {
            modelText = (select.selectedOptions[0].textContent || select.selectedOptions[0].value).trim();
        } else if (localStorage.getItem('selected_model')) {
            modelText = localStorage.getItem('selected_model');
        }
        const modelInfo = document.createElement('div');
        modelInfo.className = 'model-info';
        const label = document.createElement('span'); label.textContent = 'Model:';
        const name = document.createElement('strong'); name.className = 'model-name-inline'; name.textContent = modelText;
        modelInfo.appendChild(label); modelInfo.appendChild(name);
        contentWrapper.appendChild(modelInfo);
    } catch (e) { /* ignore */ }

    // Bubble with typing dots
    const bubble = document.createElement('div');
    bubble.className = 'typing-dots';
    const statusSpan = document.createElement('span');
    statusSpan.textContent = 'AI đang xử lý...';
    statusSpan.style.marginRight = '8px';
    bubble.appendChild(statusSpan);
    for (let i=0;i<3;i++){
        const dot = document.createElement('div');
        dot.className = 'typing-dot';
        bubble.appendChild(dot);
    }

    contentWrapper.appendChild(bubble);
    typingDiv.appendChild(contentWrapper);

    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    // Also show a small header status if present
    try {
        const headerLoading = document.getElementById('loading');
        if (headerLoading) {
            console.debug('showTypingIndicator: setting header loading visible');
            headerLoading.textContent = 'AI đang xử lý...';
            // Force visible in case inline style or CSS hides it
            headerLoading.style.display = 'inline-block';
            headerLoading.style.visibility = 'visible';
            headerLoading.style.opacity = '1';
            // flash header background briefly to help visibility during debugging
            const prevBg = headerLoading.style.backgroundColor;
            headerLoading.style.backgroundColor = 'rgba(255,223,0,0.9)';
            setTimeout(() => { headerLoading.style.backgroundColor = prevBg; }, 700);
        }
    } catch (e) { console.debug('showTypingIndicator header set failed', e); }
}

// Ẩn typing indicator
function hideTypingIndicator() {
    isTyping = false;
    const loadingMessage = document.querySelector('.message.loading');
    if (loadingMessage) {
        loadingMessage.remove();
    }
    // Hide header loading indicator if present
    try {
        const headerLoading = document.getElementById('loading');
        if (headerLoading) headerLoading.style.display = 'none';
    } catch (e) { /* ignore */ }
}

// ===== CLEAR FUNCTIONALITY =====
// Xóa chat
function clearChat() {
    const messagesContainer = document.getElementById('chat-area');
    if (messagesContainer) {
        messagesContainer.innerHTML = '';
    }
}

// ===== KEYBOARD SHORTCUTS =====
// Khởi tạo keyboard shortcuts
function initKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Ctrl + Enter để gửi tin nhắn
        if (e.ctrlKey && e.key === 'Enter') {
            sendMessage();
        }
        
        // Escape để clear input
        if (e.key === 'Escape') {
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.value = '';
                messageInput.blur();
            }
        }
    });
}

// ===== INITIALIZATION =====
// Khởi tạo ứng dụng
async function init() {
    console.log('🚀 Khởi tạo Thư Viện AI...');
    
    try {
        // Kiểm tra đăng nhập
        console.log('🔍 Initializing, checking login...');
        await checkLoginStatus();
        console.log('🔍 After checkLoginStatus, currentUser:', currentUser);
        
        // Load models
        await loadModels();
        
        // Load chat history
        loadConversations();
        
        // Khởi tạo các tính năng
        initProviderFiltering();
        initSearch();
        initDocumentUpload();
        initKeyboardShortcuts();
        
        // Khởi tạo event listeners
        const sendBtn = document.getElementById('send-btn');
        const messageInput = document.getElementById('chat-input');
        const clearBtn = document.querySelector('.btn-clear');
        
        // Chat history buttons
        const newChatBtn = document.getElementById('new-chat-btn');
        const clearAllBtn = document.getElementById('clear-all-history');
        
        if (sendBtn) {
            sendBtn.addEventListener('click', sendMessage);
        }
        
        if (messageInput) {
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }
        
        // Thêm event listener cho form submit
        const chatForm = document.getElementById('chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                sendMessage();
            });
        }
        
        // Thêm event listener cho model select
        const modelSelect = document.getElementById('model-select');
        if (modelSelect) {
            modelSelect.addEventListener('change', function() {
                updateSelectedModelDisplay();
            });
        }
        
        if (clearBtn) {
            clearBtn.addEventListener('click', clearChat);
        }
        
        // Chat history event listeners
        if (newChatBtn) {
            newChatBtn.addEventListener('click', createNewConversation);
        }
        
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearAllConversations);
        }

        // Nạp lịch sử chat từ localStorage (nếu có)
        loadConversations();
        
        // Tự động xóa tất cả chat trên khung chat và hiển thị welcome screen sau mỗi lần F5
        // Sử dụng setTimeout để đảm bảo loadConversations() đã hoàn thành
        setTimeout(function() {
            const chatArea = document.getElementById('chat-area');
            if (chatArea) {
                // Xóa tất cả messages trong chat area (giữ lại welcome-screen)
                const messages = chatArea.querySelectorAll('.message');
                messages.forEach(msg => msg.remove());
                
                // Xóa class has-messages để hiển thị lại welcome screen
                chatArea.classList.remove('has-messages');
                
                // Đảm bảo welcome-screen được hiển thị
                const welcomeScreen = document.getElementById('welcome-screen');
                if (welcomeScreen) {
                    welcomeScreen.style.display = 'block';
                }
                
                console.log('🔄 Đã xóa tất cả chat và hiển thị welcome screen');
            }
            
            // Reset current conversation
            currentConversation = null;
            
            // Cập nhật lại danh sách conversations (bỏ highlight conversation cũ)
            updateConversationsList();
        }, 300);

        console.log('✅ Khởi tạo hoàn tất!');

    } catch (error) {
        console.error('❌ Lỗi khởi tạo:', error);
    }
}

// ===== START =====
document.addEventListener('DOMContentLoaded', init);

// Force sync với index.html
function forceSyncUser() {
    console.log('🔄 Force syncing user data...');
    let userData = localStorage.getItem('user_data');
    if (!userData) {
        userData = localStorage.getItem('user');
    }
    if (!userData) {
        userData = localStorage.getItem('userData');
    }
    
    console.log('🔍 Force sync userData:', userData);
    
    if (userData) {
        try {
            currentUser = JSON.parse(userData);
            console.log('✅ Force sync success - currentUser:', currentUser);
            return true;
        } catch (error) {
            console.error('❌ Force sync error:', error);
            return false;
        }
    } else {
        console.log('❌ No user data for force sync');
        return false;
    }
}

// Function để refresh credits
function refreshUserCredits() {
    const userCreditsElement = document.getElementById('user-credits');
    if (!userCreditsElement) return;
    
    // Lấy user data từ localStorage
    let userData = localStorage.getItem('user_data');
    if (!userData) {
        userData = localStorage.getItem('user');
    }
    if (!userData) {
        userData = localStorage.getItem('userData');
    }
    
    if (userData) {
        try {
            const user = JSON.parse(userData);
            userCreditsElement.textContent = (user.credits || 0) + ' credits';
            console.log('✅ Refreshed user credits:', user.credits || 0);
        } catch (error) {
            console.error('❌ Error parsing user data:', error);
        }
    }
}

// Expose debug function to global scope
window.debugUserStatus = debugUserStatus;
window.forceReloadUser = forceReloadUser;
window.forceSyncUser = forceSyncUser;
window.refreshUserCredits = refreshUserCredits;

// Set currentUser ngay khi script load
(function() {
    console.log('🚀 Script loaded, checking for user data...');
    let userData = localStorage.getItem('user_data');
    if (!userData) {
        userData = localStorage.getItem('user');
    }
    if (!userData) {
        userData = localStorage.getItem('userData');
    }
    
    console.log('🔍 Raw userData from localStorage:', userData);
    
    if (userData) {
        try {
            currentUser = JSON.parse(userData);
            console.log('✅ Set currentUser on script load:', currentUser);
        } catch (error) {
            console.error('❌ Error setting currentUser on script load:', error);
        }
    } else {
        console.log('❌ No user data found on script load');
        // Thử kiểm tra tất cả localStorage keys
        console.log('🔍 All localStorage keys:', Object.keys(localStorage));
        console.log('🔍 All localStorage values:', Object.values(localStorage));
    }
})();

// Đảm bảo currentUser được set ngay khi có thể
window.addEventListener('load', function() {
    console.log('🔄 Window loaded, checking currentUser...');
    if (!currentUser) {
        let userData = localStorage.getItem('user_data');
        if (!userData) {
            userData = localStorage.getItem('user');
        }
        if (!userData) {
            userData = localStorage.getItem('userData');
        }
        
        console.log('🔍 Window load - userData:', userData);
        if (userData) {
            try {
                currentUser = JSON.parse(userData);
                console.log('✅ Set currentUser on window load:', currentUser);
            } catch (error) {
                console.error('❌ Error setting currentUser on window load:', error);
            }
        } else {
            console.log('❌ No user data on window load');
            // Thử sync với index.html
            setTimeout(() => {
                console.log('🔄 Retrying user data sync...');
                let retryUserData = localStorage.getItem('user_data');
                if (!retryUserData) {
                    retryUserData = localStorage.getItem('user');
                }
                if (!retryUserData) {
                    retryUserData = localStorage.getItem('userData');
                }
                
                console.log('🔍 Retry userData:', retryUserData);
                if (retryUserData) {
                    try {
                        currentUser = JSON.parse(retryUserData);
                        console.log('✅ Retry success - currentUser:', currentUser);
                    } catch (error) {
                        console.error('❌ Retry error:', error);
                    }
                }
            }, 1000);
        }
    }
});

// Function để cập nhật hiển thị model đã chọn
function updateSelectedModelDisplay() {
    const selectedModel = document.getElementById('model-select');
    const chatHeader = document.querySelector('.chat-header span');
    
    if (selectedModel && chatHeader) {
        const model = selectedModel.value;
        
        if (!model || model === 'loading' || model === '') {
            chatHeader.textContent = 'Trợ lý AI Qwen (mặc định)';
        } else {
            chatHeader.textContent = `Trợ lý AI - ${model}`;
        }
    }
}

function renderConversationMessages(conversation) {
    const chatArea = document.getElementById('chat-area');
    if (!chatArea) return;

    chatArea.innerHTML = '';
    if (!conversation || !Array.isArray(conversation.messages)) {
        return;
    }

    conversation.messages.forEach((msg) => {
        addMessage(msg.content, msg.type, false);
    });
}

function updateConversationsList() {
    const list = document.getElementById('conversations-list');
    if (!list) return;

    if (!Array.isArray(conversations) || conversations.length === 0) {
        list.innerHTML = `
            <div class="no-conversations">
                <p>Chưa có cuộc trò chuyện nào</p>
                <p>Bắt đầu chat để tạo lịch sử!</p>
            </div>`;
        return;
    }

    list.innerHTML = conversations.map((conv) => {
        const activeClass = currentConversation && conv.id === currentConversation.id ? 'active' : '';
        const title = conv.title || 'Cuộc trò chuyện mới';
        const updatedAt = conv.updatedAt ? new Date(conv.updatedAt).toLocaleString() : '';
        return `
            <div class="conversation-item ${activeClass}" data-conversation-id="${conv.id}">
                <div class="conversation-title">${title}</div>
                <div class="conversation-time">${updatedAt}</div>
                <div class="conversation-messages-count">${conv.messages?.length || 0} tin nhắn</div>
            </div>`;
    }).join('');

    list.querySelectorAll('.conversation-item').forEach((item) => {
        item.addEventListener('click', () => {
            const id = item.getAttribute('data-conversation-id');
            const found = conversations.find((conv) => conv.id === id);
            if (found) {
                currentConversation = found;
                updateConversationsList();
                renderConversationMessages(found);
            }
        });
    });
}

function saveConversations() {
    try {
        localStorage.setItem('chat_conversations', JSON.stringify(conversations));
    } catch (error) {
        console.error('❌ Không thể lưu lịch sử chat:', error);
    }
}

// Load lịch sử chat từ server
async function loadChatHistoryFromServer() {
    try {
        // Tìm token với các key khác nhau
        let token = localStorage.getItem('user_token');
        if (!token) {
            token = localStorage.getItem('token');
        }
        
        if (!token) {
            console.log('⚠️ No token found, skipping server history load');
            return;
        }
        
        // Đảm bảo luôn dùng HTTPS nếu trang đang HTTPS
        let historyUrl;
        if (window.CONFIG?.API?.url) {
            historyUrl = window.CONFIG.API.url('USER_HISTORY');
            console.log('🔍 Initial history URL from CONFIG:', historyUrl);
        } else {
            historyUrl = BACKEND_URL + '/api/user/history';
            console.log('🔍 Initial history URL from BACKEND_URL:', historyUrl, '(BACKEND_URL:', BACKEND_URL, ')');
        }
        
        // Final check: đảm bảo HTTPS và domain (không dùng IP)
        if (window.location.protocol === 'https:') {
            // Nếu là IP, chuyển sang domain
            if (historyUrl.includes('103.77.243.190') || historyUrl.match(/http:\/\/\d+\.\d+\.\d+\.\d+/) || historyUrl.match(/https:\/\/\d+\.\d+\.\d+\.\d+/)) {
                console.warn('⚠️ History URL contains IP, fixing to domain');
                historyUrl = window.location.protocol + '//' + window.location.hostname + '/api/user/history';
            } else if (historyUrl.startsWith('http://') && !historyUrl.includes('localhost') && !historyUrl.includes('127.0.0.1')) {
                console.warn('⚠️ History URL is HTTP, fixing to HTTPS');
                historyUrl = historyUrl.replace('http://', 'https://');
            }
        }
        
        console.log('🔗 Chat History URL (final):', historyUrl);
        const response = await fetchAPI(historyUrl);
        
        if (response && response.success && Array.isArray(response.data?.history)) {
            const serverHistory = response.data.history;
            console.log('✅ Loaded history from server:', serverHistory.length, 'records');
            
            // Convert server history (AIQueryHistory format) sang conversations format
            // Group theo thời gian - mỗi ngày là một conversation
            const historyByDate = {};
            
            serverHistory.forEach(record => {
                const date = new Date(record.created_at);
                const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD
                
                if (!historyByDate[dateKey]) {
                    historyByDate[dateKey] = {
                        id: `server_${dateKey}_${Date.now()}`,
                        title: `Chat ${new Date(dateKey).toLocaleDateString('vi-VN')}`,
                        messages: [],
                        createdAt: dateKey,
                        updatedAt: record.created_at,
                        isFromServer: true
                    };
                }
                
                // Thêm message user
                historyByDate[dateKey].messages.push({
                    content: record.prompt || '',
                    type: 'user',
                    timestamp: record.created_at
                });
                
                // Thêm message assistant
                historyByDate[dateKey].messages.push({
                    content: record.response || '',
                    type: 'assistant',
                    model: record.model || '',
                    timestamp: record.created_at
                });
            });
            
            // Convert object thành array và sort theo ngày (mới nhất trước)
            const serverConversations = Object.values(historyByDate).sort((a, b) => {
                return new Date(b.createdAt) - new Date(a.createdAt);
            });
            
            // Merge với conversations hiện tại từ localStorage
            const localRaw = localStorage.getItem('chat_conversations');
            let localConversations = [];
            
            if (localRaw) {
                try {
                    const parsed = JSON.parse(localRaw);
                    if (Array.isArray(parsed)) {
                        // Chỉ lấy conversations không từ server (tránh duplicate)
                        localConversations = parsed.filter(conv => !conv.isFromServer);
                    }
                } catch (error) {
                    console.error('❌ Không thể parse lịch sử local:', error);
                }
            }
            
            // Merge: server conversations trước, local conversations sau
            conversations = [...serverConversations, ...localConversations];
            
            // Lưu lại vào localStorage
            saveConversations();
            
            // Không tự động render conversation đầu tiên khi load
            // Để khung chat luôn sạch và hiển thị welcome screen sau mỗi lần F5
            // Người dùng có thể click vào conversation trong danh sách nếu muốn xem lại
            // if (!currentConversation && conversations.length > 0) {
            //     currentConversation = conversations[0];
            //     renderConversationMessages(currentConversation);
            // }
            
            updateConversationsList();
            console.log('✅ Merged history: ' + serverConversations.length + ' from server, ' + localConversations.length + ' from local');
        } else {
            console.log('⚠️ No history from server or invalid format');
        }
    } catch (error) {
        console.error('❌ Lỗi load lịch sử từ server:', error);
        // Fallback về localStorage nếu không load được từ server
        loadConversationsFromLocal();
    }
}

// Load conversations chỉ từ localStorage (fallback)
function loadConversationsFromLocal() {
    const raw = localStorage.getItem('chat_conversations');
    if (raw) {
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                conversations = parsed;
            }
        } catch (error) {
            console.error('❌ Không thể parse lịch sử chat:', error);
            conversations = [];
        }
    }

    if (!Array.isArray(conversations)) {
        conversations = [];
    }

    // Không tự động render conversation đầu tiên khi load
    // Để khung chat luôn sạch và hiển thị welcome screen sau mỗi lần F5
    // Người dùng có thể click vào conversation trong danh sách nếu muốn xem lại
    // if (!currentConversation && conversations.length > 0) {
    //     currentConversation = conversations[0];
    //     renderConversationMessages(currentConversation);
    // }

    updateConversationsList();
}

// Load conversations (từ server hoặc localStorage)
function loadConversations() {
    // Thử load từ server trước, fallback về local
    loadChatHistoryFromServer().catch(() => {
        console.log('⚠️ Falling back to local history');
        loadConversationsFromLocal();
    });
}

function createNewConversation() {
    const conversation = {
        id: `conv_${Date.now()}`,
        title: 'Cuộc trò chuyện mới',
        messages: [],
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
    };

    conversations.unshift(conversation);
    currentConversation = conversation;
    saveConversations();
    updateConversationsList();
    return conversation;
}

function addMessageToConversation(content, type) {
    if (!currentConversation) {
        createNewConversation();
    }

    if (!currentConversation) {
        return;
    }

    const message = {
        content,
        type,
        timestamp: new Date().toISOString()
    };

    currentConversation.messages = currentConversation.messages || [];
    currentConversation.messages.push(message);
    currentConversation.updatedAt = message.timestamp;

    if (!currentConversation.title && type === 'user') {
        currentConversation.title = content.slice(0, 40) || 'Cuộc trò chuyện mới';
    }

    saveConversations();
    updateConversationsList();
}

function clearAllConversations() {
    if (!confirm('Bạn có chắc muốn xóa tất cả lịch sử chat?')) {
        return;
    }
    conversations = [];
    currentConversation = null;
    saveConversations();
    updateConversationsList();
    const chatArea = document.getElementById('chat-area');
    if (chatArea) {
        chatArea.innerHTML = '';
    }
}

async function sendMessage() {
    const messageInput = document.getElementById('chat-input');
    const modelSelect = document.getElementById('model-select');
    const fileInput = document.getElementById('document-upload');

    if (!messageInput || !modelSelect) return;

    const message = messageInput.value.trim();
    console.debug('sendMessage called - message length:', message.length, 'uploadedDocument present:', Boolean(uploadedDocument));
    const model = modelSelect.value;

    if (!message && !uploadedDocument) {
        alert('Vui lòng nhập tin nhắn hoặc chọn tài liệu!');
        return;
    }

    if (!currentUser) {
        const loggedIn = await checkLoginStatus();
        if (!loggedIn) {
            alert('Vui lòng đăng nhập để tiếp tục.');
            return;
        }
    }

    const formatMatch = message.match(/tạo\s+file\s+([\w.\-]+)/i);
    const resolvedFormat = formatMatch && formatMatch[1] ? resolveOutputFormat(formatMatch[1]) : null;
    const hasAttachment = Boolean(uploadedDocument);

    // Ẩn welcome screen ngay khi người dùng bắt đầu chat
    const chatArea = document.getElementById('chat-area');
    const welcomeScreen = document.getElementById('welcome-screen');
    if (chatArea) {
        chatArea.classList.add('has-messages');
    }
    if (welcomeScreen) {
        welcomeScreen.style.display = 'none';
    }

    if (message) {
        addMessage(message, 'user');
    }

    messageInput.value = '';
    showTypingIndicator();

    try {
        if (hasAttachment && uploadedDocument) {
            const docResult = await processUploadedDocument(uploadedDocument, message, {
                includeDocumentNote: true,
                outputFormat: resolvedFormat || 'auto'
            });

            if (fileInput) {
                clearDocumentSelection(fileInput);
            } else {
                uploadedDocument = null;
            }

            if (docResult && docResult.type === 'file' && docResult.blob) {
                const linkInfo = createDownloadLink(docResult.blob, docResult.filename || `ket-qua-${Date.now()}.bin`);
                if (linkInfo) {
                    addDownloadLinkMessage(`📁 AI đã tạo file ${linkInfo.filename}.`, linkInfo);
                } else {
                    addMessage('⚠️ Không thể tạo link tải file.', 'assistant error');
                }
            } else {
                const textContent = extractTextResult(docResult) || 'AI đã xử lý tài liệu.';
                addMessage(textContent, 'assistant');

                if (resolvedFormat) {
                    const filename = `ket-qua-${Date.now()}.${resolvedFormat}`;
                    const linkInfo = createDownloadLink(textContent, filename, resolvedFormat);
                    if (linkInfo) {
                        addDownloadLinkMessage(`📁 File .${resolvedFormat} đã sẵn sàng`, linkInfo);
                    }
                }
            }
        } else {
            // Đảm bảo luôn dùng HTTPS nếu trang đang HTTPS
            let chatUrl;
            if (window.CONFIG?.API?.url) {
                chatUrl = window.CONFIG.API.url('CHAT_REAL');
            } else {
                chatUrl = BACKEND_URL + '/api/chat-real.php';
            }
            
            // Final check: đảm bảo HTTPS và domain (không dùng IP)
            if (window.location.protocol === 'https:') {
                // Nếu là IP, chuyển sang domain
                if (chatUrl.includes('103.77.243.190') || chatUrl.match(/http:\/\/\d+\.\d+\.\d+\.\d+/) || chatUrl.match(/https:\/\/\d+\.\d+\.\d+\.\d+/)) {
                    chatUrl = window.location.protocol + '//' + window.location.hostname + '/api/chat-real.php';
                } else if (chatUrl.startsWith('http://') && !chatUrl.includes('localhost') && !chatUrl.includes('127.0.0.1')) {
                    chatUrl = chatUrl.replace('http://', 'https://');
                }
            }
            
            console.log('🔗 Chat URL:', chatUrl);
            const response = await fetchAPI(chatUrl, {
                method: 'POST',
                body: JSON.stringify({
                    message,
                    model: model || 'qwen3-235b-a22b',
                    user_id: currentUser.id,
                    use_qwen_default: false
                })
            });

            if (response.success) {
                const aiResponse = response.data.content || response.data.response || '';
                const finalText = aiResponse && aiResponse.trim() !== ''
                    ? aiResponse
                    : 'Xin chào! Tôi đang được cập nhật, vui lòng thử lại sau.';

                addMessage(finalText, 'assistant');

                if (resolvedFormat) {
                    const filename = `ket-qua-${Date.now()}.${resolvedFormat}`;
                    const linkInfo = createDownloadLink(finalText, filename, resolvedFormat);
                    if (linkInfo) {
                        addDownloadLinkMessage(`📁 File .${resolvedFormat} đã sẵn sàng`, linkInfo);
                    }
                }
            } else {
                addMessage('Lỗi: ' + (response.message || 'Không thể gửi tin nhắn'), 'assistant error');
            }
        }
    } catch (error) {
        console.error('❌ Lỗi khi gửi tin nhắn hoặc xử lý tài liệu:', error);
        addMessage('Lỗi kết nối: ' + error.message, 'assistant error');
    } finally {
        hideTypingIndicator();
    }
}

async function processUploadedDocument(file, promptText = '', options = {}) {
    if (!file) return null;

    if (!currentUser) {
        const loggedIn = await checkLoginStatus();
        if (!loggedIn) {
            throw new Error('Vui lòng đăng nhập để sử dụng tính năng tải tài liệu.');
        }
    }

    const { includeDocumentNote = true, outputFormat = 'auto' } = options;
    const trimmedPrompt = (promptText || '').trim();
    let finalPrompt = trimmedPrompt || DEFAULT_DOCUMENT_PROMPT.replace('tài liệu này', `tài liệu "${file.name}"`);

    if (includeDocumentNote) {
        finalPrompt = `${finalPrompt}\n\n(Tài liệu đính kèm: ${file.name})`;
    }

    const formData = new FormData();
    formData.append('file', file, file.name);
    formData.append('user_prompt', finalPrompt);
    formData.append('output_format', outputFormat || 'auto');

    // Tìm token với các key khác nhau (hỗ trợ cả 'token' và 'user_token')
    let token = localStorage.getItem('user_token');
    if (!token) {
        token = localStorage.getItem('token');
    }
    const headers = {};
    if (token) {
        formData.append('auth_token', token);
        headers['Authorization'] = `Bearer ${token}`;
    }

    // Đảm bảo luôn dùng HTTPS nếu trang đang HTTPS
    let aiToolUrl;
    if (window.CONFIG?.API?.url) {
        aiToolUrl = window.CONFIG.API.url('AI_TOOL');
        // Đảm bảo HTTPS nếu trang đang HTTPS
        if (window.location.protocol === 'https:' && aiToolUrl.startsWith('http://')) {
            aiToolUrl = aiToolUrl.replace('http://', 'https://');
        }
        // Nếu là IP, chuyển sang domain
        if (aiToolUrl.includes('103.77.243.190') || aiToolUrl.match(/^\d+\.\d+\.\d+\.\d+/)) {
            aiToolUrl = window.location.protocol + '//' + window.location.hostname + '/api/ai-tool';
        }
    } else {
        aiToolUrl = BACKEND_URL + '/api/ai-tool';
        // Đảm bảo HTTPS
        if (window.location.protocol === 'https:' && aiToolUrl.startsWith('http://')) {
            aiToolUrl = aiToolUrl.replace('http://', 'https://');
        }
    }
    const response = await fetch(aiToolUrl, {
        method: 'POST',
        headers,
        body: formData
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText || response.statusText}`);
    }

    const disposition = response.headers.get('content-disposition') || '';
    if (disposition.includes('attachment')) {
        const blob = await response.blob();
        const filename = extractFilenameFromDisposition(disposition) || `ket-qua-ai-${Date.now()}.bin`;
        return { type: 'file', blob, filename };
    }

    const contentType = response.headers.get('content-type') || '';
    let payload;

    if (contentType.includes('application/json')) {
        try {
            payload = await response.json();
        } catch (error) {
            console.warn('Không thể parse JSON, đọc text fallback:', error);
            const fallbackText = await response.text();
            payload = fallbackText;
        }
    } else {
        const rawText = await response.text();
        try {
            payload = JSON.parse(rawText);
        } catch (error) {
            payload = rawText;
        }
    }

    if (payload && typeof payload === 'object' && 'success' in payload) {
        if (!payload.success) {
            throw new Error(payload.message || 'Không thể xử lý tài liệu.');
        }

        return {
            type: payload.type || (typeof payload.data === 'object' ? 'json' : 'text'),
            data: payload.data !== undefined ? payload.data : payload.result
        };
    }

    return payload;
}