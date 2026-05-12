// resources/js/app.js
import {
    Chart,
    BarController, BarElement,
    LineController, LineElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    PointElement, Tooltip, Legend, Title,
} from 'chart.js';

Chart.register(
    BarController, BarElement,
    LineController, LineElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    PointElement, Tooltip, Legend, Title
);

window.Chart = Chart;



// resources/js/charts/payrollOverviewChart.js

window.payrollOverviewChart = function (initialData) {
    return {
        chart: null,

        init() {
            const ctx = document.getElementById('payroll-overview-canvas');
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: initialData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const v = Number(ctx.raw);
                                    return ' ' + v.toLocaleString('fr-FR');
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 11 },
                                callback(val) {
                                    const l = this.getLabelForValue(val);
                                    return l.length > 14 ? l.slice(0, 14) + '…' : l;
                                },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { size: 11 },
                                callback: (v) =>
                                    v >= 1_000_000 ? (v / 1_000_000).toFixed(1) + 'M'
                                    : v >= 1_000   ? (v / 1_000).toFixed(0) + 'k'
                                    : v,
                            },
                        },
                    },
                    animation: { duration: 400, easing: 'easeInOutQuart' },
                },
            });
        },

        onUpdate({ chartData }) {
            if (!this.chart) return;
            this.chart.data.labels   = chartData.labels;
            this.chart.data.datasets = chartData.datasets;
            this.chart.update('active');
        },
    };
};

// resources/js/charts/employeeDemographicsChart.js

window.employeeDemographicsChart = function (initialData, initialType) {
    return {
        chart    : null,
        chartType: initialType,

        // ── Résolution du vrai type Chart.js ───────────────
        resolveType(t) {
            return t === 'bar-h' ? 'bar' : t; // bar-h = bar avec indexAxis: 'y'
        },

        // ── Options selon le type ───────────────────────────
        buildOptions(t) {
            const isHorizontal = t === 'bar-h';
            const isBar        = t === 'bar' || isHorizontal;
            const isDoughnut   = t === 'doughnut';

            return {
                responsive         : true,
                maintainAspectRatio: true,
                indexAxis          : isHorizontal ? 'y' : 'x',
                plugins: {
                    legend: {
                        display : isDoughnut,
                        position: 'right',
                        labels  : { font: { size: 12 }, padding: 16, boxWidth: 12 },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label} : ${ctx.raw} employé(s)`,
                        },
                    },
                },
                cutout: isDoughnut ? '60%' : undefined,
                scales: isBar ? {
                    x: {
                        grid : { display: isHorizontal },
                        ticks: {
                            font    : { size: 11 },
                            callback: isHorizontal
                                ? undefined
                                : function (v) {
                                    const l = this.getLabelForValue(v);
                                    return l?.length > 14 ? l.slice(0, 14) + '…' : l;
                                },
                        },
                    },
                    y: {
                        grid      : { display: !isHorizontal, color: 'rgba(0,0,0,0.04)' },
                        beginAtZero: true,
                        ticks     : {
                            font    : { size: 11 },
                            stepSize: 1,
                            callback: isHorizontal
                                ? function (v) {
                                    const l = this.getLabelForValue(v);
                                    return l?.length > 18 ? l.slice(0, 18) + '…' : l;
                                }
                                : undefined,
                        },
                    },
                } : {},
                animation: { duration: 450, easing: 'easeInOutQuart' },
            };
        },

        // ── Init ───────────────────────────────────────────
        init() {
            const ctx = document.getElementById('demographics-canvas');
            this.chart = new Chart(ctx, {
                type   : this.resolveType(this.chartType),
                data   : initialData,
                options: this.buildOptions(this.chartType),
            });
        },

        // ── Mise à jour (données + type possible) ──────────
        onUpdate({ chartData, chartType }) {
            if (!this.chart) return;

            const newType = chartType ?? this.chartType;

            // Reconstruction si changement de type
            if (newType !== this.chartType) {
                this.chartType = newType;
                this.chart.destroy();
                const ctx = document.getElementById('demographics-canvas');
                this.chart = new Chart(ctx, {
                    type   : this.resolveType(newType),
                    data   : chartData,
                    options: this.buildOptions(newType),
                });
                return;
            }

            this.chart.data.labels   = chartData.labels;
            this.chart.data.datasets = chartData.datasets;
            this.chart.update('active');
        },
    };
};