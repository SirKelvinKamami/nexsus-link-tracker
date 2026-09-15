@extends('layouts.sidebar')

@section('content')
<div class="conatiner-fluid content-inner mt-n5 py-0">
    <div class="row">

        {{-- Summary cards --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Today</h5>
                    <h3 class="text-primary mb-0">{{ number_format($todayClicks) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Last 7 Days</h5>
                    <h3 class="text-primary mb-0">{{ number_format($weekClicks) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Last 30 Days</h5>
                    <h3 class="text-primary mb-0">{{ number_format($monthClicks) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">All-Time Clicks</h5>
                    <h3 class="text-primary mb-0">{{ number_format($totalClicks) }}</h3>
                </div>
            </div>
        </div>

        {{-- Page views --}}
        <div class="col-sm-6 col-lg-3 mt-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Page Views (Today)</h5>
                    <h3 class="text-primary mb-0">{{ number_format($pageViews['day']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mt-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Page Views (Week)</h5>
                    <h3 class="text-primary mb-0">{{ number_format($pageViews['week']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mt-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Page Views (Month)</h5>
                    <h3 class="text-primary mb-0">{{ number_format($pageViews['month']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mt-3">
            <div class="card rounded">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Page Views (All)</h5>
                    <h3 class="text-primary mb-0">{{ number_format($pageViews['all']) }}</h3>
                </div>
            </div>
        </div>

        {{-- Clicks over time (30 day chart) --}}
        <div class="col-lg-12 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-bar-chart-fill text-primary"></i> Clicks &mdash; Last 30 Days</h5>
                    <div class="d-flex align-items-end" style="height:160px; gap:3px;">
                        @php $max = max(1, $dailyMax); @endphp
                        @foreach($daily as $day => $count)
                            @php
                                $h = max(2, ($count / $max) * 140);
                                $isToday = $day === now()->toDateString();
                            @endphp
                            <div class="flex-fill d-flex flex-column align-items-center" title="{{ $day }}: {{ $count }}">
                                <div style="width:100%;min-width:8px;background:{{ $isToday ? '#0d6efd' : '#6ea8fe' }};border-radius:2px;height:{{ $h }}px;" class="mt-auto"></div>
                                @if($loop->iteration % 5 === 1 || $loop->last)
                                    <small class="text-muted mt-1" style="font-size:9px;white-space:nowrap;">{{ substr($day, 5) }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Links --}}
        <div class="col-lg-12 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-link-45deg text-primary"></i> Top Links</h5>
                    @if($topLinks->isEmpty())
                        <p class="text-muted">No clicks recorded yet.</p>
                    @else
                        <div class="bd-example">
                            <ol class="list-group list-group-numbered">
                                @foreach($topLinks as $link)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto text-truncate">
                                            <div class="fw-bold text-truncate">{{ $link->title ?? $link->link }}</div>
                                            <small class="text-muted">{{ $link->link }}</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill p-2">{{ number_format($link->clicks ?? 0) }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Breakdowns row --}}
        <div class="col-lg-4 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h6 class="mb-3">Devices</h6>
                    @forelse($devices as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-capitalize">{{ $row->device_type ?: '—' }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h6 class="mb-3">Browsers</h6>
                    @forelse($browsers as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $row->browser ?: '—' }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h6 class="mb-3">Operating Systems</h6>
                    @forelse($oses as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $row->os ?: '—' }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Referrers & UTM --}}
        <div class="col-lg-6 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-arrow-left-circle text-primary"></i> Top Referrers</h6>
                    @forelse($topReferrers as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-truncate" style="max-width:80%;">{{ Str::limit($row->referrer, 60) }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No referrers yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-tags text-primary"></i> UTM Sources</h6>
                    @forelse($topSources as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $row->utm_source }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No UTM sources yet.</p>
                    @endforelse

                    <hr>
                    <h6 class="mb-3">UTM Mediums</h6>
                    @forelse($topMediums as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $row->utm_medium }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No UTM mediums yet.</p>
                    @endforelse

                    <hr>
                    <h6 class="mb-3">UTM Campaigns</h6>
                    @forelse($topCampaigns as $row)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $row->utm_campaign }}</span>
                            <span class="text-primary">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No UTM campaigns yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Clicks --}}
        <div class="col-lg-12 mt-4">
            <div class="card rounded">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-clock-history text-primary"></i> Recent Clicks</h6>
                    @if($recent->isEmpty())
                        <p class="text-muted">No clicks yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Link</th>
                                        <th>Browser</th>
                                        <th>OS</th>
                                        <th>Device</th>
                                        <th>Referrer</th>
                                        <th>UTM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent as $click)
                                        <tr>
                                            <td class="text-nowrap">{{ $click->created_at->diffForHumans() }}</td>
                                            <td class="text-truncate" style="max-width:180px;" title="{{ $click->link->title ?? $click->link_id }}">
                                                {{ $click->link->title ?? $click->link_id }}
                                            </td>
                                            <td>{{ $click->browser }} {{ $click->browser_version }}</td>
                                            <td>{{ $click->os }}</td>
                                            <td class="text-capitalize">{{ $click->device_type }}</td>
                                            <td class="text-truncate" style="max-width:140px;" title="{{ $click->referrer }}">
                                                {{ $click->referrer ? Str::limit($click->referrer, 40) : '—' }}
                                            </td>
                                            <td class="text-truncate" style="max-width:120px;">
                                                @if($click->utm_source || $click->utm_medium || $click->utm_campaign)
                                                    {{ $click->utm_source ?: '—' }} / {{ $click->utm_medium ?: '—' }} / {{ $click->utm_campaign ?: '—' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection