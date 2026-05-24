-- =========================================================
-- TAGFLOW -  DATABASE SCHEMA
-- MySQL 8+
-- =========================================================

CREATE DATABASE IF NOT EXISTS tagflow_mysql
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE tagflow_mysql;

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS user_refresh_tokens;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS user_reports;
DROP TABLE IF EXISTS comment_reports;
DROP TABLE IF EXISTS post_reports;
DROP TABLE IF EXISTS blocks;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS message_reads;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversation_participants;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS saved_posts;
DROP TABLE IF EXISTS comment_reactions;
DROP TABLE IF EXISTS post_reactions;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS post_media;
DROP TABLE IF EXISTS post_tag;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS user_topic;
DROP TABLE IF EXISTS follows;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- USERS
-- =========================================================
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  name VARCHAR(120) DEFAULT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  profile_image_url VARCHAR(512) DEFAULT NULL,
  bio VARCHAR(500) DEFAULT NULL,
  website_url VARCHAR(255) DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  role ENUM('user','moderator','admin') NOT NULL DEFAULT 'user',
  account_status ENUM('pending','active','suspended','banned') NOT NULL DEFAULT 'active',
  privacy_level ENUM('public','followers','private') NOT NULL DEFAULT 'public',
  is_verified BOOLEAN NOT NULL DEFAULT FALSE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  email_verified_at DATETIME DEFAULT NULL,
  last_login_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  CONSTRAINT ux_users_username UNIQUE (username),
  CONSTRAINT ux_users_email UNIQUE (email),
  INDEX ix_users_username (username),
  INDEX ix_users_email (email),
  INDEX ix_users_role_status (role, account_status),
  INDEX ix_users_created_at (created_at),
  INDEX ix_users_deleted_at (deleted_at)
)  ;

-- =========================================================
-- TAGS (HIERARCHICAL)
-- =========================================================
CREATE TABLE tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  parent_id BIGINT UNSIGNED DEFAULT NULL,
  usage_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT ux_tags_name UNIQUE (name),
  CONSTRAINT ux_tags_slug UNIQUE (slug),
  CONSTRAINT fk_tags_parent
    FOREIGN KEY (parent_id) REFERENCES tags(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX ix_tags_parent (parent_id),
  INDEX ix_tags_usage_count (usage_count)
) ;

-- =========================================================
-- FOLLOWS
-- =========================================================
CREATE TABLE follows (
     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
     follower_id BIGINT UNSIGNED NOT NULL,
     followed_id BIGINT UNSIGNED NOT NULL,
     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     CONSTRAINT ux_follow UNIQUE (follower_id, followed_id),
     CONSTRAINT fk_follows_follower
         FOREIGN KEY (follower_id) REFERENCES users(id)
             ON DELETE CASCADE ON UPDATE CASCADE,
     CONSTRAINT fk_follows_followed
         FOREIGN KEY (followed_id) REFERENCES users(id)
             ON DELETE CASCADE ON UPDATE CASCADE,
     INDEX ix_follow_follower (follower_id, created_at),
     INDEX ix_follow_followed (followed_id, created_at)
);

-- =========================================================
-- USER TOPICS (INTERESTS)
-- =========================================================
CREATE TABLE user_topic (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ux_user_topic UNIQUE (user_id, tag_id),
    CONSTRAINT fk_user_topic_user
        FOREIGN KEY (user_id) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_topic_tag
        FOREIGN KEY (tag_id) REFERENCES tags(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX ix_user_topic_tag (tag_id)
);
-- =========================================================
-- POSTS
-- =========================================================
CREATE TABLE posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  content TEXT,
  visibility ENUM('public','followers','private') NOT NULL DEFAULT 'public',
  status ENUM('draft','published','archived','deleted') NOT NULL DEFAULT 'published',
  comments_enabled BOOLEAN NOT NULL DEFAULT TRUE,
  is_ad BOOLEAN NOT NULL DEFAULT FALSE,
  location_name VARCHAR(150) DEFAULT NULL,
  reaction_count INT UNSIGNED NOT NULL DEFAULT 0,
  comment_count INT UNSIGNED NOT NULL DEFAULT 0,
  save_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_posts_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_posts_user_created (user_id, created_at),
  INDEX ix_posts_visibility_status_created (visibility, status, created_at),
  INDEX ix_posts_created (created_at),
  INDEX ix_posts_deleted_at (deleted_at)
)  ;

