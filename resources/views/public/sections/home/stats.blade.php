@php
    $figures = [
        [
            'value'   => $stats['homeowners'],
            'label'   => 'Homeowners',
            'caption' => 'Households holding a portal account',
        ],
        [
            'value'   => $stats['residents'],
            'label'   => 'Residents',
            'caption' => 'People living in the subdivision',
        ],
        [
            'value'   => $stats['properties'],
            'label'   => 'Properties',
            'caption' => 'Homes and lots on record',
        ],
        [
            'value'   => $stats['officers'],
            'label'   => 'Officers & Staff',
            'caption' => 'Serving the association',
        ],
    ];
@endphp

<section id="association-numbers">
    <div class="row no-gutters">
        @foreach ($figures as $figure)
            <div class="col-6 col-lg-3 d-flex">
                <div class="card w-100 h-100 mb-0 rounded-0 shadow-none border-0 text-center bg-secondary">
                    <div class="card-body d-flex flex-column justify-content-center py-4">
                        <div class="font-serif font-weight-bold" style="font-size: 2rem; line-height: 1;">
                            {{ number_format($figure['value']) }}
                        </div>

                        <p class="font-weight-bold mb-1 mt-2">{{ $figure['label'] }}</p>
                        <p class="small mb-0">{{ $figure['caption'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
