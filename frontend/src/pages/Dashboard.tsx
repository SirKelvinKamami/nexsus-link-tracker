import { useState, useEffect } from 'react';
import { CheckCircle, Circle, Pencil, Trash2, Plus, Search, SlidersHorizontal, ArrowUpDown, ChevronDown, Flame, Clock, Target, TrendingUp } from 'lucide-react';
import { Task, Priority, priorityColors } from '../data/tasks';
import { api } from '../lib/api';
import { adaptTask } from '../lib/adapters';
import { PriorityBadge } from '../components/PriorityBadge';
import { TaskModal } from '../components/TaskModal';
import { ToastData } from '../components/Toast';

function StatCard({ label, value, sub, color }: { label: string; value: string; sub: string; color: string }) {
  return (
    <div
      style={{
        background: 'var(--bg-secondary)', border: '1px solid var(--border)',
        borderRadius: '14px', padding: '18px 20px',
        boxShadow: 'var(--shadow-sm)', transition: 'all 0.2s',
      }}
      onMouseEnter={e => { (e.currentTarget as HTMLElement).style.transform = 'translateY(-2px)'; (e.currentTarget as HTMLElement).style.boxShadow = 'var(--shadow-md)'; }}
      onMouseLeave={e => { (e.currentTarget as HTMLElement).style.transform = 'none'; (e.currentTarget as HTMLElement).style.boxShadow = 'var(--shadow-sm)'; }}
    >
      <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', marginBottom: 8 }}>{label}</div>
      <div style={{ fontSize: '28px', fontWeight: 800, color, lineHeight: 1 }}>{value}</div>
      <div style={{ fontSize: '12px', color: 'var(--text-secondary)', marginTop: 6 }}>{sub}</div>
    </div>
  );
}

interface TaskRowProps {
  task: Task;
  onComplete: (id: string) => void;
  onEdit: (task: Task) => void;
  onDelete: (id: string) => void;
}

