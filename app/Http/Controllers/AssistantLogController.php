<?php

namespace App\Http\Controllers;

use App\Models\AssistantLog;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AssistantLogController extends Controller
{
	/**
	 * Display a listing of assistant logs.
	 */
	public function index(Request $request)
	{
		$perPage = (int) $request->integer('per_page', 10);
		$allowed = [10, 25, 50, 100];
		if (! in_array($perPage, $allowed, true)) {
			$perPage = 10;
		}

		$logs = AssistantLog::query()
			->with(['tenant:id,name', 'assistant:id,name'])
			->latest('id')
			->paginate($perPage)
			->withQueryString()
			->through(function (AssistantLog $log) {
				return [
					'id' => $log->id,
					'created_at' => $log->created_at,
					'comment' => $log->comment,
					'tenant' => $log->tenant ? [
						'id' => $log->tenant->id,
						'name' => $log->tenant->name,
					] : null,
					'assistant' => $log->assistant ? [
						'id' => $log->assistant->id,
						'name' => $log->assistant->name,
					] : null,
					'session_id' => $log->session_id,
					'query' => $log->query,
					'response' => $log->response,
					'rating' => $log->rating,
				];
			});

		return Inertia::render('AssistantLogs/Index', [
			'logs' => $logs,
		]);
	}
}