-- =========================================================
-- POST MEDIA
-- =========================================================
CREATE TABLE post_media (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  media_url VARCHAR(512) NOT NULL,
  media_type ENUM('image','video','gif') NOT NULL DEFAULT 'image',
  mime_type VARCHAR(100) DEFAULT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  width INT UNSIGNED DEFAULT NULL,
  height INT UNSIGNED DEFAULT NULL,
  duration_seconds INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_post_media_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_post_media_post_sort (post_id, sort_order)
)  ;

-- =========================================================
-- POST TAG (N:M)
-- =========================================================
CREATE TABLE post_tag (
  post_id BIGINT UNSIGNED NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (post_id, tag_id),
  CONSTRAINT fk_post_tag_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_post_tag_tag
    FOREIGN KEY (tag_id) REFERENCES tags(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_post_tag_tag (tag_id)
)  ;

-- =========================================================
-- COMMENTS (THREAD SUPPORT + SOFT DELETE)
-- =========================================================
CREATE TABLE comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  post_id BIGINT UNSIGNED NOT NULL,
  parent_comment_id BIGINT UNSIGNED DEFAULT NULL,
  content TEXT NOT NULL,
  is_edited BOOLEAN NOT NULL DEFAULT FALSE,
  reaction_count INT UNSIGNED NOT NULL DEFAULT 0,
  reply_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comments_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comments_parent
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_comments_post_created (post_id, created_at),
  INDEX ix_comments_parent_created (parent_comment_id, created_at),
  INDEX ix_comments_user_created (user_id, created_at),
  INDEX ix_comments_deleted_at (deleted_at)
)  ;

-- =========================================================
-- POST REACTIONS
-- =========================================================
CREATE TABLE post_reactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  post_id BIGINT UNSIGNED NOT NULL,
  type ENUM('like','love','haha','wow','sad','angry') NOT NULL DEFAULT 'like',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ux_post_reaction UNIQUE (user_id, post_id),
  CONSTRAINT fk_post_reactions_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_post_reactions_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_post_reactions_post_type (post_id, type),
  INDEX ix_post_reactions_user_created (user_id, created_at)
)  ;

-- =========================================================
-- COMMENT REACTIONS
-- =========================================================
CREATE TABLE comment_reactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  comment_id BIGINT UNSIGNED NOT NULL,
  type ENUM('like','love','haha','wow','sad','angry') NOT NULL DEFAULT 'like',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ux_comment_reaction UNIQUE (user_id, comment_id),
  CONSTRAINT fk_comment_reactions_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comment_reactions_comment
    FOREIGN KEY (comment_id) REFERENCES comments(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_comment_reactions_comment_type (comment_id, type),
  INDEX ix_comment_reactions_user_created (user_id, created_at)
)  ;

-- =========================================================
-- SAVED POSTS
-- =========================================================
CREATE TABLE saved_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  post_id BIGINT UNSIGNED NOT NULL,
  saved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ux_saved_posts UNIQUE (user_id, post_id),
  CONSTRAINT fk_saved_posts_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_saved_posts_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_saved_posts_user_saved_at (user_id, saved_at),
  INDEX ix_saved_posts_post (post_id)
)  ;

-- =========================================================
-- CONVERSATIONS
-- =========================================================
CREATE TABLE conversations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_type ENUM('direct','group') NOT NULL DEFAULT 'direct',
  title VARCHAR(150) DEFAULT NULL,
  created_by BIGINT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_conversations_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX ix_conversations_type_created (conversation_type, created_at)
)  ;

-- =========================================================
-- CONVERSATION PARTICIPANTS
-- =========================================================
CREATE TABLE conversation_participants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  left_at DATETIME DEFAULT NULL,
  is_admin BOOLEAN NOT NULL DEFAULT FALSE,
  last_read_message_id BIGINT UNSIGNED DEFAULT NULL,
  CONSTRAINT ux_conversation_participant UNIQUE (conversation_id, user_id),
  CONSTRAINT fk_conversation_participants_conversation
    FOREIGN KEY (conversation_id) REFERENCES conversations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_conversation_participants_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_conversation_participants_user (user_id),
  INDEX ix_conversation_participants_last_read (last_read_message_id)
)  ;

