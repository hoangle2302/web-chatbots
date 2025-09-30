frontend
├── index-complete.html      # Frontend hoàn chỉnh
└── style.css               # CSS responsive

backend
├── api\
│   ├── auth-status.php     # Check login status
│   ├── chat-history.php    # Load chat history  
│   └── chat.php           # Send/receive messages
├── pages\
│   ├── login.php          # Login endpoint
│   ├── register.php       # Register endpoint
│   ├── logout.php         # Logout endpoint
│   └── ai-models.php      # AI model selection
└── includes\
    ├── Database.php       # Database connection
    └── Auth.php          # Authentication class