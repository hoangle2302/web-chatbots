// Application State
const AppState = {
  user: null,
  isLoggedIn: false,
  currentAIModel: 'gpt-3.5-turbo',
  chatHistory: [],
  currentChatId: null,
  isLoading: false
};

// API Configuration
const API_BASE = '../backend';

// Bot responses data (fallback for offline mode)
const botResponses = [
  'Cảm ơn bạn đã chia sẻ! Về "{message}", tôi nghĩ rằng đây là một chủ đề thú vị. Bạn có muốn tôi giải thích thêm không? 🤔',
  'Thật tuyệt! "{message}" là một ý tưởng hay. Tôi có thể giúp bạn phát triển nó thêm. Bạn muốn bắt đầu từ đâu? 💡',
  'Tôi hiểu bạn đang quan tâm đến "{message}". Đây là một lĩnh vực rất thú vị! Tôi có thể chia sẻ một số thông tin hữu ích về điều này. 📚',
  '"{message}" - đây là một câu hỏi tốt! Hãy để tôi suy nghĩ và đưa ra câu trả lời chi tiết nhất cho bạn. ✨',
  'Wow! "{message}" nghe có vẻ thú vị đấy. Tôi có một số ý tưởng về điều này. Bạn có muốn tôi liệt kê ra không? 🚀'
];

// API Functions
async function apiRequest(endpoint, options = {}) {
  const url = `${API_BASE}/${endpoint}`;
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include' // Include cookies for session
  };

  try {
    const response = await fetch(url, { ...defaultOptions, ...options });
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Có lỗi xảy ra');
    }
    
    return data;
  } catch (error) {
    console.error('API Request Error:', error);
    throw error;
  }
}

