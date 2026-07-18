# Database Schema Design - Nexus AI Workforce

## Overview
The database is designed for high performance, scalability, and deep integration with WordPress. It uses custom tables with the `wp_` prefix (or as configured).

## Tables

### 1. `wp_ai_departments`
Groups AI employees and shares departmental context.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(255) NOT NULL
- `description`: TEXT
- `parent_id`: BIGINT(20) DEFAULT 0 (For hierarchy)
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 2. `wp_ai_employees`
Detailed profiles for AI agents.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(255) NOT NULL
- `position`: VARCHAR(255)
- `department_id`: BIGINT(20) UNSIGNED
- `role_description`: TEXT
- `responsibilities`: TEXT
- `skills`: TEXT
- `goals`: TEXT
- `kpis`: TEXT
- `prompt_template`: LONGTEXT
- `model_settings`: JSON (Model, temperature, max_tokens, etc.)
- `avatar_url`: VARCHAR(255)
- `personality_traits`: JSON
- `is_active`: TINYINT(1) DEFAULT 1
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 3. `wp_ai_conversations`
Chat sessions between users and AI(s).
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT(20) UNSIGNED
- `title`: VARCHAR(255)
- `type`: ENUM('single', 'meeting', 'workflow') DEFAULT 'single'
- `status`: VARCHAR(50)
- `last_message_at`: DATETIME
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 4. `wp_ai_messages`
Individual messages within a conversation.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `conversation_id`: BIGINT(20) UNSIGNED
- `sender_type`: ENUM('user', 'ai', 'system')
- `sender_id`: BIGINT(20) UNSIGNED (User ID or Employee ID)
- `content`: LONGTEXT
- `context_used`: JSON (References to KB chunks)
- `token_usage`: INT
- `metadata`: JSON (Thinking process, tool calls)
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 5. `wp_ai_knowledge_bases`
Containers for specialized knowledge.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(255)
- `description`: TEXT
- `owner_type`: ENUM('global', 'department', 'employee')
- `owner_id`: BIGINT(20) UNSIGNED
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 6. `wp_ai_knowledge_documents`
Individual files or URLs.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `kb_id`: BIGINT(20) UNSIGNED
- `type`: VARCHAR(50) (pdf, docx, url, etc.)
- `source_path`: VARCHAR(512)
- `content_hash`: VARCHAR(64)
- `status`: ENUM('pending', 'processing', 'indexed', 'error')
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 7. `wp_ai_knowledge_chunks`
Text chunks for RAG.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `doc_id`: BIGINT(20) UNSIGNED
- `content`: LONGTEXT
- `vector_id`: VARCHAR(255) (External reference or local index)
- `metadata`: JSON

### 8. `wp_ai_workflows`
Definitions of multi-agent tasks.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(255)
- `definition`: JSON (Steps, agents, triggers)
- `is_active`: TINYINT(1) DEFAULT 1

### 9. `wp_ai_usage_logs`
Tracking for ROI and billing.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT(20) UNSIGNED
- `employee_id`: BIGINT(20) UNSIGNED
- `model`: VARCHAR(100)
- `prompt_tokens`: INT
- `completion_tokens`: INT
- `cost`: DECIMAL(10, 6)
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 10. `wp_ai_audit_logs`
Enterprise security trail.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `event_type`: VARCHAR(100)
- `description`: TEXT
- `user_id`: BIGINT(20) UNSIGNED
- `target_id`: BIGINT(20) UNSIGNED
- `metadata`: JSON
- `created_at`: DATETIME DEFAULT CURRENT_TIMESTAMP

### 11. `wp_ai_plans`
Monetization tiers.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(255)
- `slug`: VARCHAR(100)
- `price`: DECIMAL(10, 2)
- `features`: JSON
- `is_active`: TINYINT(1) DEFAULT 1

### 12. `wp_ai_subscriptions`
Active SaaS memberships.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT(20) UNSIGNED
- `plan_id`: BIGINT(20) UNSIGNED
- `status`: VARCHAR(50)
- `renews_at`: DATETIME

### 13. `wp_ai_coupons`
Marketing and promotion engine.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `code`: VARCHAR(100)
- `discount_type`: ENUM('percentage', 'fixed')
- `amount`: DECIMAL(10, 2)
- `expires_at`: DATETIME

## Relationships
- `Employees` belong to `Departments`.
- `Messages` belong to `Conversations`.
- `KnowledgeBases` can be linked to `Employees`, `Departments`, or the whole company (`Global`).
- `KnowledgeChunks` belong to `KnowledgeDocuments`.

## Performance Considerations
- Indexes on `conversation_id`, `user_id`, `department_id`.
- JSON columns for flexible settings without excessive tables.
- Use of WordPress Transients for caching AI responses and KB lookups.
