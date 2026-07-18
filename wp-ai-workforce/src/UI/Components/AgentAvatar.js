/**
 * AgentAvatar component with status ring and premium styling.
 */
import React from 'react';

const AgentAvatar = ({ size = 'md', status = 'online', color = 'violet' }) => {
  const sizes = {
    sm: 'w-8 h-8',
    md: 'w-12 h-12',
    lg: 'w-16 h-16',
  };

  const statusColors = {
    online: 'border-green-500',
    thinking: 'border-nexus-violet animate-pulse',
    offline: 'border-gray-600',
  };

  return (
    <div className={`relative rounded-full border-2 ${statusColors[status]} ${sizes[size]} p-1 bg-[#f8fafc] overflow-hidden`}>
      <div className={`w-full h-full rounded-full bg-gradient-to-tr from-nexus-${color} to-nexus-blue flex items-center justify-center text-xs font-bold text-[#1e293b]`}>
        AI
      </div>
    </div>
  );
};

export default AgentAvatar;
