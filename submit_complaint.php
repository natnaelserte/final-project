<?php include 'header.php'; ?>

<div class="complaint-form">
  <h2>Submit a Complaint</h2>
  <form action="process_complaint.php" method="POST">
    <input type="hidden" name="name" value="<?= $_SESSION['username']; ?>" />
    
    <label>Voting Event:</label>
    <select name="voting_event_id" required>
      <option value="1">Student Union 2025</option>
      <option value="2">Club Elections</option>
      <!-- Dynamically populate from DB if needed -->
    </select>

    <label>Complaint Category:</label>
    <select name="category" required>
      <option value="Technical Issue">Technical Issue</option>
      <option value="Unfair Voting">Unfair Voting</option>
      <option value="Candidate Misconduct">Candidate Misconduct</option>
      <option value="Other">Other</option>
    </select>

    <label>Subject:</label>
    <input type="text" name="subject" required />

    <label>Description:</label>
    <textarea name="description" required></textarea>

    <button type="submit">Submit Complaint</button>
  </form>
</div>

<?php include 'footer.php'; ?>
