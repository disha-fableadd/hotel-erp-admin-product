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
    // Universal image path resolver for the entire ERP application
    window.resolveImageUrl = function(src, fallback) {
        fallback = fallback || '/admin/assets/img/customer/customer5.jpg';
        if (!src) return fallback;
        var s = String(src).trim();
        if (!s || s === 'null' || s === 'undefined') return fallback;

        // Strip localhost / 127.0.0.1 or app host
        if (s.indexOf('127.0.0.1') !== -1 || s.indexOf('localhost') !== -1) {
            s = s.replace(/^https?:\/\/[^\/]+/, '');
        }

        // If it's a full remote external URL (not storage/admin), keep it
        if (s.indexOf('http://') === 0 || s.indexOf('https://') === 0) {
            if (s.indexOf('/storage/') !== -1 || s.indexOf('/admin/assets/') !== -1) {
                s = s.replace(/^https?:\/\/[^\/]+/, '');
            } else {
                return s;
            }
        }

        s = s.replace(/^\/+/, '');
        if (s.indexOf('storage/') === 0) {
            return '/' + s;
        }
        if (s.indexOf('admin/assets/') === 0) {
            return '/' + s;
        }
        return '/storage/' + s;
    };

    // Universal error listener (capture phase) to prevent any broken image icon anywhere
    document.addEventListener('error', function(e) {
        if (e.target && e.target.tagName === 'IMG') {
            var img = e.target;
            var step = parseInt(img.getAttribute('data-img-fallback-step') || '0', 10);
            if (step === 0) {
                img.setAttribute('data-img-fallback-step', '1');
                var src = img.src || '';
                var isIcon = src.indexOf('.svg') !== -1 || (img.className || '').indexOf('icon') !== -1;
                if (!isIcon) {
                    img.src = '/admin/assets/img/customer/customer5.jpg';
                } else {
                    img.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="%23ff9f43" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                }
            } else if (step === 1) {
                img.setAttribute('data-img-fallback-step', '2');
                img.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="%23f3f4f6" width="100" height="100"/><circle cx="50" cy="38" r="20" fill="%23d1d5db"/><path d="M20 85 C20 65, 35 60, 50 60 C65 60, 80 65, 80 85 Z" fill="%23d1d5db"/></svg>';
            }
        }
    }, true);
</script>

<script>
    $(document).ready(function () {
        setTimeout(function () {
            $('#global-loader').fadeOut('fast');
        }, 800);
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

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: {!! json_encode(session('error_title') ?? 'Notice') !!},
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#ff9f43'
            });
        }
    });
</script>
@endif

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: {!! json_encode(session('success')) !!},
                confirmButtonColor: '#ff9f43'
            });
        }
    });
</script>
@endif