-- =========================================================
-- MESSAGES
-- =========================================================
CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id BIGINT UNSIGNED NOT NULL,
  sender_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  message_type ENUM('text','image','video','system') NOT NULL DEFAULT 'text',
  is_edited BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_messages_conversation
    FOREIGN KEY (conversation_id) REFERENCES conversations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_messages_sender
    FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_messages_conversation_created (conversation_id, created_at),
  INDEX ix_messages_sender_created (sender_id, created_at),
  INDEX ix_messages_deleted_at (deleted_at)
)  ;

ALTER TABLE conversation_participants
  ADD CONSTRAINT fk_conversation_participants_last_read_message
  FOREIGN KEY (last_read_message_id) REFERENCES messages(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- =========================================================
-- MESSAGE READS (OPTIONAL, MORE PRECISE THAN JUST LAST READ)
-- =========================================================
CREATE TABLE message_reads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  message_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ux_message_read UNIQUE (message_id, user_id),
  CONSTRAINT fk_message_reads_message
    FOREIGN KEY (message_id) REFERENCES messages(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_message_reads_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_message_reads_user_read_at (user_id, read_at)
)  ;

-- =========================================================
-- NOTIFICATIONS
-- =========================================================
CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  sender_id BIGINT UNSIGNED DEFAULT NULL,
  type ENUM('follow','post_reaction','comment','reply','message','mention','system') NOT NULL,
  title VARCHAR(150) DEFAULT NULL,
  message VARCHAR(500) DEFAULT NULL,
  reference_type ENUM('post','comment','message','user','conversation','system') DEFAULT NULL,
  reference_id BIGINT UNSIGNED DEFAULT NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  read_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_notifications_sender
    FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX ix_notifications_user_read_created (user_id, is_read, created_at),
  INDEX ix_notifications_reference (reference_type, reference_id)
)  ;

-- =========================================================
-- BLOCKS
-- =========================================================
CREATE TABLE blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    blocked_user_id BIGINT UNSIGNED NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ux_block UNIQUE (user_id, blocked_user_id),
    CONSTRAINT fk_blocks_user
        FOREIGN KEY (user_id) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_blocks_blocked_user
        FOREIGN KEY (blocked_user_id) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX ix_blocks_blocked_user (blocked_user_id)
);

-- =========================================================
-- REPORTS (SPLIT FOR REFERENTIAL INTEGRITY)
-- =========================================================
CREATE TABLE post_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED NOT NULL,
  post_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255) NOT NULL,
  details TEXT DEFAULT NULL,
  status ENUM('open','reviewing','resolved','rejected') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_post_reports_reporter
    FOREIGN KEY (reporter_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_post_reports_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_post_reports_status_created (status, created_at),
  INDEX ix_post_reports_post (post_id)
)  ;

CREATE TABLE comment_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED NOT NULL,
  comment_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255) NOT NULL,
  details TEXT DEFAULT NULL,
  status ENUM('open','reviewing','resolved','rejected') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_comment_reports_reporter
    FOREIGN KEY (reporter_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comment_reports_comment
    FOREIGN KEY (comment_id) REFERENCES comments(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_comment_reports_status_created (status, created_at),
  INDEX ix_comment_reports_comment (comment_id)
)  ;

CREATE TABLE user_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED NOT NULL,
  reported_user_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255) NOT NULL,
  details TEXT DEFAULT NULL,
  status ENUM('open','reviewing','resolved','rejected') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_user_reports_reporter
    FOREIGN KEY (reporter_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_user_reports_reported_user
    FOREIGN KEY (reported_user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_user_reports_status_created (status, created_at),
  INDEX ix_user_reports_reported_user (reported_user_id)
)  ;

-- =========================================================
-- AUTH / SECURITY TABLES
-- =========================================================
CREATE TABLE user_refresh_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  CONSTRAINT ux_user_refresh_tokens_hash UNIQUE (token_hash),
  CONSTRAINT fk_user_refresh_tokens_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_user_refresh_tokens_user_expires (user_id, expires_at),
  INDEX ix_user_refresh_tokens_revoked_at (revoked_at)
)  ;

CREATE TABLE password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ux_password_resets_hash UNIQUE (token_hash),
  CONSTRAINT fk_password_resets_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX ix_password_resets_user_expires (user_id, expires_at)
)  ;

CREATE TABLE login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  success BOOLEAN NOT NULL DEFAULT FALSE,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX ix_login_attempts_email_attempted (email, attempted_at),
  INDEX ix_login_attempts_ip_attempted (ip_address, attempted_at)
)  ;