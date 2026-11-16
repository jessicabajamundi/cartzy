<?php

namespace App\Services;

use App\Models\{Rider, Shipment};
use Carbon\Carbon;

class LogisticsReports
{
    public function data(int $hubId, string $from, string $to): array
    {
        $start = Carbon::parse($from, 'Asia/Manila')->startOfDay()->utc();
        $end = Carbon::parse($to, 'Asia/Manila')->endOfDay()->utc();
        $shipments = Shipment::where('logistics_provider_id', $hubId)
            ->where(fn ($q) => $q->whereBetween('created_at', [$start, $end])
                ->orWhereBetween('updated_at', [$start, $end])
                ->orWhereHas('sellerOrder', fn ($s) => $s->whereBetween('delivered_at', [$start, $end])))
            ->with('sellerOrder')->get();
        $handled = $shipments->filter(fn ($s) => $s->created_at->betweenIncluded($start, $end));
        $delivered = $shipments->filter(fn ($s) => $s->status === 'delivered'
            && $s->sellerOrder?->delivered_at?->betweenIncluded($start, $end));
        $failed = $shipments->filter(fn ($s) => $s->status === 'failed' && $s->updated_at->betweenIncluded($start, $end));
        $returned = $shipments->filter(fn ($s) => $s->status === 'returned' && $s->returned_at?->betweenIncluded($start, $end));
        $durations = $delivered->filter(fn ($s) => $s->received_at && $s->received_at->lte($s->sellerOrder->delivered_at))
            ->map(fn ($s) => $s->received_at->diffInSeconds($s->sellerOrder->delivered_at) / 86400);
        $chart = ['labels' => [], 'received' => [], 'delivered' => []];
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDays(7)) {
            $until = $cursor->copy()->addDays(7)->subMicrosecond()->min($end);
            $chart['labels'][] = $cursor->copy()->timezone('Asia/Manila')->format('M j').' – '.$until->copy()->timezone('Asia/Manila')->format('M j');
            $chart['received'][] = $handled->filter(fn ($s) => $s->created_at->betweenIncluded($cursor, $until))->count();
            $chart['delivered'][] = $delivered->filter(fn ($s) => $s->sellerOrder->delivered_at->betweenIncluded($cursor, $until))->count();
        }
        $riders = Rider::where('logistics_provider_id', $hubId)->where('status', 'approved')->with('user')->get();
        $top = $riders->map(function ($rider) use ($delivered, $failed) {
            $done = $delivered->where('rider_id', $rider->id)->count();
            $attempts = $done + $failed->where('rider_id', $rider->id)->count();
            return ['name' => $rider->user?->name ?? 'Former rider', 'deliveries' => $done,
                'success_rate' => $attempts ? round($done / $attempts * 100, 1) : 0];
        })->sortByDesc('deliveries')->take(5)->values()->all();
        return ['total_parcels_handled' => $handled->count(), 'delivered_on_time' => $delivered->count(),
            'failed_deliveries' => $failed->count(), 'returned_to_hub' => $returned->count(),
            'success_rate' => $delivered->count() + $failed->count() ? round($delivered->count() / ($delivered->count() + $failed->count()) * 100, 1) : 0,
            'avg_delivery_days' => $durations->isEmpty() ? null : round($durations->avg(), 2), 'top_riders' => $top,
            'area_breakdown' => $handled->whereNotNull('delivery_area')->groupBy('delivery_area')
                ->map(fn ($items, $area) => ['area' => $area, 'parcels' => $items->count()])->values()->all(),
            'chart' => $chart];
    }
}