// Authentication Functions
async function handleLogin(event) {
  event.preventDefault();
  
  const email = document.getElementById('loginEmail').value;
  const password = document.getElementById('loginPassword').value;
  const loginBtn = document.getElementById('loginBtn');
  const errorDiv = document.getElementById('loginError');
  
  // Reset error
  errorDiv.style.display = 'none';
  
  // Show loading
  loginBtn.disabled = true;
  loginBtn.innerHTML = '<span class="loading"></span>Đang đăng nhập...';
  
  try {
    const result = await apiRequest('pages/login.php', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
    
    if (result.success) {
      AppState.user = result.user;
      AppState.isLoggedIn = true;
      updateUIForLoggedInUser();
      closeAuth();
      showSuccessMessage('Đăng nhập thành công!');
      await loadChatHistory();
    }
  } catch (error) {
    errorDiv.textContent = error.message;
    errorDiv.style.display = 'block';
  } finally {
    loginBtn.disabled = false;
    loginBtn.innerHTML = 'Đăng Nhập';
  }
}

async function handleRegister(event) {
  event.preventDefault();
  
  const name = document.getElementById('registerName').value;
  const email = document.getElementById('registerEmail').value;
  const password = document.getElementById('registerPassword').value;
  const confirmPassword = document.getElementById('confirmPassword').value;
  
  const registerBtn = document.getElementById('registerBtn');
  const errorDiv = document.getElementById('registerError');
  const successDiv = document.getElementById('registerSuccess');
  
  // Reset messages
  errorDiv.style.display = 'none';
  successDiv.style.display = 'none';
  
  // Validate passwords
  if (password !== confirmPassword) {
    errorDiv.textContent = 'Mật khẩu xác nhận không khớp';
    errorDiv.style.display = 'block';
    return;
  }
  
  // Show loading
  registerBtn.disabled = true;
  registerBtn.innerHTML = '<span class="loading"></span>Đang đăng ký...';
  
  try {
    const result = await apiRequest('pages/register.php', {
      method: 'POST',
      body: JSON.stringify({ name, email, password })
    });
    
    if (result.success) {
      successDiv.textContent = 'Đăng ký thành công! Vui lòng đăng nhập.';
      successDiv.style.display = 'block';
      
      // Clear form
      document.getElementById('registerName').value = '';
      document.getElementById('registerEmail').value = '';
      document.getElementById('registerPassword').value = '';
      document.getElementById('confirmPassword').value = '';
      
      // Auto switch to login after 2 seconds
      setTimeout(() => {
        showLogin();
      }, 2000);
    }
  } catch (error) {
    errorDiv.textContent = error.message;
    errorDiv.style.display = 'block';
  } finally {
    registerBtn.disabled = false;
    registerBtn.innerHTML = 'Đăng Ký';
  }
}

async function handleLogout() {
  try {
    await apiRequest('pages/logout.php', { method: 'POST' });
    AppState.user = null;
    AppState.isLoggedIn = false;
    AppState.chatHistory = [];
    updateUIForGuestUser();
    showSuccessMessage('Đã đăng xuất thành công!');
  } catch (error) {
    console.error('Logout error:', error);
  }
}

async function checkAuthStatus() {
  try {
    const result = await apiRequest('api/auth-status.php');
    if (result.authenticated) {
      AppState.user = result.user;
      AppState.isLoggedIn = true;
      updateUIForLoggedInUser();
      await loadChatHistory();
    } else {
      updateUIForGuestUser();
    }
  } catch (error) {
    console.error('Auth check error:', error);
    updateUIForGuestUser();
  }
}

// Utility functions
function getCurrentTime() {
  return new Date().toLocaleTimeString('vi-VN', { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function scrollToBottom() {
  setTimeout(() => {
    const chatBox = document.getElementById('chatBox');
    if (chatBox) {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }, 100);
}

function getRandomDelay() {
  return Math.random() * 2000 + 1000; // 1-3 seconds
}

function isMobile() {
  return window.innerWidth <= 768;
}

function showSuccessMessage(message) {
  // Create temporary success notification
  const notification = document.createElement('div');
  notification.className = 'success-message';
  notification.style.position = 'fixed';
  notification.style.top = '20px';
  notification.style.right = '20px';
  notification.style.zIndex = '3000';
  notification.style.minWidth = '300px';
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 3000);
}

// UI Management Functions
function updateUIForLoggedInUser() {
  const guestMode = document.getElementById('guestMode');
  const userInfo = document.getElementById('userInfo');
  const userName = document.getElementById('userName');
  const userEmail = document.getElementById('userEmail');
  const sidebarSubtitle = document.getElementById('sidebarSubtitle');
  
  if (guestMode) guestMode.style.display = 'none';
  if (userInfo) userInfo.style.display = 'block';
  
  if (AppState.user) {
    if (userName) userName.textContent = AppState.user.name;
    if (userEmail) userEmail.textContent = AppState.user.email;
    if (sidebarSubtitle) sidebarSubtitle.textContent = `Xin chào, ${AppState.user.name}!`;
  }
}

function updateUIForGuestUser() {
  const guestMode = document.getElementById('guestMode');
  const userInfo = document.getElementById('userInfo');
  const sidebarSubtitle = document.getElementById('sidebarSubtitle');
  
  if (guestMode) guestMode.style.display = 'block';
  if (userInfo) userInfo.style.display = 'none';
  if (sidebarSubtitle) sidebarSubtitle.textContent = 'Your intelligent companion';
}

function showLogin() {
  const authOverlay = document.getElementById('authOverlay');
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  
  if (authOverlay && loginForm && registerForm) {
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
    authOverlay.classList.add('show');
    
    // Clear any previous errors
    const errorDiv = document.getElementById('loginError');
    if (errorDiv) errorDiv.style.display = 'none';
    
    // Focus email input
    setTimeout(() => {
      const emailInput = document.getElementById('loginEmail');
      if (emailInput) emailInput.focus();
    }, 300);
  }
}

function showRegister() {
  const authOverlay = document.getElementById('authOverlay');
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  
  if (authOverlay && loginForm && registerForm) {
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    authOverlay.classList.add('show');
    
    // Clear any previous messages
    const errorDiv = document.getElementById('registerError');
    const successDiv = document.getElementById('registerSuccess');
    if (errorDiv) errorDiv.style.display = 'none';
    if (successDiv) successDiv.style.display = 'none';
    
    // Focus name input
    setTimeout(() => {
      const nameInput = document.getElementById('registerName');
      if (nameInput) nameInput.focus();
    }, 300);
  }
}

function closeAuth() {
  const authOverlay = document.getElementById('authOverlay');
  if (authOverlay) {
    authOverlay.classList.remove('show');
  }
}

function toggleUserDropdown() {
  const dropdown = document.getElementById('userDropdown');
  if (dropdown) {
    dropdown.classList.toggle('show');
  }
}

function showProfile() {
  toggleUserDropdown();
  alert('Tính năng Hồ sơ cá nhân đang được phát triển!');
}

function showSettings() {
  toggleUserDropdown();
  alert('Tính năng Cài đặt đang được phát triển!');
}

function showAIModels() {
  toggleUserDropdown();
  window.open('../backend/pages/ai-models.php', '_blank');
}

// UI functions
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.classList.toggle('open');
  }
}

function showTypingIndicator() {
  const typingIndicator = document.getElementById('typingIndicator');
  if (typingIndicator) {
    typingIndicator.classList.add('show');
    scrollToBottom();
  }
}

function hideTypingIndicator() {
  const typingIndicator = document.getElementById('typingIndicator');
  if (typingIndicator) {
    typingIndicator.classList.remove('show');
  }
}

function autoResizeTextarea(textarea) {
  textarea.style.height = 'auto';
  textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function updateSendButton(inputValue) {
  const sendBtn = document.getElementById('sendBtn');
  if (sendBtn) {
    sendBtn.disabled = !inputValue.trim();
  }
}

// Chat History Functions
async function loadChatHistory() {
  if (!AppState.isLoggedIn) return;
  
  try {
    const result = await apiRequest('api/chat-history.php');
    AppState.chatHistory = result.chats || [];
    updateChatHistoryUI();
  } catch (error) {
    console.error('Load chat history error:', error);
  }
}

function updateChatHistoryUI() {
  const chatHistory = document.getElementById('chatHistory');
  if (!chatHistory) return;
  
  // Clear existing history except current chat
  const currentChat = chatHistory.querySelector('.chat-history-item.active');
  chatHistory.innerHTML = '';
  
  // Add current chat back
  if (currentChat) {
    chatHistory.appendChild(currentChat);
  }
  
  // Add chat history
  AppState.chatHistory.forEach(chat => {
    const historyItem = document.createElement('div');
    historyItem.className = 'chat-history-item';
    historyItem.innerHTML = `
      <i class="fas fa-message"></i>
      <span>${escapeHtml(chat.title)}</span>
    `;
    historyItem.onclick = () => loadChat(chat.id);
    chatHistory.appendChild(historyItem);
  });
}

async function loadChat(chatId) {
  if (!AppState.isLoggedIn) return;
  
  try {
    const result = await apiRequest(`api/chat.php?id=${chatId}`);
    if (result.success) {
      AppState.currentChatId = chatId;
      const chatBox = document.getElementById('chatBox');
      const typingIndicator = document.getElementById('typingIndicator');
      
      // Clear current messages
      if (chatBox && typingIndicator) {
        chatBox.innerHTML = '';
        chatBox.appendChild(typingIndicator);
      }
      
      // Load messages
      result.messages.forEach(message => {
        if (message.role === 'user') {
          addUserMessage(message.content, false);
        } else {
          addBotMessage(message.content, false);
        }
      });
      
      // Update UI
      updateChatHistorySelection(chatId);
    }
  } catch (error) {
    console.error('Load chat error:', error);
  }
}

function updateChatHistorySelection(chatId) {
  const historyItems = document.querySelectorAll('.chat-history-item');
  historyItems.forEach(item => {
    item.classList.remove('active');
  });
  
  // Find and activate the selected chat
  const selectedItem = Array.from(historyItems).find(item => 
    item.onclick && item.onclick.toString().includes(chatId)
  );
  if (selectedItem) {
    selectedItem.classList.add('active');
  }
}

// Chat functions
async function sendMessage() {
  const userInput = document.getElementById('userInput');
  if (!userInput) return;
  
  const text = userInput.value.trim();
  if (!text) return;
  
  // Add user message
  addUserMessage(text);
  
  // Clear input
  userInput.value = "";
  autoResizeTextarea(userInput);
  updateSendButton('');
  
  // Show typing indicator
  showTypingIndicator();
  
  if (AppState.isLoggedIn) {
    // Send to backend API
    try {
      const result = await apiRequest('api/chat.php', {
        method: 'POST',
        body: JSON.stringify({
          message: text,
          chatId: AppState.currentChatId,
          aiModel: AppState.currentAIModel
        })
      });
      
      hideTypingIndicator();
      
      if (result.success) {
        addBotMessage(result.response);
        AppState.currentChatId = result.chatId;
      } else {
        addBotMessage('Xin lỗi, có lỗi xảy ra khi xử lý tin nhắn của bạn.');
      }
    } catch (error) {
      hideTypingIndicator();
      console.error('Send message error:', error);
      addBotMessage('Không thể kết nối đến server. Vui lòng thử lại sau.');
    }
  } else {
    // Fallback to simulated response for guest users
    setTimeout(() => {
      hideTypingIndicator();
      addBotMessage(generateBotResponse(text));
    }, getRandomDelay());
  }
}

function addUserMessage(text, scroll = true) {
  const chatBox = document.getElementById('chatBox');
  const typingIndicator = document.getElementById('typingIndicator');
  
  if (!chatBox || !typingIndicator) return;
  
  const messageDiv = document.createElement("div");
  messageDiv.className = "message user";
  messageDiv.innerHTML = `
    <div class="message-avatar">
      <i class="fas fa-user"></i>
    </div>
    <div>
      <div class="message-content">${escapeHtml(text)}</div>
      <div class="message-time">${getCurrentTime()}</div>
    </div>
  `;
  
  chatBox.insertBefore(messageDiv, typingIndicator);
  if (scroll) scrollToBottom();
}

function addBotMessage(text, scroll = true) {
  const chatBox = document.getElementById('chatBox');
  const typingIndicator = document.getElementById('typingIndicator');
  
  if (!chatBox || !typingIndicator) return;
  
  const messageDiv = document.createElement("div");
  messageDiv.className = "message bot";
  messageDiv.innerHTML = `
    <div class="message-avatar">
      <i class="fas fa-robot"></i>
    </div>
    <div>
      <div class="message-content">${text}</div>
      <div class="message-time">${getCurrentTime()}</div>
    </div>
  `;
  
  chatBox.insertBefore(messageDiv, typingIndicator);
  if (scroll) scrollToBottom();
}

function generateBotResponse(userMessage) {
  const randomResponse = botResponses[Math.floor(Math.random() * botResponses.length)];
  return randomResponse.replace('{message}', userMessage);
}

async function startNewChat() {
  const chatBox = document.getElementById('chatBox');
  const typingIndicator = document.getElementById('typingIndicator');
  
  if (!chatBox || !typingIndicator) return;
  
  // Reset current chat ID
  AppState.currentChatId = null;
  
  // Clear chat messages
  chatBox.innerHTML = '';
  
  // Add welcome message
  const welcomeDiv = document.createElement("div");
  welcomeDiv.className = "message bot";
  welcomeDiv.innerHTML = `
    <div class="message-avatar">
      <i class="fas fa-robot"></i>
    </div>
    <div>
      <div class="message-content">
        Xin chào! Tôi là HngLe AI của bạn. 🤖✨<br><br>
        Tôi có thể giúp bạn với:
        <br>• Trả lời câu hỏi và giải thích
        <br>• Hỗ trợ lập trình và code
        <br>• Sáng tạo nội dung và ý tưởng
        <br>• Phân tích và tư vấn
        <br><br>Hãy cho tôi biết bạn cần hỗ trợ gì nhé! 😊
      </div>
      <div class="message-time">Bây giờ</div>
    </div>
  `;
  
  chatBox.appendChild(welcomeDiv);
  chatBox.appendChild(typingIndicator);
  
  // Update chat history selection
  const historyItems = document.querySelectorAll('.chat-history-item');
  historyItems.forEach(item => item.classList.remove('active'));
  
  const currentChat = historyItems[0];
  if (currentChat) currentChat.classList.add('active');
  
  scrollToBottom();
}

function addChatToHistory(title, icon) {
  const chatHistory = document.getElementById('chatHistory');
  if (!chatHistory) return;
  
  // Remove active class from all items
  chatHistory.querySelectorAll('.chat-history-item').forEach(item => {
    item.classList.remove('active');
  });
  
  // Create new history item
  const historyItem = document.createElement('div');
  historyItem.className = 'chat-history-item active';
  historyItem.innerHTML = `
    <i class="${icon}"></i>
    <span>${title}</span>
  `;
  
  // Insert at the beginning
  chatHistory.insertBefore(historyItem, chatHistory.firstChild);
}

// Event listeners
document.addEventListener('DOMContentLoaded', async function() {
  const userInput = document.getElementById('userInput');
  const sendBtn = document.getElementById('sendBtn');
  
  // Check authentication status
  await checkAuthStatus();
  
  if (userInput) {
    // Auto-resize textarea and update send button
    userInput.addEventListener('input', function() {
      autoResizeTextarea(this);
      updateSendButton(this.value);
    });
    
    // Enter to send (Shift+Enter for new line)
    userInput.addEventListener("keydown", function(e) {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
  }
  
  // Initialize send button state
  if (sendBtn) {
    sendBtn.disabled = true;
  }
  
  // Close auth overlay when clicking outside
  const authOverlay = document.getElementById('authOverlay');
  if (authOverlay) {
    authOverlay.addEventListener('click', function(e) {
      if (e.target === authOverlay) {
        closeAuth();
      }
    });
  }
  
  // Close user dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const userMenu = document.querySelector('.user-menu');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenu && userDropdown && 
        !userMenu.contains(e.target)) {
      userDropdown.classList.remove('show');
    }
  });
  
  // Click outside sidebar to close on mobile
  document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (isMobile() && 
        sidebar && menuBtn &&
        !sidebar.contains(e.target) && 
        !menuBtn.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
  
  // Handle window resize
  window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (menuBtn) {
      if (isMobile()) {
        menuBtn.style.display = 'block';
      } else {
        menuBtn.style.display = 'none';
        if (sidebar) {
          sidebar.classList.remove('open');
        }
      }
    }
  });
  
  // Initial mobile menu setup
  const menuBtn = document.querySelector('.mobile-menu-btn');
  if (menuBtn) {
    if (isMobile()) {
      menuBtn.style.display = 'block';
    } else {
      menuBtn.style.display = 'none';
    }
  }
  
  // ESC key to close overlays
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeAuth();
      const userDropdown = document.getElementById('userDropdown');
      if (userDropdown) userDropdown.classList.remove('show');
    }
  });
  
  console.log('🤖 HngLe AI ChatBot application initialized successfully!');
  console.log('🔐 Authentication system ready');
  console.log('💬 Chat system ready');
  console.log('🧠 AI integration ready');
});