<div class="mb-3">
    <label class="form-label">Display Stats</label>
    <select name="show_stats" class="form-select">
        <option value="yes" {{ ($typeParams['show_stats'] ?? 'yes') === 'yes' ? 'selected' : '' }}>Yes</option>
        <option value="no" {{ ($typeParams['show_stats'] ?? 'yes') === 'no' ? 'selected' : '' }}>No</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Number of Stat Cards</label>
    <input type="number" name="stat_count" value="{{ $typeParams['stat_count'] ?? 4 }}" min="2" max="6" class="form-control">
</div>

<div class="mb-3">
    <label class="form-label">Display Options</label>
    <div class="form-check form-switch">
        <input type="checkbox" name="show_overdue" value="yes"
            {{ ($typeParams['show_overdue'] ?? 'yes') === 'yes' ? 'checked' : '' }} class="form-check-input">
        <label class="form-check-label">Show Overdue Count</label>
    </div>
    <div class="form-check form-switch">
        <input type="checkbox" name="show_completed" value="yes"
            {{ ($typeParams['show_completed'] ?? 'yes') === 'yes' ? 'checked' : '' }} class="form-check-input">
        <label class="form-check-label">Show Completed Count (30d)</label>
    </div>
</div>
