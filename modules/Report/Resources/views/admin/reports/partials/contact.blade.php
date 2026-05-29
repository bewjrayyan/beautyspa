@php
    $phone = $phone ?? ($row->customer_phone ?? null);
    $email = $email ?? ($row->customer_email ?? null);
@endphp

<td class="report-cell--contact">
    @if ($phone)
        <div class="report-cell__line">{{ $phone }}</div>
    @endif
    @if ($email)
        <div class="report-cell__line">{{ $email }}</div>
    @endif
    @if (! $phone && ! $email)
        <div class="report-cell__line">—</div>
    @endif
</td>
