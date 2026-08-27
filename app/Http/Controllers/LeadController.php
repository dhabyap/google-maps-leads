<?php

namespace App\Http\Controllers;

use App\Models\GoogleMapClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * POST /api/leads/upsert
     * Accepts single lead object or array of leads (batch).
     * Upsert based on google_place_id.
     */
    public function upsert(Request $request)
    {
        $payload = $request->json()->all();

        // Accept both single object and batch array
        $items = isset($payload['leads']) ? $payload['leads'] : (isset($payload[0]) ? $payload : [$payload]);

        if (empty($items)) {
            return response()->json(['error' => 'No leads provided'], 422);
        }

        $inserted = 0;
        $updated = 0;
        $errors = [];

        foreach ($items as $i => $lead) {
            $validator = Validator::make($lead, [
                'google_place_id' => 'required|string|max:255',
                'business_name'   => 'required|string|max:255',
                'category'        => 'nullable|string|max:255',
                'phone_number'    => 'nullable|string|max:50',
                'website_url'     => 'nullable|string|max:500',
                'address'         => 'nullable|string',
                'latitude'        => 'nullable|numeric|between:-90,90',
                'longitude'       => 'nullable|numeric|between:-180,180',
                'rating'          => 'nullable|numeric|between:0,5',
                'review_count'    => 'nullable|integer|min:0',
                'search_keyword'  => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $errors[] = ['index' => $i, 'errors' => $validator->errors()->toArray()];
                continue;
            }
            $data = $validator->validated();

            $existing = GoogleMapClient::where('google_place_id', $data['google_place_id'])->first();

            if ($existing) {
                // Update data fields only — never reset manual 'status'/'notes'.
                // JANGAN timpa rating/review jadi 0 kalau scraper gak kirim nilai.
                $update = [
                    'business_name' => $data['business_name'],
                    'category'      => $data['category'] ?? $existing->category,
                    'phone_number'  => $data['phone_number'] ?? $existing->phone_number,
                    'website_url'   => $data['website_url'] ?? $existing->website_url,
                    'address'       => $data['address'] ?? $existing->address,
                    'latitude'      => $data['latitude'] ?? $existing->latitude,
                    'longitude'     => $data['longitude'] ?? $existing->longitude,
                    'search_keyword'=> $data['search_keyword'],
                ];
                if (isset($data['rating'])) {
                    $update['rating'] = $data['rating'];
                }
                if (isset($data['review_count'])) {
                    $update['review_count'] = $data['review_count'];
                }
                $existing->update($update);
                $updated++;
            } else {
                // Hanya set default kalau field memang gak dikirim.
                $data['rating'] = $data['rating'] ?? 0.0;
                $data['review_count'] = $data['review_count'] ?? 0;
                GoogleMapClient::create($data);
                $inserted++;
            }
        }

        $status = 200;
        if (!empty($errors)) {
            $status = 422;
        }

        return response()->json([
            'inserted' => $inserted,
            'updated'  => $updated,
            'errors'   => $errors,
        ], $status);
    }

    /**
     * GET /api/leads — list with filters (for debugging / integration).
     */
    public function index(Request $request)
    {
        $query = GoogleMapClient::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('keyword')) {
            $query->where('search_keyword', 'like', '%' . $request->input('keyword') . '%');
        }
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->input('no_website') === '1') {
            $query->where(function ($w) {
                $w->whereNull('website_url')->orWhere('website_url', '');
            });
        }
        if ($request->has('q')) {
            $q = $request->input('q');
            $query->where(function ($w) use ($q) {
                $w->where('business_name', 'like', "%{$q}%")
                  ->orWhere('phone_number', 'like', "%{$q}%")
                  ->orWhere('address', 'like', "%{$q}%");
            });
        }

        if ($request->input('sort') === 'prospect') {
            $query->orderByDesc(GoogleMapClient::raw('(rating * review_count)'));
        } else {
            $query->orderByDesc('id');
        }

        return response()->json($query->paginate($request->input('per_page', 50)));
    }
}
