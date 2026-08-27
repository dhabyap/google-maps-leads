<?php

namespace App\Http\Controllers;

use App\Models\GoogleMapClient;
use Illuminate\Http\Request;

class RadarController extends Controller
{
    /**
     * GET / — dashboard "Radar Klien" (template preview.html → Blade).
     */
    public function index()
    {
        $clients = GoogleMapClient::orderByDesc('id')->get();

        $counts = [
            'contacted' => GoogleMapClient::where('status', 'contacted')->count(),
            'deal'      => GoogleMapClient::where('status', 'deal')->count(),
            'rejected'  => GoogleMapClient::where('status', 'rejected')->count(),
        ];

        $categories = GoogleMapClient::whereNotNull('category')
            ->distinct()->orderBy('category')->pluck('category');

        return view('radar', [
            'clients'    => $clients,
            'total'      => $clients->count(),
            'counts'     => $counts,
            'categories' => $categories,
        ]);
    }

    /**
     * POST /leads/{id}/status — update status (dipakai dropdown di dashboard).
     */
    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');
        if (!in_array($status, ['new', 'contacted', 'deal', 'rejected'], true)) {
            return response()->json(['error' => 'Invalid status value'], 422);
        }

        $client = GoogleMapClient::findOrFail($id);
        $client->status = $status;
        $client->save();

        return response()->json(['ok' => true]);
    }

    public function updateNotes(Request $request, $id)
    {
        $notes = $request->input('notes');
        $client = GoogleMapClient::findOrFail($id);
        $client->notes = $notes;
        $client->save();

        return response()->json(['ok' => true]);
    }
}
