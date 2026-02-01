# Simple PHP Blogging Website

A minimal PHP-based blogging website with user authentication, roles, posts, comments, search, and a Pinterest-style homepage layout.  
Built mainly for learning and practice purposes.

---

## 🚀 Features

### 🔐 Authentication & Roles
- User registration and login
- Secure sessions
- CSRF protection
- Role-based access:
  - Admin
  - Normal user
  - Post owner

### 📝 Blog System
- Users can create blog posts
- Posts include:
  - Title
  - Content
  - Author
  - Categories
  - Tags / keywords
  - Created date
- Comment system for posts

### 🔍 Search & Filtering
- Search posts by title/content
- Keyword-based filtering
- AJAX-based **Load More** functionality on homepage

### 🎨 UI / Design
- Pinterest-style homepage (`index.php`)
- Masonry-style grid layout for posts
- Clean, minimal design using white, gray, and subtle accent colors
- Hover effects on cards and buttons
- Responsive layout (mobile-friendly)

---

## 🏗️ Tech Stack

- **Frontend**
  - HTML5
  - CSS3 (custom, no frameworks)
  - JavaScript (AJAX)

- **Backend**
  - PHP (Procedural)
  - PDO (prepared statements)

- **Database**
  - MySQL

---

## 📂 Project Structure (Simplified)

/BloggingPlatform
│
├── public/
│ ├── index.php
│ ├── login.php
│ ├── register.php
│ ├── post.php
│ └── dashboard.php
│
├── assets/
│ └── css/
│ └── style.css
│
├── includes/
│ ├── session.php
│ ├── csrf.php
│ └── auth.php
│
├── config/
│ └── db.php
│
└── README.txt