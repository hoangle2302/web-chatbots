// Bot responses data
const botResponses = [
  `Cảm ơn bạn đã chia sẻ! Về "{message}", tôi nghĩ rằng đây là một chủ đề thú vị. Bạn có muốn tôi giải thích thêm không? 🤔`,
  `Thật tuyệt! "{message}" là một ý tưởng hay. Tôi có thể giúp bạn phát triển nó thêm. Bạn muốn bắt đầu từ đâu? 💡`,
  `Tôi hiểu bạn đang quan tâm đến "{message}". Đây là một lĩnh vực rất thú vị! Tôi có thể chia sẻ một số thông tin hữu ích về điều này. 📚`,
  `"{message}" - đây là một câu hỏi tốt! Hãy để tôi suy nghĩ và đưa ra câu trả lời chi tiết nhất cho bạn. ✨`,
  `Wow! "{message}" nghe có vẻ thú vị đấy. Tôi có một số ý tưởng về điều này. Bạn có muốn tôi liệt kê ra không? 🚀`
];

// Chat history data
const defaultChatHistory = [
  {
    icon: 'fas fa-message',
    title: 'Cuộc trò chuyện hiện tại',
    active: true
  },
  {
    icon: 'fas fa-lightbulb',
    title: 'Ý tưởng sáng tạo',
    active: false
  },
  {
    icon: 'fas fa-code',
    title: 'Lập trình Python',
    active: false
  },
  {
    icon: 'fas fa-graduation-cap',
    title: 'Học tập AI',
    active: false
  }
];