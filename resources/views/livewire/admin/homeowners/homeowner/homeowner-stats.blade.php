<div>
    {{-- =================================================================== --}}
    {{-- HOMEOWNER PORTAL ACCESS + STATS DONUT — side by side                --}}
    {{-- Isolated component: refreshes on `homeowner-saved` only.            --}}
    {{-- =================================================================== --}}
    <div class="row mb-3">
        <div class="col-md-7 col-lg-8 mb-3 mb-md-0">
            <div class="card bg-success h-100">
                <div class="card-body py-4">
                    <div class="d-flex flex-column" style="gap:0.75rem;">
                        <div class="d-inline-flex align-items-center" style="gap:0.6rem;">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-success flex-shrink-0"
                                style="width:44px; height:44px; font-size:1.15rem;">
                                <i class="fas fa-fw fa-external-link-alt"></i>
                            </div>
                            <h5 class="font-weight-bold text-white mb-0">Homeowner Portal Access</h5>
                        </div>
                        <p class="text-white mb-0 small" style="opacity:0.9; max-width:480px; line-height:1.5;">
                            Share this link with homeowners so they can log in to their portal to view their
                            account, pay dues, submit requests, and more.
                        </p>
                        <div class="input-group input-group-sm" style="max-width:400px;">
                            <input type="text" class="form-control" value="{{ route('login') }}"
                                id="portalUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-light" type="button" onclick="copyPortalUrl()"
                                    title="Copy to clipboard">
                                    <i class="fas fa-fw fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 col-lg-4">
            <div class="card bg-white border h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <div class="position-relative d-inline-flex" style="width:110px; height:110px;">
                        <div class="rounded-circle w-100 h-100"
                            style="background: {{ ($stats['donutStyle'] ?? '') }};"></div>
                        <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center flex-column"
                            style="width:76px; height:76px; top:50%; left:50%; transform:translate(-50%,-50%);">
                            <span class="font-weight-bold" style="font-size:1.3rem; line-height:1;">{{ ($stats['total'] ?? 0) }}</span>
                            <small class="text-muted" style="font-size:0.65rem; line-height:1;">Total</small>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center mt-3" style="gap:0.75rem 1.2rem;">
                        <div class="d-flex align-items-center" style="gap:0.35rem;">
                            <span class="d-inline-block rounded-circle" style="width:10px; height:10px; background:#28a745;"></span>
                            <span class="small"><strong>{{ ($stats['active'] ?? 0) }}</strong> Active</span>
                        </div>
                        <div class="d-flex align-items-center" style="gap:0.35rem;">
                            <span class="d-inline-block rounded-circle" style="width:10px; height:10px; background:#ffc107;"></span>
                            <span class="small"><strong>{{ ($stats['disabled'] ?? 0) }}</strong> Disabled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function copyPortalUrl() {
        var input = document.getElementById('portalUrl');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
    }
</script>
@endpush
