<?php include('head.php'); ?>
<head>
    <style>
        .candidate-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
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
    </style>
</head>

<body>
    <?php include('view_banner.php'); ?>
    <div class="container_live">
        <h2 class="text-center mt-4">Real-Time Voting Results</h2>
        <div id="results-container" class="table-responsive">
            <!-- Voting results will be displayed here -->
        </div>
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

                resultsHTML += `<div style="border:2px solid rgba(30, 110, 157, 0.8); border-radius:15px; padding:10px; width:90%;"><h3 style="color:green; font:bold; text-align:center">${position}</h3>`; // Position Title

                resultsHTML += `<table class="table table-striped table-bordered table-hover " >
                    <thead class="thead-dark">
                        <tr>
                            <th>Rank</th>
                            <th>Candidate</th>
                            <th>Votes</th>
                        </tr>
                    </thead>
                    <tbody>`;

                let positionRank = 1;

                candidates.forEach(candidate => {
                    resultsHTML += `
                        <tr>
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

                resultsHTML += `</tbody></table></div><br>`; // Close table and add a line break
            }
        }

        document.getElementById('results-container').innerHTML = resultsHTML;
    }

    // Fetch results initially
    fetchResults();

    // Update results every 5 seconds
    setInterval(fetchResults, 5000);
</script>