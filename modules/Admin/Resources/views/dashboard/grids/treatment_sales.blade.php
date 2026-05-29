<div class="col-lg-3 col-md-6 col-sm-6">
    <div class="single-grid total-treatment-sales dashboard-stat-card dashboard-stat-card--violet">
        <div>
            <span class="count" title="{{ $treatmentSales->format() }}">{{ $treatmentSales->KMBTFormat() }}</span>
            <span class="title">{{ trans('admin::dashboard.treatment_sales') }}</span>
        </div>
        <div class="single-grid-icon">
            <i class="fa fa-heart"></i>
        </div>
    </div>
</div>
