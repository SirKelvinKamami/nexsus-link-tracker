import { useState, useCallback } from 'react';
import {
  CheckSquare, Calendar, BarChart2, Bell, Moon, Newspaper, Sparkles,
  Brain, Settings, User, Search, Menu, X, Sun, ChevronDown, LayoutGrid,
  List, Clock,
} from 'lucide-react';
import { Dashboard } from './pages/Dashboard';
import { AIParser } from './pages/AIParser';
import { CalendarView } from './pages/CalendarView';
import { Reminders } from './pages/Reminders';
import { Analytics } from './pages/Analytics';
import { FocusMode } from './pages/FocusMode';
import { DailyDigest } from './pages/DailyDigest';
import { ToastContainer, ToastData } from './components/Toast';

type Page = 'dashboard' | 'ai-parse' | 'calendar' | 'reminders' | 'analytics' | 'focus' | 'digest';

interface NavItem {
  id: Page;
  label: string;
  icon: React.ReactNode;
  badge?: number;
}

const NAV_SECTIONS: { label: string; items: NavItem[] }[] = [
  {
    label: 'MY TASKS',
    items: [
      { id: 'dashboard', label: 'All Tasks', icon: <CheckSquare size={15} /> },
      { id: 'calendar', label: 'Calendar', icon: <Calendar size={15} /> },
      { id: 'analytics', label: 'Analytics', icon: <BarChart2 size={15} /> },
    ],
  },
  {
    label: 'AI FEATURES',
    items: [
      { id: 'ai-parse', label: 'AI Parse', icon: <Sparkles size={15} /> },
    ],
  },
  {
    label: 'TOOLS',
    items: [
      { id: 'reminders', label: 'Reminders', icon: <Bell size={15} />, badge: 2 },
      { id: 'focus', label: 'Focus Mode', icon: <Moon size={15} /> },
      { id: 'digest', label: 'Daily Digest', icon: <Newspaper size={15} /> },
    ],
  },
];

