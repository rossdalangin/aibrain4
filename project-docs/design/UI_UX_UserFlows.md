# UI Wireframes & User Flows - Nexus AI Workforce

## Admin UI Wireframes (React-based)

### 1. Dashboard Overview
- **Sidebar:** Navigation (Dashboard, Departments, Employees, Knowledge Base, Workflows, Analytics, Settings).
- **Header:** Global search, AI usage meter (tokens/cost), User profile.
- **Main Content:**
    - Statistics Cards: Active Employees, Total Conversations, Today's Token Usage.
    - Charts: Usage over the last 30 days.
    - Recent Activity: List of latest AI interactions and document indexing status.

### 2. Employee Management (The "Hiring" Hall)
- **List View:** Cards for each AI employee with name, position, department, and status.
- **Create/Edit Drawer:**
    - **General:** Name, Position, Department, Avatar.
    - **Brain Builder:** Sliders for Temperature, sliders/dropdowns for personality traits (Professional vs. Creative), text areas for Role Description and KPIs.
    - **Model Selection:** Dropdown for OpenAI, Claude, etc.
    - **Knowledge Base:** Select which KBs this employee can access.

### 3. Knowledge Base Manager
- **File Dropzone:** Drag and drop PDFs, CSVs.
- **URL Scraper:** Input field for website URLs.
- **Index Status:** Real-time progress bar for document chunking and embedding.

## User UI Wireframes (Chat Experience)

### 1. The Command Center (Chat Interface)
- **Left Sidebar:**
    - New Chat button.
    - "Departments" grouping.
    - Recent conversations list.
- **Main Chat Area:**
    - Clean, ChatGPT-like interface.
    - Top bar showing which AI Employee(s) are in the chat.
    - Message history with distinct styles for User and different AI agents.
    - "Thinking..." indicators with expandable logs showing the AI's reasoning process.
- **Right Sidebar (Context):**
    - Employee profile details.
    - Active knowledge base references.
    - Shared documents.

## User Flows

### Flow 1: Hiring and Configuring a New AI Employee
1. Admin navigates to **Workforce**.
2. Clicks **Hire New Employee**.
3. Admin fills in Identity (e.g., "Sarah - Content Strategist").
4. Admin uses the **Brain Builder** to define Sarah's goals and style.
5. Admin links the "Marketing Assets" Knowledge Base to Sarah.
6. Admin saves. Sarah is now available for the team to chat with.

### Flow 2: Executing a Multi-Agent Meeting
1. User goes to the Chat Interface.
2. User selects **Start Meeting**.
3. User invites "CEO," "Marketing Manager," and "CFO".
4. User asks: "Should we increase our ad spend by 20% next month?"
5. The **Orchestrator** triggers the agents.
6. The UI shows the agents debating (e.g., CFO raises risk, Marketing Manager shows potential ROI).
7. CEO summarizes the consensus.
8. User receives a "Meeting Minutes" document and "Action Plan".
