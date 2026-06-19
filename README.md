# 🧠 MindCare – AI-Powered Student Mental Wellness Platform

## 📖 Overview

MindCare is a web-based mental wellness platform developed to support students by providing AI-assisted emotional guidance, mood assessment, and mental health monitoring. The system enables students to regularly assess their emotional well-being, interact with an AI chatbot for personalized support, and monitor their mental health progress over time.

The platform also includes a dedicated parent module that allows parents to view their child's wellness reports and emotional progress, helping strengthen communication and early intervention when needed.

The primary objective of MindCare is to create a safe, accessible, and intelligent environment where students can seek emotional support, improve self-awareness, and maintain better mental well-being.

---

# 🎯 Project Objectives

- Promote mental health awareness among students.
- Provide instant AI-powered emotional support.
- Help students monitor their emotional well-being through mood assessments.
- Enable parents to track their child's mental wellness.
- Maintain secure authentication and user management.
- Generate reports and progress tracking for continuous monitoring.

---

# ✨ Key Features

## 👨‍🎓 Student Module

- Student Registration
- Secure Login & Authentication
- Personalized Dashboard
- AI Chatbot for Emotional Support
- Daily Mood Assessment
- Mental Health Progress Tracking
- View Previous Assessments
- Profile Management

---

## 👨‍👩‍👧 Parent Module

- Parent Registration
- Parent Login
- Child Progress Monitoring
- View Mood Assessment Reports
- Wellness History
- AI-Based Insights
- Dashboard Overview

---

## 🤖 AI Chatbot

The integrated AI chatbot provides:

- Emotional support
- Mental wellness guidance
- Stress management suggestions
- Study motivation
- Anxiety reduction techniques
- General conversations for emotional comfort

The chatbot is powered using the **Groq AI API**.

---

# 📊 Mood Assessment

Students can regularly complete mood assessments to evaluate their emotional condition.

The system helps identify:

- Happiness
- Stress
- Anxiety
- Motivation
- Emotional Stability
- Overall Mental Wellness

Historical records allow users to monitor emotional changes over time.

---

# 📈 Progress Tracking

MindCare stores assessment history and visualizes mental wellness progress, allowing students and parents to observe long-term emotional trends.

---

# 🔒 Security Features

- Secure Login System
- Session Management
- Password Protection
- Role-Based Authentication
- Input Validation
- Database Security

---

# 💻 Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap

### Backend

- PHP

### Database

- MySQL

### AI Integration

- Groq API

### Development Tools

- XAMPP
- phpMyAdmin
- Visual Studio Code
- Git
- GitHub

---

# 📁 Project Structure

```
MindCare/
│
├── assets/
├── auth/
├── db/
├── includes/
├── uploads/
├── student/
├── parent/
├── chatbot/
├── dashboard.php
├── login.php
├── signup.php
├── logout.php
├── index.php
└── README.md
```

---

# 🚀 Installation

## Clone Repository

```bash
git clone https://github.com/rishontogy/Mindcare.git
```

## Navigate

```bash
cd MindCare
```

## Configure Database

- Create a MySQL database.
- Import the SQL file.
- Update database credentials in `includes/config.php`.

Example:

```php
$host = "localhost";
$dbname = "mindcare";
$username = "root";
$password = "";
```

## Start Server

- Start Apache
- Start MySQL

using XAMPP.

Open:

```
http://localhost/MindCare
```

---

# 🤖 AI Configuration

Create an environment variable for the Groq API key instead of hardcoding it.

Example:

```
GROQ_API_KEY=your_api_key_here
```

Never upload API keys to GitHub.

---

# 👥 User Roles

### Student

- Register
- Login
- Chat with AI
- Complete Mood Assessment
- Track Progress
- Manage Profile

### Parent

- Login
- View Student Reports
- Monitor Wellness
- Access Dashboard

---

# 🎯 Future Enhancements

- Voice-based AI Chatbot
- Video Counselling Integration
- Doctor Appointment Booking
- Emergency SOS Support
- Mobile Application
- Email Notifications
- SMS Alerts
- AI Depression Prediction
- Analytics Dashboard
- Multi-language Support

---

# 📸 Screenshots

Add screenshots of:

- Home Page
- Student Dashboard
- Parent Dashboard
- AI Chatbot
- Mood Assessment
- Reports
- Login & Registration

---

# 📜 License

This project is developed for educational and academic purposes.

---

# 👨‍💻 Developers

Developed as a Mini Project by

**Rishon Oommen Togy**

---

# 🌟 Project Vision

MindCare aims to bridge the gap between technology and mental healthcare by providing an intelligent, secure, and user-friendly platform that empowers students to understand and improve their emotional well-being while enabling parents to stay informed and supportive throughout the journey.

By combining Artificial Intelligence with mood assessment and progress monitoring, MindCare contributes toward creating a healthier, happier, and emotionally resilient student community.
