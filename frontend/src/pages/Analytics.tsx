import { useState, useEffect } from 'react';
import { TrendingUp, TrendingDown, RefreshCw } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from 'recharts';
import { api } from '../lib/api';

function StatCard({ label, value, trend, trendUp }: { label: string; value: string; trend: string; trendUp: boolean }) {
  return (
    <div style={{
      background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '14px',
      padding: '18px 20px', boxShadow: 'var(--shadow-sm)', transition: 'all 0.2s',
    }}
      onMouseEnter={e => { (e.currentTarget as HTMLElement).style.transform = 'translateY(-2px)'; }}
      onMouseLeave={e => { (e.currentTarget as HTMLElement).style.transform = 'none'; }}
    >
      <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', marginBottom: 10 }}>{label}</div>
      <div style={{ fontSize: '28px', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>{value}</div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 4, marginTop: 8, fontSize: '12px', color: trendUp ? '#06d6a0' : '#ef476f' }}>
        {trendUp ? <TrendingUp size={13} /> : <TrendingDown size={13} />}
        {trend}
      </div>
    </div>
  );
}

const RANGES = ['7 Days', '30 Days', '90 Days'];

export function Analytics() {
  const [range, setRange] = useState('30 Days');
  const [loading, setLoading] = useState(true);
  const [overview, setOverview] = useState<any>(null);
  const [completionTrend, setCompletionTrend] = useState<any[]>([]);
  const [priorityDist, setPriorityDist] = useState<any[]>([]);
  const [productivity, setProductivity] = useState<any>(null);
  const [topTasks, setTopTasks] = useState<any[]>([]);

  useEffect(() => {
    const period = range === '7 Days' ? '7d' : range === '30 Days' ? '30d' : '90d';
    const days = range === '7 Days' ? 7 : range === '30 Days' ? 30 : 90;
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const [ov, ct, pd, ps, tt] = await Promise.all([
          api.analyticsOverview(period),
          api.completionTrend(days),
          api.priorityDistribution(),
          api.productivityScore(),
          api.topCompleted(5),
        ]);
        if (cancelled) return;
        if (ov.success) setOverview(ov.data);
        if (ct.success) setCompletionTrend(ct.data);
        if (pd.success) setPriorityDist(pd.data);
        if (ps.success) setProductivity(ps.data);
        if (tt.success) setTopTasks(tt.data);
      } catch (e) {
        console.error('Analytics load failed', e);
      } finally {
        if (!cancelled) setLoading(false);
      }
      return () => { cancelled = true; };
    })();
  }, [range]);

  const priorityColors = {
    Critical: '#ef476f',
    High: '#ffd166',
    Medium: '#4361ee',
    Low: '#06d6a0',
  };

  return (
    <div style={{ padding: '32px', display: 'flex', flexDirection: 'column', gap: 28 }}>
      {/* Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <h1 style={{ margin: 0, fontSize: '24px', fontWeight: 800 }}>📈 Analytics</h1>
        <div style={{ display: 'flex', gap: 4 }}>
          {RANGES.map(r => (
            <button key={r} onClick={() => setRange(r)} style={{
              padding: '6px 14px', borderRadius: '8px', border: 'none', cursor: 'pointer',
              fontFamily: 'inherit', fontSize: '13px', fontWeight: 500,
              background: range === r ? '#4361ee' : 'var(--bg-secondary)',
              color: range === r ? 'white' : 'var(--text-secondary)',
              transition: 'all 0.15s',
            }}>{r}</button>
          ))}
        </div>
      </div>

      {loading ? (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: 300, color: 'var(--text-muted)' }}>
          Loading analytics…
        </div>
      ) : (
        <>
          {/* Stat cards */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14 }}>
            <StatCard label="TASKS CREATED" value={overview?.tasks_created ?? '—'} trend={overview ? `${overview.completion_rate}% rate` : '—'} trendUp />
            <StatCard label="TASKS COMPLETED" value={overview?.tasks_completed ?? '—'} trend={`avg ${overview?.avg_priority ?? '—'}`} trendUp />
            <StatCard label="PRODUCTIVITY SCORE" value={productivity?.score ?? '—'} trend={productivity ? 'of 100' : '—'} trendUp={productivity && productivity.score >= 50} />
            <StatCard label="AVG PRIORITY" value={overview?.avg_priority ?? '—'} trend={`rate ${overview?.completion_rate ?? '—'}%`} trendUp />
          </div>

          {/* Charts */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>
            {/* Line chart */}
            <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', padding: '20px' }}>
              <div style={{ fontSize: '13px', fontWeight: 700, color: 'var(--text-primary)', marginBottom: 16 }}>Completion Trend</div>
              <ResponsiveContainer width="100%" height={200}>
                <LineChart data={completionTrend}>
                  <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" />
                  <XAxis dataKey="date" tick={{ fontSize: 11, fill: 'var(--text-muted)' }} tickLine={false} axisLine={false} />
                  <YAxis tick={{ fontSize: 11, fill: 'var(--text-muted)' }} tickLine={false} axisLine={false} domain={[0, 100]} unit="%" />
                  <Tooltip
                    contentStyle={{ background: 'var(--bg-primary)', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '12px' }}
                    formatter={(v) => [`${v}%`, 'Completion']}
                  />
                  <Line type="monotone" dataKey="rate" stroke="#4361ee" strokeWidth={2.5} dot={{ fill: 'white', stroke: '#4361ee', strokeWidth: 2, r: 4 }} activeDot={{ r: 6 }} />
                </LineChart>
              </ResponsiveContainer>
            </div>

            {/* Donut chart */}
            <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', padding: '20px' }}>
              <div style={{ fontSize: '13px', fontWeight: 700, color: 'var(--text-primary)', marginBottom: 16 }}>Priority Distribution</div>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 24 }}>
                <ResponsiveContainer width={160} height={160}>
                  <PieChart>
                    <Pie data={priorityDist} cx="50%" cy="50%" innerRadius={45} outerRadius={70} dataKey="count" paddingAngle={3}>
                      {priorityDist.map((entry, i) => (
                        <Cell key={i} fill={priorityColors[entry.label as keyof typeof priorityColors] || '#4361ee'} />
                      ))}
                    </Pie>
                    <Tooltip contentStyle={{ background: 'var(--bg-primary)', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '12px' }} />
                  </PieChart>
                </ResponsiveContainer>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {priorityDist.map(d => (
                    <div key={d.priority} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: '13px' }}>
                      <div style={{ width: 10, height: 10, borderRadius: '50%', background: priorityColors[d.label as keyof typeof priorityColors] || '#4361ee', flexShrink: 0 }} />
                      <span style={{ color: 'var(--text-secondary)' }}>{d.label}</span>
                      <span style={{ fontWeight: 700, color: 'var(--text-primary)', marginLeft: 'auto' }}>{d.count}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Bottom section */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr auto', gap: 20 }}>
            {/* Top tasks */}
            <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', overflow: 'hidden' }}>
              <div style={{ padding: '16px 20px', borderBottom: '1px solid var(--border)', fontSize: '13px', fontWeight: 700, color: 'var(--text-primary)' }}>Top Tasks Completed</div>
              <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '13px' }}>
                <thead>
                  <tr style={{ borderBottom: '1px solid var(--border)' }}>
                    {['Task', 'Project', 'Priority', 'Completed'].map(h => (
                      <th key={h} style={{ padding: '10px 16px', textAlign: 'left', fontSize: '10px', fontWeight: 700, letterSpacing: '0.06em', color: 'var(--text-muted)' }}>{h.toUpperCase()}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {topTasks.map((t, i) => (
                    <tr key={t.id} style={{ borderBottom: '1px solid var(--border)' }}>
                      <td style={{ padding: '10px 16px', fontWeight: 500, color: 'var(--text-primary)' }}>{t.title}</td>
                      <td style={{ padding: '10px 16px' }}><span style={{ background: 'var(--bg-tertiary)', padding: '2px 8px', borderRadius: '4px', fontSize: '11px', color: 'var(--text-secondary)' }}>{t.project || '—'}</span></td>
                      <td style={{ padding: '10px 16px', color: 'var(--text-secondary)', fontFamily: 'JetBrains Mono, monospace', fontSize: '12px' }}>{t.priority}</td>
                      <td style={{ padding: '10px 16px', color: 'var(--text-muted)', fontSize: '12px' }}>{t.completed_at ? new Date(t.completed_at).toLocaleDateString() : '—'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Productivity score */}
            <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', padding: '20px', width: 200, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 14 }}>
              <div style={{ fontSize: '13px', fontWeight: 700, color: 'var(--text-primary)' }}>Productivity Score</div>
              <div style={{ position: 'relative', width: 120, height: 120 }}>
                <svg viewBox="0 0 100 100" style={{ transform: 'rotate(-90deg)' }}>
                  <circle cx="50" cy="50" r="42" fill="none" stroke="var(--bg-tertiary)" strokeWidth="10" />
                  <circle cx="50" cy="50" r="42" fill="none" stroke="#4361ee" strokeWidth="10"
                    strokeDasharray={`${(productivity?.score ?? 0) * 2.64} ${100 * 2.64}`} strokeLinecap="round"
                    style={{ transition: 'stroke-dasharray 1s ease-out' }} />
                </svg>
                <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
                  <div style={{ fontSize: '26px', fontWeight: 800, color: '#4361ee' }}>{productivity?.score ?? '—'}</div>
                  <div style={{ fontSize: '10px', color: 'var(--text-muted)', marginTop: -2 }}>/ 100</div>
                </div>
              </div>
              <div style={{ fontSize: '12px', color: 'var(--text-secondary)', textAlign: 'center', lineHeight: 1.5 }}>
                {productivity?.factors?.[0]?.name ? `🧠 ${productivity.factors[0].name}` : '🧠 Your best hours are 9–11 AM — protect this time'}
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
