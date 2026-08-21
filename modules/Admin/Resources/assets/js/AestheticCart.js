$.AestheticCart = {};

/* ----------------------------------
   - AestheticCart Options -
   ---------------------------------- */
$.AestheticCart.options = {
    animationSpeed: 220,
    // Sidebar push menu toggle button selector
    sidebarToggleSelector: "[data-toggle='offcanvas']",
    // Activate sidebar push menu
    sidebarPushMenu: true,
    // BoxRefresh Plugin
    enableBoxRefresh: true,
    // Bootstrap.js tooltip
    enableBSToppltip: true,
    BSTooltipSelector: "[data-toggle='tooltip']",
    // Control Sidebar Tree views
    enableControlTreeView: true,
    // The standard screen sizes that bootstrap uses.
    screenSizes: {
        xs: 480,
        sm: 768,
        md: 992,
        lg: 1200,
    },
};

/* ----------------------------------
   - Implementation -
   ---------------------------------- */
$(function () {
    // Easy access to options
    var o = $.AestheticCart.options;

    // Set up the object
    _init();

    // Activate layout
    $.AestheticCart.layout.activate();

    // Enable sidebar tree view controls
    if (o.enableControlTreeView) {
        $.AestheticCart.tree(".sidebar");
    }

    // Activate sidebar push menu
    if (o.sidebarPushMenu) {
        $.AestheticCart.pushMenu.activate(o.sidebarToggleSelector);
    }

    // Activate Bootstrap tooltip
    if (o.enableBSToppltip) {
        $("body").tooltip({
            selector: o.BSTooltipSelector,
            container: "body",
        });
    }
});

/* ----------------------------------
   - Initialize the AestheticCart Object -
   ---------------------------------- */
function _init() {
    // Layout
    $.AestheticCart.layout = {
        activate: function () {
            var _this = this;
            _this.fix();

            $(window, ".wrapper").resize(function () {
                _this.fix();
            });
        },
        fix: function () {
            var window_height = $(window).height();

            $(".wrapper").css("min-height", window_height + "px");
        },
    };

    // PushMenu
    $.AestheticCart.pushMenu = {
        activate: function (toggleBtn) {
            var screenSizes = $.AestheticCart.options.screenSizes;

            $(document).on("click", toggleBtn, function (e) {
                e.preventDefault();

                if ($(window).outerWidth() > screenSizes.md - 1) {
                    if ($("body").hasClass("sidebar-collapse")) {
                        $("body")
                            .removeClass("sidebar-collapse")
                            .trigger("expanded.pushMenu");

                        return;
                    }

                    $("body")
                        .addClass("sidebar-collapse")
                        .trigger("collapsed.pushMenu");

                    return;
                }

                if ($("body").hasClass("sidebar-open")) {
                    $("body")
                        .removeClass("sidebar-open")
                        .removeClass("sidebar-collapse")
                        .trigger("collapsed.pushMenu");

                    return;
                }

                $("body").addClass("sidebar-open").trigger("expanded.pushMenu");
            });

            $(window).on("resize", function () {
                if ($(window).outerWidth() > screenSizes.md - 1) {
                    return;
                } else {
                    $("body").removeClass("sidebar-collapse");
                }
            });

            $(".content-wrapper").click(function () {
                if (
                    $(window).width() <= screenSizes.md - 1 &&
                    $("body").hasClass("sidebar-open")
                ) {
                    $("body").removeClass("sidebar-open");
                }
            });
        },
    };

    // Tree (accordion + auto-open active parents)
    $.AestheticCart.tree = function (menu) {
        var animationSpeed = $.AestheticCart.options.animationSpeed;
        var $root = $(menu);

        function setExpanded($link, expanded) {
            $link.attr("aria-expanded", expanded ? "true" : "false");
        }

        function closeSiblings($li) {
            $li.siblings(".treeview.selected, .treeview:not(.closed)")
                .each(function () {
                    var $sibling = $(this);
                    var $menu = $sibling.children(".treeview-menu:visible");

                    $sibling.removeClass("selected").addClass("closed");
                    setExpanded($sibling.children("a").first(), false);

                    if ($menu.length) {
                        $menu.stop(true, true).slideUp(animationSpeed);
                    }
                });
        }

        // Ensure active parents start expanded
        $root.find("li.treeview.active").each(function () {
            var $li = $(this);
            var $link = $li.children("a").first();
            var $submenu = $li.children(".treeview-menu").first();

            $li.removeClass("closed").addClass("selected");
            setExpanded($link, true);

            if ($submenu.length && !$("body").hasClass("sidebar-collapse")) {
                $submenu.show();
            }
        });

        $(document)
            .off("click.aestheticSidebar", menu + " li.treeview > a")
            .on("click.aestheticSidebar", menu + " li.treeview > a", function (e) {
                var self = $(this);
                var $li = self.parent();
                var checkElement = self.nextAll(".treeview-menu").first();

                if (!checkElement.length) {
                    return;
                }

                // Always toggle children for treeview parents
                e.preventDefault();

                if ($("body").hasClass("sidebar-collapse")) {
                    return;
                }

                var isOpen =
                    checkElement.is(":visible") && !$li.hasClass("closed");

                if (isOpen) {
                    $li.removeClass("selected").addClass("closed");
                    setExpanded(self, false);
                    checkElement.stop(true, true).slideUp(animationSpeed);
                    return;
                }

                closeSiblings($li);
                $li.addClass("selected").removeClass("closed");
                setExpanded(self, true);
                checkElement.stop(true, true).slideDown(animationSpeed);
            });
    };
}

/* ----------------------------------
   - Box Refresh Button -
   ---------------------------------- */
(function ($) {
    $.fn.boxRefresh = function (options) {
        var settings = $.extend(
            {
                trigger: ".refresh-btn",
                source: "",
                onLoadStart: function (box) {
                    return box;
                },
                onLoadDone: function (box) {
                    return box;
                },
            },
            options
        );

        var overlay = $(
            '<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>'
        );

        return this.each(function () {
            if (settings.source === "") {
                if (window.console) {
                    window.console.log(
                        "Please specify a source first - boxRefresh()"
                    );
                }

                return;
            }

            var box = $(this);
            var rBtn = box.find(settings.trigger).first();

            rBtn.on("click", function (e) {
                e.preventDefault();
                start(box);

                box.find(".box-body").load(settings.source, function () {
                    done(box);
                });
            });
        });

        function start(box) {
            box.append(overlay);
            settings.onLoadStart.call(box);
        }

        function done(box) {
            box.find(overlay).remove();
            settings.onLoadDone.call(box);
        }
    };
})(jQuery);
