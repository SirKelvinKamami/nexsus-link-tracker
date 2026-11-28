import { useState, useEffect } from 'react';
import { Bell, CheckCircle, Clock, Eye } from 'lucide-react';
import { api } from '../lib/api';

interface Reminder {
  id: string;
  task_id: string;
  task_title: string;
  due_info: string;
  status: string;
  section: 'today' | 'upcoming' | 'past';
  channel: string;
  sent_at: string;
  response?: string;
}

const statusColors: Record<string, string> = {
  unread: '#4361ee',
  snoozed: '#ffd166',
  acknowledged: '#06d6a0',
};

function ReminderCard({ reminder, onAction }: { reminder: Reminder; onAction: (id: string, action: string) => void }) {
  const borderColor = reminder.status === 'unread' ? statusColors.unread : 'transparent';
  return (
    <div className="animate-fade-in" style={{
      background: 'var(--bg-secondary)', border: '1px solid var(--border)',
      borderLeft: `3px solid ${borderColor}`, borderRadius: '12px',
      padding: '14px 16px', display: 'flex', alignItems: 'center', gap: 14,
      transition: 'all 0.2s',
    }}>
      <div style={{ width: 36, height: 36, borderRadius: '50%', background: `${statusColors[reminder.status]}18`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
        <Bell size={16} style={{ color: statusColors[reminder.status] }} />
      </div>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: '14px', fontWeight: 600, color: 'var(--text-primary)' }}>{reminder.task_title}</div>
        <div style={{ fontSize: '12px', color: 'var(--text-secondary)', marginTop: 2 }}>{reminder.due_info}</div>
        {reminder.response && (
          <div style={{ fontSize: '11px', color: 'var(--text-muted)', marginTop: 3 }}>→ {reminder.response}</div>
        )}
      </div>
      <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
        {reminder.status === 'unread' && (
          <>
            <ActionBtn icon={<CheckCircle size={13} />} label="Done" color="#06d6a0" onClick={() => onAction(reminder.id, 'done')} />
            <ActionBtn icon={<Clock size={13} />} label="Snooze" color="#ffd166" onClick={() => onAction(reminder.id, 'snooze')} />
          </>
        )}
        <ActionBtn icon={<Eye size={13} />} label="View" color="var(--accent-primary)" onClick={() => {}} />
      </div>
    </div>
  );
}

function ActionBtn({ icon, label, color, onClick }: { icon: React.ReactNode; label: string; color: string; onClick: () => void }) {
  return (
    <button
      onClick={onClick}
      style={{
        display: 'flex', alignItems: 'center', gap: 4,
        padding: '5px 10px', borderRadius: '6px', background: 'var(--bg-tertiary)',
        border: '1px solid var(--border)', color, fontSize: '12px',
        fontWeight: 500, cursor: 'pointer', fontFamily: 'inherit', transition: 'all 0.15s',
      }}
    >
      {icon} {label}
    </button>
  );
}

const SECTION_LABELS: Record<string, string> = {
  today: 'TODAY',
  upcoming: 'UPCOMING',
  past: 'PAST',
};

