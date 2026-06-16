(function () {
    function getTheme() {
        return localStorage.getItem('trendwatch-theme') || 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('trendwatch-theme', theme);
        var button = document.getElementById('themeToggle');
        if (button) {
            button.textContent = theme === 'dark' ? 'Light' : 'Dark';
        }
    }

    function cssVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function chartOptions(extra) {
        var text = cssVar('--text') || '#0f172a';
        var muted = cssVar('--muted') || '#64748b';
        var border = cssVar('--border') || '#e2e8f0';

        return Object.assign({
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false,
                    labels: { color: text, boxWidth: 12, usePointStyle: true }
                }
            },
            scales: {
                x: { ticks: { color: muted }, grid: { color: border } },
                y: { beginAtZero: true, ticks: { color: muted }, grid: { color: border } }
            }
        }, extra || {});
    }

    function makeChart(id, config) {
        if (typeof Chart === 'undefined') {
            return;
        }
        var canvas = document.getElementById(id);
        if (!canvas) {
            return;
        }
        new Chart(canvas, config);
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getTheme());

        var themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                var next = getTheme() === 'dark' ? 'light' : 'dark';
                applyTheme(next);
                window.location.reload();
            });
        }

        var sidebar = document.getElementById('sidebar');
        var sidebarToggle = document.getElementById('sidebarToggle');
        var backdrop = document.getElementById('mobileBackdrop');

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (backdrop) backdrop.classList.remove('show');
        }

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('open');
                if (backdrop) backdrop.classList.toggle('show');
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        document.querySelectorAll('.nav-menu a').forEach(function (item) {
            item.addEventListener('click', closeSidebar);
        });

        var primary = cssVar('--primary') || '#2563eb';
        var primarySoft = 'rgba(37, 99, 235, 0.12)';
        var text = cssVar('--text') || '#0f172a';

        if (window.trendwatchCharts) {
            var data = window.trendwatchCharts;

            makeChart('volumeChart', {
                type: 'bar',
                data: {
                    labels: data.trendLabels || [],
                    datasets: [{
                        label: 'Volume pencarian',
                        data: data.trendVolumes || [],
                        borderWidth: 1,
                        borderRadius: 10,
                        backgroundColor: primary
                    }]
                },
                options: chartOptions({ plugins: { legend: { display: false } } })
            });

            makeChart('categoryChart', {
                type: 'doughnut',
                data: {
                    labels: data.categoryLabels || [],
                    datasets: [{ data: data.categoryTotals || [] }]
                },
                options: chartOptions({
                    plugins: { legend: { display: true, position: 'bottom', labels: { color: text, boxWidth: 12, usePointStyle: true } } },
                    scales: {}
                })
            });

            makeChart('historyChart', {
                type: 'line',
                data: {
                    labels: data.historyLabels || [],
                    datasets: [{
                        label: 'Pergerakan volume',
                        data: data.historyVolumes || [],
                        tension: 0.35,
                        fill: true,
                        borderColor: primary,
                        backgroundColor: primarySoft,
                        pointRadius: 3
                    }]
                },
                options: chartOptions({ plugins: { legend: { display: false } } })
            });
        }

        if (window.trendwatchDetailChart) {
            var detail = window.trendwatchDetailChart;
            makeChart('detailHistoryChart', {
                type: 'line',
                data: {
                    labels: detail.labels || [],
                    datasets: [{
                        label: 'Histori volume',
                        data: detail.volumes || [],
                        tension: 0.35,
                        fill: true,
                        borderColor: primary,
                        backgroundColor: primarySoft,
                        pointRadius: 3
                    }]
                },
                options: chartOptions({ plugins: { legend: { display: false } } })
            });
        }

        if (window.trendwatchAnalytics) {
            var analytics = window.trendwatchAnalytics;

            makeChart('analyticsVolumeChart', {
                type: 'bar',
                data: {
                    labels: analytics.volumeLabels || [],
                    datasets: [{
                        label: 'Volume pencarian',
                        data: analytics.volumeData || [],
                        borderRadius: 10,
                        backgroundColor: primary
                    }]
                },
                options: chartOptions({ plugins: { legend: { display: false } } })
            });

            makeChart('analyticsCategoryChart', {
                type: 'doughnut',
                data: {
                    labels: analytics.categoryLabels || [],
                    datasets: [{ data: analytics.categoryTotals || [] }]
                },
                options: chartOptions({
                    plugins: { legend: { display: true, position: 'bottom', labels: { color: text, boxWidth: 12, usePointStyle: true } } },
                    scales: {}
                })
            });

            makeChart('analyticsHistoryChart', {
                type: 'line',
                data: {
                    labels: analytics.historyLabels || [],
                    datasets: [{
                        label: 'Volume tren pilihan',
                        data: analytics.historyVolumes || [],
                        tension: 0.35,
                        fill: true,
                        borderColor: primary,
                        backgroundColor: primarySoft,
                        pointRadius: 3
                    }]
                },
                options: chartOptions({ plugins: { legend: { display: false } } })
            });

            makeChart('analyticsStatusChart', {
                type: 'pie',
                data: {
                    labels: analytics.statusLabels || [],
                    datasets: [{ data: analytics.statusTotals || [] }]
                },
                options: chartOptions({
                    plugins: { legend: { display: true, position: 'bottom', labels: { color: text, boxWidth: 12, usePointStyle: true } } },
                    scales: {}
                })
            });

            makeChart('analyticsCompareChart', {
                type: 'line',
                data: {
                    labels: analytics.comparisonLabels || [],
                    datasets: analytics.comparisonDatasets || []
                },
                options: chartOptions({
                    plugins: { legend: { display: true, position: 'bottom', labels: { color: text, boxWidth: 12, usePointStyle: true } } }
                })
            });

            makeChart('analyticsSeoChart', {
                type: 'bar',
                data: {
                    labels: analytics.seoLabels || [],
                    datasets: [{
                        label: 'SEO Score',
                        data: analytics.seoData || [],
                        borderRadius: 10,
                        backgroundColor: primary
                    }]
                },
                options: chartOptions({
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, max: 100 },
                        y: { beginAtZero: true }
                    }
                })
            });
        }
    });
})();
