import { CheckCircle, Clock } from 'lucide-react';
import { INITIAL_TASKS, priorityColors } from '../data/tasks';
import { PriorityBadge } from '../components/PriorityBadge';

const AI_INSIGHTS = [
  'You completed 4/5 tasks yesterday — a strong finish to the week.',
  'Your Q3 Report is critical priority and due today. Tackle it before 11 AM for best results.',
  'You tend to slip on follow-up emails — consider scheduling a recurring 30-minute block.',
  'Focus time this week is up 22%. Keep it up!',
];

const YESTERDAY_STATS = {
  completed: 4,
  total: 5,
  timeSpent: '3h 45m',
  overdue: 1,
};

export function DailyDigest({ onDismiss }: { onDismiss: () => void }) {
  const todayTasks = INITIAL_TASKS.filter(t => t.dueDate === '2026-09-16');

  const SCHEDULE = [
    { time: '9:00 AM', task: todayTasks[0] },
    { time: '11:00 AM', task: todayTasks[1] },
    { time: '2:00 PM', task: todayTasks[2] },
  ].filter(s => s.task);

  return (
    <div style={{ padding: '32px', maxWidth: 720, display: 'flex', flexDirection: 'column', gap: 28 }}>
      <div>
        <h1 style={{ margin: 0, fontSize: '24px', fontWeight: 800 }}>📋 Daily Digest</h1>
        <div style={{ fontSize: '14px', color: 'var(--text-secondary)', marginTop: 4 }}>Tuesday, September 16, 2026</div>
      </div>

      {/* Today's plan */}
      <Section title="🌅 TODAY'S PLAN">
        <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
          {SCHEDULE.map(({ time, task }, i) => (
            <div key={task.id} style={{ display: 'flex', gap: 16, position: 'relative' }}>
              {/* Timeline */}
              <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', width: 60, flexShrink: 0 }}>
                <div style={{ fontSize: '11px', fontWeight: 600, color: 'var(--text-muted)', marginBottom: 6, marginTop: 4 }}>{time}</div>
                <div style={{ width: 10, height: 10, borderRadius: '50%', background: priorityColors[task.priority].border, flexShrink: 0 }} />
                {i < SCHEDULE.length - 1 && <div style={{ width: 2, flex: 1, background: 'var(--border)', margin: '4px 0' }} />}
              </div>
              <div style={{
                flex: 1, padding: '12px 16px', background: 'var(--bg-secondary)',
                border: '1px solid var(--border)', borderRadius: '10px',
                marginBottom: 12,
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                  <PriorityBadge priority={task.priority} />
                  <span style={{ fontSize: '15px', fontWeight: 600, color: 'var(--text-primary)' }}>{task.title}</span>
                </div>
                <div style={{ display: 'flex', gap: 14, fontSize: '12px', color: 'var(--text-secondary)' }}>
                  <span style={{ display: 'flex', alignItems: 'center', gap: 4 }}><Clock size={11} /> {task.estimatedDuration}m</span>
                  <span>{task.project}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Section>

      {/* Yesterday's review */}
      <Section title="📊 YESTERDAY'S REVIEW">
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12 }}>
          {[
            { label: 'Completed', value: `${YESTERDAY_STATS.completed}/${YESTERDAY_STATS.total}`, color: '#06d6a0' },
            { label: 'Time Spent', value: YESTERDAY_STATS.timeSpent, color: '#4361ee' },
            { label: 'Overdue', value: String(YESTERDAY_STATS.overdue), color: '#ef476f' },
            { label: 'Rate', value: `${Math.round(YESTERDAY_STATS.completed / YESTERDAY_STATS.total * 100)}%`, color: '#7209b7' },
          ].map(({ label, value, color }) => (
            <div key={label} style={{ background: 'var(--bg-tertiary)', borderRadius: '10px', padding: '14px', textAlign: 'center' }}>
              <div style={{ fontSize: '20px', fontWeight: 800, color }}>{value}</div>
              <div style={{ fontSize: '11px', color: 'var(--text-muted)', marginTop: 4, fontWeight: 600 }}>{label}</div>
            </div>
          ))}
        </div>
      </Section>

      {/* AI Insights */}
      <Section title="🤖 AI INSIGHTS">
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {AI_INSIGHTS.map((insight, i) => (
            <div key={i} className="animate-fade-in" style={{
              display: 'flex', gap: 12, padding: '12px 14px',
              background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '10px',
            }}>
              <div style={{ width: 20, height: 20, borderRadius: '50%', background: 'rgba(114,9,183,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, marginTop: 1 }}>
                <span style={{ fontSize: '10px' }}>✦</span>
              </div>
              <span style={{ fontSize: '14px', color: 'var(--text-secondary)', lineHeight: 1.5 }}>{insight}</span>
            </div>
          ))}
        </div>
      </Section>

      {/* Actions */}
      <div style={{ display: 'flex', gap: 10 }}>
        <button
          onClick={onDismiss}
          style={{
            display: 'flex', alignItems: 'center', gap: 8,
            padding: '11px 22px', background: '#4361ee', color: 'white',
            border: 'none', borderRadius: '10px', fontSize: '14px', fontWeight: 600,
            cursor: 'pointer', fontFamily: 'inherit',
          }}
        >
          <CheckCircle size={15} /> Got it — Let's go!
        </button>
        <button
          onClick={() => {}}
          style={{
            padding: '11px 22px', background: 'none',
            border: '1px solid var(--border)', borderRadius: '10px', fontSize: '14px',
            cursor: 'pointer', fontFamily: 'inherit', color: 'var(--text-secondary)',
          }}
        >
          Snooze 1 hour
        </button>
      </div>
    </div>
  );
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div>
      <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', marginBottom: 14 }}>{title}</div>
      {children}
    </div>
  );
}
