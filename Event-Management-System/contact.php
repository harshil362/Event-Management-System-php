<?php include 'header.php'; ?>

<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Contact EventPro</h1>
            <p>Get in touch with our team to discuss your next event</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-envelope"></i> Send us a Message</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="Your Name" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" placeholder="Your Email" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="phone" placeholder="Phone Number" class="form-control">
                                </div>
                                <div class="form-group">
                                    <select name="event_type" class="form-control" required>
                                        <option value="">Select Event Type</option>
                                        <option value="corporate">Corporate Event</option>
                                        <option value="wedding">Wedding</option>
                                        <option value="birthday">Birthday</option>
                                        <option value="conference">Conference</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <textarea name="message" placeholder="Tell us about your event" rows="5" required class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-map-marker-alt"></i> Contact Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="contact-info">
                                <p><i class="fas fa-map-marker-alt"></i> 123 Event Street, City, Country</p>
                                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                                <p><i class="fas fa-envelope"></i> info@eventpro.com</p>
                                <p><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</p>
                            </div>
                            
                            <div class="mt-4">
                                <h4>Follow Us</h4>
                                <div class="social-links">
                                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
