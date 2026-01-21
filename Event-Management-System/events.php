<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - EventPro</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/event.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Our Event Categories</h1>
                <p>Discover our comprehensive event planning services tailored to make your special occasions unforgettable</p>
            </div>
        </section>

        <!-- Events Grid Section -->
        <section class="section">
            <div class="container">
                <div class="section-title">
                    <h2>Event Categories</h2>
                    <p>Choose from our expertly crafted event packages</p>
                </div>

                <div class="events-grid">
                    <!-- Corporate Events -->
                    <div class="event-card corporate">
                        <div class="event-image">
                            <img src="image/corpo1.jpeg" alt="Corporate Events">
                        </div>
                        <div class="event-content">
                            <h3>Corporate Events</h3>
                            <p>Professional events including conferences, seminars, team building, and corporate parties</p>
                            <ul class="event-features">
                                <li><i class="fas fa-check"></i> Conference Planning</li>
                                <li><i class="fas fa-check"></i> Team Building Activities</li>
                                <li><i class="fas fa-check"></i> Professional Setup</li>
                                <li><i class="fas fa-check"></i> AV Equipment</li>
                            </ul>
                            <a href="event-details.php?type=corporate" class="btn btn-primary">View Details</a>
                        </div>
                    </div>

                    <!-- Weddings -->
                    <div class="event-card wedding">
                        <div class="event-image">
                            <img src="image/wed1.jpeg" alt="Weddings">
                        </div>
                        <div class="event-content">
                            <h3>Weddings</h3>
                            <p>Beautiful and memorable wedding celebrations tailored to your dream vision</p>
                            <ul class="event-features">
                                <li><i class="fas fa-check"></i> Venue Decoration</li>
                                <li><i class="fas fa-check"></i> Catering Services</li>
                                <li><i class="fas fa-check"></i> Photography & Videography</li>
                                <li><i class="fas fa-check"></i> Wedding Planning</li>
                            </ul>
                            <a href="event-details.php?type=wedding" class="btn btn-primary">View Details</a>
                        </div>
                    </div>

                    <!-- Birthday -->
                    <div class="event-card birthday">
                        <div class="event-image">
                            <img src="image/bday1.webp" alt="Birthday Parties">
                        </div>
                        <div class="event-content">
                            <h3>Birthday Parties</h3>
                            <p>Fun and exciting birthday celebrations for all ages and themes</p>
                            <ul class="event-features">
                                <li><i class="fas fa-check"></i> Theme Decoration</li>
                                <li><i class="fas fa-check"></i> Entertainment</li>
                                <li><i class="fas fa-check"></i> Cake & Catering</li>
                                <li><i class="fas fa-check"></i> Party Games</li>
                            </ul>
                            <a href="event-details.php?type=birthday" class="btn btn-primary">View Details</a>
                        </div>
                    </div>

                    <!-- Conferences -->
                    <div class="event-card conference">
                        <div class="event-image">
                            <img src="image/con.jpg" alt="Conferences">
                        </div>
                        <div class="event-content">
                            <h3>Conferences</h3>
                            <p>Professional conference organization with all technical requirements</p>
                            <ul class="event-features">
                                <li><i class="fas fa-check"></i> Stage Setup</li>
                                <li><i class="fas fa-check"></i> Technical Support</li>
                                <li><i class="fas fa-check"></i> Registration Management</li>
                                <li><i class="fas fa-check"></i> Networking Sessions</li>
                            </ul>
                            <a href="event-details.php?type=conference" class="btn btn-primary">View Details</a>
                        </div>
                    </div>

                    <!-- Private Parties -->
                    <div class="event-card private">
                        <div class="event-image">
                            <img src="image/private-party.jpg" alt="Private Parties">
                        </div>
                        <div class="event-content">
                            <h3>Private Parties</h3>
                            <p>Exclusive private gatherings and celebrations for special occasions</p>
                            <ul class="event-features">
                                <li><i class="fas fa-check"></i> Intimate Settings</li>
                                <li><i class="fas fa-check"></i> Personalized Themes</li>
                                <li><i class="fas fa-check"></i> Gourmet Catering</li>
                                <li><i class="fas fa-check"></i> Entertainment Options</li>
                            </ul>
                            <a href="event-details.php?type=private" class="btn btn-primary">View Details</a>
                        </div>
                    </div>

                    <!-- Special Events -->
                    <div class="event-card special">
                        <div class="event-image">
                            <img src="image/special.jpeg" alt="Special Events">
                        </div>
                        <div class="event-content">
                            <h3>Special Events</h3>
                            <p>Unique and custom events including anniversaries, graduations, and more</p>
                            <ul class="event-features">
                                <li><i class="fas fa-check"></i> Custom Themes</li>
                                <li><i class="fas fa-check"></i> Special Decorations</li>
                                <li><i class="fas fa-check"></i> Memorable Experiences</li>
                                <li><i class="fas fa-check"></i> Personalized Service</li>
                            </ul>
                            <a href="event-details.php?type=special" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Plan Your Event?</h2>
                    <p>Contact us today to discuss your event requirements and let us make it extraordinary</p>
                    <div class="cta-buttons">
                        <a href="contact.php" class="btn btn-primary btn-large">Get a Quote</a>
                        <a href="tel:+1234567890" class="btn btn-secondary btn-large">Call Now</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
