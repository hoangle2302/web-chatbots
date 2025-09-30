// Main application initialization and event handlers

/**
 * Load HTML component into container
 * @param {string} componentPath - Path to component file
 * @param {string} containerId - ID of container element
 */
async function loadComponent(componentPath, containerId) {
  try {
    const response = await fetch(componentPath);
    const html = await response.text();
    const container = document.getElementById(containerId);
    if (container) {
      container.innerHTML = html;
    }
  } catch (error) {
    console.error(`Error loading component ${componentPath}:`, error);
  }
}

/**
 * Initialize all components
 */
async function initializeComponents() {
  await Promise.all([
    loadComponent('components/sidebar.html', 'sidebar-container'),
    loadComponent('components/chat-header.html', 'chat-header-container'),
    loadComponent('components/chat-messages.html', 'chat-messages-container'),
    loadComponent('components/input-area.html', 'input-area-container')
  ]);
  
  // Initialize event listeners after components are loaded
  initializeEventListeners();
}

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
  const userInput = document.getElementById('userInput');
  const sendBtn = document.getElementById('sendBtn');
  
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
  
  // Click outside sidebar to close on mobile
  document.addEventListener('click', handleOutsideClick);
  
  // Handle window resize
  window.addEventListener('resize', debounce(handleMobileMenu, 250));
  
  // Initial mobile menu setup
  handleMobileMenu();
}

/**
 * Application initialization
 */
async function init() {
  try {
    console.log('🤖 Initializing ChatBot application...');
    
    // Load all components
    await initializeComponents();
    
    console.log('✅ ChatBot application initialized successfully!');
  } catch (error) {
    console.error('❌ Error initializing application:', error);
  }
}

// Initialize application when DOM is loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}