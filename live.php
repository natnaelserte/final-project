
<?php include('head.php'); ?>
<head>
    <style>
        .candidate-image-live { /* Renamed to avoid conflict if .candidate-image is global */
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px; /* Added margin for spacing */
        }
        .dashboard-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d47a1;
        }
        .chart-container-live { /* Renamed for clarity */
            position: relative;
            height: auto;
            margin-bottom: 20px; /* Added margin */
        }

        @media (max-width: 767.98px) {
            .candidate-image-live {
                width: 40px;
                height: 40px;
            }
            .table-responsive {
                overflow-x: auto;
            }
        }

        /* Style for the leading candidate in the table view */
        .leading-candidate-table {
            background-color: rgb(147, 139, 179);
            font-weight: bold;
            border-radius: 5px;
        }

        /* Styling for position titles in table view */
        .position-title-table {
            color: rgb(6, 61, 120);
            font-weight: 700;
            font-size: 1.8em;
            text-align: center;
            margin-bottom: 20px;
        }

        .container_live_page { /* Renamed for clarity */
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Adjusted shadow */
        }

        /* Styles for the report-like bar graphs */
        .live-candidate-results {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px; /* Spacing between positions */
        }
        .live-candidate-results .candidate-bar-container {
            margin-bottom: 15px; /* Spacing between candidates */
        }
        .live-candidate-results strong {
            display: block; /* Make strong take full width for better layout */
            margin-bottom: 3px;
        }
        .live-candidate-results .vote-details {
            float: right;
            font-size: 0.9em;
        }
        .live-candidate-results .progress-bar-custom {
            position: relative;
            height: 30px;
            background: #e6e6e6;
            border-radius: 15px; /* Smoother radius */
            margin-top: 5px;
            overflow: hidden; /* Ensure inner bar stays within bounds */
        }
        .live-candidate-results .progress-bar-fill {
            height: 100%;
            border-radius: 15px;
            transition: width 0.5s ease-in-out; /* Smooth transition for width changes */
        }
        .live-candidate-results .candidate-avatar-on-bar {
            position: absolute;
            top: -5px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
            object-fit: cover;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
            transition: left 0.5s ease-in-out; /* Smooth transition for position */
        }

        /* Summary Box Styles (from your example) */
        .summary-box {
          padding: 10px 15px; /* Adjusted padding */
          border-radius: 8px;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
          margin-bottom: 10px; /* Spacing for mobile */
        }
        .summary-box h6 {
          font-size: 0.9rem; /* Slightly smaller */
          margin-bottom: 3px;
        }
        .summary-box h4 {
          font-size: 1.3rem; /* Slightly smaller */
          margin: 0;
        }
    </style>
</head>
<?php include('view_banner.php'); ?>
<body>
    <?php // include('view_banner.php'); // Uncomment if you use this ?>
    <div class="container_live_page">
        <h2 class="text-center mt-4 mb-4">Real-Time Voting Results</h2>

        <!-- Live Table Results Section (Optional - Keep if you want both table and graph views) -->
        <div id="live-table-results-container" class="table-responsive mb-5">
            <!-- Table-based voting results will be displayed here -->
            <p class="text-center">Loading table results...</p>
        </div>
        <hr>

        <!-- Dashboard Summary Section -->
        <div class="container mb-5">
          <h2 class="text-center mb-4">📊 Dashboard Summary</h2>
            <div class="row text-center justify-content-center">
                <div class="col-md-4 col-sm-6 col-12">
                  <div class="summary-box" style="background-color: #0d1b2a; color: white;">
                    <h6>Total Candidates</h6>
                    <h4 id="total-candidates">--</h4>
                  </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                  <div class="summary-box" style="background-color: #0d1b2a; color: white;">
                    <h6>Total Votes Cast</h6>
                    <h4 id="total-votes">--</h4>
                  </div>
                </div>
                <div class="col-md-4 col-sm-12 col-12">
                  <div class="summary-box" style="background-color: #0d1b2a; color: white;">
                    <h6>Total Positions</h6>
                    <h4 id="total-positions">--</h4>
                  </div>
                </div>
            </div>
        </div>

        <!-- Live Report-Style Bar Graph Results Section -->
        <div id="live-report-results-container" class="mt-4">
            <h3 class="text-center mb-3">Live Vote Counts (Report View)</h3>
            <!-- Report-style bar graphs will be displayed here -->
            <p class="text-center">Loading report view...</p>
        </div>

    </div>

    <?php // include('footer.php'); // Uncomment if you use this ?>
    <?php include('script.php'); // General scripts, ensure jQuery is loaded if not already by head.php ?>

