@extends('layouts.app')

@section('title', 'Admin Antrian')

@push('styles')
<style>
    .queue-shell {
        background: linear-gradient(180deg, rgba(246, 248, 255, 0.9), rgba(255, 255, 255, 0.98));
        border-radius: 28px;
        padding: 24px;
        box-shadow: 0 24px 60px rgba(34, 47, 62, 0.10);
    }

    .queue-stat-card {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        min-height: 130px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .queue-stat-card .value {
        font-size: 34px;
        font-weight: 800;
        margin-top: 8px;
    }

    .queue-panel {
        background: #fff;
        border-radius: 24px;
        padding: 22px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    }

    .queue-summary {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #fff;
        border-radius: 24px;
        padding: 24px;
    }

    .queue-summary .big-number {
        font-size: clamp(44px, 8vw, 92px);
        font-weight: 900;
        line-height: 1;
    }

    .queue-summary .name {
        font-size: 24px;
        font-weight: 700;
        margin-top: 10px;
    }

    .action-btn {
        width: 100%;
        min-height: 48px;
        border-radius: 14px;
        font-weight: 700;
    }

    .table thead th {
        background: #f8fafc;
        border-bottom: 0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-info text-white me-2">
                    <i class="mdi mdi-queue-first-in-last-out"></i>
                </span>
                Dashboard Antrian Realtime
            </h3>
        </div>

        <div class="queue-shell mb-4">
            <div class="row g-3">
            <div class="col-md-4 col-lg-2">
                <div class="card bg-gradient-primary queue-stat-card text-white">
                    <div class="card-body">
                    <div>Total</div>
                    <div class="value" id="total">{{ $snapshot['stats']['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card bg-gradient-warning queue-stat-card text-dark">
                    <div class="card-body">
                    <div>Menunggu</div>
                    <div class="value" id="menunggu">{{ $snapshot['stats']['menunggu'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card bg-gradient-info queue-stat-card text-white">
                    <div class="card-body">
                    <div>Dipanggil</div>
                    <div class="value" id="dipanggil">{{ $snapshot['stats']['dipanggil'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card bg-gradient-success queue-stat-card text-white">
                    <div class="card-body">
                    <div>Selesai</div>
                    <div class="value" id="selesai">{{ $snapshot['stats']['selesai'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card bg-gradient-danger queue-stat-card text-white">
                    <div class="card-body">
                    <div>Terlambat</div>
                    <div class="value" id="terlambat">{{ $snapshot['stats']['terlambat'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card bg-gradient-dark queue-stat-card text-white">
                    <div class="card-body">
                    <div>Nomor Aktif</div>
                    <div class="value" id="currentNumberMetric">{{ $snapshot['current']['nomor_display'] ?? '--' }}</div>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="queue-panel h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-1">Nomor Sedang Dipanggil</h4>
                            <div class="text-muted">Realtime dari SSE tanpa refresh</div>
                        </div>
                        <span id="currentStatus" class="badge rounded-pill {{ $snapshot['current'] ? match($snapshot['current']['status']) {
                            'dipanggil' => 'bg-primary',
                            'selesai' => 'bg-success',
                            'terlambat' => 'bg-danger',
                            default => 'bg-warning text-dark',
                        } : 'bg-warning text-dark' }}">
                            {{ $snapshot['current']['status_label'] ?? 'Menunggu' }}
                        </span>
                    </div>

                    <div class="queue-summary">
                        <div class="text-uppercase small opacity-75">Nomor</div>
                        <div id="currentNumber" class="big-number">{{ $snapshot['current']['nomor_display'] ?? '--' }}</div>
                        <div class="mt-3 text-uppercase small opacity-75">Nama</div>
                        <div id="currentName" class="name">{{ $snapshot['current']['nama'] ?? 'Belum ada panggilan' }}</div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6 col-xl-3">
                            <button class="btn btn-primary action-btn" data-antrian-action data-action="next" data-url="{{ route('antrian.panggil') }}">Panggil Berikutnya</button>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <button class="btn btn-warning action-btn" data-antrian-action data-action="late" data-url="{{ route('antrian.terlambat') }}">Tandai Terlambat</button>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <button class="btn btn-info action-btn text-white" data-antrian-action data-action="repeat" data-url="{{ route('antrian.ulang') }}">Panggil Ulang</button>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <button class="btn btn-success action-btn" data-antrian-action data-action="done" data-url="{{ route('antrian.selesai') }}">Selesaikan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="queue-panel h-100">
                    <h5 class="mb-3">Nomor Berikutnya</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="display-4 fw-bold mb-0" id="nextNumber">{{ $snapshot['next']['nomor_display'] ?? '--' }}</div>
                        <div>
                            <div class="text-muted">Nama</div>
                            <div class="fw-semibold" id="nextName">{{ $snapshot['next']['nama'] ?? 'Belum ada antrian' }}</div>
                        </div>
                    </div>
                    <hr>
                    <div class="small text-muted">Status warna</div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-warning text-dark">Menunggu</span>
                        <span class="badge bg-primary">Dipanggil</span>
                        <span class="badge bg-success">Selesai</span>
                        <span class="badge bg-danger">Terlambat</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="queue-panel">
                    <h5 class="mb-3">Daftar Menunggu</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Antrian</th>
                                    <th>Nama</th>
                                    <th>Daftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="waitingTable">
                                @foreach($snapshot['queues']['menunggu'] as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $item['nomor_display'] }}</strong></td>
                                        <td>{{ $item['nama'] }}</td>
                                        <td>{{ $item['created_at'] }}</td>
                                        <td><span class="badge bg-warning text-dark">Menunggu</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="queue-panel mb-4">
                    <h5 class="mb-3">Daftar Dipanggil</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Antrian</th>
                                    <th>Nama</th>
                                    <th>Daftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="calledTable">
                                @foreach($snapshot['queues']['dipanggil'] as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $item['nomor_display'] }}</strong></td>
                                        <td>{{ $item['nama'] }}</td>
                                        <td>{{ $item['created_at'] }}</td>
                                        <td><span class="badge bg-primary">Dipanggil</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="queue-panel mb-4">
                    <h5 class="mb-3">Daftar Selesai</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Antrian</th>
                                    <th>Nama</th>
                                    <th>Selesai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="doneTable">
                                @foreach($snapshot['queues']['selesai'] as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $item['nomor_display'] }}</strong></td>
                                        <td>{{ $item['nama'] }}</td>
                                        <td>{{ $item['selesai_pada'] }}</td>
                                        <td><span class="badge bg-success">Selesai</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="queue-panel">
                    <h5 class="mb-3">Daftar Terlambat</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Antrian</th>
                                    <th>Nama</th>
                                    <th>Dipanggil</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="lateTable">
                                @foreach($snapshot['queues']['terlambat'] as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $item['nomor_display'] }}</strong></td>
                                        <td>{{ $item['nama'] }}</td>
                                        <td>{{ $item['dipanggil_pada'] }}</td>
                                        <td><span class="badge bg-danger">Terlambat</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/antrian.js') }}"></script>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        initAntrianRealtime({
            mode: 'admin',
            eventSourceUrl: '{{ url('/sse/antrian') }}',
            audioUrl: '{{ asset('assets/sound/dragon-studio-censor-beep-3-372460.mp3') }}',
            audioGenerationUrl: '{{ url('/antrian/audio') }}',
            csrfToken: '{{ csrf_token() }}',
            selectors: {
                total: '#total',
                menunggu: '#menunggu',
                dipanggil: '#dipanggil',
                selesai: '#selesai',
                terlambat: '#terlambat',
                currentNumber: '#currentNumber',
                currentName: '#currentName',
                currentStatus: '#currentStatus',
                nextNumber: '#nextNumber',
                nextName: '#nextName',
                waitingTable: '#waitingTable',
                calledTable: '#calledTable',
                doneTable: '#doneTable',
                lateTable: '#lateTable',
            }
        });
    });
</script>
@endpush