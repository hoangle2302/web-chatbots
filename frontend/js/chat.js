// Chat functionality

/**
 * Send a message from user
 */
function sendMessage() {
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
  
  // Simulate bot response with delay
  setTimeout(() => {
    hideTypingIndicator();
    addBotMessage(generateBotResponse(text));
  }, getRandomDelay());
}

/**
 * Add a user message to chat
 * @param {string} text - Message text
 */
function addUserMessage(text) {
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
  scrollToBottom();
}

/**
 * Add a bot message to chat
 * @param {string} text - Message text (can contain HTML)
 */
function addBotMessage(text) {
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
  scrollToBottom();
}

/**
 * Generate bot response based on user message
 * @param {string} userMessage - User's message
 * @returns {string} Bot response
 */
function generateBotResponse(userMessage) {
  const randomResponse = botResponses[Math.floor(Math.random() * botResponses.length)];
  return randomResponse.replace('{message}', userMessage);
}

/**
 * Start a new chat conversation
 */
function startNewChat() {
  const chatBox = document.getElementById('chatBox');
  if (!chatBox) return;
  
  // Clear chat except welcome message
  const messages = chatBox.querySelectorAll('.message:not(:first-child)');
  messages.forEach(msg => msg.remove());
  
  // Add new chat to history
  addChatToHistory('Cuộc trò chuyện mới', 'fas fa-message');
}

/**
 * Add a new chat to history
 * @param {string} title - Chat title
 * @param {string} icon - Icon class
 */
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