function showTab(tabId) {
    document.getElementById('overviewTab').style.display = tabId === 'overviewTab' ? 'block' : 'none';
    document.getElementById('userDetailsTab').style.display = tabId === 'userDetailsTab' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {


    function createCharts() {
        // Overall Model Performance Chart
        const overallCtx = document.getElementById('overallPerformanceChart');
        if (overallCtx) {
            new Chart(overallCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: models,
                    datasets: [{
                        label: 'Average Rating',
                        data: models.map(model => modelPerformance[model].average),
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Overall Model Performance'
                        }
                    }
                }
            });
        }

        // Model Performance per Image Chart
        const perImageCtx = document.getElementById('performancePerImageChart');
        if (perImageCtx) {
            new Chart(perImageCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: Object.keys(surveyData).map(index => `Image ${parseInt(index) + 1}`),
                    datasets: models.map((model, index) => ({
                        label: model,
                        data: Object.values(surveyData).map(data => data.ratings[model]),
                        borderColor: `hsl(${index * 360 / models.length}, 70%, 50%)`,
                        fill: false
                    }))
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Model Performance per Image'
                        }
                    }
                }
            });
        }

        // Participation Chart
        const partCtx = document.getElementById('participationChart');
        if (partCtx) {
            new Chart(partCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: Object.keys(surveyData).map(index => `Image ${parseInt(index) + 1}`),
                    datasets: [{
                        data: Object.values(surveyData).map(data => data.count),
                        backgroundColor: Object.keys(surveyData).map((_, index) => 
                            `hsl(${index * 360 / Object.keys(surveyData).length}, 70%, 70%)`
                        )
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Participation Count per Image'
                        }
                    }
                }
            });
        }
    }

    function populateDetailedPerformance() {
        const detailedPerformance = document.getElementById('detailedPerformance');
        if (detailedPerformance) {
            let tableHTML = '<table class="w-full text-left border-collapse"><thead><tr><th>Image</th>';
            models.forEach(model => {
                tableHTML += `<th>${model}</th>`;
            });
            tableHTML += '<th>Participants</th></tr></thead><tbody>';
            for (const [index, data] of Object.entries(surveyData)) {
                tableHTML += `<tr>
                    <td>Image ${parseInt(index) + 1}</td>
                    ${models.map(model => `<td>${data.ratings[model].toFixed(2)}</td>`).join('')}
                    <td>${data.count}</td>
                </tr>`;
            }
            tableHTML += '</tbody></table>';
            detailedPerformance.innerHTML = tableHTML;
        }
    }
    function createUserAverageHistogram() {
        const ctx = document.getElementById('userAverageHistogram');
        if (ctx) {
            const data = userAveragesForHistogram;
            const binCount = 10;
            const min = Math.min(...data);
            const max = Math.max(...data);
            const binSize = (max - min) / binCount;

            const bins = Array(binCount).fill(0);
            data.forEach(value => {
                const binIndex = Math.min(Math.floor((value - min) / binSize), binCount - 1);
                bins[binIndex]++;
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: bins.map((_, i) => `${(min + i * binSize).toFixed(2)} - ${(min + (i + 1) * binSize).toFixed(2)}`),
                    datasets: [{
                        label: 'Number of Users',
                        data: bins,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Users'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Average Score Range'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribution of User Average Scores'
                        }
                    }
                }
            });
        }
    }

    function populateUserSelect() {
        const userSelect = document.getElementById('userSelect');
        if (userSelect) {
            // Sort users by timestamp (most recent first)
            const sortedUsers = Object.entries(userAverages).sort((a, b) => b[1].timestamp - a[1].timestamp);
            
            sortedUsers.forEach(([userKey, userData]) => {
                const [ip, agent] = userKey.split('|');
                const option = document.createElement('option');
                option.value = userKey;
                option.textContent = `${ip} - ${agent.substring(0, 30)}... (${new Date(userData.timestamp * 1000).toLocaleString()})`;
                userSelect.appendChild(option);
            });
        }
    }

    function populateUserDetails(selectedUser = '') {
        const tbody = document.getElementById('userDetailsBody');
        if (tbody) {
            tbody.innerHTML = '';
            userDetails.forEach(user => {
                const userKey = `${user.ip_address}|${user.user_agent}`;
                if (selectedUser === '' || userKey === selectedUser) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${user.timestamp}</td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${user.image_index}</td>
                        ${models.map((_, index) => `<td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${user['rating_' + (index + 1)]}</td>`).join('')}
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${user.user_agent}</td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${user.ip_address}</td>
                    `;
                    tbody.appendChild(row);
                }
            });
        }
    }

    function updateUserAverage(selectedUser) {
        const userAverageElement = document.getElementById('userAverage');
        if (userAverageElement) {
            if (selectedUser in userAverages) {
                userAverageElement.textContent = `Average Score: ${userAverages[selectedUser].toFixed(2)}`;
            } else {
                userAverageElement.textContent = 'No data available';
            }
        }
    }

    // Initialize tables, dropdowns, and charts
    createCharts();
    createUserAverageHistogram();
    populateDetailedPerformance();
    populateUserSelect();
    populateUserDetails();

    // Add event listener for user selection
    const userSelect = document.getElementById('userSelect');
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            const selectedUser = this.value;
            populateUserDetails(selectedUser);
            updateUserAverage(selectedUser);
        });
    }

    // Add event listeners for tab switching
    document.querySelectorAll('a[onclick^="showTab"]').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('onclick').match(/'(.+)'/)[1];
            showTab(tabId);
        });
    });

    // Show the overview tab by default
    showTab('overviewTab');
});