<?php include 'header.php'; ?>

<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Customer Reviews & Feedback</h1>
            <p>Share your experience and read what others have to say about our services</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <!-- Review Submission Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-star"></i> Share Your Experience</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" class="review-form">
                                <div class="form-group">
                                    <label for="name">Your Name</label>
                                    <input type="text" id="name" name="name" required class="form-control" placeholder="Enter your full name">
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" required class="form-control" placeholder="Enter your email">
                                </div>

                                <div class="form-group">
                                    <label for="event_type">Event Type</label>
                                    <select id="event_type" name="event_type" class="form-control" required>
                                        <option value="">Select Event Type</option>
                                        <option value="corporate">Corporate Event</option>
                                        <option value="wedding">Wedding</option>
                                        <option value="birthday">Birthday</option>
                                        <option value="conference">Conference</option>
                                        <option value="private">Private Party</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Rating</label>
                                    <div class="rating-stars">
                                        <input type="radio" id="star5" name="rating" value="5">
                                        <label for="star5" title="5 stars">★</label>
                                        <input type="radio" id="star4" name="rating" value="4">
                                        <label for="star4" title="4 stars">★</label>
                                        <input type="radio" id="star3" name="rating" value="3">
                                        <label for极="star3" title="3 stars">★</label>
                                        <input type="radio" id="star2" name="rating" value="2">
                                        <label for="star2" title="2 stars">★</label>
                                        <input极 type="radio" id="star1" name="rating" value="1">
                                        <label for="star1" title="1 star">★</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="review_title">Review Title</label>
                                    <input type="text" id="review_title" name="review_title" required class="form-control" placeholder="Brief summary of your experience">
                                </div>

                                <div class="form-group">
                                    <label for="review_text">Your Review</label>
                                    <textarea id="review_text" name="review_text" rows="5" required class="form-control" placeholder="Share your detailed experience with us..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="photos">Upload Photos (Optional)</label>
                                    <input type="file" id="photos" name="photos[]" multiple class="form-control" accept="image/*">
                                    <small class="form-text">You can upload up to 3 photos of your event</small>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" id="consent" name="consent" required class="form-check-input">
                                    <label for="consent" class="form-check-label">I consent to this review being published on the website</label>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mt-3">Submit Review</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <!-- Review Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-bar"></i> Review Statistics</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="overall-rating">
                                <div class="rating-score">4.8</div>
                                <div class="stars">
                                    ★★★★★
                                </div>
                                <div class="total-reviews">Based on 127 reviews</div>
                            </div>

                            <div class="rating-breakdown mt-4">
                                <div class="rating-item">
                                    <span class="rating-label">5 stars</span>
                                    <div class="rating-bar">
                                        <div class="rating-progress" style="width: 85%"></div>
                                    </div>
                                    <span class="rating-count">108</span>
                                </div>
                                <div class="rating-item">
                                    <span class="rating-label">4 stars</span>
                                    <div class="rating-bar">
                                        <div class="rating-progress" style="width: 12%"></div>
                                    </div>
                                    <span class="rating-count">15</span>
                                </div>
                                <div class极="rating-item">
                                    <span class="rating-label">3 stars</span>
                                    <div class="rating-bar">
                                        <div class="rating-progress" style="width: 2%"></div>
                                    </div>
                                    <span class="rating-count">3</span>
                                </div>
                                <div class="rating-item">
                                    <span class="rating-label">2 stars</span>
                                    <div class="rating-bar">
                                        <div class="rating-progress" style="width: 1%"></div>
                                    </div>
                                    <span class="rating-count">1</span>
                                </div>
                                <div class="rating-item">
                                    <span class="rating-label">1 star</span>
                                    <div class="rating-bar">
                                        <div class="rating-progress" style="width: 0%"></div>
                                    </div>
                                    <span class="rating-count">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Guidelines -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Review Guidelines</h3>
                        </div>
                        <div class="card-body">
                            <ul class="guidelines-list">
                                <li>Be honest and specific about your experience</li>
                                <li>Focus on the service quality and event execution</li>
                                <li>Mention what you liked and areas for improvement</li>
                                <li>Keep your review respectful and constructive</li>
                                <li>Photos help others visualize your experience</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="section-title">
                        <h2>Recent Reviews</h2>
                        <p>See what our clients are saying about our services</p>
                    </div>

                    <div class="reviews-grid">
                        <!-- Review 1 -->
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4>Sarah Johnson</h4>
                                        <span class="review-date">2 days ago</span>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    ★★★★★
                                    <span class="rating-value">5.0</span>
                                </div>
                            </div>
                            <div class="review-content">
                                <h5 class="review-title">Exceptional Wedding Planning!</h5>
                                <p class="review-text">EventPro made our wedding day absolutely perfect. Every detail was taken care of with precision and care. The team was professional, responsive, and truly understood our vision.</p>
                                <div class="review-meta">
                                    <span class="event-type">Wedding</span>
                                </div>
                            </div>
                        </div>

                        <!-- Review 2 -->
                        <div class="review-card">
                            <div class极="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4>Michael Chen</h4>
                                        <span class="review-date">1 week ago</span>
                                    </div>
                                </div>
                                <div class="review极-rating">
                                    ★★★★☆
                                    <span class="rating-value">4.5</span>
                                </div>
                            </div>
                            <div class="review-content">
                                <h5 class="review-title">Great Corporate Conference</h5>
                                <p class="review-text">The team handled our annual conference flawlessly. The venue setup was perfect, and the technical support was excellent. Would definitely recommend for corporate events.</p>
                                <div class="review-meta">
                                    <span class="event-type">Corporate Event</span>
                                </div>
                            </div>
                        </div>

                        <!-- Review 3 -->
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4>Emily Rodriguez</h4>
                                        <span class="review-date">2 weeks ago</span>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    ★★★★★
                                    <span class="rating-value">5.0</span>
                                </div>
                            </div>
                            <div class="review-content">
                                <h5 class="review-title">Amazing Birthday Party!</h5>
                                <p class="review-text">My daughter's birthday party was magical! The theme execution, decorations, and entertainment were all top-notch. The children had an incredible time.</p>
                                <div class="review-meta">
                                    <span class="event-type">Birthday</span>
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
