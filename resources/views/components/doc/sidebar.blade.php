@props([
    'staff'     => [],
    'controlNo' => '',
    'orNo'      => '',
    'hasOr'     => true,
    'hasControl' => true,
    'controlLabel' => 'Control No.',
])

<div class="doc-sidebar">
    <div class="doc-sidebar__photo">
        <x-doc.logo :src="$staff['photo'] ?? null" :w="33" :h="27" />
    </div>

    @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
        <div class="doc-sidebar__entry">
            <div class="doc-sidebar__name">{{ $staff[$office]['name'] ?? '' }}&#160;</div>
            <div class="doc-sidebar__role">{{ $staff[$office]['role'] ?? '' }}</div>
        </div>
    @endforeach

    <div class="doc-sidebar__heading">Board of Directors:</div>

    @foreach (($staff['committees'] ?? []) as $committee)
        <div class="doc-sidebar__entry">
            <div class="doc-sidebar__name">{{ $committee['name'] }}</div>
            <div class="doc-sidebar__role">{{ $committee['role'] }}</div>
        </div>
    @endforeach

    @if ($hasControl)
        <div class="doc-sidebar__control">
            <x-doc.row :label="$controlLabel" :labelW="18" :value="$controlNo" />
            <div class="doc-gap-1"></div>
            @if ($hasOr)
                <x-doc.row label="O.R. No." :labelW="18" :value="$orNo" />
            @endif
        </div>
    @endif
</div>
