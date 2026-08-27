<?php

namespace App\Http\Controllers;

use App\Models\GoogleMapClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Support\Str;

class RadarController extends Controller
{
    /**
     * GET / — dashboard "Radar Klien" (template preview.html → Blade).
     * Diproteksi middleware dashboard.auth.
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $query = GoogleMapClient::query();

        if ($request->filled('keyword')) {
            $kw = $request->input('keyword');
            $query->where(function ($w) use ($kw) {
                $w->where('business_name', 'like', "%{$kw}%")
                  ->orWhere('category', 'like', "%{$kw}%")
                  ->orWhere('phone_number', 'like', "%{$kw}%")
                  ->orWhere('address', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->input('no_website') === '1') {
            $query->where(function ($w) {
                $w->whereNull('website_url')->orWhere('website_url', '');
            });
        }

        if ($request->input('sort') === 'prospect') {
            $query->orderByDesc(
                GoogleMapClient::raw('(rating * review_count)')
            );
        } else {
            $query->orderByDesc('id');
        }

        $clients = $query->paginate($perPage)->withQueryString();

        $counts = [
            'contacted' => GoogleMapClient::where('status', 'contacted')->count(),
            'deal'      => GoogleMapClient::where('status', 'deal')->count(),
            'rejected'  => GoogleMapClient::where('status', 'rejected')->count(),
            'no_website'=> GoogleMapClient::where(function ($w) {
                $w->whereNull('website_url')->orWhere('website_url', '');
            })->count(),
        ];

        $categories = GoogleMapClient::whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()->orderBy('category')->pluck('category');

        return view('radar', [
            'clients'    => $clients,
            'total'      => GoogleMapClient::count(),
            'counts'     => $counts,
            'categories' => $categories,
            'filters'    => $request->only(['keyword', 'status', 'category', 'no_website', 'sort']),
        ]);
    }

    /**
     * GET /export — download CSV semua lead (atau hasil filter saat ini).
     */
    public function export(Request $request)
    {
        $query = GoogleMapClient::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->input('no_website') === '1') {
            $query->where(function ($w) {
                $w->whereNull('website_url')->orWhere('website_url', '');
            });
        }

        $clients = $query->orderByDesc('id')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="leads_' . now()->format('Ymd_His') . '.csv"',
            'Cache-Control'       => 'no-store',
        ];

        $callback = function () use ($clients) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM biar Excel baca UTF-8
            fputcsv($out, [
                'business_name', 'category', 'phone_number', 'website_url',
                'address', 'rating', 'review_count', 'status', 'notes',
            ]);
            foreach ($clients as $c) {
                fputcsv($out, [
                    $c->business_name,
                    $c->category,
                    $c->phone_number,
                    $c->website_url,
                    $c->address,
                    $c->rating,
                    $c->review_count,
                    $c->status,
                    $c->notes,
                ]);
            }
            fclose($out);
        };

        return FacadeResponse::stream($callback, 200, $headers);
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

        if ($status === 'contacted') {
            $client->last_contacted_at = $client->last_contacted_at ?? now();
        }

        $client->save();

        return response()->json([
            'ok'               => true,
            'last_contacted_at' => $client->last_contacted_at?->format('Y-m-d H:i'),
        ]);
    }

    public function updateNotes(Request $request, $id)
    {
        $notes = $request->input('notes');
        $client = GoogleMapClient::findOrFail($id);
        $client->notes = $notes;
        $client->save();

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /leads/{id} — hapus lead (mis. salah scrape).
     */
    public function destroy($id)
    {
        $client = GoogleMapClient::findOrFail($id);
        $client->delete();

        return response()->json(['ok' => true]);
    }
}
