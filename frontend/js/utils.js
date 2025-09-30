// Utility functions

/**
 * Get current time formatted for display
 * @returns {string} Formatted time string
 */
function getCurrentTime() {
  return new Date().toLocaleTimeString('vi-VN', { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
}

/**
 * Escape HTML to prevent XSS attacks
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Scroll chat to bottom with smooth animation
 */
function scrollToBottom() {
  setTimeout(() => {
    const chatBox = document.getElementById('chatBox');
    if (chatBox) {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }, 100);
}

/**
 * Generate a random delay for bot responses
 * @returns {number} Delay in milliseconds
 */
function getRandomDelay() {
  return Math.random() * 2000 + 1000; // 1-3 seconds
}

/**
 * Check if device is mobile
 * @returns {boolean} True if mobile device
 */
function isMobile() {
  return window.innerWidth <= 768;
}

/**
 * Debounce function to limit function calls
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}