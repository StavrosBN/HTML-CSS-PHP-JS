function renderCharts(avgCompletionTime, avgGrade, totalTheses) {
    const completionCtx = document.getElementById('completionChart').getContext('2d');
    new Chart(completionCtx, {
        type: 'bar',
        data: {
            labels: ['Average Completion Time (Days)'],
            datasets: [{
                label: 'Days',
                data: [avgCompletionTime],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Average: ${context.raw.toFixed(2)} days`;
                        }
                    }
                }
            }
        }
    });

    // Average Grade Chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: ['Average Grade'],
            datasets: [{
                label: 'Grade',
                data: [avgGrade],
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Average: ${context.raw.toFixed(2)}`;
                        }
                    }
                }
            }
        }
    });

    // Total Count of Theses Chart
    const countCtx = document.getElementById('countChart').getContext('2d');
    new Chart(countCtx, {
        type: 'bar',
        data: {
            labels: ['Total Theses'],
            datasets: [{
                label: 'Count',
                data: [totalTheses],
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Total: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}
