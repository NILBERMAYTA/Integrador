import QRCode from 'qrcode';
import { Html5Qrcode } from 'html5-qrcode';

const safeFilename = (name) => String(name || 'codigo-qr')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-zA-Z0-9_-]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .toLowerCase() || 'codigo-qr';

const downloadBlob = (blob, filename) => {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
};

const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

window.qrModal = (value, filename = 'codigo-qr') => ({
    value,
    filename: safeFilename(filename),
    svg: '',
    dataUrl: '',
    loading: false,
    error: '',

    async render() {
        if (this.svg) {
            return;
        }

        this.loading = true;
        this.error = '';

        try {
            this.svg = await QRCode.toString(this.value, {
                type: 'svg',
                width: 256,
                margin: 2,
                errorCorrectionLevel: 'M',
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                },
            });

            this.dataUrl = await QRCode.toDataURL(this.value, {
                width: 1024,
                margin: 2,
                errorCorrectionLevel: 'M',
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                },
            });

            this.$refs.qr.innerHTML = this.svg;
            this.$refs.qr.querySelector('svg')?.setAttribute('class', 'h-auto w-full');
        } catch (error) {
            this.error = 'No se pudo generar el codigo QR.';
        } finally {
            this.loading = false;
        }
    },

    async downloadSvg() {
        await this.render();

        if (!this.svg) {
            return;
        }

        downloadBlob(new Blob([this.svg], { type: 'image/svg+xml;charset=utf-8' }), `${this.filename}.svg`);
    },

    async downloadPng() {
        await this.render();

        if (!this.dataUrl) {
            return;
        }

        const response = await fetch(this.dataUrl);
        const blob = await response.blob();
        downloadBlob(blob, `${this.filename}.png`);
    },

    async printQr() {
        await this.render();

        if (!this.svg) {
            return;
        }

        const printWindow = window.open('', '_blank', 'width=720,height=840');

        if (!printWindow) {
            this.error = 'El navegador bloqueo la ventana de impresion.';
            return;
        }

        printWindow.document.write(`
            <!doctype html>
            <html>
                <head>
                    <title>${escapeHtml(this.filename)}</title>
                    <style>
                        body {
                            align-items: center;
                            display: flex;
                            flex-direction: column;
                            font-family: Arial, sans-serif;
                            gap: 16px;
                            min-height: 100vh;
                            justify-content: center;
                            margin: 0;
                        }
                        .qr svg {
                            height: 320px;
                            width: 320px;
                        }
                        .label {
                            max-width: 520px;
                            text-align: center;
                            word-break: break-word;
                        }
                        @media print {
                            body { min-height: auto; }
                        }
                    </style>
                </head>
                <body>
                    <div class="qr">${this.svg}</div>
                    <div class="label">${escapeHtml(this.value)}</div>
                    <script>
                        window.addEventListener('load', () => {
                            window.print();
                            window.close();
                        });
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    },
});

window.qrScanner = (readerId, onScan) => ({
    readerId,
    scanner: null,
    running: false,
    loading: false,
    error: '',
    manualValue: '',

    async open(dialog) {
        dialog.showModal();
        await this.$nextTick();
        await this.start();
    },

    async start() {
        if (this.running || this.loading) {
            return;
        }

        this.loading = true;
        this.error = '';

        try {
            this.scanner = this.scanner || new Html5Qrcode(this.readerId);
            await this.scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: (width, height) => {
                        const size = Math.floor(Math.min(width, height) * 0.72);
                        return { width: size, height: size };
                    },
                },
                async (decodedText) => {
                    await this.submit(decodedText);
                },
                () => {},
            );
            this.running = true;
        } catch (error) {
            this.error = 'No se pudo iniciar la camara. Puedes pegar el contenido del QR manualmente.';
        } finally {
            this.loading = false;
        }
    },

    async stop() {
        if (!this.scanner || !this.running) {
            return;
        }

        try {
            await this.scanner.stop();
        } finally {
            this.running = false;
        }
    },

    async close(dialog) {
        await this.stop();
        dialog.close();
    },

    async submit(value) {
        const normalized = String(value || '').trim();

        if (!normalized) {
            this.error = 'Ingresa o escanea un codigo QR valido.';
            return;
        }

        await this.stop();
        this.$refs.dialog.close();
        this.manualValue = '';
        await onScan(normalized);
    },
});

let dashboardCharts = [];
let predictionCharts = [];
let apexChartsConstructor = null;
let dashboardTrendChart = null;
let dashboardTrendPeriod = 'monthly';

const dashboardChartTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');

    return {
        mode: isDark ? 'dark' : 'light',
        foreground: isDark ? '#cbd5e1' : '#64748b',
        strong: isDark ? '#f8fafc' : '#0f172a',
        grid: isDark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(100, 116, 139, 0.14)',
        tooltip: isDark ? 'dark' : 'light',
    };
};

const baseDashboardChart = (type, height) => {
    const colors = dashboardChartTheme();

    return {
        chart: {
            type,
            height,
            background: 'transparent',
            foreColor: colors.foreground,
            fontFamily: 'Instrument Sans, ui-sans-serif, system-ui, sans-serif',
            parentHeightOffset: 0,
            redrawOnParentResize: true,
            toolbar: { show: false },
            animations: {
                enabled: ! window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                speed: 550,
            },
        },
        theme: { mode: colors.mode },
        dataLabels: { enabled: false },
        grid: {
            borderColor: colors.grid,
            strokeDashArray: 4,
        },
        tooltip: { theme: colors.tooltip },
        noData: {
            text: 'Sin datos para mostrar',
            align: 'center',
            verticalAlign: 'middle',
            style: {
                color: colors.foreground,
                fontSize: '13px',
            },
        },
    };
};

const parseDashboardData = () => {
    const source = document.querySelector('[data-dashboard-data]');

    if (! source) {
        return null;
    }

    try {
        return JSON.parse(source.textContent);
    } catch (error) {
        return null;
    }
};

const parsePredictionChartData = () => {
    const source = document.querySelector('[data-prediction-chart-data]');

    if (! source) {
        return null;
    }

    try {
        return JSON.parse(source.textContent);
    } catch (error) {
        return null;
    }
};

const destroyDashboardCharts = () => {
    dashboardCharts.forEach((chart) => {
        try {
            chart.destroy();
        } catch (error) {
            // The element may already have been removed by Livewire navigation.
        }
    });
    dashboardCharts = [];
    dashboardTrendChart = null;
};

const destroyPredictionCharts = () => {
    predictionCharts.forEach((chart) => {
        try {
            chart.destroy();
        } catch (error) {
            // Livewire may already have replaced the chart container.
        }
    });
    predictionCharts = [];
};

const renderDashboardChart = (selector, options) => {
    const element = document.querySelector(selector);

    if (! element) {
        return;
    }

    element.innerHTML = '';
    const chart = new apexChartsConstructor(element, options);
    dashboardCharts.push(chart);
    chart.render();

    return chart;
};

const updateTrendControls = (period, trend) => {
    const selectedClasses = [
        'bg-[var(--color-surface)]',
        'text-[var(--color-on-surface-strong)]',
        'shadow-sm',
        'dark:bg-[var(--color-surface-dark)]',
        'dark:text-[var(--color-on-surface-dark-strong)]',
    ];
    const idleClasses = [
        'text-[var(--color-on-surface)]/65',
        'dark:text-[var(--color-on-surface-dark)]/65',
    ];

    document.querySelectorAll('[data-trend-period]').forEach((button) => {
        const isSelected = button.dataset.trendPeriod === period;

        button.setAttribute('aria-pressed', String(isSelected));
        selectedClasses.forEach((className) => button.classList.toggle(className, isSelected));
        idleClasses.forEach((className) => button.classList.toggle(className, ! isSelected));
    });

    const title = document.querySelector('[data-trend-title]');
    const total = document.querySelector('[data-trend-total]');

    if (title) {
        title.textContent = trend.title;
    }

    if (total) {
        total.textContent = trend.series.reduce((sum, value) => sum + Number(value || 0), 0);
    }
};

const changeDashboardTrendPeriod = async (period) => {
    const data = parseDashboardData();
    const trend = data?.trend?.[period];

    if (! trend || ! dashboardTrendChart) {
        return;
    }

    dashboardTrendPeriod = period;
    updateTrendControls(period, trend);

    await dashboardTrendChart.updateOptions({
        xaxis: {
            categories: trend.categories,
        },
    });
    await dashboardTrendChart.updateSeries([{
        name: 'Préstamos',
        data: trend.series,
    }]);
};

const renderDashboardCharts = async () => {
    const data = parseDashboardData();

    destroyDashboardCharts();

    if (! data) {
        return;
    }

    if (! apexChartsConstructor) {
        const module = await import('apexcharts');
        apexChartsConstructor = module.default;
    }

    if (! document.querySelector('[data-dashboard-data]')) {
        return;
    }

    const theme = dashboardChartTheme();
    const selectedTrend = data.trend[dashboardTrendPeriod] || data.trend.monthly;
    const trendBase = baseDashboardChart('area', 315);
    dashboardTrendChart = renderDashboardChart('[data-dashboard-chart="trend"]', {
        ...trendBase,
        series: [{
            name: 'Préstamos',
            data: selectedTrend.series,
        }],
        colors: ['#0ea5e9'],
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.38,
                opacityTo: 0.03,
                stops: [0, 85, 100],
            },
        },
        markers: {
            size: 4,
            colors: ['#0ea5e9'],
            strokeColors: theme.mode === 'dark' ? '#0f172a' : '#ffffff',
            strokeWidth: 3,
            hover: { size: 6 },
        },
        xaxis: {
            categories: selectedTrend.categories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                },
            },
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                formatter: (value) => Math.round(value),
            },
        },
        tooltip: {
            ...trendBase.tooltip,
            y: {
                formatter: (value) => `${value} préstamo${value === 1 ? '' : 's'}`,
            },
        },
    });
    updateTrendControls(dashboardTrendPeriod, selectedTrend);

    const conditionBase = baseDashboardChart('donut', 255);
    renderDashboardChart('[data-dashboard-chart="condition"]', {
        ...conditionBase,
        series: data.condition.series,
        labels: data.condition.labels,
        colors: ['#10b981', '#f59e0b', '#f97316', '#f43f5e'],
        stroke: {
            width: 4,
            colors: [theme.mode === 'dark' ? '#0f172a' : '#ffffff'],
        },
        legend: { show: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            color: theme.foreground,
                            offsetY: 18,
                        },
                        value: {
                            show: true,
                            color: theme.strong,
                            fontSize: '28px',
                            fontWeight: 700,
                            offsetY: -15,
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            color: theme.foreground,
                            formatter: (chart) => chart.globals.seriesTotals.reduce((sum, value) => sum + value, 0),
                        },
                    },
                },
            },
        },
        tooltip: {
            ...conditionBase.tooltip,
            y: {
                formatter: (value) => `${value} serie${value === 1 ? '' : 's'}`,
            },
        },
    });

    const inventoryHeight = Math.max(320, data.inventory.categories.length * 46);
    const inventoryBase = baseDashboardChart('bar', inventoryHeight);
    renderDashboardChart('[data-dashboard-chart="inventory"]', {
        ...inventoryBase,
        series: [{
            name: 'Artículos',
            data: data.inventory.series,
        }],
        colors: ['#14b8a6'],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 6,
                borderRadiusApplication: 'end',
                barHeight: '52%',
                distributed: false,
            },
        },
        xaxis: {
            categories: data.inventory.categories,
            min: 0,
            forceNiceScale: true,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                formatter: (value) => Math.round(value),
            },
        },
        yaxis: {
            labels: {
                maxWidth: 150,
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                },
            },
        },
        grid: {
            ...inventoryBase.grid,
            xaxis: { lines: { show: true } },
            yaxis: { lines: { show: false } },
            padding: { left: 8, right: 18 },
        },
        tooltip: {
            ...inventoryBase.tooltip,
            y: {
                formatter: (value) => `${value} artículo${value === 1 ? '' : 's'}`,
            },
        },
    });

    const statusBase = baseDashboardChart('radialBar', 245);
    const statusTotal = data.seriesStatus.series.reduce((sum, value) => sum + value, 0);
    const statusPercentages = data.seriesStatus.series.map((value) => (
        statusTotal > 0 ? Math.round((value / statusTotal) * 100) : 0
    ));

    renderDashboardChart('[data-dashboard-chart="series-status"]', {
        ...statusBase,
        series: statusPercentages,
        labels: data.seriesStatus.labels,
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#f43f5e'],
        legend: {
            show: true,
            position: 'bottom',
            fontSize: '11px',
            markers: { size: 6 },
            itemMargin: {
                horizontal: 8,
                vertical: 4,
            },
            formatter: (label, options) => `${label}: ${data.seriesStatus.series[options.seriesIndex]}`,
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    size: '28%',
                },
                track: {
                    background: theme.mode === 'dark' ? '#1e293b' : '#e2e8f0',
                    strokeWidth: '96%',
                    margin: 3,
                },
                dataLabels: {
                    name: { show: false },
                    value: { show: false },
                    total: {
                        show: true,
                        label: 'Series',
                        color: theme.foreground,
                        fontSize: '12px',
                        formatter: () => statusTotal,
                    },
                },
            },
        },
        stroke: {
            lineCap: 'round',
        },
        tooltip: {
            ...statusBase.tooltip,
            y: {
                formatter: (_, options) => {
                    const value = data.seriesStatus.series[options.seriesIndex];
                    return `${value} serie${value === 1 ? '' : 's'}`;
                },
            },
        },
    });
};

const renderPredictionCharts = async () => {
    const data = parsePredictionChartData();

    destroyPredictionCharts();

    if (! data) {
        return;
    }

    if (! apexChartsConstructor) {
        const module = await import('apexcharts');
        apexChartsConstructor = module.default;
    }

    if (! document.querySelector('[data-prediction-chart-data]')) {
        return;
    }

    const theme = dashboardChartTheme();
    const charts = [
        {
            selector: '[data-prediction-chart="risk"]',
            labels: data.risk.labels,
            series: data.risk.series,
            colors: ['#f43f5e', '#f59e0b', '#10b981'],
            totalLabel: 'Predicciones',
            itemLabel: 'predicción',
        },
        {
            selector: '[data-prediction-chart="status"]',
            labels: data.status.labels,
            series: data.status.series,
            colors: ['#10b981', '#f43f5e'],
            totalLabel: 'Series',
            itemLabel: 'serie',
        },
    ];

    charts.forEach((config) => {
        const element = document.querySelector(config.selector);

        if (! element) {
            return;
        }

        element.innerHTML = '';
        const base = baseDashboardChart('donut', 220);
        const chart = new apexChartsConstructor(element, {
            ...base,
            series: config.series,
            labels: config.labels,
            colors: config.colors,
            stroke: {
                width: 4,
                colors: [theme.mode === 'dark' ? '#0f172a' : '#ffffff'],
            },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                color: theme.foreground,
                                offsetY: 18,
                            },
                            value: {
                                show: true,
                                color: theme.strong,
                                fontSize: '26px',
                                fontWeight: 700,
                                offsetY: -15,
                            },
                            total: {
                                show: true,
                                label: config.totalLabel,
                                color: theme.foreground,
                                formatter: (context) => context.globals.seriesTotals
                                    .reduce((sum, value) => sum + value, 0),
                            },
                        },
                    },
                },
            },
            tooltip: {
                ...base.tooltip,
                y: {
                    formatter: (value) => `${value} ${config.itemLabel}${value === 1 ? '' : config.itemLabel === 'predicción' ? 'es' : 's'}`,
                },
            },
        });

        predictionCharts.push(chart);
        chart.render();
    });
};

const scheduleDashboardChartsUpdate = () => {
    window.requestAnimationFrame(() => renderDashboardCharts());
};

const schedulePredictionChartsUpdate = () => {
    window.requestAnimationFrame(() => renderPredictionCharts());
};

const applyAppearance = (appearance) => {
    if (window.Flux?.applyAppearance) {
        window.Flux.applyAppearance(appearance);
    } else {
        document.documentElement.classList.toggle('dark', appearance === 'dark');
    }

    document.documentElement.dataset.appearance = appearance;
};

document.addEventListener('DOMContentLoaded', () => {
    renderDashboardCharts();
    renderPredictionCharts();
});
document.addEventListener('livewire:navigated', () => {
    const appearance = document.documentElement.classList.contains('dark') ? 'dark' : 'light';

    document.documentElement.dataset.appearance = appearance;
    updateAppearanceToggles(appearance);
    renderDashboardCharts();
    renderPredictionCharts();
});

window.addEventListener('predictions-updated', schedulePredictionChartsUpdate);

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-trend-period]');

    if (button) {
        changeDashboardTrendPeriod(button.dataset.trendPeriod);
    }
});

window.addEventListener('theme-changed', (event) => {
    const { theme, appearance } = event.detail || {};

    if (!theme) {
        return;
    }

    document.documentElement.dataset.theme = theme;
    applyAppearance(appearance);
    document.querySelectorAll('[data-appearance-toggle]').forEach((button) => {
        if (appearance === 'dark') {
            button.dataset.darkTheme = theme;
        } else {
            button.dataset.lightTheme = theme;
        }
    });
    updateAppearanceToggles(appearance);
    scheduleDashboardChartsUpdate();
    schedulePredictionChartsUpdate();
});

const updateAppearanceToggles = (appearance) => {
    document.querySelectorAll('[data-appearance-toggle]').forEach((button) => {
        const isDark = appearance === 'dark';

        button.dataset.appearance = appearance;
        button.querySelector('[data-theme-icon="sun"]')?.classList.toggle('hidden', ! isDark);
        button.querySelector('[data-theme-icon="moon"]')?.classList.toggle('hidden', isDark);

        const accessibleLabel = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
        button.setAttribute('aria-label', accessibleLabel);
        button.setAttribute('title', accessibleLabel);
    });
};

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-appearance-toggle]');

    if (! button || button.disabled) {
        return;
    }

    const previousTheme = document.documentElement.dataset.theme;
    const previousAppearance = button.dataset.appearance;
    const targetAppearance = previousAppearance === 'dark' ? 'light' : 'dark';
    const targetTheme = targetAppearance === 'dark'
        ? button.dataset.darkTheme
        : button.dataset.lightTheme;

    button.disabled = true;
    document.documentElement.dataset.theme = targetTheme;
    applyAppearance(targetAppearance);
    updateAppearanceToggles(targetAppearance);

    try {
        const response = await fetch(button.dataset.toggleUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        });

        if (! response.ok) {
            throw new Error('No se pudo guardar la apariencia.');
        }

        const { theme, appearance } = await response.json();
        document.documentElement.dataset.theme = theme;
        applyAppearance(appearance);
        updateAppearanceToggles(appearance);
    } catch (error) {
        document.documentElement.dataset.theme = previousTheme;
        applyAppearance(previousAppearance);
        updateAppearanceToggles(previousAppearance);
    } finally {
        button.disabled = false;
        scheduleDashboardChartsUpdate();
    }
});
