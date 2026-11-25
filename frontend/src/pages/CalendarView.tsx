import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { INITIAL_TASKS, priorityColors } from '../data/tasks';

const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

function getDaysInMonth(year: number, month: number) {
  return new Date(year, month + 1, 0).getDate();
}

function getFirstDayOfMonth(year: number, month: number) {
  const day = new Date(year, month, 1).getDay();
  return day === 0 ? 6 : day - 1; // 0=Mon
}

export function CalendarView() {
  const [year, setYear] = useState(2026);
  const [month, setMonth] = useState(8); // September
  const [view, setView] = useState<'month' | 'week'>('month');

  const daysInMonth = getDaysInMonth(year, month);
  const firstDay = getFirstDayOfMonth(year, month);

  const cells: (number | null)[] = [];
  for (let i = 0; i < firstDay; i++) cells.push(null);
  for (let i = 1; i <= daysInMonth; i++) cells.push(i);

  const tasksForDay = (day: number) => {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return INITIAL_TASKS.filter(t => t.dueDate === dateStr);
  };

  function prevMonth() {
    if (month === 0) { setMonth(11); setYear(y => y - 1); } else setMonth(m => m - 1);
  }
  function nextMonth() {
    if (month === 11) { setMonth(0); setYear(y => y + 1); } else setMonth(m => m + 1);
  }

  return (
    <div style={{ padding: '32px' }}>
      {/* Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <button onClick={prevMonth} style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '8px', width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', color: 'var(--text-secondary)' }}>
            <ChevronLeft size={16} />
          </button>
          <h2 style={{ margin: 0, fontSize: '22px', fontWeight: 700 }}>{MONTHS[month]} {year}</h2>
          <button onClick={nextMonth} style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '8px', width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', color: 'var(--text-secondary)' }}>
            <ChevronRight size={16} />
          </button>
          <button
            onClick={() => { setYear(2026); setMonth(8); }}
            style={{ padding: '6px 12px', background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '13px', cursor: 'pointer', color: 'var(--text-secondary)', fontFamily: 'inherit' }}
          >
            Today
          </button>
        </div>
        <div style={{ display: 'flex', gap: 4 }}>
          {(['month', 'week'] as const).map(v => (
            <button key={v} onClick={() => setView(v)} style={{ padding: '6px 14px', borderRadius: '8px', border: 'none', cursor: 'pointer', fontFamily: 'inherit', fontSize: '13px', fontWeight: 500, background: view === v ? '#4361ee' : 'var(--bg-secondary)', color: view === v ? 'white' : 'var(--text-secondary)', transition: 'all 0.15s' }}>
              {v.charAt(0).toUpperCase() + v.slice(1)}
            </button>
          ))}
        </div>
      </div>

      {/* Calendar grid */}
      <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', overflow: 'hidden' }}>
        {/* Day labels */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)', borderBottom: '1px solid var(--border)' }}>
          {DAYS.map(d => (
            <div key={d} style={{ padding: '10px 12px', fontSize: '11px', fontWeight: 700, letterSpacing: '0.06em', color: 'var(--text-muted)', textAlign: 'center' }}>{d}</div>
          ))}
        </div>

        {/* Day cells */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)' }}>
          {cells.map((day, idx) => {
            const isToday = day === 16 && month === 8 && year === 2026;
            const tasks = day ? tasksForDay(day) : [];
            return (
              <div
                key={idx}
                style={{
                  minHeight: 100, padding: '10px 10px 8px',
                  borderRight: (idx + 1) % 7 === 0 ? 'none' : '1px solid var(--border)',
                  borderBottom: '1px solid var(--border)',
                  background: day ? 'var(--bg-secondary)' : 'var(--bg-tertiary)',
                  transition: 'background 0.15s',
                  cursor: day ? 'pointer' : 'default',
                }}
                onMouseEnter={e => { if (day) (e.currentTarget as HTMLElement).style.background = 'var(--bg-primary)'; }}
                onMouseLeave={e => { if (day) (e.currentTarget as HTMLElement).style.background = 'var(--bg-secondary)'; }}
              >
                {day && (
                  <>
                    <div style={{
                      width: 26, height: 26, borderRadius: '50%',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: '13px', fontWeight: isToday ? 700 : 400,
                      background: isToday ? '#4361ee' : 'transparent',
                      color: isToday ? 'white' : 'var(--text-primary)',
                      marginBottom: 6,
                    }}>
                      {day}
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                      {tasks.slice(0, 3).map(task => {
                        const colors = priorityColors[task.priority];
                        return (
                          <div key={task.id} style={{
                            padding: '2px 6px', borderRadius: '4px',
                            background: colors.bg, color: colors.text,
                            fontSize: '10px', fontWeight: 600,
                            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                            transition: 'transform 0.15s',
                          }}
                            onMouseEnter={e => (e.currentTarget as HTMLElement).style.transform = 'translateY(-1px)'}
                            onMouseLeave={e => (e.currentTarget as HTMLElement).style.transform = 'none'}
                          >
                            {task.title}
                          </div>
                        );
                      })}
                      {tasks.length > 3 && (
                        <div style={{ fontSize: '10px', color: 'var(--text-muted)', paddingLeft: 6 }}>+{tasks.length - 3} more</div>
                      )}
                    </div>
                  </>
                )}
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
