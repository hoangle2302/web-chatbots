// UI interaction functions

/**
 * Toggle sidebar visibility (mobile)
 */
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.classList.toggle('open');
  }
}

/**
 * Show typing indicator
 */
function showTypingIndicator() {
  const typingIndicator = document.getElementById('typingIndicator');
  if (typingIndicator) {
    typingIndicator.classList.add('show');
    scrollToBottom();
  }
}

/**
 * Hide typing indicator
 */
function hideTypingIndicator() {
  const typingIndicator = document.getElementById('typingIndicator');
  if (typingIndicator) {
    typingIndicator.classList.remove('show');
  }
}

/**
 * Auto-resize textarea based on content
 * @param {HTMLTextAreaElement} textarea - Textarea element
 */
function autoResizeTextarea(textarea) {
  textarea.style.height = 'auto';
  textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

/**
 * Update send button state based on input
 * @param {string} inputValue - Current input value
 */
function updateSendButton(inputValue) {
  const sendBtn = document.getElementById('sendBtn');
  if (sendBtn) {
    sendBtn.disabled = !inputValue.trim();
  }
}

/**
 * Handle mobile menu display based on screen size
 */
function handleMobileMenu() {
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
}

/**
 * Handle click outside sidebar to close it
 * @param {Event} e - Click event
 */
function handleOutsideClick(e) {
  const sidebar = document.getElementById('sidebar');
  const menuBtn = document.querySelector('.mobile-menu-btn');
  
  if (isMobile() && 
      sidebar && menuBtn &&
      !sidebar.contains(e.target) && 
      !menuBtn.contains(e.target)) {
    sidebar.classList.remove('open');
  }
}