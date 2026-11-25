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

export const api = {
  listTasks: (params?: Record<string, string>) => {
    const qs = params ? '?' + new URLSearchParams(params).toString() : '';
    return request<{ success: boolean; data: { data: any[] } }>(`/tasks${qs}`);
  },
  createTask: (body: any) =>
    request<{ success: boolean; data: any }>('/tasks', { method: 'POST', body: JSON.stringify(body) }),
  completeTask: (id: string) =>
    request<{ success: boolean }>(`/tasks/${id}/complete`, { method: 'POST' }),
  deleteTask: (id: string) =>
    request<{ success: boolean }>(`/tasks/${id}`, { method: 'DELETE' }),
  analyticsOverview: (period = '30d') =>
    request<{ success: boolean; data: any }>(`/tasks/analytics/overview?period=${period}`),
  productivityScore: () =>
    request<{ success: boolean; data: any }>('/tasks/analytics/productivity-score'),
  parseTask: (text: string) =>
    request<{ success: boolean; data: any }>('/tasks/parse', { method: 'POST', body: JSON.stringify({ text }) }),
};
