<div class="col-lg-3 col-md-6 col-sm-6">
    <div class="single-grid today-appointments dashboard-stat-card dashboard-stat-card--rose">
        <div>
            <span class="count">{{ number_format($todayAppointments) }}</span>
            <span class="title">{{ trans('admin::dashboard.today_appointments') }}</span>
        </div>
        <div class="single-grid-icon">
            <i class="fa fa-calendar-check-o"></i>
        </div>
    </div>
</div>
