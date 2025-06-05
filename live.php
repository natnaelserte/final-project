<?php include('head.php'); ?>
<head>
    <style>
        /* General Body Style */
        body {
            background-color: #eef2f5; /* A slightly off-white/light grey for the page background */
        }

        /* Main Content Container Styling */
        .live-results-page-container { /* This is our new main wrapper */
            max-width: 1200px; /* Adjust as needed, e.g., 960px, 1140px */
            margin: 20px auto; /* Centering the container */
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        /* Keep your existing specific styles */
        .candidate-image-live {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .dashboard-title { /* Not explicitly used in HTML, but kept if needed elsewhere */
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d47a1;
        }

        @media (max-width: 767.98px) {
            .live-results-page-container {
                margin: 10px; /* Less margin on small screens */
                padding: 15px;
            }
            .candidate-image-live {
                width: 35px;
                height: 35px;
            }
            .table-responsive {
                overflow-x: auto;
            }
            .live-candidate-bar .candidate-info {
                flex-direction: column;
                align-items: flex-start;
                width: 100%; /* Allow full width on small screens */
                margin-bottom: 5px;
            }
            .live-candidate-bar .progress-container {
                width: 100%; /* Full width for progress bar */
                margin-top: 5px;
            }
             .live-candidate-bar .vote-count-text {
                width: 100%;
                text-align: left;
                padding-left: 0;
                margin-top: 5px;
            }
            .live-candidate-bar {
                flex-direction: column; /* Stack elements vertically on small screens */
                align-items: flex-start;
            }
        }

        .leading-candidate-table {
            background-color: #e9f5ff;
            font-weight: bold;
        }

        .position-title-table {
            color: #063d78;
            font-weight: 700;
            font-size: 1.6em; /* Adjusted size */
            text-align: center;
            margin-bottom: 15px; /* Adjusted margin */
        }

        /* Styles for the report-like bar graphs */
        .live-results-position-block {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px; /* Adjusted margin */
            box-shadow: 0 5px 10px rgba(0,0,0,0.06); /* Softer shadow */
        }

        .live-results-position-block h4.position-title-graph {
            text-align: center;
            color: #343a40; /* Darker grey */
            font-weight: 600;
            margin-bottom: 20px; /* Adjusted margin */
            font-size: 1.4rem; /* Adjusted size */
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .live-candidate-bar {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .live-candidate-bar:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .live-candidate-bar .candidate-info {
            display: flex;
            align-items: center;
            width: 220px; /* Adjusted width */
            min-width: 200px;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .live-candidate-bar .candidate-name {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem; /* Slightly smaller */
        }

        .live-candidate-bar .progress-container {
            flex-grow: 1;
            position: relative;
        }

        .live-candidate-bar .progress-track {
            height: 26px; /* Adjusted height */
            background-color: #e9ecef;
            border-radius: 13px; /* Half of height */
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .live-candidate-bar .progress-fill {
            height: 100%;
            border-radius: 13px;
            transition: width 0.6s cubic-bezier(0.25, 0.1, 0.25, 1);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px; /* Adjusted padding */
            color: white;
            font-weight: bold;
            font-size: 0.8rem; /* Adjusted size */
            white-space: nowrap;
        }
        .live-candidate-bar .progress-fill.low-percentage span {
            position: absolute;
            right: 8px;
            color: #555;
        }

        .live-candidate-bar .vote-count-text {
            width: 90px; /* Adjusted width */
            text-align: right;
            font-size: 0.85rem; /* Adjusted size */
            color: #555;
            padding-left: 15px;
            flex-shrink: 0;
        }

        /* Bar Colors */
        .progress-fill.color-1 { background-color: #dc3545; } /* Red */
        .progress-fill.color-2 { background-color: #28a745; } /* Green */
        .progress-fill.color-3 { background-color: #007bff; } /* Blue */
        .progress-fill.color-4 { background-color: #ffc107; } /* Yellow */
        .progress-fill.color-5 { background-color: #6f42c1; } /* Purple */
        .progress-fill.color-default { background-color: #adb5bd; } /* Lighter Grey for 0 votes */


        /* Summary Box Styles */
        .summary-box {
          padding: 15px;
          border-radius: 8px;
          box-shadow: 0 3px 8px rgba(0, 0, 0, 0.07);
          margin-bottom: 15px;
          text-align: center;
          transition: transform 0.2s ease-in-out;
        }
        .summary-box:hover {
            transform: translateY(-3px);
        }
        .summary-box h6 {
          font-size: 0.9rem;
          margin-bottom: 5px;
          font-weight: 500;
          color: rgba(255,255,255,0.85);
        }
        .summary-box h4 {
          font-size: 1.6rem;
          margin: 0;
          font-weight: 700;
        }
        /* Specific Summary Box Colors (adjust if needed) */
        .summary-box.candidates-total { background-color: #90D1CA; color: #333; } /* Changed to teal */
        .summary-box.votes-total { background-color: #75B5AE; color: #333; }    /* Darker teal */
        .summary-box.positions-total { background-color: #A8DCD6; color: #333; } /* Lighter teal */

    </style>
</head>
<?php include('view_banner.php'); ?>
<body>
    <div class="live-results-page-container">
        <h2 class="text-center mt-3 mb-4" style="color: #063d78; font-weight: 700;">Real-Time Voting Results</h2>

        <!-- Dashboard Summary Section -->
        <div class="container mb-4"> 
          <h3 class="text-center mb-4" style="color: #343a40; font-weight:600;">📊 Dashboard Summary</h3>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-3"> 
                  <div class="summary-box candidates-total">
                    <h6>Total Candidates</h6>
                    <h4 id="total-candidates">--</h4>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                  <div class="summary-box votes-total">
                    <h6>Total Votes Cast</h6>
                    <h4 id="total-votes">--</h4>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                  <div class="summary-box positions-total">
                    <h6>Total Positions</h6>
                    <h4 id="total-positions">--</h4>
                  </div>
                </div>
            </div>
        </div>
        <hr class="my-4">


        <!-- Live Report-Style Bar Graph Results Section -->
        <div id="live-report-results-container" class="mt-4">
            <h3 class="text-center mb-4" style="color: #343a40; font-weight:600;">Live Vote Counts by Position</h3>

            <p class="text-center text-muted">Loading report view...</p>
        </div>

        <hr class="my-4">
        <!-- Live Table Results Section (Optional) -->
        <div id="live-table-results-container" class="table-responsive mb-3">
            <h3 class="text-center mb-4" style="color: #343a40; font-weight:600;">Detailed Table View (Optional)</h3>
            <p class="text-center text-muted">Loading table results...</p>
        </div>

    </div>
    <?php include('script.php'); ?>

<script>
    const ADMIN_IMG_PATH = 'admin2/';

    function fetchLiveResults() {
        fetch('real_timedata.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (!data || typeof data !== 'object') { // Expecting an object, if it was an array before, adjust
                    console.error('Data is not in the expected format:', data);
                    // If real_timedata.php sends an array directly, use: if (!Array.isArray(data))
                    throw new Error('Received invalid data format from server.');
                }
                // Assuming data is an array of candidates
                const candidateResults = Array.isArray(data) ? data : (data.results || []); // Adapt if data is {results: [...]}

                updateDashboardSummary(candidateResults);
                displayTableResults(candidateResults);
                displayReportStyleResults(candidateResults);
            })
            .catch(error => {
                console.error('Error fetching live results:', error);
                const errorMsg = '<p class="text-danger text-center">Error loading live results. Please try again later.</p>';
                if(document.getElementById('live-table-results-container')) document.getElementById('live-table-results-container').innerHTML = errorMsg;
                if(document.getElementById('live-report-results-container')) document.getElementById('live-report-results-container').innerHTML = errorMsg;
            });
    }

    function updateDashboardSummary(results) {
        if (!Array.isArray(results)) return; // Ensure results is an array

        const totalCandidates = results.length;
        const totalVotes = results.reduce((sum, c) => sum + parseInt(c.vote_count || 0, 10), 0);
        const uniquePositions = [...new Set(results.map(c => c.position_name))];

        document.getElementById("total-candidates").innerText = totalCandidates;
        document.getElementById("total-votes").innerText = totalVotes;
        document.getElementById("total-positions").innerText = uniquePositions.length;
    }

    function displayTableResults(results) {
        if (!Array.isArray(results) || !document.getElementById('live-table-results-container')) return;

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
                candidates.sort((a, b) => parseInt(b.vote_count || 0, 10) - parseInt(a.vote_count || 0, 10));

                tableHTML += `<div class="mb-4 live-results-position-block">
                                <h4 class="position-title-table">${position}</h4>
                                <table class="table table-striped table-hover table-sm"> 
                                    <thead class="table-light">
                                        <tr><th>Rank</th><th>Candidate</th><th>Votes</th></tr>
                                    </thead>
                                    <tbody>`;
                candidates.forEach((candidate, index) => {
                    const leadingClass = index === 0 && candidate.vote_count > 0 ? 'leading-candidate-table' : '';
                    const imgSrc = candidate.img ? ADMIN_IMG_PATH + candidate.img : ADMIN_IMG_PATH + 'default_avatar.png';
                    tableHTML += `<tr class="${leadingClass}">
                                    <td class="fw-bold">${index + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${imgSrc}" alt="${candidate.firstname} ${candidate.lastname}" class="candidate-image-live">
                                            <span>${candidate.firstname} ${candidate.lastname}</span>
                                        </div>
                                    </td>
                                    <td>${candidate.vote_count || 0}</td>
                                  </tr>`;
                });
                tableHTML += `</tbody></table>`;
                if (candidates.length > 0 && candidates[0].vote_count > 0) {
                    tableHTML += `<div class="text-center p-2" style="background-color: #e9f5ff; border-radius: 0 0 8px 8px;">
                                    <strong style="color: #063d78;">Current Leader: ${candidates[0].firstname} ${candidates[0].lastname}</strong>
                                  </div>`;
                }
                tableHTML += `</div>`;
            }
        }
        document.getElementById('live-table-results-container').innerHTML = tableHTML || '<p class="text-center text-muted">No results for table view.</p>';
    }


    function displayReportStyleResults(results) {
        if (!Array.isArray(results) || !document.getElementById('live-report-results-container')) return;

        const groupedResults = {};
        results.forEach(candidate => {
            if (!groupedResults[candidate.position_name]) {
                groupedResults[candidate.position_name] = { candidates: [], total_votes_position: 0 };
            }
            const voteCount = parseInt(candidate.vote_count || 0, 10);
            groupedResults[candidate.position_name].candidates.push({...candidate, vote_count: voteCount});
            groupedResults[candidate.position_name].total_votes_position += voteCount;
        });

        let reportHTML = '';
        const barColors = ['#90D1CA', '#75B5AE', '#A8DCD6', '#ffc107', '#6f42c1', '#fd7e14', '#20c997']; // Updated to teal colors
        let globalColorIndex = 0; // Use a global index for varied colors across all positions

        for (const positionName in groupedResults) {
            if (groupedResults.hasOwnProperty(positionName)) {
                const positionData = groupedResults[positionName];
                const candidates = positionData.candidates;
                const totalPositionVotes = positionData.total_votes_position;

                candidates.sort((a, b) => b.vote_count - a.vote_count);

                reportHTML += `<div class="live-results-position-block">
                                <h4 class="position-title-graph">${positionName}</h4>`;

                if (candidates.length === 0) {
                     reportHTML += `<p class="text-center text-muted">No candidates for this position.</p>`;
                } else if (totalPositionVotes === 0 && candidates.length > 0) {
                    reportHTML += `<p class="text-center text-muted">No votes cast for this position yet.</p>`;
                    candidates.forEach((candidate) => { // Still list candidates
                        const imgSrc = candidate.img ? ADMIN_IMG_PATH + candidate.img : ADMIN_IMG_PATH + 'default_avatar.png';
                        reportHTML += `
                            <div class="live-candidate-bar">
                                <div class="candidate-info">
                                    <img src="${imgSrc}" alt="${candidate.firstname} ${candidate.lastname}" class="candidate-image-live">
                                    <span class="candidate-name">${candidate.firstname} ${candidate.lastname}</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-track">
                                        <div class="progress-fill color-default" style="width: 0%;">
                                            <span>0%</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="vote-count-text">0 votes</span>
                            </div>`;
                    });
                } else {
                    candidates.forEach((candidate) => {
                        const percent = totalPositionVotes > 0 ? ((candidate.vote_count / totalPositionVotes) * 100) : 0;
                        const currentBarColor = barColors[globalColorIndex % barColors.length];
                        const imgSrc = candidate.img ? ADMIN_IMG_PATH + candidate.img : ADMIN_IMG_PATH + 'default_avatar.png';
                        const lowPercentageClass = percent < 15 && percent > 0 ? 'low-percentage' : '';

                        reportHTML += `
                            <div class="live-candidate-bar">
                                <div class="candidate-info">
                                    <img src="${imgSrc}" alt="${candidate.firstname} ${candidate.lastname}" class="candidate-image-live">
                                    <span class="candidate-name">${candidate.firstname} ${candidate.lastname}</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-track">
                                        <div class="progress-fill ${lowPercentageClass}" style="width:${percent.toFixed(1)}%; background-color:${currentBarColor};">
                                            ${percent > 0 ? `<span>${percent.toFixed(1)}%</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                                <span class="vote-count-text">${candidate.vote_count} votes</span>
                            </div>`;
                        globalColorIndex++;
                    });
                }
                reportHTML += `</div>`;
            }
        }
        document.getElementById('live-report-results-container').innerHTML = reportHTML || '<p class="text-center text-muted">No results to display.</p>';
    }

    fetchLiveResults();
    setInterval(fetchLiveResults, 7000);
</script>
<?php include('footer.php'); ?>
</body>
</html>
