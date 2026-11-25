import { useState } from 'react';
import { TrendingUp, TrendingDown } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from 'recharts';

const completionData = [
  { date: 'Sep 9', rate: 72 },
  { date: 'Sep 10', rate: 85 },
  { date: 'Sep 11', rate: 60 },
  { date: 'Sep 12', rate: 78 },
  { date: 'Sep 13', rate: 90 },
  { date: 'Sep 14', rate: 82 },
  { date: 'Sep 15', rate: 88 },
  { date: 'Sep 16', rate: 75 },
];

const priorityData = [
  { name: 'Critical', value: 2, color: '#ef476f' },
  { name: 'High', value: 3, color: '#ffd166' },
  { name: 'Medium', value: 4, color: '#4361ee' },
  { name: 'Low', value: 1, color: '#06d6a0' },
];

const topTasks = [
  { name: 'Weekly Invoice Check', duration: '30 min', date: 'Sep 15', project: 'Operations' },
  { name: 'API Documentation Update', duration: '2h 40m', date: 'Sep 14', project: 'Engineering' },
  { name: 'Design System Review', duration: '1h 15m', date: 'Sep 13', project: 'Design' },
  { name: 'Sprint Planning', duration: '1h', date: 'Sep 12', project: 'Engineering' },
  { name: 'Quarterly Budget Review', duration: '45 min', date: 'Sep 11', project: 'Operations' },
];

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
  const [range, setRange] = useState('7 Days');

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

      {/* Stat cards */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14 }}>
        <StatCard label="TASKS CREATED" value="24" trend="+12% vs last period" trendUp />
        <StatCard label="TASKS COMPLETED" value="19" trend="+8% vs last period" trendUp />
        <StatCard label="AVG PRIORITY SCORE" value="3.8 ★" trend="-0.2 vs last period" trendUp={false} />
        <StatCard label="FOCUS TIME" value="6.5h" trend="+22% vs last period" trendUp />
      </div>

      {/* Charts */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>
        {/* Line chart */}
        <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', padding: '20px' }}>
          <div style={{ fontSize: '13px', fontWeight: 700, color: 'var(--text-primary)', marginBottom: 16 }}>Completion Trend</div>
          <ResponsiveContainer width="100%" height={200}>
            <LineChart data={completionData}>
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
                <Pie data={priorityData} cx="50%" cy="50%" innerRadius={45} outerRadius={70} dataKey="value" paddingAngle={3}>
                  {priorityData.map((entry, i) => (
                    <Cell key={i} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip contentStyle={{ background: 'var(--bg-primary)', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '12px' }} />
              </PieChart>
            </ResponsiveContainer>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              {priorityData.map(d => (
                <div key={d.name} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: '13px' }}>
                  <div style={{ width: 10, height: 10, borderRadius: '50%', background: d.color, flexShrink: 0 }} />
                  <span style={{ color: 'var(--text-secondary)' }}>{d.name}</span>
                  <span style={{ fontWeight: 700, color: 'var(--text-primary)', marginLeft: 'auto' }}>{d.value}</span>
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
                {['Task', 'Project', 'Duration', 'Date'].map(h => (
                  <th key={h} style={{ padding: '10px 16px', textAlign: 'left', fontSize: '10px', fontWeight: 700, letterSpacing: '0.06em', color: 'var(--text-muted)' }}>{h.toUpperCase()}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {topTasks.map(t => (
                <tr key={t.name} style={{ borderBottom: '1px solid var(--border)' }}>
                  <td style={{ padding: '10px 16px', fontWeight: 500, color: 'var(--text-primary)' }}>{t.name}</td>
                  <td style={{ padding: '10px 16px' }}><span style={{ background: 'var(--bg-tertiary)', padding: '2px 8px', borderRadius: '4px', fontSize: '11px', color: 'var(--text-secondary)' }}>{t.project}</span></td>
                  <td style={{ padding: '10px 16px', color: 'var(--text-secondary)', fontFamily: 'JetBrains Mono, monospace', fontSize: '12px' }}>{t.duration}</td>
                  <td style={{ padding: '10px 16px', color: 'var(--text-muted)', fontSize: '12px' }}>{t.date}</td>
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
                strokeDasharray={`${78 * 2.64} ${100 * 2.64}`} strokeLinecap="round"
                style={{ transition: 'stroke-dasharray 1s ease-out' }} />
            </svg>
            <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
              <div style={{ fontSize: '26px', fontWeight: 800, color: '#4361ee' }}>78</div>
              <div style={{ fontSize: '10px', color: 'var(--text-muted)', marginTop: -2 }}>/ 100</div>
            </div>
          </div>
          <div style={{ fontSize: '12px', color: 'var(--text-secondary)', textAlign: 'center', lineHeight: 1.5 }}>
            🧠 Your best hours are 9–11 AM — protect this time
          </div>
        </div>
      </div>
    </div>
  );
}
