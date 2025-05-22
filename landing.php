<?php
require 'admin/dbcon.php'; // Ensure this path is correct
include 'head.php'; // Assuming this includes Bootstrap CSS, jQuery
?>
<?php
$notificationMessage = ""; // Initialize
$showCountdown = false;
$endTimeForJS = 0; // Initialize
try {
    $query = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1 LIMIT 1");
    $query->execute();
    $votingEvent = $query->fetch(PDO::FETCH_ASSOC);
if ($votingEvent) {
        $title = htmlspecialchars($votingEvent['title']);
        $startTime = (int)$votingEvent['start_time'];
        $endTime = (int)$votingEvent['end_time'];
        $endTimeForJS = $endTime * 1000;

        $startTimeFormatted = date("g:i A", $startTime);
        $endTimeFormatted = date("g:i A", $endTime);

        if (time() > $endTime) {
             $updateVotersStmt = $pdo->prepare("UPDATE users SET account = 'Inactive'");
            $updateVotersStmt->execute();
             $updateEventStmt = $pdo->prepare("UPDATE voting_events SET is_active = 0 WHERE id = ?");
            $updateEventStmt->execute([$votingEvent['id']]);
            $notificationMessage = "The voting event '<strong>" . $title . "</strong>' has ended.";
            $showCountdown = false;
        } else {
            $notificationMessage = "Ongoing Vote: <strong>" . $title . "</strong> (Ends at " . $endTimeFormatted . ")";
            $showCountdown = true;
        }
    } else {
        $notificationMessage = "No active voting event at this time.";
    }
} catch (PDOException $e) {
    $notificationMessage = 'Error: Could not retrieve voting event information';
    error_log("Error fetching voting event: " . $e->getMessage());
}
?>

<body> <?php // Body tag moved after PHP logic for notification ?>

    <?php if (!empty($notificationMessage)): ?>
    <div class=" landing-notification-banner">
        <?php echo $notificationMessage; ?>
        <?php if ($showCountdown && $endTimeForJS > 0) : ?>
            <span id="countdown" class="landing-countdown-timer"></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Hero Section with Background Image Slider -->
    <section class="hero-section">
        <head> 
          <meta charset="UTF-8">
          <title>Auto Hero Slider</title>
          <style>
            .hero-slider {
              position: relative;
              width: 100%;
              height: 400px; /* This might be overridden by later styles */
              overflow: hidden;
            }

            .hero-slide {
              position: absolute;
              width: 100%;
              height: 100%;
              background-size: cover;
              background-position: center;
              opacity: 0;
              transition: opacity 1s ease-in-out;
            }

            .hero-slide.active {
              opacity: 1;
              z-index: 1;
            }
            .hero-slide {
              position: absolute;
              width: 100%;
              height: 100%;
              background-size: cover;
              background-position: center;
              background-repeat: no-repeat;
              image-rendering: auto; /* Let the browser choose best rendering */
            }
            .hero-slider { /* This redeclares .hero-slider, the last one will take precedence if properties overlap */
              width: 100%;
              height: 60vh; /* Responsive height */
              max-height: 600px;
              overflow: hidden;
            }

            /* Scroll to Top Button Style -- ADDED */
            #scrollToTopBtnLanding { /* Using a unique ID to avoid potential conflicts */
              display: none; /* Hidden by default */
              position: fixed;
              bottom: 20px;
              right: 30px;
              z-index: 1030; /* Ensure it's above most elements */
              border: none;
              outline: none;
              background-color: #007bff; /* Example: Bootstrap primary blue, adjust as needed */
              color: white;
              cursor: pointer;
              padding: 10px 13px;
              border-radius: 50%;
              font-size: 18px; /* Adjust icon size if needed */
              box-shadow: 0 2px 5px rgba(0,0,0,0.2);
              transition: background-color 0.3s, opacity 0.3s, visibility 0.3s;
            }

            #scrollToTopBtnLanding:hover {
              background-color: #0056b3; /* Darker shade on hover */
            }
            #scrollToTopBtnLanding .bi { /* If using Bootstrap Icons */
                vertical-align: middle;
            }
            /* END Scroll to Top Button Style */

          </style>
        </head> <?php /* Closing incorrect <head> tag */ ?>


<div class="hero-slider">
  
  <div class="hero-slide" style="background-image: url('img/aa.png');"></div>
  <div class="hero-slide" style="background-image: url('img/images.png');"></div>
  <div class="hero-slide" style="background-image: url('img/download (2) (1).png');"></div>
  <div class="hero-slide" style="background-image: url('img/download (1).png');"></div>
</div>

<script>
  const slides = document.querySelectorAll('.hero-slide');
  let currentSlide = 0;

  function showNextSlide() {
    if (slides.length === 0) return; // Guard against no slides
    slides[currentSlide].classList.remove('active');
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add('active');
  }

  // Change slide every 4 seconds
  if (slides.length > 0) { // Only start interval if slides exist
    setInterval(showNextSlide, 4000);
    slides[0].classList.add('active'); // Initialize first slide
  }
