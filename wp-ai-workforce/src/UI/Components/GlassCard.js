/**
 * GlassCard component for premium glassmorphism effects.
 */
import React from 'react';

const GlassCard = ({ children, className = '', hover = true }) => {
  const baseClasses = 'glass-panel p-6 rounded-2xl border border-nexus-border transition-all duration-300';
  const hoverClasses = hover ? 'hover:border-nexus-violet hover:shadow-lg hover:shadow-nexus-violet/10' : '';

  return (
    <div className={`${baseClasses} ${hoverClasses} ${className}`}>
      {children}
    </div>
  );
};

export default GlassCard;
