{{--
    The officers and Board of Directors, down the left edge of the paper
    forms. Read from config/thha.php through the letterhead — a document never
    names an officer of its own.

    @var array $letterhead  (officers, board: each [name, position])
--}}
@php
    $officers = $letterhead['officers'] ?? [];
    $board = $letterhead['board'] ?? [];
@endphp

<div class="officers">
    @foreach ($officers as $officer)
        <div class="officer">
            <div class="officer-name">{{ $officer['name'] }}</div>
            <div class="officer-pos">{{ $officer['position'] }}</div>
        </div>
    @endforeach

    @if (! empty($board))
        <div class="board-label">Board of Directors:</div>
        @foreach ($board as $member)
            <div class="officer">
                <div class="officer-name">{{ $member['name'] }}</div>
                <div class="officer-pos">{{ $member['position'] }}</div>
            </div>
        @endforeach
    @endif
</div>
