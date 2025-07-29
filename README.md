# 🤖 Mira ChatBot - Multi-Bot AI Chat Platform

[![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.0+-yellow.svg)](https://chartjs.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **Mira ChatBot** is a sophisticated multi-bot AI chat platform that enables users to create, customize, and manage multiple AI chatbots with advanced analytics and reporting capabilities. Built with PHP, MySQL, and modern web technologies.

## ✨ Features

### 🎯 Core Features
- **Multi-Bot Management**: Create and manage unlimited AI chatbots
- **Custom Branding**: Customize logos, colors, and knowledge base for each bot
- **Real-time Chat**: Interactive chat interface with AI-powered responses
- **Session Tracking**: Complete conversation history and session management
- **Support Query System**: Built-in support ticket system for each bot

### 📊 Advanced Analytics & Reporting
- **Conversation Analytics**: Track total conversations, messages, and engagement
- **Time-based Insights**: 7-day trend analysis and hourly activity patterns
- **Interactive Charts**: Beautiful Chart.js visualizations
  - Conversation distribution (doughnut chart)
  - Support query activity (bar chart)
  - 7-day conversation trends (line chart)
  - Today's hourly activity (bar chart)
- **Top Questions Analysis**: AI-powered analysis of frequently asked questions
- **Performance Metrics**: Average conversation time, message counts, and more

### 🔧 Technical Features
- **Responsive Design**: Mobile-friendly interface
- **Session Management**: Secure user authentication and session handling
- **Database Optimization**: Efficient MySQL schema with proper indexing
- **API Integration**: RESTful API endpoints for bot data
- **File Upload System**: Logo and file upload capabilities
- **Error Handling**: Robust error handling and validation

## 🏗️ Architecture

### System Overview
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│   (PHP/HTML)    │◄──►│   (PHP/MySQL)   │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Chat Widget   │    │   API Endpoints │    │   User Data     │
│   (JavaScript)  │    │   (REST)        │    │   (Tables)      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Database Schema
```sql
users                    # User authentication
├── id (PK)
├── username
└── password

bots                     # Bot configurations
├── id (PK)
├── user_id (FK)
├── name
├── logo
├── primary_color
├── secondary_color
├── knowledge_base
└── created_at

conversations            # Chat sessions
├── id (PK)
├── bot_id (FK)
├── session_id
├── total_messages
├── first_message_at
├── last_message_at
└── conversation_summary

conversation_messages    # Individual messages
├── id (PK)
├── conversation_id (FK)
├── message_type
├── message_text
└── created_at

support_queries          # Support tickets
├── id (PK)
├── bot_id (FK)
├── name
├── email
├── phone
├── category
├── description
├── file_path
└── created_at
```

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (Apache + MySQL + PHP)
- **PHP 8.0+**
- **MySQL 8.0+**
- **Web Browser** (Chrome, Firefox, Safari, Edge)

### Installation

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd Mira
   ```

2. **Setup Database**
   ```bash
   # Import the database schema
   mysql -u root -p < database/mira.sql
   ```

3. **Configure Database Connection**
   ```php
   # Edit db_connect.php
   $host = 'localhost';
   $db = 'mira_chatbot';
   $user = 'root';
   $pass = '';
   ```

4. **Start XAMPP**
   - Start Apache and MySQL services
   - Navigate to `http://localhost/Mira`

5. **Create Account**
   - Visit the homepage
   - Sign up for a new account
   - Start creating your bots!

## 📁 Project Structure

```
Mira/
├── 📁 api/                    # API endpoints
│   └── get_bot_data.php      # Bot configuration API
├── 📁 database/               # Database files
│   └── mira.sql              # Database schema
├── 📁 uploads/                # File uploads
│   └── logos/                # Bot logo images
├── 📄 index.php              # Homepage
├── 📄 dashboard.php           # Main dashboard
├── 📄 login.php              # User authentication
├── 📄 signup.php             # User registration
├── 📄 bot.php                # Bot chat interface
├── 📄 create_bot.php         # Bot creation
├── 📄 edit_bot.php           # Bot editing
├── 📄 reports.php            # Analytics dashboard
├── 📄 get_bot_report.php     # Report data API
├── 📄 gemini_api.php         # AI integration
├── 📄 log_chat.php           # Chat logging
├── 📄 submit_query.php       # Support queries
├── 📄 db_connect.php         # Database connection
└── 📄 test_db.php            # Database testing
```

## 🎮 Usage Guide

### Creating Your First Bot

1. **Sign Up/Login**
   - Visit `http://localhost/Mira`
   - Create an account or login

2. **Create Bot**
   - Click "Create New Bot" in dashboard
   - Fill in bot details:
     - **Name**: Your bot's name
     - **Logo**: Upload a custom logo
     - **Colors**: Choose primary/secondary colors
     - **Knowledge Base**: Add bot's knowledge/instructions

3. **Customize Bot**
   - Edit bot settings anytime
   - Update knowledge base
   - Change branding elements

4. **Embed Bot**
   - Use the provided embed URL
   - Integrate into your website
   - Share with users

### Analytics & Reports

1. **Access Reports**
   - Go to "Reports" in dashboard
   - Click "Show Report" on any bot

2. **View Analytics**
   - **Conversation Stats**: Total conversations, messages, time
   - **Time Trends**: 7-day and hourly activity
   - **Top Questions**: AI-analyzed frequent questions
   - **Support Queries**: Ticket statistics and trends

3. **Interactive Charts**
   - Hover over charts for details
   - Zoom and interact with data
   - Export insights

## 🔧 Configuration

### Database Configuration
```php
// db_connect.php
$host = 'localhost';      // Database host
$db = 'mira_chatbot';    // Database name
$user = 'root';          // Database user
$pass = '';              // Database password
```

### Production Deployment
1. Update database credentials
2. Change localhost URLs to production domain
3. Configure SSL certificates
4. Set up proper file permissions
5. Enable error logging

See `PRODUCTION_URLS_TODO.md` for detailed deployment checklist.

## 🛠️ API Endpoints

### Bot Data API
```
GET /api/get_bot_data.php?bot_id={id}
```
Returns bot configuration for chat widget.

### Report Data API
```
GET /get_bot_report.php?bot_id={id}
```
Returns comprehensive analytics data.

### Chat Logging API
```
POST /log_chat.php
```
Logs conversation data for analytics.

## 📊 Analytics Features

### Conversation Analytics
- **Total Conversations**: Overall and daily counts
- **Message Statistics**: Average messages per conversation
- **Time Analysis**: Average conversation duration
- **Session Tracking**: Complete conversation history

### Time-based Insights
- **7-Day Trends**: Line chart showing conversation patterns
- **Hourly Activity**: Bar chart of today's hourly usage
- **Peak Hours**: Identify most active periods
- **Growth Tracking**: Monitor bot performance over time

### Support Query Analytics
- **Query Categories**: Most common support topics
- **Response Times**: Track support efficiency
- **User Engagement**: Monitor support usage patterns

## 🔒 Security Features

- **Session Management**: Secure user sessions
- **SQL Injection Protection**: Prepared statements
- **XSS Prevention**: Input sanitization
- **File Upload Security**: Validated file uploads
- **Password Hashing**: Secure password storage

## 🎨 Customization

### Bot Branding
- Custom logos and colors
- Personalized knowledge base
- Brand-specific chat interface

### Analytics Customization
- Custom date ranges
- Filtered reports
- Export capabilities

### Integration Options
- Embed in websites
- API integration
- Custom chat widgets

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   ```bash
   # Test database connection
   php test_db.php
   ```

2. **Chat Not Loading**
   - Check bot_id parameter
   - Verify bot exists in database
   - Check file permissions

3. **Reports Not Loading**
   - Ensure conversations table exists
   - Check database permissions
   - Verify API endpoints

### Debug Tools
- `test_db.php`: Database connection testing
- Browser developer tools for frontend issues
- PHP error logs for backend debugging

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Chart.js** for beautiful data visualizations
- **Google Gemini API** for AI-powered analytics
- **XAMPP** for local development environment
- **PHP/MySQL** community for excellent documentation

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the documentation

---

**Made with ❤️ by the Mira ChatBot Team**

*Transform your customer support with AI-powered chatbots and comprehensive analytics!* 