<?php include('head.php'); ?>
<head>
    <style>
        .candidate-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;a
            object-fit: cover;
        }
        .dashboard-title {
  font-size: 1.2rem;
  font-weight: bold;
  color: #0d47a1;
}
.chart-container {
  position: relative;
  height: auto;
}


        /* Add a media query for smaller screens */
        @media (max-width: 767.98px) {
            .candidate-image {
                width: 40px;
                height: 40px;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }

        /* Style for the leading candidate */
        .leading-candidate {
            background-color:rgb(147, 139, 179); /* Green background for leading candidate */
         
            font-weight: bold;
            border-radius: 5px;
        }

        /* Styling for position titles */
        .position-title {
            color:rgb(6, 61, 120);
            font-weight: 700;
            font-size: 1.8em;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Styling for the container */
        .container_live {
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 6);
        }
    </style>
</head>

<body>
    <?php include('view_banner.php'); ?>
    <div class="container_live">
        <h2 class="text-center mt-4">Real-Time Voting Results</h2>
        <div id="results-container" class="table-responsive">
            <!-- Voting results will be displayed here -->
        </div>
        <!-- Dashboard Section Start -->
<div class="container m-6">
  <h2 class="text-center m-6">📊 Dashboard Summary</h2>
<!-- 📦 Voting Summary Boxes -->
<div class="container my-4">
  <div class="row text-center justify-content-center m-6">
    <!-- Total Candidates -->
    <div class="col-12  mb-2">
      <div class="summary-box" style="background-color: #0d1b2a; color: white;">
        <h6>Total Candidates</h6>
        <h4 id="total-candidates">--</h4>
      </div>
    </div>

    <!-- Total Votes -->
    <div class="col-12  mb-2">
      <div class="summary-box" style="background-color: #0d1b2a; color: white;">
        <h6>Total Votes</h6>
        <h4 id="total-votes">--</h4>
      </div>
    </div>

    <!-- Total Positions -->
    <div class="col-12  mb-2">
      <div class="summary-box" style="background-color: #0d1b2a; color: white;">
        <h6>Total Positions</h6>
        <h4 id="total-positions">--</h4>
      </div>
    </div>
  </div>
</div>

<style>
.summary-box {
  padding: 15px;              /* Reduced padding */
  border-radius: 10px;        /* Slightly smaller corners */
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}
.summary-box h6 {
  font-size: 0.95rem;
  margin-bottom: 5px;
}
.summary-box h4 {
  font-size: 1.4rem;
  margin: 0;
}
</style>


  <div class="chart-container mb-6">
    <canvas id="votesByPosition" height="100"></canvas>
  </div>

  <div class="chart-container mb-5">
    <canvas id="votesByCandidate" height="100"></canvas>
  </div>
</div>
<!-- Dashboard Section End -->

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function loadDashboard() {
  fetch('real_timedata.php')
    .then(res => res.json())
    .then(data => {
      const totalCandidates = data.length;
      const totalVotes = data.reduce((sum, c) => sum + c.vote_count, 0);
      const uniquePositions = [...new Set(data.map(c => c.position_name))];

      document.getElementById("total-candidates").innerText = totalCandidates;
      document.getElementById("total-votes").innerText = totalVotes;
      document.getElementById("total-positions").innerText = uniquePositions.length;

      // Clear old charts if they exist
      if (window.positionChart) window.positionChart.destroy();
      if (window.candidateChart) window.candidateChart.destroy();

      // Votes by Position
      const votesByPosition = {};
      data.forEach(c => {
        votesByPosition[c.position_name] = (votesByPosition[c.position_name] || 0) + c.vote_count;
      });

      const positionCtx = document.getElementById("votesByPosition").getContext("2d");
      window.positionChart = new Chart(positionCtx, {
        type: 'bar',
        data: {
          labels: Object.keys(votesByPosition),
          datasets: [{
            label: 'Votes per Position',
            backgroundColor: '#1e88e5',
            data: Object.values(votesByPosition)
          }]
        },
        options: {
          responsive: true,
          scales: { y: { beginAtZero: true } }
        }
      });

      // Top Candidates
      const topCandidates = [...data].sort((a, b) => b.vote_count - a.vote_count).slice(0, 10);
      const candidateCtx = document.getElementById("votesByCandidate").getContext("2d");
      window.candidateChart = new Chart(candidateCtx, {
        type: 'bar',
        data: {
          labels: topCandidates.map(c => `${c.firstname} ${c.lastname}`),
          datasets: [{
            label: 'Top 10 Candidates',
            backgroundColor: '#43a047',
            data: topCandidates.map(c => c.vote_count)
          }]
        },
        options: {
          responsive: true,
          indexAxis: 'y',
          scales: { x: { beginAtZero: true } }
        }
      });
    });
}

loadDashboard();
setInterval(loadDashboard, 10000); // Update every 10 seconds
</script>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function loadDashboardData() {
  fetch('real_timedata.php')
    .then(res => res.json())
    .then(data => {
      // 🧮 Stats
      const totalCandidates = data.length;
      const totalVotes = data.reduce((sum, c) => sum + c.vote_count, 0);
      const positions = [...new Set(data.map(c => c.position_name))];

      document.getElementById("total-candidates").innerText = totalCandidates;
      document.getElementById("total-votes").innerText = totalVotes;
      document.getElementById("total-positions").innerText = positions.length;

      // 🎯 Top 10 Candidates
      const top10 = [...data].sort((a, b) => b.vote_count - a.vote_count).slice(0, 10);
      const candidateNames = top10.map(c => `${c.firstname} ${c.lastname}`);
      const voteCounts = top10.map(c => c.vote_count);

      // Destroy old chart if exists
      if (window.topChart) window.topChart.destroy();

      const ctx = document.getElementById("topCandidatesChart").getContext("2d");
      window.topChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: candidateNames,
          datasets: [{
            label: 'Vote Count',
            data: voteCounts,
            backgroundColor: '#43a047'
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          scales: {
            x: {
              beginAtZero: true,
              title: { display: true, text: 'Votes' }
            },
            y: {
              title: { display: true, text: 'Candidate' }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    });
}

loadDashboardData();
setInterval(loadDashboardData, 10000); // Refresh every 10s
</script>

        
    </div>

    <?php include('footer.php'); ?>
    <?php include('script.php'); ?>
</body>

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