export function Reminders() {
  const [reminders, setReminders] = useState<Reminder[]>([]);
  const [emailEnabled, setEmailEnabled] = useState(true);
  const [pushEnabled, setPushEnabled] = useState(true);
  const [soundEnabled, setSoundEnabled] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const res = await api.listReminders();
        if (!cancelled && res.success) {
          setReminders(res.data.data);
        }
      } catch (e) {
        console.error('Reminders load failed', e);
      } finally {
        if (!cancelled) setLoading(false);
      }
      return () => { cancelled = true; };
    })();
  }, []);

  function handleAction(id: string, action: string) {
    setReminders(prev => prev.map(r =>
      r.id === id ? { ...r, status: action === 'done' ? 'acknowledged' : 'snoozed', response: action === 'done' ? 'Acknowledged' : 'Snoozed 15min' } : r
    ));
  }

  function markAllRead() {
    setReminders(prev => prev.map(r => ({ ...r, status: 'acknowledged' as const, response: r.response ?? 'Acknowledged' })));
  }

  const sections: Reminder['section'][] = ['today', 'upcoming', 'past'];

  if (loading) {
    return <div style={{ padding: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)' }}>Loading reminders…</div>;
  }

  return (
    <div style={{ padding: '32px', maxWidth: 760, display: 'flex', flexDirection: 'column', gap: 28 }}>
      {/* Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <h1 style={{ margin: 0, fontSize: '24px', fontWeight: 800, display: 'flex', alignItems: 'center', gap: 10 }}>
          <Bell size={22} style={{ color: '#4361ee' }} /> Reminders
        </h1>
        <button
          onClick={markAllRead}
          style={{ padding: '8px 16px', background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '13px', cursor: 'pointer', fontFamily: 'inherit', color: 'var(--text-secondary)' }}
        >
          Mark all read
        </button>
      </div>

      {/* Reminder sections */}
      {sections.map(section => {
        const items = reminders.filter(r => r.section === section);
        if (!items.length) return null;
        return (
          <div key={section}>
            <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', marginBottom: 10 }}>{SECTION_LABELS[section]}</div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              {items.map(r => <ReminderCard key={r.id} reminder={r} onAction={handleAction} />)}
            </div>
          </div>
        );
      })}

      {/* Log table */}
      <div>
        <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', marginBottom: 12 }}>REMINDER LOG</div>
        <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '12px', overflow: 'hidden' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '13px' }}>
            <thead>
              <tr style={{ borderBottom: '1px solid var(--border)' }}>
                {['Task', 'Sent at', 'Channel', 'Response'].map(h => (
                  <th key={h} style={{ padding: '10px 14px', textAlign: 'left', fontSize: '10px', fontWeight: 700, letterSpacing: '0.06em', color: 'var(--text-muted)' }}>{h.toUpperCase()}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {reminders.map(r => (
                <tr key={r.id} style={{ borderBottom: '1px solid var(--border)' }}>
                  <td style={{ padding: '10px 14px', color: 'var(--text-primary)', fontWeight: 500 }}>{r.taskTitle}</td>
                  <td style={{ padding: '10px 14px', color: 'var(--text-secondary)' }}>{r.sentAt}</td>
                  <td style={{ padding: '10px 14px', color: 'var(--text-secondary)' }}>{r.channel}</td>
                  <td style={{ padding: '10px 14px' }}>
                    <span style={{ color: r.response ? '#06d6a0' : 'var(--text-muted)', fontWeight: r.response ? 500 : 400 }}>
                      {r.response ?? 'Pending'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Preferences */}
      <div>
        <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', marginBottom: 14 }}>PREFERENCES</div>
        <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '14px', padding: '18px 20px', display: 'flex', flexDirection: 'column', gap: 14 }}>
          {[
            { label: 'Email Reminders', sub: 'Receive reminders via email', value: emailEnabled, set: setEmailEnabled },
            { label: 'Desktop Push', sub: 'Browser push notifications', value: pushEnabled, set: setPushEnabled },
            { label: 'Sound Alerts', sub: 'Play audio on reminders', value: soundEnabled, set: setSoundEnabled },
          ].map(({ label, sub, value, set }) => (
            <div key={label} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <div>
                <div style={{ fontSize: '14px', fontWeight: 500, color: 'var(--text-primary)' }}>{label}</div>
                <div style={{ fontSize: '12px', color: 'var(--text-secondary)' }}>{sub}</div>
              </div>
              <button
                onClick={() => set(!value)}
                style={{
                  width: 44, height: 24, borderRadius: '12px', border: 'none', cursor: 'pointer',
                  background: value ? '#4361ee' : 'var(--bg-tertiary)',
                  position: 'relative', transition: 'background 0.2s',
                }}
              >
                <div style={{
                  position: 'absolute', top: 3, left: value ? 22 : 3, width: 18, height: 18,
                  borderRadius: '50%', background: value ? '#4361ee' : 'var(--bg-tertiary)', transition: 'left 0.2s',
                  boxShadow: 'var(--shadow-sm)',
                }} />
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
