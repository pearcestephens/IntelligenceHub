/**
 * CIS Intelligence Dashboard - Charts Module
 * Chart.js integration and data visualization
 * Version: 2.0.0
 */

(function() {
  'use strict';

  const ChartsModule = {
    // Chart.js default configuration
    defaults: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            font: {
              family: "'Inter', -apple-system, sans-serif",
              size: 12
            },
            padding: 15,
            usePointStyle: true
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          cornerRadius: 8,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          displayColors: true,
          borderColor: 'rgba(255, 255, 255, 0.1)',
          borderWidth: 1
        }
      }
    },

    colors: {
      primary: '#4e73df',
      success: '#1cc88a',
      warning: '#f6c23e',
      danger: '#e74a3b',
      info: '#36b9cc',
      gray: '#858796'
    },

    // ============================================================================
    // LINE CHART
    // ============================================================================

    createLineChart(canvasId, data, options = {}) {
      const ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      const config = {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: data.datasets.map((dataset, index) => ({
            label: dataset.label,
            data: dataset.data,
            borderColor: dataset.color || this.getColorByIndex(index),
            backgroundColor: this.hexToRgba(dataset.color || this.getColorByIndex(index), 0.1),
            borderWidth: 2,
            fill: dataset.fill !== undefined ? dataset.fill : true,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: dataset.color || this.getColorByIndex(index),
            pointBorderColor: '#fff',
            pointBorderWidth: 2
          }))
        },
        options: {
          ...this.defaults,
          ...options,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
              },
              ticks: {
                font: {
                  size: 11
                },
                padding: 10
              }
            },
            x: {
              grid: {
                display: false,
                drawBorder: false
              },
              ticks: {
                font: {
                  size: 11
                },
                padding: 10
              }
            }
          }
        }
      };

      return new Chart(ctx, config);
    },

    // ============================================================================
    // BAR CHART
    // ============================================================================

    createBarChart(canvasId, data, options = {}) {
      const ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      const config = {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: data.datasets.map((dataset, index) => ({
            label: dataset.label,
            data: dataset.data,
            backgroundColor: dataset.color || this.getColorByIndex(index),
            borderColor: dataset.color || this.getColorByIndex(index),
            borderWidth: 0,
            borderRadius: 8,
            borderSkipped: false
          }))
        },
        options: {
          ...this.defaults,
          ...options,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
              },
              ticks: {
                font: {
                  size: 11
                },
                padding: 10
              }
            },
            x: {
              grid: {
                display: false,
                drawBorder: false
              },
              ticks: {
                font: {
                  size: 11
                },
                padding: 10
              }
            }
          }
        }
      };

      return new Chart(ctx, config);
    },

    // ============================================================================
    // DOUGHNUT CHART
    // ============================================================================

    createDoughnutChart(canvasId, data, options = {}) {
      const ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      const config = {
        type: 'doughnut',
        data: {
          labels: data.labels,
          datasets: [{
            data: data.values,
            backgroundColor: data.colors || data.labels.map((_, index) => this.getColorByIndex(index)),
            borderWidth: 0,
            borderRadius: 4
          }]
        },
        options: {
          ...this.defaults,
          ...options,
          cutout: '70%',
          plugins: {
            ...this.defaults.plugins,
            legend: {
              ...this.defaults.plugins.legend,
              position: 'right'
            }
          }
        }
      };

      return new Chart(ctx, config);
    },

    // ============================================================================
    // PIE CHART
    // ============================================================================

    createPieChart(canvasId, data, options = {}) {
      const ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      const config = {
        type: 'pie',
        data: {
          labels: data.labels,
          datasets: [{
            data: data.values,
            backgroundColor: data.colors || data.labels.map((_, index) => this.getColorByIndex(index)),
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          ...this.defaults,
          ...options,
          plugins: {
            ...this.defaults.plugins,
            legend: {
              ...this.defaults.plugins.legend,
              position: 'right'
            }
          }
        }
      };

      return new Chart(ctx, config);
    },

    // ============================================================================
    // RADAR CHART
    // ============================================================================

    createRadarChart(canvasId, data, options = {}) {
      const ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      const config = {
        type: 'radar',
        data: {
          labels: data.labels,
          datasets: data.datasets.map((dataset, index) => ({
            label: dataset.label,
            data: dataset.data,
            borderColor: dataset.color || this.getColorByIndex(index),
            backgroundColor: this.hexToRgba(dataset.color || this.getColorByIndex(index), 0.2),
            borderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: dataset.color || this.getColorByIndex(index),
            pointBorderColor: '#fff',
            pointBorderWidth: 2
          }))
        },
        options: {
          ...this.defaults,
          ...options,
          scales: {
            r: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.1)'
              },
              angleLines: {
                color: 'rgba(0, 0, 0, 0.1)'
              },
              ticks: {
                backdropColor: 'transparent',
                font: {
                  size: 10
                }
              }
            }
          }
        }
      };

      return new Chart(ctx, config);
    },

    // ============================================================================
    // AREA CHART (Stacked Line)
    // ============================================================================

    createAreaChart(canvasId, data, options = {}) {
      return this.createLineChart(canvasId, data, {
        ...options,
        scales: {
          y: {
            beginAtZero: true,
            stacked: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              drawBorder: false
            }
          },
          x: {
            stacked: true,
            grid: {
              display: false,
              drawBorder: false
            }
          }
        }
      });
    },

    // ============================================================================
    // HORIZONTAL BAR CHART
    // ============================================================================

    createHorizontalBarChart(canvasId, data, options = {}) {
      const ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      const config = {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: data.datasets.map((dataset, index) => ({
            label: dataset.label,
            data: dataset.data,
            backgroundColor: dataset.color || this.getColorByIndex(index),
            borderColor: dataset.color || this.getColorByIndex(index),
            borderWidth: 0,
            borderRadius: 8,
            borderSkipped: false
          }))
        },
        options: {
          ...this.defaults,
          ...options,
          indexAxis: 'y',
          scales: {
            x: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
              }
            },
            y: {
              grid: {
                display: false,
                drawBorder: false
              }
            }
          }
        }
      };

      return new Chart(ctx, config);
    },

    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================

    getColorByIndex(index) {
      const colorKeys = Object.keys(this.colors);
      return this.colors[colorKeys[index % colorKeys.length]];
    },

    hexToRgba(hex, alpha = 1) {
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      if (!result) return hex;

      const r = parseInt(result[1], 16);
      const g = parseInt(result[2], 16);
      const b = parseInt(result[3], 16);

      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    },

    updateChart(chart, newData) {
      if (!chart) return;

      if (newData.labels) {
        chart.data.labels = newData.labels;
      }

      if (newData.datasets) {
        chart.data.datasets = newData.datasets;
      }

      chart.update();
    },

    destroyChart(chart) {
      if (chart) {
        chart.destroy();
      }
    },

    // ============================================================================
    // PRE-CONFIGURED DASHBOARD CHARTS
    // ============================================================================

    // Health Score Trend Chart
    createHealthScoreTrend(canvasId, data) {
      return this.createLineChart(canvasId, {
        labels: data.dates,
        datasets: [{
          label: 'Health Score',
          data: data.scores,
          color: this.colors.primary,
          fill: true
        }]
      }, {
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function(value) {
                return value + '%';
              }
            }
          }
        }
      });
    },

    // File Types Distribution
    createFileTypesChart(canvasId, data) {
      return this.createDoughnutChart(canvasId, {
        labels: data.types,
        values: data.counts,
        colors: [
          this.colors.primary,
          this.colors.success,
          this.colors.warning,
          this.colors.info,
          this.colors.gray
        ]
      });
    },

    // Violations by Severity
    createViolationsChart(canvasId, data) {
      return this.createBarChart(canvasId, {
        labels: data.severities,
        datasets: [{
          label: 'Violations',
          data: data.counts,
          color: [
            this.colors.danger,
            this.colors.warning,
            this.colors.info
          ]
        }]
      });
    },

    // Complexity Distribution
    createComplexityChart(canvasId, data) {
      return this.createHorizontalBarChart(canvasId, {
        labels: data.categories,
        datasets: [{
          label: 'Number of Files',
          data: data.counts,
          color: this.colors.primary
        }]
      });
    },

    // Code Quality Metrics Radar
    createQualityRadar(canvasId, data) {
      return this.createRadarChart(canvasId, {
        labels: ['Maintainability', 'Complexity', 'Test Coverage', 'Documentation', 'Security'],
        datasets: [{
          label: 'Current',
          data: data.current,
          color: this.colors.primary
        }, {
          label: 'Target',
          data: data.target,
          color: this.colors.success
        }]
      }, {
        scales: {
          r: {
            max: 100
          }
        }
      });
    },

    // Timeline Activity Chart
    createTimelineChart(canvasId, data) {
      return this.createLineChart(canvasId, {
        labels: data.dates,
        datasets: [{
          label: 'Scans',
          data: data.scanCounts,
          color: this.colors.primary,
          fill: false
        }, {
          label: 'Issues Found',
          data: data.issueCounts,
          color: this.colors.danger,
          fill: false
        }, {
          label: 'Issues Fixed',
          data: data.fixedCounts,
          color: this.colors.success,
          fill: false
        }]
      });
    }
  };

  // Expose to window
  window.ChartsModule = ChartsModule;

})();