<script>
    const ADMIN_IMG_PATH = 'admin2/'; // Path to your admin images folder

    function fetchLiveResults() {
        fetch('real_timedata.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (!Array.isArray(data)) {
                    console.error('Data is not an array:', data);
                    throw new Error('Received invalid data format from server.');
                }
                updateDashboardSummary(data);
                displayTableResults(data); // Optional: if you keep the table view
                displayReportStyleResults(data);
            })
            .catch(error => {
                console.error('Error fetching live results:', error);
                const errorMsg = '<p class="text-danger text-center">Error loading live results. Please try again later.</p>';
                document.getElementById('live-table-results-container').innerHTML = errorMsg;
                document.getElementById('live-report-results-container').innerHTML = errorMsg;
            });
    }

    function updateDashboardSummary(results) {
        const totalCandidates = results.length;
        const totalVotes = results.reduce((sum, c) => sum + parseInt(c.vote_count, 10), 0);
        const uniquePositions = [...new Set(results.map(c => c.position_name))];

        document.getElementById("total-candidates").innerText = totalCandidates;
        document.getElementById("total-votes").innerText = totalVotes;
        document.getElementById("total-positions").innerText = uniquePositions.length;
    }

    // Optional: Function to display results in a table format
    function displayTableResults(results) {
        const groupedResults = {};
        results.forEach(candidate => {
            if (!groupedResults[candidate.position_name]) {
                groupedResults[candidate.position_name] = [];
            }
            groupedResults[candidate.position_name].push(candidate);
        });

        let tableHTML = '';
        for (const position in groupedResults) {
            if (groupedResults.hasOwnProperty(position)) {
                const candidates = groupedResults[position];
                candidates.sort((a, b) => parseInt(b.vote_count, 10) - parseInt(a.vote_count, 10));

                tableHTML += `<div class="mb-4" style="border: 1px solid rgba(30, 110, 157, 0.3); border-radius:10px; padding:15px;">
                                <h3 class="position-title-table">${position}</h3>
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead class="thead-dark">
                                        <tr><th>Rank</th><th>Candidate</th><th>Votes</th></tr>
                                    </thead>
                                    <tbody>`;
                candidates.forEach((candidate, index) => {
                    const leadingClass = index === 0 ? 'leading-candidate-table' : '';
                    const imgSrc = candidate.img ? ADMIN_IMG_PATH + candidate.img : 'admin2/default_avatar.png'; // Fallback image
                    tableHTML += `<tr class="${leadingClass}">
                                    <td>${index + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${imgSrc}" alt="${candidate.firstname} ${candidate.lastname}" class="candidate-image-live">
                                            <span>${candidate.firstname} ${candidate.lastname}</span>
                                        </div>
                                    </td>
                                    <td>${candidate.vote_count}</td>
                                  </tr>`;
                });
                tableHTML += `</tbody></table>`;
                if (candidates.length > 0) {
                    tableHTML += `<div class="text-center leading-candidate-table p-2">
                                    <span>Current Leader: ${candidates[0].firstname} ${candidates[0].lastname}</span>
                                  </div>`;
                }
                tableHTML += `</div>`;
            }
        }
        document.getElementById('live-table-results-container').innerHTML = tableHTML || '<p class="text-center">No results to display in table view.</p>';
    }


    function displayReportStyleResults(results) {
        const groupedResults = {};
        results.forEach(candidate => {
            if (!groupedResults[candidate.position_name]) {
                groupedResults[candidate.position_name] = { candidates: [], total_votes: 0 };
            }
            const voteCount = parseInt(candidate.vote_count, 10);
            groupedResults[candidate.position_name].candidates.push({...candidate, vote_count: voteCount});
            groupedResults[candidate.position_name].total_votes += voteCount;
        });

        let reportHTML = '';
        for (const positionName in groupedResults) {
            if (groupedResults.hasOwnProperty(positionName)) {
                const positionData = groupedResults[positionName];
                const candidates = positionData.candidates;
                const totalPositionVotes = positionData.total_votes;

                candidates.sort((a, b) => b.vote_count - a.vote_count); // Sort by votes descending

                reportHTML += `<div class="live-candidate-results">
                                <h4 class="text-primary text-center">${positionName}</h4>`;

                if (candidates.length === 0) {
                    reportHTML += '<p class="text-center">No candidates or votes for this position yet.</p>';
                } else {
                    candidates.forEach((candidate, index) => {
                        const percent = totalPositionVotes > 0 ? ((candidate.vote_count / totalPositionVotes) * 100).toFixed(2) : 0;
                        const barColor = index === 0 ? '#d9534f' : '#5cb85c'; // Red for leader, green for others
                        const imgSrc = candidate.img ? ADMIN_IMG_PATH + candidate.img : 'admin2/default_avatar.png'; // Fallback image

                        reportHTML += `
                            <div class="candidate-bar-container">
                                <strong>${index + 1}. ${candidate.firstname} ${candidate.lastname}</strong>
                                <span class="vote-details">${candidate.vote_count} votes | ${candidate.party || 'N/A'} | ${percent}%</span>
                                <div class="progress-bar-custom">
                                    <div class="progress-bar-fill" style="width:${percent}%; background-color:${barColor};"></div>
                                    <img src="${imgSrc}" alt="${candidate.firstname}" class="candidate-avatar-on-bar" style="left: calc(${percent}% - 20px);">
                                </div>
                            </div>`;
                    });
                }
                reportHTML += `</div>`; // Close live-candidate-results
            }
        }
        document.getElementById('live-report-results-container').innerHTML = reportHTML || '<p class="text-center">No results to display in report view.</p>';
    }

    // Initial fetch
    fetchLiveResults();

    // Set interval for live updates (e.g., every 5-10 seconds)
    setInterval(fetchLiveResults, 7000); // Update every 7 seconds
