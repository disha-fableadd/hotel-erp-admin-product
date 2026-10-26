<script src="{{ env('ImagePath').'/admin/assets/js/jquery-3.6.0.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/feather.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/jquery.slimscroll.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/jquery.dataTables.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/dataTables.bootstrap4.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/bootstrap.bundle.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/apexchart/apexcharts.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/apexchart/chart-data.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/moment.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/jquery.flot.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/jquery.flot.fillbetween.js' }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot.tooltip/0.9.0/jquery.flot.tooltip.min.js"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/jquery.flot.pie.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/chart-data.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/script.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/bootstrap-datetimepicker.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/owlcarousel/owl.carousel.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/select2/js/select2.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/sweetalert/sweetalert2.all.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/sweetalert/sweetalerts.min.js' }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>





<script>
    $(document).ready(function () {
        var $sidebarMenu = $('#sidebar-menu');
        var $searchInput = $('#sidebar-module-search');

        function expandAllModulesInCategory($category) {
            $category.find('> ul > li.sidebar-module').each(function () {
                $(this).children('a').addClass('subdrop');
                $(this).children('ul').show();
            });
        }

        function openCategory($category, expandAllModules) {
            if (!$category || !$category.length) {
                return;
            }

            $category.children('a').addClass('subdrop');
            $category.children('ul').show();

            if (expandAllModules) {
                expandAllModulesInCategory($category);
            }
        }

        function expandActiveModule($link) {
            var $module = $link.closest('li.sidebar-module.submenu');

            if (!$module.length) {
                return;
            }

            $module.children('a').addClass('subdrop');
            $module.children('ul').show();
        }

        function collapseAllCategories() {
            $sidebarMenu.find('.sidebar-category').each(function () {
                var $cat = $(this);
                $cat.children('a').removeClass('subdrop');
                $cat.children('ul').hide();
                $cat.find('> ul > li.sidebar-module > a.subdrop').removeClass('subdrop');
                $cat.find('> ul > li.sidebar-module > ul').hide();
            });
        }

        function expandActiveMenu() {
            collapseAllCategories();

            var currentUrl = window.location.href.split(/[?#]/)[0];
            var $activeLink = null;

            $sidebarMenu.find('a').each(function () {
                var activeUrl = $(this).data('active-url') || '';
                if (activeUrl && activeUrl.split(/[?#]/)[0] === currentUrl) {
                    $activeLink = $(this);
                    return false;
                }

                var href = this.href || '';
                if (!href || href.indexOf('javascript') === 0) {
                    return;
                }

                if (href.split(/[?#]/)[0] === currentUrl) {
                    $activeLink = $(this);
                    return false;
                }
            });

            if (!$activeLink || !$activeLink.length) {
                openCategory($sidebarMenu.find('.sidebar-category-erp').first(), false);
                return;
            }

            $activeLink.addClass('active');

            var $activeCategory = $activeLink.closest('.sidebar-category');
            if ($activeCategory.length) {
                openCategory($activeCategory, false);
                expandActiveModule($activeLink);

                // On the dashboard page, also auto-expand the ERP section by default
                if ($activeCategory.hasClass('sidebar-category-dashboard')) {
                    openCategory($sidebarMenu.find('.sidebar-category-erp').first(), false);
                }
            } else {
                openCategory($sidebarMenu.find('.sidebar-category-erp').first(), false);
            }

            setTimeout(function () {
                if ($activeLink[0] && typeof $activeLink[0].scrollIntoView === 'function') {
                    $activeLink[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 300);
        }

        function resetSidebarSearch() {
            $searchInput.val('');
            $sidebarMenu.removeClass('sidebar-search-active');
            $sidebarMenu.find('li').removeClass('sidebar-no-match sidebar-search-match').show();
            expandActiveMenu();
        }

        function filterSidebarModules(query) {
            var q = $.trim(query).toLowerCase();

            if (!q) {
                resetSidebarSearch();
                return;
            }

            $sidebarMenu.addClass('sidebar-search-active');
            $sidebarMenu.find('li').removeClass('sidebar-search-match').show();

            collapseAllCategories();
            $sidebarMenu.find('> ul > li:not(.sidebar-category):not(.submenu)').hide();

            $sidebarMenu.find('a').each(function () {
                var href = $(this).attr('href') || '';
                if (!href || href.indexOf('javascript') === 0) {
                    return;
                }

                var linkText = $(this).text().toLowerCase();
                var searchData = ($(this).data('search') || '').toLowerCase();
                var $module = $(this).closest('li.sidebar-module');
                var moduleText = $module.length ? $module.children('a').find('span').first().text().toLowerCase() : '';
                var categoryText = $(this).closest('.sidebar-category').children('a').find('span').first().text().toLowerCase();
                var haystack = [linkText, searchData, moduleText, categoryText].join(' ');
                
                if (haystack.indexOf(q) === -1) {
                    return;
                }

                $(this).parents('li').removeClass('sidebar-no-match').addClass('sidebar-search-match');
                $(this).parents('.sidebar-category').each(function () {
                    $(this).removeClass('sidebar-no-match').show();
                    openCategory($(this), true);
                });
            });

            $sidebarMenu.find('.sidebar-category').each(function () {
                var $cat = $(this);
                var hasMatch = $cat.find('li.sidebar-search-match, a.active').length > 0;
                var catText = $cat.children('a').text().toLowerCase();

                if (!hasMatch && catText.indexOf(q) === -1) {
                    $cat.addClass('sidebar-no-match').hide();
                } else {
                    $cat.removeClass('sidebar-no-match').show();
                    openCategory($cat, true);
                }
            });

            $sidebarMenu.find('.sidebar-category > ul > li.sidebar-module').each(function () {
                var $module = $(this);
                var moduleText = $module.children('a').find('span').first().text().toLowerCase();
                var hasMatch = $module.hasClass('sidebar-search-match') ||
                    $module.find('.sidebar-search-match').length > 0 ||
                    moduleText.indexOf(q) >= 0;
                var isDirectLink = $module.hasClass('sidebar-module-link');

                if (isDirectLink && $module.find('a.active, a.sidebar-search-match').length) {
                    return;
                }

                if (moduleText.indexOf(q) >= 0) {
                    $module.removeClass('sidebar-no-match').show();
                    return;
                }

                if (!hasMatch && !isDirectLink) {
                    $module.addClass('sidebar-no-match').hide();
                }
            });
        }

        expandActiveMenu();

        $searchInput.on('input', function () {
            filterSidebarModules($(this).val());
        });

        $(document).on('click', '.sidebar-category > a', function () {
            if ($sidebarMenu.hasClass('sidebar-search-active')) {
                return;
            }

            var $current = $(this).parent();

            setTimeout(function () {
                if (!$current.children('a').hasClass('subdrop')) {
                    return;
                }

                $sidebarMenu.find('.sidebar-category').not($current).each(function () {
                    $(this).children('a').removeClass('subdrop');
                    $(this).children('ul').slideUp(200);
                    $(this).find('> ul > li.sidebar-module > a.subdrop').removeClass('subdrop');
                    $(this).find('> ul > li.sidebar-module > ul').slideUp(200);
                });
            }, 0);
        });
    });
</script>

<!-- Active Link Script -->
<script>
    $(document).ready(function () {
        var currentUrl = window.location.href.split(/[?#]/)[0]; // remove query/hash if any

        $('#sidebar-menu a').each(function () {
            var linkUrl = this.href.split(/[?#]/)[0];

            if (linkUrl === currentUrl) {
                $(this).addClass('active');

                // Expand all parent submenus
                $(this).parents('li.submenu').each(function() {
                    $(this).find('> ul').show(); // Show the immediate submenu
                    $(this).find('> a').addClass('active subdrop'); // Highlight the immediate parent link
                });
            }
        });
    });

</script>