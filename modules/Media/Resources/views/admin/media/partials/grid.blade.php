<div
    id="media-grid"
    class="media-grid-wrapper"
    @if (!empty($pickerMode)) data-picker-mode="1" @endif
    @if (!empty($type)) data-type="{{ $type }}" @endif
>
    <div class="media-grid-toolbar">
        <div class="media-grid-search">
            <input
                type="text"
                class="form-control media-grid-search-input"
                placeholder="{{ trans('admin::admin.table.search_here') }}"
            >
        </div>

        <div class="media-grid-actions">
            <button
                type="button"
                class="btn btn-default btn-filter-unlinked"
                title="{{ trans('media::media.grid.unlinked_products_help') }}"
            >
                <i class="fa fa-unlink" aria-hidden="true"></i>
                <span class="btn-filter-unlinked-label">{{ trans('media::media.grid.unlinked_products') }}</span>
                <span class="badge badge-unlinked-count hide">0</span>
            </button>

            <div class="checkbox media-grid-select-all">
                <input type="checkbox" id="media-grid-select-all">
                <label for="media-grid-select-all">{{ trans('media::media.grid.select_all') }}</label>
            </div>

            <button type="button" class="btn btn-default btn-delete">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16" fill="none">
                    <path d="M12 3.6665L11.5868 10.3499C11.4813 12.0575 11.4285 12.9113 11.0005 13.5251C10.7889 13.8286 10.5164 14.0847 10.2005 14.2772C9.56141 14.6665 8.70599 14.6665 6.99516 14.6665C5.28208 14.6665 4.42554 14.6665 3.78604 14.2765C3.46987 14.0836 3.19733 13.827 2.98579 13.5231C2.55792 12.9082 2.5063 12.0532 2.40307 10.3433L2 3.6665" stroke="#141B34" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M5 7.82324H9" stroke="#141B34" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M6 10.436H8" stroke="#141B34" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M1 3.66659H13M9.70369 3.66659L9.24858 2.72774C8.94626 2.10409 8.7951 1.79227 8.53435 1.59779C8.47651 1.55465 8.41527 1.51628 8.35122 1.48305C8.06248 1.33325 7.71595 1.33325 7.02289 1.33325C6.31243 1.33325 5.95719 1.33325 5.66366 1.48933C5.59861 1.52392 5.53653 1.56385 5.47807 1.6087C5.2143 1.81105 5.06696 2.13429 4.77228 2.78076L4.36849 3.66659" stroke="#020010" stroke-width="1.5" stroke-linecap="round"/>
                </svg>

                <span>{{ trans('admin::admin.buttons.delete') }}</span>
            </button>
        </div>
    </div>

    <div class="media-grid-delete-progress hide" aria-live="polite">
        <div class="media-grid-delete-progress-bar">
            <div class="media-grid-delete-progress-fill" style="width: 0%"></div>
        </div>
        <p class="media-grid-delete-progress-text"></p>
    </div>

    <div class="media-grid-loading hide">
        <i class="fa fa-spinner fa-spin"></i>
        {{ trans('admin::admin.table.loading') }}
    </div>

    <div class="media-grid-items"></div>

    <div class="media-grid-empty hide">
        <p>{{ trans('admin::admin.table.no_data_available_table') }}</p>
    </div>

    <div class="media-grid-footer">
        <div class="media-grid-footer-left">
            <label class="media-grid-length">
                Show
                <select class="form-control media-grid-per-page">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
                entries
            </label>
        </div>

        <div class="media-grid-footer-right">
            <span class="media-grid-info"></span>
            <div class="media-grid-pagination"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="media-preview-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg media-preview-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title media-preview-filename"></h4>
            </div>

            <div class="modal-body media-preview-body"></div>
        </div>
    </div>
</div>
