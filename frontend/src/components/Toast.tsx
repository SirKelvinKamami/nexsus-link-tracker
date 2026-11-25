import { useEffect } from 'react';
import { CheckCircle, Bell, Sparkles, X } from 'lucide-react';

export interface ToastData {
  id: string;
  title: string;
  description?: string;
  type: 'success' | 'info' | 'warning' | 'ai';
}

const typeStyles: Record<ToastData['type'], { color: string; Icon: typeof CheckCircle }> = {
  success: { color: '#06d6a0', Icon: CheckCircle },
  info: { color: '#4361ee', Icon: Bell },
  warning: { color: '#ffd166', Icon: Bell },
  ai: { color: '#7209b7', Icon: Sparkles },
};

export function Toast({ toast, onDismiss }: { toast: ToastData; onDismiss: (id: string) => void }) {
  const { color, Icon } = typeStyles[toast.type];

  useEffect(() => {
    const t = setTimeout(() => onDismiss(toast.id), 4000);
    return () => clearTimeout(t);
  }, [toast.id, onDismiss]);

  return (
    <div
      className="animate-slide-in-right"
      style={{
        background: 'var(--bg-secondary)',
        border: '1px solid var(--border)',
        borderLeft: `3px solid ${color}`,
        borderRadius: '12px',
        padding: '12px 14px',
        display: 'flex',
        gap: '10px',
        alignItems: 'flex-start',
        boxShadow: 'var(--shadow-lg)',
        minWidth: '280px',
        maxWidth: '340px',
      }}
    >
      <Icon size={16} style={{ color, marginTop: 2, flexShrink: 0 }} />
      <div style={{ flex: 1 }}>
        <div style={{ fontWeight: 600, fontSize: '13px', color: 'var(--text-primary)' }}>{toast.title}</div>
        {toast.description && (
          <div style={{ fontSize: '12px', color: 'var(--text-secondary)', marginTop: 2 }}>{toast.description}</div>
        )}
      </div>
      <button
        onClick={() => onDismiss(toast.id)}
        style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', padding: 0 }}
      >
        <X size={14} />
      </button>
    </div>
  );
}

export function ToastContainer({ toasts, onDismiss }: { toasts: ToastData[]; onDismiss: (id: string) => void }) {
  return (
    <div style={{ position: 'fixed', top: 20, right: 20, zIndex: 9999, display: 'flex', flexDirection: 'column', gap: 8 }}>
      {toasts.map((t) => (
        <Toast key={t.id} toast={t} onDismiss={onDismiss} />
      ))}
    </div>
  );
}