</script>
<?php include('footer.php'); ?>
<?php include('script.php'); ?>
</body>
</html>
<script>
    function fetchResults() {
        fetch('real_timedata.php')
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Error fetching results:', error);
                document.getElementById('results-container').innerHTML = '<p class="text-danger">Error loading results.</p>';
            });
    }

    function displayResults(results) {
        // Group results by position
        const groupedResults = {};
        results.forEach(candidate => {
            if (!groupedResults[candidate.position_name]) {
                groupedResults[candidate.position_name] = [];
            }
            groupedResults[candidate.position_name].push(candidate);
        });

        let resultsHTML = '';

        // Iterate through each position and create a separate table
        for (const position in groupedResults) {
            if (groupedResults.hasOwnProperty(position)) {
                const candidates = groupedResults[position];

                // Sort candidates based on vote count (highest first)
                candidates.sort((a, b) => b.vote_count - a.vote_count);

                // Adding the position title
                resultsHTML += `
                    <div style=" solid rgba(30, 110, 157, 0.8); border-radius:20px; padding:10px; width:90%;">
                        <h3 class="position-title">${position}</h3>`; // Position Title

                resultsHTML += `
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Candidate</th>
                                <th>Votes</th>
                            </tr>
                        </thead>
                        <tbody>`;

                let positionRank = 1;
                let leadingCandidate = candidates[0]; // The first candidate in the sorted list is the leader

                candidates.forEach(candidate => {
                    // Highlight the leading candidate
                    const leadingClass = positionRank === 1 ? 'leading-candidate' : '';

                    resultsHTML += `
                        <tr class="${leadingClass}">
                            <td>${positionRank}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="admin2/${candidate.img}" alt="${candidate.firstname} ${candidate.lastname}" class="candidate-image mr-2">
                                    <span>${candidate.firstname} ${candidate.lastname}</span>
                                </div>
                            </td>
                            <td>${candidate.vote_count}</td>
                        </tr>`;

                    positionRank++;
                });

                resultsHTML += `</tbody></table>`;

                // Display the leading candidate's name below the table
                resultsHTML += `
                    <div class="text-center leading-candidate">
                        <span>Current Leader: ${leadingCandidate.firstname} ${leadingCandidate.lastname}</span>
                    </div>`;

                resultsHTML += `</div><br>`; // Close table and add a line break
            }
        }

        document.getElementById('results-container').innerHTML = resultsHTML;
    }

    // Fetch results initially
    fetchResults();

    // Update results every 5 seconds
    setInterval(fetchResults, 5000);
</script>
