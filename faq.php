<?php include('view_banner.php'); ?>
<?php include('head.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FAQ - AMU Voting System</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .faq-section {
      margin-top: 50px;
      padding: 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    .faq-header {
      font-weight: bold;
      color: #337ab7;
      text-align: center;
      margin-bottom: 30px;
    }
    .panel-title > a {
      display: block;
      text-decoration: none;
    }
    .panel-default > .panel-heading {
      background-color: #e7f1ff;
      border-color: #ddd;
      color: #337ab7;
    }
    .panel-body {
      font-size: 15px;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="container faq-section">
    <h2 class="faq-header">Frequently Asked Questions (FAQ)</h2>

    <div class="panel-group" id="faqAccordion">

      <!-- I. General & About the Platform -->
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#general">I. General & About the Platform</a>
          </h4>
        </div>
        <div id="general" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>1. What is this Arbaminch Community Voting Platform?</strong></p>
            <p>This is an official and secure online system designed for conducting fair and transparent elections for various community and institutional roles.</p>

            <p><strong>2. Why are we using an online voting system?</strong></p>
            <p>Online voting enhances accessibility, reduces logistical challenges, and helps modernize our democratic processes.</p>

            <p><strong>3. Who is responsible for managing this platform?</strong></p>
            <p>The system is managed and monitored by the Arbaminch University ICT Center in collaboration with local election bodies.</p>

            <p><strong>4. Is this platform the only way to vote?</strong></p>
            <p>Yes. For current elections, voting is conducted only through this platform for efficiency and accountability.</p>

            <p><strong>5. How does this benefit the Arbaminch community?</strong></p>
            <p>It ensures broader participation, quicker results, and cost-effective management of elections.</p>

            <p><strong>6. Is there a cost to use this platform or to vote?</strong></p>
            <p>No. The platform is provided as a free service to all eligible participants.</p>
          </div>
        </div>
      </div>

      <!-- II. Eligibility & Registration -->
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#registration">II. Eligibility & Registration</a>
          </h4>
        </div>
        <div id="registration" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>7. Who is eligible to register and vote?</strong></p>
            <p>Any member of the Arbaminch community or student body who meets the criteria set by the organizing committee.</p>

            <p><strong>8. How do I register to vote?</strong></p>
            <p>Visit the registration page, fill in your details, and submit the form. You will receive a confirmation if your registration is approved.</p>

            <p><strong>9. What documents or information do I need?</strong></p>
            <p>You may need your ID, full name, university ID (if applicable), and a valid email or phone number for OTP verification.</p>

            <p><strong>10. Is there a deadline for registration?</strong></p>
            <p>Yes. Registration closes a few days before the election date. Deadlines are posted on the homepage and announcements section.</p>

            <p><strong>11. I’ve recently moved. How do I update my registration?</strong></p>
            <p>Contact the system administrator or support team to update your details before registration closes.</p>
          </div>
        </div>
      </div>

      <!-- III. Voting Process -->
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#voting">III. Voting Process</a>
          </h4>
        </div>
        <div id="voting" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>12. How do I vote?</strong></p>
            <p>Log in using your credentials, select the election category, choose your preferred candidate(s), and click submit.</p>

            <p><strong>13. Can I vote from my phone?</strong></p>
            <p>Yes, the platform is mobile-friendly. Just access it via a browser on your smartphone.</p>

            <p><strong>14. Can I change my vote once submitted?</strong></p>
            <p>No. Once submitted, your vote is final and securely recorded in the system.</p>

            <p><strong>15. How do I know if my vote was counted?</strong></p>
            <p>You will receive an on-screen confirmation and optionally an email or SMS confirmation.</p>

            <p><strong>16. What if I make a mistake during voting?</strong></p>
            <p>You can review your selections before final submission. Once submitted, changes cannot be made.</p>
          </div>
        </div>
      </div>

      <!-- IV. Security & Privacy -->
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#security">IV. Security & Privacy</a>
          </h4>
        </div>
        <div id="security" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>17. Is my vote anonymous?</strong></p>
            <p>Yes. The system ensures that votes are stored without linking them to personal identifiers.</p>

            <p><strong>18. How is my personal information protected?</strong></p>
            <p>We use encryption and secure protocols to protect your data. Only authorized staff can access limited data for validation.</p>

            <p><strong>19. Can someone see who I voted for?</strong></p>
            <p>No. Vote data is encrypted and anonymized in the backend. Even administrators cannot access vote choices.</p>

            <p><strong>20. Is this system vulnerable to hacking?</strong></p>
            <p>The platform is developed using best practices in web security, and regular audits are conducted to prevent vulnerabilities.</p>

            <p><strong>21. What happens if I lose my credentials?</strong></p>
            <p>You can reset your password or contact the support team to regain access using your registered information.</p>
          </div>
        </div>
      </div>

      <!-- V. Technical Support & Troubleshooting -->
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#support">V. Technical Support & Troubleshooting</a>
          </h4>
        </div>
        <div id="support" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>22. I forgot my password. What should I do?</strong></p>
            <p>Use the “Forgot Password” link on the login page to reset it via your email or registered phone number.</p>

            <p><strong>23. I’m having trouble accessing the site. What could be wrong?</strong></p>
            <p>Make sure your internet connection is stable. Try clearing your browser cache or switching to another browser.</p>

            <p><strong>24. The page isn’t loading properly on my phone. What can I do?</strong></p>
            <p>Ensure you are using a modern browser. Refresh the page or try opening it in desktop view mode.</p>

            <p><strong>25. I didn’t receive my OTP. What should I do?</strong></p>
            <p>Wait a few minutes and click “Resend OTP.” Make sure your phone/email is working. Contact support if the issue persists.</p>

            <p><strong>26. Who can I contact for help?</strong></p>
            <p>Email: support@amu.edu.et or visit the help desk in the ICT office during working hours.</p>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#security">VI. Security & Privacy</a>
          </h4>
        </div>
        <div id="security" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>17. Is my vote anonymous?</strong></p>
            <p>Yes. The system ensures that votes are stored without linking them to personal identifiers.</p>

            <p><strong>18. How is my personal information protected?</strong></p>
            <p>We use encryption and secure protocols to protect your data. Only authorized staff can access limited data for validation.</p>

            <p><strong>19. Can someone see who I voted for?</strong></p>
            <p>No. Vote data is encrypted and anonymized in the backend. Even administrators cannot access vote choices.</p>

            <p><strong>20. Is this system vulnerable to hacking?</strong></p>
            <p>The platform is developed using best practices in web security, and regular audits are conducted to prevent vulnerabilities.</p>

            <p><strong>21. What happens if I lose my credentials?</strong></p>
            <p>You can reset your password or contact the support team to regain access using your registered information.</p>
          </div>
        </div>
      </div>

      <!-- V. Technical Support & Troubleshooting -->
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#support">VII. Technical Support & Troubleshooting</a>
          </h4>
        </div>
        <div id="support" class="panel-collapse collapse">
          <div class="panel-body">
            <p><strong>22. I forgot my password. What should I do?</strong></p>
            <p>Use the “Forgot Password” link on the login page to reset it via your email or registered phone number.</p>

            <p><strong>23. I’m having trouble accessing the site. What could be wrong?</strong></p>
            <p>Make sure your internet connection is stable. Try clearing your browser cache or switching to another browser.</p>

            <p><strong>24. The page isn’t loading properly on my phone. What can I do?</strong></p>
            <p>Ensure you are using a modern browser. Refresh the page or try opening it in desktop view mode.</p>

            <p><strong>25. I didn’t receive my OTP. What should I do?</strong></p>
            <p>Wait a few minutes and click “Resend OTP.” Make sure your phone/email is working. Contact support if the issue persists.</p>

            <p><strong>26. Who can I contact for help?</strong></p>
            <p>Email: support@amu.edu.et or visit the help desk in the ICT office during working hours.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include('footer.php'); ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
