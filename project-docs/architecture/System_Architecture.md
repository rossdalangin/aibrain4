# System Architecture Overview - Nexus AI Workforce

## High-Level Diagram
Nexus AI Workforce operates as a bridge between the WordPress ecosystem and external AI Intelligence.

```mermaid
graph TD
    User((User/Admin)) -->|Interacts| WP_UI[WordPress React UI]
    WP_UI -->|REST API| WP_Core[Nexus AI Core Plugin]
    WP_Core -->|CRUD| WP_DB[(WordPress MySQL)]
    WP_Core -->|Orchestration| AI_Orchestrator[Multi-Agent Orchestrator]

    AI_Orchestrator -->|Role & Context| AI_Provider[AI Model Provider<br/>OpenAI/Claude/Gemini]
    AI_Orchestrator -->|Vector Search| RAG_Engine[RAG Engine]

    RAG_Engine -->|Embeddings| Embedding_API[OpenAI/Ollama]
    RAG_Engine -->|Search/Index| Vector_DB[(Vector Storage<br/>SQLite-vss/Pinecone)]

    WP_Core -->|Triggers| WP_Actions[WordPress Actions<br/>Create Post/Manage Woo]
    WP_Core -->|Engagement| Frontend_Widget[Frontend Chat Widget]
```

## Component Breakdown

### 1. WordPress Environment (The Host)
- **Nexus AI Core:** The main PHP engine managing security, routing, and data persistence.
- **Hybrid UI:** PHP-rendered shells with an asynchronous JavaScript bridge for real-time interactions.
- **WP Database:** Stores employee profiles (30+ roles), 13-table enterprise schema, and audit logs.

### 2. AI Intelligence Layer (The Brain)
- **Model Adapters:** Abstracted interface to communicate with various LLMs (GPT-4o, Claude 3.5, etc.).
- **Orchestrator:** The logic that manages multi-agent interactions, task delegation, and state management.

### 3. Knowledge & Retrieval (The Memory)
- **RAG Engine:** Handles document ingestion, semantic chunking, and retrieval.
- **Vector Storage:** Efficient storage for high-dimensional data, enabling semantic search.

### 4. Integration Hub (The Hands)
- **Action Bus:** Allows the AI to perform tasks within WordPress (e.g., "Create a new draft post with this content").
- **External Webhooks:** Connects Nexus to Zapier, Make, and Slack.
