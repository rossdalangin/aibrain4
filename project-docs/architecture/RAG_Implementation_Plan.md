# Knowledge Base & RAG Implementation Plan - Nexus AI Workforce

## Architecture Overview
The Knowledge Base (KB) system is designed to provide AI agents with high-fidelity, contextually relevant information from diverse data sources.

### 1. Data Ingestion Layer
- **File Processors:** PHP-based parsers for PDF (using `smalot/pdfparser`), DOCX (`phpoffice/phpword`), and CSV/Excel.
- **Web Scraper:** A headless-compatible scraper to extract clean text from URLs.
- **Media Transcriber:** Integration with OpenAI Whisper or Deepgram for video/audio files.

### 2. Processing Pipeline
- **Cleaning:** Removing boilerplate, HTML tags, and redundant whitespace.
- **Chunking:** Semantic chunking (breaking text at paragraph/sentence boundaries) with an overlap (e.g., 500 characters chunk, 50 characters overlap) to preserve context.
- **Embedding:** Generating vector representations using `text-embedding-3-small` (OpenAI) or local models via Ollama.

### 3. Storage Layer
- **Relational Data:** Metadata (source, date, tags) stored in `wp_ai_knowledge_documents`.
- **Vector Data:**
    - *Tier 1 (Local):* SQLite with the `vss` extension (suitable for many WP hosts).
    - *Tier 2 (Cloud):* Integration with Pinecone or Weaviate for enterprise-scale search.

### 4. Retrieval Mechanism (The "R" in RAG)
- **Hybrid Search:** Combining keyword search (SQL LIKE or Full-Text) with semantic vector search.
- **Re-ranking:** Using a Cross-Encoder (if available) to refine the top results before sending them to the LLM.
- **Context Injection:** Formatting the top $N$ chunks into a "Context" block in the system prompt.

## Implementation Roadmap

### Step 1: Document Upload & Metadata
- Build the UI for file uploads.
- Implement the `wp_ai_knowledge_documents` table to track status.

### Step 2: Content Extraction
- Implement the `AI\RAG\Parser` classes for different file types.
- Store raw text temporarily in a processing queue.

### Step 3: Vectorization
- Implement the `AI\RAG\Embedder` service.
- Batch process chunks to minimize API calls.

### Step 4: Search & Prompt Integration
- Create the `AI\RAG\Searcher` class.
- Update the `AI\Orchestrator` to call the searcher before generating AI responses.
