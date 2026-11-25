export type Priority = 'critical' | 'high' | 'medium' | 'low';
export type TaskStatus = 'pending' | 'in-progress' | 'completed' | 'cancelled';

export interface Subtask {
  id: string;
  title: string;
  completed: boolean;
}

export interface Task {
  id: string;
  title: string;
  description?: string;
  priority: Priority;
  status: TaskStatus;
  dueDate: string;
  project: string;
  estimatedDuration: number; // minutes
  progress: number; // 0-100
  subtasks: Subtask[];
  createdAt: string;
  highlight?: boolean;
}

export const PROJECTS = ['Design', 'Engineering', 'Marketing', 'Personal', 'Operations'];

export const INITIAL_TASKS: Task[] = [
  {
    id: '1',
    title: 'Finalize Q3 Strategy Report',
    description: 'Complete the executive summary and data visualizations for Q3 board meeting.',
    priority: 'critical',
    status: 'in-progress',
    dueDate: '2026-09-16',
    project: 'Operations',
    estimatedDuration: 120,
    progress: 65,
    subtasks: [
      { id: 's1', title: 'Executive summary draft', completed: true },
      { id: 's2', title: 'Data visualizations', completed: true },
      { id: 's3', title: 'Review with manager', completed: false },
    ],
    createdAt: '2026-09-14',
  },
  {
    id: '2',
    title: 'Client Email Follow-ups',
    description: 'Send follow-up emails to all leads from the Acme meeting.',
    priority: 'high',
    status: 'pending',
    dueDate: '2026-09-16',
    project: 'Marketing',
    estimatedDuration: 45,
    progress: 0,
    subtasks: [],
    createdAt: '2026-09-15',
  },
  {
    id: '3',
    title: 'Team Sync Prep',
    description: 'Prepare agenda and slides for the Friday all-hands.',
    priority: 'medium',
    status: 'pending',
    dueDate: '2026-09-17',
    project: 'Engineering',
    estimatedDuration: 60,
    progress: 20,
    subtasks: [
      { id: 's4', title: 'Draft agenda', completed: true },
      { id: 's5', title: 'Collect team updates', completed: false },
    ],
    createdAt: '2026-09-15',
  },
  {
    id: '4',
    title: 'Review Onboarding Design',
    description: 'Give feedback on the new onboarding flow mockups from the design team.',
    priority: 'medium',
    status: 'pending',
    dueDate: '2026-09-18',
    project: 'Design',
    estimatedDuration: 30,
    progress: 0,
    subtasks: [],
    createdAt: '2026-09-15',
  },
  {
    id: '5',
    title: 'Dentist Appointment',
    description: 'Annual checkup — confirm address and insurance.',
    priority: 'low',
    status: 'pending',
    dueDate: '2026-09-22',
    project: 'Personal',
    estimatedDuration: 90,
    progress: 0,
    subtasks: [],
    createdAt: '2026-09-15',
  },
  {
    id: '6',
    title: 'Weekly Invoice Check',
    description: 'Reconcile invoices and flag any discrepancies to finance.',
    priority: 'high',
    status: 'completed',
    dueDate: '2026-09-15',
    project: 'Operations',
    estimatedDuration: 30,
    progress: 100,
    subtasks: [],
    createdAt: '2026-09-13',
  },
  {
    id: '7',
    title: 'API Documentation Update',
    description: 'Update the developer docs to reflect the new v3 endpoints.',
    priority: 'medium',
    status: 'in-progress',
    dueDate: '2026-09-19',
    project: 'Engineering',
    estimatedDuration: 180,
    progress: 40,
    subtasks: [
      { id: 's6', title: 'Authentication section', completed: true },
      { id: 's7', title: 'Endpoints reference', completed: false },
      { id: 's8', title: 'Code examples', completed: false },
    ],
    createdAt: '2026-09-12',
  },
];

export const priorityColors: Record<Priority, { bg: string; text: string; border: string }> = {
  critical: { bg: 'rgba(239,71,111,0.12)', text: '#ef476f', border: '#ef476f' },
  high: { bg: 'rgba(255,209,102,0.15)', text: '#d97706', border: '#ffd166' },
  medium: { bg: 'rgba(67,97,238,0.12)', text: '#4361ee', border: '#4361ee' },
  low: { bg: 'rgba(6,214,160,0.12)', text: '#059669', border: '#06d6a0' },
};

export const priorityLabel: Record<Priority, string> = {
  critical: 'Critical',
  high: 'High',
  medium: 'Medium',
  low: 'Low',
};
