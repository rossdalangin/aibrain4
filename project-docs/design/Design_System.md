# Nexus AI Workforce: Premium Design System

## 1. Visual Language
The design system for Nexus AI Workforce is inspired by "The Linear/Framer Aesthetic": minimal, precise, high-contrast, and utilizing depth through glassmorphism and subtle shadows.

## 2. Color Palette
### 2.1 Primary (Atmosphere)
- **Deep Slate (Background):** `#0A0A0B`
- **Surface (Card/Sidebar):** `#141416`
- **Elevated (Hover):** `#1C1C1F`
- **Border:** `#27272A` (1px, 20% opacity)

### 2.2 Accents (Energy)
- **Nexus Violet (Primary Action):** `#7C3AED`
- **Cyber Blue (Secondary):** `#06B6D4`
- **Success Green:** `#10B981`
- **Alert Red:** `#EF4444`
- **Gold (Enterprise/Premium):** `#F59E0B`

## 3. Typography
- **Primary Typeface:** Inter (Sans-serif)
- **Secondary (Mono):** JetBrains Mono (For reasoning logs and code)
- **Scale:**
    - Display: 36px, Bold, -0.02em tracking
    - Title: 24px, Semi-Bold
    - Body: 14px, Regular
    - Caption: 12px, Medium

## 4. Components & Patterns

### 4.1 Glassmorphism (The "Premium" Layer)
- **Utility:** `.glass-panel`
- **Effect:** `backdrop-filter: blur(12px); background: rgba(20, 20, 22, 0.7); border: 1px solid rgba(255, 255, 255, 0.05);`

### 4.2 Buttons
- **Primary:** Violet background, white text, subtle glow on hover.
- **Ghost:** No background, white border (10% opacity), white text.
- **Animation:** 0.2s cubic-bezier(0.4, 0, 0.2, 1) transition on all properties.

### 4.3 Sidebar (The "Command Navigator")
- Width: 260px
- Fixed left position.
- Hierarchical navigation with micro-interactions on hover.
- Distinct section for "Active Departments."

### 4.4 Chat Bubbles
- **User:** Right-aligned, dark violet gradient, sharp corners (4px radius).
- **AI Agent:** Left-aligned, dark surface background, subtle border, distinct avatar for each agent.

## 5. Animations & Motion
- **Entrance:** Vertical slide with fade-in (20px translate-y).
- **Thinking Indicator:** Pulse animation on the AI avatar glow.
- **Workflow Transitions:** Layout animations using Framer Motion (conceptual).

## 6. Accessibility
- Minimum contrast ratio of 4.5:1 for all text.
- Focused states with high-visibility rings.
- Aria-labels for all AI-generated actions.
