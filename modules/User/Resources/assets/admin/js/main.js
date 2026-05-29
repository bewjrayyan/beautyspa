const $permissionsRoot = $('.permissions-manager');

function permissionBulkAction(action) {
    $(`.permission-${action}`).prop('checked', true);
}

function permissionGroupBulkAction(event, action) {
    $(event.currentTarget)
        .closest('.permission-group')
        .find(`.permission-${action}`)
        .prop('checked', true);
}

$permissionsRoot.on(
    'click',
    '.permission-parent-actions > .allow-all, .permission-parent-actions > .deny-all, .permission-parent-actions > .inherit-all',
    (event) => {
        const action = event.currentTarget.className
            .split(/\s+/)
            .find((className) => ['allow-all', 'deny-all', 'inherit-all'].includes(className))
            ?.split('-')[0];

        if (action) {
            permissionBulkAction(action);
        }
    },
);

$permissionsRoot.on(
    'click',
    '.permission-group-actions > .allow-all, .permission-group-actions > .deny-all, .permission-group-actions > .inherit-all',
    (event) => {
        const action = event.currentTarget.className
            .split(/\s+/)
            .find((className) => ['allow-all', 'deny-all', 'inherit-all'].includes(className))
            ?.split('-')[0];

        if (action) {
            permissionGroupBulkAction(event, action);
        }
    },
);

function setModuleOpen($module, open) {
    $module.toggleClass('is-open', open);
    $module.find('.permission-module__toggle').attr('aria-expanded', open ? 'true' : 'false');
}

$permissionsRoot.on('click', '.permission-module__header', (event) => {
    if ($(event.target).closest('.permission-group-actions, .permissions-bulk').length) {
        return;
    }

    const $module = $(event.currentTarget).closest('.permission-module');

    setModuleOpen($module, ! $module.hasClass('is-open'));
});

$permissionsRoot.on('click', '.permission-module__toggle', (event) => {
    event.stopPropagation();

    const $module = $(event.currentTarget).closest('.permission-module');

    setModuleOpen($module, ! $module.hasClass('is-open'));
});

$permissionsRoot.on('click', '.permissions-expand-all', () => {
    $permissionsRoot.find('.permission-module:not(.is-hidden)').each((_, element) => {
        setModuleOpen($(element), true);
    });
});

$permissionsRoot.on('click', '.permissions-collapse-all', () => {
    $permissionsRoot.find('.permission-module').each((_, element) => {
        setModuleOpen($(element), false);
    });
});

$permissionsRoot.on('input', '.permissions-search-input', (event) => {
    const query = event.currentTarget.value.trim().toLowerCase();
    let visibleCount = 0;

    $permissionsRoot.find('.permission-module').each((_, element) => {
        const $module = $(element);
        const haystack = [
            $module.data('module-label') || '',
            $module.data('module') || '',
            $module.text().toLowerCase(),
        ].join(' ');

        const visible = query === '' || haystack.includes(query);

        $module.toggleClass('is-hidden', ! visible);

        if (visible) {
            visibleCount += 1;
        }
    });

    $permissionsRoot.find('.permissions-empty').prop('hidden', visibleCount > 0);
});

$('.delete-api-key').on('click', (event) => {
    $('#confirmation-form').attr('action', event.currentTarget.dataset.action);
});