export default function App() {
  const [page, setPage] = useState<Page>('dashboard');
  const [dark, setDark] = useState(true);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [notifOpen, setNotifOpen] = useState(false);
  const [profileOpen, setProfileOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [toasts, setToasts] = useState<ToastData[]>([]);

  const showToast = useCallback((t: Omit<ToastData, 'id'>) => {
    const id = Date.now().toString();
    setToasts(prev => [...prev.slice(-3), { ...t, id }]);
  }, []);

  const dismissToast = useCallback((id: string) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  }, []);

  function navigate(p: Page) {
    setPage(p);
    setSidebarOpen(false);
  }

  if (page === 'focus') {
    return (
      <>
        <FocusMode onExit={() => setPage('dashboard')} />
        <ToastContainer toasts={toasts} onDismiss={dismissToast} />
      </>
    );
  }

  return (
    <div className={dark ? 'dark' : ''} style={{ height: '100vh', display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
      {/* Top Nav */}
      <nav style={{
        height: 'var(--topnav-height)', background: 'var(--glass-bg)',
        backdropFilter: `blur(var(--glass-blur))`,
        borderBottom: '1px solid var(--border)',
        display: 'flex', alignItems: 'center', gap: 12,
        padding: '0 20px', flexShrink: 0, position: 'sticky', top: 0, zIndex: 100,
      }}>
        {/* Hamburger (mobile) */}
        <button
          onClick={() => setSidebarOpen(s => !s)}
          style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)', display: 'flex', padding: 6 }}
          className="menu-btn"
        >
          {sidebarOpen ? <X size={18} /> : <Menu size={18} />}
        </button>

        {/* Logo */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginRight: 12 }}>
          <div style={{ width: 28, height: 28, borderRadius: '8px', background: 'linear-gradient(135deg, #4361ee, #7209b7)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <CheckSquare size={14} style={{ color: 'white' }} />
          </div>
          <span style={{ fontWeight: 800, fontSize: '15px', color: 'var(--text-primary)', letterSpacing: '-0.3px' }}>Nexsus Tasks</span>
        </div>

        {/* Search */}
        <div style={{ flex: 1, maxWidth: 300, position: 'relative' }}>
          <Search size={13} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
          <input
            placeholder="Search tasks…"
            value={search}
            onChange={e => setSearch(e.target.value)}
            style={{
              width: '100%', paddingLeft: 30, paddingRight: 12, paddingTop: 7, paddingBottom: 7,
              background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
              borderRadius: '8px', fontSize: '13px', color: 'var(--text-primary)',
              fontFamily: 'inherit', outline: 'none',
            }}
          />
        </div>

        {/* AI parse shortcut */}
        <button
          onClick={() => navigate('ai-parse')}
          style={{
            display: 'flex', alignItems: 'center', gap: 6,
            padding: '6px 12px', background: 'rgba(114,9,183,0.1)',
            border: '1px solid rgba(114,9,183,0.2)', borderRadius: '8px',
            fontSize: '12px', fontWeight: 600, color: '#7209b7',
            cursor: 'pointer', fontFamily: 'inherit', transition: 'all 0.15s',
          }}
        >
          <Sparkles size={13} /> AI Parse
        </button>

        <div style={{ flex: 1 }} />

        {/* Notifications */}
        <div style={{ position: 'relative' }}>
          <button
            onClick={() => { setNotifOpen(n => !n); setProfileOpen(false); }}
            style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)', display: 'flex', position: 'relative', padding: 6, borderRadius: '8px' }}
          >
            <Bell size={17} className={notifOpen ? 'animate-bell' : ''} />
            <span style={{
              position: 'absolute', top: 2, right: 2, width: 16, height: 16,
              background: '#ef476f', borderRadius: '50%', color: 'white',
              fontSize: '9px', fontWeight: 700, display: 'flex', alignItems: 'center', justifyContent: 'center',
              lineHeight: 1,
            }}>2</span>
          </button>
          {notifOpen && (
            <div style={{
              position: 'absolute', right: 0, top: '100%', marginTop: 8, width: 320,
              background: 'var(--bg-primary)', border: '1px solid var(--border)',
              borderRadius: '14px', boxShadow: 'var(--shadow-lg)', zIndex: 200, overflow: 'hidden',
            }}>
              <div style={{ padding: '12px 16px', borderBottom: '1px solid var(--border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontWeight: 700, fontSize: '13px' }}>Notifications</span>
                <span style={{ fontSize: '11px', color: '#4361ee', cursor: 'pointer' }}>Mark all read</span>
              </div>
              {[
                { title: 'Q3 Report due today', desc: 'Finalize Q3 Strategy Report — 5:00 PM', color: '#ef476f' },
                { title: 'Client Email Follow-ups', desc: 'Due today at 3:00 PM', color: '#ffd166' },
              ].map((n, i) => (
                <div key={i} style={{ padding: '12px 16px', borderBottom: i === 0 ? '1px solid var(--border)' : 'none', display: 'flex', gap: 10, cursor: 'pointer' }}
                  onMouseEnter={e => (e.currentTarget as HTMLElement).style.background = 'var(--bg-secondary)'}
                  onMouseLeave={e => (e.currentTarget as HTMLElement).style.background = 'none'}
                >
                  <div style={{ width: 8, height: 8, borderRadius: '50%', background: n.color, marginTop: 5, flexShrink: 0 }} />
                  <div>
                    <div style={{ fontSize: '13px', fontWeight: 600, color: 'var(--text-primary)' }}>{n.title}</div>
                    <div style={{ fontSize: '12px', color: 'var(--text-secondary)', marginTop: 2 }}>{n.desc}</div>
                  </div>
                </div>
              ))}
              <div style={{ padding: '10px 16px', textAlign: 'center' }}>
                <button onClick={() => { navigate('reminders'); setNotifOpen(false); }} style={{ fontSize: '12px', color: '#4361ee', background: 'none', border: 'none', cursor: 'pointer', fontFamily: 'inherit' }}>
                  View all reminders →
                </button>
              </div>
            </div>
          )}
        </div>

        {/* Theme toggle */}
        <button
          onClick={() => setDark(d => !d)}
          style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)', display: 'flex', padding: 6, borderRadius: '8px', transition: 'color 0.2s' }}
          title={dark ? 'Switch to light mode' : 'Switch to dark mode'}
        >
          {dark ? <Sun size={17} /> : <Moon size={17} />}
        </button>

        {/* Profile */}
        <div style={{ position: 'relative' }}>
          <button
            onClick={() => { setProfileOpen(p => !p); setNotifOpen(false); }}
            style={{ display: 'flex', alignItems: 'center', gap: 6, background: 'none', border: 'none', cursor: 'pointer', padding: '4px 8px', borderRadius: '8px' }}
          >
            <div style={{ width: 28, height: 28, borderRadius: '50%', background: 'linear-gradient(135deg, #4361ee, #7209b7)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12px', fontWeight: 700, color: 'white' }}>A</div>
            <span style={{ fontSize: '13px', fontWeight: 600, color: 'var(--text-primary)' }}>Alex</span>
            <ChevronDown size={12} style={{ color: 'var(--text-muted)' }} />
          </button>
          {profileOpen && (
            <div style={{
              position: 'absolute', right: 0, top: '100%', marginTop: 8, width: 200,
              background: 'var(--bg-primary)', border: '1px solid var(--border)',
              borderRadius: '12px', boxShadow: 'var(--shadow-lg)', zIndex: 200, overflow: 'hidden',
            }}>
              {[
                { label: 'Profile', icon: <User size={13} /> },
                { label: 'Settings', icon: <Settings size={13} /> },
                { label: dark ? 'Light mode' : 'Dark mode', icon: dark ? <Sun size={13} /> : <Moon size={13} />, action: () => setDark(d => !d) },
              ].map(({ label, icon, action }) => (
                <button
                  key={label}
                  onClick={() => { action?.(); setProfileOpen(false); }}
                  style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 14px', background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)', fontSize: '13px', fontFamily: 'inherit', textAlign: 'left' }}
                  onMouseEnter={e => (e.currentTarget as HTMLElement).style.background = 'var(--bg-secondary)'}
                  onMouseLeave={e => (e.currentTarget as HTMLElement).style.background = 'none'}
                >
                  {icon} {label}
                </button>
              ))}
            </div>
          )}
        </div>
      </nav>

      {/* Body */}
      <div style={{ flex: 1, display: 'flex', overflow: 'hidden', position: 'relative' }}>
        {/* Sidebar overlay on mobile */}
        {sidebarOpen && (
          <div
            style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.4)', zIndex: 49 }}
            onClick={() => setSidebarOpen(false)}
          />
        )}

        {/* Left Sidebar */}
        <aside style={{
          width: 'var(--sidebar-width)', flexShrink: 0,
          background: 'var(--bg-secondary)', borderRight: '1px solid var(--border)',
          display: 'flex', flexDirection: 'column', overflow: 'hidden',
          position: 'relative', zIndex: 50,
          transition: 'transform 0.25s ease',
        }}>
          <nav style={{ flex: 1, overflowY: 'auto', padding: '16px 10px' }}>
            {NAV_SECTIONS.map(section => (
              <div key={section.label} style={{ marginBottom: 20 }}>
                <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.1em', color: 'var(--text-muted)', padding: '4px 10px', marginBottom: 4 }}>
                  {section.label}
                </div>
                {section.items.map(item => {
                  const active = page === item.id;
                  return (
                    <button
                      key={item.id}
                      onClick={() => navigate(item.id)}
                      style={{
                        width: '100%', display: 'flex', alignItems: 'center', gap: 10,
                        padding: '8px 10px', borderRadius: '8px', border: 'none', cursor: 'pointer',
                        background: active ? 'rgba(67,97,238,0.1)' : 'transparent',
                        color: active ? '#4361ee' : 'var(--text-secondary)',
                        fontSize: '13px', fontWeight: active ? 600 : 400,
                        fontFamily: 'inherit', textAlign: 'left', transition: 'all 0.15s',
                        position: 'relative',
                      }}
                      onMouseEnter={e => { if (!active) (e.currentTarget as HTMLElement).style.background = 'var(--bg-tertiary)'; }}
                      onMouseLeave={e => { if (!active) (e.currentTarget as HTMLElement).style.background = 'transparent'; }}
                    >
                      <span style={{ color: active ? '#4361ee' : 'inherit' }}>{item.icon}</span>
                      <span style={{ flex: 1 }}>{item.label}</span>
                      {item.badge && (
                        <span style={{ background: '#ef476f', color: 'white', fontSize: '10px', fontWeight: 700, padding: '1px 6px', borderRadius: '99px', minWidth: 18, textAlign: 'center' }}>
                          {item.badge}
                        </span>
                      )}
                      {active && <div style={{ position: 'absolute', left: 0, top: '20%', bottom: '20%', width: 3, borderRadius: '0 2px 2px 0', background: '#4361ee' }} />}
                    </button>
                  );
                })}
              </div>
            ))}
          </nav>

          {/* Bottom links */}
          <div style={{ padding: '12px 10px', borderTop: '1px solid var(--border)' }}>
            <button style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '8px 10px', borderRadius: '8px', border: 'none', cursor: 'pointer', background: 'transparent', color: 'var(--text-muted)', fontSize: '13px', fontFamily: 'inherit' }}>
              <Settings size={14} /> Settings
            </button>
          </div>
        </aside>

        {/* Main content */}
        <main style={{ flex: 1, overflowY: 'auto', position: 'relative' }}
          onClick={() => { setNotifOpen(false); setProfileOpen(false); }}
        >
          {page === 'dashboard' && <Dashboard showToast={showToast} />}
          {page === 'ai-parse' && <AIParser showToast={showToast} />}
          {page === 'calendar' && <CalendarView />}
          {page === 'reminders' && <Reminders />}
          {page === 'analytics' && <Analytics />}
          {page === 'digest' && <DailyDigest onDismiss={() => navigate('dashboard')} />}
        </main>
      </div>

      <ToastContainer toasts={toasts} onDismiss={dismissToast} />
    </div>
  );
}
