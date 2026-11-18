{{--
    The frame the framed letters print inside: the bordered sheet, the officer
    column at the left with the portrait over it, and the control and O.R.
    numbers at the foot of that column. The Certification and the Invitation
    Letter are the same sheet at this level, so both read it from here.

    @var array  $staff      officers, the board and the presidential portrait
    @var string $control_no the control number the office wrote on the paper
    @var string $or_no      the O.R. number the office wrote on the paper
--}}

<div class="thha-fl-frame"></div>
<div class="thha-fl-divider"></div>

<div class="thha-fl-photo">
    <x-doc.logo :src="$staff['photo'] ?? null" :w="32.47" :h="31.94" />
</div>

<div class="thha-fl-side">
    @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
        <div class="thha-fl-officer">
            <div class="thha-fl-name">{{ $staff[$office]['name'] ?? '' }}&#160;</div>
            <div class="thha-fl-role">{{ $staff[$office]['role'] ?? '' }}</div>
        </div>
    @endforeach

    <div class="thha-fl-board">{{ __('documents.sidebar.board') }}</div>

    @foreach (($staff['board'] ?? []) as $committee)
        <div class="thha-fl-officer">
            <div class="thha-fl-name">{{ $committee['name'] }}</div>
            <div class="thha-fl-role">{{ $committee['role'] }}</div>
        </div>
    @endforeach
</div>

<div class="thha-fl-control">
    <div>Control No. <span class="thha-fl-under thha-fl-under--control">{{ $control_no }}</span></div>
    <div>O.R. No. <span class="thha-fl-under thha-fl-under--or">{{ $or_no }}</span></div>
</div>
