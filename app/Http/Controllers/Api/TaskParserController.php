<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TaskParseRequest;
use App\Services\AiParser;

class TaskParserController extends BaseController
{
    /**
     * Parse natural language into structured task data.
     */
    public function parse(TaskParseRequest $request, AiParser $parser)
    {
        try {
            $text = $request->input('text');
            $result = $parser->parse($text);

            return $this->success($result);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Parse failed', $e->getMessage(), 500);
        }
    }
}
