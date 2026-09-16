<div class="mb-3">
    <label class="form-label">Display Type</label>
    <select name="display_type" class="form-select">
        <option value="tasks" {{ ($typeParams['display_type'] ?? 'tasks') === 'tasks' ? 'selected' : '' }}>Tasks</option>
        <option value="reminders" {{ ($typeParams['display_type'] ?? 'tasks') === 'reminders' ? 'selected' : '' }}>Reminders</option>
        <option value="both" {{ ($typeParams['display_type'] ?? 'tasks') === 'both' ? 'selected' : '' }}>Tasks & Reminders</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Max Items</label>
    <input type="number" name="max_items" value="{{ $typeParams['max_items'] ?? 10 }}" min="1" max="50" class="form-control">
</div>

<div class="mb-3">
    <label class="form-label">Display Options</label>
    <div class="form-check form-switch">
        <input type="checkbox" name="show_priority" value="yes"
            {{ ($typeParams['show_priority'] ?? 'yes') === 'yes' ? 'checked' : '' }} class="form-check-input">
        <label class="form-check-label">Show Priority</label>
    </div>
    <div class="form-check form-switch">
        <input type="checkbox" name="show_due_date" value="yes"
            {{ ($typeParams['show_due_date'] ?? 'yes') === 'yes' ? 'checked' : '' }} class="form-check-input">
        <label class="form-check-label">Show Due Date</label>
    </div>
    <div class="form-check form-switch">
        <input type="checkbox" name="show_project" value="yes"
            {{ ($typeParams['show_project'] ?? 'yes') === 'yes' ? 'checked' : '' }} class="form-check-input">
        <label class="form-check-label">Show Project</label>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status Filter (optional)</label>
    <select name="status_filter" class="form-select">
        <option value="">All Statuses</option>
        <option value="pending" {{ ($typeParams['status_filter'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="in_progress" {{ ($typeParams['status_filter'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
        <option value="completed" {{ ($typeParams['status_filter'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
        <option value="cancelled" {{ ($typeParams['status_filter'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="snoozed" {{ ($typeParams['status_filter'] ?? '') === 'snoozed' ? 'selected' : '' }}>Snoozed</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Priority Filter (optional)</label>
    <select name="priority_filter" class="form-select">
        <option value="">All Priorities</option>
        <option value="1" {{ ($typeParams['priority_filter'] ?? '') == 1 ? 'selected' : '' }}>Low</option>
        <option value="2" {{ ($typeParams['priority_filter'] ?? '') == 2 ? 'selected' : '' }}>Below Normal</option>
        <option value="3" {{ ($typeParams['priority_filter'] ?? '') == 3 ? 'selected' : '' }}>Normal</option>
        <option value="4" {{ ($typeParams['priority_filter'] ?? '') == 4 ? 'selected' : '' }}>High</option>
        <option value="5" {{ ($typeParams['priority_filter'] ?? '') == 5 ? 'selected' : '' }}>Critical</option>
    </select>
</div>
