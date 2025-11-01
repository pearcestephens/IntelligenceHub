/**
 * 09-charts.js - Chart.js integration and utilities
 */

const Charts = {
    defaultOptions: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    },

    /**
     * Create line chart
     */
    createLineChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const finalOptions = { ...this.defaultOptions, ...options };

        return new Chart(ctx, {
            type: 'line',
            data: data,
            options: finalOptions
        });
    },

    /**
     * Create bar chart
     */
    createBarChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const finalOptions = { ...this.defaultOptions, ...options };

        return new Chart(ctx, {
            type: 'bar',
            data: data,
            options: finalOptions
        });
    },

    /**
     * Create pie chart
     */
    createPieChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const finalOptions = { ...this.defaultOptions, ...options };

        return new Chart(ctx, {
            type: 'pie',
            data: data,
            options: finalOptions
        });
    },

    /**
     * Create doughnut chart
     */
    createDoughnutChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const finalOptions = { ...this.defaultOptions, ...options };

        return new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: finalOptions
        });
    },

    /**
     * Sample data generator
     */
    sampleData(labels, datasetLabel) {
        return {
            labels: labels,
            datasets: [{
                label: datasetLabel,
                data: Array.from({ length: labels.length }, () => Math.floor(Math.random() * 100)),
                borderColor: 'rgb(13, 110, 253)',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.1
            }]
        };
    }
};

console.log('✓ Charts module loaded');
