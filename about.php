<?php include('head.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About Us - AMU Voting System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Modern sans-serif */
      background-color: #fff; /* White background as per image */
      color: #333;
    }

    /* Hero Section */
    .hero-section {
      background: url('img/aa.png') no-repeat center center; /* Replace with a relevant high-quality image */
      background-size: cover;
      color: white;
      padding: 100px 0;
      text-align: center;
      position: relative;
    }
    .hero-section::before { /* Dark overlay for text readability */
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
    }
    .hero-section .container {
        position: relative; /* Ensure content is above overlay */
        z-index: 1;
    }
    .hero-section h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .hero-section p {
      font-size: 1.25rem;
      max-width: 700px;
      margin: 0 auto;
    }

    /* General Section Styling */
    .section-padding {
      padding: 60px 0;
    }
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50; /* Dark blueish grey */
      margin-bottom: 40px;
      text-align: center;
    }
    .section-subtitle {
        font-size: 1.2rem;
        font-weight: 600;
        color: #34495e; /* Slightly lighter dark blue */
        margin-bottom: 15px;
    }

    /* Our Mission & Our Story Shared Styles */
    .content-image {
      width: 100%;
      max-height: 400px; /* Adjust as needed */
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .text-block p {
        font-size: 1.05rem;
        line-height: 1.7;
        color: #555;
        text-align: justify;
    }

    /* Our Mission Section */
    .mission-list {
        list-style: none;
        padding-left: 0;
    }
    .mission-list li {
        margin-bottom: 12px;
        font-size: 1.05rem;
        color: #555;
        display: flex;
        align-items: center;
    }
    .mission-list .bi {
        color: #1abc9c; /* A pleasant green for icons */
        margin-right: 10px;
        font-size: 1.2rem;
    }


    /* Our Leadership Section */
    .leadership-card {
      text-align: center;
      margin-bottom: 30px;
      background-color: #f8f9fa; /* Light grey background for cards */
      padding: 20px;
      border-radius: 8px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .leadership-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .leader-img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 15px;
      border: 4px solid #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .leader-name {
      font-size: 1.2rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 5px;
    }
    .leader-title {
      font-size: 0.9rem;
      color:rgb(10, 24, 218); /* Muted grey */
      margin-bottom: 10px;
    }
    .leader-social .bi-linkedin {
      color:rgb(2, 52, 80); /* LinkedIn blue */
      font-size: 2rem;
      transition: color 0.3s ease;
    }
    .leader-social .bi-telegram {
        color: #0088cc; /* Telegram blue */
        font-size: 2rem;
        transition: color 0.3s ease;
      }
    .leader-social .bi-linkedin:hover {
        color: #005582;
    }

    /* Testimonials Section */
    .testimonial-section {
        background-color: #f8f9fa; /* Light grey background */
    }
    .testimonial-card {
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      text-align: left;
      min-height: 320px; /* Ensure consistent height */
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .testimonial-quote-icon .bi-quote {
      font-size: 2.5rem;
      color: #1abc9c;
      margin-bottom: 15px;
    }
    .testimonial-text {
      font-size: 1rem;
      font-style: italic;
      color: #555;
      margin-bottom: 20px;
      flex-grow: 1;
    }
    .testimonial-author {
      display: flex;
      align-items: center;
    }
    .testimonial-author img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 15px;
    }
    .testimonial-author-info .name {
      font-weight: 600;
      color: #2c3e50;
    }
    .testimonial-author-info .title {
      font-size: 0.9rem;
      color: #7f8c8d;
    }
    .testimonial-logos img {
        max-height: 40px; /* Adjust as needed */
        filter: grayscale(100%);
        opacity: 0.6;
        margin: 0 15px;
        transition: filter 0.3s ease, opacity 0.3s ease;
    }
    .testimonial-logos img:hover {
        filter: grayscale(0%);
        opacity: 1;
    }
    /* Carousel controls to match the image style (darker, rounder) */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        padding: 10px; /* Adjust size */
    }
    .carousel-control-prev, .carousel-control-next {
        width: 5%; /* Adjust hit area */
    }


    /* CTA Section */
    .cta-section {
      background-color: #2c3e50; /* Dark blueish grey */
      color: white;
      text-align: center;
    }
    .cta-section h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    .cta-section p {
        font-size: 1.1rem;
        margin-bottom: 30px;
        opacity: 0.9;
    }
    .btn-custom-primary {
        background-color: #1abc9c; /* Primary action color */
        border-color: #1abc9c;
        color: white;
        padding: 12px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px; /* Pill shape */
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .btn-custom-primary:hover {
        background-color: #16a085;
        border-color: #16a085;
        color: white;
    }

    /* Animations */
    .card-body, .leadership-card, .testimonial-card { /* Apply animation to these elements */
      animation: fadeInUp 0.8s ease;
    }
    @keyframes fadeInUp {
      from {
        transform: translateY(40px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    /* Scroll to Top Button Style */
    #scrollToTopBtn {
      display: none; /* Hidden by default */
      position: fixed;
      bottom: 30px; /* Adjusted for better spacing if footer has padding */
      right: 30px;
      z-index: 1030; /* Ensure it's above most elements, e.g. Bootstrap modals are 1050+ */
      border: none;
      outline: none;
      background-color: #1abc9c; /* Match your primary color */
      color: white;
      cursor: pointer;
      padding: 10px 13px; /* Slightly adjusted for better circular look with icon */
      border-radius: 50%;
      font-size: 20px; /* Icon size */
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: background-color 0.3s, opacity 0.3s, visibility 0.3s;
    }

    #scrollToTopBtn:hover {
      background-color: #16a085; /* Darker shade on hover */
    }
    #scrollToTopBtn .bi { /* Vertically align icon if needed */
        vertical-align: middle;
    }

  </style>
</head>
<body>
<?php include('view_banner.php'); ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <h1>Empowering Student Voice Through Secure Online Voting</h1>
    <p>Building a transparent, accessible, and modern election process for Arbaminch University.</p>
  </div>
</section>

<!-- Our Mission Section -->
<section id="mission" class="section-padding">
  <div class="container">
    <h2 class="section-title">Our Mission</h2>
    <div class="row align-items-center">
      <div class="col-lg-7 text-block">
        <p class="fs-5">
          The Arbaminch University Online Voting System is a secure and efficient platform designed to modernize the election process within our university. This system replaces traditional paper-based voting with a user-friendly online interface, making voting more accessible, convenient, and transparent for all students and faculty.
        </p>
        <p class="section-subtitle mt-4">We are committed to a fair and inclusive electoral process.</p>
        <p>
          Our primary goal is to enhance participation in university elections by providing a seamless and reliable voting experience. The system incorporates robust security measures to ensure the integrity of the voting process and protect against fraud.
        </p>
        <ul class="mission-list mt-3">
          <li><i class="bi bi-shield-lock-fill"></i>Secure Voter Authentication</li>
          <li><i class="bi bi-lock-fill"></i>Encrypted Voting Data</li>
          <li><i class="bi bi-graph-up-arrow"></i>Real-time Audit Trails & Transparency</li>
          <li><i class="bi bi-ui-checks-grid"></i>Accessible and User-Friendly Interface</li>
        </ul>
      </div>
      <div class="col-lg-5 mt-4 mt-lg-0">
        <!-- Replace with a relevant image for AMU or voting concept -->
        <img src="img/download (1).png" class="img-fluid rounded shadow-sm content-image" alt="University Collaboration">
      </div>
    </div>
  </div>
</section>

<!-- Our Story Section -->
<section id="story" class="section-padding bg-light"> <!-- Or use #f8f9fa -->
  <div class="container">
    <h2 class="section-title">Our Story</h2>
    <div class="row align-items-center">
      <div class="col-lg-5 order-lg-2 text-block">
        <p class="fs-5">
          The vision for a modern online voting system at Arbaminch University was born from a desire to increase student engagement and streamline administrative processes. Recognizing the potential of technology to transform traditional methods, a dedicated team of faculty and IT professionals embarked on this project.
        </p>
        <p>
          From initial concept through development and testing, our focus has been on creating a platform that is not only technologically advanced but also trustworthy and easy to use for every member of the AMU community. We aim to set a new standard for university elections.
        </p>
         <a href="http://www.amu.edu.et" target="_blank" class="btn btn-outline-primary mt-3">Learn more about AMU <i class="bi bi-arrow-right-short"></i></a>
      </div>
      <div class="col-lg-7 order-lg-1 mt-4 mt-lg-0">
        <!-- Replace with relevant images for AMU's history or development process -->
        <div class="row">
            <div class="col-md-12 mb-3">
                 <img src="img/aa.png" class="img-fluid rounded shadow-sm content-image" alt="Team Discussion">
            </div>
            <!-- You can add another image here like in the example if needed -->
            <!-- <div class="col-md-6">
                 <img src="img/placeholder-story-2.jpg" class="img-fluid rounded shadow-sm content-image" alt="Development Team">
            </div> -->
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Our Leadership Section -->
<section id="leadership" class="section-padding">
  <div class="container">
    <h2 class="section-title">Our Team / Project Leads</h2>
    <div class="row">
      <!-- Example Leader 1 -->
      <div class="col-md-6 col-lg-3">
        <div class="leadership-card">
          <img src="img/natty.jpg" class="leader-img" alt="Leader Name">
          <h5 class="leader-name">Natnael Serte</h5>
          <p class="leader-title"></p>
          <a href="https://www.linkedin.com/in/natty-sertse-2a9b6a342/" class="leader-social"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>
      <!-- Example Leader 2 -->
      <div class="col-md-6 col-lg-3">
        <div class="leadership-card">
          <img src="img/absir.jpg" class="leader-img" alt="Leader Name">
          <h5 class="leader-name">Absir Mugeta</h5>
          <p class="leader-title"></p>
          <a href="https://t.me/absir10" class="leader-social"><i class="bi bi-telegram"></i></a>
        </div>
      </div>
      <!-- Example Leader 3 -->
      <div class="col-md-6 col-lg-3">
        <div class="leadership-card">
          <img src="" class="leader-img" alt="Leader Name">
          <h5 class="leader-name">Mintesnot Gulilat</h5>
          <p class="leader-title"></p>
          <a href="https://t.me/Mintesnot0" class="leader-social"><i class="bi bi-telegram"></i></a>
        </div>
      </div>
      <!-- Example Leader 4 -->
      <div class="col-md-6 col-lg-3">
        <div class="leadership-card">
          <img src="https://via.placeholder.com/150/CCDDEE/FFFFFF?text=Student+Rep" class="leader-img" alt="Leader Name">
          <h5 class="leader-name">Eman Seid</h5>
          <p class="leader-title"></p>
          <a href="#" class="leader-social"><i class="bi bi-telegram"></i></a>
        </div>
      </div>
      <!-- Add more leaders as needed -->
    </div>
  </div>

  <div class="col-md-12 col-lg-3">
        <div class="leadership-card">
          <img src="https://via.placeholder.com/150/CCDDEE/FFFFFF?text=Student+Rep" class="leader-img" alt="Leader Name">
          <h5 class="leader-name" >Helina Tensay</h5>
          <p class="leader-title"></p>
          <a href="#" class="leader-social"><i class="bi bi-telegram"></i></a>
        </div>
      </div>
      <!-- Add more leaders as needed -->
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="section-padding testimonial-section">
  <div class="container">
    <h2 class="section-title">What Our University Community Says</h2>
    <div class="mb-4 text-center testimonial-logos">
        <!-- You can put logos of departments or partner institutions here if applicable -->
        <img src="img/AMU logo.jpg" alt="AMU Logo" title="Arbaminch University">
       
    </div>

    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <!-- Testimonial 1 -->
        <div class="carousel-item active">
          <div class="row justify-content-center">
            <div class="col-md-8">
              <div class="testimonial-card">
                <div>
                    <div class="testimonial-quote-icon"><i class="bi bi-quote"></i></div>
                    <p class="testimonial-text">
                      "The new online voting system is a fantastic step forward for AMU. It's intuitive, easy to use, and I feel confident that my vote is secure. This will definitely increase participation!"
                    </p>
                </div>
                <div class="testimonial-author">
                  <img src="https://via.placeholder.com/80/88aabb/FFFFFF?text=S.T." alt="Student Tesfaye">
                  <div class="testimonial-author-info">
                    <span class="name">Student Tesfaye G.</span>
                    <span class="title">3rd Year, Computer Science</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Testimonial 2 -->
        <div class="carousel-item">
          <div class="row justify-content-center">
            <div class="col-md-8">
              <div class="testimonial-card">
                 <div>
                    <div class="testimonial-quote-icon"><i class="bi bi-quote"></i></div>
                    <p class="testimonial-text">
                      "As a faculty member, I appreciate the efficiency and transparency this system brings to our university elections. It simplifies the process for everyone involved."
                    </p>
                </div>
                <div class="testimonial-author">
                  <img src="" alt="students">
                  <div class="testimonial-author-info">
                    <span class="name">students</span>
                    <span class="title"> Faculty of computing and software Engineering</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Add more testimonials here -->
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
</section>

<!-- Call to Action (CTA) Section -->
<section class="section-padding cta-section">
  <div class="container">
    <h2>Ready to Participate?</h2>
    <p>Learn more about upcoming elections, how to register, and the voting process. Your voice matters!</p>
    <a href="voters_portal.php" class="btn btn-custom-primary">Access Voter's Portal</a>
    <!-- Or link to an FAQ page, e.g., <a href="faq.php" class="btn btn-custom-primary">Learn More & FAQ</a> -->
  </div>
</section>


<?php include('script.php'); ?>

<!-- Scroll to Top Button HTML -->
<button onclick="scrollToTop()" id="scrollToTopBtn" title="Go to top">
    <i class="bi bi-arrow-up-circle-fill"></i>
</button>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Optional: Auto-cycle carousel if you want
    // const testimonialCarousel = document.getElementById('testimonialCarousel')
    // const carousel = new bootstrap.Carousel(testimonialCarousel, {
    //   interval: 5000, // 5 seconds
    //   wrap: true
    // })

    // Scroll to Top Button JavaScript
    var scrollToTopBtn = document.getElementById("scrollToTopBtn");

    // When the user scrolls down 100px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
      if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        if (scrollToTopBtn) { // Check if element exists
            scrollToTopBtn.style.display = "block";
        }
      } else {
        if (scrollToTopBtn) { // Check if element exists
            scrollToTopBtn.style.display = "none";
        }
      }
    }

    // When the user clicks on the button, scroll to the top of the document
    function scrollToTop() {
      window.scrollTo({top: 0, behavior: 'smooth'});
    }
</script>
</body>
</html>