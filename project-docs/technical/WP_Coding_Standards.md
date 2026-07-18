# WordPress Coding Standards & Best Practices - Nexus AI Workforce

## 1. PHP Standards
- **Namespacing:** All classes must reside under the `NexusAI\Workforce` namespace.
- **PSR-4:** Follow PSR-4 for autoloading.
- **Naming Conventions:**
    - Classes: `PascalCase`.
    - Methods/Functions: `snake_case`.
    - Variables: `snake_case`.
    - Constants: `SCREAMING_SNAKE_CASE`.
- **Strict Typing:** Use `declare(strict_types=1);` in all new PHP files.
- **Documentation:** Use PHPDoc for all classes and methods.

## 2. WordPress Integration
- **Security:**
    - Always use nonces for state-changing requests (`check_admin_referer` or `check_ajax_referer`).
    - Use `current_user_can()` for all capability checks.
    - Sanitize all inputs (`sanitize_text_field`, `absint`, etc.).
    - Escape all outputs (`esc_html`, `esc_attr`, `wp_kses`).
- **Database:**
    - Use `$wpdb` for all database interactions.
    - Use prepared statements to prevent SQL injection.
- **Hooks:** Prefix all custom hooks with `nexus_ai_`.

## 3. Frontend (React)
- **Component Structure:** Functional components with Hooks.
- **State Management:** Use `wp.element` (React) and native `useState`/`useReducer`. For complex state, use `wp.data` (Redux-like).
- **Styling:** Tailwind CSS for layout and custom styles.
- **Localization:** Use `__( 'text', 'nexus-ai-workforce' )` for all UI strings.

## 4. AI & External APIs
- **Timeouts:** Set appropriate timeouts for AI API calls (e.g., 30-60 seconds).
- **Asynchronous Processing:** Use `Action Scheduler` or `WP-Cron` for long-running tasks like document indexing or multi-agent workflows.
- **Caching:** Cache AI responses using Transients when appropriate to reduce costs and improve speed.
