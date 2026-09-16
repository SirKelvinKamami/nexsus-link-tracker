<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tasks — Nexsus Link Tracker</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('partials.navbar')

    <main class="max-w-7xl mx-auto py-6 px-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
            <button onclick="showTaskModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + New Task
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6 flex gap-4 items-center">
            <select id="taskStatusFilter" onchange="loadTasks()" class="border rounded px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="snoozed">Snoozed</option>
            </select>
            <select id="taskPriorityFilter" onchange="loadTasks()" class="border rounded px-3 py-2 text-sm">
                <option value="">All Priority</option>
                <option value="1">Low</option>
                <option value="2">Below Normal</option>
                <option value="3">Normal</option>
                <option value="4">High</option>
                <option value="5">Critical</option>
            </select>
            <input type="text" id="taskSearch" placeholder="Search tasks..." onkeyup="loadTasks()"
                class="border rounded px-3 py-2 text-sm flex-1">
            <a href="/api/v1/tasks/daily-digest" target="_blank" class="text-sm text-blue-600 hover:underline">
                📋 Daily Digest
            </a>
        </div>

        <!-- AI Parse Section -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h2 class="text-lg font-bold mb-3">🤖 AI Task Parser</h2>
            <p class="text-sm text-gray-500 mb-3">Type a task in plain English and let AI structure it.</p>
            <div class="flex gap-3">
                <textarea id="parseText" rows="2" placeholder='e.g. "Finish the report by Friday afternoon, high priority"'
                    class="flex-1 border rounded px-3 py-2 text-sm resize-none"></textarea>
                <button onclick="parseTask()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 self-end">
                    Parse with AI
                </button>
            </div>
            <div id="parseResult" class="hidden mt-3 p-3 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold text-gray-800" id="parseTitle"></h4>
                    <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-800" id="parseProvider"></span>
                </div>
                <div id="parseDetails" class="text-sm text-gray-600"></div>
                <button onclick="fillParseIntoForm()" class="mt-2 text-sm bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700">
                    Create as Task →
                </button>
            </div>
        </div>

        <!-- Task List -->
        <div id="taskList" class="space-y-3">
            <!-- Tasks loaded via API -->
        </div>

        <!-- Task Modal -->
        <div id="taskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                <h2 class="text-xl font-bold mb-4" id="taskModalTitle">New Task</h2>
                <form id="taskForm" onsubmit="saveTask(event)">
                    <input type="hidden" id="taskId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Title *</label>
                        <input type="text" id="taskTitle" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea id="taskDescription" rows="3"
                            class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Priority</label>
                            <select id="taskPriority" class="w-full border rounded px-3 py-2">
                                <option value="1">Low</option>
                                <option value="2">Below Normal</option>
                                <option value="3" selected>Normal</option>
                                <option value="4">High</option>
                                <option value="5">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Due Date</label>
                            <input type="datetime-local" id="taskDueDate"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Project (optional)</label>
                        <input type="text" id="taskProject"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="closeTaskModal()" class="px-4 py-2 border rounded hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const API_BASE = '/api/v1';
        const TOKEN = localStorage.getItem('api_token') || '{{ session("api_token", "") }}';

        async function loadTasks() {
            const status = document.getElementById('taskStatusFilter').value;
            const priority = document.getElementById('taskPriorityFilter').value;
            const search = document.getElementById('taskSearch').value;

            let url = `${API_BASE}/tasks?per_page=50`;
            if (status) url += `&status=${status}`;
            if (priority) url += `&priority=${priority}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;

            try {
                const res = await fetch(url, {
                    headers: { 'Authorization': `Bearer ${TOKEN}` }
                });
                const { success, data } = await res.json();
                if (success) renderTasks(data.data);
            } catch (e) {
                console.error('Failed to load tasks', e);
            }
        }

        function renderTasks(tasks) {
            const container = document.getElementById('taskList');
            if (!tasks.length) {
                container.innerHTML = '<div class="text-center py-12 text-gray-500">No tasks yet. Click "New Task" to get started.</div>';
                return;
            }

            const priorityColors = { 1: 'bg-gray-400', 2: 'bg-blue-400', 3: 'bg-green-400', 4: 'bg-orange-400', 5: 'bg-red-400' };
            const statusColors = {
                pending: 'bg-yellow-100 text-yellow-800',
                in_progress: 'bg-blue-100 text-blue-800',
                completed: 'bg-green-100 text-green-800',
                cancelled: 'bg-gray-100 text-gray-800',
                snoozed: 'bg-purple-100 text-purple-800',
            };

            container.innerHTML = tasks.map(task => `
                <div class="bg-white rounded-lg shadow p-4 flex items-start gap-4">
                    <div class="w-2 h-2 rounded-full mt-2 ${priorityColors[task.priority] || 'bg-gray-400'}"></div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="font-semibold ${task.status === 'completed' ? 'line-through text-gray-400' : ''}">${escapeHtml(task.title)}</h3>
                            <span class="text-xs px-2 py-1 rounded ${statusColors[task.status] || ''}">${task.status.replace('_', ' ')}</span>
                        </div>
                        ${task.description ? `<p class="text-sm text-gray-600 mt-1">${escapeHtml(task.description)}</p>` : ''}
                        <div class="flex gap-3 mt-2 text-xs text-gray-500">
                            ${task.project ? `<span>📁 ${escapeHtml(task.project)}</span>` : ''}
                            ${task.due_datetime ? `<span>⏰ ${new Date(task.due_datetime).toLocaleString()}</span>` : ''}
                            <span>⚡ Priority ${task.priority}/5</span>
                        </div>
                        <div class="flex gap-2 mt-3">
                            ${task.status !== 'completed' ? `<button onclick="completeTask(${task.id})" class="text-green-600 hover:underline text-sm">Complete</button>` : ''}
                            <button onclick="editTask(${task.id})" class="text-blue-600 hover:underline text-sm">Edit</button>
                            <button onclick="deleteTask(${task.id})" class="text-red-600 hover:underline text-sm">Delete</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function showTaskModal(task = null) {
            const modal = document.getElementById('taskModal');
            const title = document.getElementById('taskModalTitle');
            const form = document.getElementById('taskForm');
            form.reset();

            if (task) {
                title.textContent = 'Edit Task';
                document.getElementById('taskId').value = task.id;
                document.getElementById('taskTitle').value = task.title;
                document.getElementById('taskDescription').value = task.description || '';
                document.getElementById('taskPriority').value = task.priority;
                document.getElementById('taskProject').value = task.project || '';
                if (task.due_datetime) {
                    document.getElementById('taskDueDate').value = task.due_datetime.replace(' ', 'T');
                }
            } else {
                title.textContent = 'New Task';
            }
            modal.classList.remove('hidden');
        }

        function closeTaskModal() {
            document.getElementById('taskModal').classList.add('hidden');
        }

        async function saveTask(e) {
            e.preventDefault();
            const id = document.getElementById('taskId').value;
            const url = id ? `${API_BASE}/tasks/${id}` : `${API_BASE}/tasks`;
            const method = id ? 'PUT' : 'POST';

            const body = {
                title: document.getElementById('taskTitle').value,
                description: document.getElementById('taskDescription').value,
                priority: parseInt(document.getElementById('taskPriority').value),
                project: document.getElementById('taskProject').value,
                due_datetime: document.getElementById('taskDueDate').value ? new Date(document.getElementById('taskDueDate').value).toISOString() : null,
            };

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${TOKEN}`,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(body),
                });
                const { success } = await res.json();
                if (success) {
                    closeTaskModal();
                    loadTasks();
                }
            } catch (e) {
                console.error('Failed to save task', e);
            }
        }

        async function completeTask(id) {
            try {
                await fetch(`${API_BASE}/tasks/${id}/complete`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${TOKEN}` },
                });
                loadTasks();
            } catch (e) {
                console.error('Failed to complete task', e);
            }
        }

        async function deleteTask(id) {
            if (!confirm('Delete this task?')) return;
            try {
                await fetch(`${API_BASE}/tasks/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${TOKEN}` },
                });
                loadTasks();
            } catch (e) {
                console.error('Failed to delete task', e);
            }
        }

        function editTask(id) {
            fetch(`${API_BASE}/tasks/${id}`, {
                headers: { 'Authorization': `Bearer ${TOKEN}` },
            })
            .then(res => res.json())
            .then(({ success, data }) => {
                if (success) showTaskModal(data);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        let lastParsedResult = null;

        async function parseTask() {
            const text = document.getElementById('parseText').value.trim();
            if (!text) return;

            const resultDiv = document.getElementById('parseResult');
            resultDiv.classList.add('hidden');

            try {
                const res = await fetch(`${API_BASE}/tasks/parse`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${TOKEN}`,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ text }),
                });
                const { success, data } = await res.json();

                if (!success) {
                    alert('Parse failed: ' + (data?.message || 'Unknown error'));
                    return;
                }

                lastParsedResult = data;

                document.getElementById('parseTitle').textContent = data.title || 'Parsed Task';
                document.getElementById('parseProvider').textContent = data.provider || 'unknown';

                let details = '';
                if (data.priority) details += `<div>⚡ Priority: ${data.priority}/5</div>`;
                if (data.description) details += `<div>📝 ${escapeHtml(data.description)}</div>`;
                if (data.due_datetime) details += `<div>⏰ Due: ${new Date(data.due_datetime).toLocaleString()}</div>`;
                if (data.project) details += `<div>📁 Project: ${escapeHtml(data.project)}</div>`;
                if (data.estimated_duration) details += `<div>⏱️ Est: ${data.estimated_duration} min</div>`;
                if (data.subtasks?.length) details += `<div>📋 Subtasks: ${data.subtasks.map(escapeHtml).join(', ')}</div>`;
                if (data.dependencies?.length) details += `<div>🔗 Depends on: ${data.dependencies.map(escapeHtml).join(', ')}</div>`;

                document.getElementById('parseDetails').innerHTML = details;
                resultDiv.classList.remove('hidden');
            } catch (e) {
                console.error('Parse failed', e);
                alert('Failed to parse task');
            }
        }

        function fillParseIntoForm() {
            if (!lastParsedResult) return;
            showTaskModal();
            document.getElementById('taskTitle').value = lastParsedResult.title || '';
            document.getElementById('taskDescription').value = lastParsedResult.description || '';
            document.getElementById('taskPriority').value = lastParsedResult.priority || 3;
            document.getElementById('taskProject').value = lastParsedResult.project || '';
            if (lastParsedResult.due_datetime) {
                const d = new Date(lastParsedResult.due_datetime);
                document.getElementById('taskDueDate').value = d.toISOString().slice(0, 16);
            }
            document.getElementById('parseResult').classList.add('hidden');
        }

        // Load tasks on page load
        document.addEventListener('DOMContentLoaded', loadTasks);
    </script>
</body>
</html>
