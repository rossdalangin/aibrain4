# Nexus AI Workforce: Installation & Technical Setup

## 1. System Requirements
- WordPress 6.2+
- PHP 8.1+
- **Extensions:** `curl`, `openssl`, `mbstring`, `sqlite3` (for local vector storage).
- **PHP Libraries:** `smalot/pdfparser` and `phpoffice/phpword` (required for RAG document processing).
- **Server:** Minimum 512MB RAM (1GB recommended for large document indexing).

## 2. Installation
1. Upload the `wp-ai-workforce` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Run `composer install` in the plugin directory to fetch enterprise parsers.
4. Run `npm install && npm run build` for the premium UI.

## 3. API Configuration
1. Go to **Nexus AI > System Config**.
2. Enter your **OpenAI API Key** (or preferred provider).
3. Click **Save Infrastructure**. Your keys are now securely stored using AES-256-CTR.
4. Run **Platform Diagnostics** to verify encryption and database integrity.

## 4. Troubleshooting
- **Memory Issues:** Increase your PHP memory limit to 512MB for document indexing.
- **API Timeouts:** Ensure your server allows outbound requests to `api.openai.com`.
- **UI Not Loading:** Check that `assets/js/admin.js` exists after running the build script.
