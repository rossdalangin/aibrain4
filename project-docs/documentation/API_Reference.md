# Nexus AI Workforce: API Reference (v1)

## 1. Authentication
All requests require a valid WordPress nonce or a Bearer Token (for external integrations).
Endpoints are prefixed with `/wp-json/nexus-ai/v1`.

## 2. Workforce Endpoints
### `GET /employees`
List all active AI agents.

### `POST /employees`
Create a new agent.
**Payload:**
```json
{
  "name": "Sarah",
  "position": "Growth Hacker",
  "department_id": 2,
  "role_description": "Scale traffic using viral loops..."
}
```

## 3. Knowledge Base Endpoints
### `POST /kb/index`
Trigger indexing for a document.

## 4. Chat & Orchestration
### `POST /chat`
Interact with an agent.
**Payload:**
```json
{
  "agent_id": 15,
  "message": "Analyze our Q3 sales data.",
  "stream": true
}
```
