// src/lib/adapters.ts
import type { Task, Priority, TaskStatus } from '../data/tasks';

export function priorityFromInt(p: number): Priority {
  if (p >= 5) return 'critical';
  if (p === 4) return 'high';
  if (p === 3) return 'medium';
  return 'low';
}

export function statusFromApi(s: string): TaskStatus {
  if (s === 'in_progress') return 'in-progress';
  if (s === 'completed') return 'completed';
  if (s === 'cancelled') return 'cancelled';
  return 'pending';
}

export function adaptTask(t: any): Task {
  const due = t.due_datetime || t.dueDate || '';
  const dueDate = due ? String(due).slice(0, 10) : '';
  return {
    id: String(t.id),
    title: t.title || '',
    description: t.description || '',
    priority: priorityFromInt(Number(t.priority ?? 3)),
    status: statusFromApi(t.status || 'pending'),
    dueDate,
    project: t.project || 'Personal',
    estimatedDuration: Number(t.estimated_duration ?? t.estimatedDuration ?? 30),
    progress: t.status === 'completed' ? 100 : Number(t.progress ?? 0),
    subtasks: [],
    createdAt: t.created_at ? String(t.created_at).slice(0, 10) : new Date().toISOString().slice(0, 10),
  };
}
