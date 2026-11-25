import { Priority, priorityColors, priorityLabel } from '../data/tasks';

export function PriorityBadge({ priority }: { priority: Priority }) {
  const colors = priorityColors[priority];
  return (
    <span
      style={{
        background: colors.bg,
        color: colors.text,
        fontSize: '11px',
        fontWeight: 600,
        padding: '2px 8px',
        borderRadius: '9999px',
        letterSpacing: '0.02em',
        whiteSpace: 'nowrap',
      }}
    >
      {priorityLabel[priority]}
    </span>
  );
}
