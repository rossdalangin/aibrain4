# Memory System Design - Nexus AI Workforce

## Overview
Nexus AI Workforce employs a multi-tiered memory system to ensure that AI agents have the right context at the right time, allowing for long-term "learning" and personalized interactions.

## 1. Short-Term Memory (Conversation Context)
- **Mechanism:** Sliding window of recent messages within a single conversation.
- **Storage:** `wp_ai_messages` table.
- **Implementation:** The Orchestrator retrieves the last $X$ tokens of the conversation to include in the current prompt.
- **Summarization:** When the conversation exceeds the context limit, an "Accountant" or "Secretary" agent automatically generates a concise summary of the discussion so far to preserve key points without consuming excessive tokens.

## 2. Persistent User Memory (Personalization)
- **Mechanism:** Key-Value store of user preferences, past decisions, and interaction style.
- **Storage:** `wp_usermeta` (leveraging native WordPress user metadata).
- **Usage:** Agents can "remember" that a specific user prefers technical explanations or has a specific business goal.

## 3. Entity Memory (Employee & Department Context)
- **Mechanism:** Static and dynamic context specific to an AI agent or their department.
- **Storage:** `wp_ai_employees` and `wp_ai_departments` tables.
- **Static:** Role description, KPIs, and personality traits.
- **Dynamic:** A "scratchpad" where the agent can store notes about their ongoing tasks or findings.

## 4. Long-Term Company Memory (Global KB)
- **Mechanism:** RAG-based retrieval from the company's knowledge base.
- **Storage:** Vector database (SQLite-vss or external).
- **Implementation:** Cross-conversation knowledge that applies to all agents (e.g., "Our refund policy is 30 days").

## 5. Memory Retrieval Flow
1. **Query:** User sends a message.
2. **Vector Lookup:** Search the Knowledge Base for relevant chunks.
3. **Context Retrieval:** Fetch the recent chat history and user preferences.
4. **Agent Profile:** Load the Employee's specific goals and "scratchpad".
5. **Synthesis:** Combine all layers into a structured system prompt for the AI model.
6. **Update:** After the AI responds, update the "scratchpad" or conversation summary if necessary.
