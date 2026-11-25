import { useState } from 'react';
import { Sparkles, CheckCircle, Pencil, RefreshCw, Zap } from 'lucide-react';
import { PriorityBadge } from '../components/PriorityBadge';
import { Priority } from '../data/tasks';
import { ToastData } from '../components/Toast';

const EXAMPLES = [
  'After my 3pm meeting, prepare the Q4 deck',
  'Remind me weekly to check invoices on Mondays',
  'Call dentist next Tuesday morning, high priority',
  'Finish the API docs before Friday EOD',
];

interface ParsedResult {
  title: string;
  priority: Priority;
  dueDate: string;
  project: string;
  duration: number;
  subtasks: string[];
  provider: 'OpenAI' | 'Anthropic';
}

export function AIParser({ showToast }: { showToast: (t: Omit<ToastData, 'id'>) => void }) {
  const [input, setInput] = useState('');
  const [parsing, setParsing] = useState(false);
  const [result, setResult] = useState<ParsedResult | null>(null);
  const [creating, setCreating] = useState(false);
  const [created, setCreated] = useState(false);

  function handleParse() {
    if (!input.trim()) return;
    setParsing(true);
    setResult(null);
    setTimeout(() => {
      setParsing(false);
      setResult({
        title: input.includes('deck') || input.includes('present') ? 'Prepare the Q4 deck' :
               input.includes('dentist') ? 'Call dentist' :
               input.includes('invoice') ? 'Weekly invoice check' :
               input.includes('API') ? 'Finish API documentation' :
               'Complete task from description',
        priority: input.toLowerCase().includes('high') ? 'high' :
                  input.includes('dentist') ? 'medium' : 'high',
        dueDate: input.includes('Friday') ? '2026-09-18' :
                 input.includes('Tuesday') ? '2026-09-22' : '2026-09-19',
        project: input.includes('deck') ? 'Design' :
                 input.includes('invoice') ? 'Operations' :
                 input.includes('dentist') ? 'Personal' : 'Engineering',
        duration: 90,
        subtasks: input.includes('deck') ? ['Draft outline', 'Add data slides', 'Design slides'] :
                  input.includes('invoice') ? ['Log in to finance portal', 'Review line items'] : [],
        provider: Math.random() > 0.3 ? 'OpenAI' : 'Anthropic',
      });
    }, 1800);
  }

  function handleCreate() {
    setCreating(true);
    setTimeout(() => {
      setCreating(false);
      setCreated(true);
      showToast({ title: 'Task created from AI parse!', description: result?.title, type: 'ai' });
    }, 700);
  }

  return (
    <div style={{ padding: '32px', maxWidth: 720, margin: '0 auto' }}>
      {/* Header */}
      <div style={{ marginBottom: 28 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 8 }}>
          <div style={{ width: 36, height: 36, borderRadius: '10px', background: 'rgba(114,9,183,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Sparkles size={18} style={{ color: '#7209b7' }} />
          </div>
          <h1 style={{ fontSize: '24px', fontWeight: 800, margin: 0, color: 'var(--text-primary)' }}>AI Task Parser</h1>
        </div>
        <p style={{ fontSize: '14px', color: 'var(--text-secondary)', margin: 0 }}>
          Describe your task in plain English. AI will structure it into a complete task with priority, due date, and subtasks.
        </p>
      </div>

      {/* Input area */}
      <div style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', padding: '20px', marginBottom: 20 }}>
        <textarea
          value={input}
          onChange={e => setInput(e.target.value)}
          placeholder='e.g. "Finish the Q4 report by Friday afternoon, high priority, need review from Sarah first"'
          rows={4}
          style={{
            width: '100%', padding: '14px 16px',
            background: 'var(--bg-tertiary)', border: '1px solid var(--border)',
            borderRadius: '10px', fontSize: '15px', color: 'var(--text-primary)',
            fontFamily: 'inherit', resize: 'vertical', outline: 'none',
            lineHeight: 1.6, transition: 'border-color 0.2s',
          }}
          onFocus={e => (e.target.style.borderColor = '#7209b7')}
          onBlur={e => (e.target.style.borderColor = 'var(--border)')}
          onKeyDown={e => { if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) handleParse(); }}
        />
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 12 }}>
          <span style={{ fontSize: '12px', color: 'var(--text-muted)' }}>⌘+Enter to parse</span>
          <button
            onClick={handleParse}
            disabled={!input.trim() || parsing}
            style={{
              display: 'flex', alignItems: 'center', gap: 8,
              padding: '10px 20px', background: input.trim() ? '#7209b7' : 'var(--bg-tertiary)',
              color: input.trim() ? 'white' : 'var(--text-muted)',
              border: 'none', borderRadius: '8px', fontSize: '14px', fontWeight: 600,
              cursor: input.trim() ? 'pointer' : 'not-allowed', fontFamily: 'inherit',
              transition: 'all 0.2s', boxShadow: input.trim() ? '0 4px 12px rgba(114,9,183,0.3)' : 'none',
            }}
          >
            {parsing ? (
              <><span className="animate-spin" style={{ display: 'inline-block', fontSize: '16px' }}>◌</span> Parsing…</>
            ) : (
              <><Sparkles size={15} /> Parse with AI</>
            )}
          </button>
        </div>
      </div>

      {/* Example chips */}
      <div style={{ marginBottom: 28 }}>
        <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '0.08em', color: 'var(--text-muted)', marginBottom: 10 }}>EXAMPLES — CLICK TO TRY</div>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
          {EXAMPLES.map(ex => (
            <button
              key={ex}
              onClick={() => setInput(ex)}
              style={{
                padding: '6px 12px', background: 'var(--bg-tertiary)',
                border: '1px solid var(--border)', borderRadius: '20px',
                fontSize: '12px', color: 'var(--text-secondary)', cursor: 'pointer',
                fontFamily: 'inherit', transition: 'all 0.15s',
              }}
              onMouseEnter={e => { (e.currentTarget as HTMLElement).style.borderColor = '#7209b7'; (e.currentTarget as HTMLElement).style.color = '#7209b7'; }}
              onMouseLeave={e => { (e.currentTarget as HTMLElement).style.borderColor = 'var(--border)'; (e.currentTarget as HTMLElement).style.color = 'var(--text-secondary)'; }}
            >
              {ex}
            </button>
          ))}
        </div>
      </div>

      {/* Results */}
      {result && (
        <div className="animate-fade-in" style={{ background: 'var(--bg-secondary)', border: '1px solid var(--border)', borderRadius: '16px', overflow: 'hidden' }}>
          <div style={{ padding: '16px 20px', borderBottom: '1px solid var(--border)', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
              <CheckCircle size={16} style={{ color: '#06d6a0' }} />
              <span style={{ fontSize: '13px', fontWeight: 600, color: 'var(--text-primary)' }}>Parse Results</span>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: '12px', color: 'var(--text-muted)' }}>
              <Zap size={12} style={{ color: result.provider === 'OpenAI' ? '#06d6a0' : '#7209b7' }} />
              {result.provider}
            </div>
          </div>

          <div style={{ padding: '20px', display: 'flex', flexDirection: 'column', gap: 14 }}>
            <Field label="TITLE" value={result.title} large />
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 14 }}>
              <Field label="PRIORITY" value={<PriorityBadge priority={result.priority} />} />
              <Field label="DUE DATE" value={result.dueDate} />
              <Field label="PROJECT" value={result.project} />
            </div>
            <Field label="DURATION" value={`${result.duration} minutes`} />
            {result.subtasks.length > 0 && (
              <div>
                <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.08em', color: 'var(--text-muted)', marginBottom: 8 }}>SUBTASKS</div>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                  {result.subtasks.map(st => (
                    <span key={st} style={{ padding: '4px 10px', background: 'var(--bg-tertiary)', borderRadius: '4px', fontSize: '12px', color: 'var(--text-secondary)' }}>
                      {st}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {created ? (
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 16px', background: 'rgba(6,214,160,0.1)', borderRadius: '10px', color: '#059669' }}>
                <CheckCircle size={16} /> Task created successfully!
              </div>
            ) : (
              <div style={{ display: 'flex', gap: 10, paddingTop: 4 }}>
                <button
                  onClick={handleCreate}
                  disabled={creating}
                  style={{
                    display: 'flex', alignItems: 'center', gap: 8,
                    padding: '10px 20px', background: '#4361ee', color: 'white',
                    border: 'none', borderRadius: '8px', fontSize: '14px', fontWeight: 600,
                    cursor: 'pointer', fontFamily: 'inherit', transition: 'all 0.15s',
                  }}
                >
                  {creating ? '◌ Creating…' : <><CheckCircle size={15} /> Create as Task</>}
                </button>
                <button
                  onClick={() => { setResult(null); }}
                  style={{
                    display: 'flex', alignItems: 'center', gap: 8,
                    padding: '10px 20px', background: 'none', color: 'var(--text-secondary)',
                    border: '1px solid var(--border)', borderRadius: '8px', fontSize: '14px',
                    cursor: 'pointer', fontFamily: 'inherit',
                  }}
                >
                  <RefreshCw size={14} /> Try again
                </button>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

function Field({ label, value, large }: { label: string; value: React.ReactNode; large?: boolean }) {
  return (
    <div>
      <div style={{ fontSize: '10px', fontWeight: 700, letterSpacing: '0.08em', color: 'var(--text-muted)', marginBottom: 4 }}>{label}</div>
      <div style={{ fontSize: large ? '18px' : '14px', fontWeight: large ? 700 : 500, color: 'var(--text-primary)' }}>{value}</div>
    </div>
  );
}
