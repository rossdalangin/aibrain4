# AI Prompt & Multi-Agent Architecture - Nexus AI Workforce

## AI Prompt Architecture
Nexus uses a hierarchical prompting system to ensure consistency and role-specific behavior.

### 1. Global System Prompt (Base Layer)
Contains company-wide context, core values, and general behavioral constraints (e.g., "Never disclose internal API keys," "Always maintain a professional tone").

### 2. Role-Specific Instructions (Persona Layer)
Derived from the `wp_ai_employees` table. Includes:
- **Identity:** Who the AI is.
- **Mission:** Primary objective.
- **Expertise:** Deep knowledge areas.
- **Tone & Style:** How it speaks.
- **Decision Framework:** How it weighs options.

### 3. Contextual Injection (Dynamic Layer)
- **Knowledge Base (RAG):** Relevant chunks from the knowledge base are injected here.
- **Memory:** Recent conversation history and persistent user preferences.
- **Workflow State:** If part of a workflow, the current step's requirements.

### 4. Output Shaping (Formatting Layer)
Ensures the AI returns data in the expected format (JSON for tools, Markdown for users).

## Multi-Agent Architecture
Nexus implements an **Orchestrator-Worker** pattern for complex tasks.

### Orchestrator Agent
- Usually a high-level role (e.g., CEO or Project Manager).
- Analyzes user request and breaks it down into sub-tasks.
- Selects the best "Employee" for each task.
- Synthesizes final results.

### Collaboration Patterns
- **Sequential:** Agent A -> Agent B -> Agent C.
- **Consultative:** Agent A asks Agent B for feedback before replying to User.
- **Competitive/Debate:** Multiple agents argue different sides of a problem to find the best solution (e.g., AI Meeting).

### Communication Protocol
Agents communicate via a internal messaging bus. Each message contains:
- `sender_id`
- `receiver_id`
- `task_payload`
- `context_metadata`