function TaskRow({ task, onComplete, onEdit, onDelete }: TaskRowProps) {
  const [hovered, setHovered] = useState(false);
  const isCompleted = task.status === 'completed';
  const colors = priorityColors[task.priority];

  return (
    <div
      className={task.highlight ? 'animate-highlight' : ''}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      style={{
        display: 'flex', alignItems: 'center', gap: 12,
        padding: '12px 16px', borderRadius: '10px',
        border: '1px solid transparent',
        borderLeft: isCompleted ? '3px solid var(--accent-success)' : `3px solid ${colors.border}`,
        background: isCompleted ? 'rgba(6,214,160,0.04)' : hovered ? 'var(--bg-secondary)' : 'transparent',
        transition: 'all 0.2s', cursor: 'default',
      }}
    >
      {/* Check button */}
      <button
        onClick={() => onComplete(task.id)}
        style={{ background: 'none', border: 'none', cursor: 'pointer', display: 'flex', color: isCompleted ? '#06d6a0' : 'var(--text-muted)', flexShrink: 0, transition: 'color 0.2s' }}
      >
        {isCompleted ? <CheckCircle size={18} /> : <Circle size={18} />}
      </button>

      {/* Priority badge */}
      <PriorityBadge priority={task.priority} />

      {/* Content */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <span style={{
            fontSize: '14px', fontWeight: 500, color: 'var(--text-primary)',
            textDecoration: isCompleted ? 'line-through' : 'none',
            opacity: isCompleted ? 0.5 : 1, transition: 'all 0.3s',
          }}>
            {task.title}
          </span>
        </div>
        {task.description && (
          <div style={{ fontSize: '12px', color: 'var(--text-secondary)', marginTop: 2, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
            {task.description}
          </div>
        )}
        {/* Progress bar */}
        {task.progress > 0 && !isCompleted && (
          <div style={{ marginTop: 6, display: 'flex', alignItems: 'center', gap: 8 }}>
            <div style={{ flex: 1, height: 4, background: 'var(--bg-tertiary)', borderRadius: '2px', overflow: 'hidden' }}>
              <div style={{
                height: '100%', width: `${task.progress}%`,
                background: 'var(--accent-primary)', borderRadius: '2px',
                transition: 'width 0.5s ease-out',
              }} />
            </div>
            <span style={{ fontSize: '11px', color: 'var(--text-muted)', flexShrink: 0 }}>{task.progress}%</span>
          </div>
        )}
      </div>

      {/* Meta */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 16, fontSize: '12px', color: 'var(--text-secondary)', flexShrink: 0 }}>
        <span style={{
          background: 'var(--bg-tertiary)', padding: '2px 8px', borderRadius: '4px',
          fontSize: '11px', fontWeight: 500,
        }}>
          {task.project}
        </span>
        <span>{task.dueDate}</span>
        <span style={{ display: 'flex', alignItems: 'center', gap: 3 }}>
          <Clock size={11} /> {task.estimatedDuration < 60 ? `${task.estimatedDuration}m` : `${task.estimatedDuration / 60}h`}
        </span>
      </div>

      {/* Actions (visible on hover) */}
      <div style={{
        display: 'flex', gap: 4, opacity: hovered ? 1 : 0,
        transition: 'opacity 0.15s',
      }}>
        <button
          onClick={() => onEdit(task)}
          style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', display: 'flex', padding: '4px', borderRadius: '6px', transition: 'all 0.15s' }}
          onMouseEnter={e => { (e.currentTarget as HTMLElement).style.background = 'var(--bg-tertiary)'; (e.currentTarget as HTMLElement).style.color = 'var(--text-primary)'; }}
          onMouseLeave={e => { (e.currentTarget as HTMLElement).style.background = 'none'; (e.currentTarget as HTMLElement).style.color = 'var(--text-muted)'; }}
        >
          <Pencil size={13} />
        </button>
        <button
          onClick={() => onDelete(task.id)}
          style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', display: 'flex', padding: '4px', borderRadius: '6px', transition: 'all 0.15s' }}
          onMouseEnter={e => { (e.currentTarget as HTMLElement).style.background = 'rgba(239,71,111,0.1)'; (e.currentTarget as HTMLElement).style.color = '#ef476f'; }}
          onMouseLeave={e => { (e.currentTarget as HTMLElement).style.background = 'none'; (e.currentTarget as HTMLElement).style.color = 'var(--text-muted)'; }}
        >
          <Trash2 size={13} />
        </button>
      </div>
    </div>
  );
}

const FILTERS: { label: string; value: string }[] = [
  { label: 'All', value: 'all' },
  { label: 'Today', value: 'today' },
  { label: 'Pending', value: 'pending' },
  { label: 'In Progress', value: 'in-progress' },
  { label: 'Completed', value: 'completed' },
];

export function Dashboard({ showToast }: { showToast: (t: Omit<ToastData, 'id'>) => void }) {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const res = await api.listTasks({ per_page: '100' });
        if (cancelled) return;
        const rows = res?.data?.data || [];
        setTasks(rows.map(adaptTask));
      } catch (e) {
        console.error('Failed to load tasks', e);
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, []);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('all');
  const [modalOpen, setModalOpen] = useState(false);
  const [editingTask, setEditingTask] = useState<Task | undefined>();
  const [priorityFilter, setPriorityFilter] = useState<string>('all');

  const today = '2026-09-16';

  const filteredTasks = tasks.filter(t => {
    const matchSearch = t.title.toLowerCase().includes(search.toLowerCase()) ||
      t.project.toLowerCase().includes(search.toLowerCase());
    const matchFilter = filter === 'all' ? true :
      filter === 'today' ? t.dueDate === today :
      t.status === filter;
    const matchPriority = priorityFilter === 'all' || t.priority === priorityFilter;
    return matchSearch && matchFilter && matchPriority;
  });

  const activeTasks = filteredTasks.filter(t => t.status !== 'completed');
  const completedTasks = filteredTasks.filter(t => t.status === 'completed');

  function handleComplete(id: string) {
    const task = tasks.find(t => t.id === id);
    const wasCompleted = task?.status === 'completed';
    setTasks(prev => prev.map(t =>
      t.id === id ? { ...t, status: t.status === 'completed' ? 'pending' : 'completed', progress: t.status === 'completed' ? t.progress : 100 } : t
    ));
    if (!wasCompleted) {
      api.completeTask(id).catch(e => console.error('complete failed', e));
      showToast({ title: 'Task completed ✨', description: task?.title, type: 'success' });
    }
  }

  function handleDelete(id: string) {
    setTasks(prev => prev.filter(t => t.id !== id));
    api.deleteTask(id).catch(e => console.error('delete failed', e));
    showToast({ title: 'Task deleted', type: 'info' });
  }

  function handleSave(data: Partial<Task>) {
    if (editingTask) {
      setTasks(prev => prev.map(t => t.id === editingTask.id ? { ...t, ...data } : t));
      showToast({ title: 'Task updated', type: 'success' });
    } else {
      const newTask: Task = {
        id: Date.now().toString(),
        title: data.title!,
        description: data.description,
        priority: data.priority as Priority,
        status: 'pending',
        dueDate: data.dueDate!,
        project: data.project!,
        estimatedDuration: data.estimatedDuration!,
        progress: 0,
        subtasks: data.subtasks ?? [],
        createdAt: today,
        highlight: true,
      };
      setTasks(prev => [newTask, ...prev]);
      showToast({ title: 'Task created!', description: newTask.title, type: 'success' });
      setTimeout(() => setTasks(prev => prev.map(t => t.id === newTask.id ? { ...t, highlight: false } : t)), 2000);
    }
    setEditingTask(undefined);
  }

  const totalToday = tasks.filter(t => t.dueDate === today).length;
  const completedToday = tasks.filter(t => t.dueDate === today && t.status === 'completed').length;
  const highPriority = tasks.filter(t => (t.priority === 'critical' || t.priority === 'high') && t.status !== 'completed').length;
  const inProgress = tasks.filter(t => t.status === 'in-progress').length;

  return (
    <div style={{ padding: '32px', display: 'flex', flexDirection: 'column', gap: 28 }}>
      {/* Greeting */}
      <div>
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
          <div>
            <h1 style={{ fontSize: '28px', fontWeight: 800, margin: 0, color: 'var(--text-primary)' }}>
              Good morning, Alex ☀️
            </h1>
            <p style={{ fontSize: '14px', color: 'var(--text-secondary)', margin: '6px 0 0' }}>
              Tuesday, September 16, 2026 — You have <strong style={{ color: 'var(--accent-primary)' }}>{totalToday} tasks</strong> today, {highPriority} are high priority.
            </p>
          </div>
          <button
            onClick={() => { setEditingTask(undefined); setModalOpen(true); }}
            style={{
              display: 'flex', alignItems: 'center', gap: 8,
              padding: '10px 18px', background: '#4361ee', color: 'white',
              border: 'none', borderRadius: '10px', fontSize: '14px', fontWeight: 600,
              cursor: 'pointer', fontFamily: 'inherit', transition: 'all 0.15s',
              boxShadow: '0 4px 12px rgba(67,97,238,0.3)',
            }}
            onMouseEnter={e => (e.currentTarget as HTMLElement).style.transform = 'scale(1.02)'}
            onMouseLeave={e => (e.currentTarget as HTMLElement).style.transform = 'none'}
          >
            <Plus size={16} /> Add Task
          </button>
        </div>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14 }}>
        <StatCard label="TODAY'S TASKS" value={`${completedToday}/${totalToday}`} sub="completed today" color="var(--accent-primary)" />
        <StatCard label="HIGH PRIORITY" value={String(highPriority)} sub="need attention" color="#ef476f" />
        <StatCard label="IN PROGRESS" value={String(inProgress)} sub="active tasks" color="#ffd166" />
        <StatCard label="COMPLETION RATE" value={`${tasks.length ? Math.round((tasks.filter(t => t.status === 'completed').length / tasks.length) * 100) : 0}%`} sub="all time" color="#06d6a0" />
      </div>

      {/* AI Suggestion Banner */}
      <div style={{
        background: 'linear-gradient(135deg, rgba(114,9,183,0.08), rgba(67,97,238,0.08))',
        border: '1px solid rgba(114,9,183,0.2)',
        borderRadius: '14px', padding: '14px 18px',
        display: 'flex', alignItems: 'center', gap: 12,
      }}>
        <div style={{ fontSize: '20px' }}>🧠</div>
        <div>
          <div style={{ fontSize: '13px', fontWeight: 600, color: '#7209b7' }}>AI Insight</div>
          <div style={{ fontSize: '13px', color: 'var(--text-secondary)', marginTop: 2 }}>
            Your most productive hours are 9–11 AM. Your Q3 Strategy Report is due today — consider tackling it first.
          </div>
        </div>
      </div>

      {/* Task List */}
      <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', overflow: 'hidden' }}>
        {/* List header */}
        <div style={{ padding: '16px 20px', borderBottom: '1px solid var(--border)', display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
          {/* Search */}
          <div style={{ position: 'relative', flex: 1, minWidth: 200 }}>
            <Search size={14} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
            <input
              placeholder="Search tasks…"
              value={search}
              onChange={e => setSearch(e.target.value)}
              style={{
                width: '100%', paddingLeft: 32, paddingRight: 12, paddingTop: 8, paddingBottom: 8,
                background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
                borderRadius: '8px', fontSize: '13px', color: 'var(--text-primary)',
                fontFamily: 'inherit', outline: 'none',
              }}
            />
          </div>

          {/* Filter tabs */}
          <div style={{ display: 'flex', gap: 4 }}>
            {FILTERS.map(f => (
              <button
                key={f.value}
                onClick={() => setFilter(f.value)}
                style={{
                  padding: '6px 12px', borderRadius: '8px', fontSize: '12px', fontWeight: 500,
                  border: 'none', cursor: 'pointer', fontFamily: 'inherit',
                  background: filter === f.value ? '#4361ee' : 'transparent',
                  color: filter === f.value ? 'white' : 'var(--text-secondary)',
                  transition: 'all 0.15s',
                }}
              >
                {f.label}
              </button>
            ))}
          </div>

          {/* Priority filter */}
          <select
            value={priorityFilter}
            onChange={e => setPriorityFilter(e.target.value)}
            style={{
              padding: '6px 10px', background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
              borderRadius: '8px', fontSize: '12px', color: 'var(--text-secondary)',
              fontFamily: 'inherit', outline: 'none', cursor: 'pointer',
            }}
          >
            <option value="all">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>

        {/* Task rows */}
        <div style={{ padding: '8px 12px' }}>
          {activeTasks.length === 0 && completedTasks.length === 0 && (
            <div style={{ textAlign: 'center', padding: '48px 0', color: 'var(--text-muted)' }}>
              <div style={{ fontSize: '48px', marginBottom: 12 }}>✅</div>
              <div style={{ fontSize: '16px', fontWeight: 600, color: 'var(--text-secondary)' }}>No tasks found</div>
              <div style={{ fontSize: '13px', marginTop: 6 }}>Try adjusting your filters or add a new task.</div>
            </div>
          )}

          {activeTasks.map(task => (
            <TaskRow
              key={task.id}
              task={task}
              onComplete={handleComplete}
              onEdit={t => { setEditingTask(t); setModalOpen(true); }}
              onDelete={handleDelete}
            />
          ))}

          {completedTasks.length > 0 && (
            <>
              <div style={{ padding: '12px 16px 4px', fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)' }}>
                COMPLETED — {completedTasks.length}
              </div>
              {completedTasks.map(task => (
                <TaskRow
                  key={task.id}
                  task={task}
                  onComplete={handleComplete}
                  onEdit={t => { setEditingTask(t); setModalOpen(true); }}
                  onDelete={handleDelete}
                />
              ))}
            </>
          )}
        </div>
      </div>

      {/* FAB */}
      <button
        onClick={() => { setEditingTask(undefined); setModalOpen(true); }}
        style={{
          position: 'fixed', bottom: 32, right: 32, width: 56, height: 56,
          borderRadius: '50%', background: '#4361ee', color: 'white',
          border: 'none', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
          boxShadow: '0 8px 24px rgba(67,97,238,0.4)', transition: 'all 0.2s',
          zIndex: 100,
        }}
        onMouseEnter={e => (e.currentTarget as HTMLElement).style.transform = 'scale(1.1)'}
        onMouseLeave={e => (e.currentTarget as HTMLElement).style.transform = 'none'}
      >
        <Plus size={22} />
      </button>

      {modalOpen && (
        <TaskModal
          task={editingTask}
          onSave={handleSave}
          onClose={() => { setModalOpen(false); setEditingTask(undefined); }}
        />
      )}
    </div>
  );
}
