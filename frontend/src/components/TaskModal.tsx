import { useState } from 'react';
import { X, Sparkles, Plus, Trash2 } from 'lucide-react';
import { Task, Priority, PROJECTS } from '../data/tasks';

interface TaskModalProps {
  task?: Task;
  onSave: (task: Partial<Task>) => void;
  onClose: () => void;
}

const PRIORITIES: Priority[] = ['critical', 'high', 'medium', 'low'];
const DURATIONS = [15, 30, 45, 60, 90, 120, 180, 240];

export function TaskModal({ task, onSave, onClose }: TaskModalProps) {
  const [title, setTitle] = useState(task?.title ?? '');
  const [description, setDescription] = useState(task?.description ?? '');
  const [priority, setPriority] = useState<Priority>(task?.priority ?? 'medium');
  const [dueDate, setDueDate] = useState(task?.dueDate ?? '2026-09-20');
  const [project, setProject] = useState(task?.project ?? 'Personal');
  const [duration, setDuration] = useState(task?.estimatedDuration ?? 30);
  const [subtasks, setSubtasks] = useState<string[]>(task?.subtasks.map(s => s.title) ?? []);
  const [aiParsing, setAiParsing] = useState(false);
  const [aiDone, setAiDone] = useState(false);
  const [saving, setSaving] = useState(false);

  function handleAiParse() {
    if (!description && !title) return;
    setAiParsing(true);
    setTimeout(() => {
      setAiParsing(false);
      setAiDone(true);
      if (!title) setTitle('Prepare the presentation deck');
      setPriority('high');
      setDueDate('2026-09-19');
      setProject('Design');
      setDuration(90);
      setSubtasks(['Draft outline', 'Add data slides', 'Review with team']);
    }, 1800);
  }

  function handleSave() {
    if (!title.trim()) return;
    setSaving(true);
    setTimeout(() => {
      onSave({
        title,
        description,
        priority,
        dueDate,
        project,
        estimatedDuration: duration,
        subtasks: subtasks.filter(Boolean).map((t, i) => ({ id: `ns${i}`, title: t, completed: false })),
        status: 'pending',
        progress: 0,
      });
      onClose();
    }, 600);
  }

  return (
    <div
      style={{
        position: 'fixed', inset: 0, zIndex: 1000,
        background: 'rgba(0,0,0,0.45)',
        backdropFilter: 'blur(4px)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        padding: '16px',
      }}
      onClick={(e) => e.target === e.currentTarget && onClose()}
    >
      <div
        className="animate-fade-in"
        style={{
          background: 'var(--bg-primary)',
          border: '1px solid var(--border)',
          borderRadius: '24px',
          width: '100%',
          maxWidth: '520px',
          boxShadow: 'var(--shadow-lg)',
          overflow: 'hidden',
        }}
      >
        {/* Header */}
        <div style={{ padding: '20px 24px 0', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <Sparkles size={16} style={{ color: '#7209b7' }} />
            <span style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-secondary)' }}>
              {task ? 'EDIT TASK' : 'NEW TASK'}
            </span>
          </div>
          <button onClick={onClose} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', display: 'flex' }}>
            <X size={18} />
          </button>
        </div>

        <div style={{ padding: '16px 24px 24px', display: 'flex', flexDirection: 'column', gap: 14 }}>
          {/* Title */}
          <input
            autoFocus
            placeholder="Task title"
            value={title}
            onChange={e => setTitle(e.target.value)}
            style={{
              width: '100%', padding: '12px 14px',
              background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
              borderRadius: '10px', fontSize: '16px', fontWeight: 600,
              color: 'var(--text-primary)', fontFamily: 'inherit',
              outline: 'none',
            }}
            onFocus={e => (e.target.style.borderColor = '#4361ee')}
            onBlur={e => (e.target.style.borderColor = 'var(--border)')}
          />

          {/* Description + AI Parse */}
          <div style={{ position: 'relative' }}>
            <textarea
              placeholder="Describe your task in natural language…"
              value={description}
              onChange={e => setDescription(e.target.value)}
              rows={3}
              style={{
                width: '100%', padding: '10px 14px',
                background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
                borderRadius: '10px', fontSize: '14px',
                color: 'var(--text-primary)', fontFamily: 'inherit',
                resize: 'vertical', outline: 'none',
              }}
              onFocus={e => (e.target.style.borderColor = '#4361ee')}
              onBlur={e => (e.target.style.borderColor = 'var(--border)')}
            />
            <button
              onClick={handleAiParse}
              disabled={aiParsing}
              style={{
                position: 'absolute', bottom: 10, right: 10,
                display: 'flex', alignItems: 'center', gap: 6,
                padding: '6px 12px', borderRadius: '8px',
                background: aiDone ? 'rgba(6,214,160,0.15)' : '#7209b7',
                color: aiDone ? '#06d6a0' : 'white',
                border: 'none', cursor: 'pointer', fontSize: '12px', fontWeight: 600,
                fontFamily: 'inherit', transition: 'all 0.2s',
              }}
            >
              {aiParsing ? (
                <><span className="animate-spin" style={{ display: 'inline-block' }}>◌</span> Parsing…</>
              ) : aiDone ? (
                <><Sparkles size={12} /> AI Applied</>
              ) : (
                <><Sparkles size={12} /> Parse with AI</>
              )}
            </button>
          </div>

          {/* Priority / Due / Project */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 10 }}>
            {[
              {
                label: 'Priority', value: priority,
                onChange: (v: string) => setPriority(v as Priority),
                options: PRIORITIES.map(p => ({ value: p, label: p.charAt(0).toUpperCase() + p.slice(1) })),
              },
              {
                label: 'Due Date', value: dueDate,
                onChange: (v: string) => setDueDate(v),
                type: 'date',
              },
              {
                label: 'Project', value: project,
                onChange: (v: string) => setProject(v),
                options: PROJECTS.map(p => ({ value: p, label: p })),
              },
            ].map(({ label, value, onChange, options, type }) => (
              <div key={label}>
                <label style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.08em', color: 'var(--text-muted)', display: 'block', marginBottom: 4 }}>
                  {label.toUpperCase()}
                </label>
                {type === 'date' ? (
                  <input
                    type="date"
                    value={value}
                    onChange={e => onChange(e.target.value)}
                    style={{
                      width: '100%', padding: '8px 10px',
                      background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
                      borderRadius: '8px', fontSize: '13px', color: 'var(--text-primary)',
                      fontFamily: 'inherit', outline: 'none', cursor: 'pointer',
                    }}
                  />
                ) : (
                  <select
                    value={value}
                    onChange={e => onChange(e.target.value)}
                    style={{
                      width: '100%', padding: '8px 10px',
                      background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
                      borderRadius: '8px', fontSize: '13px', color: 'var(--text-primary)',
                      fontFamily: 'inherit', outline: 'none', cursor: 'pointer',
                    }}
                  >
                    {options!.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                  </select>
                )}
              </div>
            ))}
          </div>

          {/* Duration */}
          <div>
            <label style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.08em', color: 'var(--text-muted)', display: 'block', marginBottom: 4 }}>
              ESTIMATED DURATION
            </label>
            <select
              value={duration}
              onChange={e => setDuration(Number(e.target.value))}
              style={{
                padding: '8px 10px', background: 'var(--bg-tertiary)',
                border: '1px solid var(--border)', borderRadius: '8px',
                fontSize: '13px', color: 'var(--text-primary)', fontFamily: 'inherit', outline: 'none',
              }}
            >
              {DURATIONS.map(d => <option key={d} value={d}>{d < 60 ? `${d} min` : `${d / 60}h`}</option>)}
            </select>
          </div>

          {/* Subtasks */}
          <div>
            <label style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.08em', color: 'var(--text-muted)', display: 'block', marginBottom: 8 }}>
              SUBTASKS
            </label>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
              {subtasks.map((st, i) => (
                <div key={i} className="animate-fade-in" style={{ display: 'flex', gap: 6 }}>
                  <input
                    value={st}
                    onChange={e => setSubtasks(s => s.map((x, j) => j === i ? e.target.value : x))}
                    placeholder={`Subtask ${i + 1}`}
                    style={{
                      flex: 1, padding: '7px 10px', background: 'var(--bg-tertiary)',
                      border: '1px solid var(--border)', borderRadius: '8px',
                      fontSize: '13px', color: 'var(--text-primary)', fontFamily: 'inherit', outline: 'none',
                    }}
                  />
                  <button
                    onClick={() => setSubtasks(s => s.filter((_, j) => j !== i))}
                    style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', display: 'flex', alignItems: 'center' }}
                  >
                    <Trash2 size={14} />
                  </button>
                </div>
              ))}
              <button
                onClick={() => setSubtasks(s => [...s, ''])}
                style={{
                  display: 'flex', alignItems: 'center', gap: 6,
                  padding: '7px 10px', background: 'none',
                  border: '1px dashed var(--border)', borderRadius: '8px',
                  fontSize: '13px', color: 'var(--text-secondary)', cursor: 'pointer',
                  fontFamily: 'inherit', transition: 'all 0.2s',
                }}
              >
                <Plus size={13} /> Add subtask
              </button>
            </div>
          </div>

          {/* Actions */}
          <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end', paddingTop: 4 }}>
            <button
              onClick={onClose}
              style={{
                padding: '10px 20px', background: 'none',
                border: '1px solid var(--border)', borderRadius: '8px',
                fontSize: '14px', color: 'var(--text-secondary)', cursor: 'pointer',
                fontFamily: 'inherit', transition: 'all 0.15s',
              }}
            >
              Cancel
            </button>
            <button
              onClick={handleSave}
              disabled={!title.trim() || saving}
              style={{
                padding: '10px 24px',
                background: title.trim() ? '#4361ee' : 'var(--bg-tertiary)',
                border: 'none', borderRadius: '8px',
                fontSize: '14px', fontWeight: 600, color: title.trim() ? 'white' : 'var(--text-muted)',
                cursor: title.trim() ? 'pointer' : 'not-allowed',
                fontFamily: 'inherit', transition: 'all 0.15s',
                display: 'flex', alignItems: 'center', gap: 6,
              }}
            >
              {saving ? <><span className="animate-spin" style={{ display: 'inline-block' }}>◌</span> Saving…</> : 'Save Task'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
