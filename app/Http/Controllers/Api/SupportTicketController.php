<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    use PaginatesApiResults;

    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::query()
            ->with(['user:id,full_name,email,role', 'resolver:id,full_name,email,role'])
            ->orderByDesc('id');

        if ($request->filled('channel')) {
            $query->where('channel', strtoupper((string) $request->input('channel')));
        }

        return response()->json(
            $this->transformPaginator(
                $query->paginate($this->perPage($request)),
                fn (SupportTicket $ticket): array => $this->ticketPayload($ticket)
            )
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string'],
            'channel' => ['required', 'string', 'in:supplier,warehouse,SUPPLIER,WAREHOUSE'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => $request->user()?->id,
            'subject' => trim((string) $validated['subject']),
            'message' => trim((string) $validated['message']),
            'channel' => strtoupper((string) $validated['channel']),
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return response()->json([
            'message' => 'Support ticket created successfully.',
            'data' => $this->ticketPayload($ticket->load(['user', 'resolver'])),
        ], 201);
    }

    public function resolve(Request $request, SupportTicket $ticket): JsonResponse
    {
        $ticket->update([
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_by_user_id' => $request->user()?->id,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Support ticket resolved successfully.',
            'data' => $this->ticketPayload($ticket->refresh()->load(['user', 'resolver'])),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketPayload(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'subject' => $ticket->subject,
            'message' => $ticket->message,
            'channel' => strtolower($ticket->channel),
            'status' => strtolower($ticket->status),
            'created_at' => optional($ticket->created_at)->toISOString(),
            'updated_at' => optional($ticket->updated_at)->toISOString(),
            'resolved_at' => optional($ticket->resolved_at)->toISOString(),
            'user' => $ticket->user,
            'resolver' => $ticket->resolver,
        ];
    }
}