</script>

        <div class="hero-overlay"></div>
        <div class="hero-content text-center">
            <p class="hero-sub-headline">Arbaminch Community Voting Services</p> 
            <h1 class="hero-main-headline">Your Voice, Our Future</h1> 
            <div class="hero-cta-buttons mt-4">
                <a href="login.php" class="btn hero-btn-primary">Vote Now</a>
                <a href="about.php" class="btn hero-btn-secondary">Learn More</a> 
            </div>
        </div>
        <button class="slider-nav prev" onclick="moveHeroSlide(-1)">❮</button>
        <button class="slider-nav next" onclick="moveHeroSlide(1)">❯</button>
    </section>

    <!-- Services Section -->
    <section class="services-section py-5">
        <div class="container">
            <div class="row gy-4 gx-lg-5"> 
               
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-landmark"></i></div> 
                        <h3 class="service-title">Community Governance</h3>
                        <p class="service-description">Participate in decisions shaping our local governance.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-vote-yea"></i></div> 
                        <h3 class="service-title">Secure Elections</h3>
                        <p class="service-description">Transparent and secure voting for all community matters.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-users"></i></div> 
                        <h3 class="service-title">Civic Engagement</h3>
                        <p class="service-description">Empowering every member to contribute to community development.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-shield-alt"></i></div> <!-- Replace with actual icon -->
                        <h3 class="service-title">Data Privacy</h3>
                        <p class="service-description">Your participation and vote are handled with utmost confidentiality.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-info-circle"></i></div> 
                        <h3 class="service-title">Voter Information</h3>
                        <p class="service-description">Access clear information about candidates and proposals.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-chart-bar"></i></div> 
                        <h3 class="service-title">Verified Results</h3>
                        <p class="service-description">Timely and transparent announcement of voting outcomes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    // Include your footer here
    // Example: include('footer.php');
    ?>

    <?php if ($showCountdown && $endTimeForJS > 0) : ?>
        <script>
            var countDownDateHero = new Date(<?php echo $endTimeForJS; ?>).getTime();
            var heroCountdownInterval = setInterval(function() {
                var now = new Date().getTime();
                var distance = countDownDateHero - now;
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let countdownHTML = "";
                if (days > 0) countdownHTML += days + "d ";
                countdownHTML += (hours < 10 ? "0" : "") + hours + "h ";
                countdownHTML += (minutes < 10 ? "0" : "") + minutes + "m ";
                countdownHTML += (seconds < 10 ? "0" : "") + seconds + "s ";

                const countdownElement = document.getElementById("countdown");
                if (countdownElement) {
                    if (distance < 0) {
                        clearInterval(heroCountdownInterval);
                        countdownElement.innerHTML = "Voting Period Ended";
                        // Optionally refresh or update banner text more comprehensively
                    } else {
                        countdownElement.innerHTML = " | Ends in: " + countdownHTML.trim();
                    }
                } else {
                    clearInterval(heroCountdownInterval); // Clear if element not found
                }
            }, 1000);
        </script>
    <?php endif; ?>
    <link rel="stylesheet" href="landing-style.css">
<!-- Also ensure Font Awesome is linked if you plan to use its icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        // Hero Image Slider Logic
        let currentHeroSlide = 0;
        const heroSlides = document.querySelectorAll(".hero-section .hero-slide"); // This re-selects .hero-slide elements

        function showHeroSlide(index) {
            if (heroSlides.length === 0) return;
            heroSlides.forEach((slide, i) => {
                slide.classList.remove("active");
                if (i === index) {
                    slide.classList.add("active");
                }
            });
        }

        function moveHeroSlide(n) {
            if (heroSlides.length === 0) return;
            currentHeroSlide = (currentHeroSlide + n + heroSlides.length) % heroSlides.length;
            showHeroSlide(currentHeroSlide);
        }

        // Auto slide (optional) - This might conflict with the earlier setInterval for hero slider if both are active
        // setInterval(() => {
        //     moveHeroSlide(1);
        // }, 7000); // Change image every 7 seconds

        // Show the first slide initially if JS is enabled
        if (heroSlides.length > 0 && !document.querySelector(".hero-section .hero-slide.active")) { // Check if no slide is active yet
            showHeroSlide(0);
        }
    </script>

    <!-- Scroll to Top Button HTML -- ADDED -->
    <button onclick="scrollToTopLanding()" id="scrollToTopBtnLanding" title="Go to top">
        <i class="fas fa-arrow-up"></i> <!-- Using Font Awesome icon, ensure FA is linked -->
        <?php /* Or use Bootstrap Icon if preferred and Bootstrap Icons CSS is linked:
        <i class="bi bi-arrow-up-circle-fill"></i>
        */ ?>
    </button>

    <!-- Scroll to Top Button JavaScript -- ADDED -->
    <script>
        var scrollToTopBtnLanding = document.getElementById("scrollToTopBtnLanding");

        window.onscroll = function() {scrollFunctionLanding()};

        function scrollFunctionLanding() {
          if (scrollToTopBtnLanding) { // Check if element exists
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
              scrollToTopBtnLanding.style.display = "block";
            } else {
              scrollToTopBtnLanding.style.display = "none";
            }
          }
        }

        function scrollToTopLanding() {
          window.scrollTo({top: 0, behavior: 'smooth'});
        }
    </script>
   <script>
(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="wRNrq_W111da9rHx-Gc7G";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
</script>

    <?php include 'script.php'; // Your general script file if any ?>
</body>
</html>