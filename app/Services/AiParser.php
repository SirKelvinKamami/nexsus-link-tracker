<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiParser
{
    private const OPENAI_URL = 'https://api.openai.com/v1/chat/completions';
    private const ANTHROPIC_URL = 'https://api.anthropic.com/v1/messages';

    /**
     * Parse natural language text into structured task data.
     *
     * @return array{title: string, description: ?string, priority: int, due_datetime: ?string, project: ?string, estimated_duration: ?int, subtasks: array, dependencies: array, raw_text: string, provider: string}
     */
    public function parse(string $text): array
    {
        $result = $this->parseWithOpenAI($text);

        if ($result === null) {
            Log::warning('AiParser: OpenAI failed, trying Anthropic fallback');
            $result = $this->parseWithAnthropic($text);
        }

        if ($result === null) {
            return [
                'title' => $text,
                'description' => null,
                'priority' => 3,
                'due_datetime' => null,
                'project' => null,
                'estimated_duration' => null,
                'subtasks' => [],
                'dependencies' => [],
                'raw_text' => $text,
                'provider' => 'fallback',
            ];
        }

        return $result;
    }

    /**
     * Parse using OpenAI API (primary).
     */
    private function parseWithOpenAI(string $text): ?array
    {
        $apiKey = config('tasks.ai_parser.primary.api_key');

        if (!$apiKey) {
            Log::info('AiParser: OpenAI API key not configured');
            return null;
        }

        $prompt = $this->buildPrompt($text);

        try {
            $response = Http::timeout(config('tasks.ai_parser.primary.timeout', 30))
                ->withToken($apiKey)
                ->post(self::OPENAI_URL, [
                    'model' => config('tasks.ai_parser.primary.model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt['system']],
                        ['role' => 'user', 'content' => $prompt['user']],
                    ],
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                Log::error('AiParser: OpenAI request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');
            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('AiParser: OpenAI returned invalid JSON', ['content' => $content]);
                return null;
            }

            $parsed['provider'] = 'openai';
            return $this->normalizeResult($parsed);

        } catch (\Throwable $e) {
            Log::error('AiParser: OpenAI exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Parse using Anthropic API (fallback).
     */
    private function parseWithAnthropic(string $text): ?array
    {
        $apiKey = config('tasks.ai_parser.fallback.api_key');

        if (!$apiKey) {
            Log::info('AiParser: Anthropic API key not configured');
            return null;
        }

        $prompt = $this->buildPrompt($text);

        try {
            $response = Http::timeout(config('tasks.ai_parser.fallback.timeout', 30))
                ->withToken($apiKey)
                ->post(self::ANTHROPIC_URL, [
                    'model' => config('tasks.ai_parser.fallback.model', 'claude-3-haiku-4-20250901'),
                    'max_tokens' => 1024,
                    'temperature' => 0.3,
                    'system' => $prompt['system'],
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt['user']],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('AiParser: Anthropic request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('content.0.text');
            $parsed = $this->extractJsonFromText($content);

            if ($parsed === null) {
                Log::warning('AiParser: Anthropic returned no valid JSON', ['content' => $content]);
                return null;
            }

            $parsed['provider'] = 'anthropic';
            return $this->normalizeResult($parsed);

        } catch (\Throwable $e) {
            Log::error('AiParser: Anthropic exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Build system + user prompts for task parsing.
     */
    private function buildPrompt(string $text): array
    {
        $system = <<<'SYSTEM'
You are a task parser. Convert natural language task descriptions into structured JSON.
Respond with ONLY valid JSON, no markdown formatting, no explanation text.

Required fields: "title" (string), "priority" (integer 1-5).
Optional fields: "description" (string or null), "due_datetime" (ISO 8601 datetime string or null), "project" (string or null), "estimated_duration" (integer minutes or null), "subtasks" (array of strings or empty), "dependencies" (array of strings or empty).

Examples:
Input: "Finish the report by Friday afternoon, high priority"
Output: {"title": "Finish the report", "priority": 5, "due_datetime": "2026-09-19T14:00:00", "description": null, "project": null, "estimated_duration": 120, "subtasks": [], "dependencies": []}

Input: "After the meeting with Sarah, prepare the presentation"
Output: {"title": "Prepare the presentation", "priority": 3, "due_datetime": null, "description": null, "project": null, "estimated_duration": null, "subtasks": [], "dependencies": ["meeting with Sarah"]}
SYSTEM;

        return [
            'system' => $system,
            'user' => $text,
        ];
    }

    /**
     * Normalize parsed result to standard schema.
     */
    private function normalizeResult(array $parsed): array
    {
        return [
            'title' => (string) ($parsed['title'] ?? 'Untitled Task'),
            'description' => $parsed['description'] ?? null,
            'priority' => max(1, min(5, (int) ($parsed['priority'] ?? 3))),
            'due_datetime' => $parsed['due_datetime'] ?? null,
            'project' => $parsed['project'] ?? null,
            'estimated_duration' => isset($parsed['estimated_duration']) ? (int) $parsed['estimated_duration'] : null,
            'subtasks' => is_array($parsed['subtasks'] ?? null) ? array_map('strval', $parsed['subtasks']) : [],
            'dependencies' => is_array($parsed['dependencies'] ?? null) ? array_map('strval', $parsed['dependencies']) : [],
            'raw_text' => $parsed['raw_text'] ?? '',
            'provider' => $parsed['provider'] ?? 'unknown',
        ];
    }

    /**
     * Extract JSON object from text that may have markdown wrapping.
     */
    private function extractJsonFromText(string $text): ?array
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```[a-zA-Z]*\n/', '', $text);
            $text = preg_replace('/\n```$/', '', $text);
        }

        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}');

        if ($jsonStart === false || $jsonEnd === false || $jsonEnd < $jsonStart) {
            return null;
        }

        $json = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);
        $parsed = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE ? $parsed : null;
    }
}
