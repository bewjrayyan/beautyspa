@php
    use Modules\Report\Support\ReportFormatters;

    $row = $row ?? null;
@endphp

<td class="report-cell--spa-branch">
    {{ ReportFormatters::spaBranchName($row) }}
</td>
