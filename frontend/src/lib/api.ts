// src/lib/api.ts
// Thin client over the Nexsus Link Tracker REST API.

export const API_BASE: string =
  (window as any)?.NEXSUS_API_BASE ||
  (import.meta as any).env?.VITE_API_BASE ||
  '/api/v1';

export function getToken(): string {
  return (
    (window as any)?.NEXSUS_API_TOKEN ||
    (import.meta as any).env?.VITE_API_TOKEN ||
    localStorage.getItem('api_token') ||
    ''
  );
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, {
    ...init,
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${getToken()}`,
      ...(init?.headers || {}),
    },
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json() as Promise<T>;
}

export interface Task {
  id: string;
  title: string;
  description?: string;
  priority: number;
  status: string;
  due_datetime?: string;
  project?: string;
  estimated_duration?: number;
  progress?: number;
  subtasks?: Array<{ id: string; title: string; completed: boolean }>;
}

export interface ParsedTask {
  title: string;
  priority: number;
  description?: string;
  due_datetime?: string;
  project?: string;
  estimated_duration?: number;
  subtasks?: string[];
  dependencies?: string[];
  provider: 'openai' | 'anthropic';
}

export interface AnalyticsOverview {
  tasks_created: number;
  tasks_completed: number;
  avg_priority: number;
  completion_rate: number;
  period: string;
}

export interface CompletionTrend {
  date: string;
  completed: number;
  created: number;
  rate: number;
}

export interface PriorityDistribution {
  priority: number;
  label: string;
  count: number;
}

export interface ProductivityScore {
  score: number;
  factors: Array<{ name: string; value: number; weight: number }>;
}

export interface TopCompletedTask {
  id: string;
  title: string;
  priority: number;
  project?: string;
  completed_at?: string;
}

export interface DailyDigest {
  date: string;
  greeting: string;
  today: Task[];
  yesterday_review: {
    completed: number;
    total: number;
    completed_display: string;
    time_spent_hours: number;
    overdue: number;
    rate: number;
  };
  ai_insights: string[];
}

export interface Reminder {
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

export const api = {
  // Tasks
  listTasks: (params?: Record<string, string>) => {
    const qs = params ? '?' + new URLSearchParams(params).toString() : '';
    return request<{ success: boolean; data: { data: Task[] } }>(`/tasks${qs}`);
  },
  createTask: (body: Partial<Task>) =>
    request<{ success: boolean; data: Task }>('/tasks', { method: 'POST', body: JSON.stringify(body) }),
  completeTask: (id: string) =>
    request<{ success: boolean }>(`/tasks/${id}/complete`, { method: 'POST' }),
  deleteTask: (id: string) =>
    request<{ success: boolean }>(`/tasks/${id}`, { method: 'DELETE' }),

  // AI Parser
  parseTask: (text: string) =>
    request<{ success: boolean; data: ParsedTask }>('/tasks/parse', { method: 'POST', body: JSON.stringify({ text }) }),

  // Analytics
  analyticsOverview: (period = '30d') =>
    request<{ success: boolean; data: AnalyticsOverview }>(`/tasks/analytics/overview?period=${period}`),
  completionTrend: (days = 7) =>
    request<{ success: boolean; data: CompletionTrend[] }>(`/tasks/analytics/completion-trend?days=${days}`),
  priorityDistribution: () =>
    request<{ success: boolean; data: PriorityDistribution[] }>('/tasks/analytics/priority-distribution'),
  productivityScore: () =>
    request<{ success: boolean; data: ProductivityScore }>('/tasks/analytics/productivity-score'),
  topCompleted: (limit = 5) =>
    request<{ success: boolean; data: TopCompletedTask[] }>(`/tasks/analytics/top-completed?limit=${limit}`),

  // Daily Digest
  dailyDigest: () =>
    request<{ success: boolean; data: DailyDigest }>('/tasks/daily-digest'),

  // Reminders
  listReminders: () =>
    request<{ success: boolean; data: { data: Reminder[] } }>('/reminders'),
  createReminder: (body: any) =>
    request<{ success: boolean; data: Reminder }>('/reminders', { method: 'POST', body: JSON.stringify(body) }),
};