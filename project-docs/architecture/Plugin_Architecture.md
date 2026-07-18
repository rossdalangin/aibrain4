# Plugin Architecture & Folder Structure - Nexus AI Workforce

## Plugin Architecture
Nexus AI Workforce follows a modular, object-oriented architecture based on the MVC pattern, adapted for WordPress. It utilizes a Dependency Injection container for managing service lifetimes and dependencies.

### Key Components:
1. **Core:** Handles plugin initialization, service registration, and lifecycle events.
2. **Providers:** Adapters for external AI APIs (OpenAI, Anthropic, Google, etc.).
3. **Orchestrator:** Manages multi-agent interactions and workflow execution.
4. **RAG Engine:** Handles document parsing, chunking, and vector search.
5. **REST API:** Provides endpoints for the React-based Admin and User interfaces.
6. **Integrations:** Hooks and filters for interacting with other WP plugins (WooCommerce, Elementor).

## Folder Structure
```text
wp-ai-workforce/
├── assets/                 # Compiled CSS/JS, images, fonts
├── includes/               # Core PHP logic (Legacy style or helper functions)
├── src/                    # PSR-4 Autoloaded classes
│   ├── AI/                 # AI related logic
│   │   ├── Models/         # AI Model Adapters (OpenAI.php, Claude.php)
│   │   ├── Prompting/      # Prompt Engineering & Templates
│   │   ├── RAG/            # Vector Search & Document Processing
│   │   └── Agents/         # Agent logic and Persona management
│   ├── API/                # REST API Controllers
│   ├── Core/               # Plugin Bootstrap, DI Container, Hooks
│   ├── Database/           # Schema, Migrations, Repositories
│   ├── Integrations/       # Third-party plugin support (WooCommerce, etc.)
│   ├── UI/                 # PHP templates for WP Admin pages
│   └── Utils/              # Helper classes (Logger, Encryption)
├── templates/              # Frontend templates (Gutenberg blocks, shortcodes)
├── vendor/                 # Composer dependencies
├── composer.json           # PHP dependencies
├── package.json            # NPM dependencies
├── tailwind.config.js      # Tailwind CSS configuration
├── webpack.config.js       # Asset bundling
└── wp-ai-workforce.php     # Main plugin file
```

## Modular Design
Each feature (e.g., Knowledge Base, Multi-Agent Workflows) is treated as a module that can be enabled or disabled. This ensures the plugin remains lightweight and easy to maintain.
