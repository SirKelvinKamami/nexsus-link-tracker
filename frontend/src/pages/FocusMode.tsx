import { useState, useEffect, useCallback } from 'react';
import { Moon, Pause, Play, Square, CheckCircle } from 'lucide-react';
import { api } from '../lib/api';

export function FocusMode({ onExit }: { onExit: () => void }) {
  const [tasks, setTasks] = useState<any[]>([]);
  const [selectedTask, setSelectedTask] = useState<any>(null);
  const [seconds, setSeconds] = useState(25 * 60);
  const [running, setRunning] = useState(false);
  const [pomodoro, setPomodoro] = useState(1);
  const [complete, setComplete] = useState(false);
  const [selecting, setSelecting] = useState(true);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const res = await api.listTasks({ status: 'pending,in_progress', per_page: '50' });
        if (!cancelled && res.success) {
          const pending = res.data.data.filter((t: any) => t.status !== 'completed' && t.status !== 'cancelled');
          setTasks(pending);
          if (pending.length > 0 && !selectedTask) setSelectedTask(pending[0]);
        }
      } catch (e) {
        console.error('Focus mode tasks load failed', e);
      } finally {
        if (!cancelled) setLoading(false);
      }
      return () => { cancelled = true; };
    })();
  }, []);

  useEffect(() => {
    if (!running) return;
    if (seconds <= 0) {
      setRunning(false);
      setComplete(true);
      return;
    }
    const t = setInterval(() => setSeconds(s => s - 1), 1000);
    return () => clearInterval(t);
  }, [running, seconds]);

  const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
  const secs = String(seconds % 60).padStart(2, '0');

  const totalSecs = 25 * 60;
  const pct = ((totalSecs - seconds) / totalSecs) * 100;

  const timerColor = seconds > 10 * 60 ? '#06d6a0' : seconds > 5 * 60 ? '#ffd166' : '#ef476f';

  function handleEnd() {
    setRunning(false);
    setSeconds(25 * 60);
    setComplete(false);
    onExit();
  }

  function nextPomodoro() {
    setPomodoro(p => p + 1);
    setSeconds(25 * 60);
    setComplete(false);
    setRunning(false);
  }

  const pendingTasks = tasks.filter((t: any) => t.status !== 'completed' && t.status !== 'cancelled');

  if (loading) {
    return <div style={{ position: 'fixed', inset: 0, zIndex: 2000, background: 'linear-gradient(135deg, #0a1a0a 0%, #1a0a1a 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)' }}>Loading focus mode…</div>;
  }

  if (selecting) {
    return (
      <div style={{
        position: 'fixed', inset: 0, zIndex: 2000,
        background: 'linear-gradient(135deg, #0a1a0a 0%, #1a0a1a 100%)',
        display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
        padding: 32,
      }}>
        <div style={{ textAlign: 'center', marginBottom: 32 }}>
          <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '0.12em', color: '#66bb6a', marginBottom: 12 }}>FOCUS MODE 🌙</div>
          <div style={{ fontSize: '28px', fontWeight: 800, color: '#e8f5e9' }}>Choose a task to focus on</div>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, width: '100%', maxWidth: 480 }}>
          {pendingTasks.map(t => (
            <button
              key={t.id}
              onClick={() => { setSelectedTask(t); setSelecting(false); }}
              style={{
                padding: '16px 20px', background: 'rgba(255,255,255,0.06)',
                border: '1px solid rgba(255,255,255,0.1)', borderRadius: '12px',
                color: '#e8f5e9', fontSize: '15px', fontWeight: 600, cursor: 'pointer',
                fontFamily: 'inherit', textAlign: 'left', transition: 'all 0.15s',
              }}
              onMouseEnter={e => (e.currentTarget as HTMLElement).style.background = 'rgba(255,255,255,0.1)'}
              onMouseLeave={e => (e.currentTarget as HTMLElement).style.background = 'rgba(255,255,255,0.06)'}
            >
              {t.title}
            </button>
          ))}
          <button
            onClick={onExit}
            style={{
              padding: '12px', background: 'none', border: '1px solid rgba(255,255,255,0.1)',
              borderRadius: '10px', color: 'rgba(255,255,255,0.4)', cursor: 'pointer',
              fontFamily: 'inherit', fontSize: '14px', marginTop: 8,
            }}
          >
            Cancel
          </button>
        </div>
      </div>
    );
  }

  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 2000,
      background: 'linear-gradient(135deg, #0a1a0a 0%, #1a0a1a 100%)',
      display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
    }}>
      {/* Glass panel */}
      <div className="animate-fade-in" style={{
        background: 'rgba(30,30,30,0.85)', backdropFilter: 'blur(20px)',
        border: '1px solid rgba(255,255,255,0.1)',
        borderRadius: '28px', padding: '48px 56px',
        display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 28,
        boxShadow: '0 32px 80px rgba(0,0,0,0.6)',
        textAlign: 'center', maxWidth: 480, width: '100%',
      }}>
        {/* Label */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <Moon size={16} style={{ color: '#ffd166' }} />
          <span style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '0.12em', color: '#66bb6a' }}>FOCUS MODE</span>
        </div>

        {/* Task */}
        <div style={{ fontSize: '22px', fontWeight: 800, color: '#e8f5e9', lineHeight: 1.3 }}>
          {selectedTask?.title}
        </div>

        {/* Timer circle */}
        <div style={{ position: 'relative', width: 180, height: 180 }}>
          <svg viewBox="0 0 100 100" style={{ transform: 'rotate(-90deg)', width: '100%', height: '100%' }}>
            <circle cx="50" cy="50" r="44" fill="none" stroke="rgba(255,255,255,0.08)" strokeWidth="8" />
            <circle cx="50" cy="50" r="44" fill="none" stroke={timerColor} strokeWidth="8"
              strokeDasharray={`${pct * 2.765} 276.5`}
              strokeLinecap="round"
              style={{ transition: 'stroke-dasharray 1s linear, stroke 0.5s ease' }}
            />
          </svg>
          <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ fontSize: '42px', fontWeight: 700, color: timerColor, fontFamily: 'JetBrains Mono, monospace', letterSpacing: '-2px' }}>
              {mins}:{secs}
            </span>
          </div>
        </div>

        {/* Session info */}
        <div style={{ fontSize: '13px', color: 'rgba(255,255,255,0.4)' }}>
          Pomodoro {pomodoro} of 4
        </div>

        {/* Completion */}
        {complete && (
          <div className="animate-bounce-in" style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10 }}>
            <CheckCircle size={40} style={{ color: '#06d6a0' }} />
            <div style={{ fontSize: '18px', fontWeight: 700, color: '#e8f5e9' }}>Session complete! Great work 🎉</div>
            <button onClick={nextPomodoro} style={{ padding: '10px 24px', background: '#06d6a0', color: '#0d1a0d', border: 'none', borderRadius: '10px', fontFamily: 'inherit', fontSize: '14px', fontWeight: 700, cursor: 'pointer' }}>
              Start next session
            </button>
          </div>
        )}

        {/* Controls */}
        {!complete && (
          <div style={{ display: 'flex', gap: 12 }}>
            <button
              onClick={() => setRunning(r => !r)}
              style={{
                display: 'flex', alignItems: 'center', gap: 8,
                padding: '12px 28px', background: 'rgba(255,255,255,0.1)',
                border: '1px solid rgba(255,255,255,0.15)', borderRadius: '12px',
                color: '#e8f5e9', fontSize: '14px', fontWeight: 600, cursor: 'pointer',
                fontFamily: 'inherit', transition: 'all 0.15s',
              }}
              onMouseEnter={e => (e.currentTarget as HTMLElement).style.background = 'rgba(255,255,255,0.15)'}
              onMouseLeave={e => (e.currentTarget as HTMLElement).style.background = 'rgba(255,255,255,0.1)'}
            >
              {running ? <><Pause size={16} /> Pause</> : <><Play size={16} /> {seconds < 25 * 60 ? 'Resume' : 'Start'}</>}
            </button>
            <button
              onClick={handleEnd}
              style={{
                display: 'flex', alignItems: 'center', gap: 8,
                padding: '12px 20px', background: 'rgba(239,71,111,0.15)',
                border: '1px solid rgba(239,71,111,0.3)', borderRadius: '12px',
                color: '#ef476f', fontSize: '14px', fontWeight: 600, cursor: 'pointer',
                fontFamily: 'inherit', transition: 'all 0.15s',
              }}
            >
              <Square size={14} /> End
            </button>
          </div>
        )}
      </div>

      {/* Tip */}
      <div style={{ position: 'absolute', bottom: 32, fontSize: '12px', color: 'rgba(255,255,255,0.25)' }}>
        Focus mode blocks notifications except critical ones
      </div>
    </div>
  );
}
