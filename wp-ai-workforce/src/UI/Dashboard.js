/**
 * Nexus AI Dashboard Skeleton (Conceptual React Component)
 * This serves as the blueprint for the React-based Command Center.
 */
import React, { useState } from 'react';

const Dashboard = () => {
  const [activeTab, setActiveTab] = useState('workforce');

  return (
    <div className="flex h-screen bg-nexus-bg text-[#1e293b] overflow-hidden">
      {/* Sidebar Navigation */}
      <aside className="w-64 border-r border-nexus-border bg-[#f8fafc] flex flex-col">
        <div className="p-6">
          <h1 className="text-2xl font-bold tracking-tighter text-nexus-violet">NEXUS AI</h1>
        </div>

        <nav className="flex-1 px-4 space-y-2">
          {/* Tooltip: View your company wide performance metrics */}
          <NavItem icon="dashboard" label="Overview" active={activeTab === 'overview'} onClick={() => setActiveTab('overview')} />

          {/* Tooltip: Manage and 'Hire' your specialized AI agents */}
          <NavItem icon="users" label="Workforce" active={activeTab === 'workforce'} onClick={() => setActiveTab('workforce')} />

          {/* Tooltip: Feed company data to your AI team */}
          <NavItem icon="database" label="Knowledge Base" active={activeTab === 'kb'} onClick={() => setActiveTab('kb')} />

          {/* Tooltip: Track ROI and token consumption */}
          <NavItem icon="activity" label="Analytics" active={activeTab === 'analytics'} onClick={() => setActiveTab('analytics')} />
        </nav>

        <div className="p-4 mt-auto">
          <div className="glass-panel p-4 rounded-xl">
            <p className="text-xs text-[#64748b]">Monthly Usage</p>
            <div className="h-1 bg-nexus-border rounded-full mt-2 overflow-hidden">
              <div className="h-full bg-nexus-violet w-3/4"></div>
            </div>
          </div>
        </div>
      </aside>

      {/* Main Content Area */}
      <main className="flex-1 flex flex-col overflow-hidden bg-nexus-bg">
        <header className="h-16 border-b border-nexus-border flex items-center justify-between px-8 bg-[#f8fafc] backdrop-blur-md">
          <h2 className="text-lg font-medium text-[#1e293b]">Command Center</h2>
          <div className="flex items-center gap-4">
            <div className="bg-nexus-violet/10 text-nexus-violet px-3 py-1 rounded-full text-xs font-semibold">Enterprise Plan</div>
            <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-nexus-violet to-nexus-blue"></div>
          </div>
        </header>

        <section className="flex-1 overflow-y-auto p-8 animate-fade-in-up">
          {/* Section: Overview Metrics - High-level summary of AI workforce productivity */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <StatCard label="Total Agents" value="24" trend="+3 this month" />
            <StatCard label="Avg Response Time" value="1.2s" trend="-0.4s" />
            <StatCard label="Token Efficiency" value="94%" trend="+2%" />
          </div>
        </section>
      </main>
    </div>
  );
};

// Helper Components
const NavItem = ({ label, active, onClick }) => (
  <button
    onClick={onClick}
    className={`w-full flex items-center px-4 py-3 rounded-lg transition-all ${active ? 'bg-nexus-violet text-[#1e293b]' : 'text-[#64748b] hover:text-[#1e293b] hover:bg-nexus-elevated'}`}
  >
    <span className="text-sm font-medium">{label}</span>
  </button>
);

const StatCard = ({ label, value, trend }) => (
  <div className="glass-panel p-6 rounded-2xl gradient-border">
    <p className="text-sm text-[#64748b]">{label}</p>
    <p className="text-3xl font-bold mt-2 text-[#1e293b]">{value}</p>
    <p className="text-xs text-nexus-violet mt-2">{trend}</p>
  </div>
);

export default Dashboard;
