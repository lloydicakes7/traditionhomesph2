@props(['members', 'columns' => 'col-12 col-sm-6 col-lg-3', 'search' => null])

<div class="row justify-content-center">
    @forelse ($members as $member)
        <div class="{{ $columns }} d-flex mb-4">
            <div class="card h-100 w-100 mb-0 text-center">
                <div class="card-body d-flex flex-column">
                    <img src="{{ $member->photo_url ?: asset('image/user.png') }}"
                        alt="{{ $member->name }}" class="rounded-circle mx-auto mb-3"
                        style="width: 96px; height: 96px; object-fit: cover;">

                    <h3 class="h6 font-weight-bold mb-0">{{ $member->name }}</h3>
                    <p class="text-muted small mb-2">{{ $member->position }}</p>

                    @if ($member->phone)
                        <a href="tel:{{ $member->dialable_phone }}" class="small d-block">
                            <i class="fas fa-fw fa-phone-alt mr-1"></i>{{ $member->phone }}
                        </a>
                    @endif

                    @if ($member->email)
                        <a href="mailto:{{ $member->email }}" class="small d-block text-truncate">
                            <i class="fas fa-fw fa-envelope mr-1"></i>{{ $member->email }}
                        </a>
                    @endif

                    @if ($member->note)
                        <p class="small text-muted mt-2 mb-0">{{ $member->note }}</p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-fw fa-user-friends fa-2x mb-2"></i>
                    @if (filled($search))
                        <p class="mb-0">No members match your search.</p>
                    @else
                        <p class="mb-0">Member information is not available yet.</p>
                    @endif
                </div>
            </div>
        </div>
    @endforelse
</div>
